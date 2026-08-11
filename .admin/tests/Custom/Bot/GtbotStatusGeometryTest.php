<?php

namespace Tests\Custom\Bot;

use ApiGoat\Sessions\AuthySession;
use App\GridRun;
use App\Mcp\Tools\GtbotPnlReportTool;
use App\Mcp\Tools\GtbotStatusTool;
use App\TradeCycle;
use PHPUnit\Framework\TestCase;

/**
 * The routine must be able to SEE whether its last refit actually took effect:
 * gtbot_status exposes requested vs applied geometry + a refit_pending flag.
 * gtbot_pnl_report exposes net_per_1k_day — the capital-efficiency KPI.
 */
class GtbotStatusGeometryTest extends TestCase
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

    private function makeRun(): GridRun
    {
        $r = new GridRun();
        $r->setLabel('geom-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setPLow('64000');
        $r->setPHigh('66000');
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
        $r->setRunUid('geomuid');
        // testPnlReportExposesNetPer1kDay seeds TradeCycle rows without
        // stamping simulated (default false/real) — pin the run to real mode
        // so gtbot_pnl_report's default mode filter still sees them.
        $r->setSimulated(false);
        $r->save();
        return $r;
    }

    public function testStatusReportsPendingRefitGeometry(): void
    {
        $run = $this->makeRun();
        // the working ladder was built on OLDER geometry than the row requests
        $run->setAppliedGeometry(json_encode([
            'p_low' => '63000', 'p_high' => '65500', 'n_levels' => '4',
            'spacing' => 'Geometric', 'allocation' => 'EqualQuote',
            'budget_quote' => '1000', 'fee_pct' => '0.001',
        ]));
        $run->save();

        $out = $this->decode((new GtbotStatusTool())->handle(['run' => (int) $run->getIdGridRun()], $this->createMock(AuthySession::class)));

        $this->assertTrue($out['geometry']['refit_pending'], 'requested differs from applied → a refit is waiting');
        $this->assertEqualsWithDelta(64000.0, (float) $out['geometry']['requested']['p_low'], 0.001);
        $this->assertEqualsWithDelta(63000.0, (float) $out['geometry']['applied']['p_low'], 0.001);
        $this->assertSame(4, (int) $out['geometry']['applied']['n_levels']);
    }

    public function testStatusReportsAppliedGeometryInSync(): void
    {
        $run = $this->makeRun();
        $run->setAppliedGeometry(json_encode([
            'p_low' => '64000', 'p_high' => '66000', 'n_levels' => '5',
            'spacing' => 'Geometric', 'allocation' => 'EqualQuote',
            'budget_quote' => '1000', 'fee_pct' => '0.001',
        ]));
        $run->save();

        $out = $this->decode((new GtbotStatusTool())->handle(['run' => (int) $run->getIdGridRun()], $this->createMock(AuthySession::class)));

        $this->assertFalse($out['geometry']['refit_pending']);
    }

    public function testPnlReportExposesNetPer1kDay(): void
    {
        $run = $this->makeRun(); // budget 1000
        foreach ([['5', 26], ['3', 2]] as [$pnl, $hoursAgo]) { // two active days
            $c = new TradeCycle();
            $c->setIdGridRun((int) $run->getIdGridRun());
            $c->setLevelIdx(1);
            $c->setBuyPrice('64000');
            $c->setSellPrice('64500');
            $c->setQty('0.01');
            $c->setRealizedPnl($pnl);
            $c->setFeesTotal('0.1');
            $c->save();
            // backdate AFTER insert — the ORM stamps date_creation=NOW on insert
            $c->setDateCreation(date('Y-m-d H:i:s', time() - $hoursAgo * 3600));
            $c->save();
        }

        $out = $this->decode((new GtbotPnlReportTool())->handle(['run' => (int) $run->getIdGridRun()], $this->createMock(AuthySession::class)));

        // 8 realized / 2 active days / 1000 budget × 1000 = 4.0 per $1k per day
        $this->assertEqualsWithDelta(4.0, (float) $out['metrics']['net_per_1k_day'], 0.01);
    }
}
