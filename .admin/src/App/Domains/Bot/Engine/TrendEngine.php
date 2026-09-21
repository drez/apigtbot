<?php

namespace App\Domains\Bot\Engine;

use App\Domains\Bot\Daemon;
use App\Domains\Bot\Indicators;
use App\Domains\Bot\IntendedOrder;
use App\Domains\Bot\MarketCollector;
use App\Domains\Bot\MarketStore;
use App\Domains\Bot\TrendActivator;
use App\Domains\Bot\Gateway\BinanceGateway;

/**
 * Long-only trend follower: Donchian breakout entry (close > N-bar prior
 * high, fast EMA > slow EMA on the signal TF), ATR trailing-stop exit,
 * profile-stamped risk knobs (atr_stop_mult / atr_initial_mult /
 * reentry_cooldown). One position per run, entered in <=4 tranche orders
 * each under max_order_quote. State (entry, hwm, stop, cooldown anchor)
 * lives in grid_run.engine_state JSON so restarts rehydrate.
 *
 * PARTIAL-FILL HANDLING (handoff item 4): checkStuckPartials (shell-side) is
 * grid-shaped — it books a stale partial via LevelStateMachine::
 * onPartialBuyBooked and re-arms $levels[level+1], neither of which exists
 * for a Trend book (Trend orders sit off the ladder). The shell gates that
 * call to algo === 'Grid' (Daemon::tick); this engine owns its own partial
 * handling instead (handleStuckPartials, below), applied to its own
 * (non-legacy) open orders every tick:
 *   - a stuck entry tranche: cancel the remainder, book the executed qty as
 *     a fill (same VWAP accumulation onBuyFill does for a clean fill) —
 *     unless the executed qty is dust that could never clear the exchange's
 *     own filters as a future exit, in which case it is written off instead
 *     (mirrors the grid's own stuck-partial dust guard);
 *   - a stuck stop-sell: cancel the remainder, book the executed qty as a
 *     partial exit (proportional share of the entry fees, position qty
 *     reduced rather than cleared) and re-place a fresh sell for whatever
 *     the ENGINE still tracks as held (not a re-derivation from the
 *     canceled row), since the stop that triggered the exit is still
 *     breached.
 */
final class TrendEngine implements StrategyEngine
{
    private const SCALE = 8;
    /** ceiling on the entry split — beyond this the per-order cap is simply too small for the slice */
    private const HARD_MAX_TRANCHES = 12;

    /** Core (EmaCross1d) tranches after the first wait for a pullback to the
     *  1d EMA20: price at most this fraction ABOVE it (or anywhere below). */
    public const CORE_PULLBACK_PCT = '0.01';
    /** ...or for this many seconds since the previous tranche was placed —
     *  a leg that never pulls back still gets fully deployed, a day at a time
     *  (prod run 8, 2026-08-28: all four tranches in one second at 79214). */
    public const CORE_TRANCHE_SPACING = 86400;

    /** @var array{entry:?string, qty:?string, hwm:?string, stop:?string, fees_paid:string, stop_out_at:?string, tranches_placed:int, last_tranche_at:?string} */
    private array $state = [
        'entry' => null,
        'qty' => null,
        'hwm' => null,
        'stop' => null,
        'fees_paid' => '0',
        'stop_out_at' => null,
        'tranches_placed' => 0,
        'last_tranche_at' => null,
    ];

    /** the one-line "market data is stale" notice was already written —
     *  reset once fresh data arrives, so a later stale spell warns again */
    private bool $staleWarned = false;
    /** epoch sec of the last stale-data refresh attempt (throttled). */
    private ?int $staleRefreshAttemptAt = null;
    /** stale-entry refresh retries at most once every 5 minutes. */
    private const STALE_REFRESH_RETRY_SECONDS = 300;

    /** the one-line "entries disabled at deploy 0%" notice was already
     *  written for this flat spell — reset once deploy_pct clears above 0
     *  (or tranches() is empty for a different reason), so a later flat
     *  spell warns again. Mirrors Daemon::$flatLogged's grid-path idiom
     *  (I2, final-fix review): tryEnter has one decision point per tick
     *  rather than the grid's per-level attempts, so this fires the first
     *  time a signal would have entered but couldn't, not on every tick. */
    private bool $flatLogged = false;

    /** deploy_pct/budget_quote drift already warned for this exact live
     *  pair — reset once the values converge, so a later drift warns again.
     *  I1 (final-fix review): Daemon::GEOMETRY_KEYS excludes these two from
     *  the per-tick config merge in Daemon::tick() (only maybeRefit adopts
     *  them mid-run, and only GridEngine calls it), so a live Trend run
     *  keeps sizing entries off the config it booted with until restarted —
     *  this is a log-only notice, not a live-apply fix (see class docblock
     *  reasoning is inline here: rebuilding refit machinery for Trend was
     *  out of scope for this wave). */
    private ?string $configDriftWarned = null;

    /** the Alert 'stop_ignored' notice was written for the current below-cost
     *  stop breach (sell_at_loss OFF) — reset once an exit is allowed again */
    private bool $stopIgnoredLogged = false;
    /** the Alert 'no_budget' notice was written for the current starved spell */
    private bool $noBudgetLogged = false;

    /** TrendActivator::state() of this run, cached (one bot_event query per
     *  ARM_STATE_TTL seconds rather than per tick). */
    private ?string $armStateCached = null;
    private int $armStateAt = 0;
    private const ARM_STATE_TTL = 60;

    public function __construct(private readonly Daemon $daemon)
    {
        // Hydrate immediately, not only in boot(): the shell may construct a
        // TrendEngine to reconcile against the exchange (Daemon::boot's
        // persistedAlgo() pass) BEFORE calling boot() on the engine it
        // eventually keeps — a fill/cancel discovered during THAT reconcile
        // must still see the real persisted position (VWAP fold, stop
        // re-arm), not a blank slate. boot() re-hydrates again for the final
        // engine — idempotent, and cheap enough that the duplication doesn't
        // matter.
        $this->hydrate();
    }

    /** close breaks the period-high of the PRIOR bars AND fast EMA > slow EMA */
    public static function entrySignal(array $candles, int $donchian, int $emaFast, int $emaSlow): bool
    {
        $n = count($candles);
        // Both windows are hard preconditions. Indicators::ema() degrades to
        // a simple, EQUAL-weighted average under its period — fine as
        // general-purpose advisory analytics, but wrong here: with too few
        // bars the "slow" EMA stops being slow (no recency weighting) and the
        // fast/slow comparison can invert relative to the real trend (a
        // confirmed downtrend can still show fast > slow on an SMA dragged by
        // old data). Only once both EMAs are genuine exponential averages is
        // the comparison meaningful.
        if ($n < max($donchian + 1, $emaSlow)) {
            return false;
        }
        $closes = array_column($candles, 'close');
        $close = (float) end($closes);
        $prior = array_slice(array_column($candles, 'high'), -($donchian + 1), $donchian);
        if ($close <= max(array_map('floatval', $prior))) {
            return false;
        }
        return Indicators::ema($closes, $emaFast) > Indicators::ema($closes, $emaSlow);
    }

    /**
     * Regime entry (2026-09-05): while the activator holds the arm ACTIVE
     * (TREND_UP confirmed on the 4h/1d), the Donchian arm may also enter on
     * "price above a rising EMA stack" — close > fast EMA > slow EMA — instead
     * of waiting for a fresh period-high breakout. scripts/trend-entry-sweep.php
     * (BTC + BNB, bull 2023-11→2025-01 and bear 2025-10→2026-07, prod slice
     * shape): net +132/+160 vs Donchian-only +68/+120 on the bull tapes, the
     * bear tapes identical (the arm is never active there); top-ups to the
     * active slice were REFUTED (bear drawdown ×6 with sell_at_loss OFF) and
     * are not implemented. Same window precondition as entrySignal.
     */
    public static function regimeEntrySignal(array $candles, int $emaFast, int $emaSlow): bool
    {
        $n = count($candles);
        if ($n < $emaSlow) {
            return false;
        }
        $closes = array_column($candles, 'close');
        $close = (float) end($closes);
        $fast = Indicators::ema($closes, $emaFast);
        $slow = Indicators::ema($closes, $emaSlow);
        return $close > $fast && $fast > $slow;
    }

