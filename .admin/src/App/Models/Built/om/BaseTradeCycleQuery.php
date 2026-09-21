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
use App\GridRun;
use App\TradeCycle;
use App\TradeCyclePeer;
use App\TradeCycleQuery;

/**
 * Base class that represents a query for the 'trade_cycle' table.
 *
 * Trade Cycle
 *
 * @method TradeCycleQuery orderByIdTradeCycle($order = Criteria::ASC) Order by the id_trade_cycle column
 * @method TradeCycleQuery orderByIdGridRun($order = Criteria::ASC) Order by the id_grid_run column
 * @method TradeCycleQuery orderByLevelIdx($order = Criteria::ASC) Order by the level_idx column
 * @method TradeCycleQuery orderByBuyPrice($order = Criteria::ASC) Order by the buy_price column
 * @method TradeCycleQuery orderBySellPrice($order = Criteria::ASC) Order by the sell_price column
 * @method TradeCycleQuery orderByQty($order = Criteria::ASC) Order by the qty column
 * @method TradeCycleQuery orderByRealizedPnl($order = Criteria::ASC) Order by the realized_pnl column
 * @method TradeCycleQuery orderByFeesTotal($order = Criteria::ASC) Order by the fees_total column
 * @method TradeCycleQuery orderBySimulated($order = Criteria::ASC) Order by the simulated column
 * @method TradeCycleQuery orderByDateCreation($order = Criteria::ASC) Order by the date_creation column
 * @method TradeCycleQuery orderByDateModification($order = Criteria::ASC) Order by the date_modification column
 * @method TradeCycleQuery orderByIdGroupCreation($order = Criteria::ASC) Order by the id_group_creation column
 * @method TradeCycleQuery orderByIdCreation($order = Criteria::ASC) Order by the id_creation column
 * @method TradeCycleQuery orderByIdModification($order = Criteria::ASC) Order by the id_modification column
 *
 * @method TradeCycleQuery groupByIdTradeCycle() Group by the id_trade_cycle column
 * @method TradeCycleQuery groupByIdGridRun() Group by the id_grid_run column
 * @method TradeCycleQuery groupByLevelIdx() Group by the level_idx column
 * @method TradeCycleQuery groupByBuyPrice() Group by the buy_price column
 * @method TradeCycleQuery groupBySellPrice() Group by the sell_price column
 * @method TradeCycleQuery groupByQty() Group by the qty column
 * @method TradeCycleQuery groupByRealizedPnl() Group by the realized_pnl column
 * @method TradeCycleQuery groupByFeesTotal() Group by the fees_total column
 * @method TradeCycleQuery groupBySimulated() Group by the simulated column
 * @method TradeCycleQuery groupByDateCreation() Group by the date_creation column
 * @method TradeCycleQuery groupByDateModification() Group by the date_modification column
 * @method TradeCycleQuery groupByIdGroupCreation() Group by the id_group_creation column
 * @method TradeCycleQuery groupByIdCreation() Group by the id_creation column
 * @method TradeCycleQuery groupByIdModification() Group by the id_modification column
 *
 * @method TradeCycleQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method TradeCycleQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method TradeCycleQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method TradeCycleQuery leftJoinGridRun($relationAlias = null) Adds a LEFT JOIN clause to the query using the GridRun relation
 * @method TradeCycleQuery rightJoinGridRun($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GridRun relation
 * @method TradeCycleQuery innerJoinGridRun($relationAlias = null) Adds a INNER JOIN clause to the query using the GridRun relation
 *
 * @method TradeCycleQuery leftJoinAuthyGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroup relation
 * @method TradeCycleQuery rightJoinAuthyGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroup relation
 * @method TradeCycleQuery innerJoinAuthyGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroup relation
 *
 * @method TradeCycleQuery leftJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method TradeCycleQuery rightJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method TradeCycleQuery innerJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdCreation relation
 *
 * @method TradeCycleQuery leftJoinAuthyRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method TradeCycleQuery rightJoinAuthyRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method TradeCycleQuery innerJoinAuthyRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdModification relation
 *
 * @method TradeCycle findOne(?PropelPDO $con = null) Return the first TradeCycle matching the query
 * @method TradeCycle findOneOrCreate(?PropelPDO $con = null) Return the first TradeCycle matching the query, or a new TradeCycle object populated from the query conditions when no match is found
 *
 * @method TradeCycle findOneByIdGridRun(int $id_grid_run) Return the first TradeCycle filtered by the id_grid_run column
 * @method TradeCycle findOneByLevelIdx(int $level_idx) Return the first TradeCycle filtered by the level_idx column
 * @method TradeCycle findOneByBuyPrice(string $buy_price) Return the first TradeCycle filtered by the buy_price column
 * @method TradeCycle findOneBySellPrice(string $sell_price) Return the first TradeCycle filtered by the sell_price column
 * @method TradeCycle findOneByQty(string $qty) Return the first TradeCycle filtered by the qty column
 * @method TradeCycle findOneByRealizedPnl(string $realized_pnl) Return the first TradeCycle filtered by the realized_pnl column
 * @method TradeCycle findOneByFeesTotal(string $fees_total) Return the first TradeCycle filtered by the fees_total column
 * @method TradeCycle findOneBySimulated(boolean $simulated) Return the first TradeCycle filtered by the simulated column
 * @method TradeCycle findOneByDateCreation(string $date_creation) Return the first TradeCycle filtered by the date_creation column
 * @method TradeCycle findOneByDateModification(string $date_modification) Return the first TradeCycle filtered by the date_modification column
 * @method TradeCycle findOneByIdGroupCreation(int $id_group_creation) Return the first TradeCycle filtered by the id_group_creation column
 * @method TradeCycle findOneByIdCreation(int $id_creation) Return the first TradeCycle filtered by the id_creation column
 * @method TradeCycle findOneByIdModification(int $id_modification) Return the first TradeCycle filtered by the id_modification column
 *
 * @method array findByIdTradeCycle(int $id_trade_cycle) Return TradeCycle objects filtered by the id_trade_cycle column
 * @method array findByIdGridRun(int $id_grid_run) Return TradeCycle objects filtered by the id_grid_run column
 * @method array findByLevelIdx(int $level_idx) Return TradeCycle objects filtered by the level_idx column
 * @method array findByBuyPrice(string $buy_price) Return TradeCycle objects filtered by the buy_price column
 * @method array findBySellPrice(string $sell_price) Return TradeCycle objects filtered by the sell_price column
 * @method array findByQty(string $qty) Return TradeCycle objects filtered by the qty column
 * @method array findByRealizedPnl(string $realized_pnl) Return TradeCycle objects filtered by the realized_pnl column
 * @method array findByFeesTotal(string $fees_total) Return TradeCycle objects filtered by the fees_total column
 * @method array findBySimulated(boolean $simulated) Return TradeCycle objects filtered by the simulated column
 * @method array findByDateCreation(string $date_creation) Return TradeCycle objects filtered by the date_creation column
 * @method array findByDateModification(string $date_modification) Return TradeCycle objects filtered by the date_modification column
 * @method array findByIdGroupCreation(int $id_group_creation) Return TradeCycle objects filtered by the id_group_creation column
 * @method array findByIdCreation(int $id_creation) Return TradeCycle objects filtered by the id_creation column
 * @method array findByIdModification(int $id_modification) Return TradeCycle objects filtered by the id_modification column
 *
 * @package    propel.generator..om
 */
