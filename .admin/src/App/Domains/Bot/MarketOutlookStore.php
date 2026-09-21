<?php

namespace App\Domains\Bot;

use App\Config;
use App\ConfigQuery;
use App\MarketOutlook as OutlookRow;
use App\MarketOutlookPeer;
use App\MarketOutlookQuery;
use App\MarketOutlookState;
use App\MarketOutlookStatePeer;
use App\MarketOutlookStateQuery;

/**
 * Persistence, scoring and wording for the long-horizon outlook
 * (MarketOutlook is the pure classifier; bin/gtbot-outlook is the cron).
 *
 * market_outlook_state holds the confirmed verdict + hysteresis counters per
 * symbol; market_outlook is the append-only log — one Change row per
 * confirmed change, one Daily row per symbol per UTC day — and every row is
 * scored at +7d / +30d from the stored daily bars. The log is the source of
 * truth; Telegram is a courtesy. Nothing is journaled to bot_event: that
 * table hangs off a run, and the market has none.
 *
 * This class RETURNS the messages; the cron sends them. Every DB read of a
 * row another process writes goes through select() — Propel never
 * re-hydrates a pooled object (see MarketStore::rows).
 *
 * Wording rule (refit-evidence §16): the outlook DETECTS, it does not
 * forecast, and no long-horizon state tested had directional skill. Messages
 * describe the tape and carry the live record; they never say what comes next.
 */
final class MarketOutlookStore
{
    public const CONFIG_ALERTS = 'gtbot_outlook_alerts';
    public const CONFIG_DIGEST = 'gtbot_outlook_digest_last';
    /** a 1d/1w summary older than this is not read (collector runs every 10 min) */
    public const STALE_AFTER = 3600;

    private const STATE_COLS = ['Symbol', 'Verdict', 'VerdictSince', 'PriceAtVerdict', 'Candidate', 'CandidatePasses', 'LastRaw', 'LastPassAt'];

    /** MarketStore inputs for one symbol. @return array{s1d:?array, s1w:?array, c1d:array, c1w:array} */
    public static function inputs(string $symbol): array
    {
        $sums = MarketStore::summaries($symbol, self::STALE_AFTER);
        return [
            's1d' => $sums['1d'] ?? null,
            's1w' => $sums['1w'] ?? null,
            'c1d' => MarketStore::candles($symbol, '1d'),
            'c1w' => MarketStore::candles($symbol, '1w'),
        ];
    }

