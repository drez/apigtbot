<?php
/**
 * Walk-forward WIDTH sweep — does a NARROW re-centering grid (capital
 * concentrated on few levels around price) beat the wide "coverage" grid?
 *
 * Live evidence for the theory: prod run 1 (BTC, 3 levels ≈ ±2.7%) earns
 * ~8.1 net/$1k/day while the wide grids earn 1–2; on the old dense grids
 * levels 3–14 almost never cycled (cycles concentrate at the levels price
 * actually touches). Concentration's cost is chasing: a narrow grid exits
 * its range more often, and each re-center here REALIZES mark-to-market and
 * re-buys the ladder (live carries legacy exits, strictly better — so the
 * recenter rows are penalized, not flattered).
 *
 * Protocol (same family as spacing-sweep/deploy-policy-sweep): real 15m
 * klines ~187d (symbol = argv[1], default BTCUSDT); 14d fit / 7d test /
 * 3.5d step. Spacing FIXED at the proven 1.7% geometric, EqualQuote,
 * budget 1000, fee 0.10%. Candidates: half-width = k × fit-window 4h ATR%
 * for k ∈ {1, 2, 4, 6}, each run two ways:
 *   static    one grid for the whole 7d window, HaltAndHold on breakout
 *             (the protocol every prior sweep used)
 *   recenter  when a close exits [p_low, p_high]: book realized + MTM at
 *             the exit print, re-center a fresh grid there, continue
 * Score: net (realized + MTM at window end) per window; regime split by
 * fit-window ADX and trend family.
 */

require '/path/to/apigtbot/.admin/vendor/autoload.php';

use App\Domains\Bot\Backtester;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Indicators;

const INTERVAL = '15m';
const BATCHES = 18;            // 18 × 1000 × 15m ≈ 187 days
const FIT = 1344;              // 14d of 15m candles
const TEST = 672;              // 7d
const STEP = 336;              // 3.5d
const SPACING = 1.7;           // % per grid — the spacing-sweep winner, held fixed
$WIDTHS = [1, 2, 4, 6];        // half-width = k × 4h ATR%

$symbol = strtoupper((string) ($argv[1] ?? 'BTCUSDT'));

$gw = new BinanceGateway('https://api.binance.com', '', '');

// ── fetch with endTime pagination (oldest→newest) ────────────────────────
$all = [];
$endTime = null;
for ($b = 0; $b < BATCHES; $b++) {
    $params = ['symbol' => $symbol, 'interval' => INTERVAL, 'limit' => 1000];
    if ($endTime !== null) {
        $params['endTime'] = $endTime;
    }
    $raw = $gw->publicGet('/api/v3/klines', $params);
    if (!is_array($raw) || count($raw) === 0) {
        break;
    }
    $batch = [];
    foreach ($raw as $k) {
        $batch[] = ['t' => (int) $k[0], 'high' => (string) $k[2], 'low' => (string) $k[3], 'close' => (string) $k[4]];
    }
    $all = array_merge($batch, $all);
    $endTime = $batch[0]['t'] - 1;
    usleep(150000);
}
printf("%s candles: %d (%s → %s)\n\n", $symbol, count($all), date('Y-m-d', $all[0]['t'] / 1000), date('Y-m-d', end($all)['t'] / 1000));

/** Aggregate 15m candles into buckets of $k (same as deploy-policy-sweep). */
function aggregate(array $c, int $k, int $keep = 300): array
{
    $from = max(0, count($c) - $k * $keep);
    $out = [];
    for ($i = $from; $i + $k <= count($c); $i += $k) {
        $hi = -INF;
        $lo = INF;
        for ($j = $i; $j < $i + $k; $j++) {
            $hi = max($hi, (float) $c[$j]['high']);
            $lo = min($lo, (float) $c[$j]['low']);
        }
        $out[] = ['high' => $hi, 'low' => $lo, 'close' => (float) $c[$i + $k - 1]['close']];
    }
    return $out;
}

