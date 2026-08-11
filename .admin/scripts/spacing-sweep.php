<?php
/**
 * Walk-forward spacing sweep — does a volatility-matched spacing rule beat
 * judgment-picked spacing?
 *
 * Protocol: real 15m klines (~90 days; symbol = argv[1], default BTCUSDT).
 * Rolling windows: 14d fit /
 * 7d test / 3.5d step. Per window: grid centered on window-start price,
 * half-width 25× the fit window's 15m ATR%; candidate spacings are FIXED
 * percents; each candidate backtested on the 7d test tape through the real
 * strategy stack (Backtester = same intent→risk→fill code the daemon runs).
 * Score: realized + mark-to-market at window end (holding is not free).
 * Post-hoc: net vs spacing/ATR ratio → derive the rule empirically.
 */

require '/path/to/apigtbot/.admin/vendor/autoload.php';

use App\Domains\Bot\Backtester;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Indicators;

const INTERVAL = '15m';
const BATCHES = 9;             // 9 × 1000 × 15m ≈ 94 days
const FIT = 1344;              // 14d of 15m candles
const TEST = 672;              // 7d
const STEP = 336;              // 3.5d
$SPACINGS = ['0.4', '0.6', '0.8', '1.0', '1.3', '1.7', '2.2']; // % per grid
$FEES = ['0.001' => 'std 0.10%', '0.00075' => 'BNB 0.075%'];

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
        $batch[] = ['t' => (int) $k[0], 'open' => (string) $k[1], 'high' => (string) $k[2], 'low' => (string) $k[3], 'close' => (string) $k[4]];
    }
    $all = array_merge($batch, $all);
    $endTime = $batch[0]['t'] - 1;
    usleep(150000);
}
printf("%s candles: %d (%s → %s)\n\n", $symbol, count($all), date('Y-m-d', $all[0]['t'] / 1000), date('Y-m-d', end($all)['t'] / 1000));

// ── walk-forward ─────────────────────────────────────────────────────────
$results = [];   // fee => spacing => list of per-window rows
$windows = [];
for ($start = 0; $start + FIT + TEST <= count($all); $start += STEP) {
    $fitC = array_slice($all, $start, FIT);
    $testC = array_slice($all, $start + FIT, TEST);
    $sum = Indicators::summary($fitC);
    $atrPct = (float) $sum['atr_pct'];              // 15m ATR%
    $adx = (float) $sum['adx14'];
    $price0 = (float) end($fitC)['close'];
    $half = max(2.0, 25 * $atrPct);                 // half-width in %
    $pLow = sprintf('%.2f', $price0 * (1 - $half / 100));
    $pHigh = sprintf('%.2f', $price0 * (1 + $half / 100));
    $tape = array_column($testC, 'close');
    array_unshift($tape, (string) $price0);
    $windows[] = ['atr' => $atrPct, 'adx' => $adx, 'trend' => $sum['trend']];

    foreach ($FEES as $fee => $feeLabel) {
        foreach ($SPACINGS as $sp) {
            $n = (int) round(log((float) $pHigh / (float) $pLow) / log(1 + (float) $sp / 100));
            $n = max(2, min(40, $n));
            $cfg = [
                'p_low' => $pLow, 'p_high' => $pHigh, 'n_levels' => $n,
                'spacing' => 'Geometric', 'allocation' => 'EqualQuote',
                'budget_quote' => '1000', 'fee_pct' => $fee,
                'max_position_quote' => '1000000', 'max_order_quote' => '1000000',
                'daily_loss_limit_quote' => '1000000',
                'breakout_buffer_pct' => '0.02', 'breakout_policy' => 'HaltAndHold',
                'max_open_orders' => 60, 'min_notional' => '5',
            ];
            try {
                $r = Backtester::run($cfg, $tape);
            } catch (\InvalidArgumentException $e) {
                $results[$fee][$sp][] = ['net' => null]; // blocked by fee floor
                continue;
            }
            $net = (float) $r['realized_pnl'] + (float) $r['unrealized_pnl'];
            $results[$fee][$sp][] = [
                'net' => $net,
                'realized' => (float) $r['realized_pnl'],
                'cycles' => $r['cycles'],
                'fees' => (float) $r['fees_total'],
                'ratio' => $atrPct > 0 ? (float) $sp / $atrPct : null,
                'adx' => $adx,
                'trend' => $sum['trend'],
            ];
        }
    }
}
printf("windows: %d · median 15m ATR%%: %.3f · ADX range %.0f–%.0f\n\n",
    count($windows),
    array_values(array_map(fn ($w) => $w['atr'], $windows))[intdiv(count($windows), 2)] ?? 0,
    min(array_map(fn ($w) => $w['adx'], $windows)),
    max(array_map(fn ($w) => $w['adx'], $windows))
);

