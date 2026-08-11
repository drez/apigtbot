<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\RefitPlanner;
use PHPUnit\Framework\TestCase;

class RefitPlannerTest extends TestCase
{
    private function candidates(): array
    {
        return [
            ['name' => 'wide', 'p_low' => '50000', 'p_high' => '72000', 'n_levels' => 40, 'spacing_pct' => '0.009'],
            ['name' => 'medium', 'p_low' => '58000', 'p_high' => '68000', 'n_levels' => 30, 'spacing_pct' => '0.006'],
            ['name' => 'tight', 'p_low' => '53000', 'p_high' => '65000', 'n_levels' => 34, 'spacing_pct' => '0.006'],
        ];
    }

    public function testHoldWhenPriceComfortablyInsideRange(): void
    {
        $d = RefitPlanner::decide(['p_low' => '60000', 'p_high' => '72000'], '66000', $this->candidates());
        $this->assertSame('hold', $d['action']);
        $this->assertStringContainsString('inside', $d['reason']);
    }

    public function testRefitWhenPriceNearUpperBound(): void
    {
        // price 71500 is within 2% of p_high 72000 → grid about to go idle
        $d = RefitPlanner::decide(['p_low' => '60000', 'p_high' => '72000'], '71500', $this->candidates());
        $this->assertSame('refit', $d['action']);
        // chosen candidate must bracket the price
        $this->assertLessThan(71500, (float) $d['candidate']['p_low']);
        $this->assertGreaterThan(71500, (float) $d['candidate']['p_high']);
    }

    public function testRefitWhenPriceBelowRange(): void
    {
        $d = RefitPlanner::decide(['p_low' => '60000', 'p_high' => '72000'], '55000', $this->candidates());
        $this->assertSame('refit', $d['action']);
        $this->assertLessThan(55000, (float) $d['candidate']['p_low']);
        $this->assertGreaterThan(55000, (float) $d['candidate']['p_high']);
    }

    public function testChoosesMostCenteredBracketingCandidate(): void
    {
        // price 60000, near the low bound of the active 60000-72000 range
        $d = RefitPlanner::decide(['p_low' => '60000', 'p_high' => '72000'], '60000', $this->candidates());
        $this->assertSame('refit', $d['action']);
        // 'medium' (58k-68k, mid 63k) and 'tight' (53k-65k, mid 59k) and 'wide'(mid 61k) bracket 60000;
        // most centered on 60000 is tight (mid 59k, |1000|) vs wide (mid 61k, |1000|) vs medium(63k,|3000|).
        // tie broken toward the tighter (more cycles) — either tight or wide acceptable, not medium
        $this->assertContains($d['candidate']['name'], ['tight', 'wide']);
        $this->assertNotSame('medium', $d['candidate']['name']);
    }

    public function testHoldWhenNoCandidateBracketsPrice(): void
    {
        // price crashed far below every candidate's floor
        $d = RefitPlanner::decide(['p_low' => '60000', 'p_high' => '72000'], '40000', $this->candidates());
        $this->assertSame('hold', $d['action']);
        $this->assertStringContainsString('bracket', $d['reason']);
    }

    public function testHoldWhenBestCandidateMatchesActiveRange(): void
    {
        // price near bound but the only sensible candidate ≈ the active range
        $near = [['name' => 'same', 'p_low' => '60200', 'p_high' => '71800', 'n_levels' => 12, 'spacing_pct' => '0.015']];
        $d = RefitPlanner::decide(['p_low' => '60000', 'p_high' => '72000'], '71500', $near);
        $this->assertSame('hold', $d['action']);
        $this->assertStringContainsString('already', $d['reason']);
    }

    public function testEmptyCandidatesHolds(): void
    {
        $d = RefitPlanner::decide(['p_low' => '60000', 'p_high' => '72000'], '71500', []);
        $this->assertSame('hold', $d['action']);
    }

    // cron deferral moved out of the planner: bin/gtbot-refit now stands down
    // entirely while RoutineLiveness says the Claude routine is alive (the
    // old in-range-only defer let the cron front-run the routine on breakouts
    // with quantile grids the routine then reverted — 2026-07-30 BNB Loss).

    // ── down-exit trend guard (width-sweep.php 2026-07-28: mechanically
    // re-centering below the range in a downtrend was the worst measured
    // behavior — chasing realizes MTM and re-arms buys lower) ──────────────

    public function testDownExitInDowntrendHolds(): void
    {
        $d = RefitPlanner::decide(['p_low' => '60000', 'p_high' => '72000'], '55000', $this->candidates(), 'strong_down');
        $this->assertSame('hold', $d['action']);
        $this->assertStringContainsString('downtrend', $d['reason']);

        $d = RefitPlanner::decide(['p_low' => '60000', 'p_high' => '72000'], '55000', $this->candidates(), 'down');
        $this->assertSame('hold', $d['action'], 'plain down trend must also refuse the chase');
    }

    public function testDownExitInNonDowntrendStillRefits(): void
    {
        // a flush that the trend has not confirmed (wick / bottoming range):
        // re-centering is still the right call
        foreach (['side', 'up', 'strong_up'] as $trend) {
            $d = RefitPlanner::decide(['p_low' => '60000', 'p_high' => '72000'], '55000', $this->candidates(), $trend);
            $this->assertSame('refit', $d['action'], "trend $trend must not block a down re-center");
        }
    }

    public function testUpExitRefitsRegardlessOfTrend(): void
    {
        // an up-exit books profit and re-arms higher — never blocked
        $d = RefitPlanner::decide(['p_low' => '60000', 'p_high' => '72000'], '71500', $this->candidates(), 'strong_down');
        $this->assertSame('refit', $d['action']);
    }

    public function testNoTrendKeepsLegacyDownRefit(): void
    {
        // callers that pass no trend get the pre-guard behavior
        $d = RefitPlanner::decide(['p_low' => '60000', 'p_high' => '72000'], '55000', $this->candidates());
        $this->assertSame('refit', $d['action']);
    }

    public function testNarrowRangeMidPriceHolds(): void
    {
        // Regression (prod 2026-07-23): with a fixed 2%-of-bound trigger, a
        // narrow grid's low and high triggers OVERLAP (64200×1.02 = 65484 >
        // 66800×0.98 = 65464) so every price read "near a bound" and the cron
        // rewrote any tight grid it saw. Mid-range price must hold.
        $d = RefitPlanner::decide(['p_low' => '64200', 'p_high' => '66800'], '65768', $this->candidates());
        $this->assertSame('hold', $d['action']);
    }
}
