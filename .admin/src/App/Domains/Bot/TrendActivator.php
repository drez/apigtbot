<?php

namespace App\Domains\Bot;

use App\BotEventQuery;
use App\BotOrderQuery;
use App\ConfigQuery;
use App\FleetSlot;
use App\GridRun;
use App\GridRunQuery;

/**
 * Machine-scaled trend arm (operator directive 2026-08-19: "remove human
 * from scaling the trend arm"). Cron-driven (bin/gtbot-trend-activate,
 * every 15 min). One pass per Trend run:
 *
 *   verdict  = TrendRegime::classify(stored 4h, 1d summaries)   → journaled
 *   decision = TrendRegime::decide(state, recent verdicts)       (hysteresis)
 *
 *   idle ──activate──▶ active ──deactivate──▶ winding_down ──release──▶ idle
 *                        ▲                         │ (re-armed on a new
 *                        └──────── activate ───────┘  confirmed TREND_UP)
 *
 * activate   trend slice → TARGET_SLICE (what the grids can spare above
 *            their floors — 50×n_levels, their invested quote, never raising
 *            a grid), deploy_pct → ACTIVE_DEPLOY_PCT. Decreases first, then
 *            the increase, BudgetGuard-checked at every write; Reload for
 *            every run that changed (Trend deploy/budget edits are inert
 *            until Reload; grids re-read their slice on boot).
 * deactivate deploy_pct → 0: entries stop, the engine keeps managing the
 *            open position's stop. The slice stays parked until flat.
 * release    once the run is flat (no engine position, no working orders):
 *            slice back to its pre-activation value, grids restored to their
 *            pre-activation slices (capped so the pool is never overcommitted;
 *            a grid the routine grew meanwhile is not lowered), deploy back
 *            to its pre-activation value.
 * re-assert  while active the activator OWNS the slice: it is DERIVED on
 *            every pass (2026-09-19) rather than replayed from the
 *            activation payload. A BREAKOUT arm holding a position is sized
 *            to what that position ties up — filled inventory plus the quote
 *            its working buys will still spend — and never below the least a
 *            run may be sliced at all (MIN_SLICE, capped by the slot's
 *            target): it takes no further entry until it is flat (a
 *            top-up at activation is refuted, variant H, refit-evidence
 *            §12), so anything above that is money it cannot reach, pinned
 *            and idle on the shared pool all the same. A flat arm — and an
 *            EmaCross1d core, whose staggered tranches ARE top-ups by
 *            design — is sized to the slot's target instead. Only that
 *            target rule may take from the grids; a held breakout arm's
 *            raise comes from pool headroom alone.
 *
 * Activation is CAPACITY: the engine still waits for its own entry signal
 * before it enters — the Donchian breakout (or 1h EMA regime entry) for the
 * breakout arm, the 1d EMA20 > EMA50 cross for the EmaCross1d inventory
 * core. A BREAKOUT arm never adds to an open position, so a slice raised
 * mid-position sizes its NEXT entry and nothing sooner (the one-shot top-up,
 * variant H, is refuted — refit-evidence §12). The EmaCross1d CORE is the
 * exception and always was: its staggered tranches into an open position are
 * its designed deployment (TrendEngine::tryAddCoreTranche runs while
 * holding), so a raise reaches the market on its next tranche.
 *
 * THE UNIT OF WORK IS A SLOT, not a run (2026-09-19). The arm used to BE a
 * run: the cron enumerated Live+Trend runs and this class kept its state in
 * marker bot_events on the run id, so a symbol whose arm was Halted, Done or
 * never created was invisible — no verdict, no alert, no arm (BNB ran +5.7%
 * over 2026-09-15..18 with its arm parked by hand and nothing journaled).
 * Now `fleet_slot` declares the arm, FleetSlots reconciles the runs into it,
 * and every slot is classified each pass whether or not a run fills it:
 *
 *   no run        → the slot creates its own Draft arm and alerts
 *                   (KIND_SLOT_EMPTY), at most once a day
 *   parked run    → KIND_ARM_PARKED once per TREND_UP episode
 *   scalable run  → the state machine above, as before
 *
 * State, the confirm/deactivate counters and the activation payload live on
 * the slot row. The marker events (trend_activate / trend_deactivate /
 * trend_release / trend_reassert) and the trend_regime verdicts are still
 * written exactly as before — dashboards and DarkArmSweep read them — but
 * they are a journal now, not the store. state() and activationPayload()
 * remain as adapters so every existing caller is unaffected.
 */
final class TrendActivator
{
    public const TARGET_SLICE = '500';
    /** config override of TARGET_SLICE (whole USDT); prod seeds 350 so two arms + two grids at floor fit a 1300 pool */
    public const CONFIG_TARGET_SLICE = 'gtbot_trend_target_slice';
    /** 100 since 2026-09-16 (refit-evidence §12): 50 kept half the trend
     *  slice idle for the top-up path §10 refuted — a 350 target put 175 in
     *  the market. The arm now deploys its whole slice on activation; the
     *  slice itself (gtbot_trend_target_slice) stays the sizing knob. */
    public const ACTIVE_DEPLOY_PCT = 100;
    /** routine's min-notional viability floor: 50 USDT per grid level */
    public const FLOOR_PER_LEVEL = '50';
    public const MIN_SLICE = '50';
    /** stored summaries older than this don't count as a read (MarketStore default) */
    public const STALE_AFTER = 1800;

    public const KIND_REGIME = 'trend_regime';
    public const KIND_ACTIVATE = 'trend_activate';
    public const KIND_DEACTIVATE = 'trend_deactivate';
    public const KIND_RELEASE = 'trend_release';
    public const KIND_REASSERT = 'trend_reassert';
    public const KIND_ERROR = 'trend_activator_error';
    /** a declared slot had no run and the fleet made itself one */
    public const KIND_SLOT_EMPTY = 'fleet_slot_empty';
    /** the slot's run is not Live (or is killed) while the tape says TREND_UP */
    public const KIND_ARM_PARKED = 'arm_parked_in_trend';

    /** fleet_slot_empty says its piece at most this often per slot. */
    public const EMPTY_ALERT_EVERY = 86400;

    /** A slot whose pass THREW says so at most this often. */
    public const PASS_ERROR_EVERY = 3600;

    /** Parked statuses that are already accounted for — notes, never an alert. */
    private const PARK_NOT_ALERTED = ['Draft', 'Retiring'];

    private const SCALE = 8;

    // ── pure planners ───────────────────────────────────────────────────

