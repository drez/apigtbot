# Routine Brief + Quiet-Exit Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** One read-only MCP tool (`gtbot_routine_brief`) gives the hourly routine everything it needs for all runs in one compact payload, with a server-computed `material_change` so quiet hours end after one call; the prompt shrinks to ~1.2k words.

**Architecture:** `App\Domains\Bot\RoutineBrief` holds the pure computations (signal digest, regime, candidate + deploy band, flags/attention, fingerprint diff). `GtbotRoutineBriefTool` wires DB reads (runs, summaries, candles, decisions, wallet) into it and persists the fingerprint in config `gtbot_routine_brief_last`. The prompt is rewritten around the brief; sweep narratives move to `docs/refit-evidence.md`. Tool descriptions are trimmed.

**Tech Stack:** PHP 8.4, Propel 1 models (`App\GridRunQuery`, `BotDecisionQuery`, `TradeCycleQuery`, `BotEventQuery`, `ConfigQuery`), existing domain classes (`MarketStore`, `RangeFitter`, `TrendRegime`, `RegimeGate`, `DrawdownGuard`, `SimWallet`, `OrderStore`, `DecisionScorer`, `TrendActivator`), PHPUnit 10 (`php vendor/bin/phpunit` from `.admin/`).

**Spec:** `docs/superpowers/specs/2026-08-20-routine-brief-design.md`

## Global Constraints

- Tools are auto-discovered: any `src/App/Mcp/Tools/*Tool.php` extending `AbstractGtbotBase` with `name()/description()/inputSchema()/requiredRight()/run()` is registered (ToolRegistry glob).
- The brief is read-only for runs; the ONLY write is the config row `gtbot_routine_brief_last`.
- Brief JSON ≤ 6000 bytes for 3 runs (test-pinned).
- Prompt ≤ 1300 words; version string `v2026-08-21.1`.
- Tests: DB-backed tests run inside a transaction (pattern: `tests/Custom/Bot/TrendActivatorTest.php`); the dev DB has exactly one BTCUSDT `market_summary` row — inject summaries/candles, never rely on stored market data.
- bc-math strings for money; round only at the presentation edge.

---

### Task 1: `RoutineBrief` pure computations (digest, regime, candidate, deploy band)

**Files:**
- Create: `.admin/src/App/Domains/Bot/RoutineBrief.php`
- Test: `.admin/tests/Custom/Bot/RoutineBriefTest.php`

**Interfaces:**
- Produces:
  - `RoutineBrief::digest(array $summaries, ?string $price): array` → `['price'=>?float,'1h'=>[...],'4h'=>[...],'1d'=>[...],'age_s'=>?int,'stale'=>bool]`
  - `RoutineBrief::regime(array $summaries, ?float $price): array` → `['class'=>?string,'hostile_cap'=>?int,'reentry_gate'=>bool]`
  - `RoutineBrief::deployBand(string $profile, array $regime, int $currentDeploy): array{0:int,1:int,2:string}` (lo, hi, why)
  - `RoutineBrief::candidate(array $candles, string $feePct, string $slice, ?float $price, string $profile, array $regime, int $currentDeploy): ?array` → `['p_low','p_high','n_levels','spacing_pct','deploy_band'=>[lo,hi],'why']`
  - `RoutineBrief::PROFILE_WALLS` const

- [ ] **Step 1: Write the failing tests**

```php
<?php
namespace Tests\Custom\Bot;

use App\Domains\Bot\RoutineBrief;
use PHPUnit\Framework\TestCase;

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

    public function testDigestRoundsAndKeepsOnlyTheBriefFields(): void
    {
        $d = RoutineBrief::digest($this->sum(), '100.456');
        $this->assertSame(100.46, $d['price']);
        $this->assertSame(['trend', 'rsi', 'atr_pct', 'ema20', 'ema50'], array_keys($d['1h']));
        $this->assertSame(['trend', 'adx', 'er20', 'chop', 'atr_pct', 'atr_rank', 'ema20', 'ema50', 'ema200'], array_keys($d['4h']));
        $this->assertSame(['trend', 'rsi', 'ema20', 'ema50', 'ema200'], array_keys($d['1d']));
        $this->assertSame(55.12, $d['1h']['rsi']);
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

    public function testRegimeUsesTrendRegimeAndRegimeGate(): void
    {
        $r = RoutineBrief::regime($this->sum('strong_up', 33.0, 0.6, 'strong_down', 100.0), 100.0);
        $this->assertSame('TREND_UP', $r['class']);         // 1d label stale but EMA20/50 reclaimed
        $this->assertNull($r['hostile_cap']);
        $this->assertTrue($r['reentry_gate']);                // price > 1d ema20 & ema50, 4h+1h up
        $h = RoutineBrief::regime($this->sum('down', 35.0, 0.6, 'down', 80.0), 80.0);
        $this->assertSame('HOSTILE', $h['class']);
        $this->assertSame(25, $h['hostile_cap']);
        $this->assertFalse($h['reentry_gate']);
    }

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
        $reentry = ['class' => 'MIXED', 'hostile_cap' => null, 'reentry_gate' => true];
        $this->assertSame([30, 40], array_slice(RoutineBrief::deployBand('Balanced', $reentry, 0), 0, 2));
        $this->assertSame([30, 30], array_slice(RoutineBrief::deployBand('NoLoss', $reentry, 0), 0, 2)); // capped by the wall
        $this->assertSame([0, 70], array_slice(RoutineBrief::deployBand('Balanced', $reentry, 40), 0, 2)); // already deployed: normal band
    }

    private function candles(float $lo, float $hi, int $n = 60): array
    {
        $out = [];
        for ($i = 0; $i < $n; $i++) {
            $mid = $lo + ($hi - $lo) * (0.5 + 0.4 * sin($i / 3));
            $out[] = ['low' => (string) ($mid - ($hi - $lo) * 0.05), 'high' => (string) ($mid + ($hi - $lo) * 0.05), 'close' => (string) $mid];
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

    public function testCandidateIsNullWithTooFewCandles(): void
    {
        $calm = ['class' => 'RANGE', 'hostile_cap' => null, 'reentry_gate' => false];
        $this->assertNull(RoutineBrief::candidate($this->candles(90, 110, 5), '0.001', '350', 100.0, 'Balanced', $calm, 0));
    }
}
```

