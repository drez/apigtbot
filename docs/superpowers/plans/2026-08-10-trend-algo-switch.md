# Trend Engine + Per-Run Algo Switch Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A second, configurable trading algorithm (long-only Donchian-breakout / ATR-trailing-stop trend follower) selectable per run via a new `algo` switch with clean Grid↔Trend cutover.

**Architecture:** Spec: `docs/superpowers/specs/2026-08-10-trend-algo-switch-design.md`. A `StrategyEngine` seam inside the existing daemon: `GridEngine` wraps the current grid tick/fill logic unchanged (existing daemon suites are the byte-identical proof); `TrendEngine` is new code. The shell (heartbeat, commands, kill/drawdown/daily-loss rails, BudgetGuard, profile stamping, reconciler, gateway, event log) stays engine-agnostic. Profile-stamped trend risk knobs extend `ProfilePolicy`.

**Tech Stack:** PHP 8.4, Propel 1 (GoatCheese), PHPUnit (`.admin/vendor/bin/phpunit`), bcmath for money math, existing `Indicators` + `MarketStore` for signals.

## Global Constraints

- Money math bcmath scale 8 (12 where the daemon already uses 12 — match the surrounding call).
- `algo` enum exactly `Grid, Trend`, default `Grid`. Trend structural settings user-configurable: `trend_tf` (15m/1h/4h, default 1h), `donchian_period` (20), `trend_ema_fast` (20), `trend_ema_slow` (50), `atr_period` (14).
- Profile-stamped trend knobs (read-only): AtrStopMult / AtrInitialMult / ReentryCooldown = Cautious 2.0/1.5/6 · Balanced 3.0/2.0/3 · Aggressive 4.0/2.5/1 · Max 5.0/3.0/0 · NoLoss null/null/null.
- `algo=Trend` + `profile=NoLoss` refused at save (both change directions) and at create.
- Entry sizing: `deploy_pct × budget_quote` of quote, split into ≤4 tranches each ≤ `max_order_quote`; every tranche individually RiskManager-reviewed. `deploy_pct = 0` → no entries.
- Cutover: cancel open buys, carry inventory + working sells as legacy exits, reset `engine_state`, `algo_switch` Alert event.
- Existing daemon test suites must pass UNCHANGED after the engine seam (no assertion edits).
- All commits end with: `Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>`.

---

### Task 1: Schema — algo switch + trend settings + engine_state

**Files:**
- Modify: `/path/to/apigtbot/schema/main.hjson` (grid_run block)

**Interfaces:**
- Produces: `GridRun` accessors `getAlgo/setAlgo`, `getTrendTf`, `getDonchianPeriod`, `getTrendEmaFast`, `getTrendEmaSlow`, `getAtrPeriod`, `getAtrStopMult/setAtrStopMult`, `getAtrInitialMult/setAtrInitialMult`, `getReentryCooldown/setReentryCooldown`, `getEngineState/setEngineState`.

- [ ] **Step 1: Add the columns**

Directly under the `"profile('Risk profile')"` line add:

```hjson
            "algo('Algorithm')": ["enum(Grid, Trend)", "required", "default:Grid"],
```

After the `"max_buy_levels_below(...)"` line add the trend block:

```hjson
            "trend_tf('Signal timeframe')": ["enum(15m, 1h, 4h)", "required", "default:1h"],
            "donchian_period('Breakout period')": ["integer()", "default:20"],
            "trend_ema_fast('Trend EMA fast')": ["integer()", "default:20"],
            "trend_ema_slow('Trend EMA slow')": ["integer()", "default:50"],
            "atr_period('ATR period')": ["integer()", "default:14"],
            "atr_stop_mult('Trail stop x ATR')": ["decimal(9, 4)", "not-required", "default:null"],
            "atr_initial_mult('Initial stop x ATR')": ["decimal(9, 4)", "not-required", "default:null"],
            "reentry_cooldown('Re-entry cooldown (bars)')": ["integer()", "not-required", "default:null"],
            "engine_state('Engine state (daemon-managed)')": ["longvarchar()", "not-required"],
```

Fallback if the converter/Propel rejects digit-leading enum values (`15m`): change `trend_tf` to `["varchar(3)", "required", "default:1h"]` and note it in your report — the daemon validates against MarketStore's tf strings anyway.

- [ ] **Step 2: Wire the GUI groupings**

- `add_tab_columns`: add `"Trend settings": "trend_tf"` to the existing map.
- `set_readonly_columns`: append `"atr_stop_mult", "atr_initial_mult", "reentry_cooldown"`.
- `set_list_hide_columns`: append `"engine_state"`.

- [ ] **Step 3: Build + verify**

