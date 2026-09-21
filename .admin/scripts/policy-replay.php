<?php
/**
 * Policy replay — "the backtest is the live system", one level up.
 *
 * strategy-gate.php backtests the ENGINE under fixed deploy policies. This
 * harness backtests the REFIT LAYER itself: at every decision step it feeds
 * the exact server-side functions the hourly routine's brief is built from
 * (Indicators::summary → RoutineBrief::regime → deployBand → candidate,
 * i.e. RangeFitter envelopes picked by in-sample Backtester) with data up to
 * t only, applies the candidate mechanically, and scores the next 7d through
 * the real Backtester on the same two regime tapes (2024 bull, 2025/26 bear).
 *
 *   php scripts/policy-replay.php [SYMBOL] [PROFILE]     (BTCUSDT Balanced)
 *
 * The arms differ only in how the deploy band is read:
 *   cand_hi     follow the candidate, deploy at the band's top
 *   cand_mid    follow the candidate, deploy at the band's midpoint
 *   cand_hi0    band top, but 0 whenever the hostile cap is on
 *   gate_fit    strategy-gate's fixed fit (no candidate), deploy 100 — engine baseline
 *   usdt        never deployed (benchmark)   buy_hold = context
 *
 * This is the "follow the machine candidate 100%" baseline that
 * bot_decision.candidate_delta (same|deviated) is measured against in the
 * live journal: if the LLM's deviations don't beat cand_hi out of sample,
 * the routine should just apply the candidate. Same honesty caveats as the
 * gate: perfect fills, 7d fixed windows ignore intra-window refits, and
 * the fitter floors were chosen knowing these tapes.
 */

require '/path/to/apigtbot/.admin/vendor/autoload.php';

use App\Domains\Bot\Backtester;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Indicators;
use App\Domains\Bot\RoutineBrief;

const INTERVAL = '15m';
const TEST = 672;              // 7d of 15m candles
const STEP = 336;              // 3.5d
const LEAD_DAYS = 60;
const BUDGET = '1000';
const FEE = '0.001';

const TAPES = [
    'bull2024' => ['from' => '2023-11-01', 'to' => '2025-01-01'],
    'bear2026' => ['from' => '2025-10-01', 'to' => '2026-07-31'],
];

$symbol = strtoupper((string) ($argv[1] ?? 'BTCUSDT'));
$profile = (string) ($argv[2] ?? 'Balanced');
if (!isset(RoutineBrief::PROFILE_WALLS[$profile])) {
    fwrite(STDERR, "unknown profile $profile\n");
    exit(1);
}
$gw = new BinanceGateway('https://api.binance.com', '', '');

/** Same cache files as strategy-gate.php — offline once warmed. */
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

/** 15m → k-bucket candles in MarketStore::candles() shape (strings), newest $keep. */
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
        $out[] = ['high' => (string) $hi, 'low' => (string) $lo, 'close' => (string) $c[$i + $k - 1]['close']];
    }
    return $out;
}

/** The brief's inputs as of t: MarketStore::summaries()-shaped rows for 1h/4h/1d. */
function summaries(array $upTo): array
{
    $out = [];
    foreach (['1h' => 4, '4h' => 16, '1d' => 96] as $tf => $k) {
        $s = Indicators::summary(aggregate($upTo, $k));
        $s['stale'] = false;
        $out[$tf] = $s;
    }
    return $out;
}

/** Arms: (candidate, regime, band) → [p_low, p_high, n, deploy_pct] or null = sit out. */
$ARMS = [
    'cand_hi' => fn (?array $c, array $r, array $b) => $c ? [$c['p_low'], $c['p_high'], $c['n_levels'], $b[1]] : null,
    'cand_mid' => fn (?array $c, array $r, array $b) => $c ? [$c['p_low'], $c['p_high'], $c['n_levels'], (int) round(($b[0] + $b[1]) / 2)] : null,
    'cand_hi0' => fn (?array $c, array $r, array $b) => ($c && $r['hostile_cap'] === null) ? [$c['p_low'], $c['p_high'], $c['n_levels'], $b[1]] : null,
    'usdt' => fn () => null,
];

