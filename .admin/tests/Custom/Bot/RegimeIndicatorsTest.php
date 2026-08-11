<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\Indicators;
use PHPUnit\Framework\TestCase;

/**
 * Regime metrics for the grid/refit decision: Kaufman Efficiency Ratio
 * (|net move| / sum of bar moves — fast trend-vs-chop, 0..1) and the
 * Choppiness Index (100·log10(ΣTR/range)/log10(period) — <38.2 trending,
 * >61.8 ranging). Both answer "is grid mode right, right now" quicker than
 * the lagging ADX.
 */
class RegimeIndicatorsTest extends TestCase
{
    /** @return array{0: float[], 1: float[], 2: float[]} highs, lows, closes */
    private function trendingSeries(int $n = 40): array
    {
        $highs = $lows = $closes = [];
        for ($i = 0; $i < $n; $i++) {
            $p = 100 + $i * 2;
            $highs[] = $p + 1.0;
            $lows[] = $p - 1.0;
            $closes[] = (float) $p;
        }
        return [$highs, $lows, $closes];
    }

    /** @return array{0: float[], 1: float[], 2: float[]} */
    private function choppySeries(int $n = 40): array
    {
        $highs = $lows = $closes = [];
        for ($i = 0; $i < $n; $i++) {
            $p = 100 + (($i % 2) ? 1.0 : -1.0);
            $highs[] = $p + 1.0;
            $lows[] = $p - 1.0;
            $closes[] = $p;
        }
        return [$highs, $lows, $closes];
    }

    // ── Efficiency Ratio ────────────────────────────────────────────────

    public function testEfficiencyRatioNearOneInMonotonicTrend(): void
    {
        [, , $c] = $this->trendingSeries();
        $this->assertGreaterThan(0.95, Indicators::efficiencyRatio($c, 20));
    }

    public function testEfficiencyRatioNearZeroInChop(): void
    {
        [, , $c] = $this->choppySeries();
        $this->assertLessThan(0.15, Indicators::efficiencyRatio($c, 20));
    }

    public function testEfficiencyRatioNullWhenTooShort(): void
    {
        $this->assertNull(Indicators::efficiencyRatio([100.0, 101.0, 102.0], 20));
    }

    public function testEfficiencyRatioNullWhenFlat(): void
    {
        $this->assertNull(Indicators::efficiencyRatio(array_fill(0, 40, 100.0), 20));
    }

    // ── Choppiness Index ────────────────────────────────────────────────

    public function testChoppinessLowInSteadyTrend(): void
    {
        [$h, $l, $c] = $this->trendingSeries();
        $this->assertLessThan(38.2, Indicators::choppiness($h, $l, $c, 14));
    }

    public function testChoppinessHighInChop(): void
    {
        [$h, $l, $c] = $this->choppySeries();
        $this->assertGreaterThan(61.8, Indicators::choppiness($h, $l, $c, 14));
    }

    public function testChoppinessNullWhenTooShort(): void
    {
        $this->assertNull(Indicators::choppiness([1.0, 2.0], [0.5, 1.5], [0.8, 1.8], 14));
    }

    public function testChoppinessNullWhenRangeIsZero(): void
    {
        $h = array_fill(0, 40, 100.0);
        $l = array_fill(0, 40, 100.0);
        $c = array_fill(0, 40, 100.0);
        $this->assertNull(Indicators::choppiness($h, $l, $c, 14));
    }

    // ── summary bundling (advisory — flows to market_summary / the routine) ─

    public function testSummaryIncludesEfficiencyRatioAndChoppiness(): void
    {
        $candles = [];
        for ($i = 0; $i < 120; $i++) {
            $p = 100 + $i;
            $candles[] = ['high' => $p + 1, 'low' => $p - 1, 'close' => $p];
        }
        $s = Indicators::summary($candles);
        $this->assertGreaterThan(0.95, $s['er20'], 'monotonic series is maximally efficient');
        $this->assertLessThan(38.2, $s['chop14'], 'steady trend reads as trending');
    }
}
