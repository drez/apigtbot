<?php
/**
 * Walk-forward REGIME-SIGNAL sweep — would throttling the grid on a fit-time
 * regime signal have beaten always-on? The width sweep settled geometry
 * (wide static 4–6×ATR); what still loses money is holding inventory through
 * hostile (trending-down) windows. This sweep measures, per candidate signal,
 * whether the signal AT FIT TIME predicts the window's grid net — and scores
 * simple skip-gates against the ungated baseline.
 *
 * Protocol (same family as width/spacing/deploy-policy sweeps): real 15m
 * klines ~187d (symbol = argv[1], default BTCUSDT); 14d fit / 7d test /
 * 3.5d step; spacing FIXED 1.7% geometric, EqualQuote, budget 1000, fee
 * 0.10%; static HaltAndHold grids at 4× and 6× fit-window 4h ATR% half-width
 * (the proven winners). One Backtester run per window per width; every gate
 * is then arithmetic over the same baseline nets (skipped window = capital
 * idle = 0 net), so all gates are judged on identical tapes.
 *
 * Signals (computed on the 4h aggregate of the fit window, at fit end):
 *   adx14, trend family, Kaufman ER(20), Choppiness(14), RSI(14),
 *   ATR%-rank, price stretch vs EMA50 in ATRs, and the perp funding-rate
 *   percentile vs its trailing 30d (funding history is fetched once from
 *   fapi and aligned by timestamp — point-in-time, no lookahead).
 *
 * Output: (1) quartile table — mean baseline net by signal quartile: a
 * signal with no monotonic relation has no business gating anything;
 * (2) gate table — mean/median/worst/win% with skips counted as 0, plus
 * "avoided" = mean baseline net of exactly the windows the gate skipped
 * (a good gate avoids strongly negative windows; a bad one skips winners).
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
const SPACING = 1.7;           // % per grid — spacing-sweep winner, held fixed
$WIDTHS = [4, 6];              // half-width = k × fit-window 4h ATR% (width-sweep winners)

$symbol = strtoupper((string) ($argv[1] ?? 'BTCUSDT'));
// 2026-09-05: `--tape=bull|bear` replays a cached long tape
// (tmp/gate-{SYMBOL}-15m-*.json, see strategy-gate.php) instead of the
// live ~187d fetch — the 2026-08-11 run had ZERO bull windows, so the
// down|adx>=30 gate it picked was never scored on a tape where a grid
// sits in a 1d-aligned uptrend (BNB 2026-09: 4h ADX >= 30 for weeks in a
// +30% month, grid pinned at deploy 25). Funding gates sit out in tape mode.
$TAPES = ['bull' => ['2023-11-01', '2025-01-01'], 'bear' => ['2025-10-01', '2026-07-31']];
$tapeName = null;
foreach ($argv as $a) {
    if (str_starts_with((string) $a, '--tape=')) {
        $tapeName = substr((string) $a, 7);
    }
}

$gw = new BinanceGateway('https://api.binance.com', '', '');

// ── fetch klines with endTime pagination (oldest→newest) ─────────────────
$all = [];
$endTime = null;
if ($tapeName !== null) {
    [$from, $to] = $TAPES[$tapeName] ?? throw new \RuntimeException("unknown tape {$tapeName}");
    $file = dirname(__DIR__) . "/tmp/gate-{$symbol}-15m-{$from}-{$to}.json";
    $all = json_decode((string) file_get_contents($file), true) ?: throw new \RuntimeException("missing tape {$file}");
}
for ($b = 0; $b < BATCHES && $tapeName === null; $b++) {
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
printf("%s candles: %d (%s → %s)\n", $symbol, count($all), date('Y-m-d', $all[0]['t'] / 1000), date('Y-m-d', end($all)['t'] / 1000));

// ── fetch perp funding history (8h prints, 1000 ≈ 333d — covers the tape) ─
$funding = []; // [['t' => ms, 'r' => rate], …] oldest→newest
try {
    if ($tapeName !== null) {
        throw new \RuntimeException('tape mode');
    }
    $fut = new BinanceGateway((string) env('GTBOT_FUTURES_BASE', 'https://fapi.binance.com'), '', '');
    $raw = $fut->publicGet('/fapi/v1/fundingRate', ['symbol' => $symbol, 'limit' => 1000]);
    foreach ((array) $raw as $r) {
        if (isset($r['fundingTime'], $r['fundingRate'])) {
            $funding[] = ['t' => (int) $r['fundingTime'], 'r' => (float) $r['fundingRate']];
        }
    }
    usort($funding, static fn ($a, $b) => $a['t'] <=> $b['t']);
} catch (\Throwable $e) {
    echo "funding history unavailable ({$e->getMessage()}) — funding gates will sit out\n";
}
printf("funding prints: %d\n\n", count($funding));

/** Percentile (0..100) of the funding rate in force at $ms vs its trailing 30d; null if <30 prints. */
function fundingPctile(array $funding, int $ms): ?float
{
    $upto = array_values(array_filter($funding, static fn ($f) => $f['t'] <= $ms));
    if (count($upto) < 30) {
        return null;
    }
    $trail = array_slice($upto, -90); // 90 × 8h = 30d
    $cur = end($trail)['r'];
    $le = count(array_filter($trail, static fn ($f) => $f['r'] <= $cur + 1e-12));
    return round($le / count($trail) * 100, 1);
}

