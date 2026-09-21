<?php
namespace App\Mcp\Tools;

use ApiGoat\Mcp\ToolError;
use ApiGoat\Sessions\AuthySession;
use App\Domains\Bot\Backtester;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\MarketStore;
use App\Domains\Bot\RangeFitter;
use App\Domains\Bot\RiskManager;

/**
 * READ-ONLY refit inspector: klines → quantile range-fit → backtest sweep,
 * returning a ranked comparison. It no longer creates Draft runs — re-fitting
 * is automated server-side (the gtbot-refit cron re-anchors the live grid in
 * place), so drafts would only pile up unreviewed and shadow the live run.
 */
class GtbotRefitProposalTool extends AbstractGtbotBase
{
    public function __construct(private ?BinanceGateway $gateway = null)
    {
    }

    public function name(): string
    {
        return 'gtbot_refit_proposal';
    }

    public function description(): string
    {
        return 'READ-ONLY what-if: quantile-fit candidate ranges on recent candles + in-sample backtest (perfect '
            . 'fills — directional only). Creates/applies nothing.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'symbol' => ['type' => 'string', 'description' => 'Trading pair (default BTCUSDT)'],
                'interval' => ['type' => 'string', 'description' => 'Kline interval (default 4h)'],
                'limit' => ['type' => 'integer', 'description' => 'Number of candles (default 180 ≈ 30 days of 4h)'],
                'budget_quote' => ['type' => 'string', 'description' => 'Quote budget for the candidates (required)'],
                'fee_pct' => ['type' => 'string', 'description' => 'Per-side fee fraction (default 0.001)'],
            ],
            'required' => ['budget_quote'],
        ];
    }

    public function requiredRight(): ?array
    {
        return ['GridRun', 'r']; // read-only now — no longer writes runs
    }

    protected function run(array $args, AuthySession $session): array
    {
        $symbol = strtoupper((string) ($args['symbol'] ?? 'BTCUSDT'));
        $interval = (string) ($args['interval'] ?? '4h');
        $limit = max(30, min(1000, (int) ($args['limit'] ?? 180)));
        $budget = (string) $args['budget_quote'];
        $fee = (string) ($args['fee_pct'] ?? '0.001');

        // Prefer stored candles (collector-populated); fall back to a live
        // fetch only if the DB has nothing yet.
        $candles = MarketStore::candles($symbol, $interval);
        if (count($candles) < 10) {
            $candles = $this->gateway()->klines($symbol, $interval, $limit);
        }
        if (count($candles) < 10) {
            throw new ToolError("only " . count($candles) . " candles for $symbol $interval");
        }
        $closes = array_column($candles, 'close');

        $candidates = RangeFitter::candidates($candles, $fee, $budget);
        if ($candidates === []) {
            return $this->ok([
                'candidates' => [],
                'note' => 'no viable envelope — market too flat for the fee floor at this budget/fee',
            ]);
        }

        $report = [];
        foreach ($candidates as $cand) {
            $config = $this->configFromArgs([
                'p_low' => $cand['p_low'],
                'p_high' => $cand['p_high'],
                'n_levels' => $cand['n_levels'],
                'budget_quote' => $budget,
                'fee_pct' => $fee,
            ]);
            $entry = $cand;
            $errors = RiskManager::validateRunConfig($config);
            if ($errors) {
                $entry['viable'] = false;
                $entry['errors'] = $errors;
            } else {
                $bt = Backtester::run($config, $closes);
                $entry['viable'] = true;
                $entry['backtest'] = [
                    'cycles' => $bt['cycles'],
                    'realized_pnl' => $bt['realized_pnl'],
                    'unrealized_pnl' => $bt['unrealized_pnl'],
                    'fees_total' => $bt['fees_total'],
                    'halted' => $bt['halted'],
                    'ending_inventory' => $bt['ending_inventory'],
                ];
            }
            $report[] = $entry;
        }
        usort($report, static function (array $a, array $b): int {
            $ap = $a['viable'] ? (float) $a['backtest']['realized_pnl'] : -INF;
            $bp = $b['viable'] ? (float) $b['backtest']['realized_pnl'] : -INF;
            return $bp <=> $ap;
        });

        $payload = [
            'symbol' => $symbol,
            'window' => ['interval' => $interval, 'candles' => count($candles)],
            'current_price' => end($closes),
            'candidates' => $report,
            'honesty_note' => 'Backtests assume perfect fills over the FITTED window — in-sample and flattering. '
                . 'A grid has no predictive edge; this re-fits parameters to realized volatility, nothing more.',
        ];

        // Draft creation was retired: re-fitting is automated server-side
        // (gtbot-refit cron re-anchors the live grid in place). Refuse rather
        // than pile up unreviewed Drafts that shadow the live run.
        if (!empty($args['create_draft'])) {
            $payload['create_draft_disabled'] = 'This tool is read-only; refit is automated server-side '
                . '(gtbot-refit cron). No Draft was created. To change the live grid, adjust the run in the '
                . 'GUI — the daemon re-anchors within a tick (legacy exits carried).';
        }

        return $this->ok($payload);
    }

    private function gateway(): BinanceGateway
    {
        if ($this->gateway === null) {
            // Analysis on REAL market data (mainnet klines: public + full history).
            $base = (string) env('GTBOT_ANALYSIS_BASE', 'https://api.binance.com');
            $this->gateway = new BinanceGateway($base, '', ''); // public endpoints only
        }
        return $this->gateway;
    }
}
