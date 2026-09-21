<?php

namespace Tests\Custom\Bot;

use App\BotEventQuery;
use App\BotOrderQuery;
use App\Domains\Bot\Daemon;
use App\Domains\Bot\EventLog;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Indicators;
use App\Domains\Bot\MarketStore;
use App\Domains\Bot\TrendActivator;
use App\GridRun;
use PHPUnit\Framework\TestCase;
use Tests\Custom\Bot\Support\ExchangeSim;

/**
 * Regime entry (2026-09-05): a Donchian trend run whose activator marker
 * says ACTIVE (TREND_UP confirmed) enters on close > 1h EMA20 > EMA50
 * without a fresh 20-bar breakout. Prod run 1 sat flat 09-04 → 09-05 at
 * 79.7k under an 81.4k 20-bar high while every 15-min pass printed TREND_UP
 * — 211 USDT idle in the regime the arm exists for. Rule sweep:
 * scripts/trend-entry-sweep.php (arm B). The idle arm keeps the pure
 * breakout rule (no marker, or the last marker is a deactivate/release).
 */
class RegimeEntryTest extends TestCase
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

    private function makeTrendRun(): GridRun
    {
        $r = new GridRun();
        $r->setLabel('regime-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setAlgo('Trend');
        $r->setTrendSignal('Donchian');
        $r->setProfile('Balanced');
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualBase');
        $r->setBudgetQuote('400');
        $r->setDeployPct(50);
        $r->setTrendTf('1h');
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
        return $r;
    }

    private function makeDaemon(GridRun $run): Daemon
    {
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $d = new Daemon($run, $gw, false, new EventLog((int) $run->getIdGridRun(), false));
        $d->boot();
        return $d;
    }

    private function buys(GridRun $run): array
    {
        return BotOrderQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->filterBySide('Buy')->find()->getArrayCopy();
    }

    /**
     * 1h tape: 45 flat bars, 15 rising to 129, then 3 bars pulling back to
     * 124 — the close sits ABOVE the EMA20 > EMA50 stack (rising) but UNDER
     * the 20-bar prior high (no Donchian breakout).
     * @return array{ema20: float, ema50: float}
     */
    private function seed1hAlignedNoBreakout(): array
    {
        $candles = array_fill(0, 45, ['high' => '101', 'low' => '99', 'close' => '100']);
        for ($i = 0; $i < 15; $i++) {
            $c = (string) (101 + $i * 2);
            $candles[] = ['high' => $c, 'low' => (string) ((float) $c - 1), 'close' => $c];
        }
        foreach (['127', '125', '124'] as $c) {
            $candles[] = ['high' => (string) ((float) $c + 1), 'low' => (string) ((float) $c - 1), 'close' => $c];
        }
        $s = Indicators::summary($candles);
        $this->assertFalse(\App\Domains\Bot\Engine\TrendEngine::entrySignal($candles, 20, 20, 50), 'fixture: no breakout');
        $this->assertTrue(\App\Domains\Bot\Engine\TrendEngine::regimeEntrySignal($candles, 20, 50), 'fixture: close above EMA20 > EMA50');
        MarketStore::upsert('BTCUSDT', '1h', $s, $candles);
        return ['ema20' => (float) $s['ema20'], 'ema50' => (float) $s['ema50']];
    }

    /**
     * Put the run's arm in the state a marker event used to imply. Since
     * 2026-09-19 the arm state lives on the run's fleet slot; the marker
     * events are still written by the activator, but the engine reads the
     * slot (TrendActivator::state).
     */
    private function mark(GridRun $run, string $kind): void
    {
        (new EventLog((int) $run->getIdGridRun(), false))->write('Info', $kind, 'test marker', ['trend_after' => '400', 'deploy_after' => 50]);
        $id = (int) $run->getIdGridRun();
        $slot = \App\Domains\Bot\FleetSlots::byRun($id);
        if ($slot === null) {
            $slot = new \App\FleetSlot();
            $slot->setSymbol((string) $run->getSymbol());
            $slot->setAlgo('Trend');
            $slot->setTargetSlice('400');
            $slot->setIdGridRun($id);
        }
        $slot->setState(match ($kind) {
            TrendActivator::KIND_ACTIVATE => 'active',
            TrendActivator::KIND_DEACTIVATE => 'winding_down',
            default => 'idle',
        });
        $slot->setActivation(json_encode(['trend_after' => '400', 'deploy_after' => 50]));
        $slot->save();
    }

    public function testIdleArmKeepsTheBreakoutRule(): void
    {
        $run = $this->makeTrendRun();
        $this->seed1hAlignedNoBreakout();
        $this->sim->setPrice('124');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $daemon->tick();
        $this->assertCount(0, $this->buys($run), 'no marker → idle arm → Donchian only, no entry under the 20-bar high');
        $this->assertSame('idle', TrendActivator::state((int) $run->getIdGridRun()));
    }

    public function testActiveArmEntersOnTheEmaStackWithoutABreakout(): void
    {
        $run = $this->makeTrendRun();
        $this->seed1hAlignedNoBreakout();
        $this->mark($run, TrendActivator::KIND_ACTIVATE);
        $this->sim->setPrice('124');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $buys = $this->buys($run);
        $this->assertNotEmpty($buys, 'regime entry placed');
        $qty = '0';
        foreach ($buys as $b) {
            $qty = bcadd($qty, (string) $b->getQty(), 8);
        }
        $this->assertSame(0, bccomp($qty, bcdiv('200', '124', 8), 3), 'deploy 50% of 400 = 200 quote across the tranches (profile per-order cap splits it)');
        $ev = BotEventQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('trend_signal')->findOne();
        $this->assertNotNull($ev);
        $this->assertStringContainsString('regime entry', (string) $ev->getMessage());
        $this->assertStringContainsString('EMA20 > EMA50', (string) $ev->getMessage());
    }

    public function testDeactivatedArmDoesNotUseTheRegimeRule(): void
    {
        $run = $this->makeTrendRun();
        $this->seed1hAlignedNoBreakout();
        $this->mark($run, TrendActivator::KIND_ACTIVATE);
        $this->mark($run, TrendActivator::KIND_DEACTIVATE);
        $this->sim->setPrice('124');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $this->assertCount(0, $this->buys($run), 'winding_down → breakout rule only');
    }

    public function testRegimeEntryNeedsPriceAboveARisingStack(): void
    {
        // same shape but the pullback dives under the EMA20: stack still rising, price not above it
        $candles = array_fill(0, 45, ['high' => '101', 'low' => '99', 'close' => '100']);
        for ($i = 0; $i < 15; $i++) {
            $c = (string) (101 + $i * 2);
            $candles[] = ['high' => $c, 'low' => (string) ((float) $c - 1), 'close' => $c];
        }
        foreach (['120', '112', '106'] as $c) {
            $candles[] = ['high' => (string) ((float) $c + 1), 'low' => (string) ((float) $c - 1), 'close' => $c];
        }
        $this->assertFalse(\App\Domains\Bot\Engine\TrendEngine::regimeEntrySignal($candles, 20, 50));
        $this->assertFalse(\App\Domains\Bot\Engine\TrendEngine::regimeEntrySignal(array_slice($candles, -40), 20, 50), 'window shorter than emaSlow refuses');
    }
}
