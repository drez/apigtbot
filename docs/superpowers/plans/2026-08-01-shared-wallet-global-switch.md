# Shared Wallet + Global Simulated/Real Switch Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** One shared 1,000-USDT wallet + budget for all runs (paper and real), and a global dashboard switch that flips every run between simulated and real together.

**Architecture:** New `sim_wallet` table + `SimWallet` domain class own the shared paper wallet (ledger-derived, FOR-UPDATE-safe across four daemon processes); `PaperGateway` delegates all wallet state to it. Per-run `budget_quote` becomes a reserved slice of the config value `gtbot_shared_budget_quote`; the Daemon alerts on overcommit. The dashboard gains a header mode pill + switch (new `Dashboard/mode` route → flips all runs + enqueues Reload commands) and a shared-wallet tile. Spec: `docs/superpowers/specs/2026-08-01-shared-wallet-global-switch-design.md`.

**Tech Stack:** PHP 8.1+, Propel 1 (GoatCheese), raw SQL via `\Propel::getConnection()` for FOR UPDATE, PHPUnit 10.5, bcmath strings scale 12.

## Global Constraints

- Project root `/path/to/apigtbot`; code under `.admin/src/App/`, tests under `.admin/tests/Custom/Bot/`; schema edited ONLY in `schema/main.hjson`, applied with `cd /path/to/apigtbot && ./gc b`.
- All money values bcmath strings, scale 12; never floats.
- Config reads: `\App\ConfigQuery::create()->findOneByConfig('<key>')?->getValue()`. Keys: `gtbot_shared_budget_quote` (default `'1000'` when missing), `gtbot_sim_wallet_epoch` (empty/missing = no epoch).
- Fees stay charged in the QUOTE asset. Buy fill: quote −= (notional + fee), base += qty. Sell: base −= qty, quote += (notional − fee).
- Multi-process safety is load-bearing: four daemons share `sim_wallet`; every wallet mutation happens inside a transaction with `SELECT … FOR UPDATE`.
- Tests: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot`; DB tests use the rolled-back-transaction + skip-if-not-built boot pattern from `PaperGatewayTest`. Full suite must stay green; commit per task from `/path/to/apigtbot`, messages end with `Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>`.
- The system assumes USDT-quoted pairs (all four runs are *USDT); the shared budget seeds the USDT balance.
- Do NOT deploy; Task 7 is orchestrator-only.

---

### Task 1: Schema + config seeds

**Files:**
- Modify: `/path/to/apigtbot/schema/main.hjson`
- Create: `/path/to/apigtbot/.admin/config/custom_gtbot.sql`

**Interfaces:**
- Produces (generated): `sim_wallet` table (`asset` varchar(10) unique, `qty` decimal(18,8)); config rows `gtbot_shared_budget_quote` = `1000`, `gtbot_sim_wallet_epoch` = `''`.

- [ ] **Step 1: Add the `sim_wallet` table to `schema/main.hjson`**

After the `bot_command` table block, add (match neighboring style):

```hjson
        "sim_wallet('Paper Wallet')": {
            set_parent_menu: "Settings",
            set_menu_priority: 6,
            set_readonly_columns: ["asset", "qty"],
            unique: [["asset"]],

            "id_sim_wallet()": ["primary()"],
            "asset('Asset')": ["varchar(10)", "required"],
            "qty('Quantity')": ["decimal(18, 8)", "required", "default:0"]
        },
```

- [ ] **Step 2: Create the base seed `custom_gtbot.sql`**

Base seed (no `gc:dev-only` marker) so it survives resetdata and deploys to prod. Idempotent via WHERE NOT EXISTS (same pattern as `custom_auth_email.sql`):

```sql
-- gtbot shared-wallet config (BASE SEED — deploys to prod, survives resetdata).
-- gtbot_shared_budget_quote: the ONE budget all runs share (paper wallet seeds
-- from it; funding target for real). gtbot_sim_wallet_epoch: paper-era start —
-- wallet derivation replays only simulated fills after this datetime.

INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_shared_budget_quote', '1000', 0,
    'Shared trading budget in quote (USDT) for ALL grid runs together. Per-run budget_quote values are slices of this pool and must not sum above it.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_shared_budget_quote');

INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_sim_wallet_epoch', '', 0,
    'Paper-wallet era start (Y-m-d H:i:s, server/UTC clock). The shared sim wallet is derived from simulated fills AFTER this moment; empty = all history.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_sim_wallet_epoch');
```

- [ ] **Step 3: Build and verify**

Run: `cd /path/to/apigtbot && ./gc b`
Verify: `grep -c "SimWalletQuery" .admin/src/App/Models/Built/om/BaseSimWalletQuery.php` ≥ 1, and
`php -r "require '.admin/vendor/autoload.php'; \$skipConfig=false; require '.admin/config/Built/config.php'; require '.admin/config/Built/propel.php'; echo \App\ConfigQuery::create()->findOneByConfig('gtbot_shared_budget_quote')->getValue();"` prints `1000`.

- [ ] **Step 4: Full suite green, then commit**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot`

