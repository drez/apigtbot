<?php

namespace App\Domains\Bot\Engine;

use App\Domains\Bot\Daemon;

/**
 * Guard engine for an algo that has no implementation on this deployment
 * (today: run.algo = Trend before the TrendEngine ships). It never trades:
 * boot says so ONCE, loudly (Error → the event feed / Telegram), and every
 * tick is a no-op so the daemon keeps heartbeating instead of crash-looping
 * or — far worse — silently falling back to a different strategy on a run
 * whose orders were just torn down by the cutover.
 *
 * Legacy exits placed by the previous engine keep being resolved by the shell,
 * so held inventory is still guarded while an operator fixes the run.
 */
final class MissingEngine implements StrategyEngine
{
    public function __construct(private readonly Daemon $daemon, private readonly string $algo) {}

    public function boot(): void
    {
        $this->daemon->log->write('Error', 'engine_missing', sprintf(
            'run algo=%s has no engine on this deployment — the daemon is idling (no entries, no exits placed); existing exits keep working. Switch the run back to Grid or deploy the %s engine',
            $this->algo,
            $this->algo
        ));
    }

    public function tick(string $price, bool $entriesGated): void
    {
    }

    public function onBuyFill(\App\BotOrder $row, string $executed, string $fee, string $price): void
    {
        $this->daemon->log->write('Alert', 'engine_missing', sprintf(
            'buy %s filled but algo=%s has no engine — the fill is booked in the ledger and NOT exited; review manually',
            $row->getClientOrderId(),
            $this->algo
        ));
    }

    public function onSellFill(\App\BotOrder $row, string $executed, string $fee, string $price): void
    {
        $this->daemon->log->write('Alert', 'engine_missing', sprintf(
            'sell %s filled but algo=%s has no engine — no cycle booked; review manually',
            $row->getClientOrderId(),
            $this->algo
        ));
    }

    public function onSellCanceled(\App\BotOrder $row, ?string $price): void
    {
        $this->daemon->log->write('Alert', 'engine_missing', sprintf(
            'sell %s was canceled but algo=%s has no engine — the exit is NOT re-placed; review manually',
            $row->getClientOrderId(),
            $this->algo
        ));
    }

    /** No engine of its own to track a position with — only whatever legacy
     *  exits the shell is already guarding independently of any engine. */
    public function heldQty(): string
    {
        return $this->daemon->store->legacyRemainingQty();
    }

    /** This guard never opened a position of its own, and whatever the
     *  missing engine's state column holds is unreadable to it BY DEFINITION
     *  — it has no idea which keys mean what for a foreign algo. Legacy exits
     *  already on the book are engine-independent and keep working across the
     *  cutover regardless. But a `qty` key (the convention every real engine
     *  uses for its own held position) surviving under a marker this
     *  deployment cannot interpret means real inventory may be sitting there
     *  with nothing about to guard it — that must never pass silently. */
    public function handoffUnguardedPosition(): void
    {
        $state = json_decode((string) ($this->daemon->run->getEngineState() ?? ''), true);
        $qty = is_array($state) ? ($state['qty'] ?? null) : null;
        if ($qty === null) {
            return;
        }
        $this->daemon->log->write('Alert', 'algo_cutover', sprintf(
            'cannot hand off a position from an uninterpretable engine state (algo=%s, qty=%s) — inventory unaccounted, human review',
            $this->algo,
            (string) $qty
        ));
    }

    public function cutover(): void
    {
    }
}