Run: `cd /path/to/apigtbot/.admin && ../gc build`
Then: `grep -rn "function getAlgo\|function getEngineState\|function getAtrStopMult" .admin/src/App/Models/Built/ /path/to/apigtbot/.admin/config/Built/classes/ 2>/dev/null | head -3` → at least one hit each; and
`php -r 'require "vendor/autoload.php"; (new \Ahc\Env\Loader())->load(".env"); require "config/Built/config.php"; require "config/Built/propel.php"; foreach (\App\GridRunQuery::create()->find() as $r) echo $r->getIdGridRun()," ",$r->getAlgo()," ",$r->getTrendTf(),"\n";'` → every run prints `Grid 1h`.

- [ ] **Step 4: Commit**

```bash
cd /path/to/apigtbot && git add -A schema/ .admin/src/App/Models/Built/ .admin/config/Built/ .admin/config/.buildid .gc-meta.json .admin/docs/ && git status --porcelain && git commit -m "feat(bot): algo switch column + trend settings + engine_state on grid_run"
```

(Stage the regenerated model/build artifacts WITH the schema — project convention. Never stage `.admin/public/*/min/` or `.superpowers/`.)

---

### Task 2: ProfilePolicy trend knobs + save-path refusals (TDD)

**Files:**
- Modify: `.admin/src/App/Domains/Bot/ProfilePolicy.php`
- Modify: `.admin/src/App/Services/GridRunServiceWrapper.php`
- Modify: `.admin/src/App/Mcp/Tools/GtbotCreateRunTool.php`
- Test: `.admin/tests/Custom/Bot/ProfilePolicyTest.php` (extend), `.admin/tests/Custom/Bot/GridRunWrapperProfileTest.php` (extend)

**Interfaces:**
- Consumes: Task 1 accessors.
- Produces: `ProfilePolicy::trendCaps(string $profile): array` phpName-keyed `['AtrStopMult'=>?string,'AtrInitialMult'=>?string,'ReentryCooldown'=>?int]`; `ProfilePolicy::apply()` also stamps these three; wrapper + create tool refuse `Trend×NoLoss`.

- [ ] **Step 1: Write the failing tests**

Append to `ProfilePolicyTest`:

```php
    public function testTrendCapsMatrix(): void
    {
        $this->assertSame(['AtrStopMult' => null, 'AtrInitialMult' => null, 'ReentryCooldown' => null], ProfilePolicy::trendCaps('NoLoss'));
        $this->assertSame(['AtrStopMult' => '2.0000', 'AtrInitialMult' => '1.5000', 'ReentryCooldown' => 6], ProfilePolicy::trendCaps('Cautious'));
        $this->assertSame(['AtrStopMult' => '3.0000', 'AtrInitialMult' => '2.0000', 'ReentryCooldown' => 3], ProfilePolicy::trendCaps('Balanced'));
        $this->assertSame(['AtrStopMult' => '4.0000', 'AtrInitialMult' => '2.5000', 'ReentryCooldown' => 1], ProfilePolicy::trendCaps('Aggressive'));
        $this->assertSame(['AtrStopMult' => '5.0000', 'AtrInitialMult' => '3.0000', 'ReentryCooldown' => 0], ProfilePolicy::trendCaps('Max'));
        $this->expectException(\InvalidArgumentException::class);
        ProfilePolicy::trendCaps('YOLO');
    }

    public function testApplyStampsTrendKnobs(): void
    {
        $run = new GridRun();
        $run->setProfile('Aggressive');
        $run->setBudgetQuote('400');
        ProfilePolicy::apply($run);
        $this->assertSame('4.0000', (string) $run->getAtrStopMult());
        $this->assertSame('2.5000', (string) $run->getAtrInitialMult());
        $this->assertSame(1, (int) $run->getReentryCooldown());
        $this->assertFalse(ProfilePolicy::apply($run)); // still idempotent
    }
```

Append to `GridRunWrapperProfileTest`:

```php
    public function testTrendAlgoRefusedForNoLossOnAlgoChange(): void
    {
        $wrapper = new GridRunServiceWrapper();
        $e = new \App\GridRun();
        $e->setProfile('NoLoss');
        $e->setBudgetQuote('400');
        $data = ['Algo' => 'Trend', 'Status' => 'Draft', 'BudgetQuote' => '400'];
        $messages = []; $ext = []; $error = null;
        $wrapper->beforeSave($e, $data, false, $messages, $ext, $error);
        $this->assertNotEmpty($ext, 'Trend x NoLoss must be refused');
        $this->assertStringContainsString('NoLoss', json_encode(array_keys($ext)));
    }

    public function testNoLossProfileRefusedOnTrendRun(): void
    {
        $wrapper = new GridRunServiceWrapper();
        $e = new \App\GridRun();
        $e->setAlgo('Trend');
        $e->setProfile('Balanced');
        $e->setBudgetQuote('400');
        $data = ['Profile' => 'NoLoss', 'Status' => 'Draft', 'BudgetQuote' => '400'];
        $messages = []; $ext = []; $error = null;
        $wrapper->beforeSave($e, $data, false, $messages, $ext, $error);
        $this->assertNotEmpty($ext);
    }
```

