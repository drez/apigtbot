<?php
namespace App\Mcp\Tools;

use ApiGoat\Sessions\AuthySession;
use App\BotDecisionQuery;

/**
 * The strategy-test journal over MCP. Every refit decision is logged
 * (bot_decision) with its reasoning, deploy_pct, applied_at and — once the
 * gtbot-refit cron re-evaluates it ~6h after it took effect — a scored
 * verdict. This tool serves the raw log plus win-rate aggregates by
 * deployment band, so the routine can data-mine which postures actually
 * made money before repeating them.
 */
class GtbotDecisionsTool extends AbstractGtbotBase
{
    public function name(): string
    {
        return 'gtbot_decisions';
    }

    public function description(): string
    {
        return 'Read-only strategy-test journal: every refit decision (range, n_levels, deploy_pct, '
            . 'reason, applied_at) with its scored verdict (Win/Flat/Loss/Superseded — scored ~6h after '
            . 'the geometry actually applied, against realized cycles since then), plus win-rate '
            . 'aggregates by deployment band. Consult this BEFORE deciding: it is your own track record '
            . 'of which postures and deployment sizes worked. Defaults to the most recent non-Done run.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'run' => ['type' => ['integer', 'string'], 'description' => 'id_grid_run or exact label; omit for the latest run'],
                'verdict' => ['type' => 'string', 'enum' => ['Win', 'Flat', 'Loss', 'Superseded'], 'description' => 'only decisions with this verdict'],
                'source' => ['type' => 'string', 'enum' => ['Claude', 'Cron', 'Manual'], 'description' => 'only decisions from this source'],
                'limit' => ['type' => 'integer', 'description' => 'max rows (default 20, cap 100)'],
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

        $q = BotDecisionQuery::create()->filterByIdGridRun($runId);
        if (!empty($args['verdict'])) {
            $q->filterByVerdict((string) $args['verdict']);
        }
        if (!empty($args['source'])) {
            $q->filterBySource((string) $args['source']);
        }
        $limit = min(100, max(1, (int) ($args['limit'] ?? 20)));

        $decisions = [];
        foreach ($q->orderByIdBotDecision(\Criteria::DESC)->limit($limit)->find() as $d) {
            $decisions[] = [
                'at' => $d->getDateCreation('Y-m-d H:i'),
                'source' => (string) $d->getSource(),
                'range' => [(string) $d->getPLow(), (string) $d->getPHigh()],
                'n_levels' => (int) $d->getNLevels(),
                'deploy_pct' => $d->getDeployPct() !== null ? (int) $d->getDeployPct() : null,
                'reason' => (string) $d->getReason(),
                'applied_at' => $d->getAppliedAt('Y-m-d H:i'),
                'eval_status' => (string) $d->getEvalStatus(),
                'verdict' => $d->getVerdict() !== null ? (string) $d->getVerdict() : null,
                'cycles_after' => $d->getCyclesDelta() !== null ? (int) $d->getCyclesDelta() : null,
                'realized_after' => $d->getRealizedDelta() !== null ? (string) $d->getRealizedDelta() : null,
                'price_move_pct' => $d->getPriceMovePct() !== null ? (float) $d->getPriceMovePct() : null,
            ];
        }

        // strategy scoreboard: scored (non-Superseded) decisions bucketed by
        // deployment band — which exposure sizes actually made money
        $bands = [
            '10-40' => ['scored' => 0, 'win' => 0, 'flat' => 0, 'loss' => 0, 'realized' => '0'],
            '41-70' => ['scored' => 0, 'win' => 0, 'flat' => 0, 'loss' => 0, 'realized' => '0'],
            '71-100' => ['scored' => 0, 'win' => 0, 'flat' => 0, 'loss' => 0, 'realized' => '0'],
        ];
        foreach (BotDecisionQuery::create()
            ->filterByIdGridRun($runId)
            ->filterByEvalStatus('Scored')
            ->filterByVerdict('Superseded', \Criteria::NOT_EQUAL)
            ->find() as $d) {
            $pct = $d->getDeployPct() !== null ? (int) $d->getDeployPct() : 100;
            $band = $pct <= 40 ? '10-40' : ($pct <= 70 ? '41-70' : '71-100');
            $bands[$band]['scored']++;
            $bands[$band][strtolower((string) $d->getVerdict())]++;
            $bands[$band]['realized'] = bcadd($bands[$band]['realized'], (string) ($d->getRealizedDelta() ?? '0'), 8);
        }

        return $this->ok([
            'run' => ['id' => $runId, 'label' => (string) $run->getLabel()],
            'decisions' => $decisions,
            'summary' => ['by_deploy_band' => $bands],
            'note' => 'verdicts are re-evaluated hourly by the gtbot-refit cron once a decision has been '
                . 'applied for 6h; Superseded = replaced before it ever traded (no evidence).',
        ]);
    }
}
