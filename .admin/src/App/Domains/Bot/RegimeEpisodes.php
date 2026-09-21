<?php

namespace App\Domains\Bot;

use App\ConfigQuery;
use App\Domains\Bot\Engine\TrendEngine;
use App\FleetSlot;
use App\GridRunQuery;
use App\RegimeEpisode;
use App\RegimeEpisodeQuery;
use App\TradeCycleQuery;
use Criteria;

/**
 * The regime-episode ledger: open, sample, close, and the one alert a
 * symbol gets when the pool sits idle through a leg (correction D4).
 *
 * WHY. Nothing in the fleet measured how much of the pool was engaged per
 * symbol, nor what a TREND_UP episode captured against simply holding the
 * coin. Every case of "the arm sat out a leg" to date — edf3c14, 110bd8c,
 * 524ad30, and the BNB arm parked by hand over 2026-09-15..18 — was found
 * by the operator by eye, after the leg, from a chart. An episode row makes
 * it a journaled number instead, and the alert makes it arrive during the
 * leg rather than after it.
 *
 * LIFECYCLE. One open episode per slot at a time:
 *
 *   open()    the slot's verdict ENTERS TREND_UP (TrendActivator::
 *             recordVerdict, the same transition that stamps
 *             fleet_slot.episode_started_at). Anchors price_open.
 *   sample()  every gtbot-allocate pass (~15 min) folds the symbol's
 *             engagement into engaged_pct_tw, and counts the consecutive
 *             sub-floor passes the alert is spoken on.
 *   close()   the verdict LEAVES TREND_UP. Stamps price_close, the realized
 *             PnL booked inside the window, the mark-to-market of whatever
 *             position is still open, the HODL move over exactly the same
 *             window, and the fraction of that move the arm captured.
 *
 * The alert fires REGARDLESS OF CAUSE. A slice parked on purpose is still a
 * slice the pool is not using, and the operator asked to be told about the
 * BNB shape whether or not the machine considers it explained — so a
 * deliberately Halted arm gets the same one alert per episode that an
 * accident does, with the cause named rather than used as an excuse to stay
 * quiet.
 */
final class RegimeEpisodes
{
    /** bot_event kind of the one alert per episode. */
    public const KIND_IDLE = 'opportunity_idle';

    /** Engagement (%) below which a symbol counts as idle. */
    public const CONFIG_FLOOR = 'gtbot_engagement_floor_pct';
    public const DEFAULT_FLOOR_PCT = '40';
    /** Consecutive sub-floor samples before the alert speaks (8 × 15 min = 2 h). */
    public const CONFIG_IDLE_PASSES = 'gtbot_engagement_idle_passes';
    public const DEFAULT_IDLE_PASSES = 8;

    private const SCALE = 8;
    private const PCT_SCALE = 4;
    /** hodl_pct / captured_pct are decimal(8,4): a wilder ratio is clamped, never a DB error. */
    private const PCT_MAX = '9999.9999';

    // ── open / close ────────────────────────────────────────────────────

    /** The slot's open episode, or null. */
    public static function openFor(FleetSlot $slot): ?RegimeEpisode
    {
        return RegimeEpisodeQuery::create()
            ->filterByIdFleetSlot((int) $slot->getIdFleetSlot())
            ->filterByClosedAt(null, Criteria::ISNULL)
            ->orderByIdRegimeEpisode(Criteria::DESC)
            ->findOne();
    }

