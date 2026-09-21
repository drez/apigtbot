<?php

namespace Tests\Custom\Bot;

use App\BotOrder;
use App\Domains\Dashboard\DashboardData;
use App\GridRun;
use App\TradeCycle;
use PHPUnit\Framework\TestCase;

class DashboardDataTest extends TestCase
{
    private static bool $booted = false;
    private GridRun $run;

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
        $r = new GridRun();
        $r->setLabel('dash-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setPLow('60000');
        $r->setPHigh('72000');
        $r->setNLevels(12);
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('1000');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('1200');
        $r->setMaxOrderQuote('200');
        $r->setDailyLossLimitQuote('50');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(30);
        $r->setRunUid('dashtest');
        $r->setLastTickAt(date('Y-m-d H:i:s'));
        // seeded order()/cycle() rows below don't stamp simulated (default
        // false/real) — pin the run to real mode so KPIs still see them.
        $r->setSimulated(false);
        $r->save();
        $this->run = $r;
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
        // Config rows edited here (use-all-funds flag) stay in Propel's instance
        // pool with their in-memory value after the rollback — drop them
        \App\ConfigPeer::clearInstancePool();
    }

    private function order(string $side, string $state, string $price, string $qty, string $filled = '0'): void
    {
        $o = new BotOrder();
        $o->setIdGridRun((int) $this->run->getIdGridRun());
        $o->setClientOrderId('c-' . bin2hex(random_bytes(5)));
        $o->setLevelIdx(1);
        $o->setSide($side);
        $o->setState($state);
        $o->setPrice($price);
        $o->setQty($qty);
        $o->setFilledQty($filled);
        $o->save();
    }

    private function cycle(string $buy, string $sell, string $qty, string $pnl, string $fees): void
    {
        $c = new TradeCycle();
        $c->setIdGridRun((int) $this->run->getIdGridRun());
        $c->setLevelIdx(1);
        $c->setBuyPrice($buy);
        $c->setSellPrice($sell);
        $c->setQty($qty);
        $c->setRealizedPnl($pnl);
        $c->setFeesTotal($fees);
        $c->save();
    }

    public function testGlobalBandBudgetFollowsWalletWhenUseAllFundsIsOn(): void
    {
        $flag = \App\ConfigQuery::create()->findOneByConfig(\App\Domains\Bot\BudgetPool::CONFIG_USE_ALL) ?? (new \App\Config())->setConfig(\App\Domains\Bot\BudgetPool::CONFIG_USE_ALL);
        $flag->setValue('1');
        $flag->save();
        // the fixture run is pinned to REAL mode: the wallet is what the
        // daemon stamped on the run row, not sim_wallet
        // the wallet can only pull the cap DOWN: gtbot_shared_budget_quote is
        // the ceiling (operator rule 2026-09-19), so use a wallet BELOW the seed
        $this->run->setBalQuote('800');
        $this->run->setBalBase('0');
        $this->run->save();

        $band = DashboardData::globalBand();

        $this->assertTrue($band['use_all_funds']);
        $this->assertSame('800', bcadd($band['budget'], '0', 0), 'cap follows the wallet down');
        $this->assertSame(0, bccomp($band['budget_fixed'], \App\Domains\Bot\SimWallet::sharedBudget(), 12), 'the seed stays');
    }

    public function testRunHeaderReflectsFreshHeartbeat(): void
    {
        $h = (new DashboardData($this->run))->runHeader();
        $this->assertSame((string) $this->run->getLabel(), $h['label']);
        $this->assertSame('Testnet', $h['status']);
        $this->assertFalse($h['heartbeat_stale']);
    }

    private function event(string $level, string $kind, string $msg): void
    {
        $e = new \App\BotEvent();
        $e->setIdGridRun((int) $this->run->getIdGridRun());
        $e->setLevel($level);
        $e->setKind($kind);
        $e->setMessage($msg);
        $e->save();
    }

    public function testLatestEventsFilterNoiseAndShowSeconds(): void
    {
        $this->event('Info', 'boot', 'noise');
        $this->event('Info', 'refit_pending', 'noise');
        $this->event('Info', 'placed', 'Buy L03 @ 62798');
        $this->event('Info', 'buy_fill', 'L06 filled @ 66407');
        $this->event('Info', 'refit_by_claude', 'grid set to [63000, 70000]');
        $this->event('Alert', 'risk_kill', 'daily loss limit');

        $events = (new DashboardData($this->run))->latestEvents();
        $kinds = array_column($events, 'kind');
        $this->assertNotContains('boot', $kinds, 'noise filtered');
        $this->assertNotContains('refit_pending', $kinds, 'noise filtered');
        $this->assertContains('placed', $kinds, 'buy/sell shown');
        $this->assertContains('buy_fill', $kinds, 'fills shown');
        $this->assertContains('refit_by_claude', $kinds, 'refit shown');
        $this->assertContains('risk_kill', $kinds, 'risk shown');
        // second-precision timestamp so same-minute events are distinguishable
        $this->assertMatchesRegularExpression('/\d{2}:\d{2}:\d{2}$/', $events[0]['at']);
    }

