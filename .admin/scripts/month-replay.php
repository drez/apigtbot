<?php
/**
 * MONTH REPLAY (2026-09-16) — one month of real tape through each bot type,
 * printed next to what prod actually did over the same window (todo.md:
 * "make a simulation on 1 month of historical data for each bot type,
 * report your run result vs what happened, make recommendations").
 *
 *   php scripts/month-replay.php [SYMBOL] [--from=2026-08-02] [--to=2026-09-16]
 *                                [--active=211] [--slice=300] [--warmup=45]
 *                                [--core=211,450,625] [--core-max-order=60]
 *
 * Tape: Binance 15m klines for [from − warmup, to], cached in
 * tmp/replay-{SYMBOL}-15m-{from}-{to}.json (same row shape as the gate
 * tapes: t/high/low/close), aggregated to 1h (signal) and end-aligned 4h/1d
 * buckets (regime), exactly as scripts/trend-entry-sweep.php does.
 *
 * TREND arm = the live engine since 2026-09-05: Donchian(20) breakout OR
 * regime entry (arm active ∧ close > 1h EMA20 > EMA50), 2×ATR initial /
 * 3×ATR trail with the 1.5% floor, 3-bar cooldown, activator state from
 * TrendRegime::classify on rolling 4h/1d (hourly passes), targets idle
 * 100×25% = 25 / active --active / winding_down 0. Two policies:
 *   hold  sell_at_loss OFF (prod): a below-cost stop HOLDS, exit re-arms
 *         above breakeven; net = realized + MTM of the bag at the end
 *   stop  sell_at_loss ON: the stop executes
 * Every entry is logged with where it sat in the trailing 30d range and
 * what the next 72h did to it — the "bought the top?" check.
 *
 * GRID arm = MechanicalRefit (prod since 2026-09-09): ±max(6%, 4×ATR4h) ×
 * ≤6 rungs, re-anchored when price leaves the range or every 72h, never
 * lower in a 4h down-family trend; slice --slice; three deploy arms:
 *   d25    deploy 25 the whole month (what the HOSTILE cap did to prod)
 *   d100   deploy 100 the whole month (what the cap cost / saved)
 *   gated  RegimeGate::hostile on the 4h at each anchor → 25, else 100
 * Each anchor is one Backtester::run over its segment (DecisionScorer::barPath
 * tape); the segment's ending unrealized is carried as MTM. That is the
 * width-sweep "recenter" convention: legacy lots are marked, not carried
 * as working exits — pessimistic vs prod, which keeps them as sells.
 *
 * CORE arm (EmaCross1d, --core=<slice list>) = the live inventory core since
 * 2026-09-16, machine-scaled like the Donchian arm (refit-evidence §14):
 *   in    1d EMA20 > EMA50 AND the activator marker is 'active' (deploy 100;
 *         deactivated = deploy 0 = no entry, no further tranche)
 *   size  TrendEngine::tranches(slice, deploy, --core-max-order) — tranche 1
 *         on the signal, each later one on TrendEngine::coreTrancheDue
 *         (pullback to within 1% above the 1d EMA20, or 24h since the last)
 *   out   the 1d cross DOWN, whole position at the mark; no stop, no trail
 *   hold  sell_at_loss OFF (prod): a cross-down under breakeven HOLDS and
 *         the exit re-arms above it — same policy as the trend arm's 'hold'
 * Arms printed: gate/hold (live), gate/stop (sell_at_loss ON) and cross/hold
 * (the un-gated pre-2026-09-16 core) for the first slice; gate/hold for the
 * rest. NOTE the 1d EMA50 needs > 50 daily bars: run the core with
 * --warmup=250 (prod's MarketStore carries 200 daily rows); at the default
 * 45 the 1d EMA50 degenerates to a 45-bar mean and the gate reads a
 * different tape than prod's.
 *
 * Prod actuals (MCP gtbot_pnl_report / status, 2026-09-16 13:00Z, window
 * 45d = 2026-08-02 → 09-16, all runs simulated mode) are hard-coded in
 * ACTUALS below so the table needs no DB.
 *
 * Read-only: no DB writes, no orders.
 */

require '/path/to/apigtbot/.admin/vendor/autoload.php';

use App\Domains\Bot\Backtester;
use App\Domains\Bot\DecisionScorer;
use App\Domains\Bot\Engine\TrendEngine;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Indicators;
use App\Domains\Bot\MechanicalRefit;
use App\Domains\Bot\RegimeGate;
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
const IDLE_TARGET = '25';

// prod, 2026-08-02 → 09-16 (45d), simulated mode — see the header
const ACTUALS = [
    'BTCUSDT' => [
        'trend' => ['run' => 1, 'slice' => '250 (422 until 09-09)', 'realized' => -2.23, 'unrealized' => -10.51, 'entries' => 6, 'stop_outs' => 4, 'bag' => '0.00264 @ 79 800 since 09-05'],
        'core' => ['run' => 8, 'slice' => '200', 'realized' => 0.0, 'unrealized' => -8.55, 'bag' => '0.00252 @ 79 214'],
        'grid' => ['run' => 7, 'slice' => '300', 'realized' => 4.97, 'unrealized' => -11.95, 'cycles' => 30, 'fees' => 0.81, 'deploy' => '10–55, mostly 25', 'note' => 'below range since ~09-10, 0 buy power'],
    ],
    'BNBUSDT' => [
        'trend' => ['run' => 9, 'slice' => '100 (created 09-09)', 'realized' => 0.0, 'unrealized' => -0.66, 'entries' => 1, 'stop_outs' => 0, 'bag' => '0.034 @ 731.38 since 09-14 (Halted)'],
        'grid' => ['run' => 4, 'slice' => '450', 'realized' => 10.01, 'unrealized' => -7.32, 'cycles' => 81, 'fees' => 2.06, 'deploy' => '25–55, mostly 25', 'note' => '09-02 starved release sold 14 lots at 686 (−2.97)'],
    ],
];