    /**
     * How far the trend slice can be raised toward $target, and what each
     * grid gives up.
     *
     * THE GRIDS' IDLE CAPITAL GOES FIRST, POOL HEADROOM LAST (2026-09-21,
     * round 2). It used to be the other way round, and on a pool carrying a
     * reserve that ordering spent the reserve on capital the grids had
     * sitting idle above their floors. Prod: arms at 349/348 and grids at
     * 390/211 with 75 floors — the arms needed 3 USDT to reach their 350
     * target, took them from the 65 USDT reserve, and left the fleet packed
     * to exactly the 1300 cap with ZERO headroom, which is the state
     * BudgetPool::reservePct exists to prevent (and which, with
     * gtbot_use_all_funds on, fail-closes every entry on the fleet the next
     * time mark-to-market moves one USDT). Taking it from a grid's idle
     * capital first leaves the reserve intact and costs the grid nothing it
     * was using: never below its floor, never below its committed inventory,
     * and never a raise.
     *
     * The invariant is unchanged either way — trend + Σgrids ≤ capacity —
     * because a grid cut and a headroom spend fund the same USDT.
     *
     * HEADROOM IS NOT THE SAME THING AS THE RESERVE (2026-09-21, round 3).
     * The round-2 ordering read every unallocated USDT as reserve, so a fleet
     * carrying 584 USDT of genuinely free capital (a run retired, a run held)
     * still stripped both grids toward their floors to fund an arm — and
     * gtbot-allocate handed it all back on its next pass: two trims, two
     * raises, four Reloads, and a Reload is a daemon RESTART. $freeCapacity
     * (the ALLOCATABLE pool, same subtraction as $capacity) splits the
     * headroom in two: the part above the reserve is spent FIRST, the grids'
     * idle capital second, and the reserve itself last, as round 2 intended.
     * Omit it and the whole headroom counts as reserve — the round-2
     * behaviour, which is what the operator paths (FundsHold) still want.
     *
     * @param string $capacity pool (see the caller) minus every active run NOT in this plan
     * @param array<int|string, array{budget: string, floor: string}> $grids
     * @param string|null $freeCapacity allocatable pool, same subtraction; null = none is free
     * @return array{trend: string, grids: array<int|string, string>, taken: string} grids = full map (changed or not)
     */
    public static function planActivate(string $capacity, string $trendBudget, array $grids, string $target, ?string $freeCapacity = null): array
    {
        $trendBudget = self::whole($trendBudget);
        $need = bcsub(self::whole($target), $trendBudget, 0);
        if (bccomp($need, '0', 0) <= 0) {
            return ['trend' => $trendBudget, 'grids' => array_map(static fn ($g) => self::whole($g['budget']), $grids), 'taken' => '0'];
        }
        $cur = [];
        $floor = [];
        $sumCur = '0';
        foreach ($grids as $id => $g) {
            $cur[$id] = self::whole($g['budget']);
            $f = self::whole($g['floor']);
            // never raise a grid on activation: a slice already under its floor stays put
            $floor[$id] = bccomp($f, $cur[$id], 0) > 0 ? $cur[$id] : $f;
            $sumCur = bcadd($sumCur, $cur[$id], 0);
        }
        $headroom = bcsub(bcsub($capacity, $trendBudget, 0), $sumCur, 0);
        if (bccomp($headroom, '0', 0) < 0) {
            $headroom = '0';
        }

        // the free (non-reserve) headroom first, then the grids' idle capital
        $free = $freeCapacity === null ? '0' : bcsub(bcsub($freeCapacity, $trendBudget, 0), $sumCur, 0);
        if (bccomp($free, '0', 0) < 0) {
            $free = '0';
        }
        if (bccomp($free, $headroom, 0) > 0) {
            $free = $headroom;
        }
        $fromFree = bccomp($need, $free, 0) <= 0 ? $need : $free;
        $reserve = bcsub($headroom, $fromFree, 0);

        $new = $cur;
        $taken = '0';
        $remaining = bcsub($need, $fromFree, 0);
        while (bccomp($remaining, '0', 0) > 0) {
            $eligible = array_filter(array_keys($new), static fn ($id) => bccomp($new[$id], $floor[$id], 0) > 0);
            if ($eligible === []) {
                break;
            }
            $sumEligible = '0';
            foreach ($eligible as $id) {
                $sumEligible = bcadd($sumEligible, $new[$id], 0);
            }
            $cutThisRound = '0';
            foreach ($eligible as $id) {
                $share = self::ceilDiv(bcmul($remaining, $new[$id], 0), $sumEligible);
                $room = bcsub($new[$id], $floor[$id], 0);
                $cut = bccomp($share, $room, 0) > 0 ? $room : $share;
                $left = bcsub($remaining, $cutThisRound, 0);
                if (bccomp($cut, $left, 0) > 0) {
                    $cut = $left;
                }
                $new[$id] = bcsub($new[$id], $cut, 0);
                $cutThisRound = bcadd($cutThisRound, $cut, 0);
            }
            if (bccomp($cutThisRound, '0', 0) <= 0) {
                break;
            }
            $remaining = bcsub($remaining, $cutThisRound, 0);
            $taken = bcadd($taken, $cutThisRound, 0);
        }
        // and only what the grids could not cover comes out of the RESERVE —
        // the last resort, not the first
        $fromHeadroom = bccomp($remaining, $reserve, 0) <= 0 ? $remaining : $reserve;
        $totalTaken = bcadd(bcadd($fromFree, $fromHeadroom, 0), $taken, 0);
        return ['trend' => bcadd($trendBudget, $totalTaken, 0), 'grids' => $new, 'taken' => $totalTaken];
    }

    /**
     * Give the trend slice back: trend → $trendIdle, each grid back up to its
     * pre-activation slice (never lowered if it grew meanwhile), the raises
     * scaled down if the pool can't take them all.
     *
     * AND NEVER MORE THAN THE POOL HOLDS (2026-09-21, round 3). $trendIdle is
     * the slice the arm carried BEFORE the episode, and on a breakout arm that
     * is routinely MORE than it carries now: reassert() derives an active
     * arm's slice down to its position, gtbot-allocate hands the freed capital
     * to the grids, and release then asked for the whole pre-activation number
     * back off a pool that no longer had it. BudgetGuard refused the write,
     * the RuntimeException escaped pass() (release() is the one write path
     * with no tryWrites around it), and the slot stayed winding_down for ever
     * — never released, never re-armed, its slice stranded. A release is
     * allowed to give back less than it took; it is not allowed to overcommit
     * the pool. Never below what the arm already holds: a decrease is always
     * safe, and clamping one would be a trim, not a release.
     *
     * @param array<int|string, array{budget: string, before: string}> $grids
     * @return array{trend: string, grids: array<int|string, string>}
     */
    public static function planRelease(string $capacity, string $trendBudget, string $trendIdle, array $grids): array
    {
        $trendIdle = self::whole($trendIdle);
        $cur = [];
        $raise = [];
        $sumCur = '0';
        $sumRaise = '0';
        foreach ($grids as $id => $g) {
            $cur[$id] = self::whole($g['budget']);
            $want = self::whole($g['before']);
            $raise[$id] = bccomp($want, $cur[$id], 0) > 0 ? bcsub($want, $cur[$id], 0) : '0';
            $sumCur = bcadd($sumCur, $cur[$id], 0);
            $sumRaise = bcadd($sumRaise, $raise[$id], 0);
        }
        $affordable = bcsub(bcadd($capacity, '0', 0), $sumCur, 0);
        $held = self::whole($trendBudget);
        if (bccomp($affordable, $held, 0) < 0) {
            $affordable = $held;
        }
        if (bccomp($trendIdle, $affordable, 0) > 0) {
            $trendIdle = $affordable;
        }
        $room = bcsub(bcsub($capacity, $trendIdle, 0), $sumCur, 0);
        if (bccomp($room, '0', 0) < 0) {
            $room = '0';
        }
        $new = $cur;
        if (bccomp($sumRaise, $room, 0) <= 0) {
            foreach ($raise as $id => $r) {
                $new[$id] = bcadd($cur[$id], $r, 0);
            }
        } elseif (bccomp($room, '0', 0) > 0) {
            // scale the raises to what fits; whole USDT, remainder to the largest raise
            $given = '0';
            $largest = null;
            foreach ($raise as $id => $r) {
                $part = bcdiv(bcmul($room, $r, 0), $sumRaise, 0);
                $new[$id] = bcadd($cur[$id], $part, 0);
                $given = bcadd($given, $part, 0);
                if ($largest === null || bccomp($r, $raise[$largest], 0) > 0) {
                    $largest = $id;
                }
            }
            $rest = bcsub($room, $given, 0);
            if ($largest !== null && bccomp($rest, '0', 0) > 0) {
                $new[$largest] = bcadd($new[$largest], $rest, 0);
            }
        }
        return ['trend' => $trendIdle, 'grids' => $new];
    }

    // ── state (adapters over the slot) ──────────────────────────────────

    /**
     * idle | active | winding_down for the run filling a trend slot.
     *
     * The state used to be DERIVED from the last marker bot_event on the run
     * — which is why a symbol with no run had no state and no verdict. It now
     * lives on fleet_slot.state, and this stays as the thin adapter every
     * existing caller keeps using unchanged: Allocator::effectiveMode,
     * FundsHold, RunLifecycle, Engine\TrendEngine::armActive, the routine
     * brief. The marker events are still written (dashboards and
     * DarkArmSweep read them); they are a journal now, not the store.
     *
     * Pool-safe: FleetSlots::stateOf reads raw rows, so a daemon sees the
     * cron's activation instead of its own boot-time value.
     */
    public static function state(int $trendRunId): string
    {
        return FleetSlots::stateOf($trendRunId);
    }

    /** Payload of the activation that opened the current episode (null when idle). */
    public static function activationPayload(int $trendRunId): ?array
    {
        return FleetSlots::activationOf($trendRunId);
    }

    /** No engine position and no working order = nothing left to manage. */
    public static function isFlat(GridRun $run): bool
    {
        $s = json_decode((string) ($run->getEngineState() ?? ''), true);
        if (is_array($s) && ($s['entry'] ?? null) !== null) {
            return false;
        }
        $open = BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterBySimulated((bool) $run->getSimulated())
            ->filterByState(['BUY_OPEN', 'SELL_OPEN', 'PartFilled'], \Criteria::IN)
            ->count();
        return $open === 0;
    }

