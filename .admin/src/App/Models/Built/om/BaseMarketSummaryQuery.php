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
use App\MarketSummary;
use App\MarketSummaryPeer;
use App\MarketSummaryQuery;

/**
 * Base class that represents a query for the 'market_summary' table.
 *
 * Market Data
 *
 * @method MarketSummaryQuery orderByIdMarketSummary($order = Criteria::ASC) Order by the id_market_summary column
 * @method MarketSummaryQuery orderBySymbol($order = Criteria::ASC) Order by the symbol column
 * @method MarketSummaryQuery orderByTf($order = Criteria::ASC) Order by the tf column
 * @method MarketSummaryQuery orderByPrice($order = Criteria::ASC) Order by the price column
 * @method MarketSummaryQuery orderByEma20($order = Criteria::ASC) Order by the ema20 column
 * @method MarketSummaryQuery orderByEma50($order = Criteria::ASC) Order by the ema50 column
 * @method MarketSummaryQuery orderByEma200($order = Criteria::ASC) Order by the ema200 column
 * @method MarketSummaryQuery orderByRsi14($order = Criteria::ASC) Order by the rsi14 column
 * @method MarketSummaryQuery orderByAtr14($order = Criteria::ASC) Order by the atr14 column
 * @method MarketSummaryQuery orderByAtrPct($order = Criteria::ASC) Order by the atr_pct column
 * @method MarketSummaryQuery orderByTrend($order = Criteria::ASC) Order by the trend column
 * @method MarketSummaryQuery orderBySwingHigh($order = Criteria::ASC) Order by the swing_high column
 * @method MarketSummaryQuery orderBySwingLow($order = Criteria::ASC) Order by the swing_low column
 * @method MarketSummaryQuery orderByCandlesUsed($order = Criteria::ASC) Order by the candles_used column
 * @method MarketSummaryQuery orderByRecentCandles($order = Criteria::ASC) Order by the recent_candles column
 * @method MarketSummaryQuery orderByFundingRate($order = Criteria::ASC) Order by the funding_rate column
 * @method MarketSummaryQuery orderByDepthImbalance($order = Criteria::ASC) Order by the depth_imbalance column
 * @method MarketSummaryQuery orderByDepthImbalanceAvg($order = Criteria::ASC) Order by the depth_imbalance_avg column
 * @method MarketSummaryQuery orderByAdx14($order = Criteria::ASC) Order by the adx14 column
 * @method MarketSummaryQuery orderByAtrPctRank($order = Criteria::ASC) Order by the atr_pct_rank column
 * @method MarketSummaryQuery orderByTakerBuyRatio($order = Criteria::ASC) Order by the taker_buy_ratio column
 * @method MarketSummaryQuery orderByVolZscore($order = Criteria::ASC) Order by the vol_zscore column
 * @method MarketSummaryQuery orderByEr20($order = Criteria::ASC) Order by the er20 column
 * @method MarketSummaryQuery orderByChop14($order = Criteria::ASC) Order by the chop14 column
 * @method MarketSummaryQuery orderByFundingPct($order = Criteria::ASC) Order by the funding_pct column
 * @method MarketSummaryQuery orderByComputedAt($order = Criteria::ASC) Order by the computed_at column
 * @method MarketSummaryQuery orderByDateCreation($order = Criteria::ASC) Order by the date_creation column
 * @method MarketSummaryQuery orderByDateModification($order = Criteria::ASC) Order by the date_modification column
 * @method MarketSummaryQuery orderByIdGroupCreation($order = Criteria::ASC) Order by the id_group_creation column
 * @method MarketSummaryQuery orderByIdCreation($order = Criteria::ASC) Order by the id_creation column
 * @method MarketSummaryQuery orderByIdModification($order = Criteria::ASC) Order by the id_modification column
 *
 * @method MarketSummaryQuery groupByIdMarketSummary() Group by the id_market_summary column
 * @method MarketSummaryQuery groupBySymbol() Group by the symbol column
 * @method MarketSummaryQuery groupByTf() Group by the tf column
 * @method MarketSummaryQuery groupByPrice() Group by the price column
 * @method MarketSummaryQuery groupByEma20() Group by the ema20 column
 * @method MarketSummaryQuery groupByEma50() Group by the ema50 column
 * @method MarketSummaryQuery groupByEma200() Group by the ema200 column
 * @method MarketSummaryQuery groupByRsi14() Group by the rsi14 column
 * @method MarketSummaryQuery groupByAtr14() Group by the atr14 column
 * @method MarketSummaryQuery groupByAtrPct() Group by the atr_pct column
 * @method MarketSummaryQuery groupByTrend() Group by the trend column
 * @method MarketSummaryQuery groupBySwingHigh() Group by the swing_high column
 * @method MarketSummaryQuery groupBySwingLow() Group by the swing_low column
 * @method MarketSummaryQuery groupByCandlesUsed() Group by the candles_used column
 * @method MarketSummaryQuery groupByRecentCandles() Group by the recent_candles column
 * @method MarketSummaryQuery groupByFundingRate() Group by the funding_rate column
 * @method MarketSummaryQuery groupByDepthImbalance() Group by the depth_imbalance column
 * @method MarketSummaryQuery groupByDepthImbalanceAvg() Group by the depth_imbalance_avg column
 * @method MarketSummaryQuery groupByAdx14() Group by the adx14 column
 * @method MarketSummaryQuery groupByAtrPctRank() Group by the atr_pct_rank column
 * @method MarketSummaryQuery groupByTakerBuyRatio() Group by the taker_buy_ratio column
 * @method MarketSummaryQuery groupByVolZscore() Group by the vol_zscore column
 * @method MarketSummaryQuery groupByEr20() Group by the er20 column
 * @method MarketSummaryQuery groupByChop14() Group by the chop14 column
 * @method MarketSummaryQuery groupByFundingPct() Group by the funding_pct column
 * @method MarketSummaryQuery groupByComputedAt() Group by the computed_at column
 * @method MarketSummaryQuery groupByDateCreation() Group by the date_creation column
 * @method MarketSummaryQuery groupByDateModification() Group by the date_modification column
 * @method MarketSummaryQuery groupByIdGroupCreation() Group by the id_group_creation column
 * @method MarketSummaryQuery groupByIdCreation() Group by the id_creation column
 * @method MarketSummaryQuery groupByIdModification() Group by the id_modification column
 *
 * @method MarketSummaryQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method MarketSummaryQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method MarketSummaryQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method MarketSummaryQuery leftJoinAuthyGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroup relation
 * @method MarketSummaryQuery rightJoinAuthyGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroup relation
 * @method MarketSummaryQuery innerJoinAuthyGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroup relation
 *
 * @method MarketSummaryQuery leftJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method MarketSummaryQuery rightJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method MarketSummaryQuery innerJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdCreation relation
 *
 * @method MarketSummaryQuery leftJoinAuthyRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method MarketSummaryQuery rightJoinAuthyRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method MarketSummaryQuery innerJoinAuthyRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdModification relation
 *
 * @method MarketSummary findOne(?PropelPDO $con = null) Return the first MarketSummary matching the query
 * @method MarketSummary findOneOrCreate(?PropelPDO $con = null) Return the first MarketSummary matching the query, or a new MarketSummary object populated from the query conditions when no match is found
 *
 * @method MarketSummary findOneBySymbol(string $symbol) Return the first MarketSummary filtered by the symbol column
 * @method MarketSummary findOneByTf(string $tf) Return the first MarketSummary filtered by the tf column
 * @method MarketSummary findOneByPrice(string $price) Return the first MarketSummary filtered by the price column
 * @method MarketSummary findOneByEma20(string $ema20) Return the first MarketSummary filtered by the ema20 column
 * @method MarketSummary findOneByEma50(string $ema50) Return the first MarketSummary filtered by the ema50 column
 * @method MarketSummary findOneByEma200(string $ema200) Return the first MarketSummary filtered by the ema200 column
 * @method MarketSummary findOneByRsi14(string $rsi14) Return the first MarketSummary filtered by the rsi14 column
 * @method MarketSummary findOneByAtr14(string $atr14) Return the first MarketSummary filtered by the atr14 column
 * @method MarketSummary findOneByAtrPct(string $atr_pct) Return the first MarketSummary filtered by the atr_pct column
 * @method MarketSummary findOneByTrend(int $trend) Return the first MarketSummary filtered by the trend column
 * @method MarketSummary findOneBySwingHigh(string $swing_high) Return the first MarketSummary filtered by the swing_high column
 * @method MarketSummary findOneBySwingLow(string $swing_low) Return the first MarketSummary filtered by the swing_low column
 * @method MarketSummary findOneByCandlesUsed(int $candles_used) Return the first MarketSummary filtered by the candles_used column
 * @method MarketSummary findOneByRecentCandles(string $recent_candles) Return the first MarketSummary filtered by the recent_candles column
 * @method MarketSummary findOneByFundingRate(string $funding_rate) Return the first MarketSummary filtered by the funding_rate column
 * @method MarketSummary findOneByDepthImbalance(string $depth_imbalance) Return the first MarketSummary filtered by the depth_imbalance column
 * @method MarketSummary findOneByDepthImbalanceAvg(string $depth_imbalance_avg) Return the first MarketSummary filtered by the depth_imbalance_avg column
 * @method MarketSummary findOneByAdx14(string $adx14) Return the first MarketSummary filtered by the adx14 column
 * @method MarketSummary findOneByAtrPctRank(string $atr_pct_rank) Return the first MarketSummary filtered by the atr_pct_rank column
 * @method MarketSummary findOneByTakerBuyRatio(string $taker_buy_ratio) Return the first MarketSummary filtered by the taker_buy_ratio column
 * @method MarketSummary findOneByVolZscore(string $vol_zscore) Return the first MarketSummary filtered by the vol_zscore column
 * @method MarketSummary findOneByEr20(string $er20) Return the first MarketSummary filtered by the er20 column
 * @method MarketSummary findOneByChop14(string $chop14) Return the first MarketSummary filtered by the chop14 column
 * @method MarketSummary findOneByFundingPct(string $funding_pct) Return the first MarketSummary filtered by the funding_pct column
 * @method MarketSummary findOneByComputedAt(string $computed_at) Return the first MarketSummary filtered by the computed_at column
 * @method MarketSummary findOneByDateCreation(string $date_creation) Return the first MarketSummary filtered by the date_creation column
 * @method MarketSummary findOneByDateModification(string $date_modification) Return the first MarketSummary filtered by the date_modification column
 * @method MarketSummary findOneByIdGroupCreation(int $id_group_creation) Return the first MarketSummary filtered by the id_group_creation column
 * @method MarketSummary findOneByIdCreation(int $id_creation) Return the first MarketSummary filtered by the id_creation column
 * @method MarketSummary findOneByIdModification(int $id_modification) Return the first MarketSummary filtered by the id_modification column
 *
 * @method array findByIdMarketSummary(int $id_market_summary) Return MarketSummary objects filtered by the id_market_summary column
 * @method array findBySymbol(string $symbol) Return MarketSummary objects filtered by the symbol column
 * @method array findByTf(string $tf) Return MarketSummary objects filtered by the tf column
 * @method array findByPrice(string $price) Return MarketSummary objects filtered by the price column
 * @method array findByEma20(string $ema20) Return MarketSummary objects filtered by the ema20 column
 * @method array findByEma50(string $ema50) Return MarketSummary objects filtered by the ema50 column
 * @method array findByEma200(string $ema200) Return MarketSummary objects filtered by the ema200 column
 * @method array findByRsi14(string $rsi14) Return MarketSummary objects filtered by the rsi14 column
 * @method array findByAtr14(string $atr14) Return MarketSummary objects filtered by the atr14 column
 * @method array findByAtrPct(string $atr_pct) Return MarketSummary objects filtered by the atr_pct column
 * @method array findByTrend(int $trend) Return MarketSummary objects filtered by the trend column
 * @method array findBySwingHigh(string $swing_high) Return MarketSummary objects filtered by the swing_high column
 * @method array findBySwingLow(string $swing_low) Return MarketSummary objects filtered by the swing_low column
 * @method array findByCandlesUsed(int $candles_used) Return MarketSummary objects filtered by the candles_used column
 * @method array findByRecentCandles(string $recent_candles) Return MarketSummary objects filtered by the recent_candles column
 * @method array findByFundingRate(string $funding_rate) Return MarketSummary objects filtered by the funding_rate column
 * @method array findByDepthImbalance(string $depth_imbalance) Return MarketSummary objects filtered by the depth_imbalance column
 * @method array findByDepthImbalanceAvg(string $depth_imbalance_avg) Return MarketSummary objects filtered by the depth_imbalance_avg column
 * @method array findByAdx14(string $adx14) Return MarketSummary objects filtered by the adx14 column
 * @method array findByAtrPctRank(string $atr_pct_rank) Return MarketSummary objects filtered by the atr_pct_rank column
 * @method array findByTakerBuyRatio(string $taker_buy_ratio) Return MarketSummary objects filtered by the taker_buy_ratio column
 * @method array findByVolZscore(string $vol_zscore) Return MarketSummary objects filtered by the vol_zscore column
 * @method array findByEr20(string $er20) Return MarketSummary objects filtered by the er20 column
 * @method array findByChop14(string $chop14) Return MarketSummary objects filtered by the chop14 column
 * @method array findByFundingPct(string $funding_pct) Return MarketSummary objects filtered by the funding_pct column
 * @method array findByComputedAt(string $computed_at) Return MarketSummary objects filtered by the computed_at column
 * @method array findByDateCreation(string $date_creation) Return MarketSummary objects filtered by the date_creation column
 * @method array findByDateModification(string $date_modification) Return MarketSummary objects filtered by the date_modification column
 * @method array findByIdGroupCreation(int $id_group_creation) Return MarketSummary objects filtered by the id_group_creation column
 * @method array findByIdCreation(int $id_creation) Return MarketSummary objects filtered by the id_creation column
 * @method array findByIdModification(int $id_modification) Return MarketSummary objects filtered by the id_modification column
 *
 * @package    propel.generator..om
 */
