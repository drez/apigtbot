<?php

namespace Tests\Custom\Bot;

use ApiGoat\Audit\AuditContext;
use App\GridRun;
use App\GridRunAuditQuery;
use Tests\Builder\Support\DbTestCase;

/**
 * `add_audit` on grid_run: who changed a run knob, when, and from where.
 *
 * The behavior is framework-side (goatcheese Parameters/add_audit.php splices
 * the capture into BaseGridRun::save()); what this pins is the PROJECT's end
 * of it — that the column list is live on grid_run, that an ordinary save
 * records the change with usable values, and that an unaudited column does
 * NOT. That last one is the whole reason the list form was chosen over
 * `add_audit: true`: the daemon rewrites last_tick_at and engine_state every
 * 5 s on every run, and auditing those would bury the operator knobs under
 * ~17k rows a day.
 */
class GridRunAuditTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Static process state: another test (or the daemon boot marker) may
        // have pinned a source. The write path under phpunit is a CLI one.
        AuditContext::$source = null;
    }

    protected function tearDown(): void
    {
        AuditContext::$source = null;
        parent::tearDown();
    }

    private function mkRun(): GridRun
    {
        $r = new GridRun();
        $r->setLabel(static::uniq('audit'));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('DryRun');
        $r->setAlgo('Grid');
        $r->setProfile('Balanced');
        $r->setSimulated(true);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(5);
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('300');
        $r->setDeployPct(100);
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('300');
        $r->setMaxOrderQuote('100');
        $r->setDailyLossLimitQuote('30');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(30);
        $r->setRunUid(static::uniq('au'));
        $r->save();

        return $r;
    }

    /** @return list<array{field:string,from:?string,to:?string,actor:?string,source:string}> */
    private function history(GridRun $run): array
    {
        $out = [];
        foreach (GridRunAuditQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->orderByIdGridRunAudit()
            ->find() as $row) {
            $out[] = [
                'field'  => (string) $row->getField(),
                'from'   => $row->getValueFrom(),
                'to'     => $row->getValueTo(),
                'actor'  => $row->getActor(),
                'source' => (string) $row->getSource(),
            ];
        }

        return $out;
    }

    public function testAStatusChangeIsRecordedWithBothValues(): void
    {
        $run = $this->mkRun();
        $run->setStatus('Halted');
        $run->save();

        $history = $this->history($run);

        // The INSERT records the record coming into being, then the UPDATE
        // records the one column that actually changed.
        $this->assertSame(
            [['field' => '*', 'to' => 'created'], ['field' => 'status', 'to' => 'Halted']],
            array_map(static fn (array $r): array => ['field' => $r['field'], 'to' => $r['to']], $history)
        );
        $this->assertSame('DryRun', $history[1]['from'], 'value_from must be the enum LABEL on disk, not its ordinal');
        $this->assertNull($history[1]['actor'], 'a CLI write has no authenticated user');
        $this->assertSame('cli', $history[1]['source']);
    }

    public function testTheOperatorKnobsAreAudited(): void
    {
        $run = $this->mkRun();
        $run->setKillSwitch(true);
        $run->setBudgetQuote('450');
        $run->setDeployPct(50);
        $run->save();

        $fields = array_column($this->history($run), 'field');

        // Order is the order the columns are DECLARED in add_audit (diff()
        // walks the emitted list), not the order they were set in.
        $this->assertSame(['*', 'budget_quote', 'deploy_pct', 'kill_switch'], $fields);
    }

    public function testTheDaemonsPerTickColumnsAreNotAudited(): void
    {
        $run = $this->mkRun();
        $run->setLastTickAt(date('Y-m-d H:i:s'));
        $run->setEngineState('{"algo":"Grid"}');
        $run->setLastPrice('123.45');
        $run->save();

        $this->assertSame(['*'], array_column($this->history($run), 'field'),
            'the 5 s tick columns must never write history — that is why add_audit carries a LIST');
    }

    public function testHistoryIsStampedOnThePhpClockLikeEveryOtherTable(): void
    {
        // AuditContext used to bind MySQL NOW(), which is the DATABASE
        // server's wall clock: on this box PHP runs UTC and MySQL -0400, so
        // every history row landed 4 h before the bot_event and last_tick_at
        // rows it has to be read next to. If this fails, the vendored
        // apigoat/runtime has been rolled back under us (a plain `gc build`
        // does that — see the runtime pin) and DarkArmSweep's event leg is
        // comparing two different clocks again.
        $run = $this->mkRun();
        $run->setStatus('Halted');
        $run->save();

        $stamp = \App\GridRunAuditQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->orderByIdGridRunAudit(\Criteria::DESC)
            ->findOne()
            ->getDateCreation('Y-m-d H:i');

        $this->assertSame(date('Y-m-d H:i'), $stamp);
    }

    public function testADaemonStampsItsWritesAsSuch(): void
    {
        AuditContext::asDaemon();
        $run = $this->mkRun();
        $run->setStatus('Halted');
        $run->save();

        $history = $this->history($run);

        $this->assertSame('daemon', $history[1]['source'], 'Daemon::boot() marks the process, so its writes are attributable');
    }

    // ── append-only ─────────────────────────────────────────────────────

    /**
     * child_table_read_only only takes the buttons off the admin's child
     * list: the generated JSON API still ROUTED POST and DELETE on
     * grid_run_audit[/{id}], and the MCP write tools reach the same service.
     * A history anybody can edit cannot answer the one question it exists
     * for, so the write is now refused at the model — the layer every path
     * goes through.
     */
    public function testAHistoryRowCannotBeCreatedByHand(): void
    {
        $run = $this->mkRun();
        $row = new \App\GridRunAudit();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setField('status');
        $row->setValueTo('Live');

        $this->expectException(\PropelException::class);
        $this->expectExceptionMessage('audit history is append-only');
        $row->save();
    }

    public function testAHistoryRowCannotBeEditedOrDeleted(): void
    {
        $run = $this->mkRun();
        $run->setStatus('Halted');
        $run->save();

        $row = GridRunAuditQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByField('status')
            ->findOne();
        $this->assertNotNull($row, 'the status change wrote a row to edit');

        $row->setValueTo('Live');
        try {
            $row->save();
            $this->fail('an audit row was rewritten');
        } catch (\PropelException $e) {
            $this->assertStringContainsString('append-only', $e->getMessage());
        }

        try {
            $row->delete();
            $this->fail('an audit row was deleted');
        } catch (\PropelException $e) {
            $this->assertStringContainsString('append-only', $e->getMessage());
        }

        // and it is still on disk, unchanged
        \App\GridRunAuditPeer::clearInstancePool();
        $fresh = GridRunAuditQuery::create()->findPk((int) $row->getIdGridRunAudit());
        $this->assertNotNull($fresh);
        $this->assertSame('Halted', (string) $fresh->getValueTo());
    }

    /** The audited table itself must keep saving — and keep writing history. */
    public function testTheAuditedTableIsUnaffected(): void
    {
        $run = $this->mkRun();
        $before = count($this->history($run));
        $run->setStatus('Halted');
        $run->save();

        $history = $this->history($run);
        $this->assertCount($before + 1, $history, 'the parent save still writes its row');
        $this->assertSame('status', $history[1]['field']);
        $this->assertSame('Halted', $history[1]['to']);
    }

    /**
     * The GUI/API/MCP half: the generated service answers with the framework's
     * ordinary refusal instead of letting the model throw become a 500. Read
     * off the generated source, because halt() ends the request and cannot be
     * driven from a unit test.
     */
    public function testTheGeneratedServiceRefusesEveryWriteActionBeforeItWrites(): void
    {
        $src = file_get_contents(__DIR__ . '/../../../src/App/Services/Built/GridRunAuditService.php');
        $this->assertIsString($src);

        $refusal = 'audit history is append-only';
        $this->assertSame(3, substr_count($src, $refusal), 'create, update and delete');
        // the apostrophe must NOT be backslash-escaped: the literal is
        // double-quoted, so addslashes() would ship `table\'s` to the operator
        $this->assertStringContainsString("audited table's save", $src);

        foreach (['$obj->delete();', '$e->save();'] as $write) {
            $this->assertLessThan(
                strpos($src, $write),
                strpos($src, $refusal),
                "the refusal must come before $write"
            );
        }
    }
}
