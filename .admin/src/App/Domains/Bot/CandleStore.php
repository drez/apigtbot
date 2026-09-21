<?php

namespace App\Domains\Bot;

use App\MarketCandle;
use App\MarketCandleQuery;

/**
 * Persisted OHLCV history per (symbol, timeframe) for the dashboard chart.
 * MarketStore keeps only [high,low,close] × 300 per summary row; this is the
 * real candle series, keyed on the bar's open time (epoch seconds, UTC, as
 * Binance sends it) so the chart never has to reconstruct timestamps and the
 * bot's fills can be aligned bar-for-bar. Filled by the market collector
 * (10-min full pass + the 1-min --candles pass) and pruned per timeframe.
 */
final class CandleStore
{
    public const INTERVALS = ['1m', '5m', '15m', '1h', '4h'];

    public const TF_SECONDS = ['1m' => 60, '5m' => 300, '15m' => 900, '1h' => 3600, '4h' => 14400];

    /** How far back each timeframe is kept (seconds). */
    public const RETENTION = [
        '1m' => 2 * 86400,
        '5m' => 7 * 86400,
        '15m' => 30 * 86400,
        '1h' => 90 * 86400,
        '4h' => 365 * 86400,
    ];

    /**
     * Insert new bars, update the ones already stored (the newest bar is the
     * in-progress candle and changes on every fetch). Rows without an
     * open_time are ignored. Returns the number of bars written.
     *
     * @param array<int, array{open_time?:int, open:mixed, high:mixed, low:mixed, close:mixed, volume?:mixed}> $candles
     */
    public static function upsert(string $symbol, string $tf, array $candles): int
    {
        self::assertTf($tf);
        $byTime = [];
        foreach ($candles as $c) {
            if (!isset($c['open_time']) || (int) $c['open_time'] <= 0) {
                continue;
            }
            $byTime[(int) $c['open_time']] = $c;
        }
        if (!$byTime) {
            return 0;
        }
        $existing = [];
        foreach (MarketCandleQuery::create()
            ->filterBySymbol($symbol)
            ->filterByTf($tf)
            ->filterByOpenTime(array_keys($byTime), \Criteria::IN)
            ->find() as $row) {
            $existing[(int) $row->getOpenTime()] = $row;
        }
        $n = 0;
        foreach ($byTime as $t => $c) {
            $row = $existing[$t] ?? (new MarketCandle())->setSymbol($symbol)->setTf($tf)->setOpenTime($t);
            $row->setOpen((string) $c['open']);
            $row->setHigh((string) $c['high']);
            $row->setLow((string) $c['low']);
            $row->setClose((string) $c['close']);
            $row->setVolume((string) ($c['volume'] ?? '0'));
            $row->save();
            $n++;
        }
        return $n;
    }

    /** Delete bars older than the timeframe's retention window. Returns rows deleted. */
    public static function prune(string $symbol, string $tf): int
    {
        self::assertTf($tf);
        return (int) MarketCandleQuery::create()
            ->filterBySymbol($symbol)
            ->filterByTf($tf)
            ->filterByOpenTime(time() - self::RETENTION[$tf], \Criteria::LESS_THAN)
            ->delete();
    }

    /**
     * The newest $limit bars, oldest first, in the chart's wire shape.
     * Floats on purpose: this feeds a display, not accounting.
     *
     * @return array<int, array{time:int, open:float, high:float, low:float, close:float, volume:float}>
     */
    public static function series(string $symbol, string $tf, int $limit = 1500): array
    {
        self::assertTf($tf);
        $rows = MarketCandleQuery::create()
            ->filterBySymbol($symbol)
            ->filterByTf($tf)
            ->orderByOpenTime(\Criteria::DESC)
            ->limit(max(1, $limit))
            ->find()
            ->getArrayCopy();
        $out = [];
        foreach (array_reverse($rows) as $r) {
            $out[] = [
                'time' => (int) $r->getOpenTime(),
                'open' => (float) $r->getOpen(),
                'high' => (float) $r->getHigh(),
                'low' => (float) $r->getLow(),
                'close' => (float) $r->getClose(),
                'volume' => (float) $r->getVolume(),
            ];
        }
        return $out;
    }

    /** Open time of the newest stored bar, null when the series is empty. */
    public static function newest(string $symbol, string $tf): ?int
    {
        self::assertTf($tf);
        $row = MarketCandleQuery::create()
            ->filterBySymbol($symbol)
            ->filterByTf($tf)
            ->orderByOpenTime(\Criteria::DESC)
            ->findOne();
        return $row ? (int) $row->getOpenTime() : null;
    }

    private static function assertTf(string $tf): void
    {
        if (!isset(self::TF_SECONDS[$tf])) {
            throw new \InvalidArgumentException("unknown candle timeframe '$tf' (" . implode(',', self::INTERVALS) . ')');
        }
    }
}
