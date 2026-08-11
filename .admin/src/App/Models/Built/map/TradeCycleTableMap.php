<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'trade_cycle' table.
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
class TradeCycleTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.TradeCycleTableMap';

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
        $this->setName('trade_cycle');
        $this->setPhpName('TradeCycle');
        $this->setClassname('App\\TradeCycle');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_trade_cycle', 'IdTradeCycle', 'INTEGER', true, 11, null);
        $this->addForeignKey('id_grid_run', 'IdGridRun', 'INTEGER', 'grid_run', 'id_grid_run', true, 11, null);
        $this->addColumn('level_idx', 'LevelIdx', 'INTEGER', true, 10, null);
        $this->addColumn('buy_price', 'BuyPrice', 'DECIMAL', true, 18, null);
        $this->addColumn('sell_price', 'SellPrice', 'DECIMAL', true, 18, null);
        $this->addColumn('qty', 'Qty', 'DECIMAL', true, 18, null);
        $this->addColumn('realized_pnl', 'RealizedPnl', 'DECIMAL', true, 18, null);
        $this->addColumn('fees_total', 'FeesTotal', 'DECIMAL', true, 18, null);
        $this->addColumn('simulated', 'Simulated', 'BOOLEAN', false, 10, false);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_trade_cycle', 'required', 'propel.validator.RequiredValidator', '', ('TradeCycle_IdTradeCycle_required'));
        $this->addValidator('id_trade_cycle', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('TradeCycle_IdTradeCycle_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('id_grid_run', 'required', 'propel.validator.RequiredValidator', '', ('TradeCycle_IdGridRun_required'));
        $this->addValidator('id_grid_run', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('TradeCycle_IdGridRun_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('level_idx', 'required', 'propel.validator.RequiredValidator', '', ('TradeCycle_LevelIdx_required'));
        $this->addValidator('level_idx', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('TradeCycle_LevelIdx_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('buy_price', 'required', 'propel.validator.RequiredValidator', '', ('TradeCycle_BuyPrice_required'));
        $this->addValidator('sell_price', 'required', 'propel.validator.RequiredValidator', '', ('TradeCycle_SellPrice_required'));
        $this->addValidator('qty', 'required', 'propel.validator.RequiredValidator', '', ('TradeCycle_Qty_required'));
        $this->addValidator('realized_pnl', 'required', 'propel.validator.RequiredValidator', '', ('TradeCycle_RealizedPnl_required'));
        $this->addValidator('fees_total', 'required', 'propel.validator.RequiredValidator', '', ('TradeCycle_FeesTotal_required'));
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('GridRun', 'App\\GridRun', RelationMap::MANY_TO_ONE, array('id_grid_run' => 'id_grid_run', ), 'CASCADE', null);
        $this->addRelation('AuthyGroup', 'App\\AuthyGroup', RelationMap::MANY_TO_ONE, array('id_group_creation' => 'id_authy_group', ), null, null);
        $this->addRelation('AuthyRelatedByIdCreation', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_creation' => 'id_authy', ), null, null);
        $this->addRelation('AuthyRelatedByIdModification', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_modification' => 'id_authy', ), null, null);
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
  'set_parent_table' => 'grid_run',
  'set_child_colunms' => '{"id_grid_run":["label"]}',
  'set_summary_cards' => '[{"label":"PnL (sim)","agg":"sum","col":"realized_pnl","format":"money","filter":{"simulated":1}},{"label":"PnL (real)","agg":"sum","col":"realized_pnl","format":"money","filter":{"simulated":0}},{"label":"Fees (sim)","agg":"sum","col":"fees_total","format":"money","filter":{"simulated":1}},{"label":"Fees (real)","agg":"sum","col":"fees_total","format":"money","filter":{"simulated":0}},{"label":"Cycles (sim)","agg":"count","filter":{"simulated":1}},{"label":"Cycles (real)","agg":"count","filter":{"simulated":0}}]',
  'add_search_columns' => '{"Simulated":[["simulated","val"]]}',
  'set_order_child_list_columns' => '[["date_creation","DESC"]]',
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

} // TradeCycleTableMap
