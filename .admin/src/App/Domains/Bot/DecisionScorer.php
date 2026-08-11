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
 * live/in range), Loss (nothing harvested AND price left the chosen range).
 */
final class DecisionScorer
{
    public const EVAL_AFTER = 6 * 3600; // seconds a decision gets to prove itself

    /** Pure verdict rule (unit-tested). */
    public static function verdict(string $realizedDelta, int $cyclesDelta, bool $priceInRange): string
    {
        if (bccomp($realizedDelta, '0', 8) > 0) {
            return 'Win';
        }
        if ($cyclesDelta === 0 && !$priceInRange) {
            return 'Loss';
        }
        return 'Flat';
    }

    /** Record a refit decision (called by gtbot_set_grid and the cron). */
    public static function record(GridRun $run, string $source, string $pLow, string $pHigh, int $n, string $reason, ?string $priceAt, ?int $deployPct = null): BotDecision
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
                $d->setCyclesDelta($cyclesAfter);
                $d->setRealizedDelta($realizedNow);
                $d->setPriceMovePct($movePct);
                $d->setVerdict(self::verdict($realizedNow, $cyclesAfter, $inRange));
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
            ];
        }
        return $out;
    }
}
