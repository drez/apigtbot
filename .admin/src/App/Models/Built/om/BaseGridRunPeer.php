<?php

namespace App\om;

use \BasePeer;
use \Criteria;
use \PDO;
use \PDOStatement;
use \Propel;
use \PropelException;
use \PropelPDO;
use App\AuthyGroupPeer;
use App\AuthyPeer;
use App\BotCommandPeer;
use App\BotDecisionPeer;
use App\BotEventPeer;
use App\BotOrderPeer;
use App\FleetSlotPeer;
use App\GridRun;
use App\GridRunAuditPeer;
use App\GridRunPeer;
use App\RegimeEpisodePeer;
use App\TradeCyclePeer;
use App\map\GridRunTableMap;

/**
 * Base static class for performing query and update operations on the 'grid_run' table.
 *
 * Grid Run
 *
 * @package propel.generator..om
 */
abstract class BaseGridRunPeer
{

    /** the default database name for this class */
    const DATABASE_NAME = 'apigtbot';

    /** the table name for this class */
    const TABLE_NAME = 'grid_run';

    /** the related Propel class for this table */
    const OM_CLASS = 'App\\GridRun';

    /** the related TableMap class for this table */
    const TM_CLASS = 'App\\map\\GridRunTableMap';

    /** The total number of columns. */
    const NUM_COLUMNS = 52;

    /** The number of lazy-loaded columns. */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /** The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS) */
    const NUM_HYDRATE_COLUMNS = 52;

    /** the column name for the id_grid_run field */
    const ID_GRID_RUN = 'grid_run.id_grid_run';

    /** the column name for the label field */
    const LABEL = 'grid_run.label';

    /** the column name for the symbol field */
    const SYMBOL = 'grid_run.symbol';

    /** the column name for the status field */
    const STATUS = 'grid_run.status';

    /** the column name for the kill_switch field */
    const KILL_SWITCH = 'grid_run.kill_switch';

    /** the column name for the profile field */
    const PROFILE = 'grid_run.profile';

    /** the column name for the algo field */
    const ALGO = 'grid_run.algo';

    /** the column name for the simulated field */
    const SIMULATED = 'grid_run.simulated';

    /** the column name for the p_low field */
    const P_LOW = 'grid_run.p_low';

    /** the column name for the p_high field */
    const P_HIGH = 'grid_run.p_high';

    /** the column name for the n_levels field */
    const N_LEVELS = 'grid_run.n_levels';

    /** the column name for the spacing field */
    const SPACING = 'grid_run.spacing';

    /** the column name for the allocation field */
    const ALLOCATION = 'grid_run.allocation';

    /** the column name for the budget_quote field */
    const BUDGET_QUOTE = 'grid_run.budget_quote';

    /** the column name for the deploy_pct field */
    const DEPLOY_PCT = 'grid_run.deploy_pct';

    /** the column name for the alloc_mode field */
    const ALLOC_MODE = 'grid_run.alloc_mode';

    /** the column name for the fee_pct field */
    const FEE_PCT = 'grid_run.fee_pct';

    /** the column name for the max_position_quote field */
    const MAX_POSITION_QUOTE = 'grid_run.max_position_quote';

    /** the column name for the max_order_quote field */
    const MAX_ORDER_QUOTE = 'grid_run.max_order_quote';

    /** the column name for the daily_loss_limit_quote field */
    const DAILY_LOSS_LIMIT_QUOTE = 'grid_run.daily_loss_limit_quote';

    /** the column name for the max_unrealized_loss_quote field */
    const MAX_UNREALIZED_LOSS_QUOTE = 'grid_run.max_unrealized_loss_quote';

    /** the column name for the sell_at_loss field */
    const SELL_AT_LOSS = 'grid_run.sell_at_loss';

    /** the column name for the sell_when_starved field */
    const SELL_WHEN_STARVED = 'grid_run.sell_when_starved';

    /** the column name for the breakout_buffer_pct field */
    const BREAKOUT_BUFFER_PCT = 'grid_run.breakout_buffer_pct';

    /** the column name for the breakout_policy field */
    const BREAKOUT_POLICY = 'grid_run.breakout_policy';

    /** the column name for the max_open_orders field */
    const MAX_OPEN_ORDERS = 'grid_run.max_open_orders';

    /** the column name for the max_buy_levels_below field */
    const MAX_BUY_LEVELS_BELOW = 'grid_run.max_buy_levels_below';

    /** the column name for the trend_tf field */
    const TREND_TF = 'grid_run.trend_tf';

    /** the column name for the donchian_period field */
    const DONCHIAN_PERIOD = 'grid_run.donchian_period';

    /** the column name for the trend_ema_fast field */
    const TREND_EMA_FAST = 'grid_run.trend_ema_fast';

    /** the column name for the trend_ema_slow field */
    const TREND_EMA_SLOW = 'grid_run.trend_ema_slow';

    /** the column name for the atr_period field */
    const ATR_PERIOD = 'grid_run.atr_period';

    /** the column name for the atr_stop_mult field */
    const ATR_STOP_MULT = 'grid_run.atr_stop_mult';

    /** the column name for the atr_initial_mult field */
    const ATR_INITIAL_MULT = 'grid_run.atr_initial_mult';

    /** the column name for the trend_stop_floor_pct field */
    const TREND_STOP_FLOOR_PCT = 'grid_run.trend_stop_floor_pct';

    /** the column name for the trend_signal field */
    const TREND_SIGNAL = 'grid_run.trend_signal';

    /** the column name for the reentry_cooldown field */
    const REENTRY_COOLDOWN = 'grid_run.reentry_cooldown';

    /** the column name for the engine_state field */
    const ENGINE_STATE = 'grid_run.engine_state';

    /** the column name for the last_tick_at field */
    const LAST_TICK_AT = 'grid_run.last_tick_at';

    /** the column name for the last_price field */
    const LAST_PRICE = 'grid_run.last_price';

    /** the column name for the bal_base field */
    const BAL_BASE = 'grid_run.bal_base';

    /** the column name for the bal_quote field */
    const BAL_QUOTE = 'grid_run.bal_quote';

    /** the column name for the sim_bal_base field */
    const SIM_BAL_BASE = 'grid_run.sim_bal_base';

    /** the column name for the sim_bal_quote field */
    const SIM_BAL_QUOTE = 'grid_run.sim_bal_quote';

    /** the column name for the run_uid field */
    const RUN_UID = 'grid_run.run_uid';

    /** the column name for the applied_geometry field */
    const APPLIED_GEOMETRY = 'grid_run.applied_geometry';

    /** the column name for the ledger_reset_at field */
    const LEDGER_RESET_AT = 'grid_run.ledger_reset_at';

    /** the column name for the date_creation field */
    const DATE_CREATION = 'grid_run.date_creation';

    /** the column name for the date_modification field */
    const DATE_MODIFICATION = 'grid_run.date_modification';

    /** the column name for the id_group_creation field */
    const ID_GROUP_CREATION = 'grid_run.id_group_creation';

    /** the column name for the id_creation field */
    const ID_CREATION = 'grid_run.id_creation';

    /** the column name for the id_modification field */
    const ID_MODIFICATION = 'grid_run.id_modification';

    /** The enumerated values for the status field */
    const STATUS_DRAFT = 'Draft';
    const STATUS_DRYRUN = 'DryRun';
    const STATUS_TESTNET = 'Testnet';
    const STATUS_LIVE = 'Live';
    const STATUS_HALTED = 'Halted';
    const STATUS_DONE = 'Done';
    const STATUS_RETIRING = 'Retiring';

    /** The enumerated values for the profile field */
    const PROFILE_NOLOSS = 'NoLoss';
    const PROFILE_CAUTIOUS = 'Cautious';
    const PROFILE_BALANCED = 'Balanced';
    const PROFILE_AGGRESSIVE = 'Aggressive';
    const PROFILE_MAX = 'Max';

    /** The enumerated values for the algo field */
    const ALGO_GRID = 'Grid';
    const ALGO_TREND = 'Trend';

    /** The enumerated values for the spacing field */
    const SPACING_GEOMETRIC = 'Geometric';
    const SPACING_ARITHMETIC = 'Arithmetic';

    /** The enumerated values for the allocation field */
    const ALLOCATION_EQUALQUOTE = 'EqualQuote';
    const ALLOCATION_EQUALBASE = 'EqualBase';
    const ALLOCATION_BOTTOMWEIGHTED = 'BottomWeighted';

    /** The enumerated values for the alloc_mode field */
    const ALLOC_MODE_AUTO = 'Auto';
    const ALLOC_MODE_FIXED = 'Fixed';

