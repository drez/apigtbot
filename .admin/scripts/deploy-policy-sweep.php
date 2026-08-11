<?php
/**
 * Walk-forward DEPLOYMENT-POLICY sweep — does the regime→deploy_pct layer
 * (the actual strategy; spacing only tunes fee drag) beat holding USDT?
 *
 * Protocol: real 15m klines (~365 days; symbol = argv[1], default BTCUSDT).
 * Every 3.5d (after a 60d indicator lead-in): read the regime from data up
 * to t only (1h/4h/1d aggregated from 15m — trend, RSI, ATR%, ADX, ATR rank;
 * funding/depth are NOT available historically, which is exactly why
 * market_regime now logs them), mechanically apply a deployment policy,
 * fit the grid the way the live method does (spacing = max(1.3%, 1.5× 1h
 * ATR%), range bracketing price by ~4× 4h ATR), then backtest the next 7d
 * through the real strategy stack (Backtester). Score: realized +
 * mark-to-market at window end, per $1k budget scaled by deploy_pct.
 *
 * Policies:
 *   grid_always   100% deployed every window (grid-as-shipped baseline)
 *   guideline     the routine prompt's bands: fit→85, conflicted→45, hostile→20
 *   guideline0    same but hostile→0 (flat is a position)
 *   range_gate    100% only when 4h ADX<25 and 1d not down-family, else 0
 *   usdt          never deployed (net 0 by definition — the honest benchmark)
 *   buy_hold      $1k spot held through the window (context row)
 */

require '/path/to/apigtbot/.admin/vendor/autoload.php';

use App\Domains\Bot\Backtester;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Indicators;

const INTERVAL = '15m';
const BATCHES = 35;            // 35 × 1000 × 15m ≈ 365 days
const TEST = 672;              // 7d of 15m candles
const STEP = 336;              // 3.5d
const LEAD_DAYS = 60;          // min history before the first decision (1d EMAs)
const BUDGET = 1000.0;

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

$POLICIES = [
    'grid_always' => fn (array $r) => 100,
    'guideline' => fn (array $r) => ['fit' => 85, 'conflicted' => 45, 'hostile' => 20][posture($r)],
    'guideline0' => fn (array $r) => ['fit' => 85, 'conflicted' => 45, 'hostile' => 0][posture($r)],
    'range_gate' => fn (array $r) => ($r['adx4'] < 25 && fam($r['t1d']) !== 'down') ? 100 : 0,
    'usdt' => fn (array $r) => 0,
];

// ── walk-forward ─────────────────────────────────────────────────────────
$lead = LEAD_DAYS * 96;
$results = [];   // policy => list of ['net'=>, 'deploy'=>]
$bh = [];
$postures = [];
for ($t = $lead; $t + TEST <= count($all); $t += STEP) {
    $r = regime(array_slice($all, 0, $t));
    $postures[] = posture($r);
    $price0 = $r['price'];
    $test = array_slice($all, $t, TEST);
    $tape = array_map(static fn ($c) => (string) $c['close'], $test);
    array_unshift($tape, (string) $price0);
    $pEnd = (float) end($tape);
    $bh[] = BUDGET * ($pEnd / $price0 - 1);

    // Live-method geometry: absolute spacing floor, ATR-bracketed range.
    $sp = max(1.3, 1.5 * $r['atr1_pct']);
    $half = max(4 * $r['atr4_pct'], 2.5 * $sp);
    $pLow = $price0 * (1 - $half / 100);
    $pHigh = $price0 * (1 + $half / 100);
    $n = max(2, min(40, (int) round(log($pHigh / $pLow) / log(1 + $sp / 100))));

    foreach ($POLICIES as $name => $fn) {
        $deploy = $fn($r);
        if ($deploy <= 0) {
            $results[$name][] = ['net' => 0.0, 'deploy' => 0];
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
            $net = (float) $bt['realized_pnl'] + (float) $bt['unrealized_pnl'];
        } catch (\InvalidArgumentException $e) {
            $net = 0.0; // config rejected (e.g. deployed budget under min notional) — sat out
        }
        $results[$name][] = ['net' => $net, 'deploy' => $deploy];
    }
}

$tally = array_count_values($postures);
printf("windows: %d · postures: fit %d / conflicted %d / hostile %d\n\n",
    count($postures), $tally['fit'] ?? 0, $tally['conflicted'] ?? 0, $tally['hostile'] ?? 0);

printf("%-12s %9s %9s %9s %9s %6s %9s %8s\n", 'policy', 'total', 'mean/w', 'median', 'worst', 'win%', 'dep win%', 'avg dep');
$report = static function (string $name, array $nets, array $deploys) {
    $sorted = $nets;
    sort($sorted);
    $depWins = $depN = 0;
    foreach ($nets as $i => $n) {
        if ($deploys[$i] > 0) {
            $depN++;
            if ($n > 0) {
                $depWins++;
            }
        }
    }
    printf("%-12s %9.2f %9.2f %9.2f %9.2f %5.0f%% %8s %7.0f%%\n",
        $name, array_sum($nets), array_sum($nets) / count($nets),
        $sorted[intdiv(count($sorted), 2)], $sorted[0],
        count(array_filter($nets, fn ($n) => $n > 0)) / count($nets) * 100,
        $depN ? sprintf('%.0f%%', $depWins / $depN * 100) : '—',
        array_sum($deploys) / count($deploys)
    );
};
foreach ($POLICIES as $name => $_) {
    $report($name, array_column($results[$name], 'net'), array_column($results[$name], 'deploy'));
}
$report('buy_hold', $bh, array_fill(0, count($bh), 100));

echo "\nHonesty notes: decisions use data up to t only, but the policy encoding was
written knowing this year's tapes (mild look-ahead in DESIGN, not in data);
7d fixed grids ignore intra-window refits; perfect fills flatter every grid
row equally; depth/funding signals unavailable pre-market_regime logging.
'usdt' total is the bar: a policy below it destroyed value vs doing nothing.\n";