    /** Quote amounts for the entry, each <= per-order cap, summing to deploy%×budget. @return string[] */
    public static function tranches(string $budget, int $deployPct, string $maxOrderQuote): array
    {
        if ($deployPct <= 0) {
            return [];
        }
        $total = bcdiv(bcmul($budget, (string) $deployPct, self::SCALE), '100', self::SCALE);
        if (bccomp($total, '0', self::SCALE) <= 0) {
            return [];
        }
        // Split until every tranche clears the per-order cap (RiskManager
        // vetoes anything over it, so an unsplittable total would mean NO
        // entry). The split used to stop at four: 2026-09-16 the activation
        // deploy went 50 → 100 and a 350 slice against a 60 cap needs six
        // tranches, not four over-cap ones.
        $k = 1;
        $capOn = bccomp($maxOrderQuote, '0', self::SCALE) > 0;
        while ($capOn && $k < self::HARD_MAX_TRANCHES && bccomp(bcdiv($total, (string) $k, self::SCALE), $maxOrderQuote, self::SCALE) > 0) {
            $k++;
        }
        $each = bcdiv($total, (string) $k, self::SCALE);
        $out = array_fill(0, $k, $each);
        // absorb rounding remainder in the last tranche
        $sum = bcmul($each, (string) $k, self::SCALE);
        $out[$k - 1] = bcadd($each, bcsub($total, $sum, self::SCALE), self::SCALE);
        return $out;
    }

    /** @param array{entry:string, hwm:string, stop:string} $state */
    public static function ratchet(array $state, string $price, string $stopMult, string $atr, string $floorPct = '0'): array
    {
        if (bccomp($price, $state['hwm'], self::SCALE) > 0) {
            $state['hwm'] = $price;
        }
        $candidate = bcsub($state['hwm'], self::stopDistance($state['hwm'], $stopMult, $atr, $floorPct), self::SCALE);
        if (bccomp($candidate, $state['stop'], self::SCALE) > 0) {
            $state['stop'] = $candidate;
        }
        return $state;
    }

    /**
     * Stop distance below $ref: the wider of mult × ATR and floorPct × ref.
     * The floor exists because ATR on the 1h signal TF collapses in quiet
     * tape — 3×ATR was a 0.5% trail on 2026-08-28 and chopped the arm out
     * of a +30% leg for +0.01. floorPct 0 disables it; a null ATR leaves
     * the floor alone.
     */
    public static function stopDistance(string $ref, string $mult, ?string $atr, string $floorPct): string
    {
        $byAtr = $atr !== null ? bcmul($mult, $atr, self::SCALE) : '0';
        $byFloor = bcmul($ref, $floorPct, self::SCALE);
        return bccomp($byAtr, $byFloor, self::SCALE) >= 0 ? $byAtr : $byFloor;
    }

    /**
     * Inventory-core signal (trend_signal EmaCross1d): in while the 1d
     * EMA20 sits above the EMA50, out on the cross down. Null when the 1d
     * summary is missing/stale — never trade on a guess.
     *
     * scripts/core-sweep.php, BTC+BNB, 2023-11→2025-01 and 2025-10→2026-07:
     * +530/+1464 per $1k on the bull tapes, −17/−196 on the bear tapes,
     * mean +445, worst drawdown 41% — a bull-participation vehicle with a
     * bear-market cost, sized by the operator through its own slice.
     */
    /**
     * Core stagger rule: is tranche #$placed (0-based) due now? The first
     * tranche goes on the signal; each later one waits for a pullback to the
     * 1d EMA20 (within CORE_PULLBACK_PCT above it, or below it) OR for
     * CORE_TRANCHE_SPACING since the previous placement. A missing EMA20
     * leaves only the time fallback; a missing placement stamp (position
     * older than the stagger) counts as elapsed.
     */
    public static function coreTrancheDue(int $placed, int $total, string $price, ?float $ema20, ?string $lastPlacedAt, string $now): bool
    {
        if ($placed >= $total) {
            return false;
        }
        if ($placed === 0) {
            return true;
        }
        if ($ema20 !== null && $ema20 > 0) {
            $ceiling = bcmul((string) $ema20, bcadd('1', self::CORE_PULLBACK_PCT, self::SCALE), self::SCALE);
            if (bccomp($price, $ceiling, self::SCALE) <= 0) {
                return true;
            }
        }
        if ($lastPlacedAt === null) {
            return true;
        }
        return strtotime($now) - strtotime($lastPlacedAt) >= self::CORE_TRANCHE_SPACING;
    }

    public static function emaCross1dUp(?array $s1d): ?bool
    {
        if ($s1d === null || !empty($s1d['stale']) || ($s1d['ema20'] ?? null) === null || ($s1d['ema50'] ?? null) === null) {
            return null;
        }
        return (float) $s1d['ema20'] > (float) $s1d['ema50'];
    }

    public static function cooldownElapsed(?string $stopOutAt, int $bars, string $tf, string $now): bool
    {
        if ($stopOutAt === null || $bars <= 0) {
            return true;
        }
        return strtotime($now) > strtotime(self::cooldownUntil($stopOutAt, $bars, $tf));
    }

    /** The moment the re-entry cooldown expires — one bar length per bar. */
    public static function cooldownUntil(string $stopOutAt, int $bars, string $tf): string
    {
        return date('Y-m-d H:i:s', strtotime($stopOutAt) + $bars * self::barSeconds($tf));
    }

    /** Bar length of a signal timeframe; an unknown label reads as 1h. */
    public static function barSeconds(string $tf): int
    {
        return ['15m' => 900, '1h' => 3600, '4h' => 14400, '1d' => 86400][$tf] ?? 3600;
    }

    /** trend_signal EmaCross1d = the inventory core (see emaCross1dUp). */
    public static function isCoreConfig(array $cfg): bool
    {
        return (string) (($cfg['trend_signal'] ?? '') ?: 'Donchian') === 'EmaCross1d';
    }

    /**
     * The timeframe an entry decision is taken on: the core trades the daily
     * cross, the breakout arm its own trend_tf. tryEnter() resolves the
     * re-entry cooldown, the staleness check and the candle read against this
     * one value, so anything that wants to REPORT on that decision (the
     * idle-pool alert's causes) asks here rather than re-deriving it.
     */
    public static function entryTf(array $cfg): string
    {
        return self::isCoreConfig($cfg) ? '1d' : (string) (($cfg['trend_tf'] ?? '') ?: '1h');
    }

    /**
     * The re-entry cooldown as DATA, on the same terms tryEnter() enforces it:
     * its anchor (the stop-out), the timeframe and bar count it is measured
     * in, when it ends, and whether it has.
     *
     * Exists so the arm's state can be explained without a second copy of the
     * math — RegimeEpisodes::causes() blamed a grid's deploy_pct for an idle
     * slice while the real answer was this cooldown (prod 2026-09-21).
     *
     * @param array $cfg a run config (Daemon::configFromRun's shape)
     * @return array{stopped_out_at: ?string, tf: string, bars: int, until: ?string, elapsed: bool}
     */
    public static function reentryCooldown(array $cfg, ?string $stopOutAt, ?string $now = null): array
    {
        $tf = self::entryTf($cfg);
        $bars = (int) ($cfg['reentry_cooldown'] ?? 0);
        $has = $stopOutAt !== null && $bars > 0;
        return [
            'stopped_out_at' => $stopOutAt,
            'tf' => $tf,
            'bars' => $bars,
            'until' => $has ? self::cooldownUntil($stopOutAt, $bars, $tf) : null,
            'elapsed' => self::cooldownElapsed($stopOutAt, $bars, $tf, $now ?? date('Y-m-d H:i:s')),
        ];
    }

