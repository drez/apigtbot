<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Gateway\PaperGateway;
use App\Domains\Bot\SimWallet;
use App\GridRun;
use PHPUnit\Framework\TestCase;

/**
 * Two grid runs on different symbols sharing the ONE sim_wallet pool: a fill
 * on either run must move the same USDT bucket, and an overcommitted fill
 * must fire the negative-pool alert sink rather than silently going negative.
 */
class SharedWalletFlowTest extends TestCase
{
    private static bool $booted = false;
    private GridRun $runBtc;
    private GridRun $runEth;
    /** @var string next price the fake mainnet ticker returns for BTCUSDT */
    private string $tickBtc = '100';
    /** @var string next price the fake mainnet ticker returns for ETHUSDT */
    private string $tickEth = '2000';

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
        $this->runBtc = $this->makeRun('BTCUSDT');
        $this->runEth = $this->makeRun('ETHUSDT');
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function makeRun(string $symbol): GridRun
    {
        $r = new GridRun();
        $r->setLabel('sharedwallettest-' . bin2hex(random_bytes(4)));
        $r->setSymbol($symbol);
        $r->setStatus('Live');
        $r->setSimulated(true);
        $r->setPLow('90');
        $r->setPHigh('3000');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('1000'); // irrelevant — the shared wallet ignores per-run budget
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('1000');
        $r->setMaxOrderQuote('500');
        $r->setDailyLossLimitQuote('100');
        $r->save();
        return $r;
    }

    /** Same fake-transport keyless BinanceGateway as PaperGatewayTest::gw(), generalized to two symbols. */
    private function gatewayFor(GridRun $run, string $tick, ?callable $onAlert = null): PaperGateway
    {
        $symbol = (string) $run->getSymbol();
        if ($symbol === 'BTCUSDT') {
            $this->tickBtc = $tick;
        } else {
            $this->tickEth = $tick;
        }
        $transport = function (string $method, string $url, array $headers, ?string $body) use ($symbol): array {
            if (str_contains($url, '/api/v3/ticker/price')) {
                $price = $symbol === 'BTCUSDT' ? $this->tickBtc : $this->tickEth;
                return ['status' => 200, 'headers' => [], 'body' => json_encode(['price' => $price])];
            }
            if (str_contains($url, '/api/v3/exchangeInfo')) {
                return ['status' => 200, 'headers' => [], 'body' => json_encode(['symbols' => [['symbol' => $symbol, 'filters' => []]]])];
            }
            throw new \RuntimeException("unexpected paper-mode HTTP call: $url");
        };
        $public = new BinanceGateway('https://api.binance.com', '', '', $transport);
        return new PaperGateway($public, $run, '0.001', $onAlert);
    }

    public function testTwoRunsShareOneWallet(): void
    {
        $gwBtc = $this->gatewayFor($this->runBtc, '100');   // fake ticker returns 100
        $gwEth = $this->gatewayFor($this->runEth, '2000');
        $gwBtc->placeLimitOrder('BTCUSDT', 'Buy', '95', '1', 'sw-btc-1');
        $gwEth->placeLimitOrder('ETHUSDT', 'Buy', '1900', '0.1', 'sw-eth-1');
        $this->tickBtc = '94';  $gwBtc->tickerPrice('BTCUSDT');   // fill: -95.095
        $this->tickEth = '1890'; $gwEth->tickerPrice('ETHUSDT');  // fill: -190.19
        $bal = SimWallet::balances();
        $expected = bcsub(bcsub(SimWallet::sharedBudget(), '95.095', 12), '190.19', 12);
        $this->assertSame(0, bccomp($expected, $bal['USDT'], 8));
        $this->assertSame(0, bccomp('1', $bal['BTC'], 8));
        $this->assertSame(0, bccomp('0.1', $bal['ETH'], 8));
    }

    public function testNegativePoolFiresAlertSink(): void
    {
        $alerts = [];
        $gw = $this->gatewayFor($this->runBtc, '100', function ($kind, $msg) use (&$alerts) { $alerts[] = $kind; });
        // budget 1000: a 20-BTC buy at 95 = 1900 + fee → pool goes negative
        $gw->placeLimitOrder('BTCUSDT', 'Buy', '95', '20', 'sw-neg-1');
        $this->tickBtc = '94'; $gw->tickerPrice('BTCUSDT');
        $this->assertContains('sim_wallet_negative', $alerts);
    }
}
