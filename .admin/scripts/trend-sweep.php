<?php
/**
 * Walk-forward validation for the TrendEngine decision core (task 6 of the
 * trend-algo-switch spec): does the Donchian-breakout + ATR-trailing-stop
 * engine actually make money over the data the DAEMON ITSELF sees, and
 * which (donchian_period, atr_stop_mult) cell holds up?
 *
 * Deliberately reuses the engine's own PURE statics rather than a parallel
 * reimplementation — TrendEngine::entrySignal (breakout + EMA alignment),
 * TrendEngine::ratchet (ATR trailing stop, never moves down) and
 * TrendEngine::cooldownElapsed (re-entry gate) — and the exact data source
 * the live engine reads: MarketStore::candles(symbol, tf), the same bounded
 * (KEEP=300) window Daemon::tryEnter/currentAtr call every tick. This is a
 * REAL constraint, not an oversight: on a young or recently-reset run
 * MarketStore's window can be well under sweep-grade size, and the report
 * says so explicitly (see the short-history warning below) rather than
 * dressing up a thin sample as a verdict.
 *
 * Simulation per (symbol, tf, donchian, atr_stop_mult) cell — single
 * position at a time, mirrors Daemon::tick's mutually exclusive branches:
 *   flat:  entrySignal(candles[0..i], donchian, emaFast=20, emaSlow=50) on
 *          a growing window (the tf's full stored history is the only tape
 *          available; this is an expanding-window walk, not a rolling
 *          fit/test split — there's no spare history to hold out). Gated by
 *          TrendEngine::cooldownElapsed off the last stop-out.
 *   entry: single fill at the bar's close (no tranching), qty sized off a
 *          flat $1000 budget, fee 0.1% charged on the entry notional.
 *          Initial stop = entry - atr_initial_mult × ATR(atr_period), where
 *          atr_initial_mult follows the SAME profile ladder ProfilePolicy
 *          uses for the matching atr_stop_mult (2.0->1.5, 3.0->2.0,
 *          4.0->2.5, 5.0->3.0) rather than inventing a second sweep axis.
 *   held:  TrendEngine::ratchet() every bar (ATR recomputed over
 *          candles[0..i], exactly like Daemon::currentAtr()) — off the
 *          bar's HIGH, not its close (see "Intrabar assumptions" below);
 *          exit when the bar's low touches the (post-ratchet) stop —
 *          filled AT the stop, unless the bar's close is already below the
 *          stop (a gap through the level, which these OHLC-less-open
 *          candles can't resolve more precisely — filled at close instead,
 *          i.e. the more conservative price), fee 0.1% on the exit notional.
 *
 * Intrabar assumptions: MarketStore's stored candles carry high/low/close
 * only (no open, no tick path), so the price's route WITHIN a bar is
 * unknown and has to be assumed. This sim ratchets TrendEngine::ratchet()
 * off the bar's HIGH and only then tests the bar's LOW for a stop breach —
 * i.e. it assumes the high is reached before the low within every held bar
 * (the OPTIMISTIC-trail ordering: the stop gets to trail as tight as that
 * bar allows before the breach check runs). Production ratchets on live
 * ticks, which can and do reach the bar's high before a subsequent low —
 * so this is the closer of the two OHLC-only approximations to production,
 * but still not exact: a bar that actually printed low-before-high would
 * ratchet less than this sim assumes. Net effect versus ratcheting on
 * close (an earlier draft of this script did that): stops trail TIGHTER
 * here, which means MORE breaches and LOWER net on stop-heavy cells than a
 * close-ratcheted sim would show — this is the more conservative of the
 * two, not the more flattering one.
 *
 * CLI: php scripts/trend-sweep.php [SYMBOL] [TF] [--stale-ok]
 *   no args    -> BTCUSDT and BNBUSDT, both on 1h
 *   SYMBOL     -> that symbol only, 1h
 *   SYMBOL TF  -> that symbol/timeframe only
 *   --stale-ok -> permit a cache snapshot (see below) older than 48h
 *
 * Run from <project>/.admin (the CLI form above) — locally if MarketStore
 * already holds real history for the symbol/tf, otherwise scp this file to
 * prod and run it there read-only (SELECT-only; MarketStore::candles never
 * writes) via the established scp-report pattern. If prod's deployed
 * autoload predates TrendEngine (as it does while this task is still
 * in-branch — Engine\TrendEngine ships in task 7), the simulation itself
 * can't run there: instead scp a tiny read-only MarketStore::candles() dump
 * DOWN from prod into tmp/trend-sweep-cache/{SYMBOL}_{tf}.json (one JSON
 * object {symbol,tf,candles} per file) and run this script locally against
 * that snapshot — checked here before falling back to the live local DB.
 * Every run prints which source served each combo (cache file + mtime/age,
 * or live MarketStore) — never silent about it — and a cache file older
 * than 48h is refused (not silently used) unless --stale-ok is passed.
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Domains\Bot\Engine\TrendEngine;
use App\Domains\Bot\Indicators;
use App\Domains\Bot\MarketStore;

$admin = __DIR__ . '/..';
(new \Ahc\Env\Loader())->load($admin . '/.env');
require $admin . '/config/Built/config.php';
require $admin . '/config/Built/propel.php';

const BUDGET = 1000.0;
const FEE_PCT = '0.001'; // 0.1% per side
const EMA_FAST = 20;
const EMA_SLOW = 50;
const ATR_PERIOD = 14;
const SCALE = 8;
const SHORT_HISTORY_BARS = 200;
const TF_SECONDS = ['15m' => 900, '1h' => 3600, '4h' => 14400, '1d' => 86400];

$DONCHIANS = [10, 20, 30];
$STOP_MULTS = ['2', '3', '4', '5'];
// mirrors ProfilePolicy::TREND_RULES — same atr_stop_mult values, so the
// matching initial-stop/cooldown ladder is reused rather than re-guessed.
$STOP_LADDER = [
    '2' => ['initial' => '1.5', 'cooldown' => 6],
    '3' => ['initial' => '2.0', 'cooldown' => 3],
    '4' => ['initial' => '2.5', 'cooldown' => 1],
    '5' => ['initial' => '3.0', 'cooldown' => 0],
];

const CACHE_MAX_AGE_HOURS = 48;

$staleOk = false;
$positional = [];
foreach (array_slice($argv, 1) as $a) {
    if ($a === '--stale-ok') {
        $staleOk = true;
        continue;
    }
    $positional[] = $a;
}
$argSymbol = $positional[0] ?? null;
$argTf = $positional[1] ?? null;
$symbols = $argSymbol !== null ? [strtoupper($argSymbol)] : ['BTCUSDT', 'BNBUSDT'];
$tfs = $argTf !== null ? [$argTf] : ['1h'];

/** Synthetic evenly-spaced timestamp for bar $i — only the SPACING matters
 *  (cooldownElapsed diffs two of these), not the wall-clock value itself. */