$admin = dirname(__DIR__);
$symbol = 'BTCUSDT';
$from = '2026-08-02';
$to = '2026-09-16';
$activeTarget = '211';
$slice = null;
$warmupDays = 45;
$arms = ['B'];
$coreSlices = [];
$coreMaxOrder = '60';
foreach (array_slice($argv, 1) as $a) {
    if (str_starts_with($a, '--from=')) {
        $from = substr($a, 7);
    } elseif (str_starts_with($a, '--to=')) {
        $to = substr($a, 5);
    } elseif (str_starts_with($a, '--active=')) {
        $activeTarget = substr($a, 9);
    } elseif (str_starts_with($a, '--slice=')) {
        $slice = substr($a, 8);
    } elseif (str_starts_with($a, '--warmup=')) {
        $warmupDays = (int) substr($a, 9);
    } elseif (str_starts_with($a, '--arms=')) {
        $arms = explode(',', substr($a, 7)); // B (live), BE, BG, BR, BH, BRH, BX, BS, BXS — see trend-entry-sweep.php
    } elseif (str_starts_with($a, '--core=')) {
        $coreSlices = array_values(array_filter(array_map('trim', explode(',', substr($a, 7))), fn ($s) => $s !== ''));
    } elseif (str_starts_with($a, '--core-max-order=')) {
        $coreMaxOrder = substr($a, 17);
    } elseif ($a !== '' && $a[0] !== '-') {
        $symbol = strtoupper($a);
    }
}
$slice ??= $symbol === 'BNBUSDT' ? '450' : '300';
$fromTs = strtotime("{$from} 00:00:00 UTC");
$toTs = strtotime("{$to} 23:59:59 UTC");
$warmTs = $fromTs - $warmupDays * 86400;

// ── tape ────────────────────────────────────────────────────────────────
// the cache key carries the warm-up: the 45d tape the §12 runs used cannot
// serve a --warmup=250 core run (its EMA50 would read off 45 daily bars),
// and silently reusing it is how a replay measures a different rule than it
// prints. The legacy (45d) name is kept so the §12 numbers still reproduce.
$file = "{$admin}/tmp/replay-{$symbol}-15m-{$from}-{$to}" . ($warmupDays === 45 ? '' : "-w{$warmupDays}") . '.json';
if (is_file($file)) {
    $all = json_decode((string) file_get_contents($file), true);
} else {
    $gw = new BinanceGateway('https://api.binance.com', '', '');
    $all = [];
    $start = $warmTs * 1000;
    while ($start < $toTs * 1000) {
        $raw = $gw->publicGet('/api/v3/klines', ['symbol' => $symbol, 'interval' => '15m', 'limit' => 1000, 'startTime' => $start, 'endTime' => $toTs * 1000]);
        if (!is_array($raw) || $raw === []) {
            break;
        }
        foreach ($raw as $k) {
            $all[] = ['t' => (int) $k[0], 'high' => (string) $k[2], 'low' => (string) $k[3], 'close' => (string) $k[4]];
        }
        $start = (int) end($raw)[0] + 1;
        if (count($raw) < 1000) {
            break;
        }
        usleep(150000);
    }
    file_put_contents($file, json_encode($all));
}
if (count($all) < 96 * ($warmupDays + 7)) {
    fwrite(STDERR, "tape too short: " . count($all) . " bars\n");
    exit(1);
}

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
        // the EMA pair is part of the regime input, not decoration:
        // TrendRegime::decisiveDown reads it to tell a hairline 'down'
        // crossing from a real downtrend. Without these two keys the rule
        // fails closed and the replay silently measures the OLD gate.
        'ema20' => Indicators::ema($c, 20),
        'ema50' => Indicators::ema($c, 50),
        'adx14' => Indicators::adx($h, $l, $c),
        'er20' => Indicators::efficiencyRatio($c, 20),
        'chop14' => Indicators::choppiness($h, $l, $c, 14),
        'atr_pct' => count($c) > ATR_PERIOD ? Indicators::atr($h, $l, $c, ATR_PERIOD) / (float) end($c) : 0.0,
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
 * Per 1h bar: activator state (idle|active|winding_down), verdict, the
 * 4h summary (trend/adx/atr%) the grid anchor uses, and the 1d summary the
 * EmaCross1d core trades on (ema20/ema50 — same rolling buckets the gate
 * reads, so the core and the gate never disagree about the tape). Warm-up =
 * the bars before $firstDecision (indices < it never decide).
 * @return array<int, array{state:string, verdict:?string, s4:?array, s1:?array}>
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
        $sum4 = null;
        $sum1 = null;
        if ($i >= $firstDecision) {
            $p4 = ($i + 1) % 4;
            $j4 = intdiv($i + 1 - $p4, 4) - 1;
            $p1 = ($i + 1) % 24;
            $j1 = intdiv($i + 1 - $p1, 24) - 1;
            if ($j4 >= 30 && $j1 >= 30) {
                $b4 = array_slice($s4[$p4], max(0, $j4 + 1 - REGIME_WINDOW), min(REGIME_WINDOW, $j4 + 1));
                $b1 = array_slice($s1d[$p1], max(0, $j1 + 1 - REGIME_WINDOW), min(REGIME_WINDOW, $j1 + 1));
                $sum4 = summary4h($b4);
                $sum1 = summary1d($b1);
                $verdict = TrendRegime::classify($sum4, $sum1, $state === 'active');
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
        $out[$i] = ['state' => $state, 'verdict' => $verdict, 's4' => $sum4, 's1' => $sum1];
    }
    return $out;
}

function hstamp(array $bar): string
{
    return gmdate('m-d H:i', (int) ($bar['t'] / 1000));
}

// ── TREND arm ───────────────────────────────────────────────────────────
/**
 * @param array<int,array{high:string,low:string,close:string,t:int}> $h1
 * @param array<int,array{state:string,verdict:?string}> $regime
 */
