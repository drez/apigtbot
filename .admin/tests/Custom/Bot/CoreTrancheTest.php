<?php

namespace Tests\Custom\Bot;

use App\BotOrderQuery;
use App\Domains\Bot\Daemon;
use App\Domains\Bot\EventLog;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Indicators;
use App\Domains\Bot\MarketStore;
use App\GridRun;
use PHPUnit\Framework\TestCase;
use Tests\Custom\Bot\Support\ExchangeSim;

/**
 * Inventory core (trend_signal EmaCross1d) entries are STAGGERED through the
 * daemon: one tranche on the 1d cross, the rest on a pullback to the 1d
 * EMA20 or after TrendEngine::CORE_TRANCHE_SPACING — never the whole slice
 * in one tick (prod run 8, 2026-08-28: four tranches in the same second at
 * 79214, 3% under the swing high). The decision rule itself is unit-tested
 * in TrendEngineTest::testCoreTranchesStaggerOnPullbackOrTime; this covers
 * the wiring: placement count per tick, the engine_state bookkeeping that
 * survives a restart, and the pre-stagger marker (no tranche count) being
 * treated as fully deployed.
 */
class CoreTrancheTest extends TestCase
{
    private static bool $booted = false;
    private GridRun $run;
    private ExchangeSim $sim;
    private array $summary1d = [];

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
        putenv('GTBOT_TREND_REFRESH_STALE=0');
        putenv('GTBOT_TREND_AUTO_RELOAD_ON_DRIFT=0');
        $dd = \App\ConfigQuery::create()->findOneByConfig('gtbot_max_drawdown_pct')
            ?? (new \App\Config())->setConfig('gtbot_max_drawdown_pct');
        $dd->setValue('0');
        $dd->save();
        $this->sim = new ExchangeSim();
    }

    protected function tearDown(): void
    {
        putenv('GTBOT_TREND_REFRESH_STALE');
        putenv('GTBOT_TREND_AUTO_RELOAD_ON_DRIFT');
        \Propel::getConnection()->rollBack();
    }

    /** A core run: 400 slice, 120 per-order cap → four tranches of 100. */
    private function makeCoreRun(): GridRun
    {
        $r = new GridRun();
        $r->setLabel('core-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setAlgo('Trend');
        $r->setTrendSignal('EmaCross1d');
        $r->setProfile('Balanced');
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualBase');
        $r->setBudgetQuote('400');
        $r->setDeployPct(100);
        $r->setTrendTf('1h');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('120');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid(substr(bin2hex(random_bytes(6)), 0, 10));
        $r->save();
        $this->run = $r;
        return $r;
    }

    private function makeDaemon(GridRun $run): Daemon
    {
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $d = new Daemon($run, $gw, false, new EventLog((int) $run->getIdGridRun(), false));
        $d->boot();
        return $d;
    }

    private function marker(GridRun $run): array
    {
        $run->reload();
        return json_decode((string) ($run->getEngineState() ?? ''), true) ?: [];
    }

    private function buys(GridRun $run): array
    {
        return BotOrderQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->filterBySide('Buy')->find()->getArrayCopy();
    }

    /** 1d tape with the EMA20 above the EMA50 (45 flat bars then 15 rising). */
    private function seed1dUp(): void
    {
        $candles = array_fill(0, 45, ['high' => '101', 'low' => '99', 'close' => '100']);
        for ($i = 0; $i < 15; $i++) {
            $c = (string) (101 + $i * 2);
            $candles[] = ['high' => $c, 'low' => (string) ((float) $c - 1), 'close' => $c];
        }
        $this->summary1d = Indicators::summary($candles);
        $this->assertGreaterThan($this->summary1d['ema50'], $this->summary1d['ema20'], 'fixture: 1d cross is up');
        MarketStore::upsert('BTCUSDT', '1d', $this->summary1d, $candles);
    }

    public function testCoreEntersOneTrancheOnTheCrossNotTheWholeSlice(): void
    {
        $run = $this->makeCoreRun();
        $this->seed1dUp();
        $this->sim->setPrice('130');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();

        $this->assertCount(1, $this->buys($run), 'one tranche on the signal, not four');
        $m = $this->marker($run);
        $this->assertSame(0, bccomp((string) $m['qty'], bcdiv('100', '130', 8), 4), 'a single 100-quote tranche is held');
        $this->assertSame(1, (int) $m['tranches_placed']);
        $this->assertNotEmpty($m['last_tranche_at']);

        // price runs away, no pullback, seconds later: still one tranche
        $this->sim->setPrice('135');
        $daemon->tick();
        $daemon->tick();
        $this->assertCount(1, $this->buys($run), 'no second tranche without a pullback or the spacing');
    }

    public function testCoreAddsATrancheOnAPullbackToThe1dEma20(): void
    {
        $run = $this->makeCoreRun();
        $this->seed1dUp();
        $this->sim->setPrice('130');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $this->assertCount(1, $this->buys($run));

        // dip to just above the 1d EMA20 → second tranche
        $pullback = (string) round($this->summary1d['ema20'] * 1.005, 2);
        $this->sim->setPrice($pullback);
        $daemon->tick();
        $this->assertCount(2, $this->buys($run), 'pullback tranche placed');
        $m = $this->marker($run);
        $this->assertSame(2, (int) $m['tranches_placed']);
        $this->assertGreaterThan(0, bccomp((string) $m['qty'], bcdiv('100', '130', 8), 8), 'position grew');

        // still at the pullback price on the next tick: one tranche per tick at most,
        // and the spacing/pullback rule re-evaluates — a lingering pullback keeps adding
        $daemon->tick();
        $this->assertCount(3, $this->buys($run));
        $daemon->tick();
        $this->assertCount(4, $this->buys($run));
        $daemon->tick();
        $this->assertCount(4, $this->buys($run), 'never more than the tranche count');
        $this->assertSame(4, (int) $this->marker($run)['tranches_placed']);
    }

    public function testCoreAddsATrancheAfterTheSpacingWithoutAPullback(): void
    {
        $run = $this->makeCoreRun();
        $this->seed1dUp();
        $this->sim->setPrice('130');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $this->assertCount(1, $this->buys($run));

        // a restart after the spacing elapsed (the stamp is engine_state, so it survives)
        $daemon->persistEngineState(['last_tranche_at' => date('Y-m-d H:i:s', time() - \App\Domains\Bot\Engine\TrendEngine::CORE_TRANCHE_SPACING - 5)]);
        $this->sim->setPrice('140');
        $daemon2 = $this->makeDaemon($run);
        $daemon2->tick();
        $this->assertCount(2, $this->buys($run), 'time-fallback tranche placed at the higher price');
        $this->assertSame(2, (int) $this->marker($run)['tranches_placed']);
        $daemon2->tick();
        $this->assertCount(2, $this->buys($run), 'the stamp was refreshed by the placement');
    }

    public function testPreStaggerPositionWithoutATrancheCountIsTreatedAsFullyDeployed(): void
    {
        // prod run 8's marker (entered before the stagger shipped): entry/qty
        // but no tranches_placed — never top it up
        $run = $this->makeCoreRun();
        $this->seed1dUp();
        $this->sim->setPrice('130');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $this->assertCount(1, $this->buys($run));
        $m = $this->marker($run);
        unset($m['tranches_placed'], $m['last_tranche_at']);
        $run->setEngineState(json_encode($m));
        $run->save();

        $this->sim->setPrice((string) round($this->summary1d['ema20'] * 1.005, 2)); // a pullback, too
        $daemon2 = $this->makeDaemon($run);
        $daemon2->tick();
        $daemon2->tick();
        $this->assertCount(1, $this->buys($run), 'legacy marker: no top-up');
    }

    public function testCoreExitResetsTheTrancheCount(): void
    {
        $run = $this->makeCoreRun();
        $this->seed1dUp();
        $this->sim->setPrice('130');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $this->assertSame(1, (int) $this->marker($run)['tranches_placed']);

        // 1d cross down, price above cost (sell_at_loss OFF must still allow the exit)
        $candles = array_fill(0, 45, ['high' => '141', 'low' => '139', 'close' => '140']);
        for ($i = 0; $i < 15; $i++) {
            $c = (string) (139 - $i * 2);
            $candles[] = ['high' => (string) ((float) $c + 1), 'low' => $c, 'close' => $c];
        }
        $s = Indicators::summary($candles);
        $this->assertLessThan($s['ema50'], $s['ema20'], 'fixture: 1d cross is down');
        MarketStore::upsert('BTCUSDT', '1d', $s, $candles);
        $this->sim->setPrice('140');
        $daemon->tick();
        $daemon->tick();
        $m = $this->marker($run);
        $this->assertNull($m['entry'] ?? null, 'position closed on the cross down');
        $this->assertSame(0, (int) ($m['tranches_placed'] ?? -1));
        $this->assertArrayHasKey('tranches_placed', $m);
    }
}
