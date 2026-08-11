<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'authy_log' table.
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
class AuthyLogTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.AuthyLogTableMap';

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
        $this->setName('authy_log');
        $this->setPhpName('AuthyLog');
        $this->setClassname('App\\AuthyLog');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_authy_log', 'IdAuthyLog', 'INTEGER', true, null, null);
        $this->addForeignKey('id_authy', 'IdAuthy', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addColumn('timestamp', 'Timestamp', 'TIMESTAMP', false, null, null);
        $this->addColumn('login', 'Login', 'VARCHAR', true, 50, null);
        $this->addColumn('userid', 'Userid', 'INTEGER', false, null, null);
        $this->addColumn('result', 'Result', 'VARCHAR', true, 100, null);
        $this->addColumn('event', 'Event', 'VARCHAR', false, 64, null);
        $this->addColumn('ip', 'Ip', 'VARCHAR', true, 16, null);
        $this->addColumn('count', 'Count', 'INTEGER', false, null, null);
        // validators
        $this->addValidator('id_authy_log', 'required', 'propel.validator.RequiredValidator', '', ('AuthyLog_IdAuthyLog_required'));
        $this->addValidator('id_authy_log', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('AuthyLog_IdAuthyLog_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('id_authy', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('AuthyLog_IdAuthy_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('timestamp', 'match', 'propel.validator.MatchValidator', '', ('AuthyLog_Timestamp_match'));
        $this->addValidator('login', 'required', 'propel.validator.RequiredValidator', '', ('AuthyLog_Login_required'));
        $this->addValidator('login', 'type', 'propel.validator.TypeValidator', 'string', ('AuthyLog_Login_type_string'));
        $this->addValidator('userid', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('AuthyLog_Userid_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('result', 'required', 'propel.validator.RequiredValidator', '', ('AuthyLog_Result_required'));
        $this->addValidator('result', 'type', 'propel.validator.TypeValidator', 'string', ('AuthyLog_Result_type_string'));
        $this->addValidator('event', 'type', 'propel.validator.TypeValidator', 'string', ('AuthyLog_Event_type_string'));
        $this->addValidator('ip', 'required', 'propel.validator.RequiredValidator', '', ('AuthyLog_Ip_required'));
        $this->addValidator('ip', 'type', 'propel.validator.TypeValidator', 'string', ('AuthyLog_Ip_type_string'));
        $this->addValidator('count', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('AuthyLog_Count_match_/^(?:[0-9]*|null)$/'));
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Authy', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_authy' => 'id_authy', ), null, 'CASCADE');
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
  'set_menu_priority' => '0',
  'set_order_list_columns' => '[["timestamp", "DESC"]]',
),
            'add_tablestamp' =>  array (
  'create_column' => 'date_creation',
  'update_column' => 'date_modification',
  'create_id_column' => 'id_creation',
  'group_id_column' => 'id_group_creation',
  'update_id_column' => 'id_modification',
  'exclude' => 'all',
  'foreign_keys' => 'all',
),
            'add_validator' =>  array (
),
        );
    } // getBehaviors()

} // AuthyLogTableMap
