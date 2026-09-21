<?php
namespace App\Mcp\Tools;

use ApiGoat\Sessions\AuthySession;

class GtbotStopTool extends AbstractGtbotBase
{
    public function name(): string
    {
        return 'gtbot_stop';
    }

    public function description(): string
    {
        return 'Enqueue Pause (heartbeat continues, no new orders; open orders keep working). For an emergency '
            . 'stop use gtbot_kill. Without confirm:true it only echoes the pending action.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'run' => ['type' => ['integer', 'string'], 'description' => 'id_grid_run or exact label; omit for the latest run'],
                'confirm' => ['type' => 'boolean', 'description' => 'must be true to execute; get the user\'s approval first'],
            ],
        ];
    }

    public function requiredRight(): ?array
    {
        return ['GridRun', 'w'];
    }

    protected function run(array $args, AuthySession $session): array
    {
        $run = $this->resolveRun($args);
        if (empty($args['confirm'])) {
            return $this->ok([
                'pending' => true,
                'action' => 'Pause (stop placing orders; open orders stay working)',
                'run' => ['id' => (int) $run->getIdGridRun(), 'label' => (string) $run->getLabel(), 'status' => (string) $run->getStatus()],
                'note' => 'Nothing enqueued yet — show the user this pending action and re-call with confirm:true once they approve.',
            ]);
        }
        $cmdId = $this->enqueue($run, 'Pause', 'via gtbot_stop');
        return $this->ok(['enqueued' => ['Pause'], 'command_id' => $cmdId]);
    }
}
