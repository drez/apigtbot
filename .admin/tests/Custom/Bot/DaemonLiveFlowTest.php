<?php

namespace Tests\Custom\Bot;

use App\BotCommand;
use App\BotOrderQuery;
use App\Domains\Bot\Daemon;
use App\Domains\Bot\EventLog;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\GridRun;
use App\TradeCycleQuery;
use PHPUnit\Framework\TestCase;
use Tests\Custom\Bot\Support\ExchangeSim;

/**
 * M4 acceptance: full daemon lifecycle against the REAL project DB (inside a
 * rolled-back transaction) with the exchange emulated at the HTTP-transport
 * layer — so the real BinanceGateway request/parse code runs too.
 */
class DaemonLiveFlowTest extends TestCase
{
    private static bool $booted = false;
    private GridRun $run;
    private ExchangeSim $sim;

    public static function setUpBeforeClass(): void
    {
        if (self::$booted) {
            return;
        }
        $admin = dirname(__DIR__, 3);
        if (!is_file($admin . '/config/Built/config.php')) {
            self::markTestSkipped('project not built');
        }
        require_once $admin . '/vendor/autoload.php';
        (new \Ahc\Env\Loader())->load($admin . '/.env');
        if (!defined('_AUTH_VAR')) {
            require $admin . '/config/Built/config.php';
        }
        if (!\Propel::isInit()) {
            require $admin . '/config/Built/propel.php';
        }
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION[_AUTH_VAR] = new \ApiGoat\Sessions\AuthySession();
        self::$booted = true;
    }

