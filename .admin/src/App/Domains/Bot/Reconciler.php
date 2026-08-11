<?php

namespace App\Domains\Bot;

/**
 * Pure diff between what the DB believes is open and what the exchange
 * actually has. Returns actions; the daemon executes them BEFORE placing
 * anything new (never trade on unreconciled state — that's how bots end up
 * with duplicate ladders).
 *
 * Actions:
 *   ['check_status', cid]  db-open but gone from the exchange → filled or
 *                          canceled; resolve via GET /order and apply.
 *   ['adopt', cid]         ours by cid prefix but unknown to the DB (crash
 *                          between place and record) → write the missing row.
 *   ['alert_foreign', cid] not ours → alert, never touch. Likely a second
 *                          instance or manual trading on the bot account.
 */
class Reconciler
{
    public static function plan(array $dbOpen, array $exchangeOpen, string $cidPrefix): array
    {
        $exCids = [];
        foreach ($exchangeOpen as $o) {
            $exCids[$o['clientOrderId']] = true;
        }
        $dbCids = [];
        foreach ($dbOpen as $o) {
            $dbCids[$o['client_order_id']] = true;
        }

        $actions = [];
        foreach ($dbOpen as $o) {
            if (!isset($exCids[$o['client_order_id']])) {
                $actions[] = ['check_status', $o['client_order_id']];
            }
        }
        foreach ($exchangeOpen as $o) {
            $cid = $o['clientOrderId'];
            if (isset($dbCids[$cid])) {
                continue;
            }
            $actions[] = str_starts_with($cid, $cidPrefix)
                ? ['adopt', $cid]
                : ['alert_foreign', $cid];
        }
        return $actions;
    }
}
