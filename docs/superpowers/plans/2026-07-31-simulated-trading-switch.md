# Simulated Trading Switch Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Per-run `simulated` switch (default ON): mainnet market data with locally simulated fills, history kept and marked, stats split per mode; turning it OFF is the env-double-gated act of trading real.

**Architecture:** Extract `GatewayInterface` from the 8 methods the Daemon uses; new `PaperGateway` proxies public endpoints to mainnet and simulates signed ones with a persisted paper wallet. `OrderStore` becomes mode-aware (stamps + filters `simulated`). Stats surfaces filter by the run's current mode. Spec: `docs/superpowers/specs/2026-07-31-simulated-trading-design.md`.

**Tech Stack:** PHP 8.1+, Propel 1 ORM (GoatCheese), PHPUnit 10.5, bcmath strings for all money.

## Global Constraints

- Project root: `/path/to/apigtbot`; all PHP code under `.admin/src/App/`, tests under `.admin/tests/Custom/Bot/`.
- All money values are bcmath **strings**, scale 12 (`self::SCALE = 12`), never floats.
- Tests: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/<File>.php`. DB-backed tests run inside a rolled-back transaction and `markTestSkipped` when the project isn't built (copy the boot pattern from `DaemonLiveFlowTest`).
- Schema is edited ONLY in `/path/to/apigtbot/schema/main.hjson`, then applied with `cd /path/to/apigtbot && ./gc b` (never edit `.admin/src/App/Models/Built` or `config/Built` by hand).
- Commit after each task from `/path/to/apigtbot` (project git repo). Commit messages end with `Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>`.
- Existing suite must stay green: `cd .admin && vendor/bin/phpunit tests/Custom/Bot` before each commit.
- Do NOT deploy to prod; Task 7 is orchestrator-only.

---

### Task 1: Schema — switch, marks, paper wallet, UI cards

**Files:**
- Modify: `/path/to/apigtbot/schema/main.hjson`

**Interfaces:**
- Produces (after `./gc b` regenerates Propel models): `GridRun::getSimulated(): bool-ish`, `GridRun::getSimBalBase()/getSimBalQuote()/setSimBalBase()/setSimBalQuote()`, `BotOrder::getSimulated()/setSimulated()`, `TradeCycle::getSimulated()/setSimulated()`, and query filters `BotOrderQuery/TradeCycleQuery::filterBySimulated(bool)`.

- [ ] **Step 1: Add `simulated` switch + paper wallet columns to `grid_run`**

In `schema/main.hjson`, after the `"kill_switch(...)"` line add:

```hjson
            "simulated('Simulated')": ["boolean()", "default:1"],
```

After the `"bal_quote(...)"` line add:

```hjson
            "sim_bal_base('Paper wallet base (daemon-managed)')": ["decimal(18, 8)", "not-required", "default:null"],
            "sim_bal_quote('Paper wallet quote (daemon-managed)')": ["decimal(18, 8)", "not-required", "default:null"],
```

Extend grid_run's `set_list_hide_columns` with the two new columns:

```hjson
            set_list_hide_columns: ["run_uid", "breakout_buffer_pct", "max_open_orders", "last_tick_at", "sim_bal_base", "sim_bal_quote"],
```

- [ ] **Step 2: Mark `bot_order` rows**

In the `bot_order` table: after the `"is_legacy(...)"` line add:

```hjson
            "simulated('Simulated')": ["boolean()", "default:0"],
```

Add `"simulated"` to `set_readonly_columns`, and a search filter entry inside `add_search_columns`:

```hjson
                "Simulated": [["simulated", "val"]]
```

- [ ] **Step 3: Mark `trade_cycle` rows + split summary cards**

In `trade_cycle`: after `"fees_total(...)"` add:

```hjson
            "simulated('Simulated')": ["boolean()", "default:0"],
```

Replace `trade_cycle`'s `set_summary_cards` with per-mode pairs:

```hjson
            set_summary_cards: [
                { label: "PnL (sim)",    agg: "sum", col: "realized_pnl", format: "money", filter: { simulated: 1 } },
                { label: "PnL (real)",   agg: "sum", col: "realized_pnl", format: "money", filter: { simulated: 0 } },
                { label: "Fees (sim)",   agg: "sum", col: "fees_total",   format: "money", filter: { simulated: 1 } },
                { label: "Fees (real)",  agg: "sum", col: "fees_total",   format: "money", filter: { simulated: 0 } },
                { label: "Cycles (sim)", agg: "count", filter: { simulated: 1 } },
                { label: "Cycles (real)",agg: "count", filter: { simulated: 0 } }
            ],
```

Also add to `trade_cycle` (it has no `add_search_columns` yet):

```hjson
            add_search_columns: {
                "Simulated": [["simulated", "val"]]
            },
```

If the converter warns about the filter/search syntax, consult the `goatcheese-behaviors` skill and `vendor/apigoat/goatcheese/docs/behavior_naming.txt` for the exact accepted form — do not guess new syntax into the emitter.

- [ ] **Step 4: Build and verify columns exist**

Run: `cd /path/to/apigtbot && ./gc b` (answer defaults to any prompt; it is interactive-safe locally).
Then verify: `grep -c "getSimBalQuote\|filterBySimulated" .admin/src/App/Models/Built/BaseGridRun.php .admin/src/App/Models/Built/BaseTradeCycleQuery.php` — both files must match ≥1.
Expected: build succeeds, generated models expose the new accessors.

- [ ] **Step 5: Run existing suite**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot`
Expected: green (new columns are additive with defaults).

- [ ] **Step 6: Commit**

```bash
cd /path/to/apigtbot && git add -A && git commit -m "feat(bot): simulated switch schema — grid_run.simulated, paper wallet cols, per-mode marks + summary cards

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

### Task 2: Extract GatewayInterface

**Files:**
- Create: `/path/to/apigtbot/.admin/src/App/Domains/Bot/Gateway/GatewayInterface.php`
- Modify: `/path/to/apigtbot/.admin/src/App/Domains/Bot/Gateway/BinanceGateway.php` (class line only)
- Modify: `/path/to/apigtbot/.admin/src/App/Domains/Bot/Daemon.php` (use + constructor type-hint)

**Interfaces:**
- Produces: `App\Domains\Bot\Gateway\GatewayInterface` with exactly the 8 method signatures below. `Daemon::__construct(GridRun $run, GatewayInterface $gateway, bool $dryRun, EventLog $log)`.

- [ ] **Step 1: Create the interface**

```php
<?php

