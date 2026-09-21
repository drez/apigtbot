<?php
namespace App\Mcp\Tools;

use ApiGoat\Sessions\AuthySession;
use App\BotDecisionQuery;
use App\BotEventQuery;
use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\DecisionScorer;
use App\Domains\Bot\DrawdownGuard;
use App\Domains\Bot\GridMath;
use App\Domains\Bot\MarketCollector;
use App\Domains\Bot\MarketStore;
use App\Domains\Bot\MechanicalRefit;
use App\Domains\Bot\OrderStore;
use App\Domains\Bot\RoutineBrief;
use App\Domains\Bot\SimWallet;
use App\Domains\Bot\TrendActivator;
use App\GridRun;
use App\GridRunQuery;
use App\TradeCycleQuery;

/**
 * gtbot_routine_brief — the hourly refit routine's ONE read. Every non-Done
 * run in a single compact payload (signal digest, regime + gates, geometry,
 * slice, candidate grid + deploy band, track record, flags/attention) plus
 * material_change vs. the fingerprint stored by the previous call (config
 * gtbot_routine_brief_last — the only thing this tool writes). Quiet hour
 * = one call + a one-line report. See App\Domains\Bot\RoutineBrief.
 */
class GtbotRoutineBriefTool extends AbstractGtbotBase
{
    public const FINGERPRINT_KEY = 'gtbot_routine_brief_last';
    public const QUIET_HOURS_KEY = 'gtbot_brief_max_quiet_hours';

    /**
     * @param ?\Closure $summaries fn(string $symbol): array  (tests inject; default MarketStore::summaries)
     * @param ?\Closure $candles   fn(string $symbol, string $tf): array
     * @param ?\Closure $price     fn(string $symbol): ?string
     */
    public function __construct(
        private ?\Closure $summaries = null,
        private ?\Closure $candles = null,
        private ?\Closure $price = null
    ) {
    }

    public function name(): string
    {
        return 'gtbot_routine_brief';
    }