    /**
     * One hourly pass for one symbol: classify, advance the hysteresis, and
     * on a confirmed change write the Change row with the state update in one
     * transaction.
     *
     * @param array|null $in  self::inputs() shape — injected by tests and --fetch
     * @param bool       $dry compute only: no write, and no message
     * @return array{symbol:string, raw:?string, verdict:?string, changed:bool, reasons:string[], candidate:?string, candidate_passes:int, message:?string}
     */
    public static function pass(string $symbol, int $now, ?array $in = null, bool $dry = false): array
    {
        $in ??= self::inputs($symbol);
        $state = self::state($symbol) ?? [];
        // cold start (new symbol, fresh deploy): replay the stored tape
        // instead of sitting blank for six days — only off a fresh tape, and
        // only a replay that reached a verdict replaces the live counters
        $seed = null;
        if (($state['verdict'] ?? null) === null && $in['s1d'] !== null && $in['s1w'] !== null
            && empty($in['s1d']['stale']) && empty($in['s1w']['stale'])) {
            $seed = MarketOutlook::seed($in['c1d'], $in['c1w'], $now);
            if ($seed['verdict'] !== null) {
                $state = ['verdict' => $seed['verdict'], 'candidate' => $seed['candidate'], 'candidate_passes' => $seed['candidate_passes']];
            } else {
                $seed = null;
            }
        }
        $feat = MarketOutlook::features(array_column($in['c1d'], 'close'), array_column($in['c1w'], 'close'));
        $read = MarketOutlook::classify($in['s1d'], $in['s1w'], $feat, $state['verdict'] ?? null, $in['s1d']['funding_pct'] ?? null);
        $next = MarketOutlook::step($state, $read['verdict']);
        $price = (float) ($in['s1d']['price'] ?? 0);
        $out = [
            'symbol' => $symbol, 'raw' => $read['verdict'], 'verdict' => $next['verdict'], 'changed' => $next['changed'],
            'reasons' => $read['reasons'], 'candidate' => $next['candidate'], 'candidate_passes' => $next['candidate_passes'], 'message' => null,
        ];
        if ($dry || $read['verdict'] === null) {
            return $out; // no read this pass: counters stand, last_pass_at does not move
        }

        $cycle = self::cycleOf($in, $price);
        $stamp = date('Y-m-d H:i:s', $now);
        $con = \Propel::getConnection();
        $con->beginTransaction();
        try {
            MarketOutlookStatePeer::clearInstancePool();
            $row = MarketOutlookStateQuery::create()->filterBySymbol($symbol)->findOne() ?? (new MarketOutlookState())->setSymbol($symbol);
            $row->setCandidate($next['candidate']);
            $row->setCandidatePasses($next['candidate_passes']);
            $row->setLastRaw($read['verdict']);
            $row->setLastPassAt($stamp);
            if ($seed !== null) {
                // a replayed state, not a call: no message, never scored,
                // outside record() — the Seed row only says where it came from
                $since = date('Y-m-d H:i:s', $seed['since_ts'] ?? $now);
                $row->setVerdict($seed['verdict']);
                $row->setVerdictSince($since);
                $row->setPriceAtVerdict((string) ($seed['price_at_verdict'] ?? $price));
                self::log($symbol, 'Seed', $seed['verdict'], null, $price, $stamp, ['replayed_days' => $seed['days'], 'since' => $since, 'price_at_verdict' => $seed['price_at_verdict']], 'Seed');
            }
            if ($next['changed']) {
                $row->setVerdict($next['verdict']);
                $row->setVerdictSince($stamp);
                $row->setPriceAtVerdict((string) $price);
                self::log($symbol, 'Change', $next['verdict'], $next['prev'], $price, $stamp, ['reasons' => $read['reasons'], 'features' => $feat, 'cycle' => $cycle]);
            }
            $row->save();
            $con->commit();
        } catch (\Throwable $e) {
            $con->rollBack();
            throw $e;
        }
        if ($next['changed'] && self::shouldNotify(self::alertLevel(), $next['prev'], $next['verdict'])) {
            $out['message'] = self::changeMessage($symbol, $next['prev'], $next['verdict'], $price, $read['reasons'], $cycle);
        }
        return $out;
    }

    /** off: never · major: a MAJOR on either side of the change · all: every confirmed change */
    public static function shouldNotify(string $level, ?string $prev, string $verdict): bool
    {
        $major = static fn (?string $v): bool => abs(MarketOutlook::RANK[$v ?? ''] ?? 0) === 2;
        return match ($level) {
            'all' => true,
            'major' => $major($prev) || $major($verdict),
            default => false,
        };
    }

    public static function alertLevel(): string
    {
        $v = strtolower(trim((string) (ConfigQuery::create()->filterByConfig(self::CONFIG_ALERTS)->select(['Value'])->findOne() ?? '')));
        return in_array($v, ['major', 'all'], true) ? $v : 'off';
    }

