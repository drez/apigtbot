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
        $rbacIds = [];
        foreach (\App\ApiRbacQuery::create()
            ->filterByAction('mcp')
            ->filterByModel('gtbot_%', \Criteria::LIKE)
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
        $now = $now ?? time();
        $last = self::lastMcpActivity();
        return $last === null || ($now - $last) > $silenceSecs;
    }
}
