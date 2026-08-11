<?php

namespace App\Domains\Bot\Gateway;

/**
 * The gateway surface the Daemon drives. BinanceGateway implements it against
 * the real (or testnet) exchange; PaperGateway simulates the signed half
 * against mainnet public data. Payload shapes are Binance REST shapes — the
 * Daemon parses them identically whichever implementation is behind it.
 */
interface GatewayInterface
{
    /** Symbol entry from exchangeInfo (filters live under ['filters']). */
    public function exchangeInfo(string $symbol): array;

    public function tickerPrice(string $symbol): string;

    /** @return array<string, array{free:string, locked:string}> keyed by asset */
    public function accountBalances(): array;

    public function openOrders(string $symbol): array;

    public function orderStatus(string $symbol, string $clientOrderId): array;

    /** Must honor the idempotent-retry contract: a duplicate client order id
     *  returns ['duplicate' => true, 'clientOrderId' => $clientOrderId]. */
    public function placeLimitOrder(string $symbol, string $side, string $price, string $qty, string $clientOrderId): array;

    public function cancelOrder(string $symbol, string $clientOrderId): array;

    public function myTrades(string $symbol, array $extra = []): array;
}
