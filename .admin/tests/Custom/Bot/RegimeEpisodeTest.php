<?php

namespace Tests\Custom\Bot;

use App\BotEventQuery;
use App\BotOrder;
use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\Engagement;
use App\Domains\Bot\FleetSlots;
use App\Domains\Bot\RegimeEpisodes;
use App\Domains\Bot\TelegramNotifier;
use App\Domains\Bot\TrendActivator;
use App\FleetSlotQuery;
use App\GridRun;
use App\GridRunQuery;
use App\RegimeEpisodeQuery;
use App\TradeCycle;
use Tests\Builder\Support\DbTestCase;

/**
 * The regime-episode ledger and the engagement metric (correction D4).
 *
 * Nothing measured how much of the shared pool was actually working per
 * symbol, nor what a TREND_UP leg captured against simply holding. Every
 * "the arm sat out a leg" case to date (edf3c14, 110bd8c, 524ad30, and the
 * BNB arm parked by hand over 2026-09-15..18) was found by the operator by
 * eye, after the fact. These cases pin the correction: an episode opens on
 * the verdict transition and closes on the way out with its prices, the
 * allocator samples engagement into it, and a symbol that sits under the
 * floor for long enough alerts exactly once per episode — whatever the
 * cause, including a park the operator chose.
 */
class RegimeEpisodeTest extends DbTestCase
{
    /** @var string[] */
    private array $sent = [];
    private TelegramNotifier $notifier;

    protected function setUp(): void
    {
        parent::setUp();
        // RETIRE whatever the dev DB holds, so the fleet-wide sums below are
        // this test's rows and nothing else. Halting is not enough: engagement
        // reads every NON-Done run, because a parked run that still holds
        // inventory belongs in its symbol's numerator, so an ambient Halted
        // row with a bag would move the per-symbol ratios asserted here.
        foreach (GridRunQuery::create()->filterByStatus('Done', \Criteria::NOT_EQUAL)->find() as $r) {
            $r->setStatus('Done');
            $r->save();
        }
        foreach (FleetSlotQuery::create()->find() as $s) {
            $s->delete();
        }
        foreach (RegimeEpisodeQuery::create()->find() as $e) {
            $e->delete();
        }
        $this->cfg('gtbot_shared_budget_quote', '2000');
        // the total's denominator is BudgetPool::cap(); pin it to the fixed
        // budget so it is not whatever the dev wallet happens to be worth
        $this->cfg('gtbot_use_all_funds', '0');
        $this->cfg('gtbot_max_drawdown_pct', '0');
        $this->cfg(TrendActivator::CONFIG_TARGET_SLICE, TrendActivator::TARGET_SLICE);
        $this->cfg(RegimeEpisodes::CONFIG_FLOOR, RegimeEpisodes::DEFAULT_FLOOR_PCT);
        $this->cfg(RegimeEpisodes::CONFIG_IDLE_PASSES, (string) RegimeEpisodes::DEFAULT_IDLE_PASSES);
        $this->sent = [];
        $this->notifier = new TelegramNotifier('t', 'c', function (string $url, array $post): array {
            $this->sent[] = $post['text'] ?? '';
            return ['status' => 200, 'body' => '{"ok":true}'];
        });
    }

    private function cfg(string $key, string $value): void
    {
        $c = ConfigQuery::create()->findOneByConfig($key) ?? (new Config())->setConfig($key);
        $c->setValue($value);
        $c->save();
    }

    private function mkRun(string $symbol, string $budget, string $algo, string $status = 'Live', int $deploy = 100): GridRun
    {
        $r = new GridRun();
        $r->setLabel(static::uniq('re-' . $algo));
        $r->setSymbol($symbol);
        $r->setStatus($status);
        $r->setAlgo($algo);
        $r->setSimulated(true);
        $r->setPLow('50');
        $r->setPHigh('200');
        $r->setNLevels(5);
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualBase');
        $r->setBudgetQuote($budget);
        $r->setDeployPct($deploy);
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid(static::uniq('re'));
        $r->save();
        return $r;
    }

    /** A filled buy — the only thing OrderStore::investedQuote() counts. */
    private function buy(GridRun $run, string $price, string $qty): void
    {
        $o = new BotOrder();
        $o->setIdGridRun((int) $run->getIdGridRun());
        $o->setClientOrderId(static::uniq('cid'));
        $o->setLevelIdx(0);
        $o->setSide('Buy');
        $o->setState('Filled');
        $o->setPrice($price);
        $o->setQty($qty);
        $o->setFilledQty($qty);
        $o->setSimulated(true);
        $o->save();
    }

