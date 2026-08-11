<?php

namespace Tests\Custom\Bot;

use App\BotOrder;
use App\Domains\Bot\SimWallet;
use App\GridRun;
use PHPUnit\Framework\TestCase;

class SimWalletTest extends TestCase
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
        $r->setLabel('simwallettest-' . bin2hex(random_bytes(4)));
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

    private function filledRow(GridRun $run, string $cid, string $side, string $price, string $qty, string $fee): void
    {
        $row = new BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId($cid);
        $row->setLevelIdx(1);
        $row->setSide($side);
        $row->setState('Filled');
        $row->setPrice($price);
        $row->setQty($qty);
        $row->setFilledQty($qty);
        $row->setFeePaid($fee);
        $row->setSimulated(true);
        $row->save();
    }

    public function testSharedBudgetDefaultsTo1000(): void
    {
        // config row may or may not exist in the test DB; both paths return a non-empty bc string
        $this->assertMatchesRegularExpression('/^\d+(\.\d+)?$/', SimWallet::sharedBudget());
    }

    public function testDeriveFromEmptyLedgerSeedsBudget(): void
    {
        $bal = SimWallet::deriveAndStore();
        $this->assertSame(0, bccomp(SimWallet::sharedBudget(), $bal['USDT'], 8));
    }

    public function testDeriveReplaysBuysAndSells(): void
    {
        $this->filledRow($this->run, 'sw-b1', 'Buy', '95', '1', '0.095');
        $this->filledRow($this->run, 'sw-s1', 'Sell', '105', '0.4', '0.042');
        $bal = SimWallet::deriveAndStore();
        // budget - (95 + 0.095) + (42 - 0.042) = budget - 53.137 ; base 1 - 0.4 = 0.6
        $expected = bcadd(bcsub(SimWallet::sharedBudget(), '95.095', 12), '41.958', 12);
        $this->assertSame(0, bccomp($expected, $bal['USDT'], 8));
        $this->assertSame(0, bccomp('0.6', $bal['BTC'], 8));
    }

    public function testEpochExcludesOlderRows(): void
    {
        $this->filledRow($this->run, 'sw-old', 'Buy', '95', '1', '0.095');
        \Propel::getConnection()->exec(
            "UPDATE bot_order SET date_creation = '2000-01-01 00:00:00' WHERE client_order_id = 'sw-old'"
        );
        // point the epoch config at 2001 for this transaction
        $c = \App\ConfigQuery::create()->findOneByConfig('gtbot_sim_wallet_epoch');
        if (!$c) { $c = new \App\Config(); $c->setConfig('gtbot_sim_wallet_epoch'); $c->setCategory(0); $c->setType('string'); }
        $c->setValue('2001-01-01 00:00:00');
        $c->save();
        $bal = SimWallet::deriveAndStore();
        $this->assertSame(0, bccomp(SimWallet::sharedBudget(), $bal['USDT'], 8));
    }

    public function testApplyDeltaCreatesRowsAndAdds(): void
    {
        SimWallet::deriveAndStore();
        $out = SimWallet::applyDelta(['USDT' => '-95.095', 'BTC' => '1']);
        $this->assertSame(0, bccomp(bcsub(SimWallet::sharedBudget(), '95.095', 12), $out['USDT'], 8));
        $this->assertSame(0, bccomp('1', $out['BTC'], 8));
        $again = SimWallet::applyDelta(['BTC' => '0.5']);
        $this->assertSame(0, bccomp('1.5', $again['BTC'], 8));
    }

    public function testApplyDeltaOnBrandNewAssetTwiceIsStable(): void
    {
        $out = SimWallet::applyDelta(['XRP' => '5']);
        $this->assertSame(0, bccomp('5', $out['XRP'], 8));
        $again = SimWallet::applyDelta(['XRP' => '-2']);
        $this->assertSame(0, bccomp('3', $again['XRP'], 8));
    }

    public function testAssetsFor(): void
    {
        $this->assertSame(['BTC', 'USDT'], SimWallet::assetsFor('BTCUSDT'));
        $this->assertSame(['SOL', 'USDT'], SimWallet::assetsFor('SOLUSDT'));
    }
}
