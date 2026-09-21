<?php

namespace Tests\Custom\Bot;

use App\BotCommandQuery;
use App\BotEventQuery;
use App\BotOrder;
use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\FleetSlots;
use App\Domains\Bot\TelegramNotifier;
use App\Domains\Bot\TrendActivator;
use App\FleetSlot;
use App\FleetSlotQuery;
use App\GridRun;
use App\GridRunQuery;
use Tests\Builder\Support\DbTestCase;

/**
 * Machine-scaled trend arm: planners are pure (slices in, slices out);
 * pass() is the DB-backed state machine idle → active → winding_down → idle
 * driven by TrendRegime verdicts, with BudgetGuard ordering (decreases
 * first) and Reload commands for every run whose slice/deploy changed.
 *
 * Since 2026-09-19 the unit of work is a fleet SLOT: each pass here goes
 * through FleetSlots (reconcile, then pass the slot), exactly as the cron
 * does. The state machine and every threshold are unchanged — what moved is
 * where the state is kept (fleet_slot, not the bot_event chain) and the fact
 * that the verdict is journaled even when the arm cannot be scaled.
 */
class TrendActivatorTest extends DbTestCase
{
    private GridRun $trend;
    private GridRun $btc;
    private GridRun $bnb;
    /** @var string[] */
    private array $sent = [];
    private TelegramNotifier $notifier;


    protected function setUp(): void
    {
        parent::setUp();
        // park whatever the dev DB holds so only our three runs are active
        foreach (GridRunQuery::create()->filterByStatus(['DryRun', 'Testnet', 'Live'], \Criteria::IN)->find() as $r) {
            $r->setStatus('Halted');
            $r->save();
        }
        $c = ConfigQuery::create()->findOneByConfig('gtbot_shared_budget_quote') ?? (new Config())->setConfig('gtbot_shared_budget_quote');
        $c->setValue('1000');
        $c->save();
        // the dev DB has no sim wallet (equity 0) — the drawdown floor would trip every pass
        $d = ConfigQuery::create()->findOneByConfig('gtbot_max_drawdown_pct') ?? (new Config())->setConfig('gtbot_max_drawdown_pct');
        $d->setValue('0');
        $d->save();
        // targetSlice() is config-overridable and prod seeds it to 350; these
        // cases are written against the TARGET_SLICE default, so pin it or the
        // suite passes or fails on whatever the dev DB happens to hold
        $t = ConfigQuery::create()->findOneByConfig('gtbot_trend_target_slice') ?? (new Config())->setConfig('gtbot_trend_target_slice');
        $t->setValue(TrendActivator::TARGET_SLICE);
        $t->save();
        foreach (FleetSlotQuery::create()->find() as $fs) {
            $fs->delete();
        }
        $this->trend = $this->mkRun('BTCUSDT', '100', 'Trend', 5, 25);
        // grids at deploy 100: the whole ladder is funded, so the per-level floor applies in full
        $this->btc = $this->mkRun('BTCUSDT', '350', 'Grid', 5, 100);
        $this->bnb = $this->mkRun('BNBUSDT', '550', 'Grid', 6, 100);
        $this->sent = [];
        $this->notifier = new TelegramNotifier('t', 'c', function (string $url, array $post): array {
            $this->sent[] = $post['text'] ?? '';
            return ['status' => 200, 'body' => '{"ok":true}'];
        });
    }

    private function mkRun(string $symbol, string $budget, string $algo, int $levels, int $deploy): GridRun
    {
        $r = new GridRun();
        $r->setLabel(static::uniq('ta-' . $algo));
        $r->setSymbol($symbol);
        $r->setStatus('Live');
        $r->setAlgo($algo);
        $r->setSimulated(true);
        $r->setPLow('100');
        $r->setPHigh('200');
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
        $r->setRunUid(static::uniq('ta'));
        $r->save();
        return $r;
    }

    private function summaries(string $verdictShape): callable
    {
        $map = [
            'TREND_UP' => ['4h' => ['trend' => 'strong_up', 'adx14' => 33.0, 'er20' => 0.6, 'chop14' => 30.0, 'stale' => false],
                           '1d' => ['trend' => 'up', 'price' => 100.0, 'ema20' => 90.0, 'ema50' => 95.0, 'stale' => false]],
            'MIXED' => ['4h' => ['trend' => 'up', 'adx14' => 22.0, 'er20' => 0.1, 'chop14' => 30.0, 'stale' => false],
                        '1d' => ['trend' => 'down', 'price' => 100.0, 'ema20' => 110.0, 'ema50' => 120.0, 'stale' => false]],
            'HOSTILE' => ['4h' => ['trend' => 'down', 'adx14' => 35.0, 'er20' => 0.6, 'chop14' => 30.0, 'stale' => false],
                          '1d' => ['trend' => 'down', 'price' => 100.0, 'ema20' => 110.0, 'ema50' => 120.0, 'stale' => false]],
            // strong_up at ADX 39.7 / ER 0.13 (BTC 2026-08-28 09:18): MIXED for entry, TREND_UP while holding
            'KNIFE_EDGE' => ['4h' => ['trend' => 'strong_up', 'adx14' => 39.7, 'er20' => 0.13, 'chop14' => 46.0, 'stale' => false],
                             '1d' => ['trend' => 'up', 'price' => 100.0, 'ema20' => 90.0, 'ema50' => 95.0, 'stale' => false]],
            'STALE' => ['4h' => ['trend' => 'up', 'adx14' => 33.0, 'er20' => 0.6, 'chop14' => 30.0, 'stale' => true],
                        '1d' => ['trend' => 'up', 'price' => 100.0, 'ema20' => 90.0, 'ema50' => 95.0, 'stale' => false]],
        ];
        return static fn (string $symbol): array => $map[$verdictShape];
    }

    /** The slot the cron would hand this run's pass — reconciled like the cron does. */
    private function slotFor(GridRun $run): FleetSlot
    {
        FleetSlots::reconcileFromRuns();
        $slot = FleetSlots::byRun((int) $run->getIdGridRun());
        $this->assertNotNull($slot, 'run ' . $run->getIdGridRun() . ' fills no slot');
        return $slot;
    }

    private function pass(string $shape, bool $dry = false): array
    {
        return TrendActivator::pass($this->slotFor($this->trend), $this->notifier, $dry, $this->summaries($shape));
    }

    private function assertBudget(string $expected, GridRun $r): void
    {
        $this->assertSame(0, bccomp($expected, (string) $r->getBudgetQuote(), 8), "budget of run {$r->getIdGridRun()} is {$r->getBudgetQuote()}, expected $expected");
    }

    private function reload(GridRun $r): GridRun
    {
        return GridRunQuery::create()->findPk($r->getIdGridRun());
    }

