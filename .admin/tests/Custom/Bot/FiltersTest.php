<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\Filters;
use PHPUnit\Framework\TestCase;

class FiltersTest extends TestCase
{
    private function filters(): Filters
    {
        // Shape mirrors GET /exchangeInfo symbols[].filters
        return new Filters([
            ['filterType' => 'PRICE_FILTER', 'tickSize' => '0.01000000'],
            ['filterType' => 'LOT_SIZE', 'stepSize' => '0.00010000', 'minQty' => '0.00010000'],
            ['filterType' => 'NOTIONAL', 'minNotional' => '5.00000000'],
        ]);
    }

    public function testPriceRoundsDownToTick(): void
    {
        $this->assertSame(0, bccomp($this->filters()->priceToTick('123.456', 'down'), '123.45', 8));
    }

    public function testPriceRoundsUpToTick(): void
    {
        $this->assertSame(0, bccomp($this->filters()->priceToTick('123.456', 'up'), '123.46', 8));
    }

    public function testExactTickUnchangedBothDirections(): void
    {
        $f = $this->filters();
        $this->assertSame(0, bccomp($f->priceToTick('123.45', 'down'), '123.45', 8));
        $this->assertSame(0, bccomp($f->priceToTick('123.45', 'up'), '123.45', 8));
    }

    public function testQtyFloorsToStep(): void
    {
        $this->assertSame(0, bccomp($this->filters()->qtyToStep('0.12345'), '0.1234', 8));
    }

    public function testQtyBelowMinQtyIsRejected(): void
    {
        $this->assertFalse($this->filters()->qtyOk('0.00009'));
        $this->assertTrue($this->filters()->qtyOk('0.0001'));
    }

    public function testNotionalCheck(): void
    {
        $f = $this->filters();
        $this->assertFalse($f->meetsNotional('10', '0.4'));   // 4 < 5
        $this->assertTrue($f->meetsNotional('10', '0.5'));    // 5 >= 5
    }

    public function testNormalizeOrderAppliesAllFilters(): void
    {
        $f = $this->filters();
        // buy: price down, qty floored, notional ok
        $o = $f->normalize('Buy', '123.456', '0.12345');
        $this->assertSame(0, bccomp($o['price'], '123.45', 8));
        $this->assertSame(0, bccomp($o['qty'], '0.1234', 8));
        // sell price rounds up so grid spacing never shrinks
        $o2 = $f->normalize('Sell', '123.456', '0.12345');
        $this->assertSame(0, bccomp($o2['price'], '123.46', 8));
    }

    public function testNormalizeReturnsNullWhenNotionalFails(): void
    {
        $this->assertNull($this->filters()->normalize('Buy', '10', '0.4'));
    }
}
