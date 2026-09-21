<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\RoutineBrief;
use PHPUnit\Framework\TestCase;

/**
 * Pure computations behind gtbot_routine_brief: the compact signal digest,
 * the regime read, a deterministic candidate + deploy band, per-run
 * flags/attention, and the material-change fingerprint diff.
 */
class RoutineBriefTest extends TestCase
{
    private function sum(string $t4 = 'up', float $adx = 26.0, float $er = 0.5, string $t1d = 'up', float $price = 100.0, string $t1h = 'up'): array
    {
        return [
            '1h' => ['trend' => $t1h, 'rsi14' => 55.123, 'atr_pct' => 0.456, 'ema20' => 99.0, 'ema50' => 98.0, 'ema200' => 90.0, 'price' => $price, 'adx14' => 20.0, 'er20' => 0.3, 'chop14' => 50.0, 'atr_pct_rank' => 40.0, 'stale' => false, 'age_seconds' => 120],
            '4h' => ['trend' => $t4, 'rsi14' => 58.0, 'atr_pct' => 1.02, 'ema20' => 98.0, 'ema50' => 97.0, 'ema200' => 92.0, 'price' => $price, 'adx14' => $adx, 'er20' => $er, 'chop14' => 45.0, 'atr_pct_rank' => 34.1, 'stale' => false, 'age_seconds' => 120],
            '1d' => ['trend' => $t1d, 'rsi14' => 60.0, 'atr_pct' => 2.25, 'ema20' => 95.0, 'ema50' => 96.0, 'ema200' => 110.0, 'price' => $price, 'adx14' => 30.0, 'er20' => 0.4, 'chop14' => 40.0, 'atr_pct_rank' => 4.0, 'stale' => false, 'age_seconds' => 120],
        ];
    }

    // ── digest ──────────────────────────────────────────────────────────

    public function testDigestRoundsAndKeepsOnlyTheBriefFields(): void
    {
        $d = RoutineBrief::digest($this->sum(), '100.456');
        $this->assertSame(100.46, $d['price']);
        $this->assertSame(['trend', 'rsi', 'atr_pct', 'ema20', 'ema50'], array_keys($d['1h']));
        $this->assertSame(['trend', 'adx', 'er20', 'chop', 'atr_pct', 'atr_rank', 'ema20', 'ema50', 'ema200'], array_keys($d['4h']));
        $this->assertSame(['trend', 'rsi', 'ema20', 'ema50', 'ema200'], array_keys($d['1d']));
        $this->assertSame(55.12, $d['1h']['rsi']);
        $this->assertSame('up', $d['1h']['trend']);
        $this->assertFalse($d['stale']);
        $this->assertSame(120, $d['age_s']);
    }

    public function testDigestFlagsStaleWhenAnyFrameIsStaleOrMissing(): void
    {
        $s = $this->sum();
        $s['4h']['stale'] = true;
        $this->assertTrue(RoutineBrief::digest($s, '100')['stale']);
        unset($s['1d']);
        $this->assertTrue(RoutineBrief::digest($s, '100')['stale']);
        $this->assertNull(RoutineBrief::digest($s, '100')['1d']);
    }

    // ── regime ──────────────────────────────────────────────────────────

    public function testRegimeUsesTrendRegimeAndRegimeGate(): void
    {
        $r = RoutineBrief::regime($this->sum('strong_up', 27.0, 0.6, 'strong_down', 100.0), 100.0);
        $this->assertSame('TREND_UP', $r['class']);         // 1d label stale but EMA20/50 reclaimed
        $this->assertNull($r['hostile_cap']);
        $this->assertTrue($r['reentry_gate']);                // price > 1d ema20 & ema50, 4h+1h up
        // adx >= 30: the sweep class stays TREND_UP (aligned) but gtbot_set_grid's regime gate
        // WILL cap at 25 — the brief reports what the write path will do
        $g = RoutineBrief::regime($this->sum('strong_up', 33.0, 0.6, 'up', 100.0), 100.0);
        $this->assertSame('TREND_UP', $g['class']);
        $this->assertSame(25, $g['hostile_cap']);
        $h = RoutineBrief::regime($this->sum('down', 35.0, 0.6, 'down', 80.0), 80.0);
        $this->assertSame('HOSTILE', $h['class']);
        $this->assertSame(25, $h['hostile_cap']);
        $this->assertFalse($h['reentry_gate']);
    }