    /**
     * Once per UTC day (first pass of the day, i.e. just after the daily
     * close): one Daily row per symbol that has a verdict, and one digest
     * line. Returns the message, or null when not due / nothing to say /
     * alerts are off (the Daily rows are written regardless of the level).
     *
     * @param string[] $symbols
     * @param array<string, array>|null $inputs symbol => self::inputs() shape (tests)
     */
    public static function digest(array $symbols, int $now, ?array $inputs = null, bool $dry = false): ?string
    {
        $today = gmdate('Y-m-d', $now);
        $last = (string) (ConfigQuery::create()->filterByConfig(self::CONFIG_DIGEST)->select(['Value'])->findOne() ?? '');
        if ($last === $today) {
            return null;
        }
        $lines = [];
        $stamp = date('Y-m-d H:i:s', $now);
        foreach ($symbols as $symbol) {
            $state = self::state($symbol);
            $in = $inputs[$symbol] ?? self::inputs($symbol);
            $price = (float) ($in['s1d']['price'] ?? 0);
            if ($state === null || $state['verdict'] === null || $price <= 0 || !empty($in['s1d']['stale'])) {
                continue;
            }
            $cycle = self::cycleOf($in, $price);
            if (!$dry) {
                self::log($symbol, 'Daily', $state['verdict'], null, $price, $stamp, ['cycle' => $cycle]);
            }
            $lines[] = sprintf(
                '%s %s %s%s%s',
                preg_replace('/USDT$/', '', $symbol),
                $state['verdict'],
                self::age($now - (int) $state['since_ts']),
                $state['price_at_verdict'] > 0 ? sprintf(' (%+.1f%% since)', ($price / $state['price_at_verdict'] - 1) * 100) : '',
                $cycle ? ' · ' . self::cycleText($cycle) : ''
            );
        }
        if (!$dry) {
            \App\ConfigPeer::clearInstancePool();
            $c = ConfigQuery::create()->findOneByConfig(self::CONFIG_DIGEST) ?? (new Config())->setConfig(self::CONFIG_DIGEST);
            $c->setValue($today);
            $c->save();
        }
        if (!$lines || self::alertLevel() === 'off') {
            return null;
        }
        return "gtbot outlook $today (detection, not a forecast)\n" . implode("\n", $lines) . "\n" . self::recordText(self::record());
    }

    /**
     * Fill the 7d / 30d outcomes of every unscored row from the stored daily
     * bars — no API call. MarketStore keeps 300 contiguous UTC days, the last
     * being today's in-progress bar, so day D sits at n-1-(today-D). A row is
     * only scored off a FRESH 1d series (a frozen tape would index the wrong
     * days), and only from COMPLETED days.
     *
     * @param callable|null $bars fn(string $symbol): ?array — [h,l,c] dailies, null = not fresh (tests)
     * @return int rows advanced
     */
    public static function scorePending(int $now, ?callable $bars = null): int
    {
        $bars ??= static function (string $symbol): ?array {
            $s = MarketStore::summaries($symbol, self::STALE_AFTER)['1d'] ?? null;
            return $s === null || !empty($s['stale']) ? null : MarketStore::candles($symbol, '1d');
        };
        $pending = MarketOutlookQuery::create()
            ->filterByEvalStatus(['Scored', 'Seed'], \Criteria::NOT_IN)
            ->select(['IdMarketOutlook', 'Symbol', 'Verdict', 'PriceAt', 'CalledAt', 'EvalStatus'])
            ->find()->getArrayCopy();
        $today = intdiv($now, 86400);
        $cache = [];
        $done = 0;
        foreach ($pending as $p) {
            $callDay = intdiv((int) strtotime((string) $p['CalledAt']), 86400);
            $age = $today - $callDay;
            $want = $p['EvalStatus'] === 'Pending' ? 7 : 30;
            if ($age <= $want) {
                continue; // day callDay+want has not closed yet
            }
            $symbol = (string) $p['Symbol'];
            $c = $cache[$symbol] ??= $bars($symbol) ?? false;
            if ($c === false) {
                continue;
            }
            $n = count($c);
            $at = static fn (int $day): ?array => $c[$n - 1 - ($today - $day)] ?? null;
            $priceAt = (float) $p['PriceAt'];
            $verdict = (string) $p['Verdict'];
            MarketOutlookPeer::clearInstancePool();
            $row = MarketOutlookQuery::create()->findPk((int) $p['IdMarketOutlook']);
            if ($row === null || $priceAt <= 0) {
                continue;
            }
            if ($row->getEvalStatus() === 'Pending' && ($b = $at($callDay + 7)) !== null) {
                $ret = (float) $b['close'] / $priceAt - 1;
                $row->setPrice7d((string) $b['close']);
                $row->setRet7d((string) round($ret * 100, 4));
                $row->setHit7d($verdict === MarketOutlook::NEUTRAL ? null : MarketOutlook::hit($verdict, $ret));
                $row->setEvalStatus('Partial');
            } elseif ($row->getEvalStatus() === 'Pending') {
                $row->setEvalStatus('Partial'); // the 7d bar has left the stored window: skip it, still try 30d
            }
            if ($age > 30) {
                $b = $at($callDay + 30);
                if ($b !== null) {
                    $ret = (float) $b['close'] / $priceAt - 1;
                    $row->setPrice30d((string) $b['close']);
                    $row->setRet30d((string) round($ret * 100, 4));
                    $row->setHit30d(MarketOutlook::hit($verdict, $ret));
                    $dir = MarketOutlook::direction($verdict);
                    $worst = null;
                    for ($d = $callDay + 1; $dir !== 0 && $d <= $callDay + 30; $d++) {
                        if (($w = $at($d)) !== null) {
                            $x = $dir > 0 ? (float) $w['low'] / $priceAt - 1 : -((float) $w['high'] / $priceAt - 1);
                            $worst = $worst === null ? $x : min($worst, $x);
                        }
                    }
                    $row->setMaxAdversePct($worst === null ? null : (string) round($worst * 100, 4));
                }
                $row->setEvalStatus('Scored');
                $row->setScoredAt(date('Y-m-d H:i:s', $now));
            }
            $row->save();
            $done++;
        }
        return $done;
    }

