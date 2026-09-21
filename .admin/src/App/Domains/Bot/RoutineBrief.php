<?php

namespace App\Domains\Bot;

/**
 * Server-side computations behind the gtbot_routine_brief MCP tool — the
 * hourly refit routine's ONE read: a compact signal digest, the regime read,
 * a deterministic candidate grid + deploy band, per-run flags/attention,
 * and the "did anything material change since the last brief" fingerprint.
 * Pure functions; GtbotRoutineBriefTool feeds them from the DB.
 *
 * Purpose (2026-08-20): cut the routine's token spend. Each tool turn
 * re-sends the whole context, so ~12–15 turns/hour (status + market +
 * decisions + pnl per run) cost ~10× a 1–2 turn pass. With this brief a
 * quiet hour is one call + a one-line report; a busy hour is the brief +
 * set_grid on the flagged runs only. The judgment stays with Claude.
 */
final class RoutineBrief
{
    /** deploy_pct walls per risk profile (refit-routine.md step 3b) */
    public const PROFILE_WALLS = ['NoLoss' => 30, 'Cautious' => 40, 'Balanced' => 70, 'Aggressive' => 100, 'Max' => 100];
    /** within this % of a bound counts as "near" (routine: trail-up / re-centre territory) */
    public const NEAR_BOUND_PCT = 10.0;
    /** a grid run with no set_grid decision for this long gets attention anyway */
    public const DEFAULT_QUIET_HOURS = 6;

    // ── signal digest ───────────────────────────────────────────────────

    /**
     * Only the numbers the routine's rules actually read, rounded to 2dp.
     * @param array $s MarketStore::summaries() ('1h'/'4h'/'1d' => row)
     */
    public static function digest(array $s, ?string $price): array
    {
        $r2 = static fn ($v) => $v === null ? null : round((float) $v, 2);
        $frame = static function (string $tf, array $map) use ($s, $r2): ?array {
            if (!isset($s[$tf])) {
                return null;
            }
            $out = [];
            foreach ($map as $key => $src) {
                $out[$key] = $src === 'trend' ? (string) ($s[$tf]['trend'] ?? '') : $r2($s[$tf][$src] ?? null);
            }
            return $out;
        };
        $stale = false;
        $age = null;
        foreach (['1h', '4h', '1d'] as $tf) {
            if (!isset($s[$tf]) || !empty($s[$tf]['stale'])) {
                $stale = true;
            }
            $a = $s[$tf]['age_seconds'] ?? null;
            if ($a !== null && ($age === null || (int) $a > $age)) {
                $age = (int) $a;
            }
        }
        return [
            'price' => $r2($price),
            '1h' => $frame('1h', ['trend' => 'trend', 'rsi' => 'rsi14', 'atr_pct' => 'atr_pct', 'ema20' => 'ema20', 'ema50' => 'ema50']),
            '4h' => $frame('4h', ['trend' => 'trend', 'adx' => 'adx14', 'er20' => 'er20', 'chop' => 'chop14', 'atr_pct' => 'atr_pct', 'atr_rank' => 'atr_pct_rank', 'ema20' => 'ema20', 'ema50' => 'ema50', 'ema200' => 'ema200']),
            '1d' => $frame('1d', ['trend' => 'trend', 'rsi' => 'rsi14', 'ema20' => 'ema20', 'ema50' => 'ema50', 'ema200' => 'ema200']),
            'age_s' => $age,
            'stale' => $stale,
        ];
    }

    // ── regime ──────────────────────────────────────────────────────────

    /**
     * class = TrendRegime (sweep classification, 1d-EMA-reclaim aware);
     * hostile_cap = RegimeGate's deploy cap when the 4h tape is hostile;
     * reentry_gate = the routine's "1d label is stale" rule: price above the
     * 1d EMA20 AND EMA50 with 4h and 1h up-family.
     */
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

    // ── deploy band + candidate ─────────────────────────────────────────

    /** @return array{0:int,1:int,2:string} lo, hi, why */
    public static function deployBand(string $profile, array $regime, int $currentDeploy): array
    {
        $wall = self::PROFILE_WALLS[$profile] ?? 70;
        if (($regime['hostile_cap'] ?? null) !== null) {
            $hi = min($wall, (int) $regime['hostile_cap']);
            if (($regime['class'] ?? null) === 'TREND_UP') {
                return [0, $hi, "TREND_UP but 4h ADX >= 30: set_grid's regime gate caps at $hi — pass override_regime_gate:true with a thesis to go higher"];
            }
            return [0, $hi, "hostile regime: gate caps at $hi, prefer 0"];
        }
        if (!empty($regime['reentry_gate']) && $currentDeploy === 0) {
            return [min(30, $wall), min(40, $wall), 're-entry gate fired while flat: re-enter at the conflicting-TF band'];
        }
        $why = "$profile walls";
        if (($regime['class'] ?? null) === 'TREND_UP') {
            $why .= '; TREND_UP — lean the range up, the trend arm rides the leg';
        }
        return [0, $wall, $why];
    }

