<?php
/**
 * TREND-ARM ENTRY-RULE sweep (2026-09-05) — does the machine-scaled trend
 * arm earn more when it may ENTER on activation (and top up to its active
 * slice) instead of waiting for a fresh 1h Donchian breakout?
 *
 * Why: prod run 1 was activated 2026-09-02 19:48 (slice 100 → 422, deploy
 * 25 → 50) while holding 0.00031 BTC (24 USDT) from its slice-100 entry.
 * TrendEngine has no top-up path for Donchian runs and tryEnter only fires
 * on close > prior 20-bar 1h high, so the arm rode the 77.2k → 81.9k leg
 * with 24 of 211 deployable USDT, exited +0.21 on 09-04 and then sat FLAT
 * through three more days of TREND_UP verdicts (price 79.7k vs the 81.4k
 * 20-bar high). ~211 USDT idle in exactly the regime the arm exists for.
 *
 * Protocol: the cached 15m tapes (tmp/gate-{SYMBOL}-15m-*.json — bull
 * 2023-11→2025-01, bear 2025-10→2026-07), aggregated to 1h (signal TF),
 * 4h and 1d (regime TFs, end-aligned rolling buckets, last 200). Every 1h
 * bar: TrendRegime::classify on the 4h/1d summaries (the live classifier,
 * hold-side thresholds while active) drives a state machine mirroring
 * TrendActivator (hourly passes: one pass ≈ the live 2-pass confirm /
 * 4-pass deactivate at 15 min); the arm's TARGET notional follows the prod
 * shape: idle 100 × 25% = 25, active 422 × 50% = 211, winding_down 0.
 *
 * Arms (all: 3×ATR(1h) trail with the 1.5% floor, initial 2×ATR, cooldown
 * 3 bars, fee 0.10%, sell_at_loss OFF = a stop breach that would realize
 * a loss HOLDS, the exit re-arms above breakeven — the live rule):
 *   A   Donchian only (the current engine)
 *   B   A OR (active ∧ flat ∧ close > 1h EMA20 > EMA50)   — enter on activation
 *   C   A OR (active ∧ flat ∧ EMA20 > EMA50 ∧ close ≤ EMA20 × 1.005) — pullback entry
 *   A+  A plus TOP-UP: while active and holding < target − ½ tranche, add
 *       a tranche on a pullback to the 1h EMA20 (≤ +1%) or 24h after the
 *       previous tranche (coreTrancheDue's rule)
 *   B+  B plus top-up
 *   C+  C plus top-up
 *
 * 2026-09-16 arms (month-replay finding: across six 2026 tape-months the
 * live arm B realized +97 but ended every window holding the LAST entry of
 * the leg as a bag, −89 MTM; that entry is almost always a regime re-entry
 * placed within hours of a winning trailing-stop exit, at the same price):
 *   E   B + extension veto: no entry when close > 1.04 × mean(close, 120h)
 *   G   B + post-winner cooldown: 24 bars after an exit whose hwm ≥ +3%
 *   R   B, but a regime re-entry after a stop-out needs close > the exited
 *       position's high-water mark (the leg must make a new high; the first
 *       entry of an activation is unrestricted; Donchian entries unchanged)
 *   H   B + ONE-SHOT top-up to the active target at the idle→active
 *       transition when already holding an idle-size position (not the
 *       refuted continuous top-up)
 *   RH  R + H
 *
 * Output per tape × symbol × arm: net (realized + MTM at the end), realized,
 * trades/wins, max drawdown (USDT), time in market, whether it ends holding
 * a bag below cost; HODL(211) and the arm's active-time share for scale.
 *
 * Pre-registered acceptance (plan 2026-09-05): ship an arm only if bull
 * net > A on BOTH symbols AND bear net ≥ A − 10 AND max DD ≤ A + 21 USDT
 * (5 pts of the 422 slice) on both symbols.
 *
 * Intrabar assumptions as in trend-sweep.php: ratchet off the bar's high
 * before testing the low (optimistic trail); entries at the bar close.
 */

require '/path/to/apigtbot/.admin/vendor/autoload.php';

use App\Domains\Bot\Engine\TrendEngine;
use App\Domains\Bot\Indicators;
use App\Domains\Bot\TrendRegime;

