# Global Max-Drawdown Stop Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A wallet-level hard floor — when global mark-to-market equity falls below `gtbot_shared_budget_quote × (1 − gtbot_max_drawdown_pct/100)` (750 on today's 1000/25%) for 3 consecutive ticks, ALL active runs are killed (buys canceled, inventory held) and the hourly routine decides selloff/restart.

**Architecture:** New `App\Domains\Bot\DrawdownGuard` (sibling of `BudgetGuard`) computes global equity mode-aware (paper: `sim_wallet`; real: run-row-stamped account balances, deduped) and trips every active run's `kill_switch`. A per-tick Daemon hook wick-guards the breach with the existing `BREACH_CONFIRM_TICKS` pattern. Visibility lands in `gtbot_status` and the dashboard band; recovery authority is delegated to the refit routine prompt.

**Tech Stack:** PHP 8.4, Propel 1 ORM, bcmath (string math, SCALE 12), PHPUnit (`.admin/vendor/bin/phpunit`), real project DB inside rolled-back transactions.

**Spec:** `docs/superpowers/specs/2026-08-01-global-max-drawdown-stop-design.md`

## Global Constraints

- All money math via bcmath with `SCALE = 12`, values as strings — never floats.
- Config key `gtbot_max_drawdown_pct`: missing/empty row ⇒ default `'25'`; explicit `'0'` ⇒ disabled. Trip condition is STRICT `<` (equity == floor is OK).
- Breach action is kill + hold — never flatten (operator decision 2026-08-01; routine owns liquidation).
- Unpriced wallet assets count as 0 in equity (fail-closed) and are surfaced, never silently dropped.
- Tests boot the real project DB and wrap each test in a rolled-back transaction (copy the harness verbatim from `BudgetGuardTest` / `DaemonPaperFlowTest`).
- Working dir for all commands: `/path/to/apigtbot/.admin` unless stated.
- Commit after each task; message style `feat(bot): …` / `docs: …`; end commit bodies with `Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>`.

---

### Task 1: DrawdownGuard class

**Files:**
- Create: `.admin/src/App/Domains/Bot/DrawdownGuard.php`
- Test: `.admin/tests/Custom/Bot/DrawdownGuardTest.php`

**Interfaces:**
- Consumes: `SimWallet::sharedBudget()`, `SimWallet::balances()`, `SimWallet::assetsFor()`, `BudgetGuard::ACTIVE_STATUSES`, `App\Domains\Dashboard\ModeSwitch::systemMode()`, `GridRunQuery`, `ConfigQuery`.
- Produces (used by Tasks 2–4):
  - `DrawdownGuard::maxDrawdownPct(): string`
  - `DrawdownGuard::floor(): ?string` — null = disabled
  - `DrawdownGuard::equity(): array{equity: string, unpriced: string[]}`
  - `DrawdownGuard::check(): ?array{equity: string, floor: string, budget: string, pct: string, unpriced: string[]}` — null = OK or disabled
  - `DrawdownGuard::tripAll(): int` — count of runs newly killed

- [ ] **Step 1: Write the failing test**

`.admin/tests/Custom/Bot/DrawdownGuardTest.php`:

```php
<?php

namespace Tests\Custom\Bot;

use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\DrawdownGuard;
use App\GridRun;
use App\GridRunQuery;
use PHPUnit\Framework\TestCase;

class DrawdownGuardTest extends TestCase
{
    private static bool $booted = false;

    public static function setUpBeforeClass(): void
    {
        if (self::$booted) {
            return;
        }
        $admin = dirname(__DIR__, 3);
        require_once $admin . '/vendor/autoload.php';
        (new \Ahc\Env\Loader())->load($admin . '/.env');
        if (!defined('_AUTH_VAR')) {
            require $admin . '/config/Built/config.php';
        }
        if (!\Propel::isInit()) {
            require $admin . '/config/Built/propel.php';
        }
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION[_AUTH_VAR] = new \ApiGoat\Sessions\AuthySession();
        self::$booted = true;
    }

    protected function setUp(): void
    {
        $con = \Propel::getConnection();
        $con->beginTransaction();
        // neutralize whatever the local DB holds — this test owns the world
        $con->exec("UPDATE grid_run SET status = 'Done' WHERE status <> 'Done'");
        $con->exec('DELETE FROM sim_wallet');
        $this->setConfig('gtbot_shared_budget_quote', '1000');
        $this->setConfig('gtbot_max_drawdown_pct', '');
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function setConfig(string $key, string $v): void
    {
        $c = ConfigQuery::create()->findOneByConfig($key) ?? (new Config())->setConfig($key);
        $c->setValue($v);
        $c->save();
    }

    private function seedWallet(array $assets): void
    {
        $ins = \Propel::getConnection()->prepare(
            'INSERT INTO sim_wallet (asset, qty, date_creation, date_modification) VALUES (?, ?, NOW(), NOW())'
        );
        foreach ($assets as $asset => $qty) {
            $ins->execute([$asset, $qty]);
        }
    }

    private function mkRun(
        string $symbol,
        ?string $lastPrice,
        bool $simulated = true,
        string $status = 'Live',
        ?string $balBase = null,
        ?string $balQuote = null,
        ?string $lastTickAt = null
    ): GridRun {
        $r = new GridRun();
        $r->setLabel('dd-' . bin2hex(random_bytes(4)));
        $r->setSymbol($symbol);
        $r->setStatus($status);
        $r->setSimulated($simulated);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('100');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid('dd');
        if ($lastPrice !== null) {
            $r->setLastPrice($lastPrice);
        }
        if ($balBase !== null) {
            $r->setBalBase($balBase);
        }
        if ($balQuote !== null) {
            $r->setBalQuote($balQuote);
        }
        $r->setLastTickAt($lastTickAt ?? date('Y-m-d H:i:s'));
        $r->save();
        return $r;
    }

    // ── floor ───────────────────────────────────────────────────────────

    public function testFloorDefaultsTo25Pct(): void
    {
        $this->assertSame(0, bccomp('750', (string) DrawdownGuard::floor(), 12));
    }

    public function testFloorHonorsConfiguredPct(): void
    {
        $this->setConfig('gtbot_max_drawdown_pct', '40');
        $this->assertSame(0, bccomp('600', (string) DrawdownGuard::floor(), 12));
    }

    public function testExplicitZeroDisables(): void
    {
        $this->setConfig('gtbot_max_drawdown_pct', '0');
        $this->assertNull(DrawdownGuard::floor());
        $this->assertNull(DrawdownGuard::check());
    }

    // ── equity, paper mode ──────────────────────────────────────────────

    public function testSimEquityPricesBaseAssetsAtRunLastPrice(): void
    {
        $this->mkRun('BTCUSDT', '60000');
        $this->seedWallet(['USDT' => '500', 'BTC' => '0.01']);
        $eq = DrawdownGuard::equity();
        $this->assertSame(0, bccomp('1100', $eq['equity'], 12)); // 500 + 0.01×60000
        $this->assertSame([], $eq['unpriced']);
    }

    public function testUnpricedAssetCountsZeroAndIsReported(): void
    {
        $this->mkRun('BTCUSDT', '60000');
        $this->seedWallet(['USDT' => '800', 'XRP' => '100']);
        $eq = DrawdownGuard::equity();
        $this->assertSame(0, bccomp('800', $eq['equity'], 12));
        $this->assertSame(['XRP'], $eq['unpriced']);
    }

    // ── check boundary ──────────────────────────────────────────────────

    public function testEquityAtFloorIsOk(): void
    {
        $this->mkRun('BTCUSDT', '60000');
        $this->seedWallet(['USDT' => '750']);
        $this->assertNull(DrawdownGuard::check());
    }

    public function testEquityUnderFloorBreaches(): void
    {
        $this->mkRun('BTCUSDT', '60000');
        $this->seedWallet(['USDT' => '749']);
        $over = DrawdownGuard::check();
        $this->assertNotNull($over);
        $this->assertSame(0, bccomp('749', $over['equity'], 12));
        $this->assertSame(0, bccomp('750', $over['floor'], 12));
        $this->assertSame(0, bccomp('1000', $over['budget'], 12));
    }

    // ── equity, real mode ───────────────────────────────────────────────

    public function testRealModeDedupesTheOneAccount(): void
    {
        // two REAL runs on the same symbol stamp the SAME account —
        // quote and base must each count once (freshest stamp wins)
        $this->mkRun('BTCUSDT', '1000', false, 'Live', '0.5', '400', '2026-08-01 10:00:00');
        $this->mkRun('BTCUSDT', '1000', false, 'Live', '0.5', '400', '2026-08-01 10:00:05');
        $eq = DrawdownGuard::equity();
        $this->assertSame(0, bccomp('900', $eq['equity'], 12)); // 400 + 0.5×1000
    }

    // ── tripAll ─────────────────────────────────────────────────────────

    public function testTripAllKillsOnlyActiveRuns(): void
    {
        $a = $this->mkRun('BTCUSDT', '60000');
        $b = $this->mkRun('ETHUSDT', '2000', true, 'Testnet');
        $done = $this->mkRun('BNBUSDT', '600', true, 'Done');
        $this->assertSame(2, DrawdownGuard::tripAll());
        $a->reload();
        $b->reload();
        $done->reload();
        $this->assertTrue((bool) $a->getKillSwitch());
        $this->assertTrue((bool) $b->getKillSwitch());
        $this->assertFalse((bool) $done->getKillSwitch());
        $this->assertSame(0, DrawdownGuard::tripAll()); // idempotent
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/DrawdownGuardTest.php`
Expected: FAIL — `Class "App\Domains\Bot\DrawdownGuard" not found`

- [ ] **Step 3: Write the implementation**

`.admin/src/App/Domains/Bot/DrawdownGuard.php`:

```php
<?php

namespace App\Domains\Bot;

use App\ConfigQuery;
use App\Domains\Dashboard\ModeSwitch;
use App\GridRunQuery;

/**
 * Wallet-level hard drawdown floor (operator directive 2026-08-01: total
 * loss must never exceed gtbot_max_drawdown_pct — default 25% — of the
 * shared budget). Equity is the WHOLE system's mark-to-market value: one
 * shared wallet, one floor — a confirmed breach kills EVERY active run at
 * once (buys canceled, inventory HELD); the hourly routine, not the daemon,
 * decides selloff/restart (see refit-routine.md, drawdown-stop recovery).
 * Pricing mirrors DashboardData::computeSharedWallet(), but fail-closed:
 * an asset with no matching run price counts as 0 (understates equity).
 */
final class DrawdownGuard
{
    private const SCALE = 12;
    public const DEFAULT_PCT = '25';

    /** Config gtbot_max_drawdown_pct; missing/empty row falls back to 25.
     *  An explicit 0 disables the stop entirely. */
    public static function maxDrawdownPct(): string
    {
        $v = ConfigQuery::create()->findOneByConfig('gtbot_max_drawdown_pct')?->getValue();
        return ($v !== null && $v !== '') ? (string) $v : self::DEFAULT_PCT;
    }

    /** Equity floor = shared budget × (1 − pct/100); null = disabled. */
    public static function floor(): ?string
    {
        $pct = self::maxDrawdownPct();
        if (bccomp($pct, '0', self::SCALE) <= 0) {
            return null;
        }
        $keep = bcsub('1', bcdiv($pct, '100', self::SCALE), self::SCALE);
        return bcmul(SimWallet::sharedBudget(), $keep, self::SCALE);
    }

    /**
     * Global mark-to-market equity in quote (USDT). Paper: the shared
     * sim_wallet. Real: the ONE exchange account as stamped on the run rows
     * — freshest bal_quote, bal_base deduped per base asset (every run on
     * the same symbol stamps the same account balance).
     *
     * @return array{equity: string, unpriced: string[]}
     */
    public static function equity(): array
    {
        $runs = GridRunQuery::create()
            ->filterByStatus('Done', \Criteria::NOT_EQUAL)
            ->find()->getArrayCopy();

        // freshest last_price per base asset (BTC, ETH, …)
        $priceByBase = [];
        $priceTick = [];
        foreach ($runs as $r) {
            if ($r->getLastPrice() === null) {
                continue;
            }
            [$base] = SimWallet::assetsFor((string) $r->getSymbol());
            $tick = (string) ($r->getLastTickAt('Y-m-d H:i:s') ?? '');
            if (!isset($priceByBase[$base]) || $tick > $priceTick[$base]) {
                $priceByBase[$base] = (string) $r->getLastPrice();
                $priceTick[$base] = $tick;
            }
        }

        $balances = ModeSwitch::systemMode() === 'real'
            ? self::realBalances($runs)
            : SimWallet::balances();

        $equity = '0';
        $unpriced = [];
        foreach ($balances as $asset => $qty) {
            if ($asset === 'USDT') {
                $equity = bcadd($equity, (string) $qty, self::SCALE);
            } elseif (isset($priceByBase[$asset])) {
                $equity = bcadd($equity, bcmul((string) $qty, $priceByBase[$asset], self::SCALE), self::SCALE);
            } elseif (bccomp((string) $qty, '0', self::SCALE) !== 0) {
                $unpriced[] = (string) $asset; // counted as 0 — fail-closed
            }
        }
        return ['equity' => $equity, 'unpriced' => $unpriced];
    }

    /**
     * The one real exchange account, read from the freshest run-row stamps.
     *
     * @param \App\GridRun[] $runs
     * @return array<string, string> asset => qty
     */
    private static function realBalances(array $runs): array
    {
        $out = [];
        $quoteTick = null;
        $baseTick = [];
        foreach ($runs as $r) {
            $tick = (string) ($r->getLastTickAt('Y-m-d H:i:s') ?? '');
            if ($r->getBalQuote() !== null && ($quoteTick === null || $tick > $quoteTick)) {
                $quoteTick = $tick;
                $out['USDT'] = (string) $r->getBalQuote();
            }
            if ($r->getBalBase() === null) {
                continue;
            }
            [$base] = SimWallet::assetsFor((string) $r->getSymbol());
            if (!isset($baseTick[$base]) || $tick > $baseTick[$base]) {
                $baseTick[$base] = $tick;
                $out[$base] = (string) $r->getBalBase();
            }
        }
        return $out;
    }

    /**
     * @return array{equity: string, floor: string, budget: string,
     *               pct: string, unpriced: string[]}|null null = OK/disabled
     */
    public static function check(): ?array
    {
        $floor = self::floor();
        if ($floor === null) {
            return null;
        }
        $eq = self::equity();
        if (bccomp($eq['equity'], $floor, self::SCALE) >= 0) {
            return null; // equity == floor is still OK — strict < trips
        }
        return [
            'equity' => $eq['equity'],
            'floor' => $floor,
            'budget' => SimWallet::sharedBudget(),
            'pct' => self::maxDrawdownPct(),
            'unpriced' => $eq['unpriced'],
        ];
    }

    /** Kill every active run that isn't already killed. Each daemon's next
     *  tick takes the external-kill path (cancel buys once, hold inventory).
     *  Returns how many runs were newly killed. */
    public static function tripAll(): int
    {
        $n = 0;
        foreach (GridRunQuery::create()
            ->filterByStatus(BudgetGuard::ACTIVE_STATUSES, \Criteria::IN)
            ->find() as $r) {
            if ((bool) $r->getKillSwitch()) {
                continue;
            }
            $r->setKillSwitch(true);
            $r->save();
            $n++;
        }
        return $n;
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/DrawdownGuardTest.php`
Expected: PASS (9 tests)