    /**
     * RangeFitter's best envelope (spacing ≥ 1.3%, half-width ≥ 4×ATR of the
     * candles' timeframe — the sweeps' floors) by in-sample backtest, shifted
     * to bracket price if it doesn't, with the deploy band attached.
     * Deterministic; the routine may deviate but must say why.
     */
    public static function candidate(array $candles, string $feePct, string $slice, ?float $price, string $profile, array $regime, int $currentDeploy): ?array
    {
        if (count($candles) < 10 || $price === null) {
            return null;
        }
        try {
            $cands = RangeFitter::candidates($candles, $feePct, $slice);
        } catch (\Throwable) {
            return null;
        }
        if ($cands === []) {
            return null;
        }
        $closes = array_map(static fn ($c) => (string) $c['close'], $candles);
        $best = null;
        $bestPnl = null;
        foreach ($cands as $c) {
            $cfg = [
                'p_low' => $c['p_low'], 'p_high' => $c['p_high'], 'n_levels' => (int) $c['n_levels'],
                'spacing' => 'Geometric', 'allocation' => 'EqualQuote', 'budget_quote' => $slice, 'fee_pct' => $feePct,
                'min_notional' => '5', 'max_position_quote' => $slice, 'max_order_quote' => $slice,
                'daily_loss_limit_quote' => $slice, 'breakout_buffer_pct' => '0.02', 'breakout_policy' => 'HaltAndHold', 'max_open_orders' => 60,
            ];
            try {
                $pnl = (float) Backtester::run($cfg, $closes)['realized_pnl'];
            } catch (\Throwable) {
                continue;
            }
            if ($bestPnl === null || $pnl > $bestPnl) {
                $bestPnl = $pnl;
                $best = $c;
            }
        }
        if ($best === null) {
            return null;
        }
        $why = $best['name'] . ' envelope (fitter floors applied)';
        $lo = (float) $best['p_low'];
        $hi = (float) $best['p_high'];
        if ($price <= $lo || $price >= $hi) {
            $half = ($hi - $lo) / 2;
            $lo = $price - $half;
            $hi = $price + $half;
            $why .= ', shifted to bracket price (was outside)';
        }
        [$bLo, $bHi, $bWhy] = self::deployBand($profile, $regime, $currentDeploy);
        return [
            'p_low' => number_format($lo, 2, '.', ''),
            'p_high' => number_format($hi, 2, '.', ''),
            'n_levels' => (int) $best['n_levels'],
            'spacing_pct' => number_format((float) $best['spacing_pct'] * 100, 2, '.', ''),
            'deploy_band' => [$bLo, $bHi],
            'why' => $why . '; ' . $bWhy,
        ];
    }

    // ── flags / attention ───────────────────────────────────────────────

    public static function posBucket(?float $pos): string
    {
        if ($pos === null) {
            return 'na';
        }
        if ($pos < 0) {
            return 'below';
        }
        if ($pos < self::NEAR_BOUND_PCT) {
            return 'low';
        }
        if ($pos <= 100 - self::NEAR_BOUND_PCT) {
            return 'mid';
        }
        if ($pos <= 100) {
            return 'high';
        }
        return 'above';
    }

