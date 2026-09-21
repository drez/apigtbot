<?php

namespace App\Domains\Bot;

use App\GridRun;
use App\GridRunQuery;
use App\TradeCycleQuery;

/**
 * Create / retire / purge — the lifecycle this engine needs to be usable for
 * testing strategies rather than just running four permanent ones.
 *
 * Retire is the everyday act and it is REVERSIBLE in the sense that nothing is
 * destroyed: the run stops entering, hands its slice back to the pool and, if
 * it still holds coins, sits in Retiring managing its exits until it is flat.
 * Purge is the separate, twice-confirmed act that actually deletes rows, and
 * it refuses anything that is not already archived.
 *
 *   Draft ─retire─▶ Done                       (nothing to unwind)
 *   active ─retire─▶ Retiring ─finalize─▶ Done (holds inventory)
 *   active ─retire─▶ Done                      (already flat — finalize inline)
 *   Done   ─purge──▶ (row deleted)
 *
 * WHY A NEW STATUS. `Done` is a read-side exclusion filter in seven places —
 * DrawdownGuard::equity(), ModeSwitch, three DashboardData queries,
 * AbstractGtbotBase::resolveRun(), GtbotRoutineBriefTool. A Done run that still
 * holds coins is therefore invisible to PRICE DISCOVERY: its base asset lands
 * in equity()'s $unpriced bucket and counts as ZERO, so the drawdown floor can
 * trip on a loss that never happened. The invariant that fixes this is
 * `Done ⇒ flat`, and delivering it needs a state where the run is winding down
 * but still watched. Halted cannot be that state (FundsHold::release promotes
 * any Halted run back to Live); Done cannot be it either. Hence Retiring.
 *
 * Because Retiring is NOT Done, all seven read-side filters keep the retiring
 * run visible and priced with no change at all; because it is not in
 * BudgetGuard::ACTIVE_STATUSES, its slice leaves the pool with no change
 * either. Only four call sites had to learn the new status.
 *
 * The standing policy this is all built around: NEVER sell at a loss unless
 * there are no funds. `minimize_loss` (the default) cannot realize a loss by
 * construction. `sell_now` can, and only with a profile that permits it, the
 * run's own sell_at_loss switch, and an explicit acknowledgement.
 */
final class RunLifecycle
{
    public const KIND_RETIRE = 'run_retired';
    public const KIND_FINALIZE = 'run_finalized';
    public const KIND_PURGE = 'run_purged';

    /** never realizes a loss: carry exits at cost, wait it out */
    public const EXIT_MINIMIZE = 'minimize_loss';
    /** liquidate at the next mark; needs an acknowledgement when underwater */
    public const EXIT_SELL_NOW = 'sell_now';

    public const EXITS = [self::EXIT_MINIMIZE, self::EXIT_SELL_NOW];

    /** heartbeat silence that counts as evidence — 3x the watchdog's 60s */
    public const STALE_AFTER = 180;

    private const SCALE = 8;

    /**
     * Re-entrancy flags. The delete guard in GridRunServiceWrapper refuses
     * every delete that is not ours, and the beforeSave guard refuses every
     * write of Done/Retiring that is not ours. These are how this class says
     * "it's me".
     */
    private static bool $inPurge = false;
    private static bool $inTransition = false;

    public static function inPurge(): bool
    {
        return self::$inPurge;
    }

    public static function inTransition(): bool
    {
        return self::$inTransition;
    }

    // ── reads ───────────────────────────────────────────────────────────

    public static function store(GridRun $run): OrderStore
    {
        return new OrderStore(
            (int) $run->getIdGridRun(),
            (string) $run->getRunUid(),
            $run->getLedgerResetAt('Y-m-d H:i:s'),
            (bool) $run->getSimulated()
        );
    }

    /** No tracked inventory and no working order. */
    public static function isFlat(GridRun $run): bool
    {
        $store = self::store($run);
        $held = bcadd($store->trackedInventory(), $store->legacyRemainingQty(), self::SCALE);
        if (bccomp($held, '0', self::SCALE) > 0) {
            return false;
        }
        return $store->openOrderObjects() === [];
    }

