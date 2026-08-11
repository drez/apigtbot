<?php

namespace Tests\Custom\Bot;

use ApiGoat\Sessions\AuthySession;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Indicators;
use App\Domains\Bot\MarketStore;
use App\Mcp\Tools\GtbotMarketTool;
use PHPUnit\Framework\TestCase;
use Tests\Custom\Bot\Support\ExchangeSim;

class GtbotMarketToolTest extends TestCase
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
        $_SESSION[_AUTH_VAR] = new AuthySession();
        self::$booted = true;
    }

    protected function setUp(): void
    {
        \Propel::getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function decode(array $r): array
    {
        return json_decode($r['content'][0]['text'], true);
    }

    public function testReadsSummaryFromDbAndLivePrice(): void
    {
        $candles = [];
        for ($i = 0; $i < 60; $i++) {
            $p = 100 + $i;
            $candles[] = ['high' => $p + 2, 'low' => $p - 2, 'close' => $p];
        }
        // populate all three timeframes fresh so refresh-if-stale is a no-op
        foreach (['1h', '4h', '1d'] as $tf) {
            MarketStore::upsert('BTCUSDT', $tf, Indicators::summary($candles), $candles);
        }

        // fresh cache → no refresh; priceOverride stands in for the live fetch
        $out = $this->decode((new GtbotMarketTool('66123.45'))->handle(['symbol' => 'BTCUSDT'], $this->createMock(AuthySession::class)));
        $this->assertSame('66123.45', $out['price']);
        $this->assertArrayHasKey('4h', $out['timeframes']);
        $this->assertContains($out['timeframes']['4h']['trend'], ['up', 'strong_up']);
        $this->assertStringContainsString('database', $out['source']);
    }

    public function testStaleCacheIsRefreshedOnCall(): void
    {
        // empty cache + a sim gateway carrying candles → the tool self-refreshes
        $sim = new ExchangeSim();
        for ($i = 0; $i < 60; $i++) {
            $p = 100 + $i;
            $sim->klines[] = ['open' => $p, 'high' => $p + 2, 'low' => $p - 2, 'close' => $p];
        }
        $gw = new BinanceGateway('https://sim.local', '', '', $sim->transport());
        $out = $this->decode((new GtbotMarketTool('150', $gw))->handle(['symbol' => 'SIMUSDT'], $this->createMock(AuthySession::class)));
        // refreshed on call → timeframes now populated from the sim data
        $this->assertNotEmpty($out['timeframes']);
        $this->assertArrayHasKey('4h', $out['timeframes']);
    }

    public function testActiveRunMatchesRequestedSymbol(): void
    {
        // two active runs on different symbols; the NEWER one is BNB. Asking
        // for BTCUSDT must return the BTC run's grid — not the newest run
        // (prod 2026-07-23: a BTCUSDT query showed the BNB grid → in_range
        // false and a 203812% price position).
        $btc = $this->makeRun('BTCUSDT', '64000', '66000');
        $this->makeRun('BNBUSDT', '540', '580');

        $candles = [];
        for ($i = 0; $i < 60; $i++) {
            $p = 100 + $i;
            $candles[] = ['high' => $p + 2, 'low' => $p - 2, 'close' => $p];
        }
        foreach (['1h', '4h', '1d'] as $tf) {
            MarketStore::upsert('BTCUSDT', $tf, Indicators::summary($candles), $candles);
        }

        $out = $this->decode((new GtbotMarketTool('65000'))->handle(['symbol' => 'BTCUSDT'], $this->createMock(AuthySession::class)));
        $this->assertSame((int) $btc->getIdGridRun(), $out['active_run']['id']);
        $this->assertTrue($out['active_run']['in_range']);
    }

    public function testNoActiveRunBlockForSymbolWithoutRun(): void
    {
        $this->makeRun('BNBUSDT', '540', '580');
        $candles = [];
        for ($i = 0; $i < 60; $i++) {
            $p = 100 + $i;
            $candles[] = ['high' => $p + 2, 'low' => $p - 2, 'close' => $p];
        }
        foreach (['1h', '4h', '1d'] as $tf) {
            MarketStore::upsert('ETHUSDT', $tf, Indicators::summary($candles), $candles);
        }

        $out = $this->decode((new GtbotMarketTool('3000'))->handle(['symbol' => 'ETHUSDT'], $this->createMock(AuthySession::class)));
        $this->assertArrayNotHasKey('active_run', $out, 'no run trades this symbol — showing another run\'s grid would mislead');
    }

    private function makeRun(string $symbol, string $pLow, string $pHigh): \App\GridRun
    {
        $r = new \App\GridRun();
        $r->setLabel('mkt-' . $symbol . '-' . bin2hex(random_bytes(4)));
        $r->setSymbol($symbol);
        $r->setStatus('Testnet');
        $r->setPLow($pLow);
        $r->setPHigh($pHigh);
        $r->setNLevels(5);
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('1000');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->save();
        return $r;
    }

    public function testEmptyAndUnreachableReportsNoData(): void
    {
        // empty cache + sim with NO candles → refresh stores nothing → note
        $sim = new ExchangeSim();
        $gw = new BinanceGateway('https://sim.local', '', '', $sim->transport());
        $out = $this->decode((new GtbotMarketTool('66000', $gw))->handle(['symbol' => 'NOPEUSDT'], $this->createMock(AuthySession::class)));
        $this->assertSame([], $out['timeframes']);
        $this->assertStringContainsString('no market data', $out['note']);
    }
}
