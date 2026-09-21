<?php

namespace App\Domains\Bot;

use App\BotEventQuery;
use App\FleetSlot;
use App\FleetSlotPeer;
use App\FleetSlotQuery;
use App\GridRun;
use App\GridRunQuery;
use Criteria;

/**
 * The DECLARED fleet — reconcile, lookups and the pool-safe reads over the
 * `fleet_slot` rows.
 *
 * Until 2026-09-19 the trend arm WAS a run: bin/gtbot-trend-activate
 * enumerated Live+Trend runs and TrendActivator kept its state machine in
 * bot_event markers on the run id. A symbol whose arm was Halted, Done or
 * never created was therefore invisible to the whole mechanism — it got no
 * regime verdict, no alert and no arm. BNB ran +5.7% over 2026-09-15..18 with
 * its arm parked by hand and not one verdict journaled.
 *
 * A slot inverts that: the (symbol, algo) pair is declared first and a run
 * fills it second. Every slot is classified on every pass whether or not a
 * run is behind it, the activator's state machine lives on the slot row, and
 * an empty or parked slot alerts instead of going quiet.
 *
 * The generated Propel model (App\FleetSlot) stays the row; this class is the
 * service around it — nothing else should build fleet_slot queries.
 */
final class FleetSlots
{
    /** Today the fleet declares trend arms only; grids are mechanical (cron-owned). */
    public const ALGO = 'Trend';

    /** Statuses that still count as a run filling a slot — everything but the terminal one. */
    private const ALIVE = ['Draft', 'DryRun', 'Testnet', 'Live', 'Halted', 'Retiring'];

    // ── reconcile ───────────────────────────────────────────────────────

    /**
     * Make the declared fleet match the runs that exist. Idempotent, called at
     * the top of every activator pass, safe against prod's existing rows.
     *
     * Two sources, in order:
     *   1. every symbol with a non-Done Trend run gets a slot holding that run
     *      (see pickRun for which one), carrying over the state and activation
     *      payload the pre-slot event chain left behind — that is the one-time
     *      migration, no SQL;
     *   2. every symbol with a non-Done Grid run gets a Trend slot too, empty.
     *      That is what turns "BNB has grids but no trend arm" from nothing at
     *      all into a visible empty slot the next pass fills and alerts on.
     *
     * Existing slots are never rewritten here: a slot the operator disabled or
     * re-sliced stays as it is, and the per-pass run resolution (resolveRun)
     * owns keeping id_grid_run current.
     *
     * @return list<FleetSlot> the slots this call created
     */
    public static function reconcileFromRuns(): array
    {
        $created = [];
        foreach (self::symbolsWithRuns() as $symbol => $hasTrendRun) {
            if (self::find($symbol) !== null) {
                continue;
            }
            $run = $hasTrendRun ? self::pickRun($symbol) : null;
            $slot = new FleetSlot();
            $slot->setSymbol($symbol);
            $slot->setAlgo(self::ALGO);
            // 0 = FOLLOW gtbot_trend_target_slice (2026-09-21, round 2).
            // Copying the config value in here pinned it: every later edit of
            // the knob the config row itself calls "the slice an activation
            // raises the arm to" was inert from the moment the slot existed.
            // A non-zero target is an OPERATOR decision for one symbol and
            // still wins (TrendActivator::slotTarget), which is what the
            // column is for; prod's existing 350 rows keep working unchanged.
            $slot->setTargetSlice('0');
            $slot->setEnabled(true);
            $slot->setState('idle');
            $slot->setConfirmUp(0);
            $slot->setConfirmDown(0);
            if ($run !== null) {
                $id = (int) $run->getIdGridRun();
                $slot->setIdGridRun($id);
                $slot->setState(self::legacyState($id));
                $payload = self::legacyActivation($id);
                $slot->setActivation($payload === null ? null : json_encode($payload, JSON_UNESCAPED_SLASHES));
            }
            $slot->save();
            $created[] = $slot;
        }
        return $created;
    }

    /**
     * Symbols that need a slot: true when the symbol has a non-Done Trend run
     * (the slot adopts it), false when only its grids are alive.
     *
     * @return array<string, bool>
     */
    private static function symbolsWithRuns(): array
    {
        $out = [];
        $rows = GridRunQuery::create()
            ->filterByStatus('Done', Criteria::NOT_EQUAL)
            ->orderByIdGridRun()
            ->find();
        foreach ($rows as $r) {
            $symbol = (string) $r->getSymbol();
            $isTrend = (string) ($r->getAlgo() ?: 'Grid') === 'Trend';
            $out[$symbol] = ($out[$symbol] ?? false) || $isTrend;
        }
        return $out;
    }

