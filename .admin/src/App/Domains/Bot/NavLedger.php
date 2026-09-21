<?php

namespace App\Domains\Bot;

use App\Domains\Dashboard\ModeSwitch;
use App\WalletNav;
use App\WalletNavQuery;

/**
 * Wallet-level NAV history — the fund's track record, not a run's.
 *
 * Every gtbot-market-collect tick stores one wallet_nav row: mark-to-market
 * equity of the whole wallet (DrawdownGuard::equity — paper or the one real
 * account), the shared budget, and the BTC reference price. From that
 * series the pnl report answers the question no per-run stat can: did the
 * whole operation beat never deploying (USDT) and beat just holding BTC
 * (HODL) over the window, and how deep was the equity drawdown.
 * (2026-08-23, from ai-hedge-fund's "NAV is a track record, every result
 * vs a benchmark".)
 */
final class NavLedger
{
    public const REF_SYMBOL = 'BTCUSDT';
    public const KEEP_DAYS = 365;
    private const SCALE = 12;

    /**
     * Store one snapshot of the wallet that was actually measured; null when
     * nothing is priceable yet.
     *
     * FILED BY WHAT WAS MEASURED, not by what the fleet is called
     * (2026-09-21 review). The mode used to come from ModeSwitch, which reads
     * every non-Done row: one real run created as a Draft made the system
     * 'mixed', so a paper fleet's NAV was filed as 'real' — on a row whose
     * equity was 0, because that same never-booted run had no account stamp.
     * Two ledgers ruined at once: the paper series GoLiveGate certifies the
     * soak from stops growing (the gate goes blind the moment you prepare to
     * go live), and the real series is seeded with zeros. DrawdownGuard
     * answers both questions in one read now, so file its `source`.
     */
    public static function snapshot(?string $refPrice = null): ?WalletNav
    {
        if (ModeSwitch::systemMode() === 'none') {
            return null;
        }
        $refPrice ??= self::refPrice();
        if ($refPrice === null) {
            return null;
        }
        $eq = DrawdownGuard::equity();
        $row = new WalletNav();
        $row->setMode($eq['source']);
        $row->setEquityQuote($eq['equity']);
        // The budget column is the EXTERNAL-FLOW RECORD benchmark() reads, so
        // it has to be the capital behind the wallet that was measured
        // (2026-09-21, round 2): a 'real' row stamped with the paper pool seed
        // made every real-account funding change invisible to the benchmark
        // and the paper seed's changes look like real deposits.
        $row->setBudgetQuote(DrawdownGuard::baselineFor($eq['source']));
        $row->setRefSymbol(self::REF_SYMBOL);
        $row->setRefPrice($refPrice);
        $row->setUnpriced($eq['unpriced'] ? mb_substr(implode(',', $eq['unpriced']), 0, 100) : null);
        $row->save();
        return $row;
    }

    public static function prune(): int
    {
        return WalletNavQuery::create()
            ->filterByDateCreation(['max' => date('Y-m-d H:i:s', time() - self::KEEP_DAYS * 86400)])
            ->delete();
    }

    /** Freshest BTC mark: 1h summary price, else the freshest BTC run's last_price. */
    public static function refPrice(): ?string
    {
        $s = MarketStore::summaries(self::REF_SYMBOL, PHP_INT_MAX);
        foreach (['1h', '4h', '1d'] as $tf) {
            if (isset($s[$tf]['price']) && (float) $s[$tf]['price'] > 0) {
                return (string) $s[$tf]['price'];
            }
        }
        $r = \App\GridRunQuery::create()->filterBySymbol(self::REF_SYMBOL)->filterByLastPrice(null, \Criteria::ISNOTNULL)
            ->orderByLastTickAt(\Criteria::DESC)->findOne();
        return $r ? (string) $r->getLastPrice() : null;
    }

