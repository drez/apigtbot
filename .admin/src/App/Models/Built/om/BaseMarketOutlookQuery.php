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
use App\MarketOutlook;
use App\MarketOutlookPeer;
use App\MarketOutlookQuery;

/**
 * Base class that represents a query for the 'market_outlook' table.
 *
 * Market Outlook
 *
 * @method MarketOutlookQuery orderByIdMarketOutlook($order = Criteria::ASC) Order by the id_market_outlook column
 * @method MarketOutlookQuery orderBySymbol($order = Criteria::ASC) Order by the symbol column
 * @method MarketOutlookQuery orderByKind($order = Criteria::ASC) Order by the kind column
 * @method MarketOutlookQuery orderByVerdict($order = Criteria::ASC) Order by the verdict column
 * @method MarketOutlookQuery orderByPrevVerdict($order = Criteria::ASC) Order by the prev_verdict column
 * @method MarketOutlookQuery orderByPriceAt($order = Criteria::ASC) Order by the price_at column
 * @method MarketOutlookQuery orderByCalledAt($order = Criteria::ASC) Order by the called_at column
 * @method MarketOutlookQuery orderByDetail($order = Criteria::ASC) Order by the detail column
 * @method MarketOutlookQuery orderByEvalStatus($order = Criteria::ASC) Order by the eval_status column
 * @method MarketOutlookQuery orderByPrice7d($order = Criteria::ASC) Order by the price_7d column
 * @method MarketOutlookQuery orderByPrice30d($order = Criteria::ASC) Order by the price_30d column
 * @method MarketOutlookQuery orderByRet7d($order = Criteria::ASC) Order by the ret_7d column
 * @method MarketOutlookQuery orderByRet30d($order = Criteria::ASC) Order by the ret_30d column
 * @method MarketOutlookQuery orderByMaxAdversePct($order = Criteria::ASC) Order by the max_adverse_pct column
 * @method MarketOutlookQuery orderByHit7d($order = Criteria::ASC) Order by the hit_7d column
 * @method MarketOutlookQuery orderByHit30d($order = Criteria::ASC) Order by the hit_30d column
 * @method MarketOutlookQuery orderByScoredAt($order = Criteria::ASC) Order by the scored_at column
 * @method MarketOutlookQuery orderByDateCreation($order = Criteria::ASC) Order by the date_creation column
 * @method MarketOutlookQuery orderByDateModification($order = Criteria::ASC) Order by the date_modification column
 * @method MarketOutlookQuery orderByIdGroupCreation($order = Criteria::ASC) Order by the id_group_creation column
 * @method MarketOutlookQuery orderByIdCreation($order = Criteria::ASC) Order by the id_creation column
 * @method MarketOutlookQuery orderByIdModification($order = Criteria::ASC) Order by the id_modification column
 *
 * @method MarketOutlookQuery groupByIdMarketOutlook() Group by the id_market_outlook column
 * @method MarketOutlookQuery groupBySymbol() Group by the symbol column
 * @method MarketOutlookQuery groupByKind() Group by the kind column
 * @method MarketOutlookQuery groupByVerdict() Group by the verdict column
 * @method MarketOutlookQuery groupByPrevVerdict() Group by the prev_verdict column
 * @method MarketOutlookQuery groupByPriceAt() Group by the price_at column
 * @method MarketOutlookQuery groupByCalledAt() Group by the called_at column
 * @method MarketOutlookQuery groupByDetail() Group by the detail column
 * @method MarketOutlookQuery groupByEvalStatus() Group by the eval_status column
 * @method MarketOutlookQuery groupByPrice7d() Group by the price_7d column
 * @method MarketOutlookQuery groupByPrice30d() Group by the price_30d column
 * @method MarketOutlookQuery groupByRet7d() Group by the ret_7d column
 * @method MarketOutlookQuery groupByRet30d() Group by the ret_30d column
 * @method MarketOutlookQuery groupByMaxAdversePct() Group by the max_adverse_pct column
 * @method MarketOutlookQuery groupByHit7d() Group by the hit_7d column
 * @method MarketOutlookQuery groupByHit30d() Group by the hit_30d column
 * @method MarketOutlookQuery groupByScoredAt() Group by the scored_at column
 * @method MarketOutlookQuery groupByDateCreation() Group by the date_creation column
 * @method MarketOutlookQuery groupByDateModification() Group by the date_modification column
 * @method MarketOutlookQuery groupByIdGroupCreation() Group by the id_group_creation column
 * @method MarketOutlookQuery groupByIdCreation() Group by the id_creation column
 * @method MarketOutlookQuery groupByIdModification() Group by the id_modification column
 *
 * @method MarketOutlookQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method MarketOutlookQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method MarketOutlookQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method MarketOutlookQuery leftJoinAuthyGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroup relation
 * @method MarketOutlookQuery rightJoinAuthyGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroup relation
 * @method MarketOutlookQuery innerJoinAuthyGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroup relation
 *
 * @method MarketOutlookQuery leftJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method MarketOutlookQuery rightJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method MarketOutlookQuery innerJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdCreation relation
 *
 * @method MarketOutlookQuery leftJoinAuthyRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method MarketOutlookQuery rightJoinAuthyRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method MarketOutlookQuery innerJoinAuthyRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdModification relation
 *
 * @method MarketOutlook findOne(?PropelPDO $con = null) Return the first MarketOutlook matching the query
 * @method MarketOutlook findOneOrCreate(?PropelPDO $con = null) Return the first MarketOutlook matching the query, or a new MarketOutlook object populated from the query conditions when no match is found
 *
 * @method MarketOutlook findOneBySymbol(string $symbol) Return the first MarketOutlook filtered by the symbol column
 * @method MarketOutlook findOneByKind(string $kind) Return the first MarketOutlook filtered by the kind column
 * @method MarketOutlook findOneByVerdict(string $verdict) Return the first MarketOutlook filtered by the verdict column
 * @method MarketOutlook findOneByPrevVerdict(string $prev_verdict) Return the first MarketOutlook filtered by the prev_verdict column
 * @method MarketOutlook findOneByPriceAt(string $price_at) Return the first MarketOutlook filtered by the price_at column
 * @method MarketOutlook findOneByCalledAt(string $called_at) Return the first MarketOutlook filtered by the called_at column
 * @method MarketOutlook findOneByDetail(string $detail) Return the first MarketOutlook filtered by the detail column
 * @method MarketOutlook findOneByEvalStatus(string $eval_status) Return the first MarketOutlook filtered by the eval_status column
 * @method MarketOutlook findOneByPrice7d(string $price_7d) Return the first MarketOutlook filtered by the price_7d column
 * @method MarketOutlook findOneByPrice30d(string $price_30d) Return the first MarketOutlook filtered by the price_30d column
 * @method MarketOutlook findOneByRet7d(string $ret_7d) Return the first MarketOutlook filtered by the ret_7d column
 * @method MarketOutlook findOneByRet30d(string $ret_30d) Return the first MarketOutlook filtered by the ret_30d column
 * @method MarketOutlook findOneByMaxAdversePct(string $max_adverse_pct) Return the first MarketOutlook filtered by the max_adverse_pct column
 * @method MarketOutlook findOneByHit7d(boolean $hit_7d) Return the first MarketOutlook filtered by the hit_7d column
 * @method MarketOutlook findOneByHit30d(boolean $hit_30d) Return the first MarketOutlook filtered by the hit_30d column
 * @method MarketOutlook findOneByScoredAt(string $scored_at) Return the first MarketOutlook filtered by the scored_at column
 * @method MarketOutlook findOneByDateCreation(string $date_creation) Return the first MarketOutlook filtered by the date_creation column
 * @method MarketOutlook findOneByDateModification(string $date_modification) Return the first MarketOutlook filtered by the date_modification column
 * @method MarketOutlook findOneByIdGroupCreation(int $id_group_creation) Return the first MarketOutlook filtered by the id_group_creation column
 * @method MarketOutlook findOneByIdCreation(int $id_creation) Return the first MarketOutlook filtered by the id_creation column
 * @method MarketOutlook findOneByIdModification(int $id_modification) Return the first MarketOutlook filtered by the id_modification column
 *
 * @method array findByIdMarketOutlook(int $id_market_outlook) Return MarketOutlook objects filtered by the id_market_outlook column
 * @method array findBySymbol(string $symbol) Return MarketOutlook objects filtered by the symbol column
 * @method array findByKind(string $kind) Return MarketOutlook objects filtered by the kind column
 * @method array findByVerdict(string $verdict) Return MarketOutlook objects filtered by the verdict column
 * @method array findByPrevVerdict(string $prev_verdict) Return MarketOutlook objects filtered by the prev_verdict column
 * @method array findByPriceAt(string $price_at) Return MarketOutlook objects filtered by the price_at column
 * @method array findByCalledAt(string $called_at) Return MarketOutlook objects filtered by the called_at column
 * @method array findByDetail(string $detail) Return MarketOutlook objects filtered by the detail column
 * @method array findByEvalStatus(string $eval_status) Return MarketOutlook objects filtered by the eval_status column
 * @method array findByPrice7d(string $price_7d) Return MarketOutlook objects filtered by the price_7d column
 * @method array findByPrice30d(string $price_30d) Return MarketOutlook objects filtered by the price_30d column
 * @method array findByRet7d(string $ret_7d) Return MarketOutlook objects filtered by the ret_7d column
 * @method array findByRet30d(string $ret_30d) Return MarketOutlook objects filtered by the ret_30d column
 * @method array findByMaxAdversePct(string $max_adverse_pct) Return MarketOutlook objects filtered by the max_adverse_pct column
 * @method array findByHit7d(boolean $hit_7d) Return MarketOutlook objects filtered by the hit_7d column
 * @method array findByHit30d(boolean $hit_30d) Return MarketOutlook objects filtered by the hit_30d column
 * @method array findByScoredAt(string $scored_at) Return MarketOutlook objects filtered by the scored_at column
 * @method array findByDateCreation(string $date_creation) Return MarketOutlook objects filtered by the date_creation column
 * @method array findByDateModification(string $date_modification) Return MarketOutlook objects filtered by the date_modification column
 * @method array findByIdGroupCreation(int $id_group_creation) Return MarketOutlook objects filtered by the id_group_creation column
 * @method array findByIdCreation(int $id_creation) Return MarketOutlook objects filtered by the id_creation column
 * @method array findByIdModification(int $id_modification) Return MarketOutlook objects filtered by the id_modification column
 *
 * @package    propel.generator..om
 */
