<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\Indicators;
use App\Domains\Bot\MarketStore;
use PHPUnit\Framework\TestCase;

class MarketStoreTest extends TestCase
{
    private static bool $booted = false;

    public static function setUpBeforeClass(): void
    {
        if (self::$booted) {
            return;
        }
        $admin = dirname(__DIR__, 3);
        require_once $admin . '/vendor/autoload.php';
        (new \Ahc\Env\Loader())->load($admin . '/.env');
        if (!defined('_AUTH_VAR')) {
            require $admin . '/config/Built/config.php';
        }
        if (!\Propel::isInit()) {
            require $admin . '/config/Built/propel.php';
        }
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION[_AUTH_VAR] = new \ApiGoat\Sessions\AuthySession();
        self::$booted = true;
    }

    protected function setUp(): void
    {
        \Propel::getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function candles(int $n = 60): array
    {
        $out = [];
        for ($i = 0; $i < $n; $i++) {
            $p = 100 + $i;
            $out[] = ['high' => $p + 2, 'low' => $p - 2, 'close' => $p];
        }
        return $out;
    }

    public function testUpsertThenReadSummary(): void
    {
        $candles = $this->candles();
        MarketStore::upsert('BTCUSDT', '4h', Indicators::summary($candles), $candles);

        $sums = MarketStore::summaries('BTCUSDT');
        $this->assertArrayHasKey('4h', $sums);
        $this->assertSame(60, $sums['4h']['candles_used']);
        $this->assertContains($sums['4h']['trend'], ['up', 'strong_up']);
        $this->assertFalse($sums['4h']['stale'], 'just-written summary is fresh');
        $this->assertIsFloat($sums['4h']['ema20']);
    }

    public function testRegimeMetricsAndFundingPctRoundTrip(): void
    {
        $candles = $this->candles(120);
        MarketStore::upsert('BTCUSDT', '4h', Indicators::summary($candles), $candles, [
            'funding_rate' => 0.0001, 'depth_imbalance' => null, 'funding_pct' => 87.5,
        ]);
        $s = MarketStore::summaries('BTCUSDT')['4h'];
        $this->assertIsFloat($s['er20'], 'Kaufman ER stored and read back');
        $this->assertIsFloat($s['chop14'], 'Choppiness stored and read back');
        $this->assertSame(87.5, $s['funding_pct'], '30d funding percentile from extras');
    }

    public function testUpsertIsIdempotentPerSymbolTf(): void
    {
        $c = $this->candles();
        MarketStore::upsert('BTCUSDT', '1h', Indicators::summary($c), $c);
        MarketStore::upsert('BTCUSDT', '1h', Indicators::summary($c), $c);
        $sums = MarketStore::summaries('BTCUSDT');
        // one row per (symbol, tf) — not two
        $this->assertCount(1, array_keys($sums));
    }

    public function testCandlesRoundTrip(): void
    {
        $candles = $this->candles(40);
        MarketStore::upsert('BTCUSDT', '1d', Indicators::summary($candles), $candles);
        $read = MarketStore::candles('BTCUSDT', '1d');
        $this->assertCount(40, $read);
        $this->assertArrayHasKey('close', $read[0]);
        $this->assertSame('100', (string) $read[0]['close']);
    }

    public function testStaleFlag(): void
    {
        $c = $this->candles();
        MarketStore::upsert('ETHUSDT', '4h', Indicators::summary($c), $c);
        // negative threshold → any age counts as stale
        $sums = MarketStore::summaries('ETHUSDT', -1);
        $this->assertTrue($sums['4h']['stale']);
    }

    public function testNewMetricFieldsRoundTrip(): void
    {
        $c = $this->candles();
        $summary = Indicators::summary($c);
        $summary['adx14'] = 33.3;
        $summary['atr_pct_rank'] = 88.0;
        $summary['taker_buy_ratio'] = 0.61;
        $summary['vol_zscore'] = 2.5;
        MarketStore::upsert('BTCUSDT', '4h', $summary, $c);

        $s = MarketStore::summaries('BTCUSDT')['4h'];
        $this->assertEqualsWithDelta(33.3, $s['adx14'], 0.01);
        $this->assertEqualsWithDelta(88.0, $s['atr_pct_rank'], 0.01);
        $this->assertEqualsWithDelta(0.61, $s['taker_buy_ratio'], 0.001);
        $this->assertEqualsWithDelta(2.5, $s['vol_zscore'], 0.01);
    }

    public function testDepthImbalanceAvgSmoothsAcrossCollections(): void
    {
        // one order-book snapshot is noise — the stored average must blend
        // the new reading into the previous one, not replace it
        $c = $this->candles();
        MarketStore::upsert('BTCUSDT', '4h', Indicators::summary($c), $c, ['funding_rate' => null, 'depth_imbalance' => 0.8]);
        $first = MarketStore::summaries('BTCUSDT')['4h']['depth_imbalance_avg'];
        $this->assertEqualsWithDelta(0.8, $first, 0.001, 'first reading seeds the average');

        MarketStore::upsert('BTCUSDT', '4h', Indicators::summary($c), $c, ['funding_rate' => null, 'depth_imbalance' => 0.4]);
        $s = MarketStore::summaries('BTCUSDT')['4h'];
        $this->assertGreaterThan(0.4, $s['depth_imbalance_avg'], 'average must not jump to the raw new reading');
        $this->assertLessThan(0.8, $s['depth_imbalance_avg']);
        $this->assertEqualsWithDelta(0.4, $s['depth_imbalance'], 0.001, 'raw snapshot still reported as-is');
    }
}