    /** bcmath max — never move a ratchet-protected value backwards. */
    private static function bcmax(?string $a, string $b): string
    {
        if ($a === null) {
            return $b;
        }
        return bccomp($a, $b, self::SCALE) >= 0 ? $a : $b;
    }

    // ── wiring: decisions above → daemon shell ─────────────────────────

    /** Re-hydrate from engine_state (already done once in the constructor —
     *  see its docblock for why construction alone isn't enough). */
    public function boot(): void
    {
        $this->hydrate();
    }

    /** Decode engine_state. Not exposed by the shell as a public accessor
     *  (@internal engineState() is private) — decode the row's own getter
     *  directly; never write it (persistence goes through
     *  Daemon::persistEngineState only). */
    private function hydrate(): void
    {
        $raw = json_decode((string) ($this->daemon->run->getEngineState() ?? ''), true);
        $s = is_array($raw) ? $raw : [];
        $this->state = [
            'entry' => $s['entry'] ?? null,
            'qty' => $s['qty'] ?? null,
            'hwm' => $s['hwm'] ?? null,
            'stop' => $s['stop'] ?? null,
            'fees_paid' => (string) ($s['fees_paid'] ?? '0'),
            'stop_out_at' => $s['stop_out_at'] ?? null,
            // a position persisted before the stagger existed carries no
            // count: treat it as fully deployed rather than topping it up
            'tranches_placed' => isset($s['tranches_placed'])
                ? (int) $s['tranches_placed']
                : (($s['entry'] ?? null) !== null ? PHP_INT_MAX : 0),
            'last_tranche_at' => $s['last_tranche_at'] ?? null,
        ];
    }

    public function tick(string $price, bool $entriesGated): void
    {
        // I1: deploy_pct/budget_quote can drift from the live config every
        // tick (GEOMETRY_KEYS keeps them out of Daemon's per-tick merge) —
        // surface it rather than let a GUI/MCP change silently do nothing
        // until the run is restarted.
        $this->warnConfigDrift();
        $this->handleStuckPartials($price);

        if ($this->state['entry'] !== null) {
            $this->manageOpenPosition($price);
            if ($this->isCore() && !$entriesGated && !$this->hasOpenOwnOrders('Buy') && !$this->hasOpenOwnOrders('Sell')) {
                $this->tryAddCoreTranche($price);
            }
            return;
        }

        if ($entriesGated || $this->hasOpenOwnOrders('Buy')) {
            return; // gated, or a prior tranche batch is still working
        }
        $this->tryEnter($price);
    }

    public function onBuyFill(\App\BotOrder $row, string $executed, string $fee, string $price): void
    {
        $d = $this->daemon;
        // book at the order's OWN price, not the live tape passed in — a
        // resting tranche (or one recovered from a stuck-partial timeout)
        // may have filled well away from wherever the market is THIS tick
        $fillPrice = (string) $row->getPrice();

        // A stray fill from the batch we already stopped OUT of is orphaned
        // inventory, not a fresh entry. This must NOT be gated on
        // `state.entry === null`: a stray fill can arrive AFTER a new
        // position is already partly established (e.g. cancelOwnOpenBuys
        // hit a gateway error on this exact row and left it working — see
        // its own Warn below), and folding it into the LIVE position's VWAP
        // would silently corrupt that position's entry price with data from
        // an unrelated, already-closed trade. A row predating the last
        // stop-out is stray regardless of what state.entry currently holds.
        //
        // `state['stop_out_at']` itself stays set forever after the first
        // stop-out (it also feeds the cooldown check in tryEnter, so it must
        // never be cleared) — so the discriminator is the ORDER'S OWN age,
        // not merely whether a stop-out happened at some point in the past.
        // A row created before the last stop-out belongs to that closed
        // batch (stray); a row created after it belongs to the batch
        // tryEnter placed once the cooldown cleared (a normal fill — process
        // it below like any other). Strictly BEFORE, not <=: a same-second
        // row can only be a new-batch row — a genuinely stray row is always
        // from a strictly earlier tick (the daemon sleeps >=1s between
        // ticks in production), so same-second fills must resolve as "new",
        // never "stray" (this bit a same-tick handleStuckPartials-full-exit
        // -then-reenter sequence under a 0-bar cooldown profile before the
        // strict comparison was fixed).
        if ($this->state['stop_out_at'] !== null
            && strtotime((string) $row->getDateCreation('Y-m-d H:i:s')) < strtotime((string) $this->state['stop_out_at'])) {
            // filled late (it raced the leftover-tranche cancel — see
            // onSellFill's full-exit branch — or filled between ticks before
            // the cancel landed). Opening a "new position" on it would size a
            // stop off a single stray fill's price with no relation to the
            // trend that was actually being followed. Hand it to the shell as
            // a legacy exit at its own price instead — resolved from here on
            // exactly like any other legacy exit, whatever engine runs next.
            $d->log->write('Alert', 'stray_fill', sprintf(
                '%s filled at %s after the position it belonged to was already closed — exiting the %s qty as a legacy position, not re-opening one',
                $row->getClientOrderId(),
                $fillPrice,
                $executed
            ));
            // pinned to THIS fill's own price and fee: the shell's fallback
            // attribution (newest filled buy at the same level idx, created
            // before the exit) would otherwise match the LIVE batch's level-0
            // buy — booking a fabricated cycle out of two unrelated trades and
            // attributing that live buy a second time when its own exit closes,
            // which corrupts realizedToday and with it the daily-loss kill.
            if (!$d->placeLegacyExit(0, $fillPrice, $executed, $fillPrice, $fee)) {
                $d->warnUnguardedInventory($executed, $fillPrice, (string) $row->getClientOrderId(), 'a stray tranche fill');
            }
            return;
        }

        $prevQty = $this->state['qty'];
        $prevEntry = $this->state['entry'];
        $newQty = $prevQty !== null ? bcadd($prevQty, $executed, self::SCALE) : $executed;
        $newEntry = ($prevQty !== null && $prevEntry !== null && bccomp($newQty, '0', self::SCALE) > 0)
            ? bcdiv(
                bcadd(bcmul($prevEntry, $prevQty, self::SCALE), bcmul($fillPrice, $executed, self::SCALE), self::SCALE),
                $newQty,
                self::SCALE
            )
            : $fillPrice;

        $atr = $this->currentAtr();
        if ($atr === null) {
            // Unreachable in prod today — entrySignal() already required a
            // full emaSlow-bar window before this fill could ever happen,
            // and currentAtr() only refuses below 2 candles — but the
            // fallback below (stop = state.stop ?? entry) must never fire
            // silently if that invariant ever breaks (M14 rider).
            $d->log->write('Warn', 'trend_atr_missing', sprintf(
                'no ATR available on entry fill @ %s — stop fell back to %s instead of an ATR-derived initial stop',
                $fillPrice,
                (string) ($this->state['stop'] ?? $newEntry)
            ));
        }
        $initialMult = (string) ($d->config['atr_initial_mult'] ?? '0');
        $dist = self::stopDistance($newEntry, $initialMult, $atr, (string) ($d->config['trend_stop_floor_pct'] ?? '0'));
        $stopCandidate = bccomp($dist, '0', self::SCALE) > 0
            ? bcsub($newEntry, $dist, self::SCALE)
            : ($this->state['stop'] ?? $newEntry);

        // a later tranche filling at a worse (lower) price than an earlier
        // one must never drag an already-established hwm/stop back down —
        // both only ever ratchet forward, exactly like a running position's
        // trailing stop does
        $hwm = self::bcmax($this->state['hwm'], $newEntry);
        // the inventory core carries no ATR stop: its exit is the 1d cross
        // down (plus the run's max_unrealized_loss and the wallet drawdown floor)
        $stop = $this->isCore() ? null : self::bcmax($this->state['stop'], $stopCandidate);

        $this->state['qty'] = $newQty;
        $this->state['entry'] = $newEntry;
        $this->state['hwm'] = $hwm;
        $this->state['stop'] = $stop;
        $this->state['fees_paid'] = bcadd((string) $this->state['fees_paid'], $fee, self::SCALE);
        $d->persistEngineState($this->state);
        $d->log->write('Info', 'trend_signal', sprintf(
            'entry tranche filled @ %s qty %s — position qty %s entry(vwap) %s stop %s',
            $fillPrice,
            $executed,
            $newQty,
            $newEntry,
            $stop ?? 'none (1d cross exit)'
        ));
    }

