<?php

namespace App;

use App\Domains\Bot\BudgetGuard;
use App\Domains\Bot\MechanicalRefit;
use App\Domains\Bot\ProfilePolicy;
use App\Domains\Bot\RunLifecycle;

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

        // Mechanical geometry ownership: in mechanical mode the cron owns
        // p_low/p_high/n_levels/spacing (the tool refuses geometry writes, but
        // crm_update / the GUI bypass the tool and used to rewrite the ladder
        // under the cron). Enforce the same rule here so EVERY save path
        // respects the mode: an existing run may only change those four
        // columns to their persisted values (i.e. deploy-only edits).
        //
        // Scoped to the runs the cron actually writes (bin/gtbot-refit: algo
        // Grid, status Live/Testnet, kill switch off). Anything else — a Draft
        // grid being set up, a Trend run, a halted or kill-switched run whose
        // range needs fixing before it can restart — has NO other writer in
        // mechanical mode, so locking it would leave the operator with no way
        // to edit it short of flipping the global config.
        $geomKeys = ['PLow' => true, 'PHigh' => true, 'NLevels' => true, 'Spacing' => true];
        if (!$isNew
            && $e instanceof GridRun
            && ($pristine ?? null) !== null
            && array_intersect_key($data, $geomKeys) !== []
            && (string) ($pristine->getAlgo() ?: 'Grid') === 'Grid'
            && in_array((string) $pristine->getStatus(), ['Live', 'Testnet'], true)
            && !$pristine->getKillSwitch()
            && MechanicalRefit::isMechanical()) {
            $geom = [
                'PLow' => ['persisted' => (string) $pristine->getPLow(), 'label' => 'p_low', 'numeric' => true],
                'PHigh' => ['persisted' => (string) $pristine->getPHigh(), 'label' => 'p_high', 'numeric' => true],
                'NLevels' => ['persisted' => (string) (int) $pristine->getNLevels(), 'label' => 'n_levels', 'numeric' => true],
                'Spacing' => ['persisted' => (string) $pristine->getSpacing(), 'label' => 'spacing', 'numeric' => false],
            ];
            foreach ($geom as $col => $meta) {
                if (!array_key_exists($col, $data)) {
                    continue;
                }
                $incoming = (string) ($col === 'NLevels' ? (int) $data[$col] : $data[$col]);
                if ($meta['numeric']) {
                    // bcmath takes plain decimal notation ONLY: is_numeric is
                    // too lax ('1e3' passes it AND an <input type=number>, so
                    // does ' 100'), and bccomp throws a ValueError on those.
                    // A malformed value is the column validators' business.
                    if (!preg_match('/^[+-]?(\d+(\.\d*)?|\.\d+)$/', $incoming)) {
                        continue;
                    }
                    $changed = bccomp($incoming, $meta['persisted'], 8) !== 0;
                } else {
                    $changed = $incoming !== $meta['persisted']; // enum, never bccomp
                }
                if ($changed) {
                    $extValidationErr = is_array($extValidationErr) ? $extValidationErr : [];
                    $extValidationErr[sprintf(
                        'grid geometry is MECHANICAL (config %s): the cron owns %s — change deploy_pct only, or switch the mode back to routine',
                        MechanicalRefit::CONFIG_MODE,
                        $meta['label']
                    )]['fields'] = [$col];
                    return;
                }
            }
        }

        // Lifecycle-owned statuses. Done and Retiring may only be written by
        // RunLifecycle. Otherwise a crm_update or the GUI status dropdown can
        // archive a run that still holds coins — it then vanishes from
        // DrawdownGuard::equity(), ModeSwitch and every dashboard query while
        // its inventory keeps existing, and equity() counts that base asset as
        // ZERO (fail-closed), so the drawdown floor can trip on a phantom loss.
        if (!RunLifecycle::inTransition()) {
            $wanted = (string) ($data['Status'] ?? '');
            $current = $e instanceof GridRun && !$isNew ? (string) $e->getStatus() : '';
            $owned = ['Done', 'Retiring'];
            if (in_array($wanted, $owned, true)) {
                $extValidationErr = is_array($extValidationErr) ? $extValidationErr : [];
                $extValidationErr[sprintf(
                    'status %s is set by the run lifecycle, not by hand — use gtbot_retire_run (it stops entries, returns the slice to the pool and refuses to archive a run that still holds coins)',
                    $wanted
                )]['fields'] = ['Status'];
                return;
            }
            if ($wanted !== '' && in_array($current, $owned, true)) {
                $extValidationErr = is_array($extValidationErr) ? $extValidationErr : [];
                $extValidationErr[sprintf(
                    'run is %s and cannot be moved back to %s — a retired run stays retired; create a new run instead',
                    $current,
                    $wanted
                )]['fields'] = ['Status'];
                return;
            }
        }

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

    /**
     * API / MCP delete path (Api::deleteJson calls this by method_exists).
     *
     * $messages is by-ref but carries no refusal channel — the delete proceeds
     * regardless — so only a throw actually stops it. Api wraps this in a
     * catch(\Exception) that prefixes the text, hence the self-contained
     * capitalised sentence.
     *
     * @param \App\GridRun|null $e
     */
    public function beforeDelete($e, $data, &$messages): void
    {
        if (!$e instanceof GridRun) {
            return;
        }
        $verdict = RunLifecycle::mayPurge($e);
        if (!$verdict['ok']) {
            $messages = is_array($messages) ? $messages : [];
            $messages[] = $verdict['message'];
            throw new \RuntimeException($verdict['message']);
        }
    }

    /**
     * GUI trash icon. The generated deleteOne() goes straight to $obj->delete()
     * and never calls beforeDelete, so the guard has to be repeated here.
     */
    public function deleteOne()
    {
        $obj = $_SESSION[_AUTH_VAR]->loadPkScoped(GridRunQuery::class, json_decode($this->request['i']), 'GridRun', 'd');
        if ($obj instanceof GridRun) {
            $verdict = RunLifecycle::mayPurge($obj);
            if (!$verdict['ok']) {
                $error = handleNotOkResponse($verdict['message'], '', true, 'Grid Run');
                $this->halt($error['onReadyJs']);
            }
        }
        return parent::deleteOne();
    }
}

