<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'config' table.
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
class ConfigTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.ConfigTableMap';

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
        $this->setName('config');
        $this->setPhpName('Config');
        $this->setClassname('App\\Config');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_config', 'IdConfig', 'INTEGER', true, null, null);
        $this->addColumn('category', 'Category', 'ENUM', true, null, null);
        $this->getColumn('category', false)->setValueSet(array (
  0 => 'General',
  1 => 'API',
  2 => 'Control panel',
));
        $this->addColumn('config', 'Config', 'VARCHAR', true, 100, null);
        $this->addColumn('value', 'Value', 'LONGVARCHAR', false, 400, null);
        $this->addColumn('system', 'System', 'ENUM', false, null, 'y');
        $this->getColumn('system', false)->setValueSet(array (
  0 => 'y',
  1 => 'n',
));
        $this->addColumn('description', 'Description', 'VARCHAR', false, 500, null);
        $this->addColumn('type', 'Type', 'VARCHAR', false, 35, null);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_config', 'required', 'propel.validator.RequiredValidator', '', ('Config_IdConfig_required'));
        $this->addValidator('id_config', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('Config_IdConfig_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('category', 'required', 'propel.validator.RequiredValidator', '', ('Config_Category_required'));
        $this->addValidator('category', 'type', 'propel.validator.TypeValidator', 'string', ('Config_Category_type_string'));
        $this->addValidator('config', 'required', 'propel.validator.RequiredValidator', '', ('Config_Config_required'));
        $this->addValidator('config', 'type', 'propel.validator.TypeValidator', 'string', ('Config_Config_type_string'));
        $this->addValidator('value', 'type', 'propel.validator.TypeValidator', 'string', ('Config_Value_type_string'));
        $this->addValidator('system', 'type', 'propel.validator.TypeValidator', 'string', ('Config_System_type_string'));
        $this->addValidator('description', 'type', 'propel.validator.TypeValidator', 'string', ('Config_Description_type_string'));
        $this->addValidator('type', 'type', 'propel.validator.TypeValidator', 'string', ('Config_Type_type_string'));
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
  'set_menu_priority' => '0',
  'set_parent_menu' => 'Settings',
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

} // ConfigTableMap
