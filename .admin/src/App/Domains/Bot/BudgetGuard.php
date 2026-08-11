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
        foreach (GridRunQuery::create()->filterByStatus(self::ACTIVE_STATUSES, \Criteria::IN)->find() as $r) {
            if ($excludeRunId !== null && (int) $r->getIdGridRun() === $excludeRunId) {
                continue;
            }
            $sum = bcadd($sum, (string) $r->getBudgetQuote(), self::SCALE);
        }
        if ($candidateBudget !== null
            && ($candidateStatus === null || in_array($candidateStatus, self::ACTIVE_STATUSES, true))) {
            $sum = bcadd($sum, $candidateBudget, self::SCALE);
        }
        $budget = SimWallet::sharedBudget();
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
            rtrim(rtrim($over['sum'], '0'), '.'),
            rtrim(rtrim($over['budget'], '0'), '.'),
            rtrim(rtrim($over['excess'], '0'), '.')
        );
    }
}
