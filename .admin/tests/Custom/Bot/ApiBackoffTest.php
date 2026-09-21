<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\ApiBackoff;
use App\Domains\Bot\Gateway\BinanceApiError;
use PHPUnit\Framework\TestCase;

/**
 * Binance answers a client that keeps calling through a 429 with a 418 IP
 * ban, and each repeat lengthens it (2 min → 3 days). A flat 300 s cap kept
 * the runner knocking through a ban it had been told the length of.
 */
class ApiBackoffTest extends TestCase
{
    public function testAnOrdinaryErrorBacksOffThreeIntervalsThenDoubles(): void
    {
        $e = new BinanceApiError('boom', 500, -1000);
        $this->assertSame(15, ApiBackoff::delay($e, 5, 1));
        $this->assertSame(30, ApiBackoff::delay($e, 5, 2));
        $this->assertSame(300, ApiBackoff::delay($e, 5, 12), 'capped');
    }

    public function testARateLimitHonoursRetryAfterInFull(): void
    {
        $this->assertSame(1805, ApiBackoff::delay(new BinanceApiError('slow down', 429, -1003, 1800), 5, 1));
    }

    public function testAnIpBanWithoutAHintStartsAtTwoMinutesAndDoubles(): void
    {
        $e = new BinanceApiError('banned', 418, -1003);
        $this->assertSame(120, ApiBackoff::delay($e, 5, 1));
        $this->assertSame(240, ApiBackoff::delay($e, 5, 2));
        $this->assertSame(3600, ApiBackoff::delay($e, 5, 20), 'capped at an hour between probes');
    }

    public function testRateLimitedIsRecognisedByStatusOrCode(): void
    {
        $this->assertTrue(ApiBackoff::rateLimited(new BinanceApiError('x', 429)));
        $this->assertTrue(ApiBackoff::rateLimited(new BinanceApiError('x', 418)));
        $this->assertTrue(ApiBackoff::rateLimited(new BinanceApiError('x', 400, -1003)));
        $this->assertFalse(ApiBackoff::rateLimited(new BinanceApiError('x', 400, -2010)));
    }
}