    private function summaries(string $shape, float $price): callable
    {
        $map = [
            'TREND_UP' => ['4h' => ['trend' => 'strong_up', 'adx14' => 33.0, 'er20' => 0.6, 'chop14' => 30.0, 'stale' => false],
                           '1d' => ['trend' => 'up', 'price' => $price, 'ema20' => $price * 0.9, 'ema50' => $price * 0.95, 'stale' => false]],
            'MIXED' => ['4h' => ['trend' => 'up', 'adx14' => 22.0, 'er20' => 0.1, 'chop14' => 30.0, 'stale' => false],
                        '1d' => ['trend' => 'down', 'price' => $price, 'ema20' => $price * 1.1, 'ema50' => $price * 1.2, 'stale' => false]],
        ];
        return static fn (string $symbol): array => $map[$shape];
    }

    private function pass(string $symbol, string $shape, float $price): array
    {
        $slot = FleetSlots::find($symbol);
        $this->assertNotNull($slot, "no slot for $symbol");
        return TrendActivator::pass($slot, $this->notifier, false, $this->summaries($shape, $price));
    }

    /** One engagement payload shaped like Engagement::compute() output. */
    private function engagement(string $symbol, string $pct): array
    {
        return [
            'cap' => '0.00',
            'allocated' => '0.00',
            'headroom' => '0.00',
            'total' => ['invested' => '0.00', 'slice' => '0.00', 'pct' => $pct, 'cause' => null],
            'per_symbol' => [$symbol => ['invested' => '0.00', 'slice' => '0.00', 'pct' => $pct, 'cause' => null]],
        ];
    }

    private function alerts(): int
    {
        return BotEventQuery::create()->filterByKind(RegimeEpisodes::KIND_IDLE)->count();
    }

    // ── 5b: open / close ────────────────────────────────────────────────

    public function testEpisodeOpensOnTheTrendUpTransitionAndClosesOnExit(): void
    {
        $run = $this->mkRun('BTCUSDT', '500', 'Trend');
        FleetSlots::reconcileFromRuns();

        $this->pass('BTCUSDT', 'MIXED', 100.0);
        $this->assertSame(0, RegimeEpisodeQuery::create()->count(), 'no episode outside TREND_UP');

        $this->pass('BTCUSDT', 'TREND_UP', 100.0);
        $ep = RegimeEpisodeQuery::create()->findOne();
        $this->assertNotNull($ep, 'entering TREND_UP opens an episode');
        $this->assertSame('BTCUSDT', (string) $ep->getSymbol());
        $this->assertSame('TREND_UP', (string) $ep->getVerdict());
        $this->assertSame('Trend', (string) $ep->getAlgo());
        $this->assertNull($ep->getClosedAt());
        $this->assertSame(0, bccomp('100', (string) $ep->getPriceOpen(), 8));
        $this->assertSame((int) $run->getIdGridRun(), (int) $ep->getIdGridRun());
        $this->assertSame((int) FleetSlots::find('BTCUSDT')->getIdFleetSlot(), (int) $ep->getIdFleetSlot());

        // a second TREND_UP pass does not open a second one
        $this->pass('BTCUSDT', 'TREND_UP', 105.0);
        $this->assertSame(1, RegimeEpisodeQuery::create()->count(), 'one open episode per slot');

        // realized inside the episode, and an open position to mark
        $c = new TradeCycle();
        $c->setIdGridRun((int) $run->getIdGridRun());
        $c->setLevelIdx(0);
        $c->setBuyPrice('100');
        $c->setSellPrice('105');
        $c->setQty('1');
        $c->setRealizedPnl('5');
        $c->setFeesTotal('0');
        $c->setSimulated(true);
        $c->save();
        $run->setEngineState(json_encode(['qty' => '1', 'entry' => '100']));
        $run->save();

        $this->pass('BTCUSDT', 'MIXED', 110.0);
        $ep = RegimeEpisodeQuery::create()->findOne();
        $this->assertNotNull($ep->getClosedAt(), 'leaving TREND_UP closes it');
        $this->assertSame(0, bccomp('110', (string) $ep->getPriceClose(), 8));
        $this->assertSame(0, bccomp('5', (string) $ep->getRealized(), 8), 'realized since open');
        // 1 × (110 − 100)
        $this->assertSame(0, bccomp('10', (string) $ep->getMtmClose(), 8), 'MTM of the open position at close');
        // (110 − 100) / 100
        $this->assertSame(0, bccomp('10', (string) $ep->getHodlPct(), 4));
        // (5 + 10) / (500 × 0.10) = 30 %
        $this->assertSame(0, bccomp('30', (string) $ep->getCapturedPct(), 4));
    }

