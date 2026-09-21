<?php
namespace App\Mcp\Tools;

use ApiGoat\Mcp\ToolError;
use ApiGoat\Sessions\AuthySession;
use App\Domains\Bot\RunLifecycle;

/**
 * Archive a run: stop entries, hand its slice back to the pool, and either wait
 * out the position or liquidate it.
 *
 * Deliberately requires `run` explicitly, unlike the other tools —
 * AbstractGtbotBase::resolveRun() falls back to "the latest non-Done run", and
 * for a destructive lifecycle action a forgotten argument would retire the
 * wrong one.
 */
class GtbotRetireRunTool extends AbstractGtbotBase
{
    public function name(): string
    {
        return 'gtbot_retire_run';
    }

    public function description(): string
    {
        return 'Archive a run. Entries stop, the kill switch goes on, deploy_pct goes to 0 and the slice returns '
            . 'to the shared pool (working sells consume no quote, so this is safe while holding). A FLAT run goes '
            . "straight to Done; one still holding coins sits in Retiring and archives itself when its exits clear. "
            . "exit=minimize_loss (the default) NEVER realizes a loss — it carries exits at cost and waits. "
            . "exit=sell_now liquidates at the next mark and, when that would book a loss, is REFUSED unless "
            . 'ack_loss:true (and is refused outright under profile NoLoss, whatever ack_loss says). '
            . 'Without confirm:true it only previews. Use gtbot_purge_run afterwards to delete an archived run.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'run' => ['type' => ['integer', 'string'], 'description' => 'id_grid_run or exact label — REQUIRED, no fallback for a destructive action'],
                'exit' => [
                    'type' => 'string',
                    'enum' => [RunLifecycle::EXIT_MINIMIZE, RunLifecycle::EXIT_SELL_NOW],
                    'description' => 'minimize_loss (default): hold the position, exit at cost, never realize a loss. sell_now: liquidate at the next mark.',
                ],
                'reason' => ['type' => 'string', 'description' => 'REQUIRED with confirm — journaled to the event feed and Telegram'],
                'ack_loss' => ['type' => 'boolean', 'description' => 'sell_now only: acknowledge that the exit will realize a loss. Ignored by profile NoLoss, which refuses regardless.'],
                'confirm' => ['type' => 'boolean', 'description' => 'must be true to execute; show the user the preview and get their approval first'],
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
            throw new ToolError('run is required — gtbot_retire_run never guesses which run to archive');
        }
        $target = $this->resolveRun($args);
        $exit = (string) ($args['exit'] ?? RunLifecycle::EXIT_MINIMIZE);
        if (!in_array($exit, RunLifecycle::EXITS, true)) {
            throw new ToolError(sprintf('unknown exit "%s" — use %s', $exit, implode(' or ', RunLifecycle::EXITS)));
        }

        if (empty($args['confirm'])) {
            $i = RunLifecycle::inspect($target);
            return $this->ok([
                'pending' => true,
                'action' => 'retire',
                'exit' => $exit,
                'inspect' => $i,
                'will' => $i['flat']
                    ? 'go straight to Done (the run is flat)'
                    : sprintf('sit in Retiring holding %s until its exits clear', $i['inventory']),
                'frees' => $i['frees_slice'] ? $i['slice'] : '0',
                'needs_ack_loss' => $exit === RunLifecycle::EXIT_SELL_NOW && $i['sell_now_would_realize_loss'],
                'note' => 'Nothing was written — show the user this and re-call with confirm:true once they approve.',
            ]);
        }

        $reason = trim((string) ($args['reason'] ?? ''));
        if ($reason === '') {
            throw new ToolError('reason is required with confirm:true — it is journaled to the event feed and Telegram');
        }

        $res = RunLifecycle::retire($target, $exit, $reason, !empty($args['ack_loss']));
        if (!$res['ok']) {
            throw new ToolError($res['message']);
        }
        return $this->ok($res + ['run' => (int) $target->getIdGridRun()]);
    }
}