    /** Only a Live, un-killed Trend run can be scaled. */
    private static function isScalable(GridRun $run): bool
    {
        return (string) $run->getAlgo() === 'Trend'
            && (string) $run->getStatus() === 'Live'
            && !(bool) $run->getKillSwitch();
    }

    // ── the pass ────────────────────────────────────────────────────────

    /**
     * One pass over one SLOT. The unit of work is the declared arm, not a
     * Live run: the slot is classified whether or not a run is behind it, so
     * a symbol whose arm is Halted, Done or missing still gets a journaled
     * verdict — and an alert — instead of silence.
     *
     * @param callable|null $summaries fn(string $symbol): array — MarketStore::summaries by default (tests inject)
     * @return array{slot:int, run:?int, symbol:string, algo:string, state:string, verdict:?string, decision:string, dry:bool, reasserted:bool, notes:string[]}
     */
    public static function pass(FleetSlot $slot, ?TelegramNotifier $notifier, bool $dry = false, ?callable $summaries = null): array
    {
        $symbol = (string) $slot->getSymbol();
        $state = (string) $slot->getState();
        $report = [
            'slot' => (int) $slot->getIdFleetSlot(),
            'run' => null,
            'symbol' => $symbol,
            'algo' => (string) $slot->getAlgo(),
            'state' => $state,
            'verdict' => null,
            'decision' => 'skip',
            'dry' => $dry,
            'reasserted' => false,
            'notes' => [],
        ];

        // A Grid slot is DECLARATIVE — the mechanical grid cron owns those
        // runs. Passing one here would adopt the symbol's Trend run a second
        // time: a duplicate verdict journal, a second set of counters, and
        // two writeSlice() paths racing on one arm.
        if ((string) $slot->getAlgo() !== FleetSlots::ALGO) {
            $report['notes'][] = sprintf('%s slots are declarative — only %s slots are scaled here', (string) $slot->getAlgo(), FleetSlots::ALGO);
            return $report;
        }

        $run = FleetSlots::resolveRun($slot, !$dry);
        $report['run'] = $run !== null ? (int) $run->getIdGridRun() : null;
        // resolveRun may have adopted a different run and reset the slot
        $state = (string) $slot->getState();
        $report['state'] = $state;

        // 1. Classify EVERY pass, run or no run.
        $sum = ($summaries ?? static fn (string $s): array => MarketStore::summaries($s, self::STALE_AFTER))($symbol);
        $s4 = $sum['4h'] ?? null;
        $s1d = $sum['1d'] ?? null;
        $verdict = TrendRegime::classify($s4, $s1d, $state === 'active');
        $report['verdict'] = $verdict;
        if ($verdict === null) {
            $report['notes'][] = '4h/1d summary missing or stale — pass skipped (no verdict journaled)';
            return $report;
        }
        $why = sprintf(
            '%s — 4h %s adx %s er %s chop %s; 1d %s adx %s er %s px %s ema20 %s ema50 %s%s',
            $verdict,
            $s4['trend'] ?? '?',
            self::fmt($s4['adx14'] ?? null, 1),
            self::fmt($s4['er20'] ?? null, 2),
            self::fmt($s4['chop14'] ?? null, 0),
            $s1d['trend'] ?? '?',
            self::fmt($s1d['adx14'] ?? null, 1),
            self::fmt($s1d['er20'] ?? null, 2),
            self::fmt($s1d['price'] ?? null, 2),
            self::fmt($s1d['ema20'] ?? null, 2),
            self::fmt($s1d['ema50'] ?? null, 2),
            TrendRegime::family((string) ($s1d['trend'] ?? '')) !== 'up' && TrendRegime::oneDayUp($s1d) ? ' (1d label stale: EMA20+EMA50 reclaimed)' : ''
        );

        // 2. Journal it. bot_event is a child of grid_run, so a slot with no
        //    run hangs its verdict off the fleet host run (any active one) and
        //    names the symbol in the payload — gtbot_routine_brief reads the
        //    slot, and the feed stays complete either way.
        $journalRun = $run ?? FleetSlots::hostRun();
        $log = $journalRun === null ? null : new EventLog(
            (int) $journalRun->getIdGridRun(),
            false,
            $notifier,
            [],
            [self::KIND_ACTIVATE, self::KIND_DEACTIVATE, self::KIND_RELEASE, self::KIND_REASSERT]
        );
        if ($log === null) {
            $report['notes'][] = 'no active run in the fleet — verdict computed but not journaled';
        } elseif (!$dry) {
            $log->write('Info', self::KIND_REGIME, $why, [
                'verdict' => $verdict,
                'state' => $state,
                'symbol' => $symbol,
                'slot' => $report['slot'],
                'run' => $report['run'],
            ]);
        }

        // 3. Hysteresis counters replace counting verdict events.
        $enabled = (bool) $slot->getEnabled();
        $up = $verdict === 'TREND_UP' ? (int) $slot->getConfirmUp() + 1 : 0;
        $down = $verdict === 'TREND_UP' ? 0 : (int) $slot->getConfirmDown() + 1;
        if (!$enabled) {
            $up = 0;
            $down = 0;
        }
        if (!$dry) {
            self::recordVerdict($slot, $verdict, $up, $down, self::priceFrom($sum));
        }
        if (!$enabled) {
            $report['notes'][] = 'slot is disabled — verdict journaled, nothing else touched';
            return $report;
        }

        // 4. The stand-downs come BEFORE anything that writes or alerts.
        //    A tripped floor means the activator is out of the loop entirely:
        //    it must not spawn an arm (AUTOLIVE would make a Live one while
        //    the fleet is being killed) and must not alert that every symbol
        //    is parked — DrawdownGuard::tripAll() is why they are parked.
        //
        //    recordVerdict() has ALREADY run above, deliberately: the confirm
        //    counters stay warm right through a stand-down, so an arm the
        //    routine releases re-arms on the very first pass after the floor
        //    clears instead of spending another CONFIRM_PASSES × 15 min
        //    rebuilding a streak the tape never interrupted.
        if (DrawdownGuard::check() !== null) {
            $report['notes'][] = 'drawdown floor tripped — the routine owns recovery, activator stands down';
            return $report;
        }
        // Legacy-position cores (2026-09-16): a core run that is idle but
        // still holds a position (entered before machine-scaling, e.g. prod
        // run 8's bag) must NOT be activated mid-position — the raised slice
        // would size top-up tranches into an existing bag (the refuted §10
        // top-up path). It stays at its operator slice/deploy until flat;
        // once flat the next pass scales it like any other arm.
        if ($run !== null && (string) $run->getTrendSignal() === 'EmaCross1d' && $state === 'idle' && !self::isFlat($run)) {
            $report['notes'][] = 'EmaCross1d core holds a legacy position (entered before machine-scaling) — not scaled until flat; operator slice/deploy stay';
            return $report;
        }

        // 5. No run at all: the slot fills itself and says so.
        if ($run === null) {
            self::fillEmptySlot($slot, $sum, $notifier, $report, $dry);
            return $report;
        }

        // 6. A run that cannot be scaled: parked on purpose or by accident —
        //    in a confirmed uptrend that is worth one alert per episode.
        if (!self::isScalable($run)) {
            self::reportParked($slot, $run, $verdict, $why, $notifier, $report, $dry);
            return $report;
        }
        // past here the slot HAS a run, so the journal hung off it: the
        // transitions below always have somewhere to write.
        assert($log instanceof EventLog);

        // winding_down: release as soon as the run is flat — no verdict needed for that
        if ($state === 'winding_down' && self::isFlat($run)) {
            $report['decision'] = 'release';
            if (!$dry) {
                self::release($run, $slot, $log, $report);
            } else {
                $report['notes'][] = 'would release: flat — slice back to pre-activation, grids restored';
            }
            return $report;
        }

        $decision = TrendRegime::decide($state, self::history($verdict, $up, $down));
        $report['decision'] = $decision;
        if ($decision === 'hold') {
            if ($state === 'active' && !$dry) {
                $report['reasserted'] = self::reassert($run, $slot, $log, $report);
            }
            return $report;
        }
        if ($dry) {
            $report['notes'][] = "would $decision (streak up $up / down $down)";
            return $report;
        }
        if ($decision === 'activate') {
            $state === 'winding_down'
                ? self::rearm($run, $slot, $log, $report, $why)
                : self::activate($run, $slot, $log, $report, $why);
        } elseif ($decision === 'deactivate') {
            self::deactivate($run, $slot, $log, $report, $why);
        }
        return $report;
    }