abstract class BaseMarketSummaryQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseMarketSummaryQuery object.
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
            $modelName = 'App\\MarketSummary';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new MarketSummaryQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   MarketSummaryQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return MarketSummaryQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof MarketSummaryQuery) {
            return $criteria;
        }
        $query = new MarketSummaryQuery(null, null, $modelAlias);

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
     * @return   MarketSummary|MarketSummary[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = MarketSummaryPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(MarketSummaryPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 MarketSummary A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdMarketSummary($key, $con = null)
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
     * @return                 MarketSummary A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_market_summary`, `symbol`, `tf`, `price`, `ema20`, `ema50`, `ema200`, `rsi14`, `atr14`, `atr_pct`, `trend`, `swing_high`, `swing_low`, `candles_used`, `recent_candles`, `funding_rate`, `depth_imbalance`, `depth_imbalance_avg`, `adx14`, `atr_pct_rank`, `taker_buy_ratio`, `vol_zscore`, `er20`, `chop14`, `funding_pct`, `computed_at`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification` FROM `market_summary` WHERE `id_market_summary` = :p0';
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
            $obj = new MarketSummary();
            $obj->hydrate($row);
            MarketSummaryPeer::addInstanceToPool($obj, (string) $key);
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
     * @return MarketSummary|MarketSummary[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|MarketSummary[]|mixed the list of results, formatted by the current formatter
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(MarketSummaryPeer::ID_MARKET_SUMMARY, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(MarketSummaryPeer::ID_MARKET_SUMMARY, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id_market_summary column
     *
     * Example usage:
     * <code>
     * $query->filterByIdMarketSummary(1234); // WHERE id_market_summary = 1234
     * $query->filterByIdMarketSummary(array(12, 34)); // WHERE id_market_summary IN (12, 34)
     * $query->filterByIdMarketSummary(array('min' => 12)); // WHERE id_market_summary >= 12
     * $query->filterByIdMarketSummary(array('max' => 12)); // WHERE id_market_summary <= 12
     * </code>
     *
     * @param     mixed $idMarketSummary The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByIdMarketSummary($idMarketSummary = null, $comparison = null)
    {
        if (is_array($idMarketSummary)) {
            $useMinMax = false;
            if (isset($idMarketSummary['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::ID_MARKET_SUMMARY, $idMarketSummary['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idMarketSummary['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::ID_MARKET_SUMMARY, $idMarketSummary['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::ID_MARKET_SUMMARY, $idMarketSummary, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
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

        return $this->addUsingAlias(MarketSummaryPeer::SYMBOL, $symbol, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
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

        return $this->addUsingAlias(MarketSummaryPeer::TF, $tf, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByPrice($price = null, $comparison = null)
    {
        if (is_array($price)) {
            $useMinMax = false;
            if (isset($price['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::PRICE, $price['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($price['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::PRICE, $price['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::PRICE, $price, $comparison);
    }

    /**
     * Filter the query on the ema20 column
     *
     * Example usage:
     * <code>
     * $query->filterByEma20(1234); // WHERE ema20 = 1234
     * $query->filterByEma20(array(12, 34)); // WHERE ema20 IN (12, 34)
     * $query->filterByEma20(array('min' => 12)); // WHERE ema20 >= 12
     * $query->filterByEma20(array('max' => 12)); // WHERE ema20 <= 12
     * </code>
     *
     * @param     mixed $ema20 The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByEma20($ema20 = null, $comparison = null)
    {
        if (is_array($ema20)) {
            $useMinMax = false;
            if (isset($ema20['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::EMA20, $ema20['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($ema20['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::EMA20, $ema20['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::EMA20, $ema20, $comparison);
    }

    /**
     * Filter the query on the ema50 column
     *
     * Example usage:
     * <code>
     * $query->filterByEma50(1234); // WHERE ema50 = 1234
     * $query->filterByEma50(array(12, 34)); // WHERE ema50 IN (12, 34)
     * $query->filterByEma50(array('min' => 12)); // WHERE ema50 >= 12
     * $query->filterByEma50(array('max' => 12)); // WHERE ema50 <= 12
     * </code>
     *
     * @param     mixed $ema50 The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByEma50($ema50 = null, $comparison = null)
    {
        if (is_array($ema50)) {
            $useMinMax = false;
            if (isset($ema50['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::EMA50, $ema50['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($ema50['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::EMA50, $ema50['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::EMA50, $ema50, $comparison);
    }

    /**
     * Filter the query on the ema200 column
     *
     * Example usage:
     * <code>
     * $query->filterByEma200(1234); // WHERE ema200 = 1234
     * $query->filterByEma200(array(12, 34)); // WHERE ema200 IN (12, 34)
     * $query->filterByEma200(array('min' => 12)); // WHERE ema200 >= 12
     * $query->filterByEma200(array('max' => 12)); // WHERE ema200 <= 12
     * </code>
     *
     * @param     mixed $ema200 The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByEma200($ema200 = null, $comparison = null)
    {
        if (is_array($ema200)) {
            $useMinMax = false;
            if (isset($ema200['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::EMA200, $ema200['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($ema200['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::EMA200, $ema200['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::EMA200, $ema200, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByRsi14($rsi14 = null, $comparison = null)
    {
        if (is_array($rsi14)) {
            $useMinMax = false;
            if (isset($rsi14['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::RSI14, $rsi14['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($rsi14['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::RSI14, $rsi14['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::RSI14, $rsi14, $comparison);
    }

    /**
     * Filter the query on the atr14 column
     *
     * Example usage:
     * <code>
     * $query->filterByAtr14(1234); // WHERE atr14 = 1234
     * $query->filterByAtr14(array(12, 34)); // WHERE atr14 IN (12, 34)
     * $query->filterByAtr14(array('min' => 12)); // WHERE atr14 >= 12
     * $query->filterByAtr14(array('max' => 12)); // WHERE atr14 <= 12
     * </code>
     *
     * @param     mixed $atr14 The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByAtr14($atr14 = null, $comparison = null)
    {
        if (is_array($atr14)) {
            $useMinMax = false;
            if (isset($atr14['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::ATR14, $atr14['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($atr14['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::ATR14, $atr14['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::ATR14, $atr14, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByAtrPct($atrPct = null, $comparison = null)
    {
        if (is_array($atrPct)) {
            $useMinMax = false;
            if (isset($atrPct['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::ATR_PCT, $atrPct['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($atrPct['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::ATR_PCT, $atrPct['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::ATR_PCT, $atrPct, $comparison);
    }

    /**
     * Filter the query on the trend column
     *
     * @param     mixed $trend The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketSummaryQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByTrend($trend = null, $comparison = null)
    {
        if (is_scalar($trend)) {
            $trend = MarketSummaryPeer::getSqlValueForEnum(MarketSummaryPeer::TREND, $trend);
        } elseif (is_array($trend)) {
            $convertedValues = array();
            foreach ($trend as $value) {
                $convertedValues[] = MarketSummaryPeer::getSqlValueForEnum(MarketSummaryPeer::TREND, $value);
            }
            $trend = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::TREND, $trend, $comparison);
    }

    /**
     * Filter the query on the swing_high column
     *
     * Example usage:
     * <code>
     * $query->filterBySwingHigh(1234); // WHERE swing_high = 1234
     * $query->filterBySwingHigh(array(12, 34)); // WHERE swing_high IN (12, 34)
     * $query->filterBySwingHigh(array('min' => 12)); // WHERE swing_high >= 12
     * $query->filterBySwingHigh(array('max' => 12)); // WHERE swing_high <= 12
     * </code>
     *
     * @param     mixed $swingHigh The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterBySwingHigh($swingHigh = null, $comparison = null)
    {
        if (is_array($swingHigh)) {
            $useMinMax = false;
            if (isset($swingHigh['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::SWING_HIGH, $swingHigh['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($swingHigh['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::SWING_HIGH, $swingHigh['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::SWING_HIGH, $swingHigh, $comparison);
    }

    /**
     * Filter the query on the swing_low column
     *
     * Example usage:
     * <code>
     * $query->filterBySwingLow(1234); // WHERE swing_low = 1234
     * $query->filterBySwingLow(array(12, 34)); // WHERE swing_low IN (12, 34)
     * $query->filterBySwingLow(array('min' => 12)); // WHERE swing_low >= 12
     * $query->filterBySwingLow(array('max' => 12)); // WHERE swing_low <= 12
     * </code>
     *
     * @param     mixed $swingLow The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterBySwingLow($swingLow = null, $comparison = null)
    {
        if (is_array($swingLow)) {
            $useMinMax = false;
            if (isset($swingLow['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::SWING_LOW, $swingLow['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($swingLow['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::SWING_LOW, $swingLow['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::SWING_LOW, $swingLow, $comparison);
    }

    /**
     * Filter the query on the candles_used column
     *
     * Example usage:
     * <code>
     * $query->filterByCandlesUsed(1234); // WHERE candles_used = 1234
     * $query->filterByCandlesUsed(array(12, 34)); // WHERE candles_used IN (12, 34)
     * $query->filterByCandlesUsed(array('min' => 12)); // WHERE candles_used >= 12
     * $query->filterByCandlesUsed(array('max' => 12)); // WHERE candles_used <= 12
     * </code>
     *
     * @param     mixed $candlesUsed The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByCandlesUsed($candlesUsed = null, $comparison = null)
    {
        if (is_array($candlesUsed)) {
            $useMinMax = false;
            if (isset($candlesUsed['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::CANDLES_USED, $candlesUsed['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($candlesUsed['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::CANDLES_USED, $candlesUsed['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::CANDLES_USED, $candlesUsed, $comparison);
    }

    /**
     * Filter the query on the recent_candles column
     *
     * Example usage:
     * <code>
     * $query->filterByRecentCandles('fooValue');   // WHERE recent_candles = 'fooValue'
     * $query->filterByRecentCandles('%fooValue%'); // WHERE recent_candles LIKE '%fooValue%'
     * </code>
     *
     * @param     string $recentCandles The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByRecentCandles($recentCandles = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($recentCandles)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $recentCandles)) {
                $recentCandles = str_replace('*', '%', $recentCandles);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::RECENT_CANDLES, $recentCandles, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByFundingRate($fundingRate = null, $comparison = null)
    {
        if (is_array($fundingRate)) {
            $useMinMax = false;
            if (isset($fundingRate['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::FUNDING_RATE, $fundingRate['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($fundingRate['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::FUNDING_RATE, $fundingRate['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::FUNDING_RATE, $fundingRate, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByDepthImbalance($depthImbalance = null, $comparison = null)
    {
        if (is_array($depthImbalance)) {
            $useMinMax = false;
            if (isset($depthImbalance['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::DEPTH_IMBALANCE, $depthImbalance['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($depthImbalance['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::DEPTH_IMBALANCE, $depthImbalance['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::DEPTH_IMBALANCE, $depthImbalance, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByDepthImbalanceAvg($depthImbalanceAvg = null, $comparison = null)
    {
        if (is_array($depthImbalanceAvg)) {
            $useMinMax = false;
            if (isset($depthImbalanceAvg['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::DEPTH_IMBALANCE_AVG, $depthImbalanceAvg['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($depthImbalanceAvg['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::DEPTH_IMBALANCE_AVG, $depthImbalanceAvg['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::DEPTH_IMBALANCE_AVG, $depthImbalanceAvg, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByAdx14($adx14 = null, $comparison = null)
    {
        if (is_array($adx14)) {
            $useMinMax = false;
            if (isset($adx14['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::ADX14, $adx14['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($adx14['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::ADX14, $adx14['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::ADX14, $adx14, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByAtrPctRank($atrPctRank = null, $comparison = null)
    {
        if (is_array($atrPctRank)) {
            $useMinMax = false;
            if (isset($atrPctRank['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::ATR_PCT_RANK, $atrPctRank['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($atrPctRank['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::ATR_PCT_RANK, $atrPctRank['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::ATR_PCT_RANK, $atrPctRank, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByTakerBuyRatio($takerBuyRatio = null, $comparison = null)
    {
        if (is_array($takerBuyRatio)) {
            $useMinMax = false;
            if (isset($takerBuyRatio['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::TAKER_BUY_RATIO, $takerBuyRatio['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($takerBuyRatio['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::TAKER_BUY_RATIO, $takerBuyRatio['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::TAKER_BUY_RATIO, $takerBuyRatio, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByVolZscore($volZscore = null, $comparison = null)
    {
        if (is_array($volZscore)) {
            $useMinMax = false;
            if (isset($volZscore['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::VOL_ZSCORE, $volZscore['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($volZscore['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::VOL_ZSCORE, $volZscore['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::VOL_ZSCORE, $volZscore, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByEr20($er20 = null, $comparison = null)
    {
        if (is_array($er20)) {
            $useMinMax = false;
            if (isset($er20['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::ER20, $er20['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($er20['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::ER20, $er20['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::ER20, $er20, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByChop14($chop14 = null, $comparison = null)
    {
        if (is_array($chop14)) {
            $useMinMax = false;
            if (isset($chop14['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::CHOP14, $chop14['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($chop14['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::CHOP14, $chop14['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::CHOP14, $chop14, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByFundingPct($fundingPct = null, $comparison = null)
    {
        if (is_array($fundingPct)) {
            $useMinMax = false;
            if (isset($fundingPct['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::FUNDING_PCT, $fundingPct['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($fundingPct['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::FUNDING_PCT, $fundingPct['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::FUNDING_PCT, $fundingPct, $comparison);
    }

    /**
     * Filter the query on the computed_at column
     *
     * Example usage:
     * <code>
     * $query->filterByComputedAt('2011-03-14'); // WHERE computed_at = '2011-03-14'
     * $query->filterByComputedAt('now'); // WHERE computed_at = '2011-03-14'
     * $query->filterByComputedAt(array('max' => 'yesterday')); // WHERE computed_at < '2011-03-13'
     * </code>
     *
     * @param     mixed $computedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByComputedAt($computedAt = null, $comparison = null)
    {
        if (is_array($computedAt)) {
            $useMinMax = false;
            if (isset($computedAt['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::COMPUTED_AT, $computedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($computedAt['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::COMPUTED_AT, $computedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::COMPUTED_AT, $computedAt, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByDateCreation($dateCreation = null, $comparison = null)
    {
        if (is_array($dateCreation)) {
            $useMinMax = false;
            if (isset($dateCreation['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::DATE_CREATION, $dateCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateCreation['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::DATE_CREATION, $dateCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::DATE_CREATION, $dateCreation, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByDateModification($dateModification = null, $comparison = null)
    {
        if (is_array($dateModification)) {
            $useMinMax = false;
            if (isset($dateModification['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::DATE_MODIFICATION, $dateModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateModification['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::DATE_MODIFICATION, $dateModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::DATE_MODIFICATION, $dateModification, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByIdGroupCreation($idGroupCreation = null, $comparison = null)
    {
        if (is_array($idGroupCreation)) {
            $useMinMax = false;
            if (isset($idGroupCreation['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::ID_GROUP_CREATION, $idGroupCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGroupCreation['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::ID_GROUP_CREATION, $idGroupCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::ID_GROUP_CREATION, $idGroupCreation, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByIdCreation($idCreation = null, $comparison = null)
    {
        if (is_array($idCreation)) {
            $useMinMax = false;
            if (isset($idCreation['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::ID_CREATION, $idCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idCreation['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::ID_CREATION, $idCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::ID_CREATION, $idCreation, $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function filterByIdModification($idModification = null, $comparison = null)
    {
        if (is_array($idModification)) {
            $useMinMax = false;
            if (isset($idModification['min'])) {
                $this->addUsingAlias(MarketSummaryPeer::ID_MODIFICATION, $idModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idModification['max'])) {
                $this->addUsingAlias(MarketSummaryPeer::ID_MODIFICATION, $idModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketSummaryPeer::ID_MODIFICATION, $idModification, $comparison);
    }

    /**
     * Filter the query by a related AuthyGroup object
     *
     * @param   AuthyGroup|PropelObjectCollection $authyGroup The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 MarketSummaryQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroup($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(MarketSummaryPeer::ID_GROUP_CREATION, $authyGroup->getIdAuthyGroup(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(MarketSummaryPeer::ID_GROUP_CREATION, $authyGroup->toKeyValue('PrimaryKey', 'IdAuthyGroup'), $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
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
     * @return                 MarketSummaryQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdCreation($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(MarketSummaryPeer::ID_CREATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(MarketSummaryPeer::ID_CREATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
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
     * @return                 MarketSummaryQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdModification($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(MarketSummaryPeer::ID_MODIFICATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(MarketSummaryPeer::ID_MODIFICATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return MarketSummaryQuery The current query, for fluid interface
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
     * @param   MarketSummary $marketSummary Object to remove from the list of results
     *
     * @return MarketSummaryQuery The current query, for fluid interface
     */
    public function prune($marketSummary = null)
    {
        if ($marketSummary) {
            $this->addUsingAlias(MarketSummaryPeer::ID_MARKET_SUMMARY, $marketSummary->getIdMarketSummary(), Criteria::NOT_EQUAL);
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
                    \ApiGoat\Utility\TableVersion::bump('market_summary');
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
                    \ApiGoat\Utility\TableVersion::bump('market_summary');
                }

        return $this->postUpdate($affectedRows, $con);
    }

    // add_tablestamp behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     MarketSummaryQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7){
        return $this->addUsingAlias(MarketSummaryPeer::DATE_MODIFICATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     MarketSummaryQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst(){
        return $this->addDescendingOrderByColumn(MarketSummaryPeer::DATE_MODIFICATION);
    }

    /**
     * Order by update date asc
     *
     * @return     MarketSummaryQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst(){
        return $this->addAscendingOrderByColumn(MarketSummaryPeer::DATE_MODIFICATION);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     MarketSummaryQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7){
        return $this->addUsingAlias(MarketSummaryPeer::DATE_CREATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return     MarketSummaryQuery The current query, for fluid interface
     */
    public function lastCreatedFirst(){
        return $this->addDescendingOrderByColumn(MarketSummaryPeer::DATE_CREATION);
    }

    /**
     * Order by create date asc
     *
     * @return     MarketSummaryQuery The current query, for fluid interface
     */
    public function firstCreatedFirst(){
        return $this->addAscendingOrderByColumn(MarketSummaryPeer::DATE_CREATION);
    }
}
