<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'api_rbac' table.
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
class ApiRbacTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.ApiRbacTableMap';

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
        $this->setName('api_rbac');
        $this->setPhpName('ApiRbac');
        $this->setClassname('App\\ApiRbac');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_api_rbac', 'IdApiRbac', 'INTEGER', true, 11, null);
        $this->addColumn('date_creation', 'DateCreation', 'DATE', true, null, null);
        $this->addColumn('description', 'Description', 'LONGVARCHAR', false, 1023, null);
        $this->addColumn('model', 'Model', 'VARCHAR', true, 200, null);
        $this->addColumn('action', 'Action', 'VARCHAR', false, 200, null);
        $this->addColumn('body', 'Body', 'LONGVARCHAR', false, 1023, null);
        $this->addColumn('query', 'Query', 'LONGVARCHAR', false, 1023, null);
        $this->addColumn('method', 'Method', 'ENUM', true, null, 'GET');
        $this->getColumn('method', false)->setValueSet(array (
  0 => 'GET',
  1 => 'POST',
  2 => 'PATCH',
  3 => 'PUT',
  4 => 'DELETE',
  5 => 'ALL',
));
        $this->addColumn('scope', 'Scope', 'ENUM', true, null, 'Private');
        $this->getColumn('scope', false)->setValueSet(array (
  0 => 'Private',
  1 => 'Public',
));
        $this->addColumn('rule', 'Rule', 'ENUM', true, null, 'Deny');
        $this->getColumn('rule', false)->setValueSet(array (
  0 => 'Allow',
  1 => 'Deny',
));
        $this->addColumn('count', 'Count', 'INTEGER', true, null, 0);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_api_rbac', 'required', 'propel.validator.RequiredValidator', '', ('ApiRbac_IdApiRbac_required'));
        $this->addValidator('id_api_rbac', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('ApiRbac_IdApiRbac_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('description', 'type', 'propel.validator.TypeValidator', 'string', ('ApiRbac_Description_type_string'));
        $this->addValidator('model', 'required', 'propel.validator.RequiredValidator', '', ('ApiRbac_Model_required'));
        $this->addValidator('model', 'type', 'propel.validator.TypeValidator', 'string', ('ApiRbac_Model_type_string'));
        $this->addValidator('action', 'type', 'propel.validator.TypeValidator', 'string', ('ApiRbac_Action_type_string'));
        $this->addValidator('body', 'type', 'propel.validator.TypeValidator', 'string', ('ApiRbac_Body_type_string'));
        $this->addValidator('query', 'type', 'propel.validator.TypeValidator', 'string', ('ApiRbac_Query_type_string'));
        $this->addValidator('method', 'required', 'propel.validator.RequiredValidator', '', ('ApiRbac_Method_required'));
        $this->addValidator('method', 'type', 'propel.validator.TypeValidator', 'string', ('ApiRbac_Method_type_string'));
        $this->addValidator('scope', 'required', 'propel.validator.RequiredValidator', '', ('ApiRbac_Scope_required'));
        $this->addValidator('scope', 'type', 'propel.validator.TypeValidator', 'string', ('ApiRbac_Scope_type_string'));
        $this->addValidator('rule', 'required', 'propel.validator.RequiredValidator', '', ('ApiRbac_Rule_required'));
        $this->addValidator('rule', 'type', 'propel.validator.TypeValidator', 'string', ('ApiRbac_Rule_type_string'));
        $this->addValidator('count', 'required', 'propel.validator.RequiredValidator', '', ('ApiRbac_Count_required'));
        $this->addValidator('count', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('ApiRbac_Count_match_/^(?:[0-9]*|null)$/'));
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('AuthyGroup', 'App\\AuthyGroup', RelationMap::MANY_TO_ONE, array('id_group_creation' => 'id_authy_group', ), null, null);
        $this->addRelation('AuthyRelatedByIdCreation', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_creation' => 'id_authy', ), null, null);
        $this->addRelation('AuthyRelatedByIdModification', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_modification' => 'id_authy', ), null, null);
        $this->addRelation('ApiLog', 'App\\ApiLog', RelationMap::ONE_TO_MANY, array('id_api_rbac' => 'id_api_rbac', ), 'CASCADE', null, 'ApiLogs');
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
  'add_search_columns' => '{
                    "Scope":[["scope", "%val"]],
                    "Model":[["model", "%val"]],
                    "Action":[["action", "%val"]]}',
  'set_order_list_columns' => '[["date_creation", "DESC"]]',
  'set_list_hide_columns' => '["query"]',
  'with_child_tables' => '["api_log"]',
  'set_order_child_list_columns' => '{"api_log": [["time", "DESC"]]}',
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

} // ApiRbacTableMap
