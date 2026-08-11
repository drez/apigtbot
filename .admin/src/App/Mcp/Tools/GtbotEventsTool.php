<?php
namespace App\Mcp\Tools;

use ApiGoat\Sessions\AuthySession;
use App\BotEventQuery;

class GtbotEventsTool extends AbstractGtbotBase
{
    public function name(): string
    {
        return 'gtbot_events';
    }

    public function description(): string
    {
        return 'Read-only recent bot_event rows for a grid run (newest first): boots, placements, '
            . 'fills, vetoes, alerts. Filterable by severity level.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'run' => ['type' => ['integer', 'string'], 'description' => 'id_grid_run or exact label; omit for the latest run'],
                'level' => ['type' => 'string', 'enum' => ['Info', 'Warn', 'Error', 'Alert'], 'description' => 'Only this severity'],
                'limit' => ['type' => 'integer', 'description' => 'Max rows (default 20, cap 100)'],
            ],
        ];
    }

    public function requiredRight(): ?array
    {
        return ['GridRun', 'r'];
    }

    protected function run(array $args, AuthySession $session): array
    {
        $run = $this->resolveRun($args);
        $q = BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->orderByIdBotEvent(\Criteria::DESC)
            ->limit(max(1, min(100, (int) ($args['limit'] ?? 20))));
        if (!empty($args['level'])) {
            $q->filterByLevel((string) $args['level']);
        }
        $events = [];
        foreach ($q->find() as $e) {
            $events[] = [
                'at' => $e->getDateCreation('Y-m-d H:i:s'),
                'level' => (string) $e->getLevel(),
                'kind' => (string) $e->getKind(),
                'message' => (string) $e->getMessage(),
                'payload' => $e->getPayload() ? json_decode((string) $e->getPayload(), true) : null,
            ];
        }
        return $this->ok([
            'run' => ['id' => (int) $run->getIdGridRun(), 'label' => (string) $run->getLabel()],
            'events' => $events,
        ]);
    }
}