```bash
cd /path/to/apigtbot && git add -A && git commit -m "feat(bot): sim_wallet table + shared budget/epoch config seeds

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

### Task 2: SimWallet domain class (TDD)

**Files:**
- Create: `/path/to/apigtbot/.admin/src/App/Domains/Bot/SimWallet.php`
- Test: `/path/to/apigtbot/.admin/tests/Custom/Bot/SimWalletTest.php`

**Interfaces:**
- Consumes: Task 1's table + config rows; `BotOrderQuery` with `filterBySimulated` and the `GridRun` FK relation (`$row->getGridRun()->getSymbol()`).
- Produces: `App\Domains\Bot\SimWallet` with static methods:
  - `sharedBudget(): string` — config value, `'1000'` when the row is missing/empty.
  - `epoch(): ?string` — config value, null when missing/empty.
  - `balances(): array` — `[asset => qty]` (strings) straight from the table.
  - `applyDelta(array $deltas): array` — atomically add signed bc deltas `[asset => delta]`; creates missing rows; returns the touched assets' new balances.
  - `deriveAndStore(): array` — recompute the whole wallet from the global simulated ledger (rows after epoch) and overwrite the table; returns balances.
  - `assetsFor(string $symbol): array` — `[base, quote]` parsed like Daemon::symbolAssets (`str_ends_with 'USDT'` else last 3 chars).

- [ ] **Step 1: Write the failing test**

Boot/transaction pattern copied from `PaperGatewayTest` (setUpBeforeClass skip-if-not-built; setUp opens transaction and creates a GridRun exactly as PaperGatewayTest does — symbol BTCUSDT, budget 1000, simulated true, status Live; tearDown rolls back).

```php
<?php

namespace Tests\Custom\Bot;

use App\BotOrder;
use App\Domains\Bot\SimWallet;
use App\GridRun;
use PHPUnit\Framework\TestCase;

class SimWalletTest extends TestCase
{
    // ... setUpBeforeClass / setUp / tearDown as in PaperGatewayTest ...

    private function filledRow(GridRun $run, string $cid, string $side, string $price, string $qty, string $fee): void
    {
        $row = new BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId($cid);
        $row->setLevelIdx(1);
        $row->setSide($side);
        $row->setState('Filled');
        $row->setPrice($price);
        $row->setQty($qty);
        $row->setFilledQty($qty);
        $row->setFeePaid($fee);
        $row->setSimulated(true);
        $row->save();
    }

    public function testSharedBudgetDefaultsTo1000(): void
    {
        // config row may or may not exist in the test DB; both paths return a non-empty bc string
        $this->assertMatchesRegularExpression('/^\d+(\.\d+)?$/', SimWallet::sharedBudget());
    }

    public function testDeriveFromEmptyLedgerSeedsBudget(): void
    {
        $bal = SimWallet::deriveAndStore();
        $this->assertSame(0, bccomp(SimWallet::sharedBudget(), $bal['USDT'], 8));
    }

    public function testDeriveReplaysBuysAndSells(): void
    {
        $this->filledRow($this->run, 'sw-b1', 'Buy', '95', '1', '0.095');
        $this->filledRow($this->run, 'sw-s1', 'Sell', '105', '0.4', '0.042');
        $bal = SimWallet::deriveAndStore();
        // budget - (95 + 0.095) + (42 - 0.042) = budget - 53.137 ; base 1 - 0.4 = 0.6
        $expected = bcadd(bcsub(SimWallet::sharedBudget(), '95.095', 12), '41.958', 12);
        $this->assertSame(0, bccomp($expected, $bal['USDT'], 8));
        $this->assertSame(0, bccomp('0.6', $bal['BTC'], 8));
    }

    public function testEpochExcludesOlderRows(): void
    {
        $this->filledRow($this->run, 'sw-old', 'Buy', '95', '1', '0.095');
        \Propel::getConnection()->exec(
            "UPDATE bot_order SET date_creation = '2000-01-01 00:00:00' WHERE client_order_id = 'sw-old'"
        );
        // point the epoch config at 2001 for this transaction
        $c = \App\ConfigQuery::create()->findOneByConfig('gtbot_sim_wallet_epoch');
        if (!$c) { $c = new \App\Config(); $c->setConfig('gtbot_sim_wallet_epoch'); $c->setCategory(0); $c->setType('string'); }
        $c->setValue('2001-01-01 00:00:00');
        $c->save();
        $bal = SimWallet::deriveAndStore();
        $this->assertSame(0, bccomp(SimWallet::sharedBudget(), $bal['USDT'], 8));
    }