    /**
     * The run that fills a symbol's trend slot: **a Live one first, then the
     * newest**, among the non-Done Trend runs.
     *
     * Newest-wins alone is wrong on the shape prod is in. BTC carries run 1
     * (Live, the working arm) and run 8 (Halted, "core (retired)"), and run 8
     * is the newer row — so a plain newest-wins picker would hand the slot to
     * the retired core, park the live arm's state and alert that the fleet is
     * dark. A run the operator left Live is the arm they mean; among equals
     * (or when none is Live) the newest is the most recent intent.
     */
    public static function pickRun(string $symbol): ?GridRun
    {
        $best = null;
        $rows = GridRunQuery::create()
            ->filterBySymbol($symbol)
            ->filterByAlgo('Trend')
            ->filterByStatus(self::ALIVE, Criteria::IN)
            ->orderByIdGridRun()
            ->find();
        foreach ($rows as $r) {
            // rows arrive id-ascending, so "replace" means "newer wins":
            // a Live candidate always replaces, a non-Live one only replaces
            // another non-Live — Live therefore beats any newer parked run.
            if ($best === null
                || (string) $r->getStatus() === 'Live'
                || (string) $best->getStatus() !== 'Live') {
                $best = $r;
            }
        }
        return $best;
    }

    /**
     * The run behind a slot right now — **the Live-first preference applies
     * every pass, not only when the held run dies.**
     *
     *   - the held run is Live      → keep it, even if a newer Live run exists
     *                                 (the operator's working arm is not
     *                                 handed over because someone drafted a
     *                                 second one);
     *   - the held run is not Live and a Live Trend run exists for the symbol
     *                                 → adopt that one;
     *   - the held run is not Live, nothing Live on offer, and it is not Done
     *                                 → keep it (no churn between two parked
     *                                 runs);
     *   - the held run is Done or absent → the best candidate, or nothing.
     *
     * "Only Done releases the slot" was wrong: retiring an arm that still
     * holds inventory leaves it Retiring (RunLifecycle::retire), and halting
     * one leaves it Halted — so the slot would sit on the parked run for as
     * long as its exits take while the replacement arm the operator just put
     * Live is orphaned, reading idle/Auto and never machine-scaled.
     *
     * Adopting a DIFFERENT run starts that arm clean: state idle, both
     * counters zero, no activation payload. The payload describes an episode
     * of the run that is leaving — replaying it onto a new arm would restore
     * the wrong grid slices on release.
     */
    public static function resolveRun(FleetSlot $slot, bool $write = true): ?GridRun
    {
        $heldId = $slot->getIdGridRun() !== null ? (int) $slot->getIdGridRun() : null;
        $held = $heldId !== null ? GridRunQuery::create()->findPk($heldId) : null;
        if ($held !== null && (string) $held->getStatus() === 'Live') {
            return $held;
        }
        $candidate = self::pickRun((string) $slot->getSymbol());
        $candidateIsLive = $candidate !== null && (string) $candidate->getStatus() === 'Live';
        if ($held !== null && (string) $held->getStatus() !== 'Done' && !$candidateIsLive) {
            return $held;
        }
        $id = $candidate !== null ? (int) $candidate->getIdGridRun() : null;
        if ($write && $id !== $heldId) {
            $slot->setIdGridRun($id);
            $slot->setState('idle');
            $slot->setConfirmUp(0);
            $slot->setConfirmDown(0);
            $slot->setActivation(null);
            $slot->save();
        }
        return $candidate;
    }

    // ── lookups ─────────────────────────────────────────────────────────

    public static function find(string $symbol, string $algo = self::ALGO): ?FleetSlot
    {
        return FleetSlotQuery::create()->filterBySymbol($symbol)->filterByAlgo($algo)->findOne();
    }

    public static function byRun(int $runId): ?FleetSlot
    {
        return FleetSlotQuery::create()->filterByIdGridRun($runId)->findOne();
    }

    /** @return list<FleetSlot> every declared slot, symbol order */
    public static function all(): array
    {
        return iterator_to_array(FleetSlotQuery::create()->orderBySymbol()->orderByAlgo()->find(), false);
    }

    /**
     * The run a slot-less verdict hangs off. bot_event is a child of grid_run,
     * so a symbol with no arm still needs SOME run to journal against; any
     * active one will do (the payload carries the symbol). Same trick
     * bin/gtbot-allocate uses for its pool-wide events.
     */
    public static function hostRun(): ?GridRun
    {
        return GridRunQuery::create()
            ->filterByStatus(BudgetGuard::ACTIVE_STATUSES, Criteria::IN)
            ->orderByIdGridRun()
            ->findOne();
    }

