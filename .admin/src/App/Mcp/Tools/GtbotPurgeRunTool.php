<?php
namespace App\Mcp\Tools;

use ApiGoat\Mcp\ToolError;
use ApiGoat\Sessions\AuthySession;
use App\Domains\Bot\RunLifecycle;

/**
 * Permanently delete an ALREADY-ARCHIVED run and everything hanging off it.
 *
 * Two separate gates on purpose: `confirm` is the house two-phase pattern,
 * `confirm_data_loss` is the acknowledgement that the orders, cycles, events,
 * decisions and commands are gone forever. The preview names the exact row
 * counts so that acknowledgement is informed.
 */
class GtbotPurgeRunTool extends AbstractGtbotBase
{
    public function name(): string
    {
        return 'gtbot_purge_run';
    }

    public function description(): string
    {
        return 'PERMANENTLY delete an archived run and cascade away its orders, cycles, events, decisions and '
            . 'commands. Refuses anything that is not already Done (archive it with gtbot_retire_run first), '
            . 'anything still holding inventory, and anything whose daemon still looks alive (heartbeat, run lock, '
            . 'systemd unit). Seals the run\'s contribution into the shared paper-wallet baseline BEFORE deleting, '
            . 'so the wallet does not silently rewrite itself at the next daemon boot. Needs BOTH confirm:true and '
            . 'confirm_data_loss:true. This cannot be undone.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'run' => ['type' => ['integer', 'string'], 'description' => 'id_grid_run or exact label — REQUIRED, no fallback for a destructive action'],
                'reason' => ['type' => 'string', 'description' => 'REQUIRED with confirm — journaled to a surviving run\'s event feed'],
                'confirm' => ['type' => 'boolean', 'description' => 'must be true to execute'],
                'confirm_data_loss' => ['type' => 'boolean', 'description' => 'separate, deliberate acknowledgement that the child rows are gone forever'],
            ],
            'required' => ['run'],
        ];
    }

    public function requiredRight(): ?array
    {
        return ['GridRun', 'w'];
    }

    protected function run(array $args, AuthySession $session): array
    {
        if (!isset($args['run']) || $args['run'] === '' || $args['run'] === null) {
            throw new ToolError('run is required — gtbot_purge_run never guesses which run to delete');
        }
        $target = $this->resolveRun($args);

        if (empty($args['confirm'])) {
            // the dry pass reports the guard verdict and the row counts without
            // taking the lock or touching the wallet
            $preview = RunLifecycle::purge($target, 'preview', false);
            return $this->ok([
                'pending' => true,
                'action' => 'purge',
                'run' => (int) $target->getIdGridRun(),
                'label' => (string) $target->getLabel(),
                'status' => (string) $target->getStatus(),
                'deleted' => $preview['deleted'] ?? [],
                'blocked_by' => $preview['ok'] ? null : $preview['message'],
                'daemon' => RunLifecycle::daemonProof($target),
                'note' => 'Nothing was written — show the user this and re-call with confirm:true AND confirm_data_loss:true.',
            ]);
        }

        $reason = trim((string) ($args['reason'] ?? ''));
        if ($reason === '') {
            throw new ToolError('reason is required with confirm:true');
        }

        $res = RunLifecycle::purge($target, $reason, !empty($args['confirm_data_loss']));
        if (!$res['ok']) {
            throw new ToolError($res['message']);
        }
        return $this->ok($res);
    }
}
