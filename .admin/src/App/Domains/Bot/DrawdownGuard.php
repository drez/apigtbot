<?php

namespace App\Domains\Bot;

use App\ConfigQuery;
use App\GridRunQuery;

/**
 * Wallet-level hard drawdown floor (operator directive 2026-08-01: total
 * loss must never exceed gtbot_max_drawdown_pct — default 25% — of the
 * wallet the fleet actually trades: the paper pool seed
 * (gtbot_shared_budget_quote) while it is on paper, what the exchange
 * account was funded with (gtbot_real_account_baseline) once it is not).
 * Equity is the WHOLE system's mark-to-market value: one
 * shared wallet, one floor — a confirmed breach kills EVERY active run at
 * once (buys canceled, inventory HELD); the hourly routine, not the daemon,
 * decides selloff/restart (see refit-routine.md, drawdown-stop recovery).
 * Pricing mirrors DashboardData::computeSharedWallet(), but fail-closed:
 * an asset with no matching run price counts as 0 (understates equity).
 */
final class DrawdownGuard
{
    private const SCALE = 12;
    public const DEFAULT_PCT = '25';

    /**
     * What the real exchange account was funded with — the number the
     * drawdown percentage is measured from once the fleet trades real money.
     *
     * Auto-seeded ONCE from the first positive real equity this guard ever
     * measures (journaled, so the operator sees the number), and editable
     * afterwards like any other config row.
     */
    public const CONFIG_REAL_BASELINE = 'gtbot_real_account_baseline';

    /** a FAILED auto-seed is not retried in this process (the config row is
     *  the guard for a successful one) */
    private static bool $seedFailed = false;

    /** Config gtbot_max_drawdown_pct; missing/empty row falls back to 25.
     *  An explicit 0 disables the stop entirely. */
    public static function maxDrawdownPct(): string
    {
        $v = ConfigQuery::create()->findOneByConfig('gtbot_max_drawdown_pct')?->getValue();
        return ($v !== null && $v !== '') ? (string) $v : self::DEFAULT_PCT;
    }

    /**
     * The funded size of the REAL account, or null while the fleet has never
     * been seen holding real money.
     *
     * select(), not a hydrated read: this sits on the daemon's per-tick path
     * and is seeded by whichever process measures the account first — the
     * same pooling trap documented at BudgetGuard::check.
     */
    public static function realBaseline(): ?string
    {
        $row = ConfigQuery::create()
            ->filterByConfig(self::CONFIG_REAL_BASELINE)
            ->select(['Config', 'Value'])
            ->findOne();
        if (!is_array($row) || ($row['Value'] ?? null) === null) {
            return null;
        }
        $v = trim((string) $row['Value']);
        if ($v === '' || !is_numeric($v) || bccomp($v, '0', self::SCALE) <= 0) {
            return null;
        }
        return $v;
    }

    /**
     * THE FLOOR IS A FRACTION OF THE WALLET THAT WAS MEASURED (2026-09-21,
     * round 2). It used to be a fraction of gtbot_shared_budget_quote — the
     * PAPER pool seed — whichever wallet equity() had actually read. The
     * moment the fleet went real the floor became 75% of a number the
     * exchange account had never held: the RUNBOOK's own ordering (flip the
     * money switch, THEN fund the account) put the whole fleet three ticks
     * from a drawdown_stop, and a canary sized per its step 8 sat 775 USDT
     * under a floor that had nothing to do with it.
     *
     * @param string|null $source 'sim' | 'real'; null = the paper wallet, for
     *                            the callers that only ever mean that one
     */
    public static function baselineFor(?string $source = null): string
    {
        return $source === 'real'
            ? (self::realBaseline() ?? SimWallet::sharedBudget())
            : SimWallet::sharedBudget();
    }

    /** Equity floor = the measured wallet's baseline × (1 − pct/100); null = disabled. */
    public static function floor(?string $source = null): ?string
    {
        $pct = self::maxDrawdownPct();
        if (bccomp($pct, '0', self::SCALE) <= 0) {
            return null;
        }
        $keep = bcsub('1', bcdiv($pct, '100', self::SCALE), self::SCALE);
        return bcmul(self::baselineFor($source), $keep, self::SCALE);
    }

