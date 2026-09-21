<?php

namespace App\Domains\Bot;

use App\Domains\Bot\Gateway\BinanceApiError;

/**
 * How long the runner waits after an API error. Binance escalates a client
 * that keeps calling through a 429 to a 418 IP ban, and every repeat lengthens
 * the ban (2 min → 3 days) — so a rate-limit answer is obeyed, never clipped
 * to a convenience cap below it, and an unhinted one backs off exponentially.
 *
 * EVERY branch is capped at BAN_CAP, hinted ones included. Honouring a
 * multi-day Retry-After "in full" was tried and reverted: bin/gtbot does slice
 * the wait and Daemon::idle does keep the control plane alive between slices,
 * so the heartbeat, the kill switch and the command queue survive — and that
 * is exactly the problem. A run that keeps stamping a heartbeat for three days
 * while it cannot make a single exchange call is a run the watchdog
 * (GTBOT_HEARTBEAT_STALE), the dashboard and the hourly routine all read as
 * HEALTHY, with its orders still resting and filling on the exchange, its
 * fills unbooked and both drawdown rails unrun — and, before the mark was
 * dropped in Daemon::idle, its stale price still pricing the whole fleet's
 * wallet. One probe an hour into a ban is a cost worth paying to cap that
 * window at an hour.
 *
 * The cap is a PROBE interval, not a claim that the ban has lifted: the next
 * call simply re-learns the ban and waits another hour, which is what the
 * exponential ladder below already does for an unhinted one.
 */
class ApiBackoff
{
    private const ORDINARY_CAP = 300;
    private const BAN_START = 120;
    /** Longest wait any ban branch climbs to — the probe interval. */
    private const BAN_CAP = 3600;
    /** Longest single uninterrupted sleep: the runner wakes at least this
     *  often to stamp a heartbeat and read the kill switch. */
    public const SLICE = 30;

    public static function rateLimited(BinanceApiError $e): bool
    {
        return $e->httpStatus === 429 || $e->httpStatus === 418 || $e->binanceCode === -1003;
    }

    /** A Retry-After of 0 (or a negative one — a proxy's idea of "now") is not
     *  a zero backoff: it is an ABSENT hint. Taken literally it multiplied the
     *  exponential ladder by zero, so a run that kept erroring retried in a
     *  tight loop — exactly the behaviour a 418 is handed out for. */
    private static function hint(BinanceApiError $e): ?int
    {
        return $e->retryAfter !== null && $e->retryAfter > 0 ? $e->retryAfter : null;
    }

    /** @param int $consecutive errors in a row, this one included (≥ 1) */
    public static function delay(BinanceApiError $e, int $interval, int $consecutive): int
    {
        $doublings = min(max($consecutive, 1) - 1, 16);
        $hint = self::hint($e);
        if (self::rateLimited($e)) {
            if ($hint !== null) {
                return min($hint + $interval, self::BAN_CAP); // wake just AFTER the window, not on it
            }
            return min(self::BAN_START * (2 ** $doublings), self::BAN_CAP);
        }
        return min(($hint ?? $interval * 3) * (2 ** $doublings), self::ORDINARY_CAP);
    }
}
