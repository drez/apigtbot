<?php

namespace App\Domains\Bot;

use App\GridRunQuery;
use Criteria;

/**
 * How much of the shared pool is actually WORKING, per symbol and in total.
 *
 * The fleet has always known what each run's slice is (budget_quote) and
 * what it has bought (OrderStore::investedQuote), but nothing ever divided
 * one by the other. That ratio is the number every "the arm sat out a leg"
 * post-mortem was really about: BNB ran +5.7% over 2026-09-15..18 with its
 * arm parked by hand — 100% of its slice declared and 0% of it engaged —
 * and the only instrument that noticed was the operator's eye, days later.
 *
 * TWO DENOMINATORS, because the two questions are different (2026-09-19,
 * review fix): the first draft divided by Σ budget_quote over every non-Done
 * run, and that number was wrong in both directions at once.
 *
 *   TOTAL      Σ invested over the ACTIVE runs ÷ BudgetPool::cap().
 *              Only DryRun/Testnet/Live runs charge the shared pool
 *              (BudgetGuard::ACTIVE_STATUSES); a Halted, Draft or Retiring
 *              slice is re-lent by the allocator on its next pass, so
 *              counting it as "declared capital" DILUTED the ratio — a fleet
 *              whose arms were all parked read as busy, and true idle
 *              headroom (cap the fleet never claimed at all) never appeared
 *              in the metric at all. The cap is the money that exists;
 *              dividing by it is the only way "the pool is 40% engaged" can
 *              mean what it says. The numerator matches the denominator: the
 *              cap accounts for the active slices, so the total counts what
 *              those runs put in the market.
 *
 *   PER-SYMBOL Σ invested over the symbol's runs ÷ Σ budget_quote over the
 *              symbol's ACTIVE runs. This one asks "is the slice this symbol
 *              holds being used", so the denominator is the slice it holds —
 *              and a run that is no longer active but STILL HOLDS INVENTORY
 *              is deliberately kept in the numerator: its coins are real
 *              money in the market for that symbol, whoever is nominally
 *              charged for them. That is why a parked arm sitting on a bag
 *              can read above 100%: the bag outlived the slice that bought
 *              it, which is worth seeing rather than clamping away.
 *
 * A zero denominator is 0%, never a division by zero and never "n/a" — an
 * absent arm is the very case this metric exists to report on — and the
 * entry carries the `cause` RegimeEpisodes speaks in ('slot empty'), so the
 * reader of a 0% row is told which kind of zero it is.
 *
 * The journal carries cap / allocated / headroom alongside the ratios so the
 * two denominators RECONCILE from the row alone: allocated is Σ active
 * slices (the per-symbol denominators summed), cap is the total's, and
 * headroom = cap − allocated is what neither of them charged. A NEGATIVE
 * headroom is not a bug: it is the overcommit BudgetGuard refuses new
 * writes over, visible in the same row.
 *
 * compute() is pure (rows in, ratios out) so the arithmetic is testable
 * without a fleet; pool() is the one DB read that feeds it.
 */
final class Engagement
{
    /** bot_event kind the allocator journals one sample under. */
    public const KIND = 'pool_engagement';

    /** A symbol whose ACTIVE runs declare nothing, and that holds nothing. */
    public const CAUSE_EMPTY = 'slot empty';
    /**
     * Same empty denominator, but the symbol is sitting on inventory: every
     * run holding it is parked, so the slice was handed back while the coins
     * stayed. `slot empty` would read as "nothing here", which is the one
     * thing that is not true — this is capital the pool cannot re-lend.
     */
    public const CAUSE_PARKED = 'parked — inventory only';
    /** Only reachable with a zero/absent shared budget. */
    public const CAUSE_NO_CAP = 'no pool cap';

    private const SCALE = 8;
    /** engaged_pct_tw / hodl_pct / captured_pct are decimal(_, 4) */
    private const PCT_SCALE = 4;

    /**
     * Pure: per-symbol and total engagement from raw
     * {symbol, invested, slice, active} rows and the shared budget cap.
     * Several rows may share a symbol (an arm and its grids) — they are
     * summed before the division, because the pool does not care which run
     * inside a symbol is holding the money.
     *
     * `active` is "this run charges the shared pool" (BudgetGuard's
     * DryRun/Testnet/Live). It decides ONLY whether the row's slice joins a
     * denominator; the row's invested always joins its symbol's numerator,
     * and joins the total's only when the run is active — see the class
     * docblock for why the two numerators differ.
     *
     * @param list<array{symbol: string, invested: string, slice: string, active?: bool}> $rows
     * Every entry's `slice` is the denominator that entry divided by, so the
     * total's `slice` IS the cap (same number as the top-level `cap`). One
     * shape for both kinds of entry, one printer, one reconciliation.
     *
     * @param string $cap BudgetPool::cap() — the total's denominator
     * @return array{cap: string, allocated: string, headroom: string,
     *               total: array{invested: string, slice: string, pct: string, cause: ?string},
     *               per_symbol: array<string, array{invested: string, slice: string, pct: string, cause: ?string}>}
     */
    public static function compute(array $rows, string $cap): array
    {
        $bySymbol = [];
        $activeInv = '0';
        $allocated = '0';
        foreach ($rows as $row) {
            $symbol = (string) $row['symbol'];
            $active = (bool) ($row['active'] ?? true);
            $invested = (string) $row['invested'];
            $slice = (string) $row['slice'];
            $bySymbol[$symbol] ??= ['invested' => '0', 'slice' => '0'];
            $bySymbol[$symbol]['invested'] = bcadd($bySymbol[$symbol]['invested'], $invested, self::SCALE);
            if (!$active) {
                continue;   // a parked slice is re-lent: it declares nothing
            }
            $bySymbol[$symbol]['slice'] = bcadd($bySymbol[$symbol]['slice'], $slice, self::SCALE);
            $activeInv = bcadd($activeInv, $invested, self::SCALE);
            $allocated = bcadd($allocated, $slice, self::SCALE);
        }
        ksort($bySymbol);
        $out = [];
        foreach ($bySymbol as $symbol => $sums) {
            $out[$symbol] = self::entry($sums['invested'], $sums['slice'], self::CAUSE_EMPTY);
        }
        return [
            'cap' => bcadd($cap, '0', 2),
            'allocated' => bcadd($allocated, '0', 2),
            // not clamped at 0: a negative headroom IS the overcommit state,
            // and hiding it here would hide it from the only row that carries
            // both numbers.
            'headroom' => bcsub($cap, $allocated, 2),
            'total' => self::entry($activeInv, $cap, self::CAUSE_NO_CAP),
            'per_symbol' => $out,
        ];
    }

