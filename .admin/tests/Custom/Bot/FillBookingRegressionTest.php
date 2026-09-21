<?php

namespace Tests\Custom\Bot;

use App\BotOrderQuery;
use App\Domains\Bot\BudgetGuard;
use App\Domains\Bot\Daemon;
use App\Domains\Bot\EventLog;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\OrderStore;
use App\GridRun;
use App\GridRunQuery;
use App\TradeCycleQuery;
use Tests\Builder\Support\DbTestCase;
use Tests\Custom\Bot\Support\ExchangeSim;

/**
 * ADVERSARIAL REPRO SUITE for the 2026-09-21 real-order hardening
 * (aeafed2 "fix(fees)" + 86f2d6c "fix(ledger)"), and — from BUG 7 down — for
 * what the round-2 review found in 6c4f825, the commit that fixed BUGS 1-6.
 *
 * Every test in here asserts what the books SHOULD say. A failure is the
 * proof of the bug named in the test's docblock. Harness copied from
 * DaemonRealFeesTest.
 */
class FillBookingRegressionTest extends DbTestCase
{
    private GridRun $run;
    private ExchangeSim $sim;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('GTBOT_TREND_REFRESH_STALE=0');
        putenv('GTBOT_TREND_AUTO_RELOAD_ON_DRIFT=0');
        // the emulator answers instantly and never lags its own trade list, so
        // the real account's myTrades retry (Daemon::fillCommission) has
        // nothing to wait for — one test turns it back on deliberately
        putenv('GTBOT_FILL_TRADE_RETRIES=0');
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

    protected function tearDown(): void
    {
        putenv('GTBOT_TREND_REFRESH_STALE');
        putenv('GTBOT_TREND_AUTO_RELOAD_ON_DRIFT');
        putenv('GTBOT_FILL_TRADE_RETRIES');
        parent::tearDown();
    }

