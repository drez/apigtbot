<?php

namespace App\Domains\Bot;

use App\BotCommandQuery;
use App\Domains\Bot\Gateway\GatewayInterface;
use App\GridRun;

/**
 * Tick-loop orchestrator: wires the pure core (GridMath/LevelStateMachine/
 * RiskManager/Filters/Reconciler) to the exchange gateway and the DB control
 * plane (grid_run flags + bot_command queue + bot_order/trade_cycle ledger).
 *
 * Boot order is sacred: config → hydrate from DB → reconcile against the
 * exchange → only then place anything. Dry-run mode never calls a signed
 * endpoint and never places an order.
 */
class Daemon
{
    private Filters $filters;
    /** @internal engine access — the level machine the grid ladder runs on */
    public LevelStateMachine $machine;
    /** @internal engine access — the durable order/cycle ledger */
    public OrderStore $store;
    /** @internal engine access
     *  @var string[] */
    public array $levels;
    /** @var string[] */
    private array $qtys = [];
    /** @internal engine access — the run config the pure core consumes */
    public array $config;
    /** the strategy half of the tick loop (Grid / Trend / …) */
    private Engine\StrategyEngine $engine;
    /** run.algo as of boot — a change means a clean restart + cutover */
    private string $bootAlgo = 'Grid';
    private bool $paused = false;
    private bool $reloadRequested = false;
    private bool $killApplied = false;
    private bool $flattenOnKill = false;
    private string $geomSig = '';
    private string $refitPendingSig = '';
    private ?int $bootCodeMtime = null;
    private ?int $balStampedAt = null;
    private bool $balDirty = false;
    /** @var array<string, int> cid → ts when first seen partially filled */
    private array $partialSince = [];
    /** @var array<int, bool> levels already warned about failing exchange filters */
    private array $filterSkipLogged = [];
    /** soft de-risk stage: entries paused while a drawdown nears the cap */
    private bool $deRisking = false;
    /** half-cap steps beyond the cap already alerted while killed */
    private int $worseningAlerted = 0;
    /** consecutive ticks the unrealized cap has been breached */
    private int $breachTicks = 0;
    /** consecutive ticks the GLOBAL drawdown floor has been breached */
    private int $ddBreachTicks = 0;
    /** the one-line "flat — entries disabled" notice was written for this ladder */
    private bool $flatLogged = false;
    /** shared-budget invariant broken → entries halted (fail closed) */
    private bool $budgetOvercommitted = false;

    /** seconds between wallet-balance stamps when nothing filled (~15 min) */
    private const BAL_STAMP_INTERVAL = 900;
    /** enter the soft de-risk stage at this fraction of the unrealized cap */
    private const DERISK_ENTER_FRAC = '0.6';
    /** leave it only once the loss recovers under this fraction (hysteresis) */
    private const DERISK_EXIT_FRAC = '0.4';
    /** the unrealized kill needs the breach on this many consecutive ticks —
     *  a single aberrant ticker print (thin-book wick) recovers next tick and
     *  must not kill; a real crash persists and still kills ~2 ticks later */
    private const BREACH_CONFIRM_TICKS = 3;

    /** A partial fill older than this books what filled and cancels the rest
     *  (buy side) or alerts (sell side). Overridable for tests. */
    protected function partialTimeoutSeconds(): int
    {
        return 1800;
    }

    /** @internal engine access — the configured stuck-partial timeout, for an
     *  engine's own partial handling (see TrendEngine::handleStuckPartials).
     *  A thin public wrapper so a test daemon's shortened override (see
     *  DaemonLiveFlowTest::daemonWithPartialTimeout) is still honored: it
     *  stays protected/overridable and this just reads through it. */
    public function enginePartialTimeoutSeconds(): int
    {
        return $this->partialTimeoutSeconds();
    }

    public function __construct(
        /** @internal engine access */
        public readonly GridRun $run,
        /** @internal engine access */
        public readonly GatewayInterface $gateway,
        private readonly bool $dryRun,
        /** @internal engine access */
        public readonly EventLog $log
    ) {
    }

    /** Validate config against live exchange rules, hydrate, reconcile. */
    public function boot(): void
    {
        $info = $this->gateway->exchangeInfo($this->run->getSymbol());
        $this->filters = new Filters($info['filters'] ?? []);

        $runUid = (string) $this->run->getRunUid();
        if ($runUid === '') {
            $runUid = substr(bin2hex(random_bytes(6)), 0, 10);
            $this->run->setRunUid($runUid);
            $this->run->save();
        }
        $this->store = new OrderStore(
            (int) $this->run->getIdGridRun(),
            $runUid,
            $this->run->getLedgerResetAt('Y-m-d H:i:s'),
            (bool) $this->run->getSimulated()
        );
        // A mode flip must not leave the other mode's opens as zombies (and
        // paper opens must never be adopted onto the real exchange). Boot
        // must not block on this either way — it only ever marks rows
        // Canceled in OUR ledger; a paper daemon has no signed gateway and
        // physically cannot touch the exchange, so if the closed rows were
        // REAL, they may still be working on Binance and that must be said
        // loudly (Alert -> Telegram), not buried in a routine boot Warn.
        $closed = $this->store->cancelOtherModeOpens();
        if ($closed > 0) {
            $simulatedNow = (bool) $this->run->getSimulated();
            if ($simulatedNow) {
                // Other mode = REAL. This paper daemon only canceled them in
                // the ledger — it cannot cancel exchange orders.
                $this->log->write('Alert', 'mode_switch', sprintf(
                    '%d real open order(s) closed in the LEDGER ONLY — this paper daemon cannot cancel exchange orders; if any are still working on the exchange, cancel them there manually',
                    $closed
                ));
            } else {
                // Other mode = PAPER zombies — nothing exists on the exchange
                // to strand, so a routine Warn is enough.
                $this->log->write('Warn', 'mode_switch', sprintf(
                    '%d open order(s) from the other mode closed out (run is now %s)',
                    $closed,
                    'REAL'
                ));
            }
        }

        // Requested geometry (row) vs applied geometry (what the working
        // orders were actually placed under). A restart while holding must
        // NOT adopt a pending refit mid-flight — that's maybeRefit's job.
        if (ProfilePolicy::apply($this->run)) {
            $this->run->save();
        }
        $requested = $this->configFromRun();
        $this->config = $requested;
        $openRows = $this->store->openRows();
        $applied = $this->loadAppliedGeometry();
        // GRID ONLY: applied geometry describes a LADDER, so only the grid can
        // be holding a book placed under it. Overlaying it on a non-grid boot
        // resurrected the old grid's stamp — including deploy_pct and
        // budget_quote — over the row's fresh values, so a run flipped to
        // Trend with a new deploy kept trading the dead grid's exposure
        // (caught live by TrendEngine's trend_config_stale warn).
        //
        // The gate keys on the algo that OWNS THE OPEN BOOK (engine_state's
        // marker), not on the requested one: on a flip boot the OUTGOING grid
        // engine still runs first — it reconciles, exits a fill missed while
        // down and hands its working sells over — and it must price all of
        // that on the ladder its orders were actually placed under. Gating on
        // run.algo would hand the outgoing grid the INCOMING geometry, which
        // exits held inventory at the new ladder's lines (below cost) and in
        // the new ladder's per-level qty (more than is held). algoCutover
        // re-anchors the config on the row right after that teardown, so the
        // incoming engine still never sees the dead stamp.
        if (($this->engineStateAlgo() ?: $this->runAlgo()) === 'Grid' && $applied !== null && $openRows
            && $this->geometrySig($applied) !== $this->geometrySig($requested)) {
            $this->config = array_merge($requested, $applied); // ladder from applied; risk knobs stay live
        }
        $this->config['min_notional'] = $this->filters->minNotional();

        $errors = RiskManager::validateRunConfig($this->config);
        if ($errors) {
            $this->log->write('Error', 'config_invalid', implode('; ', $errors));
            throw new \RuntimeException('run config invalid: ' . implode('; ', $errors));
        }
        $this->checkBudgetInvariant();

        $this->buildLadder();

        // Crash recovery: rebuild the machine from persisted open orders...
        $this->machine = GridStateHydrator::machine($this->levels, $this->qtys, $openRows);
        // ...then reconcile against the exchange before ANY placement. The
        // book on the exchange still belongs to the engine that PLACED it, so
        // reconcile runs under that engine (a fill missed while down is
        // exited/booked by its own strategy, even mid-algo-switch).
        $this->bootAlgo = $this->persistedAlgo();
        $this->engine = $this->makeEngine();
        $this->reconcile();

        // Algo switch: tear the previous engine's book down BEFORE anything
        // else looks at it (reconcile ran first so a fill missed while down is
        // booked and exited, never canceled).
        $switched = $this->algoCutover();

        // Every open order must sit on a ladder line. A mismatch (legacy
        // NULL-applied state, corrupt row) means index-based hydration would
        // mis-trade — self-heal instead of guessing. GRID ONLY: no other
        // engine places on ladder lines, so for them an "off-ladder" order is
        // normal, not corruption (a geometry_reset + kill switch on every
        // restart would be the bug).
        // The stamp is grid-only for the same reason: a non-grid run that
        // stamped one would hand the NEXT flip back to Grid a stale ladder to
        // overlay, which is the bug above one flip removed.
        if ($this->runAlgo() === 'Grid') {
            if (!$this->ordersMatchLadder()) {
                $this->geometryReset($requested);
            } elseif ($this->loadAppliedGeometry() === null) {
                $this->stampAppliedGeometry();
            }
        }

        $this->geomSig = $this->geometrySig($this->config);
        $this->bootCodeMtime = $this->codeMtime();

        // From here on the run's REQUESTED algorithm trades.
        $this->bootAlgo = $this->runAlgo();
        $this->engine = $this->makeEngine();
        // the shell owns the marker: this book is now the requested algo's,
        // whether or not its engine ever persists state of its own
        $this->stampEngineAlgo();
        if ($switched) {
            $this->engine->cutover();
        }
        $this->engine->boot();

        $this->log->write('Info', 'boot', sprintf(
            'symbol=%s algo=%s levels=%d spacing=%s dry_run=%s open_orders=%d spacing_pct=%s',
            $this->run->getSymbol(),
            $this->bootAlgo,
            count($this->levels),
            $this->config['spacing'],
            $this->dryRun ? 'yes' : 'NO — LIVE ORDERS',
            $this->store->openCount(),
            GridMath::spacingPct($this->config['p_low'], $this->config['p_high'], (int) $this->config['n_levels'], $this->config['spacing'])
        ));
    }

