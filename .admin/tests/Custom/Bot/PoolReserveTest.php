<?php

namespace Tests\Custom\Bot;

use App\BotCommandQuery;
use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\Allocator;
use App\Domains\Bot\BudgetGuard;
use App\Domains\Bot\BudgetPool;
use App\Domains\Bot\FleetSlots;
use App\Domains\Bot\TrendActivator;
use App\FleetSlotQuery;
use App\GridRun;
use App\GridRunQuery;
use Tests\Builder\Support\DbTestCase;

/**
 * gtbot_pool_reserve_pct — headroom the automatic allocators may not plan
 * against (2026-09-21).
 *
 * The defect: the machine sized slices to fill the pool EXACTLY. Prod ran two
 * trend arms at 350 (gtbot_trend_target_slice) plus two grids at their 300
 * floor against a 1300 cap — 1300 of 1300, zero headroom — so any rounding,
 * any raise, or a one-USDT mark-to-market dip under gtbot_use_all_funds put
 * sum(slices) over cap() and the daemon's per-tick check fail-closed every
 * entry on the fleet at once.
 *
 * The fix is one asymmetry, and these tests exist to keep it: ALLOCATORS plan
 * against cap × (1 − reserve%); the GUARD keeps enforcing the full cap. A
 * fleet sitting between the two is legal and quiet — it is simply not
 * re-raised. Nothing is ever trimmed below a floor or below committed
 * inventory to reach the reserve.
 */
class PoolReserveTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // only the runs this test makes may charge the pool
        foreach (GridRunQuery::create()->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)->find() as $r) {
            $r->setStatus('Halted');
            $r->save();
        }
        foreach (FleetSlotQuery::create()->find() as $fs) {
            $fs->delete();
        }
        $this->cfg('gtbot_shared_budget_quote', '1000');
        $this->cfg(BudgetPool::CONFIG_USE_ALL, '0');
        // the dev DB has no sim wallet (equity 0) — the floor would trip every pass
        $this->cfg('gtbot_max_drawdown_pct', '0');
        $this->cfg(BudgetPool::CONFIG_RESERVE, '5');
    }

    private function cfg(string $key, string $value): void
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

    private function dropCfg(string $key): void
    {
        \App\ConfigPeer::clearInstancePool();
        ConfigQuery::create()->filterByConfig($key)->delete();
        \App\ConfigPeer::clearInstancePool();
    }

    private function mkRun(string $budget, string $algo = 'Grid', int $levels = 5, string $symbol = 'BTCUSDT', int $deploy = 100): GridRun
    {
        $r = new GridRun();
        $r->setLabel(static::uniq('res-' . $algo));
        $r->setSymbol($symbol);
        $r->setStatus('Live');
        $r->setAlgo($algo);
        $r->setAllocMode('Auto');
        $r->setSimulated(true);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels($levels);
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote($budget);
        $r->setDeployPct($deploy);
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid(static::uniq('res'));
        $r->setLastTickAt(date('Y-m-d H:i:s'));
        $r->setLastPrice('150');
        $r->save();
        return $r;
    }

    private function slice(GridRun $r): string
    {
        $r->reload();
        return bcadd((string) $r->getBudgetQuote(), '0', 0);
    }

    // ── the arithmetic and its clamps ───────────────────────────────────

    public function testAllocatableIsTheCapMinusTheReserve(): void
    {
        $this->assertSame('5', bcadd(BudgetPool::reservePct(), '0', 0));
        $this->assertSame('1000', BudgetPool::cap());
        $this->assertSame('950', BudgetPool::allocatable());
        $this->assertSame('50', BudgetPool::reserved());
    }

    public function testJunkOrAbsentConfigFallsBackToTheDefaultAndNeverToZero(): void
    {
        // the one outcome this must never produce is a silently DISABLED
        // reserve, so nothing but an explicit 0 gets to switch it off
        foreach (['', '   ', 'abc', '-3', 'NaN'] as $bad) {
            $this->cfg(BudgetPool::CONFIG_RESERVE, $bad);
            $this->assertSame(
                BudgetPool::DEFAULT_RESERVE_PCT,
                bcadd(BudgetPool::reservePct(), '0', 0),
                "value '$bad' must fall back to the default"
            );
            $this->assertSame('950', BudgetPool::allocatable());
        }

        $this->dropCfg(BudgetPool::CONFIG_RESERVE);
        $this->assertSame(BudgetPool::DEFAULT_RESERVE_PCT, bcadd(BudgetPool::reservePct(), '0', 0), 'a missing row is the default');
        $this->assertSame('950', BudgetPool::allocatable());
    }

    public function testAnExplicitZeroDisablesTheReserve(): void
    {
        $this->cfg(BudgetPool::CONFIG_RESERVE, '0');
        $this->assertSame('0', bcadd(BudgetPool::reservePct(), '0', 0));
        $this->assertSame('1000', BudgetPool::allocatable(), 'the allocators get the whole pool back');
        $this->assertSame('0', BudgetPool::reserved());
    }

    public function testAReserveAboveFiftyIsClampedDown(): void
    {
        $this->cfg(BudgetPool::CONFIG_RESERVE, '80');
        $this->assertSame(BudgetPool::MAX_RESERVE_PCT, bcadd(BudgetPool::reservePct(), '0', 0));
        $this->assertSame('500', BudgetPool::allocatable(), 'more than half held back is a smaller pool, not headroom');
    }

    public function testTheOddUsdtStaysOnTheReserveSide(): void
    {
        // 1301 × 0.95 = 1235.95 — floor, so the reserve is never rounded away
        $this->cfg('gtbot_shared_budget_quote', '1301');
        $this->assertSame('1235', BudgetPool::allocatable());
        $this->assertSame('66', BudgetPool::reserved());
    }

    public function testAllocatableOfPlansAgainstACapNotYetInForce(): void
    {
        // the pre-write leg of a pool lowering plans against the INCOMING
        // number while the old one is still in the config row
        $this->assertSame('1235', BudgetPool::allocatableOf('1300'));
        $this->assertSame('0', BudgetPool::allocatableOf('0'));
    }

    // ── allocators plan against it ──────────────────────────────────────

    public function testHeadroomIsMeasuredAgainstTheAllocatablePool(): void
    {
        $this->mkRun('500');
        $this->mkRun('450');
        $this->assertSame('950', bcadd(Allocator::activeSum(), '0', 0), 'slices already fill the allocatable pool');
        $this->assertSame('0', Allocator::headroom(), 'so there is nothing left to hand out');
        $this->assertNull(BudgetGuard::check(), 'while the guard still sees 50 USDT of slack');
    }

    public function testRebalanceStopsAtTheAllocatableCapAndLeavesTheReserveIdle(): void
    {
        $a = $this->mkRun('300');
        $b = $this->mkRun('200');

        $res = Allocator::rebalance('test');
        $this->assertTrue($res['ok'], $res['message']);
        $this->assertSame(
            '950',
            bcadd($this->slice($a), $this->slice($b), 0),
            'the allocator spreads the pool up to the allocatable cap and stops'
        );
        $this->assertSame('0', $res['shortfall']);
        $this->assertNull(BudgetGuard::check());
        $this->assertSame(
            '50',
            bcsub(BudgetPool::cap(), Allocator::activeSum(), 0),
            'the reserve is real, unspent headroom under the guard'
        );
    }

    public function testWithTheReserveOffTheAllocatorFillsTheWholePoolAgain(): void
    {
        // the control for the test above: nothing but the reserve changed
        $this->cfg(BudgetPool::CONFIG_RESERVE, '0');
        $a = $this->mkRun('300');
        $b = $this->mkRun('200');

        Allocator::rebalance('test');
        $this->assertSame('1000', bcadd($this->slice($a), $this->slice($b), 0));
    }

    public function testFundingAnArmIsTheOneRaiseTheReserveDoesNotBind(): void
    {
        // A grid pinned on its own floor (10 levels × 50 = 500) can give the
        // arm nothing, so the raise is funded from pool headroom alone. The
        // reserve stops the machine PACKING the pool between legs (rebalance,
        // drift, a hold's spread) — not the raise the fleet exists to make:
        // binding it here left the arm at 285 of 350 on prod's shape, the
        // whole reserve carried by the only profit engine (owner decision
        // 2026-09-21; TrendActivator::capacity).
        $this->cfg(TrendActivator::CONFIG_TARGET_SLICE, '500');
        $trend = $this->mkRun('100', 'Trend', 5, 'BTCUSDT', 25);
        $grid = $this->mkRun('500', 'Grid', 10, 'BTCUSDT', 100);

        $this->activate($trend);

        $this->assertSame('500', $this->slice($trend), 'the arm reaches its target');
        $this->assertSame('500', $this->slice($grid), 'no grid was trimmed for it');
        $this->assertNull(BudgetGuard::check(), 'and the guard, on the full cap, is satisfied');
    }

    // ── the reserve never forces a divestment ───────────────────────────

    public function testAFleetAlreadyAboveTheAllocatableCapIsNotDivested(): void
    {
        // prod on the day this shipped: slices sum to the FULL cap and every
        // run is already on its floor. Reaching the reserve would mean selling,
        // so the allocator writes nothing, reloads nothing, and — this is the
        // half that would otherwise be an alert every 15 minutes — reports no
        // shortfall and no exit proposals, because the fleet is inside the cap.
        $a = $this->mkRun('500', 'Grid', 10);
        $b = $this->mkRun('500', 'Grid', 10);

        $res = Allocator::rebalance('cron');

        $this->assertTrue($res['ok'], $res['message']);
        $this->assertSame('500', $this->slice($a), 'a run on its floor is never trimmed for the reserve');
        $this->assertSame('500', $this->slice($b));
        $this->assertSame([], $res['applied']);
        $this->assertSame([], $res['reloaded']);
        $this->assertSame('0', $res['shortfall'], 'a fleet inside the cap is not short of anything');
        $this->assertSame([], $res['proposals'], 'and gets no exit proposals');
        $this->assertNull(BudgetGuard::check());
    }

    public function testCommittedInventoryOutranksTheReserve(): void
    {
        // the floor a run cannot go below includes the quote its inventory is
        // tied up in; the reserve does not get to reach past it
        $held = $this->mkRun('600', 'Grid', 2);      // per-level floor 100
        $this->holdInventory($held, '560', '1');     // 560 committed
        $this->mkRun('400', 'Grid', 2);

        Allocator::rebalance('cron');

        $this->assertSame(1, bccomp($this->slice($held), '559', 0), 'committed quote is untouchable');
    }

    // ── the guard is NOT the reserve ────────────────────────────────────

    public function testBudgetGuardStillAllowsUpToTheFullCap(): void
    {
        $run = $this->mkRun('960');   // above allocatable 950, inside the 1000 cap
        $id = (int) $run->getIdGridRun();

        $this->assertNull(BudgetGuard::check(), 'the reserve must not become a second tripwire');
        $this->assertNull(BudgetGuard::check($id, '1000'), 'a candidate filling the pool exactly is still legal');

        $over = BudgetGuard::check(null, '41');   // 960 + 41 = 1001
        $this->assertNotNull($over, 'past the FULL cap it still refuses');
        $this->assertSame('1000', bcadd($over['budget'], '0', 0), 'and it refuses against the cap, never the allocatable pool');
    }

    // ── it is reported where the pool is reported ───────────────────────

    public function testTheReserveIsSurfacedOnTheDashboardBand(): void
    {
        // the tile keeps showing the full cap (that is what the guard
        // enforces) and names the allocatable part beside it, so the gap
        // does not read as capital the machine forgot to put to work
        $this->mkRun('500');
        $band = \App\Domains\Dashboard\DashboardData::globalBand();
        $this->assertSame('1000', bcadd((string) $band['budget'], '0', 0));
        $this->assertSame('950', bcadd((string) $band['budget_allocatable'], '0', 0));
        $this->assertSame('5', bcadd((string) $band['pool_reserve_pct'], '0', 0));
    }

    // ── helpers ─────────────────────────────────────────────────────────

    private function holdInventory(GridRun $run, string $price, string $qty): void
    {
        $o = new \App\BotOrder();
        $o->setIdGridRun((int) $run->getIdGridRun());
        $o->setClientOrderId(static::uniq('res-o'));
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

    /** TrendRegime::CONFIRM_PASSES confirmed TREND_UP passes, as the cron makes them. */
    private function activate(GridRun $trend): void
    {
        $summaries = static fn (string $symbol): array => [
            '4h' => ['trend' => 'strong_up', 'adx14' => 33.0, 'er20' => 0.6, 'chop14' => 30.0, 'stale' => false],
            '1d' => ['trend' => 'up', 'price' => 100.0, 'ema20' => 90.0, 'ema50' => 95.0, 'stale' => false],
        ];
        for ($i = 0; $i < 2; $i++) {
            FleetSlots::reconcileFromRuns();
            $slot = FleetSlots::byRun((int) $trend->getIdGridRun());
            $this->assertNotNull($slot, 'the trend run fills no slot');
            TrendActivator::pass($slot, null, false, $summaries);
        }
        $this->assertGreaterThan(
            0,
            BotCommandQuery::create()->filterByIdGridRun((int) $trend->getIdGridRun())->filterByCommand('Reload')->count(),
            'the arm should have been activated by now'
        );
    }
}
