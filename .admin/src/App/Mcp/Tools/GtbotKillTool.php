<?php
namespace App\Mcp\Tools;

use ApiGoat\Mcp\ToolError;
use ApiGoat\Sessions\AuthySession;

class GtbotKillTool extends AbstractGtbotBase
{
    public function name(): string
    {
        return 'gtbot_kill';
    }

    public function description(): string
    {
        return 'EMERGENCY STOP for a grid run: sets the kill switch directly on the run row (works '
            . 'even if the daemon is wedged — it re-reads the flag every tick) and enqueues Kill, '
            . 'which cancels open buys and stops the loop. Inventory is HELD unless flatten:true is '
            . 'also passed explicitly. A call without confirm:true changes nothing — it echoes the '
            . 'pending action; re-call with confirm:true after explicit user approval. Never pass '
            . 'flatten:true unless the user explicitly asked to liquidate inventory.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'run' => ['type' => ['integer', 'string'], 'description' => 'id_grid_run or exact label; omit for the latest run'],
                'confirm' => ['type' => 'boolean', 'description' => 'must be true to execute; get the user\'s explicit approval first'],
                'flatten' => ['type' => 'boolean', 'description' => 'ALSO liquidate held inventory — only on explicit user request'],
                'reason' => ['type' => 'string', 'description' => 'REQUIRED with confirm: one-line justification (what misbehaved / which loss rule tripped, with numbers). Persisted to bot_event kind kill_requested and fanned out to Telegram — every kill must be auditable'],
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
        $flatten = !empty($args['flatten']);
        if ($flatten && !\App\Domains\Bot\ProfilePolicy::allowsRealizedLoss((string) ($run->getProfile() ?: 'Balanced'))) {
            throw new ToolError(sprintf(
                'run %d has profile NoLoss — flatten (realizing losses) is forbidden by policy; plain kill (cancel buys, HOLD inventory) is allowed',
                (int) $run->getIdGridRun()
            ));
        }
        if (empty($args['confirm'])) {
            return $this->ok([
                'pending' => true,
                'action' => $flatten
                    ? 'KILL + FLATTEN (cancel buys, stop loop, liquidate inventory)'
                    : 'KILL (cancel buys, stop loop, HOLD inventory)',
                'run' => ['id' => (int) $run->getIdGridRun(), 'label' => (string) $run->getLabel(), 'status' => (string) $run->getStatus()],
                'note' => 'Nothing tripped yet — show the user this pending action and re-call with confirm:true once they approve.',
            ]);
        }
        $reason = trim((string) ($args['reason'] ?? ''));
        if ($reason === '') {
            throw new ToolError('reason is required to kill: state what misbehaved or which loss rule tripped (it is persisted to bot_event and Telegram — every kill must be auditable)');
        }
        // The justification lands in the event feed BEFORE the switch flips,
        // so even a wedged daemon leaves an auditable "why" next to the kill.
        (new \App\Domains\Bot\EventLog(
            (int) $run->getIdGridRun(),
            false,
            \App\Domains\Bot\TelegramNotifier::fromEnv()
        ))->write('Alert', 'kill_requested', $reason);
        $run->setKillSwitch(true);
        $run->save();
        $cmdId = $this->enqueue($run, $flatten ? 'Flatten' : 'Kill', mb_substr('via gtbot_kill: ' . $reason, 0, 255));
        return $this->ok([
            'kill_switch' => true,
            'enqueued' => [$flatten ? 'Flatten' : 'Kill'],
            'command_id' => $cmdId,
            'reason' => $reason,
            'heartbeat' => $this->heartbeat($run),
        ]);
    }
}
