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
use App\MarketOutlookState;
use App\MarketOutlookStatePeer;
use App\MarketOutlookStateQuery;

/**
 * Base class that represents a query for the 'market_outlook_state' table.
 *
 * Outlook State
 *
 * @method MarketOutlookStateQuery orderByIdMarketOutlookState($order = Criteria::ASC) Order by the id_market_outlook_state column
 * @method MarketOutlookStateQuery orderBySymbol($order = Criteria::ASC) Order by the symbol column
 * @method MarketOutlookStateQuery orderByVerdict($order = Criteria::ASC) Order by the verdict column
 * @method MarketOutlookStateQuery orderByVerdictSince($order = Criteria::ASC) Order by the verdict_since column
 * @method MarketOutlookStateQuery orderByPriceAtVerdict($order = Criteria::ASC) Order by the price_at_verdict column
 * @method MarketOutlookStateQuery orderByCandidate($order = Criteria::ASC) Order by the candidate column
 * @method MarketOutlookStateQuery orderByCandidatePasses($order = Criteria::ASC) Order by the candidate_passes column
 * @method MarketOutlookStateQuery orderByLastRaw($order = Criteria::ASC) Order by the last_raw column
 * @method MarketOutlookStateQuery orderByLastPassAt($order = Criteria::ASC) Order by the last_pass_at column
 * @method MarketOutlookStateQuery orderByDateCreation($order = Criteria::ASC) Order by the date_creation column
 * @method MarketOutlookStateQuery orderByDateModification($order = Criteria::ASC) Order by the date_modification column
 * @method MarketOutlookStateQuery orderByIdGroupCreation($order = Criteria::ASC) Order by the id_group_creation column
 * @method MarketOutlookStateQuery orderByIdCreation($order = Criteria::ASC) Order by the id_creation column
 * @method MarketOutlookStateQuery orderByIdModification($order = Criteria::ASC) Order by the id_modification column
 *
 * @method MarketOutlookStateQuery groupByIdMarketOutlookState() Group by the id_market_outlook_state column
 * @method MarketOutlookStateQuery groupBySymbol() Group by the symbol column
 * @method MarketOutlookStateQuery groupByVerdict() Group by the verdict column
 * @method MarketOutlookStateQuery groupByVerdictSince() Group by the verdict_since column
 * @method MarketOutlookStateQuery groupByPriceAtVerdict() Group by the price_at_verdict column
 * @method MarketOutlookStateQuery groupByCandidate() Group by the candidate column
 * @method MarketOutlookStateQuery groupByCandidatePasses() Group by the candidate_passes column
 * @method MarketOutlookStateQuery groupByLastRaw() Group by the last_raw column
 * @method MarketOutlookStateQuery groupByLastPassAt() Group by the last_pass_at column
 * @method MarketOutlookStateQuery groupByDateCreation() Group by the date_creation column
 * @method MarketOutlookStateQuery groupByDateModification() Group by the date_modification column
 * @method MarketOutlookStateQuery groupByIdGroupCreation() Group by the id_group_creation column
 * @method MarketOutlookStateQuery groupByIdCreation() Group by the id_creation column
 * @method MarketOutlookStateQuery groupByIdModification() Group by the id_modification column
 *
 * @method MarketOutlookStateQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method MarketOutlookStateQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method MarketOutlookStateQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method MarketOutlookStateQuery leftJoinAuthyGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroup relation
 * @method MarketOutlookStateQuery rightJoinAuthyGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroup relation
 * @method MarketOutlookStateQuery innerJoinAuthyGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroup relation
 *
 * @method MarketOutlookStateQuery leftJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method MarketOutlookStateQuery rightJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method MarketOutlookStateQuery innerJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdCreation relation
 *
 * @method MarketOutlookStateQuery leftJoinAuthyRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method MarketOutlookStateQuery rightJoinAuthyRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method MarketOutlookStateQuery innerJoinAuthyRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdModification relation
 *
 * @method MarketOutlookState findOne(?PropelPDO $con = null) Return the first MarketOutlookState matching the query
 * @method MarketOutlookState findOneOrCreate(?PropelPDO $con = null) Return the first MarketOutlookState matching the query, or a new MarketOutlookState object populated from the query conditions when no match is found
 *
 * @method MarketOutlookState findOneBySymbol(string $symbol) Return the first MarketOutlookState filtered by the symbol column
 * @method MarketOutlookState findOneByVerdict(string $verdict) Return the first MarketOutlookState filtered by the verdict column
 * @method MarketOutlookState findOneByVerdictSince(string $verdict_since) Return the first MarketOutlookState filtered by the verdict_since column
 * @method MarketOutlookState findOneByPriceAtVerdict(string $price_at_verdict) Return the first MarketOutlookState filtered by the price_at_verdict column
 * @method MarketOutlookState findOneByCandidate(string $candidate) Return the first MarketOutlookState filtered by the candidate column
 * @method MarketOutlookState findOneByCandidatePasses(int $candidate_passes) Return the first MarketOutlookState filtered by the candidate_passes column
 * @method MarketOutlookState findOneByLastRaw(string $last_raw) Return the first MarketOutlookState filtered by the last_raw column
 * @method MarketOutlookState findOneByLastPassAt(string $last_pass_at) Return the first MarketOutlookState filtered by the last_pass_at column
 * @method MarketOutlookState findOneByDateCreation(string $date_creation) Return the first MarketOutlookState filtered by the date_creation column
 * @method MarketOutlookState findOneByDateModification(string $date_modification) Return the first MarketOutlookState filtered by the date_modification column
 * @method MarketOutlookState findOneByIdGroupCreation(int $id_group_creation) Return the first MarketOutlookState filtered by the id_group_creation column
 * @method MarketOutlookState findOneByIdCreation(int $id_creation) Return the first MarketOutlookState filtered by the id_creation column
 * @method MarketOutlookState findOneByIdModification(int $id_modification) Return the first MarketOutlookState filtered by the id_modification column
 *
 * @method array findByIdMarketOutlookState(int $id_market_outlook_state) Return MarketOutlookState objects filtered by the id_market_outlook_state column
 * @method array findBySymbol(string $symbol) Return MarketOutlookState objects filtered by the symbol column
 * @method array findByVerdict(string $verdict) Return MarketOutlookState objects filtered by the verdict column
 * @method array findByVerdictSince(string $verdict_since) Return MarketOutlookState objects filtered by the verdict_since column
 * @method array findByPriceAtVerdict(string $price_at_verdict) Return MarketOutlookState objects filtered by the price_at_verdict column
 * @method array findByCandidate(string $candidate) Return MarketOutlookState objects filtered by the candidate column
 * @method array findByCandidatePasses(int $candidate_passes) Return MarketOutlookState objects filtered by the candidate_passes column
 * @method array findByLastRaw(string $last_raw) Return MarketOutlookState objects filtered by the last_raw column
 * @method array findByLastPassAt(string $last_pass_at) Return MarketOutlookState objects filtered by the last_pass_at column
 * @method array findByDateCreation(string $date_creation) Return MarketOutlookState objects filtered by the date_creation column
 * @method array findByDateModification(string $date_modification) Return MarketOutlookState objects filtered by the date_modification column
 * @method array findByIdGroupCreation(int $id_group_creation) Return MarketOutlookState objects filtered by the id_group_creation column
 * @method array findByIdCreation(int $id_creation) Return MarketOutlookState objects filtered by the id_creation column
 * @method array findByIdModification(int $id_modification) Return MarketOutlookState objects filtered by the id_modification column
 *
 * @package    propel.generator..om
 */
abstract class BaseMarketOutlookStateQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseMarketOutlookStateQuery object.
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
            $modelName = 'App\\MarketOutlookState';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new MarketOutlookStateQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   MarketOutlookStateQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return MarketOutlookStateQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof MarketOutlookStateQuery) {
            return $criteria;
        }
        $query = new MarketOutlookStateQuery(null, null, $modelAlias);

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
     * @return   MarketOutlookState|MarketOutlookState[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = MarketOutlookStatePeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(MarketOutlookStatePeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 MarketOutlookState A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdMarketOutlookState($key, $con = null)
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
     * @return                 MarketOutlookState A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_market_outlook_state`, `symbol`, `verdict`, `verdict_since`, `price_at_verdict`, `candidate`, `candidate_passes`, `last_raw`, `last_pass_at`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification` FROM `market_outlook_state` WHERE `id_market_outlook_state` = :p0';
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
            $obj = new MarketOutlookState();
            $obj->hydrate($row);
            MarketOutlookStatePeer::addInstanceToPool($obj, (string) $key);
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
     * @return MarketOutlookState|MarketOutlookState[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|MarketOutlookState[]|mixed the list of results, formatted by the current formatter
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
     * @return MarketOutlookStateQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(MarketOutlookStatePeer::ID_MARKET_OUTLOOK_STATE, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return MarketOutlookStateQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(MarketOutlookStatePeer::ID_MARKET_OUTLOOK_STATE, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id_market_outlook_state column
     *
     * Example usage:
     * <code>
     * $query->filterByIdMarketOutlookState(1234); // WHERE id_market_outlook_state = 1234
     * $query->filterByIdMarketOutlookState(array(12, 34)); // WHERE id_market_outlook_state IN (12, 34)
     * $query->filterByIdMarketOutlookState(array('min' => 12)); // WHERE id_market_outlook_state >= 12
     * $query->filterByIdMarketOutlookState(array('max' => 12)); // WHERE id_market_outlook_state <= 12
     * </code>
     *
     * @param     mixed $idMarketOutlookState The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookStateQuery The current query, for fluid interface
     */
    public function filterByIdMarketOutlookState($idMarketOutlookState = null, $comparison = null)
    {
        if (is_array($idMarketOutlookState)) {
            $useMinMax = false;
            if (isset($idMarketOutlookState['min'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::ID_MARKET_OUTLOOK_STATE, $idMarketOutlookState['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idMarketOutlookState['max'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::ID_MARKET_OUTLOOK_STATE, $idMarketOutlookState['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookStatePeer::ID_MARKET_OUTLOOK_STATE, $idMarketOutlookState, $comparison);
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
     * @return MarketOutlookStateQuery The current query, for fluid interface
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

        return $this->addUsingAlias(MarketOutlookStatePeer::SYMBOL, $symbol, $comparison);
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
     * @return MarketOutlookStateQuery The current query, for fluid interface
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

        return $this->addUsingAlias(MarketOutlookStatePeer::VERDICT, $verdict, $comparison);
    }

    /**
     * Filter the query on the verdict_since column
     *
     * Example usage:
     * <code>
     * $query->filterByVerdictSince('2011-03-14'); // WHERE verdict_since = '2011-03-14'
     * $query->filterByVerdictSince('now'); // WHERE verdict_since = '2011-03-14'
     * $query->filterByVerdictSince(array('max' => 'yesterday')); // WHERE verdict_since < '2011-03-13'
     * </code>
     *
     * @param     mixed $verdictSince The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookStateQuery The current query, for fluid interface
     */
    public function filterByVerdictSince($verdictSince = null, $comparison = null)
    {
        if (is_array($verdictSince)) {
            $useMinMax = false;
            if (isset($verdictSince['min'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::VERDICT_SINCE, $verdictSince['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($verdictSince['max'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::VERDICT_SINCE, $verdictSince['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookStatePeer::VERDICT_SINCE, $verdictSince, $comparison);
    }

    /**
     * Filter the query on the price_at_verdict column
     *
     * Example usage:
     * <code>
     * $query->filterByPriceAtVerdict(1234); // WHERE price_at_verdict = 1234
     * $query->filterByPriceAtVerdict(array(12, 34)); // WHERE price_at_verdict IN (12, 34)
     * $query->filterByPriceAtVerdict(array('min' => 12)); // WHERE price_at_verdict >= 12
     * $query->filterByPriceAtVerdict(array('max' => 12)); // WHERE price_at_verdict <= 12
     * </code>
     *
     * @param     mixed $priceAtVerdict The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookStateQuery The current query, for fluid interface
     */
    public function filterByPriceAtVerdict($priceAtVerdict = null, $comparison = null)
    {
        if (is_array($priceAtVerdict)) {
            $useMinMax = false;
            if (isset($priceAtVerdict['min'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::PRICE_AT_VERDICT, $priceAtVerdict['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($priceAtVerdict['max'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::PRICE_AT_VERDICT, $priceAtVerdict['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookStatePeer::PRICE_AT_VERDICT, $priceAtVerdict, $comparison);
    }

    /**
     * Filter the query on the candidate column
     *
     * Example usage:
     * <code>
     * $query->filterByCandidate('fooValue');   // WHERE candidate = 'fooValue'
     * $query->filterByCandidate('%fooValue%'); // WHERE candidate LIKE '%fooValue%'
     * </code>
     *
     * @param     string $candidate The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookStateQuery The current query, for fluid interface
     */
    public function filterByCandidate($candidate = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($candidate)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $candidate)) {
                $candidate = str_replace('*', '%', $candidate);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(MarketOutlookStatePeer::CANDIDATE, $candidate, $comparison);
    }

    /**
     * Filter the query on the candidate_passes column
     *
     * Example usage:
     * <code>
     * $query->filterByCandidatePasses(1234); // WHERE candidate_passes = 1234
     * $query->filterByCandidatePasses(array(12, 34)); // WHERE candidate_passes IN (12, 34)
     * $query->filterByCandidatePasses(array('min' => 12)); // WHERE candidate_passes >= 12
     * $query->filterByCandidatePasses(array('max' => 12)); // WHERE candidate_passes <= 12
     * </code>
     *
     * @param     mixed $candidatePasses The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookStateQuery The current query, for fluid interface
     */
    public function filterByCandidatePasses($candidatePasses = null, $comparison = null)
    {
        if (is_array($candidatePasses)) {
            $useMinMax = false;
            if (isset($candidatePasses['min'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::CANDIDATE_PASSES, $candidatePasses['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($candidatePasses['max'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::CANDIDATE_PASSES, $candidatePasses['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookStatePeer::CANDIDATE_PASSES, $candidatePasses, $comparison);
    }

    /**
     * Filter the query on the last_raw column
     *
     * Example usage:
     * <code>
     * $query->filterByLastRaw('fooValue');   // WHERE last_raw = 'fooValue'
     * $query->filterByLastRaw('%fooValue%'); // WHERE last_raw LIKE '%fooValue%'
     * </code>
     *
     * @param     string $lastRaw The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookStateQuery The current query, for fluid interface
     */
    public function filterByLastRaw($lastRaw = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($lastRaw)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $lastRaw)) {
                $lastRaw = str_replace('*', '%', $lastRaw);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(MarketOutlookStatePeer::LAST_RAW, $lastRaw, $comparison);
    }

    /**
     * Filter the query on the last_pass_at column
     *
     * Example usage:
     * <code>
     * $query->filterByLastPassAt('2011-03-14'); // WHERE last_pass_at = '2011-03-14'
     * $query->filterByLastPassAt('now'); // WHERE last_pass_at = '2011-03-14'
     * $query->filterByLastPassAt(array('max' => 'yesterday')); // WHERE last_pass_at < '2011-03-13'
     * </code>
     *
     * @param     mixed $lastPassAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return MarketOutlookStateQuery The current query, for fluid interface
     */
    public function filterByLastPassAt($lastPassAt = null, $comparison = null)
    {
        if (is_array($lastPassAt)) {
            $useMinMax = false;
            if (isset($lastPassAt['min'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::LAST_PASS_AT, $lastPassAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($lastPassAt['max'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::LAST_PASS_AT, $lastPassAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookStatePeer::LAST_PASS_AT, $lastPassAt, $comparison);
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
     * @return MarketOutlookStateQuery The current query, for fluid interface
     */
    public function filterByDateCreation($dateCreation = null, $comparison = null)
    {
        if (is_array($dateCreation)) {
            $useMinMax = false;
            if (isset($dateCreation['min'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::DATE_CREATION, $dateCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateCreation['max'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::DATE_CREATION, $dateCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookStatePeer::DATE_CREATION, $dateCreation, $comparison);
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
     * @return MarketOutlookStateQuery The current query, for fluid interface
     */
    public function filterByDateModification($dateModification = null, $comparison = null)
    {
        if (is_array($dateModification)) {
            $useMinMax = false;
            if (isset($dateModification['min'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::DATE_MODIFICATION, $dateModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateModification['max'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::DATE_MODIFICATION, $dateModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookStatePeer::DATE_MODIFICATION, $dateModification, $comparison);
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
     * @return MarketOutlookStateQuery The current query, for fluid interface
     */
    public function filterByIdGroupCreation($idGroupCreation = null, $comparison = null)
    {
        if (is_array($idGroupCreation)) {
            $useMinMax = false;
            if (isset($idGroupCreation['min'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::ID_GROUP_CREATION, $idGroupCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGroupCreation['max'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::ID_GROUP_CREATION, $idGroupCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookStatePeer::ID_GROUP_CREATION, $idGroupCreation, $comparison);
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
     * @return MarketOutlookStateQuery The current query, for fluid interface
     */
    public function filterByIdCreation($idCreation = null, $comparison = null)
    {
        if (is_array($idCreation)) {
            $useMinMax = false;
            if (isset($idCreation['min'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::ID_CREATION, $idCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idCreation['max'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::ID_CREATION, $idCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookStatePeer::ID_CREATION, $idCreation, $comparison);
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
     * @return MarketOutlookStateQuery The current query, for fluid interface
     */
    public function filterByIdModification($idModification = null, $comparison = null)
    {
        if (is_array($idModification)) {
            $useMinMax = false;
            if (isset($idModification['min'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::ID_MODIFICATION, $idModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idModification['max'])) {
                $this->addUsingAlias(MarketOutlookStatePeer::ID_MODIFICATION, $idModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MarketOutlookStatePeer::ID_MODIFICATION, $idModification, $comparison);
    }

    /**
     * Filter the query by a related AuthyGroup object
     *
     * @param   AuthyGroup|PropelObjectCollection $authyGroup The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 MarketOutlookStateQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroup($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(MarketOutlookStatePeer::ID_GROUP_CREATION, $authyGroup->getIdAuthyGroup(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(MarketOutlookStatePeer::ID_GROUP_CREATION, $authyGroup->toKeyValue('PrimaryKey', 'IdAuthyGroup'), $comparison);
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
     * @return MarketOutlookStateQuery The current query, for fluid interface
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
     * @return                 MarketOutlookStateQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdCreation($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(MarketOutlookStatePeer::ID_CREATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(MarketOutlookStatePeer::ID_CREATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return MarketOutlookStateQuery The current query, for fluid interface
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
     * @return                 MarketOutlookStateQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdModification($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(MarketOutlookStatePeer::ID_MODIFICATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(MarketOutlookStatePeer::ID_MODIFICATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return MarketOutlookStateQuery The current query, for fluid interface
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
     * @param   MarketOutlookState $marketOutlookState Object to remove from the list of results
     *
     * @return MarketOutlookStateQuery The current query, for fluid interface
     */
    public function prune($marketOutlookState = null)
    {
        if ($marketOutlookState) {
            $this->addUsingAlias(MarketOutlookStatePeer::ID_MARKET_OUTLOOK_STATE, $marketOutlookState->getIdMarketOutlookState(), Criteria::NOT_EQUAL);
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
                    \ApiGoat\Utility\TableVersion::bump('market_outlook_state');
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
                    \ApiGoat\Utility\TableVersion::bump('market_outlook_state');
                }

        return $this->postUpdate($affectedRows, $con);
    }

    // add_tablestamp behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     MarketOutlookStateQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7){
        return $this->addUsingAlias(MarketOutlookStatePeer::DATE_MODIFICATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     MarketOutlookStateQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst(){
        return $this->addDescendingOrderByColumn(MarketOutlookStatePeer::DATE_MODIFICATION);
    }

    /**
     * Order by update date asc
     *
     * @return     MarketOutlookStateQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst(){
        return $this->addAscendingOrderByColumn(MarketOutlookStatePeer::DATE_MODIFICATION);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     MarketOutlookStateQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7){
        return $this->addUsingAlias(MarketOutlookStatePeer::DATE_CREATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return     MarketOutlookStateQuery The current query, for fluid interface
     */
    public function lastCreatedFirst(){
        return $this->addDescendingOrderByColumn(MarketOutlookStatePeer::DATE_CREATION);
    }

    /**
     * Order by create date asc
     *
     * @return     MarketOutlookStateQuery The current query, for fluid interface
     */
    public function firstCreatedFirst(){
        return $this->addAscendingOrderByColumn(MarketOutlookStatePeer::DATE_CREATION);
    }
}
