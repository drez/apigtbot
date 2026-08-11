<?php
/**
 * Walk-forward STOP-POLICY sweep — does the staged unrealized stop truncate
 * the tail windows that made every static grid net-negative (see
 * spacing-sweep.php), and should the hard stop hold (live behavior) or
 * flatten? Same protocol as spacing-sweep: real BTCUSDT 15m klines ~94d,
 * 14d fit / 7d test / 3.5d step, cap = 10% of budget (mirrors prod).
 */

require '/path/to/apigtbot/.admin/vendor/autoload.php';

use App\Domains\Bot\Backtester;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Indicators;

const INTERVAL = '15m';
const BATCHES = 9;
const FIT = 1344;
const TEST = 672;
const STEP = 336;
$SPACINGS = ['0.8', '1.0', '1.3', '1.7', '2.2'];
$POLICIES = ['none' => null, 'hold' => 'hold', 'flatten' => 'flatten'];
const CAP = '100'; // 10% of the 1000 budget — prod ratio

$gw = new BinanceGateway('https://api.binance.com', '', '');
$all = [];
$endTime = null;
for ($b = 0; $b < BATCHES; $b++) {
    $params = ['symbol' => 'BTCUSDT', 'interval' => INTERVAL, 'limit' => 1000];
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
printf("candles: %d (%s → %s)\n\n", count($all), date('Y-m-d', $all[0]['t'] / 1000), date('Y-m-d', end($all)['t'] / 1000));

$results = []; // policy => spacing => rows
for ($start = 0; $start + FIT + TEST <= count($all); $start += STEP) {
    $fitC = array_slice($all, $start, FIT);
    $testC = array_slice($all, $start + FIT, TEST);
    $sum = Indicators::summary($fitC);
    $atrPct = (float) $sum['atr_pct'];
    $price0 = (float) end($fitC)['close'];
    $half = max(2.0, 25 * $atrPct);
    $pLow = sprintf('%.2f', $price0 * (1 - $half / 100));
    $pHigh = sprintf('%.2f', $price0 * (1 + $half / 100));
    $tape = array_column($testC, 'close');
    array_unshift($tape, (string) $price0);

    foreach ($POLICIES as $pname => $policy) {
        foreach ($SPACINGS as $sp) {
            $n = max(2, min(40, (int) round(log((float) $pHigh / (float) $pLow) / log(1 + (float) $sp / 100))));
            $cfg = [
                'p_low' => $pLow, 'p_high' => $pHigh, 'n_levels' => $n,
                'spacing' => 'Geometric', 'allocation' => 'EqualQuote',
                'budget_quote' => '1000', 'fee_pct' => '0.001',
                'max_position_quote' => '1000000', 'max_order_quote' => '1000000',
                'daily_loss_limit_quote' => '1000000',
                'breakout_buffer_pct' => '0.02', 'breakout_policy' => 'HaltAndHold',
                'max_open_orders' => 60, 'min_notional' => '5',
            ];
            if ($policy !== null) {
                $cfg['max_unrealized_loss_quote'] = CAP;
                $cfg['stop_policy'] = $policy;
            }
            try {
                $r = Backtester::run($cfg, $tape);
            } catch (\InvalidArgumentException $e) {
                continue;
            }
            $results[$pname][$sp][] = [
                'net' => (float) $r['realized_pnl'] + (float) $r['unrealized_pnl'],
                'stopped' => (bool) $r['stop_triggered'],
                'cycles' => $r['cycles'],
            ];
        }
    }
}

foreach ($POLICIES as $pname => $_) {
    printf("═══ policy: %s (cap %s) ═══\n", $pname, $pname === 'none' ? '—' : CAP);
    printf("%-9s %9s %9s %9s %6s %8s %8s\n", 'spacing', 'mean net', 'median', 'worst', 'win%', 'cycles/w', 'stopped');
    foreach ($SPACINGS as $sp) {
        $rows = $results[$pname][$sp] ?? [];
        if (!$rows) {
            continue;
        }
        $nets = array_column($rows, 'net');
        sort($nets);
        printf("%-9s %9.2f %9.2f %9.2f %5.0f%% %8.1f %7d\n",
            $sp . '%',
            array_sum($nets) / count($nets),
            $nets[intdiv(count($nets), 2)],
            $nets[0],
            count(array_filter($nets, fn ($x) => $x > 0)) / count($nets) * 100,
            array_sum(array_column($rows, 'cycles')) / count($rows),
            count(array_filter($rows, fn ($r) => $r['stopped']))
        );
    }
    echo "\n";
}