function simulateTrend(array $h1, array $regime, int $first, string $activeTarget, bool $sellAtLoss, string $rule = 'B'): array
{
    $n = count($h1);
    $highs = array_column($h1, 'high');
    $lows = array_column($h1, 'low');
    $closes = array_column($h1, 'close');

    $pos = null;
    $stopOutAt = null;
    $realized = '0';
    $trades = [];
    $log = [];
    $equity = [];
    $inMarket = 0;
    $activeBars = 0;
    $armState = 'idle';
    $stopIgnored = 0;
    $lastExitHwm = null;
    $lastExitBar = null;
    $lastExitWin = false;
    $topUps = 0;
    $extVeto = str_contains($rule, 'E');
    $winCooldown = str_contains($rule, 'G');
    $freshHigh = str_contains($rule, 'R');
    $oneShot = str_contains($rule, 'H');
    $belowExit = str_contains($rule, 'X'); // X: regime re-entry needs close < the last exit fill
    $halfReentry = str_contains($rule, 'S'); // S: re-entries after a winning exit in the same activation are half size
    $lastExitFill = null;
    $winsThisActivation = 0;

    for ($i = $first; $i < $n; $i++) {
        $close = $closes[$i];
        $low = $lows[$i];
        $high = $highs[$i];
        $now = gmdate('Y-m-d H:i:s', (int) ($h1[$i]['t'] / 1000));

        $raw = $regime[$i]['state'];
        $wasActive = $armState === 'active';
        if ($raw === 'active') {
            if (!$wasActive) {
                $lastExitFill = null;
                $winsThisActivation = 0;
                $lastExitHwm = null;
            }
            $armState = 'active';
        } elseif ($raw === 'winding_down') {
            $armState = 'winding_down';
        } else {
            $armState = ($armState === 'winding_down' && $pos !== null) ? 'winding_down' : 'idle';
        }
        $target = $armState === 'active' ? $activeTarget : ($armState === 'winding_down' ? '0' : IDLE_TARGET);
        if ($armState === 'active') {
            $activeBars++;
        }

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
        $atr = Indicators::atr($ctxH, $ctxL, $ctxC, ATR_PERIOD);
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
                if ($sellAtLoss || bccomp($fill, $breakeven, SCALE) >= 0) {
                    $exitFee = bcmul(bcmul($pos['qty'], $fill, SCALE), FEE_PCT, SCALE);
                    $gross = bcmul(bcsub($fill, $pos['entry'], SCALE), $pos['qty'], SCALE);
                    $net = bcsub($gross, bcadd($pos['fee'], $exitFee, SCALE), SCALE);
                    $realized = bcadd($realized, $net, SCALE);
                    $trades[] = (float) $net;
                    $log[] = sprintf('  %s EXIT  @ %-12s %+8.2f  (held %dh, hwm %+.2f%%)', hstamp($h1[$i]), rtrim(rtrim(bcadd($fill, '0', 2), '0'), '.'), (float) $net, $i - $pos['bar'], ((float) $pos['hwm'] / (float) $pos['entry'] - 1) * 100);
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
                if (!$pos['ignored']) {
                    $stopIgnored++;
                    $pos['ignored'] = true;
                    $log[] = sprintf('  %s STOP IGNORED @ %-8s (below breakeven %s) — holding', hstamp($h1[$i]), bcadd($pos['stop'], '0', 2), bcadd($breakeven, '0', 2));
                }
            }
            if ($oneShot && $armState === 'active' && !$wasActive) {
                $short = bcsub($target, $pos['invested'], SCALE);
                if (bccomp($short, '5', SCALE) > 0) {
                    $qty = bcdiv($short, $close, SCALE);
                    $cost = bcadd(bcmul($pos['entry'], $pos['qty'], SCALE), $short, SCALE);
                    $pos['qty'] = bcadd($pos['qty'], $qty, SCALE);
                    $pos['entry'] = bcdiv($cost, $pos['qty'], SCALE);
                    $pos['fee'] = bcadd($pos['fee'], bcmul($short, FEE_PCT, SCALE), SCALE);
                    $pos['invested'] = bcadd($pos['invested'], $short, SCALE);
                    $topUps++;
                    $log[] = sprintf('  %s TOP-UP @ %-12s +%s USDT at activation (entry now %s)', hstamp($h1[$i]), bcadd($close, '0', 2), bcadd($short, '0', 0), bcadd($pos['entry'], '0', 2));
                }
            }
            continue;
        }

        if (bccomp($target, '0', SCALE) <= 0 || $atr <= 0) {
            continue;
        }
        if (!TrendEngine::cooldownElapsed($stopOutAt, COOLDOWN_BARS, '1h', $now)) {
            continue;
        }
        if ($winCooldown && $lastExitBar !== null && $lastExitWin && $i - $lastExitBar < 24) {
            continue;
        }
        if ($extVeto) {
            $m = array_slice($ctxC, -120);
            if ((float) $close > 1.04 * array_sum(array_map('floatval', $m)) / count($m)) {
                continue;
            }
        }
        $window = [];
        for ($k = 0; $k < count($ctxC); $k++) {
            $window[] = ['high' => $ctxH[$k], 'low' => $ctxL[$k], 'close' => $ctxC[$k]];
        }
        $signal = TrendEngine::entrySignal($window, DONCHIAN, EMA_FAST, EMA_SLOW);
        $regimeEntry = !$signal && $armState === 'active' && TrendEngine::regimeEntrySignal($window, EMA_FAST, EMA_SLOW);
        if ($regimeEntry && $freshHigh && $lastExitHwm !== null && bccomp($close, $lastExitHwm, SCALE) <= 0) {
            $regimeEntry = false;
        }
        if ($regimeEntry && $belowExit && $lastExitFill !== null && bccomp($close, $lastExitFill, SCALE) >= 0) {
            $regimeEntry = false; // X: buy back only under where the last position was sold
        }
        if (!$signal && !$regimeEntry) {
            continue;
        }
        $quote = $target;
        if ($halfReentry && $armState === 'active' && $winsThisActivation > 0) {
            $quote = bcdiv($target, '2', SCALE);
        }
        $qty = bcdiv($quote, $close, SCALE);
        $fee = bcmul($quote, FEE_PCT, SCALE);
        $stop = bcsub($close, TrendEngine::stopDistance($close, INITIAL_MULT, $atrS, STOP_FLOOR_PCT), SCALE);
        $pos = ['entry' => $close, 'qty' => $qty, 'hwm' => $close, 'stop' => $stop, 'fee' => $fee, 'bar' => $i, 'ignored' => false, 'invested' => $quote];

        // where did we buy? trailing 30d range position, vs 120h mean, next 72h path
        $lo30 = min(array_map('floatval', array_slice($lows, max(0, $i - 720), min(720, $i + 1))));
        $hi30 = max(array_map('floatval', array_slice($highs, max(0, $i - 720), min(720, $i + 1))));
        $pos30 = $hi30 > $lo30 ? ((float) $close - $lo30) / ($hi30 - $lo30) * 100 : 50.0;
        $m120 = array_sum(array_map('floatval', array_slice($closes, max(0, $i - 120), min(120, $i + 1)))) / min(120, $i + 1);
        $next = array_slice($h1, $i + 1, 72);
        $mx = $next ? max(array_map(fn ($b) => (float) $b['high'] / (float) $close - 1, $next)) * 100 : 0.0;
        $mn = $next ? min(array_map(fn ($b) => (float) $b['low'] / (float) $close - 1, $next)) * 100 : 0.0;
        $log[] = sprintf('  %s ENTRY @ %-12s %s  %s %6s USDT | 30d-range pos %3.0f%% | vs 120h mean %+5.2f%% | next 72h %+5.2f%% / %+5.2f%%',
            hstamp($h1[$i]), rtrim(rtrim(bcadd($close, '0', 2), '0'), '.'), $regimeEntry ? 'regime  ' : 'donchian', $armState, $quote, $pos30, ((float) $close / $m120 - 1) * 100, $mx, $mn);
    }

    $final = $realized;
    $bag = null;
    if ($pos !== null) {
        $unreal = bcmul(bcsub(end($closes), $pos['entry'], SCALE), $pos['qty'], SCALE);
        $final = bcadd($realized, bcsub($unreal, $pos['fee'], SCALE), SCALE);
        $bag = sprintf('%s @ %s (MTM %+.2f)', rtrim(rtrim($pos['qty'], '0'), '.'), bcadd($pos['entry'], '0', 2), (float) bcsub($unreal, $pos['fee'], SCALE));
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
        'entries' => count(array_filter($log, fn ($l) => str_contains($l, 'ENTRY'))),
        'stop_ignored' => $stopIgnored,
        'top_ups' => $topUps,
        'max_dd' => $maxDd,
        'tim' => ($n - $first) > 0 ? $inMarket / ($n - $first) * 100 : 0,
        'active' => ($n - $first) > 0 ? $activeBars / ($n - $first) * 100 : 0,
        'bag' => $bag,
        'log' => $log,
    ];
}

