<?php

namespace App\Domains\Bot;

use App\ConfigQuery;

/**
 * Mechanical grid geometry (2026-09-09): re-anchor a fixed-shape ladder
 * around price on a clock or when price leaves the range, instead of an
 * hourly LLM refit. The 3-month replay (Jun–Sep 2026, scripts in the session
 * scratchpad, report "Summer Replay") had a ±6% × 8 ladder re-anchored every
 * 3 days earn +13.5 (BNB) / +17.4 (BTC) per 400 slice — level with or above
 * the routine's 116 refits on the same tape, with a third of the refits.
 *
 * Pure decision + candidate math; bin/gtbot-refit reads the config, feeds
 * the 4h candles it already fetched and does the writing. Config
 * (gtbot_grid_refit_mode: routine|mechanical) defaults to routine so an
 * unseeded install keeps today's behaviour.
 */
final class MechanicalRefit
{
    public const CONFIG_MODE = 'gtbot_grid_refit_mode';
    public const CONFIG_HALF_WIDTH = 'gtbot_grid_half_width_pct';
    public const CONFIG_LEVELS = 'gtbot_grid_levels';
    public const CONFIG_HOURS = 'gtbot_grid_refit_hours';

    public const MODE_ROUTINE = 'routine';
    public const MODE_MECHANICAL = 'mechanical';

    public const DEFAULT_HALF_WIDTH_PCT = '6';
    public const DEFAULT_LEVELS = 6;
    public const DEFAULT_HOURS = 72;
    /**
     * Ceiling on the half-width, as a fraction of price: a 4h ATR of 25%+
     * would widen the ladder past p_low = 0 (4 × ATR = 100%+), the daemon
     * refuses non-positive bounds and the hourly pass would die uncaught.
     * 0.49 keeps p_low at 51% of price — a ±49% ladder is already far wider
     * than any sweep-tested envelope and stays positive.
     */
    public const MAX_HALF_WIDTH = '0.49';

    private const SCALE = 12;

    // ── config ──────────────────────────────────────────────────────────

    public static function mode(): string
    {
        $v = strtolower(trim((string) (self::config(self::CONFIG_MODE) ?? '')));
        return $v === self::MODE_MECHANICAL ? self::MODE_MECHANICAL : self::MODE_ROUTINE;
    }

    public static function isMechanical(): bool
    {
        return self::mode() === self::MODE_MECHANICAL;
    }

    /** half-width as a fraction ('0.06') */
    public static function halfWidth(): string
    {
        $pct = self::config(self::CONFIG_HALF_WIDTH);
        $pct = $pct !== null && is_numeric($pct) && (float) $pct > 0 ? (string) $pct : self::DEFAULT_HALF_WIDTH_PCT;
        return bcdiv($pct, '100', self::SCALE);
    }

    public static function levels(): int
    {
        $n = self::config(self::CONFIG_LEVELS);
        return $n !== null && (int) $n >= 2 ? (int) $n : self::DEFAULT_LEVELS;
    }

    public static function refitHours(): int
    {
        $h = self::config(self::CONFIG_HOURS);
        return $h !== null && (int) $h > 0 ? (int) $h : self::DEFAULT_HOURS;
    }

    private static function config(string $key): ?string
    {
        // select() raw rows, never pooled objects: the daemon is long-lived and
        // Propel never re-hydrates an instance already in its pool, so a
        // hydrated ConfigQuery would freeze the mode at boot and a mode flip
        // would be invisible until restart (same trap BudgetGuard had).
        // Selecting one column yields a PropelArrayCollection of scalar values.
        $v = null;
        foreach (ConfigQuery::create()->filterByConfig($key)->select(['Value'])->limit(1)->find() as $row) {
            $v = is_array($row) ? ($row['Value'] ?? null) : $row;
            break;
        }
        return $v === null || trim((string) $v) === '' ? null : (string) $v;
    }

    // ── pure ────────────────────────────────────────────────────────────