    public function testAnEpisodeThatClosedLowerHasNoHodlToCapture(): void
    {
        $this->mkRun('BTCUSDT', '500', 'Trend');
        FleetSlots::reconcileFromRuns();
        $this->pass('BTCUSDT', 'TREND_UP', 100.0);
        $this->pass('BTCUSDT', 'MIXED', 90.0);

        $ep = RegimeEpisodeQuery::create()->findOne();
        $this->assertSame(0, bccomp('-10', (string) $ep->getHodlPct(), 4), 'the negative move is still journaled');
        $this->assertNull($ep->getCapturedPct(), 'nothing to capture when HODL lost money');
    }

    public function testAnEpisodeOpensForASlotWithNoRunAtAll(): void
    {
        // BNB's shape: grids alive, no trend arm — the slot is still declared
        $this->mkRun('BNBUSDT', '300', 'Grid');
        FleetSlots::reconcileFromRuns();
        $this->pass('BNBUSDT', 'TREND_UP', 100.0);

        $ep = RegimeEpisodeQuery::create()->findOne();
        $this->assertNotNull($ep, 'a symbol with no arm still gets its episode journaled');
        $this->assertSame('BNBUSDT', (string) $ep->getSymbol());
    }

    // ── 5b: adopting a leg that was already running ─────────────────────

    public function testALegAlreadyRunningAtDeployIsAdoptedOnTheNextPass(): void
    {
        // the shape prod is in as this ships: the slot has been TREND_UP for
        // hours (Task 3 stamped episode_started_at) and no ledger row exists
        $this->mkRun('BTCUSDT', '500', 'Trend');
        FleetSlots::reconcileFromRuns();
        $since = date('Y-m-d H:i:s', time() - 3 * 3600);
        $slot = FleetSlots::find('BTCUSDT');
        $slot->setLastVerdict('TREND_UP');
        $slot->setEpisodeStartedAt($since);
        $slot->save();
        $this->assertSame(0, RegimeEpisodeQuery::create()->count());

        $this->pass('BTCUSDT', 'TREND_UP', 100.0);
        $ep = RegimeEpisodeQuery::create()->findOne();
        $this->assertNotNull($ep, 'the running leg is adopted rather than ignored');
        $this->assertSame($since, $ep->getOpenedAt('Y-m-d H:i:s'), 'opened_at is the real start of the leg');
        $this->assertSame('TREND_UP', (string) $ep->getVerdict());
        $this->assertNull($ep->getClosedAt());
        // a backfilled anchor is a CURRENT mark, not the price the leg began at
        $this->assertSame(0, bccomp('100', (string) $ep->getPriceOpen(), 8));
    }

    public function testAdoptingALegIsIdempotent(): void
    {
        $this->mkRun('BTCUSDT', '500', 'Trend');
        FleetSlots::reconcileFromRuns();
        $since = date('Y-m-d H:i:s', time() - 3 * 3600);
        $slot = FleetSlots::find('BTCUSDT');
        $slot->setLastVerdict('TREND_UP');
        $slot->setEpisodeStartedAt($since);
        $slot->save();

        $this->pass('BTCUSDT', 'TREND_UP', 100.0);
        $this->pass('BTCUSDT', 'TREND_UP', 105.0);
        $this->pass('BTCUSDT', 'TREND_UP', 108.0);
        $this->assertSame(1, RegimeEpisodeQuery::create()->count(), 'one row for one leg, however many passes');
        $this->assertSame($since, RegimeEpisodeQuery::create()->findOne()->getOpenedAt('Y-m-d H:i:s'));
    }

    public function testAnEpisodeOpenedNormallyIsNotTouchedByTheBackfill(): void
    {
        $this->mkRun('BTCUSDT', '500', 'Trend');
        FleetSlots::reconcileFromRuns();
        $this->pass('BTCUSDT', 'TREND_UP', 100.0);
        $opened = RegimeEpisodeQuery::create()->findOne()->getOpenedAt('Y-m-d H:i:s');

        $this->pass('BTCUSDT', 'TREND_UP', 120.0);
        $this->assertSame(1, RegimeEpisodeQuery::create()->count());
        $ep = RegimeEpisodeQuery::create()->findOne();
        $this->assertSame($opened, $ep->getOpenedAt('Y-m-d H:i:s'), 'the open row is left exactly as it was');
        $this->assertSame(0, bccomp('100', (string) $ep->getPriceOpen(), 8), 'and keeps its own anchor');
    }

