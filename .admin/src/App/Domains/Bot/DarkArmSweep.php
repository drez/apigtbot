<?php

namespace App\Domains\Bot;

use App\BotEventQuery;
use App\GridRun;
use App\GridRunAuditQuery;
use App\GridRunQuery;
use Criteria;

/**
 * Explain-or-alert for an arm that has gone dark.
 *
 * The watchdog's first loop supervises the runs that are SUPPOSED to be
 * trading (Testnet / Live / Retiring) and spawns a daemon for any of them
 * whose heartbeat is stale. Everything else is silent by design and the loop
 * skips it — a Halted run has no daemon, and a killed run "is killed on
 * purpose; silence is expected".
 *
 * Except that parked-on-purpose and parked-by-accident look identical from
 * the outside. Run 7 sat killed from 08-21 for days with its 350 USDT slice
 * stranded, and run 9 the same: nothing was broken, so nothing alerted, and
 * the fleet quietly ran on a fraction of its capital.
 *
 * So: for every run that is not terminal (Done) and not unlaunched (Draft),
 * is outside the supervised set OR carries the kill switch, and has not
 * ticked for $staleAfter seconds — ask the run's OWN HISTORY why.
 *
 * The question is asked of the VALUE, not of the timestamps: the run is
 * explained when its current parked state is the state an audit row put it in
 * — the newest `status` row says `value_to` = the status it is in now, or the
 * newest `kill_switch` row turned the kill on. Somebody (GUI, MCP, CLI,
 * daemon — the row says which) did this on purpose, whenever that was.
 *
 * Timestamps cannot carry that question, because a park is written BEFORE the
 * run stops ticking: Daemon::tick() stamps last_tick_at at the TOP of the
 * tick and only then consumes commands and applies the kill, a killed run
 * keeps heartbeating for days afterwards, and FundsHold::hold() writes its
 * audit row and event before enqueuing the Reload that ends the run. A
 * "marker must be newer than last_tick_at" rule therefore excuses nothing and
 * alerts every 6 h on precisely the runs it exists to excuse.
 *
 * A lifecycle bot_event is the fallback for a park that left no audit row,
 * and that one IS time-bounded — but with a grace window (MARKER_GRACE) for
 * the same reason. Unaccounted-for silence gets one Alert per 6 h for the
 * first two and one per 24 h after that (REALERT_AFTER_SETTLED), which
 * EventLog fans out to Telegram.
 *
 * This is the first consumer of the `add_audit` behavior: before it, a status
 * change left no trace at all, so "who stopped this run" had no answer and
 * this sweep could not have been written.
 */
final class DarkArmSweep
{
    /** The bot_event kind this sweep writes. */
    public const KIND = 'arm_dark';

    /** Statuses that are not expected to tick: finished, and never started. */
    private const NEVER_TICKS = ['Done', 'Draft'];

    /** The runs the watchdog's first loop already supervises and respawns. */
    private const SUPERVISED = ['Testnet', 'Live', 'Retiring'];

    /** bot_event kinds that account for a run falling quiet. */
    public const MARKER_KINDS = [
        FundsHold::KIND_HOLD,           // slice handed back to the pool
        RunLifecycle::KIND_RETIRE,      // winding down
        RunLifecycle::KIND_FINALIZE,    // wound down
        'kill',                         // the daemon applied a kill
        'kill_requested',               // a human/routine asked for one (MCP)
        TrendActivator::KIND_RELEASE,   // trend arm released its slice
        TrendActivator::KIND_DEACTIVATE,
    ];

    /**
     * How far BEFORE the last tick a marker event still explains the silence.
     *
     * Two reasons it cannot be zero. A park is written before the run stops
     * ticking — the daemon stamps last_tick_at at the top of the tick and
     * only then applies the kill, and a killed run goes on heartbeating — so
     * the final heartbeat always lands after the marker. And bot_event rows
     * carry the WRITER's PHP clock: this project's web/MCP SAPI runs on the
     * operator's timezone (config/legacy.php) while the daemon runs on UTC,
     * which on the observed hosts is another ~4 h apart. Six hours covers
     * both with room to spare; the cost of being generous here is a late
     * alert on a run that was already explained, and the cost of being strict
     * is crying wolf about the operator's own action.
     */
    private const MARKER_GRACE = 6 * 3600;

    /** One alert per run per this window, for the first two alerts. */
    private const REALERT_AFTER = 6 * 3600;

    /**
     * ...and per this one from the third alert on.
     *
     * A run parked BEFORE `grid_run_audit` existed can never become
     * explained: there is no row recording the change, and the run cannot
     * acquire one without a status write — which is precisely the thing
     * nobody is doing to it. Flat 6 h meant such a run alerted four times a
     * day, forever, about a fact the operator learned the first time; the
     * pre-audit parks are also the oldest and least urgent ones. Two alerts
     * six hours apart is "you have not answered this yet"; after that the
     * sweep settles to daily, which still surfaces the stranded slice in any
     * day's feed without being the reason the feed is ignored.
     *
     * The back-off is per RUN and counts that run's own arm_dark history, so
     * a newly dark arm is never quieted by an old one's noise.
     */
    private const REALERT_AFTER_SETTLED = 24 * 3600;

    /** After this many prior alerts on a run, the interval settles to daily. */
    private const REALERT_BACKOFF_AFTER = 2;

