<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'message' table.
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
class MessageTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.MessageTableMap';

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
        $this->setName('message');
        $this->setPhpName('Message');
        $this->setClassname('App\\Message');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_message', 'IdMessage', 'INTEGER', true, null, null);
        $this->addColumn('label', 'Label', 'VARCHAR', true, 100, null);
        // validators
        $this->addValidator('label', 'required', 'propel.validator.RequiredValidator', '', 'message_label_required');
        $this->addValidator('id_message', 'required', 'propel.validator.RequiredValidator', '', ('Message_IdMessage_required'));
        $this->addValidator('id_message', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('Message_IdMessage_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('label', 'type', 'propel.validator.TypeValidator', 'string', ('Message_Label_type_string'));
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('MessageI18n', 'App\\MessageI18n', RelationMap::ONE_TO_MANY, array('id_message' => 'id_message', ), 'CASCADE', null, 'MessageI18ns');
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
  'add_search_columns' => '{"Label":[["label", "%val"]]}',
  'set_list_hide_columns' => '["text"]',
  'set_menu_priority' => '10',
  'set_readonly_columns' => '["label"]',
),
            'i18n' =>  array (
  'i18n_table' => '%TABLE%_i18n',
  'i18n_phpname' => '%PHPNAME%I18n',
  'i18n_columns' => 'text',
  'i18n_pk_name' => NULL,
  'locale_column' => 'locale',
  'default_locale' => NULL,
  'locale_alias' => '',
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

} // MessageTableMap
