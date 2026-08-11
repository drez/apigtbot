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
use App\BotOrder;
use App\BotOrderPeer;
use App\GridRunPeer;
use App\map\BotOrderTableMap;

/**
 * Base static class for performing query and update operations on the 'bot_order' table.
 *
 * Order
 *
 * @package propel.generator..om
 */
abstract class BaseBotOrderPeer
{

    /** the default database name for this class */
    const DATABASE_NAME = 'apigtbot';

    /** the table name for this class */
    const TABLE_NAME = 'bot_order';

    /** the related Propel class for this table */
    const OM_CLASS = 'App\\BotOrder';

    /** the related TableMap class for this table */
    const TM_CLASS = 'App\\map\\BotOrderTableMap';

    /** The total number of columns. */
    const NUM_COLUMNS = 21;

    /** The number of lazy-loaded columns. */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /** The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS) */
    const NUM_HYDRATE_COLUMNS = 21;

    /** the column name for the id_bot_order field */
    const ID_BOT_ORDER = 'bot_order.id_bot_order';

    /** the column name for the id_grid_run field */
    const ID_GRID_RUN = 'bot_order.id_grid_run';

    /** the column name for the client_order_id field */
    const CLIENT_ORDER_ID = 'bot_order.client_order_id';

    /** the column name for the exchange_order_id field */
    const EXCHANGE_ORDER_ID = 'bot_order.exchange_order_id';

    /** the column name for the level_idx field */
    const LEVEL_IDX = 'bot_order.level_idx';

    /** the column name for the side field */
    const SIDE = 'bot_order.side';

    /** the column name for the state field */
    const STATE = 'bot_order.state';

    /** the column name for the price field */
    const PRICE = 'bot_order.price';

    /** the column name for the qty field */
    const QTY = 'bot_order.qty';

    /** the column name for the filled_qty field */
    const FILLED_QTY = 'bot_order.filled_qty';

    /** the column name for the fee_paid field */
    const FEE_PAID = 'bot_order.fee_paid';

    /** the column name for the fee_asset field */
    const FEE_ASSET = 'bot_order.fee_asset';

    /** the column name for the is_legacy field */
    const IS_LEGACY = 'bot_order.is_legacy';

    /** the column name for the legacy_buy_price field */
    const LEGACY_BUY_PRICE = 'bot_order.legacy_buy_price';

    /** the column name for the legacy_buy_fee field */
    const LEGACY_BUY_FEE = 'bot_order.legacy_buy_fee';

    /** the column name for the simulated field */
    const SIMULATED = 'bot_order.simulated';

    /** the column name for the date_creation field */
    const DATE_CREATION = 'bot_order.date_creation';

    /** the column name for the date_modification field */
    const DATE_MODIFICATION = 'bot_order.date_modification';

    /** the column name for the id_group_creation field */
    const ID_GROUP_CREATION = 'bot_order.id_group_creation';

    /** the column name for the id_creation field */
    const ID_CREATION = 'bot_order.id_creation';

    /** the column name for the id_modification field */
    const ID_MODIFICATION = 'bot_order.id_modification';

    /** The enumerated values for the side field */
    const SIDE_BUY = 'Buy';
    const SIDE_SELL = 'Sell';

    /** The enumerated values for the state field */
    const STATE_INTENDED = 'Intended';
    const STATE_VETOED = 'Vetoed';
    const STATE_BUY_OPEN = 'BUY_OPEN';
    const STATE_SELL_OPEN = 'SELL_OPEN';
    const STATE_FILLED = 'Filled';
    const STATE_PARTFILLED = 'PartFilled';
    const STATE_CANCELED = 'Canceled';
    const STATE_REJECTED = 'Rejected';
    const STATE_LOST = 'Lost';