    /**
     * Open an episode for the slot. Idempotent: a slot that already has one
     * open keeps it, so a second TREND_UP pass (or a re-run of the cron in
     * the same minute) cannot split one leg into two rows.
     *
     * A price is required — the whole point of the row is to compare the
     * arm against the move over its own window, and an episode with no
     * anchor could never answer that. When the caller has none the freshest
     * stored mark is used; with no mark at all no row is written.
     */
    public static function open(FleetSlot $slot, string $verdict, ?string $price, ?string $at = null): ?RegimeEpisode
    {
        $existing = self::openFor($slot);
        if ($existing !== null) {
            return $existing;
        }
        $price ??= self::markPrice((string) $slot->getSymbol());
        if ($price === null || bccomp($price, '0', self::SCALE) <= 0) {
            return null;
        }
        $ep = new RegimeEpisode();
        $ep->setSymbol((string) $slot->getSymbol());
        $ep->setAlgo((string) $slot->getAlgo());
        $ep->setIdFleetSlot((int) $slot->getIdFleetSlot());
        $ep->setIdGridRun($slot->getIdGridRun() !== null ? (int) $slot->getIdGridRun() : null);
        $ep->setVerdict($verdict);
        $ep->setOpenedAt($at ?? date('Y-m-d H:i:s'));
        $ep->setPriceOpen($price);
        $ep->setSamples(0);
        $ep->setRealized('0');
        $ep->setIdleSamples(0);
        $ep->save();
        return $ep;
    }

    /**
     * Adopt a leg that was ALREADY RUNNING when this shipped.
     *
     * open() is bound to the TREND_UP *entry* transition, and on the fleet
     * this lands on both arms have been TREND_UP for days — so binding to the
     * entry alone would leave the whole correction dark until each symbol
     * left the regime and came back, which is precisely the situation it
     * exists for. Any pass that finds episode_started_at set (Task 3 stamps
     * it on the same transition) with no open row behind it opens one.
     *
     * opened_at is the slot's real episode start, so engagement samples and
     * the idle alert are scoped to the leg that is actually running.
     *
     * price_open, however, is A CURRENT MARK, NOT THE PRICE AT THE START OF
     * THE LEG — nothing stored the leg's opening price before this table
     * existed. hodl_pct and captured_pct on a backfilled row therefore
     * measure the move from the *adoption* point onward, not the whole leg,
     * and understate a leg that had already run. Only rows whose opened_at
     * equals their creation moment are a full-leg reading; every episode
     * opened from the entry transition (all of them, once these two adopted
     * legs close) is one.
     *
     * Idempotent: a slot with an open row is left exactly as it is.
     */
    public static function backfill(FleetSlot $slot, ?string $price = null): ?RegimeEpisode
    {
        $since = $slot->getEpisodeStartedAt('Y-m-d H:i:s');
        if ($since === null || $since === '') {
            return null;
        }
        if (self::openFor($slot) !== null) {
            return null;
        }
        return self::open($slot, (string) ($slot->getLastVerdict() ?: 'TREND_UP'), $price, $since);
    }

    /**
     * Close the slot's open episode and score it.
     *
     *   realized    every trade_cycle of the episode's run booked since it
     *               opened — the money the arm actually took off the table.
     *   mtm_close   the open position marked at the closing price
     *               (engine_state qty/entry) — what it is still holding.
     *   hodl_pct    the move over exactly the same window, the same
     *               last/first ratio NavLedger's HODL benchmark uses.
     *   captured_pct (realized + mtm) ÷ (target_slice × hodl move). 100 =
     *               the arm made what holding the whole slice would have.
     *               NULL when the move was flat or down: a TREND_UP episode
     *               that closed lower has no HODL to capture, and dividing
     *               by it would print a sign-flipped nonsense. The negative
     *               move is still journaled in hodl_pct.
     */
    public static function close(FleetSlot $slot, ?string $price, ?string $at = null): ?RegimeEpisode
    {
        $ep = self::openFor($slot);
        if ($ep === null) {
            return null;
        }
        $runId = $ep->getIdGridRun() !== null
            ? (int) $ep->getIdGridRun()
            : ($slot->getIdGridRun() !== null ? (int) $slot->getIdGridRun() : null);
        $realized = $runId === null ? '0' : self::realizedSince($runId, (string) $ep->getOpenedAt('Y-m-d H:i:s'));
        $ep->setRealized($realized);
        $ep->setClosedAt($at ?? date('Y-m-d H:i:s'));

        $price ??= self::markPrice((string) $ep->getSymbol());
        if ($price !== null && bccomp($price, '0', self::SCALE) > 0) {
            $mtm = $runId === null ? '0' : self::markToMarket($runId, $price);
            $ep->setPriceClose($price);
            $ep->setMtmClose($mtm);
            $open = (string) $ep->getPriceOpen();
            if (bccomp($open, '0', self::SCALE) > 0) {
                $move = bcdiv(bcsub($price, $open, self::SCALE), $open, self::SCALE + self::PCT_SCALE);
                $ep->setHodlPct(self::clampPct(bcmul($move, '100', self::PCT_SCALE)));
                // effectiveTarget, not the raw column: since 2026-09-21 a
                // reconciled slot stores 0 = "follow gtbot_trend_target_slice"
                // (FleetSlots::reconcileFromRuns), and a 0 denominator here
                // silently dropped captured_pct for every machine-made slot
                $target = TrendActivator::effectiveTarget($slot);
                if (bccomp($move, '0', self::SCALE) > 0 && bccomp($target, '0', self::SCALE) > 0) {
                    $captured = bcdiv(
                        bcadd($realized, $mtm, self::SCALE),
                        bcmul($target, $move, self::SCALE),
                        self::SCALE + self::PCT_SCALE
                    );
                    $ep->setCapturedPct(self::clampPct(bcmul($captured, '100', self::PCT_SCALE)));
                }
            }
        }
        $ep->save();
        return $ep;
    }