    private function mkRun(string $symbol): GridRun
    {
        $r = new GridRun();
        $r->setLabel(static::uniq('repro'));
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

    private function openSellCid(): ?string
    {
        foreach ($this->sim->open as $cid => $o) {
            if ($o['side'] === 'SELL') {
                return $cid;
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

    private function cycle(): ?\App\TradeCycle
    {
        return TradeCycleQuery::create()->filterByIdGridRun((int) $this->run->getIdGridRun())->findOne();
    }

    private function store(): OrderStore
    {
        return new OrderStore(
            (int) $this->run->getIdGridRun(),
            (string) $this->run->getRunUid(),
            $this->run->getLedgerResetAt() ?: null,
            false
        );
    }

    /** buy L01 (line 125) fills for 1.0000 with the commission taken in BTC */
    private function fillTheBuyAt125(): Daemon
    {
        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();
        $this->sim->setPrice('124');
        $d->tick();
        return $d;
    }

    // ────────────────────────────────────────────────────────────────────
    // BUG 1 — the level books the qty it WANTED to sell, not the qty the
    // exchange filters let it place. bookFill nets the commission out of the
    // held qty (0.999), placeIntent floors that to the lot step (0.99), but
    // LevelStateMachine::onPartialBuyBooked already set held[i] = 0.999. The
    // cycle then closes on 0.999 while only 0.99 was ever sold: realized P/L
    // over-reported, trackedInventory driven to zero with real coins still in
    // the wallet (which the whole fleet then reads as spare "fee float").
    // ────────────────────────────────────────────────────────────────────
    public function testACycleNeverBooksMoreBaseThanTheExitCouldCarry(): void
    {
        // a coarse lot step (0.01) is the only difference from the author's
        // own fixture — with his 0.0001 step the netted 0.999 happens to land
        // exactly on a step boundary and the bug is invisible
        $this->sim = new ExchangeSim([
            ['filterType' => 'PRICE_FILTER', 'tickSize' => '0.01000000'],
            ['filterType' => 'LOT_SIZE', 'stepSize' => '0.01000000', 'minQty' => '0.01000000'],
            ['filterType' => 'NOTIONAL', 'minNotional' => '5.00000000'],
        ]);
        $this->sim->commissionMode = 'native';
        $this->sim->balances = ['USDT' => ['free' => '1000', 'locked' => '0']];

        $d = $this->fillTheBuyAt125();

        $sell = $this->openSell();
        $this->assertNotNull($sell);
        $this->assertSame(0, bccomp('0.99', $sell['origQty'], 8), 'exit is floored to the lot step');

        $this->sim->setPrice('151');
        $d->tick();

        $c = $this->cycle();
        $this->assertNotNull($c);
        $this->assertSame(
            0,
            bccomp('0.99', (string) $c->getQty(), 8),
            'the cycle must book the qty that was actually SOLD (0.99), not the qty the level believed it held (0.999)'
        );
        // the 0.009 BTC between lot steps can never be an order: it is written
        // off the books AT THE FILL (held = 0.99) and stays in the account as
        // fee float, where the next commission finds it — so ledger and
        // account agree, and the float is real rather than phantom
        $this->assertSame(0, bccomp('0', $this->store()->trackedInventory(), 8));
        $this->assertSame(0, bccomp('0.99', (string) $this->filledBuy()->getFilledQty(), 8));
    }

    // ────────────────────────────────────────────────────────────────────
    // BUG 2 — placeShrunkExit places a SMALLER exit than the machine was
    // armed for, but never tells the machine. held[i] stays at the gross, so
    // the cycle books qty the account never sold.
    // (Same root cause as BUG 1: the machine's held is set from the intent,
    // never from what actually reached the book.)
    // ────────────────────────────────────────────────────────────────────
    public function testAShrunkExitBooksOnlyWhatItSold(): void
    {
        $hit = false;
        $this->sim->beforeOrder = function (array $q) use (&$hit): void {
            if ($q['side'] === 'SELL' && !$hit) {
                $hit = true;
                $this->sim->balances['BTC']['free'] = bcsub($this->sim->balances['BTC']['free'], '0.002', 12);
            }
        };
        $d = $this->fillTheBuyAt125();

        $this->assertSame(0, bccomp('0.997', $this->openSell()['origQty'], 8));
        $this->assertContains('exit_shrunk', $this->eventKinds());

        $this->sim->setPrice('151');
        $d->tick();

        $c = $this->cycle();
        $this->assertNotNull($c);
        $this->assertSame(
            0,
            bccomp('0.997', (string) $c->getQty(), 8),
            'the shrunk exit sold 0.997 — the cycle must not book 0.999'
        );
    }

    // ────────────────────────────────────────────────────────────────────
    // BUG 3 — an externally canceled exit is re-hydrated at the LEVEL's
    // standard qty. GridEngine::onSellCanceled calls hydrateLevel(SELL_OPEN)
    // with no $heldQty, so held[i] jumps from the netted 0.999 back to the
    // ladder's 1.0 — while the replacement order is (correctly) placed for
    // the row's 0.999. The cycle then books 1.0 against a 0.999 buy and
    // trackedInventory goes NEGATIVE.
    // ────────────────────────────────────────────────────────────────────
    public function testAReplacedExitKeepsTheNettedHeldQty(): void
    {
        $d = $this->fillTheBuyAt125();
        $cid = $this->openSellCid();
        $this->assertNotNull($cid);
        $this->assertSame(0, bccomp('0.999', $this->sim->open[$cid]['origQty'], 8));

        // the exchange drops the exit (expired / canceled by hand)
        $o = $this->sim->open[$cid];
        $o['status'] = 'CANCELED';
        $this->sim->done[$cid] = $o;
        unset($this->sim->open[$cid]);

        $d->tick(); // resolveMissing → CANCELED → GridEngine::onSellCanceled

        $this->sim->setPrice('151');
        $d->tick();

        $c = $this->cycle();
        $this->assertNotNull($c);
        $this->assertSame(
            0,
            bccomp('0.999', (string) $c->getQty(), 8),
            're-placing a canceled exit must not resurrect the gross level qty'
        );
        $this->assertSame(
            1,
            bccomp($this->store()->trackedInventory(), '-0.00000001', 8),
            'tracked inventory must never go negative'
        );
    }

    // ────────────────────────────────────────────────────────────────────
    // BUG 4 — FeeFloat::trackedBase() counts the very row being booked. A buy
    // that was seen PartFilled (state PartFilled, filled_qty 0.4) and then
    // completes is added to the fleet's tracked inventory AND again as
    // $executed by uncovered(), so `needed` overshoots by 0.4 BTC and a real
    // 0.05 BTC float is declared insufficient: the fill is netted, an alert
    // fires, and the residue is stranded. The same double count hits the
    // $unbookedBuyQty term for any sibling buy already marked PartFilled.
    // ────────────────────────────────────────────────────────────────────
    public function testACompletingPartialBuyStillSeesTheAccountFloat(): void
    {
        $this->sim->balances['BTC'] = ['free' => '0.05', 'locked' => '0']; // 50× the commission

        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();

        // the L01 buy at 125 gets a partial fill while it is still working
        $cid = null;
        foreach ($this->sim->open as $c => $o) {
            if ($o['side'] === 'BUY' && bccomp((string) $o['price'], '125', 8) === 0) {
                $cid = $c;
            }
        }
        $this->assertNotNull($cid);
        $this->sim->partialFill($cid, '0.4');
        $d->tick(); // ledger marks it PartFilled 0.4

        $this->assertSame('PartFilled', (string) BotOrderQuery::create()->findOneByClientOrderId($cid)->getState());

        $this->sim->setPrice('124'); // it completes
        $d->tick();

        $this->assertNotContains(
            'fee_netted',
            $this->eventKinds(),
            'a 0.05 BTC account float covers a 0.001 BTC commission — the fill must stay whole'
        );
        $this->assertSame(0, bccomp('1', $this->openSell()['origQty'], 8));
    }

    // ────────────────────────────────────────────────────────────────────
    // BUG 5 — the trend arm WEDGES after its first netted exit. engine_state
    // qty is now the sum of NET fills, so it is no longer a multiple of the
    // lot step; the stop-sell is floored to the step by Filters::normalize
    // and sells strictly less. TrendEngine::onSellFill's
    // `$fullExit = executed >= posQty` is therefore false, the position is
    // "partially exited" with a sub-minQty residue it can never sell, and
    // state['entry'] stays non-null — so TrendEngine::tick() routes to
    // manageOpenPosition forever and the arm never enters another trade.
    // ────────────────────────────────────────────────────────────────────
    public function testATrendArmIsFlatAfterItsExitFills(): void
    {
        putenv('GTBOT_TREND_REFRESH_STALE=0');
        putenv('GTBOT_TREND_AUTO_RELOAD_ON_DRIFT=0');
        try {
            $this->sim->commissionMode = 'native';
            $this->sim->balances = ['USDT' => ['free' => '5000', 'locked' => '0']];

            $run = $this->run;
            $run->setAlgo('Trend');
            $run->setProfile('Balanced');
            $run->setBudgetQuote('1000');
            $run->setDeployPct(100);
            $run->setTrendTf('1h');
            $run->save();

            // EngineSwitchTest's breakout fixture: 45 flat bars @100, 15 rising
            $candles = array_fill(0, 45, ['high' => '101', 'low' => '99', 'close' => '100']);
            for ($i = 0; $i < 15; $i++) {
                $c = (string) (101 + $i * 2);
                $candles[] = ['high' => $c, 'low' => (string) ((float) $c - 1), 'close' => $c];
            }
            \App\Domains\Bot\MarketStore::upsert('BTCUSDT', '1h', \App\Domains\Bot\Indicators::summary($candles), $candles);

            $this->sim->setPrice('130');
            $d = $this->daemon();
            $d->boot();
            $d->tick();
            $run->reload();
            $state = json_decode((string) $run->getEngineState(), true);
            $this->assertGreaterThan(0, bccomp((string) ($state['qty'] ?? '0'), '0', 8), 'fixture: a position opened');

            // rally so the trailing stop ratchets above cost, then pull back
            // through it: a PROFITABLE exit, so sell_at_loss never binds
            $this->sim->setPrice('200');
            $d->tick();
            $this->sim->setPrice('190');
            $d->tick();
            $d->tick();

            $run->reload();
            $state = json_decode((string) $run->getEngineState(), true);
            $this->assertNull(
                $state['entry'] ?? null,
                'the stop-out sold the position — the arm must be flat and able to re-enter, '
                . 'not stuck holding a sub-lot-step residue forever (qty left: ' . var_export($state['qty'] ?? null, true) . ')'
            );
        } finally {
            putenv('GTBOT_TREND_REFRESH_STALE');
            putenv('GTBOT_TREND_AUTO_RELOAD_ON_DRIFT');
        }
    }

    // ────────────────────────────────────────────────────────────────────
    // BUG 6 — FeeFloat::accountPaysInBnb() reads the newest real Filled row's
    // fee_asset with no symbol filter. On a BNB PAIR the native (non-BNB-fee-
    // mode) commission for a BUY is charged in BNB because BNB is the BASE
    // asset — indistinguishable from real BNB fee mode by fee_asset alone. A
    // fleet with any BNB arm therefore declares the whole account to be in BNB
    // fee mode, and every daemon starts policing a "BNB fee float" that is in
    // fact the BNB arm's own inventory (bnb_fee_float_low → Telegram).
    // ────────────────────────────────────────────────────────────────────
    public function testABnbPairCommissionIsNotProofOfBnbFeeMode(): void
    {
        $bnb = $this->mkRun('BNBUSDT');
        $o = new \App\BotOrder();
        $o->setIdGridRun((int) $bnb->getIdGridRun());
        $o->setClientOrderId(static::uniq('cid'));
        $o->setSide('Buy');
        $o->setLevelIdx(0);
        $o->setPrice('600');
        $o->setQty('1');
        $o->setFilledQty('0.999');
        $o->setFeePaid('0.6');
        $o->setFeeAsset('BNB'); // the BASE asset of BNBUSDT, not a fee float
        $o->setState('Filled');
        $o->setSimulated(false);
        $o->save();

        $this->assertFalse(
            \App\Domains\Bot\FeeFloat::accountPaysInBnb(),
            "a BNB pair's own base-asset commission must not be read as account-wide BNB fee mode"
        );
    }

    // ════════════════════════════════════════════════════════════════════
    // ROUND 2 — what 6c4f825 (the fix for BUGS 1-6) broke or left behind.
    // ════════════════════════════════════════════════════════════════════

    /** A booted daemon with the stuck-partial timeout shrunk for tests
     *  (DaemonLiveFlowTest::daemonWithPartialTimeout's pattern). */
    private function daemonWithPartialTimeout(GridRun $run, int $secs): Daemon
    {
        $gateway = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $log = new EventLog((int) $run->getIdGridRun(), false);
        $d = new class($run, $gateway, false, $log, $secs) extends Daemon {
            public function __construct($run, $gateway, $dryRun, $log, private readonly int $timeoutSecs)
            {
                parent::__construct($run, $gateway, $dryRun, $log);
            }

            protected function partialTimeoutSeconds(): int
            {
                return $this->timeoutSecs;
            }
        };
        $d->boot();
        return $d;
    }

    private function seedBreakoutCandles(): void
    {
        $candles = array_fill(0, 45, ['high' => '101', 'low' => '99', 'close' => '100']);
        for ($i = 0; $i < 15; $i++) {
            $c = (string) (101 + $i * 2);
            $candles[] = ['high' => $c, 'low' => (string) ((float) $c - 1), 'close' => $c];
        }
        \App\Domains\Bot\MarketStore::upsert('BTCUSDT', '1h', \App\Domains\Bot\Indicators::summary($candles), $candles);
    }

    private function marker(): ?array
    {
        $this->run->reload();
        $raw = (string) ($this->run->getEngineState() ?? '');
        return $raw === '' ? null : json_decode($raw, true);
    }

    /** Take an open sim order off the book as CANCELED, keeping whatever it
     *  had executed — what an external cancel (the dashboard, an operator,
     *  Binance's own STP EXPIRED_IN_MATCH) leaves behind. */
    private function externallyCancel(string $cid): void
    {
        $o = $this->sim->open[$cid];
        $o['status'] = 'CANCELED';
        $this->sim->done[$cid] = $o;
        unset($this->sim->open[$cid]);
    }

    /** Total base qty still resting as SELL on the emulated exchange. */
    private function simSellQty(): string
    {
        $qty = '0';
        foreach ($this->sim->open as $o) {
            if ($o['side'] === 'SELL') {
                $qty = bcadd($qty, bcsub((string) $o['origQty'], (string) $o['executedQty'], 12), 12);
            }
        }
        return $qty;
    }

    /** The tape runs away from every sell as it is placed, so an exit RESTS. */
    private function marketRunsAway(string $factor = '0.97'): void
    {
        $this->sim->beforeOrder = function (array $q) use ($factor): void {
            if ($q['side'] === 'SELL') {
                $this->sim->price = bcmul((string) $q['price'], $factor, 8);
            }
        };
    }

    /** A Trend run holding a position entered @130 whose stop-sell is RESTING
     *  on the book (the tape ran away from it as it was placed).
     *  @return array{0: Daemon, 1: string, 2: string} daemon, position, cid */
    private function trendWithRestingStopSell(): array
    {
        $run = $this->run;
        $run->setAlgo('Trend');
        $run->setProfile('Balanced');
        $run->setBudgetQuote('1000');
        $run->setDeployPct(100);
        $run->setTrendTf('1h');
        $run->setSellAtLoss(true);
        $run->save();
        $this->seedBreakoutCandles();
        $this->sim->balances = ['USDT' => ['free' => '5000', 'locked' => '0']];

        $this->sim->setPrice('130');
        $d = $this->daemon();
        $d->boot();
        $d->tick();                                   // the entry tranche fills
        $position = (string) ($this->marker()['qty'] ?? '0');
        $this->assertGreaterThan(0, bccomp($position, '0', 8), 'fixture: the position opened');

        $this->marketRunsAway();
        $this->sim->setPrice('100');                  // straight through the stop
        $d->tick();                                   // the engine places its stop-sell
        $cid = $this->openSellCid();
        $this->assertNotNull($cid, 'fixture: the stop-sell rests on the book');
        return [$d, $position, $cid];
    }

    // ────────────────────────────────────────────────────────────────────
    // BUG 7 — AN ENGINE BOOKS ITS OWN FILLS. A Trend stop-sell canceled
    // having already traded was booked by the SHELL
    // (Daemon::bookCanceledRemainder, new in 6c4f825): it recorded the cycle
    // itself, re-placed the remainder as a legacy row and returned — so
    // TrendEngine never heard about it. engine_state.qty still claimed the
    // whole position, fees_paid was never prorated, heldQty() counted the
    // remainder twice (state.qty + legacyRemainingQty), and only onSellFill
    // ever clears state.entry, so the arm could never read flat again.
    // ────────────────────────────────────────────────────────────────────
    public function testACanceledTrendExitThatTradedReducesThePosition(): void
    {
        [$d, $position, $cid] = $this->trendWithRestingStopSell();

        $traded = '2';
        $this->sim->partialFill($cid, $traded);
        $this->externallyCancel($cid);

        $d->tick(); // detectFills → resolveMissing → CANCELED with executedQty

        $this->assertContains('cancel_partial_booked', $this->eventKinds(), 'fixture: the traded part was booked');
        $this->assertSame(
            0,
            bccomp(bcsub($position, $traded, 8), (string) ($this->marker()['qty'] ?? '0'), 8),
            'the exit sold ' . $traded . ' of ' . $position . ': the engine position carries the remainder, '
            . 'it does not go on claiming the whole position'
        );
        $c = $this->cycle();
        $this->assertNotNull($c, 'the engine booked the cycle');
        $this->assertSame(0, bccomp($traded, (string) $c->getQty(), 8), 'the cycle books what was sold');
    }

    // ────────────────────────────────────────────────────────────────────
    // BUG 7, second consequence — with state.qty never reduced and the
    // remainder re-placed as a LEGACY row (which hasOpenOwnOrders() does not
    // count), the next tick's manageOpenPosition placed ANOTHER sell for the
    // WHOLE position: the book carried nearly twice the inventory the account
    // held. Exactly the failure 6c4f825 fixed for the chase ("an engine owns
    // and re-prices its own exits"), re-entering through the shell's own
    // cancel-booking path.
    // ────────────────────────────────────────────────────────────────────
    public function testACanceledTrendExitLeavesOneExitForOnePosition(): void
    {
        [$d, $position, $cid] = $this->trendWithRestingStopSell();

        $traded = '2';
        $this->sim->partialFill($cid, $traded);
        $this->externallyCancel($cid);

        $d->tick(); // the fill is booked through the engine
        $d->tick(); // …and the engine re-arms ONE exit for what is left

        $held = bcsub($position, $traded, 8);
        $this->assertSame(
            -1,
            bccomp($this->simSellQty(), bcadd($held, '0.00000001', 12), 8),
            sprintf('the book carries %s for an account holding %s', $this->simSellQty(), $held)
        );
    }

    // ────────────────────────────────────────────────────────────────────
    // BUG 8 — the same hole on the GRID side: the shell booked the whole
    // lot's cycle and carried the remainder off the ladder. The level keeps
    // what is still held and re-arms ITS OWN exit for exactly that, and the
    // buy-leg fee is split across the pieces so the lot pays its entry fee
    // once — not `share` of it (the remainder used to be pinned with
    // legacy_buy_fee '0', OrderStore::markLegacy's `?? '0'`).
    // ────────────────────────────────────────────────────────────────────
    public function testAGridExitCanceledMidFillKeepsItsRemainderOnTheLevel(): void
    {
        $d = $this->fillTheBuyAt125();
        $cid = $this->openSellCid();
        $this->assertNotNull($cid);
        $lot = (string) $this->sim->open[$cid]['origQty']; // 0.999 — netted at the fill

        $traded = '0.4';
        $this->sim->partialFill($cid, $traded);
        $this->externallyCancel($cid);

        $d->tick(); // booked through GridEngine::onSellFill

        $left = bcsub($lot, $traded, 8);
        $replacement = $this->openSell();
        $this->assertNotNull($replacement, 'the level re-arms its own exit for the remainder');
        $this->assertSame(0, bccomp($left, (string) $replacement['origQty'], 8), 'for exactly what is left');
        $this->assertSame(0, bccomp('150', (string) $replacement['price'], 8), 'at the level’s own exit line');
        $this->assertSame(
            0,
            bccomp($left, $this->store()->trackedInventory(), 8),
            'the ledger still tracks the base that was not sold'
        );

        // and when the rest sells, the lot has paid its entry fee exactly once
        $this->sim->setPrice('151');
        $d->tick();
        $cycles = TradeCycleQuery::create()->filterByIdGridRun((int) $this->run->getIdGridRun())->find();
        $this->assertCount(2, $cycles, 'one cycle per piece of the lot');
        $sold = '0';
        $fees = '0';
        foreach ($cycles as $c) {
            $sold = bcadd($sold, (string) $c->getQty(), 12);
            $fees = bcadd($fees, (string) $c->getFeesTotal(), 12);
        }
        $this->assertSame(0, bccomp($lot, $sold, 8), 'the pieces add up to the lot');
        $paid = (string) $this->filledBuy()->getFeePaid();
        foreach (BotOrderQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterBySide('Sell')
            ->filterByState('Filled')
            ->find() as $sell) {
            $paid = bcadd($paid, (string) ($sell->getFeePaid() ?: '0'), 12);
        }
        // fees_total is DECIMAL(18,8): two cycles can each round a half-share
        $this->assertSame(
            -1,
            bccomp(abs((float) bcsub($fees, $paid, 12)) . '', '0.00000002', 12),
            sprintf('the cycles book the fees actually paid (%s) exactly once — booked %s', $paid, $fees)
        );
    }

    // ────────────────────────────────────────────────────────────────────
    // BUG 9 — THE STUCK CLOCK IS FIRST-SEEN. 6c4f825 made detectFills refresh
    // filled_qty on every observed executedQty change, and the add_tablestamp
    // behavior re-stamps date_modification on every modified save — so an
    // order that trickled got a fresh stuck timer on every refresh and could
    // never time out (TrendEngine::handleStuckPartials measures against that
    // stamp, and it is also what a restart resumes Daemon::$partialSince
    // from). The refresh must not move the clock, in this process or across a
    // restart.
    // ────────────────────────────────────────────────────────────────────
    public function testATricklingPartialKeepsItsStuckClock(): void
    {
        $run = $this->run;
        $run->setAlgo('Trend');
        $run->setProfile('Balanced');
        $run->setBudgetQuote('1000');
        $run->setDeployPct(100);
        $run->setTrendTf('1h');
        $run->save();
        $this->seedBreakoutCandles();
        $this->sim->balances = ['USDT' => ['free' => '5000', 'locked' => '0']];
        // the tape jumps above every buy as it is placed → the tranche RESTS
        $this->sim->beforeOrder = function (array $q): void {
            if ($q['side'] === 'BUY') {
                $this->sim->price = bcmul((string) $q['price'], '1.01', 8);
            }
        };

        $this->sim->setPrice('130');
        $d = $this->daemonWithPartialTimeout($run, 1800);
        $d->tick();                                   // the tranche is placed and rests
        $cid = null;
        foreach ($this->sim->open as $c => $o) {
            if ($o['side'] === 'BUY') {
                $cid = $c;
            }
        }
        $this->assertNotNull($cid, 'fixture: an entry tranche rests on the book');

        $this->sim->partialFill($cid, '0.5');
        $d->tick();                                   // marked PartFilled — the clock starts

        // the partial has now been open for an hour (no sleeping in a test)
        $row = BotOrderQuery::create()->findOneByClientOrderId($cid);
        $this->assertSame('PartFilled', (string) $row->getState());
        $stuckSince = time() - 3600;
        \Propel::getConnection()->prepare('UPDATE bot_order SET date_modification = :t WHERE id_bot_order = :id')
            ->execute([':t' => date('Y-m-d H:i:s', $stuckSince), ':id' => (int) $row->getIdBotOrder()]);
        // Propel 1 never re-hydrates a pooled object, so a row changed behind
        // its back is only visible after this — and a real restart, which is
        // what the stamp exists for, starts with an empty pool anyway
        \App\BotOrderPeer::clearInstancePool();

        $this->sim->partialFill($cid, '0.6');         // …and it trickles on
        $d->tick();                                   // the ledger follows the fill

        \App\BotOrderPeer::clearInstancePool();
        $row = BotOrderQuery::create()->findOneByClientOrderId($cid);
        $this->assertSame(0, bccomp('0.6', (string) $row->getFilledQty(), 8), 'the ledger follows the fill');
        $this->assertSame(
            $stuckSince,
            (int) $row->getDateModification('U'),
            '…without moving the clock a restart resumes the stuck timer from'
        );

        // a fresh daemon (this process never saw the partial start) still
        // finds it stuck, cancels the remainder and books what filled
        \App\BotOrderPeer::clearInstancePool();
        $d2 = $this->daemonWithPartialTimeout($run, 1800);
        $d2->tick();
        $this->assertContains('partial_timeout', $this->eventKinds(), 'an hour-old partial is stuck, trickle or no trickle');
    }

    // ────────────────────────────────────────────────────────────────────
    // BUG 10 — a kill+flatten carries a level's lot off the ladder
    // (closeCanceledExit hydrates the level EMPTY, new in 6c4f825) and the
    // ladder was never re-sized: on resume the level re-armed at its ORIGINAL
    // per-level qty while the capital behind that lot was still locked in the
    // legacy exit — the rule buildLadder states ("Quote a new ladder must NOT
    // re-spend": free = min(deployed, budget − legacyReserve)). The geometry
    // on the row has not changed, so nothing asked for a refit.
    // ────────────────────────────────────────────────────────────────────
    public function testTheLadderIsReSizedAfterAFlattenCarriedItsLotsOff(): void
    {
        $run = $this->run;
        $run->setSellAtLoss(true);
        $run->save();
        $this->sim->balances = ['USDT' => ['free' => '5000', 'locked' => '0']];

        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();                       // buys armed at 100 and 125
        $this->sim->setPrice('100');
        $d->tick();                       // both fill; exits work at 125 and 150

        $this->marketRunsAway();
        $this->sim->setPrice('124');
        $cmd = new \App\BotCommand();
        $cmd->setIdGridRun((int) $run->getIdGridRun());
        $cmd->setCommand('Flatten');
        $cmd->setCmdStatus('Pending');
        $cmd->save();
        $d->tick();                       // the lots are carried off the ladder
        $carried = array_filter(
            BotOrderQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->filterByState('SELL_OPEN')->find()->getArrayCopy(),
            fn ($r) => (bool) $r->getIsLegacy()
        );
        $this->assertNotSame([], $carried, 'fixture: the flatten carried lots off the ladder');

        $run->setKillSwitch(false);       // the operator resumes the run
        $run->save();
        $d->tick();

        $this->assertLadderSizedOnFreeCapital($run);
    }

    /** BUG 10 on PAPER — the flatten chase is only gated on dry-run, so the
     *  whole path (and the fix) runs on the simulated fleet that is live on
     *  prod today. Same run, same command, PaperGateway underneath. */
    public function testAPaperRunReSizesItsLadderAfterAFlattenToo(): void
    {
        $run = $this->run;
        $run->setSimulated(true);
        $run->setSellAtLoss(true);
        $run->save();

        $tape = '150';
        $transport = function (string $method, string $url, array $headers, ?string $body) use (&$tape): array {
            if (str_contains($url, '/api/v3/ticker/price')) {
                return ['status' => 200, 'headers' => [], 'body' => json_encode(['price' => $tape])];
            }
            if (str_contains($url, '/api/v3/exchangeInfo')) {
                return ['status' => 200, 'headers' => [], 'body' => json_encode(['symbols' => [[
                    'symbol' => 'BTCUSDT',
                    'filters' => [
                        ['filterType' => 'PRICE_FILTER', 'tickSize' => '0.01'],
                        ['filterType' => 'LOT_SIZE', 'stepSize' => '0.0001', 'minQty' => '0.0001'],
                        ['filterType' => 'NOTIONAL', 'minNotional' => '5'],
                    ],
                ]]])];
            }
            throw new \RuntimeException("unexpected HTTP call in paper mode: $url");
        };
        $public = new BinanceGateway('https://api.binance.com', '', '', $transport);
        $gw = new \App\Domains\Bot\Gateway\PaperGateway($public, $run, (string) $run->getFeePct());
        $d = new Daemon($run, $gw, false, new EventLog((int) $run->getIdGridRun(), false));
        $d->boot();
        $d->tick();                       // buys armed at 100 and 125
        $tape = '100';
        $d->tick();                       // both fill; exits work at 125 and 150

        $tape = '124';
        $cmd = new \App\BotCommand();
        $cmd->setIdGridRun((int) $run->getIdGridRun());
        $cmd->setCommand('Flatten');
        $cmd->setCmdStatus('Pending');
        $cmd->save();
        $d->tick();                       // carried off the ladder, priced under the mark
        $tape = '120';                    // …and the tape leaves them resting there

        $run->setKillSwitch(false);
        $run->save();
        $d->tick();

        $this->assertLadderSizedOnFreeCapital($run);
    }

    /** Every buy the ladder has armed fits inside the capital the legacy
     *  exits do NOT tie up — buildLadder's own rule, re-applied. */
    private function assertLadderSizedOnFreeCapital(GridRun $run): void
    {
        $store = new OrderStore(
            (int) $run->getIdGridRun(),
            (string) $run->getRunUid(),
            $run->getLedgerResetAt() ?: null,
            (bool) $run->getSimulated()
        );
        $reserve = $store->legacyReserveQuote();
        $this->assertSame(1, bccomp($reserve, '0', 8), 'fixture: capital is locked in legacy exits');
        $sized = \App\Domains\Bot\GridMath::allocate(
            bcsub((string) $run->getBudgetQuote(), $reserve, 12),
            array_slice(\App\Domains\Bot\GridMath::levels('100', '200', 4, 'Arithmetic'), 0, -1),
            'EqualBase'
        );
        $armed = array_values(array_filter(
            BotOrderQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->find()->getArrayCopy(),
            fn ($r) => (string) $r->getState() === 'BUY_OPEN'
        ));
        $this->assertNotSame([], $armed, 'fixture: the resumed ladder re-armed a level');
        foreach ($armed as $buy) {
            $i = (int) $buy->getLevelIdx();
            $this->assertSame(
                -1,
                bccomp((string) $buy->getQty(), bcadd($sized[$i], '0.00000001', 8), 8),
                sprintf(
                    'L%02d re-armed for %s while %s of the %s slice is still held behind legacy exits — the free-capital '
                    . 'rule sizes this level at %s',
                    $i,
                    (string) $buy->getQty(),
                    $reserve,
                    (string) $run->getBudgetQuote(),
                    $sized[$i]
                )
            );
        }
    }

    // ────────────────────────────────────────────────────────────────────
    // BUG 11 — a handoff that could not place its exit is never silent.
    // placeLegacyExit() returns false for unplaceable dust and for an
    // exchange that refused the order; three callers threw that away, so real
    // base sat in the account with no exit behind one Error line.
    // ────────────────────────────────────────────────────────────────────
    public function testAnUnplaceableHandoffSaysTheInventoryIsUnguarded(): void
    {
        $d = $this->fillTheBuyAt125();
        // the exchange refuses every sell from here on (not a balance problem,
        // so placeShrunkExit cannot rescue it either)
        $this->sim->beforeOrder = function (array $q): void {
            if ($q['side'] === 'SELL') {
                throw new \App\Domains\Bot\Gateway\BinanceApiError('Filter failure: PRICE_FILTER', 400, -1013);
            }
        };
        $cid = $this->openSellCid();
        $this->sim->partialFill($cid, '0.4');
        $this->externallyCancel($cid);

        $d->tick();

        $this->assertContains(
            'unguarded_inventory',
            $this->eventKinds(),
            'base with no exit on the book is named, not left to one Error line'
        );
    }

    // ────────────────────────────────────────────────────────────────────
    // BUG 12 — myTrades lags the order status on a real account: an empty
    // trade list for an order the exchange reports executed is "not yet", not
    // "never". Giving up on the first read booked the estimate forever (no
    // netting, the limit price as the fill price). Retry a few times; when it
    // still comes up empty, say which order carries an estimate.
    // ────────────────────────────────────────────────────────────────────
    public function testAFillWhoseTradeListLagsIsStillPricedFromIt(): void
    {
        putenv('GTBOT_FILL_TRADE_RETRIES=2');
        $this->sim->tradeLag = 1; // the first read comes back empty
        $d = $this->fillTheBuyAt125();

        $this->assertNotContains('fill_unpriced', $this->eventKinds(), 'the retry found the trade list');
        $this->assertSame(
            0,
            bccomp('0.999', (string) $this->filledBuy()->getFilledQty(), 8),
            'the base-asset commission was netted off the fill, as it is when the list is there at once'
        );
        $this->assertSame('BTC', (string) $this->filledBuy()->getFeeAsset());
    }

    public function testAFillThatStaysUnpricedIsJournaled(): void
    {
        $this->sim->tradeLag = 99; // the list never arrives
        $d = $this->fillTheBuyAt125();

        $this->assertContains(
            'fill_unpriced',
            $this->eventKinds(),
            'a fill booked on the estimate is on the record — the books say which rows are guesses'
        );
    }

    /** @return \App\TradeCycle[] every cycle of this run, oldest first */
    private function cycles(): array
    {
        return TradeCycleQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->orderByIdTradeCycle()
            ->find()
            ->getArrayCopy();
    }

    private function totalSoldQty(): string
    {
        $q = '0';
        foreach ($this->cycles() as $c) {
            $q = bcadd($q, (string) $c->getQty(), 12);
        }
        return $q;
    }

    private function totalCycleFees(): string
    {
        $q = '0';
        foreach ($this->cycles() as $c) {
            $q = bcadd($q, (string) $c->getFeesTotal(), 12);
        }
        return $q;
    }

    /** Fees the exchange actually charged on every filled row of this run. */
    private function feesReallyPaid(): string
    {
        $paid = '0';
        foreach (BotOrderQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByState('Filled')
            ->find() as $row) {
            $paid = bcadd($paid, (string) ($row->getFeePaid() ?: '0'), 12);
        }
        return $paid;
    }

    /** |a - b| < $eps, in bcmath (no float round-trip). */
    private function assertClose(string $a, string $b, string $eps, string $msg): void
    {
        $diff = bcsub($a, $b, 12);
        if (bccomp($diff, '0', 12) < 0) {
            $diff = bcmul($diff, '-1', 12);
        }
        $this->assertSame(-1, bccomp($diff, $eps, 12), $msg . sprintf(' (%s vs %s)', $a, $b));
    }

    // ────────────────────────────────────────────────────────────────────
    // ROUND 3 / 1 — a lot split THREE ways: two partial cancels, then the
    // rest fills. Each piece books its own cycle; across the three the lot
    // must sell exactly what it bought and pay its entry fee exactly once.
    // ────────────────────────────────────────────────────────────────────
    public function testALotSplitThreeWaysSellsWhatItBoughtAndPaysItsFeeOnce(): void
    {
        $d = $this->fillTheBuyAt125();
        $cid = $this->openSellCid();
        $this->assertNotNull($cid);
        $lot = (string) $this->sim->open[$cid]['origQty']; // 0.999

        $this->sim->partialFill($cid, '0.4');
        $this->externallyCancel($cid);
        $d->tick();                                   // piece 1 booked, 0.599 re-armed

        $cid2 = $this->openSellCid();
        $this->assertNotNull($cid2, 'the level re-arms its own exit for the remainder');
        $this->sim->partialFill($cid2, '0.3');
        $this->externallyCancel($cid2);
        $d->tick();                                   // piece 2 booked, 0.299 re-armed

        $cid3 = $this->openSellCid();
        $this->assertNotNull($cid3, 'the remainder is still guarded after the second split');
        $this->sim->setPrice('151');
        $d->tick();                                   // piece 3 fills

        $this->assertCount(3, $this->cycles(), 'one cycle per piece of the lot');
        $this->assertSame(0, bccomp($lot, $this->totalSoldQty(), 8), 'the pieces add up to the lot');
        $this->assertSame(
            0,
            bccomp('0', $this->store()->trackedInventory(), 8),
            'the lot is fully sold — nothing may be left tracked'
        );
        $this->assertClose(
            $this->totalCycleFees(),
            $this->feesReallyPaid(),
            '0.00000005',
            'a lot split three ways pays its entry fee once'
        );
    }

    // ────────────────────────────────────────────────────────────────────
    // ROUND 3 / 2 — the remainder of a part-sold lot must be floored to the
    // lot step before the level ever holds it. bookPartialExit was the only
    // booking path that did not (bookFill/checkStuckPartials/bookPrunedPartial
    // all do), so the level went on claiming a sliver no order could carry.
    // ────────────────────────────────────────────────────────────────────
    public function testAnUnalignedRemainderLeavesTheBooksConsistent(): void
    {
        $this->sim = new ExchangeSim([
            ['filterType' => 'PRICE_FILTER', 'tickSize' => '0.01000000'],
            ['filterType' => 'LOT_SIZE', 'stepSize' => '0.01000000', 'minQty' => '0.01000000'],
            ['filterType' => 'NOTIONAL', 'minNotional' => '5.00000000'],
        ]);
        $this->sim->commissionMode = 'native';
        $this->sim->balances = ['USDT' => ['free' => '1000', 'locked' => '0']];

        $d = $this->fillTheBuyAt125();
        $cid = $this->openSellCid();
        $this->assertNotNull($cid);
        $lot = (string) $this->sim->open[$cid]['origQty']; // 0.99 (netted, floored)

        $this->sim->partialFill($cid, '0.405');           // leaves 0.585 — not a step multiple
        $this->externallyCancel($cid);
        $d->tick();

        $replacement = $this->openSell();
        $this->assertNotNull($replacement, 'the level re-arms its exit for the remainder');
        $onBook = bcsub((string) $replacement['origQty'], (string) $replacement['executedQty'], 12);
        $this->assertSame(
            0,
            bccomp('0.58', $onBook, 8),
            sprintf('the 0.585 left of the %s lot is floored to the lot step before the level ever holds it', $lot)
        );

        // …and when it sells, the cycles book exactly what reached the book —
        // never the un-placeable sliver the level used to go on claiming
        $this->sim->setPrice('151');
        $d->tick();
        $this->assertSame(
            0,
            bccomp('0.985', $this->totalSoldQty(), 8),
            'the cycles book the 0.405 that traded plus the 0.58 that was sellable, not the sliver between steps'
        );
    }

    // ────────────────────────────────────────────────────────────────────
    // ROUND 3 / 3 — a restart in the middle of a part-sold lot. The
    // replacement exit carries a pinned buy attribution on a NON-legacy row;
    // after a boot that re-hydrates from the ledger the final cycle must
    // still book against the lot's real cost and pay the fee once.
    // ────────────────────────────────────────────────────────────────────
    public function testAPinnedLadderExitSurvivesARestart(): void
    {
        $d = $this->fillTheBuyAt125();
        $cid = $this->openSellCid();
        $this->assertNotNull($cid);
        $lot = (string) $this->sim->open[$cid]['origQty'];
        $this->sim->partialFill($cid, '0.4');
        $this->externallyCancel($cid);
        $d->tick();

        $pinned = BotOrderQuery::create()->findOneByClientOrderId($this->openSellCid());
        $this->assertNotNull($pinned->getLegacyBuyPrice(), 'fixture: the replacement is pinned');
        $this->assertFalse((bool) $pinned->getIsLegacy(), 'fixture: …without being detached from its level');

        \App\BotOrderPeer::clearInstancePool();
        \App\GridRunPeer::clearInstancePool();
        $run = GridRunQuery::create()->findPk((int) $this->run->getIdGridRun());
        $this->run = $run;
        $d2 = $this->daemon($run);
        $d2->boot();

        $this->sim->setPrice('151');
        $d2->tick();

        $this->assertCount(2, $this->cycles(), 'both pieces of the lot are booked');
        $this->assertSame(0, bccomp($lot, $this->totalSoldQty(), 8), 'the pieces add up to the lot');
        $this->assertClose(
            $this->totalCycleFees(),
            $this->feesReallyPaid(),
            '0.00000005',
            'a restart between the pieces must not double- or un-book the entry fee'
        );
    }

    // ────────────────────────────────────────────────────────────────────
    // ROUND 3 / 4 — a normal full grid cycle after the exit was re-placed by
    // onSellCanceled must NOT read as a partial (heldAt vs executed). A false
    // partial books a cycle for part of the lot, keeps a phantom remainder on
    // the level and places a second exit for coins already sold.
    // ────────────────────────────────────────────────────────────────────
    public function testAReArmedExitThatFillsWholeBooksOneCycle(): void
    {
        $this->sim = new ExchangeSim([
            ['filterType' => 'PRICE_FILTER', 'tickSize' => '0.01000000'],
            ['filterType' => 'LOT_SIZE', 'stepSize' => '0.01000000', 'minQty' => '0.01000000'],
            ['filterType' => 'NOTIONAL', 'minNotional' => '5.00000000'],
        ]);
        $this->sim->commissionMode = 'native';
        $this->sim->balances = ['USDT' => ['free' => '1000', 'locked' => '0']];

        $d = $this->fillTheBuyAt125();
        $cid = $this->openSellCid();
        $this->externallyCancel($cid);
        $d->tick();                       // onSellCanceled re-places it

        $this->sim->setPrice('151');
        $d->tick();

        $this->assertCount(1, $this->cycles(), 'a whole fill is one cycle, not a cycle plus a phantom remainder');
        $this->assertNull($this->openSell(), 'no second exit is placed for coins already sold');
        $this->assertNotContains('partial_dust', $this->eventKinds());
    }

    // ────────────────────────────────────────────────────────────────────
    // ROUND 3 / 5 — the grid partial re-place catches BinanceApiError and
    // continues, leaving the level SELL_OPEN with no order and no ledger row:
    // detectFills can only ever resolve rows that EXIST, so nothing used to
    // put that exit back. One transient 5xx stranded the lot for good.
    // ────────────────────────────────────────────────────────────────────
    public function testAFailedPartialRePlaceIsRecovered(): void
    {
        $d = $this->fillTheBuyAt125();
        $cid = $this->openSellCid();
        $this->sim->beforeOrder = function (array $q): void {
            if ($q['side'] === 'SELL') {
                throw new \App\Domains\Bot\Gateway\BinanceApiError('Filter failure: PRICE_FILTER', 400, -1013);
            }
        };
        $this->sim->partialFill($cid, '0.4');
        $this->externallyCancel($cid);
        $d->tick();
        $this->assertContains('place_failed', $this->eventKinds(), 'fixture: the re-place was refused');
        $this->assertContains('unguarded_inventory', $this->eventKinds(), '…and the lot is named');
        $this->assertNull($this->openSell(), 'fixture: nothing reached the book');

        // the exchange is healthy again — the next tick must put an exit back
        $this->sim->beforeOrder = null;
        $d->tick();

        $replacement = $this->openSell();
        $this->assertNotNull(
            $replacement,
            'the remainder of a part-sold lot is real inventory: once the exchange accepts orders again, '
            . 'reArmMissingExits has to re-arm the level the refusal left holding it'
        );
        $this->assertSame(0, bccomp('0.599', (string) $replacement['origQty'], 8), 'for exactly what is left');
        $this->assertContains('exit_missing', $this->eventKinds(), '…and says so');
    }

    // ────────────────────────────────────────────────────────────────────
    // ROUND 3 / 6 — reanchorLadder() blanks geomSig on every lot the flatten
    // carries off. The chase reprices the same lots every tick: the refit
    // must not be re-triggered by each reprice.
    // ────────────────────────────────────────────────────────────────────
    public function testTheChaseDoesNotRefitTheLadderEveryTick(): void
    {
        $run = $this->run;
        $run->setSellAtLoss(true);
        $run->save();
        $this->sim->balances = ['USDT' => ['free' => '5000', 'locked' => '0']];

        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();
        $this->sim->setPrice('100');
        $d->tick();                        // both buys fill; exits work

        $this->marketRunsAway();
        $this->sim->setPrice('124');
        $cmd = new \App\BotCommand();
        $cmd->setIdGridRun((int) $run->getIdGridRun());
        $cmd->setCommand('Flatten');
        $cmd->setCmdStatus('Pending');
        $cmd->save();
        $d->tick();                        // the lots are carried off the ladder

        $before = count(array_filter($this->eventKinds(), fn ($k) => $k === 'ladder_reanchor'));
        for ($i = 0; $i < 4; $i++) {
            $this->sim->setPrice(bcsub('124', (string) $i, 8));
            $d->tick();                    // the chase keeps repricing
        }
        $after = count(array_filter($this->eventKinds(), fn ($k) => $k === 'ladder_reanchor'));
        $this->assertSame($before, $after, 'a chase reprice must not re-anchor the ladder on every tick');
    }

    // ────────────────────────────────────────────────────────────────────
    // ROUND 3 / 7 — OrderStore::markPartFilled's RAW UPDATE keeps the
    // in-memory object in step, so a later save() from any other path must
    // not write a stale filled_qty back over it.
    // ────────────────────────────────────────────────────────────────────
    public function testARawFilledQtyRefreshSurvivesALaterSave(): void
    {
        $store = $this->store();
        $row = $store->recordOpen(
            new \App\Domains\Bot\IntendedOrder('Sell', 1, '150', '1'),
            static::uniq('cid'),
            '150',
            '1',
            '999'
        );
        $store->markPartFilled($row, '0.4');   // transition: a normal save
        $store->markPartFilled($row, '0.6');   // refresh: the raw UPDATE
        $store->markCanceled($row);            // …and some other path saves the object

        \App\BotOrderPeer::clearInstancePool();
        $fresh = BotOrderQuery::create()->findPk((int) $row->getIdBotOrder());
        $this->assertSame('Canceled', (string) $fresh->getState());
        $this->assertSame(
            0,
            bccomp('0.6', (string) $fresh->getFilledQty(), 8),
            'the refreshed fill must survive the next save of the same object'
        );
    }

    // ────────────────────────────────────────────────────────────────────
    // ROUND 3 / 8 — a legacy exit canceled mid-fill: the piece that traded
    // and the piece that is re-placed must split the pinned entry fee, not
    // book it twice.
    // ────────────────────────────────────────────────────────────────────
    public function testALegacyExitCanceledMidFillSplitsItsPinnedFeeOnce(): void
    {
        $d = $this->fillTheBuyAt125();
        $cid = $this->openSellCid();
        $this->assertNotNull($cid);
        $lot = (string) $this->sim->open[$cid]['origQty'];

        // carry the ladder exit off as a LEGACY exit, pinned like a handoff
        $row = BotOrderQuery::create()->findOneByClientOrderId($cid);
        $buy = $this->filledBuy();
        $this->store()->markLegacy($row, (string) $buy->getPrice(), (string) $buy->getFeePaid());

        $this->sim->partialFill($cid, '0.4');
        $this->externallyCancel($cid);
        $d->tick();                                   // legacy branch of bookCanceledRemainder

        $replacement = $this->openSell();
        $this->assertNotNull($replacement, 'the remainder keeps a legacy exit');
        $this->sim->setPrice('151');
        $d->tick();

        $this->assertSame(0, bccomp($lot, $this->totalSoldQty(), 8), 'the pieces add up to the lot');
        $this->assertClose(
            $this->totalCycleFees(),
            $this->feesReallyPaid(),
            '0.00000005',
            'a legacy lot exited in two pieces pays its entry fee once'
        );
    }

    // ────────────────────────────────────────────────────────────────────
    // ROUND 3 / 9 — the same refused re-place, seen after a RESTART. The
    // level was SELL_OPEN in MEMORY only, so boot hydrates it EMPTY and
    // forgets the lot: the ladder re-arms the buy and re-spends capital that
    // is still sitting in base. Boot has to name it.
    // ────────────────────────────────────────────────────────────────────
    public function testAFailedPartialRePlaceIsNamedAfterARestart(): void
    {
        $d = $this->fillTheBuyAt125();
        $cid = $this->openSellCid();
        $this->sim->beforeOrder = function (array $q): void {
            if ($q['side'] === 'SELL') {
                throw new \App\Domains\Bot\Gateway\BinanceApiError('Filter failure: PRICE_FILTER', 400, -1013);
            }
        };
        $this->sim->partialFill($cid, '0.4');
        $this->externallyCancel($cid);
        $d->tick();
        $orphan = $this->store()->trackedInventory(); // 0.599 of real base, no exit
        $this->assertSame(1, bccomp($orphan, '0', 8), 'fixture: real base, nothing guarding it');

        $this->sim->beforeOrder = null;
        \App\BotOrderPeer::clearInstancePool();
        \App\GridRunPeer::clearInstancePool();
        $run = GridRunQuery::create()->findPk((int) $this->run->getIdGridRun());
        $this->run = $run;
        $d2 = $this->daemon($run);
        $d2->boot();

        $this->assertContains(
            'orphan_inventory',
            $this->eventKinds(),
            sprintf(
                '%s base the ledger tracks is behind no exit and no engine position after the restart — boot must '
                . 'name it, or the ladder re-spends its capital in silence',
                $orphan
            )
        );
    }

    // ────────────────────────────────────────────────────────────────────
    // ROUND 3 / 10 — the partial-awareness path is on every grid cycle of
    // the PAPER fleet that runs on prod today. A plain buy→exit→fill cycle
    // must still be ONE cycle, for the whole lot.
    // ────────────────────────────────────────────────────────────────────
    public function testAPaperGridCycleIsStillOneWholeCycle(): void
    {
        $run = $this->run;
        $run->setSimulated(true);
        $run->save();

        $tape = '150';
        $transport = function (string $method, string $url, array $headers, ?string $body) use (&$tape): array {
            if (str_contains($url, '/api/v3/ticker/price')) {
                return ['status' => 200, 'headers' => [], 'body' => json_encode(['price' => $tape])];
            }
            if (str_contains($url, '/api/v3/exchangeInfo')) {
                return ['status' => 200, 'headers' => [], 'body' => json_encode(['symbols' => [[
                    'symbol' => 'BTCUSDT',
                    'filters' => [
                        ['filterType' => 'PRICE_FILTER', 'tickSize' => '0.01'],
                        ['filterType' => 'LOT_SIZE', 'stepSize' => '0.0001', 'minQty' => '0.0001'],
                        ['filterType' => 'NOTIONAL', 'minNotional' => '5'],
                    ],
                ]]])];
            }
            throw new \RuntimeException("unexpected HTTP call in paper mode: $url");
        };
        $public = new BinanceGateway('https://api.binance.com', '', '', $transport);
        $gw = new \App\Domains\Bot\Gateway\PaperGateway($public, $run, (string) $run->getFeePct());
        $d = new Daemon($run, $gw, false, new EventLog((int) $run->getIdGridRun(), false));
        $d->boot();
        $d->tick();          // buys armed at 100 and 125
        $tape = '124';
        $d->tick();          // L01 fills, exit armed at 150
        $tape = '151';
        $d->tick();          // the exit fills

        $store = new OrderStore(
            (int) $run->getIdGridRun(),
            (string) $run->getRunUid(),
            $run->getLedgerResetAt() ?: null,
            true
        );
        $this->assertCount(1, $this->cycles(), 'a paper grid cycle is one cycle, not a cycle plus a phantom remainder');
        $this->assertSame(0, bccomp('1', $this->totalSoldQty(), 8), 'the whole lot is booked');
        $this->assertNotContains('partial_dust', $this->eventKinds());
        $this->assertNotContains('unguarded_inventory', $this->eventKinds());
        $this->assertSame(0, bccomp('0', $store->trackedInventory(), 8), 'nothing is left held');
    }

    // ────────────────────────────────────────────────────────────────────
    // ROUND 3 / 11 — fillCommission's retry sleeps INSIDE the tick. Once per
    // unpriced fill it blocked the whole loop (kill switch, heartbeat, every
    // other exit) for tries × 300 ms and spent 3× the weight-20 myTrades
    // budget. A lagging trade list is a condition of the ACCOUNT, not of one
    // order: the first empty read in a tick is the last.
    // ────────────────────────────────────────────────────────────────────
    public function testTheTradeListRetrySleepsOncePerFillInsideTheTick(): void
    {
        putenv('GTBOT_FILL_TRADE_RETRIES');          // the shipped default
        $this->sim->tradeLag = 999;                  // the list never arrives
        $this->sim->balances = ['USDT' => ['free' => '5000', 'locked' => '0']];

        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();                                  // buys armed at 100 and 125

        $this->sim->setPrice('100');                 // BOTH buys fill in one tick
        $t0 = microtime(true);
        $d->tick();
        $elapsed = microtime(true) - $t0;

        // one 300 ms re-read for the FIRST unpriced fill, none for the second
        $this->assertLessThan(
            0.9,
            $elapsed,
            sprintf(
                'two fills in one tick blocked the daemon loop for %.2fs of usleep() inside fillCommission — '
                . 'the kill switch, the heartbeat and every other order in the tick wait behind it',
                $elapsed
            )
        );
        $this->assertCount(
            2,
            array_filter($this->eventKinds(), fn ($k) => $k === 'fill_unpriced'),
            'both fills are still journaled as estimates — only the re-reading stops'
        );
    }

    // ────────────────────────────────────────────────────────────────────
    // ROUND 3 / 12 — reanchorLadder() blanks geomSig while the run is
    // KILLED. The refit it asks for must not arm a single buy while the kill
    // stands (nor must reArmMissingExits, which runs in that branch too).
    // ────────────────────────────────────────────────────────────────────
    public function testARefitAskedForDuringAKillPlacesNoBuys(): void
    {
        $run = $this->run;
        $run->setSellAtLoss(true);
        $run->save();
        $this->sim->balances = ['USDT' => ['free' => '5000', 'locked' => '0']];

        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();
        $this->sim->setPrice('100');
        $d->tick();                        // both buys fill

        $this->marketRunsAway();
        $this->sim->setPrice('124');
        $cmd = new \App\BotCommand();
        $cmd->setIdGridRun((int) $run->getIdGridRun());
        $cmd->setCommand('Flatten');
        $cmd->setCmdStatus('Pending');
        $cmd->save();
        $d->tick();                        // kill+flatten; the lots are carried off

        $this->sim->setPrice('150');       // …and the tape goes back above the buy lines
        $d->tick();
        $d->tick();

        foreach ($this->sim->open as $o) {
            $this->assertSame('SELL', (string) $o['side'], 'a killed run must never arm a buy');
        }
        $this->assertTrue((bool) $run->getKillSwitch(), 'fixture: the run is still killed');
        $this->assertSame(
            [],
            array_values(array_filter(
                BotOrderQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->find()->getArrayCopy(),
                fn ($r) => (string) $r->getSide() === 'Buy' && (string) $r->getState() === 'BUY_OPEN'
            )),
            'the refit reanchorLadder asked for must not arm a buy while the kill stands'
        );
    }

    // ────────────────────────────────────────────────────────────────────
    // ROUND 3 / 13 — the SAME stranded state on the ORDINARY path: a buy
    // fills, GridEngine::onBuyFill asks placeIntent for the matched exit and
    // the exchange refuses it transiently. placeIntent deliberately keeps the
    // level SELL_OPEN ("a failed exit still guards held inventory") and
    // rethrows. Nothing used to put that exit back — no ledger row exists for
    // detectFills to resolve, and not one alert fired.
    // ────────────────────────────────────────────────────────────────────
    public function testAnExitThatNeverReachedTheBookIsPutBack(): void
    {
        $this->sim->balances = ['USDT' => ['free' => '5000', 'locked' => '0']];
        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();                                   // buys armed at 100 and 125

        // one transient refusal, on the exit the fill is about to ask for
        $refuse = true;
        $this->sim->beforeOrder = function (array $q) use (&$refuse): void {
            if ($q['side'] === 'SELL' && $refuse) {
                $refuse = false;
                throw new \App\Domains\Bot\Gateway\BinanceApiError('Internal error', 500, -1001);
            }
        };
        $this->sim->setPrice('124');
        try {
            $d->tick();                               // L01 fills; its exit is refused
        } catch (\App\Domains\Bot\Gateway\BinanceApiError) {
            // what the runner sees: api_error, then a backoff
        }
        $this->assertNotNull($this->filledBuy(), 'fixture: the buy really filled');
        $this->assertNull($this->openSell(), 'fixture: its exit never reached the book');

        // the exchange is healthy again
        $d->tick();

        $this->assertNotNull(
            $this->openSell(),
            'the level holds real base after a refused exit placement — some tick has to put the exit back; '
            . 'the machine says SELL_OPEN, there is no ledger row and no order, so detectFills can never see it'
        );
        $this->assertContains('exit_missing', $this->eventKinds());
    }
}
