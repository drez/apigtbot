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
use App\MarketCandle;
use App\MarketCandlePeer;
use App\MarketCandleQuery;

/**
 * Base class that represents a query for the 'market_candle' table.
 *
 * Candles
 *
 * @method MarketCandleQuery orderByIdMarketCandle($order = Criteria::ASC) Order by the id_market_candle column
 * @method MarketCandleQuery orderBySymbol($order = Criteria::ASC) Order by the symbol column
 * @method MarketCandleQuery orderByTf($order = Criteria::ASC) Order by the tf column
 * @method MarketCandleQuery orderByOpenTime($order = Criteria::ASC) Order by the open_time column
 * @method MarketCandleQuery orderByOpen($order = Criteria::ASC) Order by the open column
 * @method MarketCandleQuery orderByHigh($order = Criteria::ASC) Order by the high column
 * @method MarketCandleQuery orderByLow($order = Criteria::ASC) Order by the low column
 * @method MarketCandleQuery orderByClose($order = Criteria::ASC) Order by the close column
 * @method MarketCandleQuery orderByVolume($order = Criteria::ASC) Order by the volume column
 * @method MarketCandleQuery orderByDateCreation($order = Criteria::ASC) Order by the date_creation column
 * @method MarketCandleQuery orderByDateModification($order = Criteria::ASC) Order by the date_modification column
 * @method MarketCandleQuery orderByIdGroupCreation($order = Criteria::ASC) Order by the id_group_creation column
 * @method MarketCandleQuery orderByIdCreation($order = Criteria::ASC) Order by the id_creation column
 * @method MarketCandleQuery orderByIdModification($order = Criteria::ASC) Order by the id_modification column
 *
 * @method MarketCandleQuery groupByIdMarketCandle() Group by the id_market_candle column
 * @method MarketCandleQuery groupBySymbol() Group by the symbol column
 * @method MarketCandleQuery groupByTf() Group by the tf column
 * @method MarketCandleQuery groupByOpenTime() Group by the open_time column
 * @method MarketCandleQuery groupByOpen() Group by the open column
 * @method MarketCandleQuery groupByHigh() Group by the high column
 * @method MarketCandleQuery groupByLow() Group by the low column
 * @method MarketCandleQuery groupByClose() Group by the close column
 * @method MarketCandleQuery groupByVolume() Group by the volume column
 * @method MarketCandleQuery groupByDateCreation() Group by the date_creation column
 * @method MarketCandleQuery groupByDateModification() Group by the date_modification column
 * @method MarketCandleQuery groupByIdGroupCreation() Group by the id_group_creation column
 * @method MarketCandleQuery groupByIdCreation() Group by the id_creation column
 * @method MarketCandleQuery groupByIdModification() Group by the id_modification column
 *
 * @method MarketCandleQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method MarketCandleQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method MarketCandleQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method MarketCandleQuery leftJoinAuthyGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroup relation
 * @method MarketCandleQuery rightJoinAuthyGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroup relation
 * @method MarketCandleQuery innerJoinAuthyGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroup relation
 *
 * @method MarketCandleQuery leftJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method MarketCandleQuery rightJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method MarketCandleQuery innerJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdCreation relation
 *
 * @method MarketCandleQuery leftJoinAuthyRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method MarketCandleQuery rightJoinAuthyRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method MarketCandleQuery innerJoinAuthyRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdModification relation
 *
 * @method MarketCandle findOne(?PropelPDO $con = null) Return the first MarketCandle matching the query
 * @method MarketCandle findOneOrCreate(?PropelPDO $con = null) Return the first MarketCandle matching the query, or a new MarketCandle object populated from the query conditions when no match is found
 *
 * @method MarketCandle findOneBySymbol(string $symbol) Return the first MarketCandle filtered by the symbol column
 * @method MarketCandle findOneByTf(int $tf) Return the first MarketCandle filtered by the tf column
 * @method MarketCandle findOneByOpenTime(int $open_time) Return the first MarketCandle filtered by the open_time column
 * @method MarketCandle findOneByOpen(string $open) Return the first MarketCandle filtered by the open column
 * @method MarketCandle findOneByHigh(string $high) Return the first MarketCandle filtered by the high column
 * @method MarketCandle findOneByLow(string $low) Return the first MarketCandle filtered by the low column
 * @method MarketCandle findOneByClose(string $close) Return the first MarketCandle filtered by the close column
 * @method MarketCandle findOneByVolume(string $volume) Return the first MarketCandle filtered by the volume column
 * @method MarketCandle findOneByDateCreation(string $date_creation) Return the first MarketCandle filtered by the date_creation column
 * @method MarketCandle findOneByDateModification(string $date_modification) Return the first MarketCandle filtered by the date_modification column
 * @method MarketCandle findOneByIdGroupCreation(int $id_group_creation) Return the first MarketCandle filtered by the id_group_creation column
 * @method MarketCandle findOneByIdCreation(int $id_creation) Return the first MarketCandle filtered by the id_creation column
 * @method MarketCandle findOneByIdModification(int $id_modification) Return the first MarketCandle filtered by the id_modification column
 *
 * @method array findByIdMarketCandle(int $id_market_candle) Return MarketCandle objects filtered by the id_market_candle column
 * @method array findBySymbol(string $symbol) Return MarketCandle objects filtered by the symbol column
 * @method array findByTf(int $tf) Return MarketCandle objects filtered by the tf column
 * @method array findByOpenTime(int $open_time) Return MarketCandle objects filtered by the open_time column
 * @method array findByOpen(string $open) Return MarketCandle objects filtered by the open column
 * @method array findByHigh(string $high) Return MarketCandle objects filtered by the high column
 * @method array findByLow(string $low) Return MarketCandle objects filtered by the low column
 * @method array findByClose(string $close) Return MarketCandle objects filtered by the close column
 * @method array findByVolume(string $volume) Return MarketCandle objects filtered by the volume column
 * @method array findByDateCreation(string $date_creation) Return MarketCandle objects filtered by the date_creation column
 * @method array findByDateModification(string $date_modification) Return MarketCandle objects filtered by the date_modification column
 * @method array findByIdGroupCreation(int $id_group_creation) Return MarketCandle objects filtered by the id_group_creation column
 * @method array findByIdCreation(int $id_creation) Return MarketCandle objects filtered by the id_creation column
 * @method array findByIdModification(int $id_modification) Return MarketCandle objects filtered by the id_modification column
 *
 * @package    propel.generator..om
 */
