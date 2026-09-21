<?php

namespace App\Domains\Dashboard;

use App\BotEventQuery;
use App\BotOrderQuery;
use App\Domains\Bot\CandleStore;
use App\Domains\Bot\DrawdownGuard;
use App\Domains\Bot\OrderStore;
use App\Domains\Bot\BudgetPool;
use App\Domains\Bot\SimWallet;
use App\GridRun;
use App\GridRunQuery;
use App\TradeCycleQuery;

/**
 * Exchange-free query layer for the dashboard — plain DB reads over the active
 * run's rows, returning scalars/arrays so DashboardRenderer has zero ORM
 * coupling (and can be unit-tested with a hand-built view model). No Binance
 * calls: the daemon owns the exchange; the dashboard reflects persisted state
 * — the trading chart included (chartModel() reads the collector-fed
 * CandleStore, never the exchange).
 */
final class DashboardData
{
    private const SCALE = 12;

    private ?GridRun $run;

    public function __construct(?GridRun $run = null)
    {
        $this->run = $run ?? $this->resolveActiveRun();
    }

    /** Every run a tab should exist for: Live/Testnet/DryRun, oldest first. */
    public static function activeRuns(): array
    {
        // Halted runs keep their tab: a run parked via "Hold funds" must stay
        // reachable so its "Release funds" button has somewhere to live.
        return GridRunQuery::create()
            ->filterByStatus(['Live', 'Testnet', 'DryRun', 'Halted'], \Criteria::IN)
            ->orderByIdGridRun()
            ->find()
            ->getArrayCopy();
    }

    /**
     * Pick the run to display: the requested id when it belongs to an active
     * run, else the first active run. Null when nothing is active (callers
     * fall back to the single-run resolution below).
     *
     * @param GridRun[] $active
     */
    public static function selectRun(array $active, ?int $requestedId): ?GridRun
    {
        foreach ($active as $run) {
            if ($requestedId !== null && (int) $run->getIdGridRun() === $requestedId) {
                return $run;
            }
        }
        // Default tab: prefer a run that's actually trading over a held one.
        foreach ($active as $run) {
            if ((string) $run->getStatus() !== 'Halted') {
                return $run;
            }
        }
        return $active[0] ?? null;
    }

    /**
     * Tab-bar view model: one entry per active run with the health signals the
     * renderer turns into dots (fresh / stale / kill).
     *
     * @param GridRun[] $active
     */
    public static function tabs(array $active, int $selectedId, string $baseUrl): array
    {
        $tabs = [];
        foreach ($active as $run) {
            $id = (int) $run->getIdGridRun();
            $tabs[] = [
                'id' => $id,
                'symbol' => (string) $run->getSymbol(),
                'status' => (string) $run->getStatus(),
                'kill_switch' => (bool) $run->getKillSwitch(),
                'held' => self::isHeld($run),
                'heartbeat_stale' => self::heartbeatStale($run),
                'selected' => $id === $selectedId,
                'href' => rtrim($baseUrl, '/') . '/?run=' . $id,
            ];
        }
        return $tabs;
    }

    /**
     * A Halted run is parked by the operator (Hold funds / manual status):
     * its daemon exited on purpose and the watchdog does not respawn it, so
     * its frozen tick is expected — not a stale heartbeat (prod run 9,
     * 2026-09-15: halted trend arm read "heartbeat stale" for a day).
     */
    public static function isHeld(GridRun $run): bool
    {
        return (string) $run->getStatus() === 'Halted';
    }

    /** Heartbeat older than 90 s (or never ticked) on a run that should be running. */
    public static function heartbeatStale(GridRun $run): bool
    {
        if (self::isHeld($run)) {
            return false;
        }
        $last = $run->getLastTickAt('Y-m-d H:i:s');
        $age = $last ? (time() - strtotime($last)) : null;
        return $age === null || $age > 90;
    }

    /** Latest running (or otherwise latest non-Done) run, mirroring the MCP tools. */
    private function resolveActiveRun(): ?GridRun
    {
        $q = GridRunQuery::create();
        $running = (clone $q)
            ->filterByStatus(['Live', 'Testnet', 'DryRun'], \Criteria::IN)
            ->orderByIdGridRun(\Criteria::DESC)
            ->findOne();
        if ($running) {
            return $running;
        }
        return GridRunQuery::create()
            ->filterByStatus('Done', \Criteria::NOT_EQUAL)
            ->orderByIdGridRun(\Criteria::DESC)
            ->findOne()
            ?: GridRunQuery::create()->orderByIdGridRun(\Criteria::DESC)->findOne();
    }

