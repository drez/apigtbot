# Schema Overview

_Auto-generated from `*.schema.xml` on `gc build`. Do not edit by hand._

## Tables

### `authy` &mdash; Authy

User

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_authy` | INTEGER(11) | no | null |  |
| `validation_key` | VARCHAR(32) | yes | null |  |
| `username` | VARCHAR(32) | yes | null | Username |
| `fullname` | VARCHAR(100) | yes | null | Fullname |
| `email` | VARCHAR(100) | no | null | Email |
| `passwd_hash` | VARCHAR(255) | no | null | Password |
| `expire` | DATE | yes | '0000-00-00' | Expiration |
| `deactivate` | ENUM | yes | 'No' | Deactivated |
| `language` | ENUM | yes | 'en_US' | Language |
| `theme` | ENUM | yes | 'mint' | Theme |
| `google_sub` | VARCHAR(64) | yes | null | Google sub |
| `google_email` | VARCHAR(255) | yes | null | Google email |
| `reset_token_hash` | VARCHAR(255) | yes | null | Reset token |
| `reset_token_expires` | INTEGER(10) | yes | null | Reset expires |
| `id_tenant` | INTEGER(10) | yes | 1 | Tenant |
| `location_address` | VARCHAR(500) | yes | null | Location Address |
| `location_lat` | DECIMAL(13) | yes | null |  |
| `location_lng` | DECIMAL(13) | yes | null |  |
| `is_root` | ENUM | no | 'No' | Root |
| `id_authy_group` | INTEGER | no | 1 | Primary group |
| `is_system` | ENUM | no | 'No' |  |
| `rights_all` | LONGVARCHAR | yes | null | Rights |
| `rights_group` | LONGVARCHAR | yes | null | Rights (group records) |
| `rights_owner` | LONGVARCHAR | yes | null | Rights (own records) |
| `onglet` | LONGVARCHAR | yes | null |  |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_authy_group` &rarr; `authy_group.id_authy_group`
- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `push_device` &mdash; PushDevice

Push device

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_push_device` | INTEGER(11) | no | null |  |
| `id_authy` | INTEGER(11) | no | null | User |
| `token` | VARCHAR(255) | no | null | Token |
| `platform` | ENUM | yes | 'ios' | Platform |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_authy` &rarr; `authy.id_authy`
- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `grid_run` &mdash; GridRun

