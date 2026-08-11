# Second algorithm: Trend-follow engine + per-run algo switch

**Date:** 2026-08-10 · **Status:** approved design, pending implementation

## Goal

A second trading algorithm — long-only trend-following (Donchian breakout
entry, ATR trailing-stop exit) — selectable per run via a new `algo` switch,
so a run can be flipped Grid ↔ Trend to test which earns more in the current
regime. Configurable signal settings when selected; risk settings driven by
the existing profile system.

## Why trend-follow (research 2026-08-10)

The deploy-policy sweep already proved grids lose in trends (no deployment
level beat USDT in the bear year; flat is a position). A trend follower earns
exactly there. Public 2026 backtests agree momentum/trend systems have the
best return/drawdown among simple algos; DCA/martingale overlaps grid's
buy-dips nature and is the dangerous family in downtrends; mean reversion
duplicates the grid's range bet; stat-arb needs shorting. Chosen with Tim:
**Trend-follow**.

## Decisions (brainstormed with Tim)

- Algorithm: trend-follow (Donchian/EMA breakout + ATR trail), long-only spot.
- Switch semantics: **clean cutover** — cancel open buys, carry inventory +
  working sells as legacy exits (existing refit machinery), both directions.
- Configurable when selected: structural signal settings are per-run columns.
- Profiles apply: risk-relevant trend settings are profile-stamped read-only;
  `Trend × NoLoss` is refused (a trend algo must realize stop losses).
- Routine skips Trend runs in v1 (its set_grid is grid-specific); the human
  drives the test. The gtbot-refit cron/RefitPlanner also skips them.

## 1. Schema (`schema/main.hjson`, grid_run)

New columns:

```
"algo('Algorithm')": ["enum(Grid, Trend)", "required", "default:Grid"],
// Trend settings tab — structural (user-configurable)
"trend_tf('Signal timeframe')": ["enum(15m, 1h, 4h)", "required", "default:1h"],
"donchian_period('Breakout period')": ["integer()", "default:20"],
"trend_ema_fast('Trend EMA fast')": ["integer()", "default:20"],
"trend_ema_slow('Trend EMA slow')": ["integer()", "default:50"],
"atr_period('ATR period')": ["integer()", "default:14"],
// profile-stamped (read-only, like the caps)
"atr_stop_mult('Trail stop x ATR')": ["decimal(9, 4)", "not-required", "default:null"],
"atr_initial_mult('Initial stop x ATR')": ["decimal(9, 4)", "not-required", "default:null"],
"reentry_cooldown('Re-entry cooldown (bars)')": ["integer()", "not-required", "default:null"],
```

`algo` sits next to `profile`; trend columns grouped via `add_tab_columns`
("Trend settings" starting at `trend_tf`). The three stamped columns join
`set_readonly_columns`.

## 2. ProfilePolicy extension

New method `trendCaps(string $profile): array` (phpName-keyed:
`AtrStopMult`, `AtrInitialMult`, `ReentryCooldown`):

| Profile    | AtrStopMult | AtrInitialMult | ReentryCooldown |
|------------|------------:|---------------:|----------------:|
| NoLoss     | null | null | null (algo Trend refused for NoLoss runs) |
| Cautious   | 2.0 | 1.5 | 6 |
| Balanced   | 3.0 | 2.0 | 3 |
| Aggressive | 4.0 | 2.5 | 1 |
| Max        | 5.0 | 3.0 | 0 |

`ProfilePolicy::apply()` stamps these too (all runs — cheap, harmless on
Grid runs; NoLoss stamps nulls). Existing cap stamping (daily loss,
unrealized, position, per-order, breakout policy) is unchanged and applies
to Trend runs identically — those rails live in the daemon shell.

**Refusal:** `GridRunServiceWrapper::beforeSave` refuses `algo=Trend` +
`profile=NoLoss` with a validation error (both directions: switching algo on
a NoLoss run, or switching profile to NoLoss on a Trend run). Same check in
`GtbotCreateRunTool`.

## 3. Engine architecture

New interface `App\Domains\Bot\Engine\StrategyEngine`:

```php
interface StrategyEngine
{
    /** Per tick: emit IntendedOrder[] given price + run config; the shell
     *  reviews each via RiskManager and routes through the gateway. */
    public function intents(string $price, array $config, EngineState $state): array;
    /** React to a fill (order transitioned to Filled). */
    public function onFill(BotOrder $order, EngineState $state): void;
}
```

- `GridEngine`: extraction of the existing per-tick grid logic (ladder
  placement, level state machine hooks) — a call-site move, not a rewrite.
  Behavior must be byte-identical for `algo=Grid` runs.