    /**
     * One slot's pass threw — say so where the operator will see it.
     *
     * The cron line is `>/dev/null 2>&1`, so the script's `fwrite(STDERR)`
     * goes nowhere: a slot whose pass raised (a summaries read that blew up,
     * a schema mismatch, a dead market row) simply stopped being scaled, in
     * silence, which is the same class of invisible-idle-capital failure the
     * whole fleet_slot work exists to end. This is the script's catch body,
     * lifted out so it can be tested without running the script.
     *
     * Throttled per SLOT rather than per run, because several slots can hang
     * their events off the same host run and one broken slot must not mute
     * the others; the per-slot identity is read back out of the payload, and
     * `Alert` separates these from the `Warn` rows reassert()/audit() write
     * under the same kind. A pass that fails while the fleet has no run at
     * all cannot be journaled (bot_event is a child of grid_run) and returns
     * null — STDERR is then genuinely all there is.
     *
     * NEVER THROWS. It is called from the per-slot catch of a loop that must
     * reach every other slot, and the very failure it reports is liable to be
     * the one that also breaks the report: EventLog::write() saves a row and
     * calls the notifier, and swallows neither. A lost connection or a
     * raising notifier would take out the alert AND every slot after this
     * one — the exact failure mode this method exists to prevent, one layer
     * up. So the whole body is guarded and degrades to STDERR, which the
     * caller has already written its own line to.
     *
     * @return string|null the message written, or null when throttled,
     *                     unwritable, or when the report itself failed
     */
    public static function reportPassFailure(FleetSlot $slot, \Throwable $e, ?TelegramNotifier $notifier = null, ?int $now = null): ?string
    {
        try {
            return self::writePassFailure($slot, $e, $notifier, $now);
        } catch (\Throwable $reportErr) {
            fwrite(STDERR, sprintf(
                "slot %d: could not journal the pass failure: %s\n",
                (int) $slot->getIdFleetSlot(),
                $reportErr->getMessage()
            ));
            return null;
        }
    }

    /** reportPassFailure()'s body — see its docblock for the guard around it. */
    private static function writePassFailure(FleetSlot $slot, \Throwable $e, ?TelegramNotifier $notifier, ?int $now): ?string
    {
        $now = $now ?? time();
        $slotId = (int) $slot->getIdFleetSlot();
        $runId = $slot->getIdGridRun() !== null ? (int) $slot->getIdGridRun() : null;
        if ($runId === null) {
            $host = FleetSlots::hostRun();
            $runId = $host === null ? null : (int) $host->getIdGridRun();
        }
        if ($runId === null) {
            return null;
        }
        $recent = BotEventQuery::create()
            ->filterByIdGridRun($runId)
            ->filterByKind(self::KIND_ERROR)
            ->filterByLevel('Alert')
            ->filterByDateCreation(['min' => date('Y-m-d H:i:s', $now - self::PASS_ERROR_EVERY)])
            ->find();
        foreach ($recent as $row) {
            $p = json_decode((string) $row->getPayload(), true);
            if (is_array($p) && (int) ($p['slot'] ?? 0) === $slotId) {
                return null;
            }
        }
        $msg = sprintf(
            '%s %s slot #%d was NOT scaled this pass — it threw: %s (%s:%d). The slot keeps its state; the next pass retries.',
            RunFactory::base((string) $slot->getSymbol()),
            (string) $slot->getAlgo(),
            $slotId,
            $e->getMessage(),
            basename($e->getFile()),
            (int) $e->getLine()
        );
        (new EventLog($runId, false, $notifier))->write('Alert', self::KIND_ERROR, $msg, [
            'symbol' => (string) $slot->getSymbol(),
            'algo' => (string) $slot->getAlgo(),
            'slot' => $slotId,
            'run' => $slot->getIdGridRun() !== null ? (int) $slot->getIdGridRun() : null,
            'error' => $e->getMessage(),
            'class' => get_class($e),
        ]);
        return $msg;
    }

    /**
     * The verdict history TrendRegime::decide() needs, rebuilt from the slot's
     * counters instead of the bot_event chain.
     *
     * decide() asks the history exactly two questions: is the NEWEST verdict
     * HOSTILE, and are the first N entries all (non-)TREND_UP. The counter IS
     * the length of the current run of like verdicts, so N copies of the
     * current verdict answers both identically — with no 15-minute-spaced
     * event scan, and with the same CONFIRM_PASSES / DEACTIVATE_PASSES rule
     * still owned by decide().
     *
     * @return string[] newest-first
     */
    private static function history(string $verdict, int $up, int $down): array
    {
        return array_fill(0, max(1, $verdict === 'TREND_UP' ? $up : $down), $verdict);
    }

    /**
     * Stamp the verdict on the slot.
     *
     * episode_started_at marks the TREND_UP spell the "parked arm" alert
     * speaks once per. Leaving TREND_UP also clears last_parked_alert_at: an
     * episode boundary must reset that voice, and a timestamp comparison
     * cannot do it on its own — two passes inside the same second (a test, or
     * two dashboard-triggered passes) would read as "already alerted".
     *
     * This same boundary is where the regime_episode LEDGER opens and closes
     * (RegimeEpisodes, 2026-09-19): the stamp says a leg is running, the
     * episode row says how much of the pool worked through it and what it
     * captured. Both hang off one transition so they can never disagree.
     *
     * The backfill runs FIRST, before either transition is handled: a slot
     * that was already inside a TREND_UP spell when the ledger shipped has
     * the stamp and no row, and binding the ledger to the entry alone would
     * keep it dark for exactly the legs it exists to measure. Adopting the
     * leg here also means a spell that ENDS on this very pass is opened and
     * then closed by the two branches below, instead of being lost.
     */
    private static function recordVerdict(FleetSlot $slot, string $verdict, int $up, int $down, ?string $price): void
    {
        RegimeEpisodes::backfill($slot, $price);
        $now = date('Y-m-d H:i:s');
        $wasUp = (string) $slot->getLastVerdict() === 'TREND_UP';
        $isUp = $verdict === 'TREND_UP';
        $slot->setLastVerdict($verdict);
        $slot->setVerdictAt($now);
        $slot->setConfirmUp($up);
        $slot->setConfirmDown($down);
        if ($isUp && !$wasUp) {
            $slot->setEpisodeStartedAt($now);
            $slot->save();
            RegimeEpisodes::open($slot, $verdict, $price, $now);
            return;
        }
        if (!$isUp && $wasUp) {
            $slot->setEpisodeStartedAt(null);
            $slot->setLastParkedAlertAt(null);
            $slot->save();
            RegimeEpisodes::close($slot, $price, $now);
            return;
        }
        $slot->save();
    }

    /**
     * An enabled slot with no run is the fleet missing an arm — the BNB hole
     * of 2026-09-15..18. Give it one (parked at MIN_SLICE, deploy 0, so it
     * costs the pool nothing) and alert, at most once a day.
     */
    private static function fillEmptySlot(FleetSlot $slot, array $sum, ?TelegramNotifier $notifier, array &$report, bool $dry): void
    {
        $symbol = (string) $slot->getSymbol();
        if ($dry) {
            $report['notes'][] = "would create a Draft trend arm for $symbol (slot is empty)";
            return;
        }
        try {
            $run = RunFactory::trendDraft($symbol, self::priceFrom($sum), $notifier);
        } catch (\Throwable $e) {
            $report['notes'][] = 'could not fill the empty slot: ' . $e->getMessage();
            return;
        }
        $id = (int) $run->getIdGridRun();
        $slot->setIdGridRun($id);
        $slot->save();
        $report['run'] = $id;

        $status = (string) $run->getStatus();
        $msg = sprintf(
            '%s trend slot had no run — created %s #%d;%s',
            RunFactory::base($symbol),
            $status,
            $id,
            $status === 'Live' ? ' GTBOT_FLEET_AUTOLIVE is on, the activator funds it on the next activation'
                : ' set it Live to let the activator fund it'
        );
        $report['notes'][] = $msg;
        $last = $slot->getLastEmptyAlertAt('U');
        if ($last !== null && time() - (int) $last < self::EMPTY_ALERT_EVERY) {
            $report['notes'][] = 'fleet_slot_empty already alerted within the day — not repeating';
            return;
        }
        (new EventLog($id, false, $notifier))->write('Alert', self::KIND_SLOT_EMPTY, $msg, [
            'symbol' => $symbol,
            'slot' => (int) $slot->getIdFleetSlot(),
            'run' => $id,
            'status' => $status,
        ]);
        $slot->setLastEmptyAlertAt(date('Y-m-d H:i:s'));
        $slot->save();
    }

