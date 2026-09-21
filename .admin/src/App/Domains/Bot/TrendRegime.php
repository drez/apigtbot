<?php

namespace App\Domains\Bot;

/**
 * TREND_UP activation classifier for the machine-scaled trend arm
 * (bin/gtbot-trend-activate → TrendActivator).
 *
 * The classification is the allocation sweep's (scripts/allocation-sweep.php
 * ::classify — HOSTILE → TREND_UP → RANGE → MIXED, fit-time 4h signals
 * + 1d alignment) with ONE change: the 1d alignment term also accepts the
 * routine's re-entry gate. The 1d trend label is EMA200-anchored and reads
 * "strong_down" for months after a deep drawdown while price actually bases
 * and turns (BTC 2026-08-19: 4h strong_up, 1h strong_up, 1d strong_down,
 * price above both the 1d EMA20 and EMA50 on a +6% breakout day) — so
 * "price > 1d EMA20 AND > 1d EMA50" counts as 1d-up. Everything else is
 * the sweep's rule verbatim: adx14(4h) >= 25 AND er20(4h) >= 0.35.
 *
 * Caveat carried from the sweep: the rule has never been validated on a
 * tape containing a bull leg (zero TREND_UP windows in 187d + the 8-day
 * live A/B). Operator decision 2026-08-19: scale the trend arm on it
 * anyway, machine-driven (no human in the loop).
 *
 * Hysteresis, two layers (2026-08-28: an arm active for 75 minutes was
 * deactivated by ADX ticking 40.2 → 39.7 with ER 0.13 — a knife-edge on
 * ADX_STRONG, not a regime end):
 *   - thresholds: an OPEN arm ($holding) keeps its TREND_UP verdict down to
 *     ADX_STRONG_HOLD / ER_HOLD instead of the entry thresholds;
 *   - passes: decide() needs CONFIRM_PASSES consecutive TREND_UP to
 *     activate (passes are >= 15 min apart) and DEACTIVATE_PASSES
 *     consecutive non-TREND_UP — or a single HOSTILE — to deactivate.
 */
final class TrendRegime
{
    public const ADX_TREND = 25.0;
    public const ER_TREND = 0.35;
    /** a 4h strong_up label at this ADX is an unambiguous impulse — the ER
     *  gate is waived (BTC 2026-08-22→25 ran ADX 57–59 with er20 0.07–0.32
     *  through a +14% leg and never confirmed under the ER-only rule) */
    public const ADX_STRONG = 40.0;
    /** hold-side thresholds for an OPEN arm: stay in until the impulse has
     *  genuinely faded, not on the first sub-threshold print */
    public const ADX_STRONG_HOLD = 35.0;
    public const ER_HOLD = 0.25;
    public const ADX_HOSTILE = 30.0;
    /** 1d override (2026-09-02): the 4h rule read MIXED all day on 4h
     *  strong_up / ADX 22 / ER 0.12 while the 1d ran ADX 44, ER 0.51 through
     *  a 62.5k → 81.5k leg the arm never joined. A strong DAILY trend — the
     *  timeframe the leg actually lives on — satisfies the gate on its own
     *  (both 1d gates, AND); HOSTILE still outranks it. Hold-side pair
     *  mirrors the 4h hysteresis. */
    public const ADX_TREND_1D = 35.0;
    public const ER_TREND_1D = 0.30;
    public const ADX_HOLD_1D = 30.0;
    public const ER_HOLD_1D = 0.20;
    public const CHOP_RANGE = 55.0;
    public const ADX_RANGE = 20.0;
    /** A 4h 'down' label whose EMA20/EMA50 sit closer together than this
     *  fraction of EMA50 is a hairline crossing, not a downtrend.
     *  Indicators::trend flips the label at 0.1% of separation; run 1 sat at
     *  0.15% on 2026-09-18 (EMA20 77196.39 vs EMA50 77315.84 — 119 points)
     *  while the 1d ran strong_up ADX 40.8 and price made 80k. A single
     *  HOSTILE pass deactivates the arm, so that rounding-scale difference
     *  parked the BTC arm through the whole 77k → 80k leg. Only relaxed with
     *  the daily behind it — see decisiveDown. */
    public const DOWN_SEPARATION_PCT = 0.005;

    /** Consecutive TREND_UP passes required to activate. */
    public const CONFIRM_PASSES = 2;
    /** Consecutive non-TREND_UP passes required to deactivate (HOSTILE needs one). */
    public const DEACTIVATE_PASSES = 4;

    public static function family(?string $trend): string
    {
        return in_array($trend, ['strong_up', 'up'], true) ? 'up'
            : (in_array($trend, ['strong_down', 'down'], true) ? 'down' : 'side');
    }

