<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\CandleStore;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\MarketCollector;
use Tests\Builder\Support\DbTestCase;

class MarketCollectorCandlesTest extends DbTestCase
{
    /** Gateway whose klines answer is synthesized per interval; records the intervals + limits asked for. */
    private function gateway(array &$calls, int $bars = 20): BinanceGateway
    {
        $transport = function (string $method, string $url, array $headers, ?string $body) use (&$calls, $bars): array {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $q);
            if (str_contains($url, '/api/v3/klines')) {
                $calls[] = [$q['interval'], (int) $q['limit']];
                $step = CandleStore::TF_SECONDS[$q['interval']] ?? 86400;
                // recent bars: the full pass prunes beyond retention
                $base = intdiv(time(), $step) * $step - ($bars - 1) * $step;
                $rows = [];
                for ($i = 0; $i < $bars; $i++) {
                    $t = ($base + $i * $step) * 1000;
                    $rows[] = [$t, '100', '110', '90', '105', '10', $t + $step * 1000 - 1, '1000', 5, '5', '500', '0'];
                }
                return ['status' => 200, 'headers' => [], 'body' => json_encode($rows)];
            }
            return ['status' => 200, 'headers' => [], 'body' => '[]'];
        };
        return new BinanceGateway('https://example.invalid', '', '', $transport, static fn (): int => 1_700_000_000_000);
    }

    public function testRefreshCandlesWritesEveryChartTimeframe(): void
    {
        $symbol = strtoupper(self::uniq('mc'));
        $calls = [];
        $n = MarketCollector::refreshCandles($symbol, $this->gateway($calls));
        $this->assertSame(5 * 20, $n);
        $this->assertSame(CandleStore::INTERVALS, array_column($calls, 0));
        $this->assertSame([120], array_unique(array_column($calls, 1)), 'minute pass fetches a shallow window');
        foreach (CandleStore::INTERVALS as $tf) {
            $this->assertCount(20, CandleStore::series($symbol, $tf), $tf);
        }
    }

    public function testFullRefreshStoresCandlesForSummaryAndChartOnlyTimeframes(): void
    {
        $symbol = strtoupper(self::uniq('mc'));
        $calls = [];
        // 20 bars clears the summary's 15-candle floor, so the 1h/4h/1d/1w
        // summaries store and the chart timeframes' klines double as candles.
        MarketCollector::refresh($symbol, $this->gateway($calls));
        $intervals = array_column($calls, 0);
        $this->assertSame(['1h', '4h', '1d', '1w', '1m', '5m', '15m'], $intervals, 'summary pass first, then the chart-only timeframes');
        foreach (CandleStore::INTERVALS as $tf) {
            $this->assertCount(20, CandleStore::series($symbol, $tf), $tf);
        }
        $this->assertSame(1000, $calls[4][1], 'chart-only timeframes backfill deep on the full pass');
        // 1w is an outlook input, not a chart timeframe: a summary, no candles
        $this->assertArrayHasKey('1w', \App\Domains\Bot\MarketStore::summaries($symbol));
        $this->assertArrayNotHasKey('1w', CandleStore::TF_SECONDS);
    }
}
