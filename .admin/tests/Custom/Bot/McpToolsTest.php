<?php

namespace Tests\Custom\Bot;

use ApiGoat\Sessions\AuthySession;
use App\Mcp\Tools\GtbotBacktestTool;
use App\Mcp\Tools\GtbotPreviewGridTool;
use PHPUnit\Framework\TestCase;

class McpToolsTest extends TestCase
{
    private function session(): AuthySession
    {
        return $this->createMock(AuthySession::class);
    }

    private function decode(array $result): array
    {
        $this->assertFalse($result['isError']);
        return json_decode($result['content'][0]['text'], true);
    }

    public function testPreviewGridReturnsLevelsAndVerdict(): void
    {
        $tool = new GtbotPreviewGridTool();
        $this->assertSame('gtbot_preview_grid', $tool->name());
        $out = $this->decode($tool->handle([
            'p_low' => '100', 'p_high' => '200', 'n_levels' => 4,
            'spacing' => 'Arithmetic', 'budget_quote' => '1000',
        ], $this->session()));
        $this->assertTrue($out['viable']);
        $this->assertCount(5, $out['levels']);
        $this->assertCount(4, $out['buy_level_qtys']);
        $this->assertGreaterThan(0, (float) $out['profit_per_grid_pct']);
    }

    public function testPreviewGridReportsFeeNegativeConfig(): void
    {
        $tool = new GtbotPreviewGridTool();
        $out = $this->decode($tool->handle([
            'p_low' => '100', 'p_high' => '200', 'n_levels' => 500,
            'budget_quote' => '100000', 'fee_pct' => '0.001',
        ], $this->session()));
        $this->assertFalse($out['viable']);
        $this->assertNotEmpty($out['config_errors']);
    }

    public function testBacktestToolRunsEndToEnd(): void
    {
        $tool = new GtbotBacktestTool();
        $out = $this->decode($tool->handle([
            'p_low' => '100', 'p_high' => '200', 'n_levels' => 4,
            'spacing' => 'Arithmetic', 'allocation' => 'EqualBase',
            'budget_quote' => '550', 'series' => ['150', 120, '150'],
        ], $this->session()));
        $this->assertSame(1, $out['cycles']);
        $this->assertSame(0, bccomp($out['realized_pnl'], '24.725', 8));
    }

    public function testBacktestToolRejectsEmptySeries(): void
    {
        $this->expectException(\ApiGoat\Mcp\ToolError::class);
        (new GtbotBacktestTool())->handle([
            'p_low' => '100', 'p_high' => '200', 'n_levels' => 4, 'budget_quote' => '550',
            'series' => [],
        ], $this->session());
    }
}
