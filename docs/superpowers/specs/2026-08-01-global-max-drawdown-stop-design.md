# Global max-drawdown stop (25% of shared budget)

Date: 2026-08-01
Status: approved by Fred (brainstorming session)
Builds on: 2026-08-01-shared-wallet-global-switch-design.md (one shared wallet/budget),
2026-07-31-simulated-trading-design.md (paper mode, mode-scoped stats)

## Problem

Every per-run rail (unrealized cap, daily loss, breakout halt) stops a run from
ADDING exposure, but a killed run HOLDS its inventory — in a sustained bear all
runs can be killed at their caps and the wallet keeps bleeding with alerts only.
Before flipping to real money Fred wants a hard wallet-level floor: total loss
across the whole system must not exceed 25% of the shared budget.

## Decisions

- **Floor formula**: floor = `gtbot_shared_budget_quote` × (1 − pct/100).
  25% of 1000 = max loss 250 → trip when global equity < 750. New config key
  `gtbot_max_drawdown_pct` = 25; missing/empty row ⇒ default 25; an explicit
  `0` disables. (Confirmed with Fred: 25% means 250 $ of the total budget.)
- **Breach action**: cancel buys + kill switches ON across ALL active runs,
  HOLD inventory — the hourly routine then decides selloff and/or restart.
  (Rejected: auto-flatten — Fred wants the routine, not the daemon, to decide
  liquidation; rejected: flatten-in-real-mode-only.)
- **Enforcement point**: every daemon tick, next to the other risk rails.
  (Rejected: watchdog cron — 1-min granularity, extra moving part; rejected:
  check-on-fill — misses pure mark-to-market drift, the case that matters.)
- **Wick guard**: breach must hold 3 consecutive ticks (same
  BREACH_CONFIRM_TICKS pattern as the unrealized stop, prod 2026-07-28 lesson).
- **Recovery is deliberate**: a restart below the floor re-trips in 3 ticks by
  design. The documented resume path is: re-baseline `gtbot_shared_budget_quote`
  to current equity (new era; slices reallocated under BudgetGuard — decreases
  before increases), then clear kill switches.

## 1. DrawdownGuard (App\Domains\Bot)

Sibling of BudgetGuard. Pure-ish class over config + wallet + run rows:

- `maxDrawdownPct(): string` — config `gtbot_max_drawdown_pct`, default '25'.
- `floor(): ?string` — null when disabled (pct ≤ 0), else budget × (1 − pct/100).
- `equity(bool $simulated): array{equity: string, unpriced: string[]}`
  - Paper: `SimWallet::balances()` — USDT qty + Σ(base qty × price).
  - Real: ONE exchange account stamped per-run — quote balance from the
    freshest run row (`bal_quote`), base assets deduped by asset across runs
    (`bal_base` of the freshest row per symbol).
  - Price per base asset: freshest `grid_run.last_price` where symbol =
    asset + 'USDT' (any status; freshest `last_tick_at` wins). An asset with
    no price counts as 0 (fail-closed, understates equity) and is returned in
    `unpriced` for a Warn log.
- `check(bool $simulated): ?array{equity, floor, budget, unpriced}` — null when
  disabled or equity ≥ floor (strict `<` trips).
- `tripAll(): int` — sets `kill_switch = 1` on every run with status in
  BudgetGuard::ACTIVE_STATUSES; returns how many rows changed.

## 2. Daemon hook

In `tick()`, immediately after `checkUnrealizedStop()`'s kill re-check (so it
never runs on an already-killed run — killed daemons early-return before it,
which is also the no-re-fire guarantee):

- `checkGlobalDrawdown($price)`: DrawdownGuard::check(run mode). Breach →
  increment `ddBreachTicks` (Warn `drawdown_breach` on the first tick);
  < BREACH_CONFIRM_TICKS ⇒ wait. Confirmed ⇒ `tripAll()`, Alert
  `drawdown_stop` (equity, floor, budget, runs killed — fans out to Telegram),
  set own kill_switch in memory, `ensureKilled()` (cancel buys, hold).
  No breach ⇒ reset counter.
- Other daemons see their `kill_switch` on the next tick reload and take the
  existing external-kill path (cancel buys once, hold, `watchKilledDrawdown`
  escalation keeps running).
- Paused daemons don't reach the check (consistent with the other rails);
  any one live daemon is enough to trip everyone.

## 3. Routine authority (refit-routine.md)

New step + carve-out in the KILL AUTHORITY section: after a `drawdown_stop`
event the routine IS authorized (normally forbidden) to decide recovery:

- **Selloff**: `gtbot_kill {run, flatten:true, confirm:true}` per run it
  chooses to liquidate, reason-stamped.
- **Restart**: only after re-baselining — set `gtbot_shared_budget_quote` to
  current equity (crm_update Config), reallocate slices to fit (decreases
  before increases, BudgetGuard invariant), then clear kills via crm_update
  GridRun `KillSwitch` false. Restarting below the floor re-trips in 3 ticks.
- **Hold**: doing nothing is legitimate (kill+hold is stable).

## 4. Visibility

- `gtbot_status` gains a global `drawdown` block: `{equity, floor,
  max_drawdown_pct, budget, tripped}` (tripped = equity < floor), computed for
  the run's current mode.
- Dashboard shared-wallet tile: one line `equity X / floor 750` (red when
  under).

## 5. Seeding & deploy

`gtbot_max_drawdown_pct` = 25 seeded in the same base seed (custom*.sql, no
`gc:dev-only` marker) that seeds `gtbot_shared_budget_quote`, idempotent
insert. Ships with the normal `./gc deploy`.

## 6. Tests

- `DrawdownGuardTest` (mirrors BudgetGuardTest harness): floor math incl.
  disabled/default; paper equity from sim_wallet + prices; real-mode dedupe
  (two runs, one base asset counted once, quote counted once); unpriced asset
  counts 0 and is reported; check() strict-< boundary (equity == floor is OK).
- Daemon-level test (mirrors DaemonRefitGuardTest): 3-tick confirm (2 breach
  ticks + recovery ⇒ no trip), confirmed breach trips ALL active runs'
  kill_switch, tripping daemon cancels its buys, no re-fire once killed.
