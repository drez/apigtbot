<?php

namespace Tests\Custom\Bot;

use App\Domains\Dashboard\DashboardRenderer;
use PHPUnit\Framework\TestCase;

class DashboardTimeTest extends TestCase
{
    private function vm(string $eventAt): array
    {
        return [
            'baseUrl' => 'https://x.test/',
            'run' => [
                'id' => 1, 'label' => 'r', 'symbol' => 'BTCUSDT', 'status' => 'Testnet',
                'kill_switch' => false, 'heartbeat_stale' => false,
                'last_tick_at' => date('Y-m-d H:i:s'), 'p_low' => '60000', 'p_high' => '72000',
            ],
            'kpis' => [],
            'points' => [],
            'cycles' => [],
            'events' => [['level' => 'Info', 'kind' => 'buy_fill', 'message' => 'x', 'at' => $eventAt]],
            'daily' => [],
        ];
    }

    public function testRelativeTimeIsHumanReadable(): void
    {
        $html = (new DashboardRenderer())->render($this->vm(date('Y-m-d H:i:s', time() - 7200)));
        $this->assertStringContainsString('2h ago', $html);
    }

    public function testMinutesAgo(): void
    {
        $html = (new DashboardRenderer())->render($this->vm(date('Y-m-d H:i:s', time() - 300)));
        $this->assertStringContainsString('5m ago', $html);
    }

    public function testJustNow(): void
    {
        $html = (new DashboardRenderer())->render($this->vm(date('Y-m-d H:i:s')));
        $this->assertStringContainsString('just now', $html);
    }

    public function testAbsoluteDatetimeKeptAsHoverTitle(): void
    {
        $abs = date('Y-m-d H:i:s', time() - 7200);
        $html = (new DashboardRenderer())->render($this->vm($abs));
        // the exact datetime is preserved in a title attribute for hover
        $this->assertStringContainsString('title="' . $abs . '"', $html);
    }
}
