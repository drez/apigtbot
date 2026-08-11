<?php

namespace App\Domains\Bot;

/**
 * Exchange-filter compliance: tick/step rounding + notional floor.
 * Built from the symbols[].filters array of GET /exchangeInfo. Every order
 * must pass through normalize() before reaching a gateway.
 */
class Filters
{
    private const SCALE = 12;

    private string $tickSize = '0.00000001';
    private string $stepSize = '0.00000001';
    private string $minQty = '0';
    private string $minNotional = '0';

    /** @param array<int, array<string, string>> $exchangeFilters */
    public function __construct(array $exchangeFilters)
    {
        foreach ($exchangeFilters as $f) {
            switch ($f['filterType'] ?? '') {
                case 'PRICE_FILTER':
                    $this->tickSize = $f['tickSize'];
                    break;
                case 'LOT_SIZE':
                    $this->stepSize = $f['stepSize'];
                    $this->minQty = $f['minQty'] ?? '0';
                    break;
                case 'NOTIONAL':
                case 'MIN_NOTIONAL':
                    $this->minNotional = $f['minNotional'] ?? '0';
                    break;
            }
        }
    }

    /** Round to a tick multiple; $dir 'down' (buys) or 'up' (sells). */
    public function priceToTick(string $price, string $dir): string
    {
        $units = bcdiv($price, $this->tickSize, 0); // truncates → floor for positives
        $down = bcmul($units, $this->tickSize, self::SCALE);
        if ($dir === 'down' || bccomp($down, $price, self::SCALE) === 0) {
            return $down;
        }
        return bcmul(bcadd($units, '1', 0), $this->tickSize, self::SCALE);
    }

    /** Floor a quantity to the lot step. */
    public function qtyToStep(string $qty): string
    {
        return bcmul(bcdiv($qty, $this->stepSize, 0), $this->stepSize, self::SCALE);
    }

    public function qtyOk(string $qty): bool
    {
        return bccomp($qty, $this->minQty, self::SCALE) >= 0;
    }

    public function minNotional(): string
    {
        return $this->minNotional;
    }

    public function meetsNotional(string $price, string $qty): bool
    {
        return bccomp(bcmul($price, $qty, self::SCALE), $this->minNotional, self::SCALE) >= 0;
    }

    /**
     * Apply all filters to an intended order. Buy prices round down, sell
     * prices round up — grid spacing can widen a hair but never shrink.
     * Returns ['price' => ..., 'qty' => ...] or null when the order can't
     * clear minQty/minNotional (caller skips the level with a warning).
     */
    public function normalize(string $side, string $price, string $qty): ?array
    {
        $p = $this->priceToTick($price, $side === 'Sell' ? 'up' : 'down');
        $q = $this->qtyToStep($qty);
        if (!$this->qtyOk($q) || !$this->meetsNotional($p, $q)) {
            return null;
        }
        // Binance rejects params beyond 8 decimals — emit API-ready strings.
        return ['price' => bcadd($p, '0', 8), 'qty' => bcadd($q, '0', 8)];
    }
}
