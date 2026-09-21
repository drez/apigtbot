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
use App\MarketOutlook;
use App\MarketOutlookPeer;
use App\map\MarketOutlookTableMap;

/**
 * Base static class for performing query and update operations on the 'market_outlook' table.
 *
 * Market Outlook
 *
 * @package propel.generator..om
 */
abstract class BaseMarketOutlookPeer
{

    /** the default database name for this class */
    const DATABASE_NAME = 'apigtbot';

    /** the table name for this class */
    const TABLE_NAME = 'market_outlook';

    /** the related Propel class for this table */
    const OM_CLASS = 'App\\MarketOutlook';

    /** the related TableMap class for this table */
    const TM_CLASS = 'App\\map\\MarketOutlookTableMap';

    /** The total number of columns. */
    const NUM_COLUMNS = 22;

    /** The number of lazy-loaded columns. */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /** The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS) */
    const NUM_HYDRATE_COLUMNS = 22;

    /** the column name for the id_market_outlook field */
    const ID_MARKET_OUTLOOK = 'market_outlook.id_market_outlook';

    /** the column name for the symbol field */
    const SYMBOL = 'market_outlook.symbol';

    /** the column name for the kind field */
    const KIND = 'market_outlook.kind';

    /** the column name for the verdict field */
    const VERDICT = 'market_outlook.verdict';

    /** the column name for the prev_verdict field */
    const PREV_VERDICT = 'market_outlook.prev_verdict';

    /** the column name for the price_at field */
    const PRICE_AT = 'market_outlook.price_at';

    /** the column name for the called_at field */
    const CALLED_AT = 'market_outlook.called_at';

    /** the column name for the detail field */
    const DETAIL = 'market_outlook.detail';

    /** the column name for the eval_status field */
    const EVAL_STATUS = 'market_outlook.eval_status';

    /** the column name for the price_7d field */
    const PRICE_7D = 'market_outlook.price_7d';

    /** the column name for the price_30d field */
    const PRICE_30D = 'market_outlook.price_30d';

    /** the column name for the ret_7d field */
    const RET_7D = 'market_outlook.ret_7d';

    /** the column name for the ret_30d field */
    const RET_30D = 'market_outlook.ret_30d';

    /** the column name for the max_adverse_pct field */
    const MAX_ADVERSE_PCT = 'market_outlook.max_adverse_pct';

    /** the column name for the hit_7d field */
    const HIT_7D = 'market_outlook.hit_7d';

    /** the column name for the hit_30d field */
    const HIT_30D = 'market_outlook.hit_30d';

    /** the column name for the scored_at field */
    const SCORED_AT = 'market_outlook.scored_at';

    /** the column name for the date_creation field */
    const DATE_CREATION = 'market_outlook.date_creation';

    /** the column name for the date_modification field */
    const DATE_MODIFICATION = 'market_outlook.date_modification';

    /** the column name for the id_group_creation field */
    const ID_GROUP_CREATION = 'market_outlook.id_group_creation';

    /** the column name for the id_creation field */
    const ID_CREATION = 'market_outlook.id_creation';

    /** the column name for the id_modification field */
    const ID_MODIFICATION = 'market_outlook.id_modification';

