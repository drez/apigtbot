# Applied-geometry boot fix + dashboard value clarity

Date: 2026-07-22 · Status: approved ("implement all")

## Problem (observed on prod 2026-07-22 07:35)

The refit cron writes *requested* geometry onto `grid_run`; `maybeRefit()`
defers applying it while inventory is held. But a daemon **restart** re-reads
config from the row, so `boot()` adopts the pending geometry and hydrates old
open orders onto the new level grid by index. Result: L5 bought 0.00126 @
65,827 but the exit was placed from the new grid — Sell 0.00185 @ 67,881
(wrong qty, wrong price). The only-when-flat guard is bypassed by restarts.

## Fix: applied vs requested geometry

- New `grid_run.applied_geometry` (longvarchar, daemon-managed): JSON of the
  7 geometry keys (p_low, p_high, n_levels, spacing, allocation, budget_quote,
  fee_pct) the ladder is ACTUALLY built on. Stamped whenever the daemon
  (re)builds the ladder: first flat boot, refit apply, geometry reset.
- `boot()`: if applied exists, open orders/inventory exist, and applied ≠
  requested → boot on **applied**; the refit stays pending until flat (the
  existing `maybeRefit` flow promotes requested → applied and stamps).
  Non-geometry config (risk caps, kill switch…) always live from the row.
- `geomSig` now tracks the applied geometry.

## Boot mismatch guard (self-healing)

After hydration+reconcile, every open order's price is checked against its
ladder level (buy → levels[i], sell → levels[i+1]; 0.2 % tolerance for tick
rounding). On mismatch — covers the legacy NULL-applied case, i.e. prod right
now — the daemon **self-heals** instead of mis-trading:

- cancel ALL our open orders (both sides; long-only, so no naked exposure),
- rebuild an empty ladder on the requested geometry and stamp it applied,
- set kill_switch ON, alert `geometry_reset` naming the untracked inventory
  (held base stays in the wallet; human decides to sell or keep it),
- idle on heartbeat until a human clears the kill switch (dashboard GUI).

This makes the prod cleanup automatic on deploy; clearing the kill switch
starts a fresh grid on current geometry.

## Dashboard clarity

New kpis (DashboardData):
- `committed_buys` — quote locked in open buy orders (+count already there)
- `open_sell_value` — quote value of open sells at their ask prices
- `inventory_value` — inventory × last_price (null without a price)
- `account_value` — bal_quote + bal_base × last_price (null until stamped)

Renderer: tiles grouped under captions instead of one undifferentiated row —
**P/L** (Realized, Unrealized, Today, Fees, Cycles), **Grid** (Inventory
value w/ BTC in label, In open buys w/ count, In open sells w/ count,
Invested, Budget free), **Wallet** (BTC, USDT, Account value).

## Chart fix

The scatter looked wrong because Canceled orders render exactly like filled
trades (solid dots) — refits/restarts produced 14+ cancels that dominated the
picture. Changes:
- `tradePoints()` excludes Canceled (Vetoed already excluded): the chart now
  shows only real fills + working orders.
- Faint horizontal grid-level lines (from the run's geometry via GridMath) so
  points visibly sit on their levels, with the range band and the
  current-price line already in place.

## Rollout

Deploy (ALTER adds the column; daemon self-restarts). Expected on prod: boot
detects the legacy mismatch → cancels stale orders → kill_switch ON +
`geometry_reset` alert. Fred flips the kill switch off in the dashboard to
start clean. Untracked 0.00251 BTC stays in the testnet wallet.

## Out of scope

- Automatic re-exit placement for untracked inventory after a reset (level
  ownership is inherently ambiguous after a geometry change).
- Time-scaled X axis on the chart.