- [ ] **Step 2: Run to verify failure**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/ProfilePolicyTest.php tests/Custom/Bot/GridRunWrapperProfileTest.php`
Expected: FAIL — `trendCaps` undefined; refusal tests find empty `$ext`.

- [ ] **Step 3: Implement**

`ProfilePolicy`: add next to `RULES`:

```php
    /** profile => [trail stop ×ATR, initial stop ×ATR, re-entry cooldown bars] */
    private const TREND_RULES = [
        'NoLoss'     => [null,     null,     null],
        'Cautious'   => ['2.0000', '1.5000', 6],
        'Balanced'   => ['3.0000', '2.0000', 3],
        'Aggressive' => ['4.0000', '2.5000', 1],
        'Max'        => ['5.0000', '3.0000', 0],
    ];

    /** @return array{AtrStopMult:?string, AtrInitialMult:?string, ReentryCooldown:?int} */
    public static function trendCaps(string $profile): array
    {
        if (!isset(self::TREND_RULES[$profile])) {
            throw new \InvalidArgumentException("unknown risk profile: $profile");
        }
        [$stop, $initial, $cooldown] = self::TREND_RULES[$profile];
        return ['AtrStopMult' => $stop, 'AtrInitialMult' => $initial, 'ReentryCooldown' => $cooldown];
    }
```

In `apply()`, merge `trendCaps($profile)` into the stamped set (same compare-then-set loop; extend the field map with the three new setters — `ReentryCooldown` compares as int, the two mults as numeric strings via the existing `sameValue`).

`GridRunServiceWrapper::beforeSave` — after the BudgetGuard block, before cap stamping:

```php
        $algo = array_key_exists('Algo', $data) ? (string) $data['Algo'] : (string) ($e?->getAlgo() ?: 'Grid');
        // $profile is already resolved just below — hoist its resolution above this block
        if ($algo === 'Trend' && $profile === 'NoLoss') {
            $extValidationErr = is_array($extValidationErr) ? $extValidationErr : [];
            $extValidationErr['algo Trend cannot run under profile NoLoss — a trend algo must realize stop losses; pick Cautious or higher']['fields'] = ['Algo'];
            return;
        }
```

(Hoist the existing `$profile` resolution to before this check so both use it.)

`GtbotCreateRunTool`: where profile is validated, add — if `($args['algo'] ?? 'Grid') === 'Trend' && $profile === 'NoLoss'` push the same message onto `$errors`. Add `'algo' => ['type'=>'string','enum'=>['Grid','Trend'],'description'=>'default Grid']` to the input schema and set it on the run row.

- [ ] **Step 4: Run tests**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/ProfilePolicyTest.php tests/Custom/Bot/GridRunWrapperProfileTest.php tests/Custom/Bot/GtbotCreateRunToolTest.php tests/Custom/Bot/BudgetGuardTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
cd /path/to/apigtbot && git add .admin/src/App/Domains/Bot/ProfilePolicy.php .admin/src/App/Services/GridRunServiceWrapper.php .admin/src/App/Mcp/Tools/GtbotCreateRunTool.php .admin/tests/Custom/Bot/ && git commit -m "feat(bot): profile-stamped trend risk knobs + Trend x NoLoss refusal"
```

---

### Task 3: Engine seam — StrategyEngine + GridEngine + cutover

**Files:**
- Create: `.admin/src/App/Domains/Bot/Engine/StrategyEngine.php`
- Create: `.admin/src/App/Domains/Bot/Engine/GridEngine.php`
- Modify: `.admin/src/App/Domains/Bot/Daemon.php` (boot ~81-174, tick strategy block 262-292, `resolveMissing` 944+ / `detectFills` 908+ fill dispatch, change detection near `geometryChanged` 339)
- Test: `.admin/tests/Custom/Bot/EngineSwitchTest.php` (new; scaffold from `DaemonRefitGuardTest.php`)

**Interfaces:**
- Produces (Task 4 implements this for Trend):