Grid Run

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_grid_run` | INTEGER(11) | no | null |  |
| `label` | VARCHAR(100) | no | null | Label |
| `symbol` | VARCHAR(20) | no | 'BTCUSDT' | Symbol |
| `status` | ENUM | no | 'Draft' | Status |
| `kill_switch` | BOOLEAN(10) | yes | false | Kill switch |
| `profile` | ENUM | no | 'Balanced' | Risk profile |
| `algo` | ENUM | no | 'Grid' | Algorithm |
| `simulated` | BOOLEAN(10) | yes | true | Simulated |
| `p_low` | DECIMAL(18) | no | null | Range low |
| `p_high` | DECIMAL(18) | no | null | Range high |
| `n_levels` | INTEGER(10) | no | 20 | Grid lines |
| `spacing` | ENUM | no | 'Geometric' | Spacing |
| `allocation` | ENUM | no | 'EqualQuote' | Allocation |
| `budget_quote` | DECIMAL(18) | no | null | Budget (USDT) |
| `deploy_pct` | INTEGER(10) | yes | 100 | Deployed budget % |
| `alloc_mode` | ENUM | no | 'Auto' | Allocation mode |
| `fee_pct` | DECIMAL(9) | no | 0.001 | Fee per side |
| `max_position_quote` | DECIMAL(18) | no | null | Max position (USDT) |
| `max_order_quote` | DECIMAL(18) | no | null | Max per-order (USDT) |
| `daily_loss_limit_quote` | DECIMAL(18) | no | null | Daily loss limit |
| `max_unrealized_loss_quote` | DECIMAL(18) | yes | null | Max unrealized loss |
| `sell_at_loss` | BOOLEAN(10) | yes | false | Sell at loss |
| `sell_when_starved` | BOOLEAN(10) | yes | false | Sell at loss when starved |
| `breakout_buffer_pct` | DECIMAL(9) | yes | 0.02 | Breakout buffer |
| `breakout_policy` | ENUM | no | 'HaltAndHold' | On breakout |
| `max_open_orders` | INTEGER(10) | yes | 60 | Max open orders |
| `max_buy_levels_below` | INTEGER(10) | yes | null | Active buy levels below price (0 = all) |
| `trend_tf` | ENUM | no | '1h' | Signal timeframe |
| `donchian_period` | INTEGER(10) | yes | 20 | Breakout period |
| `trend_ema_fast` | INTEGER(10) | yes | 20 | Trend EMA fast |
| `trend_ema_slow` | INTEGER(10) | yes | 50 | Trend EMA slow |
| `atr_period` | INTEGER(10) | yes | 14 | ATR period |
| `atr_stop_mult` | DECIMAL(9) | yes | null | Trail stop x ATR |
| `atr_initial_mult` | DECIMAL(9) | yes | null | Initial stop x ATR |
| `trend_stop_floor_pct` | DECIMAL(9) | yes | 0.015 | Trail stop floor (fraction of HWM) |
| `trend_signal` | ENUM | no | 'Donchian' | Trend entry signal |
| `reentry_cooldown` | INTEGER(10) | yes | null | Re-entry cooldown (bars) |
| `engine_state` | LONGVARCHAR(1023) | yes | null | Engine state (daemon-managed) |
| `last_tick_at` | TIMESTAMP | yes | null | Last tick |
| `last_price` | DECIMAL(18) | yes | null | Last price |
| `bal_base` | DECIMAL(18) | yes | null | Wallet base |
| `bal_quote` | DECIMAL(18) | yes | null | Wallet quote |
| `sim_bal_base` | DECIMAL(18) | yes | null | Paper wallet base (daemon-managed) |
| `sim_bal_quote` | DECIMAL(18) | yes | null | Paper wallet quote (daemon-managed) |
| `run_uid` | VARCHAR(20) | yes | null | Run UID |
| `applied_geometry` | LONGVARCHAR(1023) | yes | null | Applied geometry (daemon-managed) |
| `ledger_reset_at` | TIMESTAMP | yes | null | Ledger rebased at (daemon-managed) |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `fleet_slot` &mdash; FleetSlot

Fleet slot

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_fleet_slot` | INTEGER(11) | no | null |  |
| `symbol` | VARCHAR(20) | no | null | Symbol |
| `algo` | ENUM | no | 'Trend' | Algorithm |
| `target_slice` | DECIMAL(20) | no | 350 | Target slice (USDT) |
| `enabled` | BOOLEAN(10) | yes | true | Enabled |
| `state` | ENUM | no | 'idle' | Arm state |
| `confirm_up` | INTEGER(10) | yes | 0 | Consecutive TREND_UP passes |
| `confirm_down` | INTEGER(10) | yes | 0 | Consecutive non-TREND_UP passes |
| `last_verdict` | VARCHAR(16) | yes | null | Last verdict |
| `verdict_at` | TIMESTAMP | yes | null | Verdict at |
| `episode_started_at` | TIMESTAMP | yes | null | TREND_UP episode started |
| `activation` | LONGVARCHAR(1023) | yes | null | Activation payload |
| `id_grid_run` | INTEGER(11) | yes | null | Run |
| `last_empty_alert_at` | TIMESTAMP | yes | null | Empty alerted at |
| `last_parked_alert_at` | TIMESTAMP | yes | null | Parked alerted at |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_grid_run` &rarr; `grid_run.id_grid_run`
- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `regime_episode` &mdash; RegimeEpisode

Regime episode

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_regime_episode` | INTEGER(11) | no | null |  |
| `symbol` | VARCHAR(20) | no | null | Symbol |
| `algo` | ENUM | no | 'Trend' | Algorithm |
| `id_fleet_slot` | INTEGER(11) | yes | null | Slot |
| `id_grid_run` | INTEGER(11) | yes | null | Run |
| `verdict` | VARCHAR(16) | no | null | Verdict |
| `opened_at` | TIMESTAMP | no | null | Opened at |
| `closed_at` | TIMESTAMP | yes | null | Closed at |
| `price_open` | DECIMAL(20) | no | null | Price at open |
| `price_close` | DECIMAL(20) | yes | null | Price at close |
| `engaged_pct_tw` | DECIMAL(8) | yes | null | Engaged % (time-weighted) |
| `samples` | INTEGER(10) | yes | 0 | Samples |
| `realized` | DECIMAL(20) | yes | 0 | Realized (USDT) |
| `mtm_close` | DECIMAL(20) | yes | null | Mark-to-market at close |
| `hodl_pct` | DECIMAL(8) | yes | null | HODL move % |
| `captured_pct` | DECIMAL(8) | yes | null | Captured % of HODL |
| `idle_samples` | INTEGER(10) | yes | 0 | Consecutive sub-floor samples |
| `idle_alerted_at` | TIMESTAMP | yes | null | Idle alerted at |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_fleet_slot` &rarr; `fleet_slot.id_fleet_slot`
- `id_grid_run` &rarr; `grid_run.id_grid_run`
- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `bot_order` &mdash; BotOrder

Order

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_bot_order` | INTEGER(11) | no | null |  |
| `id_grid_run` | INTEGER(11) | no | null | Run |
| `client_order_id` | VARCHAR(36) | no | null | Client order id |
| `exchange_order_id` | VARCHAR(32) | yes | null | Exchange id |
| `level_idx` | INTEGER(10) | no | null | Level |
| `side` | ENUM | no | null | Side |
| `state` | ENUM | no | 'Intended' | State |
| `price` | DECIMAL(18) | no | null | Price |
| `qty` | DECIMAL(18) | no | null | Qty |
| `filled_qty` | DECIMAL(18) | yes | 0 | Filled qty |
| `fee_paid` | DECIMAL(18) | yes | 0 | Fee paid |
| `fee_asset` | VARCHAR(10) | yes | null | Fee asset |
| `is_legacy` | BOOLEAN(10) | yes | false | Legacy exit |
| `legacy_buy_price` | DECIMAL(18) | yes | null | Legacy buy price (daemon-managed) |
| `legacy_buy_fee` | DECIMAL(18) | yes | null | Legacy buy fee (daemon-managed) |
| `simulated` | BOOLEAN(10) | yes | false | Simulated |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_grid_run` &rarr; `grid_run.id_grid_run`
- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `trade_cycle` &mdash; TradeCycle

Trade Cycle

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_trade_cycle` | INTEGER(11) | no | null |  |
| `id_grid_run` | INTEGER(11) | no | null | Run |
| `level_idx` | INTEGER(10) | no | null | Level |
| `buy_price` | DECIMAL(18) | no | null | Buy price |
| `sell_price` | DECIMAL(18) | no | null | Sell price |
| `qty` | DECIMAL(18) | no | null | Qty |
| `realized_pnl` | DECIMAL(18) | no | null | Realized PnL |
| `fees_total` | DECIMAL(18) | no | null | Fees |
| `simulated` | BOOLEAN(10) | yes | false | Simulated |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_grid_run` &rarr; `grid_run.id_grid_run`
- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `bot_event` &mdash; BotEvent

Event

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_bot_event` | INTEGER(11) | no | null |  |
| `id_grid_run` | INTEGER(11) | no | null | Run |
| `level` | ENUM | no | 'Info' | Level |
| `kind` | VARCHAR(50) | no | null | Kind |
| `message` | VARCHAR(500) | no | null | Message |
| `payload` | LONGVARCHAR(1023) | yes | null | Payload |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_grid_run` &rarr; `grid_run.id_grid_run`
- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `bot_command` &mdash; BotCommand

Command

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_bot_command` | INTEGER(11) | no | null |  |
| `id_grid_run` | INTEGER(11) | no | null | Run |
| `command` | ENUM | no | null | Command |
| `cmd_status` | ENUM | no | 'Pending' | Status |
| `note` | VARCHAR(255) | yes | null | Note |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_grid_run` &rarr; `grid_run.id_grid_run`
- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `sim_wallet` &mdash; SimWallet

Paper Wallet

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_sim_wallet` | INTEGER(11) | no | null |  |
| `asset` | VARCHAR(10) | no | null | Asset |
| `qty` | DECIMAL(18) | no | 0 | Quantity |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `market_summary` &mdash; MarketSummary

