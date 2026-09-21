<?php

namespace Tests\Custom\Bot;

use App\BotCommandQuery;
use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\FundsHold;
use App\GridRun;
use PHPUnit\Framework\TestCase;

/**
 * Dashboard "Hold funds" / "Release funds": hold parks a run in Halted
 * (freeing its slice from the BudgetGuard pool) after enqueueing
 * CancelBuys+Reload so the daemon exits cleanly; release re-checks the
 * budget invariant before putting the run back to Live for the watchdog.
 */
class FundsHoldTest extends TestCase
{
    private static bool $booted = false;

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
        $c = ConfigQuery::create()->findOneByConfig('gtbot_shared_budget_quote') ?? (new Config())->setConfig('gtbot_shared_budget_quote');
        $c->setValue('1000');
        $c->save();
        // Pinned at the shipped default (2026-09-21): hold() spreads, and
        // release() re-sizes, against cap x (1 - 5%) = 950 — both are
        // automatic allocations, so neither may hand out the reserve. The
        // reserve's own rules live in PoolReserveTest.
        $rp = ConfigQuery::create()->findOneByConfig(\App\Domains\Bot\BudgetPool::CONFIG_RESERVE) ?? (new Config())->setConfig(\App\Domains\Bot\BudgetPool::CONFIG_RESERVE);
        $rp->setValue(\App\Domains\Bot\BudgetPool::DEFAULT_RESERVE_PCT);
        $rp->save();
        \App\ConfigPeer::clearInstancePool();
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
        // Config rows edited here (use-all-funds flag) stay in Propel's instance
        // pool with their in-memory value after the rollback — drop them
        \App\ConfigPeer::clearInstancePool();
    }

    private function mkRun(string $budget, string $status = 'Testnet', string $algo = 'Grid'): GridRun
    {
        $r = new GridRun();
        $r->setAlgo($algo);
        $r->setLabel('fh-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus($status);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualBase');
        $r->setBudgetQuote($budget);
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid('fh');
        $r->save();
        return $r;
    }

    private function budget(int $id): string
    {
        return bcadd((string) \App\GridRunQuery::create()->findPk($id)->getBudgetQuote(), '0', 0);
    }

    private function setSharedBudget(string $v): void
    {
        $c = ConfigQuery::create()->findOneByConfig('gtbot_shared_budget_quote');
        $c->setValue($v);
        $c->save();
    }

    private function commands(GridRun $r): array
    {
        $out = [];
        foreach (BotCommandQuery::create()->filterByIdGridRun((int) $r->getIdGridRun())->orderByIdBotCommand()->find() as $c) {
            $out[] = (string) $c->getCommand();
        }
        return $out;
    }

    public function testHoldParksRunAndEnqueuesCleanExit(): void
    {
        $r = $this->mkRun('400', 'Live');
        $res = FundsHold::hold($r);
        $this->assertTrue($res['ok']);
        $r->reload();
        $this->assertSame('Halted', (string) $r->getStatus());
        $this->assertSame(['CancelBuys', 'Reload'], $this->commands($r));
    }

    public function testHoldRefusesNonActiveRun(): void
    {
        $r = $this->mkRun('400', 'Halted');
        $res = FundsHold::hold($r);
        $this->assertFalse($res['ok']);
        $this->assertSame([], $this->commands($r));
    }

    public function testReleaseRestoresLiveAndClearsKill(): void
    {
        $r = $this->mkRun('400', 'Halted');
        $r->setKillSwitch(true);
        $r->save();
        $res = FundsHold::release($r);
        $this->assertTrue($res['ok']);
        $r->reload();
        $this->assertSame('Live', (string) $r->getStatus());
        $this->assertFalse((bool) $r->getKillSwitch());
        // release enqueues nothing — the watchdog respawns Live runs on its own
        $this->assertSame([], $this->commands($r));
    }

    public function testReleaseTakesWhatHeadroomAllowsWhenPoolIsTight(): void
    {
        // no hold marker → the other grid is not touched; the idle headroom
        // under the FULL cap comes back (1000 − 700 = 300).
        //
        // CHANGED 2026-09-21 (review): this used to expect 250, the
        // allocatable headroom. Releasing a hold is an OPERATOR act and the
        // pool reserve exists to stop the MACHINE from sizing itself into the
        // guard — planning it against allocatable made Hold → Release lossy
        // and refused releases the cap could fund. Allocator::capacity() now
        // takes the pool to plan against, and FundsHold passes cap().
        $other = $this->mkRun('700', 'Live');
        $r = $this->mkRun('400', 'Halted');
        $res = FundsHold::release($r);
        $this->assertTrue($res['ok']);
        $this->assertStringContainsString('300 of 400', (string) $res['message']);
        $r->reload();
        $this->assertSame('Live', (string) $r->getStatus());
        $this->assertSame('300', $this->budget((int) $r->getIdGridRun()));
        $this->assertSame('700', $this->budget((int) $other->getIdGridRun()));
    }

    public function testReleaseRefusesWhenNothingCanBeFreed(): void
    {
        $this->mkRun('1000', 'Live');
        $r = $this->mkRun('400', 'Halted');
        $res = FundsHold::release($r);
        $this->assertFalse($res['ok']);
        $this->assertStringContainsString('overcommit', strtolower((string) $res['message']));
        $r->reload();
        $this->assertSame('Halted', (string) $r->getStatus());
        $this->assertSame('400', $this->budget((int) $r->getIdGridRun()));
    }

    public function testReleaseFitsUnderTheWalletCapWhenUseAllFundsIsOn(): void
    {
        $flag = ConfigQuery::create()->findOneByConfig(\App\Domains\Bot\BudgetPool::CONFIG_USE_ALL) ?? (new Config())->setConfig(\App\Domains\Bot\BudgetPool::CONFIG_USE_ALL);
        $flag->setValue('1');
        $flag->save();
        \Propel::getConnection()->exec('DELETE FROM sim_wallet');
        \Propel::getConnection()->exec("INSERT INTO sim_wallet (asset, qty, date_creation, date_modification) VALUES ('USDT', 1400, NOW(), NOW())");
        // the cap is min(wallet, gtbot_shared_budget_quote), so the seed has to
        // be above the wallet for the WALLET to be the binding constraint here
        $seed = ConfigQuery::create()->findOneByConfig('gtbot_shared_budget_quote') ?? (new Config())->setConfig('gtbot_shared_budget_quote');
        $seed->setValue('2000');
        $seed->save();
        \App\ConfigPeer::clearInstancePool();
        $this->mkRun('1000', 'Live');
        $r = $this->mkRun('400', 'Halted');
        $res = FundsHold::release($r);
        $this->assertTrue($res['ok'], $res['message']);
        $r->reload();
        $this->assertSame('Live', (string) $r->getStatus());
        // the wallet is the binding cap (1400), and the release plans against
        // that cap: 1400 − 1000 = 400, the whole slice it parked.
        //
        // CHANGED 2026-09-21 (review): was 330, the allocatable part. The
        // point of the test is that the WALLET, not gtbot_shared_budget_quote
        // (2000), bounds the release — still exactly what it pins: the run
        // could not have had more than 400 back here under either rule, and
        // a 1500 slice would still be refused.
        $this->assertSame('400', $this->budget((int) $r->getIdGridRun()));
        $this->assertNull(\App\Domains\Bot\BudgetGuard::check(), 'and the wallet cap still holds');
    }

    // ── redistribution: hold spreads the pool, release takes it back ────

    public function testPlanSpreadIsProRataWholeUsdtRemainderToLargest(): void
    {
        // 100 over 550/250: 68.75 → 68, 31.25 → 31, the leftover 1 goes to the largest
        $this->assertSame([4 => '619', 7 => '281'], FundsHold::planSpread('100', [4 => '550', 7 => '250']));
        $this->assertSame([4 => '550'], FundsHold::planSpread('0', [4 => '550']));
        $this->assertSame([], FundsHold::planSpread('100', []));
    }

    public function testHoldSpreadsFreedSliceOverTheGridsNotTheTrendArm(): void
    {
        $trend = $this->mkRun('200', 'Live', 'Trend');
        $a = $this->mkRun('400', 'Live');
        $b = $this->mkRun('200', 'Live');
        $c = $this->mkRun('200', 'Live');
        $res = FundsHold::hold($c);
        $this->assertTrue($res['ok']);
        // 200 freed, but only the allocatable headroom is spread:
        // 950 − (200 trend + 400 + 200) = 150 → a +100, b +50
        $this->assertSame('500', $this->budget((int) $a->getIdGridRun()));
        $this->assertSame('250', $this->budget((int) $b->getIdGridRun()));
        $this->assertSame('200', $this->budget((int) $trend->getIdGridRun()));
        $this->assertSame('200', $this->budget((int) $c->getIdGridRun()));
        $this->assertSame(['Reload'], $this->commands($a));
        $this->assertSame(['Reload'], $this->commands($b));
        $this->assertSame([], $this->commands($trend));
        $this->assertNull(\App\Domains\Bot\BudgetGuard::check());
        $this->assertStringContainsString('500', (string) $res['message']);
    }

    public function testHoldSpreadsPreExistingIdleHeadroomToo(): void
    {
        $a = $this->mkRun('400', 'Live');
        $c = $this->mkRun('200', 'Live');
        // 350 idle before the hold (950 − 600) + 200 freed → a takes all 550,
        // and stops on the allocatable cap rather than the guard's ceiling
        FundsHold::hold($c);
        $this->assertSame('950', $this->budget((int) $a->getIdGridRun()));
    }

    public function testHoldWithNoGridToTakeTheSliceStillParksTheRun(): void
    {
        $trend = $this->mkRun('200', 'Live', 'Trend');
        $c = $this->mkRun('200', 'Live');
        $res = FundsHold::hold($c);
        $this->assertTrue($res['ok']);
        $c->reload();
        $this->assertSame('Halted', (string) $c->getStatus());
        $this->assertSame('200', $this->budget((int) $trend->getIdGridRun()));
        $this->assertStringContainsString('no grid', strtolower((string) $res['message']));
    }

    public function testReleaseTakesTheSliceBackFromTheGridsDownToPreHold(): void
    {
        $trend = $this->mkRun('200', 'Live', 'Trend');
        $a = $this->mkRun('400', 'Live');
        $b = $this->mkRun('200', 'Live');
        $c = $this->mkRun('200', 'Live');
        FundsHold::hold($c);
        $c->reload();
        $res = FundsHold::release($c);
        $this->assertTrue($res['ok']);
        $c->reload();
        $this->assertSame('Live', (string) $c->getStatus());
        // a and b go back to their pre-hold slices exactly, and the run gets
        // back exactly what it parked.
        //
        // CHANGED 2026-09-21 (review): was 150 — the hold spread only the
        // allocatable headroom and the release could then only claw back that
        // much, so a round trip silently cost the run the reserve. The hold is
        // still a machine act planned against allocatable; the release is the
        // operator's and plans against the cap.
        $this->assertSame('200', $this->budget((int) $c->getIdGridRun()));
        $this->assertSame('400', $this->budget((int) $a->getIdGridRun()));
        $this->assertSame('200', $this->budget((int) $b->getIdGridRun()));
        $this->assertSame('200', $this->budget((int) $trend->getIdGridRun()));
        $this->assertSame(['Reload', 'Reload'], $this->commands($a));
        $this->assertNull(\App\Domains\Bot\BudgetGuard::check());
    }

    public function testReleaseNeverLowersAGridBelowItsPreHoldSlice(): void
    {
        $a = $this->mkRun('400', 'Live');
        $b = $this->mkRun('200', 'Live');
        $c = $this->mkRun('400', 'Live');
        FundsHold::hold($c);
        // meanwhile the pool shrank: the grids can only give back down to 400/200
        $this->setSharedBudget('800');
        $c->reload();
        $res = FundsHold::release($c);
        $this->assertTrue($res['ok']);
        // the lowered cap 800 − the 400/200 the grids keep = 200.
        //
        // CHANGED 2026-09-21 (review): was 160, the allocatable part of the
        // lowered pool. What this test pins is unchanged — neither grid goes
        // below its pre-hold slice, whatever the run is owed.
        $this->assertStringContainsString('200 of 400', (string) $res['message']);
        $this->assertSame('200', $this->budget((int) $c->getIdGridRun()));
        $this->assertSame('400', $this->budget((int) $a->getIdGridRun()));
        $this->assertSame('200', $this->budget((int) $b->getIdGridRun()));
        $this->assertNull(\App\Domains\Bot\BudgetGuard::check());
    }

    public function testReleaseRestoresPreHoldStatus(): void
    {
        $r = $this->mkRun('400', 'Testnet');
        FundsHold::hold($r);
        $r->reload();
        $this->assertSame('Halted', (string) $r->getStatus());
        $res = FundsHold::release($r);
        $this->assertTrue($res['ok']);
        $r->reload();
        // a Testnet run must not come back as Live
        $this->assertSame('Testnet', (string) $r->getStatus());
    }

    public function testReleaseRefusesRunNotOnHold(): void
    {
        $r = $this->mkRun('400', 'Live');
        $res = FundsHold::release($r);
        $this->assertFalse($res['ok']);
    }

    // ── dashboard visibility: a held run must keep its tab ──────────────

    public function testHaltedRunKeepsDashboardTab(): void
    {
        $r = $this->mkRun('400', 'Halted');
        $ids = array_map(
            static fn ($run) => (int) $run->getIdGridRun(),
            \App\Domains\Dashboard\DashboardData::activeRuns()
        );
        $this->assertContains((int) $r->getIdGridRun(), $ids);
    }

    public function testDefaultTabPrefersRunningRunOverHeld(): void
    {
        $held = $this->mkRun('100', 'Halted');
        $live = $this->mkRun('400', 'Live');
        $runs = \App\Domains\Dashboard\DashboardData::activeRuns();
        $picked = \App\Domains\Dashboard\DashboardData::selectRun($runs, null);
        $this->assertSame((int) $live->getIdGridRun(), (int) $picked->getIdGridRun());
        // an explicit ?run= request still lands on the held run
        $explicit = \App\Domains\Dashboard\DashboardData::selectRun($runs, (int) $held->getIdGridRun());
        $this->assertSame((int) $held->getIdGridRun(), (int) $explicit->getIdGridRun());
    }
}