function barTime(int $i, string $tf): string
{
    $sec = TF_SECONDS[$tf] ?? 3600;
    return date('Y-m-d H:i:s', 1577836800 + $i * $sec); // 2020-01-01 + i bars
}

/**
 * Walk one (donchian, atr_stop_mult) cell over the full candle sequence.
 * @param array<int,array{high:string,low:string,close:string}> $candles
 */
function simulate(array $candles, int $donchian, string $stopMult, string $initialMult, int $cooldownBars, string $tf): array
{
    $n = count($candles);
    $highs = array_column($candles, 'high');
    $lows = array_column($candles, 'low');
    $closes = array_column($candles, 'close');

    $position = null;   // ['entry'=>, 'qty'=>, 'hwm'=>, 'stop'=>, 'entryFee'=>]
    $stopOutAt = null;  // synthetic timestamp of the last stop-out
    $realizedTotal = '0';
    $trades = [];       // realized pnl per closed trade
    $equity = [];        // per-bar mark, for drawdown

    for ($i = 0; $i < $n; $i++) {
        $close = $closes[$i];
        $low = $lows[$i];
        $high = $highs[$i];

        if ($position === null) {
            $mark = $realizedTotal;
        } else {
            $unrealGross = bcmul(bcsub($close, $position['entry'], SCALE), $position['qty'], SCALE);
            $mark = bcadd($realizedTotal, bcsub($unrealGross, $position['entryFee'], SCALE), SCALE);
        }
        $equity[] = (float) $mark;

        if ($position !== null) {
            $atr = $i >= 1
                ? Indicators::atr(array_slice($highs, 0, $i + 1), array_slice($lows, 0, $i + 1), array_slice($closes, 0, $i + 1), ATR_PERIOD)
                : 0.0;
            if ($atr > 0) {
                // OPTIMISTIC-trail: ratchet off the bar's HIGH (assumes
                // high-before-low within the bar) before testing the LOW
                // for a breach below — see "Intrabar assumptions" in the
                // file header for why and what this biases.
                $ratcheted = TrendEngine::ratchet(
                    ['entry' => $position['entry'], 'hwm' => $position['hwm'], 'stop' => $position['stop']],
                    $high,
                    $stopMult,
                    number_format($atr, SCALE, '.', '')
                );
                $position['hwm'] = $ratcheted['hwm'];
                $position['stop'] = $ratcheted['stop'];
            }

            if (bccomp($low, $position['stop'], SCALE) <= 0) {
                // breach: fill at the stop, or at the (worse) close if the
                // bar's own close already sits below it (gap proxy — these
                // candles carry no open, so close-below-stop is the closest
                // available signal that the level was jumped, not touched)
                $fill = bccomp($close, $position['stop'], SCALE) < 0 ? $close : $position['stop'];
                $exitFee = bcmul(bcmul($position['qty'], $fill, SCALE), FEE_PCT, SCALE);
                $gross = bcmul(bcsub($fill, $position['entry'], SCALE), $position['qty'], SCALE);
                $realized = bcsub($gross, bcadd($position['entryFee'], $exitFee, SCALE), SCALE);
                $realizedTotal = bcadd($realizedTotal, $realized, SCALE);
                $trades[] = (float) $realized;
                $stopOutAt = barTime($i, $tf);
                $position = null;
            }
            continue;
        }

        // flat: cooldown gate, then the breakout signal, exactly like
        // Daemon::tryEnter (cooldown checked first, entries refused while
        // it's live regardless of what the signal says)
        if (!TrendEngine::cooldownElapsed($stopOutAt, $cooldownBars, $tf, barTime($i, $tf))) {
            continue;
        }
        $window = array_slice($candles, 0, $i + 1);
        if (!TrendEngine::entrySignal($window, $donchian, EMA_FAST, EMA_SLOW)) {
            continue;
        }
        $atr = Indicators::atr(array_slice($highs, 0, $i + 1), array_slice($lows, 0, $i + 1), array_slice($closes, 0, $i + 1), ATR_PERIOD);
        if ($atr <= 0) {
            continue; // can't size an initial stop off a zero ATR
        }
        $entry = $close;
        $qty = bcdiv((string) BUDGET, $entry, SCALE);
        $entryFee = bcmul(bcmul($qty, $entry, SCALE), FEE_PCT, SCALE);
        $stop = bcsub($entry, bcmul($initialMult, number_format($atr, SCALE, '.', ''), SCALE), SCALE);
        $position = ['entry' => $entry, 'qty' => $qty, 'hwm' => $entry, 'stop' => $stop, 'entryFee' => $entryFee];
    }

    // mark any still-open position at the final close (unrealized, not a trade)
    $finalMark = $realizedTotal;
    if ($position !== null) {
        $unrealGross = bcmul(bcsub(end($closes), $position['entry'], SCALE), $position['qty'], SCALE);
        $finalMark = bcadd($realizedTotal, bcsub($unrealGross, $position['entryFee'], SCALE), SCALE);
    }

    $peak = -INF;
    $maxDd = 0.0;
    foreach ($equity as $e) {
        $peak = max($peak, $e);
        $maxDd = max($maxDd, $peak - $e);
    }

    return [
        'net' => (float) $finalMark,
        'trades' => count($trades),
        'wins' => count(array_filter($trades, fn ($t) => $t > 0)),
        'max_dd' => $maxDd, // dollars, off a $1000 budget
        'open_at_end' => $position !== null,
    ];
}

