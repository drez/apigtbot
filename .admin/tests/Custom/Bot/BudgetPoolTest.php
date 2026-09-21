<?php

namespace Tests\Custom\Bot;

use App\BotCommandQuery;
use App\BotEventQuery;
use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\BudgetGuard;
use App\Domains\Bot\BudgetPool;
use App\GridRun;
use PHPUnit\Framework\TestCase;

/**
 * Dashboard "edit budget": BudgetPool::set writes gtbot_shared_budget_quote
 * and Reloads every active run (paper wallet re-seeds at boot); the
 * gtbot_use_all_funds switch makes the pool cap follow the live account
 * value instead of the fixed config number.
 */
class BudgetPoolTest extends TestCase
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
        $this->config('gtbot_shared_budget_quote', '1000');
        $this->config(BudgetPool::CONFIG_USE_ALL, '0');
        \Propel::getConnection()->exec('DELETE FROM sim_wallet');
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
        // Config rows edited here (use-all-funds flag) stay in Propel's instance
        // pool with their in-memory value after the rollback — drop them
        \App\ConfigPeer::clearInstancePool();
    }

    private function config(string $key, string $value): void
    {
        $c = ConfigQuery::create()->findOneByConfig($key) ?? (new Config())->setConfig($key);
        $c->setValue($value);
        $c->save();
    }

    private function configValue(string $key): ?string
    {
        return ConfigQuery::create()->findOneByConfig($key)?->getValue();
    }

    /** upsert: sim_wallet.asset is unique, so a second call must not re-insert */
    private function wallet(string $asset, string $qty): void
    {
        \Propel::getConnection()->prepare(
            'INSERT INTO sim_wallet (asset, qty, date_creation, date_modification) VALUES (?, ?, NOW(), NOW())'
            . ' ON DUPLICATE KEY UPDATE qty = VALUES(qty), date_modification = NOW()'
        )->execute([$asset, $qty]);
    }

    private function mkRun(string $budget, string $status = 'Live'): GridRun
    {
        $r = new GridRun();
        $r->setLabel('bp-' . bin2hex(random_bytes(4)));
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
        $r->setRunUid('bp');
        $r->save();
        return $r;
    }

    private function commands(GridRun $r): array
    {
        $out = [];
        foreach (BotCommandQuery::create()->filterByIdGridRun((int) $r->getIdGridRun())->orderByIdBotCommand()->find() as $c) {
            $out[] = (string) $c->getCommand();
        }
        return $out;
    }

    // ── set() ───────────────────────────────────────────────────────────

    public function testSetWritesConfigAndReloadsActiveRunsOnly(): void
    {
        $live = $this->mkRun('400', 'Live');
        $halted = $this->mkRun('300', 'Halted');
        $draft = $this->mkRun('100', 'Draft');

        $res = BudgetPool::set('1500');

        $this->assertTrue($res['ok'], $res['message']);
        $this->assertSame('1500', $this->configValue('gtbot_shared_budget_quote'));
        $this->assertSame(['Reload'], $this->commands($live));
        $this->assertSame([], $this->commands($halted));
        $this->assertSame([], $this->commands($draft));
        $this->assertSame([(int) $live->getIdGridRun()], $res['reloaded']);

        $ev = BotEventQuery::create()->filterByIdGridRun((int) $live->getIdGridRun())->filterByKind(BudgetPool::KIND_CHANGED)->findOne();
        $this->assertNotNull($ev, 'budget_changed event on the reloaded run');
        $this->assertStringContainsString('1000', (string) $ev->getMessage());
        $this->assertStringContainsString('1500', (string) $ev->getMessage());
    }

    public function testSetAcceptsDecimalsAndStoresWholeUsdt(): void
    {
        $res = BudgetPool::set(' 1234.56 ');
        $this->assertTrue($res['ok'], $res['message']);
        $this->assertSame('1234', $this->configValue('gtbot_shared_budget_quote'));
    }

    public function testSetRefusesNonPositiveOrGarbage(): void
    {
        foreach (['0', '-5', 'abc', '', '1e3'] as $bad) {
            $res = BudgetPool::set($bad);
            $this->assertFalse($res['ok'], "value '$bad' must be refused");
        }
        $this->assertSame('1000', $this->configValue('gtbot_shared_budget_quote'));
    }

    public function testSetBelowSlicesNowShrinksThemInsteadOfOvercommitting(): void
    {
        // Since 2026-09-19 a pool change REALLOCATES immediately: a run with no
        // committed inventory is simply shrunk to fit, so lowering the cap no
        // longer strands the fleet in the overcommitted state.
        $run = $this->mkRun('800', 'Live');
        $res = BudgetPool::set('500');

        $this->assertTrue($res['ok'], $res['message']);
        $this->assertSame('500', $this->configValue('gtbot_shared_budget_quote'));
        $this->assertFalse($res['overcommitted'], 'the slice should have been shrunk to fit');
        $this->assertSame(0, bccomp('0', $res['shortfall'], 0), 'nothing was committed, so nothing is unfreeable');

        $run->reload();
        $this->assertSame(
            -1,
            bccomp((string) $run->getBudgetQuote(), '800', 0),
            'the slice must have come down'
        );
        $this->assertNull(\App\Domains\Bot\BudgetGuard::check());
    }

    public function testSetBelowCommittedInventoryReportsAShortfallAndSellsNothing(): void
    {
        // The floor a run cannot go below includes the quote its open inventory
        // is tied up in. When the floors alone exceed the new cap the residual
        // is reported as a shortfall — and NOTHING is sold, because a pool
        // lowering is an operator preference, not a funding emergency.
        $run = $this->mkRun('800', 'Live');

        $buy = new \App\BotOrder();
        $buy->setIdGridRun((int) $run->getIdGridRun());
        $buy->setClientOrderId('bp-' . bin2hex(random_bytes(4)));
        $buy->setLevelIdx(1);
        $buy->setSide('Buy');
        $buy->setState('Filled');
        $buy->setPrice('700');
        $buy->setQty('1');
        $buy->setFilledQty('1');
        $buy->setFeePaid('0.7');
        $buy->setSimulated((bool) $run->getSimulated());
        $buy->save();

        $res = BudgetPool::set('100');

        $this->assertTrue($res['ok'], $res['message']);
        $this->assertSame('100', $this->configValue('gtbot_shared_budget_quote'));
        $this->assertSame(1, bccomp($res['shortfall'], '0', 0), 'the committed 700 cannot be freed');
        $this->assertStringContainsString('Nothing was sold', $res['message']);

        $run->reload();
        $this->assertSame(
            1,
            bccomp((string) $run->getBudgetQuote(), '100', 0),
            'the slice must stay above the cap rather than strand the inventory'
        );
    }

    // ── cap() / use-all-funds ───────────────────────────────────────────

    public function testCapIsTheFixedBudgetByDefault(): void
    {
        $this->wallet('USDT', '2500');
        $this->assertFalse(BudgetPool::useAllFunds());
        $this->assertSame('1000', BudgetPool::cap());
    }

    public function testCapFollowsAccountValueDownButNeverAboveTheConfiguredBudget(): void
    {
        // Operator rule (2026-09-19): tokens and gains shift the MIX a run can
        // draw on, they never raise the ceiling. gtbot_shared_budget_quote is
        // the hard cap, so this branch is min(wallet, fixed).
        $this->config(BudgetPool::CONFIG_USE_ALL, '1');

        $this->wallet('USDT', '800.50');
        $this->assertTrue(BudgetPool::useAllFunds());
        $this->assertSame('800', BudgetPool::cap(), 'a shrunken wallet lowers the cap');

        $this->wallet('USDT', '1234.56');
        $this->assertSame('1000', BudgetPool::cap(), 'a wallet above the budget does NOT raise it');
        $this->assertSame('1000', BudgetPool::fixed(), 'the config seed is untouched');
    }

    public function testCapFallsBackToFixedWhenWalletIsEmpty(): void
    {
        $this->config(BudgetPool::CONFIG_USE_ALL, '1');
        $this->assertSame('1000', BudgetPool::cap());
    }

    public function testBudgetGuardHonoursTheLiveCap(): void
    {
        // the wallet can only pull the cap DOWN, so a slice above the
        // configured budget overcommits whether use-all-funds is on or off
        $this->config(BudgetPool::CONFIG_USE_ALL, '1');
        $this->wallet('USDT', '2000');
        $this->mkRun('1500', 'Live');
        $over = BudgetGuard::check();
        $this->assertNotNull($over, 'a 2000 wallet does not license a 1500 slice past the 1000 budget');
        $this->assertSame('1000', bcadd($over['budget'], '0', 0));

        // but a wallet BELOW the budget tightens it
        $this->wallet('USDT', '900');
        $this->assertSame('900', BudgetPool::cap());

        $this->config(BudgetPool::CONFIG_USE_ALL, '0');
        $over = BudgetGuard::check();
        $this->assertNotNull($over, 'the same slice overcommits the fixed 1000 budget');
        $this->assertSame('1000', bcadd($over['budget'], '0', 0));
    }
}
