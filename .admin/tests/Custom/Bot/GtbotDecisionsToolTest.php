<?php

namespace Tests\Custom\Bot;

use ApiGoat\Sessions\AuthySession;
use App\Domains\Bot\DecisionScorer;
use App\GridRun;
use App\Mcp\Tools\GtbotDecisionsTool;
use PHPUnit\Framework\TestCase;

/**
 * gtbot_decisions — the strategy-test journal over MCP: every refit decision
 * with its posture, deploy_pct, applied_at and scored verdict, plus win-rate
 * aggregates by deployment band so the routine can data-mine what worked.
 */
class GtbotDecisionsToolTest extends TestCase
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
        $r->setLabel('journal-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setPLow('60000');
        $r->setPHigh('70000');
        $r->setNLevels(10);
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('1000');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->save();
        $this->run = $r;
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function decode(array $r): array
    {
        return json_decode($r['content'][0]['text'], true);
    }

    private function decision(int $deployPct, ?string $verdict, string $realized = '0'): void
    {
        $d = DecisionScorer::record($this->run, 'Claude', '60000', '70000', 10, 'test', '65000', $deployPct);
        if ($verdict !== null) {
            $d->setVerdict($verdict);
            $d->setEvalStatus('Scored');
            $d->setRealizedDelta($realized);
            $d->save();
        }
    }

    public function testListsJournalWithDeployPctAndAggregates(): void
    {
        $this->decision(100, 'Win', '5');
        $this->decision(40, 'Loss', '-2');
        $this->decision(40, 'Flat');
        $this->decision(70, null); // still pending

        $out = $this->decode((new GtbotDecisionsTool())->handle(['run' => (int) $this->run->getIdGridRun()], $this->createMock(AuthySession::class)));

        $this->assertCount(4, $out['decisions']);
        $this->assertSame(70, $out['decisions'][0]['deploy_pct'], 'newest first');
        $this->assertSame('Pending', $out['decisions'][0]['eval_status']);

        // aggregates: the strategy scoreboard by deployment band
        $bands = $out['summary']['by_deploy_band'];
        $this->assertSame(1, $bands['71-100']['scored']);
        $this->assertSame(1, $bands['71-100']['win']);
        $this->assertSame(2, $bands['10-40']['scored']);
        $this->assertSame(0, $bands['10-40']['win']);
        $this->assertEqualsWithDelta(-2.0, (float) $bands['10-40']['realized'], 0.001);
    }

    public function testVerdictFilter(): void
    {
        $this->decision(100, 'Win', '5');
        $this->decision(40, 'Loss', '-2');

        $out = $this->decode((new GtbotDecisionsTool())->handle([
            'run' => (int) $this->run->getIdGridRun(),
            'verdict' => 'Win',
        ], $this->createMock(AuthySession::class)));
        $this->assertCount(1, $out['decisions']);
        $this->assertSame('Win', $out['decisions'][0]['verdict']);
    }
}
