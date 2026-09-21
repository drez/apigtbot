<?php
/**
 * Signal hunt for the long-horizon outlook. outlook-sweep.php showed the
 * 1d+1w agreement verdict DETECTS a major trend and predicts nothing (MAJOR_UP
 * days 51% up at 30d vs a 56% base on BTC; MAJOR_DOWN anti-predictive on both
 * symbols). Before any plumbing is built, this asks the wider question: is
 * there ANY daily/weekly state with forward directional skill that holds on
 * both symbols and in both halves of history?
 *
 * For every candidate state it reports, per symbol, the days in state and —
 * at 30/60/90d — Δup (P(fwd>0 | state) − P(fwd>0) unconditional, in pp) and
 * Δmean (mean fwd return − unconditional mean, pp). Stability columns repeat
 * Δup@60d on the first/second half of history and the bull2024 / bear2026
 * tapes. Days overlap, so n is inflated: eff = days / 60 is the honest
 * sample size, and crypto has had ~3 cycles in this window — a cycle-level
 * signal has n ≈ 3 whatever the table says.
 *
 * PASS (printed last) = |Δup@60d| >= 8pp with the SAME sign on both symbols
 * AND in both halves of each, eff >= 5 everywhere. Anything that passes is a
 * candidate to re-test, not a result.
 *
 * Uses the tmp/outlook-{SYMBOL}-1d.json caches outlook-sweep.php writes.
 * CLI: php scripts/outlook-research.php [SYMBOL ...]   Read-only.
 */

const H = [30, 60, 90];
const WARM = 400;

$symbols = array_slice($argv, 1) ?: ['BTCUSDT', 'BNBUSDT'];

function load(string $symbol): array
{
    $f = sprintf('%s/tmp/outlook-%s-1d.json', dirname(__DIR__), $symbol);
    if (!is_file($f)) {
        fwrite(STDERR, "missing $f — run scripts/outlook-sweep.php first\n");
        exit(2);
    }
    return json_decode((string) file_get_contents($f), true);
}

function sma(array $v, int $p): array
{
    $out = [];
    $sum = 0.0;
    foreach ($v as $i => $x) {
        $sum += $x;
        if ($i >= $p) {
            $sum -= $v[$i - $p];
        }
        $out[$i] = $i >= $p - 1 ? $sum / $p : null;
    }
    return $out;
}

function emaSeries(array $v, int $p): array
{
    $out = [];
    $k = 2.0 / ($p + 1);
    $e = null;
    foreach ($v as $i => $x) {
        if ($i < $p - 1) {
            $out[$i] = null;
            continue;
        }
        $e = $e === null ? array_sum(array_slice($v, 0, $p)) / $p : $x * $k + $e * (1 - $k);
        $out[$i] = $e;
    }
    return $out;
}

function rsiSeries(array $v, int $p): array
{
    $out = array_fill(0, count($v), null);
    $g = $l = 0.0;
    for ($i = 1; $i < count($v); $i++) {
        $d = $v[$i] - $v[$i - 1];
        if ($i <= $p) {
            $g += max($d, 0) / $p;
            $l += max(-$d, 0) / $p;
        } else {
            $g = ($g * ($p - 1) + max($d, 0)) / $p;
            $l = ($l * ($p - 1) + max(-$d, 0)) / $p;
        }
        if ($i >= $p) {
            $out[$i] = $l == 0.0 ? 100.0 : 100 - 100 / (1 + $g / $l);
        }
    }
    return $out;
}

function rollMax(array $v, int $p): array
{
    $out = [];
    foreach ($v as $i => $_) {
        $out[$i] = $i >= $p - 1 ? max(array_slice($v, $i - $p + 1, $p)) : null;
    }
    return $out;
}

function rollMin(array $v, int $p): array
{
    $out = [];
    foreach ($v as $i => $_) {
        $out[$i] = $i >= $p - 1 ? min(array_slice($v, $i - $p + 1, $p)) : null;
    }
    return $out;
}

