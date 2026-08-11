<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\IntendedOrder;
use App\Domains\Bot\RiskManager;
use PHPUnit\Framework\TestCase;

class RiskManagerTest extends TestCase
{
    private function config(array $over = []): array
    {
        return array_merge([
            'p_low' => '100',
            'p_high' => '200',
            'n_levels' => 4,
            'spacing' => 'Geometric',
            'budget_quote' => '1000',
            'fee_pct' => '0.001',
            'max_position_quote' => '1500',
            'max_order_quote' => '400',
            'daily_loss_limit_quote' => '50',
            'breakout_buffer_pct' => '0.02',
            'breakout_policy' => 'HaltAndHold',
            'max_open_orders' => 10,
            'min_notional' => '5',
        ], $over);
    }

    private function snapshot(array $over = []): array
    {
        return array_merge([
            'market_price' => '150',
            'invested_quote' => '0',
            'realized_pnl_today' => '0',
            'open_orders' => 2,
        ], $over);
    }

    private function buy(string $price = '125', string $qty = '1'): IntendedOrder
    {
        return new IntendedOrder('Buy', 1, $price, $qty);
    }

    public function testApprovesSaneOrder(): void
    {
        $d = RiskManager::review($this->buy(), $this->snapshot(), $this->config());
        $this->assertTrue($d->approved);
        $this->assertFalse($d->kill);
    }

    public function testVetoesOversizedOrder(): void
    {
        $d = RiskManager::review($this->buy('125', '4'), $this->snapshot(), $this->config()); // 500 > 400
        $this->assertFalse($d->approved);
        $this->assertStringContainsString('per-order', $d->reason);
    }

    public function testVetoesWhenPositionCapReached(): void
    {
        $d = RiskManager::review($this->buy(), $this->snapshot(['invested_quote' => '1450']), $this->config());
        $this->assertFalse($d->approved);
        $this->assertStringContainsString('position', $d->reason);
    }

    public function testSellsAllowedEvenAtPositionCap(): void
    {
        $sell = new IntendedOrder('Sell', 1, '150', '1');
        $d = RiskManager::review($sell, $this->snapshot(['invested_quote' => '1500']), $this->config());
        $this->assertTrue($d->approved, 'position cap must never block exits');
    }

    public function testDailyLossBreachTripsKill(): void
    {
        $d = RiskManager::review($this->buy(), $this->snapshot(['realized_pnl_today' => '-51']), $this->config());
        $this->assertFalse($d->approved);
        $this->assertTrue($d->kill);
    }

    public function testSellsExemptFromPerOrderCap(): void
    {
        // prod 2026-07-22 13:41: the exit for a just-filled buy was vetoed
        // (sell notional is one level above the buy that passed the cap) —
        // inventory was left with no exit. Exits must never be size-capped.
        $sell = new IntendedOrder('Sell', 1, '150', '3'); // 450 > 400 cap
        $d = RiskManager::review($sell, $this->snapshot(), $this->config());
        $this->assertTrue($d->approved, 'per-order cap must never block exits');
    }

    public function testSellsExemptFromMaxOpenOrders(): void
    {
        $sell = new IntendedOrder('Sell', 1, '150', '1');
        $d = RiskManager::review($sell, $this->snapshot(['open_orders' => 10]), $this->config());
        $this->assertTrue($d->approved, 'order-count cap must never block exits');
    }

    public function testSellsDoNotTripDailyLossKill(): void
    {
        $sell = new IntendedOrder('Sell', 1, '150', '1');
        $d = RiskManager::review($sell, $this->snapshot(['realized_pnl_today' => '-51']), $this->config());
        $this->assertTrue($d->approved, 'a bad day must not block the exits that end it');
        $this->assertFalse($d->kill);
    }

    public function testSellsStillVetoedOnImplausiblePrice(): void
    {
        $sell = new IntendedOrder('Sell', 1, '400', '1'); // >50% from market
        $d = RiskManager::review($sell, $this->snapshot(), $this->config());
        $this->assertFalse($d->approved, 'fat-finger guard still applies to exits');
    }

