<?php

namespace Tests\Custom\Bot;

use App\BotEventQuery;
use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\FleetSlots;
use App\Domains\Bot\RunFactory;
use App\Domains\Bot\TelegramNotifier;
use App\Domains\Bot\TrendActivator;
use App\FleetSlotQuery;
use App\GridRun;
use App\GridRunQuery;
use Tests\Builder\Support\DbTestCase;

/**
 * The DECLARED fleet: one slot per (symbol, algo) the system owns, whether or
 * not a run is behind it.
 *
 * Before fleet_slot the arm WAS a run, so a symbol whose arm was Halted, Done
 * or never created produced no verdict at all and nothing noticed (BNB,
 * 2026-09-15..18). These cases pin the correction: reconcile adopts the runs
 * that exist, an empty slot fills itself with a Draft and says so, a parked
 * run in a confirmed uptrend alerts once per episode, and the verdict is
 * journaled on every pass either way.
 */
class FleetSlotTest extends DbTestCase
{
    /** @var string[] */
    private array $sent = [];
    private TelegramNotifier $notifier;

    protected function setUp(): void
    {
        parent::setUp();
        // park whatever the dev DB holds so only this test's runs are active
        foreach (GridRunQuery::create()->filterByStatus(['DryRun', 'Testnet', 'Live'], \Criteria::IN)->find() as $r) {
            $r->setStatus('Halted');
            $r->save();
        }
        foreach (FleetSlotQuery::create()->find() as $s) {
            $s->delete();
        }
        $this->cfg('gtbot_shared_budget_quote', '2000');
        $this->cfg('gtbot_max_drawdown_pct', '0');
        $this->cfg(TrendActivator::CONFIG_TARGET_SLICE, TrendActivator::TARGET_SLICE);
        $this->sent = [];
        $this->notifier = new TelegramNotifier('t', 'c', function (string $url, array $post): array {
            $this->sent[] = $post['text'] ?? '';
            return ['status' => 200, 'body' => '{"ok":true}'];
        });
        putenv('GTBOT_FLEET_AUTOLIVE');
    }

    protected function tearDown(): void
    {
        putenv('GTBOT_FLEET_AUTOLIVE');
        parent::tearDown();
    }

    private function cfg(string $key, string $value): void
    {
        $c = ConfigQuery::create()->findOneByConfig($key) ?? (new Config())->setConfig($key);
        $c->setValue($value);
        $c->save();
    }

    private function mkRun(string $symbol, string $budget, string $algo, string $status = 'Live'): GridRun
    {
        $r = new GridRun();
        $r->setLabel(static::uniq('fs-' . $algo));
        $r->setSymbol($symbol);
        $r->setStatus($status);
        $r->setAlgo($algo);
        $r->setSimulated(true);
        $r->setPLow('50');
        $r->setPHigh('200');
        $r->setNLevels(5);
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualBase');
        $r->setBudgetQuote($budget);
        $r->setDeployPct(100);
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid(static::uniq('fs'));
        $r->save();
        return $r;
    }

    private function summaries(string $shape): callable
    {
        $map = [
            'TREND_UP' => ['4h' => ['trend' => 'strong_up', 'adx14' => 33.0, 'er20' => 0.6, 'chop14' => 30.0, 'stale' => false],
                           '1d' => ['trend' => 'up', 'price' => 100.0, 'ema20' => 90.0, 'ema50' => 95.0, 'stale' => false]],
            'MIXED' => ['4h' => ['trend' => 'up', 'adx14' => 22.0, 'er20' => 0.1, 'chop14' => 30.0, 'stale' => false],
                        '1d' => ['trend' => 'down', 'price' => 100.0, 'ema20' => 110.0, 'ema50' => 120.0, 'stale' => false]],
        ];
        return static fn (string $symbol): array => $map[$shape];
    }

    private function passSlot(\App\FleetSlot $slot, string $shape, bool $dry = false): array
    {
        return TrendActivator::pass($slot, $this->notifier, $dry, $this->summaries($shape));
    }

