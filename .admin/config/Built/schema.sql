
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- authy
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `authy`;

CREATE TABLE `authy`
(
    `id_authy` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `validation_key` VARCHAR(32),
    `username` VARCHAR(32) COMMENT 'Username',
    `fullname` VARCHAR(100) COMMENT 'Fullname',
    `email` VARCHAR(100) NOT NULL COMMENT 'Email',
    `passwd_hash` VARCHAR(255) NOT NULL COMMENT 'Password',
    `expire` DATE DEFAULT '0000-00-00' COMMENT 'Expiration',
    `deactivate` TINYINT DEFAULT 1 COMMENT 'Deactivated',
    `language` TINYINT DEFAULT 0 COMMENT 'Language',
    `theme` TINYINT DEFAULT 0 COMMENT 'Theme',
    `google_sub` VARCHAR(64) COMMENT 'Google sub',
    `google_email` VARCHAR(255) COMMENT 'Google email',
    `reset_token_hash` VARCHAR(255) COMMENT 'Reset token',
    `reset_token_expires` INTEGER(10) COMMENT 'Reset expires',
    `id_tenant` INTEGER(10) DEFAULT 1 COMMENT 'Tenant',
    `location_address` VARCHAR(500) COMMENT 'Location Address',
    `location_lat` DECIMAL(13, 8),
    `location_lng` DECIMAL(13, 8),
    `is_root` TINYINT DEFAULT 1 NOT NULL COMMENT 'Root',
    `id_authy_group` INTEGER DEFAULT 1 NOT NULL COMMENT 'Primary group',
    `is_system` TINYINT DEFAULT 1 NOT NULL,
    `rights_all` TEXT COMMENT 'Rights',
    `rights_group` TEXT COMMENT 'Rights (group records)',
    `rights_owner` TEXT COMMENT 'Rights (own records)',
    `onglet` TEXT,
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_authy`),
    UNIQUE INDEX `authy_U_1` (`username`),
    UNIQUE INDEX `authy_U_2` (`google_sub`),
    INDEX `authy_FI_1` (`id_authy_group`),
    INDEX `authy_FI_2` (`id_group_creation`),
    INDEX `authy_FI_3` (`id_creation`),
    INDEX `authy_FI_4` (`id_modification`),
    CONSTRAINT `authy_FK_1`
        FOREIGN KEY (`id_authy_group`)
        REFERENCES `authy_group` (`id_authy_group`)
        ON DELETE CASCADE,
    CONSTRAINT `authy_FK_2`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `authy_FK_3`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `authy_FK_4`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='User';

-- ---------------------------------------------------------------------
-- push_device
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `push_device`;

CREATE TABLE `push_device`
(
    `id_push_device` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `id_authy` INTEGER(11) NOT NULL COMMENT 'User',
    `token` VARCHAR(255) NOT NULL COMMENT 'Token',
    `platform` TINYINT DEFAULT 0 COMMENT 'Platform',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_push_device`),
    UNIQUE INDEX `push_device_U_1` (`token`),
    INDEX `push_device_FI_1` (`id_authy`),
    INDEX `push_device_FI_2` (`id_group_creation`),
    INDEX `push_device_FI_3` (`id_creation`),
    INDEX `push_device_FI_4` (`id_modification`),
    CONSTRAINT `push_device_FK_1`
        FOREIGN KEY (`id_authy`)
        REFERENCES `authy` (`id_authy`)
        ON DELETE CASCADE,
    CONSTRAINT `push_device_FK_2`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `push_device_FK_3`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `push_device_FK_4`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Push device';

-- ---------------------------------------------------------------------
-- grid_run
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `grid_run`;

