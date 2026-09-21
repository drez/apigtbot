<?php

namespace Tests\Custom\Bot;

use App\BotEventQuery;
use App\Domains\Bot\DarkArmSweep;
use App\Domains\Bot\EventLog;
use App\GridRun;
use App\GridRunQuery;
use Tests\Builder\Support\DbTestCase;

/**
 * The watchdog's second sweep: a run that has gone dark must be EXPLAINED or
 * ALERTED.
 *
 * The first watchdog loop only looks at the supervised set (Testnet / Live /
 * Retiring) and spawns a daemon for anything of those that is quiet. Every
 * other non-terminal status — Halted, DryRun — and every killed run is
 * silent BY DESIGN, so the loop skips it. That is exactly the hole run 7 fell
 * into: killed externally on 08-21 and left parked for days, its slice
 * stranded, with nothing saying so.
 *
 * "Explained" means the run's own history accounts for the silence, and the
 * question is asked of the VALUE, not of the timestamps: the status it sits in
 * is the status the newest audit row put it in, or the newest kill_switch row
 * turned the kill on. Timestamps cannot carry it — a park is written BEFORE
 * the run stops ticking (the daemon stamps last_tick_at at the top of the
 * tick, then applies the kill; a killed run keeps heartbeating for days), so
 * "newer than last_tick_at" would excuse nothing. The lifecycle bot_event
 * fallback IS time-bounded, with a 6 h grace for the same reason.
 * Anything else is a run nobody decided to park, and that gets one Alert per
 * 6 h.
 *
 * Every assertion here reads the real rows the real save path wrote — the
 * audit rows come from an actual GridRun::save(), not a hand-built fixture,
 * because the point of the sweep is that the add_audit behavior is what makes
 * the silence explainable.
 */
class DarkArmSweepTest extends DbTestCase
{
    /** stale threshold used throughout: 1 h, the GTBOT_ARM_DARK_ALERT default */
    private const STALE = 3600;

    protected function setUp(): void
    {
        parent::setUp();
        // The sweep scans every run in the database, and the dev database
        // holds the real fleet. Stamp them all as having just ticked so they
        // are not stale: the sweep then examines only the fixture this test
        // creates, and the injected EventLog (which is bound to ONE run id)
        // cannot be handed another run's alert. last_tick_at is deliberately
        // NOT an audited column, so these saves write no audit rows.
        foreach (GridRunQuery::create()->find() as $r) {
            $r->setLastTickAt(date('Y-m-d H:i:s'));
            $r->save();
        }
    }

    private function mkRun(string $status, ?int $lastTickAgo = null, bool $killed = false): GridRun
    {
        $r = new GridRun();
        $r->setLabel(static::uniq('dark'));
        $r->setSymbol('BTCUSDT');
        $r->setStatus($status);
        $r->setKillSwitch($killed);
        $r->setAlgo('Grid');
        $r->setProfile('Balanced');
        $r->setSimulated(true);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(5);
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('300');
        $r->setDeployPct(100);
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('300');
        $r->setMaxOrderQuote('100');
        $r->setDailyLossLimitQuote('30');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(30);
        $r->setRunUid(static::uniq('dk'));
        if ($lastTickAgo !== null) {
            $r->setLastTickAt(date('Y-m-d H:i:s', time() - $lastTickAgo));
        }
        $r->save();

        return $r;
    }

    /** The sink the sweep writes through: no echo, no Telegram. */
    private function log(GridRun $run): EventLog
    {
        return new EventLog((int) $run->getIdGridRun(), false, null);
    }

    /** Back-date every audit row of one field, to prove timing is not read. */
    private function backdateAudit(GridRun $run, string $field, int $secondsAgo): void
    {
        \Propel::getConnection()->prepare(
            'UPDATE grid_run_audit SET date_creation = ? WHERE id_grid_run = ? AND field = ?'
        )->execute([date('Y-m-d H:i:s', time() - $secondsAgo), (int) $run->getIdGridRun(), $field]);
    }

    /** Back-date a bot_event: the event leg of the rule IS time-bounded. */
    private function backdateEvent(GridRun $run, string $kind, int $secondsAgo): void
    {
        \Propel::getConnection()->prepare(
            'UPDATE bot_event SET date_creation = ? WHERE id_grid_run = ? AND kind = ?'
        )->execute([date('Y-m-d H:i:s', time() - $secondsAgo), (int) $run->getIdGridRun(), $kind]);
    }

    /** A status change nobody recorded: direct SQL, exactly like a legacy edit. */
    private function setStatusUnaudited(GridRun $run, string $status): void
    {
        $ordinal = array_search($status, \App\GridRunPeer::getValueSet(\App\GridRunPeer::STATUS), true);
        $this->assertNotFalse($ordinal, 'unknown status ' . $status);
        \Propel::getConnection()->prepare('UPDATE grid_run SET status = ? WHERE id_grid_run = ?')
            ->execute([$ordinal, (int) $run->getIdGridRun()]);
        \App\GridRunPeer::clearInstancePool();
    }

