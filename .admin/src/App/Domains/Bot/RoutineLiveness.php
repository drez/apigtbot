<?php

namespace App\Domains\Bot;

/**
 * Is the Claude refit routine alive? "No refit lately" is the wrong signal —
 * a healthy routine holds and writes nothing. But every routine pass calls
 * gtbot_* MCP tools, and AbstractGtbotBase audits every call to api_log, so
 * prolonged silence THERE means the routine/MCP link is down and the server
 * cron fallback is the only refit brain. The watchdog alerts on it; the risk
 * rails (breakout halt, staged stop, kill switch) never depended on MCP.
 */
final class RoutineLiveness
{
    /** Unix ts of the last gtbot_* MCP tool call, or null when none logged. */
    public static function lastMcpActivity(): ?int
    {
        return self::lastToolActivity('gtbot_%');
    }

    /** Unix ts of the last MCP call for one specific gtbot tool (exact model name or LIKE pattern). */
    public static function lastToolActivity(string $model): ?int
    {
        $rbacIds = [];
        foreach (\App\ApiRbacQuery::create()
            ->filterByAction('mcp')
            ->filterByModel($model, str_contains($model, '%') ? \Criteria::LIKE : \Criteria::EQUAL)
            ->find() as $rbac) {
            $rbacIds[] = (int) $rbac->getIdApiRbac();
        }
        if ($rbacIds === []) {
            return null;
        }
        $row = \App\ApiLogQuery::create()
            ->filterByIdApiRbac($rbacIds, \Criteria::IN)
            ->orderByTime(\Criteria::DESC)
            ->findOne();
        return $row ? (int) $row->getTime('U') : null;
    }

    /** True when no gtbot_* MCP call landed within $silenceSecs. */
    public static function isSilent(int $silenceSecs, ?int $now = null): bool
    {
        return self::isSilentFor('gtbot_%', $silenceSecs, $now);
    }

    /** True when one specific gtbot tool has not been called within
     *  $silenceSecs. NOTE: a CALL, not a successful one — the MCP audit row is
     *  written in a finally, so a refused or dry_run call counts the same. Use
     *  a journaled side effect (a bot_decision row) to prove a tool acted. */
    public static function isSilentFor(string $tool, int $silenceSecs, ?int $now = null): bool
    {
        $now = $now ?? time();
        $last = self::lastToolActivity($tool);
        return $last === null || ($now - $last) > $silenceSecs;
    }
}