    /**
     * Read-only pre-flight: everything a confirm dialog should show before
     * either transition. Never writes.
     */
    public static function inspect(GridRun $run): array
    {
        $store = self::store($run);
        $status = (string) $run->getStatus();
        $held = bcadd($store->trackedInventory(), $store->legacyRemainingQty(), self::SCALE);
        $vwap = LossGuard::positionVwap($store);
        $fee = (string) ($run->getFeePct() ?: '0');
        $price = $run->getLastPrice() !== null ? (string) $run->getLastPrice() : null;
        $profile = (string) ($run->getProfile() ?: 'Balanced');
        $open = $store->openOrderObjects();
        $buys = 0;
        $sells = 0;
        foreach ($open as $o) {
            if ((string) $o->getSide() === 'Buy') {
                $buys++;
            } else {
                $sells++;
            }
        }
        $flat = bccomp($held, '0', self::SCALE) <= 0 && $open === [];

        return [
            'run' => (int) $run->getIdGridRun(),
            'label' => (string) $run->getLabel(),
            'symbol' => (string) $run->getSymbol(),
            'status' => $status,
            'algo' => (string) ($run->getAlgo() ?: 'Grid'),
            'profile' => $profile,
            'slice' => bcadd((string) $run->getBudgetQuote(), '0', 0),
            'frees_slice' => in_array($status, BudgetGuard::ACTIVE_STATUSES, true),
            'inventory' => $held,
            'invested_quote' => $store->investedQuote(),
            'position_vwap' => $vwap,
            'breakeven' => $vwap !== null ? LossGuard::breakeven($vwap, $fee) : null,
            'mark' => $price,
            'open_buys' => $buys,
            'open_sells' => $sells,
            'flat' => $flat,
            'sell_at_loss' => (bool) $run->getSellAtLoss(),
            'profile_allows_loss' => ProfilePolicy::allowsRealizedLoss($profile),
            'sell_now_would_realize_loss' => LossGuard::wouldRealizeLoss($vwap, $fee, $price),
            'realized_to_date' => self::realizedToDate($run),
            'lands_in' => $flat ? 'Done' : 'Retiring',
        ];
    }

    /** @return array{stopped: bool, reasons: string[], heartbeat_age: ?int, lock_held: bool, unit: string} */
    public static function daemonProof(GridRun $run, ?callable $systemctl = null): array
    {
        $runId = (int) $run->getIdGridRun();
        $reasons = [];

        $last = $run->getLastTickAt('Y-m-d H:i:s');
        $age = $last !== null ? max(0, time() - strtotime($last)) : null;
        if ($age !== null && $age < self::STALE_AFTER) {
            $reasons[] = sprintf('heartbeat is %ds old (needs %ds of silence)', $age, self::STALE_AFTER);
        }

        // IS_USED_LOCK, never GET_LOCK: GET_LOCK would make US the holder, and
        // it is re-entrant per session so a naive probe reports success even
        // when the daemon is alive on another connection.
        $lockHeld = false;
        try {
            $con = \Propel::getConnection();
            $stmt = $con->prepare("SELECT IS_USED_LOCK(?)");
            $stmt->execute(['gtbot-run-' . $runId]);
            $holder = $stmt->fetchColumn();
            $lockHeld = $holder !== null && $holder !== false;
            if ($lockHeld) {
                $reasons[] = sprintf('a daemon still holds the gtbot-run-%d lock (connection %s)', $runId, (string) $holder);
            }
        } catch (\Throwable $e) {
            $reasons[] = 'could not probe the run lock: ' . $e->getMessage();
        }

        $unit = self::unitState($runId, $systemctl);
        // the watchdog treats '' as benign; for a purge it means "systemd is
        // not managing this host", which is a different judgement
        if (in_array($unit, ['active', 'activating', 'reloading'], true)) {
            $reasons[] = sprintf('systemd unit gtbot@%d is %s — it will respawn (Restart=always)', $runId, $unit);
        }

        return [
            'stopped' => $reasons === [],
            'reasons' => $reasons,
            'heartbeat_age' => $age,
            'lock_held' => $lockHeld,
            'unit' => $unit,
        ];
    }

    // ── transitions ─────────────────────────────────────────────────────