namespace App\Domains\Bot\Gateway;

/**
 * The gateway surface the Daemon drives. BinanceGateway implements it against
 * the real (or testnet) exchange; PaperGateway simulates the signed half
 * against mainnet public data. Payload shapes are Binance REST shapes — the
 * Daemon parses them identically whichever implementation is behind it.
 */
interface GatewayInterface
{
    /** Symbol entry from exchangeInfo (filters live under ['filters']). */
    public function exchangeInfo(string $symbol): array;

    public function tickerPrice(string $symbol): string;

    /** @return array<string, array{free:string, locked:string}> keyed by asset */
    public function accountBalances(): array;

    public function openOrders(string $symbol): array;

    public function orderStatus(string $symbol, string $clientOrderId): array;

    /** Must honor the idempotent-retry contract: a duplicate client order id
     *  returns ['duplicate' => true, 'clientOrderId' => $clientOrderId]. */
    public function placeLimitOrder(string $symbol, string $side, string $price, string $qty, string $clientOrderId): array;

    public function cancelOrder(string $symbol, string $clientOrderId): array;

    public function myTrades(string $symbol, array $extra = []): array;
}
```

- [ ] **Step 2: Implement it on BinanceGateway**

Change `class BinanceGateway` to `class BinanceGateway implements GatewayInterface`. No other change — all 8 methods already exist with matching signatures.

- [ ] **Step 3: Re-type the Daemon**

In `Daemon.php`: change `use App\Domains\Bot\Gateway\BinanceGateway;` to `use App\Domains\Bot\Gateway\GatewayInterface;` and the constructor property `private readonly BinanceGateway $gateway` to `private readonly GatewayInterface $gateway`.

- [ ] **Step 4: Run the suite**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot`
Expected: green (BinanceGateway satisfies the interface; all existing Daemon constructions still type-check).

- [ ] **Step 5: Commit**

```bash
cd /path/to/apigtbot && git add -A && git commit -m "refactor(bot): extract GatewayInterface; Daemon depends on the interface

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

### Task 3: PaperGateway (TDD)

**Files:**
- Create: `/path/to/apigtbot/.admin/src/App/Domains/Bot/Gateway/PaperGateway.php`
- Test: `/path/to/apigtbot/.admin/tests/Custom/Bot/PaperGatewayTest.php`

**Interfaces:**
- Consumes: `GatewayInterface` (Task 2), generated model accessors (Task 1).
- Produces: `PaperGateway::__construct(BinanceGateway $public, \App\GridRun $run, string $feePct)`. Implements `GatewayInterface`. Fill side effects: paper wallet updated and persisted to `grid_run.sim_bal_base/sim_bal_quote` on every fill. Hydrates its book in the constructor from open `bot_order` rows with `simulated=1`.

- [ ] **Step 1: Write the failing test**

DB-backed test (transaction + skip-if-not-built boot, copied from `DaemonLiveFlowTest::setUpBeforeClass`). The public gateway is a `BinanceGateway` built with an injected fake transport (same trick as `DaemonLiveFlowTest`'s `ExchangeSim` — but here a tiny closure suffices since only public GETs pass through).

```php
<?php

namespace Tests\Custom\Bot;

use App\BotOrder;
use App\Domains\Bot\Gateway\BinanceApiError;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Gateway\PaperGateway;
use App\GridRun;
use PHPUnit\Framework\TestCase;

class PaperGatewayTest extends TestCase
{
    private static bool $booted = false;
    private GridRun $run;
    /** @var string next price the fake mainnet ticker returns */
    private string $tick = '100';

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
        \Propel::getConnection()->beginTransaction();
        $r = new GridRun();
        $r->setLabel('papertest-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Live');
        $r->setSimulated(true);
        $r->setPLow('90');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('1000');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('1000');
        $r->setMaxOrderQuote('500');
        $r->setDailyLossLimitQuote('100');
        $r->save();
        $this->run = $r;
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function gw(): PaperGateway
    {
        $transport = function (string $method, string $url, array $headers, ?string $body): array {
            if (str_contains($url, '/api/v3/ticker/price')) {
                return ['status' => 200, 'headers' => [], 'body' => json_encode(['price' => $this->tick])];
            }
            if (str_contains($url, '/api/v3/exchangeInfo')) {
                return ['status' => 200, 'headers' => [], 'body' => json_encode(['symbols' => [['symbol' => 'BTCUSDT', 'filters' => []]]])];
            }
            throw new \RuntimeException("unexpected paper-mode HTTP call: $url");
        };
        $public = new BinanceGateway('https://api.binance.com', '', '', $transport);
        return new PaperGateway($public, $this->run, '0.001');
    }

    public function testWalletSeedsFromBudget(): void
    {
        $bal = $this->gw()->accountBalances();
        $this->assertSame('0', $bal['BTC']['free']);
        $this->assertSame(0, bccomp('1000', $bal['USDT']['free'], 8));
    }

    public function testBuyFillMovesWalletAndPersists(): void
    {
        $gw = $this->gw();
        $gw->placeLimitOrder('BTCUSDT', 'Buy', '95', '1', 'cid-b1');
        $this->assertCount(1, $gw->openOrders('BTCUSDT'));

        $this->tick = '94';
        $gw->tickerPrice('BTCUSDT'); // advances the book → fill

        $this->assertCount(0, $gw->openOrders('BTCUSDT'));
        $st = $gw->orderStatus('BTCUSDT', 'cid-b1');
        $this->assertSame('FILLED', $st['status']);
        $this->assertSame(0, bccomp('1', (string) $st['executedQty'], 8));

        // wallet: quote -(95 + 0.095 fee), base +1 — and persisted on the row
        $bal = $gw->accountBalances();
        $this->assertSame(0, bccomp('1', $bal['BTC']['free'], 8));
        $this->assertSame(0, bccomp('904.905', $bal['USDT']['free'], 8));
        $this->run->reload();
        $this->assertSame(0, bccomp('1', (string) $this->run->getSimBalBase(), 8));
        $this->assertSame(0, bccomp('904.905', (string) $this->run->getSimBalQuote(), 8));
    }

