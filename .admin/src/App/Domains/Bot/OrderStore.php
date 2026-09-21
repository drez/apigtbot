<?php

namespace App\Domains\Bot;

use App\BotOrder;
use App\BotOrderQuery;
use App\TradeCycle;
use App\TradeCycleQuery;

/**
 * Propel-backed order/cycle ledger for one run — the durable half of the
 * state store (GridStateHydrator rebuilds the in-memory machine from it).
 * All money values are bcmath strings end to end.
 */
class OrderStore
{
    private const SCALE = 12;

    /** rows at/before this datetime are a previous ledger era (orphaned by a
     *  geometry reset) and excluded from position accounting; null = all rows */
    private ?string $ledgerEpoch;

    public function __construct(private readonly int $runId, private readonly string $runUid, ?string $ledgerEpoch = null, private readonly ?bool $simulated = null)
    {
        $this->ledgerEpoch = $ledgerEpoch !== null && $ledgerEpoch !== '' ? $ledgerEpoch : null;
    }

    public function setLedgerEpoch(?string $epoch): void
    {
        $this->ledgerEpoch = $epoch !== null && $epoch !== '' ? $epoch : null;
    }

    private function epochFilter(\ModelCriteria $q): \ModelCriteria
    {
        if ($this->ledgerEpoch !== null) {
            $q->filterByDateCreation(['min' => $this->ledgerEpoch]);
        }
        return $q;
    }

    /** null = legacy callers, no mode filter (all history). */
    private function modeFilter(\ModelCriteria $q): \ModelCriteria
    {
        if ($this->simulated !== null) {
            $q->filterBySimulated($this->simulated);
        }
        return $q;
    }

    public function cidPrefix(): string
    {
        return 'gt-' . $this->runUid . '-';
    }

    /** Deterministic, unique client order id for an intent. */
    public function makeCid(IntendedOrder $o): string
    {
        $seq = BotOrderQuery::create()->filterByIdGridRun($this->runId)->count() + 1;
        return sprintf('%sL%02d-%s-%d', $this->cidPrefix(), $o->levelIdx, $o->side === 'Buy' ? 'B' : 'S', $seq);
    }

    public function recordOpen(IntendedOrder $o, string $cid, string $price, string $qty, ?string $exchangeOrderId): BotOrder
    {
        $row = new BotOrder();
        $row->setIdGridRun($this->runId);
        $row->setClientOrderId($cid);
        $row->setExchangeOrderId($exchangeOrderId);
        $row->setLevelIdx($o->levelIdx);
        $row->setSide($o->side);
        $row->setState($o->side === 'Buy' ? 'BUY_OPEN' : 'SELL_OPEN');
        $row->setPrice($price);
        $row->setQty($qty);
        $row->setSimulated($this->simulated ?? false);
        $row->save();
        return $row;
    }

    public function recordVeto(IntendedOrder $o, string $reason): void
    {
        $row = new BotOrder();
        $row->setIdGridRun($this->runId);
        $row->setClientOrderId($this->makeCid($o) . '-veto');
        $row->setLevelIdx($o->levelIdx);
        $row->setSide($o->side);
        $row->setState('Vetoed');
        $row->setPrice($o->price);
        $row->setQty($o->qty);
        $row->setFeeAsset(null);
        $row->setSimulated($this->simulated ?? false);
        $row->save();
    }

    /** @return array<int, array{client_order_id:string, level_idx:int, side:string, state:string, qty:string}> */
    public function openRows(): array
    {
        $out = [];
        foreach ($this->openOrderObjects() as $row) {
            $out[] = [
                'client_order_id' => $row->getClientOrderId(),
                'level_idx' => (int) $row->getLevelIdx(),
                'side' => (string) $row->getSide(),
                'state' => (string) $row->getState(),
                'qty' => (string) $row->getQty(),
                'is_legacy' => (bool) $row->getIsLegacy(),
            ];
        }
        return $out;
    }

    /** Open orders, OLDEST FIRST. The order is explicit because callers rely
     *  on it: the flatten chase resumes after the id it stopped at
     *  (Daemon::chaseOrder), so "the next lot" has to mean something stable.
     *  Without an ORDER BY the rows came back in whatever order the storage
     *  engine happened to walk the index in — which, by luck, was insertion
     *  order, and the whole round-robin rested on that luck.
     *  @return BotOrder[] */
    public function openOrderObjects(): array
    {
        return $this->modeFilter(BotOrderQuery::create())
            ->filterByIdGridRun($this->runId)
            ->filterByState(['BUY_OPEN', 'SELL_OPEN', 'PartFilled'], \Criteria::IN)
            ->orderByIdBotOrder()
            ->find()
            ->getArrayCopy();
    }

    public function findByCid(string $cid): ?BotOrder
    {
        return BotOrderQuery::create()
            ->filterByIdGridRun($this->runId)
            ->findOneByClientOrderId($cid);
    }

