<?php

namespace Tests\Custom\Bot\Support;

/**
 * Minimal Binance REST emulator, used as the BinanceGateway transport in
 * integration tests. Supports the endpoints the daemon touches; setPrice()
 * moves the tape and fills crossed limit orders exactly like a real book
 * would (full fills, no queue position).
 */
class ExchangeSim
{
    public string $price = '0';
    /** @var array<int, array{high:string, low:string, close:string, open?:string}> for /klines */
    public array $klines = [];
    /** @var array<string, array> open orders by clientOrderId */
    public array $open = [];
    /** @var array<string, array> terminal orders by clientOrderId */
    public array $done = [];
    /** @var array<string, array{free:string, locked:string}> account balances by asset */
    public array $balances = [];
    /** number of /api/v3/account hits (balance-stamp throttling assertions) */
    public int $accountCalls = 0;
    private int $nextOrderId = 1000;

    public function __construct(private readonly array $filters = [
        ['filterType' => 'PRICE_FILTER', 'tickSize' => '0.01000000'],
        ['filterType' => 'LOT_SIZE', 'stepSize' => '0.00010000', 'minQty' => '0.00010000'],
        ['filterType' => 'NOTIONAL', 'minNotional' => '5.00000000'],
    ]) {
    }

    public function setPrice(string $price): void
    {
        $this->price = $price;
        foreach ($this->open as $cid => $o) {
            $cmp = bccomp($price, $o['price'], 12);
            if (($o['side'] === 'BUY' && $cmp <= 0) || ($o['side'] === 'SELL' && $cmp >= 0)) {
                $o['status'] = 'FILLED';
                $o['executedQty'] = $o['origQty'];
                $this->done[$cid] = $o;
                unset($this->open[$cid]);
            }
        }
    }

    /** Partially fill an open order in place — it stays on the book. */
    public function partialFill(string $cid, string $qty): void
    {
        $this->open[$cid]['executedQty'] = $qty;
        $this->open[$cid]['status'] = 'PARTIALLY_FILLED';
    }

    public function cancelAll(): void
    {
        foreach ($this->open as $cid => $o) {
            $o['status'] = 'CANCELED';
            $this->done[$cid] = $o;
        }
        $this->open = [];
    }

    /** BinanceGateway-compatible transport closure. */
    public function transport(): callable
    {
        return function (string $method, string $url, array $headers, ?string $body): array {
            $parts = parse_url($url);
            parse_str($parts['query'] ?? '', $q);
            if ($body !== null) {
                parse_str($body, $bq);
                $q += $bq;
            }
            $path = $parts['path'];
            $respond = fn (array $payload, int $status = 200): array => [
                'status' => $status,
                'headers' => ['x-mbx-used-weight-1m' => '1'],
                'body' => json_encode($payload),
            ];

            switch (true) {
                case $path === '/api/v3/ping':
                    return $respond([]);
                case $path === '/api/v3/exchangeInfo':
                    return $respond(['symbols' => [['symbol' => $q['symbol'], 'filters' => $this->filters]]]);
                case $path === '/api/v3/ticker/price':
                    return $respond(['symbol' => $q['symbol'], 'price' => $this->price]);
                case $path === '/api/v3/klines':
                    // raw Binance shape: [openTime, open, high, low, close, ...]
                    return $respond(array_map(
                        fn (array $c): array => [0, $c['open'] ?? $c['close'], $c['high'], $c['low'], $c['close'], '0'],
                        $this->klines
                    ));
                case $path === '/api/v3/account':
                    $this->accountCalls++;
                    return $respond(['balances' => array_map(
                        fn (string $asset): array => ['asset' => $asset] + $this->balances[$asset],
                        array_keys($this->balances)
                    )]);
                case $path === '/api/v3/openOrders':
                    return $respond(array_values($this->open));
                case $path === '/api/v3/order' && $method === 'POST':
                    $cid = $q['newClientOrderId'];
                    if (isset($this->open[$cid]) || isset($this->done[$cid])) {
                        return $respond(['code' => -2010, 'msg' => 'Duplicate order sent.'], 400);
                    }
                    $order = [
                        'clientOrderId' => $cid,
                        'orderId' => $this->nextOrderId++,
                        'side' => $q['side'],
                        'price' => $q['price'],
                        'origQty' => $q['quantity'],
                        'executedQty' => '0',
                        'status' => 'NEW',
                    ];
                    $this->open[$cid] = $order;
                    // an aggressive limit fills immediately at the current tape
                    $this->setPrice($this->price);
                    return $respond($order);
                case $path === '/api/v3/order' && $method === 'GET':
                    $cid = $q['origClientOrderId'];
                    $o = $this->open[$cid] ?? $this->done[$cid] ?? null;
                    return $o ? $respond($o) : $respond(['code' => -2013, 'msg' => 'Order does not exist.'], 400);
                case $path === '/api/v3/order' && $method === 'DELETE':
                    $cid = $q['origClientOrderId'];
                    if (!isset($this->open[$cid])) {
                        return $respond(['code' => -2011, 'msg' => 'Unknown order sent.'], 400);
                    }
                    $o = $this->open[$cid];
                    $o['status'] = 'CANCELED';
                    $this->done[$cid] = $o;
                    unset($this->open[$cid]);
                    return $respond($o);
                default:
                    return $respond(['code' => -1000, 'msg' => "sim: unhandled $method $path"], 400);
            }
        };
    }
}