    /** The enumerated values for the breakout_policy field */
    const BREAKOUT_POLICY_HALTANDHOLD = 'HaltAndHold';
    const BREAKOUT_POLICY_FLATTEN = 'Flatten';

    /** The enumerated values for the trend_tf field */
    const TREND_TF_1H = '1h';
    const TREND_TF_4H = '4h';

    /** The enumerated values for the trend_signal field */
    const TREND_SIGNAL_DONCHIAN = 'Donchian';
    const TREND_SIGNAL_EMACROSS1D = 'EmaCross1d';

    /** The default string format for model objects of the related table **/
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * An identity map to hold any loaded instances of GridRun objects.
     * This must be public so that other peer classes can access this when hydrating from JOIN
     * queries.
     * @var        array GridRun[]
     */
    public static $instances = array();


    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. GridRunPeer::$fieldNames[GridRunPeer::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        BasePeer::TYPE_PHPNAME => array ('IdGridRun', 'Label', 'Symbol', 'Status', 'KillSwitch', 'Profile', 'Algo', 'Simulated', 'PLow', 'PHigh', 'NLevels', 'Spacing', 'Allocation', 'BudgetQuote', 'DeployPct', 'AllocMode', 'FeePct', 'MaxPositionQuote', 'MaxOrderQuote', 'DailyLossLimitQuote', 'MaxUnrealizedLossQuote', 'SellAtLoss', 'SellWhenStarved', 'BreakoutBufferPct', 'BreakoutPolicy', 'MaxOpenOrders', 'MaxBuyLevelsBelow', 'TrendTf', 'DonchianPeriod', 'TrendEmaFast', 'TrendEmaSlow', 'AtrPeriod', 'AtrStopMult', 'AtrInitialMult', 'TrendStopFloorPct', 'TrendSignal', 'ReentryCooldown', 'EngineState', 'LastTickAt', 'LastPrice', 'BalBase', 'BalQuote', 'SimBalBase', 'SimBalQuote', 'RunUid', 'AppliedGeometry', 'LedgerResetAt', 'DateCreation', 'DateModification', 'IdGroupCreation', 'IdCreation', 'IdModification', ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('idGridRun', 'label', 'symbol', 'status', 'killSwitch', 'profile', 'algo', 'simulated', 'pLow', 'pHigh', 'nLevels', 'spacing', 'allocation', 'budgetQuote', 'deployPct', 'allocMode', 'feePct', 'maxPositionQuote', 'maxOrderQuote', 'dailyLossLimitQuote', 'maxUnrealizedLossQuote', 'sellAtLoss', 'sellWhenStarved', 'breakoutBufferPct', 'breakoutPolicy', 'maxOpenOrders', 'maxBuyLevelsBelow', 'trendTf', 'donchianPeriod', 'trendEmaFast', 'trendEmaSlow', 'atrPeriod', 'atrStopMult', 'atrInitialMult', 'trendStopFloorPct', 'trendSignal', 'reentryCooldown', 'engineState', 'lastTickAt', 'lastPrice', 'balBase', 'balQuote', 'simBalBase', 'simBalQuote', 'runUid', 'appliedGeometry', 'ledgerResetAt', 'dateCreation', 'dateModification', 'idGroupCreation', 'idCreation', 'idModification', ),
        BasePeer::TYPE_COLNAME => array (GridRunPeer::ID_GRID_RUN, GridRunPeer::LABEL, GridRunPeer::SYMBOL, GridRunPeer::STATUS, GridRunPeer::KILL_SWITCH, GridRunPeer::PROFILE, GridRunPeer::ALGO, GridRunPeer::SIMULATED, GridRunPeer::P_LOW, GridRunPeer::P_HIGH, GridRunPeer::N_LEVELS, GridRunPeer::SPACING, GridRunPeer::ALLOCATION, GridRunPeer::BUDGET_QUOTE, GridRunPeer::DEPLOY_PCT, GridRunPeer::ALLOC_MODE, GridRunPeer::FEE_PCT, GridRunPeer::MAX_POSITION_QUOTE, GridRunPeer::MAX_ORDER_QUOTE, GridRunPeer::DAILY_LOSS_LIMIT_QUOTE, GridRunPeer::MAX_UNREALIZED_LOSS_QUOTE, GridRunPeer::SELL_AT_LOSS, GridRunPeer::SELL_WHEN_STARVED, GridRunPeer::BREAKOUT_BUFFER_PCT, GridRunPeer::BREAKOUT_POLICY, GridRunPeer::MAX_OPEN_ORDERS, GridRunPeer::MAX_BUY_LEVELS_BELOW, GridRunPeer::TREND_TF, GridRunPeer::DONCHIAN_PERIOD, GridRunPeer::TREND_EMA_FAST, GridRunPeer::TREND_EMA_SLOW, GridRunPeer::ATR_PERIOD, GridRunPeer::ATR_STOP_MULT, GridRunPeer::ATR_INITIAL_MULT, GridRunPeer::TREND_STOP_FLOOR_PCT, GridRunPeer::TREND_SIGNAL, GridRunPeer::REENTRY_COOLDOWN, GridRunPeer::ENGINE_STATE, GridRunPeer::LAST_TICK_AT, GridRunPeer::LAST_PRICE, GridRunPeer::BAL_BASE, GridRunPeer::BAL_QUOTE, GridRunPeer::SIM_BAL_BASE, GridRunPeer::SIM_BAL_QUOTE, GridRunPeer::RUN_UID, GridRunPeer::APPLIED_GEOMETRY, GridRunPeer::LEDGER_RESET_AT, GridRunPeer::DATE_CREATION, GridRunPeer::DATE_MODIFICATION, GridRunPeer::ID_GROUP_CREATION, GridRunPeer::ID_CREATION, GridRunPeer::ID_MODIFICATION, ),
        BasePeer::TYPE_RAW_COLNAME => array ('ID_GRID_RUN', 'LABEL', 'SYMBOL', 'STATUS', 'KILL_SWITCH', 'PROFILE', 'ALGO', 'SIMULATED', 'P_LOW', 'P_HIGH', 'N_LEVELS', 'SPACING', 'ALLOCATION', 'BUDGET_QUOTE', 'DEPLOY_PCT', 'ALLOC_MODE', 'FEE_PCT', 'MAX_POSITION_QUOTE', 'MAX_ORDER_QUOTE', 'DAILY_LOSS_LIMIT_QUOTE', 'MAX_UNREALIZED_LOSS_QUOTE', 'SELL_AT_LOSS', 'SELL_WHEN_STARVED', 'BREAKOUT_BUFFER_PCT', 'BREAKOUT_POLICY', 'MAX_OPEN_ORDERS', 'MAX_BUY_LEVELS_BELOW', 'TREND_TF', 'DONCHIAN_PERIOD', 'TREND_EMA_FAST', 'TREND_EMA_SLOW', 'ATR_PERIOD', 'ATR_STOP_MULT', 'ATR_INITIAL_MULT', 'TREND_STOP_FLOOR_PCT', 'TREND_SIGNAL', 'REENTRY_COOLDOWN', 'ENGINE_STATE', 'LAST_TICK_AT', 'LAST_PRICE', 'BAL_BASE', 'BAL_QUOTE', 'SIM_BAL_BASE', 'SIM_BAL_QUOTE', 'RUN_UID', 'APPLIED_GEOMETRY', 'LEDGER_RESET_AT', 'DATE_CREATION', 'DATE_MODIFICATION', 'ID_GROUP_CREATION', 'ID_CREATION', 'ID_MODIFICATION', ),
        BasePeer::TYPE_FIELDNAME => array ('id_grid_run', 'label', 'symbol', 'status', 'kill_switch', 'profile', 'algo', 'simulated', 'p_low', 'p_high', 'n_levels', 'spacing', 'allocation', 'budget_quote', 'deploy_pct', 'alloc_mode', 'fee_pct', 'max_position_quote', 'max_order_quote', 'daily_loss_limit_quote', 'max_unrealized_loss_quote', 'sell_at_loss', 'sell_when_starved', 'breakout_buffer_pct', 'breakout_policy', 'max_open_orders', 'max_buy_levels_below', 'trend_tf', 'donchian_period', 'trend_ema_fast', 'trend_ema_slow', 'atr_period', 'atr_stop_mult', 'atr_initial_mult', 'trend_stop_floor_pct', 'trend_signal', 'reentry_cooldown', 'engine_state', 'last_tick_at', 'last_price', 'bal_base', 'bal_quote', 'sim_bal_base', 'sim_bal_quote', 'run_uid', 'applied_geometry', 'ledger_reset_at', 'date_creation', 'date_modification', 'id_group_creation', 'id_creation', 'id_modification', ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. GridRunPeer::$fieldNames[BasePeer::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        BasePeer::TYPE_PHPNAME => array ('IdGridRun' => 0, 'Label' => 1, 'Symbol' => 2, 'Status' => 3, 'KillSwitch' => 4, 'Profile' => 5, 'Algo' => 6, 'Simulated' => 7, 'PLow' => 8, 'PHigh' => 9, 'NLevels' => 10, 'Spacing' => 11, 'Allocation' => 12, 'BudgetQuote' => 13, 'DeployPct' => 14, 'AllocMode' => 15, 'FeePct' => 16, 'MaxPositionQuote' => 17, 'MaxOrderQuote' => 18, 'DailyLossLimitQuote' => 19, 'MaxUnrealizedLossQuote' => 20, 'SellAtLoss' => 21, 'SellWhenStarved' => 22, 'BreakoutBufferPct' => 23, 'BreakoutPolicy' => 24, 'MaxOpenOrders' => 25, 'MaxBuyLevelsBelow' => 26, 'TrendTf' => 27, 'DonchianPeriod' => 28, 'TrendEmaFast' => 29, 'TrendEmaSlow' => 30, 'AtrPeriod' => 31, 'AtrStopMult' => 32, 'AtrInitialMult' => 33, 'TrendStopFloorPct' => 34, 'TrendSignal' => 35, 'ReentryCooldown' => 36, 'EngineState' => 37, 'LastTickAt' => 38, 'LastPrice' => 39, 'BalBase' => 40, 'BalQuote' => 41, 'SimBalBase' => 42, 'SimBalQuote' => 43, 'RunUid' => 44, 'AppliedGeometry' => 45, 'LedgerResetAt' => 46, 'DateCreation' => 47, 'DateModification' => 48, 'IdGroupCreation' => 49, 'IdCreation' => 50, 'IdModification' => 51, ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('idGridRun' => 0, 'label' => 1, 'symbol' => 2, 'status' => 3, 'killSwitch' => 4, 'profile' => 5, 'algo' => 6, 'simulated' => 7, 'pLow' => 8, 'pHigh' => 9, 'nLevels' => 10, 'spacing' => 11, 'allocation' => 12, 'budgetQuote' => 13, 'deployPct' => 14, 'allocMode' => 15, 'feePct' => 16, 'maxPositionQuote' => 17, 'maxOrderQuote' => 18, 'dailyLossLimitQuote' => 19, 'maxUnrealizedLossQuote' => 20, 'sellAtLoss' => 21, 'sellWhenStarved' => 22, 'breakoutBufferPct' => 23, 'breakoutPolicy' => 24, 'maxOpenOrders' => 25, 'maxBuyLevelsBelow' => 26, 'trendTf' => 27, 'donchianPeriod' => 28, 'trendEmaFast' => 29, 'trendEmaSlow' => 30, 'atrPeriod' => 31, 'atrStopMult' => 32, 'atrInitialMult' => 33, 'trendStopFloorPct' => 34, 'trendSignal' => 35, 'reentryCooldown' => 36, 'engineState' => 37, 'lastTickAt' => 38, 'lastPrice' => 39, 'balBase' => 40, 'balQuote' => 41, 'simBalBase' => 42, 'simBalQuote' => 43, 'runUid' => 44, 'appliedGeometry' => 45, 'ledgerResetAt' => 46, 'dateCreation' => 47, 'dateModification' => 48, 'idGroupCreation' => 49, 'idCreation' => 50, 'idModification' => 51, ),
        BasePeer::TYPE_COLNAME => array (GridRunPeer::ID_GRID_RUN => 0, GridRunPeer::LABEL => 1, GridRunPeer::SYMBOL => 2, GridRunPeer::STATUS => 3, GridRunPeer::KILL_SWITCH => 4, GridRunPeer::PROFILE => 5, GridRunPeer::ALGO => 6, GridRunPeer::SIMULATED => 7, GridRunPeer::P_LOW => 8, GridRunPeer::P_HIGH => 9, GridRunPeer::N_LEVELS => 10, GridRunPeer::SPACING => 11, GridRunPeer::ALLOCATION => 12, GridRunPeer::BUDGET_QUOTE => 13, GridRunPeer::DEPLOY_PCT => 14, GridRunPeer::ALLOC_MODE => 15, GridRunPeer::FEE_PCT => 16, GridRunPeer::MAX_POSITION_QUOTE => 17, GridRunPeer::MAX_ORDER_QUOTE => 18, GridRunPeer::DAILY_LOSS_LIMIT_QUOTE => 19, GridRunPeer::MAX_UNREALIZED_LOSS_QUOTE => 20, GridRunPeer::SELL_AT_LOSS => 21, GridRunPeer::SELL_WHEN_STARVED => 22, GridRunPeer::BREAKOUT_BUFFER_PCT => 23, GridRunPeer::BREAKOUT_POLICY => 24, GridRunPeer::MAX_OPEN_ORDERS => 25, GridRunPeer::MAX_BUY_LEVELS_BELOW => 26, GridRunPeer::TREND_TF => 27, GridRunPeer::DONCHIAN_PERIOD => 28, GridRunPeer::TREND_EMA_FAST => 29, GridRunPeer::TREND_EMA_SLOW => 30, GridRunPeer::ATR_PERIOD => 31, GridRunPeer::ATR_STOP_MULT => 32, GridRunPeer::ATR_INITIAL_MULT => 33, GridRunPeer::TREND_STOP_FLOOR_PCT => 34, GridRunPeer::TREND_SIGNAL => 35, GridRunPeer::REENTRY_COOLDOWN => 36, GridRunPeer::ENGINE_STATE => 37, GridRunPeer::LAST_TICK_AT => 38, GridRunPeer::LAST_PRICE => 39, GridRunPeer::BAL_BASE => 40, GridRunPeer::BAL_QUOTE => 41, GridRunPeer::SIM_BAL_BASE => 42, GridRunPeer::SIM_BAL_QUOTE => 43, GridRunPeer::RUN_UID => 44, GridRunPeer::APPLIED_GEOMETRY => 45, GridRunPeer::LEDGER_RESET_AT => 46, GridRunPeer::DATE_CREATION => 47, GridRunPeer::DATE_MODIFICATION => 48, GridRunPeer::ID_GROUP_CREATION => 49, GridRunPeer::ID_CREATION => 50, GridRunPeer::ID_MODIFICATION => 51, ),
        BasePeer::TYPE_RAW_COLNAME => array ('ID_GRID_RUN' => 0, 'LABEL' => 1, 'SYMBOL' => 2, 'STATUS' => 3, 'KILL_SWITCH' => 4, 'PROFILE' => 5, 'ALGO' => 6, 'SIMULATED' => 7, 'P_LOW' => 8, 'P_HIGH' => 9, 'N_LEVELS' => 10, 'SPACING' => 11, 'ALLOCATION' => 12, 'BUDGET_QUOTE' => 13, 'DEPLOY_PCT' => 14, 'ALLOC_MODE' => 15, 'FEE_PCT' => 16, 'MAX_POSITION_QUOTE' => 17, 'MAX_ORDER_QUOTE' => 18, 'DAILY_LOSS_LIMIT_QUOTE' => 19, 'MAX_UNREALIZED_LOSS_QUOTE' => 20, 'SELL_AT_LOSS' => 21, 'SELL_WHEN_STARVED' => 22, 'BREAKOUT_BUFFER_PCT' => 23, 'BREAKOUT_POLICY' => 24, 'MAX_OPEN_ORDERS' => 25, 'MAX_BUY_LEVELS_BELOW' => 26, 'TREND_TF' => 27, 'DONCHIAN_PERIOD' => 28, 'TREND_EMA_FAST' => 29, 'TREND_EMA_SLOW' => 30, 'ATR_PERIOD' => 31, 'ATR_STOP_MULT' => 32, 'ATR_INITIAL_MULT' => 33, 'TREND_STOP_FLOOR_PCT' => 34, 'TREND_SIGNAL' => 35, 'REENTRY_COOLDOWN' => 36, 'ENGINE_STATE' => 37, 'LAST_TICK_AT' => 38, 'LAST_PRICE' => 39, 'BAL_BASE' => 40, 'BAL_QUOTE' => 41, 'SIM_BAL_BASE' => 42, 'SIM_BAL_QUOTE' => 43, 'RUN_UID' => 44, 'APPLIED_GEOMETRY' => 45, 'LEDGER_RESET_AT' => 46, 'DATE_CREATION' => 47, 'DATE_MODIFICATION' => 48, 'ID_GROUP_CREATION' => 49, 'ID_CREATION' => 50, 'ID_MODIFICATION' => 51, ),
        BasePeer::TYPE_FIELDNAME => array ('id_grid_run' => 0, 'label' => 1, 'symbol' => 2, 'status' => 3, 'kill_switch' => 4, 'profile' => 5, 'algo' => 6, 'simulated' => 7, 'p_low' => 8, 'p_high' => 9, 'n_levels' => 10, 'spacing' => 11, 'allocation' => 12, 'budget_quote' => 13, 'deploy_pct' => 14, 'alloc_mode' => 15, 'fee_pct' => 16, 'max_position_quote' => 17, 'max_order_quote' => 18, 'daily_loss_limit_quote' => 19, 'max_unrealized_loss_quote' => 20, 'sell_at_loss' => 21, 'sell_when_starved' => 22, 'breakout_buffer_pct' => 23, 'breakout_policy' => 24, 'max_open_orders' => 25, 'max_buy_levels_below' => 26, 'trend_tf' => 27, 'donchian_period' => 28, 'trend_ema_fast' => 29, 'trend_ema_slow' => 30, 'atr_period' => 31, 'atr_stop_mult' => 32, 'atr_initial_mult' => 33, 'trend_stop_floor_pct' => 34, 'trend_signal' => 35, 'reentry_cooldown' => 36, 'engine_state' => 37, 'last_tick_at' => 38, 'last_price' => 39, 'bal_base' => 40, 'bal_quote' => 41, 'sim_bal_base' => 42, 'sim_bal_quote' => 43, 'run_uid' => 44, 'applied_geometry' => 45, 'ledger_reset_at' => 46, 'date_creation' => 47, 'date_modification' => 48, 'id_group_creation' => 49, 'id_creation' => 50, 'id_modification' => 51, ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, )
    );

    /** The enumerated values for this table */
    protected static $enumValueSets = array(
        GridRunPeer::STATUS => array(
            GridRunPeer::STATUS_DRAFT,
            GridRunPeer::STATUS_DRYRUN,
            GridRunPeer::STATUS_TESTNET,
            GridRunPeer::STATUS_LIVE,
            GridRunPeer::STATUS_HALTED,
            GridRunPeer::STATUS_DONE,
            GridRunPeer::STATUS_RETIRING,
        ),
        GridRunPeer::PROFILE => array(
            GridRunPeer::PROFILE_NOLOSS,
            GridRunPeer::PROFILE_CAUTIOUS,
            GridRunPeer::PROFILE_BALANCED,
            GridRunPeer::PROFILE_AGGRESSIVE,
            GridRunPeer::PROFILE_MAX,
        ),
        GridRunPeer::ALGO => array(
            GridRunPeer::ALGO_GRID,
            GridRunPeer::ALGO_TREND,
        ),
        GridRunPeer::SPACING => array(
            GridRunPeer::SPACING_GEOMETRIC,
            GridRunPeer::SPACING_ARITHMETIC,
        ),
        GridRunPeer::ALLOCATION => array(
            GridRunPeer::ALLOCATION_EQUALQUOTE,
            GridRunPeer::ALLOCATION_EQUALBASE,
            GridRunPeer::ALLOCATION_BOTTOMWEIGHTED,
        ),
        GridRunPeer::ALLOC_MODE => array(
            GridRunPeer::ALLOC_MODE_AUTO,
            GridRunPeer::ALLOC_MODE_FIXED,
        ),
        GridRunPeer::BREAKOUT_POLICY => array(
            GridRunPeer::BREAKOUT_POLICY_HALTANDHOLD,
            GridRunPeer::BREAKOUT_POLICY_FLATTEN,
        ),
        GridRunPeer::TREND_TF => array(
            GridRunPeer::TREND_TF_1H,
            GridRunPeer::TREND_TF_4H,
        ),
        GridRunPeer::TREND_SIGNAL => array(
            GridRunPeer::TREND_SIGNAL_DONCHIAN,
            GridRunPeer::TREND_SIGNAL_EMACROSS1D,
        ),
    );

    /**
     * Translates a fieldname to another type
     *
     * @param      string $name field name
     * @param      string $fromType One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *                         BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
     * @param      string $toType   One of the class type constants
     * @return string          translated name of the field.
     * @throws PropelException - if the specified name could not be found in the fieldname mappings.
     */
    public static function translateFieldName($name, $fromType, $toType)
    {
        $toNames = GridRunPeer::getFieldNames($toType);
        $key = isset(GridRunPeer::$fieldKeys[$fromType][$name]) ? GridRunPeer::$fieldKeys[$fromType][$name] : null;
        if ($key === null) {
            throw new PropelException("'$name' could not be found in the field names of type '$fromType'. These are: " . print_r(GridRunPeer::$fieldKeys[$fromType], true));
        }

        return $toNames[$key];
    }

    /**
     * Returns an array of field names.
     *
     * @param      string $type The type of fieldnames to return:
     *                      One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *                      BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
     * @return array           A list of field names
     * @throws PropelException - if the type is not valid.
     */
    public static function getFieldNames($type = BasePeer::TYPE_PHPNAME)
    {
        if (!array_key_exists($type, GridRunPeer::$fieldNames)) {
            throw new PropelException('Method getFieldNames() expects the parameter $type to be one of the class constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME, BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM. ' . $type . ' was given.');
        }

        return GridRunPeer::$fieldNames[$type];
    }

    /**
     * Gets the list of values for all ENUM columns
     * @return array
     */
    public static function getValueSets()
    {
      return GridRunPeer::$enumValueSets;
    }

    /**
     * Gets the list of values for an ENUM column
     *
     * @param string $colname The ENUM column name.
     *
     * @return array list of possible values for the column
     */
    public static function getValueSet($colname)
    {
        $valueSets = GridRunPeer::getValueSets();

        if (!isset($valueSets[$colname])) {
            throw new PropelException(sprintf('Column "%s" has no ValueSet.', $colname));
        }

        return $valueSets[$colname];
    }

    /**
     * Gets the SQL value for the ENUM column value
     *
     * @param string $colname ENUM column name.
     * @param string $enumVal ENUM value.
     *
     * @return int SQL value
     */
    public static function getSqlValueForEnum($colname, $enumVal)
    {
        $values = GridRunPeer::getValueSet($colname);
        if (!in_array($enumVal, $values)) {
            throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $colname));
        }

        return array_search($enumVal, $values);
    }

    /**
     * Convenience method which changes table.column to alias.column.
     *
     * Using this method you can maintain SQL abstraction while using column aliases.
     * <code>
     *		$c->addAlias("alias1", TablePeer::TABLE_NAME);
     *		$c->addJoin(TablePeer::alias("alias1", TablePeer::PRIMARY_KEY_COLUMN), TablePeer::PRIMARY_KEY_COLUMN);
     * </code>
     * @param      string $alias The alias for the current table.
     * @param      string $column The column name for current table. (i.e. GridRunPeer::COLUMN_NAME).
     * @return string
     */
    public static function alias($alias, $column)
    {
        return str_replace(GridRunPeer::TABLE_NAME.'.', $alias.'.', $column);
    }

    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param      Criteria $criteria object containing the columns to add.
     * @param      string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(GridRunPeer::ID_GRID_RUN);
            $criteria->addSelectColumn(GridRunPeer::LABEL);
            $criteria->addSelectColumn(GridRunPeer::SYMBOL);
            $criteria->addSelectColumn(GridRunPeer::STATUS);
            $criteria->addSelectColumn(GridRunPeer::KILL_SWITCH);
            $criteria->addSelectColumn(GridRunPeer::PROFILE);
            $criteria->addSelectColumn(GridRunPeer::ALGO);
            $criteria->addSelectColumn(GridRunPeer::SIMULATED);
            $criteria->addSelectColumn(GridRunPeer::P_LOW);
            $criteria->addSelectColumn(GridRunPeer::P_HIGH);
            $criteria->addSelectColumn(GridRunPeer::N_LEVELS);
            $criteria->addSelectColumn(GridRunPeer::SPACING);
            $criteria->addSelectColumn(GridRunPeer::ALLOCATION);
            $criteria->addSelectColumn(GridRunPeer::BUDGET_QUOTE);
            $criteria->addSelectColumn(GridRunPeer::DEPLOY_PCT);
            $criteria->addSelectColumn(GridRunPeer::ALLOC_MODE);
            $criteria->addSelectColumn(GridRunPeer::FEE_PCT);
            $criteria->addSelectColumn(GridRunPeer::MAX_POSITION_QUOTE);
            $criteria->addSelectColumn(GridRunPeer::MAX_ORDER_QUOTE);
            $criteria->addSelectColumn(GridRunPeer::DAILY_LOSS_LIMIT_QUOTE);
            $criteria->addSelectColumn(GridRunPeer::MAX_UNREALIZED_LOSS_QUOTE);
            $criteria->addSelectColumn(GridRunPeer::SELL_AT_LOSS);
            $criteria->addSelectColumn(GridRunPeer::SELL_WHEN_STARVED);
            $criteria->addSelectColumn(GridRunPeer::BREAKOUT_BUFFER_PCT);
            $criteria->addSelectColumn(GridRunPeer::BREAKOUT_POLICY);
            $criteria->addSelectColumn(GridRunPeer::MAX_OPEN_ORDERS);
            $criteria->addSelectColumn(GridRunPeer::MAX_BUY_LEVELS_BELOW);
            $criteria->addSelectColumn(GridRunPeer::TREND_TF);
            $criteria->addSelectColumn(GridRunPeer::DONCHIAN_PERIOD);
            $criteria->addSelectColumn(GridRunPeer::TREND_EMA_FAST);
            $criteria->addSelectColumn(GridRunPeer::TREND_EMA_SLOW);
            $criteria->addSelectColumn(GridRunPeer::ATR_PERIOD);
            $criteria->addSelectColumn(GridRunPeer::ATR_STOP_MULT);
            $criteria->addSelectColumn(GridRunPeer::ATR_INITIAL_MULT);
            $criteria->addSelectColumn(GridRunPeer::TREND_STOP_FLOOR_PCT);
            $criteria->addSelectColumn(GridRunPeer::TREND_SIGNAL);
            $criteria->addSelectColumn(GridRunPeer::REENTRY_COOLDOWN);
            $criteria->addSelectColumn(GridRunPeer::ENGINE_STATE);
            $criteria->addSelectColumn(GridRunPeer::LAST_TICK_AT);
            $criteria->addSelectColumn(GridRunPeer::LAST_PRICE);
            $criteria->addSelectColumn(GridRunPeer::BAL_BASE);
            $criteria->addSelectColumn(GridRunPeer::BAL_QUOTE);
            $criteria->addSelectColumn(GridRunPeer::SIM_BAL_BASE);
            $criteria->addSelectColumn(GridRunPeer::SIM_BAL_QUOTE);
            $criteria->addSelectColumn(GridRunPeer::RUN_UID);
            $criteria->addSelectColumn(GridRunPeer::APPLIED_GEOMETRY);
            $criteria->addSelectColumn(GridRunPeer::LEDGER_RESET_AT);
            $criteria->addSelectColumn(GridRunPeer::DATE_CREATION);
            $criteria->addSelectColumn(GridRunPeer::DATE_MODIFICATION);
            $criteria->addSelectColumn(GridRunPeer::ID_GROUP_CREATION);
            $criteria->addSelectColumn(GridRunPeer::ID_CREATION);
            $criteria->addSelectColumn(GridRunPeer::ID_MODIFICATION);
        } else {
            $criteria->addSelectColumn($alias . '.id_grid_run');
            $criteria->addSelectColumn($alias . '.label');
            $criteria->addSelectColumn($alias . '.symbol');
            $criteria->addSelectColumn($alias . '.status');
            $criteria->addSelectColumn($alias . '.kill_switch');
            $criteria->addSelectColumn($alias . '.profile');
            $criteria->addSelectColumn($alias . '.algo');
            $criteria->addSelectColumn($alias . '.simulated');
            $criteria->addSelectColumn($alias . '.p_low');
            $criteria->addSelectColumn($alias . '.p_high');
            $criteria->addSelectColumn($alias . '.n_levels');
            $criteria->addSelectColumn($alias . '.spacing');
            $criteria->addSelectColumn($alias . '.allocation');
            $criteria->addSelectColumn($alias . '.budget_quote');
            $criteria->addSelectColumn($alias . '.deploy_pct');
            $criteria->addSelectColumn($alias . '.alloc_mode');
            $criteria->addSelectColumn($alias . '.fee_pct');
            $criteria->addSelectColumn($alias . '.max_position_quote');
            $criteria->addSelectColumn($alias . '.max_order_quote');
            $criteria->addSelectColumn($alias . '.daily_loss_limit_quote');
            $criteria->addSelectColumn($alias . '.max_unrealized_loss_quote');
            $criteria->addSelectColumn($alias . '.sell_at_loss');
            $criteria->addSelectColumn($alias . '.sell_when_starved');
            $criteria->addSelectColumn($alias . '.breakout_buffer_pct');
            $criteria->addSelectColumn($alias . '.breakout_policy');
            $criteria->addSelectColumn($alias . '.max_open_orders');
            $criteria->addSelectColumn($alias . '.max_buy_levels_below');
            $criteria->addSelectColumn($alias . '.trend_tf');
            $criteria->addSelectColumn($alias . '.donchian_period');
            $criteria->addSelectColumn($alias . '.trend_ema_fast');
            $criteria->addSelectColumn($alias . '.trend_ema_slow');
            $criteria->addSelectColumn($alias . '.atr_period');
            $criteria->addSelectColumn($alias . '.atr_stop_mult');
            $criteria->addSelectColumn($alias . '.atr_initial_mult');
            $criteria->addSelectColumn($alias . '.trend_stop_floor_pct');
            $criteria->addSelectColumn($alias . '.trend_signal');
            $criteria->addSelectColumn($alias . '.reentry_cooldown');
            $criteria->addSelectColumn($alias . '.engine_state');
            $criteria->addSelectColumn($alias . '.last_tick_at');
            $criteria->addSelectColumn($alias . '.last_price');
            $criteria->addSelectColumn($alias . '.bal_base');
            $criteria->addSelectColumn($alias . '.bal_quote');
            $criteria->addSelectColumn($alias . '.sim_bal_base');
            $criteria->addSelectColumn($alias . '.sim_bal_quote');
            $criteria->addSelectColumn($alias . '.run_uid');
            $criteria->addSelectColumn($alias . '.applied_geometry');
            $criteria->addSelectColumn($alias . '.ledger_reset_at');
            $criteria->addSelectColumn($alias . '.date_creation');
            $criteria->addSelectColumn($alias . '.date_modification');
            $criteria->addSelectColumn($alias . '.id_group_creation');
            $criteria->addSelectColumn($alias . '.id_creation');
            $criteria->addSelectColumn($alias . '.id_modification');
        }
    }

