<?php

namespace App\Domains\Bot;

use App\BotOrderQuery;
use App\ConfigQuery;

/**
 * The ONE paper wallet all runs share (mirrors the single real exchange
 * account). Source of truth is the sim_wallet table, never process memory —
 * four daemons mutate it concurrently, so every write runs in a transaction
 * with SELECT ... FOR UPDATE. The wallet is DERIVED from the global simulated
 * ledger (budget minus a replay of every simulated fill after the era epoch),
 * which makes it self-healing: a crash between an in-memory fill and the
 * Daemon's DB record can never double-count.
 *
 * All mutations (applyDelta, deriveAndStore) additionally serialize behind
 * the 'gtbot-sim-wallet' MySQL advisory lock (GET_LOCK/RELEASE_LOCK), taken
 * for the ENTIRE operation including deriveAndStore's ledger read. Row-level
 * FOR UPDATE locking alone isn't enough: it can't stop two processes from
 * both inserting a brand-new asset row, nor stop a concurrent applyDelta
 * from landing between deriveAndStore's read and its DELETE+INSERT
 * overwrite. The advisory lock closes both gaps. GET_LOCK is per-connection
 * and re-entrant for the same session, so it never conflicts with the
 * daemon's own 'gtbot-run-N' lock.
 *
 * A boot-time derivation that runs between another daemon's fill delta and
 * that fill's bot_order row being marked Filled will briefly drop that one
 * fill's delta; the next derivation re-includes it once the row is Filled —
 * display-only drift, never double-counted, and no risk rail reads this
 * wallet.
 *
 * Config: gtbot_shared_budget_quote (default 1000) seeds the USDT pool;
 * gtbot_sim_wallet_epoch starts the paper era. USDT-quoted pairs assumed.
 */
class SimWallet
{
    private const SCALE = 12;

    /** JSON: {"adjust": {asset: signed qty}, "sealed": [run ids]} */
    public const CONFIG_BASELINE = 'gtbot_sim_wallet_baseline';

    /** JSON: {"BTC": "0.01"} — tokens the operator put INTO the shared pool */
    public const CONFIG_SEED_ASSETS = 'gtbot_shared_seed_assets';

    public static function sharedBudget(): string
    {
        $v = ConfigQuery::create()->findOneByConfig('gtbot_shared_budget_quote')?->getValue();
        return ($v !== null && $v !== '') ? (string) $v : '1000';
    }

    public static function epoch(): ?string
    {
        $v = ConfigQuery::create()->findOneByConfig('gtbot_sim_wallet_epoch')?->getValue();
        return ($v !== null && $v !== '') ? (string) $v : null;
    }

    /**
     * Opening balances the derivation starts from, on top of the USDT seed.
     *
     * The wallet is DERIVED: deriveAndStore() wipes sim_wallet and replays the
     * ledger. That is what makes it self-healing, and it is also why deleting a
     * run used to silently rewrite history — the cascade takes that run's
     * bot_order rows with it, so the next replay hands back everything the run
     * had lost. sealRun() folds a departing run's net contribution in here
     * before its rows go, so the replay still starts from the right place.
     *
     * `sealed` is the idempotence guard: a purge that dies after the config
     * write and is retried must not fold the same run in twice.
     *
     * @return array{adjust: array<string, string>, sealed: int[]}
     */
    public static function baseline(): array
    {
        $raw = (string) (ConfigQuery::create()->findOneByConfig(self::CONFIG_BASELINE)?->getValue() ?? '');
        if ($raw === '') {
            return ['adjust' => [], 'sealed' => []];
        }
        $p = json_decode($raw, true);
        if (!is_array($p)) {
            error_log('gtbot: gtbot_sim_wallet_baseline is not valid JSON — ignoring it');
            return ['adjust' => [], 'sealed' => []];
        }
        $adjust = [];
        foreach ((array) ($p['adjust'] ?? []) as $asset => $qty) {
            if (is_string($asset) && is_scalar($qty) && is_numeric((string) $qty)) {
                $adjust[$asset] = (string) $qty;
            }
        }
        $sealed = [];
        foreach ((array) ($p['sealed'] ?? []) as $id) {
            if (is_numeric($id)) {
                $sealed[] = (int) $id;
            }
        }
        return ['adjust' => $adjust, 'sealed' => $sealed];
    }