/** day-indexed features. @return array<string, array<int, ?float>> */
function featuresOf(array $d): array
{
    $c = array_column($d, 'close');
    $n = count($c);
    $f = ['close' => $c];
    foreach ([50, 111, 140, 200, 350] as $p) {
        $f["sma$p"] = sma($c, $p);
    }
    $f['ema147'] = emaSeries($c, 147);   // ≈ 21-week EMA
    $f['hi365'] = rollMax(array_column($d, 'high'), 365);
    $f['lo365'] = rollMin(array_column($d, 'low'), 365);
    $f['hi140'] = rollMax(array_column($d, 'high'), 140);
    $f['rsi98'] = rsiSeries($c, 98);     // ≈ 14-week RSI
    $ath = 0.0;
    foreach ($d as $i => $bar) {
        $ath = max($ath, $bar['high']);
        $f['dd'][$i] = $c[$i] / $ath - 1;
    }
    foreach ([30, 90, 180, 365] as $p) {
        foreach ($c as $i => $x) {
            $f["mom$p"][$i] = $i >= $p ? $x / $c[$i - $p] - 1 : null;
        }
    }
    // 30d realized vol, ranked over the trailing year
    $lr = [0.0];
    for ($i = 1; $i < $n; $i++) {
        $lr[$i] = log($c[$i] / $c[$i - 1]);
    }
    $vol = [];
    for ($i = 0; $i < $n; $i++) {
        if ($i < 30) {
            $vol[$i] = null;
            continue;
        }
        $w = array_slice($lr, $i - 29, 30);
        $m = array_sum($w) / 30;
        $vol[$i] = sqrt(array_sum(array_map(static fn ($x) => ($x - $m) ** 2, $w)) / 30);
    }
    foreach ($vol as $i => $v) {
        if ($i < 395 || $v === null) {
            $f['volrank'][$i] = null;
            continue;
        }
        $w = array_filter(array_slice($vol, $i - 364, 365), static fn ($x) => $x !== null);
        $f['volrank'][$i] = count(array_filter($w, static fn ($x) => $x <= $v)) / count($w);
    }
    return $f;
}