Also run the neighbors to catch collateral damage:
`vendor/bin/phpunit tests/Custom/Bot/BudgetGuardTest.php tests/Custom/Bot/BudgetInvariantTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
cd /path/to/apigtbot
git add .admin/src/App/Domains/Bot/DrawdownGuard.php .admin/tests/Custom/Bot/DrawdownGuardTest.php
git commit -m "feat(bot): DrawdownGuard — global equity floor at gtbot_max_drawdown_pct of shared budget

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

### Task 2: Daemon hook — wick-guarded global stop

**Files:**
- Modify: `.admin/src/App/Domains/Bot/Daemon.php` (tick flow ~line 230; new field near `$breachTicks` ~line 46; new method next to `checkUnrealizedStop` ~line 700)
- Test: `.admin/tests/Custom/Bot/DaemonDrawdownStopTest.php`

**Interfaces:**
- Consumes: `DrawdownGuard::check()`, `DrawdownGuard::tripAll()` (Task 1), existing `Daemon` members: `BREACH_CONFIRM_TICKS`, `$this->log`, `$this->run`, `$this->flattenOnKill`, `ensureKilled()`.
- Produces: bot_event kinds `drawdown_breach` (Warn, first breach tick) and `drawdown_stop` (Alert — fans out to Telegram via EventLog like every Alert).

- [ ] **Step 1: Write the failing test**

`.admin/tests/Custom/Bot/DaemonDrawdownStopTest.php` — harness copied from `DaemonPaperFlowTest` (PaperGateway, fake transport, price driven by `$this->tick`):

```php
<?php

namespace Tests\Custom\Bot;

use App\BotEventQuery;
use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\Daemon;
use App\Domains\Bot\EventLog;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Gateway\PaperGateway;
use App\GridRun;
use PHPUnit\Framework\TestCase;

/**
 * Global drawdown floor: equity under budget×(1−pct/100) held
 * BREACH_CONFIRM_TICKS ticks kills EVERY active run (buys canceled,
 * inventory held). Paper flow: buys fill, price collapses, sim-wallet
 * equity honestly sinks under the floor.
 */
class DaemonDrawdownStopTest extends TestCase
{
    private static bool $booted = false;
    private GridRun $run;
    private GridRun $bystander;
    private string $tick = '120';

    public static function setUpBeforeClass(): void
    {
        if (self::$booted) {
            return;
        }
        $admin = dirname(__DIR__, 3);
        if (!is_file($admin . '/config/Built/config.php')) {
            self::markTestSkipped('project not built');
        }
        require_once $admin . '/vendor/autoload.php';
        (new \Ahc\Env\Loader())->load($admin . '/.env');
        if (!defined('_AUTH_VAR')) {
            require $admin . '/config/Built/config.php';
        }
        if (!\Propel::isInit()) {
            require $admin . '/config/Built/propel.php';
        }
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION[_AUTH_VAR] = new \ApiGoat\Sessions\AuthySession();
        self::$booted = true;
    }