    public function onSellFill(\App\BotOrder $row, string $executed, string $fee, string $price): void
    {
        $d = $this->daemon;
        $fillPrice = (string) $row->getPrice(); // I7: the order's own price, not the live tape
        $buyPrice = (string) ($this->state['entry'] ?? $fillPrice);
        $posQty = (string) ($this->state['qty'] ?? $executed);
        // …or when what is left could never be an order again: a position
        // that is not a lot-step multiple (a commission taken in the base, a
        // shrunk exit) is sold short of itself, and a sub-minQty residue kept
        // as a 'position' parks the arm in manageOpenPosition for good
        $left = bcsub($posQty, $executed, self::SCALE);
        $fullExit = bccomp($left, '0', self::SCALE) <= 0 || !$d->filtersClear('Sell', $fillPrice, $left);
        $entryFeeShare = $fullExit
            ? (string) $this->state['fees_paid']
            : bcmul((string) $this->state['fees_paid'], bcdiv($executed, $posQty, self::SCALE), self::SCALE);
        $fees = bcadd($entryFeeShare, $fee, self::SCALE);
        $realized = bcsub(bcmul(bcsub($fillPrice, $buyPrice, self::SCALE), $executed, self::SCALE), $fees, self::SCALE);

        $d->store->recordCycle([
            'level_idx' => (int) $row->getLevelIdx(),
            'buy_price' => $buyPrice,
            'sell_price' => $fillPrice,
            'qty' => $executed,
            'realized_pnl' => $realized,
            'fees_total' => $fees,
        ]);
        $d->log->write('Info', 'cycle_closed', sprintf(
            'trend cycle: buy %s -> sell %s qty %s realized %s',
            $buyPrice,
            $fillPrice,
            $executed,
            $realized
        ));

        if ($fullExit) {
            $this->state = [
                'entry' => null,
                'qty' => null,
                'hwm' => null,
                'stop' => null,
                'fees_paid' => '0',
                'stop_out_at' => date('Y-m-d H:i:s'),
                'tranches_placed' => 0,
                'last_tranche_at' => null,
            ];
            // any other tranche from this entry batch that never filled is
            // now sizing risk for a position that no longer exists — cancel
            // it rather than leave it to land as a stray fill later
            $this->cancelOwnOpenBuys();
        } else {
            // a partial exit (stuck stop-sell timeout) — still holding the
            // remainder, still past the stop that triggered this: keep entry/
            // hwm/stop live so the next tick re-places the rest
            $this->state['qty'] = bcsub($posQty, $executed, self::SCALE);
            $this->state['fees_paid'] = bcsub((string) $this->state['fees_paid'], $entryFeeShare, self::SCALE);
        }
        $d->persistEngineState($this->state);
    }

    /** A working stop-sell left the book without filling (externally
     *  canceled/expired/rejected) — re-place it at THIS engine's own current
     *  stop price (not the ladder — a trend exit has no ladder level), for
     *  whatever qty is still tracked as held. Only when the breach is STILL
     *  live and CONFIRMED live: $price is the live tape when this fires from
     *  a tick's own fill detection, and null when it fires from
     *  Daemon::boot()'s own reconcile (no tick has run yet). Both a null
     *  price and a recovered (above-stop) price decline to re-place rather
     *  than guess — re-placing on stale/missing information risks the same
     *  liquidate-a-healthy-position failure either way: manageOpenPosition
     *  re-arms the exit itself on the next tick's genuine (confirmed) breach,
     *  off a freshly ratcheted stop. */
    public function onSellCanceled(\App\BotOrder $row, ?string $price): void
    {
        $d = $this->daemon;
        $qty = $this->state['qty'];
        if ($qty === null || bccomp($qty, '0', self::SCALE) <= 0) {
            return; // nothing left to guard
        }
        if ($price === null) {
            $d->log->write('Info', 'sell_canceled', sprintf(
                "%s (the trend stop-sell) was canceled and no live price is available yet (discovered at boot) — not re-placing; the trailing stop re-arms on the next tick's genuine breach",
                $row->getClientOrderId()
            ));
            return;
        }
        $exitPrice = (string) ($this->state['stop'] ?? $price);
        if (bccomp($price, $exitPrice, self::SCALE) > 0) {
            $d->log->write('Info', 'sell_canceled', sprintf(
                '%s (the trend stop-sell) was canceled and price %s has recovered above stop %s — not re-placing; the trailing stop re-arms on the next genuine breach',
                $row->getClientOrderId(),
                $price,
                $exitPrice
            ));
            return;
        }
        if (!$this->mayExitAt($exitPrice)) {
            return; // sell_at_loss OFF: not re-placing an exit that would realize a loss
        }
        $d->log->write('Alert', 'sell_canceled', sprintf(
            '%s (the trend stop-sell) was canceled — re-placing @ %s qty %s',
            $row->getClientOrderId(),
            $exitPrice,
            $qty
        ));
        $d->placeIntent(new IntendedOrder('Sell', 0, $exitPrice, $qty), $price);
    }

    /** This engine's own open position plus legacy exits — see the
     *  StrategyEngine::heldQty() docblock for why the shell's stops need
     *  this to see a trend position at all. */
    /** Nothing to resize: the position is `state.qty`, and onSellFill books
     *  what the exit EXECUTED — an unsellable remainder is written off there. */
    public function onExitResized(int $levelIdx, string $placedQty): void
    {
    }

    public function heldQty(): string
    {
        $own = (string) ($this->state['qty'] ?? '0');
        return bcadd($own, $this->daemon->store->legacyRemainingQty(), self::SCALE);
    }

    /**
     * The trend position lives ONLY in engine_state (there is no ladder and,
     * while price is above the stop, no working sell either) — and the cutover
     * that calls this is about to erase that column. See
     * StrategyEngine::handoffUnguardedPosition for the contract; the failure it
     * prevents is a healthy, fully-guarded-by-nothing position of several
     * hundred USDT silently abandoned on a Trend→Grid flip.
     *
     * Hand off the part of the position no working exit covers, priced at the
     * position's own entry VWAP:
     *   - at cost, an operator's algo flip never realizes a loss by itself; if
     *     price is already above entry the exchange fills it at once, above the
     *     limit, which is the outcome the trailing stop was working toward;
     *   - the legacy row's reserve (qty × entry) is the position's true cost
     *     basis, so the incoming engine sizes its book on what is actually
     *     free rather than re-spending capital that is still in base;
     *   - the entry fees already paid are pinned alongside, so the cycle this
     *     exit eventually books is the real round trip, not a half of one.
     * The engine's own working stop-sell (if any) is NOT double-counted: it is
     * still non-legacy at this point and the shell carries it moments later.
     */
    public function handoffUnguardedPosition(): void
    {
        $d = $this->daemon;
        $qty = $this->state['qty'];
        $entry = $this->state['entry'];
        if ($qty === null || $entry === null || bccomp($qty, '0', self::SCALE) <= 0) {
            return; // no position of its own to hand off
        }
        $guarded = '0';
        foreach ($d->store->openOrderObjects() as $row) {
            if ($row->getIsLegacy() || (string) $row->getSide() !== 'Sell') {
                continue; // pre-existing legacy exits guard OTHER inventory,
                // never this position (state.qty only ever counts this
                // engine's own fills — a stray fill is diverted to legacy
                // without being added, and onSellFill subtracts what exits)
            }
            // the exit's FULL qty, not its remainder: a partially executed
            // stop-sell has already sold that part of the position (the shell
            // only marks a partial PartFilled — state.qty is not reduced until
            // it books), so counting the remainder alone would "hand off" base
            // the run no longer holds and guard 0.5 of a 2.0 position twice
            $guarded = bcadd($guarded, (string) $row->getQty(), self::SCALE);
        }
        $unguarded = bccomp($qty, $guarded, self::SCALE) > 0 ? bcsub($qty, $guarded, self::SCALE) : '0';
        // in-memory only: the shell resets engine_state immediately after this
        // (persisting here would write the outgoing engine's keys back under
        // the INCOMING algo's marker), and clearing makes a second call a no-op
        $this->state['qty'] = null;
        $this->state['entry'] = null;
        if (bccomp($unguarded, '0', self::SCALE) <= 0) {
            $d->log->write('Info', 'algo_cutover', sprintf(
                'trend position qty %s is already fully covered by working exits — carried as legacy, nothing further to hand off',
                $qty
            ));
            return;
        }
        $feeShare = bcmul((string) $this->state['fees_paid'], bcdiv($unguarded, $qty, self::SCALE), self::SCALE);
        $d->log->write('Alert', 'algo_cutover', sprintf(
            'trend position qty %s (entry %s) had no working exit for %s of it — handing that off as a legacy exit at cost so the incoming engine can see, reserve and exit it',
            $qty,
            $entry,
            $unguarded
        ));
        $placed = $d->placeLegacyExit(0, (string) $entry, $unguarded, (string) $entry, $feeShare);
        if (!$placed) {
            // the sub-alert above already fired (partial_dust / place_failed);
            // these flag the handoff itself as incomplete — the position is no
            // longer tracked by ANY engine and no exit guards it.
            $d->log->write('Alert', 'algo_cutover', 'handoff failed — position unaccounted');
            $d->warnUnguardedInventory($unguarded, (string) $entry, 'the trend position', 'an algo cutover handoff');
        }
    }