abstract class BaseMarketCandleQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseMarketCandleQuery object.
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
            $modelName = 'App\\MarketCandle';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new MarketCandleQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   MarketCandleQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return MarketCandleQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof MarketCandleQuery) {
            return $criteria;
        }
        $query = new MarketCandleQuery(null, null, $modelAlias);

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
     * @return   MarketCandle|MarketCandle[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = MarketCandlePeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(MarketCandlePeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 MarketCandle A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdMarketCandle($key, $con = null)
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
     * @return                 MarketCandle A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_market_candle`, `symbol`, `tf`, `open_time`, `open`, `high`, `low`, `close`, `volume`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification` FROM `market_candle` WHERE `id_market_candle` = :p0';
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
            $obj = new MarketCandle();
            $obj->hydrate($row);
            MarketCandlePeer::addInstanceToPool($obj, (string) $key);
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
     * @return MarketCandle|MarketCandle[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|MarketCandle[]|mixed the list of results, formatted by the current formatter
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
     * @return MarketCandleQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(MarketCandlePeer::ID_MARKET_CANDLE, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return MarketCandleQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(MarketCandlePeer::ID_MARKET_CANDLE, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id_market_candle column
     *
     * Example usage:
     * <code>
     * $query->filterByIdMarketCandle(1234); // WHERE id_market_candle = 1234
     * $query->filterByIdMarketCandle(array(12, 34)); // WHERE id_market_candle IN (12, 34)
     * $query->filterByIdMarketCandle(array('min' => 12)); // WHERE id_market_candle >= 12
     * $query->filterByIdMarketCandle(array('max' => 12)); // WHERE id_market_candle <= 12
     * </code>
     *
     * @param     mixed $idMarketCandle The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketCandleQuery The current query, for fluid interface
     */
    public function filterByIdMarketCandle($idMarketCandle = null, $comparison = null)
    {
        if (is_array($idMarketCandle)) {
            $useMinMax = false;
            if (isset($idMarketCandle['min'])) {
                $this->addUsingAlias(MarketCandlePeer::ID_MARKET_CANDLE, $idMarketCandle['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idMarketCandle['max'])) {
                $this->addUsingAlias(MarketCandlePeer::ID_MARKET_CANDLE, $idMarketCandle['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketCandlePeer::ID_MARKET_CANDLE, $idMarketCandle, $comparison);
    }

    /**
     * Filter the query on the symbol column
     *
     * Example usage:
     * <code>
     * $query->filterBySymbol('fooValue');   // WHERE symbol = 'fooValue'
     * $query->filterBySymbol('%fooValue%'); // WHERE symbol LIKE '%fooValue%'
     * </code>
     *
     * @param     string $symbol The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketCandleQuery The current query, for fluid interface
     */
    public function filterBySymbol($symbol = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($symbol)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $symbol)) {
                $symbol = str_replace('*', '%', $symbol);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(MarketCandlePeer::SYMBOL, $symbol, $comparison);
    }

    /**
     * Filter the query on the tf column
     *
     * @param     mixed $tf The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketCandleQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByTf($tf = null, $comparison = null)
    {
        if (is_scalar($tf)) {
            $tf = MarketCandlePeer::getSqlValueForEnum(MarketCandlePeer::TF, $tf);
        } elseif (is_array($tf)) {
            $convertedValues = array();
            foreach ($tf as $value) {
                $convertedValues[] = MarketCandlePeer::getSqlValueForEnum(MarketCandlePeer::TF, $value);
            }
            $tf = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketCandlePeer::TF, $tf, $comparison);
    }

    /**
     * Filter the query on the open_time column
     *
     * Example usage:
     * <code>
     * $query->filterByOpenTime(1234); // WHERE open_time = 1234
     * $query->filterByOpenTime(array(12, 34)); // WHERE open_time IN (12, 34)
     * $query->filterByOpenTime(array('min' => 12)); // WHERE open_time >= 12
     * $query->filterByOpenTime(array('max' => 12)); // WHERE open_time <= 12
     * </code>
     *
     * @param     mixed $openTime The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketCandleQuery The current query, for fluid interface
     */
    public function filterByOpenTime($openTime = null, $comparison = null)
    {
        if (is_array($openTime)) {
            $useMinMax = false;
            if (isset($openTime['min'])) {
                $this->addUsingAlias(MarketCandlePeer::OPEN_TIME, $openTime['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($openTime['max'])) {
                $this->addUsingAlias(MarketCandlePeer::OPEN_TIME, $openTime['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketCandlePeer::OPEN_TIME, $openTime, $comparison);
    }

    /**
     * Filter the query on the open column
     *
     * Example usage:
     * <code>
     * $query->filterByOpen(1234); // WHERE open = 1234
     * $query->filterByOpen(array(12, 34)); // WHERE open IN (12, 34)
     * $query->filterByOpen(array('min' => 12)); // WHERE open >= 12
     * $query->filterByOpen(array('max' => 12)); // WHERE open <= 12
     * </code>
     *
     * @param     mixed $open The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketCandleQuery The current query, for fluid interface
     */
    public function filterByOpen($open = null, $comparison = null)
    {
        if (is_array($open)) {
            $useMinMax = false;
            if (isset($open['min'])) {
                $this->addUsingAlias(MarketCandlePeer::OPEN, $open['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($open['max'])) {
                $this->addUsingAlias(MarketCandlePeer::OPEN, $open['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketCandlePeer::OPEN, $open, $comparison);
    }

    /**
     * Filter the query on the high column
     *
     * Example usage:
     * <code>
     * $query->filterByHigh(1234); // WHERE high = 1234
     * $query->filterByHigh(array(12, 34)); // WHERE high IN (12, 34)
     * $query->filterByHigh(array('min' => 12)); // WHERE high >= 12
     * $query->filterByHigh(array('max' => 12)); // WHERE high <= 12
     * </code>
     *
     * @param     mixed $high The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketCandleQuery The current query, for fluid interface
     */
    public function filterByHigh($high = null, $comparison = null)
    {
        if (is_array($high)) {
            $useMinMax = false;
            if (isset($high['min'])) {
                $this->addUsingAlias(MarketCandlePeer::HIGH, $high['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($high['max'])) {
                $this->addUsingAlias(MarketCandlePeer::HIGH, $high['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketCandlePeer::HIGH, $high, $comparison);
    }

    /**
     * Filter the query on the low column
     *
     * Example usage:
     * <code>
     * $query->filterByLow(1234); // WHERE low = 1234
     * $query->filterByLow(array(12, 34)); // WHERE low IN (12, 34)
     * $query->filterByLow(array('min' => 12)); // WHERE low >= 12
     * $query->filterByLow(array('max' => 12)); // WHERE low <= 12
     * </code>
     *
     * @param     mixed $low The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketCandleQuery The current query, for fluid interface
     */
    public function filterByLow($low = null, $comparison = null)
    {
        if (is_array($low)) {
            $useMinMax = false;
            if (isset($low['min'])) {
                $this->addUsingAlias(MarketCandlePeer::LOW, $low['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($low['max'])) {
                $this->addUsingAlias(MarketCandlePeer::LOW, $low['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketCandlePeer::LOW, $low, $comparison);
    }

    /**
     * Filter the query on the close column
     *
     * Example usage:
     * <code>
     * $query->filterByClose(1234); // WHERE close = 1234
     * $query->filterByClose(array(12, 34)); // WHERE close IN (12, 34)
     * $query->filterByClose(array('min' => 12)); // WHERE close >= 12
     * $query->filterByClose(array('max' => 12)); // WHERE close <= 12
     * </code>
     *
     * @param     mixed $close The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketCandleQuery The current query, for fluid interface
     */
    public function filterByClose($close = null, $comparison = null)
    {
        if (is_array($close)) {
            $useMinMax = false;
            if (isset($close['min'])) {
                $this->addUsingAlias(MarketCandlePeer::CLOSE, $close['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($close['max'])) {
                $this->addUsingAlias(MarketCandlePeer::CLOSE, $close['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketCandlePeer::CLOSE, $close, $comparison);
    }

    /**
     * Filter the query on the volume column
     *
     * Example usage:
     * <code>
     * $query->filterByVolume(1234); // WHERE volume = 1234
     * $query->filterByVolume(array(12, 34)); // WHERE volume IN (12, 34)
     * $query->filterByVolume(array('min' => 12)); // WHERE volume >= 12
     * $query->filterByVolume(array('max' => 12)); // WHERE volume <= 12
     * </code>
     *
     * @param     mixed $volume The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketCandleQuery The current query, for fluid interface
     */
    public function filterByVolume($volume = null, $comparison = null)
    {
        if (is_array($volume)) {
            $useMinMax = false;
            if (isset($volume['min'])) {
                $this->addUsingAlias(MarketCandlePeer::VOLUME, $volume['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($volume['max'])) {
                $this->addUsingAlias(MarketCandlePeer::VOLUME, $volume['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketCandlePeer::VOLUME, $volume, $comparison);
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
     * @return MarketCandleQuery The current query, for fluid interface
     */
    public function filterByDateCreation($dateCreation = null, $comparison = null)
    {
        if (is_array($dateCreation)) {
            $useMinMax = false;
            if (isset($dateCreation['min'])) {
                $this->addUsingAlias(MarketCandlePeer::DATE_CREATION, $dateCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateCreation['max'])) {
                $this->addUsingAlias(MarketCandlePeer::DATE_CREATION, $dateCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketCandlePeer::DATE_CREATION, $dateCreation, $comparison);
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
     * @return MarketCandleQuery The current query, for fluid interface
     */
    public function filterByDateModification($dateModification = null, $comparison = null)
    {
        if (is_array($dateModification)) {
            $useMinMax = false;
            if (isset($dateModification['min'])) {
                $this->addUsingAlias(MarketCandlePeer::DATE_MODIFICATION, $dateModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateModification['max'])) {
                $this->addUsingAlias(MarketCandlePeer::DATE_MODIFICATION, $dateModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketCandlePeer::DATE_MODIFICATION, $dateModification, $comparison);
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
     * @return MarketCandleQuery The current query, for fluid interface
     */
    public function filterByIdGroupCreation($idGroupCreation = null, $comparison = null)
    {
        if (is_array($idGroupCreation)) {
            $useMinMax = false;
            if (isset($idGroupCreation['min'])) {
                $this->addUsingAlias(MarketCandlePeer::ID_GROUP_CREATION, $idGroupCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGroupCreation['max'])) {
                $this->addUsingAlias(MarketCandlePeer::ID_GROUP_CREATION, $idGroupCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketCandlePeer::ID_GROUP_CREATION, $idGroupCreation, $comparison);
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
     * @return MarketCandleQuery The current query, for fluid interface
     */
    public function filterByIdCreation($idCreation = null, $comparison = null)
    {
        if (is_array($idCreation)) {
            $useMinMax = false;
            if (isset($idCreation['min'])) {
                $this->addUsingAlias(MarketCandlePeer::ID_CREATION, $idCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idCreation['max'])) {
                $this->addUsingAlias(MarketCandlePeer::ID_CREATION, $idCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketCandlePeer::ID_CREATION, $idCreation, $comparison);
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
     * @return MarketCandleQuery The current query, for fluid interface
     */
    public function filterByIdModification($idModification = null, $comparison = null)
    {
        if (is_array($idModification)) {
            $useMinMax = false;
            if (isset($idModification['min'])) {
                $this->addUsingAlias(MarketCandlePeer::ID_MODIFICATION, $idModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idModification['max'])) {
                $this->addUsingAlias(MarketCandlePeer::ID_MODIFICATION, $idModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketCandlePeer::ID_MODIFICATION, $idModification, $comparison);
    }

    /**
     * Filter the query by a related AuthyGroup object
     *
     * @param   AuthyGroup|PropelObjectCollection $authyGroup The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 MarketCandleQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroup($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(MarketCandlePeer::ID_GROUP_CREATION, $authyGroup->getIdAuthyGroup(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(MarketCandlePeer::ID_GROUP_CREATION, $authyGroup->toKeyValue('PrimaryKey', 'IdAuthyGroup'), $comparison);
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
     * @return MarketCandleQuery The current query, for fluid interface
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
     * @return                 MarketCandleQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdCreation($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(MarketCandlePeer::ID_CREATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(MarketCandlePeer::ID_CREATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return MarketCandleQuery The current query, for fluid interface
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
     * @return                 MarketCandleQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdModification($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(MarketCandlePeer::ID_MODIFICATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(MarketCandlePeer::ID_MODIFICATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return MarketCandleQuery The current query, for fluid interface
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
     * @param   MarketCandle $marketCandle Object to remove from the list of results
     *
     * @return MarketCandleQuery The current query, for fluid interface
     */
    public function prune($marketCandle = null)
    {
        if ($marketCandle) {
            $this->addUsingAlias(MarketCandlePeer::ID_MARKET_CANDLE, $marketCandle->getIdMarketCandle(), Criteria::NOT_EQUAL);
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
                    \ApiGoat\Utility\TableVersion::bump('market_candle');
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
                    \ApiGoat\Utility\TableVersion::bump('market_candle');
                }

        return $this->postUpdate($affectedRows, $con);
    }

    // add_tablestamp behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     MarketCandleQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7){
        return $this->addUsingAlias(MarketCandlePeer::DATE_MODIFICATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     MarketCandleQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst(){
        return $this->addDescendingOrderByColumn(MarketCandlePeer::DATE_MODIFICATION);
    }

    /**
     * Order by update date asc
     *
     * @return     MarketCandleQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst(){
        return $this->addAscendingOrderByColumn(MarketCandlePeer::DATE_MODIFICATION);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     MarketCandleQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7){
        return $this->addUsingAlias(MarketCandlePeer::DATE_CREATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return     MarketCandleQuery The current query, for fluid interface
     */
    public function lastCreatedFirst(){
        return $this->addDescendingOrderByColumn(MarketCandlePeer::DATE_CREATION);
    }

    /**
     * Order by create date asc
     *
     * @return     MarketCandleQuery The current query, for fluid interface
     */
    public function firstCreatedFirst(){
        return $this->addAscendingOrderByColumn(MarketCandlePeer::DATE_CREATION);
    }
}