    /** @param array{adjust: array<string, string>, sealed: int[]} $baseline */
    private static function saveBaseline(array $baseline): void
    {
        $row = ConfigQuery::create()->findOneByConfig(self::CONFIG_BASELINE);
        if ($row === null) {
            $row = new \App\Config();
            $row->setConfig(self::CONFIG_BASELINE);
            // category/system are ENUMs: Propel's setters take the LABEL, not
            // the ordinal the seed SQL writes
            $row->setCategory('General');
            $row->setSystem('y');
            $row->setType('string');
            $row->setDescription('Paper wallet opening balances: the net contribution of purged runs, folded in so the ledger replay still starts from the right place. Written by SimWallet::sealRun(); do not hand-edit.');
            $row->setDateCreation(date('Y-m-d H:i:s'));
        }
        $row->setValue(json_encode($baseline, JSON_UNESCAPED_SLASHES));
        $row->save();
        // the next baseline() read would otherwise be served the pooled row
        \App\ConfigPeer::clearInstancePool();
    }

    /**
     * Non-USDT tokens seeded into the shared pool.
     *
     * The wallet is DERIVED — deriveAndStore() wipes sim_wallet and rebuilds it
     * from the USDT seed plus a replay of every simulated fill — so a row
     * inserted by hand is erased at the next daemon boot, and the table is
     * set_readonly_columns in schema so there is no GUI edit either. A token
     * therefore has to enter through this seed layer to survive at all.
     *
     * @return array<string, string> asset => qty
     */
    public static function seedAssets(): array
    {
        $raw = (string) (ConfigQuery::create()->findOneByConfig(self::CONFIG_SEED_ASSETS)?->getValue() ?? '');
        if ($raw === '') {
            return [];
        }
        $p = json_decode($raw, true);
        if (!is_array($p)) {
            error_log('gtbot: ' . self::CONFIG_SEED_ASSETS . ' is not valid JSON — ignoring it');
            return [];
        }
        $out = [];
        foreach ($p as $asset => $qty) {
            if (is_string($asset) && $asset !== '' && is_scalar($qty) && is_numeric((string) $qty)
                && bccomp((string) $qty, '0', self::SCALE) > 0) {
                $out[strtoupper($asset)] = (string) $qty;
            }
        }
        return $out;
    }

    /**
     * The quote assets a symbol may end in, LONGEST FIRST — the order is the
     * algorithm (see assetsFor): 'BTCUSDC' must match USDC before it can
     * match anything shorter.
     */
    public const QUOTE_ASSETS = ['FDUSD', 'USDT', 'USDC', 'TUSD', 'BUSD', 'DAI', 'EUR', 'TRY', 'BTC', 'ETH', 'BNB'];

    /**
     * [base, quote] from a symbol — the ONE parser for the fleet.
     *
     * A known-quote list, longest match first. The old rule was "USDT, else
     * the last THREE characters", which is right for every pair the fleet has
     * ever traded and silently wrong for the next one: BTCUSDC split into base
     * 'BTCU' / quote 'SDC' (2026-09-21 review). Nothing refuses that — it
     * propagates. AccountAudit then compares the account's (absent) 'BTCU'
     * balance against inventory it does find and reports a permanent phantom
     * inventory_missing, and DrawdownGuard::equity prices the same phantom
     * base, counting REAL inventory as 0 (fail-closed) and walking equity
     * toward the drawdown floor that kills the fleet.
     *
     * An unknown quote falls back to the old three-character guess: a bad
     * split is still better than a crash on a symbol nobody listed yet.
     */
    public static function assetsFor(string $symbol): array
    {
        $symbol = strtoupper($symbol);
        foreach (self::QUOTE_ASSETS as $quote) {
            if (strlen($symbol) > strlen($quote) && str_ends_with($symbol, $quote)) {
                return [substr($symbol, 0, strlen($symbol) - strlen($quote)), $quote];
            }
        }
        $quote = substr($symbol, -3);
        return [substr($symbol, 0, strlen($symbol) - strlen($quote)), $quote];
    }

