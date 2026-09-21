<?php

namespace App\Domains\Bot;

use App\BotCommand;
use App\Config;
use App\ConfigQuery;
use App\GridRunQuery;

/**
 * The shared budget pool as the operator sees it on the dashboard.
 *
 * fixed()  the config seed gtbot_shared_budget_quote (default 1000). It
 *          seeds the paper wallet and anchors the drawdown floor and the
 *          NAV ledger — those deliberately stay on the fixed number.
 * cap()    what the run slices may sum to. Normally == fixed(); when
 *          gtbot_use_all_funds = 1 it follows the live mark-to-market
 *          account value (DrawdownGuard::equity — wallet USDT + priced
 *          holdings, whole USDT), so a growing wallet is put to work and a
 *          shrinking one stops new commitments. Falls back to fixed() while
 *          the wallet cannot be valued (no rows / no tick yet).
 * allocatable()
 *          cap MINUS the reserve — what the AUTOMATIC allocators may plan
 *          against. See reservePct() for why it exists.
 * set()    the dashboard "edit budget" action: validates, writes the config
 *          row and Reloads every active run — the daemon re-seeds the paper
 *          wallet from the new number at boot. Slices are NOT resized here:
 *          the refit routine / trend activator pick the new cap up on their
 *          next pass, which is what the UI warns about.
 */
final class BudgetPool
{
    public const CONFIG_BUDGET = 'gtbot_shared_budget_quote';
    public const CONFIG_USE_ALL = 'gtbot_use_all_funds';
    public const CONFIG_RESERVE = 'gtbot_pool_reserve_pct';
    public const KIND_CHANGED = 'budget_changed';

    /** % of the pool the automatic allocators leave alone when the config row is missing or junk. */
    public const DEFAULT_RESERVE_PCT = '5';
    /** More than half the pool held back is not headroom, it is a smaller pool. */
    public const MAX_RESERVE_PCT = '50';

    private const SCALE = 12;

    /** trailing decimal zeros off, whole numbers untouched ('1000' stays '1000') */
    public static function fmt(string $n): string
    {
        return str_contains($n, '.') ? rtrim(rtrim($n, '0'), '.') : $n;
    }

    public static function fixed(): string
    {
        return SimWallet::sharedBudget();
    }

    public static function useAllFunds(): bool
    {
        $v = ConfigQuery::create()->findOneByConfig(self::CONFIG_USE_ALL)?->getValue();
        return $v !== null && in_array(strtolower(trim((string) $v)), ['1', 'true', 'yes', 'on'], true);
    }

    /** the effective pool cap, whole USDT when it follows the wallet */
    public static function cap(): string
    {
        if (!self::useAllFunds()) {
            return self::fixed();
        }
        $eq = DrawdownGuard::equity();
        if (bccomp($eq['equity'], '0', self::SCALE) <= 0) {
            return self::fixed();
        }
        // Operator rule (2026-09-19): tokens in the pool shift the MIX a run can
        // draw on, they never raise the ceiling. The ceiling is the wallet that
        // was MEASURED (2026-09-21, round 2): gtbot_shared_budget_quote while
        // the fleet is on paper, gtbot_real_account_baseline once equity is the
        // exchange account — otherwise a 200 USDT canary would be capped at the
        // 1300 paper seed and the guard would happily let the slices sum to six
        // times what the account holds. So the branch is min(wallet, anchor).
        $anchor = bcadd(DrawdownGuard::baselineFor($eq['source']), '0', 0);
        $whole = bcadd($eq['equity'], '0', 0);
        return bccomp($whole, $anchor, self::SCALE) > 0 ? $anchor : $whole;
    }

