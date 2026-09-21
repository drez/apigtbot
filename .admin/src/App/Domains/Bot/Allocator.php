<?php

namespace App\Domains\Bot;

use App\BotCommand;
use App\GridRun;
use App\GridRunQuery;

/**
 * The one owner of shared-pool plumbing.
 *
 * Slice reallocation used to live in two places that had drifted into
 * near-verbatim copies of the same helpers: TrendActivator (machine-scaled
 * trend arm) and FundsHold (the dashboard hold/release buttons) each carried
 * their own writeSlice(), capacity(), grids(), audit() and describe(). This
 * class holds that plumbing once; both callers delegate to it.
 *
 * Deliberately NOT unified here: the three planners. TrendActivator's
 * planActivate() shrinks with an iterative ceilDiv share, planRelease() and
 * FundsHold::planSpread() distribute with floor-division plus
 * remainder-to-the-largest. Those are two different rounding conventions, 44
 * tests pin exact integers off them, and this is a live money system — so the
 * math stays where it is and only the plumbing moves.
 *
 * Every read of another process's slice goes through ->select() rather than a
 * hydrated query. Propel 1 never re-hydrates pooled objects, so a hydrated
 * read inside a long-lived daemon serves boot-time values — the 2026-09-05→09
 * prod incident, documented at BudgetGuard::check().
 *
 * Nothing here is called from Daemon::tick(). Allocation happens in one place,
 * on a cron, under a lock; N daemons reallocating against each other is that
 * same incident with extra steps.
 *
 * Every plan here is made against BudgetPool::allocatable() — the cap less
 * gtbot_pool_reserve_pct — while BudgetGuard keeps enforcing the FULL cap.
 * The allocator therefore stops short of the ceiling it would otherwise trip
 * on the next rounding, and a fleet already sitting between the two is left
 * alone rather than trimmed into its floors (see rebalance()).
 */
final class Allocator
{
    /** @var string a pool-wide reallocation was applied */
    public const KIND_REBALANCE = 'alloc_rebalance';
    /** @var string budget drifted toward the better earner */
    public const KIND_DRIFT = 'alloc_drift';
    /** @var string an exit proposal was computed for a slice that cannot shrink */
    public const KIND_PROPOSAL = 'alloc_exit_proposal';
    /** @var string a run was pinned to Fixed / released to Auto */
    public const KIND_PIN = 'alloc_mode_changed';
    /** @var string a pass failed and was rolled back */
    public const KIND_ERROR = 'alloc_error';

    /** Advisory lock serialising the allocator against the trend activator. */
    public const LOCK = 'gtbot-allocator';

    private const SCALE = 8;

    // ── reads ───────────────────────────────────────────────────────────

