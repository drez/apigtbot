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
        return 'EMERGENCY STOP: sets the run\'s kill switch (daemon re-reads it every tick) and enqueues Kill — '
            . 'open buys cancel, inventory is HELD unless flatten:true. With \'Sell at loss\' OFF a flatten is refused '
            . 'only when it would book a loss at the run\'s last price (above breakeven it proceeds); profile NoLoss refuses either way. '
            . 'reason is mandatory (journaled + Telegram). '
            . 'Without confirm:true it only echoes the pending action. Never flatten unless explicitly asked.';
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
        if ($flatten && !$run->getSellAtLoss()) {
            // The switch forbids realizing LOSSES, not gains: price the
            // liquidation against the run's cost basis and only refuse one
            // that would actually book a loss. This is a pre-flight off the
            // last stamped tick price — the daemon re-checks against its own
            // live mark before selling anything.
            $store = new \App\Domains\Bot\OrderStore(
                (int) $run->getIdGridRun(),
                (string) $run->getRunUid(),
                $run->getLedgerResetAt('Y-m-d H:i:s'),
                (bool) $run->getSimulated()
            );
            $vwap = \App\Domains\Bot\LossGuard::positionVwap($store);
            $fee = (string) ($run->getFeePct() ?: '0');
            $price = $run->getLastPrice() !== null ? (string) $run->getLastPrice() : null;
            if (\App\Domains\Bot\LossGuard::wouldRealizeLoss($vwap, $fee, $price)) {
                throw new ToolError(sprintf(
                    "run %d has 'Sell at loss' OFF and flattening at %s would realize a loss (cost basis %s, breakeven %s) — refused; "
                    . 'turn the run switch on first (crm_update grid_run sell_at_loss=1), wait for price to clear cost, or use a plain kill (cancel buys, HOLD inventory)',
                    (int) $run->getIdGridRun(),
                    (string) $price,
                    (string) $vwap,
                    \App\Domains\Bot\LossGuard::breakeven((string) $vwap, $fee)
                ));
            }
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
