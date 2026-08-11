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
