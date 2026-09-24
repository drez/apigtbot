# Refit routine — the evidence behind the rules

Reference for humans and for the routine's drill-downs. The hourly prompt
(`refit-routine.md`) states each rule in one line and points here; nothing in
this file is loaded per pass. Moved out of the prompt 2026-08-20 (prompt
v2026-08-21.1) to cut the routine's per-turn token cost.

## §1 Spacing floor — 1.3% absolute, ~1.7% target (spacing sweep)

`scripts/spacing-sweep.php`, 94d of 15m, 21 walk-forward windows, BTC AND BNB
agree: fee-floor-hugging spacing is the WORST bucket at every fee level (BNB
0.4%: 0% winning windows, mean −38/wk) and ~1.7% the best on both pairs. In
compressed vol the ATR rule under-specifies: BNB's 0.30% 1h ATR would "allow"
0.45% spacing, deep in the catastrophic bucket — the absolute floor wins.
BNB's headline: in its persistent downtrend EVERY spacing lost on mean —
spacing tunes the fee drag, deploy_pct decides the sign. The tool's 3×
round-trip fee floor is far too low to be a guide. "Fit market → tighten"
stops at max(1.3%, 1.5× 1h ATR%).

## §2 Width floor — half-width ≥ 4× 4h ATR (width sweep)

`scripts/width-sweep.php`, 187d of 15m × 4 pairs, 48 windows each, spacing
pinned 1.7%: narrow grids (1–2× ATR) post the best MEDIAN week (~+7.5/1k, one
2-level cycle) but tail weeks of −130..−195 erase ~18 median weeks; mean net
improves monotonically with width (aggregate: 1×ATR −4.8/wk, 4× −1.5, 6×
−1.4). A tight ladder that prints daily is the narrow-median experience, not
edge — run 1 lived it, then brushed the tail (−165, 2026-07-22). The same
sweep measured mechanical DOWN-re-centering as the worst behavior tested
(1×ATR: −21..−43/wk, 10–27% win — each re-center realizes the drawdown and
re-arms buys lower): never trail down into a 4h downtrend; below the range in
a downtrend the answer is hold or deploy 0, not a lower grid (RefitPlanner
enforces the same guard for the fallback cron).

## §3 Deployment decides the sign (deploy-policy sweep)

`scripts/deploy-policy-sweep.php`, 365d × 3 pairs: NO deployment level beat
holding USDT in the bear year; policies ranked strictly by how little they
deployed, and the old "hostile → 10–30" band alone cost ~$50–110/pair/yr
versus going flat. FLAT IS A POSITION. Guideline: fit + agreeing timeframes →
70–100; conflicting TFs or 1d down → 30–60; hostile (1d strong_down,
atr_pct_rank > 85, sell-heavy depth) → 0.

## §4 Regime gate — down-family OR ADX ≥ 30 (regime sweep)

`scripts/regime-sweep.php`, BTC+BNB 187d: fit-windows whose 4h trend was
down-family OR 4h ADX ≥ 30 own every catastrophic week on both symbols;
skipping exactly those improved mean net and cut the worst window ~78–88%
(BTC −112.75 → −25.03, BNB −66.44 → −7.92 per $1k/7d). Single signals (trend
alone, ER, choppiness, funding percentile) flipped sign between symbols —
advisory only, never a gate. The sweep also REFUTED "chop is always
grid-friendly": BTC's choppiest fit windows were downtrend consolidations
that broke down — read er20/chop14 WITH the trend. `gtbot_set_grid` enforces
the cap (25) since 2026-08-11; `override_regime_gate:true` + a stated thesis
is journaled and Telegrammed.

## §5 Re-entry gate — the 1d label goes stale

The 1d trend classifier is anchored to distance-from-EMA200, so after a deep
drawdown it reads "strong_down" for months while price bases and turns (BNB
2026-07-31: price above 1d EMA20 AND EMA50, 1d RSI 61, 4h up + 1h strong_up —
still labeled strong_down; the fleet sat flat through an +8% rally; BTC
2026-08-19: same label on a +6% breakout day). When deploy is 0 and price has
RECLAIMED both the 1d EMA20 and EMA50 with 4h and 1h up-family, treat the 1d
label as stale: re-enter at 30–40 with the width floor and a floor ≥ ~1× 4h
ATR below price; drop back to 0 if price loses the 1d EMA50 again.

## §6 Trend arm — allocation sweep + live A/B → machine-scaled

`scripts/allocation-sweep.php` (BTC+BNB, 24×7d windows): a standing
Grid/Trend blend was refuted (gridOnly beat every blend; zero TREND_UP windows
in 187d); the live A/B (2026-08-11→19) concluded no-decision (both arms
regime-gated flat ~90% of the soak). Event-driven activation shipped
2026-08-20 as `bin/gtbot-trend-activate` (TrendActivator): two consecutive
TREND_UP passes raise the Trend run's slice toward 500 (deploy 50) by trimming
the grids pro-rata to their floors; HOSTILE or two non-TREND_UP passes →
deploy 0; flat → release. The rule is bull-tape-unvalidated — operator
choice. The routine never touches a Trend run's slice or deploy.

## §7 Signal glossary (what the brief's numbers mean)

adx14 (<20 ranging/grid-friendly, >30 trending), atr_pct_rank (>85 = vol
spike vs own history → widen, don't tighten), er20 (Kaufman efficiency 0..1:
near 0 churning, near 1 going somewhere), chop14 (>61.8 ranging, <38.2
trending — read WITH trend), taker_buy_ratio (>0.5 buyers dominate),
vol_zscore (>2 real participation, <0 thin tape), funding_pct (percentile of
the funding rate vs trailing 30d; symbol-inconsistent — context only),
depth_imbalance_avg (smoothed book pressure). The brief carries the subset
the rules use; `gtbot_market` has the rest.

## §8 Decision journal

Every `gtbot_set_grid` writes a bot_decision; the `gtbot-refit` cron scores
it ~6h after it applied (Win/Flat/Loss; Superseded = replaced before it
traded — no evidence). `gtbot_decisions {run}` shows the full journal and
win rates by deploy band; the brief carries a compact scoreboard (`track`).
Judge success by `gtbot_pnl_report` net_per_1k_day, not cycle count.