abstract class BaseMarketOutlookQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseMarketOutlookQuery object.
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
            $modelName = 'App\\MarketOutlook';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new MarketOutlookQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   MarketOutlookQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return MarketOutlookQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof MarketOutlookQuery) {
            return $criteria;
        }
        $query = new MarketOutlookQuery(null, null, $modelAlias);

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
     * @return   MarketOutlook|MarketOutlook[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = MarketOutlookPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 MarketOutlook A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdMarketOutlook($key, $con = null)
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
     * @return                 MarketOutlook A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_market_outlook`, `symbol`, `kind`, `verdict`, `prev_verdict`, `price_at`, `called_at`, `detail`, `eval_status`, `price_7d`, `price_30d`, `ret_7d`, `ret_30d`, `max_adverse_pct`, `hit_7d`, `hit_30d`, `scored_at`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification` FROM `market_outlook` WHERE `id_market_outlook` = :p0';
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
            $obj = new MarketOutlook();
            $obj->hydrate($row);
            MarketOutlookPeer::addInstanceToPool($obj, (string) $key);
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
     * @return MarketOutlook|MarketOutlook[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|MarketOutlook[]|mixed the list of results, formatted by the current formatter
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
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(MarketOutlookPeer::ID_MARKET_OUTLOOK, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(MarketOutlookPeer::ID_MARKET_OUTLOOK, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id_market_outlook column
     *
     * Example usage:
     * <code>
     * $query->filterByIdMarketOutlook(1234); // WHERE id_market_outlook = 1234
     * $query->filterByIdMarketOutlook(array(12, 34)); // WHERE id_market_outlook IN (12, 34)
     * $query->filterByIdMarketOutlook(array('min' => 12)); // WHERE id_market_outlook >= 12
     * $query->filterByIdMarketOutlook(array('max' => 12)); // WHERE id_market_outlook <= 12
     * </code>
     *
     * @param     mixed $idMarketOutlook The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByIdMarketOutlook($idMarketOutlook = null, $comparison = null)
    {
        if (is_array($idMarketOutlook)) {
            $useMinMax = false;
            if (isset($idMarketOutlook['min'])) {
                $this->addUsingAlias(MarketOutlookPeer::ID_MARKET_OUTLOOK, $idMarketOutlook['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idMarketOutlook['max'])) {
                $this->addUsingAlias(MarketOutlookPeer::ID_MARKET_OUTLOOK, $idMarketOutlook['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::ID_MARKET_OUTLOOK, $idMarketOutlook, $comparison);
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
     * @return MarketOutlookQuery The current query, for fluid interface
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

        return $this->addUsingAlias(MarketOutlookPeer::SYMBOL, $symbol, $comparison);
    }

    /**
     * Filter the query on the kind column
     *
     * Example usage:
     * <code>
     * $query->filterByKind('fooValue');   // WHERE kind = 'fooValue'
     * $query->filterByKind('%fooValue%'); // WHERE kind LIKE '%fooValue%'
     * </code>
     *
     * @param     string $kind The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByKind($kind = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($kind)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $kind)) {
                $kind = str_replace('*', '%', $kind);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::KIND, $kind, $comparison);
    }

    /**
     * Filter the query on the verdict column
     *
     * Example usage:
     * <code>
     * $query->filterByVerdict('fooValue');   // WHERE verdict = 'fooValue'
     * $query->filterByVerdict('%fooValue%'); // WHERE verdict LIKE '%fooValue%'
     * </code>
     *
     * @param     string $verdict The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByVerdict($verdict = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($verdict)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $verdict)) {
                $verdict = str_replace('*', '%', $verdict);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::VERDICT, $verdict, $comparison);
    }

    /**
     * Filter the query on the prev_verdict column
     *
     * Example usage:
     * <code>
     * $query->filterByPrevVerdict('fooValue');   // WHERE prev_verdict = 'fooValue'
     * $query->filterByPrevVerdict('%fooValue%'); // WHERE prev_verdict LIKE '%fooValue%'
     * </code>
     *
     * @param     string $prevVerdict The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByPrevVerdict($prevVerdict = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($prevVerdict)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $prevVerdict)) {
                $prevVerdict = str_replace('*', '%', $prevVerdict);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::PREV_VERDICT, $prevVerdict, $comparison);
    }

    /**
     * Filter the query on the price_at column
     *
     * Example usage:
     * <code>
     * $query->filterByPriceAt(1234); // WHERE price_at = 1234
     * $query->filterByPriceAt(array(12, 34)); // WHERE price_at IN (12, 34)
     * $query->filterByPriceAt(array('min' => 12)); // WHERE price_at >= 12
     * $query->filterByPriceAt(array('max' => 12)); // WHERE price_at <= 12
     * </code>
     *
     * @param     mixed $priceAt The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByPriceAt($priceAt = null, $comparison = null)
    {
        if (is_array($priceAt)) {
            $useMinMax = false;
            if (isset($priceAt['min'])) {
                $this->addUsingAlias(MarketOutlookPeer::PRICE_AT, $priceAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($priceAt['max'])) {
                $this->addUsingAlias(MarketOutlookPeer::PRICE_AT, $priceAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::PRICE_AT, $priceAt, $comparison);
    }

    /**
     * Filter the query on the called_at column
     *
     * Example usage:
     * <code>
     * $query->filterByCalledAt('2011-03-14'); // WHERE called_at = '2011-03-14'
     * $query->filterByCalledAt('now'); // WHERE called_at = '2011-03-14'
     * $query->filterByCalledAt(array('max' => 'yesterday')); // WHERE called_at < '2011-03-13'
     * </code>
     *
     * @param     mixed $calledAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByCalledAt($calledAt = null, $comparison = null)
    {
        if (is_array($calledAt)) {
            $useMinMax = false;
            if (isset($calledAt['min'])) {
                $this->addUsingAlias(MarketOutlookPeer::CALLED_AT, $calledAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($calledAt['max'])) {
                $this->addUsingAlias(MarketOutlookPeer::CALLED_AT, $calledAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::CALLED_AT, $calledAt, $comparison);
    }

    /**
     * Filter the query on the detail column
     *
     * Example usage:
     * <code>
     * $query->filterByDetail('fooValue');   // WHERE detail = 'fooValue'
     * $query->filterByDetail('%fooValue%'); // WHERE detail LIKE '%fooValue%'
     * </code>
     *
     * @param     string $detail The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByDetail($detail = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($detail)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $detail)) {
                $detail = str_replace('*', '%', $detail);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::DETAIL, $detail, $comparison);
    }

    /**
     * Filter the query on the eval_status column
     *
     * Example usage:
     * <code>
     * $query->filterByEvalStatus('fooValue');   // WHERE eval_status = 'fooValue'
     * $query->filterByEvalStatus('%fooValue%'); // WHERE eval_status LIKE '%fooValue%'
     * </code>
     *
     * @param     string $evalStatus The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByEvalStatus($evalStatus = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($evalStatus)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $evalStatus)) {
                $evalStatus = str_replace('*', '%', $evalStatus);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::EVAL_STATUS, $evalStatus, $comparison);
    }

    /**
     * Filter the query on the price_7d column
     *
     * Example usage:
     * <code>
     * $query->filterByPrice7d(1234); // WHERE price_7d = 1234
     * $query->filterByPrice7d(array(12, 34)); // WHERE price_7d IN (12, 34)
     * $query->filterByPrice7d(array('min' => 12)); // WHERE price_7d >= 12
     * $query->filterByPrice7d(array('max' => 12)); // WHERE price_7d <= 12
     * </code>
     *
     * @param     mixed $price7d The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByPrice7d($price7d = null, $comparison = null)
    {
        if (is_array($price7d)) {
            $useMinMax = false;
            if (isset($price7d['min'])) {
                $this->addUsingAlias(MarketOutlookPeer::PRICE_7D, $price7d['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($price7d['max'])) {
                $this->addUsingAlias(MarketOutlookPeer::PRICE_7D, $price7d['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::PRICE_7D, $price7d, $comparison);
    }

    /**
     * Filter the query on the price_30d column
     *
     * Example usage:
     * <code>
     * $query->filterByPrice30d(1234); // WHERE price_30d = 1234
     * $query->filterByPrice30d(array(12, 34)); // WHERE price_30d IN (12, 34)
     * $query->filterByPrice30d(array('min' => 12)); // WHERE price_30d >= 12
     * $query->filterByPrice30d(array('max' => 12)); // WHERE price_30d <= 12
     * </code>
     *
     * @param     mixed $price30d The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByPrice30d($price30d = null, $comparison = null)
    {
        if (is_array($price30d)) {
            $useMinMax = false;
            if (isset($price30d['min'])) {
                $this->addUsingAlias(MarketOutlookPeer::PRICE_30D, $price30d['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($price30d['max'])) {
                $this->addUsingAlias(MarketOutlookPeer::PRICE_30D, $price30d['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::PRICE_30D, $price30d, $comparison);
    }

    /**
     * Filter the query on the ret_7d column
     *
     * Example usage:
     * <code>
     * $query->filterByRet7d(1234); // WHERE ret_7d = 1234
     * $query->filterByRet7d(array(12, 34)); // WHERE ret_7d IN (12, 34)
     * $query->filterByRet7d(array('min' => 12)); // WHERE ret_7d >= 12
     * $query->filterByRet7d(array('max' => 12)); // WHERE ret_7d <= 12
     * </code>
     *
     * @param     mixed $ret7d The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByRet7d($ret7d = null, $comparison = null)
    {
        if (is_array($ret7d)) {
            $useMinMax = false;
            if (isset($ret7d['min'])) {
                $this->addUsingAlias(MarketOutlookPeer::RET_7D, $ret7d['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($ret7d['max'])) {
                $this->addUsingAlias(MarketOutlookPeer::RET_7D, $ret7d['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::RET_7D, $ret7d, $comparison);
    }

    /**
     * Filter the query on the ret_30d column
     *
     * Example usage:
     * <code>
     * $query->filterByRet30d(1234); // WHERE ret_30d = 1234
     * $query->filterByRet30d(array(12, 34)); // WHERE ret_30d IN (12, 34)
     * $query->filterByRet30d(array('min' => 12)); // WHERE ret_30d >= 12
     * $query->filterByRet30d(array('max' => 12)); // WHERE ret_30d <= 12
     * </code>
     *
     * @param     mixed $ret30d The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByRet30d($ret30d = null, $comparison = null)
    {
        if (is_array($ret30d)) {
            $useMinMax = false;
            if (isset($ret30d['min'])) {
                $this->addUsingAlias(MarketOutlookPeer::RET_30D, $ret30d['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($ret30d['max'])) {
                $this->addUsingAlias(MarketOutlookPeer::RET_30D, $ret30d['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::RET_30D, $ret30d, $comparison);
    }

    /**
     * Filter the query on the max_adverse_pct column
     *
     * Example usage:
     * <code>
     * $query->filterByMaxAdversePct(1234); // WHERE max_adverse_pct = 1234
     * $query->filterByMaxAdversePct(array(12, 34)); // WHERE max_adverse_pct IN (12, 34)
     * $query->filterByMaxAdversePct(array('min' => 12)); // WHERE max_adverse_pct >= 12
     * $query->filterByMaxAdversePct(array('max' => 12)); // WHERE max_adverse_pct <= 12
     * </code>
     *
     * @param     mixed $maxAdversePct The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByMaxAdversePct($maxAdversePct = null, $comparison = null)
    {
        if (is_array($maxAdversePct)) {
            $useMinMax = false;
            if (isset($maxAdversePct['min'])) {
                $this->addUsingAlias(MarketOutlookPeer::MAX_ADVERSE_PCT, $maxAdversePct['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($maxAdversePct['max'])) {
                $this->addUsingAlias(MarketOutlookPeer::MAX_ADVERSE_PCT, $maxAdversePct['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::MAX_ADVERSE_PCT, $maxAdversePct, $comparison);
    }

    /**
     * Filter the query on the hit_7d column
     *
     * Example usage:
     * <code>
     * $query->filterByHit7d(true); // WHERE hit_7d = true
     * $query->filterByHit7d('yes'); // WHERE hit_7d = true
     * </code>
     *
     * @param     boolean|string $hit7d The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByHit7d($hit7d = null, $comparison = null)
    {
        if (is_string($hit7d)) {
            $hit7d = in_array(strtolower($hit7d), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(MarketOutlookPeer::HIT_7D, $hit7d, $comparison);
    }

    /**
     * Filter the query on the hit_30d column
     *
     * Example usage:
     * <code>
     * $query->filterByHit30d(true); // WHERE hit_30d = true
     * $query->filterByHit30d('yes'); // WHERE hit_30d = true
     * </code>
     *
     * @param     boolean|string $hit30d The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByHit30d($hit30d = null, $comparison = null)
    {
        if (is_string($hit30d)) {
            $hit30d = in_array(strtolower($hit30d), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(MarketOutlookPeer::HIT_30D, $hit30d, $comparison);
    }

    /**
     * Filter the query on the scored_at column
     *
     * Example usage:
     * <code>
     * $query->filterByScoredAt('2011-03-14'); // WHERE scored_at = '2011-03-14'
     * $query->filterByScoredAt('now'); // WHERE scored_at = '2011-03-14'
     * $query->filterByScoredAt(array('max' => 'yesterday')); // WHERE scored_at < '2011-03-13'
     * </code>
     *
     * @param     mixed $scoredAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByScoredAt($scoredAt = null, $comparison = null)
    {
        if (is_array($scoredAt)) {
            $useMinMax = false;
            if (isset($scoredAt['min'])) {
                $this->addUsingAlias(MarketOutlookPeer::SCORED_AT, $scoredAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($scoredAt['max'])) {
                $this->addUsingAlias(MarketOutlookPeer::SCORED_AT, $scoredAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::SCORED_AT, $scoredAt, $comparison);
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
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByDateCreation($dateCreation = null, $comparison = null)
    {
        if (is_array($dateCreation)) {
            $useMinMax = false;
            if (isset($dateCreation['min'])) {
                $this->addUsingAlias(MarketOutlookPeer::DATE_CREATION, $dateCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateCreation['max'])) {
                $this->addUsingAlias(MarketOutlookPeer::DATE_CREATION, $dateCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::DATE_CREATION, $dateCreation, $comparison);
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
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByDateModification($dateModification = null, $comparison = null)
    {
        if (is_array($dateModification)) {
            $useMinMax = false;
            if (isset($dateModification['min'])) {
                $this->addUsingAlias(MarketOutlookPeer::DATE_MODIFICATION, $dateModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateModification['max'])) {
                $this->addUsingAlias(MarketOutlookPeer::DATE_MODIFICATION, $dateModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::DATE_MODIFICATION, $dateModification, $comparison);
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
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByIdGroupCreation($idGroupCreation = null, $comparison = null)
    {
        if (is_array($idGroupCreation)) {
            $useMinMax = false;
            if (isset($idGroupCreation['min'])) {
                $this->addUsingAlias(MarketOutlookPeer::ID_GROUP_CREATION, $idGroupCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGroupCreation['max'])) {
                $this->addUsingAlias(MarketOutlookPeer::ID_GROUP_CREATION, $idGroupCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::ID_GROUP_CREATION, $idGroupCreation, $comparison);
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
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByIdCreation($idCreation = null, $comparison = null)
    {
        if (is_array($idCreation)) {
            $useMinMax = false;
            if (isset($idCreation['min'])) {
                $this->addUsingAlias(MarketOutlookPeer::ID_CREATION, $idCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idCreation['max'])) {
                $this->addUsingAlias(MarketOutlookPeer::ID_CREATION, $idCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::ID_CREATION, $idCreation, $comparison);
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
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function filterByIdModification($idModification = null, $comparison = null)
    {
        if (is_array($idModification)) {
            $useMinMax = false;
            if (isset($idModification['min'])) {
                $this->addUsingAlias(MarketOutlookPeer::ID_MODIFICATION, $idModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idModification['max'])) {
                $this->addUsingAlias(MarketOutlookPeer::ID_MODIFICATION, $idModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookPeer::ID_MODIFICATION, $idModification, $comparison);
    }

    /**
     * Filter the query by a related AuthyGroup object
     *
     * @param   AuthyGroup|PropelObjectCollection $authyGroup The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 MarketOutlookQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroup($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(MarketOutlookPeer::ID_GROUP_CREATION, $authyGroup->getIdAuthyGroup(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(MarketOutlookPeer::ID_GROUP_CREATION, $authyGroup->toKeyValue('PrimaryKey', 'IdAuthyGroup'), $comparison);
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
     * @return MarketOutlookQuery The current query, for fluid interface
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
     * @return                 MarketOutlookQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdCreation($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(MarketOutlookPeer::ID_CREATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(MarketOutlookPeer::ID_CREATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return MarketOutlookQuery The current query, for fluid interface
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
     * @return                 MarketOutlookQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdModification($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(MarketOutlookPeer::ID_MODIFICATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(MarketOutlookPeer::ID_MODIFICATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return MarketOutlookQuery The current query, for fluid interface
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
     * @param   MarketOutlook $marketOutlook Object to remove from the list of results
     *
     * @return MarketOutlookQuery The current query, for fluid interface
     */
    public function prune($marketOutlook = null)
    {
        if ($marketOutlook) {
            $this->addUsingAlias(MarketOutlookPeer::ID_MARKET_OUTLOOK, $marketOutlook->getIdMarketOutlook(), Criteria::NOT_EQUAL);
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
                    \ApiGoat\Utility\TableVersion::bump('market_outlook');
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
                    \ApiGoat\Utility\TableVersion::bump('market_outlook');
                }

        return $this->postUpdate($affectedRows, $con);
    }

    // add_tablestamp behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     MarketOutlookQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7){
        return $this->addUsingAlias(MarketOutlookPeer::DATE_MODIFICATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     MarketOutlookQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst(){
        return $this->addDescendingOrderByColumn(MarketOutlookPeer::DATE_MODIFICATION);
    }

    /**
     * Order by update date asc
     *
     * @return     MarketOutlookQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst(){
        return $this->addAscendingOrderByColumn(MarketOutlookPeer::DATE_MODIFICATION);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     MarketOutlookQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7){
        return $this->addUsingAlias(MarketOutlookPeer::DATE_CREATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return     MarketOutlookQuery The current query, for fluid interface
     */
    public function lastCreatedFirst(){
        return $this->addDescendingOrderByColumn(MarketOutlookPeer::DATE_CREATION);
    }

    /**
     * Order by create date asc
     *
     * @return     MarketOutlookQuery The current query, for fluid interface
     */
    public function firstCreatedFirst(){
        return $this->addAscendingOrderByColumn(MarketOutlookPeer::DATE_CREATION);
    }
}
