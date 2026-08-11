<?php

namespace App\Domains\Bot;

use App\GridRun;

/**
 * Single source of the per-profile risk numbers (spec:
 * docs/superpowers/specs/2026-08-10-run-risk-profile-design.md). The profile
 * is a LIVE policy: caps are ratios of the run's CURRENT budget slice and are
 * stamped onto the grid_run row (daemon each tick, service wrapper on save)
 * so every existing consumer keeps reading the same columns. NoLoss's
 * daily-loss cap only catches fee/slippage dust — nothing in a NoLoss run may
 * realize a loss on purpose (allowsRealizedLoss gates every such path).
 */
final class ProfilePolicy
{
    private const SCALE = 8;

    public const PROFILES = ['NoLoss', 'Cautious', 'Balanced', 'Aggressive', 'Max'];

    /** profile => [daily-loss ratio, unrealized-stop ratio|null, breakout policy] */
    private const RULES = [
        'NoLoss'     => ['0.02', null,   'HaltAndHold'],
        'Cautious'   => ['0.03', '0.10', 'HaltAndHold'],
        'Balanced'   => ['0.06', '0.15', 'HaltAndHold'],
        'Aggressive' => ['0.10', '0.25', 'Flatten'],
        'Max'        => ['0.15', '0.35', 'Flatten'],
    ];

    /** profile => [trail stop ×ATR, initial stop ×ATR, re-entry cooldown bars] */
    private const TREND_RULES = [
        'NoLoss'     => [null,     null,     null],
        'Cautious'   => ['2.0000', '1.5000', 6],
        'Balanced'   => ['3.0000', '2.0000', 3],
        'Aggressive' => ['4.0000', '2.5000', 1],
        'Max'        => ['5.0000', '3.0000', 0],
    ];

    private const MAX_POSITION_RATIO = '1.00';
    private const MAX_ORDER_RATIO = '0.30';

    /** @return array{DailyLossLimitQuote:string, MaxUnrealizedLossQuote:?string, MaxPositionQuote:string, MaxOrderQuote:string, BreakoutPolicy:string} */
    public static function caps(string $profile, string $sliceQuote): array
    {
        if (!isset(self::RULES[$profile])) {
            throw new \InvalidArgumentException("unknown risk profile: $profile");
        }
        [$daily, $unrealized, $breakout] = self::RULES[$profile];
        return [
            'DailyLossLimitQuote' => bcmul($sliceQuote, $daily, self::SCALE),
            'MaxUnrealizedLossQuote' => $unrealized === null ? null : bcmul($sliceQuote, $unrealized, self::SCALE),
            'MaxPositionQuote' => bcmul($sliceQuote, self::MAX_POSITION_RATIO, self::SCALE),
            'MaxOrderQuote' => bcmul($sliceQuote, self::MAX_ORDER_RATIO, self::SCALE),
            'BreakoutPolicy' => $breakout,
        ];
    }

    /** Only NoLoss forbids realizing losses (flatten/selloff/breakout-Flatten). */
    public static function allowsRealizedLoss(string $profile): bool
    {
        if (!isset(self::RULES[$profile])) {
            throw new \InvalidArgumentException("unknown risk profile: $profile");
        }
        return $profile !== 'NoLoss';
    }

    /** @return array{AtrStopMult:?string, AtrInitialMult:?string, ReentryCooldown:?int} */
    public static function trendCaps(string $profile): array
    {
        if (!isset(self::TREND_RULES[$profile])) {
            throw new \InvalidArgumentException("unknown risk profile: $profile");
        }
        [$stop, $initial, $cooldown] = self::TREND_RULES[$profile];
        return ['AtrStopMult' => $stop, 'AtrInitialMult' => $initial, 'ReentryCooldown' => $cooldown];
    }

    /** Stamp derived caps onto the row. Returns true when a value changed. Does NOT save. */
    public static function apply(GridRun $run): bool
    {
        $profile = (string) ($run->getProfile() ?: 'Balanced');
        $caps = self::caps($profile, (string) $run->getBudgetQuote());
        $trendCaps = self::trendCaps($profile);
        $changed = false;

        $fieldMap = [
            'DailyLossLimitQuote' => ['get' => 'getDailyLossLimitQuote', 'set' => 'setDailyLossLimitQuote'],
            'MaxUnrealizedLossQuote' => ['get' => 'getMaxUnrealizedLossQuote', 'set' => 'setMaxUnrealizedLossQuote'],
            'MaxPositionQuote' => ['get' => 'getMaxPositionQuote', 'set' => 'setMaxPositionQuote'],
            'MaxOrderQuote' => ['get' => 'getMaxOrderQuote', 'set' => 'setMaxOrderQuote'],
            'BreakoutPolicy' => ['get' => 'getBreakoutPolicy', 'set' => 'setBreakoutPolicy'],
            'AtrStopMult' => ['get' => 'getAtrStopMult', 'set' => 'setAtrStopMult'],
            'AtrInitialMult' => ['get' => 'getAtrInitialMult', 'set' => 'setAtrInitialMult'],
            'ReentryCooldown' => ['get' => 'getReentryCooldown', 'set' => 'setReentryCooldown'],
        ];

        // Merge trend caps into caps for uniform processing
        $allCaps = array_merge($caps, $trendCaps);

        foreach ($allCaps as $phpName => $value) {
            if (!isset($fieldMap[$phpName])) {
                continue;
            }
            $getter = $fieldMap[$phpName]['get'];
            $setter = $fieldMap[$phpName]['set'];
            $current = $run->$getter();

            // For ReentryCooldown, compare as int; for others use sameValue
            if ($phpName === 'ReentryCooldown') {
                $isSame = $value === null && $current === null ? true : ($value === null || $current === null ? false : (int) $current === (int) $value);
            } else {
                $isSame = self::sameValue($current, $value);
            }

            if (!$isSame) {
                $run->$setter($value);
                $changed = true;
            }
        }
        return $changed;
    }

    /**
     * Compare field values accounting for null, numeric, and string differences.
     * Returns true if values are equivalent, false otherwise.
     */
    private static function sameValue($current, $value): bool
    {
        if ($value === null && $current === null) {
            return true;
        }
        if ($value === null || $current === null) {
            return false;
        }
        // For non-numeric values (like BreakoutPolicy), use string comparison
        if (!is_numeric($value)) {
            return (string) $current === (string) $value;
        }
        // For numeric values, use bccomp for precision
        return bccomp((string) $current, $value, self::SCALE) === 0;
    }
}
