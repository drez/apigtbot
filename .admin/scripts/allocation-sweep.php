<?php
/**
 * Walk-forward ALLOCATION sweep — does the regime-weighted Grid/Trend split
 * (docs/proposal-regime-weighted-allocation.md) beat a static 50/50 of the
 * same two arms? Companion evidence for lifting the A/B capital pin: the
 * proposal earns a routine-prompt slot only if regime-weighting beats
 * static on mean AND worst-window on BOTH symbols (the same cross-symbol
 * bar the regime deploy gate had to clear); a tie keeps static.
 *
 * Protocol: real 15m klines ~187d (symbol = argv[1], default both), 14d fit
 * / 7d test / 7d step — NON-overlapping test windows, a deliberate
 * deviation from the 3.5d-step family protocol: the policies carry weight
 * STATE across windows (step limits), and overlapping outcome windows
 * would double-count P&L inside a sequential chain.
 *
 * Arms, simulated per window on what each actually trades:
 *   GRID  — Backtester, static 4×ATR% half-width HaltAndHold, 1.7%
 *           geometric spacing (regime-sweep baseline), on the 15m tape.
 *   TREND — TrendEngine's own pure statics (entrySignal 20-bar Donchian +
 *           EMA20>50, ratchet 3×ATR trail, cooldown ladder 3x→2.0/3), on
 *           the 1h aggregate, trailing-300-bar signal window — the
 *           schema-default cell. Same intrabar assumptions as
 *           trend-sweep.php (ratchet off the HIGH, then test the LOW).
 *   Both arms MTM-close at the window boundary; neither carries a position
 *   across windows (symmetrically conservative for Trend, whose winners
 *   can outlive 7d).
 *
 * Policies (weights persist across the window chain; wT = Trend share):
 *   static      50/50 forever
 *   regime      proposal classifier (HOSTILE→freeze, TRENDING-UP→70,
 *               RANGE→30, MIXED→drift 50), step ≤20pts/window, bounds
 *               30..70. Window-grain approximation: the proposal's 2-pass
 *               hourly hysteresis has no hourly grain here, so it is NOT
 *               simulated; the ±10 evidence modulator needs live PnL
 *               history and is also out of scope — this sweep tests the
 *               CLASSIFIER + targets, not the micro-mechanics.
 *   gridOnly    0/100     trendOnly   100/0
 *   hindsight   per-window better arm (upper bound, not a real policy)
 *
 * Window net per $1k pool = wT × trendNet/1k + (1−wT) × gridNet/1k.
 * Classification uses FIT-window data only (no lookahead): 4h aggregate of
 * the fit window for trend/adx/er/chop, 1d aggregate of the FULL history up
 * to fit end for the 1d confirm.
 */

require '/path/to/apigtbot/.admin/vendor/autoload.php';

use App\Domains\Bot\Backtester;
use App\Domains\Bot\Engine\TrendEngine;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Indicators;

const INTERVAL = '15m';
const BATCHES = 18;             // ≈ 187 days
const FIT = 1344;               // 14d of 15m
const TEST = 672;               // 7d
const STEP = 672;               // NON-overlapping (see header)
const SPACING = 1.7;
const GRID_WIDTH_ATR = 4;       // regime-sweep baseline width
const DONCHIAN = 20;            // schema defaults / trend-sweep proven cell
const STOP_MULT = '3';
const INITIAL_MULT = '2.0';
const COOLDOWN_BARS = 3;
const SIGNAL_WINDOW = 300;      // trailing 1h bars fed to entrySignal
const SCALE = 8;
const FEE_PCT = '0.001';

$symbols = isset($argv[1]) ? [strtoupper((string) $argv[1])] : ['BTCUSDT', 'BNBUSDT'];

/** Aggregate 15m candles into buckets of $k over an index range. */
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
        $out[] = ['high' => (string) $hi, 'low' => (string) $lo, 'close' => (string) $c[$i + $k - 1]['close']];
    }
    return $out;
}

/** GRID arm: one Backtester run per window; net per $1k. */
function gridNet(float $price0, float $halfPct, array $tape): float
{
    $pLow = $price0 * (1 - $halfPct / 100);
    $pHigh = $price0 * (1 + $halfPct / 100);
    $n = max(2, min(40, (int) round(log($pHigh / $pLow) / log(1 + SPACING / 100))));
    $cfg = [
        'p_low' => sprintf('%.8f', $pLow), 'p_high' => sprintf('%.8f', $pHigh), 'n_levels' => $n,
        'spacing' => 'Geometric', 'allocation' => 'EqualQuote',
        'budget_quote' => '1000', 'fee_pct' => FEE_PCT,
        'max_position_quote' => '1000000', 'max_order_quote' => '1000000',
        'daily_loss_limit_quote' => '1000000',
        'breakout_buffer_pct' => '0.02', 'breakout_policy' => 'HaltAndHold',
        'max_open_orders' => 60, 'min_notional' => '5',
    ];
    array_unshift($tape, sprintf('%.8f', $price0));
    try {
        $r = Backtester::run($cfg, $tape);
    } catch (\InvalidArgumentException) {
        return 0.0;
    }
    return (float) $r['realized_pnl'] + (float) $r['unrealized_pnl'];
}

