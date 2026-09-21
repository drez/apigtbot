<?php

namespace App\Domains\Bot;

use App\BotDecision;
use App\BotDecisionQuery;
use App\GridRun;
use App\TradeCycleQuery;

/**
 * Scores past refit decisions so the routine can learn from its own track
 * record. A decision is evaluated after EVAL_AFTER: how many cycles and how
 * much realized P/L the new grid produced, and whether price stayed inside
 * its range. Verdict: Win (harvested P/L), Flat (nothing yet but grid still
 * live/in range), Loss (nothing harvested AND price left the chosen range),
 * Worse (whatever it harvested, leaving the previous geometry in place would
 * have earned more on the same tape — counterfactual_delta, via Backtester).
 */
final class DecisionScorer
{
    public const EVAL_AFTER = 6 * 3600; // seconds a decision gets to prove itself

    /**
     * Pure verdict rule (unit-tested). $counterfactual = sim(new) − sim(prev)
     * on the post-apply tape (see counterfactual()); when the previous
     * geometry would have out-earned this one by more than $tolerance the
     * decision is 'Worse' — a Loss stays a Loss (it is already the worse
     * reading), null keeps the original absolute rule.
     */
    public static function verdict(string $realizedDelta, int $cyclesDelta, bool $priceInRange, ?string $counterfactual = null, string $tolerance = '0'): string
    {
        if ($cyclesDelta === 0 && !$priceInRange && bccomp($realizedDelta, '0', 8) <= 0) {
            return 'Loss';
        }
        if ($counterfactual !== null && bccomp($counterfactual, bcmul('-1', $tolerance, 8), 8) < 0) {
            return 'Worse';
        }
        if (bccomp($realizedDelta, '0', 8) > 0) {
            return 'Win';
        }
        return 'Flat';
    }

    /**
     * sim(new) − sim(prev) realized P/L over the same tape through the real
     * Backtester (perfect fills on both sides, so the bias cancels). null
     * when there is no previous geometry to compare against or the tape is
     * too short to mean anything.
     *
     * @param string[] $tape closes since apply; index 0 = price at apply
     */
    public static function counterfactual(array $newCfg, ?array $prevCfg, array $tape): ?string
    {
        if ($prevCfg === null || count($tape) < 2) {
            return null;
        }
        try {
            $new = Backtester::run($newCfg, $tape)['realized_pnl'];
            $prev = Backtester::run($prevCfg, $tape)['realized_pnl'];
        } catch (\Throwable) {
            return null;
        }
        return bcsub((string) $new, (string) $prev, 8);
    }

    /** grid_run → Backtester config for one decision's geometry. */
    public static function simConfig(GridRun $run, string $pLow, string $pHigh, int $n, ?int $deployPct): array
    {
        $slice = (string) $run->getBudgetQuote();
        $budget = $deployPct !== null ? bcdiv(bcmul($slice, (string) $deployPct, 8), '100', 8) : $slice;
        return [
            'p_low' => $pLow, 'p_high' => $pHigh, 'n_levels' => $n,
            'spacing' => (string) ($run->getSpacing() ?: 'Geometric'), 'allocation' => (string) ($run->getAllocation() ?: 'EqualQuote'),
            'budget_quote' => $budget, 'fee_pct' => (string) $run->getFeePct(), 'min_notional' => '5',
            'max_position_quote' => $slice, 'max_order_quote' => $slice, 'daily_loss_limit_quote' => $slice,
            'breakout_buffer_pct' => (string) ($run->getBreakoutBufferPct() ?: '0.02'), 'breakout_policy' => 'HaltAndHold', 'max_open_orders' => 60,
        ];
    }

    /**
     * Price tape since $appliedTs from the stored 1h candles (newest = the
     * summary's computed_at), prefixed with the decision's price. Candles
     * carry no timestamps, so the window is counted back from computed_at.
     *
     * Each bar is expanded to its intra-bar path (see barPath) so the
     * Backtester can fill rungs the bar's high/low actually touched: a
     * closes-only tape over the ~6 bars a decision gets never completed a
     * cycle for either geometry, so every counterfactual on prod scored
     * exactly 0.00000000 and the 'Worse' verdict could not fire (2026-09-05).
     *
     * @return string[]
     */
    public static function tapeSince(string $symbol, int $appliedTs, ?string $priceAt): array
    {
        $row = \App\MarketSummaryQuery::create()->filterBySymbol($symbol)->filterByTf('1h')->findOne();
        if (!$row || !$row->getComputedAt()) {
            return [];
        }
        $hours = (int) floor(((int) $row->getComputedAt('U') - $appliedTs) / 3600);
        if ($hours < 1) {
            return [];
        }
        $candles = MarketStore::candles($symbol, '1h');
        $candles = array_slice($candles, -min($hours, count($candles)));
        return self::barPath($candles, $priceAt !== null && bccomp($priceAt, '0', 8) > 0 ? $priceAt : null);
    }

