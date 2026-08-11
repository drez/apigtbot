<?php

namespace App\Domains\Bot;

/**
 * Hostile-regime exposure cap — the regime-sweep's one cross-symbol winner
 * (scripts/regime-sweep.php, BTC+BNB 187d 15m, width-sweep protocol): a grid
 * (re)deployed while the 4h fit-window trend is down-family OR 4h ADX ≥ 30
 * loses on average and owns every catastrophic window; skipping exactly those
 * windows improved mean net on both symbols and cut the worst window by
 * ~78–88% (BTC −112.75→−25.03, BNB −66.44→−7.92 per $1k/7d). Single signals
 * (trend alone, ER, choppiness, funding) flipped sign between symbols and
 * must NOT gate — only this composite survived.
 *
 * The gate CAPS deploy_pct at MAX_HOSTILE_DEPLOY_PCT at geometry-write time
 * (routine's gtbot_set_grid + the dead-man cron refit). It never raises
 * exposure, never touches a running grid between writes, and fails OPEN when
 * the signal is missing/stale — the sweep scored null signals as "trade",
 * and a data outage must not strand the routine at minimum size.
 */
final class RegimeGate
{
    /**
     * Sweep skip-rows model 0% deployed; 25 keeps a quarter ladder working
     * (sells/legacy exits are never constrained) while staying within the
     * band the sweep showed is net-negative to over-deploy into.
     */
    public const MAX_HOSTILE_DEPLOY_PCT = 25;
    public const ADX_HOSTILE = 30.0;

    /** Down-family 4h trend OR trending-hard tape (ADX ≥ 30). Nulls fail open. */
    public static function hostile(?string $trend4h, ?float $adx4h): bool
    {
        if (in_array($trend4h, ['down', 'strong_down'], true)) {
            return true;
        }
        return $adx4h !== null && $adx4h >= self::ADX_HOSTILE;
    }

    /**
     * Clamp a requested deploy_pct for the current regime.
     * @return array{pct:int, capped:bool, why:?string}
     */
    public static function capDeployPct(int $requested, ?string $trend4h, ?float $adx4h): array
    {
        if ($requested <= self::MAX_HOSTILE_DEPLOY_PCT || !self::hostile($trend4h, $adx4h)) {
            return ['pct' => $requested, 'capped' => false, 'why' => null];
        }
        $why = sprintf(
            'hostile regime (4h trend %s, ADX %s) — deploy_pct capped %d → %d by the regime-sweep gate (down|adx>=30 windows average a loss; sells keep working)',
            $trend4h ?? '?',
            $adx4h !== null ? number_format($adx4h, 1) : '?',
            $requested,
            self::MAX_HOSTILE_DEPLOY_PCT
        );
        return ['pct' => self::MAX_HOSTILE_DEPLOY_PCT, 'capped' => true, 'why' => $why];
    }
}