    // ── pool-safe reads (adapters call these from long-lived daemons) ───

    /**
     * The arm state of the run filling a slot — 'idle' when no slot holds it.
     *
     * select(): raw rows, never pooled objects. Propel 1 never re-hydrates an
     * object already in its instance pool, so a hydrated read inside a daemon
     * would serve the state as of that process's boot and never see the cron's
     * activation (see BudgetGuard::check for the prod incident). Raw rows come
     * back with ENUMs as their int ordinal, so State is mapped back here.
     */
    public static function stateOf(int $runId): string
    {
        $row = FleetSlotQuery::create()
            ->filterByIdGridRun($runId)
            ->select(['IdFleetSlot', 'State'])
            ->findOne();
        if (!is_array($row)) {
            return 'idle';
        }
        $states = FleetSlotPeer::getValueSet(FleetSlotPeer::STATE);
        return (string) ($states[$row['State']] ?? $row['State'] ?? 'idle');
    }

    /** The activation payload of the run's slot; null when it has none. */
    public static function activationOf(int $runId): ?array
    {
        $row = FleetSlotQuery::create()
            ->filterByIdGridRun($runId)
            ->select(['IdFleetSlot', 'Activation'])
            ->findOne();
        if (!is_array($row) || ($row['Activation'] ?? null) === null) {
            return null;
        }
        $p = json_decode((string) $row['Activation'], true);
        return is_array($p) ? $p : null;
    }

    // ── surfaces ────────────────────────────────────────────────────────

    /**
     * shared.fleet for the routine brief: one entry per declared slot, so the
     * routine sees the symbols with NO arm as plainly as the ones with one.
     *
     * @return list<array{symbol:string, algo:string, run:?int, state:string, verdict:?string, verdict_at:?string, target_slice:string, empty:bool}>
     */
    public static function brief(): array
    {
        $out = [];
        foreach (self::all() as $slot) {
            $run = $slot->getIdGridRun() !== null ? (int) $slot->getIdGridRun() : null;
            $out[] = [
                'symbol' => (string) $slot->getSymbol(),
                'algo' => (string) $slot->getAlgo(),
                'run' => $run,
                'state' => (string) $slot->getState(),
                'verdict' => $slot->getLastVerdict() !== null ? (string) $slot->getLastVerdict() : null,
                'verdict_at' => $slot->getVerdictAt('Y-m-d H:i'),
                // the EFFECTIVE target: a slot storing 0 follows the fleet
                // config, so the brief must not report a bare 0
                'target_slice' => TrendActivator::effectiveTarget($slot),
                'empty' => $run === null,
                'enabled' => (bool) $slot->getEnabled(),
            ];
        }
        return $out;
    }

    // ── one-time migration of the pre-slot event chain ──────────────────

    /**
     * The arm state as the PRE-SLOT world computed it: the last marker event
     * on the run. Used ONLY by reconcileFromRuns(), to carry a running arm's
     * state across the cutover — prod run 1 is active as this ships and must
     * not be re-activated from idle. After that pass the slot is the truth and
     * nothing reads the chain again.
     */
    private static function legacyState(int $runId): string
    {
        $e = BotEventQuery::create()
            ->filterByIdGridRun($runId)
            ->filterByKind([TrendActivator::KIND_ACTIVATE, TrendActivator::KIND_DEACTIVATE, TrendActivator::KIND_RELEASE], Criteria::IN)
            ->orderByIdBotEvent(Criteria::DESC)
            ->findOne();
        if ($e === null) {
            return 'idle';
        }
        return match ((string) $e->getKind()) {
            TrendActivator::KIND_ACTIVATE => 'active',
            TrendActivator::KIND_DEACTIVATE => 'winding_down',
            default => 'idle',
        };
    }

    /** The pre-slot activation payload (last trend_activate event). @see legacyState */
    private static function legacyActivation(int $runId): ?array
    {
        if (self::legacyState($runId) === 'idle') {
            return null;
        }
        $e = BotEventQuery::create()
            ->filterByIdGridRun($runId)
            ->filterByKind(TrendActivator::KIND_ACTIVATE)
            ->orderByIdBotEvent(Criteria::DESC)
            ->findOne();
        if ($e === null) {
            return null;
        }
        $p = json_decode((string) $e->getPayload(), true);
        return is_array($p) ? $p : null;
    }
}
