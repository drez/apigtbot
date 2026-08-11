<?php

namespace Tests\Custom\Bot;

use App\Domains\Dashboard\DashboardRenderer;
use PHPUnit\Framework\TestCase;

class DashboardRendererTest extends TestCase
{
    private function vm(array $over = []): array
    {
        return array_replace_recursive([
            'baseUrl' => 'https://x.test/',
            'run' => [
                'id' => 1, 'label' => 'BTC soak', 'symbol' => 'BTCUSDT', 'status' => 'Testnet',
                'kill_switch' => false, 'heartbeat_stale' => false, 'last_tick_at' => '2026-07-21 12:00:00',
                'p_low' => '60000', 'p_high' => '72000',
            ],
            'kpis' => [
                'realized_pnl' => '24.725', 'realized_today' => '5.0', 'fees' => '0.55',
                'cycles' => 3, 'open_buys' => 7, 'open_sells' => 2,
                'inventory' => '0.0021', 'invested' => '130.0',
            ],
            'points' => [
                ['price' => '60000', 'side' => 'Buy', 'state' => 'Filled'],
                ['price' => '61000', 'side' => 'Sell', 'state' => 'Filled'],
            ],
            'cycles' => [
                ['level' => 3, 'buy' => '60000', 'sell' => '61000', 'qty' => '0.001', 'pnl' => '0.94', 'at' => '2026-07-21 11:00'],
            ],
            'events' => [
                ['level' => 'Alert', 'kind' => 'kill', 'message' => 'kill switch set', 'at' => '2026-07-21 10:00'],
            ],
            'daily' => [
                ['day' => '2026-07-20', 'pnl' => '10.0'],
                ['day' => '2026-07-21', 'pnl' => '-2.0'],
            ],
        ], $over);
    }

    public function testRendersKpisAndSections(): void
    {
        $html = (new DashboardRenderer())->render($this->vm());
        $this->assertStringContainsString('Realized P/L', $html);
        $this->assertStringContainsString('24.73', $html); // number_format 2dp
        $this->assertStringContainsString('BTC soak', $html);
        $this->assertStringContainsString('Testnet', $html);
        $this->assertStringContainsString('<svg', $html); // trade chart embedded
        $this->assertStringContainsString('61,000.00', $html); // cycle row (money-formatted)
    }

    public function testKillSwitchAndStaleHeartbeatShowWarnings(): void
    {
        $html = (new DashboardRenderer())->render($this->vm([
            'run' => ['kill_switch' => true, 'heartbeat_stale' => true],
        ]));
        $this->assertStringContainsString('is-alert', $html);
        $this->assertStringContainsString('Kill switch', $html);
        $this->assertStringContainsString('stale', strtolower($html));
    }

    public function testRestartButtonShownOnlyWhileKilled(): void
    {
        $killed = (new DashboardRenderer())->render($this->vm(['run' => ['kill_switch' => true]]));
        $this->assertStringContainsString('class="dash-restart-btn"', $killed);
        $this->assertStringContainsString('data-run="1"', $killed);
        $this->assertStringContainsString('Restart trading', $killed);

        $running = (new DashboardRenderer())->render($this->vm());
        $this->assertStringNotContainsString('class="dash-restart-btn"', $running);
    }

    public function testNegativePnlGetsNegativeTone(): void
    {
        $html = (new DashboardRenderer())->render($this->vm([
            'kpis' => ['realized_pnl' => '-12.5'],
        ]));
        $this->assertStringContainsString('is-neg', $html);
    }

    public function testEmptyStatesRenderWithoutError(): void
    {
        // array_replace_recursive won't clear arrays with [] — set them post-merge.
        $vm = $this->vm();
        $vm['cycles'] = $vm['events'] = $vm['points'] = $vm['daily'] = [];
        $html = (new DashboardRenderer())->render($vm);
        $this->assertStringContainsString('No trades', $html); // chart placeholder
        $this->assertStringContainsString('No completed cycles', $html);
    }

