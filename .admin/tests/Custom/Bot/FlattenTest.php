<?php

namespace Tests\Custom\Bot;

use App\BotCommand;
use App\BotEventQuery;
use App\BotOrderQuery;
use App\Domains\Bot\Daemon;
use App\Domains\Bot\EventLog;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Indicators;
use App\Domains\Bot\MarketStore;
use App\GridRun;
use App\TradeCycleQuery;
use PHPUnit\Framework\TestCase;
use Tests\Custom\Bot\Support\ExchangeSim;

/**
 * Flatten actually liquidates (2026-09-18). Until then "v1 flatten" cancelled
 * buys, left working sells alone and logged 'review inventory manually' — so
 * a commanded close never closed anything and a core Trend run (no trailing
 * stop) had no seller at all.
 *
 * Now: working exits are repriced to the live mark, and an engine position
 * with no working exit is sold at the mark. The loss rules still bind — with
 * grid_run.sell_at_loss OFF only lots at or above THEIR OWN cost are sold and
 * the rest are left working, with an Alert naming what stayed.
 */
class FlattenTest extends TestCase
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

    /** @param array<string,string|int|bool> $over */
    private function makeRun(array $over = []): GridRun
    {
        $r = new GridRun();
        $r->setLabel('flat-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setAlgo($over['algo'] ?? 'Grid');
        $r->setProfile($over['profile'] ?? 'Balanced');
        if (isset($over['sell_at_loss'])) {
            $r->setSellAtLoss((bool) $over['sell_at_loss']);
        }
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualBase');
        $r->setBudgetQuote($over['budget_quote'] ?? '550');
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

    private function enqueueFlatten(GridRun $run): BotCommand
    {
        $cmd = new BotCommand();
        $cmd->setIdGridRun((int) $run->getIdGridRun());
        $cmd->setCommand('Flatten');
        $cmd->setCmdStatus('Pending');
        $cmd->save();
        return $cmd;
    }

    /** @return string[] */
    private function kinds(): array
    {
        return array_map(
            fn ($e) => (string) $e->getKind(),
            BotEventQuery::create()->filterByIdGridRun((int) $this->run->getIdGridRun())->find()->getArrayCopy()
        );
    }

    private function openSells(GridRun $run): array
    {
        return array_values(array_filter(
            BotOrderQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->filterBySide('Sell')->find()->getArrayCopy(),
            fn ($r) => (string) $r->getState() === 'SELL_OPEN'
        ));
    }

    private function marker(GridRun $run): ?array
    {
        $run->reload();
        $raw = (string) ($run->getEngineState() ?? '');
        return $raw === '' ? null : json_decode($raw, true);
    }

    private function seedBreakoutCandles(): void
    {
        $candles = array_fill(0, 45, ['high' => '101', 'low' => '99', 'close' => '100']);
        for ($i = 0; $i < 15; $i++) {
            $c = (string) (101 + $i * 2);
            $candles[] = ['high' => $c, 'low' => (string) ((float) $c - 1), 'close' => $c];
        }
        MarketStore::upsert('BTCUSDT', '1h', Indicators::summary($candles), $candles);
    }

    /** A Trend run holding a position entered @130, with no working exit —
     *  the shape of prod run 8 (core: trend_signal EmaCross1d, stop null). */
    private function openTrendPosition(array $over = []): array
    {
        $run = $this->makeRun(array_merge(['algo' => 'Trend', 'budget_quote' => '1000'], $over));
        $this->seedBreakoutCandles();
        $this->sim->setPrice('130');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $marker = $this->marker($run);
        $this->assertGreaterThan(0, bccomp((string) ($marker['qty'] ?? '0'), '0', 8), 'fixture: the position opened');
        return [$run, $daemon];
    }

    // ── trend ───────────────────────────────────────────────────────

    public function testTrendFlattenSellsTheWholePositionAboveCost(): void
    {
        [$run, $daemon] = $this->openTrendPosition();

        $this->sim->setPrice('200');
        $this->enqueueFlatten($run);
        $daemon->tick(); // gate passes, the liquidation sell goes out and fills

        $this->assertCount(0, $this->openSells($run), 'nothing left working');
        $cycle = TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->findOne();
        $this->assertNotNull($cycle, 'the exit was booked');
        $this->assertGreaterThan(0, bccomp((string) $cycle->getRealizedPnl(), '0', 8), 'as a gain');
        $marker = $this->marker($run);
        $this->assertSame(0, bccomp((string) ($marker['qty'] ?? '0'), '0', 8), 'the position is closed');
    }

    public function testTrendFlattenIsNotBlockedByItsOwnKillSwitch(): void
    {
        // the kill switch is set by the same command that orders the flatten,
        // and RiskManager vetoes every order while it is tripped — the
        // liquidation sell is the one order that must still go out
        [$run, $daemon] = $this->openTrendPosition();
        $this->sim->setPrice('200');
        $this->enqueueFlatten($run);
        $daemon->tick();

        $run->reload();
        $this->assertTrue((bool) $run->getKillSwitch(), 'fixture: the kill landed');
        $this->assertNotContains('veto', $this->kinds(), 'the kill did not veto its own liquidation');
        $this->assertSame(
            1,
            BotOrderQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->filterBySide('Sell')->count(),
            'the liquidation sell was placed'
        );
    }

    // ── grid ────────────────────────────────────────────────────────

    /** Ladder [100,200]×4 on 550: buys at 100/125 fill, their exits work at
     *  125/150. Both lots are above cost at a 130 mark. */
    private function gridWithWorkingExits(array $over = []): array
    {
        $run = $this->makeRun($over);
        $this->sim->setPrice('150');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $this->sim->setPrice('100');
        $daemon->tick();
        $this->assertCount(2, $this->openSells($run), 'fixture: two exits working');
        return [$run, $daemon];
    }

    public function testGridFlattenRepricesWorkingExitsToTheMark(): void
    {
        [$run, $daemon] = $this->gridWithWorkingExits();

        $this->sim->setPrice('130');
        $this->enqueueFlatten($run);
        $daemon->tick();

        $this->assertCount(0, $this->openSells($run), 'the inventory is gone');
        $this->assertSame(2, TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->count(), 'both lots booked');
        $this->assertNotContains('flatten_partial', $this->kinds());
    }

    public function testGridFlattenKeepsLotsUnderTheirOwnCostWhenSellAtLossOff(): void
    {
        [$run, $daemon] = $this->gridWithWorkingExits();

        // mark 124 sits between the two lots' costs: the one bought at 100 is
        // in profit and goes, the one bought at 125 (breakeven 125.25) stays
        $this->sim->setPrice('124');
        $this->enqueueFlatten($run);
        $daemon->tick();

        $open = $this->openSells($run);
        $this->assertCount(1, $open, 'the underwater lot is still working');
        $this->assertSame(0, bccomp((string) $open[0]->getPrice(), '150', 8), 'at its own exit price, not chased down');
        $this->assertSame(1, TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->count(), 'only the profitable lot booked');
        $this->assertContains('flatten_partial', $this->kinds());
    }

    public function testGridFlattenSellsEverythingWhenSellAtLossOn(): void
    {
        [$run, $daemon] = $this->gridWithWorkingExits(['sell_at_loss' => true]);

        $this->sim->setPrice('90'); // under both lots' cost
        $this->enqueueFlatten($run);
        $daemon->tick();

        $this->assertCount(0, $this->openSells($run), 'the switch is ON: everything goes');
        $cycles = TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->find();
        $this->assertCount(2, $cycles);
        foreach ($cycles as $c) {
            $this->assertLessThan(0, bccomp((string) $c->getRealizedPnl(), '0', 8), 'losses realized on purpose');
        }
        $this->assertNotContains('flatten_partial', $this->kinds());
    }

    public function testFlattenChasesAFallingMarketUntilTheBookIsFlat(): void
    {
        [$run, $daemon] = $this->gridWithWorkingExits(['sell_at_loss' => true]);

        // the market drops out from under the liquidation as it is placed —
        // the fast move a flatten exists for. A limit resting at the old mark
        // would sit there for good.
        $dropped = false;
        $this->sim->beforeOrder = function (array $q) use (&$dropped): void {
            if ($q['side'] === 'SELL' && !$dropped) {
                $dropped = true;
                $this->sim->price = '110';
            }
        };
        $this->sim->setPrice('130');
        $this->enqueueFlatten($run);
        $daemon->tick();
        $this->assertGreaterThan(0, count($this->openSells($run)), 'fixture: the first liquidation missed the market');

        $daemon->tick(); // still killed: re-priced under the NEW mark
        $daemon->tick(); // …and booked

        $this->assertCount(0, $this->openSells($run), 'chased until flat');
        $this->assertSame(2, TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->count());
    }

    public function testFlattenCrossesTheMarkButNeverTheLotsBreakeven(): void
    {
        [$run, $daemon] = $this->gridWithWorkingExits();

        // mark 125.5 is a hair above the 125 lot's breakeven (125.25): 0.5%
        // under the mark would be 124.87 — a loss the switch forbids. The exit
        // is floored AT breakeven instead.
        $this->sim->beforeOrder = function (array $q): void {
            if ($q['side'] === 'SELL') {
                $this->assertGreaterThanOrEqual(0, bccomp((string) $q['price'], '100.2', 8));
                if (bccomp((string) $q['price'], '125', 8) > 0) {
                    $this->assertSame(0, bccomp((string) $q['price'], '125.25', 8), 'floored at the 125 lot\'s breakeven');
                }
            }
        };
        $this->sim->setPrice('125.5');
        $this->enqueueFlatten($run);
        $daemon->tick();

        // CHANGED (round-2 fix 5): a lot exited in pieces now carries its
        // buy-leg fee across them (the carried remainder used to be pinned
        // with legacy_buy_fee '0' — OrderStore::markLegacy's `?? '0'` — so its
        // cycle booked the sell fee alone and read as a fat profit). With both
        // legs booked, a sale floored AT the run's breakeven convention
        // (LossGuard: basis × (1 + 2·fee), which prices the exit fee off the
        // BASIS) nets the fee-on-fee term the convention leaves out: exactly
        // −2·fee²·notional, 0.00025 on a 125 lot at 0.1%. That is the floor's
        // own rounding, not a realized loss the switch is there to forbid —
        // and the assertion that matters (no exit priced under the floor) is
        // the beforeOrder hook above.
        $fee = (string) $run->getFeePct();
        foreach (TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->find() as $c) {
            $floor = bcmul(
                '-2',
                bcmul(bcmul($fee, $fee, 12), bcmul((string) $c->getBuyPrice(), (string) $c->getQty(), 12), 12),
                12
            );
            $this->assertGreaterThanOrEqual(
                0,
                bccomp((string) $c->getRealizedPnl(), $floor, 8),
                'never a realized loss with the switch OFF (beyond the breakeven convention\'s own fee² rounding)'
            );
        }
        $this->assertCount(0, $this->openSells($run));
    }
}
