<?php

namespace App\Domains\Bot;

use App\BotEvent;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\GridRun;
use App\GridRunQuery;

/**
 * The one validated path that creates a grid_run row.
 *
 * It was inline in GtbotCreateRunTool (label uniqueness, the RiskManager
 * geometry/fee checks, the "must bracket the price" rule, the profile
 * whitelist, the shared-budget invariant, then ProfilePolicy::apply + the
 * run_created event). The fleet needs the same path — FleetSlots fills an
 * empty slot with a Draft arm — and a second copy of a validator that guards
 * real money is exactly the drift this codebase keeps paying for. So the
 * checks live here once and the tool calls them; the tool keeps its own
 * argument parsing, preview and confirm gate, which are MCP concerns.
 *
 * @phpstan-type Record array{label:string, symbol:string, status:string, p_low:string,
 *   p_high:string, n_levels:int, spacing:string, allocation:string, budget_quote:string,
 *   fee_pct:string, profile:string, sell_at_loss:bool, sell_when_starved:bool, algo:string,
 *   breakout_buffer_pct:string, max_open_orders:int, max_buy_levels_below:?int,
 *   deploy_pct:?int, alloc_mode:?string, trend_signal:?string}
 */
final class RunFactory
{
    /** Exchange minimum notional the geometry is validated against. */
    public const MIN_NOTIONAL = '5';

    /** Statuses gtbot_create_run may ask for — Live is a human GUI step. */
    public const CREATABLE_STATUSES = ['Draft', 'DryRun', 'Testnet'];

    /** A trend slot's Draft is born wide: the range is reporting-only for a
     *  Trend run (no emitter reads it), but it still has to pass the grid
     *  validator, so half-to-double the market brackets the price with
     *  spacing far above the fee floor. */
    private const TREND_RANGE_LOW = '0.5';
    private const TREND_RANGE_HIGH = '2';
    private const TREND_LEVELS = 20;

    /**
     * Everything the validator and the writer need, defaults applied.
     *
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     */
    public static function record(array $args): array
    {
        $algo = ($args['algo'] ?? 'Grid') === 'Trend' ? 'Trend' : 'Grid';
        return [
            'label' => trim((string) ($args['label'] ?? '')),
            'symbol' => strtoupper(trim((string) ($args['symbol'] ?? ''))),
            'status' => (string) ($args['status'] ?? 'Draft'),
            'p_low' => (string) ($args['p_low'] ?? '0'),
            'p_high' => (string) ($args['p_high'] ?? '0'),
            'n_levels' => (int) ($args['n_levels'] ?? 0),
            'spacing' => ($args['spacing'] ?? 'Geometric') === 'Arithmetic' ? 'Arithmetic' : 'Geometric',
            'allocation' => in_array((string) ($args['allocation'] ?? ''), ['EqualQuote', 'EqualBase', 'BottomWeighted'], true)
                ? (string) $args['allocation'] : 'EqualQuote',
            'budget_quote' => (string) ($args['budget_quote'] ?? '0'),
            'fee_pct' => (string) ($args['fee_pct'] ?? '0.001'),
            'profile' => (string) ($args['profile'] ?? 'Balanced'),
            'sell_at_loss' => !empty($args['sell_at_loss']),
            'sell_when_starved' => !empty($args['sell_when_starved']),
            'algo' => $algo,
            'breakout_buffer_pct' => (string) ($args['breakout_buffer_pct'] ?? '0.02'),
            'max_open_orders' => (int) ($args['max_open_orders'] ?? 60),
            'max_buy_levels_below' => isset($args['max_buy_levels_below']) ? (int) $args['max_buy_levels_below'] : null,
            'deploy_pct' => isset($args['deploy_pct']) ? (int) $args['deploy_pct'] : null,
            'alloc_mode' => isset($args['alloc_mode']) ? (string) $args['alloc_mode'] : null,
            'trend_signal' => isset($args['trend_signal']) ? (string) $args['trend_signal'] : null,
        ];
    }

