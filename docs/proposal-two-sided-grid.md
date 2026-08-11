# Proposal: two-sided volatility harvest — regime-routed short grid (2026-07-31)

> **FALSIFIED SAME DAY — read this first.** The mandatory cross-regime test
> (identical sim on the 2024 bull tape) kills the routed version:
>
> | tape | long always | short always | long gated(up) | short gated(down) | ROUTED |
> |---|---|---|---|---|---|
> | 2024 (+121%) | **+353** | −3109 | +294 | **−2093** | **−1798** |
> | Dec25–Jul26 (−29%) | −446 | **+191** | −598 | +63 | **−535** |
>
> The EMA20/50 router is late in both directions: by the time the regime
> flips, the move has happened, and grid inventory then accumulates against
> the reversion (shorting bull pullbacks in 2024 = −2093/$1k). The routed
> combo loses on BOTH tapes; only the omniscient side-choice wins, and that
> is a macro prediction nobody here has demonstrated. The short grid survives
> only as a discretionary tool for an explicitly held bear view — not as a
> mechanical edge. See "What actually survives testing" at the bottom.

## The question

"Think outside the box, analyse historicals on real BTCUSDT, propose a new path
to profit." All numbers below are from the **real** BTCUSDT tape (Binance spot
klines + USD-M funding history), not testnet.

## What the historicals say

**The 2026 tape (YTD): −29% drift, 47% annualized vol.** 88.8k → 63.1k, low
58.6k. Only 100 of 211 days closed up.

**Monthly oscillation dwarfs monthly drift** (15m data, Dec 2025 – Jul 2026):

| month | drift | high-low range |
|---|---|---|
| 2025-12 | −1.4% | 12.3% |
| 2026-01 | −10.3% | 26.2% |
| 2026-02 | −15.6% | 31.7% |
| 2026-03 | +2.0% | 16.4% |
| 2026-04 | +12.2% | 20.4% |
| 2026-05 | −3.7% | 13.8% |
| 2026-06 | −20.6% | 26.9% |
| 2026-07 | +7.5% | 15.1% |

Every month offers 2–3× more range than drift. The harvest is there; the sign
of a grid's P&L is decided by which side holds the inventory when the drift
month hits. This is the same conclusion the project's own sweeps reached
("sign is decided by regime exposure" — stop-sweep; "nothing beat USDT" —
deploy-policy sweep), now confirmed on the real tape.

**Grid simulation, real 15m tape, $1k, ±4× daily-ATR geometric 1.7% spacing,
legacy-carry, 0.05%/side** (research probe, perfect fills — directional):

| variant | realized | mark-to-market | total |
|---|---|---|---|
| long grid always-on | +192 | −637 | **−446** |
| short grid always-on | +243 | −52 | **+191** |
| long grid gated EMA20>EMA50 (daily) | +55 | −653 | −598 |
| short grid gated EMA20<EMA50 | +147 | −84 | +63 |

- The long always-on result reproduces the deploy-policy sweep's
  `grid_always −444…−760/1k` on real data — the probe harness is credible.
- **Realized cycle income is symmetric** (+192 long vs +243 short): volatility
  pays both directions. The −637 vs −52 inventory mark is the whole story.
- Gated-long loses even in up-regimes this year: 2026's rallies were bear
  rallies; long inventory bought into them got carried into the dumps. A
  short grid in a real bull would be the mirror catastrophe — **the router is
  not optional.**

**Daily-strategy cross-check (2.7y of daily closes, 0.1%/side):**

