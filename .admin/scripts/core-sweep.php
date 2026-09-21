<?php
/**
 * Inventory-floor ("core") backtest — does holding a base-asset core while
 * the 1d regime is up beat sitting in USDT, and by how much per $1k?
 *
 * The question behind it: the shared wallet sat ~95% USDT through a +30%
 * BTC leg (2026-08) because the grids are capped at 25% in strong tape
 * (RegimeGate) and the trend arm is a 100-USDT sentinel. A core is the
 * simplest bull-participation vehicle: buy X% of the budget as base when
 * the 1d signal turns up, sell it when the signal turns down, fee 0.1%
 * per side, otherwise do nothing. Everything scales linearly with X, so
 * the sim runs at 100% and reports per $1k — an operator picking a 30%
 * core reads 30% of the numbers.
 *
 * Signals (evaluated on the daily close, acted on at that close):
 *   reclaim   price > 1d EMA20 AND price > 1d EMA50   (the routine's re-entry gate / TrendRegime::oneDayUp)
 *   cross     EMA20 > EMA50                            (Indicators::trend up-family)
 *   reclaim3  reclaim held 3 consecutive closes to enter, lost 3 closes to exit (whipsaw damping)
 *   cross3    same damping on the cross signal
 *
 * Tapes: the strategy-gate 15m caches (tmp/gate-{SYMBOL}-15m-*.json — fetched
 * from Binance when missing), aggregated to UTC days. Reports net, max
 * drawdown, trades and the two benchmarks (HODL, USDT=0) per tape and per
 * signal. Read-only; nothing here touches the DB.
 *
 * CLI: php scripts/core-sweep.php [SYMBOL ...]   (default BTCUSDT BNBUSDT)
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Indicators;

const INTERVAL = '15m';
const FEE = 0.001;
const PER_DAY = 96;

$tapes = [
    'bull2024' => ['from' => '2023-11-01', 'to' => '2025-01-01'],
    'bear2026' => ['from' => '2025-10-01', 'to' => '2026-07-31'],
];
$symbols = array_slice($argv, 1) ?: ['BTCUSDT', 'BNBUSDT'];
$gw = new BinanceGateway('https://api.binance.com', '', '');

function tape(BinanceGateway $gw, string $symbol, string $from, string $to): array
{
    $cache = sprintf('%s/tmp/gate-%s-%s-%s-%s.json', dirname(__DIR__), $symbol, INTERVAL, $from, $to);
    if (is_file($cache)) {
        return json_decode((string) file_get_contents($cache), true);
    }
    $start = (new DateTimeImmutable($from . ' 00:00:00', new DateTimeZone('UTC')))->getTimestamp() * 1000;
    $end = (new DateTimeImmutable($to . ' 00:00:00', new DateTimeZone('UTC')))->getTimestamp() * 1000;
    $all = [];
    while ($start < $end) {
        $raw = $gw->publicGet('/api/v3/klines', [
            'symbol' => $symbol, 'interval' => INTERVAL, 'limit' => 1000,
            'startTime' => $start, 'endTime' => $end - 1,
        ]);
        if (!is_array($raw) || count($raw) === 0) {
            break;
        }
        foreach ($raw as $k) {
            $all[] = ['t' => (int) $k[0], 'high' => (string) $k[2], 'low' => (string) $k[3], 'close' => (string) $k[4]];
        }
        $start = ((int) end($raw)[0]) + 1;
        usleep(150000);
    }
    file_put_contents($cache, json_encode($all));
    return $all;
}

/** 15m → UTC daily closes (partial trailing day dropped). @return array<int,array{t:int,close:float}> */
function daily(array $c): array
{
    $out = [];
    $byDay = [];
    foreach ($c as $k) {
        $day = intdiv((int) $k['t'], 86400000);
        $byDay[$day] = (float) $k['close']; // last 15m close of the day wins
    }
    ksort($byDay);
    foreach ($byDay as $day => $close) {
        $out[] = ['t' => $day * 86400, 'close' => $close];
    }
    return $out;
}