Since 2026-08-23 (after reviewing virattt/ai-hedge-fund's CycleRecord +
"conviction requests, risk disposes" design) each decision is a receipt:
`requested_json` (the ask before any gate), `clamps_json` (every limit that
changed it — `regime_gate` first; empty = applied as requested),
`brief_json` (the digest/regime/candidate/deploy band the decider saw) and
`candidate_delta` = same|deviated|none vs `candidate{}`. Scoring adds a
counterfactual: the geometry the decision REPLACED is replayed on the same
post-apply 1h tape through Backtester; `counterfactual_delta` = sim(new) −
sim(prev) in USDT and a decision that no-change would have out-earned by
more than a round-trip fee scores **Worse** (a Loss stays a Loss).
`gtbot_decisions.summary.by_candidate_delta` splits the scoreboard by
followed-vs-deviated — the live test of whether the LLM's deviations from
the machine candidate add value. Wallet-level NAV vs HODL/USDT + max
drawdown: `gtbot_pnl_report.wallet` (hourly `wallet_nav` ledger).

## §9 Candidate-follow baseline (policy replay)

`scripts/policy-replay.php BTCUSDT Balanced`, 2026-08-23 — the refit layer
itself backtested: at every 3.5d step the exact brief functions
(Indicators::summary → RoutineBrief::regime → deployBand → candidate) ran on
data up to t, the candidate was applied mechanically, the next 7d scored
through Backtester (realized + mark-to-market, per $1k), on the two
strategy-gate tapes. Harness sanity: its engine baseline `gate_fit`
(−103 / −315) reproduces strategy-gate's `grid_always` (−105 / −320).

| arm | bull2024 (103 w) | bear2026 (68 w) | deployed |
|---|---|---|---|
| cand_hi (candidate, band top) | −75.00 | −60.22 | 103 / 67 |
| cand_mid (candidate, band mid) | −37.33 | −30.93 | 103 / 67 |
| cand_hi0 (band top, 0 when hostile) | −86.19 | −19.48 | 40 / 25 |
| gate_fit (engine, fixed fit, 100%) | −103.21 | −314.61 | — |
| usdt | 0 | 0 | — |
| buy_hold | +1890 | −599 | context |

Readings: (1) the candidate generator is a large improvement over the naive
fit (bear −315 → −60) but still does NOT clear USDT on either tape — the
fitter's envelope + band, applied blindly at a fixed 3.5d cadence, loses
slowly; (2) deploying less (cand_mid) halves the loss on both tapes, the
same monotone "less deployed = better" §3 found; (3) regime classes on the
bull tape were HOSTILE 39 / MIXED 29 / RANGE 20 / TREND_UP 15 windows — the
ADX≥30 gate sits out most of a bull year, which is where the trend arm (§6)
is supposed to earn instead. Consequences: the LLM's job is NOT to follow
the candidate (that baseline is known negative) but to beat it — and the
journal now measures exactly that (`by_candidate_delta`); if `deviated`
does not out-earn `same` over a few weeks of live scoring, the honest move
is fewer refits and lower deploy, not a smarter prompt. Caveats as the gate:
perfect fills, fixed 7d windows ignore intra-window refits, floors were
chosen knowing these tapes.

## §10 Trend-arm regime entry — enter on activation (trend-entry sweep)

`scripts/trend-entry-sweep.php`, 2026-09-05, BTC+BNB on the two long tapes
(bull 2023-11→2025-01, bear 2025-10→2026-07), 1h signal bars, the live
`TrendRegime::classify` + activator state machine (hourly passes), prod
slice shape (idle 100×25% = 25, active 422×50% = 211), 3×ATR trail with the
1.5% floor, `sell_at_loss` OFF (a below-cost stop HOLDS). Prompted by prod
run 1: activated 09-02 19:48 while holding 24 USDT, then FLAT 09-04→09-05
under an 81.4k 20-bar high with every pass printing TREND_UP.

| arm | BTC bull | BTC bear | BNB bull | BNB bear | maxDD bull/bear (BNB) |
|---|---|---|---|---|---|
| A Donchian only (engine) | +68.26 | −11.75 | +119.85 | −10.17 | 96.8 / 14.6 |
| **B A ∨ (active ∧ close > EMA20 > EMA50)** | **+132.30** | −11.75 | **+160.43** | −10.17 | 93.7 / 14.6 |
| C A ∨ (active ∧ pullback ≤ EMA20×1.005) | +114.86 | −11.75 | +154.90 | −10.17 | 93.7 / 14.6 |
| A+ / B+ / C+ (top-up to the active slice) | +53 / +102 / +102 | −70 / −68 / −67 | +99 / +162 / +150 | −27 / −74 / −9 | 94 / 56–92 |

HODL(211) for scale: BTC +313 / −86, BNB +441 / −96. Readings: (1) B passes
the pre-registered bar (bull net > A on both symbols, bear net ≥ A − 10,
maxDD ≤ A + 21) and beats C on both bull tapes — SHIPPED as
`TrendEngine::regimeEntrySignal`, used only while `TrendActivator::state`
is `active`; (2) the bear tapes are identical across A/B/C because the arm
is active 4–8% of the time there and enters once, then holds a bag
(`sell_at_loss` OFF) for ~95% of the tape — the entry rule cannot hurt what
never fires; (3) every TOP-UP variant fails the bear drawdown test (a 211
bag held to the end instead of a 25 one: DD 56–92 vs 14) — refuted, not
implemented; (4) "wins = trades" everywhere is the no-loss policy, not skill:
judge on net (MTM), never on the win count.

## §11 Regime gate on the bull tape — the 4h-only cap survives

`scripts/regime-sweep.php <SYMBOL> --tape=bull|bear`, 2026-09-05 — the §4
gate re-scored on the long tapes (117 bull / 81 bear windows per symbol)
against G2 = "down-family OR (ADX ≥ 30 AND NOT 1d-aligned up)", i.e. waive
the cap when `TrendRegime::oneDayUp` holds (the BNB 2026-09 situation: 4h
ADX ≥ 30 for weeks inside a +30% month, grid pinned at deploy 25).

| gate @ 4×ATR | BTC bull mean / worst | BNB bull mean / worst | BTC bear | BNB bear |
|---|---|---|---|---|
| none | −0.67 / −64.67 | −0.08 / −96.00 | −5.81 / −91.92 | −5.15 / −132.61 |
| down\|adx≥30 (§4, live) | −0.65 / −64.67 | **+1.41 / −27.75** | +0.44 / −28.36 | −1.86 / −81.44 |
| G2 down\|adx30 ∧ ¬1d-up | −0.85 / −64.67 | +0.92 / −49.25 | +0.59 / −28.36 | −1.87 / −81.44 |

