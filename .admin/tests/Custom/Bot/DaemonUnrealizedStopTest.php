<?php

namespace Tests\Custom\Bot;

use App\BotEventQuery;
use App\Domains\Bot\Daemon;
use App\Domains\Bot\EventLog;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\GridRun;
use PHPUnit\Framework\TestCase;
use Tests\Custom\Bot\Support\ExchangeSim;

/**
 * Mark-to-market drawdown must trip the kill switch (inventory held).
 *
 * The unrealized cap is now a LIVE profile-derived value (ProfilePolicy,
 * stamped every tick — see DaemonProfileTest), not a hand-set column: at the
 * default Balanced profile and this fixture's budget_quote (550), the cap is
 * 550 x 15% = 82.5, and the per-order cap (max_order_quote) is 550 x 30% =
 * 165. A 5-level EqualBase ladder over [100,200] keeps every single order's
 * notional (top level 180 x qty 0.785714 ≈ 141.4) under that per-order cap.
 *
 * holdingDaemon() drops price straight to 115 in one jump, so the top FOUR
 * levels (120/140/160/180) fill together (qty 3.142857, invested 471.428571,
 * blended cost ~150) while the lowest level (100) stays a resting BUY — that
 * gives ample headroom (down to the 100 floor, and further once killed and
 * that resting buy is cancelled) to clear the enter/exit hysteresis lines
 * and the hard cap without any per-order veto interfering.
 * unrealized(price) = 3.142857142857*price - 471.428571428571 once filled.
 */