    /**
     * Pure benchmark arithmetic over a NAV series (oldest first).
     *
     * @param array<int, array{equity:string, ref_price:string, at:string}> $series
     * @param string|null $priorBudget the budget in force just BEFORE the
     *        window opened (report() looks it up). Without it a flow whose
     *        budget row falls outside a rolling window is invisible and its
     *        money reads as pure performance.
     * @return array|null null when fewer than two points
     */
    public static function benchmark(array $series, ?string $priorBudget = null): ?array
    {
        if (count($series) < 2) {
            return null;
        }
        $first = $series[0];
        $last = $series[count($series) - 1];
        if (bccomp((string) $first['equity'], '0', self::SCALE) <= 0 || bccomp((string) $first['ref_price'], '0', self::SCALE) <= 0) {
            return null;
        }
        $pct = static fn (string $a, string $b): float => round(((float) $a / (float) $b - 1) * 100, 2);
        $hodl = $pct((string) $last['ref_price'], (string) $first['ref_price']);

        // TIME-WEIGHTED return. Equity moves for two unrelated reasons: the
        // fleet earned (or lost) it, or money was added/removed. A raw
        // last/first ratio cannot tell them apart and books every deposit as
        // performance — prod 2026-09-03's 1000 -> 1300 budget raise read as
        // +28.8% against HODL +1.59% while the fleet was roughly flat. The
        // shared budget stamped on each row IS the external-flow record, so
        // chain the per-step returns and take the flow out of the numerator:
        // r = (E_now - flow) / E_prev - 1. With no flows this is exactly the
        // old ratio, so a series carrying no budget is unaffected.
        //
        // THE FLOW AND ITS MONEY DO NOT HAVE TO ARRIVE TOGETHER (2026-09-21
        // review). The budget column changes the instant the config row is
        // written; the paper wallet is only re-seeded when each daemon
        // reboots, so the flow can be stamped one or more points BEFORE the
        // equity moves. Subtracting it on the wrong step books a step that
        // never happened and then its mirror: a FLAT fleet across a
        // 1000->1300 raise read -9.0% with a 30% max drawdown — through the
        // 25% go-live gate — while the same raise seen inside one step was
        // priced correctly. (Prod's actual 09-03 rows moved together:
        // 998.86 -> 1299.23 with budget 1000 -> 1300, so the +3.37% on the
        // ledger stands. This is the shape that would have lied about it.)
        // DO NOT GUESS WHICH STEP IT LANDED ON — DO NOT CLOSE THE STEP AT ALL
        // (2026-09-21, round 2). The rule used to be "apply the whole flow to
        // the first step whose equity moves by at least HALF of it, and
        // force-apply it after FLOW_LAG_POINTS regardless". Both halves
        // invented returns: half a deposit arriving booked all of it (a flat
        // fleet read -3.91%), two deposits inside the lag window landed on
        // each other (-7.69% with a 20% drawdown), and the force-apply dropped
        // a whole flow onto an unrelated step (a 27% drawdown, straight past
        // the 25% go-live gate).
        //
        // A return cannot be measured across an unsettled flow, so while one
        // is pending the step simply STAYS OPEN: the index does not move, the
        // base stays at the equity from before the flow, and when the money is
        // in, ONE return is booked across the whole span —
        // (E_landed − flow) / E_before_flow. With no flows this is exactly the
        // old per-step chain, and the same-step prod shape is byte-identical.
        //
        // LANDED = the equity has moved WITH the flow by at least 90% of it,
        // counted from where the span opened (so instalments accumulate). A
        // flow still in the air when the window ends is booked then — an
        // un-booked flow is the old "every deposit is performance" bug —
        // UNLESS the equity never moved in its direction at all, the one case
        // where booking it fabricates a loss out of money that demonstrably
        // never arrived. What is left over is reported as `flows_pending`
        // rather than silently dropped.
        $index = 1.0;
        $flows = '0';
        $peak = 1.0;
        $maxDd = 0.0;
        $pending = '0';
        if ($priorBudget !== null && isset($first['budget'])) {
            $pending = bcsub((string) $first['budget'], $priorBudget, self::SCALE);
        }
        $base = (string) $first['equity']; // equity the currently open step started from
        $byDay = [substr((string) $first['at'], 0, 10) => bcadd((string) $first['equity'], '0', 2)];
        $close = function (string $equity, string $flow) use (&$index, &$peak, &$maxDd, &$base, &$flows): void {
            $flows = bcadd($flows, $flow, self::SCALE);
            if ((float) $base > 0) {
                $index *= ((float) $equity - (float) $flow) / (float) $base;
            }
            $base = $equity;
            $peak = max($peak, $index);
            $maxDd = max($maxDd, (1 - $index / $peak) * 100);
        };
        for ($i = 1, $n = count($series); $i < $n; $i++) {
            $prev = $series[$i - 1];
            $p = $series[$i];
            if (isset($p['budget'], $prev['budget'])) {
                $pending = bcadd($pending, bcsub((string) $p['budget'], (string) $prev['budget'], self::SCALE), self::SCALE);
            }
            $byDay[substr((string) $p['at'], 0, 10)] = bcadd((string) $p['equity'], '0', 2); // last point of the day wins

            $flow = '0';
            if (bccomp($pending, '0', self::SCALE) !== 0) {
                if (!self::landed(bcsub((string) $p['equity'], $base, self::SCALE), $pending)) {
                    // AN OPEN STEP MUST NOT HIDE A CRASH (round 3). peak/maxDd
                    // only moved when a step CLOSED, so a 30% drawdown that
                    // happened between a budget edit and its money arriving
                    // reported 0.00 — straight through the go-live gate. The
                    // index is provisional (no flow booked, because none is
                    // known to have arrived) and never raises the peak: it can
                    // only reveal a fall, never invent a recovery.
                    if ((float) $base > 0) {
                        $maxDd = max($maxDd, (1 - ($index * (float) $p['equity'] / (float) $base) / $peak) * 100);
                    }
                    continue; // the step stays open until the money is in
                }
                $flow = $pending;
                $pending = '0';
            }
            $close((string) $p['equity'], $flow);
        }
        if (bccomp($pending, '0', self::SCALE) !== 0) {
            $moved = bcsub((string) $last['equity'], $base, self::SCALE);
            // THE SAME RULE AS INSIDE THE SERIES (round 3). This used to ask
            // only whether the equity had moved in the flow's DIRECTION, so
            // one USDT of drift booked a 300 USDT flow: a budget lowered on
            // the last point of the window turned a flat fleet into +23% and
            // certified the go-live gate. What is left over is flows_pending,
            // and a window carrying one cannot certify anything (GoLiveGate).
            $arrived = self::landed($moved, $pending);
            $close((string) $last['equity'], $arrived ? $pending : '0');
            $pending = $arrived ? '0' : $pending;
        }
        $nav = round(($index - 1) * 100, 2);
        return [
            'from' => (string) $first['at'], 'to' => (string) $last['at'], 'points' => count($series),
            'equity_start' => bcadd((string) $first['equity'], '0', 2), 'equity_end' => bcadd((string) $last['equity'], '0', 2),
            'nav_return_pct' => $nav,
            'net_flows' => bcadd($flows, '0', 2),
            // a stamped budget change whose money never showed up in the window
            'flows_pending' => bcadd($pending, '0', 2),
            'hodl_return_pct' => $hodl,
            'usdt_return_pct' => 0.0,
            'excess_vs_hodl_pct' => round($nav - $hodl, 2),
            'excess_vs_usdt_pct' => $nav,
            'max_drawdown_pct' => round($maxDd, 2),
            'equity_by_day' => $byDay,
        ];
    }

