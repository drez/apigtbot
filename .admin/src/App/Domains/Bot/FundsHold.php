<?php

namespace App\Domains\Bot;

use App\BotCommand;
use App\BotEventQuery;
use App\GridRun;
use App\GridRunQuery;

/**
 * Dashboard "Hold funds" / "Release funds" — the whole shared budget stays
 * deployed on both sides of a hold.
 *
 * hold()    active run → CancelBuys + Reload enqueued (open buys canceled,
 *           daemon exits cleanly and the watchdog won't respawn a Halted
 *           run), then status → Halted. The pool's idle headroom (the freed
 *           slice plus whatever was already unallocated) is then spread
 *           pro-rata over the remaining grid runs, each Reloaded so it
 *           re-reads its slice. The Trend arm is never raised here: the
 *           activator owns its slice (it re-asserts it while active) and
 *           takes what it needs from headroom/grids on its own activation.
 *           The funds_hold marker records the pre-hold status and every
 *           grid's pre-hold slice so release() can take the raise back.
 *           Inventory and working sells are left alone (same posture as
 *           Flatten's leftovers).
 * release() Halted run → its slice comes back from the pool's headroom
 *           first, then from the grids this hold raised — pro-rata, never
 *           below their pre-hold slice or viability floor (same planner as
 *           the trend activator). Grids the hold didn't raise are not
 *           touched. What fits is what the run gets (a smaller slice than
 *           before is reported); under MIN_SLICE the release is refused.
 *           Decreases land before the increase, every write BudgetGuard-
 *           checked. Kill switch cleared, status restored; the watchdog
 *           respawns the daemon within a minute.
 */
final class FundsHold
{
    /** marker event: payload carries the pre-hold status + grid slices for release() */
    public const KIND_HOLD = 'funds_hold';
    public const KIND_RELEASE = 'funds_release';
    public const MIN_SLICE = TrendActivator::MIN_SLICE;

    private const SCALE = 8;

    // ── pure planner ────────────────────────────────────────────────────

    /**
     * Spread $room over the slices pro-rata, whole USDT, the rounding
     * remainder to the largest slice. Returns the full new map.
     *
     * @param array<int|string, string> $slices id => current slice
     * @return array<int|string, string>
     */
    public static function planSpread(string $room, array $slices): array
    {
        $room = bcadd($room, '0', 0);
        $new = [];
        $sum = '0';
        foreach ($slices as $id => $v) {
            $new[$id] = bcadd($v, '0', 0);
            $sum = bcadd($sum, $new[$id], 0);
        }
        if ($new === [] || bccomp($room, '0', 0) <= 0) {
            return $new;
        }
        $cur = $new;
        $given = '0';
        $largest = null;
        foreach ($cur as $id => $c) {
            $part = bccomp($sum, '0', 0) > 0 ? bcdiv(bcmul($room, $c, 0), $sum, 0) : '0';
            $new[$id] = bcadd($c, $part, 0);
            $given = bcadd($given, $part, 0);
            if ($largest === null || bccomp($c, $cur[$largest], 0) > 0) {
                $largest = $id;
            }
        }
        $rest = bcsub($room, $given, 0);
        if ($largest !== null && bccomp($rest, '0', 0) > 0) {
            $new[$largest] = bcadd($new[$largest], $rest, 0);
        }
        return $new;
    }

    // ── transitions ─────────────────────────────────────────────────────

    /**
     * Run an operator slice action under the allocator's advisory lock.
     *
     * SERIALISE AGAINST THE CRONS (2026-09-21, round 2). hold() and release()
     * re-plan the whole pool from hydrated rows while BudgetGuard reads raw
     * ones, so a dashboard button pressed inside a gtbot-allocate or
     * gtbot-trend-activate pass plans against slices that are already moving.
     *
     * NON-BLOCKING BY DESIGN: this is reached from a web request and from MCP
     * tools, so it waits two seconds and then says "busy, try again" rather
     * than holding a request open behind a cron pass. The lock is released in
     * a finally, including when the action throws.
     *
     * @param callable(): array{ok: bool, message: string} $fn
     * @return array{ok: bool, message: string}
     */
    private static function serialised(callable $fn): array
    {
        $con = \Propel::getConnection();
        $get = $con->prepare('SELECT GET_LOCK(?, 2)');
        $get->execute([Allocator::LOCK]);
        if ((int) $get->fetchColumn() !== 1) {
            return ['ok' => false, 'message' => 'the allocator is mid-pass and owns the slices right now — try again in a moment'];
        }
        try {
            return $fn();
        } finally {
            $con->prepare('SELECT RELEASE_LOCK(?)')->execute([Allocator::LOCK]);
        }
    }