foreach ($symbols as $symbol) {
    foreach ($tfs as $tf) {
        $cacheFile = $admin . "/tmp/trend-sweep-cache/{$symbol}_{$tf}.json";
        if (is_file($cacheFile)) {
            $mtime = (int) filemtime($cacheFile);
            $ageHours = (time() - $mtime) / 3600;
            $mtimeLabel = gmdate('Y-m-d H:i:s', $mtime) . ' UTC';
            if ($ageHours > CACHE_MAX_AGE_HOURS && !$staleOk) {
                printf(
                    "═══ %s %s — REFUSED: cache %s is %.1fh old (mtime %s, >%dh limit) — pass --stale-ok to use it anyway ═══\n\n",
                    $symbol,
                    $tf,
                    $cacheFile,
                    $ageHours,
                    $mtimeLabel,
                    CACHE_MAX_AGE_HOURS
                );
                continue;
            }
            printf(
                "[data source: cache %s — mtime %s, age %.1fh%s]\n",
                $cacheFile,
                $mtimeLabel,
                $ageHours,
                $ageHours > CACHE_MAX_AGE_HOURS ? ' — STALE, allowed via --stale-ok' : ''
            );
            $cached = json_decode((string) file_get_contents($cacheFile), true);
            $candles = is_array($cached['candles'] ?? null) ? $cached['candles'] : [];
        } else {
            printf("[data source: live MarketStore::candles(%s, %s)]\n", $symbol, $tf);
            $candles = MarketStore::candles($symbol, $tf);
        }
        $n = count($candles);
        printf("═══ %s %s — %d stored candles ═══\n", $symbol, $tf, $n);
        if ($n === 0) {
            echo "  no candle data available for this symbol/tf — skipped\n\n";
            continue;
        }
        if ($n < SHORT_HISTORY_BARS) {
            printf("  ** short-history: directional only ** (%d bars < %d — MarketStore's bounded window; not sweep-grade evidence)\n", $n, SHORT_HISTORY_BARS);
        }
        $sec = TF_SECONDS[$tf] ?? 3600;
        $days = ($n - 1) * $sec / 86400;
        $firstClose = (float) $candles[0]['close'];
        $lastClose = (float) end($candles)['close'];
        $buyHold = ($lastClose - $firstClose) / $firstClose * BUDGET;
        printf("  span: %.1f days · buy-and-hold on the same window: %+.2f/1k · flat USDT: 0.00/1k\n\n", $days, $buyHold);

        printf("  %-9s %-9s %10s %12s %9s %8s %6s %10s %10s\n",
            'donchian', 'stopMult', 'net/1k', 'net/1k/day', 'maxDD', 'trades', 'win%', 'vs B&H', 'vs flat');
        foreach ($DONCHIANS as $donchian) {
            foreach ($STOP_MULTS as $stopMult) {
                $ladder = $STOP_LADDER[$stopMult];
                $r = simulate($candles, $donchian, $stopMult, $ladder['initial'], $ladder['cooldown'], $tf);
                $perDay = $days > 0 ? $r['net'] / $days : 0.0;
                $winPct = $r['trades'] > 0 ? $r['wins'] / $r['trades'] * 100 : 0.0;
                printf("  %-9d %-9s %10.2f %12.3f %9.2f %8d %5.0f%% %10.2f %10.2f%s\n",
                    $donchian,
                    $stopMult . 'x',
                    $r['net'],
                    $perDay,
                    $r['max_dd'],
                    $r['trades'],
                    $winPct,
                    $r['net'] - $buyHold,
                    $r['net'],
                    $r['open_at_end'] ? ' *' : ''
                );
            }
        }
        echo "\n  (* position still open at the last stored candle — net includes its unrealized mark)\n\n";
    }
}
