<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\CandleStore;
use App\MarketCandleQuery;
use Tests\Builder\Support\DbTestCase;

class CandleStoreTest extends DbTestCase
{
    private string $symbol;

    protected function setUp(): void
    {
        parent::setUp();
        $this->symbol = strtoupper(self::uniq('cs'));
    }

    /** @return array<int, array<string, mixed>> klines-shaped candles, $n consecutive 1m bars from $start */
    private function candles(int $start, int $n, string $close = '105'): array
    {
        $out = [];
        for ($i = 0; $i < $n; $i++) {
            $out[] = [
                'open_time' => $start + $i * 60,
                'open' => '100', 'high' => '110', 'low' => '90', 'close' => $close,
                'volume' => '12.5', 'taker_buy' => '6',
            ];
        }
        return $out;
    }

    public function testUpsertInsertsThenUpdatesInsteadOfDuplicating(): void
    {
        $start = 1_700_000_000;
        $this->assertSame(3, CandleStore::upsert($this->symbol, '1m', $this->candles($start, 3)));
        // the same window again, with a new close on the in-progress bar
        $again = $this->candles($start, 3);
        $again[2]['close'] = '107';
        $this->assertSame(3, CandleStore::upsert($this->symbol, '1m', $again));

        $rows = MarketCandleQuery::create()->filterBySymbol($this->symbol)->filterByTf('1m')->orderByOpenTime()->find();
        $this->assertCount(3, $rows, 'second upsert must update, not insert');
        $this->assertSame(107.0, (float) $rows[2]->getClose());
    }

    public function testSeriesIsOldestFirstAndTypedForTheChart(): void
    {
        $start = 1_700_000_000;
        CandleStore::upsert($this->symbol, '5m', [
            ['open_time' => $start + 300, 'open' => '2', 'high' => '3', 'low' => '1', 'close' => '2.5', 'volume' => '7'],
            ['open_time' => $start, 'open' => '1', 'high' => '2', 'low' => '0.5', 'close' => '1.5', 'volume' => '5'],
        ]);
        $s = CandleStore::series($this->symbol, '5m');
        $this->assertSame([$start, $start + 300], array_column($s, 'time'));
        $this->assertSame(['time' => $start, 'open' => 1.0, 'high' => 2.0, 'low' => 0.5, 'close' => 1.5, 'volume' => 5.0], $s[0]);
        $this->assertSame($start + 300, CandleStore::newest($this->symbol, '5m'));
        $this->assertNull(CandleStore::newest($this->symbol, '1h'));
    }

    public function testSeriesLimitKeepsTheNewestBars(): void
    {
        $start = 1_700_000_000;
        CandleStore::upsert($this->symbol, '1m', $this->candles($start, 5));
        $s = CandleStore::series($this->symbol, '1m', 2);
        $this->assertSame([$start + 180, $start + 240], array_column($s, 'time'));
    }

    public function testRowsWithoutOpenTimeAreSkipped(): void
    {
        $n = CandleStore::upsert($this->symbol, '1m', [
            ['open' => '1', 'high' => '2', 'low' => '0.5', 'close' => '1.5'],
            ['open_time' => 1_700_000_000, 'open' => '1', 'high' => '2', 'low' => '0.5', 'close' => '1.5'],
        ]);
        $this->assertSame(1, $n);
    }

    public function testPruneDeletesOnlyBeyondRetention(): void
    {
        $now = time();
        $old = $now - CandleStore::RETENTION['1m'] - 3600;
        CandleStore::upsert($this->symbol, '1m', [
            ['open_time' => $old, 'open' => '1', 'high' => '2', 'low' => '0.5', 'close' => '1.5'],
            ['open_time' => $now - 60, 'open' => '1', 'high' => '2', 'low' => '0.5', 'close' => '1.5'],
        ]);
        $this->assertSame(1, CandleStore::prune($this->symbol, '1m'));
        $this->assertSame([$now - 60], array_column(CandleStore::series($this->symbol, '1m'), 'time'));
    }

    public function testUnknownTimeframeIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CandleStore::upsert($this->symbol, '1d', $this->candles(1_700_000_000, 1));
    }
}