    public function testAnAdoptedLegThatEndsOnTheSamePassIsStillClosedAndScored(): void
    {
        // the backfill runs before the transitions, so a leg that was already
        // running and ends on the first pass after deploy is opened and closed
        // rather than lost
        $this->mkRun('BTCUSDT', '500', 'Trend');
        FleetSlots::reconcileFromRuns();
        $slot = FleetSlots::find('BTCUSDT');
        $slot->setLastVerdict('TREND_UP');
        $slot->setEpisodeStartedAt(date('Y-m-d H:i:s', time() - 3 * 3600));
        $slot->save();

        $this->pass('BTCUSDT', 'MIXED', 110.0);
        $ep = RegimeEpisodeQuery::create()->findOne();
        $this->assertNotNull($ep);
        $this->assertNotNull($ep->getClosedAt(), 'opened by the backfill, closed by the exit, in one pass');
        $this->assertSame(0, bccomp('110', (string) $ep->getPriceClose(), 8));
        $this->assertNull(FleetSlots::find('BTCUSDT')->getEpisodeStartedAt());
    }

    public function testASlotWithNoEpisodeStampIsNotBackfilled(): void
    {
        $this->mkRun('BTCUSDT', '500', 'Trend');
        FleetSlots::reconcileFromRuns();
        $this->assertNull(RegimeEpisodes::backfill(FleetSlots::find('BTCUSDT'), '100'));
        $this->assertSame(0, RegimeEpisodeQuery::create()->count());
    }

    // ── 5c: engagement ──────────────────────────────────────────────────

    public function testEngagementMathOverTwoRunsOfOneSymbolAndOneOfAnother(): void
    {
        $a = $this->mkRun('BTCUSDT', '400', 'Trend');
        $b = $this->mkRun('BTCUSDT', '200', 'Grid');
        $this->mkRun('BNBUSDT', '400', 'Grid');
        $this->buy($a, '100', '1');    // 100 invested
        $this->buy($b, '50', '1');     //  50 invested
        // BNB is fully idle

        $e = Engagement::pool();
        $this->assertSame(0, bccomp('25', $e['per_symbol']['BTCUSDT']['pct'], 4), '150 / 600 allocated');
        $this->assertSame(0, bccomp('0', $e['per_symbol']['BNBUSDT']['pct'], 4));
        // the total divides by the CAP, not by the slices: 400 of the 2000
        // pool is not claimed by anyone and has to show as unengaged
        $this->assertSame(0, bccomp('7.5', $e['total']['pct'], 4), '150 / cap 2000');
        $this->assertSame(0, bccomp('150', $e['total']['invested'], 2));
        $this->assertSame(0, bccomp('2000', $e['total']['slice'], 2));
        // and the payload reconciles the two denominators
        $this->assertSame(0, bccomp('2000', $e['cap'], 2));
        $this->assertSame(0, bccomp('1000', $e['allocated'], 2), 'Σ active slices');
        $this->assertSame(0, bccomp('1000', $e['headroom'], 2), 'cap − allocated');
    }

    public function testOnlyActiveRunsChargeThePoolButTheirInventoryStillCounts(): void
    {
        // The defect: a Halted/Draft/Retiring slice is re-lent by the
        // allocator, so counting it as declared capital diluted the ratio and
        // could fire a false opportunity_idle. Its INVENTORY is still real
        // money in the market for that symbol, so it stays in the numerator.
        $live = $this->mkRun('BTCUSDT', '400', 'Grid');
        $this->buy($live, '100', '1');
        $parked = $this->mkRun('BTCUSDT', '600', 'Trend', 'Halted');
        $this->buy($parked, '50', '1');

        $e = Engagement::pool();
        $btc = $e['per_symbol']['BTCUSDT'];
        $this->assertSame(0, bccomp('400', $btc['slice'], 2), 'the Halted slice declares nothing');
        $this->assertSame(0, bccomp('150', $btc['invested'], 2), 'its bag is still in the market');
        $this->assertSame(0, bccomp('37.5', $btc['pct'], 4), '150 / 400');
        $this->assertSame(0, bccomp('400', $e['allocated'], 2));
        // the total counts what the runs the cap pays for put to work
        $this->assertSame(0, bccomp('100', $e['total']['invested'], 2));
        $this->assertSame(0, bccomp('5', $e['total']['pct'], 4), '100 / cap 2000');
    }

