<?php

namespace Tests\Custom\Bot;

use App\BotCommand;
use App\BotEventQuery;
use App\BotOrderQuery;
use App\Domains\Bot\ApiBackoff;
use App\Domains\Bot\Daemon;
use App\Domains\Bot\EventLog;
use App\Domains\Bot\Gateway\BinanceApiError;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Indicators;
use App\Domains\Bot\MarketStore;
use App\GridRun;
use App\TradeCycleQuery;
use Tests\Builder\Support\DbTestCase;
use Tests\Custom\Bot\Support\ExchangeSim;

/**
 * The stop/liquidation/runner rules, pinned.
 *
 * Every test here is a defect the 2026-09-21 real-order hardening shipped or
 * left behind — found by reading the flatten chase (694fdd2), the alert
 * routing (b5564af) and the backoff (51955a4) against a real account instead
 * of the paper fleet they were written on. The fleet has only ever traded on
 * paper, so none of these had ever cost money; every one of them would have,
 * on the first commanded close of a real position.
 *
 * Roughly in the order they would have bitten:
 *  - the chase cancelled a Trend engine's own exit and re-placed it as a
 *    legacy row, which the engine does not count as its own — so it placed
 *    another full-position sell, every tick;
 *  - `flattenOnKill` was a property of the PROCESS, so any later kill that
 *    carried no command (a sibling's drawdown stop, a retire-and-hold, the
 *    dashboard button) liquidated inventory those kills exist to hold;
 *  - a cancel dropped whatever had traded since the last look, and sized the
 *    replacement off a filled_qty that had stopped being updated;
 *  - a failed re-place left a Canceled row, i.e. a lot no later tick could
 *    see: inventory unguarded behind one Alert;
 *  - the chase had no deadband, no budget and no deadline, so a fast drop was
 *    a cancel+place per lot per tick — the order-rate limit is how an IP ban
 *    starts, and a ban stops every run on the host;
 *  - a hinted ban was slept in one piece (up to three days) with the run's
 *    GET_LOCK held and its command queue unread;
 *  - and `kill` — an unthrottled Telegram alert since b5564af — was announced
 *    per daemon BOOT rather than per kill.
 */
class KillRunnerRegressionTest extends DbTestCase
{
    private GridRun $run;
    private ExchangeSim $sim;

    protected function setUp(): void
    {
        parent::setUp();
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
        parent::tearDown();
    }

    // ── fixtures (the shapes FlattenTest uses) ──────────────────────────

