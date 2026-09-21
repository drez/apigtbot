<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\TrendRegime;
use PHPUnit\Framework\TestCase;

/**
 * TREND_UP activation classifier (the allocation-sweep classification,
 * scripts/allocation-sweep.php::classify) with the routine's re-entry gate
 * as the 1d term: the 1d trend label is EMA200-anchored and reads
 * "strong_down" for months after a deep drawdown (BTC 2026-08-19: 4h
 * strong_up, 1h strong_up, 1d strong_down while price sat above the 1d
 * EMA20 AND EMA50), so "price reclaimed 1d EMA20 and EMA50" counts as
 * 1d-up for alignment purposes.
 */
class TrendRegimeTest extends TestCase
{
    private function s4(string $trend, float $adx, ?float $er = 0.5, ?float $chop = 40.0, bool $stale = false): array
    {
        return ['trend' => $trend, 'adx14' => $adx, 'er20' => $er, 'chop14' => $chop, 'stale' => $stale, 'price' => 100.0];
    }

    private function s1d(string $trend, float $price = 100.0, ?float $ema20 = 90.0, ?float $ema50 = 95.0, bool $stale = false, ?float $adx = null, ?float $er = null): array
    {
        return ['trend' => $trend, 'price' => $price, 'ema20' => $ema20, 'ema50' => $ema50, 'stale' => $stale, 'adx14' => $adx, 'er20' => $er];
    }

    /** s4 with the EMA pair the shallow-down rule reads. $sepPct is the
     *  EMA20-below-EMA50 separation as a fraction of EMA50. */
    private function s4Ema(string $trend, float $adx, float $sepPct, ?float $er = 0.4, ?float $chop = 40.0): array
    {
        $ema50 = 100000.0;
        $s = $this->s4($trend, $adx, $er, $chop);
        $s['ema50'] = $ema50;
        $s['ema20'] = $ema50 * (1 - $sepPct);
        return $s;
    }

    /**
     * A 4h "down" label is only a downtrend when the EMAs are actually
     * apart. Indicators::trend flips to 'down' at 0.1% of separation, and on
     * 2026-09-18 run 1 sat at 0.15% — EMA20 77196.39 vs EMA50 77315.84, 119
     * points — while the 1d ran strong_up at ADX 40.8 and price made 80k.
     * That hairline crossing read HOSTILE on every 15-minute pass, and one
     * HOSTILE pass deactivates the arm, so the BTC arm was parked through
     * the whole 77k → 80k leg on a rounding-scale difference.
     */
    public function testHairlineDownCrossIsNotHostileWhileTheDailyIsUp(): void
    {
        $s1dUp = $this->s1d('strong_up', 79992.0, 77168.0, 73950.0, false, 40.8, 0.08);

        // the prod case: 0.15% apart, daily strongly up → no longer HOSTILE
        $this->assertNotSame('HOSTILE', TrendRegime::classify($this->s4Ema('down', 25.4, 0.0015), $s1dUp));

        // a real 4h downtrend still is
        $this->assertSame('HOSTILE', TrendRegime::classify($this->s4Ema('down', 25.4, 0.02), $s1dUp));
    }

    public function testShallowDownStillHostileWithoutDailySupport(): void
    {
        // same hairline cross, but the daily is not up: nothing vouches for
        // the tape, so a down label is taken at face value
        $this->assertSame('HOSTILE', TrendRegime::classify(
            $this->s4Ema('down', 25.4, 0.0015),
            $this->s1d('down', 100.0, 110.0, 120.0, false, 40.0, 0.5)
        ));
    }

    public function testStrongDownLabelIsAlwaysDecisive(): void
    {
        // strong_down means EMA20 < EMA50 < EMA200 — the separation of the
        // fast pair is not the whole story, so the label wins outright
        $this->assertSame('HOSTILE', TrendRegime::classify(
            $this->s4Ema('strong_down', 20.0, 0.0015),
            $this->s1d('strong_up', 79992.0, 77168.0, 73950.0, false, 40.8, 0.08)
        ));
    }

