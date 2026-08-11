<?php

namespace App\om;

use \BasePeer;
use \Criteria;
use \PDO;
use \PDOStatement;
use \Propel;
use \PropelException;
use \PropelPDO;
use App\ApiLogPeer;
use App\Authy;
use App\AuthyGroupPeer;
use App\AuthyPeer;
use App\AuthyRefreshTokenPeer;
use App\OauthAccessTokenPeer;
use App\OauthAuthCodePeer;
use App\OauthRefreshTokenPeer;
use App\PushDevicePeer;
use App\map\AuthyTableMap;

/**
 * Base static class for performing query and update operations on the 'authy' table.
 *
 * User
 *
 * @package propel.generator..om
 */
abstract class BaseAuthyPeer
{

    /** the default database name for this class */
    const DATABASE_NAME = 'apigtbot';

    /** the table name for this class */
    const TABLE_NAME = 'authy';

    /** the related Propel class for this table */
    const OM_CLASS = 'App\\Authy';

    /** the related TableMap class for this table */
    const TM_CLASS = 'App\\map\\AuthyTableMap';

    /** The total number of columns. */
    const NUM_COLUMNS = 30;

    /** The number of lazy-loaded columns. */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /** The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS) */
    const NUM_HYDRATE_COLUMNS = 30;

    /** the column name for the id_authy field */
    const ID_AUTHY = 'authy.id_authy';

    /** the column name for the validation_key field */
    const VALIDATION_KEY = 'authy.validation_key';

    /** the column name for the username field */
    const USERNAME = 'authy.username';

    /** the column name for the fullname field */
    const FULLNAME = 'authy.fullname';

    /** the column name for the email field */
    const EMAIL = 'authy.email';

    /** the column name for the passwd_hash field */
    const PASSWD_HASH = 'authy.passwd_hash';

    /** the column name for the expire field */
    const EXPIRE = 'authy.expire';

    /** the column name for the deactivate field */
    const DEACTIVATE = 'authy.deactivate';

    /** the column name for the language field */
    const LANGUAGE = 'authy.language';

    /** the column name for the theme field */
    const THEME = 'authy.theme';

    /** the column name for the google_sub field */
    const GOOGLE_SUB = 'authy.google_sub';

    /** the column name for the google_email field */
    const GOOGLE_EMAIL = 'authy.google_email';

    /** the column name for the reset_token_hash field */
    const RESET_TOKEN_HASH = 'authy.reset_token_hash';

    /** the column name for the reset_token_expires field */
    const RESET_TOKEN_EXPIRES = 'authy.reset_token_expires';

    /** the column name for the id_tenant field */
    const ID_TENANT = 'authy.id_tenant';

    /** the column name for the location_address field */
    const LOCATION_ADDRESS = 'authy.location_address';

    /** the column name for the location_lat field */
    const LOCATION_LAT = 'authy.location_lat';

    /** the column name for the location_lng field */
    const LOCATION_LNG = 'authy.location_lng';

    /** the column name for the is_root field */
    const IS_ROOT = 'authy.is_root';

    /** the column name for the id_authy_group field */
    const ID_AUTHY_GROUP = 'authy.id_authy_group';

    /** the column name for the is_system field */
    const IS_SYSTEM = 'authy.is_system';

    /** the column name for the rights_all field */
    const RIGHTS_ALL = 'authy.rights_all';

    /** the column name for the rights_group field */
    const RIGHTS_GROUP = 'authy.rights_group';

    /** the column name for the rights_owner field */
    const RIGHTS_OWNER = 'authy.rights_owner';

    /** the column name for the onglet field */
    const ONGLET = 'authy.onglet';

    /** the column name for the date_creation field */
    const DATE_CREATION = 'authy.date_creation';

    /** the column name for the date_modification field */
    const DATE_MODIFICATION = 'authy.date_modification';

    /** the column name for the id_group_creation field */
    const ID_GROUP_CREATION = 'authy.id_group_creation';

    /** the column name for the id_creation field */
    const ID_CREATION = 'authy.id_creation';

    /** the column name for the id_modification field */
    const ID_MODIFICATION = 'authy.id_modification';

    /** The enumerated values for the deactivate field */
    const DEACTIVATE_YES = 'Yes';
    const DEACTIVATE_NO = 'No';

    /** The enumerated values for the language field */
    const LANGUAGE_EN_US = 'en_US';
    const LANGUAGE_FR_CA = 'fr_CA';

    /** The enumerated values for the theme field */
    const THEME_MINT = 'mint';
    const THEME_INK = 'ink';
    const THEME_INDIGO = 'indigo';
    const THEME_TERRACOTTA = 'terracotta';
    const THEME_GRAPHITE = 'graphite';
    const THEME_SLATE = 'slate';
    const THEME_DUSK = 'dusk';

    /** The enumerated values for the is_root field */
    const IS_ROOT_YES = 'Yes';
    const IS_ROOT_NO = 'No';

    /** The enumerated values for the is_system field */
    const IS_SYSTEM_YES = 'Yes';
    const IS_SYSTEM_NO = 'No';

