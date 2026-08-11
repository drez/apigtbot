<?php

namespace App\Domains\Bot;

/**
 * Fits candidate grid ranges to recent price history. This is
 * PARAMETERIZATION, not prediction: it derives [p_low, p_high] envelopes from
 * realized quantiles, drops envelopes too narrow to survive one adverse leg
 * (half-width < 4 × ATR) and picks the level count whose spacing lands
 * nearest the width sweep's ~1.7% optimum without breaching the 1.3% churn
 * floor. Which candidate (if any) to trade stays a human/backtest judgment.
 * Deterministic and pure — selection by sorted index; float arithmetic only
 * in the ATR width filter, never in the money path.
 */
class RangeFitter
{
    public const MAX_LEVELS = 40;
    /**
     * Spacing floor and target from the width sweep (scripts/width-sweep.php,
     * 187d × 4 pairs): buckets under ~1.3% spacing won 0% of walk-forward
     * windows (fee churn eats every cycle), ~1.7% was the optimum. The old
     * floor (3 × round-trip fee = 0.6%) sat squarely in the catastrophic
     * bucket, and "densest ladder allowed" pinned every candidate to it.
     */
    public const SPACING_FLOOR = '0.013';
    public const SPACING_TARGET = '0.017';
    /**
     * Half-width floor in ATRs (same-timeframe candles): the sweep's surviving
     * grids kept half-width ≥ ~4× ATR; narrower envelopes get killed by one
     * adverse leg (run 1 bit -165 on 07-22, run 6 -137 on 07-28).
     */
    public const HALF_WIDTH_ATR_FLOOR = '4';
    /** Envelope quantiles per candidate: [low-quantile %, high-quantile %]. */
    private const ENVELOPES = [
        'wide' => [2.5, 97.5],
        'medium' => [10.0, 90.0],
        'tight' => [20.0, 80.0],
    ];

    /**
     * @param array  $candles [['low' => str, 'high' => str, 'close' => str], ...]
     * @return array<int, array{name:string, p_low:string, p_high:string, n_levels:int, spacing_pct:string}>
     */
    public static function candidates(array $candles, string $feePct, string $budgetQuote): array
    {
        if (count($candles) < 10) {
            throw new \InvalidArgumentException('need at least 10 candles to fit a range');
        }
        $lows = array_column($candles, 'low');
        $highs = array_column($candles, 'high');
        $bcsort = static function (array &$a): void {
            usort($a, static fn (string $x, string $y): int => bccomp($x, $y, 12));
        };
        $bcsort($lows);
        $bcsort($highs);

        // spacing floor: fee viability AND the sweep's 1.3% churn floor
        $feeFloor = bcmul('6', $feePct, 12);
        $floor = bccomp($feeFloor, self::SPACING_FLOOR, 12) > 0 ? $feeFloor : self::SPACING_FLOOR;

        // half-width floor: 4 × ATR over the same candles (0 disables, e.g. < period+1 candles)
        $atr = Indicators::atr(array_column($candles, 'high'), array_column($candles, 'low'), array_column($candles, 'close'));
        $minHalfWidth = bcmul(self::HALF_WIDTH_ATR_FLOOR, sprintf('%.12F', $atr), 12);

        $out = [];
        foreach (self::ENVELOPES as $name => [$lq, $hq]) {
            $pLow = self::quantile($lows, $lq);
            $pHigh = self::quantile($highs, $hq);
            if (bccomp($pLow, '0', 12) <= 0 || bccomp($pHigh, $pLow, 12) <= 0) {
                continue; // flat/degenerate market — no viable envelope
            }
            $halfWidth = bcdiv(bcsub($pHigh, $pLow, 12), '2', 12);
            if (bccomp($halfWidth, $minHalfWidth, 12) < 0) {
                continue; // too narrow to survive one adverse leg — width-sweep floor
            }
            $n = self::bestLevels($pLow, $pHigh, $floor);
            if ($n < 2) {
                continue;
            }
            $out[] = [
                'name' => $name,
                'p_low' => bcadd($pLow, '0', 2),
                'p_high' => bcadd($pHigh, '0', 2),
                'n_levels' => $n,
                'spacing_pct' => GridMath::spacingPct($pLow, $pHigh, $n, 'Geometric'),
            ];
        }
        return $out;
    }

    /** Nearest-rank quantile on a bc-sorted list (index selection, no interpolation). */
    private static function quantile(array $sorted, float $pct): string
    {
        $idx = (int) floor(($pct / 100) * (count($sorted) - 1));
        return $sorted[$idx];
    }

    /**
     * n (≤ MAX_LEVELS) whose geometric spacing lands closest to the sweep's
     * ~1.7% optimum while clearing the floor. Spacing shrinks monotonically
     * with n, so walk up until it drops under the floor and keep the closest.
     */
    private static function bestLevels(string $pLow, string $pHigh, string $floor): int
    {
        $best = 0;
        $bestDist = null;
        for ($n = 2; $n <= self::MAX_LEVELS; $n++) {
            $spacing = GridMath::spacingPct($pLow, $pHigh, $n, 'Geometric');
            if (bccomp($spacing, $floor, 12) < 0) {
                break;
            }
            $d = bcsub($spacing, self::SPACING_TARGET, 12);
            if (bccomp($d, '0', 12) < 0) {
                $d = bcmul($d, '-1', 12);
            }
            if ($bestDist === null || bccomp($d, $bestDist, 12) < 0) {
                $best = $n;
                $bestDist = $d;
            }
        }
        return $best;
    }
}