/** One Backtester run over a tape seeded at $price0; null if config rejected. */
function bt(float $price0, float $halfPct, array $tape): ?array
{
    $pLow = $price0 * (1 - $halfPct / 100);
    $pHigh = $price0 * (1 + $halfPct / 100);
    $n = max(2, min(40, (int) round(log($pHigh / $pLow) / log(1 + SPACING / 100))));
    $cfg = [
        'p_low' => sprintf('%.8f', $pLow), 'p_high' => sprintf('%.8f', $pHigh), 'n_levels' => $n,
        'spacing' => 'Geometric', 'allocation' => 'EqualQuote',
        'budget_quote' => '1000', 'fee_pct' => '0.001',
        'max_position_quote' => '1000000', 'max_order_quote' => '1000000',
        'daily_loss_limit_quote' => '1000000',
        // wide buffer: segmentation (recenter) / the static row's own 2% halt
        // is applied by the caller via this knob
        'breakout_buffer_pct' => '0.02', 'breakout_policy' => 'HaltAndHold',
        'max_open_orders' => 60, 'min_notional' => '5',
    ];
    array_unshift($tape, sprintf('%.8f', $price0));
    try {
        $r = Backtester::run($cfg, $tape);
    } catch (\InvalidArgumentException $e) {
        return null;
    }
    return [
        'net' => (float) $r['realized_pnl'] + (float) $r['unrealized_pnl'],
        'realized' => (float) $r['realized_pnl'],
        'cycles' => (int) $r['cycles'],
        'fees' => (float) $r['fees_total'],
        'levels' => $n,
    ];
}

/** Segmented run: re-center on the first close outside the range.
 *  $upOnly (trail mode): re-center on UP exits only — an up-exit books
 *  realized cycle profit and re-arms higher; a DOWN exit stops chasing and
 *  runs the rest of the tape statically on the current grid (halt-and-hold),
 *  exactly like the static row would. */
function btRecenter(float $price0, float $halfPct, array $tape, bool $upOnly = false): array
{
    $acc = ['net' => 0.0, 'realized' => 0.0, 'cycles' => 0, 'fees' => 0.0, 'levels' => 0, 'recenters' => 0];
    $p0 = $price0;
    $i = 0;
    $nTape = count($tape);
    while ($i < $nTape) {
        $pLow = $p0 * (1 - $halfPct / 100);
        $pHigh = $p0 * (1 + $halfPct / 100);
        $end = $nTape;
        $exitDown = false;
        for ($j = $i; $j < $nTape; $j++) {
            $p = (float) $tape[$j];
            if ($p < $pLow || $p > $pHigh) {
                $end = $j;
                $exitDown = $p < $pLow;
                break;
            }
        }
        if ($upOnly && $exitDown) {
            // stop chasing: ride the current grid to the end of the window
            $seg = array_slice($tape, $i);
            $r = bt($p0, $halfPct, $seg);
            if ($r !== null) {
                $acc['net'] += $r['net'];
                $acc['realized'] += $r['realized'];
                $acc['cycles'] += $r['cycles'];
                $acc['fees'] += $r['fees'];
                $acc['levels'] = $r['levels'];
            }
            break;
        }
        // segment INCLUDES the exit print so MTM books at the price that
        // forced the re-center, not the last friendly in-range close
        $seg = array_slice($tape, $i, max(1, $end - $i + ($end < $nTape ? 1 : 0)));
        if ($seg !== []) {
            $r = bt($p0, $halfPct, $seg);
            if ($r !== null) {
                $acc['net'] += $r['net'];
                $acc['realized'] += $r['realized'];
                $acc['cycles'] += $r['cycles'];
                $acc['fees'] += $r['fees'];
                $acc['levels'] = $r['levels'];
            }
        }
        if ($end >= $nTape) {
            break;
        }
        $p0 = (float) $tape[$end];
        $i = $end + 1;
        $acc['recenters']++;
    }
    return $acc;
}

/** up/down/side trend family. */
function fam(string $t): string
{
    return in_array($t, ['strong_up', 'up'], true) ? 'up'
        : (in_array($t, ['strong_down', 'down'], true) ? 'down' : 'side');
}

