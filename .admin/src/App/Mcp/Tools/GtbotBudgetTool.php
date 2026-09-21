<?php
namespace App\Mcp\Tools;

use ApiGoat\Mcp\ToolError;
use ApiGoat\Sessions\AuthySession;
use App\Domains\Bot\Allocator;
use App\Domains\Bot\AllocScore;
use App\Domains\Bot\BudgetGuard;
use App\Domains\Bot\BudgetPool;

/**
 * Read the allocation table, or change the shared pool.
 *
 * A pool change REALLOCATES immediately: decreases land before the config
 * write (so the fleet is never transiently overcommitted), increases after,
 * every write BudgetGuard-checked, Reload for each run that moved. If committed
 * inventory holds the floors above a lowered cap, the residual is reported as a
 * shortfall with exit proposals — and nothing is sold.
 */
class GtbotBudgetTool extends AbstractGtbotBase
{
    public function name(): string
    {
        return 'gtbot_budget';
    }

    public function description(): string
    {
        return 'Read the shared pool and the per-run allocation table, or set the pool with budget:"<USDT>". '
            . 'Setting it reallocates slices IMMEDIATELY, pro-rata over runs whose alloc_mode is Auto, never below '
            . "each run's floor (its viability floor or the quote its inventory is tied up in, whichever is larger). "
            . 'Fixed runs and an actively-managed trend arm are never moved. Lowering the pool below what inventory '
            . 'commits reports a shortfall plus per-lot exit proposals and sells NOTHING. Automatic allocation stops '
            . 'at "allocatable" (cap minus gtbot_pool_reserve_pct) while the overcommit guard still enforces the full '
            . 'cap, so that gap is deliberate headroom, not a shortfall. Without confirm:true it only previews the plan.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'budget' => ['type' => 'string', 'description' => 'new shared pool in whole USDT; omit for a read-only view'],
                'reason' => ['type' => 'string', 'description' => 'REQUIRED with confirm — journaled'],
                'confirm' => ['type' => 'boolean', 'description' => 'must be true to execute'],
            ],
        ];
    }

    public function requiredRight(): ?array
    {
        return ['GridRun', 'w'];
    }

    protected function run(array $args, AuthySession $session): array
    {
        $budget = isset($args['budget']) ? trim((string) $args['budget']) : '';

        if ($budget === '') {
            return $this->ok($this->table());
        }
        // the GUI route refuses this while the cap follows the wallet; the MCP
        // path must not be a way around that guard
        if (BudgetPool::useAllFunds()) {
            throw new ToolError(
                'the shared budget follows the wallet (gtbot_use_all_funds is on), so it cannot be set directly — '
                . 'turn that flag off first if you want a fixed pool'
            );
        }

        if (empty($args['confirm'])) {
            $plan = Allocator::rebalance('pool_change_preview', false);
            return $this->ok([
                'pending' => true,
                'action' => 'set_budget',
                'budget_now' => BudgetPool::cap(),
                'budget_next' => $budget,
                'plan' => $plan['applied'],
                'shortfall' => $plan['shortfall'],
                'proposals' => $plan['proposals'],
                'skipped' => $plan['skipped'],
                'note' => 'Nothing was written — show the user this and re-call with confirm:true once they approve.',
            ]);
        }

        $reason = trim((string) ($args['reason'] ?? ''));
        if ($reason === '') {
            throw new ToolError('reason is required with confirm:true');
        }

        $res = BudgetPool::set($budget);
        if (!$res['ok']) {
            throw new ToolError($res['message']);
        }
        return $this->ok($res + ['reason' => $reason, 'table' => $this->table()]);
    }

    private function table(): array
    {
        $days = AllocScore::windowDays();
        $rows = [];
        foreach (\App\GridRunQuery::create()
            ->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)
            ->orderByIdGridRun()->find() as $run) {
            $mode = Allocator::effectiveMode($run);
            $score = AllocScore::score($run, $days);
            $floor = Allocator::hardFloor($run);
            $slice = bcadd((string) $run->getBudgetQuote(), '0', 0);
            $rows[] = [
                'run' => (int) $run->getIdGridRun(),
                'label' => (string) $run->getLabel(),
                'symbol' => (string) $run->getSymbol(),
                'algo' => (string) ($run->getAlgo() ?: 'Grid'),
                'slice' => $slice,
                'floor' => $floor,
                'idle' => bccomp($slice, $floor, 0) > 0 ? bcsub($slice, $floor, 0) : '0',
                'alloc_mode' => (string) ($run->getAllocMode() ?: 'Auto'),
                'effective_mode' => $mode['why'],
                'per_1k_day' => $score['per_1k_day'],
                'cycles' => $score['cycles'],
                'eligible' => $score['eligible'],
                'why' => $score['why'],
            ];
        }
        $over = BudgetGuard::check();
        return [
            'cap' => BudgetPool::cap(),
            'fixed' => BudgetPool::fixed(),
            'use_all_funds' => BudgetPool::useAllFunds(),
            // cap is what BudgetGuard enforces; allocatable is what the
            // automatic allocators plan against, and `headroom` below is
            // measured against THAT — so a fleet filled to allocatable reads
            // 0 headroom while still sitting legally under the cap.
            'allocatable' => BudgetPool::allocatable(),
            'reserve_pct' => BudgetPool::reservePct(),
            'reserved' => BudgetPool::reserved(),
            'allocated' => Allocator::activeSum(),
            'headroom' => Allocator::headroom(),
            'overcommitted' => $over !== null,
            'window_days' => $days,
            'runs' => $rows,
        ];
    }
}