Market Data

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_market_summary` | INTEGER(11) | no | null |  |
| `symbol` | VARCHAR(20) | no | null | Symbol |
| `tf` | VARCHAR(5) | no | null | Timeframe |
| `price` | DECIMAL(18) | yes | null | Price |
| `ema20` | DECIMAL(18) | yes | null | EMA20 |
| `ema50` | DECIMAL(18) | yes | null | EMA50 |
| `ema200` | DECIMAL(18) | yes | null | EMA200 |
| `rsi14` | DECIMAL(9) | yes | null | RSI14 |
| `atr14` | DECIMAL(18) | yes | null | ATR14 |
| `atr_pct` | DECIMAL(9) | yes | null | ATR % |
| `trend` | ENUM | yes | 'sideways' | Trend |
| `swing_high` | DECIMAL(18) | yes | null | Swing high |
| `swing_low` | DECIMAL(18) | yes | null | Swing low |
| `candles_used` | INTEGER(10) | yes | 0 | Candles |
| `recent_candles` | LONGVARCHAR(1023) | yes | null | Candles JSON |
| `funding_rate` | DECIMAL(12) | yes | null | Funding rate |
| `depth_imbalance` | DECIMAL(9) | yes | null | Depth imbalance |
| `depth_imbalance_avg` | DECIMAL(9) | yes | null | Depth imbalance (smoothed) |
| `adx14` | DECIMAL(9) | yes | null | ADX14 |
| `atr_pct_rank` | DECIMAL(9) | yes | null | ATR% percentile |
| `taker_buy_ratio` | DECIMAL(9) | yes | null | Taker buy ratio |
| `vol_zscore` | DECIMAL(9) | yes | null | Volume z-score |
| `er20` | DECIMAL(9) | yes | null | Efficiency ratio |
| `chop14` | DECIMAL(9) | yes | null | Choppiness |
| `funding_pct` | DECIMAL(9) | yes | null | Funding 30d percentile |
| `computed_at` | TIMESTAMP | yes | null | Computed at |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `market_regime` &mdash; MarketRegime

Regime History

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_market_regime` | INTEGER(11) | no | null |  |
| `symbol` | VARCHAR(20) | no | null | Symbol |
| `tf` | VARCHAR(5) | no | null | Timeframe |
| `price` | DECIMAL(18) | yes | null | Price |
| `trend` | ENUM | yes | 'sideways' | Trend |
| `rsi14` | DECIMAL(9) | yes | null | RSI14 |
| `atr_pct` | DECIMAL(9) | yes | null | ATR % |
| `adx14` | DECIMAL(9) | yes | null | ADX14 |
| `atr_pct_rank` | DECIMAL(9) | yes | null | ATR% percentile |
| `taker_buy_ratio` | DECIMAL(9) | yes | null | Taker buy ratio |
| `vol_zscore` | DECIMAL(9) | yes | null | Volume z-score |
| `er20` | DECIMAL(9) | yes | null | Efficiency ratio |
| `chop14` | DECIMAL(9) | yes | null | Choppiness |
| `funding_pct` | DECIMAL(9) | yes | null | Funding 30d percentile |
| `funding_rate` | DECIMAL(12) | yes | null | Funding rate |
| `depth_imbalance` | DECIMAL(9) | yes | null | Depth imbalance |
| `depth_imbalance_avg` | DECIMAL(9) | yes | null | Depth imbalance (smoothed) |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `market_candle` &mdash; MarketCandle

