<?php

namespace Tests\Custom\Bot;

use App\Domains\Dashboard\TradeChart;
use PHPUnit\Framework\TestCase;

class TradeChartTest extends TestCase
{
    private function points(): array
    {
        return [
            ['price' => '60000', 'side' => 'Buy', 'state' => 'Filled'],
            ['price' => '61000', 'side' => 'Sell', 'state' => 'Filled'],
            ['price' => '60500', 'side' => 'Buy', 'state' => 'BUY_OPEN'],
        ];
    }

    public function testRendersInlineSvgWithNoExternalDeps(): void
    {
        $svg = TradeChart::svg($this->points(), ['pLow' => '59000', 'pHigh' => '62000']);
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringNotContainsString('http://', $svg);
        $this->assertStringNotContainsString('<script', $svg);
        // one marker per point
        $this->assertSame(3, substr_count($svg, '<circle'));
    }

    public function testBuyAndSellUseDistinctColors(): void
    {
        $svg = TradeChart::svg($this->points(), ['pLow' => '59000', 'pHigh' => '62000']);
        // buy = mint, sell = red (class-based so both must appear)
        $this->assertStringContainsString('tc-buy', $svg);
        $this->assertStringContainsString('tc-sell', $svg);
    }

    public function testRangeBandRendered(): void
    {
        $svg = TradeChart::svg($this->points(), ['pLow' => '59000', 'pHigh' => '62000']);
        $this->assertStringContainsString('tc-band', $svg);
    }

    public function testEachPointHasHoverTitle(): void
    {
        $svg = TradeChart::svg($this->points(), ['pLow' => '59000', 'pHigh' => '62000']);
        // native SVG <title> tooltip per marker (no JS needed)
        $this->assertSame(3, substr_count($svg, '<title>'));
        $this->assertStringContainsString('Buy', $svg);
        $this->assertStringContainsString('Filled', $svg);
    }

    public function testTitleTextIsEscaped(): void
    {
        $svg = TradeChart::svg(
            [['price' => '60000', 'side' => 'Buy', 'state' => '<x>', 'level' => 3]],
            ['pLow' => '59000', 'pHigh' => '62000']
        );
        $this->assertStringNotContainsString('<x>', $svg);
        $this->assertStringContainsString('&lt;x&gt;', $svg);
        $this->assertStringContainsString('L03', $svg); // level surfaced in the tooltip
    }

    public function testLegendRendered(): void
    {
        $svg = TradeChart::svg($this->points(), ['pLow' => '59000', 'pHigh' => '62000']);
        $this->assertStringContainsString('tc-legend', $svg);
        $this->assertStringContainsString('Buy', $svg);
        $this->assertStringContainsString('Sell', $svg);
        $this->assertStringContainsString('open', $svg);
    }

    public function testEmptyPointsRendersPlaceholderNotError(): void
    {
        $svg = TradeChart::svg([], ['pLow' => '59000', 'pHigh' => '62000']);
        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('No trades', $svg);
    }

    public function testGridLevelLinesRendered(): void
    {
        $svg = TradeChart::svg($this->points(), [
            'pLow' => '59000', 'pHigh' => '62000',
            'levels' => ['59000', '60000', '61000', '62000'],
        ]);
        $this->assertSame(4, substr_count($svg, 'class="tc-lvl"'));
    }

    public function testCurrentPriceLineRendered(): void
    {
        $svg = TradeChart::svg($this->points(), ['pLow' => '59000', 'pHigh' => '62000', 'lastPrice' => '60750']);
        $this->assertStringContainsString('tc-price', $svg);
        $this->assertStringContainsString('60,750', $svg); // price label on the line
        $this->assertStringContainsString('current price', $svg); // legend entry
    }

    public function testNoPriceLineWithoutLastPrice(): void
    {
        $svg = TradeChart::svg($this->points(), ['pLow' => '59000', 'pHigh' => '62000']);
        $this->assertStringNotContainsString('tc-price', $svg);
    }

