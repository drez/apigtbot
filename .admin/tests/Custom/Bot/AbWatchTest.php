<?php

namespace Tests\Custom\Bot;

use App\BotEvent;
use App\BotEventQuery;
use App\Domains\Bot\AbWatch;
use App\Domains\Bot\TelegramNotifier;
use App\GridRun;
use PHPUnit\Framework\TestCase;

class AbWatchTest extends TestCase
{
    private static bool $booted = false;
    private GridRun $trend;
    private GridRun $grid;
    /** @var array<int, string> */
    private array $sent = [];
    private TelegramNotifier $notifier;

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
        $this->trend = $this->makeRun('abwatch-trend-');
        $this->grid = $this->makeRun('abwatch-grid-');
        $this->sent = [];
        $this->notifier = new TelegramNotifier('t', 'c', function (string $url, array $post): array {
            $this->sent[] = $post['text'] ?? '';
            return ['status' => 200, 'body' => '{"ok":true}'];
        });
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function makeRun(string $labelPrefix): GridRun
    {
        $r = new GridRun();
        $r->setLabel($labelPrefix . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualBase');
        $r->setBudgetQuote('550');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->save();
        return $r;
    }

    private function event(GridRun $run, string $kind, string $message, string $whenUtc): BotEvent
    {
        $e = new BotEvent();
        $e->setIdGridRun((int) $run->getIdGridRun());
        $e->setLevel('Info');
        $e->setKind($kind);
        $e->setMessage($message);
        $e->save();
        // Propel stamps date_creation with now(); rewind it for since-cutoff cases.
        $e->setDateCreation($whenUtc);
        $e->save();
        return $e;
    }

    private function check(string $since = '2020-01-01 00:00:00'): array
    {
        return AbWatch::check(
            (int) $this->trend->getIdGridRun(),
            (int) $this->grid->getIdGridRun(),
            $since,
            $this->notifier
        );
    }

    public function testQuietTapeFiresNothing(): void
    {
        $this->event($this->trend, 'boot', 'symbol=BTCUSDT algo=Trend', '2026-08-12 00:00:00');
        $this->event($this->trend, 'trend_signal', 'breakout entry signal: donchian=20 — placing 2 tranche(s) @ 64000', '2026-08-12 01:00:00');
        $this->assertSame([], $this->check());
        $this->assertSame([], $this->sent);
    }

    public function testTrendFirstEntryFiresOnceWithSourceReference(): void
    {
        $src = $this->event($this->trend, 'trend_signal', 'entry tranche filled @ 64100 qty 0.001 — position qty 0.001 entry(vwap) 64100 stop 62800', '2026-08-12 01:00:00');

        $this->assertSame(['trend-first-entry'], $this->check());
        $this->assertCount(1, $this->sent);
        $this->assertStringContainsString('FIRST ENTRY', $this->sent[0]);
        $this->assertStringContainsString('#' . (int) $src->getIdBotEvent(), $this->sent[0]);

        $marker = BotEventQuery::create()
            ->filterByIdGridRun((int) $this->trend->getIdGridRun())
            ->filterByKind(AbWatch::KIND)
            ->findOne();
        $this->assertNotNull($marker, 'milestone marker persisted as a bot_event');
        $this->assertStringStartsWith('[trend-first-entry]', (string) $marker->getMessage());

        // second pass: marker dedupes, nothing new fires or sends
        $this->assertSame([], $this->check());
        $this->assertCount(1, $this->sent);
    }

    public function testEventsBeforeSinceCutoffAreIgnored(): void
    {
        $this->event($this->trend, 'trend_signal', 'entry tranche filled @ 64100 qty 0.001 — position qty 0.001 entry(vwap) 64100 stop 62800', '2026-08-10 00:00:00');
        $this->assertSame([], $this->check('2026-08-11 12:43:07'));
        $this->assertSame([], $this->sent);
    }

    public function testStopAndCycleAreDistinctMilestones(): void
    {
        $this->event($this->trend, 'trend_signal', 'stop hit: price 62700 <= stop 62800 — exiting qty 0.001', '2026-08-12 02:00:00');
        $this->assertSame(['trend-first-stop'], $this->check());

        $this->event($this->trend, 'cycle_closed', 'trend cycle: buy 64100 -> sell 62700 qty 0.001 realized -1.4', '2026-08-12 02:01:00');
        $this->assertSame(['trend-first-cycle'], $this->check());
        $this->assertCount(2, $this->sent);
        $this->assertStringContainsString('STOP', $this->sent[0]);
        $this->assertStringContainsString('FIRST CYCLE', $this->sent[1]);
    }

    public function testGridControlFirstCycleCountsLegacyExits(): void
    {
        $this->event($this->grid, 'cycle_closed', 'legacy exit cycle: buy 63753.44 -> sell 64753.21 qty 0.00019 realized 0.16', '2026-08-12 03:00:00');
        $this->assertSame(['grid-control-first-cycle'], $this->check());
        $this->assertStringContainsString('Grid control', $this->sent[0]);
    }

    public function testMilestonesScopedToTheirOwnRun(): void
    {
        // a cycle on the TREND run must not trip the grid-control milestone
        $this->event($this->trend, 'cycle_closed', 'trend cycle: buy 64100 -> sell 65000 qty 0.001 realized 0.8', '2026-08-12 04:00:00');
        $fired = $this->check();
        $this->assertContains('trend-first-cycle', $fired);
        $this->assertNotContains('grid-control-first-cycle', $fired);
    }
}