    private function reloadsFor(GridRun $r): int
    {
        return BotCommandQuery::create()->filterByIdGridRun($r->getIdGridRun())->filterByCommand('Reload')->count();
    }

    /**
     * Give a run an open trend position: a filled buy (so investedQuote()
     * reports price × qty) and the engine_state the TrendEngine persists.
     */
    private function holdPosition(GridRun $run, string $price, string $qty): void
    {
        $o = new BotOrder();
        $o->setIdGridRun((int) $run->getIdGridRun());
        $o->setClientOrderId(static::uniq('ta-pos'));
        $o->setLevelIdx(1);
        $o->setSide('Buy');
        $o->setState('Filled');
        $o->setPrice($price);
        $o->setQty($qty);
        $o->setFilledQty($qty);
        $o->setSimulated(true);
        $o->save();
        $r = $this->reload($run);
        $r->setEngineState(json_encode(['algo' => 'Trend', 'entry' => $price, 'qty' => $qty, 'hwm' => $price, 'stop' => null]));
        $r->save();
    }

    /** A working (unfilled) BUY order: quote the arm has committed but not yet spent. */
    private function restingBuy(GridRun $run, string $price, string $qty, string $filled = '0'): void
    {
        $o = new BotOrder();
        $o->setIdGridRun((int) $run->getIdGridRun());
        $o->setClientOrderId(static::uniq('ta-buy'));
        $o->setLevelIdx(2);
        $o->setSide('Buy');
        $o->setState(bccomp($filled, '0', 8) > 0 ? 'PartFilled' : 'BUY_OPEN');
        $o->setPrice($price);
        $o->setQty($qty);
        $o->setFilledQty($filled);
        $o->setSimulated(true);
        $o->save();
    }

    /** Put the fleet in the shape prod run 1 was in: active, slice $slice, grids at their floors. */
    private function activeAtSlice(string $slice): void
    {
        $this->pass('TREND_UP');
        $this->pass('TREND_UP');
        $t = $this->reload($this->trend);
        $t->setBudgetQuote($slice);
        $t->save();
        $this->trend = $t;
        $this->sent = [];
    }

    private function slotTarget(string $target): void
    {
        $slot = $this->slotFor($this->trend);
        $slot->setTargetSlice($target);
        $slot->save();
    }

    private function reassertEvents(): int
    {
        return BotEventQuery::create()->filterByIdGridRun($this->trend->getIdGridRun())->filterByKind(TrendActivator::KIND_REASSERT)->count();
    }

    // ── pure planners ───────────────────────────────────────────────────

    public function testPlanActivateTrimsGridsProRataDownToFloors(): void
    {
        // floors: BTC 5 levels → 250, BNB 6 levels → 300; pool 1000, no headroom
        $plan = TrendActivator::planActivate('1000', '100', [
            7 => ['budget' => '350', 'floor' => '250'],
            4 => ['budget' => '550', 'floor' => '300'],
        ], '500');
        // only 450 is reachable (1000 − 550 floors): trend 100 → 450
        $this->assertSame('450', $plan['trend']);
        $this->assertSame(['7' => '250', '4' => '300'], array_map('strval', $plan['grids']));
        $this->assertSame('350', $plan['taken']);
    }

    /**
     * CHANGED 2026-09-21 (round 2): the grids' IDLE capital goes first and
     * pool headroom LAST — was the other way round.
     *
     * On a pool carrying a reserve, spending headroom first spent the reserve
     * on capital the grids had sitting idle above their floors: prod's arms
     * needed 3 USDT to reach their 350 target, took them out of the 65 USDT
     * reserve, and packed the fleet to exactly the cap with zero headroom.
     * Same total for the arm either way — a grid cut and a headroom spend
     * fund the same USDT — but the reserve survives.
     */
    public function testPlanActivateCutsGridIdleCapitalBeforeSpendingPoolHeadroom(): void
    {
        // pool 1000, slices 100/300/300 → 300 headroom idle; need 400, and the
        // grids have 400 idle above their floors, so headroom is not touched
        $plan = TrendActivator::planActivate('1000', '100', [
            7 => ['budget' => '300', 'floor' => '100'],
            4 => ['budget' => '300', 'floor' => '100'],
        ], '500');
        $this->assertSame('500', $plan['trend'], 'the arm still reaches its target');
        $this->assertSame(['7' => '100', '4' => '100'], array_map('strval', $plan['grids']));
        $this->assertSame('400', $plan['taken']);
    }

    /** Headroom is the REMAINDER: what the grids' floors would not give up. */
    public function testPlanActivateFallsBackToHeadroomOnceTheGridsAreOnTheirFloors(): void
    {
        // pool 1000, slices 100/300/300, floors 250/250 → the grids can give
        // 100, the other 300 of a 400 raise comes out of the idle headroom
        $plan = TrendActivator::planActivate('1000', '100', [
            7 => ['budget' => '300', 'floor' => '250'],
            4 => ['budget' => '300', 'floor' => '250'],
        ], '500');
        $this->assertSame('500', $plan['trend']);
        $this->assertSame(['7' => '250', '4' => '250'], array_map('strval', $plan['grids']));
    }

    public function testPlanActivateRespectsInvestedFloorAndNeverRaisesAGrid(): void
    {
        // BNB already holds 420 of inventory → floor 420; BTC sits below its level floor already (120 < 250) → untouched
        $plan = TrendActivator::planActivate('1000', '100', [
            7 => ['budget' => '120', 'floor' => '250'],
            4 => ['budget' => '780', 'floor' => '420'],
        ], '500');
        $this->assertSame('460', $plan['trend']);
        $this->assertSame(['7' => '120', '4' => '420'], array_map('strval', $plan['grids']));
    }

    public function testPlanActivateNoopWhenAlreadyAtTarget(): void
    {
        $plan = TrendActivator::planActivate('1000', '500', [7 => ['budget' => '250', 'floor' => '100']], '500');
        $this->assertSame('500', $plan['trend']);
        $this->assertSame('0', $plan['taken']);
        $this->assertSame(['7' => '250'], array_map('strval', $plan['grids']));
    }

    public function testPlanReleaseRestoresPreActivationSlices(): void
    {
        $plan = TrendActivator::planRelease('1000', '450', '100', [
            7 => ['budget' => '250', 'before' => '350'],
            4 => ['budget' => '300', 'before' => '550'],
        ]);
        $this->assertSame('100', $plan['trend']);
        $this->assertSame(['7' => '350', '4' => '550'], array_map('strval', $plan['grids']));
        $this->assertSame('1000', bcadd(bcadd($plan['trend'], $plan['grids'][7], 0), $plan['grids'][4], 0));
    }

