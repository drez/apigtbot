<?php

namespace App\Domains\Bot;

use App\BotEvent;
use App\BotEventQuery;

/**
 * A/B soak milestone watch: one-shot alerts when either arm of the
 * Trend-vs-Grid experiment reaches a "first" worth a close look — the trend
 * arm's first entry fill, its first stop-out (the exit must be watched to
 * fill), the stop's cycle actually closing, and the grid control's first
 * closed cycle. Cron-driven (bin/gtbot-ab-watch).
 *
 * Each milestone fires exactly once: the marker is itself a bot_event (kind
 * ab_milestone), so the dedupe survives daemon/cron restarts and the firing
 * is auditable in the event feed next to its source event.
 */
final class AbWatch
{
    public const KIND = 'ab_milestone';

    /** @return array<string, array{run: int, kind: string, prefix: string, label: string}> */
    public static function milestones(int $trendRun, int $gridRun): array
    {
        return [
            'trend-first-entry' => [
                'run' => $trendRun,
                'kind' => 'trend_signal',
                'prefix' => 'entry tranche filled',
                'label' => 'Trend arm took its FIRST ENTRY',
            ],
            'trend-first-stop' => [
                'run' => $trendRun,
                'kind' => 'trend_signal',
                'prefix' => 'stop hit:',
                'label' => 'Trend arm hit its FIRST STOP — watch that the exit fills',
            ],
            'trend-first-cycle' => [
                'run' => $trendRun,
                'kind' => 'cycle_closed',
                'prefix' => 'trend cycle:',
                'label' => 'Trend arm closed its FIRST CYCLE (exit filled)',
            ],
            'grid-control-first-cycle' => [
                'run' => $gridRun,
                'kind' => 'cycle_closed',
                'prefix' => '', // legacy exit cycles count: same capital completing its round trip
                'label' => 'Grid control closed its FIRST CYCLE',
            ],
        ];
    }

    /**
     * Run one watch pass; returns the milestone keys that fired.
     * $sinceUtc excludes pre-experiment history (DB timestamps are UTC on prod).
     */
    public static function check(int $trendRun, int $gridRun, string $sinceUtc, ?TelegramNotifier $notifier): array
    {
        $fired = [];
        foreach (self::milestones($trendRun, $gridRun) as $key => $m) {
            if (self::alreadyMarked($m['run'], $key)) {
                continue;
            }
            $src = self::firstMatch($m['run'], $m['kind'], $m['prefix'], $sinceUtc);
            if ($src === null) {
                continue;
            }
            // KIND is passed as an immediate kind: at most 4 of these ever
            // fire, and a same-pass pair must not lose one to the 5-min
            // alert throttle (the buffer dies with this short-lived process).
            (new EventLog($m['run'], false, $notifier, [], [self::KIND]))->write(
                'Alert',
                self::KIND,
                sprintf(
                    '[%s] %s — %s (src #%d @ %s UTC)',
                    $key,
                    $m['label'],
                    mb_substr((string) $src->getMessage(), 0, 200),
                    (int) $src->getIdBotEvent(),
                    $src->getDateCreation('Y-m-d H:i:s')
                )
            );
            $fired[] = $key;
        }
        return $fired;
    }

    private static function alreadyMarked(int $runId, string $key): bool
    {
        return BotEventQuery::create()
            ->filterByIdGridRun($runId)
            ->filterByKind(self::KIND)
            ->filterByMessage('[' . $key . ']%', \Criteria::LIKE)
            ->count() > 0;
    }

    private static function firstMatch(int $runId, string $kind, string $prefix, string $sinceUtc): ?BotEvent
    {
        $q = BotEventQuery::create()
            ->filterByIdGridRun($runId)
            ->filterByKind($kind)
            ->filterByDateCreation($sinceUtc, \Criteria::GREATER_EQUAL)
            ->orderByIdBotEvent(\Criteria::ASC);
        if ($prefix !== '') {
            $q->filterByMessage($prefix . '%', \Criteria::LIKE);
        }
        return $q->findOne();
    }
}