    private function pass(string $symbol, string $shape, bool $dry = false): array
    {
        $slot = FleetSlots::find($symbol);
        $this->assertNotNull($slot, "no slot for $symbol");
        return TrendActivator::pass($slot, $this->notifier, $dry, $this->summaries($shape));
    }

    private function alerts(string $kind): int
    {
        return BotEventQuery::create()->filterByKind($kind)->count();
    }

    // ── reconcile ───────────────────────────────────────────────────────

    public function testReconcileAdoptsALiveTrendRunCarryingItsEventChainState(): void
    {
        $run = $this->mkRun('BTCUSDT', '400', 'Trend');
        $id = (int) $run->getIdGridRun();
        // the pre-slot world: state and payload lived in the marker events
        $e = new \App\BotEvent();
        $e->setIdGridRun($id);
        $e->setLevel('Info');
        $e->setKind(TrendActivator::KIND_ACTIVATE);
        $e->setMessage('legacy activation');
        $e->setPayload(json_encode(['trend_before' => '100', 'trend_after' => '400', 'deploy_before' => 25, 'grids_before' => []]));
        $e->save();

        $created = FleetSlots::reconcileFromRuns();
        $this->assertCount(1, $created);

        $slot = FleetSlots::find('BTCUSDT');
        $this->assertNotNull($slot);
        $this->assertSame($id, (int) $slot->getIdGridRun());
        $this->assertSame('active', (string) $slot->getState());
        $this->assertSame('Trend', (string) $slot->getAlgo());
        $this->assertTrue((bool) $slot->getEnabled());
        // CHANGED 2026-09-21 (round 2): a reconciled slot stores 0 = "follow
        // gtbot_trend_target_slice". Copying the config value in here pinned
        // it, so every later edit of the knob was inert. The EFFECTIVE target
        // is what callers read, and that still is the config.
        $this->assertSame(0, bccomp('0', (string) $slot->getTargetSlice(), 8), 'stored: follow the config');
        $this->assertSame(TrendActivator::targetSlice(), TrendActivator::effectiveTarget($slot), 'effective: the config');
        $this->assertSame('400', json_decode((string) $slot->getActivation(), true)['trend_after']);

        // the adapters every other caller uses now read the slot
        $this->assertSame('active', TrendActivator::state($id));
        $this->assertSame('400', TrendActivator::activationPayload($id)['trend_after']);
    }

    public function testReconcileIsIdempotent(): void
    {
        $this->mkRun('BTCUSDT', '400', 'Trend');
        $this->assertCount(1, FleetSlots::reconcileFromRuns());
        $this->assertCount(0, FleetSlots::reconcileFromRuns());
        $this->assertSame(1, FleetSlotQuery::create()->count());
    }

    public function testReconcilePrefersTheLiveRunOverANewerHaltedOne(): void
    {
        // prod shape: run 1 Live, run 8 Halted ("core (retired)") — the slot
        // must take run 1, not the newer parked one
        $live = $this->mkRun('BTCUSDT', '400', 'Trend', 'Live');
        $this->mkRun('BTCUSDT', '10', 'Trend', 'Halted');
        FleetSlots::reconcileFromRuns();
        $this->assertSame((int) $live->getIdGridRun(), (int) FleetSlots::find('BTCUSDT')->getIdGridRun());
    }

    public function testDoneTrendRunsDoNotFillASlot(): void
    {
        // prod shape: BNB's only Trend run (9) is Done and its grid (4) is Live
        $this->mkRun('BNBUSDT', '300', 'Trend', 'Done');
        $this->mkRun('BNBUSDT', '300', 'Grid', 'Live');
        FleetSlots::reconcileFromRuns();
        $slot = FleetSlots::find('BNBUSDT');
        $this->assertNotNull($slot, 'a symbol with a Grid run gets a Trend slot');
        $this->assertNull($slot->getIdGridRun(), 'a Done run does not fill the slot');
    }