    public function testShallowDownAtHostileAdxIsStillHostile(): void
    {
        // ADX >= 30 without an up-aligned 4h is its own HOSTILE clause and is
        // deliberately left alone: that much directional strength is real
        $this->assertSame('HOSTILE', TrendRegime::classify(
            $this->s4Ema('down', 31.0, 0.0015),
            $this->s1d('strong_up', 79992.0, 77168.0, 73950.0, false, 40.8, 0.08)
        ));
    }

    public function testDownWithoutEmaDataStaysHostile(): void
    {
        // summaries predating the ema columns (or a partial row) cannot prove
        // the cross is shallow — fail closed, exactly as before
        $this->assertSame('HOSTILE', TrendRegime::classify(
            $this->s4('down', 22.0, 0.12, 54.0),
            $this->s1d('strong_up', 79992.0, 77168.0, 73950.0, false, 40.8, 0.08)
        ));
    }

    public function testStrong1dTrendIsTrendUpDespiteWeak4h(): void
    {
        // BTC 2026-09-02 19:18: 4h strong_up adx 22.1 er 0.12 chop 54 — MIXED
        // on the 4h rule all day — while the 1d read up, ADX 44.2, ER 0.51,
        // 62.5k → 81.5k in the window. The daily is the timeframe the leg
        // lives on; a strong 1d trend satisfies the gate on its own.
        $s1dStrong = $this->s1d('up', 77395.84, 74657.33, 70301.36, false, 44.2, 0.51);
        $this->assertSame('TREND_UP', TrendRegime::classify($this->s4('strong_up', 22.1, 0.12, 54.0), $s1dStrong));
        // a sideways 4h (pullback bars inside the daily leg) does not veto it
        $this->assertSame('TREND_UP', TrendRegime::classify($this->s4('sideways', 18.0, 0.10, 56.0), $s1dStrong));
        // a down-family 4h is still HOSTILE — the 1d override never outranks it
        $this->assertSame('HOSTILE', TrendRegime::classify($this->s4('down', 22.0, 0.12, 54.0), $s1dStrong));
        // the 1d-up alignment term still applies (label or EMA reclaim)
        $this->assertNotSame('TREND_UP', TrendRegime::classify(
            $this->s4('sideways', 18.0, 0.10, 56.0),
            $this->s1d('down', 100.0, 110.0, 120.0, false, 44.2, 0.51)
        ));
        // both 1d gates are needed: ADX without ER, or ER without ADX, is not enough
        $this->assertSame('MIXED', TrendRegime::classify($this->s4('strong_up', 22.1, 0.12, 54.0), $this->s1d('up', 100.0, 90.0, 95.0, false, 44.2, 0.20)));
        $this->assertSame('MIXED', TrendRegime::classify($this->s4('strong_up', 22.1, 0.12, 54.0), $this->s1d('up', 100.0, 90.0, 95.0, false, 25.0, 0.51)));
        // missing 1d adx/er (older summaries) → no override, the 4h rule stands
        $this->assertSame('MIXED', TrendRegime::classify($this->s4('strong_up', 22.1, 0.12, 54.0), $this->s1d('up')));
    }

    public function testHoldingRelaxesThe1dGatesToo(): void
    {
        $s4 = $this->s4('strong_up', 22.1, 0.12, 54.0);
        $fading = $this->s1d('up', 100.0, 90.0, 95.0, false, 31.0, 0.22);
        $this->assertSame('MIXED', TrendRegime::classify($s4, $fading));
        $this->assertSame('TREND_UP', TrendRegime::classify($s4, $fading, true));
        // below the hold thresholds even an open arm reads MIXED
        $this->assertSame('MIXED', TrendRegime::classify($s4, $this->s1d('up', 100.0, 90.0, 95.0, false, 28.0, 0.22), true));
        $this->assertSame('MIXED', TrendRegime::classify($s4, $this->s1d('up', 100.0, 90.0, 95.0, false, 31.0, 0.15), true));
    }

    public function testAlignedUpWithAdxAndErIsTrendUp(): void
    {
        $this->assertSame('TREND_UP', TrendRegime::classify($this->s4('up', 26.0, 0.40), $this->s1d('up')));
        $this->assertSame('TREND_UP', TrendRegime::classify($this->s4('strong_up', 40.0, 0.90), $this->s1d('strong_up')));
    }