    /**
     * Stop a run for good and hand its slice back.
     *
     * Ordering is chosen so every refusal leaves the row untouched and every
     * partial failure is resumable: all validation and the loss pre-flight run
     * BEFORE the first write.
     *
     * @param string $exit    self::EXIT_MINIMIZE | self::EXIT_SELL_NOW
     * @param string $reason  mandatory, journaled and Telegrammed
     * @param bool   $ackLoss operator's explicit loss acknowledgement (sell_now only)
     * @return array{ok: bool, message: string, status?: string, freed?: string,
     *               inventory?: string, flat?: bool, realloc?: array}
     */
    public static function retire(GridRun $run, string $exit = self::EXIT_MINIMIZE, string $reason = '', bool $ackLoss = false): array
    {
        $runId = (int) $run->getIdGridRun();
        $status = (string) $run->getStatus();

        if (!in_array($exit, self::EXITS, true)) {
            return ['ok' => false, 'message' => sprintf('unknown exit strategy "%s" — use %s', $exit, implode(' or ', self::EXITS))];
        }
        if (trim($reason) === '') {
            return ['ok' => false, 'message' => 'a reason is required — it is journaled to the event feed and Telegram'];
        }
        if ($status === 'Done') {
            return ['ok' => false, 'message' => sprintf('run %d is already archived', $runId)];
        }
        if ($status === 'Retiring') {
            return ['ok' => false, 'message' => sprintf(
                'run %d is already retiring — it archives itself once flat; to force the exit, re-call with exit=%s',
                $runId,
                self::EXIT_SELL_NOW
            )];
        }

        $store = self::store($run);
        $profile = (string) ($run->getProfile() ?: 'Balanced');
        $sellAtLossBefore = (bool) $run->getSellAtLoss();

        // ── sell_now pre-flight, before any write ──
        if ($exit === self::EXIT_SELL_NOW) {
            if (!ProfilePolicy::allowsRealizedLoss($profile)) {
                return ['ok' => false, 'message' => sprintf(
                    'run %d has profile NoLoss — realizing losses is forbidden by policy and ack_loss cannot override it; retire with exit=%s instead (it holds the position and exits at cost)',
                    $runId,
                    self::EXIT_MINIMIZE
                )];
            }
            $vwap = LossGuard::positionVwap($store);
            $fee = (string) ($run->getFeePct() ?: '0');
            $price = $run->getLastPrice() !== null ? (string) $run->getLastPrice() : null;
            if (LossGuard::wouldRealizeLoss($vwap, $fee, $price) && !$ackLoss) {
                return ['ok' => false, 'message' => sprintf(
                    "run %d: selling now at %s would realize a loss (cost basis %s, breakeven %s) and this run's 'Sell at loss' is %s — refused. "
                    . 'Re-call with ack_loss:true to accept the loss, or retire with exit=%s to hold the position and exit at cost.',
                    $runId,
                    (string) $price,
                    (string) $vwap,
                    $vwap !== null ? LossGuard::breakeven($vwap, $fee) : '?',
                    $sellAtLossBefore ? 'ON' : 'OFF',
                    self::EXIT_MINIMIZE
                )];
            }
        }

        // ── Draft: nothing to unwind ──
        if ($status === 'Draft') {
            return self::withTransition(function () use ($run, $runId, $reason): array {
                $run->setStatus('Done');
                $run->setKillSwitch(true);
                $run->save();
                self::log($run)->write('Info', self::KIND_RETIRE, sprintf('draft run %d archived — %s', $runId, $reason), [
                    'status_before' => 'Draft',
                    'exit_strategy' => null,
                    'reason' => $reason,
                ]);
                return ['ok' => true, 'message' => sprintf('run %d archived (was a Draft — no slice, no inventory)', $runId), 'status' => 'Done', 'freed' => '0', 'flat' => true];
            });
        }

        $slice = bcadd((string) $run->getBudgetQuote(), '0', 0);
        $freed = in_array($status, BudgetGuard::ACTIVE_STATUSES, true) ? $slice : '0';
        $snapshot = self::inspect($run);

        return self::withTransition(function () use ($run, $runId, $status, $exit, $reason, $ackLoss, $slice, $freed, $snapshot, $sellAtLossBefore): array {
            // Stop entries: the switch BEFORE the command, so even a wedged
            // daemon stops entering on its next reload().
            $run->setKillSwitch(true);
            $run->setDeployPct(0);
            $run->save();
            Allocator::enqueue($runId, 'CancelBuys', 'retire: stop entries');

            if ($exit === self::EXIT_SELL_NOW) {
                if ($ackLoss && !$run->getSellAtLoss()) {
                    // the run will never trade again, so this is permanent by
                    // design — journaled via sell_at_loss_before
                    $run->setSellAtLoss(true);
                    $run->save();
                }
                Allocator::enqueue($runId, 'Flatten', 'retire: sell_now');
            }

            // Status last among the row writes, so the commands above are
            // consumed by the daemon that is still booted.
            $run->setStatus('Retiring');
            $run->save();
            Allocator::enqueue($runId, 'Reload', 'retire: re-boot into exit-only mode');

            self::log($run)->write('Alert', self::KIND_RETIRE, sprintf(
                'run %d retiring (%s) — %s',
                $runId,
                $exit,
                $reason
            ), $snapshot + [
                'status_before' => $status,
                'exit_strategy' => $exit,
                'ack_loss' => $ackLoss,
                'sell_at_loss_before' => $sellAtLossBefore,
                'reason' => $reason,
                'slice_freed' => $freed,
            ]);

            $realloc = self::returnSlice($runId, $freed);

            // a flat run needs no wind-down at all
            $done = false;
            if (self::isFlat($run)) {
                $fin = self::finalize($run);
                $done = $fin['ok'];
            }

            Allocator::audit(self::log($run), self::KIND_RETIRE);

            $msg = $done
                ? sprintf('run %d archived — flat, so it went straight to Done; %s USDT returned to the pool', $runId, $freed)
                : sprintf(
                    'run %d is retiring — %s USDT returned to the pool; it holds %s %s and archives itself once the exits clear',
                    $runId,
                    $freed,
                    $snapshot['inventory'],
                    SimWallet::assetsFor((string) $run->getSymbol())[0]
                );
            if ($realloc !== [] && ($realloc['message'] ?? '') !== '') {
                $msg .= '; ' . $realloc['message'];
            }

            return [
                'ok' => true,
                'message' => $msg,
                'status' => (string) $run->getStatus(),
                'freed' => $freed,
                'inventory' => $snapshot['inventory'],
                'flat' => $done,
                'realloc' => $realloc,
            ];
        });
    }

