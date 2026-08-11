<?php

namespace Tests\Custom\Bot;

use ApiGoat\Sessions\AuthySession;
use App\BotOrder;
use App\Domains\Dashboard\DashboardData;
use App\GridRun;
use App\Mcp\Tools\GtbotPnlReportTool;
use App\Mcp\Tools\GtbotStatusTool;
use App\TradeCycle;
use PHPUnit\Framework\TestCase;

/**
 * Every STATS surface — dashboard KPIs, gtbot_status, gtbot_pnl_report — must
 * report ONLY the run's current mode by default: a paper-mode run must never
 * see its numbers muddied by real fills, and vice versa. One run seeded with
 * a sim cycle (pnl 10) + a real cycle (pnl 20), and a sim + a real open buy,
 * proves the switch actually changes what's reported. gtbot_pnl_report also
 * accepts an explicit mode override (sim|real|all) for cross-mode reporting.
 */
class ModeScopedStatsTest extends TestCase
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
        $r->setLabel('modescope-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setPLow('60000');
        $r->setPHigh('70000');
        $r->setNLevels(10);
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
        $r->setRunUid('modescope');
        $r->setSimulated(true);
        $r->save();
        $this->run = $r;

        $simCycle = new TradeCycle();
        $simCycle->setIdGridRun((int) $this->run->getIdGridRun());
        $simCycle->setLevelIdx(1);
        $simCycle->setBuyPrice('60000');
        $simCycle->setSellPrice('61000');
        $simCycle->setQty('0.001');
        $simCycle->setRealizedPnl('10');
        $simCycle->setFeesTotal('0.06');
        $simCycle->setSimulated(true);
        $simCycle->save();

        $realCycle = new TradeCycle();
        $realCycle->setIdGridRun((int) $this->run->getIdGridRun());
        $realCycle->setLevelIdx(2);
        $realCycle->setBuyPrice('62000');
        $realCycle->setSellPrice('63000');
        $realCycle->setQty('0.001');
        $realCycle->setRealizedPnl('20');
        $realCycle->setFeesTotal('0.06');
        $realCycle->setSimulated(false);
        $realCycle->save();

        $simOrder = new BotOrder();
        $simOrder->setIdGridRun((int) $this->run->getIdGridRun());
        $simOrder->setClientOrderId('sim-' . bin2hex(random_bytes(4)));
        $simOrder->setLevelIdx(3);
        $simOrder->setSide('Buy');
        $simOrder->setState('BUY_OPEN');
        $simOrder->setPrice('59000');
        $simOrder->setQty('0.001');
        $simOrder->setSimulated(true);
        $simOrder->save();

        $realOrder = new BotOrder();
        $realOrder->setIdGridRun((int) $this->run->getIdGridRun());
        $realOrder->setClientOrderId('real-' . bin2hex(random_bytes(4)));
        $realOrder->setLevelIdx(4);
        $realOrder->setSide('Buy');
        $realOrder->setState('BUY_OPEN');
        $realOrder->setPrice('58000');
        $realOrder->setQty('0.001');
        $realOrder->setSimulated(false);
        $realOrder->save();
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function session(): AuthySession
    {
        return $this->createMock(AuthySession::class);
    }

    private function decode(array $r): array
    {
        return json_decode($r['content'][0]['text'], true);
    }

    public function testDashboardKpisFollowTheSwitch(): void
    {
        $this->run->setSimulated(true)->save();
        $kpis = (new DashboardData($this->run))->kpis();
        $this->assertSame(0, bccomp('10', $kpis['realized_pnl'], 8));
        $this->assertSame(1, $kpis['cycles']);
        $this->assertSame(1, $kpis['open_buys']);

        $this->run->setSimulated(false)->save();
        $kpis = (new DashboardData($this->run))->kpis();
        $this->assertSame(0, bccomp('20', $kpis['realized_pnl'], 8));
        $this->assertSame(1, $kpis['cycles']);
        $this->assertSame(1, $kpis['open_buys']);
    }

    public function testPnlReportModeArgSelectsStatSet(): void
    {
        $tool = new GtbotPnlReportTool();
        $runId = (int) $this->run->getIdGridRun();

        $sim = $this->decode($tool->handle(['run' => $runId, 'mode' => 'sim'], $this->session()));
        $this->assertSame(0, bccomp('10', $sim['totals']['realized_pnl'], 8));
        $this->assertSame(1, $sim['totals']['cycles']);
        $this->assertSame('sim', $sim['mode']);

        $real = $this->decode($tool->handle(['run' => $runId, 'mode' => 'real'], $this->session()));
        $this->assertSame(0, bccomp('20', $real['totals']['realized_pnl'], 8));
        $this->assertSame(1, $real['totals']['cycles']);
        $this->assertSame('real', $real['mode']);

        $all = $this->decode($tool->handle(['run' => $runId, 'mode' => 'all'], $this->session()));
        $this->assertSame(0, bccomp('30', $all['totals']['realized_pnl'], 8));
        $this->assertSame(2, $all['totals']['cycles']);
        $this->assertSame('all', $all['mode']);
    }

    public function testPnlReportDefaultModeFollowsRun(): void
    {
        $tool = new GtbotPnlReportTool();
        $runId = (int) $this->run->getIdGridRun();

        $this->run->setSimulated(true)->save();
        $default = $this->decode($tool->handle(['run' => $runId], $this->session()));
        $this->assertSame(0, bccomp('10', $default['totals']['realized_pnl'], 8));
        $this->assertSame('sim', $default['mode']);

        $this->run->setSimulated(false)->save();
        $default = $this->decode($tool->handle(['run' => $runId], $this->session()));
        $this->assertSame(0, bccomp('20', $default['totals']['realized_pnl'], 8));
        $this->assertSame('real', $default['mode']);
    }

    public function testStatusReportsModeAndFollowsSwitch(): void
    {
        $tool = new GtbotStatusTool();
        $runId = (int) $this->run->getIdGridRun();

        $this->run->setSimulated(true)->save();
        $out = $this->decode($tool->handle(['run' => $runId], $this->session()));
        $this->assertSame('simulated', $out['mode']);
        $this->assertSame(0, bccomp('10', $out['realized_pnl_total'], 8));
        $this->assertSame(1, $out['orders']['open_buys']);
        $this->assertSame(1, $out['cycles']);

        $this->run->setSimulated(false)->save();
        $out = $this->decode($tool->handle(['run' => $runId], $this->session()));
        $this->assertSame('real', $out['mode']);
        $this->assertSame(0, bccomp('20', $out['realized_pnl_total'], 8));
        $this->assertSame(1, $out['orders']['open_buys']);
        $this->assertSame(1, $out['cycles']);
    }
}