    /**
     * Every non-Done run's symbol, invested quote, slice and whether it
     * charges the shared pool — the live input to compute(). One OrderStore
     * per run, built exactly as Allocator does (run uid + ledger epoch +
     * mode), so a rebased ledger is not counted.
     *
     * Non-active runs are still READ, because their inventory belongs in
     * their symbol's numerator; compute() is what drops their slice.
     *
     * @return list<array{symbol: string, invested: string, slice: string, active: bool}>
     */
    public static function rows(): array
    {
        $out = [];
        foreach (GridRunQuery::create()
            ->filterByStatus('Done', Criteria::NOT_EQUAL)
            ->orderByIdGridRun()
            ->find() as $run) {
            $out[] = [
                'symbol' => (string) $run->getSymbol(),
                'invested' => RunLifecycle::store($run)->investedQuote(),
                'slice' => (string) $run->getBudgetQuote(),
                'active' => in_array((string) $run->getStatus(), BudgetGuard::ACTIVE_STATUSES, true),
            ];
        }
        return $out;
    }

    /** compute(rows(), cap) — what the allocator journals every pass. */
    public static function pool(): array
    {
        return self::compute(self::rows(), BudgetPool::cap());
    }

    /**
     * Journal one sample. The payload is the whole compute() output, so the
     * event feed carries the per-symbol split, the cap and the headroom and
     * not only the headline — reading back "which symbol was the idle one on
     * the 14th, and was there pool left over" has to work from the row alone.
     */
    public static function journal(EventLog $log, array $engagement): void
    {
        $log->write('Info', self::KIND, self::describe($engagement), $engagement);
    }

    /**
     * One line for the event message / the daemon's stdout. Both
     * denominators are LABELLED, because they are different numbers: the
     * headline divides by the pool cap, each symbol by what it has
     * allocated.
     */
    public static function describe(array $engagement): string
    {
        $parts = [];
        foreach ($engagement['per_symbol'] as $symbol => $e) {
            $parts[] = sprintf(
                '%s %s%% (%s of %s allocated%s)',
                $symbol,
                $e['pct'],
                $e['invested'],
                $e['slice'],
                ($e['cause'] ?? null) === null ? '' : ' — ' . $e['cause']
            );
        }
        return sprintf(
            'pool engagement %s%% (%s of cap %s USDT working; %s allocated, %s headroom)%s',
            $engagement['total']['pct'],
            $engagement['total']['invested'],
            $engagement['cap'],
            $engagement['allocated'],
            $engagement['headroom'],
            $parts === [] ? '' : ' — ' . implode(', ', $parts)
        );
    }

    /**
     * One ratio. `slice` is the DENOMINATOR this entry divided by — the
     * symbol's active slices for a per-symbol entry, the pool cap for the
     * total (the same number the payload carries as `cap`; the key stays
     * `slice` so both entries have one shape, and `describe()` can print
     * either).
     *
     * A zero denominator is 0%, and the cause distinguishes the two zeros:
     * nothing here at all, or coins parked behind a slice that was handed
     * back. The percentage is 0 either way — the capital genuinely is not
     * working, and engaged_pct_tw must not be told otherwise — but the word
     * is what sends the reader to the right place.
     *
     * @return array{invested: string, slice: string, pct: string, cause: ?string}
     */
    private static function entry(string $invested, string $slice, string $zeroCause): array
    {
        $empty = bccomp($slice, '0', self::SCALE) <= 0;
        $holds = bccomp($invested, '0', self::SCALE) > 0;
        return [
            'invested' => bcadd($invested, '0', 2),
            'slice' => bcadd($slice, '0', 2),
            'pct' => $empty
                ? bcadd('0', '0', self::PCT_SCALE)
                : bcmul(bcdiv($invested, $slice, self::SCALE + self::PCT_SCALE), '100', self::PCT_SCALE),
            'cause' => $empty
                ? ($holds && $zeroCause === self::CAUSE_EMPTY ? self::CAUSE_PARKED : $zeroCause)
                : null,
        ];
    }
}