// ── CORE arm (EmaCross1d) ───────────────────────────────────────────────
/**
 * The live inventory core, machine-scaled (refit-evidence §14). One 1h pass
 * per bar, reading the SAME rolling 1d summary the gate reads:
 *   entry   flat ∧ 1d EMA20 > EMA50 ∧ (gated ? activator marker 'active' : true)
 *           → tranche 1 of TrendEngine::tranches(slice, 100, maxOrder)
 *   add     holding ∧ cross still up ∧ deploy > 0 ∧ TrendEngine::coreTrancheDue
 *           (pullback to ≤ 1.01 × the 1d EMA20, or 24h since the last)
 *   exit    the 1d cross DOWN → the whole position at the mark; with
 *           $sellAtLoss = false a fill under breakeven (entry × (1 + 2·fee))
 *           is refused and the position held, the exit re-arming above it
 * No stop, no trail — the core's only exit is its own cross (that is why the
 * gate had to move to the ENTRY side, §14).
 *
 * @param array<int,array{high:string,low:string,close:string,t:int}> $h1
 * @param array<int,array{state:string,verdict:?string,s1:?array}> $regime
 */
function simulateCore(array $h1, array $regime, int $first, string $slice, string $maxOrder, bool $gated, bool $sellAtLoss): array
{
    $n = count($h1);
    $closes = array_column($h1, 'close');
    $quotes = TrendEngine::tranches($slice, 100, $maxOrder);
    $total = count($quotes);

    $pos = null;
    $realized = '0';
    $trades = [];
    $log = [];
    $equity = [];
    $inMarket = 0;
    $entries = 0;
    $tranchesPlaced = 0;
    $heldSpells = 0;
    $gateBlocked = 0;   // bars where the cross was up, we were flat, and the gate said no

    for ($i = $first; $i < $n; $i++) {
        $close = $closes[$i];
        $now = gmdate('Y-m-d H:i:s', (int) ($h1[$i]['t'] / 1000));
        $s1 = $regime[$i]['s1'] ?? null;
        $up = TrendEngine::emaCross1dUp($s1);          // true | false | null (no verdict → never act)
        $ema20 = $s1['ema20'] ?? null;
        $active = !$gated || $regime[$i]['state'] === 'active';

        if ($pos === null) {
            $equity[] = (float) $realized;
        } else {
            $unreal = bcmul(bcsub($close, $pos['entry'], SCALE), $pos['qty'], SCALE);
            $equity[] = (float) bcadd($realized, bcsub($unreal, $pos['fee'], SCALE), SCALE);
            $inMarket++;
        }

        if ($pos !== null) {
            if ($up === false) {
                $breakeven = bcmul($pos['entry'], bcadd('1', bcmul('2', FEE_PCT, SCALE), SCALE), SCALE);
                if ($sellAtLoss || bccomp($close, $breakeven, SCALE) >= 0) {
                    $exitFee = bcmul(bcmul($pos['qty'], $close, SCALE), FEE_PCT, SCALE);
                    $gross = bcmul(bcsub($close, $pos['entry'], SCALE), $pos['qty'], SCALE);
                    $net = bcsub($gross, bcadd($pos['fee'], $exitFee, SCALE), SCALE);
                    $realized = bcadd($realized, $net, SCALE);
                    $trades[] = (float) $net;
                    $log[] = sprintf('  %s EXIT  @ %-12s %+8.2f  (1d cross down, held %dh, %d tranche(s), %s USDT invested)',
                        hstamp($h1[$i]), bcadd($close, '0', 2), (float) $net, $i - $pos['bar'], $pos['tranches'], bcadd($pos['invested'], '0', 0));
                    $pos = null;
                    continue;
                }
                if (!$pos['ignored']) {
                    $heldSpells++;
                    $pos['ignored'] = true;
                    $log[] = sprintf('  %s EXIT IGNORED @ %-8s (cross down, below breakeven %s) — holding (sell_at_loss OFF)',
                        hstamp($h1[$i]), bcadd($close, '0', 2), bcadd($breakeven, '0', 2));
                }
                continue; // cross is down: no new tranche while the exit is pending
            }
            $pos['ignored'] = false;
            if ($up === true && $active
                && TrendEngine::coreTrancheDue($pos['tranches'], $total, $close, $ema20 !== null ? (float) $ema20 : null, $pos['last_at'], $now)) {
                $quote = $quotes[$pos['tranches']];
                $qty = bcdiv($quote, $close, SCALE);
                $cost = bcadd(bcmul($pos['entry'], $pos['qty'], SCALE), $quote, SCALE);
                $pos['qty'] = bcadd($pos['qty'], $qty, SCALE);
                $pos['entry'] = bcdiv($cost, $pos['qty'], SCALE);
                $pos['fee'] = bcadd($pos['fee'], bcmul($quote, FEE_PCT, SCALE), SCALE);
                $pos['invested'] = bcadd($pos['invested'], $quote, SCALE);
                $pos['tranches']++;
                $pos['last_at'] = $now;
                $tranchesPlaced++;
                $log[] = sprintf('  %s TRANCHE %d/%d @ %-12s +%s USDT (%s) — entry(vwap) %s',
                    hstamp($h1[$i]), $pos['tranches'], $total, bcadd($close, '0', 2), bcadd($quote, '0', 0),
                    $ema20 !== null && (float) $close <= 1.01 * (float) $ema20 ? 'pullback to the 1d EMA20' : '24h spacing',
                    bcadd($pos['entry'], '0', 2));
            }
            continue;
        }

        if ($up !== true) {
            continue;
        }
        if (!$active) {
            $gateBlocked++;
            continue;
        }
        $quote = $quotes[0];
        $qty = bcdiv($quote, $close, SCALE);
        $pos = ['entry' => $close, 'qty' => $qty, 'fee' => bcmul($quote, FEE_PCT, SCALE), 'bar' => $i,
            'ignored' => false, 'invested' => $quote, 'tranches' => 1, 'last_at' => $now];
        $entries++;
        $tranchesPlaced++;
        $log[] = sprintf('  %s ENTRY @ %-12s tranche 1/%d %6s USDT | verdict %s',
            hstamp($h1[$i]), bcadd($close, '0', 2), $total, bcadd($quote, '0', 0), (string) ($regime[$i]['verdict'] ?? '-'));
    }

    $final = $realized;
    $bag = null;
    if ($pos !== null) {
        $unreal = bcmul(bcsub(end($closes), $pos['entry'], SCALE), $pos['qty'], SCALE);
        $final = bcadd($realized, bcsub($unreal, $pos['fee'], SCALE), SCALE);
        $bag = sprintf('%s @ %s (%s USDT in %d/%d tranches, MTM %+.2f)', rtrim(rtrim($pos['qty'], '0'), '.'),
            bcadd($pos['entry'], '0', 2), bcadd($pos['invested'], '0', 0), $pos['tranches'], $total, (float) bcsub($unreal, $pos['fee'], SCALE));
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
        'entries' => $entries,
        'tranches' => $tranchesPlaced,
        'tranche_total' => $total,
        'exits' => count($trades),
        'wins' => count(array_filter($trades, fn ($t) => $t > 0)),
        'held_spells' => $heldSpells,
        'gate_blocked' => $gateBlocked,
        'max_dd' => $maxDd,
        'tim' => ($n - $first) > 0 ? $inMarket / ($n - $first) * 100 : 0,
        'bag' => $bag,
        'log' => $log,
    ];
}