    public function testPlanReleaseNeverLowersAGridTheRoutineRaisedMeanwhile(): void
    {
        $plan = TrendActivator::planRelease('1000', '450', '100', [
            7 => ['budget' => '300', 'before' => '350'],
            4 => ['budget' => '400', 'before' => '300'],   // routine grew BNB above its pre-activation slice
        ]);
        $this->assertSame('350', (string) $plan['grids'][7]);
        $this->assertSame('400', (string) $plan['grids'][4]);
    }

    public function testPlanReleaseNeverOvercommitsIfGridsGrewMeanwhile(): void
    {
        // routine already pushed grids to 300/350 while the arm was active (sum 450+300+350=1100 can't happen,
        // but 450+250+300 with shared budget lowered to 900 can): cap the give-back
        $plan = TrendActivator::planRelease('900', '450', '100', [
            7 => ['budget' => '250', 'before' => '350'],
            4 => ['budget' => '300', 'before' => '550'],
        ]);
        $sum = bcadd(bcadd($plan['trend'], $plan['grids'][7], 0), $plan['grids'][4], 0);
        $this->assertLessThanOrEqual(0, bccomp($sum, '900', 0));
        $this->assertSame('100', $plan['trend']);
    }

    // ── state machine ───────────────────────────────────────────────────

    public function testFirstTrendUpPassOnlyArms(): void
    {
        $r = $this->pass('TREND_UP');
        $this->assertSame('TREND_UP', $r['verdict']);
        $this->assertSame('hold', $r['decision']);
        $this->assertSame('idle', $r['state']);
        $this->assertBudget('100', $this->reload($this->trend));
    }

    public function testActivationDeploysTheWholeSlice(): void
    {
        // 2026-09-16 (refit-evidence §12): ACTIVE_DEPLOY_PCT 50 left half of
        // the trend slice idle for a top-up path that §10 refuted — the arm
        // now deploys its whole slice on activation.
        $this->pass('TREND_UP');
        $this->pass('TREND_UP');
        $this->assertSame(100, (int) $this->reload($this->trend)->getDeployPct());
    }

    public function testSecondConsecutiveTrendUpActivates(): void
    {
        $this->pass('TREND_UP');
        $r = $this->pass('TREND_UP');
        $this->assertSame('activate', $r['decision']);
        $this->assertSame('active', $r['state']);
        $t = $this->reload($this->trend);
        $this->assertBudget('450', $t); // floors 250+300 cap it
        $this->assertSame(TrendActivator::ACTIVE_DEPLOY_PCT, (int) $t->getDeployPct());
        $this->assertBudget('250', $this->reload($this->btc));
        $this->assertBudget('300', $this->reload($this->bnb));
        $this->assertSame(1, $this->reloadsFor($this->trend));
        $this->assertSame(1, $this->reloadsFor($this->btc));
        $this->assertSame(1, $this->reloadsFor($this->bnb));
        $this->assertCount(1, $this->sent);
        $this->assertStringContainsString('trend_activate', $this->sent[0]);
        $e = BotEventQuery::create()->filterByIdGridRun($this->trend->getIdGridRun())->filterByKind(TrendActivator::KIND_ACTIVATE)->findOne();
        $p = json_decode((string) $e->getPayload(), true);
        $this->assertSame('100', $p['trend_before']);
        $this->assertSame(25, $p['deploy_before']);
        $this->assertSame(['350', '550'], array_values(array_map('strval', $p['grids_before'])));
    }

    public function testInterruptedStreakDoesNotActivate(): void
    {
        $this->pass('TREND_UP');
        $this->pass('MIXED');
        $r = $this->pass('TREND_UP');
        $this->assertSame('hold', $r['decision']);
        $this->assertBudget('100', $this->reload($this->trend));
    }

    public function testStalePassWritesNothingAndDoesNotBreakTheStreak(): void
    {
        $this->pass('TREND_UP');
        $r = $this->pass('STALE');
        $this->assertNull($r['verdict']);
        $this->assertSame('skip', $r['decision']);
        $r = $this->pass('TREND_UP');
        $this->assertSame('activate', $r['decision']);
    }

    public function testDryRunComputesButWritesNothing(): void
    {
        $this->pass('TREND_UP');
        $r = $this->pass('TREND_UP', true);
        $this->assertSame('activate', $r['decision']);
        $this->assertTrue($r['dry']);
        $this->assertBudget('100', $this->reload($this->trend));
        $this->assertSame(0, $this->reloadsFor($this->trend));
        $this->assertSame(1, BotEventQuery::create()->filterByIdGridRun($this->trend->getIdGridRun())->filterByKind(TrendActivator::KIND_REGIME)->count());
    }

    public function testActiveHoldsThroughThreeNonTrendUpThenDeactivatesOnFourth(): void
    {
        $this->pass('TREND_UP');
        $this->pass('TREND_UP');
        $this->sent = [];
        foreach ([1, 2, 3] as $i) {
            $r = $this->pass('MIXED');
            $this->assertSame('hold', $r['decision'], "MIXED pass $i must hold");
            $this->assertSame('active', $r['state']);
        }
        $r = $this->pass('MIXED');
        $this->assertSame('deactivate', $r['decision']);
        $this->assertSame('winding_down', $r['state']);
        $t = $this->reload($this->trend);
        $this->assertSame(0, (int) $t->getDeployPct());
        $this->assertBudget('450', $t); // slice stays parked until flat
        $this->assertSame(2, $this->reloadsFor($this->trend));
        $this->assertCount(1, $this->sent);
        $this->assertStringContainsString('trend_deactivate', $this->sent[0]);
    }

    public function testKnifeEdgeTapeIsMixedForEntryButTrendUpWhileHolding(): void
    {
        $r = $this->pass('KNIFE_EDGE');
        $this->assertSame('MIXED', $r['verdict']);
        $this->pass('TREND_UP');
        $this->pass('TREND_UP');
        $r = $this->pass('KNIFE_EDGE');
        $this->assertSame('TREND_UP', $r['verdict']);
        $this->assertSame('hold', $r['decision']);
        $this->assertSame('active', $r['state']);
    }

    public function testGridFloorScalesWithDeployAndIgnoresKilledLadders(): void
    {
        $g = $this->mkRun('BTCUSDT', '550', 'Grid', 13, 25);
        $this->assertSame('163', TrendActivator::gridFloor($g)); // ceil(13 × 50 × 0.25)
        $g->setDeployPct(100);
        $this->assertSame('650', TrendActivator::gridFloor($g));
        $g->setDeployPct(0);
        $this->assertSame(TrendActivator::MIN_SLICE, TrendActivator::gridFloor($g)); // no ladder funded
        $g->setDeployPct(100);
        $g->setKillSwitch(true);
        $this->assertSame(TrendActivator::MIN_SLICE, TrendActivator::gridFloor($g)); // killed: nothing working
    }