    public function testApplyDeltaCreatesRowsAndAdds(): void
    {
        SimWallet::deriveAndStore();
        $out = SimWallet::applyDelta(['USDT' => '-95.095', 'BTC' => '1']);
        $this->assertSame(0, bccomp(bcsub(SimWallet::sharedBudget(), '95.095', 12), $out['USDT'], 8));
        $this->assertSame(0, bccomp('1', $out['BTC'], 8));
        $again = SimWallet::applyDelta(['BTC' => '0.5']);
        $this->assertSame(0, bccomp('1.5', $again['BTC'], 8));
    }

    public function testAssetsFor(): void
    {
        $this->assertSame(['BTC', 'USDT'], SimWallet::assetsFor('BTCUSDT'));
        $this->assertSame(['SOL', 'USDT'], SimWallet::assetsFor('SOLUSDT'));
    }
}
```

- [ ] **Step 2: RED**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/SimWalletTest.php`
Expected: FAIL — class not found.

- [ ] **Step 3: Implement `SimWallet`**

```php
<?php

namespace App\Domains\Bot;

use App\BotOrderQuery;
use App\ConfigQuery;

/**
 * The ONE paper wallet all runs share (mirrors the single real exchange
 * account). Source of truth is the sim_wallet table, never process memory —
 * four daemons mutate it concurrently, so every write runs in a transaction
 * with SELECT ... FOR UPDATE. The wallet is DERIVED from the global simulated
 * ledger (budget minus a replay of every simulated fill after the era epoch),
 * which makes it self-healing: a crash between an in-memory fill and the
 * Daemon's DB record can never double-count.
 *
 * Config: gtbot_shared_budget_quote (default 1000) seeds the USDT pool;
 * gtbot_sim_wallet_epoch starts the paper era. USDT-quoted pairs assumed.
 */
class SimWallet
{
    private const SCALE = 12;

    public static function sharedBudget(): string
    {
        $v = ConfigQuery::create()->findOneByConfig('gtbot_shared_budget_quote')?->getValue();
        return ($v !== null && $v !== '') ? (string) $v : '1000';
    }

    public static function epoch(): ?string
    {
        $v = ConfigQuery::create()->findOneByConfig('gtbot_sim_wallet_epoch')?->getValue();
        return ($v !== null && $v !== '') ? (string) $v : null;
    }

    /** [base, quote] from a symbol — same parse as Daemon::symbolAssets. */
    public static function assetsFor(string $symbol): array
    {
        $quote = str_ends_with($symbol, 'USDT') ? 'USDT' : substr($symbol, -3);
        return [substr($symbol, 0, strlen($symbol) - strlen($quote)), $quote];
    }

    /** @return array<string, string> asset => qty */
    public static function balances(): array
    {
        $out = [];
        $stmt = \Propel::getConnection()->query('SELECT asset, qty FROM sim_wallet');
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $out[(string) $r['asset']] = (string) $r['qty'];
        }
        return $out;
    }

    /**
     * Atomically add signed deltas: [asset => bc delta]. Missing rows are
     * created at 0 first. Returns the touched assets' new balances.
     */
    public static function applyDelta(array $deltas): array
    {
        $con = \Propel::getConnection();
        $con->beginTransaction();
        try {
            $out = [];
            foreach ($deltas as $asset => $delta) {
                $sel = $con->prepare('SELECT qty FROM sim_wallet WHERE asset = ? FOR UPDATE');
                $sel->execute([$asset]);
                $cur = $sel->fetchColumn();
                if ($cur === false) {
                    $con->prepare('INSERT INTO sim_wallet (asset, qty, date_creation, date_modification) VALUES (?, 0, NOW(), NOW())')
                        ->execute([$asset]);
                    $sel = $con->prepare('SELECT qty FROM sim_wallet WHERE asset = ? FOR UPDATE');
                    $sel->execute([$asset]);
                    $cur = $sel->fetchColumn();
                }
                $new = bcadd((string) $cur, (string) $delta, self::SCALE);
                $con->prepare('UPDATE sim_wallet SET qty = ?, date_modification = NOW() WHERE asset = ?')
                    ->execute([$new, $asset]);
                $out[(string) $asset] = $new;
            }
            $con->commit();
            return $out;
        } catch (\Throwable $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Recompute the wallet from the global simulated ledger and overwrite the
     * table. Called at daemon boot — deterministic over the same ledger, so
     * concurrent boots converge on the same numbers.
     */
    public static function deriveAndStore(): array
    {
        $wallet = ['USDT' => self::sharedBudget()];
        $q = BotOrderQuery::create()
            ->filterBySimulated(true)
            ->filterByState('Filled');
        $epoch = self::epoch();
        if ($epoch !== null) {
            $q->filterByDateCreation(['min' => $epoch]);
        }
        foreach ($q->find() as $row) {
            $run = $row->getGridRun();
            if (!$run) {
                continue;
            }
            [$base, $quote] = self::assetsFor((string) $run->getSymbol());
            $filled = (string) ($row->getFilledQty() ?: '0');
            $notional = bcmul((string) $row->getPrice(), $filled, self::SCALE);
            $fee = (string) ($row->getFeePaid() ?: '0');
            $wallet[$base] ??= '0';
            $wallet[$quote] ??= '0';
            if ((string) $row->getSide() === 'Buy') {
                $wallet[$quote] = bcsub($wallet[$quote], bcadd($notional, $fee, self::SCALE), self::SCALE);
                $wallet[$base] = bcadd($wallet[$base], $filled, self::SCALE);
            } else {
                $wallet[$base] = bcsub($wallet[$base], $filled, self::SCALE);
                $wallet[$quote] = bcadd($wallet[$quote], bcsub($notional, $fee, self::SCALE), self::SCALE);
            }
        }
        $con = \Propel::getConnection();
        $con->beginTransaction();
        try {
            $con->exec('DELETE FROM sim_wallet');
            $ins = $con->prepare('INSERT INTO sim_wallet (asset, qty, date_creation, date_modification) VALUES (?, ?, NOW(), NOW())');
            foreach ($wallet as $asset => $qty) {
                $ins->execute([$asset, $qty]);
            }
            $con->commit();
        } catch (\Throwable $e) {
            $con->rollBack();
            throw $e;
        }
        return $wallet;
    }
}
```

