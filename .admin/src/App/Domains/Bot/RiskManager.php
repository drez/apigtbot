<?php

namespace App\Domains\Bot;

/**
 * Pure risk gate. Every intended order passes review() before the gateway;
 * run configs pass validateRunConfig() before a run may start (and on GUI
 * save). Config/snapshot are plain arrays mirroring the grid_run row plus
 * live market/account numbers — see RiskManagerTest for the exact keys.
 */
class RiskManager
{
    private const SCALE = 12;
    /** Veto any order priced further than this fraction from market. */
    private const MAX_PRICE_DEVIATION = '0.5';
    /** Spacing must exceed this multiple of the round-trip fee (2·fee). */
    private const FEE_FLOOR_MULTIPLE = '3';

    public static function review(IntendedOrder $order, array $snapshot, array $config): RiskDecision
    {
        if (!empty($snapshot['kill_switch'])) {
            return new RiskDecision(false, 'kill switch is tripped', false);
        }

        $market = $snapshot['market_price'];

        // Exits reduce exposure — no exposure limit may ever block one (prod
        // 2026-07-22: a capped exit left a filled buy with no sell). Sells
        // only face the kill switch (above) and the fat-finger price check.
        if ($order->side === 'Sell') {
            $deviation = bcdiv(self::absSub($order->price, $market), $market, self::SCALE);
            if (bccomp($deviation, self::MAX_PRICE_DEVIATION, self::SCALE) > 0) {
                return new RiskDecision(false, sprintf('implausible price %s vs market %s', $order->price, $market));
            }
            return new RiskDecision(true);
        }

        // Grid breakout below range: the grid's defining failure mode.
        // Gates ENTRIES only — holding means keep inventory, and exits must
        // never be blocked by the halt (Flatten escalates to a kill though).
        $floor = bcmul($config['p_low'], bcsub('1', $config['breakout_buffer_pct'], self::SCALE), self::SCALE);
        if ($order->side === 'Buy' && bccomp($market, $floor, self::SCALE) < 0) {
            if (($config['breakout_policy'] ?? 'HaltAndHold') === 'Flatten') {
                return new RiskDecision(false, 'price broke below range: flatten policy', true, true, true);
            }
            return new RiskDecision(false, 'price broke below range: halt and hold', false, true);
        }

        // Daily loss cap → kill, not just veto.
        $lossToday = bcmul('-1', $snapshot['realized_pnl_today'], self::SCALE);
        if (bccomp($lossToday, $config['daily_loss_limit_quote'], self::SCALE) > 0) {
            return new RiskDecision(false, 'daily loss limit breached', true);
        }

        // Price sanity: defends against a bad feed or fat-finger config.
        $deviation = bcdiv(
            self::absSub($order->price, $market),
            $market,
            self::SCALE
        );
        if (bccomp($deviation, self::MAX_PRICE_DEVIATION, self::SCALE) > 0) {
            return new RiskDecision(false, sprintf('implausible price %s vs market %s', $order->price, $market));
        }

        if ((int) $snapshot['open_orders'] >= (int) $config['max_open_orders']) {
            return new RiskDecision(false, 'too many open orders');
        }

        $orderQuote = bcmul($order->price, $order->qty, self::SCALE);
        if (bccomp($orderQuote, $config['max_order_quote'], self::SCALE) > 0) {
            return new RiskDecision(false, sprintf('per-order cap exceeded (%s > %s)', $orderQuote, $config['max_order_quote']));
        }

        // Position cap gates entries only — it must never block an exit.
        if ($order->side === 'Buy') {
            $after = bcadd($snapshot['invested_quote'], $orderQuote, self::SCALE);
            if (bccomp($after, $config['max_position_quote'], self::SCALE) > 0) {
                return new RiskDecision(false, sprintf('position cap exceeded (%s > %s)', $after, $config['max_position_quote']));
            }
        }

        return new RiskDecision(true);
    }

    /**
     * Config-time validation: refuse to start anything incoherent.
     *
     * @return string[] human-readable errors, empty when sane
     */
    public static function validateRunConfig(array $config): array
    {
        $errors = [];
        if (bccomp($config['p_low'], '0', self::SCALE) <= 0) {
            $errors[] = 'p_low must be positive';
        } elseif (bccomp($config['p_high'], $config['p_low'], self::SCALE) <= 0) {
            $errors[] = 'p_low must be below p_high';
        }
        $n = (int) $config['n_levels'];
        if ($n < 2) {
            $errors[] = 'n_levels must be at least 2';
        }
        if ($errors) {
            return $errors; // geometry below needs a sane range
        }

        $spacing = GridMath::spacingPct($config['p_low'], $config['p_high'], $n, $config['spacing']);
        $roundTrip = bcmul('2', $config['fee_pct'], self::SCALE);
        $floor = bcmul(self::FEE_FLOOR_MULTIPLE, $roundTrip, self::SCALE);
        if (bccomp($spacing, $floor, self::SCALE) < 0) {
            $errors[] = sprintf(
                'grid spacing %s is below %sx round-trip fee %s — fewer levels or wider range needed',
                $spacing,
                self::FEE_FLOOR_MULTIPLE,
                $roundTrip
            );
        }

        // 0 is a legal, deliberate position: FLAT — no buys placed, working
        // sells/legacy exits keep working. The deploy-policy walk-forward
        // showed hostile-regime windows are where deploying at all loses;
        // 1-9 stays rejected as a near-zero ladder that's config noise.
        $deployPct = (int) ($config['deploy_pct'] ?? 100);
        if ($deployPct !== 0 && ($deployPct < 10 || $deployPct > 100)) {
            $errors[] = 'deploy_pct must be 0 (flat — no buys) or between 10 and 100';
            return $errors;
        }
        if ($deployPct === 0) {
            return $errors; // no buys will be placed — per-level notional is moot
        }

        // Every buy level must clear the exchange's minimum notional — on the
        // DEPLOYED budget, not the nominal one.
        $minNotional = $config['min_notional'] ?? '0';
        $deployed = bcdiv(bcmul($config['budget_quote'], (string) $deployPct, self::SCALE), '100', self::SCALE);
        $perLevel = bcdiv($deployed, (string) $n, self::SCALE);
        if (bccomp($perLevel, $minNotional, self::SCALE) < 0) {
            $errors[] = sprintf(
                'per-level deployed budget %s (deploy_pct %d%%) is under the exchange minimum notional %s',
                $perLevel,
                $deployPct,
                $minNotional
            );
        }

        return $errors;
    }

    private static function absSub(string $a, string $b): string
    {
        $d = bcsub($a, $b, self::SCALE);
        return bccomp($d, '0', self::SCALE) < 0 ? bcmul('-1', $d, self::SCALE) : $d;
    }
}
