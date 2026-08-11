<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\Backtester;
use PHPUnit\Framework\TestCase;

class BacktesterTest extends TestCase
{
    /** Arithmetic [100,200] n=4 → levels 100/125/150/175/200; EqualBase 550 → qty 1 per level. */
    private function config(array $over = []): array
    {
        return array_merge([
            'p_low' => '100',
            'p_high' => '200',
            'n_levels' => 4,
            'spacing' => 'Arithmetic',
            'allocation' => 'EqualBase',
            'budget_quote' => '550',
            'fee_pct' => '0.001',
            'max_position_quote' => '10000',
            'max_order_quote' => '1000',
            'daily_loss_limit_quote' => '10000',
            'breakout_buffer_pct' => '0.02',
            'breakout_policy' => 'HaltAndHold',
            'max_open_orders' => 60,
            'min_notional' => '5',
        ], $over);
    }

    public function testSingleCycleDipAndRecover(): void
    {
        $r = Backtester::run($this->config(), ['150', '120', '150']);
        $this->assertSame(1, $r['cycles']);
        // gross 25, fees (125+150)*0.001 = 0.275 → net 24.725
        $this->assertSame(0, bccomp($r['realized_pnl'], '24.725', 8));
        $this->assertSame(0, bccomp($r['fees_total'], '0.275', 8));
        $this->assertSame(0, bccomp($r['ending_inventory'], '0', 8));
        $this->assertFalse($r['halted']);
        $this->assertSame(1, $r['buy_fills']);
        $this->assertSame(1, $r['sell_fills']);
    }

    public function testCrashBelowRangeHaltsAndHolds(): void
    {
        $r = Backtester::run($this->config(), ['150', '90']);
        $this->assertSame(0, $r['cycles']);
        $this->assertSame(2, $r['buy_fills']); // 125 and 100 fill on the way down
        $this->assertSame(0, bccomp($r['ending_inventory'], '2', 8));
        $this->assertTrue($r['halted']);
        // cost basis 225, marked at 90 → unrealized 180 - 225 = -45
        $this->assertSame(0, bccomp($r['unrealized_pnl'], '-45', 8));
        $this->assertSame(0, bccomp($r['realized_pnl'], '0', 8));
    }

    public function testRangeBoundChopHarvestsRepeatedly(): void
    {
        $r = Backtester::run(
            $this->config(),
            ['150', '120', '160', '120', '160', '120', '160']
        );
        $this->assertGreaterThanOrEqual(3, $r['cycles']);
        $this->assertGreaterThan(0, (float) $r['realized_pnl']);
    }

    /**
     * Staged stop, hold policy (mirrors the live daemon): tape drops through
     * 175 and 150 (invested 325). At −29 the soft stage (60% of cap 30)
     * cancels the remaining buys, so the 120 step must NOT fill the 125 buy
     * (an unstopped run fills it). At −85 the hard stop freezes entries for
     * good; the exits stay working — 176 clips the 175 sell — but its re-arm
     * buy is suppressed.
     */
    public function testStagedStopHoldFreezesEntriesButKeepsExits(): void
    {
        $tape = ['190', '160', '148', '120', '176'];
        $free = Backtester::run($this->config(), $tape);
        $this->assertSame(3, $free['buy_fills'], 'baseline: without a stop the 125 buy fills at 120');

        $r = Backtester::run($this->config([
            'max_unrealized_loss_quote' => '30',
            'stop_policy' => 'hold',
        ]), $tape);
        $this->assertTrue($r['stop_triggered']);
        $this->assertSame(2, $r['buy_fills'], 'soft stage cancelled the deeper buys before 120');
        $this->assertSame(1, $r['sell_fills'], 'exits keep working while stopped');
        $this->assertSame(1, $r['cycles']);
        $this->assertSame(0, bccomp($r['ending_inventory'], '1', 8), 'the 175 lot is still held (hold policy)');
    }

    /** Same tape, flatten policy: the hard stop liquidates at the tape price. */
    public function testStagedStopFlattenLiquidatesAtStop(): void
    {
        $r = Backtester::run($this->config([
            'max_unrealized_loss_quote' => '30',
            'stop_policy' => 'flatten',
        ]), ['190', '160', '148', '120', '176']);
        $this->assertTrue($r['stop_triggered']);
        $this->assertSame(0, bccomp($r['ending_inventory'], '0', 8));
        // 2 @ 120 = 240 out vs 325 in, minus 0.24 liquidation fee
        $this->assertEqualsWithDelta(-85.24, (float) $r['realized_pnl'], 0.01);
        $this->assertSame(0, $r['sell_fills'], 'grid exits were cancelled at liquidation');
    }

    public function testStopDisabledByDefault(): void
    {
        $r = Backtester::run($this->config(), ['190', '160', '148', '120', '176']);
        $this->assertFalse($r['stop_triggered']);
    }

    public function testRejectsIncoherentConfig(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Backtester::run($this->config(['p_low' => '300']), ['150']);
    }

    public function testLedgerIsReconstructible(): void
    {
        $r = Backtester::run($this->config(), ['150', '120', '150']);
        // every cycle carries the numbers to rebuild realized_pnl by hand
        $sum = '0';
        foreach ($r['cycle_ledger'] as $c) {
            $expect = bcsub(
                bcmul($c['qty'], bcsub($c['sell_price'], $c['buy_price'], 12), 12),
                $c['fees_total'],
                12
            );
            $this->assertSame(0, bccomp($expect, $c['realized_pnl'], 8));
            $sum = bcadd($sum, $c['realized_pnl'], 12);
        }
        $this->assertSame(0, bccomp($sum, $r['realized_pnl'], 8));
    }
}
