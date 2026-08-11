<?php

namespace App\Domains\Bot;

use App\Domains\Bot\Gateway\SimulatedGateway;

/**
 * Drives the pure strategy + risk stack over a price series through the
 * SimulatedGateway. Same wiring the live daemon uses (intent → risk review →
 * place → fill → state transition), so backtests exercise the real logic —
 * but with perfect fills: results are directional, not promises.
 */
class Backtester
{
    private const SCALE = 12;

    /**
     * @param array    $config grid_run-shaped config (see RiskManagerTest)
     * @param string[] $series price tape; index 0 is the starting price
     */
    public static function run(array $config, array $series): array
    {
        $errors = RiskManager::validateRunConfig($config);
        if ($errors) {
            throw new \InvalidArgumentException(implode('; ', $errors));
        }
        if ($series === []) {
            throw new \InvalidArgumentException('empty price series');
        }

        $levels = GridMath::levels($config['p_low'], $config['p_high'], (int) $config['n_levels'], $config['spacing']);
        $buyLevels = array_slice($levels, 0, -1);
        $qtys = GridMath::allocate($config['budget_quote'], $buyLevels, $config['allocation']);
        $machine = new LevelStateMachine($levels, $qtys);
        $sim = new SimulatedGateway();
        $floor = bcmul($config['p_low'], bcsub('1', $config['breakout_buffer_pct'], self::SCALE), self::SCALE);

        // staged unrealized stop (mirrors the live daemon): soft de-risk at
        // 60% of the cap, hard stop at 100%. Policy 'hold' freezes entries
        // and keeps exits (the live behavior); 'flatten' liquidates at the
        // tape price (the alternative under evaluation).
        $cap = (string) ($config['max_unrealized_loss_quote'] ?? '');
        $capOn = $cap !== '' && bccomp($cap, '0', self::SCALE) > 0;
        $stopPolicy = ($config['stop_policy'] ?? 'hold') === 'flatten' ? 'flatten' : 'hold';

        $st = [
            'price' => $series[0],
            'invested' => '0',
            'realized' => '0',
            'fees' => '0',
            'cycles' => 0,
            'buy_fills' => 0,
            'sell_fills' => 0,
            'halted' => false,
            'stopped' => false,
            'derisk' => false,
            'vetoes' => 0,
            'ledger' => [],
            'seq' => 0,
            'cidLevel' => [],       // cid → level idx
            'buyFeeByLevel' => [],  // level idx → fee paid on the held buy
        ];

        $place = function (IntendedOrder $o) use (&$st, $sim, $config, $machine): void {
            if ($o->side === 'Buy' && ($st['stopped'] || $st['derisk'])) {
                // no new exposure while de-risking/stopped; EMPTY so the level
                // can re-arm if the drawdown recovers (de-risk stage only)
                $machine->hydrateLevel($o->levelIdx, 'EMPTY');
                return;
            }
            $snapshot = [
                'market_price' => $st['price'],
                'invested_quote' => $st['invested'],
                'realized_pnl_today' => $st['realized'],
                'open_orders' => count($sim->openOrders()),
            ];
            $d = RiskManager::review($o, $snapshot, $config);
            if ($d->halt) {
                $st['halted'] = true;
            }
            if (!$d->approved) {
                $st['vetoes']++;
                return;
            }
            $cid = sprintf('sim-L%02d-%s-%d', $o->levelIdx, $o->side === 'Buy' ? 'B' : 'S', $st['seq']++);
            $st['cidLevel'][$cid] = $o->levelIdx;
            $sim->placeOrder($cid, $o->side, $o->price, $o->qty);
        };

        $cancelBuys = function () use (&$st, $sim, $machine): void {
            foreach ($sim->openOrders() as $o) {
                if ($o['side'] === 'Buy') {
                    $sim->cancelOrder($o['client_order_id']);
                    $machine->hydrateLevel((int) $st['cidLevel'][$o['client_order_id']], 'EMPTY');
                }
            }
        };

        foreach ($machine->initialIntents($st['price']) as $intent) {
            $place($intent);
        }

        foreach (array_slice($series, 1) as $price) {
            $st['price'] = $price;
            if (bccomp($price, $floor, self::SCALE) < 0) {
                $st['halted'] = true;
            }
            foreach ($sim->step($price) as $fill) {
                $level = $st['cidLevel'][$fill['client_order_id']];
                $notional = bcmul($fill['price'], $fill['qty'], self::SCALE);
                $fee = bcmul($notional, $config['fee_pct'], self::SCALE);
                $st['fees'] = bcadd($st['fees'], $fee, self::SCALE);
                if ($fill['side'] === 'Buy') {
                    $st['buy_fills']++;
                    $st['invested'] = bcadd($st['invested'], $notional, self::SCALE);
                    $st['buyFeeByLevel'][$level] = $fee;
                    $place($machine->onBuyFill($level));
                } else {
                    $st['sell_fills']++;
                    $cycleFees = bcadd($st['buyFeeByLevel'][$level] ?? '0', $fee, self::SCALE);
                    $res = $machine->onSellFill($level, $cycleFees);
                    $st['cycles']++;
                    $st['realized'] = bcadd($st['realized'], $res['cycle']['realized_pnl'], self::SCALE);
                    $st['invested'] = bcsub(
                        $st['invested'],
                        bcmul($res['cycle']['buy_price'], $res['cycle']['qty'], self::SCALE),
                        self::SCALE
                    );
                    unset($st['buyFeeByLevel'][$level]);
                    $st['ledger'][] = $res['cycle'];
                    $place($res['rearm']);
                }
            }

            if ($capOn && !$st['stopped']) {
                $inv = $machine->heldInventory();
                if (bccomp($inv, '0', 8) > 0) {
                    $unreal = bcsub(bcmul($inv, $price, self::SCALE), $st['invested'], self::SCALE);
                    if (bccomp($unreal, bcmul('-1', $cap, self::SCALE), self::SCALE) < 0) {
                        $st['stopped'] = true;
                        $cancelBuys();
                        if ($stopPolicy === 'flatten') {
                            $proceeds = bcmul($inv, $price, self::SCALE);
                            $liqFee = bcmul($proceeds, $config['fee_pct'], self::SCALE);
                            $st['realized'] = bcadd($st['realized'], bcsub(bcsub($proceeds, $st['invested'], self::SCALE), $liqFee, self::SCALE), self::SCALE);
                            $st['fees'] = bcadd($st['fees'], $liqFee, self::SCALE);
                            $st['invested'] = '0';
                            foreach ($sim->openOrders() as $o) {
                                $sim->cancelOrder($o['client_order_id']);
                            }
                            for ($i = 0; $i < count($levels) - 1; $i++) {
                                $machine->hydrateLevel($i, 'EMPTY');
                            }
                        }
                    } elseif (!$st['derisk'] && bccomp($unreal, bcmul('-0.6', $cap, self::SCALE), self::SCALE) <= 0) {
                        $st['derisk'] = true;
                        $cancelBuys();
                    } elseif ($st['derisk'] && bccomp($unreal, bcmul('-0.4', $cap, self::SCALE), self::SCALE) > 0) {
                        $st['derisk'] = false;
                        foreach ($machine->initialIntents($price) as $intent) {
                            $place($intent);
                        }
                    }
                }
            }
        }

        $inventory = $machine->heldInventory();
        $unrealized = bcsub(bcmul($inventory, $st['price'], self::SCALE), $st['invested'], self::SCALE);

        return [
            'levels' => $levels,
            'cycles' => $st['cycles'],
            'buy_fills' => $st['buy_fills'],
            'sell_fills' => $st['sell_fills'],
            'realized_pnl' => $st['realized'],
            'unrealized_pnl' => $unrealized,
            'fees_total' => $st['fees'],
            'ending_inventory' => $inventory,
            'ending_price' => $st['price'],
            'halted' => $st['halted'],
            'stop_triggered' => $st['stopped'],
            'vetoes' => $st['vetoes'],
            'cycle_ledger' => $st['ledger'],
        ];
    }
}
