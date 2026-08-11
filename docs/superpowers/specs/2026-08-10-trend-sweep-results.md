# Trend engine walk-forward sweep — results (2026-08-10)

Script: `.admin/scripts/trend-sweep.php` — walks `TrendEngine::entrySignal` /
`ratchet` / `cooldownElapsed` (the real pure decision statics, not a
reimplementation) bar-by-bar over a candle sequence: single fill at close on
signal, ATR trailing-stop ratchet every bar, exit at stop breach (fill at the
stop, or at the bar's close if it already gapped below), fee 0.1%/side,
cooldown gated between cycles. Grid: `donchian_period ∈ {10, 20, 30} ×
atr_stop_mult ∈ {2, 3, 4, 5}` (initial-stop mult and cooldown bars follow the
same ladder `ProfilePolicy::TREND_RULES` uses for each `atr_stop_mult`, so
the sweep isn't inventing a second free parameter). `donchian_period` doesn't
change stop behavior once fewer signals fire, which is why several 10/20/30
rows are identical at higher `atr_stop_mult` — the same single trade fired
regardless of the breakout window.

## Intrabar assumptions (read this before the numbers)

`MarketStore`'s stored candles carry high/low/close only — no open, no tick
path — so the price's route *within* a bar has to be assumed. This sim
ratchets `TrendEngine::ratchet()` off the bar's **HIGH**, then tests the
bar's **LOW** for a stop breach — i.e. it assumes the high is reached before
the low within every held bar (the **OPTIMISTIC-trail** ordering: the stop
gets to trail as tight as that bar allows before the breach check runs).
Production ratchets on live ticks, which can and do reach the bar's high
before a later low, so this is the closer of the two OHLC-only
approximations to production — but still not exact: a bar that actually
printed low-before-high would ratchet less than this sim assumes. An earlier
draft of this script ratcheted off the bar's *close* instead, which trails
looser and is the more optimistic (flattering) approximation; the numbers
below are from the high-ratchet version and are systematically *tighter* —
more breaches, lower net on stop-heavy cells — than that draft produced.

## Data note

**Data source: prod, read-only, snapshotted down.** `MarketStore::candles()`
is capped at 300 bars per (symbol, tf) (`MarketStore::KEEP`), and prod's
deployed autoload predates `Engine\TrendEngine` (task 7 ships it) — so the
simulation itself can't run against a live prod process. Instead a tiny
read-only `MarketStore::candles()` dump was scp'd to prod, run there
(`SELECT`-only, no writes), and the JSON snapshot pulled back down to run the
actual sweep locally against real code, cached under
`.admin/tmp/trend-sweep-cache/{SYMBOL}_{tf}.json` (gitignored). Local DB
candles were empty for BTCUSDT and stale for BNBUSDT, so **all four
symbol×tf combinations below use the same prod snapshot** for consistency.
The script prints its data source (cache file + mtime/age, or live
`MarketStore`) for every combo, and refuses a cache file older than 48h
unless `--stale-ok` is passed — this run's cache was ~6 minutes old.

All four combos landed at exactly 300 bars — the maximum `MarketStore` ever
holds. That clears the script's own `<200 bar` short-history flag, but is
still thin in absolute terms: **12.5 days for 1h, 49.8 days for 4h**, with
only **1–6 completed trades per grid cell**. Nothing here is sweep-grade
statistical evidence — it's a directional smoke test of whether the engine's
mechanics behave sanely and roughly where the defaults sit, not a basis for
picking a profile.

## BTCUSDT 1h — 300 bars, 12.5d span, buy-and-hold +1.51/1k

| donchian | stopMult | net/1k | net/1k/day | maxDD | trades | win% | vs B&H | vs flat |
|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| 10 | 2x | -11.46 | -0.920 | 15.05 | 4 | 25% | -12.97 | -11.46 |
| 10 | 3x | -12.14 | -0.974 | 21.03 | 4 | 0% | -13.65 | -12.14 |
| 10 | 4x | -5.88 | -0.472 | 14.77 | 3 | 33% | -7.40 | -5.88 |
| 10 | 5x | -0.24 | -0.020 | 14.58 | 2 | 50% | -1.76 | -0.24 |
| 20 | 2x | -18.10 | -1.453 | 18.68 | 4 | 0% | -19.61 | -18.10 |
| 20 | 3x | -18.75 | -1.505 | 27.64 | 4 | 0% | -20.26 | -18.75 |
| 20 | 4x | -12.53 | -1.005 | 21.42 | 3 | 0% | -14.04 | -12.53 |
| 20 | 5x | -0.24 | -0.020 | 14.58 | 2 | 50% | -1.76 | -0.24 |
| 30 | 2x | -13.32 | -1.069 | 13.90 | 3 | 0% | -14.83 | -13.32 |
| 30 | 3x | -12.65 | -1.015 | 21.54 | 3 | 0% | -14.16 | -12.65 |
| 30 | 4x | -12.53 | -1.005 | 21.42 | 3 | 0% | -14.04 | -12.53 |
| 30 | 5x | -0.24 | -0.020 | 14.58 | 2 | 50% | -1.76 | -0.24 |

## BTCUSDT 4h — 300 bars, 49.8d span, buy-and-hold -2.34/1k

| donchian | stopMult | net/1k | net/1k/day | maxDD | trades | win% | vs B&H | vs flat |
|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| 10 | 2x | -62.09 | -1.246 | 78.13 | 6 | 0% | -59.74 | -62.09 |
| 10 | 3x | -99.87 | -2.004 | 115.92 | 6 | 0% | -97.53 | -99.87 |
| 10 | 4x | -107.43 | -2.156 | 135.86 | 5 | 0% | -105.09 | -107.43 |
| 10 | 5x | -69.16 | -1.388 | 98.99 | 3 | 0% | -66.82 | -69.16 * |
| 20 | 2x | -48.80 | -0.979 | 64.84 | 5 | 0% | -46.46 | -48.80 |
| 20 | 3x | -81.12 | -1.628 | 97.16 | 5 | 0% | -78.77 | -81.12 |
| 20 | 4x | -74.31 | -1.491 | 102.74 | 4 | 0% | -71.97 | -74.31 |
| 20 | 5x | -84.97 | -1.705 | 114.80 | 3 | 0% | -82.63 | -84.97 * |
| 30 | 2x | -30.59 | -0.614 | 46.63 | 4 | 0% | -28.25 | -30.59 |
| 30 | 3x | -62.80 | -1.260 | 78.85 | 4 | 0% | -60.46 | -62.80 |
| 30 | 4x | -82.99 | -1.665 | 111.42 | 4 | 0% | -80.64 | -82.99 |
| 30 | 5x | -93.33 | -1.873 | 123.17 | 3 | 0% | -90.99 | -93.33 * |

\* position still open at the last stored candle; net includes its unrealized mark.

## BNBUSDT 1h — 300 bars, 12.5d span, buy-and-hold +55.58/1k

| donchian | stopMult | net/1k | net/1k/day | maxDD | trades | win% | vs B&H | vs flat |
|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| 10 | 2x | -17.62 | -1.414 | 18.52 | 6 | 33% | -73.20 | -17.62 |
| 10 | 3x | -5.79 | -0.465 | 19.01 | 5 | 40% | -61.37 | -5.79 |
| 10 | 4x | -17.51 | -1.406 | 22.68 | 5 | 40% | -73.09 | -17.51 |
| 10 | 5x | -17.65 | -1.417 | 27.78 | 4 | 50% | -73.23 | -17.65 |
| 20 | 2x | -14.79 | -1.187 | 19.83 | 5 | 20% | -70.37 | -14.79 |
| 20 | 3x | 0.02 | 0.002 | 16.93 | 4 | 50% | -55.56 | 0.02 |
| 20 | 4x | -11.67 | -0.937 | 21.89 | 4 | 25% | -67.25 | -11.67 |
| 20 | 5x | 1.26 | 0.101 | 27.78 | 3 | 67% | -54.32 | 1.26 |
| 30 | 2x | -12.37 | -0.993 | 17.41 | 4 | 25% | -67.95 | -12.37 |
| 30 | 3x | 5.87 | 0.471 | 16.93 | 3 | 67% | -49.71 | 5.87 |
| 30 | 4x | -3.52 | -0.282 | 21.89 | 3 | 33% | -59.10 | -3.52 |
| 30 | 5x | 12.64 | 1.015 | 21.05 | 2 | 100% | -42.94 | 12.64 |

## BNBUSDT 4h — 300 bars, 49.8d span, buy-and-hold +10.46/1k

| donchian | stopMult | net/1k | net/1k/day | maxDD | trades | win% | vs B&H | vs flat |
|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| 10 | 2x | -15.18 | -0.305 | 51.91 | 5 | 40% | -25.64 | -15.18 |
| 10 | 3x | -62.49 | -1.254 | 84.25 | 5 | 0% | -72.94 | -62.49 |
| 10 | 4x | -84.88 | -1.703 | 111.78 | 4 | 0% | -95.34 | -84.88 * |
| 10 | 5x | 0.13 | 0.003 | 64.15 | 1 | 0% | -10.32 | 0.13 * |
| 20 | 2x | -43.23 | -0.867 | 66.37 | 5 | 0% | -53.68 | -43.23 |
| 20 | 3x | -87.84 | -1.763 | 111.16 | 4 | 0% | -98.30 | -87.84 * |
| 20 | 4x | -109.57 | -2.199 | 132.89 | 4 | 0% | -120.03 | -109.57 * |
| 20 | 5x | 0.13 | 0.003 | 64.15 | 1 | 0% | -10.32 | 0.13 * |
| 30 | 2x | -22.24 | -0.446 | 44.00 | 3 | 0% | -32.69 | -22.24 |
| 30 | 3x | -57.97 | -1.163 | 79.74 | 3 | 0% | -68.43 | -57.97 |
| 30 | 4x | -75.03 | -1.506 | 96.80 | 3 | 0% | -85.49 | -75.03 |
| 30 | 5x | 0.13 | 0.003 | 64.15 | 1 | 0% | -10.32 | 0.13 * |

\* position still open at the last stored candle; net includes its unrealized mark
(the 5x/0.13 cells hold a barely-profitable open position, not a closed win —
win% for that row is 0% because the one completed exit in its history lost).

## Interpretation (honest, 3 lines)

1. **Tightest stops (atr_stop_mult=2) again lost most consistently**, but with the high-ratchet fix, the mid/wide band (3–5x) mostly stopped looking like a winner too: the standout closed-trade winner from the close-ratchet draft (BTC 4h @5x, +4.30/1k, 100% win) flips to -69.16/1k at 0% win once the stop trails off the bar's high instead of its close — that single trade got ratcheted into a breach it previously survived. `donchian_period` still barely matters next to `atr_stop_mult` — identical rows across 10/20/30 are the same surviving (or now non-surviving) trade.
2. **BNB is the only symbol with any near-flat or positive cells left** (1h @20-30/3-5x: +0.02 to +12.64/1k; 4h @*/5x: a barely-positive +0.13/1k held open, not a closed win), and even those are nowhere close to BNB's own buy-and-hold (+55.58/1k on 1h, +10.46/1k on 4h) — every trend-engine cell still loses to just holding BNB. BTC has **no positive cell left at all** on either timeframe after the fix.
3. **The tighter (more realistic) ratchet makes the honest read stronger, not weaker: there is no cell in this grid with genuine, repeatable edge on this snapshot.** The closest-to-breakeven cells (wide stops, low trade count) are the least-bad, not validated winners — each is 1–3 trades on 12.5–50 days of data. Re-run against weeks of real `MarketStore` history once prod carries it, or against a longer offline tape (the `width-sweep.php`/`strategy-gate.php` pattern of pulling raw Binance klines), before treating any cell here as a chosen default.