    public function hasRun(): bool
    {
        return $this->run !== null;
    }

    /** Every per-run TradeCycle stat query routes through here — mode-scoped
     *  to the run's current simulated/real switch (see filterBySimulated). */
    private function cycleQ(): TradeCycleQuery
    {
        return TradeCycleQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterBySimulated((bool) $this->run->getSimulated());
    }

    /** Every per-run BotOrder stat query routes through here — mode-scoped. */
    private function orderQ(): BotOrderQuery
    {
        return BotOrderQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterBySimulated((bool) $this->run->getSimulated());
    }

    public function runHeader(): ?array
    {
        if (!$this->run) {
            return null;
        }
        $last = $this->run->getLastTickAt('Y-m-d H:i:s');
        return [
            'id' => (int) $this->run->getIdGridRun(),
            'label' => (string) $this->run->getLabel(),
            'symbol' => (string) $this->run->getSymbol(),
            'status' => (string) $this->run->getStatus(),
            'kill_switch' => (bool) $this->run->getKillSwitch(),
            'sell_at_loss' => (bool) $this->run->getSellAtLoss(),
            'sell_when_starved' => (bool) $this->run->getSellWhenStarved(),
            'deploy_pct' => (int) $this->run->getDeployPct(), // 0 = entries off, exits only (see the renderer's pill)
            'last_tick_at' => $last ?: '—',
            'held' => self::isHeld($this->run),
            'heartbeat_stale' => self::heartbeatStale($this->run),
            'p_low' => (string) $this->run->getPLow(),
            'p_high' => (string) $this->run->getPHigh(),
            'last_price' => (string) ($this->run->getLastPrice() ?? ''),
        ];
    }

    public function kpis(): array
    {
        if (!$this->run) {
            return [];
        }

        $realized = '0';
        $fees = '0';
        $cycleQty = '0';
        foreach ($this->cycleQ()->find() as $c) {
            $realized = bcadd($realized, (string) $c->getRealizedPnl(), self::SCALE);
            $fees = bcadd($fees, (string) $c->getFeesTotal(), self::SCALE);
            $cycleQty = bcadd($cycleQty, (string) $c->getQty(), self::SCALE);
        }

        $today = '0';
        foreach ($this->cycleQ()
            ->filterByDateCreation(['min' => date('Y-m-d 00:00:00')])
            ->find() as $c) {
            $today = bcadd($today, (string) $c->getRealizedPnl(), self::SCALE);
        }

        // held inventory + cost basis = filled buys minus completed cycles —
        // current ledger era only (inventory orphaned by a geometry reset is
        // wallet-held, visible in the Wallet tiles, not part of the live grid)
        $epoch = $this->run->getLedgerResetAt('Y-m-d H:i:s');
        $sinceEpoch = static function ($q) use ($epoch) {
            return $epoch ? $q->filterByDateCreation(['min' => $epoch]) : $q;
        };
        $boughtQty = '0';
        $invested = '0';
        foreach ($sinceEpoch($this->orderQ())
            ->filterBySide('Buy')
            ->filterByState('Filled')
            ->find() as $o) {
            $boughtQty = bcadd($boughtQty, (string) $o->getFilledQty(), self::SCALE);
            $invested = bcadd($invested, bcmul((string) $o->getPrice(), (string) $o->getFilledQty(), self::SCALE), self::SCALE);
        }
        $eraCycleQty = '0';
        foreach ($sinceEpoch($this->cycleQ())->find() as $c) {
            $invested = bcsub($invested, bcmul((string) $c->getBuyPrice(), (string) $c->getQty(), self::SCALE), self::SCALE);
            $eraCycleQty = bcadd($eraCycleQty, (string) $c->getQty(), self::SCALE);
        }
        $inventory = bcsub($boughtQty, $eraCycleQty, self::SCALE);
        if (bccomp($invested, '0', self::SCALE) < 0) {
            $invested = '0';
        }

        // mark-to-market from the daemon-stamped last price — no exchange call
        $unrealized = null;
        $lastPrice = (string) ($this->run->getLastPrice() ?? '');
        if ($lastPrice !== '' && bccomp($inventory, '0', 8) > 0) {
            $unrealized = bcsub(bcmul($inventory, $lastPrice, self::SCALE), $invested, self::SCALE);
        } elseif ($lastPrice !== '') {
            $unrealized = '0';
        }

        // bot-accounting free budget = budget − cost of held inventory − quote
        // committed in open buy orders (floored at 0, mirroring invested)
        $committed = '0';
        foreach ($this->orderQ()
            ->filterBySide('Buy')
            ->filterByState('BUY_OPEN')
            ->find() as $o) {
            $committed = bcadd($committed, bcmul((string) $o->getPrice(), (string) $o->getQty(), self::SCALE), self::SCALE);
        }
        $uncommitted = bcsub(bcsub((string) $this->run->getBudgetQuote(), $invested, self::SCALE), $committed, self::SCALE);
        if (bccomp($uncommitted, '0', self::SCALE) < 0) {
            $uncommitted = '0';
        }

        // quote value working in open sell orders (at their ask prices)
        $sellValue = '0';
        foreach ($this->orderQ()
            ->filterBySide('Sell')
            ->filterByState(['SELL_OPEN', 'PartFilled'], \Criteria::IN)
            ->find() as $o) {
            $sellValue = bcadd($sellValue, bcmul((string) $o->getPrice(), (string) $o->getQty(), self::SCALE), self::SCALE);
        }

        // mark-to-market values (null until the daemon stamps a price/balances)
        $inventoryValue = $lastPrice !== '' ? bcmul($inventory, $lastPrice, self::SCALE) : null;
        $balBase = $this->run->getBalBase() !== null ? (string) $this->run->getBalBase() : null;
        $balQuote = $this->run->getBalQuote() !== null ? (string) $this->run->getBalQuote() : null;
        $accountValue = ($balBase !== null && $balQuote !== null && $lastPrice !== '')
            ? bcadd($balQuote, bcmul($balBase, $lastPrice, self::SCALE), self::SCALE)
            : null;

        return [
            'realized_pnl' => $realized,
            'realized_today' => $today,
            'unrealized_pnl' => $unrealized,
            'fees' => $fees,
            'cycles' => $this->cycleQ()->count(),
            'open_buys' => $this->orderQ()->filterByState('BUY_OPEN')->count(),
            'open_sells' => $this->orderQ()->filterByState(['SELL_OPEN', 'PartFilled'], \Criteria::IN)->count(),
            'inventory' => $inventory,
            'inventory_value' => $inventoryValue,
            'invested' => $invested,
            'committed_buys' => $committed,
            'open_sell_value' => $sellValue,
            'quote_uncommitted' => $uncommitted,
            // daemon-stamped wallet balances (null until first live stamp)
            'bal_base' => $balBase,
            'bal_quote' => $balQuote,
            'account_value' => $accountValue,
        ] + $this->allocation();
    }

