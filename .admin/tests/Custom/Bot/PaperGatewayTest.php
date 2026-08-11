<?php

namespace Tests\Custom\Bot;

use App\BotOrder;
use App\Domains\Bot\Gateway\BinanceApiError;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Gateway\PaperGateway;
use App\Domains\Bot\SimWallet;
use App\GridRun;
use PHPUnit\Framework\TestCase;

class PaperGatewayTest extends TestCase
{
    private static bool $booted = false;
    private GridRun $run;
    /** @var string next price the fake mainnet ticker returns */
    private string $tick = '100';

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
        $r->setLabel('papertest-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Live');
        $r->setSimulated(true);
        $r->setPLow('90');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('1000');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('1000');
        $r->setMaxOrderQuote('500');
        $r->setDailyLossLimitQuote('100');
        $r->save();
        $this->run = $r;
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function gw(): PaperGateway
    {
        $transport = function (string $method, string $url, array $headers, ?string $body): array {
            if (str_contains($url, '/api/v3/ticker/price')) {
                return ['status' => 200, 'headers' => [], 'body' => json_encode(['price' => $this->tick])];
            }
            if (str_contains($url, '/api/v3/exchangeInfo')) {
                return ['status' => 200, 'headers' => [], 'body' => json_encode(['symbols' => [['symbol' => 'BTCUSDT', 'filters' => []]]])];
            }
            throw new \RuntimeException("unexpected paper-mode HTTP call: $url");
        };
        $public = new BinanceGateway('https://api.binance.com', '', '', $transport);
        return new PaperGateway($public, $this->run, '0.001');
    }

    public function testWalletSeedsFromBudget(): void
    {
        $bal = $this->gw()->accountBalances();
        $this->assertSame('0', $bal['BTC']['free']);
        $this->assertSame(0, bccomp(SimWallet::sharedBudget(), $bal['USDT']['free'], 8));
    }

    public function testBuyFillMovesWalletAndPersists(): void
    {
        $gw = $this->gw();
        $gw->placeLimitOrder('BTCUSDT', 'Buy', '95', '1', 'cid-b1');
        $this->assertCount(1, $gw->openOrders('BTCUSDT'));

        $this->tick = '94';
        $gw->tickerPrice('BTCUSDT'); // advances the book → fill

        $this->assertCount(0, $gw->openOrders('BTCUSDT'));
        $st = $gw->orderStatus('BTCUSDT', 'cid-b1');
        $this->assertSame('FILLED', $st['status']);
        $this->assertSame(0, bccomp('1', (string) $st['executedQty'], 8));

        // wallet: quote -(95 + 0.095 fee), base +1 — persisted in the shared sim_wallet table
        $expectedQuote = bcsub(SimWallet::sharedBudget(), '95.095', 12);
        $bal = $gw->accountBalances();
        $this->assertSame(0, bccomp('1', $bal['BTC']['free'], 8));
        $this->assertSame(0, bccomp($expectedQuote, $bal['USDT']['free'], 8));
        $walletBal = SimWallet::balances();
        $this->assertSame(0, bccomp('1', $walletBal['BTC'], 8));
        $this->assertSame(0, bccomp($expectedQuote, $walletBal['USDT'], 8));
    }

    public function testSellFillCreditsQuoteMinusFee(): void
    {
        $gw = $this->gw();
        $gw->placeLimitOrder('BTCUSDT', 'Buy', '95', '1', 'cid-b1');
        $this->tick = '94';
        $gw->tickerPrice('BTCUSDT');
        $gw->placeLimitOrder('BTCUSDT', 'Sell', '105', '1', 'cid-s1');
        $this->tick = '106';
        $gw->tickerPrice('BTCUSDT');

        $bal = $gw->accountBalances();
        $this->assertSame(0, bccomp('0', $bal['BTC']['free'], 8));
        // budget - (95 + 0.095) + (105 - 0.105)
        $expectedQuote = bcadd(bcsub(SimWallet::sharedBudget(), '95.095', 12), '104.895', 12);
        $this->assertSame(0, bccomp($expectedQuote, $bal['USDT']['free'], 8));
    }

    public function testMyTradesReportsQuoteCommission(): void
    {
        $gw = $this->gw();
        $res = $gw->placeLimitOrder('BTCUSDT', 'Buy', '95', '1', 'cid-b1');
        $this->tick = '94';
        $gw->tickerPrice('BTCUSDT');
        $trades = $gw->myTrades('BTCUSDT', ['orderId' => $res['orderId']]);
        $this->assertCount(1, $trades);
        $this->assertSame('USDT', $trades[0]['commissionAsset']);
        $this->assertSame(0, bccomp('0.095', (string) $trades[0]['commission'], 8));
    }

    public function testDuplicateCidHonorsIdempotentContract(): void
    {
        $gw = $this->gw();
        $gw->placeLimitOrder('BTCUSDT', 'Buy', '95', '1', 'cid-b1');
        $res = $gw->placeLimitOrder('BTCUSDT', 'Buy', '95', '1', 'cid-b1');
        $this->assertTrue($res['duplicate']);
    }

    public function testCancelRemovesFromBookAndStatusReflectsIt(): void
    {
        $gw = $this->gw();
        $gw->placeLimitOrder('BTCUSDT', 'Buy', '95', '1', 'cid-b1');
        $gw->cancelOrder('BTCUSDT', 'cid-b1');
        $this->assertCount(0, $gw->openOrders('BTCUSDT'));
        $this->assertSame('CANCELED', $gw->orderStatus('BTCUSDT', 'cid-b1')['status']);
    }