    // ── sampling + the alert ────────────────────────────────────────────

    /**
     * Fold one engagement reading into every OPEN episode, and speak up when
     * a symbol has been under the floor long enough.
     *
     * "Time-weighted" here means equal weight per sample: the allocator cron
     * is fixed at 15 minutes, so a plain running mean over `samples` IS the
     * time weighting — no per-sample duration has to be stored, and a missed
     * cron pass simply contributes nothing rather than skewing a weight.
     *
     * @param array $engagement Engagement::compute() output
     * @return list<array{symbol: string, pct: string, samples: int, tw: string, idle: int, alerted: bool}>
     */
    public static function sample(array $engagement, ?TelegramNotifier $notifier = null): array
    {
        $floor = self::floorPct();
        $passes = self::idlePasses();
        $report = [];
        foreach (RegimeEpisodeQuery::create()
            ->filterByClosedAt(null, Criteria::ISNULL)
            ->orderByIdRegimeEpisode()
            ->find() as $ep) {
            $symbol = (string) $ep->getSymbol();
            $pct = (string) ($engagement['per_symbol'][$symbol]['pct'] ?? '0');
            $n = (int) $ep->getSamples() + 1;
            $prev = (string) ($ep->getEngagedPctTw() ?? '0');
            $tw = bcdiv(
                bcadd(bcmul($prev, (string) ($n - 1), self::SCALE), $pct, self::SCALE),
                (string) $n,
                self::PCT_SCALE
            );
            $ep->setSamples($n);
            $ep->setEngagedPctTw(self::clampPct($tw));

            $below = bccomp($pct, $floor, self::PCT_SCALE) < 0;
            $idle = $below ? (int) $ep->getIdleSamples() + 1 : 0;
            $ep->setIdleSamples($idle);
            $alerted = false;
            if ($below && $idle >= $passes && $ep->getIdleAlertedAt() === null) {
                $alerted = self::alert($ep, $pct, $floor, $passes, $notifier);
            }
            $ep->save();
            $report[] = ['symbol' => $symbol, 'pct' => $pct, 'samples' => $n, 'tw' => $tw, 'idle' => $idle, 'alerted' => $alerted];
        }
        return $report;
    }