    public function testKpisAggregateCyclesAndInventory(): void
    {
        // two filled buys (held), one completed cycle
        $this->order('Buy', 'Filled', '60000', '0.001', '0.001');
        $this->order('Buy', 'Filled', '61000', '0.001', '0.001');
        $this->order('Buy', 'BUY_OPEN', '59000', '0.001');
        $this->order('Sell', 'SELL_OPEN', '62000', '0.001');
        $this->cycle('60000', '61000', '0.001', '0.94', '0.06');

        $k = (new DashboardData($this->run))->kpis();
        $this->assertSame(1, $k['cycles']);
        $this->assertSame(1, $k['open_buys']);
        $this->assertSame(1, $k['open_sells']);
        $this->assertSame(0, bccomp($k['realized_pnl'], '0.94', 6));
        $this->assertSame(0, bccomp($k['fees'], '0.06', 6));
        // held inventory = 2 filled buys (0.002) minus 1 cycle qty (0.001) = 0.001
        $this->assertSame(0, bccomp($k['inventory'], '0.001', 8));
        // invested = 60000*.001 + 61000*.001 - 60000*.001 (cycle) = 61
        $this->assertSame(0, bccomp($k['invested'], '61', 6));
    }

    public function testRunHeaderExposesSellAtLoss(): void
    {
        $this->assertFalse((new DashboardData($this->run))->runHeader()['sell_at_loss'], 'default OFF');
        $this->run->setSellAtLoss(true);
        $this->run->save();
        $this->assertTrue((new DashboardData($this->run))->runHeader()['sell_at_loss']);
    }

    public function testRunHeaderExposesLastPrice(): void
    {
        $this->run->setLastPrice('66500');
        $this->run->save();
        $h = (new DashboardData($this->run))->runHeader();
        $this->assertSame(0, bccomp($h['last_price'], '66500', 8));
    }

    public function testKpisExposeWalletBalancesAndUncommittedBudget(): void
    {
        $this->run->setBalBase('0.0025');
        $this->run->setBalQuote('912.34');
        $this->run->save();
        $this->order('Buy', 'Filled', '60000', '0.001', '0.001'); // invested 60
        $this->order('Buy', 'BUY_OPEN', '59000', '0.001');        // committed 59

        $k = (new DashboardData($this->run))->kpis();
        $this->assertSame(0, bccomp($k['bal_base'], '0.0025', 8));
        $this->assertSame(0, bccomp($k['bal_quote'], '912.34', 8));
        // budget 1000 − invested 60 − open-buy commitment 59 = 881
        $this->assertSame(0, bccomp($k['quote_uncommitted'], '881', 6));
    }

    public function testWalletBalancesNullWhenNeverStamped(): void
    {
        $k = (new DashboardData($this->run))->kpis();
        $this->assertNull($k['bal_base']);
        $this->assertNull($k['bal_quote']);
    }

    public function testKpisExposePositionValues(): void
    {
        $this->run->setLastPrice('62000');
        $this->run->setBalBase('0.005');
        $this->run->setBalQuote('900');
        $this->run->save();
        $this->order('Buy', 'Filled', '60000', '0.001', '0.001');   // inventory 0.001, invested 60
        $this->order('Buy', 'BUY_OPEN', '59000', '0.001');          // committed 59
        $this->order('Sell', 'SELL_OPEN', '62000', '0.001');        // sell value 62

        $k = (new DashboardData($this->run))->kpis();
        $this->assertSame(0, bccomp($k['committed_buys'], '59', 6));
        $this->assertSame(0, bccomp($k['open_sell_value'], '62', 6));
        $this->assertSame(0, bccomp($k['inventory_value'], '62', 6));         // 0.001 × 62000
        $this->assertSame(0, bccomp($k['account_value'], '1210', 6));         // 900 + 0.005 × 62000
    }

    public function testChartMarkersSelectLifecycleKindsChronologically(): void
    {
        $this->event('Info', 'reloading', 'new code detected on disk — restarting to load it');
        $this->event('Info', 'bot_stop', 'paused via command');
        $this->event('Info', 'placed', 'not a marker');
        $this->event('Info', 'algo_update', 'algorithm updated on disk');

        $markers = (new DashboardData($this->run))->chartMarkers();
        $this->assertSame(['algo_update', 'bot_stop', 'algo_update'], array_column($markers, 'kind'),
            'legacy reloading maps to algo_update; trade kinds excluded; oldest first');
        $this->assertNotEmpty($markers[0]['at']);
    }

    // ── trading chart model (Dashboard/chart endpoint) ────────────────