    /**
     * Expand OHLC-less bars ({high, low, close}) into a price path: from the
     * previous close, an UP bar visits low → high → close, a DOWN bar high →
     * low → close (the usual "wick against the move first" convention). Both
     * geometries replay the same path, so the optimism of perfect fills
     * cancels in the difference. Index 0 = $priceAt when given.
     *
     * @param array<int, array{high:string|float, low:string|float, close:string|float}> $candles
     * @return string[]
     */
    public static function barPath(array $candles, ?string $priceAt): array
    {
        $path = [];
        $prev = $priceAt;
        if ($priceAt !== null) {
            $path[] = $priceAt;
        }
        foreach ($candles as $c) {
            $high = (string) $c['high'];
            $low = (string) $c['low'];
            $close = (string) $c['close'];
            $up = $prev === null || bccomp($close, $prev, 8) >= 0;
            $path[] = $up ? $low : $high;
            $path[] = $up ? $high : $low;
            $path[] = $close;
            $prev = $close;
        }
        return $path;
    }

    /**
     * Record a refit decision (called by gtbot_set_grid and the cron).
     *
     * @param array{requested?:array, clamps?:ClampTrail|array, brief?:array, candidate_delta?:string} $receipt
     *        optional decision receipt: what was requested before the gates,
     *        the clamp trail, the brief snapshot the decider saw, and whether
     *        the applied geometry followed the deterministic candidate.
     */
    public static function record(GridRun $run, string $source, string $pLow, string $pHigh, int $n, string $reason, ?string $priceAt, ?int $deployPct = null, array $receipt = []): BotDecision
    {
        $realized = '0';
        foreach (TradeCycleQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterBySimulated((bool) $run->getSimulated())
            ->find() as $c) {
            $realized = bcadd($realized, (string) $c->getRealizedPnl(), 12);
        }
        $d = new BotDecision();
        $d->setIdGridRun((int) $run->getIdGridRun());
        $d->setSource($source);
        $d->setPLow($pLow);
        $d->setPHigh($pHigh);
        $d->setNLevels($n);
        $d->setDeployPct($deployPct);
        $d->setReason(mb_substr($reason, 0, 500));
        $d->setPriceAt($priceAt);
        $d->setRealizedBefore($realized);
        $d->setEvalStatus('Pending');
        if (isset($receipt['requested'])) {
            $d->setRequestedJson(json_encode($receipt['requested'], JSON_UNESCAPED_SLASHES));
        }
        if (isset($receipt['clamps'])) {
            $c = $receipt['clamps'];
            $d->setClampsJson($c instanceof ClampTrail ? $c->toJson() : json_encode($c, JSON_UNESCAPED_SLASHES));
        }
        if (isset($receipt['brief'])) {
            $d->setBriefJson(json_encode($receipt['brief'], JSON_UNESCAPED_SLASHES));
        }
        if (isset($receipt['candidate_delta'])) {
            $d->setCandidateDelta($receipt['candidate_delta']);
        }
        $d->save();
        return $d;
    }

