<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\DecisionScorer;
use PHPUnit\Framework\TestCase;

/**
 * Counterfactual verdict (2026-08-23, from ai-hedge-fund's event-study
 * framing): a refit is judged against "what if the previous geometry had
 * stayed" on the same tape through the same simulator, not only against 0.
 */
class DecisionScorerCounterfactualTest extends TestCase
{
    private function cfg(string $lo, string $hi, int $n): array
    {
        return ['p_low' => $lo, 'p_high' => $hi, 'n_levels' => $n, 'spacing' => 'Geometric', 'allocation' => 'EqualQuote',
            'budget_quote' => '1000', 'fee_pct' => '0.001', 'min_notional' => '5', 'max_position_quote' => '1000',
            'max_order_quote' => '1000', 'daily_loss_limit_quote' => '1000', 'breakout_buffer_pct' => '0.02',
            'breakout_policy' => 'HaltAndHold', 'max_open_orders' => 60];
    }

    public function testNoPreviousGeometryMeansNoCounterfactual(): void
    {
        $this->assertNull(DecisionScorer::counterfactual($this->cfg('90', '110', 10), null, ['100', '95', '100']));
        $this->assertNull(DecisionScorer::counterfactual($this->cfg('90', '110', 10), $this->cfg('80', '120', 10), ['100']));
    }

    public function testPositiveWhenNewGridHarvestsMoreThanOldOnSameTape(): void
    {
        // tight grid cycles on a ±4% wobble; the wide old grid never fills a level
        $tape = ['100', '96', '100', '96', '100', '96', '100', '96', '100'];
        $cf = DecisionScorer::counterfactual($this->cfg('94', '106', 4), $this->cfg('50', '150', 4), $tape);
        $this->assertNotNull($cf);
        $this->assertGreaterThan(0, (float) $cf);
        // and the mirror image is negative: the refit cost money vs leaving it
        $cf2 = DecisionScorer::counterfactual($this->cfg('50', '150', 4), $this->cfg('94', '106', 4), $tape);
        $this->assertLessThan(0, (float) $cf2);
    }

    /** 2026-09-05: the tape replays each bar's low/high, not only its close */
    public function testBarPathVisitsTheExtremesBeforeTheClose(): void
    {
        $bars = [
            ['high' => '104', 'low' => '99', 'close' => '103'],  // up bar: low, high, close
            ['high' => '103.5', 'low' => '97', 'close' => '98'], // down bar: high, low, close
        ];
        $this->assertSame(['100', '99', '104', '103', '103.5', '97', '98'], DecisionScorer::barPath($bars, '100'));
        $this->assertSame(['99', '104', '103', '103.5', '97', '98'], DecisionScorer::barPath($bars, null));
    }

    public function testCounterfactualSeesFillsInsideTheBars(): void
    {
        // six flat closes at 100 — a closes-only tape fills nothing; the bars
        // wobbled ±4% inside, which the 94–106 grid harvests and the wide one does not
        $bars = array_fill(0, 6, ['high' => '104', 'low' => '96', 'close' => '100']);
        $tape = DecisionScorer::barPath($bars, '100');
        $cf = DecisionScorer::counterfactual($this->cfg('94', '106', 4), $this->cfg('50', '150', 4), $tape);
        $this->assertNotNull($cf);
        $this->assertGreaterThan(0, (float) $cf);
        $closesOnly = array_fill(0, 7, '100');
        $this->assertSame('0.00000000', DecisionScorer::counterfactual($this->cfg('94', '106', 4), $this->cfg('50', '150', 4), $closesOnly));
    }

    public function testVerdictWorseWhenNoChangeWouldHaveBeatIt(): void
    {
        $this->assertSame('Win', DecisionScorer::verdict('5', 2, true));
        $this->assertSame('Win', DecisionScorer::verdict('5', 2, true, '0.5', '1'));     // within fee tolerance
        $this->assertSame('Worse', DecisionScorer::verdict('5', 2, true, '-3', '1'));    // old grid would have made 3 more
        $this->assertSame('Worse', DecisionScorer::verdict('0', 0, true, '-3', '1'));    // flat, but no-change was better
        $this->assertSame('Loss', DecisionScorer::verdict('0', 0, false, '-3', '1'));    // a Loss stays a Loss
        $this->assertSame('Flat', DecisionScorer::verdict('0', 0, true, null, '1'));
    }
}