    /** One loop iteration. Returns false only to trigger a clean restart. */
    public function tick(): bool
    {
        // Self-recovery: a deploy changed the daemon's code on disk → exit
        // cleanly so systemd (Restart=always) relaunches on the new version.
        // Boot reconciles against the exchange, so this needs no manual
        // restart and loses no state — the bot updates itself.
        if ($this->codeChanged()) {
            // distinct from the 'reloading' noise kind: this one marks an
            // algorithm deploy in the event feed and on the dashboard chart
            $this->log->write('Info', 'algo_update', sprintf(
                'algorithm updated on disk (code mtime %s) — restarting to load it',
                date('Y-m-d H:i:s', $this->codeMtime())
            ), ['code_mtime' => $this->codeMtime()]);
            return false;
        }

        $price = $this->gateway->tickerPrice($this->run->getSymbol());

        // Re-read the run row every tick: the GUI/MCP flip kill_switch there.
        // Stamp heartbeat + last seen price (dashboard mark-to-market) +
        // wallet balances (dashboard holdings tiles).
        $this->run->reload();

        // Algorithm switched under us (GUI/MCP wrote run.algo): the cutover
        // has to happen at BOOT — cancel this engine's buys, carry its exits —
        // so exit cleanly and let the supervisor relaunch into the new engine.
        if ($this->runAlgo() !== $this->bootAlgo) {
            $this->log->write('Alert', 'algo_switch', sprintf(
                'algorithm switched %s -> %s — restarting for clean cutover (open buys will be canceled, working sells carried as legacy exits)',
                $this->bootAlgo,
                $this->runAlgo()
            ));
            return false; // clean exit; supervisor relaunches
        }
        // Belt to boot's braces: re-assert the shell's marker every tick (a
        // no-op write-wise unless it went missing), so a marker an engine
        // clobbered self-heals within one tick instead of leaving the next
        // boot to guess which engine owns the open book. Deliberately AFTER
        // the switch check above — while an algo change is pending, the marker
        // must keep naming the engine whose book is still on the exchange.
        $this->stampEngineAlgo();

        // Live risk profile: re-derive caps from profile × current slice —
        // the routine reallocates slices hourly and caps must track them.
        // Merge only the non-geometry (risk-knob) keys: a full replacement
        // would clobber the applied-geometry overlay set at boot ("ladder
        // from applied; risk knobs stay live" — a restart while holding must
        // not adopt a pending refit mid-flight) and drop min_notional
        // (set at boot/refit, not present in configFromRun()). Runs
        // unconditionally every tick — the merge only touches non-geometry
        // knobs, so there is nothing gated on whether apply() actually
        // changed a value.
        ProfilePolicy::apply($this->run);
        $this->config = array_merge($this->config, array_diff_key($this->configFromRun(), array_flip(self::GEOMETRY_KEYS)));

        $this->run->setLastTickAt(date('Y-m-d H:i:s'));
        $this->run->setLastPrice($price);
        $this->stampBalances();
        $this->run->save();

        $this->consumeCommands();

        if ($this->reloadRequested) {
            $this->log->write('Info', 'reloading', 'manual reload requested — restarting (boot rehydrates from the DB)');
            return false;
        }

        if ($this->run->getKillSwitch()) {
            $this->ensureKilled();   // cancel buys once, then idle
            // a killed hold is not a resolved hold — keep marking it to
            // market and escalate as the drawdown deepens
            $this->watchKilledDrawdown($price);
            return true;             // stay alive + heartbeat (no restart loop)
        }
        if ($this->killApplied) {
            // the daemon logs the resume itself so bot_event timestamps all
            // come from ONE clock (web requests run in the user's timezone)
            $this->killApplied = false;
            $this->worseningAlerted = 0;
            $this->log->write('Info', 'restart', 'kill switch cleared — resuming trading');
            $this->rebaseLedgerIfOrphaned();
        }

        if ($this->paused) {
            return true;             // heartbeat only
        }

        // Unrealized-loss stop: realized daily-loss can't see a grid dying
        // slowly underwater. Mark held inventory to market; breach → kill.
        $this->checkUnrealizedStop($price);
        if ($this->run->getKillSwitch()) {
            return true;
        }

        // Wallet-level hard floor: global equity under the drawdown floor
        // (confirmed over BREACH_CONFIRM_TICKS) kills EVERY active run.
        $this->checkGlobalDrawdown();
        if ($this->run->getKillSwitch()) {
            return true;
        }

        // Budget invariant re-checked every tick: the GUI/MCP can change any
        // run's budget_quote at any time and this run must fail closed. It
        // gates the engine's ENTRIES, so it is evaluated before the strategy
        // runs (a slice freed this tick re-enables entries this tick).
        $this->checkBudgetInvariant();

        // The strategy half of the tick — refit detection, pruning, entry
        // intents. Everything else in this loop is shell (control plane,
        // stops, fill detection, ledger) and runs for every algorithm.
        // Entries — and ONLY entries — are gated while de-risking or while the
        // shared budget is overcommitted: exits keep working, new exposure
        // waits. The engine runs BEFORE fill detection, exactly where the grid
        // block always did: the ledger reconciles a fill one tick later, which
        // is what keeps maybeRefit deferring while a partial is being booked
        // (DaemonProfileTest pins this ordering).
        $this->engine->tick($price, $this->deRisking || $this->budgetOvercommitted);

        // Fill detection stays in the shell: every engine's orders — and the
        // engine-independent legacy exits — are resolved the same way, and a
        // resolved fill dispatches its MEANING back to the engine.
        if (!$this->dryRun) {
            $this->detectFills($price);
            // grid-shaped only (LevelStateMachine::onPartialBuyBooked +
            // $levels[level+1]) — a non-grid engine owns its own partial
            // handling (see TrendEngine's class docblock)
            if ($this->runAlgo() === 'Grid') {
                $this->checkStuckPartials($price);
            }
        }

        $this->log->flush(); // coalesce this tick's trade notifications into one message
        return true;
    }

    // ── strategy engine seam ────────────────────────────────────────────

    /** run.algo, defaulted (legacy rows predate the column). */
    private function runAlgo(): string
    {
        return (string) ($this->run->getAlgo() ?: 'Grid');
    }

    /** engine_state's own `algo` marker — the algorithm that placed the
     *  current book. Any engine persisting state stamps it; '' = never
     *  stamped. */
    private function engineStateAlgo(): string
    {
        return (string) ($this->engineState()['algo'] ?? '');
    }

    /** The algorithm the CURRENT book belongs to. With no marker, the LADDER
     *  is the evidence: a grid-shaped open book (orders on the ladder's lines)
     *  is a pre-marker grid book, so the grid engine resolves it — a fill
     *  missed while down gets its exit, and the cutover then carries that exit
     *  as legacy. Anything else falls back to the run's own algo: guessing
     *  'Grid' there would hand a foreign book to the grid engine, which would
     *  book its ladder's qty against a position it does not own (an oversized
     *  exit, placed silently). */
    private function persistedAlgo(): string
    {
        return $this->engineStateAlgo() ?: ($this->hasOpenGridShapedBook() ? 'Grid' : $this->runAlgo());
    }

    /**
     * Persist the algo marker cutover detection keys on. The SHELL owns this
     * write — every boot, not just a cutover — so detection never depends on
     * an engine remembering to persist state: a process that dies between the
     * cutover and the incoming engine's first state write would otherwise look
     * like "unmarked book under a non-grid algo" and tear down the NEW
     * engine's own orders on the next boot.
     *
     * Engines keep their own keys in the same JSON; only `algo` is the shell's
     * (a cutover resets the object, since the outgoing engine's keys are
     * meaningless to the incoming one).
     */
    private function stampEngineAlgo(bool $reset = false): void
    {
        if (!$reset && $this->engineStateAlgo() === $this->runAlgo()) {
            return; // already marked — no write
        }
        $this->writeEngineState($reset ? [] : $this->engineState());
    }

    /**
     * @internal engine access — the ONLY way an engine persists its own state.
     *
     * Merges $keys into the stored JSON and (re)writes the shell's `algo`
     * marker in the same save, so an engine can never drop it: the column is
     * shared state, and a lost marker means the next boot cannot tell which
     * engine the open book belongs to.
     *
     * @param array<string, mixed> $keys engine-owned keys to merge
     */
    public function persistEngineState(array $keys): void
    {
        $this->writeEngineState(array_merge($this->engineState(), $keys));
    }

    /** Has this run EVER carried engine state? An empty column means the run
     *  predates the marker entirely (its open book can only be a grid book);
     *  a populated one without an `algo` key means an engine overwrote it. */
    private function hasEngineState(): bool
    {
        return trim((string) ($this->run->getEngineState() ?? '')) !== '';
    }

    /** Open orders belonging to the current engine (legacy exits are
     *  engine-independent and never count). */
    private function openEngineOrderCount(): int
    {
        $n = 0;
        foreach ($this->store->openOrderObjects() as $row) {
            if (!$row->getIsLegacy()) {
                $n++;
            }
        }
        return $n;
    }

    /** Is the open book shaped like a grid's — orders sitting on the ladder's
     *  own lines? Only the grid places there, so this is the evidence that
     *  tells a pre-marker grid book from another engine's book. */
    private function hasOpenGridShapedBook(): bool
    {
        return $this->openEngineOrderCount() > 0 && $this->ordersMatchLadder();
    }

    /** @return array<string, mixed> the decoded engine_state (never null) */
    private function engineState(): array
    {
        $state = json_decode((string) ($this->run->getEngineState() ?? ''), true);
        return is_array($state) ? $state : [];
    }

    /** @param array<string, mixed> $state */
    private function writeEngineState(array $state): void
    {
        $state['algo'] = $this->runAlgo();
        $this->run->setEngineState(json_encode(['algo' => $state['algo']] + $state));
        $this->run->save();
    }

    private function makeEngine(): Engine\StrategyEngine
    {
        return match ($this->bootAlgo) {
            'Grid' => new Engine\GridEngine($this),
            'Trend' => new Engine\TrendEngine($this),
            default => new Engine\MissingEngine($this, $this->bootAlgo),
        };
    }

