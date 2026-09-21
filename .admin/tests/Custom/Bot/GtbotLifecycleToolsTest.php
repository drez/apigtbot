<?php

namespace Tests\Custom\Bot;

use ApiGoat\Mcp\ToolError;
use ApiGoat\Sessions\AuthySession;
use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\BudgetGuard;
use App\GridRun;
use App\GridRunQuery;
use App\Mcp\Tools\GtbotAllocModeTool;
use App\Mcp\Tools\GtbotBudgetTool;
use App\Mcp\Tools\GtbotPurgeRunTool;
use App\Mcp\Tools\GtbotRetireRunTool;
use Tests\Builder\Support\DbTestCase;

/**
 * The lifecycle and allocation MCP tools.
 *
 * Both lifecycle tools deliberately REQUIRE `run`: AbstractGtbotBase::resolveRun()
 * otherwise falls back to "the latest non-Done run", and for a destructive
 * action a forgotten argument would retire or delete the wrong one.
 */
class GtbotLifecycleToolsTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (GridRunQuery::create()->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)->find() as $r) {
            $r->setStatus('Halted');
            $r->save();
        }
        $this->setConfig('gtbot_shared_budget_quote', '1000');
        $this->setConfig('gtbot_use_all_funds', '0');
        $this->setConfig('gtbot_max_drawdown_pct', '0');
    }

    private function setConfig(string $key, string $value): void
    {
        \App\ConfigPeer::clearInstancePool();
        $c = ConfigQuery::create()->findOneByConfig($key) ?? (new Config())->setConfig($key);
        $c->setValue($value);
        $c->save();
        \App\ConfigPeer::clearInstancePool();
    }

    private function session(): AuthySession
    {
        return $this->createMock(AuthySession::class);
    }

    private function decode(array $result): array
    {
        $this->assertFalse($result['isError'] ?? true, 'tool returned an error');
        return json_decode($result['content'][0]['text'], true);
    }

    private function mkRun(string $budget = '300'): GridRun
    {
        $r = new GridRun();
        $r->setLabel(static::uniq('tool'));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setAlgo('Grid');
        $r->setSimulated(true);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(5);
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote($budget);
        $r->setDeployPct(100);
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('300');
        $r->setMaxOrderQuote('100');
        $r->setDailyLossLimitQuote('30');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(30);
        $r->setRunUid(static::uniq('tool'));
        $r->setLastTickAt(date('Y-m-d H:i:s', time() - 3600));
        $r->setLastPrice('150');
        $r->save();
        return $r;
    }

    // ── retire ──────────────────────────────────────────────────────────

    public function testRetirePreviewWritesNothing(): void
    {
        $r = $this->mkRun();
        $out = $this->decode((new GtbotRetireRunTool())->handle(['run' => (int) $r->getIdGridRun()], $this->session()));

        $this->assertTrue($out['pending']);
        $this->assertSame('Done', $out['inspect']['lands_in'], 'a flat run would archive immediately');
        $this->assertSame('300', $out['frees']);
        $r->reload();
        $this->assertSame('Testnet', (string) $r->getStatus(), 'preview must not write');
    }

    public function testRetireRequiresRunExplicitly(): void
    {
        $this->mkRun();
        $this->expectException(ToolError::class);
        $this->expectExceptionMessageMatches('/run is required/');
        (new GtbotRetireRunTool())->handle([], $this->session());
    }

    public function testRetireRequiresAReasonWithConfirm(): void
    {
        $r = $this->mkRun();
        $this->expectException(ToolError::class);
        $this->expectExceptionMessageMatches('/reason is required/');
        (new GtbotRetireRunTool())->handle(['run' => (int) $r->getIdGridRun(), 'confirm' => true], $this->session());
    }

    public function testRetireRejectsAnUnknownExit(): void
    {
        $r = $this->mkRun();
        $this->expectException(ToolError::class);
        (new GtbotRetireRunTool())->handle(['run' => (int) $r->getIdGridRun(), 'exit' => 'yolo'], $this->session());
    }

    public function testRetireArchivesAFlatRun(): void
    {
        $r = $this->mkRun();
        $out = $this->decode((new GtbotRetireRunTool())->handle([
            'run' => (int) $r->getIdGridRun(),
            'reason' => 'experiment over',
            'confirm' => true,
        ], $this->session()));

        $this->assertTrue($out['ok']);
        $r->reload();
        $this->assertSame('Done', (string) $r->getStatus());
    }

    // ── purge ───────────────────────────────────────────────────────────

    public function testPurgePreviewNamesRowCountsAndDeletesNothing(): void
    {
        $r = $this->mkRun();
        (new GtbotRetireRunTool())->handle([
            'run' => (int) $r->getIdGridRun(), 'reason' => 'archived', 'confirm' => true,
        ], $this->session());

        $id = (int) $r->getIdGridRun();
        $out = $this->decode((new GtbotPurgeRunTool())->handle(['run' => $id], $this->session()));

        $this->assertTrue($out['pending']);
        $this->assertArrayHasKey('bot_order', $out['deleted']);
        $this->assertNotNull(GridRunQuery::create()->findPk($id), 'preview must delete nothing');
    }

    public function testPurgeRefusesWithoutTheDataLossAcknowledgement(): void
    {
        $r = $this->mkRun();
        (new GtbotRetireRunTool())->handle([
            'run' => (int) $r->getIdGridRun(), 'reason' => 'archived', 'confirm' => true,
        ], $this->session());
        $id = (int) $r->getIdGridRun();

        $this->expectException(ToolError::class);
        $this->expectExceptionMessageMatches('/confirm_data_loss/');
        (new GtbotPurgeRunTool())->handle(['run' => $id, 'reason' => 'cleanup', 'confirm' => true], $this->session());
    }

    public function testPurgeRefusesARunThatIsNotArchived(): void
    {
        $r = $this->mkRun();
        $this->expectException(ToolError::class);
        $this->expectExceptionMessageMatches('/archive it/');
        (new GtbotPurgeRunTool())->handle([
            'run' => (int) $r->getIdGridRun(), 'reason' => 'x', 'confirm' => true, 'confirm_data_loss' => true,
        ], $this->session());
    }

    // ── budget / alloc mode ─────────────────────────────────────────────

    public function testBudgetReadReturnsTheAllocationTable(): void
    {
        $a = $this->mkRun('300');
        $out = $this->decode((new GtbotBudgetTool())->handle([], $this->session()));

        $this->assertSame('1000', $out['cap']);
        $this->assertNotSame([], $out['runs']);
        $row = $out['runs'][0];
        foreach (['slice', 'floor', 'idle', 'alloc_mode', 'effective_mode', 'per_1k_day', 'eligible'] as $key) {
            $this->assertArrayHasKey($key, $row);
        }
    }

    public function testBudgetRefusesWhileTheCapFollowsTheWallet(): void
    {
        $this->setConfig('gtbot_use_all_funds', '1');
        $this->expectException(ToolError::class);
        $this->expectExceptionMessageMatches('/follows the wallet/');
        (new GtbotBudgetTool())->handle(['budget' => '1500'], $this->session());
    }

    public function testBudgetSetRequiresConfirmThenReallocates(): void
    {
        $a = $this->mkRun('300');
        $preview = $this->decode((new GtbotBudgetTool())->handle(['budget' => '800'], $this->session()));
        $this->assertTrue($preview['pending']);

        $out = $this->decode((new GtbotBudgetTool())->handle([
            'budget' => '800', 'reason' => 'tightening', 'confirm' => true,
        ], $this->session()));
        $this->assertTrue($out['ok']);
        $this->assertSame('800', $out['budget']);
    }

    public function testAllocModePinsAndReportsTheReason(): void
    {
        $r = $this->mkRun();
        $preview = $this->decode((new GtbotAllocModeTool())->handle([
            'run' => (int) $r->getIdGridRun(), 'mode' => 'Fixed',
        ], $this->session()));
        $this->assertTrue($preview['pending']);
        $r->reload();
        $this->assertSame('Auto', (string) $r->getAllocMode(), 'preview must not write');

        $out = $this->decode((new GtbotAllocModeTool())->handle([
            'run' => (int) $r->getIdGridRun(), 'mode' => 'Fixed', 'reason' => 'pinning it', 'confirm' => true,
        ], $this->session()));
        $this->assertTrue($out['ok']);
        $r->reload();
        $this->assertSame('Fixed', (string) $r->getAllocMode());
        $this->assertStringContainsString('pinned by the operator', $out['effective_mode']);
    }

    public function testAllocModeRejectsAnUnknownMode(): void
    {
        $r = $this->mkRun();
        $this->expectException(ToolError::class);
        (new GtbotAllocModeTool())->handle(['run' => (int) $r->getIdGridRun(), 'mode' => 'Whatever'], $this->session());
    }

    // ── the guard that catches a forgotten rbac line, forever ───────────

    public function testEveryDiscoveredToolIsDeclaredInGcMeta(): void
    {
        // tests/Custom/Bot -> .admin -> project root
        $root = dirname(__DIR__, 4);
        $metaPath = $root . '/.gc-meta.json';
        $this->assertFileExists($metaPath, 'without this the check would pass vacuously');

        $meta = json_decode((string) file_get_contents($metaPath), true);
        $allow = (array) ($meta['rbac']['allow'] ?? []);
        $this->assertNotSame([], $allow, 'rbac.allow should not be empty');

        $files = glob(dirname(__DIR__, 3) . '/src/App/Mcp/Tools/*Tool.php');
        $this->assertNotSame([], $files, 'no tools discovered — the glob path is wrong');

        $missing = [];
        foreach ($files as $file) {
            $class = '\\App\\Mcp\\Tools\\' . basename($file, '.php');
            if (!class_exists($class) || (new \ReflectionClass($class))->isAbstract()) {
                continue;
            }
            $name = (new $class())->name();
            if (!in_array($name . '/mcp/POST', $allow, true)) {
                $missing[] = $name;
            }
        }
        $this->assertSame([], $missing, 'tools missing an rbac.allow entry in .gc-meta.json');
    }
}
