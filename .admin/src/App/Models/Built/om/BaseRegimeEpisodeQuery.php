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
use App\FleetSlot;
use App\GridRun;
use App\RegimeEpisode;
use App\RegimeEpisodePeer;
use App\RegimeEpisodeQuery;

/**
 * Base class that represents a query for the 'regime_episode' table.
 *
 * Regime episode
 *
 * @method RegimeEpisodeQuery orderByIdRegimeEpisode($order = Criteria::ASC) Order by the id_regime_episode column
 * @method RegimeEpisodeQuery orderBySymbol($order = Criteria::ASC) Order by the symbol column
 * @method RegimeEpisodeQuery orderByAlgo($order = Criteria::ASC) Order by the algo column
 * @method RegimeEpisodeQuery orderByIdFleetSlot($order = Criteria::ASC) Order by the id_fleet_slot column
 * @method RegimeEpisodeQuery orderByIdGridRun($order = Criteria::ASC) Order by the id_grid_run column
 * @method RegimeEpisodeQuery orderByVerdict($order = Criteria::ASC) Order by the verdict column
 * @method RegimeEpisodeQuery orderByOpenedAt($order = Criteria::ASC) Order by the opened_at column
 * @method RegimeEpisodeQuery orderByClosedAt($order = Criteria::ASC) Order by the closed_at column
 * @method RegimeEpisodeQuery orderByPriceOpen($order = Criteria::ASC) Order by the price_open column
 * @method RegimeEpisodeQuery orderByPriceClose($order = Criteria::ASC) Order by the price_close column
 * @method RegimeEpisodeQuery orderByEngagedPctTw($order = Criteria::ASC) Order by the engaged_pct_tw column
 * @method RegimeEpisodeQuery orderBySamples($order = Criteria::ASC) Order by the samples column
 * @method RegimeEpisodeQuery orderByRealized($order = Criteria::ASC) Order by the realized column
 * @method RegimeEpisodeQuery orderByMtmClose($order = Criteria::ASC) Order by the mtm_close column
 * @method RegimeEpisodeQuery orderByHodlPct($order = Criteria::ASC) Order by the hodl_pct column
 * @method RegimeEpisodeQuery orderByCapturedPct($order = Criteria::ASC) Order by the captured_pct column
 * @method RegimeEpisodeQuery orderByIdleSamples($order = Criteria::ASC) Order by the idle_samples column
 * @method RegimeEpisodeQuery orderByIdleAlertedAt($order = Criteria::ASC) Order by the idle_alerted_at column
 * @method RegimeEpisodeQuery orderByDateCreation($order = Criteria::ASC) Order by the date_creation column
 * @method RegimeEpisodeQuery orderByDateModification($order = Criteria::ASC) Order by the date_modification column
 * @method RegimeEpisodeQuery orderByIdGroupCreation($order = Criteria::ASC) Order by the id_group_creation column
 * @method RegimeEpisodeQuery orderByIdCreation($order = Criteria::ASC) Order by the id_creation column
 * @method RegimeEpisodeQuery orderByIdModification($order = Criteria::ASC) Order by the id_modification column
 *
 * @method RegimeEpisodeQuery groupByIdRegimeEpisode() Group by the id_regime_episode column
 * @method RegimeEpisodeQuery groupBySymbol() Group by the symbol column
 * @method RegimeEpisodeQuery groupByAlgo() Group by the algo column
 * @method RegimeEpisodeQuery groupByIdFleetSlot() Group by the id_fleet_slot column
 * @method RegimeEpisodeQuery groupByIdGridRun() Group by the id_grid_run column
 * @method RegimeEpisodeQuery groupByVerdict() Group by the verdict column
 * @method RegimeEpisodeQuery groupByOpenedAt() Group by the opened_at column
 * @method RegimeEpisodeQuery groupByClosedAt() Group by the closed_at column
 * @method RegimeEpisodeQuery groupByPriceOpen() Group by the price_open column
 * @method RegimeEpisodeQuery groupByPriceClose() Group by the price_close column
 * @method RegimeEpisodeQuery groupByEngagedPctTw() Group by the engaged_pct_tw column
 * @method RegimeEpisodeQuery groupBySamples() Group by the samples column
 * @method RegimeEpisodeQuery groupByRealized() Group by the realized column
 * @method RegimeEpisodeQuery groupByMtmClose() Group by the mtm_close column
 * @method RegimeEpisodeQuery groupByHodlPct() Group by the hodl_pct column
 * @method RegimeEpisodeQuery groupByCapturedPct() Group by the captured_pct column
 * @method RegimeEpisodeQuery groupByIdleSamples() Group by the idle_samples column
 * @method RegimeEpisodeQuery groupByIdleAlertedAt() Group by the idle_alerted_at column
 * @method RegimeEpisodeQuery groupByDateCreation() Group by the date_creation column
 * @method RegimeEpisodeQuery groupByDateModification() Group by the date_modification column
 * @method RegimeEpisodeQuery groupByIdGroupCreation() Group by the id_group_creation column
 * @method RegimeEpisodeQuery groupByIdCreation() Group by the id_creation column
 * @method RegimeEpisodeQuery groupByIdModification() Group by the id_modification column
 *
 * @method RegimeEpisodeQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method RegimeEpisodeQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method RegimeEpisodeQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method RegimeEpisodeQuery leftJoinFleetSlot($relationAlias = null) Adds a LEFT JOIN clause to the query using the FleetSlot relation
 * @method RegimeEpisodeQuery rightJoinFleetSlot($relationAlias = null) Adds a RIGHT JOIN clause to the query using the FleetSlot relation
 * @method RegimeEpisodeQuery innerJoinFleetSlot($relationAlias = null) Adds a INNER JOIN clause to the query using the FleetSlot relation
 *
 * @method RegimeEpisodeQuery leftJoinGridRun($relationAlias = null) Adds a LEFT JOIN clause to the query using the GridRun relation
 * @method RegimeEpisodeQuery rightJoinGridRun($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GridRun relation
 * @method RegimeEpisodeQuery innerJoinGridRun($relationAlias = null) Adds a INNER JOIN clause to the query using the GridRun relation
 *
 * @method RegimeEpisodeQuery leftJoinAuthyGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroup relation
 * @method RegimeEpisodeQuery rightJoinAuthyGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroup relation
 * @method RegimeEpisodeQuery innerJoinAuthyGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroup relation
 *
 * @method RegimeEpisodeQuery leftJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method RegimeEpisodeQuery rightJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method RegimeEpisodeQuery innerJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdCreation relation
 *
 * @method RegimeEpisodeQuery leftJoinAuthyRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method RegimeEpisodeQuery rightJoinAuthyRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method RegimeEpisodeQuery innerJoinAuthyRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdModification relation
 *
 * @method RegimeEpisode findOne(?PropelPDO $con = null) Return the first RegimeEpisode matching the query
 * @method RegimeEpisode findOneOrCreate(?PropelPDO $con = null) Return the first RegimeEpisode matching the query, or a new RegimeEpisode object populated from the query conditions when no match is found
 *
 * @method RegimeEpisode findOneBySymbol(string $symbol) Return the first RegimeEpisode filtered by the symbol column
 * @method RegimeEpisode findOneByAlgo(int $algo) Return the first RegimeEpisode filtered by the algo column
 * @method RegimeEpisode findOneByIdFleetSlot(int $id_fleet_slot) Return the first RegimeEpisode filtered by the id_fleet_slot column
 * @method RegimeEpisode findOneByIdGridRun(int $id_grid_run) Return the first RegimeEpisode filtered by the id_grid_run column
 * @method RegimeEpisode findOneByVerdict(string $verdict) Return the first RegimeEpisode filtered by the verdict column
 * @method RegimeEpisode findOneByOpenedAt(string $opened_at) Return the first RegimeEpisode filtered by the opened_at column
 * @method RegimeEpisode findOneByClosedAt(string $closed_at) Return the first RegimeEpisode filtered by the closed_at column
 * @method RegimeEpisode findOneByPriceOpen(string $price_open) Return the first RegimeEpisode filtered by the price_open column
 * @method RegimeEpisode findOneByPriceClose(string $price_close) Return the first RegimeEpisode filtered by the price_close column
 * @method RegimeEpisode findOneByEngagedPctTw(string $engaged_pct_tw) Return the first RegimeEpisode filtered by the engaged_pct_tw column
 * @method RegimeEpisode findOneBySamples(int $samples) Return the first RegimeEpisode filtered by the samples column
 * @method RegimeEpisode findOneByRealized(string $realized) Return the first RegimeEpisode filtered by the realized column
 * @method RegimeEpisode findOneByMtmClose(string $mtm_close) Return the first RegimeEpisode filtered by the mtm_close column
 * @method RegimeEpisode findOneByHodlPct(string $hodl_pct) Return the first RegimeEpisode filtered by the hodl_pct column
 * @method RegimeEpisode findOneByCapturedPct(string $captured_pct) Return the first RegimeEpisode filtered by the captured_pct column
 * @method RegimeEpisode findOneByIdleSamples(int $idle_samples) Return the first RegimeEpisode filtered by the idle_samples column
 * @method RegimeEpisode findOneByIdleAlertedAt(string $idle_alerted_at) Return the first RegimeEpisode filtered by the idle_alerted_at column
 * @method RegimeEpisode findOneByDateCreation(string $date_creation) Return the first RegimeEpisode filtered by the date_creation column
 * @method RegimeEpisode findOneByDateModification(string $date_modification) Return the first RegimeEpisode filtered by the date_modification column
 * @method RegimeEpisode findOneByIdGroupCreation(int $id_group_creation) Return the first RegimeEpisode filtered by the id_group_creation column
 * @method RegimeEpisode findOneByIdCreation(int $id_creation) Return the first RegimeEpisode filtered by the id_creation column
 * @method RegimeEpisode findOneByIdModification(int $id_modification) Return the first RegimeEpisode filtered by the id_modification column
 *
 * @method array findByIdRegimeEpisode(int $id_regime_episode) Return RegimeEpisode objects filtered by the id_regime_episode column
 * @method array findBySymbol(string $symbol) Return RegimeEpisode objects filtered by the symbol column
 * @method array findByAlgo(int $algo) Return RegimeEpisode objects filtered by the algo column
 * @method array findByIdFleetSlot(int $id_fleet_slot) Return RegimeEpisode objects filtered by the id_fleet_slot column
 * @method array findByIdGridRun(int $id_grid_run) Return RegimeEpisode objects filtered by the id_grid_run column
 * @method array findByVerdict(string $verdict) Return RegimeEpisode objects filtered by the verdict column
 * @method array findByOpenedAt(string $opened_at) Return RegimeEpisode objects filtered by the opened_at column
 * @method array findByClosedAt(string $closed_at) Return RegimeEpisode objects filtered by the closed_at column
 * @method array findByPriceOpen(string $price_open) Return RegimeEpisode objects filtered by the price_open column
 * @method array findByPriceClose(string $price_close) Return RegimeEpisode objects filtered by the price_close column
 * @method array findByEngagedPctTw(string $engaged_pct_tw) Return RegimeEpisode objects filtered by the engaged_pct_tw column
 * @method array findBySamples(int $samples) Return RegimeEpisode objects filtered by the samples column
 * @method array findByRealized(string $realized) Return RegimeEpisode objects filtered by the realized column
 * @method array findByMtmClose(string $mtm_close) Return RegimeEpisode objects filtered by the mtm_close column
 * @method array findByHodlPct(string $hodl_pct) Return RegimeEpisode objects filtered by the hodl_pct column
 * @method array findByCapturedPct(string $captured_pct) Return RegimeEpisode objects filtered by the captured_pct column
 * @method array findByIdleSamples(int $idle_samples) Return RegimeEpisode objects filtered by the idle_samples column
 * @method array findByIdleAlertedAt(string $idle_alerted_at) Return RegimeEpisode objects filtered by the idle_alerted_at column
 * @method array findByDateCreation(string $date_creation) Return RegimeEpisode objects filtered by the date_creation column
 * @method array findByDateModification(string $date_modification) Return RegimeEpisode objects filtered by the date_modification column
 * @method array findByIdGroupCreation(int $id_group_creation) Return RegimeEpisode objects filtered by the id_group_creation column
 * @method array findByIdCreation(int $id_creation) Return RegimeEpisode objects filtered by the id_creation column
 * @method array findByIdModification(int $id_modification) Return RegimeEpisode objects filtered by the id_modification column
 *
 * @package    propel.generator..om
 */
abstract class BaseRegimeEpisodeQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseRegimeEpisodeQuery object.
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
            $modelName = 'App\\RegimeEpisode';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new RegimeEpisodeQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   RegimeEpisodeQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return RegimeEpisodeQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof RegimeEpisodeQuery) {
            return $criteria;
        }
        $query = new RegimeEpisodeQuery(null, null, $modelAlias);

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
     * @return   RegimeEpisode|RegimeEpisode[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = RegimeEpisodePeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 RegimeEpisode A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdRegimeEpisode($key, $con = null)
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
     * @return                 RegimeEpisode A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_regime_episode`, `symbol`, `algo`, `id_fleet_slot`, `id_grid_run`, `verdict`, `opened_at`, `closed_at`, `price_open`, `price_close`, `engaged_pct_tw`, `samples`, `realized`, `mtm_close`, `hodl_pct`, `captured_pct`, `idle_samples`, `idle_alerted_at`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification` FROM `regime_episode` WHERE `id_regime_episode` = :p0';
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
            $obj = new RegimeEpisode();
            $obj->hydrate($row);
            RegimeEpisodePeer::addInstanceToPool($obj, (string) $key);
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
     * @return RegimeEpisode|RegimeEpisode[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|RegimeEpisode[]|mixed the list of results, formatted by the current formatter
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
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(RegimeEpisodePeer::ID_REGIME_EPISODE, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(RegimeEpisodePeer::ID_REGIME_EPISODE, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id_regime_episode column
     *
     * Example usage:
     * <code>
     * $query->filterByIdRegimeEpisode(1234); // WHERE id_regime_episode = 1234
     * $query->filterByIdRegimeEpisode(array(12, 34)); // WHERE id_regime_episode IN (12, 34)
     * $query->filterByIdRegimeEpisode(array('min' => 12)); // WHERE id_regime_episode >= 12
     * $query->filterByIdRegimeEpisode(array('max' => 12)); // WHERE id_regime_episode <= 12
     * </code>
     *
     * @param     mixed $idRegimeEpisode The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByIdRegimeEpisode($idRegimeEpisode = null, $comparison = null)
    {
        if (is_array($idRegimeEpisode)) {
            $useMinMax = false;
            if (isset($idRegimeEpisode['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::ID_REGIME_EPISODE, $idRegimeEpisode['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idRegimeEpisode['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::ID_REGIME_EPISODE, $idRegimeEpisode['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::ID_REGIME_EPISODE, $idRegimeEpisode, $comparison);
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
     * @return RegimeEpisodeQuery The current query, for fluid interface
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

        return $this->addUsingAlias(RegimeEpisodePeer::SYMBOL, $symbol, $comparison);
    }

    /**
     * Filter the query on the algo column
     *
     * @param     mixed $algo The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return RegimeEpisodeQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByAlgo($algo = null, $comparison = null)
    {
        if (is_scalar($algo)) {
            $algo = RegimeEpisodePeer::getSqlValueForEnum(RegimeEpisodePeer::ALGO, $algo);
        } elseif (is_array($algo)) {
            $convertedValues = array();
            foreach ($algo as $value) {
                $convertedValues[] = RegimeEpisodePeer::getSqlValueForEnum(RegimeEpisodePeer::ALGO, $value);
            }
            $algo = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::ALGO, $algo, $comparison);
    }

    /**
     * Filter the query on the id_fleet_slot column
     *
     * Example usage:
     * <code>
     * $query->filterByIdFleetSlot(1234); // WHERE id_fleet_slot = 1234
     * $query->filterByIdFleetSlot(array(12, 34)); // WHERE id_fleet_slot IN (12, 34)
     * $query->filterByIdFleetSlot(array('min' => 12)); // WHERE id_fleet_slot >= 12
     * $query->filterByIdFleetSlot(array('max' => 12)); // WHERE id_fleet_slot <= 12
     * </code>
     *
     * @see       filterByFleetSlot()
     *
     * @param     mixed $idFleetSlot The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByIdFleetSlot($idFleetSlot = null, $comparison = null)
    {
        if (is_array($idFleetSlot)) {
            $useMinMax = false;
            if (isset($idFleetSlot['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::ID_FLEET_SLOT, $idFleetSlot['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idFleetSlot['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::ID_FLEET_SLOT, $idFleetSlot['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::ID_FLEET_SLOT, $idFleetSlot, $comparison);
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
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByIdGridRun($idGridRun = null, $comparison = null)
    {
        if (is_array($idGridRun)) {
            $useMinMax = false;
            if (isset($idGridRun['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::ID_GRID_RUN, $idGridRun['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGridRun['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::ID_GRID_RUN, $idGridRun['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::ID_GRID_RUN, $idGridRun, $comparison);
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
     * @return RegimeEpisodeQuery The current query, for fluid interface
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

        return $this->addUsingAlias(RegimeEpisodePeer::VERDICT, $verdict, $comparison);
    }

    /**
     * Filter the query on the opened_at column
     *
     * Example usage:
     * <code>
     * $query->filterByOpenedAt('2011-03-14'); // WHERE opened_at = '2011-03-14'
     * $query->filterByOpenedAt('now'); // WHERE opened_at = '2011-03-14'
     * $query->filterByOpenedAt(array('max' => 'yesterday')); // WHERE opened_at < '2011-03-13'
     * </code>
     *
     * @param     mixed $openedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByOpenedAt($openedAt = null, $comparison = null)
    {
        if (is_array($openedAt)) {
            $useMinMax = false;
            if (isset($openedAt['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::OPENED_AT, $openedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($openedAt['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::OPENED_AT, $openedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::OPENED_AT, $openedAt, $comparison);
    }

    /**
     * Filter the query on the closed_at column
     *
     * Example usage:
     * <code>
     * $query->filterByClosedAt('2011-03-14'); // WHERE closed_at = '2011-03-14'
     * $query->filterByClosedAt('now'); // WHERE closed_at = '2011-03-14'
     * $query->filterByClosedAt(array('max' => 'yesterday')); // WHERE closed_at < '2011-03-13'
     * </code>
     *
     * @param     mixed $closedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByClosedAt($closedAt = null, $comparison = null)
    {
        if (is_array($closedAt)) {
            $useMinMax = false;
            if (isset($closedAt['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::CLOSED_AT, $closedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($closedAt['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::CLOSED_AT, $closedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::CLOSED_AT, $closedAt, $comparison);
    }

    /**
     * Filter the query on the price_open column
     *
     * Example usage:
     * <code>
     * $query->filterByPriceOpen(1234); // WHERE price_open = 1234
     * $query->filterByPriceOpen(array(12, 34)); // WHERE price_open IN (12, 34)
     * $query->filterByPriceOpen(array('min' => 12)); // WHERE price_open >= 12
     * $query->filterByPriceOpen(array('max' => 12)); // WHERE price_open <= 12
     * </code>
     *
     * @param     mixed $priceOpen The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByPriceOpen($priceOpen = null, $comparison = null)
    {
        if (is_array($priceOpen)) {
            $useMinMax = false;
            if (isset($priceOpen['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::PRICE_OPEN, $priceOpen['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($priceOpen['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::PRICE_OPEN, $priceOpen['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::PRICE_OPEN, $priceOpen, $comparison);
    }

    /**
     * Filter the query on the price_close column
     *
     * Example usage:
     * <code>
     * $query->filterByPriceClose(1234); // WHERE price_close = 1234
     * $query->filterByPriceClose(array(12, 34)); // WHERE price_close IN (12, 34)
     * $query->filterByPriceClose(array('min' => 12)); // WHERE price_close >= 12
     * $query->filterByPriceClose(array('max' => 12)); // WHERE price_close <= 12
     * </code>
     *
     * @param     mixed $priceClose The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByPriceClose($priceClose = null, $comparison = null)
    {
        if (is_array($priceClose)) {
            $useMinMax = false;
            if (isset($priceClose['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::PRICE_CLOSE, $priceClose['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($priceClose['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::PRICE_CLOSE, $priceClose['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::PRICE_CLOSE, $priceClose, $comparison);
    }

    /**
     * Filter the query on the engaged_pct_tw column
     *
     * Example usage:
     * <code>
     * $query->filterByEngagedPctTw(1234); // WHERE engaged_pct_tw = 1234
     * $query->filterByEngagedPctTw(array(12, 34)); // WHERE engaged_pct_tw IN (12, 34)
     * $query->filterByEngagedPctTw(array('min' => 12)); // WHERE engaged_pct_tw >= 12
     * $query->filterByEngagedPctTw(array('max' => 12)); // WHERE engaged_pct_tw <= 12
     * </code>
     *
     * @param     mixed $engagedPctTw The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByEngagedPctTw($engagedPctTw = null, $comparison = null)
    {
        if (is_array($engagedPctTw)) {
            $useMinMax = false;
            if (isset($engagedPctTw['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::ENGAGED_PCT_TW, $engagedPctTw['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($engagedPctTw['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::ENGAGED_PCT_TW, $engagedPctTw['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::ENGAGED_PCT_TW, $engagedPctTw, $comparison);
    }

    /**
     * Filter the query on the samples column
     *
     * Example usage:
     * <code>
     * $query->filterBySamples(1234); // WHERE samples = 1234
     * $query->filterBySamples(array(12, 34)); // WHERE samples IN (12, 34)
     * $query->filterBySamples(array('min' => 12)); // WHERE samples >= 12
     * $query->filterBySamples(array('max' => 12)); // WHERE samples <= 12
     * </code>
     *
     * @param     mixed $samples The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterBySamples($samples = null, $comparison = null)
    {
        if (is_array($samples)) {
            $useMinMax = false;
            if (isset($samples['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::SAMPLES, $samples['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($samples['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::SAMPLES, $samples['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::SAMPLES, $samples, $comparison);
    }

    /**
     * Filter the query on the realized column
     *
     * Example usage:
     * <code>
     * $query->filterByRealized(1234); // WHERE realized = 1234
     * $query->filterByRealized(array(12, 34)); // WHERE realized IN (12, 34)
     * $query->filterByRealized(array('min' => 12)); // WHERE realized >= 12
     * $query->filterByRealized(array('max' => 12)); // WHERE realized <= 12
     * </code>
     *
     * @param     mixed $realized The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByRealized($realized = null, $comparison = null)
    {
        if (is_array($realized)) {
            $useMinMax = false;
            if (isset($realized['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::REALIZED, $realized['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($realized['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::REALIZED, $realized['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::REALIZED, $realized, $comparison);
    }

    /**
     * Filter the query on the mtm_close column
     *
     * Example usage:
     * <code>
     * $query->filterByMtmClose(1234); // WHERE mtm_close = 1234
     * $query->filterByMtmClose(array(12, 34)); // WHERE mtm_close IN (12, 34)
     * $query->filterByMtmClose(array('min' => 12)); // WHERE mtm_close >= 12
     * $query->filterByMtmClose(array('max' => 12)); // WHERE mtm_close <= 12
     * </code>
     *
     * @param     mixed $mtmClose The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByMtmClose($mtmClose = null, $comparison = null)
    {
        if (is_array($mtmClose)) {
            $useMinMax = false;
            if (isset($mtmClose['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::MTM_CLOSE, $mtmClose['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($mtmClose['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::MTM_CLOSE, $mtmClose['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::MTM_CLOSE, $mtmClose, $comparison);
    }

    /**
     * Filter the query on the hodl_pct column
     *
     * Example usage:
     * <code>
     * $query->filterByHodlPct(1234); // WHERE hodl_pct = 1234
     * $query->filterByHodlPct(array(12, 34)); // WHERE hodl_pct IN (12, 34)
     * $query->filterByHodlPct(array('min' => 12)); // WHERE hodl_pct >= 12
     * $query->filterByHodlPct(array('max' => 12)); // WHERE hodl_pct <= 12
     * </code>
     *
     * @param     mixed $hodlPct The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByHodlPct($hodlPct = null, $comparison = null)
    {
        if (is_array($hodlPct)) {
            $useMinMax = false;
            if (isset($hodlPct['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::HODL_PCT, $hodlPct['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($hodlPct['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::HODL_PCT, $hodlPct['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::HODL_PCT, $hodlPct, $comparison);
    }

    /**
     * Filter the query on the captured_pct column
     *
     * Example usage:
     * <code>
     * $query->filterByCapturedPct(1234); // WHERE captured_pct = 1234
     * $query->filterByCapturedPct(array(12, 34)); // WHERE captured_pct IN (12, 34)
     * $query->filterByCapturedPct(array('min' => 12)); // WHERE captured_pct >= 12
     * $query->filterByCapturedPct(array('max' => 12)); // WHERE captured_pct <= 12
     * </code>
     *
     * @param     mixed $capturedPct The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByCapturedPct($capturedPct = null, $comparison = null)
    {
        if (is_array($capturedPct)) {
            $useMinMax = false;
            if (isset($capturedPct['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::CAPTURED_PCT, $capturedPct['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($capturedPct['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::CAPTURED_PCT, $capturedPct['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::CAPTURED_PCT, $capturedPct, $comparison);
    }

    /**
     * Filter the query on the idle_samples column
     *
     * Example usage:
     * <code>
     * $query->filterByIdleSamples(1234); // WHERE idle_samples = 1234
     * $query->filterByIdleSamples(array(12, 34)); // WHERE idle_samples IN (12, 34)
     * $query->filterByIdleSamples(array('min' => 12)); // WHERE idle_samples >= 12
     * $query->filterByIdleSamples(array('max' => 12)); // WHERE idle_samples <= 12
     * </code>
     *
     * @param     mixed $idleSamples The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByIdleSamples($idleSamples = null, $comparison = null)
    {
        if (is_array($idleSamples)) {
            $useMinMax = false;
            if (isset($idleSamples['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::IDLE_SAMPLES, $idleSamples['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idleSamples['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::IDLE_SAMPLES, $idleSamples['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::IDLE_SAMPLES, $idleSamples, $comparison);
    }

    /**
     * Filter the query on the idle_alerted_at column
     *
     * Example usage:
     * <code>
     * $query->filterByIdleAlertedAt('2011-03-14'); // WHERE idle_alerted_at = '2011-03-14'
     * $query->filterByIdleAlertedAt('now'); // WHERE idle_alerted_at = '2011-03-14'
     * $query->filterByIdleAlertedAt(array('max' => 'yesterday')); // WHERE idle_alerted_at < '2011-03-13'
     * </code>
     *
     * @param     mixed $idleAlertedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByIdleAlertedAt($idleAlertedAt = null, $comparison = null)
    {
        if (is_array($idleAlertedAt)) {
            $useMinMax = false;
            if (isset($idleAlertedAt['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::IDLE_ALERTED_AT, $idleAlertedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idleAlertedAt['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::IDLE_ALERTED_AT, $idleAlertedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::IDLE_ALERTED_AT, $idleAlertedAt, $comparison);
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
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByDateCreation($dateCreation = null, $comparison = null)
    {
        if (is_array($dateCreation)) {
            $useMinMax = false;
            if (isset($dateCreation['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::DATE_CREATION, $dateCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateCreation['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::DATE_CREATION, $dateCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::DATE_CREATION, $dateCreation, $comparison);
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
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByDateModification($dateModification = null, $comparison = null)
    {
        if (is_array($dateModification)) {
            $useMinMax = false;
            if (isset($dateModification['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::DATE_MODIFICATION, $dateModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateModification['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::DATE_MODIFICATION, $dateModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::DATE_MODIFICATION, $dateModification, $comparison);
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
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByIdGroupCreation($idGroupCreation = null, $comparison = null)
    {
        if (is_array($idGroupCreation)) {
            $useMinMax = false;
            if (isset($idGroupCreation['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::ID_GROUP_CREATION, $idGroupCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGroupCreation['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::ID_GROUP_CREATION, $idGroupCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::ID_GROUP_CREATION, $idGroupCreation, $comparison);
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
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByIdCreation($idCreation = null, $comparison = null)
    {
        if (is_array($idCreation)) {
            $useMinMax = false;
            if (isset($idCreation['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::ID_CREATION, $idCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idCreation['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::ID_CREATION, $idCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::ID_CREATION, $idCreation, $comparison);
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
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function filterByIdModification($idModification = null, $comparison = null)
    {
        if (is_array($idModification)) {
            $useMinMax = false;
            if (isset($idModification['min'])) {
                $this->addUsingAlias(RegimeEpisodePeer::ID_MODIFICATION, $idModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idModification['max'])) {
                $this->addUsingAlias(RegimeEpisodePeer::ID_MODIFICATION, $idModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(RegimeEpisodePeer::ID_MODIFICATION, $idModification, $comparison);
    }

    /**
     * Filter the query by a related FleetSlot object
     *
     * @param   FleetSlot|PropelObjectCollection $fleetSlot The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 RegimeEpisodeQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByFleetSlot($fleetSlot, $comparison = null)
    {
        if ($fleetSlot instanceof FleetSlot) {
            return $this
                ->addUsingAlias(RegimeEpisodePeer::ID_FLEET_SLOT, $fleetSlot->getIdFleetSlot(), $comparison);
        } elseif ($fleetSlot instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(RegimeEpisodePeer::ID_FLEET_SLOT, $fleetSlot->toKeyValue('PrimaryKey', 'IdFleetSlot'), $comparison);
        } else {
            throw new PropelException('filterByFleetSlot() only accepts arguments of type FleetSlot or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the FleetSlot relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function joinFleetSlot($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('FleetSlot');

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
            $this->addJoinObject($join, 'FleetSlot');
        }

        return $this;
    }

    /**
     * Use the FleetSlot relation FleetSlot object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\FleetSlotQuery A secondary query class using the current class as primary query
     */
    public function useFleetSlotQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinFleetSlot($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'FleetSlot', '\App\FleetSlotQuery');
    }

    /**
     * Filter the query by a related GridRun object
     *
     * @param   GridRun|PropelObjectCollection $gridRun The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 RegimeEpisodeQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByGridRun($gridRun, $comparison = null)
    {
        if ($gridRun instanceof GridRun) {
            return $this
                ->addUsingAlias(RegimeEpisodePeer::ID_GRID_RUN, $gridRun->getIdGridRun(), $comparison);
        } elseif ($gridRun instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(RegimeEpisodePeer::ID_GRID_RUN, $gridRun->toKeyValue('PrimaryKey', 'IdGridRun'), $comparison);
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
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function joinGridRun($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
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
    public function useGridRunQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
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
     * @return                 RegimeEpisodeQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroup($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(RegimeEpisodePeer::ID_GROUP_CREATION, $authyGroup->getIdAuthyGroup(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(RegimeEpisodePeer::ID_GROUP_CREATION, $authyGroup->toKeyValue('PrimaryKey', 'IdAuthyGroup'), $comparison);
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
     * @return RegimeEpisodeQuery The current query, for fluid interface
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
     * @return                 RegimeEpisodeQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdCreation($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(RegimeEpisodePeer::ID_CREATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(RegimeEpisodePeer::ID_CREATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return RegimeEpisodeQuery The current query, for fluid interface
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
     * @return                 RegimeEpisodeQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdModification($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(RegimeEpisodePeer::ID_MODIFICATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(RegimeEpisodePeer::ID_MODIFICATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return RegimeEpisodeQuery The current query, for fluid interface
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
     * @param   RegimeEpisode $regimeEpisode Object to remove from the list of results
     *
     * @return RegimeEpisodeQuery The current query, for fluid interface
     */
    public function prune($regimeEpisode = null)
    {
        if ($regimeEpisode) {
            $this->addUsingAlias(RegimeEpisodePeer::ID_REGIME_EPISODE, $regimeEpisode->getIdRegimeEpisode(), Criteria::NOT_EQUAL);
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
                    \ApiGoat\Utility\TableVersion::bump('regime_episode');
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
                    \ApiGoat\Utility\TableVersion::bump('regime_episode');
                }

        return $this->postUpdate($affectedRows, $con);
    }

    // add_tablestamp behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     RegimeEpisodeQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7){
        return $this->addUsingAlias(RegimeEpisodePeer::DATE_MODIFICATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     RegimeEpisodeQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst(){
        return $this->addDescendingOrderByColumn(RegimeEpisodePeer::DATE_MODIFICATION);
    }

    /**
     * Order by update date asc
     *
     * @return     RegimeEpisodeQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst(){
        return $this->addAscendingOrderByColumn(RegimeEpisodePeer::DATE_MODIFICATION);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     RegimeEpisodeQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7){
        return $this->addUsingAlias(RegimeEpisodePeer::DATE_CREATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return     RegimeEpisodeQuery The current query, for fluid interface
     */
    public function lastCreatedFirst(){
        return $this->addDescendingOrderByColumn(RegimeEpisodePeer::DATE_CREATION);
    }

    /**
     * Order by create date asc
     *
     * @return     RegimeEpisodeQuery The current query, for fluid interface
     */
    public function firstCreatedFirst(){
        return $this->addAscendingOrderByColumn(RegimeEpisodePeer::DATE_CREATION);
    }
}
