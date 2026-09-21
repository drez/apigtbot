<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'fleet_slot' table.
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
class FleetSlotTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.FleetSlotTableMap';

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
        $this->setName('fleet_slot');
        $this->setPhpName('FleetSlot');
        $this->setClassname('App\\FleetSlot');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_fleet_slot', 'IdFleetSlot', 'INTEGER', true, 11, null);
        $this->addColumn('symbol', 'Symbol', 'VARCHAR', true, 20, null);
        $this->addColumn('algo', 'Algo', 'ENUM', true, null, 'Trend');
        $this->getColumn('algo', false)->setValueSet(array (
  0 => 'Trend',
  1 => 'Grid',
));
        $this->addColumn('target_slice', 'TargetSlice', 'DECIMAL', true, 20, 350);
        $this->addColumn('enabled', 'Enabled', 'BOOLEAN', false, 10, true);
        $this->addColumn('state', 'State', 'ENUM', true, null, 'idle');
        $this->getColumn('state', false)->setValueSet(array (
  0 => 'idle',
  1 => 'active',
  2 => 'winding_down',
));
        $this->addColumn('confirm_up', 'ConfirmUp', 'INTEGER', false, 10, 0);
        $this->addColumn('confirm_down', 'ConfirmDown', 'INTEGER', false, 10, 0);
        $this->addColumn('last_verdict', 'LastVerdict', 'VARCHAR', false, 16, null);
        $this->addColumn('verdict_at', 'VerdictAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('episode_started_at', 'EpisodeStartedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('activation', 'Activation', 'LONGVARCHAR', false, 1023, null);
        $this->addForeignKey('id_grid_run', 'IdGridRun', 'INTEGER', 'grid_run', 'id_grid_run', false, 11, null);
        $this->addColumn('last_empty_alert_at', 'LastEmptyAlertAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('last_parked_alert_at', 'LastParkedAlertAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_fleet_slot', 'required', 'propel.validator.RequiredValidator', '', ('FleetSlot_IdFleetSlot_required'));
        $this->addValidator('id_fleet_slot', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('FleetSlot_IdFleetSlot_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('symbol', 'required', 'propel.validator.RequiredValidator', '', ('FleetSlot_Symbol_required'));
        $this->addValidator('symbol', 'type', 'propel.validator.TypeValidator', 'string', ('FleetSlot_Symbol_type_string'));
        $this->addValidator('algo', 'required', 'propel.validator.RequiredValidator', '', ('FleetSlot_Algo_required'));
        $this->addValidator('algo', 'type', 'propel.validator.TypeValidator', 'string', ('FleetSlot_Algo_type_string'));
        $this->addValidator('target_slice', 'required', 'propel.validator.RequiredValidator', '', ('FleetSlot_TargetSlice_required'));
        $this->addValidator('state', 'required', 'propel.validator.RequiredValidator', '', ('FleetSlot_State_required'));
        $this->addValidator('state', 'type', 'propel.validator.TypeValidator', 'string', ('FleetSlot_State_type_string'));
        $this->addValidator('confirm_up', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('FleetSlot_ConfirmUp_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('confirm_down', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('FleetSlot_ConfirmDown_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('last_verdict', 'type', 'propel.validator.TypeValidator', 'string', ('FleetSlot_LastVerdict_type_string'));
        $this->addValidator('verdict_at', 'match', 'propel.validator.MatchValidator', '', ('FleetSlot_VerdictAt_match'));
        $this->addValidator('episode_started_at', 'match', 'propel.validator.MatchValidator', '', ('FleetSlot_EpisodeStartedAt_match'));
        $this->addValidator('activation', 'type', 'propel.validator.TypeValidator', 'string', ('FleetSlot_Activation_type_string'));
        $this->addValidator('id_grid_run', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('FleetSlot_IdGridRun_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('last_empty_alert_at', 'match', 'propel.validator.MatchValidator', '', ('FleetSlot_LastEmptyAlertAt_match'));
        $this->addValidator('last_parked_alert_at', 'match', 'propel.validator.MatchValidator', '', ('FleetSlot_LastParkedAlertAt_match'));
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('GridRun', 'App\\GridRun', RelationMap::MANY_TO_ONE, array('id_grid_run' => 'id_grid_run', ), 'SET NULL', null);
        $this->addRelation('AuthyGroup', 'App\\AuthyGroup', RelationMap::MANY_TO_ONE, array('id_group_creation' => 'id_authy_group', ), null, null);
        $this->addRelation('AuthyRelatedByIdCreation', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_creation' => 'id_authy', ), null, null);
        $this->addRelation('AuthyRelatedByIdModification', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_modification' => 'id_authy', ), null, null);
        $this->addRelation('RegimeEpisode', 'App\\RegimeEpisode', RelationMap::ONE_TO_MANY, array('id_fleet_slot' => 'id_fleet_slot', ), 'SET NULL', null, 'RegimeEpisodes');
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
  'set_menu_icon' => 'ri-layout-grid-line',
  'set_parent_menu' => 'Trading',
  'set_menu_priority' => '2',
  'set_child_colunms' => '{"id_grid_run":["label"]}',
  'set_order_list_columns' => '[["symbol","ASC"]]',
  'set_readonly_columns' => '["state","confirm_up","confirm_down","last_verdict","verdict_at","episode_started_at","activation","last_empty_alert_at","last_parked_alert_at"]',
  'set_list_hide_columns' => '["activation","confirm_up","confirm_down","last_empty_alert_at","last_parked_alert_at"]',
  'add_search_columns' => '{"Symbol":[["symbol","%val"]],"Algorithm":[["algo","%val","multiple"]],"State":[["state","%val","multiple"]]}',
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

} // FleetSlotTableMap
