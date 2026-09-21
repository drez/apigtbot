<?php

namespace App\Domains\Bot;

use App\GridRunQuery;

/**
 * The shared-wallet budget invariant, ENFORCED (operator directive: never
 * overcommit). Every DryRun/Testnet/Live run draws on the ONE shared wallet
 * (SimWallet in paper mode, the single exchange account in real mode), so
 * the active runs' budget_quote slices must never sum above the shared
 * budget. One check, three enforcement points: the GridRun save path
 * (GridRunServiceWrapper::beforeSave — GUI, API and MCP writes all refuse),
 * gtbot_create_run, and the daemon's fail-closed entry gate (catches
 * anything that slipped past the write paths, e.g. direct SQL).
 */
final class BudgetGuard
{
    private const SCALE = 12;
    public const ACTIVE_STATUSES = ['DryRun', 'Testnet', 'Live'];

    /**
     * Sum the active runs' budget slices, with run $excludeRunId's slice
     * replaced by the candidate (a pending save) — pass no candidate for a
     * plain audit of what's in the DB. A candidate only counts when its
     * status is an active one (parking a run in Draft/Halted/Done frees its
     * slice).
     *
     * @return array{sum: string, budget: string, excess: string}|null null when within budget
     */
    public static function check(?int $excludeRunId = null, ?string $candidateBudget = null, ?string $candidateStatus = null): ?array
    {
        $sum = '0';
        // select(): raw rows, never pooled objects. Propel does not re-hydrate
        // an instance already in its pool, so a hydrated walk inside the
        // long-lived daemon would sum the slices as of boot and miss every
        // reallocation until restart (prod 2026-09-05→09: both grids halted
        // four days after the slices fit again).
        $rows = GridRunQuery::create()
            ->filterByStatus(self::ACTIVE_STATUSES, \Criteria::IN)
            ->select(['IdGridRun', 'BudgetQuote'])
            ->find();
        foreach ($rows as $r) {
            if ($excludeRunId !== null && (int) $r['IdGridRun'] === $excludeRunId) {
                continue;
            }
            $sum = bcadd($sum, (string) $r['BudgetQuote'], self::SCALE);
        }
        if ($candidateBudget !== null
            && ($candidateStatus === null || in_array($candidateStatus, self::ACTIVE_STATUSES, true))) {
            $sum = bcadd($sum, $candidateBudget, self::SCALE);
        }
        // cap(), never allocatable(): the guard is the hard ceiling, and
        // gtbot_pool_reserve_pct deliberately does NOT move it (2026-09-21).
        // The reserve is slack the automatic allocators refuse to plan
        // against so that rounding and mark-to-market drift cannot push them
        // into this refusal; making it a second, tighter tripwire would
        // recreate the very fail-closed-fleet failure it exists to prevent.
        $budget = BudgetPool::cap();
        if (bccomp($sum, $budget, self::SCALE) <= 0) {
            return null;
        }
        return ['sum' => $sum, 'budget' => $budget, 'excess' => bcsub($sum, $budget, self::SCALE)];
    }

    /** The one refusal message every enforcement point shows. */
    public static function message(array $over): string
    {
        return sprintf(
            'Budget overcommit refused: active run slices would sum to %s but the shared budget is %s (excess %s). Lower another run\'s budget first, or raise gtbot_shared_budget_quote.',
            BudgetPool::fmt($over['sum']),
            BudgetPool::fmt($over['budget']),
            BudgetPool::fmt($over['excess'])
        );
    }
}
