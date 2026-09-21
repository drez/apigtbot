<?php

namespace App\Domains\Bot;

/**
 * Long-horizon market outlook: do the DAILY and the WEEKLY tape agree on a
 * direction? TrendRegime answers "is there a 4h/1d impulse the trend arm can
 * ride right now" and has no vocabulary for down at all (HOSTILE is "trending
 * against an unaligned 4h", either way). This is the slower question above
 * it — the one a human wants a warning about.
 *
 *   MAJOR_UP / MAJOR_DOWN     1w and 1d agree, and the daily has strength
 *   UP_FORMING / DOWN_FORMING the daily has turned, the weekly has not
 *                             followed (or agrees without daily strength) —
 *                             the early warning, noisier by construction
 *   NEUTRAL                   everything else, including 1d against 1w
 *
 * DETECTION, NOT FORECAST. scripts/outlook-sweep.php and outlook-research.php
 * (refit-evidence §16) walked the full BTC + BNB history: days in MAJOR_UP
 * were up 30d later no more often than base, MAJOR_DOWN days slightly LESS
 * often down than base, and none of 37 other long-horizon states held on
 * both symbols and both halves. So nothing here gates an engine, the regime
 * gate or the activator, the messages describe the tape and never what comes
 * next, and every confirmed call is logged with its price (market_outlook)
 * and scored at 7d / 30d — the live record is the only thing that could
 * ever earn this more weight.
 *
 * The weekly read never touches ema200 or the strong_* label: Indicators::ema
 * degrades to a plain mean under `period` bars, and few symbols have 200
 * weekly bars. Funding is context text only — it flipped sign between symbols
 * in the regime sweep (refit-evidence §7) and must not move a verdict.
 *
 * Every threshold is a named constant so scripts/outlook-sweep.php can tune
 * it (--set=NAME=value) against the same code the cron runs.
 */
final class MarketOutlook
{
    public const MAJOR_UP = 'MAJOR_UP';
    public const UP_FORMING = 'UP_FORMING';
    public const NEUTRAL = 'NEUTRAL';
    public const DOWN_FORMING = 'DOWN_FORMING';
    public const MAJOR_DOWN = 'MAJOR_DOWN';

    /** ordered — step() reads "which side of the confirmed verdict" off it */
    public const RANK = [
        self::MAJOR_DOWN => -2,
        self::DOWN_FORMING => -1,
        self::NEUTRAL => 0,
        self::UP_FORMING => 1,
        self::MAJOR_UP => 2,
    ];

    /** 1d EMA50 now vs this many bars ago, in percent */
    public const SLOPE_BARS_1D = 10;
    public const SLOPE_MIN_1D = 0.5;
    /** 1w EMA20 now vs this many bars ago; only its sign is read */
    public const SLOPE_BARS_1W = 4;
    /** of five daily votes (price>ema20, >ema50, ema20>ema50, >ema200, slope) */
    public const DIR_VOTES_1D = 4;
    /** MAJOR needs the daily to be actually trending — either gate */
    public const ADX_MIN_1D = 20.0;
    public const ER_MIN_1D = 0.30;
    /** under this many weekly bars there is no weekly structure to read */
    public const MIN_BARS_1W = 60;

    /** Consecutive HOURLY passes to confirm: six daily closes for a MAJOR or
     *  a return to NEUTRAL, three for FORMING. The in-progress bar repaints,
     *  and at one close of confirmation the sweep flipped 23-33 times a year
     *  (refit-evidence §16); at six it is 9-12, of which 3-4 touch a MAJOR. */
    public const CONFIRM_PASSES_MAJOR = 144;
    public const CONFIRM_PASSES_FORMING = 72;
    public const CONFIRM_PASSES_NEUTRAL = 144;

    /** a NEUTRAL call "hits" when the 30d move stays inside this band */
    public const NEUTRAL_BAND_PCT = 10.0;
    public const FUNDING_CROWDED = 90.0;
    public const FUNDING_WASHED = 10.0;