    public function testHydratesBookFromSimulatedOpenRows(): void
    {
        $row = new BotOrder();
        $row->setIdGridRun((int) $this->run->getIdGridRun());
        $row->setClientOrderId('cid-hyd');
        $row->setLevelIdx(1);
        $row->setSide('Buy');
        $row->setState('BUY_OPEN');
        $row->setPrice('95');
        $row->setQty('1');
        $row->setSimulated(true);
        $row->save();
        // a REAL open row must NOT be adopted into the paper book
        $real = new BotOrder();
        $real->setIdGridRun((int) $this->run->getIdGridRun());
        $real->setClientOrderId('cid-real');
        $real->setLevelIdx(2);
        $real->setSide('Buy');
        $real->setState('BUY_OPEN');
        $real->setPrice('96');
        $real->setQty('1');
        $real->setSimulated(false);
        $real->save();

        $gw = $this->gw();
        $open = $gw->openOrders('BTCUSDT');
        $this->assertCount(1, $open);
        $this->assertSame('cid-hyd', $open[0]['clientOrderId']);
    }

    public function testOrderStatusFallsBackToDbAfterRestart(): void
    {
        $row = new BotOrder();
        $row->setIdGridRun((int) $this->run->getIdGridRun());
        $row->setClientOrderId('cid-old');
        $row->setLevelIdx(1);
        $row->setSide('Buy');
        $row->setState('Filled');
        $row->setPrice('95');
        $row->setQty('1');
        $row->setFilledQty('1');
        $row->setSimulated(true);
        $row->save();

        $st = $this->gw()->orderStatus('BTCUSDT', 'cid-old');
        $this->assertSame('FILLED', $st['status']);
        $this->assertSame(0, bccomp('1', (string) $st['executedQty'], 8));
    }

    public function testUnknownCidThrowsBinanceStyleError(): void
    {
        $this->expectException(BinanceApiError::class);
        $this->gw()->orderStatus('BTCUSDT', 'cid-nope');
    }

    /**
     * Regression for the crash-window double-count bug: PaperGateway used
     * to trust grid_run.sim_bal_quote/base at construction time; now it must
     * ignore any stale value sitting in the shared sim_wallet table and
     * re-derive from the ledger via SimWallet::deriveAndStore(). A crash
     * between fill() persisting the wallet and the Daemon marking the
     * bot_order row Filled left a row that looked still-open at next boot —
     * the book hydration re-adopted it, and the next tick filled it AGAIN,
     * moving an already-moved wallet a second time.
     *
     * Here the sim_wallet USDT row is deliberately set to a stale/wrong
     * value; the gateway's constructor must ignore it and derive from the
     * ledger instead, and the still-open row must still fill exactly once.
     */
    public function testWalletDerivesFromLedgerNotStaleSimBalOnCrashWindow(): void
    {
        $filled = new BotOrder();
        $filled->setIdGridRun((int) $this->run->getIdGridRun());
        $filled->setClientOrderId('cid-filled');
        $filled->setLevelIdx(0);
        $filled->setSide('Buy');
        $filled->setState('Filled');
        $filled->setPrice('95');
        $filled->setQty('1');
        $filled->setFilledQty('1');
        $filled->setFeePaid('0.095');
        $filled->setSimulated(true);
        $filled->save();

        $open = new BotOrder();
        $open->setIdGridRun((int) $this->run->getIdGridRun());
        $open->setClientOrderId('cid-open');
        $open->setLevelIdx(1);
        $open->setSide('Buy');
        $open->setState('BUY_OPEN');
        $open->setPrice('90');
        $open->setQty('1');
        $open->setSimulated(true);
        $open->save();

        // Stale/wrong persisted wallet from before the crash — must be
        // ignored, not used as the derivation's starting point.
        \Propel::getConnection()->exec(
            "INSERT INTO sim_wallet (asset, qty, date_creation, date_modification) VALUES ('USDT', 999999, NOW(), NOW())
             ON DUPLICATE KEY UPDATE qty = 999999"
        );

        $gw = $this->gw();

        // Derived immediately at construction from sharedBudget() minus
        // the single Filled row: budget - (95*1 + 0.095), base = 1.
        $expectedAfterFirst = bcsub(SimWallet::sharedBudget(), '95.095', 12);
        $bal = $gw->accountBalances();
        $this->assertSame(0, bccomp('1', $bal['BTC']['free'], 8));
        $this->assertSame(0, bccomp($expectedAfterFirst, $bal['USDT']['free'], 8));

        // The still-open row fills exactly once via a tick.
        $this->tick = '89';
        $gw->tickerPrice('BTCUSDT');
        $st = $gw->orderStatus('BTCUSDT', 'cid-open');
        $this->assertSame('FILLED', $st['status']);

        // Single-count expectation: expectedAfterFirst - (90*1 + 0.09), base = 2.
        // NOT stale(999999) - fee, and NOT double-counting the first fill.
        $expectedAfterSecond = bcsub($expectedAfterFirst, '90.09', 12);
        $bal = $gw->accountBalances();
        $this->assertSame(0, bccomp('2', $bal['BTC']['free'], 8));
        $this->assertSame(0, bccomp($expectedAfterSecond, $bal['USDT']['free'], 8));
    }
}
