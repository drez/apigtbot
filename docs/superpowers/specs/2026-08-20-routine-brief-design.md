# Routine brief + quiet-exit — design (2026-08-20)

Goal: cut the hourly refit routine's token spend by ≥60% without moving the
set_grid judgment off Claude. Operator choice: "brief + quiet-exit" (no
auto-apply).

## Why the spend is what it is

Per hourly pass today: prompt ~3.7k words (~5k tokens) + 14 tool schemas
(~2.5k) on EVERY turn, and ~12–15 turns (crm_list + per run: status,
market, decisions, pnl_report, proposal, set_grid) — each turn re-sends the
whole growing context. Turns dominate; payload size is second.

## What ships

### 1. `gtbot_routine_brief` (new MCP tool, `src/App/Mcp/Tools/GtbotRoutineBriefTool.php`)

Read-only (`GridRun r`). Args: none required; `run` (int) narrows to one.
Returns ONE compact JSON:

```
shared: { budget, equity, headroom, drawdown:{floor,tripped}, trend_arm:{run,state,last} }
material_change: bool
changes: string[]            # human lines, e.g. "run 7: price left band (pos 104%)"
runs: [ {
  id,label,symbol,algo,status,profile,simulated,
  flags: string[]             # kill, heartbeat_stale, refit_pending, outside_band, near_bound,
                              # hostile, reentry_gate, data_stale, quiet_too_long, trend_managed
  attention: bool
  slice, deploy_pct, floor_slice, invested, pnl:{d7,total,cycles_d7}
  geometry:{p_low,p_high,n,spacing_pct,pos_pct,in_range}
  signal:{ price, '1h':{trend,rsi,atr_pct,ema20,ema50}, '4h':{trend,adx,er20,chop,atr_pct,atr_rank,ema20,ema50,ema200}, '1d':{trend,rsi,ema20,ema50,ema200}, age_s }
  regime:{ class, hostile_cap, reentry_gate }      # TrendRegime::classify + RegimeGate + 1d-EMA reclaim
  candidate:{ p_low,p_high,n_levels,spacing,deploy_band:[lo,hi],why } | null   # grids only
  track:{ by_deploy_band, last3 }                  # DecisionScorer compact
  trend:{ entry,qty,stop,state } | null            # Trend runs only
} ]
```

Computation (`App\Domains\Bot\RoutineBrief`, pure where possible):
- signal digest from `MarketStore::summaries` (numbers rounded: prices 2dp,
  pct 2dp, ratios 2dp).
- `regime.class` = `TrendRegime::classify(4h,1d)`; `hostile_cap` =
  `RegimeGate::capDeployPct(100,…)['pct']` when hostile else null;
  `reentry_gate` = price > 1d EMA20 & EMA50 AND 4h,1h up-family.
- `candidate`: `RangeFitter::candidates` on stored 4h candles → best viable
  by backtest realized (as refit_proposal) → then server-side floors:
  spacing ≥ max(1.3%, 1.5×1h ATR%), half-width ≥ 4×4h ATR% (widen
  symmetric around price), bounds bracket price; `deploy_band` = profile
  walls ∩ regime cap (hostile→[0,25], `why` says "prefer 0"); when
  reentry_gate && deploy==0 and not hostile → [30,40]. `why` one sentence.
- `flags`/`attention`: attention = any of kill, heartbeat_stale,
  outside_band, near_bound (pos <10% or >90%), regime class changed,
  hostile && deploy>25, reentry_gate && deploy==0, data_stale, drawdown
  tripped, quiet_too_long (no bot_decision for this run in N h; config
  `gtbot_brief_max_quiet_hours`, default 6), trend transition since last
  brief (Trend runs: report-only attention).
- `material_change`: fingerprint per run {class,in_range,pos_bucket,
  deploy,slice,profile,geometry_hash,kill,hb_stale,refit_pending} +
  {newest Alert/Error event id, newest trend transition id, drawdown
  tripped}; compared with config `gtbot_routine_brief_last` (JSON); the
  tool overwrites it after computing. `changes[]` lists the diffs. First
  call (no stored fingerprint) → material_change=true.
- Size budget: JSON ≤ 6000 bytes for 3 runs (test-pinned).

### 2. Prompt v2026-08-21.1 (`docs/refit-routine.md`, ~1.2k words)

- Step 0: `gtbot_routine_brief`. If `material_change=false` AND no run has
  `attention` → report one line ("v… quiet: <n> runs, no change") and stop.
- Otherwise for each `attention` run (grids only): judge from the brief;
  `candidate` is the starting point — deviate with a stated reason;
  `gtbot_set_grid`. Keep: fit/not-fit test, profile walls, width/spacing
  floors (numbers), trail-up / never trail-down, re-entry gate, regime-gate
  override semantics, allocation order (decreases first; floors), trend arm
  hands-off, drawdown recovery procedure (pointer + the 4 steps).
- Sweep narratives → `docs/refit-evidence.md` (new; not loaded by the
  routine). Remove crm_list enumeration and the per-run status/market/
  decisions/pnl call chain (still available for drill-down, say so).

### 3. Tool-schema diet

Trim the 14 `description()` strings to 1–2 sentences; input schema
descriptions shortened. No semantic change; tests that assert on
descriptions (if any) updated.

### 4. Tests

`tests/Custom/Bot/RoutineBriefTest.php`: injected summaries/candles;
asserts payload shape, rounding, flags+attention table, candidate floors
(spacing/width/bracket/deploy band per profile×regime), fingerprint →
material_change + changes lines, quiet_too_long, size budget, Trend-run
shape. Tool registration via the existing ToolRegistry glob (no change).

## Out of scope

Auto-apply; routine schedule; changing any enforcement; dashboard.

## Success measure

Quiet hour: 1 tool turn + ≤150 output tokens. Busy hour: 1 brief +
1 set_grid per flagged run. Prompt ≤1.3k words. Brief ≤6 KB.