Candles

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_market_candle` | INTEGER(11) | no | null |  |
| `symbol` | VARCHAR(20) | no | null | Symbol |
| `tf` | ENUM | no | null | Timeframe |
| `open_time` | INTEGER(10) | no | null | Open time (epoch s) |
| `open` | DECIMAL(18) | no | null | Open |
| `high` | DECIMAL(18) | no | null | High |
| `low` | DECIMAL(18) | no | null | Low |
| `close` | DECIMAL(18) | no | null | Close |
| `volume` | DECIMAL(24) | yes | 0 | Volume |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `bot_decision` &mdash; BotDecision

Refit Decision

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_bot_decision` | INTEGER(11) | no | null |  |
| `id_grid_run` | INTEGER(11) | no | null | Run |
| `source` | ENUM | no | 'Claude' | Source |
| `p_low` | DECIMAL(18) | no | null | Range low |
| `p_high` | DECIMAL(18) | no | null | Range high |
| `n_levels` | INTEGER(10) | no | null | Levels |
| `deploy_pct` | INTEGER(10) | yes | null | Deployed budget % |
| `reason` | VARCHAR(500) | yes | null | Reason |
| `price_at` | DECIMAL(18) | yes | null | Price at decision |
| `realized_before` | DECIMAL(18) | yes | 0 | Realized before |
| `eval_status` | ENUM | no | 'Pending' | Eval |
| `eval_at` | TIMESTAMP | yes | null | Scored at |
| `applied_at` | TIMESTAMP | yes | null | Applied at |
| `cycles_delta` | INTEGER(10) | yes | null | Cycles after |
| `realized_delta` | DECIMAL(18) | yes | null | P/L after |
| `price_move_pct` | DECIMAL(9) | yes | null | Price move % |
| `verdict` | ENUM | yes | null | Verdict |
| `counterfactual_delta` | DECIMAL(18) | yes | null | vs no-change (sim) |
| `candidate_delta` | ENUM | yes | null | vs candidate |
| `requested_json` | LONGVARCHAR(1023) | yes | null | Requested (pre-gate) |
| `clamps_json` | LONGVARCHAR(1023) | yes | null | Clamp trail |
| `brief_json` | LONGVARCHAR(1023) | yes | null | Brief snapshot |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_grid_run` &rarr; `grid_run.id_grid_run`
- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `market_outlook` &mdash; MarketOutlook

Market Outlook

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_market_outlook` | INTEGER(11) | no | null |  |
| `symbol` | VARCHAR(20) | no | null | Symbol |
| `kind` | VARCHAR(10) | no | 'Change' | Kind |
| `verdict` | VARCHAR(16) | no | null | Verdict |
| `prev_verdict` | VARCHAR(16) | yes | null | Previous |
| `price_at` | DECIMAL(18) | no | null | Price at call |
| `called_at` | TIMESTAMP | no | null | Called at |
| `detail` | LONGVARCHAR(1023) | yes | null | Detail |
| `eval_status` | VARCHAR(10) | no | 'Pending' | Eval |
| `price_7d` | DECIMAL(18) | yes | null | Price +7d |
| `price_30d` | DECIMAL(18) | yes | null | Price +30d |
| `ret_7d` | DECIMAL(9) | yes | null | Return 7d % |
| `ret_30d` | DECIMAL(9) | yes | null | Return 30d % |
| `max_adverse_pct` | DECIMAL(9) | yes | null | Max adverse 30d % |
| `hit_7d` | BOOLEAN(10) | yes | null | Hit 7d |
| `hit_30d` | BOOLEAN(10) | yes | null | Hit 30d |
| `scored_at` | TIMESTAMP | yes | null | Scored at |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `market_outlook_state` &mdash; MarketOutlookState

