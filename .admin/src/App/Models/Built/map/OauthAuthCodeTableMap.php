<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'oauth_auth_code' table.
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
class OauthAuthCodeTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.OauthAuthCodeTableMap';

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
        $this->setName('oauth_auth_code');
        $this->setPhpName('OauthAuthCode');
        $this->setClassname('App\\OauthAuthCode');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_oauth_auth_code', 'IdOauthAuthCode', 'INTEGER', true, 11, null);
        $this->addColumn('code_id', 'CodeId', 'VARCHAR', true, 128, null);
        $this->addForeignKey('id_authy', 'IdAuthy', 'INTEGER', 'authy', 'id_authy', false, 11, null);
        $this->addColumn('client_id', 'ClientId', 'VARCHAR', false, 80, null);
        $this->addColumn('scopes', 'Scopes', 'VARCHAR', false, 191, null);
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
        $this->addValidator('id_oauth_auth_code', 'required', 'propel.validator.RequiredValidator', '', ('OauthAuthCode_IdOauthAuthCode_required'));
        $this->addValidator('id_oauth_auth_code', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('OauthAuthCode_IdOauthAuthCode_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('code_id', 'required', 'propel.validator.RequiredValidator', '', ('OauthAuthCode_CodeId_required'));
        $this->addValidator('code_id', 'type', 'propel.validator.TypeValidator', 'string', ('OauthAuthCode_CodeId_type_string'));
        $this->addValidator('id_authy', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('OauthAuthCode_IdAuthy_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('client_id', 'type', 'propel.validator.TypeValidator', 'string', ('OauthAuthCode_ClientId_type_string'));
        $this->addValidator('scopes', 'type', 'propel.validator.TypeValidator', 'string', ('OauthAuthCode_Scopes_type_string'));
        $this->addValidator('expires', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('OauthAuthCode_Expires_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('revoked', 'type', 'propel.validator.TypeValidator', 'string', ('OauthAuthCode_Revoked_type_string'));
        $this->addValidator('created_at', 'match', 'propel.validator.MatchValidator', '', ('OauthAuthCode_CreatedAt_match'));
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

} // OauthAuthCodeTableMap
