<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\GridStateHydrator;
use PHPUnit\Framework\TestCase;

class GridStateHydratorTest extends TestCase
{
    public function testRebuildsMachineFromOrderRows(): void
    {
        $m = GridStateHydrator::machine(
            ['100', '125', '150', '175', '200'],
            ['1', '1', '1', '1'],
            [
                ['level_idx' => 0, 'side' => 'Buy', 'state' => 'BUY_OPEN'],
                ['level_idx' => 1, 'side' => 'Sell', 'state' => 'SELL_OPEN'],
                // level 2: filled buy + filled sell (completed cycle) → EMPTY-ish history rows
                ['level_idx' => 2, 'side' => 'Buy', 'state' => 'Filled'],
                ['level_idx' => 2, 'side' => 'Sell', 'state' => 'Filled'],
            ]
        );
        $this->assertSame('BUY_OPEN', $m->state(0));
        $this->assertSame('SELL_OPEN', $m->state(1));
        $this->assertSame('EMPTY', $m->state(2));
        $this->assertSame('EMPTY', $m->state(3));
        // hydrated SELL_OPEN restores held inventory → invariant holds
        $this->assertSame(0, bccomp($m->openSellQty(), $m->heldInventory(), 8));
        $this->assertSame(0, bccomp($m->heldInventory(), '1', 8));
    }

    public function testHydratedMachineContinuesCycle(): void
    {
        $m = GridStateHydrator::machine(
            ['100', '125', '150', '175', '200'],
            ['1', '1', '1', '1'],
            [['level_idx' => 1, 'side' => 'Sell', 'state' => 'SELL_OPEN']]
        );
        $res = $m->onSellFill(1, '0');
        $this->assertSame(0, bccomp($res['cycle']['gross_pnl'], '25', 8));
        $this->assertSame('BUY_OPEN', $m->state(1));
    }
}
