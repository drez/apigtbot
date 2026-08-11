<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\Engine\TrendEngine;
use PHPUnit\Framework\TestCase;

/**
 * The TrendEngine decision core, unit-tested in isolation from the daemon
 * shell: breakout entry signal, tranche sizing, ATR trailing-stop ratchet,
 * re-entry cooldown. Daemon-level wiring (fills, cycle booking, cutover) is
 * covered in EngineSwitchTest.
 */
class TrendEngineTest extends TestCase
{
    public function testEntrySignalRequiresBreakoutAndEmaAlignment(): void
    {
        // The guard requires n >= max(donchian+1, emaSlow): below emaSlow bars
        // Indicators::ema() degrades to a plain (non-recency-weighted) average,
        // which can invert the fast/slow comparison relative to the real trend
        // — so the fixture needs the full emaSlow=50 window, not just the
        // donchian=20 one, before the "true" case is a genuine signal.
        $flat = array_fill(0, 45, ['high' => '101', 'low' => '99', 'close' => '100']);
        $this->assertFalse(TrendEngine::entrySignal($flat, 20, 20, 50));
        $rising = $flat;
        for ($i = 0; $i < 15; $i++) { $c = (string) (101 + $i * 2); $rising[] = ['high' => $c, 'low' => (string)((float)$c - 1), 'close' => $c]; }
        $this->assertSame(60, count($rising));
        $this->assertTrue(TrendEngine::entrySignal($rising, 20, 20, 50), 'close above 20-bar prior high with fast EMA over slow, both fully-weighted at 60 bars');
    }

    /**
     * I4 (task-4 review): below emaSlow bars, Indicators::ema() degrades to a
     * plain (non-recency-weighted) average — comparing that against a genuine
     * fast EMA can invert relative to the real trend. The guard refuses ANY
     * signal short of the full emaSlow window categorically, rather than
     * trying to judge case by case whether a given under-windowed comparison
     * happens to be trustworthy. Pins the boundary directly: the exact same
     * 40-bar breakout fixture that reads a spurious TRUE under a donchian-only
     * guard (n=40 >= donchian+1=21) must read FALSE once emaSlow=50 is also
     * required — the donchian breakout condition alone is not enough.
     */
    public function testEntrySignalRefusesBelowTheFullSlowEmaWindow(): void
    {
        $flat = array_fill(0, 30, ['high' => '101', 'low' => '99', 'close' => '100']);
        $rising = $flat;
        for ($i = 0; $i < 10; $i++) { $c = (string) (101 + $i * 2); $rising[] = ['high' => $c, 'low' => (string)((float)$c - 1), 'close' => $c]; }
        $this->assertSame(40, count($rising));
        // the donchian(20) breakout condition alone is satisfied at 40 bars...
        $this->assertTrue(TrendEngine::entrySignal($rising, 20, 20, 30), 'sanity: with emaSlow=30 (<=40 bars) the same fixture DOES signal');
        // ...but the full emaSlow=50 window is not, and the guard refuses
        // categorically rather than trusting a degraded (SMA) slow average
        $this->assertFalse(TrendEngine::entrySignal($rising, 20, 20, 50));
    }

    public function testTrancheSplitRespectsPerOrderCap(): void
    {
        // 400 budget, deploy 100 => 400 quote, cap 120 => 4 tranches of 100
        $t = TrendEngine::tranches('400', 100, '120.00000000');
        $this->assertCount(4, $t);
        foreach ($t as $q) { $this->assertLessThanOrEqual(0, bccomp($q, '120.00000000', 8)); }
        $this->assertSame(0, bccomp(array_reduce($t, fn($c, $q) => bcadd($c, $q, 8), '0'), '400', 8));
        // deploy 30 => 120 quote, single tranche
        $this->assertCount(1, TrendEngine::tranches('400', 30, '120.00000000'));
        // deploy 0 => no tranches
        $this->assertSame([], TrendEngine::tranches('400', 0, '120.00000000'));
    }

    public function testStopRatchetsUpOnly(): void
    {
        $state = ['entry' => '100', 'hwm' => '100', 'stop' => '94.0'];  // initial: 100 - 2.0×ATR(3)
        $s1 = TrendEngine::ratchet($state, '110', '3.0000', '3');       // price 110, stop_mult 3, ATR 3
        $this->assertSame(0, bccomp($s1['stop'], '101', 8));            // 110 - 9
        $s2 = TrendEngine::ratchet($s1, '105', '3.0000', '3');
        $this->assertSame(0, bccomp($s2['stop'], '101', 8), 'stop never moves down');
        $this->assertSame(0, bccomp($s2['hwm'], '110', 8));
    }

    public function testCooldownBlocksReentry(): void
    {
        $this->assertFalse(TrendEngine::cooldownElapsed('2026-08-10 10:00:00', 3, '1h', '2026-08-10 12:59:00'));
        $this->assertTrue(TrendEngine::cooldownElapsed('2026-08-10 10:00:00', 3, '1h', '2026-08-10 13:00:01'));
        $this->assertTrue(TrendEngine::cooldownElapsed(null, 3, '1h', '2026-08-10 13:00:01'), 'no prior stop-out');
        $this->assertTrue(TrendEngine::cooldownElapsed('2026-08-10 10:00:00', 0, '1h', '2026-08-10 10:00:05'), 'Max profile: 0 bars');
    }
}
