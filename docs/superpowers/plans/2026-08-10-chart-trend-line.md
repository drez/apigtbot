# Dashboard Chart Trend Line (EMA20) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Draw an EMA20 trend line of the collected price history on every per-run dashboard chart.

**Architecture:** Spec: `docs/superpowers/specs/2026-08-10-chart-trend-line-design.md`. One pure helper in `TradeChart` computes the EMA series; rendering reuses the two existing history-polyline builders with a `$class` parameter threaded through (they currently hard-code `tc-history` in `polylineFromVerts`).

**Tech Stack:** PHP 8.4, PHPUnit (`.admin/vendor/bin/phpunit`), server-side SVG (no JS).

## Global Constraints

- Float math (display-only; matches the chart's existing casts) — NOT bcmath.
- EMA: period 20, seed = first close, `k = 2/(period+1)`.
- CSS: `.tc-trend{stroke:#d63384;stroke-width:1.5;opacity:.8;fill:none}`; legend swatch `border-top:2px solid #d63384`, label `trend (EMA20)`.
- Skip when `count($history) < 2`.
- Commits end with: `Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>`.

---

### Task 1: EMA series + trend polyline in both branches (TDD)

**Files:**
- Modify: `.admin/src/App/Domains/Dashboard/TradeChart.php`
- Test: `.admin/tests/Custom/Bot/TradeChartTrendTest.php` (new; bootstrap pattern from `TradeChartTest.php` — it is a plain PHPUnit test, `TradeChart::svg` is pure)

**Interfaces:**
- Produces: `TradeChart::svg` output containing `<polyline class="tc-trend" …>` and a `trend (EMA20)` legend span whenever `$opts['history']` has ≥2 candles. Internal: `emaSeries(array $history, int $period = 20): array` (private — test through `svg()` output; make the math test call it via reflection OR assert through rendered Y-coordinates — reflection is simpler and this codebase's tests already test private math via known-output SVG, so use reflection only for the pure math test).

- [ ] **Step 1: Write the failing tests**

```php
<?php

namespace Tests\Custom\Bot;

use App\Domains\Dashboard\TradeChart;
use PHPUnit\Framework\TestCase;

class TradeChartTrendTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
    }

    /** @return array<int, array{at:string, close:string}> */
    private function history(array $closes): array
    {
        $out = [];
        foreach ($closes as $i => $c) {
            $out[] = ['at' => sprintf('2026-08-10 %02d:00:00', $i), 'close' => (string) $c];
        }
        return $out;
    }

    public function testEmaSeriesMathAndShape(): void
    {
        $m = new \ReflectionMethod(TradeChart::class, 'emaSeries');
        $m->setAccessible(true);
        $ema = $m->invoke(null, $this->history([10, 20, 30]), 3);
        $this->assertCount(3, $ema);
        $this->assertSame('2026-08-10 00:00:00', $ema[0]['at']);   // at keys preserved
        $this->assertEqualsWithDelta(10.0, (float) $ema[0]['close'], 1e-9);
        $this->assertEqualsWithDelta(15.0, (float) $ema[1]['close'], 1e-9);  // 20*.5 + 10*.5
        $this->assertEqualsWithDelta(22.5, (float) $ema[2]['close'], 1e-9);  // 30*.5 + 15*.5
    }

    public function testTrendLineDrawnWithPoints(): void
    {
        $svg = TradeChart::svg(
            [['price' => '100', 'side' => 'Buy', 'state' => 'Filled', 'at' => '2026-08-10 01:00:00']],
            ['history' => $this->history([100, 101, 102, 103])]
        );
        $this->assertStringContainsString('class="tc-trend"', $svg);
        $this->assertStringContainsString('trend (EMA20)', $svg);
        $this->assertStringContainsString('class="tc-history"', $svg); // pale line untouched
    }

    public function testTrendLineDrawnInEmptyBranch(): void
    {
        $svg = TradeChart::svg([], ['history' => $this->history([100, 101, 102])]);
        $this->assertStringContainsString('class="tc-trend"', $svg);
    }

    public function testNoTrendLineWithoutEnoughHistory(): void
    {
        $none = TradeChart::svg([], []);
        $one = TradeChart::svg([], ['history' => $this->history([100])]);
        $this->assertStringNotContainsString('tc-trend', $none);
        $this->assertStringNotContainsString('tc-trend', $one);
        $this->assertStringNotContainsString('trend (EMA20)', $one);
    }
}
```

- [ ] **Step 2: Run to verify failure**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/TradeChartTrendTest.php`
Expected: FAIL — `emaSeries` does not exist / `tc-trend` absent.

- [ ] **Step 3: Implement**

In `TradeChart.php`:

(a) Thread a class through the polyline chain — change `polylineFromVerts` and both builders:

```php
    private static function polylineFromVerts(array $verts, string $class = 'tc-history'): string
    {
        $pts = implode(' ', array_map(static fn ($v) => $v[0] . ',' . $v[1], $verts));
        return '<polyline class="' . $class . '" points="' . $pts . '"/>';
    }
```

`historyPolylineForPoints(array $history, array $points, float $xStep, int $padX, int $w, callable $yOf, string $class = 'tc-history')` and `historyPolylineTimeScaled(array $history, int $padX, int $w, callable $yOf, string $class = 'tc-history')` — each passes `$class` to `polylineFromVerts`.

(b) The EMA helper (place near the other private helpers):

```php
    /** Same shape as $history with each close replaced by the running EMA. */
    private static function emaSeries(array $history, int $period = 20): array
    {
        $k = 2 / ($period + 1);
        $ema = null;
        $out = [];
        foreach ($history as $hpt) {
            $close = (float) $hpt['close'];
            $ema = $ema === null ? $close : $close * $k + $ema * (1 - $k);
            $out[] = ['at' => (string) $hpt['at'], 'close' => (string) $ema];
        }
        return $out;
    }
```

(c) CSS in the main `<style>` block (next to `.tc-history`):

```
 .tc-trend{stroke:#d63384;stroke-width:1.5;opacity:.8;fill:none}
 .tc-legend i.l.tc-trend{width:14px;height:0;border-top:2px solid #d63384;display:inline-block}
```

(d) Wire both branches. Compute once, near the top after `$history` is read:

```php
        $trend = count($history) >= 2 ? self::emaSeries($history) : [];
```

Empty branch — directly after the `$historyLine = ...TimeScaled(...)` line:

```php
            $trendLine = $trend ? self::historyPolylineTimeScaled($trend, $padX, $w, $yOf, 'tc-trend') : '';
```

and include `$trendLine` in the returned SVG right after `$historyLine`. Also add the legend to the empty branch ONLY if it already has a legend — it does not (it returns bare SVG); leave the empty branch legend-less, matching its existing treatment of other legend items (the test above therefore only asserts the polyline in the empty branch, not the legend — keep it that way).

With-points branch — directly after the `historyPolylineForPoints` call:

```php
        if ($trend) {
            $svg .= self::historyPolylineForPoints($trend, $points, $xStep, $padX, $w, $yOf, 'tc-trend');
        }
```

and in the legend, after the open-order span:

```php
            . ($trend ? '<span><i class="l tc-trend"></i>trend (EMA20)</span>' : '')
```

- [ ] **Step 4: Run tests**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/TradeChartTrendTest.php tests/Custom/Bot/TradeChartTest.php tests/Custom/Bot/DashboardRendererTest.php`
Expected: PASS (new + existing chart/renderer tests).

- [ ] **Step 5: Commit**

```bash
cd /path/to/apigtbot && git add .admin/src/App/Domains/Dashboard/TradeChart.php .admin/tests/Custom/Bot/TradeChartTrendTest.php && git commit -m "feat(dashboard): EMA20 trend line on the run charts"
```

---

### Task 2: Ship

**Files:** none new.

- [ ] **Step 1:** Full suite: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/` — 0 failures.
- [ ] **Step 2:** `../gc build` from `.admin`; commit any regenerated `.buildid`/`.gc-meta.json` as `chore(build): rebuild artifacts`.
- [ ] **Step 3:** `cd /path/to/apigtbot && ./gc deploy` (bare command; render-only change, no schema).
- [ ] **Step 4:** Spot-check prod: `curl -s https://your-domain.example/ >/dev/null` warms the route; visual confirmation is the user's (charts show a magenta trend line).

## Self-Review (done at write time)

- Spec coverage: helper→T1b, class threading→T1a, CSS→T1c, both branches + skip rule→T1d, tests→T1 Step 1, rollout→T2. ✓
- No placeholders; signatures consistent (`$class` default keeps existing call sites valid). ✓
- Empty-branch legend intentionally omitted (matches existing behavior; documented in Step 3d and mirrored by the tests). ✓
