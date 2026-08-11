<?php

namespace App\Domains\Bot;

use App\Domains\Bot\Gateway\BinanceGateway;

/**
 * Fetches klines from the real market and stores the indicator summary +
 * recent candles per (symbol, timeframe). Shared by the scheduled collector
 * cron (force refresh) and the MCP (refresh-if-stale on call) — so a tool call
 * self-heals stale data and the caller never has to fetch anything itself.
 */
final class MarketCollector
{
    public const INTERVALS = ['1h', '4h', '1d'];
    // 1000 so percentile-ranked metrics (atr_pct_rank, vol_zscore) compare
    // against ~6 weeks of 1h history instead of 12.5 days; MarketStore still
    // persists only its KEEP most-recent candles per row.
    private const LIMIT = 1000;

    /** Force a fetch+store for every timeframe. Returns count stored. */
    public static function refresh(string $symbol, BinanceGateway $gw): int
    {
        $extras = self::extras($symbol, $gw);
        $n = 0;
        foreach (self::INTERVALS as $tf) {
            try {
                $candles = $gw->klines($symbol, $tf, self::LIMIT);
                if (count($candles) < 15) {
                    continue;
                }
                MarketStore::upsert($symbol, $tf, Indicators::summary($candles), $candles, $extras);
                $n++;
            } catch (\Throwable $e) {
                error_log("market collect $symbol $tf: " . $e->getMessage());
            }
        }
        return $n;
    }

    /**
     * Regime extras beyond candles: perp funding rate (futures premium index —
     * crowd positioning) and spot order-book depth imbalance (buy/sell
     * pressure). Both public; failures leave nulls.
     */
    private static function extras(string $symbol, BinanceGateway $gw): array
    {
        $extras = ['funding_rate' => null, 'depth_imbalance' => null];
        try {
            $extras['depth_imbalance'] = $gw->depthImbalance($symbol);
        } catch (\Throwable) {
        }
        try {
            $fut = new BinanceGateway((string) env('GTBOT_FUTURES_BASE', 'https://fapi.binance.com'), '', '');
            $pi = $fut->publicGet('/fapi/v1/premiumIndex', ['symbol' => $symbol]);
            if (isset($pi['lastFundingRate'])) {
                $extras['funding_rate'] = (float) $pi['lastFundingRate'];
            }
        } catch (\Throwable) {
        }
        return $extras;
    }

    /**
     * Refresh only if the stored data is missing or older than $maxAge seconds.
     * Returns true if a refresh ran.
     */
    public static function refreshIfStale(string $symbol, BinanceGateway $gw, int $maxAge = 900): bool
    {
        $sums = MarketStore::summaries($symbol, $maxAge);
        $needsRefresh = count($sums) < count(self::INTERVALS);
        foreach ($sums as $s) {
            if (!empty($s['stale'])) {
                $needsRefresh = true;
                break;
            }
        }
        if ($needsRefresh) {
            self::refresh($symbol, $gw);
            return true;
        }
        return false;
    }

    /** Default gateway for real-market analysis (public mainnet klines). */
    public static function analysisGateway(): BinanceGateway
    {
        $base = (string) env('GTBOT_ANALYSIS_BASE', 'https://api.binance.com');
        return new BinanceGateway($base, '', '');
    }
}
