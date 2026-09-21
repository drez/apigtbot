<?php
/**
 * CORE-GATE PROBE (2026-09-16) — should the EmaCross1d inventory core be
 * machine-scaled by the TrendRegime gate like the Donchian arm is?
 *
 * The core (scripts/core-sweep.php) earns +530/+1464 per $1k on the bull
 * tapes but −17/−196 on the bear tapes (mean +445, worst DD 41%) because it
 * enters on ANY 1d EMA20>EMA50 — including bear rallies — and holds through
 * the whole bear. TrendActivator::pass() explicitly skips EmaCross1d runs
 * ("not machine-scaled"), so the core carries the bear cost the trend arm was
 * shielded from. This probe asks: what does the core earn when the SAME
 * TrendRegime classifier that scales the trend arm gates the core's entries
 * (and optionally its exits)?
 *
 * Rules (daily close, fee 0.1%/side, per $1k):
 *   cross        the live core: in while 1d EMA20 > EMA50 (baseline)
 *   gate_entry   enter on EMA20>EMA50 AND TrendRegime==TREND_UP; exit on the
 *                cross down (the core's own exit stays)
 *   gate_both    enter as gate_entry; also exit when TrendRegime==HOSTILE
 *   gate_1d      enter on EMA20>EMA50 AND 1d ADX>=35 AND 1d ER>=0.30
 *                (the 1d half of TREND_UP, no 4h); exit on the cross down
 *
 * Tapes: the gate caches (bull 2023-11→2025-01, bear 2025-10→2026-07).
 * Read-only; no DB, no orders.
 */

require '/path/to/apigtbot/.admin/vendor/autoload.php';

use App\Domains\Bot\Indicators;
use App\Domains\Bot\TrendRegime;

const FEE = 0.001;
const REGIME_WINDOW = 200;

$symbols = array_slice($argv, 1) ?: ['BTCUSDT', 'BNBUSDT'];
$tapes = [
    'bull' => ['2023-11-01', '2025-01-01'],
    'bear' => ['2025-10-01', '2026-07-31'],
];
$admin = dirname(__DIR__);

/** 15m candles → UTC daily OHLC (partial trailing day dropped). */
function dailyBars(array $c): array
{
    $days = [];
    foreach ($c as $k) {
        $d = intdiv((int) $k['t'], 86400000);
        if (!isset($days[$d])) {
            $days[$d] = ['high' => (float) $k['high'], 'low' => (float) $k['low'], 'close' => (float) $k['close']];
        } else {
            $days[$d]['high'] = max($days[$d]['high'], (float) $k['high']);
            $days[$d]['low'] = min($days[$d]['low'], (float) $k['low']);
            $days[$d]['close'] = (float) $k['close'];
        }
    }
    ksort($days);
    $out = [];
    foreach ($days as $d => $v) {
        $out[] = ['t' => $d * 86400] + $v;
    }
    return $out;
}

/** 15m candles → end-aligned 4h buckets. */
function bars4h(array $c): array
{
    $out = [];
    $n = count($c);
    $off = $n % 16;
    for ($i = $off; $i + 16 <= $n; $i += 16) {
        $hi = -INF;
        $lo = INF;
        for ($j = $i; $j < $i + 16; $j++) {
            $hi = max($hi, (float) $c[$j]['high']);
            $lo = min($lo, (float) $c[$j]['low']);
        }
        $out[] = ['t' => (int) $c[$i + 15]['t'], 'high' => $hi, 'low' => $lo, 'close' => (float) $c[$i + 15]['close']];
    }
    return $out;
}

function s1d(array $days, int $i): array
{
    $w = array_slice($days, max(0, $i + 1 - REGIME_WINDOW), min(REGIME_WINDOW, $i + 1));
    $c = array_column($w, 'close');
    return [
        'trend' => Indicators::trend($c),
        'price' => (float) end($c),
        'ema20' => Indicators::ema($c, 20),
        'ema50' => Indicators::ema($c, 50),
        'adx14' => Indicators::adx(array_column($w, 'high'), array_column($w, 'low'), $c),
        'er20' => Indicators::efficiencyRatio($c, 20),
        'stale' => false,
    ];
}

function s4(array $b4, int $i): array
{
    $w = array_slice($b4, max(0, $i + 1 - REGIME_WINDOW), min(REGIME_WINDOW, $i + 1));
    $c = array_column($w, 'close');
    return [
        'trend' => Indicators::trend($c),
        'adx14' => Indicators::adx(array_column($w, 'high'), array_column($w, 'low'), $c),
        'er20' => Indicators::efficiencyRatio($c, 20),
        'chop14' => Indicators::choppiness(array_column($w, 'high'), array_column($w, 'low'), $c, 14),
        'stale' => false,
    ];
}