const SCALE = 8;
const FEE_PCT = '0.001';
const DONCHIAN = 20;
const EMA_FAST = 20;
const EMA_SLOW = 50;
const ATR_PERIOD = 14;
const STOP_MULT = '3';
const INITIAL_MULT = '2.0';
const STOP_FLOOR_PCT = '0.015';
const COOLDOWN_BARS = 3;
const SIGNAL_WINDOW = 300;
const REGIME_WINDOW = 200;   // 4h / 1d buckets fed to the classifier
const WARMUP_H = 24 * 30;    // 1h bars before the first decision (1d indicators need history)
const IDLE_TARGET = '25';    // 100 × 25%
const ACTIVE_TARGET = '211'; // 422 × 50%
const TRANCHES = 2;          // max_order_quote 30% of 422 → 211 splits in 2
const TOPUP_PULLBACK = '1.01';
const TOPUP_SPACING = 86400;

$admin = dirname(__DIR__);
$symbols = isset($argv[1]) ? [strtoupper((string) $argv[1])] : ['BTCUSDT', 'BNBUSDT'];
$tapes = [
    'bull' => ['2023-11-01', '2025-01-01'],
    'bear' => ['2025-10-01', '2026-07-31'],
];

/** Aggregate 15m candles into buckets of $k over [$from, $to). */
function aggRange(array $c, int $from, int $to, int $k): array
{
    $out = [];
    for ($i = $from; $i + $k <= $to; $i += $k) {
        $hi = -INF;
        $lo = INF;
        for ($j = $i; $j < $i + $k; $j++) {
            $hi = max($hi, (float) $c[$j]['high']);
            $lo = min($lo, (float) $c[$j]['low']);
        }
        $out[] = ['high' => (string) $hi, 'low' => (string) $lo, 'close' => (string) $c[$i + $k - 1]['close'], 't' => (int) $c[$i + $k - 1]['t']];
    }
    return $out;
}

function summary4h(array $bars): array
{
    $h = array_column($bars, 'high');
    $l = array_column($bars, 'low');
    $c = array_column($bars, 'close');
    return [
        'trend' => Indicators::trend($c),
        'adx14' => Indicators::adx($h, $l, $c),
        'er20' => Indicators::efficiencyRatio($c, 20),
        'chop14' => Indicators::choppiness($h, $l, $c, 14),
        'stale' => false,
    ];
}

function summary1d(array $bars): array
{
    $h = array_column($bars, 'high');
    $l = array_column($bars, 'low');
    $c = array_column($bars, 'close');
    return [
        'trend' => Indicators::trend($c),
        'price' => (float) end($c),
        'ema20' => Indicators::ema($c, 20),
        'ema50' => Indicators::ema($c, 50),
        'adx14' => Indicators::adx($h, $l, $c),
        'er20' => Indicators::efficiencyRatio($c, 20),
        'stale' => false,
    ];
}

/**
 * Regime state per 1h bar: 'idle' | 'active' | 'winding_down' plus the
 * target notional. One hourly pass stands in for the live 15-min passes
 * (2 to confirm = 30 min, 4 to deactivate = 60 min ≈ one bar).
 * @return array<int, array{state:string, target:string, verdict:?string}>
 */
function regimeStates(array $all, int $off, int $nH): array
{
    $n = count($all);
    // end-aligned bucket series per phase: buckets ending exactly at hour i
    $s4 = [];
    for ($p = 0; $p < 4; $p++) {
        $s4[$p] = aggRange($all, $off + 4 * $p, $n, 16);
    }
    $s1d = [];
    for ($p = 0; $p < 24; $p++) {
        $s1d[$p] = aggRange($all, $off + 4 * $p, $n, 96);
    }
    $state = 'idle';
    $out = [];
    for ($i = 0; $i < $nH; $i++) {
        $verdict = null;
        if ($i >= WARMUP_H) {
            $p4 = ($i + 1) % 4;
            $j4 = intdiv($i + 1 - $p4, 4) - 1;
            $p1 = ($i + 1) % 24;
            $j1 = intdiv($i + 1 - $p1, 24) - 1;
            if ($j4 >= 30 && $j1 >= 30) {
                $b4 = array_slice($s4[$p4], max(0, $j4 + 1 - REGIME_WINDOW), min(REGIME_WINDOW, $j4 + 1));
                $b1 = array_slice($s1d[$p1], max(0, $j1 + 1 - REGIME_WINDOW), min(REGIME_WINDOW, $j1 + 1));
                $verdict = TrendRegime::classify(summary4h($b4), summary1d($b1), $state === 'active');
            }
        }
        if ($verdict !== null) {
            if ($state === 'active') {
                if ($verdict !== 'TREND_UP') {
                    $state = 'winding_down';
                }
            } elseif ($verdict === 'TREND_UP') {
                $state = 'active';
            }
        }
        $out[$i] = ['state' => $state, 'verdict' => $verdict];
    }
    return $out;
}

