<?php

namespace App\Domains\Bot;

/**
 * The one place that answers "would selling at this price realize a loss?".
 *
 * Breakeven follows the Trend engine's long-standing convention — the
 * position's VWAP grossed up by BOTH fee legs (the entry fee already paid and
 * the exit fee the sale will pay) — so a sale at exactly breakeven nets zero
 * and is not a loss.
 *
 * Callers: TrendEngine::mayExitAt (stop-outs and stop-sell re-places), the
 * Flatten command handler (Daemon::consumeCommands) and gtbot_kill's
 * pre-flight. Until 2026-09-18 the two Flatten gates asked only whether
 * grid_run.sell_at_loss was ON, so they refused a PROFITABLE liquidation with
 * the switch OFF (prod run 8: flatten refused at +1.85 USDT, entry 79214 vs
 * spot 80106). The switch's job is to forbid realizing losses, not gains —
 * exactly what the stop-out path had always done. Sharing the rule here keeps
 * the three from drifting apart again.
 *
 * Profile NoLoss is a STRICTER, separate rule (ProfilePolicy::
 * allowsRealizedLoss) and still refuses a commanded liquidation outright,
 * profitable or not — a NoLoss run's inventory is only ever sold by its own
 * exits.
 */
final class LossGuard
{
    private const SCALE = 8;

    /** Cost basis grossed up by both fee legs. */
    public static function breakeven(string $vwap, string $feePct): string
    {
        return bcmul($vwap, bcadd('1', bcmul('2', $feePct, self::SCALE), self::SCALE), self::SCALE);
    }

    /** true when selling the position at $price nets less than it cost. An
     *  absent cost basis (nothing held) or an unknown price is never a loss:
     *  there is nothing to realize. */
    public static function wouldRealizeLoss(?string $vwap, string $feePct, ?string $price): bool
    {
        if ($vwap === null || $price === null || bccomp($vwap, '0', self::SCALE) <= 0) {
            return false;
        }
        return bccomp($price, self::breakeven($vwap, $feePct), self::SCALE) < 0;
    }

    /** Aggregate cost basis of everything the run still holds: quote spent on
     *  unsold inventory over that inventory. Algo-agnostic — a Trend
     *  position's tranches and a grid's unsold lots are both in the same
     *  ledger — so the Flatten gate needs no engine. null when flat. */
    public static function positionVwap(OrderStore $store): ?string
    {
        $qty = $store->trackedInventory();
        if (bccomp($qty, '0', self::SCALE) <= 0) {
            return null;
        }
        return bcdiv($store->investedQuote(), $qty, self::SCALE);
    }
}