    public function testSellFillCreditsQuoteMinusFee(): void
    {
        $gw = $this->gw();
        $gw->placeLimitOrder('BTCUSDT', 'Buy', '95', '1', 'cid-b1');
        $this->tick = '94';
        $gw->tickerPrice('BTCUSDT');
        $gw->placeLimitOrder('BTCUSDT', 'Sell', '105', '1', 'cid-s1');
        $this->tick = '106';
        $gw->tickerPrice('BTCUSDT');

        $bal = $gw->accountBalances();
        $this->assertSame(0, bccomp('0', $bal['BTC']['free'], 8));
        // 904.905 + 105 - 0.105 = 1009.8
        $this->assertSame(0, bccomp('1009.8', $bal['USDT']['free'], 8));
    }

    public function testMyTradesReportsQuoteCommission(): void
    {
        $gw = $this->gw();
        $res = $gw->placeLimitOrder('BTCUSDT', 'Buy', '95', '1', 'cid-b1');
        $this->tick = '94';
        $gw->tickerPrice('BTCUSDT');
        $trades = $gw->myTrades('BTCUSDT', ['orderId' => $res['orderId']]);
        $this->assertCount(1, $trades);
        $this->assertSame('USDT', $trades[0]['commissionAsset']);
        $this->assertSame(0, bccomp('0.095', (string) $trades[0]['commission'], 8));
    }

    public function testDuplicateCidHonorsIdempotentContract(): void
    {
        $gw = $this->gw();
        $gw->placeLimitOrder('BTCUSDT', 'Buy', '95', '1', 'cid-b1');
        $res = $gw->placeLimitOrder('BTCUSDT', 'Buy', '95', '1', 'cid-b1');
        $this->assertTrue($res['duplicate']);
    }

    public function testCancelRemovesFromBookAndStatusReflectsIt(): void
    {
        $gw = $this->gw();
        $gw->placeLimitOrder('BTCUSDT', 'Buy', '95', '1', 'cid-b1');
        $gw->cancelOrder('BTCUSDT', 'cid-b1');
        $this->assertCount(0, $gw->openOrders('BTCUSDT'));
        $this->assertSame('CANCELED', $gw->orderStatus('BTCUSDT', 'cid-b1')['status']);
    }

    public function testHydratesBookFromSimulatedOpenRows(): void
    {
        $row = new BotOrder();
        $row->setIdGridRun((int) $this->run->getIdGridRun());
        $row->setClientOrderId('cid-hyd');
        $row->setLevelIdx(1);
        $row->setSide('Buy');
        $row->setState('BUY_OPEN');
        $row->setPrice('95');
        $row->setQty('1');
        $row->setSimulated(true);
        $row->save();
        // a REAL open row must NOT be adopted into the paper book
        $real = new BotOrder();
        $real->setIdGridRun((int) $this->run->getIdGridRun());
        $real->setClientOrderId('cid-real');
        $real->setLevelIdx(2);
        $real->setSide('Buy');
        $real->setState('BUY_OPEN');
        $real->setPrice('96');
        $real->setQty('1');
        $real->setSimulated(false);
        $real->save();

        $gw = $this->gw();
        $open = $gw->openOrders('BTCUSDT');
        $this->assertCount(1, $open);
        $this->assertSame('cid-hyd', $open[0]['clientOrderId']);
    }

    public function testOrderStatusFallsBackToDbAfterRestart(): void
    {
        $row = new BotOrder();
        $row->setIdGridRun((int) $this->run->getIdGridRun());
        $row->setClientOrderId('cid-old');
        $row->setLevelIdx(1);
        $row->setSide('Buy');
        $row->setState('Filled');
        $row->setPrice('95');
        $row->setQty('1');
        $row->setFilledQty('1');
        $row->setSimulated(true);
        $row->save();

        $st = $this->gw()->orderStatus('BTCUSDT', 'cid-old');
        $this->assertSame('FILLED', $st['status']);
        $this->assertSame(0, bccomp('1', (string) $st['executedQty'], 8));
    }

    public function testUnknownCidThrowsBinanceStyleError(): void
    {
        $this->expectException(BinanceApiError::class);
        $this->gw()->orderStatus('BTCUSDT', 'cid-nope');
    }
}
```

- [ ] **Step 2: Run it — must fail**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/PaperGatewayTest.php`
Expected: FAIL — `Class "App\Domains\Bot\Gateway\PaperGateway" not found`.

- [ ] **Step 3: Implement PaperGateway**