/** Aggregate 15m candles into buckets of $k (same as width-sweep). */
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

/** One static HaltAndHold Backtester run seeded at $price0; null if config rejected. */
function bt(float $price0, float $halfPct, array $tape): ?float
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
        'breakout_buffer_pct' => '0.02', 'breakout_policy' => 'HaltAndHold',
        'max_open_orders' => 60, 'min_notional' => '5',
    ];
    array_unshift($tape, sprintf('%.8f', $price0));
    try {
        $r = Backtester::run($cfg, $tape);
    } catch (\InvalidArgumentException) {
        return null;
    }
    return (float) $r['realized_pnl'] + (float) $r['unrealized_pnl'];
}

/** up/down/side trend family. */
function fam(string $t): string
{
    return in_array($t, ['strong_up', 'up'], true) ? 'up'
        : (in_array($t, ['strong_down', 'down'], true) ? 'down' : 'side');
}

// ── walk-forward: baseline nets + fit-time signals per window ────────────
$windows = [];
for ($start = 0; $start + FIT + TEST <= count($all); $start += STEP) {
    $fitC = array_slice($all, $start, FIT);
    $testC = array_slice($all, $start + FIT, TEST);
    $a4h = aggregate($fitC, 16);
    $h4 = array_column($a4h, 'high');
    $l4 = array_column($a4h, 'low');
    $c4 = array_column($a4h, 'close');
    $price0 = (float) end($fitC)['close'];
    $atr4 = Indicators::atr($h4, $l4, $c4);
    $atr4Pct = $price0 > 0 ? $atr4 / $price0 * 100 : 0;
    $ema50 = Indicators::ema($c4, 50);
    // 1d alignment at fit end (TrendRegime::oneDayUp: 1d up-family label OR
    // price above both the 1d EMA20 and EMA50), from the FULL history up to
    // fit end aggregated to daily buckets — the same input the activator reads
    $a1d = aggregate(array_slice($all, 0, $start + FIT), 96, 300);
    $c1d = array_column($a1d, 'close');
    $s1d = ['trend' => Indicators::trend($c1d), 'price' => $price0, 'ema20' => Indicators::ema($c1d, 20), 'ema50' => Indicators::ema($c1d, 50)];

    $w = [
        't' => (int) end($fitC)['t'],
        'adx' => Indicators::adx($h4, $l4, $c4),
        'trend' => fam(Indicators::trend($c4)),
        'er' => Indicators::efficiencyRatio($c4, 20),
        'chop' => Indicators::choppiness($h4, $l4, $c4, 14),
        'rsi' => Indicators::rsi($c4, 14),
        'atr_rank' => Indicators::atrPctRank($h4, $l4, $c4, 14),
        'stretch' => $atr4 > 0 ? ($price0 - $ema50) / $atr4 : null, // ATRs above(+)/below(−) 4h EMA50
        'fund_pct' => fundingPctile($funding, (int) end($fitC)['t']),
        '1d_up' => count($c1d) >= 30 && \App\Domains\Bot\TrendRegime::oneDayUp($s1d),
        'net' => [],
    ];
    $tape = array_map(static fn ($c) => (string) $c['close'], $testC);
    foreach ($GLOBALS['WIDTHS'] as $k) {
        $half = max($k * $atr4Pct, SPACING);
        $w['net'][$k] = bt($price0, $half, $tape);
    }
    $windows[] = $w;
}
$nW = count($windows);
printf("windows: %d · trends: up %d / side %d / down %d · 1d_up %d\n\n", $nW,
    count(array_filter($windows, fn ($w) => $w['trend'] === 'up')),
    count(array_filter($windows, fn ($w) => $w['trend'] === 'side')),
    count(array_filter($windows, fn ($w) => $w['trend'] === 'down')),
    count(array_filter($windows, fn ($w) => $w['1d_up'])));

