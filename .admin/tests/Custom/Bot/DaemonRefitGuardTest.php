<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\Daemon;
use App\Domains\Bot\EventLog;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\BotEventQuery;
use App\BotOrderQuery;
use App\GridRun;
use Tests\Builder\Support\DbTestCase;
use Tests\Custom\Bot\Support\ExchangeSim;

/**
 * A fee-negative / incoherent geometry written onto the run row must be
 * rejected by the daemon (never applied), leaving the current grid intact.
 */
class DaemonRefitGuardTest extends DbTestCase
{
    private GridRun $run;
    private ExchangeSim $sim;

    protected function setUp(): void
    {
        parent::setUp();
        $r = new GridRun();
        $r->setLabel(static::uniq('guard'));
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
        $r->setRunUid(static::uniq('guard'));
        $r->save();
        $this->run = $r;
        $this->sim = new ExchangeSim();
    }

    public function testFeeNegativeGeometryIsRejected(): void
    {
        $this->sim->setPrice('50'); // flat below range
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $d = new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
        $d->boot();
        $d->tick();

        // write a fee-negative geometry: 200 levels over a tiny range → spacing << floor
        $this->run->reload();
        $this->run->setPLow('100');
        $this->run->setPHigh('101');
        $this->run->setNLevels(200);
        $this->run->save();

        $this->sim->setPrice('100.5');
        $d->tick();

        $rejected = BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('refit_rejected')
            ->count();
        $this->assertSame(1, $rejected, 'fee-negative geometry must be rejected, not applied');
    }

    public function testSilentGridAlertsOncePerEpisodeAndResetsWhenABuyIsOnTheBook(): void
    {
        // max_open_orders 0: RiskManager vetoes every buy, so the ladder
        // never arms although deploy is 100% and nothing gates entries
        $this->run->setMaxOpenOrders(0);
        $this->run->save();
        putenv('GTBOT_GRID_SILENT_AFTER=0');
        try {
            $this->sim->setPrice('150');
            $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
            $d = new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
            $d->boot();
            $d->tick();
            $d->tick();
            $silent = BotEventQuery::create()
                ->filterByIdGridRun((int) $this->run->getIdGridRun())
                ->filterByKind('grid_silent');
            $this->assertSame(1, $silent->count(), 'one Alert per silent episode, not one per tick');
            $this->assertStringContainsString('nothing gates entries', (string) $silent->findOne()->getMessage());
        } finally {
            putenv('GTBOT_GRID_SILENT_AFTER');
        }
    }

    /** @return \App\BotDecision */
    private function mkDecision(string $source, string $pLow, string $pHigh, int $n)
    {
        $d = new \App\BotDecision();
        $d->setIdGridRun((int) $this->run->getIdGridRun());
        $d->setSource($source);
        $d->setPLow($pLow);
        $d->setPHigh($pHigh);
        $d->setNLevels($n);
        $d->setReason('test');
        $d->save();
        return $d;
    }

    public function testApplyStampsEveryQueuedDecisionForThatGeometry(): void
    {
        // Mechanical mode makes the routine echo the run's geometry on a
        // deploy-only call, so the cron's write and the routine's echo are
        // indistinguishable by geometry. Stamping only the newest left the
        // other un-applied forever — which froze the cron's 72h clock (it
        // anchors on the newest APPLIED Cron row) and wedged its pending gate.
        $cron = $this->mkDecision('Cron', '120', '180', 4);
        $claude = $this->mkDecision('Claude', '120', '180', 4);
        $other = $this->mkDecision('Cron', '130', '190', 4);

        $this->sim->setPrice('150');
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $d = new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
        $d->boot();
        $d->tick();

        $this->run->reload();
        $this->run->setPLow('120');
        $this->run->setPHigh('180');
        $this->run->setNLevels(4);
        $this->run->save();
        $d->tick();

        $this->assertNotNull(\App\BotDecisionQuery::create()->findPk($cron->getIdBotDecision())->getAppliedAt(),
            "the cron's write is stamped");
        $this->assertNotNull(\App\BotDecisionQuery::create()->findPk($claude->getIdBotDecision())->getAppliedAt(),
            "the routine's echo of the same geometry is stamped too, not orphaned");
        $this->assertNull(\App\BotDecisionQuery::create()->findPk($other->getIdBotDecision())->getAppliedAt(),
            'a decision asking for a DIFFERENT geometry stays queued');
    }