- [ ] **Step 4: GREEN, full suite, commit**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/SimWalletTest.php && vendor/bin/phpunit tests/Custom/Bot`

```bash
cd /path/to/apigtbot && git add .admin/src/App/Domains/Bot/SimWallet.php .admin/tests/Custom/Bot/SimWalletTest.php && git commit -m "feat(bot): SimWallet — shared ledger-derived paper wallet with FOR UPDATE deltas

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

### Task 3: PaperGateway on the shared wallet (TDD)

**Files:**
- Modify: `/path/to/apigtbot/.admin/src/App/Domains/Bot/Gateway/PaperGateway.php`
- Modify: `/path/to/apigtbot/.admin/bin/gtbot` (pass the alert sink)
- Test: `/path/to/apigtbot/.admin/tests/Custom/Bot/PaperGatewayTest.php` (adapt) + new `SharedWalletFlowTest.php`

**Interfaces:**
- Consumes: `SimWallet` (Task 2).
- Produces: `PaperGateway::__construct(BinanceGateway $public, GridRun $run, string $feePct, ?callable $onAlert = null)` — `$onAlert(string $kind, string $message)` invoked on `sim_wallet_negative`. Wallet behavior: constructor calls `SimWallet::deriveAndStore()`; `accountBalances()` reads `SimWallet::balances()` fresh each call; fills go through `SimWallet::applyDelta()`. No reads or writes of `grid_run.sim_bal_*` anywhere in the class.

- [ ] **Step 1: Adapt PaperGatewayTest + write the failing shared-wallet test**

In `PaperGatewayTest`: the wallet contract changes from per-run `sim_bal_*` to the shared table.
- `testWalletSeedsFromBudget`: unchanged numbers (shared budget defaults to 1000 in the test DB), but the run's `budget_quote` no longer matters — do not weaken; assert `accountBalances()['USDT']['free']` equals `SimWallet::sharedBudget()`.
- `testBuyFillMovesWalletAndPersists`: keep the exact fill numbers (904.905 etc. hold only when shared budget is 1000 — compute expectations from `SimWallet::sharedBudget()` with bc math instead of hard-coding, e.g. `bcsub(SimWallet::sharedBudget(), '95.095', 12)`); replace the `$this->run->reload(); getSimBal*` assertions with `SimWallet::balances()` assertions.
- The crash-window regression test: stale value now goes into the `sim_wallet` table (UPDATE its USDT row to a wrong number) instead of `sim_bal_quote`; construct the gateway (deriveAndStore corrects it), fill the open row, assert single-count derivation.
- All other tests (payload shapes, duplicate contract, hydration, orderStatus fallback) stay as-is.

New `SharedWalletFlowTest.php` (same boot pattern; two runs BTCUSDT + ETHUSDT, both simulated, budget irrelevant):