    /**
     * Every reason this record must not become a row, in the order the MCP
     * tool has always reported them.
     *
     * @param array<string, mixed> $record from record()
     * @param string|null $price current market price; null skips the bracket rule
     * @return list<string>
     */
    public static function validate(array $record, ?string $price): array
    {
        $errors = RiskManager::validateRunConfig([
            'p_low' => $record['p_low'], 'p_high' => $record['p_high'], 'n_levels' => $record['n_levels'],
            'spacing' => $record['spacing'], 'budget_quote' => $record['budget_quote'],
            'fee_pct' => $record['fee_pct'], 'min_notional' => self::MIN_NOTIONAL,
            'deploy_pct' => $record['deploy_pct'] ?? 100,
        ]);
        // must bracket the current price, or the grid sits idle / underwater
        if ($price !== null) {
            if (bccomp($record['p_low'], $price, 8) >= 0 || bccomp($record['p_high'], $price, 8) <= 0) {
                $errors[] = sprintf('range [%s, %s] must bracket the current price %s', $record['p_low'], $record['p_high'], $price);
            }
        }
        if (!in_array($record['profile'], ProfilePolicy::PROFILES, true)) {
            $errors[] = sprintf("unknown profile '%s' — one of %s", $record['profile'], implode('|', ProfilePolicy::PROFILES));
        }
        if ($record['algo'] === 'Trend' && $record['profile'] === 'NoLoss') {
            $errors[] = 'algo Trend cannot run under profile NoLoss — a trend algo must realize stop losses; pick Cautious or higher';
        }
        // never overcommit: the new run's slice must fit the shared wallet
        // alongside every other active run's budget (Draft doesn't count).
        //
        // The FULL cap on purpose (2026-09-21), not the allocatable one:
        // creating a run is an operator act — the GUI form, gtbot_create_run,
        // the fleet filling an empty slot at MIN_SLICE — and the pool reserve
        // exists to stop the MACHINE from sizing itself into the guard, not
        // to become a second, tighter refusal the operator has to argue with.
        // Everything that sizes a slice automatically afterwards (Allocator,
        // TrendActivator, FundsHold) plans against BudgetPool::allocatable().
        $over = BudgetGuard::check(null, $record['budget_quote'], $record['status']);
        if ($over !== null) {
            $errors[] = BudgetGuard::message($over);
        }
        return $errors;
    }

    /**
     * Write the row. Callers validate() first — this only builds and saves.
     *
     * @param array<string, mixed> $record from record()
     */
    public static function create(array $record, ?TelegramNotifier $tg = null, string $via = ''): GridRun
    {
        $run = new GridRun();
        // born into the FLEET's mode, not the column default (paper): one
        // paper run in a real fleet makes the system 'mixed' for good. Any
        // real run means real money is in play — the new one joins that side.
        $run->setSimulated(!in_array(\App\Domains\Dashboard\ModeSwitch::systemMode(), ['real', 'mixed'], true));
        $run->setLabel($record['label']);
        $run->setSymbol($record['symbol']);
        $run->setStatus($record['status']);
        $run->setPLow($record['p_low']);
        $run->setPHigh($record['p_high']);
        $run->setNLevels($record['n_levels']);
        $run->setSpacing($record['spacing']);
        $run->setAllocation($record['allocation']);
        $run->setBudgetQuote($record['budget_quote']);
        $run->setFeePct($record['fee_pct']);
        $run->setProfile($record['profile']);
        $run->setSellAtLoss($record['sell_at_loss']);
        $run->setSellWhenStarved($record['sell_when_starved']);
        $run->setAlgo($record['algo']);
        $run->setBreakoutBufferPct($record['breakout_buffer_pct']);
        $run->setMaxOpenOrders($record['max_open_orders']);
        if (($record['max_buy_levels_below'] ?? null) !== null) {
            $run->setMaxBuyLevelsBelow($record['max_buy_levels_below']);
        }
        if (($record['deploy_pct'] ?? null) !== null) {
            $run->setDeployPct($record['deploy_pct']);
        }
        if (($record['alloc_mode'] ?? null) !== null) {
            $run->setAllocMode($record['alloc_mode']);
        }
        if (($record['trend_signal'] ?? null) !== null) {
            $run->setTrendSignal($record['trend_signal']);
        }
        // live policy: derive daily-loss/unrealized-stop/position/order caps +
        // breakout policy from profile × budget — born with the same caps the
        // daemon/service wrapper would stamp on the next save.
        ProfilePolicy::apply($run);
        $run->save();

        $e = new BotEvent();
        $e->setIdGridRun((int) $run->getIdGridRun());
        $e->setLevel('Info');
        $e->setKind('run_created');
        $e->setMessage(mb_substr(sprintf(
            'run created%s: %s %s [%s, %s] × %d, budget %s',
            $via === '' ? '' : ' via ' . $via,
            $record['label'],
            $record['symbol'],
            $record['p_low'],
            $record['p_high'],
            $record['n_levels'],
            $record['budget_quote']
        ), 0, 500));
        $e->setPayload(json_encode($record, JSON_UNESCAPED_SLASHES));
        $e->save();
        $tg?->send(sprintf(
            '🆕 gtbot run %d created — %s %s [%s, %s] × %d (%s), budget %s',
            (int) $run->getIdGridRun(),
            $record['label'],
            $record['symbol'],
            $record['p_low'],
            $record['p_high'],
            $record['n_levels'],
            $record['status'],
            $record['budget_quote']
        ));
        return $run;
    }