    /**
     * The slot has a run but it is not Live, or it is killed. Silence used to
     * be the whole answer; in a confirmed uptrend it is the expensive one
     * (BNB, +5.7% with a hand-parked arm), so say it once per TREND_UP
     * episode. DarkArmSweep covers the "why is it parked" half.
     *
     * Two statuses are reported in the notes but never alerted, because both
     * have already been announced and neither is an accident: a Draft is the
     * arm THIS code just created and said fleet_slot_empty about, and a
     * Retiring run is a deliberate wind-down (RunLifecycle::retire) whose
     * exits are still working and whose slice is already back in the pool.
     */
    private static function reportParked(FleetSlot $slot, GridRun $run, string $verdict, string $why, ?TelegramNotifier $notifier, array &$report, bool $dry): void
    {
        $id = (int) $run->getIdGridRun();
        $status = (string) $run->getStatus();
        $killed = (bool) $run->getKillSwitch();
        $report['notes'][] = sprintf(
            'run %d is %s%s (%s) — only a Live, un-killed Trend run is scaled',
            $id,
            $status,
            $killed ? ' + kill switch on' : '',
            (string) $run->getAlgo()
        );
        if ($verdict !== 'TREND_UP' || $dry) {
            return;
        }
        if (in_array($status, self::PARK_NOT_ALERTED, true)) {
            $report['notes'][] = sprintf('%s is not an accidental park — no alert', $status);
            return;
        }
        $last = $slot->getLastParkedAlertAt('U');
        $episode = $slot->getEpisodeStartedAt('U');
        if ($last !== null && ($episode === null || (int) $last >= (int) $episode)) {
            return; // already spoke this episode
        }
        $msg = sprintf(
            '%s trend arm is parked through a confirmed uptrend: run %d is %s%s. %s',
            RunFactory::base((string) $slot->getSymbol()),
            $id,
            $status,
            $killed ? ' with the kill switch on' : '',
            $why
        );
        (new EventLog($id, false, $notifier))->write('Alert', self::KIND_ARM_PARKED, $msg, [
            'symbol' => (string) $slot->getSymbol(),
            'slot' => (int) $slot->getIdFleetSlot(),
            'run' => $id,
            'status' => $status,
            'kill_switch' => $killed,
            'verdict' => $verdict,
        ]);
        $slot->setLastParkedAlertAt(date('Y-m-d H:i:s'));
        $slot->save();
        $report['notes'][] = $msg;
    }

    /** The freshest stored price — never a stale one, which would bracket a
     *  new arm's nominal range around a price the market left days ago. */
    private static function priceFrom(array $summaries): ?string
    {
        foreach (['1h', '4h', '1d'] as $tf) {
            if (($summaries[$tf]['price'] ?? null) !== null && empty($summaries[$tf]['stale'])) {
                return (string) $summaries[$tf]['price'];
            }
        }
        return null;
    }

    /** The slot's activation payload, decoded. */
    private static function slotActivation(FleetSlot $slot): array
    {
        $p = json_decode((string) ($slot->getActivation() ?? ''), true);
        return is_array($p) ? $p : [];
    }

    /** The one place slot.state / slot.activation move. */
    private static function writeState(FleetSlot $slot, string $state, ?array $activation): void
    {
        $slot->setState($state);
        $slot->setActivation($activation === null ? null : json_encode($activation, JSON_UNESCAPED_SLASHES));
        $slot->save();
    }


    // ── transitions ─────────────────────────────────────────────────────

    /** @return array<int, GridRun> active non-Trend runs (the grids), keyed by id */
    private static function grids(): array
    {
        return Allocator::grids();
    }

    /**
     * The FULL pool minus the active runs that are neither this trend run nor
     * a grid (other Trend runs). Funding an arm is the one raise the reserve
     * does not bind (owner decision 2026-09-21): with both grids on their
     * floor the arm carried the whole reserve alone — 285 of a 350 target, a
     * fifth of the fleet's only profit engine, for every TREND_UP leg — to
     * protect a cap that a fixed pool of whole-USDT slices cannot trip. The
     * reserve still binds what packs the pool between legs: rebalance, drift
     * and a hold's spread (BudgetPool::reservePct).
     */
    private static function capacity(int $trendRunId, array $grids): string
    {
        return Allocator::capacity($trendRunId, $grids, BudgetPool::cap());
    }

    /**
     * The least a grid keeps when the trend arm is funded: its invested
     * inventory (never strand a working exit), else FLOOR_PER_LEVEL per
     * level — scaled by deploy_pct, because a grid parked at the hostile
     * cap (25%) only ever funds a quarter of its ladder; the other three
     * quarters are idle USDT the trend arm exists to put to work. A killed
     * grid funds nothing at all (inventory floor only). MIN_SLICE at least.
     *
     * A TREND ARM HAS NO LADDER, so it has no per-level term here either
     * (2026-09-21 review). nominalFloor() learned that the same morning;
     * this one did not, and it is the funding-time floor EVERY pool caller
     * actually uses — Allocator::hardFloor, planPool, shrinkToward,
     * FundsHold::release. An idle arm (Auto, because only an ACTIVE arm is
     * pinned) with the fleet's own n_levels 20 and deploy 100 therefore
     * carried a 1000 USDT floor into the pool planner: prod runs #1 and #10
     * are exactly that shape. planPool assigns floors as VALUES, so the
     * 15-minute rebalance planned to RAISE a parked arm to 1000, BudgetGuard
     * refused the write, and the whole pass rolled back — with an Alert to
     * Telegram — every 15 minutes, forever; and on a pool big enough to pay
     * it, the arm got 1000 USDT it cannot deploy while both grids starved on
     * their floor. The arm's floor is MIN_SLICE and what it is actually
     * sized to is its committed quote (derivedSlice).
     */
    public static function gridFloor(GridRun $g): string
    {
        $deploy = (bool) $g->getKillSwitch() ? 0 : max(0, min(100, (int) ($g->getDeployPct() ?? 100)));
        $levels = (string) $g->getAlgo() === 'Trend' ? self::MIN_SLICE : self::ceilDiv(
            bcmul(bcmul(self::FLOOR_PER_LEVEL, (string) max(1, (int) $g->getNLevels()), 0), (string) $deploy, 0),
            '100'
        );
        $store = new OrderStore((int) $g->getIdGridRun(), (string) $g->getRunUid(), (string) ($g->getLedgerResetAt('Y-m-d H:i:s') ?? ''), (bool) $g->getSimulated());
        $invested = self::ceilDiv($store->investedQuote(), '1');
        $floor = bccomp($levels, $invested, 0) > 0 ? $levels : $invested;
        return bccomp($floor, self::MIN_SLICE, 0) > 0 ? $floor : self::MIN_SLICE;
    }

    /**
     * A run's NOMINAL viability floor — the number gtbot_routine_brief
     * reports as floor_slice.
     *
     * A GRID's is FLOOR_PER_LEVEL per ladder level: every level it funds has
     * to clear the exchange's min-notional or the ladder cannot trade.
     *
     * A TREND ARM HAS NO LADDER, so per-level is meaningless for it and
     * n_levels on a Trend row is an artefact of the shared schema — one it
     * never trades on. The fleet's own auto-created arm (RunFactory::
     * trendDraft) carries n_levels 20, which made this floor 1000 USDT: on
     * 2026-09-21 prod run 10 held ~347 against a 350 target and the re-assert
     * asked the pool for 1000 every 15 minutes, refused every time. Its floor
     * is MIN_SLICE — the least a run may be sliced at all — and what a
     * holding arm is actually sized to is its committed quote (derivedSlice).
     *
     * gridFloor() is the funding-time variant for a GRID: same per-level
     * base, scaled by deploy_pct and raised to the inventory the ladder
     * already holds.
     */
    public static function nominalFloor(GridRun $run): string
    {
        if ((string) $run->getAlgo() === 'Trend') {
            return self::MIN_SLICE;
        }
        return bcmul(self::FLOOR_PER_LEVEL, (string) max(1, (int) $run->getNLevels()), 0);
    }