    public function testPriceLineRenderedOnEmptyChart(): void
    {
        $svg = TradeChart::svg([], ['pLow' => '59000', 'pHigh' => '62000', 'lastPrice' => '60750']);
        $this->assertStringContainsString('tc-price', $svg);
    }

    public function testPriceLineOutsideRangeStaysOnChart(): void
    {
        // price above the band must widen the scale, not fall off the top
        $svg = TradeChart::svg($this->points(), ['pLow' => '59000', 'pHigh' => '62000', 'lastPrice' => '65000', 'height' => 160]);
        preg_match('/class="tc-price"[^>]* y1="(-?\d+(?:\.\d+)?)"/', $svg, $m);
        $this->assertNotEmpty($m, 'price line missing');
        $this->assertGreaterThanOrEqual(0, (float) $m[1]);
        $this->assertLessThanOrEqual(160, (float) $m[1]);
    }

    // ── dates on the chart ──────────────────────────────────────────────

    private function datedPoints(): array
    {
        return [
            ['price' => '60000', 'side' => 'Buy', 'state' => 'Filled', 'at' => '2026-07-29 10:00:00'],
            ['price' => '61000', 'side' => 'Sell', 'state' => 'Filled', 'at' => '2026-07-29 18:00:00'],
            ['price' => '60500', 'side' => 'Buy', 'state' => 'Filled', 'at' => '2026-07-30 09:00:00'],
            ['price' => '61500', 'side' => 'Sell', 'state' => 'BUY_OPEN', 'at' => '2026-07-31 12:00:00'],
        ];
    }

    public function testPointTooltipsIncludeTimestamp(): void
    {
        $svg = TradeChart::svg($this->datedPoints(), ['pLow' => '59000', 'pHigh' => '62000']);
        $this->assertStringContainsString('2026-07-29 10:00:00', $svg);
    }

    public function testDateLabelsAndTicksAtDayBoundaries(): void
    {
        $svg = TradeChart::svg($this->datedPoints(), ['pLow' => '59000', 'pHigh' => '62000']);
        // two rollovers (29→30, 30→31) → two boundary ticks
        $this->assertSame(2, substr_count($svg, 'class="tc-day"'));
        // first point's date anchors the left edge; each rollover is labeled
        $this->assertSame(2, substr_count($svg, '>07-30<') + substr_count($svg, '>07-31<'));
        $this->assertStringContainsString('>07-29<', $svg);
    }

    public function testNoDateMarkupWithoutTimestamps(): void
    {
        $svg = TradeChart::svg($this->points(), ['pLow' => '59000', 'pHigh' => '62000']);
        $this->assertStringNotContainsString('class="tc-day"', $svg);
        $this->assertStringNotContainsString('class="tc-date"', $svg);
    }

    // ── lifecycle markers ───────────────────────────────────────────────

    public function testMarkersDrawnBetweenPointsByTimestamp(): void
    {
        $svg = TradeChart::svg($this->datedPoints(), [
            'pLow' => '59000', 'pHigh' => '62000',
            'markers' => [
                ['kind' => 'algo_update', 'message' => 'new code', 'at' => '2026-07-30 00:00:00'],
                ['kind' => 'bot_stop', 'message' => 'paused', 'at' => '2026-07-30 12:00:00'],
            ],
        ]);
        $this->assertSame(2, substr_count($svg, 'class="tc-mark'));
        $this->assertStringContainsString('class="tc-mark tc-mk-algo"', $svg);
        $this->assertStringContainsString('class="tc-mark tc-mk-stop"', $svg);
        $this->assertStringContainsString('new code', $svg);
        // legend names only the kinds actually drawn
        $this->assertStringContainsString('</i>algo update</span>', $svg);
        $this->assertStringContainsString('</i>stop</span>', $svg);
        $this->assertStringNotContainsString('</i>start</span>', $svg);
    }

