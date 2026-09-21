<?php

namespace App\Domains\Bot;

use App\GridRunQuery;

/**
 * How much of an asset the REAL account holds beyond what the fleet's real
 * runs track as inventory — the only coins a commission may be paid from
 * without shorting somebody's exit.
 *
 * Binance takes a BUY's commission out of the base asset it delivers (always
 * on a BNB pair; on every pair once the "pay fees in BNB" float is empty).
 * Whether that matters depends on the ACCOUNT, not on the run: dust left by
 * earlier fills, a deliberate BNB float, or a manual top-up all absorb it,
 * while a sibling run's inventory in the same asset must not.
 */
class FeeFloat
{
    private const SCALE = 12;
    public const CONFIG_BNB_MIN = 'gtbot_bnb_fee_float_min_quote';
    /** ~50 fills of a 50-USDT order at the discounted 0.075% */
    private const BNB_MIN_DEFAULT = '2';

    /** Quote value under which the BNB fee float is about to run dry; '0' = never alert. */
    public static function bnbMinQuote(): string
    {
        $v = \App\ConfigQuery::create()->select(['Value'])->findOneByConfig(self::CONFIG_BNB_MIN);
        return is_numeric($v) ? (string) $v : self::BNB_MIN_DEFAULT;
    }

    /** Fee mode is the ACCOUNT's, and survives a daemon restart on the
     *  ledger: the newest real fill anywhere whose commission was observed. */
    public static function accountPaysInBnb(): bool
    {
        // a BNB pair is charged in BNB because BNB is what it BUYS — that says
        // nothing about the account's fee mode
        $bnbRuns = GridRunQuery::create()->filterBySymbol('BNB%', \Criteria::LIKE)->select(['IdGridRun'])->find()->getArrayCopy();
        $asset = \App\BotOrderQuery::create()
            ->filterBySimulated(false)
            ->_if($bnbRuns !== [])->filterByIdGridRun($bnbRuns, \Criteria::NOT_IN)->_endif()
            ->filterByState('Filled')
            ->filterByFeeAsset(null, \Criteria::ISNOTNULL)
            ->orderByIdBotOrder(\Criteria::DESC)
            ->select(['FeeAsset'])
            ->findOne();
        return $asset === 'BNB';
    }

    /**
     * Quote value of the BNB the account holds beyond the BNB runs' inventory.
     *
     * @param array<string, array{free?:string, locked?:string}> $balances
     */
    public static function bnbFloatQuote(array $balances, string $quote, string $bnbPrice): string
    {
        $total = bcadd((string) ($balances['BNB']['free'] ?? '0'), (string) ($balances['BNB']['locked'] ?? '0'), self::SCALE);
        $spare = bcsub($total, self::trackedBase('BNB', $quote), self::SCALE);
        return bccomp($spare, '0', self::SCALE) <= 0 ? '0' : bcmul($spare, $bnbPrice, self::SCALE);
    }

    /**
     * Base-asset inventory tracked by every real run trading $base, this
     * run's not-yet-booked fill excluded (the caller adds it).
     */
    public static function trackedBase(string $base, string $quote, ?int $exceptOrderId = null): string
    {
        // select(): raw rows — a daemon must not read sibling runs from the
        // instance pool (see BudgetGuard)
        // every pair that BUYS this asset draws on the one account balance —
        // BTCUSDT and BTCUSDC inventory are the same coins ($quote is kept in
        // the signature for the callers' symmetry, not as a filter)
        $runs = GridRunQuery::create()
            ->filterBySymbol($base . '%', \Criteria::LIKE)
            ->filterBySimulated(false)
            ->filterByStatus(['Done', 'Draft'], \Criteria::NOT_IN)
            ->select(['IdGridRun', 'RunUid', 'LedgerResetAt', 'Symbol'])
            ->find();
        $sum = '0';
        foreach ($runs as $r) {
            if (SimWallet::assetsFor((string) $r['Symbol'])[0] !== $base) {
                continue; // BTCUSDT does not make BT… or BTCB… runs its siblings
            }
            $store = new OrderStore((int) $r['IdGridRun'], (string) $r['RunUid'], $r['LedgerResetAt'] ?: null, false);
            $sum = bcadd($sum, $store->trackedInventory(), self::SCALE);
            // a buy partially filled and still working has delivered coins
            // no ledger tracks yet (trackedInventory counts Filled rows only)
            foreach (\App\BotOrderQuery::create()
                ->filterByIdGridRun((int) $r['IdGridRun'])
                ->filterBySimulated(false)
                ->filterBySide('Buy')
                ->filterByState('PartFilled')
                ->select(['IdBotOrder', 'FilledQty'])
                ->find() as $part) {
                if ($exceptOrderId !== null && (int) $part['IdBotOrder'] === $exceptOrderId) {
                    continue; // the fill being booked: the caller adds its whole qty
                }
                $sum = bcadd($sum, (string) $part['FilledQty'], self::SCALE);
            }
        }
        return $sum;
    }

    /**
     * The part of $commission the account cannot cover from spare $base once
     * $executed is booked on top of what the fleet already tracks. '0' = the
     * float pays; never more than the commission itself — a larger gap is an
     * inventory mismatch, which is not this fill's to absorb.
     *
     * @param array<string, array{free?:string, locked?:string}> $balances post-fill account
     */
    public static function uncovered(string $base, string $quote, string $executed, string $commission, array $balances, ?int $bookingOrderId = null): string
    {
        $total = bcadd((string) ($balances[$base]['free'] ?? '0'), (string) ($balances[$base]['locked'] ?? '0'), self::SCALE);
        $needed = bcadd(self::trackedBase($base, $quote, $bookingOrderId), $executed, self::SCALE);
        $gap = bcsub($needed, $total, self::SCALE);
        if (bccomp($gap, '0', self::SCALE) <= 0) {
            return '0';
        }
        return bccomp($gap, $commission, self::SCALE) > 0 ? $commission : $gap;
    }
}
