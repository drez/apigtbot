<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'template' table.
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
class TemplateTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.TemplateTableMap';

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
        $this->setName('template');
        $this->setPhpName('Template');
        $this->setClassname('App\\Template');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_template', 'IdTemplate', 'INTEGER', true, 11, null);
        $this->addColumn('name', 'Name', 'VARCHAR', true, 100, null);
        $this->addColumn('subject', 'Subject', 'VARCHAR', false, 200, null);
        $this->addColumn('color_1', 'Color1', 'VARCHAR', false, 10, null);
        $this->addColumn('color_2', 'Color2', 'VARCHAR', false, 10, null);
        $this->addColumn('color_3', 'Color3', 'VARCHAR', false, 10, null);
        $this->addColumn('status', 'Status', 'ENUM', true, null, 'Active');
        $this->getColumn('status', false)->setValueSet(array (
  0 => 'Active',
  1 => 'Inactive',
));
        $this->addColumn('body', 'Body', 'LONGVARCHAR', false, 1023, null);
        $this->addColumn('footer', 'Footer', 'LONGVARCHAR', false, 1023, null);
        $this->addColumn('lang', 'Lang', 'ENUM', false, null, 'fr_CA');
        $this->getColumn('lang', false)->setValueSet(array (
  0 => 'fr_CA',
  1 => 'en_US',
));
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_template', 'required', 'propel.validator.RequiredValidator', '', ('Template_IdTemplate_required'));
        $this->addValidator('id_template', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('Template_IdTemplate_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('name', 'required', 'propel.validator.RequiredValidator', '', ('Template_Name_required'));
        $this->addValidator('name', 'type', 'propel.validator.TypeValidator', 'string', ('Template_Name_type_string'));
        $this->addValidator('subject', 'type', 'propel.validator.TypeValidator', 'string', ('Template_Subject_type_string'));
        $this->addValidator('color_1', 'type', 'propel.validator.TypeValidator', 'string', ('Template_Color1_type_string'));
        $this->addValidator('color_2', 'type', 'propel.validator.TypeValidator', 'string', ('Template_Color2_type_string'));
        $this->addValidator('color_3', 'type', 'propel.validator.TypeValidator', 'string', ('Template_Color3_type_string'));
        $this->addValidator('status', 'required', 'propel.validator.RequiredValidator', '', ('Template_Status_required'));
        $this->addValidator('status', 'type', 'propel.validator.TypeValidator', 'string', ('Template_Status_type_string'));
        $this->addValidator('body', 'type', 'propel.validator.TypeValidator', 'string', ('Template_Body_type_string'));
        $this->addValidator('footer', 'type', 'propel.validator.TypeValidator', 'string', ('Template_Footer_type_string'));
        $this->addValidator('lang', 'type', 'propel.validator.TypeValidator', 'string', ('Template_Lang_type_string'));
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('AuthyGroup', 'App\\AuthyGroup', RelationMap::MANY_TO_ONE, array('id_group_creation' => 'id_authy_group', ), null, null);
        $this->addRelation('AuthyRelatedByIdCreation', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_creation' => 'id_authy', ), null, null);
        $this->addRelation('AuthyRelatedByIdModification', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_modification' => 'id_authy', ), null, null);
        $this->addRelation('TemplateFile', 'App\\TemplateFile', RelationMap::ONE_TO_MANY, array('id_template' => 'id_template', ), 'CASCADE', null, 'TemplateFiles');
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
  'add_search_columns' => '{"Name": [["name", "%val"]]}',
  'set_order_list_columns' => '[["date_creation", "DESC"]]',
  'is_wysiwyg_colunms' => '["body", "footer"]',
  'set_trusted_html_columns' => '["body", "footer"]',
  'set_list_hide_columns' => '["color_1", "color_2", "color_3", "body", "footer"]',
  'with_child_tables' => '["template_file"]',
  'add_child_insert_wysiwyg_tables' => '["template_file"]',
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

} // TemplateTableMap
