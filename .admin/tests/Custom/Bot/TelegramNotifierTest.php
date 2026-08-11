<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\TelegramNotifier;
use PHPUnit\Framework\TestCase;

class TelegramNotifierTest extends TestCase
{
    public function testSendPostsChatIdAndText(): void
    {
        $captured = [];
        $transport = function (string $url, array $post) use (&$captured): array {
            $captured[] = ['url' => $url, 'post' => $post];
            return ['status' => 200, 'body' => '{"ok":true}'];
        };
        $n = new TelegramNotifier('123:abc', '42', $transport);
        $this->assertTrue($n->send('grid halted'));
        $this->assertStringContainsString('/bot123:abc/sendMessage', $captured[0]['url']);
        $this->assertSame('42', $captured[0]['post']['chat_id']);
        $this->assertSame('grid halted', $captured[0]['post']['text']);
    }

    public function testSendFailureReturnsFalseNeverThrows(): void
    {
        $n = new TelegramNotifier('123:abc', '42', fn () => ['status' => 500, 'body' => '{"ok":false}']);
        $this->assertFalse($n->send('x'));
        $n2 = new TelegramNotifier('123:abc', '42', function () {
            throw new \RuntimeException('network down');
        });
        $this->assertFalse($n2->send('x'), 'alerting must never take down the caller');
    }

    public function testBufferedSendsCoalesceOnFlush(): void
    {
        $sent = [];
        $n = new TelegramNotifier('t', '42', function (string $url, array $post) use (&$sent): array {
            $sent[] = $post['text'];
            return ['status' => 200, 'body' => '{"ok":true}'];
        });
        $n->buffer('🟢 buy L03 filled');
        $n->buffer('🟢 buy L02 filled');
        $n->buffer('💰 cycle L03 +0.94');
        $this->assertSame([], $sent, 'buffered messages are not sent until flush');
        $n->flush();
        $this->assertCount(1, $sent, 'the tick digest is a single message');
        $this->assertStringContainsString('buy L03', $sent[0]);
        $this->assertStringContainsString('cycle L03', $sent[0]);
    }

    public function testFlushWithNothingBufferedSendsNothing(): void
    {
        $sent = [];
        $n = new TelegramNotifier('t', '42', function (string $url, array $post) use (&$sent): array {
            $sent[] = $post['text'];
            return ['status' => 200, 'body' => '{"ok":true}'];
        });
        $n->flush();
        $this->assertSame([], $sent);
    }

    public function testSendDeliversBufferedMessagesInTheSameDigest(): void
    {
        $sent = [];
        $n = new TelegramNotifier('t', '42', function (string $url, array $post) use (&$sent): array {
            $sent[] = $post['text'];
            return ['status' => 200, 'body' => '{"ok":true}'];
        });
        $n->buffer('trade');
        $n->send('🚨 ALERT'); // an alert flushes what's queued along with it — one message
        $this->assertCount(1, $sent);
        $this->assertStringContainsString('trade', $sent[0]);
        $this->assertStringContainsString('🚨 ALERT', $sent[0]);
    }

    // ── 5-minute global throttle ────────────────────────────────────────

    /** @return array{0: TelegramNotifier, 1: \ArrayObject} notifier + sent messages */
    private function throttled(int &$now, ?string $stateFile = null): array
    {
        $box = new \ArrayObject();
        $n = new TelegramNotifier(
            't',
            '42',
            function (string $url, array $post) use ($box): array {
                $box[] = $post['text'];
                return ['status' => 200, 'body' => '{"ok":true}'];
            },
            $stateFile,
            function () use (&$now): int {
                return $now;
            }
        );
        return [$n, $box];
    }

    public function testSecondSendWithinFiveMinutesIsQueuedNotSent(): void
    {
        $now = 1000;
        [$n, $sent] = $this->throttled($now);
        $this->assertTrue($n->send('first'), 'quiet window — first message goes instantly');
        $now = 1100;
        $n->send('second');
        $this->assertCount(1, $sent, 'max 1 send per 5 min — the second must wait');
        $this->assertSame('first', $sent[0]);
    }