    /** @param array<string,string|int|bool> $over */
    private function makeRun(array $over = []): GridRun
    {
        $r = new GridRun();
        $r->setLabel('killreg-' . bin2hex(random_bytes(5)));
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

    private function enqueue(GridRun $run, string $command): BotCommand
    {
        $cmd = new BotCommand();
        $cmd->setIdGridRun((int) $run->getIdGridRun());
        $cmd->setCommand($command);
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

    /** @return string[] */
    private function messages(string $kind): array
    {
        return array_map(
            fn ($e) => (string) $e->getMessage(),
            BotEventQuery::create()
                ->filterByIdGridRun((int) $this->run->getIdGridRun())
                ->filterByKind($kind)
                ->find()
                ->getArrayCopy()
        );
    }

    /** @return \App\BotOrder[] */
    private function openSells(GridRun $run): array
    {
        return array_values(array_filter(
            BotOrderQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->filterBySide('Sell')->find()->getArrayCopy(),
            fn ($r) => in_array((string) $r->getState(), ['SELL_OPEN', 'PartFilled'], true)
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

    /** A Trend run holding a position entered @130 with no working exit. */
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

    /** Ladder [100,200]x4 on 550: buys at 100/125 fill, exits work at 125/150. */
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

    /** Total base qty resting as SELL on the emulated exchange. */
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

    /** The tape runs away from every sell as it is placed — the fast drop a
     *  flatten exists for, and the only way an exit rests instead of filling. */
    private function marketRunsAway(string $factor = '0.97'): void
    {
        $this->sim->beforeOrder = function (array $q) use ($factor): void {
            if ($q['side'] === 'SELL') {
                $this->sim->price = bcmul((string) $q['price'], $factor, 8);
            }
        };
    }

    // ── 1. an engine owns its own exits ─────────────────────────────────

    public function testTheChaseNeverPlacesASecondSellForAPositionItAlreadyGuards(): void
    {
        [$run, $daemon] = $this->openTrendPosition(['sell_at_loss' => true]);
        $position = (string) ($this->marker($run)['qty'] ?? '0');

        $this->marketRunsAway();
        $this->sim->setPrice('200');
        $this->enqueue($run, 'Flatten');
        $daemon->tick();                       // kill + first liquidation
        $this->assertCount(1, $this->openSells($run), 'fixture: one sell is working after the first pass');

        $daemon->tick();                       // chase
        $daemon->tick();                       // chase again

        $this->assertSame(
            0,
            bccomp($this->simSellQty(), $position, 8),
            sprintf('the book carries %s for a position of %s', $this->simSellQty(), $position)
        );
        $this->assertCount(1, $this->openSells($run), 'one position, one exit — re-priced, not multiplied');
    }

    public function testTheTrendExitIsChasedDownByItsOwnEngine(): void
    {
        // the engine re-prices its own sell (it stays the engine's own,
        // non-legacy, row — that is what hasOpenOwnOrders reads)
        [$run, $daemon] = $this->openTrendPosition(['sell_at_loss' => true]);
        $this->marketRunsAway();
        $this->sim->setPrice('200');
        $this->enqueue($run, 'Flatten');
        $daemon->tick();
        $first = (string) $this->openSells($run)[0]->getPrice();

        $daemon->tick();
        $after = $this->openSells($run);

        $this->assertCount(1, $after);
        $this->assertFalse((bool) $after[0]->getIsLegacy(), "the engine's exit stays the engine's");
        $this->assertLessThan(0, bccomp((string) $after[0]->getPrice(), $first, 8), 'chased down with the tape');
    }

    // ── 2. the flatten intent belongs to the command ────────────────────

    public function testAnExternalPlainKillHoldsInventoryAfterAnEarlierFlatten(): void
    {
        [$run, $daemon] = $this->gridWithWorkingExits();

        // an earlier commanded flatten: sells the 100 lot, keeps the 125 lot
        $this->sim->setPrice('124');
        $this->enqueue($run, 'Flatten');
        $daemon->tick();
        $this->assertCount(1, $this->openSells($run), 'fixture: the underwater lot stayed working at its own exit');

        // the operator clears the kill switch and the run resumes
        $run->setKillSwitch(false);
        $run->save();
        $daemon->tick();

        // …and later something kills it with NO Flatten command: the
        // dashboard's Kill, RunLifecycle's retire-and-hold, or
        // DrawdownGuard::tripAll on a sibling daemon — all of which mean
        // "cancel buys, HOLD inventory"
        $this->sim->setPrice('140');
        $run->setKillSwitch(true);
        $run->save();
        $daemon->tick();

        $this->assertCount(1, $this->openSells($run), 'the held lot is still working at its own exit price');
        $this->assertSame(0, bccomp((string) $this->openSells($run)[0]->getPrice(), '150', 8), 'and was not chased down');
        $this->assertStringContainsString('holding inventory', implode(' | ', $this->messages('kill')));
    }

    public function testAPlainKillStopsAChaseInProgress(): void
    {
        [$run, $daemon] = $this->gridWithWorkingExits(['sell_at_loss' => true]);
        $this->marketRunsAway();
        $this->sim->setPrice('130');
        $this->enqueue($run, 'Flatten');
        $daemon->tick();
        $working = $this->openSells($run);
        $this->assertNotSame([], $working, 'fixture: the liquidation is still working');
        $prices = array_map(fn ($r) => (string) $r->getPrice(), $working);

        $this->enqueue($run, 'Kill');
        $daemon->tick();
        $daemon->tick();

        $this->assertContains('flatten_stopped', $this->kinds());
        $this->assertSame(
            $prices,
            array_map(fn ($r) => (string) $r->getPrice(), $this->openSells($run)),
            'nothing on the book is touched — the chase simply stops'
        );
    }

    // ── 3 + 4. a cancel never loses a fill or a lot ─────────────────────

    public function testTheChaseSizesTheReplacementOnWhatIsActuallyLeft(): void
    {
        [$run, $daemon] = $this->gridWithWorkingExits(['sell_at_loss' => true]);
        $exit = $this->openSells($run)[0];
        foreach ($this->openSells($run) as $row) {
            if (bccomp((string) $row->getPrice(), (string) $exit->getPrice(), 8) > 0) {
                $exit = $row; // the exit a 130 mark is NOT already through
            }
        }
        $cid = (string) $exit->getClientOrderId();
        $qty = (string) $exit->getQty();

        // 25% fills and the daemon sees it…
        $this->sim->partialFill($cid, bcmul($qty, '0.25', 8));
        $daemon->tick();
        $exit->reload();
        $this->assertSame('PartFilled', (string) $exit->getState(), 'fixture: the partial was seen');

        // …then it keeps filling, to 90%, before the flatten lands
        $this->sim->partialFill($cid, bcmul($qty, '0.90', 8));
        $placed = [];
        $this->sim->beforeOrder = function (array $q) use (&$placed): void {
            if ($q['side'] === 'SELL') {
                $placed[] = (string) $q['quantity'];
            }
        };
        $this->sim->setPrice('130');
        $this->enqueue($run, 'Flatten');
        $daemon->tick();

        $this->assertNotSame([], $placed, 'fixture: the exit was repriced');
        $left = bcmul($qty, '0.10', 8);
        $this->assertLessThanOrEqual(
            0,
            bccomp(end($placed), $left, 8),
            sprintf('re-placed %s of base with only %s left unsold on that lot', end($placed), $left)
        );
    }

    public function testEverythingThatTradedBeforeACancelIsBooked(): void
    {
        [$run, $daemon] = $this->gridWithWorkingExits(['sell_at_loss' => true]);
        $exit = $this->openSells($run)[0];
        foreach ($this->openSells($run) as $row) {
            if (bccomp((string) $row->getPrice(), (string) $exit->getPrice(), 8) > 0) {
                $exit = $row;
            }
        }
        $cid = (string) $exit->getClientOrderId();
        $qty = (string) $exit->getQty();
        $level = (int) $exit->getLevelIdx();

        $this->sim->partialFill($cid, bcmul($qty, '0.90', 8));
        $this->sim->setPrice('130');
        $this->enqueue($run, 'Flatten');
        $daemon->tick();
        $daemon->tick(); // the remainder fills at the chased price

        $sold = '0';
        foreach (TradeCycleQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByLevelIdx($level)
            ->find() as $cycle) {
            $sold = bcadd($sold, (string) $cycle->getQty(), 8);
        }
        $this->assertSame(0, bccomp($sold, $qty, 8), "the lot is booked once and whole (booked $sold of $qty)");
        $this->assertContains('flatten_partial_booked', $this->kinds());
    }

    public function testAFailedReplaceComesBackOnTheNextTick(): void
    {
        [$run, $daemon] = $this->gridWithWorkingExits(['sell_at_loss' => true]);
        $before = count($this->openSells($run));

        // the exchange refuses the repriced sell (a real -2010 during a
        // flatten: the account is a hair short, a filter rejects the qty…)
        $this->sim->beforeOrder = function (array $q): void {
            if ($q['side'] === 'SELL') {
                throw new BinanceApiError('Account has insufficient balance for requested action.', 400, -2010, null);
            }
        };
        $this->sim->setPrice('130');
        $this->enqueue($run, 'Flatten');
        try {
            $daemon->tick();
        } catch (BinanceApiError) {
            // what the runner sees: it logs api_error and backs off, and the
            // next tick picks the run up again — which is the point
        }

        $this->sim->beforeOrder = null;   // the exchange recovers
        $daemon->tick();                  // resolveMissing re-arms what was stranded
        $daemon->tick();

        $this->assertGreaterThanOrEqual(
            $before,
            count($this->openSells($run)) + TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->count(),
            'every lot is either working again or sold — none was left off the book'
        );
        $this->assertNotContains('flatten_partial', $this->kinds(), 'nothing was quietly written off');
    }

    // ── 5. the backoff is a wait, not a coma ────────────────────────────

    public function testEvenAHintedBanIsCappedAtTheProbeIntervalBecauseABlindRunLooksHealthy(): void
    {
        // Round 2 lifted the hinted cap to three days, reasoning that the
        // runner slices the wait and Daemon::idle keeps the control plane
        // alive between slices — so honouring the hint in full costs nothing,
        // while the hourly probe it replaced is a REQUEST INTO A BAN.
        //
        // Round 3 reverted it, because "the control plane stays alive" is the
        // problem, not the mitigation: a run that keeps stamping a heartbeat
        // for three days while it cannot make one exchange call reads as
        // HEALTHY to the watchdog (GTBOT_HEARTBEAT_STALE), the dashboard and
        // the hourly routine — with its orders still resting and filling, its
        // fills unbooked, and both drawdown rails unrun for the whole window.
        // One probe an hour into a ban is the cheaper of the two costs.
        $threeDays = new BinanceApiError('banned until', 418, -1003, 259200);
        $this->assertSame(3600, ApiBackoff::delay($threeDays, 5, 1), 'the blind window is capped at one probe interval');
        $this->assertSame(1805, ApiBackoff::delay(new BinanceApiError('slow down', 429, -1003, 1800), 5, 1), 'a hint UNDER the cap is still honoured in full');
        // …and with NO hint there is nothing to honour: the guess tops out at
        // the same one probe an hour
        $this->assertSame(3600, ApiBackoff::delay(new BinanceApiError('banned', 418, -1003), 5, 20));
    }

    public function testAZeroRetryAfterIsAnAbsentHintNotAZeroBackoff(): void
    {
        // taken literally it multiplied the exponential ladder by zero: a run
        // that kept erroring retried in a tight loop, which is what a 418 is
        // handed out for
        $e = new BinanceApiError('bad gateway', 502, -1000, 0);
        $this->assertGreaterThan(0, ApiBackoff::delay($e, 5, 1));
        $this->assertGreaterThan(0, ApiBackoff::delay($e, 5, 5));
    }

    public function testTheKeepAliveHeartbeatsAndTakesAKillWithoutTheExchange(): void
    {
        [$run, $daemon] = $this->gridWithWorkingExits();
        $run->setLastTickAt(date('Y-m-d H:i:s', time() - 600));
        $run->save();
        $this->enqueue($run, 'Kill');
        $this->enqueue($run, 'Flatten'); // needs a live mark — not for the keep-alive

        $calls = $this->sim->accountCalls;
        $this->assertTrue($daemon->idle(), 'no restart was asked for');

        $run->reload();
        $this->assertGreaterThan(time() - 30, (int) $run->getLastTickAt('U'), 'the heartbeat is stamped');
        $this->assertTrue((bool) $run->getKillSwitch(), 'the Kill was honoured while backing off');
        $this->assertSame($calls, $this->sim->accountCalls, 'the keep-alive never calls the exchange');
        $pending = \App\BotCommandQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByCmdStatus('Pending')
            ->findOne();
        $this->assertNotNull($pending, 'a Flatten needs a mark — it waits for a real tick');
        $this->assertSame('Flatten', (string) $pending->getCommand());
    }

    // ── 6. a liquidation outlives its daemon ────────────────────────────

    public function testARestartResumesTheLiquidationItInterrupted(): void
    {
        [$run, $daemon] = $this->gridWithWorkingExits(['sell_at_loss' => true]);

        $dropped = false;
        $this->sim->beforeOrder = function (array $q) use (&$dropped): void {
            if ($q['side'] === 'SELL' && !$dropped) {
                $dropped = true;
                $this->sim->price = '110';
            }
        };
        $this->sim->setPrice('130');
        $this->enqueue($run, 'Flatten');
        $daemon->tick();
        $this->assertGreaterThan(0, count($this->openSells($run)), 'fixture: the first liquidation missed the market');
        $this->sim->beforeOrder = null;

        // the daemon is restarted (a deploy, the watchdog, an algo_update)
        $restarted = $this->makeDaemon($run);
        $restarted->tick();
        $restarted->tick();

        $this->assertCount(0, $this->openSells($run), 'the chase picked up where it left off');
        $this->assertContains('flatten_resumed', $this->kinds());
        $this->assertNotContains('holding inventory', $this->messages('kill_seen'), 'a resumed flatten is not a hold');
    }

    // ── 7. chase discipline ─────────────────────────────────────────────

    public function testAnExitParkedOnItsBreakevenFloorIsNotRepricedForever(): void
    {
        // The floor is a fixed price and the exchange rounds a SELL UP to the
        // tick, so the resting exit sits a fraction ABOVE the target it was
        // just placed at. "Resting price > target" was therefore true on a
        // market that never moved: cancel and re-place, at the same price,
        // every tick, for every lot — pure order-rate spend, and order-rate
        // spend is how a 429 becomes a 418 IP ban that stops every run on the
        // host. A coarse tick (0.5) makes the rounding visible.
        $this->sim = new ExchangeSim([
            ['filterType' => 'PRICE_FILTER', 'tickSize' => '0.50000000'],
            ['filterType' => 'LOT_SIZE', 'stepSize' => '0.00010000', 'minQty' => '0.00010000'],
            ['filterType' => 'NOTIONAL', 'minNotional' => '5.00000000'],
        ]);
        [$run, $daemon] = $this->gridWithWorkingExits();

        // mark 125.4 clears the 125 lot's breakeven (125.25), so it is sold —
        // floored AT breakeven, which the tick rounds up to 125.5
        $this->sim->setPrice('125.4');
        $this->enqueue($run, 'Flatten');
        $daemon->tick();
        $parked = $this->openSells($run);
        $this->assertCount(1, $parked, 'fixture: one exit is parked on its floor');
        $this->assertSame(0, bccomp((string) $parked[0]->getPrice(), '125.5', 8), 'fixture: rounded up to the tick');
        $spent = count($this->sim->done);

        $daemon->tick();
        $daemon->tick();
        $daemon->tick();

        $this->assertSame($spent, count($this->sim->done), 'nothing is cancelled: the gap is inside the deadband');
        $this->assertCount(1, $this->openSells($run), 'and the exit is still working, untouched');
    }

    public function testTheChaseSpendsABoundedNumberOfOrdersPerTick(): void
    {
        $run = $this->makeRun(['sell_at_loss' => true]);
        $run->setNLevels(12);   // 6 lots fill on the way down — more than one tick may re-price
        $run->save();
        $this->sim->setPrice('199');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $this->sim->setPrice('100');
        $daemon->tick();
        $this->assertGreaterThan(4, count($this->openSells($run)), 'fixture: more lots than one tick may re-price');

        $placed = 0;
        $this->sim->beforeOrder = function (array $q) use (&$placed): void {
            if ($q['side'] === 'SELL') {
                $placed++;
                $this->sim->price = bcmul((string) $q['price'], '0.97', 8); // the tape keeps running
            }
        };
        $this->sim->setPrice('105');
        $this->enqueue($run, 'Flatten');
        $daemon->tick();

        $this->assertGreaterThan(0, $placed, 'the chase did start');
        $this->assertLessThanOrEqual(4, $placed, 'at most four lots re-priced in one tick');

        // …and the next tick works on the lots the budget deferred (the cursor
        // is what stops the first four from eating every tick)
        $before = $placed;
        $daemon->tick();
        $this->assertGreaterThan($before, $placed, 'the rest of the book progresses on the following tick');
    }

    public function testTheChaseGivesUpAndSaysSoOnce(): void
    {
        [$run, $daemon] = $this->gridWithWorkingExits(['sell_at_loss' => true]);
        $this->marketRunsAway();
        $this->sim->setPrice('130');
        $this->enqueue($run, 'Flatten');
        $daemon->tick();
        $working = count($this->openSells($run));
        $this->assertGreaterThan(0, $working, 'fixture: the liquidation is still working');

        // the commanded-at marker is what the deadline measures from, so an
        // old marker is an old chase
        $marker = BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByKind('flatten_pending')
            ->orderByIdBotEvent(\Criteria::DESC)
            ->findOne();
        $this->assertNotNull($marker, 'fixture: the liquidation is marked pending');
        $marker->setDateCreation(date('Y-m-d H:i:s', time() - 3600));
        $marker->save();
        $resumed = $this->makeDaemon($run);   // restart picks up the stale chase

        $resumed->tick();
        $resumed->tick();

        $this->assertCount(1, array_keys($this->kinds(), 'flatten_stalled', true), 'said once');
        $this->assertCount($working, $this->openSells($run), 'the exits are left working, not cancelled');

        // …and the alert's own advice works: a second Flatten restarts it.
        // (A Flatten sent to an ALREADY killed run used to do nothing at all —
        // ensureKilled short-circuits, and onKill is where a flatten starts.)
        $this->sim->beforeOrder = null;
        $this->sim->setPrice('130');
        $this->enqueue($run, 'Flatten');
        $resumed->tick();
        $this->assertCount(0, $this->openSells($run), 'the restarted chase sold what was left');
    }

    // ── 8. a lot is never sold on a guess ───────────────────────────────

    public function testALotWhoseCostIsUnknownIsKeptWhileSellAtLossIsOff(): void
    {
        [$run, $daemon] = $this->gridWithWorkingExits();
        // a carried exit whose buy is no longer identifiable (a refit
        // re-anchored the ladder, a ledger rebase dropped the era, the pinned
        // attribution is null): the loss guard reads a null basis as "not a
        // loss" by design, so the chase used to sell it 0.5% under the mark
        // with no floor under it at all — the one trade the switch forbids
        foreach ($this->openSells($run) as $sell) {
            $sell->setIsLegacy(true);
            $sell->setLegacyBuyPrice(null);
            $sell->save();
        }
        // …and nothing else can price them either: no filled buys left to
        // VWAP the position from
        foreach (BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterBySide('Buy')
            ->find() as $buy) {
            $buy->delete();
        }
        $daemon = $this->makeDaemon($run);

        $this->sim->setPrice('120'); // under both exits: nothing fills of its own accord
        $this->enqueue($run, 'Flatten');
        $daemon->tick();

        $this->assertCount(2, $this->openSells($run), 'both lots kept — no basis, no sale');
        $this->assertStringContainsString('UNKNOWN', implode(' | ', $this->messages('flatten_partial')));
    }

    // ── 9. a stop is announced once, and never dropped ──────────────────

    public function testAKillIsAnnouncedOncePerKillNotPerDaemonBoot(): void
    {
        // 'kill' bypasses the 5-min Telegram budget (EventLog::URGENT_KINDS),
        // and onKill runs on every boot of an already-killed run: a restart
        // loop re-announced the same stop until Telegram's own limit started
        // dropping messages
        [$run, $daemon] = $this->gridWithWorkingExits();
        $this->sim->setPrice('130');
        $this->enqueue($run, 'Kill');
        $daemon->tick();
        $this->assertCount(1, $this->messages('kill'), 'fixture: one kill so far');

        $this->makeDaemon($run)->tick();   // restart #1
        $this->makeDaemon($run)->tick();   // restart #2

        $this->assertCount(1, $this->messages('kill'), 'a kill is one event, not one per boot');
        $this->assertGreaterThan(0, count($this->messages('kill_seen')), 'the boot still records what it found');

        // …and a genuine new kill, after a resume, is announced again
        $run->setKillSwitch(false);
        $run->save();
        $daemon->tick();
        $run->setKillSwitch(true);
        $run->save();
        $daemon->tick();
        $this->assertCount(2, $this->messages('kill'), 'a new stop is a new alert');
    }

    public function testTheSameUrgentAlertIsCollapsedForAMinute(): void
    {
        $sent = [];
        $now = 1000;
        $notifier = $this->notifier($sent, $now);
        $run = $this->makeRun();
        $log = new EventLog((int) $run->getIdGridRun(), false, $notifier);

        $log->write('Alert', 'kill', 'kill: canceling open buys, holding inventory');
        $log->write('Alert', 'kill', 'kill: canceling open buys, holding inventory');
        $this->assertCount(1, $sent, 'the same stop twice in a second is one message');

        $log->write('Alert', 'drawdown_stop', 'a different stop entirely');
        $this->assertCount(2, $sent, 'a different urgent line is never collapsed');

        $now = 1100; // past the dedupe window: it is news again
        $log->write('Alert', 'kill', 'kill: canceling open buys, holding inventory');
        $this->assertCount(3, $sent);
    }

    public function testAnUrgentAlertTelegramRefusesIsQueuedNotLost(): void
    {
        // sendNow has no budget and — until now — no memory: a stop that hit
        // Telegram's own 429 (which a burst of urgent lines earns) was gone,
        // while an ordinary throttled alert would have been retried
        $sent = [];
        $now = 1000;
        $up = false;
        $notifier = new \App\Domains\Bot\TelegramNotifier('t', 'c', function (string $url, array $post) use (&$sent, &$up): array {
            if (!$up) {
                return ['status' => 429, 'body' => '{"ok":false,"description":"Too Many Requests"}'];
            }
            $sent[] = $post['text'] ?? '';
            return ['status' => 200, 'body' => '{"ok":true}'];
        }, null, function () use (&$now): int {
            return $now;
        });
        $run = $this->makeRun();
        $log = new EventLog((int) $run->getIdGridRun(), false, $notifier);

        $log->write('Alert', 'drawdown_stop', 'the stop that must not vanish');
        $this->assertSame([], $sent, 'fixture: Telegram refused it');

        $up = true;
        $now = 1400; // the throttled window is clear again
        $notifier->flush();

        $this->assertCount(1, $sent, 'it rode the next flush');
        $this->assertStringContainsString('the stop that must not vanish', $sent[0]);
    }

    // ── 10. the markers are the memory a kill has across daemons ────────
    //
    // Round 2 (2026-09-21). Every defect below is the same root: the durable
    // state 6c4f825 introduced (bot_event markers) was only ever WRITTEN by a
    // process holding the matching in-memory flag. A kill switch cleared while
    // nothing is running — the ordinary case, since bin/gtbot-watchdog refuses
    // to respawn a killed run's daemon ("killed on purpose; silence is
    // expected") and FundsHold::release clears it for a run with no daemon at
    // all — froze the markers mid-episode, and the next boot read a lie.

    public function testAKillIsAnnouncedAfterAResumeNoDaemonWitnessed(): void
    {
        // `restart` used to be written only by a tick that had itself applied
        // the kill, so a resume nobody witnessed left `kill` standing as the
        // newest marker: killAnnounced() then read every LATER stop as "already
        // announced" and downgraded it to an Info kill_seen, which is not an
        // Alert and fans out to no Telegram at all.
        [$run, $daemon] = $this->gridWithWorkingExits();
        $this->sim->setPrice('130');
        $this->enqueue($run, 'Kill');
        $daemon->tick();
        $this->assertCount(1, $this->messages('kill'), 'fixture: the first kill was announced');

        // that daemon is gone (a deploy, the watchdog, a host restart) and the
        // operator clears the switch from the dashboard with nothing running
        unset($daemon);
        $run->setKillSwitch(false);
        $run->save();
        $resumed = $this->makeDaemon($run);
        $resumed->tick();

        // …and later something kills it for real
        $run->setKillSwitch(true);
        $run->save();
        $resumed->tick();

        $this->assertCount(2, $this->messages('kill'), 'a genuine new stop is a new alert, whoever cleared the last one');
    }

    public function testASiblingKilledByTheGlobalDrawdownStopAnnouncesItOnce(): void
    {
        // DrawdownGuard::tripAll writes NO event on the runs it kills — a
        // sibling daemon only ever sees kill_switch=1, and its own `kill`
        // Alert is the only notification the operator gets for that run.
        [$run, $daemon] = $this->gridWithWorkingExits();
        $this->sim->setPrice('130');

        $this->assertGreaterThan(0, \App\Domains\Bot\DrawdownGuard::tripAll(), 'fixture: the floor tripped the fleet');
        $daemon->tick();
        $daemon->tick();
        $this->makeDaemon($run)->tick(); // and a restart while still killed

        $this->assertCount(1, $this->messages('kill'), 'exactly one alert per kill, per run');
        $this->assertStringContainsString('holding inventory', implode(' | ', $this->messages('kill')));
    }

    public function testAResumeClosesOutTheFlattenIntentEvenAcrossARestart(): void
    {
        [$run, $daemon] = $this->gridWithWorkingExits(['sell_at_loss' => true]);
        $this->marketRunsAway();
        $this->sim->setPrice('130');
        $this->enqueue($run, 'Flatten');
        $daemon->tick();
        $this->sim->beforeOrder = null;
        $this->assertSame('flatten_pending', $this->newestFlattenMarker($run), 'fixture: the intent is marked');

        // the daemon dies mid-chase; the switch is cleared with nothing running
        unset($daemon);
        $run->setKillSwitch(false);
        $run->save();
        $this->sim->setPrice('124');
        $this->makeDaemon($run)->tick();

        $this->assertSame(
            'flatten_done',
            $this->newestFlattenMarker($run),
            'a run that resumed trading is not liquidating — the durable intent must say so'
        );
    }

    public function testAStaleFlattenIntentNeverLiquidatesALaterHoldKill(): void
    {
        // the scariest shape of the marker leak: the intent outlives its kill
        // episode, and the next kill that means HOLD (drawdown_stop, retire,
        // the dashboard button) is turned back into a liquidation by the first
        // restart that follows it
        [$run, $daemon] = $this->gridWithWorkingExits(['sell_at_loss' => true]);
        $this->marketRunsAway();
        $this->sim->setPrice('130');
        $this->enqueue($run, 'Flatten');
        $daemon->tick();
        $this->sim->beforeOrder = null;
        $this->assertNotSame([], $this->openSells($run), 'fixture: the liquidation is still working');

        unset($daemon);
        $run->setKillSwitch(false);       // cleared with no daemon running
        $run->save();
        $resumed = $this->makeDaemon($run);
        $this->sim->setPrice('124');      // under every exit: nothing fills by itself
        $resumed->tick();

        // a plain HOLD kill from outside this process, and the routine restart
        // that follows one (deploy, watchdog, algo_update)
        $run->setKillSwitch(true);
        $run->save();
        $held = array_map(fn ($r) => (string) $r->getPrice(), $this->openSells($run));
        sort($held);

        $after = $this->makeDaemon($run);
        $after->tick();
        $after->tick();

        $now = array_map(fn ($r) => (string) $r->getPrice(), $this->openSells($run));
        sort($now);
        $this->assertSame($held, $now, 'a HOLD kill holds: the lots are neither chased down nor sold');
        $this->assertNotContains('flatten_resumed', $this->kinds(), 'the old intent belongs to a kill that is over');
    }

    public function testAStaleFlattenIntentHoldsTheProfitableLotsToo(): void
    {
        // with 'Sell at loss' OFF (the fleet's default) the loss guard keeps
        // the underwater lots anyway — so the leak's real victim is every lot
        // whose cost the mark clears, sold out of a kill that said HOLD
        [$run, $daemon] = $this->gridWithWorkingExits();
        $this->sim->setPrice('124');      // under the 125 lot's breakeven, over the 100 lot's
        $this->enqueue($run, 'Flatten');
        $daemon->tick();                  // the 100 lot sells, the 125 lot is kept
        $kept = $this->openSells($run);
        $this->assertCount(1, $kept, 'fixture: one lot was kept by the loss guard');

        unset($daemon);
        $run->setKillSwitch(false);
        $run->save();
        $this->makeDaemon($run)->tick();

        // the mark recovers well above BOTH lots' cost, then a HOLD kill lands
        $this->sim->setPrice('140');
        $run->setKillSwitch(true);
        $run->save();
        $after = $this->makeDaemon($run);
        $after->tick();
        $after->tick();

        $this->assertCount(1, $this->openSells($run), 'the profitable lot is held, not liquidated');
        $this->assertSame(0, bccomp((string) $this->openSells($run)[0]->getPrice(), (string) $kept[0]->getPrice(), 8), 'and not chased down');
    }

    public function testACompletedLiquidationEndsInsteadOfStalling(): void
    {
        // nothing closed the intent when the book went FLAT: a liquidation
        // that sold everything in ten seconds stayed "pending" for fifteen
        // minutes (adoptable by any restart — see the test above) and then
        // announced itself STALLED, telling the operator to work the book by
        // hand. There was no book.
        [$run, $daemon] = $this->gridWithWorkingExits(['sell_at_loss' => true]);
        $this->sim->setPrice('160');           // above both exits: everything sells
        $this->enqueue($run, 'Flatten');
        $daemon->tick();
        $this->assertCount(0, $this->openSells($run), 'fixture: the liquidation went flat');
        $this->assertSame('flatten_done', $this->newestFlattenMarker($run), 'finished means finished');

        // …and an old marker can no longer raise the stall alert either
        $marker = BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByKind('flatten_pending')
            ->orderByIdBotEvent(\Criteria::DESC)
            ->findOne();
        $marker->setDateCreation(date('Y-m-d H:i:s', time() - 3600));
        $marker->save();
        $this->makeDaemon($run)->tick();

        $this->assertNotContains('flatten_stalled', $this->kinds(), 'the book is flat — there is nothing to be stalled on');
    }

    public function testAStalledChaseNeverLeavesAPositionWithNoExitOnTheBook(): void
    {
        // the chase's own cancel→replace can leave the engine's position bare
        // (cancel landed, replacement refused). The deadline then walked away
        // saying "the exits stay on the book, nothing is canceled" — about a
        // position with no exit at all.
        [$run, $daemon] = $this->openTrendPosition(['sell_at_loss' => true]);
        $this->marketRunsAway();
        $this->sim->setPrice('200');
        $this->enqueue($run, 'Flatten');
        $daemon->tick();
        $this->assertCount(1, $this->openSells($run), 'fixture: the engine placed its exit');

        // the next reprice cancels it and the exchange refuses the replacement
        $this->sim->beforeOrder = function (array $q): void {
            if ($q['side'] === 'SELL') {
                throw new BinanceApiError('Account has insufficient balance for requested action.', 400, -2010, null);
            }
        };
        try {
            $daemon->tick();
        } catch (BinanceApiError) {
            // what the runner sees: it logs api_error and backs off
        }
        $this->assertCount(0, $this->openSells($run), 'fixture: the position is bare');

        // the chase has been going too long — but it may not give up on bare
        // inventory without one last attempt to guard it
        $marker = BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByKind('flatten_pending')
            ->orderByIdBotEvent(\Criteria::DESC)
            ->findOne();
        $marker->setDateCreation(date('Y-m-d H:i:s', time() - 3600));
        $marker->save();
        $this->marketRunsAway();          // the exchange takes orders again
        $resumed = $this->makeDaemon($run);
        $resumed->tick();

        $this->assertCount(1, $this->openSells($run), 'the exit is back on the book before the chase gives up');
        $this->assertStringContainsString('still working', implode(' | ', $this->messages('flatten_stalled')));
    }

    public function testTheChaseWalksEveryLotWithinThreeTicks(): void
    {
        // the round-robin cursor was an order ID that the reprice itself had
        // just cancelled off the open book, so it never matched again and
        // every tick restarted at the front of the list. A 12-lot flatten only
        // progressed because the replacements happened to come back from the
        // database last; the cursor is a POSITION now, and the row order is
        // explicit (OrderStore::openOrderObjects).
        $run = $this->makeRun(['sell_at_loss' => true, 'budget_quote' => '900']);
        // 12 buy levels, and a range tight enough that every exit is still a
        // plausible price once the tape is under it (RiskManager's fat-finger
        // check vetoes a sell more than 50% off the market)
        $run->setPHigh('130');
        $run->setNLevels(12);
        $run->save();
        $this->sim->setPrice('131');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $this->sim->setPrice('99');
        $daemon->tick();
        $lots = $this->openSells($run);
        $this->assertCount(12, $lots, 'fixture: twelve lots, three ticks of budget');

        $this->marketRunsAway('0.995'); // the tape keeps leaving every exit behind
        $this->sim->setPrice('105');
        $this->enqueue($run, 'Flatten');
        $daemon->tick();
        $daemon->tick();
        $daemon->tick();

        $untouched = [];
        foreach ($lots as $lot) {
            $lot->reload();
            if (in_array((string) $lot->getState(), ['SELL_OPEN', 'PartFilled'], true)) {
                $untouched[] = (string) $lot->getClientOrderId();
            }
        }
        $this->assertSame([], $untouched, 'every lot was repriced (or sold) within three ticks of budget');
    }

    // ── 11. round 3: what the round-2 fixes left behind ─────────────────
    //
    // Four of the five are the same shape: a fix that was right about the
    // state it moved and wrong about what the rest of the system reads off
    // that state (a heartbeat, a mark, a breach counter, a queue position).

    public function testABlindRunDropsItsMarkSoItCannotPriceTheFleet(): void
    {
        // Daemon::idle() is the backoff keep-alive: no exchange call, so the
        // run IS blind — its resting orders fill unseen and nothing is booked
        // until the ban lifts. That much is the price of a ban. What must not
        // happen is the blind run then PRICING everyone: equity() marks each
        // base asset with the last_price of the run whose last_tick_at is
        // freshest (NavLedger::refPrice picks its fallback the same way), so
        // stamping the clock without the price handed the fleet's wallet to
        // the one daemon that cannot see the market — with the pre-ban price,
        // right through the crash the drawdown floor exists for.
        $con = \Propel::getConnection();
        $con->prepare('UPDATE grid_run SET last_price = NULL')->execute();   // only this test's runs price BTC
        $con->prepare("DELETE FROM sim_wallet WHERE asset IN ('BTC','USDT')")->execute();
        $con->prepare("INSERT INTO sim_wallet (asset, qty, date_creation, date_modification) VALUES ('BTC', 1, NOW(), NOW()), ('USDT', 0, NOW(), NOW())")->execute();

        [$run, $daemon] = $this->gridWithWorkingExits();
        $exit = $this->openSells($run)[0];
        $run->setLastPrice('100');
        $run->setLastTickAt(date('Y-m-d H:i:s', time() - 600));
        $run->save();

        $sibling = new GridRun();
        $sibling->fromArray($run->toArray());
        $sibling->setNew(true);
        $sibling->setIdGridRun(null);
        $sibling->setLabel('killreg-sib-' . bin2hex(random_bytes(5)));
        $sibling->setRunUid(substr(bin2hex(random_bytes(6)), 0, 10));
        $sibling->setLastPrice('60');                               // the tape crashed
        $sibling->setLastTickAt(date('Y-m-d H:i:s', time() - 5));   // and it is still ticking
        $sibling->save();

        \App\GridRunPeer::clearInstancePool();
        $live = \App\Domains\Bot\DrawdownGuard::equity();
        $this->assertSame(0, bccomp($live['equity'], '60', 2), 'fixture: the run that can see the tape prices the wallet');

        // the banned run's keep-alive runs while an exit trades on the book
        $calls = $this->sim->accountCalls;
        $this->sim->setPrice('160');
        $this->assertTrue($daemon->idle(), 'the keep-alive keeps waiting');
        $this->assertSame($calls, $this->sim->accountCalls, 'and never calls the exchange');

        $run->reload();
        $exit->reload();
        $this->assertSame('SELL_OPEN', (string) $exit->getState(), 'blind: the fill is not booked until the ban lifts');
        $this->assertGreaterThan(time() - 30, (int) $run->getLastTickAt('U'), 'the heartbeat is still stamped — the lock and the command queue depend on it');
        $this->assertNull($run->getLastPrice(), 'but the mark is dropped: a heartbeat is not a price');

        \App\GridRunPeer::clearInstancePool();
        $after = \App\Domains\Bot\DrawdownGuard::equity();
        $this->assertSame(
            0,
            bccomp($after['equity'], $live['equity'], 2),
            'the fleet keeps pricing off the run that can actually see the tape'
        );
    }

    public function testTheWickGuardStartsFromZeroAfterAHoldItSatOutFor(): void
    {
        // BREACH_CONFIRM_TICKS exists so one aberrant print cannot kill. The
        // counters are cleared only by the check that owns them, and the
        // killed branch runs neither check — so a run killed mid-confirmation
        // came back from a hold of ANY length still armed and died on the
        // first fresh print. The global one carries the same counter into
        // DrawdownGuard::tripAll(), i.e. every run on the wallet.
        [$run, $daemon] = $this->gridWithWorkingExits();
        $run->reload();
        $this->assertGreaterThan(0, bccomp((string) $run->getMaxUnrealizedLossQuote(), '0', 8), 'fixture: the profile stamped a cap');

        $this->sim->setPrice('60');          // well through the cap
        $daemon->tick();
        $daemon->tick();
        $this->assertContains('unrealized_breach', $this->kinds(), 'fixture: the guard is counting');
        $this->assertNotContains('unrealized_stop', $this->kinds(), 'fixture: two of three ticks — not confirmed');

        // something else kills it (the dashboard, retire-and-hold, a sibling's
        // drawdown stop) and it holds for days
        $run->setKillSwitch(true);
        $run->save();
        $daemon->tick();
        $daemon->tick();

        // …and resumes, still under water. The breach is real, so the guard
        // must start counting again — not finish a count from another era.
        $run->setKillSwitch(false);
        $run->save();
        $daemon->tick();

        $this->assertNotContains('unrealized_stop', $this->kinds(), 'one print after a resume is not a confirmed breach');
        $this->assertCount(2, $this->messages('unrealized_breach'), 'the window restarted from zero (the Warn is written on tick 1 of 3)');
    }

    public function testTheDigestKeepsTheStopAndDropsTheNoiseAroundIt(): void
    {
        // sendNow requeues an urgent line Telegram refused — into the same
        // queue flush() trims at MAX_QUEUE. Trimmed to the NEWEST, the
        // requeued stop was by construction the first thing dropped: it is the
        // oldest entry, and the storm that follows a stop (every failing tick
        // writes an Error, and an Error buffers) is what survived instead.
        $sent = [];
        $now = 1000;
        $up = false;
        $notifier = new \App\Domains\Bot\TelegramNotifier('t', 'c', function (string $url, array $post) use (&$sent, &$up): array {
            if (!$up) {
                return ['status' => 429, 'body' => '{"ok":false,"description":"Too Many Requests"}'];
            }
            $sent[] = $post['text'] ?? '';
            return ['status' => 200, 'body' => '{"ok":true}'];
        }, null, function () use (&$now): int {
            return $now;
        });
        $run = $this->makeRun();
        $log = new EventLog((int) $run->getIdGridRun(), false, $notifier);

        $log->write('Alert', 'drawdown_stop', 'GLOBAL DRAWDOWN STOP: the message that must not vanish');
        $this->assertSame([], $sent, 'fixture: Telegram refused it');
        for ($i = 0; $i < 60; $i++) {
            $log->write('Error', 'api_error', "request failed #$i");
        }

        $up = true;
        $now = 1400;   // the throttled window is clear again
        $notifier->flush();

        $this->assertCount(1, $sent, 'fixture: one digest went out');
        $this->assertStringContainsString('the message that must not vanish', $sent[0], 'the stop rode the digest');
        $this->assertStringContainsString('newer notifications dropped', $sent[0], 'and the digest says what it cost');
    }

    public function testALotHeldBackByThePolicyEndsTheLiquidationInsteadOfStallingIt(): void
    {
        // flattenIsDone() is "no working exit AND heldQty <= 0", and a lot the
        // loss guard keeps IS a working exit — so with 'Sell at loss' OFF (the
        // fleet's default) a flatten that kept anything could never finish. It
        // idled for the whole CHASE_DEADLINE doing nothing the policy allows,
        // then announced a LIQUIDITY stall about a book that is fine, advising
        // a second Flatten that repeats the identical hold.
        [$run, $daemon] = $this->gridWithWorkingExits();      // sell_at_loss OFF
        $this->sim->setPrice('124');   // clears the 100 lot's cost, not the 125 lot's
        $this->enqueue($run, 'Flatten');
        $daemon->tick();               // sells what it may, keeps the rest
        $this->assertCount(1, $this->openSells($run), 'fixture: one lot is held back by the loss guard');
        $this->assertStringContainsString("'Sell at loss' is OFF", implode(' | ', $this->messages('flatten_partial')), 'fixture: the hold was explained');

        $daemon->tick();               // the next pass finds only held lots

        $this->assertSame('flatten_done', $this->newestFlattenMarker($run), 'a flatten with nothing left it may sell is finished');
        $this->assertStringContainsString(
            'held back by the loss policy',
            implode(' | ', $this->messages('flatten_done')),
            'and says why, in the policy\'s own terms'
        );

        // …so the deadline can never invent a liquidity problem out of it
        $marker = BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByKind('flatten_pending')
            ->orderByIdBotEvent(\Criteria::DESC)
            ->findOne();
        $marker->setDateCreation(date('Y-m-d H:i:s', time() - 3600));
        $marker->save();
        $this->makeDaemon($run)->tick();

        $this->assertNotContains('flatten_stalled', $this->kinds(), 'a policy hold is not a stall');
        $this->assertCount(1, $this->openSells($run), 'and the kept lot stays working at its own price');
    }

    /** The newest flatten_pending / flatten_done marker — what
     *  Daemon::restoreFlatten() reads at boot. */
    private function newestFlattenMarker(GridRun $run): ?string
    {
        $m = BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByKind(['flatten_pending', 'flatten_done'], \Criteria::IN)
            ->orderByIdBotEvent(\Criteria::DESC)
            ->findOne();
        return $m === null ? null : (string) $m->getKind();
    }

    /** @param string[] $sent */
    private function notifier(array &$sent, int &$now): \App\Domains\Bot\TelegramNotifier
    {
        return new \App\Domains\Bot\TelegramNotifier('t', 'c', function (string $url, array $post) use (&$sent): array {
            $sent[] = $post['text'] ?? '';
            return ['status' => 200, 'body' => '{"ok":true}'];
        }, null, function () use (&$now): int {
            return $now;
        });
    }
}
