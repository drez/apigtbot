<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\Gateway\BinanceApiError;
use App\Domains\Bot\Gateway\BinanceGateway;
use PHPUnit\Framework\TestCase;

class BinanceGatewayTest extends TestCase
{
    /** Official example vector from the Binance Spot API signing docs. */
    private const DOC_SECRET = 'NhqPtmdSJYdKjVHjA7PZj4Mge3R5YNiP1e3UZjInClVN65XAbvqqM6A7H5fATj0j';
    private const DOC_QUERY = 'symbol=LTCBTC&side=BUY&type=LIMIT&timeInForce=GTC&quantity=1&price=0.1&recvWindow=5000&timestamp=1499827319559';
    private const DOC_SIG = 'c8db56825ae71d6d79447849e617115f4a920fa2acdcab2b053c4b2838bd6b71';

    /** @param array<int, array{status:int, headers:array, body:string}> $responses */
    private function gateway(array $responses, array &$captured = []): BinanceGateway
    {
        $transport = function (string $method, string $url, array $headers, ?string $body) use (&$responses, &$captured): array {
            $captured[] = ['method' => $method, 'url' => $url, 'headers' => $headers, 'body' => $body];
            return array_shift($responses) ?? ['status' => 200, 'headers' => [], 'body' => '{}'];
        };
        return new BinanceGateway(
            'https://testnet.binance.vision',
            'test-api-key',
            self::DOC_SECRET,
            $transport,
            static fn (): int => 1499827319559
        );
    }

    public function testSignatureMatchesBinanceDocVector(): void
    {
        $g = $this->gateway([]);
        $signed = $g->sign(self::DOC_QUERY);
        $this->assertSame(self::DOC_QUERY . '&signature=' . self::DOC_SIG, $signed);
    }

    public function testTickerPriceIsPublicAndParsed(): void
    {
        $captured = [];
        $g = $this->gateway([
            ['status' => 200, 'headers' => [], 'body' => '{"symbol":"BTCUSDT","price":"66314.65000000"}'],
        ], $captured);
        $price = $g->tickerPrice('BTCUSDT');
        $this->assertSame('66314.65000000', $price);
        $this->assertStringContainsString('/api/v3/ticker/price?symbol=BTCUSDT', $captured[0]['url']);
        $this->assertArrayNotHasKey('X-MBX-APIKEY', $captured[0]['headers'], 'public endpoint must not leak the key');
    }

    public function testKlinesCarryVolumeAndTakerBuy(): void
    {
        // raw kline: [openTime, open, high, low, close, volume, closeTime,
        //             quoteVol, trades, takerBuyBase, takerBuyQuote, ignore]
        $g = $this->gateway([
            ['status' => 200, 'headers' => [], 'body' => json_encode([
                [1690000000000, '100', '110', '90', '105', '1234', 1690000899999, '129570', 42, '600', '63000', '0'],
            ])],
        ]);
        $k = $g->klines('BTCUSDT', '4h', 1);
        $this->assertSame('105', $k[0]['close']);
        $this->assertSame('1234', $k[0]['volume'], 'volume feeds the volume z-score signal');
        $this->assertSame('600', $k[0]['taker_buy'], 'taker buy volume feeds the aggression ratio');
    }

    public function testSignedRequestCarriesKeyTimestampAndSignature(): void
    {
        $captured = [];
        $g = $this->gateway([
            ['status' => 200, 'headers' => [], 'body' => '{"balances":[{"asset":"USDT","free":"100.0","locked":"0.0"}]}'],
        ], $captured);
        $balances = $g->accountBalances();
        $this->assertSame('100.0', $balances['USDT']['free']);
        $url = $captured[0]['url'];
        $this->assertStringContainsString('timestamp=1499827319559', $url);
        $this->assertStringContainsString('signature=', $url);
        $this->assertSame('test-api-key', $captured[0]['headers']['X-MBX-APIKEY']);
    }

