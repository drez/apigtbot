<?php

namespace Tests\Custom\Bot;

use ApiGoat\Sessions\AuthySession;
use App\BotEventQuery;
use App\GridRun;
use App\Mcp\Tools\GtbotSetGridTool;
use PHPUnit\Framework\TestCase;

class GtbotSetGridToolTest extends TestCase
{
    private static bool $booted = false;
    private GridRun $run;

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
        $_SESSION[_AUTH_VAR] = new AuthySession();
        self::$booted = true;
    }

    protected function setUp(): void
    {
        \Propel::getConnection()->beginTransaction();
        $r = new GridRun();
        $r->setLabel('setgrid-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setPLow('60000');
        $r->setPHigh('72000');
        $r->setNLevels(12);
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('1000');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('1200');
        $r->setMaxOrderQuote('200');
        $r->setDailyLossLimitQuote('50');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(30);
        $r->setRunUid('setgrid');
        $r->setLastTickAt(date('Y-m-d H:i:s'));
        $r->save();
        $this->run = $r;
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function tool(?string $price = '66000'): GtbotSetGridTool
    {
        // inject a fixed market price so the test needs no network
        return new GtbotSetGridTool($price);
    }

    private function decode(array $r): array
    {
        return json_decode($r['content'][0]['text'], true);
    }

    private function session(): AuthySession
    {
        return $this->createMock(AuthySession::class);
    }

    public function testAppliesGeometryToActiveRun(): void
    {
        $out = $this->decode($this->tool()->handle([
            'p_low' => '62000', 'p_high' => '70000', 'n_levels' => 14,
            'reason' => 'uptrend, RSI 58, ATR 1.2% — re-centering higher',
        ], $this->session()));
        $this->assertTrue($out['applied']);
        $this->run->reload();
        $this->assertSame(0, bccomp((string) $this->run->getPLow(), '62000', 2));
        $this->assertSame(0, bccomp((string) $this->run->getPHigh(), '70000', 2));
        $this->assertSame(14, (int) $this->run->getNLevels());
        // a refit_by_claude event carries the reason (also Telegrams / shows in GUI)
        $ev = BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('refit_by_claude')
            ->findOne();
        $this->assertNotNull($ev);
        $this->assertStringContainsString('uptrend', (string) $ev->getMessage());
    }

    public function testDeployPctIsAppliedAndJournaled(): void
    {
        $out = $this->decode($this->tool()->handle([
            'p_low' => '62000', 'p_high' => '70000', 'n_levels' => 14,
            'deploy_pct' => 40,
            'reason' => 'not fit: 1d down — quarter-size the exposure',
        ], $this->session()));
        $this->assertTrue($out['applied']);
        $this->run->reload();
        $this->assertSame(40, (int) $this->run->getDeployPct());
        // the strategy-test log: the decision row records how much was deployed
        $d = \App\BotDecisionQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->orderByIdBotDecision(\Criteria::DESC)
            ->findOne();
        $this->assertSame(40, (int) $d->getDeployPct());
    }

    public function testDeployPctOutOfRangeIsRejected(): void
    {
        $out = $this->decode($this->tool()->handle([
            'p_low' => '62000', 'p_high' => '70000', 'n_levels' => 14,
            'deploy_pct' => 5, // below the 10% floor — a near-zero ladder is a config error
            'reason' => 'x',
        ], $this->session()));
        $this->assertFalse($out['applied']);
        $this->assertNotEmpty($out['errors']);
        $this->run->reload();
        $this->assertSame(100, (int) $this->run->getDeployPct(), 'nothing written on a rejected call');
    }

    public function testDryRunWritesNothing(): void
    {
        $before = (string) $this->run->getPLow();
        $out = $this->decode($this->tool()->handle([
            'p_low' => '62000', 'p_high' => '70000', 'n_levels' => 14,
            'reason' => 'x', 'dry_run' => true,
        ], $this->session()));
        $this->assertFalse($out['applied']);
        $this->assertArrayHasKey('preview', $out);
        $this->run->reload();
        $this->assertSame(0, bccomp((string) $this->run->getPLow(), $before, 2));
    }

    public function testRejectsFeeNegativeGeometry(): void
    {
        $out = $this->decode($this->tool()->handle([
            'p_low' => '65900', 'p_high' => '66100', 'n_levels' => 40, 'reason' => 'x',
        ], $this->session()));
        $this->assertFalse($out['applied']);
        $this->assertStringContainsStringIgnoringCase('fee', json_encode($out['errors']));
    }

    public function testRejectsRangeNotBracketingPrice(): void
    {
        // price 66000 but range entirely below it → grid would sit idle
        $out = $this->decode($this->tool('66000')->handle([
            'p_low' => '50000', 'p_high' => '60000', 'n_levels' => 12, 'reason' => 'x',
        ], $this->session()));
        $this->assertFalse($out['applied']);
        $this->assertStringContainsStringIgnoringCase('bracket', json_encode($out['errors']));
    }

    public function testRequiresReason(): void
    {
        $this->expectException(\ApiGoat\Mcp\ToolError::class);
        $this->tool()->handle(['p_low' => '62000', 'p_high' => '70000', 'n_levels' => 14], $this->session());
    }
}