```php
namespace App\Domains\Bot\Engine;

interface StrategyEngine
{
    /** Hydrate engine state at daemon boot (after shell config is built). */
    public function boot(): void;
    /** The strategy section of one tick: refits/pruning/entry intents.
     *  $entriesGated mirrors the shell's deRisking/budgetOvercommitted gate:
     *  refit-detection/pruning/stop management still run; NEW entries do not. */
    public function tick(string $price, bool $entriesGated): void;
    /** Post-fill semantics for a non-legacy engine order. */
    public function onBuyFill(\App\BotOrder $row, string $executed, string $fee, string $price): void;
    public function onSellFill(\App\BotOrder $row, string $executed, string $fee, string $price): void;
    /** One-time cutover INTO this engine when run.algo changed since last boot. */
    public function cutover(): void;
}
```

- Daemon gains `/** @internal engine access */ public` visibility on the members the engines need: `placeIntent`, `cancelOpenBuys`, `machine`, `levels`, `config`, `store`, `log`, `run`, `gateway`, plus the grid-tick helpers `maybeRefit`, `geometryChanged`, `distanceMinLevel`, `pruneDistantBuys`, `checkStuckPartials`. Mark each with `@internal` — this is the deliberate narrow surface, not a general API.

- [ ] **Step 1: Write the failing test**

`EngineSwitchTest` (copy the DB/daemon scaffold from `DaemonRefitGuardTest.php`; the assertions are the contract):

```php
    public function testGridToTrendCutoverCarriesLegacyExitsAndCancelsBuys(): void
    {
        // fixture: Grid run with 1 open buy + 1 working sell backed by inventory
        $run = $this->makeRun(['algo' => 'Grid', 'profile' => 'Balanced', 'budget_quote' => '400']);
        $daemon = $this->makeDaemon($run);
        $daemon->tick(); // places ladder orders
        $this->fillLowestBuy($run); // helper: simulate one buy fill so a sell is working
        $daemon->tick();

        $run->reload();
        $run->setAlgo('Trend');
        $run->save();
        $this->assertFalse($daemon->tick(), 'algo change must request a clean restart');

        $daemon2 = $this->makeDaemon($run); // relaunch = new boot performs cutover
        $daemon2->tick();

        $this->assertSame(0, \App\BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByState('BUY_OPEN')->filterByIsLegacy(false)->count(),
            'open grid buys canceled');
        $this->assertGreaterThan(0, \App\BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByState('SELL_OPEN')->filterByIsLegacy(true)->count(),
            'working sells carried as legacy exits');
        $this->assertSame(1, \App\BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByKind('algo_switch')->count());
        $run->reload();
        $this->assertNull($run->getEngineState());
    }

    public function testGridRunBehaviorUnchangedThroughSeam(): void
    {
        // a Grid run ticked through the seam still places its ladder
        $run = $this->makeRun(['algo' => 'Grid', 'profile' => 'Balanced', 'budget_quote' => '400']);
        $daemon = $this->makeDaemon($run);
        $daemon->tick();
        $this->assertGreaterThan(0, \App\BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())->filterByState('BUY_OPEN')->count());
    }
```

(`TrendEngine` does not exist yet — boot for `algo=Trend` in this task installs a temporary `NullEngine`-style guard: boot refuses `algo=Trend` with a clear `engine_missing` Error event and idles (heartbeat only). The first test asserts only cutover mechanics — cancel/legacy/reset/event — which run BEFORE engine selection. Task 4 replaces the guard with the real engine and extends this test.)

- [ ] **Step 2: Run to verify failure**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/EngineSwitchTest.php`
Expected: FAIL — no algo change detection, no cutover, no `algo_switch` event.

- [ ] **Step 3: Implement the seam**

1. **Change detection**: in `tick()` next to the `codeChanged()` self-recovery check, add:

```php
        if ((string) $this->run->getAlgo() !== $this->bootAlgo) {
            $this->log->write('Alert', 'algo_switch', sprintf(
                'algorithm switched %s -> %s — restarting for clean cutover (open buys will be canceled, working sells carried as legacy exits)',
                $this->bootAlgo, (string) $this->run->getAlgo()
            ));
            return false; // clean exit; supervisor relaunches
        }
