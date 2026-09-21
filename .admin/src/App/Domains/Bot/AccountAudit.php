<?php

namespace App\Domains\Bot;

use App\BotOrderQuery;
use App\Domains\Bot\Gateway\GatewayInterface;
use App\GridRunQuery;

/**
 * Does the real ACCOUNT back what the ledgers say the fleet holds and works?
 *
 * Every daemon reconciles its OWN orders at boot and nothing ever looks at the
 * account as a whole: inventory two runs both count, an exit a ledger believes
 * is working and the book does not have, coins that left the account by hand.
 * On paper none of that can happen — the wallet IS the ledger. On a real
 * account it is the difference between "guarded" and "thinks it is guarded",
 * so the canary is watched by this, not by eye.
 *
 * Read-only. Real (simulated = 0), non-Done runs only.
 */
class AccountAudit
{
    /** base-asset shortfall under this is lot-step dust, not missing inventory */
    private const DUST = '0.00000100';

    /**
     * Every finding carries a STABLE `key` — kind + the thing it is about,
     * never the numbers (2026-09-21 review). bin/gtbot-audit --alert only
     * tells Telegram about a finding that survives two consecutive audits,
     * and it identified them by hashing the whole detail line. The detail of
     * an inventory_missing embeds live quantities at 8 decimals, so on an
     * account that is trading they differ every single audit, the state
     * machine never advanced from 'seen' to 'told', and the most important
     * finding the audit can produce was the one that could never be sent.
     *
     * @return array{ok: bool,
     *   assets: array<string, array{account:string, tracked:string, float:string}>,
     *   findings: array<int, array{kind:string, key:string, detail:string}>}
     */
    public static function run(GatewayInterface $gateway): array
    {
        $runs = GridRunQuery::create()
            ->filterBySimulated(false)
            ->filterByStatus(['Done', 'Draft'], \Criteria::NOT_IN) // the population FeeFloat::trackedBase counts
            ->select(['IdGridRun', 'Symbol'])
            ->find()->getArrayCopy();
        $findings = [];
        $assets = [];
        if ($runs === []) {
            return ['ok' => true, 'assets' => [], 'findings' => []];
        }
        $balances = $gateway->accountBalances();

        $symbols = array_values(array_unique(array_column($runs, 'Symbol')));
        foreach ($symbols as $symbol) {
            [$base, $quote] = SimWallet::assetsFor((string) $symbol);

            // ── inventory: the account must hold what the ledgers track —
            // once per BASE asset (trackedBase spans every pair that buys it)
            if (!isset($assets[$base])) {
                $account = bcadd((string) ($balances[$base]['free'] ?? '0'), (string) ($balances[$base]['locked'] ?? '0'), 8);
                $tracked = bcadd(FeeFloat::trackedBase($base, $quote), '0', 8);
                $float = bcsub($account, $tracked, 8);
                $assets[$base] = ['account' => $account, 'tracked' => $tracked, 'float' => $float];
                if (bccomp($float, '-' . self::DUST, 8) < 0) {
                    $findings[] = ['kind' => 'inventory_missing', 'key' => 'inventory_missing|' . $base, 'detail' => sprintf(
                        '%s: the ledgers track %s, the account holds %s — %s is unaccounted for; some exit cannot be placed or filled',
                        $base, $tracked, $account, bcsub($tracked, $account, 8)
                    )];
                }
            }

            // ── orders: ledger ↔ book, both directions ──
            $runIds = array_column(array_filter($runs, static fn (array $r): bool => $r['Symbol'] === $symbol), 'IdGridRun');
            $ledger = BotOrderQuery::create()
                ->filterByIdGridRun($runIds, \Criteria::IN)
                ->filterBySimulated(false)
                ->filterByState(['BUY_OPEN', 'SELL_OPEN', 'PartFilled'], \Criteria::IN)
                ->select(['ClientOrderId', 'IdGridRun', 'Side', 'Qty', 'Price'])
                ->find()->getArrayCopy();
            $book = array_column($gateway->openOrders((string) $symbol), null, 'clientOrderId');
            foreach ($ledger as $o) {
                if (!isset($book[$o['ClientOrderId']])) {
                    // a fill the daemon books on its next tick looks like this
                    // for a few seconds; a finding that SURVIVES two audits is real
                    $findings[] = ['kind' => 'order_not_on_book', 'key' => 'order_not_on_book|' . $o['ClientOrderId'], 'detail' => sprintf(
                        'run %d: %s %s @ %s (%s) is open on the ledger and absent from the book',
                        $o['IdGridRun'], $o['Side'], $o['Qty'], $o['Price'], $o['ClientOrderId']
                    )];
                }
                unset($book[$o['ClientOrderId']]);
            }
            foreach ($book as $cid => $o) {
                $findings[] = ['kind' => 'foreign_order', 'key' => 'foreign_order|' . $cid, 'detail' => sprintf(
                    '%s: %s %s @ %s (%s) is on the book and on nobody\'s ledger',
                    $symbol, $o['side'] ?? '?', $o['origQty'] ?? '?', $o['price'] ?? '?', $cid
                )];
            }
        }
        return ['ok' => $findings === [], 'assets' => $assets, 'findings' => $findings];
    }
}
