<?php
namespace App\Mcp\Tools;

use ApiGoat\Sessions\AuthySession;
use App\TradeCycleQuery;

class GtbotPnlReportTool extends AbstractGtbotBase
{
    public function name(): string
    {
        return 'gtbot_pnl_report';
    }

    public function description(): string
    {
        return 'Read-only PnL report for a grid run: realized PnL, fees and cycle count per day, '
            . 'plus totals and per-level utilization. Defaults to the most recent non-Done run. wallet = whole-wallet '
            . 'NAV over the window vs HODL (BTC) and USDT (never deployed) benchmarks + max drawdown (from wallet_nav, hourly). '
            . 'episodes = the last 10 CLOSED regime episodes, fleet-wide (not per run), newest first: '
            . '{symbol, verdict, opened, closed, price_open, price_close, engaged_pct_tw, samples, realized, mtm_close, '
            . 'hodl_pct (the move over that same window), captured_pct = (realized + mtm) / (target slice x hodl move) x 100 — '
            . '100 means the arm made what holding the whole slice would have; null when the episode closed lower than it opened}.';
    }

    public function inputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'run' => ['type' => ['integer', 'string'], 'description' => 'id_grid_run or exact label; omit for the latest run'],
                'days' => ['type' => 'integer', 'description' => 'Look-back window in days (default 30)'],
                'mode' => ['type' => 'string', 'enum' => ['sim', 'real', 'all'], 'description' => "Which stat set: sim, real, or all; default = the run's current mode"],
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
        $days = max(1, min(365, (int) ($args['days'] ?? 30)));
        $since = date('Y-m-d 00:00:00', strtotime("-$days days"));

        $mode = (string) ($args['mode'] ?? '');
        $modeFilter = function ($q) use ($mode, $run) {
            return match ($mode) {
                'sim' => $q->filterBySimulated(true),
                'real' => $q->filterBySimulated(false),
                'all' => $q,
                default => $q->filterBySimulated((bool) $run->getSimulated()),
            };
        };
        $orderStoreSimulated = $mode === 'all' ? null : ($mode === 'sim' ? true : ($mode === 'real' ? false : (bool) $run->getSimulated()));

        $byDay = [];
        $byLevel = [];
        $totalPnl = '0';
        $totalFees = '0';
        $cycles = $modeFilter(TradeCycleQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByDateCreation(['min' => $since]))
            ->orderByIdTradeCycle()
            ->find();
        foreach ($cycles as $c) {
            $day = $c->getDateCreation('Y-m-d');
            $byDay[$day] ??= ['realized_pnl' => '0', 'fees' => '0', 'cycles' => 0];
            $byDay[$day]['realized_pnl'] = bcadd($byDay[$day]['realized_pnl'], (string) $c->getRealizedPnl(), 12);
            $byDay[$day]['fees'] = bcadd($byDay[$day]['fees'], (string) $c->getFeesTotal(), 12);
            $byDay[$day]['cycles']++;
            $lvl = (int) $c->getLevelIdx();
            $byLevel[$lvl] = ($byLevel[$lvl] ?? 0) + 1;
            $totalPnl = bcadd($totalPnl, (string) $c->getRealizedPnl(), 12);
            $totalFees = bcadd($totalFees, (string) $c->getFeesTotal(), 12);
        }
        ksort($byLevel);

        // performance metrics
        $activeDays = max(1, count($byDay));
        $gross = bcadd($totalPnl, $totalFees, 12);
        $feesPctOfGross = bccomp($gross, '0', 12) > 0
            ? (float) bcmul(bcdiv($totalFees, $gross, 8), '100', 2)
            : null;
        // equity curve: cumulative realized by day + current unrealized mark
        $equityCurve = [];
        $cum = '0';
        foreach ($byDay as $day => $d) {
            $cum = bcadd($cum, $d['realized_pnl'], 12);
            $equityCurve[$day] = $cum;
        }
        // mark-to-market of what the CURRENT ledger era holds: filled buys
        // minus every cycle since the epoch (NOT just the cycles inside the
        // report window — that over-counted inventory by every cycle older
        // than the window and printed thousands of phantom USDT)
        $unrealized = null;
        if ($run->getLastPrice()) {
            $store = new \App\Domains\Bot\OrderStore((int) $run->getIdGridRun(), (string) $run->getRunUid(), $run->getLedgerResetAt('Y-m-d H:i:s'), $orderStoreSimulated);
            $inv = $store->trackedInventory();
            if (bccomp($inv, '0', 8) > 0) {
                $unrealized = bcsub(bcmul($inv, (string) $run->getLastPrice(), 12), $store->investedQuote(), 12);
            } else {
                $unrealized = '0';
            }
        }
        // decision track record (win rate of scored refits)
        $verdicts = ['Win' => 0, 'Flat' => 0, 'Loss' => 0, 'Worse' => 0];
        foreach (\App\Domains\Bot\DecisionScorer::trackRecord((int) $run->getIdGridRun(), 50) as $d) {
            $verdicts[$d['verdict']] = ($verdicts[$d['verdict']] ?? 0) + 1;
        }
        $scoredTotal = array_sum($verdicts);

        return $this->ok([
            'run' => ['id' => (int) $run->getIdGridRun(), 'label' => (string) $run->getLabel()],
            'mode' => $mode !== '' ? $mode : ($run->getSimulated() ? 'sim' : 'real'),
            'window_days' => $days,
            'totals' => ['realized_pnl' => $totalPnl, 'fees' => $totalFees, 'cycles' => count($cycles)],
            'metrics' => [
                'cycles_per_active_day' => round(count($cycles) / $activeDays, 2),
                'fees_pct_of_gross' => $feesPctOfGross,
                // capital efficiency: net realized per day per $1k of budget —
                // the number to maximize (cycle COUNT alone rewards fee churn)
                'net_per_1k_day' => bccomp((string) $run->getBudgetQuote(), '0', 8) > 0
                    ? round((float) $totalPnl / $activeDays / (float) $run->getBudgetQuote() * 1000, 2)
                    : null,
                'unrealized_pnl' => $unrealized,
                'refit_decisions_scored' => $scoredTotal,
                'refit_verdicts' => $verdicts,
                'refit_win_rate_pct' => $scoredTotal ? round($verdicts['Win'] / $scoredTotal * 100, 1) : null,
                'refit_worse_than_no_change' => $verdicts['Worse'],
            ],
            'equity_curve' => $equityCurve,
            // wallet-level NAV vs the honest benchmarks (USDT = never deployed,
            // HODL = held BTC) over the same window — from the wallet_nav ledger
            'wallet' => \App\Domains\Bot\NavLedger::report($mode === 'real' || ($mode === '' && !$run->getSimulated()) ? 'real' : 'sim', $days),
            // Fleet-wide, not per run: an episode is a SYMBOL's leg, and what
            // it captured against simply holding is the honest scoreboard for
            // the trend arm — the per-run cycle stats above cannot show it.
            'episodes' => \App\Domains\Bot\RegimeEpisodes::recentClosed(10),
            'by_day' => $byDay,
            'cycles_per_level' => $byLevel,
        ]);
    }
}