    /**
     * Retiring → Done. Idempotent and safe to call from anywhere (the daemon,
     * the watchdog sweep, a re-invoked tool). Refuses while not flat — that
     * refusal IS the `Done ⇒ flat` invariant.
     *
     * @return array{ok: bool, message: string, flat?: bool, inventory?: string, open_orders?: int}
     */
    public static function finalize(GridRun $run): array
    {
        $runId = (int) $run->getIdGridRun();
        $status = (string) $run->getStatus();
        if ($status === 'Done') {
            return ['ok' => true, 'message' => sprintf('run %d is already archived', $runId), 'flat' => true];
        }
        if ($status !== 'Retiring') {
            return ['ok' => false, 'message' => sprintf('run %d is %s — only a Retiring run can be finalized', $runId, $status)];
        }

        $store = self::store($run);
        $held = bcadd($store->trackedInventory(), $store->legacyRemainingQty(), self::SCALE);
        $open = count($store->openOrderObjects());
        if (bccomp($held, '0', self::SCALE) > 0 || $open > 0) {
            return [
                'ok' => false,
                'message' => sprintf(
                    'run %d still holds %s %s with %d working order(s) — it archives itself when the exits fill, or re-retire with exit=%s + ack_loss to force it',
                    $runId,
                    $held,
                    SimWallet::assetsFor((string) $run->getSymbol())[0],
                    $open,
                    self::EXIT_SELL_NOW
                ),
                'flat' => false,
                'inventory' => $held,
                'open_orders' => $open,
            ];
        }

        return self::withTransition(function () use ($run, $runId): array {
            $run->setKillSwitch(true);
            $run->setDeployPct(0);
            $run->setStatus('Done');
            $run->save();
            self::log($run)->write('Info', self::KIND_FINALIZE, sprintf(
                'run %d archived — flat, realized %s to date',
                $runId,
                self::realizedToDate($run)
            ), ['realized' => self::realizedToDate($run)]);
            return ['ok' => true, 'message' => sprintf('run %d archived', $runId), 'flat' => true];
        });
    }