    /**
     * Global mark-to-market equity in quote (USDT). Paper: the shared
     * sim_wallet. Real: the ONE exchange account as stamped on the run rows
     * — freshest bal_quote, bal_base deduped per base asset (every run on
     * the same symbol stamps the same account balance).
     *
     * WHICH SIDE IS MEASURED IS NOT "IS ANY ROW REAL" (2026-09-21 review).
     * It used to be, and the row that decided it did not have to have ever
     * run: a single real run created as a Draft — exactly what preparing a
     * live canary looks like — has no bal_quote / bal_base stamp, so
     * realBalances() returned an EMPTY account, equity read 0, and 0 is
     * under every floor. Three ticks later Daemon::checkGlobalDrawdown kills
     * every run in the (paper, perfectly healthy) fleet and only the hourly
     * routine can restart it. The real account is therefore the source only
     * when a real run is actually EXPOSED — active, or still holding
     * inventory — AND has stamped the account it is exposed on. Anything
     * else leaves the paper wallet as the measured side, which is what it
     * is. `source` says which one answered, so callers (NavLedger) file the
     * number under what was measured instead of re-deriving it.
     *
     * @return array{equity: string, unpriced: string[], source: string} source: 'real'|'sim'
     */
    public static function equity(): array
    {
        // select(): raw rows, never pooled objects — the daemon marks equity
        // every tick and the stamps it needs (last_price, bal_*) are written
        // by OTHER daemons; a GridRun object pooled at boot never sees them
        // (same trap as BudgetGuard::check).
        $runs = GridRunQuery::create()
            ->filterByStatus('Done', \Criteria::NOT_EQUAL)
            ->select(['IdGridRun', 'Symbol', 'Simulated', 'Status', 'RunUid', 'LedgerResetAt', 'LastPrice', 'LastTickAt', 'BalBase', 'BalQuote'])
            ->find()->getArrayCopy();
        $statuses = \App\GridRunPeer::getValueSet(\App\GridRunPeer::STATUS);
        foreach ($runs as $i => $r) {
            // select() hands ENUMs back as their int ordinal
            $runs[$i]['Status'] = $statuses[$r['Status']] ?? (string) $r['Status'];
        }

        // freshest last_price per base asset (BTC, ETH, …)
        $priceByBase = [];
        $priceTick = [];
        foreach ($runs as $r) {
            if ($r['LastPrice'] === null) {
                continue;
            }
            [$base] = SimWallet::assetsFor((string) $r['Symbol']);
            $tick = (string) ($r['LastTickAt'] ?? '');
            if (!isset($priceByBase[$base]) || $tick > $priceTick[$base]) {
                $priceByBase[$base] = (string) $r['LastPrice'];
                $priceTick[$base] = $tick;
            }
        }

        // A real run that is EXPOSED and has stamped its account puts real
        // money at stake, so the floor follows the real account — a 'mixed'
        // fleet (one run left on paper, or born on paper into a real fleet)
        // must not swing it onto the paper wallet. Paper runs stamp bal_* too
        // (from the paper gateway): never the account.
        $stamped = array_values(array_filter(
            $runs,
            static fn (array $r): bool => !$r['Simulated'] && $r['BalQuote'] !== null
        ));

        // AN ACCOUNT THAT HAS NEVER HELD ANYTHING IS NOT THE MEASURED WALLET
        // (2026-09-21, round 2). The RUNBOOK used to flip the money switch
        // before funding the account, so the first real daemon to reboot
        // stamped bal_quote 0 — exposed, stamped, and worth nothing — and the
        // whole (perfectly healthy) fleet was three ticks from a
        // drawdown_stop nobody had lost a cent to. There is nothing real to
        // guard until the account has been seen holding something: until
        // then the paper wallet stays the measured side. Once it HAS been
        // funded the baseline row exists and latches the switch, so a real
        // account later running to zero is a total loss and does trip.
        // A FEE FLOAT IS NOT A FUNDED ACCOUNT (2026-09-21, round 3). "First
        // positive equity" latched on anything at all: the RUNBOOK's own step
        // 7 says to hold a BNB fee float, so a float landing before the USDT
        // baselined the fleet at 15 USDT — floor 11.25, the guard disarmed for
        // the whole real account, and with gtbot_use_all_funds on a cap of 15
        // that fail-closes every entry. Nothing below MIN_SLICE can be traded
        // by a run at all, so an account holding less than that is not the
        // fleet's wallet yet and the paper wallet stays the measured side.
        $source = 'sim';
        $balances = null;
        if (self::realIsExposed($stamped)) {
            $real = self::value(self::realBalances($stamped), $priceByBase);
            if (bccomp($real['equity'], TrendActivator::MIN_SLICE, self::SCALE) >= 0) {
                self::seedBaseline($real['equity']);
                $source = 'real';
                $balances = $real;
            } elseif (self::realBaseline() !== null) {
                $source = 'real'; // funded once: a zero account is a real loss
                $balances = $real;
            }
        }
        if ($balances === null) {
            $balances = self::value(SimWallet::balances(), $priceByBase);
        }
        return ['equity' => $balances['equity'], 'unpriced' => $balances['unpriced'], 'source' => $source];
    }