    /**
     * The floor under a HOLDING arm's derived slice, never above the slot's
     * own target: the target is the most the fleet has declared this arm may
     * hold, so a floor above it would re-assert a slice the slot never asked
     * for — which is how a 1000 USDT floor reached a 350 target arm.
     */
    private static function holdingFloor(GridRun $trend, FleetSlot $slot): string
    {
        $floor = self::nominalFloor($trend);
        $target = self::slotTarget($slot);
        return bccomp($floor, $target, 0) > 0 ? $target : $floor;
    }

    /**
     * The slice the slot declares for its arm, else the fleet-wide target.
     *
     * A stored 0 means "follow gtbot_trend_target_slice" — that is what
     * FleetSlots::reconcileFromRuns writes for a new slot, so the config knob
     * stays live. A non-zero value is an operator decision for one symbol and
     * pins it (prod's pre-existing 350 rows are exactly that).
     */
    public static function effectiveTarget(FleetSlot $slot): string
    {
        return self::slotTarget($slot);
    }

    private static function slotTarget(FleetSlot $slot): string
    {
        $declared = $slot->getTargetSlice();
        return $declared !== null && bccomp((string) $declared, '0', self::SCALE) > 0
            ? self::whole((string) $declared)
            : self::targetSlice();
    }

    /**
     * Plan funding the trend arm up to $target: headroom first, then the
     * grids pro-rata above their floors, never raising one.
     *
     * Split from applyFunding() so a caller can see what the pool can
     * actually give BEFORE it decides there is anything to write — an
     * unreachable target must be silent, not an event every 15 minutes.
     *
     * @return array{runs: array<int, GridRun>, before: array<int, string>, trend: string, grids: array<int|string, string>}
     */
    private static function fundingPlan(GridRun $trend, string $target): array
    {
        $grids = self::grids();
        $input = [];
        $before = [];
        foreach ($grids as $id => $g) {
            $before[$id] = self::whole((string) $g->getBudgetQuote());
            $input[$id] = ['budget' => (string) $g->getBudgetQuote(), 'floor' => self::gridFloor($g)];
        }
        $runId = (int) $trend->getIdGridRun();
        $plan = self::planActivate(
            self::capacity($runId, $grids),
            (string) $trend->getBudgetQuote(),
            $input,
            $target,
            // what of the headroom is NOT the reserve, so the grids are only
            // asked for what the free pool could not cover (round 3)
            Allocator::capacity($runId, $grids, BudgetPool::allocatable())
        );
        return ['runs' => $grids, 'before' => $before, 'trend' => $plan['trend'], 'grids' => $plan['grids']];
    }

    /**
     * Apply a funding plan: the grid decreases FIRST, so no intermediate
     * state ever sums above the shared budget and BudgetGuard never refuses
     * the raise it is busy making room for. The one funding path — an
     * activation opens an episode with it, a re-assert re-derives the slice
     * with it.
     *
     * $touched is filled AS THE WRITES LAND, by reference, because the raise
     * can still be refused after the trims are committed: Allocator::grids()
     * is hydrated (pooled) while BudgetGuard reads raw rows, so a slice
     * another process moved between this pass's read and its write makes the
     * plan too big. The caller then knows exactly which grids are already
     * trimmed in the database and must be Reloaded, instead of leaving their
     * daemons spending against a slice that no longer exists.
     *
     * @param array{runs: array<int, GridRun>, before: array<int, string>, trend: string, grids: array<int|string, string>} $plan
     * @param int[] $touched out: the runs that moved, for the Reload fan-out
     */
    private static function applyFunding(GridRun $trend, array $plan, string $what, array &$touched): void
    {
        foreach ($plan['grids'] as $id => $new) {
            if (bccomp($new, $plan['before'][$id], 0) === 0) {
                continue;
            }
            self::writeSlice($plan['runs'][$id], $new, null, $what . ' — grid slice trimmed to fund the trend arm');
            $touched[] = (int) $id;
        }
        self::writeSlice($trend, $plan['trend'], self::ACTIVE_DEPLOY_PCT, $what . ' — slice + deploy raised');
        $touched[] = (int) $trend->getIdGridRun();
    }

    /**
     * Run slice writes that BudgetGuard is allowed to refuse, and leave the
     * fleet in a state the next pass can retry from.
     *
     * Every write path here is multi-step (trim the grids, then raise the
     * arm) and only the LAST step can be refused — Allocator::grids() is
     * hydrated (pooled) while BudgetGuard reads raw rows, so a slice another
     * process moved between this pass's read and its write makes the plan too
     * big. Two things must then happen, and before this helper only
     * reassert() did either of them: the trims that ARE committed have to be
     * Reloaded (their daemons would otherwise keep spending a slice that no
     * longer exists), and the refusal has to be journaled instead of escaping
     * as an uncaught RuntimeException that kills the whole slot's pass.
     *
     * The slot's state and counters are deliberately NOT touched by the
     * caller on a refusal: the decision stands, and the next pass makes it
     * again against a pool that may by then have room.
     *
     * @param callable(array &$touched): void $write appends run ids AS THEY LAND
     * @return int[]|null the runs that moved, or null when the write was refused
     */
    private static function tryWrites(EventLog $log, array &$report, string $what, string $reloadNote, callable $write): ?array
    {
        $touched = [];
        try {
            $write($touched);
        } catch (\RuntimeException $e) {
            // The pool is too tight for the write: a held arm asking for more
            // headroom than there is, someone else having grown a grid, or the
            // shared budget lowered under the committed slices. Say so and try
            // again next pass — but first Reload whatever DID land.
            if ($touched !== []) {
                self::reloadAll($touched, $reloadNote);
            }
            $log->write('Warn', self::KIND_ERROR, 'could not ' . $what . ': ' . $e->getMessage());
            $report['notes'][] = $e->getMessage();
            return null;
        }
        return $touched;
    }

    private static function activate(GridRun $trend, FleetSlot $slot, EventLog $log, array &$report, string $why): void
    {
        $trendBefore = self::whole((string) $trend->getBudgetQuote());
        $deployBefore = (int) ($trend->getDeployPct() ?? 100);
        // the SLOT's target, not the fleet-wide knob (2026-09-21, round 3):
        // every other path (reassert, rearm, derivedSlice, the brief, the
        // episode ledger) reads slotTarget(), so an operator who pinned one
        // symbol to 200 had it activated at 350 — funded off the grids, and
        // entered at that size on the very next tick.
        $plan = self::fundingPlan($trend, self::slotTarget($slot));
        $payload = [
            'trend_before' => $trendBefore,
            'deploy_before' => $deployBefore,
            'grids_before' => $plan['before'],
            'trend_after' => $plan['trend'],
            'deploy_after' => self::ACTIVE_DEPLOY_PCT,
            'grids_after' => $plan['grids'],
            'why' => $why,
        ];
        $touched = self::tryWrites(
            $log,
            $report,
            'activate the trend arm',
            'trend activation — grid trim landed before the raise was refused',
            static function (array &$touched) use ($trend, $plan): void {
                self::applyFunding($trend, $plan, 'trend activation', $touched);
            }
        );
        if ($touched === null) {
            // slot state, counters and the activation payload are untouched:
            // the decision is still 'activate' next pass.
            return;
        }
        $msg = sprintf(
            'TREND_UP confirmed — trend arm ACTIVATED: slice %s → %s, deploy %d%% → %d%%; grids %s. %s [%s]',
            $payload['trend_before'],
            $plan['trend'],
            $payload['deploy_before'],
            self::ACTIVE_DEPLOY_PCT,
            self::describeGrids($payload['grids_before'], $payload['grids_after']),
            self::entrySentence($trend),
            $why
        );
        $log->write('Info', self::KIND_ACTIVATE, $msg, $payload);
        self::writeState($slot, 'active', $payload);
        self::reloadAll($touched, 'trend activation — re-read slice/deploy');
        $report['state'] = 'active';
        $report['notes'][] = $msg;
        self::audit($log);
    }