    /** engine_state was already reset by the shell's cutover before this
     *  runs (algoCutover → boot() rehydrates the now-empty state) — nothing
     *  else to do, exactly like GridEngine::cutover(). */
    public function cutover(): void
    {
    }

    // ── internals ────────────────────────────────────────────────────

    private function manageOpenPosition(string $price): void
    {
        if ($this->isCore()) {
            $this->manageCorePosition($price);
            return;
        }
        $d = $this->daemon;
        $cfg = $d->config;
        $stopMult = $cfg['atr_stop_mult'] ?? null;
        $atr = $this->currentAtr();
        if ($atr !== null && $stopMult !== null && $this->state['hwm'] !== null && $this->state['stop'] !== null) {
            $ratcheted = self::ratchet(
                ['entry' => (string) $this->state['entry'], 'hwm' => (string) $this->state['hwm'], 'stop' => (string) $this->state['stop']],
                $price,
                (string) $stopMult,
                $atr,
                (string) ($cfg['trend_stop_floor_pct'] ?? '0')
            );
            if (bccomp($ratcheted['hwm'], (string) $this->state['hwm'], self::SCALE) !== 0
                || bccomp($ratcheted['stop'], (string) $this->state['stop'], self::SCALE) !== 0) {
                $this->state['hwm'] = $ratcheted['hwm'];
                $this->state['stop'] = $ratcheted['stop'];
                $d->persistEngineState(['hwm' => $this->state['hwm'], 'stop' => $this->state['stop']]);
            }
        }

        if ($this->state['stop'] === null || bccomp($price, (string) $this->state['stop'], self::SCALE) > 0) {
            return; // no stop yet, or still above it — hold
        }
        if ($this->hasOpenOwnOrders('Sell')) {
            return; // exit already working
        }
        if (!$this->mayExitAt($price)) {
            return; // sell_at_loss OFF: a stop-out below cost is ignored, hold
        }
        $qty = (string) $this->state['qty'];
        $d->log->write('Info', 'trend_signal', sprintf(
            'stop hit: price %s <= stop %s — exiting qty %s',
            $price,
            $this->state['stop'],
            $qty
        ));
        $d->placeIntent(new IntendedOrder('Sell', 0, $price, $qty), $price);
    }

    /**
     * kill+flatten: the position lives in engine_state, so nothing else in the
     * system knows it is there — sell it, marketable, and chase it down while
     * the market runs away (Daemon::marketableExit / chaseNeedsReprice).
     *
     * THIS ENGINE OWNS ITS OWN EXIT, both to place and to re-price. The shell
     * used to chase it for us: it cancelled the stop-sell and re-placed it as
     * a LEGACY row, which hasOpenOwnOrders() does not count — so this method
     * read "no exit working" and placed a second sell for the WHOLE position,
     * every chase tick, until the book carried several times the inventory
     * (paper: a short; mainnet: -2010 on every tick and a stuck liquidation).
     * The shell now touches only ladder and legacy exits (Daemon::shellExits).
     *
     * One basis for the position: state.entry, its VWAP. The shell's per-lot
     * fallback (the newest filled buy at level 0) is the LAST tranche, which
     * floors a multi-tranche position at the wrong price in both directions.
     *
     * mayExitAt still binds: with 'Sell at loss' OFF a position under its own
     * cost is held (the shell's own gate refuses such a flatten up front; this
     * is the second line, for the paths that reach a flatten another way).
     */
    public function liquidate(string $price): void
    {
        $qty = $this->state['qty'];
        if ($qty === null || bccomp($qty, '0', self::SCALE) <= 0) {
            return; // flat
        }
        if (!$this->mayExitAt($price)) {
            return; // logs stop_ignored; the shell's flatten_partial names it
        }
        $d = $this->daemon;
        $at = $d->marketableExit($price, $this->state['entry'] !== null ? (string) $this->state['entry'] : null);
        $working = $this->ownOpenSell();
        if ($working !== null) {
            if (!$d->chaseNeedsReprice((string) $working->getPrice(), $at) || !$d->chaseTakeBudget()) {
                return; // close enough to the target, or the budget is spent
            }
            // cancel AND book: whatever traded before the cancel landed is
            // real, and onSellFill is what reduces (or closes) the position
            if (!$this->cancelOwnExit($working, $price)) {
                return; // the cancel failed, or the fill it carried closed us out
            }
            $qty = (string) ($this->state['qty'] ?? '0');
            if (bccomp($qty, '0', self::SCALE) <= 0 || !$d->filtersClear('Sell', $at, $qty)) {
                return; // the position is closed (or the dust left cannot be an order)
            }
        }
        $d->log->write('Info', 'trend_signal', sprintf(
            'flatten: selling the whole position (qty %s) at %s, under the mark %s',
            $qty,
            $at,
            $price
        ));
        $d->placeIntent(new IntendedOrder('Sell', 0, $at, $qty), $price);
    }

    /** This engine's own working exit, if it has one. */
    private function ownOpenSell(): ?\App\BotOrder
    {
        foreach ($this->daemon->store->openOrderObjects() as $row) {
            if (!$row->getIsLegacy() && (string) $row->getSide() === 'Sell') {
                return $row;
            }
        }
        return null;
    }

    /**
     * Cancel one of our own working exits so it can be re-placed lower, taking
     * the executed qty off the cancel response with it: the part that traded
     * between the last look and the cancel is a real exit fill, and dropping
     * it left the position marked as still held while the coins were gone.
     *
     * @return bool true when a replacement should now be placed
     */
    private function cancelOwnExit(\App\BotOrder $row, string $price): bool
    {
        $d = $this->daemon;
        $cid = (string) $row->getClientOrderId();
        try {
            $res = $d->gateway->cancelOrder($d->run->getSymbol(), $cid);
        } catch (\Throwable $e) {
            // -2011 and friends: fill detection resolves it on the next tick,
            // and the ledger row stays OPEN so nothing is lost either way
            $d->log->write('Info', 'cancel_gone', sprintf('%s could not be canceled for the chase: %s', $cid, $e->getMessage()));
            return false;
        }
        $executed = (string) ($res['executedQty'] ?? $row->getFilledQty() ?? '0');
        if (bccomp($executed, '0', self::SCALE) <= 0) {
            $d->store->markCanceled($row);
            return true;
        }
        [$executed, $fee] = $d->bookFill($row, (string) ($row->getExchangeOrderId() ?? ''), $executed);
        $d->log->write('Alert', 'flatten_partial_booked', sprintf(
            '%s had already traded %s when the chase canceled it — booked as a partial exit',
            $cid,
            $executed
        ));
        $this->onSellFill($row, $executed, $fee, $price);
        return true;
    }

