<?php

namespace App\Domains\Bot;

/**
 * Per-level state machine for a long-only grid (§4.4 of the plan):
 *
 *   EMPTY → BUY_OPEN → SELL_OPEN → BUY_OPEN (cycle)
 *
 * Level i's buy sits at levels[i]; its matched sell at levels[i+1]. The top
 * grid line is only ever a sell target. The machine is pure — it emits
 * IntendedOrder values and never touches network or DB. Invariant: open sell
 * quantity always equals held inventory (checked by openSellQty/heldInventory).
 */
class LevelStateMachine
{
    private const SCALE = 12;

    /** @var string[] n+1 grid line prices */
    private array $levels;
    /** @var string[] qty per buy level (n entries) */
    private array $qtys;
    /** @var string[] state per buy level: EMPTY | BUY_OPEN | SELL_OPEN */
    private array $states;
    /** @var string[] held base qty per level */
    private array $held;

    /**
     * @param string[] $levels n+1 ascending prices
     * @param string[] $qtys   per-buy-level quantities (n entries)
     */
    public function __construct(array $levels, array $qtys)
    {
        if (count($qtys) !== count($levels) - 1) {
            throw new \InvalidArgumentException('need one qty per buy level (levels - 1)');
        }
        $this->levels = array_values($levels);
        $this->qtys = array_values($qtys);
        $this->states = array_fill(0, count($qtys), 'EMPTY');
        $this->held = array_fill(0, count($qtys), '0');
    }

    /**
     * Arm buys at every level strictly below the current price (and at or
     * above $minLevel — the distance-from-price window). Levels at/above
     * price stay EMPTY — a long-only grid never pre-sells inventory it
     * doesn't hold.
     *
     * @return IntendedOrder[]
     */
    public function initialIntents(string $currentPrice, int $minLevel = 0): array
    {
        $intents = [];
        foreach ($this->qtys as $i => $qty) {
            if ($i >= $minLevel && $this->states[$i] === 'EMPTY' && bccomp($this->levels[$i], $currentPrice, self::SCALE) < 0) {
                $this->states[$i] = 'BUY_OPEN';
                $intents[] = new IntendedOrder('Buy', $i, $this->levels[$i], $qty);
            }
        }
        return $intents;
    }

    public function state(int $level): string
    {
        return $this->states[$level];
    }

    /**
     * Restore a level's state from persistence (GridStateHydrator only).
     * SELL_OPEN re-establishes the held inventory backing that sell so the
     * inventory invariant survives a restart; $heldQty carries the actual
     * open-sell quantity when it differs from the level's standard qty
     * (partial-booked buys).
     */
    public function hydrateLevel(int $i, string $state, ?string $heldQty = null): void
    {
        if (!in_array($state, ['EMPTY', 'BUY_OPEN', 'SELL_OPEN'], true)) {
            throw new \InvalidArgumentException("cannot hydrate level $i to $state");
        }
        $this->states[$i] = $state;
        $this->held[$i] = $state === 'SELL_OPEN' ? ($heldQty ?? $this->qtys[$i]) : '0';
    }

    /**
     * A stuck partial buy at level $i was cancelled and its filled portion
     * booked: hold the ACTUAL executed qty, arm the matched sell for it.
     */
    public function onPartialBuyBooked(int $i, string $executedQty): IntendedOrder
    {
        if (($this->states[$i] ?? null) !== 'BUY_OPEN') {
            throw new \LogicException("partial booking on level $i in state " . ($this->states[$i] ?? 'unknown'));
        }
        $this->states[$i] = 'SELL_OPEN';
        $this->held[$i] = $executedQty;
        return new IntendedOrder('Sell', $i, $this->levels[$i + 1], $executedQty);
    }

    /** A buy at level $i filled: hold the inventory, arm the matched sell. */
    public function onBuyFill(int $i): IntendedOrder
    {
        if (($this->states[$i] ?? null) !== 'BUY_OPEN') {
            throw new \LogicException("buy fill on level $i in state " . ($this->states[$i] ?? 'unknown'));
        }
        $this->states[$i] = 'SELL_OPEN';
        $this->held[$i] = $this->qtys[$i];
        return new IntendedOrder('Sell', $i, $this->levels[$i + 1], $this->qtys[$i]);
    }

    /**
     * The sell for level $i filled: realize the cycle, re-arm the buy.
     *
     * @return array{cycle: array<string,string>, rearm: IntendedOrder}
     */
    public function onSellFill(int $i, string $feesTotal = '0'): array
    {
        if (($this->states[$i] ?? null) !== 'SELL_OPEN') {
            throw new \LogicException("sell fill on level $i in state " . ($this->states[$i] ?? 'unknown'));
        }
        $qty = $this->held[$i]; // actual backing qty (≠ level qty after a partial booking)
        $gross = bcmul($qty, bcsub($this->levels[$i + 1], $this->levels[$i], self::SCALE), self::SCALE);
        $cycle = [
            'level_idx' => (string) $i,
            'buy_price' => $this->levels[$i],
            'sell_price' => $this->levels[$i + 1],
            'qty' => $qty,
            'gross_pnl' => $gross,
            'fees_total' => $feesTotal,
            'realized_pnl' => bcsub($gross, $feesTotal, self::SCALE),
        ];
        $this->held[$i] = '0';
        $this->states[$i] = 'BUY_OPEN';
        return [
            'cycle' => $cycle,
            // re-arm at the standard level qty, not the (possibly partial) cycle qty
            'rearm' => new IntendedOrder('Buy', $i, $this->levels[$i], $this->qtys[$i]),
        ];
    }

    /** Total base qty committed to open sells (actual per-level backing). */
    public function openSellQty(): string
    {
        $sum = '0';
        foreach ($this->states as $i => $s) {
            if ($s === 'SELL_OPEN') {
                $sum = bcadd($sum, $this->held[$i], self::SCALE);
            }
        }
        return $sum;
    }

    /** Total base inventory currently held. Must always equal openSellQty(). */
    public function heldInventory(): string
    {
        $sum = '0';
        foreach ($this->held as $h) {
            $sum = bcadd($sum, $h, self::SCALE);
        }
        return $sum;
    }
}
