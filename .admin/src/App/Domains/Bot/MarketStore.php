<?php

namespace App\Domains\Bot;

use App\MarketSummary;
use App\MarketSummaryQuery;

/**
 * Persisted market data: the collector cron fetches klines, computes the
 * indicator summary + keeps the recent candle series, and stores one row per
 * (symbol, timeframe). The MCP then reads all of this from the DB and only
 * fetches the current live price from the exchange — so tool calls don't hit
 * Binance for history.
 */
final class MarketStore
{
    /** How many recent candles to keep per row (enough to recompute/backtest). */
    private const KEEP = 300;

    /**
     * Upsert the summary + recent candles for one (symbol, tf).
     * @param array $summary  Indicators::summary() output
     * @param array<int, array{high:mixed, low:mixed, close:mixed}> $candles
     */
    public static function upsert(string $symbol, string $tf, array $summary, array $candles, ?array $extras = null): void
    {
        $row = MarketSummaryQuery::create()
            ->filterBySymbol($symbol)
            ->filterByTf($tf)
            ->findOne() ?? new MarketSummary();
        $row->setSymbol($symbol);
        $row->setTf($tf);
        $row->setPrice((string) $summary['price']);
        $row->setEma20((string) $summary['ema20']);
        $row->setEma50((string) $summary['ema50']);
        $row->setEma200((string) $summary['ema200']);
        $row->setRsi14((string) $summary['rsi14']);
        $row->setAtr14((string) $summary['atr14']);
        $row->setAtrPct((string) $summary['atr_pct']);
        $row->setTrend((string) $summary['trend']);
        $row->setSwingHigh((string) $summary['swing_high']);
        $row->setSwingLow((string) $summary['swing_low']);
        $row->setAdx14(isset($summary['adx14']) ? (string) $summary['adx14'] : null);
        $row->setAtrPctRank(isset($summary['atr_pct_rank']) ? (string) $summary['atr_pct_rank'] : null);
        $row->setTakerBuyRatio(isset($summary['taker_buy_ratio']) ? (string) $summary['taker_buy_ratio'] : null);
        $row->setVolZscore(isset($summary['vol_zscore']) ? (string) $summary['vol_zscore'] : null);

        $compact = array_map(
            static fn ($c) => [(string) $c['high'], (string) $c['low'], (string) $c['close']],
            array_slice($candles, -self::KEEP)
        );
        $row->setCandlesUsed(count($compact));
        $row->setRecentCandles(json_encode($compact, JSON_UNESCAPED_SLASHES));
        if ($extras !== null) {
            $row->setFundingRate($extras['funding_rate'] ?? null);
            $di = $extras['depth_imbalance'] ?? null;
            $row->setDepthImbalance($di);
            if ($di !== null) {
                // one order-book snapshot is noise — keep an EMA across
                // collections so the routine reads sustained pressure
                $prev = $row->getDepthImbalanceAvg();
                $row->setDepthImbalanceAvg($prev === null
                    ? (string) $di
                    : (string) round(0.7 * (float) $prev + 0.3 * (float) $di, 4));
            }
        }
        $row->setComputedAt(date('Y-m-d H:i:s'));
        $row->save();

        // Append-only regime history. Depth/funding snapshots have no
        // historical API — if they aren't logged at collection time the
        // series is gone for good, and the routine's regime reads can never
        // be validated against outcomes. Pruned by gtbot-refit housekeeping.
        try {
            $log = new \App\MarketRegime();
            $log->setSymbol($symbol);
            $log->setTf($tf);
            $log->setPrice((string) $summary['price']);
            $log->setTrend((string) $summary['trend']);
            $log->setRsi14((string) $summary['rsi14']);
            $log->setAtrPct((string) $summary['atr_pct']);
            $log->setAdx14(isset($summary['adx14']) ? (string) $summary['adx14'] : null);
            $log->setAtrPctRank(isset($summary['atr_pct_rank']) ? (string) $summary['atr_pct_rank'] : null);
            $log->setTakerBuyRatio(isset($summary['taker_buy_ratio']) ? (string) $summary['taker_buy_ratio'] : null);
            $log->setVolZscore(isset($summary['vol_zscore']) ? (string) $summary['vol_zscore'] : null);
            $log->setFundingRate($extras['funding_rate'] ?? null);
            $log->setDepthImbalance($extras['depth_imbalance'] ?? null);
            $log->setDepthImbalanceAvg($row->getDepthImbalanceAvg());
            $log->save();
        } catch (\Throwable $e) {
            error_log("market regime log $symbol $tf: " . $e->getMessage());
        }
    }

    /**
     * All stored summaries for a symbol, keyed by timeframe, with freshness.
     * @return array<string, array>
     */
    public static function summaries(string $symbol, int $staleAfter = 1800): array
    {
        $out = [];
        foreach (MarketSummaryQuery::create()->filterBySymbol($symbol)->find() as $r) {
            $computed = $r->getComputedAt('Y-m-d H:i:s');
            $age = $computed ? (time() - strtotime($computed)) : null;
            $out[(string) $r->getTf()] = [
                'price' => self::num($r->getPrice()),
                'ema20' => self::num($r->getEma20()),
                'ema50' => self::num($r->getEma50()),
                'ema200' => self::num($r->getEma200()),
                'rsi14' => self::num($r->getRsi14()),
                'atr14' => self::num($r->getAtr14()),
                'atr_pct' => self::num($r->getAtrPct()),
                'trend' => (string) $r->getTrend(),
                'swing_high' => self::num($r->getSwingHigh()),
                'swing_low' => self::num($r->getSwingLow()),
                'candles_used' => (int) $r->getCandlesUsed(),
                'adx14' => self::num($r->getAdx14()),
                'atr_pct_rank' => self::num($r->getAtrPctRank()),
                'taker_buy_ratio' => self::num($r->getTakerBuyRatio()),
                'vol_zscore' => self::num($r->getVolZscore()),
                'funding_rate' => $r->getFundingRate() !== null ? (float) $r->getFundingRate() : null,
                'depth_imbalance' => $r->getDepthImbalance() !== null ? (float) $r->getDepthImbalance() : null,
                'depth_imbalance_avg' => self::num($r->getDepthImbalanceAvg()),
                'computed_at' => $computed,
                'age_seconds' => $age,
                'stale' => $age === null || $age > $staleAfter,
            ];
        }
        return $out;
    }

    /**
     * Recent candles for one (symbol, tf) from the DB.
     * @return array<int, array{high:string, low:string, close:string}>
     */
    public static function candles(string $symbol, string $tf): array
    {
        $r = MarketSummaryQuery::create()->filterBySymbol($symbol)->filterByTf($tf)->findOne();
        if (!$r || !$r->getRecentCandles()) {
            return [];
        }
        $raw = json_decode((string) $r->getRecentCandles(), true);
        if (!is_array($raw)) {
            return [];
        }
        return array_map(static fn ($c) => ['high' => (string) $c[0], 'low' => (string) $c[1], 'close' => (string) $c[2]], $raw);
    }

    private static function num($v): ?float
    {
        return $v === null ? null : (float) $v;
    }
}
