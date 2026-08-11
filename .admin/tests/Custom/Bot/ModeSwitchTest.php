<?php

namespace Tests\Custom\Bot;

use App\BotCommandQuery;
use App\Domains\Dashboard\ModeSwitch;
use App\GridRun;
use App\GridRunQuery;
use PHPUnit\Framework\TestCase;

/**
 * The system trades ONE shared wallet, so simulated-vs-real is a SYSTEM mode
 * (see ModeSwitch's class doc): systemMode() reports mixed the moment two
 * non-Done runs disagree, and flipAll() is the only writer that flips every
 * non-Done run + enqueues its Reload command in one shot.
 */
class ModeSwitchTest extends TestCase
{
    private static bool $booted = false;
    private GridRun $run;
    /** Non-Done runs that already existed in the dev DB before this test's
     *  own fixture rows — a real dashboard click or a leftover run from
     *  another test session persists outside this test's transaction, so
     *  global counts must be measured against this baseline, not zero. */
    private int $ambientRuns = 0;

    public static function setUpBeforeClass(): void
    {
        if (self::$booted) {
            return;
        }
        $admin = dirname(__DIR__, 3);
        if (!is_file($admin . '/config/Built/config.php')) {
            self::markTestSkipped('project not built');
        }
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
        // Measured BEFORE this test creates any fixture rows, so it reflects
        // only pre-existing (ambient) non-Done runs.
        $this->ambientRuns = GridRunQuery::create()->filterByStatus('Done', \Criteria::NOT_EQUAL)->count();
        $r = new GridRun();
        $r->setLabel('modeswitchtest-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Live');
        $r->setSimulated(true);
        $r->setPLow('90');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('1000');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('1000');
        $r->setMaxOrderQuote('500');
        $r->setDailyLossLimitQuote('100');
        $r->save();
        $this->run = $r;
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function makeRun(string $symbol, bool $simulated, string $status): GridRun
    {
        $r = new GridRun();
        $r->setLabel('modeswitchtest-' . bin2hex(random_bytes(4)));
        $r->setSymbol($symbol);
        $r->setStatus($status);
        $r->setSimulated($simulated);
        $r->setPLow('90');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('1000');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('1000');
        $r->setMaxOrderQuote('500');
        $r->setDailyLossLimitQuote('100');
        $r->save();
        return $r;
    }

    public function testSystemModeAndFlipAll(): void
    {
        // setUp created one simulated Live run; add a real one → mixed
        $other = $this->makeRun('ETHUSDT', false, 'Live');
        $this->assertSame('mixed', ModeSwitch::systemMode());

        $res = ModeSwitch::flipAll(true);
        // flipAll touches every non-Done run, so the total includes whatever
        // ambient non-Done runs already existed in the dev DB — assert the
        // baseline plus this test's own two rows, not a hardcoded 2.
        $this->assertSame($this->ambientRuns + 2, $res['runs']);
        $this->assertSame($this->ambientRuns + 2, $res['commands']);
        $this->assertSame('simulated', ModeSwitch::systemMode());
        // Scope to this test's own runs: a real dashboard click can leave
        // other Reload/'dashboard-mode' bot_command rows in the DB, so a
        // global count would drift with ambient state.
        $runIds = [(int) $this->run->getIdGridRun(), (int) $other->getIdGridRun()];
        $this->assertSame(count($runIds), BotCommandQuery::create()
            ->filterByIdGridRun($runIds, \Criteria::IN)
            ->filterByCommand('Reload')->filterByNote('dashboard-mode')->count());

        // Done runs are untouched
        $done = $this->makeRun('BNBUSDT', false, 'Done');
        ModeSwitch::flipAll(true);
        $done->reload();
        $this->assertFalse((bool) $done->getSimulated());
    }

    public function testSystemModeAllSimulated(): void
    {
        // Ambient non-Done runs in the dev DB may already be real-mode; flip
        // everything (ambient + this test's run) to simulated first, mirroring
        // the dashboard's own flipAll semantics, so the assertion doesn't
        // depend on ambient DB state.
        ModeSwitch::flipAll(true);
        $this->assertSame('simulated', ModeSwitch::systemMode());
    }

    public function testSystemModeAllReal(): void
    {
        ModeSwitch::flipAll(false);
        $this->assertSame('real', ModeSwitch::systemMode());
    }

    public function testSystemModeNoneWhenNoNonDoneRuns(): void
    {
        $this->run->setStatus('Done');
        $this->run->save();
        // Self-certifying: 'none' only holds if the dev DB has no OTHER
        // non-Done runs left over from a real session. Skip loudly rather
        // than assert against ambient state we don't control, mirroring
        // BudgetInvariantTest's ambient-sizing pattern.
        $ambient = GridRunQuery::create()->filterByStatus('Done', \Criteria::NOT_EQUAL)->count();
        if ($ambient > 0) {
            $this->markTestSkipped("ambient non-Done runs ($ambient) exist in the dev DB — systemMode() cannot report 'none'");
        }
        $this->assertSame('none', ModeSwitch::systemMode());
    }
}
