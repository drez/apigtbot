<?php
namespace App\Mcp\Tools;

use ApiGoat\Mcp\ToolError;
use ApiGoat\Sessions\AuthySession;
use App\Domains\Bot\GridMath;
use App\Domains\Bot\RunFactory;
use App\Domains\Bot\TelegramNotifier;

/**
 * Create a NEW grid run row (a second symbol runs alongside the first — each
 * run gets its own daemon via the templated systemd unit gtbot@<id>). The tool
 * only creates the row: status is capped at Testnet (going Live is a
 * deliberate human step in the GUI), and the daemon process still has to be
 * launched. Validated like set_grid (fee floor, brackets price) and
 * confirm-gated like the other mutating tools.
 *
 * The validation and the write itself live in Domains\Bot\RunFactory — the
 * fleet's empty-slot path creates arms through the same checks, and a second
 * copy of a validator guarding real money is drift waiting to happen. What
 * stays here is the MCP surface: argument schema, preview, confirm gate.
 */
class GtbotCreateRunTool extends AbstractGtbotBase
{
    public function __construct(private ?string $priceOverride = null)
    {
    }

    public function name(): string
    {
        return 'gtbot_create_run';
    }

    public function description(): string
    {
        return 'Create a NEW grid run row (no trading starts). Without confirm:true it only echoes the validated '
            . 'pending record — show it, then re-call with confirm:true. Status Draft/DryRun/Testnet only; Live is a '
            . 'human GUI step. The watchdog cron spawns the daemon; gtbot_start enqueues Start.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'label' => ['type' => 'string', 'description' => 'Unique human label, e.g. "BNB testnet soak"'],
                'symbol' => ['type' => 'string', 'description' => 'Trading pair, e.g. BNBUSDT'],
                'status' => ['type' => 'string', 'enum' => ['Draft', 'DryRun', 'Testnet'], 'description' => 'default Draft; Live is deliberately not creatable here'],
                'p_low' => ['type' => 'string', 'description' => 'Grid floor (must be below current price)'],
                'p_high' => ['type' => 'string', 'description' => 'Grid ceiling (must be above current price)'],
                'n_levels' => ['type' => 'integer', 'description' => 'Number of grid intervals (≥ 2)'],
                'spacing' => ['type' => 'string', 'enum' => ['Geometric', 'Arithmetic'], 'description' => 'default Geometric'],
                'allocation' => ['type' => 'string', 'enum' => ['EqualQuote', 'EqualBase', 'BottomWeighted'], 'description' => 'default EqualQuote'],
                'budget_quote' => ['type' => 'string', 'description' => 'Total quote budget (USDT). Mind the shared wallet: all runs draw on the same balance'],
                'fee_pct' => ['type' => 'string', 'description' => 'Per-side fee fraction, default 0.001'],
                'breakout_buffer_pct' => ['type' => 'string', 'description' => 'default 0.02'],
                'profile' => ['type' => 'string', 'enum' => ['NoLoss', 'Cautious', 'Balanced', 'Aggressive', 'Max'], 'description' => 'risk profile (live policy, derives the caps) — default Balanced'],
                'sell_at_loss' => ['type' => 'boolean', 'description' => 'allow automated sells that realize a loss (trend stop-out below cost, Flatten) — default false: such stop-outs are held and a Flatten under breakeven is refused (one above cost still proceeds)'],
                'sell_when_starved' => ['type' => 'boolean', 'description' => 'Grid only: when the ladder cannot fund a single buy (no_budget), reprice legacy exits down to market — closest to market first, only as many as refund the ladder — realizing a loss on purpose; default false (hold)'],
                'algo' => ['type' => 'string', 'enum' => ['Grid', 'Trend'], 'description' => 'default Grid'],
                'max_open_orders' => ['type' => 'integer', 'description' => 'default 60'],
                'max_buy_levels_below' => ['type' => 'integer', 'description' => 'Active buy levels below price, 0/omit = all'],
                'confirm' => ['type' => 'boolean', 'description' => 'must be true to execute; get the user\'s approval first'],
            ],
            'required' => ['label', 'symbol', 'p_low', 'p_high', 'n_levels', 'budget_quote'],
        ];
    }

    public function requiredRight(): ?array
    {
        return ['GridRun', 'w'];
    }

    protected function run(array $args, AuthySession $session): array
    {
        $record = RunFactory::record($args);
        if ($record['label'] === '' || $record['symbol'] === '') {
            throw new ToolError('label and symbol are required');
        }
        if (\App\GridRunQuery::create()->findOneByLabel($record['label'])) {
            throw new ToolError("a run labeled '{$record['label']}' already exists — labels must be unique (tools resolve runs by label)");
        }
        if (!in_array($record['status'], RunFactory::CREATABLE_STATUSES, true)) {
            throw new ToolError('status must be Draft, DryRun or Testnet — going Live is a human step in the GUI');
        }

        $price = $this->price($record['symbol']);
        $errors = RunFactory::validate($record, $price);

        $levels = [];
        if (bccomp($record['p_low'], '0', 8) > 0 && bccomp($record['p_high'], $record['p_low'], 8) > 0 && $record['n_levels'] >= 2) {
            $levels = GridMath::levels($record['p_low'], $record['p_high'], $record['n_levels'], $record['spacing']);
        }
        // a bad profile would throw in caps(); the run is never created while
        // $errors is non-empty, so the preview falls back for display only
        $profile = in_array($record['profile'], \App\Domains\Bot\ProfilePolicy::PROFILES, true) ? $record['profile'] : 'Balanced';
        $preview = [
            'record' => $this->publicRecord($record),
            'levels' => $levels,
            'spacing_pct' => $levels ? GridMath::spacingPct($record['p_low'], $record['p_high'], $record['n_levels'], $record['spacing']) : null,
            'current_price' => $price,
            // live policy: these are what ProfilePolicy::apply() will stamp on the row at creation
            'derived_caps' => \App\Domains\Bot\ProfilePolicy::caps($profile, $record['budget_quote']),
        ];

        if ($errors) {
            return $this->ok(['created' => false, 'errors' => $errors, 'preview' => $preview]);
        }
        if (empty($args['confirm'])) {
            return $this->ok([
                'pending' => true,
                'action' => 'Create grid run',
                'preview' => $preview,
                'note' => 'Nothing created yet — show the user this record and re-call with confirm:true once they approve.',
            ]);
        }

        $run = RunFactory::create($record, TelegramNotifier::fromEnv(), 'MCP');
        $id = (int) $run->getIdGridRun();

        return $this->ok([
            'created' => true,
            'id_grid_run' => $id,
            'record' => $this->publicRecord($record),
            'next_steps' => array_values(array_filter([
                'launch the daemon: systemctl enable --now gtbot@' . $id . ' (or php bin/gtbot --run=' . $id . ')',
                'then gtbot_start run:' . $id . ' to enqueue the Start command',
                $record['status'] === 'Draft' ? 'status is Draft — set DryRun/Testnet before it will trade' : null,
            ])),
        ]);
    }

    /** The record as this tool has always echoed it (no fleet-only fields). */
    private function publicRecord(array $record): array
    {
        unset($record['deploy_pct'], $record['alloc_mode'], $record['trend_signal']);
        return $record;
    }

    /** Always the exchange: the bracket rule validates a range the operator
     *  typed against the price RIGHT NOW, not against a stored summary. */
    private function price(string $symbol): ?string
    {
        return $this->priceOverride ?? RunFactory::ticker($symbol);
    }
}