    private function seedCandles(string $symbol, string $tf, int $from, int $n): void
    {
        $step = \App\Domains\Bot\CandleStore::TF_SECONDS[$tf];
        $rows = [];
        for ($i = 0; $i < $n; $i++) {
            $rows[] = ['open_time' => $from + $i * $step, 'open' => '60000', 'high' => '61000', 'low' => '59000', 'close' => '60500', 'volume' => '3'];
        }
        \App\Domains\Bot\CandleStore::upsert($symbol, $tf, $rows);
    }

    public function testChartModelCarriesCandlesFillsLadderAndGrid(): void
    {
        $symbol = 'CHART' . strtoupper(bin2hex(random_bytes(3)));
        $this->run->setSymbol($symbol);
        $this->run->setLastPrice('60250');
        $this->run->save();
        $from = intdiv(time(), 3600) * 3600 - 4 * 3600;
        $this->seedCandles($symbol, '1h', $from, 5);
        $this->order('Buy', 'Filled', '60000', '0.002', '0.002');
        $this->order('Sell', 'SELL_OPEN', '61000', '0.002');
        $this->order('Buy', 'Canceled', '58000', '0.001');
        $this->order('Buy', 'Vetoed', '57000', '0.001');

        $m = (new DashboardData($this->run))->chartModel('1h');

        $this->assertSame('1h', $m['tf']);
        $this->assertSame(3600, $m['tf_seconds']);
        $this->assertSame($symbol, $m['symbol']);
        $this->assertFalse($m['stale'], 'newest bar is the current hour');
        $this->assertCount(5, $m['candles']);
        $this->assertSame($from, $m['candles'][0]['time'], 'oldest first');
        $this->assertSame(['time', 'open', 'high', 'low', 'close', 'volume'], array_keys($m['candles'][0]));

        $this->assertCount(1, $m['fills'], 'only filled orders are fills — open, canceled, vetoed excluded');
        $this->assertSame('Buy', $m['fills'][0]['side']);
        $this->assertSame(60000.0, $m['fills'][0]['price']);
        $this->assertSame(0.002, $m['fills'][0]['qty']);
        $this->assertEqualsWithDelta(time(), $m['fills'][0]['time'], 120, 'fill time is epoch seconds of date_modification');

        $this->assertSame([['price' => 61000.0, 'side' => 'Sell', 'level' => 1]], $m['orders_open']);
        $this->assertSame(60000.0, $m['grid']['p_low']);
        $this->assertSame(72000.0, $m['grid']['p_high']);
        $this->assertCount(13, $m['grid']['levels'], 'n_levels + 1 lines');
        $this->assertSame(60250.0, $m['last_price']);
        $this->assertNull($m['trend'], 'grid run carries no trend block');
        $this->assertSame([], $m['events']);
    }

    public function testChartModelStaleWhenTheSeriesIsOldOrEmpty(): void
    {
        $symbol = 'CHART' . strtoupper(bin2hex(random_bytes(3)));
        $this->run->setSymbol($symbol);
        $this->run->save();
        $this->assertTrue((new DashboardData($this->run))->chartModel('5m')['stale'], 'no bars at all');
        $this->seedCandles($symbol, '5m', time() - 7200, 3);
        $this->assertTrue((new DashboardData($this->run))->chartModel('5m')['stale'], 'newest bar is ~2h old on a 5m series');
    }

    public function testChartModelTrendBlockReadsEngineState(): void
    {
        $this->run->setAlgo('Trend');
        $this->run->setEngineState(json_encode(['algo' => 'Trend', 'qty' => '0.01', 'entry' => '61000', 'hwm' => '63000', 'stop' => '60100']));
        $this->run->save();
        $m = (new DashboardData($this->run))->chartModel('15m');
        $this->assertSame(['entry' => 61000.0, 'stop' => 60100.0, 'hwm' => 63000.0, 'qty' => 0.01], $m['trend']);

        // flat position: keys present, values null
        $this->run->setEngineState(json_encode(['algo' => 'Trend', 'qty' => '0', 'entry' => null, 'hwm' => null, 'stop' => null]));
        $this->run->save();
        $m = (new DashboardData($this->run))->chartModel('15m');
        $this->assertNull($m['trend']['stop']);
        $this->assertSame(0.0, $m['trend']['qty']);
    }

    public function testChartModelFillAndEventTimesIgnoreTheSessionTimezone(): void
    {
        // Stamps are written by the daemon in the process default timezone
        // (php.ini, UTC on prod); the dashboard request runs in the user's
        // session timezone (legacy.php). Parsing a UTC stamp in -0400 would
        // land every fill four hours late on the chart.
        $this->order('Buy', 'Filled', '60000', '0.002', '0.002');
        $this->event('Info', 'trend_activate', 'trend arm on');
        $tz = date_default_timezone_get();
        date_default_timezone_set('America/New_York');
        try {
            $m = (new DashboardData($this->run))->chartModel('1m');
        } finally {
            date_default_timezone_set($tz);
        }
        $this->assertEqualsWithDelta(time(), $m['fills'][0]['time'], 120, 'fill epoch must not shift with the session timezone');
        $this->assertEqualsWithDelta(time(), $m['events'][0]['time'], 120, 'event epoch must not shift with the session timezone');
    }