    public function testASymbolWithOnlyAGridRunGetsAnEmptyTrendSlot(): void
    {
        $this->mkRun('BNBUSDT', '300', 'Grid', 'Live');
        FleetSlots::reconcileFromRuns();
        $slot = FleetSlots::find('BNBUSDT');
        $this->assertNotNull($slot);
        $this->assertNull($slot->getIdGridRun());
        $this->assertSame('idle', (string) $slot->getState());
    }

    // ── the pass ────────────────────────────────────────────────────────

    public function testVerdictIsJournaledOnTheHostRunWhenTheSlotHasNoRun(): void
    {
        $grid = $this->mkRun('BNBUSDT', '300', 'Grid', 'Live');
        FleetSlots::reconcileFromRuns();
        $r = $this->pass('BNBUSDT', 'TREND_UP');
        $this->assertSame('TREND_UP', $r['verdict']);

        $e = BotEventQuery::create()
            ->filterByIdGridRun((int) $grid->getIdGridRun())
            ->filterByKind(TrendActivator::KIND_REGIME)
            ->findOne();
        $this->assertNotNull($e, 'the verdict is journaled on the fleet host run');
        $this->assertSame('BNBUSDT', json_decode((string) $e->getPayload(), true)['symbol']);
        $this->assertSame('TREND_UP', (string) FleetSlots::find('BNBUSDT')->getLastVerdict());
    }

    public function testEmptySlotCreatesADraftRunOnceAndAlertsOnce(): void
    {
        $this->mkRun('BNBUSDT', '300', 'Grid', 'Live');
        FleetSlots::reconcileFromRuns();
        $this->pass('BNBUSDT', 'TREND_UP');

        $slot = FleetSlots::find('BNBUSDT');
        $draftId = (int) $slot->getIdGridRun();
        $this->assertGreaterThan(0, $draftId, 'the slot filled itself');
        $draft = GridRunQuery::create()->findPk($draftId);
        $this->assertSame('Draft', (string) $draft->getStatus());
        $this->assertSame('Trend', (string) $draft->getAlgo());
        $this->assertSame('BNB trend', (string) $draft->getLabel());
        $this->assertSame('Balanced', (string) $draft->getProfile());
        $this->assertFalse((bool) $draft->getSellAtLoss());
        $this->assertFalse((bool) $draft->getSellWhenStarved());
        $this->assertSame(0, (int) $draft->getDeployPct());
        $this->assertSame('Auto', (string) $draft->getAllocMode());
        $this->assertSame(0, bccomp(TrendActivator::MIN_SLICE, (string) $draft->getBudgetQuote(), 8));
        $this->assertSame(1, $this->alerts(TrendActivator::KIND_SLOT_EMPTY));
        $this->assertNotNull($slot->getLastEmptyAlertAt());

        // the next pass sees a run: no second Draft, no second alert
        $this->pass('BNBUSDT', 'TREND_UP');
        $this->assertSame(1, GridRunQuery::create()->filterByAlgo('Trend')->filterBySymbol('BNBUSDT')->count());
        $this->assertSame(1, $this->alerts(TrendActivator::KIND_SLOT_EMPTY));
    }

    public function testEmptySlotAlertIsThrottledToOncePerDay(): void
    {
        $this->mkRun('BNBUSDT', '300', 'Grid', 'Live');
        FleetSlots::reconcileFromRuns();
        $this->pass('BNBUSDT', 'TREND_UP');
        $this->assertSame(1, $this->alerts(TrendActivator::KIND_SLOT_EMPTY));

        // empty it again within the window: a new Draft, but no new alert
        $this->emptyTheSlot('BNBUSDT');
        $this->pass('BNBUSDT', 'TREND_UP');
        $this->assertSame(1, $this->alerts(TrendActivator::KIND_SLOT_EMPTY), 'throttled inside 24 h');

        // …and again a day later: it speaks up
        $slot = FleetSlots::find('BNBUSDT');
        $slot->setLastEmptyAlertAt(date('Y-m-d H:i:s', time() - 25 * 3600));
        $slot->save();
        $this->emptyTheSlot('BNBUSDT');
        $this->pass('BNBUSDT', 'TREND_UP');
        $this->assertSame(2, $this->alerts(TrendActivator::KIND_SLOT_EMPTY));
    }

