<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'bot_command' table.
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
class BotCommandTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.BotCommandTableMap';

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
        $this->setName('bot_command');
        $this->setPhpName('BotCommand');
        $this->setClassname('App\\BotCommand');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_bot_command', 'IdBotCommand', 'INTEGER', true, 11, null);
        $this->addForeignKey('id_grid_run', 'IdGridRun', 'INTEGER', 'grid_run', 'id_grid_run', true, 11, null);
        $this->addColumn('command', 'Command', 'ENUM', true, null, null);
        $this->getColumn('command', false)->setValueSet(array (
  0 => 'Start',
  1 => 'Pause',
  2 => 'Resume',
  3 => 'Kill',
  4 => 'Flatten',
  5 => 'CancelBuys',
  6 => 'Reload',
));
        $this->addColumn('cmd_status', 'CmdStatus', 'ENUM', true, null, 'Pending');
        $this->getColumn('cmd_status', false)->setValueSet(array (
  0 => 'Pending',
  1 => 'Acked',
  2 => 'Done',
  3 => 'Failed',
));
        $this->addColumn('note', 'Note', 'VARCHAR', false, 255, null);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_bot_command', 'required', 'propel.validator.RequiredValidator', '', ('BotCommand_IdBotCommand_required'));
        $this->addValidator('id_bot_command', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('BotCommand_IdBotCommand_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('id_grid_run', 'required', 'propel.validator.RequiredValidator', '', ('BotCommand_IdGridRun_required'));
        $this->addValidator('id_grid_run', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('BotCommand_IdGridRun_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('command', 'required', 'propel.validator.RequiredValidator', '', ('BotCommand_Command_required'));
        $this->addValidator('command', 'type', 'propel.validator.TypeValidator', 'string', ('BotCommand_Command_type_string'));
        $this->addValidator('cmd_status', 'required', 'propel.validator.RequiredValidator', '', ('BotCommand_CmdStatus_required'));
        $this->addValidator('cmd_status', 'type', 'propel.validator.TypeValidator', 'string', ('BotCommand_CmdStatus_type_string'));
        $this->addValidator('note', 'type', 'propel.validator.TypeValidator', 'string', ('BotCommand_Note_type_string'));
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

} // BotCommandTableMap