Outlook State

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_market_outlook_state` | INTEGER(11) | no | null |  |
| `symbol` | VARCHAR(20) | no | null | Symbol |
| `verdict` | VARCHAR(16) | yes | null | Verdict |
| `verdict_since` | TIMESTAMP | yes | null | Since |
| `price_at_verdict` | DECIMAL(18) | yes | null | Price at verdict |
| `candidate` | VARCHAR(16) | yes | null | Candidate |
| `candidate_passes` | INTEGER(10) | no | 0 | Candidate passes |
| `last_raw` | VARCHAR(16) | yes | null | Last raw read |
| `last_pass_at` | TIMESTAMP | yes | null | Last pass |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `wallet_nav` &mdash; WalletNav

Wallet NAV

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_wallet_nav` | INTEGER(11) | no | null |  |
| `mode` | ENUM | no | 'sim' | Mode |
| `equity_quote` | DECIMAL(18) | no | null | Equity (USDT) |
| `budget_quote` | DECIMAL(18) | no | null | Shared budget |
| `ref_symbol` | VARCHAR(20) | yes | null | HODL reference |
| `ref_price` | DECIMAL(18) | yes | null | Reference price |
| `unpriced` | VARCHAR(100) | yes | null | Unpriced assets |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `authy_group` &mdash; AuthyGroup