    protected function setUp(): void
    {
        \Propel::getConnection()->beginTransaction();
        // the global drawdown stop has its own suite (DaemonDrawdownStopTest);
        // pin it OFF so this suite's scenarios only trip their own rails
        $dd = \App\ConfigQuery::create()->findOneByConfig('gtbot_max_drawdown_pct')
            ?? (new \App\Config())->setConfig('gtbot_max_drawdown_pct');
        $dd->setValue('0');
        $dd->save();
        $r = new GridRun();
        $r->setLabel('livetest-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualBase');
        $r->setBudgetQuote('550');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid(substr(bin2hex(random_bytes(6)), 0, 10));
        $r->save();
        $this->run = $r;
        $this->sim = new ExchangeSim();
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function daemon(): Daemon
    {
        $gateway = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        return new Daemon($this->run, $gateway, false, new EventLog((int) $this->run->getIdGridRun(), false));
    }

    /** Daemon with the stuck-partial timeout shrunk for tests. */
    private function daemonWithPartialTimeout(int $secs): Daemon
    {
        $gateway = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $log = new EventLog((int) $this->run->getIdGridRun(), false);
        return new class($this->run, $gateway, false, $log, $secs) extends Daemon {
            public function __construct($run, $gateway, $dryRun, $log, private readonly int $timeoutSecs)
            {
                parent::__construct($run, $gateway, $dryRun, $log);
            }

            protected function partialTimeoutSeconds(): int
            {
                return $this->timeoutSecs;
            }
        };
    }

    private function eventKinds(): array
    {
        return array_map(
            fn ($e) => (string) $e->getKind(),
            \App\BotEventQuery::create()
                ->filterByIdGridRun((int) $this->run->getIdGridRun())
                ->find()
                ->getArrayCopy()
        );
    }

    private function orders(): array
    {
        return BotOrderQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->orderByIdBotOrder()
            ->find()
            ->getArrayCopy();
    }

    public function testFullCycleAgainstRealDb(): void
    {
        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();
        // buys at 100 and 125 placed + persisted
        $this->assertCount(2, $this->sim->open);
        $open = array_filter($this->orders(), fn ($o) => (string) $o->getState() === 'BUY_OPEN');
        $this->assertCount(2, $open);

        $this->sim->setPrice('120'); // buy L1 fills
        $d->tick();
        $this->sim->setPrice('150'); // its sell at 150 fills
        $d->tick();

        $cycles = TradeCycleQuery::create()->filterByIdGridRun((int) $this->run->getIdGridRun())->find();
        $this->assertCount(1, $cycles);
        $this->assertSame(0, bccomp((string) $cycles[0]->getRealizedPnl(), '24.725', 6));
        // rearm: the L1 buy is live again on the book
        $buyCids = array_filter(array_keys($this->sim->open), fn ($c) => str_contains($c, '-B-'));
        $this->assertCount(2, $buyCids);
    }

    public function testCrashRecoveryProducesNoDuplicates(): void
    {
        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();
        $this->sim->setPrice('120');
        $d->tick(); // buy filled, sell open — now "crash"

        $bookBefore = array_keys($this->sim->open);
        $rowsBefore = count($this->orders());

        $d2 = $this->daemon(); // fresh process: rehydrate + reconcile
        $d2->boot();
        $d2->tick();

        $this->assertSame($bookBefore, array_keys($this->sim->open), 'restart must not place duplicate orders');
        $this->assertSame($rowsBefore, count($this->orders()), 'restart must not create duplicate rows');

        $this->sim->setPrice('150');
        $d2->tick(); // recovered daemon completes the cycle
        $this->assertSame(1, TradeCycleQuery::create()->filterByIdGridRun((int) $this->run->getIdGridRun())->count());
    }

    public function testDaemonLogsRestartWhenKillSwitchClears(): void
    {
        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();
        $this->run->reload();
        $this->run->setKillSwitch(true);
        $this->run->save();
        $d->tick(); // applies the kill (cancels buys, idles)
        $this->assertNotContains('restart', $this->eventKinds());

        // human clears the switch (dashboard button / GridRun edit)
        $this->run->reload();
        $this->run->setKillSwitch(false);
        $this->run->save();
        $d->tick(); // daemon notices → logs the restart in ITS OWN clock
        $this->assertContains('restart', $this->eventKinds(), 'daemon records the resume');
        $d->tick();
        $kinds = array_filter($this->eventKinds(), fn ($k) => $k === 'restart');
        $this->assertCount(1, $kinds, 'restart logged once per transition, not per tick');
    }

    public function testFillsMissedWhileDownAreAppliedOnBoot(): void
    {
        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();

        // daemon down; price dips and recovers: buy fills while nobody watched
        $this->sim->setPrice('120');

        $d2 = $this->daemon();
        $d2->boot(); // reconcile must resolve the missing buy as FILLED + arm the sell
        $sells = array_filter(array_keys($this->sim->open), fn ($c) => str_contains($c, '-S-'));
        $this->assertCount(1, $sells, 'missed buy fill must arm its exit during reconcile');
    }

    public function testKillCommandCancelsBuysAndIdlesWithoutExiting(): void
    {
        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();
        $this->assertCount(2, $this->sim->open);

        $cmd = new BotCommand();
        $cmd->setIdGridRun((int) $this->run->getIdGridRun());
        $cmd->setCommand('Kill');
        $cmd->setCmdStatus('Pending');
        $cmd->save();

        // new contract: kill cancels buys + trips the switch but the daemon
        // stays ALIVE (heartbeat only) — no exit, so systemd won't restart-loop
        $this->assertTrue($d->tick(), 'killed daemon keeps running (idle), does not exit');
        $this->assertSame([], array_filter($this->sim->open, fn ($o) => $o['side'] === 'BUY'), 'open buys canceled');
        $this->run->reload();
        $this->assertTrue((bool) $this->run->getKillSwitch());
        $cmd->reload();
        $this->assertSame('Done', (string) $cmd->getCmdStatus());

        // a second tick while killed places nothing new (stays idle)
        $before = count($this->sim->open);
        $d->tick();
        $this->assertSame($before, count($this->sim->open), 'killed daemon places no orders');
    }

    public function testGeometryChangeReanchorsGridWhenFlat(): void
    {
        // start flat BELOW the whole range (100-200) so no buys are open yet
        $this->sim->setPrice('50');
        $d = $this->daemon();
        $d->boot();
        $d->tick(); // no level is below 50 → no buys → flat
        $this->assertCount(0, $this->sim->open);

        // refit cron writes a new geometry onto the run row (range around 50)
        $this->run->reload();
        $this->run->setPLow('20');
        $this->run->setPHigh('100');
        $this->run->save();

        $this->sim->setPrice('50'); // inside the new [20,100] range
        $d->tick(); // geometry changed + flat → re-anchor, then place new ladder
        $this->assertGreaterThan(0, count($this->sim->open), 'new ladder placed on the re-anchored range');
        foreach ($this->sim->open as $o) {
            $this->assertGreaterThanOrEqual(20.0, (float) $o['price']);
            $this->assertLessThanOrEqual(100.0, (float) $o['price']);
        }
    }

    public function testGeometryChangeDeferredWhileHoldingInventory(): void
    {
        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();
        $this->sim->setPrice('120');
        $d->tick(); // a buy fills → open sell → holding inventory (not flat)
        $openSells = array_filter($this->sim->open, fn ($o) => $o['side'] === 'SELL');
        $this->assertNotEmpty($openSells);

        $this->run->reload();
        $this->run->setPLow('90');
        $this->run->setPHigh('130');
        $this->run->save();
        $this->sim->setPrice('110');
        $d->tick(); // geometry changed but holding inventory → deferred, sells intact
        $stillSells = array_filter($this->sim->open, fn ($o) => $o['side'] === 'SELL');
        $this->assertNotEmpty($stillSells, 'refit must not strand held inventory');
    }

    public function testWalletBalancesStampedOnTick(): void
    {
        $this->sim->balances = [
            'BTC' => ['free' => '0.002', 'locked' => '0.001'],
            'USDT' => ['free' => '900', 'locked' => '50'],
        ];
        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();
        $this->run->reload();
        $this->assertSame(0, bccomp((string) $this->run->getBalBase(), '0.003', 8), 'free+locked base stamped');
        $this->assertSame(0, bccomp((string) $this->run->getBalQuote(), '950', 8), 'free+locked quote stamped');
    }

    public function testBalanceStampThrottledBetweenFills(): void
    {
        $this->sim->balances = [
            'BTC' => ['free' => '0.002', 'locked' => '0'],
            'USDT' => ['free' => '900', 'locked' => '0'],
        ];
        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();
        $this->assertSame(1, $this->sim->accountCalls, 'first tick stamps');
        $d->tick();
        $d->tick();
        $this->assertSame(1, $this->sim->accountCalls, 'quiet ticks inside the interval do not re-fetch');

        $this->sim->setPrice('120'); // buy L1 fills
        $d->tick();                  // fill processed → balances marked dirty
        $d->tick();                  // dirty → re-stamp
        $this->assertSame(2, $this->sim->accountCalls, 'a fill triggers a fresh stamp');
    }

    public function testStuckPartialBuyBookedAfterTimeout(): void
    {
        $this->sim->setPrice('150');
        $d = $this->daemonWithPartialTimeout(0);
        $d->boot();
        $d->tick(); // buys at 100 and 125 placed
        $buyCid = null;
        foreach ($this->sim->open as $cid => $o) {
            if ($o['price'] === '125.00000000' || bccomp($o['price'], '125', 8) === 0) {
                $buyCid = $cid;
            }
        }
        $this->assertNotNull($buyCid);
        $this->sim->partialFill($buyCid, '0.40000000');
        $this->sim->setPrice('140'); // keep the coming 150 exit resting, not marketable

        $d->tick(); // partial detected → timer (0s) elapsed → remainder cancelled, fill booked

        $row = BotOrderQuery::create()->findOneByClientOrderId($buyCid);
        $this->assertSame('Filled', (string) $row->getState(), 'partial booked as filled');
        $this->assertSame(0, bccomp((string) $row->getFilledQty(), '0.4', 8));
        $this->assertArrayNotHasKey($buyCid, $this->sim->open, 'remainder cancelled on exchange');

        // matched sell placed for the ACTUAL bought qty, one level up (150)
        $sells = array_filter($this->sim->open, fn ($o) => $o['side'] === 'SELL');
        $this->assertCount(1, $sells);
        $sell = array_values($sells)[0];
        $this->assertSame(0, bccomp($sell['price'], '150', 8));
        $this->assertSame(0, bccomp($sell['origQty'], '0.4', 8));
        $this->assertContains('partial_timeout', $this->eventKinds());
    }

    public function testStuckPartialSellAlertsButStaysWorking(): void
    {
        $this->sim->setPrice('150');
        $d = $this->daemonWithPartialTimeout(0);
        $d->boot();
        $d->tick();
        $this->sim->setPrice('120'); // L1 buy fills
        $d->tick();                  // sell placed at 150
        $sellCids = array_keys(array_filter($this->sim->open, fn ($o) => $o['side'] === 'SELL'));
        $this->assertCount(1, $sellCids);
        $this->sim->partialFill($sellCids[0], '0.60000000');

        $d->tick(); // stuck partial sell → alert only

        $this->assertArrayHasKey($sellCids[0], $this->sim->open, 'exit stays working');
        $this->assertContains('stuck_partial_sell', $this->eventKinds());
        $this->assertNotContains('partial_timeout', $this->eventKinds(), 'sell side is never force-booked');
    }

    public function testRestartKeepsAppliedGeometryWhileHolding(): void
    {
        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick();                  // buys @100, @125
        $this->sim->setPrice('120'); // L1 fills
        $d->tick();                  // sell @150 placed — now holding inventory

        // refit cron writes new REQUESTED geometry while we hold
        $this->run->setPLow('110');
        $this->run->setPHigh('210');
        $this->run->save();

        // daemon restart (deploy) — must boot on the APPLIED [100,200] ladder
        $d2 = $this->daemon();
        $d2->boot();
        $this->assertNotContains('geometry_reset', $this->eventKinds(), 'orders match applied geometry — no reset');
        $d2->tick();
        // refit-with-inventory: the tick applies the new geometry — the old
        // buy re-anchors onto [110,210] while the exit is carried as legacy
        $this->assertContains('refit_applied', $this->eventKinds());
        $prices = array_map(fn ($o) => (float) $o['price'], $this->sim->open);
        sort($prices);
        $this->assertSame([110.0, 150.0], $prices, 'new-ladder buy@110 + legacy exit@150');
        $sell = \App\BotOrderQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterBySide('Sell')
            ->filterByState('SELL_OPEN')
            ->findOne();
        $this->assertTrue((bool) $sell->getIsLegacy(), 'the carried exit is tagged legacy');
    }

    public function testLegacyGeometryMismatchSelfHealsWithReset(): void
    {
        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $d->tick(); // buys @100, @125 on the [100,200] ladder

        // simulate legacy state: no applied stamp + an incompatible requested grid
        $this->run->setAppliedGeometry(null);
        $this->run->setPLow('300');
        $this->run->setPHigh('500');
        $this->run->save();

        $d2 = $this->daemon();
        $d2->boot(); // must NOT throw — self-heal instead
        $this->assertContains('geometry_reset', $this->eventKinds());
        $this->assertSame([], $this->sim->open, 'all our open orders canceled on the exchange');
        $this->run->reload();
        $this->assertTrue((bool) $this->run->getKillSwitch(), 'kill switch tripped for human review');
        $this->assertNotNull($this->run->getAppliedGeometry(), 'requested geometry stamped as the new applied ladder');
        $this->assertTrue($d2->tick(), 'daemon idles on heartbeat instead of crashing');
    }

    public function testRestartSticksAfterGeometryResetOrphanedInventory(): void
    {
        // prod 2026-07-22 13:41: pre-reset filled buys still fed investedQuote,
        // so the unrealized stop saw a phantom loss and re-killed the bot
        // within one tick of every restart — the button "did nothing".
        // The orphaned rows below are hand-inserted (bypassing OrderStore) so
        // they carry the column default simulated=0 — keep the run in REAL
        // mode too so the daemon's mode-filtered queries still see them.
        $this->run->setSimulated(false);
        $this->run->setMaxUnrealizedLossQuote('50');
        $this->run->save();
        $this->sim->setPrice('90');
        $d = $this->daemon();
        $d->boot(); // flat boot — machine tracks nothing

        // orphaned inventory: Filled buys from a previous grid era, no open
        // orders (exactly the post-geometry_reset prod state)
        foreach ([['150', '1'], ['125', '1']] as [$price, $qty]) {
            $o = new \App\BotOrder();
            $o->setIdGridRun((int) $this->run->getIdGridRun());
            $o->setClientOrderId('orphan-' . bin2hex(random_bytes(4)));
            $o->setLevelIdx(0);
            $o->setSide('Buy');
            $o->setState('Filled');
            $o->setPrice($price);
            $o->setQty($qty);
            $o->setFilledQty($qty);
            $o->save();
        }

        $this->run->reload();
        $this->run->setKillSwitch(true);
        $this->run->save();
        $d->tick(); // kill applied, idling

        // human presses Restart
        $this->run->reload();
        $this->run->setKillSwitch(false);
        $this->run->save();
        $d->tick();                  // restart + ledger rebase (orphans detected)
        $d->tick();                  // unrealized stop must NOT re-kill on phantom loss
        $this->run->reload();
        $this->assertFalse((bool) $this->run->getKillSwitch(), 'restart must stick — no phantom-loss re-kill');
        $this->assertNotNull($this->run->getLedgerResetAt('Y-m-d H:i:s'), 'ledger rebased');
        $this->assertContains('restart', $this->eventKinds());
        $this->assertContains('ledger_rebased', $this->eventKinds());
    }

    public function testFirstFlatBootStampsAppliedGeometry(): void
    {
        $this->sim->setPrice('150');
        $d = $this->daemon();
        $d->boot();
        $this->run->reload();
        $applied = json_decode((string) $this->run->getAppliedGeometry(), true);
        $this->assertIsArray($applied);
        $this->assertSame(0, bccomp($applied['p_low'], '100', 8));
    }

    public function testDistantBuysPrunedAndRearmedWithPriceMoves(): void
    {
        // 5 levels (not the fixture default 4): with the profile-derived
        // per-order cap (30% of budget_quote), the top buy level of a 4-level
        // [100,200] ladder (175 x qty 1 = 175) exceeds 0.3*550 = 165 and gets
        // vetoed outright — 5 levels keeps every level's notional under that
        // cap (top level 180 x qty 0.785714 ≈ 141.4) so this test can arm the
        // window's top level too.
        $this->run->setNLevels(5);
        $this->run->setMaxBuyLevelsBelow(2);
        $this->run->save();
        $this->sim->setPrice('130'); // buy levels below: 100 (L0), 120 (L1) → window covers both
        $d = $this->daemon();
        $d->boot();
        $d->tick();
        $buys = array_filter($this->sim->open, fn ($o) => $o['side'] === 'BUY');
        $this->assertCount(2, $buys);

        $this->sim->setPrice('190'); // window slides to L3 (160), L4 (180)
        $d->tick();
        $buys = array_filter($this->sim->open, fn ($o) => $o['side'] === 'BUY');
        $prices = array_map(fn ($o) => (float) $o['price'], $buys);
        sort($prices);
        $this->assertSame([160.0, 180.0], $prices, 'L0/L1 pruned, L3/L4 armed');
        $this->assertContains('distance_prune', $this->eventKinds());
        // pruned rows are Canceled in the ledger, not lost
        $canceled = BotOrderQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByState('Canceled')
            ->count();
        $this->assertSame(2, $canceled);
    }
}
