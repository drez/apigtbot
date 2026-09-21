<?php

namespace Tests\Custom\Bot;

use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\AllocScore;
use App\Domains\Bot\BudgetGuard;
use App\GridRun;
use App\GridRunQuery;
use App\TradeCycle;
use Tests\Builder\Support\DbTestCase;

/**
 * The capital-efficiency score the allocator ranks runs by, and the dashboard
 * Allocation tiles show: net realized per active day per 1000 USDT of slice.
 *
 * Deliberately net (trade_cycle.realized_pnl is already gross − fees) and
 * deliberately not cycle count, which would reward fee churn.
 */
class AllocScoreTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (GridRunQuery::create()->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)->find() as $r) {
            $r->setStatus('Halted');
            $r->save();
        }
    }

    private function mkRun(string $budget): GridRun
    {
        $r = new GridRun();
        $r->setLabel(static::uniq('score'));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setAlgo('Grid');
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
        $r->setRunUid(static::uniq('score'));
        $r->save();
        return $r;
    }

    private function cycle(GridRun $run, string $pnl, string $when): void
    {
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
        // date_creation is auto-stamped on save (audit column), so back-dating
        // has to happen after the insert
        $con = \Propel::getConnection();
        $st = $con->prepare('UPDATE trade_cycle SET date_creation = ? WHERE id_trade_cycle = ?');
        $st->execute([$when, (int) $c->getIdTradeCycle()]);
    }

    public function testScoreIsNetPerActiveDayPer1kOfSlice(): void
    {
        $run = $this->mkRun('1000');
        // 10 cycles of +1 across 2 distinct days = net 10 over 2 active days
        for ($i = 0; $i < 5; $i++) {
            $this->cycle($run, '1', date('Y-m-d H:i:s', strtotime('-1 day')));
            $this->cycle($run, '1', date('Y-m-d H:i:s', strtotime('-2 day')));
        }
        $s = AllocScore::score($run, 14);

        $this->assertSame(10, $s['cycles']);
        $this->assertSame(2, $s['active_days']);
        $this->assertSame(0, bccomp('10', $s['net'], 8));
        // 10 / 2 days / 1000 * 1000 = 5
        $this->assertSame(0, bccomp('5', (string) $s['per_1k_day'], 6));
        $this->assertTrue($s['eligible']);
    }

    public function testABiggerSliceEarningTheSameScoresLower(): void
    {
        $small = $this->mkRun('500');
        $big = $this->mkRun('2000');
        for ($i = 0; $i < 10; $i++) {
            $this->cycle($small, '1', date('Y-m-d H:i:s', strtotime('-1 day')));
            $this->cycle($big, '1', date('Y-m-d H:i:s', strtotime('-1 day')));
        }
        $a = AllocScore::score($small, 14);
        $b = AllocScore::score($big, 14);
        $this->assertSame(
            1,
            bccomp((string) $a['per_1k_day'], (string) $b['per_1k_day'], 6),
            'return on capital, not absolute P&L'
        );
    }

    public function testARunUnderMinCyclesIsIneligible(): void
    {
        $run = $this->mkRun('1000');
        $this->cycle($run, '5', date('Y-m-d H:i:s', strtotime('-1 day')));
        $s = AllocScore::score($run, 14);
        $this->assertFalse($s['eligible'], 'one lucky cycle is not evidence of edge');
        $this->assertStringContainsString('needs ' . AllocScore::MIN_CYCLES, (string) $s['why']);
    }

    public function testCyclesOutsideTheWindowAreIgnored(): void
    {
        $run = $this->mkRun('1000');
        for ($i = 0; $i < 10; $i++) {
            $this->cycle($run, '1', date('Y-m-d H:i:s', strtotime('-40 day')));
        }
        $s = AllocScore::score($run, 14);
        $this->assertSame(0, $s['cycles']);
        $this->assertSame(0, bccomp('0', $s['net'], 8));
    }

    public function testALosingRunScoresNegative(): void
    {
        $run = $this->mkRun('1000');
        for ($i = 0; $i < 10; $i++) {
            $this->cycle($run, '-2', date('Y-m-d H:i:s', strtotime('-1 day')));
        }
        $s = AllocScore::score($run, 14);
        $this->assertSame(-1, bccomp((string) $s['per_1k_day'], '0', 6));
    }

    public function testAZeroSliceHasNoScore(): void
    {
        $run = $this->mkRun('0');
        for ($i = 0; $i < 10; $i++) {
            $this->cycle($run, '1', date('Y-m-d H:i:s', strtotime('-1 day')));
        }
        $s = AllocScore::score($run, 14);
        $this->assertNull($s['per_1k_day']);
        $this->assertFalse($s['eligible']);
    }

    public function testWindowDaysComesFromConfigAndDefaultsTo14(): void
    {
        $c = ConfigQuery::create()->findOneByConfig('gtbot_alloc_window_days') ?? (new Config())->setConfig('gtbot_alloc_window_days');
        $c->setValue('7');
        $c->save();
        \App\ConfigPeer::clearInstancePool();
        $this->assertSame(7, AllocScore::windowDays());

        $c->setValue('');
        $c->save();
        \App\ConfigPeer::clearInstancePool();
        $this->assertSame(14, AllocScore::windowDays());
    }
}