    /** @return array<string, string> asset => qty */
    public static function balances(): array
    {
        $out = [];
        $stmt = \Propel::getConnection()->query('SELECT asset, qty FROM sim_wallet');
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $out[(string) $r['asset']] = (string) $r['qty'];
        }
        return $out;
    }

    /** Serialize every wallet mutation across all daemon processes. The
     *  advisory lock (not just row locks) is what makes deriveAndStore's
     *  read-ledger-then-overwrite span atomic w.r.t. concurrent fill deltas. */
    private static function withWalletLock(callable $fn): mixed
    {
        $con = \Propel::getConnection();
        $lockStmt = $con->prepare("SELECT GET_LOCK('gtbot-sim-wallet', 10)");
        $lockStmt->execute();
        $got = $lockStmt->fetchColumn();
        if ((int) $got !== 1) {
            throw new \RuntimeException('sim_wallet advisory lock timeout');
        }
        try {
            return $fn($con);
        } finally {
            $con->prepare("SELECT RELEASE_LOCK('gtbot-sim-wallet')")->execute();
        }
    }

    /**
     * Atomically add signed deltas: [asset => bc delta]. Missing rows are
     * created at 0 first. Returns the touched assets' new balances.
     */
    public static function applyDelta(array $deltas): array
    {
        return self::withWalletLock(function ($con) use ($deltas): array {
            $con->beginTransaction();
            try {
                $out = [];
                foreach ($deltas as $asset => $delta) {
                    $sel = $con->prepare('SELECT qty FROM sim_wallet WHERE asset = ? FOR UPDATE');
                    $sel->execute([$asset]);
                    $cur = $sel->fetchColumn();
                    if ($cur === false) {
                        $con->prepare('INSERT INTO sim_wallet (asset, qty, date_creation, date_modification) VALUES (?, 0, NOW(), NOW()) ON DUPLICATE KEY UPDATE asset = asset')
                            ->execute([$asset]);
                        $sel = $con->prepare('SELECT qty FROM sim_wallet WHERE asset = ? FOR UPDATE');
                        $sel->execute([$asset]);
                        $cur = $sel->fetchColumn();
                    }
                    $new = bcadd((string) $cur, (string) $delta, self::SCALE);
                    $con->prepare('UPDATE sim_wallet SET qty = ?, date_modification = NOW() WHERE asset = ?')
                        ->execute([$new, $asset]);
                    $out[(string) $asset] = $new;
                }
                $con->commit();
                return $out;
            } catch (\Throwable $e) {
                $con->rollBack();
                throw $e;
            }
        });
    }

    /**
     * Recompute the wallet from the global simulated ledger and overwrite the
     * table. Called at daemon boot — deterministic over the same ledger, so
     * concurrent boots converge on the same numbers.
     */
    public static function deriveAndStore(): array
    {
        return self::withWalletLock(function ($con): array {
            $wallet = ['USDT' => self::sharedBudget()];
            // tokens the operator put into the pool (see seedAssets())
            foreach (self::seedAssets() as $asset => $qty) {
                $wallet[$asset] = bcadd($wallet[$asset] ?? '0', $qty, self::SCALE);
            }
            // opening balances of runs that have been purged (see baseline())
            foreach (self::baseline()['adjust'] as $asset => $qty) {
                $wallet[$asset] = bcadd($wallet[$asset] ?? '0', $qty, self::SCALE);
            }
            foreach (self::replayDelta(null) as $asset => $delta) {
                $wallet[$asset] = bcadd($wallet[$asset] ?? '0', $delta, self::SCALE);
            }
            $con->beginTransaction();
            try {
                $con->exec('DELETE FROM sim_wallet');
                $ins = $con->prepare('INSERT INTO sim_wallet (asset, qty, date_creation, date_modification) VALUES (?, ?, NOW(), NOW())');
                foreach ($wallet as $asset => $qty) {
                    $ins->execute([$asset, $qty]);
                }
                $con->commit();
            } catch (\Throwable $e) {
                $con->rollBack();
                throw $e;
            }
            return $wallet;
        });
    }

    /**
     * Net wallet contribution of the simulated ledger, as signed per-asset
     * deltas. $onlyRunId scopes it to a single run.
     *
     * deriveAndStore() and runDelta() BOTH go through here on purpose: the
     * seal folds a run's delta into the baseline and the replay then omits
     * that run's rows, so the two arithmetics must agree exactly or the wallet
     * drifts by the difference every time a run is purged.
     *
     * @return array<string, string> asset => signed delta
     */
    private static function replayDelta(?int $onlyRunId): array
    {
        $symbols = self::runSymbols();
        $q = BotOrderQuery::create()
            ->filterBySimulated(true)
            ->filterByState('Filled');
        if ($onlyRunId !== null) {
            $q->filterByIdGridRun($onlyRunId);
        }
        $epoch = self::epoch();
        if ($epoch !== null) {
            $q->filterByDateCreation(['min' => $epoch]);
        }
        $rows = $q->select(['IdGridRun', 'Side', 'Price', 'FilledQty', 'FeePaid'])->find();

        $out = [];
        foreach ($rows as $row) {
            $symbol = $symbols[(int) $row['IdGridRun']] ?? null;
            if ($symbol === null) {
                continue; // run gone and not sealed — same posture as before
            }
            [$base, $quote] = self::assetsFor($symbol);
            $filled = (string) ($row['FilledQty'] ?: '0');
            $notional = bcmul((string) $row['Price'], $filled, self::SCALE);
            $fee = (string) ($row['FeePaid'] ?: '0');
            $out[$base] ??= '0';
            $out[$quote] ??= '0';
            // 'Side' comes back as the raw ENUM ordinal from select()
            if (self::isBuy($row['Side'])) {
                $out[$quote] = bcsub($out[$quote], bcadd($notional, $fee, self::SCALE), self::SCALE);
                $out[$base] = bcadd($out[$base], $filled, self::SCALE);
            } else {
                $out[$base] = bcsub($out[$base], $filled, self::SCALE);
                $out[$quote] = bcadd($out[$quote], bcsub($notional, $fee, self::SCALE), self::SCALE);
            }
        }
        return $out;
    }

    /** select() hands back ENUM ordinals, so accept both shapes. */
    private static function isBuy(mixed $side): bool
    {
        if (is_int($side) || ctype_digit((string) $side)) {
            return (\App\BotOrderPeer::getValueSet(\App\BotOrderPeer::SIDE)[(int) $side] ?? '') === 'Buy';
        }
        return (string) $side === 'Buy';
    }

    /** @return array<int, string> run id => symbol — one query, kills the N+1 */
    private static function runSymbols(): array
    {
        $out = [];
        foreach (\App\GridRunQuery::create()->select(['IdGridRun', 'Symbol'])->find() as $r) {
            $out[(int) $r['IdGridRun']] = (string) $r['Symbol'];
        }
        return $out;
    }

    /**
     * One run's net contribution to the shared wallet.
     *
     * @return array<string, string> asset => signed delta
     */
    public static function runDelta(\App\GridRun $run): array
    {
        return self::replayDelta((int) $run->getIdGridRun());
    }

    /**
     * Fold a run's contribution into the opening baseline so its bot_order
     * rows can be deleted without rewriting the shared wallet.
     *
     * MUST be called before the delete, never after: once the rows are gone
     * the delta is unknowable. Idempotent — a run already in `sealed` is a
     * no-op, which is what makes a retried purge safe.
     *
     * @return array{sealed: bool, delta: array<string, string>, reason?: string}
     */
    public static function sealRun(\App\GridRun $run): array
    {
        return self::withWalletLock(function () use ($run): array {
            $runId = (int) $run->getIdGridRun();
            $baseline = self::baseline();
            if (in_array($runId, $baseline['sealed'], true)) {
                return ['sealed' => false, 'delta' => [], 'reason' => 'already sealed'];
            }
            $delta = self::runDelta($run);
            foreach ($delta as $asset => $qty) {
                $baseline['adjust'][$asset] = bcadd($baseline['adjust'][$asset] ?? '0', $qty, self::SCALE);
            }
            $baseline['sealed'][] = $runId;
            self::saveBaseline($baseline);
            return ['sealed' => true, 'delta' => $delta];
        });
    }
}

