<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'market_outlook_state' table.
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
class MarketOutlookStateTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.MarketOutlookStateTableMap';

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
        $this->setName('market_outlook_state');
        $this->setPhpName('MarketOutlookState');
        $this->setClassname('App\\MarketOutlookState');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_market_outlook_state', 'IdMarketOutlookState', 'INTEGER', true, 11, null);
        $this->addColumn('symbol', 'Symbol', 'VARCHAR', true, 20, null);
        $this->addColumn('verdict', 'Verdict', 'VARCHAR', false, 16, null);
        $this->addColumn('verdict_since', 'VerdictSince', 'TIMESTAMP', false, null, null);
        $this->addColumn('price_at_verdict', 'PriceAtVerdict', 'DECIMAL', false, 18, null);
        $this->addColumn('candidate', 'Candidate', 'VARCHAR', false, 16, null);
        $this->addColumn('candidate_passes', 'CandidatePasses', 'INTEGER', true, 10, 0);
        $this->addColumn('last_raw', 'LastRaw', 'VARCHAR', false, 16, null);
        $this->addColumn('last_pass_at', 'LastPassAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_market_outlook_state', 'required', 'propel.validator.RequiredValidator', '', ('MarketOutlookState_IdMarketOutlookState_required'));
        $this->addValidator('id_market_outlook_state', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('MarketOutlookState_IdMarketOutlookState_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('symbol', 'required', 'propel.validator.RequiredValidator', '', ('MarketOutlookState_Symbol_required'));
        $this->addValidator('symbol', 'type', 'propel.validator.TypeValidator', 'string', ('MarketOutlookState_Symbol_type_string'));
        $this->addValidator('verdict', 'type', 'propel.validator.TypeValidator', 'string', ('MarketOutlookState_Verdict_type_string'));
        $this->addValidator('verdict_since', 'match', 'propel.validator.MatchValidator', '', ('MarketOutlookState_VerdictSince_match'));
        $this->addValidator('candidate', 'type', 'propel.validator.TypeValidator', 'string', ('MarketOutlookState_Candidate_type_string'));
        $this->addValidator('candidate_passes', 'required', 'propel.validator.RequiredValidator', '', ('MarketOutlookState_CandidatePasses_required'));
        $this->addValidator('candidate_passes', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('MarketOutlookState_CandidatePasses_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('last_raw', 'type', 'propel.validator.TypeValidator', 'string', ('MarketOutlookState_LastRaw_type_string'));
        $this->addValidator('last_pass_at', 'match', 'propel.validator.MatchValidator', '', ('MarketOutlookState_LastPassAt_match'));
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
  'set_menu_priority' => '10',
  'set_readonly_columns' => '["symbol","verdict","verdict_since","price_at_verdict","candidate","candidate_passes","last_raw","last_pass_at"]',
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

} // MarketOutlookStateTableMap
