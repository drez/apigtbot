<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'authy_group' table.
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
class AuthyGroupTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.AuthyGroupTableMap';

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
        $this->setName('authy_group');
        $this->setPhpName('AuthyGroup');
        $this->setClassname('App\\AuthyGroup');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_authy_group', 'IdAuthyGroup', 'INTEGER', true, null, null);
        $this->addColumn('name', 'Name', 'VARCHAR', false, 50, null);
        $this->addColumn('desc', 'Desc', 'VARCHAR', false, 32, null);
        $this->addColumn('default_group', 'DefaultGroup', 'ENUM', true, null, null);
        $this->getColumn('default_group', false)->setValueSet(array (
  0 => 'No',
  1 => 'Yes',
));
        $this->addColumn('admin', 'Admin', 'ENUM', true, null, null);
        $this->getColumn('admin', false)->setValueSet(array (
  0 => 'No',
  1 => 'Yes',
));
        $this->addColumn('rights_all', 'RightsAll', 'VARCHAR', false, 1023, null);
        $this->addColumn('rights_owner', 'RightsOwner', 'VARCHAR', false, 1023, null);
        $this->addColumn('rights_group', 'RightsGroup', 'VARCHAR', false, 1023, null);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('name', 'required', 'propel.validator.RequiredValidator', '', 'group_name_required');
        $this->addValidator('name', 'type', 'propel.validator.TypeValidator', 'string', ('AuthyGroup_Name_type_string'));
        $this->addValidator('desc', 'type', 'propel.validator.TypeValidator', 'string', ('AuthyGroup_Desc_type_string'));
        $this->addValidator('default_group', 'required', 'propel.validator.RequiredValidator', '', ('AuthyGroup_DefaultGroup_required'));
        $this->addValidator('default_group', 'type', 'propel.validator.TypeValidator', 'string', ('AuthyGroup_DefaultGroup_type_string'));
        $this->addValidator('admin', 'required', 'propel.validator.RequiredValidator', '', ('AuthyGroup_Admin_required'));
        $this->addValidator('admin', 'type', 'propel.validator.TypeValidator', 'string', ('AuthyGroup_Admin_type_string'));
        $this->addValidator('rights_all', 'type', 'propel.validator.TypeValidator', 'string', ('AuthyGroup_RightsAll_type_string'));
        $this->addValidator('rights_owner', 'type', 'propel.validator.TypeValidator', 'string', ('AuthyGroup_RightsOwner_type_string'));
        $this->addValidator('rights_group', 'type', 'propel.validator.TypeValidator', 'string', ('AuthyGroup_RightsGroup_type_string'));
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('AuthyGroupRelatedByIdGroupCreation', 'App\\AuthyGroup', RelationMap::MANY_TO_ONE, array('id_group_creation' => 'id_authy_group', ), null, null);
        $this->addRelation('AuthyRelatedByIdCreation', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_creation' => 'id_authy', ), null, null);
        $this->addRelation('AuthyRelatedByIdModification', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_modification' => 'id_authy', ), null, null);
        $this->addRelation('AuthyGroupXRelatedByIdAuthyGroup', 'App\\AuthyGroupX', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_authy_group', ), 'CASCADE', null, 'AuthyGroupxesRelatedByIdAuthyGroup');
        $this->addRelation('AuthyRelatedByIdAuthyGroup', 'App\\Authy', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_authy_group', ), 'CASCADE', null, 'AuthiesRelatedByIdAuthyGroup');
        $this->addRelation('AuthyRelatedByIdGroupCreation', 'App\\Authy', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'AuthiesRelatedByIdGroupCreation');
        $this->addRelation('PushDevice', 'App\\PushDevice', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'PushDevices');
        $this->addRelation('GridRun', 'App\\GridRun', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'GridRuns');
        $this->addRelation('FleetSlot', 'App\\FleetSlot', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'FleetSlots');
        $this->addRelation('RegimeEpisode', 'App\\RegimeEpisode', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'RegimeEpisodes');
        $this->addRelation('BotOrder', 'App\\BotOrder', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'BotOrders');
        $this->addRelation('TradeCycle', 'App\\TradeCycle', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'TradeCycles');
        $this->addRelation('BotEvent', 'App\\BotEvent', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'BotEvents');
        $this->addRelation('BotCommand', 'App\\BotCommand', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'BotCommands');
        $this->addRelation('SimWallet', 'App\\SimWallet', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'SimWallets');
        $this->addRelation('MarketSummary', 'App\\MarketSummary', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'MarketSummaries');
        $this->addRelation('MarketRegime', 'App\\MarketRegime', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'MarketRegimes');
        $this->addRelation('MarketCandle', 'App\\MarketCandle', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'MarketCandles');
        $this->addRelation('BotDecision', 'App\\BotDecision', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'BotDecisions');
        $this->addRelation('MarketOutlook', 'App\\MarketOutlook', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'MarketOutlooks');
        $this->addRelation('MarketOutlookState', 'App\\MarketOutlookState', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'MarketOutlookStates');
        $this->addRelation('WalletNav', 'App\\WalletNav', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'WalletNavs');
        $this->addRelation('AuthyGroupRelatedByIdAuthyGroup', 'App\\AuthyGroup', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'AuthyGroupsRelatedByIdAuthyGroup');
        $this->addRelation('AuthyGroupXRelatedByIdGroupCreation', 'App\\AuthyGroupX', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'AuthyGroupxesRelatedByIdGroupCreation');
        $this->addRelation('Config', 'App\\Config', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'Configs');
        $this->addRelation('ApiRbac', 'App\\ApiRbac', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'ApiRbacs');
        $this->addRelation('Template', 'App\\Template', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'Templates');
        $this->addRelation('TemplateFile', 'App\\TemplateFile', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'TemplateFiles');
        $this->addRelation('AuthyRefreshToken', 'App\\AuthyRefreshToken', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'AuthyRefreshTokens');
        $this->addRelation('GridRunAudit', 'App\\GridRunAudit', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'GridRunAudits');
        $this->addRelation('Country', 'App\\Country', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'Countries');
        $this->addRelation('OauthClient', 'App\\OauthClient', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'OauthClients');
        $this->addRelation('OauthAuthCode', 'App\\OauthAuthCode', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'OauthAuthCodes');
        $this->addRelation('OauthAccessToken', 'App\\OauthAccessToken', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'OauthAccessTokens');
        $this->addRelation('OauthRefreshToken', 'App\\OauthRefreshToken', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'OauthRefreshTokens');
        $this->addRelation('MessageI18n', 'App\\MessageI18n', RelationMap::ONE_TO_MANY, array('id_authy_group' => 'id_group_creation', ), null, null, 'MessageI18ns');
        $this->addRelation('AuthyRelatedByIdAuthys', 'App\\Authy', RelationMap::MANY_TO_MANY, array(), null, 'CASCADE', 'AuthiesRelatedByIdAuthy');
        $this->addRelation('AuthyGroupRelatedByIdGroupCreations', 'App\\AuthyGroup', RelationMap::MANY_TO_MANY, array(), null, null, 'AuthyGroupsRelatedByIdGroupCreation');
        $this->addRelation('AuthyRelatedByIdCreations', 'App\\Authy', RelationMap::MANY_TO_MANY, array(), null, null, 'AuthiesRelatedByIdCreation');
        $this->addRelation('AuthyRelatedByIdModifications', 'App\\Authy', RelationMap::MANY_TO_MANY, array(), null, null, 'AuthiesRelatedByIdModification');
        $this->addRelation('AuthyGroupRelatedByIdAuthyGroups', 'App\\AuthyGroup', RelationMap::MANY_TO_MANY, array(), 'CASCADE', null, 'AuthyGroupsRelatedByIdAuthyGroup');
        $this->addRelation('AuthyRelatedByIdAuthys', 'App\\Authy', RelationMap::MANY_TO_MANY, array(), null, 'CASCADE', 'AuthiesRelatedByIdAuthy');
        $this->addRelation('AuthyRelatedByIdCreations', 'App\\Authy', RelationMap::MANY_TO_MANY, array(), null, null, 'AuthiesRelatedByIdCreation');
        $this->addRelation('AuthyRelatedByIdModifications', 'App\\Authy', RelationMap::MANY_TO_MANY, array(), null, null, 'AuthiesRelatedByIdModification');
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
  'is_group_table' => 'true',
  'set_parent_menu' => 'Settings',
  'set_menu_priority' => '50',
  'is_rights_column' => '["rights_all", "rights_owner", "rights_group"]',
  'add_tab_columns' => '{"Rights":"rights_all"}',
  'set_list_hide_columns' => '["rights_all", "rights_owner", "rights_group"]',
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

} // AuthyGroupTableMap