Readings: G2 FAILS its bar (bull mean below the live gate on both symbols;
BNB worst-window nearly doubles). The windows the ADX cap skips in a
1d-aligned uptrend averaged −2.90/wk on BNB bull — a static grid under a
trending 4h loses on the pullbacks it buys, even with the daily up. Keep
the §4 gate as is; the bull-leg income belongs to the trend arm (§10) and
the core, not to a fully-deployed grid. Grid means of −0.7…+1.4 per $1k/wk
on every tape are the ceiling of this product: cycle income is small and
the sign is set by regime exposure (§3).

## §12 Month replay 2026 — the live engine vs prod, six tape-months (month-replay)

`scripts/month-replay.php <SYMBOL> --from --to [--arms=B,BE,BG,BR,BH,BRH]`,
2026-09-16. One script per bot type over real Binance 15m tape (45d warm-up,
1h signal bars, end-aligned 4h/1d regime buckets, the live `TrendRegime` +
activator state machine at hourly passes), printed next to the prod actuals
(MCP `gtbot_pnl_report`, 45d window 08-02 → 09-16, all runs simulated).
Windows: Aug 2 → Sep 16, Jun 1 → Jul 31, Apr 1 → May 31, BTC + BNB.

**Prod, 08-02 → 09-16:** pool −2.1% (realized +12.75, unrealized −38.8).
Trend run 1 −2.23 realized / −10.5 bag (6 entries, 4 stop-outs, bag 0.00264
@ 79 800 since 09-05); core run 8 bag −8.6; BNB trend run 9 bag −0.7
(Halted); BNB grid 4 +10.01 / −7.3 (81 cycles, fees 17% of gross); BTC grid
7 +4.97 / −12.0 (30 cycles, below range with 0 buy power since ~09-10).
Tape: BTC +20.5%, BNB +23.8% — almost all in ONE week (08-16..22).

### Trend arm — today's engine (B: Donchian ∨ regime entry, hold policy), active 211

| window | BTC net (real / bag) | BNB net (real / bag) | HODL(211) BTC / BNB | stop policy BTC / BNB |
|---|---|---|---|---|
| Aug 2 → Sep 16 | **+15.71** (+29.13 / −13.42) | **+14.60** (+28.37 / −13.77) | +43.3 / +50.2 | +10.16 / −8.03 |
| Jun 1 → Jul 31 | +1.00 (+1.99 / −0.99) | **−37.01** (0 / −37.01, one 32h activation) | −31.4 / −36.7 | −1.70 / −9.60 |
| Apr 1 → May 31 | −7.98 (+12.34 / −20.33) | +19.05 (+22.86 / −3.81) | +16.5 / +31.1 | −31.85 / −9.12 |
| **six-month sum** | **+5.4** (hold) · **−50.2** (stop) | | +72 | |

Readings: (1) **prod's −12.7 is not this engine's number** — prod ran three
engine versions in the window (ADX_STRONG 08-25, 1d gate 09-02, regime entry
09-05); the engine prod runs NOW would have activated 08-19 at 65k (BTC, 13%
into the 62.3k → 82.3k leg) / 630 (BNB, 17% into 600 → 781) and realized
+29 / +28 in the leg. It shipped 09-05, after the leg; its only live entry
since (09-05 @ 79 800) is the same last-of-the-leg entry the replay makes
(09-04 @ 80 862). The lateness theory (activator fires > 60% into a leg) is
REFUTED for the current rules on this tape. (2) **Every bull window ends
holding the last entry of the leg as a bag** (−89 MTM across the six vs +97
realized): a regime re-entry placed within hours of a winning trailing-stop
exit. (3) **Hold beats stop** on every window but one (six-month +5.4 vs
−50.2; the long tapes agree) — Fred's policy is right on the data, and its
cost is the −37 BNB Jun–Jul tail (one false activation held through a −17%
month = HODL loss). (4) The arm captures ~40% of HODL on its active notional
in a leg, and ACTIVE_DEPLOY_PCT = 50 means only HALF of the trend slice is
ever in the market (350 target → 175 deployed); the other half was reserved
for the top-up path that §10 refuted. Deploy 100 on the same slice doubles
every number above, both ways (+30/+30 in Aug, −74 in the BNB bear month) —
a sizing decision, not an engine change. **Decided 2026-09-16 (Fred):
ACTIVE_DEPLOY_PCT → 100**; the target slice stays the sizing knob.

### Entry variants vs the live arm (trend-entry-sweep, long tapes + the 2026 windows)

| arm | BTC bull | BNB bull | BTC bear | BNB bear | 2026 six-month sum |
|---|---|---|---|---|---|
| B live | +132.30 | +160.43 | −11.75 | −10.17 | +5.4 |
| E extension veto (close > 1.04 × 120h mean) | +89.55 | +86.13 | −11.75 | −9.15 | +26.8 (BNB Jun–Jul bag avoided) |
| G 24-bar cooldown after a ≥ +3% winner | +116.61 | +146.06 | −11.75 | −8.36 | −10.9 |
| R regime re-entry needs a new high above the last exit's hwm | +82.57 | +125.92 | −11.75 | −10.17 | −12.4 |
| H one-shot top-up at activation | +133.06 | +163.93 | **−68.00** | **−74.66** | +4.7 |
| RH | +83.33 | +129.43 | −27.83 | −74.66 | −4.8 |

All five REFUTED against B: E/G/R give up 15–50% of the bull tapes (the
"extended" entries at the top of the 30d range are the +16%/+15% winners,
not the losers — the bag is the LAST re-entry, and no local rule separates it
from the ones that keep paying); H fails the bear drawdown bar like every
top-up. The entry logic stays as shipped.

### Grid arm — mechanical ±6% × 6 / 72h, continuous ladder (legacy lots carried as exits at cost + 1 rung, ladder budget = min(deploy × slice, slice − reserve))