    private function alerts(GridRun $run): int
    {
        return BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByKind(DarkArmSweep::KIND)
            ->count();
    }

    private function alertMessage(GridRun $run): string
    {
        $row = BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByKind(DarkArmSweep::KIND)
            ->findOne();

        return $row ? (string) $row->getMessage() : '';
    }

    public function testHaltedAndStaleWithNoMarkerAlertsOnce(): void
    {
        $run = $this->mkRun('Halted', 3 * 3600);

        $raised = DarkArmSweep::run(self::STALE, $this->log($run), time());

        $this->assertSame(1, $this->alerts($run), 'a Halted run quiet for 3 h with nothing explaining it must alert exactly once');
        $this->assertCount(1, $raised);
        $this->assertSame((int) $run->getIdGridRun(), $raised[0]['run']);
        $msg = $this->alertMessage($run);
        $this->assertStringContainsString((string) $run->getLabel(), $msg);
        $this->assertStringContainsString('status Halted', $msg);
        $this->assertStringContainsString('Alert', (string) BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByKind(DarkArmSweep::KIND)
            ->findOne()
            ->getLevel(), 'it must be an Alert so EventLog fans it out to Telegram');
    }

    public function testTheStatusItSitsInBeingTheAuditedOneExplainsTheSilence(): void
    {
        // Parked by hand: the audit row IS the explanation.
        $run = $this->mkRun('DryRun', 3 * 3600);
        $run->setStatus('Halted');
        $run->save();

        DarkArmSweep::run(self::STALE, $this->log($run), time());

        $this->assertSame(0, $this->alerts($run), 'the newest status audit row put it where it is — somebody parked it');
    }

    public function testAParkRecordedBeforeTheLastTickStillExplainsIt(): void
    {
        // The real ordering: FundsHold writes the status row and the event,
        // THEN enqueues the Reload, so the run's final heartbeats land after
        // the park. A "newer than last_tick_at" rule would alert here — every
        // 6 h, on exactly the run it exists to excuse.
        $run = $this->mkRun('DryRun', 3 * 3600);
        $run->setStatus('Halted');
        $run->save();
        $this->backdateAudit($run, 'status', 9 * 3600);   // 6 h BEFORE the last tick

        DarkArmSweep::run(self::STALE, $this->log($run), time());

        $this->assertSame(0, $this->alerts($run), 'the park is explained by its VALUE, whenever it was written');
    }

    public function testAStatusTheAuditTrailDoesNotAccountForAlerts(): void
    {
        // Audited edit to Live, then something parked it back to Halted with
        // no row to show for it (direct SQL, a legacy edit). The newest
        // audited value is not where the run is, so nothing explains it.
        $run = $this->mkRun('DryRun', 3 * 3600);
        $run->setStatus('Live');
        $run->save();
        $this->setStatusUnaudited($run, 'Halted');

        DarkArmSweep::run(self::STALE, $this->log($run), time());

        $this->assertSame(1, $this->alerts($run), 'an unaudited park is exactly what this sweep is for');
    }

    public function testFundsHoldEventExplainsTheSilence(): void
    {
        $run = $this->mkRun('Halted', 3 * 3600);
        (new EventLog((int) $run->getIdGridRun(), false, null))
            ->write('Info', 'funds_hold', 'slice handed back to the pool');

        DarkArmSweep::run(self::STALE, $this->log($run), time());

        $this->assertSame(0, $this->alerts($run), 'a funds_hold marker accounts for a parked run');
    }

    public function testALiveRunTickingNormallyIsNeverExamined(): void
    {
        $run = $this->mkRun('Live', 10);

        DarkArmSweep::run(self::STALE, $this->log($run), time());

        $this->assertSame(0, $this->alerts($run), 'a healthy supervised run is the first watchdog loop\'s business, not this sweep\'s');
    }

    public function testASecondSweepInsideSixHoursStaysQuiet(): void
    {
        $run = $this->mkRun('Halted', 3 * 3600);

        DarkArmSweep::run(self::STALE, $this->log($run), time());
        $again = DarkArmSweep::run(self::STALE, $this->log($run), time() + 60);

        $this->assertSame(1, $this->alerts($run), 'the 6 h throttle must keep a parked run from alerting every minute');
        $this->assertSame([], $again);
    }

