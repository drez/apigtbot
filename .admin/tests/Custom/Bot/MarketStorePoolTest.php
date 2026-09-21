<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\MarketStore;
use Tests\Builder\Support\DbTestCase;

/**
 * Market data must be read fresh, not from Propel's instance pool.
 *
 * The collector cron (gtbot-market-collect, every 10 min) writes market_summary from
 * its OWN process. The trading daemons read it from a process that has been
 * up for days. Propel never re-hydrates an object already in its pool, so a
 * hydrated read serves the row AS OF the first read in that process and every
 * later collection is invisible — the daemon trades a frozen tape.
 *
 * Prod 2026-09-14: run 9's daemon logged "1h market data was stale; collector
 * refresh succeeded" every 1800s to the second (self-clocked off its own
 * in-process write) while the cron was writing fresh rows every 10 minutes,
 * and run 8's core arm — whose branch never triggers that fallback refresh —
 * sat on a 1d summary pooled at boot five days earlier, unable to enter or
 * exit. Same trap BudgetGuard and DrawdownGuard were pulled out of on
 * 2026-09-09.
 */
class MarketStorePoolTest extends DbTestCase
{
    /** A minimal Indicators::summary()-shaped payload. */
    private function summary(string $price): array
    {
        return [
            'price' => $price,
            'ema20' => $price,
            'ema50' => $price,
            'ema200' => $price,
            'rsi14' => '50',
            'atr14' => '1',
            'atr_pct' => '1',
            'trend' => 'up',
            'swing_high' => $price,
            'swing_low' => $price,
        ];
    }

    /** @return array<int, array{high:string, low:string, close:string}> */
    private function candles(string $close): array
    {
        return [
            ['high' => $close, 'low' => $close, 'close' => $close],
            ['high' => $close, 'low' => $close, 'close' => $close],
        ];
    }

    private function seed(string $tf = '1h', string $price = '100'): string
    {
        $symbol = strtoupper(self::uniq('msp'));
        MarketStore::upsert($symbol, $tf, $this->summary($price), $this->candles($price));
        return $symbol;
    }

    /** The collector's next run, as the daemon's process sees it: a row
     *  rewritten by someone else, behind the pooled object. */
    private function collectBehindThePool(string $symbol, string $tf, string $price, string $computedAt): void
    {
        \Propel::getConnection()
            ->prepare('UPDATE market_summary SET price = ?, ema20 = ?, recent_candles = ?, computed_at = ? WHERE symbol = ? AND tf = ?')
            ->execute([
                $price,
                $price,
                json_encode([[$price, $price, $price]]),
                $computedAt,
                $symbol,
                $tf,
            ]);
    }

    public function testSummariesSeeACollectionBehindTheInstancePool(): void
    {
        $symbol = $this->seed();
        $first = MarketStore::summaries($symbol)['1h'] ?? null;
        $this->assertNotNull($first, 'seeded row must be readable');
        $this->assertSame(100.0, $first['price'], 'the row is now pooled in this process');

        $this->collectBehindThePool($symbol, '1h', '222.00000000', date('Y-m-d H:i:s'));

        $second = MarketStore::summaries($symbol)['1h'] ?? null;
        $this->assertNotNull($second);
        $this->assertSame(222.0, $second['price'], 'a collection behind the pool must be seen');
        $this->assertSame(222.0, $second['ema20'], 'every indicator comes from the same row, so all of them must refresh');
    }

    /**
     * The freshness gate is the part that actually halted prod: computed_at
     * frozen at the pooled value ages past staleAfter and the engine refuses
     * entries (or, on the core arm, refuses to enter AND to exit) while the
     * DB row is in fact seconds old.
     */
    public function testStalenessIsJudgedOnTheStoredComputedAtNotThePooledOne(): void
    {
        $symbol = $this->seed();
        $stale = date('Y-m-d H:i:s', time() - 7200);
        $this->collectBehindThePool($symbol, '1h', '100.00000000', $stale);
        $this->assertTrue(MarketStore::summaries($symbol)['1h']['stale'], 'an old computed_at must read stale');

        $this->collectBehindThePool($symbol, '1h', '100.00000000', date('Y-m-d H:i:s'));
        $row = MarketStore::summaries($symbol)['1h'];
        $this->assertFalse($row['stale'], 'a fresh collection must clear the stale gate without a restart');
        $this->assertLessThan(60, (int) $row['age_seconds'], 'age is measured from the stored computed_at');
    }

    /**
     * candles() reads the recent_candles blob off the SAME row, so the entry
     * signal (Donchian high/low, EMA cross) walks the pooled tape too: the
     * in-progress bar never moves and a breakout inside the freeze window is
     * invisible.
     */
    public function testCandlesSeeACollectionBehindTheInstancePool(): void
    {
        $symbol = $this->seed();
        $this->assertSame('100', MarketStore::candles($symbol, '1h')[0]['close'], 'the row is now pooled in this process');

        $this->collectBehindThePool($symbol, '1h', '333.00000000', date('Y-m-d H:i:s'));

        $candles = MarketStore::candles($symbol, '1h');
        $this->assertCount(1, $candles, 'the rewritten series replaces the pooled one');
        $this->assertSame('333.00000000', $candles[0]['close'], 'a collection behind the pool must be seen');
    }
}
