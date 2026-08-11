<?php

namespace App\Domains\Bot;

/**
 * Rebuilds the in-memory LevelStateMachine from persisted bot_order rows —
 * the crash-recovery half of the state store. An open sell restores the held
 * inventory it is backed by, so the inventory invariant survives restarts.
 */
class GridStateHydrator
{
    /**
     * @param string[] $levels    n+1 grid prices
     * @param string[] $qtys      per-buy-level quantities
     * @param array    $orderRows [['level_idx' =>, 'side' =>, 'state' =>], ...]
     */
    public static function machine(array $levels, array $qtys, array $orderRows): LevelStateMachine
    {
        $m = new LevelStateMachine($levels, $qtys);
        foreach ($orderRows as $row) {
            // Legacy exits belong to a PREVIOUS geometry — their level index
            // is meaningless on this ladder. The daemon tracks them off-machine.
            if (!empty($row['is_legacy'])) {
                continue;
            }
            $state = $row['state'];
            // A PartFilled order is still working on the book — hydrate it by
            // side (forgetting it would re-arm the level → duplicate order).
            if ($state === 'PartFilled') {
                $state = ($row['side'] ?? 'Buy') === 'Sell' ? 'SELL_OPEN' : 'BUY_OPEN';
            }
            if ($state === 'BUY_OPEN' || $state === 'SELL_OPEN') {
                // open sells carry their actual row qty (partial-booked buys
                // back a smaller sell than the level's standard qty)
                $m->hydrateLevel((int) $row['level_idx'], $state, $state === 'SELL_OPEN' ? ($row['qty'] ?? null) : null);
            }
            // Filled/Canceled/Vetoed rows are history — the level's live state
            // is defined solely by its open order (or the absence of one).
        }
        return $m;
    }
}
