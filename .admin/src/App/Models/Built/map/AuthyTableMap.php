<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'authy' table.
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
class AuthyTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.AuthyTableMap';

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
        $this->setName('authy');
        $this->setPhpName('Authy');
        $this->setClassname('App\\Authy');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_authy', 'IdAuthy', 'INTEGER', true, 11, null);
        $this->addColumn('validation_key', 'ValidationKey', 'VARCHAR', false, 32, null);
        $this->addColumn('username', 'Username', 'VARCHAR', false, 32, null);
        $this->addColumn('fullname', 'Fullname', 'VARCHAR', false, 100, null);
        $this->addColumn('email', 'Email', 'VARCHAR', true, 100, null);
        $this->addColumn('passwd_hash', 'PasswdHash', 'VARCHAR', true, 255, null);
        $this->addColumn('expire', 'Expire', 'DATE', false, null, '0000-00-00');
        $this->addColumn('deactivate', 'Deactivate', 'ENUM', false, null, 'No');
        $this->getColumn('deactivate', false)->setValueSet(array (
  0 => 'Yes',
  1 => 'No',
));
        $this->addColumn('language', 'Language', 'ENUM', false, null, 'en_US');
        $this->getColumn('language', false)->setValueSet(array (
  0 => 'en_US',
  1 => 'fr_CA',
));
        $this->addColumn('theme', 'Theme', 'ENUM', false, null, 'mint');
        $this->getColumn('theme', false)->setValueSet(array (
  0 => 'mint',
  1 => 'ink',
  2 => 'indigo',
  3 => 'terracotta',
  4 => 'graphite',
  5 => 'slate',
  6 => 'dusk',
));
        $this->addColumn('google_sub', 'GoogleSub', 'VARCHAR', false, 64, null);
        $this->addColumn('google_email', 'GoogleEmail', 'VARCHAR', false, 255, null);
        $this->addColumn('reset_token_hash', 'ResetTokenHash', 'VARCHAR', false, 255, null);
        $this->addColumn('reset_token_expires', 'ResetTokenExpires', 'INTEGER', false, 10, null);
        $this->addColumn('id_tenant', 'IdTenant', 'INTEGER', false, 10, 1);
        $this->addColumn('location_address', 'LocationAddress', 'VARCHAR', false, 500, null);
        $this->addColumn('location_lat', 'LocationLat', 'DECIMAL', false, 13, null);
        $this->addColumn('location_lng', 'LocationLng', 'DECIMAL', false, 13, null);
        $this->addColumn('is_root', 'IsRoot', 'ENUM', true, null, 'No');
        $this->getColumn('is_root', false)->setValueSet(array (
  0 => 'Yes',
  1 => 'No',
));
        $this->addForeignKey('id_authy_group', 'IdAuthyGroup', 'INTEGER', 'authy_group', 'id_authy_group', true, null, 1);
        $this->addColumn('is_system', 'IsSystem', 'ENUM', true, null, 'No');
        $this->getColumn('is_system', false)->setValueSet(array (
  0 => 'Yes',
  1 => 'No',
));
        $this->addColumn('rights_all', 'RightsAll', 'LONGVARCHAR', false, null, null);
        $this->addColumn('rights_group', 'RightsGroup', 'LONGVARCHAR', false, null, null);
        $this->addColumn('rights_owner', 'RightsOwner', 'LONGVARCHAR', false, null, null);
        $this->addColumn('onglet', 'Onglet', 'LONGVARCHAR', false, null, null);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('email', 'required', 'propel.validator.RequiredValidator', '', 'authy_email_required');
        $this->addValidator('email', 'unique', 'propel.validator.UniqueValidator', '', 'authy_email_in_use');
        $this->addValidator('passwd_hash', 'required', 'propel.validator.RequiredValidator', '', 'authy_password_required');
        $this->addValidator('id_authy', 'required', 'propel.validator.RequiredValidator', '', ('Authy_IdAuthy_required'));
        $this->addValidator('id_authy', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('Authy_IdAuthy_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('validation_key', 'type', 'propel.validator.TypeValidator', 'string', ('Authy_ValidationKey_type_string'));
        $this->addValidator('username', 'type', 'propel.validator.TypeValidator', 'string', ('Authy_Username_type_string'));
        $this->addValidator('fullname', 'type', 'propel.validator.TypeValidator', 'string', ('Authy_Fullname_type_string'));
        $this->addValidator('email', 'type', 'propel.validator.TypeValidator', 'string', ('Authy_Email_type_string'));
        $this->addValidator('passwd_hash', 'type', 'propel.validator.TypeValidator', 'string', ('Authy_PasswdHash_type_string'));
        $this->addValidator('expire', 'match', 'propel.validator.MatchValidator', '', ('Authy_Expire_match'));
        $this->addValidator('deactivate', 'type', 'propel.validator.TypeValidator', 'string', ('Authy_Deactivate_type_string'));
        $this->addValidator('language', 'type', 'propel.validator.TypeValidator', 'string', ('Authy_Language_type_string'));
        $this->addValidator('theme', 'type', 'propel.validator.TypeValidator', 'string', ('Authy_Theme_type_string'));
        $this->addValidator('google_sub', 'type', 'propel.validator.TypeValidator', 'string', ('Authy_GoogleSub_type_string'));
        $this->addValidator('google_email', 'type', 'propel.validator.TypeValidator', 'string', ('Authy_GoogleEmail_type_string'));
        $this->addValidator('reset_token_hash', 'type', 'propel.validator.TypeValidator', 'string', ('Authy_ResetTokenHash_type_string'));
        $this->addValidator('reset_token_expires', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('Authy_ResetTokenExpires_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('id_tenant', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('Authy_IdTenant_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('location_address', 'type', 'propel.validator.TypeValidator', 'string', ('Authy_LocationAddress_type_string'));
        $this->addValidator('is_root', 'required', 'propel.validator.RequiredValidator', '', ('Authy_IsRoot_required'));
        $this->addValidator('is_root', 'type', 'propel.validator.TypeValidator', 'string', ('Authy_IsRoot_type_string'));
        $this->addValidator('is_system', 'required', 'propel.validator.RequiredValidator', '', ('Authy_IsSystem_required'));
        $this->addValidator('is_system', 'type', 'propel.validator.TypeValidator', 'string', ('Authy_IsSystem_type_string'));
        $this->addValidator('rights_all', 'type', 'propel.validator.TypeValidator', 'string', ('Authy_RightsAll_type_string'));
        $this->addValidator('rights_group', 'type', 'propel.validator.TypeValidator', 'string', ('Authy_RightsGroup_type_string'));
        $this->addValidator('rights_owner', 'type', 'propel.validator.TypeValidator', 'string', ('Authy_RightsOwner_type_string'));
        $this->addValidator('onglet', 'type', 'propel.validator.TypeValidator', 'string', ('Authy_Onglet_type_string'));
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('AuthyGroupRelatedByIdAuthyGroup', 'App\\AuthyGroup', RelationMap::MANY_TO_ONE, array('id_authy_group' => 'id_authy_group', ), 'CASCADE', null);
        $this->addRelation('AuthyGroupRelatedByIdGroupCreation', 'App\\AuthyGroup', RelationMap::MANY_TO_ONE, array('id_group_creation' => 'id_authy_group', ), null, null);
        $this->addRelation('AuthyRelatedByIdCreation', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_creation' => 'id_authy', ), null, null);
        $this->addRelation('AuthyRelatedByIdModification', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_modification' => 'id_authy', ), null, null);
        $this->addRelation('PushDeviceRelatedByIdAuthy', 'App\\PushDevice', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_authy', ), 'CASCADE', null, 'PushDevicesRelatedByIdAuthy');
        $this->addRelation('AuthyGroupXRelatedByIdAuthy', 'App\\AuthyGroupX', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_authy', ), null, 'CASCADE', 'AuthyGroupxesRelatedByIdAuthy');
        $this->addRelation('AuthyLog', 'App\\AuthyLog', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_authy', ), null, 'CASCADE', 'AuthyLogs');
        $this->addRelation('AuthyRelatedByIdAuthy0', 'App\\Authy', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'AuthiesRelatedByIdAuthy0');
        $this->addRelation('AuthyRelatedByIdAuthy1', 'App\\Authy', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'AuthiesRelatedByIdAuthy1');
        $this->addRelation('PushDeviceRelatedByIdCreation', 'App\\PushDevice', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'PushDevicesRelatedByIdCreation');
        $this->addRelation('PushDeviceRelatedByIdModification', 'App\\PushDevice', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'PushDevicesRelatedByIdModification');
        $this->addRelation('CountryRelatedByIdCreation', 'App\\Country', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'CountriesRelatedByIdCreation');
        $this->addRelation('CountryRelatedByIdModification', 'App\\Country', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'CountriesRelatedByIdModification');
        $this->addRelation('GridRunRelatedByIdCreation', 'App\\GridRun', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'GridRunsRelatedByIdCreation');
        $this->addRelation('GridRunRelatedByIdModification', 'App\\GridRun', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'GridRunsRelatedByIdModification');
        $this->addRelation('BotOrderRelatedByIdCreation', 'App\\BotOrder', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'BotOrdersRelatedByIdCreation');
        $this->addRelation('BotOrderRelatedByIdModification', 'App\\BotOrder', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'BotOrdersRelatedByIdModification');
        $this->addRelation('TradeCycleRelatedByIdCreation', 'App\\TradeCycle', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'TradeCyclesRelatedByIdCreation');
        $this->addRelation('TradeCycleRelatedByIdModification', 'App\\TradeCycle', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'TradeCyclesRelatedByIdModification');
        $this->addRelation('BotEventRelatedByIdCreation', 'App\\BotEvent', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'BotEventsRelatedByIdCreation');
        $this->addRelation('BotEventRelatedByIdModification', 'App\\BotEvent', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'BotEventsRelatedByIdModification');
        $this->addRelation('BotCommandRelatedByIdCreation', 'App\\BotCommand', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'BotCommandsRelatedByIdCreation');
        $this->addRelation('BotCommandRelatedByIdModification', 'App\\BotCommand', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'BotCommandsRelatedByIdModification');
        $this->addRelation('SimWalletRelatedByIdCreation', 'App\\SimWallet', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'SimWalletsRelatedByIdCreation');
        $this->addRelation('SimWalletRelatedByIdModification', 'App\\SimWallet', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'SimWalletsRelatedByIdModification');
        $this->addRelation('MarketSummaryRelatedByIdCreation', 'App\\MarketSummary', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'MarketSummariesRelatedByIdCreation');
        $this->addRelation('MarketSummaryRelatedByIdModification', 'App\\MarketSummary', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'MarketSummariesRelatedByIdModification');
        $this->addRelation('MarketRegimeRelatedByIdCreation', 'App\\MarketRegime', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'MarketRegimesRelatedByIdCreation');
        $this->addRelation('MarketRegimeRelatedByIdModification', 'App\\MarketRegime', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'MarketRegimesRelatedByIdModification');
        $this->addRelation('BotDecisionRelatedByIdCreation', 'App\\BotDecision', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'BotDecisionsRelatedByIdCreation');
        $this->addRelation('BotDecisionRelatedByIdModification', 'App\\BotDecision', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'BotDecisionsRelatedByIdModification');
        $this->addRelation('AuthyGroupRelatedByIdCreation', 'App\\AuthyGroup', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'AuthyGroupsRelatedByIdCreation');
        $this->addRelation('AuthyGroupRelatedByIdModification', 'App\\AuthyGroup', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'AuthyGroupsRelatedByIdModification');
        $this->addRelation('AuthyGroupXRelatedByIdCreation', 'App\\AuthyGroupX', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'AuthyGroupxesRelatedByIdCreation');
        $this->addRelation('AuthyGroupXRelatedByIdModification', 'App\\AuthyGroupX', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'AuthyGroupxesRelatedByIdModification');
        $this->addRelation('ConfigRelatedByIdCreation', 'App\\Config', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'ConfigsRelatedByIdCreation');
        $this->addRelation('ConfigRelatedByIdModification', 'App\\Config', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'ConfigsRelatedByIdModification');
        $this->addRelation('ApiRbacRelatedByIdCreation', 'App\\ApiRbac', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'ApiRbacsRelatedByIdCreation');
        $this->addRelation('ApiRbacRelatedByIdModification', 'App\\ApiRbac', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'ApiRbacsRelatedByIdModification');
        $this->addRelation('ApiLog', 'App\\ApiLog', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_authy', ), 'CASCADE', null, 'ApiLogs');
        $this->addRelation('TemplateRelatedByIdCreation', 'App\\Template', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'TemplatesRelatedByIdCreation');
        $this->addRelation('TemplateRelatedByIdModification', 'App\\Template', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'TemplatesRelatedByIdModification');
        $this->addRelation('TemplateFileRelatedByIdCreation', 'App\\TemplateFile', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'TemplateFilesRelatedByIdCreation');
        $this->addRelation('TemplateFileRelatedByIdModification', 'App\\TemplateFile', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'TemplateFilesRelatedByIdModification');
        $this->addRelation('AuthyRefreshTokenRelatedByIdAuthy', 'App\\AuthyRefreshToken', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_authy', ), 'CASCADE', null, 'AuthyRefreshTokensRelatedByIdAuthy');
        $this->addRelation('AuthyRefreshTokenRelatedByIdCreation', 'App\\AuthyRefreshToken', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'AuthyRefreshTokensRelatedByIdCreation');
        $this->addRelation('AuthyRefreshTokenRelatedByIdModification', 'App\\AuthyRefreshToken', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'AuthyRefreshTokensRelatedByIdModification');
        $this->addRelation('OauthClientRelatedByIdCreation', 'App\\OauthClient', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'OauthClientsRelatedByIdCreation');
        $this->addRelation('OauthClientRelatedByIdModification', 'App\\OauthClient', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'OauthClientsRelatedByIdModification');
        $this->addRelation('OauthAuthCodeRelatedByIdAuthy', 'App\\OauthAuthCode', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_authy', ), 'CASCADE', null, 'OauthAuthCodesRelatedByIdAuthy');
        $this->addRelation('OauthAuthCodeRelatedByIdCreation', 'App\\OauthAuthCode', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'OauthAuthCodesRelatedByIdCreation');
        $this->addRelation('OauthAuthCodeRelatedByIdModification', 'App\\OauthAuthCode', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'OauthAuthCodesRelatedByIdModification');
        $this->addRelation('OauthAccessTokenRelatedByIdAuthy', 'App\\OauthAccessToken', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_authy', ), 'CASCADE', null, 'OauthAccessTokensRelatedByIdAuthy');
        $this->addRelation('OauthAccessTokenRelatedByIdCreation', 'App\\OauthAccessToken', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'OauthAccessTokensRelatedByIdCreation');
        $this->addRelation('OauthAccessTokenRelatedByIdModification', 'App\\OauthAccessToken', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'OauthAccessTokensRelatedByIdModification');
        $this->addRelation('OauthRefreshTokenRelatedByIdAuthy', 'App\\OauthRefreshToken', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_authy', ), 'CASCADE', null, 'OauthRefreshTokensRelatedByIdAuthy');
        $this->addRelation('OauthRefreshTokenRelatedByIdCreation', 'App\\OauthRefreshToken', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'OauthRefreshTokensRelatedByIdCreation');
        $this->addRelation('OauthRefreshTokenRelatedByIdModification', 'App\\OauthRefreshToken', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'OauthRefreshTokensRelatedByIdModification');
        $this->addRelation('MessageI18nRelatedByIdCreation', 'App\\MessageI18n', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_creation', ), null, null, 'MessageI18nsRelatedByIdCreation');
        $this->addRelation('MessageI18nRelatedByIdModification', 'App\\MessageI18n', RelationMap::ONE_TO_MANY, array('id_authy' => 'id_modification', ), null, null, 'MessageI18nsRelatedByIdModification');
        $this->addRelation('AuthyGroupRelatedByIdAuthyGroups', 'App\\AuthyGroup', RelationMap::MANY_TO_MANY, array(), 'CASCADE', null, 'AuthyGroupsRelatedByIdAuthyGroup');
        $this->addRelation('AuthyGroupRelatedByIdGroupCreations', 'App\\AuthyGroup', RelationMap::MANY_TO_MANY, array(), null, null, 'AuthyGroupsRelatedByIdGroupCreation');
        $this->addRelation('AuthyRelatedByIdCreations', 'App\\Authy', RelationMap::MANY_TO_MANY, array(), null, null, 'AuthiesRelatedByIdCreation');
        $this->addRelation('AuthyRelatedByIdModifications', 'App\\Authy', RelationMap::MANY_TO_MANY, array(), null, null, 'AuthiesRelatedByIdModification');
        $this->addRelation('AuthyGroupRelatedByIdAuthyGroups', 'App\\AuthyGroup', RelationMap::MANY_TO_MANY, array(), 'CASCADE', null, 'AuthyGroupsRelatedByIdAuthyGroup');
        $this->addRelation('AuthyRelatedByIdAuthys', 'App\\Authy', RelationMap::MANY_TO_MANY, array(), null, 'CASCADE', 'AuthiesRelatedByIdAuthy');
        $this->addRelation('AuthyGroupRelatedByIdGroupCreations', 'App\\AuthyGroup', RelationMap::MANY_TO_MANY, array(), null, null, 'AuthyGroupsRelatedByIdGroupCreation');
        $this->addRelation('AuthyRelatedByIdModifications', 'App\\Authy', RelationMap::MANY_TO_MANY, array(), null, null, 'AuthiesRelatedByIdModification');
        $this->addRelation('AuthyGroupRelatedByIdAuthyGroups', 'App\\AuthyGroup', RelationMap::MANY_TO_MANY, array(), 'CASCADE', null, 'AuthyGroupsRelatedByIdAuthyGroup');
        $this->addRelation('AuthyRelatedByIdAuthys', 'App\\Authy', RelationMap::MANY_TO_MANY, array(), null, 'CASCADE', 'AuthiesRelatedByIdAuthy');
        $this->addRelation('AuthyGroupRelatedByIdGroupCreations', 'App\\AuthyGroup', RelationMap::MANY_TO_MANY, array(), null, null, 'AuthyGroupsRelatedByIdGroupCreation');
        $this->addRelation('AuthyRelatedByIdCreations', 'App\\Authy', RelationMap::MANY_TO_MANY, array(), null, null, 'AuthiesRelatedByIdCreation');
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
  'is_auth_table' => 'true',
  'is_root_columns' => '["is_root"]',
  'set_password_columns' => '["passwd_hash"]',
  'is_rights_column' => '["rights_all","rights_owner","rights_group"]',
  'add_tab_columns' => '{"Rights":"rights_all"}',
  'set_list_hide_columns' => '["rights","passwd_hash","rights_all","rights_owner","rights_group","google_sub","google_email","reset_token_hash","reset_token_expires"]',
  'i18n_langs' => '["en_US"]',
  'logo_url' => '',
  'set_parent_menu' => 'Settings',
  'set_input_options' => '{"location_address":{"type":"location","lat":"location_lat","lng":"location_lng","country":"ca"}}',
  'set_menu_priority' => '200',
  'add_search_columns' => '{"Name":[["username","%val","or"],["email","%val"]],"Primary group":[["id_authy_group","%val"]]}',
  'with_child_tables' => '["authy_group_x","authy_log"]',
  'with_refresh_tokens' => '1',
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

} // AuthyTableMap