```

`$this->bootAlgo` is captured in `boot()`.

2. **Cutover in `boot()`**: after the run row is loaded, if the previous boot's algo (persisted marker: first key of `engine_state` JSON, `{"algo":"Grid",...}`) differs from `run.algo` OR `engine_state` is null on a Trend run with grid orders present: cancel open non-legacy buys (`cancelOpenBuys()`), mark working non-legacy sells legacy (`UPDATE ... is_legacy=1` via OrderStore — add `OrderStore::carrySellsAsLegacy(): int` if no equivalent exists), `setEngineState(null)` + save. This is exactly the sequence `geometryReset` performs for refits — reuse its pieces rather than re-implementing (read `geometryReset` at `Daemon.php:433` first).
3. **Engine selection + delegation**: `boot()` ends with `$this->engine = $this->makeEngine()` (`GridEngine` for Grid; the Task-3 guard for Trend). Replace the tick strategy block (lines 262-292: the `geometryChanged/maybeRefit` block, `distanceMinLevel`+`pruneDistantBuys`, and the `machine->initialIntents` loop — NOT `detectFills`/`checkStuckPartials`/`checkBudgetInvariant`, which stay in the shell) with:

```php
        if (!$this->deRisking && !$this->budgetOvercommitted) {
            $this->engine->tick($price);
        } else {
            $this->engine->tickHalted($price); // grid: refit detection still runs; entries gated
        }
```

Careful reading of the current block ordering is required: `detectFills` currently runs between the refit block and pruning; preserve the exact relative order by keeping `detectFills`/`checkStuckPartials` calls in the shell where they are and having `GridEngine::tick` do refit-check → prune → intents. If the current code gates ONLY the intents loop on `deRisking/budgetOvercommitted` (it does — the refit/prune parts run regardless), model that precisely: give the interface a single `tick(string $price, bool $entriesGated)` instead of two methods, and gate only the intents loop inside `GridEngine`. **The existing suites define correctness — match them, not this sketch.**
4. **GridEngine**: constructor takes the `Daemon`; `tick` calls the daemon's grid helpers in the current order; `onBuyFill`/`onSellFill` contain the exact code currently in `resolveMissing`'s FILLED branches (machine `onBuyFill`/`onSellFill`, cycle booking, re-arm) — move it, then have `resolveMissing` (and the paper-mode fill path in `detectFills`) call `$this->engine->onBuyFill(...)`/`onSellFill(...)` for non-legacy rows. Legacy-exit resolution (`resolveLegacy`) stays in the shell (engine-independent by design).
5. **`configFromRun()`**: add `'algo' => (string) $r->getAlgo()`, plus the five trend structural settings and three stamped knobs (string/int casts, null-safe) — the trend engine reads config, not the row.

- [ ] **Step 4: Run the full daemon suites**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/EngineSwitchTest.php tests/Custom/Bot/DaemonPaperFlowTest.php tests/Custom/Bot/DaemonLiveFlowTest.php tests/Custom/Bot/DaemonRefitGuardTest.php tests/Custom/Bot/DaemonRefitWithInventoryTest.php tests/Custom/Bot/DaemonUnrealizedStopTest.php tests/Custom/Bot/DaemonDrawdownStopTest.php tests/Custom/Bot/DaemonProfileTest.php`
Expected: ALL PASS with zero assertion edits to the pre-existing files. Any pre-existing daemon test you had to modify is a seam bug — fix the seam, not the test.

- [ ] **Step 5: Commit**

```bash
cd /path/to/apigtbot && git add .admin/src/App/Domains/Bot/ .admin/tests/Custom/Bot/EngineSwitchTest.php && git commit -m "feat(bot): StrategyEngine seam — GridEngine extraction + algo-switch clean cutover"
```

---

### Task 4: TrendEngine (TDD)

**Files:**
- Create: `.admin/src/App/Domains/Bot/Engine/TrendEngine.php`
- Modify: `.admin/src/App/Domains/Bot/Daemon.php` (`makeEngine()`: replace the Task-3 Trend guard)
- Test: `.admin/tests/Custom/Bot/TrendEngineTest.php`

**Interfaces:**
- Consumes: `StrategyEngine` (Task 3), `Indicators::ema/atr`, `MarketStore::candles(symbol, tf)` (returns `[['high','low','close'], …]` oldest→newest), daemon internals per the Task-3 `@internal` surface, config keys from Task 3 step 5.
- Produces: a Trend run trades: breakout entry (tranches), trailing-stop exit, cycle booking, cooldown; state JSON persisted in `engine_state`.

- [ ] **Step 1: Write the failing tests**

`TrendEngineTest` — unit-test the pure decision core, not the daemon plumbing. Structure `TrendEngine` so the decisions are static-testable:

```php
    public function testEntrySignalRequiresBreakoutAndEmaAlignment(): void
    {
        // 25 candles flat at 100, EMA20<EMA50 impossible on flat — craft a rising tail
        $flat = array_fill(0, 30, ['high' => '101', 'low' => '99', 'close' => '100']);
        $this->assertFalse(TrendEngine::entrySignal($flat, 20, 20, 50));
        $rising = $flat;
        for ($i = 0; $i < 10; $i++) { $c = (string) (101 + $i * 2); $rising[] = ['high' => $c, 'low' => (string)((float)$c - 1), 'close' => $c]; }
        $this->assertTrue(TrendEngine::entrySignal($rising, 20, 20, 50), 'close above 20-bar prior high with fast EMA over slow');
    }

    public function testTrancheSplitRespectsPerOrderCap(): void
    {
        // 400 budget, deploy 100 => 400 quote, cap 120 => 4 tranches of 100
        $t = TrendEngine::tranches('400', 100, '120.00000000');
        $this->assertCount(4, $t);
        foreach ($t as $q) { $this->assertLessThanOrEqual(0, bccomp($q, '120.00000000', 8)); }
        $this->assertSame(0, bccomp(array_reduce($t, fn($c, $q) => bcadd($c, $q, 8), '0'), '400', 8));
        // deploy 30 => 120 quote, single tranche
        $this->assertCount(1, TrendEngine::tranches('400', 30, '120.00000000'));
        // deploy 0 => no tranches
        $this->assertSame([], TrendEngine::tranches('400', 0, '120.00000000'));
    }

    public function testStopRatchetsUpOnly(): void
    {
        $state = ['entry' => '100', 'hwm' => '100', 'stop' => '94.0'];  // initial: 100 - 2.0×ATR(3)
        $s1 = TrendEngine::ratchet($state, '110', '3.0000', '3');       // price 110, stop_mult 3, ATR 3
        $this->assertSame(0, bccomp($s1['stop'], '101', 8));            // 110 - 9
        $s2 = TrendEngine::ratchet($s1, '105', '3.0000', '3');
        $this->assertSame(0, bccomp($s2['stop'], '101', 8), 'stop never moves down');
        $this->assertSame(0, bccomp($s2['hwm'], '110', 8));
    }

    public function testCooldownBlocksReentry(): void
    {
        $this->assertFalse(TrendEngine::cooldownElapsed('2026-08-10 10:00:00', 3, '1h', '2026-08-10 12:59:00'));
        $this->assertTrue(TrendEngine::cooldownElapsed('2026-08-10 10:00:00', 3, '1h', '2026-08-10 13:00:01'));
        $this->assertTrue(TrendEngine::cooldownElapsed(null, 3, '1h', '2026-08-10 13:00:01'), 'no prior stop-out');
        $this->assertTrue(TrendEngine::cooldownElapsed('2026-08-10 10:00:00', 0, '1h', '2026-08-10 10:00:05'), 'Max profile: 0 bars');
    }
```

Plus one daemon-level test appended to `EngineSwitchTest` (harness exists after Task 3): a Trend run with a crafted rising candle fixture (write the candles into `market_summary` via `MarketStore::upsert`) places BUY_OPEN tranche orders on tick; after simulating the fill and a price drop through the stop, a sell is placed and, when filled, a `trade_cycle` row exists with `realized_pnl` ≠ 0 and `engine_state` shows no open position.

- [ ] **Step 2: Run to verify failure**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/TrendEngineTest.php`
Expected: FAIL — class not found.

- [ ] **Step 3: Implement TrendEngine**

Core (complete decision logic; wire-up reads the Task-3 seam):

```php
<?php

namespace App\Domains\Bot\Engine;

use App\Domains\Bot\Daemon;
use App\Domains\Bot\Indicators;
use App\Domains\Bot\IntendedOrder;
use App\Domains\Bot\MarketStore;

/**
 * Long-only trend follower: Donchian breakout entry (close > N-bar prior
 * high, fast EMA > slow EMA on the signal TF), ATR trailing-stop exit,
 * profile-stamped risk knobs (atr_stop_mult / atr_initial_mult /
 * reentry_cooldown). One position per run, entered in <=4 tranche orders
 * each under max_order_quote. State (entry, hwm, stop, cooldown anchor)
 * lives in grid_run.engine_state JSON so restarts rehydrate.
 */
final class TrendEngine implements StrategyEngine
{
    private const SCALE = 8;
    private const MAX_TRANCHES = 4;

    public function __construct(private readonly Daemon $daemon)
    {
    }