    // ── deploy band ─────────────────────────────────────────────────────

    public function testDeployBandProfileWallsAndRegime(): void
    {
        $calm = ['class' => 'RANGE', 'hostile_cap' => null, 'reentry_gate' => false];
        $this->assertSame([0, 70], array_slice(RoutineBrief::deployBand('Balanced', $calm, 30), 0, 2));
        $this->assertSame([0, 30], array_slice(RoutineBrief::deployBand('NoLoss', $calm, 30), 0, 2));
        $this->assertSame([0, 100], array_slice(RoutineBrief::deployBand('Max', $calm, 30), 0, 2));
        $hostile = ['class' => 'HOSTILE', 'hostile_cap' => 25, 'reentry_gate' => false];
        $b = RoutineBrief::deployBand('Aggressive', $hostile, 50);
        $this->assertSame([0, 25], array_slice($b, 0, 2));
        $this->assertStringContainsString('prefer 0', $b[2]);
        $cappedUp = ['class' => 'TREND_UP', 'hostile_cap' => 25, 'reentry_gate' => false];
        $b = RoutineBrief::deployBand('Balanced', $cappedUp, 50);
        $this->assertSame([0, 25], array_slice($b, 0, 2));
        $this->assertStringContainsString('override_regime_gate', $b[2]);
        $reentry = ['class' => 'MIXED', 'hostile_cap' => null, 'reentry_gate' => true];
        $this->assertSame([30, 40], array_slice(RoutineBrief::deployBand('Balanced', $reentry, 0), 0, 2));
        $this->assertSame([30, 30], array_slice(RoutineBrief::deployBand('NoLoss', $reentry, 0), 0, 2)); // capped by the wall
        $this->assertSame([0, 70], array_slice(RoutineBrief::deployBand('Balanced', $reentry, 40), 0, 2)); // already deployed: normal band
    }

    // ── candidate ───────────────────────────────────────────────────────

    private function candles(float $lo, float $hi, int $n = 60): array
    {
        $out = [];
        for ($i = 0; $i < $n; $i++) {
            $mid = $lo + ($hi - $lo) * (0.5 + 0.4 * sin($i / 6));
            $out[] = ['low' => (string) ($mid - ($hi - $lo) * 0.01), 'high' => (string) ($mid + ($hi - $lo) * 0.01), 'close' => (string) $mid];
        }
        return $out;
    }

    public function testCandidateBracketsPriceAndCarriesDeployBand(): void
    {
        $calm = ['class' => 'RANGE', 'hostile_cap' => null, 'reentry_gate' => false];
        $c = RoutineBrief::candidate($this->candles(90, 110), '0.001', '350', 100.0, 'Balanced', $calm, 30);
        $this->assertNotNull($c);
        $this->assertLessThan(100.0, (float) $c['p_low']);
        $this->assertGreaterThan(100.0, (float) $c['p_high']);
        $this->assertGreaterThanOrEqual(1.3, (float) $c['spacing_pct']);
        $this->assertSame([0, 70], $c['deploy_band']);
        $this->assertIsString($c['why']);
    }

    public function testCandidateShiftsEnvelopeWhenPriceIsOutsideIt(): void
    {
        $calm = ['class' => 'RANGE', 'hostile_cap' => null, 'reentry_gate' => false];
        $c = RoutineBrief::candidate($this->candles(90, 110), '0.001', '350', 130.0, 'Balanced', $calm, 30);
        $this->assertNotNull($c);
        $this->assertLessThan(130.0, (float) $c['p_low']);
        $this->assertGreaterThan(130.0, (float) $c['p_high']);
        $this->assertStringContainsString('shifted', $c['why']);
    }

    public function testCandidateIsNullWithTooFewCandlesOrNoPrice(): void
    {
        $calm = ['class' => 'RANGE', 'hostile_cap' => null, 'reentry_gate' => false];
        $this->assertNull(RoutineBrief::candidate($this->candles(90, 110, 5), '0.001', '350', 100.0, 'Balanced', $calm, 0));
        $this->assertNull(RoutineBrief::candidate($this->candles(90, 110), '0.001', '350', null, 'Balanced', $calm, 0));
    }

