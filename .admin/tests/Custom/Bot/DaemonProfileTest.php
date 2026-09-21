<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\Daemon;
use App\Domains\Bot\EventLog;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\BotEventQuery;
use App\GridRun;
use PHPUnit\Framework\TestCase;
use Tests\Custom\Bot\Support\ExchangeSim;

/**
 * Risk profile is a LIVE policy: the daemon re-derives + stamps the
 * profile-driven caps every tick (the routine reallocates slices hourly),
 * and NoLoss must refuse any command that would realize a loss.
 */
class DaemonProfileTest extends TestCase
{
    private static bool $booted = false;
    private ExchangeSim $sim;

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
        // the global drawdown stop has its own suite (DaemonDrawdownStopTest);
        // pin it OFF so this suite's scenarios only trip their own rails
        $dd = \App\ConfigQuery::create()->findOneByConfig('gtbot_max_drawdown_pct')
            ?? (new \App\Config())->setConfig('gtbot_max_drawdown_pct');
        $dd->setValue('0');
        $dd->save();
        $this->sim = new ExchangeSim();
        $this->sim->setPrice('150');
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function makeRun(array $overrides = []): GridRun
    {
        $r = new GridRun();
        $r->setLabel('profile-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualBase');
        $r->setBudgetQuote('550');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid('profile-' . bin2hex(random_bytes(3)));
        foreach ($overrides as $field => $value) {
            $setter = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
            $r->$setter($value);
        }
        $r->save();
        return $r;
    }

    private function makeDaemon(GridRun $run): Daemon
    {
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $d = new Daemon($run, $gw, false, new EventLog((int) $run->getIdGridRun(), false));
        $d->boot();
        return $d;
    }

    public function testTickRestampsCapsAfterSliceChange(): void
    {
        // fixture run: profile Balanced, budget 400 → daily cap 24
        $run = $this->makeRun(['profile' => 'Balanced', 'budget_quote' => '400']);
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $run->reload();
        $this->assertSame(0, bccomp('24', (string) $run->getDailyLossLimitQuote(), 8));

        // routine reallocates the slice out-of-band → next tick re-derives
        $run->setBudgetQuote('800');
        $run->save();
        $daemon->tick();
        $run->reload();
        $this->assertSame(0, bccomp('48', (string) $run->getDailyLossLimitQuote(), 8));
    }

    public function testFlattenCommandRefusedForNoLoss(): void
    {
        $run = $this->makeRun(['profile' => 'NoLoss', 'budget_quote' => '400']);
        $cmd = new \App\BotCommand();
        $cmd->setIdGridRun((int) $run->getIdGridRun());
        $cmd->setCommand('Flatten');
        $cmd->setCmdStatus('Pending');
        $cmd->save();

        $daemon = $this->makeDaemon($run);
        $daemon->tick();

        $cmd->reload();
        $run->reload();
        $this->assertSame('Failed', (string) $cmd->getCmdStatus());
        $this->assertFalse((bool) $run->getKillSwitch(), 'refusal must not flip the kill switch');
        $refused = \App\BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByKind('flatten_refused')
            ->count();
        $this->assertSame(1, $refused);
    }

    /** Positive control for testFlattenCommandRefusedForNoLoss: a Balanced
     *  run's Flatten command is NOT profile-blocked — it proceeds normally
     *  (cmd Done, kill switch flipped). */
    public function testFlattenCommandAllowedForBalanced(): void
    {
        $run = $this->makeRun(['profile' => 'Balanced', 'budget_quote' => '400']);
        $run->setSellAtLoss(true); // the run switch is gated separately (SellAtLossTest)
        $run->save();
        $cmd = new \App\BotCommand();
        $cmd->setIdGridRun((int) $run->getIdGridRun());
        $cmd->setCommand('Flatten');
        $cmd->setCmdStatus('Pending');
        $cmd->save();

        $daemon = $this->makeDaemon($run);
        $daemon->tick();

        $cmd->reload();
        $run->reload();
        $this->assertSame('Done', (string) $cmd->getCmdStatus());
        $this->assertTrue((bool) $run->getKillSwitch(), 'Balanced profile must allow Flatten to proceed');
        $refused = \App\BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByKind('flatten_refused')
            ->count();
        $this->assertSame(0, $refused);
    }