    public function testMaliciousLabelIsEscaped(): void
    {
        $html = (new DashboardRenderer())->render($this->vm([
            'run' => ['label' => '<script>alert(1)</script>'],
        ]));
        $this->assertStringNotContainsString('<script>alert(1)', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testGroupedTilesShowPositionValues(): void
    {
        $html = (new DashboardRenderer())->render($this->vm([
            'kpis' => [
                'committed_buys' => '372.1', 'open_sell_value' => '167.9',
                'inventory_value' => '165.36', 'account_value' => '1210.55',
                'bal_base' => '1.0025', 'bal_quote' => '9834.69',
            ],
        ]));
        $this->assertStringContainsString('In open buys', $html);
        $this->assertStringContainsString('372.10', $html);
        $this->assertStringContainsString('In open sells', $html);
        $this->assertStringContainsString('167.90', $html);
        // per-run Account value tile is gone (the band's is the only one; no
        // band in this vm → the per-run value must not leak in anywhere)
        $this->assertStringNotContainsString('1,210.55', $html);
        // section captions make the tile groups scannable
        $this->assertStringContainsString('>P/L<', $html);
        $this->assertStringContainsString('>Position<', $html);
        $this->assertStringNotContainsString('>Grid<', $html);
        $this->assertStringNotContainsString('>Wallet<', $html);
    }

    public function testFeesTileCarriesCycleCount(): void
    {
        $html = (new DashboardRenderer())->render($this->vm());
        // cycles fold into the fees tile label — no standalone Cycles tile
        $this->assertStringContainsString('Fees · 3 cycles', $html);
        $this->assertStringNotContainsString('>Cycles<', $html);
    }

    public function testInventoryTileCarriesCostBasis(): void
    {
        // cost basis folds into the inventory label — no standalone tile
        $html = (new DashboardRenderer())->render($this->vm(['kpis' => ['inventory_value' => '165.36']]));
        $this->assertStringContainsString('Inventory 0.0021 BTC · cost 130.00', $html);
        $this->assertStringNotContainsString('Invested (cost basis)', $html);

        $noPrice = (new DashboardRenderer())->render($this->vm());
        $this->assertStringContainsString('Inventory · cost 130.00', $noPrice);
    }

    public function testWalletBaseTileRendersInPositionGroupWhenStamped(): void
    {
        $html = (new DashboardRenderer())->render($this->vm([
            'kpis' => ['bal_base' => '0.0025', 'bal_quote' => '912.34'],
        ]));
        $this->assertStringContainsString('Wallet BTC', $html);
        $this->assertStringContainsString('0.0025', $html);
        // per-run Wallet USDT tile removed — only the band's remains
        $this->assertSame(1, substr_count($html, 'Wallet USDT'), 'band tile only, no per-run one');
        $this->assertStringNotContainsString('912.34', $html);
    }

    public function testWalletTilesHiddenWhenNeverStamped(): void
    {
        // default vm carries no bal_* keys (pre-migration / dry-run) — the
        // per-run wallet tiles stay hidden. The global band always shows its
        // own "Wallet USDT" tile (system-wide, "—" until stamped), so "Wallet
        // USDT" itself now legitimately appears once (the band); only the
        // per-run pairing ("Wallet BTC" alongside it) must stay absent.
        $html = (new DashboardRenderer())->render($this->vm());
        $this->assertStringNotContainsString('Wallet BTC', $html);
        $this->assertSame(1, substr_count($html, 'Wallet USDT'), 'only the band tile, not a per-run one');
    }

    public function testPositionPillFlatWhenInventoryZero(): void
    {
        $html = (new DashboardRenderer())->render($this->vm([
            'kpis' => ['inventory' => '0', 'inventory_value' => null],
        ]));
        $this->assertStringContainsString('<span class="dash-pos-pill dash-pos-flat">FLAT</span>', $html);
        $this->assertStringNotContainsString('<span class="dash-pos-pill dash-pos-long"', $html);
    }

    public function testPositionPillLongShowsQtyAssetAndApproxValue(): void
    {
        $html = (new DashboardRenderer())->render($this->vm([
            'kpis' => ['inventory' => '0.00150000', 'inventory_value' => '96.35'],
        ]));
        $this->assertStringContainsString('dash-pos-pill dash-pos-long', $html);
        $this->assertStringContainsString('LONG 0.0015 BTC', $html);
        // the ≈ entity must render literally, not double-escaped
        $this->assertStringContainsString('&#8776; 96.35', $html);
        $this->assertStringNotContainsString('&amp;#8776;', $html);
    }

    public function testPositionPillOmitsApproxValueWhenInventoryValueNull(): void
    {
        $html = (new DashboardRenderer())->render($this->vm([
            'kpis' => ['inventory' => '0.0015', 'inventory_value' => null],
        ]));
        $this->assertStringContainsString('LONG 0.0015 BTC', $html);
        $this->assertStringNotContainsString('&#8776;', $html);
    }

    public function testPositionPillOmittedWhenNoKpis(): void
    {
        $html = (new DashboardRenderer())->render(['baseUrl' => 'https://x.test/', 'run' => null]);
        $this->assertStringNotContainsString('<span class="dash-pos-pill', $html);
    }

    public function testPositionPillRendersInRunHeadBeforeDaemonControls(): void
    {
        $html = (new DashboardRenderer())->render($this->vm());
        $headPos = strpos($html, 'dash-run-head');
        $pillPos = strpos($html, 'dash-pos-pill');
        $cmdsPos = strpos($html, 'dash-cmds');
        $this->assertNotFalse($headPos);
        $this->assertNotFalse($pillPos);
        $this->assertNotFalse($cmdsPos);
        $this->assertGreaterThan($headPos, $pillPos);
        $this->assertLessThan($cmdsPos, $pillPos);
    }

    public function testBudgetFreeTileRendered(): void
    {
        $html = (new DashboardRenderer())->render($this->vm([
            'kpis' => ['quote_uncommitted' => '870'],
        ]));
        $this->assertStringContainsString('Budget free', $html);
        $this->assertStringContainsString('870.00', $html);
    }

    public function testCurrentPriceLinePassedToChart(): void
    {
        $html = (new DashboardRenderer())->render($this->vm([
            'run' => ['last_price' => '66500'],
        ]));
        $this->assertStringContainsString('tc-price', $html);
        // default vm has no last_price → no line
        $this->assertStringNotContainsString('tc-price', (new DashboardRenderer())->render($this->vm()));
    }

    public function testNoActiveRunRendersInviteState(): void
    {
        $html = (new DashboardRenderer())->render(['baseUrl' => 'https://x.test/', 'run' => null]);
        $this->assertStringContainsString('No grid run', $html);
        $this->assertStringContainsString('GridRun/edit', $html); // CTA to create one
    }

    private function tabs(): array
    {
        return [
            ['id' => 1, 'symbol' => 'BTCUSDT', 'status' => 'Testnet', 'kill_switch' => false,
             'heartbeat_stale' => false, 'selected' => true, 'href' => 'https://x.test/?run=1'],
            ['id' => 4, 'symbol' => 'BNBUSDT', 'status' => 'Testnet', 'kill_switch' => false,
             'heartbeat_stale' => true, 'selected' => false, 'href' => 'https://x.test/?run=4'],
        ];
    }

    public function testTabBarRendersOneTabPerRunWithHealthDots(): void
    {
        $html = (new DashboardRenderer())->render($this->vm(['tabs' => $this->tabs()]));
        $this->assertStringContainsString('dash-tabs', $html);
        $this->assertStringContainsString('BTCUSDT #1', $html);
        $this->assertStringContainsString('BNBUSDT #4', $html);
        $this->assertStringContainsString('?run=4', $html); // unselected tab links out
        // selected tab marked; stale run gets the warn dot, fresh one the ok dot
        $this->assertStringContainsString('is-selected', $html);
        $this->assertStringContainsString('dot-warn', $html);
        $this->assertStringContainsString('dot-ok', $html);
    }

    public function testKillSwitchTabGetsAlertDot(): void
    {
        $tabs = $this->tabs();
        $tabs[1]['kill_switch'] = true;
        $html = (new DashboardRenderer())->render($this->vm(['tabs' => $tabs]));
        $this->assertStringContainsString('dot-alert', $html);
    }

    public function testTabBarOmittedForSingleOrNoTabs(): void
    {
        $this->assertStringNotContainsString('<div class="dash-tabs">', (new DashboardRenderer())->render($this->vm()));
        $one = [$this->tabs()[0]];
        $this->assertStringNotContainsString('<div class="dash-tabs">', (new DashboardRenderer())->render($this->vm(['tabs' => $one])));
    }

    private function wallet(array $over = []): array
    {
        return array_replace([
            'mode' => 'simulated',
            'assets' => [
                ['asset' => 'USDT', 'qty' => '904.905', 'value' => '904.905'],
                ['asset' => 'BTC', 'qty' => '1.0', 'value' => '65000.00'],
            ],
            'total_quote' => '65904.905',
        ], $over);
    }

    /** Global-band fixture — the mode pill/switch + wallet holdings strip now
     *  live here (see the dashboard-global-band plan), not in vm['wallet']. */
    private function band(array $over = []): array
    {
        return array_replace([
            'mode' => 'simulated',
            'budget' => '1000',
            'slices_sum' => '600',
            'wallet_usdt' => '904.905',
            'account_value' => '1210.55',
            'value_partial' => false,
            'realized_total' => '24.725',
            'realized_today' => '5.0',
            'total_pl' => '210.55',
            'assets' => [
                ['asset' => 'USDT', 'qty' => '904.905', 'value' => '904.905'],
                ['asset' => 'BTC', 'qty' => '0.0015', 'value' => '93.2'],
            ],
            'holdings_value' => '93.2',
            'open_sell_value' => '52.5',
            'open_buy_value' => '95.0',
            'free_budget' => '150.0',
        ], $over);
    }

    public function testModePillSimulatedAndSwitchButtonTargetsReal(): void
    {
        $html = (new DashboardRenderer())->render($this->vm(['band' => $this->band()]));
        $this->assertStringContainsString('dash-mode-pill dash-mode-simulated', $html);
        $this->assertStringContainsString('SIMULATED', $html);
        $this->assertStringContainsString('data-target="real"', $html);
    }

    public function testModePillRealShowsSwitchToSimulatedButton(): void
    {
        $html = (new DashboardRenderer())->render($this->vm([
            'band' => $this->band(['mode' => 'real']),
        ]));
        $this->assertStringContainsString('dash-mode-pill dash-mode-real', $html);
        $this->assertStringContainsString('REAL', $html);
        $this->assertStringContainsString('data-target="simulated"', $html);
    }

    public function testModePillMixedShowsBothSwitchButtons(): void
    {
        $html = (new DashboardRenderer())->render($this->vm([
            'band' => $this->band(['mode' => 'mixed']),
        ]));
        $this->assertStringContainsString('dash-mode-pill dash-mode-mixed', $html);
        $this->assertStringContainsString('MIXED', $html);
        $this->assertStringContainsString('data-target="real"', $html);
        $this->assertStringContainsString('data-target="simulated"', $html);
    }

    public function testModePillNoneRendersWithoutSwitchButton(): void
    {
        $html = (new DashboardRenderer())->render($this->vm([
            'band' => $this->band(['mode' => 'none', 'assets' => []]),
        ]));
        $this->assertStringContainsString('dash-mode-pill dash-mode-none', $html);
        $this->assertStringContainsString('NONE', $html);
        $this->assertStringNotContainsString('class="dash-mode-btn"', $html);
    }

    public function testBandRendersBeforeTabsMarkup(): void
    {
        $html = (new DashboardRenderer())->render($this->vm(['tabs' => $this->tabs(), 'band' => $this->band()]));
        $bandPos = strpos($html, 'dash-band');
        $tabsPos = strpos($html, 'dash-tabs');
        $this->assertNotFalse($bandPos);
        $this->assertNotFalse($tabsPos);
        $this->assertLessThan($tabsPos, $bandPos, 'global band must render before the run tabs');
    }

    public function testBandShowsBudgetWalletAccountAndRealizedTiles(): void
    {
        $html = (new DashboardRenderer())->render($this->vm(['band' => $this->band()]));
        $this->assertStringContainsString('Budget', $html);
        $this->assertStringContainsString('1,000.00', $html);
        $this->assertStringContainsString('Wallet USDT', $html);
        $this->assertStringContainsString('904.91', $html); // wallet_usdt money-formatted (bankers-safe)
        $this->assertStringContainsString('Account value', $html);
        $this->assertStringContainsString('1,210.55', $html);
        $this->assertStringContainsString('Realized P/L', $html);
        $this->assertStringContainsString('24.73', $html);
        $this->assertStringContainsString('Total P/L', $html);
        $this->assertStringContainsString('210.55', $html);
    }

    public function testTotalPlPositiveGetsPositiveTone(): void
    {
        $html = (new DashboardRenderer())->render($this->vm(['band' => $this->band(['total_pl' => '50.0'])]));
        $this->assertStringContainsString('dash-band-tile is-pos', $html);
    }

    public function testTotalPlNegativeGetsNegativeTone(): void
    {
        $html = (new DashboardRenderer())->render($this->vm(['band' => $this->band(['total_pl' => '-50.0'])]));
        $this->assertStringContainsString('dash-band-tile is-neg', $html);
    }

    public function testTotalPlNullRendersDashWithoutWarnings(): void
    {
        $html = (new DashboardRenderer())->render($this->vm([
            'band' => $this->band(['total_pl' => null, 'account_value' => null, 'wallet_usdt' => null]),
        ]));
        $this->assertStringContainsString('—', $html);
    }

    public function testHoldingsStripShowsAssetQtyAndValue(): void
    {
        $html = (new DashboardRenderer())->render($this->vm(['band' => $this->band()]));
        $this->assertStringContainsString('BTC', $html);
        $this->assertStringContainsString('0.0015', $html);
        $this->assertStringContainsString('93.20', $html); // BTC holding value, money-formatted
        // full-width grid cells: qty as the value, asset as the label
        $this->assertStringContainsString('class="dh-val"', $html);
        $this->assertStringContainsString('BTC wallet', $html);
    }

    public function testHoldingsStripShowsDashForUnpricedAsset(): void
    {
        $html = (new DashboardRenderer())->render($this->vm([
            'band' => $this->band(['assets' => [
                ['asset' => 'USDT', 'qty' => '500', 'value' => '500'],
                ['asset' => 'ETH', 'qty' => '1.0', 'value' => null],
            ]]),
        ]));
        $this->assertStringContainsString('ETH', $html);
        $this->assertStringContainsString('—', $html);
    }

    public function testCommittedCapitalRowShowsHoldingsOpenOrdersAndFreeBudget(): void
    {
        $html = (new DashboardRenderer())->render($this->vm(['band' => $this->band()]));
        $this->assertStringContainsString('Holdings', $html);
        $this->assertStringContainsString('93.20', $html);
        $this->assertStringContainsString('Open sells', $html);
        $this->assertStringContainsString('52.50', $html);
        $this->assertStringContainsString('Open buys', $html);
        $this->assertStringContainsString('95.00', $html);
        $this->assertStringContainsString('Free budget', $html);
        $this->assertStringContainsString('150.00', $html);
        // the ≈ entity must render literally, not double-escaped into &amp;#8776;
        $this->assertStringContainsString('&#8776;', $html);
        $this->assertStringNotContainsString('&amp;#8776;', $html);
    }

    public function testCommittedCapitalRowNullsRenderAsDash(): void
    {
        $html = (new DashboardRenderer())->render($this->vm([
            'band' => $this->band(['holdings_value' => null, 'open_sell_value' => null, 'open_buy_value' => null, 'free_budget' => null]),
        ]));
        $this->assertStringContainsString('Holdings', $html);
        $this->assertStringContainsString('—', $html);
    }

    public function testCommittedCapitalRowNegativeFreeBudgetGetsRedTone(): void
    {
        $html = (new DashboardRenderer())->render($this->vm([
            'band' => $this->band(['free_budget' => '-25.0']),
        ]));
        $this->assertStringContainsString('dash-holdings-free is-neg', $html);
    }

    public function testPerRunSectionNoLongerRendersModePillOrWalletDetail(): void
    {
        $html = (new DashboardRenderer())->render($this->vm(['band' => $this->band()]));
        // the pill markup renders exactly once now — inside the band, not
        // duplicated into the per-run section (the CSS selectors in <style>
        // also mention the class name, so match the actual element instead)
        // — and the old standalone "Shared wallet" table panel is gone
        // entirely (replaced by the band's holdings strip).
        $this->assertSame(1, substr_count($html, 'class="dash-mode-pill'));
        $this->assertStringNotContainsString('Shared wallet', $html);
    }
}