    public function description(): string
    {
        return 'ONE compact read of every non-Done run for the hourly refit routine: signal digest (1h/4h/1d), '
            . 'regime class + gates, geometry/slice, a deterministic candidate grid + deploy band, track record, '
            . 'per-run flags/attention, and material_change since the previous brief. Start here; drill into '
            . 'gtbot_status/gtbot_market/gtbot_decisions only for flagged runs. '
            . 'shared.fleet = one entry per DECLARED arm (symbols with no run included). '
            . 'shared.episodes = the regime episodes running RIGHT NOW, one per symbol currently in TREND_UP: '
            . '{symbol, algo, verdict, since, engaged_pct_tw (time-weighted % of that symbol\'s slices actually '
            . 'invested, sampled every 15 min), samples}. A low engaged_pct_tw through a leg is capital the fleet '
            . 'declared but never put to work — closed episodes and what each captured vs HODL are in gtbot_pnl_report.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'run' => ['type' => 'integer', 'description' => 'Narrow the runs[] list to one run id (the fingerprint still covers all runs)'],
            ],
        ];
    }

    public function requiredRight(): ?array
    {
        return ['GridRun', 'r'];
    }

    protected function run(array $args, AuthySession $session): array
    {
        return $this->ok($this->build(isset($args['run']) ? (int) $args['run'] : null));
    }

    public function build(?int $onlyRun): array
    {
        $quietHours = (float) ((ConfigQuery::create()->findOneByConfig(self::QUIET_HOURS_KEY)?->getValue()) ?: RoutineBrief::DEFAULT_QUIET_HOURS);
        $prevRaw = (string) (ConfigQuery::create()->findOneByConfig(self::FINGERPRINT_KEY)?->getValue() ?? '');
        $prev = $prevRaw !== '' ? json_decode($prevRaw, true) : null;
        $prev = is_array($prev) && isset($prev['runs']) ? $prev : null;

        $eq = DrawdownGuard::equity();
        $floor = DrawdownGuard::floor($eq['source']); // the measured wallet's, not the paper seed
        $tripped = $floor !== null && bccomp($eq['equity'], $floor, 12) < 0;
        $trendRun = GridRunQuery::create()->filterByStatus('Live')->filterByAlgo('Trend')->orderByIdGridRun()->findOne();
        $lastTransition = $trendRun ? (int) (BotEventQuery::create()
            ->filterByIdGridRun((int) $trendRun->getIdGridRun())
            ->filterByKind([TrendActivator::KIND_ACTIVATE, TrendActivator::KIND_DEACTIVATE, TrendActivator::KIND_RELEASE], \Criteria::IN)
            ->orderByIdBotEvent(\Criteria::DESC)->findOne()?->getIdBotEvent() ?? 0) : 0;
        $lastAlert = (int) (BotEventQuery::create()->filterByLevel(['Alert', 'Error'], \Criteria::IN)->orderByIdBotEvent(\Criteria::DESC)->findOne()?->getIdBotEvent() ?? 0);

        $fp = ['drawdown_tripped' => $tripped, 'last_alert_id' => $lastAlert, 'last_trend_transition_id' => $lastTransition, 'runs' => []];
        $runs = [];
        $allocated = '0';
        $cache = [];
        foreach (GridRunQuery::create()->filterByStatus(['Draft', 'Done'], \Criteria::NOT_IN)->orderByIdGridRun()->find() as $run) {
            $id = (int) $run->getIdGridRun();
            $symbol = (string) $run->getSymbol();
            $algo = (string) ($run->getAlgo() ?: 'Grid');
            $allocated = bcadd($allocated, (string) $run->getBudgetQuote(), 8);
            if (!isset($cache[$symbol])) {
                $cache[$symbol] = ['s' => $this->summariesFor($symbol), 'p' => $this->priceFor($symbol)];
            }
            $s = $cache[$symbol]['s'];
            $price = $cache[$symbol]['p'];
            $pf = $price !== null ? (float) $price : null;
            $digest = RoutineBrief::digest($s, $price);
            $regime = RoutineBrief::regime($s, $pf);
            $pLow = (float) $run->getPLow();
            $pHigh = (float) $run->getPHigh();
            $pos = ($pHigh > $pLow && $pf !== null) ? round(($pf - $pLow) / ($pHigh - $pLow) * 100, 1) : null;
            $inRange = $pos !== null && $pos > 0 && $pos < 100;
            $deploy = (int) ($run->getDeployPct() ?? 100);
            $profile = (string) ($run->getProfile() ?: 'Balanced');
            $hb = $this->heartbeat($run);
            $refitPending = $this->refitPending($run);
            $store = new OrderStore($id, (string) $run->getRunUid(), (string) ($run->getLedgerResetAt('Y-m-d H:i:s') ?? ''), (bool) $run->getSimulated());
            $lastDecision = BotDecisionQuery::create()->filterByIdGridRun($id)->orderByIdBotDecision(\Criteria::DESC)->findOne();
            $hours = $lastDecision ? (time() - strtotime($lastDecision->getDateCreation('Y-m-d H:i:s'))) / 3600 : 999.0;
            $prevClass = $prev['runs'][$id]['class'] ?? null;
            $geomHash = substr(md5(implode('|', [(string) $run->getPLow(), (string) $run->getPHigh(), (int) $run->getNLevels(), (string) $run->getSpacing()])), 0, 8);
            $slice = bcadd((string) $run->getBudgetQuote(), '0', 0);

            $fp['runs'][$id] = [
                'class' => $regime['class'], 'in_range' => $inRange, 'pos_bucket' => RoutineBrief::posBucket($pos),
                'deploy' => $deploy, 'slice' => $slice, 'profile' => $profile, 'geom' => $geomHash,
                'kill' => (bool) $run->getKillSwitch(), 'hb_stale' => (bool) $hb['stale'], 'refit_pending' => $refitPending,
            ];
            $geometry = [
                'p_low' => bcadd((string) $run->getPLow(), '0', 2), 'p_high' => bcadd((string) $run->getPHigh(), '0', 2),
                'n' => (int) $run->getNLevels(), 'spacing_pct' => $this->spacingPct($run), 'pos_pct' => $pos, 'in_range' => $inRange,
            ];
            $entry = [
                'id' => $id, 'label' => (string) $run->getLabel(), 'symbol' => $symbol, 'algo' => $algo,
                'status' => (string) $run->getStatus(), 'profile' => $profile, 'simulated' => (bool) $run->getSimulated(),
                'flags' => [], 'attention' => false,
                'slice' => $slice, 'deploy_pct' => $deploy,
                'floor_slice' => TrendActivator::nominalFloor($run),
                'invested' => bcadd($store->investedQuote(), '0', 2),
                'pnl' => $this->pnl($id, (bool) $run->getSimulated()),
                'geometry' => $geometry,
                'signal' => $digest,
                'regime' => $regime,
                'candidate' => $algo === 'Trend' ? null
                    : RoutineBrief::candidate($this->candlesFor($symbol, '4h'), (string) $run->getFeePct(), (string) $run->getBudgetQuote(), $pf, $profile, $regime, $deploy),
                'track' => $this->track($id),
                'trend' => $algo === 'Trend' ? $this->trendBlock($run) : null,
            ];
            $fl = RoutineBrief::flags([
                'algo' => $algo, 'kill' => (bool) $run->getKillSwitch(), 'hb_stale' => (bool) $hb['stale'], 'held' => (bool) $hb['held'],
                'refit_pending' => $refitPending, 'geometry' => $geometry, 'deploy_pct' => $deploy, 'regime' => $regime,
                'signal' => $digest, 'hours_since_decision' => $hours, 'quiet_hours' => $quietHours,
                'drawdown_tripped' => $tripped,
                'trend_transition_since_last' => $algo === 'Trend' && $prev !== null && $lastTransition !== (int) ($prev['last_trend_transition_id'] ?? 0),
                'regime_changed' => $prevClass !== null && $prevClass !== $regime['class'],
            ]);
            $entry['flags'] = $fl['flags'];
            $entry['attention'] = $fl['attention'];
            if ($onlyRun === null || $onlyRun === $id) {
                $runs[] = $entry;
            }
        }

        // long-horizon outlook: context only (see RoutineBrief::diff); a
        // failure here must never cost the routine its brief
        try {
            $outlook = \App\Domains\Bot\MarketOutlookStore::brief(\App\Domains\Bot\MarketCollector::watchedSymbols(), time());
        } catch (\Throwable $e) {
            error_log('routine brief outlook: ' . $e->getMessage());
            $outlook = ['note' => \App\Domains\Bot\MarketOutlookStore::NOTE, 'symbols' => []];
        }
        $fp['outlook'] = array_map(static fn (array $o) => $o['verdict'], $outlook['symbols']);

        $diff = RoutineBrief::diff($prev, $fp);
        $c = ConfigQuery::create()->findOneByConfig(self::FINGERPRINT_KEY) ?? (new Config())->setConfig(self::FINGERPRINT_KEY);
        $c->setValue(json_encode($fp, JSON_UNESCAPED_SLASHES));
        $c->save();

        return [
            'at' => gmdate('Y-m-d H:i') . 'Z',
            'shared' => [
                'budget' => \App\Domains\Bot\BudgetPool::cap(),
                'budget_fixed' => SimWallet::sharedBudget(),
                'use_all_funds' => \App\Domains\Bot\BudgetPool::useAllFunds(),
                // What the MACHINE may plan against: budget minus
                // gtbot_pool_reserve_pct. BudgetGuard still refuses only past
                // `budget`, so a fleet allocated between the two is fine — it
                // is simply not raised again (BudgetPool::reservePct).
                'budget_allocatable' => \App\Domains\Bot\BudgetPool::allocatable(),
                'pool_reserve_pct' => \App\Domains\Bot\BudgetPool::reservePct(),
                // mechanical = the cron owns grid geometry; gtbot_set_grid is deploy_pct-only
                'grid_refit_mode' => MechanicalRefit::mode(),
                'allocated' => bcadd($allocated, '0', 0),
                'equity' => bcadd($eq['equity'], '0', 2),
                'drawdown' => ['floor' => $floor !== null ? bcadd($floor, '0', 2) : null, 'tripped' => $tripped],
                // One entry per DECLARED slot — the symbols with NO arm read
                // as plainly as the ones with one (that hole is what let BNB
                // run +5.7% unremarked over 2026-09-15..18).
                'fleet' => \App\Domains\Bot\FleetSlots::brief(),
                // The legs running right now and how much of the pool has
                // been working through each — the number that turns "the arm
                // sat out that move" from an after-the-fact chart read into
                // something the routine can act on while the leg is on.
                'episodes' => \App\Domains\Bot\RegimeEpisodes::brief(),
                // 1d + 1w agreement per symbol, cycle position and the live
                // scored record. DETECTION, not forecast — read `note`.
                'outlook' => $outlook,
                // kept one release for the routine prompt still reading it;
                // shared.fleet is the replacement.
                'trend_arm' => $trendRun ? [
                    'run' => (int) $trendRun->getIdGridRun(),
                    'state' => TrendActivator::state((int) $trendRun->getIdGridRun()),
                    'last_transition_id' => $lastTransition,
                ] : null,
            ],
            'material_change' => $diff['material'],
            'changes' => $diff['changes'],
            'runs' => $runs,
        ];
    }

    // ── data sources (injectable) ───────────────────────────────────────

    private function summariesFor(string $symbol): array
    {
        if ($this->summaries !== null) {
            return ($this->summaries)($symbol);
        }
        try {
            MarketCollector::refreshIfStale($symbol, MarketCollector::analysisGateway());
        } catch (\Throwable $e) {
            error_log('gtbot_routine_brief refresh failed: ' . $e->getMessage());
        }
        return MarketStore::summaries($symbol);
    }

    private function candlesFor(string $symbol, string $tf): array
    {
        return $this->candles !== null ? ($this->candles)($symbol, $tf) : MarketStore::candles($symbol, $tf);
    }

    private function priceFor(string $symbol): ?string
    {
        if ($this->price !== null) {
            return ($this->price)($symbol);
        }
        try {
            return MarketCollector::analysisGateway()->tickerPrice($symbol);
        } catch (\Throwable) {
            // fall back to the freshest stored summary price
            $s = MarketStore::summaries($symbol);
            foreach (['1h', '4h', '1d'] as $tf) {
                if (isset($s[$tf]['price'])) {
                    return (string) $s[$tf]['price'];
                }
            }
            return null;
        }
    }

    // ── per-run helpers ─────────────────────────────────────────────────

    private function refitPending(GridRun $run): bool
    {
        $applied = json_decode((string) ($run->getAppliedGeometry() ?? ''), true);
        if (!is_array($applied)) {
            return false;
        }
        return bccomp((string) ($applied['p_low'] ?? '0'), (string) $run->getPLow(), 8) !== 0
            || bccomp((string) ($applied['p_high'] ?? '0'), (string) $run->getPHigh(), 8) !== 0
            || (int) ($applied['n_levels'] ?? 0) !== (int) $run->getNLevels()
            || (string) ($applied['spacing'] ?? '') !== (string) $run->getSpacing()
            || (int) ($applied['deploy_pct'] ?? 100) !== (int) ($run->getDeployPct() ?? 100);
    }

    private function spacingPct(GridRun $run): ?string
    {
        try {
            $frac = GridMath::spacingPct((string) $run->getPLow(), (string) $run->getPHigh(), max(2, (int) $run->getNLevels()), (string) ($run->getSpacing() ?: 'Geometric'));
            return bcmul($frac, '100', 2);
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array{d7: string, total: string, cycles_d7: int} */
    private function pnl(int $runId, bool $simulated): array
    {
        $since = gmdate('Y-m-d H:i:s', time() - 7 * 86400);
        $d7 = '0';
        $n7 = 0;
        $total = '0';
        foreach (TradeCycleQuery::create()->filterByIdGridRun($runId)->filterBySimulated($simulated)->find() as $c) {
            $pnl = (string) $c->getRealizedPnl();
            $total = bcadd($total, $pnl, 8);
            if ($c->getDateCreation('Y-m-d H:i:s') >= $since) {
                $d7 = bcadd($d7, $pnl, 8);
                $n7++;
            }
        }
        return ['d7' => bcadd($d7, '0', 2), 'total' => bcadd($total, '0', 2), 'cycles_d7' => $n7];
    }

    /** compact scoreboard: scored/win/loss/realized per deploy band + last 3 verdicts */
    private function track(int $runId): array
    {
        $bands = ['10-40' => [0, 0, 0, '0'], '41-70' => [0, 0, 0, '0'], '71-100' => [0, 0, 0, '0']];
        foreach (BotDecisionQuery::create()
            ->filterByIdGridRun($runId)
            ->filterByEvalStatus('Scored')
            ->filterByVerdict('Superseded', \Criteria::NOT_EQUAL)
            ->find() as $d) {
            $pct = $d->getDeployPct() !== null ? (int) $d->getDeployPct() : 100;
            $band = $pct <= 40 ? '10-40' : ($pct <= 70 ? '41-70' : '71-100');
            $bands[$band][0]++;
            $v = (string) $d->getVerdict();
            if ($v === 'Win') {
                $bands[$band][1]++;
            } elseif ($v === 'Loss' || $v === 'Worse') {
                $bands[$band][2]++;
            }
            $bands[$band][3] = bcadd($bands[$band][3], (string) ($d->getRealizedDelta() ?? '0'), 8);
        }
        $out = [];
        foreach ($bands as $k => [$n, $w, $l, $r]) {
            if ($n > 0) {
                $out[$k] = ['n' => $n, 'win' => $w, 'loss' => $l, 'realized' => bcadd($r, '0', 2)];
            }
        }
        $last = [];
        foreach (DecisionScorer::trackRecord($runId, 3) as $t) {
            $last[] = ['at' => $t['at'], 'deploy' => $t['deploy_pct'], 'verdict' => $t['verdict'], 'realized' => bcadd((string) $t['realized_after'], '0', 2),
                'vs_prev' => $t['counterfactual'] !== null ? bcadd($t['counterfactual'], '0', 2) : null, 'cand' => $t['candidate_delta']];
        }
        return ['by_deploy_band' => $out, 'last3' => $last];
    }

    private function trendBlock(GridRun $run): array
    {
        $s = json_decode((string) ($run->getEngineState() ?? ''), true);
        $s = is_array($s) ? $s : [];
        return [
            'entry' => isset($s['entry']) ? bcadd((string) $s['entry'], '0', 2) : null,
            'qty' => $s['qty'] ?? null,
            'stop' => isset($s['stop']) ? bcadd((string) $s['stop'], '0', 2) : null,
            'state' => TrendActivator::state((int) $run->getIdGridRun()),
        ];
    }
}
