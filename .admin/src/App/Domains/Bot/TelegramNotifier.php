<?php

namespace App\Domains\Bot;

/**
 * Telegram Bot API alert channel. send() never throws — a broken alert
 * channel must never take the trading loop down; failures return false and
 * the event row (already persisted) remains the source of truth.
 *
 * Rate limit: at most ONE Telegram message per MIN_INTERVAL (5 min), global
 * across every producer (daemon, refit cron, MCP tools, watchdog) via a
 * shared flock'd state file. The first message after a quiet window goes
 * instantly; anything else queues and is delivered as a single digest by the
 * next flush() once the window expires (the daemon flushes every tick, so a
 * queued message waits at most ~5 min). A failed delivery keeps the messages
 * queued for the next window — it never hammers a 429.
 *
 * Env: GTBOT_TELEGRAM_BOT_TOKEN (from @BotFather), GTBOT_TELEGRAM_CHAT_ID
 * (discoverable via bin/gtbot-watchdog --discover after messaging the bot),
 * GTBOT_TELEGRAM_STATE (throttle state file; default .admin/tmp).
 */
class TelegramNotifier
{
    /** Seconds between Telegram messages — the "max 1 per 5 min" budget. */
    private const MIN_INTERVAL = 300;
    /** A digest never carries more than this many queued lines. */
    private const MAX_QUEUE = 50;

    /** @var callable fn(string $url, array $post): array{status:int, body:string} */
    private $transport;
    /** @var callable fn(): int */
    private $clock;
    /** @var array{last:int, queue:string[]} fallback state when no file is usable */
    private array $memState = ['last' => 0, 'queue' => []];

    public function __construct(
        private readonly string $botToken,
        private readonly string $chatId,
        ?callable $transport = null,
        private readonly ?string $stateFile = null,
        ?callable $clock = null
    ) {
        $this->transport = $transport ?? [$this, 'curlTransport'];
        $this->clock = $clock ?? static fn (): int => time();
    }

    /** Null when the env isn't configured — callers just skip fan-out. */
    public static function fromEnv(): ?self
    {
        $token = (string) env('GTBOT_TELEGRAM_BOT_TOKEN', '');
        $chat = (string) env('GTBOT_TELEGRAM_CHAT_ID', '');
        if ($token === '' || $chat === '') {
            return null;
        }
        // shared state so the 5-min budget holds across daemon + cron + web
        $state = (string) env('GTBOT_TELEGRAM_STATE', dirname(__DIR__, 4) . '/tmp/telegram-notify.json');
        return new self($token, $chat, null, $state !== '' ? $state : null);
    }

    /** Queue a message for the next due flush() without attempting delivery. */
    public function buffer(string $text): void
    {
        $this->mutate(static function (array $s) use ($text): array {
            $s['queue'][] = $text;
            return $s;
        });
    }

    /**
     * Queue + attempt delivery now. Delivers immediately when the 5-min
     * window is clear (an isolated alert is instant); otherwise the message
     * waits for the next due flush. Returns false only on a failed delivery
     * attempt — a queued message is a success.
     */
    public function send(string $text): bool
    {
        $this->buffer($text);
        return $this->flush();
    }

    /**
     * Deliver everything queued as ONE digest if the window allows; no-op
     * otherwise. Call freely (the daemon calls it every tick) — it only
     * touches the network when something is both queued and due.
     */
    public function flush(): bool
    {
        $now = ($this->clock)();
        $claimed = null;
        $this->mutate(function (array $s) use ($now, &$claimed): array {
            if ($s['queue'] === [] || $now - $s['last'] < self::MIN_INTERVAL) {
                return $s;
            }
            // claim under the lock: the window is spent by the ATTEMPT, so
            // concurrent processes can't double-send and failures can't hammer
            $claimed = $s['queue'];
            $s['queue'] = [];
            $s['last'] = $now;
            return $s;
        });
        if ($claimed === null) {
            return true;
        }
        $dropped = 0;
        if (count($claimed) > self::MAX_QUEUE) {
            $dropped = count($claimed) - self::MAX_QUEUE;
            $claimed = array_slice($claimed, -self::MAX_QUEUE);
        }
        $digest = ($dropped > 0 ? "(… {$dropped} older notifications dropped)\n" : '')
            . implode("\n", $claimed);
        if ($this->deliver($digest)) {
            return true;
        }
        // failed: requeue so the next window retries them
        $this->mutate(static function (array $s) use ($claimed): array {
            $s['queue'] = array_merge($claimed, $s['queue']);
            return $s;
        });
        return false;
    }

    /**
     * Deliver immediately, EXEMPT from the 5-min budget: no queue, no window
     * check, and the window state is left untouched so throttled traffic
     * keeps its schedule. Reserved for the few messages the operator wants
     * the instant they happen (closed-cycle results).
     */
    public function sendNow(string $text): bool
    {
        return $this->deliver($text);
    }

    /** One raw Telegram API call. */
    private function deliver(string $text): bool
    {
        try {
            $res = ($this->transport)(
                "https://api.telegram.org/bot{$this->botToken}/sendMessage",
                ['chat_id' => $this->chatId, 'text' => mb_substr($text, 0, 4000)]
            );
            $body = json_decode($res['body'] ?? '', true);
            return ($res['status'] ?? 0) === 200 && !empty($body['ok']);
        } catch (\Throwable $e) {
            fwrite(STDERR, 'telegram send failed: ' . $e->getMessage() . "\n");
            return false;
        }
    }

    /**
     * Atomic read-modify-write of the throttle state, flock'd so the daemon,
     * cron and web workers share one budget. Any file trouble falls back to
     * in-process state — alerting must never take the caller down.
     *
     * @param callable(array{last:int, queue:string[]}): array $fn
     */
    private function mutate(callable $fn): void
    {
        if ($this->stateFile === null) {
            $this->memState = $fn($this->memState);
            return;
        }
        try {
            $existed = file_exists($this->stateFile);
            $fh = @fopen($this->stateFile, 'c+');
            if ($fh === false) {
                $this->memState = $fn($this->memState);
                return;
            }
            try {
                flock($fh, LOCK_EX);
                $raw = stream_get_contents($fh);
                $s = json_decode((string) $raw, true);
                $state = [
                    'last' => (int) (is_array($s) ? ($s['last'] ?? 0) : 0),
                    'queue' => array_values(array_filter(
                        is_array($s) ? (array) ($s['queue'] ?? []) : [],
                        'is_string'
                    )),
                ];
                $state = $fn($state);
                ftruncate($fh, 0);
                rewind($fh);
                fwrite($fh, json_encode($state, JSON_UNESCAPED_SLASHES));
                fflush($fh);
            } finally {
                flock($fh, LOCK_UN);
                fclose($fh);
            }
            if (!$existed) {
                // daemon/cron/web share via GROUP bits only — world-writable
                // would let any local user inject spoofed digest lines
                @chmod($this->stateFile, 0660);
            }
        } catch (\Throwable) {
            $this->memState = $fn($this->memState);
        }
    }

    /** Recent updates — used once by --discover to find the chat id. */
    public function getUpdates(): array
    {
        try {
            $res = ($this->transport)("https://api.telegram.org/bot{$this->botToken}/getUpdates", []);
            $body = json_decode($res['body'] ?? '', true);
            return is_array($body['result'] ?? null) ? $body['result'] : [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function curlTransport(string $url, array $post): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        if ($post !== []) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }
        $out = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        return ['status' => $status, 'body' => (string) $out];
    }
}
