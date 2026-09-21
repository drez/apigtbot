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
use App\FleetSlotPeer;
use App\GridRunPeer;
use App\RegimeEpisode;
use App\RegimeEpisodePeer;
use App\map\RegimeEpisodeTableMap;

/**
 * Base static class for performing query and update operations on the 'regime_episode' table.
 *
 * Regime episode
 *
 * @package propel.generator..om
 */
abstract class BaseRegimeEpisodePeer
{

    /** the default database name for this class */
    const DATABASE_NAME = 'apigtbot';

    /** the table name for this class */
    const TABLE_NAME = 'regime_episode';

    /** the related Propel class for this table */
    const OM_CLASS = 'App\\RegimeEpisode';

    /** the related TableMap class for this table */
    const TM_CLASS = 'App\\map\\RegimeEpisodeTableMap';

    /** The total number of columns. */
    const NUM_COLUMNS = 23;

    /** The number of lazy-loaded columns. */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /** The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS) */
    const NUM_HYDRATE_COLUMNS = 23;

    /** the column name for the id_regime_episode field */
    const ID_REGIME_EPISODE = 'regime_episode.id_regime_episode';

    /** the column name for the symbol field */
    const SYMBOL = 'regime_episode.symbol';

    /** the column name for the algo field */
    const ALGO = 'regime_episode.algo';

    /** the column name for the id_fleet_slot field */
    const ID_FLEET_SLOT = 'regime_episode.id_fleet_slot';

    /** the column name for the id_grid_run field */
    const ID_GRID_RUN = 'regime_episode.id_grid_run';

    /** the column name for the verdict field */
    const VERDICT = 'regime_episode.verdict';

    /** the column name for the opened_at field */
    const OPENED_AT = 'regime_episode.opened_at';

    /** the column name for the closed_at field */
    const CLOSED_AT = 'regime_episode.closed_at';

    /** the column name for the price_open field */
    const PRICE_OPEN = 'regime_episode.price_open';

    /** the column name for the price_close field */
    const PRICE_CLOSE = 'regime_episode.price_close';

    /** the column name for the engaged_pct_tw field */
    const ENGAGED_PCT_TW = 'regime_episode.engaged_pct_tw';

    /** the column name for the samples field */
    const SAMPLES = 'regime_episode.samples';

    /** the column name for the realized field */
    const REALIZED = 'regime_episode.realized';

    /** the column name for the mtm_close field */
    const MTM_CLOSE = 'regime_episode.mtm_close';

    /** the column name for the hodl_pct field */
    const HODL_PCT = 'regime_episode.hodl_pct';

    /** the column name for the captured_pct field */
    const CAPTURED_PCT = 'regime_episode.captured_pct';

    /** the column name for the idle_samples field */
    const IDLE_SAMPLES = 'regime_episode.idle_samples';

    /** the column name for the idle_alerted_at field */
    const IDLE_ALERTED_AT = 'regime_episode.idle_alerted_at';

    /** the column name for the date_creation field */
    const DATE_CREATION = 'regime_episode.date_creation';

    /** the column name for the date_modification field */
    const DATE_MODIFICATION = 'regime_episode.date_modification';

    /** the column name for the id_group_creation field */
    const ID_GROUP_CREATION = 'regime_episode.id_group_creation';

    /** the column name for the id_creation field */
    const ID_CREATION = 'regime_episode.id_creation';

    /** the column name for the id_modification field */
    const ID_MODIFICATION = 'regime_episode.id_modification';

    /** The enumerated values for the algo field */
    const ALGO_TREND = 'Trend';
    const ALGO_GRID = 'Grid';