| arm | Aug–Sep BTC / BNB | Jun–Jul BTC / BNB | Apr–May BTC / BNB | six-month sum | worst |
|---|---|---|---|---|---|
| d25 (the hostile cap all month) | +2.38 / +5.33 | −3.60 / −6.19 | +1.29 / +11.33 | +10.5 | −6.2 |
| d100 | +9.75 / +18.01 | −21.79 / −40.35 | +4.97 / +35.57 | +6.2 | −40.4 |
| **gated (§4 gate at each anchor)** | +5.74 / +6.14 | −2.25 / −15.56 | −4.05 / +23.78 | **+13.8** | −15.6 |
| gated + inventory cap 50% | +3.22 / +5.28 | −3.63 / −12.23 | −0.47 / +14.13 | +6.3 | −12.2 |
| d100 + cap 50% / 70% | +4.87 / +9.01 · +6.82 / +12.61 | −11.07 / −20.51 · −15.26 / −28.25 | +2.49 / +17.78 · +3.48 / +24.90 | +2.6 / +4.3 | −20.5 / −28.3 |
| PROD (routine geometry until 09-09, then mechanical) | −6.98 / +2.69 (30 / 81 cycles) | | | | |

Readings: the §4 gate is the best allocation across the six months and the
second-safest; **the inventory cap (T5) is REFUTED** — it trims both sides
and nets less than the gate. Grid slices of 300/450 earn ≈ +14 over six
tape-months (≈ 0.4%/month): cycle income is small, the sign is regime
exposure (§3, §11 again). Prod run 7's zero-buy-power state is not a
mechanical-ladder property (starved bars 0% in every gated replay) — it came
from the 09-05/09-09 slice cuts (422 → 300) landing on a ladder that was
already full of routine-era inventory.

### What the month says about the pool

The −2.1% is three bags (436 USDT, a third of the pool, parked at deploy 0)
plus the grids' legacy inventory marked at a −8% BTC pullback; realized
cycle income is +12.75. Nothing in the engine family tested here changes
the ceiling: trend arm ≈ 40% of HODL in a leg on half its slice, grids ≈
0.4%/month. The decisions that move the number are sizing (trend
ACTIVE_DEPLOY_PCT, target slice), and they move the drawdown with it.

### Derived slice, 2026-09-19

The activator re-asserted the slice the ACTIVATION wrote, every 15 minutes,
for as long as the episode lasted. On a held BREAKOUT arm that number is
unreachable: it takes no further entry until it is flat, and the one-shot
top-up at activation (variant H above) is refuted on both bear tapes — so
everything between the position and the payload was pinned, idle, and still
charged to the shared pool. Run 1 on 2026-09-19 held 210.67 of BTC on a 422
slice: 172 USDT, a seventh of the pool, reserved for an entry that could not
happen. The asserted slice is now DERIVED on every pass instead:

* **breakout arm holding** (engine `qty` > 0) → `max(ceil(committed), 50 × n_levels)`,
  where committed = filled inventory + **the unfilled remainder of every
  working BUY row** + the legacy exit reserve. The working buys matter: the
  arm places its whole tranche batch in one tick, so at a 15-minute pass part
  of it is routinely still on the book, and sizing to the filled part alone
  would let the rest fill past the asserted slice — understating exposure
  against the never-overcommit invariant.
* **flat arm, and any `EmaCross1d` core** → the fleet slot's target slice. The
  core is not an exception to the refuted top-up: staggered tranches into an
  open position are its designed deployment (`tryAddCoreTranche` runs while
  holding), and deriving it down to its first fill would re-split a shrunken
  budget on every pass.

deploy ACTIVE_DEPLOY_PCT throughout. Sizing a held breakout arm DOWN is the
whole point; raising one is funded from pool **headroom alone** — no grid is
trimmed for an arm whose entries are gated until it is flat. Only the target
rule (flat arm, core) may use the funding planner (headroom first, then the
grids above their floors), so an unreachable target moves nothing and
journals nothing. A decrease touches no grid either: the freed amount lands
in pool headroom and `gtbot-allocate` distributes it on its own pass. First
prod pass on run 1: 422 → 250 (the 5-level floor, above its 210.67
position), 172 back to the pool.

## §13 Trend-arm EXIT rules — all refuted (trend-exit sweep)

`scripts/trend-exit-sweep.php`, 2026-09-16 — the exit-side companion to the
§10 entry sweep. §12's finding (the live arm realizes +97 over six 2026
tape-months but ends every bull window holding the last entry of the leg as
a bag, −89 MTM; entry fixes E/G/R/H all refuted) left the exit side
untested, so four exit policies were replayed on the same harness (baseline
B = the live engine, hold policy: Donchian ∨ regime entry, idle 25 / active
211, 2×ATR initial, 3×ATR trail with the 1.5% floor, 3-bar cooldown,
sell_at_loss OFF):

| policy | BTC bull | BTC bear | BTC 2026 Σ | BNB bull | BNB bear | BNB 2026 Σ |
|---|---|---|---|---|---|---|
| B hold (live) | +132.30 | −11.75 | +8.73 | +160.43 | −10.17 | −3.36 |
| BE breakeven lock | +125.23 | −11.75 | +8.73 | **+138.96** | −10.27 | −7.63 |
| T tier 50% at +2R | **+137.37** | −11.75 | **+11.65** | +154.86 | **−8.92** | **−9.14** |
| BE+T | +128.33 | −11.75 | +11.65 | +149.79 | −9.79 | −10.58 |
| TS stall-bail (hold) | +137.37 | −11.75 | +11.65 | +154.86 | −8.92 | −9.14 |
| B stop policy | −62.65 | −44.61 | −23.39 | −38.53 | −40.87 | −26.75 |
| TS stall-bail (stop) | −63.66 | −43.31 | −21.19 | −38.40 | −38.48 | −26.21 |

End-of-window |MTM| (the bag): B BTC bull 0 / BNB bull −11.90; the 2026 Σ is
89.33 for every hold-policy arm — no exit rule moved it.

Pre-registered bar (both symbols): bull net ≥ B − 5% of |B|, bull |MTM| ≤
0.7×B, bull DD ≤ B + 21; bear net ≥ B − 10, DD ≤ B + 21; 2026 Σnet ≥ B and
Σ|MTM| ≤ 0.7×B. **Verdicts: BE FAIL, T FAIL, BE+T FAIL.**

Readings:
1. **BE is actively harmful, not neutral.** On BNB bull it costs −21.47 of
   net AND makes the bag worse (−17.43 vs −11.90): the lock chops winners at
   breakeven, the 3-bar cooldown re-enters at the top, and the re-entry
   becomes the bag. It fails on BTC bull too (−7.07). Breakeven locks are
   the wrong tool for a strategy whose problem is the LAST entry.
2. **T (tiered take-profit) is symbol-fragile.** It improves every BTC
   tape (+5.07 bull, +2.92 across the 2026 windows) but gives up −5.57 on
   BNB bull and −5.91 on BNB Aug–Sep; across both symbols the 2026 sum is
   worse than B (+2.51 vs +5.36). It never touches the bag — the bag entry
   never reaches +2R, so a take-profit tier cannot help it.