    /**
     * Sweep every dark run and alert on the unexplained ones.
     *
     * @param int       $staleAfter seconds of silence before a run is dark
     * @param EventLog|null $log    sink for EVERY alert this call raises — a
     *                              test's capture, or a single-run caller's.
     *                              Null (the watchdog's case) builds one per
     *                              run with the Telegram notifier from .env,
     *                              which is the same notifier the script
     *                              itself refuses to start without.
     * @param int|null  $now        clock override for tests
     *
     * @return list<array{run:int, label:string, message:string}> alerts raised
     */
    public static function run(int $staleAfter, ?EventLog $log = null, ?int $now = null): array
    {
        $now = $now ?? time();
        $out = [];

        $runs = GridRunQuery::create()
            ->filterByStatus(self::NEVER_TICKS, Criteria::NOT_IN)
            ->find();

        foreach ($runs as $run) {
            $status = (string) $run->getStatus();
            $killed = (bool) $run->getKillSwitch();
            // A healthy supervised run is the first loop's business: it gets a
            // daemon spawned, not an "is this parked?" question. The kill
            // switch is what makes such a run ours — it suppresses the spawn.
            if (in_array($status, self::SUPERVISED, true) && !$killed) {
                continue;
            }

            $last = $run->getLastTickAt('U');
            $last = $last === null || $last === '' ? null : (int) $last;
            $age  = $last === null ? null : $now - $last;
            // A non-Draft run that has NEVER ticked is silence too, not a
            // fresh heartbeat (Draft is already excluded above).
            if ($age !== null && $age <= $staleAfter) {
                continue;
            }

            $runId = (int) $run->getIdGridRun();
            if (self::explained($run, $last, $now)) {
                continue;
            }

            // Same throttle pattern as watchdog_stale, and safe to compare
            // straight: our own alerts are always written from this process.
            // The window widens once this run has been told twice — see
            // REALERT_AFTER_SETTLED.
            $prior = BotEventQuery::create()
                ->filterByIdGridRun($runId)
                ->filterByKind(self::KIND)
                ->count();
            $window = $prior < self::REALERT_BACKOFF_AFTER ? self::REALERT_AFTER : self::REALERT_AFTER_SETTLED;
            $recent = BotEventQuery::create()
                ->filterByIdGridRun($runId)
                ->filterByKind(self::KIND)
                ->filterByDateCreation(['min' => date('Y-m-d H:i:s', $now - $window)])
                ->count();
            if ($recent > 0) {
                continue;
            }

            $message = sprintf(
                'run %d (%s) has not ticked for %s — status %s, kill %s, and nothing in the audit or event log '
                . 'explains it (parked on purpose? deploy a Hold or set it Live)',
                $runId,
                (string) $run->getLabel(),
                $age === null ? 'ever (no tick on record)' : self::humanAge($age),
                $status,
                $killed ? 'on' : 'off'
            );
            ($log ?? new EventLog($runId, false, TelegramNotifier::fromEnv()))
                ->write('Alert', self::KIND, $message);
            $out[] = ['run' => $runId, 'label' => (string) $run->getLabel(), 'message' => $message];
        }

        return $out;
    }

    /**
     * Does the run's own history account for the silence?
     *
     * Value first (no timestamp involved, see the class docblock), lifecycle
     * event second. $since is the last tick, null for a run that never
     * ticked — then the event leg considers its whole history, since there is
     * no "since".
     */
    private static function explained(GridRun $run, ?int $since, int $now): bool
    {
        $runId  = (int) $run->getIdGridRun();
        $status = (string) $run->getStatus();

        // Parked: the status it sits in is the status an audit row put it in.
        // A run whose status was changed with no row to show for it (direct
        // SQL, a pre-add_audit park) is NOT explained — which is the point.
        if (!in_array($status, self::SUPERVISED, true)
            && self::newestAuditValue($runId, 'status') === $status) {
            return true;
        }

        // Killed: the newest kill_switch row is the one that turned it on.
        if ((bool) $run->getKillSwitch()
            && self::isOn(self::newestAuditValue($runId, 'kill_switch'))) {
            return true;
        }

        // Fallback for a park that left no audit row (an engine parking
        // itself, or a change made before this table existed).
        $events = BotEventQuery::create()
            ->filterByIdGridRun($runId)
            ->filterByKind(self::MARKER_KINDS, Criteria::IN);
        if ($since !== null) {
            $events->filterByDateCreation([
                'min' => date('Y-m-d H:i:s', $since - self::MARKER_GRACE),
                'max' => date('Y-m-d H:i:s', $now),
            ]);
        }

        return $events->count() > 0;
    }

    /**
     * `value_to` of the LAST audit row for one field — newest by primary key,
     * never by timestamp: the id is the write order, and it is the only thing
     * here that cannot be wrong about which change came last.
     */
    private static function newestAuditValue(int $runId, string $field): ?string
    {
        $row = GridRunAuditQuery::create()
            ->filterByIdGridRun($runId)
            ->filterByField($field)
            ->orderByIdGridRunAudit(Criteria::DESC)
            ->findOne();

        return $row === null ? null : $row->getValueTo();
    }

    /** A boolean audit value normalizes to '1'/'0'; be liberal reading it. */
    private static function isOn(?string $value): bool
    {
        if ($value === null) {
            return false;
        }

        return !in_array(strtolower(trim($value)), ['', '0', 'false', 'no', 'off'], true);
    }

    /**
     * "3.2h" — the operator reads hours, not seconds. Past two days it reads
     * days: a run stranded since August is "49.4d", not "1185.8h".
     */
    private static function humanAge(int $seconds): string
    {
        [$value, $unit] = $seconds >= 48 * 3600
            ? [$seconds / 86400, 'd']
            : [$seconds / 3600, 'h'];

        return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.') . $unit;
    }
}