    /**
     * One-time cutover when run.algo changed since the engine state was last
     * written. The previous engine's book cannot be handed to the new one:
     * open buys are canceled (long-only → no naked exposure) and working
     * sells are carried as LEGACY EXITS — they guard real inventory at their
     * correct prices and the shell resolves them whatever engine is running
     * (exactly what a refit does, see maybeRefit). The persisted engine_state
     * is dropped so the incoming engine starts clean.
     *
     * The marker is engine_state's own `algo` key, written by the shell. Three
     * unmarked cases are told apart by the column and the ladder:
     *   - state present, marker missing → an engine clobbered it; that book is
     *     its own, leave it (the marker heals at boot / next tick);
     *   - no state at all + grid-shaped book → pre-marker grid run, cut over;
     *   - no state at all + off-ladder book → STRANDED: nothing can exit it,
     *     so alert and kill rather than pass silently.
     *
     * @return bool true if a switch was detected and the teardown ran
     */
    private function algoCutover(): bool
    {
        $algo = $this->runAlgo();
        $prev = $this->engineStateAlgo();
        $open = $this->openEngineOrderCount();

        if ($prev !== '') {
            $switched = $prev !== $algo;
        } elseif ($this->hasEngineState()) {
            // state exists but the marker is gone: an engine clobbered it (the
            // shell heals the marker below). The book is that engine's own —
            // tearing it down would be the bug.
            $switched = false;
        } elseif ($algo !== 'Grid' && $open > 0 && !$this->ordersMatchLadder()) {
            // NO engine state at all → this run predates the marker, so its
            // open book is a GRID book. Off-ladder means it belongs to no
            // coherent ladder either: it cannot be cut over (level ownership
            // is ambiguous) and the non-grid engine will never exit it. That
            // used to pass silently — and the marker stamped right after
            // closed the window forever, leaving entries filling with no
            // exits. Stop and hand it to a human instead.
            $this->log->write('Alert', 'stranded_book', sprintf(
                '%d open grid order(s) match no ladder and algo=%s has no way to exit them — canceling entries, HOLDING inventory, kill switch ON for review',
                $open,
                $algo
            ));
            $this->run->setKillSwitch(true);
            $this->run->save();
            $this->flattenOnKill = false; // hold inventory; a human decides
            $this->ensureKilled();
            return false;
        } else {
            // pre-marker GRID book under a non-grid algo, sitting on the
            // ladder: the genuine migration case — cut it over.
            $switched = $algo !== 'Grid' && $open > 0;
        }

        if (!$switched) {
            return false;
        }

        $booked = $this->cancelEntriesBookingFills();
        // Inventory the OUTGOING engine holds in its OWN state — not on the
        // ladder, not behind a working sell — would vanish here: $this->engine
        // is still the outgoing (hydrated) engine, but stampEngineAlgo(true)
        // below erases the state that is the only record of it, and the
        // incoming engine's heldQty() then reads 0 (blind unrealized stop) and
        // sizes its book on the full budget (no reserve). Give the outgoing
        // engine the one chance it has to hand that position off as a legacy
        // exit — engine-independent, shell-resolved, reserve-counted. Runs
        // BEFORE carrySellsAsLegacy on purpose: while the engine's own working
        // exits are still non-legacy, "already guarded" is decidable (after the
        // carry they are indistinguishable from older legacy rows, and the
        // handoff would double-guard the same inventory).
        $this->engine->handoffUnguardedPosition();
        $carried = $this->store->carrySellsAsLegacy();
        // A cutover abandons the outgoing engine's book, so the incoming
        // engine anchors on the RUN ROW — never on the geometry stamp of the
        // book being torn down (boot's overlay may have merged it in).
        $this->config = $this->configFromRun();
        $this->config['min_notional'] = $this->filters->minNotional();
        // boot validated the config it had — which, on a flip boot, was the
        // OUTGOING ladder's (the overlay above). The row's own geometry has
        // not been through the gate at all, so validate it here too: "boot
        // never runs unvalidated geometry" stays unconditional.
        $errors = RiskManager::validateRunConfig($this->config);
        if ($errors) {
            $this->log->write('Error', 'config_invalid', implode('; ', $errors));
            throw new \RuntimeException('run config invalid: ' . implode('; ', $errors));
        }
        // ...and the stamp itself goes: it is grid-only, it describes a ladder
        // that no longer holds anything, and leaving it behind let a later
        // flip back to Grid resurrect a dead ladder (with its dead deploy_pct
        // and budget) instead of re-anchoring fresh. Boot re-stamps right
        // after this when the INCOMING algo is the grid.
        $this->run->setAppliedGeometry(null);
        $this->run->save();
        // rebuild an empty ladder on the run's geometry: the carried exits are
        // off-machine now (their capital is reserved by buildLadder), so the
        // machine must not keep claiming their inventory
        $this->buildLadder();
        $this->machine = new LevelStateMachine($this->levels, $this->qtys);
        $this->stampEngineAlgo(true); // fresh marker; the old engine's keys go
        $this->log->write('Alert', 'algo_cutover', sprintf(
            'algorithm cutover %s -> %s — open entries canceled%s, %d working exit(s) carried as legacy, engine state reset',
            $prev !== '' ? $prev : 'Grid',
            $algo,
            $booked > 0 ? sprintf(' (%d had already filled — booked and exited)', $booked) : '',
            $carried
        ));
        return true;
    }

    /**
     * Cutover teardown of the outgoing engine's entries. Unlike the plain
     * cancelOpenBuys(), this NEVER discards a fill: a buy that is partially
     * (or fully) filled when the cancel lands has real inventory behind it, so
     * the executed qty is booked and immediately guarded by a legacy exit at
     * the ladder line above it. maybeRefit refuses to run in this state at
     * all; a cutover cannot refuse (the incoming engine must not inherit the
     * old book), so it settles the position instead.
     *
     * @return int how many canceled entries carried an executed qty
     */
    private function cancelEntriesBookingFills(): int
    {
        $booked = 0;
        foreach ($this->store->openOrderObjects() as $row) {
            if ((string) $row->getSide() !== 'Buy') {
                continue;
            }
            $cid = $row->getClientOrderId();
            $res = null;
            if (!$this->dryRun) {
                try {
                    $res = $this->gateway->cancelOrder($this->run->getSymbol(), $cid);
                } catch (Gateway\BinanceApiError $e) {
                    $this->logCancelFailure($e, $cid, 'algo cutover');
                    continue;
                }
            }
            $level = (int) $row->getLevelIdx();
            $executed = (string) ($res['executedQty'] ?? $row->getFilledQty() ?? '0');
            if (bccomp($executed, '0', 8) <= 0) {
                $this->store->markCanceled($row);
                $this->machine->hydrateLevel($level, 'EMPTY');
                continue;
            }
            $fee = bcmul(bcmul((string) $row->getPrice(), $executed, 12), $this->config['fee_pct'], 12);
            $feeExact = $this->exactFee((string) ($row->getExchangeOrderId() ?? ''), (string) $row->getPrice());
            if ($feeExact !== null) {
                $fee = $feeExact;
            }
            $this->store->markFilled($row, $executed, $fee);
            $this->balDirty = true;
            $booked++;
            $this->log->write('Alert', 'cutover_partial_booked', sprintf(
                'L%02d entry had filled %s/%s when the cutover canceled it — booked; carrying an exit for it',
                $level, $executed, $row->getQty()
            ));
            // the ladder is about to be discarded, so the exit is placed as a
            // LEGACY exit right away — off-machine, resolved by the shell.
            // Priced one ladder line up ONLY for a grid book (that IS the
            // grid's own exit for this level); any other engine's entry has no
            // ladder line to take profit on, so it exits at its own cost —
            // a ladder price unrelated to what it paid could sell it at an
            // arbitrary loss the moment the cutover lands. The matched buy is
            // pinned on the row (this row, its price, its fee) so the cycle is
            // booked against it and cannot be misattributed later.
            $exitPrice = $this->bootAlgo === 'Grid'
                ? (string) ($this->levels[$level + 1] ?? $row->getPrice())
                : (string) $row->getPrice();
            $this->placeLegacyExit($level, $exitPrice, $executed, (string) $row->getPrice(), $fee);
        }
        return $booked;
    }

    // ── self-recovery + auto-refit ──────────────────────────────────────

    private function codeChanged(): bool
    {
        return $this->bootCodeMtime !== null && $this->codeMtime() > $this->bootCodeMtime;
    }

    /** Newest mtime across the daemon's own source (Bot domain + gateway + entry scripts). */
    private function codeMtime(): int
    {
        $max = 0;
        $sets = [
            glob(__DIR__ . '/*.php') ?: [],
            glob(__DIR__ . '/Gateway/*.php') ?: [],
            glob(__DIR__ . '/Engine/*.php') ?: [],
            glob(__DIR__ . '/../../../../bin/gtbot*') ?: [],
        ];
        foreach ($sets as $set) {
            foreach ($set as $f) {
                $max = max($max, (int) @filemtime($f));
            }
        }
        return $max;
    }

    /** The keys that define the ladder (vs live risk knobs). */
    private const GEOMETRY_KEYS = ['p_low', 'p_high', 'n_levels', 'spacing', 'allocation', 'budget_quote', 'fee_pct', 'deploy_pct'];

    private function geometrySig(array $config): string
    {
        // numeric keys are normalized: '100' and '100.00000000' are the SAME
        // geometry (in-memory vs DB-reloaded decimals) — a formatting
        // difference must never read as a pending refit
        $numeric = ['p_low' => 1, 'p_high' => 1, 'budget_quote' => 1, 'fee_pct' => 1];
        return implode('|', array_map(
            static function (string $k) use ($config, $numeric): string {
                $v = (string) ($config[$k] ?? '');
                return isset($numeric[$k]) && is_numeric($v) ? bcadd($v, '0', 8) : $v;
            },
            self::GEOMETRY_KEYS
        ));
    }

    /** @internal engine access — a pending geometry write on the run row */
    public function geometryChanged(): bool
    {
        return $this->geometrySig($this->configFromRun()) !== $this->geomSig;
    }

    private function buildLadder(): void
    {
        $this->levels = GridMath::levels(
            $this->config['p_low'],
            $this->config['p_high'],
            (int) $this->config['n_levels'],
            $this->config['spacing']
        );
        // deploy_pct: the routine's exposure lever — "not fit" can mean a
        // SMALLER ladder, not just a wider one (the sweeps showed the sign of
        // a grid week is decided by regime exposure, not spacing).
        $budget = $this->config['budget_quote'];
        $deployPct = (int) ($this->config['deploy_pct'] ?? 100);
        if ($deployPct <= 0) {
            // FLAT: zero ladder budget — every buy level fails minQty/minNotional
            // and is skipped (filter_skip), while working sells/legacy exits
            // keep working. This is a position, not an error.
            $budget = '0';
        } elseif ($deployPct < 100) {
            $budget = bcdiv(bcmul($budget, (string) $deployPct, 12), '100', 12);
        }
        // capital still tied up in legacy exits is NOT re-spendable — the new
        // ladder allocates only what's actually free (valued at exit price,
        // slightly conservative). Frees up as each legacy exit fills.
        $reserve = $this->store->legacyReserveQuote();
        if (bccomp($reserve, '0', 12) > 0) {
            $budget = bccomp($budget, $reserve, 12) > 0 ? bcsub($budget, $reserve, 12) : '0';
        }
        $this->qtys = GridMath::allocate(
            $budget,
            array_slice($this->levels, 0, -1),
            $this->config['allocation']
        );
        $this->filterSkipLogged = [];
        $this->flatLogged = false;
    }

