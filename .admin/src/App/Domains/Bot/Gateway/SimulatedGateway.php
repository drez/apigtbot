<?php

namespace App\Domains\Bot\Gateway;

/**
 * In-memory limit-order fill engine for backtests. A buy fills when the
 * stepped price touches or crosses below its limit; a sell when price touches
 * or crosses above. Fills are perfect (full qty, at the limit price, no queue
 * position) — which flatters grid strategies; treat backtest output as
 * directional, never as a promise.
 */
class SimulatedGateway
{
    private const SCALE = 12;

    /** @var array<string, array{side:string, price:string, qty:string}> keyed by client order id */
    private array $book = [];

    public function placeOrder(string $clientOrderId, string $side, string $price, string $qty): void
    {
        if (isset($this->book[$clientOrderId])) {
            throw new \LogicException("duplicate client order id $clientOrderId");
        }
        $this->book[$clientOrderId] = ['side' => $side, 'price' => $price, 'qty' => $qty];
    }

    public function cancelOrder(string $clientOrderId): void
    {
        unset($this->book[$clientOrderId]);
    }

    /** @return array<int, array{client_order_id:string, side:string, price:string, qty:string}> */
    public function openOrders(): array
    {
        $out = [];
        foreach ($this->book as $cid => $o) {
            $out[] = ['client_order_id' => $cid] + $o;
        }
        return $out;
    }

    /**
     * Advance the tape to $price and return the fills it triggers.
     * Buys fill highest-limit-first, sells lowest-first — the order a real
     * move through the book would hit them.
     *
     * @return array<int, array{client_order_id:string, side:string, price:string, qty:string}>
     */
    public function step(string $price): array
    {
        $hit = [];
        foreach ($this->book as $cid => $o) {
            $cmp = bccomp($price, $o['price'], self::SCALE);
            if (($o['side'] === 'Buy' && $cmp <= 0) || ($o['side'] === 'Sell' && $cmp >= 0)) {
                $hit[$cid] = $o;
            }
        }
        uasort($hit, function (array $a, array $b): int {
            if ($a['side'] !== $b['side']) {
                return $a['side'] === 'Buy' ? -1 : 1;
            }
            $cmp = bccomp($b['price'], $a['price'], self::SCALE);
            return $a['side'] === 'Buy' ? $cmp : -$cmp;
        });
        $fills = [];
        foreach ($hit as $cid => $o) {
            unset($this->book[$cid]);
            $fills[] = ['client_order_id' => $cid] + $o;
        }
        return $fills;
    }
}