    /** The default string format for model objects of the related table **/
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * An identity map to hold any loaded instances of BotOrder objects.
     * This must be public so that other peer classes can access this when hydrating from JOIN
     * queries.
     * @var        array BotOrder[]
     */
    public static $instances = array();


    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. BotOrderPeer::$fieldNames[BotOrderPeer::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        BasePeer::TYPE_PHPNAME => array ('IdBotOrder', 'IdGridRun', 'ClientOrderId', 'ExchangeOrderId', 'LevelIdx', 'Side', 'State', 'Price', 'Qty', 'FilledQty', 'FeePaid', 'FeeAsset', 'IsLegacy', 'LegacyBuyPrice', 'LegacyBuyFee', 'Simulated', 'DateCreation', 'DateModification', 'IdGroupCreation', 'IdCreation', 'IdModification', ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('idBotOrder', 'idGridRun', 'clientOrderId', 'exchangeOrderId', 'levelIdx', 'side', 'state', 'price', 'qty', 'filledQty', 'feePaid', 'feeAsset', 'isLegacy', 'legacyBuyPrice', 'legacyBuyFee', 'simulated', 'dateCreation', 'dateModification', 'idGroupCreation', 'idCreation', 'idModification', ),
        BasePeer::TYPE_COLNAME => array (BotOrderPeer::ID_BOT_ORDER, BotOrderPeer::ID_GRID_RUN, BotOrderPeer::CLIENT_ORDER_ID, BotOrderPeer::EXCHANGE_ORDER_ID, BotOrderPeer::LEVEL_IDX, BotOrderPeer::SIDE, BotOrderPeer::STATE, BotOrderPeer::PRICE, BotOrderPeer::QTY, BotOrderPeer::FILLED_QTY, BotOrderPeer::FEE_PAID, BotOrderPeer::FEE_ASSET, BotOrderPeer::IS_LEGACY, BotOrderPeer::LEGACY_BUY_PRICE, BotOrderPeer::LEGACY_BUY_FEE, BotOrderPeer::SIMULATED, BotOrderPeer::DATE_CREATION, BotOrderPeer::DATE_MODIFICATION, BotOrderPeer::ID_GROUP_CREATION, BotOrderPeer::ID_CREATION, BotOrderPeer::ID_MODIFICATION, ),
        BasePeer::TYPE_RAW_COLNAME => array ('ID_BOT_ORDER', 'ID_GRID_RUN', 'CLIENT_ORDER_ID', 'EXCHANGE_ORDER_ID', 'LEVEL_IDX', 'SIDE', 'STATE', 'PRICE', 'QTY', 'FILLED_QTY', 'FEE_PAID', 'FEE_ASSET', 'IS_LEGACY', 'LEGACY_BUY_PRICE', 'LEGACY_BUY_FEE', 'SIMULATED', 'DATE_CREATION', 'DATE_MODIFICATION', 'ID_GROUP_CREATION', 'ID_CREATION', 'ID_MODIFICATION', ),
        BasePeer::TYPE_FIELDNAME => array ('id_bot_order', 'id_grid_run', 'client_order_id', 'exchange_order_id', 'level_idx', 'side', 'state', 'price', 'qty', 'filled_qty', 'fee_paid', 'fee_asset', 'is_legacy', 'legacy_buy_price', 'legacy_buy_fee', 'simulated', 'date_creation', 'date_modification', 'id_group_creation', 'id_creation', 'id_modification', ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. BotOrderPeer::$fieldNames[BasePeer::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        BasePeer::TYPE_PHPNAME => array ('IdBotOrder' => 0, 'IdGridRun' => 1, 'ClientOrderId' => 2, 'ExchangeOrderId' => 3, 'LevelIdx' => 4, 'Side' => 5, 'State' => 6, 'Price' => 7, 'Qty' => 8, 'FilledQty' => 9, 'FeePaid' => 10, 'FeeAsset' => 11, 'IsLegacy' => 12, 'LegacyBuyPrice' => 13, 'LegacyBuyFee' => 14, 'Simulated' => 15, 'DateCreation' => 16, 'DateModification' => 17, 'IdGroupCreation' => 18, 'IdCreation' => 19, 'IdModification' => 20, ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('idBotOrder' => 0, 'idGridRun' => 1, 'clientOrderId' => 2, 'exchangeOrderId' => 3, 'levelIdx' => 4, 'side' => 5, 'state' => 6, 'price' => 7, 'qty' => 8, 'filledQty' => 9, 'feePaid' => 10, 'feeAsset' => 11, 'isLegacy' => 12, 'legacyBuyPrice' => 13, 'legacyBuyFee' => 14, 'simulated' => 15, 'dateCreation' => 16, 'dateModification' => 17, 'idGroupCreation' => 18, 'idCreation' => 19, 'idModification' => 20, ),
        BasePeer::TYPE_COLNAME => array (BotOrderPeer::ID_BOT_ORDER => 0, BotOrderPeer::ID_GRID_RUN => 1, BotOrderPeer::CLIENT_ORDER_ID => 2, BotOrderPeer::EXCHANGE_ORDER_ID => 3, BotOrderPeer::LEVEL_IDX => 4, BotOrderPeer::SIDE => 5, BotOrderPeer::STATE => 6, BotOrderPeer::PRICE => 7, BotOrderPeer::QTY => 8, BotOrderPeer::FILLED_QTY => 9, BotOrderPeer::FEE_PAID => 10, BotOrderPeer::FEE_ASSET => 11, BotOrderPeer::IS_LEGACY => 12, BotOrderPeer::LEGACY_BUY_PRICE => 13, BotOrderPeer::LEGACY_BUY_FEE => 14, BotOrderPeer::SIMULATED => 15, BotOrderPeer::DATE_CREATION => 16, BotOrderPeer::DATE_MODIFICATION => 17, BotOrderPeer::ID_GROUP_CREATION => 18, BotOrderPeer::ID_CREATION => 19, BotOrderPeer::ID_MODIFICATION => 20, ),
        BasePeer::TYPE_RAW_COLNAME => array ('ID_BOT_ORDER' => 0, 'ID_GRID_RUN' => 1, 'CLIENT_ORDER_ID' => 2, 'EXCHANGE_ORDER_ID' => 3, 'LEVEL_IDX' => 4, 'SIDE' => 5, 'STATE' => 6, 'PRICE' => 7, 'QTY' => 8, 'FILLED_QTY' => 9, 'FEE_PAID' => 10, 'FEE_ASSET' => 11, 'IS_LEGACY' => 12, 'LEGACY_BUY_PRICE' => 13, 'LEGACY_BUY_FEE' => 14, 'SIMULATED' => 15, 'DATE_CREATION' => 16, 'DATE_MODIFICATION' => 17, 'ID_GROUP_CREATION' => 18, 'ID_CREATION' => 19, 'ID_MODIFICATION' => 20, ),
        BasePeer::TYPE_FIELDNAME => array ('id_bot_order' => 0, 'id_grid_run' => 1, 'client_order_id' => 2, 'exchange_order_id' => 3, 'level_idx' => 4, 'side' => 5, 'state' => 6, 'price' => 7, 'qty' => 8, 'filled_qty' => 9, 'fee_paid' => 10, 'fee_asset' => 11, 'is_legacy' => 12, 'legacy_buy_price' => 13, 'legacy_buy_fee' => 14, 'simulated' => 15, 'date_creation' => 16, 'date_modification' => 17, 'id_group_creation' => 18, 'id_creation' => 19, 'id_modification' => 20, ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, )
    );

    /** The enumerated values for this table */
    protected static $enumValueSets = array(
        BotOrderPeer::SIDE => array(
            BotOrderPeer::SIDE_BUY,
            BotOrderPeer::SIDE_SELL,
        ),
        BotOrderPeer::STATE => array(
            BotOrderPeer::STATE_INTENDED,
            BotOrderPeer::STATE_VETOED,
            BotOrderPeer::STATE_BUY_OPEN,
            BotOrderPeer::STATE_SELL_OPEN,
            BotOrderPeer::STATE_FILLED,
            BotOrderPeer::STATE_PARTFILLED,
            BotOrderPeer::STATE_CANCELED,
            BotOrderPeer::STATE_REJECTED,
            BotOrderPeer::STATE_LOST,
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
        $toNames = BotOrderPeer::getFieldNames($toType);
        $key = isset(BotOrderPeer::$fieldKeys[$fromType][$name]) ? BotOrderPeer::$fieldKeys[$fromType][$name] : null;
        if ($key === null) {
            throw new PropelException("'$name' could not be found in the field names of type '$fromType'. These are: " . print_r(BotOrderPeer::$fieldKeys[$fromType], true));
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
        if (!array_key_exists($type, BotOrderPeer::$fieldNames)) {
            throw new PropelException('Method getFieldNames() expects the parameter $type to be one of the class constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME, BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM. ' . $type . ' was given.');
        }

        return BotOrderPeer::$fieldNames[$type];
    }

    /**
     * Gets the list of values for all ENUM columns
     * @return array
     */
    public static function getValueSets()
    {
      return BotOrderPeer::$enumValueSets;
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
        $valueSets = BotOrderPeer::getValueSets();

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
        $values = BotOrderPeer::getValueSet($colname);
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
     * @param      string $column The column name for current table. (i.e. BotOrderPeer::COLUMN_NAME).
     * @return string
     */
    public static function alias($alias, $column)
    {
        return str_replace(BotOrderPeer::TABLE_NAME.'.', $alias.'.', $column);
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
            $criteria->addSelectColumn(BotOrderPeer::ID_BOT_ORDER);
            $criteria->addSelectColumn(BotOrderPeer::ID_GRID_RUN);
            $criteria->addSelectColumn(BotOrderPeer::CLIENT_ORDER_ID);
            $criteria->addSelectColumn(BotOrderPeer::EXCHANGE_ORDER_ID);
            $criteria->addSelectColumn(BotOrderPeer::LEVEL_IDX);
            $criteria->addSelectColumn(BotOrderPeer::SIDE);
            $criteria->addSelectColumn(BotOrderPeer::STATE);
            $criteria->addSelectColumn(BotOrderPeer::PRICE);
            $criteria->addSelectColumn(BotOrderPeer::QTY);
            $criteria->addSelectColumn(BotOrderPeer::FILLED_QTY);
            $criteria->addSelectColumn(BotOrderPeer::FEE_PAID);
            $criteria->addSelectColumn(BotOrderPeer::FEE_ASSET);
            $criteria->addSelectColumn(BotOrderPeer::IS_LEGACY);
            $criteria->addSelectColumn(BotOrderPeer::LEGACY_BUY_PRICE);
            $criteria->addSelectColumn(BotOrderPeer::LEGACY_BUY_FEE);
            $criteria->addSelectColumn(BotOrderPeer::SIMULATED);
            $criteria->addSelectColumn(BotOrderPeer::DATE_CREATION);
            $criteria->addSelectColumn(BotOrderPeer::DATE_MODIFICATION);
            $criteria->addSelectColumn(BotOrderPeer::ID_GROUP_CREATION);
            $criteria->addSelectColumn(BotOrderPeer::ID_CREATION);
            $criteria->addSelectColumn(BotOrderPeer::ID_MODIFICATION);
        } else {
            $criteria->addSelectColumn($alias . '.id_bot_order');
            $criteria->addSelectColumn($alias . '.id_grid_run');
            $criteria->addSelectColumn($alias . '.client_order_id');
            $criteria->addSelectColumn($alias . '.exchange_order_id');
            $criteria->addSelectColumn($alias . '.level_idx');
            $criteria->addSelectColumn($alias . '.side');
            $criteria->addSelectColumn($alias . '.state');
            $criteria->addSelectColumn($alias . '.price');
            $criteria->addSelectColumn($alias . '.qty');
            $criteria->addSelectColumn($alias . '.filled_qty');
            $criteria->addSelectColumn($alias . '.fee_paid');
            $criteria->addSelectColumn($alias . '.fee_asset');
            $criteria->addSelectColumn($alias . '.is_legacy');
            $criteria->addSelectColumn($alias . '.legacy_buy_price');
            $criteria->addSelectColumn($alias . '.legacy_buy_fee');
            $criteria->addSelectColumn($alias . '.simulated');
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
        $criteria->setPrimaryTableName(BotOrderPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotOrderPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count
        $criteria->setDbName(BotOrderPeer::DATABASE_NAME); // Set the correct dbName

        if ($con === null) {
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return BotOrder
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectOne(Criteria $criteria, ?PropelPDO $con = null)
    {
        $critcopy = clone $criteria;
        $critcopy->setLimit(1);
        $objects = BotOrderPeer::doSelect($critcopy, $con);
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
        return BotOrderPeer::populateObjects(BotOrderPeer::doSelectStmt($criteria, $con));
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
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        if (!$criteria->hasSelectClause()) {
            $criteria = clone $criteria;
            BotOrderPeer::addSelectColumns($criteria);
        }

        // Set the correct dbName
        $criteria->setDbName(BotOrderPeer::DATABASE_NAME);

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
     * @param BotOrder $obj A BotOrder object.
     * @param      string $key (optional) key to use for instance map (for performance boost if key was already calculated externally).
     */
    public static function addInstanceToPool($obj, $key = null)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if ($key === null) {
                $key = (string) $obj->getIdBotOrder();
            } // if key === null
            BotOrderPeer::$instances[$key] = $obj;
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
     * @param      mixed $value A BotOrder object or a primary key value.
     *
     * @return void
     * @throws PropelException - if the value is invalid.
     */
    public static function removeInstanceFromPool($value)
    {
        if (Propel::isInstancePoolingEnabled() && $value !== null) {
            if (is_object($value) && $value instanceof BotOrder) {
                $key = (string) $value->getIdBotOrder();
            } elseif (is_scalar($value)) {
                // assume we've been passed a primary key
                $key = (string) $value;
            } else {
                $e = new PropelException("Invalid value passed to removeInstanceFromPool().  Expected primary key or BotOrder object; got " . (is_object($value) ? get_class($value) . ' object.' : var_export($value,true)));
                throw $e;
            }

            unset(BotOrderPeer::$instances[$key]);
        }
    } // removeInstanceFromPool()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param      string $key The key (@see getPrimaryKeyHash()) for this instance.
     * @return BotOrder Found object or null if 1) no instance exists for specified key or 2) instance pooling has been disabled.
     * @see        getPrimaryKeyHash()
     */
    public static function getInstanceFromPool($key)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if (isset(BotOrderPeer::$instances[$key])) {
                return BotOrderPeer::$instances[$key];
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
        foreach (BotOrderPeer::$instances as $instance) {
          $instance->clearAllReferences(true);
        }
      }
        BotOrderPeer::$instances = array();
    }

    /**
     * Method to invalidate the instance pool of all tables related to bot_order
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
        $cls = BotOrderPeer::getOMClass();
        // populate the object(s)
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key = BotOrderPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj = BotOrderPeer::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                BotOrderPeer::addInstanceToPool($obj, $key);
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
     * @return array (BotOrder object, last column rank)
     */
    public static function populateObject($row, $startcol = 0)
    {
        $key = BotOrderPeer::getPrimaryKeyHashFromRow($row, $startcol);
        if (null !== ($obj = BotOrderPeer::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $startcol, true); // rehydrate
            $col = $startcol + BotOrderPeer::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = BotOrderPeer::OM_CLASS;
            $obj = new $cls();
            $col = $obj->hydrate($row, $startcol);
            BotOrderPeer::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * Gets the SQL value for Side ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getSideSqlValue($enumVal)
    {
        return BotOrderPeer::getSqlValueForEnum(BotOrderPeer::SIDE, $enumVal);
    }

    /**
     * Gets the SQL value for State ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getStateSqlValue($enumVal)
    {
        return BotOrderPeer::getSqlValueForEnum(BotOrderPeer::STATE, $enumVal);
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
        $criteria->setPrimaryTableName(BotOrderPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotOrderPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(BotOrderPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(BotOrderPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

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
        $criteria->setPrimaryTableName(BotOrderPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotOrderPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(BotOrderPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(BotOrderPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

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
        $criteria->setPrimaryTableName(BotOrderPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotOrderPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(BotOrderPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(BotOrderPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
        $criteria->setPrimaryTableName(BotOrderPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotOrderPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(BotOrderPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(BotOrderPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
     * Selects a collection of BotOrder objects pre-filled with their GridRun objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of BotOrder objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinGridRun(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(BotOrderPeer::DATABASE_NAME);
        }

        BotOrderPeer::addSelectColumns($criteria);
        $startcol = BotOrderPeer::NUM_HYDRATE_COLUMNS;
        GridRunPeer::addSelectColumns($criteria);

        $criteria->addJoin(BotOrderPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = BotOrderPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = BotOrderPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = BotOrderPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                BotOrderPeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (BotOrder) to $obj2 (GridRun)
                $obj2->addBotOrder($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of BotOrder objects pre-filled with their AuthyGroup objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of BotOrder objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyGroup(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(BotOrderPeer::DATABASE_NAME);
        }

        BotOrderPeer::addSelectColumns($criteria);
        $startcol = BotOrderPeer::NUM_HYDRATE_COLUMNS;
        AuthyGroupPeer::addSelectColumns($criteria);

        $criteria->addJoin(BotOrderPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = BotOrderPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = BotOrderPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = BotOrderPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                BotOrderPeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (BotOrder) to $obj2 (AuthyGroup)
                $obj2->addBotOrder($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of BotOrder objects pre-filled with their Authy objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of BotOrder objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyRelatedByIdCreation(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(BotOrderPeer::DATABASE_NAME);
        }

        BotOrderPeer::addSelectColumns($criteria);
        $startcol = BotOrderPeer::NUM_HYDRATE_COLUMNS;
        AuthyPeer::addSelectColumns($criteria);

        $criteria->addJoin(BotOrderPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = BotOrderPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = BotOrderPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = BotOrderPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                BotOrderPeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (BotOrder) to $obj2 (Authy)
                $obj2->addBotOrderRelatedByIdCreation($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of BotOrder objects pre-filled with their Authy objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of BotOrder objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyRelatedByIdModification(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(BotOrderPeer::DATABASE_NAME);
        }

        BotOrderPeer::addSelectColumns($criteria);
        $startcol = BotOrderPeer::NUM_HYDRATE_COLUMNS;
        AuthyPeer::addSelectColumns($criteria);

        $criteria->addJoin(BotOrderPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = BotOrderPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = BotOrderPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = BotOrderPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                BotOrderPeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (BotOrder) to $obj2 (Authy)
                $obj2->addBotOrderRelatedByIdModification($obj1);

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
        $criteria->setPrimaryTableName(BotOrderPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotOrderPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(BotOrderPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(BotOrderPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(BotOrderPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(BotOrderPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(BotOrderPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
     * Selects a collection of BotOrder objects pre-filled with all related objects.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of BotOrder objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAll(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(BotOrderPeer::DATABASE_NAME);
        }

        BotOrderPeer::addSelectColumns($criteria);
        $startcol2 = BotOrderPeer::NUM_HYDRATE_COLUMNS;

        GridRunPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol6 = $startcol5 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(BotOrderPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(BotOrderPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(BotOrderPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(BotOrderPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = BotOrderPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = BotOrderPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = BotOrderPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                BotOrderPeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (BotOrder) to the collection in $obj2 (GridRun)
                $obj2->addBotOrder($obj1);
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

                // Add the $obj1 (BotOrder) to the collection in $obj3 (AuthyGroup)
                $obj3->addBotOrder($obj1);
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

                // Add the $obj1 (BotOrder) to the collection in $obj4 (Authy)
                $obj4->addBotOrderRelatedByIdCreation($obj1);
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

                // Add the $obj1 (BotOrder) to the collection in $obj5 (Authy)
                $obj5->addBotOrderRelatedByIdModification($obj1);
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
        $criteria->setPrimaryTableName(BotOrderPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotOrderPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(BotOrderPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(BotOrderPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(BotOrderPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(BotOrderPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
        $criteria->setPrimaryTableName(BotOrderPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotOrderPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(BotOrderPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(BotOrderPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(BotOrderPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(BotOrderPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

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
        $criteria->setPrimaryTableName(BotOrderPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotOrderPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(BotOrderPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(BotOrderPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(BotOrderPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

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
        $criteria->setPrimaryTableName(BotOrderPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotOrderPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(BotOrderPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(BotOrderPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(BotOrderPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

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
     * Selects a collection of BotOrder objects pre-filled with all related objects except GridRun.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of BotOrder objects.
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
            $criteria->setDbName(BotOrderPeer::DATABASE_NAME);
        }

        BotOrderPeer::addSelectColumns($criteria);
        $startcol2 = BotOrderPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(BotOrderPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(BotOrderPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(BotOrderPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = BotOrderPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = BotOrderPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = BotOrderPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                BotOrderPeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (BotOrder) to the collection in $obj2 (AuthyGroup)
                $obj2->addBotOrder($obj1);

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

                // Add the $obj1 (BotOrder) to the collection in $obj3 (Authy)
                $obj3->addBotOrderRelatedByIdCreation($obj1);

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

                // Add the $obj1 (BotOrder) to the collection in $obj4 (Authy)
                $obj4->addBotOrderRelatedByIdModification($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of BotOrder objects pre-filled with all related objects except AuthyGroup.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of BotOrder objects.
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
            $criteria->setDbName(BotOrderPeer::DATABASE_NAME);
        }

        BotOrderPeer::addSelectColumns($criteria);
        $startcol2 = BotOrderPeer::NUM_HYDRATE_COLUMNS;

        GridRunPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(BotOrderPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(BotOrderPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(BotOrderPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = BotOrderPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = BotOrderPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = BotOrderPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                BotOrderPeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (BotOrder) to the collection in $obj2 (GridRun)
                $obj2->addBotOrder($obj1);

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

                // Add the $obj1 (BotOrder) to the collection in $obj3 (Authy)
                $obj3->addBotOrderRelatedByIdCreation($obj1);

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

                // Add the $obj1 (BotOrder) to the collection in $obj4 (Authy)
                $obj4->addBotOrderRelatedByIdModification($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of BotOrder objects pre-filled with all related objects except AuthyRelatedByIdCreation.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of BotOrder objects.
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
            $criteria->setDbName(BotOrderPeer::DATABASE_NAME);
        }

        BotOrderPeer::addSelectColumns($criteria);
        $startcol2 = BotOrderPeer::NUM_HYDRATE_COLUMNS;

        GridRunPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(BotOrderPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(BotOrderPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = BotOrderPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = BotOrderPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = BotOrderPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                BotOrderPeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (BotOrder) to the collection in $obj2 (GridRun)
                $obj2->addBotOrder($obj1);

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

                // Add the $obj1 (BotOrder) to the collection in $obj3 (AuthyGroup)
                $obj3->addBotOrder($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of BotOrder objects pre-filled with all related objects except AuthyRelatedByIdModification.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of BotOrder objects.
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
            $criteria->setDbName(BotOrderPeer::DATABASE_NAME);
        }

        BotOrderPeer::addSelectColumns($criteria);
        $startcol2 = BotOrderPeer::NUM_HYDRATE_COLUMNS;

        GridRunPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(BotOrderPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(BotOrderPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = BotOrderPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = BotOrderPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = BotOrderPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                BotOrderPeer::addInstanceToPool($obj1, $key1);
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

                // Add the $obj1 (BotOrder) to the collection in $obj2 (GridRun)
                $obj2->addBotOrder($obj1);

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

                // Add the $obj1 (BotOrder) to the collection in $obj3 (AuthyGroup)
                $obj3->addBotOrder($obj1);

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
        return Propel::getDatabaseMap(BotOrderPeer::DATABASE_NAME)->getTable(BotOrderPeer::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this peer class.
     */
    public static function buildTableMap()
    {
      $dbMap = Propel::getDatabaseMap(BaseBotOrderPeer::DATABASE_NAME);
      if (!$dbMap->hasTable(BaseBotOrderPeer::TABLE_NAME)) {
        $dbMap->addTableObject(new \App\map\BotOrderTableMap());
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
        return BotOrderPeer::OM_CLASS;
    }

    /**
     * Performs an INSERT on the database, given a BotOrder or Criteria object.
     *
     * @param      mixed $values Criteria or BotOrder object containing data that is used to create the INSERT statement.
     * @param      PropelPDO $con the PropelPDO connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doInsert($values, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity
        } else {
            $criteria = $values->buildCriteria(); // build Criteria from BotOrder object
        }

        if ($criteria->containsKey(BotOrderPeer::ID_BOT_ORDER) && $criteria->keyContainsValue(BotOrderPeer::ID_BOT_ORDER) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.BotOrderPeer::ID_BOT_ORDER.')');
        }


        // Set the correct dbName
        $criteria->setDbName(BotOrderPeer::DATABASE_NAME);

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
     * Performs an UPDATE on the database, given a BotOrder or Criteria object.
     *
     * @param      mixed $values Criteria or BotOrder object containing data that is used to create the UPDATE statement.
     * @param      PropelPDO $con The connection to use (specify PropelPDO connection object to exert more control over transactions).
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doUpdate($values, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $selectCriteria = new Criteria(BotOrderPeer::DATABASE_NAME);

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity

            $comparison = $criteria->getComparison(BotOrderPeer::ID_BOT_ORDER);
            $value = $criteria->remove(BotOrderPeer::ID_BOT_ORDER);
            if ($value) {
                $selectCriteria->add(BotOrderPeer::ID_BOT_ORDER, $value, $comparison);
            } else {
                $selectCriteria->setPrimaryTableName(BotOrderPeer::TABLE_NAME);
            }

        } else { // $values is BotOrder object
            $criteria = $values->buildCriteria(); // gets full criteria
            $selectCriteria = $values->buildPkeyCriteria(); // gets criteria w/ primary key(s)
        }

        // set the correct dbName
        $criteria->setDbName(BotOrderPeer::DATABASE_NAME);

        return BasePeer::doUpdate($selectCriteria, $criteria, $con);
    }

    /**
     * Deletes all rows from the bot_order table.
     *
     * @param      PropelPDO $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException
     */
    public static function doDeleteAll(?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += BasePeer::doDeleteAll(BotOrderPeer::TABLE_NAME, $con, BotOrderPeer::DATABASE_NAME);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            BotOrderPeer::clearInstancePool();
            BotOrderPeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs a DELETE on the database, given a BotOrder or Criteria object OR a primary key value.
     *
     * @param      mixed $values Criteria or BotOrder object or primary key or array of primary keys
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
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            // invalidate the cache for all objects of this type, since we have no
            // way of knowing (without running a query) what objects should be invalidated
            // from the cache based on this Criteria.
            BotOrderPeer::clearInstancePool();
            // rename for clarity
            $criteria = clone $values;
        } elseif ($values instanceof BotOrder) { // it's a model object
            // invalidate the cache for this single object
            BotOrderPeer::removeInstanceFromPool($values);
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(BotOrderPeer::DATABASE_NAME);
            $criteria->add(BotOrderPeer::ID_BOT_ORDER, (array) $values, Criteria::IN);
            // invalidate the cache for this object(s)
            foreach ((array) $values as $singleval) {
                BotOrderPeer::removeInstanceFromPool($singleval);
            }
        }

        // Set the correct dbName
        $criteria->setDbName(BotOrderPeer::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();

            $affectedRows += BasePeer::doDelete($criteria, $con);
            BotOrderPeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Validates all modified columns of given BotOrder object.
     * If parameter $columns is either a single column name or an array of column names
     * than only those columns are validated.
     *
     * NOTICE: This does not apply to primary or foreign keys for now.
     *
     * @param BotOrder $obj The object to validate.
     * @param      mixed $cols Column name or array of column names.
     *
     * @return mixed TRUE if all columns are valid or the error message of the first invalid column.
     */
    public static function doValidate($obj, $cols = null)
    {
        $columns = array();

        if ($cols) {
            $dbMap = Propel::getDatabaseMap(BotOrderPeer::DATABASE_NAME);
            $tableMap = $dbMap->getTable(BotOrderPeer::TABLE_NAME);

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

        if ($obj->isNew() || $obj->isColumnModified(BotOrderPeer::ID_GRID_RUN))
            $columns[BotOrderPeer::ID_GRID_RUN] = $obj->getIdGridRun();

        if ($obj->isNew() || $obj->isColumnModified(BotOrderPeer::ID_GRID_RUN))
            $columns[BotOrderPeer::ID_GRID_RUN] = $obj->getIdGridRun();

        if ($obj->isNew() || $obj->isColumnModified(BotOrderPeer::CLIENT_ORDER_ID))
            $columns[BotOrderPeer::CLIENT_ORDER_ID] = $obj->getClientOrderId();

        if ($obj->isNew() || $obj->isColumnModified(BotOrderPeer::CLIENT_ORDER_ID))
            $columns[BotOrderPeer::CLIENT_ORDER_ID] = $obj->getClientOrderId();

        if ($obj->isNew() || $obj->isColumnModified(BotOrderPeer::EXCHANGE_ORDER_ID))
            $columns[BotOrderPeer::EXCHANGE_ORDER_ID] = $obj->getExchangeOrderId();

        if ($obj->isNew() || $obj->isColumnModified(BotOrderPeer::LEVEL_IDX))
            $columns[BotOrderPeer::LEVEL_IDX] = $obj->getLevelIdx();

        if ($obj->isNew() || $obj->isColumnModified(BotOrderPeer::LEVEL_IDX))
            $columns[BotOrderPeer::LEVEL_IDX] = $obj->getLevelIdx();

        if ($obj->isNew() || $obj->isColumnModified(BotOrderPeer::SIDE))
            $columns[BotOrderPeer::SIDE] = $obj->getSide();

        if ($obj->isNew() || $obj->isColumnModified(BotOrderPeer::SIDE))
            $columns[BotOrderPeer::SIDE] = $obj->getSide();

        if ($obj->isNew() || $obj->isColumnModified(BotOrderPeer::STATE))
            $columns[BotOrderPeer::STATE] = $obj->getState();

        if ($obj->isNew() || $obj->isColumnModified(BotOrderPeer::STATE))
            $columns[BotOrderPeer::STATE] = $obj->getState();

        if ($obj->isNew() || $obj->isColumnModified(BotOrderPeer::PRICE))
            $columns[BotOrderPeer::PRICE] = $obj->getPrice();

        if ($obj->isNew() || $obj->isColumnModified(BotOrderPeer::QTY))
            $columns[BotOrderPeer::QTY] = $obj->getQty();

        if ($obj->isNew() || $obj->isColumnModified(BotOrderPeer::FEE_ASSET))
            $columns[BotOrderPeer::FEE_ASSET] = $obj->getFeeAsset();

        }

        return BasePeer::doValidate(BotOrderPeer::DATABASE_NAME, BotOrderPeer::TABLE_NAME, $columns);
    }

    /**
     * Retrieve a single object by pkey.
     *
     * @param int $pk the primary key.
     * @param      PropelPDO $con the connection to use
     * @return BotOrder
     */
    public static function retrieveByPK($pk, ?PropelPDO $con = null)
    {

        if (null !== ($obj = BotOrderPeer::getInstanceFromPool((string) $pk))) {
            return $obj;
        }

        if ($con === null) {
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria = new Criteria(BotOrderPeer::DATABASE_NAME);
        $criteria->add(BotOrderPeer::ID_BOT_ORDER, $pk);

        $v = BotOrderPeer::doSelect($criteria, $con);

        return !empty($v) > 0 ? $v[0] : null;
    }

    /**
     * Retrieve multiple objects by pkey.
     *
     * @param      array $pks List of primary keys
     * @param      PropelPDO $con the connection to use
     * @return BotOrder[]
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function retrieveByPKs($pks, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $objs = null;
        if (empty($pks)) {
            $objs = array();
        } else {
            $criteria = new Criteria(BotOrderPeer::DATABASE_NAME);
            $criteria->add(BotOrderPeer::ID_BOT_ORDER, $pks, Criteria::IN);
            $objs = BotOrderPeer::doSelect($criteria, $con);
        }

        return $objs;
    }

} // BaseBotOrderPeer

// This is the static code needed to register the TableMap for this table with the main Propel class.
//
BaseBotOrderPeer::buildTableMap();