    /**
     * Mark a wallet to market.
     *
     * A token with no run on its symbol would otherwise be $unpriced and
     * count as ZERO — which silently hides a seeded asset from the pool. Fall
     * back to the market summaries, the same 1h/4h/1d ladder
     * NavLedger::refPrice() already uses.
     *
     * @param array<string, string> $balances asset => qty
     * @param array<string, string> $priceByBase base => mark (extended here)
     * @return array{equity: string, unpriced: string[]}
     */
    private static function value(array $balances, array $priceByBase): array
    {
        foreach (array_keys($balances) as $asset) {
            if ($asset === 'USDT' || isset($priceByBase[$asset])) {
                continue;
            }
            $mark = self::marketPrice((string) $asset);
            if ($mark !== null) {
                $priceByBase[$asset] = $mark;
            }
        }
        $equity = '0';
        $unpriced = [];
        foreach ($balances as $asset => $qty) {
            if ($asset === 'USDT') {
                $equity = bcadd($equity, (string) $qty, self::SCALE);
            } elseif (isset($priceByBase[$asset])) {
                $equity = bcadd($equity, bcmul((string) $qty, $priceByBase[$asset], self::SCALE), self::SCALE);
            } elseif (bccomp((string) $qty, '0', self::SCALE) !== 0) {
                $unpriced[] = (string) $asset; // counted as 0 — fail-closed
            }
        }
        return ['equity' => $equity, 'unpriced' => $unpriced];
    }

    /**
     * The first time real money is measured, record what the account was
     * funded with — that is what the drawdown percentage is a fraction of.
     *
     * Once only, and loudly: the operator has to be able to see the number
     * and correct it (a deposit that lands in two transfers would otherwise
     * baseline the fleet on the first half). Never throws — it is reached
     * from the daemon's per-tick read path, and a guard that cannot write a
     * config row must still be able to answer the question it was asked.
     */
    private static function seedBaseline(string $equity): void
    {
        if (self::$seedFailed || self::realBaseline() !== null) {
            return;
        }
        $whole = bcadd($equity, '0', 0);
        try {
            $row = ConfigQuery::create()->findOneByConfig(self::CONFIG_REAL_BASELINE);
            if ($row === null) {
                $row = new \App\Config();
                $row->setConfig(self::CONFIG_REAL_BASELINE);
                $row->setCategory('General');
                $row->setSystem('n');
                $row->setType('string');
                $row->setDateCreation(date('Y-m-d H:i:s'));
            }
            $row->setDescription('Real account: what it was funded with, in USDT. The drawdown floor is this × (1 − gtbot_max_drawdown_pct/100). Seeded once from the first real equity measured; edit it if you add to or withdraw from the account.');
            $row->setValue($whole);
            $row->save();
            \App\ConfigPeer::clearInstancePool();

            $msg = sprintf(
                'REAL MONEY: the account is funded and measured at %s USDT. That is now what the %s%% drawdown floor is a fraction of (floor %s). Edit %s if the account is funded differently.',
                $whole,
                self::maxDrawdownPct(),
                bcadd((string) self::floor('real'), '0', 2),
                self::CONFIG_REAL_BASELINE
            );
            $host = FleetSlots::hostRun();
            if ($host !== null) {
                (new EventLog((int) $host->getIdGridRun(), false, TelegramNotifier::fromEnv()))->write(
                    'Alert',
                    'real_baseline_seeded',
                    $msg,
                    ['baseline' => $whole, 'pct' => self::maxDrawdownPct()]
                );
            } else {
                // bot_event is a child of grid_run, and the fleet has no
                // active run to hang one off — which is exactly the shape of
                // the go-live window this number is seeded in. The one number
                // the operator must check cannot be the one that goes
                // unannounced, so Telegram gets it directly (round 3).
                TelegramNotifier::fromEnv()?->sendNow('⚠️ ' . $msg, 'real_baseline_seeded');
            }
        } catch (\Throwable $e) {
            self::$seedFailed = true;
            error_log('gtbot: could not seed ' . self::CONFIG_REAL_BASELINE . ' — ' . $e->getMessage());
        }
    }