    public function testTheAllocatorJournalsOnePoolEngagementEventPerPass(): void
    {
        $host = $this->mkRun('BTCUSDT', '400', 'Trend');
        $this->buy($host, '100', '1');

        $e = Engagement::pool();
        Engagement::journal(new \App\Domains\Bot\EventLog((int) $host->getIdGridRun(), false), $e);

        $row = BotEventQuery::create()->filterByKind(Engagement::KIND)->findOne();
        $this->assertNotNull($row, 'the pass is journaled on the host run');
        $this->assertSame('Info', (string) $row->getLevel());
        $payload = json_decode((string) $row->getPayload(), true);
        $this->assertSame(0, bccomp('5', $payload['total']['pct'], 4), '100 / cap 2000');
        $this->assertSame(0, bccomp('25', $payload['per_symbol']['BTCUSDT']['pct'], 4), '100 / 400 allocated');
        $this->assertSame(0, bccomp('2000', $payload['cap'], 2));
        $this->assertSame(0, bccomp('400', $payload['allocated'], 2));
        $this->assertSame(0, bccomp('1600', $payload['headroom'], 2));
        // the operator reads the message, not the payload: both denominators
        // are named in it
        $this->assertStringContainsString('of cap 2000.00', (string) $row->getMessage());
        $this->assertStringContainsString('allocated', (string) $row->getMessage());
    }

    public function testASymbolWithNoRunsIsZeroEngagedRatherThanUndefined(): void
    {
        $e = Engagement::compute([['symbol' => 'BTCUSDT', 'invested' => '0', 'slice' => '0', 'active' => true]], '1000');
        $this->assertSame(0, bccomp('0', $e['per_symbol']['BTCUSDT']['pct'], 4));
        $this->assertSame(0, bccomp('0', $e['total']['pct'], 4));
        $this->assertSame(Engagement::CAUSE_EMPTY, $e['per_symbol']['BTCUSDT']['cause'], 'a zero denominator names its cause');

        // a symbol whose only run is parked declares nothing at all: 0%, and
        // the row says which kind of zero it is
        $e = Engagement::compute([['symbol' => 'BNBUSDT', 'invested' => '0', 'slice' => '400', 'active' => false]], '1000');
        $this->assertSame(0, bccomp('0', $e['per_symbol']['BNBUSDT']['pct'], 4));
        $this->assertSame(Engagement::CAUSE_EMPTY, $e['per_symbol']['BNBUSDT']['cause']);
        $this->assertSame(0, bccomp('0', $e['allocated'], 2));
        $this->assertSame(0, bccomp('1000', $e['headroom'], 2));
    }

    public function testAParkedSymbolStillHoldingCoinsSaysSoRatherThanSlotEmpty(): void
    {
        // Same empty denominator, a very different fact: every run holding
        // BNB is parked, so the slice went back to the pool and the coins did
        // not. "slot empty" would read as "nothing here", which is the one
        // thing that is not true.
        $parked = $this->mkRun('BNBUSDT', '400', 'Trend', 'Halted');
        $this->buy($parked, '100', '1.5');

        $e = Engagement::pool();
        $bnb = $e['per_symbol']['BNBUSDT'];
        $this->assertSame(Engagement::CAUSE_PARKED, $bnb['cause']);
        $this->assertSame(0, bccomp('150', $bnb['invested'], 2), 'the coins are still counted');
        $this->assertSame(0, bccomp('0', $bnb['slice'], 2));
        // pct stays 0: the capital genuinely is not working, and
        // engaged_pct_tw must not be told otherwise. Only the word changes.
        $this->assertSame(0, bccomp('0', $bnb['pct'], 4));
        $this->assertStringContainsString('parked — inventory only', Engagement::describe($e));

        // ...and an empty symbol still says slot empty
        $this->mkRun('BTCUSDT', '0', 'Grid', 'Halted');
        $btc = Engagement::pool()['per_symbol']['BTCUSDT'];
        $this->assertSame(Engagement::CAUSE_EMPTY, $btc['cause']);
    }

    public function testAnOvercommittedPoolShowsANegativeHeadroom(): void
    {
        $e = Engagement::compute([['symbol' => 'BTCUSDT', 'invested' => '0', 'slice' => '1200', 'active' => true]], '1000');
        $this->assertSame(0, bccomp('-200', $e['headroom'], 2), 'the overcommit is visible in the same row');
    }

    public function testTheTimeWeightedAverageIsARunningMeanOverTheSamples(): void
    {
        $this->mkRun('BTCUSDT', '500', 'Trend');
        FleetSlots::reconcileFromRuns();
        $this->pass('BTCUSDT', 'TREND_UP', 100.0);

        foreach (['20', '40', '60'] as $pct) {
            RegimeEpisodes::sample($this->engagement('BTCUSDT', $pct), $this->notifier);
        }
        $ep = RegimeEpisodeQuery::create()->findOne();
        $this->assertSame(3, (int) $ep->getSamples());
        $this->assertSame(0, bccomp('40', (string) $ep->getEngagedPctTw(), 4));
    }