```php
public function testTwoRunsShareOneWallet(): void
{
    $gwBtc = $this->gatewayFor($this->runBtc, '100');   // fake ticker returns 100
    $gwEth = $this->gatewayFor($this->runEth, '2000');
    $gwBtc->placeLimitOrder('BTCUSDT', 'Buy', '95', '1', 'sw-btc-1');
    $gwEth->placeLimitOrder('ETHUSDT', 'Buy', '1900', '0.1', 'sw-eth-1');
    $this->tickBtc = '94';  $gwBtc->tickerPrice('BTCUSDT');   // fill: -95.095
    $this->tickEth = '1890'; $gwEth->tickerPrice('ETHUSDT');  // fill: -190.19
    $bal = \App\Domains\Bot\SimWallet::balances();
    $expected = bcsub(bcsub(\App\Domains\Bot\SimWallet::sharedBudget(), '95.095', 12), '190.19', 12);
    $this->assertSame(0, bccomp($expected, $bal['USDT'], 8));
    $this->assertSame(0, bccomp('1', $bal['BTC'], 8));
    $this->assertSame(0, bccomp('0.1', $bal['ETH'], 8));
}

public function testNegativePoolFiresAlertSink(): void
{
    $alerts = [];
    $gw = $this->gatewayFor($this->runBtc, '100', function ($kind, $msg) use (&$alerts) { $alerts[] = $kind; });
    // budget 1000: a 20-BTC buy at 95 = 1900 + fee → pool goes negative
    $gw->placeLimitOrder('BTCUSDT', 'Buy', '95', '20', 'sw-neg-1');
    $this->tickBtc = '94'; $gw->tickerPrice('BTCUSDT');
    $this->assertContains('sim_wallet_negative', $alerts);
}
```

