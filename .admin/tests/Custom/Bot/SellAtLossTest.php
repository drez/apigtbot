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
 * grid_run.sell_at_loss (default OFF): no automated sell may realize a loss
 * unless the run's switch is on. The only automated loss-realizing sell is the
 * Trend engine's ATR stop-out (grid exits sit one ladder line above their buy;
 * legacy exits are carried at their own price/cost) — with the switch off a
 * stop-out below breakeven is IGNORED (position held, stop keeps ratcheting),
 * while a stop-out above breakeven still exits (that is a profit). Flatten
 * (daemon command + MCP kill tool) is gated the same way.
 *
 * Companion rule: a run that cannot fund a single entry (grid ladder budget
 * eaten by legacy reserve, trend tranches empty) warns ONCE per episode with
 * an Alert 'no_budget' event (Alert → Telegram).
 */
class SellAtLossTest extends TestCase
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
        $r->setLabel('sal-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setAlgo($over['algo'] ?? 'Grid');
        $r->setProfile($over['profile'] ?? 'Balanced');
        if (isset($over['simulated'])) {
            $r->setSimulated((bool) $over['simulated']);
        }
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

    private function marker(GridRun $run): ?array
    {
        $run->reload();
        $raw = (string) ($run->getEngineState() ?? '');
        return $raw === '' ? null : json_decode($raw, true);
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

    private function sells(GridRun $run): array
    {
        return BotOrderQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->filterBySide('Sell')->find()->getArrayCopy();
    }

    /** Same breakout fixture as EngineSwitchTest: 45 flat bars @100 then 15
     *  rising to 129 — entrySignal() true at 130 with the default periods. */
    private function seedBreakoutCandles(): void
    {
        $candles = array_fill(0, 45, ['high' => '101', 'low' => '99', 'close' => '100']);
        for ($i = 0; $i < 15; $i++) {
            $c = (string) (101 + $i * 2);
            $candles[] = ['high' => $c, 'low' => (string) ((float) $c - 1), 'close' => $c];
        }
        MarketStore::upsert('BTCUSDT', '1h', Indicators::summary($candles), $candles);
    }

    /** A Trend run with an open position entered @130 (real filled tranches). */
    private function openTrendPosition(array $over = []): array
    {
        $run = $this->makeRun(array_merge(['algo' => 'Trend', 'budget_quote' => '1000'], $over));
        $this->seedBreakoutCandles();
        $this->sim->setPrice('130');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $marker = $this->marker($run);
        $this->assertGreaterThan(0, bccomp((string) ($marker['qty'] ?? '0'), '0', 8), 'fixture: the position opened');
        $this->assertNotNull($marker['stop'] ?? null, 'fixture: an initial ATR stop was set');
        return [$run, $daemon];
    }

    // ── trend stop-out ──────────────────────────────────────────────

    public function testSellAtLossDefaultsOff(): void
    {
        $run = $this->makeRun();
        $run->reload();
        $this->assertFalse((bool) $run->getSellAtLoss());
    }

    public function testTrendStopBelowCostIsIgnoredWhenSellAtLossOff(): void
    {
        [$run, $daemon] = $this->openTrendPosition();

        // price falls through the ATR stop, well below the 130 entry
        $this->sim->setPrice('120');
        $daemon->tick();
        $daemon->tick(); // a second tick at the same breached price

        $this->assertCount(0, $this->sells($run), 'no exit may be placed: it would realize a loss');
        $marker = $this->marker($run);
        $this->assertGreaterThan(0, bccomp((string) ($marker['qty'] ?? '0'), '0', 8), 'the position is still held');
        $this->assertSame(0, TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->count());
        $this->assertSame(1, $this->countKind('stop_ignored'), 'warned once per breach episode, not every tick');
    }

    public function testTrendStopBelowCostExitsWhenSellAtLossOn(): void
    {
        [$run, $daemon] = $this->openTrendPosition(['sell_at_loss' => true]);

        $this->sim->setPrice('120');
        $daemon->tick();

        $sells = $this->sells($run);
        $this->assertCount(1, $sells, 'the stop breach placed exactly one exit');
        $this->assertSame('Filled', (string) $sells[0]->getState());
        $cycle = TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->findOne();
        $this->assertNotNull($cycle);
        $this->assertLessThan(0, bccomp((string) $cycle->getRealizedPnl(), '0', 8), 'a loss was realized, as allowed');
        $this->assertNotContains('stop_ignored', $this->kinds());
    }

    public function testTrendStopAboveCostStillExitsWhenSellAtLossOff(): void
    {
        [$run, $daemon] = $this->openTrendPosition();

        // rally: hwm 200, the trailing stop ratchets up to ~194 (3×ATR≈2)
        $this->sim->setPrice('200');
        $daemon->tick();
        $marker = $this->marker($run);
        $this->assertGreaterThan(0, bccomp((string) $marker['stop'], '150', 8), 'fixture: the stop ratcheted above cost');

        // pull back through the (profitable) stop — this exit is a gain
        $this->sim->setPrice('190');
        $daemon->tick();

        $sells = $this->sells($run);
        $this->assertCount(1, $sells, 'a stop-out above breakeven is a profit and proceeds');
        $this->assertSame('Filled', (string) $sells[0]->getState());
        $cycle = TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->findOne();
        $this->assertNotNull($cycle);
        $this->assertGreaterThan(0, bccomp((string) $cycle->getRealizedPnl(), '0', 8));
        $this->assertNotContains('stop_ignored', $this->kinds());
    }

    public function testTrendExitSellsWhatABaseAssetCommissionLeft(): void
    {
        // a real account without a fee float: every tranche delivers its qty
        // minus 0.1% — the stop-sell must be for what is HELD, or the
        // exchange refuses it and the position has no exit at all
        $this->sim->commissionMode = 'native';
        $this->sim->balances = ['USDT' => ['free' => '5000', 'locked' => '0']];
        [$run, $daemon] = $this->openTrendPosition(['simulated' => false]);
        // held is floored to the lot step per tranche (the sliver between
        // steps stays in the account as fee float): never MORE than the
        // account, and short of it by less than a step per tranche
        $held = (string) $this->marker($run)['qty'];
        $account = $this->sim->balances['BTC']['free'];
        $this->assertLessThanOrEqual(0, bccomp($held, $account, 8), 'the engine never holds more than the account');
        $this->assertLessThan(0, bccomp(bcsub($account, $held, 8), '0.0004', 8), 'and only lot-step slivers less');
        $this->assertSame(0, bccomp(bcmul(bcdiv($held, '0.0001', 0), '0.0001', 8), $held, 8), 'a position an exit can carry whole');

        $this->sim->setPrice('200');
        $daemon->tick();
        $this->sim->setPrice('190');
        $daemon->tick();

        $sells = $this->sells($run);
        $this->assertCount(1, $sells);
        $this->assertSame('Filled', (string) $sells[0]->getState());
        $this->assertNotContains('api_error', $this->kinds());
    }

    public function testTrendIgnoredStopReArmsWhenPriceRecoversAboveCost(): void
    {
        [$run, $daemon] = $this->openTrendPosition();

        $this->sim->setPrice('120');
        $daemon->tick(); // ignored (below cost)
        $this->assertCount(0, $this->sells($run));

        // recovery above breakeven while still at/below the (now ratcheted)
        // stop: hwm was pinned at 130 on entry, stop ~126, price 128 > stop —
        // so hold; then a fresh rally + pullback exits at a profit
        $this->sim->setPrice('200');
        $daemon->tick();
        $this->sim->setPrice('190');
        $daemon->tick();
        $this->assertCount(1, $this->sells($run), 'held through the loss, exited on the profitable stop');
        $this->assertSame(1, $this->countKind('stop_ignored'));
    }

    // ── flatten ─────────────────────────────────────────────────────

    private function enqueue(GridRun $run, string $command): BotCommand
    {
        $cmd = new BotCommand();
        $cmd->setIdGridRun((int) $run->getIdGridRun());
        $cmd->setCommand($command);
        $cmd->setCmdStatus('Pending');
        $cmd->save();
        return $cmd;
    }

    public function testFlattenCommandRefusedWhenUnderwaterAndSellAtLossOff(): void
    {
        [$run, $daemon] = $this->openTrendPosition();

        $this->sim->setPrice('120'); // under the 130 entry
        $cmd = $this->enqueue($run, 'Flatten');
        $daemon->tick();

        $cmd->reload();
        $run->reload();
        $this->assertSame('Failed', (string) $cmd->getCmdStatus());
        $this->assertFalse((bool) $run->getKillSwitch(), 'refusal must not flip the kill switch');
        $this->assertSame(1, $this->countKind('flatten_refused'));
        $this->assertCount(0, $this->sells($run), 'nothing was liquidated');
    }

    /**
     * The switch forbids realizing LOSSES, not gains: with the position above
     * breakeven a Flatten is a profitable liquidation and proceeds even with
     * sell_at_loss OFF. Prod run 8 (2026-09-18) was refused at +1.85 USDT
     * because the gate only read the boolean.
     */
    public function testFlattenCommandAllowedAboveBreakevenWithSellAtLossOff(): void
    {
        [$run, $daemon] = $this->openTrendPosition();

        $this->sim->setPrice('200'); // well above the 130 entry + both fees
        $cmd = $this->enqueue($run, 'Flatten');
        $daemon->tick();

        $cmd->reload();
        $run->reload();
        $this->assertSame('Done', (string) $cmd->getCmdStatus());
        $this->assertTrue((bool) $run->getKillSwitch());
        $this->assertSame(0, $this->countKind('flatten_refused'));
        // and the liquidation actually ran — FlattenTest covers what it sells
        $this->assertCount(1, $this->sells($run), 'the position was liquidated');
    }

    /** A run holding nothing cannot realize anything — the gate has no reason
     *  to stand in the way (mirrors TrendEngine::mayExitAt's "no cost basis,
     *  never block on a guess"). */
    public function testFlattenOnAFlatRunIsAllowedWithSellAtLossOff(): void
    {
        $run = $this->makeRun(['profile' => 'Balanced']);
        $cmd = $this->enqueue($run, 'Flatten');
        $this->makeDaemon($run)->tick();

        $cmd->reload();
        $run->reload();
        $this->assertSame('Done', (string) $cmd->getCmdStatus());
        $this->assertTrue((bool) $run->getKillSwitch());
        $this->assertSame(0, $this->countKind('flatten_refused'));
    }

    /** The gate is algo-agnostic: a grid's unsold lots are priced off the same
     *  ledger (invested quote / tracked inventory), not off a Trend marker. */
    public function testGridFlattenRefusedUnderTheLadderCostBasis(): void
    {
        $run = $this->makeRun();
        $this->sim->setPrice('150');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();             // buys at 100 + 125
        $this->sim->setPrice('100'); // both fill
        $daemon->tick();
        $this->assertGreaterThan(0, bccomp($this->storeInvested($run), '0', 8), 'fixture: the ladder holds inventory');

        $this->sim->setPrice('80');  // under the ladder's cost basis
        $cmd = $this->enqueue($run, 'Flatten');
        $daemon->tick();

        $cmd->reload();
        $this->assertSame('Failed', (string) $cmd->getCmdStatus());
        $this->assertSame(1, $this->countKind('flatten_refused'));
    }

    private function storeInvested(GridRun $run): string
    {
        return (new \App\Domains\Bot\OrderStore(
            (int) $run->getIdGridRun(),
            (string) $run->getRunUid(),
            null,
            (bool) $run->getSimulated()
        ))->investedQuote();
    }

    public function testFlattenCommandAllowedWhenSellAtLossOn(): void
    {
        $run = $this->makeRun(['profile' => 'Balanced', 'sell_at_loss' => true]);
        $cmd = $this->enqueue($run, 'Flatten');
        $this->makeDaemon($run)->tick();

        $cmd->reload();
        $run->reload();
        $this->assertSame('Done', (string) $cmd->getCmdStatus());
        $this->assertTrue((bool) $run->getKillSwitch());
        $this->assertSame(0, $this->countKind('flatten_refused'));
    }

    public function testPlainKillStillAllowedWhenSellAtLossOff(): void
    {
        $run = $this->makeRun();
        $cmd = $this->enqueue($run, 'Kill');
        $this->makeDaemon($run)->tick();
        $cmd->reload();
        $run->reload();
        $this->assertSame('Done', (string) $cmd->getCmdStatus());
        $this->assertTrue((bool) $run->getKillSwitch(), 'kill holds inventory — never a sale, always allowed');
    }

    // ── no budget warning ───────────────────────────────────────────

    /**
     * Grid: [100,200]×4 on 550 — buys at 100/125 fill at 100, their exits work
     * at 125/150 (150 vs 100 is exactly the 0.5 deviation cap, not over it).
     * A refit then carries those exits as legacy; their reserve (275) exceeds
     * the shrunken slice (200), so the new ladder cannot fund a single entry
     * → one Alert, not one per tick.
     */
    private function holdInventoryThenStarveTheSlice(): array
    {
        $run = $this->makeRun();
        $this->sim->setPrice('150');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();                 // buys at 100 + 125 (150 is at market)
        $this->sim->setPrice('100');     // both fill (floor 98 not breached)
        $daemon->tick();                 // exits at 125 + 150 placed
        $this->sim->setPrice('120');
        $daemon->tick();
        $this->assertCount(2, array_filter($this->sim->open, fn ($o) => $o['side'] === 'SELL'), 'fixture: two exits working');
        $this->assertNotContains('no_budget', $this->kinds(), 'a fully working ladder is not a budget problem');

        // refit: new geometry + slice shrunk by the routine → reserve > slice
        $run->reload();
        $run->setPLow('112');
        $run->setPHigh('192');
        $run->setBudgetQuote('200');
        $run->save();
        $daemon->tick();
        $daemon->tick();
        return [$run, $daemon];
    }

    public function testGridRefitWithNoFreeBudgetWarnsOnce(): void
    {
        [$run] = $this->holdInventoryThenStarveTheSlice();
        $this->assertContains('refit_applied', $this->kinds(), 'fixture: the refit landed');
        $this->assertSame(1, $this->countKind('no_budget'), 'one warning per episode');
        $ev = BotEventQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('no_budget')->findOne();
        $this->assertSame('Alert', (string) $ev->getLevel(), 'Alert level → fans out to Telegram');
        $this->assertStringContainsString('no budget available for trading', (string) $ev->getMessage());
    }

    public function testGridBudgetRecoveryReArmsTheWarning(): void
    {
        [$run, $daemon] = $this->holdInventoryThenStarveTheSlice();
        $this->assertSame(1, $this->countKind('no_budget'));

        // the legacy exits fill → reserve released; the next refit has budget
        $this->sim->setPrice('160');
        $daemon->tick();
        $this->assertCount(0, array_filter($this->sim->open, fn ($o) => $o['side'] === 'SELL'), 'fixture: exits filled');
        $run->reload();
        $run->setPLow('114');
        $run->save();
        $daemon->tick();
        $this->assertSame(2, count(array_keys($this->kinds(), 'refit_applied', true)), 'fixture: second refit landed');
        $this->assertSame(1, $this->countKind('no_budget'), 'no repeat while budget is available');

        // inventory builds up again on the new ladder (buys at 114/140 fill)…
        $this->sim->setPrice('112');
        $daemon->tick();
        $this->assertCount(2, array_filter($this->sim->open, fn ($o) => $o['side'] === 'SELL'), 'fixture: two exits working again');
        // …and a later refit shrinks the slice under that reserve → new episode
        $run->reload();
        $run->setPLow('116');
        $run->setBudgetQuote('100');
        $run->save();
        $daemon->tick();
        $this->assertSame(2, $this->countKind('no_budget'), 'a new starved episode warns again');
    }

    public function testTrendEntryWithNoFreeBudgetWarnsOnce(): void
    {
        $run = $this->makeRun(['algo' => 'Trend', 'budget_quote' => '1000']);
        // a legacy exit carried from an earlier engine reserves the whole slice
        $row = new \App\BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId('legacy-' . bin2hex(random_bytes(4)));
        $row->setLevelIdx(0);
        $row->setSide('Sell');
        $row->setState('SELL_OPEN');
        $row->setPrice('200');
        $row->setQty('6');
        $row->setIsLegacy(true);
        $row->setSimulated(true); // the run is simulated (default) — the store is mode-scoped
        $row->save();
        $this->sim->open[$row->getClientOrderId()] = [
            'orderId' => 1, 'clientOrderId' => $row->getClientOrderId(), 'side' => 'SELL',
            'price' => '200', 'origQty' => '6', 'executedQty' => '0', 'status' => 'NEW',
        ];

        $this->seedBreakoutCandles();
        $this->sim->setPrice('130');
        $daemon = $this->makeDaemon($run);
        $daemon->tick(); // breakout signal, but 0 free budget
        $daemon->tick();

        $this->assertSame(0, BotOrderQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->filterBySide('Buy')->count());
        $this->assertSame(1, $this->countKind('no_budget'));
    }
}
