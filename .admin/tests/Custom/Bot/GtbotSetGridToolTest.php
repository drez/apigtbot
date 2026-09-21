<?php

namespace Tests\Custom\Bot;

use ApiGoat\Sessions\AuthySession;
use App\BotEventQuery;
use App\GridRun;
use ApiGoat\Mcp\ToolError;
use App\Mcp\Tools\GtbotSetGridTool;
use Tests\Builder\Support\DbTestCase;

class GtbotSetGridToolTest extends DbTestCase
{
    private GridRun $run;

    protected function setUp(): void
    {
        parent::setUp();
        $r = new GridRun();
        $r->setLabel(static::uniq('setgrid'));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setPLow('60000');
        $r->setPHigh('72000');
        $r->setNLevels(12);
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('1000');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('1200');
        $r->setMaxOrderQuote('200');
        $r->setDailyLossLimitQuote('50');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(30);
        $r->setRunUid(static::uniq('setgrid'));
        $r->setLastTickAt(date('Y-m-d H:i:s'));
        $r->save();
        // mode() is config-driven and prod seeds it to mechanical, which refuses
        // every geometry change. These cases are written against routine mode,
        // so pin it; testMechanicalModeRefusesAGeometryChange overrides it.
        $this->setMode(\App\Domains\Bot\MechanicalRefit::MODE_ROUTINE);
        $this->run = $r;
    }

    private function tool(?string $price = '66000'): GtbotSetGridTool
    {
        // inject a fixed market price so the test needs no network
        return new GtbotSetGridTool($price);
    }

    private function decode(array $r): array
    {
        return json_decode($r['content'][0]['text'], true);
    }

    private function session(): AuthySession
    {
        return $this->createMock(AuthySession::class);
    }

    private function setMode(string $mode): void
    {
        $c = \App\ConfigQuery::create()->findOneByConfig(\App\Domains\Bot\MechanicalRefit::CONFIG_MODE)
            ?? (new \App\Config())->setConfig(\App\Domains\Bot\MechanicalRefit::CONFIG_MODE);
        $c->setValue($mode);
        $c->save();
        // Config rows keep their in-memory value after a write, so the read
        // inside MechanicalRefit::mode() would serve the pooled one
        \App\ConfigPeer::clearInstancePool();
    }

    public function testMechanicalModeRefusesAGeometryChange(): void
    {
        $this->setMode('mechanical');
        try {
            $this->expectException(ToolError::class);
            $this->expectExceptionMessageMatches('/MECHANICAL/');
            $this->tool()->handle([
                'p_low' => '62000', 'p_high' => '70000', 'n_levels' => 14,
                'reason' => 'the routine trying to move the ladder',
            ], $this->session());
        } finally {
            $this->setMode('routine');
            \App\ConfigPeer::clearInstancePool();
        }
    }

    public function testMechanicalModeAcceptsADeployOnlyCallEvenOutsideTheRange(): void
    {
        $this->setMode('mechanical');
        try {
            // price 75000 is above the fixture's [60000, 72000]: a geometry
            // call would fail the bracket check; a deploy-only call passes
            $out = $this->decode($this->tool('75000')->handle([
                'p_low' => '60000', 'p_high' => '72000', 'n_levels' => 12,
                'deploy_pct' => 40,
                'reason' => 'deploy only — geometry is mechanical',
            ], $this->session()));
            $this->assertTrue($out['applied']);
            $this->run->reload();
            $this->assertSame(40, (int) $this->run->getDeployPct());
            $this->assertSame(12, (int) $this->run->getNLevels());
            // deploy-only mechanical calls keep their decision journal — deploy
            // is the routine's one remaining lever there, and the cron clock
            // filters by source=Cron so the stamped row cannot restart it.
            $d = \App\BotDecisionQuery::create()
                ->filterByIdGridRun((int) $this->run->getIdGridRun())
                ->filterBySource('Claude')
                ->orderByIdBotDecision(\Criteria::DESC)
                ->findOne();
            $this->assertNotNull($d, 'deploy-only calls keep their decision receipt');
        } finally {
            $this->setMode('routine');
            \App\ConfigPeer::clearInstancePool();
        }
    }

