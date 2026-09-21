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
        return 'Read-only refit decision journal: every set_grid (range, levels, deploy_pct, reason, applied_at) with '
            . 'its receipt (requested vs applied: clamp trail, candidate_delta = same|deviated vs the brief candidate) and '
            . 'verdict (Win/Flat/Loss/Worse/Superseded, scored ~6h after applying; Worse = the replaced geometry would have '
            . 'earned more on the same tape, counterfactual). Win rates by deploy band and by candidate_delta. '
            . 'Drill-down behind gtbot_routine_brief.track.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'run' => ['type' => ['integer', 'string'], 'description' => 'id_grid_run or exact label; omit for the latest run'],
                'verdict' => ['type' => 'string', 'enum' => ['Win', 'Flat', 'Loss', 'Worse', 'Superseded'], 'description' => 'only decisions with this verdict'],
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
                'counterfactual' => $d->getCounterfactualDelta() !== null ? (string) $d->getCounterfactualDelta() : null,
                'candidate_delta' => $d->getCandidateDelta() !== null ? (string) $d->getCandidateDelta() : null,
                'clamps' => $d->getClampsJson() ? json_decode((string) $d->getClampsJson(), true) : [],
                'requested' => $d->getRequestedJson() ? json_decode((string) $d->getRequestedJson(), true) : null,
            ];
        }

        // strategy scoreboard: scored (non-Superseded) decisions bucketed by
        // deployment band — which exposure sizes actually made money
        $empty = ['scored' => 0, 'win' => 0, 'flat' => 0, 'loss' => 0, 'worse' => 0, 'realized' => '0', 'counterfactual' => '0'];
        $bands = ['10-40' => $empty, '41-70' => $empty, '71-100' => $empty];
        // did following the deterministic candidate beat deviating from it?
        $byCand = ['same' => $empty, 'deviated' => $empty, 'none' => $empty];
        foreach (BotDecisionQuery::create()
            ->filterByIdGridRun($runId)
            ->filterByEvalStatus('Scored')
            ->filterByVerdict('Superseded', \Criteria::NOT_EQUAL)
            ->find() as $d) {
            $pct = $d->getDeployPct() !== null ? (int) $d->getDeployPct() : 100;
            $band = $pct <= 40 ? '10-40' : ($pct <= 70 ? '41-70' : '71-100');
            $cd = $d->getCandidateDelta() !== null ? (string) $d->getCandidateDelta() : 'none';
            foreach ([&$bands[$band], &$byCand[$cd]] as &$b) {
                $b['scored']++;
                $b[strtolower((string) $d->getVerdict())]++;
                $b['realized'] = bcadd($b['realized'], (string) ($d->getRealizedDelta() ?? '0'), 8);
                $b['counterfactual'] = bcadd($b['counterfactual'], (string) ($d->getCounterfactualDelta() ?? '0'), 8);
            }
            unset($b);
        }

        return $this->ok([
            'run' => ['id' => $runId, 'label' => (string) $run->getLabel()],
            'decisions' => $decisions,
            'summary' => ['by_deploy_band' => $bands, 'by_candidate_delta' => array_filter($byCand, static fn ($b) => $b['scored'] > 0)],
            'note' => 'verdicts are re-evaluated hourly by the gtbot-refit cron once a decision has been '
                . 'applied for 6h; Superseded = replaced before it ever traded (no evidence); Worse = the geometry it '
                . 'replaced would have earned more on the same tape (counterfactual = sim(new) - sim(prev), USDT).',
        ]);
    }
}
