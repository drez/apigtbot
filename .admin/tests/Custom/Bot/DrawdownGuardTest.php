<?php

namespace Tests\Custom\Bot;

use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\DrawdownGuard;
use App\GridRun;
use PHPUnit\Framework\TestCase;

class DrawdownGuardTest extends TestCase
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
        $con = \Propel::getConnection();
        $con->beginTransaction();
        // neutralize whatever the local DB holds — this test owns the world
        // (status is a Propel ENUM stored as an int: raw SQL can't say 'Done')
        foreach (\App\GridRunQuery::create()->filterByStatus('Done', \Criteria::NOT_EQUAL)->find() as $r) {
            $r->setStatus('Done');
            $r->save();
        }
        $con->exec('DELETE FROM sim_wallet');
        $this->setConfig('gtbot_shared_budget_quote', '1000');
        $this->setConfig('gtbot_max_drawdown_pct', '');
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function setConfig(string $key, string $v): void
    {
        $c = ConfigQuery::create()->findOneByConfig($key) ?? (new Config())->setConfig($key);
        $c->setValue($v);
        $c->save();
    }

    private function seedWallet(array $assets): void
    {
        $ins = \Propel::getConnection()->prepare(
            'INSERT INTO sim_wallet (asset, qty, date_creation, date_modification) VALUES (?, ?, NOW(), NOW())'
        );
        foreach ($assets as $asset => $qty) {
            $ins->execute([$asset, $qty]);
        }
    }

    private function mkRun(
        string $symbol,
        ?string $lastPrice,
        bool $simulated = true,
        string $status = 'Live',
        ?string $balBase = null,
        ?string $balQuote = null,
        ?string $lastTickAt = null
    ): GridRun {
        $r = new GridRun();
        $r->setLabel('dd-' . bin2hex(random_bytes(4)));
        $r->setSymbol($symbol);
        $r->setStatus($status);
        $r->setSimulated($simulated);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('100');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid('dd');
        if ($lastPrice !== null) {
            $r->setLastPrice($lastPrice);
        }
        if ($balBase !== null) {
            $r->setBalBase($balBase);
        }
        if ($balQuote !== null) {
            $r->setBalQuote($balQuote);
        }
        $r->setLastTickAt($lastTickAt ?? date('Y-m-d H:i:s'));
        $r->save();
        return $r;
    }

    // ── floor ───────────────────────────────────────────────────────────

    public function testFloorDefaultsTo25Pct(): void
    {
        $this->assertSame(0, bccomp('750', (string) DrawdownGuard::floor(), 12));
    }

    public function testFloorHonorsConfiguredPct(): void
    {
        $this->setConfig('gtbot_max_drawdown_pct', '40');
        $this->assertSame(0, bccomp('600', (string) DrawdownGuard::floor(), 12));
    }

    public function testExplicitZeroDisables(): void
    {
        $this->setConfig('gtbot_max_drawdown_pct', '0');
        $this->assertNull(DrawdownGuard::floor());
        $this->assertNull(DrawdownGuard::check());
    }

    // ── equity, paper mode ──────────────────────────────────────────────

    public function testSimEquityPricesBaseAssetsAtRunLastPrice(): void
    {
        $this->mkRun('BTCUSDT', '60000');
        $this->seedWallet(['USDT' => '500', 'BTC' => '0.01']);
        $eq = DrawdownGuard::equity();
        $this->assertSame(0, bccomp('1100', $eq['equity'], 12)); // 500 + 0.01×60000
        $this->assertSame([], $eq['unpriced']);
    }

    public function testUnpricedAssetCountsZeroAndIsReported(): void
    {
        $this->mkRun('BTCUSDT', '60000');
        $this->seedWallet(['USDT' => '800', 'XRP' => '100']);
        $eq = DrawdownGuard::equity();
        $this->assertSame(0, bccomp('800', $eq['equity'], 12));
        $this->assertSame(['XRP'], $eq['unpriced']);
    }

    // ── check boundary ──────────────────────────────────────────────────

    public function testEquityAtFloorIsOk(): void
    {
        $this->mkRun('BTCUSDT', '60000');
        $this->seedWallet(['USDT' => '750']);
        $this->assertNull(DrawdownGuard::check());
    }

    public function testEquityUnderFloorBreaches(): void
    {
        $this->mkRun('BTCUSDT', '60000');
        $this->seedWallet(['USDT' => '749']);
        $over = DrawdownGuard::check();
        $this->assertNotNull($over);
        $this->assertSame(0, bccomp('749', $over['equity'], 12));
        $this->assertSame(0, bccomp('750', $over['floor'], 12));
        $this->assertSame(0, bccomp('1000', $over['budget'], 12));
    }

    // ── equity, real mode ───────────────────────────────────────────────

    public function testRealModeDedupesTheOneAccount(): void
    {
        // two REAL runs on the same symbol stamp the SAME account —
        // quote and base must each count once (freshest stamp wins)
        $this->mkRun('BTCUSDT', '1000', false, 'Live', '0.5', '400', '2026-08-01 10:00:00');
        $this->mkRun('BTCUSDT', '1000', false, 'Live', '0.5', '400', '2026-08-01 10:00:05');
        $eq = DrawdownGuard::equity();
        $this->assertSame(0, bccomp('900', $eq['equity'], 12)); // 400 + 0.5×1000
    }

    // ── tripAll ─────────────────────────────────────────────────────────

    public function testTripAllKillsOnlyActiveRuns(): void
    {
        $a = $this->mkRun('BTCUSDT', '60000');
        $b = $this->mkRun('ETHUSDT', '2000', true, 'Testnet');
        $done = $this->mkRun('BNBUSDT', '600', true, 'Done');
        $this->assertSame(2, DrawdownGuard::tripAll());
        $a->reload();
        $b->reload();
        $done->reload();
        $this->assertTrue((bool) $a->getKillSwitch());
        $this->assertTrue((bool) $b->getKillSwitch());
        $this->assertFalse((bool) $done->getKillSwitch());
        $this->assertSame(0, DrawdownGuard::tripAll()); // idempotent
    }
}