    /** @return array<string,string>|null geometry the current ladder was built on */
    private function loadAppliedGeometry(): ?array
    {
        $raw = (string) ($this->run->getAppliedGeometry() ?? '');
        if ($raw === '') {
            return null;
        }
        $geom = json_decode($raw, true);
        return is_array($geom) ? array_intersect_key($geom, array_flip(self::GEOMETRY_KEYS)) : null;
    }

    /** Persist the geometry the ladder is ACTUALLY built on (daemon-owned). */
    private function stampAppliedGeometry(): void
    {
        $geom = [];
        foreach (self::GEOMETRY_KEYS as $k) {
            $geom[$k] = (string) $this->config[$k];
        }
        $this->run->setAppliedGeometry(json_encode($geom));
        $this->run->save();
    }

    /** Every open order must sit on its ladder line (0.2% tick tolerance).
     *  Legacy exits are EXPECTED off-ladder — they belong to a prior geometry. */
    private function ordersMatchLadder(): bool
    {
        foreach ($this->store->openOrderObjects() as $row) {
            if ($row->getIsLegacy()) {
                continue;
            }
            $i = (int) $row->getLevelIdx();
            $line = (string) $row->getSide() === 'Buy'
                ? ($this->levels[$i] ?? null)
                : ($this->levels[$i + 1] ?? null);
            if ($line === null) {
                return false;
            }
            if (abs((float) $row->getPrice() - (float) $line) / (float) $line > 0.002) {
                return false;
            }
        }
        return true;
    }

    /**
     * Open orders don't fit any coherent ladder (legacy pre-applied-geometry
     * state, corrupt row): index-based hydration would mis-trade, and level
     * ownership of held inventory is inherently ambiguous. Self-heal: cancel
     * ALL our open orders (long-only → no naked exposure), rebuild an empty
     * ladder on the requested geometry, trip the kill switch and alert —
     * a human clears the switch to start clean.
     */
    private function geometryReset(array $requested): void
    {
        foreach ($this->store->openOrderObjects() as $row) {
            if (!$this->dryRun) {
                try {
                    $this->gateway->cancelOrder($this->run->getSymbol(), $row->getClientOrderId());
                } catch (Gateway\BinanceApiError $e) {
                    $this->log->write('Error', 'cancel_failed', $row->getClientOrderId() . ': ' . $e->getMessage());
                    continue;
                }
            }
            $this->store->markCanceled($row);
        }
        $this->config = $requested;
        $this->config['min_notional'] = $this->filters->minNotional();
        $this->buildLadder();
        $this->machine = new LevelStateMachine($this->levels, $this->qtys);
        $this->stampAppliedGeometry();
        $this->stampDecisionApplied();
        // start a fresh ledger era: the orphaned inventory's cost basis must
        // not feed invested/unrealized math for the new grid
        $epoch = date('Y-m-d H:i:s');
        $this->run->setLedgerResetAt($epoch);
        $this->store->setLedgerEpoch($epoch);
        $this->run->setKillSwitch(true);
        $this->run->save();
        $this->killApplied = true; // orders already canceled; idle from here

        $bought = '0';
        foreach (\App\BotOrderQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterBySimulated((bool) $this->run->getSimulated())
            ->filterBySide('Buy')->filterByState('Filled')->find() as $o) {
            $bought = bcadd($bought, (string) $o->getFilledQty(), 8);
        }
        foreach (\App\TradeCycleQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterBySimulated((bool) $this->run->getSimulated())
            ->find() as $c) {
            $bought = bcsub($bought, (string) $c->getQty(), 8);
        }
        $this->log->write('Alert', 'geometry_reset', sprintf(
            'open orders did not match the ladder — all canceled; ~%s base held untracked in the wallet; kill switch ON, review and re-enable to start clean',
            $bought
        ));
    }

    /**
     * Re-anchor the grid to the run row's new geometry. Holding inventory no
     * longer blocks this: open buys are cancelled, working sells are carried
     * as LEGACY EXITS (kept on the exchange, detached from the ladder — they
     * exit real inventory at their correct prices), and the new buy ladder
     * starts immediately on the budget those exits don't tie up. Defers only
     * while a partially-filled buy is still being booked (its inventory
     * isn't backed by a sell yet, so tagging sells legacy would orphan it).
     */
    /** @internal engine access */
    public function maybeRefit(string $price): void
    {
        $partialBuys = 0;
        foreach ($this->store->openOrderObjects() as $row) {
            if ((string) $row->getSide() === 'Buy' && (string) $row->getState() === 'PartFilled') {
                $partialBuys++;
            }
        }
        if ($partialBuys > 0
            || bccomp($this->machine->heldInventory(), $this->machine->openSellQty(), 8) > 0) {
            // keep the old grid; geomSig unchanged so we re-check next tick.
            // Log ONCE per pending geometry (not every tick) — else it floods.
            $sig = $this->geometrySig($this->configFromRun());
            if ($sig !== $this->refitPendingSig) {
                $this->refitPendingSig = $sig;
                $this->log->write('Warn', 'refit_pending', 'refit deferred — a partial fill is still being booked');
            }
            return;
        }
        $this->refitPendingSig = '';
        // Refuse a fee-negative / incoherent geometry (e.g. a bad manual write);
        // the refit cron only writes floor-checked ranges, but guard anyway.
        $newConfig = $this->configFromRun();
        $newConfig['min_notional'] = $this->filters->minNotional();
        $errors = RiskManager::validateRunConfig($newConfig);
        if ($errors) {
            $this->geomSig = $this->geometrySig($newConfig); // don't retry this bad geometry
            $this->log->write('Error', 'refit_rejected', 'new geometry rejected: ' . implode('; ', $errors) . ' — keeping current grid');
            return;
        }
        $this->cancelOpenBuys();
        $legacyCount = $this->store->carrySellsAsLegacy();
        $this->config = $newConfig;
        $this->buildLadder();
        $this->machine = new LevelStateMachine($this->levels, $this->qtys);
        $this->stampAppliedGeometry();
        $this->stampDecisionApplied();
        $this->geomSig = $this->geometrySig($this->config);
        $this->log->write('Info', 'refit_applied', sprintf(
            'grid re-anchored to [%s, %s] × %d levels%s',
            $this->config['p_low'],
            $this->config['p_high'],
            (int) $this->config['n_levels'],
            $legacyCount > 0 ? sprintf(' — %d exit(s) carried as legacy', $legacyCount) : ''
        ));
        $this->checkBudgetInvariant();
    }

    /** All runs share ONE wallet: their budget slices must not sum above the
     *  shared budget. ENFORCED fail-closed (operator directive: never
     *  overcommit) — while the invariant is broken this daemon places no new
     *  entries (exits keep working, reducing exposure). The write paths
     *  (GridRunServiceWrapper::beforeSave, gtbot_create_run) refuse the
     *  overcommitting save in the first place; this gate catches anything
     *  that slipped past them. Transition-logged so a stuck state alerts
     *  once, not every tick. The sum is deliberately GLOBAL — every
     *  DryRun/Testnet/Live GridRun, not just $this->run — because the wallet
     *  itself is shared across all of them; do not "fix" this into a
     *  per-run filter. */
    private function checkBudgetInvariant(): void
    {
        $over = BudgetGuard::check();
        if ($over !== null && !$this->budgetOvercommitted) {
            $this->budgetOvercommitted = true;
            $this->log->write('Alert', 'budget_overcommit', sprintf(
                'run budget slices sum to %s but the shared budget is %s — NEW ENTRIES HALTED until reallocated (routine/GUI) or gtbot_shared_budget_quote is raised',
                $over['sum'],
                $over['budget']
            ));
        } elseif ($over === null && $this->budgetOvercommitted) {
            $this->budgetOvercommitted = false;
            $this->log->write('Info', 'budget_ok', 'budget slices fit the shared budget again — entries re-enabled');
        }
    }

    /**
     * A refit decision's evaluation clock must start when its geometry
     * actually TAKES EFFECT — mark the newest matching un-applied decision.
     * Without this, decisions were scored on whatever the old grid traded
     * while they sat refit_pending (the track record was noise).
     */
    private function stampDecisionApplied(): void
    {
        $d = \App\BotDecisionQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByAppliedAt(null, \Criteria::ISNULL)
            ->orderByIdBotDecision(\Criteria::DESC)
            ->findOne();
        if ($d
            && bccomp((string) $d->getPLow(), $this->config['p_low'], 8) === 0
            && bccomp((string) $d->getPHigh(), $this->config['p_high'], 8) === 0
            && (int) $d->getNLevels() === (int) $this->config['n_levels']) {
            $d->setAppliedAt(date('Y-m-d H:i:s'));
            $d->save();
        }
    }

    /** Stamp free+locked wallet balances for the run's assets onto the run
     *  row. Signed endpoint → skipped in dry-run; failures are non-fatal (the
     *  last stamped values stay). Throttled: refreshes on the first live tick,
     *  after a fill was processed (balDirty), or every BAL_STAMP_INTERVAL —
     *  totals only move on fills, so quiet ticks need no re-fetch. */
    private function stampBalances(): void
    {
        if ($this->dryRun) {
            return;
        }
        if (!$this->balDirty
            && $this->balStampedAt !== null
            && time() - $this->balStampedAt < self::BAL_STAMP_INTERVAL) {
            return;
        }
        try {
            $bal = $this->gateway->accountBalances();
        } catch (\Throwable) {
            return;
        }
        [$base, $quote] = $this->symbolAssets();
        $tot = static fn (string $asset): string => bcadd(
            (string) ($bal[$asset]['free'] ?? '0'),
            (string) ($bal[$asset]['locked'] ?? '0'),
            8
        );
        $this->run->setBalBase($tot($base));
        $this->run->setBalQuote($tot($quote));
        $this->balStampedAt = time();
        $this->balDirty = false;
    }

    /** [base, quote] assets parsed from the run symbol ("BTCUSDT" → BTC/USDT). */
    private function symbolAssets(): array
    {
        $symbol = (string) $this->run->getSymbol();
        $quote = str_ends_with($symbol, 'USDT') ? 'USDT' : substr($symbol, -3);
        return [substr($symbol, 0, strlen($symbol) - strlen($quote)), $quote];
    }

