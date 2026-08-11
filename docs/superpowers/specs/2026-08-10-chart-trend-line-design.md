# Dashboard chart trend line (EMA20)

**Date:** 2026-08-10 · **Status:** approved design, pending implementation

## Goal

A trend line on every per-run dashboard chart: an EMA20 of the collected
price history, drawn as a smooth curve so the chart shows the same trend
family the refit routine reasons with.

## Decisions (brainstormed)

- **Trend type:** EMA smoothing (not linear regression). Period 20 over the
  10-minute collector candles the chart already receives (~3.3h smoothing).
- **Where computed:** in `TradeChart` (PHP, dependency-free, pure) — the
  class's established ethos. No JS, no schema, no new data plumbing.

## Design

`App\Domains\Dashboard\TradeChart`:

1. New private helper `emaSeries(array $history, int $period = 20): array` —
   takes the `$opts['history']` array (`[{at, close}]`, oldest→newest),
   returns the SAME shape with each `close` replaced by the running EMA.
   Standard EMA: seed = first close, `k = 2/(period+1)`,
   `ema = close×k + prev×(1−k)`. Float math (display-only — bcmath not
   required by the chart's existing code, which casts to float throughout).
2. Rendering reuses the existing polyline builders with the transformed
   series: `historyPolylineForPoints(emaSeries(...), ...)` in the
   with-points branch, `historyPolylineTimeScaled(emaSeries(...), ...)` in
   the empty branch — zero new geometry code; both branches get the line.
   The builders take a CSS class — if they hard-code `tc-history`, add an
   optional `$class = 'tc-history'` parameter rather than duplicating them.
3. Draw order: immediately after the pale history line (above it, under
   levels/band/points/markers).
4. Style: `.tc-trend{stroke:#d63384;stroke-width:1.5;opacity:.8;fill:none}`
   in the existing `<style>` block; magenta is unused elsewhere in the
   chart. Legend entry `trend (EMA20)` using the existing `i.l` line-swatch
   idiom (`border-top:2px solid #d63384`).
5. Skip entirely when `count($history) < 2` (nothing to draw); EMA values
   lie inside the close envelope, so the Y-scale code is untouched.

## Testing

New `TradeChartTrendTest` beside the existing dashboard tests:
- `emaSeries` math on a known series (e.g. closes 10,20,30 @ period 3 →
  10, 15, 22.5) and shape preservation (`at` keys untouched).
- Rendered SVG contains a `tc-trend` polyline + legend entry when history
  present — in BOTH branches (with points, without points).
- No `tc-trend` polyline/legend when history has 0 or 1 candles.

## Out of scope

- No period configurability, no per-run settings, no regression line.
- No change to the collector, DashboardData, or the routine.

## Rollout

Implement + tests green, commit, `gc build`, `./gc deploy` (render-only
change, no schema).
