<?php
/**
 * Forecast-skill sweep for the long-horizon outlook (App\Domains\Bot\MarketOutlook).
 *
 * The outlook is WARN-ONLY, so the question is not PnL but skill: when it
 * says MAJOR_UP, is the next 30 days up more often than the tape's base
 * rate — on BOTH symbols and on BOTH the bull and the bear tape — and how
 * often does it change its mind? It walks every daily close of the full
 * Binance history with the SAME features/classify/step the cron runs.
 *
 * Data: full 1d kline history, cached as tmp/outlook-{SYMBOL}-1d.json
 * (--refresh refetches). Weekly bars are composed from the dailies (Binance
 * weeks open Monday 00:00 UTC), so the in-progress week at each close is
 * exactly what the live 1w summary would have shown. The 15m gate tapes are
 * not used: 14 and 10 months leave ~60 and ~43 weekly bars.
 *
 * KNOWN APPROXIMATION: live runs hourly against an in-progress, repainting
 * daily bar and needs CONFIRM_PASSES_* consecutive passes; the sweep sees
 * one read per daily CLOSE and feeds it to step() --passes-per-close times
 * (default 24). Intraday repaint is invisible here — only the live scored
 * log (market_outlook) measures the real thing.
 *
 * Acceptance bar to turn the Telegram warning on (config gtbot_outlook_alerts),
 * every line on BOTH symbols:
 *   major  MAJOR_UP and MAJOR_DOWN hit@30d >= base + 10pp on full history;
 *          no tape cell with n >= 20 worse than base - 5pp (n < 20 =
 *          insufficient, neither pass nor fail); flips <= 12/yr;
 *          direct MAJOR<->MAJOR reversals <= 2/yr
 *   all    + both FORMING verdicts hit@30d >= base, and >= 40% of FORMING
 *          calls promoted to the same-direction MAJOR within 21 days
 *   else   off — logging, scoring and the routine brief only
 *
 * CLI: php scripts/outlook-sweep.php [SYMBOL ...] [--refresh]
 *        [--passes-per-close=24] [--set=NAME=value ...]
 * Read-only; nothing here touches the DB.
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Indicators;
use App\Domains\Bot\MarketOutlook as MO;

const WARMUP_DAYS = 420;   // 60 weekly bars before the first read
const KEEP = 300;          // MarketStore::KEEP — the series features() sees live
const LIMIT = 1000;        // MarketCollector::LIMIT — the series the summary sees live

$symbols = [];
$refresh = false;
$perClose = 24;
foreach (array_slice($argv, 1) as $a) {
    if ($a === '--refresh') {
        $refresh = true;
    } elseif (preg_match('/^--passes-per-close=(\d+)$/', $a, $m)) {
        $perClose = max(1, (int) $m[1]);
    } elseif (preg_match('/^--set=([A-Z0-9_]+)=(-?[\d.]+)$/', $a, $m)) {
        MO::tune($m[1], (float) $m[2]);
        echo "tuned {$m[1]} = {$m[2]}\n";
    } elseif ($a[0] !== '-') {
        $symbols[] = strtoupper($a);
    } else {
        fwrite(STDERR, "unknown option $a\n");
        exit(2);
    }
}
$symbols = $symbols ?: ['BTCUSDT', 'BNBUSDT'];
$slices = [
    'full' => [null, null],
    'bull2024' => ['2023-11-01', '2025-01-01'],
    'bear2026' => ['2025-10-01', '2026-07-31'],
];

/** @return array<int, array{t:int, high:float, low:float, close:float}> completed UTC days, oldest first */
function dailies(string $symbol, bool $refresh): array
{
    $cache = sprintf('%s/tmp/outlook-%s-1d.json', dirname(__DIR__), $symbol);
    if (!$refresh && is_file($cache)) {
        return json_decode((string) file_get_contents($cache), true);
    }
    $gw = new BinanceGateway('https://api.binance.com', '', '');
    $start = strtotime('2017-01-01 00:00:00 UTC') * 1000;
    $today = intdiv(time(), 86400) * 86400 * 1000; // drop the in-progress day
    $all = [];
    while ($start < $today) {
        $raw = $gw->publicGet('/api/v3/klines', [
            'symbol' => $symbol, 'interval' => '1d', 'limit' => 1000,
            'startTime' => $start, 'endTime' => $today - 1,
        ]);
        if (!is_array($raw) || count($raw) === 0) {
            break;
        }
        foreach ($raw as $k) {
            $all[] = ['t' => intdiv((int) $k[0], 1000), 'high' => (float) $k[2], 'low' => (float) $k[3], 'close' => (float) $k[4]];
        }
        $start = ((int) end($raw)[0]) + 1;
        usleep(150000);
    }
    file_put_contents($cache, json_encode($all));
    return $all;
}