    public function testMarkersOlderThanTheChartWindowAreSkipped(): void
    {
        $svg = TradeChart::svg($this->datedPoints(), [
            'pLow' => '59000', 'pHigh' => '62000',
            'markers' => [['kind' => 'bot_restart', 'message' => 'old', 'at' => '2026-07-01 00:00:00']],
        ]);
        $this->assertStringNotContainsString('class="tc-mark', $svg);
    }

    public function testMarkerMessageIsEscaped(): void
    {
        $svg = TradeChart::svg($this->datedPoints(), [
            'pLow' => '59000', 'pHigh' => '62000',
            'markers' => [['kind' => 'algo_update', 'message' => '<b>x</b>', 'at' => '2026-07-30 00:00:00']],
        ]);
        $this->assertStringNotContainsString('<b>', $svg);
        $this->assertStringContainsString('&lt;b&gt;', $svg);
    }

    public function testPricesAreEscapedAndClamped(): void
    {
        // a wild point must not blow the viewbox — y stays within [0,height]
        $svg = TradeChart::svg(
            [['price' => '999999', 'side' => 'Buy', 'state' => 'Filled']],
            ['pLow' => '59000', 'pHigh' => '62000', 'height' => 160]
        );
        $this->assertMatchesRegularExpression('/cy="-?\d+(\.\d+)?"/', $svg);
        preg_match('/cy="(-?\d+(?:\.\d+)?)"/', $svg, $m);
        $this->assertGreaterThanOrEqual(0, (float) $m[1]);
        $this->assertLessThanOrEqual(160, (float) $m[1]);
    }

    // ── pale historical price line ──────────────────────────────────────

    private function history(): array
    {
        return [
            ['at' => '2026-07-29 09:00:00', 'close' => '59800'],
            ['at' => '2026-07-29 10:00:00', 'close' => '60000'],
            ['at' => '2026-07-30 09:00:00', 'close' => '60600'],
            ['at' => '2026-07-31 12:00:00', 'close' => '61400'],
        ];
    }

    public function testHistoryPolylineRenderedWhenHistoryGiven(): void
    {
        $svg = TradeChart::svg($this->datedPoints(), [
            'pLow' => '59000', 'pHigh' => '62000', 'history' => $this->history(),
        ]);
        $this->assertStringContainsString('class="tc-history"', $svg);
        $this->assertStringContainsString('<polyline', $svg);
    }

    public function testNoHistoryPolylineWithoutHistoryOpt(): void
    {
        $svg = TradeChart::svg($this->datedPoints(), ['pLow' => '59000', 'pHigh' => '62000']);
        $this->assertStringNotContainsString('<polyline', $svg);
        $this->assertStringNotContainsString('class="tc-history"', $svg);
    }

    public function testHistoryPolylineEmittedBeforeFirstPointCircle(): void
    {
        $svg = TradeChart::svg($this->datedPoints(), [
            'pLow' => '59000', 'pHigh' => '62000', 'history' => $this->history(),
        ]);
        $historyPos = strpos($svg, 'class="tc-history"');
        $circlePos = strpos($svg, '<circle');
        $this->assertNotFalse($historyPos, 'history polyline missing');
        $this->assertNotFalse($circlePos, 'point circle missing');
        $this->assertLessThan($circlePos, $historyPos, 'history line must be emitted under (before) the points');
    }

    public function testHistoryPolylineEmittedBeforeRangeBand(): void
    {
        $svg = TradeChart::svg($this->datedPoints(), [
            'pLow' => '59000', 'pHigh' => '62000', 'history' => $this->history(),
        ]);
        $historyPos = strpos($svg, 'class="tc-history"');
        $bandPos = strpos($svg, 'class="tc-band"');
        $this->assertNotFalse($bandPos, 'range band missing');
        $this->assertLessThan($bandPos, $historyPos, 'history line must be emitted under (before) the range band');
    }

