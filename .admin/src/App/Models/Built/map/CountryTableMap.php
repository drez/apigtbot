<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'country' table.
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
class CountryTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.CountryTableMap';

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
        $this->setName('country');
        $this->setPhpName('Country');
        $this->setClassname('App\\Country');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_country', 'IdCountry', 'INTEGER', true, 11, null);
        $this->addColumn('name', 'Name', 'VARCHAR', false, 100, null);
        $this->addColumn('code', 'Code', 'VARCHAR', false, 3, null);
        $this->addColumn('timezone', 'Timezone', 'VARCHAR', false, 20, null);
        $this->addColumn('timezone_code', 'TimezoneCode', 'VARCHAR', false, 50, null);
        $this->addColumn('priority', 'Priority', 'INTEGER', false, 10, null);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('name', 'required', 'propel.validator.RequiredValidator', '', ('coutry_name_required'));
        $this->addValidator('id_country', 'required', 'propel.validator.RequiredValidator', '', ('Country_IdCountry_required'));
        $this->addValidator('id_country', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('Country_IdCountry_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('name', 'type', 'propel.validator.TypeValidator', 'string', ('Country_Name_type_string'));
        $this->addValidator('code', 'type', 'propel.validator.TypeValidator', 'string', ('Country_Code_type_string'));
        $this->addValidator('timezone', 'type', 'propel.validator.TypeValidator', 'string', ('Country_Timezone_type_string'));
        $this->addValidator('timezone_code', 'type', 'propel.validator.TypeValidator', 'string', ('Country_TimezoneCode_type_string'));
        $this->addValidator('priority', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('Country_Priority_match_/^(?:[0-9]*|null)$/'));
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

} // CountryTableMap