    /**
     * Shared guard for purge() AND both delete hooks.
     *
     * Returns ok ONLY inside purge(): a raw delete — the GUI trash icon,
     * crm_delete — must never remove a grid_run, because the paper wallet has
     * to be sealed first and neither hook has anywhere to hang that.
     *
     * @return array{ok: bool, message: string}
     */
    public static function mayPurge(GridRun $run, ?callable $systemctl = null): array
    {
        $runId = (int) $run->getIdGridRun();
        if (!self::$inPurge) {
            return ['ok' => false, 'message' => sprintf(
                'run %d cannot be deleted directly. Grid runs are removed with gtbot_purge_run, which seals the shared paper-wallet ledger first — a raw delete silently rewrites every balance at the next daemon boot. Archive it with gtbot_retire_run, then purge.',
                $runId
            )];
        }
        $status = (string) $run->getStatus();
        if ($status !== 'Done') {
            return ['ok' => false, 'message' => sprintf('run %d is %s — archive it with gtbot_retire_run first; purge only removes an already-archived run', $runId, $status)];
        }
        if (!self::isFlat($run)) {
            return ['ok' => false, 'message' => sprintf('run %d is archived but still holds inventory or a working order — refusing to purge', $runId)];
        }
        $proof = self::daemonProof($run, $systemctl);
        if (!$proof['stopped']) {
            return ['ok' => false, 'message' => sprintf('run %d still looks alive: %s', $runId, implode('; ', $proof['reasons']))];
        }
        return ['ok' => true, 'message' => 'ok'];
    }

    /**
     * Delete an archived run and everything hanging off it.
     *
     * The shared paper wallet is DERIVED from a replay of every simulated fill,
     * so the cascade that removes this run's bot_order rows would hand the
     * wallet back whatever the run had lost. SimWallet::sealRun() folds its net
     * contribution into the opening baseline FIRST — and if that throws, we
     * abort rather than delete.
     *
     * @return array{ok: bool, message: string, deleted?: array<string,int>, sealed?: array}
     */
    public static function purge(GridRun $run, string $reason, bool $confirmDataLoss = false, ?callable $systemctl = null): array
    {
        $runId = (int) $run->getIdGridRun();
        $label = (string) $run->getLabel();

        if (trim($reason) === '') {
            return ['ok' => false, 'message' => 'a reason is required'];
        }

        self::$inPurge = true;
        try {
            $guard = self::mayPurge($run, $systemctl);
            if (!$guard['ok']) {
                return $guard;
            }

            $counts = self::childCounts($runId);
            if (!$confirmDataLoss) {
                return ['ok' => false, 'message' => sprintf(
                    'purging run %d (%s) deletes %s — permanently. Re-call with confirm_data_loss:true to proceed.',
                    $runId,
                    $label,
                    self::describeCounts($counts)
                ), 'deleted' => $counts];
            }

            // Hold the run's own lock across seal+delete: it closes the race
            // where the watchdog spawns a daemon in the second between the
            // probe and the DELETE — the newborn fails its GET_LOCK and exits.
            $con = \Propel::getConnection();
            $lock = $con->prepare('SELECT GET_LOCK(?, 0)');
            $lock->execute(['gtbot-run-' . $runId]);
            if ((int) $lock->fetchColumn() !== 1) {
                return ['ok' => false, 'message' => sprintf('could not take the gtbot-run-%d lock — a daemon is alive; refusing to purge', $runId)];
            }

            try {
                $sealed = SimWallet::sealRun($run);
                $run->delete();
            } finally {
                $con->prepare('SELECT RELEASE_LOCK(?)')->execute(['gtbot-run-' . $runId]);
            }

            self::stopUnit($runId, $systemctl);
            self::archiveLog($runId);
            self::logDetached($runId, $label, $reason, $counts, $sealed);

            return [
                'ok' => true,
                'message' => sprintf('run %d (%s) purged — %s removed; wallet delta sealed into the baseline first', $runId, $label, self::describeCounts($counts)),
                'deleted' => $counts,
                'sealed' => $sealed,
            ];
        } finally {
            self::$inPurge = false;
        }
    }

    // ── helpers ─────────────────────────────────────────────────────────

    private static function withTransition(callable $fn): array
    {
        self::$inTransition = true;
        try {
            return $fn();
        } finally {
            self::$inTransition = false;
        }
    }

    private static function log(GridRun $run): EventLog
    {
        return new EventLog((int) $run->getIdGridRun(), false, TelegramNotifier::fromEnv(), [self::KIND_RETIRE, self::KIND_FINALIZE]);
    }

