<?php
namespace App\Mcp\Tools;

use ApiGoat\Sessions\AuthySession;
use App\Domains\Bot\GridMath;
use App\Domains\Bot\RiskManager;

class GtbotPreviewGridTool extends AbstractGtbotBase
{
    public function name(): string
    {
        return 'gtbot_preview_grid';
    }

    public function description(): string
    {
        return 'Read-only grid preview: computes the level prices, per-level quantities, spacing and '
            . 'profit-per-grid for a candidate grid config, plus the profitability-floor verdict. '
            . 'Pure math — creates no run and places no orders.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => $this->configSchema(),
            'required' => ['p_low', 'p_high', 'n_levels', 'budget_quote'],
        ];
    }

    public function requiredRight(): ?array
    {
        return null; // pure computation, no data access
    }

    protected function run(array $args, AuthySession $session): array
    {
        $config = $this->configFromArgs($args);
        $errors = RiskManager::validateRunConfig($config);

        $payload = ['config_errors' => $errors, 'viable' => $errors === []];
        if (bccomp($config['p_low'], '0', 12) > 0
            && bccomp($config['p_high'], $config['p_low'], 12) > 0
            && $config['n_levels'] >= 2
        ) {
            $levels = GridMath::levels($config['p_low'], $config['p_high'], $config['n_levels'], $config['spacing']);
            $buyLevels = array_slice($levels, 0, -1);
            $qtys = GridMath::allocate($config['budget_quote'], $buyLevels, $config['allocation']);
            $spacing = GridMath::spacingPct($config['p_low'], $config['p_high'], $config['n_levels'], $config['spacing']);
            $payload += [
                'levels' => $levels,
                'buy_level_qtys' => $qtys,
                'spacing_pct' => $spacing,
                'profit_per_grid_pct' => GridMath::profitPerGridPct($spacing, $config['fee_pct']),
                'round_trip_fee_pct' => bcmul('2', $config['fee_pct'], 12),
            ];
        }
        return $this->ok($payload);
    }
}