    /**
     * Score every Pending decision that is ripe. Each run is judged against
     * ITS OWN last price (a single global price scored BNB grids against the
     * BTC price — bogus Losses fed back into the routine). The evaluation
     * clock starts when the daemon APPLIED the geometry (applied_at), not when
     * the decision was written — a decision that never took effect can't earn
     * Win/Loss from the old grid's trades: once replaced by a newer decision
     * it is scored Superseded; while still the queued geometry it stays
     * Pending. Returns the number scored.
     */
    public static function scorePending(): int
    {
        $cutoffTs = time() - self::EVAL_AFTER;
        $byRun = [];
        foreach (BotDecisionQuery::create()
            ->filterByEvalStatus('Pending')
            ->orderByIdBotDecision()
            ->find() as $d) {
            $byRun[(int) $d->getIdGridRun()][] = $d;
        }
        $n = 0;
        foreach ($byRun as $runId => $decisions) {
            $run = \App\GridRunQuery::create()->findPk($runId);
            $price = ($run && $run->getLastPrice() !== null) ? (string) $run->getLastPrice() : null;
            $latestId = 0;
            foreach ($decisions as $d) {
                $latestId = max($latestId, (int) $d->getIdBotDecision());
            }
            foreach ($decisions as $d) {
                $appliedTs = $d->getAppliedAt('U');
                if ($appliedTs === null) {
                    if ((int) $d->getIdBotDecision() === $latestId) {
                        continue; // still the queued geometry — the daemon may yet apply it
                    }
                    $d->setCyclesDelta(0);
                    $d->setRealizedDelta('0');
                    $d->setVerdict('Superseded');
                    $d->setEvalStatus('Scored');
                    $d->setEvalAt(date('Y-m-d H:i:s'));
                    $d->save();
                    $n++;
                    continue;
                }
                if ((int) $appliedTs > $cutoffTs || $price === null) {
                    continue; // still proving itself (clock runs from apply time)
                }
                $realizedNow = '0';
                $cyclesAfter = 0;
                foreach (TradeCycleQuery::create()
                    ->filterByIdGridRun($runId)
                    ->filterBySimulated((bool) $run->getSimulated())
                    ->filterByDateCreation(['min' => date('Y-m-d H:i:s', (int) $appliedTs)])
                    ->find() as $c) {
                    $realizedNow = bcadd($realizedNow, (string) $c->getRealizedPnl(), 12);
                    $cyclesAfter++;
                }
                $inRange = bccomp($price, (string) $d->getPLow(), 8) > 0
                    && bccomp($price, (string) $d->getPHigh(), 8) < 0;
                $movePct = null;
                if ($d->getPriceAt() && bccomp((string) $d->getPriceAt(), '0', 8) > 0) {
                    $movePct = bcmul(bcdiv(bcsub($price, (string) $d->getPriceAt(), 12), (string) $d->getPriceAt(), 12), '100', 4);
                }
                // counterfactual: the geometry this decision replaced (the
                // newest OLDER decision that was actually applied), replayed
                // on the same post-apply tape
                $prevD = BotDecisionQuery::create()
                    ->filterByIdGridRun($runId)
                    ->filterByIdBotDecision((int) $d->getIdBotDecision(), \Criteria::LESS_THAN)
                    ->filterByAppliedAt(null, \Criteria::ISNOTNULL)
                    ->orderByIdBotDecision(\Criteria::DESC)
                    ->findOne();
                $cf = null;
                if ($prevD !== null) {
                    $tape = self::tapeSince((string) $run->getSymbol(), (int) $appliedTs, $d->getPriceAt() !== null ? (string) $d->getPriceAt() : null);
                    $cf = self::counterfactual(
                        self::simConfig($run, (string) $d->getPLow(), (string) $d->getPHigh(), (int) $d->getNLevels(), $d->getDeployPct() !== null ? (int) $d->getDeployPct() : null),
                        self::simConfig($run, (string) $prevD->getPLow(), (string) $prevD->getPHigh(), (int) $prevD->getNLevels(), $prevD->getDeployPct() !== null ? (int) $prevD->getDeployPct() : null),
                        $tape
                    );
                }
                // tolerance: one round-trip fee on the slice — below that the sims are noise
                $tol = bcmul(bcmul((string) $run->getBudgetQuote(), (string) $run->getFeePct(), 8), '2', 8);
                $d->setCyclesDelta($cyclesAfter);
                $d->setRealizedDelta($realizedNow);
                $d->setPriceMovePct($movePct);
                $d->setCounterfactualDelta($cf);
                $d->setVerdict(self::verdict($realizedNow, $cyclesAfter, $inRange, $cf, $tol));
                $d->setEvalStatus('Scored');
                $d->setEvalAt(date('Y-m-d H:i:s'));
                $d->save();
                $n++;
            }
        }
        return $n;
    }

    /** Last N scored decisions, newest first — the routine's track record. */
    public static function trackRecord(int $runId, int $limit = 5): array
    {
        $out = [];
        foreach (BotDecisionQuery::create()
            ->filterByIdGridRun($runId)
            ->filterByEvalStatus('Scored')
            ->orderByIdBotDecision(\Criteria::DESC)
            ->limit($limit)
            ->find() as $d) {
            $out[] = [
                'at' => $d->getDateCreation('Y-m-d H:i'),
                'source' => (string) $d->getSource(),
                'applied_at' => $d->getAppliedAt('Y-m-d H:i'),
                'range' => [(string) $d->getPLow(), (string) $d->getPHigh()],
                'n_levels' => (int) $d->getNLevels(),
                'deploy_pct' => $d->getDeployPct() !== null ? (int) $d->getDeployPct() : null,
                'reason' => (string) $d->getReason(),
                'verdict' => (string) $d->getVerdict(),
                'cycles_after' => (int) $d->getCyclesDelta(),
                'realized_after' => (string) $d->getRealizedDelta(),
                'price_move_pct' => $d->getPriceMovePct() !== null ? (float) $d->getPriceMovePct() : null,
                'counterfactual' => $d->getCounterfactualDelta() !== null ? (string) $d->getCounterfactualDelta() : null,
                'candidate_delta' => $d->getCandidateDelta() !== null ? (string) $d->getCandidateDelta() : null,
            ];
        }
        return $out;
    }
}