    /**
     * Every active run as a raw row — the pool-safe read.
     *
     * select() returns the RAW column values, so an ENUM comes back as its int
     * ordinal (Status 5, not 'Done'; Algo 1, not 'Trend'). Comparing those
     * against a string silently never matches, so Status and Algo are mapped
     * back through the peer's value set here and callers get strings.
     *
     * @return array<int, array<string, mixed>> id => row
     */
    public static function activeRows(): array
    {
        $statuses = \App\GridRunPeer::getValueSet(\App\GridRunPeer::STATUS);
        $algos = \App\GridRunPeer::getValueSet(\App\GridRunPeer::ALGO);
        $out = [];
        $rows = GridRunQuery::create()
            ->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)
            ->select(['IdGridRun', 'Label', 'Symbol', 'Algo', 'Status', 'BudgetQuote', 'DeployPct', 'KillSwitch', 'NLevels'])
            ->find();
        foreach ($rows as $row) {
            $row = (array) $row;
            $row['Status'] = $statuses[$row['Status']] ?? (string) $row['Status'];
            $row['Algo'] = $algos[$row['Algo']] ?? (string) $row['Algo'];
            $out[(int) $row['IdGridRun']] = $row;
        }
        return $out;
    }

    /**
     * Active non-Trend runs (the grids), keyed by id, optionally excluding one.
     *
     * Hydrated on purpose: callers write these rows back.
     *
     * @return array<int, GridRun>
     */
    public static function grids(?int $exceptId = null): array
    {
        $out = [];
        $rows = GridRunQuery::create()
            ->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)
            ->orderByIdGridRun()
            ->find();
        foreach ($rows as $r) {
            $id = (int) $r->getIdGridRun();
            if ($id !== $exceptId && (string) $r->getAlgo() !== 'Trend') {
                $out[$id] = $r;
            }
        }
        return $out;
    }

    /** Sum of every active run's slice, at SCALE. */
    public static function activeSum(?int $exceptId = null): string
    {
        $sum = '0';
        foreach (self::sliceRows() as $id => $budget) {
            if ($id !== $exceptId) {
                $sum = bcadd($sum, $budget, self::SCALE);
            }
        }
        return $sum;
    }

    /**
     * What is free to HAND OUT: the allocatable pool minus every active
     * slice, whole USDT, never negative.
     *
     * Allocatable, not cap: everything downstream of this number is an
     * automatic raise, and the reserve exists precisely so the machine stops
     * short of the guard's ceiling (BudgetPool::reservePct). A fleet already
     * sitting between allocatable and cap reports 0 here — no headroom to
     * spend, and equally no trimming demanded.
     */
    public static function headroom(?string $pool = null): string
    {
        $room = bcsub($pool ?? BudgetPool::allocatable(), self::activeSum(), self::SCALE);
        return bccomp($room, '0', self::SCALE) > 0 ? bcadd($room, '0', 0) : '0';
    }

    /**
     * Allocatable pool minus the active runs that are neither $runId nor one
     * of $grids — i.e. what the plan for ($runId + $grids) may spend. Returned
     * at SCALE; callers that want whole USDT round it themselves, because the
     * two existing callers differ on that and the tests pin both.
     *
     * Allocatable by default, for the same reason as headroom(): the caller
     * is usually the MACHINE deciding how much to hand a run (trend
     * activation, trend release), and the reserve exists to stop the machine
     * short of the guard's ceiling.
     *
     * $pool overrides that with the number the caller must plan against.
     * FundsHold::release passes BudgetPool::cap(), because releasing a hold
     * is an OPERATOR act (2026-09-21 review) — the same reasoning that keeps
     * RunFactory::validate on the full cap. Planning it against allocatable
     * made Hold → Release lossy: a grid parked at 300 came back with 235 on a
     * fleet that was never overcommitted, the difference quietly absorbed
     * into the reserve, and a release the cap could easily fund was refused
     * outright. The reserve is slack the machine refuses to spend, not a
     * budget the operator has to argue with.
     *
     * @param array<int, mixed> $grids keyed by run id
     * @param string|null       $pool  what to plan against; null = allocatable
     */
    public static function capacity(int $runId, array $grids, ?string $pool = null): string
    {
        $cap = $pool ?? BudgetPool::allocatable();
        foreach (self::sliceRows() as $id => $budget) {
            if ($id !== $runId && !isset($grids[$id])) {
                $cap = bcsub($cap, $budget, self::SCALE);
            }
        }
        return $cap;
    }

    /** @return array<int, string> id => budget_quote, raw rows */
    private static function sliceRows(): array
    {
        $out = [];
        $rows = GridRunQuery::create()
            ->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)
            ->select(['IdGridRun', 'BudgetQuote'])
            ->find();
        foreach ($rows as $row) {
            $out[(int) $row['IdGridRun']] = (string) $row['BudgetQuote'];
        }
        return $out;
    }

    // ── writes ──────────────────────────────────────────────────────────

    /**
     * BudgetGuard-checked slice / deploy write. A null leaves that field alone.
     *
     * @throws \RuntimeException on overcommit — the caller decides whether that
     *         aborts a plan or is caught and downgraded to a warning
     */
    public static function writeSlice(GridRun $run, ?string $budget, ?int $deployPct = null, bool $allowDecreaseWhileOver = false): void
    {
        if ($budget !== null) {
            // A decrease strictly reduces the committed sum, so it can never
            // make the invariant worse. Without this, a pool lowered below the
            // current slices could never shrink them at all: the first write
            // would be refused for an overcommit it was busy fixing.
            $isDecrease = bccomp($budget, (string) $run->getBudgetQuote(), self::SCALE) < 0;
            $over = ($allowDecreaseWhileOver && $isDecrease)
                ? null
                : BudgetGuard::check((int) $run->getIdGridRun(), $budget, (string) $run->getStatus());
            if ($over !== null) {
                throw new \RuntimeException(sprintf(
                    'run %d → %s refused: %s',
                    (int) $run->getIdGridRun(),
                    $budget,
                    BudgetGuard::message($over)
                ));
            }
            $run->setBudgetQuote($budget);
        }
        if ($deployPct !== null) {
            $run->setDeployPct($deployPct);
        }
        $run->save();
    }

    /** Queue one command for one run. */
    public static function enqueue(int $runId, string $command, string $note): void
    {
        $c = new BotCommand();
        $c->setIdGridRun($runId);
        $c->setCommand($command);
        $c->setCmdStatus('Pending');
        $c->setNote($note);
        $c->save();
    }

    /**
     * Reload every run whose slice moved — a budget edit is inert until the
     * daemon re-reads it. Deduped: one Reload per run per pass.
     *
     * @param int[] $runIds
     */
    public static function reload(array $runIds, string $note): void
    {
        foreach (array_unique($runIds) as $id) {
            self::enqueue((int) $id, 'Reload', $note);
        }
    }

    /**
     * Post-plan invariant assert. Belt, braces and a smoke alarm: every write
     * is already guarded, but a plan that somehow lands overcommitted must be
     * loud rather than silent.
     */
    public static function audit(EventLog $log, string $kind): void
    {
        $over = BudgetGuard::check();
        if ($over !== null) {
            $log->write('Alert', $kind, 'INVARIANT VIOLATION after realloc: ' . BudgetGuard::message($over));
        }
    }

    // ── helpers ─────────────────────────────────────────────────────────

    /** "#3 200 → 350, #5 150" — unchanged slices print without an arrow. */
    public static function describe(array $before, array $after): string
    {
        $parts = [];
        foreach ($after as $id => $v) {
            $b = $before[$id] ?? null;
            $parts[] = $b !== null && is_numeric((string) $b) && bccomp((string) $b, (string) $v, 0) === 0
                ? "#$id $v"
                : ($b === null ? "#$id $v" : "#$id $b → $v");
        }
        return $parts === [] ? '(none)' : implode(', ', $parts);
    }

    // ── reallocation ────────────────────────────────────────────────────

    /**
     * What a run must keep: its viability floor, and never less than the quote
     * its open inventory is actually tied up in. Taking a slice below this
     * would strand a working exit or force a divestment.
     */
    public static function hardFloor(GridRun $run): string
    {
        $store = new OrderStore(
            (int) $run->getIdGridRun(),
            (string) $run->getRunUid(),
            $run->getLedgerResetAt('Y-m-d H:i:s'),
            (bool) $run->getSimulated()
        );
        $invested = $store->investedQuote();
        $legacy = $store->legacyReserveQuote();
        // the conservative reservation: inventory valued at cost vs at its
        // working exit price, whichever ties up more
        $committed = bccomp($invested, $legacy, self::SCALE) >= 0 ? $invested : $legacy;
        $floor = TrendActivator::gridFloor($run);
        $committedWhole = self::ceilWhole($committed);
        return bccomp($floor, $committedWhole, 0) >= 0 ? $floor : $committedWhole;
    }

    private static function ceilWhole(string $v): string
    {
        $t = bcadd($v, '0', 0);
        return bccomp($t, $v, self::SCALE) < 0 ? bcadd($t, '1', 0) : $t;
    }

    /**
     * Is this run's slice ours to move?
     *
     * A Fixed run is pinned by the operator. A Trend arm that the activator is
     * actively managing is pinned too, whatever its column says — reassert()
     * re-writes its slice every 15 minutes, so touching it would just start a
     * tug-of-war that produces churn and Reloads and nothing else.
     *
     * @return array{auto: bool, why: string}
     */
    public static function effectiveMode(GridRun $run): array
    {
        if ((string) ($run->getAllocMode() ?: 'Auto') === 'Fixed') {
            return ['auto' => false, 'why' => 'Fixed (pinned by the operator)'];
        }
        if ((string) ($run->getAlgo() ?: 'Grid') === 'Trend') {
            $state = TrendActivator::state((int) $run->getIdGridRun());
            if (in_array($state, ['active', 'winding_down'], true)) {
                return ['auto' => false, 'why' => 'Fixed (trend arm ' . $state . ' — the activator owns it)'];
            }
        }
        return ['auto' => true, 'why' => 'Auto'];
    }

    /**
     * Spread a target total over the auto runs pro-rata, clamped to floors.
     *
     * Pure and whole-USDT. Returns the full id => slice map including the
     * pinned runs, unchanged, so callers can diff against it.
     *
     * `deploys` (default true) says whether a run can actually put capital to
     * work right now. It only matters in the equal-split fallback below; see
     * there.
     *
     * @param array<int, array{budget: string, floor: string, auto: bool, deploys?: bool}> $runs
     * @return array<int, string>
     */
    public static function planPool(string $cap, array $runs): array
    {
        $out = [];
        $pinned = '0';
        $autoCur = '0';
        foreach ($runs as $id => $r) {
            $out[$id] = bcadd($r['budget'], '0', 0);
            if ($r['auto']) {
                $autoCur = bcadd($autoCur, $out[$id], 0);
            } else {
                $pinned = bcadd($pinned, $out[$id], 0);
            }
        }
        $room = bcsub(bcadd($cap, '0', 0), $pinned, 0);
        if (bccomp($room, '0', 0) < 0) {
            $room = '0'; // pinned runs alone exceed the cap; auto runs go to floor
        }
        $auto = array_filter($runs, static fn (array $r): bool => $r['auto']);
        if ($auto === []) {
            return $out;
        }

        // floors first: nobody goes below them, whatever the arithmetic says
        $floorSum = '0';
        foreach ($auto as $id => $r) {
            $floorSum = bcadd($floorSum, $r['floor'], 0);
        }
        if (bccomp($floorSum, $room, 0) >= 0) {
            // A FLOOR IS A FLOOR, NEVER A RAISE (2026-09-21 review). This
            // branch hands every auto run its floor, and for a run sitting
            // BELOW that floor the assignment is an increase — on a pool
            // that by definition has no room for it, so BudgetGuard refuses
            // the write and the whole pass rolls back (with an Alert) rather
            // than applying the decreases that were the point of it. The
            // floor's job here is to stop a trim, so cap it at what the run
            // already holds: an under-floor run is left exactly where it is
            // and the operator (or a later pass with room) raises it.
            foreach ($auto as $id => $r) {
                $floor = bcadd($r['floor'], '0', 0);
                $cur = $out[$id];
                $out[$id] = bccomp($floor, $cur, 0) > 0 ? $cur : $floor;
            }
            return $out;
        }

        // distribute the room above the floors in proportion to current slices
        $spare = bcsub($room, $floorSum, 0);
        $weightBase = '0';
        foreach ($auto as $id => $r) {
            $w = bcsub(bcadd($r['budget'], '0', 0), $r['floor'], 0);
            $weightBase = bcadd($weightBase, bccomp($w, '0', 0) > 0 ? $w : '0', 0);
        }
        // EVERY AUTO RUN EXACTLY ON ITS FLOOR (2026-09-21, round 2). That is
        // not a rare shape — it is precisely what a trend activation LEAVES
        // the grids in — and with every weight 0 the spare used to be split
        // EQUALLY over all of them, which handed an IDLE trend arm (deploy 0,
        // entries gated until the activator funds it) a full share of capital
        // it cannot reach: 245 USDT on a 1300 pool, parked, and subtracted
        // from the capacity the OTHER symbol's arm can be funded from. Only
        // the runs that can deploy share it; if none can, everyone does,
        // because refusing to allocate at all is worse than allocating badly.
        $spreaders = $auto;
        if (bccomp($weightBase, '0', 0) <= 0) {
            $able = array_filter($auto, static fn (array $r): bool => ($r['deploys'] ?? true) === true);
            if ($able !== []) {
                $spreaders = $able;
            }
        }
        $assigned = '0';
        $largest = null;
        foreach ($auto as $id => $r) {
            $w = bcsub(bcadd($r['budget'], '0', 0), $r['floor'], 0);
            $w = bccomp($w, '0', 0) > 0 ? $w : '0';
            if (bccomp($weightBase, '0', 0) > 0) {
                $share = bcdiv(bcmul($spare, $w, 0), $weightBase, 0);
            } else {
                $share = isset($spreaders[$id]) ? bcdiv($spare, (string) count($spreaders), 0) : '0';
            }
            $out[$id] = bcadd($r['floor'], $share, 0);
            $assigned = bcadd($assigned, $share, 0);
            // the rounding remainder follows the share, so it can never land
            // on a run this pass deliberately gave nothing to
            $eligible = bccomp($weightBase, '0', 0) > 0 || isset($spreaders[$id]);
            if ($eligible && ($largest === null || bccomp($out[$id], $out[$largest], 0) > 0)) {
                $largest = $id;
            }
        }
        // whole-USDT remainder to the largest slice (the planSpread convention)
        $rest = bcsub($spare, $assigned, 0);
        if ($largest !== null && bccomp($rest, '0', 0) > 0) {
            $out[$largest] = bcadd($out[$largest], $rest, 0);
        }
        return $out;
    }

    /**
     * Can this run put capital to work right now? @see planPool
     *
     * A Trend arm the activator has not funded is capacity waiting for a
     * signal, not a place to park USDT: its deploy_pct is its idle value and
     * the activator raises both together on the next confirmed TREND_UP.
     */
    public static function canDeploy(GridRun $run): bool
    {
        if ((string) ($run->getAlgo() ?: 'Grid') !== 'Trend') {
            return true;
        }
        return TrendActivator::state((int) $run->getIdGridRun()) !== 'idle';
    }

    /**
     * How much of the fleet's allocated capital is IDLE inside its slice —
     * Σ max(0, slice − floor) over the AUTO runs. Zero means every USDT is
     * held by a floor or by committed inventory, so no plan can move
     * anything without divesting, which this allocator never does.
     */
    public static function idleAboveFloors(): string
    {
        $idle = '0';
        foreach (GridRunQuery::create()->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)->find() as $run) {
            if (!self::effectiveMode($run)['auto']) {
                continue;
            }
            $room = bcsub(bcadd((string) $run->getBudgetQuote(), '0', 0), self::hardFloor($run), 0);
            if (bccomp($room, '0', 0) > 0) {
                $idle = bcadd($idle, $room, 0);
            }
        }
        return $idle;
    }

    /** Σ slices − allocatable, never negative: the reserve the fleet has eaten into. */
    public static function excessOverAllocatable(): string
    {
        $over = bcsub(self::activeSum(), BudgetPool::allocatable(), self::SCALE);
        return bccomp($over, '0', self::SCALE) > 0 ? bcadd($over, '0', 0) : '0';
    }

    /**
     * Should the 15-minute rebalance leg run at all? (bin/gtbot-allocate)
     *
     * THE MISSING WAY BACK (2026-09-21, round 2). The gate used to be
     * "headroom() past the deadband, or overcommitted", and headroom() is
     * measured against ALLOCATABLE — so it reads 0 for every fleet sitting
     * between allocatable and cap and the leg was skipped forever. That is
     * exactly the state an activation or a hold/release leaves behind, and it
     * meant the pool reserve, once spent, never came back: only a budget edit
     * or a retire (which call rebalance() directly) ever trimmed it.
     *
     * So a fleet over allocatable is a reason to run — but only when some
     * auto run actually has idle capital above its floor. When every USDT is
     * held by a floor or by inventory the plan cannot move anything, and
     * running would print an event about capital that is not in danger.
     * Nothing here ever trims below a floor, below committed inventory, or
     * sells: that is planPool's contract, unchanged.
     *
     * @return array{run: bool, why: string}
     */
    public static function rebalanceNeeded(string $deadband): array
    {
        $over = BudgetGuard::check();
        if ($over !== null) {
            return ['run' => true, 'why' => 'the fleet is overcommitted — ' . BudgetGuard::message($over)];
        }
        $headroom = self::headroom();
        if (bccomp($headroom, $deadband, 0) > 0) {
            return ['run' => true, 'why' => sprintf('%s USDT idle, past the %s deadband', $headroom, $deadband)];
        }
        $excess = self::excessOverAllocatable();
        if (bccomp($excess, '0', 0) > 0) {
            $idle = self::idleAboveFloors();
            if (bccomp($idle, '0', 0) > 0) {
                return ['run' => true, 'why' => sprintf(
                    '%s USDT of the pool reserve is committed and %s USDT of it is idle inside a slice — giving it back',
                    $excess,
                    $idle
                )];
            }
            return ['run' => false, 'why' => sprintf(
                '%s USDT past the allocatable pool, but every USDT of it is held by a floor or by inventory — nothing to give back without divesting',
                $excess
            )];
        }
        return ['run' => false, 'why' => sprintf('%s USDT idle, inside the %s deadband — leaving it', $headroom, $deadband)];
    }

    /**
     * Bring every auto run's slice in line with the current cap.
     *
     * Ordering: decreases are written first, then increases. A decrease can
     * never violate the invariant under either the old or the new cap, so it
     * is always safe to land first; increases go last and are each
     * BudgetGuard-checked against the cap in force.
     *
     * Nothing is ever SOLD here. If the floors alone exceed the cap the
     * shortfall is reported and the pool sits overcommitted — a state the
     * system already handles safely (Daemon gates entries; exits keep working)
     * — with exit proposals as advice for the operator.
     *
     * TWO NUMBERS, ON PURPOSE (2026-09-21). The plan is made against the
     * ALLOCATABLE pool, so a rebalance never hands out the reserve; the
     * shortfall — the thing that produces exit proposals and the "could NOT
     * be freed" sentence — is measured against the FULL cap. Measuring it
     * against allocatable instead would declare prod's 1300-of-1300 fleet
     * short by 65 USDT on every 15-minute cron pass: an exit-proposal storm
     * about capital that is not in any danger. The reserve is reached by not
     * re-raising and by releases as arms go flat, never by trimming a run
     * into its floors or its inventory.
     *
     * @return array{ok: bool, message: string, applied: array<int, array{from: string, to: string}>,
     *               shortfall: string, proposals: array, reloaded: int[], skipped: array<int, string>}
     */
    public static function rebalance(string $trigger, bool $apply = true, ?EventLog $log = null, bool $reload = true): array
    {
        $cap = BudgetPool::cap();
        $allocatable = BudgetPool::allocatable();
        $runs = [];
        $models = [];
        $skipped = [];
        foreach (GridRunQuery::create()->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)->orderByIdGridRun()->find() as $run) {
            $id = (int) $run->getIdGridRun();
            $mode = self::effectiveMode($run);
            $models[$id] = $run;
            $runs[$id] = [
                'budget' => bcadd((string) $run->getBudgetQuote(), '0', 0),
                'floor' => self::hardFloor($run),
                'auto' => $mode['auto'],
                'deploys' => self::canDeploy($run),
            ];
            if (!$mode['auto']) {
                $skipped[$id] = $mode['why'];
            }
        }
        if ($runs === []) {
            return ['ok' => true, 'message' => 'no active runs to rebalance', 'applied' => [], 'shortfall' => '0', 'proposals' => [], 'reloaded' => [], 'skipped' => []];
        }

        $plan = self::planPool($allocatable, $runs);

        // A RUN LEFT UNDER ITS FLOOR IS NOT "NOTHING HAPPENED" (2026-09-21,
        // round 2). planPool caps a floor at what the run already holds, so a
        // run below its floor is left exactly where it is — correct (the
        // alternative is a raise the guard refuses and a rolled-back pass),
        // but it was invisible: no event, no alert, nothing telling the
        // operator that a run is carrying less than its own viability floor
        // and that only they can fix it.
        $under = [];
        foreach ($plan as $id => $target) {
            if (!$runs[$id]['auto'] || bccomp($target, $runs[$id]['budget'], 0) !== 0) {
                continue;
            }
            if (bccomp($runs[$id]['budget'], $runs[$id]['floor'], 0) < 0) {
                $under[] = sprintf('#%d (%s of %s)', $id, $runs[$id]['budget'], $runs[$id]['floor']);
                $skipped[$id] = sprintf(
                    'under its floor (%s of %s) — the pool has no room to raise it, so it was left where it is',
                    $runs[$id]['budget'],
                    $runs[$id]['floor']
                );
            }
        }

        $planned = '0';
        foreach ($plan as $v) {
            $planned = bcadd($planned, $v, 0);
        }
        $shortfall = bcsub($planned, bcadd($cap, '0', 0), 0);
        $shortfall = bccomp($shortfall, '0', 0) > 0 ? $shortfall : '0';

        $applied = [];
        $decreases = [];
        $increases = [];
        foreach ($plan as $id => $target) {
            $cmp = bccomp($target, $runs[$id]['budget'], 0);
            if ($cmp === 0) {
                continue;
            }
            if ($cmp < 0) {
                $decreases[$id] = $target;
            } else {
                $increases[$id] = $target;
            }
        }

        $proposals = [];
        if (bccomp($shortfall, '0', 0) > 0) {
            foreach ($models as $id => $run) {
                if (!$runs[$id]['auto']) {
                    continue;
                }
                $p = self::exitProposal($run, $runs[$id], $plan[$id]);
                if ($p !== null) {
                    $proposals[] = $p;
                }
            }
        }

        if (!$apply) {
            return [
                'ok' => true,
                'message' => sprintf('plan only: %s', self::describe(array_map(static fn (array $r): string => $r['budget'], $runs), $plan)),
                'applied' => self::asFromTo($runs, $decreases + $increases),
                'shortfall' => $shortfall,
                'proposals' => $proposals,
                'reloaded' => [],
                'skipped' => $skipped,
            ];
        }

        $con = \Propel::getConnection();
        $con->beginTransaction();
        try {
            foreach ($decreases as $id => $target) {
                self::writeSlice($models[$id], $target, null, true);
                $applied[$id] = ['from' => $runs[$id]['budget'], 'to' => $target];
            }
            foreach ($increases as $id => $target) {
                self::writeSlice($models[$id], $target);
                $applied[$id] = ['from' => $runs[$id]['budget'], 'to' => $target];
            }
            $con->commit();
        } catch (\Throwable $e) {
            $con->rollBack();
            if ($log !== null) {
                $log->write('Alert', self::KIND_ERROR, 'rebalance rolled back: ' . $e->getMessage(), ['trigger' => $trigger]);
            }
            return ['ok' => false, 'message' => 'rebalance rolled back: ' . $e->getMessage(), 'applied' => [], 'shortfall' => $shortfall, 'proposals' => $proposals, 'reloaded' => [], 'skipped' => $skipped];
        }

        // the caller may already be issuing its own Reload sweep (BudgetPool::set
        // reloads every active run, because the cap moved even for runs whose
        // slice did not) — a second one per run is pure churn
        $reloaded = array_keys($applied);
        if ($reload) {
            self::reload($reloaded, 'allocator: ' . $trigger);
        }

        $msg = $applied === []
            ? 'slices already match the pool'
            : sprintf('slices rebalanced (%s): %s', $trigger, self::describe(array_map(static fn (array $r): string => $r['budget'], $runs), $plan));
        if (bccomp($shortfall, '0', 0) > 0) {
            $msg .= sprintf('; %s USDT could NOT be freed — committed inventory holds the floors above the cap. Nothing was sold; see the exit proposals.', $shortfall);
        }
        // A PASS THAT MOVED NOTHING IS EXACTLY WHEN THIS HAS TO BE SAID
        // (2026-09-21, round 3): round 2 journaled the under-floor run inside
        // `$applied !== []`, and a pass whose whole point is "nothing could be
        // moved" applies nothing by definition — so the one case the message
        // was written for never reached the feed. Narrowed to the UNDER-FLOOR
        // entries on purpose: `$skipped` also carries every pinned run, and
        // those are a standing state, not news.
        if ($under !== []) {
            $msg .= sprintf('; run%s %s carrying less than its own viability floor — the pool has no room to raise it, only the operator can', count($under) === 1 ? '' : 's', implode(', ', $under));
        }

        if ($log !== null && ($applied !== [] || $under !== [])) {
            $log->write('Info', self::KIND_REBALANCE, $msg, [
                'trigger' => $trigger,
                'cap' => $cap,
                'allocatable' => $allocatable,
                'reserve_pct' => BudgetPool::reservePct(),
                'applied' => $applied,
                'shortfall' => $shortfall,
                'skipped' => $skipped,
                'proposals' => $proposals,
            ]);
            self::audit($log, self::KIND_ERROR);
        }

        return ['ok' => true, 'message' => $msg, 'applied' => $applied, 'shortfall' => $shortfall, 'proposals' => $proposals, 'reloaded' => $reloaded, 'skipped' => $skipped];
    }

    /**
     * Shrink auto runs toward a cap that has NOT been written yet.
     *
     * This is the first half of a pool lowering: the decreases are computed
     * against the incoming cap but applied while the OLD, larger cap is still
     * in force, so every write trivially satisfies BudgetGuard and the fleet is
     * never transiently overcommitted. Only decreases are applied here.
     *
     * $cap is the FULL incoming cap, as the operator typed it. Like
     * rebalance(), the plan is made against its allocatable part (so the new
     * pool is not immediately re-filled to its own ceiling) while the
     * shortfall — "this much could not be freed" — is measured against the
     * full number, because that is the promise the operator was given.
     *
     * @return array{applied: array<int, array{from: string, to: string}>, shortfall: string, proposals: array}
     */
    public static function shrinkToward(string $cap, bool $reload = true): array
    {
        $runs = [];
        $models = [];
        foreach (GridRunQuery::create()->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)->orderByIdGridRun()->find() as $run) {
            $id = (int) $run->getIdGridRun();
            $models[$id] = $run;
            $runs[$id] = [
                'budget' => bcadd((string) $run->getBudgetQuote(), '0', 0),
                'floor' => self::hardFloor($run),
                'auto' => self::effectiveMode($run)['auto'],
                'deploys' => self::canDeploy($run),
            ];
        }
        if ($runs === []) {
            return ['applied' => [], 'shortfall' => '0', 'proposals' => []];
        }

        $plan = self::planPool(BudgetPool::allocatableOf($cap), $runs);
        $planned = '0';
        foreach ($plan as $v) {
            $planned = bcadd($planned, $v, 0);
        }
        $shortfall = bcsub($planned, bcadd($cap, '0', 0), 0);
        $shortfall = bccomp($shortfall, '0', 0) > 0 ? $shortfall : '0';

        $proposals = [];
        $applied = [];
        $con = \Propel::getConnection();
        $con->beginTransaction();
        try {
            foreach ($plan as $id => $target) {
                if (bccomp($target, $runs[$id]['budget'], 0) >= 0) {
                    continue; // increases wait for the new cap
                }
                self::writeSlice($models[$id], $target, null, true);
                $applied[$id] = ['from' => $runs[$id]['budget'], 'to' => $target];
            }
            $con->commit();
        } catch (\Throwable $e) {
            $con->rollBack();
            return ['applied' => [], 'shortfall' => $shortfall, 'proposals' => []];
        }

        if (bccomp($shortfall, '0', 0) > 0) {
            foreach ($models as $id => $run) {
                if (!$runs[$id]['auto']) {
                    continue;
                }
                $p = self::exitProposal($run, $runs[$id], $plan[$id]);
                if ($p !== null) {
                    $proposals[] = $p;
                }
            }
        }

        if ($reload) {
            self::reload(array_keys($applied), 'allocator: pool lowering');
        }
        return ['applied' => $applied, 'shortfall' => $shortfall, 'proposals' => $proposals];
    }

    /**
     * A run freed its slice permanently (retire / purge). The slice is already
     * out of BudgetGuard's sum by then, so this is just a rebalance.
     *
     * @return array{ok: bool, message: string, moved: array<int, string>}
     */
    public static function onSliceFreed(int $runId, string $freed, string $cause): array
    {
        if (bccomp($freed, '0', 0) <= 0) {
            return ['ok' => true, 'message' => '', 'moved' => []];
        }
        $res = self::rebalance($cause, true);
        $moved = [];
        foreach ($res['applied'] as $id => $fromTo) {
            $moved[$id] = $fromTo['to'];
        }
        return [
            'ok' => $res['ok'],
            'message' => $moved === [] ? sprintf('%s USDT is idle headroom', $freed) : sprintf('%s USDT spread: %s', $freed, self::describe([], $moved)),
            'moved' => $moved,
        ];
    }

    /** @param array<int, string> $targets */
    private static function asFromTo(array $runs, array $targets): array
    {
        $out = [];
        foreach ($targets as $id => $t) {
            $out[$id] = ['from' => $runs[$id]['budget'], 'to' => $t];
        }
        return $out;
    }

    /**
     * What it would take to free the capital a lowered cap wants back.
     *
     * Lots are classified INDIVIDUALLY, never by the position VWAP: an
     * aggregate VWAP under water hides that half the lots are green, and green
     * lots are exactly the ones policy permits selling. Getting this wrong
     * pushes the operator toward an unnecessary loss opt-in.
     */
    private static function exitProposal(GridRun $run, array $row, string $target): ?array
    {
        $mustFree = bcsub($row['budget'], $target, 0);
        if (bccomp($mustFree, '0', 0) <= 0) {
            return null;
        }
        $store = new OrderStore(
            (int) $run->getIdGridRun(),
            (string) $run->getRunUid(),
            $run->getLedgerResetAt('Y-m-d H:i:s'),
            (bool) $run->getSimulated()
        );
        $fee = (string) ($run->getFeePct() ?: '0');
        $mark = $run->getLastPrice() !== null ? (string) $run->getLastPrice() : null;
        $profile = (string) ($run->getProfile() ?: 'Balanced');

        $green = ['lots' => 0, 'frees' => '0'];
        $red = ['lots' => 0, 'frees' => '0'];
        $openBuys = '0';
        foreach ($store->openOrderObjects() as $o) {
            if ((string) $o->getSide() === 'Buy') {
                $openBuys = bcadd($openBuys, bcmul((string) $o->getPrice(), (string) $o->getQty(), self::SCALE), self::SCALE);
                continue;
            }
            $lotCost = $o->getLegacyBuyPrice() !== null ? (string) $o->getLegacyBuyPrice() : null;
            $qty = (string) $o->getQty();
            $proceeds = $mark !== null ? bcmul($mark, $qty, self::SCALE) : '0';
            // one lot is a position of one — same loss math, one source of truth
            $isRed = $lotCost !== null && LossGuard::wouldRealizeLoss($lotCost, $fee, $mark);
            if ($isRed) {
                $red['lots']++;
                $red['frees'] = bcadd($red['frees'], $proceeds, self::SCALE);
            } else {
                $green['lots']++;
                $green['frees'] = bcadd($green['frees'], $proceeds, self::SCALE);
            }
        }

        $verdict = 'none';
        if (bccomp(bcadd($green['frees'], $openBuys, self::SCALE), $mustFree, self::SCALE) >= 0) {
            $verdict = 'covered';
        } elseif ($green['lots'] > 0 || bccomp($openBuys, '0', self::SCALE) > 0) {
            $verdict = 'partial';
        } elseif ($red['lots'] > 0) {
            $verdict = 'loss_only';
        }

        return [
            'run' => (int) $run->getIdGridRun(),
            'label' => (string) $run->getLabel(),
            'symbol' => (string) $run->getSymbol(),
            'slice' => $row['budget'],
            'target_slice' => $target,
            'must_free' => $mustFree,
            'floor' => $row['floor'],
            'invested_quote' => $store->investedQuote(),
            'legacy_reserve' => $store->legacyReserveQuote(),
            // cancelling surplus buys frees quote with NO sale and no loss
            'open_buys_reclaimable' => $openBuys,
            'mark' => $mark,
            'green' => $green,
            'red' => $red,
            'verdict' => $verdict,
            'sell_at_loss' => (bool) $run->getSellAtLoss(),
            'profile_allows_loss' => ProfilePolicy::allowsRealizedLoss($profile),
            'requires_loss_optin' => $verdict !== 'covered' && $red['lots'] > 0,
        ];
    }

    // ── profit drift ────────────────────────────────────────────────────

    /** @return array{pct: string, max: string, hours: int, deadband: string, maxShare: string} */
    public static function driftConfig(): array
    {
        $get = static function (string $key, string $default): string {
            $v = \App\ConfigQuery::create()->findOneByConfig($key)?->getValue();
            return $v !== null && $v !== '' && is_numeric($v) ? (string) $v : $default;
        };
        return [
            'pct' => $get('gtbot_alloc_drift_pct', '0'),
            'max' => $get('gtbot_alloc_drift_max', '50'),
            'hours' => (int) $get('gtbot_alloc_drift_hours', '24'),
            'deadband' => '10',
            'maxShare' => $get('gtbot_alloc_max_share_pct', '50'),
        ];
    }

    /**
     * Move a little budget from the worse earners to the better ones.
     *
     * THE RULE THAT MAKES THIS SAFE TO RUN UNATTENDED: a donor may only give up
     * capital that is IDLE inside its slice — it is capped at
     * `slice - max(floor, committed)`. Drift can therefore never ask a run to
     * divest, which keeps the whole feature outside the never-sell-at-a-loss
     * problem. The only path that can require a divestment is a pool lowering,
     * and that is confirm-gated and sells nothing either.
     *
     * Ships disabled (gtbot_alloc_drift_pct = 0). Run the cron with --dry for a
     * window, read the alloc_drift events, then turn it on.
     *
     * @return array{ok: bool, message: string, applied: array<int, array{from: string, to: string}>,
     *               mean: ?string, moved: string, skipped: array<int, string>}
     */
    public static function drift(bool $apply = true, ?EventLog $log = null): array
    {
        $cfg = self::driftConfig();
        // NB: build early returns with array_merge, not +. PHP's union operator
        // keeps the LEFT operand on a key clash, so a $none template would
        // silently win over the real 'skipped'/'mean' values.
        $none = static fn (string $msg, array $extra = []): array => array_merge(
            ['ok' => true, 'applied' => [], 'mean' => null, 'moved' => '0', 'skipped' => [], 'message' => $msg],
            $extra
        );

        if (bccomp($cfg['pct'], '0', 6) <= 0) {
            return $none('drift is disabled (gtbot_alloc_drift_pct = 0)');
        }
        // a fleet-wide stand-down: the same posture the trend activator takes
        if (DrawdownGuard::check() !== null) {
            return $none('drawdown guard is tripped — standing down');
        }

        $days = AllocScore::windowDays();
        $scores = [];
        $slices = [];
        $floors = [];
        $models = [];
        $skipped = [];

        foreach (GridRunQuery::create()->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)->orderByIdGridRun()->find() as $run) {
            $id = (int) $run->getIdGridRun();
            $models[$id] = $run;
            $slices[$id] = bcadd((string) $run->getBudgetQuote(), '0', 0);

            $mode = self::effectiveMode($run);
            if (!$mode['auto']) {
                $skipped[$id] = $mode['why'];
                continue;
            }
            if ((bool) $run->getKillSwitch()) {
                $skipped[$id] = 'kill switch is on';
                continue;
            }
            $sc = AllocScore::score($run, $days);
            if (!$sc['eligible']) {
                $skipped[$id] = (string) $sc['why'];
                continue;
            }
            $scores[$id] = $sc;
            $floors[$id] = self::hardFloor($run);
        }

        $p = AllocScore::pressure($scores, $slices, $cfg['deadband']);
        if ($p['donors'] === [] || $p['recipients'] === []) {
            return $none(
                'no drift: ' . ($p['mean'] === null
                    ? 'fewer than two runs have earned an opinion'
                    : 'every eligible run is within the deadband'),
                ['mean' => $p['mean'], 'skipped' => $skipped]
            );
        }

        // the budget for THIS pass: a percentage of the eligible slice sum,
        // hard-capped in absolute USDT
        $eligibleSum = '0';
        foreach ($scores as $id => $_) {
            $eligibleSum = bcadd($eligibleSum, $slices[$id], 0);
        }
        $budget = bcdiv(bcmul($eligibleSum, $cfg['pct'], 0), '100', 0);
        if (bccomp($budget, $cfg['max'], 0) > 0) {
            $budget = bcadd($cfg['max'], '0', 0);
        }
        if (bccomp($budget, '0', 0) <= 0) {
            return $none('no drift: the per-pass budget rounds to zero', ['mean' => $p['mean'], 'skipped' => $skipped]);
        }

        // ── collect from donors, capped at what is IDLE in each slice ──
        $donorWeight = '0';
        foreach ($p['donors'] as $w) {
            $donorWeight = bcadd($donorWeight, $w, self::SCALE);
        }
        $collected = '0';
        $take = [];
        foreach ($p['donors'] as $id => $w) {
            $share = bccomp($donorWeight, '0', self::SCALE) > 0
                ? bcdiv(bcmul($budget, $w, self::SCALE), $donorWeight, 0)
                : '0';
            $idle = bcsub($slices[$id], $floors[$id], 0);
            if (bccomp($idle, '0', 0) < 0) {
                $idle = '0';
            }
            if (bccomp($share, $idle, 0) > 0) {
                $share = $idle; // never ask a run to sell
            }
            if (bccomp($share, '0', 0) > 0) {
                $take[$id] = $share;
                $collected = bcadd($collected, $share, 0);
            }
        }
        if (bccomp($collected, '0', 0) <= 0) {
            return $none(
                'no drift: every donor is fully invested, so there is no idle capital to move',
                ['mean' => $p['mean'], 'skipped' => $skipped]
            );
        }

        // ── hand it to recipients, capped at the max single share ──
        // Off the ALLOCATABLE pool: drift is an automatic raise like any
        // other, so the share ceiling is a share of what the machine may
        // hand out, not of the guard's ceiling.
        $cap = BudgetPool::allocatable();
        $ceiling = bcdiv(bcmul($cap, $cfg['maxShare'], 0), '100', 0);
        $recWeight = '0';
        foreach ($p['recipients'] as $w) {
            $recWeight = bcadd($recWeight, $w, self::SCALE);
        }
        $give = [];
        $handed = '0';
        $largest = null;
        foreach ($p['recipients'] as $id => $w) {
            $share = bccomp($recWeight, '0', self::SCALE) > 0
                ? bcdiv(bcmul($collected, $w, self::SCALE), $recWeight, 0)
                : '0';
            $room = bcsub($ceiling, $slices[$id], 0);
            if (bccomp($room, '0', 0) < 0) {
                $room = '0';
            }
            if (bccomp($share, $room, 0) > 0) {
                $share = $room;
            }
            if (bccomp($share, '0', 0) > 0) {
                $give[$id] = $share;
                $handed = bcadd($handed, $share, 0);
            }
            if ($largest === null || bccomp($slices[$id], $slices[$largest], 0) > 0) {
                $largest = $id;
            }
        }
        // whole-USDT remainder to the largest recipient that still has room
        $rest = bcsub($collected, $handed, 0);
        if ($largest !== null && isset($give[$largest]) && bccomp($rest, '0', 0) > 0) {
            $give[$largest] = bcadd($give[$largest], $rest, 0);
            $handed = $collected;
        }
        if (bccomp($handed, '0', 0) <= 0) {
            return $none('no drift: every recipient is already at the max single share', ['mean' => $p['mean'], 'skipped' => $skipped]);
        }
        // only take what was actually handed out — the pool must not lose USDT
        if (bccomp($handed, $collected, 0) < 0) {
            $take = self::scaleDown($take, $handed);
        }

        $applied = [];
        foreach ($take as $id => $amount) {
            $applied[$id] = ['from' => $slices[$id], 'to' => bcsub($slices[$id], $amount, 0)];
        }
        foreach ($give as $id => $amount) {
            $applied[$id] = ['from' => $slices[$id], 'to' => bcadd($slices[$id], $amount, 0)];
        }

        $msg = sprintf(
            'drift %s USDT (mean %s/1k/day): %s',
            $handed,
            $p['mean'] === null ? '?' : bcadd($p['mean'], '0', 4),
            self::describe($slices, array_map(static fn (array $a): string => $a['to'], $applied))
        );

        if (!$apply) {
            return ['ok' => true, 'message' => '[dry] ' . $msg, 'applied' => $applied, 'mean' => $p['mean'], 'moved' => $handed, 'skipped' => $skipped];
        }

        $con = \Propel::getConnection();
        $con->beginTransaction();
        try {
            foreach ($take as $id => $_) {          // decreases first, always
                self::writeSlice($models[$id], $applied[$id]['to'], null, true);
            }
            foreach ($give as $id => $_) {
                self::writeSlice($models[$id], $applied[$id]['to']);
            }
            $con->commit();
        } catch (\Throwable $e) {
            $con->rollBack();
            if ($log !== null) {
                $log->write('Alert', self::KIND_ERROR, 'drift rolled back: ' . $e->getMessage());
            }
            return ['ok' => false, 'message' => 'drift rolled back: ' . $e->getMessage(), 'applied' => [], 'mean' => $p['mean'], 'moved' => '0', 'skipped' => $skipped];
        }

        self::reload(array_keys($applied), 'allocator: profit drift');
        if ($log !== null) {
            $log->write('Info', self::KIND_DRIFT, $msg, [
                'mean' => $p['mean'],
                'moved' => $handed,
                'window_days' => $days,
                'applied' => $applied,
                'skipped' => $skipped,
                'scores' => array_map(static fn (array $sc): ?string => $sc['per_1k_day'], $scores),
            ]);
            self::audit($log, self::KIND_ERROR);
        }
        return ['ok' => true, 'message' => $msg, 'applied' => $applied, 'mean' => $p['mean'], 'moved' => $handed, 'skipped' => $skipped];
    }

    /**
     * Trim a collection map so it totals exactly $target — the recipients could
     * not absorb everything the donors were willing to give, and the pool must
     * not quietly lose the difference.
     *
     * @param array<int, string> $take
     * @return array<int, string>
     */
    private static function scaleDown(array $take, string $target): array
    {
        $total = '0';
        foreach ($take as $v) {
            $total = bcadd($total, $v, 0);
        }
        if (bccomp($total, '0', 0) <= 0 || bccomp($target, $total, 0) >= 0) {
            return $take;
        }
        $out = [];
        $running = '0';
        $last = array_key_last($take);
        foreach ($take as $id => $v) {
            if ($id === $last) {
                $out[$id] = bcsub($target, $running, 0);
                break;
            }
            $share = bcdiv(bcmul($target, $v, self::SCALE), $total, 0);
            $out[$id] = $share;
            $running = bcadd($running, $share, 0);
        }
        return array_filter($out, static fn (string $v): bool => bccomp($v, '0', 0) > 0);
    }

    /** Hours since the last drift pass, or null if there has never been one. */
    public static function hoursSinceDrift(): ?float
    {
        $ev = \App\BotEventQuery::create()
            ->filterByKind(self::KIND_DRIFT)
            ->orderByIdBotEvent(\Criteria::DESC)
            ->findOne();
        if ($ev === null) {
            return null;
        }
        $at = $ev->getDateCreation('Y-m-d H:i:s');
        return $at === null ? null : (time() - strtotime($at)) / 3600;
    }
}

