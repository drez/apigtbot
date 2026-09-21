<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'wallet_nav' table.
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
class WalletNavTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.WalletNavTableMap';

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
        $this->setName('wallet_nav');
        $this->setPhpName('WalletNav');
        $this->setClassname('App\\WalletNav');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_wallet_nav', 'IdWalletNav', 'INTEGER', true, 11, null);
        $this->addColumn('mode', 'Mode', 'ENUM', true, null, 'sim');
        $this->getColumn('mode', false)->setValueSet(array (
  0 => 'sim',
  1 => 'real',
));
        $this->addColumn('equity_quote', 'EquityQuote', 'DECIMAL', true, 18, null);
        $this->addColumn('budget_quote', 'BudgetQuote', 'DECIMAL', true, 18, null);
        $this->addColumn('ref_symbol', 'RefSymbol', 'VARCHAR', false, 20, null);
        $this->addColumn('ref_price', 'RefPrice', 'DECIMAL', false, 18, null);
        $this->addColumn('unpriced', 'Unpriced', 'VARCHAR', false, 100, null);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_wallet_nav', 'required', 'propel.validator.RequiredValidator', '', ('WalletNav_IdWalletNav_required'));
        $this->addValidator('id_wallet_nav', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('WalletNav_IdWalletNav_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('mode', 'required', 'propel.validator.RequiredValidator', '', ('WalletNav_Mode_required'));
        $this->addValidator('mode', 'type', 'propel.validator.TypeValidator', 'string', ('WalletNav_Mode_type_string'));
        $this->addValidator('equity_quote', 'required', 'propel.validator.RequiredValidator', '', ('WalletNav_EquityQuote_required'));
        $this->addValidator('budget_quote', 'required', 'propel.validator.RequiredValidator', '', ('WalletNav_BudgetQuote_required'));
        $this->addValidator('ref_symbol', 'type', 'propel.validator.TypeValidator', 'string', ('WalletNav_RefSymbol_type_string'));
        $this->addValidator('unpriced', 'type', 'propel.validator.TypeValidator', 'string', ('WalletNav_Unpriced_type_string'));
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
  'set_menu_priority' => '7',
  'set_order_list_columns' => '[["date_creation","DESC"]]',
  'set_readonly_columns' => '["mode","equity_quote","budget_quote","ref_symbol","ref_price","unpriced"]',
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

} // WalletNavTableMap