    // ── flags / attention ───────────────────────────────────────────────

    private function baseRun(): array
    {
        return ['id' => 7, 'algo' => 'Grid', 'kill' => false, 'hb_stale' => false, 'refit_pending' => false,
            'geometry' => ['pos_pct' => 50.0, 'in_range' => true], 'deploy_pct' => 30,
            'regime' => ['class' => 'RANGE', 'hostile_cap' => null, 'reentry_gate' => false],
            'signal' => ['stale' => false], 'hours_since_decision' => 1.0, 'quiet_hours' => 6,
            'drawdown_tripped' => false, 'trend_transition_since_last' => false, 'regime_changed' => false];
    }

    public function testQuietGridRunHasNoFlags(): void
    {
        $this->assertSame(['flags' => [], 'attention' => false], RoutineBrief::flags($this->baseRun()));
    }

    public function testFlagsAndAttentionTable(): void
    {
        $base = $this->baseRun();
        $f = RoutineBrief::flags(array_replace($base, ['geometry' => ['pos_pct' => 104.0, 'in_range' => false]]));
        $this->assertContains('outside_band', $f['flags']);
        $this->assertTrue($f['attention']);
        $this->assertContains('near_bound', RoutineBrief::flags(array_replace($base, ['geometry' => ['pos_pct' => 93.0, 'in_range' => true]]))['flags']);
        $this->assertContains('hostile_overdeployed', RoutineBrief::flags(array_replace($base, ['regime' => ['class' => 'HOSTILE', 'hostile_cap' => 25, 'reentry_gate' => false], 'deploy_pct' => 40]))['flags']);
        $h = RoutineBrief::flags(array_replace($base, ['regime' => ['class' => 'HOSTILE', 'hostile_cap' => 25, 'reentry_gate' => false], 'deploy_pct' => 0]));
        $this->assertContains('hostile', $h['flags']);
        $this->assertFalse($h['attention'], 'hostile but already flat is not attention');
        $this->assertContains('reentry_gate', RoutineBrief::flags(array_replace($base, ['regime' => ['class' => 'MIXED', 'hostile_cap' => null, 'reentry_gate' => true], 'deploy_pct' => 0]))['flags']);
        $this->assertContains('quiet_too_long', RoutineBrief::flags(array_replace($base, ['hours_since_decision' => 7.5]))['flags']);
        $this->assertContains('kill', RoutineBrief::flags(array_replace($base, ['kill' => true]))['flags']);
        $this->assertContains('heartbeat_stale', RoutineBrief::flags(array_replace($base, ['hb_stale' => true]))['flags']);
        $this->assertContains('data_stale', RoutineBrief::flags(array_replace($base, ['signal' => ['stale' => true]]))['flags']);
        $this->assertContains('regime_changed', RoutineBrief::flags(array_replace($base, ['regime_changed' => true]))['flags']);
        $this->assertContains('drawdown_tripped', RoutineBrief::flags(array_replace($base, ['drawdown_tripped' => true]))['flags']);
        $rp = RoutineBrief::flags(array_replace($base, ['refit_pending' => true]));
        $this->assertContains('refit_pending', $rp['flags']);
        $this->assertFalse($rp['attention'], 'refit_pending is informational (wait a tick), not a decision');
    }

    public function testTrendRunFlags(): void
    {
        $base = $this->baseRun();
        $t = RoutineBrief::flags(array_replace($base, ['algo' => 'Trend', 'trend_transition_since_last' => true]));
        $this->assertContains('trend_managed', $t['flags']);
        $this->assertContains('trend_transition', $t['flags']);
        $this->assertTrue($t['attention']);
        // a Trend run with nothing new is NOT attention, even if quiet for long or outside the (irrelevant) band
        $q = RoutineBrief::flags(array_replace($base, ['algo' => 'Trend', 'hours_since_decision' => 99.0, 'refit_pending' => true, 'geometry' => ['pos_pct' => 150.0, 'in_range' => false]]));
        $this->assertFalse($q['attention']);
        $this->assertSame(['trend_managed'], $q['flags']);
    }

