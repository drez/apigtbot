<?php

namespace App\Domains\Bot;

use App\GridRunQuery;
use App\RegimeEpisodeQuery;

/**
 * The go-live question answered from the ledgers instead of from memory.
 *
 * The project set its own bar on 2026-07-31 (docs/proposal-two-sided-grid.md,
 * "Gate 2"): at least four weeks of paper trading that BEAT FLAT USDT, out of
 * sample. Every review since re-derived whether it had been met by hand, from
 * whichever window was on screen — and it never had. This is that bar, plus
 * the state a real fleet must not start from, as one read-only verdict.
 *
 * It never flips anything. Real money stays a human act (ModeSwitch).
 */
class GoLiveGate
{
    public const SOAK_DAYS = 28;

    /**
     * @return array{ready: bool, gates: array<int, array{gate:string, pass:bool, detail:string}>}
     */
    public static function evaluate(): array
    {
        $gates = [];
        $add = static function (string $gate, bool $pass, string $detail) use (&$gates): void {
            $gates[] = ['gate' => $gate, 'pass' => $pass, 'detail' => $detail];
        };

        // ── the soak: long enough, and ahead of never having deployed ──
        $nav = NavLedger::report('sim', self::SOAK_DAYS);
        $span = $nav === null ? 0.0 : (strtotime((string) $nav['to']) - strtotime((string) $nav['from'])) / 86400;
        $add(
            'soak_length',
            $span >= self::SOAK_DAYS - 1,
            sprintf('%.1f of %d paper days on the NAV ledger', $span, self::SOAK_DAYS)
        );
        // A GATE THAT CANNOT TELL MUST NOT PASS (round 3). flows_pending is a
        // stamped budget change whose money never showed up in the window:
        // the return on either side of it is unknowable, so the soak is not
        // certified off it.
        $pending = $nav === null ? '0' : (string) $nav['flows_pending'];
        $unsettled = bccomp($pending, '0', 2) !== 0;
        $add(
            'beats_flat_usdt',
            $nav !== null && !$unsettled && $span >= self::SOAK_DAYS - 1 && (float) $nav['nav_return_pct'] > 0,
            $nav === null
                ? 'no paper NAV in the window'
                : ($unsettled
                    ? sprintf('cannot tell: a budget change of %s USDT is still unsettled in the window', $pending)
                    : sprintf('NAV %+.2f%% vs USDT 0%% (HODL %+.2f%%) over the window', $nav['nav_return_pct'], $nav['hodl_return_pct']))
        );
        $cap = (float) DrawdownGuard::maxDrawdownPct();
        $add(
            'drawdown_inside_floor',
            $nav !== null && ($cap <= 0 || (float) $nav['max_drawdown_pct'] < $cap),
            $nav === null
                ? 'no paper NAV in the window'
                : sprintf('max drawdown %.2f%% vs the %.0f%% floor', $nav['max_drawdown_pct'], $cap)
        );

        // ── the engine has been seen doing the one thing it is for ──
        $since = date('Y-m-d H:i:s', time() - self::SOAK_DAYS * 86400);
        // A COMPLETE leg is the bar, so the gate counts closed episodes only —
        // an open one has not yet shown what the arm did with it. It is named
        // in the detail all the same, because "0 legs" while a leg is running
        // reads as a broken journal rather than as a leg in progress.
        $legs = RegimeEpisodeQuery::create()
            ->filterByVerdict('TREND_UP')
            ->filterByClosedAt(['min' => $since])
            ->find();
        $open = RegimeEpisodeQuery::create()
            ->filterByVerdict('TREND_UP')
            ->filterByClosedAt(null, \Criteria::ISNULL)
            ->count();
        $engaged = 0;
        foreach ($legs as $ep) {
            if ((float) $ep->getEngagedPctTw() > 0) {
                $engaged++;
            }
        }
        $add(
            'trend_leg_seen',
            $engaged > 0,
            sprintf(
                '%d closed TREND_UP episode(s) in the window, %d with the arm engaged; still open: %d',
                count($legs),
                $engaged,
                $open
            )
        );

        // ── the state a real fleet must not start from ──
        $killed = GridRunQuery::create()
            ->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)
            ->filterByKillSwitch(true)
            ->count();
        // A PARKED RUN STILL HOLDING COINS IS THE SAME UNFINISHED BUSINESS
        // (2026-09-21 review). Halted is not an active status, so a run that
        // was killed and parked with its inventory — the exact state the
        // drawdown stop leaves behind — passed this gate untouched while its
        // coins sat on the exchange. Going live on top of that mixes the old
        // position into the new account's books.
        $holding = [];
        foreach (GridRunQuery::create()->filterByStatus(['Done', ...BudgetGuard::ACTIVE_STATUSES], \Criteria::NOT_IN)->find() as $r) {
            if (bccomp(RunLifecycle::store($r)->trackedInventory(), '0', 12) > 0) {
                $holding[] = sprintf('#%d %s (%s)', (int) $r->getIdGridRun(), (string) $r->getLabel(), (string) $r->getStatus());
            }
        }
        $add(
            'no_killed_runs',
            $killed === 0 && $holding === [],
            sprintf(
                '%d active run(s) with the kill switch on; %s',
                $killed,
                $holding === [] ? 'no parked run is still holding inventory' : 'still holding inventory: ' . implode(', ', $holding)
            )
        );
        $add(
            'pool_is_fixed',
            !BudgetPool::useAllFunds(),
            BudgetPool::useAllFunds()
                ? 'gtbot_use_all_funds is ON — on a real account the cap would follow whatever the account holds'
                : 'gtbot_use_all_funds off'
        );
        $add(
            'pool_has_headroom',
            bccomp(BudgetPool::reservePct(), '0', 2) > 0,
            'gtbot_pool_reserve_pct = ' . BudgetPool::reservePct()
        );
        $add('drawdown_floor_armed', $cap > 0, 'gtbot_max_drawdown_pct = ' . DrawdownGuard::maxDrawdownPct());
        $tg = (string) env('GTBOT_TELEGRAM_BOT_TOKEN', '') !== '' && (string) env('GTBOT_TELEGRAM_CHAT_ID', '') !== '';
        $add('alerts_reach_someone', $tg, $tg ? 'Telegram configured' : 'GTBOT_TELEGRAM_BOT_TOKEN / CHAT_ID missing');

        return [
            'ready' => !in_array(false, array_column($gates, 'pass'), true),
            'gates' => $gates,
        ];
    }
}