    /**
     * Returns the number of rows matching criteria.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @return int Number of matching rows.
     */
    public static function doCount(Criteria $criteria, $distinct = false, ?PropelPDO $con = null)
    {
        // we may modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(GridRunPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            GridRunPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count
        $criteria->setDbName(GridRunPeer::DATABASE_NAME); // Set the correct dbName

        if ($con === null) {
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }
        // BasePeer returns a PDOStatement
        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }
    /**
     * Selects one object from the DB.
     *
     * @param      Criteria $criteria object used to create the SELECT statement.
     * @param      PropelPDO $con
     * @return GridRun
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectOne(Criteria $criteria, ?PropelPDO $con = null)
    {
        $critcopy = clone $criteria;
        $critcopy->setLimit(1);
        $objects = GridRunPeer::doSelect($critcopy, $con);
        if ($objects) {
            return $objects[0];
        }

        return null;
    }
    /**
     * Selects several row from the DB.
     *
     * @param      Criteria $criteria The Criteria object used to build the SELECT statement.
     * @param      PropelPDO $con
     * @return array           Array of selected Objects
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelect(Criteria $criteria, ?PropelPDO $con = null)
    {
        return GridRunPeer::populateObjects(GridRunPeer::doSelectStmt($criteria, $con));
    }
    /**
     * Prepares the Criteria object and uses the parent doSelect() method to execute a PDOStatement.
     *
     * Use this method directly if you want to work with an executed statement directly (for example
     * to perform your own object hydration).
     *
     * @param      Criteria $criteria The Criteria object used to build the SELECT statement.
     * @param      PropelPDO $con The connection to use
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     * @return PDOStatement The executed PDOStatement object.
     * @see        BasePeer::doSelect()
     */
    public static function doSelectStmt(Criteria $criteria, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        if (!$criteria->hasSelectClause()) {
            $criteria = clone $criteria;
            GridRunPeer::addSelectColumns($criteria);
        }

        // Set the correct dbName
        $criteria->setDbName(GridRunPeer::DATABASE_NAME);

        // BasePeer returns a PDOStatement
        return BasePeer::doSelect($criteria, $con);
    }
    /**
     * Adds an object to the instance pool.
     *
     * Propel keeps cached copies of objects in an instance pool when they are retrieved
     * from the database.  In some cases -- especially when you override doSelect*()
     * methods in your stub classes -- you may need to explicitly add objects
     * to the cache in order to ensure that the same objects are always returned by doSelect*()
     * and retrieveByPK*() calls.
     *
     * @param GridRun $obj A GridRun object.
     * @param      string $key (optional) key to use for instance map (for performance boost if key was already calculated externally).
     */
    public static function addInstanceToPool($obj, $key = null)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if ($key === null) {
                $key = (string) $obj->getIdGridRun();
            } // if key === null
            GridRunPeer::$instances[$key] = $obj;
        }
    }

    /**
     * Removes an object from the instance pool.
     *
     * Propel keeps cached copies of objects in an instance pool when they are retrieved
     * from the database.  In some cases -- especially when you override doDelete
     * methods in your stub classes -- you may need to explicitly remove objects
     * from the cache in order to prevent returning objects that no longer exist.
     *
     * @param      mixed $value A GridRun object or a primary key value.
     *
     * @return void
     * @throws PropelException - if the value is invalid.
     */
    public static function removeInstanceFromPool($value)
    {
        if (Propel::isInstancePoolingEnabled() && $value !== null) {
            if (is_object($value) && $value instanceof GridRun) {
                $key = (string) $value->getIdGridRun();
            } elseif (is_scalar($value)) {
                // assume we've been passed a primary key
                $key = (string) $value;
            } else {
                $e = new PropelException("Invalid value passed to removeInstanceFromPool().  Expected primary key or GridRun object; got " . (is_object($value) ? get_class($value) . ' object.' : var_export($value,true)));
                throw $e;
            }

            unset(GridRunPeer::$instances[$key]);
        }
    } // removeInstanceFromPool()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param      string $key The key (@see getPrimaryKeyHash()) for this instance.
     * @return GridRun Found object or null if 1) no instance exists for specified key or 2) instance pooling has been disabled.
     * @see        getPrimaryKeyHash()
     */
    public static function getInstanceFromPool($key)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if (isset(GridRunPeer::$instances[$key])) {
                return GridRunPeer::$instances[$key];
            }
        }

        return null; // just to be explicit
    }

    /**
     * Clear the instance pool.
     *
     * @return void
     */
    public static function clearInstancePool($and_clear_all_references = false)
    {
      if ($and_clear_all_references) {
        foreach (GridRunPeer::$instances as $instance) {
          $instance->clearAllReferences(true);
        }
      }
        GridRunPeer::$instances = array();
    }

    /**
     * Method to invalidate the instance pool of all tables related to grid_run
     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool()
    {
        // Invalidate objects in FleetSlotPeer instance pool,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        FleetSlotPeer::clearInstancePool();
        // Invalidate objects in RegimeEpisodePeer instance pool,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        RegimeEpisodePeer::clearInstancePool();
        // Invalidate objects in BotOrderPeer instance pool,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        BotOrderPeer::clearInstancePool();
        // Invalidate objects in TradeCyclePeer instance pool,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        TradeCyclePeer::clearInstancePool();
        // Invalidate objects in BotEventPeer instance pool,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        BotEventPeer::clearInstancePool();
        // Invalidate objects in BotCommandPeer instance pool,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        BotCommandPeer::clearInstancePool();
        // Invalidate objects in BotDecisionPeer instance pool,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        BotDecisionPeer::clearInstancePool();
        // Invalidate objects in GridRunAuditPeer instance pool,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        GridRunAuditPeer::clearInstancePool();
    }

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param      array $row PropelPDO resultset row.
     * @param      int $startcol The 0-based offset for reading from the resultset row.
     * @return string A string version of PK or null if the components of primary key in result array are all null.
     */
    public static function getPrimaryKeyHashFromRow($row, $startcol = 0)
    {
        // If the PK cannot be derived from the row, return null.
        if ($row[$startcol] === null) {
            return null;
        }

        return (string) $row[$startcol];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param      array $row PropelPDO resultset row.
     * @param      int $startcol The 0-based offset for reading from the resultset row.
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $startcol = 0)
    {

        return (int) $row[$startcol];
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function populateObjects(PDOStatement $stmt)
    {
        $results = array();

        // set the class once to avoid overhead in the loop
        $cls = GridRunPeer::getOMClass();
        // populate the object(s)
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key = GridRunPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj = GridRunPeer::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                GridRunPeer::addInstanceToPool($obj, $key);
            } // if key exists
        }
        $stmt->closeCursor();

        return $results;
    }
    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param      array $row PropelPDO resultset row.
     * @param      int $startcol The 0-based offset for reading from the resultset row.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     * @return array (GridRun object, last column rank)
     */
    public static function populateObject($row, $startcol = 0)
    {
        $key = GridRunPeer::getPrimaryKeyHashFromRow($row, $startcol);
        if (null !== ($obj = GridRunPeer::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $startcol, true); // rehydrate
            $col = $startcol + GridRunPeer::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = GridRunPeer::OM_CLASS;
            $obj = new $cls();
            $col = $obj->hydrate($row, $startcol);
            GridRunPeer::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * Gets the SQL value for Status ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getStatusSqlValue($enumVal)
    {
        return GridRunPeer::getSqlValueForEnum(GridRunPeer::STATUS, $enumVal);
    }

    /**
     * Gets the SQL value for Profile ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getProfileSqlValue($enumVal)
    {
        return GridRunPeer::getSqlValueForEnum(GridRunPeer::PROFILE, $enumVal);
    }

    /**
     * Gets the SQL value for Algo ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getAlgoSqlValue($enumVal)
    {
        return GridRunPeer::getSqlValueForEnum(GridRunPeer::ALGO, $enumVal);
    }

    /**
     * Gets the SQL value for Spacing ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getSpacingSqlValue($enumVal)
    {
        return GridRunPeer::getSqlValueForEnum(GridRunPeer::SPACING, $enumVal);
    }

    /**
     * Gets the SQL value for Allocation ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getAllocationSqlValue($enumVal)
    {
        return GridRunPeer::getSqlValueForEnum(GridRunPeer::ALLOCATION, $enumVal);
    }

    /**
     * Gets the SQL value for AllocMode ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getAllocModeSqlValue($enumVal)
    {
        return GridRunPeer::getSqlValueForEnum(GridRunPeer::ALLOC_MODE, $enumVal);
    }

    /**
     * Gets the SQL value for BreakoutPolicy ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getBreakoutPolicySqlValue($enumVal)
    {
        return GridRunPeer::getSqlValueForEnum(GridRunPeer::BREAKOUT_POLICY, $enumVal);
    }

    /**
     * Gets the SQL value for TrendTf ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getTrendTfSqlValue($enumVal)
    {
        return GridRunPeer::getSqlValueForEnum(GridRunPeer::TREND_TF, $enumVal);
    }

    /**
     * Gets the SQL value for TrendSignal ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getTrendSignalSqlValue($enumVal)
    {
        return GridRunPeer::getSqlValueForEnum(GridRunPeer::TREND_SIGNAL, $enumVal);
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyGroup table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAuthyGroup(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(GridRunPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            GridRunPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(GridRunPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(GridRunPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyRelatedByIdCreation table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAuthyRelatedByIdCreation(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(GridRunPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            GridRunPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(GridRunPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(GridRunPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyRelatedByIdModification table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAuthyRelatedByIdModification(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(GridRunPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            GridRunPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(GridRunPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(GridRunPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Selects a collection of GridRun objects pre-filled with their AuthyGroup objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of GridRun objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyGroup(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(GridRunPeer::DATABASE_NAME);
        }

        GridRunPeer::addSelectColumns($criteria);
        $startcol = GridRunPeer::NUM_HYDRATE_COLUMNS;
        AuthyGroupPeer::addSelectColumns($criteria);

        $criteria->addJoin(GridRunPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = GridRunPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = GridRunPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = GridRunPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                GridRunPeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = AuthyGroupPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = AuthyGroupPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    AuthyGroupPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (GridRun) to $obj2 (AuthyGroup)
                $obj2->addGridRun($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of GridRun objects pre-filled with their Authy objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of GridRun objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyRelatedByIdCreation(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(GridRunPeer::DATABASE_NAME);
        }

        GridRunPeer::addSelectColumns($criteria);
        $startcol = GridRunPeer::NUM_HYDRATE_COLUMNS;
        AuthyPeer::addSelectColumns($criteria);

        $criteria->addJoin(GridRunPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = GridRunPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = GridRunPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = GridRunPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                GridRunPeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = AuthyPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = AuthyPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    AuthyPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (GridRun) to $obj2 (Authy)
                $obj2->addGridRunRelatedByIdCreation($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of GridRun objects pre-filled with their Authy objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of GridRun objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyRelatedByIdModification(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(GridRunPeer::DATABASE_NAME);
        }

        GridRunPeer::addSelectColumns($criteria);
        $startcol = GridRunPeer::NUM_HYDRATE_COLUMNS;
        AuthyPeer::addSelectColumns($criteria);

        $criteria->addJoin(GridRunPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = GridRunPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = GridRunPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = GridRunPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                GridRunPeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = AuthyPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = AuthyPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    AuthyPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (GridRun) to $obj2 (Authy)
                $obj2->addGridRunRelatedByIdModification($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Returns the number of rows matching criteria, joining all related tables
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAll(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(GridRunPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            GridRunPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(GridRunPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(GridRunPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(GridRunPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(GridRunPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }

    /**
     * Selects a collection of GridRun objects pre-filled with all related objects.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of GridRun objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAll(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(GridRunPeer::DATABASE_NAME);
        }

        GridRunPeer::addSelectColumns($criteria);
        $startcol2 = GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(GridRunPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(GridRunPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(GridRunPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = GridRunPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = GridRunPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = GridRunPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                GridRunPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

            // Add objects for joined AuthyGroup rows

            $key2 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol2);
            if ($key2 !== null) {
                $obj2 = AuthyGroupPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = AuthyGroupPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    AuthyGroupPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 loaded

                // Add the $obj1 (GridRun) to the collection in $obj2 (AuthyGroup)
                $obj2->addGridRun($obj1);
            } // if joined row not null

            // Add objects for joined Authy rows

            $key3 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol3);
            if ($key3 !== null) {
                $obj3 = AuthyPeer::getInstanceFromPool($key3);
                if (!$obj3) {

                    $cls = AuthyPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    AuthyPeer::addInstanceToPool($obj3, $key3);
                } // if obj3 loaded

                // Add the $obj1 (GridRun) to the collection in $obj3 (Authy)
                $obj3->addGridRunRelatedByIdCreation($obj1);
            } // if joined row not null

            // Add objects for joined Authy rows

            $key4 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol4);
            if ($key4 !== null) {
                $obj4 = AuthyPeer::getInstanceFromPool($key4);
                if (!$obj4) {

                    $cls = AuthyPeer::getOMClass();

                    $obj4 = new $cls();
                    $obj4->hydrate($row, $startcol4);
                    AuthyPeer::addInstanceToPool($obj4, $key4);
                } // if obj4 loaded

                // Add the $obj1 (GridRun) to the collection in $obj4 (Authy)
                $obj4->addGridRunRelatedByIdModification($obj1);
            } // if joined row not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyGroup table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptAuthyGroup(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(GridRunPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            GridRunPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(GridRunPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(GridRunPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(GridRunPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyRelatedByIdCreation table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptAuthyRelatedByIdCreation(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(GridRunPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            GridRunPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(GridRunPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(GridRunPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyRelatedByIdModification table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptAuthyRelatedByIdModification(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(GridRunPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            GridRunPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(GridRunPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(GridRunPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Selects a collection of GridRun objects pre-filled with all related objects except AuthyGroup.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of GridRun objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptAuthyGroup(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(GridRunPeer::DATABASE_NAME);
        }

        GridRunPeer::addSelectColumns($criteria);
        $startcol2 = GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(GridRunPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(GridRunPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = GridRunPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = GridRunPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = GridRunPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                GridRunPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined Authy rows

                $key2 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = AuthyPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = AuthyPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    AuthyPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (GridRun) to the collection in $obj2 (Authy)
                $obj2->addGridRunRelatedByIdCreation($obj1);

            } // if joined row is not null

                // Add objects for joined Authy rows

                $key3 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = AuthyPeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = AuthyPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    AuthyPeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (GridRun) to the collection in $obj3 (Authy)
                $obj3->addGridRunRelatedByIdModification($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of GridRun objects pre-filled with all related objects except AuthyRelatedByIdCreation.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of GridRun objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptAuthyRelatedByIdCreation(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(GridRunPeer::DATABASE_NAME);
        }

        GridRunPeer::addSelectColumns($criteria);
        $startcol2 = GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(GridRunPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = GridRunPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = GridRunPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = GridRunPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                GridRunPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined AuthyGroup rows

                $key2 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = AuthyGroupPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = AuthyGroupPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    AuthyGroupPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (GridRun) to the collection in $obj2 (AuthyGroup)
                $obj2->addGridRun($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of GridRun objects pre-filled with all related objects except AuthyRelatedByIdModification.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of GridRun objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptAuthyRelatedByIdModification(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(GridRunPeer::DATABASE_NAME);
        }

        GridRunPeer::addSelectColumns($criteria);
        $startcol2 = GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(GridRunPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = GridRunPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = GridRunPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = GridRunPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                GridRunPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined AuthyGroup rows

                $key2 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = AuthyGroupPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = AuthyGroupPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    AuthyGroupPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (GridRun) to the collection in $obj2 (AuthyGroup)
                $obj2->addGridRun($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }

    /**
     * Returns the TableMap related to this peer.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getDatabaseMap(GridRunPeer::DATABASE_NAME)->getTable(GridRunPeer::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this peer class.
     */
    public static function buildTableMap()
    {
      $dbMap = Propel::getDatabaseMap(BaseGridRunPeer::DATABASE_NAME);
      if (!$dbMap->hasTable(BaseGridRunPeer::TABLE_NAME)) {
        $dbMap->addTableObject(new \App\map\GridRunTableMap());
      }
    }

    /**
     * The class that the Peer will make instances of.
     *
     *
     * @return string ClassName
     */
    public static function getOMClass($row = 0, $colnum = 0)
    {
        return GridRunPeer::OM_CLASS;
    }

    /**
     * Performs an INSERT on the database, given a GridRun or Criteria object.
     *
     * @param      mixed $values Criteria or GridRun object containing data that is used to create the INSERT statement.
     * @param      PropelPDO $con the PropelPDO connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doInsert($values, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity
        } else {
            $criteria = $values->buildCriteria(); // build Criteria from GridRun object
        }

        if ($criteria->containsKey(GridRunPeer::ID_GRID_RUN) && $criteria->keyContainsValue(GridRunPeer::ID_GRID_RUN) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.GridRunPeer::ID_GRID_RUN.')');
        }


        // Set the correct dbName
        $criteria->setDbName(GridRunPeer::DATABASE_NAME);

        try {
            // use transaction because $criteria could contain info
            // for more than one table (I guess, conceivably)
            $con->beginTransaction();
            $pk = BasePeer::doInsert($criteria, $con);
            $con->commit();
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }

        return $pk;
    }

    /**
     * Performs an UPDATE on the database, given a GridRun or Criteria object.
     *
     * @param      mixed $values Criteria or GridRun object containing data that is used to create the UPDATE statement.
     * @param      PropelPDO $con The connection to use (specify PropelPDO connection object to exert more control over transactions).
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doUpdate($values, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $selectCriteria = new Criteria(GridRunPeer::DATABASE_NAME);

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity

            $comparison = $criteria->getComparison(GridRunPeer::ID_GRID_RUN);
            $value = $criteria->remove(GridRunPeer::ID_GRID_RUN);
            if ($value) {
                $selectCriteria->add(GridRunPeer::ID_GRID_RUN, $value, $comparison);
            } else {
                $selectCriteria->setPrimaryTableName(GridRunPeer::TABLE_NAME);
            }

        } else { // $values is GridRun object
            $criteria = $values->buildCriteria(); // gets full criteria
            $selectCriteria = $values->buildPkeyCriteria(); // gets criteria w/ primary key(s)
        }

        // set the correct dbName
        $criteria->setDbName(GridRunPeer::DATABASE_NAME);

        return BasePeer::doUpdate($selectCriteria, $criteria, $con);
    }

    /**
     * Deletes all rows from the grid_run table.
     *
     * @param      PropelPDO $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException
     */
    public static function doDeleteAll(?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += BasePeer::doDeleteAll(GridRunPeer::TABLE_NAME, $con, GridRunPeer::DATABASE_NAME);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            GridRunPeer::clearInstancePool();
            GridRunPeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs a DELETE on the database, given a GridRun or Criteria object OR a primary key value.
     *
     * @param      mixed $values Criteria or GridRun object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param      PropelPDO $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *				if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ?PropelPDO $con = null)
     {
        if ($con === null) {
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            // invalidate the cache for all objects of this type, since we have no
            // way of knowing (without running a query) what objects should be invalidated
            // from the cache based on this Criteria.
            GridRunPeer::clearInstancePool();
            // rename for clarity
            $criteria = clone $values;
        } elseif ($values instanceof GridRun) { // it's a model object
            // invalidate the cache for this single object
            GridRunPeer::removeInstanceFromPool($values);
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(GridRunPeer::DATABASE_NAME);
            $criteria->add(GridRunPeer::ID_GRID_RUN, (array) $values, Criteria::IN);
            // invalidate the cache for this object(s)
            foreach ((array) $values as $singleval) {
                GridRunPeer::removeInstanceFromPool($singleval);
            }
        }

        // Set the correct dbName
        $criteria->setDbName(GridRunPeer::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();

            $affectedRows += BasePeer::doDelete($criteria, $con);
            GridRunPeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Validates all modified columns of given GridRun object.
     * If parameter $columns is either a single column name or an array of column names
     * than only those columns are validated.
     *
     * NOTICE: This does not apply to primary or foreign keys for now.
     *
     * @param GridRun $obj The object to validate.
     * @param      mixed $cols Column name or array of column names.
     *
     * @return mixed TRUE if all columns are valid or the error message of the first invalid column.
     */
    public static function doValidate($obj, $cols = null)
    {
        $columns = array();

        if ($cols) {
            $dbMap = Propel::getDatabaseMap(GridRunPeer::DATABASE_NAME);
            $tableMap = $dbMap->getTable(GridRunPeer::TABLE_NAME);

            if (! is_array($cols)) {
                $cols = array($cols);
            }

            foreach ($cols as $colName) {
                if ($tableMap->hasColumn($colName)) {
                    $get = 'get' . $tableMap->getColumn($colName)->getPhpName();
                    $columns[$colName] = $obj->$get();
                }
            }
        } else {

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::LABEL))
            $columns[GridRunPeer::LABEL] = $obj->getLabel();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::P_LOW))
            $columns[GridRunPeer::P_LOW] = $obj->getPLow();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::P_HIGH))
            $columns[GridRunPeer::P_HIGH] = $obj->getPHigh();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::BUDGET_QUOTE))
            $columns[GridRunPeer::BUDGET_QUOTE] = $obj->getBudgetQuote();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::LABEL))
            $columns[GridRunPeer::LABEL] = $obj->getLabel();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::SYMBOL))
            $columns[GridRunPeer::SYMBOL] = $obj->getSymbol();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::SYMBOL))
            $columns[GridRunPeer::SYMBOL] = $obj->getSymbol();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::STATUS))
            $columns[GridRunPeer::STATUS] = $obj->getStatus();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::STATUS))
            $columns[GridRunPeer::STATUS] = $obj->getStatus();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::PROFILE))
            $columns[GridRunPeer::PROFILE] = $obj->getProfile();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::PROFILE))
            $columns[GridRunPeer::PROFILE] = $obj->getProfile();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::ALGO))
            $columns[GridRunPeer::ALGO] = $obj->getAlgo();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::ALGO))
            $columns[GridRunPeer::ALGO] = $obj->getAlgo();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::N_LEVELS))
            $columns[GridRunPeer::N_LEVELS] = $obj->getNLevels();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::N_LEVELS))
            $columns[GridRunPeer::N_LEVELS] = $obj->getNLevels();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::SPACING))
            $columns[GridRunPeer::SPACING] = $obj->getSpacing();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::SPACING))
            $columns[GridRunPeer::SPACING] = $obj->getSpacing();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::ALLOCATION))
            $columns[GridRunPeer::ALLOCATION] = $obj->getAllocation();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::ALLOCATION))
            $columns[GridRunPeer::ALLOCATION] = $obj->getAllocation();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::DEPLOY_PCT))
            $columns[GridRunPeer::DEPLOY_PCT] = $obj->getDeployPct();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::ALLOC_MODE))
            $columns[GridRunPeer::ALLOC_MODE] = $obj->getAllocMode();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::ALLOC_MODE))
            $columns[GridRunPeer::ALLOC_MODE] = $obj->getAllocMode();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::FEE_PCT))
            $columns[GridRunPeer::FEE_PCT] = $obj->getFeePct();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::MAX_POSITION_QUOTE))
            $columns[GridRunPeer::MAX_POSITION_QUOTE] = $obj->getMaxPositionQuote();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::MAX_ORDER_QUOTE))
            $columns[GridRunPeer::MAX_ORDER_QUOTE] = $obj->getMaxOrderQuote();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::DAILY_LOSS_LIMIT_QUOTE))
            $columns[GridRunPeer::DAILY_LOSS_LIMIT_QUOTE] = $obj->getDailyLossLimitQuote();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::BREAKOUT_POLICY))
            $columns[GridRunPeer::BREAKOUT_POLICY] = $obj->getBreakoutPolicy();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::BREAKOUT_POLICY))
            $columns[GridRunPeer::BREAKOUT_POLICY] = $obj->getBreakoutPolicy();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::MAX_OPEN_ORDERS))
            $columns[GridRunPeer::MAX_OPEN_ORDERS] = $obj->getMaxOpenOrders();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::MAX_BUY_LEVELS_BELOW))
            $columns[GridRunPeer::MAX_BUY_LEVELS_BELOW] = $obj->getMaxBuyLevelsBelow();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::TREND_TF))
            $columns[GridRunPeer::TREND_TF] = $obj->getTrendTf();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::TREND_TF))
            $columns[GridRunPeer::TREND_TF] = $obj->getTrendTf();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::DONCHIAN_PERIOD))
            $columns[GridRunPeer::DONCHIAN_PERIOD] = $obj->getDonchianPeriod();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::TREND_EMA_FAST))
            $columns[GridRunPeer::TREND_EMA_FAST] = $obj->getTrendEmaFast();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::TREND_EMA_SLOW))
            $columns[GridRunPeer::TREND_EMA_SLOW] = $obj->getTrendEmaSlow();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::ATR_PERIOD))
            $columns[GridRunPeer::ATR_PERIOD] = $obj->getAtrPeriod();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::TREND_SIGNAL))
            $columns[GridRunPeer::TREND_SIGNAL] = $obj->getTrendSignal();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::TREND_SIGNAL))
            $columns[GridRunPeer::TREND_SIGNAL] = $obj->getTrendSignal();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::REENTRY_COOLDOWN))
            $columns[GridRunPeer::REENTRY_COOLDOWN] = $obj->getReentryCooldown();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::ENGINE_STATE))
            $columns[GridRunPeer::ENGINE_STATE] = $obj->getEngineState();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::LAST_TICK_AT))
            $columns[GridRunPeer::LAST_TICK_AT] = $obj->getLastTickAt();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::RUN_UID))
            $columns[GridRunPeer::RUN_UID] = $obj->getRunUid();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::APPLIED_GEOMETRY))
            $columns[GridRunPeer::APPLIED_GEOMETRY] = $obj->getAppliedGeometry();

        if ($obj->isNew() || $obj->isColumnModified(GridRunPeer::LEDGER_RESET_AT))
            $columns[GridRunPeer::LEDGER_RESET_AT] = $obj->getLedgerResetAt();

        }

        return BasePeer::doValidate(GridRunPeer::DATABASE_NAME, GridRunPeer::TABLE_NAME, $columns);
    }

    /**
     * Retrieve a single object by pkey.
     *
     * @param int $pk the primary key.
     * @param      PropelPDO $con the connection to use
     * @return GridRun
     */
    public static function retrieveByPK($pk, ?PropelPDO $con = null)
    {

        if (null !== ($obj = GridRunPeer::getInstanceFromPool((string) $pk))) {
            return $obj;
        }

        if ($con === null) {
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria = new Criteria(GridRunPeer::DATABASE_NAME);
        $criteria->add(GridRunPeer::ID_GRID_RUN, $pk);

        $v = GridRunPeer::doSelect($criteria, $con);

        return !empty($v) > 0 ? $v[0] : null;
    }

    /**
     * Retrieve multiple objects by pkey.
     *
     * @param      array $pks List of primary keys
     * @param      PropelPDO $con the connection to use
     * @return GridRun[]
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function retrieveByPKs($pks, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $objs = null;
        if (empty($pks)) {
            $objs = array();
        } else {
            $criteria = new Criteria(GridRunPeer::DATABASE_NAME);
            $criteria->add(GridRunPeer::ID_GRID_RUN, $pks, Criteria::IN);
            $objs = GridRunPeer::doSelect($criteria, $con);
        }

        return $objs;
    }

} // BaseGridRunPeer

// This is the static code needed to register the TableMap for this table with the main Propel class.
//
BaseGridRunPeer::buildTableMap();

