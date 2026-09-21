<?php

namespace Tests\Custom\Bot;

use ApiGoat\Sessions\AuthySession;
use App\BotCommandQuery;
use App\GridRun;
use App\Mcp\Tools\GtbotKillTool;
use App\Mcp\Tools\GtbotSetGridTool;
use App\Mcp\Tools\GtbotStatusTool;
use App\Mcp\Tools\GtbotStopTool;
use PHPUnit\Framework\TestCase;

class GtbotControlToolsTest extends TestCase
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
        $r->setLabel('tooltest-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualBase');
        $r->setBudgetQuote('550');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid('tooltest');
        $r->save();
        $this->run = $r;
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    /** A second run with an explicit profile, for the flatten/profile-policy tests. */
    private function makeRun(string $profile): GridRun
    {
        $r = new GridRun();
        $r->setLabel('tooltest-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualBase');
        $r->setBudgetQuote('550');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid('tooltest-' . bin2hex(random_bytes(3)));
        $r->setProfile($profile);
        $r->save();
        return $r;
    }

    private function session(): AuthySession
    {
        return $this->createMock(AuthySession::class);
    }

    private function decode(array $result): array
    {
        return json_decode($result['content'][0]['text'], true);
    }

    public function testStatusReportsRunAndStaleHeartbeat(): void
    {
        $out = $this->decode((new GtbotStatusTool())->handle(
            ['run' => (int) $this->run->getIdGridRun()],
            $this->session()
        ));
        $this->assertSame((string) $this->run->getLabel(), $out['run']['label']);
        $this->assertFalse($out['run']['kill_switch']);
        $this->assertTrue($out['heartbeat']['stale'], 'never-ticked run must read as stale');
        $this->assertSame(0, $out['cycles']);
    }

    public function testStopWithoutConfirmEnqueuesNothing(): void
    {
        $out = $this->decode((new GtbotStopTool())->handle(
            ['run' => (int) $this->run->getIdGridRun()],
            $this->session()
        ));
        $this->assertTrue($out['pending']);
        $this->assertSame(0, BotCommandQuery::create()->filterByIdGridRun((int) $this->run->getIdGridRun())->count());
    }

    public function testKillWithConfirmSetsSwitchAndEnqueues(): void
    {
        $out = $this->decode((new GtbotKillTool())->handle(
            ['run' => (int) $this->run->getIdGridRun(), 'confirm' => true, 'reason' => 'test: loss rule tripped'],
            $this->session()
        ));
        $this->assertTrue($out['kill_switch']);
        $this->run->reload();
        $this->assertTrue((bool) $this->run->getKillSwitch());
        $cmd = BotCommandQuery::create()->filterByIdGridRun((int) $this->run->getIdGridRun())->findOne();
        $this->assertSame('Kill', (string) $cmd->getCommand());
        $this->assertSame('Pending', (string) $cmd->getCmdStatus());
        $this->assertStringContainsString('loss rule tripped', (string) $cmd->getNote());
        // the justification is persisted to the event feed (auditable kill)
        $e = \App\BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('kill_requested')
            ->findOne();
        $this->assertNotNull($e);
        $this->assertSame('Alert', (string) $e->getLevel());
        $this->assertSame('test: loss rule tripped', (string) $e->getMessage());
    }

    public function testKillWithConfirmButNoReasonIsRefused(): void
    {
        $this->expectException(\ApiGoat\Mcp\ToolError::class);
        try {
            (new GtbotKillTool())->handle(
                ['run' => (int) $this->run->getIdGridRun(), 'confirm' => true],
                $this->session()
            );
        } finally {
            $this->run->reload();
            $this->assertFalse((bool) $this->run->getKillSwitch(), 'an unjustified kill must change nothing');
            $this->assertSame(0, BotCommandQuery::create()->filterByIdGridRun((int) $this->run->getIdGridRun())->count());
        }
    }

    public function testKillWithoutFlattenNeverFlattens(): void
    {
        $this->decode((new GtbotKillTool())->handle(
            ['run' => (int) $this->run->getIdGridRun(), 'confirm' => true, 'reason' => 'test'],
            $this->session()
        ));
        $flatten = BotCommandQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByCommand('Flatten')
            ->count();
        $this->assertSame(0, $flatten);
    }

    public function testFlattenIsRefusedForNoLossEvenWithConfirm(): void
    {
        $run = $this->makeRun('NoLoss');
        $this->expectException(\ApiGoat\Mcp\ToolError::class);
        try {
            (new GtbotKillTool())->handle(
                ['run' => (int) $run->getIdGridRun(), 'flatten' => true, 'confirm' => true, 'reason' => 'test: nolos flatten attempt'],
                $this->session()
            );
        } finally {
            $run->reload();
            $this->assertFalse((bool) $run->getKillSwitch(), 'a refused flatten must change nothing');
            $this->assertSame(0, BotCommandQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->count());
        }
    }

    public function testFlattenIsRefusedForNoLossEvenWithoutConfirm(): void
    {
        // the profile guard sits BEFORE the confirm/preview gate — a flatten
        // request on a NoLoss run is refused outright, it never even reaches
        // the "here's what would happen, confirm?" preview
        $run = $this->makeRun('NoLoss');
        $this->expectException(\ApiGoat\Mcp\ToolError::class);
        try {
            (new GtbotKillTool())->handle(
                ['run' => (int) $run->getIdGridRun(), 'flatten' => true],
                $this->session()
            );
        } finally {
            $run->reload();
            $this->assertFalse((bool) $run->getKillSwitch());
            $this->assertSame(0, BotCommandQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->count());
        }
    }

    public function testPlainKillStillAllowedForNoLoss(): void
    {
        // NoLoss forbids realizing losses (flatten), not the emergency stop
        // itself — a plain kill (hold inventory) must proceed normally
        $run = $this->makeRun('NoLoss');
        $out = $this->decode((new GtbotKillTool())->handle(
            ['run' => (int) $run->getIdGridRun(), 'confirm' => true, 'reason' => 'test: nolos plain kill'],
            $this->session()
        ));
        $this->assertTrue($out['kill_switch']);
        $run->reload();
        $this->assertTrue((bool) $run->getKillSwitch());
        $cmd = BotCommandQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->findOne();
        $this->assertSame('Kill', (string) $cmd->getCommand());
        $this->assertSame('Pending', (string) $cmd->getCmdStatus());
    }

    /** Filled buy giving the run a cost basis the flatten gate can price. */
    private function giveInventory(GridRun $run, string $price, string $qty): void
    {
        $row = new \App\BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId('inv-' . bin2hex(random_bytes(4)));
        $row->setLevelIdx(0);
        $row->setSide('Buy');
        $row->setState('Filled');
        $row->setPrice($price);
        $row->setQty($qty);
        $row->setFilledQty($qty);
        $row->setSimulated((bool) $run->getSimulated());
        $row->save();
    }

    public function testFlattenIsRefusedWhenSellAtLossIsOffAndUnderwater(): void
    {
        // the run switch (default OFF) gates flatten independently of profile —
        // but only for a liquidation that would actually realize a loss
        $run = $this->makeRun('Balanced');
        $this->giveInventory($run, '100', '1');
        $run->setLastPrice('90');
        $run->save();
        $this->expectException(\ApiGoat\Mcp\ToolError::class);
        try {
            (new GtbotKillTool())->handle(
                ['run' => (int) $run->getIdGridRun(), 'flatten' => true, 'confirm' => true, 'reason' => 'test: switch-off flatten attempt'],
                $this->session()
            );
        } finally {
            $run->reload();
            $this->assertFalse((bool) $run->getKillSwitch(), 'a refused flatten must change nothing');
            $this->assertSame(0, BotCommandQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->count());
        }
    }

    public function testFlattenIsAllowedAboveBreakevenWithSellAtLossOff(): void
    {
        // the switch forbids realizing losses, not gains: a liquidation above
        // the position's breakeven is a profit and proceeds (prod run 8,
        // 2026-09-18 — refused at +1.85 USDT before this gate learned to look)
        $run = $this->makeRun('Balanced');
        $this->giveInventory($run, '100', '1');
        $run->setLastPrice('120');
        $run->save();
        $out = $this->decode((new GtbotKillTool())->handle(
            ['run' => (int) $run->getIdGridRun(), 'flatten' => true, 'confirm' => true, 'reason' => 'test: profitable flatten with the switch off'],
            $this->session()
        ));
        $this->assertTrue($out['kill_switch']);
        $cmd = BotCommandQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->findOne();
        $this->assertSame('Flatten', (string) $cmd->getCommand());
    }

    public function testFlattenIsAllowedOnAFlatRunWithSellAtLossOff(): void
    {
        // nothing held: the liquidation realizes nothing, so there is no loss
        // to forbid
        $run = $this->makeRun('Balanced');
        $out = $this->decode((new GtbotKillTool())->handle(
            ['run' => (int) $run->getIdGridRun(), 'flatten' => true, 'confirm' => true, 'reason' => 'test: flatten a flat run'],
            $this->session()
        ));
        $this->assertTrue($out['kill_switch']);
    }

    public function testNoLossProfileStillRefusesAProfitableFlatten(): void
    {
        // profile NoLoss is a stronger rule than the run switch: it forbids
        // the daemon ever liquidating on command, gain or not
        $run = $this->makeRun('NoLoss');
        $this->giveInventory($run, '100', '1');
        $run->setLastPrice('120');
        $run->save();
        $this->expectException(\ApiGoat\Mcp\ToolError::class);
        try {
            (new GtbotKillTool())->handle(
                ['run' => (int) $run->getIdGridRun(), 'flatten' => true, 'confirm' => true, 'reason' => 'test: nolos profitable flatten'],
                $this->session()
            );
        } finally {
            $run->reload();
            $this->assertFalse((bool) $run->getKillSwitch());
        }
    }

    public function testFlattenIsAllowedForBalancedProfile(): void
    {
        // positive control: the profile guard only blocks NoLoss — a
        // Balanced run with sell_at_loss ON must be allowed/enqueued as usual
        $run = $this->makeRun('Balanced');
        $run->setSellAtLoss(true);
        $run->save();
        $out = $this->decode((new GtbotKillTool())->handle(
            ['run' => (int) $run->getIdGridRun(), 'flatten' => true, 'confirm' => true, 'reason' => 'test: balanced flatten positive control'],
            $this->session()
        ));
        $this->assertTrue($out['kill_switch']);
        $run->reload();
        $this->assertTrue((bool) $run->getKillSwitch());
        $cmd = BotCommandQuery::create()->filterByIdGridRun((int) $run->getIdGridRun())->findOne();
        $this->assertSame('Flatten', (string) $cmd->getCommand());
        $this->assertSame('Pending', (string) $cmd->getCmdStatus());
    }

    public function testStatusPrefersRunningRunOverLaterDraft(): void
    {
        // a later Draft (higher id) must NOT shadow the running Testnet run
        $draft = new GridRun();
        $draft->setLabel('refit-draft-' . bin2hex(random_bytes(4)));
        $draft->setSymbol('BTCUSDT');
        $draft->setStatus('Draft');
        $draft->setPLow('50000');
        $draft->setPHigh('70000');
        $draft->setNLevels(20);
        $draft->setSpacing('Geometric');
        $draft->setAllocation('EqualQuote');
        $draft->setBudgetQuote('1000');
        $draft->setFeePct('0.001');
        $draft->setMaxPositionQuote('1000');
        $draft->setMaxOrderQuote('100');
        $draft->setDailyLossLimitQuote('50');
        $draft->setBreakoutBufferPct('0.02');
        $draft->setBreakoutPolicy('HaltAndHold');
        $draft->setMaxOpenOrders(60);
        $draft->setRunUid('draftx');
        $draft->save();
        $this->assertGreaterThan((int) $this->run->getIdGridRun(), (int) $draft->getIdGridRun());

        // no run arg → resolves to the running Testnet run, not the newer Draft
        $out = $this->decode((new GtbotStatusTool())->handle([], $this->session()));
        $this->assertSame((int) $this->run->getIdGridRun(), $out['run']['id']);
        $this->assertSame('Testnet', $out['run']['status']);
    }

    public function testStatusReportsSellAtLossSwitch(): void
    {
        $out = $this->decode((new GtbotStatusTool())->handle(['run' => (int) $this->run->getIdGridRun()], $this->session()));
        $this->assertFalse($out['run']['sell_at_loss'], 'default OFF');
        $this->run->setSellAtLoss(true);
        $this->run->save();
        $out = $this->decode((new GtbotStatusTool())->handle(['run' => (int) $this->run->getIdGridRun()], $this->session()));
        $this->assertTrue($out['run']['sell_at_loss']);
    }

    public function testStatusReportsAlgoAndOmitsEngineBlockForGridRuns(): void
    {
        $out = $this->decode((new GtbotStatusTool())->handle(
            ['run' => (int) $this->run->getIdGridRun()],
            $this->session()
        ));
        $this->assertSame('Grid', $out['run']['algo']);
        $this->assertArrayNotHasKey('engine', $out, 'a Grid run has no engine state block to report');
    }

    public function testStatusReportsAlgoAndEngineStateForTrendRuns(): void
    {
        $run = $this->makeRun('Balanced');
        $run->setAlgo('Trend');
        $run->setEngineState(json_encode(['algo' => 'Trend', 'entry' => '65000', 'qty' => '0.01', 'stop' => '63000']));
        $run->save();

        $out = $this->decode((new GtbotStatusTool())->handle(
            ['run' => (int) $run->getIdGridRun()],
            $this->session()
        ));
        $this->assertSame('Trend', $out['run']['algo']);
        $this->assertArrayHasKey('engine', $out);
        $this->assertSame('65000', (string) $out['engine']['entry']);
        $this->assertSame('0.01', (string) $out['engine']['qty']);
    }

    public function testSetGridRefusesATrendRun(): void
    {
        $run = $this->makeRun('Balanced');
        $run->setAlgo('Trend');
        $run->save();

        $this->expectException(\ApiGoat\Mcp\ToolError::class);
        try {
            (new GtbotSetGridTool('66000'))->handle(
                ['run' => (int) $run->getIdGridRun(), 'p_low' => '60000', 'p_high' => '70000', 'n_levels' => 10, 'reason' => 'test'],
                $this->session()
            );
        } finally {
            $run->reload();
            $this->assertSame(0, bccomp((string) $run->getPLow(), '100', 2), 'a refused set_grid on a Trend run must not touch geometry');
        }
    }

    public function testMcpCallIsLoggedToApiLog(): void
    {
        $before = \App\ApiLogQuery::create()->count();
        (new GtbotStatusTool())->handle(
            ['run' => (int) $this->run->getIdGridRun()],
            $this->session()
        );
        $this->assertSame($before + 1, \App\ApiLogQuery::create()->count(), 'each MCP tool call writes one api_log row');
        // and an api_rbac catalog row for the tool exists
        $rbac = \App\ApiRbacQuery::create()
            ->filterByModel('gtbot_status')
            ->filterByAction('mcp')
            ->findOne();
        $this->assertNotNull($rbac, 'api_rbac catalog row created for the tool');
        $log = \App\ApiLogQuery::create()->orderByIdApiLog(\Criteria::DESC)->findOne();
        $this->assertSame((int) $rbac->getIdApiRbac(), (int) $log->getIdApiRbac());
    }
}
