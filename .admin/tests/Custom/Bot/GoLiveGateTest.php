<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\BudgetGuard;
use App\Domains\Bot\GoLiveGate;
use App\GridRunQuery;
use App\RegimeEpisode;
use App\WalletNav;
use App\WalletNavQuery;
use Tests\Builder\Support\DbTestCase;

/** The project's own go-live bar (≥ 4 paper weeks ahead of flat USDT, a trend
 *  leg seen, a clean fleet) read off the ledgers — see GoLiveGate. */
class GoLiveGateTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        WalletNavQuery::create()->deleteAll();
        \App\RegimeEpisodeQuery::create()->deleteAll();
        foreach (GridRunQuery::create()->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)->find() as $r) {
            $r->setStatus('Halted');
            $r->save();
        }
    }

    private function nav(float $daysAgo, string $equity, string $mode = 'sim', string $budget = '1000'): void
    {
        $r = new WalletNav();
        $r->setMode($mode);
        $r->setEquityQuote($equity);
        $r->setBudgetQuote($budget);
        $r->setRefSymbol('BTCUSDT');
        $r->setRefPrice('100');
        $r->save();
        // backdate AFTER insert — the ORM stamps date_creation = NOW on insert
        \Propel::getConnection()->prepare('UPDATE wallet_nav SET date_creation = ? WHERE id_wallet_nav = ?')
            ->execute([date('Y-m-d H:i:s', time() - (int) ($daysAgo * 86400)), (int) $r->getIdWalletNav()]);
        \App\WalletNavPeer::clearInstancePool();
    }

    private function episode(string $engaged): void
    {
        $e = new RegimeEpisode();
        $e->setSymbol('BTCUSDT');
        $e->setVerdict('TREND_UP');
        $e->setOpenedAt(date('Y-m-d H:i:s', time() - 5 * 86400));
        $e->setClosedAt(date('Y-m-d H:i:s', time() - 3 * 86400));
        $e->setPriceOpen('100');
        $e->setEngagedPctTw($engaged);
        $e->save();
    }

    /** @return array<string, bool> */
    private function verdicts(): array
    {
        $out = [];
        foreach (GoLiveGate::evaluate()['gates'] as $g) {
            $out[$g['gate']] = $g['pass'];
        }
        return $out;
    }

    public function testAnEmptyLedgerIsNotReady(): void
    {
        $res = GoLiveGate::evaluate();
        $this->assertFalse($res['ready']);
        $v = $this->verdicts();
        $this->assertFalse($v['soak_length']);
        $this->assertFalse($v['beats_flat_usdt']);
        $this->assertFalse($v['trend_leg_seen']);
    }

    public function testFourWinningWeeksWithAnEngagedLegPassTheSoak(): void
    {
        $this->nav(27.9, '1000');
        $this->nav(14, '990');
        $this->nav(0, '1012');
        $this->episode('62.5');
        $v = $this->verdicts();
        $this->assertTrue($v['soak_length']);
        $this->assertTrue($v['beats_flat_usdt']);
        $this->assertTrue($v['drawdown_inside_floor']);
        $this->assertTrue($v['trend_leg_seen']);
    }

    public function testAShortWinningSoakDoesNotCount(): void
    {
        $this->nav(10, '1000');
        $this->nav(0, '1050');
        $v = $this->verdicts();
        $this->assertFalse($v['soak_length']);
        $this->assertFalse($v['beats_flat_usdt'], 'ten good days are not the bar');
    }

    public function testLosingToFlatUsdtFails(): void
    {
        $this->nav(27.9, '1000');
        $this->nav(0, '979');
        $this->assertFalse($this->verdicts()['beats_flat_usdt']);
    }

    public function testALegTheArmSatOutIsNotALegSeen(): void
    {
        $this->episode('0');
        $this->assertFalse($this->verdicts()['trend_leg_seen']);
    }

    public function testRealNavDoesNotStandInForThePaperSoak(): void
    {
        $this->nav(27.9, '1000', 'real');
        $this->nav(0, '1100', 'real');
        $this->assertFalse($this->verdicts()['beats_flat_usdt']);
    }

    /**
     * A GATE THAT CANNOT TELL MUST NOT PASS (2026-09-21, round 3).
     *
     * A budget change whose money never showed up inside the window leaves
     * `flows_pending` on the report: the return on either side of it is
     * unknowable — book it and a 1 USDT drift prices a 300 USDT flow, leave
     * it and the deposit reads as performance. Either way the soak is not
     * certified off that window. WAS: the gate read nav_return_pct and said
     * PASS.
     */
    public function testAnUnsettledBudgetChangeCannotCertifyTheSoak(): void
    {
        $this->nav(27.9, '1000', 'sim', '1000');
        $this->nav(14, '1010', 'sim', '1000');
        // the operator raises the pool on the last point; the money has not
        // reached the wallet yet (each daemon re-seeds at its next reboot)
        $this->nav(0, '1012', 'sim', '1300');
        $this->episode('62.5');

        $v = $this->verdicts();
        $this->assertTrue($v['soak_length'], 'the window itself is long enough');
        $this->assertFalse($v['beats_flat_usdt'], 'but nothing can be certified across an unsettled flow');
        $detail = '';
        foreach (GoLiveGate::evaluate()['gates'] as $g) {
            if ($g['gate'] === 'beats_flat_usdt') {
                $detail = $g['detail'];
            }
        }
        $this->assertStringContainsString('cannot tell', $detail, 'and it says so, rather than printing a number it made up');
    }
}
