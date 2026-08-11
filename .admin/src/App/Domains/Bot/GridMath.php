<?php

namespace App\Domains\Bot;

/**
 * Pure grid geometry + capital allocation. All prices/quantities are bcmath
 * strings — no floats in the money path (floats only seed Newton's iteration).
 */
class GridMath
{
    private const SCALE = 12;

    /**
     * Grid line prices, index 0..$n. Endpoints are exact; geometric interior
     * levels share a common ratio r = (pHigh/pLow)^(1/n).
     *
     * @return string[] n+1 ascending prices
     */
    public static function levels(string $pLow, string $pHigh, int $n, string $spacing): array
    {
        if (bccomp($pLow, '0', self::SCALE) <= 0 || bccomp($pHigh, $pLow, self::SCALE) <= 0) {
            throw new \InvalidArgumentException("invalid range [$pLow, $pHigh]");
        }
        if ($n < 2) {
            throw new \InvalidArgumentException("need at least 2 grid lines, got $n");
        }

        $levels = [$pLow];
        if ($spacing === 'Arithmetic') {
            $step = bcdiv(bcsub($pHigh, $pLow, self::SCALE), (string) $n, self::SCALE);
            for ($i = 1; $i < $n; $i++) {
                $levels[] = bcadd($pLow, bcmul($step, (string) $i, self::SCALE), self::SCALE);
            }
        } else {
            $r = self::ratio($pLow, $pHigh, $n);
            $prev = $pLow;
            for ($i = 1; $i < $n; $i++) {
                $prev = bcmul($prev, $r, self::SCALE);
                $levels[] = $prev;
            }
        }
        $levels[] = $pHigh;
        return $levels;
    }

    /** Common ratio r = (pHigh/pLow)^(1/n), by Newton's method on x^n = A. */
    public static function ratio(string $pLow, string $pHigh, int $n): string
    {
        $a = bcdiv($pHigh, $pLow, self::SCALE + 8);
        $x = (string) pow((float) $a, 1 / $n); // float seed only
        $nStr = (string) $n;
        $nMinus1 = (string) ($n - 1);
        for ($iter = 0; $iter < 50; $iter++) {
            $xPow = bcpow($x, $nMinus1, self::SCALE + 8);
            $next = bcdiv(
                bcadd(bcmul($nMinus1, $x, self::SCALE + 8), bcdiv($a, $xPow, self::SCALE + 8), self::SCALE + 8),
                $nStr,
                self::SCALE + 8
            );
            if (bccomp($next, $x, self::SCALE + 6) === 0) {
                break;
            }
            $x = $next;
        }
        return bcadd($x, '0', self::SCALE);
    }

    /**
     * Smallest per-grid relative move — the number the profitability floor
     * is checked against. Geometric: r-1 everywhere. Arithmetic: the top gap.
     */
    public static function spacingPct(string $pLow, string $pHigh, int $n, string $spacing): string
    {
        $levels = self::levels($pLow, $pHigh, $n, $spacing);
        if ($spacing === 'Arithmetic') {
            $top = count($levels) - 1;
            $gap = bcsub($levels[$top], $levels[$top - 1], self::SCALE);
            return bcdiv($gap, $levels[$top - 1], self::SCALE);
        }
        return bcsub(self::ratio($pLow, $pHigh, $n), '1', self::SCALE);
    }

    /**
     * Per-buy-level quantities for a quote budget.
     * EqualQuote:     budget/M quote each → qty_i = (budget/M)/price_i.
     * EqualBase:      same qty q at every level, q = budget / sum(price_i).
     * BottomWeighted: quote per level scales linearly 2x (bottom) → 1x (top)
     *                 — heavier accumulation near support, same total budget.
     *
     * @param string[] $buyLevelPrices ascending
     * @return string[] qty per buy level
     */
    public static function allocate(string $budgetQuote, array $buyLevelPrices, string $mode): array
    {
        $m = count($buyLevelPrices);
        if ($m === 0) {
            return [];
        }
        $qtys = [];
        if ($mode === 'EqualBase') {
            $sum = '0';
            foreach ($buyLevelPrices as $p) {
                $sum = bcadd($sum, $p, self::SCALE);
            }
            $q = bcdiv($budgetQuote, $sum, self::SCALE);
            return array_fill(0, $m, $q);
        }
        if ($mode === 'BottomWeighted') {
            // weight_i = 2 - i/(M-1)  (2 at the lowest level, 1 at the highest)
            $weights = [];
            $wsum = '0';
            for ($i = 0; $i < $m; $i++) {
                $w = $m === 1 ? '1' : bcsub('2', bcdiv((string) $i, (string) ($m - 1), self::SCALE), self::SCALE);
                $weights[] = $w;
                $wsum = bcadd($wsum, $w, self::SCALE);
            }
            foreach ($buyLevelPrices as $i => $p) {
                $quote = bcmul($budgetQuote, bcdiv($weights[$i], $wsum, self::SCALE), self::SCALE);
                $qtys[] = bcdiv($quote, $p, self::SCALE);
            }
            return $qtys;
        }
        $perLevel = bcdiv($budgetQuote, (string) $m, self::SCALE);
        foreach ($buyLevelPrices as $p) {
            $qtys[] = bcdiv($perLevel, $p, self::SCALE);
        }
        return $qtys;
    }

    /** profit ≈ spacing − 2·fee (slippage excluded; maker orders assumed). */
    public static function profitPerGridPct(string $spacingPct, string $feePct): string
    {
        return bcsub($spacingPct, bcmul('2', $feePct, self::SCALE), self::SCALE);
    }
}