/** Monday-anchored week index of a UTC day timestamp (1970-01-01 was a Thursday). */
function weekOf(int $t): int
{
    return intdiv(intdiv($t, 86400) + 3, 7);
}

function pct(?float $v): string
{
    return $v === null ? '   n/a' : sprintf('%5.1f%%', $v * 100);
}

function median(array $v): ?float
{
    if (!$v) {
        return null;
    }
    sort($v);
    $n = count($v);
    return $n % 2 ? $v[intdiv($n, 2)] : ($v[$n / 2 - 1] + $v[$n / 2]) / 2;
}

/** share of $rets a $verdict call would have "hit" — also the base rate when fed every day of the slice */
function hitRate(string $verdict, array $rets): ?float
{
    $rets = array_values(array_filter($rets, static fn ($r) => $r !== null));
    if (!$rets) {
        return null;
    }
    return count(array_filter($rets, static fn ($r) => MO::hit($verdict, $r) === true)) / count($rets);
}

/**
 * Walk one symbol. @return array{days: array<int, array>, calls: array<int, array>}
 * day  = {t, price, verdict, ret7, ret30}
 * call = {i, t, verdict, prev, price, ret7, ret30, adverse}
 */
function walk(array $d, int $perClose): array
{
    $n = count($d);
    $weeks = [];          // completed + in-progress weekly bars up to day $i
    $state = [];
    $days = $calls = [];
    for ($i = 0; $i < $n; $i++) {
        $wk = weekOf($d[$i]['t']);
        $last = count($weeks) - 1;
        if ($last >= 0 && $weeks[$last]['w'] === $wk) {
            $weeks[$last]['high'] = max($weeks[$last]['high'], $d[$i]['high']);
            $weeks[$last]['low'] = min($weeks[$last]['low'], $d[$i]['low']);
            $weeks[$last]['close'] = $d[$i]['close'];
        } else {
            $weeks[] = ['w' => $wk] + $d[$i];
        }
        if ($i < WARMUP_DAYS) {
            continue;
        }
        $c1d = array_slice($d, max(0, $i + 1 - LIMIT), min($i + 1, LIMIT));
        $c1w = array_slice($weeks, -LIMIT);
        $cl1d = array_column($c1d, 'close');
        $cl1w = array_column($c1w, 'close');
        $s1d = [
            'price' => $d[$i]['close'],
            'ema20' => Indicators::ema($cl1d, 20), 'ema50' => Indicators::ema($cl1d, 50), 'ema200' => Indicators::ema($cl1d, 200),
            'adx14' => Indicators::adx(array_column($c1d, 'high'), array_column($c1d, 'low'), $cl1d, 14),
            'er20' => Indicators::efficiencyRatio($cl1d, 20),
            'candles_used' => min(count($c1d), KEEP),
        ];
        $s1w = [
            'price' => $d[$i]['close'],
            'ema20' => Indicators::ema($cl1w, 20), 'ema50' => Indicators::ema($cl1w, 50),
            'candles_used' => min(count($c1w), KEEP),
        ];
        $feat = MO::features(array_slice($cl1d, -KEEP), array_slice($cl1w, -KEEP));
        $raw = MO::classify($s1d, $s1w, $feat, $state['verdict'] ?? null)['verdict'];
        for ($p = 0; $p < $perClose; $p++) {
            $state = MO::step($state, $raw);
            if ($state['changed']) {
                $calls[] = ['i' => $i, 't' => $d[$i]['t'], 'verdict' => $state['verdict'], 'prev' => $state['prev'], 'price' => $d[$i]['close']];
            }
        }
        $days[$i] = ['t' => $d[$i]['t'], 'price' => $d[$i]['close'], 'verdict' => $state['verdict'] ?? null];
    }
    $fwd = static fn (int $i, int $h): ?float => isset($d[$i + $h]) ? $d[$i + $h]['close'] / $d[$i]['close'] - 1 : null;
    foreach ($days as $i => &$day) {
        $day['ret7'] = $fwd($i, 7);
        $day['ret30'] = $fwd($i, 30);
    }
    unset($day);
    foreach ($calls as &$c) {
        $c['ret7'] = $fwd($c['i'], 7);
        $c['ret30'] = $fwd($c['i'], 30);
        $dir = MO::direction($c['verdict']);
        $c['adverse'] = null;
        if ($dir !== 0 && isset($d[$c['i'] + 30])) {
            $win = array_slice($d, $c['i'] + 1, 30);
            $c['adverse'] = $dir > 0
                ? min(array_column($win, 'low')) / $c['price'] - 1
                : -(max(array_column($win, 'high')) / $c['price'] - 1);
        }
    }
    unset($c);
    return ['days' => $days, 'calls' => $calls];
}

