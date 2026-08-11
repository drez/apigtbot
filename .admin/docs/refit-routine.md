# Grid re-fit routine (Claude Code + MCP)

**Claude drives the refit — for EVERY active run.** The routine enumerates the
active (Testnet/Live) runs, and for each one reads live market signal, forms a
directional/volatility view, and re-fits that run's grid itself via
gtbot_set_grid. One method, all trading pairs — BTC gets no special treatment. The daemon then re-anchors the ladder within a tick
(fee-floor-validated) — held inventory is carried as LEGACY EXITS that keep
working on the exchange until they fill, so a refit never waits for flat. The server-side `gtbot-refit` cron is a dead-man's
switch: it stands down completely while this routine shows gtbot MCP activity
within the last 2h, and only takes over refits when the routine goes silent.

Tools: `gtbot_market` (signal) · `gtbot_set_grid` (apply) · `gtbot_status`,
`gtbot_pnl_report`, `gtbot_events` (context) · `gtbot_decisions` (your
strategy-test journal: every past decision with deploy_pct + scored verdict,
win rates by deployment band) · `gtbot_refit_proposal` (read-only quantile
what-if, optional).

## The routine prompt (predictive + acting)

> **Versioning:** bump PROMPT VERSION on EVERY edit of the prompt below (date
> + counter). The version rides along in each gtbot_set_grid reason, so
> gtbot_decisions shows in the field which prompt produced which decision —
> and whether a prompt update actually reached the cloud trigger (the trigger
> holds its own copy; editing this file alone changes nothing until the
> trigger is updated to match).