    public function testChartModelEventsCarryLabelsAndEpochTimes(): void
    {
        $this->event('Info', 'reloading', 'restart on deploy');
        $this->event('Info', 'placed', 'not a marker');
        $this->event('Info', 'trend_activate', 'trend arm on');
        $m = (new DashboardData($this->run))->chartModel('1m');
        $this->assertSame(['algo_update', 'trend_activate'], array_column($m['events'], 'kind'));
        $this->assertSame(['algo update', 'trend arm on'], array_column($m['events'], 'label'));
        $this->assertEqualsWithDelta(time(), $m['events'][0]['time'], 120);
    }

    public function testChartModelRejectsUnknownTimeframe(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new DashboardData($this->run))->chartModel('1d');
    }

    public function testViewModelNoLongerCarriesTheSvgChartFeeds(): void
    {
        $vm = (new DashboardData($this->run))->viewModel('https://x.local/');
        foreach (['points', 'history', 'markers', 'grid_levels'] as $k) {
            $this->assertArrayNotHasKey($k, $vm, "$k moved to chartModel()");
        }
    }

    public function testDailyPnlZeroFillsWindow(): void
    {
        $this->cycle('60000', '61000', '0.001', '1.0', '0');
        $daily = (new DashboardData($this->run))->dailyPnl(7);
        $this->assertCount(7, $daily);
        $this->assertSame(date('Y-m-d'), $daily[6]['day']); // last bucket is today
        $this->assertSame(0, bccomp($daily[6]['pnl'], '1.0', 6));
    }

    public function testViewModelIsRenderable(): void
    {
        $this->cycle('60000', '61000', '0.001', '0.94', '0.06');
        $vm = (new DashboardData($this->run))->viewModel('https://x.test/');
        $html = (new \App\Domains\Dashboard\DashboardRenderer())->render($vm);
        $this->assertStringContainsString('Realized P/L', $html);
        $this->assertStringContainsString((string) $this->run->getLabel(), $html);
    }

    private function extraRun(string $status, string $symbol = 'BNBUSDT'): GridRun
    {
        $r = new GridRun();
        $r->setLabel('dash2-' . bin2hex(random_bytes(4)));
        $r->setSymbol($symbol);
        $r->setStatus($status);
        $r->setPLow('500');
        $r->setPHigh('600');
        $r->setNLevels(5);
        $r->setBudgetQuote('300');
        $r->setMaxPositionQuote('360');
        $r->setMaxOrderQuote('60');
        $r->setDailyLossLimitQuote('25');
        $r->save();
        return $r;
    }

    public function testActiveRunsListsActiveInIdOrderExcludingInactive(): void
    {
        $live = $this->extraRun('Live');
        $this->extraRun('Draft');
        $this->extraRun('Done');
        $held = $this->extraRun('Halted');

        $ids = array_map(
            static fn (GridRun $r): int => (int) $r->getIdGridRun(),
            DashboardData::activeRuns()
        );

        $a = (int) $this->run->getIdGridRun(); // Testnet, created first
        $b = (int) $live->getIdGridRun();
        $this->assertContains($a, $ids);
        $this->assertContains($b, $ids);
        // Halted keeps its tab (Hold funds parks a run there; its Release
        // button needs a home) — Draft and Done stay off the dashboard.
        $this->assertContains((int) $held->getIdGridRun(), $ids);
        $this->assertLessThan(array_search($b, $ids, true), array_search($a, $ids, true));
        foreach (['Draft', 'Done'] as $status) {
            foreach (DashboardData::activeRuns() as $r) {
                $this->assertNotSame($status, (string) $r->getStatus());
            }
        }
    }

    public function testSelectRunHonorsRequestedIdAndFallsBackToFirst(): void
    {
        $live = $this->extraRun('Live');
        $active = DashboardData::activeRuns();

        $picked = DashboardData::selectRun($active, (int) $live->getIdGridRun());
        $this->assertSame((int) $live->getIdGridRun(), (int) $picked->getIdGridRun());

        // invalid / absent id → first active
        $first = (int) $active[0]->getIdGridRun();
        $this->assertSame($first, (int) DashboardData::selectRun($active, 999999)->getIdGridRun());
        $this->assertSame($first, (int) DashboardData::selectRun($active, null)->getIdGridRun());
    }

    public function testSelectRunWithNoActiveRunsReturnsNull(): void
    {
        $this->assertNull(DashboardData::selectRun([], 4));
    }

    public function testTabsViewModelCarriesHealthAndSelection(): void
    {
        $live = $this->extraRun('Live');
        $live->setKillSwitch(true);
        $live->save();

        $tabs = DashboardData::tabs(DashboardData::activeRuns(), (int) $this->run->getIdGridRun(), 'https://x.test');
        $byId = [];
        foreach ($tabs as $t) {
            $byId[$t['id']] = $t;
        }
        $mine = $byId[(int) $this->run->getIdGridRun()];
        $other = $byId[(int) $live->getIdGridRun()];

        $this->assertTrue($mine['selected']);
        $this->assertFalse($other['selected']);
        $this->assertSame('BTCUSDT', $mine['symbol']);
        $this->assertFalse($mine['kill_switch']);
        $this->assertFalse($mine['heartbeat_stale']); // ticked in setUp
        $this->assertTrue($other['kill_switch']);
        $this->assertTrue($other['heartbeat_stale']); // never ticked
        $this->assertStringContainsString('?run=' . $other['id'], $other['href']);
    }

    public function testHaltedRunIsHeldNotStale(): void
    {
        // A Halted run has no daemon BY DESIGN (Hold funds stops it, the
        // watchdog skips it) — its frozen tick is not a stale heartbeat.
        $held = $this->extraRun('Halted'); // never ticked
        $tabs = DashboardData::tabs(DashboardData::activeRuns(), (int) $this->run->getIdGridRun(), 'https://x.test');
        $tab = array_column($tabs, null, 'id')[(int) $held->getIdGridRun()];
        $this->assertTrue($tab['held']);
        $this->assertFalse($tab['heartbeat_stale']);

        $h = (new DashboardData($held))->runHeader();
        $this->assertTrue($h['held']);
        $this->assertFalse($h['heartbeat_stale']);

        // a ticking run is not held
        $mine = array_column($tabs, null, 'id')[(int) $this->run->getIdGridRun()];
        $this->assertFalse($mine['held']);
        $this->assertFalse((new DashboardData($this->run))->runHeader()['held']);
    }

    public function testSharedWalletRealModeUsesFreshestStampedRunAndPerRunBase(): void
    {
        // setUp's run is BTCUSDT, simulated=false (real).
        $this->run->setBalBase('0.01');
        $this->run->setBalQuote('500');
        $this->run->setLastTickAt('2026-01-01 00:00:00');
        $this->run->save();

        $eth = $this->extraRun('Live', 'ETHUSDT');
        $eth->setSimulated(false);
        $eth->setBalBase('2');
        $eth->setBalQuote('9000'); // freshest last_tick_at — wins as the USDT figure
        $eth->setLastTickAt('2026-06-01 00:00:00');
        $eth->save();

        $wallet = (new DashboardData($this->run))->sharedWallet();
        $this->assertSame('real', $wallet['mode']);
        $this->assertSame(0, bccomp('9000', $wallet['total_quote'], 6));

        $byAsset = [];
        foreach ($wallet['assets'] as $a) {
            $byAsset[$a['asset']] = $a;
        }
        $this->assertSame(0, bccomp('0.01', $byAsset['BTC']['qty'], 8));
        $this->assertSame(0, bccomp('2', $byAsset['ETH']['qty'], 8));
        $this->assertSame(0, bccomp('9000', $byAsset['USDT']['qty'], 8));
    }

    public function testSharedWalletSimulatedModeValuesNonUsdtAtLastPrice(): void
    {
        $this->run->setSimulated(true);
        $this->run->setLastPrice('65000');
        $this->run->save();
        \Propel::getConnection()->exec(
            "INSERT INTO sim_wallet (asset, qty, date_creation, date_modification) VALUES "
            . "('USDT', 400, NOW(), NOW()), ('BTC', 0.5, NOW(), NOW())"
        );

        $wallet = (new DashboardData($this->run))->sharedWallet();
        $this->assertSame('simulated', $wallet['mode']);
        $byAsset = [];
        foreach ($wallet['assets'] as $a) {
            $byAsset[$a['asset']] = $a;
        }
        $this->assertSame(0, bccomp('32500', $byAsset['BTC']['value'], 6)); // 0.5 * 65000
        $this->assertSame(0, bccomp('32900', $wallet['total_quote'], 6));   // 400 + 32500
    }

    public function testSharedWalletSimulatedAssetWithoutPriceHasNullValueAndIsSkippedFromTotal(): void
    {
        $this->run->setSimulated(true);
        // no last_price stamped on the run → BTC can't be valued
        $this->run->save();
        \Propel::getConnection()->exec(
            "INSERT INTO sim_wallet (asset, qty, date_creation, date_modification) VALUES "
            . "('USDT', 400, NOW(), NOW()), ('BTC', 0.5, NOW(), NOW())"
        );

        $wallet = (new DashboardData($this->run))->sharedWallet();
        $byAsset = [];
        foreach ($wallet['assets'] as $a) {
            $byAsset[$a['asset']] = $a;
        }
        $this->assertNull($byAsset['BTC']['value']);
        $this->assertSame(0, bccomp('400', $wallet['total_quote'], 6)); // only the priced USDT row counted
    }

    public function testSharedWalletModeNoneWhenNoNonDoneRuns(): void
    {
        $this->run->setStatus('Done');
        $this->run->save();
        $wallet = (new DashboardData($this->run))->sharedWallet();
        $this->assertSame('none', $wallet['mode']);
    }

    /** @return array{realized:string, today:string, slices:string} ambient sums excluding $excludeId */
    private function ambientBandSums(int $excludeId): array
    {
        $realized = '0';
        $today = '0';
        $todayStart = date('Y-m-d 00:00:00');
        foreach (\App\GridRunQuery::create()
            ->filterByStatus('Done', \Criteria::NOT_EQUAL)
            ->filterByIdGridRun($excludeId, \Criteria::NOT_EQUAL)
            ->find() as $r) {
            $simulated = (bool) $r->getSimulated();
            $id = (int) $r->getIdGridRun();
            foreach (\App\TradeCycleQuery::create()->filterByIdGridRun($id)->filterBySimulated($simulated)->find() as $c) {
                $realized = bcadd($realized, (string) $c->getRealizedPnl(), 12);
            }
            foreach (\App\TradeCycleQuery::create()->filterByIdGridRun($id)->filterBySimulated($simulated)
                ->filterByDateCreation(['min' => $todayStart])->find() as $c) {
                $today = bcadd($today, (string) $c->getRealizedPnl(), 12);
            }
        }
        $slices = '0';
        foreach (\App\GridRunQuery::create()
            ->filterByStatus(['DryRun', 'Testnet', 'Live'], \Criteria::IN)
            ->filterByIdGridRun($excludeId, \Criteria::NOT_EQUAL)
            ->find() as $r) {
            $slices = bcadd($slices, (string) $r->getBudgetQuote(), 12);
        }
        return ['realized' => $realized, 'today' => $today, 'slices' => $slices];
    }

    public function testGlobalBandAggregatesRealizedWalletAccountValueAndTotalPl(): void
    {
        // Ambient sums measured BEFORE this test seeds its own fixtures — other
        // active runs/cycles may already exist in the dev DB (see
        // BudgetInvariantTest/ModeSwitchTest's ambient-sizing pattern); assert
        // deltas for global sums, not absolute values.
        $ambient = $this->ambientBandSums((int) $this->run->getIdGridRun());

        // this->run: Testnet/BTCUSDT from setUp — flip simulated, stamp a price,
        // give it a realized cycle (pnl 10). TradeCycle.simulated defaults to 0
        // (unlike GridRun.simulated, which defaults 1) — stamp it explicitly so
        // it matches the now-simulated run (mode-scoped filterBySimulated).
        $this->run->setSimulated(true);
        $this->run->setLastPrice('65000');
        $this->run->save();
        $c1 = new TradeCycle();
        $c1->setIdGridRun((int) $this->run->getIdGridRun());
        $c1->setLevelIdx(1);
        $c1->setBuyPrice('60000');
        $c1->setSellPrice('61000');
        $c1->setQty('0.001');
        $c1->setRealizedPnl('10');
        $c1->setFeesTotal('0');
        $c1->setSimulated(true);
        $c1->save();

        // second simulated run, same symbol, its own cycle (pnl 20)
        $other = $this->extraRun('Testnet', 'BTCUSDT');
        $other->setSimulated(true);
        $other->save();
        $c2 = new TradeCycle();
        $c2->setIdGridRun((int) $other->getIdGridRun());
        $c2->setLevelIdx(1);
        $c2->setBuyPrice('60000');
        $c2->setSellPrice('61000');
        $c2->setQty('0.001');
        $c2->setRealizedPnl('20');
        $c2->setFeesTotal('0');
        $c2->setSimulated(true);
        $c2->save();

        // sim_wallet is a singleton-per-asset table — clear it (transaction is
        // rolled back in tearDown) so wallet_usdt/account_value are exact.
        \Propel::getConnection()->exec('DELETE FROM sim_wallet');
        \Propel::getConnection()->exec(
            "INSERT INTO sim_wallet (asset, qty, date_creation, date_modification) VALUES "
            . "('USDT', 900, NOW(), NOW()), ('BTC', 0.001, NOW(), NOW())"
        );

        $band = DashboardData::globalBand();

        $this->assertSame(0, bccomp($band['realized_total'], bcadd($ambient['realized'], '30', 12), 12));
        $this->assertSame(0, bccomp($band['realized_today'], bcadd($ambient['today'], '30', 12), 12));
        $this->assertSame(0, bccomp($band['slices_sum'], bcadd($ambient['slices'], '1300', 12), 12), 'this run 1000 + other 300');
        $this->assertSame(0, bccomp($band['wallet_usdt'], '900', 8));
        $this->assertSame(0, bccomp($band['account_value'], '965', 6)); // 900 + 0.001*65000
        $expectedTotalPl = bcsub('965', \App\Domains\Bot\SimWallet::sharedBudget(), 12);
        $this->assertSame(0, bccomp($band['total_pl'], $expectedTotalPl, 6));
        $this->assertFalse($band['value_partial']);

        $byAsset = [];
        foreach ($band['assets'] as $a) {
            $byAsset[$a['asset']] = $a;
        }
        $this->assertSame(0, bccomp($byAsset['BTC']['qty'], '0.001', 8));
        $this->assertSame(0, bccomp($byAsset['BTC']['value'], '65', 6));
        $this->assertSame(0, bccomp($byAsset['USDT']['qty'], '900', 8));
    }

    public function testGlobalBandAccountValuePartialWhenAssetUnpriced(): void
    {
        // Self-certifying: skip if an ambient non-Done ETHUSDT run already
        // stamps a last_price — this fixture needs ETH to be genuinely unpriced.
        foreach (\App\GridRunQuery::create()
            ->filterByStatus('Done', \Criteria::NOT_EQUAL)
            ->filterByIdGridRun((int) $this->run->getIdGridRun(), \Criteria::NOT_EQUAL)
            ->find() as $r) {
            if (str_starts_with((string) $r->getSymbol(), 'ETH') && $r->getLastPrice() !== null) {
                $this->markTestSkipped('ambient ETH-priced run exists — cannot express an unpriced-ETH fixture');
            }
        }

        \Propel::getConnection()->exec('DELETE FROM sim_wallet');
        \Propel::getConnection()->exec(
            "INSERT INTO sim_wallet (asset, qty, date_creation, date_modification) VALUES "
            . "('USDT', 500, NOW(), NOW()), ('ETH', 1, NOW(), NOW())"
        );
        $this->run->setSimulated(true);
        $this->run->save(); // BTCUSDT — no ETH price anywhere

        $band = DashboardData::globalBand();
        $this->assertTrue($band['value_partial']);
        $this->assertSame(0, bccomp($band['account_value'], '500', 6));
    }

    public function testGlobalBandModeReflectsModeSwitch(): void
    {
        \App\Domains\Dashboard\ModeSwitch::flipAll(true);
        $band = DashboardData::globalBand();
        $this->assertSame('simulated', $band['mode']);
    }

    /** @return array{invested:string, open_sell:string, open_buy:string} ambient sums excluding $excludeId */
    private function ambientCommittedSums(int $excludeId): array
    {
        $invested = '0';
        $openSell = '0';
        $openBuy = '0';
        foreach (\App\GridRunQuery::create()
            ->filterByStatus('Done', \Criteria::NOT_EQUAL)
            ->filterByIdGridRun($excludeId, \Criteria::NOT_EQUAL)
            ->find() as $r) {
            $id = (int) $r->getIdGridRun();
            $simulated = (bool) $r->getSimulated();
            $store = new \App\Domains\Bot\OrderStore($id, (string) $r->getRunUid(), $r->getLedgerResetAt('Y-m-d H:i:s'), $simulated);
            $invested = bcadd($invested, $store->investedQuote(), 12);

            foreach (\App\BotOrderQuery::create()->filterByIdGridRun($id)->filterBySimulated($simulated)
                ->filterBySide('Sell')->filterByState(['SELL_OPEN', 'PartFilled'], \Criteria::IN)->find() as $o) {
                $remaining = bcsub((string) $o->getQty(), (string) ($o->getFilledQty() ?? '0'), 12);
                $openSell = bcadd($openSell, bcmul((string) $o->getPrice(), $remaining, 12), 12);
            }
            foreach (\App\BotOrderQuery::create()->filterByIdGridRun($id)->filterBySimulated($simulated)
                ->filterBySide('Buy')->filterByState(['BUY_OPEN', 'PartFilled'], \Criteria::IN)->find() as $o) {
                $remaining = bcsub((string) $o->getQty(), (string) ($o->getFilledQty() ?? '0'), 12);
                $openBuy = bcadd($openBuy, bcmul((string) $o->getPrice(), $remaining, 12), 12);
            }
        }
        return ['invested' => $invested, 'open_sell' => $openSell, 'open_buy' => $openBuy];
    }

    public function testGlobalBandCommittedCapitalRowHoldingsOpenOrdersAndFreeBudget(): void
    {
        // Ambient sums measured BEFORE seeding — other active runs may already
        // carry orders in the dev DB (same pattern as ambientBandSums above).
        $ambient = $this->ambientCommittedSums((int) $this->run->getIdGridRun());

        $this->run->setSimulated(true);
        $this->run->setLastPrice('65000');
        $this->run->save();

        // open buy: 95 x 1, no fill
        $buyOpen = new BotOrder();
        $buyOpen->setIdGridRun((int) $this->run->getIdGridRun());
        $buyOpen->setClientOrderId('c-' . bin2hex(random_bytes(5)));
        $buyOpen->setLevelIdx(1);
        $buyOpen->setSide('Buy');
        $buyOpen->setState('BUY_OPEN');
        $buyOpen->setPrice('95');
        $buyOpen->setQty('1');
        $buyOpen->setFilledQty('0');
        $buyOpen->setSimulated(true);
        $buyOpen->save();

        // open sell: 105 x 0.5, no fill
        $sellOpen = new BotOrder();
        $sellOpen->setIdGridRun((int) $this->run->getIdGridRun());
        $sellOpen->setClientOrderId('c-' . bin2hex(random_bytes(5)));
        $sellOpen->setLevelIdx(1);
        $sellOpen->setSide('Sell');
        $sellOpen->setState('SELL_OPEN');
        $sellOpen->setPrice('105');
        $sellOpen->setQty('0.5');
        $sellOpen->setFilledQty('0');
        $sellOpen->setSimulated(true);
        $sellOpen->save();

        // filled buy: cost basis 50 (price 50 x qty 1)
        $filledBuy = new BotOrder();
        $filledBuy->setIdGridRun((int) $this->run->getIdGridRun());
        $filledBuy->setClientOrderId('c-' . bin2hex(random_bytes(5)));
        $filledBuy->setLevelIdx(1);
        $filledBuy->setSide('Buy');
        $filledBuy->setState('Filled');
        $filledBuy->setPrice('50');
        $filledBuy->setQty('1');
        $filledBuy->setFilledQty('1');
        $filledBuy->setSimulated(true);
        $filledBuy->save();

        \Propel::getConnection()->exec('DELETE FROM sim_wallet');
        \Propel::getConnection()->exec(
            "INSERT INTO sim_wallet (asset, qty, date_creation, date_modification) VALUES "
            . "('USDT', 900, NOW(), NOW()), ('BTC', 0.001, NOW(), NOW())"
        );

        $band = DashboardData::globalBand();

        // holdings_value: 0.001 BTC * 65000 = 65 (USDT itself excluded)
        $this->assertSame(0, bccomp($band['holdings_value'], '65', 6));

        // open_sell_value = ambient + 105 * 0.5
        $this->assertSame(0, bccomp($band['open_sell_value'], bcadd($ambient['open_sell'], '52.5', 12), 12));

        // open_buy_value = ambient + 95 * 1
        $this->assertSame(0, bccomp($band['open_buy_value'], bcadd($ambient['open_buy'], '95', 12), 12));

        // free_budget = sharedBudget - (ambient invested + this run's 50 cost basis) - open_buy_value
        $invested = bcadd($ambient['invested'], '50', 12);
        $expectedFree = bcsub(bcsub(\App\Domains\Bot\SimWallet::sharedBudget(), $invested, 12), $band['open_buy_value'], 12);
        $this->assertSame(0, bccomp($band['free_budget'], $expectedFree, 12));
    }

    public function testGlobalBandHoldingsValueNullWhenNoNonUsdtAssetPriced(): void
    {
        foreach (\App\GridRunQuery::create()
            ->filterByStatus('Done', \Criteria::NOT_EQUAL)
            ->filterByIdGridRun((int) $this->run->getIdGridRun(), \Criteria::NOT_EQUAL)
            ->find() as $r) {
            if (str_starts_with((string) $r->getSymbol(), 'ETH') && $r->getLastPrice() !== null) {
                $this->markTestSkipped('ambient ETH-priced run exists — cannot express an unpriced-ETH fixture');
            }
        }

        \Propel::getConnection()->exec('DELETE FROM sim_wallet');
        \Propel::getConnection()->exec(
            "INSERT INTO sim_wallet (asset, qty, date_creation, date_modification) VALUES "
            . "('USDT', 500, NOW(), NOW()), ('ETH', 1, NOW(), NOW())"
        );
        $this->run->setSimulated(true);
        $this->run->save(); // BTCUSDT — no ETH price anywhere

        $band = DashboardData::globalBand();
        $this->assertNull($band['holdings_value']);
    }

    public function testGlobalBandFreeBudgetGoesNegativeWhenOverCommitted(): void
    {
        $this->run->setSimulated(true);
        $this->run->save();

        // a single open buy larger than the shared budget
        $bigBuy = new BotOrder();
        $bigBuy->setIdGridRun((int) $this->run->getIdGridRun());
        $bigBuy->setClientOrderId('c-' . bin2hex(random_bytes(5)));
        $bigBuy->setLevelIdx(1);
        $bigBuy->setSide('Buy');
        $bigBuy->setState('BUY_OPEN');
        $bigBuy->setPrice('1000000');
        $bigBuy->setQty('1');
        $bigBuy->setFilledQty('0');
        $bigBuy->setSimulated(true);
        $bigBuy->save();

        $band = DashboardData::globalBand();
        $this->assertLessThan(0, (float) $band['free_budget']);
    }
}