    /**
     * Staged unrealized-loss stop. At DERISK_ENTER_FRAC of the cap: soft
     * stage — cancel open buys and pause entries (exits keep working), so
     * the grid stops adding exposure into a drawdown instead of buying all
     * the way down to the hard stop (prod 2026-07-22: the binary stop fired
     * 65% past its cap). Recovers with hysteresis at DERISK_EXIT_FRAC. At
     * 100% of the cap: kill and hold — but only once the breach has held
     * BREACH_CONFIRM_TICKS consecutive ticks (prod 2026-07-28: a testnet
     * thin-book wick printed 1500 for one tick and killed run 6 while the
     * real market never left ~1880; the mark is a single tickerPrice read).
     */
    private function checkUnrealizedStop(string $price): void
    {
        $cap = (string) ($this->run->getMaxUnrealizedLossQuote() ?? '');
        if ($cap === '' || bccomp($cap, '0', 8) <= 0) {
            return; // disabled
        }
        // legacy exits guard real inventory too — the stop must see it. The
        // engine owns what "position" means for its own algorithm (a ladder's
        // held qty for Grid, an open trend position for Trend) — heldQty()
        // folds legacy in too, so this stays algo-agnostic.
        $inventory = $this->engine->heldQty();
        if (bccomp($inventory, '0', 8) <= 0) {
            $this->breachTicks = 0;
            if ($this->deRisking) {
                $this->deRisking = false;
                $this->log->write('Info', 'derisk_off', 'flat — entries re-armed');
            }
            return;
        }
        $invested = $this->store->investedQuote();
        $unrealized = bcsub(bcmul($inventory, $price, 12), $invested, 12);

        if (bccomp($unrealized, bcmul('-1', $cap, 12), 12) < 0) {
            if (++$this->breachTicks < self::BREACH_CONFIRM_TICKS) {
                if ($this->breachTicks === 1) {
                    $this->log->write('Warn', 'unrealized_breach', sprintf(
                        'unrealized loss %s exceeds cap %s (inventory %s @ %s) — confirming over %d ticks before kill (wick guard)',
                        bcadd($unrealized, '0', 2), $cap, $inventory, $price, self::BREACH_CONFIRM_TICKS
                    ));
                }
                // not confirmed — fall through to the soft de-risk stage so
                // entries still pause and open buys are cancelled meanwhile
            } else {
                $this->breachTicks = 0;
                $this->flattenOnKill = false; // hold inventory; human decides
                $this->run->setKillSwitch(true);
                $this->run->save();
                $this->log->write('Alert', 'unrealized_stop', sprintf(
                    'unrealized loss %s exceeds cap %s (inventory %s @ %s) — killed, holding inventory',
                    bcadd($unrealized, '0', 2), $cap, $inventory, $price
                ));
                $this->ensureKilled();
                return;
            }
        } else {
            $this->breachTicks = 0;
        }

        $enter = bcmul('-1', bcmul($cap, self::DERISK_ENTER_FRAC, 12), 12);
        $exit = bcmul('-1', bcmul($cap, self::DERISK_EXIT_FRAC, 12), 12);
        if (!$this->deRisking && bccomp($unrealized, $enter, 12) <= 0) {
            $this->deRisking = true;
            $this->log->write('Alert', 'derisk_on', sprintf(
                'unrealized loss %s at ≥%s%% of cap %s — pausing entries and cancelling open buys; exits keep working',
                bcadd($unrealized, '0', 2), bcmul(self::DERISK_ENTER_FRAC, '100', 0), $cap
            ));
            $this->cancelOpenBuys();
        } elseif ($this->deRisking && bccomp($unrealized, $exit, 12) > 0) {
            $this->deRisking = false;
            $this->log->write('Info', 'derisk_off', sprintf(
                'unrealized loss recovered to %s (under %s%% of cap) — entries re-armed',
                bcadd($unrealized, '0', 2), bcmul(self::DERISK_EXIT_FRAC, '100', 0)
            ));
        }
    }

    /** While killed and still holding: alert each additional half-cap of
     *  mark-to-market loss — a worsening hold must never be silent. */
    private function watchKilledDrawdown(string $price): void
    {
        $cap = (string) ($this->run->getMaxUnrealizedLossQuote() ?? '');
        $hasCap = $cap !== '' && bccomp($cap, '0', 8) > 0;
        // NoLoss disables the unrealized-loss STOP by policy (its
        // MaxUnrealizedLossQuote is null — see ProfilePolicy::RULES), but
        // that disables the STOP, not the TELEMETRY: a killed NoLoss run can
        // still bleed paper losses while holding, and "a worsening hold must
        // never be silent" applies regardless of profile. Without a cap to
        // step off of, derive an alert-only escalation unit from the budget
        // slice (mirrors the Balanced-profile unrealized ratio) — this
        // changes nothing about whether/when the run stops, only how loudly
        // a deepening hold is reported.
        $unit = $hasCap ? bcdiv($cap, '2', 12) : bcmul((string) $this->run->getBudgetQuote(), '0.15', 8);
        if (bccomp($unit, '0', 12) <= 0) {
            return; // no cap and no budget to derive a unit from — nothing to escalate against
        }
        $inventory = $this->engine->heldQty();
        if (bccomp($inventory, '0', 8) <= 0) {
            return;
        }
        $unrealized = bcsub(bcmul($inventory, $price, 12), $this->store->investedQuote(), 12);
        $beyond = $hasCap
            ? bcsub(bcmul('-1', $unrealized, 12), $cap, 12)
            : bcmul('-1', $unrealized, 12); // uncapped: escalate from the first dollar of loss
        if (bccomp($beyond, '0', 12) <= 0) {
            return; // inside the cap again (or, uncapped, not currently losing) — nothing to escalate
        }
        $steps = (int) bcdiv($beyond, $unit, 0);
        if ($steps > $this->worseningAlerted) {
            $this->worseningAlerted = $steps;
            $this->log->write('Alert', 'unrealized_worsening', $hasCap
                ? sprintf(
                    'killed hold is bleeding: unrealized %s vs cap %s (inventory %s @ %s) — flatten or exit manually',
                    bcadd($unrealized, '0', 2), $cap, $inventory, $price
                )
                : sprintf(
                    'killed hold is bleeding: unrealized %s (NoLoss — no stop cap, alert-only) (inventory %s @ %s) — flatten is forbidden by policy; exit manually',
                    bcadd($unrealized, '0', 2), $inventory, $price
                ));
        }
    }

    /**
     * Global max-drawdown stop (operator directive 2026-08-01). One shared
     * wallet → one floor: equity below budget×(1−pct/100), held
     * BREACH_CONFIRM_TICKS consecutive ticks (same wick guard as the
     * unrealized stop), kills ALL active runs — buys cancel, inventory is
     * HELD. The hourly routine owns the selloff/restart decision
     * (refit-routine.md); a restart below the floor re-trips here in 3
     * ticks, so resuming requires re-baselining the shared budget first.
     * Killed/paused daemons never reach this check — any one live daemon
     * is enough to trip the whole system, and a tripped system can't
     * re-fire (every daemon lands in the killed branch above).
     */
    private function checkGlobalDrawdown(): void
    {
        $over = DrawdownGuard::check();
        if ($over === null) {
            $this->ddBreachTicks = 0;
            return;
        }
        $unpriced = $over['unpriced'] !== []
            ? sprintf(' (unpriced, counted as 0: %s)', implode(', ', $over['unpriced']))
            : '';
        if (++$this->ddBreachTicks < self::BREACH_CONFIRM_TICKS) {
            if ($this->ddBreachTicks === 1) {
                $this->log->write('Warn', 'drawdown_breach', sprintf(
                    'global equity %s under drawdown floor %s (budget %s)%s — confirming over %d ticks before stop (wick guard)',
                    bcadd($over['equity'], '0', 2),
                    bcadd($over['floor'], '0', 2),
                    $over['budget'],
                    $unpriced,
                    self::BREACH_CONFIRM_TICKS
                ));
            }
            return;
        }
        $this->ddBreachTicks = 0;
        $killed = DrawdownGuard::tripAll();
        $this->run->reload(); // pick up our own kill_switch from tripAll
        $this->flattenOnKill = false; // hold inventory; routine decides
        $this->log->write('Alert', 'drawdown_stop', sprintf(
            'GLOBAL DRAWDOWN STOP: equity %s under floor %s (max drawdown %s%% of budget %s)%s — %d run(s) killed, buys canceled, inventory HELD; the routine decides selloff/restart',
            bcadd($over['equity'], '0', 2),
            bcadd($over['floor'], '0', 2),
            $over['pct'],
            $over['budget'],
            $unpriced,
            $killed
        ));
        $this->ensureKilled();
    }

    private function ensureKilled(): void
    {
        if ($this->killApplied) {
            return;
        }
        $this->killApplied = true;
        $this->onKill($this->flattenOnKill);
    }

    // ── placement ───────────────────────────────────────────────────────

    /** @internal engine access — risk review + filters + placement */
    public function placeIntent(IntendedOrder $o, string $price): void
    {
        $snapshot = [
            'market_price' => $price,
            'invested_quote' => $this->store->investedQuote(),
            'realized_pnl_today' => $this->store->realizedToday(),
            'open_orders' => $this->store->openCount(),
            'kill_switch' => (bool) $this->run->getKillSwitch(),
        ];
        $d = RiskManager::review($o, $snapshot, $this->config);
        if ($d->kill) {
            $this->flattenOnKill = $d->flatten;
            $this->run->setKillSwitch(true);
            $this->run->save();
            $this->log->write('Alert', 'risk_kill', $d->reason);
            $this->ensureKilled();
            return;
        }
        if (!$d->approved) {
            if ($d->halt) {
                $this->log->write('Alert', 'breakout_halt', $d->reason);
            } else {
                $this->store->recordVeto($o, $d->reason);
                $this->log->write('Warn', 'veto', sprintf('L%02d %s @ %s: %s', $o->levelIdx, $o->side, $o->price, $d->reason));
            }
            // un-arm so the level can retry on a later tick when conditions change
            if ($o->side === 'Buy') {
                $this->machine->hydrateLevel($o->levelIdx, 'EMPTY');
            }
            return;
        }
        $norm = $this->filters->normalize($o->side, $o->price, $o->qty);
        if ($norm === null) {
            if ($o->side === 'Buy' && (int) ($this->config['deploy_pct'] ?? 100) <= 0) {
                // flat is a position: every buy level fails filters on a zero
                // budget by design — one Info line, not a Warn per level
                if (!$this->flatLogged) {
                    $this->flatLogged = true;
                    $this->log->write('Info', 'flat', 'entries disabled at deploy 0% — buy levels idle (exits keep working)');
                }
            } elseif (empty($this->filterSkipLogged[$o->levelIdx])) {
                $this->filterSkipLogged[$o->levelIdx] = true;
                $this->log->write('Warn', 'filter_skip', sprintf('L%02d cannot clear minQty/minNotional — level skipped', $o->levelIdx));
            }
            // un-arm so the level can retry once conditions change (e.g. a
            // legacy exit fills and frees budget on the next refit)
            if ($o->side === 'Buy') {
                $this->machine->hydrateLevel($o->levelIdx, 'EMPTY');
            }
            return;
        }

        if ($this->dryRun) {
            $this->log->write('Info', 'would_place', sprintf(
                '%s L%02d @ %s qty %s (market %s)',
                $o->side, $o->levelIdx, $norm['price'], $norm['qty'], $price
            ), ['side' => $o->side, 'level' => $o->levelIdx, 'price' => $norm['price'], 'qty' => $norm['qty']]);
            return;
        }

        $cid = $this->store->makeCid($o);
        $res = $this->gateway->placeLimitOrder($this->run->getSymbol(), $o->side, $norm['price'], $norm['qty'], $cid);
        $this->store->recordOpen($o, $cid, $norm['price'], $norm['qty'], isset($res['orderId']) ? (string) $res['orderId'] : null);
        $this->log->write('Info', 'placed', sprintf('%s L%02d @ %s qty %s cid=%s%s',
            $o->side, $o->levelIdx, $norm['price'], $norm['qty'], $cid, !empty($res['duplicate']) ? ' (duplicate→adopted)' : ''));
    }