    /** @return array{ok: bool, message: string} */
    public static function hold(GridRun $run): array
    {
        return self::serialised(static fn (): array => self::doHold($run));
    }

    /** @return array{ok: bool, message: string} */
    public static function release(GridRun $run): array
    {
        return self::serialised(static fn (): array => self::doRelease($run));
    }

    /** @return array{ok: bool, message: string} */
    private static function doHold(GridRun $run): array
    {
        $status = (string) $run->getStatus();
        $runId = (int) $run->getIdGridRun();
        if (!in_array($status, BudgetGuard::ACTIVE_STATUSES, true)) {
            return ['ok' => false, 'message' => sprintf('run is %s — only an active run can be put on hold', $status)];
        }
        foreach (['CancelBuys', 'Reload'] as $command) {
            self::enqueue($runId, $command, 'dashboard hold funds');
        }
        $run->setStatus('Halted');
        $run->save();

        // spread the pool's idle headroom (freed slice + anything already unallocated) over the grids
        $grids = self::grids($runId);
        $before = [];
        foreach ($grids as $id => $g) {
            $before[$id] = bcadd((string) $g->getBudgetQuote(), '0', 0);
        }
        $plan = self::planSpread(self::headroom(), $before);
        foreach ($plan as $id => $new) {
            if (bccomp($new, $before[$id], 0) !== 0) {
                self::writeSlice($grids[$id], $new);
                self::enqueue($id, 'Reload', 'funds hold — re-read slice');
            }
        }
        $spread = $grids === []
            ? 'no grid run to take the slice — it stays idle in the pool'
            : sprintf('grids %s', self::describe($before, $plan));
        $log = new EventLog($runId, false);
        $log->write('Info', self::KIND_HOLD, sprintf(
            'funds on hold via dashboard — %s freed from the shared pool (was %s); %s',
            (string) $run->getBudgetQuote(),
            $status,
            $spread
        ), ['status_before' => $status, 'grids_before' => $before, 'grids_after' => $plan]);
        self::audit($log);
        return ['ok' => true, 'message' => sprintf('run %d on hold — %s freed from the shared pool; %s', $runId, (string) $run->getBudgetQuote(), $spread)];
    }

    /** @return array{ok: bool, message: string} */
    private static function doRelease(GridRun $run): array
    {
        if ((string) $run->getStatus() !== 'Halted') {
            return ['ok' => false, 'message' => sprintf('run is %s — only a Halted run can be released', (string) $run->getStatus())];
        }
        $runId = (int) $run->getIdGridRun();
        $marker = self::marker($run);
        $restore = $marker['status_before'] ?? 'Live';
        $want = bcadd((string) $run->getBudgetQuote(), '0', 0);

        // take the slice back: headroom first, then the grids this hold raised, down to their pre-hold slice / floor
        $grids = self::grids($runId);
        $before = [];
        $input = [];
        foreach ($grids as $id => $g) {
            $before[$id] = bcadd((string) $g->getBudgetQuote(), '0', 0);
            $preHold = isset($marker['grids_before'][$id]) ? bcadd((string) $marker['grids_before'][$id], '0', 0) : $before[$id];
            $floor = TrendActivator::gridFloor($g);
            $input[$id] = ['budget' => $before[$id], 'floor' => bccomp($preHold, $floor, 0) > 0 ? $preHold : $floor];
        }
        // a pool that shrank below the grids' sum meanwhile: the grids first cover that deficit, the run gets the rest
        $capacity = self::capacity($runId, $grids);
        $deficit = bcsub(array_reduce($before, static fn (string $c, string $v) => bcadd($c, $v, 0), '0'), $capacity, 0);
        $deficit = bccomp($deficit, '0', 0) > 0 ? $deficit : '0';
        $plan = TrendActivator::planActivate($capacity, '0', $input, bcadd($want, $deficit, 0));
        $got = bcsub($plan['trend'], $deficit, 0);
        $got = bccomp($got, '0', 0) > 0 ? $got : '0';
        if (bccomp($got, self::MIN_SLICE, 0) < 0) {
            // SAY WHAT IS TRUE (2026-09-21 review). This used to fall back to
            // a FABRICATED BudgetGuard payload when check() came back null —
            // "slices would sum to 1300 but the shared budget is 1300 (excess
            // 100)", which is not an overcommit, not an excess, and sends the
            // operator to lower a run that is not the problem. An overcommit
            // is reported only when there IS one; otherwise the honest
            // reason is that the grids' floors and committed inventory hold
            // the capital, and what the pool could free was too small a slice
            // to run at all.
            $over = BudgetGuard::check($runId, $want, $restore);
            $why = $over !== null
                ? BudgetGuard::message($over)
                : sprintf(
                    'The pool is not overcommitted — %s of the %s cap is committed to the other runs\' floors and inventory, which this release may not take. Lower another run\'s budget (or free its inventory) first.',
                    BudgetPool::fmt(self::activeSum()),
                    BudgetPool::fmt(BudgetPool::cap())
                );
            return ['ok' => false, 'message' => sprintf('only %s can be freed for run %d (min %s). %s', $got, $runId, self::MIN_SLICE, $why)];
        }

        // decreases before the increase
        foreach ($plan['grids'] as $id => $new) {
            if (bccomp($new, $before[$id], 0) !== 0) {
                self::writeSlice($grids[$id], $new);
                self::enqueue($id, 'Reload', 'funds release — re-read slice');
            }
        }
        $over = BudgetGuard::check($runId, $got, $restore);
        if ($over !== null) {
            return ['ok' => false, 'message' => BudgetGuard::message($over)];
        }
        $run->setKillSwitch(false);
        $run->setStatus($restore);
        $run->setBudgetQuote($got);
        $run->save();

        // "or held back" is not hedging: since 2026-09-21 the planner sizes
        // against the ALLOCATABLE pool (BudgetPool::reservePct), so a release
        // that comes up short may be short because of the reserve rather than
        // because another run has the capital committed. Saying only the
        // latter would send the operator hunting for inventory that isn't there.
        $slice = bccomp($got, $want, 0) === 0 ? $got : sprintf('%s of %s (the rest is committed elsewhere or held back as pool reserve)', $got, $want);
        $gridNote = $grids === [] ? '' : sprintf('; grids %s', self::describe($before, $plan['grids']));
        $log = new EventLog($runId, false);
        $log->write('Info', self::KIND_RELEASE, sprintf(
            'funds released via dashboard — slice %s back to %s, the watchdog respawns the daemon%s',
            $slice,
            $restore,
            $gridNote
        ), ['slice_before' => $want, 'slice' => $got, 'grids_before' => $before, 'grids_after' => $plan['grids']]);
        self::audit($log);
        return ['ok' => true, 'message' => sprintf('run %d released with %s — %s again, the watchdog respawns it within a minute%s', $runId, $slice, $restore, $gridNote)];
    }