// ── GRID arm ────────────────────────────────────────────────────────────
/**
 * Mechanical ladder over the 1h tape from $first: anchor at the first bar,
 * re-anchor per MechanicalRefit::due (72h clock / outside range, no lower
 * re-anchor in a 4h down trend). Each segment is one Backtester run on a
 * barPath tape; segment-end unrealized is carried as MTM.
 * @param callable(array $s4): int $deployFor deploy_pct for the anchor given the 4h summary
 */
function simulateGrid(array $h1, array $regime, int $first, string $slice, callable $deployFor, array $extraCfg = []): array
{
    $n = count($h1);
    $halfWidth = bcdiv(MechanicalRefit::DEFAULT_HALF_WIDTH_PCT, '100', 12);
    $levels = MechanicalRefit::DEFAULT_LEVELS;
    $hours = MechanicalRefit::DEFAULT_HOURS;

    $segments = [];
    $i = $first;
    $active = null;
    $lastApplied = null;
    $realized = 0.0;
    $unrealCarried = 0.0;
    $cycles = 0;
    $fees = 0.0;
    $zeroBuyBars = 0;
    $anchors = 0;
    $held = 0;
    $log = [];
    while ($i < $n) {
        $price = $h1[$i]['close'];
        $now = gmdate('Y-m-d H:i:s', (int) ($h1[$i]['t'] / 1000));
        $s4 = $regime[$i]['s4'] ?? ['trend' => null, 'adx14' => null, 'atr_pct' => 0.0];
        if ($active === null) {
            $due = ['due' => true, 'reason' => 'first anchor'];
        } else {
            $due = MechanicalRefit::due($active, $price, $lastApplied, $now, $hours, $s4['trend'] ?? null);
        }
        if (!$due['due']) {
            $i++;
            continue;
        }
        // anchor here, run the segment until the next due bar
        $cand = MechanicalRefit::candidate($price, number_format((float) ($s4['atr_pct'] ?? 0), 8, '.', ''), $halfWidth, $levels);
        $deploy = $deployFor($s4);
        $anchors++;
        $lastApplied = $now;
        $active = ['p_low' => $cand['p_low'], 'p_high' => $cand['p_high']];
        // find segment end: first later bar where due() says yes
        $j = $i + 1;
        while ($j < $n) {
            $sj = $regime[$j]['s4'] ?? ['trend' => null];
            $d = MechanicalRefit::due($active, $h1[$j]['close'], $lastApplied, gmdate('Y-m-d H:i:s', (int) ($h1[$j]['t'] / 1000)), $hours, $sj['trend'] ?? null);
            if ($d['due']) {
                break;
            }
            if (str_contains($d['reason'], 'holding rather than')) {
                $held++;
            }
            $j++;
        }
        $seg = array_slice($h1, $i + 1, $j - $i - 1);
        $tape = DecisionScorer::barPath(array_map(fn ($b) => ['high' => $b['high'], 'low' => $b['low'], 'close' => $b['close']], $seg), $price);
        $budget = bcdiv(bcmul($slice, (string) $deploy, 8), '100', 8);
        $cfg = array_replace([
            'p_low' => $cand['p_low'], 'p_high' => $cand['p_high'], 'n_levels' => $cand['n_levels'],
            'spacing' => 'Geometric', 'allocation' => 'EqualQuote',
            'budget_quote' => $budget, 'fee_pct' => FEE_PCT, 'min_notional' => '5',
            'max_position_quote' => $slice, 'max_order_quote' => $slice, 'daily_loss_limit_quote' => $slice,
            'breakout_buffer_pct' => '0.02', 'breakout_policy' => 'HaltAndHold', 'max_open_orders' => 60,
        ], $extraCfg);
        $r = null;
        if (count($tape) >= 2 && (float) $budget >= 5 * ($cand['n_levels'] - 1)) {
            try {
                $r = Backtester::run($cfg, $tape);
            } catch (\InvalidArgumentException $e) {
                $r = null;
            }
        }
        $segReal = $r ? (float) $r['realized_pnl'] : 0.0;
        $segUnreal = $r ? (float) $r['unrealized_pnl'] : 0.0;
        $realized += $segReal;
        $unrealCarried += $segUnreal;
        $cycles += $r ? (int) $r['cycles'] : 0;
        $fees += $r ? (float) $r['fees_total'] : 0.0;
        if ($deploy <= 0) {
            $zeroBuyBars += count($seg);
        }
        $log[] = sprintf('  %s anchor [%s, %s]×%d sp %.2f%% deploy %3d  %s  → %3dh  real %+6.2f  unreal %+6.2f  cyc %2d  (%s)',
            hstamp($h1[$i]), $cand['p_low'], $cand['p_high'], $cand['n_levels'], (float) $cand['spacing_pct'] * 100, $deploy, $cand['widened'] ? 'ATR-widened' : '           ', count($seg), $segReal, $segUnreal, $r ? (int) $r['cycles'] : 0, $due['reason']);
        $segments[] = ['from' => $i, 'to' => $j];
        $i = $j;
    }
    return [
        'net' => $realized + $unrealCarried,
        'realized' => $realized,
        'unrealized' => $unrealCarried,
        'cycles' => $cycles,
        'fees' => $fees,
        'anchors' => $anchors,
        'held_lower' => $held,
        'log' => $log,
    ];
}

