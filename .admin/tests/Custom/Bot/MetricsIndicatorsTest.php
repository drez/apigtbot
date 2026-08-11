<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\Indicators;
use PHPUnit\Framework\TestCase;

/**
 * New signal metrics: ADX14 (regime: trend vs range), ATR% percentile rank
 * (is volatility spiking or normal vs its own history), and volume metrics
 * (taker buy ratio + volume z-score) when candles carry volume data.
 */
class MetricsIndicatorsTest extends TestCase
{
    /** @return array{0: float[], 1: float[], 2: float[]} highs, lows, closes */
    private function trendingSeries(int $n = 80): array
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
    private function choppySeries(int $n = 80): array
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

    // ── ADX ─────────────────────────────────────────────────────────────

    public function testAdxHighInSteadyTrend(): void
    {
        [$h, $l, $c] = $this->trendingSeries();
        $this->assertGreaterThan(60, Indicators::adx($h, $l, $c, 14));
    }

    public function testAdxLowInChop(): void
    {
        [$h, $l, $c] = $this->choppySeries();
        $this->assertLessThan(25, Indicators::adx($h, $l, $c, 14));
    }

    public function testAdxInsufficientDataIsZero(): void
    {
        $this->assertSame(0.0, Indicators::adx([1.0, 2.0], [0.5, 1.5], [0.8, 1.8], 14));
    }

    // ── ATR% percentile rank ────────────────────────────────────────────

    public function testAtrPctRankHighWhenVolatilityJustExploded(): void
    {
        $highs = $lows = $closes = [];
        for ($i = 0; $i < 100; $i++) { // calm: range 1
            $highs[] = 100.5;
            $lows[] = 99.5;
            $closes[] = 100.0;
        }
        for ($i = 0; $i < 20; $i++) {  // wild: range 20
            $highs[] = 110.0;
            $lows[] = 90.0;
            $closes[] = 100.0;
        }
        $this->assertGreaterThanOrEqual(90, Indicators::atrPctRank($highs, $lows, $closes, 14));
    }

    public function testAtrPctRankLowWhenVolatilityCalmedDown(): void
    {
        $highs = $lows = $closes = [];
        for ($i = 0; $i < 60; $i++) {  // wild first
            $highs[] = 110.0;
            $lows[] = 90.0;
            $closes[] = 100.0;
        }
        for ($i = 0; $i < 60; $i++) {  // calm since
            $highs[] = 100.5;
            $lows[] = 99.5;
            $closes[] = 100.0;
        }
        $this->assertLessThanOrEqual(30, Indicators::atrPctRank($highs, $lows, $closes, 14));
    }

    // ── summary bundling ────────────────────────────────────────────────

    public function testSummaryIncludesAdxAndAtrRank(): void
    {
        $candles = [];
        for ($i = 0; $i < 120; $i++) {
            $p = 100 + $i;
            $candles[] = ['high' => $p + 1, 'low' => $p - 1, 'close' => $p];
        }
        $s = Indicators::summary($candles);
        $this->assertArrayHasKey('adx14', $s);
        $this->assertArrayHasKey('atr_pct_rank', $s);
        $this->assertGreaterThan(25, $s['adx14']);
    }

    public function testSummaryVolumeMetricsWhenCandlesCarryVolume(): void
    {
        $candles = [];
        for ($i = 0; $i < 120; $i++) {
            $p = 100 + $i;
            $vol = 90.0 + ($i % 21);           // ~100 ± noise
            $candles[] = [
                'high' => $p + 1, 'low' => $p - 1, 'close' => $p,
                'volume' => (string) $vol, 'taker_buy' => (string) ($vol * 0.6),
            ];
        }
        // last candle: volume spike, same 60% taker-buy share
        $candles[119]['volume'] = '500';
        $candles[119]['taker_buy'] = '300';

        $s = Indicators::summary($candles);
        $this->assertEqualsWithDelta(0.6, $s['taker_buy_ratio'], 0.01, 'share of volume that was taker (aggressive) buying');
        $this->assertGreaterThan(3, $s['vol_zscore'], 'a 5x volume spike must read as a strong outlier');
    }

    public function testSummaryVolumeMetricsNullWithoutVolumeData(): void
    {
        $candles = [];
        for ($i = 0; $i < 60; $i++) {
            $p = 100 + $i;
            $candles[] = ['high' => $p + 1, 'low' => $p - 1, 'close' => $p];
        }
        $s = Indicators::summary($candles);
        $this->assertNull($s['taker_buy_ratio']);
        $this->assertNull($s['vol_zscore']);
    }
}