    /** Retire the run the slot holds so the next pass sees an empty slot again. */
    private function emptyTheSlot(string $symbol): void
    {
        $slot = FleetSlots::find($symbol);
        $run = GridRunQuery::create()->findPk((int) $slot->getIdGridRun());
        $run->setStatus('Done');
        $run->save();
        $slot->setIdGridRun(null);
        $slot->save();
    }

    public function testAutoliveCreatesTheDraftLive(): void
    {
        putenv('GTBOT_FLEET_AUTOLIVE=1');
        $this->mkRun('BNBUSDT', '300', 'Grid', 'Live');
        FleetSlots::reconcileFromRuns();
        $this->pass('BNBUSDT', 'TREND_UP');
        $run = GridRunQuery::create()->findPk((int) FleetSlots::find('BNBUSDT')->getIdGridRun());
        $this->assertSame('Live', (string) $run->getStatus());
    }

    public function testParkedArmInATrendAlertsOncePerEpisode(): void
    {
        $this->mkRun('BTCUSDT', '100', 'Trend', 'Halted');
        FleetSlots::reconcileFromRuns();

        $r = $this->pass('BTCUSDT', 'TREND_UP');
        $this->assertSame('skip', $r['decision']);
        $this->assertSame(1, $this->alerts(TrendActivator::KIND_ARM_PARKED));
        $e = BotEventQuery::create()->filterByKind(TrendActivator::KIND_ARM_PARKED)->findOne();
        $this->assertStringContainsString('Halted', (string) $e->getMessage());

        $this->pass('BTCUSDT', 'TREND_UP');
        $this->assertSame(1, $this->alerts(TrendActivator::KIND_ARM_PARKED), 'once per episode');

        // the episode ends…
        $this->pass('BTCUSDT', 'MIXED');
        $this->assertNull(FleetSlots::find('BTCUSDT')->getEpisodeStartedAt());
        $this->assertSame(1, $this->alerts(TrendActivator::KIND_ARM_PARKED));

        // …and a new one speaks up again
        $this->pass('BTCUSDT', 'TREND_UP');
        $this->assertSame(2, $this->alerts(TrendActivator::KIND_ARM_PARKED));
    }

    public function testKilledRunIsTreatedAsParked(): void
    {
        $run = $this->mkRun('BTCUSDT', '100', 'Trend', 'Live');
        $run->setKillSwitch(true);
        $run->save();
        FleetSlots::reconcileFromRuns();
        $r = $this->pass('BTCUSDT', 'TREND_UP');
        $this->assertSame('skip', $r['decision']);
        $this->assertSame('TREND_UP', $r['verdict'], 'the verdict is journaled even for a killed arm');
        $this->assertSame(1, $this->alerts(TrendActivator::KIND_ARM_PARKED));
    }

    public function testDisabledSlotOnlyJournalsTheVerdict(): void
    {
        $run = $this->mkRun('BTCUSDT', '100', 'Trend', 'Live');
        FleetSlots::reconcileFromRuns();
        $slot = FleetSlots::find('BTCUSDT');
        $slot->setEnabled(false);
        $slot->save();

        $this->pass('BTCUSDT', 'TREND_UP');
        $r = $this->pass('BTCUSDT', 'TREND_UP');
        $this->assertSame('skip', $r['decision']);
        $this->assertSame(2, BotEventQuery::create()->filterByKind(TrendActivator::KIND_REGIME)->count());
        $slot = FleetSlots::find('BTCUSDT');
        $this->assertSame('idle', (string) $slot->getState());
        $this->assertSame('TREND_UP', (string) $slot->getLastVerdict());
        $this->assertSame(0, (int) $slot->getConfirmUp(), 'a disabled slot confirms nothing');
        $this->assertSame(0, bccomp('100', (string) GridRunQuery::create()->findPk($run->getIdGridRun())->getBudgetQuote(), 8));
    }