/**
 * @return array{net:float,dd:float,trades:int,days_in:int,entries:int}
 */
function simulate(array $days, callable $in, callable $out): array
{
    $usdt = 1000.0;
    $qty = 0.0;
    $peak = 1000.0;
    $dd = 0.0;
    $trades = 0;
    $daysIn = 0;
    $entries = 0;
    foreach ($days as $i => $d) {
        $px = $d['close'];
        if ($qty === 0.0 && $in($i)) {
            $qty = $usdt * (1 - FEE) / $px;
            $usdt = 0.0;
            $trades++;
            $entries++;
        } elseif ($qty > 0.0 && $out($i)) {
            $usdt = $qty * $px * (1 - FEE);
            $qty = 0.0;
            $trades++;
        }
        if ($qty > 0.0) {
            $daysIn++;
        }
        $eq = $usdt + $qty * $px;
        $peak = max($peak, $eq);
        $dd = max($dd, ($peak - $eq) / $peak * 100);
    }
    $last = end($days)['close'];
    return ['net' => $usdt + $qty * $last - 1000.0, 'dd' => $dd, 'trades' => $trades, 'days_in' => $daysIn, 'entries' => $entries];
}

foreach ($symbols as $symbol) {
    foreach ($tapes as $name => [$from, $to]) {
        $file = "{$admin}/tmp/gate-{$symbol}-15m-{$from}-{$to}.json";
        if (!is_file($file)) {
            echo "missing {$file}\n";
            continue;
        }
        $all = json_decode((string) file_get_contents($file), true);
        $days = dailyBars($all);
        $b4 = bars4h($all);
        $n = count($days);

        // align 4h buckets to the daily index: last 4h bar ending on/before day i
        $b4Idx = [];
        foreach ($days as $i => $d) {
            $j = 0;
            foreach ($b4 as $k => $b) {
                if ($b['t'] / 1000 <= $d['t']) {
                    $j = $k;
                } else {
                    break;
                }
            }
            $b4Idx[$i] = $j;
        }

        $closes = array_column($days, 'close');
        $crossUp = [];
        $class = [];
        $strong1d = [];
        for ($i = 0; $i < $n; $i++) {
            if ($i < 50) {
                $crossUp[$i] = false;
                $class[$i] = null;
                $strong1d[$i] = false;
                continue;
            }
            $w = array_slice($closes, 0, $i + 1);
            $crossUp[$i] = Indicators::ema($w, 20) > Indicators::ema($w, 50);
            $s1 = s1d($days, $i);
            $s4h = s4($b4, $b4Idx[$i]);
            $class[$i] = TrendRegime::classify($s4h, $s1, false);
            $strong1d[$i] = TrendRegime::oneDayStrong($s1, false);
        }

        $hodl = ($days[$n - 1]['close'] / $days[50]['close'] - 1) * 1000.0;
        printf("═══ %s %s (%s → %s, %d days) — HODL from day 50: %+.0f/1k, USDT 0 ═══\n", $symbol, $name, $from, $to, $n, $hodl);
        printf("%-10s %10s %8s %7s %8s %8s %10s\n", 'rule', 'net/1k', 'maxDD%', 'trades', 'days_in', 'entries', 'vs HODL');

        $rules = [
            'cross' => [
                fn ($i) => $crossUp[$i],
                fn ($i) => !$crossUp[$i],
            ],
            'gate_entry' => [
                fn ($i) => $crossUp[$i] && $class[$i] === 'TREND_UP',
                fn ($i) => !$crossUp[$i],
            ],
            'gate_both' => [
                fn ($i) => $crossUp[$i] && $class[$i] === 'TREND_UP',
                fn ($i) => !$crossUp[$i] || $class[$i] === 'HOSTILE',
            ],
            'gate_1d' => [
                fn ($i) => $crossUp[$i] && $strong1d[$i],
                fn ($i) => !$crossUp[$i],
            ],
        ];
        foreach ($rules as $label => [$in, $out]) {
            $r = simulate($days, $in, $out);
            printf("%-10s %+10.0f %8.1f %7d %8d %8d %+10.0f\n", $label, $r['net'], $r['dd'], $r['trades'], $r['days_in'], $r['entries'], $r['net'] - $hodl);
        }
        echo "\n";
    }
}

echo "Read as: the core is sized by the operator (slice = X% of the pool); all\nnumbers scale with X. 'gate_*' uses the live TrendRegime classifier at the\ndaily close (4h + 1d windows, entry-side thresholds; no hysteresis here).\n";
