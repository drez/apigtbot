<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\NavLedger;
use PHPUnit\Framework\TestCase;

/** Wallet NAV vs the two honest benchmarks: never deployed (USDT) and just held BTC (HODL). */
class NavLedgerTest extends TestCase
{
    public function testNeedsTwoPoints(): void
    {
        $this->assertNull(NavLedger::benchmark([]));
        $this->assertNull(NavLedger::benchmark([['equity' => '1000', 'ref_price' => '60000', 'at' => '2026-08-01 00:00']]));
    }

    public function testReturnsExcessVsHodlAndUsdtWithDrawdown(): void
    {
        $b = NavLedger::benchmark([
            ['equity' => '1000', 'ref_price' => '60000', 'at' => '2026-08-01 00:00'],
            ['equity' => '1100', 'ref_price' => '66000', 'at' => '2026-08-02 00:00'],
            ['equity' => '990',  'ref_price' => '63000', 'at' => '2026-08-02 12:00'],
            ['equity' => '1050', 'ref_price' => '63000', 'at' => '2026-08-03 00:00'],
        ]);
        $this->assertSame(5.0, $b['nav_return_pct']);
        $this->assertSame(5.0, $b['hodl_return_pct']);
        $this->assertSame(0.0, $b['excess_vs_hodl_pct']);
        $this->assertSame(5.0, $b['excess_vs_usdt_pct']);
        $this->assertSame(10.0, $b['max_drawdown_pct'], '1100 peak → 990 trough');
        $this->assertSame(['2026-08-01' => '1000.00', '2026-08-02' => '990.00', '2026-08-03' => '1050.00'], $b['equity_by_day']);
    }

    /**
     * A budget top-up is money added, not money earned. Raising the shared
     * budget lifts wallet equity by the same amount, and a naive
     * last/first ratio books the whole deposit as return — prod 2026-09-03:
     * the 1000 -> 1300 raise showed as +28.8% NAV against HODL +1.59%, while
     * the fleet was in fact roughly flat. Returns are time-weighted: the
     * chain breaks at every external flow and the flow itself is excluded.
     */
    public function testADepositIsNotAReturn(): void
    {
        $b = NavLedger::benchmark([
            ['equity' => '1000', 'ref_price' => '60000', 'at' => '2026-09-02 00:00', 'budget' => '1000'],
            ['equity' => '1300', 'ref_price' => '60000', 'at' => '2026-09-03 00:00', 'budget' => '1300'],
        ]);
        $this->assertSame(0.0, $b['nav_return_pct'], 'equity rose only by the deposit');
        $this->assertSame(0.0, $b['excess_vs_usdt_pct']);
        $this->assertSame('300.00', $b['net_flows']);
    }

    public function testReturnAfterADepositIsMeasuredOnTheLargerBase(): void
    {
        $b = NavLedger::benchmark([
            ['equity' => '1000', 'ref_price' => '60000', 'at' => '2026-09-02 00:00', 'budget' => '1000'],
            ['equity' => '1300', 'ref_price' => '60000', 'at' => '2026-09-03 00:00', 'budget' => '1300'],
            ['equity' => '1313', 'ref_price' => '60000', 'at' => '2026-09-04 00:00', 'budget' => '1300'],
        ]);
        $this->assertSame(1.0, $b['nav_return_pct'], '13 earned on 1300, not 313 on 1000');
        $this->assertSame('300.00', $b['net_flows']);
    }

    public function testAWithdrawalIsNotALoss(): void
    {
        $b = NavLedger::benchmark([
            ['equity' => '1000', 'ref_price' => '60000', 'at' => '2026-09-02 00:00', 'budget' => '1000'],
            ['equity' => '600',  'ref_price' => '60000', 'at' => '2026-09-03 00:00', 'budget' => '600'],
        ]);
        $this->assertSame(0.0, $b['nav_return_pct']);
        $this->assertSame('-400.00', $b['net_flows']);
    }

    /** A deposit must not stamp a fake new equity peak that later reads as drawdown. */
    public function testDrawdownIgnoresTheDeposit(): void
    {
        $b = NavLedger::benchmark([
            ['equity' => '1000', 'ref_price' => '60000', 'at' => '2026-09-02 00:00', 'budget' => '1000'],
            ['equity' => '1300', 'ref_price' => '60000', 'at' => '2026-09-03 00:00', 'budget' => '1300'],
            ['equity' => '1287', 'ref_price' => '60000', 'at' => '2026-09-04 00:00', 'budget' => '1300'],
        ]);
        $this->assertSame(1.0, $b['max_drawdown_pct'], '13 lost on a 1300 base is 1%, not 1300 vs a 1000 peak');
    }

    /** No budget key anywhere (older rows): behaves exactly as before. */
    public function testSeriesWithoutBudgetIsUnchanged(): void
    {
        $b = NavLedger::benchmark([
            ['equity' => '1000', 'ref_price' => '60000', 'at' => '2026-08-01 00:00'],
            ['equity' => '1050', 'ref_price' => '60000', 'at' => '2026-08-03 00:00'],
        ]);
        $this->assertSame(5.0, $b['nav_return_pct']);
        $this->assertSame('0.00', $b['net_flows']);
    }

    public function testBeatingHodlShowsPositiveExcess(): void
    {
        $b = NavLedger::benchmark([
            ['equity' => '1000', 'ref_price' => '60000', 'at' => '2026-08-01 00:00'],
            ['equity' => '1020', 'ref_price' => '57000', 'at' => '2026-08-05 00:00'],
        ]);
        $this->assertSame(2.0, $b['nav_return_pct']);
        $this->assertSame(-5.0, $b['hodl_return_pct']);
        $this->assertSame(7.0, $b['excess_vs_hodl_pct']);
    }
}