    /** The default string format for model objects of the related table **/
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * An identity map to hold any loaded instances of Authy objects.
     * This must be public so that other peer classes can access this when hydrating from JOIN
     * queries.
     * @var        array Authy[]
     */
    public static $instances = array();


    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. AuthyPeer::$fieldNames[AuthyPeer::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        BasePeer::TYPE_PHPNAME => array ('IdAuthy', 'ValidationKey', 'Username', 'Fullname', 'Email', 'PasswdHash', 'Expire', 'Deactivate', 'Language', 'Theme', 'GoogleSub', 'GoogleEmail', 'ResetTokenHash', 'ResetTokenExpires', 'IdTenant', 'LocationAddress', 'LocationLat', 'LocationLng', 'IsRoot', 'IdAuthyGroup', 'IsSystem', 'RightsAll', 'RightsGroup', 'RightsOwner', 'Onglet', 'DateCreation', 'DateModification', 'IdGroupCreation', 'IdCreation', 'IdModification', ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('idAuthy', 'validationKey', 'username', 'fullname', 'email', 'passwdHash', 'expire', 'deactivate', 'language', 'theme', 'googleSub', 'googleEmail', 'resetTokenHash', 'resetTokenExpires', 'idTenant', 'locationAddress', 'locationLat', 'locationLng', 'isRoot', 'idAuthyGroup', 'isSystem', 'rightsAll', 'rightsGroup', 'rightsOwner', 'onglet', 'dateCreation', 'dateModification', 'idGroupCreation', 'idCreation', 'idModification', ),
        BasePeer::TYPE_COLNAME => array (AuthyPeer::ID_AUTHY, AuthyPeer::VALIDATION_KEY, AuthyPeer::USERNAME, AuthyPeer::FULLNAME, AuthyPeer::EMAIL, AuthyPeer::PASSWD_HASH, AuthyPeer::EXPIRE, AuthyPeer::DEACTIVATE, AuthyPeer::LANGUAGE, AuthyPeer::THEME, AuthyPeer::GOOGLE_SUB, AuthyPeer::GOOGLE_EMAIL, AuthyPeer::RESET_TOKEN_HASH, AuthyPeer::RESET_TOKEN_EXPIRES, AuthyPeer::ID_TENANT, AuthyPeer::LOCATION_ADDRESS, AuthyPeer::LOCATION_LAT, AuthyPeer::LOCATION_LNG, AuthyPeer::IS_ROOT, AuthyPeer::ID_AUTHY_GROUP, AuthyPeer::IS_SYSTEM, AuthyPeer::RIGHTS_ALL, AuthyPeer::RIGHTS_GROUP, AuthyPeer::RIGHTS_OWNER, AuthyPeer::ONGLET, AuthyPeer::DATE_CREATION, AuthyPeer::DATE_MODIFICATION, AuthyPeer::ID_GROUP_CREATION, AuthyPeer::ID_CREATION, AuthyPeer::ID_MODIFICATION, ),
        BasePeer::TYPE_RAW_COLNAME => array ('ID_AUTHY', 'VALIDATION_KEY', 'USERNAME', 'FULLNAME', 'EMAIL', 'PASSWD_HASH', 'EXPIRE', 'DEACTIVATE', 'LANGUAGE', 'THEME', 'GOOGLE_SUB', 'GOOGLE_EMAIL', 'RESET_TOKEN_HASH', 'RESET_TOKEN_EXPIRES', 'ID_TENANT', 'LOCATION_ADDRESS', 'LOCATION_LAT', 'LOCATION_LNG', 'IS_ROOT', 'ID_AUTHY_GROUP', 'IS_SYSTEM', 'RIGHTS_ALL', 'RIGHTS_GROUP', 'RIGHTS_OWNER', 'ONGLET', 'DATE_CREATION', 'DATE_MODIFICATION', 'ID_GROUP_CREATION', 'ID_CREATION', 'ID_MODIFICATION', ),
        BasePeer::TYPE_FIELDNAME => array ('id_authy', 'validation_key', 'username', 'fullname', 'email', 'passwd_hash', 'expire', 'deactivate', 'language', 'theme', 'google_sub', 'google_email', 'reset_token_hash', 'reset_token_expires', 'id_tenant', 'location_address', 'location_lat', 'location_lng', 'is_root', 'id_authy_group', 'is_system', 'rights_all', 'rights_group', 'rights_owner', 'onglet', 'date_creation', 'date_modification', 'id_group_creation', 'id_creation', 'id_modification', ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. AuthyPeer::$fieldNames[BasePeer::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        BasePeer::TYPE_PHPNAME => array ('IdAuthy' => 0, 'ValidationKey' => 1, 'Username' => 2, 'Fullname' => 3, 'Email' => 4, 'PasswdHash' => 5, 'Expire' => 6, 'Deactivate' => 7, 'Language' => 8, 'Theme' => 9, 'GoogleSub' => 10, 'GoogleEmail' => 11, 'ResetTokenHash' => 12, 'ResetTokenExpires' => 13, 'IdTenant' => 14, 'LocationAddress' => 15, 'LocationLat' => 16, 'LocationLng' => 17, 'IsRoot' => 18, 'IdAuthyGroup' => 19, 'IsSystem' => 20, 'RightsAll' => 21, 'RightsGroup' => 22, 'RightsOwner' => 23, 'Onglet' => 24, 'DateCreation' => 25, 'DateModification' => 26, 'IdGroupCreation' => 27, 'IdCreation' => 28, 'IdModification' => 29, ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('idAuthy' => 0, 'validationKey' => 1, 'username' => 2, 'fullname' => 3, 'email' => 4, 'passwdHash' => 5, 'expire' => 6, 'deactivate' => 7, 'language' => 8, 'theme' => 9, 'googleSub' => 10, 'googleEmail' => 11, 'resetTokenHash' => 12, 'resetTokenExpires' => 13, 'idTenant' => 14, 'locationAddress' => 15, 'locationLat' => 16, 'locationLng' => 17, 'isRoot' => 18, 'idAuthyGroup' => 19, 'isSystem' => 20, 'rightsAll' => 21, 'rightsGroup' => 22, 'rightsOwner' => 23, 'onglet' => 24, 'dateCreation' => 25, 'dateModification' => 26, 'idGroupCreation' => 27, 'idCreation' => 28, 'idModification' => 29, ),
        BasePeer::TYPE_COLNAME => array (AuthyPeer::ID_AUTHY => 0, AuthyPeer::VALIDATION_KEY => 1, AuthyPeer::USERNAME => 2, AuthyPeer::FULLNAME => 3, AuthyPeer::EMAIL => 4, AuthyPeer::PASSWD_HASH => 5, AuthyPeer::EXPIRE => 6, AuthyPeer::DEACTIVATE => 7, AuthyPeer::LANGUAGE => 8, AuthyPeer::THEME => 9, AuthyPeer::GOOGLE_SUB => 10, AuthyPeer::GOOGLE_EMAIL => 11, AuthyPeer::RESET_TOKEN_HASH => 12, AuthyPeer::RESET_TOKEN_EXPIRES => 13, AuthyPeer::ID_TENANT => 14, AuthyPeer::LOCATION_ADDRESS => 15, AuthyPeer::LOCATION_LAT => 16, AuthyPeer::LOCATION_LNG => 17, AuthyPeer::IS_ROOT => 18, AuthyPeer::ID_AUTHY_GROUP => 19, AuthyPeer::IS_SYSTEM => 20, AuthyPeer::RIGHTS_ALL => 21, AuthyPeer::RIGHTS_GROUP => 22, AuthyPeer::RIGHTS_OWNER => 23, AuthyPeer::ONGLET => 24, AuthyPeer::DATE_CREATION => 25, AuthyPeer::DATE_MODIFICATION => 26, AuthyPeer::ID_GROUP_CREATION => 27, AuthyPeer::ID_CREATION => 28, AuthyPeer::ID_MODIFICATION => 29, ),
        BasePeer::TYPE_RAW_COLNAME => array ('ID_AUTHY' => 0, 'VALIDATION_KEY' => 1, 'USERNAME' => 2, 'FULLNAME' => 3, 'EMAIL' => 4, 'PASSWD_HASH' => 5, 'EXPIRE' => 6, 'DEACTIVATE' => 7, 'LANGUAGE' => 8, 'THEME' => 9, 'GOOGLE_SUB' => 10, 'GOOGLE_EMAIL' => 11, 'RESET_TOKEN_HASH' => 12, 'RESET_TOKEN_EXPIRES' => 13, 'ID_TENANT' => 14, 'LOCATION_ADDRESS' => 15, 'LOCATION_LAT' => 16, 'LOCATION_LNG' => 17, 'IS_ROOT' => 18, 'ID_AUTHY_GROUP' => 19, 'IS_SYSTEM' => 20, 'RIGHTS_ALL' => 21, 'RIGHTS_GROUP' => 22, 'RIGHTS_OWNER' => 23, 'ONGLET' => 24, 'DATE_CREATION' => 25, 'DATE_MODIFICATION' => 26, 'ID_GROUP_CREATION' => 27, 'ID_CREATION' => 28, 'ID_MODIFICATION' => 29, ),
        BasePeer::TYPE_FIELDNAME => array ('id_authy' => 0, 'validation_key' => 1, 'username' => 2, 'fullname' => 3, 'email' => 4, 'passwd_hash' => 5, 'expire' => 6, 'deactivate' => 7, 'language' => 8, 'theme' => 9, 'google_sub' => 10, 'google_email' => 11, 'reset_token_hash' => 12, 'reset_token_expires' => 13, 'id_tenant' => 14, 'location_address' => 15, 'location_lat' => 16, 'location_lng' => 17, 'is_root' => 18, 'id_authy_group' => 19, 'is_system' => 20, 'rights_all' => 21, 'rights_group' => 22, 'rights_owner' => 23, 'onglet' => 24, 'date_creation' => 25, 'date_modification' => 26, 'id_group_creation' => 27, 'id_creation' => 28, 'id_modification' => 29, ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, )
    );

    /** The enumerated values for this table */
    protected static $enumValueSets = array(
        AuthyPeer::DEACTIVATE => array(
            AuthyPeer::DEACTIVATE_YES,
            AuthyPeer::DEACTIVATE_NO,
        ),
        AuthyPeer::LANGUAGE => array(
            AuthyPeer::LANGUAGE_EN_US,
            AuthyPeer::LANGUAGE_FR_CA,
        ),
        AuthyPeer::THEME => array(
            AuthyPeer::THEME_MINT,
            AuthyPeer::THEME_INK,
            AuthyPeer::THEME_INDIGO,
            AuthyPeer::THEME_TERRACOTTA,
            AuthyPeer::THEME_GRAPHITE,
            AuthyPeer::THEME_SLATE,
            AuthyPeer::THEME_DUSK,
        ),
        AuthyPeer::IS_ROOT => array(
            AuthyPeer::IS_ROOT_YES,
            AuthyPeer::IS_ROOT_NO,
        ),
        AuthyPeer::IS_SYSTEM => array(
            AuthyPeer::IS_SYSTEM_YES,
            AuthyPeer::IS_SYSTEM_NO,
        ),
    );

    /**
     * Translates a fieldname to another type
     *
     * @param      string $name field name
     * @param      string $fromType One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *                         BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
     * @param      string $toType   One of the class type constants
     * @return string          translated name of the field.
     * @throws PropelException - if the specified name could not be found in the fieldname mappings.
     */
    public static function translateFieldName($name, $fromType, $toType)
    {
        $toNames = AuthyPeer::getFieldNames($toType);
        $key = isset(AuthyPeer::$fieldKeys[$fromType][$name]) ? AuthyPeer::$fieldKeys[$fromType][$name] : null;
        if ($key === null) {
            throw new PropelException("'$name' could not be found in the field names of type '$fromType'. These are: " . print_r(AuthyPeer::$fieldKeys[$fromType], true));
        }

        return $toNames[$key];
    }

    /**
     * Returns an array of field names.
     *
     * @param      string $type The type of fieldnames to return:
     *                      One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *                      BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
     * @return array           A list of field names
     * @throws PropelException - if the type is not valid.
     */
    public static function getFieldNames($type = BasePeer::TYPE_PHPNAME)
    {
        if (!array_key_exists($type, AuthyPeer::$fieldNames)) {
            throw new PropelException('Method getFieldNames() expects the parameter $type to be one of the class constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME, BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM. ' . $type . ' was given.');
        }

        return AuthyPeer::$fieldNames[$type];
    }

    /**
     * Gets the list of values for all ENUM columns
     * @return array
     */
    public static function getValueSets()
    {
      return AuthyPeer::$enumValueSets;
    }

    /**
     * Gets the list of values for an ENUM column
     *
     * @param string $colname The ENUM column name.
     *
     * @return array list of possible values for the column
     */
    public static function getValueSet($colname)
    {
        $valueSets = AuthyPeer::getValueSets();

        if (!isset($valueSets[$colname])) {
            throw new PropelException(sprintf('Column "%s" has no ValueSet.', $colname));
        }

        return $valueSets[$colname];
    }

    /**
     * Gets the SQL value for the ENUM column value
     *
     * @param string $colname ENUM column name.
     * @param string $enumVal ENUM value.
     *
     * @return int SQL value
     */
    public static function getSqlValueForEnum($colname, $enumVal)
    {
        $values = AuthyPeer::getValueSet($colname);
        if (!in_array($enumVal, $values)) {
            throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $colname));
        }

        return array_search($enumVal, $values);
    }

    /**
     * Convenience method which changes table.column to alias.column.
     *
     * Using this method you can maintain SQL abstraction while using column aliases.
     * <code>
     *		$c->addAlias("alias1", TablePeer::TABLE_NAME);
     *		$c->addJoin(TablePeer::alias("alias1", TablePeer::PRIMARY_KEY_COLUMN), TablePeer::PRIMARY_KEY_COLUMN);
     * </code>
     * @param      string $alias The alias for the current table.
     * @param      string $column The column name for current table. (i.e. AuthyPeer::COLUMN_NAME).
     * @return string
     */
    public static function alias($alias, $column)
    {
        return str_replace(AuthyPeer::TABLE_NAME.'.', $alias.'.', $column);
    }

    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param      Criteria $criteria object containing the columns to add.
     * @param      string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(AuthyPeer::ID_AUTHY);
            $criteria->addSelectColumn(AuthyPeer::VALIDATION_KEY);
            $criteria->addSelectColumn(AuthyPeer::USERNAME);
            $criteria->addSelectColumn(AuthyPeer::FULLNAME);
            $criteria->addSelectColumn(AuthyPeer::EMAIL);
            $criteria->addSelectColumn(AuthyPeer::PASSWD_HASH);
            $criteria->addSelectColumn(AuthyPeer::EXPIRE);
            $criteria->addSelectColumn(AuthyPeer::DEACTIVATE);
            $criteria->addSelectColumn(AuthyPeer::LANGUAGE);
            $criteria->addSelectColumn(AuthyPeer::THEME);
            $criteria->addSelectColumn(AuthyPeer::GOOGLE_SUB);
            $criteria->addSelectColumn(AuthyPeer::GOOGLE_EMAIL);
            $criteria->addSelectColumn(AuthyPeer::RESET_TOKEN_HASH);
            $criteria->addSelectColumn(AuthyPeer::RESET_TOKEN_EXPIRES);
            $criteria->addSelectColumn(AuthyPeer::ID_TENANT);
            $criteria->addSelectColumn(AuthyPeer::LOCATION_ADDRESS);
            $criteria->addSelectColumn(AuthyPeer::LOCATION_LAT);
            $criteria->addSelectColumn(AuthyPeer::LOCATION_LNG);
            $criteria->addSelectColumn(AuthyPeer::IS_ROOT);
            $criteria->addSelectColumn(AuthyPeer::ID_AUTHY_GROUP);
            $criteria->addSelectColumn(AuthyPeer::IS_SYSTEM);
            $criteria->addSelectColumn(AuthyPeer::RIGHTS_ALL);
            $criteria->addSelectColumn(AuthyPeer::RIGHTS_GROUP);
            $criteria->addSelectColumn(AuthyPeer::RIGHTS_OWNER);
            $criteria->addSelectColumn(AuthyPeer::ONGLET);
            $criteria->addSelectColumn(AuthyPeer::DATE_CREATION);
            $criteria->addSelectColumn(AuthyPeer::DATE_MODIFICATION);
            $criteria->addSelectColumn(AuthyPeer::ID_GROUP_CREATION);
            $criteria->addSelectColumn(AuthyPeer::ID_CREATION);
            $criteria->addSelectColumn(AuthyPeer::ID_MODIFICATION);
        } else {
            $criteria->addSelectColumn($alias . '.id_authy');
            $criteria->addSelectColumn($alias . '.validation_key');
            $criteria->addSelectColumn($alias . '.username');
            $criteria->addSelectColumn($alias . '.fullname');
            $criteria->addSelectColumn($alias . '.email');
            $criteria->addSelectColumn($alias . '.passwd_hash');
            $criteria->addSelectColumn($alias . '.expire');
            $criteria->addSelectColumn($alias . '.deactivate');
            $criteria->addSelectColumn($alias . '.language');
            $criteria->addSelectColumn($alias . '.theme');
            $criteria->addSelectColumn($alias . '.google_sub');
            $criteria->addSelectColumn($alias . '.google_email');
            $criteria->addSelectColumn($alias . '.reset_token_hash');
            $criteria->addSelectColumn($alias . '.reset_token_expires');
            $criteria->addSelectColumn($alias . '.id_tenant');
            $criteria->addSelectColumn($alias . '.location_address');
            $criteria->addSelectColumn($alias . '.location_lat');
            $criteria->addSelectColumn($alias . '.location_lng');
            $criteria->addSelectColumn($alias . '.is_root');
            $criteria->addSelectColumn($alias . '.id_authy_group');
            $criteria->addSelectColumn($alias . '.is_system');
            $criteria->addSelectColumn($alias . '.rights_all');
            $criteria->addSelectColumn($alias . '.rights_group');
            $criteria->addSelectColumn($alias . '.rights_owner');
            $criteria->addSelectColumn($alias . '.onglet');
            $criteria->addSelectColumn($alias . '.date_creation');
            $criteria->addSelectColumn($alias . '.date_modification');
            $criteria->addSelectColumn($alias . '.id_group_creation');
            $criteria->addSelectColumn($alias . '.id_creation');
            $criteria->addSelectColumn($alias . '.id_modification');
        }
    }