- [ ] **Step 2: Run to verify failure**

Run: `cd .admin && php vendor/bin/phpunit tests/Custom/Bot/RoutineBriefTest.php`
Expected: errors "Class App\Domains\Bot\RoutineBrief not found".

- [ ] **Step 3: Implement `RoutineBrief` (pure part)**

```php
<?php
namespace App\Domains\Bot;

/**
 * Server-side computations behind gtbot_routine_brief: the compact signal
 * digest, the regime read, a deterministic candidate grid + deploy band,
 * the per-run flags/attention, and the "did anything material change since
 * the last brief" fingerprint. Pure functions — the MCP tool feeds them.
 */
final class RoutineBrief
{
    /** deploy_pct walls per risk profile (refit-routine.md 3b) */
    public const PROFILE_WALLS = ['NoLoss' => 30, 'Cautious' => 40, 'Balanced' => 70, 'Aggressive' => 100, 'Max' => 100];
    public const NEAR_BOUND_PCT = 10.0;
    public const DEFAULT_QUIET_HOURS = 6;

    public static function digest(array $s, ?string $price): array
    {
        $r2 = static fn ($v) => $v === null ? null : round((float) $v, 2);
        $f = static fn (string $tf, array $keys) => isset($s[$tf]) ? array_combine(array_keys($keys), array_map(static fn ($k) => $k === 'trend' ? (string) ($s[$tf]['trend'] ?? '') : $r2($s[$tf][$k] ?? null), $keys)) : null;
        $stale = false;
        $age = null;
        foreach (['1h', '4h', '1d'] as $tf) {
            if (!isset($s[$tf]) || !empty($s[$tf]['stale'])) {
                $stale = true;
            }
            $a = $s[$tf]['age_seconds'] ?? null;
            if ($a !== null && ($age === null || $a > $age)) {
                $age = (int) $a;
            }
        }
        return [
            'price' => $r2($price),
            '1h' => $f('1h', ['trend' => 'trend', 'rsi' => 'rsi14', 'atr_pct' => 'atr_pct', 'ema20' => 'ema20', 'ema50' => 'ema50']),
            '4h' => $f('4h', ['trend' => 'trend', 'adx' => 'adx14', 'er20' => 'er20', 'chop' => 'chop14', 'atr_pct' => 'atr_pct', 'atr_rank' => 'atr_pct_rank', 'ema20' => 'ema20', 'ema50' => 'ema50', 'ema200' => 'ema200']),
            '1d' => $f('1d', ['trend' => 'trend', 'rsi' => 'rsi14', 'ema20' => 'ema20', 'ema50' => 'ema50', 'ema200' => 'ema200']),
            'age_s' => $age,
            'stale' => $stale,
        ];
    }

    public static function regime(array $s, ?float $price): array
    {
        $s4 = $s['4h'] ?? null;
        $s1d = $s['1d'] ?? null;
        $class = TrendRegime::classify($s4, $s1d);
        $hostile = RegimeGate::hostile($s4['trend'] ?? null, isset($s4['adx14']) ? (float) $s4['adx14'] : null);
        $reentry = $s1d !== null && $price !== null
            && ($s1d['ema20'] ?? null) !== null && ($s1d['ema50'] ?? null) !== null
            && $price > (float) $s1d['ema20'] && $price > (float) $s1d['ema50']
            && TrendRegime::family((string) ($s4['trend'] ?? '')) === 'up'
            && TrendRegime::family((string) ($s['1h']['trend'] ?? '')) === 'up';
        return ['class' => $class, 'hostile_cap' => $hostile ? RegimeGate::MAX_HOSTILE_DEPLOY_PCT : null, 'reentry_gate' => $reentry];
    }

    /** @return array{0:int,1:int,2:string} lo, hi, why */
    public static function deployBand(string $profile, array $regime, int $currentDeploy): array
    {
        $wall = self::PROFILE_WALLS[$profile] ?? 70;
        if ($regime['hostile_cap'] !== null) {
            $hi = min($wall, (int) $regime['hostile_cap']);
            return [0, $hi, "hostile regime: gate caps at $hi, prefer 0"];
        }
        if ($regime['reentry_gate'] && $currentDeploy === 0) {
            return [min(30, $wall), min(40, $wall), 're-entry gate fired while flat: re-enter at the conflicting-TF band'];
        }
        return [0, $wall, "$profile walls" . ($regime['class'] === 'TREND_UP' ? '; TREND_UP — lean the range up, trend arm handles the leg' : '')];
    }

    public static function candidate(array $candles, string $feePct, string $slice, ?float $price, string $profile, array $regime, int $currentDeploy): ?array
    {
        if (count($candles) < 10 || $price === null) {
            return null;
        }
        $cands = RangeFitter::candidates($candles, $feePct, $slice);
        if ($cands === []) {
            return null;
        }
        $closes = array_map('floatval', array_column($candles, 'close'));
        $best = null;
        $bestPnl = -INF;
        foreach ($cands as $c) {
            $cfg = ['p_low' => $c['p_low'], 'p_high' => $c['p_high'], 'n_levels' => $c['n_levels'], 'budget_quote' => $slice, 'fee_pct' => $feePct, 'spacing' => 'Geometric', 'allocation' => 'EqualBase'];
            try {
                $bt = Backtester::run($cfg, array_map('strval', $closes));
                $pnl = (float) $bt['realized_pnl'];
            } catch (\Throwable) {
                $pnl = -INF;
            }
            if ($pnl > $bestPnl) {
                $bestPnl = $pnl;
                $best = $c;
            }
        }
        $why = $best['name'] . ' envelope';
        $lo = (float) $best['p_low'];
        $hi = (float) $best['p_high'];
        if ($price <= $lo || $price >= $hi) {
            // shift the envelope (same width) so it brackets price, price at 50%
            $half = ($hi - $lo) / 2;
            $lo = $price - $half;
            $hi = $price + $half;
            $why .= ' shifted to bracket price (was outside)';
        }
        [$bLo, $bHi, $bWhy] = self::deployBand($profile, $regime, $currentDeploy);
        return [
            'p_low' => (string) round($lo, 2),
            'p_high' => (string) round($hi, 2),
            'n_levels' => (int) $best['n_levels'],
            'spacing_pct' => (string) round((float) $best['spacing_pct'], 2),
            'deploy_band' => [$bLo, $bHi],
            'why' => $why . '; ' . $bWhy,
        ];
    }
}
```
Check `RangeFitter::candidates` returns `spacing_pct` as a percent (e.g. "1.70") — read `RangeFitter.php` lines 70–110; if it is a fraction, multiply by 100 in `candidate()`. Check `Backtester::run` config keys by reading `GtbotRefitProposalTool::run` + `AbstractGtbotBase::configFromArgs` and pass the same keys (spacing/allocation names as that helper emits).