    /**
     * winding_down → active again, on a confirmed TREND_UP before the arm
     * went flat (release is what a flat arm gets).
     *
     * It therefore re-arms an arm that is BY DEFINITION holding, and the
     * slice it writes is DERIVED (2026-09-19, review fix) rather than
     * replayed from activation.trend_after: that payload records the moment
     * the episode opened, so replaying it re-pinned up to target − committed
     * on a breakout arm that cannot spend the difference until it is flat —
     * the same defect derivedSlice() was introduced to remove from
     * reassert(), for one pass, on the tightest pool of the cycle.
     *
     * A bare write, not the funding planner: the raise (if any) is for an arm
     * mid-position, which is exactly the case that may not take capital off a
     * working grid. A refusal is a warn and a retry on the next pass, with
     * the slot left in winding_down so decide() says 'activate' again.
     */
    private static function rearm(GridRun $trend, FleetSlot $slot, EventLog $log, array &$report, string $why): void
    {
        $p = self::slotActivation($slot);
        $derived = self::derivedSlice($trend, $slot);
        $slice = $derived['slice'];
        $touched = self::tryWrites(
            $log,
            $report,
            're-arm the trend arm',
            'trend re-arm — the write was refused',
            static function (array &$touched) use ($trend, $slice): void {
                self::writeSlice($trend, $slice, self::ACTIVE_DEPLOY_PCT, 'trend re-armed — deploy raised again');
                $touched[] = (int) $trend->getIdGridRun();
            }
        );
        if ($touched === null) {
            return;
        }
        // the new episode carries the ORIGINAL pre-activation values forward
        $payload = array_merge($p, ['why' => $why, 'rearmed' => true, 'trend_after' => $slice, 'rule' => $derived['rule']]);
        $msg = sprintf(
            'TREND_UP confirmed again while winding down — trend arm RE-ARMED at slice %s, deploy %d%%: %s [%s]',
            $slice,
            self::ACTIVE_DEPLOY_PCT,
            $derived['why'],
            $why
        );
        $log->write('Info', self::KIND_ACTIVATE, $msg, $payload);
        self::writeState($slot, 'active', $payload);
        self::reloadAll($touched, 'trend re-arm — re-read slice/deploy');
        $report['state'] = 'active';
        $report['notes'][] = $msg;
    }

    private static function deactivate(GridRun $trend, FleetSlot $slot, EventLog $log, array &$report, string $why): void
    {
        self::writeSlice($trend, null, 0, 'trend deactivation — entries off, stop management continues');
        $msg = sprintf('trend regime ended — trend arm DEACTIVATED: deploy → 0%% (stop keeps working; slice released once flat) [%s]', $why);
        $log->write('Info', self::KIND_DEACTIVATE, $msg, ['why' => $why]);
        // the slice stays parked, so the activation payload stays too: release
        // needs its pre-activation values, and a re-arm reuses them
        self::writeState($slot, 'winding_down', self::slotActivation($slot) ?: null);
        self::reloadAll([(int) $trend->getIdGridRun()], 'trend deactivation — re-read deploy');
        $report['state'] = 'winding_down';
        $report['notes'][] = $msg;
    }

    private static function release(GridRun $trend, FleetSlot $slot, EventLog $log, array &$report): void
    {
        $p = self::slotActivation($slot);
        $grids = self::grids();
        $before = $p['grids_before'] ?? [];
        $input = [];
        foreach ($grids as $id => $g) {
            $input[$id] = ['budget' => (string) $g->getBudgetQuote(), 'before' => (string) ($before[$id] ?? $g->getBudgetQuote())];
        }
        $trendIdle = (string) ($p['trend_before'] ?? self::whole((string) $trend->getBudgetQuote()));
        $deployIdle = (int) ($p['deploy_before'] ?? 0);
        $plan = self::planRelease(self::capacity((int) $trend->getIdGridRun(), $grids), (string) $trend->getBudgetQuote(), $trendIdle, $input);
        $touched = [];
        $trendNow = self::whole((string) $trend->getBudgetQuote());
        // decrease first (trend), then the grid raises
        self::writeSlice($trend, $plan['trend'], $deployIdle, 'trend release — back to idle slice/deploy');
        $touched[] = (int) $trend->getIdGridRun();
        foreach ($plan['grids'] as $id => $new) {
            if (bccomp($new, self::whole((string) $grids[$id]->getBudgetQuote()), 0) === 0) {
                continue;
            }
            self::writeSlice($grids[$id], $new, null, 'trend release — grid slice restored');
            $touched[] = (int) $id;
        }
        $after = $plan['grids'];
        $msg = sprintf(
            'trend arm flat — RELEASED: slice %s → %s, deploy → %d%%; grids %s',
            $trendNow,
            $plan['trend'],
            $deployIdle,
            self::describeGrids(array_map(static fn ($g) => self::whole((string) $g->getBudgetQuote()), $grids), $after)
        );
        $log->write('Info', self::KIND_RELEASE, $msg, ['trend_after' => $plan['trend'], 'deploy_after' => $deployIdle, 'grids_after' => $after]);
        // the episode is over: an idle slot carries no activation payload
        self::writeState($slot, 'idle', null);
        self::reloadAll($touched, 'trend release — re-read slice/deploy');
        $report['state'] = 'idle';
        $report['notes'][] = $msg;
        self::audit($log);
    }

    /**
     * The slice an ACTIVE arm should be carrying right now, and the sentence
     * that explains it.
     *
     * Three cases, because the arms do not all deploy the same way:
     *
     *   flat                     → the slot's target slice
     *   EmaCross1d core, holding → the slot's target slice
     *   breakout arm, holding    → max(ceil(committed), holdingFloor)
     *
     * A BREAKOUT arm takes no further entry until it is flat, and the
     * one-shot top-up at activation (variant H) is REFUTED on both bear
     * tapes (refit-evidence §12) — so every USDT of its slice above what the
     * position ties up is unreachable: pinned, idle, and charged to the
     * shared pool all the same. Prod run 1 on 2026-09-19 held 210.67 on a
     * 422 slice, with 172 doing nothing for as long as the position lasted.
     * Sizing DOWN to the position is therefore always right.
     *
     * An EmaCross1d CORE is the exception, and it is not a top-up in the
     * refuted sense: staggered tranches into an open position are its
     * designed deployment (TrendEngine::tryAddCoreTranche runs while
     * holding). Derive it down to its filled part and the next pass re-splits
     * a shrunken budget, so it keeps the target rule until the episode ends.
     *
     * COMMITTED is not just filled inventory: a breakout arm places its whole
     * tranche batch in ONE tick, so at a 15-minute pass part of it is
     * routinely still resting on the book. Sizing to the filled part alone
     * would let the rest fill past the asserted slice and understate exposure
     * against the never-overcommit invariant. It is therefore
     * investedQuote + the unfilled remainder of every working BUY row +
     * legacyReserveQuote.
     *
     * The floor keeps a held arm from being sized below the least a run may
     * be sliced at all when its position is small — MIN_SLICE, capped by the
     * slot's target (holdingFloor). It is deliberately NOT per ladder level:
     * the arm has no ladder, and reading n_levels off a Trend row made the
     * floor 1000 for an arm the slot targets at 350 (prod 2026-09-21).
     *
     * Public because RegimeEpisodes asks it the same question from the other
     * side — "is this arm sized to a position smaller than its slice?" is
     * the `rule === holding` branch, and the idle-pool alert must name that
     * cause with the same number the re-assert writes, not a second copy of
     * the rule.
     *
     * @return array{slice: string, rule: string, why: string, invested: ?string, floor: ?string, target: ?string}
     */
    public static function derivedSlice(GridRun $trend, FleetSlot $slot): array
    {
        $core = (string) $trend->getTrendSignal() === 'EmaCross1d';
        $holding = bccomp(self::positionQty((int) $trend->getIdGridRun()), '0', self::SCALE) > 0;
        if ($core || !$holding) {
            $target = self::slotTarget($slot);
            return [
                'slice' => $target,
                'rule' => $holding ? 'core' : 'flat',
                'why' => $holding
                    ? sprintf('EmaCross1d core holding — its staggered tranches ARE its deployment, so the slot\'s target slice %s stands', $target)
                    : sprintf('flat — the slot\'s target slice %s is what the next entry may spend', $target),
                'invested' => null,
                'floor' => null,
                'target' => $target,
            ];
        }
        $store = RunLifecycle::store($trend);
        $invested = $store->investedQuote();
        $working = $store->openBuyReserveQuote();
        $legacy = $store->legacyReserveQuote();
        $committed = self::ceilDiv(bcadd(bcadd($invested, $working, self::SCALE), $legacy, self::SCALE), '1');
        $floor = self::holdingFloor($trend, $slot);
        return [
            'slice' => bccomp($committed, $floor, 0) > 0 ? $committed : $floor,
            'rule' => 'holding',
            'why' => sprintf(
                'holding %s of committed quote (invested %s + working buys %s + legacy reserve %s), floor %s — this arm takes no further entry until it is flat, so the slice follows the position',
                $committed,
                self::fmt($invested, 2),
                self::fmt($working, 2),
                self::fmt($legacy, 2),
                $floor
            ),
            'invested' => $committed,
            'floor' => $floor,
            'target' => null,
        ];
    }

