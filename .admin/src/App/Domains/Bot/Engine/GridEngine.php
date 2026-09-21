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
        // buy levels below price are armed — deep buys are cancelled (budget
        // freed) and re-arm automatically as price falls back. ONE window,
        // read once per tick, for both halves: arming at its 'arm' edge and
        // pruning at its 'keep' edge, which lags by a level so the two can
        // never fight over the same order (Daemon::buyWindow).
        $window = $d->buyWindow($price);
        $d->pruneDistantBuys($window['keep'], $price);

        // soft de-risk stage: no new entries while a drawdown is running at
        // the cap — initialIntents only ever arms buys, so gate the call.
        // An overcommitted shared budget gates entries the same way (never
        // overcommit): exits keep working, new exposure waits.
        if ($entriesGated) {
            return;
        }
        // initialIntents arms EVERY eligible level in one pass, so a throw
        // part-way through the batch leaves the levels BEHIND it marked
        // BUY_OPEN with no order and no store row: initialIntents only re-arms
        // 'EMPTY' levels, so nothing would ever place them (until a boot or
        // refit rebuilds the machine) and the silent-grid watch would read
        // those phantoms as an armed ladder. placeIntent un-arms the level
        // that threw; un-arm the rest of the batch here so the next tick
        // re-arms and re-places them.
        $intents = $d->machine->initialIntents($price, $window['arm']);
        foreach ($intents as $i => $o) {
            try {
                $d->placeIntent($o, $price);
            } catch (\Throwable $e) {
                for ($j = $i + 1, $n = count($intents); $j < $n; $j++) {
                    if ($intents[$j]->side === 'Buy') {
                        $d->machine->hydrateLevel($intents[$j]->levelIdx, 'EMPTY');
                    }
                }
                throw $e; // the runner logs api_error and backs off
            }
        }
    }

    /** A filled buy arms its exit one level up. */
    public function onBuyFill(\App\BotOrder $row, string $executed, string $fee, string $price): void
    {
        $d = $this->daemon;
        $level = (int) $row->getLevelIdx();
        // a fill that delivered less than it ordered (commission taken out of
        // the base asset — Daemon::bookFill) is exited for what is held
        $sell = bccomp($executed, (string) $row->getQty(), 8) < 0
            ? $d->machine->onPartialBuyBooked($level, $executed)
            : $d->machine->onBuyFill($level);
        $d->log->write('Info', 'buy_fill', sprintf('L%02d filled @ %s qty %s', $level, $row->getPrice(), $executed));
        $d->placeIntent($sell, $price);
    }

    /** A filled exit books the cycle and re-arms the level's buy. */
    /** A grid's inventory IS its working exits, which the shell has already
     *  repriced to the mark — nothing else is held off the book. */
    public function liquidate(string $price): void
    {
    }

    public function onSellFill(\App\BotOrder $row, string $executed, string $fee, string $price): void
    {
        $d = $this->daemon;
        $level = (int) $row->getLevelIdx();
        // What the LEVEL is backing — which is the qty the machine is about to
        // book a cycle for. An exit canceled after part of it traded sold less
        // than that (Daemon::bookCanceledRemainder hands it here instead of
        // booking a cycle of its own: an engine books its own fills), and the
        // rest is still held: closing the whole lot here would book base the
        // account never sold and re-arm the buy on top of inventory that is
        // still there.
        $holding = $d->machine->heldAt($level);
        if (bccomp($holding, $executed, 8) > 0) {
            // floored to the lot step, like every other booking path: a level
            // left holding the sliver between steps sells short of itself and
            // keeps the difference as inventory no order can ever carry
            $left = $d->qtyToStep(bcsub($holding, $executed, 8));
            $this->bookPartialExit($row, $executed, $fee, $left, $holding, $price);
            return;
        }
        // cycle fees = this sell's fee + the matched buy's fee — or, when this
        // exit is a piece of a lot that was already partly sold, the share of
        // it that piece still carries (Daemon::exitBuyFee reads the pin)
        // booked at what the two orders TRADED at (Daemon::bookFill puts it
        // on the rows) — the ladder's lines only when the ledger has no row
        $res = $d->machine->onSellFill(
            $level,
            bcadd($fee, $d->exitBuyFee($row), 12),
            $row->getLegacyBuyPrice() !== null ? (string) $row->getLegacyBuyPrice() : $d->matchedBuyPrice($level),
            (string) $row->getPrice()
        );
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

    /**
     * Part of a level's lot sold and the rest is still held (an exit canceled
     * mid-fill). Book the cycle for what really traded — buy-leg fee prorated
     * — keep the remainder on the level and re-arm its exit for exactly that,
     * pinning the UNBOOKED share of the buy fee onto the replacement so the
     * lot pays its entry fee once across the pieces (Daemon::exitBuyFee reads
     * the pin back). A remainder that could never be an order again is dust:
     * written off and the level re-armed, exactly like the stuck-partial and
     * pruned-partial guards do.
     */
    private function bookPartialExit(
        \App\BotOrder $row,
        string $executed,
        string $fee,
        string $left,
        string $holding,
        string $price
    ): void {
        $d = $this->daemon;
        $level = (int) $row->getLevelIdx();
        $sellPrice = (string) $row->getPrice(); // bookFill put what it TRADED at on the row
        $buyPrice = $row->getLegacyBuyPrice() !== null
            ? (string) $row->getLegacyBuyPrice()
            : ($d->matchedBuyPrice($level) ?? $sellPrice);
        $buyFee = $d->exitBuyFee($row);
        $booked = bcmul($buyFee, bcdiv($executed, $holding, 12), 12);
        $fees = bcadd($fee, $booked, 12);
        $d->store->recordCycle([
            'level_idx' => $level,
            'buy_price' => $buyPrice,
            'sell_price' => $sellPrice,
            'qty' => $executed,
            'realized_pnl' => bcsub(bcmul(bcsub($sellPrice, $buyPrice, 12), $executed, 12), $fees, 12),
            'fees_total' => $fees,
        ]);
        $d->log->write('Info', 'cycle_closed', sprintf(
            'L%02d partial cycle: buy %s → sell %s qty %s of %s (the rest stays held)',
            $level,
            $buyPrice,
            $sellPrice,
            $executed,
            $holding
        ));
        $exitLine = (string) ($d->levels[$level + 1] ?? $sellPrice);
        if (!$d->filtersClear('Sell', $exitLine, $left)) {
            $d->machine->hydrateLevel($level, 'EMPTY');
            $d->log->write('Alert', 'partial_dust', sprintf(
                'L%02d exit %s sold %s of %s and the %s left cannot clear exchange filters — written off, level re-armed',
                $level,
                (string) $row->getClientOrderId(),
                $executed,
                $holding,
                $left
            ));
            return;
        }
        $d->machine->hydrateLevel($level, 'SELL_OPEN', $left);
        try {
            $replacement = $d->placeIntent(new IntendedOrder('Sell', $level, $exitLine, $left), $price);
        } catch (\App\Domains\Bot\Gateway\BinanceApiError $e) {
            // The fill is already booked, so throwing out of here would lose
            // the re-place AND abandon whatever else this tick was booking.
            $d->log->write('Error', 'place_failed', sprintf(
                'L%02d exit for the %s left of a part-sold lot: %s',
                $level,
                $left,
                $e->getMessage()
            ));
            $replacement = null;
        }
        if ($replacement === null) {
            // Nothing reached the book (refused above, or vetoed). Named once
            // — the base is real and unguarded right now. The level goes on
            // claiming it, so the unrealized stop and the drawdown watch still
            // mark it, and Daemon::reArmMissingExits keeps trying to put the
            // exit back: it is the only sweep that can see a level armed in
            // memory with no ledger row behind it.
            $d->warnUnguardedInventory($left, $buyPrice, (string) $row->getClientOrderId(), 'the remainder of a canceled exit');
            return;
        }
        $d->store->pinBuyAttribution($replacement, $buyPrice, bcsub($buyFee, $booked, 12));
    }

    public function onExitResized(int $levelIdx, string $placedQty): void
    {
        $this->daemon->machine->hydrateLevel($levelIdx, 'SELL_OPEN', $placedQty);
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
        // held = the ROW's qty: a netted/shrunk exit must not jump back to the
        // ladder's standard qty because its order was canceled
        $d->machine->hydrateLevel($level, 'SELL_OPEN', (string) $row->getQty());
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