- [ ] **Step 4: Run tests**

Run: `php vendor/bin/phpunit tests/Custom/Bot/RoutineBriefTest.php`
Expected: PASS (8 tests).

- [ ] **Step 5: Commit**

```bash
git add .admin/src/App/Domains/Bot/RoutineBrief.php .admin/tests/Custom/Bot/RoutineBriefTest.php
git commit -m "feat(bot): RoutineBrief — signal digest, regime, candidate + deploy band"
```

---

### Task 2: flags/attention + fingerprint diff

**Files:**
- Modify: `.admin/src/App/Domains/Bot/RoutineBrief.php`
- Test: `.admin/tests/Custom/Bot/RoutineBriefTest.php` (append)

**Interfaces:**
- Produces:
  - `RoutineBrief::flags(array $run): array{flags: string[], attention: bool}` where `$run` is the per-run brief array BEFORE flags (keys: `algo,kill,hb_stale,refit_pending,geometry{pos_pct,in_range},deploy_pct,regime{class,hostile_cap,reentry_gate},signal{stale},hours_since_decision,quiet_hours,drawdown_tripped,trend_transition_since_last,regime_changed`)
  - `RoutineBrief::fingerprint(array $brief): array` (per-run + global keys)
  - `RoutineBrief::diff(?array $prev, array $cur): array{material: bool, changes: string[]}`

- [ ] **Step 1: Append failing tests**

