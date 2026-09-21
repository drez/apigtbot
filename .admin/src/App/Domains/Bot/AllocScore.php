<?php

namespace App\Domains\Bot;

use App\GridRun;
use App\TradeCycleQuery;

/**
 * Capital efficiency: net realized P&L per active day per 1000 USDT of slice.
 *
 * This is the number the allocator ranks runs by, and it is deliberately the
 * SAME formula GtbotPnlReportTool has always reported as `net_per_1k_day` —
 * that tool now calls in here so the definition lives in one place.
 *
 * Why not the alternatives:
 *  - cycle COUNT rewards fee churn (the PnL tool's own comment says so);
 *  - GROSS P&L does the same, since trade_cycle.realized_pnl is already net of
 *    fees (LevelStateMachine books it as gross − fees);
 *  - UNREALIZED mark-to-market would make the score chase drawdowns — a bag
 *    that has not sold yet is not evidence of edge.
 *
 * Known bias, stated plainly: the denominator is the run's CURRENT slice, not
 * its time-weighted slice over the window. A run whose slice was just raised
 * therefore scores low for a while. Three things keep that harmless — a run
 * needs MIN_CYCLES before it is eligible at all, each drift pass is capped to a
 * few percent of the pool, and every pass journals its slice map, so a
 * time-weighted denominator is a drop-in once that history exists. It is
 * deliberately NOT the first version: it needs history the system has not
 * collected yet.
 */
final class AllocScore
{
    /** below this, a run has not earned an opinion */
    public const MIN_CYCLES = 10;

    private const SCALE = 12;

    /**
     * Score one run over a window.
     *
     * @return array{net: string, fees: string, cycles: int, active_days: int,
     *               per_1k_day: ?string, eligible: bool, why: ?string}
     */
    public static function score(GridRun $run, int $days, ?string $now = null): array
    {
        $since = date('Y-m-d H:i:s', strtotime(($now ?? date('Y-m-d H:i:s')) . ' -' . max(1, $days) . ' days'));

        $rows = TradeCycleQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterBySimulated((bool) $run->getSimulated())
            ->filterByDateCreation(['min' => $since])
            ->select(['RealizedPnl', 'FeesTotal', 'DateCreation'])
            ->find();

        $net = '0';
        $fees = '0';
        $cycles = 0;
        $byDay = [];
        foreach ($rows as $r) {
            $cycles++;
            $net = bcadd($net, (string) ($r['RealizedPnl'] ?? '0'), self::SCALE);
            $fees = bcadd($fees, (string) ($r['FeesTotal'] ?? '0'), self::SCALE);
            $day = substr((string) ($r['DateCreation'] ?? ''), 0, 10);
            if ($day !== '') {
                $byDay[$day] = true;
            }
        }
        $activeDays = max(1, count($byDay));
        $slice = (string) $run->getBudgetQuote();

        $per1k = null;
        if (bccomp($slice, '0', 8) > 0) {
            // net / activeDays / slice * 1000, all in bcmath
            $per1k = bcdiv(bcmul($net, '1000', self::SCALE), bcmul($slice, (string) $activeDays, self::SCALE), 6);
        }

        $why = null;
        if ($cycles < self::MIN_CYCLES) {
            $why = sprintf('only %d cycle(s) in %dd — needs %d', $cycles, $days, self::MIN_CYCLES);
        } elseif ($per1k === null) {
            $why = 'no slice to measure against';
        }

        return [
            'net' => $net,
            'fees' => $fees,
            'cycles' => $cycles,
            'active_days' => $activeDays,
            'per_1k_day' => $per1k,
            'eligible' => $why === null,
            'why' => $why,
        ];
    }

    /** Window length from config, defaulting to 14 days. */
    public static function windowDays(): int
    {
        $v = \App\ConfigQuery::create()->findOneByConfig('gtbot_alloc_window_days')?->getValue();
        return $v !== null && is_numeric($v) && (int) $v > 0 ? (int) $v : 14;
    }

    /**
     * Rank eligible runs against the slice-weighted mean and turn the distance
     * into signed pressure.
     *
     * A run within $deadbandPct of the mean is left alone — otherwise slices
     * would jitter forever around an average they are already sitting on.
     * Pressure is weighted by BOTH the distance from the mean and the run's
     * current slice, so a big slice that is underperforming gives up more than
     * a small one that is equally bad.
     *
     * Pure: it decides who should give and who should get, not how much can
     * actually move. The caps and floors are the caller's job.
     *
     * @param array<int, array{per_1k_day: ?string, eligible: bool}> $scores
     * @param array<int, string> $slices  id => current slice
     * @return array{mean: ?string, donors: array<int, string>, recipients: array<int, string>}
     */
    public static function pressure(array $scores, array $slices, string $deadbandPct = '10'): array
    {
        $weighted = '0';
        $sliceSum = '0';
        $eligible = [];
        foreach ($scores as $id => $sc) {
            if (!($sc['eligible'] ?? false) || ($sc['per_1k_day'] ?? null) === null) {
                continue;
            }
            $slice = (string) ($slices[$id] ?? '0');
            if (bccomp($slice, '0', self::SCALE) <= 0) {
                continue;
            }
            $eligible[$id] = (string) $sc['per_1k_day'];
            $weighted = bcadd($weighted, bcmul($eligible[$id], $slice, self::SCALE), self::SCALE);
            $sliceSum = bcadd($sliceSum, $slice, self::SCALE);
        }
        if (count($eligible) < 2 || bccomp($sliceSum, '0', self::SCALE) <= 0) {
            // one opinion is not a ranking
            return ['mean' => null, 'donors' => [], 'recipients' => []];
        }

        $mean = bcdiv($weighted, $sliceSum, self::SCALE);
        $band = bcdiv(bcmul(self::abs($mean), $deadbandPct, self::SCALE), '100', self::SCALE);

        $donors = [];
        $recipients = [];
        foreach ($eligible as $id => $score) {
            $gap = bcsub($score, $mean, self::SCALE);
            if (bccomp(self::abs($gap), $band, self::SCALE) <= 0) {
                continue; // close enough to the mean to leave alone
            }
            $weight = bcmul(self::abs($gap), (string) $slices[$id], self::SCALE);
            if (bccomp($gap, '0', self::SCALE) < 0) {
                $donors[$id] = $weight;
            } else {
                $recipients[$id] = $weight;
            }
        }
        return ['mean' => $mean, 'donors' => $donors, 'recipients' => $recipients];
    }

    private static function abs(string $v): string
    {
        return bccomp($v, '0', self::SCALE) < 0 ? bcsub('0', $v, self::SCALE) : $v;
    }
}

