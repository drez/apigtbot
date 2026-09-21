<?php

namespace Tests\Custom\Bot;

use App\BotCommandQuery;
use App\BotOrder;
use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\BudgetGuard;
use App\Domains\Bot\RunLifecycle;
use App\GridRun;
use App\GridRunQuery;
use Tests\Builder\Support\DbTestCase;

/**
 * retire / finalize, and the guards around them.
 *
 * The invariant under test is `Done ⇒ flat`: a Done run drops out of
 * DrawdownGuard::equity(), ModeSwitch and every dashboard query, so one that
 * still holds coins would be unpriced (counted as ZERO, fail-closed) and could
 * trip the drawdown floor on a loss that never happened. Retiring is the state
 * that keeps such a run visible until its exits clear.
 *
 * The other rule everything here defends: never sell at a loss unless there are
 * no funds. minimize_loss cannot realize one; sell_now needs a permitting
 * profile AND an explicit acknowledgement.
 */
class RunLifecycleTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (GridRunQuery::create()->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)->find() as $r) {
            $r->setStatus('Halted');
            $r->save();
        }
        $this->setConfig('gtbot_shared_budget_quote', '1000');
        $this->setConfig('gtbot_max_drawdown_pct', '0');
    }

    private function setConfig(string $key, string $value): void
    {
        // clear BEFORE the read too: a pooled row already holding $value makes
        // save() a no-op, so the rolled-back DB row keeps the stale value while
        // the pool reports the new one
        \App\ConfigPeer::clearInstancePool();
        $c = ConfigQuery::create()->findOneByConfig($key) ?? (new Config())->setConfig($key);
        $c->setValue($value);
        $c->save();
        \App\ConfigPeer::clearInstancePool();
    }

    private function mkRun(string $status = 'Testnet', string $budget = '300', string $profile = 'Balanced'): GridRun
    {
        $r = new GridRun();
        $r->setLabel(static::uniq('life'));
        $r->setSymbol('BTCUSDT');
        $r->setStatus($status);
        $r->setAlgo('Grid');
        $r->setProfile($profile);
        $r->setSimulated(true);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(5);
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote($budget);
        $r->setDeployPct(100);
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('300');
        $r->setMaxOrderQuote('100');
        $r->setDailyLossLimitQuote('30');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(30);
        $r->setSellAtLoss(false);
        $r->setRunUid(static::uniq('life'));
        // old enough that daemonProof()'s heartbeat leg passes
        $r->setLastTickAt(date('Y-m-d H:i:s', time() - 3600));
        $r->setLastPrice('150');
        $r->save();
        return $r;
    }

    /** a filled buy with no matching sell = held inventory */
    private function holdInventory(GridRun $run, string $price = '160', string $qty = '1'): void
    {
        $row = new BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId(static::uniq('life-o'));
        $row->setLevelIdx(1);
        $row->setSide('Buy');
        $row->setState('Filled');
        $row->setPrice($price);
        $row->setQty($qty);
        $row->setFilledQty($qty);
        $row->setFeePaid('0.1');
        $row->setSimulated(true);
        $row->save();
    }

    private function commands(GridRun $r): array
    {
        $out = [];
        foreach (BotCommandQuery::create()->filterByIdGridRun((int) $r->getIdGridRun())->orderByIdBotCommand()->find() as $c) {
            $out[] = (string) $c->getCommand();
        }
        return $out;
    }

    /** Propel's reload() returns void, so this is the re-read idiom here. */
    private function fresh(GridRun $r): GridRun
    {
        $r->reload();
        return $r;
    }

    /** systemctl stub: the unit is not managed here */
    private function noUnit(): callable
    {
        return static fn (string $args): string => '';
    }

    // ── retire: the happy paths ─────────────────────────────────────────

    public function testRetiringADraftGoesStraightToDone(): void
    {
        $r = $this->mkRun('Draft');
        $res = RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'scratch experiment');
        $this->assertTrue($res['ok'], $res['message']);
        $this->assertSame('Done', (string) $this->fresh($r)->getStatus());
        $this->assertSame('0', $res['freed'], 'a Draft holds no slice');
        $this->assertSame([], $this->commands($r), 'nothing to cancel');
    }

    public function testRetiringAFlatRunArchivesInOneCallAndFreesTheSlice(): void
    {
        $r = $this->mkRun('Testnet', '300');
        $before = BudgetGuard::check();
        $this->assertNull($before, 'fixture should start within budget');

        $res = RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'done with it');
        $this->assertTrue($res['ok'], $res['message']);
        $this->assertTrue($res['flat']);
        $this->assertSame('Done', (string) $this->fresh($r)->getStatus());
        $this->assertSame('300', $res['freed']);
        $this->assertContains('CancelBuys', $this->commands($r));

        // the slice is out of the pool: a new run may now claim it
        $this->assertNull(BudgetGuard::check(null, '300', 'Live'));
    }

    public function testRetiringWithInventoryLandsInRetiringNotDone(): void
    {
        $r = $this->mkRun('Testnet', '300');
        $this->holdInventory($r);

        $res = RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'winding down');
        $this->assertTrue($res['ok'], $res['message']);
        $this->assertFalse($res['flat']);
        $r->reload();
        $this->assertSame('Retiring', (string) $r->getStatus());
        $this->assertTrue((bool) $r->getKillSwitch());
        $this->assertSame(0, (int) $r->getDeployPct());
        $this->assertFalse((bool) $r->getSellAtLoss(), 'minimize_loss must never touch the switch');
        $this->assertNotContains('Flatten', $this->commands($r));
        // the slice still leaves the pool — working sells consume no quote
        $this->assertSame('300', $res['freed']);
    }

    public function testBudgetQuoteIsNotZeroedOnRetire(): void
    {
        // ProfilePolicy derives max_order_quote from the slice; a zero slice
        // would make the run veto its own exits.
        $r = $this->mkRun('Testnet', '300');
        $this->holdInventory($r);
        RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'keep the caps');
        $this->assertSame(0, bccomp('300', (string) $this->fresh($r)->getBudgetQuote(), 8));
    }

    // ── sell_now and the loss rules ─────────────────────────────────────

    public function testSellNowUnderwaterWithoutAckIsRefusedAndWritesNothing(): void
    {
        $r = $this->mkRun('Testnet', '300');
        $this->holdInventory($r, '160', '1');   // cost 160, mark 150 → underwater

        $res = RunLifecycle::retire($r, RunLifecycle::EXIT_SELL_NOW, 'get me out');
        $this->assertFalse($res['ok']);
        $this->assertStringContainsString('realize a loss', $res['message']);

        $r->reload();
        $this->assertSame('Testnet', (string) $r->getStatus(), 'status must be untouched');
        $this->assertFalse((bool) $r->getKillSwitch(), 'kill switch must be untouched');
        $this->assertFalse((bool) $r->getSellAtLoss(), 'switch must be untouched');
        $this->assertSame(100, (int) $r->getDeployPct(), 'deploy must be untouched');
        $this->assertSame([], $this->commands($r), 'nothing may be queued');
    }

    public function testSellNowUnderwaterWithAckFlipsTheSwitchAndFlattens(): void
    {
        $r = $this->mkRun('Testnet', '300');
        $this->holdInventory($r, '160', '1');

        $res = RunLifecycle::retire($r, RunLifecycle::EXIT_SELL_NOW, 'accepting the loss', true);
        $this->assertTrue($res['ok'], $res['message']);
        $r->reload();
        $this->assertTrue((bool) $r->getSellAtLoss(), 'ack_loss must open the switch or Flatten is refused downstream');
        $this->assertContains('Flatten', $this->commands($r));
        $this->assertSame('Retiring', (string) $r->getStatus());
    }

    public function testSellNowAboveBreakevenNeedsNoAckAndLeavesTheSwitchAlone(): void
    {
        $r = $this->mkRun('Testnet', '300');
        $this->holdInventory($r, '100', '1');   // cost 100, mark 150 → in profit

        $res = RunLifecycle::retire($r, RunLifecycle::EXIT_SELL_NOW, 'taking the win');
        $this->assertTrue($res['ok'], $res['message']);
        $r->reload();
        $this->assertFalse((bool) $r->getSellAtLoss(), 'a profitable exit needs no switch change');
        $this->assertContains('Flatten', $this->commands($r));
    }

    public function testNoLossProfileRefusesSellNowEvenWithAck(): void
    {
        $r = $this->mkRun('Testnet', '300', 'NoLoss');
        $this->holdInventory($r, '160', '1');

        $res = RunLifecycle::retire($r, RunLifecycle::EXIT_SELL_NOW, 'force it', true);
        $this->assertFalse($res['ok'], 'profile policy must beat a per-call acknowledgement');
        $this->assertStringContainsString('NoLoss', $res['message']);
        $this->assertSame('Testnet', (string) $this->fresh($r)->getStatus());
    }

    // ── validation ──────────────────────────────────────────────────────

    public function testRetireRequiresAReason(): void
    {
        $r = $this->mkRun();
        $res = RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, '   ');
        $this->assertFalse($res['ok']);
        $this->assertStringContainsString('reason is required', $res['message']);
        $this->assertSame('Testnet', (string) $this->fresh($r)->getStatus());
    }

    public function testRetireRejectsAnUnknownExitStrategy(): void
    {
        $r = $this->mkRun();
        $res = RunLifecycle::retire($r, 'yolo', 'because');
        $this->assertFalse($res['ok']);
        $this->assertSame('Testnet', (string) $this->fresh($r)->getStatus());
    }

    public function testRetiringAnArchivedRunIsRefused(): void
    {
        $r = $this->mkRun('Testnet');
        RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'first');
        $this->assertSame('Done', (string) $this->fresh($r)->getStatus());

        $res = RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'again');
        $this->assertFalse($res['ok']);
        $this->assertStringContainsString('already archived', $res['message']);
    }

    public function testRetiringARetiringRunPointsAtSellNow(): void
    {
        $r = $this->mkRun('Testnet');
        $this->holdInventory($r);
        RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'winding');
        $this->assertSame('Retiring', (string) $this->fresh($r)->getStatus());

        $res = RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'again');
        $this->assertFalse($res['ok']);
        $this->assertStringContainsString(RunLifecycle::EXIT_SELL_NOW, $res['message']);
    }

    // ── finalize ────────────────────────────────────────────────────────

    public function testFinalizeRefusesWhileInventoryRemains(): void
    {
        $r = $this->mkRun('Testnet');
        $this->holdInventory($r);
        RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'winding');

        $res = RunLifecycle::finalize($r);
        $this->assertFalse($res['ok'], 'Done must imply flat');
        $this->assertFalse($res['flat']);
        $this->assertSame('Retiring', (string) $this->fresh($r)->getStatus());
    }

    public function testFinalizeArchivesOnceFlat(): void
    {
        $r = $this->mkRun('Testnet');
        $this->holdInventory($r);
        RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'winding');
        $this->assertSame('Retiring', (string) $this->fresh($r)->getStatus());

        // the exit fills: drop the inventory
        \App\BotOrderQuery::create()->filterByIdGridRun((int) $r->getIdGridRun())->delete();
        \App\BotOrderPeer::clearInstancePool();

        $res = RunLifecycle::finalize($r);
        $this->assertTrue($res['ok'], $res['message']);
        $this->assertSame('Done', (string) $this->fresh($r)->getStatus());
    }

    public function testFinalizeIsIdempotent(): void
    {
        $r = $this->mkRun('Testnet');
        RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'flat already');
        $this->assertSame('Done', (string) $this->fresh($r)->getStatus());
        $res = RunLifecycle::finalize($r);
        $this->assertTrue($res['ok']);
    }

    public function testFinalizeRefusesANonRetiringRun(): void
    {
        $r = $this->mkRun('Testnet');
        $res = RunLifecycle::finalize($r);
        $this->assertFalse($res['ok']);
        $this->assertStringContainsString('only a Retiring run', $res['message']);
    }

    // ── inspect ─────────────────────────────────────────────────────────

    public function testInspectReportsTheLossPictureWithoutWriting(): void
    {
        $r = $this->mkRun('Testnet', '300');
        $this->holdInventory($r, '160', '1');

        $i = RunLifecycle::inspect($r);
        $this->assertFalse($i['flat']);
        $this->assertSame('Retiring', $i['lands_in']);
        $this->assertTrue($i['sell_now_would_realize_loss']);
        $this->assertTrue($i['profile_allows_loss']);
        $this->assertFalse($i['sell_at_loss']);
        $this->assertSame('300', $i['slice']);
        $this->assertTrue($i['frees_slice']);

        $this->assertSame('Testnet', (string) $this->fresh($r)->getStatus(), 'inspect must not write');
    }

    public function testInspectOnAFlatRunSaysItLandsInDone(): void
    {
        $r = $this->mkRun('Testnet');
        $this->assertSame('Done', RunLifecycle::inspect($r)['lands_in']);
    }

    // ── purge guards ────────────────────────────────────────────────────

    public function testMayPurgeRefusesOutsideOfPurge(): void
    {
        $r = $this->mkRun('Testnet');
        RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'archived');
        $verdict = RunLifecycle::mayPurge($this->fresh($r), $this->noUnit());
        $this->assertFalse($verdict['ok'], 'a raw delete must never be allowed');
        $this->assertStringContainsString('gtbot_purge_run', $verdict['message']);
    }

    public function testPurgeRefusesARunThatIsNotArchived(): void
    {
        $r = $this->mkRun('Testnet');
        $res = RunLifecycle::purge($r, 'nope', true, $this->noUnit());
        $this->assertFalse($res['ok']);
        $this->assertStringContainsString('archive it', $res['message']);
        $this->assertNotNull(GridRunQuery::create()->findPk((int) $r->getIdGridRun()));
    }

    public function testPurgeWithoutDataLossConfirmationOnlyPreviews(): void
    {
        $r = $this->mkRun('Testnet');
        $this->holdInventory($r);
        RunLifecycle::retire($r, RunLifecycle::EXIT_SELL_NOW, 'out', true);
        \App\BotOrderQuery::create()->filterByIdGridRun((int) $r->getIdGridRun())->delete();
        \App\BotOrderPeer::clearInstancePool();
        RunLifecycle::finalize($r);
        $this->assertSame('Done', (string) $this->fresh($r)->getStatus());

        $res = RunLifecycle::purge($r, 'cleanup', false, $this->noUnit());
        $this->assertFalse($res['ok']);
        $this->assertArrayHasKey('deleted', $res);
        $this->assertNotNull(GridRunQuery::create()->findPk((int) $r->getIdGridRun()), 'preview must delete nothing');
    }

    public function testPurgeRequiresAReason(): void
    {
        $r = $this->mkRun('Testnet');
        RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'archived');
        $res = RunLifecycle::purge($this->fresh($r), '  ', true, $this->noUnit());
        $this->assertFalse($res['ok']);
    }

    public function testPurgeRefusesWhileTheUnitIsActive(): void
    {
        $r = $this->mkRun('Testnet');
        RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'archived');
        $res = RunLifecycle::purge($this->fresh($r), 'cleanup', true, static fn (string $a): string => 'active');
        $this->assertFalse($res['ok']);
        $this->assertStringContainsString('respawn', $res['message']);
        $this->assertNotNull(GridRunQuery::create()->findPk((int) $r->getIdGridRun()));
    }

    public function testPurgeRefusesAFreshHeartbeat(): void
    {
        $r = $this->mkRun('Testnet');
        RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'archived');
        $r->reload();
        $r->setLastTickAt(date('Y-m-d H:i:s'));
        $r->save();
        $res = RunLifecycle::purge($r, 'cleanup', true, $this->noUnit());
        $this->assertFalse($res['ok']);
        $this->assertStringContainsString('heartbeat', $res['message']);
    }
}