    /** sweep overrides, keyed by constant name — never set in production */
    private static array $override = [];

    public static function tune(string $name, float $value): void
    {
        if (!defined(self::class . '::' . $name)) {
            throw new \InvalidArgumentException("unknown MarketOutlook constant $name");
        }
        self::$override[$name] = $value;
    }

    private static function k(string $name): float
    {
        return (float) (self::$override[$name] ?? constant(self::class . '::' . $name));
    }

    /**
     * The two slopes the stored summary does not carry.
     * @param array<int, mixed> $closes1d oldest-first daily closes
     * @param array<int, mixed> $closes1w oldest-first weekly closes
     * @return array{slope50_1d:?float, slope20_1w:?float}
     */
    public static function features(array $closes1d, array $closes1w): array
    {
        return [
            'slope50_1d' => self::slopePct($closes1d, 50, (int) self::k('SLOPE_BARS_1D')),
            'slope20_1w' => self::slopePct($closes1w, 20, (int) self::k('SLOPE_BARS_1W')),
        ];
    }

    /** EMA(period) now vs $back bars ago, percent; null unless both ends are seeded. */
    private static function slopePct(array $closes, int $period, int $back): ?float
    {
        $closes = array_values($closes);
        if (count($closes) < $period + $back) {
            return null;
        }
        $then = Indicators::ema(array_slice($closes, 0, -$back), $period);
        if ($then == 0.0) {
            return null;
        }
        return round((Indicators::ema($closes, $period) / $then - 1) * 100, 3);
    }

    /**
     * Five daily votes each way; a vote whose inputs are missing counts for
     * neither side.
     * @return array{dir:string, up:int, down:int}
     */
    public static function dir1d(array $s1d, array $feat): array
    {
        $p = self::f($s1d, 'price');
        $e20 = self::f($s1d, 'ema20');
        $e50 = self::f($s1d, 'ema50');
        $e200 = self::f($s1d, 'ema200');
        $slope = $feat['slope50_1d'] ?? null;
        $min = self::k('SLOPE_MIN_1D');
        $pairs = [[$p, $e20], [$p, $e50], [$e20, $e50], [$p, $e200]];
        $up = $down = 0;
        foreach ($pairs as [$a, $b]) {
            if ($a === null || $b === null) {
                continue;
            }
            $up += $a > $b ? 1 : 0;
            $down += $a < $b ? 1 : 0;
        }
        if ($slope !== null) {
            $up += $slope >= $min ? 1 : 0;
            $down += $slope <= -$min ? 1 : 0;
        }
        $need = (int) self::k('DIR_VOTES_1D');
        return ['dir' => $up >= $need ? 'up' : ($down >= $need ? 'down' : 'flat'), 'up' => $up, 'down' => $down];
    }

    /** Weekly: price over (under) BOTH EMAs and an EMA20 that is not falling (rising). */
    public static function dir1w(array $s1w, array $feat): string
    {
        $p = self::f($s1w, 'price');
        $e20 = self::f($s1w, 'ema20');
        $e50 = self::f($s1w, 'ema50');
        $slope = $feat['slope20_1w'] ?? null;
        if ($p === null || $e20 === null || $e50 === null || $slope === null) {
            return 'flat';
        }
        if ($p > $e20 && $p > $e50 && $slope >= 0) {
            return 'up';
        }
        if ($p < $e20 && $p < $e50 && $slope <= 0) {
            return 'down';
        }
        return 'flat';
    }

