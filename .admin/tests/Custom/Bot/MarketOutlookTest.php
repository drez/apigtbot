<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\MarketOutlook;
use PHPUnit\Framework\TestCase;

/**
 * Long-horizon outlook (1d + 1w agreement). Warn-only: nothing here gates an
 * engine, so the tests pin the vocabulary, the vote boundaries, the hold-side
 * and the pass hysteresis — the knobs scripts/outlook-sweep.php tunes.
 */
class MarketOutlookTest extends TestCase
{
    /** 1d summary: $votes of the four price/EMA votes go up (the slope is the fifth). */
    private function s1d(string $dir, float $adx = 25.0, float $er = 0.4, bool $stale = false): array
    {
        $up = ['price' => 100.0, 'ema20' => 95.0, 'ema50' => 90.0, 'ema200' => 80.0];
        $down = ['price' => 100.0, 'ema20' => 105.0, 'ema50' => 110.0, 'ema200' => 120.0];
        $flat = ['price' => 100.0, 'ema20' => 95.0, 'ema50' => 110.0, 'ema200' => 120.0];
        return ['up' => $up, 'down' => $down, 'flat' => $flat][$dir]
            + ['adx14' => $adx, 'er20' => $er, 'stale' => $stale, 'candles_used' => 300];
    }

    private function s1w(string $dir, int $bars = 300, bool $stale = false): array
    {
        $up = ['price' => 100.0, 'ema20' => 90.0, 'ema50' => 80.0];
        $down = ['price' => 100.0, 'ema20' => 110.0, 'ema50' => 120.0];
        $flat = ['price' => 100.0, 'ema20' => 95.0, 'ema50' => 110.0];
        return ['up' => $up, 'down' => $down, 'flat' => $flat][$dir]
            + ['adx14' => 25.0, 'stale' => $stale, 'candles_used' => $bars];
    }

    private function feat(float $slope1d, float $slope1w): array
    {
        return ['slope50_1d' => $slope1d, 'slope20_1w' => $slope1w];
    }

    private function verdict(array $s1d, array $s1w, array $feat, ?string $holding = null, ?float $funding = null): ?string
    {
        return MarketOutlook::classify($s1d, $s1w, $feat, $holding, $funding)['verdict'];
    }

    public function testVerdictTable(): void
    {
        $this->assertSame('MAJOR_UP', $this->verdict($this->s1d('up'), $this->s1w('up'), $this->feat(2.0, 1.0)));
        $this->assertSame('MAJOR_DOWN', $this->verdict($this->s1d('down'), $this->s1w('down'), $this->feat(-2.0, -1.0)));
        // daily turned, weekly has not followed yet → the early warning
        $this->assertSame('UP_FORMING', $this->verdict($this->s1d('up'), $this->s1w('flat'), $this->feat(2.0, 0.0)));
        $this->assertSame('DOWN_FORMING', $this->verdict($this->s1d('down'), $this->s1w('flat'), $this->feat(-2.0, 0.0)));
        // the two horizons disagree outright → no call
        $this->assertSame('NEUTRAL', $this->verdict($this->s1d('up'), $this->s1w('down'), $this->feat(2.0, -1.0)));
        $this->assertSame('NEUTRAL', $this->verdict($this->s1d('down'), $this->s1w('up'), $this->feat(-2.0, 1.0)));
        // a weekly trend with a directionless daily is not a call either
        $this->assertSame('NEUTRAL', $this->verdict($this->s1d('flat'), $this->s1w('up'), $this->feat(0.0, 1.0)));
    }

    public function testAgreementWithoutDailyStrengthIsOnlyForming(): void
    {
        $weak = $this->s1d('up', MarketOutlook::ADX_MIN_1D - 0.1, MarketOutlook::ER_MIN_1D - 0.01);
        $this->assertSame('UP_FORMING', $this->verdict($weak, $this->s1w('up'), $this->feat(2.0, 1.0)));
        // either strength gate is enough
        $adx = $this->s1d('up', MarketOutlook::ADX_MIN_1D, 0.0);
        $er = $this->s1d('up', 0.0, MarketOutlook::ER_MIN_1D);
        $this->assertSame('MAJOR_UP', $this->verdict($adx, $this->s1w('up'), $this->feat(2.0, 1.0)));
        $this->assertSame('MAJOR_UP', $this->verdict($er, $this->s1w('up'), $this->feat(2.0, 1.0)));
    }