    public function testBreakoutBelowRangeHalts(): void
    {
        // 100*(1-0.02) = 98; price 97.9 breaches
        $d = RiskManager::review($this->buy(), $this->snapshot(['market_price' => '97.9']), $this->config());
        $this->assertFalse($d->approved);
        $this->assertTrue($d->halt);
        $this->assertFalse($d->kill, 'HaltAndHold policy must not flatten');
    }

    public function testBreakoutWithFlattenPolicySignalsKill(): void
    {
        $d = RiskManager::review(
            $this->buy(),
            $this->snapshot(['market_price' => '97.9']),
            $this->config(['breakout_policy' => 'Flatten'])
        );
        $this->assertTrue($d->kill);
        $this->assertTrue($d->flatten);
    }

    public function testVetoesImplausiblePrice(): void
    {
        // buy 100% above market = bad feed or fat-finger
        $d = RiskManager::review($this->buy('300', '0.5'), $this->snapshot(), $this->config());
        $this->assertFalse($d->approved);
        $this->assertStringContainsString('implausib', $d->reason);
    }

    public function testVetoesWhenTooManyOpenOrders(): void
    {
        $d = RiskManager::review($this->buy(), $this->snapshot(['open_orders' => 10]), $this->config());
        $this->assertFalse($d->approved);
        $this->assertStringContainsString('open orders', $d->reason);
    }

    public function testValidateRunConfigAcceptsSaneConfig(): void
    {
        $this->assertSame([], RiskManager::validateRunConfig($this->config()));
    }

    public function testValidateRunConfigRejectsInvertedRange(): void
    {
        $errors = RiskManager::validateRunConfig($this->config(['p_low' => '300']));
        $this->assertNotEmpty($errors);
    }

    public function testValidateRunConfigEnforcesProfitabilityFloor(): void
    {
        // 200 levels over [100,200] → spacing ≈ 0.35% ... use tighter: fee 0.001 → floor = 0.006
        $errors = RiskManager::validateRunConfig($this->config(['n_levels' => 200]));
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('fee', implode(' ', $errors));
    }

    public function testValidateRunConfigChecksBudgetVsNotional(): void
    {
        // 4 buy levels, budget 10 → 2.5 per level < minNotional 5
        $errors = RiskManager::validateRunConfig($this->config(['budget_quote' => '10']));
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('notional', strtolower(implode(' ', $errors)));
    }

    public function testValidateRunConfigUsesDeployedBudgetForNotional(): void
    {
        // budget 60 at 100% → 15/level clears minNotional 5; at 20% → 3/level fails
        $this->assertSame([], RiskManager::validateRunConfig($this->config(['budget_quote' => '60', 'deploy_pct' => 100])));
        $errors = RiskManager::validateRunConfig($this->config(['budget_quote' => '60', 'deploy_pct' => 20]));
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('notional', strtolower(implode(' ', $errors)));
    }

    public function testValidateRunConfigRejectsDeployPctOutOfRange(): void
    {
        $this->assertNotEmpty(RiskManager::validateRunConfig($this->config(['deploy_pct' => 5])));
        $this->assertNotEmpty(RiskManager::validateRunConfig($this->config(['deploy_pct' => 150])));
    }

    public function testValidateRunConfigAllowsDeployPctZeroAsFlat(): void
    {
        // 0 = deliberate FLAT (no buys) — legal even when the per-level
        // notional check would fail on any deployed budget
        $this->assertSame([], RiskManager::validateRunConfig($this->config(['deploy_pct' => 0])));
        $this->assertSame([], RiskManager::validateRunConfig($this->config(['budget_quote' => '60', 'deploy_pct' => 0])));
    }

    public function testKillSwitchBlocksEverything(): void
    {
        $d = RiskManager::review($this->buy(), $this->snapshot(['kill_switch' => true]), $this->config());
        $this->assertFalse($d->approved);
    }
}