    /**
     * @param array|null  $s1d     MarketStore::summaries()['1d']
     * @param array|null  $s1w     MarketStore::summaries()['1w']
     * @param array       $feat    self::features()
     * @param string|null $holding the CONFIRMED verdict — a confirmed MAJOR
     *                             holds while the weekly stands and the daily
     *                             has not turned against it
     * @param float|null  $fundingPct 30d funding percentile — reasons only
     * @return array{verdict:?string, reasons:string[]} verdict null = no read
     *         this pass (missing/stale input, thin weekly history)
     */
    public static function classify(?array $s1d, ?array $s1w, array $feat, ?string $holding = null, ?float $fundingPct = null): array
    {
        if ($s1d === null || $s1w === null) {
            return ['verdict' => null, 'reasons' => ['1d or 1w summary missing']];
        }
        if (!empty($s1d['stale']) || !empty($s1w['stale'])) {
            return ['verdict' => null, 'reasons' => ['1d or 1w summary stale']];
        }
        $bars = (int) ($s1w['candles_used'] ?? 0);
        if ($bars < (int) self::k('MIN_BARS_1W')) {
            return ['verdict' => null, 'reasons' => ["insufficient 1w history ($bars bars)"]];
        }

        $d = self::dir1d($s1d, $feat);
        $w = self::dir1w($s1w, $feat);
        $adx = self::f($s1d, 'adx14');
        $er = self::f($s1d, 'er20');
        $strong = ($adx !== null && $adx >= self::k('ADX_MIN_1D')) || ($er !== null && $er >= self::k('ER_MIN_1D'));

        $votes = $d['dir'] === 'down' ? $d['down'] : $d['up'];
        $slope = $feat['slope50_1d'] ?? null;
        $reasons = [sprintf(
            '1d %s %d/5 (EMA50 slope %s, ADX %s, ER %s)',
            $d['dir'],
            $votes,
            $slope === null ? 'n/a' : sprintf('%+.1f%%', $slope),
            $adx === null ? 'n/a' : sprintf('%.0f', $adx),
            $er === null ? 'n/a' : sprintf('%.2f', $er)
        ), "1w $w"];

        if ($holding === self::MAJOR_UP && $w === 'up' && $d['dir'] !== 'down') {
            $verdict = self::MAJOR_UP;
        } elseif ($holding === self::MAJOR_DOWN && $w === 'down' && $d['dir'] !== 'up') {
            $verdict = self::MAJOR_DOWN;
        } elseif ($d['dir'] === 'up' && $w !== 'down') {
            $verdict = $w === 'up' && $strong ? self::MAJOR_UP : self::UP_FORMING;
        } elseif ($d['dir'] === 'down' && $w !== 'up') {
            $verdict = $w === 'down' && $strong ? self::MAJOR_DOWN : self::DOWN_FORMING;
        } else {
            $verdict = self::NEUTRAL;
            if ($d['dir'] !== 'flat' && $w !== 'flat') {
                $reasons[] = '1d against 1w';
            }
        }
        if ($verdict === $holding && $d['dir'] === 'flat') {
            $reasons[] = 'held: weekly intact, daily not reversed';
        }

        if ($fundingPct !== null && $fundingPct >= self::k('FUNDING_CROWDED')) {
            $reasons[] = sprintf('funding p%.0f — crowded longs (context)', $fundingPct);
        } elseif ($fundingPct !== null && $fundingPct <= self::k('FUNDING_WASHED')) {
            $reasons[] = sprintf('funding p%.0f — washed out (context)', $fundingPct);
        }
        return ['verdict' => $verdict, 'reasons' => $reasons];
    }

    /**
     * Pass hysteresis. Passes accumulate while the raw read stays on the same
     * SIDE of the confirmed verdict (RANK order), and the newest read is the
     * candidate — so a tape oscillating between two reads that both say the
     * verdict is over still leaves it, instead of resetting each other
     * forever. A read back AT the verdict, or on the other side, resets. No
     * single-pass fast path (unlike TrendRegime's HOSTILE): this only warns,
     * and whipsaw is the failure mode.
     *
     * @param array{verdict?:?string, candidate?:?string, candidate_passes?:int} $state
     * @return array{verdict:?string, prev:?string, candidate:?string, candidate_passes:int, changed:bool}
     */
    public static function step(array $state, ?string $raw): array
    {
        $verdict = $state['verdict'] ?? null;
        $candidate = $state['candidate'] ?? null;
        $passes = (int) ($state['candidate_passes'] ?? 0);
        $out = static fn (?string $v, ?string $prev, ?string $c, int $n, bool $changed): array
            => ['verdict' => $v, 'prev' => $prev, 'candidate' => $c, 'candidate_passes' => $n, 'changed' => $changed];

        if ($raw === null || !isset(self::RANK[$raw])) {
            return $out($verdict, null, $candidate, $passes, false);
        }
        if ($raw === $verdict) {
            return $out($verdict, null, null, 0, false);
        }
        $passes = $candidate !== null && self::side($candidate, $verdict) === self::side($raw, $verdict) ? $passes + 1 : 1;
        if ($passes >= self::passesFor($raw)) {
            return $out($raw, $verdict, null, 0, true);
        }
        return $out($verdict, null, $raw, $passes, false);
    }

