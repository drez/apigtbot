<?php

namespace Tests\Custom\Bot;

use ApiGoat\Sessions\AuthySession;
use App\BotOrder;
use App\GridRun;
use App\Mcp\Tools\GtbotPnlReportTool;
use App\TradeCycle;
use PHPUnit\Framework\TestCase;

/**
 * gtbot_pnl_report.metrics.unrealized_pnl must mark the inventory the
 * CURRENT ledger era actually holds — filled buys minus ALL cycles since the
 * epoch — not "all-time buys minus the cycles inside the report window",
 * which on prod run 1 (2026-08-28) reported +7695 USDT of phantom inventory
 * on a run holding 0.00008 BTC.
 */
class GtbotPnlReportToolTest extends TestCase
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
        $_SESSION[_AUTH_VAR] = new AuthySession();
        self::$booted = true;
    }

    protected function setUp(): void
    {
        \Propel::getConnection()->beginTransaction();
        $r = new GridRun();
        $r->setLabel('pnl-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Live');
        $r->setSimulated(true);
        $r->setPLow('60000');
        $r->setPHigh('72000');
        $r->setNLevels(6);
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
        $r->setRunUid('pnl' . bin2hex(random_bytes(2)));
        $r->setLastPrice('80000');
        $r->save();
        $this->run = $r;
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function buy(string $price, string $qty, string $when): void
    {
        $o = new BotOrder();
        $o->setIdGridRun((int) $this->run->getIdGridRun());
        $o->setClientOrderId('c-' . bin2hex(random_bytes(5)));
        $o->setLevelIdx(0);
        $o->setSide('Buy');
        $o->setState('Filled');
        $o->setPrice($price);
        $o->setQty($qty);
        $o->setFilledQty($qty);
        $o->setSimulated(true);
        $o->save();
        $o->setDateCreation($when); // add_tablestamp overwrites it on insert, keeps it on update
        $o->save();
    }

    private function cycle(string $buy, string $sell, string $qty, string $when): void
    {
        $c = new TradeCycle();
        $c->setIdGridRun((int) $this->run->getIdGridRun());
        $c->setLevelIdx(0);
        $c->setBuyPrice($buy);
        $c->setSellPrice($sell);
        $c->setQty($qty);
        $c->setRealizedPnl('1');
        $c->setFeesTotal('0.1');
        $c->setSimulated(true);
        $c->save();
        $c->setDateCreation($when);
        $c->save();
    }

    private function report(int $days = 7): array
    {
        $out = (new GtbotPnlReportTool())->handle(['run' => (int) $this->run->getIdGridRun(), 'days' => $days], $this->createMock(AuthySession::class));
        $text = $out['content'][0]['text'] ?? '';
        return json_decode($text, true) ?: [];
    }

    public function testCyclesOutsideTheWindowStillCloseTheirBuys(): void
    {
        $old = date('Y-m-d H:i:s', strtotime('-40 days'));
        $this->buy('70000', '0.01', $old);
        $this->cycle('70000', '71000', '0.01', $old);   // closed long before the 7-day window
        $r = $this->report(7);
        $this->assertSame(0, bccomp('0', (string) $r['metrics']['unrealized_pnl'], 8), 'a flat run has no unrealized PnL');
    }

    public function testOpenInventoryIsMarkedAtLastPrice(): void
    {
        $this->buy('70000', '0.01', date('Y-m-d H:i:s', strtotime('-1 day')));
        $r = $this->report(7);
        // 0.01 × (80000 − 70000)
        $this->assertSame(0, bccomp('100', (string) $r['metrics']['unrealized_pnl'], 6));
    }

    public function testInventoryBeforeTheLedgerEpochIsNotCounted(): void
    {
        $this->buy('70000', '0.01', date('Y-m-d H:i:s', strtotime('-10 days')));
        $this->run->setLedgerResetAt(date('Y-m-d H:i:s', strtotime('-5 days')));
        $this->run->save();
        $r = $this->report(30);
        $this->assertSame(0, bccomp('0', (string) $r['metrics']['unrealized_pnl'], 8), 'orphaned pre-epoch inventory is wallet-held, not the grid\'s');
    }
}
