<?php

namespace App\Domains\Bot;

use App\ConfigQuery;
use App\Domains\Dashboard\ModeSwitch;
use App\GridRunQuery;

/**
 * Wallet-level hard drawdown floor (operator directive 2026-08-01: total
 * loss must never exceed gtbot_max_drawdown_pct — default 25% — of the
 * shared budget). Equity is the WHOLE system's mark-to-market value: one
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

    /** Config gtbot_max_drawdown_pct; missing/empty row falls back to 25.
     *  An explicit 0 disables the stop entirely. */
    public static function maxDrawdownPct(): string
    {
        $v = ConfigQuery::create()->findOneByConfig('gtbot_max_drawdown_pct')?->getValue();
        return ($v !== null && $v !== '') ? (string) $v : self::DEFAULT_PCT;
    }

    /** Equity floor = shared budget × (1 − pct/100); null = disabled. */
    public static function floor(): ?string
    {
        $pct = self::maxDrawdownPct();
        if (bccomp($pct, '0', self::SCALE) <= 0) {
            return null;
        }
        $keep = bcsub('1', bcdiv($pct, '100', self::SCALE), self::SCALE);
        return bcmul(SimWallet::sharedBudget(), $keep, self::SCALE);
    }

    /**
     * Global mark-to-market equity in quote (USDT). Paper: the shared
     * sim_wallet. Real: the ONE exchange account as stamped on the run rows
     * — freshest bal_quote, bal_base deduped per base asset (every run on
     * the same symbol stamps the same account balance).
     *
     * @return array{equity: string, unpriced: string[]}
     */
    public static function equity(): array
    {
        $runs = GridRunQuery::create()
            ->filterByStatus('Done', \Criteria::NOT_EQUAL)
            ->find()->getArrayCopy();

        // freshest last_price per base asset (BTC, ETH, …)
        $priceByBase = [];
        $priceTick = [];
        foreach ($runs as $r) {
            if ($r->getLastPrice() === null) {
                continue;
            }
            [$base] = SimWallet::assetsFor((string) $r->getSymbol());
            $tick = (string) ($r->getLastTickAt('Y-m-d H:i:s') ?? '');
            if (!isset($priceByBase[$base]) || $tick > $priceTick[$base]) {
                $priceByBase[$base] = (string) $r->getLastPrice();
                $priceTick[$base] = $tick;
            }
        }

        $balances = ModeSwitch::systemMode() === 'real'
            ? self::realBalances($runs)
            : SimWallet::balances();

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
     * The one real exchange account, read from the freshest run-row stamps.
     *
     * @param \App\GridRun[] $runs
     * @return array<string, string> asset => qty
     */
    private static function realBalances(array $runs): array
    {
        $out = [];
        $quoteTick = null;
        $baseTick = [];
        foreach ($runs as $r) {
            $tick = (string) ($r->getLastTickAt('Y-m-d H:i:s') ?? '');
            if ($r->getBalQuote() !== null && ($quoteTick === null || $tick > $quoteTick)) {
                $quoteTick = $tick;
                $out['USDT'] = (string) $r->getBalQuote();
            }
            if ($r->getBalBase() === null) {
                continue;
            }
            [$base] = SimWallet::assetsFor((string) $r->getSymbol());
            if (!isset($baseTick[$base]) || $tick > $baseTick[$base]) {
                $baseTick[$base] = $tick;
                $out[$base] = (string) $r->getBalBase();
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
        $floor = self::floor();
        if ($floor === null) {
            return null;
        }
        $eq = self::equity();
        if (bccomp($eq['equity'], $floor, self::SCALE) >= 0) {
            return null; // equity == floor is still OK — strict < trips
        }
        return [
            'equity' => $eq['equity'],
            'floor' => $floor,
            'budget' => SimWallet::sharedBudget(),
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
}