    protected function setUp(): void
    {
        $con = \Propel::getConnection();
        $con->beginTransaction();
        // this test owns the world: no stray active runs, no stray wallet,
        // no pre-epoch simulated fills leaking into the derived wallet
        $con->exec("UPDATE grid_run SET status = 'Done' WHERE status <> 'Done'");
        $this->setConfig('gtbot_shared_budget_quote', '1000');
        $this->setConfig('gtbot_max_drawdown_pct', '25'); // floor 750
        $this->setConfig('gtbot_sim_wallet_epoch', date('Y-m-d H:i:s'));

        $this->run = $this->mkRun('BTCUSDT', '800');      // the traded run
        $this->bystander = $this->mkRun('ETHUSDT', '100'); // proves fan-out
        $this->tick = '120';
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function setConfig(string $key, string $v): void
    {
        $c = ConfigQuery::create()->findOneByConfig($key) ?? (new Config())->setConfig($key);
        $c->setValue($v);
        $c->save();
    }

    private function mkRun(string $symbol, string $budget): GridRun
    {
        $r = new GridRun();
        $r->setLabel('ddstop-' . bin2hex(random_bytes(4)));
        $r->setSymbol($symbol);
        $r->setStatus('Live');
        $r->setSimulated(true);
        $r->setPLow('100');
        $r->setPHigh('140');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote($budget);
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setMaxUnrealizedLossQuote('10000'); // park the per-run stop far away
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid(substr(bin2hex(random_bytes(6)), 0, 10));
        $r->save();
        return $r;
    }

    private function makeDaemon(): Daemon
    {
        $transport = function (string $method, string $url, array $headers, ?string $body): array {
            if (str_contains($url, '/api/v3/ticker/price')) {
                return ['status' => 200, 'headers' => [], 'body' => json_encode(['price' => $this->tick])];
            }
            if (str_contains($url, '/api/v3/exchangeInfo')) {
                return ['status' => 200, 'headers' => [], 'body' => json_encode(['symbols' => [[
                    'symbol' => 'BTCUSDT',
                    'filters' => [
                        ['filterType' => 'PRICE_FILTER', 'tickSize' => '0.01'],
                        ['filterType' => 'LOT_SIZE', 'stepSize' => '0.00001', 'minQty' => '0.00001'],
                        ['filterType' => 'NOTIONAL', 'minNotional' => '5'],
                    ],
                ]]])];
            }
            throw new \RuntimeException("unexpected HTTP call in paper mode: $url");
        };
        $public = new BinanceGateway('https://api.binance.com', '', '', $transport);
        $gw = new PaperGateway($public, $this->run, (string) $this->run->getFeePct());
        return new Daemon($this->run, $gw, false, new EventLog((int) $this->run->getIdGridRun(), false));
    }

    /** Boot, place the ladder at 120, collapse to 30 so both buys fill and
     *  the base inventory is nearly worthless: equity ≈ 599.6 + 3.76×30
     *  ≈ 712 < 750 floor. Fills land after the tick's drawdown check, so
     *  breach counting starts on the NEXT tick. */
    private function driveUnderFloor(Daemon $d): void
    {
        $d->boot();
        $d->tick();          // buys at 100 and 113.33 (800×2/4 = 200 each)
        $this->tick = '30';
        $d->tick();          // both fill this tick; check ran pre-fill
    }

    public function testConfirmedBreachKillsAllActiveRuns(): void
    {
        $d = $this->makeDaemon();
        $this->driveUnderFloor($d);
        $d->tick();          // breach tick 1 (Warn drawdown_breach)
        $d->tick();          // breach tick 2
        $this->run->reload();
        $this->assertFalse((bool) $this->run->getKillSwitch(), 'wick guard: 2 ticks must not trip');
        $d->tick();          // breach tick 3 — confirmed

        $this->run->reload();
        $this->bystander->reload();
        $this->assertTrue((bool) $this->run->getKillSwitch(), 'tripping run must be killed');
        $this->assertTrue((bool) $this->bystander->getKillSwitch(), 'ALL active runs must be killed');
        $this->assertSame(1, BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('drawdown_stop')->count());
        $this->assertGreaterThan(0, BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('drawdown_breach')->count());
    }

    public function testRecoveryBeforeConfirmResetsTheCounter(): void
    {
        $d = $this->makeDaemon();
        $this->driveUnderFloor($d);
        $d->tick();          // breach tick 1
        $d->tick();          // breach tick 2
        // recovery price must stay BELOW the posted sells (lowest ≈113.33,
        // they'd fill and change the wallet) while clearing the floor:
        // equity at 100 ≈ 599.6 + 3.76×100 ≈ 976 > 750, nothing fills
        $this->tick = '100';
        $d->tick();          // recovery — counter must reset
        $this->tick = '30';
        $d->tick();          // breach tick 1 again
        $d->tick();          // breach tick 2 again

        $this->run->reload();
        $this->bystander->reload();
        $this->assertFalse((bool) $this->run->getKillSwitch());
        $this->assertFalse((bool) $this->bystander->getKillSwitch());
        $this->assertSame(0, BotEventQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByKind('drawdown_stop')->count());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/DaemonDrawdownStopTest.php`
Expected: FAIL — kill switches stay false, no `drawdown_stop` event (the hook doesn't exist yet).

If `testConfirmedBreachKillsAllActiveRuns` fails for a DIFFERENT reason (e.g. no buys placed, no fills), STOP and debug the harness first — the negative test passing for the wrong reason is worthless.

- [ ] **Step 3: Implement the Daemon hook**

3a. Add the field next to the existing breach counter (after `private int $breachTicks = 0;`, ~line 46):

```php
    /** consecutive ticks the GLOBAL drawdown floor has been breached */
    private int $ddBreachTicks = 0;
```

3b. In `tick()`, directly after the unrealized-stop block (currently:)

```php
        $this->checkUnrealizedStop($price);
        if ($this->run->getKillSwitch()) {
            return true;
        }
```

insert:

```php
        // Wallet-level hard floor: global equity under the drawdown floor
        // (confirmed over BREACH_CONFIRM_TICKS) kills EVERY active run.
        $this->checkGlobalDrawdown();
        if ($this->run->getKillSwitch()) {
            return true;
        }
```

3c. Add the method next to `checkUnrealizedStop` (after `watchKilledDrawdown`):

```php
    /**
     * Global max-drawdown stop (operator directive 2026-08-01). One shared
     * wallet → one floor: equity below budget×(1−pct/100), held
     * BREACH_CONFIRM_TICKS consecutive ticks (same wick guard as the
     * unrealized stop), kills ALL active runs — buys cancel, inventory is
     * HELD. The hourly routine owns the selloff/restart decision
     * (refit-routine.md); a restart below the floor re-trips here in 3
     * ticks, so resuming requires re-baselining the shared budget first.
     * Killed/paused daemons never reach this check — any one live daemon
     * is enough to trip the whole system, and a tripped system can't
     * re-fire (every daemon lands in the killed branch above).
     */
    private function checkGlobalDrawdown(): void
    {
        $over = DrawdownGuard::check();
        if ($over === null) {
            $this->ddBreachTicks = 0;
            return;
        }
        $unpriced = $over['unpriced'] !== []
            ? sprintf(' (unpriced, counted as 0: %s)', implode(', ', $over['unpriced']))
            : '';
        if (++$this->ddBreachTicks < self::BREACH_CONFIRM_TICKS) {
            if ($this->ddBreachTicks === 1) {
                $this->log->write('Warn', 'drawdown_breach', sprintf(
                    'global equity %s under drawdown floor %s (budget %s)%s — confirming over %d ticks before stop (wick guard)',
                    bcadd($over['equity'], '0', 2),
                    bcadd($over['floor'], '0', 2),
                    $over['budget'],
                    $unpriced,
                    self::BREACH_CONFIRM_TICKS
                ));
            }
            return;
        }
        $this->ddBreachTicks = 0;
        $killed = DrawdownGuard::tripAll();
        $this->run->reload(); // pick up our own kill_switch from tripAll
        $this->flattenOnKill = false; // hold inventory; routine decides
        $this->log->write('Alert', 'drawdown_stop', sprintf(
            'GLOBAL DRAWDOWN STOP: equity %s under floor %s (max drawdown %s%% of budget %s)%s — %d run(s) killed, buys canceled, inventory HELD; the routine decides selloff/restart',
            bcadd($over['equity'], '0', 2),
            bcadd($over['floor'], '0', 2),
            $over['pct'],
            $over['budget'],
            $unpriced,
            $killed
        ));
        $this->ensureKilled();
    }
```

(`DrawdownGuard` needs no `use` line — it's in the same `App\Domains\Bot` namespace.)

- [ ] **Step 4: Run tests to verify they pass**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/DaemonDrawdownStopTest.php`
Expected: PASS (2 tests)

Regression sweep over the daemon suites (the tick flow changed for everyone):
`vendor/bin/phpunit tests/Custom/Bot/DaemonPaperFlowTest.php tests/Custom/Bot/DaemonLiveFlowTest.php tests/Custom/Bot/DaemonUnrealizedStopTest.php tests/Custom/Bot/DaemonRefitGuardTest.php tests/Custom/Bot/DaemonRefitWithInventoryTest.php`
Expected: PASS. If an existing daemon test now trips the drawdown stop (its scenario legitimately sinks equity under 750 of the default budget), fix the TEST SETUP by setting `gtbot_max_drawdown_pct` to `'0'` (disable) or the shared budget appropriately in that test's setUp — do NOT weaken the guard.

- [ ] **Step 5: Commit**

```bash
cd /path/to/apigtbot
git add .admin/src/App/Domains/Bot/Daemon.php .admin/tests/Custom/Bot/DaemonDrawdownStopTest.php
git commit -m "feat(bot): global max-drawdown stop — wick-guarded, kills all active runs, holds inventory

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

### Task 3: Config seed + gtbot_status drawdown block

**Files:**
- Modify: `.admin/config/custom_gtbot.sql` (append)
- Modify: `.admin/src/App/Mcp/Tools/GtbotStatusTool.php` (~line 96, the `return $this->ok([...])` payload)

**Interfaces:**
- Consumes: `DrawdownGuard::floor()`, `DrawdownGuard::equity()`, `DrawdownGuard::maxDrawdownPct()` (Task 1), `SimWallet::sharedBudget()`.
- Produces: `gtbot_status` response key `drawdown`: `{enabled, equity, floor, budget, max_drawdown_pct, tripped, unpriced}` (or `{enabled: false}`) — consumed by the routine (Task 5) and the operator.

- [ ] **Step 1: Append the seed (idempotent, same pattern as the file's existing rows)**

Append to `.admin/config/custom_gtbot.sql`:

```sql

-- gtbot_max_drawdown_pct: wallet-level hard floor — when global equity falls
-- below shared_budget × (1 − pct/100) for 3 consecutive ticks, ALL active
-- runs are killed (buys canceled, inventory held; routine decides recovery).
-- Empty/missing = default 25. Explicit 0 disables the stop.
INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_max_drawdown_pct', '25', 0,
    'Max total drawdown as % of gtbot_shared_budget_quote before ALL runs are killed (buys canceled, inventory held). Empty = 25. 0 disables.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_max_drawdown_pct');
```

(No local build needed — `DrawdownGuard` defaults to 25 without the row; the seed ships to prod on the next `gc deploy`, which replays unmarked `custom*.sql` base seeds.)

- [ ] **Step 2: Add the drawdown block to GtbotStatusTool**

In `GtbotStatusTool::run()`, the payload currently ends:

```php
            'orders' => ['open_buys' => $openBuys, 'open_sells' => $openSells],
            'invested_quote' => $store->investedQuote(),
```

Add ONE new top-level key right after the `'mode'` line (`'mode' => $simulated ? 'simulated' : 'real',`):

```php
            'drawdown' => self::drawdownBlock(),
```

and add the helper method to the class:

```php
    /** Global wallet-floor snapshot — same numbers the daemons enforce. */
    private static function drawdownBlock(): array
    {
        $floor = DrawdownGuard::floor();
        if ($floor === null) {
            return ['enabled' => false];
        }
        $eq = DrawdownGuard::equity();
        return [
            'enabled' => true,
            'equity' => bcadd($eq['equity'], '0', 2),
            'floor' => bcadd($floor, '0', 2),
            'budget' => SimWallet::sharedBudget(),
            'max_drawdown_pct' => DrawdownGuard::maxDrawdownPct(),
            'tripped' => bccomp($eq['equity'], $floor, 12) < 0,
            'unpriced' => $eq['unpriced'],
        ];
    }
```

with imports (top of file, alongside the existing `use App\...` lines):

```php
use App\Domains\Bot\DrawdownGuard;
use App\Domains\Bot\SimWallet;
```

(Check the existing `use` block first — add only what's missing.)

- [ ] **Step 3: Verify**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/GtbotControlToolsTest.php tests/Custom/Bot/DrawdownGuardTest.php`
Expected: PASS (status tool untested directly there, but this catches fatals via the tools' shared base; the guard tests pin the numbers).

Syntax check: `php -l src/App/Mcp/Tools/GtbotStatusTool.php`
Expected: `No syntax errors detected`

- [ ] **Step 4: Commit**

```bash
cd /path/to/apigtbot
git add .admin/config/custom_gtbot.sql .admin/src/App/Mcp/Tools/GtbotStatusTool.php
git commit -m "feat(bot): seed gtbot_max_drawdown_pct; expose drawdown block in gtbot_status

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

### Task 4: Dashboard — floor readout on the Account value tile

**Files:**
- Modify: `.admin/src/App/Domains/Dashboard/DashboardData.php` (`globalBand()`, ~line 556 and its return array + docblock)
- Modify: `.admin/src/App/Domains/Dashboard/DashboardRenderer.php` (band tiles block, ~lines 240–254)

**Interfaces:**
- Consumes: `DrawdownGuard::floor()` (Task 1); existing `globalBand()` locals `$accountValue`, `self::SCALE`.
- Produces: `globalBand()` keys `drawdown_floor: ?string`, `drawdown_under: bool`.

- [ ] **Step 1: Extend globalBand()**

In `DashboardData::globalBand()`, after the `$totalPl = ...` line, add:

```php
        // wallet-level drawdown floor (DrawdownGuard) — shown on the
        // Account value tile; red when equity is under it
        $ddFloor = DrawdownGuard::floor();
        $ddUnder = $ddFloor !== null && $accountValue !== null
            && bccomp($accountValue, $ddFloor, self::SCALE) < 0;
```

Add to the returned array (next to `'total_pl' => $totalPl,`):

```php
            'drawdown_floor' => $ddFloor,
            'drawdown_under' => $ddUnder,
```

Add the import `use App\Domains\Bot\DrawdownGuard;` (the file already imports `App\Domains\Bot\SimWallet`), and extend the `@return` docblock shape with `drawdown_floor:?string, drawdown_under:bool`.

- [ ] **Step 2: Render it**

In `DashboardRenderer` (band block, near `$partialSub`), add:

```php
        $ddFloor = $band['drawdown_floor'] ?? null;
        $floorSub = $ddFloor !== null
            ? '<div class="dash-band-sub">floor ' . $this->esc($money($ddFloor)) . '</div>'
            : '';
        $accountTone = !empty($band['drawdown_under']) ? ' is-neg' : '';
```

and change the Account value tile from:

```php
            . $this->bandTile('Account value', $dash($band['account_value'] ?? null), $partialSub)
```

to:

```php
            . $this->bandTile('Account value', $dash($band['account_value'] ?? null), $partialSub . $floorSub, $accountTone)
```

- [ ] **Step 3: Verify**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/DashboardDataTest.php tests/Custom/Bot/DashboardRendererTest.php tests/Custom/Bot/DashboardTimeTest.php`
Expected: PASS. If `DashboardRendererTest` snapshots the band HTML, update the expectation to include the new `floor …` sub-line — that change is the point of this task.

Syntax: `php -l src/App/Domains/Dashboard/DashboardData.php && php -l src/App/Domains/Dashboard/DashboardRenderer.php`

- [ ] **Step 4: Commit**

```bash
cd /path/to/apigtbot
git add .admin/src/App/Domains/Dashboard/DashboardData.php .admin/src/App/Domains/Dashboard/DashboardRenderer.php
git commit -m "feat(dashboard): drawdown floor on the Account value tile (red when under)

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

### Task 5: Routine recovery authority + runbook

**Files:**
- Modify: `.admin/docs/refit-routine.md` (step 1 ~line 41; after the KILL AUTHORITY block ~line 194)
- Modify: `.admin/docs/RUNBOOK.md` (append a short section)

**Interfaces:**
- Consumes: `gtbot_status.drawdown` block (Task 3), bot_event kind `drawdown_stop` (Task 2), existing tools `gtbot_kill {flatten}`, `crm_update` on Config and GridRun.
- Produces: prompt text only — no code.

- [ ] **Step 1: Amend step 1 of the routine**

In `refit-routine.md`, replace:

```
1. gtbot_status {run}. If kill_switch is set OR heartbeat is stale, skip this
   run (note it in the report) and move to the next.
```

with:

```
1. gtbot_status {run}. If kill_switch is set OR heartbeat is stale, skip this
   run (note it in the report) and move to the next — UNLESS the status
   drawdown block shows tripped:true or the events show a drawdown_stop:
   then run the DRAWDOWN-STOP RECOVERY procedure below instead (it covers
   all runs at once; do it once, not per run).
```

- [ ] **Step 2: Add the recovery authority section**

Insert after the KILL AUTHORITY block (after `…(Start does NOT clear the switch).`), before the closing note:

```
DRAWDOWN-STOP RECOVERY — the ONE situation where you have restart/selloff
authority. A drawdown_stop event means the wallet-level floor tripped:
global equity fell below gtbot_shared_budget_quote × (1 − gtbot_max_drawdown_pct/100)
for 3 consecutive ticks and EVERY active run was killed (buys canceled,
inventory held). The system is stable in this state — doing nothing is a
legitimate choice. Decide ONE of:
  • HOLD (default): regime read suggests the mark-to-market loss is a dip,
    not a break — leave everything killed, say so in the report, re-decide
    next hour.
  • SELLOFF: the regime read is hostile (strong_down, no basing) — liquidate
    with gtbot_kill {run, flatten:true, confirm:true, reason:"[<prompt
    version>] drawdown-stop selloff: <equity/floor numbers + regime read>"}
    per run that still holds inventory. This realizes the loss to USDT and
    stops further bleed.
  • RESTART: ONLY after re-baselining, or the stop re-trips in 3 ticks.
    Sequence: (1) crm_update Config gtbot_shared_budget_quote to current
    equity (the drawdown block's equity, rounded DOWN to whole USDT);
    (2) reallocate the runs' BudgetQuote slices to fit the new budget —
    decreases BEFORE increases (BudgetGuard refuses overcommits);
    (3) clear kills via crm_update GridRun KillSwitch=false per run you are
    restarting; (4) set conservative DeployPct (0 for hostile pairs).
Cite the drawdown block's equity/floor/budget numbers in the report
whichever branch you take. These powers apply ONLY while recovering from a
drawdown_stop — the KILL AUTHORITY limits above stay in force otherwise.
```

- [ ] **Step 3: Runbook entry**

Append to `.admin/docs/RUNBOOK.md`:

```
## Global drawdown stop (wallet floor)

When global equity < gtbot_shared_budget_quote × (1 − gtbot_max_drawdown_pct/100)
(default 25% → floor 750 on a 1000 budget) for 3 consecutive ticks, any live
daemon kills ALL active runs: buys canceled, kill switches ON, inventory HELD.
Telegram gets the `drawdown_stop` alert; `gtbot_status` shows the `drawdown`
block (equity, floor, tripped); the dashboard Account value tile turns red
with the floor shown under it.

Recovery (hourly routine is authorized, or do it manually):
- Hold: nothing to do — killed+holding is stable.
- Selloff: `gtbot_kill {run, flatten:true, confirm:true, reason:…}` per run.
- Restart: re-baseline first — set `gtbot_shared_budget_quote` to current
  equity, refit the budget slices under it (decreases before increases),
  THEN clear the kill switches. Restarting below the floor re-trips in 3
  ticks by design.
Disable/resize: config `gtbot_max_drawdown_pct` (empty = 25, 0 = off) —
takes effect next tick, no daemon restart needed.
```

- [ ] **Step 4: Commit**

```bash
cd /path/to/apigtbot
git add .admin/docs/refit-routine.md .admin/docs/RUNBOOK.md
git commit -m "docs(routine): drawdown-stop recovery authority (hold / selloff / re-baseline+restart)

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

### Task 6: Full suite, deploy, prod verification

**Files:** none new — verification and shipping.

- [ ] **Step 1: Full custom-test sweep**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/`
Expected: PASS (every suite — the tick flow and status payload changed).

- [ ] **Step 2: Deploy**

From `/path/to/apigtbot`, run `./gc deploy` **bare** (no pipes/redirects — the permission allow-rule needs the bare form; `-y` only if it asks about additive schema, and there is NO schema change in this feature). Review the dry-run/security-advisory output it prints.

- [ ] **Step 3: Verify on prod**

1. Config row landed: MCP `crm_list` entity `Config`, filter `{"Config": [["config", "gtbot_max_drawdown_pct"]]}` → one row, value `25`.
2. Daemons reloaded the new code (they self-restart on code mtime change within a tick) and `gtbot_status {run: 1}` now returns the `drawdown` block with `enabled: true`, `floor: "750.00"`, `tripped: false`, and a sane `equity` (≈ the dashboard Account value).
3. No unexpected `drawdown_breach`/`drawdown_stop` events fired on deploy: `gtbot_events` or the status alerts list is clean of those kinds.
4. Dashboard: Account value tile shows `floor 750.00` sub-line, not red (while equity > 750).

- [ ] **Step 4: Update memory**

Update `~/.claude/projects/-var-www-gc-p-apigtbot/memory/never-overcommit.md` (or add a linked sibling `drawdown-floor.md` + MEMORY.md index line): the wallet now has an ENFORCED hard floor — `gtbot_max_drawdown_pct` (25%) of `gtbot_shared_budget_quote`, 3-tick confirmed, kills all runs holding inventory, routine holds recovery authority (hold/selloff/re-baseline+restart).
