<?php
/**
 * Strategy acceptance gate (Gate 1 of the protocol in
 * docs/proposal-two-sided-grid.md): every candidate policy walks forward
 * over TWO fixed regime tapes — the 2024 bull year and the Dec-2025→Jul-2026
 * bear — and PASSES only if it scores >= the usdt benchmark (0) on BOTH.
 * One-tape results are regime artifacts by default; this harness exists so
 * no future "new best strategy" ships without surviving the opposite regime.
 *
 *   php scripts/strategy-gate.php [SYMBOL]         (default BTCUSDT)
 *
 * Protocol per tape: after a 60d indicator lead-in, every 3.5d read the
 * regime from data up to t only (1h/4h/1d aggregated from 15m), apply each
 * policy mechanically, fit the grid the live way (spacing = max(1.3%,
 * 1.5× 1h ATR%), range ~4× 4h ATR), backtest the next 7d through the real
 * Backtester, score realized + mark-to-market. Klines are fetched once and
 * cached under tmp/ so re-runs are offline and reproducible.
 *
 * Gates 2 and 3 are operational, not simulated: a passing policy still needs
 * a >= 4 week paper soak beating flat out-of-sample, and every reported
 * result carries the usdt + buy_hold benchmarks alongside.
 */

require '/path/to/apigtbot/.admin/vendor/autoload.php';

use App\Domains\Bot\Backtester;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Indicators;

const INTERVAL = '15m';
const TEST = 672;              // 7d of 15m candles
const STEP = 336;              // 3.5d
const LEAD_DAYS = 60;          // min history before the first decision (1d EMAs)
const BUDGET = 1000.0;

/** Fixed evaluation tapes. Lead-in is inside the range; decisions start
 *  LEAD_DAYS after 'from', so evaluation covers ~'from'+60d → 'to'. */
const TAPES = [
    'bull2024' => ['from' => '2023-11-01', 'to' => '2025-01-01'],   // evaluates 2024 (+121%)
    'bear2026' => ['from' => '2025-10-01', 'to' => '2026-07-31'],   // evaluates Dec→Jul (−29%)
];

$symbol = strtoupper((string) ($argv[1] ?? 'BTCUSDT'));
$gw = new BinanceGateway('https://api.binance.com', '', '');

/** Fetch 15m candles for [$from, $to) with forward pagination + disk cache. */
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

/** Aggregate 15m candles into buckets of $k, keep the most recent $keep. */
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

/** up/down/side trend family. */
function fam(string $t): string
{
    return in_array($t, ['strong_up', 'up'], true) ? 'up'
        : (in_array($t, ['strong_down', 'down'], true) ? 'down' : 'side');
}

/** Regime read at time t — same signals the routine cites (minus depth/funding). */
function regime(array $upTo): array
{
    $a1h = aggregate($upTo, 4);
    $a4h = aggregate($upTo, 16);
    $a1d = aggregate($upTo, 96);
    $c1 = array_column($a1h, 'close');
    $c4 = array_column($a4h, 'close');
    $c1d = array_column($a1d, 'close');
    $price = end($c1);
    $atr1 = Indicators::atr(array_column($a1h, 'high'), array_column($a1h, 'low'), $c1);
    $atr4 = Indicators::atr(array_column($a4h, 'high'), array_column($a4h, 'low'), $c4);
    return [
        'price' => $price,
        't1' => Indicators::trend($c1),
        't4' => Indicators::trend($c4),
        't1d' => Indicators::trend($c1d),
        'rsi4' => Indicators::rsi($c4),
        'adx4' => Indicators::adx(array_column($a4h, 'high'), array_column($a4h, 'low'), $c4),
        'rank1' => Indicators::atrPctRank(array_column($a1h, 'high'), array_column($a1h, 'low'), $c1),
        'atr1_pct' => $price > 0 ? $atr1 / $price * 100 : 0,
        'atr4_pct' => $price > 0 ? $atr4 / $price * 100 : 0,
    ];
}

/** fit / conflicted / hostile — mechanical encoding of the routine's bands. */
function posture(array $r): string
{
    $rank = $r['rank1'] ?? 50.0;
    if ($r['t1d'] === 'strong_down' || $rank > 85) {
        return 'hostile';
    }
    $agree = fam($r['t1']) === fam($r['t4']) && fam($r['t4']) !== 'side';
    if ($agree && fam($r['t4']) !== 'down' && $r['rsi4'] >= 35 && $r['rsi4'] <= 70) {
        return 'fit';
    }
    return 'conflicted';
}

/** Candidate policies: regime → deploy_pct. Add new candidates HERE; the
 *  verdict table below is the acceptance decision. */
