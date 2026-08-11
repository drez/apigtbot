<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\Daemon;
use App\Domains\Bot\EventLog;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Gateway\PaperGateway;
use App\GridRun;
use PHPUnit\Framework\TestCase;

/**
 * All runs share ONE wallet: their budget slices must not sum above the
 * shared budget. checkBudgetInvariant() is advisory (Alert) — it fires at
 * boot and after a successful refit apply. Mirrors DaemonPaperFlowTest's
 * boot pattern (PaperGateway + fake transport) against the real project DB
 * inside a rolled-back transaction.
 */
class BudgetInvariantTest extends TestCase
{
    private static bool $booted = false;
    private GridRun $run;

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
        \Propel::getConnection()->beginTransaction();
        $r = new GridRun();
        $r->setLabel('budgetinvarianttest-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Live');
        $r->setSimulated(true);
        $r->setPLow('100');
        $r->setPHigh('140');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('1000');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('1000');
        $r->setMaxOrderQuote('500');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid(substr(bin2hex(random_bytes(6)), 0, 10));
        $r->save();
        $this->run = $r;
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private string $tick = '120';

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

    public function testBootAlertsWhenSlicesOvercommit(): void
    {
        // second Live run to overcommit the pool
        $other = new GridRun();
        $other->setLabel('budgetinvarianttest-other-' . bin2hex(random_bytes(4)));
        $other->setSymbol('BTCUSDT');
        $other->setStatus('Live');
        $other->setSimulated(true);
        $other->setPLow('100');
        $other->setPHigh('140');
        $other->setNLevels(4);
        $other->setSpacing('Arithmetic');
        $other->setAllocation('EqualQuote');
        $other->setBudgetQuote('800');
        $other->setFeePct('0.001');
        $other->setMaxPositionQuote('1000');
        $other->setMaxOrderQuote('500');
        $other->setDailyLossLimitQuote('10000');
        $other->setBreakoutBufferPct('0.02');
        $other->setBreakoutPolicy('HaltAndHold');
        $other->setMaxOpenOrders(60);
        $other->setRunUid(substr(bin2hex(random_bytes(6)), 0, 10));
        $other->save();

        $this->run->setBudgetQuote('800')->save();
        $this->makeDaemon()->boot();
        $this->assertSame(1, \App\BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('budget_overcommit')->filterByLevel('Alert')->count());
    }

    public function testNoAlertWhenWithinBudget(): void
    {
        // Self-certifying fixture: checkBudgetInvariant() sums EVERY active
        // run globally, so a hardcoded slice would silently depend on
        // whatever ambient DryRun/Testnet/Live rows happen to exist in the
        // project DB. Measure the ambient sum (excluding this test's own
        // run) and size this run's slice to land strictly under budget once
        // the ambient rows are counted back in.
        $ambient = '0';
        foreach (\App\GridRunQuery::create()
            ->filterByStatus(['DryRun', 'Testnet', 'Live'], \Criteria::IN)
            ->filterByIdGridRun((int) $this->run->getIdGridRun(), \Criteria::NOT_EQUAL)
            ->find() as $r) {
            $ambient = bcadd($ambient, (string) $r->getBudgetQuote(), 12);
        }
        $budget = \App\Domains\Bot\SimWallet::sharedBudget();
        if (bccomp($ambient, $budget, 12) >= 0) {
            $this->markTestSkipped("ambient active runs already sum to $ambient >= shared budget $budget — cannot express a within-budget fixture");
        }
        // size this run's slice to stay strictly under budget WITH the ambient rows counted
        $slice = bcsub($budget, bcadd($ambient, '1', 12), 12);
        $this->run->setBudgetQuote($slice)->save();

        $this->makeDaemon()->boot();
        $this->assertSame(0, \App\BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('budget_overcommit')->count());
    }
}