    /**
     * The open trend position's qty, read past Propel's instance pool.
     *
     * select() returns raw columns for the same reason BudgetGuard::check
     * does: Propel 1 never re-hydrates a pooled object, and the daemon
     * rewrites engine_state on every fill. Sizing off a hydrated read is
     * sizing off a position that may have been closed since this process
     * first touched the row. '0' when there is no position.
     */
    private static function positionQty(int $runId): string
    {
        $row = GridRunQuery::create()
            ->filterByIdGridRun($runId)
            ->select(['IdGridRun', 'EngineState'])
            ->findOne();
        $state = is_array($row) ? json_decode((string) ($row['EngineState'] ?? ''), true) : null;
        $qty = is_array($state) ? ($state['qty'] ?? null) : null;
        return is_numeric($qty) ? (string) $qty : '0';
    }

    /**
     * While active, the activator OWNS the arm's slice and deploy: whatever
     * someone else moved (the hourly routine running an older prompt) is put
     * back. Since 2026-09-19 what it puts back is DERIVED every pass
     * (derivedSlice) instead of replayed from the activation payload — the
     * payload describes the moment the episode opened, and a breakout arm
     * that has since taken a position cannot spend the difference.
     *
     * A DECREASE writes the trend run alone: no grid has to give anything
     * up, and the freed amount becomes pool headroom that gtbot-allocate
     * distributes on its own pass.
     *
     * An INCREASE depends on which rule produced the number, because only
     * one of them describes an arm that can still trade the money:
     *
     *   target rule (flat arm, EmaCross1d core) → the activation's funding
     *      path, headroom first and then the grids above their floors. The
     *      arm will deploy it, so a grid may fund it.
     *   holding rule (breakout arm mid-position) → a bare write funded from
     *      pool headroom alone, NO planner and NO grid write. Its entries are
     *      gated until it is flat, so trimming a working grid for it would be
     *      the pinned-capital defect this change exists to remove, in
     *      miniature. Refused for want of headroom = warn, retry next pass.
     *
     * Planning before deciding also keeps an unreachable target silent: it
     * writes nothing and says nothing, rather than journaling the same wish
     * every 15 minutes.
     *
     * Whole-USDT throughout: a difference under 1 USDT is not worth a write
     * and the Reload that follows it.
     *
     * Returns true when it had to write.
     */
    private static function reassert(GridRun $trend, FleetSlot $slot, EventLog $log, array &$report): bool
    {
        $derived = self::derivedSlice($trend, $slot);
        $curSlice = self::whole((string) $trend->getBudgetQuote());
        $curDeploy = (int) ($trend->getDeployPct() ?? 100);
        $deployOk = $curDeploy === self::ACTIVE_DEPLOY_PCT;

        $slice = $derived['slice'];
        $funding = null;
        if (bccomp($slice, $curSlice, 0) > 0) {
            if ($derived['rule'] !== 'holding') {
                $funding = self::fundingPlan($trend, $slice);
                $slice = $funding['trend'];
                if (bccomp($slice, $curSlice, 0) === 0) {
                    $funding = null;   // the pool has nothing to give: leave it be
                }
            } else {
                // The holding rule may take from POOL HEADROOM ALONE (no
                // planner, no grid write) — so plan it against the headroom
                // exactly as fundingPlan plans the target rule, instead of
                // writing a number BudgetGuard is certain to refuse. Prod
                // 2026-09-21: an arm whose floor exceeded the whole pool
                // journaled "could not re-assert the trend slice ... refused"
                // every 15 minutes for hours. An unreachable target must be
                // silent (fundingPlan's contract); a refusal is then what it
                // was meant to be — a genuine race with another writer.
                $reachable = bcadd($curSlice, Allocator::headroom(BudgetPool::cap()), 0);
                if (bccomp($slice, $reachable, 0) > 0) {
                    $slice = $reachable;
                }
            }
        }
        $sliceOk = bccomp($slice, $curSlice, 0) === 0;
        if ($sliceOk && $deployOk) {
            return false;
        }
        $touched = self::tryWrites(
            $log,
            $report,
            're-assert the trend slice',
            'trend re-assert — grid trim landed before the raise was refused',
            static function (array &$touched) use ($trend, $funding, $slice, $sliceOk, $deployOk): void {
                if ($funding !== null) {
                    self::applyFunding($trend, $funding, 'trend re-assert', $touched);
                    return;
                }
                self::writeSlice($trend, $sliceOk ? null : $slice, $deployOk ? null : self::ACTIVE_DEPLOY_PCT, 'trend re-assert — activator owns this run while active');
                $touched[] = (int) $trend->getIdGridRun();
            }
        );
        if ($touched === null) {
            return false;
        }
        $grids = $funding !== null && count($touched) > 1
            ? '; grids ' . self::describeGrids($funding['before'], $funding['grids'])
            : '';
        $msg = sprintf(
            'trend arm is machine-managed while active — re-asserted slice %s (was %s), deploy %d%% (was %d%%)%s: %s',
            $slice,
            $curSlice,
            self::ACTIVE_DEPLOY_PCT,
            $curDeploy,
            $grids,
            $derived['why']
        );
        $log->write('Info', self::KIND_REASSERT, $msg, [
            'slice' => $slice,
            'slice_was' => $curSlice,
            'deploy_was' => $curDeploy,
            'rule' => $derived['rule'],
            'invested' => $derived['invested'],
            'floor' => $derived['floor'],
            'target' => $derived['target'],
            'grids_after' => $funding !== null ? $funding['grids'] : null,
        ]);
        self::reloadAll($touched, 'trend re-assert — re-read slice/deploy');
        if ($funding !== null) {
            self::audit($log);
        }
        $report['notes'][] = $msg;
        return true;
    }

    /** The slice an activation targets: config gtbot_trend_target_slice, else TARGET_SLICE. */
    public static function targetSlice(): string
    {
        $v = ConfigQuery::create()->findOneByConfig(self::CONFIG_TARGET_SLICE)?->getValue();
        return $v !== null && is_numeric($v) && (float) $v >= (float) self::MIN_SLICE ? bcadd((string) $v, '0', 0) : self::TARGET_SLICE;
    }

    // ── writes ──────────────────────────────────────────────────────────

    /** BudgetGuard-checked slice/deploy write. @throws \RuntimeException on overcommit */
    private static function writeSlice(GridRun $run, ?string $budget, ?int $deployPct, string $note): void
    {
        Allocator::writeSlice($run, $budget, $deployPct);
    }

    private static function reloadAll(array $runIds, string $note): void
    {
        Allocator::reload($runIds, $note);
    }

    private static function audit(EventLog $log): void
    {
        Allocator::audit($log, self::KIND_ERROR);
    }

    // ── helpers ─────────────────────────────────────────────────────────

    private static function describeGrids(array $before, array $after): string
    {
        return Allocator::describe($before, $after);
    }

    /** How the activated engine takes entries, per its trend_signal. */
    private static function entrySentence(GridRun $trend): string
    {
        if ((string) $trend->getTrendSignal() === 'EmaCross1d') {
            return 'The inventory core enters on its own 1d EMA20 > EMA50 cross; the raised deploy sizes its staggered tranches.';
        }
        return 'Engine enters on its next Donchian breakout, or at once while price holds above the 1h EMA20 > EMA50 (regime entry).';
    }

    private static function whole(string $v): string
    {
        return bcadd($v, '0', 0);
    }

    private static function ceilDiv(string $a, string $b): string
    {
        $q = bcdiv($a, $b, 0);
        return bccomp(bcmul($q, $b, self::SCALE), $a, self::SCALE) < 0 ? bcadd($q, '1', 0) : $q;
    }

    private static function fmt($v, int $dec): string
    {
        return $v === null ? '?' : number_format((float) $v, $dec, '.', '');
    }
}