    /** The default string format for model objects of the related table **/
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * An identity map to hold any loaded instances of MarketOutlook objects.
     * This must be public so that other peer classes can access this when hydrating from JOIN
     * queries.
     * @var        array MarketOutlook[]
     */
    public static $instances = array();


    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. MarketOutlookPeer::$fieldNames[MarketOutlookPeer::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        BasePeer::TYPE_PHPNAME => array ('IdMarketOutlook', 'Symbol', 'Kind', 'Verdict', 'PrevVerdict', 'PriceAt', 'CalledAt', 'Detail', 'EvalStatus', 'Price7d', 'Price30d', 'Ret7d', 'Ret30d', 'MaxAdversePct', 'Hit7d', 'Hit30d', 'ScoredAt', 'DateCreation', 'DateModification', 'IdGroupCreation', 'IdCreation', 'IdModification', ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('idMarketOutlook', 'symbol', 'kind', 'verdict', 'prevVerdict', 'priceAt', 'calledAt', 'detail', 'evalStatus', 'price7d', 'price30d', 'ret7d', 'ret30d', 'maxAdversePct', 'hit7d', 'hit30d', 'scoredAt', 'dateCreation', 'dateModification', 'idGroupCreation', 'idCreation', 'idModification', ),
        BasePeer::TYPE_COLNAME => array (MarketOutlookPeer::ID_MARKET_OUTLOOK, MarketOutlookPeer::SYMBOL, MarketOutlookPeer::KIND, MarketOutlookPeer::VERDICT, MarketOutlookPeer::PREV_VERDICT, MarketOutlookPeer::PRICE_AT, MarketOutlookPeer::CALLED_AT, MarketOutlookPeer::DETAIL, MarketOutlookPeer::EVAL_STATUS, MarketOutlookPeer::PRICE_7D, MarketOutlookPeer::PRICE_30D, MarketOutlookPeer::RET_7D, MarketOutlookPeer::RET_30D, MarketOutlookPeer::MAX_ADVERSE_PCT, MarketOutlookPeer::HIT_7D, MarketOutlookPeer::HIT_30D, MarketOutlookPeer::SCORED_AT, MarketOutlookPeer::DATE_CREATION, MarketOutlookPeer::DATE_MODIFICATION, MarketOutlookPeer::ID_GROUP_CREATION, MarketOutlookPeer::ID_CREATION, MarketOutlookPeer::ID_MODIFICATION, ),
        BasePeer::TYPE_RAW_COLNAME => array ('ID_MARKET_OUTLOOK', 'SYMBOL', 'KIND', 'VERDICT', 'PREV_VERDICT', 'PRICE_AT', 'CALLED_AT', 'DETAIL', 'EVAL_STATUS', 'PRICE_7D', 'PRICE_30D', 'RET_7D', 'RET_30D', 'MAX_ADVERSE_PCT', 'HIT_7D', 'HIT_30D', 'SCORED_AT', 'DATE_CREATION', 'DATE_MODIFICATION', 'ID_GROUP_CREATION', 'ID_CREATION', 'ID_MODIFICATION', ),
        BasePeer::TYPE_FIELDNAME => array ('id_market_outlook', 'symbol', 'kind', 'verdict', 'prev_verdict', 'price_at', 'called_at', 'detail', 'eval_status', 'price_7d', 'price_30d', 'ret_7d', 'ret_30d', 'max_adverse_pct', 'hit_7d', 'hit_30d', 'scored_at', 'date_creation', 'date_modification', 'id_group_creation', 'id_creation', 'id_modification', ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. MarketOutlookPeer::$fieldNames[BasePeer::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        BasePeer::TYPE_PHPNAME => array ('IdMarketOutlook' => 0, 'Symbol' => 1, 'Kind' => 2, 'Verdict' => 3, 'PrevVerdict' => 4, 'PriceAt' => 5, 'CalledAt' => 6, 'Detail' => 7, 'EvalStatus' => 8, 'Price7d' => 9, 'Price30d' => 10, 'Ret7d' => 11, 'Ret30d' => 12, 'MaxAdversePct' => 13, 'Hit7d' => 14, 'Hit30d' => 15, 'ScoredAt' => 16, 'DateCreation' => 17, 'DateModification' => 18, 'IdGroupCreation' => 19, 'IdCreation' => 20, 'IdModification' => 21, ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('idMarketOutlook' => 0, 'symbol' => 1, 'kind' => 2, 'verdict' => 3, 'prevVerdict' => 4, 'priceAt' => 5, 'calledAt' => 6, 'detail' => 7, 'evalStatus' => 8, 'price7d' => 9, 'price30d' => 10, 'ret7d' => 11, 'ret30d' => 12, 'maxAdversePct' => 13, 'hit7d' => 14, 'hit30d' => 15, 'scoredAt' => 16, 'dateCreation' => 17, 'dateModification' => 18, 'idGroupCreation' => 19, 'idCreation' => 20, 'idModification' => 21, ),
        BasePeer::TYPE_COLNAME => array (MarketOutlookPeer::ID_MARKET_OUTLOOK => 0, MarketOutlookPeer::SYMBOL => 1, MarketOutlookPeer::KIND => 2, MarketOutlookPeer::VERDICT => 3, MarketOutlookPeer::PREV_VERDICT => 4, MarketOutlookPeer::PRICE_AT => 5, MarketOutlookPeer::CALLED_AT => 6, MarketOutlookPeer::DETAIL => 7, MarketOutlookPeer::EVAL_STATUS => 8, MarketOutlookPeer::PRICE_7D => 9, MarketOutlookPeer::PRICE_30D => 10, MarketOutlookPeer::RET_7D => 11, MarketOutlookPeer::RET_30D => 12, MarketOutlookPeer::MAX_ADVERSE_PCT => 13, MarketOutlookPeer::HIT_7D => 14, MarketOutlookPeer::HIT_30D => 15, MarketOutlookPeer::SCORED_AT => 16, MarketOutlookPeer::DATE_CREATION => 17, MarketOutlookPeer::DATE_MODIFICATION => 18, MarketOutlookPeer::ID_GROUP_CREATION => 19, MarketOutlookPeer::ID_CREATION => 20, MarketOutlookPeer::ID_MODIFICATION => 21, ),
        BasePeer::TYPE_RAW_COLNAME => array ('ID_MARKET_OUTLOOK' => 0, 'SYMBOL' => 1, 'KIND' => 2, 'VERDICT' => 3, 'PREV_VERDICT' => 4, 'PRICE_AT' => 5, 'CALLED_AT' => 6, 'DETAIL' => 7, 'EVAL_STATUS' => 8, 'PRICE_7D' => 9, 'PRICE_30D' => 10, 'RET_7D' => 11, 'RET_30D' => 12, 'MAX_ADVERSE_PCT' => 13, 'HIT_7D' => 14, 'HIT_30D' => 15, 'SCORED_AT' => 16, 'DATE_CREATION' => 17, 'DATE_MODIFICATION' => 18, 'ID_GROUP_CREATION' => 19, 'ID_CREATION' => 20, 'ID_MODIFICATION' => 21, ),
        BasePeer::TYPE_FIELDNAME => array ('id_market_outlook' => 0, 'symbol' => 1, 'kind' => 2, 'verdict' => 3, 'prev_verdict' => 4, 'price_at' => 5, 'called_at' => 6, 'detail' => 7, 'eval_status' => 8, 'price_7d' => 9, 'price_30d' => 10, 'ret_7d' => 11, 'ret_30d' => 12, 'max_adverse_pct' => 13, 'hit_7d' => 14, 'hit_30d' => 15, 'scored_at' => 16, 'date_creation' => 17, 'date_modification' => 18, 'id_group_creation' => 19, 'id_creation' => 20, 'id_modification' => 21, ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, )
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
        $toNames = MarketOutlookPeer::getFieldNames($toType);
        $key = isset(MarketOutlookPeer::$fieldKeys[$fromType][$name]) ? MarketOutlookPeer::$fieldKeys[$fromType][$name] : null;
        if ($key === null) {
            throw new PropelException("'$name' could not be found in the field names of type '$fromType'. These are: " . print_r(MarketOutlookPeer::$fieldKeys[$fromType], true));
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
        if (!array_key_exists($type, MarketOutlookPeer::$fieldNames)) {
            throw new PropelException('Method getFieldNames() expects the parameter $type to be one of the class constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME, BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM. ' . $type . ' was given.');
        }

        return MarketOutlookPeer::$fieldNames[$type];
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
     * @param      string $column The column name for current table. (i.e. MarketOutlookPeer::COLUMN_NAME).
     * @return string
     */
    public static function alias($alias, $column)
    {
        return str_replace(MarketOutlookPeer::TABLE_NAME.'.', $alias.'.', $column);
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
            $criteria->addSelectColumn(MarketOutlookPeer::ID_MARKET_OUTLOOK);
            $criteria->addSelectColumn(MarketOutlookPeer::SYMBOL);
            $criteria->addSelectColumn(MarketOutlookPeer::KIND);
            $criteria->addSelectColumn(MarketOutlookPeer::VERDICT);
            $criteria->addSelectColumn(MarketOutlookPeer::PREV_VERDICT);
            $criteria->addSelectColumn(MarketOutlookPeer::PRICE_AT);
            $criteria->addSelectColumn(MarketOutlookPeer::CALLED_AT);
            $criteria->addSelectColumn(MarketOutlookPeer::DETAIL);
            $criteria->addSelectColumn(MarketOutlookPeer::EVAL_STATUS);
            $criteria->addSelectColumn(MarketOutlookPeer::PRICE_7D);
            $criteria->addSelectColumn(MarketOutlookPeer::PRICE_30D);
            $criteria->addSelectColumn(MarketOutlookPeer::RET_7D);
            $criteria->addSelectColumn(MarketOutlookPeer::RET_30D);
            $criteria->addSelectColumn(MarketOutlookPeer::MAX_ADVERSE_PCT);
            $criteria->addSelectColumn(MarketOutlookPeer::HIT_7D);
            $criteria->addSelectColumn(MarketOutlookPeer::HIT_30D);
            $criteria->addSelectColumn(MarketOutlookPeer::SCORED_AT);
            $criteria->addSelectColumn(MarketOutlookPeer::DATE_CREATION);
            $criteria->addSelectColumn(MarketOutlookPeer::DATE_MODIFICATION);
            $criteria->addSelectColumn(MarketOutlookPeer::ID_GROUP_CREATION);
            $criteria->addSelectColumn(MarketOutlookPeer::ID_CREATION);
            $criteria->addSelectColumn(MarketOutlookPeer::ID_MODIFICATION);
        } else {
            $criteria->addSelectColumn($alias . '.id_market_outlook');
            $criteria->addSelectColumn($alias . '.symbol');
            $criteria->addSelectColumn($alias . '.kind');
            $criteria->addSelectColumn($alias . '.verdict');
            $criteria->addSelectColumn($alias . '.prev_verdict');
            $criteria->addSelectColumn($alias . '.price_at');
            $criteria->addSelectColumn($alias . '.called_at');
            $criteria->addSelectColumn($alias . '.detail');
            $criteria->addSelectColumn($alias . '.eval_status');
            $criteria->addSelectColumn($alias . '.price_7d');
            $criteria->addSelectColumn($alias . '.price_30d');
            $criteria->addSelectColumn($alias . '.ret_7d');
            $criteria->addSelectColumn($alias . '.ret_30d');
            $criteria->addSelectColumn($alias . '.max_adverse_pct');
            $criteria->addSelectColumn($alias . '.hit_7d');
            $criteria->addSelectColumn($alias . '.hit_30d');
            $criteria->addSelectColumn($alias . '.scored_at');
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
        $criteria->setPrimaryTableName(MarketOutlookPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            MarketOutlookPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count
        $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME); // Set the correct dbName

        if ($con === null) {
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return MarketOutlook
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectOne(Criteria $criteria, ?PropelPDO $con = null)
    {
        $critcopy = clone $criteria;
        $critcopy->setLimit(1);
        $objects = MarketOutlookPeer::doSelect($critcopy, $con);
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
        return MarketOutlookPeer::populateObjects(MarketOutlookPeer::doSelectStmt($criteria, $con));
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
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        if (!$criteria->hasSelectClause()) {
            $criteria = clone $criteria;
            MarketOutlookPeer::addSelectColumns($criteria);
        }

        // Set the correct dbName
        $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME);

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
     * @param MarketOutlook $obj A MarketOutlook object.
     * @param      string $key (optional) key to use for instance map (for performance boost if key was already calculated externally).
     */
    public static function addInstanceToPool($obj, $key = null)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if ($key === null) {
                $key = (string) $obj->getIdMarketOutlook();
            } // if key === null
            MarketOutlookPeer::$instances[$key] = $obj;
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
     * @param      mixed $value A MarketOutlook object or a primary key value.
     *
     * @return void
     * @throws PropelException - if the value is invalid.
     */
    public static function removeInstanceFromPool($value)
    {
        if (Propel::isInstancePoolingEnabled() && $value !== null) {
            if (is_object($value) && $value instanceof MarketOutlook) {
                $key = (string) $value->getIdMarketOutlook();
            } elseif (is_scalar($value)) {
                // assume we've been passed a primary key
                $key = (string) $value;
            } else {
                $e = new PropelException("Invalid value passed to removeInstanceFromPool().  Expected primary key or MarketOutlook object; got " . (is_object($value) ? get_class($value) . ' object.' : var_export($value,true)));
                throw $e;
            }

            unset(MarketOutlookPeer::$instances[$key]);
        }
    } // removeInstanceFromPool()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param      string $key The key (@see getPrimaryKeyHash()) for this instance.
     * @return MarketOutlook Found object or null if 1) no instance exists for specified key or 2) instance pooling has been disabled.
     * @see        getPrimaryKeyHash()
     */
    public static function getInstanceFromPool($key)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if (isset(MarketOutlookPeer::$instances[$key])) {
                return MarketOutlookPeer::$instances[$key];
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
        foreach (MarketOutlookPeer::$instances as $instance) {
          $instance->clearAllReferences(true);
        }
      }
        MarketOutlookPeer::$instances = array();
    }

    /**
     * Method to invalidate the instance pool of all tables related to market_outlook
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
        $cls = MarketOutlookPeer::getOMClass();
        // populate the object(s)
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key = MarketOutlookPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj = MarketOutlookPeer::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                MarketOutlookPeer::addInstanceToPool($obj, $key);
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
     * @return array (MarketOutlook object, last column rank)
     */
    public static function populateObject($row, $startcol = 0)
    {
        $key = MarketOutlookPeer::getPrimaryKeyHashFromRow($row, $startcol);
        if (null !== ($obj = MarketOutlookPeer::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $startcol, true); // rehydrate
            $col = $startcol + MarketOutlookPeer::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = MarketOutlookPeer::OM_CLASS;
            $obj = new $cls();
            $col = $obj->hydrate($row, $startcol);
            MarketOutlookPeer::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
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
        $criteria->setPrimaryTableName(MarketOutlookPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            MarketOutlookPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(MarketOutlookPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

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
        $criteria->setPrimaryTableName(MarketOutlookPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            MarketOutlookPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(MarketOutlookPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
        $criteria->setPrimaryTableName(MarketOutlookPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            MarketOutlookPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(MarketOutlookPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
     * Selects a collection of MarketOutlook objects pre-filled with their AuthyGroup objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of MarketOutlook objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyGroup(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME);
        }

        MarketOutlookPeer::addSelectColumns($criteria);
        $startcol = MarketOutlookPeer::NUM_HYDRATE_COLUMNS;
        AuthyGroupPeer::addSelectColumns($criteria);

        $criteria->addJoin(MarketOutlookPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = MarketOutlookPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = MarketOutlookPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = MarketOutlookPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                MarketOutlookPeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (MarketOutlook) to $obj2 (AuthyGroup)
                $obj2->addMarketOutlook($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of MarketOutlook objects pre-filled with their Authy objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of MarketOutlook objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyRelatedByIdCreation(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME);
        }

        MarketOutlookPeer::addSelectColumns($criteria);
        $startcol = MarketOutlookPeer::NUM_HYDRATE_COLUMNS;
        AuthyPeer::addSelectColumns($criteria);

        $criteria->addJoin(MarketOutlookPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = MarketOutlookPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = MarketOutlookPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = MarketOutlookPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                MarketOutlookPeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (MarketOutlook) to $obj2 (Authy)
                $obj2->addMarketOutlookRelatedByIdCreation($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of MarketOutlook objects pre-filled with their Authy objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of MarketOutlook objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyRelatedByIdModification(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME);
        }

        MarketOutlookPeer::addSelectColumns($criteria);
        $startcol = MarketOutlookPeer::NUM_HYDRATE_COLUMNS;
        AuthyPeer::addSelectColumns($criteria);

        $criteria->addJoin(MarketOutlookPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = MarketOutlookPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = MarketOutlookPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = MarketOutlookPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                MarketOutlookPeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (MarketOutlook) to $obj2 (Authy)
                $obj2->addMarketOutlookRelatedByIdModification($obj1);

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
        $criteria->setPrimaryTableName(MarketOutlookPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            MarketOutlookPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(MarketOutlookPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(MarketOutlookPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(MarketOutlookPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
     * Selects a collection of MarketOutlook objects pre-filled with all related objects.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of MarketOutlook objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAll(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME);
        }

        MarketOutlookPeer::addSelectColumns($criteria);
        $startcol2 = MarketOutlookPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(MarketOutlookPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(MarketOutlookPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(MarketOutlookPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = MarketOutlookPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = MarketOutlookPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = MarketOutlookPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                MarketOutlookPeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (MarketOutlook) to the collection in $obj2 (AuthyGroup)
                $obj2->addMarketOutlook($obj1);
            } // if joined row not null

            // Add objects for joined Authy rows

            $key3 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol3);
            if ($key3 !== null) {
                $obj3 = AuthyPeer::getInstanceFromPool($key3);
                if (!$obj3) {

                    $cls = AuthyPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    AuthyPeer::addInstanceToPool($obj3, $key3);
                } // if obj3 loaded

                // Add the $obj1 (MarketOutlook) to the collection in $obj3 (Authy)
                $obj3->addMarketOutlookRelatedByIdCreation($obj1);
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

                // Add the $obj1 (MarketOutlook) to the collection in $obj4 (Authy)
                $obj4->addMarketOutlookRelatedByIdModification($obj1);
            } // if joined row not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
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
        $criteria->setPrimaryTableName(MarketOutlookPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            MarketOutlookPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(MarketOutlookPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(MarketOutlookPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
        $criteria->setPrimaryTableName(MarketOutlookPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            MarketOutlookPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(MarketOutlookPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

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
        $criteria->setPrimaryTableName(MarketOutlookPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            MarketOutlookPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(MarketOutlookPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

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
     * Selects a collection of MarketOutlook objects pre-filled with all related objects except AuthyGroup.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of MarketOutlook objects.
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
            $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME);
        }

        MarketOutlookPeer::addSelectColumns($criteria);
        $startcol2 = MarketOutlookPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(MarketOutlookPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(MarketOutlookPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = MarketOutlookPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = MarketOutlookPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = MarketOutlookPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                MarketOutlookPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined Authy rows

                $key2 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = AuthyPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = AuthyPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    AuthyPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (MarketOutlook) to the collection in $obj2 (Authy)
                $obj2->addMarketOutlookRelatedByIdCreation($obj1);

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

                // Add the $obj1 (MarketOutlook) to the collection in $obj3 (Authy)
                $obj3->addMarketOutlookRelatedByIdModification($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of MarketOutlook objects pre-filled with all related objects except AuthyRelatedByIdCreation.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of MarketOutlook objects.
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
            $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME);
        }

        MarketOutlookPeer::addSelectColumns($criteria);
        $startcol2 = MarketOutlookPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(MarketOutlookPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = MarketOutlookPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = MarketOutlookPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = MarketOutlookPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                MarketOutlookPeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (MarketOutlook) to the collection in $obj2 (AuthyGroup)
                $obj2->addMarketOutlook($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of MarketOutlook objects pre-filled with all related objects except AuthyRelatedByIdModification.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of MarketOutlook objects.
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
            $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME);
        }

        MarketOutlookPeer::addSelectColumns($criteria);
        $startcol2 = MarketOutlookPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(MarketOutlookPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = MarketOutlookPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = MarketOutlookPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = MarketOutlookPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                MarketOutlookPeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (MarketOutlook) to the collection in $obj2 (AuthyGroup)
                $obj2->addMarketOutlook($obj1);

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
        return Propel::getDatabaseMap(MarketOutlookPeer::DATABASE_NAME)->getTable(MarketOutlookPeer::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this peer class.
     */
    public static function buildTableMap()
    {
      $dbMap = Propel::getDatabaseMap(BaseMarketOutlookPeer::DATABASE_NAME);
      if (!$dbMap->hasTable(BaseMarketOutlookPeer::TABLE_NAME)) {
        $dbMap->addTableObject(new \App\map\MarketOutlookTableMap());
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
        return MarketOutlookPeer::OM_CLASS;
    }

    /**
     * Performs an INSERT on the database, given a MarketOutlook or Criteria object.
     *
     * @param      mixed $values Criteria or MarketOutlook object containing data that is used to create the INSERT statement.
     * @param      PropelPDO $con the PropelPDO connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doInsert($values, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity
        } else {
            $criteria = $values->buildCriteria(); // build Criteria from MarketOutlook object
        }

        if ($criteria->containsKey(MarketOutlookPeer::ID_MARKET_OUTLOOK) && $criteria->keyContainsValue(MarketOutlookPeer::ID_MARKET_OUTLOOK) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.MarketOutlookPeer::ID_MARKET_OUTLOOK.')');
        }


        // Set the correct dbName
        $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME);

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
     * Performs an UPDATE on the database, given a MarketOutlook or Criteria object.
     *
     * @param      mixed $values Criteria or MarketOutlook object containing data that is used to create the UPDATE statement.
     * @param      PropelPDO $con The connection to use (specify PropelPDO connection object to exert more control over transactions).
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doUpdate($values, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $selectCriteria = new Criteria(MarketOutlookPeer::DATABASE_NAME);

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity

            $comparison = $criteria->getComparison(MarketOutlookPeer::ID_MARKET_OUTLOOK);
            $value = $criteria->remove(MarketOutlookPeer::ID_MARKET_OUTLOOK);
            if ($value) {
                $selectCriteria->add(MarketOutlookPeer::ID_MARKET_OUTLOOK, $value, $comparison);
            } else {
                $selectCriteria->setPrimaryTableName(MarketOutlookPeer::TABLE_NAME);
            }

        } else { // $values is MarketOutlook object
            $criteria = $values->buildCriteria(); // gets full criteria
            $selectCriteria = $values->buildPkeyCriteria(); // gets criteria w/ primary key(s)
        }

        // set the correct dbName
        $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME);

        return BasePeer::doUpdate($selectCriteria, $criteria, $con);
    }

    /**
     * Deletes all rows from the market_outlook table.
     *
     * @param      PropelPDO $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException
     */
    public static function doDeleteAll(?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += BasePeer::doDeleteAll(MarketOutlookPeer::TABLE_NAME, $con, MarketOutlookPeer::DATABASE_NAME);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            MarketOutlookPeer::clearInstancePool();
            MarketOutlookPeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs a DELETE on the database, given a MarketOutlook or Criteria object OR a primary key value.
     *
     * @param      mixed $values Criteria or MarketOutlook object or primary key or array of primary keys
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
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            // invalidate the cache for all objects of this type, since we have no
            // way of knowing (without running a query) what objects should be invalidated
            // from the cache based on this Criteria.
            MarketOutlookPeer::clearInstancePool();
            // rename for clarity
            $criteria = clone $values;
        } elseif ($values instanceof MarketOutlook) { // it's a model object
            // invalidate the cache for this single object
            MarketOutlookPeer::removeInstanceFromPool($values);
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(MarketOutlookPeer::DATABASE_NAME);
            $criteria->add(MarketOutlookPeer::ID_MARKET_OUTLOOK, (array) $values, Criteria::IN);
            // invalidate the cache for this object(s)
            foreach ((array) $values as $singleval) {
                MarketOutlookPeer::removeInstanceFromPool($singleval);
            }
        }

        // Set the correct dbName
        $criteria->setDbName(MarketOutlookPeer::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();

            $affectedRows += BasePeer::doDelete($criteria, $con);
            MarketOutlookPeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Validates all modified columns of given MarketOutlook object.
     * If parameter $columns is either a single column name or an array of column names
     * than only those columns are validated.
     *
     * NOTICE: This does not apply to primary or foreign keys for now.
     *
     * @param MarketOutlook $obj The object to validate.
     * @param      mixed $cols Column name or array of column names.
     *
     * @return mixed TRUE if all columns are valid or the error message of the first invalid column.
     */
    public static function doValidate($obj, $cols = null)
    {
        $columns = array();

        if ($cols) {
            $dbMap = Propel::getDatabaseMap(MarketOutlookPeer::DATABASE_NAME);
            $tableMap = $dbMap->getTable(MarketOutlookPeer::TABLE_NAME);

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

        if ($obj->isNew() || $obj->isColumnModified(MarketOutlookPeer::SYMBOL))
            $columns[MarketOutlookPeer::SYMBOL] = $obj->getSymbol();

        if ($obj->isNew() || $obj->isColumnModified(MarketOutlookPeer::SYMBOL))
            $columns[MarketOutlookPeer::SYMBOL] = $obj->getSymbol();

        if ($obj->isNew() || $obj->isColumnModified(MarketOutlookPeer::KIND))
            $columns[MarketOutlookPeer::KIND] = $obj->getKind();

        if ($obj->isNew() || $obj->isColumnModified(MarketOutlookPeer::KIND))
            $columns[MarketOutlookPeer::KIND] = $obj->getKind();

        if ($obj->isNew() || $obj->isColumnModified(MarketOutlookPeer::VERDICT))
            $columns[MarketOutlookPeer::VERDICT] = $obj->getVerdict();

        if ($obj->isNew() || $obj->isColumnModified(MarketOutlookPeer::VERDICT))
            $columns[MarketOutlookPeer::VERDICT] = $obj->getVerdict();

        if ($obj->isNew() || $obj->isColumnModified(MarketOutlookPeer::PREV_VERDICT))
            $columns[MarketOutlookPeer::PREV_VERDICT] = $obj->getPrevVerdict();

        if ($obj->isNew() || $obj->isColumnModified(MarketOutlookPeer::PRICE_AT))
            $columns[MarketOutlookPeer::PRICE_AT] = $obj->getPriceAt();

        if ($obj->isNew() || $obj->isColumnModified(MarketOutlookPeer::CALLED_AT))
            $columns[MarketOutlookPeer::CALLED_AT] = $obj->getCalledAt();

        if ($obj->isNew() || $obj->isColumnModified(MarketOutlookPeer::CALLED_AT))
            $columns[MarketOutlookPeer::CALLED_AT] = $obj->getCalledAt();

        if ($obj->isNew() || $obj->isColumnModified(MarketOutlookPeer::DETAIL))
            $columns[MarketOutlookPeer::DETAIL] = $obj->getDetail();

        if ($obj->isNew() || $obj->isColumnModified(MarketOutlookPeer::EVAL_STATUS))
            $columns[MarketOutlookPeer::EVAL_STATUS] = $obj->getEvalStatus();

        if ($obj->isNew() || $obj->isColumnModified(MarketOutlookPeer::EVAL_STATUS))
            $columns[MarketOutlookPeer::EVAL_STATUS] = $obj->getEvalStatus();

        if ($obj->isNew() || $obj->isColumnModified(MarketOutlookPeer::SCORED_AT))
            $columns[MarketOutlookPeer::SCORED_AT] = $obj->getScoredAt();

        }

        return BasePeer::doValidate(MarketOutlookPeer::DATABASE_NAME, MarketOutlookPeer::TABLE_NAME, $columns);
    }

    /**
     * Retrieve a single object by pkey.
     *
     * @param int $pk the primary key.
     * @param      PropelPDO $con the connection to use
     * @return MarketOutlook
     */
    public static function retrieveByPK($pk, ?PropelPDO $con = null)
    {

        if (null !== ($obj = MarketOutlookPeer::getInstanceFromPool((string) $pk))) {
            return $obj;
        }

        if ($con === null) {
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria = new Criteria(MarketOutlookPeer::DATABASE_NAME);
        $criteria->add(MarketOutlookPeer::ID_MARKET_OUTLOOK, $pk);

        $v = MarketOutlookPeer::doSelect($criteria, $con);

        return !empty($v) > 0 ? $v[0] : null;
    }

    /**
     * Retrieve multiple objects by pkey.
     *
     * @param      array $pks List of primary keys
     * @param      PropelPDO $con the connection to use
     * @return MarketOutlook[]
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function retrieveByPKs($pks, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $objs = null;
        if (empty($pks)) {
            $objs = array();
        } else {
            $criteria = new Criteria(MarketOutlookPeer::DATABASE_NAME);
            $criteria->add(MarketOutlookPeer::ID_MARKET_OUTLOOK, $pks, Criteria::IN);
            $objs = MarketOutlookPeer::doSelect($criteria, $con);
        }

        return $objs;
    }

} // BaseMarketOutlookPeer

// This is the static code needed to register the TableMap for this table with the main Propel class.
//
BaseMarketOutlookPeer::buildTableMap();