function barTime(int $i): string
{
    return date('Y-m-d H:i:s', 1577836800 + $i * 3600);
}

/**
 * Walk one arm over the 1h tape.
 * @param array<int,array{high:string,low:string,close:string}> $h1
 * @param array<int,array{state:string,verdict:?string}> $regime
 */
function simulate(array $h1, array $regime, string $rule, bool $topUp): array
{
    $n = count($h1);
    $highs = array_column($h1, 'high');
    $lows = array_column($h1, 'low');
    $closes = array_column($h1, 'close');

    $pos = null;       // ['entry','qty','hwm','stop','fee','invested','tranches','lastAt']
    $stopOutAt = null;
    $realized = '0';
    $trades = [];
    $equity = [];
    $inMarket = 0;
    $activeBars = 0;
    $regimeEntries = 0;
    $topUps = 0;
    $released = false; // winding_down → idle needs a flat arm (TrendActivator release)
    $armState = 'idle';
    $lastExitHwm = null;   // R: hwm of the last stopped-out position in this activation
    $lastExitBar = null;   // G: bar index of the last exit
    $lastExitWin = false;  // G: did that exit ride ≥ +3%?
    $extVeto = str_contains($rule, 'E');
    $winCooldown = str_contains($rule, 'G');
    $freshHigh = str_contains($rule, 'R');
    $oneShot = str_contains($rule, 'H');
    $belowExit = str_contains($rule, 'X'); // X: regime re-entry needs close < the last exit fill
    $halfReentry = str_contains($rule, 'S'); // S: re-entries after a winning exit in the same activation are half size
    $lastExitFill = null;
    $winsThisActivation = 0;
    $base = $rule === 'A' ? 'A' : ($rule === 'C' ? 'C' : 'B');

    for ($i = 0; $i < $n; $i++) {
        $close = $closes[$i];
        $low = $lows[$i];
        $high = $highs[$i];
        $now = barTime($i);

        // arm state: the activator releases a winding_down arm only once flat
        $raw = $regime[$i]['state'];
        $wasActive = $armState === 'active';
        if ($raw === 'active') {
            if (!$wasActive) {
                $lastExitFill = null;
                $winsThisActivation = 0;
                $lastExitHwm = null; // fresh activation: the first entry is unrestricted
            }
            $armState = 'active';
        } elseif ($raw === 'winding_down') {
            $armState = 'winding_down';
        } else {
            $armState = ($armState === 'winding_down' && $pos !== null) ? 'winding_down' : 'idle';
        }
        $target = $armState === 'active' ? ACTIVE_TARGET : ($armState === 'winding_down' ? '0' : IDLE_TARGET);
        if ($armState === 'active') {
            $activeBars++;
        }

        // mark
        if ($pos === null) {
            $mark = $realized;
        } else {
            $unreal = bcmul(bcsub($close, $pos['entry'], SCALE), $pos['qty'], SCALE);
            $mark = bcadd($realized, bcsub($unreal, $pos['fee'], SCALE), SCALE);
            $inMarket++;
        }
        $equity[] = (float) $mark;

        $from = max(0, $i + 1 - SIGNAL_WINDOW);
        $ctxH = array_slice($highs, $from, $i + 1 - $from);
        $ctxL = array_slice($lows, $from, $i + 1 - $from);
        $ctxC = array_slice($closes, $from, $i + 1 - $from);
        $atr = $i >= ATR_PERIOD ? Indicators::atr($ctxH, $ctxL, $ctxC, ATR_PERIOD) : 0.0;
        $atrS = number_format($atr, SCALE, '.', '');

        if ($pos !== null) {
            if ($atr > 0) {
                $r = TrendEngine::ratchet(['entry' => $pos['entry'], 'hwm' => $pos['hwm'], 'stop' => $pos['stop']], $high, STOP_MULT, $atrS, STOP_FLOOR_PCT);
                $pos['hwm'] = $r['hwm'];
                $pos['stop'] = $r['stop'];
            }
            if (bccomp($low, $pos['stop'], SCALE) <= 0) {
                $fill = bccomp($close, $pos['stop'], SCALE) < 0 ? $close : $pos['stop'];
                $breakeven = bcmul($pos['entry'], bcadd('1', bcmul('2', FEE_PCT, SCALE), SCALE), SCALE);
                if (bccomp($fill, $breakeven, SCALE) >= 0) {
                    $exitFee = bcmul(bcmul($pos['qty'], $fill, SCALE), FEE_PCT, SCALE);
                    $gross = bcmul(bcsub($fill, $pos['entry'], SCALE), $pos['qty'], SCALE);
                    $net = bcsub($gross, bcadd($pos['fee'], $exitFee, SCALE), SCALE);
                    $realized = bcadd($realized, $net, SCALE);
                    $trades[] = (float) $net;
                    $stopOutAt = $now;
                    $lastExitHwm = $pos['hwm'];
                    $lastExitFill = $fill;
                    if ((float) $net > 0) {
                        $winsThisActivation++;
                    }
                    $lastExitBar = $i;
                    $lastExitWin = (float) $pos['hwm'] / (float) $pos['entry'] - 1 >= 0.03;
                    $pos = null;
                    continue;
                }
                // sell_at_loss OFF: hold, the stop keeps ratcheting
            }
            // H: one-shot top-up at the idle→active transition
            if ($oneShot && $armState === 'active' && !$wasActive) {
                $short = bcsub($target, $pos['invested'], SCALE);
                if (bccomp($short, '5', SCALE) > 0) {
                    $qty = bcdiv($short, $close, SCALE);
                    $fee = bcmul($short, FEE_PCT, SCALE);
                    $cost = bcadd(bcmul($pos['entry'], $pos['qty'], SCALE), $short, SCALE);
                    $pos['qty'] = bcadd($pos['qty'], $qty, SCALE);
                    $pos['entry'] = bcdiv($cost, $pos['qty'], SCALE);
                    $pos['fee'] = bcadd($pos['fee'], $fee, SCALE);
                    $pos['invested'] = bcadd($pos['invested'], $short, SCALE);
                    $pos['lastAt'] = $now;
                    $topUps++;
                }
            }
            // top-up: while ACTIVE and under target by more than half a tranche
            if ($topUp && $armState === 'active') {
                $tranche = bcdiv($target, (string) TRANCHES, SCALE);
                $short = bcsub($target, $pos['invested'], SCALE);
                if (bccomp($short, bcdiv($tranche, '2', SCALE), SCALE) > 0) {
                    $ema20 = Indicators::ema($ctxC, EMA_FAST);
                    $due = bccomp($close, bcmul((string) $ema20, TOPUP_PULLBACK, SCALE), SCALE) <= 0
                        || strtotime($now) - strtotime($pos['lastAt']) >= TOPUP_SPACING;
                    if ($due) {
                        $add = bccomp($short, $tranche, SCALE) < 0 ? $short : $tranche;
                        $qty = bcdiv($add, $close, SCALE);
                        $fee = bcmul($add, FEE_PCT, SCALE);
                        $cost = bcadd(bcmul($pos['entry'], $pos['qty'], SCALE), $add, SCALE);
                        $pos['qty'] = bcadd($pos['qty'], $qty, SCALE);
                        $pos['entry'] = bcdiv($cost, $pos['qty'], SCALE);
                        $pos['fee'] = bcadd($pos['fee'], $fee, SCALE);
                        $pos['invested'] = bcadd($pos['invested'], $add, SCALE);
                        $pos['lastAt'] = $now;
                        $topUps++;
                    }
                }
            }
            continue;
        }

        // flat
        if (bccomp($target, '0', SCALE) <= 0 || $atr <= 0) {
            continue;
        }
        if (!TrendEngine::cooldownElapsed($stopOutAt, COOLDOWN_BARS, '1h', $now)) {
            continue;
        }
        $window = [];
        for ($k = 0; $k < count($ctxC); $k++) {
            $window[] = ['high' => $ctxH[$k], 'low' => $ctxL[$k], 'close' => $ctxC[$k]];
        }
        if ($winCooldown && $lastExitBar !== null && $lastExitWin && $i - $lastExitBar < 24) {
            continue;
        }
        if ($extVeto) {
            $m120 = array_slice($ctxC, -120);
            $mean = array_sum(array_map('floatval', $m120)) / count($m120);
            if ((float) $close > 1.04 * $mean) {
                continue;
            }
        }
        $signal = TrendEngine::entrySignal($window, DONCHIAN, EMA_FAST, EMA_SLOW);
        $regimeEntry = false;
        if (!$signal && $armState === 'active' && $base !== 'A' && count($ctxC) >= EMA_SLOW) {
            $e20 = Indicators::ema($ctxC, EMA_FAST);
            $e50 = Indicators::ema($ctxC, EMA_SLOW);
            if ($base === 'B') {
                $regimeEntry = (float) $close > $e20 && $e20 > $e50;
            } elseif ($base === 'C') {
                $regimeEntry = $e20 > $e50 && (float) $close <= $e20 * 1.005;
            }
            if ($regimeEntry && $freshHigh && $lastExitHwm !== null && bccomp($close, $lastExitHwm, SCALE) <= 0) {
                $regimeEntry = false; // R: the leg has not made a new high since the stop-out
            }
            if ($regimeEntry && $belowExit && $lastExitFill !== null && bccomp($close, $lastExitFill, SCALE) >= 0) {
                $regimeEntry = false; // X: buy back only under where the last position was sold
            }
        }
        if (!$signal && !$regimeEntry) {
            continue;
        }
        $quote = $target;
        if ($halfReentry && $armState === 'active' && $winsThisActivation > 0) {
            $quote = bcdiv($target, '2', SCALE);
        }
        if ($topUp && $armState === 'active') {
            $quote = bcdiv($target, (string) TRANCHES, SCALE); // first tranche now, the rest staggered
        }
        $qty = bcdiv($quote, $close, SCALE);
        $fee = bcmul($quote, FEE_PCT, SCALE);
        $stop = bcsub($close, TrendEngine::stopDistance($close, INITIAL_MULT, $atrS, STOP_FLOOR_PCT), SCALE);
        $pos = ['entry' => $close, 'qty' => $qty, 'hwm' => $close, 'stop' => $stop, 'fee' => $fee, 'invested' => $quote, 'lastAt' => $now];
        if ($regimeEntry) {
            $regimeEntries++;
        }
    }

    $final = $realized;
    $bag = false;
    if ($pos !== null) {
        $unreal = bcmul(bcsub(end($closes), $pos['entry'], SCALE), $pos['qty'], SCALE);
        $final = bcadd($realized, bcsub($unreal, $pos['fee'], SCALE), SCALE);
        $bag = bccomp(end($closes), $pos['entry'], SCALE) < 0;
    }
    $peak = -INF;
    $maxDd = 0.0;
    foreach ($equity as $e) {
        $peak = max($peak, $e);
        $maxDd = max($maxDd, $peak - $e);
    }
    return [
        'net' => (float) $final,
        'realized' => (float) $realized,
        'trades' => count($trades),
        'wins' => count(array_filter($trades, fn ($t) => $t > 0)),
        'max_dd' => $maxDd,
        'tim' => $n > 0 ? $inMarket / $n * 100 : 0,
        'active' => $n > 0 ? $activeBars / $n * 100 : 0,
        'regime_entries' => $regimeEntries,
        'top_ups' => $topUps,
        'bag' => $bag,
        'open_at_end' => $pos !== null,
    ];
}

