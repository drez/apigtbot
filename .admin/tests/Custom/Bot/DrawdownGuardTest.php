<?php

namespace Tests\Custom\Bot;

use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\DrawdownGuard;
use App\GridRun;
use Tests\Builder\Support\DbTestCase;

class DrawdownGuardTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // neutralize whatever the local DB holds — this test owns the world
        // (status is a Propel ENUM stored as an int: raw SQL can't say 'Done')
        foreach (\App\GridRunQuery::create()->filterByStatus('Done', \Criteria::NOT_EQUAL)->find() as $r) {
            $r->setStatus('Done');
            $r->save();
        }
        \Propel::getConnection()->exec('DELETE FROM sim_wallet');
        $this->setConfig('gtbot_shared_budget_quote', '1000');
        $this->setConfig('gtbot_max_drawdown_pct', '');
    }

    private function setConfig(string $key, string $v): void
    {
        $c = ConfigQuery::create()->findOneByConfig($key) ?? (new Config())->setConfig($key);
        $c->setValue($v);
        $c->save();
    }

    private function seedWallet(array $assets): void
    {
        $ins = \Propel::getConnection()->prepare(
            'INSERT INTO sim_wallet (asset, qty, date_creation, date_modification) VALUES (?, ?, NOW(), NOW())'
        );
        foreach ($assets as $asset => $qty) {
            $ins->execute([$asset, $qty]);
        }
    }

    private function mkRun(
        string $symbol,
        ?string $lastPrice,
        bool $simulated = true,
        string $status = 'Live',
        ?string $balBase = null,
        ?string $balQuote = null,
        ?string $lastTickAt = null
    ): GridRun {
        $r = new GridRun();
        $r->setLabel(static::uniq('dd'));
        $r->setSymbol($symbol);
        $r->setStatus($status);
        $r->setSimulated($simulated);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('100');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid(static::uniq('dd'));
        if ($lastPrice !== null) {
            $r->setLastPrice($lastPrice);
        }
        if ($balBase !== null) {
            $r->setBalBase($balBase);
        }
        if ($balQuote !== null) {
            $r->setBalQuote($balQuote);
        }
        $r->setLastTickAt($lastTickAt ?? date('Y-m-d H:i:s'));
        $r->save();
        return $r;
    }

    // ── floor ───────────────────────────────────────────────────────────

    public function testFloorDefaultsTo25Pct(): void
    {
        $this->assertSame(0, bccomp('750', (string) DrawdownGuard::floor(), 12));
    }

    public function testFloorHonorsConfiguredPct(): void
    {
        $this->setConfig('gtbot_max_drawdown_pct', '40');
        $this->assertSame(0, bccomp('600', (string) DrawdownGuard::floor(), 12));
    }

    public function testExplicitZeroDisables(): void
    {
        $this->setConfig('gtbot_max_drawdown_pct', '0');
        $this->assertNull(DrawdownGuard::floor());
        $this->assertNull(DrawdownGuard::check());
    }

    // ── equity, paper mode ──────────────────────────────────────────────

    public function testSimEquityPricesBaseAssetsAtRunLastPrice(): void
    {
        $this->mkRun('BTCUSDT', '60000');
        $this->seedWallet(['USDT' => '500', 'BTC' => '0.01']);
        $eq = DrawdownGuard::equity();
        $this->assertSame(0, bccomp('1100', $eq['equity'], 12)); // 500 + 0.01×60000
        $this->assertSame([], $eq['unpriced']);
    }

    /** Same pool-staleness trap as BudgetGuard: the daemon marks equity every
     *  tick, so last_price/balances stamped by OTHER daemons must be read
     *  fresh, not from GridRun objects pooled at boot. */
    public function testEquityReadsRunRowsFreshBehindTheInstancePool(): void
    {
        $r = $this->mkRun('BTCUSDT', '60000');
        $this->seedWallet(['USDT' => '500', 'BTC' => '0.01']);
        $this->assertSame(0, bccomp('1100', DrawdownGuard::equity()['equity'], 12));

        \Propel::getConnection()
            ->prepare('UPDATE grid_run SET last_price = 50000 WHERE id_grid_run = ?')
            ->execute([(int) $r->getIdGridRun()]);
        $this->assertSame(0, bccomp('60000', (string) $r->getLastPrice(), 2), 'pooled object is stale on purpose');
        $this->assertSame(0, bccomp('1000', DrawdownGuard::equity()['equity'], 12), '500 + 0.01×50000 — the fresh price');
    }

    public function testRealEquityReadsBalancesFreshBehindTheInstancePool(): void
    {
        $r = $this->mkRun('BTCUSDT', '60000', false, 'Live', '0.01', '500');
        $this->assertSame(0, bccomp('1100', DrawdownGuard::equity()['equity'], 12));

        \Propel::getConnection()
            ->prepare('UPDATE grid_run SET bal_quote = 200, bal_base = 0.02 WHERE id_grid_run = ?')
            ->execute([(int) $r->getIdGridRun()]);
        $this->assertSame(0, bccomp('1400', DrawdownGuard::equity()['equity'], 12), '200 + 0.02×60000 — the fresh stamps');
    }

    public function testUnpricedAssetCountsZeroAndIsReported(): void
    {
        $this->mkRun('BTCUSDT', '60000');
        $this->seedWallet(['USDT' => '800', 'XRP' => '100']);
        $eq = DrawdownGuard::equity();
        $this->assertSame(0, bccomp('800', $eq['equity'], 12));
        $this->assertSame(['XRP'], $eq['unpriced']);
    }

    // ── check boundary ──────────────────────────────────────────────────

    public function testEquityAtFloorIsOk(): void
    {
        $this->mkRun('BTCUSDT', '60000');
        $this->seedWallet(['USDT' => '750']);
        $this->assertNull(DrawdownGuard::check());
    }

    public function testEquityUnderFloorBreaches(): void
    {
        $this->mkRun('BTCUSDT', '60000');
        $this->seedWallet(['USDT' => '749']);
        $over = DrawdownGuard::check();
        $this->assertNotNull($over);
        $this->assertSame(0, bccomp('749', $over['equity'], 12));
        $this->assertSame(0, bccomp('750', $over['floor'], 12));
        $this->assertSame(0, bccomp('1000', $over['budget'], 12));
    }

    // ── equity, real mode ───────────────────────────────────────────────

    public function testRealModeDedupesTheOneAccount(): void
    {
        // two REAL runs on the same symbol stamp the SAME account —
        // quote and base must each count once (freshest stamp wins)
        $this->mkRun('BTCUSDT', '1000', false, 'Live', '0.5', '400', '2026-08-01 10:00:00');
        $this->mkRun('BTCUSDT', '1000', false, 'Live', '0.5', '400', '2026-08-01 10:00:05');
        $eq = DrawdownGuard::equity();
        $this->assertSame(0, bccomp('900', $eq['equity'], 12)); // 400 + 0.5×1000
    }

    public function testAMixedFleetIsGuardedOnItsRealMoney(): void
    {
        // one paper run left behind in a real fleet must not swing the floor
        // onto the paper wallet: the only equity that can be LOST is real
        $this->seedWallet(['USDT' => '5000']);
        $this->mkRun('BTCUSDT', '1000', false, 'Live', '0.5', '400');
        $this->mkRun('BNBUSDT', '600', true, 'Live', '9', '9999'); // paper stamps are not the account
        $eq = DrawdownGuard::equity();
        $this->assertSame(0, bccomp('900', $eq['equity'], 12));
    }

    // ── tripAll ─────────────────────────────────────────────────────────

    public function testTripAllKillsOnlyActiveRuns(): void
    {
        $a = $this->mkRun('BTCUSDT', '60000');
        $b = $this->mkRun('ETHUSDT', '2000', true, 'Testnet');
        $done = $this->mkRun('BNBUSDT', '600', true, 'Done');
        $this->assertSame(2, DrawdownGuard::tripAll());
        $a->reload();
        $b->reload();
        $done->reload();
        $this->assertTrue((bool) $a->getKillSwitch());
        $this->assertTrue((bool) $b->getKillSwitch());
        $this->assertFalse((bool) $done->getKillSwitch());
        $this->assertSame(0, DrawdownGuard::tripAll()); // idempotent
    }

    // ── NAV ledger (wallet_nav) ─────────────────────────────────────────

    public function testNavSnapshotStoresWalletEquityAndReference(): void
    {
        $this->mkRun('BTCUSDT', '60000');
        $this->seedWallet(['USDT' => '500', 'BTC' => '0.01']);
        $row = \App\Domains\Bot\NavLedger::snapshot('60000');
        $this->assertNotNull($row);
        $this->assertSame('sim', (string) $row->getMode());
        $this->assertSame(0, bccomp('1100', (string) $row->getEquityQuote(), 8));
        $this->assertSame(0, bccomp('1000', (string) $row->getBudgetQuote(), 8));
        $this->assertSame('BTCUSDT', (string) $row->getRefSymbol());
        $this->assertNull($row->getUnpriced());
        // a second point makes a report: equity flat, BTC +10% → trailing HODL
        $this->seedWallet([]);
        $row2 = \App\Domains\Bot\NavLedger::snapshot('66000');
        $row2->setEquityQuote('1100');
        $row2->save();
        $rep = \App\Domains\Bot\NavLedger::report('sim', 1);
        $this->assertNotNull($rep);
        $this->assertSame(2, $rep['points']);
        $this->assertSame(10.0, $rep['hodl_return_pct']);
        $this->assertSame(-10.0, $rep['excess_vs_hodl_pct']);
    }

    public function testNavSnapshotSkipsWhenNoActiveRuns(): void
    {
        $before = \App\WalletNavQuery::create()->count();
        $this->assertNull(\App\Domains\Bot\NavLedger::snapshot('60000'), 'no active runs → mode none → nothing to record');
        $this->assertSame($before, \App\WalletNavQuery::create()->count());
    }
}