/**
 * Continuous mechanical ladder: like simulateGrid, but the inventory left
 * at a re-anchor is CARRIED as legacy lots (one lot per segment at the
 * segment's average cost, exit target = cost × (1 + rung spacing)) instead
 * of being marked, and the next ladder's budget is the daemon's
 * freeLadderQuote = min(deploy% × slice, slice − Σ legacy cost) — so the
 * replay starves exactly the way prod run 7 did (0 buy power once the slice
 * is all inventory). $capFrac < 1 = T5: never let Σ cost exceed capFrac ×
 * slice (leaves buy power for the lower rungs).
 */
function simulateGridCont(array $h1, array $regime, int $first, string $slice, callable $deployFor, float $capFrac = 1.0): array
{
    $n = count($h1);
    $halfWidth = bcdiv(MechanicalRefit::DEFAULT_HALF_WIDTH_PCT, '100', 12);
    $levels = MechanicalRefit::DEFAULT_LEVELS;
    $hours = MechanicalRefit::DEFAULT_HOURS;
    $lots = [];        // [{cost, qty, target}]
    $realized = 0.0;
    $fees = 0.0;
    $cycles = 0;
    $legacyExits = 0;
    $anchors = 0;
    $starvedBars = 0;  // bars with < 5 USDT of buy power
    $maxReserve = 0.0;
    $i = $first;
    $active = null;
    $lastApplied = null;
    $log = [];
    $walkLegacy = function (array $bars) use (&$lots, &$realized, &$fees, &$legacyExits): void {
        foreach ($bars as $b) {
            foreach ($lots as $k => $lot) {
                if ((float) $b['high'] >= $lot['target']) {
                    $proceeds = $lot['qty'] * $lot['target'];
                    $fee = $proceeds * (float) FEE_PCT;
                    $realized += $proceeds - $lot['cost'] - $fee;
                    $fees += $fee;
                    $legacyExits++;
                    unset($lots[$k]);
                }
            }
            $lots = array_values($lots);
        }
    };
    while ($i < $n) {
        $price = $h1[$i]['close'];
        $now = gmdate('Y-m-d H:i:s', (int) ($h1[$i]['t'] / 1000));
        $s4 = $regime[$i]['s4'] ?? ['trend' => null, 'adx14' => null, 'atr_pct' => 0.0];
        $due = $active === null ? ['due' => true, 'reason' => 'first anchor'] : MechanicalRefit::due($active, $price, $lastApplied, $now, $hours, $s4['trend'] ?? null);
        if (!$due['due']) {
            $i++;
            continue;
        }
        $cand = MechanicalRefit::candidate($price, number_format((float) ($s4['atr_pct'] ?? 0), 8, '.', ''), $halfWidth, $levels);
        $deploy = $deployFor($s4, $regime[$i]['state'] ?? null);
        $anchors++;
        $lastApplied = $now;
        $active = ['p_low' => $cand['p_low'], 'p_high' => $cand['p_high']];
        $j = $i + 1;
        while ($j < $n) {
            $sj = $regime[$j]['s4'] ?? ['trend' => null];
            $d = MechanicalRefit::due($active, $h1[$j]['close'], $lastApplied, gmdate('Y-m-d H:i:s', (int) ($h1[$j]['t'] / 1000)), $hours, $sj['trend'] ?? null);
            if ($d['due']) {
                break;
            }
            $j++;
        }
        $seg = array_slice($h1, $i + 1, $j - $i - 1);
        $reserve = array_sum(array_column($lots, 'cost'));
        $maxReserve = max($maxReserve, $reserve);
        $deployed = (float) $slice * $deploy / 100;
        $free = max(0.0, min($deployed, (float) $slice - $reserve, $capFrac * (float) $slice - $reserve));
        $r = null;
        $spacing = (float) $cand['spacing_pct'];
        if (count($seg) >= 1 && $free >= 5 * ($cand['n_levels'] - 1)) {
            $tape = DecisionScorer::barPath(array_map(fn ($b) => ['high' => $b['high'], 'low' => $b['low'], 'close' => $b['close']], $seg), $price);
            $cfg = [
                'p_low' => $cand['p_low'], 'p_high' => $cand['p_high'], 'n_levels' => $cand['n_levels'],
                'spacing' => 'Geometric', 'allocation' => 'EqualQuote',
                'budget_quote' => number_format($free, 8, '.', ''), 'fee_pct' => FEE_PCT, 'min_notional' => '5',
                'max_position_quote' => $slice, 'max_order_quote' => $slice, 'daily_loss_limit_quote' => $slice,
                'breakout_buffer_pct' => '0.02', 'breakout_policy' => 'HaltAndHold', 'max_open_orders' => 60,
            ];
            try {
                $r = Backtester::run($cfg, $tape);
            } catch (\InvalidArgumentException) {
                $r = null;
            }
        } else {
            $starvedBars += count($seg);
        }
        $walkLegacy($seg);
        $segReal = $r ? (float) $r['realized_pnl'] : 0.0;
        $realized += $segReal;
        $fees += $r ? (float) $r['fees_total'] : 0.0;
        $cycles += $r ? (int) $r['cycles'] : 0;
        $inv = $r ? (float) $r['ending_inventory'] : 0.0;
        if ($inv > 0) {
            $cost = $inv * (float) $r['ending_price'] - (float) $r['unrealized_pnl'];
            $lots[] = ['cost' => $cost, 'qty' => $inv, 'target' => $cost / $inv * (1 + $spacing)];
        }
        $log[] = sprintf('  %s anchor [%s, %s]×%d sp %.2f%% deploy %3d free %6.0f reserve %6.0f → %3dh real %+6.2f cyc %2d carry %6.0f  (%s)',
            hstamp($h1[$i]), $cand['p_low'], $cand['p_high'], $cand['n_levels'], $spacing * 100, $deploy, $free, $reserve, count($seg), $segReal, $r ? (int) $r['cycles'] : 0, $inv > 0 ? $cost : 0.0, $due['reason']);
        $i = $j;
    }
    $last = (float) end($h1)['close'];
    $unreal = 0.0;
    foreach ($lots as $lot) {
        $unreal += $lot['qty'] * $last - $lot['cost'];
    }
    return [
        'net' => $realized + $unreal, 'realized' => $realized, 'unrealized' => $unreal,
        'cycles' => $cycles + $legacyExits, 'legacy_exits' => $legacyExits, 'fees' => $fees, 'anchors' => $anchors,
        'starved_bars' => $starvedBars, 'max_reserve' => $maxReserve, 'lots' => count($lots), 'log' => $log,
    ];
}