| strategy | 2026 YTD | total 2023-11→2026-07 | maxDD |
|---|---|---|---|
| buy & hold | −28% | +80% | 53% |
| SMA200 long/**short** | **+22%** | +19% | 53% |
| Donchian 20/10 long/flat | −9% | +39% | 38% |
| EMA20×50 long/flat | −9% | +32% | 41% |
| SMA200 long/flat | +0% | −6% | 32% |

The only thing positive on 2026 is the short side. Every long/flat filter just
loses less.

**Funding carry (real USD-M funding, 2024-02 → 2026-07):** BTC funding
annualized +11.9% (2024) → +5.1% (2025) → **+1.9% (2026, negative in 33% of
epochs; Feb–Apr were net-negative)**. Carry-when-positive nets ~3.8%/yr with
0.4% maxDD. Two implications: (1) pure BTC carry is NOT the path this year —
the 10–30% APY claims in circulation are bull-market/altcoin numbers; (2) a
**short perp position currently RECEIVES funding** (~+2%/yr tailwind on held
notional), and funding above SMA200 (+7.5% ann) vs below (+2.7%) makes carry a
bull-regime bolt-on for later.

## The proposal

**Add a short-grid mode on USD-M perps, routed by the macro regime the
project already computes.** Keep the existing harvest machinery — ladder,
refits, legacy carry, staged stops, watchdog — and make inventory side a
regime decision:

- **Down-regime** (daily EMA20<EMA50, 4h trend down-family — the router the
  refit routine already runs): short grid on BTCUSDT USD-M perp, 1× (fully
  collateralized, no leverage), same geometry rules (≥4× 4h-ATR half-width,
  spacing ≥1.5× 1h ATR). Sell rallies, buy back one level lower.
- **Up-regime**: existing long spot grid, unchanged.
- **Conflict / transition**: flat (deploy_pct 0 is already first-class). Later
  option: park the flat budget in delta-neutral carry when funding is rich.
- **Risk mirrors the long side**: unrealized-loss cap with the 3-tick breach
  confirm, kill switch shared, shorts capped at a fraction of budget,
  liquidation buffer enforced (at 1× a BTC short liquidates only near ~2×
  entry — the cap fires long before).

Why this beats the alternatives considered:

- *Pure trend-following*: SMA200 long/short made +22% YTD but with 53% maxDD
  and 2025 −31% whipsaw; academic/industry results (QuantPedia's multi-TF
  study: ~6.6%/yr, Sharpe 0.8–1.07) are modest. The grid's cycle income is a
  real additive edge on top of the same regime signal.
- *Funding carry*: 2–4%/yr on BTC in this regime. Worth a sleeve someday, not
  a path.
- *More long-grid parameter tuning*: three sweeps have shown no spacing / fee
  / stop / width setting flips a bear tape positive. That door is closed.

## Validation plan (before any real order)

1. **Backtester short mode** — mirror `LevelStateMachine`/`Backtester`
   (short-at-level, cover-one-below), TDD like the stop-policy work.
2. **Walk-forward sweep** — `scripts/` harness pattern: 21×7d windows over the
   same 94d + the 242d 15m archive, long-vs-short-vs-routed-vs-flat, scored
   realized + MTM. Acceptance: routed ≥ flat on bear windows AND routed ≥
   long-only on bull windows (2024 daily tape as proxy), no window worse than
   the staged-stop cap.
3. **Binance USD-M testnet soak** — new run type (`side: Short`), small
   notional, the watchdog/routine/dashboard already generalize.
4. **Review gate** — promote only after ≥4 weeks of soak with routed total ≥
   flat and no liquidation-buffer breach.

## What actually survives testing (added after falsification)

Across this doc's sims AND the project's three sweeps (spacing, stop-policy,
deploy-policy), the findings that held up on every tape tested:

1. **Cycle income is real and symmetric** (+192/+243 per $1k per 8mo, both
   directions) but small relative to inventory drift. The harvest is not the
   profit engine; the inventory side is a macro bet.
2. **Lagging trend routers destroy the edge in both directions** — whipsaw
   entries put inventory on exactly the wrong side of reversions.
3. **Nothing mechanical beat flat (USDT) over the bear stretch, and nothing
   beat buy-and-hold over the full 2.7y** (+80%, −53% DD). Net edge after
   fees is small; anyone claiming otherwise is quoting a single regime.
4. The **best-supported live policy remains** the deploy-policy sweep's
   winner: long grid deployed only in strongly favorable regimes, hard-flat
   otherwise (`guideline0` / `range_gate` ordering), which is what the
   routine + deploy_pct-0 already implement.

**Acceptance protocol for ANY future strategy idea** (this is the doc's real
deliverable — it would have caught this proposal a day earlier):

- Gate 1: walk-forward positive (or ≥ flat) on BOTH the 2024 bull tape and
  the 2026 bear tape. One-tape results are regime artifacts by default.
- Gate 2: ≥ 4 weeks paper/testnet soak beating flat out-of-sample.
- Gate 3: explicit benchmarks reported next to the result: USDT (0%) and
  buy-and-hold over the same window.

**Gate 1 is now runnable: `php scripts/strategy-gate.php [SYMBOL]`** (cached
klines under `tmp/`, real Backtester stack, PASS/FAIL verdict per policy).
First run, 2026-07-31, BTCUSDT, per $1k:

| policy | bull2024 (103 wk) | bear2026 (68 wk) | verdict |
|---|---|---|---|
| grid_always | −105 | −320 | FAIL |
| guideline | −67 | −99 | FAIL |
| guideline0 | −71 | −66 | FAIL |
| range_gate | −94 | −40 | FAIL |
| usdt | 0 | 0 | benchmark |
| buy_hold | +1890 | −599 | context |

**No existing policy passes — including on the bull tape.** Even in the
+121% year, walk-forward 7d fixed grids lost money vs flat (the grid caps
upside at range top and eats every pullback; buy-hold made +1890). Caveat:
the harness ignores intra-window refits, which flatters nothing and
penalizes bull tapes most (the live bot re-anchors upward); a refit-aware
Gate 1 could soften the bull numbers. But as measured, the bar for any new
candidate is now explicit — and currently unmet by everything, which is
itself the most honest summary of this research line.

## Sources

- QuantPedia — multi-timeframe trend on Bitcoin: https://quantpedia.com/how-to-design-a-simple-multi-timeframe-trend-strategy-on-bitcoin/
- Trend/momentum on BTC survey: https://www.quantifiedstrategies.com/trend-following-and-momentum-strategies-on-bitcoin/
- BTC-USD trend-following study 2021-2025: https://www.researchgate.net/publication/395400190
- Funding-rate arbitrage guides (2026): https://arbitragescanner.io/blog/crypto-funding-rate-arbitrage-guide , https://www.buildix.trade/blog/cash-and-carry-crypto-delta-neutral-funding-rate-strategy-2026
- Market-neutral crypto overview: https://www.tv-hub.org/guide/market-neutral-strategy-crypto
- Data: Binance spot klines + USD-M fundingRate public APIs (fetched 2026-07-31).
