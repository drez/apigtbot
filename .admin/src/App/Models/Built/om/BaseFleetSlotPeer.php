<?php

namespace App\om;

use \BasePeer;
use \Criteria;
use \PDO;
use \PDOStatement;
use \Propel;
use \PropelException;
use \PropelPDO;
use App\AuthyGroupPeer;
use App\AuthyPeer;
use App\FleetSlot;
use App\FleetSlotPeer;
use App\GridRunPeer;
use App\RegimeEpisodePeer;
use App\map\FleetSlotTableMap;

/**
 * Base static class for performing query and update operations on the 'fleet_slot' table.
 *
 * Fleet slot
 *
 * @package propel.generator..om
 */
abstract class BaseFleetSlotPeer
{

    /** the default database name for this class */
    const DATABASE_NAME = 'apigtbot';

    /** the table name for this class */
    const TABLE_NAME = 'fleet_slot';

    /** the related Propel class for this table */
    const OM_CLASS = 'App\\FleetSlot';

    /** the related TableMap class for this table */
    const TM_CLASS = 'App\\map\\FleetSlotTableMap';

    /** The total number of columns. */
    const NUM_COLUMNS = 20;

    /** The number of lazy-loaded columns. */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /** The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS) */
    const NUM_HYDRATE_COLUMNS = 20;

    /** the column name for the id_fleet_slot field */
    const ID_FLEET_SLOT = 'fleet_slot.id_fleet_slot';

    /** the column name for the symbol field */
    const SYMBOL = 'fleet_slot.symbol';

    /** the column name for the algo field */
    const ALGO = 'fleet_slot.algo';

    /** the column name for the target_slice field */
    const TARGET_SLICE = 'fleet_slot.target_slice';

    /** the column name for the enabled field */
    const ENABLED = 'fleet_slot.enabled';

    /** the column name for the state field */
    const STATE = 'fleet_slot.state';

    /** the column name for the confirm_up field */
    const CONFIRM_UP = 'fleet_slot.confirm_up';

    /** the column name for the confirm_down field */
    const CONFIRM_DOWN = 'fleet_slot.confirm_down';

    /** the column name for the last_verdict field */
    const LAST_VERDICT = 'fleet_slot.last_verdict';

    /** the column name for the verdict_at field */
    const VERDICT_AT = 'fleet_slot.verdict_at';

    /** the column name for the episode_started_at field */
    const EPISODE_STARTED_AT = 'fleet_slot.episode_started_at';

    /** the column name for the activation field */
    const ACTIVATION = 'fleet_slot.activation';

    /** the column name for the id_grid_run field */
    const ID_GRID_RUN = 'fleet_slot.id_grid_run';

    /** the column name for the last_empty_alert_at field */
    const LAST_EMPTY_ALERT_AT = 'fleet_slot.last_empty_alert_at';

    /** the column name for the last_parked_alert_at field */
    const LAST_PARKED_ALERT_AT = 'fleet_slot.last_parked_alert_at';

    /** the column name for the date_creation field */
    const DATE_CREATION = 'fleet_slot.date_creation';

    /** the column name for the date_modification field */
    const DATE_MODIFICATION = 'fleet_slot.date_modification';

    /** the column name for the id_group_creation field */
    const ID_GROUP_CREATION = 'fleet_slot.id_group_creation';

    /** the column name for the id_creation field */
    const ID_CREATION = 'fleet_slot.id_creation';

    /** the column name for the id_modification field */
    const ID_MODIFICATION = 'fleet_slot.id_modification';

    /** The enumerated values for the algo field */
    const ALGO_TREND = 'Trend';
    const ALGO_GRID = 'Grid';

    /** The enumerated values for the state field */
    const STATE_IDLE = 'idle';
    const STATE_ACTIVE = 'active';
    const STATE_WINDING_DOWN = 'winding_down';