CREATE TABLE `grid_run`
(
    `id_grid_run` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `label` VARCHAR(100) NOT NULL COMMENT 'Label',
    `symbol` VARCHAR(20) DEFAULT 'BTCUSDT' NOT NULL COMMENT 'Symbol',
    `status` TINYINT DEFAULT 0 NOT NULL COMMENT 'Status',
    `kill_switch` TINYINT(10) DEFAULT 0 COMMENT 'Kill switch',
    `profile` TINYINT DEFAULT 2 NOT NULL COMMENT 'Risk profile',
    `algo` TINYINT DEFAULT 0 NOT NULL COMMENT 'Algorithm',
    `simulated` TINYINT(10) DEFAULT 1 COMMENT 'Simulated',
    `p_low` DECIMAL(18, 8) NOT NULL COMMENT 'Range low',
    `p_high` DECIMAL(18, 8) NOT NULL COMMENT 'Range high',
    `n_levels` INTEGER(10) DEFAULT 20 NOT NULL COMMENT 'Grid lines',
    `spacing` TINYINT DEFAULT 0 NOT NULL COMMENT 'Spacing',
    `allocation` TINYINT DEFAULT 0 NOT NULL COMMENT 'Allocation',
    `budget_quote` DECIMAL(18, 8) NOT NULL COMMENT 'Budget (USDT)',
    `deploy_pct` INTEGER(10) DEFAULT 100 COMMENT 'Deployed budget %',
    `alloc_mode` TINYINT DEFAULT 0 NOT NULL COMMENT 'Allocation mode',
    `fee_pct` DECIMAL(9, 6) DEFAULT 0.001 NOT NULL COMMENT 'Fee per side',
    `max_position_quote` DECIMAL(18, 8) NOT NULL COMMENT 'Max position (USDT)',
    `max_order_quote` DECIMAL(18, 8) NOT NULL COMMENT 'Max per-order (USDT)',
    `daily_loss_limit_quote` DECIMAL(18, 8) NOT NULL COMMENT 'Daily loss limit',
    `max_unrealized_loss_quote` DECIMAL(18, 8) COMMENT 'Max unrealized loss',
    `sell_at_loss` TINYINT(10) DEFAULT 0 COMMENT 'Sell at loss',
    `sell_when_starved` TINYINT(10) DEFAULT 0 COMMENT 'Sell at loss when starved',
    `breakout_buffer_pct` DECIMAL(9, 6) DEFAULT 0.02 COMMENT 'Breakout buffer',
    `breakout_policy` TINYINT DEFAULT 0 NOT NULL COMMENT 'On breakout',
    `max_open_orders` INTEGER(10) DEFAULT 60 COMMENT 'Max open orders',
    `max_buy_levels_below` INTEGER(10) COMMENT 'Active buy levels below price (0 = all)',
    `trend_tf` TINYINT DEFAULT 0 NOT NULL COMMENT 'Signal timeframe',
    `donchian_period` INTEGER(10) DEFAULT 20 COMMENT 'Breakout period',
    `trend_ema_fast` INTEGER(10) DEFAULT 20 COMMENT 'Trend EMA fast',
    `trend_ema_slow` INTEGER(10) DEFAULT 50 COMMENT 'Trend EMA slow',
    `atr_period` INTEGER(10) DEFAULT 14 COMMENT 'ATR period',
    `atr_stop_mult` DECIMAL(9, 4) COMMENT 'Trail stop x ATR',
    `atr_initial_mult` DECIMAL(9, 4) COMMENT 'Initial stop x ATR',
    `trend_stop_floor_pct` DECIMAL(9, 6) DEFAULT 0.015 COMMENT 'Trail stop floor (fraction of HWM)',
    `trend_signal` TINYINT DEFAULT 0 NOT NULL COMMENT 'Trend entry signal',
    `reentry_cooldown` INTEGER(10) COMMENT 'Re-entry cooldown (bars)',
    `engine_state` TEXT(1023) COMMENT 'Engine state (daemon-managed)',
    `last_tick_at` DATETIME COMMENT 'Last tick',
    `last_price` DECIMAL(18, 8) COMMENT 'Last price',
    `bal_base` DECIMAL(18, 8) COMMENT 'Wallet base',
    `bal_quote` DECIMAL(18, 8) COMMENT 'Wallet quote',
    `sim_bal_base` DECIMAL(18, 8) COMMENT 'Paper wallet base (daemon-managed)',
    `sim_bal_quote` DECIMAL(18, 8) COMMENT 'Paper wallet quote (daemon-managed)',
    `run_uid` VARCHAR(20) COMMENT 'Run UID',
    `applied_geometry` TEXT(1023) COMMENT 'Applied geometry (daemon-managed)',
    `ledger_reset_at` DATETIME COMMENT 'Ledger rebased at (daemon-managed)',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_grid_run`),
    UNIQUE INDEX `grid_run_U_1` (`label`),
    INDEX `grid_run_FI_1` (`id_group_creation`),
    INDEX `grid_run_FI_2` (`id_creation`),
    INDEX `grid_run_FI_3` (`id_modification`),
    CONSTRAINT `grid_run_FK_1`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `grid_run_FK_2`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `grid_run_FK_3`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Grid Run';

-- ---------------------------------------------------------------------
-- fleet_slot
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `fleet_slot`;

CREATE TABLE `fleet_slot`
(
    `id_fleet_slot` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `symbol` VARCHAR(20) NOT NULL COMMENT 'Symbol',
    `algo` TINYINT DEFAULT 0 NOT NULL COMMENT 'Algorithm',
    `target_slice` DECIMAL(20, 8) DEFAULT 350 NOT NULL COMMENT 'Target slice (USDT)',
    `enabled` TINYINT(10) DEFAULT 1 COMMENT 'Enabled',
    `state` TINYINT DEFAULT 0 NOT NULL COMMENT 'Arm state',
    `confirm_up` INTEGER(10) DEFAULT 0 COMMENT 'Consecutive TREND_UP passes',
    `confirm_down` INTEGER(10) DEFAULT 0 COMMENT 'Consecutive non-TREND_UP passes',
    `last_verdict` VARCHAR(16) COMMENT 'Last verdict',
    `verdict_at` DATETIME COMMENT 'Verdict at',
    `episode_started_at` DATETIME COMMENT 'TREND_UP episode started',
    `activation` TEXT(1023) COMMENT 'Activation payload',
    `id_grid_run` INTEGER(11) COMMENT 'Run',
    `last_empty_alert_at` DATETIME COMMENT 'Empty alerted at',
    `last_parked_alert_at` DATETIME COMMENT 'Parked alerted at',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_fleet_slot`),
    UNIQUE INDEX `fleet_slot_U_1` (`symbol`, `algo`),
    INDEX `fleet_slot_FI_1` (`id_grid_run`),
    INDEX `fleet_slot_FI_2` (`id_group_creation`),
    INDEX `fleet_slot_FI_3` (`id_creation`),
    INDEX `fleet_slot_FI_4` (`id_modification`),
    CONSTRAINT `fleet_slot_FK_1`
        FOREIGN KEY (`id_grid_run`)
        REFERENCES `grid_run` (`id_grid_run`)
        ON DELETE SET NULL,
    CONSTRAINT `fleet_slot_FK_2`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `fleet_slot_FK_3`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `fleet_slot_FK_4`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Fleet slot';

-- ---------------------------------------------------------------------
-- regime_episode
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `regime_episode`;