    /**
     * The ladder around $price: half-width = max(configured, 4 × ATR% — the
     * width sweep's survival floor, same constant RangeFitter uses), level
     * count reduced until the geometric spacing clears RangeFitter's 1.3%
     * churn floor. Bounds rounded to cents (both prod symbols tick at 0.01).
     *
     * @param string $atrPct 4h ATR as a fraction of price (0.013 = 1.3%); 0 when unknown
     * @return array{name:string, p_low:string, p_high:string, n_levels:int, spacing_pct:string, half_width:string, widened:bool}
     */
    public static function candidate(string $price, string $atrPct, string $halfWidth, int $levels): array
    {
        if (bccomp($price, '0', self::SCALE) <= 0) {
            throw new \InvalidArgumentException('price must be positive');
        }
        $floorHalf = bcmul(RangeFitter::HALF_WIDTH_ATR_FLOOR, $atrPct, self::SCALE);
        $widened = bccomp($floorHalf, $halfWidth, self::SCALE) > 0;
        $h = $widened ? $floorHalf : $halfWidth;
        if (bccomp($h, self::MAX_HALF_WIDTH, self::SCALE) > 0) {
            $h = self::MAX_HALF_WIDTH;
            $widened = true; // clamped, not the raw 4×ATR — still wider than configured
        }

        $pLow = bcmul($price, bcsub('1', $h, self::SCALE), 2);
        $pHigh = bcmul($price, bcadd('1', $h, self::SCALE), 2);

        // n ≤ ln((1+h)/(1−h)) / ln(1+floor) keeps every rung ≥ the spacing floor
        $ratio = (float) bcdiv(bcadd('1', $h, self::SCALE), bcsub('1', $h, self::SCALE), self::SCALE);
        $maxN = (int) floor(log($ratio) / log(1 + (float) RangeFitter::SPACING_FLOOR));
        $n = max(2, min($levels, $maxN));

        return [
            'name' => 'mechanical',
            'p_low' => $pLow,
            'p_high' => $pHigh,
            'n_levels' => $n,
            'spacing_pct' => GridMath::spacingPct($pLow, $pHigh, $n, 'Geometric'),
            'half_width' => $h,
            'widened' => $widened,
        ];
    }

    /**
     * Highest level count whose per-level deployed quote still clears the
     * exchange minimum notional — the daemon refuses a ladder where
     * deployed_quote / n_levels < min_notional, so a config that asks for
     * more rungs than the slice can fund must never be written to the row.
     *
     * @param string $deployedQuote budget_quote × deploy_pct (0 = flat)
     * @return int 0 when no level can be funded
     */
    public static function clampLevels(int $levels, string $deployedQuote, string $minNotional): int
    {
        if ($levels < 2 || bccomp($deployedQuote, '0', self::SCALE) <= 0 || bccomp($minNotional, '0', self::SCALE) <= 0) {
            return 0;
        }
        $maxN = (int) bcdiv($deployedQuote, $minNotional, 0);
        return max(0, min($levels, $maxN));
    }

    /**
     * Is a re-anchor due? Yes when price is outside the active range or the
     * clock since the last applied geometry has run out — except that a
     * re-anchor that would sit LOWER than the current range in a 4h
     * down-family trend is held (width sweep: mechanical down-chasing
     * realizes the drawdown and re-arms buys lower; exits keep working and
     * the next up-side pass re-centres).
     *
     * @param array{p_low:string, p_high:string} $active
     * @return array{due:bool, reason:string}
     */
    public static function due(array $active, string $price, ?string $lastAppliedAt, string $now, int $refitHours, ?string $trend4h): array
    {
        $below = bccomp($price, $active['p_low'], self::SCALE) < 0;
        $above = bccomp($price, $active['p_high'], self::SCALE) > 0;
        $mid = bcdiv(bcadd($active['p_low'], $active['p_high'], self::SCALE), '2', self::SCALE);
        $lower = bccomp($price, $mid, self::SCALE) < 0;
        $down = in_array($trend4h, ['down', 'strong_down'], true);

        $elapsedH = $lastAppliedAt === null ? null : (strtotime($now) - strtotime($lastAppliedAt)) / 3600;
        $clock = $elapsedH === null || $elapsedH >= $refitHours;

        if (!$below && !$above && !$clock) {
            return ['due' => false, 'reason' => sprintf('price %s inside [%s, %s], %.1fh of %dh since the last geometry', $price, $active['p_low'], $active['p_high'], $elapsedH, $refitHours)];
        }
        if ($lower && $down) {
            return ['due' => false, 'reason' => sprintf('price %s %s the range in a 4h %s — holding rather than re-anchoring lower (exits keep working)', $price, $below ? 'below' : 'in the lower half of', $trend4h)];
        }
        if ($below || $above) {
            return ['due' => true, 'reason' => sprintf('price %s %s the active range [%s, %s]', $price, $below ? 'below' : 'above', $active['p_low'], $active['p_high'])];
        }
        return ['due' => true, 'reason' => $elapsedH === null
            ? 'no applied geometry on record — first mechanical anchor'
            : sprintf('%.1fh since the last geometry (clock %dh)', $elapsedH, $refitHours)];
    }
}