```php
    public function testFlagsAndAttention(): void
    {
        $base = ['id' => 7, 'algo' => 'Grid', 'kill' => false, 'hb_stale' => false, 'refit_pending' => false,
            'geometry' => ['pos_pct' => 50.0, 'in_range' => true], 'deploy_pct' => 30,
            'regime' => ['class' => 'RANGE', 'hostile_cap' => null, 'reentry_gate' => false],
            'signal' => ['stale' => false], 'hours_since_decision' => 1.0, 'quiet_hours' => 6,
            'drawdown_tripped' => false, 'trend_transition_since_last' => false, 'regime_changed' => false];
        $this->assertSame(['flags' => [], 'attention' => false], RoutineBrief::flags($base));
        $f = RoutineBrief::flags(array_replace($base, ['geometry' => ['pos_pct' => 104.0, 'in_range' => false]]));
        $this->assertContains('outside_band', $f['flags']);
        $this->assertTrue($f['attention']);
        $this->assertContains('near_bound', RoutineBrief::flags(array_replace($base, ['geometry' => ['pos_pct' => 93.0, 'in_range' => true]]))['flags']);
        $this->assertContains('hostile_overdeployed', RoutineBrief::flags(array_replace($base, ['regime' => ['class' => 'HOSTILE', 'hostile_cap' => 25, 'reentry_gate' => false], 'deploy_pct' => 40]))['flags']);
        $this->assertContains('reentry_gate', RoutineBrief::flags(array_replace($base, ['regime' => ['class' => 'MIXED', 'hostile_cap' => null, 'reentry_gate' => true], 'deploy_pct' => 0]))['flags']);
        $this->assertContains('quiet_too_long', RoutineBrief::flags(array_replace($base, ['hours_since_decision' => 7.5]))['flags']);
        $this->assertContains('kill', RoutineBrief::flags(array_replace($base, ['kill' => true]))['flags']);
        $this->assertContains('heartbeat_stale', RoutineBrief::flags(array_replace($base, ['hb_stale' => true]))['flags']);
        $this->assertContains('data_stale', RoutineBrief::flags(array_replace($base, ['signal' => ['stale' => true]]))['flags']);
        $this->assertContains('regime_changed', RoutineBrief::flags(array_replace($base, ['regime_changed' => true]))['flags']);
        $t = RoutineBrief::flags(array_replace($base, ['algo' => 'Trend', 'trend_transition_since_last' => true]));
        $this->assertContains('trend_managed', $t['flags']);
        $this->assertContains('trend_transition', $t['flags']);
        $this->assertTrue($t['attention']);
        // a Trend run with nothing new is NOT attention, even if quiet for long
        $this->assertFalse(RoutineBrief::flags(array_replace($base, ['algo' => 'Trend', 'hours_since_decision' => 99.0]))['attention']);
    }

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

    public function testPosBucket(): void
    {
        $this->assertSame('below', RoutineBrief::posBucket(-3.0));
        $this->assertSame('low', RoutineBrief::posBucket(5.0));
        $this->assertSame('mid', RoutineBrief::posBucket(50.0));
        $this->assertSame('high', RoutineBrief::posBucket(95.0));
        $this->assertSame('above', RoutineBrief::posBucket(120.0));
        $this->assertSame('na', RoutineBrief::posBucket(null));
    }
```

- [ ] **Step 2: Run to verify failure** — `php vendor/bin/phpunit tests/Custom/Bot/RoutineBriefTest.php` → errors on undefined methods.

- [ ] **Step 3: Implement**

```php
    public static function posBucket(?float $pos): string
    {
        if ($pos === null) { return 'na'; }
        if ($pos < 0) { return 'below'; }
        if ($pos < self::NEAR_BOUND_PCT) { return 'low'; }
        if ($pos <= 100 - self::NEAR_BOUND_PCT) { return 'mid'; }
        if ($pos <= 100) { return 'high'; }
        return 'above';
    }

    /** @return array{flags: string[], attention: bool} */
    public static function flags(array $r): array
    {
        $f = [];
        $attention = false;
        $mark = static function (string $flag, bool $att = true) use (&$f, &$attention): void { $f[] = $flag; if ($att) { $attention = true; } };
        if ($r['kill']) { $mark('kill'); }
        if ($r['hb_stale']) { $mark('heartbeat_stale'); }
        if ($r['refit_pending']) { $mark('refit_pending', false); }
        if (!empty($r['drawdown_tripped'])) { $mark('drawdown_tripped'); }
        if (($r['signal']['stale'] ?? false)) { $mark('data_stale'); }
        if ($r['algo'] === 'Trend') {
            $mark('trend_managed', false);
            if (!empty($r['trend_transition_since_last'])) { $mark('trend_transition'); }
            return ['flags' => $f, 'attention' => $attention];
        }
        $pos = $r['geometry']['pos_pct'] ?? null;
        $bucket = self::posBucket($pos);
        if ($bucket === 'below' || $bucket === 'above') { $mark('outside_band'); }
        elseif ($bucket === 'low' || $bucket === 'high') { $mark('near_bound'); }
        if (!empty($r['regime_changed'])) { $mark('regime_changed'); }
        if (($r['regime']['hostile_cap'] ?? null) !== null) {
            $mark('hostile', false);
            if ((int) $r['deploy_pct'] > (int) $r['regime']['hostile_cap']) { $mark('hostile_overdeployed'); }
        }
        if (!empty($r['regime']['reentry_gate']) && (int) $r['deploy_pct'] === 0) { $mark('reentry_gate'); }
        if (($r['hours_since_decision'] ?? 0) > ($r['quiet_hours'] ?? self::DEFAULT_QUIET_HOURS)) { $mark('quiet_too_long'); }
        return ['flags' => $f, 'attention' => $attention];
    }

    /** @return array{material: bool, changes: string[]} */
    public static function diff(?array $prev, array $cur): array
    {
        if ($prev === null) {
            return ['material' => true, 'changes' => ['first brief — no previous fingerprint']];
        }
        $changes = [];
        foreach ($cur['runs'] as $id => $c) {
            if (!isset($prev['runs'][$id])) { $changes[] = "run $id joined the pool"; continue; }
            foreach ($c as $k => $v) {
                $pv = $prev['runs'][$id][$k] ?? null;
                if ($pv !== $v) {
                    $changes[] = sprintf('run %s: %s %s→%s', $id, $k, self::fmtv($pv), self::fmtv($v));
                }
            }
        }
        foreach (array_keys($prev['runs']) as $id) {
            if (!isset($cur['runs'][$id])) { $changes[] = "run $id left the pool"; }
        }
        if (($prev['last_alert_id'] ?? 0) !== ($cur['last_alert_id'] ?? 0)) { $changes[] = 'new Alert/Error events since last brief'; }
        if (($prev['last_trend_transition_id'] ?? 0) !== ($cur['last_trend_transition_id'] ?? 0)) { $changes[] = 'trend arm transition since last brief'; }
        if (($prev['drawdown_tripped'] ?? false) !== ($cur['drawdown_tripped'] ?? false)) { $changes[] = 'drawdown state changed'; }
        return ['material' => $changes !== [], 'changes' => $changes];
    }

    private static function fmtv($v): string
    {
        return is_bool($v) ? ($v ? 'true' : 'false') : (string) ($v ?? 'null');
    }
```