/** Synthetic evenly-spaced 1h timestamp (only spacing matters — cooldown diffs). */
function barTime(int $i): string
{
    return date('Y-m-d H:i:s', 1577836800 + $i * 3600);
}

/**
 * TREND arm: windowed sim of the engine's pure statics over 1h bars
 * [$start, $end) with trailing signal context; net per $1k, MTM at window
 * end, no cross-window position carry.
 * @param array<int,array{high:string,low:string,close:string}> $h1 full 1h tape
 */
function trendNet(array $h1, int $start, int $end): float
{
    $position = null;
    $stopOutAt = null;
    $realized = '0';
    for ($i = $start; $i < $end; $i++) {
        $bar = $h1[$i];
        $ctxFrom = max(0, $i + 1 - SIGNAL_WINDOW);
        $ctx = array_slice($h1, $ctxFrom, $i + 1 - $ctxFrom);
        $highs = array_column($ctx, 'high');
        $lows = array_column($ctx, 'low');
        $closes = array_column($ctx, 'close');

        if ($position !== null) {
            $atr = Indicators::atr($highs, $lows, $closes, 14);
            if ($atr > 0) {
                $r = TrendEngine::ratchet(
                    ['entry' => $position['entry'], 'hwm' => $position['hwm'], 'stop' => $position['stop']],
                    (string) $bar['high'],
                    STOP_MULT,
                    number_format($atr, SCALE, '.', '')
                );
                $position['hwm'] = $r['hwm'];
                $position['stop'] = $r['stop'];
            }
            if (bccomp((string) $bar['low'], $position['stop'], SCALE) <= 0) {
                $fill = bccomp((string) $bar['close'], $position['stop'], SCALE) < 0 ? (string) $bar['close'] : $position['stop'];
                $exitFee = bcmul(bcmul($position['qty'], $fill, SCALE), FEE_PCT, SCALE);
                $gross = bcmul(bcsub($fill, $position['entry'], SCALE), $position['qty'], SCALE);
                $realized = bcadd($realized, bcsub($gross, bcadd($position['entryFee'], $exitFee, SCALE), SCALE), SCALE);
                $stopOutAt = barTime($i);
                $position = null;
            }
            continue;
        }

        if (!TrendEngine::cooldownElapsed($stopOutAt, COOLDOWN_BARS, '1h', barTime($i))) {
            continue;
        }
        if (!TrendEngine::entrySignal($ctx, DONCHIAN, 20, 50)) {
            continue;
        }
        $atr = Indicators::atr($highs, $lows, $closes, 14);
        if ($atr <= 0) {
            continue;
        }
        $entry = (string) $bar['close'];
        $qty = bcdiv('1000', $entry, SCALE);
        $position = [
            'entry' => $entry, 'qty' => $qty, 'hwm' => $entry,
            'stop' => bcsub($entry, bcmul(INITIAL_MULT, number_format($atr, SCALE, '.', ''), SCALE), SCALE),
            'entryFee' => bcmul(bcmul($qty, $entry, SCALE), FEE_PCT, SCALE),
        ];
    }
    if ($position !== null) {
        $close = (string) $h1[$end - 1]['close'];
        $gross = bcmul(bcsub($close, $position['entry'], SCALE), $position['qty'], SCALE);
        $realized = bcadd($realized, bcsub($gross, $position['entryFee'], SCALE), SCALE);
    }
    return (float) $realized;
}

/** up/down/side family. */
function fam(string $t): string
{
    return in_array($t, ['strong_up', 'up'], true) ? 'up'
        : (in_array($t, ['strong_down', 'down'], true) ? 'down' : 'side');
}

/** Proposal classifier — HOSTILE → TRENDING-UP → RANGE → MIXED. */
function classify(string $trend4, float $adx4, ?float $er4, ?float $chop4, string $trend1d): string
{
    $upAligned = fam($trend4) === 'up' && fam($trend1d) === 'up';
    if (fam($trend4) === 'down' || ($adx4 >= 30 && !$upAligned)) {
        return 'HOSTILE';
    }
    if ($upAligned && $adx4 >= 25 && $er4 !== null && $er4 >= 0.35) {
        return 'TREND_UP';
    }
    if (($chop4 !== null && $chop4 >= 55) || ($adx4 < 20 && fam($trend4) === 'side')) {
        return 'RANGE';
    }
    return 'MIXED';
}

