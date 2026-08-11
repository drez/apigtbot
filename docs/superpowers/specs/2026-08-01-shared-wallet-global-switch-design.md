# Shared wallet, shared budget, global simulated/real switch

Date: 2026-08-01 (designed 2026-07-31 EDT)
Status: approved by Fred (brainstorming session)
Builds on: 2026-07-31-simulated-trading-design.md (per-run simulated switch, PaperGateway, mode-scoped stats)

## Problem

All runs trade one account in reality — one wallet, one pot of capital — but the
shipped paper mode gives each run its own 1,000 USDT wallet and budget, and the
switch is per-run (form-only). Fred wants: (1) a dashboard button to flip
simulated/real, (2) all runs sharing ONE wallet and ONE budget.

## Decisions

- **Shared budget**: 1,000 USDT total for the whole system (config value).
  (Rejected: 4,000 = today's sum — Fred chose the tighter, real-account-like
  figure.)
- **Contention model**: reserved slices — each run's `budget_quote` IS its
  slice of the pool; the hourly refit routine reallocates slices toward the
  most profitable runs. (Rejected: first-come-first-served pool draws.)
- **Switch scope**: one global dashboard switch flipping ALL runs together —
  mixed modes over one shared wallet would double-commit capital.
  (Rejected: per-run buttons.)
- **Negative pool**: Alert (Telegram), never a hard stop — the exchange
  enforces reality in real mode; paper mirrors with the alert.

## 1. Shared budget model

- Config key `gtbot_shared_budget_quote` = `1000` in the project config table,
  seeded via a base seed (custom*.sql, no `gc:dev-only` marker — must deploy).
- Per-run `budget_quote` = the run's reserved slice. Invariant:
  sum(`budget_quote`) over runs with status in (DryRun, Testnet, Live)
  ≤ shared budget.
- Daemon boot + each refit apply: if the invariant is violated, write an
  Alert-level `budget_overcommit` bot_event (fans out to Telegram) and
  continue. No blocking; the routine maintains the invariant.
- Initial slices at migration: BTC(run 1) 350, ETH(6) 350, BNB(4) 250,
  SOL(5) 50. The routine re-decides hourly.

## 2. Shared paper wallet

- New table `sim_wallet`: `asset` varchar(10) unique + `qty` decimal(18,8).
  Source of truth for the paper wallet across ALL runs. The per-run
  `grid_run.sim_bal_base/sim_bal_quote` columns stop being used by
  PaperGateway (they remain in the schema, stale-harmless, display-optional).
- Config key `gtbot_sim_wallet_epoch` (datetime string): the global paper-era
  epoch. Wallet derivation replays only rows with `date_creation` > epoch.
- **PaperGateway rework** (multi-process safety — four daemons share the
  table):
  - No long-lived in-memory wallet. `accountBalances()` reads `sim_wallet`
    fresh (USDT row + this run's base-asset row; missing rows read as the
    derived seed / 0).
  - A fill applies its delta in a DB transaction with `SELECT … FOR UPDATE`
    on the touched `sim_wallet` rows (read-modify-write, bcmath scale 12).
    Buy: USDT −= (notional + fee), base += qty. Sell: base −= qty,
    USDT += (notional − fee). Fees stay charged in quote.
  - **Seeding/derivation**: on boot, a daemon ensures `sim_wallet` is
    consistent by deriving from the global ledger: USDT = shared budget −
    replay of ALL runs' `simulated=1`, state `Filled` bot_order rows with
    `date_creation` > epoch (buys subtract notional+fee_paid and add base
    qty per asset; sells the reverse). If the derived value differs from the
    stored row, the stored row is corrected (last-writer-wins is safe: the
    derivation is deterministic and monotonic over the same ledger; a
    concurrent fill between read and write is protected by FOR UPDATE on the
    correction transaction too). This keeps the crash-idempotency property:
    a crash between an in-memory fill and the Daemon's DB record cannot
    double-count, because derivation always re-reads the ledger.
  - If USDT would go negative on a fill, apply it anyway and write an
    Alert-level `sim_wallet_negative` bot_event (invariant breach signal).
- `bal_base`/`bal_quote` stamps keep working (Daemon stampBalances reads
  accountBalances — now the shared wallet view). The dashboard gains a
  dedicated shared-wallet tile (below); per-run wallet tiles show the shared
  values, which is correct (it IS the account wallet).

## 3. Dashboard

- **Header mode control**: pill showing SIMULATED (amber) / REAL (red) — the
  system mode = the runs' common `simulated` value. If runs are mixed (only
  possible via manual form edits), show MIXED (red) and the switch still
  forces all one way.
- **Switch button** next to the pill. POST `Dashboard/mode` with target mode:
  - Updates `simulated` on every non-Done run.
  - Enqueues a `Reload` bot_command per affected run (daemons ack + exit;
    watchdog respawns them into the new mode within ~a minute).
  - Flip to REAL: JS confirm dialog spelling out the consequences ("flips
    ALL runs to real trading; requires prod API keys + env flags or daemons
    will refuse to start"). Flip to SIMULATED: plain confirm.
  - CSRF: same fetch wrapper/token as `Dashboard/command`.
- **Shared wallet tile**: one tile — USDT pool qty, per-asset holdings with
  mark-to-market value at last prices, total account value. Reads
  `sim_wallet` in simulated mode; in real mode shows the stamped
  `bal_*` values aggregated per asset (best-effort, unchanged semantics).

## 4. Routine-driven slice allocation

- `refit-routine.md` (the hourly cloud routine prompt) gains an allocation
  step: after per-pair refit analysis, reallocate `budget_quote` slices
  across runs weighted by mode-scoped realized PnL + regime, under:
  sum ≤ `gtbot_shared_budget_quote`; min slice 50 for any run with
  deploy_pct > 0; never below a run's currently invested cost
  (`gtbot_status.invested_quote`); write via `crm_update` `BudgetQuote`.
- No code change required for the routine itself (it already writes
  DeployPct via crm_update); the daemon's existing refit path picks up
  budget changes (budget is part of the applied-geometry signature).

## 5. Migration (prod, after deploy)

1. Deploy (additive schema: `sim_wallet` table + config seed).
2. Set the global epoch config to the migration time; cancel open simulated
   orders in the ledger (mark Canceled) so the new era starts flat; history
   rows remain (marked, pre-epoch).
3. Set slices: run 1 → 350, 6 → 350, 4 → 250, 5 → 50.
4. Reload all daemons; verify each `gtbot_status` heartbeat + a
   `sim_wallet` USDT row at 1000 and the dashboard tile/pill.

## 6. Testing

- Shared-wallet concurrency: two PaperGateway instances (different runs) over
  the same DB apply fills; final wallet equals the exact bc-derivation; FOR
  UPDATE path exercised.
- Global derivation: multi-run ledger + epoch respected; pre-epoch rows
  ignored; crash-window regression (open row re-fill cannot double-count).
- Invariant alerts: overcommitted slices → `budget_overcommit` Alert;
  negative pool → `sim_wallet_negative` Alert.
- Dashboard mode endpoint: flips all non-Done runs, enqueues Reload per run,
  CSRF-gated; renderer shows SIMULATED/REAL/MIXED pill correctly; wallet
  tile numbers match `sim_wallet`.
- Existing suite stays green (PaperGateway tests updated to the shared-wallet
  contract without weakening fill-math assertions).