    /** The activator's marker state for this run is 'active' (TREND_UP
     *  confirmed, slice + deploy raised). Cached for ARM_STATE_TTL seconds. */
    private function armActive(): bool
    {
        if ($this->armStateCached === null || time() - $this->armStateAt >= self::ARM_STATE_TTL) {
            $this->armStateCached = TrendActivator::state((int) $this->daemon->run->getIdGridRun());
            $this->armStateAt = time();
        }
        return $this->armStateCached === 'active';
    }

    /** trend_signal EmaCross1d — the inventory core (see emaCross1dUp). */
    private function isCore(): bool
    {
        return self::isCoreConfig($this->daemon->config);
    }

    /** The 1d summary the core trades on; 2h staleness allowance — the
     *  collector refreshes 1d rows on the hour, and a daily signal does not
     *  go bad in an hour the way a 1h breakout does. */
    private function coreSummary(): ?array
    {
        return MarketStore::summaries($this->daemon->run->getSymbol(), 7200)['1d'] ?? null;
    }

    /** Core exit: the 1d EMA20 crossed under the EMA50. Stale/missing data
     *  holds (never exits on a guess); sell_at_loss OFF holds below cost
     *  exactly like a stop-out would. */
    private function manageCorePosition(string $price): void
    {
        $d = $this->daemon;
        if (self::emaCross1dUp($this->coreSummary()) !== false) {
            return; // still up, or no verdict
        }
        if ($this->hasOpenOwnOrders('Sell')) {
            return; // exit already working
        }
        if (!$this->mayExitAt($price)) {
            return;
        }
        $qty = (string) $this->state['qty'];
        $d->log->write('Info', 'trend_signal', sprintf(
            '1d EMA20 crossed under EMA50 — core exit @ %s qty %s',
            $price,
            $qty
        ));
        $d->placeIntent(new IntendedOrder('Sell', 0, $price, $qty), $price);
    }

    private function tryEnter(string $price): void
    {
        $d = $this->daemon;
        $cfg = $d->config;
        if (($cfg['atr_initial_mult'] ?? null) === null || ($cfg['atr_stop_mult'] ?? null) === null) {
            return; // profile forbids Trend — the service layer refuses this
            // combo at save time (Trend × NoLoss), so this is belt-and-braces
        }
        $core = $this->isCore();
        $regimeEntry = false;
        $tf = self::entryTf($cfg);
        if (!self::reentryCooldown($cfg, $this->state['stop_out_at'])['elapsed']) {
            return;
        }
        $donchian = (int) ($cfg['donchian_period'] ?? 20);
        $emaFast = (int) ($cfg['trend_ema_fast'] ?? 20);
        $emaSlow = (int) ($cfg['trend_ema_slow'] ?? 50);
        if ($core) {
            if (self::emaCross1dUp($this->coreSummary()) !== true) {
                return;
            }
        } else {
            if ($this->isSignalDataStale($tf)) {
                return; // stop management still ran above this tick — only entries are gated
            }
            $candles = MarketStore::candles($d->run->getSymbol(), $tf);
            if (!self::entrySignal($candles, $donchian, $emaFast, $emaSlow)) {
                if (!$this->armActive() || !self::regimeEntrySignal($candles, $emaFast, $emaSlow)) {
                    return;
                }
                $regimeEntry = true;
            }
        }
        $budget = $this->spendableBudget();
        $reserve = $d->store->legacyReserveQuote();
        $tranches = $this->trancheQuotes();
        if (!$tranches) {
            // I2: mirror the grid path's flat notice (Daemon::$flatLogged) —
            // deploy 0% is a position (exits keep working), not silence.
            if ((int) ($cfg['deploy_pct'] ?? 100) <= 0) {
                if (!$this->flatLogged) {
                    $this->flatLogged = true;
                    $d->log->write('Info', 'flat', 'entries disabled at deploy 0% — trend entries idle (exits keep working)');
                }
            } else {
                $this->flatLogged = false;
                // deploy > 0 and still nothing to place: the legacy reserve ate
                // the whole slice — warn ONCE per starved spell (Alert → Telegram)
                if (!$this->noBudgetLogged) {
                    $this->noBudgetLogged = true;
                    $d->log->write('Alert', 'no_budget', sprintf(
                        'no budget available for trading: slice %s minus %s reserved by working exits leaves %s — breakout entries idle until an exit fills, the slice grows, or inventory is sold (sell_at_loss is %s)',
                        (string) $cfg['budget_quote'],
                        $reserve,
                        $budget,
                        !empty($cfg['sell_at_loss']) ? 'ON' : 'OFF'
                    ));
                }
            }
            return;
        }
        $this->flatLogged = false;
        $this->noBudgetLogged = false;

        $total = count($tranches);
        if ($core) {
            // staggered: the first tranche only — the rest follow on a
            // pullback / the spacing via tryAddCoreTranche (see coreTrancheDue)
            $tranches = [$tranches[0]];
        }

        // Pre-filter tranche intents once here so we can place only valid ones
        // (instead of emitting one filter_skip per rejected tranche), and emit
        // one actionable blocker when none can clear the exchange filters.
        $valid = [];
        $blocked = 0;
        $minQuote = null;
        foreach ($tranches as $quote) {
            if ($minQuote === null || bccomp((string) $quote, (string) $minQuote, self::SCALE) < 0) {
                $minQuote = (string) $quote;
            }
            $qty = bcdiv((string) $quote, $price, self::SCALE);
            if (bccomp($qty, '0', self::SCALE) <= 0) {
                continue;
            }
            if (!$d->filtersClear('Buy', $price, $qty)) {
                $blocked++;
                continue;
            }
            $valid[] = $qty;
        }
        if (!$valid) {
            $d->log->write('Warn', 'trend_entry_blocked', sprintf(
                'breakout signal present but every tranche failed exchange filters (deploy %d%%, budget %s, min tranche %s, min_notional %s) — raise deploy/slice or lower tranche split pressure',
                (int) ($cfg['deploy_pct'] ?? 100),
                $budget,
                (string) ($minQuote ?? '0'),
                (string) ($cfg['min_notional'] ?? '0')
            ));
            return;
        }
        if ($blocked > 0) {
            $d->log->write('Info', 'trend_entry_trimmed', sprintf(
                'breakout signal trimmed by exchange filters: %d/%d tranche(s) placeable at %s',
                count($valid),
                count($tranches),
                $price
            ));
        }

        $d->log->write('Info', 'trend_signal', $core
            ? sprintf('1d EMA20 above EMA50 — core entry: placing tranche 1/%d @ %s (the rest on a pullback to the 1d EMA20 or every %dh)', $total, $price, intdiv(self::CORE_TRANCHE_SPACING, 3600))
            : ($regimeEntry
                ? sprintf(
                    'regime entry: arm ACTIVE (TREND_UP) and price above the %s EMA%d > EMA%d stack — placing %d tranche(s) @ %s without waiting for a %d-bar breakout',
                    $tf,
                    $emaFast,
                    $emaSlow,
                    count($valid),
                    $price,
                    $donchian
                )
                : sprintf(
                    'breakout entry signal: donchian=%d emaFast=%d emaSlow=%d — placing %d tranche(s) @ %s',
                    $donchian,
                    $emaFast,
                    $emaSlow,
                    count($valid),
                    $price
                )));
        foreach ($valid as $i => $qty) {
            $d->placeIntent(new IntendedOrder('Buy', $i, $price, $qty), $price);
        }
        if ($core) {
            $this->stampTranchePlaced(1);
        }
    }