    /** The default string format for model objects of the related table **/
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * An identity map to hold any loaded instances of RegimeEpisode objects.
     * This must be public so that other peer classes can access this when hydrating from JOIN
     * queries.
     * @var        array RegimeEpisode[]
     */
    public static $instances = array();


    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. RegimeEpisodePeer::$fieldNames[RegimeEpisodePeer::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        BasePeer::TYPE_PHPNAME => array ('IdRegimeEpisode', 'Symbol', 'Algo', 'IdFleetSlot', 'IdGridRun', 'Verdict', 'OpenedAt', 'ClosedAt', 'PriceOpen', 'PriceClose', 'EngagedPctTw', 'Samples', 'Realized', 'MtmClose', 'HodlPct', 'CapturedPct', 'IdleSamples', 'IdleAlertedAt', 'DateCreation', 'DateModification', 'IdGroupCreation', 'IdCreation', 'IdModification', ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('idRegimeEpisode', 'symbol', 'algo', 'idFleetSlot', 'idGridRun', 'verdict', 'openedAt', 'closedAt', 'priceOpen', 'priceClose', 'engagedPctTw', 'samples', 'realized', 'mtmClose', 'hodlPct', 'capturedPct', 'idleSamples', 'idleAlertedAt', 'dateCreation', 'dateModification', 'idGroupCreation', 'idCreation', 'idModification', ),
        BasePeer::TYPE_COLNAME => array (RegimeEpisodePeer::ID_REGIME_EPISODE, RegimeEpisodePeer::SYMBOL, RegimeEpisodePeer::ALGO, RegimeEpisodePeer::ID_FLEET_SLOT, RegimeEpisodePeer::ID_GRID_RUN, RegimeEpisodePeer::VERDICT, RegimeEpisodePeer::OPENED_AT, RegimeEpisodePeer::CLOSED_AT, RegimeEpisodePeer::PRICE_OPEN, RegimeEpisodePeer::PRICE_CLOSE, RegimeEpisodePeer::ENGAGED_PCT_TW, RegimeEpisodePeer::SAMPLES, RegimeEpisodePeer::REALIZED, RegimeEpisodePeer::MTM_CLOSE, RegimeEpisodePeer::HODL_PCT, RegimeEpisodePeer::CAPTURED_PCT, RegimeEpisodePeer::IDLE_SAMPLES, RegimeEpisodePeer::IDLE_ALERTED_AT, RegimeEpisodePeer::DATE_CREATION, RegimeEpisodePeer::DATE_MODIFICATION, RegimeEpisodePeer::ID_GROUP_CREATION, RegimeEpisodePeer::ID_CREATION, RegimeEpisodePeer::ID_MODIFICATION, ),
        BasePeer::TYPE_RAW_COLNAME => array ('ID_REGIME_EPISODE', 'SYMBOL', 'ALGO', 'ID_FLEET_SLOT', 'ID_GRID_RUN', 'VERDICT', 'OPENED_AT', 'CLOSED_AT', 'PRICE_OPEN', 'PRICE_CLOSE', 'ENGAGED_PCT_TW', 'SAMPLES', 'REALIZED', 'MTM_CLOSE', 'HODL_PCT', 'CAPTURED_PCT', 'IDLE_SAMPLES', 'IDLE_ALERTED_AT', 'DATE_CREATION', 'DATE_MODIFICATION', 'ID_GROUP_CREATION', 'ID_CREATION', 'ID_MODIFICATION', ),
        BasePeer::TYPE_FIELDNAME => array ('id_regime_episode', 'symbol', 'algo', 'id_fleet_slot', 'id_grid_run', 'verdict', 'opened_at', 'closed_at', 'price_open', 'price_close', 'engaged_pct_tw', 'samples', 'realized', 'mtm_close', 'hodl_pct', 'captured_pct', 'idle_samples', 'idle_alerted_at', 'date_creation', 'date_modification', 'id_group_creation', 'id_creation', 'id_modification', ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. RegimeEpisodePeer::$fieldNames[BasePeer::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        BasePeer::TYPE_PHPNAME => array ('IdRegimeEpisode' => 0, 'Symbol' => 1, 'Algo' => 2, 'IdFleetSlot' => 3, 'IdGridRun' => 4, 'Verdict' => 5, 'OpenedAt' => 6, 'ClosedAt' => 7, 'PriceOpen' => 8, 'PriceClose' => 9, 'EngagedPctTw' => 10, 'Samples' => 11, 'Realized' => 12, 'MtmClose' => 13, 'HodlPct' => 14, 'CapturedPct' => 15, 'IdleSamples' => 16, 'IdleAlertedAt' => 17, 'DateCreation' => 18, 'DateModification' => 19, 'IdGroupCreation' => 20, 'IdCreation' => 21, 'IdModification' => 22, ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('idRegimeEpisode' => 0, 'symbol' => 1, 'algo' => 2, 'idFleetSlot' => 3, 'idGridRun' => 4, 'verdict' => 5, 'openedAt' => 6, 'closedAt' => 7, 'priceOpen' => 8, 'priceClose' => 9, 'engagedPctTw' => 10, 'samples' => 11, 'realized' => 12, 'mtmClose' => 13, 'hodlPct' => 14, 'capturedPct' => 15, 'idleSamples' => 16, 'idleAlertedAt' => 17, 'dateCreation' => 18, 'dateModification' => 19, 'idGroupCreation' => 20, 'idCreation' => 21, 'idModification' => 22, ),
        BasePeer::TYPE_COLNAME => array (RegimeEpisodePeer::ID_REGIME_EPISODE => 0, RegimeEpisodePeer::SYMBOL => 1, RegimeEpisodePeer::ALGO => 2, RegimeEpisodePeer::ID_FLEET_SLOT => 3, RegimeEpisodePeer::ID_GRID_RUN => 4, RegimeEpisodePeer::VERDICT => 5, RegimeEpisodePeer::OPENED_AT => 6, RegimeEpisodePeer::CLOSED_AT => 7, RegimeEpisodePeer::PRICE_OPEN => 8, RegimeEpisodePeer::PRICE_CLOSE => 9, RegimeEpisodePeer::ENGAGED_PCT_TW => 10, RegimeEpisodePeer::SAMPLES => 11, RegimeEpisodePeer::REALIZED => 12, RegimeEpisodePeer::MTM_CLOSE => 13, RegimeEpisodePeer::HODL_PCT => 14, RegimeEpisodePeer::CAPTURED_PCT => 15, RegimeEpisodePeer::IDLE_SAMPLES => 16, RegimeEpisodePeer::IDLE_ALERTED_AT => 17, RegimeEpisodePeer::DATE_CREATION => 18, RegimeEpisodePeer::DATE_MODIFICATION => 19, RegimeEpisodePeer::ID_GROUP_CREATION => 20, RegimeEpisodePeer::ID_CREATION => 21, RegimeEpisodePeer::ID_MODIFICATION => 22, ),
        BasePeer::TYPE_RAW_COLNAME => array ('ID_REGIME_EPISODE' => 0, 'SYMBOL' => 1, 'ALGO' => 2, 'ID_FLEET_SLOT' => 3, 'ID_GRID_RUN' => 4, 'VERDICT' => 5, 'OPENED_AT' => 6, 'CLOSED_AT' => 7, 'PRICE_OPEN' => 8, 'PRICE_CLOSE' => 9, 'ENGAGED_PCT_TW' => 10, 'SAMPLES' => 11, 'REALIZED' => 12, 'MTM_CLOSE' => 13, 'HODL_PCT' => 14, 'CAPTURED_PCT' => 15, 'IDLE_SAMPLES' => 16, 'IDLE_ALERTED_AT' => 17, 'DATE_CREATION' => 18, 'DATE_MODIFICATION' => 19, 'ID_GROUP_CREATION' => 20, 'ID_CREATION' => 21, 'ID_MODIFICATION' => 22, ),
        BasePeer::TYPE_FIELDNAME => array ('id_regime_episode' => 0, 'symbol' => 1, 'algo' => 2, 'id_fleet_slot' => 3, 'id_grid_run' => 4, 'verdict' => 5, 'opened_at' => 6, 'closed_at' => 7, 'price_open' => 8, 'price_close' => 9, 'engaged_pct_tw' => 10, 'samples' => 11, 'realized' => 12, 'mtm_close' => 13, 'hodl_pct' => 14, 'captured_pct' => 15, 'idle_samples' => 16, 'idle_alerted_at' => 17, 'date_creation' => 18, 'date_modification' => 19, 'id_group_creation' => 20, 'id_creation' => 21, 'id_modification' => 22, ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, )
    );

    /** The enumerated values for this table */
    protected static $enumValueSets = array(
        RegimeEpisodePeer::ALGO => array(
            RegimeEpisodePeer::ALGO_TREND,
            RegimeEpisodePeer::ALGO_GRID,
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
        $toNames = RegimeEpisodePeer::getFieldNames($toType);
        $key = isset(RegimeEpisodePeer::$fieldKeys[$fromType][$name]) ? RegimeEpisodePeer::$fieldKeys[$fromType][$name] : null;
        if ($key === null) {
            throw new PropelException("'$name' could not be found in the field names of type '$fromType'. These are: " . print_r(RegimeEpisodePeer::$fieldKeys[$fromType], true));
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
        if (!array_key_exists($type, RegimeEpisodePeer::$fieldNames)) {
            throw new PropelException('Method getFieldNames() expects the parameter $type to be one of the class constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME, BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM. ' . $type . ' was given.');
        }

        return RegimeEpisodePeer::$fieldNames[$type];
    }

    /**
     * Gets the list of values for all ENUM columns
     * @return array
     */
    public static function getValueSets()
    {
      return RegimeEpisodePeer::$enumValueSets;
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
        $valueSets = RegimeEpisodePeer::getValueSets();

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
        $values = RegimeEpisodePeer::getValueSet($colname);
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
     * @param      string $column The column name for current table. (i.e. RegimeEpisodePeer::COLUMN_NAME).
     * @return string
     */
    public static function alias($alias, $column)
    {
        return str_replace(RegimeEpisodePeer::TABLE_NAME.'.', $alias.'.', $column);
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
            $criteria->addSelectColumn(RegimeEpisodePeer::ID_REGIME_EPISODE);
            $criteria->addSelectColumn(RegimeEpisodePeer::SYMBOL);
            $criteria->addSelectColumn(RegimeEpisodePeer::ALGO);
            $criteria->addSelectColumn(RegimeEpisodePeer::ID_FLEET_SLOT);
            $criteria->addSelectColumn(RegimeEpisodePeer::ID_GRID_RUN);
            $criteria->addSelectColumn(RegimeEpisodePeer::VERDICT);
            $criteria->addSelectColumn(RegimeEpisodePeer::OPENED_AT);
            $criteria->addSelectColumn(RegimeEpisodePeer::CLOSED_AT);
            $criteria->addSelectColumn(RegimeEpisodePeer::PRICE_OPEN);
            $criteria->addSelectColumn(RegimeEpisodePeer::PRICE_CLOSE);
            $criteria->addSelectColumn(RegimeEpisodePeer::ENGAGED_PCT_TW);
            $criteria->addSelectColumn(RegimeEpisodePeer::SAMPLES);
            $criteria->addSelectColumn(RegimeEpisodePeer::REALIZED);
            $criteria->addSelectColumn(RegimeEpisodePeer::MTM_CLOSE);
            $criteria->addSelectColumn(RegimeEpisodePeer::HODL_PCT);
            $criteria->addSelectColumn(RegimeEpisodePeer::CAPTURED_PCT);
            $criteria->addSelectColumn(RegimeEpisodePeer::IDLE_SAMPLES);
            $criteria->addSelectColumn(RegimeEpisodePeer::IDLE_ALERTED_AT);
            $criteria->addSelectColumn(RegimeEpisodePeer::DATE_CREATION);
            $criteria->addSelectColumn(RegimeEpisodePeer::DATE_MODIFICATION);
            $criteria->addSelectColumn(RegimeEpisodePeer::ID_GROUP_CREATION);
            $criteria->addSelectColumn(RegimeEpisodePeer::ID_CREATION);
            $criteria->addSelectColumn(RegimeEpisodePeer::ID_MODIFICATION);
        } else {
            $criteria->addSelectColumn($alias . '.id_regime_episode');
            $criteria->addSelectColumn($alias . '.symbol');
            $criteria->addSelectColumn($alias . '.algo');
            $criteria->addSelectColumn($alias . '.id_fleet_slot');
            $criteria->addSelectColumn($alias . '.id_grid_run');
            $criteria->addSelectColumn($alias . '.verdict');
            $criteria->addSelectColumn($alias . '.opened_at');
            $criteria->addSelectColumn($alias . '.closed_at');
            $criteria->addSelectColumn($alias . '.price_open');
            $criteria->addSelectColumn($alias . '.price_close');
            $criteria->addSelectColumn($alias . '.engaged_pct_tw');
            $criteria->addSelectColumn($alias . '.samples');
            $criteria->addSelectColumn($alias . '.realized');
            $criteria->addSelectColumn($alias . '.mtm_close');
            $criteria->addSelectColumn($alias . '.hodl_pct');
            $criteria->addSelectColumn($alias . '.captured_pct');
            $criteria->addSelectColumn($alias . '.idle_samples');
            $criteria->addSelectColumn($alias . '.idle_alerted_at');
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
        $criteria->setPrimaryTableName(RegimeEpisodePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            RegimeEpisodePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count
        $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME); // Set the correct dbName

        if ($con === null) {
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return RegimeEpisode
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectOne(Criteria $criteria, ?PropelPDO $con = null)
    {
        $critcopy = clone $criteria;
        $critcopy->setLimit(1);
        $objects = RegimeEpisodePeer::doSelect($critcopy, $con);
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
        return RegimeEpisodePeer::populateObjects(RegimeEpisodePeer::doSelectStmt($criteria, $con));
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
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        if (!$criteria->hasSelectClause()) {
            $criteria = clone $criteria;
            RegimeEpisodePeer::addSelectColumns($criteria);
        }

        // Set the correct dbName
        $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);

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
     * @param RegimeEpisode $obj A RegimeEpisode object.
     * @param      string $key (optional) key to use for instance map (for performance boost if key was already calculated externally).
     */
    public static function addInstanceToPool($obj, $key = null)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if ($key === null) {
                $key = (string) $obj->getIdRegimeEpisode();
            } // if key === null
            RegimeEpisodePeer::$instances[$key] = $obj;
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
     * @param      mixed $value A RegimeEpisode object or a primary key value.
     *
     * @return void
     * @throws PropelException - if the value is invalid.
     */
    public static function removeInstanceFromPool($value)
    {
        if (Propel::isInstancePoolingEnabled() && $value !== null) {
            if (is_object($value) && $value instanceof RegimeEpisode) {
                $key = (string) $value->getIdRegimeEpisode();
            } elseif (is_scalar($value)) {
                // assume we've been passed a primary key
                $key = (string) $value;
            } else {
                $e = new PropelException("Invalid value passed to removeInstanceFromPool().  Expected primary key or RegimeEpisode object; got " . (is_object($value) ? get_class($value) . ' object.' : var_export($value,true)));
                throw $e;
            }

            unset(RegimeEpisodePeer::$instances[$key]);
        }
    } // removeInstanceFromPool()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param      string $key The key (@see getPrimaryKeyHash()) for this instance.
     * @return RegimeEpisode Found object or null if 1) no instance exists for specified key or 2) instance pooling has been disabled.
     * @see        getPrimaryKeyHash()
     */
    public static function getInstanceFromPool($key)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if (isset(RegimeEpisodePeer::$instances[$key])) {
                return RegimeEpisodePeer::$instances[$key];
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
        foreach (RegimeEpisodePeer::$instances as $instance) {
          $instance->clearAllReferences(true);
        }
      }
        RegimeEpisodePeer::$instances = array();
    }

    /**
     * Method to invalidate the instance pool of all tables related to regime_episode
     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool()
    {
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
        $cls = RegimeEpisodePeer::getOMClass();
        // populate the object(s)
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key = RegimeEpisodePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj = RegimeEpisodePeer::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                RegimeEpisodePeer::addInstanceToPool($obj, $key);
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
     * @return array (RegimeEpisode object, last column rank)
     */
    public static function populateObject($row, $startcol = 0)
    {
        $key = RegimeEpisodePeer::getPrimaryKeyHashFromRow($row, $startcol);
        if (null !== ($obj = RegimeEpisodePeer::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $startcol, true); // rehydrate
            $col = $startcol + RegimeEpisodePeer::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = RegimeEpisodePeer::OM_CLASS;
            $obj = new $cls();
            $col = $obj->hydrate($row, $startcol);
            RegimeEpisodePeer::addInstanceToPool($obj, $key);
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
        return RegimeEpisodePeer::getSqlValueForEnum(RegimeEpisodePeer::ALGO, $enumVal);
    }


    /**
     * Returns the number of rows matching criteria, joining the related FleetSlot table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinFleetSlot(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(RegimeEpisodePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            RegimeEpisodePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(RegimeEpisodePeer::ID_FLEET_SLOT, FleetSlotPeer::ID_FLEET_SLOT, $join_behavior);

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
        $criteria->setPrimaryTableName(RegimeEpisodePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            RegimeEpisodePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(RegimeEpisodePeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

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
        $criteria->setPrimaryTableName(RegimeEpisodePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            RegimeEpisodePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(RegimeEpisodePeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

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
        $criteria->setPrimaryTableName(RegimeEpisodePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            RegimeEpisodePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(RegimeEpisodePeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
        $criteria->setPrimaryTableName(RegimeEpisodePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            RegimeEpisodePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(RegimeEpisodePeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
     * Selects a collection of RegimeEpisode objects pre-filled with their FleetSlot objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of RegimeEpisode objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinFleetSlot(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);
        }

        RegimeEpisodePeer::addSelectColumns($criteria);
        $startcol = RegimeEpisodePeer::NUM_HYDRATE_COLUMNS;
        FleetSlotPeer::addSelectColumns($criteria);

        $criteria->addJoin(RegimeEpisodePeer::ID_FLEET_SLOT, FleetSlotPeer::ID_FLEET_SLOT, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = RegimeEpisodePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = RegimeEpisodePeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = RegimeEpisodePeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                RegimeEpisodePeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = FleetSlotPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = FleetSlotPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = FleetSlotPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    FleetSlotPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (RegimeEpisode) to $obj2 (FleetSlot)
                $obj2->addRegimeEpisode($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of RegimeEpisode objects pre-filled with their GridRun objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of RegimeEpisode objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinGridRun(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);
        }

        RegimeEpisodePeer::addSelectColumns($criteria);
        $startcol = RegimeEpisodePeer::NUM_HYDRATE_COLUMNS;
        GridRunPeer::addSelectColumns($criteria);

        $criteria->addJoin(RegimeEpisodePeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = RegimeEpisodePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = RegimeEpisodePeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = RegimeEpisodePeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                RegimeEpisodePeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (RegimeEpisode) to $obj2 (GridRun)
                $obj2->addRegimeEpisode($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of RegimeEpisode objects pre-filled with their AuthyGroup objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of RegimeEpisode objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyGroup(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);
        }

        RegimeEpisodePeer::addSelectColumns($criteria);
        $startcol = RegimeEpisodePeer::NUM_HYDRATE_COLUMNS;
        AuthyGroupPeer::addSelectColumns($criteria);

        $criteria->addJoin(RegimeEpisodePeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = RegimeEpisodePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = RegimeEpisodePeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = RegimeEpisodePeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                RegimeEpisodePeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (RegimeEpisode) to $obj2 (AuthyGroup)
                $obj2->addRegimeEpisode($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of RegimeEpisode objects pre-filled with their Authy objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of RegimeEpisode objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyRelatedByIdCreation(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);
        }

        RegimeEpisodePeer::addSelectColumns($criteria);
        $startcol = RegimeEpisodePeer::NUM_HYDRATE_COLUMNS;
        AuthyPeer::addSelectColumns($criteria);

        $criteria->addJoin(RegimeEpisodePeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = RegimeEpisodePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = RegimeEpisodePeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = RegimeEpisodePeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                RegimeEpisodePeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (RegimeEpisode) to $obj2 (Authy)
                $obj2->addRegimeEpisodeRelatedByIdCreation($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of RegimeEpisode objects pre-filled with their Authy objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of RegimeEpisode objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyRelatedByIdModification(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);
        }

        RegimeEpisodePeer::addSelectColumns($criteria);
        $startcol = RegimeEpisodePeer::NUM_HYDRATE_COLUMNS;
        AuthyPeer::addSelectColumns($criteria);

        $criteria->addJoin(RegimeEpisodePeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = RegimeEpisodePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = RegimeEpisodePeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = RegimeEpisodePeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                RegimeEpisodePeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (RegimeEpisode) to $obj2 (Authy)
                $obj2->addRegimeEpisodeRelatedByIdModification($obj1);

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
        $criteria->setPrimaryTableName(RegimeEpisodePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            RegimeEpisodePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(RegimeEpisodePeer::ID_FLEET_SLOT, FleetSlotPeer::ID_FLEET_SLOT, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
     * Selects a collection of RegimeEpisode objects pre-filled with all related objects.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of RegimeEpisode objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAll(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);
        }

        RegimeEpisodePeer::addSelectColumns($criteria);
        $startcol2 = RegimeEpisodePeer::NUM_HYDRATE_COLUMNS;

        FleetSlotPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + FleetSlotPeer::NUM_HYDRATE_COLUMNS;

        GridRunPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol6 = $startcol5 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol7 = $startcol6 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(RegimeEpisodePeer::ID_FLEET_SLOT, FleetSlotPeer::ID_FLEET_SLOT, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = RegimeEpisodePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = RegimeEpisodePeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = RegimeEpisodePeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                RegimeEpisodePeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

            // Add objects for joined FleetSlot rows

            $key2 = FleetSlotPeer::getPrimaryKeyHashFromRow($row, $startcol2);
            if ($key2 !== null) {
                $obj2 = FleetSlotPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = FleetSlotPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    FleetSlotPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 loaded

                // Add the $obj1 (RegimeEpisode) to the collection in $obj2 (FleetSlot)
                $obj2->addRegimeEpisode($obj1);
            } // if joined row not null

            // Add objects for joined GridRun rows

            $key3 = GridRunPeer::getPrimaryKeyHashFromRow($row, $startcol3);
            if ($key3 !== null) {
                $obj3 = GridRunPeer::getInstanceFromPool($key3);
                if (!$obj3) {

                    $cls = GridRunPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    GridRunPeer::addInstanceToPool($obj3, $key3);
                } // if obj3 loaded

                // Add the $obj1 (RegimeEpisode) to the collection in $obj3 (GridRun)
                $obj3->addRegimeEpisode($obj1);
            } // if joined row not null

            // Add objects for joined AuthyGroup rows

            $key4 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol4);
            if ($key4 !== null) {
                $obj4 = AuthyGroupPeer::getInstanceFromPool($key4);
                if (!$obj4) {

                    $cls = AuthyGroupPeer::getOMClass();

                    $obj4 = new $cls();
                    $obj4->hydrate($row, $startcol4);
                    AuthyGroupPeer::addInstanceToPool($obj4, $key4);
                } // if obj4 loaded

                // Add the $obj1 (RegimeEpisode) to the collection in $obj4 (AuthyGroup)
                $obj4->addRegimeEpisode($obj1);
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

                // Add the $obj1 (RegimeEpisode) to the collection in $obj5 (Authy)
                $obj5->addRegimeEpisodeRelatedByIdCreation($obj1);
            } // if joined row not null

            // Add objects for joined Authy rows

            $key6 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol6);
            if ($key6 !== null) {
                $obj6 = AuthyPeer::getInstanceFromPool($key6);
                if (!$obj6) {

                    $cls = AuthyPeer::getOMClass();

                    $obj6 = new $cls();
                    $obj6->hydrate($row, $startcol6);
                    AuthyPeer::addInstanceToPool($obj6, $key6);
                } // if obj6 loaded

                // Add the $obj1 (RegimeEpisode) to the collection in $obj6 (Authy)
                $obj6->addRegimeEpisodeRelatedByIdModification($obj1);
            } // if joined row not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Returns the number of rows matching criteria, joining the related FleetSlot table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptFleetSlot(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(RegimeEpisodePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            RegimeEpisodePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(RegimeEpisodePeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
        $criteria->setPrimaryTableName(RegimeEpisodePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            RegimeEpisodePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(RegimeEpisodePeer::ID_FLEET_SLOT, FleetSlotPeer::ID_FLEET_SLOT, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
        $criteria->setPrimaryTableName(RegimeEpisodePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            RegimeEpisodePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(RegimeEpisodePeer::ID_FLEET_SLOT, FleetSlotPeer::ID_FLEET_SLOT, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
        $criteria->setPrimaryTableName(RegimeEpisodePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            RegimeEpisodePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(RegimeEpisodePeer::ID_FLEET_SLOT, FleetSlotPeer::ID_FLEET_SLOT, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

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
        $criteria->setPrimaryTableName(RegimeEpisodePeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            RegimeEpisodePeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(RegimeEpisodePeer::ID_FLEET_SLOT, FleetSlotPeer::ID_FLEET_SLOT, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

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
     * Selects a collection of RegimeEpisode objects pre-filled with all related objects except FleetSlot.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of RegimeEpisode objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptFleetSlot(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);
        }

        RegimeEpisodePeer::addSelectColumns($criteria);
        $startcol2 = RegimeEpisodePeer::NUM_HYDRATE_COLUMNS;

        GridRunPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol6 = $startcol5 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(RegimeEpisodePeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = RegimeEpisodePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = RegimeEpisodePeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = RegimeEpisodePeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                RegimeEpisodePeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (RegimeEpisode) to the collection in $obj2 (GridRun)
                $obj2->addRegimeEpisode($obj1);

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

                // Add the $obj1 (RegimeEpisode) to the collection in $obj3 (AuthyGroup)
                $obj3->addRegimeEpisode($obj1);

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

                // Add the $obj1 (RegimeEpisode) to the collection in $obj4 (Authy)
                $obj4->addRegimeEpisodeRelatedByIdCreation($obj1);

            } // if joined row is not null

                // Add objects for joined Authy rows

                $key5 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol5);
                if ($key5 !== null) {
                    $obj5 = AuthyPeer::getInstanceFromPool($key5);
                    if (!$obj5) {

                        $cls = AuthyPeer::getOMClass();

                    $obj5 = new $cls();
                    $obj5->hydrate($row, $startcol5);
                    AuthyPeer::addInstanceToPool($obj5, $key5);
                } // if $obj5 already loaded

                // Add the $obj1 (RegimeEpisode) to the collection in $obj5 (Authy)
                $obj5->addRegimeEpisodeRelatedByIdModification($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of RegimeEpisode objects pre-filled with all related objects except GridRun.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of RegimeEpisode objects.
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
            $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);
        }

        RegimeEpisodePeer::addSelectColumns($criteria);
        $startcol2 = RegimeEpisodePeer::NUM_HYDRATE_COLUMNS;

        FleetSlotPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + FleetSlotPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol6 = $startcol5 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(RegimeEpisodePeer::ID_FLEET_SLOT, FleetSlotPeer::ID_FLEET_SLOT, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = RegimeEpisodePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = RegimeEpisodePeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = RegimeEpisodePeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                RegimeEpisodePeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined FleetSlot rows

                $key2 = FleetSlotPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = FleetSlotPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = FleetSlotPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    FleetSlotPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (RegimeEpisode) to the collection in $obj2 (FleetSlot)
                $obj2->addRegimeEpisode($obj1);

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

                // Add the $obj1 (RegimeEpisode) to the collection in $obj3 (AuthyGroup)
                $obj3->addRegimeEpisode($obj1);

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

                // Add the $obj1 (RegimeEpisode) to the collection in $obj4 (Authy)
                $obj4->addRegimeEpisodeRelatedByIdCreation($obj1);

            } // if joined row is not null

                // Add objects for joined Authy rows

                $key5 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol5);
                if ($key5 !== null) {
                    $obj5 = AuthyPeer::getInstanceFromPool($key5);
                    if (!$obj5) {

                        $cls = AuthyPeer::getOMClass();

                    $obj5 = new $cls();
                    $obj5->hydrate($row, $startcol5);
                    AuthyPeer::addInstanceToPool($obj5, $key5);
                } // if $obj5 already loaded

                // Add the $obj1 (RegimeEpisode) to the collection in $obj5 (Authy)
                $obj5->addRegimeEpisodeRelatedByIdModification($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of RegimeEpisode objects pre-filled with all related objects except AuthyGroup.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of RegimeEpisode objects.
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
            $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);
        }

        RegimeEpisodePeer::addSelectColumns($criteria);
        $startcol2 = RegimeEpisodePeer::NUM_HYDRATE_COLUMNS;

        FleetSlotPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + FleetSlotPeer::NUM_HYDRATE_COLUMNS;

        GridRunPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol6 = $startcol5 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(RegimeEpisodePeer::ID_FLEET_SLOT, FleetSlotPeer::ID_FLEET_SLOT, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = RegimeEpisodePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = RegimeEpisodePeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = RegimeEpisodePeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                RegimeEpisodePeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined FleetSlot rows

                $key2 = FleetSlotPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = FleetSlotPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = FleetSlotPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    FleetSlotPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (RegimeEpisode) to the collection in $obj2 (FleetSlot)
                $obj2->addRegimeEpisode($obj1);

            } // if joined row is not null

                // Add objects for joined GridRun rows

                $key3 = GridRunPeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = GridRunPeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = GridRunPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    GridRunPeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (RegimeEpisode) to the collection in $obj3 (GridRun)
                $obj3->addRegimeEpisode($obj1);

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

                // Add the $obj1 (RegimeEpisode) to the collection in $obj4 (Authy)
                $obj4->addRegimeEpisodeRelatedByIdCreation($obj1);

            } // if joined row is not null

                // Add objects for joined Authy rows

                $key5 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol5);
                if ($key5 !== null) {
                    $obj5 = AuthyPeer::getInstanceFromPool($key5);
                    if (!$obj5) {

                        $cls = AuthyPeer::getOMClass();

                    $obj5 = new $cls();
                    $obj5->hydrate($row, $startcol5);
                    AuthyPeer::addInstanceToPool($obj5, $key5);
                } // if $obj5 already loaded

                // Add the $obj1 (RegimeEpisode) to the collection in $obj5 (Authy)
                $obj5->addRegimeEpisodeRelatedByIdModification($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of RegimeEpisode objects pre-filled with all related objects except AuthyRelatedByIdCreation.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of RegimeEpisode objects.
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
            $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);
        }

        RegimeEpisodePeer::addSelectColumns($criteria);
        $startcol2 = RegimeEpisodePeer::NUM_HYDRATE_COLUMNS;

        FleetSlotPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + FleetSlotPeer::NUM_HYDRATE_COLUMNS;

        GridRunPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(RegimeEpisodePeer::ID_FLEET_SLOT, FleetSlotPeer::ID_FLEET_SLOT, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = RegimeEpisodePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = RegimeEpisodePeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = RegimeEpisodePeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                RegimeEpisodePeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined FleetSlot rows

                $key2 = FleetSlotPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = FleetSlotPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = FleetSlotPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    FleetSlotPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (RegimeEpisode) to the collection in $obj2 (FleetSlot)
                $obj2->addRegimeEpisode($obj1);

            } // if joined row is not null

                // Add objects for joined GridRun rows

                $key3 = GridRunPeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = GridRunPeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = GridRunPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    GridRunPeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (RegimeEpisode) to the collection in $obj3 (GridRun)
                $obj3->addRegimeEpisode($obj1);

            } // if joined row is not null

                // Add objects for joined AuthyGroup rows

                $key4 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol4);
                if ($key4 !== null) {
                    $obj4 = AuthyGroupPeer::getInstanceFromPool($key4);
                    if (!$obj4) {

                        $cls = AuthyGroupPeer::getOMClass();

                    $obj4 = new $cls();
                    $obj4->hydrate($row, $startcol4);
                    AuthyGroupPeer::addInstanceToPool($obj4, $key4);
                } // if $obj4 already loaded

                // Add the $obj1 (RegimeEpisode) to the collection in $obj4 (AuthyGroup)
                $obj4->addRegimeEpisode($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of RegimeEpisode objects pre-filled with all related objects except AuthyRelatedByIdModification.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of RegimeEpisode objects.
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
            $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);
        }

        RegimeEpisodePeer::addSelectColumns($criteria);
        $startcol2 = RegimeEpisodePeer::NUM_HYDRATE_COLUMNS;

        FleetSlotPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + FleetSlotPeer::NUM_HYDRATE_COLUMNS;

        GridRunPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(RegimeEpisodePeer::ID_FLEET_SLOT, FleetSlotPeer::ID_FLEET_SLOT, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(RegimeEpisodePeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = RegimeEpisodePeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = RegimeEpisodePeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = RegimeEpisodePeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                RegimeEpisodePeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined FleetSlot rows

                $key2 = FleetSlotPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = FleetSlotPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = FleetSlotPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    FleetSlotPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (RegimeEpisode) to the collection in $obj2 (FleetSlot)
                $obj2->addRegimeEpisode($obj1);

            } // if joined row is not null

                // Add objects for joined GridRun rows

                $key3 = GridRunPeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = GridRunPeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = GridRunPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    GridRunPeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (RegimeEpisode) to the collection in $obj3 (GridRun)
                $obj3->addRegimeEpisode($obj1);

            } // if joined row is not null

                // Add objects for joined AuthyGroup rows

                $key4 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol4);
                if ($key4 !== null) {
                    $obj4 = AuthyGroupPeer::getInstanceFromPool($key4);
                    if (!$obj4) {

                        $cls = AuthyGroupPeer::getOMClass();

                    $obj4 = new $cls();
                    $obj4->hydrate($row, $startcol4);
                    AuthyGroupPeer::addInstanceToPool($obj4, $key4);
                } // if $obj4 already loaded

                // Add the $obj1 (RegimeEpisode) to the collection in $obj4 (AuthyGroup)
                $obj4->addRegimeEpisode($obj1);

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
        return Propel::getDatabaseMap(RegimeEpisodePeer::DATABASE_NAME)->getTable(RegimeEpisodePeer::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this peer class.
     */
    public static function buildTableMap()
    {
      $dbMap = Propel::getDatabaseMap(BaseRegimeEpisodePeer::DATABASE_NAME);
      if (!$dbMap->hasTable(BaseRegimeEpisodePeer::TABLE_NAME)) {
        $dbMap->addTableObject(new \App\map\RegimeEpisodeTableMap());
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
        return RegimeEpisodePeer::OM_CLASS;
    }

    /**
     * Performs an INSERT on the database, given a RegimeEpisode or Criteria object.
     *
     * @param      mixed $values Criteria or RegimeEpisode object containing data that is used to create the INSERT statement.
     * @param      PropelPDO $con the PropelPDO connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doInsert($values, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity
        } else {
            $criteria = $values->buildCriteria(); // build Criteria from RegimeEpisode object
        }

        if ($criteria->containsKey(RegimeEpisodePeer::ID_REGIME_EPISODE) && $criteria->keyContainsValue(RegimeEpisodePeer::ID_REGIME_EPISODE) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.RegimeEpisodePeer::ID_REGIME_EPISODE.')');
        }


        // Set the correct dbName
        $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);

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
     * Performs an UPDATE on the database, given a RegimeEpisode or Criteria object.
     *
     * @param      mixed $values Criteria or RegimeEpisode object containing data that is used to create the UPDATE statement.
     * @param      PropelPDO $con The connection to use (specify PropelPDO connection object to exert more control over transactions).
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doUpdate($values, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $selectCriteria = new Criteria(RegimeEpisodePeer::DATABASE_NAME);

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity

            $comparison = $criteria->getComparison(RegimeEpisodePeer::ID_REGIME_EPISODE);
            $value = $criteria->remove(RegimeEpisodePeer::ID_REGIME_EPISODE);
            if ($value) {
                $selectCriteria->add(RegimeEpisodePeer::ID_REGIME_EPISODE, $value, $comparison);
            } else {
                $selectCriteria->setPrimaryTableName(RegimeEpisodePeer::TABLE_NAME);
            }

        } else { // $values is RegimeEpisode object
            $criteria = $values->buildCriteria(); // gets full criteria
            $selectCriteria = $values->buildPkeyCriteria(); // gets criteria w/ primary key(s)
        }

        // set the correct dbName
        $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);

        return BasePeer::doUpdate($selectCriteria, $criteria, $con);
    }

    /**
     * Deletes all rows from the regime_episode table.
     *
     * @param      PropelPDO $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException
     */
    public static function doDeleteAll(?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += BasePeer::doDeleteAll(RegimeEpisodePeer::TABLE_NAME, $con, RegimeEpisodePeer::DATABASE_NAME);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            RegimeEpisodePeer::clearInstancePool();
            RegimeEpisodePeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs a DELETE on the database, given a RegimeEpisode or Criteria object OR a primary key value.
     *
     * @param      mixed $values Criteria or RegimeEpisode object or primary key or array of primary keys
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
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            // invalidate the cache for all objects of this type, since we have no
            // way of knowing (without running a query) what objects should be invalidated
            // from the cache based on this Criteria.
            RegimeEpisodePeer::clearInstancePool();
            // rename for clarity
            $criteria = clone $values;
        } elseif ($values instanceof RegimeEpisode) { // it's a model object
            // invalidate the cache for this single object
            RegimeEpisodePeer::removeInstanceFromPool($values);
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(RegimeEpisodePeer::DATABASE_NAME);
            $criteria->add(RegimeEpisodePeer::ID_REGIME_EPISODE, (array) $values, Criteria::IN);
            // invalidate the cache for this object(s)
            foreach ((array) $values as $singleval) {
                RegimeEpisodePeer::removeInstanceFromPool($singleval);
            }
        }

        // Set the correct dbName
        $criteria->setDbName(RegimeEpisodePeer::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();

            $affectedRows += BasePeer::doDelete($criteria, $con);
            RegimeEpisodePeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Validates all modified columns of given RegimeEpisode object.
     * If parameter $columns is either a single column name or an array of column names
     * than only those columns are validated.
     *
     * NOTICE: This does not apply to primary or foreign keys for now.
     *
     * @param RegimeEpisode $obj The object to validate.
     * @param      mixed $cols Column name or array of column names.
     *
     * @return mixed TRUE if all columns are valid or the error message of the first invalid column.
     */
    public static function doValidate($obj, $cols = null)
    {
        $columns = array();

        if ($cols) {
            $dbMap = Propel::getDatabaseMap(RegimeEpisodePeer::DATABASE_NAME);
            $tableMap = $dbMap->getTable(RegimeEpisodePeer::TABLE_NAME);

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

        if ($obj->isNew() || $obj->isColumnModified(RegimeEpisodePeer::SYMBOL))
            $columns[RegimeEpisodePeer::SYMBOL] = $obj->getSymbol();

        if ($obj->isNew() || $obj->isColumnModified(RegimeEpisodePeer::SYMBOL))
            $columns[RegimeEpisodePeer::SYMBOL] = $obj->getSymbol();

        if ($obj->isNew() || $obj->isColumnModified(RegimeEpisodePeer::ALGO))
            $columns[RegimeEpisodePeer::ALGO] = $obj->getAlgo();

        if ($obj->isNew() || $obj->isColumnModified(RegimeEpisodePeer::ALGO))
            $columns[RegimeEpisodePeer::ALGO] = $obj->getAlgo();

        if ($obj->isNew() || $obj->isColumnModified(RegimeEpisodePeer::ID_FLEET_SLOT))
            $columns[RegimeEpisodePeer::ID_FLEET_SLOT] = $obj->getIdFleetSlot();

        if ($obj->isNew() || $obj->isColumnModified(RegimeEpisodePeer::ID_GRID_RUN))
            $columns[RegimeEpisodePeer::ID_GRID_RUN] = $obj->getIdGridRun();

        if ($obj->isNew() || $obj->isColumnModified(RegimeEpisodePeer::VERDICT))
            $columns[RegimeEpisodePeer::VERDICT] = $obj->getVerdict();

        if ($obj->isNew() || $obj->isColumnModified(RegimeEpisodePeer::VERDICT))
            $columns[RegimeEpisodePeer::VERDICT] = $obj->getVerdict();

        if ($obj->isNew() || $obj->isColumnModified(RegimeEpisodePeer::OPENED_AT))
            $columns[RegimeEpisodePeer::OPENED_AT] = $obj->getOpenedAt();

        if ($obj->isNew() || $obj->isColumnModified(RegimeEpisodePeer::OPENED_AT))
            $columns[RegimeEpisodePeer::OPENED_AT] = $obj->getOpenedAt();

        if ($obj->isNew() || $obj->isColumnModified(RegimeEpisodePeer::CLOSED_AT))
            $columns[RegimeEpisodePeer::CLOSED_AT] = $obj->getClosedAt();

        if ($obj->isNew() || $obj->isColumnModified(RegimeEpisodePeer::PRICE_OPEN))
            $columns[RegimeEpisodePeer::PRICE_OPEN] = $obj->getPriceOpen();

        if ($obj->isNew() || $obj->isColumnModified(RegimeEpisodePeer::SAMPLES))
            $columns[RegimeEpisodePeer::SAMPLES] = $obj->getSamples();

        if ($obj->isNew() || $obj->isColumnModified(RegimeEpisodePeer::IDLE_SAMPLES))
            $columns[RegimeEpisodePeer::IDLE_SAMPLES] = $obj->getIdleSamples();

        if ($obj->isNew() || $obj->isColumnModified(RegimeEpisodePeer::IDLE_ALERTED_AT))
            $columns[RegimeEpisodePeer::IDLE_ALERTED_AT] = $obj->getIdleAlertedAt();

        }

        return BasePeer::doValidate(RegimeEpisodePeer::DATABASE_NAME, RegimeEpisodePeer::TABLE_NAME, $columns);
    }

    /**
     * Retrieve a single object by pkey.
     *
     * @param int $pk the primary key.
     * @param      PropelPDO $con the connection to use
     * @return RegimeEpisode
     */
    public static function retrieveByPK($pk, ?PropelPDO $con = null)
    {

        if (null !== ($obj = RegimeEpisodePeer::getInstanceFromPool((string) $pk))) {
            return $obj;
        }

        if ($con === null) {
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria = new Criteria(RegimeEpisodePeer::DATABASE_NAME);
        $criteria->add(RegimeEpisodePeer::ID_REGIME_EPISODE, $pk);

        $v = RegimeEpisodePeer::doSelect($criteria, $con);

        return !empty($v) > 0 ? $v[0] : null;
    }

    /**
     * Retrieve multiple objects by pkey.
     *
     * @param      array $pks List of primary keys
     * @param      PropelPDO $con the connection to use
     * @return RegimeEpisode[]
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function retrieveByPKs($pks, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $objs = null;
        if (empty($pks)) {
            $objs = array();
        } else {
            $criteria = new Criteria(RegimeEpisodePeer::DATABASE_NAME);
            $criteria->add(RegimeEpisodePeer::ID_REGIME_EPISODE, $pks, Criteria::IN);
            $objs = RegimeEpisodePeer::doSelect($criteria, $con);
        }

        return $objs;
    }

} // BaseRegimeEpisodePeer

// This is the static code needed to register the TableMap for this table with the main Propel class.
//
BaseRegimeEpisodePeer::buildTableMap();