    public function testActivationTargetsTheConfiguredSlice(): void
    {
        $c = ConfigQuery::create()->findOneByConfig(TrendActivator::CONFIG_TARGET_SLICE) ?? (new Config())->setConfig(TrendActivator::CONFIG_TARGET_SLICE);
        $c->setValue('350');
        $c->save();
        try {
            $this->assertSame('350', TrendActivator::targetSlice());
            $this->pass('TREND_UP');
            $r = $this->pass('TREND_UP');
            $this->assertSame('activate', $r['decision']);
            $this->assertBudget('350', $this->reload($this->trend));
            // 250 taken from the 100 headroom first, then pro-rata from the grids
            $this->assertSame(0, bccomp('1000', bcadd(bcadd('350', (string) $this->reload($this->btc)->getBudgetQuote(), 0), (string) $this->reload($this->bnb)->getBudgetQuote(), 0), 0));
        } finally {
            $c->setValue('');
            $c->save();
            \App\ConfigPeer::clearInstancePool();
        }
        $this->assertSame(TrendActivator::TARGET_SLICE, TrendActivator::targetSlice(), 'empty config → the constant');
    }

    public function testActivationTakesMoreFromRegimeCappedGrids(): void
    {
        // both grids parked at the hostile cap (25%): 3/4 of their slices are
        // idle USDT — the trend arm may take it down to the funded quarter
        $this->btc->setDeployPct(25);
        $this->btc->save();
        $this->bnb->setDeployPct(25);
        $this->bnb->save();
        $this->pass('TREND_UP');
        $r = $this->pass('TREND_UP');
        $this->assertSame('activate', $r['decision']);
        $this->assertBudget(TrendActivator::TARGET_SLICE, $this->reload($this->trend));
        // 400 taken pro-rata from 350 and 550 (floors 63 / 75 leave plenty of room)
        $this->assertBudget('194', $this->reload($this->btc));
        $this->assertBudget('306', $this->reload($this->bnb));
    }

    public function testScalesEmaCross1dCoreRuns(): void
    {
        $t = $this->reload($this->trend);
        $t->setTrendSignal('EmaCross1d');
        $t->save();
        $this->trend = $t;
        $this->pass('TREND_UP');
        $r = $this->pass('TREND_UP');
        $this->assertSame('activate', $r['decision']);
        $this->assertSame('active', $r['state']);
        $this->assertSame(TrendActivator::ACTIVE_DEPLOY_PCT, (int) $this->reload($this->trend)->getDeployPct());
        $this->assertStringContainsString('inventory core', implode(' ', $r['notes']));

        // and it deactivates like any other Trend run
        $r = $this->pass('HOSTILE');
        $this->assertSame('deactivate', $r['decision']);
        $this->assertSame(0, (int) $this->reload($this->trend)->getDeployPct());
    }

    public function testOneSlotPerSymbolPicksTheNewestLiveArm(): void
    {
        // setUp's $this->trend is a Live Donchian arm on BTCUSDT. A symbol has
        // ONE trend slot, so a second Live arm on it does not get its own
        // machine-scaling pass — the slot resolves to the newest Live run and
        // that is the arm the activator owns.
        $donchian = $this->reload($this->trend);
        $donchian->setBudgetQuote('0');
        $donchian->save();
        $btc = $this->reload($this->btc);
        $btc->setBudgetQuote('250');
        $btc->save();
        $this->btc = $btc;
        $c = ConfigQuery::create()->findOneByConfig('gtbot_trend_target_slice') ?? (new Config())->setConfig('gtbot_trend_target_slice');
        $c->setValue('400');
        $c->save();

        $core = $this->mkRun('BTCUSDT', '200', 'Trend', 5, 100);
        $core->setTrendSignal('EmaCross1d');
        $core->save();
        $slot = $this->slotFor($core);
        $this->assertSame((int) $core->getIdGridRun(), (int) $slot->getIdGridRun(), 'newest Live arm owns the slot');
        $r = TrendActivator::pass($slot, $this->notifier, false, $this->summaries('TREND_UP'));
        $this->assertSame('hold', $r['decision'], 'first TREND_UP pass: confirming');
        $r = TrendActivator::pass($slot, $this->notifier, false, $this->summaries('TREND_UP'));
        $this->assertSame('activate', $r['decision']);
        $this->assertSame('active', $r['state']);
        $this->assertSame(TrendActivator::ACTIVE_DEPLOY_PCT, (int) $this->reload($core)->getDeployPct());
        $this->assertSame(25, (int) $this->reload($donchian)->getDeployPct(), 'the other arm is not the slot\'s — its operator deploy is untouched');
    }

    public function testCoreWithALegacyPositionIsNotScaledUntilFlat(): void
    {
        $t = $this->reload($this->trend);
        $t->setTrendSignal('EmaCross1d');
        $t->setEngineState(json_encode(['entry' => '100', 'qty' => '1', 'stop' => null]));
        $t->save();
        $this->trend = $t;

        $this->pass('TREND_UP');
        $r = $this->pass('TREND_UP');
        $this->assertSame('skip', $r['decision']);
        $this->assertStringContainsString('legacy position', implode(' ', $r['notes']));
        // the verdict is journaled anyway — a parked arm still owes the
        // routine a regime read (the defect fleet_slot exists to fix)
        $this->assertSame(2, BotEventQuery::create()->filterByIdGridRun($this->trend->getIdGridRun())->filterByKind(TrendActivator::KIND_REGIME)->count());
        $this->assertSame(25, (int) $this->reload($this->trend)->getDeployPct(), 'operator deploy untouched while the legacy position is held');

        // once flat, the confirmed streak the slot kept counting activates it
        $t = $this->reload($this->trend);
        $t->setEngineState(null);
        $t->save();
        $this->trend = $t;
        $r = $this->pass('TREND_UP');
        $this->assertSame('activate', $r['decision']);
        $this->assertSame('active', $r['state']);
    }

    public function testHostileDeactivatesImmediately(): void
    {
        $this->pass('TREND_UP');
        $this->pass('TREND_UP');
        $r = $this->pass('HOSTILE');
        $this->assertSame('deactivate', $r['decision']);
        $this->assertSame(0, (int) $this->reload($this->trend)->getDeployPct());
    }

    public function testWindingDownReleasesWhenFlatAndRestoresGrids(): void
    {
        $this->pass('TREND_UP');
        $this->pass('TREND_UP');
        $this->pass('HOSTILE');
        // flat: no engine position, no open orders → the next pass releases
        $r = $this->pass('MIXED');
        $this->assertSame('release', $r['decision']);
        $this->assertSame('idle', $r['state']);
        $t = $this->reload($this->trend);
        $this->assertBudget('100', $t);
        $this->assertSame(25, (int) $t->getDeployPct());
        $this->assertBudget('350', $this->reload($this->btc));
        $this->assertBudget('550', $this->reload($this->bnb));
    }

