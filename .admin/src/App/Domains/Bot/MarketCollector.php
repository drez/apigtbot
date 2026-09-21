<?php

namespace App\Domains\Bot;

use App\Domains\Bot\Gateway\BinanceGateway;

/**
 * Fetches klines from the real market and stores the indicator summary +
 * recent candles per (symbol, timeframe), plus the chart's OHLCV history
 * (CandleStore, all five chart timeframes). Shared by the scheduled collector
 * cron (force refresh) and the MCP (refresh-if-stale on call) — so a tool call
 * self-heals stale data and the caller never has to fetch anything itself.
 */
final class MarketCollector
{
    /** 1w feeds MarketOutlook only (summary + regime log; never a chart
     *  timeframe — CandleStore::TF_SECONDS has no 1w, so the guard in
     *  refresh() skips it). Every other reader names the frames it wants. */
    public const INTERVALS = ['1h', '4h', '1d', '1w'];
    // 1000 so percentile-ranked metrics (atr_pct_rank, vol_zscore) compare
    // against ~6 weeks of 1h history instead of 12.5 days; MarketStore still
    // persists only its KEEP most-recent candles per row.
    private const LIMIT = 1000;

    /**
     * The symbols the machine watches: every active run's, plus BTCUSDT so a
     * fleet with no runs still has a tape. One list for the collector and
     * the outlook cron — a symbol is never "also add it over there".
     * @return string[]
     */
    public static function watchedSymbols(): array
    {
        $symbols = ['BTCUSDT'];
        foreach (\App\GridRunQuery::create()->filterByStatus(['Live', 'Testnet', 'DryRun'], \Criteria::IN)->select(['Symbol'])->find() as $symbol) {
            $symbols[] = strtoupper((string) $symbol);
        }
        return array_values(array_unique($symbols));
    }

    /** Force a fetch+store for every timeframe. Returns count stored. */
    public static function refresh(string $symbol, BinanceGateway $gw): int
    {
        $extras = self::extras($symbol, $gw);
        $n = 0;
        foreach (self::INTERVALS as $tf) {
            try {
                $candles = $gw->klines($symbol, $tf, self::LIMIT);
                // same klines feed the chart's candle history — no extra
                // call, and stored before the summary so an indicator
                // failure never costs the chart its bars
                if (isset(CandleStore::TF_SECONDS[$tf])) {
                    CandleStore::upsert($symbol, $tf, $candles);
                    CandleStore::prune($symbol, $tf);
                }
                if (count($candles) < 15) {
                    continue;
                }
                MarketStore::upsert($symbol, $tf, Indicators::summary($candles), $candles, $extras);
                $n++;
            } catch (\Throwable $e) {
                error_log("market collect $symbol $tf: " . $e->getMessage());
            }
        }
        // the chart-only timeframes (1m/5m/15m): deep fetch here so a fresh
        // install backfills history; the minute cron keeps them current
        foreach (self::candleOnlyIntervals() as $tf) {
            try {
                CandleStore::prune($symbol, $tf);
            } catch (\Throwable $e) {
                error_log("candle prune $symbol $tf: " . $e->getMessage());
            }
        }
        self::refreshCandles($symbol, $gw, self::candleOnlyIntervals(), self::LIMIT);
        return $n;
    }

    /**
     * Chart candles only — no indicator summary, no regime log, no extras.
     * The 1-minute cron runs this so the 1m/5m/15m views stay ≤1 min behind;
     * $limit bars per timeframe are fetched and upserted (the newest bar is
     * the in-progress one and is overwritten each pass). Returns bars written.
     */
    public static function refreshCandles(string $symbol, BinanceGateway $gw, array $tfs = CandleStore::INTERVALS, int $limit = 120): int
    {
        $n = 0;
        foreach ($tfs as $tf) {
            try {
                $n += CandleStore::upsert($symbol, $tf, $gw->klines($symbol, $tf, $limit));
            } catch (\Throwable $e) {
                error_log("candle collect $symbol $tf: " . $e->getMessage());
            }
        }
        return $n;
    }

    /** Candle timeframes the summary pass does not already fetch. */
    private static function candleOnlyIntervals(): array
    {
        return array_values(array_diff(CandleStore::INTERVALS, self::INTERVALS));
    }

    /**
     * Regime extras beyond candles: perp funding rate (futures premium index —
     * crowd positioning), its 30d percentile (the level carries no signal on
     * its own — the regime sweep showed the PERCENTILE is what separated
     * BTC's worst windows), and spot order-book depth imbalance (buy/sell
     * pressure). All public; failures leave nulls.
     */
    private static function extras(string $symbol, BinanceGateway $gw): array
    {
        $extras = ['funding_rate' => null, 'funding_pct' => null, 'depth_imbalance' => null];
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
            // trailing 30d of 8h prints; the premiumIndex rate above is the
            // in-force one — the history endpoint lags it by up to 8h, so
            // rank the freshest value we have within the historical prints
            $hist = $fut->publicGet('/fapi/v1/fundingRate', ['symbol' => $symbol, 'limit' => 90]);
            $rates = array_map(static fn ($r) => (float) $r['fundingRate'], array_filter((array) $hist, static fn ($r) => isset($r['fundingRate'])));
            if ($extras['funding_rate'] !== null) {
                $rates[] = $extras['funding_rate'];
            }
            $extras['funding_pct'] = self::fundingPercentile($rates);
        } catch (\Throwable) {
        }
        return $extras;
    }

    /**
     * Percentile (0..100) of the LAST rate vs the whole series (trailing 30d
     * of 8h prints). Null under 30 prints — a thin series ranks noise.
     */
    public static function fundingPercentile(array $rates): ?float
    {
        $n = count($rates);
        if ($n < 30) {
            return null;
        }
        $cur = (float) end($rates);
        $le = count(array_filter($rates, static fn ($r) => (float) $r <= $cur + 1e-12));
        return round($le / $n * 100, 1);
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