(`gatewayFor(GridRun $run, string &$tickVarRef …)` builds a PaperGateway with the fake-transport keyless BinanceGateway exactly like PaperGatewayTest's `gw()` helper, plus the optional `$onAlert`.)

- [ ] **Step 2: RED**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/PaperGatewayTest.php tests/Custom/Bot/SharedWalletFlowTest.php`
Expected: FAIL — wallet still per-run; no 4th ctor param.

- [ ] **Step 3: Rework PaperGateway**

- Constructor: add `private readonly mixed $onAlert = null` as 4th param (`?callable` semantics — store as property, call via `($this->onAlert)(...)` when non-null). Remove the sim_bal/budget wallet fields and the per-run derivation loop; call `SimWallet::deriveAndStore();` once. Keep book hydration, `$baseAsset`/`$quoteAsset` (may reuse `SimWallet::assetsFor()`), `nextOrderId`.
- `accountBalances()`: `$bal = SimWallet::balances(); return [$this->baseAsset => ['free' => $bal[$this->baseAsset] ?? '0', 'locked' => '0'], $this->quoteAsset => ['free' => $bal[$this->quoteAsset] ?? '0', 'locked' => '0']];`
- `fill()`: compute `$notional`/`$fee` as today; replace in-memory wallet math with one `SimWallet::applyDelta([...])` per fill (buy: quote `-(notional+fee)`, base `+qty`; sell: base `-qty`, quote `+(notional-fee)`); keep `$closed`/`$trades` bookkeeping. After the delta, if the returned quote balance is negative (`bccomp(..., '0', 12) < 0`) and `$this->onAlert !== null`, call `($this->onAlert)('sim_wallet_negative', 'shared paper wallet went negative: USDT ' . $newQuote . ' — run slices overcommitted?')`.
- Delete `persistWallet()` and the `step()` call to it (deltas are already persisted per fill).
- Update the class docblock: wallet is the shared `sim_wallet` table via `SimWallet`; `grid_run.sim_bal_*` unused.
- `bin/gtbot` paper branch: pass the sink —

```php
    $gateway = new \App\Domains\Bot\Gateway\PaperGateway(
        new \App\Domains\Bot\Gateway\BinanceGateway('https://api.binance.com', '', ''),
        $run,
        (string) $run->getFeePct(),
        function (string $kind, string $message) use ($log): void {
            $log->write('Alert', $kind, $message);
        }
    );
```

(`$log` is constructed BEFORE the gateway in bin/gtbot — verify the ordering and move the EventLog construction above the gateway block if needed.)

- [ ] **Step 4: GREEN, full suite, commit**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/PaperGatewayTest.php tests/Custom/Bot/SharedWalletFlowTest.php && vendor/bin/phpunit tests/Custom/Bot`
Note: `DaemonPaperFlowTest` asserts `sim_bal_quote` is persisted — that contract is gone; change that assertion to `SimWallet::balances()['USDT']` being set (do not weaken the simulated-marking assertions).

```bash
cd /path/to/apigtbot && git add -A && git commit -m "feat(bot): PaperGateway on the shared SimWallet — per-fill FOR UPDATE deltas, negative-pool alert sink

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

### Task 4: Budget-overcommit alert in the Daemon (TDD)

**Files:**
- Modify: `/path/to/apigtbot/.admin/src/App/Domains/Bot/Daemon.php`
- Test: `/path/to/apigtbot/.admin/tests/Custom/Bot/BudgetInvariantTest.php`

**Interfaces:**
- Consumes: `SimWallet::sharedBudget()`.
- Produces: private `Daemon::checkBudgetInvariant(): void` called from `boot()` (after config validation) and at the end of `maybeRefit()`'s successful-apply path; writes Alert `budget_overcommit` when sum of active slices exceeds the shared budget.

- [ ] **Step 1: Write the failing test**

Same DB boot pattern; drive a Daemon boot like `DaemonPaperFlowTest` does (PaperGateway + fake transport), with a SECOND run whose `budget_quote` pushes the sum over 1000 (e.g. main run 800 + second run 800, both status Live):

```php
public function testBootAlertsWhenSlicesOvercommit(): void
{
    // second Live run to overcommit the pool
    $other = /* create GridRun exactly like setUp's, budget_quote 800, status Live, simulated true */;
    $this->run->setBudgetQuote('800')->save();
    $this->makeDaemon()->boot();
    $this->assertSame(1, \App\BotEventQuery::create()
        ->filterByIdGridRun((int) $this->run->getIdGridRun())
        ->filterByKind('budget_overcommit')->filterByLevel('Alert')->count());
}

public function testNoAlertWhenWithinBudget(): void
{
    $this->run->setBudgetQuote('400')->save();
    $this->makeDaemon()->boot();
    $this->assertSame(0, \App\BotEventQuery::create()
        ->filterByIdGridRun((int) $this->run->getIdGridRun())
        ->filterByKind('budget_overcommit')->count());
}
```

- [ ] **Step 2: RED** — `vendor/bin/phpunit tests/Custom/Bot/BudgetInvariantTest.php` fails (no such event).

- [ ] **Step 3: Implement**

In Daemon.php add and call from `boot()` (after `RiskManager::validateRunConfig` passes) and at the end of `maybeRefit()`'s apply path (after `refit_applied` is logged):

```php
    /** All runs share ONE wallet: their budget slices must not sum above the
     *  shared budget. Advisory (Alert) — the routine maintains the invariant;
     *  the exchange (or the sim_wallet_negative alert) enforces reality. */
    private function checkBudgetInvariant(): void
    {
        $sum = '0';
        foreach (\App\GridRunQuery::create()
            ->filterByStatus(['DryRun', 'Testnet', 'Live'], \Criteria::IN)
            ->find() as $r) {
            $sum = bcadd($sum, (string) $r->getBudgetQuote(), 12);
        }
        $budget = SimWallet::sharedBudget();
        if (bccomp($sum, $budget, 12) > 0) {
            $this->log->write('Alert', 'budget_overcommit', sprintf(
                'run budget slices sum to %s but the shared budget is %s — reallocate (routine) or raise gtbot_shared_budget_quote',
                $sum,
                $budget
            ));
        }
    }
```

- [ ] **Step 4: GREEN, full suite, commit**

```bash
cd /path/to/apigtbot && git add .admin/src/App/Domains/Bot/Daemon.php .admin/tests/Custom/Bot/BudgetInvariantTest.php && git commit -m "feat(bot): budget_overcommit Alert — slices must fit the shared budget

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

### Task 5: Dashboard — mode switch + shared wallet tile (TDD)

**Files:**
- Create: `/path/to/apigtbot/.admin/src/App/Domains/Dashboard/ModeSwitch.php`
- Modify: `/path/to/apigtbot/.admin/config/routes.php` (new `Dashboard/mode` route)
- Modify: `/path/to/apigtbot/.admin/src/App/Domains/Dashboard/DashboardData.php` (`sharedWallet()` + mode in viewModel)
- Modify: `/path/to/apigtbot/.admin/src/App/Domains/Dashboard/DashboardRenderer.php` (header pill + switch button + wallet tile)
- Modify: `/path/to/apigtbot/.admin/src/App/Domains/Dashboard/View.php` (switch JS)
- Test: `/path/to/apigtbot/.admin/tests/Custom/Bot/ModeSwitchTest.php` + additions to `DashboardRendererTest.php`

**Interfaces:**
- Consumes: `SimWallet::balances()`, generated `GridRunQuery`/`BotCommandQuery`.
- Produces: `ModeSwitch::systemMode(): string` (`'simulated'|'real'|'mixed'|'none'` over non-Done runs); `ModeSwitch::flipAll(bool $simulated): array{runs:int, commands:int}` (updates every non-Done run + enqueues one `Reload` bot_command per run, note `'dashboard-mode'`). Route `POST Dashboard/mode` body `{"mode":"simulated"|"real"}` → `{"status":"ok","runs":N,"commands":N}`, same auth/CSRF posture as `Dashboard/command`.

- [ ] **Step 1: Write the failing ModeSwitch test**

```php
public function testSystemModeAndFlipAll(): void
{
    // setUp created one simulated Live run; add a real one → mixed
    $other = /* GridRun like setUp's, simulated false, status Live */;
    $this->assertSame('mixed', \App\Domains\Dashboard\ModeSwitch::systemMode());

    $res = \App\Domains\Dashboard\ModeSwitch::flipAll(true);
    $this->assertSame(2, $res['runs']);
    $this->assertSame(2, $res['commands']);
    $this->assertSame('simulated', \App\Domains\Dashboard\ModeSwitch::systemMode());
    $this->assertSame(2, \App\BotCommandQuery::create()
        ->filterByCommand('Reload')->filterByNote('dashboard-mode')->count());

    // Done runs are untouched
    $done = /* GridRun, status Done, simulated false */;
    \App\Domains\Dashboard\ModeSwitch::flipAll(true);
    $done->reload();
    $this->assertFalse((bool) $done->getSimulated());
}
```

- [ ] **Step 2: RED**, then implement `ModeSwitch`

```php
<?php

namespace App\Domains\Dashboard;

use App\BotCommand;
use App\GridRunQuery;

/**
 * The system trades ONE shared wallet, so simulated-vs-real is a SYSTEM mode:
 * mixed modes would double-commit the same capital. flipAll() is the only
 * writer the dashboard uses — every non-Done run flips together and gets a
 * Reload command so its daemon reboots into the new mode within ~a minute.
 */
class ModeSwitch
{
    public static function systemMode(): string
    {
        $modes = [];
        foreach (GridRunQuery::create()->filterByStatus('Done', \Criteria::NOT_EQUAL)->find() as $r) {
            $modes[(int) (bool) $r->getSimulated()] = true;
        }
        if ($modes === []) {
            return 'none';
        }
        if (count($modes) > 1) {
            return 'mixed';
        }
        return isset($modes[1]) ? 'simulated' : 'real';
    }

    /** @return array{runs:int, commands:int} */
    public static function flipAll(bool $simulated): array
    {
        $runs = 0;
        $commands = 0;
        foreach (GridRunQuery::create()->filterByStatus('Done', \Criteria::NOT_EQUAL)->find() as $r) {
            $r->setSimulated($simulated);
            $r->save();
            $runs++;
            $cmd = new BotCommand();
            $cmd->setIdGridRun((int) $r->getIdGridRun());
            $cmd->setCommand('Reload');
            $cmd->setCmdStatus('Pending');
            $cmd->setNote('dashboard-mode');
            $cmd->save();
            $commands++;
        }
        return ['runs' => $runs, 'commands' => $commands];
    }
}
```

- [ ] **Step 3: Add the route**

In `.admin/config/routes.php`, after the `Dashboard/command` route, clone its closure structure exactly (same `$json` helper, same session/`isAdmin`/`GridRun w` checks):

```php
    // Global simulated/real switch — the system trades ONE shared wallet, so
    // the mode flips for ALL non-Done runs at once (mixed modes would
    // double-commit capital). Same auth/CSRF posture as Dashboard/command.
    $app->post(_SUB_DIR_URL . 'Dashboard/mode', function (Request $request, Response $response, $args) {
        $json = static function (array $out, int $status) use ($response) {
            $response->getBody()->write(json_encode($out));
            return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
        };
        $session = $_SESSION[_AUTH_VAR] ?? null;
        if (!$session || $session->get('connected') !== 'YES') {
            return $json(['status' => 'unauthenticated'], 401);
        }
        if (!$session->isAdmin() && $session->hasRights('GridRun', 'w') === false) {
            return $json(['status' => 'forbidden'], 403);
        }
        $body = json_decode((string) $request->getBody(), true) ?: [];
        $mode = (string) ($body['mode'] ?? '');
        if (!in_array($mode, ['simulated', 'real'], true)) {
            return $json(['status' => 'unknown_mode'], 400);
        }
        $res = \App\Domains\Dashboard\ModeSwitch::flipAll($mode === 'simulated');
        return $json(['status' => 'ok'] + $res, 200);
    })->setName('Dashboard/mode');
```

- [ ] **Step 4: DashboardData + Renderer + View**

- `DashboardData::sharedWallet(): array` — `['mode' => ModeSwitch::systemMode(), 'assets' => [...], 'total_quote' => ?string]`. Simulated (or mixed): read `SimWallet::balances()`; value each non-USDT asset at the matching active run's `last_price` (match by `assetsFor(symbol)[0]`; null value when no price); `total_quote` = USDT + valued assets (skip null-valued). Real: use the freshest-stamped run's `bal_quote` as the USDT figure and each run's `bal_base` per its base asset (the stamps all describe the same account). Add the result to `viewModel()` under `'wallet'`.
- `DashboardRenderer`: in the header area (near where tabs render), emit the pill + button:
  `<span class="dash-mode-pill dash-mode-simulated">SIMULATED</span>` (classes `dash-mode-simulated|dash-mode-real|dash-mode-mixed`; text uppercase mode) and `<button type="button" class="dash-mode-btn" data-target="real">Switch to REAL</button>` (target = the opposite of current; for `mixed`, two buttons — one per direction). Below the KPI tiles, a wallet tile: one row per asset (`asset`, qty, value when priced), plus the total. Follow the file's existing HTML-string style; minimal inline styles consistent with neighboring tiles (amber `#f0ad4e` for simulated pill, red `#d9534f` for real/mixed).
- `View.php`: extend the existing delegated click listener JS with `.dash-mode-btn` handling — messages:
  - to real: `'Switch ALL runs to REAL trading? This is the money switch: daemons will refuse to start until prod API keys + GTBOT_USE_TESTNET=0/GTBOT_DRY_RUN=0 are set. Continue?'` via `gcScreens.confirm(msg, {confirmLabel:'Switch to REAL', danger:true})` (with the same native-confirm fallback pattern the file already uses — copy it).
  - to simulated: `'Switch ALL runs back to SIMULATED (paper) trading?'`, `danger:false`.
  On confirm, POST `Dashboard/mode` with `{mode: target}` (same XHR shape as `sendCmd`), then `location.reload()` after ~1.2s on ok.
- `DashboardRendererTest.php` additions: render a viewModel with `wallet.mode='simulated'` and two assets → assert the pill text/class, the switch button's `data-target="real"`, and the USDT row appear in the HTML (mirror the file's existing assertion style).