- [ ] **Step 4: Run tests** → PASS (11 tests).
- [ ] **Step 5: Commit** — `git commit -m "feat(bot): RoutineBrief flags/attention + fingerprint diff"`

---

### Task 3: `GtbotRoutineBriefTool` (DB wiring, fingerprint persistence, size budget)

**Files:**
- Create: `.admin/src/App/Mcp/Tools/GtbotRoutineBriefTool.php`
- Test: `.admin/tests/Custom/Bot/GtbotRoutineBriefToolTest.php`

**Interfaces:**
- Consumes: all `RoutineBrief::*` from Tasks 1–2; `TrendActivator::state()`, `TrendActivator::KIND_*`; `MarketStore::summaries/candles`; `DecisionScorer::trackRecord`; `DrawdownGuard::floor/equity/check`; `SimWallet::sharedBudget`; `OrderStore::investedQuote`; `AbstractGtbotBase::heartbeat/ok`.
- Produces: MCP tool `gtbot_routine_brief` with constructor `(?callable $summaries = null, ?callable $candles = null, ?callable $price = null)` for test injection: `summaries(string $symbol): array`, `candles(string $symbol, string $tf): array`, `price(string $symbol): ?string`. Public `build(?int $runId): array` returns the payload array (the test reads this; `run()` wraps it in `ok()`).

- [ ] **Step 1: Failing test (DB-backed, transaction-wrapped, three runs like `TrendActivatorTest`)**

```php
<?php
namespace Tests\Custom\Bot;

use App\Config;
use App\ConfigQuery;
use App\GridRun;
use App\GridRunQuery;
use App\Mcp\Tools\GtbotRoutineBriefTool;
use PHPUnit\Framework\TestCase;

class GtbotRoutineBriefToolTest extends TestCase
{
    private static bool $booted = false;
    private GridRun $trend; private GridRun $btc; private GridRun $bnb;

    public static function setUpBeforeClass(): void
    {   /* same boot block as TrendActivatorTest */ }

    protected function setUp(): void
    {
        \Propel::getConnection()->beginTransaction();
        foreach (GridRunQuery::create()->filterByStatus(['DryRun', 'Testnet', 'Live'], \Criteria::IN)->find() as $r) { $r->setStatus('Halted'); $r->save(); }
        $this->cfg('gtbot_shared_budget_quote', '1000');
        $this->cfg('gtbot_max_drawdown_pct', '0');
        $this->cfg('gtbot_routine_brief_last', '');
        $this->trend = $this->mkRun('BTCUSDT', '100', 'Trend', 5, 25);
        $this->btc = $this->mkRun('BTCUSDT', '350', 'Grid', 5, 0);
        $this->bnb = $this->mkRun('BNBUSDT', '550', 'Grid', 6, 30);
    }
    protected function tearDown(): void { \Propel::getConnection()->rollBack(); }

    private function cfg(string $k, string $v): void
    {
        $c = ConfigQuery::create()->findOneByConfig($k) ?? (new Config())->setConfig($k);
        $c->setValue($v); $c->save();
    }
    private function mkRun(...) { /* same as TrendActivatorTest::mkRun, plus ->setProfile('Balanced'), ->setPLow('90'), ->setPHigh('110'), ->setLastTickAt(date('Y-m-d H:i:s')) */ }

    private function tool(float $btcPrice = 100.0, string $t4 = 'up'): GtbotRoutineBriefTool
    {
        $sum = static fn (string $sym) => [ /* three frames as in RoutineBriefTest::sum(), 4h trend $t4, price $btcPrice for BTC, 100 for BNB */ ];
        $candles = static function (string $sym, string $tf): array { /* 60 candles 90..110 like RoutineBriefTest::candles */ };
        $price = static fn (string $sym) => $sym === 'BTCUSDT' ? (string) $btcPrice : '100';
        return new GtbotRoutineBriefTool($sum, $candles, $price);
    }

    public function testFirstBriefIsMaterialAndCoversAllRuns(): void
    {
        $b = $this->tool()->build(null);
        $this->assertTrue($b['material_change']);
        $this->assertCount(3, $b['runs']);
        $ids = array_column($b['runs'], 'id');
        $this->assertContains((int) $this->trend->getIdGridRun(), $ids);
        $byId = array_column($b['runs'], null, 'id');
        $t = $byId[(int) $this->trend->getIdGridRun()];
        $this->assertNull($t['candidate']);
        $this->assertIsArray($t['trend']);
        $this->assertContains('trend_managed', $t['flags']);
        $g = $byId[(int) $this->btc->getIdGridRun()];
        $this->assertNotNull($g['candidate']);
        $this->assertSame(50.0, $g['geometry']['pos_pct']);
        $this->assertSame('1000', $b['shared']['budget']);
        $this->assertSame('idle', $b['shared']['trend_arm']['state']);
        $this->assertLessThanOrEqual(6000, strlen(json_encode($b, JSON_UNESCAPED_SLASHES)));
    }

    public function testSecondIdenticalBriefIsQuiet(): void
    {
        $this->tool()->build(null);
        $b = $this->tool()->build(null);
        $this->assertFalse($b['material_change']);
        $this->assertSame([], $b['changes']);
        foreach ($b['runs'] as $r) { $this->assertFalse($r['attention'], 'run ' . $r['id'] . ' flags: ' . implode(',', $r['flags'])); }
    }

    public function testPriceLeavingBandIsMaterialAndFlagsTheRun(): void
    {
        $this->tool()->build(null);
        $b = $this->tool(130.0)->build(null);
        $this->assertTrue($b['material_change']);
        $byId = array_column($b['runs'], null, 'id');
        $g = $byId[(int) $this->btc->getIdGridRun()];
        $this->assertContains('outside_band', $g['flags']);
        $this->assertTrue($g['attention']);
        $this->assertNotEmpty(array_filter($b['changes'], static fn ($c) => str_contains($c, 'pos_bucket')));
    }

    public function testRunFilterNarrowsButFingerprintStillCoversAll(): void
    {
        $b = $this->tool()->build((int) $this->bnb->getIdGridRun());
        $this->assertCount(1, $b['runs']);
        $b2 = $this->tool()->build(null);
        $this->assertFalse($b2['material_change']);
    }
}
```