    public function testSamplesOnlyLandOnOpenEpisodes(): void
    {
        $this->mkRun('BTCUSDT', '500', 'Trend');
        FleetSlots::reconcileFromRuns();
        $this->pass('BTCUSDT', 'TREND_UP', 100.0);
        RegimeEpisodes::sample($this->engagement('BTCUSDT', '50'), $this->notifier);
        $this->pass('BTCUSDT', 'MIXED', 100.0);
        RegimeEpisodes::sample($this->engagement('BTCUSDT', '90'), $this->notifier);

        $ep = RegimeEpisodeQuery::create()->findOne();
        $this->assertSame(1, (int) $ep->getSamples(), 'a closed episode takes no more samples');
        $this->assertSame(0, bccomp('50', (string) $ep->getEngagedPctTw(), 4));
    }

    // ── 5d: the alert ───────────────────────────────────────────────────

    public function testTheIdleAlertFiresOnTheEighthSubFloorSampleAndNotTheNinth(): void
    {
        $this->mkRun('BTCUSDT', '500', 'Trend');
        FleetSlots::reconcileFromRuns();
        $this->pass('BTCUSDT', 'TREND_UP', 100.0);

        for ($i = 1; $i <= 7; $i++) {
            RegimeEpisodes::sample($this->engagement('BTCUSDT', '10'), $this->notifier);
            $this->assertSame(0, $this->alerts(), "sample $i is still inside the grace window");
        }
        RegimeEpisodes::sample($this->engagement('BTCUSDT', '10'), $this->notifier);
        $this->assertSame(1, $this->alerts(), 'the 8th consecutive sub-floor sample speaks');

        RegimeEpisodes::sample($this->engagement('BTCUSDT', '10'), $this->notifier);
        $this->assertSame(1, $this->alerts(), 'exactly once per episode');

        $e = BotEventQuery::create()->filterByKind(RegimeEpisodes::KIND_IDLE)->findOne();
        $this->assertStringContainsString('BTC', (string) $e->getMessage());
        $this->assertStringContainsString('40', (string) $e->getMessage(), 'the floor is named');
        $this->assertNotEmpty(json_decode((string) $e->getPayload(), true)['causes']);
        $this->assertNotNull(RegimeEpisodeQuery::create()->findOne()->getIdleAlertedAt());
    }

    public function testAnEngagedSymbolResetsTheConsecutiveCount(): void
    {
        $this->mkRun('BTCUSDT', '500', 'Trend');
        FleetSlots::reconcileFromRuns();
        $this->pass('BTCUSDT', 'TREND_UP', 100.0);

        for ($i = 1; $i <= 7; $i++) {
            RegimeEpisodes::sample($this->engagement('BTCUSDT', '10'), $this->notifier);
        }
        RegimeEpisodes::sample($this->engagement('BTCUSDT', '80'), $this->notifier);
        RegimeEpisodes::sample($this->engagement('BTCUSDT', '10'), $this->notifier);
        $this->assertSame(0, $this->alerts(), 'the streak restarts after an engaged pass');
    }

    public function testADeliberatelyHaltedArmStillGetsTheAlertOnceWithItsCause(): void
    {
        $this->mkRun('BTCUSDT', '500', 'Trend', 'Halted');
        FleetSlots::reconcileFromRuns();
        $this->pass('BTCUSDT', 'TREND_UP', 100.0);

        for ($i = 1; $i <= 8; $i++) {
            RegimeEpisodes::sample($this->engagement('BTCUSDT', '0'), $this->notifier);
        }
        $this->assertSame(1, $this->alerts(), 'a park the operator chose is still an idle pool');
        $e = BotEventQuery::create()->filterByKind(RegimeEpisodes::KIND_IDLE)->findOne();
        $this->assertContains('run not Live (Halted)', json_decode((string) $e->getPayload(), true)['causes']);
        $this->assertNotEmpty($this->sent, 'the alert fans out to Telegram');
    }

    public function testAnEmptySlotIsNamedAsTheCause(): void
    {
        $this->mkRun('BNBUSDT', '300', 'Grid');
        FleetSlots::reconcileFromRuns();
        $slot = FleetSlots::find('BNBUSDT');
        $this->assertContains('slot empty', RegimeEpisodes::causes($slot));
    }

    public function testAKilledRunIsNamedAsACause(): void
    {
        $run = $this->mkRun('BTCUSDT', '500', 'Trend');
        $run->setKillSwitch(true);
        $run->save();
        FleetSlots::reconcileFromRuns();
        $this->assertContains('run killed', RegimeEpisodes::causes(FleetSlots::find('BTCUSDT')));
    }