    public function testHistoryPolylineRenderedOnEmptyStateChart(): void
    {
        $svg = TradeChart::svg([], ['pLow' => '59000', 'pHigh' => '62000', 'history' => $this->history()]);
        $this->assertStringContainsString('class="tc-history"', $svg);
        $this->assertStringContainsString('No trades', $svg);
        // the line is drawn behind the caption
        $this->assertLessThan(strpos($svg, 'No trades'), strpos($svg, 'class="tc-history"'));
    }

    public function testHistoryClosesJoinTheEnvelopeSoLineNeverClips(): void
    {
        // history closes far outside the trade-point/range envelope must
        // widen the y-scale rather than clip off-canvas
        $svg = TradeChart::svg($this->datedPoints(), [
            'pLow' => '59000', 'pHigh' => '62000', 'height' => 160,
            'history' => [
                ['at' => '2026-07-29 09:00:00', 'close' => '50000'],
                ['at' => '2026-07-31 12:00:00', 'close' => '70000'],
            ],
        ]);
        preg_match('/<polyline class="tc-history"[^>]*points="([^"]+)"/', $svg, $m);
        $this->assertNotEmpty($m, 'history polyline points missing');
        foreach (explode(' ', $m[1]) as $pair) {
            [, $y] = explode(',', $pair);
            $this->assertGreaterThanOrEqual(0, (float) $y);
            $this->assertLessThanOrEqual(160, (float) $y);
        }
    }

    // ── range band + level lines in the empty state ─────────────────────

    public function testEmptyStateRangeBandAndLevelsRendered(): void
    {
        $svg = TradeChart::svg([], [
            'pLow' => '59000', 'pHigh' => '62000',
            'levels' => ['59000', '60000', '61000', '62000'],
        ]);
        $this->assertStringContainsString('class="tc-band"', $svg);
        $this->assertSame(4, substr_count($svg, 'class="tc-lvl"'));
        // high/low labels still print, same as the with-points branch
        $this->assertStringContainsString('62,000', $svg);
        $this->assertStringContainsString('59,000', $svg);
        $this->assertStringContainsString('No trades', $svg);
    }

    public function testEmptyStateNoBandOrLevelsWithoutRangeOrLevelsOpt(): void
    {
        $svg = TradeChart::svg([], []);
        $this->assertStringNotContainsString('class="tc-band"', $svg);
        $this->assertStringNotContainsString('class="tc-lvl"', $svg);
    }

    public function testEmptyStateOrderingHistoryThenLevelsThenBandThenCaption(): void
    {
        $svg = TradeChart::svg([], [
            'pLow' => '59000', 'pHigh' => '62000',
            'levels' => ['60000'],
            'history' => $this->history(),
        ]);
        $historyPos = strpos($svg, 'class="tc-history"');
        $lvlPos = strpos($svg, 'class="tc-lvl"');
        $bandPos = strpos($svg, 'class="tc-band"');
        $captionPos = strpos($svg, 'No trades');
        $this->assertNotFalse($historyPos, 'history polyline missing');
        $this->assertNotFalse($lvlPos, 'level line missing');
        $this->assertNotFalse($bandPos, 'range band missing');
        $this->assertNotFalse($captionPos, 'caption missing');
        $this->assertLessThan($lvlPos, $historyPos, 'history must be under (before) the level lines');
        $this->assertLessThan($bandPos, $lvlPos, 'level lines must be under (before) the range band');
        $this->assertLessThan($captionPos, $bandPos, 'range band must be under (before) the caption');
    }

