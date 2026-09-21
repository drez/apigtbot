<?php
namespace App\Mcp\Tools;

use ApiGoat\Sessions\AuthySession;

class GtbotStartTool extends AbstractGtbotBase
{
    public function name(): string
    {
        return 'gtbot_start';
    }

    public function description(): string
    {
        return 'Enqueue Start for a run (clears a Pause). Without confirm:true it only echoes the pending action. '
            . 'If the heartbeat is stale the process itself needs launching (watchdog cron / bin/gtbot) — the tool says so.';
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
        $hb = $this->heartbeat($run);
        if (empty($args['confirm'])) {
            return $this->ok([
                'pending' => true,
                'action' => 'Start (resume trading loop)',
                'run' => ['id' => (int) $run->getIdGridRun(), 'label' => (string) $run->getLabel(), 'status' => (string) $run->getStatus()],
                'note' => 'Nothing enqueued yet — show the user this pending action and re-call with confirm:true once they approve.',
            ]);
        }
        $cmdId = $this->enqueue($run, 'Resume', 'via gtbot_start');
        return $this->ok([
            'enqueued' => ['Resume'],
            'command_id' => $cmdId,
            'daemon_running' => !$hb['stale'],
            'hint' => $hb['stale']
                ? 'Heartbeat is stale — the daemon process is not running. Launch it: php bin/gtbot --run=' . (int) $run->getIdGridRun()
                : 'Daemon heartbeat is fresh; the command will be consumed on the next tick.',
        ]);
    }
}
