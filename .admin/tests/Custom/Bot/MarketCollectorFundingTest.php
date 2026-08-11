<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\MarketCollector;
use PHPUnit\Framework\TestCase;

/**
 * 30d funding-rate percentile (advisory): the regime sweep showed BTC's
 * crowded-longs windows (top funding quartile) averaged −10/wk vs +4 for the
 * bottom quartile — real information, but sign-flipped on BNB, so it informs
 * the routine and never gates. Percentile = share of the trailing prints ≤
 * the current one.
 */
class MarketCollectorFundingTest extends TestCase
{
    public function testCurrentAtTopOfRangeReadsHigh(): void
    {
        $rates = array_merge(array_fill(0, 89, 0.0001), [0.0009]);
        $this->assertSame(100.0, MarketCollector::fundingPercentile($rates));
    }

    public function testCurrentAtBottomReadsLow(): void
    {
        $rates = array_merge(array_fill(0, 89, 0.0001), [-0.0005]);
        $this->assertEqualsWithDelta(1.1, MarketCollector::fundingPercentile($rates), 0.1);
    }

    public function testMidpackReadsMiddle(): void
    {
        // 0..89 ascending, current = 45 → ~half the prints are ≤ it
        $rates = array_map(static fn ($i) => $i / 10000, range(0, 89));
        $rates[] = 45 / 10000;
        $p = MarketCollector::fundingPercentile($rates);
        $this->assertGreaterThan(45, $p);
        $this->assertLessThan(56, $p);
    }

    public function testTooFewPrintsIsNull(): void
    {
        $this->assertNull(MarketCollector::fundingPercentile(array_fill(0, 29, 0.0001)));
    }
}