    /**
     * How this run's slice is being used, and what that slice earns.
     *
     * `committed` is the quote the allocator may NOT reclaim (it is tied up in
     * inventory); `idle` is what a rebalance could actually move. `per_1k_day`
     * is the capital-efficiency score auto-allocate ranks runs by — null until
     * the run has enough cycles to have earned an opinion.
     */
    private function allocation(): array
    {
        $run = $this->run;
        if ($run === null) {
            return [];
        }
        $slice = bcadd((string) $run->getBudgetQuote(), '0', 0);
        $floor = \App\Domains\Bot\Allocator::hardFloor($run);
        $mode = \App\Domains\Bot\Allocator::effectiveMode($run);

        $store = new \App\Domains\Bot\OrderStore(
            (int) $run->getIdGridRun(),
            (string) $run->getRunUid(),
            $run->getLedgerResetAt('Y-m-d H:i:s'),
            (bool) $run->getSimulated()
        );
        $invested = $store->investedQuote();
        $legacy = $store->legacyReserveQuote();
        $committed = bccomp($invested, $legacy, self::SCALE) >= 0 ? $invested : $legacy;
        $idle = bcsub($slice, $committed, self::SCALE);
        if (bccomp($idle, '0', self::SCALE) < 0) {
            $idle = '0';
        }

        $days = \App\Domains\Bot\AllocScore::windowDays();
        $score = \App\Domains\Bot\AllocScore::score($run, $days);

        return [
            'alloc_slice' => $slice,
            'alloc_floor' => $floor,
            'alloc_committed' => $committed,
            'alloc_idle' => $idle,
            'alloc_mode' => (string) ($run->getAllocMode() ?: 'Auto'),
            'alloc_auto' => $mode['auto'],
            'alloc_mode_why' => $mode['why'],
            'alloc_window_days' => $days,
            'alloc_per_1k_day' => $score['per_1k_day'],
            'alloc_net' => $score['net'],
            'alloc_cycles' => $score['cycles'],
            'alloc_eligible' => $score['eligible'],
            'alloc_why' => $score['why'],
        ];
    }