    public function testRejectedGeometryClosesEveryQueuedDecisionForIt(): void
    {
        $cron = $this->mkDecision('Cron', '100', '101', 200);
        $claude = $this->mkDecision('Claude', '100', '101', 200);

        $this->sim->setPrice('50');
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $d = new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
        $d->boot();
        $d->tick();

        $this->run->reload();
        $this->run->setPLow('100');
        $this->run->setPHigh('101');
        $this->run->setNLevels(200); // fee-negative: the daemon refuses it
        $this->run->save();
        $this->sim->setPrice('100.5');
        $d->tick();

        foreach ([$cron, $claude] as $row) {
            $fresh = \App\BotDecisionQuery::create()->findPk($row->getIdBotDecision());
            $this->assertSame('Scored', (string) $fresh->getEvalStatus(), 'a refused geometry must not stay queued');
            $this->assertSame('Superseded', (string) $fresh->getVerdict());
        }
    }

    public function testSilentGridAlertDoesNotReFireAfterAReload(): void
    {
        // The alert is once per EPISODE. A reload mid-episode (budget edit,
        // code-mtime, watchdog respawn) builds a fresh Daemon: the epoch AND
        // the "already alerted" flag must both come back from engine_state.
        $this->run->setMaxOpenOrders(0);
        $this->run->save();
        putenv('GTBOT_GRID_SILENT_AFTER=0');
        try {
            $this->sim->setPrice('150');
            $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
            $d = new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
            $d->boot();
            $d->tick();
            unset($d);
            $reloaded = \App\GridRunQuery::create()->findPk((int) $this->run->getIdGridRun());
            $d2 = new Daemon($reloaded, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
            $d2->boot();
            $d2->tick();
            $this->assertSame(1, BotEventQuery::create()
                ->filterByIdGridRun((int) $this->run->getIdGridRun())
                ->filterByKind('grid_silent')
                ->count(), 'a reload mid-episode must not re-fire the alert');
        } finally {
            putenv('GTBOT_GRID_SILENT_AFTER');
        }
    }

    public function testArmedGridNeverAlertsSilent(): void
    {
        putenv('GTBOT_GRID_SILENT_AFTER=0');
        try {
            $this->sim->setPrice('150');
            $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
            $d = new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
            $d->boot();
            $d->tick();
            $d->tick();
            $this->assertSame(0, BotEventQuery::create()->filterByIdGridRun((int) $this->run->getIdGridRun())->filterByKind('grid_silent')->count());
            $this->assertGreaterThan(0, BotOrderQuery::create()->filterByIdGridRun((int) $this->run->getIdGridRun())->filterByState('BUY_OPEN')->count(), 'fixture sanity: buys are on the book');
        } finally {
            putenv('GTBOT_GRID_SILENT_AFTER');
        }
    }

    public function testReloadCommandEndsTheTickLoopCleanly(): void
    {
        $this->sim->setPrice('150');
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $d = new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
        $d->boot();
        $this->assertTrue($d->tick());

        $cmd = new \App\BotCommand();
        $cmd->setIdGridRun((int) $this->run->getIdGridRun());
        $cmd->setCommand('Reload');
        $cmd->setCmdStatus('Pending');
        $cmd->save();

        $this->assertFalse($d->tick(), 'Reload must end the tick loop so the supervisor relaunches on fresh code');
        $cmd->reload();
        $this->assertSame('Done', (string) $cmd->getCmdStatus(), 'the command is acked before the exit');
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('reloading')->count());
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('bot_restart')->count(), 'restart lands in the event feed / chart markers');
    }

    public function testPauseAndResumeWriteLifecycleEvents(): void
    {
        $this->sim->setPrice('150');
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $d = new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
        $d->boot();
        $d->tick();

        foreach (['Pause', 'Resume'] as $name) {
            $cmd = new \App\BotCommand();
            $cmd->setIdGridRun((int) $this->run->getIdGridRun());
            $cmd->setCommand($name);
            $cmd->setCmdStatus('Pending');
            $cmd->save();
            $d->tick();
        }

        $kind = fn (string $k): int => BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind($k)->count();
        $this->assertSame(1, $kind('bot_stop'), 'Pause writes a bot_stop lifecycle event');
        $this->assertSame(1, $kind('bot_start'), 'Resume writes a bot_start lifecycle event');
    }

    public function testDeployPctZeroIsFlatNoBuysPlaced(): void
    {
        $this->run->setDeployPct(0);
        $this->run->save();
        $this->sim->setPrice('150');
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $d = new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
        $d->boot();
        $this->assertTrue($d->tick(), 'flat is a position — the daemon stays alive and heartbeats');
        $buys = array_filter($this->sim->open, fn ($o) => $o['side'] === 'BUY');
        $this->assertCount(0, $buys, 'deploy_pct 0 must place no buy orders');
    }

    public function testDeployPctZeroLogsOneFlatInfoNotPerLevelWarns(): void
    {
        $this->run->setDeployPct(0);
        $this->run->save();
        $this->sim->setPrice('150');
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $d = new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
        $d->boot();
        $d->tick();
        $d->tick(); // skipped levels retry every tick — still no spam

        $kind = fn (string $k): int => BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind($k)->count();
        $this->assertSame(0, $kind('filter_skip'), 'flat is a position — per-level minQty warns are noise');
        $this->assertSame(1, $kind('flat'), 'one Info line says why entries are idle');
    }

    public function testDeployPctScalesTheLadderAndTriggersRefitAlone(): void
    {
        // full deployment: EqualBase 550 over [100,200]×4 → qty 1 per level
        $this->sim->setPrice('150');
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $d = new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
        $d->boot();
        $d->tick();
        $qtys = array_map(fn ($o) => (float) $o['origQty'], array_filter($this->sim->open, fn ($o) => $o['side'] === 'BUY'));
        $this->assertEqualsWithDelta(1.0, max($qtys), 0.001);

        // halving deploy_pct — with NO other geometry change — must re-anchor
        $this->run->reload();
        $this->run->setDeployPct(50);
        $this->run->save();
        $d->tick();

        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('refit_applied')->count(), 'deploy_pct is ladder geometry — changing it re-anchors');
        $qtys = array_map(fn ($o) => (float) $o['origQty'], array_filter($this->sim->open, fn ($o) => $o['side'] === 'BUY'));
        $this->assertEqualsWithDelta(0.5, max($qtys), 0.001, 'half the budget deployed → half the qty per level');
    }

    public function testOvercommittedSharedBudgetHaltsEntriesFailClosed(): void
    {
        // a second active run pushes the slices past the shared 1000 pool
        $other = new GridRun();
        $other->setLabel(static::uniq('guard2'));
        $other->setSymbol('ETHUSDT');
        $other->setStatus('Testnet');
        $other->setPLow('1000');
        $other->setPHigh('2000');
        $other->setNLevels(4);
        $other->setSpacing('Arithmetic');
        $other->setAllocation('EqualBase');
        $other->setBudgetQuote('600');
        $other->setFeePct('0.001');
        $other->setMaxPositionQuote('10000');
        $other->setMaxOrderQuote('1000');
        $other->setDailyLossLimitQuote('10000');
        $other->setBreakoutBufferPct('0.02');
        $other->setBreakoutPolicy('HaltAndHold');
        $other->setMaxOpenOrders(60);
        $other->setRunUid(static::uniq('guard2'));
        $other->save(); // 550 + 600 = 1150 > 1000

        $this->sim->setPrice('150');
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $d = new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
        $d->boot();
        $d->tick();

        $buys = array_filter($this->sim->open, fn ($o) => $o['side'] === 'BUY');
        $this->assertCount(0, $buys, 'never overcommit: an overcommitted shared budget must halt new entries');
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('budget_overcommit')->count());

        // freeing the other run's slice re-enables entries on the next tick
        $other->setStatus('Done');
        $other->save();
        $d->tick();
        $buys = array_filter($this->sim->open, fn ($o) => $o['side'] === 'BUY');
        $this->assertGreaterThan(0, count($buys), 'entries resume once the slices fit again');
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('budget_ok')->count());
        // and the alert did not spam a second time
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('budget_overcommit')->count());
    }

    public function testAppliedRefitStampsTheMatchingDecision(): void
    {
        // flat below range → no orders, so a pending refit may apply
        $this->sim->setPrice('50');
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport());
        $d = new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
        $d->boot();
        $d->tick();

        $this->run->reload();
        $this->run->setPLow('120');
        $this->run->setPHigh('220');
        $this->run->save();
        $dec = \App\Domains\Bot\DecisionScorer::record($this->run, 'Claude', '120', '220', 4, 'test refit', '50');

        $d->tick(); // maybeRefit applies the new geometry (flat)

        $dec->reload();
        $this->assertNotNull($dec->getAppliedAt(), 'the daemon must stamp when a decision\'s geometry actually took effect');

        $applied = BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('refit_applied')
            ->count();
        $this->assertSame(1, $applied);
    }
}