    public function testEmptyStateOrderingWithCurrentPriceLineHistoryBandPriceThenCaption(): void
    {
        // with lastPrice present, the price line is non-empty and must sit
        // ON TOP of the band but still UNDER the caption (the caption is
        // the topmost element of the empty state, same as the with-points
        // branch keeps points/markers on top of the band).
        $svg = TradeChart::svg([], [
            'pLow' => '59000', 'pHigh' => '62000',
            'levels' => ['60000'],
            'history' => $this->history(),
            'lastPrice' => '60750',
        ]);
        $historyPos = strpos($svg, 'class="tc-history"');
        $bandPos = strpos($svg, 'class="tc-band"');
        $priceLinePos = strpos($svg, 'class="tc-price"');
        $captionPos = strpos($svg, 'No trades');
        $this->assertNotFalse($historyPos, 'history polyline missing');
        $this->assertNotFalse($bandPos, 'range band missing');
        $this->assertNotFalse($priceLinePos, 'current-price line missing');
        $this->assertNotFalse($captionPos, 'caption missing');
        $this->assertLessThan($bandPos, $historyPos, 'history must be under (before) the range band');
        $this->assertLessThan($priceLinePos, $bandPos, 'range band must be under (before) the current-price line');
        $this->assertLessThan($captionPos, $priceLinePos, 'current-price line must be under (before) the caption');
    }

    public function testWithPointsOutputByteUnchangedByEmptyStateBandFactoring(): void
    {
        // regression guard for the private-helper factoring done for the
        // empty-state band/levels: the with-points branch must render byte
        // for byte identical to before the refactor.
        $svg = TradeChart::svg($this->datedPoints(), [
            'pLow' => '59000', 'pHigh' => '62000',
            'levels' => ['59000', '60000', '61000', '62000'],
            'history' => $this->history(),
        ]);
        $this->assertSame($this->withPointsSnapshot(), $svg);
    }