    public function testDailyDirectionNeedsFourOfFiveVotes(): void
    {
        // four EMA votes up, slope against → still 4/5
        $this->assertSame('up', MarketOutlook::dir1d($this->s1d('up'), $this->feat(-2.0, 0.0))['dir']);
        // price under the EMA200 AND a flat slope → 3/5, no direction
        $s = $this->s1d('up');
        $s['ema200'] = 150.0;
        $this->assertSame('flat', MarketOutlook::dir1d($s, $this->feat(0.0, 0.0))['dir']);
        // the slope vote has a dead band
        $this->assertSame(4, MarketOutlook::dir1d($s, $this->feat(MarketOutlook::SLOPE_MIN_1D, 0.0))['up']);
        $this->assertSame(3, MarketOutlook::dir1d($s, $this->feat(MarketOutlook::SLOPE_MIN_1D - 0.01, 0.0))['up']);
    }

    public function testWeeklyDirectionNeedsPriceOverBothEmasAndANonFallingSlope(): void
    {
        $this->assertSame('up', MarketOutlook::dir1w($this->s1w('up'), $this->feat(0.0, 0.0)));
        $this->assertSame('flat', MarketOutlook::dir1w($this->s1w('up'), $this->feat(0.0, -0.1)));
        $this->assertSame('down', MarketOutlook::dir1w($this->s1w('down'), $this->feat(0.0, 0.0)));
        $this->assertSame('flat', MarketOutlook::dir1w($this->s1w('down'), $this->feat(0.0, 0.1)));
    }

    public function testNoVerdictOnMissingStaleOrThinInput(): void
    {
        $f = $this->feat(2.0, 1.0);
        $this->assertNull(MarketOutlook::classify(null, $this->s1w('up'), $f)['verdict']);
        $this->assertNull(MarketOutlook::classify($this->s1d('up'), null, $f)['verdict']);
        $this->assertNull($this->verdict($this->s1d('up', 25.0, 0.4, true), $this->s1w('up'), $f));
        $this->assertNull($this->verdict($this->s1d('up'), $this->s1w('up', 300, true), $f));
        // a young listing has no weekly structure to read
        $thin = MarketOutlook::classify($this->s1d('up'), $this->s1w('up', MarketOutlook::MIN_BARS_1W - 1), $f);
        $this->assertNull($thin['verdict']);
        $this->assertStringContainsString('insufficient 1w history', implode(' ', $thin['reasons']));
        $this->assertSame('MAJOR_UP', $this->verdict($this->s1d('up'), $this->s1w('up', MarketOutlook::MIN_BARS_1W), $f));
    }

    public function testConfirmedMajorHoldsThroughAWeakOrFlatDaily(): void
    {
        $weekUp = $this->s1w('up');
        $f = $this->feat(0.0, 1.0);
        // the daily went flat: a fresh read is NEUTRAL, a held MAJOR_UP stays
        $this->assertSame('NEUTRAL', $this->verdict($this->s1d('flat'), $weekUp, $f));
        $this->assertSame('MAJOR_UP', $this->verdict($this->s1d('flat'), $weekUp, $f, 'MAJOR_UP'));
        // the daily turning DOWN, or the weekly giving way, ends the hold
        $this->assertNotSame('MAJOR_UP', $this->verdict($this->s1d('down'), $weekUp, $this->feat(-2.0, 1.0), 'MAJOR_UP'));
        $this->assertNotSame('MAJOR_UP', $this->verdict($this->s1d('flat'), $this->s1w('flat'), $f, 'MAJOR_UP'));
        // mirrored
        $this->assertSame('MAJOR_DOWN', $this->verdict($this->s1d('flat'), $this->s1w('down'), $this->feat(0.0, -1.0), 'MAJOR_DOWN'));
        // holding the OTHER side buys nothing
        $this->assertSame('NEUTRAL', $this->verdict($this->s1d('flat'), $weekUp, $f, 'MAJOR_DOWN'));
    }

    public function testFundingIsContextNeverAVerdict(): void
    {
        $f = $this->feat(2.0, 1.0);
        foreach ([null, 5.0, 50.0, 95.0] as $pct) {
            $this->assertSame('MAJOR_UP', $this->verdict($this->s1d('up'), $this->s1w('up'), $f, null, $pct));
        }
        $crowded = MarketOutlook::classify($this->s1d('up'), $this->s1w('up'), $f, null, 95.0);
        $this->assertStringContainsString('crowded longs', implode(' ', $crowded['reasons']));
    }

