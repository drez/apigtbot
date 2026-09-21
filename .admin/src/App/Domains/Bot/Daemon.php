<?php

namespace App\Domains\Bot;

use ApiGoat\Audit\AuditContext;
use App\BotCommandQuery;
use App\BotEventQuery;
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
    private ?string $reloadReason = null;
    private bool $killApplied = false;
    /** This process has already reconciled the DURABLE kill marker with a run
     *  it found alive (see tick()'s resume branch). Reset whenever a kill is
     *  applied, so the next resume is recorded again. */
    private bool $resumeReconciled = false;
    /** A trade list that came back empty this tick: the lag is an ACCOUNT
     *  condition, not an order one, so no later fill in the same tick pays
     *  for the re-read again (see fillCommission). Reset per tick. */
    private bool $tradesLagging = false;
    /** Ladder levels whose exit PLACEMENT was refused by the exchange (see
     *  placeIntent's BinanceApiError catch): real base the machine still holds
     *  with no order and no ledger row behind it, so nothing but
     *  reArmMissingExits can ever see it. A level a risk veto or a filter
     *  skipped is NOT in here — that is a decision, not a failure.
     *  @var array<int, true> */
    private array $exitPlacementFailed = [];
    /** The flatten intent of the COMMAND being consumed — never a standing
     *  property of the process. It is cleared the moment onKill() has acted on
     *  it (and on a resume), because a kill that arrives from OUTSIDE this
     *  process carries no command: DrawdownGuard::tripAll on a sibling daemon,
     *  RunLifecycle's retire-and-hold, the dashboard's Kill button. All of
     *  those mean "cancel buys, HOLD" — and a stale `true` here sold the
     *  inventory they had just decided to keep. */
    private bool $flattenOnKill = false;
    /** a kill+flatten is still selling: re-price what has not filled, every
     *  tick, until the book is flat (chaseFlatten). Mirrored in bot_event
     *  (flatten_pending / flatten_done) so a restart resumes it — see
     *  restoreFlatten(). */
    private bool $flattenPending = false;
    /** epoch the running liquidation was commanded at (the chase deadline
     *  measures from here; restored from the flatten_pending marker) */
    private ?int $flattenSince = null;
    /** flatten_stalled was raised for the current liquidation */
    private bool $flattenStalled = false;
    /** id_bot_order of the last lot the chase re-priced — the round-robin
     *  cursor that keeps a reprice budget from starving the same lots */
    private ?int $chaseCursor = null;
    /** cancel+replace pairs left in THIS tick's reprice budget */
    private int $chaseBudget = 0;
    private string $geomSig = '';
    private string $refitPendingSig = '';
    private ?int $bootCodeMtime = null;
    private ?int $balStampedAt = null;
    private bool $balDirty = false;
    private bool $balStampFailing = false;
    /** fee_netted has been raised as an Alert by this process already */
    private bool $feeNettedAlerted = false;
    /** base the exchange has already delivered for buys this tick is still
     *  about to book — in the account, on nobody's ledger yet, and so NOT a
     *  fee float (four tranches filling at once would each see the other
     *  three as spare coins) */
    private string $unbookedBuyQty = '0';
    /** bnb_fee_float_low raised for the current low-float episode */
    private bool $bnbFloatLowAlerted = false;
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
    /** the Alert 'no_budget' notice was written for the current no-budget episode */
    private bool $noBudgetLogged = false;
    /** silent-grid watch: epoch when the book last went buy-less (0 = has buys) */
    private ?int $silentSince = null;
    /** the Alert 'grid_silent' notice was written for the current silent episode */
    private bool $gridSilentLogged = false;
    /** cached MechanicalRefit::isMechanical() (Config read, 5 min TTL) */
    private ?bool $mechanicalCached = null;
    private int $mechanicalAt = 0;
    /** cached fresh 4h trend label (null = missing or stale), 5 min TTL */
    private ?string $trend4hCached = null;
    private int $trend4hAt = 0;
    /** a starved release is working: rebuild the ladder once its reserve drops */
    private bool $starvedRebuildArmed = false;
    /** the kill's OWN liquidation is placing orders — see flattenInventory */
    private bool $liquidating = false;
    /** legacy reserve the current ladder was built against */
    private string $ladderReserve = '0';
    /** shared-budget invariant broken → entries halted (fail closed) */
    private bool $budgetOvercommitted = false;

    /** how far under the mark a commanded liquidation prices its sell limit:
     *  marketable (it takes the bids, filling at THEIR prices) with the
     *  slippage bounded — see marketableExit() */
    private const FLATTEN_CROSS_PCT = '0.005';
    /**
     * Chase discipline. A reprice is a cancel AND a place, both of which count
     * against Binance's order-rate limit (100/10 s, and a 429 answered by more
     * calls becomes a 418 IP ban that stops every run on this host). Without a
     * deadband the condition was simply "resting price above target", which a
     * one-tick move satisfies — and the same lesson was already paid for on
     * the buy side (buyWindow's arm/keep deadband, prod BNB 2026-09-21: 1050
     * place/cancel pairs in a day without one fill).
     *
     * DEADBAND: reprice only when the resting price is further than
     * max(0.1%, 2 ticks) above the target. BUDGET: at most this many lots per
     * run per tick, round-robin (chaseCursor) so no lot is starved. DEADLINE:
     * after this long the chase gives up, leaves the exits working and says so
     * ONCE — a liquidation that has not filled in fifteen minutes is not a
     * liquidity problem the loop can solve by making more orders.
     */
    private const CHASE_DEADBAND_PCT = '0.001';
    private const CHASE_DEADBAND_TICKS = 2;
    private const CHASE_MAX_REPRICES = 4;
    private const CHASE_DEADLINE = 900;
    /** largest part of an exit the account may be short of and still sell (see placeShrunkExit) */
    private const MAX_EXIT_SHRINK = '0.005';
    /** seconds between wallet-balance stamps when nothing filled (~15 min) */
    private const BAL_STAMP_INTERVAL = 900;
    /** …on a REAL account: the stamps ARE the equity the global drawdown
     *  floor reads (DrawdownGuard::realBalances), and money moves there that
     *  no fill of ours explains — a sibling's commission, a withdrawal. One
     *  weight-20 call a minute per run is ~1% of the request budget. */
    private const BAL_STAMP_INTERVAL_REAL = 60;
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

    /**
     * @internal engine access — when this process FIRST saw $cid partially
     * filled, the only clock a stuck-partial handler may measure against
     * (null = this process never saw it start; the row's date_modification is
     * the restart fallback, and OrderStore::markPartFilled keeps that stamp on
     * first-seen too).
     *
     * A resting order's progress is not a state change: since 6c4f825 the
     * ledger follows every observed executedQty, and an engine measuring "how
     * long has this been stuck" off the row's last write therefore gave a
     * trickling order a fresh timer on every refresh (Repro2, BUG B).
     */
    public function partialSinceFor(string $cid): ?int
    {
        return $this->partialSince[$cid] ?? null;
    }

    /** How many times a fill's trade list is re-read before the daemon gives
     *  up and books an estimate (see fillCommission). ONE by default: the lag
     *  it covers is a few hundred milliseconds, and every read is a weight-20
     *  myTrades call made with the whole tick — the kill switch, the
     *  heartbeat, every other fill's exit — waiting behind its usleep().
     *  Env-tunable so a test (and a paper run, which never lags) pays nothing. */
    private function tradeLookupRetries(): int
    {
        $v = env('GTBOT_FILL_TRADE_RETRIES', '');
        return is_numeric($v) ? max(0, (int) $v) : 1;
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
        // add_audit attribution: from here on every grid_run change this
        // process writes is a DAEMON write, not a human at the dashboard —
        // which is what makes the audit trail worth reading when a run goes
        // dark (DarkArmSweep). Guarded because AuditContext ships with
        // apigoat/runtime: a box pinned to an older one must still boot.
        if (class_exists(AuditContext::class)) {
            AuditContext::asDaemon();
        }

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

        // Base the ledger accounts for that nothing is guarding. A level held
        // SELL_OPEN with no order lives in MEMORY only (reArmMissingExits puts
        // it back within a tick), so a restart in between hydrates the level
        // EMPTY and forgets the lot entirely: it re-arms the buy and the
        // ladder re-spends capital that is still sitting in base. Named once,
        // here — the ladder is NOT re-sized for it (only legacy exits reserve
        // quote, and this base is behind no order at all), so it has to be
        // sold or re-armed by hand.
        $orphan = bcsub($this->store->trackedInventory(), $this->engine->heldQty(), 8);
        if (bccomp($orphan, '0.00000001', 8) > 0) {
            $this->log->write('Alert', 'orphan_inventory', sprintf(
                '%s base is on the ledger with nothing guarding it — no exit on the book and no engine position. '
                . 'The ladder does not reserve its capital: sell or re-arm it by hand',
                $orphan
            ));
        }

        // A liquidation outlives the daemon that commanded it: pick it up
        // again while the switch is still on, so the first tick chases instead
        // of announcing a hold it is not doing.
        if ($this->run->getKillSwitch() && !$this->dryRun) {
            $this->restoreFlatten();
        }

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
        // ONE reprice budget per tick, whichever passes spend it: the kill's
        // own liquidation pass and the chase that follows it in the same tick
        // are the same cancel+place spend against the same rate limit
        $this->chaseBudget = self::CHASE_MAX_REPRICES;
        $this->tradesLagging = false;

        $this->consumeCommands($price);

        if ($this->reloadRequested) {
            $this->log->write('Info', 'reloading', ($this->reloadReason ?: 'manual reload requested') . ' — restarting (boot rehydrates from the DB)');
            return false;
        }

        if ($this->run->getKillSwitch()) {
            $this->ensureKilled($price);   // cancel buys (+ liquidate on a flatten) once, then idle
            // A killed run still has orders on the exchange — the exits it was
            // holding, and the liquidation kill+flatten just placed. They fill
            // whether or not this loop is trading, so keep booking them: the
            // ledger (and the drawdown watch right below, which marks that
            // inventory to market) would otherwise be blind until a restart.
            $this->detectFills($price);
            $this->chaseFlatten($price);
            // a killed hold is not a resolved hold — keep marking it to
            // market and escalate as the drawdown deepens
            $this->watchKilledDrawdown($price);
            // …and a killed run must get a lost exit back too: a kill vetoes
            // new exposure, never the order guarding what is already held.
            $this->reArmMissingExits($price);
            $this->log->flush();     // a killed run still books fills — don't strand their digest
            return true;             // stay alive + heartbeat (no restart loop)
        }
        // not killed: nothing is being liquidated, and no flatten intent is
        // owed to anyone — the next kill decides for itself whether it sells
        $this->endFlatten('the run resumed');
        $this->flattenOnKill = false;
        if ($this->killApplied) {
            // the daemon logs the resume itself so bot_event timestamps all
            // come from ONE clock (web requests run in the user's timezone)
            $this->killApplied = false;
            $this->worseningAlerted = 0;
            $this->markResumed('kill switch cleared — resuming trading');
            $this->rebaseLedgerIfOrphaned();
        } elseif (!$this->resumeReconciled) {
            // A RESUME THIS PROCESS NEVER SAW. The markers are the only memory
            // a kill has across daemons, and until now the `restart` one was
            // written solely by the process that had applied the kill — so a
            // switch cleared while nothing was running (the dashboard's
            // toggle, FundsHold::release, an MCP/SQL write; and the watchdog
            // deliberately does NOT respawn a killed run's daemon) left `kill`
            // standing as the newest marker for good. Every later kill then
            // read as "already announced" (killAnnounced) and the operator was
            // told nothing, while restoreFlatten() kept adopting a liquidation
            // that had been called off days earlier. The run is trading: that
            // is the evidence, whoever cleared the switch.
            $this->resumeReconciled = true;
            if ($this->killAnnounced()) {
                $this->markResumed('kill switch was cleared while this daemon was not running — resuming trading');
            }
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
        // operator rule (grid_run.sell_when_starved): a starved ladder may
        // sell held inventory below cost — only then, only what it takes
        if ($this->runAlgo() === 'Grid' && $this->noBudgetLogged && !empty($this->config['sell_when_starved'])) {
            $this->releaseStarvedInventory($price);
        }
        if ($this->runAlgo() === 'Grid') {
            $this->watchSilentGrid($price);
        }
        if ($this->reloadRequested) {
            $this->log->write('Info', 'reloading', ($this->reloadReason ?: 'reload requested') . ' — restarting (boot rehydrates from the DB)');
            return false;
        }

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
        $this->reArmMissingExits($price);
        if ($this->starvedRebuildArmed && bccomp($this->store->legacyReserveQuote(), $this->ladderReserve, 12) < 0) {
            // a released exit filled: its capital is free again — re-anchor
            // the same geometry so the ladder allocates it (maybeRefit next
            // tick; geomSig blanked = "pending refit" on unchanged geometry)
            $this->starvedRebuildArmed = false;
            $this->geomSig = '';
            $this->log->write('Info', 'starved_rebuild', sprintf(
                'released inventory sold — legacy reserve %s → %s, rebuilding the ladder on the freed budget',
                $this->ladderReserve,
                $this->store->legacyReserveQuote()
            ));
        }

        $this->log->flush(); // coalesce this tick's trade notifications into one message
        return true;
    }

    /**
     * Keep the run ALIVE without touching the exchange. The runner calls this
     * between the slices of an API backoff: a rate limit is the exchange's
     * problem, but sleeping through it in one piece made it everybody's —
     * three days of Retry-After meant three days with no heartbeat (the
     * watchdog reports a crash loop and spawns daemons that bounce off this
     * run's GET_LOCK), no kill switch read, and an operator's Kill sitting in
     * bot_command until the ban lifted. The DB write also keeps the connection
     * the lock lives on from being reaped by MySQL's wait_timeout.
     *
     * Only what needs no exchange call is honoured: the heartbeat, the kill
     * switch, and the commands that are pure control plane (Kill, Pause,
     * Reload). Anything that needs a live mark — Flatten above all — stays
     * Pending and is consumed by the next real tick.
     *
     * AND THE MARK GOES. A heartbeat says "this process is alive", not "this
     * price is current" — but DrawdownGuard::equity() prices each base asset
     * off the last_price of the run with the freshest last_tick_at, and
     * NavLedger::refPrice() picks its fallback the same way. Stamping the
     * clock without the price therefore let the one run that demonstrably
     * CANNOT see the market win that contest for every run on its symbol, with
     * a mark as old as the backoff: the shared-wallet equity the global
     * drawdown floor is compared against read the pre-ban price all the way
     * through the crash the floor exists for. Every reader null-guards
     * last_price and falls back (market summaries, "no mark available"), so
     * dropping it turns a lie into an absence, which they already handle.
     *
     * @return bool false when the process should stop looping (a restart was
     *              requested); true to keep waiting.
     */
    public function idle(): bool
    {
        $this->run->reload();
        $this->run->setLastTickAt(date('Y-m-d H:i:s'));
        $this->run->setLastPrice(null);
        $this->run->save();
        foreach (BotCommandQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByCmdStatus('Pending')
            ->orderByIdBotCommand()
            ->find() as $cmd) {
            $name = (string) $cmd->getCommand();
            if (!in_array($name, ['Kill', 'Pause', 'Reload'], true)) {
                continue; // needs the exchange (or a mark) — the next tick takes it
            }
            $cmd->setCmdStatus('Acked');
            $cmd->save();
            $this->log->write('Info', 'command', "consuming command while backing off: $name");
            if ($name === 'Kill') {
                $this->flattenOnKill = false;
                $this->endFlatten('a plain Kill stopped it');
                $this->run->setKillSwitch(true);
                $this->run->save();
            } elseif ($name === 'Pause') {
                $this->paused = true;
            } else {
                $this->requestReload('restart requested via command');
            }
            $cmd->setCmdStatus('Done');
            $cmd->save();
        }
        return !$this->reloadRequested;
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
            [$executed, $fee] = $this->bookFill($row, (string) ($row->getExchangeOrderId() ?? ''), $executed);
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
        $deployPct = (int) ($this->config['deploy_pct'] ?? 100);
        // deploy 0 = FLAT: zero ladder budget — every buy level fails
        // minQty/minNotional and is skipped (filter_skip), while working
        // sells/legacy exits keep working. This is a position, not an error.
        $reserve = $this->store->legacyReserveQuote();
        $this->ladderReserve = $reserve;
        $budget = $this->freeLadderQuote($reserve);
        $this->qtys = GridMath::allocate(
            $budget,
            array_slice($this->levels, 0, -1),
            $this->config['allocation']
        );
        $this->filterSkipLogged = [];
        $this->flatLogged = false;
        if ($this->runAlgo() === 'Grid') {
            $this->warnNoBudget($deployPct, $budget, $reserve); // the ladder is the grid's book only
        }
    }

    /** Warn ONCE per episode when the ladder cannot fund a single entry: the
     *  free ladder quote (freeLadderQuote: deployed slice, capped by slice
     *  minus the legacy reserve) is under one exchange minimum notional, so
     *  every buy level will be filter-skipped. Typical cause:
     *  inventory held behind exits (sell_at_loss OFF) while the slice shrank.
     *  deploy 0% is a deliberate FLAT position, not a budget problem. */
    /** A grid that should be armed but has had no open buy for this long alerts (env override for tests). */
    private function gridSilentAfter(): int
    {
        $v = env('GTBOT_GRID_SILENT_AFTER', '');
        return is_numeric($v) ? (int) $v : 6 * 3600;
    }

    /** Silent-grid alert (2026-09-09): deploy > 0 and nothing gating entries
     *  (no de-risk, no budget overcommit, no no_budget starvation — those have
     *  their own alerts) yet no armed buy on the ladder for gridSilentAfter().
     *  Prod 2026-09-05→09 sat exactly like this for four days: both grids
     *  refit every 7h and placed nothing, and nothing said so. Once per
     *  episode; a buy on the ladder (or a legitimate gate) resets it.
     *
     *  Correctness passes (2026-09-16):
     *  - reads the LevelStateMachine in memory (BUY_OPEN covers untouched AND
     *    PartFilled buys), not a bot_order query every tick;
     *  - skips DryRun daemons (they never record orders — silence is the mode);
     *  - treats the mechanical hold below p_low in a 4h downtrend as an armed
     *    posture, not silence (the cron deliberately holds the ladder below
     *    price there);
     *  - the silence window survives a reload: the epoch is persisted under a
     *    shell-owned engine_state key, so a restart cannot reset the 6h clock. */
    private function watchSilentGrid(string $price): void
    {
        if ($this->dryRun) {
            return; // dry-run never records orders; silence is the mode
        }
        $deployPct = (int) ($this->config['deploy_pct'] ?? 100);
        $hasBuy = false;
        for ($i = 0, $n = count($this->qtys); $i < $n; $i++) {
            if ($this->machine->state($i) === 'BUY_OPEN') {
                $hasBuy = true;
                break;
            }
        }
        if (!$hasBuy && $this->mechanicalMode()
            && bccomp($price, (string) ($this->config['p_low'] ?? '0'), 12) < 0) {
            // The cron's actual hold condition, not merely "below p_low":
            // below the range in a 4h down-family trend the cron deliberately
            // holds the ladder instead of chasing lower. Any other below-p_low
            // spell (e.g. the cron failed to re-anchor in an uptrend) must
            // still alert. A STALE 4h row cannot establish that hold: a frozen
            // 'down' label would silence the watch for as long as the
            // collector stays dead — exactly the outage this alert exists for.
            if (in_array($this->trend4h(), ['down', 'strong_down'], true)) {
                $hasBuy = true; // intended mechanical hold — not silence
            }
        }
        if ($this->silentSince === null) {
            $state = $this->engineState();
            $this->silentSince = (int) ($state[self::SILENT_SINCE_KEY] ?? 0);
            // the alert is once per EPISODE, not once per process: a reload
            // mid-episode (budget edit, code-mtime, watchdog respawn) must not
            // re-fire it, so the flag rides in engine_state with the epoch
            $this->gridSilentLogged = !empty($state[self::SILENT_ALERTED_KEY]);
        }
        if ($hasBuy || $deployPct <= 0 || $this->deRisking || $this->budgetOvercommitted || $this->noBudgetLogged) {
            if ($this->silentSince !== 0 || $this->gridSilentLogged) {
                $this->silentSince = 0;
                $this->gridSilentLogged = false;
                $this->persistSilentSince(0);
            }
            return;
        }
        if ($this->silentSince === 0) {
            $this->silentSince = time();
            $this->persistSilentSince($this->silentSince);
        }
        if ($this->gridSilentLogged || time() - $this->silentSince < $this->gridSilentAfter()) {
            return;
        }
        $this->gridSilentLogged = true;
        $this->persistSilentSince($this->silentSince);
        $this->log->write('Alert', 'grid_silent', sprintf(
            'no armed buy on the ladder for %dh while deploy is %d%% and nothing gates entries — the ladder is not armed (vetoes? geometry above/below price? exchange filters?); slice %s, range [%s, %s]',
            intdiv(time() - $this->silentSince, 3600),
            $deployPct,
            (string) $this->config['budget_quote'],
            (string) ($this->config['p_low'] ?? ''),
            (string) ($this->config['p_high'] ?? '')
        ));
    }

    /** Shell-owned engine_state key: epoch when the book last went buy-less. */
    private const SILENT_SINCE_KEY = 'grid_silent_since';
    /** Shell-owned engine_state key: the alert for the current episode was written. */
    private const SILENT_ALERTED_KEY = 'grid_silent_alerted';

    private function persistSilentSince(int $ts): void
    {
        $state = $this->engineState();
        if ($ts === 0) {
            unset($state[self::SILENT_SINCE_KEY], $state[self::SILENT_ALERTED_KEY]);
        } else {
            $state[self::SILENT_SINCE_KEY] = $ts;
            if ($this->gridSilentLogged) {
                $state[self::SILENT_ALERTED_KEY] = 1;
            } else {
                unset($state[self::SILENT_ALERTED_KEY]);
            }
        }
        $this->writeEngineState($state);
    }

    /** The stored 4h trend label, or null when the row is missing or STALE.
     *  Cached for 5 min: the collector refreshes every 10 min while a Grid
     *  daemon ticks every 5s, and summaries() is an uncached SELECT. */
    private function trend4h(): ?string
    {
        if ($this->trend4hAt === 0 || time() - $this->trend4hAt >= 300) {
            $s4 = MarketStore::summaries((string) $this->run->getSymbol())['4h'] ?? null;
            $this->trend4hCached = (is_array($s4) && empty($s4['stale'])) ? ($s4['trend'] ?? null) : null;
            $this->trend4hAt = time();
        }
        return $this->trend4hCached;
    }

    /** MechanicalRefit::isMechanical() with a 5-minute cache (Config read). */
    private function mechanicalMode(): bool
    {
        if ($this->mechanicalCached === null || time() - $this->mechanicalAt >= 300) {
            $this->mechanicalCached = MechanicalRefit::isMechanical();
            $this->mechanicalAt = time();
        }
        return $this->mechanicalCached;
    }

    private function warnNoBudget(int $deployPct, string $free, string $reserve): void
    {
        $minNotional = (string) ($this->config['min_notional'] ?? '0');
        $starved = $deployPct > 0 && (bccomp($free, '0', 12) <= 0 || bccomp($free, $minNotional, 12) < 0);
        if (!$starved) {
            $this->noBudgetLogged = false;
            return;
        }
        if ($this->noBudgetLogged) {
            return;
        }
        $this->noBudgetLogged = true;
        $this->log->write('Alert', 'no_budget', sprintf(
            'no budget available for trading: slice %s at deploy %d%% minus %s reserved by working exits leaves %s (< min notional %s) — buys idle until an exit fills, the slice grows, or inventory is sold (sell_at_loss is %s, sell_when_starved is %s)',
            (string) $this->config['budget_quote'],
            $deployPct,
            $reserve,
            $free,
            $minNotional,
            !empty($this->config['sell_at_loss']) ? 'ON' : 'OFF',
            !empty($this->config['sell_when_starved']) ? 'ON' : 'OFF'
        ));
    }

    /**
     * Capital the ladder may allocate right now: the deployed fraction of the
     * slice (budget × deploy_pct), capped by what is actually free once the
     * legacy reserve (inventory still working behind old exits, valued at
     * exit price) is taken out of the WHOLE slice — min(deployed, slice −
     * reserve). The reserve eats the undeployed part of the slice first.
     * Before 2026-09-05 it was deployed − reserve: a slice trimmed to its
     * inventory floor at deploy 35% read as starved (deployed 80 < reserve
     * 166 on BNB #4, 2026-09-02) and sell_when_starved sold 14 lots at the
     * bottom minutes before a +12% leg. Frees up as each legacy exit fills.
     */
    private function freeLadderQuote(string $reserve): string
    {
        $deployed = $this->deployedQuote();
        if (bccomp($deployed, '0', 12) <= 0 || bccomp($reserve, '0', 12) <= 0) {
            return $deployed;
        }
        $free = bcsub((string) $this->config['budget_quote'], $reserve, 12);
        if (bccomp($free, '0', 12) < 0) {
            return '0';
        }
        return bccomp($deployed, $free, 12) < 0 ? $deployed : $free;
    }

    /** Deployed slice the ladder may allocate (budget × deploy_pct). */
    private function deployedQuote(): string
    {
        $deployPct = (int) ($this->config['deploy_pct'] ?? 100);
        if ($deployPct <= 0) {
            return '0';
        }
        $budget = (string) $this->config['budget_quote'];
        return $deployPct < 100 ? bcdiv(bcmul($budget, (string) $deployPct, 12), '100', 12) : $budget;
    }

    /**
     * grid_run.sell_when_starved: the ladder is starved (no_budget episode)
     * and the operator allows selling below cost in exactly that case —
     * reprice legacy exits DOWN to the live price, closest to the market
     * first (the smallest loss per unit), until the freed reserve would fund
     * the ladder (every buy level over the exchange minimum). Rows already
     * at/near the live price are left working (no chase). A slice too small
     * to fund the ladder at all never gets here: the refit validator refuses
     * such geometry before the ladder is built.
     */
    private function releaseStarvedInventory(string $price): void
    {
        $minNotional = (string) ($this->config['min_notional'] ?? '0');
        $needed = bcmul($minNotional, (string) max(1, count($this->levels) - 1), 12);
        $reserve = $this->store->legacyReserveQuote();
        $free = $this->freeLadderQuote($reserve);
        if (bccomp($free, $needed, 12) >= 0) {
            return; // reserve already dropped (a release filled) — the rebuild hook takes it from here
        }
        if (bccomp($this->deployedQuote(), $needed, 12) < 0) {
            return; // the deployed fraction itself cannot fund the ladder — selling inventory would not help
        }
        $rows = array_filter($this->store->legacyRows(), static fn ($r) => (string) $r->getState() === 'SELL_OPEN');
        usort($rows, static fn ($a, $b) => bccomp((string) $a->getPrice(), (string) $b->getPrice(), 12));
        $nearMarket = bcmul($price, '1.005', 12);
        foreach ($rows as $row) {
            $exitPrice = (string) $row->getPrice();
            if (bccomp($exitPrice, $nearMarket, 12) <= 0) {
                continue; // already working at the market — do not chase it down
            }
            $cid = (string) $row->getClientOrderId();
            if (!$this->dryRun) {
                try {
                    $this->gateway->cancelOrder($this->run->getSymbol(), $cid);
                } catch (Gateway\BinanceApiError $e) {
                    $this->logCancelFailure($e, $cid, 'starved release');
                    continue;
                }
            }
            $this->store->markCanceled($row);
            $rem = bcsub((string) $row->getQty(), (string) ($row->getFilledQty() ?: '0'), 12);
            $placed = $this->placeLegacyExit(
                (int) $row->getLevelIdx(),
                $price,
                $rem,
                $row->getLegacyBuyPrice() !== null ? (string) $row->getLegacyBuyPrice() : null,
                $row->getLegacyBuyFee() !== null ? (string) $row->getLegacyBuyFee() : null
            );
            $this->log->write('Alert', 'starved_release', sprintf(
                'starved ladder: legacy exit %s (qty %s @ %s) repriced to the live %s to free %s of reserve — a loss is realized on purpose (sell_when_starved ON)%s',
                $cid,
                $rem,
                $exitPrice,
                $price,
                bcmul($rem, $exitPrice, 8),
                $placed ? '' : ' — RE-PLACE FAILED, inventory is now unguarded'
            ));
            $this->starvedRebuildArmed = true;
            // what the ladder gets once this release fills: the reserve drops
            // by the released notional, the deployed fraction stays the cap
            $reserve = bcsub($reserve, bcmul($rem, $exitPrice, 12), 12);
            $free = $this->freeLadderQuote($reserve);
            if (bccomp($free, $needed, 12) >= 0) {
                break;
            }
        }
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
            // Close the decision row too: the geometry is refused for good
            // (geomSig above makes sure it is never retried), so leaving it
            // applied_at NULL would park it as "pending" forever — and the
            // refit cron holds all future re-anchors while a cron write is
            // pending. Superseded is the scorer's existing verdict for a
            // decision that never took effect.
            $this->markDecisionRejected($newConfig);
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
        $now = date('Y-m-d H:i:s');
        foreach ($this->queuedDecisionsFor($this->config) as $d) {
            $d->setAppliedAt($now);
            $d->save();
        }
    }

    /**
     * Every decision still queued for this run that asks for the geometry
     * passed in. PLURAL on purpose: in mechanical mode the routine must echo
     * the run's current geometry on a deploy-only call, so the cron's write
     * and the routine's echo carry IDENTICAL p_low/p_high/n_levels. The daemon
     * reads those columns off the run row and cannot tell who wrote them, so
     * stamping only the newest match left the other one un-applied forever:
     * the cron's 72h clock (keyed on the newest APPLIED Cron row) never
     * advanced, and its pending gate used to wedge the run outright. Once the
     * ladder is live both decisions are in force — the cron's for the
     * geometry, the routine's for the deploy — so both are closed together.
     *
     * Scored rows are left alone: the scorer already closed them (Superseded)
     * and a late applied_at would resurrect a decision that never ran.
     *
     * @return \App\BotDecision[]
     */
    private function queuedDecisionsFor(array $config): array
    {
        $out = [];
        foreach (\App\BotDecisionQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByAppliedAt(null, \Criteria::ISNULL)
            ->filterByEvalStatus('Pending')
            ->orderByIdBotDecision(\Criteria::DESC)
            ->find() as $d) {
            if (bccomp((string) $d->getPLow(), (string) $config['p_low'], 8) === 0
                && bccomp((string) $d->getPHigh(), (string) $config['p_high'], 8) === 0
                && (int) $d->getNLevels() === (int) $config['n_levels']) {
                $out[] = $d;
            }
        }
        return $out;
    }

    /** The daemon refused this geometry and will never retry it: close the
     *  matching un-applied decision so nothing downstream keeps waiting on it
     *  (the refit cron holds every re-anchor while a cron write is pending,
     *  and the scorer leaves the newest un-applied row Pending forever). */
    private function markDecisionRejected(array $config): void
    {
        $now = date('Y-m-d H:i:s');
        foreach ($this->queuedDecisionsFor($config) as $d) {
            $d->setCyclesDelta(0);
            $d->setRealizedDelta('0');
            $d->setVerdict('Superseded');
            $d->setEvalStatus('Scored');
            $d->setEvalAt($now);
            $d->save();
        }
    }

    /** Stamp free+locked wallet balances for the run's assets onto the run
     *  row. Signed endpoint → skipped in dry-run; failures are non-fatal (the
     *  last stamped values stay). Throttled: refreshes on the first live tick,
     *  after a fill was processed (balDirty), or every BAL_STAMP_INTERVAL —
     *  totals only move on fills, so quiet ticks need no re-fetch. */
    protected function balStampInterval(): int
    {
        return $this->run->getSimulated() ? self::BAL_STAMP_INTERVAL : self::BAL_STAMP_INTERVAL_REAL;
    }

    private function stampBalances(): void
    {
        if ($this->dryRun) {
            return;
        }
        if (!$this->balDirty
            && $this->balStampedAt !== null
            && time() - $this->balStampedAt < $this->balStampInterval()) {
            return;
        }
        try {
            $bal = $this->gateway->accountBalances();
            $this->balStampFailing = false;
        } catch (\Throwable $e) {
            // the floor keeps reading the LAST stamp: say the view went stale,
            // once per episode (the runner reports a persistent API failure)
            if (!$this->balStampFailing) {
                $this->balStampFailing = true;
                $this->log->write('Warn', 'balance_stamp_failed', 'account balances unreadable — equity guards are reading the last stamp: ' . $e->getMessage());
            }
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
        $this->warnBnbFloat($bal, $quote);
    }

    /** In BNB fee mode an empty float does not fail anything — Binance just
     *  starts charging the base asset, and every exit shrinks by its
     *  commission (fee_netted). Say so BEFORE it runs dry, once per episode. */
    private function warnBnbFloat(array $bal, string $quote): void
    {
        if (!FeeFloat::accountPaysInBnb()) {
            return;
        }
        $min = FeeFloat::bnbMinQuote();
        if (bccomp($min, '0', 8) <= 0) {
            return;
        }
        try {
            $float = FeeFloat::bnbFloatQuote($bal, $quote, $this->gateway->tickerPrice('BNB' . $quote));
        } catch (\Throwable) {
            return;
        }
        if (bccomp($float, $min, 8) >= 0) {
            $this->bnbFloatLowAlerted = false;
            return;
        }
        if (!$this->bnbFloatLowAlerted) {
            $this->bnbFloatLowAlerted = true;
            $this->log->write('Alert', 'bnb_fee_float_low', sprintf(
                'BNB fee float is worth %s %s (floor %s) — top it up, or fees fall back to the traded asset and exits shrink',
                BudgetPool::fmt(bcadd($float, '0', 2)), $quote, $min
            ));
        }
    }

    /** [base, quote] assets parsed from the run symbol ("BTCUSDT" → BTC/USDT). */
    public function symbolAssets(): array
    {
        return SimWallet::assetsFor((string) $this->run->getSymbol()); // ONE parser for the fleet
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
                $this->ensureKilled($price);
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

    private function ensureKilled(?string $price = null): void
    {
        if ($this->killApplied) {
            return;
        }
        $this->killApplied = true;
        $this->resumeReconciled = false; // a new kill episode: its resume must be recorded again
        // …and so must its breach confirmations. Both counters are cleared
        // only by the check that owns them, and the killed branch never runs
        // either check — so a run killed after 2 of the 3 ticks came back from
        // a hold of ANY length still armed and died on the first fresh print,
        // which is precisely the aberration BREACH_CONFIRM_TICKS exists to
        // survive (for the global one, on every run on the wallet). Both call
        // sites zero their own counter before killing, so this only ever
        // clears evidence a kill from OUTSIDE that check left behind.
        $this->breachTicks = 0;
        $this->ddBreachTicks = 0;
        $this->onKill($this->flattenOnKill, $price);
    }

    // ── placement ───────────────────────────────────────────────────────

    /** @internal engine access — risk review + filters + placement.
     *  @return \App\BotOrder|null the ledger row that reached the book, so a
     *  caller that knows something about it the shell does not (which buy this
     *  exit is closing, and how much of its fee is still unbooked) can pin it
     *  — see GridEngine's partial-exit path. null = vetoed, filtered out or
     *  dry-run: nothing was placed. */
    public function placeIntent(IntendedOrder $o, string $price): ?\App\BotOrder
    {
        $snapshot = [
            'market_price' => $price,
            'invested_quote' => $this->store->investedQuote(),
            'realized_pnl_today' => $this->store->realizedToday(),
            'open_orders' => $this->store->openCount(),
            // The kill switch vetoes new EXPOSURE, not the exits that guard
            // inventory the run already holds: a kill cancels buys and leaves
            // sells working (onKill), so an exit is never the thing a kill is
            // stopping. Exempting only `liquidating` meant a killed HOLD whose
            // exit was canceled on the exchange could not re-place it at all —
            // resolveMissing → onSellCanceled → placeIntent came back "kill
            // switch is tripped" and the inventory sat unguarded until a
            // human resumed the run.
            'kill_switch' => (bool) $this->run->getKillSwitch() && !$this->liquidating && $o->side !== 'Sell',
        ];
        $d = RiskManager::review($o, $snapshot, $this->config);
        if ($d->kill) {
            $this->flattenOnKill = $d->flatten;
            $this->run->setKillSwitch(true);
            $this->run->save();
            $this->log->write('Alert', 'risk_kill', $d->reason);
            $this->ensureKilled($price);
            return null;
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
            return null;
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
            return null;
        }

        if ($this->dryRun) {
            $this->log->write('Info', 'would_place', sprintf(
                '%s L%02d @ %s qty %s (market %s)',
                $o->side, $o->levelIdx, $norm['price'], $norm['qty'], $price
            ), ['side' => $o->side, 'level' => $o->levelIdx, 'price' => $norm['price'], 'qty' => $norm['qty']]);
            return null;
        }

        $cid = $this->store->makeCid($o);
        try {
            $res = $this->gateway->placeLimitOrder($this->run->getSymbol(), $o->side, $norm['price'], $norm['qty'], $cid);
        } catch (Gateway\BinanceApiError $e) {
            // A buy that never reached the book must not leave the level
            // marked BUY_OPEN in memory (the machine set it before placement
            // and nothing re-arms it here) — that phantom would mask the
            // silent-grid watch, which reads the machine state. Sells keep
            // their state: a failed exit still guards held inventory.
            if ($o->side === 'Buy') {
                $this->machine->hydrateLevel($o->levelIdx, 'EMPTY');
            }
            $res = $o->side === 'Sell' ? $this->placeShrunkExit($o->levelIdx, $norm, $cid, $e) : null;
            if ($res === null) {
                if ($o->side === 'Sell') {
                    // the EXCHANGE refused it: the level holds base with no
                    // order and no ledger row, so only reArmMissingExits can
                    // ever put it back. Recorded here and nowhere else — a
                    // veto or a filter skip below is a DECISION, not a failed
                    // placement, and must never be overruled by the sweep.
                    $this->exitPlacementFailed[$o->levelIdx] = true;
                }
                throw $e; // the runner logs api_error and backs off
            }
            $norm['qty'] = $res['qty'];
        }
        if ($o->side === 'Sell') {
            unset($this->exitPlacementFailed[$o->levelIdx]); // an exit reached the book
            if (bccomp($norm['qty'], $o->qty, 8) !== 0) {
                // the level holds what its exit CARRIES (floored to the lot
                // step, or shrunk above) — the cycle must not book base it
                // never sold
                $this->engine->onExitResized($o->levelIdx, $norm['qty']);
            }
        }
        $placed = $this->store->recordOpen($o, $cid, $norm['price'], $norm['qty'], isset($res['orderId']) ? (string) $res['orderId'] : null);
        $this->log->write('Info', 'placed', sprintf('%s L%02d @ %s qty %s cid=%s%s',
            $o->side, $o->levelIdx, $norm['price'], $norm['qty'], $cid, !empty($res['duplicate']) ? ' (duplicate→adopted)' : ''));
        return $placed;
    }

    // ── fills & reconciliation ──────────────────────────────────────────

    private function reconcile(): void
    {
        $exchangeOpen = $this->dryRun ? [] : $this->gateway->openOrders($this->run->getSymbol());
        $actions = Reconciler::plan($this->store->openRows(), $exchangeOpen, $this->store->cidPrefix());
        // the fills missed while down are booked one by one against an account
        // that already holds ALL of them — same trap as detectFills
        $pending = [];
        foreach ($actions as [$action, $cid]) {
            $row = $action === 'check_status' ? $this->store->findByCid($cid) : null;
            if ($row && (string) $row->getSide() === 'Buy') {
                $pending[$cid] = (string) $row->getQty();
            }
        }
        foreach ($actions as [$action, $cid]) {
            switch ($action) {
                case 'check_status':
                    unset($pending[$cid]);
                    $this->unbookedBuyQty = array_reduce($pending, static fn (string $a, string $q): string => bcadd($a, $q, 8), '0');
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
            if (bccomp($executed, '0', 8) <= 0) {
                continue;
            }
            $partFilled = (string) $row->getState() === 'PartFilled';
            if ($partFilled && !isset($this->partialSince[$cid])) {
                // a partial this process never saw START (restart): resume the
                // stuck timer from the row — date_modification is stamped when
                // it was marked PartFilled and OrderStore::markPartFilled
                // deliberately does NOT move it again while the order keeps
                // filling, so this is first-seen on both sides of a restart
                // (an engine reads the same clock through partialSinceFor).
                $this->partialSince[$cid] = (int) $row->getDateModification('U') ?: time();
            }
            if (!$partFilled) {
                $this->store->markPartFilled($row, $executed);
                $this->partialSince[$cid] = time();
                $this->balDirty = true;
                $this->log->write('Warn', 'partial_fill', sprintf('%s partially filled %s/%s — waiting for completion', $cid, $executed, $row->getQty()));
            } elseif (bccomp($executed, (string) ($row->getFilledQty() ?: '0'), 8) !== 0) {
                // …and it keeps filling. The ledger used to be written ONCE,
                // on the transition into PartFilled: an order that went 25% →
                // 90% while resting still read 25%, so everything sized off
                // "qty − filled_qty" (a flatten's replacement, a cutover's
                // carry) asked the exchange to sell coins that were already
                // sold. A resting order's progress is not a state change, it
                // is a number, and the number is the exchange's.
                $this->store->markPartFilled($row, $executed);
                $this->balDirty = true;
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
        $this->unbookedBuyQty = '0';
        foreach ($resolved as $row) {
            if ((string) $row->getSide() === 'Buy') {
                $this->unbookedBuyQty = bcadd($this->unbookedBuyQty, (string) $row->getQty(), 8);
            }
        }
        try {
            foreach ($resolved as $row) {
                if ((string) $row->getSide() === 'Buy') {
                    $this->unbookedBuyQty = bcsub($this->unbookedBuyQty, (string) $row->getQty(), 8);
                }
                $this->resolveMissing($row->getClientOrderId(), $price);
            }
        } finally {
            // an API error thrown out of the loop must not leave a count the
            // next tick's OTHER booking paths (prune, stuck partials) inherit
            $this->unbookedBuyQty = '0';
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
            // exact fees + the qty actually held (estimate / gross as fallback)
            [$executed, $feeEst] = $this->bookFill(
                $row,
                (string) ($status['orderId'] ?? ''),
                (string) ($status['executedQty'] ?? $row->getQty())
            );
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
            // A cancel does not undo what already traded. Book that part
            // first — markCanceled() alone wrote off coins the account had
            // really sold (or really bought), with no cycle and no realized
            // P/L, and then re-armed an exit for the WHOLE original qty.
            $executed = (string) ($status['executedQty'] ?? '0');
            if (bccomp($executed, '0', 8) > 0 && $this->bookCanceledRemainder($row, $executed, $price)) {
                return;
            }
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
                try {
                    $this->engine->onSellCanceled($row, $price);
                } catch (Gateway\BinanceApiError $e) {
                    // The replacement never reached the book (a flatten racing
                    // a -2010, a filter reject, a transport error). Put the row
                    // BACK on the open ledger: a Canceled row is invisible to
                    // openOrderObjects(), so the lot it guarded would never be
                    // looked at again — real inventory, no exit, one Alert.
                    // Open, the next tick resolves it exactly like this one.
                    $row->setState('SELL_OPEN');
                    $row->save();
                    throw $e; // the runner logs api_error and backs off
                }
            } else {
                $this->machine->hydrateLevel($level, 'EMPTY');
                $this->log->write('Warn', 'order_canceled', "$cid canceled on exchange — level re-armed");
            }
        } else {
            $this->log->write('Warn', 'order_unknown_state', "$cid in state $state");
        }
    }

    /**
     * A canceled order that had already (partly) traded: book what traded and
     * guard what is left. Returns true once it has taken ownership of the row
     * (the caller must then neither mark it canceled nor re-arm it for the
     * full original qty — that is how a partially filled exit used to come
     * back as an order for coins the account no longer had).
     *
     * A SELL that belongs to an ENGINE is handed to that engine: an engine
     * books its own fills (StrategyEngine::onSellFill), because only it knows
     * what the fill did to the position it is running — a Trend arm's qty and
     * fees_paid, a ladder level's own remainder. The shell booking the cycle
     * itself left the engine's position untouched, so the same coins were
     * counted twice (state.qty + the legacy row), the trend arm could never
     * read flat again and its next tick placed a SECOND full-position sell
     * (Repro2, BUG A). Only a LEGACY exit — engine-independent by
     * construction — is booked here.
     *
     * A BUY books the fill and guards it with a legacy exit pinned to its own
     * cost — the same settlement cancelEntriesBookingFills makes at a
     * cutover, because the alternative (writing it off) erases base the
     * wallet really holds.
     */
    private function bookCanceledRemainder(\App\BotOrder $row, string $executed, ?string $price): bool
    {
        $level = (int) $row->getLevelIdx();
        $qty = (string) $row->getQty();
        $ownPrice = (string) $row->getPrice();
        $rem = bcsub($qty, $executed, 12);
        $sell = (string) $row->getSide() === 'Sell';
        $basis = $sell ? $this->exitBasis($row) : null;
        [$held, $fee] = $this->bookFill($row, (string) ($row->getExchangeOrderId() ?? ''), $executed);
        $this->log->write('Alert', 'cancel_partial_booked', sprintf(
            '%s was canceled having already traded %s of %s — booked, not written off',
            (string) $row->getClientOrderId(),
            $executed,
            $qty
        ));
        if ($sell) {
            if (!$row->getIsLegacy()) {
                // the engine owns the position this exit was selling: it books
                // the cycle for what traded, keeps what is left and re-arms its
                // own exit for it. $price is this tick's mark where there is
                // one; an engine that needs the order's own price reads the row
                // (both engines do — see TrendEngine::onSellFill's $fillPrice).
                $this->engine->onSellFill($row, $held, $fee, $price ?? (string) $row->getPrice());
                return true;
            }
            $bookedBuyFee = $this->recordExitCycle($row, $held, $fee, $basis, $qty);
            if (bccomp($rem, '0', 12) > 0) {
                // only what is LEFT gets an exit, at the price this one held —
                // carrying the buy-leg fee the cycle above did NOT book, so the
                // lot pays its entry fee exactly once across the pieces
                $placed = $this->placeLegacyExit(
                    $level,
                    $ownPrice,
                    $rem,
                    $basis,
                    bcsub($this->exitBuyFee($row), $bookedBuyFee, 12)
                );
                if (!$placed) {
                    $this->warnUnguardedInventory($rem, $ownPrice, (string) $row->getClientOrderId(), 'the remainder of a canceled exit');
                }
            }
            return true;
        }
        $this->machine->hydrateLevel($level, 'EMPTY');
        $exitPrice = $this->runAlgo() === 'Grid' ? (string) ($this->levels[$level + 1] ?? $ownPrice) : $ownPrice;
        if (!$this->placeLegacyExit($level, $exitPrice, $held, $ownPrice, $fee)) {
            $this->warnUnguardedInventory($held, $ownPrice, (string) $row->getClientOrderId(), 'an entry canceled after it had filled');
        }
        return true;
    }

    /**
     * A handoff placed no exit for base the run really holds — never silent.
     *
     * placeLegacyExit() returns false for two different things and both end
     * here: a remainder too small to be an order again (its own partial_dust
     * Alert has already fired, and there is genuinely nothing to retry), and
     * an exchange that refused the order (place_failed). NOTHING sweeps this
     * up later: the base is booked on the ledger, the ladder level is EMPTY
     * and no order guards it, so the lot is worked by hand — which is what
     * this says, once, per lot.
     *
     * @internal engine access — the engines hand inventory off the same way
     */
    public function warnUnguardedInventory(string $qty, string $price, string $cid, string $what): void
    {
        $this->log->write('Alert', 'unguarded_inventory', sprintf(
            '%s: %s base from %s (cost %s) is in the account with NO exit on the book — it is booked on the ledger and '
            . 'no order guards it; sell or re-arm it by hand if an exit does not come back',
            $what,
            $qty,
            $cid,
            $price
        ));
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
            [$executed, $fee] = $this->bookFill($row, (string) ($status['orderId'] ?? ''), $executed);
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
            // whatever traded before the cancel is booked and only the rest is
            // re-placed (see bookCanceledRemainder) — the old path re-placed
            // qty − filled_qty off a filled_qty that could be stale
            $executed = (string) ($status['executedQty'] ?? '0');
            if (bccomp($executed, '0', 8) > 0 && $this->bookCanceledRemainder($row, $executed, $price)) {
                return;
            }
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
                // same last resort as placeIntent: an exit a hair short of the
                // account is sold for what is there, not left off the book
                $res = $this->placeShrunkExit($level, $norm, $cid, $e);
                if ($res === null) {
                    $this->log->write('Error', 'place_failed', "$cid (legacy exit): " . $e->getMessage());
                    return false; // detectFills sees the canceled row resolved; alert already fired
                }
                $norm['qty'] = $res['qty'];
                $o = new IntendedOrder('Sell', $level, $norm['price'], $norm['qty']);
                $orderId = isset($res['orderId']) ? (string) $res['orderId'] : null;
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
            [$executed, $fee] = $this->bookFill($row, (string) ($row->getExchangeOrderId() ?? ''), $executed);
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

    /**
     * Every level the machine holds must have an exit on the book. A
     * placement that never reached the exchange leaves one SELL_OPEN with no
     * ledger row and no order — real base, nothing selling it, and nothing
     * that could ever notice: detectFills only resolves rows that EXIST.
     * placeIntent keeps the level armed on purpose ("a failed exit still
     * guards held inventory") and GridEngine's partial re-place swallows the
     * same refusal, so both ended in a lot stranded by one transient 5xx.
     * Re-place it through the normal path (filters, onExitResized and the
     * shrink fallback all apply); once per tick, so the runner's own backoff
     * spaces the retries after an API error.
     */
    private function reArmMissingExits(string $price): void
    {
        if ($this->exitPlacementFailed === [] || $this->runAlgo() !== 'Grid' || $this->dryRun) {
            return; // nothing failed, or no ladder whose line the shell can re-derive
        }
        // NEVER re-arm off a machine the ledger does not back: if the lot was
        // carried off the ladder and its legacy exit has since filled, the
        // level is claiming coins that are sold and this would be a second
        // order for inventory the account no longer has.
        if (bccomp($this->store->trackedInventory(), $this->machine->heldInventory(), 8) < 0) {
            return;
        }
        // ANY open sell on the level counts, legacy included — one order
        // guarding those coins is enough.
        $guarded = [];
        foreach ($this->store->openOrderObjects() as $row) {
            if ((string) $row->getSide() === 'Sell') {
                $guarded[(int) $row->getLevelIdx()] = true;
            }
        }
        foreach (array_keys($this->exitPlacementFailed) as $i) {
            $held = $this->machine->heldAt($i);
            if (isset($guarded[$i]) || !isset($this->qtys[$i])
                || $this->machine->state($i) !== 'SELL_OPEN' || bccomp($held, '0', 8) <= 0) {
                unset($this->exitPlacementFailed[$i]); // nothing left to put back
                continue;
            }
            try {
                $placed = $this->placeIntent(new IntendedOrder('Sell', $i, $this->levels[$i + 1], $held), $price);
            } catch (Gateway\BinanceApiError) {
                // Best-effort repair, at the tail of the tick: throwing here
                // would abandon the rest of it (the event digest above all)
                // over an exchange that is already refusing orders. The lot
                // was announced when its placement first failed; this just
                // keeps trying, once a tick, until one is accepted.
                continue;
            }
            if ($placed !== null) {
                $this->log->write('Alert', 'exit_missing', sprintf(
                    'L%02d held %s with no exit on the book (a placement that never reached the exchange) — re-armed',
                    $i,
                    $held
                ));
            }
        }
    }

    /**
     * @internal engine access — THE distance window, in ladder levels
     * (max_buy_levels_below; 0 = no window, both edges 0).
     *
     * ONE definition for both halves of the window, because they used to
     * disagree across ticks and cancel each other's work. Prod BNB grid,
     * 2026-09-21: "placed Buy L00 @ 714.64" then, seconds later,
     * "distance_prune | L00 buy outside the 4-level window", 1050+ times,
     * every 1–2 minutes, without one fill.
     *
     *   'arm'  the lowest level that may be ARMED — the N nearest buy levels
     *          below price, exactly as max_buy_levels_below is documented.
     *   'keep' the lowest level that may stay OPEN. It lags 'arm' by ONE
     *          level, so a buy is never cancelled at the very edge that
     *          placed it.
     *
     * WHY THE TWO EDGES. The window's top is the highest ladder LINE below
     * price, so it steps by a whole level the moment the tape crosses one —
     * 0.03% of price on that grid, and it was trading right on L4. With a
     * single edge the level AT the edge was therefore armed on the way down
     * and cancelled on the way back up, at no cost to the tape and no benefit
     * to anyone. The deadband makes that impossible: a level the window has
     * just left is KEPT, and only a move a full level further cancels it
     * (arming it again then needs the tape to come back two levels — a real
     * move, not a wiggle). The price is at most one buy resting one level
     * beyond the window, deliberately paid against an unbounded place/cancel
     * loop.
     *
     * @return array{arm: int, keep: int}
     */
    public function buyWindow(string $price): array
    {
        $n = (int) ($this->config['max_buy_levels_below'] ?? 0);
        if ($n <= 0) {
            return ['arm' => 0, 'keep' => 0];
        }
        $top = -1; // highest buy level strictly below market
        foreach (array_slice($this->levels, 0, -1) as $i => $lvl) {
            if (bccomp($lvl, $price, 12) < 0) {
                $top = $i;
            }
        }
        if ($top < 0) {
            return ['arm' => 0, 'keep' => 0];
        }
        $arm = max(0, $top - $n + 1);
        return ['arm' => $arm, 'keep' => max(0, $arm - 1)];
    }

    /** @internal engine access — cancel armed buys that fell out of the
     *  distance window (frees budget). $minLevel is buyWindow()'s 'keep'
     *  edge, never the 'arm' edge: pruning at the edge that arms is what
     *  placed and cancelled one order 1050 times. The strategy runs BEFORE
     *  fill detection, so a buy the exchange has already (partially) filled
     *  can still look untouched in the ledger here: never discard what the
     *  cancel response says was executed — book it and exit it, exactly like
     *  the stuck-partial handler does. */
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
        [$executed, $fee] = $this->bookFill($row, (string) ($row->getExchangeOrderId() ?? ''), $executed);
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
     * Last resort for an exit the exchange refuses for insufficient balance.
     * bookFill nets a commission taken out of the inventory, but it judges
     * the account at one instant: a sibling daemon booking its own fill in
     * the same second, or a manual withdrawal of dust, can still leave the
     * account a hair short. No exit on the book is the one state a run must
     * not rest in, so a shortfall of at most MAX_EXIT_SHRINK of the order is
     * sold short of it — loudly — and anything larger stays an error: that
     * is missing inventory, not a fee.
     *
     * @param array{price:string, qty:string} $norm
     * @return array{qty:string}|null the placed order's response + qty, null = not recoverable
     */
    private function placeShrunkExit(int $levelIdx, array $norm, string $cid, Gateway\BinanceApiError $e): ?array
    {
        if ($e->binanceCode !== -2010 || !str_contains(strtolower($e->getMessage()), 'insufficient')) {
            return null;
        }
        try {
            [$base] = $this->symbolAssets();
            $free = (string) ($this->gateway->accountBalances()[$base]['free'] ?? '0');
            $floor = bcmul($norm['qty'], bcsub('1', self::MAX_EXIT_SHRINK, 8), 8);
            if (bccomp($free, $norm['qty'], 8) >= 0 || bccomp($free, $floor, 8) < 0) {
                return null;
            }
            $shrunk = $this->filters->normalize('Sell', $norm['price'], $free);
            if ($shrunk === null) {
                return null;
            }
            $res = $this->gateway->placeLimitOrder($this->run->getSymbol(), 'Sell', $shrunk['price'], $shrunk['qty'], $cid);
        } catch (\Throwable) {
            return null;
        }
        $this->log->write('Alert', 'exit_shrunk', sprintf(
            'L%02d exit for %s refused (account holds %s %s) — placed for %s instead; check the fee float and for anything else drawing on the account',
            $levelIdx, $norm['qty'], $free, $base, $shrunk['qty']
        ));
        return $res + ['qty' => $shrunk['qty']];
    }

    /**
     * @internal engine access — what a fill really cost and really delivered,
     * from myTrades. `fee` is the commission in QUOTE (quote asset as-is, base
     * asset × trade price, anything else — BNB fee mode — × its own quote
     * ticker); null = unknown, keep the estimate. `base` is the part of it the
     * exchange took out of the base asset this order traded.
     *
     * `avg` is the volume-weighted price the order actually traded at — its
     * limit only for an order the tape came to; one that crossed the book as
     * it was placed (a trend entry, a stop-sell, a flatten) trades at the
     * book's prices.
     *
     * @return array{fee: ?string, asset: ?string, base: string, avg: ?string}
     */
    public function fillCommission(string $orderId, string $fallbackPrice): array
    {
        $none = ['fee' => null, 'asset' => null, 'base' => '0', 'avg' => null];
        if ($orderId === '' || $this->dryRun) {
            return $none;
        }
        try {
            $symbol = (string) $this->run->getSymbol();
            [$base, $quote] = $this->symbolAssets();
            $trades = $this->gateway->myTrades($symbol, ['orderId' => $orderId]);
            // An empty list for an order the exchange has just reported
            // executed is "not yet", not "never": on a real account the trade
            // list lags the order status by a few hundred milliseconds, and
            // giving up on the first read books the ESTIMATE forever — the
            // commission is never netted, the exit is armed for the gross and
            // only placeShrunkExit saves it. Paper answers from its own book,
            // so it never lags and never pays for this.
            // …and only the FIRST unpriced fill of a tick pays for it: a
            // lagging trade list is a condition of the ACCOUNT, not of one
            // order, so re-reading for every fill in a burst bought nothing
            // and cost the tick tries × 300 ms and 3× the myTrades weight per
            // fill (12 fills = ~11 s asleep with the kill switch unread).
            $tries = ((bool) $this->run->getSimulated()) || $this->tradesLagging
                ? 0
                : $this->tradeLookupRetries();
            for ($i = 0; !$trades && $i < $tries; $i++) {
                usleep(300000);
                $trades = $this->gateway->myTrades($symbol, ['orderId' => $orderId]);
            }
            if (!$trades) {
                $this->tradesLagging = true;
                // no trade list is "unknown", not "free" — and which rows carry
                // an estimate instead of the real commission has to be on the
                // record, or the books cannot be audited afterwards
                $this->log->write('Warn', 'fill_unpriced', sprintf(
                    'order %s on %s reports executed but has no trade list after %d read(s) — booking the estimated fee '
                    . 'and NOT netting a base-asset commission; the exit may come back short',
                    $orderId,
                    $symbol,
                    $tries + 1
                ));
                return $none;
            }
            $sum = '0';
            $inBase = '0';
            $asset = null;
            $marks = [];
            $tradedQty = '0';
            $tradedQuote = '0';
            foreach ($trades as $t) {
                $tq = (string) ($t['qty'] ?? '0');
                $tradedQty = bcadd($tradedQty, $tq, 12);
                $tradedQuote = bcadd($tradedQuote, bcmul($tq, (string) ($t['price'] ?? $fallbackPrice), 12), 12);
                $c = (string) ($t['commission'] ?? '0');
                $a = (string) ($t['commissionAsset'] ?? '');
                $asset = $a;
                if ($a === $quote) {
                    $sum = bcadd($sum, $c, 12);
                } elseif ($a === $base) {
                    $inBase = bcadd($inBase, $c, 12);
                    $sum = bcadd($sum, bcmul($c, (string) ($t['price'] ?? $fallbackPrice), 12), 12);
                } else {
                    try {
                        $marks[$a] ??= $this->gateway->tickerPrice($a . $quote);
                        $sum = bcadd($sum, bcmul($c, $marks[$a], 12), 12);
                    } catch (\Throwable) {
                        // unpriceable leg: the FEE falls back to the estimate —
                        // what was taken out of the base, and the traded
                        // price, are still known and still matter
                        $feeUnknown = true;
                    }
                }
            }
            if (bccomp($inBase, '0', 12) > 0) {
                $asset = $base; // an order that fell back to the base mid-way: that is the leg with consequences
            }
            if (!empty($feeUnknown)) {
                $sum = null;
            }
            $avg = bccomp($tradedQty, '0', 12) > 0 ? bcdiv($tradedQuote, $tradedQty, 8) : null;
            return ['fee' => $sum, 'asset' => $asset, 'base' => $inBase, 'avg' => $avg];
        } catch (\Throwable) {
            return $none;
        }
    }

    /**
     * @internal engine access — book a (whole or partial) fill on the ledger
     * and return what the run now HOLDS from it plus its fee in quote. Every
     * fill path goes through here, because on a real account the two are not
     * the order's numbers: a BUY's commission comes out of the base asset it
     * delivers unless a fee float (BNB fee mode, or spare base already in the
     * account — see FeeFloat) pays it. An exit armed for the gross qty is then
     * refused for insufficient balance and the inventory sits unguarded, so
     * the ledger and the engine are given the net qty instead.
     *
     * @return array{0: string, 1: string} [held qty, fee in quote]
     */
    public function bookFill(\App\BotOrder $row, string $orderId, string $executed): array
    {
        $price = (string) $row->getPrice();
        $cost = $this->fillCommission($orderId, $price);
        if ($cost['avg'] !== null && bccomp($cost['avg'], $price, 8) !== 0) {
            // Everything downstream — cost basis, VWAP, cycle P/L, breakeven —
            // reads the row's price, so the row carries what was PAID, and the
            // limit it was placed at survives in the event.
            $this->log->write('Info', 'fill_price', sprintf(
                '%s traded at %s, not its limit %s', $row->getClientOrderId(), $cost['avg'], $price
            ), ['limit' => $price, 'avg' => $cost['avg']]);
            $row->setPrice($cost['avg']);
            $price = $cost['avg'];
        }
        $fee = $cost['fee'] ?? bcmul(bcmul($price, $executed, 12), (string) $this->config['fee_pct'], 12);
        $held = $executed;
        if ((string) $row->getSide() === 'Buy' && bccomp($cost['base'], '0', 12) > 0) {
            [$base, $quote] = $this->symbolAssets();
            try {
                $short = FeeFloat::uncovered(
                    $base,
                    $quote,
                    bcadd($executed, $this->unbookedBuyQty, 8),
                    $cost['base'],
                    $this->gateway->accountBalances(),
                    (int) $row->getIdBotOrder()
                );
            } catch (\Throwable) {
                $short = $cost['base']; // cannot see the account: assume no float
            }
            if (bccomp($short, '0', 12) > 0) {
                // …floored to the lot step: `held` is what an exit can carry.
                // A qty between steps is sold short of itself by every exit
                // (Filters floors at placement) while the books — the cycle's
                // qty, a trend arm's position, trackedInventory — go on
                // counting the residue: a trend arm never reads flat again, a
                // cycle books base it never sold. The sliver stays in the
                // account as fee float, where the next commission finds it.
                $held = $this->filters->qtyToStep(bcsub($executed, $short, 8));
                // once per daemon life as an Alert — the operator fixes it on
                // the ACCOUNT (fund the float), every later fill just repeats it
                $this->log->write($this->feeNettedAlerted ? 'Info' : 'Alert', 'fee_netted', sprintf(
                    '%s: %s %s of the fill was taken as commission and no fee float covers it — holding %s of %s. Keep BNB in the account (fee mode ON) to stop paying fees out of inventory',
                    $row->getClientOrderId(), $short, $base, $held, $executed
                ));
                $this->feeNettedAlerted = true;
            }
        }
        $this->store->markFilled($row, $held, $fee, $cost['asset']);
        $this->balDirty = true;
        return [$held, $fee];
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
    /** @internal engine access — floor a qty to the exchange's lot step. The
     *  sliver between steps can never be an order: every other booking path
     *  writes it off at the fill (bookFill's qtyToStep), and a level that
     *  holds one sells short of itself for good. */
    public function qtyToStep(string $qty): string
    {
        return $this->filters->qtyToStep($qty);
    }

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

    /** @internal engine access
     *  Request a clean daemon restart after this tick, exactly like the
     *  command-path reload: supervisor relaunches and boot rehydrates from
     *  persisted DB state. */
    public function requestReload(string $reason, string $kind = 'bot_restart'): void
    {
        if ($this->reloadRequested) {
            return;
        }
        $this->reloadRequested = true;
        $this->reloadReason = $reason;
        $this->log->write('Info', $kind, $reason . ' — reloading (boot rehydrates from the DB)');
    }

    /** Consume pending bot_command rows. Kill/Flatten set the switch; the tick
     *  loop then idles the daemon (heartbeat only) rather than exiting — so
     *  there's no systemd restart loop and the watchdog stays quiet.
     *  $price is this tick's mark: Flatten is priced against it (LossGuard). */
    private function consumeCommands(string $price): void
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
                    if ($this->flattenPending) {
                        // a plain Kill is the operator saying "stop": it stops
                        // the liquidation too. Nothing on the book is touched —
                        // the exits the chase already placed keep working — but
                        // no more are cancelled and re-priced. There was no way
                        // to stop a running flatten at all: killApplied made
                        // ensureKilled a no-op and the chase read its own flag.
                        $this->log->write('Alert', 'flatten_stopped', sprintf(
                            'Kill received while a liquidation was chasing — the chase stops, %d exit(s) stay working as they are; inventory is HELD',
                            count($this->workingExits())
                        ));
                        $this->endFlatten('a plain Kill stopped it');
                    }
                    $this->run->setKillSwitch(true);
                    $this->run->save();
                    break;
                case 'Flatten':
                    $refusal = null;
                    if (!ProfilePolicy::allowsRealizedLoss((string) ($this->run->getProfile() ?: 'Balanced'))) {
                        // NoLoss: never realize — refuse, don't kill, leave inventory
                        $refusal = 'profile NoLoss forbids realizing losses — Flatten refused (plain Kill holds inventory and stays allowed)';
                    } elseif (!$this->run->getSellAtLoss()) {
                        // The switch forbids realizing LOSSES, not gains — the
                        // same rule the stop-out path has always applied
                        // (TrendEngine::mayExitAt). Priced here, at execution
                        // time, off the tick's live price: whatever the tool's
                        // pre-flight saw when the command was enqueued may be
                        // minutes old by now.
                        $vwap = LossGuard::positionVwap($this->store);
                        if (LossGuard::wouldRealizeLoss($vwap, (string) ($this->config['fee_pct'] ?? '0'), $price)) {
                            $refusal = sprintf(
                                "run switch 'Sell at loss' is OFF and flattening at %s would realize a loss (cost basis %s, breakeven %s) — Flatten refused "
                                . '(turn the switch on to allow it, or wait for the exit to clear cost; plain Kill holds inventory and stays allowed)',
                                $price,
                                (string) $vwap,
                                LossGuard::breakeven((string) $vwap, (string) ($this->config['fee_pct'] ?? '0'))
                            );
                        }
                    }
                    if ($refusal !== null) {
                        $cmd->setCmdStatus('Failed');
                        $cmd->save();
                        $this->log->write('Alert', 'flatten_refused', $refusal);
                        continue 2;
                    }
                    $this->flattenOnKill = true;
                    $this->run->setKillSwitch(true);
                    $this->run->save();
                    if ($this->killApplied) {
                        // The run is ALREADY killed, so ensureKilled below is a
                        // no-op and onKill — where a flatten starts — never
                        // runs: a Flatten sent to a killed run did nothing at
                        // all. It is also how an operator restarts a chase that
                        // gave up (flatten_stalled says to), so it starts here.
                        $this->flattenOnKill = false;
                        $this->beginFlatten('commanded liquidation (the run was already killed)');
                        $this->flattenInventory($price);
                    }
                    break;
                case 'CancelBuys':
                    $this->cancelOpenBuys();
                    break;
                case 'Reload':
                    // acknowledged below, then the tick returns false and the
                    // process exits cleanly — systemd or the watchdog
                    // supervisor relaunches it; boot rehydrates from the DB
                    $this->requestReload('restart requested via command');
                    break;
            }
            $cmd->setCmdStatus('Done');
            $cmd->save();
        }
    }

    /**
     * Kill switch: stop entries, cancel open buys, optionally flatten.
     *
     * The flatten INTENT is consumed here and cleared here. It belongs to the
     * command that raised the kill, never to the process: kills arrive from
     * outside this daemon too (DrawdownGuard::tripAll on a sibling, a retire
     * that holds, the dashboard's Kill), they carry no command, and every one
     * of them means "cancel buys, HOLD". A `flattenOnKill` left standing from
     * an earlier Flatten made all of them liquidate — the global drawdown stop
     * says "inventory HELD" in the same breath as selling it.
     *
     * The announcement is per KILL, not per process: `kill` is an URGENT
     * (unthrottled, instant) Telegram alert, and onKill runs on every boot of
     * an already-killed run — a restart loop re-announced the same stop until
     * Telegram's own rate limit started dropping messages.
     */
    private function onKill(bool $flatten, ?string $price = null): void
    {
        $this->flattenOnKill = false;
        if ($this->killAnnounced()) {
            $this->log->write('Info', 'kill_seen', $flatten
                ? 'still killed at boot with a liquidation outstanding — resuming it (announced once, when it happened)'
                : 'still killed at boot — buys canceled, inventory held (announced once, when it happened)');
        } else {
            $this->log->write('Alert', 'kill', $flatten
                ? 'kill+flatten: canceling buys, selling inventory'
                : 'kill: canceling open buys, holding inventory');
        }
        $this->cancelOpenBuys();
        if (!$flatten) {
            return;
        }
        if ($price === null) {
            // no mark to sell against (a kill raised outside a tick) — hold
            // rather than guess a price
            $this->log->write('Alert', 'flatten_manual', 'no live mark available for the liquidation; open sells left working, review inventory manually');
            return;
        }
        $this->beginFlatten('commanded liquidation');
        $this->flattenInventory($price);
    }

    /**
     * Has the operator already been told this run is killed? The newest of the
     * kill/resume markers answers it across restarts (bot_event is the only
     * state that survives a process, and adding a column needs a build).
     */
    private function killAnnounced(): bool
    {
        $last = $this->killEpisodeMarker();
        return $last !== null && (string) $last->getKind() === 'kill';
    }

    /** The newest kill/resume marker — the boundary of the current kill
     *  episode. Everything a kill owns (its announcement, its flatten intent)
     *  is younger than this row. */
    private function killEpisodeMarker(): ?\App\BotEvent
    {
        return BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind(['kill', 'restart'], \Criteria::IN)
            ->orderByIdBotEvent(\Criteria::DESC)
            ->findOne();
    }

    /**
     * Close the kill episode durably: the `restart` marker, plus the flatten
     * intent it may have carried. Belt to the braces of endFlatten() — that
     * one only writes flatten_done when THIS process is the one holding the
     * chase, and the whole point here is the resumes no process witnessed.
     */
    private function markResumed(string $why): void
    {
        $this->log->write('Info', 'restart', $why);
        $marker = $this->flattenMarker();
        if ($marker !== null && (string) $marker->getKind() === 'flatten_pending') {
            $this->log->write('Info', 'flatten_done', 'the liquidation is no longer running: the run resumed trading');
        }
    }

    // ── the liquidation and its chase ───────────────────────────────────

    /**
     * Start (or adopt) a liquidation. The marker pair flatten_pending /
     * flatten_done is the only durable record of "a commanded close is still
     * running": a daemon restarted mid-chase (a deploy, the watchdog, an
     * algo_update — all routine) came back with the flag false and abandoned
     * the liquidation, leaving limits resting above a market that had already
     * left them, and announced "holding inventory" while doing it.
     */
    private function beginFlatten(string $why): void
    {
        if ($this->flattenPending) {
            return;
        }
        $this->flattenPending = true;
        $this->flattenStalled = false;
        $this->flattenSince = time();
        $this->chaseCursor = null;
        $this->log->write('Info', 'flatten_pending', $why . ' — chasing the exits down until the book is flat');
    }

    /** Stop chasing, for any reason, and say so once. Idempotent. */
    private function endFlatten(string $why): void
    {
        if (!$this->flattenPending) {
            return;
        }
        $this->flattenPending = false;
        $this->flattenSince = null;
        $this->chaseCursor = null;
        $this->log->write('Info', 'flatten_done', 'the liquidation is no longer running: ' . $why);
    }

    /** The newest flatten_pending / flatten_done marker: the durable record of
     *  whether a commanded liquidation is still running. */
    private function flattenMarker(): ?\App\BotEvent
    {
        return BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind(['flatten_pending', 'flatten_done'], \Criteria::IN)
            ->orderByIdBotEvent(\Criteria::DESC)
            ->findOne();
    }

    /** Boot: a liquidation that outlived its daemon is picked up again, with
     *  the kill already applied (buys are canceled below, not re-announced). */
    private function restoreFlatten(): void
    {
        $marker = $this->flattenMarker();
        if ($marker === null || (string) $marker->getKind() !== 'flatten_pending') {
            return;
        }
        // …and only from THIS kill episode. A flatten intent belongs to the
        // command that raised the kill it ran under (see onKill) — carried
        // across a resume it liquidates a later kill that had decided to HOLD:
        // drawdown_stop, retire-and-hold, the dashboard's Kill. The markers are
        // written in order (`kill` then `flatten_pending` for a kill+flatten; a
        // Flatten sent to an already-killed run writes its own fresh pending),
        // so an intent older than the newest kill/resume marker is an intent
        // from an episode that is over.
        $episode = $this->killEpisodeMarker();
        if ($episode !== null && (int) $episode->getIdBotEvent() > (int) $marker->getIdBotEvent()) {
            $this->log->write('Info', 'flatten_done', sprintf(
                'a liquidation commanded %s belongs to an earlier kill — this kill holds inventory; not resuming it',
                $marker->getDateCreation('Y-m-d H:i:s')
            ));
            return;
        }
        $this->flattenPending = true;
        $this->flattenStalled = false;
        $this->flattenSince = (int) $marker->getDateCreation('U') ?: time();
        $this->killApplied = true; // the kill itself was applied before the restart
        $this->log->write('Info', 'flatten_resumed', sprintf(
            'a commanded liquidation was still running when this daemon restarted (commanded %s) — resuming the chase; buys stay canceled',
            $marker->getDateCreation('Y-m-d H:i:s')
        ));
        $this->cancelOpenBuys(); // belt to the original kill's braces
    }

    /**
     * The price a commanded liquidation sells at. A limit resting AT the last
     * mark is a maker order: in the fast drop a flatten exists for, the market
     * is under it before it is on the book, and it never fills. Priced
     * FLATTEN_CROSS_PCT under the mark it takes the bids instead — filled at
     * the bids' own prices, the limit only bounding the slippage — and
     * chaseFlatten() walks it down while anything is left. Never under the
     * lot's breakeven when 'Sell at loss' is OFF: the policy outranks the exit.
     *
     * @internal engine access
     */
    public function marketableExit(string $mark, ?string $basis): string
    {
        $target = bcmul($mark, bcsub('1', self::FLATTEN_CROSS_PCT, 8), 8);
        if ($basis !== null && $basis !== '' && !$this->run->getSellAtLoss()) {
            $floor = LossGuard::breakeven($basis, (string) ($this->config['fee_pct'] ?? '0'));
            if (bccomp($target, $floor, 8) < 0) {
                $target = bcadd($floor, '0', 8);
            }
        }
        return $target;
    }

    /**
     * Is this resting exit far enough above the target to be worth a cancel
     * AND a place? max(CHASE_DEADBAND_PCT, CHASE_DEADBAND_TICKS ticks) — the
     * tick term matters on a wide-tick symbol, where the re-placed price is
     * rounded UP to the tick and would otherwise sit a fraction above its own
     * target forever, re-pricing itself every tick on a market that never
     * moved.
     *
     * @internal engine access — an engine chases its own exits by the same rule
     */
    public function chaseNeedsReprice(string $resting, string $target): bool
    {
        $gap = bcsub($resting, $target, 12);
        if (bccomp($gap, '0', 12) <= 0) {
            return false; // already marketable at this mark — it is filling
        }
        $band = bcmul($target, self::CHASE_DEADBAND_PCT, 12);
        $ticks = bcmul((string) self::CHASE_DEADBAND_TICKS, $this->filters->tickSize(), 12);
        if (bccomp($ticks, $band, 12) > 0) {
            $band = $ticks;
        }
        return bccomp($gap, $band, 12) > 0;
    }

    /** Take one slot of this tick's reprice budget; false = the rest of the
     *  book waits for the next tick (the cursor makes sure it is a different
     *  part of the book next time).
     *  @internal engine access */
    public function chaseTakeBudget(): bool
    {
        if ($this->chaseBudget <= 0) {
            return false;
        }
        $this->chaseBudget--;
        return true;
    }

    /** Re-price whatever a kill+flatten has not sold yet. Silent: the first
     *  pass said what is being sold and what is being kept. */
    private function chaseFlatten(string $price): void
    {
        if (!$this->flattenPending || $this->dryRun) {
            return;
        }
        // A liquidation that went flat is FINISHED, and has to say so: the
        // intent was only ever closed by a resume, a plain Kill or the
        // deadline, so a close that sold everything in ten seconds stayed
        // "pending" for fifteen minutes — long enough for a restart to adopt
        // it (see restoreFlatten) — and then announced itself STALLED.
        if ($this->flattenIsDone()) {
            $this->endFlatten('the book is flat');
            return;
        }
        if ($this->flattenSince !== null && time() - $this->flattenSince > self::CHASE_DEADLINE) {
            // Inventory with no working exit at all is the one state this must
            // not walk away from: a cancel that landed while its replacement
            // failed (the engine's own chase, see TrendEngine::liquidate) is
            // real base with nothing selling it. One last placement attempt
            // before giving up — and if the loss rules hold it back (sell_at
            // _loss OFF under cost), say THAT, instead of claiming the exits
            // are on the book.
            //
            // That attempt is the ENGINE's: shellExits() is empty by the
            // branch condition above, so the reprice loop has nothing to walk
            // and only engine->liquidate() can place. For a Grid it is
            // therefore a deliberate no-op (GridEngine::liquidate is empty —
            // a grid's position IS its open book), and the grid's own re-arm
            // has already run earlier in this tick: detectFills →
            // resolveMissing → onSellCanceled re-places the exit repriceExit
            // left open. The $exits === 0 alert below is what a grid that
            // still has bare inventory here gets, and it is truthful.
            if ($this->workingExits() === []) {
                try {
                    $this->flattenInventory($price, true);
                } catch (Gateway\BinanceApiError $e) {
                    $this->log->write('Error', 'flatten_exit', 'the last attempt to re-arm the exit failed: ' . $e->getMessage());
                }
                if ($this->flattenIsDone()) {
                    $this->endFlatten('the book is flat'); // that attempt closed it out
                    return;
                }
            }
            $exits = count($this->workingExits());
            if (!$this->flattenStalled) {
                $this->flattenStalled = true;
                $this->log->write('Alert', 'flatten_stalled', $exits > 0
                    ? sprintf(
                        'the liquidation has been chasing for %d min without going flat (%d exit(s) still working at %s) — '
                        . 'chasing stops here: the exits stay on the book, nothing is canceled, and this is a liquidity '
                        . 'problem more orders cannot solve. Flatten again to restart the chase, or work the book by hand',
                        (int) ((time() - $this->flattenSince) / 60),
                        $exits,
                        $price
                    )
                    : sprintf(
                        'the liquidation has been chasing for %d min without going flat and %s base is held with NO exit '
                        . 'working at %s (the exit could not be re-placed, or the loss rules hold it back) — chasing stops '
                        . 'here; guard or sell that inventory by hand',
                        (int) ((time() - $this->flattenSince) / 60),
                        $this->engine->heldQty(),
                        $price
                    ));
            }
            $this->endFlatten('the chase deadline expired');
            return;
        }
        $this->flattenInventory($price, true);
    }

    /** Nothing left for the liquidation to do: no working exit and no
     *  inventory behind one (the engine's own position included). */
    private function flattenIsDone(): bool
    {
        return $this->workingExits() === []
            && bccomp($this->engine->heldQty(), '0', 8) <= 0;
    }

    /** @return \App\BotOrder[] working sells, whoever placed them */
    private function workingExits(): array
    {
        return array_values(array_filter(
            $this->store->openOrderObjects(),
            static fn (\App\BotOrder $r): bool => (string) $r->getSide() === 'Sell'
        ));
    }

    /**
     * The exits the SHELL owns: genuine legacy exits (off-machine inventory,
     * resolved by resolveLegacy whatever engine runs) and — for a grid — its
     * ladder exits. An engine's own working sell is NOT touched here: the
     * shell used to cancel it and re-place it as a legacy row, which made
     * TrendEngine::hasOpenOwnOrders('Sell') read "no exit working" and
     * liquidate() place a SECOND sell for the whole position, every chase
     * tick, until the book carried several times the inventory. An engine
     * chases its own exits (StrategyEngine::liquidate).
     *
     * @return \App\BotOrder[]
     */
    private function shellExits(): array
    {
        $grid = $this->runAlgo() === 'Grid';
        return array_values(array_filter(
            $this->workingExits(),
            static fn (\App\BotOrder $r): bool => (bool) $r->getIsLegacy() || $grid
        ));
    }

    /**
     * Round-robin: continue after the lot the last reprice budget stopped at,
     * so a book with more lots than CHASE_MAX_REPRICES still walks all of them
     * down instead of spending every tick on the first four.
     *
     * The cursor is a POSITION, not an identity: it is the id the last budget
     * stopped at, and the rows are oldest-first (OrderStore::openOrderObjects),
     * so resuming means "the first lot whose id is above it", wrapping around.
     * Matching the cursor row itself never worked — repricing a lot cancels it,
     * which takes it straight out of the open book, so the cursor was never
     * found again and every tick restarted at the front of the list. What kept
     * a 12-lot flatten moving was luck: the replacements happened to come back
     * from the database last.
     *
     * @param  \App\BotOrder[] $rows
     * @return \App\BotOrder[]
     */
    private function chaseOrder(array $rows): array
    {
        if ($this->chaseCursor === null || count($rows) < 2) {
            return $rows;
        }
        foreach ($rows as $i => $row) {
            if ((int) $row->getIdBotOrder() > $this->chaseCursor) {
                return $i === 0 ? $rows : array_merge(array_slice($rows, $i), array_slice($rows, 0, $i));
            }
        }
        return $rows; // the whole book is older than the cursor — start again at the front
    }

    /**
     * A lot's cost basis, for the loss rules and for the breakeven floor: the
     * buy pinned on the row, else the filled buy on its ladder level, else the
     * position's own VWAP. A lot whose basis cannot be established at all is
     * KEPT while 'Sell at loss' is OFF (see flattenInventory) — the old code
     * read a null basis as "not a loss" (LossGuard::wouldRealizeLoss says so
     * by design) and sold it with no floor under it, which is exactly the
     * trade the switch exists to forbid.
     */
    private function exitBasis(\App\BotOrder $row): ?string
    {
        $basis = $row->getLegacyBuyPrice() !== null
            ? (string) $row->getLegacyBuyPrice()
            : $this->matchedBuyPrice((int) $row->getLevelIdx());
        return $basis ?? LossGuard::positionVwap($this->store);
    }

    /**
     * The liquidation half of kill+flatten: every working exit the shell owns
     * is repriced marketable and re-priced again each tick while the market
     * runs away from it, and inventory an engine holds with no working exit of
     * its own is sold by that engine (StrategyEngine::liquidate).
     *
     * Until 2026-09-18 this did nothing at all ("v1 flatten = cancel sells and
     * alert for manual review rather than blind market-selling"), which left a
     * commanded close closing nothing — and a core Trend run, whose position
     * carries no trailing stop, with no seller in the system at all.
     *
     * The loss rules still bind and bind PER LOT: with grid_run.sell_at_loss
     * OFF an exit is only repriced when the mark clears THAT lot's own cost, so
     * a flatten never books a loss on a lot just because the position as a
     * whole is green. Whatever that leaves working is named in one Alert.
     */
    private function flattenInventory(string $price, bool $chasing = false): void
    {
        $fee = (string) ($this->config['fee_pct'] ?? '0');
        $sellAtLoss = (bool) $this->run->getSellAtLoss();
        $kept = [];
        $candidates = $this->shellExits();
        $book = array_map(static fn (\App\BotOrder $r): int => (int) $r->getIdBotOrder(), $this->workingExits());
        $this->liquidating = true;
        try {
            foreach ($this->chaseOrder($candidates) as $row) {
                $basis = $this->exitBasis($row);
                if (!$sellAtLoss && ($basis === null || LossGuard::wouldRealizeLoss($basis, $fee, $price))) {
                    $kept[] = sprintf(
                        '%s (qty %s @ %s, cost %s)',
                        (string) $row->getClientOrderId(),
                        (string) $row->getQty(),
                        (string) $row->getPrice(),
                        $basis ?? 'UNKNOWN — not sold on a guess'
                    );
                    continue;
                }
                $target = $this->marketableExit($price, $basis);
                if (!$this->chaseNeedsReprice((string) $row->getPrice(), $target)) {
                    continue;
                }
                if (!$this->chaseTakeBudget()) {
                    continue; // this tick's order-rate budget is spent
                }
                $this->chaseCursor = (int) $row->getIdBotOrder();
                $this->repriceExit($row, $target, $price, $basis, $chasing);
            }
            $this->engine->liquidate($price);
        } finally {
            $this->liquidating = false;
        }
        if ($kept !== [] && !$chasing) {
            $this->log->write('Alert', 'flatten_partial', sprintf(
                "flatten left %d lot(s) working: selling at %s would realize a loss on them (or their cost basis is unknown) and 'Sell at loss' is OFF — %s",
                count($kept),
                $price,
                implode('; ', $kept)
            ));
        }
        // A pass that kept EVERY lot and moved nothing has finished the
        // liquidation: the chase can only ever reprice these same lots, and it
        // is forbidden to. Left pending it idled for the whole CHASE_DEADLINE
        // and then announced a LIQUIDITY stall — about a book that is fine,
        // with advice ("Flatten again") that repeats the identical hold. The
        // kept exits stay working at their own (profitable) prices, which is
        // what the policy wants. Not gated on $chasing: the ordinary shape is
        // a first pass that sells what it may and a SECOND one, a tick later,
        // that finds only held lots left. An unchanged id list is the exact
        // test — a lot that filled, or an exit the engine placed or repriced,
        // changes it and the chase carries on.
        if ($kept !== [] && count($kept) === count($candidates)
            && array_map(static fn (\App\BotOrder $r): int => (int) $r->getIdBotOrder(), $this->workingExits()) === $book) {
            $this->endFlatten('every remaining lot is held back by the loss policy');
        }
    }

    /**
     * Cancel one working exit and put it back marketable.
     *
     * Two rules paid for in blood:
     *  - whatever traded between the last look and the cancel is REAL. The
     *    cancel response carries it; markCanceled() dropped it, so the coins
     *    were gone from the account and still on our books, no cycle, no
     *    realized P/L — and the replacement was sized off a filled_qty that
     *    had stopped being updated, i.e. bigger than what was left to sell.
     *  - the ledger row stays OPEN until the replacement is on the book. A
     *    failed re-place used to leave a Canceled row, which no longer appears
     *    in openOrderObjects(): the next chase tick could not see the lot at
     *    all and the inventory sat unguarded forever behind a single Alert.
     *    Left open, the next tick's detectFills → resolveMissing (grid) /
     *    resolveLegacy (legacy) sees it gone from the exchange, books whatever
     *    executed and re-arms the exit — and the chase re-prices that one.
     */
    private function repriceExit(\App\BotOrder $row, string $target, string $price, ?string $basis, bool $chasing): void
    {
        $cid = (string) $row->getClientOrderId();
        $level = (int) $row->getLevelIdx();
        $was = (string) $row->getPrice();
        $res = null;
        if (!$this->dryRun) {
            try {
                $res = $this->gateway->cancelOrder($this->run->getSymbol(), $cid);
            } catch (Gateway\BinanceApiError $e) {
                $this->logCancelFailure($e, $cid, 'flatten');
                return;
            }
        }
        $executed = (string) ($res['executedQty'] ?? $row->getFilledQty() ?? '0');
        if (bccomp($executed, (string) ($row->getFilledQty() ?: '0'), 8) > 0) {
            // the ledger's idea of this order is out of date — fix it BEFORE
            // anything sizes a replacement off it
            $this->store->markPartFilled($row, $executed);
            $this->balDirty = true;
        }
        $rem = bcsub((string) $row->getQty(), $executed, 12);
        // the replacement carries the buy-leg fee the cycle below will NOT
        // book (closeCanceledExit prorates by the same share), so the lot pays
        // its entry fee exactly once however many pieces it is exited in
        $buyFee = $this->exitBuyFee($row);
        $share = bccomp((string) $row->getQty(), '0', 12) > 0
            ? bcdiv($executed, (string) $row->getQty(), 12)
            : '1';
        $carryFee = bcsub($buyFee, bcmul($buyFee, $share, 12), 12);
        if (bccomp($rem, '0', 12) <= 0 || $this->filters->normalize('Sell', $target, $rem) === null) {
            // nothing sellable left (it filled, or the remainder is dust)
            $this->closeCanceledExit($row, $executed, $basis, $rem);
            return;
        }
        if (!$this->placeLegacyExit($level, $target, $rem, $basis, $carryFee)) {
            $this->log->write('Alert', 'flatten_exit', sprintf(
                'flatten: exit %s (qty %s @ %s) could not be re-placed at %s — the ledger row is left open, '
                . 'so the next tick resolves it off the exchange and re-arms the exit; the chase then re-prices it',
                $cid,
                $rem,
                $was,
                $target
            ));
            return;
        }
        $this->closeCanceledExit($row, $executed, $basis, $rem);
        $this->log->write($chasing ? 'Info' : 'Alert', 'flatten_exit', sprintf(
            'flatten: exit %s (qty %s @ %s) repriced to %s, under the live %s',
            $cid,
            $rem,
            $was,
            $target,
            $price
        ));
    }

    /**
     * Close the ledger row of an exit the flatten canceled: book the part that
     * traded (cycle, realized P/L, fees) and only then mark it gone. A grid
     * level whose exit has been carried off the ladder is left EMPTY — the
     * machine still calling it SELL_OPEN while a legacy row guards the same
     * coins double-counts them in heldQty(), which is what the unrealized stop
     * and the killed-drawdown watch mark to market.
     *
     * …and an EMPTY level re-arms its buy the moment the run resumes, so the
     * ladder must be re-sized first: its per-level qty was allocated against a
     * budget that did not reserve the lot now sitting in a legacy exit
     * (buildLadder: "Quote a new ladder must NOT re-spend"). Nothing else
     * re-anchors it — the geometry on the row has not changed — so blank the
     * signature here exactly as the starved release does, and the first
     * resumed tick refits onto budget − legacy reserve before arming anything
     * (Repro2, BUG C). A restart in between gets the same result from boot's
     * own buildLadder.
     */
    private function closeCanceledExit(\App\BotOrder $row, string $executed, ?string $basis, string $rem): void
    {
        $level = (int) $row->getLevelIdx();
        $wasLadder = !$row->getIsLegacy() && $this->runAlgo() === 'Grid';
        if (bccomp($executed, '0', 8) > 0) {
            $qty = (string) $row->getQty();
            [$held, $fee] = $this->bookFill($row, (string) ($row->getExchangeOrderId() ?? ''), $executed);
            $this->recordExitCycle($row, $held, $fee, $basis, $qty);
            if (bccomp($rem, '0', 12) > 0) {
                $this->log->write('Alert', 'flatten_partial_booked', sprintf(
                    '%s had already traded %s of %s when the flatten canceled it — booked; the rest is re-placed',
                    (string) $row->getClientOrderId(),
                    $executed,
                    $qty
                ));
            }
        } else {
            $this->store->markCanceled($row);
        }
        if ($wasLadder) {
            $this->machine->hydrateLevel($level, 'EMPTY');
            $this->reanchorLadder(sprintf('L%02d\'s lot was carried off the ladder by the liquidation', $level));
        }
    }

    /**
     * Ask for a refit onto the CURRENT free capital without changing the
     * geometry: blanking the signature makes geometryChanged() true, so the
     * grid's next tick runs maybeRefit and buildLadder re-derives the
     * per-level qty from budget − legacyReserveQuote. Used whenever inventory
     * leaves the ladder without its capital leaving with it.
     */
    private function reanchorLadder(string $why): void
    {
        if ($this->runAlgo() !== 'Grid' || $this->geomSig === '') {
            return; // not a ladder, or a refit is already pending
        }
        $this->geomSig = '';
        $this->log->write('Info', 'ladder_reanchor', sprintf(
            '%s — re-anchoring the ladder on the budget those exits do not tie up before any level re-arms',
            $why
        ));
    }

    /**
     * The buy-leg fee an exit row still carries: pinned on the row by whoever
     * knew which purchase this inventory came from (placeLegacyExit /
     * OrderStore::pinBuyAttribution), else the matched buy's own recorded fee.
     * ONE definition, because the piece that books a cycle and the piece that
     * re-places the remainder have to agree on the total they are splitting.
     *
     * @internal engine access
     */
    public function exitBuyFee(\App\BotOrder $row): string
    {
        return $row->getLegacyBuyFee() !== null
            ? (string) $row->getLegacyBuyFee()
            : $this->matchedBuyFee((int) $row->getLevelIdx());
    }

    /**
     * Book the cycle for a (whole or partial) exit fill the shell resolved
     * itself. Mirrors resolveLegacy's arithmetic, with the buy leg's fee
     * PRORATED: only the part that traded is being closed.
     *
     * @return string the part of the buy-leg fee this cycle booked — the
     *         caller re-places the remainder carrying exactly the rest
     *         (exitBuyFee − this), so a lot exited in pieces pays its entry
     *         fee once and only once.
     */
    private function recordExitCycle(\App\BotOrder $row, string $qty, string $sellFee, ?string $basis, string $orderQty): string
    {
        $level = (int) $row->getLevelIdx();
        $sellPrice = (string) $row->getPrice(); // bookFill put what it TRADED at on the row
        $buyPrice = $basis ?? $sellPrice;       // unknown basis books a flat cycle, never a fictional gain
        $buyFee = $this->exitBuyFee($row);
        $share = bccomp($orderQty, '0', 12) > 0 ? bcdiv($qty, $orderQty, 12) : '1';
        $bookedBuyFee = bcmul($buyFee, $share, 12);
        $fees = bcadd($sellFee, $bookedBuyFee, 12);
        $realized = bcsub(bcmul(bcsub($sellPrice, $buyPrice, 12), $qty, 12), $fees, 12);
        $this->store->recordCycle([
            'level_idx' => $level,
            'buy_price' => $buyPrice,
            'sell_price' => $sellPrice,
            'qty' => $qty,
            'realized_pnl' => $realized,
            'fees_total' => $fees,
        ]);
        $this->log->write('Info', 'cycle_closed', sprintf(
            'flatten exit cycle: buy %s → sell %s qty %s realized %s',
            $buyPrice,
            $sellPrice,
            $qty,
            $realized
        ));
        return $bookedBuyFee;
    }


    /** The filled buy this ladder level's exit books against — the lot's own
     *  cost basis (companion to matchedBuyFee). null when the level has no
     *  filled buy on this side of the ledger. */
    public function matchedBuyPrice(int $level): ?string
    {
        $row = \App\BotOrderQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterBySimulated((bool) $this->run->getSimulated())
            ->filterByLevelIdx($level)
            ->filterBySide('Buy')
            ->filterByState('Filled')
            ->orderByIdBotOrder(\Criteria::DESC)
            ->findOne();
        return $row ? (string) $row->getPrice() : null;
    }

    /** @internal engine access */
    public function cancelOpenBuys(): void
    {
        foreach ($this->store->openOrderObjects() as $row) {
            if ((string) $row->getSide() !== 'Buy') {
                continue;
            }
            $res = null;
            if (!$this->dryRun) {
                try {
                    $res = $this->gateway->cancelOrder($this->run->getSymbol(), $row->getClientOrderId());
                } catch (Gateway\BinanceApiError $e) {
                    $this->logCancelFailure($e, $row->getClientOrderId(), 'cancel buys');
                    continue;
                }
            }
            // an entry the exchange had already (partly) executed when the
            // kill's cancel landed bought real coins: booking it Canceled at
            // zero erases base the wallet holds, untracked and unguarded (the
            // same invariant cancelEntriesBookingFills states for a cutover)
            $executed = (string) ($res['executedQty'] ?? $row->getFilledQty() ?? '0');
            if (bccomp($executed, '0', 8) > 0) {
                $this->bookCanceledRemainder($row, $executed, null);
                continue;
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
            // trailing-stop floor as a fraction of the HWM (3×ATR(1h) in
            // quiet tape was a 0.5% trail — chopped out for +0.01 on
            // 2026-08-28); 0 disables the floor
            'trend_stop_floor_pct' => $r->getTrendStopFloorPct() !== null ? (string) $r->getTrendStopFloorPct() : '0',
            // Donchian = the breakout arm; EmaCross1d = the inventory core
            // (in while 1d EMA20 > EMA50, out on the cross down, no ATR stop)
            'trend_signal' => (string) ($r->getTrendSignal() ?: 'Donchian'),
            // per-run switch (default OFF): no automated sell may realize a
            // loss unless it is on — trend stop-outs below cost are ignored
            // and Flatten is refused (see SellAtLossTest)
            'sell_at_loss' => (bool) $r->getSellAtLoss(),
            // ...except when the ladder is starved and the operator allowed
            // exactly that case (releaseStarvedInventory)
            'sell_when_starved' => (bool) $r->getSellWhenStarved(),
        ];
    }
}
