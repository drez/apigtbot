<?php

namespace Tests\Custom\Bot;

use App\BotOrder;
use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\BudgetGuard;
use App\Domains\Dashboard\DashboardData;
use App\Domains\Dashboard\DashboardRenderer;
use App\GridRun;
use App\GridRunQuery;
use Tests\Builder\Support\DbTestCase;

/**
 * The per-run Allocation tiles: how the slice is being used, and what it earns.
 *
 * `committed` is the quote the allocator may NOT reclaim (tied up in
 * inventory); `idle` is what a rebalance could actually move. The two together
 * answer "is this run using its money, and is the money working?".
 */
class DashboardAllocationTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (GridRunQuery::create()->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)->find() as $r) {
            $r->setStatus('Halted');
            $r->save();
        }
        $this->setConfig('gtbot_shared_budget_quote', '1000');
        $this->setConfig('gtbot_alloc_window_days', '14');
    }

    private function setConfig(string $key, string $value): void
    {
        // clear BEFORE the read too: a pooled row already holding $value makes
        // save() a no-op, so the rolled-back DB row keeps the stale value while
        // the pool reports the new one
        \App\ConfigPeer::clearInstancePool();
        $c = ConfigQuery::create()->findOneByConfig($key) ?? (new Config())->setConfig($key);
        $c->setValue($value);
        $c->save();
        \App\ConfigPeer::clearInstancePool();
    }

    private function mkRun(string $budget = '316', string $mode = 'Auto'): GridRun
    {
        $r = new GridRun();
        $r->setLabel(static::uniq('dashalloc'));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setAlgo('Grid');
        $r->setAllocMode($mode);
        $r->setSimulated(true);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(5);
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote($budget);
        $r->setDeployPct(100);
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('1000');
        $r->setMaxOrderQuote('300');
        $r->setDailyLossLimitQuote('60');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(30);
        $r->setRunUid(static::uniq('dashalloc'));
        $r->setLastTickAt(date('Y-m-d H:i:s'));
        $r->setLastPrice('150');
        $r->save();
        return $r;
    }

    private function holdInventory(GridRun $run, string $price, string $qty): void
    {
        $o = new BotOrder();
        $o->setIdGridRun((int) $run->getIdGridRun());
        $o->setClientOrderId(static::uniq('dashalloc-o'));
        $o->setLevelIdx(1);
        $o->setSide('Buy');
        $o->setState('Filled');
        $o->setPrice($price);
        $o->setQty($qty);
        $o->setFilledQty($qty);
        $o->setFeePaid('0.1');
        $o->setSimulated(true);
        $o->save();
    }

    private function html(GridRun $run): string
    {
        return (new DashboardRenderer())->render((new DashboardData($run))->viewModel('/x/'));
    }

    public function testKpisCarryTheAllocationBlock(): void
    {
        $run = $this->mkRun('316');
        $k = (new DashboardData($run))->kpis();

        $this->assertSame('316', $k['alloc_slice']);
        $this->assertTrue($k['alloc_auto']);
        $this->assertSame('Auto', $k['alloc_mode']);
        $this->assertSame(0, bccomp('0', $k['alloc_committed'], 8), 'nothing held yet');
        $this->assertSame(0, bccomp('316', $k['alloc_idle'], 8), 'the whole slice is reallocatable');
        $this->assertFalse($k['alloc_eligible'], 'no cycles yet');
    }

    public function testCommittedInventoryMovesOutOfIdle(): void
    {
        $run = $this->mkRun('316');
        $this->holdInventory($run, '100', '1');   // 100 quote committed
        \App\BotOrderPeer::clearInstancePool();

        $k = (new DashboardData($run))->kpis();
        $this->assertSame(1, bccomp($k['alloc_committed'], '99', 8));
        $this->assertSame(
            0,
            bccomp(bcadd($k['alloc_committed'], $k['alloc_idle'], 8), '316', 8),
            'committed + idle must account for the whole slice'
        );
    }

    public function testRenderedPanelShowsTheAllocationTiles(): void
    {
        $html = $this->html($this->mkRun('316'));
        $this->assertStringContainsString('Allocation', $html);
        $this->assertStringContainsString('Slice', $html);
        $this->assertStringContainsString('Idle', $html);
        $this->assertStringContainsString('Earning /1k/day', $html);
    }

    public function testAnIneligibleRunShowsNaRatherThanAMisleadingZero(): void
    {
        $html = $this->html($this->mkRun('316'));
        $this->assertStringContainsString('n/a', $html);
        $this->assertStringContainsString('needs 10', $html, 'and says why');
    }

    public function testAutoRunShowsTheAutoPill(): void
    {
        $html = $this->html($this->mkRun('316', 'Auto'));
        $this->assertStringContainsString('alloc Auto', $html);
    }

    public function testFixedRunShowsThePinnedPillAndReason(): void
    {
        $html = $this->html($this->mkRun('316', 'Fixed'));
        $this->assertStringContainsString('alloc Fixed', $html);
        $this->assertStringContainsString('pinned by the operator', $html);
    }
}