Group

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_authy_group` | INTEGER | no | null |  |
| `name` | VARCHAR(50) | yes | null | Name |
| `desc` | VARCHAR(32) | yes | null | Description |
| `default_group` | ENUM | no | null | Default |
| `admin` | ENUM | no | null | Admin |
| `rights_all` | VARCHAR(1023) | yes | null | Rights |
| `rights_owner` | VARCHAR(1023) | yes | null | Rights owner |
| `rights_group` | VARCHAR(1023) | yes | null | Rights group |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `authy_group_x` &mdash; AuthyGroupX

Group

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_authy` | INTEGER | no | null |  |
| `id_authy_group` | INTEGER | no | null | Group |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_authy_group` &rarr; `authy_group.id_authy_group`
- `id_authy` &rarr; `authy.id_authy`
- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `authy_log` &mdash; AuthyLog

Login log

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_authy_log` | INTEGER | no | null |  |
| `id_authy` | INTEGER | yes | null |  |
| `timestamp` | TIMESTAMP | yes | null | Date |
| `login` | VARCHAR(50) | no | null | Username |
| `userid` | INTEGER | yes | null |  |
| `result` | VARCHAR(100) | no | null |  |
| `event` | VARCHAR(64) | yes | null | Event |
| `ip` | VARCHAR(16) | no | null | Ip |
| `count` | INTEGER | yes | null | Count |

**Foreign keys:**

- `id_authy` &rarr; `authy.id_authy`

### `message` &mdash; Message

Message

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_message` | INTEGER | no | null |  |
| `label` | VARCHAR(100) | no | null | Label |

### `config` &mdash; Config

Setting

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_config` | INTEGER | no | null |  |
| `category` | ENUM | no | null | Category |
| `config` | VARCHAR(100) | no | null | Setting |
| `value` | LONGVARCHAR(400) | yes | null | Value |
| `system` | ENUM | yes | 'y' |  |
| `description` | VARCHAR(500) | yes | null | Description |
| `type` | VARCHAR(35) | yes | null |  |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `api_rbac` &mdash; ApiRbac

API ACL

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_api_rbac` | INTEGER(11) | no | null |  |
| `date_creation` | DATE | no | null | Date |
| `description` | LONGVARCHAR(1023) | yes | null | Description |
| `model` | VARCHAR(200) | no | null | Model |
| `action` | VARCHAR(200) | yes | null | Action |
| `body` | LONGVARCHAR(1023) | yes | null | Body |
| `query` | LONGVARCHAR(1023) | yes | null | Query |
| `method` | ENUM | no | 'GET' | Method |
| `scope` | ENUM | no | 'Private' | Scope |
| `rule` | ENUM | no | 'Deny' | Rule |
| `count` | INTEGER | no | 0 | Used count |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `api_log` &mdash; ApiLog

API log

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_api_log` | INTEGER(11) | no | null |  |
| `id_api_rbac` | INTEGER | no | null | Rule |
| `id_authy` | INTEGER | yes | null | User |
| `time` | TIMESTAMP | no | null | Time |
| `raw_parameters` | LONGVARCHAR(1023) | yes | null | Raw parameters |
| `count` | INTEGER | no | 1 | Count |

**Foreign keys:**

- `id_api_rbac` &rarr; `api_rbac.id_api_rbac`
- `id_authy` &rarr; `authy.id_authy`

### `template` &mdash; Template