    /**
     * One Alert per episode. Returns false (and stamps nothing) when there
     * is no run anywhere to hang a bot_event off — the fleet is empty, and
     * the next pass with a run gets to say it instead of losing the alert.
     */
    private static function alert(RegimeEpisode $ep, string $pct, string $floor, int $passes, ?TelegramNotifier $notifier): bool
    {
        $slot = $ep->getIdFleetSlot() !== null ? \App\FleetSlotQuery::create()->findPk((int) $ep->getIdFleetSlot()) : null;
        $causes = $slot !== null ? self::causes($slot) : ['slot empty'];
        if ($causes === []) {
            // Deliberate: the alert is about the MONEY, not about a fault.
            // A Live, funded arm that simply has not entered is still a
            // slice the pool is not using through a confirmed uptrend.
            $causes = ['no visible cause — the arm is Live and sized but has not deployed'];
        }
        $runId = $slot?->getIdGridRun() ?? $ep->getIdGridRun();
        if ($runId === null) {
            $host = FleetSlots::hostRun();
            $runId = $host !== null ? (int) $host->getIdGridRun() : null;
        }
        if ($runId === null) {
            return false;
        }
        $msg = sprintf(
            '%s has been %s%% engaged — under the %s%% floor for %d consecutive passes (%s) of this %s episode. Cause: %s',
            RunFactory::base((string) $ep->getSymbol()),
            rtrim(rtrim($pct, '0'), '.') ?: '0',
            $floor,
            $passes,
            self::humanWindow($passes),
            (string) $ep->getVerdict(),
            implode('; ', $causes)
        );
        (new EventLog((int) $runId, false, $notifier))->write('Alert', self::KIND_IDLE, $msg, [
            'symbol' => (string) $ep->getSymbol(),
            'episode' => (int) $ep->getIdRegimeEpisode(),
            'slot' => $ep->getIdFleetSlot() !== null ? (int) $ep->getIdFleetSlot() : null,
            'run' => (int) $runId,
            'engaged_pct' => $pct,
            'engaged_pct_tw' => (string) ($ep->getEngagedPctTw() ?? '0'),
            'floor_pct' => $floor,
            'passes' => $passes,
            'causes' => $causes,
        ]);
        $ep->setIdleAlertedAt(date('Y-m-d H:i:s'));
        return true;
    }

    /**
     * Everything the system can SEE that would explain an idle slice, in the
     * order it can see it. Derived, never stored: the row is journalled at
     * alert time and the causes are re-read from the fleet each pass.
     *
     * ORDER: the ARM'S OWN STATE comes first, then anything about the other
     * runs on the symbol. An alert that names a neighbouring grid while the
     * arm itself is sitting in a re-entry cooldown sends the operator to the
     * wrong lever (prod 2026-09-21: "Cause: grid at hostile cap" for an arm
     * whose trailing stop had fired two hours earlier).
     *
     * @param callable|null $summaries fn(string $symbol): array — MarketStore::summaries by default (tests inject)
     * @return string[] every cause that applies (possibly none)
     */
    public static function causes(FleetSlot $slot, ?callable $summaries = null): array
    {
        $out = [];
        $run = $slot->getIdGridRun() !== null ? GridRunQuery::create()->findPk((int) $slot->getIdGridRun()) : null;
        if ($run === null) {
            $out[] = 'slot empty';
        } else {
            $status = (string) $run->getStatus();
            if ($status !== 'Live') {
                $out[] = sprintf('run not Live (%s)', $status);
            }
            if ((bool) $run->getKillSwitch()) {
                $out[] = 'run killed';
            }
            if ((string) ($run->getAlgo() ?: 'Grid') === 'Trend') {
                foreach (self::armCauses($run) as $c) {
                    $out[] = $c;
                }
                // Task 4's derived sizing already answers "how much can this
                // arm actually reach": the holding rule means it is sized to
                // the position it took, and a position under the slot's
                // target is the rest of the slice sitting idle behind it.
                $derived = TrendActivator::derivedSlice($run, $slot);
                if ($derived['rule'] === 'holding' && $derived['invested'] !== null
                    && bccomp($derived['invested'], TrendActivator::effectiveTarget($slot), self::SCALE) < 0) {
                    $out[] = 'position smaller than slice';
                }
            }
        }
        $hostile = self::hostileCapCause((string) $slot->getSymbol(), $summaries);
        if ($hostile !== null) {
            $out[] = $hostile;
        }
        return $out;
    }

