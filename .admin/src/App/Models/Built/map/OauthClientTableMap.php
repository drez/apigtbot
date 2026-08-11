<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'oauth_client' table.
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
class OauthClientTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.OauthClientTableMap';

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
        $this->setName('oauth_client');
        $this->setPhpName('OauthClient');
        $this->setClassname('App\\OauthClient');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_oauth_client', 'IdOauthClient', 'INTEGER', true, 11, null);
        $this->addColumn('client_id', 'ClientId', 'VARCHAR', true, 80, null);
        $this->addColumn('client_secret_hash', 'ClientSecretHash', 'VARCHAR', false, 255, null);
        $this->addColumn('name', 'Name', 'VARCHAR', false, 191, null);
        $this->addColumn('redirect_uris', 'RedirectUris', 'LONGVARCHAR', false, null, null);
        $this->addColumn('grant_types', 'GrantTypes', 'VARCHAR', false, 191, null);
        $this->addColumn('scopes', 'Scopes', 'VARCHAR', false, 191, null);
        $this->addColumn('is_confidential', 'IsConfidential', 'ENUM', false, null, 'No');
        $this->getColumn('is_confidential', false)->setValueSet(array (
  0 => 'No',
  1 => 'Yes',
));
        $this->addColumn('created_at', 'CreatedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_oauth_client', 'required', 'propel.validator.RequiredValidator', '', ('OauthClient_IdOauthClient_required'));
        $this->addValidator('id_oauth_client', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('OauthClient_IdOauthClient_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('client_id', 'required', 'propel.validator.RequiredValidator', '', ('OauthClient_ClientId_required'));
        $this->addValidator('client_id', 'type', 'propel.validator.TypeValidator', 'string', ('OauthClient_ClientId_type_string'));
        $this->addValidator('client_secret_hash', 'type', 'propel.validator.TypeValidator', 'string', ('OauthClient_ClientSecretHash_type_string'));
        $this->addValidator('name', 'type', 'propel.validator.TypeValidator', 'string', ('OauthClient_Name_type_string'));
        $this->addValidator('redirect_uris', 'type', 'propel.validator.TypeValidator', 'string', ('OauthClient_RedirectUris_type_string'));
        $this->addValidator('grant_types', 'type', 'propel.validator.TypeValidator', 'string', ('OauthClient_GrantTypes_type_string'));
        $this->addValidator('scopes', 'type', 'propel.validator.TypeValidator', 'string', ('OauthClient_Scopes_type_string'));
        $this->addValidator('is_confidential', 'type', 'propel.validator.TypeValidator', 'string', ('OauthClient_IsConfidential_type_string'));
        $this->addValidator('created_at', 'match', 'propel.validator.MatchValidator', '', ('OauthClient_CreatedAt_match'));
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

} // OauthClientTableMap