- [ ] **Step 2: Run → fails (class not found).**

- [ ] **Step 3: Implement the tool**

```php
<?php
namespace App\Mcp\Tools;

use ApiGoat\Sessions\AuthySession;
use App\BotDecisionQuery;
use App\BotEventQuery;
use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\DecisionScorer;
use App\Domains\Bot\DrawdownGuard;
use App\Domains\Bot\MarketCollector;
use App\Domains\Bot\MarketStore;
use App\Domains\Bot\OrderStore;
use App\Domains\Bot\RoutineBrief;
use App\Domains\Bot\SimWallet;
use App\Domains\Bot\TrendActivator;
use App\GridRunQuery;
use App\TradeCycleQuery;

class GtbotRoutineBriefTool extends AbstractGtbotBase
{
    public const FINGERPRINT_KEY = 'gtbot_routine_brief_last';
    public const QUIET_HOURS_KEY = 'gtbot_brief_max_quiet_hours';

    public function __construct(private ?\Closure $summaries = null, private ?\Closure $candles = null, private ?\Closure $price = null) {}

    public function name(): string { return 'gtbot_routine_brief'; }
    public function description(): string
    {
        return 'ONE compact read of every non-Done run for the hourly refit routine: signal digest (1h/4h/1d), '
            . 'regime class + gates, current geometry and slice, a deterministic candidate grid + deploy band, '
            . 'scored track record, per-run flags/attention, and material_change since the previous brief. '
            . 'Start here; drill into gtbot_status/gtbot_market/gtbot_decisions only for flagged runs.';
    }
    public function inputSchema(): array
    {
        return ['type' => 'object', 'properties' => ['run' => ['type' => 'integer', 'description' => 'Narrow to one run id (fingerprint still covers all runs)']]];
    }
    public function requiredRight(): ?array { return ['GridRun', 'r']; }

    protected function run(array $args, AuthySession $session): array
    {
        return $this->ok($this->build(isset($args['run']) ? (int) $args['run'] : null));
    }

    public function build(?int $onlyRun): array
    {
        $quietHours = (float) ((ConfigQuery::create()->findOneByConfig(self::QUIET_HOURS_KEY)?->getValue()) ?: RoutineBrief::DEFAULT_QUIET_HOURS);
        $prevRaw = (string) (ConfigQuery::create()->findOneByConfig(self::FINGERPRINT_KEY)?->getValue() ?? '');
        $prev = $prevRaw !== '' ? json_decode($prevRaw, true) : null;
        $prev = is_array($prev) ? $prev : null;

        $dd = DrawdownGuard::floor();
        $eq = DrawdownGuard::equity();
        $tripped = $dd !== null && bccomp($eq['equity'], $dd, 12) < 0;
        $trendRun = GridRunQuery::create()->filterByStatus('Live')->filterByAlgo('Trend')->orderByIdGridRun()->findOne();
        $lastTransition = $trendRun ? (int) (BotEventQuery::create()->filterByIdGridRun((int) $trendRun->getIdGridRun())->filterByKind([TrendActivator::KIND_ACTIVATE, TrendActivator::KIND_DEACTIVATE, TrendActivator::KIND_RELEASE], \Criteria::IN)->orderByIdBotEvent(\Criteria::DESC)->findOne()?->getIdBotEvent() ?? 0) : 0;
        $lastAlert = (int) (BotEventQuery::create()->filterByLevel(['Alert', 'Error'], \Criteria::IN)->orderByIdBotEvent(\Criteria::DESC)->findOne()?->getIdBotEvent() ?? 0);

        $fp = ['drawdown_tripped' => $tripped, 'last_alert_id' => $lastAlert, 'last_trend_transition_id' => $lastTransition, 'runs' => []];
        $runs = [];
        $sumTotal = '0';
        $cache = [];
        foreach (GridRunQuery::create()->filterByStatus(['Draft', 'Done'], \Criteria::NOT_IN)->orderByIdGridRun()->find() as $run) {
            $id = (int) $run->getIdGridRun();
            $symbol = (string) $run->getSymbol();
            $algo = (string) ($run->getAlgo() ?: 'Grid');
            $sumTotal = bcadd($sumTotal, (string) $run->getBudgetQuote(), 8);
            if (!isset($cache[$symbol])) {
                $cache[$symbol] = ['s' => $this->summariesFor($symbol), 'p' => $this->priceFor($symbol)];
            }
            $s = $cache[$symbol]['s'];
            $price = $cache[$symbol]['p'];
            $pf = $price !== null ? (float) $price : null;
            $digest = RoutineBrief::digest($s, $price);
            $regime = RoutineBrief::regime($s, $pf);
            $pLow = (float) $run->getPLow(); $pHigh = (float) $run->getPHigh();
            $pos = ($pHigh > $pLow && $pf !== null) ? round(($pf - $pLow) / ($pHigh - $pLow) * 100, 1) : null;
            $deploy = (int) ($run->getDeployPct() ?? 100);
            $profile = (string) ($run->getProfile() ?: 'Balanced');
            $hb = $this->heartbeat($run);
            $store = new OrderStore($id, (string) $run->getRunUid(), (string) ($run->getLedgerResetAt('Y-m-d H:i:s') ?? ''), (bool) $run->getSimulated());
            $lastDecision = BotDecisionQuery::create()->filterByIdGridRun($id)->orderByIdBotDecision(\Criteria::DESC)->findOne();
            $hours = $lastDecision ? (time() - strtotime($lastDecision->getDateCreation('Y-m-d H:i:s'))) / 3600 : 999.0;
            $prevClass = $prev['runs'][$id]['class'] ?? null;
            $geomHash = substr(md5(implode('|', [(string) $run->getPLow(), (string) $run->getPHigh(), (int) $run->getNLevels(), (string) $run->getSpacing()])), 0, 8);
            $fp['runs'][$id] = ['class' => $regime['class'], 'in_range' => $pos !== null && $pos > 0 && $pos < 100, 'pos_bucket' => RoutineBrief::posBucket($pos), 'deploy' => $deploy, 'slice' => bcadd((string) $run->getBudgetQuote(), '0', 0), 'profile' => $profile, 'geom' => $geomHash, 'kill' => (bool) $run->getKillSwitch(), 'hb_stale' => (bool) $hb['stale'], 'refit_pending' => $this->refitPending($run)];
            $entry = [
                'id' => $id, 'label' => (string) $run->getLabel(), 'symbol' => $symbol, 'algo' => $algo, 'status' => (string) $run->getStatus(), 'profile' => $profile, 'simulated' => (bool) $run->getSimulated(),
                'slice' => bcadd((string) $run->getBudgetQuote(), '0', 0), 'deploy_pct' => $deploy,
                'floor_slice' => (string) (50 * max(1, (int) $run->getNLevels())), 'invested' => bcadd($store->investedQuote(), '0', 2),
                'pnl' => $this->pnl($id, (bool) $run->getSimulated()),
                'geometry' => ['p_low' => (string) $run->getPLow(), 'p_high' => (string) $run->getPHigh(), 'n' => (int) $run->getNLevels(), 'spacing_pct' => $this->spacingPct($run), 'pos_pct' => $pos, 'in_range' => $fp['runs'][$id]['in_range']],
                'signal' => $digest, 'regime' => $regime,
                'candidate' => $algo === 'Trend' ? null : RoutineBrief::candidate($this->candlesFor($symbol, '4h'), (string) $run->getFeePct(), (string) $run->getBudgetQuote(), $pf, $profile, $regime, $deploy),
                'track' => $this->track($id),
                'trend' => $algo === 'Trend' ? $this->trendBlock($run) : null,
            ];
            $fl = RoutineBrief::flags(['algo' => $algo, 'kill' => (bool) $run->getKillSwitch(), 'hb_stale' => (bool) $hb['stale'], 'refit_pending' => $fp['runs'][$id]['refit_pending'], 'geometry' => $entry['geometry'], 'deploy_pct' => $deploy, 'regime' => $regime, 'signal' => $digest, 'hours_since_decision' => $hours, 'quiet_hours' => $quietHours, 'drawdown_tripped' => $tripped, 'trend_transition_since_last' => $algo === 'Trend' && $lastTransition !== (int) ($prev['last_trend_transition_id'] ?? 0), 'regime_changed' => $prevClass !== null && $prevClass !== $regime['class']]);
            $entry['flags'] = $fl['flags'];
            $entry['attention'] = $fl['attention'];
            if ($onlyRun === null || $onlyRun === $id) {
                $runs[] = $entry;
            }
        }
        $diff = RoutineBrief::diff($prev, $fp);
        $c = ConfigQuery::create()->findOneByConfig(self::FINGERPRINT_KEY) ?? (new Config())->setConfig(self::FINGERPRINT_KEY);
        $c->setValue(json_encode($fp, JSON_UNESCAPED_SLASHES));
        $c->save();
        return [
            'at' => gmdate('Y-m-d H:i') . 'Z',
            'shared' => ['budget' => SimWallet::sharedBudget(), 'allocated' => bcadd($sumTotal, '0', 0), 'equity' => bcadd($eq['equity'], '0', 2), 'drawdown' => ['floor' => $dd !== null ? bcadd($dd, '0', 2) : null, 'tripped' => $tripped], 'trend_arm' => $trendRun ? ['run' => (int) $trendRun->getIdGridRun(), 'state' => TrendActivator::state((int) $trendRun->getIdGridRun()), 'last_transition_id' => $lastTransition] : null],
            'material_change' => $diff['material'],
            'changes' => $diff['changes'],
            'runs' => $runs,
        ];
    }
    // helpers: summariesFor/priceFor/candlesFor (closures or MarketStore/MarketCollector::analysisGateway()->tickerPrice),
    // refitPending (copy of GtbotMarketTool::refitPending), spacingPct (from GridMath or applied_geometry spacing_pct if present, else null),
    // pnl(id, sim): TradeCycleQuery realized sum 7d + total + cycles_7d, rounded 2dp,
    // track(id): ['by_deploy_band' => same 3 bands as GtbotDecisionsTool but only scored/win/loss/realized, 'last3' => DecisionScorer::trackRecord($id, 3) reduced to at/deploy_pct/verdict/realized_after],
    // trendBlock(run): ['entry','qty','stop'] from engine_state + 'state' => TrendActivator::state(id).
}
```
Read `GridMath` for the spacing-pct helper name used by `GtbotStatusTool` (`applied_geometry` stamp carries `spacing_pct`); reuse it.