/** candidate states: name => fn(features, i, btcFeatures|null, btcIndex|null): bool */
function candidates(): array
{
    $g = static fn (array $f, string $k, int $i) => $f[$k][$i] ?? null;
    $slope = static fn (array $f, string $k, int $i, int $back) => isset($f[$k][$i], $f[$k][$i - $back]) ? $f[$k][$i] / $f[$k][$i - $back] - 1 : null;
    return [
        // ---- trend state (the family the outlook already is)
        'px > 200d SMA' => fn ($f, $i) => $g($f, 'sma200', $i) !== null && $f['close'][$i] > $f['sma200'][$i],
        'px < 200d SMA' => fn ($f, $i) => $g($f, 'sma200', $i) !== null && $f['close'][$i] < $f['sma200'][$i],
        '200d SMA rising (30d)' => fn ($f, $i) => ($s = $slope($f, 'sma200', $i, 30)) !== null && $s > 0,
        '200d SMA falling (30d)' => fn ($f, $i) => ($s = $slope($f, 'sma200', $i, 30)) !== null && $s < 0,
        'golden (50d > 200d)' => fn ($f, $i) => $g($f, 'sma200', $i) !== null && $f['sma50'][$i] > $f['sma200'][$i],
        'death (50d < 200d)' => fn ($f, $i) => $g($f, 'sma200', $i) !== null && $f['sma50'][$i] < $f['sma200'][$i],
        'above bull band (20w SMA & 21w EMA)' => fn ($f, $i) => $g($f, 'ema147', $i) !== null && $f['close'][$i] > max($f['sma140'][$i], $f['ema147'][$i]),
        'below bull band' => fn ($f, $i) => $g($f, 'ema147', $i) !== null && $f['close'][$i] < min($f['sma140'][$i], $f['ema147'][$i]),
        // ---- fresh events (first 30 days after a cross) — "entered", not "in"
        'fresh reclaim of 200d (<=30d)' => function ($f, $i) use ($g) {
            if ($g($f, 'sma200', $i - 30) === null || $f['close'][$i] <= $f['sma200'][$i]) {
                return false;
            }
            for ($j = $i - 30; $j < $i; $j++) {
                if ($f['close'][$j] < $f['sma200'][$j]) {
                    return true;
                }
            }
            return false;
        },
        'fresh loss of 200d (<=30d)' => function ($f, $i) use ($g) {
            if ($g($f, 'sma200', $i - 30) === null || $f['close'][$i] >= $f['sma200'][$i]) {
                return false;
            }
            for ($j = $i - 30; $j < $i; $j++) {
                if ($f['close'][$j] > $f['sma200'][$j]) {
                    return true;
                }
            }
            return false;
        },
        // ---- momentum
        'mom 90d > 0' => fn ($f, $i) => ($m = $g($f, 'mom90', $i)) !== null && $m > 0,
        'mom 90d < 0' => fn ($f, $i) => ($m = $g($f, 'mom90', $i)) !== null && $m < 0,
        'mom 180d > +50%' => fn ($f, $i) => ($m = $g($f, 'mom180', $i)) !== null && $m > 0.5,
        'mom 180d < -30%' => fn ($f, $i) => ($m = $g($f, 'mom180', $i)) !== null && $m < -0.3,
        'mom 365d > 0' => fn ($f, $i) => ($m = $g($f, 'mom365', $i)) !== null && $m > 0,
        'mom 365d < 0' => fn ($f, $i) => ($m = $g($f, 'mom365', $i)) !== null && $m < 0,
        // ---- breakouts
        'within 5% of 365d high' => fn ($f, $i) => $g($f, 'hi365', $i) !== null && $f['close'][$i] >= 0.95 * $f['hi365'][$i],
        'within 10% of 365d low' => fn ($f, $i) => $g($f, 'lo365', $i) !== null && $f['close'][$i] <= 1.10 * $f['lo365'][$i],
        'new 20w high today' => fn ($f, $i) => $g($f, 'hi140', $i - 1) !== null && $f['close'][$i] > $f['hi140'][$i - 1],
        // ---- stretch / mean reversion
        'Mayer > 1.8 (px/200d)' => fn ($f, $i) => $g($f, 'sma200', $i) !== null && $f['close'][$i] / $f['sma200'][$i] > 1.8,
        'Mayer 1.0-1.4' => fn ($f, $i) => $g($f, 'sma200', $i) !== null && ($m = $f['close'][$i] / $f['sma200'][$i]) >= 1.0 && $m <= 1.4,
        'Mayer < 0.7' => fn ($f, $i) => $g($f, 'sma200', $i) !== null && $f['close'][$i] / $f['sma200'][$i] < 0.7,
        'drawdown from ATH < 15%' => fn ($f, $i) => $f['dd'][$i] > -0.15,
        'drawdown 15-40%' => fn ($f, $i) => $f['dd'][$i] <= -0.15 && $f['dd'][$i] > -0.40,
        'drawdown 40-60%' => fn ($f, $i) => $f['dd'][$i] <= -0.40 && $f['dd'][$i] > -0.60,
        'drawdown > 60%' => fn ($f, $i) => $f['dd'][$i] <= -0.60,
        'weekly RSI > 70' => fn ($f, $i) => ($r = $g($f, 'rsi98', $i)) !== null && $r > 70,
        'weekly RSI < 40' => fn ($f, $i) => ($r = $g($f, 'rsi98', $i)) !== null && $r < 40,
        'pi-cycle (111d > 2x350d)' => fn ($f, $i) => $g($f, 'sma350', $i) !== null && $f['sma111'][$i] > 2 * $f['sma350'][$i],
        // ---- volatility
        'vol rank < 10% (compressed)' => fn ($f, $i) => ($v = $g($f, 'volrank', $i)) !== null && $v < 0.10,
        'vol rank > 90%' => fn ($f, $i) => ($v = $g($f, 'volrank', $i)) !== null && $v > 0.90,
        // ---- combos
        'px>200d AND 200d rising AND mom90>0' => fn ($f, $i) => $g($f, 'sma200', $i - 30) !== null && $f['close'][$i] > $f['sma200'][$i] && $f['sma200'][$i] > $f['sma200'][$i - 30] && ($f['mom90'][$i] ?? 0) > 0,
        'px<200d AND 200d falling AND mom90<0' => fn ($f, $i) => $g($f, 'sma200', $i - 30) !== null && $f['close'][$i] < $f['sma200'][$i] && $f['sma200'][$i] < $f['sma200'][$i - 30] && ($f['mom90'][$i] ?? 0) < 0,
        'px>200d AND drawdown > 40% (early recovery)' => fn ($f, $i) => $g($f, 'sma200', $i) !== null && $f['close'][$i] > $f['sma200'][$i] && $f['dd'][$i] <= -0.40,
        'px<200d AND drawdown < 40% (early breakdown)' => fn ($f, $i) => $g($f, 'sma200', $i) !== null && $f['close'][$i] < $f['sma200'][$i] && $f['dd'][$i] > -0.40,
        // ---- BTC leads (evaluated on the BTC features, scored on this symbol's returns)
        'BTC px > 200d SMA [lead]' => fn ($f, $i, $b, $bi) => $b !== null && $bi !== null && ($b['sma200'][$bi] ?? null) !== null && $b['close'][$bi] > $b['sma200'][$bi],
        'BTC mom 90d < 0 [lead]' => fn ($f, $i, $b, $bi) => $b !== null && $bi !== null && ($b['mom90'][$bi] ?? null) !== null && $b['mom90'][$bi] < 0,
    ];
}