    /**
     * @param array|null $s4  MarketStore::summaries()[ '4h' ] (trend, adx14, er20, chop14, stale)
     * @param array|null $s1d MarketStore::summaries()[ '1d' ] (trend, price, ema20, ema50, stale)
     * @param bool $holding the arm is active — use the hold-side ADX/ER thresholds
     * @return string|null HOSTILE | TREND_UP | RANGE | MIXED; null when either summary is missing/stale
     */
    public static function classify(?array $s4, ?array $s1d, bool $holding = false): ?string
    {
        if ($s4 === null || $s1d === null || !empty($s4['stale']) || !empty($s1d['stale'])) {
            return null;
        }
        $trend4 = (string) ($s4['trend'] ?? '');
        $adx4 = $s4['adx14'] !== null ? (float) $s4['adx14'] : null;
        $er4 = isset($s4['er20']) ? (float) $s4['er20'] : null;
        $chop4 = isset($s4['chop14']) ? (float) $s4['chop14'] : null;

        $upAligned = self::family($trend4) === 'up' && self::oneDayUp($s1d);
        if (self::decisiveDown($s4, $s1d) || ($adx4 !== null && $adx4 >= self::ADX_HOSTILE && !$upAligned)) {
            return 'HOSTILE';
        }
        if (self::oneDayUp($s1d) && self::oneDayStrong($s1d, $holding)) {
            return 'TREND_UP';
        }
        $erGate = $holding ? self::ER_HOLD : self::ER_TREND;
        $adxStrong = $holding ? self::ADX_STRONG_HOLD : self::ADX_STRONG;
        if ($upAligned && $adx4 !== null && $adx4 >= self::ADX_TREND
            && (($er4 !== null && $er4 >= $erGate)
                || ($trend4 === 'strong_up' && $adx4 >= $adxStrong))) {
            return 'TREND_UP';
        }
        if (($chop4 !== null && $chop4 >= self::CHOP_RANGE)
            || ($adx4 !== null && $adx4 < self::ADX_RANGE && self::family($trend4) === 'side')) {
            return 'RANGE';
        }
        return 'MIXED';
    }

    /**
     * Is this 4h 'down' label a real downtrend, or the EMAs brushing past
     * each other? Only a hairline cross WITH the daily behind it is excused:
     *   - not a down family at all        → nothing to decide
     *   - strong_down                     → decisive (EMA20 < EMA50 < EMA200)
     *   - the 1d is not up                → decisive (nothing vouches for it)
     *   - EMA pair >= DOWN_SEPARATION_PCT → decisive (a real separation)
     *   - EMAs missing from the summary   → decisive (cannot prove shallow)
     * Everything else is a crossing inside the noise band of a daily uptrend,
     * and HOSTILE — which kills the arm on one pass — is too strong a verdict
     * for it. The ADX >= ADX_HOSTILE clause is deliberately NOT relaxed: that
     * much directional strength is real whatever the EMAs are doing.
     */
    public static function decisiveDown(?array $s4, ?array $s1d): bool
    {
        if ($s4 === null) {
            return false;
        }
        $trend4 = (string) ($s4['trend'] ?? '');
        if (self::family($trend4) !== 'down') {
            return false;
        }
        if ($trend4 === 'strong_down' || $s1d === null || !self::oneDayUp($s1d)) {
            return true;
        }
        $sep = self::emaSeparationPct($s4);
        return $sep === null || $sep >= self::DOWN_SEPARATION_PCT;
    }

    /** |EMA50 - EMA20| as a fraction of EMA50; null when either is absent. */
    public static function emaSeparationPct(array $s): ?float
    {
        $e20 = $s['ema20'] ?? null;
        $e50 = $s['ema50'] ?? null;
        if ($e20 === null || $e50 === null || (float) $e50 == 0.0) {
            return null;
        }
        return abs((float) $e50 - (float) $e20) / abs((float) $e50);
    }

    /** 1d ADX and ER both over the (entry or hold-side) daily gates; false
     *  when either is missing (summaries predating the adx/er columns). */
    public static function oneDayStrong(array $s1d, bool $holding = false): bool
    {
        $adx = isset($s1d['adx14']) ? (float) $s1d['adx14'] : null;
        $er = isset($s1d['er20']) ? (float) $s1d['er20'] : null;
        if ($adx === null || $er === null) {
            return false;
        }
        return $adx >= ($holding ? self::ADX_HOLD_1D : self::ADX_TREND_1D)
            && $er >= ($holding ? self::ER_HOLD_1D : self::ER_TREND_1D);
    }

    /** 1d up-family label, OR the re-entry gate: price above both the 1d EMA20 and EMA50. */
    public static function oneDayUp(array $s1d): bool
    {
        if (self::family((string) ($s1d['trend'] ?? '')) === 'up') {
            return true;
        }
        $price = $s1d['price'] ?? null;
        $e20 = $s1d['ema20'] ?? null;
        $e50 = $s1d['ema50'] ?? null;
        if ($price === null || $e20 === null || $e50 === null) {
            return false;
        }
        return (float) $price > (float) $e20 && (float) $price > (float) $e50;
    }

    /**
     * Hysteresis over the verdict history (NEWEST FIRST; null verdicts —
     * stale passes — must already be excluded by the caller).
     *
     * @param string   $state   idle | active | winding_down
     * @param string[] $history newest-first verdicts
     * @return string activate | deactivate | hold
     */
    public static function decide(string $state, array $history): string
    {
        $newest = $history[0] ?? null;
        $confirmed = static function (callable $pred, int $passes) use ($history): bool {
            if (count($history) < $passes) {
                return false;
            }
            for ($i = 0; $i < $passes; $i++) {
                if (!$pred($history[$i])) {
                    return false;
                }
            }
            return true;
        };
        if ($state === 'active') {
            if ($newest === 'HOSTILE') {
                return 'deactivate';
            }
            return $confirmed(static fn ($v) => $v !== 'TREND_UP', self::DEACTIVATE_PASSES) ? 'deactivate' : 'hold';
        }
        // idle or winding_down: (re)arm on a confirmed TREND_UP
        return $confirmed(static fn ($v) => $v === 'TREND_UP', self::CONFIRM_PASSES) ? 'activate' : 'hold';
    }
}