- [ ] **Step 4: Run tests** → PASS (4 tests). Also run `php vendor/bin/phpunit tests/Custom/Bot/McpToolsTest.php` to confirm registration still loads all tools.
- [ ] **Step 5: Commit** — `git commit -m "feat(mcp): gtbot_routine_brief — one-call routine brief with material_change"`

---

### Task 4: Prompt v2026-08-21.1 + evidence doc

**Files:**
- Modify: `.admin/docs/refit-routine.md` (rewrite, ≤1300 words)
- Create: `.admin/docs/refit-evidence.md` (the sweep narratives moved verbatim: spacing sweep, width sweep, regime sweep, deploy-policy sweep, allocation sweep, trend A/B; plus the drawdown-recovery long form)

- [ ] **Step 1: Move narratives** — cut the evidence paragraphs from steps 2–4 and 6 into `refit-evidence.md` under headings; leave one-line rule + "(evidence: refit-evidence.md §x)".
- [ ] **Step 2: Rewrite the flow**:
  - Header: `PROMPT VERSION v2026-08-21.1` (same journaling rule).
  - Step 0: call `gtbot_routine_brief` (no args). If `material_change=false` AND no run `attention=true` → report exactly one line `v2026-08-21.1 quiet — <n> runs, no material change` and STOP (no other tool calls).
  - Step 1: for each run with `attention=true` and `algo!=Trend`: read its `flags`, `regime`, `signal`, `candidate`, `track`; apply the judgment rules (fit/not-fit; profile walls; width/spacing floors as numbers; trail-up / never trail-down; re-entry gate; regime-gate override semantics); `candidate` is your starting point — deviate with a stated reason in the set_grid reason; call `gtbot_set_grid`. Drill-down tools (`gtbot_status`, `gtbot_market`, `gtbot_decisions`, `gtbot_pnl_report`) are allowed but should be rare — say why.
  - Step 2: Trend runs: report `trend` block + flags only; never set_grid/BudgetQuote/DeployPct (activator owns them).
  - Step 3: allocation — only when a flagged grid run's slice needs to change (decreases first, floors, ≤ shared; trimmed-by-activator slices are authoritative).
  - Step 4: drawdown recovery — trigger from `shared.drawdown.tripped` or flag; the 4-step procedure kept (short).
  - Report: ≤ 8 lines.
