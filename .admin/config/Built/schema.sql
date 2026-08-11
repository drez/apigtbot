
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
    `fee_pct` DECIMAL(9, 6) DEFAULT 0.001 NOT NULL COMMENT 'Fee per side',
    `max_position_quote` DECIMAL(18, 8) NOT NULL COMMENT 'Max position (USDT)',
    `max_order_quote` DECIMAL(18, 8) NOT NULL COMMENT 'Max per-order (USDT)',
    `daily_loss_limit_quote` DECIMAL(18, 8) NOT NULL COMMENT 'Daily loss limit',
    `max_unrealized_loss_quote` DECIMAL(18, 8) COMMENT 'Max unrealized loss',
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
