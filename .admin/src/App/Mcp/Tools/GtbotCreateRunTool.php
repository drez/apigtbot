<?php
namespace App\Mcp\Tools;

use ApiGoat\Mcp\ToolError;
use ApiGoat\Sessions\AuthySession;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\GridMath;
use App\Domains\Bot\RiskManager;
use App\Domains\Bot\TelegramNotifier;

/**
 * Create a NEW grid run row (a second symbol runs alongside the first — each
 * run gets its own daemon via the templated systemd unit gtbot@<id>). The tool
 * only creates the row: status is capped at Testnet (going Live is a
 * deliberate human step in the GUI), and the daemon process still has to be
 * launched. Validated like set_grid (fee floor, brackets price) and
 * confirm-gated like the other mutating tools.
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
        return 'Create a NEW grid run (row only — does not start trading). Use for adding a second '
            . 'symbol alongside an existing run; every active run gets its own daemon (systemd '
            . 'gtbot@<id>). A call without confirm:true changes nothing: it echoes the validated '
            . 'pending record for user approval — show it, then re-call with confirm:true. Status may '
            . 'be Draft/DryRun/Testnet only; flipping a run Live is a human step in the GUI. After '
            . 'creating, the daemon must be launched (systemctl enable --now gtbot@<id>) and then '
            . 'gtbot_start enqueues the Start.';
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
        $label = trim((string) ($args['label'] ?? ''));
        $symbol = strtoupper(trim((string) ($args['symbol'] ?? '')));
        if ($label === '' || $symbol === '') {
            throw new ToolError('label and symbol are required');
        }
        if (\App\GridRunQuery::create()->findOneByLabel($label)) {
            throw new ToolError("a run labeled '$label' already exists — labels must be unique (tools resolve runs by label)");
        }
        $status = (string) ($args['status'] ?? 'Draft');
        if (!in_array($status, ['Draft', 'DryRun', 'Testnet'], true)) {
            throw new ToolError('status must be Draft, DryRun or Testnet — going Live is a human step in the GUI');
        }

        $pLow = (string) ($args['p_low'] ?? '0');
        $pHigh = (string) ($args['p_high'] ?? '0');
        $n = (int) ($args['n_levels'] ?? 0);
        $spacing = ($args['spacing'] ?? 'Geometric') === 'Arithmetic' ? 'Arithmetic' : 'Geometric';
        $allocation = in_array((string) ($args['allocation'] ?? ''), ['EqualQuote', 'EqualBase', 'BottomWeighted'], true)
            ? (string) $args['allocation'] : 'EqualQuote';
        $budget = (string) ($args['budget_quote'] ?? '0');
        $feePct = (string) ($args['fee_pct'] ?? '0.001');
        $price = $this->price($symbol);

        $errors = RiskManager::validateRunConfig([
            'p_low' => $pLow, 'p_high' => $pHigh, 'n_levels' => $n, 'spacing' => $spacing,
            'budget_quote' => $budget, 'fee_pct' => $feePct, 'min_notional' => '5',
        ]);
        // must bracket the current price, or the grid sits idle / underwater
        if ($price !== null) {
            if (bccomp($pLow, $price, 8) >= 0 || bccomp($pHigh, $price, 8) <= 0) {
                $errors[] = sprintf('range [%s, %s] must bracket the current price %s', $pLow, $pHigh, $price);
            }
        }
        $profileArg = (string) ($args['profile'] ?? 'Balanced');
        if (!in_array($profileArg, \App\Domains\Bot\ProfilePolicy::PROFILES, true)) {
            $errors[] = sprintf(
                "unknown profile '%s' — one of %s",
                $profileArg,
                implode('|', \App\Domains\Bot\ProfilePolicy::PROFILES)
            );
        }
        // fallback only feeds the preview's derived_caps computation below
        // (which would otherwise throw on a bad profile) — the run is never
        // created while $errors is non-empty (see the check below)
        $profile = in_array($profileArg, \App\Domains\Bot\ProfilePolicy::PROFILES, true) ? $profileArg : 'Balanced';

        $algo = ($args['algo'] ?? 'Grid') === 'Trend' ? 'Trend' : 'Grid';
        if ($algo === 'Trend' && $profile === 'NoLoss') {
            $errors[] = 'algo Trend cannot run under profile NoLoss — a trend algo must realize stop losses; pick Cautious or higher';
        }
        // never overcommit: the new run's slice must fit the shared wallet
        // alongside every other active run's budget (Draft doesn't count)
        $over = \App\Domains\Bot\BudgetGuard::check(null, $budget, $status);
        if ($over !== null) {
            $errors[] = \App\Domains\Bot\BudgetGuard::message($over);
        }

        $levels = [];
        if (bccomp($pLow, '0', 8) > 0 && bccomp($pHigh, $pLow, 8) > 0 && $n >= 2) {
            $levels = GridMath::levels($pLow, $pHigh, $n, $spacing);
        }
        $record = [
            'label' => $label, 'symbol' => $symbol, 'status' => $status,
            'p_low' => $pLow, 'p_high' => $pHigh, 'n_levels' => $n,
            'spacing' => $spacing, 'allocation' => $allocation,
            'budget_quote' => $budget, 'fee_pct' => $feePct,
            'profile' => $profile,
            'algo' => $algo,
            'breakout_buffer_pct' => (string) ($args['breakout_buffer_pct'] ?? '0.02'),
            'max_open_orders' => (int) ($args['max_open_orders'] ?? 60),
            'max_buy_levels_below' => isset($args['max_buy_levels_below']) ? (int) $args['max_buy_levels_below'] : null,
        ];
        $preview = [
            'record' => $record,
            'levels' => $levels,
            'spacing_pct' => $levels ? GridMath::spacingPct($pLow, $pHigh, $n, $spacing) : null,
            'current_price' => $price,
            // live policy: these are what ProfilePolicy::apply() will stamp on the row at creation
            'derived_caps' => \App\Domains\Bot\ProfilePolicy::caps($profile, $budget),
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

        $run = new \App\GridRun();
        $run->setLabel($label);
        $run->setSymbol($symbol);
        $run->setStatus($status);
        $run->setPLow($pLow);
        $run->setPHigh($pHigh);
        $run->setNLevels($n);
        $run->setSpacing($spacing);
        $run->setAllocation($allocation);
        $run->setBudgetQuote($budget);
        $run->setFeePct($feePct);
        $run->setProfile($profile);
        $run->setAlgo($algo);
        $run->setBreakoutBufferPct($record['breakout_buffer_pct']);
        $run->setMaxOpenOrders($record['max_open_orders']);
        if ($record['max_buy_levels_below'] !== null) {
            $run->setMaxBuyLevelsBelow($record['max_buy_levels_below']);
        }
        // live policy: derive daily-loss/unrealized-stop/position/order caps +
        // breakout policy from profile × budget — born with the same caps the
        // daemon/service wrapper would stamp on the next save.
        \App\Domains\Bot\ProfilePolicy::apply($run);
        $run->save();
        $id = (int) $run->getIdGridRun();

        $e = new \App\BotEvent();
        $e->setIdGridRun($id);
        $e->setLevel('Info');
        $e->setKind('run_created');
        $e->setMessage(mb_substr(sprintf('run created via MCP: %s %s [%s, %s] × %d, budget %s', $label, $symbol, $pLow, $pHigh, $n, $budget), 0, 500));
        $e->setPayload(json_encode($record, JSON_UNESCAPED_SLASHES));
        $e->save();
        if ($tg = TelegramNotifier::fromEnv()) {
            $tg->send(sprintf('🆕 gtbot run %d created — %s %s [%s, %s] × %d (%s), budget %s', $id, $label, $symbol, $pLow, $pHigh, $n, $status, $budget));
        }

        return $this->ok([
            'created' => true,
            'id_grid_run' => $id,
            'record' => $record,
            'next_steps' => array_values(array_filter([
                'launch the daemon: systemctl enable --now gtbot@' . $id . ' (or php bin/gtbot --run=' . $id . ')',
                'then gtbot_start run:' . $id . ' to enqueue the Start command',
                $status === 'Draft' ? 'status is Draft — set DryRun/Testnet before it will trade' : null,
            ])),
        ]);
    }

    private function price(string $symbol): ?string
    {
        if ($this->priceOverride !== null) {
            return $this->priceOverride;
        }
        try {
            $base = env('GTBOT_USE_TESTNET', '1') !== '0' ? 'https://testnet.binance.vision' : 'https://api.binance.com';
            return (new BinanceGateway($base, '', ''))->tickerPrice($symbol);
        } catch (\Throwable) {
            return null;
        }
    }
}
