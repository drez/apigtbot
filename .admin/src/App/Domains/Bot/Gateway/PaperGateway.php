<?php

namespace App\Domains\Bot\Gateway;

use App\BotOrderQuery;
use App\Domains\Bot\SimWallet;
use App\GridRun;

/**
 * Paper-trading gateway: real mainnet market data (public endpoints proxied
 * to an injected keyless BinanceGateway), locally simulated execution for the
 * signed half. Fill rule matches the backtest SimulatedGateway — touch/cross
 * fills full qty at the limit price — plus per-side fees charged in the QUOTE
 * asset (commissionAsset = quote), so wallet math stays single-currency and
 * matches the Daemon's own fee estimate.
 *
 * The paper wallet is the ONE shared `sim_wallet` table (see SimWallet) — it
 * is NOT per-run; the legacy per-run wallet columns on grid_run are unused by
 * this class entirely (mirrors the single real exchange account: every
 * simulated run draws from and credits the same pool). The constructor calls
 * SimWallet::deriveAndStore() to re-derive the wallet from the global
 * simulated=1, state=Filled ledger — self-healing against the crash-window
 * double-count bug (a crash between fill() moving the wallet and the Daemon
 * marking the bot_order row Filled used to re-hydrate the still-open row at
 * next boot and re-fill it; replaying only durably-Filled rows is idempotent).
 * Every fill moves the shared pool via SimWallet::applyDelta(), which
 * serializes concurrent daemons behind a MySQL advisory lock + row FOR
 * UPDATE. If a fill drives the pool's quote balance negative, the optional
 * $onAlert sink is invoked with kind 'sim_wallet_negative' so an operator can
 * see that concurrent runs have overcommitted the shared budget. The book
 * itself still hydrates from OPEN bot_order rows with simulated = 1 (per
 * run), so restarts resume cleanly.
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
    private readonly string $baseAsset;
    private readonly string $quoteAsset;

    public function __construct(
        private readonly BinanceGateway $public,
        private readonly GridRun $run,
        private readonly string $feePct,
        private readonly mixed $onAlert = null
    ) {
        $symbol = (string) $run->getSymbol();
        [$this->baseAsset, $this->quoteAsset] = SimWallet::assetsFor($symbol);

        // Re-derive the shared wallet from the global ledger — see class
        // docblock. Self-healing: idempotent over durably Filled rows.
        SimWallet::deriveAndStore();

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
        $bal = SimWallet::balances();
        return [
            $this->baseAsset => ['free' => $bal[$this->baseAsset] ?? '0', 'locked' => '0'],
            $this->quoteAsset => ['free' => $bal[$this->quoteAsset] ?? '0', 'locked' => '0'],
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
        foreach ($hit as $cid => $o) {
            unset($this->book[$cid]);
            $this->fill($cid, $o);
        }
    }

    private function fill(string $cid, array $o): void
    {
        $notional = bcmul($o['price'], $o['qty'], self::SCALE);
        $fee = bcmul($notional, $this->feePct, self::SCALE);
        if ($o['side'] === 'Buy') {
            $deltas = [
                $this->quoteAsset => bcmul('-1', bcadd($notional, $fee, self::SCALE), self::SCALE),
                $this->baseAsset => $o['qty'],
            ];
        } else {
            $deltas = [
                $this->baseAsset => bcmul('-1', $o['qty'], self::SCALE),
                $this->quoteAsset => bcsub($notional, $fee, self::SCALE),
            ];
        }
        $bal = SimWallet::applyDelta($deltas);
        $this->closed[$cid] = $this->payload($cid, $o, 'FILLED', $o['qty']);
        $this->trades[$o['orderId']][] = [
            'orderId' => $o['orderId'],
            'price' => $o['price'],
            'qty' => $o['qty'],
            'commission' => $fee,
            'commissionAsset' => $this->quoteAsset,
        ];

        $newQuote = $bal[$this->quoteAsset] ?? null;
        if ($newQuote !== null && bccomp((string) $newQuote, '0', self::SCALE) < 0 && $this->onAlert !== null) {
            ($this->onAlert)(
                'sim_wallet_negative',
                'shared paper wallet went negative: USDT ' . $newQuote . ' — run slices overcommitted?'
            );
        }
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
