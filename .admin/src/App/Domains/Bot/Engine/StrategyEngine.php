<?php

namespace App\Domains\Bot\Engine;

/**
 * The strategy seam. The Daemon owns everything that is NOT a strategy —
 * boot/reconcile, the control plane (commands, kill switch), the stops
 * (unrealized / global drawdown / budget invariant), fill detection and the
 * ledger — and delegates the strategy half of a tick, plus the post-fill
 * semantics of its own orders, to one of these.
 *
 * Legacy exits are engine-independent by design: they are resolved by the
 * shell (Daemon::resolveLegacy) whatever engine is running, so a strategy
 * switch can carry real inventory out of the previous strategy safely.
 *
 * PERSISTENCE CONTRACT: an engine persists its state ONLY through
 * `Daemon::persistEngineState(array $keys)`, which merges the given keys into
 * `grid_run.engine_state` and re-writes the shell's own `algo` marker in the
 * same save. Never call `$run->setEngineState()` (or otherwise write that
 * column) from an engine: the column is shared with the shell, and dropping
 * the `algo` marker leaves the next boot unable to tell which engine the open
 * book belongs to — which is how a book gets handed to, or torn down by, the
 * wrong strategy.
 */
interface StrategyEngine
{
    /** Hydrate engine state at daemon boot (after shell config is built). */
    public function boot(): void;

    /** The strategy section of one tick: refits/pruning/entry intents.
     *  $entriesGated mirrors the shell's deRisking/budgetOvercommitted gate:
     *  refit-detection/pruning/stop management still run; NEW entries do not. */
    public function tick(string $price, bool $entriesGated): void;

    /** Post-fill semantics for a non-legacy engine order. */
    public function onBuyFill(\App\BotOrder $row, string $executed, string $fee, string $price): void;

    /**
     * An exit of this engine's own traded — AN ENGINE BOOKS ITS OWN FILLS.
     * The shell has booked the fill on the ledger (Daemon::bookFill) and hands
     * over what the account actually delivered; the cycle, the position and
     * the re-arm are the engine's, because only it knows what this fill did to
     * the position it is running.
     *
     * $executed is NOT always the whole order: an exit canceled after part of
     * it traded comes through here too (Daemon::bookCanceledRemainder), with
     * the rest still held. An engine that assumes "sell filled ⇒ position
     * closed" books base it never sold, and — for an engine that tracks its
     * position off the book — can never read flat again.
     */
    public function onSellFill(\App\BotOrder $row, string $executed, string $fee, string $price): void;

    /** A working exit of THIS engine's own (non-legacy) left the book without
     *  filling (canceled/expired/rejected externally — not the engine's own
     *  doing). The re-arm is strategy-shaped (a grid ladder level vs. a trend
     *  stop price), so the shell dispatches here rather than assuming a
     *  ladder: re-place the exit at wherever this engine believes it belongs
     *  now.
     *
     *  $price is the LIVE tape price when this fires from a tick's own fill
     *  detection, and null when it fires from Daemon::boot()'s own reconcile
     *  (no tick has run yet, so no live price exists). Never coerce null to
     *  $row->getPrice() before calling this — an engine that needs to judge
     *  whether the exit is STILL genuinely warranted (not just re-arm
     *  unconditionally) has to be able to tell "no live price available"
     *  apart from "the live price happens to equal this row's own price". */
    public function onSellCanceled(\App\BotOrder $row, ?string $price): void;

    /**
     * Base-asset inventory this run currently holds: this engine's own open
     * position PLUS legacy exits (engine-independent — guarded by whichever
     * engine is running, per the class docblock). Feeds the shell's
     * unrealized-loss stop, the soft de-risk stage, and the orphaned-ledger
     * rebase check — all of which must see a trend position exactly as they
     * see grid inventory, or those rails run blind for one algorithm.
     */
    /** The shell placed this engine's exit for LESS than it asked (floored to
     *  the lot step, or shrunk to what the account holds — Daemon::
     *  placeShrunkExit): hold, and later book, what the exit carries. */
    public function onExitResized(int $levelIdx, string $placedQty): void;

    public function heldQty(): string;

    /**
     * Last call the OUTGOING engine gets before a cutover erases its state:
     * hand off any inventory this engine holds that nothing else will guard.
     *
     * The shell's cutover cancels open entries and carries working exits as
     * legacy exits, which covers every engine whose position IS its open book.
     * An engine that tracks a position in `engine_state` instead (Trend) holds
     * inventory that survives only in a column the cutover is about to reset:
     * unless it is re-expressed as a legacy exit here, it is abandoned outright
     * — invisible to the incoming engine's heldQty() (so the unrealized stop
     * and the drawdown rails run blind on it), unreserved (so the incoming
     * ladder re-spends the same capital), and unguarded (nothing will ever
     * sell it).
     *
     * Hand off only what is NOT already guarded: this runs BEFORE the shell
     * carries working exits, so an exit still working for this position covers
     * its own qty and must not be handed off a second time. Handing off at COST
     * (the position's own entry) is the rule: an algorithm flip is an operator
     * action, not a trading signal, so it must never realize a loss on its own
     * — if the market is already above cost the exchange fills the exit
     * immediately at the better price anyway.
     */
    public function handoffUnguardedPosition(): void;

    /**
     * Sell whatever this engine holds that is NOT already represented by a
     * working exit, at $price — the engine half of kill+flatten
     * (Daemon::flattenInventory has already repriced the working exits to the
     * mark). An engine whose position IS its open book has nothing to do here;
     * one that tracks a position in engine_state (Trend) owns the only record
     * of that inventory and must place its exit itself.
     *
     * The loss rules bind here too: an engine that would realize a loss with
     * grid_run.sell_at_loss OFF holds instead (TrendEngine::mayExitAt), same
     * as it does for a stop-out.
     */
    public function liquidate(string $price): void;

    /** One-time cutover INTO this engine when run.algo changed since last boot.
     *  The shell has already torn the previous engine's book down (open buys
     *  canceled, working sells carried as legacy exits, engine_state reset) —
     *  this is the incoming engine's own re-initialization. */
    public function cutover(): void;
}