CREATE TABLE `regime_episode`
(
    `id_regime_episode` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `symbol` VARCHAR(20) NOT NULL COMMENT 'Symbol',
    `algo` TINYINT DEFAULT 0 NOT NULL COMMENT 'Algorithm',
    `id_fleet_slot` INTEGER(11) COMMENT 'Slot',
    `id_grid_run` INTEGER(11) COMMENT 'Run',
    `verdict` VARCHAR(16) NOT NULL COMMENT 'Verdict',
    `opened_at` DATETIME NOT NULL COMMENT 'Opened at',
    `closed_at` DATETIME COMMENT 'Closed at',
    `price_open` DECIMAL(20, 8) NOT NULL COMMENT 'Price at open',
    `price_close` DECIMAL(20, 8) COMMENT 'Price at close',
    `engaged_pct_tw` DECIMAL(8, 4) COMMENT 'Engaged % (time-weighted)',
    `samples` INTEGER(10) DEFAULT 0 COMMENT 'Samples',
    `realized` DECIMAL(20, 8) DEFAULT 0 COMMENT 'Realized (USDT)',
    `mtm_close` DECIMAL(20, 8) COMMENT 'Mark-to-market at close',
    `hodl_pct` DECIMAL(8, 4) COMMENT 'HODL move %',
    `captured_pct` DECIMAL(8, 4) COMMENT 'Captured % of HODL',
    `idle_samples` INTEGER(10) DEFAULT 0 COMMENT 'Consecutive sub-floor samples',
    `idle_alerted_at` DATETIME COMMENT 'Idle alerted at',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_regime_episode`),
    INDEX `regime_episode_FI_1` (`id_fleet_slot`),
    INDEX `regime_episode_FI_2` (`id_grid_run`),
    INDEX `regime_episode_FI_3` (`id_group_creation`),
    INDEX `regime_episode_FI_4` (`id_creation`),
    INDEX `regime_episode_FI_5` (`id_modification`),
    CONSTRAINT `regime_episode_FK_1`
        FOREIGN KEY (`id_fleet_slot`)
        REFERENCES `fleet_slot` (`id_fleet_slot`)
        ON DELETE SET NULL,
    CONSTRAINT `regime_episode_FK_2`
        FOREIGN KEY (`id_grid_run`)
        REFERENCES `grid_run` (`id_grid_run`)
        ON DELETE SET NULL,
    CONSTRAINT `regime_episode_FK_3`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `regime_episode_FK_4`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `regime_episode_FK_5`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Regime episode';

-- ---------------------------------------------------------------------
-- bot_order
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `bot_order`;

CREATE TABLE `bot_order`
(
    `id_bot_order` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `id_grid_run` INTEGER(11) NOT NULL COMMENT 'Run',
    `client_order_id` VARCHAR(36) NOT NULL COMMENT 'Client order id',
    `exchange_order_id` VARCHAR(32) COMMENT 'Exchange id',
    `level_idx` INTEGER(10) NOT NULL COMMENT 'Level',
    `side` TINYINT NOT NULL COMMENT 'Side',
    `state` TINYINT DEFAULT 0 NOT NULL COMMENT 'State',
    `price` DECIMAL(18, 8) NOT NULL COMMENT 'Price',
    `qty` DECIMAL(18, 8) NOT NULL COMMENT 'Qty',
    `filled_qty` DECIMAL(18, 8) DEFAULT 0 COMMENT 'Filled qty',
    `fee_paid` DECIMAL(18, 8) DEFAULT 0 COMMENT 'Fee paid',
    `fee_asset` VARCHAR(10) COMMENT 'Fee asset',
    `is_legacy` TINYINT(10) DEFAULT 0 COMMENT 'Legacy exit',
    `legacy_buy_price` DECIMAL(18, 8) COMMENT 'Legacy buy price (daemon-managed)',
    `legacy_buy_fee` DECIMAL(18, 8) COMMENT 'Legacy buy fee (daemon-managed)',
    `simulated` TINYINT(10) DEFAULT 0 COMMENT 'Simulated',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_bot_order`),
    UNIQUE INDEX `bot_order_U_1` (`client_order_id`),
    INDEX `bot_order_FI_1` (`id_grid_run`),
    INDEX `bot_order_FI_2` (`id_group_creation`),
    INDEX `bot_order_FI_3` (`id_creation`),
    INDEX `bot_order_FI_4` (`id_modification`),
    CONSTRAINT `bot_order_FK_1`
        FOREIGN KEY (`id_grid_run`)
        REFERENCES `grid_run` (`id_grid_run`)
        ON DELETE CASCADE,
    CONSTRAINT `bot_order_FK_2`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `bot_order_FK_3`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `bot_order_FK_4`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Order';

-- ---------------------------------------------------------------------
-- trade_cycle
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `trade_cycle`;

CREATE TABLE `trade_cycle`
(
    `id_trade_cycle` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `id_grid_run` INTEGER(11) NOT NULL COMMENT 'Run',
    `level_idx` INTEGER(10) NOT NULL COMMENT 'Level',
    `buy_price` DECIMAL(18, 8) NOT NULL COMMENT 'Buy price',
    `sell_price` DECIMAL(18, 8) NOT NULL COMMENT 'Sell price',
    `qty` DECIMAL(18, 8) NOT NULL COMMENT 'Qty',
    `realized_pnl` DECIMAL(18, 8) NOT NULL COMMENT 'Realized PnL',
    `fees_total` DECIMAL(18, 8) NOT NULL COMMENT 'Fees',
    `simulated` TINYINT(10) DEFAULT 0 COMMENT 'Simulated',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_trade_cycle`),
    INDEX `trade_cycle_FI_1` (`id_grid_run`),
    INDEX `trade_cycle_FI_2` (`id_group_creation`),
    INDEX `trade_cycle_FI_3` (`id_creation`),
    INDEX `trade_cycle_FI_4` (`id_modification`),
    CONSTRAINT `trade_cycle_FK_1`
        FOREIGN KEY (`id_grid_run`)
        REFERENCES `grid_run` (`id_grid_run`)
        ON DELETE CASCADE,
    CONSTRAINT `trade_cycle_FK_2`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `trade_cycle_FK_3`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `trade_cycle_FK_4`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Trade Cycle';

-- ---------------------------------------------------------------------
-- bot_event
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `bot_event`;

CREATE TABLE `bot_event`
(
    `id_bot_event` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `id_grid_run` INTEGER(11) NOT NULL COMMENT 'Run',
    `level` TINYINT DEFAULT 0 NOT NULL COMMENT 'Level',
    `kind` VARCHAR(50) NOT NULL COMMENT 'Kind',
    `message` VARCHAR(500) NOT NULL COMMENT 'Message',
    `payload` TEXT(1023) COMMENT 'Payload',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_bot_event`),
    INDEX `bot_event_FI_1` (`id_grid_run`),
    INDEX `bot_event_FI_2` (`id_group_creation`),
    INDEX `bot_event_FI_3` (`id_creation`),
    INDEX `bot_event_FI_4` (`id_modification`),
    CONSTRAINT `bot_event_FK_1`
        FOREIGN KEY (`id_grid_run`)
        REFERENCES `grid_run` (`id_grid_run`)
        ON DELETE CASCADE,
    CONSTRAINT `bot_event_FK_2`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `bot_event_FK_3`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `bot_event_FK_4`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Event';

-- ---------------------------------------------------------------------
-- bot_command
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `bot_command`;