    public function testSlopeFeatures(): void
    {
        // a series climbing 1% a bar: both slopes positive; falling: negative
        $rise = $fall = [];
        for ($i = 0; $i < 120; $i++) {
            $rise[] = 100.0 * (1.01 ** $i);
            $fall[] = 100.0 * (0.99 ** $i);
        }
        $up = MarketOutlook::features($rise, $rise);
        $down = MarketOutlook::features($fall, $fall);
        $this->assertGreaterThan(MarketOutlook::SLOPE_MIN_1D, $up['slope50_1d']);
        $this->assertGreaterThan(0.0, $up['slope20_1w']);
        $this->assertLessThan(-MarketOutlook::SLOPE_MIN_1D, $down['slope50_1d']);
        $this->assertLessThan(0.0, $down['slope20_1w']);
        // not enough bars to seed the EMA on both ends → no slope, no vote
        $short = MarketOutlook::features(array_slice($rise, 0, 50), array_slice($rise, 0, 20));
        $this->assertNull($short['slope50_1d']);
        $this->assertNull($short['slope20_1w']);
    }

    private function walk(array $state, array $reads): array
    {
        foreach ($reads as $r) {
            $state = MarketOutlook::step($state, $r);
        }
        return $state;
    }

    public function testStepPromotesOnlyAfterTheConfirmingPasses(): void
    {
        $n = MarketOutlook::CONFIRM_PASSES_MAJOR;
        $s = $this->walk(['verdict' => 'NEUTRAL'], array_fill(0, $n - 1, 'MAJOR_UP'));
        $this->assertSame('NEUTRAL', $s['verdict']);
        $this->assertSame($n - 1, $s['candidate_passes']);
        $this->assertFalse($s['changed']);

        $s = MarketOutlook::step($s, 'MAJOR_UP');
        $this->assertSame('MAJOR_UP', $s['verdict']);
        $this->assertSame('NEUTRAL', $s['prev']);
        $this->assertTrue($s['changed']);
        $this->assertNull($s['candidate']);

        // and says so exactly once
        $this->assertFalse(MarketOutlook::step($s, 'MAJOR_UP')['changed']);
    }

    public function testFormingConfirmsFasterThanMajor(): void
    {
        $this->assertLessThan(MarketOutlook::CONFIRM_PASSES_MAJOR, MarketOutlook::CONFIRM_PASSES_FORMING);
        $s = $this->walk(['verdict' => 'NEUTRAL'], array_fill(0, MarketOutlook::CONFIRM_PASSES_FORMING, 'UP_FORMING'));
        $this->assertSame('UP_FORMING', $s['verdict']);
    }

    public function testAReadBackAtTheVerdictResetsTheCandidate(): void
    {
        $s = $this->walk(['verdict' => 'NEUTRAL'], array_fill(0, 10, 'UP_FORMING'));
        $s = MarketOutlook::step($s, 'NEUTRAL');
        $this->assertNull($s['candidate']);
        $this->assertSame(0, $s['candidate_passes']);
        // a read on the OTHER side restarts the count
        $s = $this->walk(['verdict' => 'NEUTRAL'], array_fill(0, 10, 'UP_FORMING'));
        $s = MarketOutlook::step($s, 'DOWN_FORMING');
        $this->assertSame('DOWN_FORMING', $s['candidate']);
        $this->assertSame(1, $s['candidate_passes']);
    }

    public function testNullReadsAreSkipped(): void
    {
        $s = $this->walk(['verdict' => 'NEUTRAL'], ['UP_FORMING', 'UP_FORMING', null, null, 'UP_FORMING']);
        $this->assertSame(3, $s['candidate_passes']);
    }

    /**
     * A verdict must not get stuck because the tape oscillates between two
     * reads that BOTH say it is over: passes accumulate while the read stays
     * on the same side of the confirmed verdict, and the newest read is what
     * gets promoted.
     */
    public function testAlternatingReadsOnTheSameSideStillLeaveAStaleVerdict(): void
    {
        $reads = [];
        for ($i = 0; $i < MarketOutlook::CONFIRM_PASSES_NEUTRAL; $i++) {
            $reads[] = $i % 2 ? 'NEUTRAL' : 'UP_FORMING';
        }
        $s = $this->walk(['verdict' => 'MAJOR_UP'], $reads);
        $this->assertNotSame('MAJOR_UP', $s['verdict']);
    }

    public function testFirstEverVerdictIsConfirmedLikeAnyOther(): void
    {
        $s = $this->walk([], array_fill(0, MarketOutlook::CONFIRM_PASSES_MAJOR, 'MAJOR_DOWN'));
        $this->assertSame('MAJOR_DOWN', $s['verdict']);
        $this->assertNull($s['prev']);
        $this->assertTrue($s['changed']);
    }