    // ── D1: the cause must be the real one ──────────────────────────────

    /** A summaries provider shaped like MarketStore::summaries(). */
    private function tape(string $trend4h, float $adx4h): callable
    {
        return static fn (string $symbol): array => [
            '4h' => ['trend' => $trend4h, 'adx14' => $adx4h, 'stale' => false],
        ];
    }

    /** @return string[] the causes mentioning the hostile cap */
    private function hostileCauses(array $causes): array
    {
        return array_values(array_filter($causes, static fn (string $c): bool => str_contains($c, 'hostile')));
    }

    /** A flat trend arm inside its re-entry cooldown: the arm's OWN state. */
    private function stoppedOutArm(int $agoSeconds, int $cooldownBars = 3): \App\GridRun
    {
        $run = $this->mkRun('BTCUSDT', '500', 'Trend');
        $run->setTrendTf('1h');
        $run->setReentryCooldown($cooldownBars);
        $run->setEngineState(json_encode([
            'entry' => null,
            'qty' => null,
            'stop_out_at' => date('Y-m-d H:i:s', time() - $agoSeconds),
        ]));
        $run->save();
        FleetSlots::reconcileFromRuns();
        return $run;
    }

    public function testAnArmInsideItsReentryCooldownSaysSoFirst(): void
    {
        // prod 2026-09-21: the trailing stop fired 2 h before the alert, the
        // 3 × 1h cooldown still had an hour to run, and the alert blamed a
        // grid's deploy_pct instead.
        $this->stoppedOutArm(2 * 3600);
        $causes = RegimeEpisodes::causes(FleetSlots::find('BTCUSDT'));
        $this->assertNotSame([], $causes, 'a stopped-out arm is a visible cause');
        $this->assertStringContainsString('stopped out at', $causes[0], "the arm's own state comes first");
        $this->assertStringContainsString('re-entry cooldown until', $causes[0]);
        $this->assertStringContainsString(date('Y-m-d H:i', time() + 3600), $causes[0], 'the cooldown ends 3 × 1h after the stop-out');
    }

    public function testAnArmPastItsCooldownIsWaitingForASignalNotForTheClock(): void
    {
        $this->stoppedOutArm(4 * 3600);
        $causes = RegimeEpisodes::causes(FleetSlots::find('BTCUSDT'));
        $this->assertStringContainsString('flat since', $causes[0]);
        $this->assertStringContainsString('cooldown elapsed, waiting for an entry signal', $causes[0]);
    }

    public function testAnArmThatNeverStoppedOutIsNotGivenACooldownItDoesNotHave(): void
    {
        $run = $this->mkRun('BTCUSDT', '500', 'Trend');
        $run->setReentryCooldown(3);
        $run->setEngineState(json_encode(['entry' => null, 'qty' => null]));
        $run->save();
        FleetSlots::reconcileFromRuns();
        $this->assertSame(
            [],
            array_values(array_filter(
                RegimeEpisodes::causes(FleetSlots::find('BTCUSDT')),
                static fn (string $c): bool => str_contains($c, 'stopped out') || str_contains($c, 'cooldown')
            )),
            'an arm that never stopped out has no cooldown to blame'
        );
    }

    public function testAGridAtTheCapIsBlamedOnlyWhileTheTapeIsReallyHostile(): void
    {
        $this->mkRun('BTCUSDT', '500', 'Trend');
        $this->mkRun('BTCUSDT', '300', 'Grid', 'Live', \App\Domains\Bot\RegimeGate::MAX_HOSTILE_DEPLOY_PCT);
        FleetSlots::reconcileFromRuns();
        $slot = FleetSlots::find('BTCUSDT');

        $hostile = $this->hostileCauses(RegimeEpisodes::causes($slot, $this->tape('strong_down', 33.0)));
        $this->assertCount(1, $hostile, 'a hostile 4h tape IS what the gate would apply');
        $this->assertStringContainsString('strong_down', $hostile[0], 'the reading is named');

        $this->assertSame(
            [],
            $this->hostileCauses(RegimeEpisodes::causes($slot, $this->tape('up', 18.0))),
            'a grid sitting at 25 in a calm tape was not capped by the gate'
        );
    }