$verdicts = array_reverse(array_keys(MO::RANK));
$results = []; // [symbol][slice][verdict] = {n, hit30, base30}, plus ['_flips'], ['_reversals'], ['_promoted']

foreach ($symbols as $symbol) {
    $d = dailies($symbol, $refresh);
    $w = walk($d, $perClose);
    printf("\n================ %s — %d daily bars, %s → %s ================\n", $symbol, count($d), gmdate('Y-m-d', $d[0]['t']), gmdate('Y-m-d', end($d)['t']));

    foreach ($slices as $name => [$from, $to]) {
        $lo = $from ? strtotime("$from UTC") : 0;
        $hi = $to ? strtotime("$to UTC") : PHP_INT_MAX;
        $days = array_filter($w['days'], static fn ($x) => $x['t'] >= $lo && $x['t'] < $hi && $x['verdict'] !== null);
        $calls = array_values(array_filter($w['calls'], static fn ($x) => $x['t'] >= $lo && $x['t'] < $hi));
        if (!$days) {
            printf("\n-- %s: no data --\n", $name);
            continue;
        }
        $years = count($days) / 365.25;
        $all7 = array_column($days, 'ret7');
        $all30 = array_column($days, 'ret30');

        printf("\n-- %s  (%d days, %.1f yr) — per DAY in state (overlapping samples, autocorrelated) --\n", $name, count($days), $years);
        printf("%-13s %6s %6s | %8s %8s %7s %7s | %7s %7s\n", 'verdict', 'days', 'share', 'mean30', 'med30', 'hit30', 'base30', 'hit7', 'base7');
        foreach ($verdicts as $v) {
            $in = array_filter($days, static fn ($x) => $x['verdict'] === $v);
            $r30 = array_values(array_filter(array_column($in, 'ret30'), static fn ($r) => $r !== null));
            $r7 = array_column($in, 'ret7');
            $hit30 = hitRate($v, $r30);
            $base30 = hitRate($v, $all30);
            $results[$symbol][$name][$v] = ['n' => count($r30), 'hit30' => $hit30, 'base30' => $base30];
            printf(
                "%-13s %6d %5.0f%% | %8s %8s %7s %7s | %7s %7s\n",
                $v, count($in), count($in) / count($days) * 100,
                pct($r30 ? array_sum($r30) / count($r30) : null), pct(median($r30)), pct($hit30), pct($base30),
                $v === MO::NEUTRAL ? '   n/a' : pct(hitRate($v, $r7)),
                $v === MO::NEUTRAL ? '   n/a' : pct(hitRate($v, $all7))
            );
        }

        printf("\n   per CALL (one sample per confirmed change)\n");
        printf("   %-13s %5s | %8s %8s %7s | %7s | %9s\n", 'verdict', 'calls', 'mean30', 'med30', 'hit30', 'hit7', 'adverse30');
        foreach ($verdicts as $v) {
            $cs = array_filter($calls, static fn ($x) => $x['verdict'] === $v);
            $r30 = array_values(array_filter(array_column($cs, 'ret30'), static fn ($r) => $r !== null));
            $adv = array_values(array_filter(array_column($cs, 'adverse'), static fn ($r) => $r !== null));
            printf(
                "   %-13s %5d | %8s %8s %7s | %7s | %9s\n",
                $v, count($cs), pct($r30 ? array_sum($r30) / count($r30) : null), pct(median($r30)), pct(hitRate($v, $r30)),
                $v === MO::NEUTRAL ? '   n/a' : pct(hitRate($v, array_column($cs, 'ret7'))),
                pct(median($adv))
            );
        }

        $reversals = count(array_filter($calls, static fn ($x) => abs(MO::RANK[$x['verdict']]) === 2 && $x['prev'] !== null && MO::RANK[$x['prev']] === -MO::RANK[$x['verdict']]));
        $forming = $promoted = 0;
        $lead = [];
        foreach ($calls as $k => $c) {
            if (abs(MO::RANK[$c['verdict']]) !== 1) {
                continue;
            }
            $forming++;
            $want = MO::RANK[$c['verdict']] * 2;
            for ($j = $k + 1; isset($calls[$j]) && $calls[$j]['i'] - $c['i'] <= 21; $j++) {
                if (MO::RANK[$calls[$j]['verdict']] === $want) {
                    $promoted++;
                    $lead[] = $calls[$j]['i'] - $c['i'];
                    break;
                }
            }
        }
        $results[$symbol][$name]['_flips'] = count($calls) / $years;
        $results[$symbol][$name]['_reversals'] = $reversals / $years;
        $results[$symbol][$name]['_promoted'] = $forming ? $promoted / $forming : null;
        printf(
            "   flips %.1f/yr · direct MAJOR reversals %.1f/yr · FORMING→MAJOR within 21d %s (%d/%d, median lead %s d)\n",
            count($calls) / $years, $reversals / $years, pct($forming ? $promoted / $forming : null), $promoted, $forming,
            $lead ? (string) median($lead) : 'n/a'
        );
    }

    $now = end($w['days']);
    $lastCall = end($w['calls']) ?: null;
    printf("\n   now: %s @ %.2f%s\n", $now['verdict'] ?? 'none', $now['price'], $lastCall ? sprintf(' (since %s, called @ %.2f)', gmdate('Y-m-d', $lastCall['t']), $lastCall['price']) : '');
}

