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

    /** [base, quote] from a symbol — same parse as Daemon::symbolAssets. */
    public static function assetsFor(string $symbol): array
    {
        $quote = str_ends_with($symbol, 'USDT') ? 'USDT' : substr($symbol, -3);
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
            $q = BotOrderQuery::create()
                ->filterBySimulated(true)
                ->filterByState('Filled');
            $epoch = self::epoch();
            if ($epoch !== null) {
                $q->filterByDateCreation(['min' => $epoch]);
            }
            foreach ($q->find() as $row) {
                $run = $row->getGridRun();
                if (!$run) {
                    continue;
                }
                [$base, $quote] = self::assetsFor((string) $run->getSymbol());
                $filled = (string) ($row->getFilledQty() ?: '0');
                $notional = bcmul((string) $row->getPrice(), $filled, self::SCALE);
                $fee = (string) ($row->getFeePaid() ?: '0');
                $wallet[$base] ??= '0';
                $wallet[$quote] ??= '0';
                if ((string) $row->getSide() === 'Buy') {
                    $wallet[$quote] = bcsub($wallet[$quote], bcadd($notional, $fee, self::SCALE), self::SCALE);
                    $wallet[$base] = bcadd($wallet[$base], $filled, self::SCALE);
                } else {
                    $wallet[$base] = bcsub($wallet[$base], $filled, self::SCALE);
                    $wallet[$quote] = bcadd($wallet[$quote], bcsub($notional, $fee, self::SCALE), self::SCALE);
                }
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
}