    public function testStale1dLabelIsOverriddenByEmaReclaim(): void
    {
        // 1d says strong_down but price is above BOTH the 1d EMA20 and EMA50
        $this->assertSame('TREND_UP', TrendRegime::classify(
            $this->s4('strong_up', 32.0, 0.60),
            $this->s1d('strong_down', 68700.0, 63972.0, 64386.0)
        ));
    }

    public function testEmaReclaimNeedsBothEmas(): void
    {
        // above EMA20 only — label stays authoritative → not aligned, and adx>=30 → HOSTILE
        $this->assertSame('HOSTILE', TrendRegime::classify(
            $this->s4('strong_up', 32.0, 0.60),
            $this->s1d('strong_down', 64000.0, 63972.0, 64386.0)
        ));
        // missing EMAs → no override
        $this->assertSame('MIXED', TrendRegime::classify(
            $this->s4('up', 26.0, 0.60),
            $this->s1d('strong_down', 64000.0, null, null)
        ));
    }

    public function testDownFamily4hIsHostile(): void
    {
        $this->assertSame('HOSTILE', TrendRegime::classify($this->s4('down', 10.0), $this->s1d('up')));
        $this->assertSame('HOSTILE', TrendRegime::classify($this->s4('strong_down', 40.0), $this->s1d('up')));
    }

    public function testHighAdxWithoutAlignmentIsHostile(): void
    {
        $this->assertSame('HOSTILE', TrendRegime::classify($this->s4('sideways', 31.0), $this->s1d('down', 100.0, 110.0, 120.0)));
    }

    public function testStrongUpWithPowerfulAdxIsTrendUpDespiteLowEr(): void
    {
        // BTC 2026-08-22→25: 4h strong_up, ADX 57–59, but er20 0.07–0.32 —
        // the ER veto kept the arm parked through a +14% leg. A strong_up
        // label with ADX >= ADX_STRONG is an unambiguous impulse; ER stays
        // the gate only for moderate (ADX 25–40) trends.
        $this->assertSame('TREND_UP', TrendRegime::classify($this->s4('strong_up', 58.8, 0.08, 44.0), $this->s1d('up')));
        // EMA-reclaim alignment path gets the same override
        $this->assertSame('TREND_UP', TrendRegime::classify(
            $this->s4('strong_up', 57.5, 0.19, 45.0),
            $this->s1d('strong_down', 78947.0, 69492.0, 66844.0)
        ));
        // strong_up below ADX_STRONG still needs ER
        $this->assertSame('MIXED', TrendRegime::classify($this->s4('strong_up', 35.0, 0.10, 44.0), $this->s1d('up')));
        // plain 'up' never gets the override, however high ADX reads
        $this->assertNotSame('TREND_UP', TrendRegime::classify($this->s4('up', 58.0, 0.10, 44.0), $this->s1d('up')));
    }

    public function testWeakAdxOrErIsNotTrendUp(): void
    {
        $this->assertNotSame('TREND_UP', TrendRegime::classify($this->s4('up', 20.0, 0.60), $this->s1d('up')));
        $this->assertNotSame('TREND_UP', TrendRegime::classify($this->s4('up', 28.0, 0.20), $this->s1d('up')));
        $this->assertNotSame('TREND_UP', TrendRegime::classify($this->s4('up', 28.0, null), $this->s1d('up')));
    }

    public function testRangeAndMixed(): void
    {
        $this->assertSame('RANGE', TrendRegime::classify($this->s4('sideways', 15.0, 0.1, 60.0), $this->s1d('up')));
        $this->assertSame('RANGE', TrendRegime::classify($this->s4('sideways', 15.0, 0.1, 30.0), $this->s1d('down', 100.0, 110.0, 120.0)));
        $this->assertSame('MIXED', TrendRegime::classify($this->s4('up', 22.0, 0.1, 30.0), $this->s1d('down', 100.0, 110.0, 120.0)));
    }

