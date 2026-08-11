<?php

namespace App\Domains\Bot;

/**
 * Technical indicators over a candle series — the signal layer Claude reasons
 * over to form a directional/regime view before choosing a grid. These are
 * ADVISORY analytics (floats are fine here); the grid geometry Claude then
 * sets is still validated in bcmath. Pure, deterministic, testable.
 */
final class Indicators
{
    /** Exponential moving average; returns the last value. */
    public static function ema(array $values, int $period): float
    {
        $values = array_values(array_map('floatval', $values));
        $n = count($values);
        if ($n === 0) {
            return 0.0;
        }
        if ($n < $period) {
            return array_sum($values) / $n;
        }
        $k = 2.0 / ($period + 1);
        $ema = array_sum(array_slice($values, 0, $period)) / $period; // seed = SMA
        for ($i = $period; $i < $n; $i++) {
            $ema = $values[$i] * $k + $ema * (1 - $k);
        }
        return $ema;
    }

    /** Wilder's RSI; returns the last value (0..100). */
    public static function rsi(array $closes, int $period = 14): float
    {
        $closes = array_values(array_map('floatval', $closes));
        $n = count($closes);
        if ($n <= $period) {
            return 50.0;
        }
        $gain = 0.0;
        $loss = 0.0;
        for ($i = 1; $i <= $period; $i++) {
            $d = $closes[$i] - $closes[$i - 1];
            $gain += max($d, 0.0);
            $loss += max(-$d, 0.0);
        }
        $avgGain = $gain / $period;
        $avgLoss = $loss / $period;
        for ($i = $period + 1; $i < $n; $i++) {
            $d = $closes[$i] - $closes[$i - 1];
            $avgGain = ($avgGain * ($period - 1) + max($d, 0.0)) / $period;
            $avgLoss = ($avgLoss * ($period - 1) + max(-$d, 0.0)) / $period;
        }
        if ($avgLoss == 0.0) {
            return 100.0;
        }
        $rs = $avgGain / $avgLoss;
        return 100.0 - (100.0 / (1.0 + $rs));
    }

    /** Wilder's ATR (average true range); returns the last value. */
    public static function atr(array $highs, array $lows, array $closes, int $period = 14): float
    {
        $highs = array_values(array_map('floatval', $highs));
        $lows = array_values(array_map('floatval', $lows));
        $closes = array_values(array_map('floatval', $closes));
        $n = count($closes);
        if ($n < 2) {
            return 0.0;
        }
        $tr = [];
        for ($i = 1; $i < $n; $i++) {
            $tr[] = max(
                $highs[$i] - $lows[$i],
                abs($highs[$i] - $closes[$i - 1]),
                abs($lows[$i] - $closes[$i - 1])
            );
        }
        $m = count($tr);
        if ($m < $period) {
            return array_sum($tr) / max(1, $m);
        }
        $atr = array_sum(array_slice($tr, 0, $period)) / $period;
        for ($i = $period; $i < $m; $i++) {
            $atr = ($atr * ($period - 1) + $tr[$i]) / $period;
        }
        return $atr;
    }

    /** Trend from the EMA stack: strong_up|up|sideways|down|strong_down. */
    public static function trend(array $closes): string
    {
        $e20 = self::ema($closes, 20);
        $e50 = self::ema($closes, 50);
        $e200 = self::ema($closes, 200);
        $eps = max(1e-9, abs($e50) * 0.001); // ignore sub-0.1% noise
        if ($e20 - $e50 > $eps && $e50 - $e200 > $eps) {
            return 'strong_up';
        }
        if ($e50 - $e20 > $eps && $e200 - $e50 > $eps) {
            return 'strong_down';
        }
        if ($e20 - $e50 > $eps) {
            return 'up';
        }
        if ($e50 - $e20 > $eps) {
            return 'down';
        }
        return 'sideways';
    }

    /**
     * Wilder's ADX (trend strength, 0..100): the regime gauge for a grid —
     * low ADX = ranging (grid-friendly), high ADX = trending (grid hostile
     * unless the range leans with the trend). Returns 0 when the series is
     * too short to smooth (2×period + 1 candles needed).
     */
    public static function adx(array $highs, array $lows, array $closes, int $period = 14): float
    {
        $highs = array_values(array_map('floatval', $highs));
        $lows = array_values(array_map('floatval', $lows));
        $closes = array_values(array_map('floatval', $closes));
        $n = count($closes);
        if ($n < 2 * $period + 1) {
            return 0.0;
        }
        $plusDM = [];
        $minusDM = [];
        $tr = [];
        for ($i = 1; $i < $n; $i++) {
            $up = $highs[$i] - $highs[$i - 1];
            $down = $lows[$i - 1] - $lows[$i];
            $plusDM[] = ($up > $down && $up > 0) ? $up : 0.0;
            $minusDM[] = ($down > $up && $down > 0) ? $down : 0.0;
            $tr[] = max(
                $highs[$i] - $lows[$i],
                abs($highs[$i] - $closes[$i - 1]),
                abs($lows[$i] - $closes[$i - 1])
            );
        }
        $m = count($tr);
        $sTr = array_sum(array_slice($tr, 0, $period));
        $sPlus = array_sum(array_slice($plusDM, 0, $period));
        $sMinus = array_sum(array_slice($minusDM, 0, $period));
        $dx = [];
        for ($i = $period; ; $i++) {
            $plusDi = $sTr > 0 ? 100.0 * $sPlus / $sTr : 0.0;
            $minusDi = $sTr > 0 ? 100.0 * $sMinus / $sTr : 0.0;
            $sum = $plusDi + $minusDi;
            $dx[] = $sum > 0 ? 100.0 * abs($plusDi - $minusDi) / $sum : 0.0;
            if ($i >= $m) {
                break;
            }
            $sTr = $sTr - $sTr / $period + $tr[$i];
            $sPlus = $sPlus - $sPlus / $period + $plusDM[$i];
            $sMinus = $sMinus - $sMinus / $period + $minusDM[$i];
        }
        if (count($dx) < $period) {
            return 0.0;
        }
        $adx = array_sum(array_slice($dx, 0, $period)) / $period;
        $k = count($dx);
        for ($i = $period; $i < $k; $i++) {
            $adx = ($adx * ($period - 1) + $dx[$i]) / $period;
        }
        return $adx;
    }

