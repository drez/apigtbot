<?php

namespace App\Domains\Bot;

/**
 * Audit trail of every gate that changed a refit request — the
 * "conviction requests, risk disposes" record. A refit decision stores
 * what was asked (requested_json), what each limit did to it
 * (clamps_json, this class) and what was finally applied, so
 * requested-vs-applied is reconstructible from the bot_decision row
 * alone, and the track record can split "the routine asked for X" from
 * "the gates allowed Y".
 *
 * Limits in use: regime_gate (RegimeGate::capDeployPct), profile_wall
 * (RoutineBrief::PROFILE_WALLS), budget_guard, spacing_floor /
 * half_width_floor / max_levels (RangeFitter). A no-op add() (before ==
 * after) is dropped so an empty trail means "applied as requested".
 */
final class ClampTrail
{
    /** bounds within this fraction of the candidate's count as "followed" */
    public const SAME_TOL = 0.01;

    /** @var array<int, array{limit:string, before:mixed, after:mixed, why?:string}> */
    private array $events = [];

    public function add(string $limit, int|float|string $before, int|float|string $after, ?string $why = null): self
    {
        if ((string) $before === (string) $after) {
            return $this;
        }
        $e = ['limit' => $limit, 'before' => $before, 'after' => $after];
        if ($why !== null && $why !== '') {
            $e['why'] = $why;
        }
        $this->events[] = $e;
        return $this;
    }

    public function any(): bool
    {
        return $this->events !== [];
    }

    /** @return array<int, array{limit:string, before:mixed, after:mixed, why?:string}> */
    public function all(): array
    {
        return $this->events;
    }

    public function toJson(): string
    {
        return json_encode($this->events, JSON_UNESCAPED_SLASHES);
    }

    /**
     * 'same' when the applied geometry follows RoutineBrief::candidate()
     * (both bounds within SAME_TOL, identical n_levels), 'deviated'
     * otherwise, 'none' when no candidate existed (no candles / Trend run).
     *
     * @param array{p_low:string|float, p_high:string|float, n_levels:int}|null $candidate
     */
    public static function candidateDelta(?array $candidate, string $pLow, string $pHigh, int $n): string
    {
        if ($candidate === null || !isset($candidate['p_low'], $candidate['p_high'], $candidate['n_levels'])) {
            return 'none';
        }
        $close = static function (float $a, float $b): bool {
            return $b > 0 && abs($a - $b) / $b <= self::SAME_TOL;
        };
        return (int) $candidate['n_levels'] === $n
            && $close((float) $pLow, (float) $candidate['p_low'])
            && $close((float) $pHigh, (float) $candidate['p_high'])
            ? 'same' : 'deviated';
    }
}