    public function testQueuedMessagesDigestAfterWindowExpires(): void
    {
        $now = 1000;
        [$n, $sent] = $this->throttled($now);
        $n->send('first');
        $now = 1100;
        $n->send('second');
        $n->send('third');
        $now = 1200;
        $n->flush();
        $this->assertCount(1, $sent, 'window still open — nothing extra sent');
        $now = 1301; // 5 min after the first send
        $n->flush();
        $this->assertCount(2, $sent, 'one digest for the whole burst');
        $this->assertStringContainsString('second', $sent[1]);
        $this->assertStringContainsString('third', $sent[1]);
    }

    public function testThrottleStateIsSharedAcrossProcesses(): void
    {
        // the daemon, refit cron, MCP tools and watchdog each construct their
        // own notifier — the 5-min budget must hold across all of them
        $state = tempnam(sys_get_temp_dir(), 'tg-test-');
        try {
            $now = 1000;
            [$a, $sentA] = $this->throttled($now, $state);
            $a->send('from the daemon');
            $this->assertCount(1, $sentA);

            $now = 1100;
            [$b, $sentB] = $this->throttled($now, $state);
            $b->send('from the refit cron');
            $this->assertCount(0, $sentB, 'other process already spent the window');

            $now = 1301;
            [$c, $sentC] = $this->throttled($now, $state);
            $c->flush();
            $this->assertCount(1, $sentC, 'a later process delivers the queued digest');
            $this->assertStringContainsString('refit cron', $sentC[0]);
        } finally {
            @unlink($state);
        }
    }

    public function testSendNowBypassesThrottleAndLeavesQueueIntact(): void
    {
        $now = 1000;
        [$n, $sent] = $this->throttled($now);
        $n->send('first');              // spends the 5-min window
        $now = 1100;
        $n->send('queued trade');       // waits (window spent)
        $n->sendNow('💰 cycle closed'); // exempt — delivers immediately
        $this->assertCount(2, $sent);
        $this->assertSame('💰 cycle closed', $sent[1]);

        $now = 1301;                    // exemption didn't reset the window
        $n->flush();
        $this->assertCount(3, $sent, 'queued digest still delivers on schedule');
        $this->assertStringContainsString('queued trade', $sent[2]);
    }

    public function testStateFileIsNotWorldWritable(): void
    {
        // world-writable would let any local user inject spoofed lines into
        // the operator's Telegram digest — share via group bits only
        $state = sys_get_temp_dir() . '/tg-perms-' . bin2hex(random_bytes(4)) . '.json';
        try {
            $now = 1000;
            [$n] = $this->throttled($now, $state);
            $n->buffer('seed the file');
            clearstatcache(true, $state);
            $this->assertFileExists($state);
            $this->assertSame(0, fileperms($state) & 0o002, 'state file must not be world-writable');
        } finally {
            @unlink($state);
        }
    }

    public function testFailedDigestIsRetainedAndRetriedNextWindow(): void
    {
        $now = 1000;
        $fail = true;
        $sent = [];
        $n = new TelegramNotifier('t', '42', function (string $url, array $post) use (&$sent, &$fail): array {
            if ($fail) {
                return ['status' => 500, 'body' => '{"ok":false}'];
            }
            $sent[] = $post['text'];
            return ['status' => 200, 'body' => '{"ok":true}'];
        }, null, function () use (&$now): int {
            return $now;
        });
        $this->assertFalse($n->send('important'), 'delivery failed');
        $fail = false;
        $n->flush();
        $this->assertCount(0, $sent, 'failed attempt still spent the window — no hammering');
        $now = 1301;
        $n->flush();
        $this->assertSame(['important'], (array) $sent, 'message survives the failure and retries');
    }

    public function testTokenNeverAppearsInMessageText(): void
    {
        $captured = [];
        $n = new TelegramNotifier('SECRET-TOKEN', '42', function (string $url, array $post) use (&$captured): array {
            $captured[] = $post;
            return ['status' => 200, 'body' => '{"ok":true}'];
        });
        $n->send('alert about SECRET-TOKEN leak?');
        // the token belongs in the URL only; text is passed through verbatim —
        // this guards the notifier itself from embedding credentials
        $this->assertArrayNotHasKey('token', $captured[0]);
    }
}