    /**
     * Regression: tick()'s profile re-stamp must NOT fully replace $config
     * with configFromRun() — that would silently adopt a pending refit's
     * geometry (still unapplied — maybeRefit is deferring because the run is
     * holding inventory) into risk calcs, while the actual ladder stays on
     * the OLD geometry. Concretely: a fresh buy placement's breakout floor
     * (config['p_low'] * (1 - buffer)) must keep using the OLD p_low.
     *
     * MaxBuyLevelsBelow=1 keeps level L0 (100) unarmed at boot (window
     * starts at L1/120); a later price drop to 105 both (a) fully fills the
     * still-open L1 buy — which the daemon's own ledger only reconciles
     * AFTER maybeRefit runs this tick (one-tick lag), keeping maybeRefit's
     * partialBuys guard seeing L1 as still PartFilled from the prior tick,
     * forcing a DEFER — and (b) slides the window open, arming L0 fresh.
     * L0's breakout check is exactly where a corrupted config['p_low'] would
     * show up: old p_low (100) floor = 98 (105 clears it, order placed); the
     * pending-but-unapplied new p_low (120) floor = 117.6 (105 does NOT
     * clear it — HaltAndHold veto).
     */
    public function testProfileOnlyStampDoesNotClobberAppliedGeometryDuringDeferredRefit(): void
    {
        $run = $this->makeRun(['n_levels' => 5, 'max_buy_levels_below' => 1]);
        // profile Balanced (default), budget 550: daily cap 33
        $this->sim->setPrice('125');
        $daemon = $this->makeDaemon($run); // boot @125: only L1 (120) arms, L0 (100) stays out of the window
        $daemon->tick();

        $buys = array_filter($this->sim->open, fn ($o) => $o['side'] === 'BUY');
        $this->assertCount(1, $buys, 'L0 must stay unarmed at boot — outside the 1-level window');

        // partial-fill L1 so its ledger row lands PartFilled — this is what
        // makes maybeRefit() DEFER (not accept/reject) once geometry changes
        $l1Cid = array_key_first($buys);
        $this->sim->partialFill($l1Cid, '0.3');
        $daemon->tick(); // detects the partial, marks the ledger row PartFilled

        $run->reload();
        $dailyCapBefore = (string) $run->getDailyLossLimitQuote();
        $appliedBefore = $run->getAppliedGeometry();

        // routine writes a pending refit (unapplied — the run is about to be
        // holding, so maybeRefit will defer) AND a profile-only change
        $run->setPLow('120');
        $run->setPHigh('220');
        $run->setProfile('Cautious'); // budget unchanged: a pure risk-posture change
        $run->save();

        $this->sim->setPrice('105'); // fills the rest of L1; slides the window to arm L0
        $daemon->tick();

        $run->reload();
        // risk knob re-derived (Cautious daily-loss ratio 3% of 550 = 16.5, was Balanced 6% of 550 = 33)
        $this->assertSame(0, bccomp('16.5', (string) $run->getDailyLossLimitQuote(), 8));
        $this->assertNotSame($dailyCapBefore, (string) $run->getDailyLossLimitQuote());

        // the pending refit was deferred, not applied — geometry-driven
        // state must be untouched
        $this->assertSame($appliedBefore, $run->getAppliedGeometry(), 'applied_geometry must not move — the refit was deferred, not applied');
        $this->assertSame(0, \App\BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('refit_applied')->count());

        // the fresh L0 (100) placement must use the OLD applied p_low (100),
        // not the pending-unapplied 120 — at market 105 that means NO
        // breakout veto, and the order actually lands
        $this->assertSame(0, \App\BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('breakout_halt')->count(),
            'a corrupted config[p_low] (120 instead of the still-applied 100) would wrongly veto L0 at market 105');
        $buysAfter = array_filter($this->sim->open, fn ($o) => $o['side'] === 'BUY' && bccomp($o['price'], '100', 8) === 0);
        $this->assertCount(1, $buysAfter, 'L0 (100) must actually get placed once the window admits it');
    }
}