Template

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_template` | INTEGER(11) | no | null |  |
| `name` | VARCHAR(100) | no | null | Name |
| `subject` | VARCHAR(200) | yes | null | Subject |
| `color_1` | VARCHAR(10) | yes | null | Color 1 |
| `color_2` | VARCHAR(10) | yes | null | Color 2 |
| `color_3` | VARCHAR(10) | yes | null | Color 3 |
| `status` | ENUM | no | 'Active' | Status |
| `body` | LONGVARCHAR(1023) | yes | null | Body |
| `footer` | LONGVARCHAR(1023) | yes | null | Footer |
| `lang` | ENUM | yes | 'fr_CA' | Language |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `template_file` &mdash; TemplateFile

File

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_template_file` | INTEGER(11) | no | null |  |
| `id_template` | INTEGER | no | null |  |
| `name` | VARCHAR(100) | yes | null | Name |
| `file` | VARCHAR(500) | yes | null | File |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_template` &rarr; `template.id_template`
- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `authy_refresh_token` &mdash; AuthyRefreshToken

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_authy_refresh_token` | INTEGER(11) | no | null |  |
| `id_authy` | INTEGER(11) | no | null |  |
| `family_id` | VARCHAR(64) | no | null |  |
| `token_hash` | VARCHAR(64) | no | null |  |
| `expires` | INTEGER | no | null |  |
| `family_expires` | INTEGER | no | null |  |
| `revoked` | ENUM | yes | 'No' |  |
| `created_at` | TIMESTAMP | yes | null |  |
| `last_used_at` | TIMESTAMP | yes | null |  |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_authy` &rarr; `authy.id_authy`
- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `grid_run_audit` &mdash; GridRunAudit

Change history

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_grid_run_audit` | INTEGER(11) | no | null |  |
| `id_grid_run` | INTEGER | no | null | Record |
| `field` | VARCHAR(64) | no | null | Field |
| `value_from` | LONGVARCHAR | yes | null | From |
| `value_to` | LONGVARCHAR | yes | null | To |
| `actor` | VARCHAR(128) | yes | null | Actor |
| `source` | ENUM | yes | 'gui' | Source |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_grid_run` &rarr; `grid_run.id_grid_run`
- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `country` &mdash; Country

Country

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_country` | INTEGER(11) | no | null |  |
| `name` | VARCHAR(100) | yes | null | Name |
| `code` | VARCHAR(3) | yes | null | Code |
| `timezone` | VARCHAR(20) | yes | null | Timezone |
| `timezone_code` | VARCHAR(50) | yes | null | Timezone code |
| `priority` | INTEGER(10) | yes | null | Priority |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `oauth_client` &mdash; OauthClient

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_oauth_client` | INTEGER(11) | no | null |  |
| `client_id` | VARCHAR(80) | no | null |  |
| `client_secret_hash` | VARCHAR(255) | yes | null |  |
| `name` | VARCHAR(191) | yes | null |  |
| `redirect_uris` | LONGVARCHAR | yes | null |  |
| `grant_types` | VARCHAR(191) | yes | null |  |
| `scopes` | VARCHAR(191) | yes | null |  |
| `is_confidential` | ENUM | yes | 'No' |  |
| `created_at` | TIMESTAMP | yes | null |  |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `oauth_auth_code` &mdash; OauthAuthCode

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_oauth_auth_code` | INTEGER(11) | no | null |  |
| `code_id` | VARCHAR(128) | no | null |  |
| `id_authy` | INTEGER(11) | yes | null |  |
| `client_id` | VARCHAR(80) | yes | null |  |
| `scopes` | VARCHAR(191) | yes | null |  |
| `expires` | INTEGER | yes | null |  |
| `revoked` | ENUM | yes | 'No' |  |
| `created_at` | TIMESTAMP | yes | null |  |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_authy` &rarr; `authy.id_authy`
- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `oauth_access_token` &mdash; OauthAccessToken

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_oauth_access_token` | INTEGER(11) | no | null |  |
| `token_id` | VARCHAR(128) | no | null |  |
| `id_authy` | INTEGER(11) | yes | null |  |
| `client_id` | VARCHAR(80) | yes | null |  |
| `scopes` | VARCHAR(191) | yes | null |  |
| `expires` | INTEGER | yes | null |  |
| `revoked` | ENUM | yes | 'No' |  |
| `created_at` | TIMESTAMP | yes | null |  |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_authy` &rarr; `authy.id_authy`
- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `oauth_refresh_token` &mdash; OauthRefreshToken

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_oauth_refresh_token` | INTEGER(11) | no | null |  |
| `token_id` | VARCHAR(128) | no | null |  |
| `access_token_id` | VARCHAR(128) | yes | null |  |
| `id_authy` | INTEGER(11) | yes | null |  |
| `client_id` | VARCHAR(80) | yes | null |  |
| `expires` | INTEGER | yes | null |  |
| `revoked` | ENUM | yes | 'No' |  |
| `created_at` | TIMESTAMP | yes | null |  |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_authy` &rarr; `authy.id_authy`
- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`

### `message_i18n` &mdash; MessageI18n

| Column | Type | Nullable | Default | Description |
| --- | --- | --- | --- | --- |
| `id_message` | INTEGER | no | null |  |
| `locale` | VARCHAR(5) | no | 'en_US' |  |
| `text` | LONGVARCHAR(200) | yes | null | Texte |
| `date_creation` | TIMESTAMP | yes | null |  |
| `date_modification` | TIMESTAMP | yes | null |  |
| `id_group_creation` | INTEGER | yes | null |  |
| `id_creation` | INTEGER | yes | null |  |
| `id_modification` | INTEGER | yes | null |  |

**Foreign keys:**

- `id_message` &rarr; `message.id_message`
- `id_group_creation` &rarr; `authy_group.id_authy_group`
- `id_creation` &rarr; `authy.id_authy`
- `id_modification` &rarr; `authy.id_authy`