    public function testAGridLeftAtTheCapByAnOldRefitSaysTheTapeHasMovedOn(): void
    {
        $this->mkRun('BTCUSDT', '500', 'Trend');
        $grid = $this->mkRun('BTCUSDT', '300', 'Grid', 'Live', \App\Domains\Bot\RegimeGate::MAX_HOSTILE_DEPLOY_PCT);
        FleetSlots::reconcileFromRuns();
        $trail = (new \App\Domains\Bot\ClampTrail())->add('regime_gate', 100, \App\Domains\Bot\RegimeGate::MAX_HOSTILE_DEPLOY_PCT, 'hostile regime');
        \App\Domains\Bot\DecisionScorer::record(
            $grid,
            'Claude',
            (string) $grid->getPLow(),
            (string) $grid->getPHigh(),
            (int) $grid->getNLevels(),
            'refit under a hostile tape',
            '100',
            \App\Domains\Bot\RegimeGate::MAX_HOSTILE_DEPLOY_PCT,
            ['clamps' => $trail]
        );

        $hostile = $this->hostileCauses(RegimeEpisodes::causes(FleetSlots::find('BTCUSDT'), $this->tape('up', 18.0)));
        $this->assertCount(1, $hostile, 'the gate really did cap this grid — say so, with the truth about the tape');
        $this->assertStringContainsString('no longer hostile', $hostile[0]);
    }

    // ── 5e: surfaces ────────────────────────────────────────────────────

    public function testOpenEpisodesAreOnTheRoutineBrief(): void
    {
        $this->mkRun('BTCUSDT', '500', 'Trend');
        FleetSlots::reconcileFromRuns();
        $this->pass('BTCUSDT', 'TREND_UP', 100.0);
        RegimeEpisodes::sample($this->engagement('BTCUSDT', '30'), $this->notifier);

        $open = RegimeEpisodes::brief();
        $this->assertCount(1, $open);
        $this->assertSame('BTCUSDT', $open[0]['symbol']);
        $this->assertNotNull($open[0]['since']);
        $this->assertSame(1, $open[0]['samples']);
        $this->assertSame(0, bccomp('30', (string) $open[0]['engaged_pct_tw'], 4));
    }

    public function testClosedEpisodesAreOnThePnlReport(): void
    {
        $this->mkRun('BTCUSDT', '500', 'Trend');
        FleetSlots::reconcileFromRuns();
        $this->pass('BTCUSDT', 'TREND_UP', 100.0);
        $this->pass('BTCUSDT', 'MIXED', 110.0);

        $closed = RegimeEpisodes::recentClosed(10);
        $this->assertCount(1, $closed);
        $this->assertSame('BTCUSDT', $closed[0]['symbol']);
        $this->assertArrayHasKey('captured_pct', $closed[0]);
        $this->assertSame(0, bccomp('10', (string) $closed[0]['hodl_pct'], 4));
    }

    public function testTheRoutineBriefToolCarriesSharedEpisodes(): void
    {
        $this->mkRun('BTCUSDT', '500', 'Trend');
        FleetSlots::reconcileFromRuns();
        $this->pass('BTCUSDT', 'TREND_UP', 100.0);
        RegimeEpisodes::sample($this->engagement('BTCUSDT', '30'), $this->notifier);

        $s = $this->summaries('TREND_UP', 100.0);
        $brief = (new \App\Mcp\Tools\GtbotRoutineBriefTool(
            static fn (string $symbol): array => $s($symbol),
            static fn (string $symbol, string $tf): array => [],
            static fn (string $symbol): ?string => '100'
        ))->build(null);

        $this->assertArrayHasKey('episodes', $brief['shared']);
        $this->assertSame('BTCUSDT', $brief['shared']['episodes'][0]['symbol']);
        $this->assertSame(1, $brief['shared']['episodes'][0]['samples']);
        $this->assertSame(0, bccomp('30', (string) $brief['shared']['episodes'][0]['engaged_pct_tw'], 4));
    }

    public function testThePnlReportToolCarriesClosedEpisodes(): void
    {
        $run = $this->mkRun('BTCUSDT', '500', 'Trend');
        FleetSlots::reconcileFromRuns();
        $this->pass('BTCUSDT', 'TREND_UP', 100.0);
        $this->pass('BTCUSDT', 'MIXED', 110.0);

        $out = (new \App\Mcp\Tools\GtbotPnlReportTool())->handle(
            ['run' => (int) $run->getIdGridRun(), 'days' => 7],
            $this->createMock(\ApiGoat\Sessions\AuthySession::class)
        );
        $payload = json_decode((string) ($out['content'][0]['text'] ?? ''), true);
        $this->assertArrayHasKey('episodes', $payload);
        $this->assertSame('BTCUSDT', $payload['episodes'][0]['symbol']);
        $this->assertArrayHasKey('captured_pct', $payload['episodes'][0]);
        $this->assertSame(0, bccomp('10', (string) $payload['episodes'][0]['hodl_pct'], 4));
    }
}
