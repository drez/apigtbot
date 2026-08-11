<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\DecisionScorer;
use App\Domains\Bot\GridMath;
use PHPUnit\Framework\TestCase;

class ImprovementsTest extends TestCase
{
    // ── BottomWeighted allocation ───────────────────────────────────────

    public function testBottomWeightedSpendsTotalBudget(): void
    {
        $prices = ['100', '125', '150', '175'];
        $qtys = GridMath::allocate('1000', $prices, 'BottomWeighted');
        $spent = '0';
        foreach ($qtys as $i => $q) {
            $spent = bcadd($spent, bcmul($q, $prices[$i], 12), 12);
        }
        $this->assertEqualsWithDelta(1000.0, (float) $spent, 0.001, 'weights must normalize to the full budget');
    }

    public function testBottomWeightedIsHeavierAtTheFloor(): void
    {
        $prices = ['100', '125', '150', '175'];
        $qtys = GridMath::allocate('1000', $prices, 'BottomWeighted');
        $quoteBottom = bcmul($qtys[0], '100', 12);
        $quoteTop = bcmul($qtys[3], '175', 12);
        // bottom level gets 2x the quote of the top level
        $this->assertEqualsWithDelta(2.0, (float) $quoteBottom / (float) $quoteTop, 0.01);
    }

    public function testBottomWeightedSingleLevelDegenerates(): void
    {
        $qtys = GridMath::allocate('500', ['100'], 'BottomWeighted');
        $this->assertSame(0, bccomp(bcmul($qtys[0], '100', 8), '500', 4));
    }

    // ── decision verdict rule ───────────────────────────────────────────

    public function testVerdictWinWhenRealized(): void
    {
        $this->assertSame('Win', DecisionScorer::verdict('0.5', 1, true));
        $this->assertSame('Win', DecisionScorer::verdict('0.5', 2, false), 'harvested P/L wins even if price left later');
    }

    public function testVerdictLossWhenNothingHarvestedAndPriceLeftRange(): void
    {
        $this->assertSame('Loss', DecisionScorer::verdict('0', 0, false));
    }

    public function testVerdictFlatWhenStillInRangeWaiting(): void
    {
        $this->assertSame('Flat', DecisionScorer::verdict('0', 0, true));
    }
}