foreach ($symbols as $symbol) {
    $gw = new BinanceGateway('https://api.binance.com', '', '');
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
    $h1 = aggRange($all, count($all) % 4, count($all), 4); // full 1h tape, aligned to the end
    printf("═══ %s — %d×15m (%s → %s), %d×1h ═══\n", $symbol, count($all),
        date('Y-m-d', $all[0]['t'] / 1000), date('Y-m-d', end($all)['t'] / 1000), count($h1));

    // per-window arm nets + class (fit-time only)
    $windows = [];
    for ($start = 0; $start + FIT + TEST <= count($all); $start += STEP) {
        $fitC = array_slice($all, $start, FIT);
        $a4h = aggRange($all, $start, $start + FIT, 16);
        $h4 = array_column($a4h, 'high');
        $l4 = array_column($a4h, 'low');
        $c4 = array_column($a4h, 'close');
        $a1d = aggRange($all, ($start + FIT) % 96, $start + FIT, 96); // full history up to fit end
        $price0 = (float) end($fitC)['close'];
        $atr4 = Indicators::atr($h4, $l4, $c4);
        $atr4Pct = $price0 > 0 ? $atr4 / $price0 * 100 : 0;

        $class = classify(
            Indicators::trend($c4),
            Indicators::adx($h4, $l4, $c4),
            Indicators::efficiencyRatio($c4, 20),
            Indicators::choppiness($h4, $l4, $c4, 14),
            Indicators::trend(array_column($a1d, 'close'))
        );

        $tape = array_map(static fn ($c) => (string) $c['close'], array_slice($all, $start + FIT, TEST));
        $g = gridNet($price0, max(GRID_WIDTH_ATR * $atr4Pct, SPACING), $tape);
        $t1hStart = intdiv($start + FIT - (count($all) - 4 * count($h1)), 4);
        $t = trendNet($h1, $t1hStart, $t1hStart + intdiv(TEST, 4));
        $windows[] = ['class' => $class, 'grid' => $g, 'trend' => $t];
    }
    $nW = count($windows);

    // class distribution + per-class arm means (does the classifier see what it claims?)
    printf("windows: %d · class mix:", $nW);
    foreach (['HOSTILE', 'TREND_UP', 'RANGE', 'MIXED'] as $c) {
        $rows = array_filter($windows, fn ($w) => $w['class'] === $c);
        if ($rows) {
            printf("  %s %d (grid %+.2f / trend %+.2f)", $c, count($rows),
                array_sum(array_column($rows, 'grid')) / count($rows),
                array_sum(array_column($rows, 'trend')) / count($rows));
        }
    }
    echo "\n\n";

    // policies over the window chain
    $policies = [
        'static 50/50' => static function (array $w, float $wT): array {
            return [0.5, 0.5];
        },
        'regime'       => static function (array $w, float $wT): array {
            $target = match ($w['class']) {
                'TREND_UP' => 0.70,
                'RANGE' => 0.30,
                'MIXED' => 0.50,
                default => $wT, // HOSTILE: freeze
            };
            $wT += max(-0.20, min(0.20, $target - $wT));
            $wT = max(0.30, min(0.70, $wT));
            return [$wT, $wT];
        },
        'gridOnly'     => static fn (array $w, float $wT): array => [0.0, 0.0],
        'trendOnly'    => static fn (array $w, float $wT): array => [1.0, 1.0],
        'hindsight'    => static fn (array $w, float $wT): array => [$w['trend'] >= $w['grid'] ? 1.0 : 0.0, 0.5],
    ];

    printf("%-14s %10s %10s %10s %10s %6s\n", 'policy', 'total/1k', 'mean/wk', 'worst wk', 'maxDD', 'win%');
    foreach ($policies as $name => $stepFn) {
        $wT = 0.5;
        $nets = [];
        foreach ($windows as $w) {
            [$useW, $wT] = $stepFn($w, $wT);
            $nets[] = $useW * $w['trend'] + (1 - $useW) * $w['grid'];
        }
        $cum = 0.0;
        $peak = 0.0;
        $dd = 0.0;
        foreach ($nets as $x) {
            $cum += $x;
            $peak = max($peak, $cum);
            $dd = max($dd, $peak - $cum);
        }
        printf("%-14s %10.2f %10.2f %10.2f %10.2f %5.0f%%\n", $name,
            array_sum($nets), array_sum($nets) / max(1, $nW), min($nets), $dd,
            count(array_filter($nets, fn ($x) => $x > 0)) / max(1, $nW) * 100);
    }
    echo "\n";
}

echo "Honesty notes: hindsight is an upper bound, not a policy; both arms
MTM-close at each 7d boundary (Trend winners longer than 7d are truncated —
conservative for trendOnly and every trend-weighted row); the proposal's
2-pass hysteresis and ±10 evidence modulator are NOT simulated (window
grain ≠ hourly grain); non-overlapping windows shrink N vs the family
protocol (~26/symbol) — treat close calls as ties, and ties keep static.\n";
