<?php
namespace App\Mcp\Tools;

use ApiGoat\Sessions\AuthySession;
use App\BotEventQuery;
use App\BotOrderQuery;
use App\Domains\Bot\DrawdownGuard;
use App\Domains\Bot\OrderStore;
use App\Domains\Bot\SimWallet;
use App\TradeCycleQuery;

class GtbotStatusTool extends AbstractGtbotBase
{
    public function name(): string
    {
        return 'gtbot_status';
    }

    public function description(): string
    {
        return 'Read-only run status: mode, kill switch, heartbeat, requested vs APPLIED geometry (refit_pending), '
            . 'open buys/sells, invested, realized PnL, cycles, recent alerts, drawdown block. Drill-down behind gtbot_routine_brief.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'run' => ['type' => ['integer', 'string'], 'description' => 'id_grid_run or exact label; omit for the latest run'],
            ],
        ];
    }

    public function requiredRight(): ?array
    {
        return ['GridRun', 'r'];
    }

    protected function run(array $args, AuthySession $session): array
    {
        $run = $this->resolveRun($args);
        $runId = (int) $run->getIdGridRun();
        $simulated = (bool) $run->getSimulated();
        $store = new OrderStore($runId, (string) $run->getRunUid(), null, $simulated);

        $openBuys = BotOrderQuery::create()->filterByIdGridRun($runId)->filterBySimulated($simulated)->filterByState('BUY_OPEN')->count();
        $openSells = BotOrderQuery::create()->filterByIdGridRun($runId)->filterBySimulated($simulated)->filterByState(['SELL_OPEN', 'PartFilled'], \Criteria::IN)->count();

        $realizedTotal = '0';
        foreach (TradeCycleQuery::create()->filterByIdGridRun($runId)->filterBySimulated($simulated)->find() as $c) {
            $realizedTotal = bcadd($realizedTotal, (string) $c->getRealizedPnl(), 12);
        }

        $alerts = [];
        $rows = BotEventQuery::create()
            ->filterByIdGridRun($runId)
            ->filterByLevel(['Alert', 'Error'], \Criteria::IN)
            ->orderByIdBotEvent(\Criteria::DESC)
            ->limit(5)
            ->find();
        foreach ($rows as $e) {
            $alerts[] = [
                'at' => $e->getDateCreation('Y-m-d H:i:s'),
                'level' => (string) $e->getLevel(),
                'kind' => (string) $e->getKind(),
                'message' => (string) $e->getMessage(),
            ];
        }

        // Requested (run row) vs applied (what the working ladder was built
        // on): the refit brain must SEE when its last decision is still
        // queued — otherwise it stacks a new refit every cycle, none apply,
        // and the decision journal scores ghosts.
        $requested = [
            'p_low' => (string) $run->getPLow(),
            'p_high' => (string) $run->getPHigh(),
            'n_levels' => (int) $run->getNLevels(),
            'spacing' => (string) $run->getSpacing(),
            'deploy_pct' => (int) ($run->getDeployPct() ?? 100),
        ];
        $appliedRaw = (string) ($run->getAppliedGeometry() ?? '');
        $applied = $appliedRaw !== '' ? json_decode($appliedRaw, true) : null;
        $applied = is_array($applied) ? $applied : null;
        $refitPending = false;
        if ($applied !== null) {
            $refitPending = bccomp((string) ($applied['p_low'] ?? '0'), $requested['p_low'], 8) !== 0
                || bccomp((string) ($applied['p_high'] ?? '0'), $requested['p_high'], 8) !== 0
                || (int) ($applied['n_levels'] ?? 0) !== $requested['n_levels']
                || (string) ($applied['spacing'] ?? '') !== $requested['spacing']
                || (int) ($applied['deploy_pct'] ?? 100) !== $requested['deploy_pct'];
        }

        $algo = (string) ($run->getAlgo() ?: 'Grid');

        $out = [
            'run' => [
                'id' => $runId,
                'label' => (string) $run->getLabel(),
                'symbol' => (string) $run->getSymbol(),
                'status' => (string) $run->getStatus(),
                'kill_switch' => (bool) $run->getKillSwitch(),
                'profile' => (string) $run->getProfile(),
                'sell_at_loss' => (bool) $run->getSellAtLoss(),
                'sell_when_starved' => (bool) $run->getSellWhenStarved(),
                'algo' => $algo,
            ],
            'mode' => $simulated ? 'simulated' : 'real',
            'drawdown' => self::drawdownBlock(),
            'heartbeat' => $this->heartbeat($run),
            'geometry' => [
                'requested' => $requested,
                'applied' => $applied,
                'refit_pending' => $refitPending,
            ],
            'orders' => ['open_buys' => $openBuys, 'open_sells' => $openSells],
            'invested_quote' => $store->investedQuote(),
            'realized_pnl_total' => $realizedTotal,
            'realized_pnl_today' => $store->realizedToday(),
            'cycles' => TradeCycleQuery::create()->filterByIdGridRun($runId)->filterBySimulated($simulated)->count(),
            'recent_alerts' => $alerts,
        ];
        if ($algo === 'Trend') {
            // set_grid does not apply to a Trend run — surface the engine's own
            // position/stop state instead, so the routine can report on it
            // (skip the run, don't try to grid-refit it) without a second call.
            $out['engine'] = json_decode((string) ($run->getEngineState() ?? ''), true);
        }

        return $this->ok($out);
    }

    /** Global wallet-floor snapshot — same numbers the daemons enforce. */
    private static function drawdownBlock(): array
    {
        // the floor follows the wallet equity() MEASURED (2026-09-21): the
        // paper pool seed on paper, the funded real account once real money
        // is exposed. Reading floor() without the source printed a paper
        // floor beside a real equity.
        $eq = DrawdownGuard::equity();
        $floor = DrawdownGuard::floor($eq['source']);
        if ($floor === null) {
            return ['enabled' => false];
        }
        return [
            'enabled' => true,
            'equity' => bcadd($eq['equity'], '0', 2),
            'floor' => bcadd($floor, '0', 2),
            'source' => $eq['source'],
            'budget' => DrawdownGuard::baselineFor($eq['source']),
            'max_drawdown_pct' => DrawdownGuard::maxDrawdownPct(),
            'tripped' => bccomp($eq['equity'], $floor, 12) < 0,
            'unpriced' => $eq['unpriced'],
        ];
    }
}