// ── run ─────────────────────────────────────────────────────────────────
$off = count($all) % 4;
$h1 = aggRange($all, $off, count($all), 4);
$first = 0;
foreach ($h1 as $k => $b) {
    if ($b['t'] / 1000 >= $fromTs) {
        $first = $k;
        break;
    }
}
$t0 = microtime(true);
$regime = regimeStates($all, $off, count($h1), max(0, $first - 48));
$window = array_slice($h1, $first);
$activeH = count(array_filter(array_slice($regime, $first), fn ($r) => $r['state'] === 'active'));
$verdicts = array_count_values(array_map(fn ($r) => (string) $r['verdict'], array_slice($regime, $first)));
$p0 = (float) $window[0]['close'];
$pN = (float) end($window)['close'];
$hi = max(array_map('floatval', array_column($window, 'high')));
$lo = min(array_map('floatval', array_column($window, 'low')));
printf("═══ %s month replay %s → %s · %d×1h · %s → %s (%+.1f%%) hi %s lo %s · regime %.1fs ═══\n",
    $symbol, $from, $to, count($window), $p0, $pN, ($pN / $p0 - 1) * 100, $hi, $lo, microtime(true) - $t0);
printf("activator: active %d h (%.0f%%) · verdicts %s\n", $activeH, $activeH / count($window) * 100,
    implode(' ', array_map(fn ($k, $v) => "{$k}:{$v}", array_keys($verdicts), $verdicts)));
$hodlActive = (float) $activeTarget * ($pN / $p0 - 1);
$hodlSlice = (float) $slice * ($pN / $p0 - 1);
printf("HODL for scale: %s USDT → %+.2f · %s USDT → %+.2f · USDT 0\n\n", $activeTarget, $hodlActive, $slice, $hodlSlice);

// activation timeline
$prev = null;
$timeline = [];
for ($i = $first; $i < count($h1); $i++) {
    $s = $regime[$i]['state'];
    if ($s !== $prev) {
        $timeline[] = sprintf('%s %s @ %s', hstamp($h1[$i]), $s, bcadd($h1[$i]['close'], '0', 2));
        $prev = $s;
    }
}
echo "activator timeline: " . implode(' → ', $timeline) . "\n\n";

// ── TREND ──
echo "─── TREND arm (live engine: donchian ∨ regime entry, idle 25 / active {$activeTarget}) ───\n";
printf("%-6s %9s %9s %9s %7s %6s %5s %7s %8s %6s  %s\n", 'policy', 'net', 'realized', 'unreal', 'entries', 'exits', 'wins', 'ignored', 'maxDD', 'tim%', 'bag');
$trendRes = [];
foreach ($arms as $arm) {
    foreach (['hold' => false, 'stop' => true] as $label => $sal) {
        if ($sal && $arm !== $arms[0]) {
            continue; // stop policy only for the first arm — it loses everywhere
        }
        $r = simulateTrend($h1, $regime, $first, $activeTarget, $sal, $arm);
        $trendRes["{$arm}/{$label}"] = $r;
        printf("%-6s %+9.2f %+9.2f %+9.2f %7d %6d %5d %7d %8.2f %5.1f%%  %s%s\n", "{$arm}/" . substr($label, 0, 1), $r['net'], $r['realized'], $r['unrealized'], $r['entries'], $r['trades'], $r['wins'], $r['stop_ignored'], $r['max_dd'], $r['tim'], $r['bag'] ?? '-', $r['top_ups'] ? " (+{$r['top_ups']} top-up)" : '');
    }
}
$a = ACTUALS[$symbol]['trend'] ?? null;
if ($a) {
    printf("%-6s %+9.2f %+9.2f %+9.2f %7d %6s %5s %7s %8s %6s  %s\n", 'PROD', $a['realized'] + $a['unrealized'], $a['realized'], $a['unrealized'], $a['entries'], $a['stop_outs'], '-', '-', '-', '-', "run {$a['run']} slice {$a['slice']}: bag {$a['bag']}");
}
if (isset(ACTUALS[$symbol]['core'])) {
    $c = ACTUALS[$symbol]['core'];
    printf("%-6s %+9.2f %+9.2f %+9.2f %s\n", 'PROD', $c['realized'] + $c['unrealized'], $c['realized'], $c['unrealized'], "core run {$c['run']} slice {$c['slice']}: bag {$c['bag']}");
}
echo "\nentry / exit log ({$arms[0]} hold policy):\n" . implode("\n", $trendRes["{$arms[0]}/hold"]['log']) . "\n\n";