    /** Event kinds drawn as lifecycle markers on the trade chart.
     *  'reloading' is the pre-algo_update kind for code-deploy restarts —
     *  kept so history recorded before the rename still shows.
     *  algo_switch/algo_cutover/engine_missing/stranded_book are the
     *  Trend-engine seam's own lifecycle events — worth a marker on the
     *  chart same as a start/stop/restart. 'trend_signal' is deliberately
     *  NOT here (I3a, final-fix review): it fires up to ~6×/cycle (every
     *  tranche fill, every ratchet, every stop hit) and would flood the
     *  newest-20 chartMarkers() window, evicting the lifecycle markers this
     *  list exists to surface — it belongs in the event feed/Telegram, not
     *  the chart. Every kind listed here MUST have a MARKER_LABEL entry
     *  (pinned by MarkerKindCoverageTest) — a marker with no label is
     *  dropped by chartModel(). */
    private const MARKER_KINDS = [
        'algo_update', 'reloading', 'bot_start', 'bot_stop', 'bot_restart',
        'algo_switch', 'algo_cutover', 'engine_missing', 'stranded_book',
        'trend_activate', 'trend_deactivate', 'trend_release',
        'run_retired', 'run_finalized',
    ];

    /** Marker text per chart-worthy kind (after the 'reloading' rewrite). */
    private const MARKER_LABEL = [
        'algo_update' => 'algo update',
        'bot_start' => 'start',
        'bot_stop' => 'stop',
        'bot_restart' => 'restart',
        'algo_switch' => 'algo switch',
        'algo_cutover' => 'algo cutover',
        'engine_missing' => 'engine missing',
        'stranded_book' => 'stranded book',
        'trend_activate' => 'trend arm on',
        'trend_deactivate' => 'trend arm off',
        'trend_release' => 'trend slice released',
        'run_retired' => 'retired',
        'run_finalized' => 'archived',
    ];

    /**
     * Recent lifecycle events (algo deploys, start/stop/restart) for the
     * chart, oldest→newest; chartModel() places them on the candle they
     * fall in.
     */
    /** Epoch seconds of a DB stamp written by the daemon.
     *
     *  Propel stamps date_creation/date_modification in the writing
     *  process's default timezone — the daemon's, i.e. php.ini
     *  (UTC on prod). The dashboard request runs in the user's session
     *  timezone (legacy.php overrides the default), so a bare strtotime()
     *  would read a UTC stamp as local time and place every fill hours
     *  away from the candle it happened in. Parse in the writer's zone. */
    private static function stampEpoch(string $stamp): int
    {
        static $zone = null;
        $zone ??= new \DateTimeZone(ini_get('date.timezone') ?: 'UTC');
        try {
            return (new \DateTimeImmutable($stamp, $zone))->getTimestamp();
        } catch (\Exception) {
            return 0;
        }
    }

    public function chartMarkers(int $limit = 20): array
    {
        if (!$this->run) {
            return [];
        }
        $rows = BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind(self::MARKER_KINDS, \Criteria::IN)
            ->orderByIdBotEvent(\Criteria::DESC)
            ->limit($limit)
            ->find()
            ->getArrayCopy();
        $out = [];
        foreach (array_reverse($rows) as $e) {
            $kind = (string) $e->getKind();
            $out[] = [
                'kind' => $kind === 'reloading' ? 'algo_update' : $kind,
                'message' => (string) $e->getMessage(),
                'at' => $e->getDateCreation('Y-m-d H:i:s'),
            ];
        }
        return $out;
    }

    public function latestCycles(int $limit = 8): array
    {
        if (!$this->run) {
            return [];
        }
        $out = [];
        foreach ($this->cycleQ()
            ->orderByIdTradeCycle(\Criteria::DESC)
            ->limit($limit)
            ->find() as $c) {
            $out[] = [
                'level' => (int) $c->getLevelIdx(),
                'buy' => (string) $c->getBuyPrice(),
                'sell' => (string) $c->getSellPrice(),
                'qty' => (string) $c->getQty(),
                'pnl' => (string) $c->getRealizedPnl(),
                'at' => $c->getDateCreation('Y-m-d H:i:s'),
            ];
        }
        return $out;
    }

    /** Operational noise excluded from the dashboard's event feed. */
    private const EVENT_NOISE = ['boot', 'reloading', 'refit_pending', 'command', 'heartbeat', 'would_place'];