    /** $filledQty is what the run HOLDS from the fill (net of a base-asset
     *  commission — Daemon::bookFill); $feeAsset what the exchange charged in
     *  (null = estimated, never observed). */
    public function markFilled(BotOrder $row, string $filledQty, string $feePaid, ?string $feeAsset = null): void
    {
        $row->setState('Filled');
        $row->setFilledQty($filledQty);
        $row->setFeePaid($feePaid);
        if ($feeAsset !== null && $feeAsset !== '') {
            $row->setFeeAsset($feeAsset);
        }
        $row->save();
    }

    /**
     * Track a resting order's progress.
     *
     * THE STUCK CLOCK. `date_modification` on a PartFilled row is when the
     * order FIRST went partial, and nothing may move it while it stays
     * partial: it is the only memory a restart has of how long a partial has
     * been stuck (Daemon::detectFills resumes Daemon::$partialSince from it,
     * TrendEngine::handleStuckPartials reads it live). Since 6c4f825 the
     * ledger follows EVERY observed executedQty change — and the
     * add_tablestamp behavior re-stamps date_modification on every modified
     * save(), so a trickling order was handed a fresh timer on every refresh
     * and could never be stuck at all (Repro2, BUG B).
     *
     * So the TRANSITION into PartFilled is a normal save (it stamps the
     * clock) and every later REFRESH writes filled_qty alone, through an
     * UPDATE that leaves the timestamp columns exactly as they are. The
     * in-memory object is kept in step, so a later markFilled()/markCanceled()
     * still writes the value it holds.
     */
    public function markPartFilled(BotOrder $row, string $filledQty): void
    {
        if ((string) $row->getState() !== 'PartFilled' || $row->isNew()) {
            $row->setState('PartFilled');
            $row->setFilledQty($filledQty);
            $row->save();
            return;
        }
        $con = \Propel::getConnection(\App\BotOrderPeer::DATABASE_NAME, \Propel::CONNECTION_WRITE);
        $stmt = $con->prepare('UPDATE bot_order SET filled_qty = :qty WHERE id_bot_order = :id');
        $stmt->execute([':qty' => $filledQty, ':id' => (int) $row->getIdBotOrder()]);
        $row->setFilledQty($filledQty);
    }

    public function markCanceled(BotOrder $row): void
    {
        $row->setState('Canceled');
        $row->save();
    }

    /** Tag a working sell as a LEGACY EXIT — kept on the exchange but no
     *  longer owned by any ladder level (refit-with-inventory).
     *
     *  $buyPrice/$buyFee pin the buy this exit's inventory actually came from,
     *  for callers that know it (see Daemon::placeLegacyExit); left null the
     *  cycle is attributed by Daemon::resolveLegacy's level-index heuristic,
     *  which is all a carried grid exit needs. */
    public function markLegacy(BotOrder $row, ?string $buyPrice = null, ?string $buyFee = null): void
    {
        $row->setIsLegacy(true);
        if ($buyPrice !== null) {
            $row->setLegacyBuyPrice($buyPrice);
            $row->setLegacyBuyFee($buyFee ?? '0');
        }
        $row->save();
    }

    /**
     * Pin the buy this exit's inventory came from WITHOUT detaching the exit
     * from its ladder level (markLegacy's attribution half, on its own).
     *
     * A lot that is exited in more than one order — a ladder exit canceled
     * after part of it traded — must book the buy-leg fee it paid EXACTLY
     * ONCE across the pieces: the cycle for the part that sold books its
     * share, and the re-placed remainder carries the rest here. Without it
     * every piece re-read the matched buy's whole fee (double-booked) or none
     * of it (markLegacy's `$buyFee ?? '0'` — Repro2, BUG 5).
     */
    public function pinBuyAttribution(BotOrder $row, string $buyPrice, string $buyFee): void
    {
        $row->setLegacyBuyPrice($buyPrice);
        $row->setLegacyBuyFee($buyFee);
        $row->save();
    }

    /** Carry every working (non-legacy) sell out of the current ladder as a
     *  LEGACY EXIT: it stays on the exchange guarding real inventory, detached
     *  from the level machine. Used by both a refit and an algo cutover.
     *  @return int how many exits were carried */
    public function carrySellsAsLegacy(): int
    {
        $n = 0;
        foreach ($this->openOrderObjects() as $row) {
            if ((string) $row->getSide() === 'Sell' && !$row->getIsLegacy()) {
                $this->markLegacy($row);
                $n++;
            }
        }
        return $n;
    }

    /** @return BotOrder[] open legacy exits (still working on the book) */
    public function legacyRows(): array
    {
        return $this->modeFilter(BotOrderQuery::create())
            ->filterByIdGridRun($this->runId)
            ->filterByIsLegacy(true)
            ->filterByState(['SELL_OPEN', 'PartFilled'], \Criteria::IN)
            ->find()
            ->getArrayCopy();
    }

