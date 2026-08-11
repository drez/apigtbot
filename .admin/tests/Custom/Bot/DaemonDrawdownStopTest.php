<?php

namespace Tests\Custom\Bot;

use App\BotEventQuery;
use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\Daemon;
use App\Domains\Bot\EventLog;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Gateway\PaperGateway;
use App\GridRun;
use App\GridRunQuery;
use PHPUnit\Framework\TestCase;

/**
 * Global drawdown floor: equity under budget×(1−pct/100) held
 * BREACH_CONFIRM_TICKS ticks kills EVERY active run (buys canceled,
 * inventory held). Paper flow: buys fill, price collapses, sim-wallet
 * equity honestly sinks under the floor.
 */
class DaemonDrawdownStopTest extends TestCase
{
    private static bool $booted = false;
    private GridRun $run;
    private GridRun $bystander;
    private string $tick = '120';

    public static function setUpBeforeClass(): void
    {
        if (self::$booted) {
            return;
        }
        $admin = dirname(__DIR__, 3);
        if (!is_file($admin . '/config/Built/config.php')) {
            self::markTestSkipped('project not built');
        }
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
        $con = \Propel::getConnection();
        $con->beginTransaction();
        // this test owns the world: no stray active runs (status is a Propel
        // ENUM stored as an int — neutralize via the ORM, not raw SQL), no
        // pre-epoch simulated fills leaking into the derived wallet
        foreach (GridRunQuery::create()->filterByStatus('Done', \Criteria::NOT_EQUAL)->find() as $r) {
            $r->setStatus('Done');
            $r->save();
        }
        $this->setConfig('gtbot_shared_budget_quote', '1000');
        $this->setConfig('gtbot_max_drawdown_pct', '25'); // floor 750
        $this->setConfig('gtbot_sim_wallet_epoch', date('Y-m-d H:i:s'));

        $this->run = $this->mkRun('BTCUSDT', '800');      // the traded run
        $this->bystander = $this->mkRun('ETHUSDT', '100'); // proves fan-out
        $this->tick = '120';
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function setConfig(string $key, string $v): void
    {
        $c = ConfigQuery::create()->findOneByConfig($key) ?? (new Config())->setConfig($key);
        $c->setValue($v);
        $c->save();
    }

    private function mkRun(string $symbol, string $budget): GridRun
    {
        $r = new GridRun();
        $r->setLabel('ddstop-' . bin2hex(random_bytes(4)));
        $r->setSymbol($symbol);
        $r->setStatus('Live');
        $r->setSimulated(true);
        $r->setPLow('100');
        $r->setPHigh('140');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote($budget);
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setMaxUnrealizedLossQuote('10000'); // park the per-run stop far away
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid(substr(bin2hex(random_bytes(6)), 0, 10));
        $r->save();
        return $r;
    }

    private function makeDaemon(): Daemon
    {
        $transport = function (string $method, string $url, array $headers, ?string $body): array {
            if (str_contains($url, '/api/v3/ticker/price')) {
                return ['status' => 200, 'headers' => [], 'body' => json_encode(['price' => $this->tick])];
            }
            if (str_contains($url, '/api/v3/exchangeInfo')) {
                return ['status' => 200, 'headers' => [], 'body' => json_encode(['symbols' => [[
                    'symbol' => 'BTCUSDT',
                    'filters' => [
                        ['filterType' => 'PRICE_FILTER', 'tickSize' => '0.01'],
                        ['filterType' => 'LOT_SIZE', 'stepSize' => '0.00001', 'minQty' => '0.00001'],
                        ['filterType' => 'NOTIONAL', 'minNotional' => '5'],
                    ],
                ]]])];
            }
            throw new \RuntimeException("unexpected HTTP call in paper mode: $url");
        };
        $public = new BinanceGateway('https://api.binance.com', '', '', $transport);
        $gw = new PaperGateway($public, $this->run, (string) $this->run->getFeePct());
        return new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
    }

    /** Boot, place the ladder at 120, collapse to 30 so both buys fill and
     *  the base inventory is nearly worthless: equity ≈ 599.6 + 3.76×30
     *  ≈ 712 < 750 floor. Fills are processed BEFORE the risk checks within
     *  a tick, so the collapse tick itself is breach tick 1. */
    private function driveUnderFloor(Daemon $d): void
    {
        $d->boot();
        $d->tick();          // buys at 100 and 113.33 (800×2/4 = 200 each)
        $this->tick = '30';
        $d->tick();          // both fill → breach tick 1 (Warn drawdown_breach)
    }

    public function testConfirmedBreachKillsAllActiveRuns(): void
    {
        $d = $this->makeDaemon();
        $this->driveUnderFloor($d);
        $d->tick();          // breach tick 2
        $this->run->reload();
        $this->assertFalse((bool) $this->run->getKillSwitch(), 'wick guard: 2 ticks must not trip');
        $d->tick();          // breach tick 3 — confirmed

        $this->run->reload();
        $this->bystander->reload();
        $this->assertTrue((bool) $this->run->getKillSwitch(), 'tripping run must be killed');
        $this->assertTrue((bool) $this->bystander->getKillSwitch(), 'ALL active runs must be killed');
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('drawdown_stop')->count());
        $this->assertGreaterThan(0, BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('drawdown_breach')->count());
    }

    public function testRecoveryBeforeConfirmResetsTheCounter(): void
    {
        $d = $this->makeDaemon();
        $this->driveUnderFloor($d); // breach tick 1
        $d->tick();          // breach tick 2
        // recovery price must stay BELOW the posted sells (lowest ≈113.33,
        // they'd fill and change the wallet) while clearing the floor:
        // equity at 100 ≈ 599.6 + 3.76×100 ≈ 976 > 750, nothing fills
        $this->tick = '100';
        $d->tick();          // recovery — counter must reset
        $this->tick = '30';
        $d->tick();          // breach tick 1 again
        $d->tick();          // breach tick 2 again

        $this->run->reload();
        $this->bystander->reload();
        $this->assertFalse((bool) $this->run->getKillSwitch());
        $this->assertFalse((bool) $this->bystander->getKillSwitch());
        $this->assertSame(0, BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('drawdown_stop')->count());
    }
}
