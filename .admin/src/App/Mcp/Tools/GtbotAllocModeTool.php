<?php
namespace App\Mcp\Tools;

use ApiGoat\Mcp\ToolError;
use ApiGoat\Sessions\AuthySession;
use App\Domains\Bot\Allocator;
use App\Domains\Bot\BudgetGuard;
use App\Domains\Bot\EventLog;

/**
 * Pin a run's slice (Fixed) or let the allocator manage it (Auto).
 *
 * `effective_mode` matters as much as the column: an actively-managed trend arm
 * is pinned whatever its alloc_mode says, because TrendActivator::reassert()
 * re-writes its slice every 15 minutes. Reporting the computed pin is what lets
 * an operator see WHY a slice is not moving without reading source.
 */
class GtbotAllocModeTool extends AbstractGtbotBase
{
    public function name(): string
    {
        return 'gtbot_alloc_mode';
    }

    public function description(): string
    {
        return 'Report or set a run\'s allocation mode. Auto: the run takes part in automatic pool reallocation '
            . '(a budget change, retire or purge spreads the delta pro-rata, never below its floor). Fixed: the '
            . 'operator pins the slice and the allocator never moves it. Omit run for the whole table; omit mode to '
            . 'report only. Note effective_mode: a trend arm the activator is actively managing is pinned whatever '
            . 'its column says. Without confirm:true a mode change only previews.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'run' => ['type' => ['integer', 'string'], 'description' => 'id_grid_run or exact label; omit for the whole table'],
                'mode' => ['type' => 'string', 'enum' => ['Auto', 'Fixed'], 'description' => 'omit to report only'],
                'reason' => ['type' => 'string', 'description' => 'REQUIRED with confirm — journaled'],
                'confirm' => ['type' => 'boolean', 'description' => 'must be true to execute a mode change'],
            ],
        ];
    }

    public function requiredRight(): ?array
    {
        return ['GridRun', 'w'];
    }

    protected function run(array $args, AuthySession $session): array
    {
        $mode = isset($args['mode']) ? (string) $args['mode'] : '';

        if ($mode === '') {
            return $this->ok(['runs' => $this->rows($args['run'] ?? null)]);
        }
        if (!in_array($mode, ['Auto', 'Fixed'], true)) {
            throw new ToolError('mode must be Auto or Fixed');
        }
        if (!isset($args['run']) || $args['run'] === '' || $args['run'] === null) {
            throw new ToolError('run is required to change a mode');
        }
        $target = $this->resolveRun($args);
        $before = (string) ($target->getAllocMode() ?: 'Auto');

        if (empty($args['confirm'])) {
            return $this->ok([
                'pending' => true,
                'action' => 'alloc_mode',
                'run' => (int) $target->getIdGridRun(),
                'from' => $before,
                'to' => $mode,
                'effective_mode' => Allocator::effectiveMode($target)['why'],
                'note' => 'Nothing was written — re-call with confirm:true once the user approves.',
            ]);
        }
        $reason = trim((string) ($args['reason'] ?? ''));
        if ($reason === '') {
            throw new ToolError('reason is required with confirm:true');
        }
        if ($before === $mode) {
            return $this->ok(['ok' => true, 'message' => sprintf('run %d is already %s', (int) $target->getIdGridRun(), $mode)]);
        }

        $target->setAllocMode($mode);
        $target->save();
        (new EventLog((int) $target->getIdGridRun(), false))->write(
            'Info',
            Allocator::KIND_PIN,
            sprintf('allocation mode %s → %s: %s', $before, $mode, $reason),
            ['from' => $before, 'to' => $mode, 'reason' => $reason]
        );

        return $this->ok([
            'ok' => true,
            'message' => sprintf('run %d allocation mode %s → %s', (int) $target->getIdGridRun(), $before, $mode),
            'effective_mode' => Allocator::effectiveMode($target)['why'],
        ]);
    }

    private function rows(mixed $only): array
    {
        $q = \App\GridRunQuery::create()->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)->orderByIdGridRun();
        $out = [];
        foreach ($q->find() as $run) {
            if ($only !== null && (int) $run->getIdGridRun() !== (int) $only && (string) $run->getLabel() !== (string) $only) {
                continue;
            }
            $slice = bcadd((string) $run->getBudgetQuote(), '0', 0);
            $floor = Allocator::hardFloor($run);
            $out[] = [
                'run' => (int) $run->getIdGridRun(),
                'label' => (string) $run->getLabel(),
                'slice' => $slice,
                'floor' => $floor,
                'idle' => bccomp($slice, $floor, 0) > 0 ? bcsub($slice, $floor, 0) : '0',
                'alloc_mode' => (string) ($run->getAllocMode() ?: 'Auto'),
                'effective_mode' => Allocator::effectiveMode($run)['why'],
            ];
        }
        return $out;
    }
}
