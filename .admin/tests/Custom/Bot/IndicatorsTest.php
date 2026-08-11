<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\Indicators;
use PHPUnit\Framework\TestCase;

class IndicatorsTest extends TestCase
{
    public function testEmaOfConstantSeriesIsThatConstant(): void
    {
        $this->assertEqualsWithDelta(100.0, Indicators::ema(array_fill(0, 50, 100.0), 20), 1e-6);
    }

    public function testEmaTracksTrendDirection(): void
    {
        $rising = range(1, 60);
        // EMA of a rising series sits below the last value but above the middle
        $ema = Indicators::ema(array_map('floatval', $rising), 20);
        $this->assertGreaterThan(30, $ema);
        $this->assertLessThan(60, $ema);
    }

    public function testRsiMonotonicUpIsHigh(): void
    {
        $this->assertGreaterThan(95, Indicators::rsi(array_map('floatval', range(1, 40)), 14));
    }

    public function testRsiMonotonicDownIsLow(): void
    {
        $this->assertLessThan(5, Indicators::rsi(array_map('floatval', range(40, 1)), 14));
    }

    public function testAtrOfConstantRangeEqualsThatRange(): void
    {
        // every candle spans exactly 10 with no gaps → ATR ≈ 10
        $highs = array_fill(0, 40, 110.0);
        $lows = array_fill(0, 40, 100.0);
        $closes = array_fill(0, 40, 105.0);
        $this->assertEqualsWithDelta(10.0, Indicators::atr($highs, $lows, $closes, 14), 0.5);
    }

    public function testTrendClassification(): void
    {
        $up = array_map('floatval', range(1, 250));
        $this->assertContains(Indicators::trend($up), ['up', 'strong_up']);
        $down = array_map('floatval', range(250, 1));
        $this->assertContains(Indicators::trend($down), ['down', 'strong_down']);
        $flat = array_fill(0, 250, 100.0);
        $this->assertSame('sideways', Indicators::trend($flat));
    }

    public function testSwingHighLow(): void
    {
        $prices = [10.0, 20.0, 5.0, 30.0, 15.0];
        $this->assertSame(30.0, Indicators::swingHigh($prices, 5));
        $this->assertSame(5.0, Indicators::swingLow($prices, 5));
    }

    public function testSummaryBundlesAllSignals(): void
    {
        $candles = [];
        for ($i = 0; $i < 220; $i++) {
            $p = 100 + $i * 0.5;
            $candles[] = ['high' => $p + 2, 'low' => $p - 2, 'close' => $p];
        }
        $s = Indicators::summary($candles);
        $this->assertArrayHasKey('price', $s);
        $this->assertArrayHasKey('ema20', $s);
        $this->assertArrayHasKey('rsi14', $s);
        $this->assertArrayHasKey('atr14', $s);
        $this->assertArrayHasKey('atr_pct', $s);
        $this->assertArrayHasKey('trend', $s);
        $this->assertArrayHasKey('swing_high', $s);
        $this->assertArrayHasKey('swing_low', $s);
        $this->assertContains($s['trend'], ['up', 'strong_up']);
    }
}
