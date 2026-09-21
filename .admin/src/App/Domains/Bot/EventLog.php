<?php

namespace App\Domains\Bot;

use App\BotEvent;

/**
 * Structured event sink: every daemon decision lands as a bot_event row
 * (browsable in the GUI, queryable over MCP) and echoes to stdout for the
 * journal. Alert/Error events additionally fan out to Telegram when
 * configured — fan-out failure never breaks the write path. Secrets must
 * never reach message/payload.
 */
class EventLog
{
    /**
     * @param string[] $notifyKinds    Info-level event kinds that ALSO fan out (e.g. trades).
     * @param string[] $immediateKinds Kinds delivered the moment they happen —
     *                                 exempt from the buffer AND the 5-min
     *                                 throttle (closed-cycle results).
     */
    public function __construct(
        private readonly int $runId,
        private readonly bool $echo = true,
        private readonly ?TelegramNotifier $notifier = null,
        private readonly array $notifyKinds = [],
        private readonly array $immediateKinds = []
    ) {
    }

    /**
     * Stops and the failures that leave real inventory unguarded: always
     * sent the instant they happen, whoever constructed the log. As plain
     * alerts they shared the one-message-per-5-min budget, so a kill could
     * reach the operator five minutes after the alert that preceded it.
     */
    public const URGENT_KINDS = [
        'kill', 'risk_kill', 'drawdown_stop', 'unrealized_stop',
        'exit_shrunk', 'api_rate_limited', 'clock_drift',
    ];

    private const KIND_ICON = [
        'buy_fill' => '🟢',
        'run_retired' => '📦',
        'run_finalized' => '🏁',
        'alloc_rebalance' => '⚖️',
        'alloc_drift' => '📈',
        'cycle_closed' => '💰',
        'refit_applied' => '🔄',
        'boot' => '🚀',
        'algo_update' => '🧠',
        'bot_start' => '▶️',
        'bot_stop' => '⏸️',
        'bot_restart' => '🔁',
        'algo_switch' => '🔀',
        'algo_cutover' => '🔀',
        'engine_missing' => '❓',
        'stranded_book' => '🧊',
        'trend_signal' => '📈',
    ];

    public function write(string $level, string $kind, string $message, ?array $payload = null): void
    {
        $e = new BotEvent();
        $e->setIdGridRun($this->runId);
        $e->setLevel($level);
        $e->setKind($kind);
        $e->setMessage(mb_substr($message, 0, 500));
        if ($payload !== null) {
            $e->setPayload(json_encode($payload, JSON_UNESCAPED_SLASHES));
        }
        $e->save();
        if ($this->echo) {
            fwrite(STDOUT, sprintf("[%s] %s %s: %s\n", date('H:i:s'), $level, $kind, $message));
        }
        $isAlert = in_array($level, ['Alert', 'Error'], true);
        $isUrgent = $isAlert && in_array($kind, self::URGENT_KINDS, true);
        $isImmediate = in_array($kind, $this->immediateKinds, true) || $isUrgent;
        $isTrade = in_array($kind, $this->notifyKinds, true);
        if ($this->notifier !== null && ($isAlert || $isImmediate || $isTrade)) {
            $icon = $isAlert ? ($level === 'Alert' ? '🚨' : '❌') : (self::KIND_ICON[$kind] ?? '•');
            // The event id tags every line: grid cycles at the same level
            // produce byte-identical text (same prices, qty, realized P/L),
            // and untagged they read as an accidental double-send.
            $line = sprintf('%s gtbot run %d — %s: %s [#%d]', $icon, $this->runId, $kind, $message, (int) $e->getIdBotEvent());
            // Immediate kinds (cycle results) bypass buffer AND throttle;
            // alerts respect the throttle but skip the buffer; other trade
            // notifications buffer so a burst of fills in one tick coalesces
            // into a single digest (flush() sends it).
            if ($isImmediate) {
                // An URGENT line repeats for reasons that are not news: the
                // same kill re-announced by every boot of a restart loop, the
                // same exit_shrunk on every chase tick. Collapse those for a
                // minute — Telegram answers a burst with a 429 and sendNow's
                // casualty is as likely to be the stop as the noise. A trade
                // notification (cycle_closed) passes no key: two real cycles
                // at one level print the same text and are both real.
                $this->notifier->sendNow(
                    $line,
                    $isUrgent ? sprintf('%d:%s:%s', $this->runId, $kind, $message) : null
                );
            } elseif ($isAlert) {
                $this->notifier->send($line);
            } else {
                $this->notifier->buffer($line);
            }
        }
    }

    /** Send any buffered trade notifications as one message (call once per tick). */
    public function flush(): void
    {
        $this->notifier?->flush();
    }
}