/** @return bool[] per-day raw signal */
function signal(array $days, string $kind): array
{
    $closes = array_column($days, 'close');
    $out = [];
    foreach ($days as $i => $d) {
        if ($i < 50) {
            $out[] = false;
            continue;
        }
        $window = array_slice($closes, 0, $i + 1);
        $e20 = Indicators::ema($window, 20);
        $e50 = Indicators::ema($window, 50);
        $out[] = $kind === 'cross' ? $e20 > $e50 : ($d['close'] > $e20 && $d['close'] > $e50);
    }
    return $out;
}

/** Damp a raw signal: need $n consecutive trues to turn on, $n falses to turn off. */
function damp(array $raw, int $n): array
{
    $out = [];
    $state = false;
    $streak = 0;
    foreach ($raw as $v) {
        $streak = $v === $state ? 0 : $streak + 1;
        if ($streak >= $n) {
            $state = $v;
            $streak = 0;
        }
        $out[] = $state;
    }
    return $out;
}

/** Trade the signal with $1000; in base while on. @return array{net:float,dd:float,trades:int,days_in:int} */
function simulate(array $days, array $sig): array
{
    $usdt = 1000.0;
    $qty = 0.0;
    $peak = 1000.0;
    $dd = 0.0;
    $trades = 0;
    $daysIn = 0;
    foreach ($days as $i => $d) {
        $px = $d['close'];
        if ($sig[$i] && $qty === 0.0) {
            $qty = $usdt * (1 - FEE) / $px;
            $usdt = 0.0;
            $trades++;
        } elseif (!$sig[$i] && $qty > 0.0) {
            $usdt = $qty * $px * (1 - FEE);
            $qty = 0.0;
            $trades++;
        }
        if ($qty > 0.0) {
            $daysIn++;
        }
        $equity = $usdt + $qty * $px;
        $peak = max($peak, $equity);
        $dd = max($dd, ($peak - $equity) / $peak * 100);
    }
    $last = end($days)['close'];
    return ['net' => $usdt + $qty * $last - 1000.0, 'dd' => $dd, 'trades' => $trades, 'days_in' => $daysIn];
}

$variants = ['reclaim' => ['reclaim', 1], 'cross' => ['cross', 1], 'reclaim3' => ['reclaim', 3], 'cross3' => ['cross', 3]];
$agg = [];
foreach ($symbols as $symbol) {
    foreach ($tapes as $name => $t) {
        $days = daily(tape($gw, $symbol, $t['from'], $t['to']));
        $n = count($days);
        $hodl = ($days[$n - 1]['close'] / $days[50]['close'] - 1) * 1000.0; // from the first tradable day
        printf("═══ %s %s (%s → %s, %d days) — HODL from day 50: %+.0f/1k, USDT 0 ═══\n", $symbol, $name, $t['from'], $t['to'], $n, $hodl);
        printf("%-10s %10s %8s %7s %8s %10s\n", 'signal', 'net/1k', 'maxDD%', 'trades', 'days_in', 'vs HODL');
        foreach ($variants as $label => [$kind, $damp]) {
            $r = simulate($days, damp(signal($days, $kind), $damp));
            printf("%-10s %+10.0f %8.1f %7d %8d %+10.0f\n", $label, $r['net'], $r['dd'], $r['trades'], $r['days_in'], $r['net'] - $hodl);
            $agg[$label][] = $r['net'];
            $agg[$label . '_dd'][] = $r['dd'];
        }
        echo "\n";
    }
}
echo "═══ cross-tape summary (net/1k: min | mean; worst maxDD%) ═══\n";
foreach ($variants as $label => $_) {
    $nets = $agg[$label] ?? [];
    $dds = $agg[$label . '_dd'] ?? [];
    if ($nets === []) {
        continue;
    }
    printf("%-10s min %+7.0f  mean %+7.0f  worstDD %5.1f%%\n", $label, min($nets), array_sum($nets) / count($nets), max($dds));
}
echo "\nRead as: a core of X% of the shared budget earns X% of net/1k and carries X% of the drawdown on top of the grids'.\n";