    public function testAppliesGeometryToActiveRun(): void
    {
        $out = $this->decode($this->tool()->handle([
            'p_low' => '62000', 'p_high' => '70000', 'n_levels' => 14,
            'reason' => 'uptrend, RSI 58, ATR 1.2% — re-centering higher',
        ], $this->session()));
        $this->assertTrue($out['applied']);
        $this->run->reload();
        $this->assertSame(0, bccomp((string) $this->run->getPLow(), '62000', 2));
        $this->assertSame(0, bccomp((string) $this->run->getPHigh(), '70000', 2));
        $this->assertSame(14, (int) $this->run->getNLevels());
        // a refit_by_claude event carries the reason (also Telegrams / shows in GUI)
        $ev = BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('refit_by_claude')
            ->findOne();
        $this->assertNotNull($ev);
        $this->assertStringContainsString('uptrend', (string) $ev->getMessage());
    }

    public function testDeployPctIsAppliedAndJournaled(): void
    {
        $out = $this->decode($this->tool()->handle([
            'p_low' => '62000', 'p_high' => '70000', 'n_levels' => 14,
            'deploy_pct' => 40,
            'reason' => 'not fit: 1d down — quarter-size the exposure',
        ], $this->session()));
        $this->assertTrue($out['applied']);
        $this->run->reload();
        $this->assertSame(40, (int) $this->run->getDeployPct());
        // the strategy-test log: the decision row records how much was deployed
        $d = \App\BotDecisionQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->orderByIdBotDecision(\Criteria::DESC)
            ->findOne();
        $this->assertSame(40, (int) $d->getDeployPct());
        // decision receipt: requested geometry + (empty) clamp trail + candidate verdict
        $req = json_decode((string) $d->getRequestedJson(), true);
        $this->assertSame(['62000', '70000', 14, 40], [$req['p_low'], $req['p_high'], $req['n_levels'], $req['deploy_pct']]);
        $this->assertSame([], json_decode((string) $d->getClampsJson(), true), 'no gate fired → empty trail');
        $this->assertContains((string) $d->getCandidateDelta(), ['same', 'deviated', 'none']);
        $brief = json_decode((string) $d->getBriefJson(), true);
        $this->assertArrayHasKey('regime', $brief);
        $this->assertSame(['clamps' => [], 'candidate_delta' => (string) $d->getCandidateDelta()], $out['receipt']);
    }

    public function testDeployPctOutOfRangeIsRejected(): void
    {
        $out = $this->decode($this->tool()->handle([
            'p_low' => '62000', 'p_high' => '70000', 'n_levels' => 14,
            'deploy_pct' => 5, // below the 10% floor — a near-zero ladder is a config error
            'reason' => 'x',
        ], $this->session()));
        $this->assertFalse($out['applied']);
        $this->assertNotEmpty($out['errors']);
        $this->run->reload();
        $this->assertSame(100, (int) $this->run->getDeployPct(), 'nothing written on a rejected call');
    }

    public function testDryRunWritesNothing(): void
    {
        $before = (string) $this->run->getPLow();
        $out = $this->decode($this->tool()->handle([
            'p_low' => '62000', 'p_high' => '70000', 'n_levels' => 14,
            'reason' => 'x', 'dry_run' => true,
        ], $this->session()));
        $this->assertFalse($out['applied']);
        $this->assertArrayHasKey('preview', $out);
        $this->run->reload();
        $this->assertSame(0, bccomp((string) $this->run->getPLow(), $before, 2));
    }

    public function testRejectsFeeNegativeGeometry(): void
    {
        $out = $this->decode($this->tool()->handle([
            'p_low' => '65900', 'p_high' => '66100', 'n_levels' => 40, 'reason' => 'x',
        ], $this->session()));
        $this->assertFalse($out['applied']);
        $this->assertStringContainsStringIgnoringCase('fee', json_encode($out['errors']));
    }

    public function testRejectsRangeNotBracketingPrice(): void
    {
        // price 66000 but range entirely below it → grid would sit idle
        $out = $this->decode($this->tool('66000')->handle([
            'p_low' => '50000', 'p_high' => '60000', 'n_levels' => 12, 'reason' => 'x',
        ], $this->session()));
        $this->assertFalse($out['applied']);
        $this->assertStringContainsStringIgnoringCase('bracket', json_encode($out['errors']));
    }

    /** Seed (or update) the stored 4h market summary the regime gate reads. */
    private function seedRegime(string $trend, string $adx, int $ageSeconds = 0): void
    {
        $row = \App\MarketSummaryQuery::create()
            ->filterBySymbol('BTCUSDT')->filterByTf('4h')->findOne() ?? new \App\MarketSummary();
        $row->setSymbol('BTCUSDT');
        $row->setTf('4h');
        $row->setTrend($trend);
        $row->setAdx14($adx);
        $row->setComputedAt(date('Y-m-d H:i:s', time() - $ageSeconds));
        $row->save();
    }

