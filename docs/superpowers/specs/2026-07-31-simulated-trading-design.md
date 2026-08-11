# Simulated trading switch (paper mode on mainnet data)

Date: 2026-07-31
Status: approved by Fred (brainstorming session)

## Problem

The bot currently trades against the Binance Spot testnet, whose phantom
flash-wicks are not real market behavior (a 1500-USDT testnet wick killed run 6
on 2026-07-28). We want real mainnet market data with **simulated** execution:
a per-run switch that, when ON, simulates fills locally, keeps full history
marked as simulated, and requires no API keys. Turning it OFF is the explicit,
env-double-gated act of trading real money.

## Decisions (with alternatives rejected)

- **Switch shape**: boolean `grid_run.simulated`, default ON. (Rejected: new
  `Paper` status value — no "starts ON" semantics; env flag — not per-run,
  can't mark rows.)
- **Fill model**: touch/cross at the limit price fills full qty, `fee_pct`
  charged per side. (Rejected: no-fee perfect fills — PnL not comparable to
  real; strict-cross — understates maker fills.)
- **Architecture**: extract `GatewayInterface`, add `PaperGateway`; Daemon
  untouched except the type-hint. (Rejected: `if (simulated)` branches in
  Daemon — scatters ~15 conditionals; subclassing BinanceGateway — inherits
  HTTP/signer baggage, silently wrong for new signed methods.)
- **Paper wallet seed**: quote = `budget_quote`, base = 0.
- **Prod migration**: the four existing runs (BTC 1, BNB 4, SOL 5, ETH 6) move
  to simulated-on-mainnet; testnet is retired.
- **Stats**: two disjoint stat sets per run (simulated vs real); the switch
  selects which set every stats surface shows.

## 1. Schema (`schema/main.hjson`, all additive)

- `grid_run.simulated('Simulated')`: `boolean()`, `default:1` — the switch.
- `grid_run.sim_bal_base` / `sim_bal_quote`: `decimal(18,8)`, nullable,
  daemon-managed, hidden from forms — the persisted paper wallet. Dedicated
  columns (not `bal_base`/`bal_quote`) so a sim→real→sim round trip cannot
  destroy paper-wallet state; `bal_*` remain the display stamp of whatever the
  active gateway reports.
- `bot_order.simulated`: `boolean()`, `default:0`, readonly — stamped 1 on
  rows created while the run's switch is ON.
- `trade_cycle.simulated`: `boolean()`, `default:0`, readonly — same stamping
  at cycle close.
- `bot_event` is NOT marked (diagnostics, not trade history).
- Existing rows keep 0 (their history was testnet/live-real).

## 2. GatewayInterface + PaperGateway

Extract `App\Domains\Bot\Gateway\GatewayInterface` with the 8 methods the
Daemon uses: `tickerPrice`, `exchangeInfo`, `accountBalances`, `openOrders`,
`orderStatus`, `placeLimitOrder`, `cancelOrder`, `myTrades`. `BinanceGateway`
implements it unchanged; `Daemon` type-hints the interface.

`PaperGateway implements GatewayInterface`:

- **Public data**: `tickerPrice`/`exchangeInfo` proxy to an injected keyless
  `BinanceGateway('https://api.binance.com')` — real mainnet prices.
- **Fill engine**: every `tickerPrice` call advances the book with the same
  touch/cross rule and fill ordering as the backtest `SimulatedGateway`
  (buy fills at price ≤ limit, sell at price ≥ limit; buys highest-first,
  sells lowest-first), full qty at the limit price.
- **Fees**: `fee_pct` per side, charged in the **quote asset**
  (`commissionAsset` = quote) — matches the Daemon's own fee estimate and
  keeps wallet math single-currency.
- **Paper wallet**: buy fill → quote −(notional + fee), base +qty; sell fill →
  base −qty, quote +(notional − fee). Persisted to
  `grid_run.sim_bal_base`/`sim_bal_quote` on every fill. Seeded
  quote=`budget_quote`, base=0 when null.
- **Binance-shaped answers** (so the Daemon's disappearance-based fill
  detection works untouched): filled orders drop out of `openOrders`;
  `orderStatus` returns `status: FILLED` + `executedQty`/`price`/`origQty`/
  `orderId`; `myTrades` returns commission entries
  (`commission`/`commissionAsset`/`price`); `accountBalances` returns
  `[asset => ['free' => …, 'locked' => …]]` from the paper wallet.
- **Restart survival**: at boot the book hydrates from the run's
  `BUY_OPEN`/`SELL_OPEN` `bot_order` rows **where `simulated = 1`**, wallet
  from `sim_bal_*`. A crash between an in-memory fill and the DB record
  re-fills deterministically on the next tick (price already crossed) — same
  recovery class the real flow gets from `orderStatus`.

## 3. `bin/gtbot` wiring and safety guards

- `simulated = 1` (any startable status): construct `PaperGateway`; no API
  keys required; `GTBOT_USE_TESTNET`/`GTBOT_DRY_RUN` are ignored. Daemon runs
  with `dryRun = false` so orders enter the paper book — except status
  `DryRun`, which keeps `dryRun = true` (its "compute only, place nothing"
  meaning is unchanged).
- `simulated = 0`: the existing guard matrix applies unchanged — status Live
  requires both env flags explicitly flipped and real keys. Flipping the
  switch OFF **is** the deliberate act of going real, and stays double-gated.
- `OrderStore` (order rows) and cycle close (trade_cycle rows) stamp
  `simulated` from the run flag.
- **Mode-filtered hydration (safety-critical)**: boot hydration and reconcile
  only see `bot_order` rows whose `simulated` matches the run's current mode —
  otherwise a flip to real would try to adopt paper opens onto the real
  exchange. On boot, open rows from the *other* mode are marked `Canceled`
  with a `mode_switch` bot_event, so a flip leaves no zombie opens.

## 4. Stats separation

A shared scope helper (`Bot\ModeScope`) returns `TradeCycleQuery` /
`BotOrderQuery` pre-filtered by `id_grid_run` AND `simulated = run.simulated`.
All stats consumers go through it:

- `DashboardData` (PnL, cycles, fees, per-level stats, chart series)
- MCP `gtbot_status`; MCP `gtbot_pnl_report` gains an optional
  `mode: sim|real|all` argument defaulting to the run's current mode
- `DecisionScorer` and the daily-loss-limit computation — a run flipped to
  real starts its loss budget clean; paper losses never halt real trading and
  vice versa
- `OrderStore` ledger / realized PnL (composes with the existing
  `ledger_reset_at` epoch filter)

Flipping the switch therefore flips every stat surface at once: one run, two
disjoint stat sets. Nothing is deleted.

## 5. Admin UI

- Grid Run form: "Simulated" toggle, default ON.
- `trade_cycle` summary cards split per mode via static card filters:
  "Realized PnL (sim)"/"(real)", "Fees (sim)"/"(real)", "Cycles (sim)"/
  "(real)".
- "Simulated" added to search filters on the order and cycle child lists;
  `simulated` visible as a column.
- Known limitation: the `add_total` footer on grid_run child lists is static
  behavior config and cannot follow the switch — it totals all history. The
  split summary cards are the authoritative per-mode numbers.

## 6. Migration of the four prod runs

After deploy: for BTC 1, BNB 4, SOL 5, ETH 6 —
set `simulated = 1`, status `Live`, seed `sim_bal_quote = budget_quote`,
`sim_bal_base = 0`, cancel remaining testnet open orders, restart daemons.
Statuses `DryRun`/`Testnet` stay in the enum as legacy; "Live" now means
"actively trading — paper or real per the switch". Existing history stays
`simulated = 0`.

## 7. Testing (TDD)

- `PaperGatewayTest`: fill mechanics (touch, ordering), fee/wallet arithmetic,
  Binance payload shapes, boot hydration from DB rows, wallet persistence.
- Daemon flow test against `PaperGateway` with a scripted price tape
  (mirroring `DaemonLiveFlowTest`): orders/cycles land `simulated = 1`,
  wallet persists, mode-filtered hydration cancels other-mode opens.
- Stats scope test: sim and real cycles on one run; each surface reports only
  the current mode's set.
