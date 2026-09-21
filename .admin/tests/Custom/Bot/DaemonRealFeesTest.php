<?php

namespace Tests\Custom\Bot;

use App\BotOrderQuery;
use App\Domains\Bot\BudgetGuard;
use App\Domains\Bot\Daemon;
use App\Domains\Bot\EventLog;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\GridRun;
use App\GridRunQuery;
use Tests\Builder\Support\DbTestCase;
use Tests\Custom\Bot\Support\ExchangeSim;

/**
 * What a REAL account does to a fill and the paper gateway cannot show:
 * Binance takes a BUY's commission out of the base asset it delivers (or out
 * of a BNB float in "pay fees in BNB" mode, silently falling back to the base
 * asset when that float is empty). An exit armed for the gross qty is then an
 * order for coins the account does not hold: -2010, nothing on the book, and
 * a machine that believes its inventory is guarded.
 */
class DaemonRealFeesTest extends DbTestCase
{
    private GridRun $run;
    private ExchangeSim $sim;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (GridRunQuery::create()->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)->find() as $r) {
            $r->setStatus('Halted');
            $r->save();
        }
        $dd = \App\ConfigQuery::create()->findOneByConfig('gtbot_max_drawdown_pct')
            ?? (new \App\Config())->setConfig('gtbot_max_drawdown_pct');
        $dd->setValue('0');
        $dd->save();
        $this->run = $this->mkRun('BTCUSDT');
        $this->sim = new ExchangeSim();
        $this->sim->commissionMode = 'native';
        $this->sim->balances = ['USDT' => ['free' => '1000', 'locked' => '0']];
    }

    private function mkRun(string $symbol): GridRun
    {
        $r = new GridRun();
        $r->setLabel(static::uniq('fees'));
        $r->setSymbol($symbol);
        $r->setStatus('Live');
        $r->setSimulated(false);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualBase');
        $r->setBudgetQuote('550'); // 1.0000 base per level
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid(substr(bin2hex(random_bytes(6)), 0, 10));
        $r->save();
        return $r;
    }

    private function daemon(?GridRun $run = null): Daemon
    {
        $run ??= $this->run;
        $gateway = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        return new Daemon($run, $gateway, false, new EventLog((int) $run->getIdGridRun(), false));
    }

    private function filledBuy(): \App\BotOrder
    {
        return BotOrderQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterBySide('Buy')
            ->filterByState('Filled')
            ->findOne();
    }

    private function openSell(): ?array
    {
        foreach ($this->sim->open as $o) {
            if ($o['side'] === 'SELL') {
                return $o;
            }
        }
        return null;
    }

    private function eventKinds(): array
    {
        return array_map(
            fn ($e) => (string) $e->getKind(),
            \App\BotEventQuery::create()->filterByIdGridRun((int) $this->run->getIdGridRun())->find()->getArrayCopy()
        );
    }

    private function fillTheBuyAt125(): void
    {
        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();
        $this->sim->setPrice('124');
        $d->tick();
    }

    public function testABaseAssetCommissionShrinksTheExitToWhatTheAccountHolds(): void
    {
        $this->fillTheBuyAt125();

        // 1.0000 bought, 0.0010 taken as commission: 0.9990 is all there is
        $sell = $this->openSell();
        $this->assertNotNull($sell, 'the exit must be ON THE BOOK, not refused for insufficient balance');
        $this->assertSame(0, bccomp('0.999', $sell['origQty'], 8));
        $buy = $this->filledBuy();
        $this->assertSame(0, bccomp('0.999', (string) $buy->getFilledQty(), 8), 'the ledger holds the net qty');
        $this->assertSame('BTC', (string) $buy->getFeeAsset());
        $this->assertSame(0, bccomp('0.125', (string) $buy->getFeePaid(), 8), '0.001 BTC at 125');
        $this->assertContains('fee_netted', $this->eventKinds());
        $this->assertNotContains('api_error', $this->eventKinds());
    }

    public function testASpareBaseFloatAbsorbsTheCommission(): void
    {
        $this->sim->balances['BTC'] = ['free' => '0.5', 'locked' => '0'];
        $this->fillTheBuyAt125();

        $this->assertSame(0, bccomp('1', $this->openSell()['origQty'], 8), 'the float pays, the level sells whole');
        $this->assertSame(0, bccomp('1', (string) $this->filledBuy()->getFilledQty(), 8));
        $this->assertNotContains('fee_netted', $this->eventKinds());
    }

    public function testAnotherRealRunsInventoryIsNotAFloat(): void
    {
        // a sibling run holds 0.5 BTC behind its own exit — that is inventory,
        // not spare change this run may sell
        $other = $this->mkRun('BTCUSDT');
        $other->setStatus('Halted'); // parked, still holding — and outside the budget sum
        $other->save();
        $o = new \App\BotOrder();
        $o->setIdGridRun((int) $other->getIdGridRun());
        $o->setClientOrderId(static::uniq('cid'));
        $o->setSide('Buy');
        $o->setLevelIdx(0);
        $o->setPrice('100');
        $o->setQty('0.5');
        $o->setFilledQty('0.5');
        $o->setState('Filled');
        $o->setSimulated(false);
        $o->save();
        $this->sim->balances['BTC'] = ['free' => '0.5', 'locked' => '0'];

        $this->fillTheBuyAt125();

        $this->assertSame(0, bccomp('0.999', $this->openSell()['origQty'], 8));
    }

    public function testBnbFeeModeKeepsTheLevelWholeAndPricesTheFeeInQuote(): void
    {
        $this->sim->commissionMode = 'BNB';
        $this->sim->prices['BNBUSDT'] = '600';
        $this->sim->balances['BNB'] = ['free' => '1', 'locked' => '0'];
        $this->fillTheBuyAt125();

        $this->assertSame(0, bccomp('1', $this->openSell()['origQty'], 8));
        $buy = $this->filledBuy();
        $this->assertSame('BNB', (string) $buy->getFeeAsset());
        // 125 notional × 0.1% × 0.75 = 0.09375 USDT, paid as 0.00015625 BNB
        $this->assertSame(0, bccomp('0.09375', (string) $buy->getFeePaid(), 6));
        $this->assertNotContains('fee_netted', $this->eventKinds());
    }

    public function testAnEmptyBnbFloatFallsBackToNettingInsteadOfAnUnplaceableExit(): void
    {
        $this->sim->commissionMode = 'BNB';
        $this->sim->prices['BNBUSDT'] = '600';
        $this->sim->balances['BNB'] = ['free' => '0', 'locked' => '0'];
        $this->fillTheBuyAt125();

        $this->assertSame(0, bccomp('0.999', $this->openSell()['origQty'], 8));
        $this->assertContains('fee_netted', $this->eventKinds());
    }

    public function testAnExitAHairShortOfTheAccountIsPlacedForWhatIsThere(): void
    {
        // something else drew on the account between the fill being booked
        // and its exit being placed (a sibling daemon's commission, dust swept
        // by hand): no exit on the book is the one state not to rest in
        $hit = false;
        $this->sim->beforeOrder = function (array $q) use (&$hit): void {
            if ($q['side'] === 'SELL' && !$hit) {
                $hit = true;
                $this->sim->balances['BTC']['free'] = bcsub($this->sim->balances['BTC']['free'], '0.002', 12);
            }
        };
        $this->fillTheBuyAt125();

        $this->assertSame(0, bccomp('0.997', $this->openSell()['origQty'], 8));
        $this->assertContains('exit_shrunk', $this->eventKinds());
    }

    public function testMissingInventoryIsNotPaperedOver(): void
    {
        $hit = false;
        $this->sim->beforeOrder = function (array $q) use (&$hit): void {
            if ($q['side'] === 'SELL' && !$hit) {
                $hit = true;
                $this->sim->balances['BTC']['free'] = '0.5'; // half the level is gone
            }
        };
        try {
            $this->fillTheBuyAt125();
            $this->fail('an exit the account cannot back must surface as an API error');
        } catch (\App\Domains\Bot\Gateway\BinanceApiError $e) {
            $this->assertSame(-2010, $e->binanceCode);
        }
        $this->assertNull($this->openSell());
        $this->assertNotContains('exit_shrunk', $this->eventKinds());
    }

    public function testARealAccountIsReStampedEveryMinuteNotEveryQuarterHour(): void
    {
        $m = new \ReflectionMethod(Daemon::class, 'balStampInterval');
        $this->assertSame(60, $m->invoke($this->daemon()));
        $paper = $this->mkRun('BTCUSDT');
        $paper->setSimulated(true);
        $this->assertSame(900, $m->invoke($this->daemon($paper)));
    }

    public function testAnUnreadableAccountIsSaidOncePerEpisode(): void
    {
        $this->sim->accountDown = true;
        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();
        $d->tick();
        $this->assertSame(1, count(array_keys($this->eventKinds(), 'balance_stamp_failed', true)));
    }

    public function testAMarketableFillIsBookedAtWhatItTradedAtNotItsLimit(): void
    {
        // the tape drops under a level as its buy is placed: the limit at 125
        // crosses the book and trades at 120. The ledger must say 120 — it is
        // what left the wallet, and what the cycle's profit is measured from.
        $this->sim->balances['BTC'] = ['free' => '0.5', 'locked' => '0']; // a float: qty stays whole
        $moved = false;
        $this->sim->beforeOrder = function (array $q) use (&$moved): void {
            if ($q['side'] === 'BUY' && bccomp((string) $q['price'], '125', 8) === 0 && !$moved) {
                $moved = true;
                $this->sim->price = '120';
            }
        };
        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();
        $d->tick();

        $buy = $this->filledBuy();
        $this->assertSame(0, bccomp('120', (string) $buy->getPrice(), 8));
        $this->assertContains('fill_price', $this->eventKinds());

        $this->sim->setPrice('151');
        $d->tick();
        $cycle = \App\TradeCycleQuery::create()->filterByIdGridRun((int) $this->run->getIdGridRun())->findOne();
        $this->assertNotNull($cycle);
        $this->assertSame(0, bccomp('120', (string) $cycle->getBuyPrice(), 8));
        // gross 1 × (150 − 120) = 30; fees 0.12 (buy, BTC at 120) + 0.15 (sell)
        $this->assertSame(0, bccomp('29.73', (string) $cycle->getRealizedPnl(), 5));
    }

    public function testALowBnbFloatIsAnnouncedBeforeItRunsDry(): void
    {
        $this->sim->commissionMode = 'BNB';
        $this->sim->prices['BNBUSDT'] = '600';
        $this->sim->balances['BNB'] = ['free' => '0.0002', 'locked' => '0']; // one fee's worth
        $this->fillTheBuyAt125();
        $d = $this->daemon(); // balances are stamped at the top of the NEXT tick
        $d->boot();
        $d->tick();

        $this->assertSame(0, bccomp('1', $this->openSell()['origQty'], 8), 'this fill was still covered');
        $this->assertContains('bnb_fee_float_low', $this->eventKinds());
    }

    public function testTheCycleClosesOnTheNetQty(): void
    {
        $this->fillTheBuyAt125();
        $this->sim->setPrice('151');
        $d = $this->daemon();
        $d->boot();
        $d->tick();

        $cycle = \App\TradeCycleQuery::create()->filterByIdGridRun((int) $this->run->getIdGridRun())->findOne();
        $this->assertNotNull($cycle);
        $this->assertSame(0, bccomp('0.999', (string) $cycle->getQty(), 8));
        // gross 0.999 × 25 = 24.975; fees 0.125 (buy, BTC) + 0.14985 (sell, USDT)
        $this->assertSame(0, bccomp('24.70015', (string) $cycle->getRealizedPnl(), 5));
    }
}
