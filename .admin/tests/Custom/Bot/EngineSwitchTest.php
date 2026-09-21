<?php

namespace Tests\Custom\Bot;

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
 * The StrategyEngine seam: a Grid run keeps trading exactly as before through
 * the engine, and flipping run.algo performs a CLEAN cutover — the daemon
 * exits (supervisor relaunches), and the next boot cancels the open grid buys,
 * carries the working sells as legacy exits (they guard real inventory) and
 * resets the persisted engine state.
 */
class EngineSwitchTest extends TestCase
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
        // Keep legacy expectations deterministic; specific tests opt-in when
        // they exercise stale-data auto-refresh behavior.
        putenv('GTBOT_TREND_REFRESH_STALE=0');
        // Drift auto-reload changes tick control flow (returns false in the
        // same tick); default it off and opt-in only where asserted.
        putenv('GTBOT_TREND_AUTO_RELOAD_ON_DRIFT=0');
        // the global drawdown stop has its own suite — pin it OFF so this
        // suite's scenarios only trip their own rails
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

    /** @param array<string,string|int> $over */
    private function makeRun(array $over = []): GridRun
    {
        $r = new GridRun();
        $r->setLabel('engsw-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setAlgo($over['algo'] ?? 'Grid');
        $r->setProfile($over['profile'] ?? 'Balanced');
        // the Trend stop-out mechanics under test need the run switch ON —
        // with the default (OFF) a below-cost stop is ignored, which is
        // SellAtLossTest's subject, not this suite's
        $r->setSellAtLoss((bool) ($over['sell_at_loss'] ?? (($over['algo'] ?? 'Grid') === 'Trend')));
        if (isset($over['max_buy_levels_below'])) {
            $r->setMaxBuyLevelsBelow((int) $over['max_buy_levels_below']);
        }
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels((int) ($over['n_levels'] ?? 4));
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualBase');
        $r->setBudgetQuote($over['budget_quote'] ?? '550');
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

    /** A BOOTED daemon on the sim exchange (boot is mandatory before tick). */
    private function makeDaemon(GridRun $run): Daemon
    {
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $d = new Daemon($run, $gw, false, new EventLog((int) $run->getIdGridRun(), false));
        $d->boot();
        return $d;
    }

    /** A BOOTED daemon with the stuck-partial timeout shrunk for tests —
     *  same pattern as DaemonLiveFlowTest::daemonWithPartialTimeout. */
    private function makeDaemonWithPartialTimeout(GridRun $run, int $secs): Daemon
    {
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $log = new EventLog((int) $run->getIdGridRun(), false);
        $d = new class($run, $gw, false, $log, $secs) extends Daemon {
            public function __construct($run, $gateway, $dryRun, $log, private readonly int $timeoutSecs)
            {
                parent::__construct($run, $gateway, $dryRun, $log);
            }

            protected function partialTimeoutSeconds(): int
            {
                return $this->timeoutSecs;
            }
        };
        $d->boot();
        return $d;
    }

    /**
     * Fill exactly ONE buy (the nearest line under price) so a sell works on
     * real inventory while a deeper buy stays open — the state the cutover has
     * to handle: cancel the open buy, carry the working sell.
     */
    private function fillOneBuy(Daemon $d): void
    {
        $this->sim->setPrice('120'); // crosses the 125 line, leaves the 100 buy
        $d->tick();                  // books the fill, arms the exit at 150
    }

    /** The shell's own cutover marker on the run row. */
    private function marker(GridRun $run): ?array
    {
        $run->reload();
        $raw = (string) ($run->getEngineState() ?? '');
        return $raw === '' ? null : json_decode($raw, true);
    }

    /** The cid of the open sim order at a given price. */
    private function cidAt(string $side, string $price): string
    {
        foreach ($this->sim->open as $cid => $o) {
            if ($o['side'] === $side && bccomp((string) $o['price'], $price, 8) === 0) {
                return $cid;
            }
        }
        $this->fail("no open $side at $price on the sim");
    }

    private function kinds(): array
    {
        return array_map(
            fn ($e) => (string) $e->getKind(),
            BotEventQuery::create()
                ->filterByIdGridRun((int) $this->run->getIdGridRun())
                ->find()
                ->getArrayCopy()
        );
    }

    public function testGridToTrendCutoverCarriesLegacyExitsAndCancelsBuys(): void
    {
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Grid', 'profile' => 'Balanced', 'budget_quote' => '550']);
        $daemon = $this->makeDaemon($run);
        $daemon->tick();          // places the buy ladder (100, 125)
        $this->fillOneBuy($daemon); // one buy fills → a sell works at 150

        $this->assertGreaterThan(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByState('BUY_OPEN')->count(), 'fixture: a grid buy is open');
        $this->assertGreaterThan(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByState('SELL_OPEN')->count(), 'fixture: a sell works on inventory');

        // the shell stamps its marker at boot — detection keys on THIS, not on
        // an engine remembering to persist state
        $this->assertSame(['algo' => 'Grid'], $this->marker($run));

        $run->reload();
        $run->setAlgo('Trend');
        $run->save();
        $this->assertFalse($daemon->tick(), 'algo change must request a clean restart');

        $daemon2 = $this->makeDaemon($run); // relaunch = new boot performs the cutover
        $daemon2->tick();

        $this->assertSame(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByState('BUY_OPEN')->filterByIsLegacy(false)->count(),
            'open grid buys canceled');
        $this->assertGreaterThan(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByState('SELL_OPEN')->filterByIsLegacy(true)->count(),
            'working sells carried as legacy exits');
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('algo_switch')->count());
        // engine state reset to the bare marker: the outgoing engine's own
        // keys are gone, and the marker now names the incoming engine
        $this->assertSame(['algo' => 'Trend'], $this->marker($run));

        // the cutover is one-time: a later relaunch on the same algo must not
        // tear anything down again (it would keep re-alerting and re-tagging)
        $daemon3 = $this->makeDaemon($run);
        $daemon3->tick();
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('algo_cutover')->count(),
            'the cutover runs once, not on every boot');
    }

    /**
     * Task 4 note: this test pinned the Task-3 placeholder (run.algo=Trend
     * had no engine, MissingEngine idled loudly). TrendEngine now ships, and
     * run.algo is a strict enum(Grid, Trend) — 'Trend' can never again reach
     * the missing-engine guard through normal boot, so that scenario is gone
     * by construction. Repurposed to pin what replaces it: a real engine
     * boots clean and, with no market data to signal a breakout on, trades
     * nothing rather than idling with an alert.
     */
    public function testTrendBootWithARealEngineTradesNothingWithoutASignal(): void
    {
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Trend']);
        $daemon = $this->makeDaemon($run);
        $this->assertTrue($daemon->tick(), 'a real Trend engine ticks cleanly');

        $this->assertSame(0, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('engine_missing')->count(),
            'Trend has a real engine now — the guard never fires for it');
        $this->assertSame(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByState('BUY_OPEN')->count(),
            'no market data → no breakout signal → no entries');
    }

    /**
     * Trend stale gate self-heals: when the signal TF summary is missing,
     * the engine asks MarketCollector to refresh and continues entry logic
     * in the same tick if data becomes fresh.
     */
    public function testTrendStaleSignalCanAutoRefreshAndEnter(): void
    {
        putenv('GTBOT_TREND_REFRESH_STALE=1');
        try {
            $this->sim->setPrice('130');
            // 60 bars: enough for Donchian+EMA signal guard.
            $flat = array_fill(0, 45, ['open' => '100', 'high' => '101', 'low' => '99', 'close' => '100']);
            $rising = $flat;
            for ($i = 0; $i < 15; $i++) {
                $c = (string) (101 + $i * 2);
                $rising[] = ['open' => (string) ((float) $c - 1), 'high' => $c, 'low' => (string) ((float) $c - 2), 'close' => $c];
            }
            $this->sim->klines = $rising;

            $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '550']);
            $run->setDeployPct(50);
            $run->setTrendTf('1h');
            $run->save();

            $daemon = $this->makeDaemon($run);
            $daemon->tick();

            $this->assertGreaterThan(0, BotOrderQuery::create()
                ->filterByIdGridRun((int) $run->getIdGridRun())
                ->filterBySide('Buy')
                ->filterByState(['BUY_OPEN', 'Filled'], \Criteria::IN)
                ->count(), 'freshly collected trend data allows entry in the same tick (open or immediately filled)');
            $this->assertSame(1, BotEventQuery::create()
                ->filterByIdGridRun((int) $run->getIdGridRun())
                ->filterByKind('trend_data_refreshed')
                ->count(), 'stale data refresh is explicit in the event log');
            $this->assertSame(0, BotEventQuery::create()
                ->filterByIdGridRun((int) $run->getIdGridRun())
                ->filterByKind('trend_data_stale')
                ->count(), 'no stale warning when refresh succeeds immediately');
        } finally {
            putenv('GTBOT_TREND_REFRESH_STALE');
        }
    }

    public function testGridRunBehaviorUnchangedThroughSeam(): void
    {
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Grid', 'profile' => 'Balanced', 'budget_quote' => '550']);
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $this->assertGreaterThan(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByState('BUY_OPEN')->count());

        // and the grid's fill semantics still run through the engine: a buy
        // fill arms its exit, and the exit filling closes a cycle
        $this->fillOneBuy($daemon);
        $this->assertContains('buy_fill', $this->kinds());
        $this->sim->setPrice('150');
        $daemon->tick();
        $this->assertSame(1, \App\TradeCycleQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->count(), 'the exit filling closes a cycle');
        $this->assertContains('cycle_closed', $this->kinds());
    }

    public function testTrendToGridCutoverRunsAndRewritesTheMarker(): void
    {
        // a run already living under the Trend marker with a grid-era exit
        // still working: flipping back to Grid must cut over the OTHER way
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Grid']);
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $this->fillOneBuy($daemon);

        $run->reload();
        $run->setAlgo('Trend');
        $run->save();
        $daemon->tick();                    // clean-restart request
        $this->makeDaemon($run)->tick();    // cutover INTO Trend
        $marker = $this->marker($run);
        $this->assertIsArray($marker);
        $this->assertSame('Trend', (string) ($marker['algo'] ?? ''),
            'the shell marker names the incoming engine even if Trend state keys are present');

        $run->reload();
        $run->setAlgo('Grid');
        $run->save();
        $this->makeDaemon($run)->tick();    // cutover BACK to Grid

        $this->assertGreaterThanOrEqual(2, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('algo_cutover')->count(),
            'the marker branch fires in both directions');
        $this->assertSame(['algo' => 'Grid'], $this->marker($run));
        // the exit carried out of the grid is still legacy and still working
        $this->assertGreaterThan(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByState('SELL_OPEN')->filterByIsLegacy(true)->count());
    }

    /**
     * Regression (fix 5): the marker is written by the SHELL at every boot, so
     * a relaunch of an engine that never persists state of its own must NOT
     * look like "an unmarked book under a non-grid algo" and tear that book
     * down. Also covers fix 4: the ladder self-heal is grid-only, so a
     * non-grid engine's off-ladder order must not trip geometry_reset.
     */
    public function testMarkedNonGridRunKeepsItsOwnBookOnRestart(): void
    {
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Trend']);
        $this->makeDaemon($run); // first boot: the SHELL stamps the marker
        $this->assertSame(['algo' => 'Trend'], $this->marker($run),
            'the marker must not wait for an engine to persist state');

        // an order this (hypothetical) engine placed: deliberately OFF every
        // ladder line, which is normal for anything that is not the grid
        $cid = 'gt-' . $run->getRunUid() . '-L00-B-99';
        $row = new \App\BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId($cid);
        $row->setLevelIdx(0);
        $row->setSide('Buy');
        $row->setState('BUY_OPEN');
        $row->setPrice('137.77');
        $row->setQty('0.5');
        $row->setSimulated((bool) $run->getSimulated());
        $row->save();
        $this->sim->open[$cid] = [
            'clientOrderId' => $cid, 'orderId' => 77, 'side' => 'BUY',
            'price' => '137.77', 'origQty' => '0.5', 'executedQty' => '0', 'status' => 'NEW',
        ];

        $daemon = $this->makeDaemon($run);
        $daemon->tick();

        $this->assertNotContains('algo_cutover', $this->kinds(), 'a marked run is not mid-switch — nothing to tear down');
        $this->assertNotContains('geometry_reset', $this->kinds(), 'off-ladder is normal outside the grid');
        $row->reload();
        $this->assertSame('BUY_OPEN', (string) $row->getState(), "the engine's own order survives the restart");
        $run->reload();
        $this->assertFalse((bool) $run->getKillSwitch());
    }

    /**
     * Regression (fix 1): the strategy runs BEFORE fill detection, so a buy
     * the exchange has already partially filled still looks untouched in the
     * ledger when the distance window prunes it. The cancel response carries
     * the executed qty — book it and exit it; dropping it would orphan real
     * inventory silently.
     */
    public function testPrunedBuyThatAlreadyFilledIsBookedNotOrphaned(): void
    {
        $this->sim->setPrice('130');
        $run = $this->makeRun(['n_levels' => 5, 'max_buy_levels_below' => 2]);
        $daemon = $this->makeDaemon($run);
        $daemon->tick(); // buys at 100 (L0) and 120 (L1)
        $l0 = $this->cidAt('BUY', '100');

        // a wick fills part of the deep buy — the ledger has not seen it yet
        $this->sim->partialFill($l0, '0.4');
        $this->sim->setPrice('190'); // window slides up → L0/L1 get pruned
        $daemon->tick();

        $row = BotOrderQuery::create()->findOneByClientOrderId($l0);
        $this->assertSame('Filled', (string) $row->getState(), 'the executed part must be booked, not canceled away');
        $this->assertSame(0, bccomp((string) $row->getFilledQty(), '0.4', 8));
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('prune_partial_booked')->count());
        // and the inventory got an exit rather than being left naked
        $exit = BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterBySide('Sell')
            ->filterByLevelIdx(0)
            ->findOne();
        $this->assertNotNull($exit, 'booked inventory must be exited');
        $this->assertSame(0, bccomp((string) $exit->getQty(), '0.4', 8));
    }

    /**
     * The engine-facing persistence API: engines never touch the column, they
     * merge keys through the shell, which re-writes its own `algo` marker in
     * the same save so it can never be dropped.
     */
    public function testPersistEngineStateMergesKeysAndKeepsTheMarker(): void
    {
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Grid']);
        $daemon = $this->makeDaemon($run);

        $daemon->persistEngineState(['stop' => '118.5', 'bars' => 7]);
        $this->assertSame(['algo' => 'Grid', 'stop' => '118.5', 'bars' => 7], $this->marker($run));

        $daemon->persistEngineState(['stop' => '121.0']);
        $this->assertSame(['algo' => 'Grid', 'stop' => '121.0', 'bars' => 7], $this->marker($run),
            'a partial write merges — it must not drop the other keys or the marker');
    }

    /**
     * Regression: an engine bug that overwrites engine_state (dropping the
     * shell's marker) must NOT make the next boot mistake the run's own book
     * for a stale grid book and tear it down — and the marker must heal.
     */
    public function testWipedEngineStateDoesNotTearDownTheRunsOwnBook(): void
    {
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Trend']);
        $this->makeDaemon($run); // marker stamped by the shell

        // an order this engine placed — off every ladder line, as any non-grid
        // engine's orders are
        $cid = 'gt-' . $run->getRunUid() . '-L00-B-42';
        $row = new \App\BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId($cid);
        $row->setLevelIdx(0);
        $row->setSide('Buy');
        $row->setState('BUY_OPEN');
        $row->setPrice('137.77');
        $row->setQty('0.5');
        $row->setSimulated((bool) $run->getSimulated());
        $row->save();
        $this->sim->open[$cid] = [
            'clientOrderId' => $cid, 'orderId' => 42, 'side' => 'BUY',
            'price' => '137.77', 'origQty' => '0.5', 'executedQty' => '0', 'status' => 'NEW',
        ];

        // the engine clobbers the column with its own state (the bug)
        $run->reload();
        $run->setEngineState('{"stop":"1"}');
        $run->save();

        $daemon = $this->makeDaemon($run);

        $this->assertNotContains('algo_cutover', $this->kinds(), "a wiped marker is not an algo switch");
        $row->reload();
        $this->assertSame('BUY_OPEN', (string) $row->getState(), "the engine's own book survives");
        $this->assertSame(['algo' => 'Trend', 'stop' => '1'], $this->marker($run),
            'the marker heals at boot without discarding the engine keys');

        // and the tick belt heals it too, without waiting for a restart
        $run->reload();
        $run->setEngineState('{"stop":"2"}');
        $run->save();
        $daemon->tick();
        $this->assertSame(['algo' => 'Trend', 'stop' => '2'], $this->marker($run),
            'a marker lost mid-run heals within one tick');
    }

    /**
     * Regression: with NO marker the shell must fall back to the run's OWN
     * algo. Guessing 'Grid' would hand a non-grid book to the grid engine,
     * which books its own ladder qty against a position it does not own —
     * silently placing an oversized exit.
     *
     * Task 4 note: this used to assert the fill landed on the (then
     * placeholder) MissingEngine, which could only log an alert and leave it
     * unexited. TrendEngine now ships and resolves the fallback to 'Trend'
     * exactly the same way — the difference is it actually adopts the
     * missed fill as its own open position instead of just alerting on it.
     */
    public function testUnmarkedNonGridBookIsNeverResolvedByTheGridEngine(): void
    {
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Trend']);
        $run->setEngineState('{"stop":"1"}'); // no algo marker
        $run->save();

        // the engine's order filled while the daemon was down
        $cid = 'gt-' . $run->getRunUid() . '-L00-B-7';
        $row = new \App\BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId($cid);
        $row->setLevelIdx(0);
        $row->setSide('Buy');
        $row->setState('BUY_OPEN');
        $row->setPrice('137.77');
        $row->setQty('0.5');
        $row->setSimulated((bool) $run->getSimulated());
        $row->save();
        $this->sim->done[$cid] = [
            'clientOrderId' => $cid, 'orderId' => 7, 'side' => 'BUY',
            'price' => '137.77', 'origQty' => '0.5', 'executedQty' => '0.5', 'status' => 'FILLED',
        ];

        $this->makeDaemon($run); // boot reconciles the missed fill

        $row->reload();
        $this->assertSame('Filled', (string) $row->getState(), 'the missed fill is still booked in the ledger');
        $this->assertSame(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterBySide('Sell')->count(),
            'the grid engine must never arm a ladder-sized exit for a book that is not its own');
        $this->assertNotContains('engine_missing', $this->kinds(), 'Trend has a real engine — nothing is dropped silently');
        $marker = $this->marker($run);
        $this->assertSame('Trend', $marker['algo'] ?? null, 'the fallback resolved to Trend, not Grid');
        $this->assertSame(0, bccomp((string) ($marker['qty'] ?? '0'), '0.5', 8),
            'the missed fill was adopted as the Trend engine\'s own open position, not left unexited');
    }

    /**
     * A run that predates the marker (engine_state never written) flipped to a
     * non-grid algo while its GRID book is still on the ladder: the grid engine
     * must resolve that book — a fill missed while down gets its exit — and the
     * cutover then carries that exit out as legacy. Anything less strands real
     * inventory the incoming engine cannot exit.
     */
    public function testPreMarkerGridBookIsResolvedByTheGridEngineThenCarried(): void
    {
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Grid']);
        $daemon = $this->makeDaemon($run);
        $daemon->tick(); // buys at 100 (L0) and 125 (L1), on the ladder

        // rewind to a pre-marker world and flip the algo while the daemon is down
        $run->reload();
        $run->setEngineState(null);
        $run->setAlgo('Trend');
        $run->save();
        $this->sim->setPrice('120'); // the 125 buy fills while nobody is watching

        $this->makeDaemon($run); // boot: reconcile under Grid, then cut over

        $exit = BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterBySide('Sell')
            ->findOne();
        $this->assertNotNull($exit, 'the grid engine must exit the fill it missed while down');
        $this->assertTrue((bool) $exit->getIsLegacy(), 'and the cutover carries that exit as legacy');
        $this->assertSame('SELL_OPEN', (string) $exit->getState());
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('algo_cutover')->count());
        $this->assertNotContains('stranded_book', $this->kinds(), 'an on-ladder book is not stranded');
        $run->reload();
        $this->assertFalse((bool) $run->getKillSwitch());
    }

    /**
     * Same pre-marker situation but the grid book matches NO ladder: nothing
     * can exit it (the cutover cannot assign level ownership, and the incoming
     * engine never placed it). Passing silently left entries filling with no
     * exits — and the marker stamped moments later closed the window forever.
     */
    public function testPreMarkerOffLadderBookIsAlertedAndKilled(): void
    {
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Grid']);
        $daemon = $this->makeDaemon($run);
        $daemon->tick(); // buys at 100 and 125

        // pre-marker world + a geometry the open orders belong to no ladder of
        $run->reload();
        $run->setEngineState(null);
        $run->setAppliedGeometry(null);
        $run->setAlgo('Trend');
        $run->setPLow('300');
        $run->setPHigh('500');
        $run->save();
        $this->sim->setPrice('400');

        $this->makeDaemon($run);

        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('stranded_book')->count(),
            'a book nothing can exit must say so, once');
        $run->reload();
        $this->assertTrue((bool) $run->getKillSwitch(), 'and stop, for a human');
        $this->assertSame(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByState('BUY_OPEN')->count(),
            'stranded entries are canceled — they must not keep filling');
        $this->assertSame(0, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('algo_cutover')->count(),
            'nothing was cut over — level ownership is ambiguous');
    }

    /**
     * Regression (fix 2): pruning an order the exchange has ALREADY filled
     * raises -2011 "unknown order". That is a normal race — fill detection
     * resolves it later in the same tick — so it must not page an operator
     * with an Error-level cancel_failed.
     */
    public function testPruningAnAlreadyGoneOrderIsNotAnError(): void
    {
        $this->sim->setPrice('130');
        $run = $this->makeRun(['n_levels' => 5, 'max_buy_levels_below' => 2]);
        $daemon = $this->makeDaemon($run);
        $daemon->tick(); // buys at 100 (L0) and 120 (L1)
        $l0 = $this->cidAt('BUY', '100');

        // the exchange filled it entirely; our ledger has not caught up yet
        $o = $this->sim->open[$l0];
        $o['status'] = 'FILLED';
        $o['executedQty'] = $o['origQty'];
        $this->sim->done[$l0] = $o;
        unset($this->sim->open[$l0]);

        $this->sim->setPrice('190'); // window slides up → prune tries to cancel it
        $daemon->tick();

        $this->assertSame(0, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('cancel_failed')->count(),
            'an already-filled order is not a cancel failure');
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('cancel_gone')->count());
        $row = BotOrderQuery::create()->findOneByClientOrderId($l0);
        $this->assertSame('Filled', (string) $row->getState(), 'fill detection still resolves it the same tick');
    }

    /**
     * Regression (fix 3): a cutover cannot refuse the way maybeRefit does — the
     * incoming engine must not inherit the old book — so an entry that is
     * mid-fill when it is canceled has its executed qty booked and guarded by
     * a legacy exit instead of being orphaned.
     */
    public function testCutoverBooksAnEntryThatWasMidFill(): void
    {
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Grid']);
        $daemon = $this->makeDaemon($run);
        $daemon->tick(); // buys at 100 and 125
        $l1 = $this->cidAt('BUY', '125');
        $this->sim->partialFill($l1, '0.5'); // ledger still says BUY_OPEN

        $run->reload();
        $run->setAlgo('Trend');
        $run->save();
        $daemon->tick();                 // clean-restart request
        $this->makeDaemon($run)->tick(); // boot performs the cutover

        $row = BotOrderQuery::create()->findOneByClientOrderId($l1);
        $this->assertSame('Filled', (string) $row->getState(), 'a mid-fill entry must be booked by the cutover');
        $this->assertSame(0, bccomp((string) $row->getFilledQty(), '0.5', 8));
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('cutover_partial_booked')->count());
        $exit = BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterBySide('Sell')
            ->filterByIsLegacy(true)
            ->findOne();
        $this->assertNotNull($exit, 'the booked inventory is carried as a legacy exit');
        $this->assertSame(0, bccomp((string) $exit->getQty(), '0.5', 8));
    }

    public function testAlgoUnchangedNeverCutsOverAGridRun(): void
    {
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Grid']);
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $this->fillOneBuy($daemon);

        $daemon2 = $this->makeDaemon($run); // plain restart, same algo
        $daemon2->tick();

        $this->assertNotContains('algo_cutover', $this->kinds(), 'a same-algo restart must not touch the ladder');
        $this->assertSame(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByIsLegacy(true)->count(), 'no exit carried');
    }

    /**
     * TrendEngine end to end: a crafted breakout candle series makes
     * entrySignal() true, so a tick places the tranche entry — and (the sim
     * fills a crossing limit immediately, same as the grid tests above) the
     * same tick's fill detection folds it into an open position. Dropping
     * price through the ATR trailing stop on the next tick places the exit,
     * which fills the same way and books the cycle.
     *
     * Uses the DEFAULT signal periods (donchian=20, emaFast=20, emaSlow=50,
     * atr=14), not shrunk ones — entrySignal()'s length guard requires the
     * full emaSlow window (see I4 in the task-4 review), so the fixture must
     * carry 60+ bars for the "true" case to be a genuine signal rather than
     * one that only fires because the periods were made artificially tiny.
     */
    public function testTrendRunEntersOnBreakoutAndExitsOnStop(): void
    {
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
        $run->setDeployPct(100);
        $run->setTrendTf('1h');
        $run->save(); // donchian/emaFast/emaSlow/atr_period keep their schema defaults (20/20/50/14)

        // 45 flat bars @100, then 15 rising: close(129) > the prior 20-bar
        // high, and — with the full 60-bar window behind both — the real
        // (recency-weighted) fast EMA pulls above the real slow EMA
        $candles = array_fill(0, 45, ['high' => '101', 'low' => '99', 'close' => '100']);
        for ($i = 0; $i < 15; $i++) {
            $c = (string) (101 + $i * 2);
            $candles[] = ['high' => $c, 'low' => (string) ((float) $c - 1), 'close' => $c];
        }
        MarketStore::upsert('BTCUSDT', '1h', Indicators::summary($candles), $candles);

        $this->sim->setPrice('130');
        $daemon = $this->makeDaemon($run);
        $daemon->tick(); // places the tranche entry AND (aggressive limit @ market) fills it

        $buys = BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterBySide('Buy')->find()->getArrayCopy();
        $this->assertNotEmpty($buys, 'the breakout placed at least one entry tranche');
        foreach ($buys as $b) {
            $this->assertSame('Filled', (string) $b->getState(), 'the aggressive tranche fills in the same tick as the sim crosses it');
        }
        $marker = $this->marker($run);
        $this->assertSame('Trend', $marker['algo'] ?? null);
        $this->assertNotNull($marker['entry'] ?? null, 'the fill opened a position');
        $this->assertGreaterThan(0, bccomp((string) ($marker['qty'] ?? '0'), '0', 8), 'position qty > 0');
        $this->assertNotNull($marker['stop'] ?? null, 'an initial ATR stop was set on entry');

        // price falls through the trailing stop (well short of the unrealized
        // stop's cap so that guard stays out of this scenario) — the engine
        // places the exit and the sim fills it the same tick
        $this->sim->setPrice('120');
        $daemon->tick();

        $sells = BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterBySide('Sell')->find()->getArrayCopy();
        $this->assertCount(1, $sells, 'the stop breach placed exactly one exit');
        $this->assertSame('Filled', (string) $sells[0]->getState());
        $this->assertSame(0, (int) $sells[0]->getLevelIdx());

        $this->assertSame(1, TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->count(),
            'the exit fill booked exactly one cycle');
        $cycle = TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->findOne();
        $this->assertNotSame(0, bccomp((string) $cycle->getRealizedPnl(), '0', 8), 'realized_pnl is non-zero');
        $this->assertContains('cycle_closed', $this->kinds());

        $marker = $this->marker($run);
        $this->assertArrayHasKey('qty', $marker);
        $this->assertNull($marker['qty'], 'engine_state shows no open position after the stop-out');
        $this->assertArrayHasKey('entry', $marker);
        $this->assertNull($marker['entry']);
    }

    /**
     * I9 (task-4 review): MissingEngine positive coverage. run.algo is now a
     * strict enum(Grid, Trend), so the 'default' arm of Daemon::makeEngine()
     * is unreachable through normal run configuration — but it is still
     * reachable via a CORRUPTED engine_state.algo marker naming an algorithm
     * this deployment has no engine for (the marker is free-form JSON; only
     * run.algo itself is enum-constrained). The reconcile-time engine at boot
     * resolves to MissingEngine for that marker, and a fill missed while down
     * must still be booked (never dropped) and alerted on loudly rather than
     * silently traded or crashed on.
     */
    public function testForeignEngineStateMarkerBooksTheMissedFillViaMissingEngine(): void
    {
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Trend']);
        $run->setEngineState(json_encode(['algo' => 'SomeFutureAlgo']));
        $run->save();

        $cid = 'gt-' . $run->getRunUid() . '-L00-B-1';
        $row = new \App\BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId($cid);
        $row->setLevelIdx(0);
        $row->setSide('Buy');
        $row->setState('BUY_OPEN');
        $row->setPrice('137.77');
        $row->setQty('0.5');
        $row->setSimulated((bool) $run->getSimulated());
        $row->save();
        $this->sim->done[$cid] = [
            'clientOrderId' => $cid, 'orderId' => 9, 'side' => 'BUY',
            'price' => '137.77', 'origQty' => '0.5', 'executedQty' => '0.5', 'status' => 'FILLED',
        ];

        $daemon = $this->makeDaemon($run); // boot: reconcile runs under MissingEngine('SomeFutureAlgo')

        $row->reload();
        $this->assertSame('Filled', (string) $row->getState(), 'the missed fill is booked even with no engine to exit it');
        $this->assertContains('engine_missing', $this->kinds(), 'the guard alerts loudly instead of crashing or silently dropping the fill');
        $this->assertSame(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterBySide('Sell')->count(),
            'no engine placed an exit for it');

        // and it must not crash or silently trade afterwards either — the
        // foreign marker differs from the run's own algo, so boot cuts over
        // into the run's real (Trend) engine; with no market data on hand
        // that engine places nothing
        $this->assertTrue($daemon->tick());
        $this->assertSame(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByState('BUY_OPEN')->count());
    }

    /**
     * I10 (task-4 review): TrendEngine's own stuck-partial handling, with the
     * timeout shrunk to 0s (same technique as
     * DaemonLiveFlowTest::testStuckPartialBuyBookedAfterTimeout). A Trend
     * entry always places an aggressive/marketable limit that the sim fills
     * instantly, so a genuinely RESTING partially-filled order has to be
     * constructed directly (as the off-ladder fixtures above do), then
     * partial-filled on the sim.
     *
     * TrendEngine::handleStuckPartials runs at the START of tick(), i.e.
     * BEFORE the shell's own detectFills — one tick earlier in the pipeline
     * than the grid's checkStuckPartials (which runs right after
     * detectFills, same tick). So unlike the grid's single-tick fixture, this
     * needs two: the first lets detectFills mark the row PartFilled (stamping
     * date_modification); the second's handleStuckPartials sees it past the
     * (0s) timeout and books it.
     */
    public function testTrendStuckPartialEntryBooksTheFillAfterTimeout(): void
    {
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
        $run->setDeployPct(100);
        $run->save();
        $daemon = $this->makeDaemonWithPartialTimeout($run, 0);

        $cid = 'gt-' . $run->getRunUid() . '-L00-B-1';
        $row = new \App\BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId($cid);
        $row->setLevelIdx(0);
        $row->setSide('Buy');
        $row->setState('BUY_OPEN');
        $row->setPrice('150');
        $row->setQty('1.0');
        $row->setSimulated((bool) $run->getSimulated());
        $row->save();
        $this->sim->open[$cid] = [
            'clientOrderId' => $cid, 'orderId' => 501, 'side' => 'BUY',
            'price' => '150', 'origQty' => '1.0', 'executedQty' => '0', 'status' => 'NEW',
        ];
        $this->sim->partialFill($cid, '0.4');

        $daemon->tick(); // detectFills marks it PartFilled (handleStuckPartials ran first this tick and saw BUY_OPEN — one tick early)
        $daemon->tick(); // handleStuckPartials: 0s timeout elapsed → cancel remainder, book 0.4, fold into the position

        $row->reload();
        $this->assertSame('Filled', (string) $row->getState(), 'the stuck partial is booked as filled');
        $this->assertSame(0, bccomp((string) $row->getFilledQty(), '0.4', 8));
        $this->assertArrayNotHasKey($cid, $this->sim->open, 'the remainder was cancelled on the exchange');

        $marker = $this->marker($run);
        $this->assertSame(0, bccomp((string) ($marker['qty'] ?? '0'), '0.4', 8),
            'the booked fill was folded into the tracked position, not left untracked');
        $this->assertContains('partial_timeout', $this->kinds());
    }

    /**
     * C1 (task-4 review): the per-run unrealized-loss stop and soft de-risk
     * stage used to compute inventory from machine+legacy only, so a Trend
     * position was invisible to them — the run could bleed straight through
     * MaxUnrealizedLossQuote with the shell never noticing. Isolates the
     * check from the engine's OWN (tighter, ATR-based) stop by corrupting the
     * persisted stop to an unreachable price after the position is
     * established — otherwise the ATR stop closes the position before the
     * unrealized check's 3-tick confirm window completes, and this test
     * would prove nothing.
     */
    public function testTrendUnrealizedStopSeesAnOpenTrendPosition(): void
    {
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
        $run->setDeployPct(100);
        $run->setTrendTf('1h');
        $run->save();

        $candles = array_fill(0, 45, ['high' => '101', 'low' => '99', 'close' => '100']);
        for ($i = 0; $i < 15; $i++) {
            $c = (string) (101 + $i * 2);
            $candles[] = ['high' => $c, 'low' => (string) ((float) $c - 1), 'close' => $c];
        }
        MarketStore::upsert('BTCUSDT', '1h', Indicators::summary($candles), $candles);

        $this->sim->setPrice('130');
        $daemon = $this->makeDaemon($run);
        $daemon->tick(); // establishes a real position (real Filled buys → real investedQuote())

        $marker = $this->marker($run);
        $this->assertGreaterThan(0, bccomp((string) $marker['qty'], '0', 8), 'fixture: the position opened');

        // Pin hwm AND stop low, not just stop: manageOpenPosition ALWAYS
        // re-ratchets before checking the stop, and ratchet() raises hwm to
        // the current price on its very first call (then re-derives stop
        // from it) — pinning stop alone gets healed right back to a real,
        // reachable level on the next tick. Pinning hwm too means the first
        // tick at the crashed price becomes the new hwm and the ATR stop
        // lands safely below it, keeping the position open across the
        // 3-tick confirm window so this test isolates the UNREALIZED stop's
        // own behavior instead of the engine's own (tighter) exit closing
        // the position first.
        $run->reload();
        $run->setEngineState(json_encode(array_merge($marker, ['stop' => '1', 'hwm' => '1'])));
        $run->save();

        // qty(~7.69) * (130-100) ≈ 231 > MaxUnrealizedLossQuote (0.15×1000=150)
        $this->sim->setPrice('100');
        $daemon2 = $this->makeDaemon($run); // fresh boot re-hydrates the (corrupted) stop
        $daemon2->tick();
        $daemon2->tick();
        $daemon2->tick(); // BREACH_CONFIRM_TICKS = 3

        $run->reload();
        $this->assertTrue((bool) $run->getKillSwitch(), 'the unrealized stop must see and kill an open Trend position');
        $this->assertContains('unrealized_stop', $this->kinds());
    }

    /**
     * C2 (task-4 review): an externally-canceled Trend stop-sell used to be
     * re-placed through the grid's own ladder-level machinery (wrong price,
     * wrong bookkeeping — a Trend order has no ladder level). It must be
     * re-placed at the TREND's own current stop price instead.
     */
    public function testTrendExternallyCanceledStopSellReplacedAtTrendsOwnStopPrice(): void
    {
        // Boot with an empty book first: Daemon::boot()'s own reconcile
        // calls resolveMissing() with NO live price at all (none exists yet
        // at boot — see Daemon::reconcile's 'check_status' case), and
        // TrendEngine::onSellCanceled now correctly declines to re-place
        // without one (see the R1-boot-path test below) — a genuinely
        // confirmed breach can only be established via a live tick's own
        // detectFills(), which threads the real tape price through.
        $this->sim->setPrice('130');
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
        $run->setEngineState(json_encode([
            'algo' => 'Trend', 'entry' => '130', 'qty' => '2.0', 'hwm' => '130',
            'stop' => '126.5', 'fees_paid' => '0', 'stop_out_at' => null,
        ]));
        $run->save();
        $daemon = $this->makeDaemon($run);

        $cid = 'gt-' . $run->getRunUid() . '-L00-S-1';
        $row = new \App\BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId($cid);
        $row->setLevelIdx(0);
        $row->setSide('Sell');
        $row->setState('SELL_OPEN');
        $row->setPrice('126.5');
        $row->setQty('2.0');
        $row->setSimulated((bool) $run->getSimulated());
        $row->save();
        // canceled externally — never reached this daemon's exchangeOpen
        $this->sim->done[$cid] = [
            'clientOrderId' => $cid, 'orderId' => 601, 'side' => 'SELL',
            'price' => '126.5', 'origQty' => '2.0', 'executedQty' => '0', 'status' => 'CANCELED',
        ];
        // price (120) still at/below the stop (126.5) — a genuinely live,
        // CONFIRMED breach, so the re-place must go through (see R1's
        // companion tests below for the "price recovered" and "no live
        // price yet" cases, where it must NOT)
        $this->sim->setPrice('120');

        $daemon->tick(); // detectFills discovers the cancellation WITH the live price this time

        $row->reload();
        $this->assertSame('Canceled', (string) $row->getState());
        $replacement = BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterBySide('Sell')
            ->filterByState('SELL_OPEN')
            ->findOne();
        $this->assertNotNull($replacement, 'the canceled stop-sell was re-placed');
        $this->assertSame(0, bccomp((string) $replacement->getPrice(), '126.5', 8),
            're-placed at the TREND stop price — a grid ladder line ($levels[level+1] on a 4-level 100-200 ladder) would be nowhere near this');
        $this->assertSame(0, bccomp((string) $replacement->getQty(), '2.0', 8));
        $this->assertContains('sell_canceled', $this->kinds());
    }

    /**
     * C3 (task-4 review): Trend entry sizing used to ignore quote already
     * tied up in legacy exits (e.g. carried out of a Grid→Trend cutover),
     * overcommitting the run's own budget by the reserve. tryEnter must size
     * from budget_quote minus legacyReserveQuote(), exactly like the grid's
     * own buildLadder does for its ladder budget.
     */
    public function testTrendEntrySizingExcludesLegacyReserve(): void
    {
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
        $run->setDeployPct(100);
        $run->setTrendTf('1h');
        $run->save();

        // a legacy exit still guarding 600 quote of inventory (e.g. carried
        // out of a prior Grid→Trend cutover) — not re-spendable
        $legacyCid = 'gt-' . $run->getRunUid() . '-L00-S-legacy';
        $legacy = new \App\BotOrder();
        $legacy->setIdGridRun((int) $run->getIdGridRun());
        $legacy->setClientOrderId($legacyCid);
        $legacy->setLevelIdx(0);
        $legacy->setSide('Sell');
        $legacy->setState('SELL_OPEN');
        $legacy->setIsLegacy(true);
        $legacy->setPrice('150'); // 4.0 qty × 150 = 600 reserved
        $legacy->setQty('4.0');
        $legacy->setSimulated((bool) $run->getSimulated());
        $legacy->save();
        $this->sim->open[$legacyCid] = [
            'clientOrderId' => $legacyCid, 'orderId' => 701, 'side' => 'SELL',
            'price' => '150', 'origQty' => '4.0', 'executedQty' => '0', 'status' => 'NEW',
        ];

        $candles = array_fill(0, 45, ['high' => '101', 'low' => '99', 'close' => '100']);
        for ($i = 0; $i < 15; $i++) {
            $c = (string) (101 + $i * 2);
            $candles[] = ['high' => $c, 'low' => (string) ((float) $c - 1), 'close' => $c];
        }
        MarketStore::upsert('BTCUSDT', '1h', Indicators::summary($candles), $candles);

        $this->sim->setPrice('130');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();

        $buys = BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterBySide('Buy')->find()->getArrayCopy();
        $this->assertNotEmpty($buys, 'fixture: the breakout still enters');
        $spent = '0';
        foreach ($buys as $b) {
            $spent = bcadd($spent, bcmul((string) $b->getPrice(), (string) $b->getQty(), 8), 8);
        }
        // budget 1000 − legacy reserve 600 = 400 max — NOT the full 1000
        $this->assertLessThanOrEqual(0, bccomp($spent, '400', 6),
            sprintf('entry notional %s must not exceed budget minus the legacy reserve (400)', $spent));
        $this->assertGreaterThan(0, bccomp($spent, '0', 6), 'fixture: something was actually entered');
    }

    /**
     * R1 (re-review): onSellCanceled used to re-place at the tracked stop
     * price unconditionally. If price recovered back above the stop between
     * the cancellation and this tick, that re-place is a MARKETABLE limit —
     * it fills immediately at a price above the order, liquidating a
     * healthy, still-trending position instead of guarding a real breach.
     * Companion to testTrendExternallyCanceledStopSellReplacedAtTrendsOwnStopPrice
     * (which pins the "still breached — DO re-place" side).
     */
    public function testTrendCanceledStopSellNotReplacedWhenPriceRecoveredAboveStop(): void
    {
        // Boot with an empty book first: Daemon::boot()'s own reconcile
        // calls resolveMissing() WITHOUT a live price (none is available
        // yet at boot — see Daemon::reconcile's 'check_status' case), so it
        // falls back to the canceled row's OWN price — which is exactly the
        // stop price, never actually exercising the "price recovered above
        // the stop" comparison this test exists to pin. Only a live tick's
        // detectFills() threads the real tape price through to
        // onSellCanceled, so the cancellation has to be discovered THERE.
        $this->sim->setPrice('130');
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
        $run->setEngineState(json_encode([
            'algo' => 'Trend', 'entry' => '130', 'qty' => '2.0', 'hwm' => '130',
            'stop' => '126.5', 'fees_paid' => '0', 'stop_out_at' => null,
        ]));
        $run->save();
        $daemon = $this->makeDaemon($run);

        $cid = 'gt-' . $run->getRunUid() . '-L00-S-99'; // not the daemon's own next-sequence cid
        $row = new \App\BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId($cid);
        $row->setLevelIdx(0);
        $row->setSide('Sell');
        $row->setState('SELL_OPEN');
        $row->setPrice('126.5');
        $row->setQty('2.0');
        $row->setSimulated((bool) $run->getSimulated());
        $row->save();
        // canceled externally, and price has since recovered well above the stop
        $this->sim->done[$cid] = [
            'clientOrderId' => $cid, 'orderId' => 602, 'side' => 'SELL',
            'price' => '126.5', 'origQty' => '2.0', 'executedQty' => '0', 'status' => 'CANCELED',
        ];
        $this->sim->setPrice('150');

        $daemon->tick(); // detectFills discovers the cancellation with the LIVE (recovered) price

        $row->reload();
        $this->assertSame('Canceled', (string) $row->getState());
        $this->assertSame(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByState('SELL_OPEN')->count(),
            'price recovered above the stop — must NOT re-place a marketable liquidation');
        $marker = $this->marker($run);
        $this->assertSame(0, bccomp((string) $marker['qty'], '2.0', 8), 'the healthy position is left intact');
        $this->assertSame(0, TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->count(),
            'nothing was liquidated — no cycle to book');

        // and the position is still genuinely guarded: a REAL breach on a
        // later tick re-arms the exit fresh through the normal path and
        // this time actually closes the position
        $this->sim->setPrice('120');
        $daemon->tick();
        $this->assertSame(1, TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->count(),
            'the stop still protects the position on a genuine breach');
        $marker = $this->marker($run);
        $this->assertNull($marker['qty']);
    }

    /**
     * N1 (re-review, CRITICAL): the stray-fill guard added for I6
     * (`state.entry === null && state.stop_out_at !== null`) was permanently
     * true after the FIRST stop-out forever after — state.stop_out_at is
     * never cleared (it also feeds the cooldown check, so it must not be),
     * so EVERY later legitimate entry's fills were being diverted to
     * placeLegacyExit as "stray". Two full cycles, back to back: the second
     * entry must open a real tracked position (no stray_fill anywhere) and
     * then stop out cleanly again — proving the fix (dateCreation vs
     * stop_out_at) actually discriminates instead of just being permanently
     * (in)active.
     */
    public function testTrendTwoFullEntryStopOutCyclesBothTrackCorrectly(): void
    {
        // deploy 50% (not 100%): with the full slice each cycle's realized
        // stop-out loss is close to Balanced's DailyLossLimitQuote
        // (0.06×budget) on its own — TWO cycles in the same test (same
        // calendar day, per OrderStore::realizedToday()) would cumulatively
        // breach it and trip an unrelated risk_kill, vetoing cycle 2's later
        // tranches before this test ever reaches the bug it's pinning. Half
        // size keeps both cycles' combined loss safely under the cap.
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
        $run->setDeployPct(50);
        $run->setTrendTf('1h');
        $run->save();

        $candles = array_fill(0, 45, ['high' => '101', 'low' => '99', 'close' => '100']);
        for ($i = 0; $i < 15; $i++) {
            $c = (string) (101 + $i * 2);
            $candles[] = ['high' => $c, 'low' => (string) ((float) $c - 1), 'close' => $c];
        }
        MarketStore::upsert('BTCUSDT', '1h', Indicators::summary($candles), $candles);

        // cycle 1: enter, then stop out (125 is just below the ATR stop —
        // ~126.05 for this fixture at entry 130 — with margin for bcmath,
        // but shallow enough to keep the realized loss well under the cap)
        $this->sim->setPrice('130');
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $marker = $this->marker($run);
        $this->assertGreaterThan(0, bccomp((string) $marker['qty'], '0', 8), 'fixture: cycle 1 entered');

        $this->sim->setPrice('125');
        $daemon->tick();
        $marker = $this->marker($run);
        $this->assertNull($marker['qty'], 'fixture: cycle 1 stopped out');
        $this->assertNotNull($marker['stop_out_at']);
        $this->assertSame(1, TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->count());

        // Balanced's reentry_cooldown is 3 bars on 1h (10800s) — backdate
        // stop_out_at past that window instead of sleeping. NOT cleared, per
        // the reviewer's note: it must keep feeding cooldownElapsed().
        $run->reload();
        $marker = json_decode((string) $run->getEngineState(), true);
        $marker['stop_out_at'] = date('Y-m-d H:i:s', time() - 5 * 3600);
        $run->setEngineState(json_encode($marker));
        $run->save();

        // cycle 2: re-entry (fresh boot re-hydrates the backdated cooldown anchor)
        $this->sim->setPrice('130');
        $daemon2 = $this->makeDaemon($run);
        $daemon2->tick();

        $this->assertNotContains('stray_fill', $this->kinds(), 'the second entry\'s own fills must never be diverted as stray');
        $marker = $this->marker($run);
        $this->assertNotNull($marker['entry'], 'cycle 2 opened a real TRACKED position, not a legacy write-off');
        $this->assertGreaterThan(0, bccomp((string) $marker['qty'], '0', 8), 'cycle 2 position qty > 0');
        $buys = BotOrderQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->filterBySide('Buy')->find();
        foreach ($buys as $b) {
            $this->assertSame('Filled', (string) $b->getState(), 'no leftover/legacy-diverted buy from either batch');
        }

        // cycle 2: stop out cleanly too
        $this->sim->setPrice('125');
        $daemon2->tick();

        $this->assertNotContains('stray_fill', $this->kinds());
        $marker = $this->marker($run);
        $this->assertNull($marker['qty'], 'cycle 2 stopped out and cleared, exactly like cycle 1');
        $this->assertSame(2, TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->count(),
            'both cycles booked a real cycle — neither routed through the legacy write-off path');
        $this->assertSame(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByIsLegacy(true)->count(),
            'nothing was ever diverted to a legacy exit across either cycle');
    }

    /**
     * Item 1 (round-2 re-review): the N1 discriminator (row date_creation vs
     * state.stop_out_at) used `<=`, so a SAME-SECOND row was misclassified
     * as stray. Reviewer's E1 probe recipe: TrendEngine::handleStuckPartials
     * runs at the TOP of tick() — a stuck partial's full exit there sets
     * stop_out_at to "now", and on a 0-bar-cooldown profile (Max)
     * cooldownElapsed() clears immediately, so tryEnter() re-enters in the
     * SAME tick, same wall-clock second. With `<=`, every one of that new
     * batch's fills reads dateCreation == stop_out_at and gets diverted as
     * "stray" — the whole freshly-bought slice gets dumped as legacy exits
     * in the very same tick it was bought. Fixed to strict `<`.
     */
    public function testTrendSameTickReentryAfterStuckPartialFullExitIsNotStray(): void
    {
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Max', 'budget_quote' => '1000']);
        $run->setDeployPct(100);
        $run->setTrendTf('1h');
        $run->setEngineState(json_encode([
            'algo' => 'Trend', 'entry' => '130', 'qty' => '0.4', 'hwm' => '130',
            'stop' => '100', 'fees_paid' => '0', 'stop_out_at' => null,
        ]));
        $run->save();

        // a stuck partial stop-sell for the CURRENT (small) position —
        // handleStuckPartials resolves it as a full exit at the TOP of the
        // very same tick that also re-enters (Max profile: 0-bar cooldown)
        $cid = 'gt-' . $run->getRunUid() . '-L00-S-1';
        $row = new \App\BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId($cid);
        $row->setLevelIdx(0);
        $row->setSide('Sell');
        $row->setState('PartFilled');
        $row->setPrice('125');
        $row->setQty('1.0');
        $row->setFilledQty('0.4');
        $row->setSimulated((bool) $run->getSimulated());
        $row->save();
        $this->sim->open[$cid] = [
            'clientOrderId' => $cid, 'orderId' => 801, 'side' => 'SELL',
            'price' => '125', 'origQty' => '1.0', 'executedQty' => '0.4', 'status' => 'PARTIALLY_FILLED',
        ];

        $candles = array_fill(0, 45, ['high' => '101', 'low' => '99', 'close' => '100']);
        for ($i = 0; $i < 15; $i++) {
            $c = (string) (101 + $i * 2);
            $candles[] = ['high' => $c, 'low' => (string) ((float) $c - 1), 'close' => $c];
        }
        MarketStore::upsert('BTCUSDT', '1h', Indicators::summary($candles), $candles);

        $this->sim->setPrice('130');
        $daemon = $this->makeDaemonWithPartialTimeout($run, 0); // 0s timeout: the partial is stale immediately
        $daemon->tick(); // handleStuckPartials full-exits the old position, THEN (same tick, Max cooldown=0) re-enters

        $this->assertNotContains('stray_fill', $this->kinds(), 'the same-tick re-entry batch must never be diverted as stray');
        $marker = $this->marker($run);
        $this->assertNotNull($marker['entry'], 'entry #2 opened a real tracked position');
        $this->assertGreaterThan(0, bccomp((string) $marker['qty'], '0', 8), 'entry #2 position qty > 0');
        $this->assertSame(1, TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->count(),
            'exactly the ONE stuck-partial exit\'s cycle — no phantom legacy cycles from misclassified stray fills');
        $this->assertSame(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByIsLegacy(true)->count(),
            'nothing from entry #2 was diverted to a legacy exit');
    }

    /**
     * Item 2 (round-2 re-review): onSellCanceled discovered via
     * Daemon::boot()'s own reconcile receives no live price at all (the
     * shell used to fall back to $row->getPrice(), which for a stop-sell
     * IS the stop — so the price>stop guard could never fire there, no
     * matter how far price had actually recovered by the time the daemon
     * restarted). A restart between an external cancel and the next tick
     * must not liquidate a healthy position on stale information. $price is
     * now threaded through nullable and TrendEngine declines outright when
     * it's null, deferring to the next tick's REAL price.
     */
    public function testTrendCanceledStopSellNotReplacedAtBootWithNoLivePriceYet(): void
    {
        $this->sim->setPrice('130');
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
        $run->setEngineState(json_encode([
            'algo' => 'Trend', 'entry' => '130', 'qty' => '2.0', 'hwm' => '130',
            'stop' => '126.5', 'fees_paid' => '0', 'stop_out_at' => null,
        ]));
        $run->save();

        $cid = 'gt-' . $run->getRunUid() . '-L00-S-99';
        $row = new \App\BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId($cid);
        $row->setLevelIdx(0);
        $row->setSide('Sell');
        $row->setState('SELL_OPEN');
        $row->setPrice('126.5');
        $row->setQty('2.0');
        $row->setSimulated((bool) $run->getSimulated());
        $row->save();
        // canceled while this daemon was down — discovered at BOOT, before
        // any tick (and so any live price) exists
        $this->sim->done[$cid] = [
            'clientOrderId' => $cid, 'orderId' => 603, 'side' => 'SELL',
            'price' => '126.5', 'origQty' => '2.0', 'executedQty' => '0', 'status' => 'CANCELED',
        ];

        $daemon = $this->makeDaemon($run); // boot's own reconcile — no live price available

        $row->reload();
        $this->assertSame('Canceled', (string) $row->getState());
        $this->assertSame(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByState('SELL_OPEN')->count(),
            'no live price at boot — must NOT guess and re-place blind');
        $marker = $this->marker($run);
        $this->assertSame(0, bccomp((string) $marker['qty'], '2.0', 8), 'the position is left intact');

        // one tick at a price still well above the stop: still correctly
        // does nothing — the boot-time decline didn't leave anything stuck
        $this->sim->setPrice('150');
        $daemon->tick();
        $this->assertSame(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByState('SELL_OPEN')->count());
        $marker = $this->marker($run);
        $this->assertSame(0, bccomp((string) $marker['qty'], '2.0', 8), 'still intact after a live tick above the stop');
    }

    /**
     * Item 3 / E3 (round-2 re-review, inherited from the I6/N1 stray-fill
     * guard): the guard only fired when state.entry was null. A stray fill
     * predating the last stop-out, arriving while a NEW position is already
     * live (e.g. cancelOwnOpenBuys hit a gateway error on this exact row and
     * left it open — see its own Warn), fell through to the normal
     * VWAP-fold path and silently corrupted the LIVE position's entry price
     * with data from an unrelated, already-closed trade. The discriminator
     * now applies regardless of state.entry.
     */
    public function testTrendPreStopOutStrayFillNeverCorruptsALivePosition(): void
    {
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
        $run->save();
        // a live position, opened well AFTER a stop-out an hour ago
        $run->setEngineState(json_encode([
            'algo' => 'Trend', 'entry' => '130', 'qty' => '2.0', 'hwm' => '130',
            'stop' => '126.5', 'fees_paid' => '0',
            'stop_out_at' => date('Y-m-d H:i:s', time() - 3600),
        ]));
        $run->save();

        // a stray fill from the batch that stop-out closed — created BEFORE
        // it, but only discovered now (e.g. it raced cancelOwnOpenBuys)
        $this->sim->setPrice('130');
        $cid = 'gt-' . $run->getRunUid() . '-L00-B-1';
        $row = new \App\BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId($cid);
        $row->setLevelIdx(0);
        $row->setSide('Buy');
        $row->setState('BUY_OPEN');
        $row->setPrice('116.67'); // a materially different price from the live position's entry
        $row->setQty('0.3');
        $row->setSimulated((bool) $run->getSimulated());
        $row->save();
        $con = \Propel::getConnection();
        $con->prepare('UPDATE bot_order SET date_creation = ? WHERE id_bot_order = ?')
            ->execute([date('Y-m-d H:i:s', time() - 7200), $row->getIdBotOrder()]); // 2h ago — before the 1h-ago stop-out
        // the raw UPDATE bypasses Propel's identity map — without reload(),
        // a later Propel-layer fetch of this same row (by PK, during boot's
        // own reconcile) returns the STALE pooled object with the ORIGINAL
        // (un-backdated) date_creation instead of hitting the DB
        $row->reload();
        $this->sim->done[$cid] = [
            'clientOrderId' => $cid, 'orderId' => 901, 'side' => 'BUY',
            'price' => '116.67', 'origQty' => '0.3', 'executedQty' => '0.3', 'status' => 'FILLED',
        ];

        $this->makeDaemon($run); // boot's reconcile discovers the stray fill

        $this->assertContains('stray_fill', $this->kinds());
        $marker = $this->marker($run);
        $this->assertSame(0, bccomp((string) $marker['entry'], '130', 8),
            "the LIVE position's entry must not be corrupted by an unrelated stray fill's price");
        $this->assertSame(0, bccomp((string) $marker['qty'], '2.0', 8),
            "the LIVE position's qty must not be inflated by the stray fill");
        $legacy = BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByIsLegacy(true)->findOne();
        $this->assertNotNull($legacy, 'the stray fill was diverted to a legacy exit instead');
        $this->assertSame(0, bccomp((string) $legacy->getQty(), '0.3', 8));
    }

    /** The breakout fixture every Trend entry test uses: 45 flat bars @100
     *  then 15 rising, on the schema-default periods (20/20/50/14). */
    private function breakoutCandles(): void
    {
        $candles = array_fill(0, 45, ['high' => '101', 'low' => '99', 'close' => '100']);
        for ($i = 0; $i < 15; $i++) {
            $c = (string) (101 + $i * 2);
            $candles[] = ['high' => $c, 'low' => (string) ((float) $c - 1), 'close' => $c];
        }
        MarketStore::upsert('BTCUSDT', '1h', Indicators::summary($candles), $candles);
    }

    /**
     * H1 (round-4 review, CRITICAL — this is the plan's headline operator
     * action): a HEALTHY Trend position has no working sell (price is above
     * the trailing stop, so no exit is armed) and exists ONLY in
     * engine_state. The cutover's stampEngineAlgo(true) erases that column,
     * so before this fix the whole position was silently abandoned: the
     * incoming GridEngine's heldQty() read 0 (the unrealized stop and the
     * drawdown rails blind to real inventory), legacyReserveQuote() was 0 (so
     * the new ladder re-spent the very capital still sitting in base), and
     * nothing would ever sell it. The outgoing engine must hand it off as a
     * legacy exit while it is still hydrated.
     */
    public function testTrendToGridCutoverHandsOffAnUnguardedHealthyPosition(): void
    {
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
        $run->setDeployPct(100);
        $run->setTrendTf('1h');
        $run->save();
        $this->breakoutCandles();

        $this->sim->setPrice('130');
        $daemon = $this->makeDaemon($run);
        $daemon->tick(); // breakout entry fills → a real, healthy position

        $marker = $this->marker($run);
        $posQty = (string) $marker['qty'];
        $entry = (string) $marker['entry'];
        $this->assertGreaterThan(0, bccomp($posQty, '0', 8), 'fixture: the position opened');
        $this->assertSame(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterBySide('Sell')->count(),
            'fixture: a healthy position has NO working sell — nothing for the shell to carry');

        // drift down a little, still comfortably above the ATR stop (~126.05)
        // so the position stays healthy and un-armed right up to the flip
        $this->sim->setPrice('128');
        $run->reload();
        $run->setAlgo('Grid');
        $run->save();
        $this->assertFalse($daemon->tick(), 'the algo flip requests a clean restart');

        $daemon2 = $this->makeDaemon($run); // boot performs the cutover
        $daemon2->tick();

        $legacy = BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByIsLegacy(true)
            ->find()
            ->getArrayCopy();
        $this->assertCount(1, $legacy, 'the abandoned position is handed off as exactly one legacy exit');
        $this->assertSame(0, bccomp((string) $legacy[0]->getQty(), $posQty, 8),
            'the legacy exit covers the FULL position qty — no silent abandonment');
        $this->assertSame('SELL_OPEN', (string) $legacy[0]->getState());
        $this->assertSame(0, bccomp((string) $legacy[0]->getPrice(), $entry, 8),
            'handed off at cost: an operator\'s algo flip must not realize a loss by itself');
        $this->assertSame(0, bccomp((string) $legacy[0]->getLegacyBuyPrice(), $entry, 8),
            'the buy it books against is pinned, not left to the level-idx heuristic');

        // engine_state is reset — the legacy exit is now the ONLY record of
        // this inventory, which is exactly why it has to exist
        $this->assertSame(['algo' => 'Grid'], $this->marker($run));
        $this->assertSame(0, bccomp($daemon2->store->legacyRemainingQty(), $posQty, 8),
            'GridEngine::heldQty() (machine + legacyRemainingQty) now sees the carried inventory');

        // and the incoming ladder must NOT re-spend it: the whole budget is
        // still tied up in base, so the reserve leaves nothing to deploy
        $this->assertSame(0, bccomp(
            bcmul($posQty, $entry, 8),
            $daemon2->store->legacyReserveQuote(),
            6
        ), 'the reserve is the position\'s real cost basis');
        $this->assertSame(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByState('BUY_OPEN')->count(),
            'the new grid ladder is sized on budget MINUS the reserve — with the full budget still in base it deploys nothing');
        $this->assertSame(0, TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->count(),
            'nothing was sold — the position is guarded, not liquidated');

        // end to end: the handed-off exit is a real, working exit under the
        // NEW engine — price back through cost fills it and the shell books
        // the round trip against the pinned entry (and its pinned entry fees)
        $this->sim->setPrice('131');
        $daemon2->tick();
        $cycles = TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->find()->getArrayCopy();
        $this->assertCount(1, $cycles, 'the carried inventory closes a real cycle under the incoming engine');
        $this->assertSame(0, bccomp((string) $cycles[0]->getBuyPrice(), $entry, 8),
            'booked against the trend position\'s own entry, not a heuristic guess');
        $this->assertSame(0, bccomp((string) $cycles[0]->getQty(), $posQty, 8));
        $this->assertGreaterThan(0, bccomp((string) $cycles[0]->getFeesTotal(), '0', 8),
            'the entry fees paid by the trend position are carried into the cycle');
        $this->assertSame(0, bccomp($daemon2->store->legacyRemainingQty(), '0', 8),
            'the capital is free again once it fills');
    }

    /**
     * H1 companion (A1b): the same flip while a stop-sell IS working for the
     * whole position. The shell already carries that sell as a legacy exit,
     * so the handoff must recognise the position as covered and add nothing —
     * carried exactly once, never double-guarded (which would place an exit
     * for base the run does not hold and double the reserve).
     */
    public function testTrendToGridCutoverDoesNotDoubleCarryAGuardedPosition(): void
    {
        $this->sim->setPrice('120'); // below the working stop-sell: it rests
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
        $run->setEngineState(json_encode([
            'algo' => 'Trend', 'entry' => '130', 'qty' => '2.0', 'hwm' => '130',
            'stop' => '126.5', 'fees_paid' => '0.26', 'stop_out_at' => null,
        ]));
        $run->save();

        $cid = 'gt-' . $run->getRunUid() . '-L00-S-1';
        $row = new \App\BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId($cid);
        $row->setLevelIdx(0);
        $row->setSide('Sell');
        $row->setState('SELL_OPEN');
        $row->setPrice('126.5');
        $row->setQty('2.0');
        $row->setSimulated((bool) $run->getSimulated());
        $row->save();
        $this->sim->open[$cid] = [
            'clientOrderId' => $cid, 'orderId' => 951, 'side' => 'SELL',
            'price' => '126.5', 'origQty' => '2.0', 'executedQty' => '0', 'status' => 'NEW',
        ];

        $this->makeDaemon($run); // boot under Trend (marker already says Trend)
        $run->reload();
        $run->setAlgo('Grid');
        $run->save();
        $daemon2 = $this->makeDaemon($run); // boot performs the cutover

        $sells = BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterBySide('Sell')->find()->getArrayCopy();
        $this->assertCount(1, $sells, 'the position is carried EXACTLY once — no second exit for inventory that is already guarded');
        $this->assertSame($cid, $sells[0]->getClientOrderId());
        $this->assertTrue((bool) $sells[0]->getIsLegacy(), 'the working exit was carried as a legacy exit');
        $this->assertSame(0, bccomp($daemon2->store->legacyRemainingQty(), '2.0', 8),
            'the run still guards exactly the 2.0 it holds — not 4.0');
    }

    /**
     * Task-5 review (b): MissingEngine::handoffUnguardedPosition. A cutover
     * OUT OF a foreign/uninterpretable engine_state marker (the same
     * "SomeFutureAlgo" fixture as the MissingEngine boot-time coverage above)
     * runs through MissingEngine as the OUTGOING engine. It cannot know what
     * the foreign state's keys mean, but a `qty` key — the convention every
     * real engine uses for its own held position — surviving under a marker
     * this deployment cannot read means real inventory may be about to lose
     * its only guard. That must alert loudly, never pass silently.
     */
    public function testMissingEngineAlertsWhenAForeignMarkerCarriesAQty(): void
    {
        $run = $this->makeRun(['algo' => 'Grid']);
        $run->setEngineState(json_encode(['algo' => 'SomeFutureAlgo', 'qty' => '1.5', 'entry' => '140']));
        $run->save();

        $this->makeDaemon($run); // boot: reconcile under MissingEngine('SomeFutureAlgo'), then cuts over to Grid

        $alert = BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByKind('algo_cutover')
            ->filterByLevel('Alert')
            ->filterByMessage('cannot hand off a position from an uninterpretable engine state (algo=SomeFutureAlgo, qty=1.5) — inventory unaccounted, human review')
            ->findOne();
        $this->assertNotNull($alert, 'a qty surviving under an unreadable marker must alert for human review');
    }

    /** Negative control: a foreign marker with no qty key (or a null one) hands
     *  off silently — MissingEngine never opened a position of its own, so
     *  there is nothing to alert about. (The cutover itself still logs its
     *  own summary algo_cutover event — that's unrelated to the handoff.) */
    public function testMissingEngineHandsOffSilentlyWhenTheForeignMarkerCarriesNoQty(): void
    {
        $run = $this->makeRun(['algo' => 'Grid']);
        $run->setEngineState(json_encode(['algo' => 'SomeFutureAlgo']));
        $run->save();

        $this->makeDaemon($run);

        $this->assertSame(0, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByKind('algo_cutover')
            ->filterByMessage('cannot hand off%', \Criteria::LIKE)
            ->count(), 'nothing to hand off — no handoff alert needed');
    }

    /**
     * Task-5 review (c): Daemon::placeLegacyExit now returns bool, and
     * TrendEngine's handoff path escalates a false return into its own
     * explicit alert — a dust write-off (or a rejected order) during a
     * handoff is not "nothing to worry about" like an ordinary legacy-exit
     * placement, it is a position that is about to have NO exit at all.
     */
    public function testTrendToGridCutoverHandoffFailureRaisesItsOwnAlert(): void
    {
        $this->sim->setPrice('100');
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
        // whisper-thin position: qty x entry (0.001 x 100 = 0.1) sits well
        // below the sim's minNotional (5) — placeLegacyExit writes it off as
        // dust and returns false.
        $run->setEngineState(json_encode([
            'algo' => 'Trend', 'entry' => '100', 'qty' => '0.001', 'hwm' => '100',
            'stop' => null, 'fees_paid' => '0.0001', 'stop_out_at' => null,
        ]));
        $run->save();

        $this->makeDaemon($run); // boot under Trend (marker already says Trend)
        $run->reload();
        $run->setAlgo('Grid');
        $run->save();
        $this->makeDaemon($run); // boot performs the cutover; the handoff fails as dust

        $this->assertContains('partial_dust', $this->kinds(), 'fixture: the tiny remainder is written off as dust');
        $alert = BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByKind('algo_cutover')
            ->filterByLevel('Alert')
            ->filterByMessage('handoff failed — position unaccounted')
            ->findOne();
        $this->assertNotNull($alert, 'a failed handoff must raise its own alert beyond the dust write-off');
    }

    /**
     * H2 (round-4 review): cancelOwnOpenBuys ignored the cancel response's
     * executedQty, so a leftover entry tranche the exchange had already
     * (partly) executed was marked Canceled at zero fee — real base the
     * wallet holds, erased from the ledger. That breaks the same invariant
     * the shell's own cutover teardown documents ("NEVER discards a fill").
     */
    public function testTrendLeftoverTrancheExecutedAtCancelIsBookedNotDiscarded(): void
    {
        $this->sim->setPrice('120'); // keeps the legacy exit (@129) resting
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
        $run->setEngineState(json_encode([
            'algo' => 'Trend', 'entry' => '130', 'qty' => '1.0', 'hwm' => '130',
            'stop' => '126.5', 'fees_paid' => '0.13', 'stop_out_at' => null,
        ]));
        $run->save();

        // the exit that closes the position (filled while the daemon was down)
        $sellCid = 'gt-' . $run->getRunUid() . '-L00-S-1';
        $sell = new \App\BotOrder();
        $sell->setIdGridRun((int) $run->getIdGridRun());
        $sell->setClientOrderId($sellCid);
        $sell->setLevelIdx(0);
        $sell->setSide('Sell');
        $sell->setState('SELL_OPEN');
        $sell->setPrice('126.5');
        $sell->setQty('1.0');
        $sell->setSimulated((bool) $run->getSimulated());
        $sell->save();
        $this->sim->done[$sellCid] = [
            'clientOrderId' => $sellCid, 'orderId' => 961, 'side' => 'SELL',
            'price' => '126.5', 'origQty' => '1.0', 'executedQty' => '1.0', 'status' => 'FILLED',
        ];

        // a leftover entry tranche still working — and half executed by the
        // time the full exit's cancelOwnOpenBuys reaches it
        $buyCid = 'gt-' . $run->getRunUid() . '-L01-B-2';
        $buy = new \App\BotOrder();
        $buy->setIdGridRun((int) $run->getIdGridRun());
        $buy->setClientOrderId($buyCid);
        $buy->setLevelIdx(1);
        $buy->setSide('Buy');
        $buy->setState('BUY_OPEN');
        $buy->setPrice('129');
        $buy->setQty('1.0');
        $buy->setSimulated((bool) $run->getSimulated());
        $buy->save();
        $this->sim->open[$buyCid] = [
            'clientOrderId' => $buyCid, 'orderId' => 962, 'side' => 'BUY',
            'price' => '129', 'origQty' => '1.0', 'executedQty' => '0', 'status' => 'NEW',
        ];
        $this->sim->partialFill($buyCid, '0.5');

        $daemon = $this->makeDaemon($run); // boot's reconcile books the exit → full exit → cancelOwnOpenBuys

        $buy->reload();
        $this->assertSame('Filled', (string) $buy->getState(), 'the executed half must be booked, never discarded as Canceled');
        $this->assertSame(0, bccomp((string) $buy->getFilledQty(), '0.5', 8));
        $this->assertGreaterThan(0, bccomp((string) $buy->getFeePaid(), '0', 8), 'booked with a real fee, not zero');
        $this->assertArrayNotHasKey($buyCid, $this->sim->open, 'the remainder was cancelled on the exchange');

        $legacy = BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByIsLegacy(true)->find()->getArrayCopy();
        $this->assertCount(1, $legacy, 'the booked half is immediately guarded by a legacy exit');
        $this->assertSame(0, bccomp((string) $legacy[0]->getQty(), '0.5', 8));
        $this->assertSame(0, bccomp((string) $legacy[0]->getPrice(), '129', 8), 'exited at its own cost');
        $this->assertSame(0, bccomp((string) $legacy[0]->getLegacyBuyPrice(), '129', 8), 'attributed to itself, not guessed');
        $this->assertContains('stray_fill', $this->kinds());

        // …and the pin has to survive a re-place: a legacy exit canceled on the
        // exchange is re-placed by the shell, and a replacement that dropped the
        // pin would fall straight back to the level-idx heuristic
        $legacyCid = $legacy[0]->getClientOrderId();
        $this->sim->done[$legacyCid] = ['clientOrderId' => $legacyCid, 'orderId' => 963, 'side' => 'SELL',
            'price' => '129', 'origQty' => '0.5', 'executedQty' => '0', 'status' => 'CANCELED'];
        unset($this->sim->open[$legacyCid]);
        $daemon->tick();

        $replacement = BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByIsLegacy(true)->filterByState('SELL_OPEN')->findOne();
        $this->assertNotNull($replacement, 'the canceled legacy exit was re-placed');
        $this->assertNotSame($legacyCid, $replacement->getClientOrderId());
        $this->assertSame(0, bccomp((string) $replacement->getLegacyBuyPrice(), '129', 8),
            'the replacement carries the pinned attribution forward');
    }

    /**
     * H1 fallout: the shell's cutover teardown books a mid-fill entry and
     * exits it one LADDER LINE up — which is the grid's own exit for that
     * level, and meaningless for any other engine's entry. A Trend tranche at
     * level idx 0 bought at 129 would be exited at the ladder's 125 line:
     * an immediate, arbitrary loss caused purely by an operator's algo flip.
     * A non-grid book exits at its own cost, attribution pinned to itself.
     */
    public function testCutoverExitsAMidFillNonGridEntryAtItsOwnCostNotALadderLine(): void
    {
        $this->sim->setPrice('120');
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
        // marker only — no tracked position, so this isolates the shell's own
        // teardown of a working entry from the engine's position handoff
        $run->setEngineState(json_encode(['algo' => 'Trend']));
        $run->save();
        $this->makeDaemon($run);

        $cid = 'gt-' . $run->getRunUid() . '-L00-B-1';
        $row = new \App\BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId($cid);
        $row->setLevelIdx(0); // ladder line above level 0 is 125 — BELOW this cost
        $row->setSide('Buy');
        $row->setState('BUY_OPEN');
        $row->setPrice('129');
        $row->setQty('1.0');
        $row->setSimulated((bool) $run->getSimulated());
        $row->save();
        $this->sim->open[$cid] = [
            'clientOrderId' => $cid, 'orderId' => 981, 'side' => 'BUY',
            'price' => '129', 'origQty' => '1.0', 'executedQty' => '0', 'status' => 'NEW',
        ];
        $this->sim->partialFill($cid, '0.5');

        $run->reload();
        $run->setAlgo('Grid');
        $run->save();
        $this->makeDaemon($run); // boot performs the cutover

        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('cutover_partial_booked')->count());
        $legacy = BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByIsLegacy(true)->find()->getArrayCopy();
        $this->assertCount(1, $legacy);
        $this->assertSame(0, bccomp((string) $legacy[0]->getQty(), '0.5', 8));
        $this->assertSame(0, bccomp((string) $legacy[0]->getPrice(), '129', 8),
            'exited at its own cost — 125 (the ladder line above level 0) would sell it at a loss the operator never asked for');
        $this->assertSame(0, bccomp((string) $legacy[0]->getLegacyBuyPrice(), '129', 8));
    }

    /**
     * H3 (round-4 review): a stray fill's legacy exit used to carry no
     * attribution, so Daemon::resolveLegacy fell back to its level-index
     * heuristic — which matches the newest same-level filled buy, i.e. the
     * LIVE batch's buy. The stray's cycle was booked against a price it had
     * nothing to do with (a fabricated loss) and that live buy was attributed
     * a second time when its own exit closed, corrupting realizedToday — the
     * input to the daily-loss kill. The stray path knows its own fill price,
     * so the legacy row now pins it.
     */
    public function testStrayLegacyExitBooksAgainstItsOwnBuyNotTheLiveBatch(): void
    {
        $this->sim->setPrice('130');
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
        $run->setEngineState(json_encode([
            'algo' => 'Trend', 'entry' => '130', 'qty' => '2.0', 'hwm' => '130',
            'stop' => '126.5', 'fees_paid' => '0.26',
            'stop_out_at' => date('Y-m-d H:i:s', time() - 3600),
        ]));
        $run->save();

        // the stray: same level idx, created BEFORE the last stop-out, filled
        // late (it raced cancelOwnOpenBuys) at a very different price.
        // Inserted BEFORE the live batch's buy on purpose — that is the real
        // chronology (old batch, then the post-cooldown one), and the
        // heuristic's `orderByIdBotOrder(DESC)` picks the NEWEST matching row,
        // so with the rows the other way round it would land on the stray by
        // accident and this test would pass without the fix.
        $strayCid = 'gt-' . $run->getRunUid() . '-L00-B-1';
        $stray = new \App\BotOrder();
        $stray->setIdGridRun((int) $run->getIdGridRun());
        $stray->setClientOrderId($strayCid);
        $stray->setLevelIdx(0);
        $stray->setSide('Buy');
        $stray->setState('BUY_OPEN');
        $stray->setPrice('116.67');
        $stray->setQty('0.3');
        $stray->setSimulated((bool) $run->getSimulated());
        $stray->save();
        $con = \Propel::getConnection();
        $con->prepare('UPDATE bot_order SET date_creation = ? WHERE id_bot_order = ?')
            ->execute([date('Y-m-d H:i:s', time() - 7200), $stray->getIdBotOrder()]);
        $stray->reload(); // the raw UPDATE bypasses Propel's identity map
        $this->sim->done[$strayCid] = [
            'clientOrderId' => $strayCid, 'orderId' => 971, 'side' => 'BUY',
            'price' => '116.67', 'origQty' => '0.3', 'executedQty' => '0.3', 'status' => 'FILLED',
        ];

        // the LIVE batch's own buy: same level idx, filled, created after the
        // stop-out — the newest level-0 buy, i.e. exactly the row the
        // heuristic would (wrongly) attribute the stray's cycle to
        $liveCid = 'gt-' . $run->getRunUid() . '-L00-B-2';
        $live = new \App\BotOrder();
        $live->setIdGridRun((int) $run->getIdGridRun());
        $live->setClientOrderId($liveCid);
        $live->setLevelIdx(0);
        $live->setSide('Buy');
        $live->setState('Filled');
        $live->setPrice('130');
        $live->setQty('2.0');
        $live->setFilledQty('2.0');
        $live->setFeePaid('0.26');
        $live->setSimulated((bool) $run->getSimulated());
        $live->save();

        $daemon = $this->makeDaemon($run); // boot: stray fill → pinned legacy exit
        $this->assertContains('stray_fill', $this->kinds());
        $legacy = BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByIsLegacy(true)->findOne();
        $this->assertNotNull($legacy);
        $this->assertSame(0, bccomp((string) $legacy->getLegacyBuyPrice(), '116.67', 8));

        // the legacy exit (placed at 116.67, crossed by the 130 tape) fills →
        // its cycle must book against ITS OWN buy
        $daemon->tick();
        $cycles = TradeCycleQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->find()->getArrayCopy();
        $this->assertCount(1, $cycles, 'the stray round trip booked one cycle');
        $this->assertSame(0, bccomp((string) $cycles[0]->getBuyPrice(), '116.67', 8),
            "booked against the stray's own buy price — the live batch's 130 buy is not its counterparty");
        $this->assertLessThanOrEqual(0, bccomp(bcmul((string) $cycles[0]->getRealizedPnl(), '-1', 8), '1', 8),
            'pnl is its own (fee-sized) spread, not the ~-4 fabricated from two unrelated trades');

        // and the live position's OWN exit still books its own cycle against
        // its own buy — the live buy is used once, by the trade it belongs to
        $this->sim->setPrice('120');
        $daemon->tick();
        $cycles = TradeCycleQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->orderByIdTradeCycle()->find()->getArrayCopy();
        $this->assertCount(2, $cycles);
        $this->assertSame(0, bccomp((string) $cycles[1]->getBuyPrice(), '130', 8));
        $this->assertSame(0, bccomp((string) $cycles[1]->getQty(), '2.0', 8),
            'the live position closed in full against its own entry');
    }

    /**
     * I2 (final-fix review): TrendEngine::tryEnter returned silently when
     * tranches() came back empty at deploy_pct=0 — an operator flattening a
     * live Trend run via deploy_pct got no confirmation entries were idle.
     * Mirrors Daemon::$flatLogged's grid-path idiom: one Info line per flat
     * spell, not one per tick, even though tryEnter re-evaluates a genuine
     * breakout signal every tick.
     */
    public function testTrendFlatAtZeroDeployLogsFlatOnceNotPerTick(): void
    {
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
        $run->setDeployPct(0);
        $run->setTrendTf('1h');
        $run->save();

        // same breakout fixture as testTrendRunEntersOnBreakoutAndExitsOnStop
        // — a genuine signal every tick, so tryEnter reaches the tranches()
        // check (not gated out earlier by stale/no-signal)
        $candles = array_fill(0, 45, ['high' => '101', 'low' => '99', 'close' => '100']);
        for ($i = 0; $i < 15; $i++) {
            $c = (string) (101 + $i * 2);
            $candles[] = ['high' => $c, 'low' => (string) ((float) $c - 1), 'close' => $c];
        }
        MarketStore::upsert('BTCUSDT', '1h', Indicators::summary($candles), $candles);

        $this->sim->setPrice('130');
        $daemon = $this->makeDaemon($run);

        $daemon->tick();
        $this->assertSame(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterBySide('Buy')->count(),
            'deploy 0% places no entries despite the live breakout signal');
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('flat')->count(),
            'the flat notice fires once entries were refused at deploy 0%');

        // the same signal re-evaluates every tick — the notice must not repeat
        $daemon->tick();
        $daemon->tick();
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('flat')->count(),
            'flat is logged once per flat spell, not once per tick');
    }

    /**
     * I1 (final-fix review): Daemon::GEOMETRY_KEYS keeps deploy_pct/
     * budget_quote out of the per-tick config merge (Daemon::tick() line
     * ~279) — only maybeRefit adopts them mid-run, and only GridEngine calls
     * it, so a live Trend run silently keeps sizing off the config it booted
     * with until restarted. The minimum-viable fix (chosen over threading
     * the fresh values into TrendEngine's own config — see TrendEngine::
     * warnConfigDrift's docblock) is a Warn notice, once per distinct live
     * drift value, so the drift isn't invisible.
     */
    public function testTrendConfigDriftWarnsOnceThenAgainOnlyOnANewDrift(): void
    {
        $this->sim->setPrice('130');
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
        $run->setDeployPct(100);
        $run->save();
        $daemon = $this->makeDaemon($run); // boots config off budget_quote=1000, deploy_pct=100

        // drift #1: GUI/MCP lowers the live budget on the run row
        $run->setBudgetQuote('500');
        $run->save();
        $daemon->tick();
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('trend_config_stale')->count(),
            'the drift between the live row and the booted config is warned once');
        $ev = BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('trend_config_stale')->findOne();
        $this->assertSame('Warn', (string) $ev->getLevel());

        // same drift, another tick: must not repeat
        $daemon->tick();
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('trend_config_stale')->count(),
            'the same live/booted drift does not re-warn every tick');

        // the live row converges back to what the engine booted with — no new warning
        $run->setBudgetQuote('1000');
        $run->save();
        $daemon->tick();
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('trend_config_stale')->count(),
            'converging back to the booted values raises no new warning');

        // drift #2: a DIFFERENT live value (deploy_pct, not budget) — warns again
        $run->setDeployPct(50);
        $run->save();
        $daemon->tick();
        $this->assertSame(2, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('trend_config_stale')->count(),
            'a later, distinct drift warns again after the reset');
    }

    public function testTrendConfigDriftCanAutoReloadWhenEnabled(): void
    {
        putenv('GTBOT_TREND_AUTO_RELOAD_ON_DRIFT=1');
        try {
            $this->sim->setPrice('130');
            $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
            $run->setDeployPct(100);
            $run->save();
            $daemon = $this->makeDaemon($run);

            // Drift the live row away from the booted config; the trend engine
            // should request a reload in the same tick.
            $run->setBudgetQuote('700');
            $run->save();

            $this->assertFalse($daemon->tick(), 'drift auto-reload requests a clean restart in the same tick');
            $this->assertSame(1, BotEventQuery::create()
                ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('trend_auto_reload')->count());
            $this->assertSame(1, BotEventQuery::create()
                ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('reloading')->count());
        } finally {
            putenv('GTBOT_TREND_AUTO_RELOAD_ON_DRIFT=0');
        }
    }

    /**
     * M14 rider (final-fix review): TrendEngine::onBuyFill's no-ATR fallback
     * (stop = state.stop ?? entry) is unreachable via the normal tryEnter
     * path in prod (entrySignal already required a full candle window before
     * an entry could ever be placed) but was unlogged — so the branch could
     * fire silently if that invariant ever broke. handleStuckPartials's own
     * onBuyFill call is NOT gated on entrySignal (the row is constructed
     * directly here, exactly like testTrendStuckPartialEntryBooksTheFillAfter
     * Timeout), which is precisely the seam where a future regression could
     * reach the fallback for real — pin that the Warn fires when it does.
     * Uses a symbol MarketStore has never seen (BTCUSDT/1h carries real
     * collector data in this dev DB even inside a rolled-back transaction —
     * it was committed by an out-of-band process before this test's own
     * transaction began) so currentAtr()'s `count($candles) < 2` guard is
     * hit for the reason under test, not by accident.
     */
    public function testTrendNoAtrFallbackWarnsOnEntryFill(): void
    {
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Trend', 'profile' => 'Balanced', 'budget_quote' => '1000']);
        $run->setSymbol('ZZZNOATRUSDT');
        $run->setDeployPct(100);
        $run->setTrendStopFloorPct('0'); // pure no-ATR fallback under test — the pct floor would otherwise stand in
        $run->save();
        $daemon = $this->makeDaemonWithPartialTimeout($run, 0);

        $cid = 'gt-' . $run->getRunUid() . '-L00-B-1';
        $row = new \App\BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId($cid);
        $row->setLevelIdx(0);
        $row->setSide('Buy');
        $row->setState('BUY_OPEN');
        $row->setPrice('150');
        $row->setQty('1.0');
        $row->setSimulated((bool) $run->getSimulated());
        $row->save();
        $this->sim->open[$cid] = [
            'clientOrderId' => $cid, 'orderId' => 502, 'side' => 'BUY',
            'price' => '150', 'origQty' => '1.0', 'executedQty' => '0', 'status' => 'NEW',
        ];
        $this->sim->partialFill($cid, '0.4');

        $daemon->tick(); // detectFills marks it PartFilled
        $daemon->tick(); // handleStuckPartials books it → onBuyFill, no candles seeded → currentAtr() is null

        $row->reload();
        $this->assertSame('Filled', (string) $row->getState());
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('trend_atr_missing')->count(),
            'the no-ATR stop fallback must never fire silently');
        $ev = BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('trend_atr_missing')->findOne();
        $this->assertSame('Warn', (string) $ev->getLevel());
        // the fallback (stop = state.stop ?? entry, i.e. no give at all)
        // still ran and produced a real, non-crashing round trip — a stop
        // pinned at entry breaches on the very same tick the position opens
        $this->assertContains('cycle_closed', $this->kinds());
    }

    /**
     * PROD REGRESSION (2026-08-11): flipping a Grid run to Trend left it
     * trading the DEAD grid's exposure. Boot's applied-geometry overlay is a
     * grid device (it keeps a restart-while-holding on the ladder its working
     * orders were placed under), but it was ungated: on the first Trend boot
     * after a flip the pre-cutover grid book was still open, so the stale grid
     * stamp — deploy_pct and budget_quote included — was merged OVER the row's
     * fresh values. A run flipped with deploy 25 booted at the old stamp's
     * deploy 0 and never entered. TrendEngine's trend_config_stale warn (live
     * row vs booted config) is exactly the detector that caught it, so its
     * ABSENCE after the new boot is the pin.
     */
    public function testFlipToTrendBootsOnTheRowNotTheDeadGridsStamp(): void
    {
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Grid', 'budget_quote' => '550']);
        $daemon = $this->makeDaemon($run);
        $daemon->tick();            // grid ladder placed
        $this->fillOneBuy($daemon); // one buy fills → a sell works, a deeper buy stays open

        // the applied stamp of the OLD grid: the routine had dialed that grid
        // flat (deploy 0) before the flip, and its book is still open
        $run->reload();
        $stale = json_decode((string) $run->getAppliedGeometry(), true);
        $this->assertIsArray($stale, 'fixture: the grid stamped its applied geometry');
        $stale['deploy_pct'] = '0';
        $run->setAppliedGeometry(json_encode($stale));
        // the flip, exactly as it was written in prod: algo AND deploy in one save
        $run->setAlgo('Trend');
        $run->setDeployPct(25);
        $run->save();
        $this->assertFalse($daemon->tick(), 'algo change requests a clean restart');

        $trend = $this->makeDaemon($run); // relaunch: cutover into Trend
        $this->assertSame(25, (int) $trend->config['deploy_pct'],
            'the Trend boot sizes on the ROW (25), not on the dead grid stamp (0)');
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('algo_cutover')->count(),
            'fixture: the cutover ran');

        $trend->tick();
        $this->assertSame(0, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('trend_config_stale')->count(),
            'a freshly booted Trend run cannot already be stale against its own row');

        // and the stamp itself is gone: it describes a ladder nothing holds
        $run->reload();
        $this->assertNull($run->getAppliedGeometry(),
            'the cutover clears the grid-only stamp instead of leaving it stale forever');

        // The rows already running in prod when this shipped still carry the
        // stale stamp their cutover wrote, and their marker is already Trend —
        // so no cutover re-anchors them. Every plain restart re-read that
        // stamp: the overlay is the only thing standing between the row and
        // the engine, which is why the gate (not just the clear) is the fix.
        $run->setAppliedGeometry(json_encode($stale));
        $run->save();
        $restart = $this->makeDaemon($run);
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('algo_cutover')->count(),
            'fixture: a plain Trend restart, no second cutover');
        $this->assertSame(25, (int) $restart->config['deploy_pct'],
            'a Trend boot never overlays applied geometry — it is a grid device');
        $restart->tick();
        $this->assertSame(0, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('trend_config_stale')->count(),
            'the run trades the row it was given, on every boot');
    }

    /**
     * The other half of the same fix: a stamp left behind on a non-grid run is
     * the same bug one flip removed — the NEXT flip back to Grid would overlay
     * the dead ladder (its deploy_pct and budget with it) instead of
     * re-anchoring on the row. Pin that the returning grid anchors fresh and
     * actually arms its ladder.
     */
    public function testFlipBackToGridReAnchorsInsteadOfResurrectingTheStamp(): void
    {
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Grid', 'budget_quote' => '550']);
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $this->fillOneBuy($daemon);

        // dead grid stamped flat, then flipped to Trend
        $run->reload();
        $stale = json_decode((string) $run->getAppliedGeometry(), true);
        $stale['deploy_pct'] = '0';
        $run->setAppliedGeometry(json_encode($stale));
        $run->setAlgo('Trend');
        $run->setDeployPct(25);
        $run->save();
        $this->makeDaemon($run)->tick(); // cutover INTO Trend

        // a row that was already living under Trend when this fix shipped
        // still carries the stamp its pre-fix cutover wrote — the flip back to
        // Grid is exactly the boot that would overlay it
        $run->reload();
        $run->setAppliedGeometry(json_encode($stale));
        $run->save();

        // ...and back to Grid at full deploy
        $run->reload();
        $run->setAlgo('Grid');
        $run->setDeployPct(100);
        $run->save();
        $grid = $this->makeDaemon($run); // cutover BACK to Grid
        // asserted at BOOT, before any tick: a later refit repairing the
        // config would hide the resurrection (and refits defer while the run
        // holds), so the anchor has to be right the moment the grid comes up
        $this->assertSame(100, (int) $grid->config['deploy_pct'],
            'the returning grid builds its ladder on the row, not on the dead stamp');
        $grid->tick();
        $run->reload();
        $fresh = json_decode((string) $run->getAppliedGeometry(), true);
        $this->assertSame('100', (string) $fresh['deploy_pct'],
            'and re-stamps the geometry it actually applied');
        $this->assertSame((string) $run->getBudgetQuote(), (string) $fresh['budget_quote']);
        $this->assertNotContains('geometry_reset', $this->kinds(),
            'the carried legacy exits are expected off-ladder — no self-heal');
        $this->assertGreaterThan(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByState('BUY_OPEN')->filterByIsLegacy(false)->count(),
            'the ladder is armed again (a resurrected deploy 0 would place nothing)');
    }

    /**
     * The other side of the same gate (review): the overlay must key on the
     * algo that OWNS THE OPEN BOOK, not on the requested one. On a flip boot
     * the OUTGOING grid engine runs first — it reconciles, exits a fill missed
     * while down, and hands its working sells over as legacy — and all of that
     * has to be priced on the ladder those orders were actually placed under.
     * Gate it on run.algo instead and the outgoing grid gets the INCOMING
     * geometry: it exits held inventory at the new ladder's line (below cost)
     * and in the new ladder's per-level qty (more base than is held).
     */
    public function testFlipBootExitsTheOldBookOnTheOldLadder(): void
    {
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Grid', 'budget_quote' => '550']);
        $daemon = $this->makeDaemon($run);
        $daemon->tick(); // ladder A: buys at 100 (L0) and 125 (L1), stamped

        // daemon down; the routine re-geometries the row to a LOWER ladder B
        // and flips to Trend in the same write...
        $run->reload();
        $run->setPLow('40');
        $run->setPHigh('80');
        $run->setAlgo('Trend');
        $run->save();
        // ...and while it is down, ladder A's 125 buy fills
        $this->sim->setPrice('120');

        $this->makeDaemon($run); // boot: reconcile under Grid, then cut over

        $bought = BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterBySide('Buy')->filterByState('Filled')->findOne();
        $this->assertNotNull($bought, 'fixture: the missed fill is booked');
        $exit = BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterBySide('Sell')->findOne();
        $this->assertNotNull($exit, 'the outgoing grid engine exits the fill it missed while down');
        $this->assertSame('SELL_OPEN', (string) $exit->getState(),
            'the booked inventory must end up GUARDED — an exit priced off the incoming '
            . 'ladder lands below cost, is vetoed, and leaves the position naked');
        $this->assertSame(0, bccomp((string) $exit->getPrice(), '150', 8),
            "the exit sits on ladder A's take-profit line (125 → 150), not on ladder B's");
        $this->assertGreaterThan((float) $bought->getPrice(), (float) $exit->getPrice(),
            'an exit priced off the incoming ladder would sit BELOW cost');
        $this->assertSame(0, bccomp((string) $exit->getQty(), (string) $bought->getQty(), 8),
            'and it guards exactly the inventory that was bought — not ladder B per-level qty');
        $this->assertTrue((bool) $exit->getIsLegacy(), 'and the cutover carries that exit as legacy');

        // the incoming engine still anchors on the ROW (the cutover re-anchor)
        $run->reload();
        $this->assertNull($run->getAppliedGeometry());
    }

    /**
     * ...and because the overlay above means boot validated the OUTGOING
     * ladder, the row's own geometry has to go through the same gate when the
     * cutover re-anchors on it — otherwise a flip boot is the one path that
     * can start trading geometry nothing ever validated.
     */
    public function testCutoverRefusesToReAnchorOnGeometryThatFailsValidation(): void
    {
        $this->sim->setPrice('150');
        $run = $this->makeRun(['algo' => 'Grid', 'budget_quote' => '550']);
        $daemon = $this->makeDaemon($run);
        $daemon->tick(); // a valid ladder, stamped, with open orders

        // the row is edited to something that could never boot on its own
        // (deploy_pct 5 — 0 or 10..100 only), and the applied stamp hides it
        // from boot's own check. It still builds a perfectly good ladder, so
        // ONLY the re-anchor's validation stands between it and the exchange.
        $run->reload();
        $run->setDeployPct(5);
        $run->setAlgo('Trend');
        $run->save();

        $thrown = null;
        try {
            $this->makeDaemon($run);
        } catch (\RuntimeException $e) {
            $thrown = $e;
        }
        $this->assertNotNull($thrown, 'a cutover must not re-anchor on geometry that fails validation');
        $this->assertStringContainsString('run config invalid', $thrown->getMessage());
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('config_invalid')->count(),
            'and says so at Error level, exactly like boot does');
    }
}