    /**
     * Hand the freed slice to the reallocator. Guarded by class_exists so this
     * subsystem works before the allocator's rebalance leg lands; until then
     * the slice simply sits as idle headroom, which is journaled.
     */
    private static function returnSlice(int $runId, string $freed): array
    {
        if (bccomp($freed, '0', 0) <= 0) {
            return [];
        }
        if (!method_exists(Allocator::class, 'onSliceFreed')) {
            return ['message' => sprintf('%s USDT is idle headroom (no reallocator installed yet)', $freed)];
        }
        try {
            return Allocator::onSliceFreed($runId, $freed, self::KIND_RETIRE);
        } catch (\Throwable $e) {
            return ['message' => 'reallocation failed: ' . $e->getMessage()];
        }
    }

    private static function realizedToDate(GridRun $run): string
    {
        $sum = '0';
        $rows = TradeCycleQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterBySimulated((bool) $run->getSimulated())
            ->select(['RealizedPnl'])
            ->find();
        foreach ($rows as $r) {
            $v = is_array($r) ? ($r['RealizedPnl'] ?? '0') : $r;
            $sum = bcadd($sum, (string) $v, self::SCALE);
        }
        return $sum;
    }

    /** @return array<string, int> */
    private static function childCounts(int $runId): array
    {
        return [
            'bot_order' => \App\BotOrderQuery::create()->filterByIdGridRun($runId)->count(),
            'trade_cycle' => TradeCycleQuery::create()->filterByIdGridRun($runId)->count(),
            'bot_event' => \App\BotEventQuery::create()->filterByIdGridRun($runId)->count(),
            'bot_command' => \App\BotCommandQuery::create()->filterByIdGridRun($runId)->count(),
            'bot_decision' => \App\BotDecisionQuery::create()->filterByIdGridRun($runId)->count(),
        ];
    }

    private static function describeCounts(array $counts): string
    {
        $parts = [];
        foreach ($counts as $table => $n) {
            $parts[] = "$n $table";
        }
        return implode(', ', $parts);
    }

    private static function unitState(int $runId, ?callable $systemctl): string
    {
        $call = $systemctl ?? static function (string $args): string {
            // sudo -n first: the web/MCP process runs as the app owner, and a
            // plain systemctl there fails with "Interactive authentication
            // required" rather than reporting the unit's real state.
            $out = @shell_exec('sudo -n systemctl ' . $args . ' 2>/dev/null');
            if ($out === null || trim((string) $out) === '') {
                $out = @shell_exec('systemctl ' . $args . ' 2>/dev/null');
            }
            return trim((string) $out);
        };
        try {
            return $call(sprintf('is-active gtbot@%d', $runId));
        } catch (\Throwable $e) {
            return '';
        }
    }

    private static function stopUnit(int $runId, ?callable $systemctl): void
    {
        $call = $systemctl ?? static function (string $args): string {
            $out = @shell_exec('sudo -n systemctl ' . $args . ' 2>/dev/null');
            return trim((string) $out);
        };
        try {
            $call(sprintf('disable --now gtbot@%d', $runId));
        } catch (\Throwable $e) {
            error_log(sprintf('gtbot: could not disable gtbot@%d: %s', $runId, $e->getMessage()));
        }
    }

    /** Rename, not delete — the post-mortem value outlives the run. */
    private static function archiveLog(int $runId): void
    {
        $path = _BASE_DIR . 'tmp/logs/gtbot-run' . $runId . '.log';
        if (is_file($path)) {
            @rename($path, $path . '.purged-' . date('YmdHis'));
        }
    }

    /**
     * The purged run's own bot_event rows are gone with it, so the receipt
     * goes to a surviving run, or to error_log when there is none.
     */
    private static function logDetached(int $runId, string $label, string $reason, array $counts, array $sealed): void
    {
        $msg = sprintf(
            'run %d (%s) purged — %s; wallet delta sealed: %s. %s',
            $runId,
            $label,
            self::describeCounts($counts),
            json_encode($sealed['delta'] ?? [], JSON_UNESCAPED_SLASHES),
            $reason
        );
        $host = GridRunQuery::create()->filterByStatus('Done', \Criteria::NOT_EQUAL)->orderByIdGridRun()->findOne();
        if ($host === null) {
            error_log('gtbot: ' . $msg);
            return;
        }
        (new EventLog((int) $host->getIdGridRun(), false))->write('Alert', self::KIND_PURGE, $msg, [
            'purged_run' => $runId,
            'label' => $label,
            'deleted' => $counts,
            'sealed' => $sealed,
            'reason' => $reason,
        ]);
    }
}