    // ── fills & reconciliation ──────────────────────────────────────────

    private function reconcile(): void
    {
        $exchangeOpen = $this->dryRun ? [] : $this->gateway->openOrders($this->run->getSymbol());
        $actions = Reconciler::plan($this->store->openRows(), $exchangeOpen, $this->store->cidPrefix());
        foreach ($actions as [$action, $cid]) {
            switch ($action) {
                case 'check_status':
                    $this->resolveMissing($cid);
                    break;
                case 'adopt':
                    $this->adopt($cid);
                    break;
                case 'alert_foreign':
                    $this->log->write('Alert', 'foreign_order', "order $cid on the account is not ours — NOT touching it");
                    break;
            }
        }
        // consistency check after applying whatever we missed while down
        if (bccomp($this->machine->openSellQty(), $this->machine->heldInventory(), 8) !== 0) {
            $this->log->write('Alert', 'invariant_violation', 'open sell qty != held inventory after reconcile');
            throw new \RuntimeException('inventory invariant violated after reconcile');
        }
    }

    /** Every-tick fill detection: db-open orders that left the exchange book,
     *  plus partial-fill tracking for orders still working on it. */
    private function detectFills(string $price): void
    {
        $exchangeOpen = $this->gateway->openOrders($this->run->getSymbol());
        $exCids = array_flip(array_column($exchangeOpen, 'clientOrderId'));
        $resolved = [];
        foreach ($this->store->openOrderObjects() as $row) {
            $cid = $row->getClientOrderId();
            if (!isset($exCids[$cid])) {
                $resolved[] = $row;
                continue;
            }
            // still on the book — a partial fill only shows up here (the order
            // never "leaves"), so track it and start the stuck timer
            $executed = (string) ($exchangeOpen[$exCids[$cid]]['executedQty'] ?? '0');
            if (bccomp($executed, '0', 8) > 0 && (string) $row->getState() !== 'PartFilled') {
                $this->store->markPartFilled($row, $executed);
                $this->partialSince[$cid] = time();
                $this->balDirty = true;
                $this->log->write('Warn', 'partial_fill', sprintf('%s partially filled %s/%s — waiting for completion', $cid, $executed, $row->getQty()));
            }
        }
        // apply buys top-down and sells bottom-up — the order a move hits them
        usort($resolved, function ($a, $b) {
            if ((string) $a->getSide() !== (string) $b->getSide()) {
                return (string) $a->getSide() === 'Buy' ? -1 : 1;
            }
            return (string) $a->getSide() === 'Buy'
                ? $b->getLevelIdx() <=> $a->getLevelIdx()
                : $a->getLevelIdx() <=> $b->getLevelIdx();
        });
        foreach ($resolved as $row) {
            $this->resolveMissing($row->getClientOrderId(), $price);
        }
    }

    /** A db-open order is gone from the book: filled, partial, or canceled? */
    private function resolveMissing(string $cid, ?string $price = null): void
    {
        $row = $this->store->findByCid($cid);
        if (!$row) {
            return;
        }
        if ($this->dryRun) {
            // no signed endpoints in dry-run; treat as canceled bookkeeping
            $this->store->markCanceled($row);
            return;
        }
        if ($row->getIsLegacy()) {
            $this->resolveLegacy($row, $price);
            return;
        }
        $status = $this->gateway->orderStatus($this->run->getSymbol(), $cid);
        $state = (string) ($status['status'] ?? 'UNKNOWN');
        $level = (int) $row->getLevelIdx();

        if ($state === 'FILLED') {
            $this->balDirty = true; // wallet moved — refresh the stamp next tick
            $executed = (string) ($status['executedQty'] ?? $row->getQty());
            $notional = bcmul((string) $row->getPrice(), $executed, 12);
            $feeEst = bcmul($notional, $this->config['fee_pct'], 12);
            // Exact fees from myTrades when available (estimate as fallback).
            $feeExact = $this->exactFee((string) ($status['orderId'] ?? ''), (string) $row->getPrice());
            if ($feeExact !== null) {
                $feeEst = $feeExact;
            }
            $this->store->markFilled($row, $executed, $feeEst);
            // ledger side is engine-independent (above); what a fill MEANS —
            // arm an exit, book a cycle, re-arm — belongs to the engine
            $fillPrice = $price ?? (string) $row->getPrice();
            if ((string) $row->getSide() === 'Buy') {
                $this->engine->onBuyFill($row, $executed, $feeEst, $fillPrice);
            } else {
                $this->engine->onSellFill($row, $executed, $feeEst, $fillPrice);
            }
        } elseif ($state === 'PARTIALLY_FILLED') {
            $this->balDirty = true; // partial fills move the wallet too
            $this->store->markPartFilled($row, (string) ($status['executedQty'] ?? '0'));
            $this->log->write('Warn', 'partial_fill', "$cid partially filled — waiting for completion");
        } elseif (in_array($state, ['CANCELED', 'EXPIRED', 'REJECTED'], true)) {
            $this->store->markCanceled($row);
            if ((string) $row->getSide() === 'Sell') {
                // a canceled exit still has inventory behind it — re-place it
                // immediately, else the next hydration would forget the
                // inventory. The ladder-level re-arm is grid-shaped (a Trend
                // exit has no ladder level to re-arm onto), so the shell
                // dispatches to the engine that placed it — each engine
                // re-places its own exit at its own price (see
                // StrategyEngine::onSellCanceled). $price is passed through
                // AS-IS (nullable) — it is only ever non-null from a live
                // tick's detectFills; boot's own reconcile ('check_status')
                // calls resolveMissing with no tick price at all, and the
                // shell must NOT paper over that by guessing $row->getPrice()
                // here: an engine that needs a live price to judge whether an
                // exit is still genuinely warranted (Trend) has to be able to
                // tell "no live price available" apart from "the live price
                // happens to equal this row's own price".
                $this->engine->onSellCanceled($row, $price);
            } else {
                $this->machine->hydrateLevel($level, 'EMPTY');
                $this->log->write('Warn', 'order_canceled', "$cid canceled on exchange — level re-armed");
            }
        } else {
            $this->log->write('Warn', 'order_unknown_state', "$cid in state $state");
        }
    }

    /**
     * A legacy exit left the book (or changed state): filled → book its cycle
     * against the ORIGINAL buy; canceled → re-place it (it guards real
     * inventory). The ladder machine is never touched — legacy exits live
     * entirely off-machine.
     */
    private function resolveLegacy(\App\BotOrder $row, ?string $price): void
    {
        $cid = $row->getClientOrderId();
        $status = $this->gateway->orderStatus($this->run->getSymbol(), $cid);
        $state = (string) ($status['status'] ?? 'UNKNOWN');

        if ($state === 'FILLED') {
            $this->balDirty = true;
            $executed = (string) ($status['executedQty'] ?? $row->getQty());
            $fee = bcmul(bcmul((string) $row->getPrice(), $executed, 12), $this->config['fee_pct'], 12);
            $feeExact = $this->exactFee((string) ($status['orderId'] ?? ''), (string) $row->getPrice());
            if ($feeExact !== null) {
                $fee = $feeExact;
            }
            $this->store->markFilled($row, $executed, $fee);
            // Pinned attribution wins whenever the placer knew exactly which
            // buy this inventory came from (Daemon::placeLegacyExit's
            // $buyPrice/$buyFee). The level-index heuristic below is a guess —
            // for an OFF-LADDER exit placed while a live batch is working it
            // matches that batch's newest same-level buy, fabricating a pnl
            // from two unrelated trades and attributing the live buy a second
            // time when its own cycle later closes.
            $pinned = $row->getLegacyBuyPrice();
            if ($pinned !== null) {
                $buyPrice = (string) $pinned;
                $fees = bcadd($fee, (string) ($row->getLegacyBuyFee() ?: '0'), 12);
            } else {
                // matched by level AND created before this exit — a new-ladder buy
                // reusing the same index can't be confused with the old grid's fill
                $buy = \App\BotOrderQuery::create()
                    ->filterByIdGridRun((int) $this->run->getIdGridRun())
                    ->filterBySimulated((bool) $this->run->getSimulated())
                    ->filterByLevelIdx((int) $row->getLevelIdx())
                    ->filterBySide('Buy')
                    ->filterByState('Filled')
                    ->filterByDateCreation(['max' => $row->getDateCreation('Y-m-d H:i:s')])
                    ->orderByIdBotOrder(\Criteria::DESC)
                    ->findOne();
                $buyPrice = $buy ? (string) $buy->getPrice() : (string) $row->getPrice();
                $fees = bcadd($fee, $buy ? (string) ($buy->getFeePaid() ?: '0') : '0', 12);
            }
            $realized = bcsub(bcmul(bcsub((string) $row->getPrice(), $buyPrice, 12), $executed, 12), $fees, 12);
            $this->store->recordCycle([
                'level_idx' => (int) $row->getLevelIdx(),
                'buy_price' => $buyPrice,
                'sell_price' => (string) $row->getPrice(),
                'qty' => $executed,
                'realized_pnl' => $realized,
                'fees_total' => $fees,
            ]);
            $this->log->write('Info', 'cycle_closed', sprintf(
                'legacy exit cycle: buy %s → sell %s qty %s realized %s',
                $buyPrice, $row->getPrice(), $executed, $realized
            ));
        } elseif ($state === 'PARTIALLY_FILLED') {
            $this->balDirty = true;
            $this->store->markPartFilled($row, (string) ($status['executedQty'] ?? '0'));
            $this->log->write('Warn', 'partial_fill', "$cid (legacy exit) partially filled — waiting for completion");
        } elseif (in_array($state, ['CANCELED', 'EXPIRED', 'REJECTED'], true)) {
            $this->store->markCanceled($row);
            $this->log->write('Alert', 'sell_canceled', "$cid (a legacy exit) was canceled — re-placing it");
            $this->replaceLegacyExit($row);
        } else {
            $this->log->write('Warn', 'order_unknown_state', "$cid in state $state");
        }
    }