    /** Core top-up: the next staggered tranche when coreTrancheDue says so
     *  (called from tick() with the position open and no own order working). */
    private function tryAddCoreTranche(string $price): void
    {
        $d = $this->daemon;
        if (self::emaCross1dUp($this->coreSummary()) !== true) {
            return; // signal gone (an exit is coming) or no verdict
        }
        $quotes = $this->trancheQuotes();
        $placed = (int) $this->state['tranches_placed'];
        $ema20 = $this->coreSummary()['ema20'] ?? null;
        if (!self::coreTrancheDue($placed, count($quotes), $price, $ema20 !== null ? (float) $ema20 : null, $this->state['last_tranche_at'], date('Y-m-d H:i:s'))) {
            return;
        }
        $qty = bcdiv((string) $quotes[$placed], $price, self::SCALE);
        if (bccomp($qty, '0', self::SCALE) <= 0 || !$d->filtersClear('Buy', $price, $qty)) {
            $d->log->write('Warn', 'trend_entry_blocked', sprintf(
                'core tranche %d/%d (%s quote) fails exchange filters at %s — skipped, retried next tick',
                $placed + 1,
                count($quotes),
                (string) $quotes[$placed],
                $price
            ));
            return;
        }
        $d->log->write('Info', 'trend_signal', sprintf(
            'core tranche %d/%d @ %s (%s) — position qty %s entry(vwap) %s',
            $placed + 1,
            count($quotes),
            $price,
            $ema20 !== null && bccomp($price, bcmul((string) $ema20, bcadd('1', self::CORE_PULLBACK_PCT, self::SCALE), self::SCALE), self::SCALE) <= 0
                ? sprintf('pullback to the 1d EMA20 %s', (string) $ema20)
                : sprintf('%dh since the previous tranche', intdiv(self::CORE_TRANCHE_SPACING, 3600)),
            (string) $this->state['qty'],
            (string) $this->state['entry']
        ));
        $d->placeIntent(new IntendedOrder('Buy', $placed, $price, $qty), $price);
        $this->stampTranchePlaced($placed + 1);
    }

    private function stampTranchePlaced(int $placed): void
    {
        $this->state['tranches_placed'] = $placed;
        $this->state['last_tranche_at'] = date('Y-m-d H:i:s');
        $this->daemon->persistEngineState([
            'tranches_placed' => $placed,
            'last_tranche_at' => $this->state['last_tranche_at'],
        ]);
    }

    /** The slice minus capital tied up in legacy exits (e.g. carried out of
     *  a Grid→Trend cutover) — not re-spendable, same principle buildLadder
     *  applies for the grid's own ladder budget. */
    private function spendableBudget(): string
    {
        $budget = (string) $this->daemon->config['budget_quote'];
        $reserve = $this->daemon->store->legacyReserveQuote();
        if (bccomp($reserve, '0', self::SCALE) > 0) {
            $budget = bccomp($budget, $reserve, self::SCALE) > 0 ? bcsub($budget, $reserve, self::SCALE) : '0';
        }
        return $budget;
    }

    /** @return string[] tranche quotes for the current deploy/slice/cap */
    private function trancheQuotes(): array
    {
        $cfg = $this->daemon->config;
        return self::tranches($this->spendableBudget(), (int) ($cfg['deploy_pct'] ?? 100), (string) $cfg['max_order_quote']);
    }

    /** sell_at_loss gate for every exit this engine places on its own
     *  authority (stop-out, stop-sell re-place, partial remainder): with the
     *  run switch OFF an exit that would realize a loss — price under the
     *  position's breakeven, entry VWAP × (1 + 2·fee) — is not placed; the
     *  position is held and the trailing stop keeps ratcheting, so a later
     *  breach above cost still exits (that one is a profit). Logged once per
     *  ignored spell; re-armed the moment an exit is allowed again. */
    private function mayExitAt(string $price): bool
    {
        $d = $this->daemon;
        if (!empty($d->config['sell_at_loss'])) {
            $this->stopIgnoredLogged = false;
            return true;
        }
        $entry = $this->state['entry'];
        if ($entry === null) {
            return true; // no cost basis to compare against — never block on a guess
        }
        $fee = (string) ($d->config['fee_pct'] ?? '0');
        $breakeven = \App\Domains\Bot\LossGuard::breakeven((string) $entry, $fee);
        if (bccomp($price, $breakeven, self::SCALE) >= 0) {
            $this->stopIgnoredLogged = false;
            return true;
        }
        if (!$this->stopIgnoredLogged) {
            $this->stopIgnoredLogged = true;
            $d->log->write('Alert', 'stop_ignored', sprintf(
                'stop hit at %s but selling would realize a loss (entry %s, breakeven %s, qty %s) and the run switch \'Sell at loss\' is OFF — holding; the exit re-arms above breakeven, or turn the switch on',
                $price,
                (string) $entry,
                $breakeven,
                (string) ($this->state['qty'] ?? '0')
            ));
        }
        return false;
    }

    /** Refuse entries when the signal TF's newest candle is stale (mirrors
     *  MarketStore::summaries' own staleAfter default) — a breakout read off
     *  hours-old data is not a signal. Logged once per stale spell; exits
     *  are never gated by this (manageOpenPosition doesn't call it). */
    private function isSignalDataStale(string $tf): bool
    {
        $symbol = $this->daemon->run->getSymbol();
        $summary = MarketStore::summaries($symbol)[$tf] ?? null;
        $stale = $summary === null || $summary['stale'];
        if ($stale && $this->shouldTryStaleRefresh()) {
            $this->staleRefreshAttemptAt = time();
            try {
                $gw = $this->daemon->gateway instanceof BinanceGateway
                    ? $this->daemon->gateway
                    : MarketCollector::analysisGateway();
                MarketCollector::refreshIfStale($symbol, $gw, 900);
                $summary = MarketStore::summaries($symbol)[$tf] ?? null;
                $stale = $summary === null || $summary['stale'];
                if (!$stale) {
                    $this->staleWarned = false;
                    $this->daemon->log->write('Info', 'trend_data_refreshed', sprintf(
                        '%s market data was stale; collector refresh succeeded and entries are re-enabled',
                        $tf
                    ));
                    return false;
                }
            } catch (\Throwable) {
                // Keep the stale gate closed and fall through to the existing warn.
            }
        }
        if ($stale) {
            if (!$this->staleWarned) {
                $this->staleWarned = true;
                $this->daemon->log->write('Warn', 'trend_data_stale', sprintf(
                    '%s market data is missing or stale — entries refused until fresh data arrives (stop management continues)',
                    $tf
                ));
            }
        } else {
            $this->staleWarned = false;
        }
        return $stale;
    }

    /** Only attempt stale-data self-refresh when enabled and throttled. */
    private function shouldTryStaleRefresh(): bool
    {
        if ((string) env('GTBOT_TREND_REFRESH_STALE', '1') === '0') {
            return false;
        }
        return $this->staleRefreshAttemptAt === null
            || (time() - $this->staleRefreshAttemptAt) >= self::STALE_REFRESH_RETRY_SECONDS;
    }

    /** Any of THIS engine's own (non-legacy) open orders on the given side —
     *  gates double-entry while tranches are still working, and stops a
     *  second stop-sell being placed while one is already working. */
    private function hasOpenOwnOrders(string $side): bool
    {
        foreach ($this->daemon->store->openOrderObjects() as $row) {
            if (!$row->getIsLegacy() && (string) $row->getSide() === $side) {
                return true;
            }
        }
        return false;
    }