    /** Base qty still guarded by open legacy exits — the unrealized stop and
     *  the orphan check must keep seeing this inventory. */
    public function legacyRemainingQty(): string
    {
        $qty = '0';
        foreach ($this->legacyRows() as $row) {
            $qty = bcadd($qty, bcsub((string) $row->getQty(), (string) ($row->getFilledQty() ?: '0'), self::SCALE), self::SCALE);
        }
        return $qty;
    }

    /** Quote a new ladder must NOT re-spend: legacy inventory valued at its
     *  exit price (slightly above cost — deliberately conservative). */
    public function legacyReserveQuote(): string
    {
        $sum = '0';
        foreach ($this->legacyRows() as $row) {
            $rem = bcsub((string) $row->getQty(), (string) ($row->getFilledQty() ?: '0'), self::SCALE);
            $sum = bcadd($sum, bcmul($rem, (string) $row->getPrice(), self::SCALE), self::SCALE);
        }
        return $sum;
    }

    /**
     * Quote committed to WORKING buy orders: what a resting or part-filled
     * entry will still spend when it fills.
     *
     * investedQuote() counts Filled rows only, so it cannot see a batch of
     * entry tranches placed in one tick and still on the book — and a slice
     * derived from the filled part alone would be undercut the moment the
     * rest fills. Each row contributes its UNFILLED remainder at its own
     * price, the same conservative reservation legacyReserveQuote() makes
     * for the exit side (which covers Sell rows only, so the two never
     * double-count).
     */
    public function openBuyReserveQuote(): string
    {
        $sum = '0';
        foreach ($this->openOrderObjects() as $row) {
            if ((string) $row->getSide() !== 'Buy') {
                continue;
            }
            $rem = bcsub((string) $row->getQty(), (string) ($row->getFilledQty() ?: '0'), self::SCALE);
            if (bccomp($rem, '0', self::SCALE) <= 0) {
                continue;
            }
            $sum = bcadd($sum, bcmul($rem, (string) $row->getPrice(), self::SCALE), self::SCALE);
        }
        return $sum;
    }

    /** @param array<string,string> $cycle LevelStateMachine cycle payload */
    public function recordCycle(array $cycle): void
    {
        $c = new TradeCycle();
        $c->setIdGridRun($this->runId);
        $c->setLevelIdx((int) $cycle['level_idx']);
        $c->setBuyPrice($cycle['buy_price']);
        $c->setSellPrice($cycle['sell_price']);
        $c->setQty($cycle['qty']);
        $c->setRealizedPnl($cycle['realized_pnl']);
        $c->setFeesTotal($cycle['fees_total']);
        $c->setSimulated($this->simulated ?? false);
        $c->save();
    }

    public function openCount(): int
    {
        return count($this->openOrderObjects());
    }

    /** Quote currently tied up in held inventory (filled buys minus completed
     *  cycles) — current ledger era only: cost of inventory orphaned by a
     *  geometry reset must not poison the unrealized-loss stop. */
    public function investedQuote(): string
    {
        $bought = '0';
        $rows = $this->modeFilter($this->epochFilter(BotOrderQuery::create()))
            ->filterByIdGridRun($this->runId)
            ->filterBySide('Buy')
            ->filterByState('Filled')
            ->find();
        foreach ($rows as $row) {
            $bought = bcadd($bought, bcmul((string) $row->getPrice(), (string) $row->getFilledQty(), self::SCALE), self::SCALE);
        }
        foreach ($this->modeFilter($this->epochFilter(TradeCycleQuery::create()))->filterByIdGridRun($this->runId)->find() as $c) {
            $bought = bcsub($bought, bcmul((string) $c->getBuyPrice(), (string) $c->getQty(), self::SCALE), self::SCALE);
        }
        return $bought;
    }

    /** Base inventory the CURRENT ledger era accounts for (filled buys minus
     *  cycle qty since the epoch). The daemon compares this to the machine's
     *  held inventory to detect orphans after a kill-clear. */
    public function trackedInventory(): string
    {
        $qty = '0';
        foreach ($this->modeFilter($this->epochFilter(BotOrderQuery::create()))
            ->filterByIdGridRun($this->runId)
            ->filterBySide('Buy')
            ->filterByState('Filled')
            ->find() as $row) {
            $qty = bcadd($qty, (string) $row->getFilledQty(), self::SCALE);
        }
        foreach ($this->modeFilter($this->epochFilter(TradeCycleQuery::create()))->filterByIdGridRun($this->runId)->find() as $c) {
            $qty = bcsub($qty, (string) $c->getQty(), self::SCALE);
        }
        return $qty;
    }

    /** Realized PnL since local midnight (daily-loss-limit input). */
    public function realizedToday(): string
    {
        $sum = '0';
        $rows = $this->modeFilter(TradeCycleQuery::create())
            ->filterByIdGridRun($this->runId)
            ->filterByDateCreation(['min' => date('Y-m-d 00:00:00')])
            ->find();
        foreach ($rows as $c) {
            $sum = bcadd($sum, (string) $c->getRealizedPnl(), self::SCALE);
        }
        return $sum;
    }

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
}
