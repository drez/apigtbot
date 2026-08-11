<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'bot_event' table.
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
class BotEventTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.BotEventTableMap';

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
        $this->setName('bot_event');
        $this->setPhpName('BotEvent');
        $this->setClassname('App\\BotEvent');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_bot_event', 'IdBotEvent', 'INTEGER', true, 11, null);
        $this->addForeignKey('id_grid_run', 'IdGridRun', 'INTEGER', 'grid_run', 'id_grid_run', true, 11, null);
        $this->addColumn('level', 'Level', 'ENUM', true, null, 'Info');
        $this->getColumn('level', false)->setValueSet(array (
  0 => 'Info',
  1 => 'Warn',
  2 => 'Error',
  3 => 'Alert',
));
        $this->addColumn('kind', 'Kind', 'VARCHAR', true, 50, null);
        $this->addColumn('message', 'Message', 'VARCHAR', true, 500, null);
        $this->addColumn('payload', 'Payload', 'LONGVARCHAR', false, 1023, null);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_bot_event', 'required', 'propel.validator.RequiredValidator', '', ('BotEvent_IdBotEvent_required'));
        $this->addValidator('id_bot_event', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('BotEvent_IdBotEvent_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('id_grid_run', 'required', 'propel.validator.RequiredValidator', '', ('BotEvent_IdGridRun_required'));
        $this->addValidator('id_grid_run', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('BotEvent_IdGridRun_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('level', 'required', 'propel.validator.RequiredValidator', '', ('BotEvent_Level_required'));
        $this->addValidator('level', 'type', 'propel.validator.TypeValidator', 'string', ('BotEvent_Level_type_string'));
        $this->addValidator('kind', 'required', 'propel.validator.RequiredValidator', '', ('BotEvent_Kind_required'));
        $this->addValidator('kind', 'type', 'propel.validator.TypeValidator', 'string', ('BotEvent_Kind_type_string'));
        $this->addValidator('message', 'required', 'propel.validator.RequiredValidator', '', ('BotEvent_Message_required'));
        $this->addValidator('message', 'type', 'propel.validator.TypeValidator', 'string', ('BotEvent_Message_type_string'));
        $this->addValidator('payload', 'type', 'propel.validator.TypeValidator', 'string', ('BotEvent_Payload_type_string'));
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
  'set_order_child_list_columns' => '[["date_creation","DESC"]]',
  'add_search_columns' => '{"Level":[["level","%val","multiple"]],"Kind":[["kind","%val"]]}',
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

} // BotEventTableMap