CREATE TABLE `bot_command`
(
    `id_bot_command` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `id_grid_run` INTEGER(11) NOT NULL COMMENT 'Run',
    `command` TINYINT NOT NULL COMMENT 'Command',
    `cmd_status` TINYINT DEFAULT 0 NOT NULL COMMENT 'Status',
    `note` VARCHAR(255) COMMENT 'Note',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_bot_command`),
    INDEX `bot_command_FI_1` (`id_grid_run`),
    INDEX `bot_command_FI_2` (`id_group_creation`),
    INDEX `bot_command_FI_3` (`id_creation`),
    INDEX `bot_command_FI_4` (`id_modification`),
    CONSTRAINT `bot_command_FK_1`
        FOREIGN KEY (`id_grid_run`)
        REFERENCES `grid_run` (`id_grid_run`)
        ON DELETE CASCADE,
    CONSTRAINT `bot_command_FK_2`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `bot_command_FK_3`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `bot_command_FK_4`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Command';

-- ---------------------------------------------------------------------
-- sim_wallet
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `sim_wallet`;

CREATE TABLE `sim_wallet`
(
    `id_sim_wallet` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `asset` VARCHAR(10) NOT NULL COMMENT 'Asset',
    `qty` DECIMAL(18, 8) DEFAULT 0 NOT NULL COMMENT 'Quantity',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_sim_wallet`),
    UNIQUE INDEX `sim_wallet_U_1` (`asset`),
    INDEX `sim_wallet_FI_1` (`id_group_creation`),
    INDEX `sim_wallet_FI_2` (`id_creation`),
    INDEX `sim_wallet_FI_3` (`id_modification`),
    CONSTRAINT `sim_wallet_FK_1`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `sim_wallet_FK_2`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `sim_wallet_FK_3`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Paper Wallet';

-- ---------------------------------------------------------------------
-- market_summary
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `market_summary`;

CREATE TABLE `market_summary`
(
    `id_market_summary` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `symbol` VARCHAR(20) NOT NULL COMMENT 'Symbol',
    `tf` VARCHAR(5) NOT NULL COMMENT 'Timeframe',
    `price` DECIMAL(18, 8) COMMENT 'Price',
    `ema20` DECIMAL(18, 8) COMMENT 'EMA20',
    `ema50` DECIMAL(18, 8) COMMENT 'EMA50',
    `ema200` DECIMAL(18, 8) COMMENT 'EMA200',
    `rsi14` DECIMAL(9, 4) COMMENT 'RSI14',
    `atr14` DECIMAL(18, 8) COMMENT 'ATR14',
    `atr_pct` DECIMAL(9, 4) COMMENT 'ATR %',
    `trend` TINYINT DEFAULT 2 COMMENT 'Trend',
    `swing_high` DECIMAL(18, 8) COMMENT 'Swing high',
    `swing_low` DECIMAL(18, 8) COMMENT 'Swing low',
    `candles_used` INTEGER(10) DEFAULT 0 COMMENT 'Candles',
    `recent_candles` TEXT(1023) COMMENT 'Candles JSON',
    `funding_rate` DECIMAL(12, 8) COMMENT 'Funding rate',
    `depth_imbalance` DECIMAL(9, 4) COMMENT 'Depth imbalance',
    `depth_imbalance_avg` DECIMAL(9, 4) COMMENT 'Depth imbalance (smoothed)',
    `adx14` DECIMAL(9, 4) COMMENT 'ADX14',
    `atr_pct_rank` DECIMAL(9, 4) COMMENT 'ATR% percentile',
    `taker_buy_ratio` DECIMAL(9, 4) COMMENT 'Taker buy ratio',
    `vol_zscore` DECIMAL(9, 4) COMMENT 'Volume z-score',
    `er20` DECIMAL(9, 4) COMMENT 'Efficiency ratio',
    `chop14` DECIMAL(9, 4) COMMENT 'Choppiness',
    `funding_pct` DECIMAL(9, 4) COMMENT 'Funding 30d percentile',
    `computed_at` DATETIME COMMENT 'Computed at',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_market_summary`),
    UNIQUE INDEX `market_summary_U_1` (`symbol`, `tf`),
    INDEX `market_summary_FI_1` (`id_group_creation`),
    INDEX `market_summary_FI_2` (`id_creation`),
    INDEX `market_summary_FI_3` (`id_modification`),
    CONSTRAINT `market_summary_FK_1`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `market_summary_FK_2`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `market_summary_FK_3`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Market Data';

-- ---------------------------------------------------------------------
-- market_regime
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `market_regime`;

CREATE TABLE `market_regime`
(
    `id_market_regime` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `symbol` VARCHAR(20) NOT NULL COMMENT 'Symbol',
    `tf` VARCHAR(5) NOT NULL COMMENT 'Timeframe',
    `price` DECIMAL(18, 8) COMMENT 'Price',
    `trend` TINYINT DEFAULT 2 COMMENT 'Trend',
    `rsi14` DECIMAL(9, 4) COMMENT 'RSI14',
    `atr_pct` DECIMAL(9, 4) COMMENT 'ATR %',
    `adx14` DECIMAL(9, 4) COMMENT 'ADX14',
    `atr_pct_rank` DECIMAL(9, 4) COMMENT 'ATR% percentile',
    `taker_buy_ratio` DECIMAL(9, 4) COMMENT 'Taker buy ratio',
    `vol_zscore` DECIMAL(9, 4) COMMENT 'Volume z-score',
    `er20` DECIMAL(9, 4) COMMENT 'Efficiency ratio',
    `chop14` DECIMAL(9, 4) COMMENT 'Choppiness',
    `funding_pct` DECIMAL(9, 4) COMMENT 'Funding 30d percentile',
    `funding_rate` DECIMAL(12, 8) COMMENT 'Funding rate',
    `depth_imbalance` DECIMAL(9, 4) COMMENT 'Depth imbalance',
    `depth_imbalance_avg` DECIMAL(9, 4) COMMENT 'Depth imbalance (smoothed)',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_market_regime`),
    INDEX `market_regime_FI_1` (`id_group_creation`),
    INDEX `market_regime_FI_2` (`id_creation`),
    INDEX `market_regime_FI_3` (`id_modification`),
    CONSTRAINT `market_regime_FK_1`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `market_regime_FK_2`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `market_regime_FK_3`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Regime History';

-- ---------------------------------------------------------------------
-- market_candle
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `market_candle`;