    private function withPointsSnapshot(): string
    {
        return '<style>' . "\n"
            . ' .tc-wrap{width:100%;overflow-x:auto}' . "\n"
            . ' .tc-band{fill:rgba(0,209,178,.10)}' . "\n"
            . ' .tc-grid{stroke:#e3e8ee;stroke-width:1}' . "\n"
            . ' line.tc-lvl{stroke:#e3e8ee;stroke-width:1;stroke-dasharray:2 3}' . "\n"
            . ' .tc-buy{fill:#00b894}' . "\n"
            . ' .tc-sell{fill:#e74c3c}' . "\n"
            . ' .tc-open{fill-opacity:.35;stroke-width:1.5}' . "\n"
            . ' .tc-buy.tc-open{stroke:#00b894}' . "\n"
            . ' .tc-sell.tc-open{stroke:#e74c3c}' . "\n"
            . ' .tc-lbl{fill:#8898aa;font-size:10px}' . "\n"
            . ' .tc-date{fill:#8898aa;font-size:9px}' . "\n"
            . ' line.tc-day{stroke:#cfd6de;stroke-width:1;stroke-dasharray:1 3}' . "\n"
            . ' line.tc-mark{stroke-width:1.5;stroke-dasharray:3 3;cursor:pointer}' . "\n"
            . ' .tc-mk-algo{stroke:#8e44ad}' . "\n"
            . ' .tc-mk-start{stroke:#00b894}' . "\n"
            . ' .tc-mk-stop{stroke:#e67e22}' . "\n"
            . ' .tc-mk-restart{stroke:#3498db}' . "\n"
            . ' .tc-mk-switch{stroke:#f39c12}' . "\n"
            . ' .tc-mk-cutover{stroke:#34495e}' . "\n"
            . ' .tc-mk-missing{stroke:#c0392b}' . "\n"
            . ' .tc-mk-stranded{stroke:#7b241c}' . "\n"
            . ' .tc-legend i.l{width:14px;height:0;display:inline-block}' . "\n"
            . ' .tc-legend i.l.tc-mk-algo{border-top:2px dashed #8e44ad}' . "\n"
            . ' .tc-legend i.l.tc-mk-start{border-top:2px dashed #00b894}' . "\n"
            . ' .tc-legend i.l.tc-mk-stop{border-top:2px dashed #e67e22}' . "\n"
            . ' .tc-legend i.l.tc-mk-restart{border-top:2px dashed #3498db}' . "\n"
            . ' .tc-legend i.l.tc-mk-switch{border-top:2px dashed #f39c12}' . "\n"
            . ' .tc-legend i.l.tc-mk-cutover{border-top:2px dashed #34495e}' . "\n"
            . ' .tc-legend i.l.tc-mk-missing{border-top:2px dashed #c0392b}' . "\n"
            . ' .tc-legend i.l.tc-mk-stranded{border-top:2px dashed #7b241c}' . "\n"
            . ' circle.tc-buy,circle.tc-sell{cursor:pointer}' . "\n"
            . ' .tc-legend{display:flex;gap:14px;margin-top:6px;font-size:11px;color:#6b7280}' . "\n"
            . ' .tc-legend span{display:inline-flex;align-items:center;gap:5px}' . "\n"
            . ' .tc-legend i.d{width:9px;height:9px;border-radius:50%;display:inline-block}' . "\n"
            . ' .tc-legend i.tc-buy{background:#00b894}' . "\n"
            . ' .tc-legend i.tc-sell{background:#e74c3c}' . "\n"
            . ' .tc-legend i.tc-open{background:transparent;border:1.5px solid #00b894}' . "\n"
            . ' .tc-history{stroke:#b6bec9;stroke-width:1;opacity:.55;fill:none}' . "\n"
            . '</style><style>'
            . ' .tc-trend{stroke:#d63384;stroke-width:1.5;opacity:.8;fill:none}'
            . ' .tc-legend i.l.tc-trend{width:14px;height:0;border-top:2px solid #d63384;display:inline-block}'
            . '</style><div class="tc-wrap"><svg viewBox="0 0 640 180" width="100%" height="180" role="img" aria-label="Trade points">'
            . '<polyline class="tc-history" points="8,115.4 8,115.4 216,115.4 424,84.9 632,44.3 632,44.3"/>'
            . '<polyline class="tc-trend" points="8,124.6 8,124.6 216,124.6 424,120.8 632,113.5 632,113.5"/>'
            . '<line class="tc-lvl" x1="8" y1="166.2" x2="632" y2="166.2"/>'
            . '<line class="tc-lvl" x1="8" y1="115.4" x2="632" y2="115.4"/>'
            . '<line class="tc-lvl" x1="8" y1="64.6" x2="632" y2="64.6"/>'
            . '<line class="tc-lvl" x1="8" y1="13.8" x2="632" y2="13.8"/>'
            . '<rect class="tc-band" x="8" y="13.8" width="624" height="152.4"/>'
            . '<text x="632" y="11.8" text-anchor="end" class="tc-lbl">62,000</text>'
            . '<text x="632" y="176.2" text-anchor="end" class="tc-lbl">59,000</text>'
            . '<text x="8" y="179" class="tc-date">07-29</text>'
            . '<line class="tc-day" x1="320" y1="0" x2="320" y2="180"/>'
            . '<text x="322" y="179" class="tc-date">07-30</text>'
            . '<line class="tc-day" x1="528" y1="0" x2="528" y2="180"/>'
            . '<text x="530" y="179" class="tc-date">07-31</text>'
            . '<circle class="tc-buy" cx="8" cy="115.4" r="4"><title>Buy @ 60,000 — Filled — 2026-07-29 10:00:00</title></circle>'
            . '<circle class="tc-sell" cx="216" cy="64.6" r="4"><title>Sell @ 61,000 — Filled — 2026-07-29 18:00:00</title></circle>'
            . '<circle class="tc-buy" cx="424" cy="90" r="4"><title>Buy @ 60,500 — Filled — 2026-07-30 09:00:00</title></circle>'
            . '<circle class="tc-sell tc-open" cx="632" cy="39.2" r="4"><title>Sell @ 61,500 — BUY_OPEN — 2026-07-31 12:00:00</title></circle>'
            . '</svg></div><div class="tc-legend"><span><i class="d tc-buy"></i>Buy</span><span><i class="d tc-sell"></i>Sell</span><span><i class="d tc-buy tc-open"></i>open order</span><span><i class="l tc-trend"></i>trend (EMA20)</span></div>';
    }
}
