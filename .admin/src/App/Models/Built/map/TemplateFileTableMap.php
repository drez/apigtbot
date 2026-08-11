<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'template_file' table.
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
class TemplateFileTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.TemplateFileTableMap';

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
        $this->setName('template_file');
        $this->setPhpName('TemplateFile');
        $this->setClassname('App\\TemplateFile');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_template_file', 'IdTemplateFile', 'INTEGER', true, 11, null);
        $this->addForeignKey('id_template', 'IdTemplate', 'INTEGER', 'template', 'id_template', true, null, null);
        $this->addColumn('name', 'Name', 'VARCHAR', false, 100, null);
        $this->addColumn('file', 'File', 'VARCHAR', false, 500, null);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_template_file', 'required', 'propel.validator.RequiredValidator', '', ('TemplateFile_IdTemplateFile_required'));
        $this->addValidator('id_template_file', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('TemplateFile_IdTemplateFile_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('id_template', 'required', 'propel.validator.RequiredValidator', '', ('TemplateFile_IdTemplate_required'));
        $this->addValidator('id_template', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('TemplateFile_IdTemplate_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('name', 'type', 'propel.validator.TypeValidator', 'string', ('TemplateFile_Name_type_string'));
        $this->addValidator('file', 'type', 'propel.validator.TypeValidator', 'string', ('TemplateFile_File_type_string'));
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Template', 'App\\Template', RelationMap::MANY_TO_ONE, array('id_template' => 'id_template', ), 'CASCADE', null);
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
  'is_file_upload_table' => '{
            "thumbnail": "Yes",
            "filters" : {
                    "max_file_size" : "10mb"
            },
            "image_support":"yes"}',
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

} // TemplateFileTableMap