    /**
     * Returns the number of rows matching criteria.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @return int Number of matching rows.
     */
    public static function doCount(Criteria $criteria, $distinct = false, ?PropelPDO $con = null)
    {
        // we may modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(AuthyPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            AuthyPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count
        $criteria->setDbName(AuthyPeer::DATABASE_NAME); // Set the correct dbName

        if ($con === null) {
            $con = Propel::getConnection(AuthyPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }
        // BasePeer returns a PDOStatement
        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }
    /**
     * Selects one object from the DB.
     *
     * @param      Criteria $criteria object used to create the SELECT statement.
     * @param      PropelPDO $con
     * @return Authy
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectOne(Criteria $criteria, ?PropelPDO $con = null)
    {
        $critcopy = clone $criteria;
        $critcopy->setLimit(1);
        $objects = AuthyPeer::doSelect($critcopy, $con);
        if ($objects) {
            return $objects[0];
        }

        return null;
    }
    /**
     * Selects several row from the DB.
     *
     * @param      Criteria $criteria The Criteria object used to build the SELECT statement.
     * @param      PropelPDO $con
     * @return array           Array of selected Objects
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelect(Criteria $criteria, ?PropelPDO $con = null)
    {
        return AuthyPeer::populateObjects(AuthyPeer::doSelectStmt($criteria, $con));
    }
    /**
     * Prepares the Criteria object and uses the parent doSelect() method to execute a PDOStatement.
     *
     * Use this method directly if you want to work with an executed statement directly (for example
     * to perform your own object hydration).
     *
     * @param      Criteria $criteria The Criteria object used to build the SELECT statement.
     * @param      PropelPDO $con The connection to use
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     * @return PDOStatement The executed PDOStatement object.
     * @see        BasePeer::doSelect()
     */
    public static function doSelectStmt(Criteria $criteria, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(AuthyPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        if (!$criteria->hasSelectClause()) {
            $criteria = clone $criteria;
            AuthyPeer::addSelectColumns($criteria);
        }

        // Set the correct dbName
        $criteria->setDbName(AuthyPeer::DATABASE_NAME);

        // BasePeer returns a PDOStatement
        return BasePeer::doSelect($criteria, $con);
    }
    /**
     * Adds an object to the instance pool.
     *
     * Propel keeps cached copies of objects in an instance pool when they are retrieved
     * from the database.  In some cases -- especially when you override doSelect*()
     * methods in your stub classes -- you may need to explicitly add objects
     * to the cache in order to ensure that the same objects are always returned by doSelect*()
     * and retrieveByPK*() calls.
     *
     * @param Authy $obj A Authy object.
     * @param      string $key (optional) key to use for instance map (for performance boost if key was already calculated externally).
     */
    public static function addInstanceToPool($obj, $key = null)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if ($key === null) {
                $key = (string) $obj->getIdAuthy();
            } // if key === null
            AuthyPeer::$instances[$key] = $obj;
        }
    }