    /** Re-place a canceled legacy exit at its own recorded price/qty — carrying
     *  its pinned buy attribution across, or the replacement would fall back to
     *  the level-index heuristic and could book the cycle against a different
     *  buy than the one this inventory actually came from. */
    private function replaceLegacyExit(\App\BotOrder $old): void
    {
        $remaining = bcsub((string) $old->getQty(), (string) ($old->getFilledQty() ?: '0'), 8);
        $pinnedPrice = $old->getLegacyBuyPrice();
        $this->placeLegacyExit(
            (int) $old->getLevelIdx(),
            (string) $old->getPrice(),
            $remaining,
            $pinnedPrice === null ? null : (string) $pinnedPrice,
            $pinnedPrice === null ? null : (string) ($old->getLegacyBuyFee() ?: '0')
        );
    }

    /** @internal engine access — place an exit that belongs to NO ladder
     *  level: it guards real inventory at its own price and is resolved by
     *  the shell (resolveLegacy) whatever engine is running. Used when a
     *  refit/cutover discards the ladder that owned the inventory, to
     *  re-place a canceled legacy exit, and by an engine handing off
     *  inventory it can no longer manage as its own (e.g. a late tranche
     *  fill after the position it belonged to already closed).
     *
     *  $buyPrice/$buyFee PIN this exit's buy-side attribution: when the caller
     *  knows exactly which purchase this inventory came from (its cost and the
     *  fee paid for it), resolveLegacy books the resulting cycle against THAT
     *  buy instead of guessing with the level-index heuristic — which matches
     *  the newest same-level filled buy and, for an off-ladder exit placed
     *  while a live batch is working, cheerfully picks the wrong one (fabricated
     *  pnl, a live buy attributed twice, a corrupt realizedToday feeding the
     *  daily-loss kill). Grid legacy exits pass nothing and keep the heuristic.
     *
     *  @return bool true once the exit is placed/recorded; false when the
     *  remainder was written off as dust or the exchange rejected the order —
     *  callers that are handing off inventory (e.g. a cutover) must not treat
     *  a false return as "nothing to worry about", the inventory is real. */
    public function placeLegacyExit(int $level, string $price, string $qty, ?string $buyPrice = null, ?string $buyFee = null): bool
    {
        $norm = $this->filters->normalize('Sell', $price, $qty);
        if ($norm === null) {
            $this->log->write('Alert', 'partial_dust', sprintf(
                'legacy exit remainder %s cannot clear exchange filters — written off', $qty
            ));
            return false;
        }
        $o = new IntendedOrder('Sell', $level, $norm['price'], $norm['qty']);
        $cid = $this->store->makeCid($o);
        $orderId = null;
        if (!$this->dryRun) {
            try {
                $res = $this->gateway->placeLimitOrder($this->run->getSymbol(), 'Sell', $norm['price'], $norm['qty'], $cid);
                $orderId = isset($res['orderId']) ? (string) $res['orderId'] : null;
            } catch (Gateway\BinanceApiError $e) {
                $this->log->write('Error', 'place_failed', "$cid (legacy exit): " . $e->getMessage());
                return false; // detectFills sees the canceled row resolved; alert already fired
            }
        }
        $row = $this->store->recordOpen($o, $cid, $norm['price'], $norm['qty'], $orderId);
        $this->store->markLegacy($row, $buyPrice, $buyFee);
        $this->log->write('Info', 'placed', sprintf(
            'legacy exit placed @ %s qty %s cid=%s%s',
            $norm['price'],
            $norm['qty'],
            $cid,
            $buyPrice !== null ? sprintf(' (books against buy @ %s)', $buyPrice) : ''
        ));
        return true;
    }

    /**
     * Stuck-partial handler. A buy stuck partially filled past the timeout
     * blocks its level forever: cancel the remainder, book the filled portion
     * and exit at the ACTUAL bought qty. A stuck sell is left working (it is
     * the correctly-priced exit for held inventory) — alert per window.
     */
    /** @internal engine access */
    public function checkStuckPartials(string $price): void
    {
        foreach ($this->partialSince as $cid => $since) {
            $row = $this->store->findByCid($cid);
            if (!$row || (string) $row->getState() !== 'PartFilled') {
                unset($this->partialSince[$cid]); // resolved through the normal flow
                continue;
            }
            if (time() - $since < $this->partialTimeoutSeconds()) {
                continue;
            }
            $level = (int) $row->getLevelIdx();
            if ((string) $row->getSide() === 'Sell') {
                $this->log->write('Alert', 'stuck_partial_sell', sprintf(
                    '%s stuck partially filled %s/%s — exit left working, review manually',
                    $cid, $row->getFilledQty(), $row->getQty()
                ));
                $this->partialSince[$cid] = time(); // re-alert next window
                continue;
            }
            try {
                $res = $this->gateway->cancelOrder($this->run->getSymbol(), $cid);
            } catch (Gateway\BinanceApiError $e) {
                $this->log->write('Error', 'cancel_failed', "$cid: " . $e->getMessage());
                continue; // retry next tick
            }
            unset($this->partialSince[$cid]);
            $executed = (string) ($res['executedQty'] ?? $row->getFilledQty() ?? '0');
            if (bccomp($executed, '0', 8) <= 0) {
                $this->store->markCanceled($row);
                $this->machine->hydrateLevel($level, 'EMPTY');
                $this->log->write('Warn', 'partial_timeout', "$cid timed out with nothing filled — level re-armed");
                continue;
            }
            $fee = bcmul(bcmul((string) $row->getPrice(), $executed, 12), $this->config['fee_pct'], 12);
            $feeExact = $this->exactFee((string) ($row->getExchangeOrderId() ?? ''), (string) $row->getPrice());
            if ($feeExact !== null) {
                $fee = $feeExact;
            }
            $this->store->markFilled($row, $executed, $fee);
            $this->balDirty = true;
            // a remainder too small to exit (minQty/minNotional) is written off
            // as dust rather than left as untracked phantom inventory
            if ($this->filters->normalize('Sell', $this->levels[$level + 1], $executed) === null) {
                $this->machine->hydrateLevel($level, 'EMPTY');
                $this->log->write('Alert', 'partial_dust', sprintf('L%02d booked %s but it cannot clear exchange filters — written off, level re-armed', $level, $executed));
                continue;
            }
            $sell = $this->machine->onPartialBuyBooked($level, $executed);
            $this->log->write('Info', 'partial_timeout', sprintf(
                'L%02d buy stuck at %s/%s past timeout — booked the fill, cancelled the remainder',
                $level, $executed, $row->getQty()
            ));
            $this->placeIntent($sell, $price);
        }
    }

    /** @internal engine access — first armed buy level under
     *  max_buy_levels_below (0 = no window). */
    public function distanceMinLevel(string $price): int
    {
        $n = (int) ($this->config['max_buy_levels_below'] ?? 0);
        if ($n <= 0) {
            return 0;
        }
        $top = -1; // highest buy level strictly below market
        foreach (array_slice($this->levels, 0, -1) as $i => $lvl) {
            if (bccomp($lvl, $price, 12) < 0) {
                $top = $i;
            }
        }
        return $top < 0 ? 0 : max(0, $top - $n + 1);
    }

    /** @internal engine access — cancel armed buys that fell out of the
     *  distance window (frees budget). The strategy runs BEFORE fill
     *  detection, so a buy the exchange has already (partially) filled can
     *  still look untouched in the ledger here: never discard what the cancel
     *  response says was executed — book it and exit it, exactly like the
     *  stuck-partial handler does. */
    public function pruneDistantBuys(int $minLevel, string $price): void
    {
        if ($minLevel <= 0) {
            return;
        }
        foreach ($this->store->openOrderObjects() as $row) {
            // only untouched buys — ledger-known partials belong to the stuck handler
            if ((string) $row->getSide() !== 'Buy' || (string) $row->getState() !== 'BUY_OPEN') {
                continue;
            }
            $level = (int) $row->getLevelIdx();
            if ($level >= $minLevel) {
                continue;
            }
            $cid = $row->getClientOrderId();
            $res = null;
            if (!$this->dryRun) {
                try {
                    $res = $this->gateway->cancelOrder($this->run->getSymbol(), $cid);
                } catch (Gateway\BinanceApiError $e) {
                    $this->logCancelFailure($e, $cid, 'distance prune');
                    continue; // still open in the ledger — fill detection resolves it
                }
            }
            $executed = (string) ($res['executedQty'] ?? '0');
            if (bccomp($executed, '0', 8) > 0) {
                $this->bookPrunedPartial($row, $executed, $price);
                continue;
            }
            $this->store->markCanceled($row);
            $this->machine->hydrateLevel($level, 'EMPTY');
            $this->log->write('Info', 'distance_prune', sprintf(
                'L%02d buy outside the %d-level window — cancelled to free budget',
                $level, (int) $this->config['max_buy_levels_below']
            ));
        }
    }

    /** The pruned buy had already filled (partially or fully) on the exchange:
     *  book the executed qty and arm its exit at the level's line, so the
     *  inventory is never orphaned by the cancel. */
    private function bookPrunedPartial(\App\BotOrder $row, string $executed, string $price): void
    {
        $level = (int) $row->getLevelIdx();
        $fee = bcmul(bcmul((string) $row->getPrice(), $executed, 12), $this->config['fee_pct'], 12);
        $feeExact = $this->exactFee((string) ($row->getExchangeOrderId() ?? ''), (string) $row->getPrice());
        if ($feeExact !== null) {
            $fee = $feeExact;
        }
        $this->store->markFilled($row, $executed, $fee);
        $this->balDirty = true;
        // a remainder too small to exit is dust, not phantom inventory
        if ($this->filters->normalize('Sell', $this->levels[$level + 1], $executed) === null) {
            $this->machine->hydrateLevel($level, 'EMPTY');
            $this->log->write('Alert', 'partial_dust', sprintf(
                'L%02d pruned buy had %s executed but it cannot clear exchange filters — written off, level re-armed',
                $level, $executed
            ));
            return;
        }
        $sell = $this->machine->onPartialBuyBooked($level, $executed);
        $this->log->write('Alert', 'prune_partial_booked', sprintf(
            'L%02d buy outside the window had already filled %s/%s — booked the fill and placed its exit instead of dropping it',
            $level, $executed, $row->getQty()
        ));
        $this->placeIntent($sell, $price);
    }

