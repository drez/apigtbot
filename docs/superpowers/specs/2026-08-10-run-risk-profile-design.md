# Per-run risk profile (NoLoss → Max)

**Date:** 2026-08-10 · **Status:** approved design, pending implementation

## Goal

One dropdown on a grid run — `profile` — that sets the run's whole risk
posture, from "never realize a loss" to "maximum opportunity capture". The
profile is a **live policy**, not a one-time preset: daemon-side caps are
derived from it continuously (as ratios of the run's current budget slice),
and the hourly refit routine reads it as an instruction, deciding within the
profile's posture according to current market conditions. No hard server-side
clamp on deploy_pct — the routine judges; the daemon enforces the caps.

## Decisions made during brainstorming

- **Mechanics:** live policy (profile is source of truth; cap columns become
  derived/read-only). Preset-once and preset+hint were rejected.
- **"No loss" semantics:** *never realize* — sells only at/above cost+fees,
  breakout locked to HaltAndHold, no selloff at a loss ever, unrealized
  drawdown tolerated indefinitely. The residual risk is paper drawdown and
  time, not realized USDT.
- **Scale:** five steps — NoLoss, Cautious, Balanced, Aggressive, Max.
- **Routine interplay:** the profile *instructs* the routine (posture block in
  the prompt); the routine still chooses deploy_pct within that posture from
  regime evidence. Enforcement of the never-realize rule is server-side; the
  deploy band itself is guidance, not a clamp.
- **Architecture:** Approach A — profile stamps the *effective* cap columns on
  `grid_run` each tick, so every existing consumer (RiskManager, dashboard,
  gtbot_status, reports) keeps reading the columns it reads today.

## 1. Schema (`schema/main.hjson`, grid_run)

New column, placed in the "Grid + budget" tab near the top:

```
"profile('Risk profile')": ["enum(NoLoss, Cautious, Balanced, Aggressive, Max)", "required", "default:Balanced"]
```

The five derived columns become read-only in the GUI via
`set_readonly_columns`: `daily_loss_limit_quote`, `max_unrealized_loss_quote`,
`max_position_quote`, `max_order_quote`, `breakout_policy`. They stay visible
(dashboard/status truthfulness) but are no longer hand-edited — the profile is
the dial.

Additive schema change; existing runs land on **Balanced**. Ships with
`gc build` locally and `gc deploy -y` to prod.

## 2. ProfilePolicy — single source of numbers

New class `App\Domains\Bot\ProfilePolicy` (`.admin/src/App/Domains/Bot/`).
All ratios are of the run's **current** `budget_quote` slice:

| Profile    | daily_loss | unrealized stop | breakout    | realizing losses        |
|------------|-----------:|----------------:|-------------|-------------------------|
| NoLoss     | 2%         | disabled (null) | HaltAndHold | **forbidden everywhere**|
| Cautious   | 3%         | 10%             | HaltAndHold | via stops only          |
| Balanced   | 6%         | 15%             | HaltAndHold | via stops only          |
| Aggressive | 10%        | 25%             | Flatten OK  | yes                     |
| Max        | 15%        | 35%             | Flatten OK  | yes                     |

Uniform for all profiles: `max_position_quote = slice`,
`max_order_quote = 0.30 × slice`.

API sketch:

```php
ProfilePolicy::caps(string $profile, string $sliceQuote): array  // column => value
ProfilePolicy::stamp(GridRun $run): bool          // recompute + save if drifted
ProfilePolicy::allowsRealizedLoss(string $profile): bool  // false only for NoLoss
```

**Stamping:** the daemon calls `stamp()` once per tick (cheap: compares before
saving) and on Reload. Slice reallocations by the routine therefore re-derive
the caps within a tick. The GUI save path (GridRunServiceWrapper) also stamps
on save so a profile change from the dashboard takes effect immediately, not a
tick later. NoLoss's 2% daily-loss cap exists only to catch fee/slippage dust
going negative; nothing in a NoLoss run may realize a loss on purpose.

## 3. NoLoss enforcement — three layers (BudgetGuard convention)

1. **GtbotKillTool** refuses `flatten:true` when the target run's profile is
   NoLoss (error names the profile; plain kill — cancel buys, hold inventory —
   stays allowed).
2. **Daemon Flatten command handler** refuses a Flatten bot_command for a
   NoLoss run (defense in depth for any other enqueue path, incl. the GUI).
3. **RiskManager breakout path** cannot escalate to Flatten because the
   stamped `breakout_policy` is HaltAndHold; `ProfilePolicy::stamp()` corrects
   any drifted value each tick.

Derisk behavior (cancel buys at 60% of the unrealized cap) does not fire for
NoLoss (cap is null); grid sells are structurally above cost+fees (spacing >
fee floor), and legacy exits keep their original above-cost prices — so the
only loss-realizing paths are the three above.

## 4. Routine prompt (v2026-08-10.1)

- `gtbot_status` gains a `profile` field.
- New **PROFILE POSTURE** block in the prompt (refit-routine.md, then pasted
  into the cloud trigger by Tim — the trigger is not reachable from the local
  CLI): *the profile instructs; market conditions decide within it.*
  - **NoLoss:** deploy 0–30; enter only on the full re-entry gate with
    all-timeframe alignment; never Flatten (the tool refuses anyway);
    drawdown-stop recovery = HOLD only for this run.
  - **Cautious:** deploy 0–40; standard gate.
  - **Balanced:** deploy 0–70 — today's guideline unchanged.
  - **Aggressive:** deploy 0–100; fit → 70–100; proactive trail-up in trends;
    Flatten available on breakout.
  - **Max:** deploy 0–100 *biased deployed* — default to the top of the
    evidence band unless the regime read is hostile; fastest re-entry (1h
    reclaim suffices); BottomWeighted at credible support; selloff available
    in drawdown recovery.
  - Per-run win-band evidence (gtbot_decisions summary.by_deploy_band) still
    modulates the choice within each posture.
- The slice floor and pool-utilization rules from v2026-08-09.1 are unchanged.

## 5. Testing (`php tests/...`, suite `tests/Custom/Bot/`)

- **ProfilePolicyTest:** cap values per profile at a reference slice; stamp()
  recomputes when the slice changes and is a no-op otherwise; NoLoss stamps
  null unrealized stop + HaltAndHold; allowsRealizedLoss() matrix.
- **GtbotKillTool test:** flatten refused for NoLoss (error, nothing
  enqueued), allowed for other profiles; plain kill allowed for NoLoss.
- **Daemon test extension:** a Flatten bot_command against a NoLoss run is
  acked as Failed with a bot_event, inventory untouched.

## Out of scope

- The global drawdown floor (`gtbot_max_drawdown_pct`) stays wallet-level and
  profile-independent.
- No per-profile spacing/width changes: sweep-derived geometry floors apply to
  all profiles identically.
- No migration UI: existing runs default to Balanced; changing them is a
  dropdown click.

## Rollout

1. Implement + tests green locally, `gc build`.
2. Commit; `gc deploy -y` (additive schema + code).
3. Extract prompt v2026-08-10.1 block; Tim pastes it into the cloud routine.
4. Verify: pick a profile on a run in the GUI, watch caps re-stamp within a
   tick; next routine pass cites the posture in its reason.
