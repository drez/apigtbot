<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\Gateway\SimulatedGateway;
use PHPUnit\Framework\TestCase;

class SimulatedGatewayTest extends TestCase
{
    public function testLimitBuyFillsWhenPriceCrossesDown(): void
    {
        $g = new SimulatedGateway();
        $g->placeOrder('cid-1', 'Buy', '125', '1');
        $this->assertSame([], $g->step('130'));
        $fills = $g->step('124');
        $this->assertCount(1, $fills);
        $this->assertSame('cid-1', $fills[0]['client_order_id']);
        $this->assertSame('Buy', $fills[0]['side']);
        $this->assertSame(0, bccomp($fills[0]['price'], '125', 8));
        $this->assertSame([], $g->openOrders(), 'filled order leaves the book');
    }

    public function testLimitSellFillsWhenPriceCrossesUp(): void
    {
        $g = new SimulatedGateway();
        $g->placeOrder('cid-2', 'Sell', '150', '1');
        $this->assertSame([], $g->step('149'));
        $fills = $g->step('151');
        $this->assertCount(1, $fills);
        $this->assertSame('Sell', $fills[0]['side']);
    }

    public function testTouchCountsAsFill(): void
    {
        $g = new SimulatedGateway();
        $g->placeOrder('b', 'Buy', '125', '1');
        $this->assertCount(1, $g->step('125'));
    }

    public function testCancelRemovesOrder(): void
    {
        $g = new SimulatedGateway();
        $g->placeOrder('cid-3', 'Buy', '125', '1');
        $g->cancelOrder('cid-3');
        $this->assertSame([], $g->step('100'));
    }

    public function testDuplicateClientIdRejected(): void
    {
        $g = new SimulatedGateway();
        $g->placeOrder('dup', 'Buy', '125', '1');
        $this->expectException(\LogicException::class);
        $g->placeOrder('dup', 'Buy', '120', '1');
    }

    public function testGapThroughSeveralOrdersFillsAllInPriceOrder(): void
    {
        $g = new SimulatedGateway();
        $g->placeOrder('b175', 'Buy', '175', '1');
        $g->placeOrder('b125', 'Buy', '125', '1');
        $g->placeOrder('b150', 'Buy', '150', '1');
        $fills = $g->step('100'); // gap through all three
        $this->assertCount(3, $fills);
        // buys fill highest-first as price falls
        $this->assertSame(['b175', 'b150', 'b125'], array_column($fills, 'client_order_id'));
    }
}
