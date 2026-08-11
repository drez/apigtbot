# Dashboard Global Band Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: superpowers:subagent-driven-development. Single task.

**Goal:** A global system band at the very top of the dashboard, outside/above the run tabs: Mode pill+switch, Budget, Wallet USDT, Account value, Realized PnL (global, current mode), and Total P/L (account value − shared budget).

**Architecture:** Display-only. `DashboardData` gains a static `globalBand(): array` aggregating across non-Done runs; `DashboardRenderer` renders the band before the tabs/run section; the existing mode pill + switch + wallet tile move INTO the band (out of the per-run section). No schema, daemon, or route changes.

## Global Constraints

- bcmath strings scale 12 for all money math; null when unpriceable (render "—").
- All aggregates are CURRENT-MODE scoped (ModeSwitch::systemMode(); mixed → treat as simulated for wallet source, and label values accordingly).
- Tests: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot`; suite stays green.
- Never stage todo.md, docs/proposal-two-sided-grid.md, .admin/scripts/strategy-gate.php.
- Commits end with `Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>`.

---

### Task 1: Global band

**Files:**
- Modify: `.admin/src/App/Domains/Dashboard/DashboardData.php` (add `globalBand()`; keep `sharedWallet()` as its data source)
- Modify: `.admin/src/App/Domains/Dashboard/DashboardRenderer.php` (render band above tabs; relocate mode pill/switch + wallet detail into it; remove them from the per-run section)
- Modify: `.admin/src/App/Domains/Dashboard/View.php` (only if the band needs the vm wired; the switch JS is delegated on document and needs no change)
- Test: `.admin/tests/Custom/Bot/DashboardDataTest.php` + `DashboardRendererTest.php` additions

**Band contract** (`globalBand()` returns, all strings-or-null):
- `mode`: ModeSwitch::systemMode()
- `budget`: SimWallet::sharedBudget(); `slices_sum`: bc sum of budget_quote over DryRun/Testnet/Live runs (shown as "slices X / budget")
- `wallet_usdt`: sim path = SimWallet::balances()['USDT']; real path = freshest-stamped run's bal_quote (reuse sharedWallet()'s logic)
- `account_value`: wallet USDT + each non-USDT wallet asset × matching non-Done run's last_price (skip unpriced from the sum only if ALL priced; if any asset is unpriced, account_value stays computable from the priced ones but set `value_partial: true`)
- `realized_total` / `realized_today`: bc sums over all non-Done runs of mode-scoped TradeCycle realized_pnl (filterBySimulated(run's simulated), today = date_creation >= local midnight)
- `total_pl`: account_value − budget (null when account_value is null); the tile renders green/red by sign
- `assets`: the per-asset rows (from sharedWallet()) for the band's wallet detail line

**Steps (TDD):**
- [ ] Failing tests: DashboardDataTest — seed two runs (both simulated) with one cycle each (pnl 10 and 20) + sim_wallet rows (USDT 900, BTC 0.001 with a run last_price) → assert realized_total 30, wallet_usdt 900, account_value = 900 + 0.001×price, total_pl = account_value − sharedBudget(). DashboardRendererTest — band HTML appears BEFORE the tabs markup, contains the pill, switch button, "Budget", "Total P/L" labels; per-run section no longer contains the pill/switch/wallet tile.
- [ ] RED → implement → GREEN; full suite green.
- [ ] Commit: `feat(dashboard): global band — budget, wallet, account value, realized + total P/L above the runs`

---

### Task 2: Committed-capital row in the holdings strip (follow-up)

**Files:** Modify DashboardData.php (`globalBand()` gains fields), DashboardRenderer.php (extend the dash-holdings strip), tests.

**Contract additions to globalBand()** (strings-or-null, bcmath scale 12, all across NON-DONE runs, each run mode-scoped via filterBySimulated((bool)$run->getSimulated())):
- `holdings_value`: Σ non-USDT wallet assets × matching run last_price (null if none priced; partial ok — sum the priced ones, reuse account_value's asset pricing).
- `open_sell_value`: Σ over BotOrder rows state IN (SELL_OPEN, PartFilled) side Sell of price × (qty − COALESCE(filled_qty,0)).
- `open_buy_value`: same for state IN (BUY_OPEN, PartFilled) side Buy.
- `free_budget`: sharedBudget − Σ per-run invested cost basis (reuse OrderStore(runId, runUid, ledger_reset_at, simulated)->investedQuote()) − open_buy_value. May go negative — render red then.

**Renderer:** append to the dash-holdings strip line (same styling): ` | Holdings ≈X · Open sells X · Open buys X · Free budget X` with "—" for nulls, red tone on negative free_budget.

**Tests (TDD):** DashboardDataTest — seed a run with an open buy (95×1, no fill) + an open sell (105×0.5) + a Filled buy (cost 50) → assert the four fields with exact bc values (ambient-safe: measure ambient values first, assert deltas). RendererTest — labels + a red class when free_budget negative.

---

### Task 3: Pale historical price line in the run charts (follow-up)

**Files:** Modify TradeChart.php (opts['history'] polyline), DashboardData.php (priceHistory() feed + vm wiring), DashboardRenderer.php (pass history opt), tests (TradeChartTest + DashboardDataTest).

**Data feed** — `DashboardData::priceHistory(): array` of `['at' => 'Y-m-d H:i:s', 'close' => string]`, oldest→newest, for the run's symbol:
- Source `MarketStore::candles(symbol, tf)` trying tfs in order '15m','1h','4h','1d' — first non-empty wins. NOTE: candles carry NO timestamps; reconstruct: the matching market_summary row's `computed_at` is the newest candle's time; candle i (0-based, oldest first, N total) gets `computed_at − (N−1−i) × tfSeconds` (tfSeconds: 15m=900, 1h=3600, 4h=14400, 1d=86400). Fetch computed_at via MarketSummaryQuery (same symbol+tf).
- Empty when no market_summary rows (local dev) — chart falls back to current behavior.

**TradeChart** — new `opts['history']` (same shape). Rendering:
- With points: for each point index i with time at_i, y = close of the candle whose reconstructed time is the latest ≤ at_i (nearest fallback at edges); x = the point's existing x. Straight polyline through those (x,y). Prepend/append edge segments: chart-left uses the candle at/before the first point, chart-right the newest candle.
- Without points (empty state): time-scale the full history across the width, y-scale = price envelope of range ∪ closes; keep the "No trades yet" caption but draw the line behind it.
- History closes join the y-envelope computation (so the line never clips).
- Style: `<polyline class="tc-history" ...>` stroke #b6bec9, stroke-width 1, opacity 0.55, fill none; emitted BEFORE (i.e. under) the range band border, points and markers; add the class to the existing <style> block.

**Tests (TDD):** TradeChartTest — polyline present with class tc-history when history given; emitted earlier in the SVG string than the first point circle; absent when no history; empty-state chart (no points + history) contains the polyline. DashboardDataTest — seed a market_summary row (symbol matching the run, tf '1h', recent_candles JSON of 3 candles, computed_at fixed) → priceHistory() returns 3 entries with correctly reconstructed hourly timestamps and string closes (ambient-safe: use a unique symbol for the seeded run).

---

### Task 4: Range band + level lines in the chart's empty state (follow-up)

**Files:** Modify TradeChart.php (empty-state branch only), TradeChartTest.php.

**Spec:** When there are no points, the "No trades yet" branch must ALSO draw (a) the faint grid-level lines from opts['levels'] (class tc-lvl) and (b) the shaded grid-range band from opts['pLow']/opts['pHigh'] (same rect/classes as the with-points branch), so a flat run still shows where its grid is armed. Layering must match the with-points branch exactly: history polyline first (bottom), then level lines, then the range band, then the current-price line and the caption on top. No change when pLow/pHigh absent. Reuse/factor the band+levels emission shared with the main branch rather than duplicating the markup strings (small private helper), unless the existing code structure makes duplication clearly simpler — match file style.

**Tests (TDD):** empty-state chart with pLow/pHigh + levels + history → band rect present, tc-lvl lines present, ordering asserted (history before band, band before caption); no band when range absent; with-points output byte-unchanged for a fixture that previously rendered (regression: reuse an existing with-points test's expected fragments).

---

### Task 5: Position pill in the run header (follow-up)

**Files:** Modify DashboardRenderer.php (run-head pill), DashboardRendererTest.php.

**Spec:** In the dash-run-head (next to the existing status pill), render a position pill from the already-computed per-run kpis: inventory (era-scoped, string) and inventory_value (nullable string).
- bccomp(inventory, '0', 8) <= 0 → `<span class="dash-pos-pill dash-pos-flat">FLAT</span>` (muted gray, same pill styling family as the status pill).
- Otherwise → `<span class="dash-pos-pill dash-pos-long">LONG {inventory trimmed} {baseAsset} ≈ {value}</span>` (teal); omit the "≈ {value}" part when inventory_value is null. Base asset from the run symbol (SimWallet::assetsFor()[0] — the vm already carries the symbol). Trim trailing zeros on the inventory qty for display (e.g. 0.0015 not 0.00150000).
- The ≈ entity must NOT be double-escaped (same rule as the committed-capital row — escape numbers only).
- No pill when kpis are absent (no-run view).

**Tests (TDD):** renderer test: flat fixture → FLAT pill present with dash-pos-flat; holding fixture (inventory 0.00150000, value 96.35) → "LONG 0.0015 BTC" + ≈96.35 with dash-pos-long, no double-escaped entity; null value → no ≈ part; pill markup inside the run-head block (assert it appears between the run-head open tag and the daemon-control buttons).