    /**
     * Live track record of confirmed CHANGE calls, all symbols together, by
     * verdict: hits / scored at 30d. Daily rows are excluded — they overlap.
     * @return array<string, array{hits:int, n:int}>
     */
    public static function record(): array
    {
        $rows = MarketOutlookQuery::create()
            ->filterByKind('Change')
            ->filterByHit30d(null, \Criteria::ISNOTNULL)
            ->select(['Verdict', 'Hit30d'])
            ->find()->getArrayCopy();
        $out = [];
        foreach ($rows as $r) {
            $v = (string) $r['Verdict'];
            $out[$v] ??= ['hits' => 0, 'n' => 0];
            $out[$v]['n']++;
            $out[$v]['hits'] += (int) (bool) $r['Hit30d'];
        }
        return $out;
    }

    /**
     * The routine brief's `outlook` block. Context only — see NOTE.
     * @param string[] $symbols
     */
    public static function brief(array $symbols, int $now): array
    {
        $record = self::record();
        $out = ['note' => self::NOTE, 'symbols' => []];
        foreach ($symbols as $symbol) {
            $state = self::state($symbol);
            if ($state === null) {
                continue;
            }
            $in = self::inputs($symbol);
            $price = (float) ($in['s1d']['price'] ?? 0);
            $v = $state['verdict'];
            $out['symbols'][$symbol] = [
                'verdict' => $v,
                'since' => $state['since_ts'] ? gmdate('Y-m-d H:i', (int) $state['since_ts']) . 'Z' : null,
                'age_h' => $state['since_ts'] ? (int) round(($now - (int) $state['since_ts']) / 3600) : null,
                'price_at_verdict' => $state['price_at_verdict'] ?: null,
                'move_since_pct' => $state['price_at_verdict'] > 0 && $price > 0 ? round(($price / $state['price_at_verdict'] - 1) * 100, 2) : null,
                'candidate' => $state['candidate'] ? [
                    'verdict' => $state['candidate'],
                    'passes' => $state['candidate_passes'],
                    'needs' => MarketOutlook::passesFor($state['candidate']),
                ] : null,
                'cycle' => $price > 0 ? self::cycleOf($in, $price) : null,
                'record_30d' => $v !== null && isset($record[$v]) ? $record[$v] : ['hits' => 0, 'n' => 0],
            ];
        }
        return $out;
    }

    public const NOTE = 'DETECTION, not forecast. Backtest (refit-evidence §16): no long-horizon state predicts 30-90d direction; MAJOR_DOWN days were followed by UP slightly more often than base. Context only — must not move deploy, gates or overrides.';