    public function testEpisodeStartIsStampedWhenTheVerdictEntersTrendUp(): void
    {
        $this->mkRun('BTCUSDT', '100', 'Trend', 'Live');
        FleetSlots::reconcileFromRuns();
        $this->pass('BTCUSDT', 'MIXED');
        $this->assertNull(FleetSlots::find('BTCUSDT')->getEpisodeStartedAt());
        $this->pass('BTCUSDT', 'TREND_UP');
        $started = FleetSlots::find('BTCUSDT')->getEpisodeStartedAt('Y-m-d H:i:s');
        $this->assertNotNull($started);
        $this->pass('BTCUSDT', 'TREND_UP');
        $this->assertSame($started, FleetSlots::find('BTCUSDT')->getEpisodeStartedAt('Y-m-d H:i:s'), 'the episode is not restarted');
        $this->pass('BTCUSDT', 'MIXED');
        $this->assertNull(FleetSlots::find('BTCUSDT')->getEpisodeStartedAt());
    }

    public function testCountersDriveTheSameHysteresisTheEventChainDid(): void
    {
        $this->mkRun('BTCUSDT', '100', 'Trend', 'Live');
        FleetSlots::reconcileFromRuns();
        $r = $this->pass('BTCUSDT', 'TREND_UP');
        $this->assertSame('hold', $r['decision']);
        $this->assertSame(1, (int) FleetSlots::find('BTCUSDT')->getConfirmUp());
        $r = $this->pass('BTCUSDT', 'TREND_UP');
        $this->assertSame('activate', $r['decision'], 'CONFIRM_PASSES = 2');

        foreach ([1, 2, 3] as $i) {
            $r = $this->pass('BTCUSDT', 'MIXED');
            $this->assertSame('hold', $r['decision'], "MIXED pass $i holds");
        }
        $this->assertSame(3, (int) FleetSlots::find('BTCUSDT')->getConfirmDown());
        $r = $this->pass('BTCUSDT', 'MIXED');
        $this->assertSame('deactivate', $r['decision'], 'DEACTIVATE_PASSES = 4');
        $this->assertSame('winding_down', (string) FleetSlots::find('BTCUSDT')->getState());
    }

    public function testDryPassLeavesTheSlotAlone(): void
    {
        $this->mkRun('BTCUSDT', '100', 'Trend', 'Live');
        FleetSlots::reconcileFromRuns();
        $this->pass('BTCUSDT', 'TREND_UP');
        $r = $this->pass('BTCUSDT', 'TREND_UP', true);
        $this->assertSame('activate', $r['decision']);
        $slot = FleetSlots::find('BTCUSDT');
        $this->assertSame('idle', (string) $slot->getState());
        $this->assertSame(1, (int) $slot->getConfirmUp(), 'a dry pass counts nothing');
    }

    public function testBriefListsEverySlot(): void
    {
        $trend = $this->mkRun('BTCUSDT', '100', 'Trend', 'Live');
        $this->mkRun('BNBUSDT', '300', 'Grid', 'Live');
        FleetSlots::reconcileFromRuns();
        $this->pass('BTCUSDT', 'TREND_UP');

        $fleet = FleetSlots::brief();
        $this->assertCount(2, $fleet);
        $by = array_column($fleet, null, 'symbol');
        $this->assertSame((int) $trend->getIdGridRun(), $by['BTCUSDT']['run']);
        $this->assertFalse($by['BTCUSDT']['empty']);
        $this->assertSame('TREND_UP', $by['BTCUSDT']['verdict']);
        $this->assertSame('idle', $by['BTCUSDT']['state']);
        $this->assertNotNull($by['BTCUSDT']['verdict_at']);
        $this->assertSame(0, bccomp(TrendActivator::targetSlice(), $by['BTCUSDT']['target_slice'], 8));
        $this->assertTrue($by['BNBUSDT']['empty']);
        $this->assertNull($by['BNBUSDT']['run']);
    }