    /** Cancel any of this engine's own open Buy orders (leftover entry
     *  tranches). Called when a position closes fully — see onSellFill —
     *  so a late fill on one of them can't re-open a phantom position (see
     *  onBuyFill's stray-fill guard, which exists as a second line of
     *  defense for whatever races this).
     *
     *  NEVER discards a fill (the same invariant cancelEntriesBookingFills
     *  states for the shell's own cutover teardown): a tranche the exchange
     *  had already (partly) executed when the cancel landed has real inventory
     *  behind it. Booking it Canceled at zero fee would erase base the wallet
     *  actually holds — untracked, unguarded, and free to distort the next
     *  ledger rebase. It is booked at its executed qty and immediately guarded
     *  by a legacy exit at its own cost, attribution pinned to itself. */
    private function cancelOwnOpenBuys(): void
    {
        $d = $this->daemon;
        foreach ($d->store->openOrderObjects() as $row) {
            if ($row->getIsLegacy() || (string) $row->getSide() !== 'Buy') {
                continue;
            }
            try {
                $res = $d->gateway->cancelOrder($d->run->getSymbol(), $row->getClientOrderId());
            } catch (\Throwable $e) {
                // stays BUY_OPEN and can still fill later — not silent
                // corruption though: onBuyFill's stray-fill check (date
                // created vs stop_out_at) catches it whenever it does,
                // regardless of what state.entry holds by then, and diverts
                // it to a legacy exit instead of folding it into whatever
                // position is live at that point.
                $d->log->write('Warn', 'cancel_failed', sprintf(
                    '%s: %s — still open on the exchange; a later fill is still caught as stray, not lost',
                    $row->getClientOrderId(),
                    $e->getMessage()
                ));
                continue;
            }
            $executed = (string) ($res['executedQty'] ?? $row->getFilledQty() ?? '0');
            if (bccomp($executed, '0', self::SCALE) <= 0) {
                $d->store->markCanceled($row);
                continue;
            }
            // it executed (in whole or in part) before the cancel landed: real
            // inventory, bought for a position that no longer exists
            $rowPrice = (string) $row->getPrice();
            [$executed, $fee] = $d->bookFill($row, (string) ($row->getExchangeOrderId() ?? ''), $executed);
            $d->log->write('Alert', 'stray_fill', sprintf(
                '%s had already executed %s/%s when the exit closed the position — booked and handed off as a legacy exit @ %s, never discarded',
                $row->getClientOrderId(),
                $executed,
                $row->getQty(),
                $rowPrice
            ));
            if (!$d->placeLegacyExit(0, $rowPrice, $executed, $rowPrice, $fee)) {
                $d->warnUnguardedInventory($executed, $rowPrice, (string) $row->getClientOrderId(), 'a tranche canceled after it had filled');
            }
        }
    }

    /** See the class docblock's PARTIAL-FILL HANDLING note. Runs every tick;
     *  a no-op unless one of this engine's own orders has sat PartFilled past
     *  the timeout.
     *
     *  THE CLOCK IS FIRST-SEEN, NEVER LAST-REFRESHED. The shell tracks when
     *  it first saw each order go partial (Daemon::partialSinceFor — the same
     *  signal the grid's own checkStuckPartials measures against), and the
     *  row's date_modification is the restart fallback because
     *  OrderStore::markPartFilled keeps that stamp on first-seen while the
     *  order keeps filling. Measuring off the row's last write instead gave a
     *  trickling order a fresh timer on every observed fill, so its remainder
     *  was never cancelled and the tranche batch never completed (Repro2,
     *  BUG B). Uses the shell's own configured timeout
     *  (Daemon::enginePartialTimeoutSeconds — a thin public wrapper around the
     *  protected, test-overridable partialTimeoutSeconds()) so a test
     *  daemon's shortened window applies here too. */
    private function handleStuckPartials(string $price): void
    {
        $d = $this->daemon;
        $timeout = $d->enginePartialTimeoutSeconds();
        foreach ($d->store->openOrderObjects() as $row) {
            if ($row->getIsLegacy() || (string) $row->getState() !== 'PartFilled') {
                continue;
            }
            $cid = $row->getClientOrderId();
            $since = $d->partialSinceFor((string) $cid) ?? (int) $row->getDateModification('U');
            if ($since > 0 && (time() - $since) < $timeout) {
                continue;
            }
            try {
                $res = $d->gateway->cancelOrder($d->run->getSymbol(), $cid);
            } catch (\Throwable $e) {
                $d->log->write('Warn', 'cancel_failed', "$cid: " . $e->getMessage());
                continue; // retry next tick
            }
            $executed = (string) ($res['executedQty'] ?? $row->getFilledQty() ?? '0');
            if (bccomp($executed, '0', self::SCALE) <= 0) {
                $d->store->markCanceled($row);
                continue;
            }
            $rowPrice = (string) $row->getPrice();
            // the ledger row and the qty actually held (net of a base-asset
            // commission) — every branch below books the same fill
            [$executed, $fee] = $d->bookFill($row, (string) ($row->getExchangeOrderId() ?? ''), $executed);

            if ((string) $row->getSide() === 'Buy') {
                if (!$d->filtersClear('Sell', $rowPrice, $executed)) {
                    // written off as dust: booked as bought (real, on the
                    // exchange) but never folded into a trackable position —
                    // it could never clear filters as a future exit anyway
                    $d->log->write('Alert', 'partial_dust', sprintf(
                        'trend entry tranche %s booked %s but it cannot clear exchange filters as a future exit — written off',
                        $cid,
                        $executed
                    ));
                    continue;
                }
                $d->log->write('Info', 'partial_timeout', sprintf(
                    'trend entry tranche %s stuck at %s/%s past timeout — booked the fill, cancelled the remainder',
                    $cid,
                    $executed,
                    $row->getQty()
                ));
                $this->onBuyFill($row, $executed, $fee, $price);
            } else {
                $d->log->write('Info', 'partial_timeout', sprintf(
                    'trend stop-sell %s stuck at %s/%s past timeout — booking the fill',
                    $cid,
                    $executed,
                    $row->getQty()
                ));
                $this->onSellFill($row, $executed, $fee, $price);
                // re-place from the ENGINE's own tracked remainder, not a
                // value derived from the canceled row — onSellFill is the
                // single source of truth for what's still held (a full exit
                // clears it to null; a partial exit reduces it)
                if ($this->state['qty'] !== null && bccomp((string) $this->state['qty'], '0', self::SCALE) > 0 && $this->mayExitAt($price)) {
                    $d->placeIntent(new IntendedOrder('Sell', 0, $price, (string) $this->state['qty']), $price);
                }
            }
        }
    }

    /** I1: log-only staleness notice — see $configDriftWarned's docblock for
     *  why this doesn't live-apply the drift. Compares the live run row
     *  (reloaded every tick by the shell, Daemon::tick's `$this->run->
     *  reload()`) against $this->daemon->config, which only catches up at
     *  restart for these two keys. */
    private function warnConfigDrift(): void
    {
        $d = $this->daemon;
        $liveDeployPct = (int) ($d->run->getDeployPct() ?? 100);
        $liveBudget = (string) $d->run->getBudgetQuote();
        $cfgDeployPct = (int) ($d->config['deploy_pct'] ?? 100);
        $cfgBudget = (string) ($d->config['budget_quote'] ?? '0');
        if ($liveDeployPct === $cfgDeployPct && bccomp($liveBudget, $cfgBudget, self::SCALE) === 0) {
            $this->configDriftWarned = null; // back in sync — a later drift warns again
            return;
        }
        $sig = $liveDeployPct . '|' . $liveBudget;
        if ($this->configDriftWarned === $sig) {
            return; // already warned for this exact live pair
        }
        $this->configDriftWarned = $sig;
        $d->log->write('Warn', 'trend_config_stale', 'deploy_pct/budget changed on a live Trend run — takes effect after restart; issue a Reload');
        if ((string) env('GTBOT_TREND_AUTO_RELOAD_ON_DRIFT', '1') !== '0') {
            $d->requestReload('trend deploy_pct/budget drift detected — restarting to apply live config', 'trend_auto_reload');
        }
    }

    private function currentAtr(): ?string
    {
        $cfg = $this->daemon->config;
        $tf = (string) ($cfg['trend_tf'] ?? '1h');
        $candles = MarketStore::candles($this->daemon->run->getSymbol(), $tf);
        if (count($candles) < 2) {
            return null;
        }
        $atr = Indicators::atr(
            array_column($candles, 'high'),
            array_column($candles, 'low'),
            array_column($candles, 'close'),
            (int) ($cfg['atr_period'] ?? 14)
        );
        return number_format($atr, self::SCALE, '.', '');
    }
}