    /** @return array{verdict:?string, since_ts:?int, price_at_verdict:float, candidate:?string, candidate_passes:int}|null */
    public static function state(string $symbol): ?array
    {
        $r = MarketOutlookStateQuery::create()->filterBySymbol($symbol)->select(self::STATE_COLS)->findOne();
        if (!$r) {
            return null;
        }
        return [
            'verdict' => $r['Verdict'] !== null ? (string) $r['Verdict'] : null,
            'since_ts' => $r['VerdictSince'] !== null ? (int) strtotime((string) $r['VerdictSince']) : null,
            'price_at_verdict' => (float) ($r['PriceAtVerdict'] ?? 0),
            'candidate' => $r['Candidate'] !== null ? (string) $r['Candidate'] : null,
            'candidate_passes' => (int) $r['CandidatePasses'],
        ];
    }

    private static function log(string $symbol, string $kind, string $verdict, ?string $prev, float $price, string $stamp, array $detail, string $eval = 'Pending'): void
    {
        $row = new OutlookRow();
        $row->setSymbol($symbol);
        $row->setKind($kind);
        $row->setVerdict($verdict);
        $row->setPrevVerdict($prev);
        $row->setPriceAt((string) $price);
        $row->setCalledAt($stamp);
        $row->setDetail(json_encode($detail, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $row->setEvalStatus($eval);
        $row->save();
    }

    private static function cycleOf(array $in, float $price): ?array
    {
        return MarketOutlook::cycle(array_column($in['c1w'], 'high'), array_column($in['c1d'], 'close'), $price);
    }

    public static function cycleText(array $cycle): string
    {
        $note = ['near_high' => ' — near highs', 'deep_drawdown' => ' — deep drawdown', 'mid' => ''][$cycle['bucket']] ?? '';
        return sprintf(
            '%.0f%% below %.1fy high%s%s',
            $cycle['drawdown_pct'],
            $cycle['weeks'] / 52.18,
            $cycle['mayer'] !== null ? sprintf(', Mayer %.2f', $cycle['mayer']) : '',
            $note
        );
    }

    public static function changeMessage(string $symbol, ?string $prev, string $verdict, float $price, array $reasons, ?array $cycle): string
    {
        $what = [
            MarketOutlook::MAJOR_UP => 'major UPTREND detected — 1d and 1w agree',
            MarketOutlook::MAJOR_DOWN => 'major DOWNTREND detected — 1d and 1w agree',
            MarketOutlook::UP_FORMING => 'uptrend forming — daily turned up, weekly not confirmed',
            MarketOutlook::DOWN_FORMING => 'downtrend forming — daily turned down, weekly not confirmed',
            MarketOutlook::NEUTRAL => 'no long-term trend — 1d and 1w no longer agree',
        ][$verdict] ?? $verdict;
        $lines = [
            sprintf('%s outlook: %s → %s @ %s', $symbol, $prev ?? 'none', $verdict, rtrim(rtrim(number_format($price, 2, '.', ''), '0'), '.')),
            ucfirst($what) . '.',
            implode('; ', $reasons),
        ];
        if ($cycle) {
            $lines[] = 'Cycle: ' . self::cycleText($cycle);
        }
        $lines[] = self::recordText(self::record(), $verdict);
        $lines[] = 'Detection, not a forecast — nothing in the fleet acts on this.';
        return implode("\n", $lines);
    }

    /** "Live record @30d: MAJOR_UP 3/5" — one verdict, or all that have a score. */
    public static function recordText(array $record, ?string $only = null): string
    {
        $parts = [];
        foreach ($record as $v => $r) {
            if ($only === null || $only === $v) {
                $parts[] = sprintf('%s %d/%d', $v, $r['hits'], $r['n']);
            }
        }
        return 'Live record @30d: ' . ($parts ? implode(', ', $parts) : 'none scored yet');
    }

    private static function age(int $seconds): string
    {
        return $seconds >= 86400 ? intdiv($seconds, 86400) . 'd' : max(0, intdiv($seconds, 3600)) . 'h';
    }
}
