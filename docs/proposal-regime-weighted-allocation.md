# Proposal: regime-weighted allocation between the Grid and Trend arms

**Status: REFUTED AS WRITTEN (2026-08-13) — see Sweep results below.** The
allocation sweep ran the day after drafting: regime-weighting did beat
static 50/50 on both symbols (its pre-registered bar), but BOTH lost badly
to grid-only, because the 187d tape contained ZERO TRENDING-UP windows and
the Trend arm bled through every MIXED/RANGE window it was forced (by the
30% floor) to hold. The salvageable idea is event-driven activation (Trend
weight ~0 until the classifier actually fires TREND_UP), not a standing
floor. Do not lift the A/B pin on the strength of this proposal.

## Premise

The two arms earn in complementary regimes — the grid harvests chop, the
Trend engine rides confirmed legs — so the blend's value is NOT a higher
mean (that's just the weighted mean of the arms) but the ability to shift
weight toward the arm whose regime is on. The regime read that already
gates deploy_pct (er20 / chop14 / adx14 / trend families, regime-sweep
2026-08-11) is the same read that should drive the weights. Slices stay
CAPACITY (deploy_pct stays the risk throttle): weighting an arm up gives
it room to act when its trigger fires; it does not force exposure.

## Classification (per symbol, once per hourly pass)

Read the stored 4h summary (1d as confirm). Exactly one class applies —
checked in this order: **HOSTILE → TRENDING-UP → RANGE → MIXED**, first
match wins:

| Class | Condition (4h unless noted) | Target weight (Trend/Grid) |
|---|---|---|
| TRENDING-UP | 4h AND 1d trend up-family, adx14 ≥ 25, er20 ≥ 0.35 | 70 / 30 |
| RANGE | chop14 ≥ 55, OR (adx14 < 20 AND trend sideways) | 30 / 70 |
| HOSTILE | trend down-family, OR adx14 ≥ 30 without the TRENDING-UP alignment | freeze weights |
| MIXED | anything else | drift toward 50 / 50 |

Rationale per row:
- TRENDING-UP needs all three confirmations because ADX alone is
  direction-blind and er20 alone flipped sign between symbols in the sweep.
- RANGE is the grid's paycheck regime — but HOSTILE is checked first
  because the sweep showed BTC's deepest chop was downtrend consolidation:
  a high chop14 with a down-family trend is a trap, not a range, and must
  classify HOSTILE.
- HOSTILE freezes rather than reallocates: a hostile tape says "don't be
  exposed" (deploy_pct's job — the enforced regime gate already caps grid
  entries at 25 and the Trend arm's own entry gate keeps it flat); it says
  nothing about which arm is better, so moving capacity on it is noise.

## Mechanics (all inside the existing allocator rules)

1. **Step limit**: move at most 20 percentage points of the symbol's pool
   per pass. A regime flip changes weights over 1–2 hours, not instantly —
   re-fits are cheap but slice churn pollutes the decision journal.
2. **Hysteresis**: take the FIRST step in a new direction only after the
   same class on 2 consecutive passes. (One 4h candle can flip er20/chop14;
   two passes ≈ requires the read to survive at least one new candle.)
3. **Weight bounds**: neither arm below 30% of the symbol pool. A 0-weight
   arm cannot catch the regime flip that is the entire point of running
   both. The 30% floor also keeps both arms above their viability floors:
   grid ≥ 50 × n_levels; Trend ≥ ~100 so the 10% deploy band still clears
   two 5-USDT tranches (entry = ≤4 tranches summing to deploy% × slice).
4. **Evidence modulator (±10)**: after the class sets the target, tilt up
   to 10 points toward the arm with better trailing-14d mode-scoped
   net_per_1k_day (gtbot_pnl_report). Regime decides, evidence tilts —
   never the reverse (performance-chasing was the allocator failure mode
   the deploy-policy sweep punished).
5. **Existing hard rules unchanged**: decreases before increases
   (BudgetGuard); never below invested_quote; active slices sum to 85–100%
   of the shared pool; NoLoss-profile runs keep their own posture rules;
   cross-symbol allocation (BTC pool vs BNB pool) stays under the current
   profitability weighting — this proposal only splits WITHIN a symbol that
   runs both arms.

## Worked example (BTC pool 500, currently 250/250)

- Pass N: 4h flips to up-family, 1d up, adx 27, er20 0.41 → TRENDING-UP,
  but first occurrence → no move (hysteresis).
- Pass N+1: still TRENDING-UP → step: Grid 250→200, Trend 250→300
  (decrease first, then increase; 20-pt step of the 500 pool = 100).
- Pass N+2: still TRENDING-UP. Trend's PnL trails grid over 14d, so the
  modulator lowers the target from 70/30 to 65/35 → Grid 200→175,
  Trend 300→325, and that's the resting point (the modulator adjusts the
  TARGET the steps walk toward, never adds an extra move of its own).
