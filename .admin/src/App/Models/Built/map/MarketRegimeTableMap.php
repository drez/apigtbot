<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'market_regime' table.
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
class MarketRegimeTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.MarketRegimeTableMap';

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
        $this->setName('market_regime');
        $this->setPhpName('MarketRegime');
        $this->setClassname('App\\MarketRegime');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_market_regime', 'IdMarketRegime', 'INTEGER', true, 11, null);
        $this->addColumn('symbol', 'Symbol', 'VARCHAR', true, 20, null);
        $this->addColumn('tf', 'Tf', 'VARCHAR', true, 5, null);
        $this->addColumn('price', 'Price', 'DECIMAL', false, 18, null);
        $this->addColumn('trend', 'Trend', 'ENUM', false, null, 'sideways');
        $this->getColumn('trend', false)->setValueSet(array (
  0 => 'strong_up',
  1 => 'up',
  2 => 'sideways',
  3 => 'down',
  4 => 'strong_down',
));
        $this->addColumn('rsi14', 'Rsi14', 'DECIMAL', false, 9, null);
        $this->addColumn('atr_pct', 'AtrPct', 'DECIMAL', false, 9, null);
        $this->addColumn('adx14', 'Adx14', 'DECIMAL', false, 9, null);
        $this->addColumn('atr_pct_rank', 'AtrPctRank', 'DECIMAL', false, 9, null);
        $this->addColumn('taker_buy_ratio', 'TakerBuyRatio', 'DECIMAL', false, 9, null);
        $this->addColumn('vol_zscore', 'VolZscore', 'DECIMAL', false, 9, null);
        $this->addColumn('er20', 'Er20', 'DECIMAL', false, 9, null);
        $this->addColumn('chop14', 'Chop14', 'DECIMAL', false, 9, null);
        $this->addColumn('funding_pct', 'FundingPct', 'DECIMAL', false, 9, null);
        $this->addColumn('funding_rate', 'FundingRate', 'DECIMAL', false, 12, null);
        $this->addColumn('depth_imbalance', 'DepthImbalance', 'DECIMAL', false, 9, null);
        $this->addColumn('depth_imbalance_avg', 'DepthImbalanceAvg', 'DECIMAL', false, 9, null);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_market_regime', 'required', 'propel.validator.RequiredValidator', '', ('MarketRegime_IdMarketRegime_required'));
        $this->addValidator('id_market_regime', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('MarketRegime_IdMarketRegime_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('symbol', 'required', 'propel.validator.RequiredValidator', '', ('MarketRegime_Symbol_required'));
        $this->addValidator('symbol', 'type', 'propel.validator.TypeValidator', 'string', ('MarketRegime_Symbol_type_string'));
        $this->addValidator('tf', 'required', 'propel.validator.RequiredValidator', '', ('MarketRegime_Tf_required'));
        $this->addValidator('tf', 'type', 'propel.validator.TypeValidator', 'string', ('MarketRegime_Tf_type_string'));
        $this->addValidator('trend', 'type', 'propel.validator.TypeValidator', 'string', ('MarketRegime_Trend_type_string'));
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
  'set_menu_priority' => '6',
  'set_order_list_columns' => '[["date_creation","DESC"]]',
  'set_readonly_columns' => '["symbol","tf","price","trend","rsi14","atr_pct","adx14","atr_pct_rank","taker_buy_ratio","vol_zscore","funding_rate","depth_imbalance","depth_imbalance_avg","er20","chop14","funding_pct"]',
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

} // MarketRegimeTableMap