class DaemonUnrealizedStopTest extends TestCase
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
        $r->setLabel('ustop-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(5); // keeps every single order's notional under the
        // profile-derived per-order cap (30% of budget) while still leaving
        // enough headroom, once several levels fill together, to clear the
        // profile-derived unrealized cap
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualBase');
        $r->setBudgetQuote('550'); // profile Balanced (default) -> cap 82.5
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.9'); // park the breakout stop far away
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid('ustop');
        $r->save();
        $this->run = $r;
        $this->sim = new ExchangeSim();
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    public function testDeepUnrealizedLossTripsKill(): void
    {
        $this->sim->setPrice('200'); // above every level — all 5 buys pending
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $d = new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
        $d->boot();
        $d->tick();
        $this->sim->setPrice('115'); // fills 120/140/160/180 together; 100 stays open
        $d->tick(); // holding 3.142857 @ blended cost ~150 (fill tick itself doesn't evaluate the fresh fill)

        // unrealized = 3.142857*115 - 471.428571 = -110.0 < -82.5 cap, held 3
        // more ticks (still above the parked breakout floor at 10, and above
        // the still-open 100-level buy) → kill
        $d->tick();
        $d->tick();
        $d->tick();

        $this->run->reload();
        $this->assertTrue((bool) $this->run->getKillSwitch(), 'unrealized stop must trip the kill switch');
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('unrealized_stop')->count());
    }

    public function testShallowDrawdownDoesNotTrip(): void
    {
        $this->sim->setPrice('200');
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $d = new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
        $d->boot();
        $d->tick();
        $this->sim->setPrice('175'); // fills only the 180 level
        $d->tick(); // holding 0.785714 @ 180; unrealized 0.785714*(175-180) = -3.93 > -82.5 cap
        $this->run->reload();
        $this->assertFalse((bool) $this->run->getKillSwitch());
    }

    // ── staged de-risking ───────────────────────────────────────────────

    private function holdingDaemon(): Daemon
    {
        $this->sim->setPrice('200');
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $d = new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
        $d->boot();
        $d->tick();                  // buys at 100/120/140/160/180 pending
        $this->sim->setPrice('115');
        $d->tick();                  // 120/140/160/180 fill together → holding 3.142857 @ ~150; 100 stays open
        return $d;
    }

    private function kindCount(string $kind): int
    {
        return BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind($kind)
            ->count();
    }

    public function testSoftDeriskPausesEntriesAtSixtyPercentOfCap(): void
    {
        $d = $this->holdingDaemon();
        // 2 resting sells exist after the fill (L01@140, L02@160 — the
        // L03/L04 sells got vetoed as implausibly far from the market at
        // 115 and never retry: only BUY-side vetoes un-arm for a retry)
        $sellsBefore = count(array_filter($this->sim->open, fn ($o) => $o['side'] === 'SELL'));
        $this->assertSame(2, $sellsBefore);

        // 3.142857*134 - 471.428571 = -50.29, past 60% of the 82.5 cap but under the hard stop
        $this->sim->setPrice('134');
        $d->tick();

        $this->run->reload();
        $this->assertFalse((bool) $this->run->getKillSwitch(), 'soft stage must NOT kill');
        $this->assertSame(1, $this->kindCount('derisk_on'));
        $buys = array_filter($this->sim->open, fn ($o) => $o['side'] === 'BUY');
        $this->assertCount(0, $buys, 'open buys cancelled — stop adding exposure into a drawdown');
        $this->assertCount($sellsBefore, array_filter($this->sim->open, fn ($o) => $o['side'] === 'SELL'), 'the exit keeps working — de-risk touches buys only');

        $d->tick(); // still de-risking: nothing re-arms
        $this->assertCount(0, array_filter($this->sim->open, fn ($o) => $o['side'] === 'BUY'));
        $this->assertSame(1, $this->kindCount('derisk_on'), 'alert fires once, not every tick');
    }

    public function testDeriskRecoversWithHysteresis(): void
    {
        $d = $this->holdingDaemon();
        $this->sim->setPrice('134'); // -50.29 (past 60% of cap) → de-risk
        $d->tick();

        // 3.142857*136 - 471.428571 = -44 has NOT recovered past the 40% (-33) re-arm line — stay paused
        $this->sim->setPrice('136');
        $d->tick();
        $this->assertCount(0, array_filter($this->sim->open, fn ($o) => $o['side'] === 'BUY'), 'hysteresis: 40% of cap is not recovered');

        // 3.142857*139.7 - 471.428571 = -32.4 clears the 40% line → entries re-arm
        // (stays under the 140 resting sell so no round-trip fires mid-test)
        $this->sim->setPrice('139.7');
        $d->tick();
        $this->assertSame(1, $this->kindCount('derisk_off'));
        $this->assertCount(1, array_filter($this->sim->open, fn ($o) => $o['side'] === 'BUY'), 'buy below price re-armed after recovery');
    }

    // ── wick guard: the hard kill needs the breach to HOLD ──────────────

    public function testSingleTickWickPrintDoesNotKill(): void
    {
        $d = $this->holdingDaemon(); // holding 3.142857 @ ~150

        // one aberrant print far past the cap (thin-book testnet wick,
        // prod 2026-07-28: 1889 → 1500 → 1878 inside a single candle); this
        // also crosses the still-open 100-level buy, but that only deepens
        // the mark-to-market loss, it doesn't change the assertions
        $this->sim->setPrice('15');
        $d->tick();
        $this->run->reload();
        $this->assertFalse((bool) $this->run->getKillSwitch(), 'one bogus print must not kill');
        $this->assertSame(1, $this->kindCount('unrealized_breach'), 'a pending breach must be visible');

        // the print reverts next tick — no kill happened, streak reset (now
        // holding all 5 levels @ cost 550: 3.928571*134 - 550 = -23.57, under cap)
        $this->sim->setPrice('134');
        $d->tick();
        $this->run->reload();
        $this->assertFalse((bool) $this->run->getKillSwitch());
        $this->assertSame(0, $this->kindCount('unrealized_stop'));
    }

    public function testSustainedBreachStillKillsAfterConfirmTicks(): void
    {
        $d = $this->holdingDaemon();

        // -110.0 < -82.5 cap, and it stays there
        $d->tick();
        $d->tick();
        $this->run->reload();
        $this->assertFalse((bool) $this->run->getKillSwitch(), 'two breach ticks are not yet confirmation');

        $d->tick(); // third consecutive breach tick → confirmed, kill
        $this->run->reload();
        $this->assertTrue((bool) $this->run->getKillSwitch(), 'a persistent breach must still kill');
        $this->assertSame(1, $this->kindCount('unrealized_stop'));
    }

    public function testRecoveryResetsBreachStreak(): void
    {
        $d = $this->holdingDaemon();

        $d->tick(); // breach (still @115)
        $d->tick();
        $this->sim->setPrice('134'); // -50.29: back inside the cap
        $d->tick();
        $this->sim->setPrice('115'); // breach again
        $d->tick();
        $d->tick();

        $this->run->reload();
        $this->assertFalse((bool) $this->run->getKillSwitch(), 'the streak must reset when the mark recovers');
    }

    public function testKilledDrawdownKeepsAlertingAsItWorsens(): void
    {
        $d = $this->holdingDaemon();

        // -110.0 < -82.5, held 3 more ticks → hard stop
        $d->tick();
        $d->tick();
        $d->tick();
        $this->run->reload();
        $this->assertTrue((bool) $this->run->getKillSwitch());

        // still holding while killed — each further half-cap (41.25) of
        // loss beyond the 82.5 cap alerts
        $this->sim->setPrice('105'); // -141.4: 58.9 beyond the cap → first step
        $d->tick();
        $this->assertSame(1, $this->kindCount('unrealized_worsening'));

        $this->sim->setPrice('90'); // -188.6: 106.1 beyond the cap → second step
        $d->tick();
        $this->assertSame(2, $this->kindCount('unrealized_worsening'));

        $d->tick(); // unchanged → no duplicate alert
        $this->assertSame(2, $this->kindCount('unrealized_worsening'), 'a killed hold must not spam, only escalate');
    }

    /**
     * NoLoss disables the unrealized-loss STOP (MaxUnrealizedLossQuote is
     * profile-derived null — ProfilePolicy::RULES), so this run never trips
     * checkUnrealizedStop on its own; kill it directly (a plain Kill stays
     * allowed for NoLoss) and confirm watchKilledDrawdown still escalates as
     * the paper loss deepens — mirrors testKilledDrawdownKeepsAlertingAsItWorsens
     * but for the no-cap profile, using the budget-derived alert unit
     * (budget_quote x 0.15 = 82.5) in place of half the (nonexistent) cap.
     */
    public function testKilledNoLossHoldStillAlertsAsUnrealizedLossWorsens(): void
    {
        $this->run->setProfile('NoLoss');
        $this->run->save();
        $d = $this->holdingDaemon(); // holding 3.142857 @ ~150; boot re-derives NoLoss caps (cap -> null)

        $this->run->setKillSwitch(true);
        $this->run->save();

        // alert unit = budget_quote(550) x 0.15 = 82.5
        $this->sim->setPrice('105'); // unrealized = 3.142857*105 - 471.428571 = -141.43 -> loss 141.43, step 1
        $d->tick();
        $this->assertSame(1, $this->kindCount('unrealized_worsening'));
        $this->run->reload();
        $this->assertNull($this->run->getMaxUnrealizedLossQuote(), 'NoLoss must keep the stop disabled even while alerting');

        $this->sim->setPrice('90'); // unrealized = 3.142857*90 - 471.428571 = -188.57 -> loss 188.57, step 2
        $d->tick();
        $this->assertSame(2, $this->kindCount('unrealized_worsening'));

        $d->tick(); // unchanged → no duplicate alert
        $this->assertSame(2, $this->kindCount('unrealized_worsening'), 'a killed NoLoss hold must not spam, only escalate');
    }
}
