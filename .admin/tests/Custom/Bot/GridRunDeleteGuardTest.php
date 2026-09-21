<?php

namespace Tests\Custom\Bot;

use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\BudgetGuard;
use App\Domains\Bot\RunLifecycle;
use App\GridRun;
use App\GridRunQuery;
use App\GridRunServiceWrapper;
use Tests\Builder\Support\DbTestCase;

/**
 * A grid_run must only ever be deleted through RunLifecycle::purge(), because
 * the shared paper wallet is DERIVED from a replay of every simulated fill and
 * the FK cascade takes this run's bot_order rows with it. Purge seals the run's
 * net contribution into the wallet baseline first; a raw delete cannot, so it
 * is refused outright on both paths.
 *
 * Two paths, two hooks: crm_delete / the API go through beforeDelete (which has
 * no refusal channel, so only a throw stops them), while the GUI trash icon
 * calls the generated deleteOne() and never reaches beforeDelete at all.
 */
class GridRunDeleteGuardTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (GridRunQuery::create()->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)->find() as $r) {
            $r->setStatus('Halted');
            $r->save();
        }
        $c = ConfigQuery::create()->findOneByConfig('gtbot_shared_budget_quote') ?? (new Config())->setConfig('gtbot_shared_budget_quote');
        $c->setValue('1000');
        $c->save();
        \App\ConfigPeer::clearInstancePool();
    }

    private function mkRun(string $status = 'Testnet'): GridRun
    {
        $r = new GridRun();
        $r->setLabel(static::uniq('delguard'));
        $r->setSymbol('BTCUSDT');
        $r->setStatus($status);
        $r->setAlgo('Grid');
        $r->setSimulated(true);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(5);
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('200');
        $r->setDeployPct(100);
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('200');
        $r->setMaxOrderQuote('60');
        $r->setDailyLossLimitQuote('20');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(30);
        $r->setRunUid(static::uniq('delguard'));
        $r->setLastTickAt(date('Y-m-d H:i:s', time() - 3600));
        $r->setLastPrice('150');
        $r->save();
        return $r;
    }

    /** same construction idiom as GridRunWrapperProfileTest */
    private function wrapper(): GridRunServiceWrapper
    {
        $request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
        $response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $args = [];
        return new GridRunServiceWrapper($request, $response, $args);
    }

    public function testBeforeDeleteThrowsForALiveRun(): void
    {
        $r = $this->mkRun('Testnet');
        $messages = [];
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/gtbot_purge_run/');
        $this->wrapper()->beforeDelete($r, [], $messages);
    }

    public function testBeforeDeleteThrowsEvenForAnArchivedRun(): void
    {
        // the important one: crm_delete on a tidy, archived, flat run must
        // still be refused, because only purge() seals the wallet first
        $r = $this->mkRun('Testnet');
        RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'archived');
        $r->reload();
        $this->assertSame('Done', (string) $r->getStatus());

        $messages = [];
        $threw = false;
        try {
            $this->wrapper()->beforeDelete($r, [], $messages);
        } catch (\RuntimeException $e) {
            $threw = true;
            $this->assertStringContainsString('seals the shared paper-wallet ledger', $e->getMessage());
        }
        $this->assertTrue($threw, 'an archived run is still not raw-deletable');
        $this->assertNotEmpty($messages, 'the refusal is also reported through $messages');
        $this->assertNotNull(GridRunQuery::create()->findPk((int) $r->getIdGridRun()));
    }

    public function testPurgeItselfIsAllowedThroughTheSameGuard(): void
    {
        $r = $this->mkRun('Testnet');
        RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'archived');
        $r->reload();
        $id = (int) $r->getIdGridRun();

        $res = RunLifecycle::purge($r, 'cleanup', true, static fn (string $a): string => '');
        $this->assertTrue($res['ok'], $res['message']);
        $this->assertNull(GridRunQuery::create()->findPk($id), 'the re-entrancy flag must let purge through');
    }

    // ── status guard ────────────────────────────────────────────────────

    public function testBeforeSaveRefusesHandWrittenDone(): void
    {
        $r = $this->mkRun('Testnet');
        $data = ['Status' => 'Done'];
        $messages = '';
        $ext = [];
        $error = null;
        $this->wrapper()->beforeSave($r, $data, false, $messages, $ext, $error);

        $this->assertNotEmpty($ext, 'archiving by hand must be refused');
        $this->assertStringContainsString('gtbot_retire_run', array_key_first($ext));
    }

    public function testBeforeSaveRefusesHandWrittenRetiring(): void
    {
        $r = $this->mkRun('Testnet');
        $data = ['Status' => 'Retiring'];
        $messages = '';
        $ext = [];
        $error = null;
        $this->wrapper()->beforeSave($r, $data, false, $messages, $ext, $error);
        $this->assertNotEmpty($ext);
    }

    public function testBeforeSaveRefusesResurrectingAnArchivedRun(): void
    {
        $r = $this->mkRun('Testnet');
        RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'archived');
        $r->reload();

        $data = ['Status' => 'Live'];
        $messages = '';
        $ext = [];
        $error = null;
        $this->wrapper()->beforeSave($r, $data, false, $messages, $ext, $error);

        $this->assertNotEmpty($ext, 'a retired run stays retired');
        $this->assertStringContainsString('stays retired', array_key_first($ext));
    }

    public function testBeforeSaveStillAllowsOrdinaryStatusChanges(): void
    {
        $r = $this->mkRun('Testnet');
        $data = ['Status' => 'Halted'];
        $messages = '';
        $ext = [];
        $error = null;
        $this->wrapper()->beforeSave($r, $data, false, $messages, $ext, $error);
        $this->assertSame([], $ext, 'the guard must only cover Done/Retiring');
    }

    public function testLifecycleTransitionsAreNotBlockedByTheStatusGuard(): void
    {
        // retire() writes Done through the same wrapper path in the GUI case;
        // the re-entrancy flag is what lets it through.
        $r = $this->mkRun('Testnet');
        $res = RunLifecycle::retire($r, RunLifecycle::EXIT_MINIMIZE, 'archived');
        $this->assertTrue($res['ok'], $res['message']);
        $r->reload();
        $this->assertSame('Done', (string) $r->getStatus());
    }
}
