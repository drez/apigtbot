<?php
/**
 * TREND-ARM EXIT-RULE sweep (2026-09-16) — can an exit-side rule shrink the
 * end-of-window BAG without giving up the bull-tape net? (refit-evidence §12:
 * the live arm realizes +97 over six 2026 tape-months but ends every bull
 * window holding the LAST entry of the leg as an underwater bag, −89 MTM.
 * Entry-side fixes E/G/R/H were all refuted; the exit side was never tested.)
 *
 *   php scripts/trend-exit-sweep.php [SYMBOL]
 *
 * Baseline B = the live engine since 2026-09-05, hold policy (sell_at_loss
 * OFF): Donchian(20) breakout OR regime entry (arm active ∧ close > 1h EMA20
 * > EMA50), idle 25 / active 211, initial stop 2×ATR(1h), trail 3×ATR with
 * the 1.5% floor, 3-bar cooldown, fee 0.10%, a below-breakeven stop HOLDS.
 *
 * Policies (all on top of B):
 *   BE    breakeven lock: once HWM ≥ entry + 1.5 × initial-stop-distance, the
 *         stop is floored at entry × (1 + 2·fee) forever; the trail ratchets
 *         above that floor. Winners can no longer become losers.
 *   T     tiered take-profit: at the first bar where close ≥ entry + 2 ×
 *         initial-stop-distance, book a sell of 50% of qty at that close; the
 *         remaining half keeps the standard trail.
 *   BE+T  both of the above.
 *   TS    HWM-stall bail: HWM unchanged for 24 consecutive 1h bars AND close
 *         < entry → exit at close. Reported under BOTH sell_at_loss settings:
 *         with the live switch OFF the bail is refused (blocked, held — the
 *         cost of the current no-loss switch), with ON it executes.
 * B/stop and TS/stop rows are context (the §12 stop-policy comparison).
 *
 * Acceptance (pre-registered, printed PASS/FAIL; only BE/T/BE+T are eligible
 * for implementation because they keep the live sell_at_loss OFF posture):
 *   bull       net ≥ B − 5% of |B net| (min 1 USDT) on both symbols,
 *              |end MTM| reduced ≥ 30% vs B on both symbols,
 *              maxDD ≤ B + 21 USDT on both symbols.
 *   bear       net ≥ B − 10 USDT and maxDD ≤ B + 21 USDT on both symbols.
 *   2026 sum   Σ net ≥ Σ B net and Σ|end MTM| reduced ≥ 30% vs B
 *              (three windows × both symbols).
 *
 * Tapes: tmp/gate-{SYMBOL}-15m-*.json (bull 2023-11→2025-01, bear
 * 2025-10→2026-07 — the same tapes trend-entry-sweep used) and the cached
 * tmp/replay-{SYMBOL}-15m-*.json 2026 windows (Apr–May, Jun–Jul, Aug–Sep,
 * fetched with 45d warm-up by month-replay).
 *
 * Read-only: no DB writes, no orders. Honesty notes as trend-entry-sweep:
 * entries at bar close, optimistic intrabar trail (ratchet off the high
 * before testing the low), hourly activator passes stand in for the live
 * 15-min passes, end-aligned rolling 4h/1d buckets.
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
const REGIME_WINDOW = 200;
const WARMUP_H = 24 * 30;     // 1h bars before the first decision on the gate tapes
const IDLE_TARGET = '25';     // 100 × 25%
const ACTIVE_TARGET = '211';  // 422 × 50%
const TS_STALL_BARS = 24;     // HWM unchanged for this many 1h bars → bail condition
const BE_LOCK_MULT = '1.5';   // × initial stop distance before the stop floors at breakeven
const TIER_MULT = '2';        // × initial stop distance for the 50% take-profit

$admin = dirname(__DIR__);
$symbols = isset($argv[1]) ? [strtoupper((string) $argv[1])] : ['BTCUSDT', 'BNBUSDT'];

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
 * Regime state per 1h bar: 'idle' | 'active' | 'winding_down'.
 * @return array<int, array{state:string, verdict:?string}>
 */
