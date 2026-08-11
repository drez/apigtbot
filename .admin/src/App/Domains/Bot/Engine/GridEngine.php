<?php

namespace App\Domains\Bot\Engine;

use App\Domains\Bot\Daemon;
use App\Domains\Bot\IntendedOrder;

/**
 * The original grid strategy, extracted verbatim from the Daemon tick loop —
 * behaviour-preserving by construction: every step still runs the daemon's own
 * helpers, in the same order, on the same state.
 *
 * One tick: apply a pending refit (geometry written onto the run row), prune
 * buys that fell outside the distance window, then arm the entry intents the
 * level machine asks for (unless entries are gated).
 */
final class GridEngine implements StrategyEngine
{
    public function __construct(private readonly Daemon $daemon) {}

    /** The grid's state IS the ladder + level machine, both built by the
     *  shell's boot (buildLadder + GridStateHydrator) — nothing extra. */
    public function boot(): void
    {
    }

    public function tick(string $price, bool $entriesGated): void
    {
        $d = $this->daemon;

        // Auto-refit: the refit cron / Claude writes new geometry onto the run
        // row; the daemon re-anchors within a tick, carrying working sells as
        // legacy exits (see maybeRefit).
        if ($d->geometryChanged()) {
            $d->maybeRefit($price);
        }

        // Distance window: with max_buy_levels_below set, only the N nearest
        // buy levels below price stay armed — deep buys are cancelled (budget
        // freed) and re-arm automatically as price falls back.
        $minLevel = $d->distanceMinLevel($price);
        $d->pruneDistantBuys($minLevel, $price);

        // soft de-risk stage: no new entries while a drawdown is running at
        // the cap — initialIntents only ever arms buys, so gate the call.
        // An overcommitted shared budget gates entries the same way (never
        // overcommit): exits keep working, new exposure waits.
        if ($entriesGated) {
            return;
        }
        foreach ($d->machine->initialIntents($price, $minLevel) as $o) {
            $d->placeIntent($o, $price);
        }
    }

    /** A filled buy arms its exit one level up. */
    public function onBuyFill(\App\BotOrder $row, string $executed, string $fee, string $price): void
    {
        $d = $this->daemon;
        $level = (int) $row->getLevelIdx();
        $sell = $d->machine->onBuyFill($level);
        $d->log->write('Info', 'buy_fill', sprintf('L%02d filled @ %s qty %s', $level, $row->getPrice(), $executed));
        $d->placeIntent($sell, $price);
    }

    /** A filled exit books the cycle and re-arms the level's buy. */
    public function onSellFill(\App\BotOrder $row, string $executed, string $fee, string $price): void
    {
        $d = $this->daemon;
        $level = (int) $row->getLevelIdx();
        // cycle fees = this sell's fee + the matched buy's fee
        $res = $d->machine->onSellFill($level, bcadd($fee, $d->matchedBuyFee($level), 12));
        $d->store->recordCycle($res['cycle']);
        $d->log->write('Info', 'cycle_closed', sprintf(
            'L%02d cycle: buy %s → sell %s qty %s realized %s',
            $level,
            $res['cycle']['buy_price'],
            $res['cycle']['sell_price'],
            $res['cycle']['qty'],
            $res['cycle']['realized_pnl']
        ));
        $d->placeIntent($res['rearm'], $price);
    }

    /** A working exit left the book without filling (externally canceled/
     *  expired/rejected) — re-place it, exactly as the shell always has: the
     *  ladder level it belongs to is unambiguous for a grid book. Moved out
     *  of Daemon::resolveMissing verbatim (byte-identical behavior) so a
     *  non-grid engine's book, which has no ladder level, isn't forced
     *  through the same re-arm. */
    public function onSellCanceled(\App\BotOrder $row, ?string $price): void
    {
        $d = $this->daemon;
        $level = (int) $row->getLevelIdx();
        // a canceled exit still has inventory behind it — re-place it
        // immediately, else the next hydration would forget the inventory.
        // Unconditional (unlike Trend's own onSellCanceled): a grid exit
        // guards inventory at its own fixed ladder price regardless of the
        // live tape, so a boot-time re-arm (no live price available yet —
        // see StrategyEngine::onSellCanceled) falls back to the row's own
        // last-known price exactly as before this became nullable.
        $d->machine->hydrateLevel($level, 'SELL_OPEN');
        $d->log->write('Alert', 'sell_canceled', sprintf('%s (an exit) was canceled — re-placing the exit', $row->getClientOrderId()));
        $d->placeIntent(new IntendedOrder('Sell', $level, $d->levels[$level + 1], (string) $row->getQty()), $price ?? (string) $row->getPrice());
    }

    /** The grid's own held inventory (its ladder's filled buys still awaiting
     *  an exit) plus any legacy exits still guarding older inventory —
     *  engine-independent, but every engine adds it in since it can coexist
     *  with any strategy's own book. */
    public function heldQty(): string
    {
        $d = $this->daemon;
        return bcadd($d->machine->heldInventory(), $d->store->legacyRemainingQty(), 8);
    }

    /** Nothing to hand off: the grid's position IS its open book. Every filled
     *  buy arms its exit immediately, so held inventory is already behind a
     *  working sell the shell's own cutover carries as a legacy exit — and a
     *  buy still working is canceled (and, if it had filled, booked and exited)
     *  by cancelEntriesBookingFills. There is no grid inventory living only in
     *  engine_state for a cutover to erase. */
    public function handoffUnguardedPosition(): void
    {
    }

    /** Switching INTO the grid: the shell already canceled the previous
     *  engine's orders and rebuilt an empty ladder on the run's geometry, so
     *  the next tick simply arms it. */
    public function cutover(): void
    {
    }
}
