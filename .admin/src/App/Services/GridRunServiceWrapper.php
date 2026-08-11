<?php

namespace App;

use App\Domains\Bot\BudgetGuard;
use App\Domains\Bot\ProfilePolicy;

/**
 * Enforces the shared-budget invariant and stamps profile-derived risk caps on
 * every GridRun write (operator directive: never overcommit; live risk profile).
 * The runtime routes GUI requests here automatically (RouteHelper::getService
 * prefers <Model>ServiceWrapper) and the API/MCP path calls beforeSave() by
 * method_exists — so a save that would push the active runs' budget slices
 * above the shared wallet is refused on ALL paths with the same message. The
 * daemon's per-tick entry gate (Daemon::checkBudgetInvariant) backstops
 * anything that bypasses the service layer entirely. Profile changes stamp
 * atomically via ProfilePolicy::caps on the API path ($data) and
 * ProfilePolicy::apply on the GUI path ($e).
 */
class GridRunServiceWrapper extends GridRunService
{
    /**
     * Model-first hook convention (see Parameters/add_hooks.php): the GUI
     * splices this after $e carries the posted values; the API path calls it
     * BEFORE applying $data to the model — so candidates resolve from $data
     * first, falling back to the model.
     *
     * @param \App\GridRun|null $e
     */
    public function beforeSave($e, &$data, $isNew, &$messages, &$extValidationErr, &$error): void
    {
        $data = (array) $data;

        // Daemon-managed column: engine_state is declared set_readonly_columns
        // in schema (form-locked + documented API-exclusion), but the
        // generated Api's editable-field allowlist reads the DATABASE-level
        // GoatCheese behavior instead of this table's own (a goatcheese
        // emitter bug — ApiSupport::buildEditableFields consults
        // apigoatDbBehavior, not apigoatBehavior), so set_readonly_columns
        // does not actually strip it from API/MCP writes. Enforce it here
        // until that's fixed upstream: only the daemon (direct Propel
        // setEngineState()) may write this column.
        //
        // The $data strip alone only protects the API/MCP path (Api::setEntry
        // calls beforeSave BEFORE applying $data to the model). The GUI path
        // is the opposite order: Form::setUpdateDefaultsGridRun already ran
        // $e->fromArray($data) — no allowlist — BEFORE beforeSave is called,
        // so a crafted EngineState in $data is already sitting on $e by the
        // time we get here and $data alone can't undo that. Reset the column
        // on the MODEL back to its persisted value (or null for a brand new
        // row, which has no persisted value to restore).
        unset($data['EngineState']);
        if ($e instanceof GridRun) {
            if ($isNew) {
                $e->setEngineState(null);
            } else {
                $pk = (int) $e->getIdGridRun();
                // findPk() alone would return $e itself (or an alias of it)
                // out of Propel's instance pool — already polluted by
                // fromArray() — instead of a genuine DB read. Evict first so
                // this is a real SELECT.
                GridRunPeer::removeInstanceFromPool($pk);
                $pristine = $pk > 0 ? GridRunQuery::create()->findPk($pk) : null;
                $e->setEngineState($pristine ? $pristine->getEngineState() : null);
            }
        }

        $budget = array_key_exists('BudgetQuote', $data) ? (string) $data['BudgetQuote'] : (string) $e?->getBudgetQuote();
        $status = array_key_exists('Status', $data) ? (string) $data['Status'] : (string) $e?->getStatus();
        $excludeId = (!$isNew && $e && $e->getIdGridRun()) ? (int) $e->getIdGridRun() : null;
        if ($budget === '' || !is_numeric($budget)) {
            return; // column validators own malformed input
        }
        $over = BudgetGuard::check($excludeId, $budget, $status !== '' ? $status : null);
        if ($over !== null) {
            $extValidationErr = is_array($extValidationErr) ? $extValidationErr : [];
            $extValidationErr[BudgetGuard::message($over)]['fields'] = ['BudgetQuote'];
            return; // refused — don't bother stamping
        }

        // Live risk profile: derive the effective cap columns from
        // profile × slice on every save, so a profile or slice change from
        // the GUI/API stamps atomically (the daemon re-stamps each tick).
        // Hoist profile resolution before Trend×NoLoss check
        $profile = array_key_exists('Profile', $data)
            ? (string) $data['Profile']
            : (string) ($e?->getProfile() ?: 'Balanced');

        // Reject Trend algorithm with NoLoss profile — a trend algo must realize stop losses
        $algo = array_key_exists('Algo', $data) ? (string) $data['Algo'] : (string) ($e?->getAlgo() ?: 'Grid');
        if ($algo === 'Trend' && $profile === 'NoLoss') {
            $extValidationErr = is_array($extValidationErr) ? $extValidationErr : [];
            $extValidationErr['algo Trend cannot run under profile NoLoss — a trend algo must realize stop losses; pick Cautious or higher']['fields'] = ['Algo'];
            return;
        }

        if (in_array($profile, ProfilePolicy::PROFILES, true)) {
            foreach (ProfilePolicy::caps($profile, $budget) as $col => $val) {
                $data[$col] = $val;
            }
            foreach (ProfilePolicy::trendCaps($profile) as $col => $val) {
                $data[$col] = $val;
            }
            // GUI path: also stamp caps onto $e so saveUpdate saves them directly
            // (cap columns are readonly in the form, so $data doesn't carry them).
            // Only touch Profile/BudgetQuote from $data when they exist (posted);
            // never overwrite from stale model state.
            if ($e instanceof GridRun) {
                if (array_key_exists('Profile', $data)) {
                    $e->setProfile($data['Profile']);
                }
                if (array_key_exists('BudgetQuote', $data)) {
                    $e->setBudgetQuote($data['BudgetQuote']);
                }
                ProfilePolicy::apply($e);
            }
        }
    }
}