    public function testStaleOrMissingSummaryYieldsNull(): void
    {
        $this->assertNull(TrendRegime::classify($this->s4('up', 30.0, 0.5, 40.0, true), $this->s1d('up')));
        $this->assertNull(TrendRegime::classify($this->s4('up', 30.0), $this->s1d('up', 100.0, 90.0, 95.0, true)));
        $this->assertNull(TrendRegime::classify(null, $this->s1d('up')));
        $this->assertNull(TrendRegime::classify($this->s4('up', 30.0), null));
    }

    // ── hysteresis: decide() from the recent verdict history ────────────

    public function testActivateNeedsTwoConsecutiveTrendUp(): void
    {
        $this->assertSame('hold', TrendRegime::decide('idle', ['TREND_UP']));
        $this->assertSame('hold', TrendRegime::decide('idle', ['TREND_UP', 'MIXED']));      // newest first
        $this->assertSame('activate', TrendRegime::decide('idle', ['TREND_UP', 'TREND_UP']));
        $this->assertSame('activate', TrendRegime::decide('idle', ['TREND_UP', 'TREND_UP', 'HOSTILE']));
    }

    public function testDeactivateNeedsFourConsecutiveNonTrendUpOrOneHostile(): void
    {
        // 2026-08-28: two MIXED passes (ADX 40.2 → 39.7) deactivated an arm
        // that had been active for 75 minutes — a knife-edge, not a regime
        // end. Deactivation now needs DEACTIVATE_PASSES (an hour of
        // non-TREND_UP at the 15-min cadence); HOSTILE still cuts at once.
        $this->assertSame(4, TrendRegime::DEACTIVATE_PASSES);
        $this->assertSame('hold', TrendRegime::decide('active', ['MIXED', 'TREND_UP']));
        $this->assertSame('hold', TrendRegime::decide('active', ['MIXED', 'RANGE']));
        $this->assertSame('hold', TrendRegime::decide('active', ['MIXED', 'RANGE', 'MIXED', 'TREND_UP']));
        $this->assertSame('deactivate', TrendRegime::decide('active', ['MIXED', 'RANGE', 'MIXED', 'MIXED']));
        $this->assertSame('deactivate', TrendRegime::decide('active', ['HOSTILE']));
        $this->assertSame('hold', TrendRegime::decide('active', ['TREND_UP', 'TREND_UP']));
    }

    public function testHoldingRelaxesTheStrongAdxAndErGates(): void
    {
        // exactly the 2026-08-28 09:18 tape: strong_up, ADX 39.7, ER 0.13 —
        // MIXED for a fresh entry, but an OPEN arm keeps TREND_UP down to
        // ADX_STRONG_HOLD (hysteresis on the threshold, not just on passes)
        $s4 = $this->s4('strong_up', 39.7, 0.13, 46.0);
        $this->assertSame('MIXED', TrendRegime::classify($s4, $this->s1d('up')));
        $this->assertSame('TREND_UP', TrendRegime::classify($s4, $this->s1d('up'), true));
        // below the hold threshold even an open arm reads MIXED
        $this->assertSame('MIXED', TrendRegime::classify($this->s4('strong_up', 34.0, 0.13, 46.0), $this->s1d('up'), true));
        // the ER gate relaxes too for moderate-ADX trends
        $this->assertSame('MIXED', TrendRegime::classify($this->s4('up', 28.0, 0.27), $this->s1d('up')));
        $this->assertSame('TREND_UP', TrendRegime::classify($this->s4('up', 28.0, 0.27), $this->s1d('up'), true));
        // HOSTILE is never relaxed
        $this->assertSame('HOSTILE', TrendRegime::classify($this->s4('down', 45.0, 0.9), $this->s1d('up'), true));
    }

    public function testWindingDownReactivatesOnTrendUpAgain(): void
    {
        // deploy is 0 but the slice is still parked on the trend run: a
        // renewed TREND_UP re-arms entries without a full release/activate
        $this->assertSame('activate', TrendRegime::decide('winding_down', ['TREND_UP', 'TREND_UP']));
        $this->assertSame('hold', TrendRegime::decide('winding_down', ['MIXED', 'MIXED']));
    }
}
