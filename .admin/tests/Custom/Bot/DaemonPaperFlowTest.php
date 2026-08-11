<?php

namespace Tests\Custom\Bot;

use App\BotOrderQuery;
use App\Domains\Bot\Daemon;
use App\Domains\Bot\EventLog;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Gateway\PaperGateway;
use App\Domains\Bot\SimWallet;
use App\GridRun;
use App\TradeCycleQuery;
use PHPUnit\Framework\TestCase;

/**
 * Paper-mode acceptance: Daemon driven through a PaperGateway (mainnet public
 * data faked, fills simulated locally) against the REAL project DB (inside a
 * rolled-back transaction) — mirrors DaemonLiveFlowTest's boot/tick pattern.
 */
class DaemonPaperFlowTest extends TestCase
{
    private static bool $booted = false;
    private GridRun $run;

    public static function setUpBeforeClass(): void
    {
        if (self::$booted) {
            return;
        }
        $admin = dirname(__DIR__, 3);
        if (!is_file($admin . '/config/Built/config.php')) {
            self::markTestSkipped('project not built');
        }
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
        $r->setLabel('paperflowtest-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Live');
        $r->setSimulated(true);
        $r->setPLow('100');
        $r->setPHigh('140');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('1000');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('1000');
        $r->setMaxOrderQuote('500');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid(substr(bin2hex(random_bytes(6)), 0, 10));
        $r->save();
        $this->run = $r;
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private string $tick = '120';

    private function makeDaemon(): Daemon
    {
        $transport = function (string $method, string $url, array $headers, ?string $body): array {
            if (str_contains($url, '/api/v3/ticker/price')) {
                return ['status' => 200, 'headers' => [], 'body' => json_encode(['price' => $this->tick])];
            }
            if (str_contains($url, '/api/v3/exchangeInfo')) {
                return ['status' => 200, 'headers' => [], 'body' => json_encode(['symbols' => [[
                    'symbol' => 'BTCUSDT',
                    'filters' => [
                        ['filterType' => 'PRICE_FILTER', 'tickSize' => '0.01'],
                        ['filterType' => 'LOT_SIZE', 'stepSize' => '0.00001', 'minQty' => '0.00001'],
                        ['filterType' => 'NOTIONAL', 'minNotional' => '5'],
                    ],
                ]]])];
            }
            throw new \RuntimeException("unexpected HTTP call in paper mode: $url");
        };
        $public = new BinanceGateway('https://api.binance.com', '', '', $transport);
        $gw = new PaperGateway($public, $this->run, (string) $this->run->getFeePct());
        return new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
    }

    public function testPaperCycleLandsMarkedSimulated(): void
    {
        $daemon = $this->makeDaemon();
        $daemon->boot();
        $daemon->tick();                     // places buy ladder below 120

        $this->assertGreaterThan(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByState('BUY_OPEN')->filterBySimulated(true)->count());

        $this->tick = '105'; $daemon->tick(); // fills lower buys, posts sells
        $this->tick = '139'; $daemon->tick(); // fills sells → cycles close
        $daemon->tick();                      // settle

        $cycles = TradeCycleQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())->find();
        $this->assertGreaterThan(0, count($cycles));
        foreach ($cycles as $c) {
            $this->assertTrue((bool) $c->getSimulated());
        }
        $walletBal = SimWallet::balances();
        $this->assertArrayHasKey('USDT', $walletBal); // wallet persisted in the shared table
    }

    public function testBootCancelsOtherModeOpens(): void
    {
        $real = new \App\BotOrder();
        $real->setIdGridRun((int) $this->run->getIdGridRun());
        $real->setClientOrderId('cid-real-zombie');
        $real->setLevelIdx(0);
        $real->setSide('Buy');
        $real->setState('BUY_OPEN');
        $real->setPrice('101');
        $real->setQty('1');
        $real->setSimulated(false);
        $real->save();

        $this->makeDaemon()->boot();

        $real->reload();
        $this->assertSame('Canceled', (string) $real->getState());

        // The daemon is paper (run.simulated = true, see setUp) and the
        // stranded row was REAL: this daemon has no signed gateway and
        // cannot cancel it on the exchange — the boot event must say so
        // loudly (Alert, so it fans out to Telegram) rather than the
        // routine "closed out" Warn, which would misleadingly imply the
        // exchange order is actually gone.
        $event = \App\BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('mode_switch')
            ->orderByIdBotEvent(\Criteria::DESC)
            ->findOne();
        $this->assertNotNull($event, 'mode_switch bot_event row expected');
        $this->assertSame('Alert', (string) $event->getLevel());
        $this->assertStringContainsString('LEDGER ONLY', (string) $event->getMessage());
        $this->assertStringContainsString('cannot cancel exchange orders', (string) $event->getMessage());
    }
}
