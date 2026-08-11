# Stuck-partial-fill handler + distance-from-price buy window

Date: 2026-07-21 · Status: approved (agreed in discussion: "condition, not
timeout" — these are the two conditions worth building)

## 1. Stuck partial fills

Problem: a partially filled order stays on the book, so `detectFills()` (which
only resolves orders that *left* the book) never touches it — the level is
blocked forever and today the partial isn't even detected until the order
leaves the book.

- **Detection**: `detectFills()` also scans the exchange's open orders for our
  rows with `executedQty > 0` → `markPartFilled` + start an in-memory timer
  (`cid → first-seen ts`). Timer restarting on daemon restart is accepted.
- **Timeout**: 30 min (`Daemon::partialTimeoutSeconds()`, overridable for
  tests).
- **Buy side on timeout**: cancel the remainder on the exchange, book the
  filled portion (`markFilled` with actual executedQty + fee estimate on that
  notional), transition the level via new
  `LevelStateMachine::onPartialBuyBooked(i, executedQty)` → emit the matched
  sell for the *actual* quantity. If the sell can't clear exchange filters
  (dust), write it off: level re-armed EMPTY + Alert.
- **Sell side on timeout**: alert only (`stuck_partial_sell`), re-alert each
  timeout window. The exit is still correctly priced; cancelling it would
  strand inventory.

### LevelStateMachine gains variable-qty awareness

- `held[i]` becomes the source of truth: `openSellQty()` sums `held` (not
  `qtys`), `onSellFill()` books the cycle with `held[i]`, `hydrateLevel()`
  takes an optional explicit held qty.
- `GridStateHydrator` passes the open sell row's actual qty, and now also
  hydrates `PartFilled` rows (side → BUY_OPEN/SELL_OPEN) — previously a
  restart would forget a partial and re-arm the level (duplicate order).

## 2. Distance-from-price buy window

New nullable int on `grid_run`: `max_buy_levels_below` (0/null = disabled,
today's behavior). When set to N, only the N nearest buy levels below the
market price stay armed:

- `minLevel = top − N + 1` where `top` = highest buy level priced below
  market.
- `initialIntents(price, minLevel)` arms only levels ≥ minLevel.
- A prune pass each tick cancels `BUY_OPEN` orders below minLevel
  (`distance_prune` event); `PartFilled` buys are never pruned (the stuck
  handler owns those). As price falls back, levels re-arm automatically.
- Churn bound: one cancel+place per grid-line crossing of the window edge —
  acceptable at ≥1% spacing.

## Testing

- Machine unit tests: partial booking emits sell for actual qty, cycle uses
  actual qty, invariant holds; `initialIntents` respects minLevel; hydrate
  with explicit qty.
- Daemon E2E (ExchangeSim gains `partialFill()` + PARTIALLY_FILLED support):
  stuck partial buy → remainder cancelled, fill booked, sell placed at actual
  qty; distance window: far buys pruned as price rises, re-armed as it falls;
  sells untouched.

## Out of scope

- Backtester support for either feature.
- Persisting partial-fill timers across restarts.
