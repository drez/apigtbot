<?php

namespace Tests\Custom\Bot;

use App\BotEventQuery;
use App\Domains\Bot\Daemon;
use App\Domains\Bot\EventLog;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\GridRun;
use App\TradeCycleQuery;
use PHPUnit\Framework\TestCase;
use Tests\Custom\Bot\Support\ExchangeSim;

/**
 * grid_run.sell_when_starved (default OFF) — the operator's rule, 2026-09-02:
 * "selling at a loss is a problem unless no funds are available". With the
 * switch ON, a GRID run whose ladder cannot fund a single entry (the
 * no_budget episode: deployed slice minus the legacy reserve under the
 * ladder's minimum) reprices its legacy exits DOWN to the live price —
 * oldest first, only as many as it takes to fund the ladder again — and
 * rebuilds the ladder once they fill. It never fires outside a starved
 * episode, never touches the ladder's own working sells, and does nothing
 * when even a full release could not fund the ladder (slice too small).
 * OFF keeps today's behaviour: the exits stay at their price, the run idles.
 */
class SellWhenStarvedTest extends TestCase
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
        $dd = \App\ConfigQuery::create()->findOneByConfig('gtbot_max_drawdown_pct')
            ?? (new \App\Config())->setConfig('gtbot_max_drawdown_pct');
        $dd->setValue('0');
        $dd->save();
        $this->sim = new ExchangeSim();
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function makeRun(array $over = []): GridRun
    {
        $r = new GridRun();
        $r->setLabel('sws-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setAlgo('Grid');
        $r->setProfile('Balanced');
        if (isset($over['sell_when_starved'])) {
            $r->setSellWhenStarved((bool) $over['sell_when_starved']);
        }
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualBase');
        $r->setBudgetQuote('550');
        $r->setDeployPct(100);
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

    /** @return string[] */
    private function kinds(): array
    {
        return array_map(
            fn ($e) => (string) $e->getKind(),
            BotEventQuery::create()->filterByIdGridRun((int) $this->run->getIdGridRun())->find()->getArrayCopy()
        );
    }

    private function countKind(string $kind): int
    {
        return count(array_keys($this->kinds(), $kind, true));
    }

    /** @return string[] prices of the open SELLs on the simulated book */
    private function openSellPrices(): array
    {
        $p = array_map(fn ($o) => (string) round((float) $o['price'], 2), array_filter($this->sim->open, fn ($o) => $o['side'] === 'SELL'));
        sort($p);
        return array_values($p);
    }

    /**
     * Same fixture as SellAtLossTest::holdInventoryThenStarveTheSlice: buys
     * at 100/125 fill, their exits work at 125/150; a refit shrinks the slice
     * to $slice and carries those exits as legacy → their reserve exceeds
     * the slice, the new ladder cannot fund a single entry (no_budget).
     */
    private function starve(array $over = [], string $slice = '200'): array
    {
        $run = $this->makeRun($over);
        $this->sim->setPrice('150');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $this->sim->setPrice('100');
        $daemon->tick();
        $this->sim->setPrice('120');
        $daemon->tick();
        $this->assertSame(['125', '150'], $this->openSellPrices(), 'fixture: two exits working');
        $run->reload();
        $run->setPLow('112');
        $run->setPHigh('192');
        $run->setBudgetQuote($slice);
        $run->save();
        $daemon->tick();
        $daemon->tick();
        $this->assertSame(1, $this->countKind('no_budget'), 'fixture: the run is starved');
        return [$run, $daemon];
    }

    /**
     * 2026-09-05: the legacy reserve eats the UNDEPLOYED part of the slice
     * first — the ladder gets min(deployed, slice − reserve). Same fixture
     * (reserve 275 behind the 125/150 exits) on a 550 slice at deploy 45%:
     * deployed 247.5 < reserve 275 used to read as starved and, with the
     * switch ON, sold both lots at the live 120; now slice − reserve = 275
     * caps nothing, the ladder allocates the full 247.5 and nothing is sold.
     */
    public function testReserveEatsTheUndeployedSliceFirst(): void
    {
        $run = $this->makeRun(['sell_when_starved' => true]);
        $this->sim->setPrice('150');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $this->sim->setPrice('100');
        $daemon->tick();
        $this->sim->setPrice('120');
        $daemon->tick();
        $this->assertSame(['125', '150'], $this->openSellPrices(), 'fixture: two exits working');
        $run->reload();
        $run->setPLow('112');
        $run->setPHigh('192');
        $run->setDeployPct(45);
        $run->save();
        $daemon->tick();
        $daemon->tick();
        $this->assertSame(0, $this->countKind('no_budget'), 'deployed 247.5 is fully free (slice 550 − reserve 275 = 275)');
        $this->assertSame(['125', '150'], $this->openSellPrices(), 'nothing is sold below cost');
        $this->assertNotContains('starved_release', $this->kinds());
        $buys = array_filter($this->sim->open, fn ($o) => $o['side'] === 'BUY');
        $this->assertNotEmpty($buys, 'the ladder is funded from the deployed fraction');

        // the whole slice really tied up: 279 slice at deploy 45% → min(125.55, 4) = 4 < 5 → starved,
        // but the deployed 125.55 cannot fund 3 × 5 … it can (15) → one release, closest to market first
        $run->reload();
        $run->setBudgetQuote('279');
        $run->save();
        $daemon->tick();
        $daemon->tick();
        $this->assertSame(1, $this->countKind('no_budget'), 'slice − reserve = 4 < min notional: starved');
        $this->assertSame(['150'], $this->openSellPrices(), 'only the 125 exit was released');
    }

    public function testDefaultsOff(): void
    {
        $run = $this->makeRun();
        $run->reload();
        $this->assertFalse((bool) $run->getSellWhenStarved());
    }

    public function testOffKeepsTheExitsAtTheirPrice(): void
    {
        [, $daemon] = $this->starve();
        $daemon->tick();
        $this->assertSame(['125', '150'], $this->openSellPrices(), 'nothing is sold below cost');
        $this->assertNotContains('starved_release', $this->kinds());
    }

    public function testOnRepricesLegacyExitsToMarketAndRebuildsTheLadder(): void
    {
        // reserve 275 (1 base @125 + 1 base @150) on a 130 slice: releasing
        // the 125 exit alone leaves 130 − 150 < 0, so both are repriced down
        // to the live 120 (the simulated book fills a sell at market at once)
        [$run] = $this->starve(['sell_when_starved' => true], '130');

        $this->assertSame(2, $this->countKind('starved_release'), 'one event per released exit');
        $ev = BotEventQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('starved_release')->findOne();
        $this->assertSame('Alert', (string) $ev->getLevel(), 'a loss-realizing sell is operator news (Alert → Telegram)');
        $this->assertStringContainsString('@ 125.00000000) repriced to the live 120', (string) $ev->getMessage(), 'closest to the market first');
        $this->assertSame([], $this->openSellPrices(), 'both released exits filled at the live price');

        // the cycles book — one of them at a loss (bought 125, sold 120)
        $cycles = TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->find()->getArrayCopy();
        $this->assertCount(2, $cycles);
        $realized = array_map(fn ($c) => (float) $c->getRealizedPnl(), $cycles);
        $this->assertLessThan(0, min($realized), 'the 125-cost exit realized a loss');
        $this->assertGreaterThan(0, max($realized), 'the 100-cost exit realized a profit');

        // the freed reserve refunds the ladder without a routine refit
        $this->assertSame(1, $this->countKind('starved_rebuild'));
        $buys = array_filter($this->sim->open, fn ($o) => $o['side'] === 'BUY');
        $this->assertNotEmpty($buys, 'ladder rebuilt on the freed budget, buys working');
        $this->assertSame(1, $this->countKind('no_budget'), 'the starved episode ended, no repeat');
    }

    public function testOnReleasesOnlyTheExitsTheLadderNeedsClosestToMarketFirst(): void
    {
        // slice 279 vs reserve 275: 4 free (< 5 min notional → starved);
        // releasing the exit closest to the market (125, the smaller loss)
        // leaves 129 ≥ the ladder's 4 × 5 minimum → the 150 exit stays put
        [, $daemon] = $this->starve(['sell_when_starved' => true], '279');
        $this->assertSame(['150'], $this->openSellPrices(), 'only the 125 exit was released');
        $this->assertSame(1, $this->countKind('starved_release'));
        $daemon->tick();
        $daemon->tick();
        $this->assertSame(['150'], $this->openSellPrices(), 'the 150 exit is never chased once the ladder is funded');
        $this->assertSame(1, $this->countKind('starved_release'));
        $this->assertSame(1, $this->countKind('starved_rebuild'));
    }
}