    /**
     * Meaningful recent events for the dashboard: trades (placed / buy_fill /
     * cycle_closed), refits (refit_applied / refit_by_claude / refit_rejected)
     * and risk (veto / breakout_halt / risk_kill / kill / alerts) — the noise
     * kinds are filtered out so these don't get buried. Second-precision time.
     */
    public function latestEvents(int $limit = 12): array
    {
        if (!$this->run) {
            return [];
        }
        $out = [];
        foreach (BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind(self::EVENT_NOISE, \Criteria::NOT_IN)
            ->orderByIdBotEvent(\Criteria::DESC)
            ->limit($limit)
            ->find() as $e) {
            $out[] = [
                'level' => (string) $e->getLevel(),
                'kind' => (string) $e->getKind(),
                'message' => (string) $e->getMessage(),
                'at' => $e->getDateCreation('Y-m-d H:i:s'),
            ];
        }
        return $out;
    }

    /** Realized P/L per day for the last N days (oldest→newest), zero-filled. */
    public function dailyPnl(int $days = 14): array
    {
        if (!$this->run) {
            return [];
        }
        $buckets = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $buckets[date('Y-m-d', strtotime("-$i days"))] = '0';
        }
        $since = date('Y-m-d 00:00:00', strtotime('-' . ($days - 1) . ' days'));
        foreach ($this->cycleQ()
            ->filterByDateCreation(['min' => $since])
            ->find() as $c) {
            $day = $c->getDateCreation('Y-m-d');
            if (isset($buckets[$day])) {
                $buckets[$day] = bcadd($buckets[$day], (string) $c->getRealizedPnl(), self::SCALE);
            }
        }
        $out = [];
        foreach ($buckets as $day => $pnl) {
            $out[] = ['day' => $day, 'pnl' => $pnl];
        }
        return $out;
    }

    /** Grid line prices for the chart (empty on bad geometry). */
    public function gridLevels(): array
    {
        if (!$this->run) {
            return [];
        }
        try {
            return \App\Domains\Bot\GridMath::levels(
                (string) $this->run->getPLow(),
                (string) $this->run->getPHigh(),
                (int) $this->run->getNLevels(),
                (string) $this->run->getSpacing()
            );
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Shared-wallet view model for the header tile: one shared pool covers
     * every run, so this is system-wide, not scoped to $this->run.
     *
     * Simulated (or mixed): the paper SimWallet balances, each non-USDT asset
     * valued at the matching non-Done run's last_price (matched by base
     * asset — SimWallet::assetsFor(symbol)[0]).
     *
     * Real: the exchange account is one account, so any run's stamp
     * describes the whole wallet — the freshest-stamped run's bal_quote is
     * the USDT figure, and every run's bal_base is listed under its own base
     * asset (no cross price known here, so those rows carry qty only).
     *
     * @return array{mode:string, assets:array<int, array{asset:string, qty:string, value:?string}>, total_quote:?string}
     */
    public function sharedWallet(): array
    {
        return self::computeSharedWallet();
    }

    /** @return array{mode:string, assets:array<int, array{asset:string, qty:string, value:?string}>, total_quote:?string} */
    private static function computeSharedWallet(): array
    {
        $mode = ModeSwitch::systemMode();
        $runs = GridRunQuery::create()->filterByStatus('Done', \Criteria::NOT_EQUAL)->find();

        if ($mode === 'real') {
            return self::sharedWalletRealStatic($runs->getArrayCopy());
        }

        // simulated / mixed / none — read the one shared paper wallet
        $lastPriceByBase = [];
        foreach ($runs as $r) {
            if ($r->getLastPrice() === null) {
                continue;
            }
            [$base] = SimWallet::assetsFor((string) $r->getSymbol());
            $lastPriceByBase[$base] = (string) $r->getLastPrice();
        }

        $assets = [];
        $total = null;
        foreach (SimWallet::balances() as $asset => $qty) {
            if ($asset === 'USDT') {
                $value = (string) $qty;
            } else {
                $price = $lastPriceByBase[$asset] ?? null;
                $value = $price !== null ? bcmul((string) $qty, $price, self::SCALE) : null;
            }
            $assets[] = ['asset' => (string) $asset, 'qty' => (string) $qty, 'value' => $value];
            if ($value !== null) {
                $total = bcadd($total ?? '0', $value, self::SCALE);
            }
        }

        return ['mode' => $mode, 'assets' => $assets, 'total_quote' => $total];
    }

    /** @param GridRun[] $runs */
    private static function sharedWalletRealStatic(array $runs): array
    {
        $freshestQuote = null;
        $freshestTick = null;
        $assets = [];
        foreach ($runs as $r) {
            [$base] = SimWallet::assetsFor((string) $r->getSymbol());
            if ($r->getBalBase() !== null) {
                $assets[] = ['asset' => $base, 'qty' => (string) $r->getBalBase(), 'value' => null];
            }
            $tick = $r->getLastTickAt('Y-m-d H:i:s');
            if ($r->getBalQuote() !== null && ($freshestTick === null || ($tick !== null && $tick > $freshestTick))) {
                $freshestTick = $tick;
                $freshestQuote = (string) $r->getBalQuote();
            }
        }
        if ($freshestQuote !== null) {
            array_unshift($assets, ['asset' => 'USDT', 'qty' => $freshestQuote, 'value' => $freshestQuote]);
        }
        return ['mode' => 'real', 'assets' => $assets, 'total_quote' => $freshestQuote];
    }

    /** Runs whose budget_quote counts toward the shared pool — same scope as
     *  Daemon::checkBudgetInvariant(). */
    private const BUDGET_SCOPE_STATUSES = ['DryRun', 'Testnet', 'Live'];

    /**
     * System-wide band view model rendered ABOVE the run tabs, outside any
     * run's section: mode + budget + wallet + account value + realized P/L
     * (global, current-mode-per-run) + total P/L. Static because it does not
     * depend on which run tab is selected — mirrors sharedWallet()/tabs().
     *
     * @return array{
     *   mode:string, budget:string, slices_sum:string, wallet_usdt:?string,
     *   account_value:?string, value_partial:bool, realized_total:string,
     *   realized_today:string, total_pl:?string,
     *   budget_allocatable:string, pool_reserve_pct:string,
     *   drawdown_floor:?string, drawdown_under:bool,
     *   assets:array<int, array{asset:string, qty:string, value:?string}>,
     *   holdings_value:?string, open_sell_value:string, open_buy_value:string,
     *   free_budget:string
     * }
     */
    public static function globalBand(): array
    {
        $mode = ModeSwitch::systemMode();
        $wallet = self::computeSharedWallet();
        $runs = GridRunQuery::create()->filterByStatus('Done', \Criteria::NOT_EQUAL)->find()->getArrayCopy();

        // the pool cap (follows the wallet when gtbot_use_all_funds is on);
        // total_pl stays relative to the fixed seed — that is the deposit
        $budgetFixed = SimWallet::sharedBudget();
        $budget = BudgetPool::cap();
        $useAllFunds = BudgetPool::useAllFunds();
        // What the automatic allocators may plan against (cap minus
        // gtbot_pool_reserve_pct). The tile shows it so the operator can see
        // why the slices stop short of the budget; free_budget and every
        // other number on the band stay on the cap, which is what the
        // overcommit guard enforces.
        $allocatable = BudgetPool::allocatable();
        $reservePct = BudgetPool::reservePct();
        $slicesSum = '0';
        foreach (GridRunQuery::create()->filterByStatus(self::BUDGET_SCOPE_STATUSES, \Criteria::IN)->find() as $r) {
            $slicesSum = bcadd($slicesSum, (string) $r->getBudgetQuote(), self::SCALE);
        }

        // matching non-Done run's last_price, keyed by base asset (BTC, ETH, …)
        $lastPriceByBase = [];
        foreach ($runs as $r) {
            if ($r->getLastPrice() === null) {
                continue;
            }
            [$base] = SimWallet::assetsFor((string) $r->getSymbol());
            $lastPriceByBase[$base] = (string) $r->getLastPrice();
        }

        // holdings strip: every wallet asset (USDT included) with qty + USDT
        // value at the matching run's last price ("—"/null when unpriced).
        $assets = [];
        $walletUsdt = null;
        $valuePartial = false;
        foreach ($wallet['assets'] as $a) {
            $asset = (string) $a['asset'];
            $qty = (string) $a['qty'];
            if ($asset === 'USDT') {
                $value = $qty;
                $walletUsdt = $qty;
            } else {
                $price = $lastPriceByBase[$asset] ?? null;
                $value = $price !== null ? bcmul($qty, $price, self::SCALE) : null;
                if ($value === null) {
                    $valuePartial = true;
                }
            }
            $assets[] = ['asset' => $asset, 'qty' => $qty, 'value' => $value];
        }

        // account_value = wallet USDT + every priced non-USDT asset's value;
        // unpriced assets are skipped from the sum (value_partial flags it).
        $accountValue = null;
        if ($walletUsdt !== null) {
            $accountValue = $walletUsdt;
            foreach ($assets as $a) {
                if ($a['asset'] !== 'USDT' && $a['value'] !== null) {
                    $accountValue = bcadd($accountValue, $a['value'], self::SCALE);
                }
            }
        }

        // realized P/L, mode-scoped PER RUN (each run's own simulated/real switch)
        $realizedTotal = '0';
        $realizedToday = '0';
        $todayStart = date('Y-m-d 00:00:00');
        foreach ($runs as $r) {
            $simulated = (bool) $r->getSimulated();
            $id = (int) $r->getIdGridRun();
            foreach (TradeCycleQuery::create()->filterByIdGridRun($id)->filterBySimulated($simulated)->find() as $c) {
                $realizedTotal = bcadd($realizedTotal, (string) $c->getRealizedPnl(), self::SCALE);
            }
            foreach (TradeCycleQuery::create()->filterByIdGridRun($id)->filterBySimulated($simulated)
                ->filterByDateCreation(['min' => $todayStart])->find() as $c) {
                $realizedToday = bcadd($realizedToday, (string) $c->getRealizedPnl(), self::SCALE);
            }
        }

        $totalPl = $accountValue !== null ? bcsub($accountValue, $budgetFixed, self::SCALE) : null;

        // holdings_value: every non-USDT wallet asset priced at its matching
        // run's last_price — reuses the asset pricing already computed above
        // (partial ok: unpriced assets are simply skipped from the sum; null
        // only when NONE of the non-USDT assets could be priced).
        $holdingsValue = null;
        foreach ($assets as $a) {
            if ($a['asset'] === 'USDT' || $a['value'] === null) {
                continue;
            }
            $holdingsValue = bcadd($holdingsValue ?? '0', $a['value'], self::SCALE);
        }

        // open_sell_value / open_buy_value / free_budget: committed-capital row.
        // Each run's orders are mode-scoped by its own simulated/real switch
        // (mirrors kpis()'s per-run scoping, done here across all non-Done runs).
        $openSellValue = '0';
        $openBuyValue = '0';
        $investedTotal = '0';
        foreach ($runs as $r) {
            $simulated = (bool) $r->getSimulated();
            $id = (int) $r->getIdGridRun();

            foreach (BotOrderQuery::create()->filterByIdGridRun($id)->filterBySimulated($simulated)
                ->filterBySide('Sell')->filterByState(['SELL_OPEN', 'PartFilled'], \Criteria::IN)->find() as $o) {
                $remaining = bcsub((string) $o->getQty(), (string) ($o->getFilledQty() ?? '0'), self::SCALE);
                $openSellValue = bcadd($openSellValue, bcmul((string) $o->getPrice(), $remaining, self::SCALE), self::SCALE);
            }
            foreach (BotOrderQuery::create()->filterByIdGridRun($id)->filterBySimulated($simulated)
                ->filterBySide('Buy')->filterByState(['BUY_OPEN', 'PartFilled'], \Criteria::IN)->find() as $o) {
                $remaining = bcsub((string) $o->getQty(), (string) ($o->getFilledQty() ?? '0'), self::SCALE);
                $openBuyValue = bcadd($openBuyValue, bcmul((string) $o->getPrice(), $remaining, self::SCALE), self::SCALE);
            }

            $store = new OrderStore($id, (string) $r->getRunUid(), $r->getLedgerResetAt('Y-m-d H:i:s'), $simulated);
            $investedTotal = bcadd($investedTotal, $store->investedQuote(), self::SCALE);
        }
        // free_budget may legitimately go negative (over-committed) — the
        // renderer paints it red, it is not floored like the per-run KPI.
        $freeBudget = bcsub(bcsub($budget, $investedTotal, self::SCALE), $openBuyValue, self::SCALE);

        // wallet-level drawdown floor (DrawdownGuard) — shown on the
        // Account value tile; red when equity is under it
        $ddFloor = DrawdownGuard::floor(DrawdownGuard::equity()['source']);
        $ddUnder = $ddFloor !== null && $accountValue !== null
            && bccomp($accountValue, $ddFloor, self::SCALE) < 0;

        return [
            'mode' => $mode,
            'budget' => $budget,
            'budget_fixed' => $budgetFixed,
            'use_all_funds' => $useAllFunds,
            'budget_allocatable' => $allocatable,
            'pool_reserve_pct' => $reservePct,
            'slices_sum' => $slicesSum,
            'wallet_usdt' => $walletUsdt,
            'account_value' => $accountValue,
            'value_partial' => $valuePartial,
            'realized_total' => $realizedTotal,
            'realized_today' => $realizedToday,
            'total_pl' => $totalPl,
            'drawdown_floor' => $ddFloor,
            'drawdown_under' => $ddUnder,
            'assets' => $assets,
            'holdings_value' => $holdingsValue,
            'open_sell_value' => $openSellValue,
            'open_buy_value' => $openBuyValue,
            'free_budget' => $freeBudget,
        ];
    }

    /** Chart timeframes the dashboard offers (candle store intervals). */
    public const CHART_TFS = CandleStore::INTERVALS;

    /** Bars the chart endpoint returns per request. */
    private const CHART_BARS = 1500;

    /**
     * Everything the trading chart draws for one timeframe: the persisted
     * OHLCV candles for the run's symbol, the run's fills placed at their
     * fill time (date_modification — date_creation is placement), the
     * working ladder, the grid geometry, the trend arm's entry/stop/hwm
     * (engine_state, Trend runs only) and the lifecycle markers. Times are
     * epoch seconds so the client aligns bot activity bar-for-bar.
     *
     * @throws \InvalidArgumentException on an unknown timeframe
     */
    public function chartModel(string $tf): array
    {
        if (!in_array($tf, self::CHART_TFS, true)) {
            throw new \InvalidArgumentException("unknown chart timeframe '$tf'");
        }
        if (!$this->run) {
            return [];
        }
        $symbol = (string) $this->run->getSymbol();
        $candles = CandleStore::series($symbol, $tf, self::CHART_BARS);
        $newest = $candles ? $candles[count($candles) - 1]['time'] : null;
        $tfSec = CandleStore::TF_SECONDS[$tf];
        // the collector passes every 1–10 min; a series whose newest bar is
        // older than three bars (or 15 min for the long timeframes) is stale
        $stale = $newest === null || (time() - $newest) > max(3 * $tfSec, 900) + $tfSec;

        $fills = [];
        $rows = $this->orderQ()
            ->filterByState(['Filled', 'PartFilled'], \Criteria::IN)
            ->orderByIdBotOrder(\Criteria::DESC)
            ->limit(500)
            ->find()
            ->getArrayCopy();
        foreach (array_reverse($rows) as $o) {
            $at = $o->getDateModification('Y-m-d H:i:s') ?: $o->getDateCreation('Y-m-d H:i:s');
            $fills[] = [
                'time' => $at ? self::stampEpoch($at) : 0,
                'price' => (float) $o->getPrice(),
                'qty' => (float) ((string) $o->getFilledQty() !== '' && bccomp((string) $o->getFilledQty(), '0', 8) > 0 ? $o->getFilledQty() : $o->getQty()),
                'side' => (string) $o->getSide(),
                'level' => (int) $o->getLevelIdx(),
                'state' => (string) $o->getState(),
            ];
        }

        $open = [];
        foreach ($this->orderQ()->filterByState(['BUY_OPEN', 'SELL_OPEN'], \Criteria::IN)->find() as $o) {
            $open[] = ['price' => (float) $o->getPrice(), 'side' => (string) $o->getSide(), 'level' => (int) $o->getLevelIdx()];
        }

        $trend = null;
        if ((string) $this->run->getAlgo() === 'Trend') {
            $state = json_decode((string) ($this->run->getEngineState() ?? ''), true);
            $state = is_array($state) ? $state : [];
            $num = static fn ($v) => $v === null || $v === '' ? null : (float) $v;
            $trend = [
                'entry' => $num($state['entry'] ?? null),
                'stop' => $num($state['stop'] ?? null),
                'hwm' => $num($state['hwm'] ?? null),
                'qty' => $num($state['qty'] ?? null),
            ];
        }

        $events = [];
        foreach ($this->chartMarkers() as $m) {
            if (!isset(self::MARKER_LABEL[$m['kind']])) {
                continue;
            }
            $events[] = [
                'time' => self::stampEpoch($m['at']),
                'kind' => $m['kind'],
                'label' => self::MARKER_LABEL[$m['kind']],
                'message' => $m['message'],
            ];
        }

        return [
            'run' => (int) $this->run->getIdGridRun(),
            'symbol' => $symbol,
            'tf' => $tf,
            'tf_seconds' => $tfSec,
            'server_time' => time(),
            'stale' => $stale,
            'candles' => $candles,
            'fills' => $fills,
            'orders_open' => $open,
            'grid' => [
                'p_low' => (float) $this->run->getPLow(),
                'p_high' => (float) $this->run->getPHigh(),
                'levels' => array_map('floatval', $this->gridLevels()),
            ],
            'trend' => $trend,
            'events' => $events,
            'last_price' => $this->run->getLastPrice() !== null ? (float) $this->run->getLastPrice() : null,
        ];
    }

    /** Full view model consumed by DashboardRenderer. */
    public function viewModel(string $baseUrl): array
    {
        return [
            'baseUrl' => $baseUrl,
            'run' => $this->runHeader(),
            'kpis' => $this->kpis(),
            'cycles' => $this->latestCycles(),
            'events' => $this->latestEvents(),
            'daily' => $this->dailyPnl(),
            'wallet' => $this->sharedWallet(),
        ];
    }
}