    // ── helpers ─────────────────────────────────────────────────────────

    /** Latest funds_hold marker payload (status_before, grids_before), [] when held some other way. */
    private static function marker(GridRun $run): array
    {
        $e = BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByKind(self::KIND_HOLD)
            ->orderByIdBotEvent(\Criteria::DESC)
            ->findOne();
        if ($e === null) {
            return [];
        }
        $p = json_decode((string) $e->getPayload(), true);
        if (!is_array($p)) {
            return [];
        }
        $out = [];
        $status = (string) ($p['status_before'] ?? '');
        if (in_array($status, BudgetGuard::ACTIVE_STATUSES, true)) {
            $out['status_before'] = $status;
        }
        if (isset($p['grids_before']) && is_array($p['grids_before'])) {
            $out['grids_before'] = $p['grids_before'];
        }
        return $out;
    }

    /** @return array<int, GridRun> active non-Trend runs other than $exceptId, keyed by id */
    private static function grids(int $exceptId): array
    {
        return Allocator::grids($exceptId);
    }

    /** shared budget minus every active run's slice, whole USDT, never negative */
    private static function headroom(): string
    {
        return Allocator::headroom();
    }

    /**
     * The FULL cap minus the active runs that are neither $runId nor a grid
     * in the plan.
     *
     * cap(), not allocatable(): a release is an operator act, and the pool
     * reserve is there to stop the MACHINE from sizing itself into the guard
     * (see Allocator::capacity). Planned against allocatable, Hold → Release
     * returned less than was parked — 300 in, 235 back — and a release the
     * cap could fund was refused outright (2026-09-21 review).
     */
    private static function capacity(int $runId, array $grids): string
    {
        // whole USDT here, unlike TrendActivator's raw-SCALE capacity()
        return bcadd(Allocator::capacity($runId, $grids, BudgetPool::cap()), '0', 0);
    }

    private static function activeSum(): string
    {
        return Allocator::activeSum();
    }

    /** BudgetGuard-checked slice write. @throws \RuntimeException on overcommit */
    private static function writeSlice(GridRun $run, string $budget): void
    {
        Allocator::writeSlice($run, $budget);
    }

    private static function enqueue(int $runId, string $command, string $note): void
    {
        Allocator::enqueue($runId, $command, $note);
    }

    private static function audit(EventLog $log): void
    {
        Allocator::audit($log, TrendActivator::KIND_ERROR);
    }

    private static function describe(array $before, array $after): string
    {
        return Allocator::describe($before, $after);
    }
}