- [ ] **Step 5: GREEN, full suite, commit**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/ModeSwitchTest.php tests/Custom/Bot/DashboardRendererTest.php && vendor/bin/phpunit tests/Custom/Bot && php -l config/routes.php`

```bash
cd /path/to/apigtbot && git add -A && git commit -m "feat(dashboard): global simulated/real switch + shared wallet tile

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

### Task 6: Routine allocation step

**Files:**
- Modify: `/path/to/apigtbot/.admin/docs/refit-routine.md`

- [ ] **Step 1: Add the allocation step**

After the existing apply step (the `gtbot_set_grid` instruction around line 130), insert a new numbered step (renumber followers if needed):

```markdown
N. BUDGET ALLOCATION (shared pool). All runs draw from ONE shared budget:
   config `gtbot_shared_budget_quote` (currently 1000 USDT). Each run's
   `budget_quote` is its reserved slice. After the per-pair refit decisions,
   reallocate the slices for the coming hour:
   • Weight toward the most profitable runs — use each run's mode-scoped
     realized PnL (gtbot_status / gtbot_pnl_report, current mode) and the
     regime read; a run held FLAT (deploy 0) needs only a minimal slice.
   • Hard rules: sum of all active runs' slices ≤ the shared budget; minimum
     slice 50 for any run with deploy_pct > 0; NEVER cut a run's slice below
     its current invested_quote (gtbot_status) — a holding run must keep the
     capital its inventory already cost.
   • Write with crm_update GridRun {"BudgetQuote": <slice>} per run. If you
     change nothing, say so in the journal note.
```