3. **TS (stall-bail) is the only rule that would cut the bag, and it needs
   sell_at_loss ON** (bail_blocked counts every episode under the live OFF
   switch). Under the stop policy it doesn't reliably beat B/stop (BTC bull
   −63.66 vs −62.65; BNB Aug–Sep −10.93 vs −8.03), so even flipping the
   switch it earns nothing.
4. **The bag is an entry-side artifact the exit cannot see.** Every hold-policy
   arm ends with the same 89.33 of 2026 MTM regardless of exit rule. The
   levers that move it remain: sell_at_loss (stop policy — refuted on net,
   §12), not re-entering after a winner (R — refuted on bull, §12), or
   sizing the arm down. **No exit-side variant ships; the engine stays as
   shipped 2026-09-05.**

## §14 Core arm machine-scaled (core-gate probe)

`scripts/core-gate-probe.php`, 2026-09-16 — the inventory core (EmaCross1d)
was the one arm the activator never scaled, so it carried the full bear cost
(−17/−196 per $1k) while the trend arm was shielded. Probe: gate the core's
ENTRIES by the live `TrendRegime` classifier (daily close, no hysteresis),
keep the core's own 1d-cross exit. Same tapes as core-sweep, per $1k:

| rule | BTC bull | BTC bear | BNB bull | BNB bear | worst DD |
|---|---|---|---|---|---|
| cross (live core) | +530 | −17 | +1464 | −196 | 40.7% |
| gate_entry (TREND_UP-gated entries) | **+916** | −22 | **+1527** | **−145** | **27.5%** |
| gate_both (also exit on HOSTILE) | +621 | −10 | +366 | −187 | 40.1% |
| gate_1d (1d ADX/ER only) | +56 | 0 | +778 | −147 | 27.8% |

