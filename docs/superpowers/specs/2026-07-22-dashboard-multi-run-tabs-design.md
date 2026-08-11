# Dashboard multi-run tabs — design

Date: 2026-07-22 · Status: approved by Fred

## Problem

The admin dashboard (`.admin/src/App/Domains/Dashboard/`) resolves a single
"active run" (latest Live/Testnet/DryRun). With two simultaneous runs on prod
(BTC #1, BNB #4) the older run becomes invisible: the newest run always wins.

## Decision

Tabbed layout, one run rendered at a time, selected via `?run=N`.
(Stacked-with-overview and fleet-overview-with-drilldown were considered;
Fred chose tabs.)

## Design

**DashboardData** (per-run class, unchanged internals):
- New static `activeRuns(): GridRun[]` — all Live/Testnet/DryRun runs ordered
  by `id_grid_run` ASC. Powers the tab bar.
- Selection: `?run=N` validated against `activeRuns()`; invalid/absent → first
  active run; no active runs → existing `resolveActiveRun()` fallback (latest
  non-Done, else latest), rendering exactly today's single-run page.

**DashboardRenderer**:
- `render(array $vm)` gains an optional `tabs` entry:
  `[{id, symbol, status, kill_switch, heartbeat_stale, selected, href}]`.
- Tab bar above the sections; each tab is a plain `<a>` labeled `SYMBOL #id`
  with a health dot — green (fresh heartbeat), amber (stale), red (kill switch
  on) — so a hidden run's ill health is visible without opening its tab.
- 0 or 1 active runs → no tab bar (today's markup unchanged).

**View**:
- `dashboard()` reads the `run` query param from the PSR-7 request, picks the
  run, builds `tabs` + the single-run view model.
- Untouched: 60s auto-refresh (`location.reload()` preserves the query
  string) and the restart button (delegated click, `data-run` per button).

## Testing

Extend `DashboardDataTest` / `DashboardRendererTest`:
- `activeRuns()` returns both runs in id order; excludes Draft/Halted/Done.
- Renderer emits a tab bar with correct labels, dots, and selected state;
  omits it for a single run.
- Selection: `?run=<other>` renders that run; invalid `run` falls back to the
  first active; zero-active fallback keeps current behavior.