// ── walk-forward ─────────────────────────────────────────────────────────
$results = [];   // "k/static"|"k/recenter" => rows
$windows = [];
for ($start = 0; $start + FIT + TEST <= count($all); $start += STEP) {
    $fitC = array_slice($all, $start, FIT);
    $testC = array_slice($all, $start + FIT, TEST);
    $a4h = aggregate($fitC, 16);
    $c4 = array_column($a4h, 'close');
    $price0 = (float) end($fitC)['close'];
    $atr4 = Indicators::atr(array_column($a4h, 'high'), array_column($a4h, 'low'), $c4);
    $atr4Pct = $price0 > 0 ? $atr4 / $price0 * 100 : 0;
    $adx4 = Indicators::adx(array_column($a4h, 'high'), array_column($a4h, 'low'), $c4);
    $trend = fam(Indicators::trend($c4));
    $windows[] = ['adx' => $adx4, 'trend' => $trend];
    $tape = array_map(static fn ($c) => (string) $c['close'], $testC);

    foreach ($WIDTHS as $k) {
        $half = max($k * $atr4Pct, SPACING); // floor: at least a 2-level ladder
        $meta = ['adx' => $adx4, 'trend' => $trend, 'half' => $half];

        $r = bt($price0, $half, $tape);
        if ($r !== null) {
            $results["$k/static"][] = $r + $meta + ['recenters' => 0];
        }
        $results["$k/recenter"][] = btRecenter($price0, $half, $tape) + $meta;
        $results["$k/trail"][] = btRecenter($price0, $half, $tape, true) + $meta;
    }
}
$adxs = array_map(fn ($w) => $w['adx'], $windows);
sort($adxs);
printf("windows: %d · median 4h ADX %.0f · trends: up %d / side %d / down %d\n\n",
    count($windows), $adxs[intdiv(count($adxs), 2)] ?? 0,
    count(array_filter($windows, fn ($w) => $w['trend'] === 'up')),
    count(array_filter($windows, fn ($w) => $w['trend'] === 'side')),
    count(array_filter($windows, fn ($w) => $w['trend'] === 'down')));

// ── report ───────────────────────────────────────────────────────────────
printf("%-12s %9s %9s %9s %6s %8s %8s %7s %7s %6s\n",
    'variant', 'mean net', 'median', 'worst', 'win%', 'cycles/w', 'fees/w', 'half%', 'lvls', 'rec/w');
foreach ($WIDTHS as $k) {
    foreach (['static', 'recenter', 'trail'] as $mode) {
        $rows = $results["$k/$mode"] ?? [];
        if (!$rows) {
            continue;
        }
        $nets = array_column($rows, 'net');
        sort($nets);
        printf("%-12s %9.2f %9.2f %9.2f %5.0f%% %8.1f %8.2f %7.2f %7.1f %6.1f\n",
            "{$k}xATR/$mode",
            array_sum($nets) / count($nets),
            $nets[intdiv(count($nets), 2)],
            $nets[0],
            count(array_filter($nets, fn ($n) => $n > 0)) / count($nets) * 100,
            array_sum(array_column($rows, 'cycles')) / count($rows),
            array_sum(array_column($rows, 'fees')) / count($rows),
            array_sum(array_column($rows, 'half')) / count($rows),
            array_sum(array_column($rows, 'levels')) / count($rows),
            array_sum(array_column($rows, 'recenters')) / count($rows));
    }
}

// regime splits: mean net by 4h ADX band and by fit-trend family
echo "\n═══ regime split — mean net (n) ═══\n";
printf("%-12s %16s %16s %14s %14s %14s\n", 'variant', 'ADX<25', 'ADX>=25', 'up', 'side', 'down');
$cell = static function (array $rows): string {
    return $rows ? sprintf('%.2f (%d)', array_sum(array_column($rows, 'net')) / count($rows), count($rows)) : '—';
};
foreach ($WIDTHS as $k) {
    foreach (['static', 'recenter', 'trail'] as $mode) {
        $rows = $results["$k/$mode"] ?? [];
        if (!$rows) {
            continue;
        }
        printf("%-12s %16s %16s %14s %14s %14s\n", "{$k}xATR/$mode",
            $cell(array_filter($rows, fn ($r) => $r['adx'] < 25)),
            $cell(array_filter($rows, fn ($r) => $r['adx'] >= 25)),
            $cell(array_filter($rows, fn ($r) => $r['trend'] === 'up')),
            $cell(array_filter($rows, fn ($r) => $r['trend'] === 'side')),
            $cell(array_filter($rows, fn ($r) => $r['trend'] === 'down')));
    }
}

echo "\nHonesty notes: perfect fills flatter every row equally; re-center books
MTM at the exit print while live carries legacy exits (recenter rows are
CONSERVATIVE); static rows halt-and-hold at 2% beyond the floor exactly like
the prior sweeps; spacing held at 1.7% so width is the only variable.\n";