// ── report ───────────────────────────────────────────────────────────────
foreach ($FEES as $fee => $feeLabel) {
    printf("═══ fee %s ═══\n", $feeLabel);
    printf("%-9s %9s %9s %9s %6s %8s %8s %9s\n", 'spacing', 'mean net', 'median', 'worst', 'win%', 'cycles/w', 'fees/w', 'sp/ATR');
    foreach ($SPACINGS as $sp) {
        $rows = array_filter($results[$fee][$sp], fn ($r) => $r['net'] !== null);
        if (!$rows) {
            printf("%-9s blocked by fee floor in every window\n", $sp . '%');
            continue;
        }
        $nets = array_column($rows, 'net');
        sort($nets);
        $mean = array_sum($nets) / count($nets);
        $median = $nets[intdiv(count($nets), 2)];
        $wins = count(array_filter($nets, fn ($n) => $n > 0));
        $ratios = array_filter(array_column($rows, 'ratio'));
        printf("%-9s %9.2f %9.2f %9.2f %5.0f%% %8.1f %8.2f %9.1f\n",
            $sp . '%', $mean, $median, $nets[0],
            $wins / count($nets) * 100,
            array_sum(array_column($rows, 'cycles')) / count($rows),
            array_sum(array_column($rows, 'fees')) / count($rows),
            $ratios ? array_sum($ratios) / count($ratios) : 0
        );
    }
    echo "\n";
}

// deployment gate: same windows, but skip any whose FIT trend was down —
// compare always-on vs gated aggregate (capital idle counts as 0)
echo "═══ trend gate (fee 0.10%) — deploy only when fit trend is not down/strong_down ═══\n";
printf("%-9s %12s %12s %10s %12s\n", 'spacing', 'always mean', 'gated mean', 'deployed', 'gated worst');
foreach ($SPACINGS as $sp) {
    $rows = array_filter($results['0.001'][$sp], fn ($r) => $r['net'] !== null);
    if (!$rows) { continue; }
    $gated = array_map(fn ($r) => in_array($r['trend'], ['down', 'strong_down'], true) ? 0.0 : $r['net'], $rows);
    $always = array_column($rows, 'net');
    $deployedN = count(array_filter($rows, fn ($r) => !in_array($r['trend'], ['down', 'strong_down'], true)));
    printf("%-9s %12.2f %12.2f %6d/%-3d %12.2f\n", $sp . '%',
        array_sum($always) / count($always),
        array_sum($gated) / count($gated),
        $deployedN, count($rows),
        min($gated));
}
echo "\n";

// regime split for the standard fee: low-ADX (ranging) vs high-ADX windows
echo "═══ regime split (fee 0.10%) — mean net by spacing ═══\n";
printf("%-9s %12s %12s\n", 'spacing', 'ADX<25', 'ADX>=25');
foreach ($SPACINGS as $sp) {
    $rows = array_filter($results['0.001'][$sp], fn ($r) => $r['net'] !== null);
    $lo = array_filter($rows, fn ($r) => $r['adx'] < 25);
    $hi = array_filter($rows, fn ($r) => $r['adx'] >= 25);
    printf("%-9s %12s %12s\n", $sp . '%',
        $lo ? sprintf('%.2f (n=%d)', array_sum(array_column($lo, 'net')) / count($lo), count($lo)) : '—',
        $hi ? sprintf('%.2f (n=%d)', array_sum(array_column($hi, 'net')) / count($hi), count($hi)) : '—'
    );
}