    /** close breaks the period-high of the PRIOR bars AND fast EMA > slow EMA */
    public static function entrySignal(array $candles, int $donchian, int $emaFast, int $emaSlow): bool
    {
        $n = count($candles);
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
        $k = 1;
        while ($k < self::MAX_TRANCHES && bccomp(bcdiv($total, (string) $k, self::SCALE), $maxOrderQuote, self::SCALE) > 0) {
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
    public static function ratchet(array $state, string $price, string $stopMult, string $atr): array
    {
        if (bccomp($price, $state['hwm'], self::SCALE) > 0) {
            $state['hwm'] = $price;
        }
        $candidate = bcsub($state['hwm'], bcmul($stopMult, $atr, self::SCALE), self::SCALE);
        if (bccomp($candidate, $state['stop'], self::SCALE) > 0) {
            $state['stop'] = $candidate;
        }
        return $state;
    }

    public static function cooldownElapsed(?string $stopOutAt, int $bars, string $tf, string $now): bool
    {
        if ($stopOutAt === null || $bars <= 0) {
            return true;
        }
        $sec = ['15m' => 900, '1h' => 3600, '4h' => 14400][$tf] ?? 3600;
        return strtotime($now) - strtotime($stopOutAt) > $bars * $sec;
    }

    // boot()/tick()/onBuyFill()/onSellFill()/cutover() wire these decisions
    // to the daemon shell — see Step 3 wiring notes below.
}
```

Wiring (in the same class; consult the Task-3 `@internal` surface):
- `boot()`: decode `engine_state` JSON (`{'algo':'Trend','entry':…,'qty':…,'hwm':…,'stop':…,'stop_out_at':…}`); null → no position.
- `tick($price, $entriesGated)`:
  - With a position: `ratchet()` with ATR from `MarketStore::candles(symbol, trend_tf)` + `Indicators::atr`; persist state if changed; if `$price` ≤ stop and no working stop-sell → place a Sell `IntendedOrder` for the full position qty at `$price` (level_idx 0) via `$daemon->placeIntent`.
  - No position, `!$entriesGated`, cooldown elapsed, `entrySignal()` true → place Buy tranches (level_idx 0..k-1) at `$price`; record pending-entry marker in state so a second tick doesn't double-enter while orders are open.
  - Emit `trend_signal` Info events on entry/stop placement (message includes donchian/EMA/ATR numbers for the journal).
- `onBuyFill`: accumulate position qty + set entry (VWAP of tranche fills), hwm = entry, stop = `entry − atr_initial_mult × ATR`; persist.
- `onSellFill`: book a `trade_cycle` via `$daemon->store->recordCycle([...])` with buy_price = state entry, sell_price = fill price, qty, realized_pnl = `(sell−buy)×qty − fees`, fees_total; clear position, set `stop_out_at`; persist. Emit `cycle_closed`.
- `cutover()`: `engine_state` reset only (the shell's boot cutover already canceled buys / carried legacy sells).
- Replace the Task-3 Trend guard in `Daemon::makeEngine()` with `new TrendEngine($this)`.

- [ ] **Step 4: Run tests**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/TrendEngineTest.php tests/Custom/Bot/EngineSwitchTest.php` then the full `tests/Custom/Bot/` suite.
Expected: PASS everywhere; zero edits to pre-existing tests.

- [ ] **Step 5: Commit**

```bash
cd /path/to/apigtbot && git add .admin/src/App/Domains/Bot/Engine/ .admin/src/App/Domains/Bot/Daemon.php .admin/tests/Custom/Bot/ && git commit -m "feat(bot): TrendEngine — Donchian breakout entry, ATR trailing stop, cooldown, tranche sizing"
```

---

### Task 5: Guards on MCP/routine/cron surfaces + prompt v2026-08-10.3

**Files:**
- Modify: `.admin/src/App/Mcp/Tools/GtbotSetGridTool.php` (refuse Trend runs)
- Modify: `.admin/src/App/Mcp/Tools/GtbotStatusTool.php` (expose `algo` + engine-state block for Trend runs)
- Modify: `.admin/src/App/Domains/Bot/RefitPlanner.php` + `.admin/bin/gtbot-refit` (skip `algo=Trend`)
- Modify: `.admin/docs/refit-routine.md` (skip-Trend instruction; version bump BOTH occurrences → v2026-08-10.3; regenerate paste file `routine-prompt-v2026-08-10.3.txt` in the session scratchpad with the established sed command)
- Test: extend `.admin/tests/Custom/Bot/GtbotControlToolsTest.php`

**Interfaces:**
- Consumes: `getAlgo()` (Task 1), `getEngineState()` (Task 1).

- [ ] **Step 1: Failing test** — `gtbot_set_grid` on a Trend run throws ToolError naming the algo switch; `gtbot_status` payload contains `'algo' => 'Trend'` and an `'engine'` key with the decoded state for Trend runs (assert `'algo' => 'Grid'` and absent `'engine'` for grid runs).
- [ ] **Step 2: Run to verify failure** — `vendor/bin/phpunit tests/Custom/Bot/GtbotControlToolsTest.php`.
- [ ] **Step 3: Implement** — set-grid guard right after run resolution: `if ((string)($run->getAlgo() ?: 'Grid') === 'Trend') { throw new ToolError('run N runs algo=Trend — grid geometry does not apply; switch algo back to Grid first'); }`. Status: `'algo' => (string)($run->getAlgo() ?: 'Grid')` in the run block; when Trend, `'engine' => json_decode((string)$run->getEngineState(), true)`. RefitPlanner/cron: `continue` over Trend runs with a journal note. Prompt: in step 0 after the enumerate sentence add: `Skip any run whose gtbot_status shows algo=Trend (v1: human-driven trend test) — report it with its engine position/stop state, do NOT set_grid or reallocate its slice below its invested_quote.` Bump both version strings; regenerate + verify the paste file (line 1 = v2026-08-10.3).
- [ ] **Step 4: Run tests** — the control-tools file + full `tests/Custom/Bot/`.
- [ ] **Step 5: Commit** — `feat(bot): Trend-run guards on set_grid/status/cron + routine prompt v2026-08-10.3`.

---

### Task 6: Walk-forward validation script

**Files:**
- Create: `.admin/scripts/trend-sweep.php` (pattern: read the header comment style of `.admin/scripts/width-sweep.php` and mirror its CLI/report conventions)

**Interfaces:**
- Consumes: `TrendEngine::entrySignal/ratchet/cooldownElapsed` (pure statics), `MarketStore::candles`.

- [ ] **Step 1: Implement** — CLI: `php scripts/trend-sweep.php [SYMBOL] [TF]` (defaults: both BTCUSDT/BNBUSDT × 1h). Walk-forward: for each stored candle sequence, iterate bars simulating the engine's decision core (entry on signal, tranche-free single fill at close, ratchet per bar, exit at stop breach, cooldown), fee 0.1%/side, over a grid of `donchian_period ∈ {10, 20, 30} × atr_stop_mult ∈ {2, 3, 4, 5}`; report per cell: net/1k/day, max drawdown, trades, win rate, vs buy-and-hold and vs flat-USDT. Note honestly in the output how many bars of history were available (`market_summary.recent_candles` holds a bounded window — if under ~200 bars, print a "short-history: directional only" warning rather than implying sweep-grade evidence).
- [ ] **Step 2: Run it** — `php8.4 (or php) scripts/trend-sweep.php` locally against the synced candle data (if local candles are empty, run it on prod READ-ONLY via the established scp-report pattern). Save the output to `docs/superpowers/specs/2026-08-10-trend-sweep-results.md` with a 3-line interpretation (which defaults hold up).
- [ ] **Step 3: Commit** — `feat(bot): trend walk-forward sweep script + initial results`.

---

### Task 7: Ship + verify

- [ ] **Step 1:** Full suite: `cd .admin && vendor/bin/phpunit tests/` — 0 failures.
- [ ] **Step 2:** `../gc build`; commit regenerated artifacts (`chore(build): rebuild artifacts`).
- [ ] **Step 3:** `cd /path/to/apigtbot && ./gc deploy -y` (additive schema: algo + trend columns). Expect advisory clean + N schema changes applied.
- [ ] **Step 4:** Post-deploy read-only verification (established scp-report pattern): all runs show `algo=Grid`, trend knobs stamped per their profile (Balanced → 3.0/2.0/3), daemons ticking. State-changing SSH is never attempted from the session.
- [ ] **Step 5:** Hand Tim: (a) the v2026-08-10.3 paste file path, (b) the test protocol from the spec — flip the BTC run to Trend in the GUI, watch for the `algo_switch` event and the daemon relaunch, confirm `gtbot_status` shows the engine block, let it paper-trade against the BNB grid control.

## Self-Review (done at write time)

- Spec §1→T1, §2→T2, §3→T3+T4, §4→T3, §5→T5, §6→T6, §7→T2/T3/T4/T5 tests, Rollout→T7. ✓
- No placeholders: every code step carries code or names the exact source lines to move; T3's "match the suites, not this sketch" is a stated contract, not an omission — the byte-identical gate is the spec.
- Type consistency: `StrategyEngine` signature identical in T3 (definition) and T4 (implementation); `trendCaps` phpName keys match Task 1 column phpNames; `tranches(budget, deployPct, maxOrderQuote)` string/int/string across T4 tests and impl. ✓
- Known judgment point for the T3 implementer (flagged, not hidden): the exact `tick()` gating semantics (`$entriesGated`) must be read from the current code before extraction; the interface in T3's header uses `tick(string $price)` while step 3.3 refines it to `tick(string $price, bool $entriesGated)` — **the refined form governs**; T4's wiring notes already consume `$entriesGated`.