$POLICIES = [
    'grid_always' => fn (array $r) => 100,
    'guideline' => fn (array $r) => ['fit' => 85, 'conflicted' => 45, 'hostile' => 20][posture($r)],
    'guideline0' => fn (array $r) => ['fit' => 85, 'conflicted' => 45, 'hostile' => 0][posture($r)],
    'range_gate' => fn (array $r) => ($r['adx4'] < 25 && fam($r['t1d']) !== 'down') ? 100 : 0,
    'usdt' => fn (array $r) => 0,
];

/** Walk one tape; returns [policy => total net, 'buy_hold' => total]. */
function walk(array $all, array $policies): array
{
    $lead = LEAD_DAYS * 96;
    $totals = array_fill_keys(array_keys($policies), 0.0);
    $totals['buy_hold'] = 0.0;
    $windows = 0;
    for ($t = $lead; $t + TEST <= count($all); $t += STEP) {
        $r = regime(array_slice($all, 0, $t));
        $price0 = $r['price'];
        $test = array_slice($all, $t, TEST);
        $tape = array_map(static fn ($c) => (string) $c['close'], $test);
        array_unshift($tape, (string) $price0);
        $totals['buy_hold'] += BUDGET * ((float) end($tape) / $price0 - 1);
        $windows++;

        $sp = max(1.3, 1.5 * $r['atr1_pct']);
        $half = max(4 * $r['atr4_pct'], 2.5 * $sp);
        $pLow = $price0 * (1 - $half / 100);
        $pHigh = $price0 * (1 + $half / 100);
        $n = max(2, min(40, (int) round(log($pHigh / $pLow) / log(1 + $sp / 100))));

        foreach ($policies as $name => $fn) {
            $deploy = $fn($r);
            if ($deploy <= 0) {
                continue;
            }
            $cfg = [
                'p_low' => sprintf('%.8f', $pLow), 'p_high' => sprintf('%.8f', $pHigh), 'n_levels' => $n,
                'spacing' => 'Geometric', 'allocation' => 'EqualQuote',
                'budget_quote' => sprintf('%.2f', BUDGET * $deploy / 100), 'fee_pct' => '0.001',
                'max_position_quote' => '1000000', 'max_order_quote' => '1000000',
                'daily_loss_limit_quote' => '1000000',
                'breakout_buffer_pct' => '0.02', 'breakout_policy' => 'HaltAndHold',
                'max_open_orders' => 60, 'min_notional' => '5',
            ];
            try {
                $bt = Backtester::run($cfg, $tape);
                $totals[$name] += (float) $bt['realized_pnl'] + (float) $bt['unrealized_pnl'];
            } catch (\InvalidArgumentException) {
                // config rejected (deployed budget under min notional) — sat out
            }
        }
    }
    $totals['_windows'] = $windows;
    return $totals;
}

// ── run both tapes ───────────────────────────────────────────────────────
$byTape = [];
foreach (TAPES as $name => $range) {
    $candles = tape($gw, $symbol, $range['from'], $range['to']);
    printf("%s: %d candles %s → %s\n", $name, count($candles),
        date('Y-m-d', $candles[0]['t'] / 1000), date('Y-m-d', end($candles)['t'] / 1000));
    $byTape[$name] = walk($candles, $POLICIES);
}

// ── verdict table ────────────────────────────────────────────────────────
$tapeNames = array_keys(TAPES);
printf("\n%s — per-$1k totals over %s\n", $symbol, implode(' + ', array_map(
    static fn ($n) => sprintf('%s (%d windows)', $n, $byTape[$n]['_windows']), $tapeNames
)));
printf("%-12s %12s %12s   %s\n", 'policy', ...array_merge($tapeNames, ['verdict']));
$rows = array_merge(array_keys($POLICIES), ['buy_hold']);
foreach ($rows as $name) {
    $vals = array_map(static fn ($t) => $byTape[$t][$name], $tapeNames);
    $usdtRef = array_map(static fn ($t) => $byTape[$t]['usdt'], $tapeNames);
    $pass = true;
    foreach ($vals as $i => $v) {
        if ($v < $usdtRef[$i]) {
            $pass = false;
        }
    }
    $verdict = $name === 'usdt' ? 'benchmark'
        : ($name === 'buy_hold' ? 'context' : ($pass ? 'PASS gate 1' : 'FAIL'));
    printf("%-12s %+12.2f %+12.2f   %s\n", $name, $vals[0], $vals[1], $verdict);
}

echo "\nGate 1 rule: >= usdt on BOTH tapes. A PASS is necessary, not sufficient —\n"
    . "Gate 2: >= 4 week paper soak beating flat out-of-sample (runs are paper-\n"
    . "mode capable). Gate 3: report vs usdt AND buy_hold, never standalone.\n"
    . "Honesty notes: perfect fills flatter every grid row equally; 7d fixed\n"
    . "grids ignore intra-window refits; posture encoding was designed knowing\n"
    . "these tapes (look-ahead in DESIGN, not in data).\n";
