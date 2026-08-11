<?php

namespace Tests\Custom\Bot;

use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\BudgetGuard;
use App\GridRun;
use PHPUnit\Framework\TestCase;

class BudgetGuardTest extends TestCase
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
        $_SESSION[_AUTH_VAR] = new \ApiGoat\Sessions\AuthySession();
        self::$booted = true;
    }

    protected function setUp(): void
    {
        \Propel::getConnection()->beginTransaction();
        $this->setSharedBudget('1000');
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function setSharedBudget(string $v): void
    {
        $c = ConfigQuery::create()->findOneByConfig('gtbot_shared_budget_quote') ?? (new Config())->setConfig('gtbot_shared_budget_quote');
        $c->setValue($v);
        $c->save();
    }

    private function mkRun(string $budget, string $status = 'Testnet'): GridRun
    {
        $r = new GridRun();
        $r->setLabel('bg-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus($status);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualBase');
        $r->setBudgetQuote($budget);
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid('bg');
        $r->save();
        return $r;
    }

    public function testWithinBudgetIsNull(): void
    {
        $this->mkRun('400');
        $this->mkRun('600'); // exactly the pool — not an overcommit
        $this->assertNull(BudgetGuard::check());
    }

    public function testOvercommitReportsSumAndExcess(): void
    {
        $this->mkRun('700');
        $this->mkRun('600');
        $over = BudgetGuard::check();
        $this->assertNotNull($over);
        $this->assertSame(0, bccomp('1300', $over['sum'], 2));
        $this->assertSame(0, bccomp('300', $over['excess'], 2));
    }

    public function testInactiveStatusesDoNotCount(): void
    {
        $this->mkRun('700');
        $this->mkRun('600', 'Done');
        $this->mkRun('600', 'Draft');
        $this->mkRun('600', 'Halted');
        $this->assertNull(BudgetGuard::check(), 'only DryRun/Testnet/Live slices draw on the wallet');
    }

    public function testCandidateReplacesTheExcludedRunsSlice(): void
    {
        $a = $this->mkRun('700');
        $this->mkRun('250');
        // raising A to 800 would overcommit; lowering it to 750 fits
        $this->assertNotNull(BudgetGuard::check((int) $a->getIdGridRun(), '800', 'Testnet'));
        $this->assertNull(BudgetGuard::check((int) $a->getIdGridRun(), '750', 'Testnet'));
    }

    public function testCandidateParkedInDraftFreesItsSlice(): void
    {
        $a = $this->mkRun('700');
        $this->mkRun('600'); // DB is overcommitted right now
        $this->assertNotNull(BudgetGuard::check());
        // ...but a save moving A to Draft resolves it
        $this->assertNull(BudgetGuard::check((int) $a->getIdGridRun(), '700', 'Draft'));
    }

    public function testWrapperHookRefusesOvercommittingSave(): void
    {
        $this->mkRun('700');
        $b = $this->mkRun('250');
        // simulate the GUI/API save path: raise B's budget to 400 (sum 1100)
        $wrapper = (new \ReflectionClass(\App\GridRunServiceWrapper::class))->newInstanceWithoutConstructor();
        $data = ['BudgetQuote' => '400', 'Status' => 'Testnet'];
        $messages = null;
        $ext = false;
        $error = null;
        $wrapper->beforeSave($b, $data, false, $messages, $ext, $error);
        $this->assertIsArray($ext, 'overcommitting save must set the extended-validation failure');
        $this->assertStringContainsString('overcommit', array_key_first($ext));

        // and a save that fits passes untouched
        $data = ['BudgetQuote' => '300', 'Status' => 'Testnet'];
        $ext = false;
        $wrapper->beforeSave($b, $data, false, $messages, $ext, $error);
        $this->assertFalse($ext);
    }
}