    private static function side(string $read, ?string $verdict): int
    {
        return $verdict === null || !isset(self::RANK[$verdict]) ? 0 : self::RANK[$read] <=> self::RANK[$verdict];
    }

    public static function passesFor(string $verdict): int
    {
        return (int) self::k(match ($verdict) {
            self::MAJOR_UP, self::MAJOR_DOWN => 'CONFIRM_PASSES_MAJOR',
            self::UP_FORMING, self::DOWN_FORMING => 'CONFIRM_PASSES_FORMING',
            default => 'CONFIRM_PASSES_NEUTRAL',
        });
    }

    /**
     * Did the call come true? $ret is the forward return as a fraction
     * (price_Nd / price_at - 1). FORMING and MAJOR share a rule and are
     * always REPORTED separately.
     */
    public static function hit(string $verdict, float $ret): ?bool
    {
        return match ($verdict) {
            self::MAJOR_UP, self::UP_FORMING => $ret > 0,
            self::MAJOR_DOWN, self::DOWN_FORMING => $ret < 0,
            self::NEUTRAL => abs($ret) * 100 < self::k('NEUTRAL_BAND_PCT'),
            default => null,
        };
    }

    /** drawdown-from-high buckets, the only sign-consistent long-horizon
     *  structure §16 found — CONTRARIAN, and ~3 cycles of evidence */
    public const CYCLE_NEAR_HIGH_PCT = 15.0;
    public const CYCLE_DEEP_PCT = 60.0;

    /**
     * Where price sits in the cycle. Context for a human, never a verdict
     * input: days within CYCLE_NEAR_HIGH_PCT of the high were followed by an
     * up 60d LESS often than base (−12pp BTC / −15pp BNB), days deeper than
     * CYCLE_DEEP_PCT MORE often (+15 / +23) — about three cycles, so n ≈ 3.
     *
     * @param array<int, mixed> $highs1w  stored weekly highs (MarketStore keeps 300 ≈ 5.7y — "the high", not provably the ATH)
     * @param array<int, mixed> $closes1d oldest-first daily closes (Mayer = price / 200d SMA)
     * @return array{high:float, drawdown_pct:float, bucket:string, mayer:?float, weeks:int}|null
     */
    public static function cycle(array $highs1w, array $closes1d, float $price): ?array
    {
        if (!$highs1w || $price <= 0) {
            return null;
        }
        $high = max(array_map('floatval', $highs1w));
        if ($high <= 0) {
            return null;
        }
        $dd = round(max(0.0, (1 - $price / $high) * 100), 1);
        $closes1d = array_values(array_map('floatval', $closes1d));
        $mayer = count($closes1d) >= 200 ? round($price / (array_sum(array_slice($closes1d, -200)) / 200), 2) : null;
        $bucket = $dd < self::k('CYCLE_NEAR_HIGH_PCT') ? 'near_high' : ($dd > self::k('CYCLE_DEEP_PCT') ? 'deep_drawdown' : 'mid');
        return ['high' => $high, 'drawdown_pct' => $dd, 'bucket' => $bucket, 'mayer' => $mayer, 'weeks' => count($highs1w)];
    }

    /** closed days the cold-start replay walks — far past the six closes a
     *  verdict needs, so the null start washes out */
    public const SEED_DAYS = 90;
    /** one hourly pass per hour of a daily close, as the sweep counts them */
    public const PASSES_PER_CLOSE = 24;