    /**
     * The slice of the pool the automatic allocators may NOT plan against.
     *
     * WHY (2026-09-21). The machine sizes slices to fill the pool EXACTLY:
     * prod ran 2 trend arms × 350 (gtbot_trend_target_slice) + 2 grids × 300
     * (TrendActivator::gridFloor, 50 × 6 levels) = 1300 against a 1300 cap.
     * Zero headroom means any rounding, any raise, or — with
     * gtbot_use_all_funds on — a mark-to-market dip of one USDT puts
     * sum(slices) over cap(), and the daemon's per-tick check fail-closes
     * EVERY entry on the fleet at once (budget_overcommit). On paper that was
     * an annoyance; with real money it is an expensive way to fail.
     *
     * So the allocators plan against cap × (1 − reserve%) while BudgetGuard
     * keeps enforcing the FULL cap. That asymmetry is the whole point: the
     * reserve is slack the machine refuses to spend, NOT a second tripwire.
     * A fleet sitting between allocatable and cap is legal, quiet and simply
     * not re-raised — nothing is ever trimmed below a floor or committed
     * capital to reach the reserve (see Allocator::rebalance).
     *
     * Clamping is deliberately asymmetric. Above MAX_RESERVE_PCT the value is
     * clamped down (an operator asking for more headroom errs in the safe
     * direction), but a negative or non-numeric value falls back to the
     * DEFAULT rather than to 0: clamping junk to 0 would silently switch the
     * protection off, which is the one outcome this must never produce. 0
     * itself — explicit, and the only way — disables the reserve.
     */
    public static function reservePct(): string
    {
        $v = self::freshConfig(self::CONFIG_RESERVE);
        if ($v === null || !is_numeric($v)) {
            return self::DEFAULT_RESERVE_PCT;
        }
        $pct = bcadd($v, '0', 2);
        if (bccomp($pct, '0', 2) < 0) {
            return self::DEFAULT_RESERVE_PCT;
        }
        return bccomp($pct, self::MAX_RESERVE_PCT, 2) > 0 ? bcadd(self::MAX_RESERVE_PCT, '0', 2) : $pct;
    }

    /** What the automatic allocators may hand out, whole USDT. @see reservePct */
    public static function allocatable(): string
    {
        return self::allocatableOf(self::cap());
    }

    /**
     * allocatable() against a cap that is not (yet) the one in force — the
     * pre-write leg of a pool lowering plans against the INCOMING number.
     *
     * Floor division on purpose: the reserve must never be rounded away, so
     * the odd USDT of a 1235.x split stays on the reserve's side.
     */
    public static function allocatableOf(string $cap): string
    {
        $cap = bcadd($cap, '0', 0);
        $pct = self::reservePct();
        if (bccomp($pct, '0', 2) <= 0) {
            return $cap;
        }
        $keep = bcdiv(bcmul($cap, bcsub('100', $pct, 2), self::SCALE), '100', 0);
        return bccomp($keep, '0', 0) > 0 ? $keep : '0';
    }

    /** cap − allocatable: the headroom the reserve is holding back, for reporting. */
    public static function reserved(): string
    {
        $cap = bcadd(self::cap(), '0', 0);
        return bcsub($cap, self::allocatableOf($cap), 0);
    }

    /**
     * Read one config value past Propel's instance pool.
     *
     * select(): raw rows, never pooled objects — the same reason
     * BudgetGuard::check does it. The reserve is read on every 15-minute
     * activator and allocator pass and, through Allocator::headroom(), from
     * inside long-lived daemons; Propel 1 never re-hydrates a pooled object,
     * so a hydrated read would serve the value as of that process's boot and
     * an operator's edit would not land until a restart (the four-day prod
     * halt of 2026-09-05→09, documented at BudgetGuard::check).
     *
     * Two columns because a single-column select() returns the bare scalar.
     */
    private static function freshConfig(string $key): ?string
    {
        $row = ConfigQuery::create()
            ->filterByConfig($key)
            ->select(['Config', 'Value'])
            ->findOne();
        if (!is_array($row) || ($row['Value'] ?? null) === null) {
            return null;
        }
        $v = trim((string) $row['Value']);
        return $v === '' ? null : $v;
    }