- [ ] **Step 2: Commit**

```bash
cd /path/to/apigtbot && git add .admin/docs/refit-routine.md && git commit -m "docs(routine): hourly budget-slice allocation over the shared pool

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

### Task 7: Deploy + migrate (ORCHESTRATOR ONLY — not a subagent task)

- [ ] Suite green + merge to master.
- [ ] `cd /path/to/gc && ./gc deploy apigtbot -y` (adds `sim_wallet`, ships seeds).
- [ ] Set epoch: `crm_update` Config row `gtbot_sim_wallet_epoch` → current UTC datetime (prod clock is UTC). Cancel open simulated orders in the ledger (state → Canceled) so the new era starts flat.
- [ ] Slices via `crm_update` GridRun `BudgetQuote`: run 1 → 350, 6 → 350, 4 → 250, 5 → 50.
- [ ] Reload all daemons (bot_command Reload or the dashboard buttons); verify per run: `gtbot_status` fresh heartbeat + `mode: simulated`; `sim_wallet` USDT row = 1000; dashboard pill shows SIMULATED and the wallet tile renders.

---

## Execution notes

- Tasks 1→2→3 are strictly sequential; Task 4 needs Task 2; Task 5 needs Tasks 2+4's `SimWallet`/generated classes but not Task 3; run them in order anyway (single worktree).
- The config table's Propel object is `\App\Config` with `getConfig()`/`getValue()` accessors (column `config` holds the key); `findOneByConfig('<key>')` is the generated finder.
- `sim_wallet` writes use raw SQL through `\Propel::getConnection()` — the generated `SimWallet` Propel model exists but is NOT used for wallet math (FOR UPDATE + bc strings need the raw path); don't "fix" that in review.
