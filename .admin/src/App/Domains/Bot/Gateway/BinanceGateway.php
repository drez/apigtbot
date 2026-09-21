<?php

namespace App\Domains\Bot\Gateway;

/**
 * The only class that talks to Binance. Testnet vs mainnet is just the base
 * URL + keys. The HTTP transport and the clock are injectable so every code
 * path is unit-testable offline; the default transport is curl.
 *
 * Transport contract:
 *   fn(string $method, string $url, array $headers, ?string $body)
 *     : array{status:int, headers:array<string,string>, body:string}
 * Response header names are lowercase.
 */
class BinanceGateway implements GatewayInterface
{
    /** @var callable */
    private $transport;
    /** @var callable returns current epoch milliseconds */
    private $clockMs;
    private ?int $usedWeight = null;
    /** server clock − host clock, learned from a -1021 rejection (see request()) */
    private int $clockOffsetMs = 0;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
        private readonly string $apiSecret,
        ?callable $transport = null,
        ?callable $clockMs = null,
        private readonly ?Ed25519Signer $signer = null
    ) {
        $this->transport = $transport ?? [$this, 'curlTransport'];
        $this->clockMs = $clockMs ?? static fn (): int => (int) floor(microtime(true) * 1000);
    }

    // ── public endpoints (no auth) ──────────────────────────────────────

    public function ping(): bool
    {
        $this->request('GET', '/api/v3/ping', [], false);
        return true;
    }

    /** Symbol entry from exchangeInfo (filters live under ['filters']). */
    public function exchangeInfo(string $symbol): array
    {
        $data = $this->request('GET', '/api/v3/exchangeInfo', ['symbol' => $symbol], false);
        if (empty($data['symbols'][0])) {
            throw new BinanceApiError("symbol $symbol not found in exchangeInfo", 200);
        }
        return $data['symbols'][0];
    }

    public function tickerPrice(string $symbol): string
    {
        $data = $this->request('GET', '/api/v3/ticker/price', ['symbol' => $symbol], false);
        return (string) $data['price'];
    }

    /**
     * Public candlestick history, newest last.
     *
     * @return array<int, array{open_time:int, open:string, high:string, low:string, close:string, volume:?string, taker_buy:?string}>
     */
    public function klines(string $symbol, string $interval, int $limit): array
    {
        $raw = $this->request('GET', '/api/v3/klines', [
            'symbol' => $symbol,
            'interval' => $interval,
            'limit' => min(1000, max(1, $limit)),
        ], false);
        $out = [];
        foreach ($raw as $k) {
            if (!is_array($k) || count($k) < 5) {
                continue;
            }
            $out[] = [
                // epoch seconds (Binance sends ms): the candle store keys on it
                'open_time' => (int) ((int) $k[0] / 1000),
                'open' => (string) $k[1], 'high' => (string) $k[2], 'low' => (string) $k[3], 'close' => (string) $k[4],
                'volume' => isset($k[5]) ? (string) $k[5] : null,
                'taker_buy' => isset($k[9]) ? (string) $k[9] : null,
            ];
        }
        return $out;
    }

    /** Generic public GET (used for depth, futures premium index, ...). */
    public function publicGet(string $path, array $params = []): array
    {
        return $this->request('GET', $path, $params, false);
    }

    /**
     * Order-book depth imbalance: bidVolume/(bidVolume+askVolume) over the top
     * $limit levels. > 0.5 = bid-heavy (buying pressure), < 0.5 = ask-heavy.
     */
    public function depthImbalance(string $symbol, int $limit = 100): ?float
    {
        $d = $this->publicGet('/api/v3/depth', ['symbol' => $symbol, 'limit' => $limit]);
        $bid = 0.0;
        $ask = 0.0;
        foreach ($d['bids'] ?? [] as $b) {
            $bid += (float) $b[1];
        }
        foreach ($d['asks'] ?? [] as $a) {
            $ask += (float) $a[1];
        }
        $tot = $bid + $ask;
        return $tot > 0 ? round($bid / $tot, 4) : null;
    }

    // ── signed endpoints ────────────────────────────────────────────────

    /** @return array<string, array{free:string, locked:string}> keyed by asset */
    public function accountBalances(): array
    {
        $data = $this->request('GET', '/api/v3/account', [], true);
        $out = [];
        foreach ($data['balances'] ?? [] as $b) {
            $out[$b['asset']] = ['free' => $b['free'], 'locked' => $b['locked']];
        }
        return $out;
    }

    public function openOrders(string $symbol): array
    {
        return $this->request('GET', '/api/v3/openOrders', ['symbol' => $symbol], true);
    }

    public function orderStatus(string $symbol, string $clientOrderId): array
    {
        return $this->request('GET', '/api/v3/order', [
            'symbol' => $symbol,
            'origClientOrderId' => $clientOrderId,
        ], true);
    }

    /**
     * Maker limit order. A -2010 "Duplicate order" response is returned as
     * ['duplicate' => true] — the idempotent retry contract: the order the
     * caller wanted is already on the book.
     */
    public function placeLimitOrder(string $symbol, string $side, string $price, string $qty, string $clientOrderId): array
    {
        try {
            $res = $this->request('POST', '/api/v3/order', [
                'symbol' => $symbol,
                'side' => strtoupper($side),
                'type' => 'LIMIT',
                'timeInForce' => 'GTC',
                'quantity' => $qty,
                'price' => $price,
                'newClientOrderId' => $clientOrderId,
            ], true);
        } catch (BinanceApiError $e) {
            if ($e->binanceCode === -2010 && str_contains(strtolower($e->getMessage()), 'duplicate')) {
                return ['duplicate' => true, 'clientOrderId' => $clientOrderId];
            }
            throw $e;
        }
        $res['duplicate'] = false;
        return $res;
    }

    public function cancelOrder(string $symbol, string $clientOrderId): array
    {
        return $this->request('DELETE', '/api/v3/order', [
            'symbol' => $symbol,
            'origClientOrderId' => $clientOrderId,
        ], true);
    }

    public function myTrades(string $symbol, array $extra = []): array
    {
        return $this->request('GET', '/api/v3/myTrades', ['symbol' => $symbol] + $extra, true);
    }

    /** Last observed X-MBX-USED-WEIGHT-1m, for proactive throttling. */
    public function usedWeight(): ?int
    {
        return $this->usedWeight;
    }

    /**
     * Append the signature to an already-built query string. Ed25519 keys
     * (registered-public-key flow, e.g. the Spot testnet) produce a base64
     * signature that must be URL-encoded; HMAC hex passes through unchanged.
     */
    public function sign(string $query): string
    {
        $sig = $this->signer !== null
            ? $this->signer->sign($query)
            : hash_hmac('sha256', $query, $this->apiSecret);
        return $query . '&signature=' . rawurlencode($sig);
    }

    // ── internals ───────────────────────────────────────────────────────

    public function clockOffsetMs(): int
    {
        return $this->clockOffsetMs;
    }

    /**
     * A signed request is stamped with the host clock; a host that drifted
     * past recvWindow (VM suspend/resume, dead NTP) gets -1021 on EVERY signed
     * call — no fills seen, no exits placed — until a human fixes the clock.
     * Resync on the exchange's own time once and retry; a second -1021 is a
     * real problem and surfaces.
     */
    private function request(string $method, string $path, array $params, bool $signed): array
    {
        try {
            return $this->send($method, $path, $params, $signed);
        } catch (BinanceApiError $e) {
            if (!$signed || $e->binanceCode !== -1021) {
                throw $e;
            }
        }
        $server = (int) ($this->send('GET', '/api/v3/time', [], false)['serverTime'] ?? 0);
        if ($server > 0) {
            $this->clockOffsetMs = $server - ($this->clockMs)();
        }
        return $this->send($method, $path, $params, $signed);
    }

    private function send(string $method, string $path, array $params, bool $signed): array
    {
        $headers = [];
        if ($signed) {
            $params['recvWindow'] = 5000;
            $params['timestamp'] = ($this->clockMs)() + $this->clockOffsetMs;
            $headers['X-MBX-APIKEY'] = $this->apiKey;
        }
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        if ($signed) {
            $query = $this->sign($query);
        }

        $url = $this->baseUrl . $path;
        $body = null;
        if ($method === 'GET' || $method === 'DELETE') {
            $url .= $query === '' ? '' : '?' . $query;
        } else {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            $body = $query;
        }

        $res = ($this->transport)($method, $url, $headers, $body);
        $this->usedWeight = isset($res['headers']['x-mbx-used-weight-1m'])
            ? (int) $res['headers']['x-mbx-used-weight-1m']
            : $this->usedWeight;

        $decoded = json_decode($res['body'], true);
        if ($res['status'] >= 400 || (is_array($decoded) && isset($decoded['code']) && (int) $decoded['code'] < 0)) {
            $retryAfter = isset($res['headers']['retry-after']) ? (int) $res['headers']['retry-after'] : null;
            throw new BinanceApiError(
                (string) ($decoded['msg'] ?? ('HTTP ' . $res['status'])),
                $res['status'],
                (int) ($decoded['code'] ?? 0),
                $retryAfter
            );
        }
        return is_array($decoded) ? $decoded : [];
    }

    private function curlTransport(string $method, string $url, array $headers, ?string $body): array
    {
        $ch = curl_init($url);
        $headerLines = [];
        foreach ($headers as $k => $v) {
            $headerLines[] = "$k: $v";
        }
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_HEADERFUNCTION => function ($ch, string $line) use (&$respHeaders): int {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $respHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
                }
                return strlen($line);
            },
        ]);
        $respHeaders = [];
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $out = curl_exec($ch);
        if ($out === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new BinanceApiError("transport error: $err", 0);
        }
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        return ['status' => $status, 'headers' => $respHeaders, 'body' => (string) $out];
    }
}