```php
<?php

namespace App\Domains\Bot\Gateway;

use App\BotOrderQuery;
use App\GridRun;

/**
 * Paper-trading gateway: real mainnet market data (public endpoints proxied
 * to an injected keyless BinanceGateway), locally simulated execution for the
 * signed half. Fill rule matches the backtest SimulatedGateway — touch/cross
 * fills full qty at the limit price — plus per-side fees charged in the QUOTE
 * asset (commissionAsset = quote), so wallet math stays single-currency and
 * matches the Daemon's own fee estimate.
 *
 * The paper wallet persists on grid_run.sim_bal_base/sim_bal_quote after
 * every fill (seeded quote = budget_quote, base = 0). The book hydrates from
 * open bot_order rows with simulated = 1, so restarts resume cleanly; a crash
 * between an in-memory fill and the Daemon's DB record re-fills
 * deterministically on the next tick.
 */
class PaperGateway implements GatewayInterface
{
    private const SCALE = 12;

    /** @var array<string, array{side:string, price:string, qty:string, orderId:int}> cid → open order */
    private array $book = [];
    /** @var array<string, array> cid → final Binance-shaped orderStatus payload */
    private array $closed = [];
    /** @var array<int, array<int, array>> orderId → myTrades entries */
    private array $trades = [];
    private int $nextOrderId;
    private string $balBase;
    private string $balQuote;
    private readonly string $baseAsset;
    private readonly string $quoteAsset;

    public function __construct(
        private readonly BinanceGateway $public,
        private readonly GridRun $run,
        private readonly string $feePct
    ) {
        $symbol = (string) $run->getSymbol();
        $this->quoteAsset = str_ends_with($symbol, 'USDT') ? 'USDT' : substr($symbol, -3);
        $this->baseAsset = substr($symbol, 0, strlen($symbol) - strlen($this->quoteAsset));

        $this->balQuote = $run->getSimBalQuote() !== null
            ? (string) $run->getSimBalQuote()
            : (string) $run->getBudgetQuote();
        $this->balBase = $run->getSimBalBase() !== null ? (string) $run->getSimBalBase() : '0';

        $this->nextOrderId = (int) floor(microtime(true) * 1000);
        foreach (BotOrderQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterBySimulated(true)
            ->filterByState(['BUY_OPEN', 'SELL_OPEN', 'PartFilled'], \Criteria::IN)
            ->find() as $row) {
            $this->book[(string) $row->getClientOrderId()] = [
                'side' => (string) $row->getSide(),
                'price' => (string) $row->getPrice(),
                'qty' => (string) $row->getQty(),
                'orderId' => (int) ($row->getExchangeOrderId() ?: $this->nextOrderId++),
            ];
        }
    }

    // ── public data: proxied to mainnet ─────────────────────────────────

    public function exchangeInfo(string $symbol): array
    {
        return $this->public->exchangeInfo($symbol);
    }

    /** Fetch the real price AND advance the paper book to it. */
    public function tickerPrice(string $symbol): string
    {
        $price = $this->public->tickerPrice($symbol);
        $this->step($price);
        return $price;
    }

    // ── simulated signed endpoints ──────────────────────────────────────

    public function accountBalances(): array
    {
        return [
            $this->baseAsset => ['free' => $this->balBase, 'locked' => '0'],
            $this->quoteAsset => ['free' => $this->balQuote, 'locked' => '0'],
        ];
    }

    public function openOrders(string $symbol): array
    {
        $out = [];
        foreach ($this->book as $cid => $o) {
            $out[] = $this->payload($cid, $o, 'NEW', '0');
        }
        return $out;
    }

    public function orderStatus(string $symbol, string $clientOrderId): array
    {
        if (isset($this->book[$clientOrderId])) {
            return $this->payload($clientOrderId, $this->book[$clientOrderId], 'NEW', '0');
        }
        if (isset($this->closed[$clientOrderId])) {
            return $this->closed[$clientOrderId];
        }
        // restart fallback: answer from the durable ledger
        $row = BotOrderQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterBySimulated(true)
            ->findOneByClientOrderId($clientOrderId);
        if ($row !== null) {
            $state = (string) $row->getState();
            $o = [
                'side' => (string) $row->getSide(),
                'price' => (string) $row->getPrice(),
                'qty' => (string) $row->getQty(),
                'orderId' => (int) ($row->getExchangeOrderId() ?: 0),
            ];
            if ($state === 'Filled') {
                return $this->payload($clientOrderId, $o, 'FILLED', (string) $row->getFilledQty());
            }
            if ($state === 'Canceled') {
                return $this->payload($clientOrderId, $o, 'CANCELED', (string) ($row->getFilledQty() ?: '0'));
            }
        }
        throw new BinanceApiError('Order does not exist.', 400, -2013);
    }

    public function placeLimitOrder(string $symbol, string $side, string $price, string $qty, string $clientOrderId): array
    {
        if (isset($this->book[$clientOrderId]) || isset($this->closed[$clientOrderId])) {
            return ['duplicate' => true, 'clientOrderId' => $clientOrderId];
        }
        $o = ['side' => $side, 'price' => $price, 'qty' => $qty, 'orderId' => $this->nextOrderId++];
        $this->book[$clientOrderId] = $o;
        return $this->payload($clientOrderId, $o, 'NEW', '0') + ['duplicate' => false];
    }

    public function cancelOrder(string $symbol, string $clientOrderId): array
    {
        if (!isset($this->book[$clientOrderId])) {
            throw new BinanceApiError('Unknown order sent.', 400, -2011);
        }
        $o = $this->book[$clientOrderId];
        unset($this->book[$clientOrderId]);
        $payload = $this->payload($clientOrderId, $o, 'CANCELED', '0');
        $this->closed[$clientOrderId] = $payload;
        return $payload;
    }

    public function myTrades(string $symbol, array $extra = []): array
    {
        if (isset($extra['orderId'])) {
            return $this->trades[(int) $extra['orderId']] ?? [];
        }
        return array_merge(...array_values($this->trades) ?: [[]]);
    }

    // ── fill engine ─────────────────────────────────────────────────────

    /** Advance the paper book to $price: same touch/cross rule and fill
     *  ordering as the backtest SimulatedGateway. */
    private function step(string $price): void
    {
        $hit = [];
        foreach ($this->book as $cid => $o) {
            $cmp = bccomp($price, $o['price'], self::SCALE);
            if (($o['side'] === 'Buy' && $cmp <= 0) || ($o['side'] === 'Sell' && $cmp >= 0)) {
                $hit[$cid] = $o;
            }
        }
        uasort($hit, function (array $a, array $b): int {
            if ($a['side'] !== $b['side']) {
                return $a['side'] === 'Buy' ? -1 : 1;
            }
            $cmp = bccomp($b['price'], $a['price'], self::SCALE);
            return $a['side'] === 'Buy' ? $cmp : -$cmp;
        });
        $filled = false;
        foreach ($hit as $cid => $o) {
            unset($this->book[$cid]);
            $this->fill($cid, $o);
            $filled = true;
        }
        if ($filled) {
            $this->persistWallet();
        }
    }

    private function fill(string $cid, array $o): void
    {
        $notional = bcmul($o['price'], $o['qty'], self::SCALE);
        $fee = bcmul($notional, $this->feePct, self::SCALE);
        if ($o['side'] === 'Buy') {
            $this->balQuote = bcsub($this->balQuote, bcadd($notional, $fee, self::SCALE), self::SCALE);
            $this->balBase = bcadd($this->balBase, $o['qty'], self::SCALE);
        } else {
            $this->balBase = bcsub($this->balBase, $o['qty'], self::SCALE);
            $this->balQuote = bcadd($this->balQuote, bcsub($notional, $fee, self::SCALE), self::SCALE);
        }
        $this->closed[$cid] = $this->payload($cid, $o, 'FILLED', $o['qty']);
        $this->trades[$o['orderId']][] = [
            'orderId' => $o['orderId'],
            'price' => $o['price'],
            'qty' => $o['qty'],
            'commission' => $fee,
            'commissionAsset' => $this->quoteAsset,
        ];
    }

    private function persistWallet(): void
    {
        $this->run->setSimBalBase($this->balBase);
        $this->run->setSimBalQuote($this->balQuote);
        $this->run->save();
    }

    /** Binance-shaped order payload. */
    private function payload(string $cid, array $o, string $status, string $executedQty): array
    {
        return [
            'symbol' => (string) $this->run->getSymbol(),
            'orderId' => $o['orderId'],
            'clientOrderId' => $cid,
            'price' => $o['price'],
            'origQty' => $o['qty'],
            'executedQty' => $executedQty,
            'status' => $status,
            'type' => 'LIMIT',
            'side' => strtoupper($o['side']),
        ];
    }
}
```