    /** A cancel that failed because the order is already off the book
     *  (filled/canceled elsewhere — Binance -2011) is NOT an error: fill
     *  detection resolves it moments later in the same tick, and an Error
     *  here would page an operator over a normal race. */
    private function logCancelFailure(Gateway\BinanceApiError $e, string $cid, string $what): void
    {
        if ($e->binanceCode === -2011) {
            $this->log->write('Info', 'cancel_gone', sprintf(
                '%s already off the book when %s tried to cancel it — leaving it to fill detection',
                $cid, $what
            ));
            return;
        }
        $this->log->write('Error', 'cancel_failed', $cid . ': ' . $e->getMessage());
    }

    /**
     * @internal engine access — sum the actual commissions for an order via
     * myTrades, converted to quote (quote asset as-is; base asset × trade
     * price; other assets — e.g. BNB — unpriced here, fall back to the
     * estimate). Null = use the estimate.
     */
    public function exactFee(string $orderId, string $fallbackPrice): ?string
    {
        if ($orderId === '' || $this->dryRun) {
            return null;
        }
        try {
            $symbol = (string) $this->run->getSymbol();
            [$base, $quote] = $this->symbolAssets();
            $sum = '0';
            foreach ($this->gateway->myTrades($symbol, ['orderId' => $orderId]) as $t) {
                $c = (string) ($t['commission'] ?? '0');
                $asset = (string) ($t['commissionAsset'] ?? '');
                if ($asset === $quote) {
                    $sum = bcadd($sum, $c, 12);
                } elseif ($asset === $base) {
                    $sum = bcadd($sum, bcmul($c, (string) ($t['price'] ?? $fallbackPrice), 12), 12);
                } else {
                    return null; // BNB-fee mode etc. — keep the estimate
                }
            }
            return $sum;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Position accounting orphaned by a geometry reset (or any state where the
     * wallet holds more than the machine tracks after a kill-clear) skews
     * invested/unrealized: rebase the ledger era so the stops act on the LIVE
     * grid only. The orphaned base stays in the wallet — alerted, not sold.
     */
    private function rebaseLedgerIfOrphaned(): void
    {
        $tracked = $this->store->trackedInventory();
        $held = $this->engine->heldQty();
        if (bccomp(bcsub($tracked, $held, 8), '0.00000001', 8) <= 0) {
            return; // ledger and machine (incl. legacy exits) agree — nothing orphaned
        }
        $epoch = date('Y-m-d H:i:s');
        $this->run->setLedgerResetAt($epoch);
        $this->run->save();
        $this->store->setLedgerEpoch($epoch);
        $this->log->write('Alert', 'ledger_rebased', sprintf(
            '%s base untracked by the grid (wallet-held) — position accounting rebased so stops track the live grid only',
            bcsub($tracked, $held, 8)
        ));
    }

    /** @internal engine access — fee estimate recorded on the matched buy
     *  for this level's open cycle. */
    public function matchedBuyFee(int $level): string
    {
        $rows = \App\BotOrderQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterBySimulated((bool) $this->run->getSimulated())
            ->filterByLevelIdx($level)
            ->filterBySide('Buy')
            ->filterByState('Filled')
            ->orderByIdBotOrder(\Criteria::DESC)
            ->findOne();
        return $rows ? (string) ($rows->getFeePaid() ?: '0') : '0';
    }

    /** @internal engine access — would this qty clear the exchange's own
     *  filters (minQty/minNotional/tick size) as an order on this side at
     *  this price? Lets an engine write off unplaceable dust up front (see
     *  the grid's own stuck-partial dust guard) instead of discovering it
     *  only when placeIntent silently declines to place it. */
    public function filtersClear(string $side, string $price, string $qty): bool
    {
        return $this->filters->normalize($side, $price, $qty) !== null;
    }

    private function adopt(string $cid): void
    {
        // Crash between place and record: rebuild the row from the exchange.
        $status = $this->gateway->orderStatus($this->run->getSymbol(), $cid);
        if (!preg_match('/-L(\d+)-(B|S)-\d+$/', $cid, $m)) {
            $this->log->write('Alert', 'adopt_failed', "cannot parse level from $cid");
            return;
        }
        $o = new IntendedOrder($m[2] === 'B' ? 'Buy' : 'Sell', (int) $m[1], (string) $status['price'], (string) $status['origQty']);
        $this->store->recordOpen($o, $cid, (string) $status['price'], (string) $status['origQty'], (string) ($status['orderId'] ?? ''));
        $this->machine->hydrateLevel($o->levelIdx, $o->side === 'Buy' ? 'BUY_OPEN' : 'SELL_OPEN');
        $this->log->write('Warn', 'adopted', "recovered untracked order $cid from the exchange");
    }

    // ── control plane ───────────────────────────────────────────────────

    /** Consume pending bot_command rows. Kill/Flatten set the switch; the tick
     *  loop then idles the daemon (heartbeat only) rather than exiting — so
     *  there's no systemd restart loop and the watchdog stays quiet. */
    private function consumeCommands(): void
    {
        $pending = BotCommandQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByCmdStatus('Pending')
            ->orderByIdBotCommand()
            ->find();
        foreach ($pending as $cmd) {
            $name = (string) $cmd->getCommand();
            $cmd->setCmdStatus('Acked');
            $cmd->save();
            $this->log->write('Info', 'command', "consuming command: $name");
            switch ($name) {
                case 'Pause':
                    $this->paused = true;
                    $this->log->write('Info', 'bot_stop', 'paused via command — order placement halted (open orders stay working)');
                    break;
                case 'Resume':
                case 'Start':
                    $this->paused = false;
                    $this->log->write('Info', 'bot_start', 'resumed via command — order placement active');
                    break;
                case 'Kill':
                    $this->flattenOnKill = false;
                    $this->run->setKillSwitch(true);
                    $this->run->save();
                    break;
                case 'Flatten':
                    if (!ProfilePolicy::allowsRealizedLoss((string) ($this->run->getProfile() ?: 'Balanced'))) {
                        // NoLoss: never realize — refuse, don't kill, leave inventory
                        $cmd->setCmdStatus('Failed');
                        $cmd->save();
                        $this->log->write('Alert', 'flatten_refused',
                            'profile NoLoss forbids realizing losses — Flatten refused (plain Kill holds inventory and stays allowed)');
                        continue 2;
                    }
                    $this->flattenOnKill = true;
                    $this->run->setKillSwitch(true);
                    $this->run->save();
                    break;
                case 'CancelBuys':
                    $this->cancelOpenBuys();
                    break;
                case 'Reload':
                    // acknowledged below, then the tick returns false and the
                    // process exits cleanly — systemd or the watchdog
                    // supervisor relaunches it; boot rehydrates from the DB
                    $this->reloadRequested = true;
                    $this->log->write('Info', 'bot_restart', 'restart requested via command — reloading (boot rehydrates from the DB)');
                    break;
            }
            $cmd->setCmdStatus('Done');
            $cmd->save();
        }
    }

    /** Kill switch: stop entries, cancel open buys, optionally flatten. */
    private function onKill(bool $flatten): void
    {
        $this->log->write('Alert', 'kill', $flatten ? 'kill+flatten: canceling buys, selling inventory' : 'kill: canceling open buys, holding inventory');
        $this->cancelOpenBuys();
        if ($flatten && !$this->dryRun) {
            // v1 flatten = cancel sells and alert for manual review rather than
            // blind market-selling — a deliberate safety choice.
            $this->log->write('Alert', 'flatten_manual', 'open sells left working; review inventory manually');
        }
    }

    /** @internal engine access */
    public function cancelOpenBuys(): void
    {
        foreach ($this->store->openOrderObjects() as $row) {
            if ((string) $row->getSide() !== 'Buy') {
                continue;
            }
            if (!$this->dryRun) {
                try {
                    $this->gateway->cancelOrder($this->run->getSymbol(), $row->getClientOrderId());
                } catch (Gateway\BinanceApiError $e) {
                    $this->logCancelFailure($e, $row->getClientOrderId(), 'cancel buys');
                    continue;
                }
            }
            $this->store->markCanceled($row);
            $this->machine->hydrateLevel((int) $row->getLevelIdx(), 'EMPTY');
        }
    }

    /** grid_run row → the plain config array the pure core consumes. */
    private function configFromRun(): array
    {
        $r = $this->run;
        return [
            'p_low' => (string) $r->getPLow(),
            'p_high' => (string) $r->getPHigh(),
            'n_levels' => (int) $r->getNLevels(),
            'spacing' => (string) $r->getSpacing(),
            'allocation' => (string) $r->getAllocation(),
            'budget_quote' => (string) $r->getBudgetQuote(),
            'deploy_pct' => (int) ($r->getDeployPct() ?? 100),
            'fee_pct' => (string) $r->getFeePct(),
            'max_position_quote' => (string) $r->getMaxPositionQuote(),
            'max_order_quote' => (string) $r->getMaxOrderQuote(),
            'daily_loss_limit_quote' => (string) $r->getDailyLossLimitQuote(),
            'breakout_buffer_pct' => (string) $r->getBreakoutBufferPct(),
            'breakout_policy' => (string) $r->getBreakoutPolicy(),
            'max_open_orders' => (int) $r->getMaxOpenOrders(),
            'max_buy_levels_below' => (int) ($r->getMaxBuyLevelsBelow() ?? 0),
            // which strategy runs this row (engine selection at boot)
            'algo' => $this->runAlgo(),
            // trend structure (signal geometry) — the engine reads config, not
            // the row, so a backtest/sweep can drive it without a DB row
            'trend_tf' => (string) ($r->getTrendTf() ?: '1h'),
            'donchian_period' => (int) ($r->getDonchianPeriod() ?? 20),
            'trend_ema_fast' => (int) ($r->getTrendEmaFast() ?? 20),
            'trend_ema_slow' => (int) ($r->getTrendEmaSlow() ?? 50),
            'atr_period' => (int) ($r->getAtrPeriod() ?? 14),
            // trend risk knobs — profile-stamped, null under NoLoss (which
            // cannot run Trend at all): null means "not configured", never 0
            'atr_stop_mult' => $r->getAtrStopMult() !== null ? (string) $r->getAtrStopMult() : null,
            'atr_initial_mult' => $r->getAtrInitialMult() !== null ? (string) $r->getAtrInitialMult() : null,
            'reentry_cooldown' => $r->getReentryCooldown() !== null ? (int) $r->getReentryCooldown() : null,
        ];
    }
}
