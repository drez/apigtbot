<?php

namespace App\Domains\Bot;

/**
 * Pure decision: given the active run's range, the current price, and the
 * viable RangeFitter candidates, decide whether to re-fit the grid and to
 * what. Re-fits only when the price is near/outside the current bounds (the
 * grid is about to go idle or underwater) and a candidate actually brackets
 * the price — otherwise holds. Deterministic; no side effects.
 *
 * Grid-only: a Trend run has no p_low/p_high geometry to fit, so
 * bin/gtbot-refit filters run.algo === 'Trend' out BEFORE calling decide() —
 * this class never sees a Trend run's range.
 */
final class RefitPlanner
{
    private const SCALE = 12;
    /**
     * Trigger a refit once price is within this fraction of the RANGE WIDTH
     * from a bound. A fraction of the bound itself (the old rule) degenerates
     * on narrow grids: at 2%-of-bound, a [64200, 66800] grid's two triggers
     * OVERLAP and every price reads "near a bound" — the cron then thrashes
     * any tight grid. Width-based margins can never overlap.
     */
    private const NEAR_FRAC = '0.15';
    /** Treat a candidate as "same as active" within this fraction on both bounds. */
    private const SAME_RANGE = '0.01';

    /**
     * @param array{p_low:string, p_high:string} $active
     * @param array<int, array{name:string, p_low:string, p_high:string, n_levels:int, spacing_pct:string}> $candidates
     * @param ?string $trend4h Indicators::trend() over 4h closes, if the
     *        caller has it. Down-family trend blocks LOW-side re-centers:
     *        the width sweep (scripts/width-sweep.php, 187d × 4 pairs)
     *        measured mechanical down-chasing as the worst behavior tested —
     *        each re-center realizes the drawdown and re-arms buys lower.
     *        Up-side re-centers book profit and are never blocked.
     * @return array{action:string, candidate:?array, reason:string}
     */
    public static function decide(array $active, string $price, array $candidates, ?string $trend4h = null): array
    {
        $margin = bcmul(bcsub($active['p_high'], $active['p_low'], self::SCALE), self::NEAR_FRAC, self::SCALE);
        $lowTrigger = bcadd($active['p_low'], $margin, self::SCALE);
        $highTrigger = bcsub($active['p_high'], $margin, self::SCALE);
        $nearLow = bccomp($price, $lowTrigger, self::SCALE) <= 0;
        $nearHigh = bccomp($price, $highTrigger, self::SCALE) >= 0;

        if (!$nearLow && !$nearHigh) {
            return ['action' => 'hold', 'candidate' => null, 'reason' => 'price is comfortably inside the active range'];
        }

        if ($nearLow && !$nearHigh && in_array($trend4h, ['down', 'strong_down'], true)) {
            return [
                'action' => 'hold',
                'candidate' => null,
                'reason' => sprintf('price %s left the range low side in a 4h %s — holding instead of chasing the downtrend (exits keep working; the routine re-deploys when the regime turns)', $price, $trend4h === 'strong_down' ? 'strong downtrend' : 'downtrend'),
            ];
        }

        // candidates that strictly bracket the current price
        $bracket = array_values(array_filter($candidates, static fn ($c) => bccomp($c['p_low'], $price, self::SCALE) < 0 && bccomp($c['p_high'], $price, self::SCALE) > 0));
        if (!$bracket) {
            return ['action' => 'hold', 'candidate' => null, 'reason' => 'no viable candidate brackets the current price'];
        }

        // most centered on price; tie → tighter range (more cycles)
        usort($bracket, static function ($a, $b) use ($price): int {
            $da = self::absDist(self::mid($a), $price);
            $db = self::absDist(self::mid($b), $price);
            $c = bccomp($da, $db, self::SCALE);
            if ($c !== 0) {
                return $c;
            }
            return bccomp(self::width($a), self::width($b), self::SCALE);
        });
        $chosen = $bracket[0];

        $lowSame = bccomp(self::absDist($chosen['p_low'], $active['p_low']), bcmul($active['p_low'], self::SAME_RANGE, self::SCALE), self::SCALE) <= 0;
        $highSame = bccomp(self::absDist($chosen['p_high'], $active['p_high']), bcmul($active['p_high'], self::SAME_RANGE, self::SCALE), self::SCALE) <= 0;
        if ($lowSame && $highSame) {
            return ['action' => 'hold', 'candidate' => $chosen, 'reason' => 'best candidate is already ≈ the active range'];
        }

        return [
            'action' => 'refit',
            'candidate' => $chosen,
            'reason' => sprintf('price %s near/outside [%s, %s]; re-centering on %s [%s, %s]', $price, $active['p_low'], $active['p_high'], $chosen['name'], $chosen['p_low'], $chosen['p_high']),
        ];
    }

    private static function mid(array $c): string
    {
        return bcdiv(bcadd($c['p_low'], $c['p_high'], self::SCALE), '2', self::SCALE);
    }

    private static function width(array $c): string
    {
        return bcsub($c['p_high'], $c['p_low'], self::SCALE);
    }

    private static function absDist(string $a, string $b): string
    {
        $d = bcsub($a, $b, self::SCALE);
        return bccomp($d, '0', self::SCALE) < 0 ? bcmul($d, '-1', self::SCALE) : $d;
    }
}
