<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\LevelStateMachine;
use PHPUnit\Framework\TestCase;

class LevelStateMachineTest extends TestCase
{
    /** Grid [100,125,150,175,200], qty 1 per buy level. */
    private function machine(): LevelStateMachine
    {
        return new LevelStateMachine(
            ['100', '125', '150', '175', '200'],
            ['1', '1', '1', '1']
        );
    }

    public function testInitialIntentsOnlyBelowCurrentPrice(): void
    {
        $m = $this->machine();
        $intents = $m->initialIntents('150');
        // levels 100 and 125 are strictly below 150; 150 itself gets no buy
        $this->assertCount(2, $intents);
        $this->assertSame('Buy', $intents[0]->side);
        $this->assertSame(0, $intents[0]->levelIdx);
        $this->assertSame(0, bccomp($intents[0]->price, '100', 8));
        $this->assertSame(1, $intents[1]->levelIdx);
        $this->assertSame(0, bccomp($intents[1]->price, '125', 8));
        $this->assertSame('BUY_OPEN', $m->state(0));
        $this->assertSame('BUY_OPEN', $m->state(1));
        $this->assertSame('EMPTY', $m->state(2));
    }

    public function testBuyFillArmsSellOneLevelUp(): void
    {
        $m = $this->machine();
        $m->initialIntents('150');
        $sell = $m->onBuyFill(1);
        $this->assertSame('Sell', $sell->side);
        $this->assertSame(1, $sell->levelIdx);
        $this->assertSame(0, bccomp($sell->price, '150', 8), 'sell target is level 2 price');
        $this->assertSame(0, bccomp($sell->qty, '1', 8));
        $this->assertSame('SELL_OPEN', $m->state(1));
    }

    public function testSellFillRealizesCycleAndRearmsBuy(): void
    {
        $m = $this->machine();
        $m->initialIntents('150');
        $m->onBuyFill(1);
        $result = $m->onSellFill(1, '0.275'); // fees
        $cycle = $result['cycle'];
        // gross = 1*(150-125) = 25; net = 25 - 0.275
        $this->assertSame(0, bccomp($cycle['gross_pnl'], '25', 8));
        $this->assertSame(0, bccomp($cycle['realized_pnl'], '24.725', 8));
        $this->assertSame(0, bccomp($cycle['buy_price'], '125', 8));
        $this->assertSame(0, bccomp($cycle['sell_price'], '150', 8));
        $rearm = $result['rearm'];
        $this->assertSame('Buy', $rearm->side);
        $this->assertSame(0, bccomp($rearm->price, '125', 8));
        $this->assertSame('BUY_OPEN', $m->state(1));
    }

    public function testTopLevelNeverGetsBuy(): void
    {
        $m = $this->machine();
        $intents = $m->initialIntents('500'); // price above whole range
        // buys at levels 0..3 only — level 4 (200) is a sell target, never a buy
        $this->assertCount(4, $intents);
        foreach ($intents as $i) {
            $this->assertLessThan(4, $i->levelIdx);
        }
    }

    public function testGapBurstFillsProcessInOrder(): void
    {
        $m = $this->machine();
        $m->initialIntents('200');
        // price gaps down through levels 3 and 2 in one tick
        $s3 = $m->onBuyFill(3);
        $s2 = $m->onBuyFill(2);
        $this->assertSame(0, bccomp($s3->price, '200', 8));
        $this->assertSame(0, bccomp($s2->price, '175', 8));
        $this->assertSame('SELL_OPEN', $m->state(3));
        $this->assertSame('SELL_OPEN', $m->state(2));
    }

    public function testInventoryInvariantHolds(): void
    {
        $m = $this->machine();
        $m->initialIntents('150');
        $m->onBuyFill(0);
        $m->onBuyFill(1);
        // open sell qty == held inventory
        $this->assertSame(0, bccomp($m->openSellQty(), $m->heldInventory(), 8));
        $m->onSellFill(1, '0');
        $this->assertSame(0, bccomp($m->openSellQty(), $m->heldInventory(), 8));
    }

    public function testBuyFillOnNonOpenLevelThrows(): void
    {
        $m = $this->machine();
        $m->initialIntents('150');
        $this->expectException(\LogicException::class);
        $m->onBuyFill(3); // level 3 was never armed
    }

    public function testPartialBuyBookedSellsActualQtyAndKeepsInvariant(): void
    {
        $m = $this->machine();
        $m->initialIntents('150');
        $sell = $m->onPartialBuyBooked(0, '0.4');
        $this->assertSame('Sell', $sell->side);
        $this->assertSame(0, $sell->levelIdx);
        $this->assertSame(0, bccomp($sell->price, '125', 8)); // one level up
        $this->assertSame(0, bccomp($sell->qty, '0.4', 8));   // actual, not level qty
        $this->assertSame('SELL_OPEN', $m->state(0));
        $this->assertSame(0, bccomp($m->heldInventory(), '0.4', 8));
        $this->assertSame(0, bccomp($m->openSellQty(), $m->heldInventory(), 8));
    }

    public function testSellFillBooksCycleWithActualHeldQty(): void
    {
        $m = $this->machine();
        $m->initialIntents('150');
        $m->onPartialBuyBooked(0, '0.4');
        $res = $m->onSellFill(0, '0.1');
        // gross = 0.4 × (125 − 100) = 10, realized = 10 − 0.1
        $this->assertSame(0, bccomp($res['cycle']['qty'], '0.4', 8));
        $this->assertSame(0, bccomp($res['cycle']['gross_pnl'], '10', 8));
        $this->assertSame(0, bccomp($res['cycle']['realized_pnl'], '9.9', 8));
        // level re-arms at the standard full level qty
        $this->assertSame(0, bccomp($res['rearm']->qty, '1', 8));
    }

    public function testPartialBookedOnNonOpenLevelThrows(): void
    {
        $m = $this->machine();
        $this->expectException(\LogicException::class);
        $m->onPartialBuyBooked(0, '0.4'); // never armed
    }

    public function testInitialIntentsRespectMinLevel(): void
    {
        $m = $this->machine();
        $intents = $m->initialIntents('160', 1);
        // levels 0..2 are below 160 but the window starts at 1
        $this->assertCount(2, $intents);
        $this->assertSame(1, $intents[0]->levelIdx);
        $this->assertSame(2, $intents[1]->levelIdx);
        $this->assertSame('EMPTY', $m->state(0));
    }

    public function testHydrateSellWithExplicitHeldQty(): void
    {
        $m = $this->machine();
        $m->hydrateLevel(2, 'SELL_OPEN', '0.25');
        $this->assertSame(0, bccomp($m->heldInventory(), '0.25', 8));
        $this->assertSame(0, bccomp($m->openSellQty(), '0.25', 8));
    }
}
