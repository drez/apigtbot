<?php

namespace Tests\Custom\Bot;

use App\BotOrderQuery;
use App\Domains\Bot\IntendedOrder;
use App\Domains\Bot\OrderStore;
use App\GridRun;
use App\TradeCycleQuery;
use PHPUnit\Framework\TestCase;

class OrderStoreModeTest extends TestCase
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
        $r->setLabel('ordstoremodetest-' . bin2hex(random_bytes(4)));
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

    private function store(bool $simulated): OrderStore
    {
        return new OrderStore((int) $this->run->getIdGridRun(), 'uidtest', null, $simulated);
    }

    public function testRowsStampedWithMode(): void
    {
        $sim = $this->store(true);
        $o = new IntendedOrder('Buy', 1, '95', '1');
        $row = $sim->recordOpen($o, 'cid-sim-1', '95', '1', null);
        $this->assertTrue((bool) $row->getSimulated());

        $sim->recordCycle(['level_idx' => 1, 'buy_price' => '95', 'sell_price' => '105', 'qty' => '1', 'realized_pnl' => '9.8', 'fees_total' => '0.2']);
        $c = TradeCycleQuery::create()->filterByIdGridRun((int) $this->run->getIdGridRun())->findOne();
        $this->assertTrue((bool) $c->getSimulated());
    }

    public function testQueriesAreModeDisjoint(): void
    {
        $sim = $this->store(true);
        $real = $this->store(false);
        $sim->recordOpen(new IntendedOrder('Buy', 1, '95', '1'), 'cid-sim-1', '95', '1', null);
        $real->recordOpen(new IntendedOrder('Buy', 2, '96', '1'), 'cid-real-1', '96', '1', null);

        $this->assertCount(1, $sim->openRows());
        $this->assertSame('cid-sim-1', $sim->openRows()[0]['client_order_id']);
        $this->assertCount(1, $real->openRows());
        $this->assertSame('cid-real-1', $real->openRows()[0]['client_order_id']);

        $sim->recordCycle(['level_idx' => 1, 'buy_price' => '95', 'sell_price' => '105', 'qty' => '1', 'realized_pnl' => '9.8', 'fees_total' => '0.2']);
        $this->assertSame(0, bccomp('9.8', $sim->realizedToday(), 8));
        $this->assertSame(0, bccomp('0', $real->realizedToday(), 8));
    }

    public function testNullModeIsUnfiltered(): void
    {
        $this->store(true)->recordOpen(new IntendedOrder('Buy', 1, '95', '1'), 'cid-sim-1', '95', '1', null);
        $this->store(false)->recordOpen(new IntendedOrder('Buy', 2, '96', '1'), 'cid-real-1', '96', '1', null);
        $legacy = new OrderStore((int) $this->run->getIdGridRun(), 'uidtest');
        $this->assertCount(2, $legacy->openRows());
    }

    public function testCancelOtherModeOpens(): void
    {
        $this->store(false)->recordOpen(new IntendedOrder('Buy', 2, '96', '1'), 'cid-real-1', '96', '1', null);
        $sim = $this->store(true);
        $sim->recordOpen(new IntendedOrder('Buy', 1, '95', '1'), 'cid-sim-1', '95', '1', null);

        $this->assertSame(1, $sim->cancelOtherModeOpens());
        $realRow = BotOrderQuery::create()->findOneByClientOrderId('cid-real-1');
        $this->assertSame('Canceled', (string) $realRow->getState());
        // own-mode order untouched
        $this->assertCount(1, $sim->openRows());
        // idempotent
        $this->assertSame(0, $sim->cancelOtherModeOpens());
    }
}
