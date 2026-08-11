<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'bot_order' table.
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
class BotOrderTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.BotOrderTableMap';

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
        $this->setName('bot_order');
        $this->setPhpName('BotOrder');
        $this->setClassname('App\\BotOrder');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_bot_order', 'IdBotOrder', 'INTEGER', true, 11, null);
        $this->addForeignKey('id_grid_run', 'IdGridRun', 'INTEGER', 'grid_run', 'id_grid_run', true, 11, null);
        $this->addColumn('client_order_id', 'ClientOrderId', 'VARCHAR', true, 36, null);
        $this->addColumn('exchange_order_id', 'ExchangeOrderId', 'VARCHAR', false, 32, null);
        $this->addColumn('level_idx', 'LevelIdx', 'INTEGER', true, 10, null);
        $this->addColumn('side', 'Side', 'ENUM', true, null, null);
        $this->getColumn('side', false)->setValueSet(array (
  0 => 'Buy',
  1 => 'Sell',
));
        $this->addColumn('state', 'State', 'ENUM', true, null, 'Intended');
        $this->getColumn('state', false)->setValueSet(array (
  0 => 'Intended',
  1 => 'Vetoed',
  2 => 'BUY_OPEN',
  3 => 'SELL_OPEN',
  4 => 'Filled',
  5 => 'PartFilled',
  6 => 'Canceled',
  7 => 'Rejected',
  8 => 'Lost',
));
        $this->addColumn('price', 'Price', 'DECIMAL', true, 18, null);
        $this->addColumn('qty', 'Qty', 'DECIMAL', true, 18, null);
        $this->addColumn('filled_qty', 'FilledQty', 'DECIMAL', false, 18, 0);
        $this->addColumn('fee_paid', 'FeePaid', 'DECIMAL', false, 18, 0);
        $this->addColumn('fee_asset', 'FeeAsset', 'VARCHAR', false, 10, null);
        $this->addColumn('is_legacy', 'IsLegacy', 'BOOLEAN', false, 10, false);
        $this->addColumn('legacy_buy_price', 'LegacyBuyPrice', 'DECIMAL', false, 18, null);
        $this->addColumn('legacy_buy_fee', 'LegacyBuyFee', 'DECIMAL', false, 18, null);
        $this->addColumn('simulated', 'Simulated', 'BOOLEAN', false, 10, false);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_bot_order', 'required', 'propel.validator.RequiredValidator', '', ('BotOrder_IdBotOrder_required'));
        $this->addValidator('id_bot_order', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('BotOrder_IdBotOrder_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('id_grid_run', 'required', 'propel.validator.RequiredValidator', '', ('BotOrder_IdGridRun_required'));
        $this->addValidator('id_grid_run', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('BotOrder_IdGridRun_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('client_order_id', 'required', 'propel.validator.RequiredValidator', '', ('BotOrder_ClientOrderId_required'));
        $this->addValidator('client_order_id', 'type', 'propel.validator.TypeValidator', 'string', ('BotOrder_ClientOrderId_type_string'));
        $this->addValidator('exchange_order_id', 'type', 'propel.validator.TypeValidator', 'string', ('BotOrder_ExchangeOrderId_type_string'));
        $this->addValidator('level_idx', 'required', 'propel.validator.RequiredValidator', '', ('BotOrder_LevelIdx_required'));
        $this->addValidator('level_idx', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('BotOrder_LevelIdx_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('side', 'required', 'propel.validator.RequiredValidator', '', ('BotOrder_Side_required'));
        $this->addValidator('side', 'type', 'propel.validator.TypeValidator', 'string', ('BotOrder_Side_type_string'));
        $this->addValidator('state', 'required', 'propel.validator.RequiredValidator', '', ('BotOrder_State_required'));
        $this->addValidator('state', 'type', 'propel.validator.TypeValidator', 'string', ('BotOrder_State_type_string'));
        $this->addValidator('price', 'required', 'propel.validator.RequiredValidator', '', ('BotOrder_Price_required'));
        $this->addValidator('qty', 'required', 'propel.validator.RequiredValidator', '', ('BotOrder_Qty_required'));
        $this->addValidator('fee_asset', 'type', 'propel.validator.TypeValidator', 'string', ('BotOrder_FeeAsset_type_string'));
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
  'set_summary_cards' => '[{"label":"Open buys","agg":"count","filter":{"state":"BUY_OPEN"}},{"label":"Open sells","agg":"count","filter":{"state":"SELL_OPEN"}},{"label":"Vetoed","agg":"count","filter":{"state":"Vetoed"}}]',
  'set_order_child_list_columns' => '[["date_modification","DESC"]]',
  'add_search_columns' => '{"Client order id":[["client_order_id","%val"]],"Side":[["side","%val","multiple"]],"State":[["state","%val","multiple"]],"Simulated":[["simulated","val"]]}',
  'set_list_hide_columns' => '["legacy_buy_price","legacy_buy_fee"]',
  'set_readonly_columns' => '["client_order_id","exchange_order_id","filled_qty","fee_paid","simulated","legacy_buy_price","legacy_buy_fee"]',
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

} // BotOrderTableMap
