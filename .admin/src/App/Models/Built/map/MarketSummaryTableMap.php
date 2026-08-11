<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'market_summary' table.
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
class MarketSummaryTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.MarketSummaryTableMap';

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
        $this->setName('market_summary');
        $this->setPhpName('MarketSummary');
        $this->setClassname('App\\MarketSummary');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_market_summary', 'IdMarketSummary', 'INTEGER', true, 11, null);
        $this->addColumn('symbol', 'Symbol', 'VARCHAR', true, 20, null);
        $this->addColumn('tf', 'Tf', 'VARCHAR', true, 5, null);
        $this->addColumn('price', 'Price', 'DECIMAL', false, 18, null);
        $this->addColumn('ema20', 'Ema20', 'DECIMAL', false, 18, null);
        $this->addColumn('ema50', 'Ema50', 'DECIMAL', false, 18, null);
        $this->addColumn('ema200', 'Ema200', 'DECIMAL', false, 18, null);
        $this->addColumn('rsi14', 'Rsi14', 'DECIMAL', false, 9, null);
        $this->addColumn('atr14', 'Atr14', 'DECIMAL', false, 18, null);
        $this->addColumn('atr_pct', 'AtrPct', 'DECIMAL', false, 9, null);
        $this->addColumn('trend', 'Trend', 'ENUM', false, null, 'sideways');
        $this->getColumn('trend', false)->setValueSet(array (
  0 => 'strong_up',
  1 => 'up',
  2 => 'sideways',
  3 => 'down',
  4 => 'strong_down',
));
        $this->addColumn('swing_high', 'SwingHigh', 'DECIMAL', false, 18, null);
        $this->addColumn('swing_low', 'SwingLow', 'DECIMAL', false, 18, null);
        $this->addColumn('candles_used', 'CandlesUsed', 'INTEGER', false, 10, 0);
        $this->addColumn('recent_candles', 'RecentCandles', 'LONGVARCHAR', false, 1023, null);
        $this->addColumn('funding_rate', 'FundingRate', 'DECIMAL', false, 12, null);
        $this->addColumn('depth_imbalance', 'DepthImbalance', 'DECIMAL', false, 9, null);
        $this->addColumn('depth_imbalance_avg', 'DepthImbalanceAvg', 'DECIMAL', false, 9, null);
        $this->addColumn('adx14', 'Adx14', 'DECIMAL', false, 9, null);
        $this->addColumn('atr_pct_rank', 'AtrPctRank', 'DECIMAL', false, 9, null);
        $this->addColumn('taker_buy_ratio', 'TakerBuyRatio', 'DECIMAL', false, 9, null);
        $this->addColumn('vol_zscore', 'VolZscore', 'DECIMAL', false, 9, null);
        $this->addColumn('computed_at', 'ComputedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_market_summary', 'required', 'propel.validator.RequiredValidator', '', ('MarketSummary_IdMarketSummary_required'));
        $this->addValidator('id_market_summary', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('MarketSummary_IdMarketSummary_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('symbol', 'required', 'propel.validator.RequiredValidator', '', ('MarketSummary_Symbol_required'));
        $this->addValidator('symbol', 'type', 'propel.validator.TypeValidator', 'string', ('MarketSummary_Symbol_type_string'));
        $this->addValidator('tf', 'required', 'propel.validator.RequiredValidator', '', ('MarketSummary_Tf_required'));
        $this->addValidator('tf', 'type', 'propel.validator.TypeValidator', 'string', ('MarketSummary_Tf_type_string'));
        $this->addValidator('trend', 'type', 'propel.validator.TypeValidator', 'string', ('MarketSummary_Trend_type_string'));
        $this->addValidator('candles_used', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('MarketSummary_CandlesUsed_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('recent_candles', 'type', 'propel.validator.TypeValidator', 'string', ('MarketSummary_RecentCandles_type_string'));
        $this->addValidator('computed_at', 'match', 'propel.validator.MatchValidator', '', ('MarketSummary_ComputedAt_match'));
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
  'set_menu_priority' => '5',
  'set_order_list_columns' => '[["computed_at","DESC"]]',
  'set_list_hide_columns' => '["recent_candles"]',
  'set_readonly_columns' => '["symbol","tf","price","ema20","ema50","ema200","rsi14","atr14","atr_pct","trend","swing_high","swing_low","candles_used","recent_candles","computed_at","depth_imbalance_avg","adx14","atr_pct_rank","taker_buy_ratio","vol_zscore"]',
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

} // MarketSummaryTableMap