CREATE TABLE `market_candle`
(
    `id_market_candle` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `symbol` VARCHAR(20) NOT NULL COMMENT 'Symbol',
    `tf` TINYINT NOT NULL COMMENT 'Timeframe',
    `open_time` INTEGER(10) NOT NULL COMMENT 'Open time (epoch s)',
    `open` DECIMAL(18, 8) NOT NULL COMMENT 'Open',
    `high` DECIMAL(18, 8) NOT NULL COMMENT 'High',
    `low` DECIMAL(18, 8) NOT NULL COMMENT 'Low',
    `close` DECIMAL(18, 8) NOT NULL COMMENT 'Close',
    `volume` DECIMAL(24, 8) DEFAULT 0 COMMENT 'Volume',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_market_candle`),
    UNIQUE INDEX `market_candle_U_1` (`symbol`, `tf`, `open_time`),
    INDEX `market_candle_FI_1` (`id_group_creation`),
    INDEX `market_candle_FI_2` (`id_creation`),
    INDEX `market_candle_FI_3` (`id_modification`),
    CONSTRAINT `market_candle_FK_1`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `market_candle_FK_2`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `market_candle_FK_3`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Candles';

-- ---------------------------------------------------------------------
-- bot_decision
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `bot_decision`;

CREATE TABLE `bot_decision`
(
    `id_bot_decision` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `id_grid_run` INTEGER(11) NOT NULL COMMENT 'Run',
    `source` TINYINT DEFAULT 0 NOT NULL COMMENT 'Source',
    `p_low` DECIMAL(18, 8) NOT NULL COMMENT 'Range low',
    `p_high` DECIMAL(18, 8) NOT NULL COMMENT 'Range high',
    `n_levels` INTEGER(10) NOT NULL COMMENT 'Levels',
    `deploy_pct` INTEGER(10) COMMENT 'Deployed budget %',
    `reason` VARCHAR(500) COMMENT 'Reason',
    `price_at` DECIMAL(18, 8) COMMENT 'Price at decision',
    `realized_before` DECIMAL(18, 8) DEFAULT 0 COMMENT 'Realized before',
    `eval_status` TINYINT DEFAULT 0 NOT NULL COMMENT 'Eval',
    `eval_at` DATETIME COMMENT 'Scored at',
    `applied_at` DATETIME COMMENT 'Applied at',
    `cycles_delta` INTEGER(10) COMMENT 'Cycles after',
    `realized_delta` DECIMAL(18, 8) COMMENT 'P/L after',
    `price_move_pct` DECIMAL(9, 4) COMMENT 'Price move %',
    `verdict` TINYINT COMMENT 'Verdict',
    `counterfactual_delta` DECIMAL(18, 8) COMMENT 'vs no-change (sim)',
    `candidate_delta` TINYINT COMMENT 'vs candidate',
    `requested_json` TEXT(1023) COMMENT 'Requested (pre-gate)',
    `clamps_json` TEXT(1023) COMMENT 'Clamp trail',
    `brief_json` TEXT(1023) COMMENT 'Brief snapshot',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_bot_decision`),
    INDEX `bot_decision_FI_1` (`id_grid_run`),
    INDEX `bot_decision_FI_2` (`id_group_creation`),
    INDEX `bot_decision_FI_3` (`id_creation`),
    INDEX `bot_decision_FI_4` (`id_modification`),
    CONSTRAINT `bot_decision_FK_1`
        FOREIGN KEY (`id_grid_run`)
        REFERENCES `grid_run` (`id_grid_run`)
        ON DELETE CASCADE,
    CONSTRAINT `bot_decision_FK_2`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `bot_decision_FK_3`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `bot_decision_FK_4`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Refit Decision';

-- ---------------------------------------------------------------------
-- market_outlook
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `market_outlook`;

CREATE TABLE `market_outlook`
(
    `id_market_outlook` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `symbol` VARCHAR(20) NOT NULL COMMENT 'Symbol',
    `kind` VARCHAR(10) DEFAULT 'Change' NOT NULL COMMENT 'Kind',
    `verdict` VARCHAR(16) NOT NULL COMMENT 'Verdict',
    `prev_verdict` VARCHAR(16) COMMENT 'Previous',
    `price_at` DECIMAL(18, 8) NOT NULL COMMENT 'Price at call',
    `called_at` DATETIME NOT NULL COMMENT 'Called at',
    `detail` TEXT(1023) COMMENT 'Detail',
    `eval_status` VARCHAR(10) DEFAULT 'Pending' NOT NULL COMMENT 'Eval',
    `price_7d` DECIMAL(18, 8) COMMENT 'Price +7d',
    `price_30d` DECIMAL(18, 8) COMMENT 'Price +30d',
    `ret_7d` DECIMAL(9, 4) COMMENT 'Return 7d %',
    `ret_30d` DECIMAL(9, 4) COMMENT 'Return 30d %',
    `max_adverse_pct` DECIMAL(9, 4) COMMENT 'Max adverse 30d %',
    `hit_7d` TINYINT(10) COMMENT 'Hit 7d',
    `hit_30d` TINYINT(10) COMMENT 'Hit 30d',
    `scored_at` DATETIME COMMENT 'Scored at',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_market_outlook`),
    INDEX `market_outlook_FI_1` (`id_group_creation`),
    INDEX `market_outlook_FI_2` (`id_creation`),
    INDEX `market_outlook_FI_3` (`id_modification`),
    CONSTRAINT `market_outlook_FK_1`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `market_outlook_FK_2`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `market_outlook_FK_3`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Market Outlook';

-- ---------------------------------------------------------------------
-- market_outlook_state
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `market_outlook_state`;

CREATE TABLE `market_outlook_state`
(
    `id_market_outlook_state` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `symbol` VARCHAR(20) NOT NULL COMMENT 'Symbol',
    `verdict` VARCHAR(16) COMMENT 'Verdict',
    `verdict_since` DATETIME COMMENT 'Since',
    `price_at_verdict` DECIMAL(18, 8) COMMENT 'Price at verdict',
    `candidate` VARCHAR(16) COMMENT 'Candidate',
    `candidate_passes` INTEGER(10) DEFAULT 0 NOT NULL COMMENT 'Candidate passes',
    `last_raw` VARCHAR(16) COMMENT 'Last raw read',
    `last_pass_at` DATETIME COMMENT 'Last pass',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_market_outlook_state`),
    UNIQUE INDEX `market_outlook_state_U_1` (`symbol`),
    INDEX `market_outlook_state_FI_1` (`id_group_creation`),
    INDEX `market_outlook_state_FI_2` (`id_creation`),
    INDEX `market_outlook_state_FI_3` (`id_modification`),
    CONSTRAINT `market_outlook_state_FK_1`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `market_outlook_state_FK_2`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `market_outlook_state_FK_3`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Outlook State';