    public function testWindingDownHoldsWhilePositionIsOpen(): void
    {
        $this->pass('TREND_UP');
        $this->pass('TREND_UP');
        $this->pass('HOSTILE');
        $t = $this->reload($this->trend);
        $t->setEngineState(json_encode(['entry' => '100', 'qty' => '1', 'stop' => '95']));
        $t->save();
        $this->trend = $t;
        $r = $this->pass('MIXED');
        $this->assertSame('hold', $r['decision']);
        $this->assertSame('winding_down', $r['state']);
        $this->assertBudget('450', $this->reload($this->trend));
    }

    public function testWindingDownReArmsAtTheDerivedSliceNotTheActivationPayload(): void
    {
        // A re-arm only happens on an arm that is NOT flat (a flat one is
        // released), so replaying activation.trend_after re-pinned the whole
        // original slice on a position that cannot spend it — the same defect
        // derivedSlice() removed from reassert(), for one pass.
        $this->pass('TREND_UP');
        $this->pass('TREND_UP');
        $this->assertBudget('450', $this->reload($this->trend), 'the activation funded 450');
        $this->pass('HOSTILE');
        $this->holdPosition($this->trend, '100', '3');    // 300 of committed quote
        $this->trend = $this->reload($this->trend);

        $this->pass('TREND_UP');
        $r = $this->pass('TREND_UP');
        $this->assertSame('activate', $r['decision']);
        $this->assertSame('active', $r['state']);
        $t = $this->reload($this->trend);
        $this->assertSame(TrendActivator::ACTIVE_DEPLOY_PCT, (int) $t->getDeployPct());
        $this->assertBudget('300', $t);   // the position, not the payload's 450
    }

    public function testAReArmRefusedByTheGuardLeavesTheSlotWindingDownAndWarns(): void
    {
        // A re-arm is a bare write, so a refusal must be journaled and
        // retried, never thrown out of pass() — the whole slot's pass (and,
        // under the cron, every slot after it) used to die on it.
        $this->pass('TREND_UP');
        $this->pass('TREND_UP');
        $this->pass('HOSTILE');
        $this->holdPosition($this->trend, '100', '3');
        $this->trend = $this->reload($this->trend);
        $this->pass('TREND_UP');
        // the pool is taken behind the activator's back: 450 (trend) + 250 +
        // 900 is already over the 1000 cap, so any write is refused
        \Propel::getConnection()
            ->prepare('UPDATE grid_run SET budget_quote = 900 WHERE id_grid_run = ?')
            ->execute([(int) $this->bnb->getIdGridRun()]);

        $r = $this->pass('TREND_UP');
        $this->assertSame('activate', $r['decision']);
        $this->assertSame('winding_down', $r['state'], 'the slot state is left for the next pass to retry');
        \App\GridRunPeer::clearInstancePool();
        $this->assertBudget('450', $this->reload($this->trend), 'nothing was written');
        $this->assertSame('winding_down', (string) $this->slotFor($this->trend)->getState());
        $w = BotEventQuery::create()
            ->filterByIdGridRun($this->trend->getIdGridRun())
            ->filterByKind(TrendActivator::KIND_ERROR)
            ->findOne();
        $this->assertNotNull($w);
        $this->assertSame('Warn', (string) $w->getLevel());
        $this->assertStringContainsString('could not re-arm the trend arm', (string) $w->getMessage());
    }

    public function testActiveReassertsSliceIfSomeoneElseMovedIt(): void
    {
        $this->pass('TREND_UP');
        $this->pass('TREND_UP');
        $t = $this->reload($this->trend);
        $t->setBudgetQuote('200');
        $t->setDeployPct(10);
        $t->save();
        $this->trend = $t;
        $this->sent = [];
        $r = $this->pass('TREND_UP');
        $this->assertSame('hold', $r['decision']);
        $this->assertTrue($r['reasserted']);
        $t = $this->reload($this->trend);
        $this->assertBudget('450', $t);
        $this->assertSame(TrendActivator::ACTIVE_DEPLOY_PCT, (int) $t->getDeployPct());
        $this->assertCount(1, $this->sent);
        $this->assertStringContainsString('trend_reassert', $this->sent[0]);
    }

    // ── derived slice (D2, 2026-09-19) ──────────────────────────────────

    public function testHoldingArmIsSizedDownToThePositionItTiesUp(): void
    {
        // prod run 1, 2026-09-19: 210.67 invested on a 422 slice. 211 of that
        // slice was unreachable — the engine never adds to an open position —
        // and charged to the shared pool. The arm is sized to the position
        // and nothing else: the per-ladder-level floor that used to hold it
        // at 250 is a grid's floor, not an arm's (D2, 2026-09-21).
        $this->activeAtSlice('422');
        $this->holdPosition($this->trend, '210.67', '1');

        $r = $this->pass('TREND_UP');
        $this->assertSame('hold', $r['decision']);
        $this->assertTrue($r['reasserted']);
        $this->assertBudget('211', $this->reload($this->trend));
        $this->assertSame(TrendActivator::ACTIVE_DEPLOY_PCT, (int) $this->reload($this->trend)->getDeployPct());
        // a decrease funds nothing: the grids are not touched, the 211 goes
        // back to the pool as headroom for gtbot-allocate
        $this->assertBudget('250', $this->reload($this->btc));
        $this->assertBudget('300', $this->reload($this->bnb));
        $this->assertSame(1, $this->reloadsFor($this->btc), 'only the activation reloaded the grid');
        $this->assertSame(1, $this->reassertEvents());
        $e = BotEventQuery::create()->filterByIdGridRun($this->trend->getIdGridRun())->filterByKind(TrendActivator::KIND_REASSERT)->findOne();
        $this->assertStringContainsString('holding', (string) $e->getMessage());
        $p = json_decode((string) $e->getPayload(), true);
        $this->assertSame('holding', $p['rule']);
        $this->assertSame('211', $p['slice']);
        $this->assertSame('422', $p['slice_was']);
    }

    public function testHoldingArmKeepsTheWholePositionWhenItIsAboveTheFloor(): void
    {
        $this->activeAtSlice('422');
        $this->holdPosition($this->trend, '300', '1');

        $r = $this->pass('TREND_UP');
        $this->assertTrue($r['reasserted']);
        $this->assertBudget('300', $this->reload($this->trend));
    }