    /** Has the equity moved WITH a pending flow by at least 90% of it? */
    private static function landed(string $moved, string $pending): bool
    {
        if (bccomp($moved, '0', self::SCALE) !== bccomp($pending, '0', self::SCALE)) {
            return false;
        }
        return bccomp(
            bcmul(self::abs($moved), '10', self::SCALE),
            bcmul(self::abs($pending), '9', self::SCALE),
            self::SCALE
        ) >= 0;
    }

    private static function abs(string $n): string
    {
        return bccomp($n, '0', self::SCALE) < 0 ? bcsub('0', $n, self::SCALE) : $n;
    }

    /**
     * benchmark() over the stored series for one mode and look-back.
     *
     * THE WINDOW EDGE IS NOT THE START OF HISTORY (2026-09-21, round 2). The
     * series carried only the rows INSIDE the window, so the first row's
     * budget had nothing to be compared against — and a budget change that
     * fell just outside a rolling 28-day window became invisible while its
     * money landed inside it. A flat fleet across prod's 1000 -> 1300 raise
     * then read +30%, and GoLiveGate certifies the soak off exactly this
     * number: the gate could PASS on a deposit. One extra row — the last one
     * before the window — is the whole fix.
     */
    public static function report(string $mode, int $days): ?array
    {
        $since = date('Y-m-d H:i:s', time() - $days * 86400);
        $series = [];
        $firstId = null;
        foreach (WalletNavQuery::create()
            ->filterByMode($mode)
            ->filterByDateCreation(['min' => $since])
            ->orderByIdWalletNav()
            ->find() as $r) {
            $firstId ??= (int) $r->getIdWalletNav();
            $series[] = [
                'equity' => (string) $r->getEquityQuote(),
                'ref_price' => (string) $r->getRefPrice(),
                'at' => $r->getDateCreation('Y-m-d H:i'),
                'budget' => (string) $r->getBudgetQuote(), // external-flow marker; see benchmark()
            ];
        }
        // by id, not by date: the row right before the window, whatever its
        // timestamp rounds to
        $prior = $firstId === null ? null : WalletNavQuery::create()
            ->filterByMode($mode)
            ->filterByIdWalletNav($firstId, \Criteria::LESS_THAN)
            ->orderByIdWalletNav(\Criteria::DESC)
            ->findOne();
        $out = self::benchmark($series, $prior === null ? null : (string) $prior->getBudgetQuote());
        if ($out !== null) {
            $out['mode'] = $mode;
            $out['ref_symbol'] = self::REF_SYMBOL;
        }
        return $out;
    }
}
