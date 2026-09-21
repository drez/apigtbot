<?php

namespace Tests\Custom\Bot;

use App\BotDecision;
use App\Domains\Bot\DecisionScorer;
use App\GridRun;
use App\TradeCycle;
use PHPUnit\Framework\TestCase;

/**
 * scorePending() must judge each run against ITS OWN price (prod 2026-07-23:
 * a BNB decision scored against the BTC price → bogus Loss fed back into the
 * routine), and must only start a decision's evaluation clock when the daemon
 * actually APPLIED its geometry — a decision that never took effect and was
 * replaced by a newer one is Superseded, not Win/Loss on the old grid's trades.
 */
class DecisionScorerTest extends TestCase
{
    private static bool $booted = false;

    public static function setUpBeforeClass(): void
    {
        if (self::$booted) {
            return;
        }
        $admin = dirname(__DIR__, 3);
        require_once $admin . '/vendor/autoload.php';
        (new \Ahc\Env\Loader())->load($admin . '/.env');
        if (!defined('_AUTH_VAR')) {
            require $admin . '/config/Built/config.php';
        }
        if (!\Propel::isInit()) {
            require $admin . '/config/Built/propel.php';
        }
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION[_AUTH_VAR] = new \ApiGoat\Sessions\AuthySession();
        self::$booted = true;
    }

    protected function setUp(): void
    {
        \Propel::getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function makeRun(string $symbol, string $lastPrice): GridRun
    {
        $r = new GridRun();
        $r->setLabel('scorer-' . $symbol . '-' . bin2hex(random_bytes(4)));
        $r->setSymbol($symbol);
        $r->setStatus('Testnet');
        $r->setPLow('1');
        $r->setPHigh('2');
        $r->setNLevels(4);
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('1000');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setLastPrice($lastPrice);
        $r->save();
        return $r;
    }

    private function makeDecision(GridRun $run, string $pLow, string $pHigh, int $hoursAgo, ?int $appliedHoursAgo): BotDecision
    {
        $d = new BotDecision();
        $d->setIdGridRun((int) $run->getIdGridRun());
        $d->setSource('Claude');
        $d->setPLow($pLow);
        $d->setPHigh($pHigh);
        $d->setNLevels(5);
        $d->setReason('test');
        $d->setPriceAt($run->getLastPrice());
        $d->setRealizedBefore('0');
        $d->setEvalStatus('Pending');
        $d->setDateCreation(date('Y-m-d H:i:s', time() - $hoursAgo * 3600));
        if ($appliedHoursAgo !== null) {
            $d->setAppliedAt(date('Y-m-d H:i:s', time() - $appliedHoursAgo * 3600));
        }
        $d->save();
        return $d;
    }

    public function testScoresEachRunWithItsOwnPrice(): void
    {
        $btc = $this->makeRun('BTCUSDT', '65000');
        $bnb = $this->makeRun('BNBUSDT', '550');
        // BTC decision: price 65000 left its [60000, 63000] range → Loss
        $dBtc = $this->makeDecision($btc, '60000', '63000', 8, 7);
        // BNB decision: 550 is inside [540, 580] → Flat (not a bogus Loss
        // from being compared against the BTC price)
        $dBnb = $this->makeDecision($bnb, '540', '580', 8, 7);

        DecisionScorer::scorePending();

        $dBtc->reload();
        $dBnb->reload();
        $this->assertSame('Loss', (string) $dBtc->getVerdict());
        $this->assertSame('Flat', (string) $dBnb->getVerdict());
    }

    public function testSupersededUnappliedDecisionIsScoredSuperseded(): void
    {
        $run = $this->makeRun('BTCUSDT', '65000');
        $older = $this->makeDecision($run, '64000', '66000', 9, null); // never applied
        $newer = $this->makeDecision($run, '64500', '66500', 8, null);

        DecisionScorer::scorePending();

        $older->reload();
        $newer->reload();
        $this->assertSame('Superseded', (string) $older->getVerdict(), 'replaced before it ever traded');
        $this->assertSame('Scored', (string) $older->getEvalStatus());
        $this->assertSame('Pending', (string) $newer->getEvalStatus(), 'latest queued geometry keeps waiting for the daemon');
    }

    public function testLatestUnappliedDecisionStaysPending(): void
    {
        $run = $this->makeRun('BTCUSDT', '65000');
        $d = $this->makeDecision($run, '64000', '66000', 9, null);

        DecisionScorer::scorePending();

        $d->reload();
        $this->assertSame('Pending', (string) $d->getEvalStatus());
    }

    public function testAppliedRecentlyIsNotScoredYet(): void
    {
        $run = $this->makeRun('BTCUSDT', '65000');
        $d = $this->makeDecision($run, '64000', '66000', 9, 1); // applied 1h ago < EVAL_AFTER

        DecisionScorer::scorePending();

        $d->reload();
        $this->assertSame('Pending', (string) $d->getEvalStatus(), 'evaluation clock starts at apply time');
    }

    public function testMeasuresCyclesSinceAppliedNotSinceDecision(): void
    {
        $run = $this->makeRun('BTCUSDT', '65000');
        $d = $this->makeDecision($run, '64000', '66000', 10, 7);

        // a cycle harvested BEFORE the geometry applied (old grid's trade)
        $c = new TradeCycle();
        $c->setIdGridRun((int) $run->getIdGridRun());
        $c->setLevelIdx(1);
        $c->setBuyPrice('64000');
        $c->setSellPrice('64500');
        $c->setQty('0.01');
        $c->setRealizedPnl('5');
        $c->setFeesTotal('0.1');
        $c->save();
        // backdate AFTER insert — the ORM stamps date_creation=NOW on insert
        $c->setDateCreation(date('Y-m-d H:i:s', time() - 9 * 3600));
        $c->save();

        DecisionScorer::scorePending();

        $d->reload();
        $this->assertSame('Flat', (string) $d->getVerdict(), 'old-grid harvest must not count as this decision\'s Win');
        $this->assertSame(0, (int) $d->getCyclesDelta());
    }

    public function testScoresCounterfactualAgainstPreviousAppliedGeometry(): void
    {
        $run = $this->makeRun('CFTUSDT', '100');
        $run->setBudgetQuote('1000');
        $run->save();
        // the geometry this decision replaced: wide, never fills on a ±4% wobble
        $this->makeDecision($run, '50', '150', 30, 20);
        $new = $this->makeDecision($run, '94', '106', 8, 7);
        $new->setNLevels(4);
        $new->save();
        // 1h tape (newest = computed_at = now): 7h of wobble since apply
        $row = new \App\MarketSummary();
        $row->setSymbol('CFTUSDT');
        $row->setTf('1h');
        $row->setComputedAt(date('Y-m-d H:i:s'));
        $row->setRecentCandles(json_encode(array_map(static fn ($c) => [$c, $c, $c], ['100', '96', '100', '96', '100', '96', '100', '96'])));
        $row->save();

        DecisionScorer::scorePending();

        $new->reload();
        $this->assertSame('Scored', (string) $new->getEvalStatus());
        $this->assertNotNull($new->getCounterfactualDelta());
        $this->assertGreaterThan(0, (float) $new->getCounterfactualDelta(), 'the tight refit out-earns the wide grid it replaced on the same tape');
        $this->assertSame('Flat', (string) $new->getVerdict(), 'no live cycles yet and in range; counterfactual favourable → not Worse');
    }
}
