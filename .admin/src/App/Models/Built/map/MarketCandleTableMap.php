<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'market_candle' table.
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
class MarketCandleTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.MarketCandleTableMap';

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
        $this->setName('market_candle');
        $this->setPhpName('MarketCandle');
        $this->setClassname('App\\MarketCandle');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_market_candle', 'IdMarketCandle', 'INTEGER', true, 11, null);
        $this->addColumn('symbol', 'Symbol', 'VARCHAR', true, 20, null);
        $this->addColumn('tf', 'Tf', 'ENUM', true, null, null);
        $this->getColumn('tf', false)->setValueSet(array (
  0 => '1m',
  1 => '5m',
  2 => '15m',
  3 => '1h',
  4 => '4h',
));
        $this->addColumn('open_time', 'OpenTime', 'INTEGER', true, 10, null);
        $this->addColumn('open', 'Open', 'DECIMAL', true, 18, null);
        $this->addColumn('high', 'High', 'DECIMAL', true, 18, null);
        $this->addColumn('low', 'Low', 'DECIMAL', true, 18, null);
        $this->addColumn('close', 'Close', 'DECIMAL', true, 18, null);
        $this->addColumn('volume', 'Volume', 'DECIMAL', false, 24, 0);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_market_candle', 'required', 'propel.validator.RequiredValidator', '', ('MarketCandle_IdMarketCandle_required'));
        $this->addValidator('id_market_candle', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('MarketCandle_IdMarketCandle_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('symbol', 'required', 'propel.validator.RequiredValidator', '', ('MarketCandle_Symbol_required'));
        $this->addValidator('symbol', 'type', 'propel.validator.TypeValidator', 'string', ('MarketCandle_Symbol_type_string'));
        $this->addValidator('tf', 'required', 'propel.validator.RequiredValidator', '', ('MarketCandle_Tf_required'));
        $this->addValidator('tf', 'type', 'propel.validator.TypeValidator', 'string', ('MarketCandle_Tf_type_string'));
        $this->addValidator('open_time', 'required', 'propel.validator.RequiredValidator', '', ('MarketCandle_OpenTime_required'));
        $this->addValidator('open_time', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('MarketCandle_OpenTime_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('open', 'required', 'propel.validator.RequiredValidator', '', ('MarketCandle_Open_required'));
        $this->addValidator('high', 'required', 'propel.validator.RequiredValidator', '', ('MarketCandle_High_required'));
        $this->addValidator('low', 'required', 'propel.validator.RequiredValidator', '', ('MarketCandle_Low_required'));
        $this->addValidator('close', 'required', 'propel.validator.RequiredValidator', '', ('MarketCandle_Close_required'));
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
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
  'set_parent_menu' => 'Settings',
  'set_menu_priority' => '8',
  'set_order_list_columns' => '[["open_time","DESC"]]',
  'set_readonly_columns' => '["symbol","tf","open_time","open","high","low","close","volume"]',
  'add_search_columns' => '{"Symbol":[["symbol","%val"]],"Timeframe":[["tf","%val","multiple"]]}',
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

} // MarketCandleTableMap