    public function testFlatArmIsRaisedToTheSlotTargetThroughThePlanner(): void
    {
        $this->activeAtSlice('100');
        // give the grids their pre-activation slices back: pool 1000 is fully
        // allocated (100 + 350 + 550), so the raise can only come from them
        foreach ([[$this->btc, '350'], [$this->bnb, '550']] as [$g, $b]) {
            $g = $this->reload($g);
            $g->setBudgetQuote($b);
            $g->save();
        }
        $this->slotTarget('350');

        $r = $this->pass('TREND_UP');
        $this->assertTrue($r['reasserted']);
        $this->assertBudget('350', $this->reload($this->trend));
        // trimmed pro-rata, both still above their floors (250 / 300)
        $this->assertBudget('252', $this->reload($this->btc));
        $this->assertBudget('398', $this->reload($this->bnb));
        $e = BotEventQuery::create()->filterByIdGridRun($this->trend->getIdGridRun())->filterByKind(TrendActivator::KIND_REASSERT)->findOne();
        $this->assertSame('flat', json_decode((string) $e->getPayload(), true)['rule']);
    }

    public function testReassertWritesNothingWhenTheDerivedSliceIsAlreadyInPlace(): void
    {
        $this->pass('TREND_UP');
        $this->pass('TREND_UP');
        $this->assertBudget('450', $this->reload($this->trend));
        $this->sent = [];
        $this->slotTarget('450');   // flat arm, already at its target

        $r = $this->pass('TREND_UP');
        $this->assertSame('hold', $r['decision']);
        $this->assertFalse($r['reasserted']);
        $this->assertBudget('450', $this->reload($this->trend));
        $this->assertSame(0, $this->reassertEvents());
        $this->assertSame(1, $this->reloadsFor($this->trend), 'only the activation reloaded');
        $this->assertCount(0, $this->sent);
    }

    public function testBudgetGuardRefusalIsWarnedAndWritesNothing(): void
    {
        $this->activeAtSlice('450');
        $this->holdPosition($this->trend, '300', '1');
        // the pool is lowered under the committed slices: even the DECREASE
        // to 300 leaves the sum (250 + 300 + 300) above it, so BudgetGuard
        // refuses the write
        $c = ConfigQuery::create()->findOneByConfig('gtbot_shared_budget_quote');
        $c->setValue('500');
        $c->save();

        $r = $this->pass('TREND_UP');
        $this->assertFalse($r['reasserted']);
        $this->assertBudget('450', $this->reload($this->trend), 'nothing partial was written');
        $this->assertBudget('250', $this->reload($this->btc));
        $this->assertSame(0, $this->reassertEvents());
        $w = BotEventQuery::create()->filterByIdGridRun($this->trend->getIdGridRun())->filterByKind(TrendActivator::KIND_ERROR)->findOne();
        $this->assertNotNull($w);
        $this->assertSame('Warn', (string) $w->getLevel());
        $this->assertStringContainsString('could not re-assert the trend slice', (string) $w->getMessage());
    }

    public function testHoldingArmCountsCapitalRestingInWorkingBuys(): void
    {
        // a breakout arm places its whole tranche batch in ONE tick, so at a
        // 15-min pass part of it is routinely still on the book. Sizing to
        // the filled part alone lets the rest fill past the asserted slice.
        $this->activeAtSlice('422');
        $t = $this->reload($this->trend);
        $t->setNLevels(1);          // floor 50, so the position decides
        $t->save();
        $this->trend = $t;
        $this->holdPosition($this->trend, '100', '1');   // filled tranche: 100
        $this->restingBuy($this->trend, '100', '1');     // resting tranche: 100

        $r = $this->pass('TREND_UP');
        $this->assertTrue($r['reasserted']);
        $this->assertBudget('200', $this->reload($this->trend));
        $e = BotEventQuery::create()->filterByIdGridRun($this->trend->getIdGridRun())->filterByKind(TrendActivator::KIND_REASSERT)->findOne();
        $this->assertStringContainsString('working buys', (string) $e->getMessage());
    }

    public function testHoldingArmCountsOnlyTheUnfilledRemainderOfAPartFill(): void
    {
        $this->activeAtSlice('422');
        $t = $this->reload($this->trend);
        $t->setNLevels(1);
        $t->save();
        $this->trend = $t;
        $this->holdPosition($this->trend, '100', '1');            // 100
        $this->restingBuy($this->trend, '100', '1', '0.4');       // 60 still to spend

        $this->pass('TREND_UP');
        $this->assertBudget('160', $this->reload($this->trend));
    }

    public function testCoreArmHoldingKeepsItsTargetSliceForItsStaggeredTranches(): void
    {
        // an EmaCross1d core DOES add to an open position — staggered
        // tranches are its designed deployment, not the refuted top-up — so
        // the holding rule must not size it down to its first fill.
        $t = $this->reload($this->trend);
        $t->setTrendSignal('EmaCross1d');
        $t->save();
        $this->trend = $t;
        $this->pass('TREND_UP');
        $this->pass('TREND_UP');
        $t = $this->reload($this->trend);
        $t->setBudgetQuote('350');
        $t->save();
        $this->trend = $t;
        $this->slotTarget('350');
        $this->holdPosition($this->trend, '100', '1');   // the holding rule would say max(100, 250) = 250
        $this->sent = [];

        $r = $this->pass('TREND_UP');
        $this->assertSame('hold', $r['decision']);
        $this->assertFalse($r['reasserted'], 'the core is already at its target');
        $this->assertBudget('350', $this->reload($this->trend));
        $this->assertSame(0, $this->reassertEvents());
        $this->assertCount(0, $this->sent);
    }

    public function testHoldingArmsRaiseNeverTrimsAGridAndIsSilentWhenThePoolHasNothing(): void
    {
        // its entries are gated until it is flat, so a grid must not be
        // trimmed for it — the pinned-capital defect in miniature. Headroom
        // is all it may have, and there is none: an unreachable target must
        // be SILENT (fundingPlan's contract), not an event every 15 minutes.
        $this->activeAtSlice('450');
        foreach ([[$this->btc, '350'], [$this->bnb, '550']] as [$g, $b]) {
            $g = $this->reload($g);
            $g->setBudgetQuote($b);
            $g->save();
        }
        $t = $this->reload($this->trend);
        $t->setBudgetQuote('100');
        $t->save();
        $this->trend = $t;
        $this->holdPosition($this->trend, '300', '1');  // committed 300 on a 100 slice

        $r = $this->pass('TREND_UP');
        $this->assertFalse($r['reasserted']);
        $this->assertBudget('100', $this->reload($this->trend));
        $this->assertBudget('350', $this->reload($this->btc), 'no grid funds a gated arm');
        $this->assertBudget('550', $this->reload($this->bnb));
        $this->assertSame(0, $this->reassertEvents());
        $this->assertSame(
            0,
            BotEventQuery::create()->filterByIdGridRun($this->trend->getIdGridRun())->filterByKind(TrendActivator::KIND_ERROR)->count(),
            'a target the pool cannot reach is planned away, not refused every pass'
        );
        $this->assertCount(0, $this->sent, 'and nothing is Telegrammed either');
    }