// ---------------------------------------------------------------- acceptance
echo "\n================ acceptance ================\n";
$fail = ['major' => [], 'all' => []];
foreach ($results as $symbol => $bySlice) {
    foreach ([MO::MAJOR_UP, MO::MAJOR_DOWN, MO::UP_FORMING, MO::DOWN_FORMING] as $v) {
        $tier = abs(MO::RANK[$v]) === 2 ? 'major' : 'all';
        $full = $bySlice['full'][$v] ?? null;
        $edge = $tier === 'major' ? 0.10 : 0.0;
        if (!$full || $full['hit30'] === null || $full['hit30'] < $full['base30'] + $edge) {
            $fail[$tier][] = sprintf('%s %s full: hit30 %s vs base %s (+%dpp needed)', $symbol, $v, pct($full['hit30'] ?? null), pct($full['base30'] ?? null), $edge * 100);
        }
        if ($tier !== 'major') {
            continue;
        }
        foreach (['bull2024', 'bear2026'] as $s) {
            $cell = $bySlice[$s][$v] ?? null;
            if ($cell && $cell['n'] >= 20 && $cell['hit30'] < $cell['base30'] - 0.05) {
                $fail['major'][] = sprintf('%s %s %s: hit30 %s vs base %s (n=%d)', $symbol, $v, $s, pct($cell['hit30']), pct($cell['base30']), $cell['n']);
            } elseif ($cell && $cell['n'] < 20) {
                printf("  (insufficient) %s %s %s n=%d\n", $symbol, $v, $s, $cell['n']);
            }
        }
    }
    $f = $bySlice['full'];
    if ($f['_flips'] > 12) {
        $fail['major'][] = sprintf('%s flips %.1f/yr > 12', $symbol, $f['_flips']);
    }
    if ($f['_reversals'] > 2) {
        $fail['major'][] = sprintf('%s direct MAJOR reversals %.1f/yr > 2', $symbol, $f['_reversals']);
    }
    if ($f['_promoted'] === null || $f['_promoted'] < 0.40) {
        $fail['all'][] = sprintf('%s FORMING promoted %s < 40%%', $symbol, pct($f['_promoted']));
    }
}
foreach ($fail as $tier => $why) {
    foreach ($why as $line) {
        echo "  FAIL[$tier] $line\n";
    }
}
$level = $fail['major'] ? 'off' : ($fail['all'] ? 'major' : 'all');
echo "\n  => gtbot_outlook_alerts = $level\n";
