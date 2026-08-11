<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'grid_run' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    propel.generator..map
 */
class GridRunTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.GridRunTableMap';

    /**
     * Initialize the table attributes, columns and validators
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('grid_run');
        $this->setPhpName('GridRun');
        $this->setClassname('App\\GridRun');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_grid_run', 'IdGridRun', 'INTEGER', true, 11, null);
        $this->addColumn('label', 'Label', 'VARCHAR', true, 100, null);
        $this->addColumn('symbol', 'Symbol', 'VARCHAR', true, 20, 'BTCUSDT');
        $this->addColumn('status', 'Status', 'ENUM', true, null, 'Draft');
        $this->getColumn('status', false)->setValueSet(array (
  0 => 'Draft',
  1 => 'DryRun',
  2 => 'Testnet',
  3 => 'Live',
  4 => 'Halted',
  5 => 'Done',
));
        $this->addColumn('kill_switch', 'KillSwitch', 'BOOLEAN', false, 10, false);
        $this->addColumn('profile', 'Profile', 'ENUM', true, null, 'Balanced');
        $this->getColumn('profile', false)->setValueSet(array (
  0 => 'NoLoss',
  1 => 'Cautious',
  2 => 'Balanced',
  3 => 'Aggressive',
  4 => 'Max',
));
        $this->addColumn('algo', 'Algo', 'ENUM', true, null, 'Grid');
        $this->getColumn('algo', false)->setValueSet(array (
  0 => 'Grid',
  1 => 'Trend',
));
        $this->addColumn('simulated', 'Simulated', 'BOOLEAN', false, 10, true);
        $this->addColumn('p_low', 'PLow', 'DECIMAL', true, 18, null);
        $this->addColumn('p_high', 'PHigh', 'DECIMAL', true, 18, null);
        $this->addColumn('n_levels', 'NLevels', 'INTEGER', true, 10, 20);
        $this->addColumn('spacing', 'Spacing', 'ENUM', true, null, 'Geometric');
        $this->getColumn('spacing', false)->setValueSet(array (
  0 => 'Geometric',
  1 => 'Arithmetic',
));
        $this->addColumn('allocation', 'Allocation', 'ENUM', true, null, 'EqualQuote');
        $this->getColumn('allocation', false)->setValueSet(array (
  0 => 'EqualQuote',
  1 => 'EqualBase',
  2 => 'BottomWeighted',
));
        $this->addColumn('budget_quote', 'BudgetQuote', 'DECIMAL', true, 18, null);
        $this->addColumn('deploy_pct', 'DeployPct', 'INTEGER', false, 10, 100);
        $this->addColumn('fee_pct', 'FeePct', 'DECIMAL', true, 9, 0.001);
        $this->addColumn('max_position_quote', 'MaxPositionQuote', 'DECIMAL', true, 18, null);
        $this->addColumn('max_order_quote', 'MaxOrderQuote', 'DECIMAL', true, 18, null);
        $this->addColumn('daily_loss_limit_quote', 'DailyLossLimitQuote', 'DECIMAL', true, 18, null);
        $this->addColumn('max_unrealized_loss_quote', 'MaxUnrealizedLossQuote', 'DECIMAL', false, 18, null);
        $this->addColumn('breakout_buffer_pct', 'BreakoutBufferPct', 'DECIMAL', false, 9, 0.02);
        $this->addColumn('breakout_policy', 'BreakoutPolicy', 'ENUM', true, null, 'HaltAndHold');
        $this->getColumn('breakout_policy', false)->setValueSet(array (
  0 => 'HaltAndHold',
  1 => 'Flatten',
));
        $this->addColumn('max_open_orders', 'MaxOpenOrders', 'INTEGER', false, 10, 60);
        $this->addColumn('max_buy_levels_below', 'MaxBuyLevelsBelow', 'INTEGER', false, 10, null);
        $this->addColumn('trend_tf', 'TrendTf', 'ENUM', true, null, '1h');
        $this->getColumn('trend_tf', false)->setValueSet(array (
  0 => '1h',
  1 => '4h',
));
        $this->addColumn('donchian_period', 'DonchianPeriod', 'INTEGER', false, 10, 20);
        $this->addColumn('trend_ema_fast', 'TrendEmaFast', 'INTEGER', false, 10, 20);
        $this->addColumn('trend_ema_slow', 'TrendEmaSlow', 'INTEGER', false, 10, 50);
        $this->addColumn('atr_period', 'AtrPeriod', 'INTEGER', false, 10, 14);
        $this->addColumn('atr_stop_mult', 'AtrStopMult', 'DECIMAL', false, 9, null);
        $this->addColumn('atr_initial_mult', 'AtrInitialMult', 'DECIMAL', false, 9, null);
        $this->addColumn('reentry_cooldown', 'ReentryCooldown', 'INTEGER', false, 10, null);
        $this->addColumn('engine_state', 'EngineState', 'LONGVARCHAR', false, 1023, null);
        $this->addColumn('last_tick_at', 'LastTickAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('last_price', 'LastPrice', 'DECIMAL', false, 18, null);
        $this->addColumn('bal_base', 'BalBase', 'DECIMAL', false, 18, null);
        $this->addColumn('bal_quote', 'BalQuote', 'DECIMAL', false, 18, null);
        $this->addColumn('sim_bal_base', 'SimBalBase', 'DECIMAL', false, 18, null);
        $this->addColumn('sim_bal_quote', 'SimBalQuote', 'DECIMAL', false, 18, null);
        $this->addColumn('run_uid', 'RunUid', 'VARCHAR', false, 20, null);
        $this->addColumn('applied_geometry', 'AppliedGeometry', 'LONGVARCHAR', false, 1023, null);
        $this->addColumn('ledger_reset_at', 'LedgerResetAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('label', 'required', 'propel.validator.RequiredValidator', '', 'grid_run_label_required');
        $this->addValidator('p_low', 'required', 'propel.validator.RequiredValidator', '', 'grid_run_p_low_required');
        $this->addValidator('p_high', 'required', 'propel.validator.RequiredValidator', '', 'grid_run_p_high_required');
        $this->addValidator('budget_quote', 'required', 'propel.validator.RequiredValidator', '', 'grid_run_budget_required');
        $this->addValidator('id_grid_run', 'required', 'propel.validator.RequiredValidator', '', ('GridRun_IdGridRun_required'));
        $this->addValidator('id_grid_run', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('GridRun_IdGridRun_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('label', 'type', 'propel.validator.TypeValidator', 'string', ('GridRun_Label_type_string'));
        $this->addValidator('symbol', 'required', 'propel.validator.RequiredValidator', '', ('GridRun_Symbol_required'));
        $this->addValidator('symbol', 'type', 'propel.validator.TypeValidator', 'string', ('GridRun_Symbol_type_string'));
        $this->addValidator('status', 'required', 'propel.validator.RequiredValidator', '', ('GridRun_Status_required'));
        $this->addValidator('status', 'type', 'propel.validator.TypeValidator', 'string', ('GridRun_Status_type_string'));
        $this->addValidator('profile', 'required', 'propel.validator.RequiredValidator', '', ('GridRun_Profile_required'));
        $this->addValidator('profile', 'type', 'propel.validator.TypeValidator', 'string', ('GridRun_Profile_type_string'));
        $this->addValidator('algo', 'required', 'propel.validator.RequiredValidator', '', ('GridRun_Algo_required'));
        $this->addValidator('algo', 'type', 'propel.validator.TypeValidator', 'string', ('GridRun_Algo_type_string'));
        $this->addValidator('n_levels', 'required', 'propel.validator.RequiredValidator', '', ('GridRun_NLevels_required'));
        $this->addValidator('n_levels', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('GridRun_NLevels_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('spacing', 'required', 'propel.validator.RequiredValidator', '', ('GridRun_Spacing_required'));
        $this->addValidator('spacing', 'type', 'propel.validator.TypeValidator', 'string', ('GridRun_Spacing_type_string'));
        $this->addValidator('allocation', 'required', 'propel.validator.RequiredValidator', '', ('GridRun_Allocation_required'));
        $this->addValidator('allocation', 'type', 'propel.validator.TypeValidator', 'string', ('GridRun_Allocation_type_string'));
        $this->addValidator('deploy_pct', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('GridRun_DeployPct_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('fee_pct', 'required', 'propel.validator.RequiredValidator', '', ('GridRun_FeePct_required'));
        $this->addValidator('max_position_quote', 'required', 'propel.validator.RequiredValidator', '', ('GridRun_MaxPositionQuote_required'));
        $this->addValidator('max_order_quote', 'required', 'propel.validator.RequiredValidator', '', ('GridRun_MaxOrderQuote_required'));
        $this->addValidator('daily_loss_limit_quote', 'required', 'propel.validator.RequiredValidator', '', ('GridRun_DailyLossLimitQuote_required'));
        $this->addValidator('breakout_policy', 'required', 'propel.validator.RequiredValidator', '', ('GridRun_BreakoutPolicy_required'));
        $this->addValidator('breakout_policy', 'type', 'propel.validator.TypeValidator', 'string', ('GridRun_BreakoutPolicy_type_string'));
        $this->addValidator('max_open_orders', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('GridRun_MaxOpenOrders_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('max_buy_levels_below', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('GridRun_MaxBuyLevelsBelow_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('trend_tf', 'required', 'propel.validator.RequiredValidator', '', ('GridRun_TrendTf_required'));
        $this->addValidator('trend_tf', 'type', 'propel.validator.TypeValidator', 'string', ('GridRun_TrendTf_type_string'));
        $this->addValidator('donchian_period', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('GridRun_DonchianPeriod_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('trend_ema_fast', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('GridRun_TrendEmaFast_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('trend_ema_slow', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('GridRun_TrendEmaSlow_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('atr_period', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('GridRun_AtrPeriod_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('reentry_cooldown', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('GridRun_ReentryCooldown_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('engine_state', 'type', 'propel.validator.TypeValidator', 'string', ('GridRun_EngineState_type_string'));
        $this->addValidator('last_tick_at', 'match', 'propel.validator.MatchValidator', '', ('GridRun_LastTickAt_match'));
        $this->addValidator('run_uid', 'type', 'propel.validator.TypeValidator', 'string', ('GridRun_RunUid_type_string'));
        $this->addValidator('applied_geometry', 'type', 'propel.validator.TypeValidator', 'string', ('GridRun_AppliedGeometry_type_string'));
        $this->addValidator('ledger_reset_at', 'match', 'propel.validator.MatchValidator', '', ('GridRun_LedgerResetAt_match'));
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('AuthyGroup', 'App\\AuthyGroup', RelationMap::MANY_TO_ONE, array('id_group_creation' => 'id_authy_group', ), null, null);
        $this->addRelation('AuthyRelatedByIdCreation', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_creation' => 'id_authy', ), null, null);
        $this->addRelation('AuthyRelatedByIdModification', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_modification' => 'id_authy', ), null, null);
        $this->addRelation('BotOrder', 'App\\BotOrder', RelationMap::ONE_TO_MANY, array('id_grid_run' => 'id_grid_run', ), 'CASCADE', null, 'BotOrders');
        $this->addRelation('TradeCycle', 'App\\TradeCycle', RelationMap::ONE_TO_MANY, array('id_grid_run' => 'id_grid_run', ), 'CASCADE', null, 'TradeCycles');
        $this->addRelation('BotEvent', 'App\\BotEvent', RelationMap::ONE_TO_MANY, array('id_grid_run' => 'id_grid_run', ), 'CASCADE', null, 'BotEvents');
        $this->addRelation('BotCommand', 'App\\BotCommand', RelationMap::ONE_TO_MANY, array('id_grid_run' => 'id_grid_run', ), 'CASCADE', null, 'BotCommands');
        $this->addRelation('BotDecision', 'App\\BotDecision', RelationMap::ONE_TO_MANY, array('id_grid_run' => 'id_grid_run', ), 'CASCADE', null, 'BotDecisions');
    } // buildRelations()

    /**
     *
     * Gets the list of behaviors registered for this table
     *
     * @return array Associative array (name => parameters) of behaviors
     */
    public function getBehaviors()
    {
        return array(
            'GoatCheese' =>  array (
  'i18n_langs' => '["en_US"]',
  'logo_url' => '',
  'set_menu_icon' => 'ri-line-chart-line',
  'set_parent_menu' => 'Trading',
  'with_child_tables' => '["bot_order","trade_cycle","bot_event","bot_command"]',
  'add_total' => '{"trade_cycle":[["realized_pnl","$"],["fees_total","$"]]}',
  'set_order_list_columns' => '[["date_creation","DESC"]]',
  'set_list_hide_columns' => '["run_uid","breakout_buffer_pct","max_open_orders","last_tick_at","sim_bal_base","sim_bal_quote","engine_state"]',
  'set_readonly_columns' => '["daily_loss_limit_quote","max_unrealized_loss_quote","max_position_quote","max_order_quote","breakout_policy","atr_stop_mult","atr_initial_mult","reentry_cooldown","engine_state"]',
  'add_tab_columns' => '{"Grid + budget":"p_low","Risk limits":"max_position_quote","Trend settings":"trend_tf","Telemetry":"last_tick_at"}',
),
            'add_validator' =>  array (
),
            'add_tablestamp' =>  array (
  'create_column' => 'date_creation',
  'update_column' => 'date_modification',
  'create_id_column' => 'id_creation',
  'group_id_column' => 'id_group_creation',
  'update_id_column' => 'id_modification',
  'exclude' => 'none',
  'foreign_keys' => 'all',
),
        );
    } // getBehaviors()

} // GridRunTableMap