    public function testHoldingArmsRaiseTakesWhateverHeadroomThereIs(): void
    {
        // partial progress, exactly as the funding planner makes for the
        // target rule: what the pool HAS is reachable, the rest waits.
        $this->activeAtSlice('450');
        foreach ([[$this->btc, '300'], [$this->bnb, '500']] as [$g, $b]) {
            $g = $this->reload($g);
            $g->setBudgetQuote($b);
            $g->save();
        }
        $t = $this->reload($this->trend);
        $t->setBudgetQuote('100');                      // pool 1000 − 900 = 100 headroom
        $t->save();
        $this->trend = $t;
        $this->holdPosition($this->trend, '300', '1');

        $this->assertTrue($this->pass('TREND_UP')['reasserted']);
        $this->assertBudget('200', $this->reload($this->trend), 'raised by the headroom, no further');
        $this->assertBudget('300', $this->reload($this->btc), 'still no grid trimmed');
        $this->assertBudget('500', $this->reload($this->bnb));
    }

    // ── D2: a trend arm has no ladder levels ────────────────────────────

    public function testATrendArmsFloorIsNotPerLadderLevel(): void
    {
        // prod run 10, 2026-09-21: the fleet auto-creates a missing arm with
        // the trend-draft geometry — n_levels 20, p_low/p_high 375–1503 — and
        // FLOOR_PER_LEVEL × 20 = 1000 became the floor of an arm whose slot
        // targets 350. Levels are a GRID concept; the arm has no ladder.
        $t = $this->reload($this->trend);
        $t->setNLevels(20);
        $t->save();
        $this->assertSame(TrendActivator::MIN_SLICE, TrendActivator::nominalFloor($t));
        $this->assertSame('300', TrendActivator::nominalFloor($this->bnb), 'a GRID still floors per level (6 × 50)');
    }

    public function testAutoCreatedArmHoldingItsPositionDoesNotAskForATousandEveryPass(): void
    {
        // the prod loop: "could not re-assert the trend slice: run 10 → 1000
        // refused: Budget overcommit refused..." every 15 minutes while the
        // arm quietly held ~347 USDT.
        $this->activeAtSlice('350');
        $t = $this->reload($this->trend);
        $t->setNLevels(20);                              // the trend-draft geometry
        $t->save();
        $this->trend = $t;
        $this->slotTarget('350');
        $this->holdPosition($this->trend, '347', '1');

        $d = TrendActivator::derivedSlice($this->reload($this->trend), $this->slotFor($this->trend));
        $this->assertSame('holding', $d['rule']);
        $this->assertSame('347', $d['slice'], 'the slice follows the committed quote');
        $this->assertSame(TrendActivator::MIN_SLICE, $d['floor']);

        $r = $this->pass('TREND_UP');
        $this->assertTrue($r['reasserted']);
        $this->assertBudget('347', $this->reload($this->trend));
        $this->assertSame(
            0,
            BotEventQuery::create()->filterByIdGridRun($this->trend->getIdGridRun())->filterByKind(TrendActivator::KIND_ERROR)->count(),
            'nothing was refused, so nothing was journaled as an error'
        );
    }

    public function testAHoldingArmsFloorNeverExceedsTheSlotsTarget(): void
    {
        // whatever the floor is derived from, the slot's target is the most
        // the arm may ever be sized to by the holding rule.
        $this->activeAtSlice('350');
        $this->slotTarget('30');                         // a target under MIN_SLICE
        $this->holdPosition($this->trend, '10', '1');

        $d = TrendActivator::derivedSlice($this->reload($this->trend), $this->slotFor($this->trend));
        $this->assertSame('holding', $d['rule']);
        $this->assertSame('30', $d['floor']);
        $this->assertSame('30', $d['slice']);
    }

    public function testARefusedRaiseReloadsTheGridsItAlreadyTrimmed(): void
    {
        // Allocator::grids() is hydrated (pooled) while BudgetGuard reads raw
        // rows, so a slice another process moved between this pass's read and
        // its write makes the plan too big: the grid trim lands, the raise is
        // refused. The trimmed grid's daemon must be told, or it keeps
        // spending a slice that no longer exists.
        $this->activeAtSlice('100');
        $g = $this->reload($this->btc);
        $g->setBudgetQuote('350');
        $g->save();
        $this->btc = $g;
        // another process raises BNB behind the pool: 300 to the planner, 550
        // to BudgetGuard. The plan leaves BNB alone (it reads it at its floor)
        // so nothing overwrites the raw value.
        \Propel::getConnection()
            ->prepare('UPDATE grid_run SET budget_quote = 550 WHERE id_grid_run = ?')
            ->execute([(int) $this->bnb->getIdGridRun()]);
        $before = $this->reloadsFor($this->btc);

        $r = $this->pass('TREND_UP');
        $this->assertFalse($r['reasserted']);
        \App\GridRunPeer::clearInstancePool();
        $this->assertBudget('100', $this->reload($this->trend), 'the raise was refused');
        $this->assertBudget('250', $this->reload($this->btc), 'the trim is committed');
        $this->assertSame($before + 1, $this->reloadsFor($this->btc), 'the trimmed grid was told to re-read');
        $this->assertSame(0, $this->reassertEvents());
        $w = BotEventQuery::create()->filterByIdGridRun($this->trend->getIdGridRun())->filterByKind(TrendActivator::KIND_ERROR)->findOne();
        $this->assertNotNull($w);
        $this->assertSame('Warn', (string) $w->getLevel());
    }