- [ ] **Step 4: Run the test — must pass**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/PaperGatewayTest.php`
Expected: PASS (all 9 tests).

- [ ] **Step 5: Full suite + commit**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot`
Expected: green.

```bash
cd /path/to/apigtbot && git add -A && git commit -m "feat(bot): PaperGateway — mainnet data, simulated fills, persisted paper wallet

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

### Task 4: Mode-aware OrderStore (TDD)

**Files:**
- Modify: `/path/to/apigtbot/.admin/src/App/Domains/Bot/OrderStore.php`
- Test: `/path/to/apigtbot/.admin/tests/Custom/Bot/OrderStoreModeTest.php`

**Interfaces:**
- Produces: `OrderStore::__construct(int $runId, string $runUid, ?string $ledgerEpoch = null, ?bool $simulated = null)` — `null` keeps today's unfiltered behavior (backward compatible). When non-null: `recordOpen`/`recordVeto`/`recordCycle` stamp `simulated`; `openOrderObjects`/`openRows`/`legacyRows`/`investedQuote`/`trackedInventory`/`realizedToday` filter by it. New: `cancelOtherModeOpens(): int`.

- [ ] **Step 1: Write the failing test**

Same DB boot/transaction pattern as `PaperGatewayTest` (copy `setUpBeforeClass`; `setUp` creates a GridRun exactly as in PaperGatewayTest Step 1 and opens the transaction; `tearDown` rolls back).

```php
<?php

namespace Tests\Custom\Bot;

use App\BotOrderQuery;
use App\Domains\Bot\IntendedOrder;
use App\Domains\Bot\OrderStore;
use App\GridRun;
use App\TradeCycleQuery;
use PHPUnit\Framework\TestCase;

class OrderStoreModeTest extends TestCase
{
    // ... setUpBeforeClass / setUp / tearDown identical to PaperGatewayTest ...

    private function store(bool $simulated): OrderStore
    {
        return new OrderStore((int) $this->run->getIdGridRun(), 'uidtest', null, $simulated);
    }

    public function testRowsStampedWithMode(): void
    {
        $sim = $this->store(true);
        $o = new IntendedOrder('Buy', 1, '95', '1');
        $row = $sim->recordOpen($o, 'cid-sim-1', '95', '1', null);
        $this->assertTrue((bool) $row->getSimulated());

        $sim->recordCycle(['level_idx' => 1, 'buy_price' => '95', 'sell_price' => '105', 'qty' => '1', 'realized_pnl' => '9.8', 'fees_total' => '0.2']);
        $c = TradeCycleQuery::create()->filterByIdGridRun((int) $this->run->getIdGridRun())->findOne();
        $this->assertTrue((bool) $c->getSimulated());
    }

    public function testQueriesAreModeDisjoint(): void
    {
        $sim = $this->store(true);
        $real = $this->store(false);
        $sim->recordOpen(new IntendedOrder('Buy', 1, '95', '1'), 'cid-sim-1', '95', '1', null);
        $real->recordOpen(new IntendedOrder('Buy', 2, '96', '1'), 'cid-real-1', '96', '1', null);

        $this->assertCount(1, $sim->openRows());
        $this->assertSame('cid-sim-1', $sim->openRows()[0]['client_order_id']);
        $this->assertCount(1, $real->openRows());
        $this->assertSame('cid-real-1', $real->openRows()[0]['client_order_id']);

        $sim->recordCycle(['level_idx' => 1, 'buy_price' => '95', 'sell_price' => '105', 'qty' => '1', 'realized_pnl' => '9.8', 'fees_total' => '0.2']);
        $this->assertSame(0, bccomp('9.8', $sim->realizedToday(), 8));
        $this->assertSame(0, bccomp('0', $real->realizedToday(), 8));
    }

    public function testNullModeIsUnfiltered(): void
    {
        $this->store(true)->recordOpen(new IntendedOrder('Buy', 1, '95', '1'), 'cid-sim-1', '95', '1', null);
        $this->store(false)->recordOpen(new IntendedOrder('Buy', 2, '96', '1'), 'cid-real-1', '96', '1', null);
        $legacy = new OrderStore((int) $this->run->getIdGridRun(), 'uidtest');
        $this->assertCount(2, $legacy->openRows());
    }

    public function testCancelOtherModeOpens(): void
    {
        $this->store(false)->recordOpen(new IntendedOrder('Buy', 2, '96', '1'), 'cid-real-1', '96', '1', null);
        $sim = $this->store(true);
        $sim->recordOpen(new IntendedOrder('Buy', 1, '95', '1'), 'cid-sim-1', '95', '1', null);

        $this->assertSame(1, $sim->cancelOtherModeOpens());
        $realRow = BotOrderQuery::create()->findOneByClientOrderId('cid-real-1');
        $this->assertSame('Canceled', (string) $realRow->getState());
        // own-mode order untouched
        $this->assertCount(1, $sim->openRows());
        // idempotent
        $this->assertSame(0, $sim->cancelOtherModeOpens());
    }
}
```

- [ ] **Step 2: Run it — must fail**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/OrderStoreModeTest.php`
Expected: FAIL (constructor takes 3 args; `cancelOtherModeOpens` undefined; rows not stamped).

- [ ] **Step 3: Implement**

In `OrderStore.php`:

1. Constructor: `public function __construct(private readonly int $runId, private readonly string $runUid, ?string $ledgerEpoch = null, private readonly ?bool $simulated = null)`.
2. Add next to `epochFilter()`:

```php
    /** null = legacy callers, no mode filter (all history). */
    private function modeFilter(\ModelCriteria $q): \ModelCriteria
    {
        if ($this->simulated !== null) {
            $q->filterBySimulated($this->simulated);
        }
        return $q;
    }
```

3. Stamp on create — in `recordOpen()` and `recordVeto()` add before `save()`: `$row->setSimulated($this->simulated ?? false);` and in `recordCycle()`: `$c->setSimulated($this->simulated ?? false);`.
4. Wrap queries with `$this->modeFilter(...)`:
   - `openOrderObjects()`: `$this->modeFilter(BotOrderQuery::create())->...`
   - `legacyRows()`: same wrap.
   - `investedQuote()`: wrap BOTH the `BotOrderQuery` and the `TradeCycleQuery` (compose with the existing `epochFilter`: `$this->modeFilter($this->epochFilter(...))`).
   - `trackedInventory()`: both queries, same composition.
   - `realizedToday()`: wrap its `TradeCycleQuery`.
   - `makeCid()` and `findByCid()` stay UNfiltered (cid uniqueness is global).
5. Add:

```php
    /** A mode flip must not leave the other mode's opens as zombies — and a
     *  flip to real must NEVER let paper opens be adopted onto the exchange.
     *  Called at daemon boot; returns how many rows were closed out. */
    public function cancelOtherModeOpens(): int
    {
        if ($this->simulated === null) {
            return 0;
        }
        $n = 0;
        foreach (BotOrderQuery::create()
            ->filterByIdGridRun($this->runId)
            ->filterBySimulated(!$this->simulated)
            ->filterByState(['BUY_OPEN', 'SELL_OPEN', 'PartFilled'], \Criteria::IN)
            ->find() as $row) {
            $row->setState('Canceled');
            $row->save();
            $n++;
        }
        return $n;
    }
```

- [ ] **Step 4: Run the test — must pass; then full suite**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/OrderStoreModeTest.php && vendor/bin/phpunit tests/Custom/Bot`
Expected: both green (existing callers pass no 4th arg → null → behavior unchanged).

- [ ] **Step 5: Commit**

```bash
cd /path/to/apigtbot && git add -A && git commit -m "feat(bot): mode-aware OrderStore — stamp + filter simulated, cancelOtherModeOpens

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

### Task 5: Daemon + bin/gtbot wiring (paper mode end to end)

**Files:**
- Modify: `/path/to/apigtbot/.admin/src/App/Domains/Bot/Daemon.php` (boot: OrderStore ctor + mode-switch cancel)
- Modify: `/path/to/apigtbot/.admin/bin/gtbot` (guards + gateway selection)
- Test: `/path/to/apigtbot/.admin/tests/Custom/Bot/DaemonPaperFlowTest.php`

**Interfaces:**
- Consumes: `PaperGateway` (Task 3), mode-aware `OrderStore` (Task 4), `GatewayInterface` type-hint (Task 2).
- Produces: a Daemon that, given a PaperGateway and a simulated run, places/fills paper orders whose `bot_order`/`trade_cycle` rows carry `simulated = 1` and whose wallet lands on `sim_bal_*` and `bal_*`.

- [ ] **Step 1: Write the failing test**

Same DB boot/transaction pattern; run setup as in `PaperGatewayTest` but with grid geometry that trades: `p_low=100, p_high=140, n_levels=4, Arithmetic, budget 1000` (levels 100/110/120/130/140-ish; exact ladder comes from GridMath — assert on outcomes, not exact level prices). Drive the daemon exactly as `DaemonLiveFlowTest` does (`boot()` then `tick()` per price step), but through a `PaperGateway` whose fake mainnet transport serves a scripted tape.

```php
<?php

namespace Tests\Custom\Bot;

use App\BotOrderQuery;
use App\Domains\Bot\Daemon;
use App\Domains\Bot\EventLog;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\Domains\Bot\Gateway\PaperGateway;
use App\GridRun;
use App\TradeCycleQuery;
use PHPUnit\Framework\TestCase;

class DaemonPaperFlowTest extends TestCase
{
    // ... setUpBeforeClass / setUp / tearDown as in PaperGatewayTest, with:
    //     p_low 100, p_high 140, n_levels 4, budget_quote 1000,
    //     max_position_quote 1000, max_order_quote 500, fee_pct 0.001,
    //     status Live, simulated true ...

    private string $tick = '120';

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

    public function testPaperCycleLandsMarkedSimulated(): void
    {
        $daemon = $this->makeDaemon();
        $daemon->boot();
        $daemon->tick();                     // places buy ladder below 120

        $this->assertGreaterThan(0, BotOrderQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterByState('BUY_OPEN')->filterBySimulated(true)->count());

        $this->tick = '105'; $daemon->tick(); // fills lower buys, posts sells
        $this->tick = '139'; $daemon->tick(); // fills sells → cycles close
        $daemon->tick();                      // settle

        $cycles = TradeCycleQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())->find();
        $this->assertGreaterThan(0, count($cycles));
        foreach ($cycles as $c) {
            $this->assertTrue((bool) $c->getSimulated());
        }
        $this->run->reload();
        $this->assertNotNull($this->run->getSimBalQuote()); // wallet persisted
    }

    public function testBootCancelsOtherModeOpens(): void
    {
        $real = new \App\BotOrder();
        $real->setIdGridRun((int) $this->run->getIdGridRun());
        $real->setClientOrderId('cid-real-zombie');
        $real->setLevelIdx(0);
        $real->setSide('Buy');
        $real->setState('BUY_OPEN');
        $real->setPrice('101');
        $real->setQty('1');
        $real->setSimulated(false);
        $real->save();

        $this->makeDaemon()->boot();

        $real->reload();
        $this->assertSame('Canceled', (string) $real->getState());
    }
}
```

Check `EventLog`'s actual constructor signature before writing (`grep -n "__construct" .admin/src/App/Domains/Bot/EventLog.php`) and construct it the way `DaemonLiveFlowTest` does — copy that test's EventLog usage verbatim.

