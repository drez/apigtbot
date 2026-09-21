<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\RunFactory;
use App\GridRun;
use App\GridRunQuery;
use Tests\Builder\Support\DbTestCase;

/**
 * Simulated-vs-real is a SYSTEM mode (one wallet). A run the machine creates
 * — gtbot_create_run, an auto-filled fleet slot — is born into the fleet's
 * mode: the column default (paper) dropped into a real fleet made it 'mixed'
 * for good, with every mode-scoped surface reading the wrong wallet.
 */
class RunFactoryModeTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (GridRunQuery::create()->filterByStatus('Done', \Criteria::NOT_EQUAL)->find() as $r) {
            $r->setStatus('Done');
            $r->save();
        }
    }

    private function existing(bool $simulated): void
    {
        $r = new GridRun();
        $r->setLabel(static::uniq('mode'));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Halted');
        $r->setSimulated($simulated);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setBudgetQuote('100');
        $r->setRunUid(static::uniq('m'));
        $r->save();
    }

    public function testARunBornIntoARealFleetIsReal(): void
    {
        $this->existing(false);
        $this->assertFalse((bool) RunFactory::trendDraft('BNBUSDT', '600')->getSimulated());
    }

    public function testARunBornIntoAPaperFleetIsPaper(): void
    {
        $this->existing(true);
        $this->assertTrue((bool) RunFactory::trendDraft('BNBUSDT', '600')->getSimulated());
    }

    public function testTheFirstRunEverIsPaper(): void
    {
        $this->assertTrue((bool) RunFactory::trendDraft('BNBUSDT', '600')->getSimulated());
    }

    public function testAMixedFleetIsNotMadeWorse(): void
    {
        // already mixed: real money is in play, so the new arm joins the real side
        $this->existing(false);
        $this->existing(true);
        $this->assertFalse((bool) RunFactory::trendDraft('BNBUSDT', '600')->getSimulated());
    }
}