- [ ] **Step 3: Word count** — `wc -w .admin/docs/refit-routine.md` ≤ 1300.
- [ ] **Step 4: Commit** — `git commit -m "docs(routine): prompt v2026-08-21.1 around gtbot_routine_brief; evidence moved to refit-evidence.md"`

---

### Task 5: Tool-schema diet + full suite + deploy handoff

**Files:**
- Modify: `description()` in `.admin/src/App/Mcp/Tools/Gtbot{Market,Status,Decisions,PnlReport,RefitProposal,SetGrid,PreviewGrid,Events,Backtest,CreateRun,Kill,Start,Stop}Tool.php` — 1–2 sentences each; keep any phrase a test asserts on (grep `tests/` for `description()` assertions first).
- Modify: `todo.md` (operator note), memory.

- [ ] **Step 1:** `grep -rn "description()" .admin/tests | head` — note asserted substrings.
- [ ] **Step 2:** Trim each description; keep semantics ("read-only", "confirm:true", "refit_pending" hints).
- [ ] **Step 3:** `php vendor/bin/phpunit` (full) → all green.
- [ ] **Step 4:** Commit — `git commit -m "chore(mcp): trim gtbot tool descriptions (schema tokens ride on every routine turn)"`
- [ ] **Step 5:** Hand Tim: `./gc deploy apigtbot -y`, then paste `refit-routine.md` v2026-08-21.1 into the cloud routine, then one manual brief call to seed the fingerprint (or let the first routine pass do it).