/** Δup / Δmean of the days in $idx vs the unconditional days in $all, at horizon $h. */
function edge(array $c, array $idx, array $all, int $h): ?array
{
    $fwd = static function (array $ix) use ($c, $h): array {
        $r = [];
        foreach ($ix as $i) {
            if (isset($c[$i + $h])) {
                $r[] = $c[$i + $h] / $c[$i] - 1;
            }
        }
        return $r;
    };
    $s = $fwd($idx);
    $u = $fwd($all);
    if (count($s) < 1 || !$u) {
        return null;
    }
    $up = static fn (array $r) => count(array_filter($r, static fn ($x) => $x > 0)) / count($r);
    return [
        'n' => count($s),
        'dup' => ($up($s) - $up($u)) * 100,
        'dmean' => (array_sum($s) / count($s) - array_sum($u) / count($u)) * 100,
    ];
}

$btc = in_array('BTCUSDT', $symbols, true) ? load('BTCUSDT') : null;
$btcF = $btc ? featuresOf($btc) : null;
$btcIdx = $btc ? array_flip(array_column($btc, 't')) : [];
$cands = candidates();
$score = []; // [name][symbol] = ['full'=>dup60, 'h1'=>, 'h2'=>, 'eff'=>]

foreach ($symbols as $symbol) {
    $d = load($symbol);
    $f = featuresOf($d);
    $c = $f['close'];
    $n = count($d);
    $all = range(WARM, $n - 1);
    $mid = WARM + intdiv($n - WARM, 2);
    $slice = static fn (string $from, string $to) => array_values(array_filter($all, static fn ($i) => $d[$i]['t'] >= strtotime("$from UTC") && $d[$i]['t'] < strtotime("$to UTC")));
    $parts = [
        'H1' => range(WARM, $mid - 1),
        'H2' => range($mid, $n - 1),
        'bull' => $slice('2023-11-01', '2025-01-01'),
        'bear' => $slice('2025-10-01', '2026-07-31'),
    ];

    printf("\n================ %s — %d days scored, halves split at %s ================\n", $symbol, count($all), gmdate('Y-m-d', $d[$mid]['t']));
    printf("%-46s %5s %4s | %6s %6s | %6s %6s | %6s %6s || %5s %5s %5s %5s\n", 'state', 'days', 'eff', 'Δup30', 'Δmn30', 'Δup60', 'Δmn60', 'Δup90', 'Δmn90', 'H1', 'H2', 'bull', 'bear');
    foreach ($cands as $name => $fn) {
        $in = [];
        foreach ($all as $i) {
            $bi = $btcF ? ($btcIdx[$d[$i]['t']] ?? null) : null;
            if ($fn($f, $i, $btcF, $bi)) {
                $in[] = $i;
            }
        }
        $cols = [];
        foreach (H as $h) {
            $e = edge($c, $in, $all, $h);
            $cols[] = $e ? sprintf('%+6.1f %+6.0f', $e['dup'], $e['dmean']) : sprintf('%6s %6s', 'n/a', 'n/a');
        }
        $stab = [];
        $sv = [];
        foreach ($parts as $k => $ix) {
            $sub = array_values(array_intersect($in, $ix));
            $e = count($sub) >= 60 ? edge($c, $sub, $ix, 60) : null;
            $sv[$k] = $e ? ['dup' => $e['dup'], 'eff' => $e['n'] / 60] : null;
            $stab[] = $e ? sprintf('%+5.0f', $e['dup']) : '    -';
        }
        $e60 = edge($c, $in, $all, 60);
        $score[$name][$symbol] = ['full' => $e60['dup'] ?? null, 'h1' => $sv['H1'], 'h2' => $sv['H2'], 'eff' => count($in) / 60];
        printf("%-46s %5d %4.0f | %s | %s | %s || %s\n", $name, count($in), count($in) / 60, $cols[0], $cols[1], $cols[2], implode(' ', $stab));
    }
}

echo "\n================ PASS: |Δup@60d| >= 8pp, same sign on every symbol and both halves, eff >= 5 ================\n";
$any = false;
foreach ($score as $name => $bySym) {
    if (count($bySym) < count($symbols)) {
        continue;
    }
    $signs = [];
    $ok = true;
    foreach ($bySym as $s) {
        foreach ([$s['full'], $s['h1']['dup'] ?? null, $s['h2']['dup'] ?? null] as $v) {
            if ($v === null || abs($v) < 8) {
                $ok = false;
                break 2;
            }
            $signs[] = $v <=> 0;
        }
        if (($s['h1']['eff'] ?? 0) < 5 || ($s['h2']['eff'] ?? 0) < 5) {
            $ok = false;
            break;
        }
    }
    if ($ok && count(array_unique($signs)) === 1) {
        $any = true;
        printf("  %-46s %s\n", $name, implode('  ', array_map(static fn ($k, $s) => sprintf('%s %+.0f (H1 %+.0f, H2 %+.0f)', substr($k, 0, 3), $s['full'], $s['h1']['dup'], $s['h2']['dup']), array_keys($bySym), $bySym)));
    }
}
echo $any ? '' : "  none\n";