    /**
     * Percentile rank (0..100) of the CURRENT ATR% within the series' own
     * rolling ATR% history — "is volatility spiking or normal for this
     * market", where a bare atr_pct is just a guess. 100 = as volatile as
     * it has been in the window; near 0 = calmest. Null when too short.
     */
    public static function atrPctRank(array $highs, array $lows, array $closes, int $period = 14): ?float
    {
        $highs = array_values(array_map('floatval', $highs));
        $lows = array_values(array_map('floatval', $lows));
        $closes = array_values(array_map('floatval', $closes));
        $n = count($closes);
        if ($n < $period * 2) {
            return null;
        }
        $tr = [];
        for ($i = 1; $i < $n; $i++) {
            $tr[] = max(
                $highs[$i] - $lows[$i],
                abs($highs[$i] - $closes[$i - 1]),
                abs($lows[$i] - $closes[$i - 1])
            );
        }
        $atr = array_sum(array_slice($tr, 0, $period)) / $period;
        $series = [];
        $m = count($tr);
        for ($i = $period; $i <= $m; $i++) {
            $close = $closes[min($i, $n - 1)];
            $series[] = $close > 0 ? $atr / $close * 100.0 : 0.0;
            if ($i === $m) {
                break;
            }
            $atr = ($atr * ($period - 1) + $tr[$i]) / $period;
        }
        $last = end($series);
        $le = 0;
        foreach ($series as $v) {
            if ($v <= $last + 1e-12) {
                $le++;
            }
        }
        return round($le / count($series) * 100.0, 1);
    }

    /**
     * Share of traded volume that was taker (aggressive) BUYING over the last
     * $lookback candles — >0.5 means buyers crossing the spread dominate.
     * Null when the candles carry no volume data.
     */
    public static function takerBuyRatio(array $candles, int $lookback = 20): ?float
    {
        $slice = array_slice($candles, -$lookback);
        $vol = 0.0;
        $taker = 0.0;
        foreach ($slice as $c) {
            if (!isset($c['volume'], $c['taker_buy']) || $c['volume'] === null || $c['taker_buy'] === null) {
                return null;
            }
            $vol += (float) $c['volume'];
            $taker += (float) $c['taker_buy'];
        }
        return $vol > 0 ? round($taker / $vol, 4) : null;
    }

    /**
     * Z-score of the LAST candle's volume vs the prior candles (up to
     * $lookback) — how unusual current participation is. Null when volume
     * data is missing, the history is too short, or variance is ~0.
     */
    public static function volZscore(array $candles, int $lookback = 100): ?float
    {
        $vols = [];
        foreach ($candles as $c) {
            if (!isset($c['volume']) || $c['volume'] === null) {
                return null;
            }
            $vols[] = (float) $c['volume'];
        }
        $n = count($vols);
        if ($n < 21) {
            return null;
        }
        $last = $vols[$n - 1];
        $prior = array_slice($vols, max(0, $n - 1 - $lookback), min($lookback, $n - 1));
        $mean = array_sum($prior) / count($prior);
        $var = 0.0;
        foreach ($prior as $v) {
            $var += ($v - $mean) ** 2;
        }
        $std = sqrt($var / count($prior));
        return $std > 1e-9 ? round(($last - $mean) / $std, 2) : null;
    }

    public static function swingHigh(array $values, int $lookback): float
    {
        $slice = array_slice(array_map('floatval', $values), -$lookback);
        return $slice ? max($slice) : 0.0;
    }

    public static function swingLow(array $values, int $lookback): float
    {
        $slice = array_slice(array_map('floatval', $values), -$lookback);
        return $slice ? min($slice) : 0.0;
    }

    /**
     * All signals for one candle series.
     * @param array<int, array{high:mixed, low:mixed, close:mixed}> $candles
     */
    public static function summary(array $candles, int $swingLookback = 30): array
    {
        $closes = array_column($candles, 'close');
        $highs = array_column($candles, 'high');
        $lows = array_column($candles, 'low');
        $price = $closes ? (float) end($closes) : 0.0;
        $atr = self::atr($highs, $lows, $closes, 14);
        return [
            'price' => $price,
            'ema20' => round(self::ema($closes, 20), 2),
            'ema50' => round(self::ema($closes, 50), 2),
            'ema200' => round(self::ema($closes, 200), 2),
            'rsi14' => round(self::rsi($closes, 14), 1),
            'atr14' => round($atr, 2),
            'atr_pct' => $price > 0 ? round($atr / $price * 100, 2) : 0.0,
            'trend' => self::trend($closes),
            'swing_high' => round(self::swingHigh($highs, $swingLookback), 2),
            'swing_low' => round(self::swingLow($lows, $swingLookback), 2),
            'adx14' => round(self::adx($highs, $lows, $closes, 14), 1),
            'atr_pct_rank' => self::atrPctRank($highs, $lows, $closes, 14),
            'taker_buy_ratio' => self::takerBuyRatio($candles),
            'vol_zscore' => self::volZscore($candles),
        ];
    }
}
