<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\GridMath;
use PHPUnit\Framework\TestCase;

class GridMathTest extends TestCase
{
    public function testGeometricLevelsEndpointsExactAndMonotonic(): void
    {
        $levels = GridMath::levels('100', '200', 4, 'Geometric');
        $this->assertCount(5, $levels);
        $this->assertSame(0, bccomp($levels[0], '100', 8));
        $this->assertSame(0, bccomp($levels[4], '200', 8));
        for ($i = 1; $i < 5; $i++) {
            $this->assertSame(1, bccomp($levels[$i], $levels[$i - 1], 8), "level $i not > level " . ($i - 1));
        }
    }

    public function testGeometricLevelsHaveEqualRatios(): void
    {
        $levels = GridMath::levels('100', '200', 4, 'Geometric');
        // r = 2^(1/4) ≈ 1.18920712
        $prev = null;
        for ($i = 1; $i < 5; $i++) {
            $ratio = bcdiv($levels[$i], $levels[$i - 1], 12);
            if ($prev !== null) {
                $diff = bcsub($ratio, $prev, 12);
                $this->assertLessThan(1e-8, abs((float) $diff), "ratio drift at level $i");
            }
            $prev = $ratio;
        }
        $this->assertEqualsWithDelta(1.18920712, (float) $prev, 1e-6);
    }

    public function testArithmeticLevels(): void
    {
        $levels = GridMath::levels('100', '200', 4, 'Arithmetic');
        $this->assertSame(0, bccomp($levels[0], '100', 8));
        $this->assertSame(0, bccomp($levels[1], '125', 8));
        $this->assertSame(0, bccomp($levels[2], '150', 8));
        $this->assertSame(0, bccomp($levels[3], '175', 8));
        $this->assertSame(0, bccomp($levels[4], '200', 8));
    }

    public function testGeometricSpacingPct(): void
    {
        $pct = GridMath::spacingPct('100', '200', 4, 'Geometric');
        $this->assertEqualsWithDelta(0.18920712, (float) $pct, 1e-6);
    }

    public function testArithmeticSpacingPctIsSmallestRelativeGap(): void
    {
        // smallest relative gap of an arithmetic grid is the top one: 25/175
        $pct = GridMath::spacingPct('100', '200', 4, 'Arithmetic');
        $this->assertEqualsWithDelta(25 / 175, (float) $pct, 1e-9);
    }

    public function testEqualQuoteAllocation(): void
    {
        $buyLevels = ['100', '125', '150', '175'];
        $qtys = GridMath::allocate('1000', $buyLevels, 'EqualQuote');
        $this->assertCount(4, $qtys);
        // 250 quote per level → qty = 250 / price
        $this->assertSame(0, bccomp($qtys[0], '2.5', 8));
        $this->assertSame(0, bccomp($qtys[1], '2', 8));
        $this->assertEqualsWithDelta(250 / 150, (float) $qtys[2], 1e-8);
    }

    public function testEqualBaseAllocation(): void
    {
        $buyLevels = ['100', '125', '150', '175'];
        $qtys = GridMath::allocate('1100', $buyLevels, 'EqualBase');
        // q = 1100 / (100+125+150+175) = 2
        foreach ($qtys as $q) {
            $this->assertSame(0, bccomp($q, '2', 8));
        }
    }

    public function testProfitPerGridPct(): void
    {
        $this->assertEqualsWithDelta(0.001, (float) GridMath::profitPerGridPct('0.003', '0.001'), 1e-12);
        // fee-negative grid goes below zero
        $this->assertLessThan(0, (float) GridMath::profitPerGridPct('0.001', '0.001'));
    }

    public function testRejectsInvalidRange(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        GridMath::levels('200', '100', 4, 'Geometric');
    }

    public function testRejectsTooFewLevels(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        GridMath::levels('100', '200', 1, 'Geometric');
    }
}