    // ── run resolution: Live-first EVERY pass ───────────────────────────

    public function testSlotLeavesAParkedRunForALiveOne(): void
    {
        // retiring an arm that still holds inventory leaves it Retiring, and
        // halting one leaves it Halted — neither is Done, and the slot used
        // to sit on it while the replacement arm was orphaned
        $old = $this->mkRun('BTCUSDT', '100', 'Trend', 'Live');
        FleetSlots::reconcileFromRuns();
        $this->pass('BTCUSDT', 'TREND_UP');
        $slot = FleetSlots::find('BTCUSDT');
        $this->assertSame((int) $old->getIdGridRun(), (int) $slot->getIdGridRun());
        $this->assertSame(1, (int) $slot->getConfirmUp());

        $old->setStatus('Retiring');
        $old->save();
        $fresh = $this->mkRun('BTCUSDT', '100', 'Trend', 'Live');

        $this->pass('BTCUSDT', 'TREND_UP');
        $slot = FleetSlots::find('BTCUSDT');
        $this->assertSame((int) $fresh->getIdGridRun(), (int) $slot->getIdGridRun(), 'the Live arm takes the slot');
        $this->assertSame('idle', (string) $slot->getState(), 'a fresh arm starts clean');
        $this->assertNull($slot->getActivation());
        $this->assertSame(1, (int) $slot->getConfirmUp(), 'counters restarted with this pass');
    }

    public function testSlotKeepsItsLiveRunWhenANewerLiveOneAppears(): void
    {
        $held = $this->mkRun('BTCUSDT', '100', 'Trend', 'Live');
        FleetSlots::reconcileFromRuns();
        $this->pass('BTCUSDT', 'TREND_UP');
        $this->mkRun('BTCUSDT', '100', 'Trend', 'Live');
        $this->pass('BTCUSDT', 'TREND_UP');
        $slot = FleetSlots::find('BTCUSDT');
        $this->assertSame((int) $held->getIdGridRun(), (int) $slot->getIdGridRun(), 'the working arm is not handed over');
        $this->assertSame(2, (int) $slot->getConfirmUp(), 'and its streak is not reset');
    }

    // ── parked alerts that are not accidents ────────────────────────────

    public function testTheDraftTheSlotJustCreatedDoesNotAlertAsParked(): void
    {
        $this->mkRun('BNBUSDT', '300', 'Grid', 'Live');
        FleetSlots::reconcileFromRuns();
        $this->pass('BNBUSDT', 'TREND_UP');           // creates the Draft + fleet_slot_empty
        $r = $this->pass('BNBUSDT', 'TREND_UP');      // the Draft is now the slot's run
        $this->assertSame('skip', $r['decision']);
        $this->assertSame(0, $this->alerts(TrendActivator::KIND_ARM_PARKED), 'fleet_slot_empty already said it');
        $this->assertStringContainsString('not an accidental park', implode(' ', $r['notes']));
    }

    public function testARetiringArmDoesNotAlertAsParked(): void
    {
        $run = $this->mkRun('BTCUSDT', '100', 'Trend', 'Retiring');
        FleetSlots::reconcileFromRuns();
        $this->assertSame((int) $run->getIdGridRun(), (int) FleetSlots::find('BTCUSDT')->getIdGridRun());
        $r = $this->pass('BTCUSDT', 'TREND_UP');
        $this->assertSame('TREND_UP', $r['verdict'], 'still journaled');
        $this->assertSame(0, $this->alerts(TrendActivator::KIND_ARM_PARKED), 'a deliberate wind-down is not an accident');
    }

    // ── a Grid slot is declarative ──────────────────────────────────────