abstract class BaseTradeCycleQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseTradeCycleQuery object.
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
            $modelName = 'App\\TradeCycle';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new TradeCycleQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   TradeCycleQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return TradeCycleQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof TradeCycleQuery) {
            return $criteria;
        }
        $query = new TradeCycleQuery(null, null, $modelAlias);

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
     * @return   TradeCycle|TradeCycle[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = TradeCyclePeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(TradeCyclePeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 TradeCycle A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdTradeCycle($key, $con = null)
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
     * @return                 TradeCycle A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_trade_cycle`, `id_grid_run`, `level_idx`, `buy_price`, `sell_price`, `qty`, `realized_pnl`, `fees_total`, `simulated`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification` FROM `trade_cycle` WHERE `id_trade_cycle` = :p0';
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
            $obj = new TradeCycle();
            $obj->hydrate($row);
            TradeCyclePeer::addInstanceToPool($obj, (string) $key);
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
     * @return TradeCycle|TradeCycle[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|TradeCycle[]|mixed the list of results, formatted by the current formatter
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
     * @return TradeCycleQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(TradeCyclePeer::ID_TRADE_CYCLE, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return TradeCycleQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(TradeCyclePeer::ID_TRADE_CYCLE, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id_trade_cycle column
     *
     * Example usage:
     * <code>
     * $query->filterByIdTradeCycle(1234); // WHERE id_trade_cycle = 1234
     * $query->filterByIdTradeCycle(array(12, 34)); // WHERE id_trade_cycle IN (12, 34)
     * $query->filterByIdTradeCycle(array('min' => 12)); // WHERE id_trade_cycle >= 12
     * $query->filterByIdTradeCycle(array('max' => 12)); // WHERE id_trade_cycle <= 12
     * </code>
     *
     * @param     mixed $idTradeCycle The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TradeCycleQuery The current query, for fluid interface
     */
    public function filterByIdTradeCycle($idTradeCycle = null, $comparison = null)
    {
        if (is_array($idTradeCycle)) {
            $useMinMax = false;
            if (isset($idTradeCycle['min'])) {
                $this->addUsingAlias(TradeCyclePeer::ID_TRADE_CYCLE, $idTradeCycle['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idTradeCycle['max'])) {
                $this->addUsingAlias(TradeCyclePeer::ID_TRADE_CYCLE, $idTradeCycle['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TradeCyclePeer::ID_TRADE_CYCLE, $idTradeCycle, $comparison);
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
     * @return TradeCycleQuery The current query, for fluid interface
     */
    public function filterByIdGridRun($idGridRun = null, $comparison = null)
    {
        if (is_array($idGridRun)) {
            $useMinMax = false;
            if (isset($idGridRun['min'])) {
                $this->addUsingAlias(TradeCyclePeer::ID_GRID_RUN, $idGridRun['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGridRun['max'])) {
                $this->addUsingAlias(TradeCyclePeer::ID_GRID_RUN, $idGridRun['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TradeCyclePeer::ID_GRID_RUN, $idGridRun, $comparison);
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
     * @return TradeCycleQuery The current query, for fluid interface
     */
    public function filterByLevelIdx($levelIdx = null, $comparison = null)
    {
        if (is_array($levelIdx)) {
            $useMinMax = false;
            if (isset($levelIdx['min'])) {
                $this->addUsingAlias(TradeCyclePeer::LEVEL_IDX, $levelIdx['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($levelIdx['max'])) {
                $this->addUsingAlias(TradeCyclePeer::LEVEL_IDX, $levelIdx['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TradeCyclePeer::LEVEL_IDX, $levelIdx, $comparison);
    }

    /**
     * Filter the query on the buy_price column
     *
     * Example usage:
     * <code>
     * $query->filterByBuyPrice(1234); // WHERE buy_price = 1234
     * $query->filterByBuyPrice(array(12, 34)); // WHERE buy_price IN (12, 34)
     * $query->filterByBuyPrice(array('min' => 12)); // WHERE buy_price >= 12
     * $query->filterByBuyPrice(array('max' => 12)); // WHERE buy_price <= 12
     * </code>
     *
     * @param     mixed $buyPrice The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TradeCycleQuery The current query, for fluid interface
     */
    public function filterByBuyPrice($buyPrice = null, $comparison = null)
    {
        if (is_array($buyPrice)) {
            $useMinMax = false;
            if (isset($buyPrice['min'])) {
                $this->addUsingAlias(TradeCyclePeer::BUY_PRICE, $buyPrice['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($buyPrice['max'])) {
                $this->addUsingAlias(TradeCyclePeer::BUY_PRICE, $buyPrice['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TradeCyclePeer::BUY_PRICE, $buyPrice, $comparison);
    }

    /**
     * Filter the query on the sell_price column
     *
     * Example usage:
     * <code>
     * $query->filterBySellPrice(1234); // WHERE sell_price = 1234
     * $query->filterBySellPrice(array(12, 34)); // WHERE sell_price IN (12, 34)
     * $query->filterBySellPrice(array('min' => 12)); // WHERE sell_price >= 12
     * $query->filterBySellPrice(array('max' => 12)); // WHERE sell_price <= 12
     * </code>
     *
     * @param     mixed $sellPrice The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TradeCycleQuery The current query, for fluid interface
     */
    public function filterBySellPrice($sellPrice = null, $comparison = null)
    {
        if (is_array($sellPrice)) {
            $useMinMax = false;
            if (isset($sellPrice['min'])) {
                $this->addUsingAlias(TradeCyclePeer::SELL_PRICE, $sellPrice['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($sellPrice['max'])) {
                $this->addUsingAlias(TradeCyclePeer::SELL_PRICE, $sellPrice['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TradeCyclePeer::SELL_PRICE, $sellPrice, $comparison);
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
     * @return TradeCycleQuery The current query, for fluid interface
     */
    public function filterByQty($qty = null, $comparison = null)
    {
        if (is_array($qty)) {
            $useMinMax = false;
            if (isset($qty['min'])) {
                $this->addUsingAlias(TradeCyclePeer::QTY, $qty['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($qty['max'])) {
                $this->addUsingAlias(TradeCyclePeer::QTY, $qty['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TradeCyclePeer::QTY, $qty, $comparison);
    }

    /**
     * Filter the query on the realized_pnl column
     *
     * Example usage:
     * <code>
     * $query->filterByRealizedPnl(1234); // WHERE realized_pnl = 1234
     * $query->filterByRealizedPnl(array(12, 34)); // WHERE realized_pnl IN (12, 34)
     * $query->filterByRealizedPnl(array('min' => 12)); // WHERE realized_pnl >= 12
     * $query->filterByRealizedPnl(array('max' => 12)); // WHERE realized_pnl <= 12
     * </code>
     *
     * @param     mixed $realizedPnl The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TradeCycleQuery The current query, for fluid interface
     */
    public function filterByRealizedPnl($realizedPnl = null, $comparison = null)
    {
        if (is_array($realizedPnl)) {
            $useMinMax = false;
            if (isset($realizedPnl['min'])) {
                $this->addUsingAlias(TradeCyclePeer::REALIZED_PNL, $realizedPnl['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($realizedPnl['max'])) {
                $this->addUsingAlias(TradeCyclePeer::REALIZED_PNL, $realizedPnl['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TradeCyclePeer::REALIZED_PNL, $realizedPnl, $comparison);
    }

    /**
     * Filter the query on the fees_total column
     *
     * Example usage:
     * <code>
     * $query->filterByFeesTotal(1234); // WHERE fees_total = 1234
     * $query->filterByFeesTotal(array(12, 34)); // WHERE fees_total IN (12, 34)
     * $query->filterByFeesTotal(array('min' => 12)); // WHERE fees_total >= 12
     * $query->filterByFeesTotal(array('max' => 12)); // WHERE fees_total <= 12
     * </code>
     *
     * @param     mixed $feesTotal The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return TradeCycleQuery The current query, for fluid interface
     */
    public function filterByFeesTotal($feesTotal = null, $comparison = null)
    {
        if (is_array($feesTotal)) {
            $useMinMax = false;
            if (isset($feesTotal['min'])) {
                $this->addUsingAlias(TradeCyclePeer::FEES_TOTAL, $feesTotal['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($feesTotal['max'])) {
                $this->addUsingAlias(TradeCyclePeer::FEES_TOTAL, $feesTotal['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TradeCyclePeer::FEES_TOTAL, $feesTotal, $comparison);
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
     * @return TradeCycleQuery The current query, for fluid interface
     */
    public function filterBySimulated($simulated = null, $comparison = null)
    {
        if (is_string($simulated)) {
            $simulated = in_array(strtolower($simulated), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(TradeCyclePeer::SIMULATED, $simulated, $comparison);
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
     * @return TradeCycleQuery The current query, for fluid interface
     */
    public function filterByDateCreation($dateCreation = null, $comparison = null)
    {
        if (is_array($dateCreation)) {
            $useMinMax = false;
            if (isset($dateCreation['min'])) {
                $this->addUsingAlias(TradeCyclePeer::DATE_CREATION, $dateCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateCreation['max'])) {
                $this->addUsingAlias(TradeCyclePeer::DATE_CREATION, $dateCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TradeCyclePeer::DATE_CREATION, $dateCreation, $comparison);
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
     * @return TradeCycleQuery The current query, for fluid interface
     */
    public function filterByDateModification($dateModification = null, $comparison = null)
    {
        if (is_array($dateModification)) {
            $useMinMax = false;
            if (isset($dateModification['min'])) {
                $this->addUsingAlias(TradeCyclePeer::DATE_MODIFICATION, $dateModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateModification['max'])) {
                $this->addUsingAlias(TradeCyclePeer::DATE_MODIFICATION, $dateModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TradeCyclePeer::DATE_MODIFICATION, $dateModification, $comparison);
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
     * @return TradeCycleQuery The current query, for fluid interface
     */
    public function filterByIdGroupCreation($idGroupCreation = null, $comparison = null)
    {
        if (is_array($idGroupCreation)) {
            $useMinMax = false;
            if (isset($idGroupCreation['min'])) {
                $this->addUsingAlias(TradeCyclePeer::ID_GROUP_CREATION, $idGroupCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGroupCreation['max'])) {
                $this->addUsingAlias(TradeCyclePeer::ID_GROUP_CREATION, $idGroupCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TradeCyclePeer::ID_GROUP_CREATION, $idGroupCreation, $comparison);
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
     * @return TradeCycleQuery The current query, for fluid interface
     */
    public function filterByIdCreation($idCreation = null, $comparison = null)
    {
        if (is_array($idCreation)) {
            $useMinMax = false;
            if (isset($idCreation['min'])) {
                $this->addUsingAlias(TradeCyclePeer::ID_CREATION, $idCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idCreation['max'])) {
                $this->addUsingAlias(TradeCyclePeer::ID_CREATION, $idCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TradeCyclePeer::ID_CREATION, $idCreation, $comparison);
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
     * @return TradeCycleQuery The current query, for fluid interface
     */
    public function filterByIdModification($idModification = null, $comparison = null)
    {
        if (is_array($idModification)) {
            $useMinMax = false;
            if (isset($idModification['min'])) {
                $this->addUsingAlias(TradeCyclePeer::ID_MODIFICATION, $idModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idModification['max'])) {
                $this->addUsingAlias(TradeCyclePeer::ID_MODIFICATION, $idModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(TradeCyclePeer::ID_MODIFICATION, $idModification, $comparison);
    }

    /**
     * Filter the query by a related GridRun object
     *
     * @param   GridRun|PropelObjectCollection $gridRun The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 TradeCycleQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByGridRun($gridRun, $comparison = null)
    {
        if ($gridRun instanceof GridRun) {
            return $this
                ->addUsingAlias(TradeCyclePeer::ID_GRID_RUN, $gridRun->getIdGridRun(), $comparison);
        } elseif ($gridRun instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(TradeCyclePeer::ID_GRID_RUN, $gridRun->toKeyValue('PrimaryKey', 'IdGridRun'), $comparison);
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
     * @return TradeCycleQuery The current query, for fluid interface
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
     * @return                 TradeCycleQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroup($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(TradeCyclePeer::ID_GROUP_CREATION, $authyGroup->getIdAuthyGroup(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(TradeCyclePeer::ID_GROUP_CREATION, $authyGroup->toKeyValue('PrimaryKey', 'IdAuthyGroup'), $comparison);
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
     * @return TradeCycleQuery The current query, for fluid interface
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
     * @return                 TradeCycleQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdCreation($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(TradeCyclePeer::ID_CREATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(TradeCyclePeer::ID_CREATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return TradeCycleQuery The current query, for fluid interface
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
     * @return                 TradeCycleQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdModification($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(TradeCyclePeer::ID_MODIFICATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(TradeCyclePeer::ID_MODIFICATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return TradeCycleQuery The current query, for fluid interface
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
     * @param   TradeCycle $tradeCycle Object to remove from the list of results
     *
     * @return TradeCycleQuery The current query, for fluid interface
     */
    public function prune($tradeCycle = null)
    {
        if ($tradeCycle) {
            $this->addUsingAlias(TradeCyclePeer::ID_TRADE_CYCLE, $tradeCycle->getIdTradeCycle(), Criteria::NOT_EQUAL);
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
                    \ApiGoat\Utility\TableVersion::bump('trade_cycle');
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
                    \ApiGoat\Utility\TableVersion::bump('trade_cycle');
                }

        return $this->postUpdate($affectedRows, $con);
    }

    // add_tablestamp behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     TradeCycleQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7){
        return $this->addUsingAlias(TradeCyclePeer::DATE_MODIFICATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     TradeCycleQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst(){
        return $this->addDescendingOrderByColumn(TradeCyclePeer::DATE_MODIFICATION);
    }

    /**
     * Order by update date asc
     *
     * @return     TradeCycleQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst(){
        return $this->addAscendingOrderByColumn(TradeCyclePeer::DATE_MODIFICATION);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     TradeCycleQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7){
        return $this->addUsingAlias(TradeCyclePeer::DATE_CREATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return     TradeCycleQuery The current query, for fluid interface
     */
    public function lastCreatedFirst(){
        return $this->addDescendingOrderByColumn(TradeCyclePeer::DATE_CREATION);
    }

    /**
     * Order by create date asc
     *
     * @return     TradeCycleQuery The current query, for fluid interface
     */
    public function firstCreatedFirst(){
        return $this->addAscendingOrderByColumn(TradeCyclePeer::DATE_CREATION);
    }
}
