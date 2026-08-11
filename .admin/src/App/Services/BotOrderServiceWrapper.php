<?php

namespace App;

/**
 * Daemon-managed columns: legacy_buy_price/legacy_buy_fee (the pinned
 * buy-attribution on a legacy exit — see Daemon::placeLegacyExit) are
 * declared set_readonly_columns in schema (form-locked + documented
 * API-exclusion), but the generated Api's editable-field allowlist reads the
 * DATABASE-level GoatCheese behavior instead of this table's own (a
 * goatcheese emitter bug — ApiSupport::buildEditableFields consults
 * apigoatDbBehavior, not apigoatBehavior), so set_readonly_columns does not
 * actually strip them from API/MCP writes. Enforce it here until that's
 * fixed upstream: only the daemon (direct Propel setters) may write these.
 */
class BotOrderServiceWrapper extends BotOrderService
{
    /**
     * Model-first hook convention (see Parameters/add_hooks.php).
     *
     * @param \App\BotOrder|null $e
     */
    public function beforeSave($e, &$data, $isNew, &$messages, &$extValidationErr, &$error): void
    {
        $data = (array) $data;

        // The $data strip alone only protects the API/MCP path (Api::setEntry
        // calls beforeSave BEFORE applying $data to the model). The GUI path
        // is the opposite order: Form::setUpdateDefaultsBotOrder already ran
        // $e->fromArray($data) — no allowlist — BEFORE beforeSave is called,
        // so a crafted LegacyBuyPrice/LegacyBuyFee in $data is already sitting
        // on $e by the time we get here and $data alone can't undo that.
        // Reset both columns on the MODEL back to their persisted values (or
        // null for a brand new row, which has no persisted value to restore).
        unset($data['LegacyBuyPrice'], $data['LegacyBuyFee']);
        if ($e instanceof BotOrder) {
            if ($isNew) {
                $e->setLegacyBuyPrice(null);
                $e->setLegacyBuyFee(null);
            } else {
                $pk = (int) $e->getIdBotOrder();
                // findPk() alone would return $e itself (or an alias of it)
                // out of Propel's instance pool — already polluted by
                // fromArray() — instead of a genuine DB read. Evict first so
                // this is a real SELECT.
                BotOrderPeer::removeInstanceFromPool($pk);
                $pristine = $pk > 0 ? BotOrderQuery::create()->findPk($pk) : null;
                $e->setLegacyBuyPrice($pristine ? $pristine->getLegacyBuyPrice() : null);
                $e->setLegacyBuyFee($pristine ? $pristine->getLegacyBuyFee() : null);
            }
        }
    }
}