$ARMS = ['A' => ['A', false], 'B' => ['B', false], 'C' => ['C', false], 'A+' => ['A', true], 'B+' => ['B', true], 'C+' => ['C', true],
    'E' => ['BE', false], 'G' => ['BG', false], 'R' => ['BR', false], 'H' => ['BH', false], 'RH' => ['BRH', false],
    'X' => ['BX', false], 'S' => ['BS', false], 'XS' => ['BXS', false]];
$results = [];
foreach ($symbols as $symbol) {
    foreach ($tapes as $name => [$from, $to]) {
        $file = "{$admin}/tmp/gate-{$symbol}-15m-{$from}-{$to}.json";
        if (!is_file($file)) {
            echo "missing tape {$file}\n";
            continue;
        }
        $all = json_decode((string) file_get_contents($file), true);
        $off = count($all) % 4;
        $h1 = aggRange($all, $off, count($all), 4);
        $t0 = microtime(true);
        $regime = regimeStates($all, $off, count($h1));
        $activeH = count(array_filter($regime, fn ($r) => $r['state'] === 'active'));
        $hodl = (float) ACTIVE_TARGET * ((float) end($h1)['close'] / (float) $h1[WARMUP_H]['close'] - 1);
        printf("═══ %s %s — %d×1h (%s → %s) · active %d h (%.0f%%) · HODL(%s from bar %d) %+.2f · regime %.1fs ═══\n",
            $symbol, $name, count($h1), date('Y-m-d', $h1[0]['t'] / 1000), date('Y-m-d', end($h1)['t'] / 1000),
            $activeH, $activeH / count($h1) * 100, ACTIVE_TARGET, WARMUP_H, $hodl, microtime(true) - $t0);
        printf("%-4s %9s %9s %6s %5s %8s %6s %7s %7s %5s\n", 'arm', 'net', 'realized', 'trades', 'wins', 'maxDD', 'tim%', 'regEnt', 'topUps', 'bag');
        foreach ($ARMS as $label => [$rule, $topUp]) {
            $r = simulate($h1, $regime, $rule, $topUp);
            $results[$symbol][$name][$label] = $r;
            printf("%-4s %+9.2f %+9.2f %6d %5d %8.2f %5.1f%% %7d %7d %5s\n",
                $label, $r['net'], $r['realized'], $r['trades'], $r['wins'], $r['max_dd'], $r['tim'], $r['regime_entries'], $r['top_ups'], $r['bag'] ? 'yes' : ($r['open_at_end'] ? 'open' : '-'));
        }
        echo "\n";
    }
}

