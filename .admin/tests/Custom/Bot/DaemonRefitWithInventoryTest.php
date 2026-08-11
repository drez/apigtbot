<?php

namespace Tests\Custom\Bot;

use App\BotEventQuery;
use App\BotOrderQuery;
use App\Domains\Bot\Daemon;
use App\Domains\Bot\EventLog;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\GridRun;
use App\TradeCycleQuery;
use PHPUnit\Framework\TestCase;
use Tests\Custom\Bot\Support\ExchangeSim;

/**
 * Refit-with-inventory: a geometry change no longer waits for a flat grid.
 * Open buys are cancelled, working sells become LEGACY EXITS (kept on the
 * exchange, detached from the ladder), and the new buy ladder goes to work
 * immediately on the budget not tied up in those exits. A legacy exit that
 * fills books its cycle against its original buy; one that's cancelled is
 * re-placed (it guards real inventory).
 */
class DaemonRefitWithInventoryTest extends TestCase
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
        $r->setLabel('refitinv-' . bin2hex(random_bytes(4)));
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

    private function eventKinds(): array
    {
        return array_map(
            fn ($e) => (string) $e->getKind(),
            BotEventQuery::create()
                ->filterByIdGridRun((int) $this->run->getIdGridRun())
                ->find()
                ->getArrayCopy()
        );
    }

    /** Boot on [100,200]×4, fill the 125 buy so a sell works at 150. */
    private function reachHoldingState(Daemon $d): void
    {
        $this->sim->setPrice('150');
        $d->boot();
        $d->tick();               // buys at 100 + 125
        $this->sim->setPrice('120');
        $d->tick();               // 125 buy fills → sell placed at 150
        $this->assertCount(1, array_filter($this->sim->open, fn ($o) => $o['side'] === 'SELL'));
    }

    /** The old ladder's sell cid (the future legacy exit). */
    private function sellCid(): string
    {
        foreach ($this->sim->open as $cid => $o) {
            if ($o['side'] === 'SELL') {
                return $cid;
            }
        }
        $this->fail('no open sell on the sim');
    }

    private function writeNewGeometry(): void
    {
        $this->run->reload();
        $this->run->setPLow('112');
        $this->run->setPHigh('192');
        $this->run->save();
    }

    public function testRefitAppliesWhileHoldingAndCarriesSellAsLegacyExit(): void
    {
        $d = $this->daemon();
        $this->reachHoldingState($d);
        $sellCid = $this->sellCid();

        $this->writeNewGeometry();
        $d->tick(); // price still 120

        $kinds = $this->eventKinds();
        $this->assertContains('refit_applied', $kinds, 'holding inventory must no longer block a refit');
        $this->assertNotContains('refit_pending', $kinds);

        // the exit survived the refit on the exchange, tagged legacy in the DB
        $this->assertArrayHasKey($sellCid, $this->sim->open, 'the working exit must NOT be cancelled');
        $row = BotOrderQuery::create()->findOneByClientOrderId($sellCid);
        $this->assertTrue((bool) $row->getIsLegacy());
        $this->assertSame('SELL_OPEN', (string) $row->getState());

        // new ladder buys are armed (112 is the only line under price 120)
        $newBuys = array_filter($this->sim->open, fn ($o) => $o['side'] === 'BUY');
        $this->assertCount(1, $newBuys);
        $this->assertEqualsWithDelta(112.0, (float) reset($newBuys)['price'], 0.001);
    }

    public function testNewLadderBudgetExcludesCapitalTiedInLegacyExits(): void
    {
        $d = $this->daemon();
        $this->reachHoldingState($d);
        $this->writeNewGeometry();
        $d->tick();

        // reserve = 1.0 × 150 (exit qty × its price) → 400 left for the new
        // ladder; EqualBase over buy lines 112+132+152+172 → ~0.7042 each
        $newBuys = array_filter($this->sim->open, fn ($o) => $o['side'] === 'BUY');
        $this->assertEqualsWithDelta(400 / 568, (float) reset($newBuys)['origQty'], 0.001, 'ladder must not re-spend capital the legacy exit still holds');
    }

    public function testLegacyExitFillBooksCycleAgainstItsOriginalBuy(): void
    {
        $d = $this->daemon();
        $this->reachHoldingState($d);
        $sellCid = $this->sellCid();
        $this->writeNewGeometry();
        $d->tick();

        $this->assertContains('refit_applied', $this->eventKinds());
        $this->sim->setPrice('150'); // legacy exit fills; nothing else crosses
        $d->tick();

        $this->assertArrayNotHasKey($sellCid, $this->sim->open);
        $cycles = TradeCycleQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->find()
            ->getArrayCopy();
        $this->assertCount(1, $cycles);
        $this->assertEqualsWithDelta(125.0, (float) $cycles[0]->getBuyPrice(), 0.001, 'cycle must match the OLD grid buy, not a new-ladder line');
        $this->assertEqualsWithDelta(150.0, (float) $cycles[0]->getSellPrice(), 0.001);
        // 25 gross − (0.125 buy fee + 0.15 sell fee)
        $this->assertEqualsWithDelta(24.725, (float) $cycles[0]->getRealizedPnl(), 0.01);
        $this->assertContains('cycle_closed', $this->eventKinds());

        // the legacy level does NOT re-arm a sell — the exit is done
        $this->assertCount(0, array_filter($this->sim->open, fn ($o) => $o['side'] === 'SELL'));
    }

    public function testRestartWithLegacyExitDoesNotGeometryReset(): void
    {
        $d = $this->daemon();
        $this->reachHoldingState($d);
        $this->writeNewGeometry();
        $d->tick();

        $this->assertContains('refit_applied', $this->eventKinds());
        // fresh process: boot must accept the off-ladder legacy exit
        $d2 = $this->daemon();
        $d2->boot();
        $d2->tick();

        $this->assertNotContains('geometry_reset', $this->eventKinds(), 'a legacy exit off the new ladder is expected, not corruption');
        $this->assertCount(1, array_filter($this->sim->open, fn ($o) => $o['side'] === 'SELL'), 'legacy exit still working after restart');
    }

    public function testUnrealizedStopCountsLegacyInventory(): void
    {
        $d = $this->daemon();
        $this->reachHoldingState($d);
        $this->writeNewGeometry();
        $d->tick();

        $this->assertContains('refit_applied', $this->eventKinds());
        $this->run->reload();
        // the cap is now profile-derived (live, re-stamped every tick) —
        // Cautious x 100 budget = 10, matching this test's original hand-set cap
        $this->run->setProfile('Cautious');
        $this->run->setBudgetQuote('100');
        $this->run->save();

        // 118: legacy mark 1×118 vs 125 invested → −7, inside the cap
        $this->sim->setPrice('118');
        $d->tick();
        $this->run->reload();
        $this->assertFalse((bool) $this->run->getKillSwitch());

        // 114: −11 breaches the cap — the stop must SEE legacy inventory
        // (held 3 consecutive ticks to clear the wick guard)
        $this->sim->setPrice('114');
        $d->tick();
        $d->tick();
        $d->tick();
        $this->run->reload();
        $this->assertTrue((bool) $this->run->getKillSwitch(), 'legacy inventory must stay under the unrealized stop');
        $this->assertContains('unrealized_stop', $this->eventKinds());
    }

    public function testCancelledLegacyExitIsReplaced(): void
    {
        $d = $this->daemon();
        $this->reachHoldingState($d);
        $sellCid = $this->sellCid();
        $this->writeNewGeometry();
        $d->tick();

        // someone cancels the exit on the exchange
        $o = $this->sim->open[$sellCid];
        $o['status'] = 'CANCELED';
        $this->sim->done[$sellCid] = $o;
        unset($this->sim->open[$sellCid]);

        $d->tick();

        $sells = array_filter($this->sim->open, fn ($o) => $o['side'] === 'SELL');
        $this->assertCount(1, $sells, 'the exit guards real inventory — it must come back');
        $replaced = reset($sells);
        $this->assertEqualsWithDelta(150.0, (float) $replaced['price'], 0.001);
        $this->assertEqualsWithDelta(1.0, (float) $replaced['origQty'], 0.001);
        $newRow = BotOrderQuery::create()->findOneByClientOrderId($replaced['clientOrderId']);
        $this->assertTrue((bool) $newRow->getIsLegacy(), 're-placed exit stays legacy');
    }
}