    public function testCyclePosition(): void
    {
        $closes = array_fill(0, 200, 50.0);
        $near = MarketOutlook::cycle([80.0, 100.0, 90.0], $closes, 90.0);
        $this->assertSame(100.0, $near['high']);
        $this->assertSame(10.0, $near['drawdown_pct']);
        $this->assertSame('near_high', $near['bucket']);
        $this->assertSame(1.8, $near['mayer']);
        $this->assertSame('mid', MarketOutlook::cycle([100.0], $closes, 60.0)['bucket']);
        $this->assertSame('deep_drawdown', MarketOutlook::cycle([100.0], $closes, 39.0)['bucket']);
        // a new high above every stored bar is 0% down, never negative
        $this->assertSame(0.0, MarketOutlook::cycle([100.0], $closes, 120.0)['drawdown_pct']);
        // Mayer needs 200 dailies; no weekly highs → no read at all
        $this->assertNull(MarketOutlook::cycle([100.0], array_fill(0, 199, 50.0), 90.0)['mayer']);
        $this->assertNull(MarketOutlook::cycle([], $closes, 90.0));
    }

    /** Wed 2026-09-23 12:00 UTC — mid-week, so the weekly bar is in progress */
    private const NOW = 1_790_000_000 - (1_790_000_000 % 86400) + 2 * 86400 + 43200;

    /** $n oldest-first [h,l,c] bars, the last closing at $last, moving $k per bar */
    private function tape(int $n, float $k, float $last = 100.0): array
    {
        $c = [];
        for ($i = 0; $i < $n; $i++) {
            $x = $last * ($k ** ($i - $n + 1));
            $c[] = ['high' => (string) ($x * 1.01), 'low' => (string) ($x * 0.99), 'close' => (string) $x];
        }
        return $c;
    }

    public function testSeedReplaysTheStoredTapeToTodaysVerdict(): void
    {
        $s = MarketOutlook::seed($this->tape(300, 1.004), $this->tape(300, 1.02), self::NOW);
        $this->assertSame(MarketOutlook::MAJOR_UP, $s['verdict']);
        $this->assertNull($s['candidate']);
        $this->assertSame(MarketOutlook::SEED_DAYS, $s['days']);
        // confirmed at a past daily CLOSE (midnight UTC), long before today
        $this->assertSame(0, $s['since_ts'] % 86400);
        $this->assertLessThan(self::NOW - 30 * 86400, $s['since_ts']);
        $this->assertGreaterThan(0.0, $s['price_at_verdict']);

        $flat = MarketOutlook::seed($this->tape(300, 1.0), $this->tape(300, 1.0), self::NOW);
        $this->assertSame(MarketOutlook::NEUTRAL, $flat['verdict']);
    }

    public function testSeedNeverReadsTodaysInProgressBar(): void
    {
        $d = $this->tape(300, 1.004);
        $w = $this->tape(300, 1.02);
        $crash = $d;
        $crash[299] = ['high' => '100', 'low' => '10', 'close' => '10'];
        $this->assertEquals(MarketOutlook::seed($d, $w, self::NOW), MarketOutlook::seed($crash, $w, self::NOW));
    }

    public function testSeedCarriesALateTurnAsTheCandidate(): void
    {
        // a long uptrend whose last two closes broke hard: the replay still
        // holds MAJOR_UP, with the turn counted as 24 passes per close
        $d = $this->tape(300, 1.004);
        for ($i = 297; $i <= 298; $i++) {
            $x = 100.0 * 0.8 ** ($i - 296);
            $d[$i] = ['high' => (string) ($x * 1.01), 'low' => (string) ($x * 0.99), 'close' => (string) $x];
        }
        $s = MarketOutlook::seed($d, $this->tape(300, 1.02), self::NOW);
        $this->assertSame(MarketOutlook::MAJOR_UP, $s['verdict']);
        $this->assertNotNull($s['candidate']);
        $this->assertSame(2 * MarketOutlook::PASSES_PER_CLOSE, $s['candidate_passes']);
    }

    public function testSeedWithoutEnoughHistoryHasNoVerdict(): void
    {
        $s = MarketOutlook::seed($this->tape(300, 1.004), $this->tape(MarketOutlook::MIN_BARS_1W - 1, 1.02), self::NOW);
        $this->assertNull($s['verdict']);
        $this->assertNull(MarketOutlook::seed([], [], self::NOW)['verdict']);
    }

    public function testHitTable(): void
    {
        $this->assertTrue(MarketOutlook::hit('MAJOR_UP', 0.01));
        $this->assertFalse(MarketOutlook::hit('UP_FORMING', -0.01));
        $this->assertTrue(MarketOutlook::hit('MAJOR_DOWN', -0.01));
        $this->assertFalse(MarketOutlook::hit('DOWN_FORMING', 0.01));
        $band = MarketOutlook::NEUTRAL_BAND_PCT / 100;
        $this->assertTrue(MarketOutlook::hit('NEUTRAL', $band - 0.001));
        $this->assertFalse(MarketOutlook::hit('NEUTRAL', -$band - 0.001));
        $this->assertNull(MarketOutlook::hit('bogus', 0.5));
    }
}
