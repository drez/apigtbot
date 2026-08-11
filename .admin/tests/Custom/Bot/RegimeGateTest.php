<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\RegimeGate;
use PHPUnit\Framework\TestCase;

/**
 * Sweep-validated hostile-regime deploy cap (scripts/regime-sweep.php,
 * BTC+BNB 187d): fit-time "4h trend down-family OR ADX>=30" was the only
 * gate that improved mean net AND slashed the worst window on BOTH symbols
 * (BTC −112.75→−25.03, BNB −66.44→−7.92). The gate caps deploy_pct at 25
 * in hostile regime; it never raises exposure and fails OPEN on missing
 * signal (sweep semantics: null = trade).
 */
class RegimeGateTest extends TestCase
{
    // ── hostile() ───────────────────────────────────────────────────────

    public function testDownTrendIsHostile(): void
    {
        $this->assertTrue(RegimeGate::hostile('down', 12.0));
        $this->assertTrue(RegimeGate::hostile('strong_down', 12.0));
    }

    public function testHighAdxIsHostileRegardlessOfTrend(): void
    {
        $this->assertTrue(RegimeGate::hostile('up', 30.0));
        $this->assertTrue(RegimeGate::hostile('sideways', 45.0));
    }

    public function testCalmSidewaysOrUpIsFriendly(): void
    {
        $this->assertFalse(RegimeGate::hostile('sideways', 15.0));
        $this->assertFalse(RegimeGate::hostile('up', 29.9));
        $this->assertFalse(RegimeGate::hostile('strong_up', 10.0));
    }

    public function testMissingSignalsFailOpen(): void
    {
        $this->assertFalse(RegimeGate::hostile(null, null));
        $this->assertFalse(RegimeGate::hostile('sideways', null));
        $this->assertTrue(RegimeGate::hostile(null, 35.0), 'adx alone still gates when trend is missing');
    }

    // ── capDeployPct() ──────────────────────────────────────────────────

    public function testHostileRegimeCapsAt25(): void
    {
        $r = RegimeGate::capDeployPct(100, 'down', 20.0);
        $this->assertSame(25, $r['pct']);
        $this->assertTrue($r['capped']);
        $this->assertStringContainsString('down', (string) $r['why']);
    }

    public function testFriendlyRegimeLeavesRequestUntouched(): void
    {
        $r = RegimeGate::capDeployPct(100, 'sideways', 18.0);
        $this->assertSame(100, $r['pct']);
        $this->assertFalse($r['capped']);
        $this->assertNull($r['why']);
    }

    public function testNeverRaisesExposure(): void
    {
        // explicit 0 (flat) and low requests pass through even when hostile
        $this->assertSame(0, RegimeGate::capDeployPct(0, 'down', 40.0)['pct']);
        $this->assertSame(10, RegimeGate::capDeployPct(10, 'strong_down', 40.0)['pct']);
        $this->assertSame(25, RegimeGate::capDeployPct(25, 'down', 40.0)['pct']);
        $this->assertFalse(RegimeGate::capDeployPct(25, 'down', 40.0)['capped'], 'at the cap is not "capped"');
    }

    public function testMissingSignalsNeverCap(): void
    {
        $r = RegimeGate::capDeployPct(100, null, null);
        $this->assertSame(100, $r['pct']);
        $this->assertFalse($r['capped']);
    }
}
