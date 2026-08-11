<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'push_device' table.
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
class PushDeviceTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.PushDeviceTableMap';

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
        $this->setName('push_device');
        $this->setPhpName('PushDevice');
        $this->setClassname('App\\PushDevice');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_push_device', 'IdPushDevice', 'INTEGER', true, 11, null);
        $this->addForeignKey('id_authy', 'IdAuthy', 'INTEGER', 'authy', 'id_authy', true, 11, null);
        $this->addColumn('token', 'Token', 'VARCHAR', true, 255, null);
        $this->addColumn('platform', 'Platform', 'ENUM', false, null, 'ios');
        $this->getColumn('platform', false)->setValueSet(array (
  0 => 'ios',
  1 => 'android',
  2 => 'web',
));
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_push_device', 'required', 'propel.validator.RequiredValidator', '', ('PushDevice_IdPushDevice_required'));
        $this->addValidator('id_push_device', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('PushDevice_IdPushDevice_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('id_authy', 'required', 'propel.validator.RequiredValidator', '', ('PushDevice_IdAuthy_required'));
        $this->addValidator('id_authy', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('PushDevice_IdAuthy_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('token', 'required', 'propel.validator.RequiredValidator', '', ('PushDevice_Token_required'));
        $this->addValidator('token', 'type', 'propel.validator.TypeValidator', 'string', ('PushDevice_Token_type_string'));
        $this->addValidator('platform', 'type', 'propel.validator.TypeValidator', 'string', ('PushDevice_Platform_type_string'));
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('AuthyRelatedByIdAuthy', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_authy' => 'id_authy', ), 'CASCADE', null);
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
  'set_menu_priority' => '210',
  'set_child_colunms' => '{"id_authy":["username"]}',
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

} // PushDeviceTableMap
