<?php

namespace Tests\Custom\Bot;

use App\BotOrder;
use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\Allocator;
use App\Domains\Bot\AllocScore;
use App\Domains\Bot\BudgetGuard;
use App\GridRun;
use App\GridRunQuery;
use App\TradeCycle;
use Tests\Builder\Support\DbTestCase;

/**
 * Profit drift: move a little budget from the worse earners to the better ones.
 *
 * THE RULE THAT MAKES IT SAFE TO RUN UNATTENDED: a donor may only give up
 * capital that is IDLE inside its slice (capped at slice − max(floor,
 * committed)). Drift can therefore never ask a run to divest, which keeps it
 * entirely outside the never-sell-at-a-loss policy. testAFullyInvestedDonor…
 * is the test that pins that.
 */
class AllocDriftTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (GridRunQuery::create()->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)->find() as $r) {
            $r->setStatus('Halted');
            $r->save();
        }
        $this->setConfig('gtbot_shared_budget_quote', '2000');
        $this->setConfig('gtbot_use_all_funds', '0');
        $this->setConfig('gtbot_max_drawdown_pct', '0');
        $this->setConfig('gtbot_alloc_window_days', '14');
        $this->setConfig('gtbot_alloc_drift_pct', '5');
        $this->setConfig('gtbot_alloc_drift_max', '50');
        $this->setConfig('gtbot_alloc_max_share_pct', '50');
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

    private function mkRun(string $budget, string $mode = 'Auto'): GridRun
    {
        $r = new GridRun();
        $r->setLabel(static::uniq('drift'));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setAlgo('Grid');
        $r->setAllocMode($mode);
        $r->setSimulated(true);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(2);   // keeps the per-level floor small
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote($budget);
        $r->setDeployPct(100);
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('2000');
        $r->setMaxOrderQuote('500');
        $r->setDailyLossLimitQuote('100');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(30);
        $r->setRunUid(static::uniq('drift'));
        $r->setLastTickAt(date('Y-m-d H:i:s'));
        $r->setLastPrice('150');
        $r->save();
        return $r;
    }

    /** $pnl per cycle, MIN_CYCLES of them, all on one day */
    private function earn(GridRun $run, string $pnl): void
    {
        for ($i = 0; $i < AllocScore::MIN_CYCLES; $i++) {
            $c = new TradeCycle();
            $c->setIdGridRun((int) $run->getIdGridRun());
            $c->setLevelIdx(1);
            $c->setBuyPrice('100');
            $c->setSellPrice('110');
            $c->setQty('0.1');
            $c->setRealizedPnl($pnl);
            $c->setFeesTotal('0.01');
            $c->setSimulated(true);
            $c->save();
            $st = \Propel::getConnection()->prepare('UPDATE trade_cycle SET date_creation = ? WHERE id_trade_cycle = ?');
            $st->execute([date('Y-m-d H:i:s', strtotime('-1 day')), (int) $c->getIdTradeCycle()]);
        }
    }

    private function holdInventory(GridRun $run, string $price, string $qty): void
    {
        $o = new BotOrder();
        $o->setIdGridRun((int) $run->getIdGridRun());
        $o->setClientOrderId(static::uniq('drift-o'));
        $o->setLevelIdx(1);
        $o->setSide('Buy');
        $o->setState('Filled');
        $o->setPrice($price);
        $o->setQty($qty);
        $o->setFilledQty($qty);
        $o->setFeePaid('0.1');
        $o->setSimulated(true);
        $o->save();
        \App\BotOrderPeer::clearInstancePool();
    }

    private function slice(GridRun $r): string
    {
        $r->reload();
        return bcadd((string) $r->getBudgetQuote(), '0', 0);
    }

    // ── the core behaviour ──────────────────────────────────────────────

    public function testBudgetMovesFromTheWorseEarnerToTheBetter(): void
    {
        $good = $this->mkRun('500');
        $bad = $this->mkRun('500');
        $this->earn($good, '5');
        $this->earn($bad, '0.1');

        $res = Allocator::drift(true);
        $this->assertTrue($res['ok'], $res['message']);
        $this->assertSame(1, bccomp($res['moved'], '0', 0), 'something should have moved');
        $this->assertSame(1, bccomp($this->slice($good), '500', 0), 'the better earner gains');
        $this->assertSame(-1, bccomp($this->slice($bad), '500', 0), 'the worse earner gives');
    }

    public function testThePoolTotalIsConserved(): void
    {
        $good = $this->mkRun('500');
        $bad = $this->mkRun('500');
        $this->earn($good, '5');
        $this->earn($bad, '0.1');

        Allocator::drift(true);
        $this->assertSame(
            0,
            bccomp('1000', bcadd($this->slice($good), $this->slice($bad), 0), 0),
            'drift moves budget, it does not create or destroy it'
        );
    }

    public function testAFullyInvestedDonorGivesUpNothing(): void
    {
        // the load-bearing rule: drift may only move IDLE capital, so a donor
        // whose slice is entirely committed cannot be asked to sell
        $good = $this->mkRun('500');
        $bad = $this->mkRun('500');
        $this->earn($good, '5');
        $this->earn($bad, '0.1');
        // 600 of inventory less the 100 the earn() cycles already closed out
        // leaves 500 committed — exactly the slice, so nothing is idle
        $this->holdInventory($bad, '600', '1');

        $res = Allocator::drift(true);
        $this->assertSame('500', $this->slice($bad), 'a fully-invested run donates exactly 0');
        $this->assertStringContainsString('no idle capital', $res['message']);
        $this->assertSame('500', $this->slice($good), 'and nothing is handed out either');
    }

    public function testDriftIsCappedByThePerPassPercentage(): void
    {
        $good = $this->mkRun('500');
        $bad = $this->mkRun('500');
        $this->earn($good, '5');
        $this->earn($bad, '0.1');

        // 5% of the 1000 eligible sum = 50
        $res = Allocator::drift(true);
        $this->assertSame(
            -1,
            bccomp($res['moved'], '51', 0),
            'a pass must not exceed gtbot_alloc_drift_pct of the eligible sum'
        );
    }

    public function testTheAbsoluteMaxAlsoCaps(): void
    {
        $this->setConfig('gtbot_alloc_drift_max', '10');
        $good = $this->mkRun('500');
        $bad = $this->mkRun('500');
        $this->earn($good, '5');
        $this->earn($bad, '0.1');

        $res = Allocator::drift(true);
        // a ceiling, not an exact figure — whole-USDT share arithmetic can land
        // a unit or two under it
        $this->assertLessThanOrEqual(0, bccomp($res['moved'], '10', 0), 'gtbot_alloc_drift_max is a hard ceiling');
        $this->assertSame(1, bccomp($res['moved'], '0', 0), 'but something still moved');
    }

    public function testDisabledByDefaultConfig(): void
    {
        $this->setConfig('gtbot_alloc_drift_pct', '0');
        $good = $this->mkRun('500');
        $bad = $this->mkRun('500');
        $this->earn($good, '5');
        $this->earn($bad, '0.1');

        $res = Allocator::drift(true);
        $this->assertStringContainsString('disabled', $res['message']);
        $this->assertSame('500', $this->slice($good));
        $this->assertSame('500', $this->slice($bad));
    }

    public function testOneEligibleRunDriftsNothing(): void
    {
        $only = $this->mkRun('500');
        $this->earn($only, '5');
        $this->mkRun('500');   // no cycles → ineligible

        $res = Allocator::drift(true);
        $this->assertSame('500', $this->slice($only));
        $this->assertStringContainsString('fewer than two', $res['message']);
    }

    public function testRunsInsideTheDeadbandAreLeftAlone(): void
    {
        $a = $this->mkRun('500');
        $b = $this->mkRun('500');
        $this->earn($a, '1');
        $this->earn($b, '1');   // identical scores

        $res = Allocator::drift(true);
        $this->assertStringContainsString('deadband', $res['message']);
        $this->assertSame('500', $this->slice($a));
        $this->assertSame('500', $this->slice($b));
    }

    public function testAFixedRunNeitherGivesNorGets(): void
    {
        $pinned = $this->mkRun('500', 'Fixed');
        $good = $this->mkRun('500');
        $bad = $this->mkRun('500');
        $this->earn($pinned, '0.05');   // worst earner, but pinned
        $this->earn($good, '5');
        $this->earn($bad, '0.1');

        $res = Allocator::drift(true);
        $this->assertSame('500', $this->slice($pinned), 'a pinned slice is untouchable');
        $this->assertArrayHasKey((int) $pinned->getIdGridRun(), $res['skipped']);
    }

    public function testAKilledRunIsSkipped(): void
    {
        $killed = $this->mkRun('500');
        $killed->setKillSwitch(true);
        $killed->save();
        $good = $this->mkRun('500');
        $bad = $this->mkRun('500');
        $this->earn($killed, '0.05');
        $this->earn($good, '5');
        $this->earn($bad, '0.1');

        $res = Allocator::drift(true);
        $this->assertSame('500', $this->slice($killed));
        $this->assertStringContainsString('kill switch', $res['skipped'][(int) $killed->getIdGridRun()]);
    }

    public function testDryRunWritesNothing(): void
    {
        $good = $this->mkRun('500');
        $bad = $this->mkRun('500');
        $this->earn($good, '5');
        $this->earn($bad, '0.1');

        $res = Allocator::drift(false);
        $this->assertStringContainsString('[dry]', $res['message']);
        $this->assertNotSame([], $res['applied'], 'a dry run still reports the plan');
        $this->assertSame('500', $this->slice($good), 'but writes nothing');
        $this->assertSame('500', $this->slice($bad));
    }

    public function testDriftKeepsTheBudgetInvariant(): void
    {
        $good = $this->mkRun('500');
        $bad = $this->mkRun('500');
        $this->earn($good, '5');
        $this->earn($bad, '0.1');

        Allocator::drift(true);
        $this->assertNull(BudgetGuard::check(), 'the pool must never end a pass overcommitted');
    }

    public function testRecipientIsCappedAtTheMaxSingleShare(): void
    {
        $this->setConfig('gtbot_alloc_max_share_pct', '25');   // 25% of 2000 = 500
        $good = $this->mkRun('500');   // already at the ceiling
        $bad = $this->mkRun('500');
        $this->earn($good, '5');
        $this->earn($bad, '0.1');

        $res = Allocator::drift(true);
        $this->assertSame('500', $this->slice($good), 'a run at the ceiling takes no more');
        $this->assertStringContainsString('max single share', $res['message']);
    }
}