function walk(array $all, array $arms, string $profile): array
{
    $lead = LEAD_DAYS * 96;
    $totals = array_fill_keys(array_keys($arms), 0.0);
    $totals['gate_fit'] = 0.0;
    $totals['buy_hold'] = 0.0;
    $deployed = array_fill_keys(array_keys($arms), 0);
    $windows = 0;
    $classes = [];
    $lastDeploy = array_fill_keys(array_keys($arms), 100);
    for ($t = $lead; $t + TEST <= count($all); $t += STEP) {
        $upTo = array_slice($all, 0, $t);
        $s = summaries($upTo);
        $price = (float) $s['1h']['price'];
        $test = array_slice($all, $t, TEST);
        $tape = array_map(static fn ($c) => (string) $c['close'], $test);
        array_unshift($tape, sprintf('%.8f', $price));
        $totals['buy_hold'] += (float) BUDGET * ((float) end($tape) / $price - 1);
        $windows++;

        $regime = RoutineBrief::regime($s, $price);
        $classes[$regime['class']] = ($classes[$regime['class']] ?? 0) + 1;
        $c4 = aggregate($upTo, 16);

        // engine baseline: strategy-gate's mechanical fit at 100%
        $sp = max(1.3, 1.5 * (float) $s['1h']['atr_pct']);
        $half = max(4 * (float) $s['4h']['atr_pct'], 2.5 * $sp);
        $gLo = $price * (1 - $half / 100);
        $gHi = $price * (1 + $half / 100);
        $gN = max(2, min(40, (int) round(log($gHi / $gLo) / log(1 + $sp / 100))));
        $totals['gate_fit'] += score(sprintf('%.8f', $gLo), sprintf('%.8f', $gHi), $gN, 100, $tape);

        foreach ($arms as $name => $fn) {
            $cand = RoutineBrief::candidate($c4, FEE, BUDGET, $price, $profile, $regime, $lastDeploy[$name]);
            $band = RoutineBrief::deployBand($profile, $regime, $lastDeploy[$name]);
            $pick = $fn($cand, $regime, $band);
            if ($pick === null || (int) $pick[3] <= 0) {
                $lastDeploy[$name] = 0;
                continue;
            }
            [$lo, $hi, $n, $deploy] = $pick;
            $lastDeploy[$name] = (int) $deploy;
            $deployed[$name]++;
            $totals[$name] += score((string) $lo, (string) $hi, (int) $n, (int) $deploy, $tape);
        }
    }
    return ['totals' => $totals, 'windows' => $windows, 'deployed' => $deployed, 'classes' => $classes];
}

/** realized + mark-to-market of one 7d window through the real Backtester. */
function score(string $lo, string $hi, int $n, int $deploy, array $tape): float
{
    $cfg = [
        'p_low' => $lo, 'p_high' => $hi, 'n_levels' => $n,
        'spacing' => 'Geometric', 'allocation' => 'EqualQuote',
        'budget_quote' => bcdiv(bcmul(BUDGET, (string) $deploy, 8), '100', 2), 'fee_pct' => FEE,
        'max_position_quote' => '1000000', 'max_order_quote' => '1000000', 'daily_loss_limit_quote' => '1000000',
        'breakout_buffer_pct' => '0.02', 'breakout_policy' => 'HaltAndHold', 'max_open_orders' => 60, 'min_notional' => '5',
    ];
    try {
        $bt = Backtester::run($cfg, $tape);
        return (float) $bt['realized_pnl'] + (float) $bt['unrealized_pnl'];
    } catch (\InvalidArgumentException) {
        return 0.0; // rejected config (deployed budget under min notional) — sat out
    }
}

// ── run both tapes ───────────────────────────────────────────────────────
$byTape = [];
foreach (TAPES as $name => $range) {
    $candles = tape($gw, $symbol, $range['from'], $range['to']);
    $byTape[$name] = walk($candles, $ARMS, $profile);
    printf("%s: %d candles %s → %s, %d windows, regime classes %s\n", $name, count($candles),
        date('Y-m-d', $candles[0]['t'] / 1000), date('Y-m-d', end($candles)['t'] / 1000),
        $byTape[$name]['windows'], json_encode($byTape[$name]['classes']));
}

$tapeNames = array_keys(TAPES);
printf("\n%s / %s — per-\$1k totals (realized + mark-to-market), 7d windows every 3.5d\n", $symbol, $profile);
printf("%-10s %12s %12s   %-12s %s\n", 'arm', $tapeNames[0], $tapeNames[1], 'verdict', 'windows deployed');
foreach (array_merge(array_keys($ARMS), ['gate_fit', 'buy_hold']) as $name) {
    $vals = array_map(static fn ($t) => $byTape[$t]['totals'][$name], $tapeNames);
    $pass = $vals[0] >= $byTape[$tapeNames[0]]['totals']['usdt'] && $vals[1] >= $byTape[$tapeNames[1]]['totals']['usdt'];
    $verdict = $name === 'usdt' ? 'benchmark' : ($name === 'buy_hold' ? 'context' : ($pass ? 'PASS gate 1' : 'FAIL'));
    $dep = isset($ARMS[$name]) ? sprintf('%d / %d', $byTape[$tapeNames[0]]['deployed'][$name], $byTape[$tapeNames[1]]['deployed'][$name]) : '';
    printf("%-10s %+12.2f %+12.2f   %-12s %s\n", $name, $vals[0], $vals[1], $verdict, $dep);
}
echo "\nReading: cand_* = the routine applying RoutineBrief::candidate() mechanically (no LLM).\n"
    . "Live decisions journal candidate_delta=same|deviated against this baseline (gtbot_decisions\n"
    . "summary.by_candidate_delta); if 'deviated' does not out-earn 'same' out of sample, the LLM's\n"
    . "deviations are not adding value. Same caveats as strategy-gate.php (perfect fills, fixed 7d windows).\n";