// ── CORE ──
if ($coreSlices) {
    echo "─── CORE arm (EmaCross1d: 1d EMA20>EMA50, gated entries, staggered tranches, max order {$coreMaxOrder}) ───\n";
    if ($warmupDays < 200) {
        printf("WARNING: warm-up %dd < 200 daily bars — the 1d EMA50 is a %d-bar mean here, not prod's EMA50. Re-run with --warmup=250.\n", $warmupDays, $warmupDays);
    }
    printf("%-14s %9s %9s %9s %7s %8s %5s %5s %7s %8s %6s  %s\n", 'arm/slice', 'net', 'realized', 'unreal', 'entries', 'tranches', 'exits', 'wins', 'held', 'maxDD', 'tim%', 'bag');
    foreach ($coreSlices as $k => $cs) {
        $variants = $k === 0
            ? ['gate/hold' => [true, false], 'gate/stop' => [true, true], 'cross/hold' => [false, false]]
            : ['gate/hold' => [true, false]];
        foreach ($variants as $label => [$g, $sal]) {
            $r = simulateCore($h1, $regime, $first, (string) $cs, $coreMaxOrder, $g, $sal);
            printf("%-14s %+9.2f %+9.2f %+9.2f %7d %5d/%-2d %5d %5d %7d %8.2f %5.1f%%  %s\n",
                "{$label} {$cs}", $r['net'], $r['realized'], $r['unrealized'], $r['entries'], $r['tranches'], $r['tranche_total'],
                $r['exits'], $r['wins'], $r['held_spells'], $r['max_dd'], $r['tim'], $r['bag'] ?? '-');
            if ($k === 0 && $label === 'gate/hold') {
                $coreLog = $r['log'];
            }
        }
        printf("%-14s %+9.2f  (HODL on the same notional)\n", "hodl {$cs}", (float) $cs * ($pN / $p0 - 1));
    }
    if (isset(ACTUALS[$symbol]['core'])) {
        $c = ACTUALS[$symbol]['core'];
        printf("%-14s %+9.2f %+9.2f %+9.2f   prod core run %d slice %s: bag %s (pre-gate engine)\n", 'PROD', $c['realized'] + $c['unrealized'], $c['realized'], $c['unrealized'], $c['run'], $c['slice'], $c['bag']);
    }
    echo "\ncore log (gate/hold, slice {$coreSlices[0]}):\n" . implode("\n", $coreLog ?? []) . "\n\n";
}

// ── GRID ──
echo "─── GRID arm (mechanical ±6%×6 / 72h, slice {$slice}) — segments reset (legacy lots marked at each re-anchor) ───\n";
printf("%-6s %9s %9s %9s %7s %7s %8s %9s\n", 'arm', 'net', 'realized', 'unreal', 'cycles', 'fees', 'anchors', 'heldLower');
$gridArms = [
    'd25' => fn (array $s4): int => 25,
    'd100' => fn (array $s4): int => 100,
    'gated' => fn (array $s4): int => RegimeGate::hostile($s4['trend'] ?? null, isset($s4['adx14']) ? (float) $s4['adx14'] : null) ? 25 : 100,
];
// lend (2026-09-23, §17): while the symbol's trend arm is active the grid
// keeps only its deploy-25 share (the other 75% is lent to the arm, which
// runs at --active + 0.75 × --slice); gated otherwise. Continuous arm only;
// decided at each anchor, so a segment that outlives the episode stays at 25.
$lendFn = fn (array $s4, ?string $state = null): int => $state === 'active' ? 25 : $gridArms['gated']($s4);
$gridRes = [];
foreach ($gridArms as $label => $fn) {
    $r = simulateGrid($h1, $regime, $first, $slice, $fn);
    $gridRes[$label] = $r;
    printf("%-6s %+9.2f %+9.2f %+9.2f %7d %7.2f %8d %9d\n", $label, $r['net'], $r['realized'], $r['unrealized'], $r['cycles'], $r['fees'], $r['anchors'], $r['held_lower']);
}
echo "\n─── GRID arm, CONTINUOUS (legacy lots carried as exits at cost + 1 rung; ladder budget = min(deploy×slice, slice − reserve)) ───\n";
printf("%-9s %9s %9s %9s %7s %7s %7s %8s %8s %5s\n", 'arm', 'net', 'realized', 'unreal', 'cycles', 'legacy', 'fees', 'starved%', 'maxRsv', 'lots');
$contArms = ['d25' => ['d25', 1.0], 'd100' => ['d100', 1.0], 'gated' => ['gated', 1.0], 'gated50' => ['gated', 0.5], 'd100c50' => ['d100', 0.5], 'd100c70' => ['d100', 0.7], 'lend' => ['lend', 1.0]];
foreach ($contArms as $label => [$base, $cap]) {
    $r = simulateGridCont($h1, $regime, $first, $slice, $base === 'lend' ? $lendFn : $gridArms[$base], $cap);
    $gridRes["cont:{$label}"] = $r;
    printf("%-9s %+9.2f %+9.2f %+9.2f %7d %7d %7.2f %7.1f%% %8.0f %5d\n", $label, $r['net'], $r['realized'], $r['unrealized'], $r['cycles'], $r['legacy_exits'], $r['fees'], $r['starved_bars'] / max(1, count($window)) * 100, $r['max_reserve'], $r['lots']);
}
$g = ACTUALS[$symbol]['grid'] ?? null;
if ($g) {
    printf("%-9s %+9.2f %+9.2f %+9.2f %7d %7s %7.2f   run %d slice %s deploy %s — %s\n", 'PROD', $g['realized'] + $g['unrealized'], $g['realized'], $g['unrealized'], $g['cycles'], '-', $g['fees'], $g['run'], $g['slice'], $g['deploy'], $g['note']);
}
echo "\nanchor log (continuous gated arm):\n" . implode("\n", $gridRes['cont:gated']['log']) . "\n";

echo "\nHonesty notes: entries at bar close, optimistic intrabar trail (ratchet off
the high before testing the low), hourly activator passes (live = 15 min),
end-aligned rolling 4h/1d buckets; grid segments mark legacy lots at the
segment end instead of carrying them as working sells (pessimistic vs prod);
the tape window starts {$from} while the prod actuals cover the runs' own
history inside the same 45d window (run 9 exists since 09-09, run 1's slice
changed 09-09), so PROD rows are context, not a like-for-like arm. The CORE
arm decides on the rolling 1d bucket that ENDS at each hourly pass (prod
reads a UTC daily summary refreshed hourly), fills tranches and its cross
exit at that bar's close, and needs --warmup=250 for a real 1d EMA50 — a
warm-up change moves the 1d half of the regime gate, so trend/grid numbers
from a 250d run are NOT the same series as the 45d §12 run.\n";
