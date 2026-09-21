<?php

namespace Tests\Custom\Bot;

use App\BotOrder;
use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\Allocator;
use App\Domains\Bot\BudgetGuard;
use App\Domains\Bot\BudgetPool;
use App\GridRun;
use App\GridRunQuery;
use Tests\Builder\Support\DbTestCase;

/**
 * planPool() and rebalance().
 *
 * The rule the whole allocator is built around: it may only move capital that
 * is IDLE inside a slice. A run's hard floor includes the quote its open
 * inventory is tied up in, so a rebalance can never ask a run to divest — which
 * keeps the allocator entirely outside the never-sell-at-a-loss problem. The
 * only path that can require a divestment is a pool lowering, and that reports
 * a shortfall and sells nothing.
 */
class AllocatorTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (GridRunQuery::create()->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)->find() as $r) {
            $r->setStatus('Halted');
            $r->save();
        }
        $this->setConfig('gtbot_shared_budget_quote', '1000');
        $this->setConfig('gtbot_use_all_funds', '0');
        $this->setConfig('gtbot_max_drawdown_pct', '0');
        // Pinned at the shipped default (2026-09-21): rebalance plans against
        // cap × (1 − 5%) = 950, so "the whole pool" below means the
        // ALLOCATABLE pool. The reserve's own rules live in PoolReserveTest.
        $this->setConfig('gtbot_pool_reserve_pct', BudgetPool::DEFAULT_RESERVE_PCT);
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

    private function mkRun(string $budget, string $algo = 'Grid', string $mode = 'Auto', int $levels = 5): GridRun
    {
        $r = new GridRun();
        $r->setLabel(static::uniq('alloc'));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Live');
        $r->setAlgo($algo);
        $r->setAllocMode($mode);
        $r->setSimulated(true);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels($levels);
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
        $r->setRunUid(static::uniq('alloc'));
        $r->setLastTickAt(date('Y-m-d H:i:s'));
        $r->setLastPrice('150');
        $r->save();
        return $r;
    }

    private function holdInventory(GridRun $run, string $price, string $qty): void
    {
        $o = new BotOrder();
        $o->setIdGridRun((int) $run->getIdGridRun());
        $o->setClientOrderId(static::uniq('alloc-o'));
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

    private function slice(GridRun $r): string
    {
        $r->reload();
        return bcadd((string) $r->getBudgetQuote(), '0', 0);
    }

    // ── planPool: pure ──────────────────────────────────────────────────

    private function row(string $budget, string $floor, bool $auto = true): array
    {
        return ['budget' => $budget, 'floor' => $floor, 'auto' => $auto];
    }

    public function testPlanPoolSpreadsARaiseProRataAndSumsToTheCap(): void
    {
        $plan = Allocator::planPool('1200', [
            1 => $this->row('400', '100'),
            2 => $this->row('200', '100'),
        ]);
        $this->assertSame('1200', bcadd(bcadd($plan[1], $plan[2], 0), '0', 0));
        // the bigger slice keeps the bigger share
        $this->assertSame(1, bccomp($plan[1], $plan[2], 0));
    }

    public function testPlanPoolShrinksProRataButNeverBelowAFloor(): void
    {
        // cap 400 against floors 250 + 100 leaves 50 of spare to share out, so
        // both land ABOVE their floor while the total lands exactly on the cap
        $plan = Allocator::planPool('400', [
            1 => $this->row('400', '250'),
            2 => $this->row('400', '100'),
        ]);
        $this->assertGreaterThanOrEqual(0, bccomp($plan[1], '250', 0), 'floor is a hard stop');
        $this->assertGreaterThanOrEqual(0, bccomp($plan[2], '100', 0), 'floor is a hard stop');
        $this->assertSame(0, bccomp('400', bcadd($plan[1], $plan[2], 0), 0), 'the plan must sum to the cap');
        $this->assertSame(-1, bccomp($plan[1], '400', 0), 'and both must have come down');
        $this->assertSame(-1, bccomp($plan[2], '400', 0));
    }

    public function testPlanPoolPutsEveryoneOnTheirFloorWhenTheFloorsExceedTheCap(): void
    {
        $plan = Allocator::planPool('200', [
            1 => $this->row('400', '250'),
            2 => $this->row('400', '150'),
        ]);
        $this->assertSame(0, bccomp('250', $plan[1], 0));
        $this->assertSame(0, bccomp('150', $plan[2], 0));
    }

    public function testPlanPoolNeverTouchesAFixedRun(): void
    {
        $plan = Allocator::planPool('1000', [
            1 => $this->row('600', '100', false),
            2 => $this->row('200', '100'),
        ]);
        $this->assertSame('600', $plan[1], 'a pinned slice is untouchable');
        $this->assertSame(0, bccomp('400', $plan[2], 0), 'the auto run absorbs the rest');
    }

    public function testPlanPoolReservesPinnedSlicesEvenWhenTheyExceedTheCap(): void
    {
        $plan = Allocator::planPool('500', [
            1 => $this->row('900', '100', false),
            2 => $this->row('200', '150'),
        ]);
        $this->assertSame('900', $plan[1]);
        $this->assertSame(0, bccomp('150', $plan[2], 0), 'auto run falls to its floor');
    }

    // ── rebalance: DB-backed ────────────────────────────────────────────

    public function testRebalanceSpreadsIdleHeadroom(): void
    {
        $a = $this->mkRun('300');
        $b = $this->mkRun('200');

        $res = Allocator::rebalance('test');
        $this->assertTrue($res['ok'], $res['message']);
        $this->assertSame(
            0,
            bccomp('950', bcadd($this->slice($a), $this->slice($b), 0), 0),
            'the allocatable pool should be allocated — the 5% reserve is left idle'
        );
        $this->assertNull(BudgetGuard::check());
    }

    public function testRebalanceIsIdempotent(): void
    {
        $a = $this->mkRun('300');
        $b = $this->mkRun('200');
        Allocator::rebalance('first');
        $before = [$this->slice($a), $this->slice($b)];

        $second = Allocator::rebalance('second');
        $this->assertSame([], $second['applied'], 'a settled pool must write nothing');
        $this->assertSame([], $second['reloaded'], 'and enqueue no Reload');
        $this->assertSame($before, [$this->slice($a), $this->slice($b)]);
    }

    public function testRebalanceSkipsAFixedRun(): void
    {
        $pinned = $this->mkRun('300', 'Grid', 'Fixed');
        $auto = $this->mkRun('200');

        $res = Allocator::rebalance('test');
        $this->assertSame('300', $this->slice($pinned), 'Fixed must not move');
        $this->assertSame('650', $this->slice($auto), '950 allocatable − the 300 pinned');
        $this->assertArrayHasKey((int) $pinned->getIdGridRun(), $res['skipped']);
        $this->assertStringContainsString('Fixed', $res['skipped'][(int) $pinned->getIdGridRun()]);
    }

    public function testADonorIsCappedAtItsCommittedInventory(): void
    {
        // the load-bearing rule: a fully-invested run donates nothing, because
        // taking its slice down would mean selling
        $held = $this->mkRun('600');
        $this->holdInventory($held, '500', '1');   // 500 quote committed
        $other = $this->mkRun('200');

        $this->setConfig('gtbot_shared_budget_quote', '700');
        Allocator::rebalance('tighten');

        $this->assertSame(
            1,
            bccomp($this->slice($held), '499', 0),
            'the committed 500 must never be reallocated away'
        );
    }

    public function testRebalanceReportsAShortfallRatherThanForcingASale(): void
    {
        $held = $this->mkRun('600');
        $this->holdInventory($held, '550', '1');
        $this->setConfig('gtbot_shared_budget_quote', '100');

        $res = Allocator::rebalance('tighten');
        $this->assertTrue($res['ok']);
        $this->assertSame(1, bccomp($res['shortfall'], '0', 0), 'the unfreeable part is reported');
        $this->assertStringContainsString('Nothing was sold', $res['message']);
        $this->assertNotSame([], $res['proposals'], 'the operator gets advice, not a forced exit');
    }

    public function testRebalanceOnlyReloadsRunsThatActuallyMoved(): void
    {
        $a = $this->mkRun('475');
        $b = $this->mkRun('475');
        // already exactly the allocatable pool — nothing to do
        $res = Allocator::rebalance('test');
        $this->assertSame([], $res['reloaded']);
    }

    public function testOnSliceFreedRedistributes(): void
    {
        $a = $this->mkRun('300');
        $b = $this->mkRun('200');
        $res = Allocator::onSliceFreed(999, '500', 'run_retired');
        $this->assertTrue($res['ok']);
        $this->assertSame(
            0,
            bccomp('950', bcadd($this->slice($a), $this->slice($b), 0), 0)
        );
    }

    public function testOnSliceFreedWithNothingFreedIsANoOp(): void
    {
        $res = Allocator::onSliceFreed(1, '0', 'run_retired');
        $this->assertTrue($res['ok']);
        $this->assertSame([], $res['moved']);
    }

    public function testHardFloorIncludesCommittedQuote(): void
    {
        $r = $this->mkRun('600');
        $bare = Allocator::hardFloor($r);
        $this->holdInventory($r, '480', '1');
        \App\BotOrderPeer::clearInstancePool();
        $withInventory = Allocator::hardFloor($r);
        $this->assertSame(1, bccomp($withInventory, $bare, 0), 'committed quote raises the floor');
        $this->assertSame(1, bccomp($withInventory, '479', 0));
    }

    public function testPlanOnlyModeWritesNothing(): void
    {
        $a = $this->mkRun('300');
        $res = Allocator::rebalance('preview', false);
        $this->assertTrue($res['ok']);
        $this->assertSame('300', $this->slice($a), 'a preview must not write');
        $this->assertSame([], $res['reloaded']);
    }
}