-- ---------------------------------------------------------------------
-- wallet_nav
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `wallet_nav`;

CREATE TABLE `wallet_nav`
(
    `id_wallet_nav` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `mode` TINYINT DEFAULT 0 NOT NULL COMMENT 'Mode',
    `equity_quote` DECIMAL(18, 8) NOT NULL COMMENT 'Equity (USDT)',
    `budget_quote` DECIMAL(18, 8) NOT NULL COMMENT 'Shared budget',
    `ref_symbol` VARCHAR(20) COMMENT 'HODL reference',
    `ref_price` DECIMAL(18, 8) COMMENT 'Reference price',
    `unpriced` VARCHAR(100) COMMENT 'Unpriced assets',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_wallet_nav`),
    INDEX `wallet_nav_FI_1` (`id_group_creation`),
    INDEX `wallet_nav_FI_2` (`id_creation`),
    INDEX `wallet_nav_FI_3` (`id_modification`),
    CONSTRAINT `wallet_nav_FK_1`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `wallet_nav_FK_2`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `wallet_nav_FK_3`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Wallet NAV';

-- ---------------------------------------------------------------------
-- authy_group
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `authy_group`;

CREATE TABLE `authy_group`
(
    `id_authy_group` INTEGER NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) COMMENT 'Name',
    `desc` VARCHAR(32) COMMENT 'Description',
    `default_group` TINYINT NOT NULL COMMENT 'Default',
    `admin` TINYINT NOT NULL COMMENT 'Admin',
    `rights_all` VARCHAR(1023) COMMENT 'Rights',
    `rights_owner` VARCHAR(1023) COMMENT 'Rights owner',
    `rights_group` VARCHAR(1023) COMMENT 'Rights group',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_authy_group`),
    INDEX `authy_group_FI_1` (`id_group_creation`),
    INDEX `authy_group_FI_2` (`id_creation`),
    INDEX `authy_group_FI_3` (`id_modification`),
    CONSTRAINT `authy_group_FK_1`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `authy_group_FK_2`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `authy_group_FK_3`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Group';

-- ---------------------------------------------------------------------
-- authy_group_x
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `authy_group_x`;

CREATE TABLE `authy_group_x`
(
    `id_authy` INTEGER NOT NULL,
    `id_authy_group` INTEGER NOT NULL COMMENT 'Group',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_authy`,`id_authy_group`),
    INDEX `authy_group_x_FI_1` (`id_authy_group`),
    INDEX `authy_group_x_FI_3` (`id_group_creation`),
    INDEX `authy_group_x_FI_4` (`id_creation`),
    INDEX `authy_group_x_FI_5` (`id_modification`),
    CONSTRAINT `authy_group_x_FK_1`
        FOREIGN KEY (`id_authy_group`)
        REFERENCES `authy_group` (`id_authy_group`)
        ON DELETE CASCADE,
    CONSTRAINT `authy_group_x_FK_2`
        FOREIGN KEY (`id_authy`)
        REFERENCES `authy` (`id_authy`)
        ON UPDATE CASCADE,
    CONSTRAINT `authy_group_x_FK_3`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `authy_group_x_FK_4`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `authy_group_x_FK_5`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Group';

-- ---------------------------------------------------------------------
-- authy_log
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `authy_log`;

CREATE TABLE `authy_log`
(
    `id_authy_log` INTEGER NOT NULL AUTO_INCREMENT,
    `id_authy` INTEGER,
    `timestamp` DATETIME COMMENT 'Date',
    `login` VARCHAR(50) NOT NULL COMMENT 'Username',
    `userid` INTEGER,
    `result` VARCHAR(100) NOT NULL,
    `event` VARCHAR(64) COMMENT 'Event',
    `ip` VARCHAR(16) NOT NULL COMMENT 'Ip',
    `count` INTEGER COMMENT 'Count',
    PRIMARY KEY (`id_authy_log`),
    INDEX `authy_log_FI_1` (`id_authy`),
    CONSTRAINT `authy_log_FK_1`
        FOREIGN KEY (`id_authy`)
        REFERENCES `authy` (`id_authy`)
        ON UPDATE CASCADE
) ENGINE=InnoDB COMMENT='Login log';

-- ---------------------------------------------------------------------
-- message
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `message`;

CREATE TABLE `message`
(
    `id_message` INTEGER NOT NULL AUTO_INCREMENT,
    `label` VARCHAR(100) NOT NULL COMMENT 'Label',
    PRIMARY KEY (`id_message`)
) ENGINE=InnoDB COMMENT='Message';

-- ---------------------------------------------------------------------
-- config
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `config`;

CREATE TABLE `config`
(
    `id_config` INTEGER NOT NULL AUTO_INCREMENT,
    `category` TINYINT NOT NULL COMMENT 'Category',
    `config` VARCHAR(100) NOT NULL COMMENT 'Setting',
    `value` TEXT(400) COMMENT 'Value',
    `system` TINYINT DEFAULT 0,
    `description` VARCHAR(500) COMMENT 'Description',
    `type` VARCHAR(35),
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_config`),
    INDEX `config_FI_1` (`id_group_creation`),
    INDEX `config_FI_2` (`id_creation`),
    INDEX `config_FI_3` (`id_modification`),
    CONSTRAINT `config_FK_1`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `config_FK_2`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `config_FK_3`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Setting';

-- ---------------------------------------------------------------------
-- api_rbac
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `api_rbac`;