    public function testPlaceLimitOrderPostsMappedParams(): void
    {
        $captured = [];
        $g = $this->gateway([
            ['status' => 200, 'headers' => [], 'body' => '{"orderId":123,"status":"NEW"}'],
        ], $captured);
        $g->placeLimitOrder('BTCUSDT', 'Buy', '60000.00', '0.001', 'gt-x-L01-B-1');
        $this->assertSame('POST', $captured[0]['method']);
        $body = $captured[0]['body'];
        $this->assertStringContainsString('side=BUY', $body);
        $this->assertStringContainsString('type=LIMIT', $body);
        $this->assertStringContainsString('timeInForce=GTC', $body);
        $this->assertStringContainsString('newClientOrderId=gt-x-L01-B-1', $body);
        $this->assertStringContainsString('signature=', $body);
    }

    public function testUsedWeightCapturedFromHeaders(): void
    {
        $g = $this->gateway([
            ['status' => 200, 'headers' => ['x-mbx-used-weight-1m' => '12'], 'body' => '{"symbol":"BTCUSDT","price":"1"}'],
        ]);
        $g->tickerPrice('BTCUSDT');
        $this->assertSame(12, $g->usedWeight());
    }

    public function testApiErrorMapsCodeAndMessage(): void
    {
        $g = $this->gateway([
            ['status' => 400, 'headers' => [], 'body' => '{"code":-1013,"msg":"Filter failure: LOT_SIZE"}'],
        ]);
        try {
            $g->tickerPrice('BTCUSDT');
            $this->fail('expected BinanceApiError');
        } catch (BinanceApiError $e) {
            $this->assertSame(-1013, $e->binanceCode);
            $this->assertSame(400, $e->httpStatus);
            $this->assertStringContainsString('LOT_SIZE', $e->getMessage());
        }
    }

    public function testRateLimitCarriesRetryAfter(): void
    {
        $g = $this->gateway([
            ['status' => 429, 'headers' => ['retry-after' => '30'], 'body' => '{"code":-1003,"msg":"Too many requests"}'],
        ]);
        try {
            $g->tickerPrice('BTCUSDT');
            $this->fail('expected BinanceApiError');
        } catch (BinanceApiError $e) {
            $this->assertSame(429, $e->httpStatus);
            $this->assertSame(30, $e->retryAfter);
        }
    }

    public function testEd25519SignerProducesVerifiableUrlEncodedSignature(): void
    {
        $pem = "-----BEGIN PRIVATE KEY-----\nMC4CAQAwBQYDK2VwBCIEINXXc1+TFp10o5okUrAWahOW9/8B+UtTgiZ3jukTEVHt\n-----END PRIVATE KEY-----";
        $signer = \App\Domains\Bot\Gateway\Ed25519Signer::fromPem($pem);
        $captured = [];
        $transport = function (string $method, string $url, array $headers, ?string $body) use (&$captured): array {
            $captured[] = ['url' => $url];
            return ['status' => 200, 'headers' => [], 'body' => '{"balances":[]}'];
        };
        $g = new BinanceGateway('https://testnet.binance.vision', 'k', '', $transport, static fn (): int => 1499827319559, $signer);
        $g->accountBalances();

        parse_str(parse_url($captured[0]['url'], PHP_URL_QUERY), $q);
        $payload = 'recvWindow=5000&timestamp=1499827319559';
        $pubDer = base64_decode('MCowBQYDK2VwAyEAy08Azo9zDwQIdPDftmDQg/rF1pVVkX2xNDacBHg/yQw=');
        $this->assertTrue(
            sodium_crypto_sign_verify_detached(base64_decode($q['signature']), $payload, substr($pubDer, -32)),
            'URL-decoded signature must verify over the exact signed payload'
        );
    }

    public function testDuplicateClientOrderIdIsTreatedAsSuccess(): void
    {
        // -2010 duplicate cid on retry-after-timeout must NOT throw: the order
        // is already on the book, which is exactly what the caller wanted.
        $g = $this->gateway([
            ['status' => 400, 'headers' => [], 'body' => '{"code":-2010,"msg":"Duplicate order sent."}'],
        ]);
        $res = $g->placeLimitOrder('BTCUSDT', 'Buy', '60000', '0.001', 'gt-dup');
        $this->assertTrue($res['duplicate']);
    }
}
