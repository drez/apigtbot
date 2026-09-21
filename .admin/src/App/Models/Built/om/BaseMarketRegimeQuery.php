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
use App\MarketRegime;
use App\MarketRegimePeer;
use App\MarketRegimeQuery;

/**
 * Base class that represents a query for the 'market_regime' table.
 *
 * Regime History
 *
 * @method MarketRegimeQuery orderByIdMarketRegime($order = Criteria::ASC) Order by the id_market_regime column
 * @method MarketRegimeQuery orderBySymbol($order = Criteria::ASC) Order by the symbol column
 * @method MarketRegimeQuery orderByTf($order = Criteria::ASC) Order by the tf column
 * @method MarketRegimeQuery orderByPrice($order = Criteria::ASC) Order by the price column
 * @method MarketRegimeQuery orderByTrend($order = Criteria::ASC) Order by the trend column
 * @method MarketRegimeQuery orderByRsi14($order = Criteria::ASC) Order by the rsi14 column
 * @method MarketRegimeQuery orderByAtrPct($order = Criteria::ASC) Order by the atr_pct column
 * @method MarketRegimeQuery orderByAdx14($order = Criteria::ASC) Order by the adx14 column
 * @method MarketRegimeQuery orderByAtrPctRank($order = Criteria::ASC) Order by the atr_pct_rank column
 * @method MarketRegimeQuery orderByTakerBuyRatio($order = Criteria::ASC) Order by the taker_buy_ratio column
 * @method MarketRegimeQuery orderByVolZscore($order = Criteria::ASC) Order by the vol_zscore column
 * @method MarketRegimeQuery orderByEr20($order = Criteria::ASC) Order by the er20 column
 * @method MarketRegimeQuery orderByChop14($order = Criteria::ASC) Order by the chop14 column
 * @method MarketRegimeQuery orderByFundingPct($order = Criteria::ASC) Order by the funding_pct column
 * @method MarketRegimeQuery orderByFundingRate($order = Criteria::ASC) Order by the funding_rate column
 * @method MarketRegimeQuery orderByDepthImbalance($order = Criteria::ASC) Order by the depth_imbalance column
 * @method MarketRegimeQuery orderByDepthImbalanceAvg($order = Criteria::ASC) Order by the depth_imbalance_avg column
 * @method MarketRegimeQuery orderByDateCreation($order = Criteria::ASC) Order by the date_creation column
 * @method MarketRegimeQuery orderByDateModification($order = Criteria::ASC) Order by the date_modification column
 * @method MarketRegimeQuery orderByIdGroupCreation($order = Criteria::ASC) Order by the id_group_creation column
 * @method MarketRegimeQuery orderByIdCreation($order = Criteria::ASC) Order by the id_creation column
 * @method MarketRegimeQuery orderByIdModification($order = Criteria::ASC) Order by the id_modification column
 *
 * @method MarketRegimeQuery groupByIdMarketRegime() Group by the id_market_regime column
 * @method MarketRegimeQuery groupBySymbol() Group by the symbol column
 * @method MarketRegimeQuery groupByTf() Group by the tf column
 * @method MarketRegimeQuery groupByPrice() Group by the price column
 * @method MarketRegimeQuery groupByTrend() Group by the trend column
 * @method MarketRegimeQuery groupByRsi14() Group by the rsi14 column
 * @method MarketRegimeQuery groupByAtrPct() Group by the atr_pct column
 * @method MarketRegimeQuery groupByAdx14() Group by the adx14 column
 * @method MarketRegimeQuery groupByAtrPctRank() Group by the atr_pct_rank column
 * @method MarketRegimeQuery groupByTakerBuyRatio() Group by the taker_buy_ratio column
 * @method MarketRegimeQuery groupByVolZscore() Group by the vol_zscore column
 * @method MarketRegimeQuery groupByEr20() Group by the er20 column
 * @method MarketRegimeQuery groupByChop14() Group by the chop14 column
 * @method MarketRegimeQuery groupByFundingPct() Group by the funding_pct column
 * @method MarketRegimeQuery groupByFundingRate() Group by the funding_rate column
 * @method MarketRegimeQuery groupByDepthImbalance() Group by the depth_imbalance column
 * @method MarketRegimeQuery groupByDepthImbalanceAvg() Group by the depth_imbalance_avg column
 * @method MarketRegimeQuery groupByDateCreation() Group by the date_creation column
 * @method MarketRegimeQuery groupByDateModification() Group by the date_modification column
 * @method MarketRegimeQuery groupByIdGroupCreation() Group by the id_group_creation column
 * @method MarketRegimeQuery groupByIdCreation() Group by the id_creation column
 * @method MarketRegimeQuery groupByIdModification() Group by the id_modification column
 *
 * @method MarketRegimeQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method MarketRegimeQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method MarketRegimeQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method MarketRegimeQuery leftJoinAuthyGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroup relation
 * @method MarketRegimeQuery rightJoinAuthyGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroup relation
 * @method MarketRegimeQuery innerJoinAuthyGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroup relation
 *
 * @method MarketRegimeQuery leftJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method MarketRegimeQuery rightJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method MarketRegimeQuery innerJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdCreation relation
 *
 * @method MarketRegimeQuery leftJoinAuthyRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method MarketRegimeQuery rightJoinAuthyRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method MarketRegimeQuery innerJoinAuthyRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdModification relation
 *
 * @method MarketRegime findOne(?PropelPDO $con = null) Return the first MarketRegime matching the query
 * @method MarketRegime findOneOrCreate(?PropelPDO $con = null) Return the first MarketRegime matching the query, or a new MarketRegime object populated from the query conditions when no match is found
 *
 * @method MarketRegime findOneBySymbol(string $symbol) Return the first MarketRegime filtered by the symbol column
 * @method MarketRegime findOneByTf(string $tf) Return the first MarketRegime filtered by the tf column
 * @method MarketRegime findOneByPrice(string $price) Return the first MarketRegime filtered by the price column
 * @method MarketRegime findOneByTrend(int $trend) Return the first MarketRegime filtered by the trend column
 * @method MarketRegime findOneByRsi14(string $rsi14) Return the first MarketRegime filtered by the rsi14 column
 * @method MarketRegime findOneByAtrPct(string $atr_pct) Return the first MarketRegime filtered by the atr_pct column
 * @method MarketRegime findOneByAdx14(string $adx14) Return the first MarketRegime filtered by the adx14 column
 * @method MarketRegime findOneByAtrPctRank(string $atr_pct_rank) Return the first MarketRegime filtered by the atr_pct_rank column
 * @method MarketRegime findOneByTakerBuyRatio(string $taker_buy_ratio) Return the first MarketRegime filtered by the taker_buy_ratio column
 * @method MarketRegime findOneByVolZscore(string $vol_zscore) Return the first MarketRegime filtered by the vol_zscore column
 * @method MarketRegime findOneByEr20(string $er20) Return the first MarketRegime filtered by the er20 column
 * @method MarketRegime findOneByChop14(string $chop14) Return the first MarketRegime filtered by the chop14 column
 * @method MarketRegime findOneByFundingPct(string $funding_pct) Return the first MarketRegime filtered by the funding_pct column
 * @method MarketRegime findOneByFundingRate(string $funding_rate) Return the first MarketRegime filtered by the funding_rate column
 * @method MarketRegime findOneByDepthImbalance(string $depth_imbalance) Return the first MarketRegime filtered by the depth_imbalance column
 * @method MarketRegime findOneByDepthImbalanceAvg(string $depth_imbalance_avg) Return the first MarketRegime filtered by the depth_imbalance_avg column
 * @method MarketRegime findOneByDateCreation(string $date_creation) Return the first MarketRegime filtered by the date_creation column
 * @method MarketRegime findOneByDateModification(string $date_modification) Return the first MarketRegime filtered by the date_modification column
 * @method MarketRegime findOneByIdGroupCreation(int $id_group_creation) Return the first MarketRegime filtered by the id_group_creation column
 * @method MarketRegime findOneByIdCreation(int $id_creation) Return the first MarketRegime filtered by the id_creation column
 * @method MarketRegime findOneByIdModification(int $id_modification) Return the first MarketRegime filtered by the id_modification column
 *
 * @method array findByIdMarketRegime(int $id_market_regime) Return MarketRegime objects filtered by the id_market_regime column
 * @method array findBySymbol(string $symbol) Return MarketRegime objects filtered by the symbol column
 * @method array findByTf(string $tf) Return MarketRegime objects filtered by the tf column
 * @method array findByPrice(string $price) Return MarketRegime objects filtered by the price column
 * @method array findByTrend(int $trend) Return MarketRegime objects filtered by the trend column
 * @method array findByRsi14(string $rsi14) Return MarketRegime objects filtered by the rsi14 column
 * @method array findByAtrPct(string $atr_pct) Return MarketRegime objects filtered by the atr_pct column
 * @method array findByAdx14(string $adx14) Return MarketRegime objects filtered by the adx14 column
 * @method array findByAtrPctRank(string $atr_pct_rank) Return MarketRegime objects filtered by the atr_pct_rank column
 * @method array findByTakerBuyRatio(string $taker_buy_ratio) Return MarketRegime objects filtered by the taker_buy_ratio column
 * @method array findByVolZscore(string $vol_zscore) Return MarketRegime objects filtered by the vol_zscore column
 * @method array findByEr20(string $er20) Return MarketRegime objects filtered by the er20 column
 * @method array findByChop14(string $chop14) Return MarketRegime objects filtered by the chop14 column
 * @method array findByFundingPct(string $funding_pct) Return MarketRegime objects filtered by the funding_pct column
 * @method array findByFundingRate(string $funding_rate) Return MarketRegime objects filtered by the funding_rate column
 * @method array findByDepthImbalance(string $depth_imbalance) Return MarketRegime objects filtered by the depth_imbalance column
 * @method array findByDepthImbalanceAvg(string $depth_imbalance_avg) Return MarketRegime objects filtered by the depth_imbalance_avg column
 * @method array findByDateCreation(string $date_creation) Return MarketRegime objects filtered by the date_creation column
 * @method array findByDateModification(string $date_modification) Return MarketRegime objects filtered by the date_modification column
 * @method array findByIdGroupCreation(int $id_group_creation) Return MarketRegime objects filtered by the id_group_creation column
 * @method array findByIdCreation(int $id_creation) Return MarketRegime objects filtered by the id_creation column
 * @method array findByIdModification(int $id_modification) Return MarketRegime objects filtered by the id_modification column
 *
 * @package    propel.generator..om
 */