    /**
     * What a FLAT trend arm is waiting for, in its own words.
     *
     * TrendEngine::tryEnter() refuses to enter for exactly one reason the
     * arm can state without reading the tape: the re-entry cooldown that
     * follows a stop-out. The timeframe, the bar count and the expiry are
     * asked of the engine (TrendEngine::reentryCooldown) rather than
     * recomputed here, so the sentence can never describe a different
     * cooldown from the one that is actually gating entries.
     *
     * engine_state is read through select(): the daemon rewrites that column
     * on every tick and Propel 1 never re-hydrates a pooled object, so a
     * hydrated read from this cron would describe the position as it was when
     * this process first touched the row.
     *
     * @return string[]
     */
    private static function armCauses(\App\GridRun $run): array
    {
        $state = self::engineState((int) $run->getIdGridRun());
        if (($state['entry'] ?? null) !== null) {
            return []; // holding: the sizing causes below describe it
        }
        $stopOutAt = $state['stop_out_at'] ?? null;
        if (!is_string($stopOutAt) || $stopOutAt === '') {
            return [];
        }
        $cd = TrendEngine::reentryCooldown([
            'trend_signal' => (string) $run->getTrendSignal(),
            'trend_tf' => (string) $run->getTrendTf(),
            'reentry_cooldown' => $run->getReentryCooldown() !== null ? (int) $run->getReentryCooldown() : 0,
        ], $stopOutAt);
        if (!$cd['elapsed']) {
            return [sprintf(
                'trend arm stopped out at %s, re-entry cooldown until %s (%d × %s) — it cannot enter before then',
                $stopOutAt,
                (string) $cd['until'],
                $cd['bars'],
                $cd['tf']
            )];
        }
        return [sprintf(
            'trend arm flat since its stop-out at %s — cooldown elapsed, waiting for an entry signal',
            $stopOutAt
        )];
    }

    /**
     * A grid on the symbol held at the regime gate's cap — WHEN THE EVIDENCE
     * SAYS SO.
     *
     * deploy_pct === MAX_HOSTILE_DEPLOY_PCT on its own proves nothing: 25 is
     * a number an operator can set by hand, and a grid capped at a refit days
     * ago keeps that value long after the tape it was capped for is gone. The
     * cap is only named when one of the two things that can produce it is
     * actually true:
     *
     *   the tape is hostile NOW  → the gate would clamp any write this minute
     *                              (RegimeGate::hostile on the stored 4h row,
     *                              which is what GtbotSetGridTool reads)
     *   the last refit WAS capped → bot_decision.clamps_json carries the
     *                              regime_gate clamp that wrote this very
     *                              deploy_pct, and the tape has since changed
     *                              — said plainly, because the operator can
     *                              lift it
     *
     * A stale/missing 4h row is not evidence either way (the gate itself
     * fails open there), so it leaves the clamp trail to answer.
     */
    private static function hostileCapCause(string $symbol, ?callable $summaries): ?string
    {
        $s4 = null;
        foreach (GridRunQuery::create()
            ->filterBySymbol($symbol)
            ->filterByStatus('Done', Criteria::NOT_EQUAL)
            ->find() as $g) {
            if ((string) ($g->getAlgo() ?: 'Grid') === 'Trend') {
                continue;
            }
            if ((int) ($g->getDeployPct() ?? 100) !== RegimeGate::MAX_HOSTILE_DEPLOY_PCT) {
                continue;
            }
            $s4 ??= ($summaries ?? static fn (string $s): array => MarketStore::summaries($s, TrendActivator::STALE_AFTER))($symbol)['4h'] ?? [];
            $fresh = $s4 !== [] && empty($s4['stale']);
            $adx = isset($s4['adx14']) && is_numeric($s4['adx14']) ? (float) $s4['adx14'] : null;
            if ($fresh && RegimeGate::hostile($s4['trend'] ?? null, $adx)) {
                return sprintf(
                    'grid %s held at the regime gate\'s %d%% cap — the 4h tape is hostile (trend %s, ADX %s)',
                    (string) $g->getLabel(),
                    RegimeGate::MAX_HOSTILE_DEPLOY_PCT,
                    (string) ($s4['trend'] ?? '?'),
                    $adx !== null ? number_format($adx, 1) : '?'
                );
            }
            if (self::cappedByTheGate($g)) {
                return sprintf(
                    'grid %s still at the %d%% hostile cap its last refit was clamped to — the 4h tape is no longer hostile, so the cap can be lifted',
                    (string) $g->getLabel(),
                    RegimeGate::MAX_HOSTILE_DEPLOY_PCT
                );
            }
        }
        return null;
    }