    /**
     * Is any of these stamped real runs actually exposed?
     *
     * Active is exposure by definition (it can place an order this tick). A
     * parked run — Halted with coins it never sold — is exposure too, and it
     * is the case that matters most: the fleet is not trading but the money
     * is still on the exchange. Only a run that is neither (Draft, Halted and
     * flat, Retiring with nothing left) leaves the floor on the paper wallet.
     *
     * The inventory read costs one query per parked real run and is reached
     * only when NO stamped real run is active, so the common shapes — a paper
     * fleet (no stamped real runs at all) and a live fleet (first row
     * short-circuits) — pay nothing for it on the daemon's per-tick path.
     *
     * @param array<int, array<string, mixed>> $stamped real runs carrying a bal_quote stamp
     */
    private static function realIsExposed(array $stamped): bool
    {
        foreach ($stamped as $r) {
            if (in_array((string) $r['Status'], BudgetGuard::ACTIVE_STATUSES, true)) {
                return true;
            }
        }
        foreach ($stamped as $r) {
            $store = new OrderStore(
                (int) $r['IdGridRun'],
                (string) $r['RunUid'],
                ($r['LedgerResetAt'] ?? null) ?: null,
                false
            );
            if (bccomp($store->trackedInventory(), '0', self::SCALE) > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * The one real exchange account, read from the freshest run-row stamps.
     *
     * @param array<int, array{Symbol:string, LastTickAt:?string, BalBase:?string, BalQuote:?string}> $runs select() rows
     * @return array<string, string> asset => qty
     */
    private static function realBalances(array $runs): array
    {
        $out = [];
        $quoteTick = null;
        $baseTick = [];
        foreach ($runs as $r) {
            $tick = (string) ($r['LastTickAt'] ?? '');
            if ($r['BalQuote'] !== null && ($quoteTick === null || $tick > $quoteTick)) {
                $quoteTick = $tick;
                $out['USDT'] = (string) $r['BalQuote'];
            }
            if ($r['BalBase'] === null) {
                continue;
            }
            [$base] = SimWallet::assetsFor((string) $r['Symbol']);
            if (!isset($baseTick[$base]) || $tick > $baseTick[$base]) {
                $baseTick[$base] = $tick;
                $out[$base] = (string) $r['BalBase'];
            }
        }
        return $out;
    }

    /**
     * @return array{equity: string, floor: string, budget: string,
     *               pct: string, unpriced: string[]}|null null = OK/disabled
     */
    public static function check(): ?array
    {
        if (bccomp(self::maxDrawdownPct(), '0', self::SCALE) <= 0) {
            return null; // disabled — never read the wallet for nothing
        }
        $eq = self::equity();
        // the floor follows the wallet equity() measured, not the paper seed
        $floor = self::floor($eq['source']);
        if ($floor === null || bccomp($eq['equity'], $floor, self::SCALE) >= 0) {
            return null; // equity == floor is still OK — strict < trips
        }
        return [
            'equity' => $eq['equity'],
            'floor' => $floor,
            'budget' => self::baselineFor($eq['source']),
            'source' => $eq['source'],
            'pct' => self::maxDrawdownPct(),
            'unpriced' => $eq['unpriced'],
        ];
    }

    /** Kill every active run that isn't already killed. Each daemon's next
     *  tick takes the external-kill path (cancel buys once, hold inventory).
     *  Returns how many runs were newly killed. */
    public static function tripAll(): int
    {
        $n = 0;
        foreach (GridRunQuery::create()
            ->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)
            ->find() as $r) {
            if ((bool) $r->getKillSwitch()) {
                continue;
            }
            $r->setKillSwitch(true);
            $r->save();
            $n++;
        }
        return $n;
    }

    /**
     * Mark for a base asset from the stored market summaries, so pricing does
     * not depend on a run existing for that symbol. Same tf ladder as
     * NavLedger::refPrice().
     */
    private static function marketPrice(string $base): ?string
    {
        try {
            $s = MarketStore::summaries($base . 'USDT', PHP_INT_MAX);
        } catch (\Throwable $e) {
            return null;
        }
        foreach (['1h', '4h', '1d'] as $tf) {
            if (isset($s[$tf]['price']) && (float) $s[$tf]['price'] > 0) {
                return (string) $s[$tf]['price'];
            }
        }
        return null;
    }
}

