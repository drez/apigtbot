<?php

namespace App\Domains\Bot;

use App\MarketSummary;
use App\MarketSummaryPeer;
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
        // Hydrate the row as it actually IS, not as this process last left it.
        // Propel serves findOne() from its instance pool and then writes only
        // the columns that differ FROM THAT OBJECT — so in a long-lived daemon
        // an upsert computes its diff against a copy the collector cron has
        // since overwritten, and silently skips every column whose pooled value
        // happens to match (computed_at, written twice in the same second, is
        // the one that matters: the freshness stamp never lands and the row
        // reads stale forever). See self::rows().
        MarketSummaryPeer::clearInstancePool();
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
        $row->setEr20(isset($summary['er20']) ? (string) $summary['er20'] : null);
        $row->setChop14(isset($summary['chop14']) ? (string) $summary['chop14'] : null);

        $compact = array_map(
            static fn ($c) => [(string) $c['high'], (string) $c['low'], (string) $c['close']],
            array_slice($candles, -self::KEEP)
        );
        $row->setCandlesUsed(count($compact));
        $row->setRecentCandles(json_encode($compact, JSON_UNESCAPED_SLASHES));
        if ($extras !== null) {
            $row->setFundingRate($extras['funding_rate'] ?? null);
            $row->setFundingPct(isset($extras['funding_pct']) ? (string) $extras['funding_pct'] : null);
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
            $log->setEr20(isset($summary['er20']) ? (string) $summary['er20'] : null);
            $log->setChop14(isset($summary['chop14']) ? (string) $summary['chop14'] : null);
            $log->setFundingPct(isset($extras['funding_pct']) ? (string) $extras['funding_pct'] : null);
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
        foreach (self::rows($symbol) as $r) {
            $computed = $r['ComputedAt'] !== null ? (string) $r['ComputedAt'] : null;
            $age = $computed ? (time() - strtotime($computed)) : null;
            $out[(string) $r['Tf']] = [
                'price' => self::num($r['Price']),
                'ema20' => self::num($r['Ema20']),
                'ema50' => self::num($r['Ema50']),
                'ema200' => self::num($r['Ema200']),
                'rsi14' => self::num($r['Rsi14']),
                'atr14' => self::num($r['Atr14']),
                'atr_pct' => self::num($r['AtrPct']),
                'trend' => self::enum($r['Trend']),
                'swing_high' => self::num($r['SwingHigh']),
                'swing_low' => self::num($r['SwingLow']),
                'candles_used' => (int) $r['CandlesUsed'],
                'adx14' => self::num($r['Adx14']),
                'atr_pct_rank' => self::num($r['AtrPctRank']),
                'taker_buy_ratio' => self::num($r['TakerBuyRatio']),
                'vol_zscore' => self::num($r['VolZscore']),
                'er20' => self::num($r['Er20']),
                'chop14' => self::num($r['Chop14']),
                'funding_pct' => self::num($r['FundingPct']),
                'funding_rate' => $r['FundingRate'] !== null ? (float) $r['FundingRate'] : null,
                'depth_imbalance' => $r['DepthImbalance'] !== null ? (float) $r['DepthImbalance'] : null,
                'depth_imbalance_avg' => self::num($r['DepthImbalanceAvg']),
                'computed_at' => $computed,
                'age_seconds' => $age,
                'stale' => $age === null || $age > $staleAfter,
            ];
        }
        return $out;
    }

    /**
     * select(): raw rows, never pooled objects. Propel does not re-hydrate an
     * object already in its instance pool, so a hydrated read inside a
     * long-lived daemon serves the summary AS OF that process's first read and
     * every later collection — the cron writes one every 10 minutes, from its
     * own process — stays invisible until restart. Prod 2026-09-14: run 9's
     * daemon self-clocked a stale/refresh cycle every 1800s against a row that
     * was in fact seconds old, and run 8's core arm (whose branch never hits
     * that fallback refresh) sat on a 1d summary pooled five days earlier,
     * unable to enter or exit. Same trap as BudgetGuard::check and
     * DrawdownGuard::equity, fixed the same way.
     *
     * recent_candles is deliberately NOT selected here — it is a ~300-bar blob
     * per row that summaries() never exposes; candles() selects it on its own.
     *
     * @return array<int, array<string, mixed>>
     */
    private static function rows(string $symbol): array
    {
        return MarketSummaryQuery::create()
            ->filterBySymbol($symbol)
            ->select([
                'Tf', 'Price', 'Ema20', 'Ema50', 'Ema200', 'Rsi14', 'Atr14', 'AtrPct', 'Trend',
                'SwingHigh', 'SwingLow', 'CandlesUsed', 'Adx14', 'AtrPctRank', 'TakerBuyRatio',
                'VolZscore', 'Er20', 'Chop14', 'FundingPct', 'FundingRate', 'DepthImbalance',
                'DepthImbalanceAvg', 'ComputedAt',
            ])
            ->find()->getArrayCopy();
    }

    /**
     * Recent candles for one (symbol, tf) from the DB.
     * @return array<int, array{high:string, low:string, close:string}>
     */
    public static function candles(string $symbol, string $tf): array
    {
        // select(): raw row, never the pooled object — this blob IS the tape the
        // entry signal walks (Donchian high/low, EMA cross), so a pooled read
        // freezes the in-progress bar and hides every breakout inside the
        // freeze window. See self::rows().
        $r = MarketSummaryQuery::create()
            ->filterBySymbol($symbol)
            ->filterByTf($tf)
            ->select(['Tf', 'RecentCandles'])
            ->findOne();
        if (!$r || empty($r['RecentCandles'])) {
            return [];
        }
        $raw = json_decode((string) $r['RecentCandles'], true);
        if (!is_array($raw)) {
            return [];
        }
        return array_map(static fn ($c) => ['high' => (string) $c[0], 'low' => (string) $c[1], 'close' => (string) $c[2]], $raw);
    }

    private static function num($v): ?float
    {
        return $v === null ? null : (float) $v;
    }

    /**
     * market_summary.trend is an ENUM: Propel stores the INDEX and only the
     * generated getter maps it back. select() hands us the raw index, so do
     * the mapping here — without it every consumer reads a bare '4' where it
     * expects 'strong_down'.
     */
    private static function enum($v): string
    {
        if ($v === null || $v === '') {
            return '';
        }
        $set = MarketSummaryPeer::getValueSet(MarketSummaryPeer::TREND);
        return (string) ($set[(int) $v] ?? $v);
    }
}