// ── (1) signal → outcome quartile table (baseline = 4×ATR nets) ──────────
$SIGNALS = ['adx', 'er', 'chop', 'rsi', 'atr_rank', 'stretch', 'fund_pct'];
echo "═══ signal quartiles — mean 4xATR net (n) · Q1 = lowest signal values ═══\n";
printf("%-9s %15s %15s %15s %15s %6s\n", 'signal', 'Q1', 'Q2', 'Q3', 'Q4', 'null');
foreach ($SIGNALS as $sig) {
    $rows = array_values(array_filter($windows, fn ($w) => $w[$sig] !== null && $w['net'][4] !== null));
    $nulls = $nW - count($rows);
    usort($rows, fn ($a, $b) => $a[$sig] <=> $b[$sig]);
    $cells = [];
    foreach (array_chunk($rows, max(1, (int) ceil(count($rows) / 4))) as $q) {
        $nets = array_map(fn ($w) => $w['net'][4], $q);
        $cells[] = sprintf('%.2f (%d)', array_sum($nets) / count($nets), count($nets));
    }
    printf("%-9s %15s %15s %15s %15s %6d\n", $sig, $cells[0] ?? '—', $cells[1] ?? '—', $cells[2] ?? '—', $cells[3] ?? '—', $nulls);
}

// ── (2) gate table — skip window when predicate true (null = trade) ──────
$GATES = [
    'none (baseline)' => fn ($w) => false,
    'adx>=25'         => fn ($w) => $w['adx'] >= 25,
    'adx>=30'         => fn ($w) => $w['adx'] >= 30,
    'er>=0.30'        => fn ($w) => $w['er'] !== null && $w['er'] >= 0.30,
    'er>=0.40'        => fn ($w) => $w['er'] !== null && $w['er'] >= 0.40,
    'chop<=38.2'      => fn ($w) => $w['chop'] !== null && $w['chop'] <= 38.2,
    'chop<=45'        => fn ($w) => $w['chop'] !== null && $w['chop'] <= 45,
    'trend=down'      => fn ($w) => $w['trend'] === 'down',
    'down|adx>=30'    => fn ($w) => $w['trend'] === 'down' || $w['adx'] >= 30,
    'G2 down|adx30&!1dup' => fn ($w) => $w['trend'] === 'down' || ($w['adx'] >= 30 && !$w['1d_up']),
    '1d_up only'      => fn ($w) => !$w['1d_up'],
    'rsi<=40'         => fn ($w) => $w['rsi'] <= 40,
    'atr_rank>=90'    => fn ($w) => $w['atr_rank'] !== null && $w['atr_rank'] >= 90,
    'stretch<=-1'     => fn ($w) => $w['stretch'] !== null && $w['stretch'] <= -1,
    'fund_pct<=10'    => fn ($w) => $w['fund_pct'] !== null && $w['fund_pct'] <= 10,
    'fund_pct>=90'    => fn ($w) => $w['fund_pct'] !== null && $w['fund_pct'] >= 90,
];
foreach ($GLOBALS['WIDTHS'] as $k) {
    echo "\n═══ gates @ {$k}xATR static — skipped window = 0 net ═══\n";
    printf("%-22s %9s %9s %9s %6s %6s %10s\n", 'gate', 'mean net', 'median', 'worst', 'win%', 'skip', 'avoided');
    foreach ($GATES as $name => $pred) {
        $nets = [];
        $avoided = [];
        foreach ($windows as $w) {
            if ($w['net'][$k] === null) {
                continue;
            }
            if ($pred($w)) {
                $nets[] = 0.0;
                $avoided[] = $w['net'][$k];
            } else {
                $nets[] = $w['net'][$k];
            }
        }
        if (!$nets) {
            continue;
        }
        $sorted = $nets;
        sort($sorted);
        printf("%-22s %9.2f %9.2f %9.2f %5.0f%% %6d %10s\n",
            $name,
            array_sum($nets) / count($nets),
            $sorted[intdiv(count($sorted), 2)],
            $sorted[0],
            count(array_filter($nets, fn ($n) => $n > 0)) / count($nets) * 100,
            count($avoided),
            $avoided ? sprintf('%.2f', array_sum($avoided) / count($avoided)) : '—');
    }
}

echo "\nHonesty notes: perfect fills flatter every row equally; a skipped
window earns 0 (idle capital), so a gate only wins if the windows it skips
were net-negative on the SAME tape; signals are fit-window-only (no
lookahead); funding percentile is point-in-time vs its trailing 30d; gates
with skip=0 are inert on this tape and prove nothing either way.\n";
