<?php
namespace App\Mcp\Tools;

use ApiGoat\Mcp\ToolError;
use ApiGoat\Sessions\AuthySession;
use App\Domains\Bot\Backtester;

class GtbotBacktestTool extends AbstractGtbotBase
{
    private const MAX_SERIES = 20000;
    private const LEDGER_TAIL = 50;

    public function name(): string
    {
        return 'gtbot_backtest';
    }

    public function description(): string
    {
        return 'Run the grid strategy over a supplied price series through the simulated exchange '
            . '(perfect fills — results are directional, not promises). Returns cycles, realized/unrealized '
            . 'PnL, fees, halt state and the tail of the per-cycle ledger. Read-only: no run, no orders.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => $this->configSchema() + [
                'series' => [
                    'type' => 'array',
                    'items' => ['type' => ['string', 'number']],
                    'description' => 'Price tape; first element is the starting price. Max ' . self::MAX_SERIES . ' points.',
                ],
            ],
            'required' => ['p_low', 'p_high', 'n_levels', 'budget_quote', 'series'],
        ];
    }

    public function requiredRight(): ?array
    {
        return null; // pure computation, no data access
    }

    protected function run(array $args, AuthySession $session): array
    {
        $series = $args['series'] ?? [];
        if (!is_array($series) || $series === []) {
            throw new ToolError('series must be a non-empty array of prices');
        }
        if (count($series) > self::MAX_SERIES) {
            throw new ToolError('series exceeds ' . self::MAX_SERIES . ' points');
        }
        $tape = array_map(static fn ($p) => (string) $p, $series);

        try {
            $result = Backtester::run($this->configFromArgs($args), $tape);
        } catch (\InvalidArgumentException $e) {
            throw new ToolError('invalid config: ' . $e->getMessage());
        }

        $dropped = max(0, count($result['cycle_ledger']) - self::LEDGER_TAIL);
        $result['cycle_ledger'] = array_slice($result['cycle_ledger'], -self::LEDGER_TAIL);
        $result['ledger_truncated'] = $dropped;
        return $this->ok($result);
    }
}
