<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'regime_episode' table.
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
class RegimeEpisodeTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.RegimeEpisodeTableMap';

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
        $this->setName('regime_episode');
        $this->setPhpName('RegimeEpisode');
        $this->setClassname('App\\RegimeEpisode');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_regime_episode', 'IdRegimeEpisode', 'INTEGER', true, 11, null);
        $this->addColumn('symbol', 'Symbol', 'VARCHAR', true, 20, null);
        $this->addColumn('algo', 'Algo', 'ENUM', true, null, 'Trend');
        $this->getColumn('algo', false)->setValueSet(array (
  0 => 'Trend',
  1 => 'Grid',
));
        $this->addForeignKey('id_fleet_slot', 'IdFleetSlot', 'INTEGER', 'fleet_slot', 'id_fleet_slot', false, 11, null);
        $this->addForeignKey('id_grid_run', 'IdGridRun', 'INTEGER', 'grid_run', 'id_grid_run', false, 11, null);
        $this->addColumn('verdict', 'Verdict', 'VARCHAR', true, 16, null);
        $this->addColumn('opened_at', 'OpenedAt', 'TIMESTAMP', true, null, null);
        $this->addColumn('closed_at', 'ClosedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('price_open', 'PriceOpen', 'DECIMAL', true, 20, null);
        $this->addColumn('price_close', 'PriceClose', 'DECIMAL', false, 20, null);
        $this->addColumn('engaged_pct_tw', 'EngagedPctTw', 'DECIMAL', false, 8, null);
        $this->addColumn('samples', 'Samples', 'INTEGER', false, 10, 0);
        $this->addColumn('realized', 'Realized', 'DECIMAL', false, 20, 0);
        $this->addColumn('mtm_close', 'MtmClose', 'DECIMAL', false, 20, null);
        $this->addColumn('hodl_pct', 'HodlPct', 'DECIMAL', false, 8, null);
        $this->addColumn('captured_pct', 'CapturedPct', 'DECIMAL', false, 8, null);
        $this->addColumn('idle_samples', 'IdleSamples', 'INTEGER', false, 10, 0);
        $this->addColumn('idle_alerted_at', 'IdleAlertedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_regime_episode', 'required', 'propel.validator.RequiredValidator', '', ('RegimeEpisode_IdRegimeEpisode_required'));
        $this->addValidator('id_regime_episode', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('RegimeEpisode_IdRegimeEpisode_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('symbol', 'required', 'propel.validator.RequiredValidator', '', ('RegimeEpisode_Symbol_required'));
        $this->addValidator('symbol', 'type', 'propel.validator.TypeValidator', 'string', ('RegimeEpisode_Symbol_type_string'));
        $this->addValidator('algo', 'required', 'propel.validator.RequiredValidator', '', ('RegimeEpisode_Algo_required'));
        $this->addValidator('algo', 'type', 'propel.validator.TypeValidator', 'string', ('RegimeEpisode_Algo_type_string'));
        $this->addValidator('id_fleet_slot', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('RegimeEpisode_IdFleetSlot_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('id_grid_run', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('RegimeEpisode_IdGridRun_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('verdict', 'required', 'propel.validator.RequiredValidator', '', ('RegimeEpisode_Verdict_required'));
        $this->addValidator('verdict', 'type', 'propel.validator.TypeValidator', 'string', ('RegimeEpisode_Verdict_type_string'));
        $this->addValidator('opened_at', 'required', 'propel.validator.RequiredValidator', '', ('RegimeEpisode_OpenedAt_required'));
        $this->addValidator('opened_at', 'match', 'propel.validator.MatchValidator', '', ('RegimeEpisode_OpenedAt_match'));
        $this->addValidator('closed_at', 'match', 'propel.validator.MatchValidator', '', ('RegimeEpisode_ClosedAt_match'));
        $this->addValidator('price_open', 'required', 'propel.validator.RequiredValidator', '', ('RegimeEpisode_PriceOpen_required'));
        $this->addValidator('samples', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('RegimeEpisode_Samples_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('idle_samples', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('RegimeEpisode_IdleSamples_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('idle_alerted_at', 'match', 'propel.validator.MatchValidator', '', ('RegimeEpisode_IdleAlertedAt_match'));
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('FleetSlot', 'App\\FleetSlot', RelationMap::MANY_TO_ONE, array('id_fleet_slot' => 'id_fleet_slot', ), 'SET NULL', null);
        $this->addRelation('GridRun', 'App\\GridRun', RelationMap::MANY_TO_ONE, array('id_grid_run' => 'id_grid_run', ), 'SET NULL', null);
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
  'set_menu_icon' => 'ri-timer-flash-line',
  'set_parent_menu' => 'Trading',
  'set_menu_priority' => '3',
  'set_child_colunms' => '{"id_grid_run":["label"],"id_fleet_slot":["symbol","algo"]}',
  'set_order_list_columns' => '[["opened_at","DESC"]]',
  'set_readonly_columns' => '["symbol","algo","verdict","opened_at","closed_at","price_open","price_close","engaged_pct_tw","samples","realized","mtm_close","hodl_pct","captured_pct","idle_samples","idle_alerted_at"]',
  'set_list_hide_columns' => '["algo","idle_samples","idle_alerted_at"]',
  'add_search_columns' => '{"Symbol":[["symbol","%val"]],"Algorithm":[["algo","%val","multiple"]],"Verdict":[["verdict","%val"]]}',
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

} // RegimeEpisodeTableMap
