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
    /** /api/v3/account answers 503 */
    public bool $accountDown = false;
    /**
     * Commission model. null = the account keeps no trade list (myTrades is
     * unhandled, the daemon falls back to its estimate). 'native' = Binance
     * without BNB fee mode: a BUY is charged in the BASE asset it receives, a
     * SELL in the quote. 'BNB' = "pay fees in BNB": charged in BNB at a 25%
     * discount while the BNB balance covers it, else native — exactly the
     * silent fallback the real exchange does.
     */
    public ?string $commissionMode = null;
    public string $feeRate = '0.001';
    /** @var array<string, string> per-symbol tape overrides for /ticker/price */
    public array $prices = [];
    /** @var array<int, array<int, array>> orderId → myTrades entries */
    public array $trades = [];
    /** answer this many myTrades calls with an empty list before serving the
     *  real one — a real account's trade list lags the order status by a few
     *  hundred milliseconds (Daemon::fillCommission retries it) */
    public int $tradeLag = 0;
    /** called with the request params before every order POST — lets a test
     *  move the account between the daemon's read and its placement */
    public ?\Closure $beforeOrder = null;
    private int $nextOrderId = 1000;

    public function __construct(private readonly array $filters = [
        ['filterType' => 'PRICE_FILTER', 'tickSize' => '0.01000000'],
        ['filterType' => 'LOT_SIZE', 'stepSize' => '0.00010000', 'minQty' => '0.00010000'],
        ['filterType' => 'NOTIONAL', 'minNotional' => '5.00000000'],
    ]) {
    }

    /** $taker: the fill is an order crossing the book as it is placed — it
     *  trades at the TAPE, not at its limit (a resting order trades at its own
     *  price when the tape comes to it). */
    public function setPrice(string $price, bool $taker = false): void
    {
        $this->price = $price;
        foreach ($this->open as $cid => $o) {
            $cmp = bccomp($price, $o['price'], 12);
            if (($o['side'] === 'BUY' && $cmp <= 0) || ($o['side'] === 'SELL' && $cmp >= 0)) {
                $o['status'] = 'FILLED';
                $o['executedQty'] = $o['origQty'];
                $this->done[$cid] = $o;
                unset($this->open[$cid]);
                $this->settle($taker ? ['price' => $price] + $o : $o);
            }
        }
    }

    /** Move the wallet and record the trade for a full fill (only when a
     *  commission model is set — the legacy suites keep a static wallet). */
    private function settle(array $o): void
    {
        if ($this->commissionMode === null) {
            return;
        }
        $symbol = (string) ($o['symbol'] ?? 'BTCUSDT');
        $quote = 'USDT';
        $base = substr($symbol, 0, -strlen($quote));
        $qty = (string) $o['executedQty'];
        $notional = bcmul($qty, (string) $o['price'], 12);
        $buy = $o['side'] === 'BUY';
        $this->move($base, $buy ? $qty : '-' . $qty);
        $this->move($quote, $buy ? '-' . $notional : $notional);

        $asset = $buy ? $base : $quote;
        $commission = bcmul($buy ? $qty : $notional, $this->feeRate, 12);
        if ($this->commissionMode === 'BNB') {
            $bnbPrice = $this->prices['BNB' . $quote] ?? '0';
            $inBnb = bccomp($bnbPrice, '0', 12) > 0
                ? bcdiv(bcmul(bcmul($notional, $this->feeRate, 12), '0.75', 12), $bnbPrice, 8)
                : '0';
            if (bccomp($inBnb, '0', 8) > 0 && bccomp($this->balances['BNB']['free'] ?? '0', $inBnb, 8) >= 0) {
                $asset = 'BNB';
                $commission = $inBnb;
            }
        }
        $this->move($asset, '-' . $commission);
        $this->trades[(int) $o['orderId']][] = [
            'orderId' => $o['orderId'],
            'price' => $o['price'],
            'qty' => $qty,
            'commission' => $commission,
            'commissionAsset' => $asset,
        ];
    }

    private function move(string $asset, string $delta): void
    {
        $this->balances[$asset] ??= ['free' => '0', 'locked' => '0'];
        $this->balances[$asset]['free'] = bcadd($this->balances[$asset]['free'], $delta, 12);
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
                    return $respond(['symbol' => $q['symbol'], 'price' => $this->prices[$q['symbol']] ?? $this->price]);
                case $path === '/api/v3/klines':
                    // raw Binance shape: [openTime, open, high, low, close, ...]
                    return $respond(array_map(
                        fn (array $c): array => [0, $c['open'] ?? $c['close'], $c['high'], $c['low'], $c['close'], '0'],
                        $this->klines
                    ));
                case $path === '/api/v3/account':
                    $this->accountCalls++;
                    if ($this->accountDown) {
                        return $respond(['code' => -1001, 'msg' => 'Internal error; unable to process your request.'], 503);
                    }
                    return $respond(['balances' => array_map(
                        fn (string $asset): array => ['asset' => $asset] + $this->balances[$asset],
                        array_keys($this->balances)
                    )]);
                case $path === '/api/v3/openOrders':
                    return $respond(array_values($this->open));
                case $path === '/api/v3/order' && $method === 'POST':
                    $cid = $q['newClientOrderId'];
                    if ($this->beforeOrder) {
                        ($this->beforeOrder)($q);
                    }
                    if (isset($this->open[$cid]) || isset($this->done[$cid])) {
                        return $respond(['code' => -2010, 'msg' => 'Duplicate order sent.'], 400);
                    }
                    if ($this->commissionMode !== null && $q['side'] === 'SELL') {
                        // a real account cannot sell what it does not hold
                        $base = substr((string) $q['symbol'], 0, -4);
                        if (bccomp($this->balances[$base]['free'] ?? '0', (string) $q['quantity'], 12) < 0) {
                            return $respond(['code' => -2010, 'msg' => 'Account has insufficient balance for requested action.'], 400);
                        }
                    }
                    $order = [
                        'symbol' => $q['symbol'],
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
                    $this->setPrice($this->price, true);
                    return $respond($order);
                case $path === '/api/v3/myTrades' && $this->commissionMode !== null:
                    if ($this->tradeLag > 0) {
                        $this->tradeLag--;
                        return $respond([]);
                    }
                    return $respond($this->trades[(int) ($q['orderId'] ?? 0)] ?? []);
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