    /** The default string format for model objects of the related table **/
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * An identity map to hold any loaded instances of FleetSlot objects.
     * This must be public so that other peer classes can access this when hydrating from JOIN
     * queries.
     * @var        array FleetSlot[]
     */
    public static $instances = array();


    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. FleetSlotPeer::$fieldNames[FleetSlotPeer::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        BasePeer::TYPE_PHPNAME => array ('IdFleetSlot', 'Symbol', 'Algo', 'TargetSlice', 'Enabled', 'State', 'ConfirmUp', 'ConfirmDown', 'LastVerdict', 'VerdictAt', 'EpisodeStartedAt', 'Activation', 'IdGridRun', 'LastEmptyAlertAt', 'LastParkedAlertAt', 'DateCreation', 'DateModification', 'IdGroupCreation', 'IdCreation', 'IdModification', ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('idFleetSlot', 'symbol', 'algo', 'targetSlice', 'enabled', 'state', 'confirmUp', 'confirmDown', 'lastVerdict', 'verdictAt', 'episodeStartedAt', 'activation', 'idGridRun', 'lastEmptyAlertAt', 'lastParkedAlertAt', 'dateCreation', 'dateModification', 'idGroupCreation', 'idCreation', 'idModification', ),
        BasePeer::TYPE_COLNAME => array (FleetSlotPeer::ID_FLEET_SLOT, FleetSlotPeer::SYMBOL, FleetSlotPeer::ALGO, FleetSlotPeer::TARGET_SLICE, FleetSlotPeer::ENABLED, FleetSlotPeer::STATE, FleetSlotPeer::CONFIRM_UP, FleetSlotPeer::CONFIRM_DOWN, FleetSlotPeer::LAST_VERDICT, FleetSlotPeer::VERDICT_AT, FleetSlotPeer::EPISODE_STARTED_AT, FleetSlotPeer::ACTIVATION, FleetSlotPeer::ID_GRID_RUN, FleetSlotPeer::LAST_EMPTY_ALERT_AT, FleetSlotPeer::LAST_PARKED_ALERT_AT, FleetSlotPeer::DATE_CREATION, FleetSlotPeer::DATE_MODIFICATION, FleetSlotPeer::ID_GROUP_CREATION, FleetSlotPeer::ID_CREATION, FleetSlotPeer::ID_MODIFICATION, ),
        BasePeer::TYPE_RAW_COLNAME => array ('ID_FLEET_SLOT', 'SYMBOL', 'ALGO', 'TARGET_SLICE', 'ENABLED', 'STATE', 'CONFIRM_UP', 'CONFIRM_DOWN', 'LAST_VERDICT', 'VERDICT_AT', 'EPISODE_STARTED_AT', 'ACTIVATION', 'ID_GRID_RUN', 'LAST_EMPTY_ALERT_AT', 'LAST_PARKED_ALERT_AT', 'DATE_CREATION', 'DATE_MODIFICATION', 'ID_GROUP_CREATION', 'ID_CREATION', 'ID_MODIFICATION', ),
        BasePeer::TYPE_FIELDNAME => array ('id_fleet_slot', 'symbol', 'algo', 'target_slice', 'enabled', 'state', 'confirm_up', 'confirm_down', 'last_verdict', 'verdict_at', 'episode_started_at', 'activation', 'id_grid_run', 'last_empty_alert_at', 'last_parked_alert_at', 'date_creation', 'date_modification', 'id_group_creation', 'id_creation', 'id_modification', ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. FleetSlotPeer::$fieldNames[BasePeer::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        BasePeer::TYPE_PHPNAME => array ('IdFleetSlot' => 0, 'Symbol' => 1, 'Algo' => 2, 'TargetSlice' => 3, 'Enabled' => 4, 'State' => 5, 'ConfirmUp' => 6, 'ConfirmDown' => 7, 'LastVerdict' => 8, 'VerdictAt' => 9, 'EpisodeStartedAt' => 10, 'Activation' => 11, 'IdGridRun' => 12, 'LastEmptyAlertAt' => 13, 'LastParkedAlertAt' => 14, 'DateCreation' => 15, 'DateModification' => 16, 'IdGroupCreation' => 17, 'IdCreation' => 18, 'IdModification' => 19, ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('idFleetSlot' => 0, 'symbol' => 1, 'algo' => 2, 'targetSlice' => 3, 'enabled' => 4, 'state' => 5, 'confirmUp' => 6, 'confirmDown' => 7, 'lastVerdict' => 8, 'verdictAt' => 9, 'episodeStartedAt' => 10, 'activation' => 11, 'idGridRun' => 12, 'lastEmptyAlertAt' => 13, 'lastParkedAlertAt' => 14, 'dateCreation' => 15, 'dateModification' => 16, 'idGroupCreation' => 17, 'idCreation' => 18, 'idModification' => 19, ),
        BasePeer::TYPE_COLNAME => array (FleetSlotPeer::ID_FLEET_SLOT => 0, FleetSlotPeer::SYMBOL => 1, FleetSlotPeer::ALGO => 2, FleetSlotPeer::TARGET_SLICE => 3, FleetSlotPeer::ENABLED => 4, FleetSlotPeer::STATE => 5, FleetSlotPeer::CONFIRM_UP => 6, FleetSlotPeer::CONFIRM_DOWN => 7, FleetSlotPeer::LAST_VERDICT => 8, FleetSlotPeer::VERDICT_AT => 9, FleetSlotPeer::EPISODE_STARTED_AT => 10, FleetSlotPeer::ACTIVATION => 11, FleetSlotPeer::ID_GRID_RUN => 12, FleetSlotPeer::LAST_EMPTY_ALERT_AT => 13, FleetSlotPeer::LAST_PARKED_ALERT_AT => 14, FleetSlotPeer::DATE_CREATION => 15, FleetSlotPeer::DATE_MODIFICATION => 16, FleetSlotPeer::ID_GROUP_CREATION => 17, FleetSlotPeer::ID_CREATION => 18, FleetSlotPeer::ID_MODIFICATION => 19, ),
        BasePeer::TYPE_RAW_COLNAME => array ('ID_FLEET_SLOT' => 0, 'SYMBOL' => 1, 'ALGO' => 2, 'TARGET_SLICE' => 3, 'ENABLED' => 4, 'STATE' => 5, 'CONFIRM_UP' => 6, 'CONFIRM_DOWN' => 7, 'LAST_VERDICT' => 8, 'VERDICT_AT' => 9, 'EPISODE_STARTED_AT' => 10, 'ACTIVATION' => 11, 'ID_GRID_RUN' => 12, 'LAST_EMPTY_ALERT_AT' => 13, 'LAST_PARKED_ALERT_AT' => 14, 'DATE_CREATION' => 15, 'DATE_MODIFICATION' => 16, 'ID_GROUP_CREATION' => 17, 'ID_CREATION' => 18, 'ID_MODIFICATION' => 19, ),
        BasePeer::TYPE_FIELDNAME => array ('id_fleet_slot' => 0, 'symbol' => 1, 'algo' => 2, 'target_slice' => 3, 'enabled' => 4, 'state' => 5, 'confirm_up' => 6, 'confirm_down' => 7, 'last_verdict' => 8, 'verdict_at' => 9, 'episode_started_at' => 10, 'activation' => 11, 'id_grid_run' => 12, 'last_empty_alert_at' => 13, 'last_parked_alert_at' => 14, 'date_creation' => 15, 'date_modification' => 16, 'id_group_creation' => 17, 'id_creation' => 18, 'id_modification' => 19, ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, )
    );

    /** The enumerated values for this table */
    protected static $enumValueSets = array(
        FleetSlotPeer::ALGO => array(
            FleetSlotPeer::ALGO_TREND,
            FleetSlotPeer::ALGO_GRID,
        ),
        FleetSlotPeer::STATE => array(
            FleetSlotPeer::STATE_IDLE,
            FleetSlotPeer::STATE_ACTIVE,
            FleetSlotPeer::STATE_WINDING_DOWN,
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
        $toNames = FleetSlotPeer::getFieldNames($toType);
        $key = isset(FleetSlotPeer::$fieldKeys[$fromType][$name]) ? FleetSlotPeer::$fieldKeys[$fromType][$name] : null;
        if ($key === null) {
            throw new PropelException("'$name' could not be found in the field names of type '$fromType'. These are: " . print_r(FleetSlotPeer::$fieldKeys[$fromType], true));
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
        if (!array_key_exists($type, FleetSlotPeer::$fieldNames)) {
            throw new PropelException('Method getFieldNames() expects the parameter $type to be one of the class constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME, BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM. ' . $type . ' was given.');
        }

        return FleetSlotPeer::$fieldNames[$type];
    }

    /**
     * Gets the list of values for all ENUM columns
     * @return array
     */
    public static function getValueSets()
    {
      return FleetSlotPeer::$enumValueSets;
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
        $valueSets = FleetSlotPeer::getValueSets();

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
        $values = FleetSlotPeer::getValueSet($colname);
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
     * @param      string $column The column name for current table. (i.e. FleetSlotPeer::COLUMN_NAME).
     * @return string
     */
    public static function alias($alias, $column)
    {
        return str_replace(FleetSlotPeer::TABLE_NAME.'.', $alias.'.', $column);
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
            $criteria->addSelectColumn(FleetSlotPeer::ID_FLEET_SLOT);
            $criteria->addSelectColumn(FleetSlotPeer::SYMBOL);
            $criteria->addSelectColumn(FleetSlotPeer::ALGO);
            $criteria->addSelectColumn(FleetSlotPeer::TARGET_SLICE);
            $criteria->addSelectColumn(FleetSlotPeer::ENABLED);
            $criteria->addSelectColumn(FleetSlotPeer::STATE);
            $criteria->addSelectColumn(FleetSlotPeer::CONFIRM_UP);
            $criteria->addSelectColumn(FleetSlotPeer::CONFIRM_DOWN);
            $criteria->addSelectColumn(FleetSlotPeer::LAST_VERDICT);
            $criteria->addSelectColumn(FleetSlotPeer::VERDICT_AT);
            $criteria->addSelectColumn(FleetSlotPeer::EPISODE_STARTED_AT);
            $criteria->addSelectColumn(FleetSlotPeer::ACTIVATION);
            $criteria->addSelectColumn(FleetSlotPeer::ID_GRID_RUN);
            $criteria->addSelectColumn(FleetSlotPeer::LAST_EMPTY_ALERT_AT);
            $criteria->addSelectColumn(FleetSlotPeer::LAST_PARKED_ALERT_AT);
            $criteria->addSelectColumn(FleetSlotPeer::DATE_CREATION);
            $criteria->addSelectColumn(FleetSlotPeer::DATE_MODIFICATION);
            $criteria->addSelectColumn(FleetSlotPeer::ID_GROUP_CREATION);
            $criteria->addSelectColumn(FleetSlotPeer::ID_CREATION);
            $criteria->addSelectColumn(FleetSlotPeer::ID_MODIFICATION);
        } else {
            $criteria->addSelectColumn($alias . '.id_fleet_slot');
            $criteria->addSelectColumn($alias . '.symbol');
            $criteria->addSelectColumn($alias . '.algo');
            $criteria->addSelectColumn($alias . '.target_slice');
            $criteria->addSelectColumn($alias . '.enabled');
            $criteria->addSelectColumn($alias . '.state');
            $criteria->addSelectColumn($alias . '.confirm_up');
            $criteria->addSelectColumn($alias . '.confirm_down');
            $criteria->addSelectColumn($alias . '.last_verdict');
            $criteria->addSelectColumn($alias . '.verdict_at');
            $criteria->addSelectColumn($alias . '.episode_started_at');
            $criteria->addSelectColumn($alias . '.activation');
            $criteria->addSelectColumn($alias . '.id_grid_run');
            $criteria->addSelectColumn($alias . '.last_empty_alert_at');
            $criteria->addSelectColumn($alias . '.last_parked_alert_at');
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
        $criteria->setPrimaryTableName(FleetSlotPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            FleetSlotPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count
        $criteria->setDbName(FleetSlotPeer::DATABASE_NAME); // Set the correct dbName

        if ($con === null) {
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return FleetSlot
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectOne(Criteria $criteria, ?PropelPDO $con = null)
    {
        $critcopy = clone $criteria;
        $critcopy->setLimit(1);
        $objects = FleetSlotPeer::doSelect($critcopy, $con);
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
        return FleetSlotPeer::populateObjects(FleetSlotPeer::doSelectStmt($criteria, $con));
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
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        if (!$criteria->hasSelectClause()) {
            $criteria = clone $criteria;
            FleetSlotPeer::addSelectColumns($criteria);
        }

        // Set the correct dbName
        $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);

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
     * @param FleetSlot $obj A FleetSlot object.
     * @param      string $key (optional) key to use for instance map (for performance boost if key was already calculated externally).
     */
    public static function addInstanceToPool($obj, $key = null)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if ($key === null) {
                $key = (string) $obj->getIdFleetSlot();
            } // if key === null
            FleetSlotPeer::$instances[$key] = $obj;
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
     * @param      mixed $value A FleetSlot object or a primary key value.
     *
     * @return void
     * @throws PropelException - if the value is invalid.
     */
    public static function removeInstanceFromPool($value)
    {
        if (Propel::isInstancePoolingEnabled() && $value !== null) {
            if (is_object($value) && $value instanceof FleetSlot) {
                $key = (string) $value->getIdFleetSlot();
            } elseif (is_scalar($value)) {
                // assume we've been passed a primary key
                $key = (string) $value;
            } else {
                $e = new PropelException("Invalid value passed to removeInstanceFromPool().  Expected primary key or FleetSlot object; got " . (is_object($value) ? get_class($value) . ' object.' : var_export($value,true)));
                throw $e;
            }

            unset(FleetSlotPeer::$instances[$key]);
        }
    } // removeInstanceFromPool()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param      string $key The key (@see getPrimaryKeyHash()) for this instance.
     * @return FleetSlot Found object or null if 1) no instance exists for specified key or 2) instance pooling has been disabled.
     * @see        getPrimaryKeyHash()
     */
    public static function getInstanceFromPool($key)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if (isset(FleetSlotPeer::$instances[$key])) {
                return FleetSlotPeer::$instances[$key];
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
        foreach (FleetSlotPeer::$instances as $instance) {
          $instance->clearAllReferences(true);
        }
      }
        FleetSlotPeer::$instances = array();
    }

    /**
     * Method to invalidate the instance pool of all tables related to fleet_slot
     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool()
    {
        // Invalidate objects in RegimeEpisodePeer instance pool,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        RegimeEpisodePeer::clearInstancePool();
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
        $cls = FleetSlotPeer::getOMClass();
        // populate the object(s)
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key = FleetSlotPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj = FleetSlotPeer::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                FleetSlotPeer::addInstanceToPool($obj, $key);
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
     * @return array (FleetSlot object, last column rank)
     */
    public static function populateObject($row, $startcol = 0)
    {
        $key = FleetSlotPeer::getPrimaryKeyHashFromRow($row, $startcol);
        if (null !== ($obj = FleetSlotPeer::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $startcol, true); // rehydrate
            $col = $startcol + FleetSlotPeer::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = FleetSlotPeer::OM_CLASS;
            $obj = new $cls();
            $col = $obj->hydrate($row, $startcol);
            FleetSlotPeer::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * Gets the SQL value for Algo ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getAlgoSqlValue($enumVal)
    {
        return FleetSlotPeer::getSqlValueForEnum(FleetSlotPeer::ALGO, $enumVal);
    }

    /**
     * Gets the SQL value for State ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getStateSqlValue($enumVal)
    {
        return FleetSlotPeer::getSqlValueForEnum(FleetSlotPeer::STATE, $enumVal);
    }


    /**
     * Returns the number of rows matching criteria, joining the related GridRun table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinGridRun(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(FleetSlotPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            FleetSlotPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(FleetSlotPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

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
     * Returns the number of rows matching criteria, joining the related AuthyGroup table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAuthyGroup(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(FleetSlotPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            FleetSlotPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(FleetSlotPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

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
    public static function doCountJoinAuthyRelatedByIdCreation(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(FleetSlotPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            FleetSlotPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(FleetSlotPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
    public static function doCountJoinAuthyRelatedByIdModification(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(FleetSlotPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            FleetSlotPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(FleetSlotPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
     * Selects a collection of FleetSlot objects pre-filled with their GridRun objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of FleetSlot objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinGridRun(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);
        }

        FleetSlotPeer::addSelectColumns($criteria);
        $startcol = FleetSlotPeer::NUM_HYDRATE_COLUMNS;
        GridRunPeer::addSelectColumns($criteria);

        $criteria->addJoin(FleetSlotPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = FleetSlotPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = FleetSlotPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = FleetSlotPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                FleetSlotPeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = GridRunPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = GridRunPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = GridRunPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    GridRunPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (FleetSlot) to $obj2 (GridRun)
                $obj2->addFleetSlot($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of FleetSlot objects pre-filled with their AuthyGroup objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of FleetSlot objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyGroup(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);
        }

        FleetSlotPeer::addSelectColumns($criteria);
        $startcol = FleetSlotPeer::NUM_HYDRATE_COLUMNS;
        AuthyGroupPeer::addSelectColumns($criteria);

        $criteria->addJoin(FleetSlotPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = FleetSlotPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = FleetSlotPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = FleetSlotPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                FleetSlotPeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (FleetSlot) to $obj2 (AuthyGroup)
                $obj2->addFleetSlot($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of FleetSlot objects pre-filled with their Authy objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of FleetSlot objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyRelatedByIdCreation(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);
        }

        FleetSlotPeer::addSelectColumns($criteria);
        $startcol = FleetSlotPeer::NUM_HYDRATE_COLUMNS;
        AuthyPeer::addSelectColumns($criteria);

        $criteria->addJoin(FleetSlotPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = FleetSlotPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = FleetSlotPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = FleetSlotPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                FleetSlotPeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = AuthyPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = AuthyPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    AuthyPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (FleetSlot) to $obj2 (Authy)
                $obj2->addFleetSlotRelatedByIdCreation($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of FleetSlot objects pre-filled with their Authy objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of FleetSlot objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyRelatedByIdModification(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);
        }

        FleetSlotPeer::addSelectColumns($criteria);
        $startcol = FleetSlotPeer::NUM_HYDRATE_COLUMNS;
        AuthyPeer::addSelectColumns($criteria);

        $criteria->addJoin(FleetSlotPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = FleetSlotPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = FleetSlotPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = FleetSlotPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                FleetSlotPeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = AuthyPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = AuthyPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    AuthyPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (FleetSlot) to $obj2 (Authy)
                $obj2->addFleetSlotRelatedByIdModification($obj1);

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
        $criteria->setPrimaryTableName(FleetSlotPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            FleetSlotPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(FleetSlotPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(FleetSlotPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(FleetSlotPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(FleetSlotPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
     * Selects a collection of FleetSlot objects pre-filled with all related objects.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of FleetSlot objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAll(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);
        }

        FleetSlotPeer::addSelectColumns($criteria);
        $startcol2 = FleetSlotPeer::NUM_HYDRATE_COLUMNS;

        GridRunPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol6 = $startcol5 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(FleetSlotPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(FleetSlotPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(FleetSlotPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(FleetSlotPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = FleetSlotPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = FleetSlotPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = FleetSlotPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                FleetSlotPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

            // Add objects for joined GridRun rows

            $key2 = GridRunPeer::getPrimaryKeyHashFromRow($row, $startcol2);
            if ($key2 !== null) {
                $obj2 = GridRunPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = GridRunPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    GridRunPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 loaded

                // Add the $obj1 (FleetSlot) to the collection in $obj2 (GridRun)
                $obj2->addFleetSlot($obj1);
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

                // Add the $obj1 (FleetSlot) to the collection in $obj3 (AuthyGroup)
                $obj3->addFleetSlot($obj1);
            } // if joined row not null

            // Add objects for joined Authy rows

            $key4 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol4);
            if ($key4 !== null) {
                $obj4 = AuthyPeer::getInstanceFromPool($key4);
                if (!$obj4) {

                    $cls = AuthyPeer::getOMClass();

                    $obj4 = new $cls();
                    $obj4->hydrate($row, $startcol4);
                    AuthyPeer::addInstanceToPool($obj4, $key4);
                } // if obj4 loaded

                // Add the $obj1 (FleetSlot) to the collection in $obj4 (Authy)
                $obj4->addFleetSlotRelatedByIdCreation($obj1);
            } // if joined row not null

            // Add objects for joined Authy rows

            $key5 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol5);
            if ($key5 !== null) {
                $obj5 = AuthyPeer::getInstanceFromPool($key5);
                if (!$obj5) {

                    $cls = AuthyPeer::getOMClass();

                    $obj5 = new $cls();
                    $obj5->hydrate($row, $startcol5);
                    AuthyPeer::addInstanceToPool($obj5, $key5);
                } // if obj5 loaded

                // Add the $obj1 (FleetSlot) to the collection in $obj5 (Authy)
                $obj5->addFleetSlotRelatedByIdModification($obj1);
            } // if joined row not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Returns the number of rows matching criteria, joining the related GridRun table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptGridRun(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(FleetSlotPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            FleetSlotPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(FleetSlotPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(FleetSlotPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(FleetSlotPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
     * Returns the number of rows matching criteria, joining the related AuthyGroup table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptAuthyGroup(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(FleetSlotPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            FleetSlotPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(FleetSlotPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(FleetSlotPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(FleetSlotPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
        $criteria->setPrimaryTableName(FleetSlotPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            FleetSlotPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(FleetSlotPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(FleetSlotPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

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
        $criteria->setPrimaryTableName(FleetSlotPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            FleetSlotPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(FleetSlotPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(FleetSlotPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

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
     * Selects a collection of FleetSlot objects pre-filled with all related objects except GridRun.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of FleetSlot objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptGridRun(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);
        }

        FleetSlotPeer::addSelectColumns($criteria);
        $startcol2 = FleetSlotPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(FleetSlotPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(FleetSlotPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(FleetSlotPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = FleetSlotPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = FleetSlotPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = FleetSlotPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                FleetSlotPeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (FleetSlot) to the collection in $obj2 (AuthyGroup)
                $obj2->addFleetSlot($obj1);

            } // if joined row is not null

                // Add objects for joined Authy rows

                $key3 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = AuthyPeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = AuthyPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    AuthyPeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (FleetSlot) to the collection in $obj3 (Authy)
                $obj3->addFleetSlotRelatedByIdCreation($obj1);

            } // if joined row is not null

                // Add objects for joined Authy rows

                $key4 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol4);
                if ($key4 !== null) {
                    $obj4 = AuthyPeer::getInstanceFromPool($key4);
                    if (!$obj4) {

                        $cls = AuthyPeer::getOMClass();

                    $obj4 = new $cls();
                    $obj4->hydrate($row, $startcol4);
                    AuthyPeer::addInstanceToPool($obj4, $key4);
                } // if $obj4 already loaded

                // Add the $obj1 (FleetSlot) to the collection in $obj4 (Authy)
                $obj4->addFleetSlotRelatedByIdModification($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of FleetSlot objects pre-filled with all related objects except AuthyGroup.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of FleetSlot objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptAuthyGroup(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);
        }

        FleetSlotPeer::addSelectColumns($criteria);
        $startcol2 = FleetSlotPeer::NUM_HYDRATE_COLUMNS;

        GridRunPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(FleetSlotPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(FleetSlotPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(FleetSlotPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = FleetSlotPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = FleetSlotPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = FleetSlotPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                FleetSlotPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined GridRun rows

                $key2 = GridRunPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = GridRunPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = GridRunPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    GridRunPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (FleetSlot) to the collection in $obj2 (GridRun)
                $obj2->addFleetSlot($obj1);

            } // if joined row is not null

                // Add objects for joined Authy rows

                $key3 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = AuthyPeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = AuthyPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    AuthyPeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (FleetSlot) to the collection in $obj3 (Authy)
                $obj3->addFleetSlotRelatedByIdCreation($obj1);

            } // if joined row is not null

                // Add objects for joined Authy rows

                $key4 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol4);
                if ($key4 !== null) {
                    $obj4 = AuthyPeer::getInstanceFromPool($key4);
                    if (!$obj4) {

                        $cls = AuthyPeer::getOMClass();

                    $obj4 = new $cls();
                    $obj4->hydrate($row, $startcol4);
                    AuthyPeer::addInstanceToPool($obj4, $key4);
                } // if $obj4 already loaded

                // Add the $obj1 (FleetSlot) to the collection in $obj4 (Authy)
                $obj4->addFleetSlotRelatedByIdModification($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of FleetSlot objects pre-filled with all related objects except AuthyRelatedByIdCreation.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of FleetSlot objects.
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
            $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);
        }

        FleetSlotPeer::addSelectColumns($criteria);
        $startcol2 = FleetSlotPeer::NUM_HYDRATE_COLUMNS;

        GridRunPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(FleetSlotPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(FleetSlotPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = FleetSlotPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = FleetSlotPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = FleetSlotPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                FleetSlotPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined GridRun rows

                $key2 = GridRunPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = GridRunPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = GridRunPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    GridRunPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (FleetSlot) to the collection in $obj2 (GridRun)
                $obj2->addFleetSlot($obj1);

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

                // Add the $obj1 (FleetSlot) to the collection in $obj3 (AuthyGroup)
                $obj3->addFleetSlot($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of FleetSlot objects pre-filled with all related objects except AuthyRelatedByIdModification.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of FleetSlot objects.
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
            $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);
        }

        FleetSlotPeer::addSelectColumns($criteria);
        $startcol2 = FleetSlotPeer::NUM_HYDRATE_COLUMNS;

        GridRunPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(FleetSlotPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(FleetSlotPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = FleetSlotPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = FleetSlotPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = FleetSlotPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                FleetSlotPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined GridRun rows

                $key2 = GridRunPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = GridRunPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = GridRunPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    GridRunPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (FleetSlot) to the collection in $obj2 (GridRun)
                $obj2->addFleetSlot($obj1);

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

                // Add the $obj1 (FleetSlot) to the collection in $obj3 (AuthyGroup)
                $obj3->addFleetSlot($obj1);

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
        return Propel::getDatabaseMap(FleetSlotPeer::DATABASE_NAME)->getTable(FleetSlotPeer::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this peer class.
     */
    public static function buildTableMap()
    {
      $dbMap = Propel::getDatabaseMap(BaseFleetSlotPeer::DATABASE_NAME);
      if (!$dbMap->hasTable(BaseFleetSlotPeer::TABLE_NAME)) {
        $dbMap->addTableObject(new \App\map\FleetSlotTableMap());
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
        return FleetSlotPeer::OM_CLASS;
    }

    /**
     * Performs an INSERT on the database, given a FleetSlot or Criteria object.
     *
     * @param      mixed $values Criteria or FleetSlot object containing data that is used to create the INSERT statement.
     * @param      PropelPDO $con the PropelPDO connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doInsert($values, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity
        } else {
            $criteria = $values->buildCriteria(); // build Criteria from FleetSlot object
        }

        if ($criteria->containsKey(FleetSlotPeer::ID_FLEET_SLOT) && $criteria->keyContainsValue(FleetSlotPeer::ID_FLEET_SLOT) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.FleetSlotPeer::ID_FLEET_SLOT.')');
        }


        // Set the correct dbName
        $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);

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
     * Performs an UPDATE on the database, given a FleetSlot or Criteria object.
     *
     * @param      mixed $values Criteria or FleetSlot object containing data that is used to create the UPDATE statement.
     * @param      PropelPDO $con The connection to use (specify PropelPDO connection object to exert more control over transactions).
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doUpdate($values, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $selectCriteria = new Criteria(FleetSlotPeer::DATABASE_NAME);

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity

            $comparison = $criteria->getComparison(FleetSlotPeer::ID_FLEET_SLOT);
            $value = $criteria->remove(FleetSlotPeer::ID_FLEET_SLOT);
            if ($value) {
                $selectCriteria->add(FleetSlotPeer::ID_FLEET_SLOT, $value, $comparison);
            } else {
                $selectCriteria->setPrimaryTableName(FleetSlotPeer::TABLE_NAME);
            }

        } else { // $values is FleetSlot object
            $criteria = $values->buildCriteria(); // gets full criteria
            $selectCriteria = $values->buildPkeyCriteria(); // gets criteria w/ primary key(s)
        }

        // set the correct dbName
        $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);

        return BasePeer::doUpdate($selectCriteria, $criteria, $con);
    }

    /**
     * Deletes all rows from the fleet_slot table.
     *
     * @param      PropelPDO $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException
     */
    public static function doDeleteAll(?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += BasePeer::doDeleteAll(FleetSlotPeer::TABLE_NAME, $con, FleetSlotPeer::DATABASE_NAME);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            FleetSlotPeer::clearInstancePool();
            FleetSlotPeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs a DELETE on the database, given a FleetSlot or Criteria object OR a primary key value.
     *
     * @param      mixed $values Criteria or FleetSlot object or primary key or array of primary keys
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
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            // invalidate the cache for all objects of this type, since we have no
            // way of knowing (without running a query) what objects should be invalidated
            // from the cache based on this Criteria.
            FleetSlotPeer::clearInstancePool();
            // rename for clarity
            $criteria = clone $values;
        } elseif ($values instanceof FleetSlot) { // it's a model object
            // invalidate the cache for this single object
            FleetSlotPeer::removeInstanceFromPool($values);
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(FleetSlotPeer::DATABASE_NAME);
            $criteria->add(FleetSlotPeer::ID_FLEET_SLOT, (array) $values, Criteria::IN);
            // invalidate the cache for this object(s)
            foreach ((array) $values as $singleval) {
                FleetSlotPeer::removeInstanceFromPool($singleval);
            }
        }

        // Set the correct dbName
        $criteria->setDbName(FleetSlotPeer::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();

            $affectedRows += BasePeer::doDelete($criteria, $con);
            FleetSlotPeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Validates all modified columns of given FleetSlot object.
     * If parameter $columns is either a single column name or an array of column names
     * than only those columns are validated.
     *
     * NOTICE: This does not apply to primary or foreign keys for now.
     *
     * @param FleetSlot $obj The object to validate.
     * @param      mixed $cols Column name or array of column names.
     *
     * @return mixed TRUE if all columns are valid or the error message of the first invalid column.
     */
    public static function doValidate($obj, $cols = null)
    {
        $columns = array();

        if ($cols) {
            $dbMap = Propel::getDatabaseMap(FleetSlotPeer::DATABASE_NAME);
            $tableMap = $dbMap->getTable(FleetSlotPeer::TABLE_NAME);

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

        if ($obj->isNew() || $obj->isColumnModified(FleetSlotPeer::SYMBOL))
            $columns[FleetSlotPeer::SYMBOL] = $obj->getSymbol();

        if ($obj->isNew() || $obj->isColumnModified(FleetSlotPeer::SYMBOL))
            $columns[FleetSlotPeer::SYMBOL] = $obj->getSymbol();

        if ($obj->isNew() || $obj->isColumnModified(FleetSlotPeer::ALGO))
            $columns[FleetSlotPeer::ALGO] = $obj->getAlgo();

        if ($obj->isNew() || $obj->isColumnModified(FleetSlotPeer::ALGO))
            $columns[FleetSlotPeer::ALGO] = $obj->getAlgo();

        if ($obj->isNew() || $obj->isColumnModified(FleetSlotPeer::TARGET_SLICE))
            $columns[FleetSlotPeer::TARGET_SLICE] = $obj->getTargetSlice();

        if ($obj->isNew() || $obj->isColumnModified(FleetSlotPeer::STATE))
            $columns[FleetSlotPeer::STATE] = $obj->getState();

        if ($obj->isNew() || $obj->isColumnModified(FleetSlotPeer::STATE))
            $columns[FleetSlotPeer::STATE] = $obj->getState();

        if ($obj->isNew() || $obj->isColumnModified(FleetSlotPeer::CONFIRM_UP))
            $columns[FleetSlotPeer::CONFIRM_UP] = $obj->getConfirmUp();

        if ($obj->isNew() || $obj->isColumnModified(FleetSlotPeer::CONFIRM_DOWN))
            $columns[FleetSlotPeer::CONFIRM_DOWN] = $obj->getConfirmDown();

        if ($obj->isNew() || $obj->isColumnModified(FleetSlotPeer::LAST_VERDICT))
            $columns[FleetSlotPeer::LAST_VERDICT] = $obj->getLastVerdict();

        if ($obj->isNew() || $obj->isColumnModified(FleetSlotPeer::VERDICT_AT))
            $columns[FleetSlotPeer::VERDICT_AT] = $obj->getVerdictAt();

        if ($obj->isNew() || $obj->isColumnModified(FleetSlotPeer::EPISODE_STARTED_AT))
            $columns[FleetSlotPeer::EPISODE_STARTED_AT] = $obj->getEpisodeStartedAt();

        if ($obj->isNew() || $obj->isColumnModified(FleetSlotPeer::ACTIVATION))
            $columns[FleetSlotPeer::ACTIVATION] = $obj->getActivation();

        if ($obj->isNew() || $obj->isColumnModified(FleetSlotPeer::ID_GRID_RUN))
            $columns[FleetSlotPeer::ID_GRID_RUN] = $obj->getIdGridRun();

        if ($obj->isNew() || $obj->isColumnModified(FleetSlotPeer::LAST_EMPTY_ALERT_AT))
            $columns[FleetSlotPeer::LAST_EMPTY_ALERT_AT] = $obj->getLastEmptyAlertAt();

        if ($obj->isNew() || $obj->isColumnModified(FleetSlotPeer::LAST_PARKED_ALERT_AT))
            $columns[FleetSlotPeer::LAST_PARKED_ALERT_AT] = $obj->getLastParkedAlertAt();

        }

        return BasePeer::doValidate(FleetSlotPeer::DATABASE_NAME, FleetSlotPeer::TABLE_NAME, $columns);
    }

    /**
     * Retrieve a single object by pkey.
     *
     * @param int $pk the primary key.
     * @param      PropelPDO $con the connection to use
     * @return FleetSlot
     */
    public static function retrieveByPK($pk, ?PropelPDO $con = null)
    {

        if (null !== ($obj = FleetSlotPeer::getInstanceFromPool((string) $pk))) {
            return $obj;
        }

        if ($con === null) {
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria = new Criteria(FleetSlotPeer::DATABASE_NAME);
        $criteria->add(FleetSlotPeer::ID_FLEET_SLOT, $pk);

        $v = FleetSlotPeer::doSelect($criteria, $con);

        return !empty($v) > 0 ? $v[0] : null;
    }

    /**
     * Retrieve multiple objects by pkey.
     *
     * @param      array $pks List of primary keys
     * @param      PropelPDO $con the connection to use
     * @return FleetSlot[]
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function retrieveByPKs($pks, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $objs = null;
        if (empty($pks)) {
            $objs = array();
        } else {
            $criteria = new Criteria(FleetSlotPeer::DATABASE_NAME);
            $criteria->add(FleetSlotPeer::ID_FLEET_SLOT, $pks, Criteria::IN);
            $objs = FleetSlotPeer::doSelect($criteria, $con);
        }

        return $objs;
    }

} // BaseFleetSlotPeer

// This is the static code needed to register the TableMap for this table with the main Propel class.
//
BaseFleetSlotPeer::buildTableMap();

