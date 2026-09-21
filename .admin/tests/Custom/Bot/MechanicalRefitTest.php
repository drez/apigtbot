<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\MechanicalRefit;
use App\Domains\Bot\RangeFitter;
use PHPUnit\Framework\TestCase;

class MechanicalRefitTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
    }

    // ── candidate ───────────────────────────────────────────────────────

    public function testCandidateBracketsPriceAtTheConfiguredHalfWidth(): void
    {
        $c = MechanicalRefit::candidate('750', '0.010', '0.06', 6);
        $this->assertSame('705.00', $c['p_low']);
        $this->assertSame('795.00', $c['p_high']);
        $this->assertSame(6, $c['n_levels']);
        $this->assertFalse($c['widened']);
        $this->assertGreaterThanOrEqual((float) RangeFitter::SPACING_FLOOR, (float) $c['spacing_pct']);
    }

    public function testCandidateWidensToFourAtrWhenTheTapeIsWild(): void
    {
        // 4h ATR 2% → 4×ATR = 8% > the 6% setting
        $c = MechanicalRefit::candidate('80000', '0.02', '0.06', 6);
        $this->assertTrue($c['widened']);
        $this->assertSame('0.080000000000', $c['half_width']);
        $this->assertSame('73600.00', $c['p_low']);
        $this->assertSame('86400.00', $c['p_high']);
    }

    public function testCandidateDropsLevelsUntilRungsClearTheSpacingFloor(): void
    {
        // ±3% is a 6.19% band: at 1.3%/rung that is 4 rungs, never 8
        $c = MechanicalRefit::candidate('100', '0', '0.03', 8);
        $this->assertSame(4, $c['n_levels']);
        $this->assertGreaterThanOrEqual((float) RangeFitter::SPACING_FLOOR, (float) $c['spacing_pct']);
        // a wide band keeps the requested count
        $this->assertSame(8, MechanicalRefit::candidate('100', '0', '0.10', 8)['n_levels']);
    }

    public function testCandidateNeverGoesBelowTwoLevels(): void
    {
        $this->assertSame(2, MechanicalRefit::candidate('100', '0', '0.005', 8)['n_levels']);
    }

    public function testCandidateClampsExtremeAtrWideningToKeepPLowPositive(): void
    {
        // 4h ATR 30% → 4×ATR = 120%: unclamped that put p_low at −20% of
        // price and killed the hourly pass. The ceiling keeps p_low positive.
        $c = MechanicalRefit::candidate('80000', '0.30', '0.06', 6);
        $this->assertTrue($c['widened']);
        $this->assertSame('0.49', $c['half_width']);
        $this->assertSame('40800.00', $c['p_low']);
        $this->assertSame('119200.00', $c['p_high']);
        $this->assertGreaterThan(0, bccomp($c['p_low'], '0', 8));
    }

    public function testClampLevelsNeverExceedsTheFundableRungs(): void
    {
        $this->assertSame(6, MechanicalRefit::clampLevels(6, '500', '5'));
        $this->assertSame(100, MechanicalRefit::clampLevels(200, '500', '5'));
        $this->assertSame(0, MechanicalRefit::clampLevels(6, '0', '5'), 'flat: no level fundable');
        $this->assertSame(0, MechanicalRefit::clampLevels(1, '500', '5'), 'below two levels is not a ladder');
        $this->assertSame(0, MechanicalRefit::clampLevels(6, '4', '5'), 'one rung at best, below min notional');
    }

    // ── due ─────────────────────────────────────────────────────────────

    private const ACTIVE = ['p_low' => '700', 'p_high' => '800'];

    public function testInsideTheRangeAndInsideTheClockHolds(): void
    {
        $d = MechanicalRefit::due(self::ACTIVE, '750', '2026-09-09 00:00:00', '2026-09-10 00:00:00', 72, 'up');
        $this->assertFalse($d['due']);
        $this->assertStringContainsString('24.0h of 72h', $d['reason']);
    }

    public function testClockElapsedIsDue(): void
    {
        $d = MechanicalRefit::due(self::ACTIVE, '750', '2026-09-06 00:00:00', '2026-09-09 00:00:00', 72, 'sideways');
        $this->assertTrue($d['due']);
        $this->assertStringContainsString('72.0h since', $d['reason']);
    }

    public function testNoJournalIsDueImmediately(): void
    {
        $this->assertTrue(MechanicalRefit::due(self::ACTIVE, '750', null, '2026-09-09 00:00:00', 72, null)['due']);
    }

    public function testPriceAboveTheRangeIsDueEvenInsideTheClock(): void
    {
        $d = MechanicalRefit::due(self::ACTIVE, '812', '2026-09-09 00:00:00', '2026-09-09 01:00:00', 72, 'strong_down');
        $this->assertTrue($d['due'], 'an up-side re-centre books profit and is never blocked');
        $this->assertStringContainsString('above', $d['reason']);
    }

    public function testPriceBelowTheRangeInADowntrendHolds(): void
    {
        $d = MechanicalRefit::due(self::ACTIVE, '690', '2026-09-01 00:00:00', '2026-09-09 00:00:00', 72, 'down');
        $this->assertFalse($d['due'], 'never chase a downtrend lower (width sweep)');
        $this->assertStringContainsString('holding', $d['reason']);
    }

    public function testPriceBelowTheRangeOutsideADowntrendIsDue(): void
    {
        $this->assertTrue(MechanicalRefit::due(self::ACTIVE, '690', '2026-09-09 00:00:00', '2026-09-09 01:00:00', 72, 'sideways')['due']);
    }

    public function testClockRefitInTheLowerHalfOfADowntrendHolds(): void
    {
        // the clock ran out but price sits under the mid in a 4h downtrend:
        // re-anchoring would sit the ladder lower — hold
        $d = MechanicalRefit::due(self::ACTIVE, '720', '2026-09-01 00:00:00', '2026-09-09 00:00:00', 72, 'strong_down');
        $this->assertFalse($d['due']);
        // …but in the upper half the clock refit goes ahead
        $this->assertTrue(MechanicalRefit::due(self::ACTIVE, '780', '2026-09-01 00:00:00', '2026-09-09 00:00:00', 72, 'strong_down')['due']);
    }
}