- [ ] **Step 2: Run it — must fail**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/DaemonPaperFlowTest.php`
Expected: FAIL — rows not stamped (`simulated` = 0) because Daemon builds OrderStore without the mode, and no boot-time cancel happens.

- [ ] **Step 3: Wire the Daemon**

In `Daemon::boot()`, change the OrderStore construction to pass the mode, and cancel the other mode's opens immediately after (BEFORE `openRows()` is first read for geometry checks):

```php
        $this->store = new OrderStore(
            (int) $this->run->getIdGridRun(),
            $runUid,
            $this->run->getLedgerResetAt('Y-m-d H:i:s'),
            (bool) $this->run->getSimulated()
        );
        // A mode flip must not leave the other mode's opens as zombies (and
        // paper opens must never be adopted onto the real exchange).
        $closed = $this->store->cancelOtherModeOpens();
        if ($closed > 0) {
            $this->log->write('Warn', 'mode_switch', sprintf(
                '%d open order(s) from the other mode closed out (run is now %s)',
                $closed,
                $this->run->getSimulated() ? 'SIMULATED' : 'REAL'
            ));
        }
```

Also in `Daemon::geometryReset()` (the two inline queries around lines 399–407 computing untracked `$bought`): add `->filterBySimulated((bool) $this->run->getSimulated())` to both the `BotOrderQuery` and the `TradeCycleQuery`.

- [ ] **Step 4: Run the test — must pass; then full suite**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/DaemonPaperFlowTest.php && vendor/bin/phpunit tests/Custom/Bot`
Expected: green. (`DaemonLiveFlowTest` creates runs that now default `simulated = 1` and its rows get stamped 1 — consistent on both write and read sides, so it stays green. If it asserts on rows it inserts by hand with no `simulated`, set the run's `setSimulated(false)` in that test's setUp instead of weakening filters.)

- [ ] **Step 5: Wire `bin/gtbot`**

Replace the guard + gateway block (currently lines 47–85, from `$useTestnet = ...` through `$gateway = new ...BinanceGateway(...)`) with:

```php
$useTestnet = env('GTBOT_USE_TESTNET', '1') !== '0';
$dryRun = env('GTBOT_DRY_RUN', '1') !== '0';
$apiKey = (string) env('GTBOT_API_KEY', '');
$apiSecret = (string) env('GTBOT_API_SECRET', '');
$simulated = (bool) $run->getSimulated();

// Ed25519 (registered-public-key) keys take precedence over HMAC secrets.
$signer = null;
$privKeyPath = (string) env('GTBOT_API_PRIVATE_KEY', '');
if ($privKeyPath !== '' && !$simulated) {
    $path = str_starts_with($privKeyPath, '/') ? $privKeyPath : $admin . '/' . $privKeyPath;
    try {
        $signer = \App\Domains\Bot\Gateway\Ed25519Signer::fromPemFile($path);
    } catch (\InvalidArgumentException $e) {
        $fail('GTBOT_API_PRIVATE_KEY: ' . $e->getMessage());
    }
}

$status = (string) $run->getStatus();
if (!in_array($status, ['DryRun', 'Testnet', 'Live'], true)) {
    $fail("run $runId status is $status — set it to DryRun/Testnet/Live to start");
}
if (!$simulated) {
    // REAL mode cross-checks: a Live run must never talk to testnet and a
    // Testnet/DryRun run must never touch mainnet. Flipping the simulated
    // switch OFF is the deliberate act — it stays double-gated by env.
    if ($status === 'Live' && ($useTestnet || $dryRun)) {
        $fail("run $runId has status Live but GTBOT_USE_TESTNET/GTBOT_DRY_RUN are on — flip them explicitly to go live");
    }
    if ($status !== 'Live' && !$useTestnet) {
        $fail("run $runId has status $status but GTBOT_USE_TESTNET=0 points at mainnet — refusing");
    }
}
```

Keep the GET_LOCK block where it is, then replace the base-url/gateway lines with:

```php
if ($simulated) {
    // Paper mode: mainnet public data, locally simulated fills. No keys,
    // env flags irrelevant; status DryRun still means "place nothing".
    $gateway = new \App\Domains\Bot\Gateway\PaperGateway(
        new \App\Domains\Bot\Gateway\BinanceGateway('https://api.binance.com', '', ''),
        $run,
        (string) $run->getFeePct()
    );
    $dryRun = ($status === 'DryRun');
} else {
    $baseUrl = $useTestnet ? 'https://testnet.binance.vision' : 'https://api.binance.com';
    $gateway = new \App\Domains\Bot\Gateway\BinanceGateway($baseUrl, $apiKey, $apiSecret, null, null, $signer);
}
```

Update the file's doc-comment safety-model block to add: ` *  - grid_run.simulated (default 1): paper mode — mainnet public data, fills simulated locally, no keys needed; env flags apply only when the switch is OFF.`

- [ ] **Step 6: Syntax-check + suite**

Run: `php -l /path/to/apigtbot/.admin/bin/gtbot && cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot`
Expected: no syntax errors; suite green.

- [ ] **Step 7: Commit**

```bash
cd /path/to/apigtbot && git add -A && git commit -m "feat(bot): paper mode end to end — Daemon mode wiring + bin/gtbot gateway selection and guards

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

### Task 6: Mode-scoped stats (dashboard, MCP tools, scorer)

**Files:**
- Modify: `/path/to/apigtbot/.admin/src/App/Domains/Dashboard/DashboardData.php`
- Modify: `/path/to/apigtbot/.admin/src/App/Mcp/Tools/GtbotStatusTool.php`
- Modify: `/path/to/apigtbot/.admin/src/App/Mcp/Tools/GtbotPnlReportTool.php`
- Modify: `/path/to/apigtbot/.admin/src/App/Domains/Bot/DecisionScorer.php`
- Test: `/path/to/apigtbot/.admin/tests/Custom/Bot/ModeScopedStatsTest.php`

**Interfaces:**
- Consumes: `filterBySimulated` (Task 1), mode-aware `OrderStore` (Task 4).
- Produces: every stats surface reports only the run's current mode; `gtbot_pnl_report` accepts `mode: "sim"|"real"|"all"` (default: the run's current mode); `gtbot_status` output gains `'mode' => 'simulated'|'real'`.

- [ ] **Step 1: Write the failing test**

Same DB boot/transaction pattern. Seed one run with one sim cycle and one real cycle (insert `TradeCycle` rows directly, `setSimulated(true/false)`, both with `realized_pnl` 10 and 20 respectively), plus one sim `BUY_OPEN` and one real `BUY_OPEN` order row. Then:

```php
    public function testDashboardKpisFollowTheSwitch(): void
    {
        $this->run->setSimulated(true)->save();
        $kpis = (new \App\Domains\Dashboard\DashboardData($this->run))->kpis();
        $this->assertSame(0, bccomp('10', $kpis['realized_pnl'], 8));
        $this->assertSame(1, $kpis['cycles']);
        $this->assertSame(1, $kpis['open_buys']);

        $this->run->setSimulated(false)->save();
        $kpis = (new \App\Domains\Dashboard\DashboardData($this->run))->kpis();
        $this->assertSame(0, bccomp('20', $kpis['realized_pnl'], 8));
    }