    /**
     * Flags the routine reads at a glance; attention = this run needs a
     * decision this pass. Informational flags (refit_pending, hostile while
     * already flat, trend_managed) never raise attention on their own.
     *
     * @return array{flags: string[], attention: bool}
     */
    public static function flags(array $r): array
    {
        $f = [];
        $attention = false;
        $mark = static function (string $flag, bool $att = true) use (&$f, &$attention): void {
            $f[] = $flag;
            if ($att) {
                $attention = true;
            }
        };
        if (!empty($r['kill'])) {
            $mark('kill');
        }
        if (!empty($r['held'])) {
            $mark('held', false); // parked by the operator: no daemon by design, nothing to act on
        } elseif (!empty($r['hb_stale'])) {
            $mark('heartbeat_stale');
        }
        if (!empty($r['refit_pending']) && ($r['algo'] ?? 'Grid') !== 'Trend') {
            $mark('refit_pending', false); // a Trend run's applied_geometry stamp is not a ladder — meaningless there
        }
        if (!empty($r['drawdown_tripped'])) {
            $mark('drawdown_tripped');
        }
        if (!empty($r['signal']['stale'])) {
            $mark('data_stale');
        }
        if (($r['algo'] ?? 'Grid') === 'Trend') {
            $mark('trend_managed', false);
            if (!empty($r['trend_transition_since_last'])) {
                $mark('trend_transition');
            }
            return ['flags' => $f, 'attention' => $attention];
        }
        $bucket = self::posBucket($r['geometry']['pos_pct'] ?? null);
        if ($bucket === 'below' || $bucket === 'above') {
            $mark('outside_band');
        } elseif ($bucket === 'low' || $bucket === 'high') {
            $mark('near_bound');
        }
        if (!empty($r['regime_changed'])) {
            $mark('regime_changed');
        }
        if (($r['regime']['hostile_cap'] ?? null) !== null) {
            $mark('hostile', false);
            if ((int) ($r['deploy_pct'] ?? 0) > (int) $r['regime']['hostile_cap']) {
                $mark('hostile_overdeployed');
            }
        }
        if (!empty($r['regime']['reentry_gate']) && (int) ($r['deploy_pct'] ?? 0) === 0) {
            $mark('reentry_gate');
        }
        if ((float) ($r['hours_since_decision'] ?? 0) > (float) ($r['quiet_hours'] ?? self::DEFAULT_QUIET_HOURS)) {
            $mark('quiet_too_long');
        }
        return ['flags' => $f, 'attention' => $attention];
    }

    // ── fingerprint diff ────────────────────────────────────────────────

    /**
     * @param array|null $prev stored fingerprint from the previous brief (null = first call)
     * @param array      $cur  {drawdown_tripped, last_alert_id, last_trend_transition_id, runs: {id: {...}}}
     * @return array{material: bool, changes: string[]}
     */
    public static function diff(?array $prev, array $cur): array
    {
        if ($prev === null) {
            return ['material' => true, 'changes' => ['first brief — no previous fingerprint']];
        }
        $changes = [];
        $prevRuns = is_array($prev['runs'] ?? null) ? $prev['runs'] : [];
        foreach ($cur['runs'] as $id => $c) {
            if (!isset($prevRuns[$id])) {
                $changes[] = "run $id joined the pool";
                continue;
            }
            foreach ($c as $k => $v) {
                $pv = $prevRuns[$id][$k] ?? null;
                if ($pv !== $v) {
                    $changes[] = sprintf('run %s: %s %s→%s', $id, $k, self::fmtv($pv), self::fmtv($v));
                }
            }
        }
        foreach (array_keys($prevRuns) as $id) {
            if (!isset($cur['runs'][$id])) {
                $changes[] = "run $id left the pool";
            }
        }
        if ((int) ($prev['last_alert_id'] ?? 0) !== (int) ($cur['last_alert_id'] ?? 0)) {
            $changes[] = 'new Alert/Error events since last brief';
        }
        if ((int) ($prev['last_trend_transition_id'] ?? 0) !== (int) ($cur['last_trend_transition_id'] ?? 0)) {
            $changes[] = 'trend arm transition since last brief';
        }
        if ((bool) ($prev['drawdown_tripped'] ?? false) !== (bool) ($cur['drawdown_tripped'] ?? false)) {
            $changes[] = 'drawdown state changed';
        }
        // The long-horizon outlook is CONTEXT (refit-evidence §16: detection,
        // no forecast skill): a change is reported, but it is never material —
        // it must not wake a quiet routine into refitting. Compared only when
        // the previous fingerprint carried the key, so the first brief after
        // the deploy does not announce every symbol.
        $context = [];
        if (is_array($prev['outlook'] ?? null)) {
            foreach (($cur['outlook'] ?? []) as $symbol => $v) {
                $pv = $prev['outlook'][$symbol] ?? null;
                if ($pv !== $v) {
                    $context[] = sprintf('outlook %s: %s→%s (context only)', $symbol, self::fmtv($pv), self::fmtv($v));
                }
            }
        }
        return ['material' => $changes !== [], 'changes' => array_merge($changes, $context)];
    }

    private static function fmtv($v): string
    {
        if (is_bool($v)) {
            return $v ? 'true' : 'false';
        }
        return (string) ($v ?? 'null');
    }
}