    public function testTheThirdAlertOnOneRunWaitsADayNotSixHours(): void
    {
        // A park that predates grid_run_audit can NEVER become explained:
        // there is no row recording the change and the run cannot acquire one
        // without a status write, which is the one thing nobody is doing to
        // it. At a flat 6 h that is four alerts a day, forever, about a fact
        // the operator learned the first time.
        $run = $this->mkRun('Halted', 3 * 3600);
        $t0 = time();

        DarkArmSweep::run(self::STALE, $this->log($run), $t0);
        $this->assertSame(1, $this->alerts($run));

        DarkArmSweep::run(self::STALE, $this->log($run), $t0 + 6 * 3600 + 60);
        $this->assertSame(2, $this->alerts($run), 'the first two are still 6 h apart');

        DarkArmSweep::run(self::STALE, $this->log($run), $t0 + 12 * 3600 + 120);
        $this->assertSame(2, $this->alerts($run), 'said twice, it backs off: 6 h is no longer enough');

        DarkArmSweep::run(self::STALE, $this->log($run), $t0 + 30 * 3600 + 180);
        $this->assertSame(3, $this->alerts($run), '...and 24 h is — the stranded slice still shows up in a day\'s feed');
    }

    public function testTheBackOffIsPerRunNotFleetWide(): void
    {
        $old = $this->mkRun('Halted', 3 * 3600);
        $t0 = time();
        DarkArmSweep::run(self::STALE, $this->log($old), $t0);
        DarkArmSweep::run(self::STALE, $this->log($old), $t0 + 6 * 3600 + 60);
        $this->assertSame(2, $this->alerts($old), 'this run is now on the daily interval');

        $fresh = $this->mkRun('Halted', 3 * 3600);
        DarkArmSweep::run(self::STALE, $this->log($fresh), $t0 + 12 * 3600 + 120);
        $this->assertSame(1, $this->alerts($fresh), 'a newly dark arm is not quieted by an old one\'s history');
        $this->assertSame(2, $this->alerts($old));
    }

    public function testAKilledRunWithAKillEventIsExplained(): void
    {
        // Live + killed: supervised, so the first loop skips it (silence is
        // expected while killed) — this sweep is the only thing looking.
        $run = $this->mkRun('Live', 3 * 3600, true);
        (new EventLog((int) $run->getIdGridRun(), false, null))
            ->write('Alert', 'kill', 'kill: canceling open buys, holding inventory');

        DarkArmSweep::run(self::STALE, $this->log($run), time());

        $this->assertSame(0, $this->alerts($run), 'a kill event explains a killed run holding still');
    }

    public function testAKilledSupervisedRunWithNothingExplainingItAlerts(): void
    {
        // Run 7: killed from outside, left parked for days, slice stranded.
        $run = $this->mkRun('Live', 3 * 3600, true);

        DarkArmSweep::run(self::STALE, $this->log($run), time());

        $this->assertSame(1, $this->alerts($run), 'the kill switch is what makes a supervised run this sweep\'s business');
        $this->assertStringContainsString('kill on', $this->alertMessage($run));
    }

    public function testARunThatHasNeverTickedCountsAsStale(): void
    {
        $run = $this->mkRun('Halted', null);

        DarkArmSweep::run(self::STALE, $this->log($run), time());

        $this->assertSame(1, $this->alerts($run), 'a NULL last_tick_at on a non-Draft run is silence, not a fresh heartbeat');
    }

    public function testAKillEventInsideTheGraceWindowExplainsIt(): void
    {
        // The daemon stamps last_tick_at at the TOP of the tick and only then
        // applies the kill, and a killed run keeps heartbeating — so the kill
        // event is always older than the last tick.
        $run = $this->mkRun('Live', 3 * 3600, true);
        (new EventLog((int) $run->getIdGridRun(), false, null))->write('Alert', 'kill', 'kill: holding inventory');
        $this->backdateEvent($run, 'kill', 8 * 3600);     // 5 h before the last tick

        DarkArmSweep::run(self::STALE, $this->log($run), time());

        $this->assertSame(0, $this->alerts($run), 'a marker within the 6 h grace still explains the silence');
    }

    public function testAKillEventOlderThanTheGraceWindowDoesNot(): void
    {
        $run = $this->mkRun('Live', 3 * 3600, true);
        (new EventLog((int) $run->getIdGridRun(), false, null))->write('Alert', 'kill', 'kill: holding inventory');
        $this->backdateEvent($run, 'kill', 10 * 3600);    // 7 h before the last tick

        DarkArmSweep::run(self::STALE, $this->log($run), time());

        $this->assertSame(1, $this->alerts($run), 'the grace is 6 h, not forever — an ancient kill explains nothing about today');
    }

    public function testAKillSwitchAuditRowExplainsItWhateverItsAge(): void
    {
        $run = $this->mkRun('Live', 3 * 3600);
        $run->setKillSwitch(true);
        $run->save();
        $this->backdateAudit($run, 'kill_switch', 30 * 86400);   // a month ago

        DarkArmSweep::run(self::STALE, $this->log($run), time());

        $this->assertSame(0, $this->alerts($run), 'somebody threw this switch; the kill is not news no matter how old');
    }

    public function testADraftRunIsNotExpectedToTick(): void
    {
        $run = $this->mkRun('Draft', null);

        DarkArmSweep::run(self::STALE, $this->log($run), time());

        $this->assertSame(0, $this->alerts($run), 'a Draft run has never been launched — its silence is the point');
    }
}
