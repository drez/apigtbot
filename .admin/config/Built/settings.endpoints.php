<?php

namespace App;


$table['authy'] = [
    'id_authy' => [
        'type' => 'INTEGER',
    ],
    'validation_key' => [
        'type' => 'VARCHAR',
    ],
    'username' => [
        'type' => 'VARCHAR',
        'description' => 'Username',
    ],
    'fullname' => [
        'type' => 'VARCHAR',
        'description' => 'Fullname',
    ],
    'email' => [
        'type' => 'VARCHAR',
        'description' => 'Email',
    ],
    'passwd_hash' => [
        'type' => 'VARCHAR',
        'description' => 'Password',
    ],
    'expire' => [
        'type' => 'DATE',
        'description' => 'Expiration',
    ],
    'deactivate' => [
        'type' => 'ENUM',
        'description' => 'Deactivated',
        'valueSet' => [
            'Yes',
            'No',
        ],
    ],
    'language' => [
        'type' => 'ENUM',
        'description' => 'Language',
        'valueSet' => [
            'en_US',
            'fr_CA',
        ],
    ],
    'theme' => [
        'type' => 'ENUM',
        'description' => 'Theme',
        'valueSet' => [
            'mint',
            'ink',
            'indigo',
            'terracotta',
            'graphite',
            'slate',
            'dusk',
        ],
    ],
    'google_sub' => [
        'type' => 'VARCHAR',
        'description' => 'Google sub',
    ],
    'google_email' => [
        'type' => 'VARCHAR',
        'description' => 'Google email',
    ],
    'reset_token_hash' => [
        'type' => 'VARCHAR',
        'description' => 'Reset token',
    ],
    'reset_token_expires' => [
        'type' => 'INTEGER',
        'description' => 'Reset expires',
    ],
    'id_tenant' => [
        'type' => 'INTEGER',
        'description' => 'Tenant',
    ],
    'location_address' => [
        'type' => 'VARCHAR',
        'description' => 'Location Address',
    ],
    'location_lat' => [
        'type' => 'DECIMAL',
    ],
    'location_lng' => [
        'type' => 'DECIMAL',
    ],
    'is_root' => [
        'type' => 'ENUM',
        'description' => 'Root',
        'valueSet' => [
            'Yes',
            'No',
        ],
    ],
    'id_authy_group' => [
        'type' => 'INTEGER',
        'description' => 'Primary group',
    ],
    'is_system' => [
        'type' => 'ENUM',
    ],
    'rights_all' => [
        'type' => 'LONGVARCHAR',
        'description' => 'Rights',
    ],
    'rights_group' => [
        'type' => 'LONGVARCHAR',
        'description' => 'Rights (group records)',
    ],
    'rights_owner' => [
        'type' => 'LONGVARCHAR',
        'description' => 'Rights (own records)',
    ],
    'onglet' => [
        'type' => 'LONGVARCHAR',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['authy'] = [
    'select' => $table['authy'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['push_device'] = [
    'id_push_device' => [
        'type' => 'INTEGER',
    ],
    'id_authy' => [
        'type' => 'INTEGER',
        'description' => 'User',
    ],
    'token' => [
        'type' => 'VARCHAR',
        'description' => 'Token',
    ],
    'platform' => [
        'type' => 'ENUM',
        'description' => 'Platform',
        'valueSet' => [
            'ios',
            'android',
            'web',
        ],
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['push_device'] = [
    'select' => $table['push_device'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['country'] = [
    'id_country' => [
        'type' => 'INTEGER',
    ],
    'name' => [
        'type' => 'VARCHAR',
        'description' => 'Name',
    ],
    'code' => [
        'type' => 'VARCHAR',
        'description' => 'Code',
    ],
    'timezone' => [
        'type' => 'VARCHAR',
        'description' => 'Timezone',
    ],
    'timezone_code' => [
        'type' => 'VARCHAR',
        'description' => 'Timezone code',
    ],
    'priority' => [
        'type' => 'INTEGER',
        'description' => 'Priority',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['country'] = [
    'select' => $table['country'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['grid_run'] = [
    'id_grid_run' => [
        'type' => 'INTEGER',
    ],
    'label' => [
        'type' => 'VARCHAR',
        'description' => 'Label',
    ],
    'symbol' => [
        'type' => 'VARCHAR',
        'description' => 'Symbol',
    ],
    'status' => [
        'type' => 'ENUM',
        'description' => 'Status',
        'valueSet' => [
            'Draft',
            'DryRun',
            'Testnet',
            'Live',
            'Halted',
            'Done',
        ],
    ],
    'kill_switch' => [
        'type' => 'BOOLEAN',
        'description' => 'Kill switch',
    ],
    'profile' => [
        'type' => 'ENUM',
        'description' => 'Risk profile',
        'valueSet' => [
            'NoLoss',
            'Cautious',
            'Balanced',
            'Aggressive',
            'Max',
        ],
    ],
    'algo' => [
        'type' => 'ENUM',
        'description' => 'Algorithm',
        'valueSet' => [
            'Grid',
            'Trend',
        ],
    ],
    'simulated' => [
        'type' => 'BOOLEAN',
        'description' => 'Simulated',
    ],
    'p_low' => [
        'type' => 'DECIMAL',
        'description' => 'Range low',
    ],
    'p_high' => [
        'type' => 'DECIMAL',
        'description' => 'Range high',
    ],
    'n_levels' => [
        'type' => 'INTEGER',
        'description' => 'Grid lines',
    ],
    'spacing' => [
        'type' => 'ENUM',
        'description' => 'Spacing',
        'valueSet' => [
            'Geometric',
            'Arithmetic',
        ],
    ],
    'allocation' => [
        'type' => 'ENUM',
        'description' => 'Allocation',
        'valueSet' => [
            'EqualQuote',
            'EqualBase',
            'BottomWeighted',
        ],
    ],
    'budget_quote' => [
        'type' => 'DECIMAL',
        'description' => 'Budget (USDT)',
    ],
    'deploy_pct' => [
        'type' => 'INTEGER',
        'description' => 'Deployed budget %',
    ],
    'fee_pct' => [
        'type' => 'DECIMAL',
        'description' => 'Fee per side',
    ],
    'max_position_quote' => [
        'type' => 'DECIMAL',
        'description' => 'Max position (USDT)',
    ],
    'max_order_quote' => [
        'type' => 'DECIMAL',
        'description' => 'Max per-order (USDT)',
    ],
    'daily_loss_limit_quote' => [
        'type' => 'DECIMAL',
        'description' => 'Daily loss limit',
    ],
    'max_unrealized_loss_quote' => [
        'type' => 'DECIMAL',
        'description' => 'Max unrealized loss',
    ],
    'breakout_buffer_pct' => [
        'type' => 'DECIMAL',
        'description' => 'Breakout buffer',
    ],
    'breakout_policy' => [
        'type' => 'ENUM',
        'description' => 'On breakout',
        'valueSet' => [
            'HaltAndHold',
            'Flatten',
        ],
    ],
    'max_open_orders' => [
        'type' => 'INTEGER',
        'description' => 'Max open orders',
    ],
    'max_buy_levels_below' => [
        'type' => 'INTEGER',
        'description' => 'Active buy levels below price (0 = all)',
    ],
    'trend_tf' => [
        'type' => 'ENUM',
        'description' => 'Signal timeframe',
        'valueSet' => [
            '1h',
            '4h',
        ],
    ],
    'donchian_period' => [
        'type' => 'INTEGER',
        'description' => 'Breakout period',
    ],
    'trend_ema_fast' => [
        'type' => 'INTEGER',
        'description' => 'Trend EMA fast',
    ],
    'trend_ema_slow' => [
        'type' => 'INTEGER',
        'description' => 'Trend EMA slow',
    ],
    'atr_period' => [
        'type' => 'INTEGER',
        'description' => 'ATR period',
    ],
    'atr_stop_mult' => [
        'type' => 'DECIMAL',
        'description' => 'Trail stop x ATR',
    ],
    'atr_initial_mult' => [
        'type' => 'DECIMAL',
        'description' => 'Initial stop x ATR',
    ],
    'reentry_cooldown' => [
        'type' => 'INTEGER',
        'description' => 'Re-entry cooldown (bars)',
    ],
    'engine_state' => [
        'type' => 'LONGVARCHAR',
        'description' => 'Engine state (daemon-managed)',
    ],
    'last_tick_at' => [
        'type' => 'TIMESTAMP',
        'description' => 'Last tick',
    ],
    'last_price' => [
        'type' => 'DECIMAL',
        'description' => 'Last price',
    ],
    'bal_base' => [
        'type' => 'DECIMAL',
        'description' => 'Wallet base',
    ],
    'bal_quote' => [
        'type' => 'DECIMAL',
        'description' => 'Wallet quote',
    ],
    'sim_bal_base' => [
        'type' => 'DECIMAL',
        'description' => 'Paper wallet base (daemon-managed)',
    ],
    'sim_bal_quote' => [
        'type' => 'DECIMAL',
        'description' => 'Paper wallet quote (daemon-managed)',
    ],
    'run_uid' => [
        'type' => 'VARCHAR',
        'description' => 'Run UID',
    ],
    'applied_geometry' => [
        'type' => 'LONGVARCHAR',
        'description' => 'Applied geometry (daemon-managed)',
    ],
    'ledger_reset_at' => [
        'type' => 'TIMESTAMP',
        'description' => 'Ledger rebased at (daemon-managed)',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['grid_run'] = [
    'select' => $table['grid_run'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['bot_order'] = [
    'id_bot_order' => [
        'type' => 'INTEGER',
    ],
    'id_grid_run' => [
        'type' => 'INTEGER',
        'description' => 'Run',
    ],
    'client_order_id' => [
        'type' => 'VARCHAR',
        'description' => 'Client order id',
    ],
    'exchange_order_id' => [
        'type' => 'VARCHAR',
        'description' => 'Exchange id',
    ],
    'level_idx' => [
        'type' => 'INTEGER',
        'description' => 'Level',
    ],
    'side' => [
        'type' => 'ENUM',
        'description' => 'Side',
        'valueSet' => [
            'Buy',
            'Sell',
        ],
    ],
    'state' => [
        'type' => 'ENUM',
        'description' => 'State',
        'valueSet' => [
            'Intended',
            'Vetoed',
            'BUY_OPEN',
            'SELL_OPEN',
            'Filled',
            'PartFilled',
            'Canceled',
            'Rejected',
            'Lost',
        ],
    ],
    'price' => [
        'type' => 'DECIMAL',
        'description' => 'Price',
    ],
    'qty' => [
        'type' => 'DECIMAL',
        'description' => 'Qty',
    ],
    'filled_qty' => [
        'type' => 'DECIMAL',
        'description' => 'Filled qty',
    ],
    'fee_paid' => [
        'type' => 'DECIMAL',
        'description' => 'Fee paid',
    ],
    'fee_asset' => [
        'type' => 'VARCHAR',
        'description' => 'Fee asset',
    ],
    'is_legacy' => [
        'type' => 'BOOLEAN',
        'description' => 'Legacy exit',
    ],
    'legacy_buy_price' => [
        'type' => 'DECIMAL',
        'description' => 'Legacy buy price (daemon-managed)',
    ],
    'legacy_buy_fee' => [
        'type' => 'DECIMAL',
        'description' => 'Legacy buy fee (daemon-managed)',
    ],
    'simulated' => [
        'type' => 'BOOLEAN',
        'description' => 'Simulated',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['bot_order'] = [
    'select' => $table['bot_order'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['trade_cycle'] = [
    'id_trade_cycle' => [
        'type' => 'INTEGER',
    ],
    'id_grid_run' => [
        'type' => 'INTEGER',
        'description' => 'Run',
    ],
    'level_idx' => [
        'type' => 'INTEGER',
        'description' => 'Level',
    ],
    'buy_price' => [
        'type' => 'DECIMAL',
        'description' => 'Buy price',
    ],
    'sell_price' => [
        'type' => 'DECIMAL',
        'description' => 'Sell price',
    ],
    'qty' => [
        'type' => 'DECIMAL',
        'description' => 'Qty',
    ],
    'realized_pnl' => [
        'type' => 'DECIMAL',
        'description' => 'Realized PnL',
    ],
    'fees_total' => [
        'type' => 'DECIMAL',
        'description' => 'Fees',
    ],
    'simulated' => [
        'type' => 'BOOLEAN',
        'description' => 'Simulated',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['trade_cycle'] = [
    'select' => $table['trade_cycle'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['bot_event'] = [
    'id_bot_event' => [
        'type' => 'INTEGER',
    ],
    'id_grid_run' => [
        'type' => 'INTEGER',
        'description' => 'Run',
    ],
    'level' => [
        'type' => 'ENUM',
        'description' => 'Level',
        'valueSet' => [
            'Info',
            'Warn',
            'Error',
            'Alert',
        ],
    ],
    'kind' => [
        'type' => 'VARCHAR',
        'description' => 'Kind',
    ],
    'message' => [
        'type' => 'VARCHAR',
        'description' => 'Message',
    ],
    'payload' => [
        'type' => 'LONGVARCHAR',
        'description' => 'Payload',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['bot_event'] = [
    'select' => $table['bot_event'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['bot_command'] = [
    'id_bot_command' => [
        'type' => 'INTEGER',
    ],
    'id_grid_run' => [
        'type' => 'INTEGER',
        'description' => 'Run',
    ],
    'command' => [
        'type' => 'ENUM',
        'description' => 'Command',
        'valueSet' => [
            'Start',
            'Pause',
            'Resume',
            'Kill',
            'Flatten',
            'CancelBuys',
            'Reload',
        ],
    ],
    'cmd_status' => [
        'type' => 'ENUM',
        'description' => 'Status',
        'valueSet' => [
            'Pending',
            'Acked',
            'Done',
            'Failed',
        ],
    ],
    'note' => [
        'type' => 'VARCHAR',
        'description' => 'Note',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['bot_command'] = [
    'select' => $table['bot_command'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['sim_wallet'] = [
    'id_sim_wallet' => [
        'type' => 'INTEGER',
    ],
    'asset' => [
        'type' => 'VARCHAR',
        'description' => 'Asset',
    ],
    'qty' => [
        'type' => 'DECIMAL',
        'description' => 'Quantity',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['sim_wallet'] = [
    'select' => $table['sim_wallet'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['market_summary'] = [
    'id_market_summary' => [
        'type' => 'INTEGER',
    ],
    'symbol' => [
        'type' => 'VARCHAR',
        'description' => 'Symbol',
    ],
    'tf' => [
        'type' => 'VARCHAR',
        'description' => 'Timeframe',
    ],
    'price' => [
        'type' => 'DECIMAL',
        'description' => 'Price',
    ],
    'ema20' => [
        'type' => 'DECIMAL',
        'description' => 'EMA20',
    ],
    'ema50' => [
        'type' => 'DECIMAL',
        'description' => 'EMA50',
    ],
    'ema200' => [
        'type' => 'DECIMAL',
        'description' => 'EMA200',
    ],
    'rsi14' => [
        'type' => 'DECIMAL',
        'description' => 'RSI14',
    ],
    'atr14' => [
        'type' => 'DECIMAL',
        'description' => 'ATR14',
    ],
    'atr_pct' => [
        'type' => 'DECIMAL',
        'description' => 'ATR %',
    ],
    'trend' => [
        'type' => 'ENUM',
        'description' => 'Trend',
        'valueSet' => [
            'strong_up',
            'up',
            'sideways',
            'down',
            'strong_down',
        ],
    ],
    'swing_high' => [
        'type' => 'DECIMAL',
        'description' => 'Swing high',
    ],
    'swing_low' => [
        'type' => 'DECIMAL',
        'description' => 'Swing low',
    ],
    'candles_used' => [
        'type' => 'INTEGER',
        'description' => 'Candles',
    ],
    'recent_candles' => [
        'type' => 'LONGVARCHAR',
        'description' => 'Candles JSON',
    ],
    'funding_rate' => [
        'type' => 'DECIMAL',
        'description' => 'Funding rate',
    ],
    'depth_imbalance' => [
        'type' => 'DECIMAL',
        'description' => 'Depth imbalance',
    ],
    'depth_imbalance_avg' => [
        'type' => 'DECIMAL',
        'description' => 'Depth imbalance (smoothed)',
    ],
    'adx14' => [
        'type' => 'DECIMAL',
        'description' => 'ADX14',
    ],
    'atr_pct_rank' => [
        'type' => 'DECIMAL',
        'description' => 'ATR% percentile',
    ],
    'taker_buy_ratio' => [
        'type' => 'DECIMAL',
        'description' => 'Taker buy ratio',
    ],
    'vol_zscore' => [
        'type' => 'DECIMAL',
        'description' => 'Volume z-score',
    ],
    'computed_at' => [
        'type' => 'TIMESTAMP',
        'description' => 'Computed at',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['market_summary'] = [
    'select' => $table['market_summary'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['market_regime'] = [
    'id_market_regime' => [
        'type' => 'INTEGER',
    ],
    'symbol' => [
        'type' => 'VARCHAR',
        'description' => 'Symbol',
    ],
    'tf' => [
        'type' => 'VARCHAR',
        'description' => 'Timeframe',
    ],
    'price' => [
        'type' => 'DECIMAL',
        'description' => 'Price',
    ],
    'trend' => [
        'type' => 'ENUM',
        'description' => 'Trend',
        'valueSet' => [
            'strong_up',
            'up',
            'sideways',
            'down',
            'strong_down',
        ],
    ],
    'rsi14' => [
        'type' => 'DECIMAL',
        'description' => 'RSI14',
    ],
    'atr_pct' => [
        'type' => 'DECIMAL',
        'description' => 'ATR %',
    ],
    'adx14' => [
        'type' => 'DECIMAL',
        'description' => 'ADX14',
    ],
    'atr_pct_rank' => [
        'type' => 'DECIMAL',
        'description' => 'ATR% percentile',
    ],
    'taker_buy_ratio' => [
        'type' => 'DECIMAL',
        'description' => 'Taker buy ratio',
    ],
    'vol_zscore' => [
        'type' => 'DECIMAL',
        'description' => 'Volume z-score',
    ],
    'funding_rate' => [
        'type' => 'DECIMAL',
        'description' => 'Funding rate',
    ],
    'depth_imbalance' => [
        'type' => 'DECIMAL',
        'description' => 'Depth imbalance',
    ],
    'depth_imbalance_avg' => [
        'type' => 'DECIMAL',
        'description' => 'Depth imbalance (smoothed)',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['market_regime'] = [
    'select' => $table['market_regime'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['bot_decision'] = [
    'id_bot_decision' => [
        'type' => 'INTEGER',
    ],
    'id_grid_run' => [
        'type' => 'INTEGER',
        'description' => 'Run',
    ],
    'source' => [
        'type' => 'ENUM',
        'description' => 'Source',
        'valueSet' => [
            'Claude',
            'Cron',
            'Manual',
        ],
    ],
    'p_low' => [
        'type' => 'DECIMAL',
        'description' => 'Range low',
    ],
    'p_high' => [
        'type' => 'DECIMAL',
        'description' => 'Range high',
    ],
    'n_levels' => [
        'type' => 'INTEGER',
        'description' => 'Levels',
    ],
    'deploy_pct' => [
        'type' => 'INTEGER',
        'description' => 'Deployed budget %',
    ],
    'reason' => [
        'type' => 'VARCHAR',
        'description' => 'Reason',
    ],
    'price_at' => [
        'type' => 'DECIMAL',
        'description' => 'Price at decision',
    ],
    'realized_before' => [
        'type' => 'DECIMAL',
        'description' => 'Realized before',
    ],
    'eval_status' => [
        'type' => 'ENUM',
        'description' => 'Eval',
        'valueSet' => [
            'Pending',
            'Scored',
        ],
    ],
    'eval_at' => [
        'type' => 'TIMESTAMP',
        'description' => 'Scored at',
    ],
    'applied_at' => [
        'type' => 'TIMESTAMP',
        'description' => 'Applied at',
    ],
    'cycles_delta' => [
        'type' => 'INTEGER',
        'description' => 'Cycles after',
    ],
    'realized_delta' => [
        'type' => 'DECIMAL',
        'description' => 'P/L after',
    ],
    'price_move_pct' => [
        'type' => 'DECIMAL',
        'description' => 'Price move %',
    ],
    'verdict' => [
        'type' => 'ENUM',
        'description' => 'Verdict',
        'valueSet' => [
            'Win',
            'Flat',
            'Loss',
            'Superseded',
        ],
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['bot_decision'] = [
    'select' => $table['bot_decision'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['authy_group'] = [
    'id_authy_group' => [
        'type' => 'INTEGER',
    ],
    'name' => [
        'type' => 'VARCHAR',
        'description' => 'Name',
    ],
    'desc' => [
        'type' => 'VARCHAR',
        'description' => 'Description',
    ],
    'default_group' => [
        'type' => 'ENUM',
        'description' => 'Default',
        'valueSet' => [
            'No',
            'Yes',
        ],
    ],
    'admin' => [
        'type' => 'ENUM',
        'description' => 'Admin',
        'valueSet' => [
            'No',
            'Yes',
        ],
    ],
    'rights_all' => [
        'type' => 'VARCHAR',
        'description' => 'Rights',
    ],
    'rights_owner' => [
        'type' => 'VARCHAR',
        'description' => 'Rights owner',
    ],
    'rights_group' => [
        'type' => 'VARCHAR',
        'description' => 'Rights group',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['authy_group'] = [
    'select' => $table['authy_group'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['authy_group_x'] = [
    'id_authy' => [
        'type' => 'INTEGER',
    ],
    'id_authy_group' => [
        'type' => 'INTEGER',
        'description' => 'Group',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['authy_group_x'] = [
    'select' => $table['authy_group_x'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['authy_log'] = [
    'id_authy_log' => [
        'type' => 'INTEGER',
    ],
    'id_authy' => [
        'type' => 'INTEGER',
    ],
    'timestamp' => [
        'type' => 'TIMESTAMP',
        'description' => 'Date',
    ],
    'login' => [
        'type' => 'VARCHAR',
        'description' => 'Username',
    ],
    'userid' => [
        'type' => 'INTEGER',
    ],
    'result' => [
        'type' => 'VARCHAR',
    ],
    'event' => [
        'type' => 'VARCHAR',
        'description' => 'Event',
    ],
    'ip' => [
        'type' => 'VARCHAR',
        'description' => 'Ip',
    ],
    'count' => [
        'type' => 'INTEGER',
        'description' => 'Count',
    ],
];

$query['authy_log'] = [
    'select' => $table['authy_log'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['message'] = [
    'id_message' => [
        'type' => 'INTEGER',
    ],
    'label' => [
        'type' => 'VARCHAR',
        'description' => 'Label',
    ],
];

$query['message'] = [
    'select' => $table['message'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['config'] = [
    'id_config' => [
        'type' => 'INTEGER',
    ],
    'category' => [
        'type' => 'ENUM',
        'description' => 'Category',
        'valueSet' => [
            'General',
            'API',
            'Control panel',
        ],
    ],
    'config' => [
        'type' => 'VARCHAR',
        'description' => 'Setting',
    ],
    'value' => [
        'type' => 'LONGVARCHAR',
        'description' => 'Value',
    ],
    'system' => [
        'type' => 'ENUM',
    ],
    'description' => [
        'type' => 'VARCHAR',
        'description' => 'Description',
    ],
    'type' => [
        'type' => 'VARCHAR',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['config'] = [
    'select' => $table['config'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['api_rbac'] = [
    'id_api_rbac' => [
        'type' => 'INTEGER',
    ],
    'date_creation' => [
        'type' => 'DATE',
        'description' => 'Date',
    ],
    'description' => [
        'type' => 'LONGVARCHAR',
        'description' => 'Description',
    ],
    'model' => [
        'type' => 'VARCHAR',
        'description' => 'Model',
    ],
    'action' => [
        'type' => 'VARCHAR',
        'description' => 'Action',
    ],
    'body' => [
        'type' => 'LONGVARCHAR',
        'description' => 'Body',
    ],
    'query' => [
        'type' => 'LONGVARCHAR',
        'description' => 'Query',
    ],
    'method' => [
        'type' => 'ENUM',
        'description' => 'Method',
        'valueSet' => [
            'GET',
            'POST',
            'PATCH',
            'PUT',
            'DELETE',
            'ALL',
        ],
    ],
    'scope' => [
        'type' => 'ENUM',
        'description' => 'Scope',
        'valueSet' => [
            'Private',
            'Public',
        ],
    ],
    'rule' => [
        'type' => 'ENUM',
        'description' => 'Rule',
        'valueSet' => [
            'Allow',
            'Deny',
        ],
    ],
    'count' => [
        'type' => 'INTEGER',
        'description' => 'Used count',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['api_rbac'] = [
    'select' => $table['api_rbac'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['api_log'] = [
    'id_api_log' => [
        'type' => 'INTEGER',
    ],
    'id_api_rbac' => [
        'type' => 'INTEGER',
        'description' => 'Rule',
    ],
    'id_authy' => [
        'type' => 'INTEGER',
        'description' => 'User',
    ],
    'time' => [
        'type' => 'TIMESTAMP',
        'description' => 'Time',
    ],
    'raw_parameters' => [
        'type' => 'LONGVARCHAR',
        'description' => 'Raw parameters',
    ],
    'count' => [
        'type' => 'INTEGER',
        'description' => 'Count',
    ],
];

$query['api_log'] = [
    'select' => $table['api_log'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['template'] = [
    'id_template' => [
        'type' => 'INTEGER',
    ],
    'name' => [
        'type' => 'VARCHAR',
        'description' => 'Name',
    ],
    'subject' => [
        'type' => 'VARCHAR',
        'description' => 'Subject',
    ],
    'color_1' => [
        'type' => 'VARCHAR',
        'description' => 'Color 1',
    ],
    'color_2' => [
        'type' => 'VARCHAR',
        'description' => 'Color 2',
    ],
    'color_3' => [
        'type' => 'VARCHAR',
        'description' => 'Color 3',
    ],
    'status' => [
        'type' => 'ENUM',
        'description' => 'Status',
        'valueSet' => [
            'Active',
            'Inactive',
        ],
    ],
    'body' => [
        'type' => 'LONGVARCHAR',
        'description' => 'Body',
    ],
    'footer' => [
        'type' => 'LONGVARCHAR',
        'description' => 'Footer',
    ],
    'lang' => [
        'type' => 'ENUM',
        'description' => 'Language',
        'valueSet' => [
            'fr_CA',
            'en_US',
        ],
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['template'] = [
    'select' => $table['template'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['template_file'] = [
    'id_template_file' => [
        'type' => 'INTEGER',
    ],
    'id_template' => [
        'type' => 'INTEGER',
    ],
    'name' => [
        'type' => 'VARCHAR',
        'description' => 'Name',
    ],
    'file' => [
        'type' => 'VARCHAR',
        'description' => 'File',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['template_file'] = [
    'select' => $table['template_file'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['authy_refresh_token'] = [
    'id_authy_refresh_token' => [
        'type' => 'INTEGER',
    ],
    'id_authy' => [
        'type' => 'INTEGER',
    ],
    'family_id' => [
        'type' => 'VARCHAR',
    ],
    'token_hash' => [
        'type' => 'VARCHAR',
    ],
    'expires' => [
        'type' => 'INTEGER',
    ],
    'family_expires' => [
        'type' => 'INTEGER',
    ],
    'revoked' => [
        'type' => 'ENUM',
    ],
    'created_at' => [
        'type' => 'TIMESTAMP',
    ],
    'last_used_at' => [
        'type' => 'TIMESTAMP',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['authy_refresh_token'] = [
    'select' => $table['authy_refresh_token'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['oauth_client'] = [
    'id_oauth_client' => [
        'type' => 'INTEGER',
    ],
    'client_id' => [
        'type' => 'VARCHAR',
    ],
    'client_secret_hash' => [
        'type' => 'VARCHAR',
    ],
    'name' => [
        'type' => 'VARCHAR',
    ],
    'redirect_uris' => [
        'type' => 'LONGVARCHAR',
    ],
    'grant_types' => [
        'type' => 'VARCHAR',
    ],
    'scopes' => [
        'type' => 'VARCHAR',
    ],
    'is_confidential' => [
        'type' => 'ENUM',
    ],
    'created_at' => [
        'type' => 'TIMESTAMP',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['oauth_client'] = [
    'select' => $table['oauth_client'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['oauth_auth_code'] = [
    'id_oauth_auth_code' => [
        'type' => 'INTEGER',
    ],
    'code_id' => [
        'type' => 'VARCHAR',
    ],
    'id_authy' => [
        'type' => 'INTEGER',
    ],
    'client_id' => [
        'type' => 'VARCHAR',
    ],
    'scopes' => [
        'type' => 'VARCHAR',
    ],
    'expires' => [
        'type' => 'INTEGER',
    ],
    'revoked' => [
        'type' => 'ENUM',
    ],
    'created_at' => [
        'type' => 'TIMESTAMP',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['oauth_auth_code'] = [
    'select' => $table['oauth_auth_code'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['oauth_access_token'] = [
    'id_oauth_access_token' => [
        'type' => 'INTEGER',
    ],
    'token_id' => [
        'type' => 'VARCHAR',
    ],
    'id_authy' => [
        'type' => 'INTEGER',
    ],
    'client_id' => [
        'type' => 'VARCHAR',
    ],
    'scopes' => [
        'type' => 'VARCHAR',
    ],
    'expires' => [
        'type' => 'INTEGER',
    ],
    'revoked' => [
        'type' => 'ENUM',
    ],
    'created_at' => [
        'type' => 'TIMESTAMP',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['oauth_access_token'] = [
    'select' => $table['oauth_access_token'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['oauth_refresh_token'] = [
    'id_oauth_refresh_token' => [
        'type' => 'INTEGER',
    ],
    'token_id' => [
        'type' => 'VARCHAR',
    ],
    'access_token_id' => [
        'type' => 'VARCHAR',
    ],
    'id_authy' => [
        'type' => 'INTEGER',
    ],
    'client_id' => [
        'type' => 'VARCHAR',
    ],
    'expires' => [
        'type' => 'INTEGER',
    ],
    'revoked' => [
        'type' => 'ENUM',
    ],
    'created_at' => [
        'type' => 'TIMESTAMP',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['oauth_refresh_token'] = [
    'select' => $table['oauth_refresh_token'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];
$table['message_i18n'] = [
    'id_message' => [
        'type' => 'INTEGER',
    ],
    'locale' => [
        'type' => 'VARCHAR',
    ],
    'text' => [
        'type' => 'LONGVARCHAR',
        'description' => 'Texte',
    ],
    'date_creation' => [
        'type' => 'TIMESTAMP',
    ],
    'date_modification' => [
        'type' => 'TIMESTAMP',
    ],
    'id_group_creation' => [
        'type' => 'INTEGER',
    ],
    'id_creation' => [
        'type' => 'INTEGER',
    ],
    'id_modification' => [
        'type' => 'INTEGER',
    ],
];

$query['message_i18n'] = [
    'select' => $table['message_i18n'],
    'filter' => [],
    'join' => [],
    'limit' => [],
    'order' => [],
    'page' => [],
];

return [
            'Authy/auth' => [
            'description' => "Authenticate a user and get a JWT token.",
            'type' => 'service',
            'POST' => [
                'request' => [
                            'u' => ['type' => 'string',
                                    'description'=> 'Username'
                            ],
                            'pw' => ['type' => 'string',
                                    'description'=> 'The MD5 hash of the password.'
                            ],
                ],
                'response' => [
                    'token' => ['type' => 'string',
                                'description'=> 'The JWT tocken.'
                    ],
                    'expires' => ['type' => 'timestamp',
                                'description'=> 'The expiration datetime.'
                    ],
                ]
            ]
        ],
        'ApiGoat/sendEmail' => [
            'description' => "Send an email to one or multiple existing email address(es).",
            'type' => 'service',
            'POST' => [
                'request' => [
                    'template_name' => ['type' => 'string',
                                        'description'=> 'The name of an existing template.'
                    ],
                    'email' => ['type' => 'string',
                                        'description'=> 'An existing email from the the Authy table.'
                    ],
                ],
                'response' => [
                    'data' => 'null',
                    'messages' => ['type' => 'string',
                                'description'=> 'The status of the email sender.'
                    ]
                ]
            ]
        ],
        'ApiGoat/account/{id}' => [
            'description' => "Get an account details.",
            'type' => 'service',
            'GET' => [
                'request' => [
                    'id' => ['type' => 'integer',
                            'description'=> 'A Authy id.'
                    ]
                ],
                'response' => [
                    'data' => ['type' => 'array',
                                'description'=> 'All Authy fields minus the secured ones (password, rights, etc...).'
                    ],
                ]
            ]
        ],
        'ApiGoat/reset/{key}' => [
            'description' => "Reset a password from a one time key.",
            'type' => 'service',
            'POST' => [
                'request' => [
                    'key' => ['type' => 'string',
                                        'description'=> 'The name of an existing template.'
                    ],
                    'email' => ['type' => 'string',
                                        'description'=> 'An existing email from the the Authy table.'
                    ],
                ],
                'response' => [
                    'data' => ['type' => 'string',
                                'description'=> 'The status of the email sender.'
                    ],
                ]
            ]
        ],
        'ApiGoat/oAuth/{Provider}/' => [
            'description' => "Use Oauth service to register and connect your user. The service must have been configured separatly beforehand.",
            'type' => 'service',
            'GET' => [
                'request' => [
                    'Provider' => ['type' => 'string',
                                    'description'=> 'One of the supported provider [facebook, github].'],
                ],
                'response' => [
                    'description' => "Redirection to the provider."
                ]
            ]
        ],
    'authy[/{id}]' => [
        'description' => 'User',
        'type' => 'base',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_authy'
                ]
            ],
            'response' => [
                'data' => $table['authy']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['authy'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['authy']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_authy'
            ]
        ],
    ],
    'push_device[/{id}]' => [
        'description' => 'Push device',
        'type' => 'custom',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_push_device'
                ]
            ],
            'response' => [
                'data' => $table['push_device']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['push_device'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['push_device']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_push_device'
            ]
        ],
    ],
    'country[/{id}]' => [
        'description' => 'Country',
        'type' => 'custom',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_country'
                ]
            ],
            'response' => [
                'data' => $table['country']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['country'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['country']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_country'
            ]
        ],
    ],
    'grid_run[/{id}]' => [
        'description' => 'Grid Run',
        'type' => 'custom',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_grid_run'
                ]
            ],
            'response' => [
                'data' => $table['grid_run']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['grid_run'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['grid_run']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_grid_run'
            ]
        ],
    ],
    'bot_order[/{id}]' => [
        'description' => 'Order',
        'type' => 'custom',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_bot_order'
                ]
            ],
            'response' => [
                'data' => $table['bot_order']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['bot_order'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['bot_order']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_bot_order'
            ]
        ],
    ],
    'trade_cycle[/{id}]' => [
        'description' => 'Trade Cycle',
        'type' => 'custom',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_trade_cycle'
                ]
            ],
            'response' => [
                'data' => $table['trade_cycle']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['trade_cycle'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['trade_cycle']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_trade_cycle'
            ]
        ],
    ],
    'bot_event[/{id}]' => [
        'description' => 'Event',
        'type' => 'custom',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_bot_event'
                ]
            ],
            'response' => [
                'data' => $table['bot_event']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['bot_event'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['bot_event']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_bot_event'
            ]
        ],
    ],
    'bot_command[/{id}]' => [
        'description' => 'Command',
        'type' => 'custom',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_bot_command'
                ]
            ],
            'response' => [
                'data' => $table['bot_command']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['bot_command'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['bot_command']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_bot_command'
            ]
        ],
    ],
    'sim_wallet[/{id}]' => [
        'description' => 'Paper Wallet',
        'type' => 'custom',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_sim_wallet'
                ]
            ],
            'response' => [
                'data' => $table['sim_wallet']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['sim_wallet'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['sim_wallet']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_sim_wallet'
            ]
        ],
    ],
    'market_summary[/{id}]' => [
        'description' => 'Market Data',
        'type' => 'custom',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_market_summary'
                ]
            ],
            'response' => [
                'data' => $table['market_summary']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['market_summary'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['market_summary']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_market_summary'
            ]
        ],
    ],
    'market_regime[/{id}]' => [
        'description' => 'Regime History',
        'type' => 'custom',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_market_regime'
                ]
            ],
            'response' => [
                'data' => $table['market_regime']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['market_regime'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['market_regime']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_market_regime'
            ]
        ],
    ],
    'bot_decision[/{id}]' => [
        'description' => 'Refit Decision',
        'type' => 'custom',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_bot_decision'
                ]
            ],
            'response' => [
                'data' => $table['bot_decision']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['bot_decision'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['bot_decision']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_bot_decision'
            ]
        ],
    ],
    'authy_group[/{id}]' => [
        'description' => 'Group',
        'type' => 'base',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_authy_group'
                ]
            ],
            'response' => [
                'data' => $table['authy_group']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['authy_group'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['authy_group']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_authy_group'
            ]
        ],
    ],
    'authy_group_x[/{id}]' => [
        'description' => 'Group',
        'type' => 'base',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_authy'
                ]
            ],
            'response' => [
                'data' => $table['authy_group_x']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['authy_group_x'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['authy_group_x']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_authy'
            ]
        ],
    ],
    'authy_log[/{id}]' => [
        'description' => 'Login log',
        'type' => 'base',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_authy_log'
                ]
            ],
            'response' => [
                'data' => $table['authy_log']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['authy_log'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['authy_log']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_authy_log'
            ]
        ],
    ],
    'message[/{id}]' => [
        'description' => 'Message',
        'type' => 'base',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_message'
                ]
            ],
            'response' => [
                'data' => $table['message']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['message'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['message']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_message'
            ]
        ],
    ],
    'config[/{id}]' => [
        'description' => 'Setting',
        'type' => 'base',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_config'
                ]
            ],
            'response' => [
                'data' => $table['config']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['config'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['config']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_config'
            ]
        ],
    ],
    'api_rbac[/{id}]' => [
        'description' => 'API ACL',
        'type' => 'base',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_api_rbac'
                ]
            ],
            'response' => [
                'data' => $table['api_rbac']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['api_rbac'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['api_rbac']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_api_rbac'
            ]
        ],
    ],
    'api_log[/{id}]' => [
        'description' => 'API log',
        'type' => 'base',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_api_log'
                ]
            ],
            'response' => [
                'data' => $table['api_log']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['api_log'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['api_log']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_api_log'
            ]
        ],
    ],
    'template[/{id}]' => [
        'description' => 'Template',
        'type' => 'base',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_template'
                ]
            ],
            'response' => [
                'data' => $table['template']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['template'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['template']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_template'
            ]
        ],
    ],
    'template_file[/{id}]' => [
        'description' => 'File',
        'type' => 'base',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_template_file'
                ]
            ],
            'response' => [
                'data' => $table['template_file']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['template_file'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['template_file']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_template_file'
            ]
        ],
    ],
    'authy_refresh_token[/{id}]' => [
        'description' => '',
        'type' => 'custom',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_authy_refresh_token'
                ]
            ],
            'response' => [
                'data' => $table['authy_refresh_token']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['authy_refresh_token'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['authy_refresh_token']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_authy_refresh_token'
            ]
        ],
    ],
    'oauth_client[/{id}]' => [
        'description' => '',
        'type' => 'custom',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_oauth_client'
                ]
            ],
            'response' => [
                'data' => $table['oauth_client']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['oauth_client'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['oauth_client']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_oauth_client'
            ]
        ],
    ],
    'oauth_auth_code[/{id}]' => [
        'description' => '',
        'type' => 'custom',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_oauth_auth_code'
                ]
            ],
            'response' => [
                'data' => $table['oauth_auth_code']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['oauth_auth_code'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['oauth_auth_code']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_oauth_auth_code'
            ]
        ],
    ],
    'oauth_access_token[/{id}]' => [
        'description' => '',
        'type' => 'custom',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_oauth_access_token'
                ]
            ],
            'response' => [
                'data' => $table['oauth_access_token']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['oauth_access_token'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['oauth_access_token']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_oauth_access_token'
            ]
        ],
    ],
    'oauth_refresh_token[/{id}]' => [
        'description' => '',
        'type' => 'custom',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_oauth_refresh_token'
                ]
            ],
            'response' => [
                'data' => $table['oauth_refresh_token']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['oauth_refresh_token'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['oauth_refresh_token']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_oauth_refresh_token'
            ]
        ],
    ],
    'message_i18n[/{id}]' => [
        'description' => '',
        'type' => 'base',
        'GET' => [
            'request' => [
                'id' => [
                    'type' => 'INTEGER',
                    'name' => 'id_message'
                ]
            ],
            'response' => [
                'data' => $table['message_i18n']
            ]
        ],
        'POST' =>  [
            'request' => [
                'fields' => $table['message_i18n'],
                'query' => $query
            ],
            'response' => [
                'ids' => [],
                'count' => []
            ]
        ],
        'PATCH' =>  [
            'request' => $table['message_i18n']
            ],
        'DELETE' =>  [
            'request' => [
                        'type' => 'INTEGER',
                        'name' => 'id_message'
            ]
        ],
    ],
];
