<?php

namespace App\om;

use \Criteria;
use \Exception;
use \ModelCriteria;
use \ModelJoin;
use \PDO;
use \Propel;
use \PropelCollection;
use \PropelException;
use \PropelObjectCollection;
use \PropelPDO;
use App\Authy;
use App\AuthyGroup;
use App\BotOrder;
use App\BotOrderPeer;
use App\BotOrderQuery;
use App\GridRun;

/**
 * Base class that represents a query for the 'bot_order' table.
 *
 * Order
 *
 * @method BotOrderQuery orderByIdBotOrder($order = Criteria::ASC) Order by the id_bot_order column
 * @method BotOrderQuery orderByIdGridRun($order = Criteria::ASC) Order by the id_grid_run column
 * @method BotOrderQuery orderByClientOrderId($order = Criteria::ASC) Order by the client_order_id column
 * @method BotOrderQuery orderByExchangeOrderId($order = Criteria::ASC) Order by the exchange_order_id column
 * @method BotOrderQuery orderByLevelIdx($order = Criteria::ASC) Order by the level_idx column
 * @method BotOrderQuery orderBySide($order = Criteria::ASC) Order by the side column
 * @method BotOrderQuery orderByState($order = Criteria::ASC) Order by the state column
 * @method BotOrderQuery orderByPrice($order = Criteria::ASC) Order by the price column
 * @method BotOrderQuery orderByQty($order = Criteria::ASC) Order by the qty column
 * @method BotOrderQuery orderByFilledQty($order = Criteria::ASC) Order by the filled_qty column
 * @method BotOrderQuery orderByFeePaid($order = Criteria::ASC) Order by the fee_paid column
 * @method BotOrderQuery orderByFeeAsset($order = Criteria::ASC) Order by the fee_asset column
 * @method BotOrderQuery orderByIsLegacy($order = Criteria::ASC) Order by the is_legacy column
 * @method BotOrderQuery orderByLegacyBuyPrice($order = Criteria::ASC) Order by the legacy_buy_price column
 * @method BotOrderQuery orderByLegacyBuyFee($order = Criteria::ASC) Order by the legacy_buy_fee column
 * @method BotOrderQuery orderBySimulated($order = Criteria::ASC) Order by the simulated column
 * @method BotOrderQuery orderByDateCreation($order = Criteria::ASC) Order by the date_creation column
 * @method BotOrderQuery orderByDateModification($order = Criteria::ASC) Order by the date_modification column
 * @method BotOrderQuery orderByIdGroupCreation($order = Criteria::ASC) Order by the id_group_creation column
 * @method BotOrderQuery orderByIdCreation($order = Criteria::ASC) Order by the id_creation column
 * @method BotOrderQuery orderByIdModification($order = Criteria::ASC) Order by the id_modification column
 *
 * @method BotOrderQuery groupByIdBotOrder() Group by the id_bot_order column
 * @method BotOrderQuery groupByIdGridRun() Group by the id_grid_run column
 * @method BotOrderQuery groupByClientOrderId() Group by the client_order_id column
 * @method BotOrderQuery groupByExchangeOrderId() Group by the exchange_order_id column
 * @method BotOrderQuery groupByLevelIdx() Group by the level_idx column
 * @method BotOrderQuery groupBySide() Group by the side column
 * @method BotOrderQuery groupByState() Group by the state column
 * @method BotOrderQuery groupByPrice() Group by the price column
 * @method BotOrderQuery groupByQty() Group by the qty column
 * @method BotOrderQuery groupByFilledQty() Group by the filled_qty column
 * @method BotOrderQuery groupByFeePaid() Group by the fee_paid column
 * @method BotOrderQuery groupByFeeAsset() Group by the fee_asset column
 * @method BotOrderQuery groupByIsLegacy() Group by the is_legacy column
 * @method BotOrderQuery groupByLegacyBuyPrice() Group by the legacy_buy_price column
 * @method BotOrderQuery groupByLegacyBuyFee() Group by the legacy_buy_fee column
 * @method BotOrderQuery groupBySimulated() Group by the simulated column
 * @method BotOrderQuery groupByDateCreation() Group by the date_creation column
 * @method BotOrderQuery groupByDateModification() Group by the date_modification column
 * @method BotOrderQuery groupByIdGroupCreation() Group by the id_group_creation column
 * @method BotOrderQuery groupByIdCreation() Group by the id_creation column
 * @method BotOrderQuery groupByIdModification() Group by the id_modification column
 *
 * @method BotOrderQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method BotOrderQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method BotOrderQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method BotOrderQuery leftJoinGridRun($relationAlias = null) Adds a LEFT JOIN clause to the query using the GridRun relation
 * @method BotOrderQuery rightJoinGridRun($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GridRun relation
 * @method BotOrderQuery innerJoinGridRun($relationAlias = null) Adds a INNER JOIN clause to the query using the GridRun relation
 *
 * @method BotOrderQuery leftJoinAuthyGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroup relation
 * @method BotOrderQuery rightJoinAuthyGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroup relation
 * @method BotOrderQuery innerJoinAuthyGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroup relation
 *
 * @method BotOrderQuery leftJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method BotOrderQuery rightJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method BotOrderQuery innerJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdCreation relation
 *
 * @method BotOrderQuery leftJoinAuthyRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method BotOrderQuery rightJoinAuthyRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method BotOrderQuery innerJoinAuthyRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdModification relation
 *
 * @method BotOrder findOne(?PropelPDO $con = null) Return the first BotOrder matching the query
 * @method BotOrder findOneOrCreate(?PropelPDO $con = null) Return the first BotOrder matching the query, or a new BotOrder object populated from the query conditions when no match is found
 *
 * @method BotOrder findOneByIdGridRun(int $id_grid_run) Return the first BotOrder filtered by the id_grid_run column
 * @method BotOrder findOneByClientOrderId(string $client_order_id) Return the first BotOrder filtered by the client_order_id column
 * @method BotOrder findOneByExchangeOrderId(string $exchange_order_id) Return the first BotOrder filtered by the exchange_order_id column
 * @method BotOrder findOneByLevelIdx(int $level_idx) Return the first BotOrder filtered by the level_idx column
 * @method BotOrder findOneBySide(int $side) Return the first BotOrder filtered by the side column
 * @method BotOrder findOneByState(int $state) Return the first BotOrder filtered by the state column
 * @method BotOrder findOneByPrice(string $price) Return the first BotOrder filtered by the price column
 * @method BotOrder findOneByQty(string $qty) Return the first BotOrder filtered by the qty column
 * @method BotOrder findOneByFilledQty(string $filled_qty) Return the first BotOrder filtered by the filled_qty column
 * @method BotOrder findOneByFeePaid(string $fee_paid) Return the first BotOrder filtered by the fee_paid column
 * @method BotOrder findOneByFeeAsset(string $fee_asset) Return the first BotOrder filtered by the fee_asset column
 * @method BotOrder findOneByIsLegacy(boolean $is_legacy) Return the first BotOrder filtered by the is_legacy column
 * @method BotOrder findOneByLegacyBuyPrice(string $legacy_buy_price) Return the first BotOrder filtered by the legacy_buy_price column
 * @method BotOrder findOneByLegacyBuyFee(string $legacy_buy_fee) Return the first BotOrder filtered by the legacy_buy_fee column
 * @method BotOrder findOneBySimulated(boolean $simulated) Return the first BotOrder filtered by the simulated column
 * @method BotOrder findOneByDateCreation(string $date_creation) Return the first BotOrder filtered by the date_creation column
 * @method BotOrder findOneByDateModification(string $date_modification) Return the first BotOrder filtered by the date_modification column
 * @method BotOrder findOneByIdGroupCreation(int $id_group_creation) Return the first BotOrder filtered by the id_group_creation column
 * @method BotOrder findOneByIdCreation(int $id_creation) Return the first BotOrder filtered by the id_creation column
 * @method BotOrder findOneByIdModification(int $id_modification) Return the first BotOrder filtered by the id_modification column
 *
 * @method array findByIdBotOrder(int $id_bot_order) Return BotOrder objects filtered by the id_bot_order column
 * @method array findByIdGridRun(int $id_grid_run) Return BotOrder objects filtered by the id_grid_run column
 * @method array findByClientOrderId(string $client_order_id) Return BotOrder objects filtered by the client_order_id column
 * @method array findByExchangeOrderId(string $exchange_order_id) Return BotOrder objects filtered by the exchange_order_id column
 * @method array findByLevelIdx(int $level_idx) Return BotOrder objects filtered by the level_idx column
 * @method array findBySide(int $side) Return BotOrder objects filtered by the side column
 * @method array findByState(int $state) Return BotOrder objects filtered by the state column
 * @method array findByPrice(string $price) Return BotOrder objects filtered by the price column
 * @method array findByQty(string $qty) Return BotOrder objects filtered by the qty column
 * @method array findByFilledQty(string $filled_qty) Return BotOrder objects filtered by the filled_qty column
 * @method array findByFeePaid(string $fee_paid) Return BotOrder objects filtered by the fee_paid column
 * @method array findByFeeAsset(string $fee_asset) Return BotOrder objects filtered by the fee_asset column
 * @method array findByIsLegacy(boolean $is_legacy) Return BotOrder objects filtered by the is_legacy column
 * @method array findByLegacyBuyPrice(string $legacy_buy_price) Return BotOrder objects filtered by the legacy_buy_price column
 * @method array findByLegacyBuyFee(string $legacy_buy_fee) Return BotOrder objects filtered by the legacy_buy_fee column
 * @method array findBySimulated(boolean $simulated) Return BotOrder objects filtered by the simulated column
 * @method array findByDateCreation(string $date_creation) Return BotOrder objects filtered by the date_creation column
 * @method array findByDateModification(string $date_modification) Return BotOrder objects filtered by the date_modification column
 * @method array findByIdGroupCreation(int $id_group_creation) Return BotOrder objects filtered by the id_group_creation column
 * @method array findByIdCreation(int $id_creation) Return BotOrder objects filtered by the id_creation column
 * @method array findByIdModification(int $id_modification) Return BotOrder objects filtered by the id_modification column
 *
 * @package    propel.generator..om
 */