    /**
     * The arm a trend slot fills itself with: parked at the minimum slice with
     * deploy 0, so it costs the pool nothing until the activator funds it on
     * the next confirmed TREND_UP. Born Draft unless GTBOT_FLEET_AUTOLIVE=1 —
     * putting an arm Live is the operator's call by default.
     *
     * @param string|null $price market price for the range/bracket rule; the
     *                           caller passes the one it already read
     * @throws \RuntimeException when no price can be resolved, or validation refuses
     */
    public static function trendDraft(string $symbol, ?string $price = null, ?TelegramNotifier $tg = null): GridRun
    {
        $symbol = strtoupper(trim($symbol));
        $price = $price ?? self::price($symbol);
        if ($price === null || bccomp($price, '0', 8) <= 0) {
            throw new \RuntimeException("no market price for $symbol — cannot size a trend draft");
        }
        $live = (string) env('GTBOT_FLEET_AUTOLIVE', '0') === '1';
        $record = self::record([
            'label' => self::freeLabel(self::base($symbol) . ' trend'),
            'symbol' => $symbol,
            'status' => $live ? 'Live' : 'Draft',
            'p_low' => bcmul($price, self::TREND_RANGE_LOW, 8),
            'p_high' => bcmul($price, self::TREND_RANGE_HIGH, 8),
            'n_levels' => self::TREND_LEVELS,
            'budget_quote' => TrendActivator::MIN_SLICE,
            'profile' => 'Balanced',
            'algo' => 'Trend',
            'sell_at_loss' => false,
            'sell_when_starved' => false,
            'deploy_pct' => 0,
            'alloc_mode' => 'Auto',
        ]);
        $errors = self::validate($record, $price);
        if ($errors !== [] && $live) {
            // AUTOLIVE asked for Live and the pool (or anything else) refused:
            // a Draft still gives the operator an arm to promote by hand.
            $record['status'] = 'Draft';
            $errors = self::validate($record, $price);
        }
        if ($errors !== []) {
            throw new \RuntimeException('cannot create a trend arm for ' . $symbol . ': ' . implode('; ', $errors));
        }
        return self::create($record, $tg, 'fleet slot');
    }

    /** BTCUSDT → BTC: what the operator calls the arm. */
    public static function base(string $symbol): string
    {
        $base = preg_replace('/(USDT|USDC|BUSD|FDUSD|TUSD)$/', '', strtoupper($symbol));
        return $base !== null && $base !== '' ? $base : strtoupper($symbol);
    }

    /** grid_run.label is UNIQUE and tools resolve runs by it, so never collide. */
    private static function freeLabel(string $want): string
    {
        $label = $want;
        for ($n = 2; GridRunQuery::create()->findOneByLabel($label) !== null; $n++) {
            $label = $want . ' ' . $n;
        }
        return $label;
    }

    /**
     * The FLEET path's price helper: a stored summary when it is fresh, the
     * exchange ticker otherwise.
     *
     * The age bound is the point. An unbounded "first stored price" would
     * happily bracket a new arm's range around whatever the collector last
     * wrote — days old on a symbol whose collector stalled. gtbot_create_run
     * does NOT use this: it asks the exchange directly, as it always has,
     * because its bracket check is a user-facing validation of a range the
     * operator typed against the price right now.
     *
     * @param int|null      $maxAge seconds a stored summary may be (default STALE_AFTER)
     * @param callable|null $ticker exchange fallback, fn(string $symbol): ?string (tests inject)
     */
    public static function price(string $symbol, ?int $maxAge = null, ?callable $ticker = null): ?string
    {
        $maxAge = $maxAge ?? TrendActivator::STALE_AFTER;
        foreach (MarketStore::summaries($symbol, $maxAge) as $s) {
            if (($s['price'] ?? null) !== null && empty($s['stale'])) {
                return (string) $s['price'];
            }
        }
        return ($ticker ?? self::ticker(...))($symbol);
    }

    /**
     * The REST base a run's TAPE comes from. Paper runs and mainnet runs trade
     * the real market (GTBOT_ANALYSIS_BASE, like the collector and the refit
     * cron); only a real-mode run on the testnet trades the testnet's own
     * prices. GTBOT_USE_TESTNET alone must not decide this: its default is ON,
     * so a paper fleet had its geometry priced off testnet.binance.vision.
     */
    public static function marketBase(bool $simulated): string
    {
        if (!$simulated && env('GTBOT_USE_TESTNET', '1') !== '0') {
            return 'https://testnet.binance.vision';
        }
        return (string) env('GTBOT_ANALYSIS_BASE', 'https://api.binance.com');
    }

    /** Spot price straight from the exchange; null when it cannot be reached.
     *  $simulated null = the fleet's mode (what a new run is born into). */
    public static function ticker(string $symbol, ?bool $simulated = null): ?string
    {
        try {
            $simulated ??= !in_array(\App\Domains\Dashboard\ModeSwitch::systemMode(), ['real', 'mixed'], true);
            return (new BinanceGateway(self::marketBase($simulated), '', ''))->tickerPrice($symbol);
        } catch (\Throwable) {
            return null;
        }
    }
}