// ── acceptance ──────────────────────────────────────────────────────────
echo "═══ acceptance — bull net > A (both symbols) ∧ bear net ≥ A − 10 ∧ maxDD ≤ A + 21 (both) ═══\n";
foreach (array_keys($ARMS) as $label) {
    if ($label === 'A') {
        continue;
    }
    $ok = true;
    $why = [];
    foreach ($symbols as $symbol) {
        $a = $results[$symbol] ?? [];
        if (!isset($a['bull'][$label], $a['bear'][$label])) {
            $ok = false;
            $why[] = "{$symbol}: missing tape";
            continue;
        }
        if ($a['bull'][$label]['net'] <= $a['bull']['A']['net']) {
            $ok = false;
            $why[] = sprintf('%s bull %+.2f ≤ A %+.2f', $symbol, $a['bull'][$label]['net'], $a['bull']['A']['net']);
        }
        if ($a['bear'][$label]['net'] < $a['bear']['A']['net'] - 10) {
            $ok = false;
            $why[] = sprintf('%s bear %+.2f < A %+.2f − 10', $symbol, $a['bear'][$label]['net'], $a['bear']['A']['net']);
        }
        foreach (['bull', 'bear'] as $t) {
            if ($a[$t][$label]['max_dd'] > $a[$t]['A']['max_dd'] + 21) {
                $ok = false;
                $why[] = sprintf('%s %s DD %.2f > A %.2f + 21', $symbol, $t, $a[$t][$label]['max_dd'], $a[$t]['A']['max_dd']);
            }
        }
    }
    printf("%-3s %s%s\n", $label, $ok ? 'PASS' : 'FAIL', $why ? ' — ' . implode('; ', $why) : '');
}
echo "\nHonesty notes: entries at bar close and optimistic intrabar trail as in
trend-sweep.php; hourly regime passes (the live activator runs every 15 min);
end-aligned rolling 4h/1d buckets stand in for exchange-aligned candles; the
idle/active targets copy the prod slice shape (100×25% / 422×50%); sell_at_loss
OFF means a stopped position below cost is HELD to the end (net = MTM).\n";