abstract class BaseBotOrderQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseBotOrderQuery object.
     *
     * @param     string $dbName The dabase name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = null, $modelName = null, $modelAlias = null)
    {
        if (null === $dbName) {
            $dbName = 'apigtbot';
        }
        if (null === $modelName) {
            $modelName = 'App\\BotOrder';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new BotOrderQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   BotOrderQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return BotOrderQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof BotOrderQuery) {
            return $criteria;
        }
        $query = new BotOrderQuery(null, null, $modelAlias);

        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * @Query()
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param     PropelPDO $con an optional connection object
     *
     * @return   BotOrder|BotOrder[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = BotOrderPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }
        $this->basePreSelect($con);
        if ($this->formatter || $this->modelAlias || $this->with || $this->select
         || $this->selectColumns || $this->asColumns || $this->selectModifiers
         || $this->map || $this->having || $this->joins) {
            return $this->findPkComplex($key, $con);
        } else {
            return $this->findPkSimple($key, $con);
        }
    }

    /**
     * Alias of findPk to use instance pooling
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return                 BotOrder A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdBotOrder($key, $con = null)
     {
        return $this->findPk($key, $con);
     }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return                 BotOrder A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_bot_order`, `id_grid_run`, `client_order_id`, `exchange_order_id`, `level_idx`, `side`, `state`, `price`, `qty`, `filled_qty`, `fee_paid`, `fee_asset`, `is_legacy`, `legacy_buy_price`, `legacy_buy_fee`, `simulated`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification` FROM `bot_order` WHERE `id_bot_order` = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $obj = new BotOrder();
            $obj->hydrate($row);
            BotOrderPeer::addInstanceToPool($obj, (string) $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * @Query()
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return BotOrder|BotOrder[]|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $stmt = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($stmt);
    }


    /**
     * @Query()
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     PropelPDO $con an optional connection object
     *
     * @return PropelObjectCollection|BotOrder[]|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection($this->getDbName(), Propel::CONNECTION_READ);
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $stmt = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($stmt);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(BotOrderPeer::ID_BOT_ORDER, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(BotOrderPeer::ID_BOT_ORDER, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id_bot_order column
     *
     * Example usage:
     * <code>
     * $query->filterByIdBotOrder(1234); // WHERE id_bot_order = 1234
     * $query->filterByIdBotOrder(array(12, 34)); // WHERE id_bot_order IN (12, 34)
     * $query->filterByIdBotOrder(array('min' => 12)); // WHERE id_bot_order >= 12
     * $query->filterByIdBotOrder(array('max' => 12)); // WHERE id_bot_order <= 12
     * </code>
     *
     * @param     mixed $idBotOrder The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByIdBotOrder($idBotOrder = null, $comparison = null)
    {
        if (is_array($idBotOrder)) {
            $useMinMax = false;
            if (isset($idBotOrder['min'])) {
                $this->addUsingAlias(BotOrderPeer::ID_BOT_ORDER, $idBotOrder['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idBotOrder['max'])) {
                $this->addUsingAlias(BotOrderPeer::ID_BOT_ORDER, $idBotOrder['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::ID_BOT_ORDER, $idBotOrder, $comparison);
    }

    /**
     * Filter the query on the id_grid_run column
     *
     * Example usage:
     * <code>
     * $query->filterByIdGridRun(1234); // WHERE id_grid_run = 1234
     * $query->filterByIdGridRun(array(12, 34)); // WHERE id_grid_run IN (12, 34)
     * $query->filterByIdGridRun(array('min' => 12)); // WHERE id_grid_run >= 12
     * $query->filterByIdGridRun(array('max' => 12)); // WHERE id_grid_run <= 12
     * </code>
     *
     * @see       filterByGridRun()
     *
     * @param     mixed $idGridRun The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByIdGridRun($idGridRun = null, $comparison = null)
    {
        if (is_array($idGridRun)) {
            $useMinMax = false;
            if (isset($idGridRun['min'])) {
                $this->addUsingAlias(BotOrderPeer::ID_GRID_RUN, $idGridRun['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGridRun['max'])) {
                $this->addUsingAlias(BotOrderPeer::ID_GRID_RUN, $idGridRun['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::ID_GRID_RUN, $idGridRun, $comparison);
    }

    /**
     * Filter the query on the client_order_id column
     *
     * Example usage:
     * <code>
     * $query->filterByClientOrderId('fooValue');   // WHERE client_order_id = 'fooValue'
     * $query->filterByClientOrderId('%fooValue%'); // WHERE client_order_id LIKE '%fooValue%'
     * </code>
     *
     * @param     string $clientOrderId The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByClientOrderId($clientOrderId = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($clientOrderId)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $clientOrderId)) {
                $clientOrderId = str_replace('*', '%', $clientOrderId);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::CLIENT_ORDER_ID, $clientOrderId, $comparison);
    }

    /**
     * Filter the query on the exchange_order_id column
     *
     * Example usage:
     * <code>
     * $query->filterByExchangeOrderId('fooValue');   // WHERE exchange_order_id = 'fooValue'
     * $query->filterByExchangeOrderId('%fooValue%'); // WHERE exchange_order_id LIKE '%fooValue%'
     * </code>
     *
     * @param     string $exchangeOrderId The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByExchangeOrderId($exchangeOrderId = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($exchangeOrderId)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $exchangeOrderId)) {
                $exchangeOrderId = str_replace('*', '%', $exchangeOrderId);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::EXCHANGE_ORDER_ID, $exchangeOrderId, $comparison);
    }

    /**
     * Filter the query on the level_idx column
     *
     * Example usage:
     * <code>
     * $query->filterByLevelIdx(1234); // WHERE level_idx = 1234
     * $query->filterByLevelIdx(array(12, 34)); // WHERE level_idx IN (12, 34)
     * $query->filterByLevelIdx(array('min' => 12)); // WHERE level_idx >= 12
     * $query->filterByLevelIdx(array('max' => 12)); // WHERE level_idx <= 12
     * </code>
     *
     * @param     mixed $levelIdx The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByLevelIdx($levelIdx = null, $comparison = null)
    {
        if (is_array($levelIdx)) {
            $useMinMax = false;
            if (isset($levelIdx['min'])) {
                $this->addUsingAlias(BotOrderPeer::LEVEL_IDX, $levelIdx['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($levelIdx['max'])) {
                $this->addUsingAlias(BotOrderPeer::LEVEL_IDX, $levelIdx['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::LEVEL_IDX, $levelIdx, $comparison);
    }

    /**
     * Filter the query on the side column
     *
     * @param     mixed $side The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterBySide($side = null, $comparison = null)
    {
        if (is_scalar($side)) {
            $side = BotOrderPeer::getSqlValueForEnum(BotOrderPeer::SIDE, $side);
        } elseif (is_array($side)) {
            $convertedValues = array();
            foreach ($side as $value) {
                $convertedValues[] = BotOrderPeer::getSqlValueForEnum(BotOrderPeer::SIDE, $value);
            }
            $side = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::SIDE, $side, $comparison);
    }

    /**
     * Filter the query on the state column
     *
     * @param     mixed $state The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByState($state = null, $comparison = null)
    {
        if (is_scalar($state)) {
            $state = BotOrderPeer::getSqlValueForEnum(BotOrderPeer::STATE, $state);
        } elseif (is_array($state)) {
            $convertedValues = array();
            foreach ($state as $value) {
                $convertedValues[] = BotOrderPeer::getSqlValueForEnum(BotOrderPeer::STATE, $value);
            }
            $state = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::STATE, $state, $comparison);
    }

    /**
     * Filter the query on the price column
     *
     * Example usage:
     * <code>
     * $query->filterByPrice(1234); // WHERE price = 1234
     * $query->filterByPrice(array(12, 34)); // WHERE price IN (12, 34)
     * $query->filterByPrice(array('min' => 12)); // WHERE price >= 12
     * $query->filterByPrice(array('max' => 12)); // WHERE price <= 12
     * </code>
     *
     * @param     mixed $price The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByPrice($price = null, $comparison = null)
    {
        if (is_array($price)) {
            $useMinMax = false;
            if (isset($price['min'])) {
                $this->addUsingAlias(BotOrderPeer::PRICE, $price['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($price['max'])) {
                $this->addUsingAlias(BotOrderPeer::PRICE, $price['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::PRICE, $price, $comparison);
    }

    /**
     * Filter the query on the qty column
     *
     * Example usage:
     * <code>
     * $query->filterByQty(1234); // WHERE qty = 1234
     * $query->filterByQty(array(12, 34)); // WHERE qty IN (12, 34)
     * $query->filterByQty(array('min' => 12)); // WHERE qty >= 12
     * $query->filterByQty(array('max' => 12)); // WHERE qty <= 12
     * </code>
     *
     * @param     mixed $qty The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByQty($qty = null, $comparison = null)
    {
        if (is_array($qty)) {
            $useMinMax = false;
            if (isset($qty['min'])) {
                $this->addUsingAlias(BotOrderPeer::QTY, $qty['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($qty['max'])) {
                $this->addUsingAlias(BotOrderPeer::QTY, $qty['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::QTY, $qty, $comparison);
    }

    /**
     * Filter the query on the filled_qty column
     *
     * Example usage:
     * <code>
     * $query->filterByFilledQty(1234); // WHERE filled_qty = 1234
     * $query->filterByFilledQty(array(12, 34)); // WHERE filled_qty IN (12, 34)
     * $query->filterByFilledQty(array('min' => 12)); // WHERE filled_qty >= 12
     * $query->filterByFilledQty(array('max' => 12)); // WHERE filled_qty <= 12
     * </code>
     *
     * @param     mixed $filledQty The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByFilledQty($filledQty = null, $comparison = null)
    {
        if (is_array($filledQty)) {
            $useMinMax = false;
            if (isset($filledQty['min'])) {
                $this->addUsingAlias(BotOrderPeer::FILLED_QTY, $filledQty['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($filledQty['max'])) {
                $this->addUsingAlias(BotOrderPeer::FILLED_QTY, $filledQty['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::FILLED_QTY, $filledQty, $comparison);
    }

    /**
     * Filter the query on the fee_paid column
     *
     * Example usage:
     * <code>
     * $query->filterByFeePaid(1234); // WHERE fee_paid = 1234
     * $query->filterByFeePaid(array(12, 34)); // WHERE fee_paid IN (12, 34)
     * $query->filterByFeePaid(array('min' => 12)); // WHERE fee_paid >= 12
     * $query->filterByFeePaid(array('max' => 12)); // WHERE fee_paid <= 12
     * </code>
     *
     * @param     mixed $feePaid The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByFeePaid($feePaid = null, $comparison = null)
    {
        if (is_array($feePaid)) {
            $useMinMax = false;
            if (isset($feePaid['min'])) {
                $this->addUsingAlias(BotOrderPeer::FEE_PAID, $feePaid['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($feePaid['max'])) {
                $this->addUsingAlias(BotOrderPeer::FEE_PAID, $feePaid['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::FEE_PAID, $feePaid, $comparison);
    }

    /**
     * Filter the query on the fee_asset column
     *
     * Example usage:
     * <code>
     * $query->filterByFeeAsset('fooValue');   // WHERE fee_asset = 'fooValue'
     * $query->filterByFeeAsset('%fooValue%'); // WHERE fee_asset LIKE '%fooValue%'
     * </code>
     *
     * @param     string $feeAsset The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByFeeAsset($feeAsset = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($feeAsset)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $feeAsset)) {
                $feeAsset = str_replace('*', '%', $feeAsset);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::FEE_ASSET, $feeAsset, $comparison);
    }

    /**
     * Filter the query on the is_legacy column
     *
     * Example usage:
     * <code>
     * $query->filterByIsLegacy(true); // WHERE is_legacy = true
     * $query->filterByIsLegacy('yes'); // WHERE is_legacy = true
     * </code>
     *
     * @param     boolean|string $isLegacy The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByIsLegacy($isLegacy = null, $comparison = null)
    {
        if (is_string($isLegacy)) {
            $isLegacy = in_array(strtolower($isLegacy), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(BotOrderPeer::IS_LEGACY, $isLegacy, $comparison);
    }

    /**
     * Filter the query on the legacy_buy_price column
     *
     * Example usage:
     * <code>
     * $query->filterByLegacyBuyPrice(1234); // WHERE legacy_buy_price = 1234
     * $query->filterByLegacyBuyPrice(array(12, 34)); // WHERE legacy_buy_price IN (12, 34)
     * $query->filterByLegacyBuyPrice(array('min' => 12)); // WHERE legacy_buy_price >= 12
     * $query->filterByLegacyBuyPrice(array('max' => 12)); // WHERE legacy_buy_price <= 12
     * </code>
     *
     * @param     mixed $legacyBuyPrice The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByLegacyBuyPrice($legacyBuyPrice = null, $comparison = null)
    {
        if (is_array($legacyBuyPrice)) {
            $useMinMax = false;
            if (isset($legacyBuyPrice['min'])) {
                $this->addUsingAlias(BotOrderPeer::LEGACY_BUY_PRICE, $legacyBuyPrice['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($legacyBuyPrice['max'])) {
                $this->addUsingAlias(BotOrderPeer::LEGACY_BUY_PRICE, $legacyBuyPrice['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::LEGACY_BUY_PRICE, $legacyBuyPrice, $comparison);
    }

    /**
     * Filter the query on the legacy_buy_fee column
     *
     * Example usage:
     * <code>
     * $query->filterByLegacyBuyFee(1234); // WHERE legacy_buy_fee = 1234
     * $query->filterByLegacyBuyFee(array(12, 34)); // WHERE legacy_buy_fee IN (12, 34)
     * $query->filterByLegacyBuyFee(array('min' => 12)); // WHERE legacy_buy_fee >= 12
     * $query->filterByLegacyBuyFee(array('max' => 12)); // WHERE legacy_buy_fee <= 12
     * </code>
     *
     * @param     mixed $legacyBuyFee The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByLegacyBuyFee($legacyBuyFee = null, $comparison = null)
    {
        if (is_array($legacyBuyFee)) {
            $useMinMax = false;
            if (isset($legacyBuyFee['min'])) {
                $this->addUsingAlias(BotOrderPeer::LEGACY_BUY_FEE, $legacyBuyFee['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($legacyBuyFee['max'])) {
                $this->addUsingAlias(BotOrderPeer::LEGACY_BUY_FEE, $legacyBuyFee['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::LEGACY_BUY_FEE, $legacyBuyFee, $comparison);
    }

    /**
     * Filter the query on the simulated column
     *
     * Example usage:
     * <code>
     * $query->filterBySimulated(true); // WHERE simulated = true
     * $query->filterBySimulated('yes'); // WHERE simulated = true
     * </code>
     *
     * @param     boolean|string $simulated The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterBySimulated($simulated = null, $comparison = null)
    {
        if (is_string($simulated)) {
            $simulated = in_array(strtolower($simulated), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(BotOrderPeer::SIMULATED, $simulated, $comparison);
    }

    /**
     * Filter the query on the date_creation column
     *
     * Example usage:
     * <code>
     * $query->filterByDateCreation('2011-03-14'); // WHERE date_creation = '2011-03-14'
     * $query->filterByDateCreation('now'); // WHERE date_creation = '2011-03-14'
     * $query->filterByDateCreation(array('max' => 'yesterday')); // WHERE date_creation < '2011-03-13'
     * </code>
     *
     * @param     mixed $dateCreation The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByDateCreation($dateCreation = null, $comparison = null)
    {
        if (is_array($dateCreation)) {
            $useMinMax = false;
            if (isset($dateCreation['min'])) {
                $this->addUsingAlias(BotOrderPeer::DATE_CREATION, $dateCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateCreation['max'])) {
                $this->addUsingAlias(BotOrderPeer::DATE_CREATION, $dateCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::DATE_CREATION, $dateCreation, $comparison);
    }

    /**
     * Filter the query on the date_modification column
     *
     * Example usage:
     * <code>
     * $query->filterByDateModification('2011-03-14'); // WHERE date_modification = '2011-03-14'
     * $query->filterByDateModification('now'); // WHERE date_modification = '2011-03-14'
     * $query->filterByDateModification(array('max' => 'yesterday')); // WHERE date_modification < '2011-03-13'
     * </code>
     *
     * @param     mixed $dateModification The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByDateModification($dateModification = null, $comparison = null)
    {
        if (is_array($dateModification)) {
            $useMinMax = false;
            if (isset($dateModification['min'])) {
                $this->addUsingAlias(BotOrderPeer::DATE_MODIFICATION, $dateModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateModification['max'])) {
                $this->addUsingAlias(BotOrderPeer::DATE_MODIFICATION, $dateModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::DATE_MODIFICATION, $dateModification, $comparison);
    }

    /**
     * Filter the query on the id_group_creation column
     *
     * Example usage:
     * <code>
     * $query->filterByIdGroupCreation(1234); // WHERE id_group_creation = 1234
     * $query->filterByIdGroupCreation(array(12, 34)); // WHERE id_group_creation IN (12, 34)
     * $query->filterByIdGroupCreation(array('min' => 12)); // WHERE id_group_creation >= 12
     * $query->filterByIdGroupCreation(array('max' => 12)); // WHERE id_group_creation <= 12
     * </code>
     *
     * @see       filterByAuthyGroup()
     *
     * @param     mixed $idGroupCreation The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByIdGroupCreation($idGroupCreation = null, $comparison = null)
    {
        if (is_array($idGroupCreation)) {
            $useMinMax = false;
            if (isset($idGroupCreation['min'])) {
                $this->addUsingAlias(BotOrderPeer::ID_GROUP_CREATION, $idGroupCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGroupCreation['max'])) {
                $this->addUsingAlias(BotOrderPeer::ID_GROUP_CREATION, $idGroupCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::ID_GROUP_CREATION, $idGroupCreation, $comparison);
    }

    /**
     * Filter the query on the id_creation column
     *
     * Example usage:
     * <code>
     * $query->filterByIdCreation(1234); // WHERE id_creation = 1234
     * $query->filterByIdCreation(array(12, 34)); // WHERE id_creation IN (12, 34)
     * $query->filterByIdCreation(array('min' => 12)); // WHERE id_creation >= 12
     * $query->filterByIdCreation(array('max' => 12)); // WHERE id_creation <= 12
     * </code>
     *
     * @see       filterByAuthyRelatedByIdCreation()
     *
     * @param     mixed $idCreation The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByIdCreation($idCreation = null, $comparison = null)
    {
        if (is_array($idCreation)) {
            $useMinMax = false;
            if (isset($idCreation['min'])) {
                $this->addUsingAlias(BotOrderPeer::ID_CREATION, $idCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idCreation['max'])) {
                $this->addUsingAlias(BotOrderPeer::ID_CREATION, $idCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::ID_CREATION, $idCreation, $comparison);
    }

    /**
     * Filter the query on the id_modification column
     *
     * Example usage:
     * <code>
     * $query->filterByIdModification(1234); // WHERE id_modification = 1234
     * $query->filterByIdModification(array(12, 34)); // WHERE id_modification IN (12, 34)
     * $query->filterByIdModification(array('min' => 12)); // WHERE id_modification >= 12
     * $query->filterByIdModification(array('max' => 12)); // WHERE id_modification <= 12
     * </code>
     *
     * @see       filterByAuthyRelatedByIdModification()
     *
     * @param     mixed $idModification The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function filterByIdModification($idModification = null, $comparison = null)
    {
        if (is_array($idModification)) {
            $useMinMax = false;
            if (isset($idModification['min'])) {
                $this->addUsingAlias(BotOrderPeer::ID_MODIFICATION, $idModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idModification['max'])) {
                $this->addUsingAlias(BotOrderPeer::ID_MODIFICATION, $idModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotOrderPeer::ID_MODIFICATION, $idModification, $comparison);
    }

    /**
     * Filter the query by a related GridRun object
     *
     * @param   GridRun|PropelObjectCollection $gridRun The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 BotOrderQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByGridRun($gridRun, $comparison = null)
    {
        if ($gridRun instanceof GridRun) {
            return $this
                ->addUsingAlias(BotOrderPeer::ID_GRID_RUN, $gridRun->getIdGridRun(), $comparison);
        } elseif ($gridRun instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(BotOrderPeer::ID_GRID_RUN, $gridRun->toKeyValue('PrimaryKey', 'IdGridRun'), $comparison);
        } else {
            throw new PropelException('filterByGridRun() only accepts arguments of type GridRun or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the GridRun relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function joinGridRun($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('GridRun');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'GridRun');
        }

        return $this;
    }

    /**
     * Use the GridRun relation GridRun object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\GridRunQuery A secondary query class using the current class as primary query
     */
    public function useGridRunQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinGridRun($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'GridRun', '\App\GridRunQuery');
    }

    /**
     * Filter the query by a related AuthyGroup object
     *
     * @param   AuthyGroup|PropelObjectCollection $authyGroup The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 BotOrderQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroup($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(BotOrderPeer::ID_GROUP_CREATION, $authyGroup->getIdAuthyGroup(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(BotOrderPeer::ID_GROUP_CREATION, $authyGroup->toKeyValue('PrimaryKey', 'IdAuthyGroup'), $comparison);
        } else {
            throw new PropelException('filterByAuthyGroup() only accepts arguments of type AuthyGroup or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyGroup relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function joinAuthyGroup($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyGroup');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AuthyGroup');
        }

        return $this;
    }

    /**
     * Use the AuthyGroup relation AuthyGroup object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyGroupQuery A secondary query class using the current class as primary query
     */
    public function useAuthyGroupQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAuthyGroup($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyGroup', '\App\AuthyGroupQuery');
    }

    /**
     * Filter the query by a related Authy object
     *
     * @param   Authy|PropelObjectCollection $authy The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 BotOrderQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdCreation($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(BotOrderPeer::ID_CREATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(BotOrderPeer::ID_CREATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
        } else {
            throw new PropelException('filterByAuthyRelatedByIdCreation() only accepts arguments of type Authy or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function joinAuthyRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AuthyRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the AuthyRelatedByIdCreation relation Authy object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyQuery A secondary query class using the current class as primary query
     */
    public function useAuthyRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAuthyRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyRelatedByIdCreation', '\App\AuthyQuery');
    }

    /**
     * Filter the query by a related Authy object
     *
     * @param   Authy|PropelObjectCollection $authy The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 BotOrderQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdModification($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(BotOrderPeer::ID_MODIFICATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(BotOrderPeer::ID_MODIFICATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
        } else {
            throw new PropelException('filterByAuthyRelatedByIdModification() only accepts arguments of type Authy or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function joinAuthyRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AuthyRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the AuthyRelatedByIdModification relation Authy object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyQuery A secondary query class using the current class as primary query
     */
    public function useAuthyRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAuthyRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyRelatedByIdModification', '\App\AuthyQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   BotOrder $botOrder Object to remove from the list of results
     *
     * @return BotOrderQuery The current query, for fluid interface
     */
    public function prune($botOrder = null)
    {
        if ($botOrder) {
            $this->addUsingAlias(BotOrderPeer::ID_BOT_ORDER, $botOrder->getIdBotOrder(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Code to execute after every DELETE statement
     *
     * @param     int $affectedRows the number of deleted rows
     * @param     PropelPDO $con The connection object used by the query
     */
    protected function basePostDelete($affectedRows, PropelPDO $con)
    {
        // GoatCheese behavior

                if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                    \ApiGoat\Utility\TableVersion::bump('bot_order');
                }

        return $this->postDelete($affectedRows, $con);
    }

    /**
     * Code to execute after every UPDATE statement
     *
     * @param     int $affectedRows the number of updated rows
     * @param     PropelPDO $con The connection object used by the query
     */
    protected function basePostUpdate($affectedRows, PropelPDO $con)
    {
        // GoatCheese behavior

                if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                    \ApiGoat\Utility\TableVersion::bump('bot_order');
                }

        return $this->postUpdate($affectedRows, $con);
    }

    // add_tablestamp behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     BotOrderQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7){
        return $this->addUsingAlias(BotOrderPeer::DATE_MODIFICATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     BotOrderQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst(){
        return $this->addDescendingOrderByColumn(BotOrderPeer::DATE_MODIFICATION);
    }

    /**
     * Order by update date asc
     *
     * @return     BotOrderQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst(){
        return $this->addAscendingOrderByColumn(BotOrderPeer::DATE_MODIFICATION);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     BotOrderQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7){
        return $this->addUsingAlias(BotOrderPeer::DATE_CREATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return     BotOrderQuery The current query, for fluid interface
     */
    public function lastCreatedFirst(){
        return $this->addDescendingOrderByColumn(BotOrderPeer::DATE_CREATION);
    }

    /**
     * Order by create date asc
     *
     * @return     BotOrderQuery The current query, for fluid interface
     */
    public function firstCreatedFirst(){
        return $this->addAscendingOrderByColumn(BotOrderPeer::DATE_CREATION);
    }
}
