<?php

namespace Tests\Custom\Bot;

use App\BotDecision;
use App\Config;
use App\ConfigQuery;
use App\GridRun;
use App\GridRunQuery;
use App\Mcp\Tools\GtbotRoutineBriefTool;
use PHPUnit\Framework\TestCase;

/**
 * gtbot_routine_brief: one compact payload for all runs + material_change
 * against the fingerprint stored from the previous call. Market data is
 * injected (the dev DB holds a single BTCUSDT 1h summary row).
 */
class GtbotRoutineBriefToolTest extends TestCase
{
    private static bool $booted = false;
    private GridRun $trend;
    private GridRun $btc;
    private GridRun $bnb;

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
        foreach (GridRunQuery::create()->filterByStatus(['DryRun', 'Testnet', 'Live'], \Criteria::IN)->find() as $r) {
            $r->setStatus('Halted');
            $r->save();
        }
        $this->cfg('gtbot_shared_budget_quote', '1000');
        $this->cfg('gtbot_max_drawdown_pct', '0');
        $this->cfg(GtbotRoutineBriefTool::FINGERPRINT_KEY, '');
        $this->trend = $this->mkRun('BTCUSDT', '100', 'Trend', 5, 25);
        $this->btc = $this->mkRun('BTCUSDT', '350', 'Grid', 5, 30);
        $this->bnb = $this->mkRun('BNBUSDT', '550', 'Grid', 6, 30);
        // a fresh decision on each grid so quiet_too_long doesn't fire
        foreach ([$this->btc, $this->bnb] as $r) {
            $d = new BotDecision();
            $d->setIdGridRun($r->getIdGridRun());
            $d->setSource('Claude');
            $d->setPLow('90');
            $d->setPHigh('110');
            $d->setNLevels(5);
            $d->setDeployPct(30);
            $d->setReason('test seed');
            $d->setEvalStatus('Pending');
            $d->save();
        }
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function cfg(string $k, string $v): void
    {
        $c = ConfigQuery::create()->findOneByConfig($k) ?? (new Config())->setConfig($k);
        $c->setValue($v);
        $c->save();
    }

    private function mkRun(string $symbol, string $budget, string $algo, int $levels, int $deploy): GridRun
    {
        $r = new GridRun();
        $r->setLabel('brief-' . $algo . '-' . bin2hex(random_bytes(3)));
        $r->setSymbol($symbol);
        $r->setStatus('Live');
        $r->setAlgo($algo);
        $r->setProfile('Balanced');
        $r->setSimulated(true);
        $r->setPLow('90');
        $r->setPHigh('110');
        $r->setNLevels($levels);
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualBase');
        $r->setBudgetQuote($budget);
        $r->setDeployPct($deploy);
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid('br' . bin2hex(random_bytes(2)));
        $r->setLastTickAt(date('Y-m-d H:i:s'));
        $r->save();
        return $r;
    }

    private function tool(float $btcPrice = 100.0, string $t4 = 'up'): GtbotRoutineBriefTool
    {
        $frames = static function (float $price, string $t4): array {
            $f = static fn (string $t, float $adx) => ['trend' => $t, 'rsi14' => 55.0, 'atr_pct' => 1.0, 'ema20' => 99.0, 'ema50' => 98.0, 'ema200' => 92.0, 'price' => $price, 'adx14' => $adx, 'er20' => 0.3, 'chop14' => 50.0, 'atr_pct_rank' => 40.0, 'stale' => false, 'age_seconds' => 60];
            return ['1h' => $f('up', 20.0), '4h' => $f($t4, 22.0), '1d' => $f('up', 25.0)];
        };
        $summaries = static fn (string $sym): array => $frames($sym === 'BTCUSDT' ? $btcPrice : 100.0, $t4);
        $candles = static function (string $sym, string $tf): array {
            $out = [];
            for ($i = 0; $i < 60; $i++) {
                $mid = 90 + 20 * (0.5 + 0.4 * sin($i / 6));
                $out[] = ['low' => (string) ($mid - 0.2), 'high' => (string) ($mid + 0.2), 'close' => (string) $mid];
            }
            return $out;
        };
        $price = static fn (string $sym): ?string => $sym === 'BTCUSDT' ? (string) $btcPrice : '100';
        return new GtbotRoutineBriefTool($summaries, $candles, $price);
    }

    private function byId(array $brief): array
    {
        return array_column($brief['runs'], null, 'id');
    }