    /** Did the gate write this grid's current deploy_pct? (decision receipt) */
    private static function cappedByTheGate(\App\GridRun $g): bool
    {
        $d = \App\BotDecisionQuery::create()
            ->filterByIdGridRun((int) $g->getIdGridRun())
            ->orderByIdBotDecision(Criteria::DESC)
            ->findOne();
        if ($d === null || (int) ($d->getDeployPct() ?? -1) !== (int) ($g->getDeployPct() ?? 100)) {
            return false;
        }
        $clamps = json_decode((string) $d->getClampsJson(), true);
        foreach (is_array($clamps) ? $clamps : [] as $c) {
            if (is_array($c) && ($c['limit'] ?? null) === 'regime_gate'
                && (int) ($c['after'] ?? -1) === RegimeGate::MAX_HOSTILE_DEPLOY_PCT) {
                return true;
            }
        }
        return false;
    }

    // ── surfaces ────────────────────────────────────────────────────────

    /**
     * shared.episodes for the routine brief: the legs that are running right
     * now and how much of the pool each has had working through them.
     *
     * @return list<array{symbol: string, algo: string, verdict: string, since: ?string, engaged_pct_tw: ?string, samples: int}>
     */
    public static function brief(): array
    {
        $out = [];
        foreach (RegimeEpisodeQuery::create()
            ->filterByClosedAt(null, Criteria::ISNULL)
            ->orderByIdRegimeEpisode()
            ->find() as $ep) {
            $out[] = [
                'symbol' => (string) $ep->getSymbol(),
                'algo' => (string) $ep->getAlgo(),
                'verdict' => (string) $ep->getVerdict(),
                'since' => $ep->getOpenedAt('Y-m-d H:i'),
                'engaged_pct_tw' => $ep->getEngagedPctTw() !== null ? (string) $ep->getEngagedPctTw() : null,
                'samples' => (int) $ep->getSamples(),
            ];
        }
        return $out;
    }

    /**
     * The last closed episodes for the PnL report — the track record of what
     * the fleet captured leg by leg, newest first.
     *
     * @return list<array<string, mixed>>
     */
    public static function recentClosed(int $limit = 10): array
    {
        $out = [];
        foreach (RegimeEpisodeQuery::create()
            ->filterByClosedAt(null, Criteria::ISNOTNULL)
            ->orderByIdRegimeEpisode(Criteria::DESC)
            ->limit(max(1, $limit))
            ->find() as $ep) {
            $out[] = [
                'symbol' => (string) $ep->getSymbol(),
                'verdict' => (string) $ep->getVerdict(),
                'opened' => $ep->getOpenedAt('Y-m-d H:i'),
                'closed' => $ep->getClosedAt('Y-m-d H:i'),
                'price_open' => bcadd((string) $ep->getPriceOpen(), '0', 2),
                'price_close' => $ep->getPriceClose() !== null ? bcadd((string) $ep->getPriceClose(), '0', 2) : null,
                'engaged_pct_tw' => $ep->getEngagedPctTw() !== null ? (string) $ep->getEngagedPctTw() : null,
                'samples' => (int) $ep->getSamples(),
                'realized' => bcadd((string) $ep->getRealized(), '0', 2),
                'mtm_close' => $ep->getMtmClose() !== null ? bcadd((string) $ep->getMtmClose(), '0', 2) : null,
                'hodl_pct' => $ep->getHodlPct() !== null ? (string) $ep->getHodlPct() : null,
                'captured_pct' => $ep->getCapturedPct() !== null ? (string) $ep->getCapturedPct() : null,
            ];
        }
        return $out;
    }

