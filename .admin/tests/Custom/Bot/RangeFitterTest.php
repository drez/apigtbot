<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\GridMath;
use App\Domains\Bot\RangeFitter;
use PHPUnit\Framework\TestCase;

class RangeFitterTest extends TestCase
{
    /**
     * 40 candles of a slow, wide oscillation (±15 around 100) with small
     * per-candle ranges — envelope width ≫ ATR, like a real ranging market.
     * The old fixture (per-candle range 60) fails the 4×ATR width floor by
     * construction: an envelope narrower than the bars that made it is
     * exactly what the floor exists to reject.
     */
    private function candles(): array
    {
        $out = [];
        for ($i = 0; $i < 40; $i++) {
            $close = 100 + 15 * sin($i * 0.15);
            $out[] = [
                'low' => sprintf('%.2F', $close - 0.6),
                'high' => sprintf('%.2F', $close + 0.6),
                'close' => sprintf('%.2F', $close),
            ];
        }
        return $out;
    }

    public function testProducesThreeOrderedCandidates(): void
    {
        $c = RangeFitter::candidates($this->candles(), '0.001', '1000');
        $this->assertCount(3, $c);
        $this->assertSame(['wide', 'medium', 'tight'], array_column($c, 'name'));
        // wide envelope contains medium contains tight
        $this->assertLessThan((float) $c[1]['p_low'], (float) $c[0]['p_low'] + 0.0001);
        $this->assertGreaterThan((float) $c[1]['p_high'], (float) $c[0]['p_high'] + 0.0001);
        foreach ($c as $cand) {
            $this->assertLessThan((float) $cand['p_high'], (float) $cand['p_low'], 'p_low < p_high');
        }
    }

    public function testSpacingClearsSweepFloorAndTracksOptimum(): void
    {
        $floor = (float) RangeFitter::SPACING_FLOOR;
        $target = (float) RangeFitter::SPACING_TARGET;
        foreach (RangeFitter::candidates($this->candles(), '0.001', '1000') as $cand) {
            $n = $cand['n_levels'];
            $s = (float) GridMath::spacingPct($cand['p_low'], $cand['p_high'], $n, 'Geometric');
            $this->assertGreaterThanOrEqual($floor, $s, $cand['name'] . ' spacing under the 1.3% sweep floor');
            // n is the floor-respecting count closest to the ~1.7% optimum:
            // no neighbour that still clears the floor sits closer to target
            $wider = (float) GridMath::spacingPct($cand['p_low'], $cand['p_high'], max(2, $n - 1), 'Geometric');
            $this->assertLessThanOrEqual(abs($wider - $target) + 1e-12, abs($s - $target), $cand['name'] . ': n-1 is closer to the optimum');
            $tighter = (float) GridMath::spacingPct($cand['p_low'], $cand['p_high'], $n + 1, 'Geometric');
            if ($tighter >= $floor) {
                $this->assertLessThanOrEqual(abs($tighter - $target) + 1e-12, abs($s - $target), $cand['name'] . ': n+1 is closer to the optimum');
            }
        }
    }

    public function testHighFeeRaisesFloorAboveSweepFloor(): void
    {
        // fee 0.4% → 6×fee = 2.4% floor beats the 1.3% sweep floor
        foreach (RangeFitter::candidates($this->candles(), '0.004', '1000') as $cand) {
            $s = (float) GridMath::spacingPct($cand['p_low'], $cand['p_high'], $cand['n_levels'], 'Geometric');
            $this->assertGreaterThanOrEqual(0.024, $s, $cand['name'] . ' spacing under 6×fee');
        }
    }

    public function testEnvelopeNarrowerThanAtrFloorIsDropped(): void
    {
        // violent bars (range 20) around a flat close: every envelope is far
        // narrower than 4×ATR — nothing here survives one adverse leg
        $whip = array_fill(0, 40, ['low' => '90', 'high' => '110', 'close' => '100']);
        $this->assertSame([], RangeFitter::candidates($whip, '0.001', '1000'));
    }

    public function testFlatMarketYieldsNoDegenerateCandidates(): void
    {
        $flat = array_fill(0, 20, ['low' => '100', 'high' => '100', 'close' => '100']);
        $this->assertSame([], RangeFitter::candidates($flat, '0.001', '1000'));
    }

    public function testRejectsTooFewCandles(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        RangeFitter::candidates([['low' => '1', 'high' => '2', 'close' => '1']], '0.001', '1000');
    }
}