- `TrendEngine`: one position per run.
  - **Entry** (no position, cooldown elapsed): latest `trend_tf` close
    breaks above the `donchian_period`-high of closes AND EMA(fast) >
    EMA(slow) on the same TF → buy `deploy_pct × slice` of quote. Because
    the profile's per-order cap is 30% of slice, an entry larger than
    `max_order_quote` is split into up to 4 tranche orders in the same
    tick, each ≤ the cap — every tranche still passes RiskManager review
    individually. `deploy_pct = 0` means no entries (flat is a position —
    unchanged).
  - **Exit**: trailing stop at `high-watermark − atr_stop_mult × ATR(atr_period)`,
    ratchet-up only; initial stop `entry − atr_initial_mult × ATR`. Stop
    breached at tick price → sell the full position (limit at market, the
    paper gateway's existing fill model). Fill books a `trade_cycle`
    (realized PnL, fees) exactly like grid cycles.
  - After a stop-out: no new entry for `reentry_cooldown` bars of `trend_tf`.
  - Signals from `MarketStore::candles(symbol, trend_tf)` — data already
    collected every 10 min by gtbot-market-collect.
- Engine selection at daemon boot from `run.algo`. The shell (heartbeat,
  commands, kill/drawdown/daily-loss rails, BudgetGuard, profile stamping,
  reconciler, event log, dashboard stamps) is engine-agnostic.
- Engine state (position entry price, high-watermark, stop level, cooldown
  anchor) persists as JSON in a new run column
  `engine_state('Engine state (daemon-managed)')` (longvarchar, hidden in
  list, daemon-managed — sibling of `applied_geometry`, which stays
  grid-only) so restarts rehydrate.

## 4. Switch flow (clean cutover)

`algo` joins the daemon's per-tick change detection (alongside geometry
keys): when the stored `algo` differs from the booted engine, the daemon
logs `algo_switch` (Alert → Telegram), finishes the tick, exits cleanly;
the supervisor relaunches; boot selects the new engine and performs the
cutover: cancel open buys, carry inventory + working sells as **legacy
exits** (existing machinery), reset `engine_state`. Works both directions —
a trend position's stop-sell (if working) carries as a legacy exit into
Grid mode; grid ladder sells carry into Trend mode.

## 5. Routine / cron

- Prompt (bump to v2026-08-10.3 at ship time): step 0 gains "skip runs
  whose gtbot_status shows algo=Trend (v1: human-driven test) — note them
  in the report with their position/stop state". `gtbot_status` exposes
  `algo` and, for trend runs, the engine state block.
- `bin/gtbot-refit` cron + RefitPlanner: skip `algo=Trend` runs.
- `gtbot_set_grid` refuses runs whose algo is Trend (clear error naming the
  switch) — grid geometry is meaningless there.

## 6. Validation (house evidence culture)

`scripts/trend-sweep.php`: walk-forward over the stored candles (BTC + BNB,
all three TFs), grid of `donchian_period × atr_stop_mult`, reporting
net/1k/day, max drawdown, trade count vs buy-and-hold and vs "USDT". Run
once before enabling on a live run; results recorded in the journal doc.
Not a gate for merging code — a gate for flipping a real run.

## 7. Testing

- `TrendEngineTest`: entry gate (breakout + EMA filter + cooldown), sizing,
  ratchet math (monotonic), initial-stop, stop-fill → trade_cycle booking,
  deploy 0 = no entries, state round-trip through `engine_state`.
- `EngineSwitchTest` (daemon-level, existing harness): Grid→Trend cutover
  carries legacy exits + cancels buys; Trend→Grid symmetric; `algo_switch`
  event emitted; engine_state reset.
- `GridEngine` extraction: the existing daemon suites (Paper/Live flow,
  refit guard, unrealized stop, drawdown) must pass unchanged — they ARE
  the byte-identical-behavior proof.
- ProfilePolicy: trendCaps matrix + stamping; wrapper refusal NoLoss×Trend
  both directions; create-tool refusal.

## Out of scope (v1)

- Routine-driven trend tuning or algo switching; shorting; pyramiding;
  multiple positions; per-run overrides of profile-stamped trend knobs;
  new dashboard chart types (the existing chart + EMA trend line already
  serve; trend runs' stop level rendering can come later).

## Rollout

1. Implement + tests green; run `scripts/trend-sweep.php`, record results.
2. `gc build`, commit, `./gc deploy -y` (additive schema).
3. Prompt v2026-08-10.3 paste (Tim) — routine skips Trend runs.
4. Test protocol: flip ONE run (suggest BTC, the chronic flat-sitter) to
   Trend in the GUI, watch the cutover event, observe a few days of paper
   cycles vs the BNB grid control.