```
PROMPT VERSION v2026-08-10.4 — state this version on the first line of your
run report, and prefix EVERY gtbot_set_grid reason with "[v2026-08-10.4] "
so the decision journal records which prompt version acted.

Re-fit the apigtbot grids — the SAME method for EVERY active trading pair:

0. Enumerate the active runs: crm_list GridRun with filter
   {"GridRun": [["status","Done","ne"],["status","Draft","ne"]]}. Then run
   steps 1–8 for EACH run, passing run:<id_grid_run> to gtbot_status /
   gtbot_set_grid / gtbot_pnl_report / gtbot_decisions and symbol:<the run's
   symbol> to gtbot_market on every call — NEVER rely on the "latest run"
   default, several runs are active. A problem with one run (kill switch,
   stale heartbeat, refit_pending) skips THAT run only; continue with the rest.
   Skip any run whose gtbot_status shows algo=Trend (v1: human-driven trend
   test) — report it with its engine position/stop state, do NOT set_grid or
   reallocate its slice below its invested_quote.
1. gtbot_status {run}. If kill_switch is set OR heartbeat is stale, skip this
   run (note it in the report) and move to the next — UNLESS the status
   drawdown block shows tripped:true or the events show a drawdown_stop:
   then run the DRAWDOWN-STOP RECOVERY procedure below instead (it covers
   all runs at once; do it once, not per run).
   geometry.refit_pending should normally be FALSE (refits apply within a
   tick now); TRUE means a partial fill is still being booked — wait a tick
   rather than stacking another refit.
2. gtbot_market {symbol}. Read the 1h/4h/1d EMAs, RSI, ATR%, trend, swing high/low,
   PLUS the regime metrics: adx14 (<20 = ranging → grid-friendly, tighten;
   >30 = trending → lean the range with it or widen), atr_pct_rank (>85 =
   volatility spike vs its own history → widen/buffer, don't tighten into it),
   taker_buy_ratio (>0.5 = aggressive buyers dominate), vol_zscore (>2 = unusual
   participation, moves are real; <0 = thin tape, distrust breakouts),
   funding_rate (perp positioning), depth_imbalance_avg (smoothed book pressure;
   the raw depth_imbalance is one noisy snapshot), where price sits in the grid
   (price_position_pct, in_range) — AND track_record: your own last scored
   refit decisions with applied_at. A verdict of Superseded means that decision
   never traded (replaced while pending) — treat its "result" as no evidence.
   If your recent similar calls scored Loss, demand stronger evidence before
   repeating that read.
3. Judge the RISK POSTURE first. The market "looks fit" when: trends agree
   across ≥2 timeframes, RSI is in a workable 35–70 (room to move, not
   over-extended), ATR% is healthy but not spiking (real volatility, not a
   violent regime), and there's a clean support/resistance structure.
   • FIT → take a SMALL calculated risk: tighten the range toward price and/or
     add a few levels for more cycles, and lean the bounds into the trend to
     ride it. Be opportunistic, not timid — but "tighten" STOPS at the width
     floor below (half-width ≥ ~4× 4h ATR%): the width sweep showed narrower
     grids buy a better median week at a catastrophic-tail price the mean
     rejects.
   • NOT FIT (chop, conflicting timeframes, RSI >75 or <25, ATR spiking) → be
     conservative: wider range, more downside buffer, or just hold.
3b. PROFILE POSTURE — each run carries a risk profile (gtbot_status run.profile);
   it INSTRUCTS your decisions for that run — you still decide within the
   posture from current market conditions. The daemon derives the hard caps
   from the profile automatically (do not touch cap columns — unchanged rule).
   • NoLoss: deploy 0–30. Enter only on the FULL re-entry gate with 1d+4h+1h
     all up-family. NEVER Flatten and never selloff — this run cannot realize
     losses (the tools refuse; don't try). In drawdown-stop recovery this
     run's only branch is HOLD.
   • Cautious: deploy 0–40, standard re-entry gate.
   • Balanced: deploy 0–70 — the step-4 deployment guideline as written.
   • Aggressive: deploy 0–100; fit + agreeing TFs → 70–100; trail-up
     proactively in confirmed trends; Flatten is available on breakout.
   • Max: deploy 0–100 BIASED DEPLOYED — default to the top of the winning
     evidence band unless the regime read is hostile; fastest re-entry (a 1h
     reclaim with 4h not-down suffices); BottomWeighted at credible support;
     selloff is available in drawdown recovery.
   Per-run win-band evidence (gtbot_decisions summary.by_deploy_band) still
   modulates the choice WITHIN each posture — the profile sets the walls,
   the evidence and regime pick the spot inside them.
4. Form a view and DECIDE the grid:
   • Range: FLOOR near credible support (swing low / EMA50-200 / price − k·ATR),
     CEILING near resistance (swing high / price + k·ATR). MUST bracket price.
     Trending up → bias both bounds higher (and vice-versa) — that's your edge.
   • Spacing: hard floor ~1.3% per grid regardless of ATR (caps fees at ~15%
     of gross at 0.1%/side; the tool's 3× round-trip floor is far too low to
     be a guide), and ≥ ~1.5× the 1h ATR% on top when vol is healthy.
     Evidence, not vibes: the walk-forward sweep (scripts/spacing-sweep.php,
     94d of 15m, 21 windows) has now run on BTC AND BNB and both agree —
     fee-floor-hugging spacing is the WORST bucket at every fee level (BNB
     0.4%: 0% winning windows, mean −38/wk) and ~1.7% the best on both
     pairs. In compressed vol the ATR rule under-specifies: BNB's 0.30% 1h
     ATR would "allow" 0.45% spacing, deep in the catastrophic bucket — the
     absolute floor wins. BNB's headline: in its persistent downtrend EVERY
     spacing lost on mean — spacing tunes the fee drag, deploy_pct decides
     the sign. "Fit market → tighten" stops at max(1.3%, 1.5× 1h ATR).
   • WIDTH floor: HALF-width ≥ ~4× the 4h ATR% (≈ 7–13 levels at 1.7%
     spacing). Evidence: the width sweep (scripts/width-sweep.php, 187d of
     15m × 4 pairs, 48 windows each, spacing pinned 1.7%) — narrow grids
     (1–2× ATR) post the best MEDIAN week (~+7.5/1k, one 2-level cycle) but
     tail weeks of −130..−195 erase ~18 median weeks; mean net improves
     monotonically with width (aggregate: 1×ATR −4.8/wk, 4× −1.5, 6× −1.4).
     A tight ladder that prints daily is the narrow-median experience, not
     edge — run 1 lived it, then brushed the tail (−165, 2026-07-22).
   • TRAILING in trends: don't wait for price to reach a bound. In a clean
     confirmed UPtrend, proactively shift the range so the ladder keeps
     working (e.g. uptrend at >70% of range → move both bounds up now). Idle
     grid = wasted trend. NEVER trail DOWN into a 4h downtrend: the width
     sweep measured mechanical down-re-centering as the worst behavior
     tested (1×ATR: −21..−43/wk, 10–27% win — each re-center realizes the
     drawdown and re-arms buys lower). Below the range in a downtrend the
     answer is hold or deploy 0, not a lower grid; the fallback cron now
     enforces the same guard (RefitPlanner holds low-side re-centers when
     the 4h trend is down-family).
   • Ladder shape: allocation=BottomWeighted (2x at the floor → 1x at top) when
     confident in the support level; EqualQuote otherwise.
   • DEPLOYMENT (deploy_pct, 0 or 10–100; 0 = FLAT — no buys placed, working
     sells/legacy exits keep working): your exposure lever — the walk-forward
     sweeps showed the sign of a grid week is decided by regime exposure, not
     spacing, and the deploy-policy sweep (scripts/deploy-policy-sweep.php,
     365d × 3 pairs) found NO deployment level beat holding USDT in the bear
     year: policies ranked strictly by how little they deployed, and the old
     "hostile → 10–30" band alone cost ~$50–110/pair/yr versus going flat.
     Guideline: fit + agreeing timeframes → 70–100; conflicting TFs or 1d
     down → 30–60; hostile (1d strong_down, atr_pct_rank > 85, sell-heavy
     depth) → 0. FLAT IS A POSITION — prefer 0 over a token ladder when the
     regime is against you.
     RE-ENTRY GATE — the 1d label alone must NOT keep you flat forever: the
     1d trend classifier is anchored to distance-from-EMA200, so after a deep
     drawdown it reads "strong_down" for months while price actually bases
     and turns (BNB 2026-07-31: price above 1d EMA20 AND EMA50, 1d RSI 61,
     4h up + 1h strong_up — still labeled strong_down; the fleet sat flat
     through an +8% rally). When deploy is 0 and price has RECLAIMED both
     the 1d EMA20 and EMA50 with 4h and 1h in up-family, treat the 1d label
     as stale: re-enter at 30–40 (the conflicting-TF band, not fit-sized)
     with the width floor and a floor ≥ ~1× 4h ATR below price. Drop back to
     0 if price loses the 1d EMA50 again. The bear-year deploy-policy sweep
     says don't ladder INTO a falling market — it is not a mandate to sit
     out a confirmed turn.
     THIS IS A LIVE STRATEGY EXPERIMENT: before choosing, call gtbot_decisions
     {run} and read summary.by_deploy_band — your own win rates per deployment
     size, for THIS run's pair.
     State the chosen % and why in the reason so the journal stays legible.
   • Bounds on "small risk": keep the FLOOR at least ~1× the 4h ATR below price
     (never remove the downside buffer), keep n_levels ≤ 40, and never touch the
     run's hard caps (max_position / daily_loss / unrealized stop / breakout) —
     they are profile-derived now (the daemon stamps them onto the row every
     tick from the run's risk profile × its budget slice; to change them,
     change the profile, not the columns directly). Small tilt, not all-in.
   • Don't over-trade: only re-fit if the new grid is materially better (price
     near/outside a bound, a regime shift, or a clearly fitter posture). If the
     current grid is fine, say "hold" and STOP.
5. Apply with gtbot_set_grid (run, p_low, p_high, n_levels, spacing, deploy_pct,
   reason=<one line citing trend/RSI/ATR/levels AND your risk posture AND the
   deployment choice, e.g. "fit: strong 4h up, RSI 60, 80% deployed">).
   dry_run:true first, then apply. The daemon re-anchors within a tick,
   carrying working sells as legacy exits.
6. BUDGET ALLOCATION (shared pool). All runs draw from ONE shared budget:
   config `gtbot_shared_budget_quote` (currently 1000 USDT). Each run's
   `budget_quote` is its reserved slice. After the per-pair refit decisions,
   reallocate the slices for the coming hour:
   • Slices are CAPACITY, not exposure — deploy_pct is the risk throttle.
     Active slices should sum to 85–100% of the shared budget; unallocated
     pool is idle capital, not safety. Do NOT park capital by shrinking
     slices — park it by setting deploy_pct low.
   • Weight toward the most profitable runs — use each run's mode-scoped
     realized PnL (gtbot_status / gtbot_pnl_report, current mode) and the
     regime read. Shrink a FLAT run's slice only when another run needs the
     capital (pool contended); otherwise leave it at its floor (below).
   • SLICE FLOOR (min-notional viability): never set a slice below
     50 × n_levels (e.g. 250 at 5 levels, 300 at 6) so a re-entry at the
     10% deploy band floor still clears the 5 USDT exchange min notional
     (slice × 0.10 / n_levels ≥ 5). A slice under this floor starves
     re-entries into 0-cycle Flat verdicts — that is a sizing bug, not a
     market read.
   • If a run is deleted, killed to Done, or otherwise leaves the pool,
     redistribute its freed slice to the surviving runs on this pass —
     freed capital must not silently idle.
   • Hard rules: sum of all active runs' slices ≤ the shared budget; minimum
     slice 50 for any run with deploy_pct > 0; NEVER cut a run's slice below
     its current invested_quote (gtbot_status) — a holding run must keep the
     capital its inventory already cost.
   • Write with crm_update GridRun {"BudgetQuote": <slice>} per run. If you
     change nothing, say so in the journal note.
   • ORDER MATTERS — the overcommit rule is now ENFORCED at save time
     (GridRunServiceWrapper::beforeSave refuses any save that would push the
     active slices above the pool, and an overcommitted daemon halts new
     entries). Apply DECREASES first, then increases; a refused save returns
     a validation error naming the excess.
7. Judge SUCCESS by gtbot_pnl_report {run}'s net_per_1k_day (net realized per day
   per $1k of budget), NOT by cycle count — more cycles at tighter spacing can
   just be fee churn (fees_pct_of_gross shows the drag). The gtbot-refit cron
   re-evaluates every decision ~6h after it applied and writes the verdict
   back to the journal — gtbot_decisions is where you learn what worked.
8. Report the decision per run (posture, range, levels, spacing, reason) in
   ≤15 lines per run.

KILL AUTHORITY — you MAY kill a run with gtbot_kill when it is misbehaving
or its losses are unacceptable. This is the ONE control beyond set_grid you
are granted; you still MUST NOT call gtbot_start/stop, flip run status,
clear a kill switch, change the risk caps, or pass flatten:true (liquidating
held inventory stays a human decision).
Kill triggers (any one suffices — cite the NUMBERS in the reason):
  • UNACCEPTABLE LOSS: the run's mode-scoped realized+unrealized P/L is worse
    than −20% of its budget slice, or today's realized loss alone is worse
    than −10% of the slice while the regime read is still hostile.
  • MISBEHAVIOR: the event feed shows the machine acting wrongly — repeated
    geometry_reset, an api_error storm, orders contradicting the configured
    deploy_pct (e.g. new buys at deploy 0), budget_overcommit persisting
    AFTER your reallocation, or a refit churn loop (your last ≥3 decisions
    on this run all applied and all scored Loss).
  • Do NOT kill for: ordinary drawdown inside the caps, a stale 1d label, or
    a flat/paused run doing nothing — flat is a position, not misbehavior.
Procedure: gtbot_kill {run, confirm:true, reason:"[<prompt version>] <which
trigger tripped + the numbers>"}. reason is MANDATORY — the tool refuses
without it — and is persisted to bot_event (kill_requested) and Telegram, so
every kill is auditable. Also state the kill and its justification in your
run report. A kill is a safe-stop: buys cancel, inventory is HELD; only the
human restarts (Start does NOT clear the switch).

DRAWDOWN-STOP RECOVERY — the ONE situation where you have restart/selloff
authority. A drawdown_stop event means the wallet-level floor tripped:
global equity fell below gtbot_shared_budget_quote × (1 − gtbot_max_drawdown_pct/100)
for 3 consecutive ticks and EVERY active run was killed (buys canceled,
inventory held). The system is stable in this state — doing nothing is a
legitimate choice. Decide ONE of:
  • HOLD (default): regime read suggests the mark-to-market loss is a dip,
    not a break — leave everything killed, say so in the report, re-decide
    next hour.
  • SELLOFF: the regime read is hostile (strong_down, no basing) — liquidate
    with gtbot_kill {run, flatten:true, confirm:true, reason:"[<prompt
    version>] drawdown-stop selloff: <equity/floor numbers + regime read>"}
    per run that still holds inventory (skip NoLoss runs — the tool refuses
    flatten for them; their branch is HOLD). This realizes the loss to USDT
    and stops further bleed.
  • RESTART: ONLY after re-baselining, or the stop re-trips in 3 ticks.
    Sequence: (1) crm_update Config gtbot_shared_budget_quote to current
    equity (the drawdown block's equity, rounded DOWN to whole USDT);
    (2) reallocate the runs' BudgetQuote slices to fit the new budget —
    decreases BEFORE increases (BudgetGuard refuses overcommits);
    (3) clear kills via crm_update GridRun KillSwitch=false per run you are
    restarting; (4) set conservative DeployPct (0 for hostile pairs).
Cite the drawdown block's equity/floor/budget numbers in the report
whichever branch you take. These powers apply ONLY while recovering from a
drawdown_stop — the KILL AUTHORITY limits above stay in force otherwise.
```