    public function testARefusedActivationReloadsTheGridsItAlreadyTrimmed(): void
    {
        // Same hazard as the re-assert case, on the path that is FAR more
        // likely to hit it: applyFunding() commits the grid trims and only
        // then raises the arm, and before this fix the RuntimeException from
        // a refused raise escaped activate() and pass() entirely — the
        // trimmed grid never got its Reload and kept spending a slice the
        // database no longer gave it, and every slot after this one in the
        // cron's loop was skipped.
        $b = $this->reload($this->bnb);
        $b->setBudgetQuote('300');              // hydrated: already at its floor
        $b->save();
        $this->bnb = $b;
        $this->pass('TREND_UP');                // 1st confirm — no writes yet
        // another process raises BNB behind the pool: the planner still reads
        // 300 from the instance pool and leaves it alone, BudgetGuard reads
        // 550 from disk and refuses the arm's raise.
        \Propel::getConnection()
            ->prepare('UPDATE grid_run SET budget_quote = 550 WHERE id_grid_run = ?')
            ->execute([(int) $this->bnb->getIdGridRun()]);
        $before = $this->reloadsFor($this->btc);

        $r = $this->pass('TREND_UP');
        $this->assertSame('activate', $r['decision']);
        \App\GridRunPeer::clearInstancePool();
        $this->assertBudget('100', $this->reload($this->trend), 'the raise was refused');
        $this->assertBudget('250', $this->reload($this->btc), 'the trim is committed');
        $this->assertSame($before + 1, $this->reloadsFor($this->btc), 'the trimmed grid was told to re-read');
        $this->assertSame('idle', (string) $this->slotFor($this->trend)->getState(), 'the slot retries next pass');
        $this->assertSame(0, BotEventQuery::create()->filterByKind(TrendActivator::KIND_ACTIVATE)->count(), 'no activation was announced');
        $w = BotEventQuery::create()
            ->filterByIdGridRun($this->trend->getIdGridRun())
            ->filterByKind(TrendActivator::KIND_ERROR)
            ->findOne();
        $this->assertNotNull($w);
        $this->assertSame('Warn', (string) $w->getLevel());
        $this->assertStringContainsString('could not activate the trend arm', (string) $w->getMessage());
    }

    public function testASlotWhosePassThrowsAlertsOncePerHour(): void
    {
        // The cron line is `>/dev/null 2>&1`: a slot that stops being scaled
        // has to reach the event feed, or it is exactly the invisible idle
        // capital the fleet slots exist to end.
        $slot = $this->slotFor($this->trend);
        $boom = static function (string $symbol): array {
            throw new \RuntimeException('market summaries blew up');
        };
        $now = time();

        $first = $this->failPass($slot, $boom, $now);
        $this->assertNotNull($first);
        $this->assertStringContainsString('was NOT scaled this pass', $first);
        $this->assertSame(1, $this->passErrorAlerts());

        $this->assertNull($this->failPass($slot, $boom, $now + 60), 'throttled inside the hour');
        $this->assertSame(1, $this->passErrorAlerts());

        $this->assertNotNull($this->failPass($slot, $boom, $now + TrendActivator::PASS_ERROR_EVERY + 60));
        $this->assertSame(2, $this->passErrorAlerts(), 'a fault that persists is worth saying again, hourly');
    }

    public function testOneBrokenSlotDoesNotMuteAnother(): void
    {
        // Several slots can hang their events off the same host run, so the
        // throttle has to key on the SLOT.
        $a = $this->slotFor($this->trend);
        FleetSlots::reconcileFromRuns();
        $b = FleetSlots::find('BNBUSDT');
        $this->assertNotNull($b, 'the BNB grid declares an empty BNB trend slot');
        $boom = static function (string $symbol): array {
            throw new \RuntimeException('market summaries blew up');
        };
        $now = time();

        $this->assertNotNull($this->failPass($a, $boom, $now));
        $this->assertNotNull($this->failPass($b, $boom, $now + 1), 'the other slot still gets to speak');
        $this->assertSame(2, $this->passErrorAlerts());
    }

    public function testAnAlertWriteThatItselfThrowsDoesNotStopTheNextSlot(): void
    {
        // The catch body must not be able to throw. EventLog::write() saves a
        // row AND calls the notifier and swallows neither, so the very outage
        // that broke the pass can break the report — and before this guard
        // that took out every slot after it, which is the failure this whole
        // alert exists to prevent, one layer up.
        $a = $this->slotFor($this->trend);
        FleetSlots::reconcileFromRuns();
        $b = FleetSlots::find('BNBUSDT');
        $this->assertNotNull($b);
        $boom = static function (string $symbol): array {
            throw new \RuntimeException('market summaries blew up');
        };
        $now = time();

        $raising = new class ('t', 'c') extends TelegramNotifier {
            public function send(string $text): bool
            {
                throw new \RuntimeException('notifier is down too');
            }
        };

        try {
            TrendActivator::pass($a, $raising, false, $boom);
            $this->fail('the pass was expected to throw');
        } catch (\Throwable $e) {
            $this->assertNull(
                TrendActivator::reportPassFailure($a, $e, $raising, $now),
                'the report degrades to STDERR instead of escaping the loop'
            );
        }

        // ...and the loop reaches the next slot, with a working notifier
        $this->assertNotNull($this->failPass($b, $boom, $now + 1), 'the next slot still ran');
    }

    /** Drive a pass into a throw and hand the catch body its exception, as the script does. */
    private function failPass(FleetSlot $slot, callable $summaries, int $now): ?string
    {
        try {
            TrendActivator::pass($slot, $this->notifier, false, $summaries);
        } catch (\Throwable $e) {
            return TrendActivator::reportPassFailure($slot, $e, $this->notifier, $now);
        }
        $this->fail('the pass was expected to throw');
    }

    private function passErrorAlerts(): int
    {
        return BotEventQuery::create()
            ->filterByKind(TrendActivator::KIND_ERROR)
            ->filterByLevel('Alert')
            ->count();
    }

    public function testNominalFloorIsTheFloorTheRoutineBriefReports(): void
    {
        // a GRID's viability floor is per ladder level; a trend arm has no
        // ladder, so its floor is the minimum slice (D2, 2026-09-21)
        $this->assertSame('250', TrendActivator::nominalFloor($this->btc));     // 5 levels
        $this->assertSame('300', TrendActivator::nominalFloor($this->bnb));     // 6 levels
        $this->assertSame(TrendActivator::MIN_SLICE, TrendActivator::nominalFloor($this->trend));
    }

    public function testDoesNotScaleARunThatIsNotLiveButStillJournalsTheVerdict(): void
    {
        $t = $this->reload($this->trend);
        $t->setStatus('Halted');
        $t->save();
        $r = $this->pass('TREND_UP');
        $this->assertSame('skip', $r['decision']);
        $this->assertSame('TREND_UP', $r['verdict']);
        $this->assertBudget('100', $this->reload($this->trend));
        // before fleet_slot a parked arm produced no verdict at all
        $this->assertSame(1, BotEventQuery::create()->filterByIdGridRun($t->getIdGridRun())->filterByKind(TrendActivator::KIND_REGIME)->count());
        $this->assertSame(1, BotEventQuery::create()->filterByKind(TrendActivator::KIND_ARM_PARKED)->count());
    }

    public function testSkipsWhenKillSwitchIsOn(): void
    {
        $this->pass('TREND_UP');
        $t = $this->reload($this->trend);
        $t->setKillSwitch(true);
        $t->save();
        $r = $this->pass('TREND_UP');
        $this->assertSame('skip', $r['decision']);
        $this->assertBudget('100', $this->reload($this->trend));
    }
}