CREATE TABLE `api_rbac`
(
    `id_api_rbac` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `date_creation` DATE NOT NULL COMMENT 'Date',
    `description` TEXT(1023) COMMENT 'Description',
    `model` VARCHAR(200) NOT NULL COMMENT 'Model',
    `action` VARCHAR(200) COMMENT 'Action',
    `body` TEXT(1023) COMMENT 'Body',
    `query` TEXT(1023) COMMENT 'Query',
    `method` TINYINT DEFAULT 0 NOT NULL COMMENT 'Method',
    `scope` TINYINT DEFAULT 0 NOT NULL COMMENT 'Scope',
    `rule` TINYINT DEFAULT 1 NOT NULL COMMENT 'Rule',
    `count` INTEGER DEFAULT 0 NOT NULL COMMENT 'Used count',
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_api_rbac`),
    INDEX `api_rbac_FI_1` (`id_group_creation`),
    INDEX `api_rbac_FI_2` (`id_creation`),
    INDEX `api_rbac_FI_3` (`id_modification`),
    CONSTRAINT `api_rbac_FK_1`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `api_rbac_FK_2`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `api_rbac_FK_3`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='API ACL';

-- ---------------------------------------------------------------------
-- api_log
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `api_log`;

CREATE TABLE `api_log`
(
    `id_api_log` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `id_api_rbac` INTEGER NOT NULL COMMENT 'Rule',
    `id_authy` INTEGER COMMENT 'User',
    `time` DATETIME NOT NULL COMMENT 'Time',
    `raw_parameters` TEXT(1023) COMMENT 'Raw parameters',
    `count` INTEGER DEFAULT 1 NOT NULL COMMENT 'Count',
    PRIMARY KEY (`id_api_log`),
    INDEX `api_log_FI_1` (`id_api_rbac`),
    INDEX `api_log_FI_2` (`id_authy`),
    CONSTRAINT `api_log_FK_1`
        FOREIGN KEY (`id_api_rbac`)
        REFERENCES `api_rbac` (`id_api_rbac`)
        ON DELETE CASCADE,
    CONSTRAINT `api_log_FK_2`
        FOREIGN KEY (`id_authy`)
        REFERENCES `authy` (`id_authy`)
        ON DELETE CASCADE
) ENGINE=InnoDB COMMENT='API log';

-- ---------------------------------------------------------------------
-- template
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `template`;

CREATE TABLE `template`
(
    `id_template` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL COMMENT 'Name',
    `subject` VARCHAR(200) COMMENT 'Subject',
    `color_1` VARCHAR(10) COMMENT 'Color 1',
    `color_2` VARCHAR(10) COMMENT 'Color 2',
    `color_3` VARCHAR(10) COMMENT 'Color 3',
    `status` TINYINT DEFAULT 0 NOT NULL COMMENT 'Status',
    `body` TEXT(1023) COMMENT 'Body',
    `footer` TEXT(1023) COMMENT 'Footer',
    `lang` TINYINT DEFAULT 0 COMMENT 'Language',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_template`),
    INDEX `template_FI_1` (`id_group_creation`),
    INDEX `template_FI_2` (`id_creation`),
    INDEX `template_FI_3` (`id_modification`),
    CONSTRAINT `template_FK_1`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `template_FK_2`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `template_FK_3`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Template';

-- ---------------------------------------------------------------------
-- template_file
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `template_file`;

CREATE TABLE `template_file`
(
    `id_template_file` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `id_template` INTEGER NOT NULL,
    `name` VARCHAR(100) COMMENT 'Name',
    `file` VARCHAR(500) COMMENT 'File',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_template_file`),
    INDEX `template_file_FI_1` (`id_template`),
    INDEX `template_file_FI_2` (`id_group_creation`),
    INDEX `template_file_FI_3` (`id_creation`),
    INDEX `template_file_FI_4` (`id_modification`),
    CONSTRAINT `template_file_FK_1`
        FOREIGN KEY (`id_template`)
        REFERENCES `template` (`id_template`)
        ON DELETE CASCADE,
    CONSTRAINT `template_file_FK_2`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `template_file_FK_3`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `template_file_FK_4`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='File';

-- ---------------------------------------------------------------------
-- authy_refresh_token
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `authy_refresh_token`;

CREATE TABLE `authy_refresh_token`
(
    `id_authy_refresh_token` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `id_authy` INTEGER(11) NOT NULL,
    `family_id` VARCHAR(64) NOT NULL,
    `token_hash` VARCHAR(64) NOT NULL,
    `expires` INTEGER NOT NULL,
    `family_expires` INTEGER NOT NULL,
    `revoked` TINYINT DEFAULT 0,
    `created_at` DATETIME,
    `last_used_at` DATETIME,
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_authy_refresh_token`),
    UNIQUE INDEX `authy_refresh_token_token_hash_unique` (`token_hash`),
    INDEX `authy_refresh_token_family_id_index` (`family_id`),
    INDEX `authy_refresh_token_FI_1` (`id_authy`),
    INDEX `authy_refresh_token_FI_2` (`id_group_creation`),
    INDEX `authy_refresh_token_FI_3` (`id_creation`),
    INDEX `authy_refresh_token_FI_4` (`id_modification`),
    CONSTRAINT `authy_refresh_token_FK_1`
        FOREIGN KEY (`id_authy`)
        REFERENCES `authy` (`id_authy`)
        ON DELETE CASCADE,
    CONSTRAINT `authy_refresh_token_FK_2`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `authy_refresh_token_FK_3`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `authy_refresh_token_FK_4`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- grid_run_audit
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `grid_run_audit`;

CREATE TABLE `grid_run_audit`
(
    `id_grid_run_audit` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `id_grid_run` INTEGER NOT NULL COMMENT 'Record',
    `field` VARCHAR(64) NOT NULL COMMENT 'Field',
    `value_from` TEXT COMMENT 'From',
    `value_to` TEXT COMMENT 'To',
    `actor` VARCHAR(128) COMMENT 'Actor',
    `source` TINYINT DEFAULT 0 COMMENT 'Source',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_grid_run_audit`),
    INDEX `grid_run_audit_FI_1` (`id_grid_run`),
    INDEX `grid_run_audit_FI_2` (`id_group_creation`),
    INDEX `grid_run_audit_FI_3` (`id_creation`),
    INDEX `grid_run_audit_FI_4` (`id_modification`),
    CONSTRAINT `grid_run_audit_FK_1`
        FOREIGN KEY (`id_grid_run`)
        REFERENCES `grid_run` (`id_grid_run`)
        ON DELETE CASCADE,
    CONSTRAINT `grid_run_audit_FK_2`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `grid_run_audit_FK_3`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `grid_run_audit_FK_4`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Change history';

-- ---------------------------------------------------------------------
-- country
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `country`;

CREATE TABLE `country`
(
    `id_country` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) COMMENT 'Name',
    `code` VARCHAR(3) COMMENT 'Code',
    `timezone` VARCHAR(20) COMMENT 'Timezone',
    `timezone_code` VARCHAR(50) COMMENT 'Timezone code',
    `priority` INTEGER(10) COMMENT 'Priority',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_country`),
    INDEX `country_FI_1` (`id_group_creation`),
    INDEX `country_FI_2` (`id_creation`),
    INDEX `country_FI_3` (`id_modification`),
    CONSTRAINT `country_FK_1`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `country_FK_2`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `country_FK_3`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB COMMENT='Country';