    /**
     * @return array{ok: bool, message: string, budget?: string, previous?: string,
     *               reloaded?: int[], overcommitted?: bool}
     */
    public static function set(string $value): array
    {
        $value = trim($value);
        if (!preg_match('/^\d+(\.\d+)?$/', $value)) {
            return ['ok' => false, 'message' => 'budget must be a positive number of USDT'];
        }
        $budget = bcadd($value, '0', 0);
        if (bccomp($budget, '0', 0) <= 0) {
            return ['ok' => false, 'message' => 'budget must be a positive number of USDT'];
        }

        $previous = self::fixed();

        // Decreases BEFORE the config write, increases after.
        //
        // A decrease can never violate the invariant under either the old cap
        // or the new one, so it is always safe to land first — and landing it
        // first is what lets a LOWERED cap take effect without the fleet ever
        // being transiently overcommitted. Increases go last, each
        // BudgetGuard-checked against the cap now in force.
        $lowering = bccomp($budget, $previous, 0) < 0;
        $pre = $lowering ? Allocator::shrinkToward($budget, false) : ['applied' => [], 'shortfall' => '0', 'proposals' => []];

        $row = ConfigQuery::create()->findOneByConfig(self::CONFIG_BUDGET) ?? (new Config())->setConfig(self::CONFIG_BUDGET);
        $row->setValue($budget);
        $row->save();
        \App\ConfigPeer::clearInstancePool();

        $post = Allocator::rebalance('pool_change', true, null, false);

        $reloaded = [];
        foreach (GridRunQuery::create()->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)->find() as $run) {
            $id = (int) $run->getIdGridRun();
            $log = new EventLog($id, false);
            $log->write('Info', self::KIND_CHANGED, sprintf(
                'shared budget %s → %s USDT (dashboard); slices reallocated and Reload enqueued',
                self::fmt($previous),
                $budget
            ), ['previous' => $previous, 'budget' => $budget]);
            $cmd = new BotCommand();
            $cmd->setIdGridRun($id);
            $cmd->setCommand('Reload');
            $cmd->setCmdStatus('Pending');
            $cmd->setNote('dashboard budget change');
            $cmd->save();
            $reloaded[] = $id;
        }

        $over = BudgetGuard::check();
        // NOT `+`: PHP's union keeps the LEFT operand on a key clash, so a run
        // trimmed by the pre-write shrink and then raised by the post-write
        // rebalance reported only its first move — the operator read "#3 300 →
        // 235" for a run that ended at 300 (2026-09-21 review). The same trap
        // is documented at Allocator::drift. Merge per run: the FROM of the
        // first move, the TO of the last.
        $moved = $pre['applied'];
        foreach ($post['applied'] as $id => $ft) {
            $moved[$id] = isset($moved[$id])
                ? ['from' => $moved[$id]['from'], 'to' => $ft['to']]
                : $ft;
        }
        // a run trimmed and then put back where it started did not move
        $moved = array_filter($moved, static fn (array $ft): bool => bccomp($ft['from'], $ft['to'], 0) !== 0);
        $shortfall = bccomp($pre['shortfall'], $post['shortfall'], 0) >= 0 ? $pre['shortfall'] : $post['shortfall'];
        $proposals = array_merge($pre['proposals'], $post['proposals']);

        $message = sprintf('shared budget %s → %s USDT; %d run%s reloaded', self::fmt($previous), $budget, count($reloaded), count($reloaded) === 1 ? '' : 's');
        if ($moved !== []) {
            $parts = [];
            foreach ($moved as $id => $ft) {
                $parts[] = sprintf('#%d %s → %s', $id, $ft['from'], $ft['to']);
            }
            $message .= '; slices ' . implode(', ', $parts);
        }
        if (bccomp($shortfall, '0', 0) > 0) {
            $message .= sprintf(
                ' — %s USDT could NOT be freed: committed inventory holds the floors above the new cap. Nothing was sold; %d exit proposal(s) available.',
                $shortfall,
                count($proposals)
            );
        } elseif ($over !== null) {
            $message .= sprintf(' — slices sum to %s: new entries halt until the floors clear', self::fmt($over['sum']));
        }
        return [
            'ok' => true,
            'message' => $message,
            'moved' => $moved,
            'shortfall' => $shortfall,
            'proposals' => $proposals,
            'budget' => $budget,
            'previous' => $previous,
            'reloaded' => $reloaded,
            'overcommitted' => $over !== null,
        ];
    }
}