    /**
     * Removes an object from the instance pool.
     *
     * Propel keeps cached copies of objects in an instance pool when they are retrieved
     * from the database.  In some cases -- especially when you override doDelete
     * methods in your stub classes -- you may need to explicitly remove objects
     * from the cache in order to prevent returning objects that no longer exist.
     *
     * @param      mixed $value A Authy object or a primary key value.
     *
     * @return void
     * @throws PropelException - if the value is invalid.
     */
    public static function removeInstanceFromPool($value)
    {
        if (Propel::isInstancePoolingEnabled() && $value !== null) {
            if (is_object($value) && $value instanceof Authy) {
                $key = (string) $value->getIdAuthy();
            } elseif (is_scalar($value)) {
                // assume we've been passed a primary key
                $key = (string) $value;
            } else {
                $e = new PropelException("Invalid value passed to removeInstanceFromPool().  Expected primary key or Authy object; got " . (is_object($value) ? get_class($value) . ' object.' : var_export($value,true)));
                throw $e;
            }

            unset(AuthyPeer::$instances[$key]);
        }
    } // removeInstanceFromPool()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param      string $key The key (@see getPrimaryKeyHash()) for this instance.
     * @return Authy Found object or null if 1) no instance exists for specified key or 2) instance pooling has been disabled.
     * @see        getPrimaryKeyHash()
     */
    public static function getInstanceFromPool($key)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if (isset(AuthyPeer::$instances[$key])) {
                return AuthyPeer::$instances[$key];
            }
        }

        return null; // just to be explicit
    }

    /**
     * Clear the instance pool.
     *
     * @return void
     */
    public static function clearInstancePool($and_clear_all_references = false)
    {
      if ($and_clear_all_references) {
        foreach (AuthyPeer::$instances as $instance) {
          $instance->clearAllReferences(true);
        }
      }
        AuthyPeer::$instances = array();
    }

    /**
     * Method to invalidate the instance pool of all tables related to authy
     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool()
    {
        // Invalidate objects in PushDevicePeer instance pool,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        PushDevicePeer::clearInstancePool();
        // Invalidate objects in ApiLogPeer instance pool,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        ApiLogPeer::clearInstancePool();
        // Invalidate objects in AuthyRefreshTokenPeer instance pool,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        AuthyRefreshTokenPeer::clearInstancePool();
        // Invalidate objects in OauthAuthCodePeer instance pool,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        OauthAuthCodePeer::clearInstancePool();
        // Invalidate objects in OauthAccessTokenPeer instance pool,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        OauthAccessTokenPeer::clearInstancePool();
        // Invalidate objects in OauthRefreshTokenPeer instance pool,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        OauthRefreshTokenPeer::clearInstancePool();
    }

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param      array $row PropelPDO resultset row.
     * @param      int $startcol The 0-based offset for reading from the resultset row.
     * @return string A string version of PK or null if the components of primary key in result array are all null.
     */
    public static function getPrimaryKeyHashFromRow($row, $startcol = 0)
    {
        // If the PK cannot be derived from the row, return null.
        if ($row[$startcol] === null) {
            return null;
        }

        return (string) $row[$startcol];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param      array $row PropelPDO resultset row.
     * @param      int $startcol The 0-based offset for reading from the resultset row.
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $startcol = 0)
    {

        return (int) $row[$startcol];
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function populateObjects(PDOStatement $stmt)
    {
        $results = array();

        // set the class once to avoid overhead in the loop
        $cls = AuthyPeer::getOMClass();
        // populate the object(s)
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key = AuthyPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj = AuthyPeer::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                AuthyPeer::addInstanceToPool($obj, $key);
            } // if key exists
        }
        $stmt->closeCursor();

        return $results;
    }
    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param      array $row PropelPDO resultset row.
     * @param      int $startcol The 0-based offset for reading from the resultset row.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     * @return array (Authy object, last column rank)
     */
    public static function populateObject($row, $startcol = 0)
    {
        $key = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol);
        if (null !== ($obj = AuthyPeer::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $startcol, true); // rehydrate
            $col = $startcol + AuthyPeer::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = AuthyPeer::OM_CLASS;
            $obj = new $cls();
            $col = $obj->hydrate($row, $startcol);
            AuthyPeer::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * Gets the SQL value for Deactivate ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getDeactivateSqlValue($enumVal)
    {
        return AuthyPeer::getSqlValueForEnum(AuthyPeer::DEACTIVATE, $enumVal);
    }

    /**
     * Gets the SQL value for Language ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getLanguageSqlValue($enumVal)
    {
        return AuthyPeer::getSqlValueForEnum(AuthyPeer::LANGUAGE, $enumVal);
    }

    /**
     * Gets the SQL value for Theme ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getThemeSqlValue($enumVal)
    {
        return AuthyPeer::getSqlValueForEnum(AuthyPeer::THEME, $enumVal);
    }

    /**
     * Gets the SQL value for IsRoot ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getIsRootSqlValue($enumVal)
    {
        return AuthyPeer::getSqlValueForEnum(AuthyPeer::IS_ROOT, $enumVal);
    }

    /**
     * Gets the SQL value for IsSystem ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getIsSystemSqlValue($enumVal)
    {
        return AuthyPeer::getSqlValueForEnum(AuthyPeer::IS_SYSTEM, $enumVal);
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyGroupRelatedByIdAuthyGroup table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAuthyGroupRelatedByIdAuthyGroup(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(AuthyPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            AuthyPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(AuthyPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(AuthyPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(AuthyPeer::ID_AUTHY_GROUP, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyGroupRelatedByIdGroupCreation table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAuthyGroupRelatedByIdGroupCreation(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(AuthyPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            AuthyPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(AuthyPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(AuthyPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(AuthyPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Selects a collection of Authy objects pre-filled with their AuthyGroup objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of Authy objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyGroupRelatedByIdAuthyGroup(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(AuthyPeer::DATABASE_NAME);
        }

        AuthyPeer::addSelectColumns($criteria);
        $startcol = AuthyPeer::NUM_HYDRATE_COLUMNS;
        AuthyGroupPeer::addSelectColumns($criteria);

        $criteria->addJoin(AuthyPeer::ID_AUTHY_GROUP, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = AuthyPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = AuthyPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = AuthyPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                AuthyPeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = AuthyGroupPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = AuthyGroupPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    AuthyGroupPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (Authy) to $obj2 (AuthyGroup)
                $obj2->addAuthyRelatedByIdAuthyGroup($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of Authy objects pre-filled with their AuthyGroup objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of Authy objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyGroupRelatedByIdGroupCreation(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(AuthyPeer::DATABASE_NAME);
        }

        AuthyPeer::addSelectColumns($criteria);
        $startcol = AuthyPeer::NUM_HYDRATE_COLUMNS;
        AuthyGroupPeer::addSelectColumns($criteria);

        $criteria->addJoin(AuthyPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = AuthyPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = AuthyPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = AuthyPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                AuthyPeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = AuthyGroupPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = AuthyGroupPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    AuthyGroupPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (Authy) to $obj2 (AuthyGroup)
                $obj2->addAuthyRelatedByIdGroupCreation($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Returns the number of rows matching criteria, joining all related tables
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAll(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(AuthyPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            AuthyPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(AuthyPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(AuthyPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(AuthyPeer::ID_AUTHY_GROUP, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(AuthyPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }

    /**
     * Selects a collection of Authy objects pre-filled with all related objects.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of Authy objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAll(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(AuthyPeer::DATABASE_NAME);
        }

        AuthyPeer::addSelectColumns($criteria);
        $startcol2 = AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(AuthyPeer::ID_AUTHY_GROUP, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(AuthyPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = AuthyPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = AuthyPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = AuthyPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                AuthyPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

            // Add objects for joined AuthyGroup rows

            $key2 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol2);
            if ($key2 !== null) {
                $obj2 = AuthyGroupPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = AuthyGroupPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    AuthyGroupPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 loaded

                // Add the $obj1 (Authy) to the collection in $obj2 (AuthyGroup)
                $obj2->addAuthyRelatedByIdAuthyGroup($obj1);
            } // if joined row not null

            // Add objects for joined AuthyGroup rows

            $key3 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol3);
            if ($key3 !== null) {
                $obj3 = AuthyGroupPeer::getInstanceFromPool($key3);
                if (!$obj3) {

                    $cls = AuthyGroupPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    AuthyGroupPeer::addInstanceToPool($obj3, $key3);
                } // if obj3 loaded

                // Add the $obj1 (Authy) to the collection in $obj3 (AuthyGroup)
                $obj3->addAuthyRelatedByIdGroupCreation($obj1);
            } // if joined row not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyGroupRelatedByIdAuthyGroup table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptAuthyGroupRelatedByIdAuthyGroup(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(AuthyPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            AuthyPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(AuthyPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(AuthyPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyGroupRelatedByIdGroupCreation table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptAuthyGroupRelatedByIdGroupCreation(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(AuthyPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            AuthyPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(AuthyPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(AuthyPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyRelatedByIdCreation table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptAuthyRelatedByIdCreation(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(AuthyPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            AuthyPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(AuthyPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(AuthyPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(AuthyPeer::ID_AUTHY_GROUP, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(AuthyPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyRelatedByIdModification table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptAuthyRelatedByIdModification(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(AuthyPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            AuthyPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(AuthyPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(AuthyPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(AuthyPeer::ID_AUTHY_GROUP, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(AuthyPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Selects a collection of Authy objects pre-filled with all related objects except AuthyGroupRelatedByIdAuthyGroup.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of Authy objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptAuthyGroupRelatedByIdAuthyGroup(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(AuthyPeer::DATABASE_NAME);
        }

        AuthyPeer::addSelectColumns($criteria);
        $startcol2 = AuthyPeer::NUM_HYDRATE_COLUMNS;


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = AuthyPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = AuthyPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = AuthyPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                AuthyPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of Authy objects pre-filled with all related objects except AuthyGroupRelatedByIdGroupCreation.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of Authy objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptAuthyGroupRelatedByIdGroupCreation(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(AuthyPeer::DATABASE_NAME);
        }

        AuthyPeer::addSelectColumns($criteria);
        $startcol2 = AuthyPeer::NUM_HYDRATE_COLUMNS;


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = AuthyPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = AuthyPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = AuthyPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                AuthyPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of Authy objects pre-filled with all related objects except AuthyRelatedByIdCreation.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of Authy objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptAuthyRelatedByIdCreation(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(AuthyPeer::DATABASE_NAME);
        }

        AuthyPeer::addSelectColumns($criteria);
        $startcol2 = AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(AuthyPeer::ID_AUTHY_GROUP, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(AuthyPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = AuthyPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = AuthyPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = AuthyPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                AuthyPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined AuthyGroup rows

                $key2 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = AuthyGroupPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = AuthyGroupPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    AuthyGroupPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (Authy) to the collection in $obj2 (AuthyGroup)
                $obj2->addAuthyRelatedByIdAuthyGroup($obj1);

            } // if joined row is not null

                // Add objects for joined AuthyGroup rows

                $key3 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = AuthyGroupPeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = AuthyGroupPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    AuthyGroupPeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (Authy) to the collection in $obj3 (AuthyGroup)
                $obj3->addAuthyRelatedByIdGroupCreation($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of Authy objects pre-filled with all related objects except AuthyRelatedByIdModification.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of Authy objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptAuthyRelatedByIdModification(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(AuthyPeer::DATABASE_NAME);
        }

        AuthyPeer::addSelectColumns($criteria);
        $startcol2 = AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(AuthyPeer::ID_AUTHY_GROUP, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(AuthyPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = AuthyPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = AuthyPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = AuthyPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                AuthyPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined AuthyGroup rows

                $key2 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = AuthyGroupPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = AuthyGroupPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    AuthyGroupPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (Authy) to the collection in $obj2 (AuthyGroup)
                $obj2->addAuthyRelatedByIdAuthyGroup($obj1);

            } // if joined row is not null

                // Add objects for joined AuthyGroup rows

                $key3 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = AuthyGroupPeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = AuthyGroupPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    AuthyGroupPeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (Authy) to the collection in $obj3 (AuthyGroup)
                $obj3->addAuthyRelatedByIdGroupCreation($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }

    /**
     * Returns the TableMap related to this peer.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getDatabaseMap(AuthyPeer::DATABASE_NAME)->getTable(AuthyPeer::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this peer class.
     */
    public static function buildTableMap()
    {
      $dbMap = Propel::getDatabaseMap(BaseAuthyPeer::DATABASE_NAME);
      if (!$dbMap->hasTable(BaseAuthyPeer::TABLE_NAME)) {
        $dbMap->addTableObject(new \App\map\AuthyTableMap());
      }
    }

    /**
     * The class that the Peer will make instances of.
     *
     *
     * @return string ClassName
     */
    public static function getOMClass($row = 0, $colnum = 0)
    {
        return AuthyPeer::OM_CLASS;
    }

    /**
     * Performs an INSERT on the database, given a Authy or Criteria object.
     *
     * @param      mixed $values Criteria or Authy object containing data that is used to create the INSERT statement.
     * @param      PropelPDO $con the PropelPDO connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doInsert($values, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(AuthyPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity
        } else {
            $criteria = $values->buildCriteria(); // build Criteria from Authy object
        }

        if ($criteria->containsKey(AuthyPeer::ID_AUTHY) && $criteria->keyContainsValue(AuthyPeer::ID_AUTHY) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.AuthyPeer::ID_AUTHY.')');
        }


        // Set the correct dbName
        $criteria->setDbName(AuthyPeer::DATABASE_NAME);

        try {
            // use transaction because $criteria could contain info
            // for more than one table (I guess, conceivably)
            $con->beginTransaction();
            $pk = BasePeer::doInsert($criteria, $con);
            $con->commit();
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }

        return $pk;
    }

    /**
     * Performs an UPDATE on the database, given a Authy or Criteria object.
     *
     * @param      mixed $values Criteria or Authy object containing data that is used to create the UPDATE statement.
     * @param      PropelPDO $con The connection to use (specify PropelPDO connection object to exert more control over transactions).
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doUpdate($values, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(AuthyPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $selectCriteria = new Criteria(AuthyPeer::DATABASE_NAME);

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity

            $comparison = $criteria->getComparison(AuthyPeer::ID_AUTHY);
            $value = $criteria->remove(AuthyPeer::ID_AUTHY);
            if ($value) {
                $selectCriteria->add(AuthyPeer::ID_AUTHY, $value, $comparison);
            } else {
                $selectCriteria->setPrimaryTableName(AuthyPeer::TABLE_NAME);
            }

        } else { // $values is Authy object
            $criteria = $values->buildCriteria(); // gets full criteria
            $selectCriteria = $values->buildPkeyCriteria(); // gets criteria w/ primary key(s)
        }

        // set the correct dbName
        $criteria->setDbName(AuthyPeer::DATABASE_NAME);

        return BasePeer::doUpdate($selectCriteria, $criteria, $con);
    }

    /**
     * Deletes all rows from the authy table.
     *
     * @param      PropelPDO $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException
     */
    public static function doDeleteAll(?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(AuthyPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += BasePeer::doDeleteAll(AuthyPeer::TABLE_NAME, $con, AuthyPeer::DATABASE_NAME);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            AuthyPeer::clearInstancePool();
            AuthyPeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs a DELETE on the database, given a Authy or Criteria object OR a primary key value.
     *
     * @param      mixed $values Criteria or Authy object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param      PropelPDO $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *				if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ?PropelPDO $con = null)
     {
        if ($con === null) {
            $con = Propel::getConnection(AuthyPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            // invalidate the cache for all objects of this type, since we have no
            // way of knowing (without running a query) what objects should be invalidated
            // from the cache based on this Criteria.
            AuthyPeer::clearInstancePool();
            // rename for clarity
            $criteria = clone $values;
        } elseif ($values instanceof Authy) { // it's a model object
            // invalidate the cache for this single object
            AuthyPeer::removeInstanceFromPool($values);
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(AuthyPeer::DATABASE_NAME);
            $criteria->add(AuthyPeer::ID_AUTHY, (array) $values, Criteria::IN);
            // invalidate the cache for this object(s)
            foreach ((array) $values as $singleval) {
                AuthyPeer::removeInstanceFromPool($singleval);
            }
        }

        // Set the correct dbName
        $criteria->setDbName(AuthyPeer::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();

            $affectedRows += BasePeer::doDelete($criteria, $con);
            AuthyPeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Validates all modified columns of given Authy object.
     * If parameter $columns is either a single column name or an array of column names
     * than only those columns are validated.
     *
     * NOTICE: This does not apply to primary or foreign keys for now.
     *
     * @param Authy $obj The object to validate.
     * @param      mixed $cols Column name or array of column names.
     *
     * @return mixed TRUE if all columns are valid or the error message of the first invalid column.
     */
    public static function doValidate($obj, $cols = null)
    {
        $columns = array();

        if ($cols) {
            $dbMap = Propel::getDatabaseMap(AuthyPeer::DATABASE_NAME);
            $tableMap = $dbMap->getTable(AuthyPeer::TABLE_NAME);

            if (! is_array($cols)) {
                $cols = array($cols);
            }

            foreach ($cols as $colName) {
                if ($tableMap->hasColumn($colName)) {
                    $get = 'get' . $tableMap->getColumn($colName)->getPhpName();
                    $columns[$colName] = $obj->$get();
                }
            }
        } else {

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::EMAIL))
            $columns[AuthyPeer::EMAIL] = $obj->getEmail();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::PASSWD_HASH))
            $columns[AuthyPeer::PASSWD_HASH] = $obj->getPasswdHash();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::VALIDATION_KEY))
            $columns[AuthyPeer::VALIDATION_KEY] = $obj->getValidationKey();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::USERNAME))
            $columns[AuthyPeer::USERNAME] = $obj->getUsername();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::FULLNAME))
            $columns[AuthyPeer::FULLNAME] = $obj->getFullname();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::EMAIL))
            $columns[AuthyPeer::EMAIL] = $obj->getEmail();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::PASSWD_HASH))
            $columns[AuthyPeer::PASSWD_HASH] = $obj->getPasswdHash();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::EXPIRE))
            $columns[AuthyPeer::EXPIRE] = $obj->getExpire();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::DEACTIVATE))
            $columns[AuthyPeer::DEACTIVATE] = $obj->getDeactivate();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::LANGUAGE))
            $columns[AuthyPeer::LANGUAGE] = $obj->getLanguage();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::THEME))
            $columns[AuthyPeer::THEME] = $obj->getTheme();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::GOOGLE_SUB))
            $columns[AuthyPeer::GOOGLE_SUB] = $obj->getGoogleSub();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::GOOGLE_EMAIL))
            $columns[AuthyPeer::GOOGLE_EMAIL] = $obj->getGoogleEmail();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::RESET_TOKEN_HASH))
            $columns[AuthyPeer::RESET_TOKEN_HASH] = $obj->getResetTokenHash();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::RESET_TOKEN_EXPIRES))
            $columns[AuthyPeer::RESET_TOKEN_EXPIRES] = $obj->getResetTokenExpires();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::ID_TENANT))
            $columns[AuthyPeer::ID_TENANT] = $obj->getIdTenant();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::LOCATION_ADDRESS))
            $columns[AuthyPeer::LOCATION_ADDRESS] = $obj->getLocationAddress();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::IS_ROOT))
            $columns[AuthyPeer::IS_ROOT] = $obj->getIsRoot();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::IS_ROOT))
            $columns[AuthyPeer::IS_ROOT] = $obj->getIsRoot();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::IS_SYSTEM))
            $columns[AuthyPeer::IS_SYSTEM] = $obj->getIsSystem();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::IS_SYSTEM))
            $columns[AuthyPeer::IS_SYSTEM] = $obj->getIsSystem();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::RIGHTS_ALL))
            $columns[AuthyPeer::RIGHTS_ALL] = $obj->getRightsAll();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::RIGHTS_GROUP))
            $columns[AuthyPeer::RIGHTS_GROUP] = $obj->getRightsGroup();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::RIGHTS_OWNER))
            $columns[AuthyPeer::RIGHTS_OWNER] = $obj->getRightsOwner();

        if ($obj->isNew() || $obj->isColumnModified(AuthyPeer::ONGLET))
            $columns[AuthyPeer::ONGLET] = $obj->getOnglet();

        }

        return BasePeer::doValidate(AuthyPeer::DATABASE_NAME, AuthyPeer::TABLE_NAME, $columns);
    }

    /**
     * Retrieve a single object by pkey.
     *
     * @param int $pk the primary key.
     * @param      PropelPDO $con the connection to use
     * @return Authy
     */
    public static function retrieveByPK($pk, ?PropelPDO $con = null)
    {

        if (null !== ($obj = AuthyPeer::getInstanceFromPool((string) $pk))) {
            return $obj;
        }

        if ($con === null) {
            $con = Propel::getConnection(AuthyPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria = new Criteria(AuthyPeer::DATABASE_NAME);
        $criteria->add(AuthyPeer::ID_AUTHY, $pk);

        $v = AuthyPeer::doSelect($criteria, $con);

        return !empty($v) > 0 ? $v[0] : null;
    }

    /**
     * Retrieve multiple objects by pkey.
     *
     * @param      array $pks List of primary keys
     * @param      PropelPDO $con the connection to use
     * @return Authy[]
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function retrieveByPKs($pks, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(AuthyPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $objs = null;
        if (empty($pks)) {
            $objs = array();
        } else {
            $criteria = new Criteria(AuthyPeer::DATABASE_NAME);
            $criteria->add(AuthyPeer::ID_AUTHY, $pks, Criteria::IN);
            $objs = AuthyPeer::doSelect($criteria, $con);
        }

        return $objs;
    }

} // BaseAuthyPeer

// This is the static code needed to register the TableMap for this table with the main Propel class.
//
BaseAuthyPeer::buildTableMap();

