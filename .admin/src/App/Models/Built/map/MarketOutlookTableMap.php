<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'market_outlook' table.
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
class MarketOutlookTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.MarketOutlookTableMap';

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
        $this->setName('market_outlook');
        $this->setPhpName('MarketOutlook');
        $this->setClassname('App\\MarketOutlook');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_market_outlook', 'IdMarketOutlook', 'INTEGER', true, 11, null);
        $this->addColumn('symbol', 'Symbol', 'VARCHAR', true, 20, null);
        $this->addColumn('kind', 'Kind', 'VARCHAR', true, 10, 'Change');
        $this->addColumn('verdict', 'Verdict', 'VARCHAR', true, 16, null);
        $this->addColumn('prev_verdict', 'PrevVerdict', 'VARCHAR', false, 16, null);
        $this->addColumn('price_at', 'PriceAt', 'DECIMAL', true, 18, null);
        $this->addColumn('called_at', 'CalledAt', 'TIMESTAMP', true, null, null);
        $this->addColumn('detail', 'Detail', 'LONGVARCHAR', false, 1023, null);
        $this->addColumn('eval_status', 'EvalStatus', 'VARCHAR', true, 10, 'Pending');
        $this->addColumn('price_7d', 'Price7d', 'DECIMAL', false, 18, null);
        $this->addColumn('price_30d', 'Price30d', 'DECIMAL', false, 18, null);
        $this->addColumn('ret_7d', 'Ret7d', 'DECIMAL', false, 9, null);
        $this->addColumn('ret_30d', 'Ret30d', 'DECIMAL', false, 9, null);
        $this->addColumn('max_adverse_pct', 'MaxAdversePct', 'DECIMAL', false, 9, null);
        $this->addColumn('hit_7d', 'Hit7d', 'BOOLEAN', false, 10, null);
        $this->addColumn('hit_30d', 'Hit30d', 'BOOLEAN', false, 10, null);
        $this->addColumn('scored_at', 'ScoredAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_market_outlook', 'required', 'propel.validator.RequiredValidator', '', ('MarketOutlook_IdMarketOutlook_required'));
        $this->addValidator('id_market_outlook', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('MarketOutlook_IdMarketOutlook_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('symbol', 'required', 'propel.validator.RequiredValidator', '', ('MarketOutlook_Symbol_required'));
        $this->addValidator('symbol', 'type', 'propel.validator.TypeValidator', 'string', ('MarketOutlook_Symbol_type_string'));
        $this->addValidator('kind', 'required', 'propel.validator.RequiredValidator', '', ('MarketOutlook_Kind_required'));
        $this->addValidator('kind', 'type', 'propel.validator.TypeValidator', 'string', ('MarketOutlook_Kind_type_string'));
        $this->addValidator('verdict', 'required', 'propel.validator.RequiredValidator', '', ('MarketOutlook_Verdict_required'));
        $this->addValidator('verdict', 'type', 'propel.validator.TypeValidator', 'string', ('MarketOutlook_Verdict_type_string'));
        $this->addValidator('prev_verdict', 'type', 'propel.validator.TypeValidator', 'string', ('MarketOutlook_PrevVerdict_type_string'));
        $this->addValidator('price_at', 'required', 'propel.validator.RequiredValidator', '', ('MarketOutlook_PriceAt_required'));
        $this->addValidator('called_at', 'required', 'propel.validator.RequiredValidator', '', ('MarketOutlook_CalledAt_required'));
        $this->addValidator('called_at', 'match', 'propel.validator.MatchValidator', '', ('MarketOutlook_CalledAt_match'));
        $this->addValidator('detail', 'type', 'propel.validator.TypeValidator', 'string', ('MarketOutlook_Detail_type_string'));
        $this->addValidator('eval_status', 'required', 'propel.validator.RequiredValidator', '', ('MarketOutlook_EvalStatus_required'));
        $this->addValidator('eval_status', 'type', 'propel.validator.TypeValidator', 'string', ('MarketOutlook_EvalStatus_type_string'));
        $this->addValidator('scored_at', 'match', 'propel.validator.MatchValidator', '', ('MarketOutlook_ScoredAt_match'));
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
  'set_menu_priority' => '9',
  'set_order_list_columns' => '[["called_at","DESC"]]',
  'set_list_hide_columns' => '["detail"]',
  'set_readonly_columns' => '["symbol","kind","verdict","prev_verdict","price_at","called_at","detail","eval_status","price_7d","price_30d","ret_7d","ret_30d","max_adverse_pct","hit_7d","hit_30d","scored_at"]',
  'add_search_columns' => '{"Symbol":[["symbol","%val"]],"Kind":[["kind","%val"]],"Verdict":[["verdict","%val"]]}',
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

} // MarketOutlookTableMap