    // ── config ──────────────────────────────────────────────────────────

    public static function floorPct(): string
    {
        $v = ConfigQuery::create()->findOneByConfig(self::CONFIG_FLOOR)?->getValue();
        return ($v !== null && $v !== '' && is_numeric($v)) ? (string) $v : self::DEFAULT_FLOOR_PCT;
    }

    public static function idlePasses(): int
    {
        $v = ConfigQuery::create()->findOneByConfig(self::CONFIG_IDLE_PASSES)?->getValue();
        return ($v !== null && $v !== '' && (int) $v > 0) ? (int) $v : self::DEFAULT_IDLE_PASSES;
    }

    // ── internals ───────────────────────────────────────────────────────

    /** Realized PnL booked by the run since the episode opened. */
    private static function realizedSince(int $runId, string $since): string
    {
        $sum = '0';
        foreach (TradeCycleQuery::create()
            ->filterByIdGridRun($runId)
            ->filterByDateCreation(['min' => $since])
            ->find() as $c) {
            $sum = bcadd($sum, (string) $c->getRealizedPnl(), self::SCALE);
        }
        return $sum;
    }

    /**
     * The open position marked at $price: qty × (price − entry).
     *
     * select(): raw columns, never a pooled object. This runs from the
     * activator cron, and Propel 1 never re-hydrates an object the process
     * already holds — a hydrated read would mark a position as it was at
     * boot (the staleness class of bug BudgetGuard::check documents).
     */
    private static function markToMarket(int $runId, string $price): string
    {
        $state = self::engineState($runId);
        $qty = is_numeric($state['qty'] ?? null) ? (string) $state['qty'] : '0';
        $entry = is_numeric($state['entry'] ?? null) ? (string) $state['entry'] : '0';
        if (bccomp($qty, '0', self::SCALE) <= 0 || bccomp($entry, '0', self::SCALE) <= 0) {
            return '0';
        }
        return bcmul($qty, bcsub($price, $entry, self::SCALE), self::SCALE);
    }

    /**
     * A run's engine_state, read past Propel's instance pool.
     *
     * select() for the reason BudgetGuard::check documents: this runs from
     * the allocator/activator crons while the daemon rewrites the column, and
     * Propel 1 never re-hydrates an object the process already holds.
     *
     * @return array<string, mixed>
     */
    private static function engineState(int $runId): array
    {
        $row = GridRunQuery::create()->filterByIdGridRun($runId)->select(['IdGridRun', 'EngineState'])->findOne();
        $state = is_array($row) ? json_decode((string) ($row['EngineState'] ?? ''), true) : null;
        return is_array($state) ? $state : [];
    }

    /** Freshest stored mark for a symbol; null when nothing is priceable. */
    private static function markPrice(string $symbol): ?string
    {
        $s = MarketStore::summaries($symbol, PHP_INT_MAX);
        foreach (['1h', '4h', '1d'] as $tf) {
            if (isset($s[$tf]['price']) && (float) $s[$tf]['price'] > 0) {
                return (string) $s[$tf]['price'];
            }
        }
        return null;
    }

    /** Keep a ratio inside decimal(8,4) — a tiny HODL move must not break the write. */
    private static function clampPct(string $v): string
    {
        if (bccomp($v, self::PCT_MAX, self::PCT_SCALE) > 0) {
            return self::PCT_MAX;
        }
        $min = '-' . self::PCT_MAX;
        return bccomp($v, $min, self::PCT_SCALE) < 0 ? $min : $v;
    }

    /** "2 h" / "45 min" — the passes count in the operator's units (15 min cron). */
    private static function humanWindow(int $passes): string
    {
        $minutes = $passes * 15;
        return $minutes % 60 === 0 ? sprintf('%d h', intdiv($minutes, 60)) : sprintf('%d min', $minutes);
    }
}
