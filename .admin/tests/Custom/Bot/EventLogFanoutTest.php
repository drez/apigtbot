<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\EventLog;
use App\Domains\Bot\TelegramNotifier;
use App\GridRun;
use PHPUnit\Framework\TestCase;

class EventLogFanoutTest extends TestCase
{
    private static bool $booted = false;
    private GridRun $run;
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
        $r = new GridRun();
        $r->setLabel('fanout-' . bin2hex(random_bytes(4)));
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
        $r->setRunUid('fanout');
        $r->save();
        $this->run = $r;
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

    public function testAlertAndErrorFanOutInfoAndWarnDoNot(): void
    {
        // clocked notifier: back-to-back alerts must respect the 5-min budget
        $now = 1000;
        $notifier = new TelegramNotifier('t', 'c', function (string $url, array $post): array {
            $this->sent[] = $post['text'] ?? '';
            return ['status' => 200, 'body' => '{"ok":true}'];
        }, null, function () use (&$now): int {
            return $now;
        });
        $log = new EventLog((int) $this->run->getIdGridRun(), false, $notifier);
        $log->write('Info', 'boot', 'quiet');
        $log->write('Warn', 'veto', 'quiet');
        $log->write('Alert', 'stranded_book', 'loud');
        $log->write('Error', 'api_error', 'loud too');
        $this->assertCount(1, $this->sent, 'second alert inside the window waits for the digest');
        $this->assertStringContainsString('stranded_book: loud', $this->sent[0]);
        $this->assertStringContainsString('🚨', $this->sent[0]);

        $now = 1301; // window expired — next tick's flush delivers the digest
        $log->flush();
        $this->assertCount(2, $this->sent);
        $this->assertStringContainsString('api_error: loud too', $this->sent[1]);
        $this->assertStringContainsString('❌', $this->sent[1]);
    }

    public function testTradeKindsBufferThenFlushAsOneDigest(): void
    {
        $log = new EventLog((int) $this->run->getIdGridRun(), false, $this->notifier, ['buy_fill', 'cycle_closed']);
        $log->write('Info', 'placed', 'quiet — not a trade');
        $log->write('Info', 'buy_fill', 'L03 filled @ 62798');
        $log->write('Info', 'cycle_closed', 'L03 realized 0.94');
        $this->assertSame([], $this->sent, 'trades buffer — nothing sent until flush');
        $log->flush();
        $this->assertCount(1, $this->sent, 'the tick digest is a single message');
        $this->assertStringContainsString('🟢', $this->sent[0]);
        $this->assertStringContainsString('💰', $this->sent[0]);
        $this->assertStringNotContainsString('placed', $this->sent[0]); // non-trade not included
    }

    public function testAlertsStillSendImmediately(): void
    {
        $log = new EventLog((int) $this->run->getIdGridRun(), false, $this->notifier, ['buy_fill']);
        $log->write('Alert', 'kill', 'kill switch set');
        $this->assertCount(1, $this->sent, 'alerts bypass the buffer');
        $this->assertStringContainsString('🚨', $this->sent[0]);
    }

    public function testImmediateKindsSendInstantlyEvenInsideThrottleWindow(): void
    {
        // cycle results go out the moment they happen — no buffer, no window
        $now = 1000;
        $notifier = new TelegramNotifier('t', 'c', function (string $url, array $post): array {
            $this->sent[] = $post['text'] ?? '';
            return ['status' => 200, 'body' => '{"ok":true}'];
        }, null, function () use (&$now): int {
            return $now;
        });
        $log = new EventLog((int) $this->run->getIdGridRun(), false, $notifier, ['buy_fill'], ['cycle_closed']);
        $log->write('Alert', 'no_budget', 'spends the window');
        $this->assertCount(1, $this->sent);

        $now = 1100; // inside the 5-min window
        $log->write('Info', 'cycle_closed', 'L02 cycle: buy 65305.68 → sell 65865.64 qty 0.00306 realized 1.31');
        $this->assertCount(2, $this->sent, 'cycle result must not wait for the window');
        $this->assertStringContainsString('💰', $this->sent[1]);
        $this->assertStringContainsString('realized 1.31', $this->sent[1]);
    }

    public function testAKillIsNeverHeldBackByTheThrottleWindow(): void
    {
        // an ordinary alert spends the 5-min window; the stop that follows it
        // seconds later is the one message that cannot wait for the next one
        $now = 1000;
        $notifier = new TelegramNotifier('t', 'c', function (string $url, array $post): array {
            $this->sent[] = $post['text'] ?? '';
            return ['status' => 200, 'body' => '{"ok":true}'];
        }, null, function () use (&$now): int {
            return $now;
        });
        $log = new EventLog((int) $this->run->getIdGridRun(), false, $notifier);
        $log->write('Alert', 'no_budget', 'spends the window');
        $now = 1010;
        $log->write('Alert', 'ordinary', 'waits for the window');
        $this->assertCount(1, $this->sent);

        foreach (EventLog::URGENT_KINDS as $kind) {
            $log->write('Alert', $kind, 'now');
        }
        $this->assertCount(1 + count(EventLog::URGENT_KINDS), $this->sent);
        $this->assertStringContainsString('🚨', $this->sent[1]);
        $this->assertContains('drawdown_stop', EventLog::URGENT_KINDS);
        $this->assertContains('kill', EventLog::URGENT_KINDS);
    }

    public function testIdenticalMessagesGetDistinctEventIdTags(): void
    {
        // grid cycles at the same level produce byte-identical text (same
        // prices, qty, realized P/L) — the [#event-id] tag is what lets the
        // operator tell two real cycles from an accidental double-send
        $log = new EventLog((int) $this->run->getIdGridRun(), false, $this->notifier, [], ['cycle_closed']);
        $msg = 'L00 cycle: buy 62500 → sell 63500 qty 0.0024 realized 2.0992';
        $log->write('Info', 'cycle_closed', $msg);
        $log->write('Info', 'cycle_closed', $msg);
        $this->assertCount(2, $this->sent);
        $this->assertMatchesRegularExpression('/\[#\d+\]$/', $this->sent[0]);
        $this->assertNotSame($this->sent[0], $this->sent[1], 'two distinct events must never produce identical Telegram lines');
    }

    public function testNotifierFailureDoesNotBreakEventWrite(): void
    {
        $broken = new TelegramNotifier('t', 'c', function (): array {
            throw new \RuntimeException('telegram down');
        });
        $log = new EventLog((int) $this->run->getIdGridRun(), false, $broken);
        $log->write('Alert', 'kill', 'still persisted');
        $count = \App\BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('kill')
            ->count();
        $this->assertSame(1, $count, 'event row must persist even when Telegram is down');
    }
}
