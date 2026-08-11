<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'oauth_refresh_token' table.
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
class OauthRefreshTokenTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.OauthRefreshTokenTableMap';

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
        $this->setName('oauth_refresh_token');
        $this->setPhpName('OauthRefreshToken');
        $this->setClassname('App\\OauthRefreshToken');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_oauth_refresh_token', 'IdOauthRefreshToken', 'INTEGER', true, 11, null);
        $this->addColumn('token_id', 'TokenId', 'VARCHAR', true, 128, null);
        $this->addColumn('access_token_id', 'AccessTokenId', 'VARCHAR', false, 128, null);
        $this->addForeignKey('id_authy', 'IdAuthy', 'INTEGER', 'authy', 'id_authy', false, 11, null);
        $this->addColumn('client_id', 'ClientId', 'VARCHAR', false, 80, null);
        $this->addColumn('expires', 'Expires', 'INTEGER', false, null, null);
        $this->addColumn('revoked', 'Revoked', 'ENUM', false, null, 'No');
        $this->getColumn('revoked', false)->setValueSet(array (
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
        $this->addValidator('id_oauth_refresh_token', 'required', 'propel.validator.RequiredValidator', '', ('OauthRefreshToken_IdOauthRefreshToken_required'));
        $this->addValidator('id_oauth_refresh_token', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('OauthRefreshToken_IdOauthRefreshToken_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('token_id', 'required', 'propel.validator.RequiredValidator', '', ('OauthRefreshToken_TokenId_required'));
        $this->addValidator('token_id', 'type', 'propel.validator.TypeValidator', 'string', ('OauthRefreshToken_TokenId_type_string'));
        $this->addValidator('access_token_id', 'type', 'propel.validator.TypeValidator', 'string', ('OauthRefreshToken_AccessTokenId_type_string'));
        $this->addValidator('id_authy', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('OauthRefreshToken_IdAuthy_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('client_id', 'type', 'propel.validator.TypeValidator', 'string', ('OauthRefreshToken_ClientId_type_string'));
        $this->addValidator('expires', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('OauthRefreshToken_Expires_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('revoked', 'type', 'propel.validator.TypeValidator', 'string', ('OauthRefreshToken_Revoked_type_string'));
        $this->addValidator('created_at', 'match', 'propel.validator.MatchValidator', '', ('OauthRefreshToken_CreatedAt_match'));
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

} // OauthRefreshTokenTableMap
