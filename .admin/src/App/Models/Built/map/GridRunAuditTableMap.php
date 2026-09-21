<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'grid_run_audit' table.
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
class GridRunAuditTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.GridRunAuditTableMap';

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
        $this->setName('grid_run_audit');
        $this->setPhpName('GridRunAudit');
        $this->setClassname('App\\GridRunAudit');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_grid_run_audit', 'IdGridRunAudit', 'INTEGER', true, 11, null);
        $this->addForeignKey('id_grid_run', 'IdGridRun', 'INTEGER', 'grid_run', 'id_grid_run', true, null, null);
        $this->addColumn('field', 'Field', 'VARCHAR', true, 64, null);
        $this->addColumn('value_from', 'ValueFrom', 'LONGVARCHAR', false, null, null);
        $this->addColumn('value_to', 'ValueTo', 'LONGVARCHAR', false, null, null);
        $this->addColumn('actor', 'Actor', 'VARCHAR', false, 128, null);
        $this->addColumn('source', 'Source', 'ENUM', false, null, 'gui');
        $this->getColumn('source', false)->setValueSet(array (
  0 => 'gui',
  1 => 'api',
  2 => 'mcp',
  3 => 'cli',
  4 => 'daemon',
));
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_grid_run_audit', 'required', 'propel.validator.RequiredValidator', '', ('GridRunAudit_IdGridRunAudit_required'));
        $this->addValidator('id_grid_run_audit', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('GridRunAudit_IdGridRunAudit_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('id_grid_run', 'required', 'propel.validator.RequiredValidator', '', ('GridRunAudit_IdGridRun_required'));
        $this->addValidator('id_grid_run', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('GridRunAudit_IdGridRun_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('field', 'required', 'propel.validator.RequiredValidator', '', ('GridRunAudit_Field_required'));
        $this->addValidator('field', 'type', 'propel.validator.TypeValidator', 'string', ('GridRunAudit_Field_type_string'));
        $this->addValidator('value_from', 'type', 'propel.validator.TypeValidator', 'string', ('GridRunAudit_ValueFrom_type_string'));
        $this->addValidator('value_to', 'type', 'propel.validator.TypeValidator', 'string', ('GridRunAudit_ValueTo_type_string'));
        $this->addValidator('actor', 'type', 'propel.validator.TypeValidator', 'string', ('GridRunAudit_Actor_type_string'));
        $this->addValidator('source', 'type', 'propel.validator.TypeValidator', 'string', ('GridRunAudit_Source_type_string'));
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
  'set_parent_table' => 'grid_run',
  'set_order_list_columns' => '[["date_creation", "DESC"]]',
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

} // GridRunAuditTableMap
