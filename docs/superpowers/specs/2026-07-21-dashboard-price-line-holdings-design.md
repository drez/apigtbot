# Dashboard: current-price line + holdings tiles

Date: 2026-07-21 · Status: approved

## Goal

1. Draw the current market price as a line on the dashboard trade chart.
2. Show holdings in USDT and BTC — both real wallet balances and the bot's
   own accounting view ("Both" chosen by Fred).

## 1. Current price line (TradeChart)

- Data source: `grid_run.last_price`, already stamped by the daemon every tick.
  No new data, no exchange call from the dashboard.
- `DashboardData::runHeader()` adds `last_price`.
- `DashboardRenderer` passes it to `TradeChart::svg()` as a new `lastPrice` opt.
- `TradeChart`:
  - includes `lastPrice` in the Y-scale envelope so the line is always on-chart;
  - draws a horizontal dashed line (`.tc-price`, blue `#1a56db`) across the
    plot at that price, with a small right-aligned price label;
  - adds a "current price" legend entry;
  - renders in both the has-points and no-points branches; skipped entirely
    when no price is stamped yet (empty string / absent opt).

## 2. Holdings tiles

### Wallet balances (account truth)

- Schema (`schema/main.hjson`, `grid_run`): two nullable `decimal(18,8)`
  columns, `bal_base('Wallet base')` and `bal_quote('Wallet quote')` —
  free + locked totals. Applied through the normal `gc build` flow.
- Daemon `tick()`: next to the `last_price` stamp, call the existing
  `BinanceGateway::accountBalances()` (skipped in dry-run — signed endpoint),
  resolve the symbol's base/quote assets (same suffix parsing `exactFee()`
  uses, extracted into a shared helper), stamp both columns. Balance fetch
  failures are non-fatal (keep last stamped values).
- Dashboard stays exchange-free: `kpis()` exposes `bal_base` / `bal_quote`
  read from the run row.

### Bot accounting

- `kpis()` adds `quote_uncommitted` = budget − invested − quote locked in
  open buy orders (sum of `price × qty` over BUY_OPEN rows). Bot-held BTC is
  the existing Inventory tile.

### Renderer

- New tiles: **Wallet <base>**, **Wallet <quote>** (hidden while null — e.g.
  dry-run or pre-migration) and **Budget free** (`quote_uncommitted`).
  Asset names derived from the symbol, not hardcoded.

## Testing

- `TradeChartTest`: line present with `lastPrice`, absent without; label text.
- `DashboardDataTest`: new kpi fields, `quote_uncommitted` arithmetic.
- `DashboardRendererTest`: wallet tiles render when set, hidden when null;
  budget-free tile.

## Amendment (same day)

Balance stamping is throttled (Fred's request): refresh on the first live
tick, after any processed fill (incl. partial), or every 15 min
(`Daemon::BAL_STAMP_INTERVAL = 900`) — wallet totals only move on fills, so
quiet 5-second ticks don't re-hit the signed `/account` endpoint.

## Out of scope

- Historic balance tracking / charting.