abstract class BaseMarketRegimeQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseMarketRegimeQuery object.
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
            $modelName = 'App\\MarketRegime';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new MarketRegimeQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   MarketRegimeQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return MarketRegimeQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof MarketRegimeQuery) {
            return $criteria;
        }
        $query = new MarketRegimeQuery(null, null, $modelAlias);

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
     * @return   MarketRegime|MarketRegime[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = MarketRegimePeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(MarketRegimePeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 MarketRegime A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdMarketRegime($key, $con = null)
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
     * @return                 MarketRegime A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_market_regime`, `symbol`, `tf`, `price`, `trend`, `rsi14`, `atr_pct`, `adx14`, `atr_pct_rank`, `taker_buy_ratio`, `vol_zscore`, `er20`, `chop14`, `funding_pct`, `funding_rate`, `depth_imbalance`, `depth_imbalance_avg`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification` FROM `market_regime` WHERE `id_market_regime` = :p0';
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
            $obj = new MarketRegime();
            $obj->hydrate($row);
            MarketRegimePeer::addInstanceToPool($obj, (string) $key);
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
     * @return MarketRegime|MarketRegime[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|MarketRegime[]|mixed the list of results, formatted by the current formatter
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
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(MarketRegimePeer::ID_MARKET_REGIME, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(MarketRegimePeer::ID_MARKET_REGIME, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id_market_regime column
     *
     * Example usage:
     * <code>
     * $query->filterByIdMarketRegime(1234); // WHERE id_market_regime = 1234
     * $query->filterByIdMarketRegime(array(12, 34)); // WHERE id_market_regime IN (12, 34)
     * $query->filterByIdMarketRegime(array('min' => 12)); // WHERE id_market_regime >= 12
     * $query->filterByIdMarketRegime(array('max' => 12)); // WHERE id_market_regime <= 12
     * </code>
     *
     * @param     mixed $idMarketRegime The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByIdMarketRegime($idMarketRegime = null, $comparison = null)
    {
        if (is_array($idMarketRegime)) {
            $useMinMax = false;
            if (isset($idMarketRegime['min'])) {
                $this->addUsingAlias(MarketRegimePeer::ID_MARKET_REGIME, $idMarketRegime['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idMarketRegime['max'])) {
                $this->addUsingAlias(MarketRegimePeer::ID_MARKET_REGIME, $idMarketRegime['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::ID_MARKET_REGIME, $idMarketRegime, $comparison);
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
     * @return MarketRegimeQuery The current query, for fluid interface
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

        return $this->addUsingAlias(MarketRegimePeer::SYMBOL, $symbol, $comparison);
    }

    /**
     * Filter the query on the tf column
     *
     * Example usage:
     * <code>
     * $query->filterByTf('fooValue');   // WHERE tf = 'fooValue'
     * $query->filterByTf('%fooValue%'); // WHERE tf LIKE '%fooValue%'
     * </code>
     *
     * @param     string $tf The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByTf($tf = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($tf)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $tf)) {
                $tf = str_replace('*', '%', $tf);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::TF, $tf, $comparison);
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
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByPrice($price = null, $comparison = null)
    {
        if (is_array($price)) {
            $useMinMax = false;
            if (isset($price['min'])) {
                $this->addUsingAlias(MarketRegimePeer::PRICE, $price['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($price['max'])) {
                $this->addUsingAlias(MarketRegimePeer::PRICE, $price['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::PRICE, $price, $comparison);
    }

    /**
     * Filter the query on the trend column
     *
     * @param     mixed $trend The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketRegimeQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByTrend($trend = null, $comparison = null)
    {
        if (is_scalar($trend)) {
            $trend = MarketRegimePeer::getSqlValueForEnum(MarketRegimePeer::TREND, $trend);
        } elseif (is_array($trend)) {
            $convertedValues = array();
            foreach ($trend as $value) {
                $convertedValues[] = MarketRegimePeer::getSqlValueForEnum(MarketRegimePeer::TREND, $value);
            }
            $trend = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::TREND, $trend, $comparison);
    }

    /**
     * Filter the query on the rsi14 column
     *
     * Example usage:
     * <code>
     * $query->filterByRsi14(1234); // WHERE rsi14 = 1234
     * $query->filterByRsi14(array(12, 34)); // WHERE rsi14 IN (12, 34)
     * $query->filterByRsi14(array('min' => 12)); // WHERE rsi14 >= 12
     * $query->filterByRsi14(array('max' => 12)); // WHERE rsi14 <= 12
     * </code>
     *
     * @param     mixed $rsi14 The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByRsi14($rsi14 = null, $comparison = null)
    {
        if (is_array($rsi14)) {
            $useMinMax = false;
            if (isset($rsi14['min'])) {
                $this->addUsingAlias(MarketRegimePeer::RSI14, $rsi14['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($rsi14['max'])) {
                $this->addUsingAlias(MarketRegimePeer::RSI14, $rsi14['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::RSI14, $rsi14, $comparison);
    }

    /**
     * Filter the query on the atr_pct column
     *
     * Example usage:
     * <code>
     * $query->filterByAtrPct(1234); // WHERE atr_pct = 1234
     * $query->filterByAtrPct(array(12, 34)); // WHERE atr_pct IN (12, 34)
     * $query->filterByAtrPct(array('min' => 12)); // WHERE atr_pct >= 12
     * $query->filterByAtrPct(array('max' => 12)); // WHERE atr_pct <= 12
     * </code>
     *
     * @param     mixed $atrPct The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByAtrPct($atrPct = null, $comparison = null)
    {
        if (is_array($atrPct)) {
            $useMinMax = false;
            if (isset($atrPct['min'])) {
                $this->addUsingAlias(MarketRegimePeer::ATR_PCT, $atrPct['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($atrPct['max'])) {
                $this->addUsingAlias(MarketRegimePeer::ATR_PCT, $atrPct['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::ATR_PCT, $atrPct, $comparison);
    }

    /**
     * Filter the query on the adx14 column
     *
     * Example usage:
     * <code>
     * $query->filterByAdx14(1234); // WHERE adx14 = 1234
     * $query->filterByAdx14(array(12, 34)); // WHERE adx14 IN (12, 34)
     * $query->filterByAdx14(array('min' => 12)); // WHERE adx14 >= 12
     * $query->filterByAdx14(array('max' => 12)); // WHERE adx14 <= 12
     * </code>
     *
     * @param     mixed $adx14 The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByAdx14($adx14 = null, $comparison = null)
    {
        if (is_array($adx14)) {
            $useMinMax = false;
            if (isset($adx14['min'])) {
                $this->addUsingAlias(MarketRegimePeer::ADX14, $adx14['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($adx14['max'])) {
                $this->addUsingAlias(MarketRegimePeer::ADX14, $adx14['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::ADX14, $adx14, $comparison);
    }

    /**
     * Filter the query on the atr_pct_rank column
     *
     * Example usage:
     * <code>
     * $query->filterByAtrPctRank(1234); // WHERE atr_pct_rank = 1234
     * $query->filterByAtrPctRank(array(12, 34)); // WHERE atr_pct_rank IN (12, 34)
     * $query->filterByAtrPctRank(array('min' => 12)); // WHERE atr_pct_rank >= 12
     * $query->filterByAtrPctRank(array('max' => 12)); // WHERE atr_pct_rank <= 12
     * </code>
     *
     * @param     mixed $atrPctRank The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByAtrPctRank($atrPctRank = null, $comparison = null)
    {
        if (is_array($atrPctRank)) {
            $useMinMax = false;
            if (isset($atrPctRank['min'])) {
                $this->addUsingAlias(MarketRegimePeer::ATR_PCT_RANK, $atrPctRank['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($atrPctRank['max'])) {
                $this->addUsingAlias(MarketRegimePeer::ATR_PCT_RANK, $atrPctRank['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::ATR_PCT_RANK, $atrPctRank, $comparison);
    }

    /**
     * Filter the query on the taker_buy_ratio column
     *
     * Example usage:
     * <code>
     * $query->filterByTakerBuyRatio(1234); // WHERE taker_buy_ratio = 1234
     * $query->filterByTakerBuyRatio(array(12, 34)); // WHERE taker_buy_ratio IN (12, 34)
     * $query->filterByTakerBuyRatio(array('min' => 12)); // WHERE taker_buy_ratio >= 12
     * $query->filterByTakerBuyRatio(array('max' => 12)); // WHERE taker_buy_ratio <= 12
     * </code>
     *
     * @param     mixed $takerBuyRatio The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByTakerBuyRatio($takerBuyRatio = null, $comparison = null)
    {
        if (is_array($takerBuyRatio)) {
            $useMinMax = false;
            if (isset($takerBuyRatio['min'])) {
                $this->addUsingAlias(MarketRegimePeer::TAKER_BUY_RATIO, $takerBuyRatio['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($takerBuyRatio['max'])) {
                $this->addUsingAlias(MarketRegimePeer::TAKER_BUY_RATIO, $takerBuyRatio['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::TAKER_BUY_RATIO, $takerBuyRatio, $comparison);
    }

    /**
     * Filter the query on the vol_zscore column
     *
     * Example usage:
     * <code>
     * $query->filterByVolZscore(1234); // WHERE vol_zscore = 1234
     * $query->filterByVolZscore(array(12, 34)); // WHERE vol_zscore IN (12, 34)
     * $query->filterByVolZscore(array('min' => 12)); // WHERE vol_zscore >= 12
     * $query->filterByVolZscore(array('max' => 12)); // WHERE vol_zscore <= 12
     * </code>
     *
     * @param     mixed $volZscore The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByVolZscore($volZscore = null, $comparison = null)
    {
        if (is_array($volZscore)) {
            $useMinMax = false;
            if (isset($volZscore['min'])) {
                $this->addUsingAlias(MarketRegimePeer::VOL_ZSCORE, $volZscore['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($volZscore['max'])) {
                $this->addUsingAlias(MarketRegimePeer::VOL_ZSCORE, $volZscore['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::VOL_ZSCORE, $volZscore, $comparison);
    }

    /**
     * Filter the query on the er20 column
     *
     * Example usage:
     * <code>
     * $query->filterByEr20(1234); // WHERE er20 = 1234
     * $query->filterByEr20(array(12, 34)); // WHERE er20 IN (12, 34)
     * $query->filterByEr20(array('min' => 12)); // WHERE er20 >= 12
     * $query->filterByEr20(array('max' => 12)); // WHERE er20 <= 12
     * </code>
     *
     * @param     mixed $er20 The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByEr20($er20 = null, $comparison = null)
    {
        if (is_array($er20)) {
            $useMinMax = false;
            if (isset($er20['min'])) {
                $this->addUsingAlias(MarketRegimePeer::ER20, $er20['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($er20['max'])) {
                $this->addUsingAlias(MarketRegimePeer::ER20, $er20['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::ER20, $er20, $comparison);
    }

    /**
     * Filter the query on the chop14 column
     *
     * Example usage:
     * <code>
     * $query->filterByChop14(1234); // WHERE chop14 = 1234
     * $query->filterByChop14(array(12, 34)); // WHERE chop14 IN (12, 34)
     * $query->filterByChop14(array('min' => 12)); // WHERE chop14 >= 12
     * $query->filterByChop14(array('max' => 12)); // WHERE chop14 <= 12
     * </code>
     *
     * @param     mixed $chop14 The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByChop14($chop14 = null, $comparison = null)
    {
        if (is_array($chop14)) {
            $useMinMax = false;
            if (isset($chop14['min'])) {
                $this->addUsingAlias(MarketRegimePeer::CHOP14, $chop14['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($chop14['max'])) {
                $this->addUsingAlias(MarketRegimePeer::CHOP14, $chop14['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::CHOP14, $chop14, $comparison);
    }

    /**
     * Filter the query on the funding_pct column
     *
     * Example usage:
     * <code>
     * $query->filterByFundingPct(1234); // WHERE funding_pct = 1234
     * $query->filterByFundingPct(array(12, 34)); // WHERE funding_pct IN (12, 34)
     * $query->filterByFundingPct(array('min' => 12)); // WHERE funding_pct >= 12
     * $query->filterByFundingPct(array('max' => 12)); // WHERE funding_pct <= 12
     * </code>
     *
     * @param     mixed $fundingPct The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByFundingPct($fundingPct = null, $comparison = null)
    {
        if (is_array($fundingPct)) {
            $useMinMax = false;
            if (isset($fundingPct['min'])) {
                $this->addUsingAlias(MarketRegimePeer::FUNDING_PCT, $fundingPct['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($fundingPct['max'])) {
                $this->addUsingAlias(MarketRegimePeer::FUNDING_PCT, $fundingPct['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::FUNDING_PCT, $fundingPct, $comparison);
    }

    /**
     * Filter the query on the funding_rate column
     *
     * Example usage:
     * <code>
     * $query->filterByFundingRate(1234); // WHERE funding_rate = 1234
     * $query->filterByFundingRate(array(12, 34)); // WHERE funding_rate IN (12, 34)
     * $query->filterByFundingRate(array('min' => 12)); // WHERE funding_rate >= 12
     * $query->filterByFundingRate(array('max' => 12)); // WHERE funding_rate <= 12
     * </code>
     *
     * @param     mixed $fundingRate The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByFundingRate($fundingRate = null, $comparison = null)
    {
        if (is_array($fundingRate)) {
            $useMinMax = false;
            if (isset($fundingRate['min'])) {
                $this->addUsingAlias(MarketRegimePeer::FUNDING_RATE, $fundingRate['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($fundingRate['max'])) {
                $this->addUsingAlias(MarketRegimePeer::FUNDING_RATE, $fundingRate['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::FUNDING_RATE, $fundingRate, $comparison);
    }

    /**
     * Filter the query on the depth_imbalance column
     *
     * Example usage:
     * <code>
     * $query->filterByDepthImbalance(1234); // WHERE depth_imbalance = 1234
     * $query->filterByDepthImbalance(array(12, 34)); // WHERE depth_imbalance IN (12, 34)
     * $query->filterByDepthImbalance(array('min' => 12)); // WHERE depth_imbalance >= 12
     * $query->filterByDepthImbalance(array('max' => 12)); // WHERE depth_imbalance <= 12
     * </code>
     *
     * @param     mixed $depthImbalance The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByDepthImbalance($depthImbalance = null, $comparison = null)
    {
        if (is_array($depthImbalance)) {
            $useMinMax = false;
            if (isset($depthImbalance['min'])) {
                $this->addUsingAlias(MarketRegimePeer::DEPTH_IMBALANCE, $depthImbalance['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($depthImbalance['max'])) {
                $this->addUsingAlias(MarketRegimePeer::DEPTH_IMBALANCE, $depthImbalance['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::DEPTH_IMBALANCE, $depthImbalance, $comparison);
    }

    /**
     * Filter the query on the depth_imbalance_avg column
     *
     * Example usage:
     * <code>
     * $query->filterByDepthImbalanceAvg(1234); // WHERE depth_imbalance_avg = 1234
     * $query->filterByDepthImbalanceAvg(array(12, 34)); // WHERE depth_imbalance_avg IN (12, 34)
     * $query->filterByDepthImbalanceAvg(array('min' => 12)); // WHERE depth_imbalance_avg >= 12
     * $query->filterByDepthImbalanceAvg(array('max' => 12)); // WHERE depth_imbalance_avg <= 12
     * </code>
     *
     * @param     mixed $depthImbalanceAvg The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByDepthImbalanceAvg($depthImbalanceAvg = null, $comparison = null)
    {
        if (is_array($depthImbalanceAvg)) {
            $useMinMax = false;
            if (isset($depthImbalanceAvg['min'])) {
                $this->addUsingAlias(MarketRegimePeer::DEPTH_IMBALANCE_AVG, $depthImbalanceAvg['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($depthImbalanceAvg['max'])) {
                $this->addUsingAlias(MarketRegimePeer::DEPTH_IMBALANCE_AVG, $depthImbalanceAvg['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::DEPTH_IMBALANCE_AVG, $depthImbalanceAvg, $comparison);
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
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByDateCreation($dateCreation = null, $comparison = null)
    {
        if (is_array($dateCreation)) {
            $useMinMax = false;
            if (isset($dateCreation['min'])) {
                $this->addUsingAlias(MarketRegimePeer::DATE_CREATION, $dateCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateCreation['max'])) {
                $this->addUsingAlias(MarketRegimePeer::DATE_CREATION, $dateCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::DATE_CREATION, $dateCreation, $comparison);
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
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByDateModification($dateModification = null, $comparison = null)
    {
        if (is_array($dateModification)) {
            $useMinMax = false;
            if (isset($dateModification['min'])) {
                $this->addUsingAlias(MarketRegimePeer::DATE_MODIFICATION, $dateModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateModification['max'])) {
                $this->addUsingAlias(MarketRegimePeer::DATE_MODIFICATION, $dateModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::DATE_MODIFICATION, $dateModification, $comparison);
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
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByIdGroupCreation($idGroupCreation = null, $comparison = null)
    {
        if (is_array($idGroupCreation)) {
            $useMinMax = false;
            if (isset($idGroupCreation['min'])) {
                $this->addUsingAlias(MarketRegimePeer::ID_GROUP_CREATION, $idGroupCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGroupCreation['max'])) {
                $this->addUsingAlias(MarketRegimePeer::ID_GROUP_CREATION, $idGroupCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::ID_GROUP_CREATION, $idGroupCreation, $comparison);
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
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByIdCreation($idCreation = null, $comparison = null)
    {
        if (is_array($idCreation)) {
            $useMinMax = false;
            if (isset($idCreation['min'])) {
                $this->addUsingAlias(MarketRegimePeer::ID_CREATION, $idCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idCreation['max'])) {
                $this->addUsingAlias(MarketRegimePeer::ID_CREATION, $idCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::ID_CREATION, $idCreation, $comparison);
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
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function filterByIdModification($idModification = null, $comparison = null)
    {
        if (is_array($idModification)) {
            $useMinMax = false;
            if (isset($idModification['min'])) {
                $this->addUsingAlias(MarketRegimePeer::ID_MODIFICATION, $idModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idModification['max'])) {
                $this->addUsingAlias(MarketRegimePeer::ID_MODIFICATION, $idModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketRegimePeer::ID_MODIFICATION, $idModification, $comparison);
    }

    /**
     * Filter the query by a related AuthyGroup object
     *
     * @param   AuthyGroup|PropelObjectCollection $authyGroup The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 MarketRegimeQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroup($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(MarketRegimePeer::ID_GROUP_CREATION, $authyGroup->getIdAuthyGroup(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(MarketRegimePeer::ID_GROUP_CREATION, $authyGroup->toKeyValue('PrimaryKey', 'IdAuthyGroup'), $comparison);
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
     * @return MarketRegimeQuery The current query, for fluid interface
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
     * @return                 MarketRegimeQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdCreation($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(MarketRegimePeer::ID_CREATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(MarketRegimePeer::ID_CREATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return MarketRegimeQuery The current query, for fluid interface
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
     * @return                 MarketRegimeQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdModification($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(MarketRegimePeer::ID_MODIFICATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(MarketRegimePeer::ID_MODIFICATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return MarketRegimeQuery The current query, for fluid interface
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
     * @param   MarketRegime $marketRegime Object to remove from the list of results
     *
     * @return MarketRegimeQuery The current query, for fluid interface
     */
    public function prune($marketRegime = null)
    {
        if ($marketRegime) {
            $this->addUsingAlias(MarketRegimePeer::ID_MARKET_REGIME, $marketRegime->getIdMarketRegime(), Criteria::NOT_EQUAL);
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
                    \ApiGoat\Utility\TableVersion::bump('market_regime');
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
                    \ApiGoat\Utility\TableVersion::bump('market_regime');
                }

        return $this->postUpdate($affectedRows, $con);
    }

    // add_tablestamp behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     MarketRegimeQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7){
        return $this->addUsingAlias(MarketRegimePeer::DATE_MODIFICATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     MarketRegimeQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst(){
        return $this->addDescendingOrderByColumn(MarketRegimePeer::DATE_MODIFICATION);
    }

    /**
     * Order by update date asc
     *
     * @return     MarketRegimeQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst(){
        return $this->addAscendingOrderByColumn(MarketRegimePeer::DATE_MODIFICATION);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     MarketRegimeQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7){
        return $this->addUsingAlias(MarketRegimePeer::DATE_CREATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return     MarketRegimeQuery The current query, for fluid interface
     */
    public function lastCreatedFirst(){
        return $this->addDescendingOrderByColumn(MarketRegimePeer::DATE_CREATION);
    }

    /**
     * Order by create date asc
     *
     * @return     MarketRegimeQuery The current query, for fluid interface
     */
    public function firstCreatedFirst(){
        return $this->addAscendingOrderByColumn(MarketRegimePeer::DATE_CREATION);
    }
}
