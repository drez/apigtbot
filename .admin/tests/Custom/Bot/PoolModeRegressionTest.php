<?php

namespace Tests\Custom\Bot;

use App\BotCommandQuery;
use App\BotEventQuery;
use App\BotOrder;
use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\AccountAudit;
use App\Domains\Bot\Allocator;
use App\Domains\Bot\BudgetGuard;
use App\Domains\Bot\BudgetPool;
use App\Domains\Bot\DrawdownGuard;
use App\Domains\Bot\FleetSlots;
use App\Domains\Bot\FundsHold;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\GoLiveGate;
use App\Domains\Bot\NavLedger;
use App\Domains\Bot\SimWallet;
use App\Domains\Bot\TrendActivator;
use App\Domains\Dashboard\ModeSwitch;
use App\FleetSlot;
use App\FleetSlotQuery;
use App\GridRun;
use App\GridRunQuery;
use Tests\Builder\Support\DbTestCase;
use Tests\Custom\Bot\Support\ExchangeSim;

/**
 * The pool / mode / go-live defects found in the 2026-09-21 adversarial review
 * of 743f637, 4f3bb5c and 7945cfc — each one locked out here.
 *
 * Every test names the behaviour it keeps, and its docblock names the failure
 * it replaces, because these are all cases where the WRONG behaviour looked
 * perfectly reasonable in code review:
 *
 *   equity()      a real row that never booted must not move the floor
 *   snapshot()    NAV is filed by the wallet that was measured
 *   gridFloor()   a trend arm has no ladder here either
 *   planPool()    a floor may stop a trim, never demand a raise
 *   release()     an operator act plans against the full cap
 *   benchmark()   a flow lands where its money lands
 *   assetsFor()   a known quote list, longest match first
 *   audit keys    identity, never live quantities
 *   go-live       parked inventory is unfinished business
 */
class PoolModeRegressionTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // this test owns the world: nothing else may charge the pool or the floor
        foreach (GridRunQuery::create()->filterByStatus('Done', \Criteria::NOT_EQUAL)->find() as $r) {
            $r->setStatus('Done');
            $r->save();
        }
        foreach (FleetSlotQuery::create()->find() as $fs) {
            $fs->delete();
        }
        \Propel::getConnection()->exec('DELETE FROM sim_wallet');
        $this->cfg('gtbot_shared_budget_quote', '1300');
        $this->cfg(BudgetPool::CONFIG_USE_ALL, '0');
        $this->cfg(BudgetPool::CONFIG_RESERVE, '5');
        $this->cfg('gtbot_max_drawdown_pct', '25');
        $this->cfg(TrendActivator::CONFIG_TARGET_SLICE, '350');
    }

    private function cfg(string $key, string $value): void
    {
        \App\ConfigPeer::clearInstancePool();
        $c = ConfigQuery::create()->findOneByConfig($key) ?? (new Config())->setConfig($key);
        $c->setValue($value);
        $c->save();
        \App\ConfigPeer::clearInstancePool();
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
        string $budget,
        string $algo = 'Grid',
        int $levels = 6,
        string $status = 'Live',
        bool $simulated = true,
        string $symbol = 'BTCUSDT',
        int $deploy = 100
    ): GridRun {
        $r = new GridRun();
        $r->setLabel(static::uniq('pmr'));
        $r->setSymbol($symbol);
        $r->setStatus($status);
        $r->setAlgo($algo);
        $r->setAllocMode('Auto');
        $r->setSimulated($simulated);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels($levels);
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote($budget);
        $r->setDeployPct($deploy);
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid(static::uniq('pmr'));
        $r->setLastTickAt(date('Y-m-d H:i:s'));
        $r->setLastPrice('150');
        $r->save();
        return $r;
    }

    /** A filled buy = inventory this run still holds. */
    private function inventory(GridRun $run, string $qty): void
    {
        $o = new BotOrder();
        $o->setIdGridRun((int) $run->getIdGridRun());
        $o->setClientOrderId(static::uniq('cid'));
        $o->setSide('Buy');
        $o->setLevelIdx(0);
        $o->setPrice('100');
        $o->setQty($qty);
        $o->setFilledQty($qty);
        $o->setState('Filled');
        $o->setSimulated((bool) $run->getSimulated());
        $o->save();
    }

    private function slice(GridRun $r): string
    {
        $r->reload();
        return bcadd((string) $r->getBudgetQuote(), '0', 0);
    }

    // ── mode: what moves the floor onto the real account ────────────────

    /**
     * WAS: equity() switched to the real account on "any non-Done row is
     * real". A real run created as a Draft — what preparing a live canary
     * looks like — has no bal_* stamp, so the real account read as EMPTY,
     * equity was 0, 0 is under every floor, and three ticks later
     * Daemon::checkGlobalDrawdown killed the whole healthy paper fleet.
     */
    public function testARealRunThatNeverBootedDoesNotMoveTheFloorOffThePaperWallet(): void
    {
        $this->seedWallet(['USDT' => '1300']);
        $this->mkRun('650');

        $draft = $this->mkRun('50', 'Grid', 6, 'Draft', false);
        $this->assertNull($draft->getBalQuote(), 'it never booted, so it never stamped an account');

        $eq = DrawdownGuard::equity();
        $this->assertSame('sim', $eq['source'], 'nothing real is exposed yet');
        $this->assertSame(0, bccomp('1300', $eq['equity'], 8), 'the paper wallet is still what is measured');
        $this->assertNull(DrawdownGuard::check(), 'and the fleet is nowhere near the 975 floor');
    }

    /** A real run that IS trading, with its account stamped, is the floor. */
    public function testAnActiveStampedRealRunPutsTheFloorOnTheRealAccount(): void
    {
        $this->seedWallet(['USDT' => '5000']); // paper wallet must not stand in for it
        $real = $this->mkRun('650', 'Grid', 6, 'Live', false);
        $real->setBalQuote('400');
        $real->setBalBase('2');
        $real->save();

        $eq = DrawdownGuard::equity();
        $this->assertSame('real', $eq['source']);
        $this->assertSame(0, bccomp('700', $eq['equity'], 8), '400 USDT + 2 BTC × 150');
    }

    /**
     * The case that matters most: not trading, but the coins are still on the
     * exchange. A Halted real run holding inventory is exposure, so it keeps
     * the floor on the real account.
     */
    public function testAParkedRealRunStillHoldingCoinsKeepsTheFloorOnTheRealAccount(): void
    {
        $this->seedWallet(['USDT' => '5000']);
        $parked = $this->mkRun('650', 'Grid', 6, 'Halted', false);
        $parked->setBalQuote('100');
        $parked->setBalBase('1');
        $parked->save();
        $this->inventory($parked, '1');

        $eq = DrawdownGuard::equity();
        $this->assertSame('real', $eq['source'], 'parked but still holding = still exposed');
        $this->assertSame(0, bccomp('250', $eq['equity'], 8), '100 USDT + 1 BTC × 150');
    }

    /** Same row, flat: nothing is at stake, so the paper wallet answers. */
    public function testAParkedRealRunThatSoldOutNoLongerMovesTheFloor(): void
    {
        $this->seedWallet(['USDT' => '1300']);
        $this->mkRun('650');
        $parked = $this->mkRun('50', 'Grid', 6, 'Halted', false);
        $parked->setBalQuote('7');
        $parked->save();

        $eq = DrawdownGuard::equity();
        $this->assertSame('sim', $eq['source']);
        $this->assertSame(0, bccomp('1300', $eq['equity'], 8));
    }

    /**
     * WAS: snapshot() filed by ModeSwitch, which reads every non-Done row —
     * so one real Draft made the fleet 'mixed' and a paper soak was filed as
     * 'real' (at equity 0). GoLiveGate reads 'sim': the gate went blind the
     * moment the operator prepared to go live, and the real series was seeded
     * with zeros. It files what was MEASURED now.
     */
    public function testNavIsFiledByTheWalletThatWasActuallyMeasured(): void
    {
        $this->seedWallet(['USDT' => '1300']);
        $this->mkRun('650');
        $this->mkRun('50', 'Grid', 6, 'Draft', false);
        $this->assertSame('mixed', ModeSwitch::systemMode(), 'the fleet is mixed by the letter of it');

        $row = NavLedger::snapshot('60000');
        $this->assertNotNull($row);
        $this->assertSame('sim', (string) $row->getMode(), 'but what was measured is the paper wallet');
        $this->assertSame(0, bccomp('1300', (string) $row->getEquityQuote(), 8));
    }

    // ── pool: a trend arm has no ladder, a floor is not a raise ──────────

    /**
     * WAS: gridFloor() charged a Trend run FLOOR_PER_LEVEL × n_levels. Only
     * an ACTIVE arm is pinned, so an idle one (prod runs #1 and #10, deploy
     * 100) carried a 1000 USDT floor into planPool — which assigns floors as
     * VALUES. The 15-minute rebalance planned to RAISE a parked arm to 1000,
     * BudgetGuard refused the write, and the whole pass rolled back with an
     * Alert to Telegram, every pass, forever.
     */
    public function testAnIdleTrendArmsPoolFloorIsMinSliceNotItsLadder(): void
    {
        $arm = $this->mkRun('50', 'Trend', 20);
        $g1 = $this->mkRun('300', 'Grid', 6);
        $g2 = $this->mkRun('300', 'Grid', 6);

        $this->assertSame('idle', TrendActivator::state((int) $arm->getIdGridRun()));
        $this->assertSame('50', TrendActivator::gridFloor($arm), 'the arm has no ladder to fund');
        $this->assertSame('50', Allocator::hardFloor($arm), 'so the pool charges it MIN_SLICE');
        $this->assertSame('300', Allocator::hardFloor($g1), 'a GRID keeps its per-level floor');

        $res = Allocator::rebalance('regression');
        $this->assertTrue($res['ok'], $res['message']);
        $this->assertSame('1235', bcadd(Allocator::activeSum(), '0', 0), 'the pass lands, up to the allocatable pool');
        $this->assertNull(BudgetGuard::check());
        $this->assertTrue(bccomp($this->slice($arm), '1000', 0) < 0, 'and no parked arm is handed a ladder-sized slice');
        $this->assertSame('0', $res['shortfall']);
        $this->assertSame([], $res['proposals'], 'no exit proposals for capital that was never in danger');
        unset($g2);
    }

    /**
     * WAS: when the floors did not fit the pool, planPool() assigned every
     * auto run its floor — an INCREASE for anything below it, on a pool with
     * no room for one, which BudgetGuard refuses and which rolled the whole
     * pass back. A floor's job is to stop a trim; it may never demand a raise.
     */
    public function testAFloorStopsATrimAndNeverDemandsARaise(): void
    {
        $plan = Allocator::planPool('1000', [
            1 => ['budget' => '100', 'floor' => '300', 'auto' => true],  // below its floor
            2 => ['budget' => '900', 'floor' => '900', 'auto' => true],  // on its floor
        ]);
        $this->assertSame('100', $plan[1], 'left where it is, not raised into a refusal');
        $this->assertSame('900', $plan[2], 'and nothing is trimmed below its floor');
        $this->assertSame('1000', bcadd($plan[1], $plan[2], 0), 'the plan fits the pool it was given');
    }

    /**
     * With a 5% reserve and both grids on their floor, an arm funded against
     * the ALLOCATABLE pool got 285 of its 350 target — the whole reserve
     * carried by the fleet's only profit engine. Funding an arm plans against
     * the full cap instead (TrendActivator::capacity).
     */
    public function testAnActivationTakesWhatThePoolHasRatherThanRefusing(): void
    {
        $arm = $this->mkRun('50', 'Trend', 20);
        $this->mkRun('350', 'Trend', 20);
        $g1 = $this->mkRun('300', 'Grid', 6);
        $g2 = $this->mkRun('300', 'Grid', 6);

        $grids = [
            (int) $g1->getIdGridRun() => ['budget' => '300', 'floor' => TrendActivator::gridFloor($g1)],
            (int) $g2->getIdGridRun() => ['budget' => '300', 'floor' => TrendActivator::gridFloor($g2)],
        ];
        // the activator's own capacity: the FULL pool (owner decision
        // 2026-09-21 — funding an arm is the raise the reserve does not bind)
        $plan = TrendActivator::planActivate(
            Allocator::capacity((int) $arm->getIdGridRun(), $grids, BudgetPool::cap()),
            '50',
            $grids,
            TrendActivator::targetSlice()
        );

        $this->assertSame('1235', BudgetPool::allocatable());
        $this->assertSame('350', $plan['trend'], 'the arm reaches its target; the reserve binds rebalance/drift, not this');
        $this->assertSame('300', $plan['grids'][(int) $g1->getIdGridRun()], 'no grid is trimmed below its floor for it');
    }

    // ── the operator's own buttons plan against the full cap ────────────

    /**
     * WAS: release() planned against the allocatable pool, so Hold → Release
     * was LOSSY — a grid parked at 300 came back with 235 on a fleet that was
     * never overcommitted, the difference absorbed into the reserve. The
     * reserve is slack the MACHINE refuses to spend, not a budget the
     * operator has to argue with (same reasoning as RunFactory::validate).
     */
    public function testAHoldReleaseRoundTripReturnsWhatWasParked(): void
    {
        $this->mkRun('350', 'Trend', 20);
        $this->mkRun('350', 'Trend', 20);
        $held = $this->mkRun('300', 'Grid', 6);
        $other = $this->mkRun('300', 'Grid', 6);
        $this->assertNull(BudgetGuard::check(), 'the fleet starts exactly on the cap, legally');

        $this->assertTrue(FundsHold::hold($held)['ok']);
        $this->assertSame('535', $this->slice($other), 'the hold spreads the ALLOCATABLE headroom — a machine act');

        $res = FundsHold::release($held);
        $this->assertTrue($res['ok'], $res['message']);
        $this->assertSame('300', $this->slice($held), 'and the operator gets back every USDT that was parked');
        $this->assertSame('300', $this->slice($other));
        $this->assertStringNotContainsString('held back as pool reserve', $res['message']);
        $this->assertNull(BudgetGuard::check());
    }

    /**
     * WAS: a release the cap could fund was refused outright — and the
     * refusal FABRICATED a BudgetGuard payload when check() came back null
     * ("would sum to 1300 but the budget is 1300 (excess 100)"), sending the
     * operator to lower a run that was not the problem. Under the cap the
     * arithmetic is consistent again: what the cap can fund is released, and
     * a refusal names a real overcommit.
     */
    public function testAReleaseTheCapCanFundIsNotRefused(): void
    {
        $this->mkRun('350', 'Trend', 20);
        $this->mkRun('350', 'Trend', 20);
        $this->mkRun('500', 'Grid', 10);              // on its 500 floor
        $held = $this->mkRun('100', 'Grid', 2, 'Halted');
        $this->assertNull(BudgetGuard::check((int) $held->getIdGridRun(), '100', 'Live'), 'the cap fits it');

        $res = FundsHold::release($held);
        $this->assertTrue($res['ok'], $res['message']);
        $this->assertSame('100', $this->slice($held));
    }

    /** And when the cap genuinely cannot fund it, the refusal is the true one. */
    public function testAReleaseTheCapCannotFundIsRefusedWithTheRealReason(): void
    {
        $this->mkRun('350', 'Trend', 20);
        $this->mkRun('350', 'Trend', 20);
        $this->mkRun('580', 'Grid', 12);              // floor 580, nothing to give
        $held = $this->mkRun('100', 'Grid', 2, 'Halted');
        $this->assertNotNull(BudgetGuard::check((int) $held->getIdGridRun(), '100', 'Live'), 'this one really is an overcommit');

        $res = FundsHold::release($held);
        $this->assertFalse($res['ok']);
        $this->assertStringContainsString('Budget overcommit refused', $res['message']);
        $this->assertStringContainsString('1380', $res['message'], 'the real sum, not an invented one');
    }

    // ── NAV: a flow lands where its money lands ─────────────────────────

    /**
     * WAS: the flow was taken out at the step where the BUDGET column moved.
     * The paper wallet is only re-seeded when the daemons reboot, so a raise
     * can be stamped points before its money arrives: a FLAT fleet across
     * 1000 → 1300 read −9.0% with a 30% max drawdown, straight through the
     * 25% go-live gate.
     */
    public function testALaggedBudgetRaiseIsAttributedToTheStepWhereTheMoneyLands(): void
    {
        $at = static fn (int $d): string => date('Y-m-d H:i', time() - $d * 86400);
        $lagged = NavLedger::benchmark([
            ['equity' => '1000', 'ref_price' => '100', 'at' => $at(3), 'budget' => '1000'],
            ['equity' => '1000', 'ref_price' => '100', 'at' => $at(2), 'budget' => '1300'], // config written
            ['equity' => '1300', 'ref_price' => '100', 'at' => $at(1), 'budget' => '1300'], // daemons re-seeded
        ]);
        $this->assertNotNull($lagged);
        $this->assertSame(0.0, $lagged['nav_return_pct'], 'a flat fleet reads flat');
        $this->assertSame(0.0, $lagged['max_drawdown_pct'], 'and has no drawdown to explain');
        $this->assertSame('300.00', $lagged['net_flows'], 'the raise is still recorded as a flow');
    }

    /** The same raise inside ONE step was always right and must stay identical. */
    public function testASameStepBudgetRaiseIsUnchanged(): void
    {
        $at = static fn (int $d): string => date('Y-m-d H:i', time() - $d * 86400);
        // prod's real 2026-09-03 shape: budget and equity moved together
        $same = NavLedger::benchmark([
            ['equity' => '998.86', 'ref_price' => '100', 'at' => $at(3), 'budget' => '1000'],
            ['equity' => '1299.23', 'ref_price' => '100', 'at' => $at(2), 'budget' => '1300'],
        ]);
        // +0.04% = the 0.37 USDT the fleet actually earned across that step,
        // with the 300 deposit taken out of the numerator — byte-identical to
        // what the old code produced for this shape
        $this->assertSame(0.04, $same['nav_return_pct']);
        $this->assertSame(0.0, $same['max_drawdown_pct']);

        // and real performance is still measured: +5% on the point after
        $earning = NavLedger::benchmark([
            ['equity' => '1000', 'ref_price' => '100', 'at' => $at(3), 'budget' => '1000'],
            ['equity' => '1300', 'ref_price' => '100', 'at' => $at(2), 'budget' => '1300'],
            ['equity' => '1365', 'ref_price' => '100', 'at' => $at(1), 'budget' => '1300'],
        ]);
        $this->assertSame(5.0, $earning['nav_return_pct']);
    }

    /**
     * CHANGED 2026-09-21 (round 2): a deposit whose money never showed up is
     * PENDING, not a loss.
     *
     * This used to force-apply the flow after FLOW_LAG_POINTS and read the
     * window as −23%. But a budget row raised while the daemons are down (the
     * drawdown-stop recovery shape: every run killed, the routine restarts
     * them hours later) moves no equity at all, and booking 300 USDT that
     * never arrived fabricates a loss out of nothing. The equity did not move
     * in the flow's direction, so there is nothing to attribute: the window
     * reads flat, which it was, and the un-landed flow is REPORTED rather
     * than silently dropped.
     */
    public function testAFlowWhoseMoneyNeverArrivedIsPendingNotALoss(): void
    {
        $series = [['equity' => '1000', 'ref_price' => '100', 'at' => '2026-09-01 00:00', 'budget' => '1000']];
        $series[] = ['equity' => '1000', 'ref_price' => '100', 'at' => '2026-09-01 01:00', 'budget' => '1300'];
        for ($i = 2; $i <= 14; $i++) {
            $series[] = ['equity' => '1000', 'ref_price' => '100', 'at' => sprintf('2026-09-01 %02d:00', $i), 'budget' => '1300'];
        }
        $out = NavLedger::benchmark($series);
        $this->assertSame(0.0, $out['nav_return_pct'], 'a wallet that never received the money is flat, not down 23%');
        $this->assertSame(0.0, $out['max_drawdown_pct']);
        $this->assertSame('0.00', $out['net_flows'], 'nothing arrived, so nothing is a flow yet');
        $this->assertSame('300.00', $out['flows_pending'], 'and the stamped raise is not silently dropped');
    }

    /** The mirror: the money DOES arrive late, and it is a flow, never return. */
    public function testALateArrivalIsBookedAsAFlowAcrossTheWholeSpan(): void
    {
        $series = [['equity' => '1000', 'ref_price' => '100', 'at' => '2026-09-01 00:00', 'budget' => '1000']];
        $series[] = ['equity' => '1000', 'ref_price' => '100', 'at' => '2026-09-01 01:00', 'budget' => '1300'];
        for ($i = 2; $i <= 14; $i++) {
            $series[] = ['equity' => '1000', 'ref_price' => '100', 'at' => sprintf('2026-09-01 %02d:00', $i), 'budget' => '1300'];
        }
        $series[] = ['equity' => '1300', 'ref_price' => '100', 'at' => '2026-09-02 00:00', 'budget' => '1300'];
        $out = NavLedger::benchmark($series);
        $this->assertSame(0.0, $out['nav_return_pct'], 'however late it lands, a deposit is not a return');
        $this->assertSame('300.00', $out['net_flows']);
        $this->assertSame('0.00', $out['flows_pending']);
    }

    // ── symbols, and the audit's alerting identity ──────────────────────

    /**
     * WAS: "USDT, else the last three characters" — BTCUSDC split into base
     * 'BTCU' / quote 'SDC'. AccountAudit then compared an account balance
     * that cannot exist against inventory it did find (a permanent phantom
     * inventory_missing), and DrawdownGuard::equity priced the same phantom
     * base, counting REAL coins as 0 and walking equity toward the floor.
     */
    public function testASymbolIsSplitOnItsRealQuoteAsset(): void
    {
        $this->assertSame(['BTC', 'USDT'], SimWallet::assetsFor('BTCUSDT'));
        $this->assertSame(['BTC', 'USDC'], SimWallet::assetsFor('BTCUSDC'));
        $this->assertSame(['BTC', 'FDUSD'], SimWallet::assetsFor('BTCFDUSD'));
        $this->assertSame(['ETH', 'BTC'], SimWallet::assetsFor('ETHBTC'));
        $this->assertSame(['SOL', 'USDT'], SimWallet::assetsFor('SOLUSDT'));
    }

    /** The audit on a non-USDT pair is clean when the account backs it. */
    public function testANonUsdtPairIsAuditedAgainstTheAssetItActuallyHolds(): void
    {
        $run = $this->mkRun('100', 'Grid', 4, 'Live', false, 'BTCUSDC');
        $this->inventory($run, '0.5');

        $sim = new ExchangeSim();
        $sim->setPrice('100');
        $sim->balances = ['BTC' => ['free' => '0.5', 'locked' => '0']];
        $res = AccountAudit::run(new BinanceGateway('https://sim.local', 'k', 's', $sim->transport()));

        $this->assertArrayHasKey('BTC', $res['assets']);
        $this->assertArrayNotHasKey('BTCU', $res['assets'], 'an asset that cannot exist');
        $this->assertTrue($res['ok'], json_encode($res['findings']));
    }

    /**
     * WAS: bin/gtbot-audit --alert hashed the whole detail line, and an
     * inventory_missing detail embeds live quantities at 8 decimals. On a
     * trading account they differ every audit, so the finding never survived
     * two of them and the most important alert the audit can raise was the
     * one that could never be sent. The key is identity now; the numbers stay
     * in the message.
     */
    public function testAFindingsAlertKeyIsItsIdentityNotItsNumbers(): void
    {
        $run = $this->mkRun('100', 'Grid', 4, 'Live', false);
        $this->inventory($run, '0.5');

        $sim = new ExchangeSim();
        $sim->setPrice('100');
        $sim->balances = ['BTC' => ['free' => '0.3', 'locked' => '0']];
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $sim->transport());
        $first = AccountAudit::run($gw)['findings'];

        // the account moves between two audits, as a trading account does
        $sim->balances = ['BTC' => ['free' => '0.30017', 'locked' => '0']];
        $second = AccountAudit::run($gw)['findings'];

        $this->assertSame('inventory_missing', $first[0]['kind']);
        $this->assertSame('inventory_missing|BTC', $first[0]['key']);
        $this->assertSame($first[0]['key'], $second[0]['key'], 'the same finding keeps the same key');
        $this->assertNotSame($first[0]['detail'], $second[0]['detail'], 'while the detail still reports the live numbers');
    }

    // ── go-live: parked inventory is unfinished business ────────────────

    /**
     * WAS: no_killed_runs only looked at ACTIVE statuses, so a run killed and
     * parked with its coins — exactly what the drawdown stop leaves behind —
     * passed the gate while its inventory sat on the exchange.
     */
    public function testAParkedRunStillHoldingInventoryFailsTheGate(): void
    {
        $parked = $this->mkRun('300', 'Grid', 6, 'Halted');
        $this->inventory($parked, '0.4');

        $gates = [];
        foreach (GoLiveGate::evaluate()['gates'] as $g) {
            $gates[$g['gate']] = $g;
        }
        $this->assertFalse($gates['no_killed_runs']['pass']);
        $this->assertStringContainsString('still holding inventory', $gates['no_killed_runs']['detail']);
        $this->assertStringContainsString('#' . (int) $parked->getIdGridRun(), $gates['no_killed_runs']['detail']);
    }

    /** A clean fleet still passes that gate, and says so. */
    public function testACleanFleetPassesTheKilledRunsGate(): void
    {
        $this->mkRun('300', 'Grid', 6);
        $gates = [];
        foreach (GoLiveGate::evaluate()['gates'] as $g) {
            $gates[$g['gate']] = $g;
        }
        $this->assertTrue($gates['no_killed_runs']['pass']);
        $this->assertStringContainsString('no parked run is still holding inventory', $gates['no_killed_runs']['detail']);
        $this->assertStringContainsString('still open:', $gates['trend_leg_seen']['detail'], 'open legs are named, not counted');
    }

    // ═══════════════════════════════════════════════════════════════════
    // ROUND 2 (2026-09-21) — what an adversarial review of d6a958a found
    // ═══════════════════════════════════════════════════════════════════

    private function slotFor(GridRun $run, string $state, ?array $activation = null, int $confirmUp = 0): FleetSlot
    {
        FleetSlots::reconcileFromRuns();
        $slot = FleetSlots::byRun((int) $run->getIdGridRun());
        $this->assertNotNull($slot, 'run ' . $run->getIdGridRun() . ' fills no slot');
        $slot->setState($state);
        $slot->setActivation($activation === null ? null : json_encode($activation));
        if ($confirmUp > 0) {
            $slot->setConfirmUp($confirmUp);
            $slot->setLastVerdict('TREND_UP');
        }
        $slot->save();
        return $slot;
    }

    /** @return callable(string): array the market shape the activator classifies */
    private function summaries(string $shape): callable
    {
        $map = [
            'TREND_UP' => ['4h' => ['trend' => 'strong_up', 'adx14' => 33.0, 'er20' => 0.6, 'chop14' => 30.0, 'stale' => false, 'price' => 150.0],
                           '1d' => ['trend' => 'up', 'price' => 150.0, 'ema20' => 90.0, 'ema50' => 95.0, 'stale' => false]],
            'HOSTILE' => ['4h' => ['trend' => 'down', 'adx14' => 35.0, 'er20' => 0.6, 'chop14' => 30.0, 'stale' => false, 'price' => 150.0],
                          '1d' => ['trend' => 'down', 'price' => 150.0, 'ema20' => 160.0, 'ema50' => 170.0, 'stale' => false]],
        ];
        return static fn (string $symbol): array => $map[$shape];
    }

    /** bin/gtbot-allocate's rebalance leg, gated exactly as the cron gates it. */
    private function allocateCron(): bool
    {
        if (!Allocator::rebalanceNeeded('25')['run']) {
            return false;
        }
        $res = Allocator::rebalance('cron', true);
        $this->assertTrue($res['ok'], (string) $res['message']);
        return true;
    }

    /** setUp() empties the wallet, and an unpriceable wallet stands the
     *  activator down (DrawdownGuard) — so give the fleet one to measure. */
    private function walletUsdt(string $qty): void
    {
        \Propel::getConnection()->exec('DELETE FROM sim_wallet');
        $this->seedWallet(['USDT' => $qty]);
    }

    /** The prod fleet of 2026-09-21, row for row. @return GridRun[] [arm1, arm10, g4, g7] */
    private function liveShape(string $armState = 'active'): array
    {
        $this->walletUsdt('1300');
        $arm1 = $this->mkRun('349', 'Trend', 5, 'Live', true, 'BTCUSDT', 100);
        $arm10 = $this->mkRun('348', 'Trend', 20, 'Live', true, 'BNBUSDT', 100);
        $g4 = $this->mkRun('390', 'Grid', 6, 'Live', true, 'BNBUSDT', 25);
        $g7 = $this->mkRun('211', 'Grid', 6, 'Live', true, 'BTCUSDT', 25);
        $payload = ['trend_before' => '50', 'deploy_before' => 0, 'grids_before' => []];
        $this->slotFor($arm1, $armState, $payload, 4);
        $this->slotFor($arm10, $armState, $payload, 4);
        return [$arm1, $arm10, $g4, $g7];
    }

    private function pass(GridRun $arm, string $shape = 'TREND_UP'): void
    {
        TrendActivator::pass(FleetSlots::byRun((int) $arm->getIdGridRun()), null, false, $this->summaries($shape));
    }

    private function shape(array $runs): array
    {
        $out = array_map(fn (GridRun $r): string => $this->slice($r), $runs);
        $out[] = bcadd(Allocator::activeSum(), '0', 0);
        return $out;
    }

    // ── the reserve: spent last, and with a way home ────────────────────

    /**
     * WAS: the activator funded a raise from POOL HEADROOM first, so prod's
     * arms took the 3 USDT they needed to reach 350 out of the 65 USDT
     * reserve and left the fleet packed to exactly the 1300 cap — zero
     * headroom, which is the state gtbot_pool_reserve_pct exists to prevent.
     * And it was a one-way ratchet: bin/gtbot-allocate gated its rebalance on
     * headroom() (measured against ALLOCATABLE, so 0 for any fleet between
     * allocatable and cap), so the reserve never came back.
     *
     * Now the grids' idle capital above their floors goes first, and a fleet
     * over allocatable WITH idle capital is itself a reason to rebalance. One
     * pass to converge, then silence.
     */
    public function testTheLiveShapeSettlesInsideTheAllocatablePoolAndThenGoesQuiet(): void
    {
        [$arm1, $arm10, $g4, $g7] = $this->liveShape();
        $runs = [$arm1, $arm10, $g4, $g7];
        $this->assertSame('1298', bcadd(Allocator::activeSum(), '0', 0), 'prod, as it stands');
        $this->assertSame('1235', BudgetPool::allocatable());

        $seen = [];
        for ($p = 1; $p <= 4; $p++) {
            $this->pass($arm1);
            $this->pass($arm10);
            $this->allocateCron();
            $seen[$p] = $this->shape($runs);
        }
        $this->assertSame('350', $this->slice($arm1), 'the arm still reaches its target');
        $this->assertSame('350', $this->slice($arm10));
        $this->assertSame('1235', $seen[1][4], 'and the fleet is back inside the allocatable pool in ONE pass');
        $this->assertSame($seen[1], $seen[2], 'pass 2 is a no-op');
        $this->assertSame($seen[2], $seen[3], 'pass 3 is a no-op');
        $this->assertSame($seen[3], $seen[4], 'pass 4 is a no-op');
        $this->assertNull(BudgetGuard::check());
        $this->assertSame(
            0,
            BotEventQuery::create()->filterByLevel('Alert')->filterByKind(Allocator::KIND_ERROR)->count(),
            'nothing rolled back'
        );
    }

    /**
     * The other half of the same gate: a fleet over allocatable whose every
     * USDT is held by a floor or by inventory has nothing to give back
     * without divesting, so the leg must NOT run — no plan, no write, no
     * Reload, no event about capital that is not in danger.
     */
    public function testAFleetWithNoIdleCapitalIsLeftAloneEvenAboveTheAllocatablePool(): void
    {
        $this->walletUsdt('1300');
        $arm1 = $this->mkRun('350', 'Trend', 5, 'Live', true, 'BTCUSDT', 100);
        $arm10 = $this->mkRun('350', 'Trend', 20, 'Live', true, 'BNBUSDT', 100);
        $g4 = $this->mkRun('300', 'Grid', 6, 'Live', true, 'BNBUSDT', 100); // 6 × 50 = on its floor
        $g7 = $this->mkRun('300', 'Grid', 6, 'Live', true, 'BTCUSDT', 100);
        $this->slotFor($arm1, 'active', ['trend_before' => '50'], 4);
        $this->slotFor($arm10, 'active', ['trend_before' => '50'], 4);

        $this->assertSame('1300', bcadd(Allocator::activeSum(), '0', 0));
        $this->assertSame('0', Allocator::idleAboveFloors(), 'every USDT is on a floor');
        $gate = Allocator::rebalanceNeeded('25');
        $this->assertFalse($gate['run'], $gate['why']);
        $this->assertStringContainsString('without divesting', $gate['why']);

        $before = BotCommandQuery::create()->filterByCommand('Reload')->count();
        for ($p = 1; $p <= 4; $p++) {
            $this->pass($arm1);
            $this->pass($arm10);
            $this->assertFalse($this->allocateCron(), "pass $p must stay quiet");
        }
        $this->assertSame('300', $this->slice($g4));
        $this->assertSame('300', $this->slice($g7));
        $this->assertSame($before, BotCommandQuery::create()->filterByCommand('Reload')->count(), 'no Reload churn');
    }

    /**
     * WAS: hold() gave back only the ALLOCATABLE headroom while release()
     * took from the FULL cap, so a round trip ratcheted the reserve into
     * committed slices — prod's 1298 came back as 1300 with the whole 65
     * USDT reserve spent, permanently.
     */
    public function testAHoldReleaseRoundTripDoesNotSpendThePoolReserve(): void
    {
        [, , $g4, $g7] = $this->liveShape();

        $this->assertTrue(FundsHold::hold($g4)['ok']);
        $res = FundsHold::release($g4);
        $this->assertTrue($res['ok'], $res['message']);
        $this->assertSame('390', $this->slice($g4), 'the operator still gets back every USDT that was parked');
        $this->assertSame('211', $this->slice($g7), 'and the grid the hold raised goes back too');
        $this->assertSame('1298', bcadd(Allocator::activeSum(), '0', 0), 'the reserve is where it was');
        $this->assertNull(BudgetGuard::check());
    }

    /**
     * WAS: with every auto run exactly on its floor — precisely what a trend
     * activation LEAVES the grids in — planPool's weightBase was 0 and the
     * spare was split EQUALLY, handing an IDLE arm (deploy 0, entries gated
     * until the activator funds it) 245 USDT it cannot reach, and taking that
     * much off the capacity the other symbol's arm is funded from.
     */
    public function testAnIdleArmOnItsFloorIsNotHandedIdlePoolCapital(): void
    {
        $arm = $this->mkRun('50', 'Trend', 20, 'Live', true, 'BTCUSDT', 0);
        $g1 = $this->mkRun('300', 'Grid', 6);
        $g2 = $this->mkRun('300', 'Grid', 6, 'Live', true, 'BNBUSDT');
        $this->slotFor($arm, 'idle');
        $this->assertFalse(Allocator::canDeploy($arm), 'an arm the activator has not funded cannot deploy');

        $res = Allocator::rebalance('round2');
        $this->assertTrue($res['ok'], $res['message']);
        $this->assertSame('50', $this->slice($arm), 'the arm keeps its floor and nothing more');
        $this->assertSame(
            '1185',
            bcadd(bcadd($this->slice($g1), $this->slice($g2), 0), '0', 0),
            'the grids that can trade it get all of it'
        );
        $this->assertSame('1235', bcadd(Allocator::activeSum(), '0', 0));
    }

    /** An ACTIVE arm is pinned, so it is not a spreader either — and says why. */
    public function testAnActiveArmIsPinnedOutOfTheRebalanceEntirely(): void
    {
        $arm = $this->mkRun('350', 'Trend', 20, 'Live', true, 'BTCUSDT', 100);
        $this->mkRun('300', 'Grid', 6);
        $this->slotFor($arm, 'active', ['trend_before' => '50']);

        $res = Allocator::rebalance('round2');
        $this->assertSame('350', $this->slice($arm));
        $this->assertArrayHasKey((int) $arm->getIdGridRun(), $res['skipped']);
        $this->assertStringContainsString('activator owns it', $res['skipped'][(int) $arm->getIdGridRun()]);
    }

    /**
     * WAS: planPool caps a floor at what a run already holds — right, because
     * the alternative is a raise the guard refuses and a rolled-back pass —
     * but a run left UNDER its own viability floor was invisible: no event,
     * nothing telling the operator only they can fix it.
     */
    public function testARunLeftUnderItsFloorIsJournaledAsSkipped(): void
    {
        $pinned = $this->mkRun('1000', 'Grid', 20);
        $pinned->setAllocMode('Fixed');
        $pinned->save();
        $starved = $this->mkRun('40', 'Grid', 6, 'Live', true, 'BNBUSDT');
        $this->assertSame('300', Allocator::hardFloor($starved));

        $res = Allocator::rebalance('round2');
        $this->assertTrue($res['ok'], $res['message']);
        $this->assertSame('40', $this->slice($starved), 'still not raised into a refusal');
        $this->assertArrayHasKey((int) $starved->getIdGridRun(), $res['skipped']);
        $this->assertStringContainsString('under its floor (40 of 300)', $res['skipped'][(int) $starved->getIdGridRun()]);
    }

    // ── the target-slice knob ───────────────────────────────────────────

    /**
     * WAS: reconcileFromRuns copied gtbot_trend_target_slice into
     * fleet_slot.target_slice at creation and slotTarget preferred the
     * column, so every later edit of the knob the config row itself calls
     * "the slice an activation raises the arm to" was inert.
     */
    public function testChangingTheTargetSliceConfigMovesTheArm(): void
    {
        $this->walletUsdt('1300');
        $arm = $this->mkRun('50', 'Trend', 5, 'Live', true, 'BTCUSDT', 100);
        $this->mkRun('300', 'Grid', 6);
        $slot = $this->slotFor($arm, 'active', ['trend_before' => '50', 'deploy_before' => 0, 'grids_before' => []], 4);
        $this->assertSame(0, bccomp('0', (string) $slot->getTargetSlice(), 8), 'stored: follow the config');

        $this->cfg(TrendActivator::CONFIG_TARGET_SLICE, '450');
        $this->pass($arm);
        $this->assertSame('450', $this->slice($arm), 'the operator raised the target and the arm followed');
    }

    /** An operator who pins ONE symbol still wins over the fleet-wide knob. */
    public function testAnOperatorPinnedSlotTargetStillWins(): void
    {
        $this->walletUsdt('1300');
        $arm = $this->mkRun('50', 'Trend', 5, 'Live', true, 'BTCUSDT', 100);
        $this->mkRun('300', 'Grid', 6);
        $slot = $this->slotFor($arm, 'active', ['trend_before' => '50', 'deploy_before' => 0, 'grids_before' => []], 4);
        $slot->setTargetSlice('200');
        $slot->save();

        $this->cfg(TrendActivator::CONFIG_TARGET_SLICE, '450');
        $this->assertSame('200', TrendActivator::effectiveTarget($slot));
        $this->pass($arm);
        $this->assertSame('200', $this->slice($arm));
    }

    // ── the drawdown floor follows the wallet it measured ───────────────

    /**
     * WAS: the floor was 75% of gtbot_shared_budget_quote whichever wallet
     * equity() had read. The RUNBOOK's own ordering (flip the money switch,
     * THEN fund the account) meant the first real daemon to reboot stamped an
     * account holding nothing: equity 0 against a 975 floor, and three ticks
     * later Daemon::checkGlobalDrawdown killed the entire fleet without a
     * cent having been lost.
     */
    public function testAFleetFlippedToRealBeforeTheAccountIsFundedIsNotKilled(): void
    {
        $this->seedWallet(['USDT' => '1300']);
        $g = $this->mkRun('650', 'Grid', 6, 'Live', false);
        $this->mkRun('350', 'Trend', 5, 'Live', false);
        $g->setBalQuote('0');      // the daemon booted and measured an empty account
        $g->setBalBase('0');
        $g->save();

        $eq = DrawdownGuard::equity();
        $this->assertSame('sim', $eq['source'], 'there is nothing real to guard until the account holds something');
        $this->assertNull(DrawdownGuard::check(), 'and nothing has been lost');
        $this->assertNull(DrawdownGuard::realBaseline(), 'an empty account is not a baseline');
    }

    /**
     * Fund it and the floor is a fraction of what YOU put in — 150 on a 200
     * canary, not 975 off the paper pool the account never held.
     */
    public function testTheFloorFollowsTheFundedRealAccount(): void
    {
        $this->seedWallet(['USDT' => '5000']); // the paper wallet must not stand in for it
        $g = $this->mkRun('100', 'Grid', 6, 'Live', false);
        $g->setBalQuote('200');
        $g->setBalBase('0');
        $g->save();

        $eq = DrawdownGuard::equity();
        $this->assertSame('real', $eq['source']);
        $this->assertSame('200', DrawdownGuard::realBaseline(), 'seeded from the first real equity measured');
        $this->assertSame(0, bccomp('150', (string) DrawdownGuard::floor('real'), 8), '25% of 200, not of 1300');
        $this->assertNull(DrawdownGuard::check(), 'a funded canary is not in drawdown the instant it starts');

        // and it still stops a REAL loss
        $g->setBalQuote('140');
        $g->save();
        $over = DrawdownGuard::check();
        $this->assertNotNull($over, '140 is under the 150 floor');
        $this->assertSame('real', $over['source']);
        $this->assertSame('200', $over['budget'], 'the alert names the account, not the paper pool');
    }

    /** Funded once, the switch is latched: an account run to zero IS a total loss. */
    public function testAFundedAccountRunToZeroStillTrips(): void
    {
        $this->seedWallet(['USDT' => '5000']);
        $g = $this->mkRun('100', 'Grid', 6, 'Live', false);
        $g->setBalQuote('200');
        $g->save();
        $this->assertSame('real', DrawdownGuard::equity()['source']);

        $g->setBalQuote('0');
        $g->save();
        $eq = DrawdownGuard::equity();
        $this->assertSame('real', $eq['source'], 'the baseline latches it — this is a loss, not an unfunded account');
        $this->assertNotNull(DrawdownGuard::check());
    }

    /** gtbot_use_all_funds caps against the wallet that was measured. */
    public function testUseAllFundsCapsAgainstTheRealBaselineNotThePaperPool(): void
    {
        $this->cfg(BudgetPool::CONFIG_USE_ALL, '1');
        $this->seedWallet(['USDT' => '5000']);
        $g = $this->mkRun('100', 'Grid', 6, 'Live', false);
        $g->setBalQuote('200');
        $g->setBalBase('0');
        $g->save();
        DrawdownGuard::equity();   // the first measurement is what seeds it
        $this->assertSame('200', DrawdownGuard::realBaseline());

        $g->setBalQuote('400');   // the canary doubled
        $g->save();
        $this->assertSame('200', BudgetPool::cap(), 'profit shifts the mix, it never raises the ceiling');
        $this->assertSame('190', BudgetPool::allocatable(), 'and the 5% reserve is 5% of THAT');
    }

    /**
     * gtbot_use_all_funds + a pool packed to the full cap was the compounding
     * failure: one USDT of mark-to-market drift put sum(slices) over cap and
     * fail-closed every entry on the fleet at once. The activator leaves the
     * reserve alone now, so the drift is absorbed.
     */
    public function testOneUsdtOfDriftNoLongerFailsClosesTheWholeFleet(): void
    {
        $this->cfg(BudgetPool::CONFIG_USE_ALL, '1');
        [$arm1, $arm10] = $this->liveShape();
        $this->assertSame('1300', BudgetPool::cap());
        $this->pass($arm1);
        $this->pass($arm10);

        \Propel::getConnection()->exec("UPDATE sim_wallet SET qty = '1299' WHERE asset = 'USDT'");
        $this->assertSame('1299', BudgetPool::cap());
        $this->assertNull(BudgetGuard::check(), 'the reserve absorbs exactly this');
    }

    // ── NAV: the window edge, and never guessing where a flow landed ────

    /**
     * WAS: report() built the series from the rows INSIDE the window only, so
     * a budget change that fell just outside a rolling 28-day window was
     * invisible while its money landed inside it — a flat fleet across prod's
     * 1000 → 1300 raise read +30%, and GoLiveGate certifies the soak off
     * exactly that number. The gate could PASS on a deposit.
     */
    public function testAFlowStampedBeforeTheWindowIsStillAFlow(): void
    {
        $series = [
            ['equity' => '1000', 'ref_price' => '100', 'at' => '2026-09-03 10:00', 'budget' => '1300'],
            ['equity' => '1300', 'ref_price' => '100', 'at' => '2026-09-03 10:15', 'budget' => '1300'],
            ['equity' => '1300', 'ref_price' => '100', 'at' => '2026-09-30 10:15', 'budget' => '1300'],
        ];
        $blind = NavLedger::benchmark($series);
        $this->assertSame(30.0, $blind['nav_return_pct'], 'with no history the jump is unattributable');

        // report() hands benchmark() the budget in force just before the window
        $seen = NavLedger::benchmark($series, '1000');
        $this->assertSame(0.0, $seen['nav_return_pct'], 'a deposit is not a return, whichever side of the edge it was stamped');
        $this->assertSame('300.00', $seen['net_flows']);
    }

    /**
     * WAS: "landed" meant the equity moved by at least HALF the flow, so half
     * a deposit arriving booked ALL of it and a flat fleet read −3.91%. A
     * return cannot be measured across an unsettled flow, so the step stays
     * OPEN until the money is in and one return is booked across the span.
     */
    public function testADepositArrivingInInstalmentsIsOneFlowAcrossOneSpan(): void
    {
        $b = NavLedger::benchmark([
            ['equity' => '1000', 'ref_price' => '100', 'at' => '2026-09-01 00:00', 'budget' => '1000'],
            ['equity' => '1000', 'ref_price' => '100', 'at' => '2026-09-01 00:15', 'budget' => '1300'],
            ['equity' => '1150', 'ref_price' => '100', 'at' => '2026-09-01 00:30', 'budget' => '1300'],
            ['equity' => '1300', 'ref_price' => '100', 'at' => '2026-09-01 00:45', 'budget' => '1300'],
        ]);
        $this->assertSame(0.0, $b['nav_return_pct']);
        $this->assertSame(0.0, $b['max_drawdown_pct']);
        $this->assertSame('300.00', $b['net_flows']);
    }

    /** WAS: two flows inside the lag window landed on each other — −7.69%, 20% drawdown. */
    public function testTwoFlowsInFlightAreOneOpenSpan(): void
    {
        $b = NavLedger::benchmark([
            ['equity' => '1000', 'ref_price' => '100', 'at' => '2026-09-01 00:00', 'budget' => '1000'],
            ['equity' => '1000', 'ref_price' => '100', 'at' => '2026-09-01 00:15', 'budget' => '1300'],
            ['equity' => '1000', 'ref_price' => '100', 'at' => '2026-09-01 00:30', 'budget' => '1500'],
            ['equity' => '1300', 'ref_price' => '100', 'at' => '2026-09-01 00:45', 'budget' => '1500'],
            ['equity' => '1500', 'ref_price' => '100', 'at' => '2026-09-01 01:00', 'budget' => '1500'],
        ]);
        $this->assertSame(0.0, $b['nav_return_pct'], 'a flat fleet across two deposits returned nothing');
        $this->assertSame(0.0, $b['max_drawdown_pct']);
        $this->assertSame('500.00', $b['net_flows'], 'both deposits are flows');
    }

    /**
     * A deposit whose money never visibly arrived, beside a fleet that also
     * moved: the window cannot say which of the two happened, so it books
     * neither and REPORTS the flow as pending.
     *
     * CHANGED 2026-09-21 (round 3). This used to assert −20% and a 20%
     * drawdown, on the reading "the money IS in, so the flow lands and what
     * is left is the real loss". But equity moved 100 against a 300 flow, and
     * `landed()` — the rule the same function uses everywhere else — says
     * that is NOT in. Round 2 only reached the lenient reading because the
     * end-of-window branch asked a different, weaker question (does the
     * equity move in the flow's DIRECTION at all), and that question is what
     * let one USDT of drift book a whole 300 USDT flow and hand GoLiveGate a
     * +23% soak. One rule now, at both ends: what is left is `flows_pending`,
     * and a window carrying one cannot certify the soak (GoLiveGate).
     */
    public function testADepositThatNeverArrivedIsReportedPendingNotBooked(): void
    {
        $series = [['equity' => '1000', 'ref_price' => '100', 'at' => '2026-09-01 00:00', 'budget' => '1000']];
        $series[] = ['equity' => '1100', 'ref_price' => '100', 'at' => '2026-09-01 00:10', 'budget' => '1300'];
        for ($i = 2; $i <= 14; $i++) {
            $series[] = ['equity' => '1100', 'ref_price' => '100', 'at' => sprintf('2026-09-01 %02d:%02d', intdiv($i * 10, 60), ($i * 10) % 60), 'budget' => '1300'];
        }
        $b = NavLedger::benchmark($series);
        $this->assertSame('300.00', $b['flows_pending'], 'the deposit is unaccounted for — say so');
        $this->assertSame('0.00', $b['net_flows'], 'and it is not booked as one');
        $this->assertSame(0.0, $b['max_drawdown_pct'], 'no drawdown is invented out of it either');
    }

    /** A 'real' NAV row is stamped with the REAL account's baseline. */
    public function testANavRowIsStampedWithTheBudgetBehindTheWalletItMeasured(): void
    {
        $this->seedWallet(['USDT' => '5000']);
        $g = $this->mkRun('100', 'Grid', 6, 'Live', false);
        $g->setBalQuote('200');
        $g->setBalBase('0');
        $g->save();

        $row = NavLedger::snapshot('60000');
        $this->assertNotNull($row);
        $this->assertSame('real', (string) $row->getMode());
        $this->assertSame(0, bccomp('200', (string) $row->getBudgetQuote(), 8), 'the real account, not the 1300 paper seed');
    }

    // ── a whole trend cycle, both crons, no oscillation ─────────────────

    public function testDeactivateReleaseAndReArmDoNotOscillate(): void
    {
        $this->walletUsdt('1300');
        $arm1 = $this->mkRun('350', 'Trend', 5, 'Live', true, 'BTCUSDT', 100);
        $arm10 = $this->mkRun('350', 'Trend', 20, 'Live', true, 'BNBUSDT', 100);
        $g4 = $this->mkRun('300', 'Grid', 6, 'Live', true, 'BNBUSDT');
        $g7 = $this->mkRun('300', 'Grid', 6, 'Live', true, 'BTCUSDT');
        $this->slotFor($arm1, 'active', [
            'trend_before' => '50',
            'deploy_before' => 0,
            'grids_before' => [(int) $g4->getIdGridRun() => '450', (int) $g7->getIdGridRun() => '450'],
        ], 4);
        $this->slotFor($arm10, 'active', ['trend_before' => '50'], 4);
        $runs = [$arm1, $arm10, $g4, $g7];

        $seen = [];
        foreach (['HOSTILE', 'HOSTILE', 'HOSTILE', 'HOSTILE', 'HOSTILE', 'HOSTILE'] as $i => $shape) {
            $this->pass($arm1, $shape);
            $this->allocateCron();
            $seen['down' . $i] = $this->shape($runs);
        }
        foreach (['TREND_UP', 'TREND_UP', 'TREND_UP', 'TREND_UP', 'TREND_UP', 'TREND_UP'] as $i => $shape) {
            $this->pass($arm1, $shape);
            $this->allocateCron();
            $seen['up' . $i] = $this->shape($runs);
        }
        $this->assertSame('active', (string) FleetSlots::byRun((int) $arm1->getIdGridRun())->getState(), 're-armed at the end');
        $this->assertSame($seen['down4'], $seen['down5'], 'the wind-down settles');
        $this->assertSame($seen['up4'], $seen['up5'], 'and so does the re-arm');
        $this->assertNull(BudgetGuard::check());
        $this->assertSame(
            0,
            BotEventQuery::create()->filterByLevel('Alert')->filterByKind(Allocator::KIND_ERROR)->count(),
            'no rolled-back pass through a whole trend cycle'
        );
        foreach ($seen as $tag => $row) {
            $this->assertTrue(
                bccomp($row[4], BudgetPool::cap(), 0) <= 0,
                sprintf('%s summed to %s, over the cap', $tag, $row[4])
            );
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // ROUND 3 (2026-09-21) — what an adversarial review of 6e03b03 found
    // ═══════════════════════════════════════════════════════════════════

    // ── release: give back what the pool holds, never more ──────────────

    /**
     * WAS: planRelease() returned the pre-activation slice unconditionally
     * and release() wrote it with no tryWrites() around it — the one write
     * path without one. On a breakout arm that is routinely a RAISE: the
     * re-assert derives an active arm's slice down to its position, the
     * 15-minute allocator hands the freed capital to the grids, and release
     * then asked for the whole pre-activation number back off a pool that no
     * longer had it. BudgetGuard refused, the RuntimeException escaped
     * TrendActivator::pass(), and the slot stayed winding_down for ever:
     * never released, never re-armed (the flat branch runs before decide()),
     * pinned out of the allocator, its slice stranded.
     */
    public function testAFlatArmIsReleasedToWhatThePoolCanActuallyFund(): void
    {
        $this->walletUsdt('1300');
        $arm = $this->mkRun('331', 'Trend', 5, 'Live', true, 'BTCUSDT', 100);
        $g4 = $this->mkRun('660', 'Grid', 6, 'Live', true, 'BNBUSDT', 25);
        $g7 = $this->mkRun('244', 'Grid', 6, 'Live', true, 'BTCUSDT', 25);
        $this->assertSame('1235', bcadd(Allocator::activeSum(), '0', 0), 'the fleet starts inside the allocatable pool');
        $this->slotFor($arm, 'idle', null, 1);

        // TREND_UP confirmed → funded to the 350 target
        $this->pass($arm);
        $this->assertSame('active', (string) FleetSlots::byRun((int) $arm->getIdGridRun())->getState());
        $this->assertSame('350', $this->slice($arm));
        $this->assertSame('331', (string) FleetSlots::activationOf((int) $arm->getIdGridRun())['trend_before']);

        // it takes a position worth 150 → the re-assert sizes it down to it
        $arm->setEngineState(json_encode(['entry' => '100', 'qty' => '1.5']));
        $arm->save();
        $this->inventory($arm, '1.5');
        $this->pass($arm);
        $this->assertSame('150', $this->slice($arm), 'sized to the position it can actually reach');

        // the allocator hands the freed capital to the grids — as it should
        $this->assertTrue($this->allocateCron());
        $this->assertTrue(
            bccomp(bcadd($this->slice($g4), $this->slice($g7), 0), '1000', 0) > 0,
            'the grids now hold what the arm gave back'
        );

        // the regime ends and the arm closes its position
        $this->pass($arm, 'HOSTILE');
        $this->assertSame('winding_down', (string) FleetSlots::byRun((int) $arm->getIdGridRun())->getState());
        $arm->setEngineState(null);
        $arm->save();
        \Propel::getConnection()->exec('DELETE FROM bot_order WHERE id_grid_run = ' . (int) $arm->getIdGridRun());

        $this->pass($arm, 'HOSTILE');
        $this->assertSame('idle', (string) FleetSlots::byRun((int) $arm->getIdGridRun())->getState(), 'a flat arm is released, whatever the pool can afford');
        $this->assertNull(BudgetGuard::check(), 'and never by overcommitting the pool');
        $this->assertSame(
            0,
            BotEventQuery::create()->filterByLevel('Alert')->filterByKind(TrendActivator::KIND_ERROR)->count(),
            'no refusal, so nothing to alert about'
        );
    }

    /**
     * And an arm ALREADY stuck in winding_down by that bug — prod is one
     * regime change away from this — comes back on the very next pass, with
     * no operator action: it takes what the pool can fund, the allocator
     * settles the rest, and the slot is free to re-arm.
     */
    public function testAnArmWedgedInWindingDownIsReleasedOnTheNextPass(): void
    {
        $this->walletUsdt('1300');
        $arm = $this->mkRun('150', 'Trend', 5, 'Live', true, 'BTCUSDT', 0);
        $g4 = $this->mkRun('802', 'Grid', 6, 'Live', true, 'BNBUSDT', 25);
        $g7 = $this->mkRun('283', 'Grid', 6, 'Live', true, 'BTCUSDT', 25);
        // the wedged state: deploy 0, slice sized to a position that is gone,
        // and an activation payload asking for 331 the pool no longer has
        $this->slotFor($arm, 'winding_down', [
            'trend_before' => '331',
            'deploy_before' => 0,
            'grids_before' => [(int) $g4->getIdGridRun() => '660', (int) $g7->getIdGridRun() => '244'],
        ]);
        $this->assertTrue(TrendActivator::isFlat($arm));

        $this->pass($arm, 'HOSTILE');
        $slot = FleetSlots::byRun((int) $arm->getIdGridRun());
        $this->assertSame('idle', (string) $slot->getState(), 'un-wedged');
        $this->assertSame('215', $this->slice($arm), 'what the 1300 cap could fund of the 331 it asked for');
        $this->assertNull($slot->getActivation(), 'the episode is over');
        $this->assertNull(BudgetGuard::check());
        $this->assertTrue(Allocator::effectiveMode($arm->reload() ?? $arm)['auto'], 'and the allocator owns it again');
    }

    // ── activation: free headroom first, and the SLOT's target ──────────

    /**
     * WAS: round 2 made the grids' idle capital pay for an activation before
     * any headroom, on the reading "headroom == the reserve". It is not: a
     * fleet with 584 USDT of genuinely free capital (a run retired, a run
     * held) still stripped both grids toward their floors — and the allocator
     * handed it all straight back on its next pass. Two trims, two raises,
     * four Reloads, and a Reload is a daemon RESTART.
     */
    public function testActivationSpendsFreeHeadroomBeforeTouchingTheGrids(): void
    {
        $this->walletUsdt('1300');
        $arm = $this->mkRun('50', 'Trend', 5, 'Live', true, 'BTCUSDT', 100);
        $g4 = $this->mkRun('390', 'Grid', 6, 'Live', true, 'BNBUSDT', 25);
        $g7 = $this->mkRun('211', 'Grid', 6, 'Live', true, 'BTCUSDT', 25);
        // 651 of a 1235 allocatable pool: 584 free, 65 of it the reserve
        $this->slotFor($arm, 'idle', null, 1);
        $reloads = BotCommandQuery::create()->filterByCommand('Reload')->count();

        $this->pass($arm);
        $this->assertSame('350', $this->slice($arm));
        $this->assertSame('390', $this->slice($g4), 'the grids keep what the free headroom could fund');
        $this->assertSame('211', $this->slice($g7));
        $this->assertSame(
            $reloads + 1,
            BotCommandQuery::create()->filterByCommand('Reload')->count(),
            'one Reload — the arm. A grid restarted for nothing is a restart for nothing'
        );
        $this->assertTrue(
            bccomp(Allocator::headroom(BudgetPool::cap()), BudgetPool::reserved(), 0) >= 0,
            'and the reserve is still whole'
        );
    }

    /**
     * The reserve is still the LAST resort: with the free headroom spent and
     * the grids on their floors, the raise comes out of it rather than being
     * refused. (The round-2 ordering, intact under the new one.)
     */
    public function testTheReserveStillFundsAnArmTheGridsAndTheFreePoolCannot(): void
    {
        $this->walletUsdt('1300');
        $arm = $this->mkRun('50', 'Trend', 5, 'Live', true, 'BTCUSDT', 100);
        $this->mkRun('600', 'Grid', 6, 'Live', true, 'BNBUSDT', 100);   // floor 300
        $this->mkRun('585', 'Grid', 6, 'Live', true, 'BTCUSDT', 100);   // floor 300
        $this->assertSame('1235', bcadd(Allocator::activeSum(), '0', 0), 'no free headroom at all');
        $this->slotFor($arm, 'idle', null, 1);

        $this->pass($arm);
        $this->assertSame('350', $this->slice($arm), 'the target is still reached');
        $this->assertNull(BudgetGuard::check());
    }

    /**
     * WAS: activate() funded self::targetSlice() — the fleet-wide config knob
     * — while every other path (reassert, rearm, derivedSlice, the brief, the
     * episode ledger) reads the SLOT's target. An operator who pinned one
     * symbol to 200 got an arm funded at 350 off the grids, and the engine
     * entered at that size on the next tick; the re-assert only took it back
     * 15 minutes later, with the position already on.
     */
    public function testActivationFundsTheSlotsPinnedTarget(): void
    {
        $this->walletUsdt('1300');
        $arm = $this->mkRun('50', 'Trend', 5, 'Live', true, 'BTCUSDT', 100);
        $this->mkRun('600', 'Grid', 6, 'Live', true, 'BTCUSDT', 25);
        $slot = $this->slotFor($arm, 'idle', null, 1);
        $slot->setTargetSlice('200');
        $slot->save();

        $this->pass($arm);
        $this->assertSame('200', $this->slice($arm), 'the arm is funded to what the operator pinned');
        $this->assertSame('200', (string) FleetSlots::activationOf((int) $arm->getIdGridRun())['trend_after']);
    }

    // ── NAV: one landing rule, and a drawdown that cannot hide ──────────

    /**
     * WAS: inside the series a flow landed only once the equity had moved
     * with it by 90%; at the LAST point the rule collapsed to "did it move in
     * that direction at all", so one USDT booked a 300 USDT flow. A budget
     * lowered on the last point of the window, with the fleet down 1 USDT for
     * its own reasons, read +23% — and GoLiveGate certifies the soak off
     * exactly that number.
     */
    public function testATinyMoveDoesNotBookAWholeFlowAtTheWindowEdge(): void
    {
        $b = NavLedger::benchmark([
            ['equity' => '1300', 'ref_price' => '100', 'at' => '2026-09-01 00:00', 'budget' => '1300'],
            ['equity' => '1300', 'ref_price' => '100', 'at' => '2026-09-01 00:15', 'budget' => '1300'],
            ['equity' => '1299', 'ref_price' => '100', 'at' => '2026-09-01 00:30', 'budget' => '1000'],
        ]);
        $this->assertEqualsWithDelta(-0.08, $b['nav_return_pct'], 0.05, 'a flat fleet returned ~0, not +23%');
        $this->assertSame('-300.00', $b['flows_pending'], 'the withdrawal is still in the air — say so');
    }

    /**
     * WAS: peak/maxDd only moved when a step CLOSED, and a step stays open
     * for as long as a flow is pending — so a 30% crash between a budget edit
     * and its money arriving reported 0.00% drawdown, straight through the
     * 25% go-live gate. The open step now carries a provisional index that
     * can reveal a fall but never invent a recovery.
     */
    public function testACrashInsideAPendingFlowIsStillInTheDrawdown(): void
    {
        $b = NavLedger::benchmark([
            ['equity' => '1000', 'ref_price' => '100', 'at' => '2026-09-01 00:00', 'budget' => '1000'],
            ['equity' => '1000', 'ref_price' => '100', 'at' => '2026-09-01 00:15', 'budget' => '1300'],
            ['equity' => '700', 'ref_price' => '100', 'at' => '2026-09-01 00:30', 'budget' => '1300'],
            ['equity' => '1000', 'ref_price' => '100', 'at' => '2026-09-01 00:45', 'budget' => '1300'],
            ['equity' => '1300', 'ref_price' => '100', 'at' => '2026-09-01 01:00', 'budget' => '1300'],
        ]);
        $this->assertSame(0.0, $b['nav_return_pct'], 'the deposit is not a return');
        $this->assertSame(30.0, $b['max_drawdown_pct'], 'and the 30% the fleet was down is not hidden by it');
    }

    // ── the real account: what counts as funded ─────────────────────────

    /**
     * WAS: the baseline latched on the FIRST positive real equity, whatever
     * it was. RUNBOOK step 7 says to hold a BNB fee float — land it before
     * the USDT, let one daemon stamp the account, and the fleet was
     * baselined at 15 USDT: a floor of 11.25 (the guard disarmed for the
     * whole real account) and, with gtbot_use_all_funds on, a cap of 15 that
     * fail-closes every entry. Nothing under MIN_SLICE can be traded by a run
     * at all, so an account holding less is not the fleet's wallet yet.
     */
    public function testAFeeFloatDoesNotLatchTheRealAccountBaseline(): void
    {
        $this->seedWallet(['USDT' => '1300']);
        $g = $this->mkRun('100', 'Grid', 6, 'Live', false, 'BNBUSDT');
        $g->setBalQuote('0');
        $g->setBalBase('0.1');   // the fee float, priced at 150 → 15 USDT
        $g->save();

        $this->assertSame('sim', DrawdownGuard::equity()['source'], 'the floor stays on the wallet that holds the fleet');
        $this->assertNull(DrawdownGuard::realBaseline(), 'a fee float is not what the account was funded with');
        $this->assertNull(DrawdownGuard::check(), 'and nothing is in drawdown');

        $g->setBalQuote('1300');   // the real money arrives
        $g->save();
        $this->assertSame('real', DrawdownGuard::equity()['source']);
        $this->assertSame('1315', DrawdownGuard::realBaseline(), 'THIS is the funded account');
    }

    /**
     * The RUNBOOK's recovery step, executable. A withdrawal trips the floor
     * (the baseline is what the account was FUNDED with), and the row that
     * clears it is the MEASURED wallet's — editing the paper seed while the
     * source is 'real' does nothing at all, which is what the RUNBOOK used to
     * tell the operator to do.
     */
    public function testTheRowThatClearsARealFloorIsTheMeasuredWalletsBaseline(): void
    {
        $this->seedWallet(['USDT' => '1300']);
        $g = $this->mkRun('100', 'Grid', 6, 'Live', false, 'BTCUSDT');
        $g->setBalQuote('2000');
        $g->setBalBase('0');
        $g->save();
        DrawdownGuard::equity();   // the first measurement latches the baseline
        $this->assertSame('2000', DrawdownGuard::realBaseline());

        $g->setBalQuote('1300');   // the operator withdraws the 700 of profit
        $g->save();
        $this->assertNotNull(DrawdownGuard::check(), '1300 against a 1500 floor');

        $this->cfg('gtbot_shared_budget_quote', '1300');
        $this->assertNotNull(DrawdownGuard::check(), 'the paper seed is not the measured wallet');
        $this->cfg(DrawdownGuard::CONFIG_REAL_BASELINE, '1300');
        $this->assertNull(DrawdownGuard::check(), 'the real account\'s baseline is the row that clears it');
    }

    // ── the starved run reaches the feed ────────────────────────────────

    /**
     * WAS: round 2 journaled a run left under its floor inside
     * `if ($applied !== [])`, and a pass whose whole point is "nothing could
     * be moved" applies nothing by definition — so the one case it was
     * written for never reached the event feed, and bin/gtbot-allocate never
     * printed `skipped` either.
     */
    public function testAnUnderFloorRunIsJournaledEvenWhenNothingMoved(): void
    {
        $this->walletUsdt('1300');
        $pinned = $this->mkRun('1000', 'Grid', 20);
        $pinned->setAllocMode('Fixed');
        $pinned->save();
        $starved = $this->mkRun('40', 'Grid', 6, 'Live', true, 'BNBUSDT');
        $this->assertSame('300', Allocator::hardFloor($starved));
        $this->assertTrue(Allocator::rebalanceNeeded('25')['run'], 'the cron does run this pass');

        $host = FleetSlots::hostRun();
        $log = new \App\Domains\Bot\EventLog((int) $host->getIdGridRun(), false);
        $res = Allocator::rebalance('cron', true, $log);
        $this->assertSame([], $res['applied'], 'nothing can move — that IS the case being reported');

        $ev = BotEventQuery::create()->filterByKind(Allocator::KIND_REBALANCE)->orderByIdBotEvent(\Criteria::DESC)->findOne();
        $this->assertNotNull($ev, 'a run carrying less than its own viability floor has to be said out loud');
        $this->assertStringContainsString(
            sprintf('#%d (40 of 300)', (int) $starved->getIdGridRun()),
            (string) $ev->getMessage()
        );
    }

    /** ...and a pass where every skipped run is merely PINNED stays silent:
     *  a standing state is not news, and this runs every 15 minutes. */
    public function testAPassThatOnlySkippedPinnedRunsWritesNothing(): void
    {
        $this->walletUsdt('1300');
        $arm = $this->mkRun('350', 'Trend', 20, 'Live', true, 'BTCUSDT', 100);
        $this->mkRun('885', 'Grid', 6, 'Live', true, 'BNBUSDT');
        $this->slotFor($arm, 'active', ['trend_before' => '50']);
        $before = BotEventQuery::create()->count();

        $host = FleetSlots::hostRun();
        $res = Allocator::rebalance('cron', true, new \App\Domains\Bot\EventLog((int) $host->getIdGridRun(), false));
        $this->assertSame([], $res['applied']);
        $this->assertNotSame([], $res['skipped'], 'the arm IS skipped — it is pinned');
        $this->assertSame($before, BotEventQuery::create()->count(), 'and that is not worth an event every 15 minutes');
    }
}