```

(Adjust `DashboardData` construction to its real constructor — check `grep -n "__construct" DashboardData.php` first; if it resolves the run itself, set the seeded run up so it resolves.) Add equivalent assertions for `GtbotPnlReportTool` with `mode` `'sim'` / `'real'` / `'all'` (total_pnl 10 / 20 / 30) and default (= current mode), and for `GtbotStatusTool` (`realized_pnl_total` follows the switch; output contains `mode`). Invoke tools the way `McpToolsTest`/`GtbotStatusGeometryTest` invoke them — copy their session/args plumbing.

- [ ] **Step 2: Run it — must fail**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/ModeScopedStatsTest.php`
Expected: FAIL — aggregates include both modes today.

- [ ] **Step 3: Implement — DashboardData**

Add two private helpers and route EVERY `TradeCycleQuery::create()`/`BotOrderQuery::create()` in the class through them (kpis realized/fees/today loops, cycles count, open buys/sells counts, filled-buys loop, era-cycle loop, `latestCycles()`, the daily-buckets loop, and any other per-run stat query in the file — grep for `Query::create()` and convert each):

```php
    private function cycleQ(): TradeCycleQuery
    {
        return TradeCycleQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterBySimulated((bool) $this->run->getSimulated());
    }

    private function orderQ(): BotOrderQuery
    {
        return BotOrderQuery::create()
            ->filterByIdGridRun((int) $this->run->getIdGridRun())
            ->filterBySimulated((bool) $this->run->getSimulated());
    }
```

- [ ] **Step 4: Implement — GtbotStatusTool**

- `new OrderStore($runId, (string) $run->getRunUid())` → `new OrderStore($runId, (string) $run->getRunUid(), null, (bool) $run->getSimulated())`.
- Add `->filterBySimulated((bool) $run->getSimulated())` to the open-buys count, open-sells count, realized-total loop, and cycles count queries.
- In the returned array add `'mode' => $run->getSimulated() ? 'simulated' : 'real',` next to the run block.

- [ ] **Step 5: Implement — GtbotPnlReportTool**

- In `inputSchema()` properties add: `'mode' => ['type' => 'string', 'enum' => ['sim', 'real', 'all'], 'description' => "Which stat set: sim, real, or all; default = the run's current mode"],`.
- In `run()`:

```php
        $mode = (string) ($args['mode'] ?? '');
        $modeFilter = function ($q) use ($mode, $run) {
            return match ($mode) {
                'sim' => $q->filterBySimulated(true),
                'real' => $q->filterBySimulated(false),
                'all' => $q,
                default => $q->filterBySimulated((bool) $run->getSimulated()),
            };
        };
```

Apply `$modeFilter(...)` to the cycles query; pass the mode into the `OrderStore` it builds for unrealized (`new OrderStore(..., null, $mode === 'all' ? null : ($mode === 'sim' ? true : ($mode === 'real' ? false : (bool) $run->getSimulated())))`); include `'mode' => $mode !== '' ? $mode : ($run->getSimulated() ? 'sim' : 'real')` in the output payload.

- [ ] **Step 6: Implement — DecisionScorer**

Add `->filterBySimulated((bool) $run->getSimulated())` to the `TradeCycleQuery` in `record()` and to the scoring loop's query (its run row is available in that scope — check the surrounding method for the variable name).

- [ ] **Step 7: Run the test — must pass; then full suite**

Run: `cd /path/to/apigtbot/.admin && vendor/bin/phpunit tests/Custom/Bot/ModeScopedStatsTest.php && vendor/bin/phpunit tests/Custom/Bot`
Expected: green. `DashboardDataTest` seeds rows for runs that now default `simulated = 1`; since seeded rows default `simulated = 0`, that test may break — fix by stamping its seeded rows `setSimulated(true)` OR setting its run `setSimulated(false)`; pick whichever matches what the test already asserts (do not weaken assertions).

- [ ] **Step 8: Commit**

```bash
cd /path/to/apigtbot && git add -A && git commit -m "feat(bot): mode-scoped stats — dashboard, gtbot_status/pnl_report (+mode arg), DecisionScorer

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

### Task 7: Deploy + migrate the four prod runs (ORCHESTRATOR ONLY — not a subagent task)

**Files:** none (operational).

- [ ] **Step 1:** Confirm local suite green + `./gc doctor apigtbot` clean.
- [ ] **Step 2:** Deploy: from `/path/to/gc` run `./gc deploy apigtbot` bare (no pipes), answer `-y`-equivalent prompt to apply the additive schema. Show dry-run/advisory output to the user if anything looks off.
- [ ] **Step 3:** For each prod run (BTC 1, BNB 4, SOL 5, ETH 6) via the apigtbot MCP tools: `gtbot_stop`, then `crm_update` GridRun `{simulated: 1, status: 'Live'}` (leave `sim_bal_*` null — PaperGateway seeds quote=budget, base=0 on first boot), then `gtbot_start` (or let the watchdog respawn).
- [ ] **Step 4:** Verify each run via `gtbot_status`: `mode: simulated`, heartbeat fresh, boot event shows mainnet paper mode, `mode_switch` event closed out the old testnet opens.
- [ ] **Step 5:** Telegram sanity: confirm the bot_start lifecycle message arrived.

---

## Execution notes

- Tasks 1→2→3→4→5→6 are sequential (each consumes the previous task's interfaces). Task 7 is manual/orchestrator-gated.
- If `filterBySimulated` doesn't exist after the Task 1 build, the build didn't regenerate models — delete `.admin/tmp/.codegen-fingerprint` and re-run `./gc b`.