    public function testFirstBriefIsMaterialAndCoversAllRuns(): void
    {
        $b = $this->tool()->build(null);
        $this->assertTrue($b['material_change']);
        $this->assertSame(['first brief — no previous fingerprint'], $b['changes']);
        $this->assertCount(3, $b['runs']);
        $byId = $this->byId($b);
        $t = $byId[(int) $this->trend->getIdGridRun()];
        $this->assertNull($t['candidate']);
        $this->assertIsArray($t['trend']);
        $this->assertSame('idle', $t['trend']['state']);
        $this->assertContains('trend_managed', $t['flags']);
        $g = $byId[(int) $this->btc->getIdGridRun()];
        $this->assertNotNull($g['candidate']);
        $this->assertSame(50.0, $g['geometry']['pos_pct']);
        $this->assertTrue($g['geometry']['in_range']);
        $this->assertSame('350', $g['slice']);
        $this->assertSame('250', $g['floor_slice']);
        $this->assertSame('MIXED', $g['regime']['class']);
        $this->assertSame(100.0, $g['signal']['price']);
        $this->assertArrayHasKey('by_deploy_band', $g['track']);
        $this->assertSame('1000', $b['shared']['budget']);
        $this->assertSame('1000', $b['shared']['allocated']);
        $this->assertSame('idle', $b['shared']['trend_arm']['state']);
        // the long-horizon outlook rides along as labelled context
        $this->assertStringContainsString('DETECTION, not forecast', $b['shared']['outlook']['note']);
        $this->assertIsArray($b['shared']['outlook']['symbols']);
        $this->assertSame((int) $this->trend->getIdGridRun(), $b['shared']['trend_arm']['run']);
        $this->assertLessThanOrEqual(6000, strlen(json_encode($b, JSON_UNESCAPED_SLASHES)), 'brief exceeds its 6 KB budget');
    }

    public function testHeldRunIsFlaggedHeldWithoutAttention(): void
    {
        $this->bnb->setStatus('Halted');
        $this->bnb->setLastTickAt('2026-01-01 00:00:00');
        $this->bnb->save();
        $b = $this->tool()->build(null);
        $r = $this->byId($b)[(int) $this->bnb->getIdGridRun()];
        $this->assertSame('Halted', $r['status']);
        $this->assertContains('held', $r['flags']);
        $this->assertNotContains('heartbeat_stale', $r['flags']);
        $this->assertFalse($r['attention']);
    }

    public function testSecondIdenticalBriefIsQuiet(): void
    {
        $this->tool()->build(null);
        $b = $this->tool()->build(null);
        $this->assertFalse($b['material_change']);
        $this->assertSame([], $b['changes']);
        foreach ($b['runs'] as $r) {
            $this->assertFalse($r['attention'], 'run ' . $r['id'] . ' flags: ' . implode(',', $r['flags']));
        }
    }

    public function testPriceLeavingBandIsMaterialAndFlagsTheRun(): void
    {
        $this->tool()->build(null);
        $b = $this->tool(130.0)->build(null);
        $this->assertTrue($b['material_change']);
        $g = $this->byId($b)[(int) $this->btc->getIdGridRun()];
        $this->assertContains('outside_band', $g['flags']);
        $this->assertTrue($g['attention']);
        $this->assertNotEmpty(array_filter($b['changes'], static fn ($c) => str_contains($c, 'pos_bucket')));
        $this->assertGreaterThan(130.0, (float) $g['candidate']['p_high']);
        // BNB (unchanged symbol) stays quiet
        $this->assertFalse($this->byId($b)[(int) $this->bnb->getIdGridRun()]['attention']);
    }

    public function testRegimeChangeIsMaterial(): void
    {
        $this->tool(100.0, 'up')->build(null);
        $b = $this->tool(100.0, 'down')->build(null);
        $this->assertTrue($b['material_change']);
        $g = $this->byId($b)[(int) $this->bnb->getIdGridRun()];
        $this->assertSame('HOSTILE', $g['regime']['class']);
        $this->assertContains('regime_changed', $g['flags']);
        $this->assertContains('hostile_overdeployed', $g['flags']); // BNB deploy 30 > cap 25
        $this->assertSame([0, 25], $g['candidate']['deploy_band']);
    }

    public function testRunFilterNarrowsButFingerprintStillCoversAll(): void
    {
        $b = $this->tool()->build((int) $this->bnb->getIdGridRun());
        $this->assertCount(1, $b['runs']);
        $this->assertSame((int) $this->bnb->getIdGridRun(), $b['runs'][0]['id']);
        $b2 = $this->tool()->build(null);
        $this->assertFalse($b2['material_change']);
    }

    public function testRunToolWrapsPayload(): void
    {
        $res = (new \ReflectionMethod(GtbotRoutineBriefTool::class, 'run'))->invoke($this->tool(), [], $_SESSION[_AUTH_VAR]);
        $this->assertFalse($res['isError']);
        $payload = json_decode($res['content'][0]['text'], true);
        $this->assertArrayHasKey('material_change', $payload);
    }
}