-- ---------------------------------------------------------------------
-- oauth_client
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `oauth_client`;

CREATE TABLE `oauth_client`
(
    `id_oauth_client` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `client_id` VARCHAR(80) NOT NULL,
    `client_secret_hash` VARCHAR(255),
    `name` VARCHAR(191),
    `redirect_uris` TEXT,
    `grant_types` VARCHAR(191),
    `scopes` VARCHAR(191),
    `is_confidential` TINYINT DEFAULT 0,
    `created_at` DATETIME,
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_oauth_client`),
    UNIQUE INDEX `oauth_client_client_id_unique` (`client_id`),
    INDEX `oauth_client_FI_1` (`id_group_creation`),
    INDEX `oauth_client_FI_2` (`id_creation`),
    INDEX `oauth_client_FI_3` (`id_modification`),
    CONSTRAINT `oauth_client_FK_1`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `oauth_client_FK_2`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `oauth_client_FK_3`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- oauth_auth_code
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `oauth_auth_code`;

CREATE TABLE `oauth_auth_code`
(
    `id_oauth_auth_code` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `code_id` VARCHAR(128) NOT NULL,
    `id_authy` INTEGER(11),
    `client_id` VARCHAR(80),
    `scopes` VARCHAR(191),
    `expires` INTEGER,
    `revoked` TINYINT DEFAULT 0,
    `created_at` DATETIME,
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_oauth_auth_code`),
    UNIQUE INDEX `oauth_auth_code_code_id_unique` (`code_id`),
    INDEX `oauth_auth_code_FI_1` (`id_authy`),
    INDEX `oauth_auth_code_FI_2` (`id_group_creation`),
    INDEX `oauth_auth_code_FI_3` (`id_creation`),
    INDEX `oauth_auth_code_FI_4` (`id_modification`),
    CONSTRAINT `oauth_auth_code_FK_1`
        FOREIGN KEY (`id_authy`)
        REFERENCES `authy` (`id_authy`)
        ON DELETE CASCADE,
    CONSTRAINT `oauth_auth_code_FK_2`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `oauth_auth_code_FK_3`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `oauth_auth_code_FK_4`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- oauth_access_token
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `oauth_access_token`;

CREATE TABLE `oauth_access_token`
(
    `id_oauth_access_token` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `token_id` VARCHAR(128) NOT NULL,
    `id_authy` INTEGER(11),
    `client_id` VARCHAR(80),
    `scopes` VARCHAR(191),
    `expires` INTEGER,
    `revoked` TINYINT DEFAULT 0,
    `created_at` DATETIME,
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_oauth_access_token`),
    UNIQUE INDEX `oauth_access_token_token_id_unique` (`token_id`),
    INDEX `oauth_access_token_FI_1` (`id_authy`),
    INDEX `oauth_access_token_FI_2` (`id_group_creation`),
    INDEX `oauth_access_token_FI_3` (`id_creation`),
    INDEX `oauth_access_token_FI_4` (`id_modification`),
    CONSTRAINT `oauth_access_token_FK_1`
        FOREIGN KEY (`id_authy`)
        REFERENCES `authy` (`id_authy`)
        ON DELETE CASCADE,
    CONSTRAINT `oauth_access_token_FK_2`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `oauth_access_token_FK_3`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `oauth_access_token_FK_4`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- oauth_refresh_token
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `oauth_refresh_token`;

CREATE TABLE `oauth_refresh_token`
(
    `id_oauth_refresh_token` INTEGER(11) NOT NULL AUTO_INCREMENT,
    `token_id` VARCHAR(128) NOT NULL,
    `access_token_id` VARCHAR(128),
    `id_authy` INTEGER(11),
    `client_id` VARCHAR(80),
    `expires` INTEGER,
    `revoked` TINYINT DEFAULT 0,
    `created_at` DATETIME,
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_oauth_refresh_token`),
    UNIQUE INDEX `oauth_refresh_token_token_id_unique` (`token_id`),
    INDEX `oauth_refresh_token_FI_1` (`id_authy`),
    INDEX `oauth_refresh_token_FI_2` (`id_group_creation`),
    INDEX `oauth_refresh_token_FI_3` (`id_creation`),
    INDEX `oauth_refresh_token_FI_4` (`id_modification`),
    CONSTRAINT `oauth_refresh_token_FK_1`
        FOREIGN KEY (`id_authy`)
        REFERENCES `authy` (`id_authy`)
        ON DELETE CASCADE,
    CONSTRAINT `oauth_refresh_token_FK_2`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `oauth_refresh_token_FK_3`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `oauth_refresh_token_FK_4`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- message_i18n
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `message_i18n`;

CREATE TABLE `message_i18n`
(
    `id_message` INTEGER NOT NULL,
    `locale` VARCHAR(5) DEFAULT 'en_US' NOT NULL,
    `text` TEXT(200) COMMENT 'Texte',
    `date_creation` DATETIME,
    `date_modification` DATETIME,
    `id_group_creation` INTEGER,
    `id_creation` INTEGER,
    `id_modification` INTEGER,
    PRIMARY KEY (`id_message`,`locale`),
    INDEX `message_i18n_FI_2` (`id_group_creation`),
    INDEX `message_i18n_FI_3` (`id_creation`),
    INDEX `message_i18n_FI_4` (`id_modification`),
    CONSTRAINT `message_i18n_FK_1`
        FOREIGN KEY (`id_message`)
        REFERENCES `message` (`id_message`)
        ON DELETE CASCADE,
    CONSTRAINT `message_i18n_FK_2`
        FOREIGN KEY (`id_group_creation`)
        REFERENCES `authy_group` (`id_authy_group`),
    CONSTRAINT `message_i18n_FK_3`
        FOREIGN KEY (`id_creation`)
        REFERENCES `authy` (`id_authy`),
    CONSTRAINT `message_i18n_FK_4`
        FOREIGN KEY (`id_modification`)
        REFERENCES `authy` (`id_authy`)
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