`gate_entry` passes a pre-registered bar (bull net ≥ cross on both symbols,
bear net ≥ cross − 10, maxDD ≤ cross): it improves BOTH bull tapes and the
BNB bear tape, and cuts the worst drawdown 40.7% → 27.5%. `gate_both`
refuted on bull (exits the core on every HOSTILE pullback); `gate_1d`
refuted on BTC bull. **Shipped 2026-09-16**: `TrendActivator` no longer
skips `EmaCross1d` runs — the core is machine-scaled exactly like the
Donchian arm (deploy 100 on TREND_UP, 0 on deactivate, slice released when
flat). Both arms on one symbol are scaled by the same gate; the pool is the
arbiter (BudgetGuard fails closed, grid floors cap the trim), and the core's
exit stays its own 1d EMA cross — deactivation only stops new tranches,
never liquidates. One carve-out: **a core that is idle but still holds a
legacy position (entered before machine-scaling, e.g. prod run 8's bag) is
not scaled until flat** — raising its deploy mid-position would size
top-up tranches into the existing bag (the refuted §10 top-up path). The
core's own entry/exit logic is unchanged; the activator's deploy write only
gates the SIZE of its staggered tranches. Caveat: the probe used a
no-hysteresis daily-close gate, the live activator's 2-pass confirm /
4-pass deactivate is at least as selective, and it acts every 15 min.

## §15 Core arm on the 2026 windows — the stagger eats the gate (month-replay)

`scripts/month-replay.php <SYMBOL> --from --to --warmup=250 --core=211,350,450,625`,
2026-09-21. §14 selected `gate_entry` on the long tapes with a probe that
puts the WHOLE slice in at the 1d cross (`core-gate-probe.php`, all-in,
daily bars); §12 replayed the trend arm and the grids on three 2026 windows
and never ran the core. This closes that gap: the core arm as SHIPPED —
`TrendEngine::emaCross1dUp` entry, activator marker gating entries AND
tranches, `TrendEngine::tranches` + `coreTrancheDue` stagger (pullback to
≤ 1.01 × the 1d EMA20 or 24h), 1d-cross exit, `sell_at_loss` OFF — on the
same six tape-months, fee 0.10%/side, one 1h pass per bar.

Warm-up: the core reads a 1d EMA50, so these runs use `--warmup=250` (prod's
MarketStore carries 200 daily rows). At §12's 45d warm-up the 1d EMA50
degenerates to a 45-bar mean AND the 1h bucket phase shifts, which moves the
Aug–Sep numbers (trend B BTC +15.71 → +14.39, BNB +14.60 → +23.31; gated
grid BTC +5.74 → +0.14, BNB +6.14 → +10.13; Apr–Jul unchanged to the cent).
Every number below comes from the 250d runs, so the core, the trend arm and
the grid in one row are the same tape and the same regime series.

### Core arm, the live rule (slice 211 = the trend arm's active notional)

| window | BTC net (real / bag) | BNB net (real / bag) | trend B(211) BTC / BNB | HODL(211) BTC / BNB |
|---|---|---|---|---|
| Aug 2 → Sep 16 | **+0.28** (0 / +0.28) | **+26.52** (0 / +26.52) | +14.39 / +23.31 | +44.90 / +55.60 |
| Jun 1 → Jul 31 | 0.00 (never entered) | **−22.86** (0 / −22.86) | +1.00 / −37.01 | −31.41 / −36.71 |
| Apr 1 → May 31 | −7.93 (0 / −7.93) | +11.76 (0 / +11.76) | −7.98 / +19.05 | +16.50 / +31.13 |
| **six-month sum** | **+7.77** | | **+12.76** | **+80.01** |

| window / symbol | entries (tranches) | % of time invested | max DD (USDT on 211) |
|---|---|---|---|
| Aug–Sep BTC / BNB | 1 (4/4) / 1 (4/4) | 60.1% / 83.1% | 17.49 / 23.30 |
| Jun–Jul BTC / BNB | 0 (0/4) / 1 (4/4) | 0% / 99.9% | 0.00 / 37.24 |
| Apr–May BTC / BNB | 1 (4/4) / 1 (4/4) | 75.5% / 34.5% | 26.47 / 18.09 |

**Realized P&L over six tape-months: zero.** Five entries, five bags, not one
completed round trip — the 1d cross only came down while the position was
under breakeven, and `sell_at_loss` OFF held every one of them. The whole
column is open-bag MTM, and the window boundary flatters it: each window
starts the arm flat, so a core running continuously since 2025 would have
carried its Apr bag into Jun.

### Why it underperforms its own §14 evidence — the per-order cap

The shipped arm splits the slice into `ceil(slice / max_order_quote)`
tranches (60 is the live cap — prod run 8 placed 4 on a 200 slice; a 350
slice needs six). Tranche 1 goes on the cross, the rest on a pullback or the
**24h fallback**, and the activator's deploy-0 spells pause the ladder.
Six-month sums, both symbols, slice 211:

| variant | Apr–May | Jun–Jul | Aug–Sep | sum | worst window |
|---|---|---|---|---|---|
| **live: gated + stagger (cap 60)** | +3.83 | −22.86 | +26.80 | **+7.77** | −22.86 |
| gated, all-in (cap = slice) | +10.13 | −36.92 | +53.92 | **+27.13** | −36.92 |
| un-gated + stagger (pre-2026-09-16 core) | +7.23 | −29.46 | +42.48 | +20.25 | −29.46 |
| un-gated, all-in (= §14's probe shape) | +13.46 | −36.92 | +56.63 | +33.17 | −36.92 |
| trend arm B (211) | +11.07 | −36.01 | +37.70 | +12.76 | −36.01 |
| HODL (211 × 2) | +47.63 | −68.12 | +100.50 | +80.01 | −68.12 |

Readings: (1) **the stagger costs more than the gate earns.** All-in → live
rule is −19.4 over the six months; the gate is worth +7.8 of that back only
because it kept the all-in arm out of part of the BNB bear window. BTC
Aug–Sep is the clean demonstration: all-in enters 08-20 @ 71 592 and nets
+13.39, the shipped ladder averages up into 79 258 / 77 210 / 76 486 for a
VWAP of 76 030 and nets **+0.28** on a +21% tape. The 24h fallback deploys
into vertical strength by construction — it is the only rule in the fleet
that BUYS a leg it has already ridden. (2) **The §14 gate does not repeat
out of sample.** On these windows gating costs 12.5 (stagger) / 6.0 (all-in)
per 211 of notional; it pays only in BNB Jun–Jul (+6.6). Worse, the gate
interacts with the stagger the wrong way: BNB Aug–Sep sat deploy-0 from
08-09 23:45 to 08-19 21:45, so tranches 2–4 fired at 634/653/684 instead of
~605 (+26.52 gated vs +42.20 un-gated). Gating a LADDER is not the same
experiment as gating an all-in entry, and §14 only ever tested the latter.
(3) **The core does not beat the trend arm on the 2026 tape**: +7.77 vs
+12.76 on the same 211, with a smaller worst window (−22.86 vs −36.01) and
no realized income. It is not the best-performing algo here; on the long
tapes of §14 it still is, in a shape (all-in, no cap) that prod does not
run. (4) Every arm loses to HODL on every up window and beats it on both
down windows — the same shape §12 reported.

### Portfolio, 1300 USDT over the six tape-months

Each window is an independent deployment of the same 1300 (no compounding
across windows), both symbols, headroom earns 0.

| allocation | Apr–May | Jun–Jul | Aug–Sep | sum net | worst window |
|---|---|---|---|---|---|
| **(a) current fleet — 2×350 trend B + 2×300 gated grid** | +30.17 | −72.36 | +69.43 | **+27.24** | −72.36 |
| (b) 2×core 450 + 2×trend 175 + 50 — **live core** | +6.77 | −74.43 | +60.83 | **−6.83** | −74.43 |
| (b*) same, core all-in (cap = slice) | +30.79 | −108.60 | +146.25 | +68.44 | −108.60 |
| (c) 2×core 625 + 50 — **live core** | −15.34 | −45.01 | +31.00 | **−29.35** | −45.01 |
| (c*) same, core all-in | +30.01 | −109.35 | +159.69 | +80.35 | −109.35 |
| (d) HODL 650 BTC / 650 BNB | +146.73 | −209.85 | +309.60 | +246.48 | −209.85 |
| (e) flat USDT | 0 | 0 | 0 | **0** | 0 |

Inputs, explicitly: the core rows at 450 and 625 are **re-simulated at that
slice** (the tranche count is `ceil(slice/60)` — 8 and 11 — so scaling a 211
result would be wrong); the trend rows are the re-simulated 211 numbers
**scaled linearly** (×1.6588 for 350, ×0.8294 for 175 — exact for this arm,
§12); the BTC grid is re-simulated at 300 and the **BNB grid is scaled
×0.667 from its 450 run**, which is only approximately linear (level
granularity, the 5-USDT minimum). (d) is the window return on the notional.

Reading: on this tape the current fleet shape (a) is the only positive
allocation that is not un-gated HODL-in-disguise, and it wins on the worst
window by 36–37 against every core-heavy shape. Swapping trend notional into
the core as it is shipped (b, c) turns +27 into −7 / −29 — the core-heavy
books lose the trend arm's realized income and add a bag that is 75–100% of
the time invested. (b*)/(c*) are the interesting rows and they are NOT an
option today: they need the per-order cap lifted, they are 1.5–4× the worst
window of (a), and their entire edge is one BNB leg. Flat USDT (e) beats
both live core books over these six months.

Caveats: **Apr–Jul sits inside the 2025-10 → 2026-07 bear tape §14 selected
`gate_entry` on — only Aug 2 → Sep 16 is out of sample**, and it is the
window where gating costs the most. Perfect fills at the bar close, no
slippage, no partials, no funding; hourly passes where the live daemon runs
every 15 min; the activator here confirms/deactivates on one pass, not the
live 2/4; the core reads the rolling 1d bucket ENDING at each pass, prod
reads a UTC daily summary refreshed hourly; each window starts every arm
flat. The per-order cap (60) is read off the engine's own comments and prod
run 8's four tranches, not from a prod config dump — if a core run were
created with `max_order_quote = budget_quote` it would behave like the
all-in rows.

## §16 Long-horizon "oracle" — detects a major trend, predicts nothing (outlook-sweep, outlook-research)

Question (todo 2026-09-21): warn on long-term trends and "broaden the oracle
to predict major up/down trend". Two read-only scripts over the FULL Binance
daily history (BTC 2017-08 →, BNB 2017-11 →, weekly bars composed from the
dailies), both symbols:

**`scripts/outlook-sweep.php`** — `MarketOutlook` (1d 4-of-5 EMA/slope votes +
1w price over EMA20/50 with a non-falling EMA20; MAJOR = both agree + daily
ADX≥20 or ER≥0.30; FORMING = daily turned, weekly not yet). Skill = P(30d
forward return has the called sign) vs the tape's base rate.

| per DAY in state, full history | BTC hit30 / base | BNB hit30 / base |
|---|---|---|
| MAJOR_UP | 51.7% / 55.6% | 63.6% / 57.6% |
| MAJOR_DOWN | 41.7% / 44.4% | 38.2% / 42.4% |
| UP_FORMING | 72–84% / 55.6% | 47–53% / 57.6% |
| DOWN_FORMING | ~50% / 44.4% | ~52% / 42.4% |

MAJOR_DOWN is ANTI-predictive on both symbols under every variant tried
(passes-per-close 24/8/4, DIR_VOTES 5, ADX 30/ER 0.4, SLOPE_MIN 2): price
bounces more often than it falls after the state is established. bear2026:
MAJOR_DOWN days hit 41% (BTC) / 38% (BNB) vs a 56–58% base. The only real
edge, BTC UP_FORMING, does not exist on BNB — the §7 lesson again. Flips
23–33/yr at one close of confirmation, 9–12/yr at six closes. Pre-registered
acceptance → `gtbot_outlook_alerts = off`.

**`scripts/outlook-research.php`** — 37 candidate states (200d SMA side and
slope, golden/death cross, 20w/21w bull band, fresh 200d reclaim/loss,
90/180/365d momentum, 365d-high/low proximity, 20w breakout, Mayer multiple,
drawdown-from-ATH buckets, weekly RSI, pi-cycle, realized-vol rank, combos,
BTC-leads-BNB), Δup = P(up | state) − P(up) at 30/60/90d, plus first/second
half of history and the bull2024/bear2026 tapes. PASS = |Δup@60d| ≥ 8pp,
same sign on both symbols and both halves, eff n ≥ 5: **none**.

- The whole TREND family (the thing an "oracle" would be) has Δup ≈ 0 ± 3pp
  on both symbols at every horizon. Being in an uptrend says nothing about
  the next 30–90 days being up. (Δmean is positive — the right tail is fat —
  but that is not a direction call.)
- The only sign-consistent structure is CONTRARIAN and cycle-level:
  drawdown > 60% from ATH → Δup@60d +15 (BTC) / +23 (BNB), positive in both
  halves; drawdown < 15% → −12 / −15. ~3 cycles in the sample, so n ≈ 3.
  "Fresh loss of the 200d" is followed by UP more often (+16 / +13).
- Funding (30d avg, trailing-year rank; throwaway check, futures history
  2020 →): both extremes read "up" on BTC, and BNB flips sign vs BTC.
  Refuted again, now at 30–90d as well as the short horizons of §7.

Verdict: a long-horizon direction PREDICTOR is refuted on this data. A
DETECTOR ("BTC entered MAJOR_DOWN: 1d and 1w agree") works and is cheap, but
must be worded as a description, never a forecast, and must not gate or lean
any deploy decision — the state it names is, if anything, slightly
contrarian. Caveats: daily-close walk (live is hourly on a repainting bar);
overlapping forward windows inflate n; one exchange, two correlated symbols.

**Decision (operator, 2026-09-21): ship the DETECTOR + a cycle-position line,
not a predictor.** `bin/gtbot-outlook` (hourly :12) logs and scores every
confirmed call (`market_outlook`), `gtbot_outlook_alerts` ships at `major` —
the sweep's own bar says `off`, and that bar measures FORECAST skill, which
the alert no longer claims: the message reads "major downtrend detected — 1d
and 1w agree … Detection, not a forecast". Confirmation is six daily closes
for MAJOR/NEUTRAL, three for FORMING (144/72 hourly passes): 8.6 (BTC) /
11.9 (BNB) confirmed changes a year, ~3–5 of them touching a MAJOR, 0.1
direct MAJOR↔MAJOR reversals a year. The routine brief carries
`shared.outlook` as labelled context; an outlook change is listed in
`changes[]` and is never `material_change`.

## §17 30-day review 2026-08-23 → 09-23 — the engine is fine, half the pool sits idle (month-replay + prod)

`month-replay.php {BTC,BNB}USDT --from=2026-08-23 --to=2026-09-23` (45d
warm-up, active 211, grid slices 300/450) next to prod read off the DB
(trade_cycle, engine_state, wallet_nav, bot_event, pool_engagement).
Tape: BTC 77 282 → 86 178 (+11.5%), BNB 699 → 790 (+13.0%); activator
TREND_UP 72% / 74% of the hours.

**Prod pool:** NAV 1005.76 → 1349.28 with +300 added 09-03, so **+43.5
(≈ +3.7%)**; maxDD 3.5% (1311 → 1265, 09-15). 50/50 HODL of the pool ≈
+150: prod kept ~30% of it. Reconciles: realized +42.0 (run 1 +17.57,
run 4 +11.28, run 7 +6.14, run 8 +3.09, run 9 +1.09, run 10 +2.82) + open
trend MTM (run 1 0.00408 @ 85 366 ≈ +3.3, run 10 0.44 @ 791.45 ≈ −0.7).

| arm | replay @211 / grid slice | replay scaled to prod slice | prod |
|---|---|---|---|
| BTC trend (hold) | +16.16 | +26.8 @350 | run 1 +20.9 (slice 100–450, deploy 50 until 09-16) + core run 8 +3.1 |
| BNB trend (hold) | +11.75 | +19.5 @350 | runs 9+10 +3.2 — no BNB arm until 09-09, a wrong idle entry 09-14, the right one only from 09-20; the replay's +10.36 (09-04 → 09-06) had no prod arm |
| BTC grid (gated, continuous) | +8.93 @300 | ≈ +5.7 @192 | run 7 +6.14 |
| BNB grid (gated, continuous) | +17.06 @450 | ≈ +13 @343 | run 4 +11.28 (14 losing cycles −2.97, the 09-02 starved release) |

Readings: (1) **The live engine did what the replay says.** BTC trend
reached ~80% of its replay number despite three engine/size changes in the
window. The 09-20 → 09-22 leg (80 656 → 85 184, +18.84) was the arm working
as designed, and so was the 15-day hold of the 09-05 bag (79 800 → exit
+1.91 on 09-20). The gap on BNB is fleet shape (no BNB arm for most of the
window), and fleet_slot fixed that on 09-20. (2) **Even perfect execution
at today's shape is ≈ +65 / 1300 (+5%) against HODL +12%.** That ceiling is
sizing, not signal (as §12 said). (3) **Half the pool is idle in a
TREND_UP.** pool_engagement 09-21 → 09-23 reads 54–56% engaged, 730 of
1300. Both trend arms sit at ~350 each; the two grids (343 + 192) sit at
the hostile cap (deploy 25) with near-zero inventory, and the 5% reserve
holds 65. About 470 USDT of grid slice is uncommitted by design for as long
as the 4h regime stays hostile to grids, which during a TREND_UP episode is
the whole episode.

Defects seen in the window, already fixed: run 10's 1000-slice re-assert
loop (479 `trend_activator_error`, 09-20 16:33 → 09-21 07:33, fixed
a68460b); BNB run 9's off-mandate idle entry (retired 09-18);
August activation flip-flops (hysteresis 08-28). Still set: run 7
`sell_when_starved = 1` (fired 09-20: 9 legacy exits repriced to market,
net +0.9, one −0.15), which contradicts the 09-09 refocus "OFF".

Ranked candidates (none on the refuted list):
1. **Lend capped-grid capital to the active trend arm of the same symbol**
   while its episode lasts. The grid keeps exactly what deploy 25 works
   (plus its exit reserve), and the rest goes to the arm's target slice,
   returned on deactivate, which is also when the §4 gate uncaps the grid.
   This window: arms at ~550 instead of 350 ≈ **+26 more** (BTC +42 /
   BNB +31). Cost: sizing scales the bear tail with it (§12 BNB Jun–Jul
   −37 @211 → ≈ −96 @550, −7% of pool, inside the 25% floor). Needs the
   six-window month-replay at 350/450/550 before shipping.
2. **Idle USDT to Binance Simple Earn (real mode only):** ~600 idle ×
   ~4–5% APR ≈ +2–3/month. Only once real money is live.
3. **Housekeeping:** decide run 7 `sell_when_starved`, and escalate a
   repeated `trend_activator_error` (> 4 in a row) to an Alert. The 1000
   loop ran 15 h and the only thing that surfaced was a mis-labelled
   `opportunity_idle`.

Not proposed again: entry variants E/G/R/H, top-ups, §13 exits, the
inventory cap, narrow grids, a standing blend, a core-heavy book, the 1d
gate, the outlook as a predictor. The end-of-leg bag (run 1 re-entered
85 366 on 09-22, as the replay did at 85 424) is the §12 pattern and
stays.

### §17 follow-up — candidate 1 swept: lending is leverage, not edge (2026-09-23)

`month-replay.php` gained a `lend` continuous grid arm: deploy 25 at any
anchor where the symbol's activator is active, gated otherwise. The trend
arm ran at `--active` = 350 (today) vs 350 + 0.75 × grid slice (BTC 494 on
the 192 grid, BNB 607 on the 343 grid). Windows Apr 1 → May 31,
Jun 1 → Jul 31, Aug 1 → Sep 23.

| window (BTC / BNB tape) | today: trend 350 + gated grids | lend: trend 494/607 + lend grids |
|---|---|---|
| Apr–May (+7.8% / +14.8%) | +33.1 | +50.8 |
| Jun–Jul (−14.9% / −17.4%) | **−74.1** | **−119.2** |
| Aug–Sep (+36.8% / +34.3%) | +143.5 | +201.5 |
| sum | **+102.6** | **+133.1** |
| sum / worst window | 1.39 | 1.12 |

The trend arm is linear in its slice. Every row above is the same trades
scaled up (BNB Jun–Jul −61 → −106, maxDD 83 → 145 = 11% of the pool).
Lending only scales them. It gains +30 over seven months (≈ +4/month) and
gives up 45 more in the worst month, and it lowers the return per unit of
worst drawdown. The grids lose little (−8 on the Aug–Sep tape), because they
are capped at 25 in those hours anyway. **Not recommended as a mechanism.**
If more trend exposure is wanted, raise `fleet_slot.target_slice`: same
effect, one knob, operator's call.

Housekeeping done the same day: run 7 `sell_when_starved` → 0 (+ Reload),
per the operator. `TrendActivator` now escalates a refused slice write to
one Alert once it has been refused `REFUSED_ALERT_AFTER` (4) times in 65
min, at most one per `PASS_ERROR_EVERY`
(`testARefusalRepeatedEveryPassEscalatesToOneAlert`).

## §18 Re-entry after a winning exit — X (below the exit) and S (half size), both refuted (2026-09-24)

Prompted by run 10 (BNB trend, episode 09-20→09-23): regime entry 761.56
→ stop +2.15 → re-entry 783.56 → stop +0.67 → re-entry 791.45 (hwm 799),
then held under breakeven (sell_at_loss OFF) at ~766. This is the §12
"last entry of the leg ends up underwater" pattern, and so is run 1's
85 366. R (new high) and C (EMA20 pullback) were already refuted, so two
untested variants were added to `trend-entry-sweep.php` and
`month-replay.php` (`--arms=BX,BS,BXS`):

* **X**: a regime re-entry after a stop-out in the same activation needs
  close < that exit's fill. The first entry and Donchian entries are
  unchanged.
* **S**: after a winning exit in the same activation, the next entry is
  half the target.

| arm | BTC bull | BNB bull | BTC bear | BNB bear | 2026 Σ (6 windows, §12 set) | Σ\|MTM\| | Aug 1→Sep 23 (BTC+BNB) |
|---|---|---|---|---|---|---|---|
| B live | +132.30 | +160.43 | −11.75 | −10.17 | +5.37 | 89.33 | +71.07 |
| X | +73.89 | +146.26 | −11.75 | −10.17 | +6.50 | 82.77 | +67.55 |
| S | +71.31 | +108.26 | −11.75 | −10.17 | −5.06 | 73.84 | +48.34 |
| XS | +40.20 | +97.31 | −11.75 | −10.17 | −3.11 | 70.56 | +46.57 |

Both refuted. X gives up 58 on BTC bull and 14 on BNB bull, buying +1.1 on
the 2026 sum. S shrinks the bag (−17% Σ|MTM|) by halving every re-entry,
including the winners: on BTC bull all 34 regime re-entries closed as
winners, and S roughly halves that tape. The bear tapes don't move, because
the arm enters once and holds. This confirms §12: the re-entries are where
the bull money is, and nothing visible at entry time separates the last one.
The bag is the price of the hold policy plus the re-entry rule, not a
separate defect. The only levers left are the ones already decided: the
target slice (sizing) and sell_at_loss.