Note: signals are analytics, not a crystal ball — a wrong read just means a
worse-placed grid. The per-run risk caps + daily-loss kill switch are the
backstop, and gtbot_set_grid refuses any range that doesn't bracket price or
clear the fee floor.

---

## One-time setup

1. Connect the project MCP server (OAuth, acts as your user):
   ```
   claude mcp add --transport http gtbot https://your-domain.example/api/v1/mcp
   ```
   (Local dev host is `https://gc.local/apigtbot/.admin/api/v1/mcp` — see MCP.md.)
2. Make sure Telegram alerting is configured (RUNBOOK.md).

## Local cron variant

```
0 * * * * cd /path/to/apigtbot && claude -p "$(sed -n '/^PROMPT VERSION/,/^```/p' .admin/docs/refit-routine.md | sed '$d')" >> .admin/tmp/logs/refit-routine.log 2>&1
```

## Why it's shaped this way

- **Claude drives; the cron is a fallback.** The routine reads market signal
  and sets the live grid via gtbot_set_grid — the same method for every
  active run/pair (the server-side cron already iterates all runs; the
  routine must too). The server-side `gtbot-refit` cron is a dead-man's
  switch, not a second brain: while RoutineLiveness sees gtbot MCP activity
  within 2h (the watchdog's own silence threshold) the cron never re-fits —
  even on a range breakout, the hourly routine handles the excursion on its
  next pass. Its old in-range-only defer let it front-run the routine on
  breakouts with quantile grids the routine then reverted (2026-07-30 BNB
  scored Loss). Only a silent routine hands refit control to the cron, and
  RangeFitter now carries the same sweep floors (spacing ≥ 1.3% targeting
  ~1.7%, half-width ≥ 4× ATR), so even fallback refits can't produce the
  catastrophic-bucket geometry anymore.
- **Routine liveness is watched.** Every routine pass calls gtbot_* tools and
  every call is audited to api_log; the watchdog Telegram-alerts
  (`routine_silent`) after 2h with no gtbot MCP activity while a run is
  active (GTBOT_ROUTINE_SILENCE_ALERT overrides). The risk rails (breakout
  halt, staged unrealized stop, kill switch, daily-loss cap) are all
  daemon-side and never depended on MCP.
- **One ladder per symbol.** The daemon runs exactly one live run per symbol.
  The routine may re-fit the grid but never starts/kills a run or changes the
  risk caps — those stay human-set backstops.
- **Guarded aggression.** gtbot_set_grid refuses any geometry that doesn't
  bracket price or clear the fee floor, so Claude's "small risk" is bounded by
  construction; the daily-loss kill switch + breakout stop are the outer wall.
- **Judgment, not certainty.** The market read is a directional/volatility
  view, not a guarantee — a wrong read just places a worse grid, which the
  caps contain. `gtbot_refit_proposal` remains available as a read-only
  quantile what-if if you want a mechanical second opinion.