    /**
     * Cold start: the verdict the cron WOULD hold today had it been running,
     * recovered by walking the last SEED_DAYS closed days of the stored tapes
     * through classify() + step(), PASSES_PER_CLOSE passes per close (the
     * sweep's method, scripts/outlook-sweep.php). Without it a new symbol —
     * or a fresh deploy — sits blank for six days and then "detects" a trend
     * that has been running for months.
     *
     * Each day sees what the cron would have seen then: the dailies up to that
     * close, the weekly bars up to that week with the in-progress week closed
     * at that day. Today's in-progress daily is never read. Approximate by
     * design: the live summary is computed over 1000 fetched bars, the replay
     * over the ≤300 MarketStore keeps (EMA200 is the only input that notices).
     *
     * @param array<int, array{high:mixed, low:mixed, close:mixed}> $c1d stored dailies, oldest first, the last = today (UTC day of $now)
     * @param array<int, array{high:mixed, low:mixed, close:mixed}> $c1w stored weeklies, oldest first, the last = this week (Monday-anchored)
     * @return array{verdict:?string, since_ts:?int, price_at_verdict:?float, candidate:?string, candidate_passes:int, days:int}
     */
    public static function seed(array $c1d, array $c1w, int $now, int $days = self::SEED_DAYS): array
    {
        $c1d = array_values($c1d);
        $c1w = array_values($c1w);
        $n = count($c1d);
        $nw = count($c1w);
        $today = intdiv($now, 86400);
        $weekOf = static fn (int $day): int => intdiv($day + 3, 7); // 1970-01-01 was a Thursday
        $state = [];
        $since = $priceAt = null;
        $walked = 0;
        for ($k = min($days, $n - 1); $k >= 1; $k--) {
            $j = $n - 1 - $k;
            $keepW = $nw - ($weekOf($today) - $weekOf($today - $k));
            if ($keepW < 1) {
                continue;
            }
            $d = array_slice($c1d, 0, $j + 1);
            $w = array_slice($c1w, 0, $keepW);
            $w[$keepW - 1]['close'] = $c1d[$j]['close'];
            $cl1d = array_column($d, 'close');
            $cl1w = array_column($w, 'close');
            $price = (float) $c1d[$j]['close'];
            $s1d = [
                'price' => $price,
                'ema20' => Indicators::ema($cl1d, 20), 'ema50' => Indicators::ema($cl1d, 50), 'ema200' => Indicators::ema($cl1d, 200),
                'adx14' => Indicators::adx(array_column($d, 'high'), array_column($d, 'low'), $cl1d, 14),
                'er20' => Indicators::efficiencyRatio($cl1d, 20),
                'candles_used' => count($d),
            ];
            $s1w = ['price' => $price, 'ema20' => Indicators::ema($cl1w, 20), 'ema50' => Indicators::ema($cl1w, 50), 'candles_used' => count($w)];
            $raw = self::classify($s1d, $s1w, self::features($cl1d, $cl1w), $state['verdict'] ?? null)['verdict'];
            $walked++;
            for ($p = 0; $p < (int) self::k('PASSES_PER_CLOSE'); $p++) {
                $state = self::step($state, $raw);
                if ($state['changed']) {
                    $since = ($today - $k + 1) * 86400; // day j closed at the next midnight
                    $priceAt = $price;
                }
            }
        }
        return [
            'verdict' => $state['verdict'] ?? null,
            'since_ts' => $since,
            'price_at_verdict' => $priceAt,
            'candidate' => $state['candidate'] ?? null,
            'candidate_passes' => (int) ($state['candidate_passes'] ?? 0),
            'days' => $walked,
        ];
    }

    public static function direction(?string $verdict): int
    {
        return isset(self::RANK[$verdict ?? '']) ? self::RANK[$verdict] <=> 0 : 0;
    }

    private static function f(array $s, string $key): ?float
    {
        return isset($s[$key]) ? (float) $s[$key] : null;
    }
}