function regimeStates(array $all, int $off, int $nH, int $firstDecision): array
{
    $n = count($all);
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
        if ($i >= $firstDecision) {
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

function hstamp(array $bar): string
{
    return gmdate('m-d H:i', (int) ($bar['t'] / 1000));
}

/**
 * Walk one policy over the 1h tape from $first.
 *
 * @param array<int,array{high:string,low:string,close:string,t:int}> $h1
 * @param array<int,array{state:string,verdict:?string}> $regime
 * @return array{net:float,realized:float,unrealized:float,trades:int,wins:int,max_dd:float,tim:float,active:float,bag:bool,open_at_end:bool,be_locks:int,tiers:int,bails:int,bail_blocked:int}
 */
function simulateTrend(array $h1, array $regime, int $first, bool $sellAtLoss, string $policy): array
{
    $n = count($h1);
    $highs = array_column($h1, 'high');
    $lows = array_column($h1, 'low');
    $closes = array_column($h1, 'close');

    $pos = null;
    $stopOutAt = null;
    $realized = '0';
    $trades = [];
    $equity = [];
    $inMarket = 0;
    $activeBars = 0;
    $armState = 'idle';
    $beLocks = 0;
    $tiers = 0;
    $bails = 0;
    $bailBlocked = 0;
    $be = str_contains($policy, 'BE');
    $tier = str_contains($policy, 'T');
    $ts = str_contains($policy, 'TS');

    for ($i = $first; $i < $n; $i++) {
        $close = $closes[$i];
        $low = $lows[$i];
        $high = $highs[$i];
        $now = gmdate('Y-m-d H:i:s', (int) ($h1[$i]['t'] / 1000));

        $raw = $regime[$i]['state'];
        $armState = $raw === 'active' ? 'active' : ($raw === 'winding_down' ? 'winding_down' : ($armState === 'winding_down' && $pos !== null ? 'winding_down' : 'idle'));
        $target = $armState === 'active' ? ACTIVE_TARGET : ($armState === 'winding_down' ? '0' : IDLE_TARGET);
        if ($armState === 'active') {
            $activeBars++;
        }

        // mark to market
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
                if (bccomp($r['hwm'], $pos['hwm'], SCALE) > 0) {
                    $pos['lastHwmBar'] = $i;
                }
                $pos['hwm'] = $r['hwm'];
                $pos['stop'] = $r['stop'];
            }

            // BE: once HWM ≥ entry + 1.5 × initial stop distance, floor the
            // stop at breakeven forever (the trail keeps ratcheting above it).
            if ($be && !$pos['beLocked']) {
                if (bccomp($pos['hwm'], bcadd($pos['entry'], bcmul(BE_LOCK_MULT, $pos['initialDist'], SCALE), SCALE), SCALE) >= 0) {
                    $pos['beLocked'] = true;
                    $beLocks++;
                }
            }
            if ($pos['beLocked']) {
                $breakeven = bcmul($pos['entry'], bcadd('1', bcmul('2', FEE_PCT, SCALE), SCALE), SCALE);
                if (bccomp($breakeven, $pos['stop'], SCALE) > 0) {
                    $pos['stop'] = $breakeven;
                }
            }

            // T: first bar whose close reaches +2 × initial stop distance books
            // 50% of qty at that close; the rest keeps the trail.
            if ($tier && !$pos['tierDone'] && bccomp($close, bcadd($pos['entry'], bcmul(TIER_MULT, $pos['initialDist'], SCALE), SCALE), SCALE) >= 0) {
                $half = bcdiv($pos['qty'], '2', SCALE);
                $exitFee = bcmul(bcmul($half, $close, SCALE), FEE_PCT, SCALE);
                $entryFeeShare = bcmul($pos['fee'], '0.5', SCALE);
                $net = bcsub(bcmul(bcsub($close, $pos['entry'], SCALE), $half, SCALE), bcadd($entryFeeShare, $exitFee, SCALE), SCALE);
                $realized = bcadd($realized, $net, SCALE);
                $pos['qty'] = bcsub($pos['qty'], $half, SCALE);
                $pos['fee'] = bcsub($pos['fee'], $entryFeeShare, SCALE);
                $pos['tierDone'] = true;
                $tiers++;
            }

            // TS: HWM stalled for TS_STALL_BARS and underwater → bail at close.
            // sell_at_loss OFF refuses it (the live switch) and it is counted.
            if ($ts && (int) $i - (int) $pos['lastHwmBar'] >= TS_STALL_BARS && bccomp($close, $pos['entry'], SCALE) < 0) {
                if (!$pos['bailLogged']) {
                    $pos['bailLogged'] = true;
                    if ($sellAtLoss) {
                        $exitFee = bcmul(bcmul($pos['qty'], $close, SCALE), FEE_PCT, SCALE);
                        $net = bcsub(bcmul(bcsub($close, $pos['entry'], SCALE), $pos['qty'], SCALE), bcadd($pos['fee'], $exitFee, SCALE), SCALE);
                        $realized = bcadd($realized, $net, SCALE);
                        $trades[] = (float) $net;
                        $bails++;
                        $stopOutAt = $now;
                        $pos = null;
                        continue;
                    }
                    $bailBlocked++;
                }
            } elseif ($pos['bailLogged'] && bccomp($close, $pos['entry'], SCALE) >= 0) {
                $pos['bailLogged'] = false; // recovered above entry — a later spell re-arms the bail
            }

            // standard trailing-stop exit
            if (bccomp($low, $pos['stop'], SCALE) <= 0) {
                $fill = bccomp($close, $pos['stop'], SCALE) < 0 ? $close : $pos['stop'];
                $breakeven = bcmul($pos['entry'], bcadd('1', bcmul('2', FEE_PCT, SCALE), SCALE), SCALE);
                if ($sellAtLoss || bccomp($fill, $breakeven, SCALE) >= 0) {
                    $exitFee = bcmul(bcmul($pos['qty'], $fill, SCALE), FEE_PCT, SCALE);
                    $gross = bcmul(bcsub($fill, $pos['entry'], SCALE), $pos['qty'], SCALE);
                    $net = bcsub($gross, bcadd($pos['fee'], $exitFee, SCALE), SCALE);
                    $realized = bcadd($realized, $net, SCALE);
                    $trades[] = (float) $net;
                    $stopOutAt = $now;
                    $pos = null;
                    continue;
                }
                // sell_at_loss OFF: a below-breakeven stop holds
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
        $signal = TrendEngine::entrySignal($window, DONCHIAN, EMA_FAST, EMA_SLOW);
        $regimeEntry = !$signal && $armState === 'active' && TrendEngine::regimeEntrySignal($window, EMA_FAST, EMA_SLOW);
        if (!$signal && !$regimeEntry) {
            continue;
        }
        $quote = $target;
        $qty = bcdiv($quote, $close, SCALE);
        $fee = bcmul($quote, FEE_PCT, SCALE);
        $initialDist = TrendEngine::stopDistance($close, INITIAL_MULT, $atrS, STOP_FLOOR_PCT);
        $stop = bcsub($close, $initialDist, SCALE);
        $pos = [
            'entry' => $close, 'qty' => $qty, 'hwm' => $close, 'stop' => $stop,
            'fee' => $fee, 'bar' => $i, 'initialDist' => $initialDist, 'atrEntry' => $atrS,
            'beLocked' => false, 'tierDone' => false, 'lastHwmBar' => $i, 'bailLogged' => false,
        ];
    }

    $final = $realized;
    $bag = false;
    $open = false;
    if ($pos !== null) {
        $unreal = bcmul(bcsub(end($closes), $pos['entry'], SCALE), $pos['qty'], SCALE);
        $final = bcadd($realized, bcsub($unreal, $pos['fee'], SCALE), SCALE);
        $bag = bccomp(end($closes), $pos['entry'], SCALE) < 0;
        $open = true;
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
        'unrealized' => (float) $final - (float) $realized,
        'trades' => count($trades),
        'wins' => count(array_filter($trades, fn ($t) => $t > 0)),
        'max_dd' => $maxDd,
        'tim' => ($n - $first) > 0 ? $inMarket / ($n - $first) * 100 : 0,
        'active' => ($n - $first) > 0 ? $activeBars / ($n - $first) * 100 : 0,
        'bag' => $bag,
        'open_at_end' => $open,
        'be_locks' => $beLocks,
        'tiers' => $tiers,
        'bails' => $bails,
        'bail_blocked' => $bailBlocked,
    ];
}

// ── run ───────────────────────────────────────────────────────────────────

$results = [];   // [symbol][tape][policy] = metrics
$h1ByTape = [];  // [symbol][tape] = [h1, first, hodlLabel...]

foreach ($symbols as $symbol) {
    $tapes = [
        'bull' => ['file' => "{$admin}/tmp/gate-{$symbol}-15m-2023-11-01-2025-01-01.json", 'from' => '2023-11-01', 'to' => '2025-01-01'],
        'bear' => ['file' => "{$admin}/tmp/gate-{$symbol}-15m-2025-10-01-2026-07-31.json", 'from' => '2025-10-01', 'to' => '2026-07-31'],
        '2026 Apr–May' => ['file' => "{$admin}/tmp/replay-{$symbol}-15m-2026-04-01-2026-05-31.json", 'from' => '2026-04-01', 'to' => '2026-05-31'],
        '2026 Jun–Jul' => ['file' => "{$admin}/tmp/replay-{$symbol}-15m-2026-06-01-2026-07-31.json", 'from' => '2026-06-01', 'to' => '2026-07-31'],
        '2026 Aug–Sep' => ['file' => "{$admin}/tmp/replay-{$symbol}-15m-2026-08-02-2026-09-16.json", 'from' => '2026-08-02', 'to' => '2026-09-16'],
    ];

    foreach ($tapes as $name => $tape) {
        if (!is_file($tape['file'])) {
            echo "missing tape {$tape['file']}\n";
            continue;
        }
        $all = json_decode((string) file_get_contents($tape['file']), true);
        $off = count($all) % 4;
        $h1 = aggRange($all, $off, count($all), 4);

        if (str_starts_with($name, 'bull') || str_starts_with($name, 'bear')) {
            $first = 0;                       // trend-entry-sweep convention: whole tape tradable
            $firstDecision = WARMUP_H;
            $hodlFrom = $h1[WARMUP_H]['close'];
            $window = $h1;
        } else {
            $fromTs = strtotime("{$tape['from']} 00:00:00 UTC");
            $first = 0;
            foreach ($h1 as $k => $b) {
                if ($b['t'] / 1000 >= $fromTs) {
                    $first = $k;
                    break;
                }
            }
            $firstDecision = max(0, $first - 48);
            $hodlFrom = $h1[$first]['close'];
            $window = array_slice($h1, $first);
        }

        $t0 = microtime(true);
        $regime = regimeStates($all, $off, count($h1), $firstDecision);
        $activeH = count(array_filter(array_slice($regime, $first), fn ($r) => $r['state'] === 'active'));
        $verdicts = array_count_values(array_map(fn ($r) => (string) $r['verdict'], array_slice($regime, $first)));
        $hodl = (float) ACTIVE_TARGET * ((float) end($window)['close'] / (float) $hodlFrom - 1);
        printf("═══ %s %s — %d×1h (%s → %s) · active %d h (%.0f%%) · verdicts %s · HODL(%s) %+.2f · regime %.1fs ═══\n",
            $symbol, $name, count($window),
            gmdate('Y-m-d', $window[0]['t'] / 1000), gmdate('Y-m-d', end($window)['t'] / 1000),
            $activeH, count($window) > 0 ? $activeH / count($window) * 100 : 0,
            implode(' ', array_map(fn ($k, $v) => "{$k}:{$v}", array_keys($verdicts), $verdicts)),
            ACTIVE_TARGET, $hodl, microtime(true) - $t0);

        printf("%-8s %9s %9s %9s %6s %5s %8s %6s %7s %6s %6s %6s  %s\n",
            'policy', 'net', 'realized', 'unreal', 'trades', 'wins', 'maxDD', 'tim%', 'beLock', 'tiers', 'bails', 'blk', 'bag');

        $policies = [
            'B/hold' => [false, 'B'],
            'BE/hold' => [false, 'BE'],
            'T/hold' => [false, 'T'],
            'BE+T/hold' => [false, 'BE+T'],
            'TS/hold' => [false, 'TS'],
            'B/stop' => [true, 'B'],
            'TS/stop' => [true, 'TS'],
        ];
        foreach ($policies as $label => [$sal, $pol]) {
            $r = simulateTrend($h1, $regime, $first, $sal, $pol);
            $results[$symbol][$name][$label] = $r;
            printf("%-8s %+9.2f %+9.2f %+9.2f %6d %5d %8.2f %5.1f%% %7d %6d %6d %6d  %s\n",
                $label, $r['net'], $r['realized'], $r['unrealized'], $r['trades'], $r['wins'],
                $r['max_dd'], $r['tim'], $r['be_locks'], $r['tiers'], $r['bails'], $r['bail_blocked'],
                $r['bag'] ? 'bag' : ($r['open_at_end'] ? 'open' : '-'));
        }
        echo "\n";
    }
}

// ── acceptance ────────────────────────────────────────────────────────────
echo "═══ acceptance — bull: net ≥ B−5% · |MTM| ≤ 0.7×B · DD ≤ B+21 (both) ; bear: net ≥ B−10 · DD ≤ B+21 (both) ; 2026: Σnet ≥ B · Σ|MTM| ≤ 0.7×B ═══\n";

function absUnreal(array $r): float
{
    return abs($r['unrealized']);
}

$eligible = ['BE/hold', 'T/hold', 'BE+T/hold'];
foreach ($eligible as $label) {
    $ok = true;
    $why = [];
    foreach ($symbols as $symbol) {
        $r = $results[$symbol] ?? [];
        if (!isset($r['bull']['B/hold'], $r['bull'][$label], $r['bear']['B/hold'], $r['bear'][$label])) {
            $ok = false;
            $why[] = "{$symbol}: missing tape";
            continue;
        }
        $b = $r['bull']['B/hold'];
        $v = $r['bull'][$label];
        $tol = max(1.0, 0.05 * abs($b['net']));
        if ($v['net'] < $b['net'] - $tol) {
            $ok = false;
            $why[] = sprintf('%s bull %+.2f < B %+.2f − %.1f', $symbol, $v['net'], $b['net'], $tol);
        }
        if (absUnreal($v) > 0.7 * absUnreal($b) + 1.0) {
            $ok = false;
            $why[] = sprintf('%s bull |MTM| %.2f > 0.7×B %.2f', $symbol, absUnreal($v), 0.7 * absUnreal($b));
        }
        if ($v['max_dd'] > $b['max_dd'] + 21) {
            $ok = false;
            $why[] = sprintf('%s bull DD %.2f > B %.2f + 21', $symbol, $v['max_dd'], $b['max_dd']);
        }
        $bb = $r['bear']['B/hold'];
        $vb = $r['bear'][$label];
        if ($vb['net'] < $bb['net'] - 10) {
            $ok = false;
            $why[] = sprintf('%s bear %+.2f < B %+.2f − 10', $symbol, $vb['net'], $bb['net']);
        }
        if ($vb['max_dd'] > $bb['max_dd'] + 21) {
            $ok = false;
            $why[] = sprintf('%s bear DD %.2f > B %.2f + 21', $symbol, $vb['max_dd'], $bb['max_dd']);
        }
    }

    // 2026 three windows × both symbols
    $sumB = 0.0;
    $sumV = 0.0;
    $absB = 0.0;
    $absV = 0.0;
    foreach ($symbols as $symbol) {
        foreach (['2026 Apr–May', '2026 Jun–Jul', '2026 Aug–Sep'] as $t) {
            if (!isset($results[$symbol][$t]['B/hold'], $results[$symbol][$t][$label])) {
                $ok = false;
                $why[] = "{$symbol} {$t}: missing";
                continue 2;
            }
            $sumB += $results[$symbol][$t]['B/hold']['net'];
            $sumV += $results[$symbol][$t][$label]['net'];
            $absB += absUnreal($results[$symbol][$t]['B/hold']);
            $absV += absUnreal($results[$symbol][$t][$label]);
        }
    }
    if ($sumV < $sumB) {
        $ok = false;
        $why[] = sprintf('2026 Σ %+.2f < B %+.2f', $sumV, $sumB);
    }
    if ($absV > 0.7 * $absB + 1.0) {
        $ok = false;
        $why[] = sprintf('2026 Σ|MTM| %.2f > 0.7×B %.2f', $absV, 0.7 * $absB);
    }
    printf("%-10s %s%s\n", $label, $ok ? 'PASS' : 'FAIL', $why ? ' — ' . implode('; ', $why) : '');
}

echo "\nTS rows are evidence only: TS/hold is refused by the live sell_at_loss OFF\nswitch (bail_blocked counts the episodes); TS/stop shows what the bail earns\nif the operator allows below-cost exits. Eligible to implement without touching\nsell_at_loss: only the rows printed above.\n";
echo "\nHonesty notes: entries at bar close; optimistic intrabar trail (ratchet off\nthe high before testing the low); hourly activator passes stand in for the live\n15-min passes; end-aligned rolling 4h/1d buckets; gate tapes are fully tradable\nfrom bar 0 with a 720-bar regime warm-up (trend-entry-sweep convention), 2026\nwindows trade only from the window start (month-replay convention).\n";