    public function testGridSlotsAreDeclarativeAndScaleNothing(): void
    {
        $run = $this->mkRun('BTCUSDT', '100', 'Trend', 'Live');
        FleetSlots::reconcileFromRuns();
        $grid = new \App\FleetSlot();
        $grid->setSymbol('BTCUSDT');
        $grid->setAlgo('Grid');
        $grid->setTargetSlice('350');
        $grid->save();

        $r = $this->passSlot($grid, 'TREND_UP');
        $this->assertSame('skip', $r['decision']);
        $this->assertNull($r['run'], 'a Grid slot adopts nothing');
        $this->assertStringContainsString('declarative', implode(' ', $r['notes']));
        $this->assertSame(0, BotEventQuery::create()->filterByKind(TrendActivator::KIND_REGIME)->count());
        $grid = FleetSlotQuery::create()->findPk($grid->getIdFleetSlot());
        $this->assertNull($grid->getIdGridRun());
        $this->assertSame(0, (int) $grid->getConfirmUp());
        $this->assertSame(0, bccomp('100', (string) GridRunQuery::create()->findPk($run->getIdGridRun())->getBudgetQuote(), 8));
    }

    // ── the drawdown stand-down is a stand-down ─────────────────────────

    public function testATrippedDrawdownFloorCreatesNothingAndAlertsNothing(): void
    {
        putenv('GTBOT_FLEET_AUTOLIVE=1');
        $this->mkRun('BNBUSDT', '300', 'Grid', 'Live');
        FleetSlots::reconcileFromRuns();
        // the dev DB has no sim wallet, so equity is 0: any non-zero pct trips
        $this->cfg('gtbot_max_drawdown_pct', '25');

        $r = $this->pass('BNBUSDT', 'TREND_UP');
        $this->assertSame('skip', $r['decision']);
        $this->assertStringContainsString('drawdown floor tripped', implode(' ', $r['notes']));
        $this->assertNull(FleetSlots::find('BNBUSDT')->getIdGridRun(), 'no arm spawned while the floor is tripped');
        $this->assertSame(0, GridRunQuery::create()->filterByAlgo('Trend')->count());
        $this->assertSame(0, $this->alerts(TrendActivator::KIND_SLOT_EMPTY));
        // …but the routine still gets its regime read
        $this->assertSame(1, BotEventQuery::create()->filterByKind(TrendActivator::KIND_REGIME)->count());
        $this->assertSame('TREND_UP', (string) FleetSlots::find('BNBUSDT')->getLastVerdict());
    }

    public function testATrippedFloorDoesNotAlertEverySymbolAsParked(): void
    {
        $run = $this->mkRun('BTCUSDT', '100', 'Trend', 'Live');
        $run->setKillSwitch(true);   // what DrawdownGuard::tripAll() does
        $run->save();
        FleetSlots::reconcileFromRuns();
        $this->cfg('gtbot_max_drawdown_pct', '25');

        $this->pass('BTCUSDT', 'TREND_UP');
        $this->assertSame(0, $this->alerts(TrendActivator::KIND_ARM_PARKED), 'the guard is why it is killed');
    }

    // ── price source ────────────────────────────────────────────────────

    public function testTheFleetPriceHelperRefusesAStaleSummary(): void
    {
        $symbol = static::uniq('PX') . 'USDT';
        $row = new \App\MarketSummary();
        $row->setSymbol($symbol);
        $row->setTf('1h');
        $row->setPrice('999');
        $row->setComputedAt(date('Y-m-d H:i:s', time() - TrendActivator::STALE_AFTER - 60));
        $row->save();

        $ticker = static fn (string $s): ?string => '123';
        $this->assertSame('123', RunFactory::price($symbol, null, $ticker), 'stale summary ignored');

        // …and used while it is fresh
        $row->setComputedAt(date('Y-m-d H:i:s'));
        $row->save();
        \App\MarketSummaryPeer::clearInstancePool();
        $this->assertSame(0, bccomp('999', (string) RunFactory::price($symbol, null, $ticker), 8));
    }
}