    public function testHeldRunIsParkedNotStale(): void
    {
        // Halted = parked by the operator: no daemon, so a stale heartbeat is
        // expected and must not raise attention; the brief says "held" instead.
        $base = $this->baseRun();
        $h = RoutineBrief::flags(array_replace($base, ['held' => true, 'hb_stale' => true]));
        $this->assertContains('held', $h['flags']);
        $this->assertNotContains('heartbeat_stale', $h['flags']);
        $this->assertFalse($h['attention']);
        $t = RoutineBrief::flags(array_replace($base, ['algo' => 'Trend', 'held' => true, 'hb_stale' => true]));
        $this->assertContains('held', $t['flags']);
        $this->assertNotContains('heartbeat_stale', $t['flags']);
        $this->assertFalse($t['attention']);
    }

    // ── fingerprint diff ────────────────────────────────────────────────

    public function testFingerprintDiff(): void
    {
        $cur = ['drawdown_tripped' => false, 'last_alert_id' => 10, 'last_trend_transition_id' => 3,
            'runs' => [7 => ['class' => 'RANGE', 'in_range' => true, 'pos_bucket' => 'mid', 'deploy' => 30, 'slice' => '350', 'profile' => 'Balanced', 'geom' => 'abc', 'kill' => false, 'hb_stale' => false, 'refit_pending' => false]]];
        $d = RoutineBrief::diff(null, $cur);
        $this->assertTrue($d['material']);
        $this->assertSame(['first brief — no previous fingerprint'], $d['changes']);
        $this->assertFalse(RoutineBrief::diff($cur, $cur)['material']);
        $next = $cur;
        $next['runs'][7]['class'] = 'HOSTILE';
        $next['runs'][7]['pos_bucket'] = 'above';
        $next['last_alert_id'] = 12;
        $d = RoutineBrief::diff($cur, $next);
        $this->assertTrue($d['material']);
        $this->assertSame(['run 7: class RANGE→HOSTILE', 'run 7: pos_bucket mid→above', 'new Alert/Error events since last brief'], $d['changes']);
        $gone = $cur;
        unset($gone['runs'][7]);
        $this->assertSame(['run 7 left the pool'], RoutineBrief::diff($cur, $gone)['changes']);
        $this->assertSame(['run 7 joined the pool'], RoutineBrief::diff($gone, $cur)['changes']);
    }

    /**
     * The long-horizon outlook is context (refit-evidence §16: no forecast
     * skill): a change is reported but must never wake a quiet routine, and
     * the first brief after the deploy must not announce every symbol.
     */
    public function testOutlookChangeIsReportedButNeverMaterial(): void
    {
        $base = ['drawdown_tripped' => false, 'last_alert_id' => 10, 'last_trend_transition_id' => 3, 'runs' => []];
        $cur = $base + ['outlook' => ['BTCUSDT' => 'MAJOR_DOWN', 'BNBUSDT' => 'NEUTRAL']];

        $d = RoutineBrief::diff($base, $cur);
        $this->assertFalse($d['material']);
        $this->assertSame([], $d['changes'], 'previous fingerprint predates the key → silent');

        $prev = $base + ['outlook' => ['BTCUSDT' => 'NEUTRAL', 'BNBUSDT' => 'NEUTRAL']];
        $d = RoutineBrief::diff($prev, $cur);
        $this->assertFalse($d['material']);
        $this->assertSame(['outlook BTCUSDT: NEUTRAL→MAJOR_DOWN (context only)'], $d['changes']);
    }

    public function testPosBucket(): void
    {
        $this->assertSame('below', RoutineBrief::posBucket(-3.0));
        $this->assertSame('low', RoutineBrief::posBucket(5.0));
        $this->assertSame('mid', RoutineBrief::posBucket(50.0));
        $this->assertSame('high', RoutineBrief::posBucket(95.0));
        $this->assertSame('above', RoutineBrief::posBucket(120.0));
        $this->assertSame('na', RoutineBrief::posBucket(null));
    }
}