- Pass N+9: 4h down-family → HOSTILE → weights freeze at 325/175; grid
  deploy capped 25 by the enforced gate, Trend flat by its own entry gate.
  Capital is parked by deploy, not by slice.

## Validation before it goes live (same bar as every other rule)

Build `scripts/allocation-sweep.php` on the width-sweep protocol: replay
15m history; per walk-forward window compute the class from fit data;
compare {static 50/50, regime-weighted, best-single-arm-hindsight} where
the grid arm's window net comes from the Backtester and the trend arm's
from the trend-sweep harness. The proposal earns a routine-prompt slot
only if regime-weighted beats static 50/50 on mean AND worst-window on
BOTH symbols — the same cross-symbol bar the deploy gate had to clear.
If it only matches static, keep static: fewer moving parts wins ties.

## What this is NOT

- Not a change to deploy_pct policy — the hostile→0 guideline, the
  enforced 25 cap, and the re-entry gate all stay exactly as written.
- Not cross-symbol reallocation — BTC vs BNB weighting is out of scope.
- Not a second brain: the routine still makes the call each pass; these
  are its written rules, enforced the same way the rest of step 6 is.

## Sweep results (2026-08-13, scripts/allocation-sweep.php, 24×7d windows/symbol)

| policy | BTC total/1k | BTC worst wk | BNB total/1k | BNB worst wk |
|---|---|---|---|---|
| static 50/50 | −79.66 | −44.23 | −88.32 | −15.11 |
| regime | −71.22 | −44.23 | −74.90 | −14.70 |
| **gridOnly** | **−43.75** | −88.46 | **−47.81** | −75.48 |
| trendOnly | −115.56 | −37.64 | −128.82 | −36.12 |
| hindsight (bound) | +114.22 | −11.10 | +167.88 | −11.32 |

Findings:
1. **Regime > static on both symbols** (mean AND worst) — the classifier's
   tilts point the right way. But that cleared a bar nobody should care
   about, because…
2. **Any standing Trend weight was a drag on this tape.** Class mix: BTC
   13 HOSTILE / 2 RANGE / 9 MIXED, BNB 14/4/6 — **zero TREND_UP windows in
   48**. The bull-run thesis is UNTESTED by this tape; what the sweep
   measured is the cost of carrying the Trend arm through 187 days of
   not-bull: ~2–3.5/wk per $1k of drag vs grid-only. The 1h 20-bar
   breakout fires plenty in chop and gets whipsawed (BNB RANGE windows:
   trend −25.33/wk vs grid +0.75).
3. **Caveats cut both ways**: the 7d MTM truncation clips exactly the long
   winners that justify a trend arm, so trendOnly is understated — but not
   by 70+ per $1k. And trend-sweep.php's own validation ran on ~300 stored
   1h bars (12.5d, explicitly "not sweep-grade") — the Trend cell has never
   passed a long-tape walk-forward. The live A/B soak is currently the only
   fair test of the Trend arm; interpret its results with this sim's
   chop-whipsaw warning in mind.
4. **Hindsight gap is huge** (+114/+168 vs best real policy −44/−48): the
   arms ARE complementary window-by-window — the information exists, this
   classifier just can't extract it at fit time. A better classifier is
   worth hunting for; a standing 30% floor is not.

Revised recommendation: keep grid-only capital allocation as the default;
treat the Trend arm as an EVENT-DRIVEN activation (weight ~0, activated
toward 70 only on a fired TREND_UP class, deactivated on class exit), and
re-run this sweep on a tape that actually contains a confirmed bull leg
before trusting the activation rule. The A/B pin stays until then.