    public function testHostileRegimeCapsDeployPct(): void
    {
        $this->seedRegime('down', '35');
        $out = $this->decode($this->tool()->handle([
            'p_low' => '62000', 'p_high' => '70000', 'n_levels' => 14,
            'deploy_pct' => 100,
            'reason' => 'x',
        ], $this->session()));
        $this->assertTrue($out['applied']);
        $this->assertSame(25, $out['preview']['deploy_pct']);
        $this->assertStringContainsString('hostile regime', (string) ($out['regime_gate'] ?? ''));
        $this->run->reload();
        $this->assertSame(25, (int) $this->run->getDeployPct(), 'sweep gate: down|adx>=30 caps deploy at 25');
        // the clamp is on the record: requested 100, regime_gate → 25
        $d = \App\BotDecisionQuery::create()->filterByIdGridRun((int) $this->run->getIdGridRun())->orderByIdBotDecision(\Criteria::DESC)->findOne();
        $clamps = json_decode((string) $d->getClampsJson(), true);
        $this->assertCount(1, $clamps);
        $this->assertSame(['regime_gate', 100, 25], [$clamps[0]['limit'], $clamps[0]['before'], $clamps[0]['after']]);
        $this->assertSame(100, json_decode((string) $d->getRequestedJson(), true)['deploy_pct']);
        $this->assertSame('regime_gate', $out['receipt']['clamps'][0]['limit']);
    }

    public function testFriendlyRegimeDoesNotCap(): void
    {
        $this->seedRegime('sideways', '15');
        $out = $this->decode($this->tool()->handle([
            'p_low' => '62000', 'p_high' => '70000', 'n_levels' => 14,
            'deploy_pct' => 100,
            'reason' => 'x',
        ], $this->session()));
        $this->assertTrue($out['applied']);
        $this->run->reload();
        $this->assertSame(100, (int) $this->run->getDeployPct());
        $this->assertArrayNotHasKey('regime_gate', $out);
    }

    public function testExplicitOverrideBypassesRegimeGate(): void
    {
        $this->seedRegime('down', '35');
        $out = $this->decode($this->tool()->handle([
            'p_low' => '62000', 'p_high' => '70000', 'n_levels' => 14,
            'deploy_pct' => 100,
            'override_regime_gate' => true,
            'reason' => 'capitulation wick reclaimed — deploying into the reversal deliberately',
        ], $this->session()));
        $this->assertTrue($out['applied']);
        $this->run->reload();
        $this->assertSame(100, (int) $this->run->getDeployPct(), 'explicit override keeps the requested size');
        // the override is auditable, not silent: the response still reports the hostile read
        $this->assertStringContainsString('overridden', strtolower((string) ($out['regime_gate'] ?? '')));
    }

    public function testOverrideInFriendlyRegimeIsANoOp(): void
    {
        $this->seedRegime('sideways', '15');
        $out = $this->decode($this->tool()->handle([
            'p_low' => '62000', 'p_high' => '70000', 'n_levels' => 14,
            'deploy_pct' => 100,
            'override_regime_gate' => true,
            'reason' => 'x',
        ], $this->session()));
        $this->assertTrue($out['applied']);
        $this->run->reload();
        $this->assertSame(100, (int) $this->run->getDeployPct());
        $this->assertArrayNotHasKey('regime_gate', $out, 'nothing to report when the gate would not have fired');
    }

    public function testStaleRegimeDataFailsOpen(): void
    {
        $this->seedRegime('down', '35', 3 * 3600); // 3h old — collector is down
        $out = $this->decode($this->tool()->handle([
            'p_low' => '62000', 'p_high' => '70000', 'n_levels' => 14,
            'deploy_pct' => 100,
            'reason' => 'x',
        ], $this->session()));
        $this->assertTrue($out['applied']);
        $this->run->reload();
        $this->assertSame(100, (int) $this->run->getDeployPct(), 'stale signal must not strand the routine at minimum size');
    }

    public function testRequiresReason(): void
    {
        $this->expectException(\ApiGoat\Mcp\ToolError::class);
        $this->tool()->handle(['p_low' => '62000', 'p_high' => '70000', 'n_levels' => 14], $this->session());
    }
}
