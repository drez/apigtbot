<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'api_log' table.
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
class ApiLogTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.ApiLogTableMap';

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
        $this->setName('api_log');
        $this->setPhpName('ApiLog');
        $this->setClassname('App\\ApiLog');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_api_log', 'IdApiLog', 'INTEGER', true, 11, null);
        $this->addForeignKey('id_api_rbac', 'IdApiRbac', 'INTEGER', 'api_rbac', 'id_api_rbac', true, null, null);
        $this->addForeignKey('id_authy', 'IdAuthy', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addColumn('time', 'Time', 'TIMESTAMP', true, null, null);
        $this->addColumn('raw_parameters', 'RawParameters', 'LONGVARCHAR', false, 1023, null);
        $this->addColumn('count', 'Count', 'INTEGER', true, null, 1);
        // validators
        $this->addValidator('id_api_log', 'required', 'propel.validator.RequiredValidator', '', ('ApiLog_IdApiLog_required'));
        $this->addValidator('id_api_log', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('ApiLog_IdApiLog_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('id_api_rbac', 'required', 'propel.validator.RequiredValidator', '', ('ApiLog_IdApiRbac_required'));
        $this->addValidator('id_api_rbac', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('ApiLog_IdApiRbac_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('id_authy', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('ApiLog_IdAuthy_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('time', 'required', 'propel.validator.RequiredValidator', '', ('ApiLog_Time_required'));
        $this->addValidator('time', 'match', 'propel.validator.MatchValidator', '', ('ApiLog_Time_match'));
        $this->addValidator('raw_parameters', 'type', 'propel.validator.TypeValidator', 'string', ('ApiLog_RawParameters_type_string'));
        $this->addValidator('count', 'required', 'propel.validator.RequiredValidator', '', ('ApiLog_Count_required'));
        $this->addValidator('count', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('ApiLog_Count_match_/^(?:[0-9]*|null)$/'));
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('ApiRbac', 'App\\ApiRbac', RelationMap::MANY_TO_ONE, array('id_api_rbac' => 'id_api_rbac', ), 'CASCADE', null);
        $this->addRelation('Authy', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_authy' => 'id_authy', ), 'CASCADE', null);
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
  'set_parent_menu' => 'Settings',
  'set_child_colunms' => '{"id_api_rbac":["model", "action", "query"], "id_authy":["username"]}',
  'add_prune_action' => '{"column":"time"}',
  'set_order_list_columns' => '[["time", "DESC"]]',
),
            'add_tablestamp' =>  array (
  'exclude' => 'all',
),
            'add_validator' =>  array (
),
        );
    } // getBehaviors()

} // ApiLogTableMap
