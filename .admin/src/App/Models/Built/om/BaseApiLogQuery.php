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
use App\ApiLog;
use App\ApiLogPeer;
use App\ApiLogQuery;
use App\ApiRbac;
use App\Authy;

/**
 * Base class that represents a query for the 'api_log' table.
 *
 * API log
 *
 * @method ApiLogQuery orderByIdApiLog($order = Criteria::ASC) Order by the id_api_log column
 * @method ApiLogQuery orderByIdApiRbac($order = Criteria::ASC) Order by the id_api_rbac column
 * @method ApiLogQuery orderByIdAuthy($order = Criteria::ASC) Order by the id_authy column
 * @method ApiLogQuery orderByTime($order = Criteria::ASC) Order by the time column
 * @method ApiLogQuery orderByRawParameters($order = Criteria::ASC) Order by the raw_parameters column
 * @method ApiLogQuery orderByCount($order = Criteria::ASC) Order by the count column
 *
 * @method ApiLogQuery groupByIdApiLog() Group by the id_api_log column
 * @method ApiLogQuery groupByIdApiRbac() Group by the id_api_rbac column
 * @method ApiLogQuery groupByIdAuthy() Group by the id_authy column
 * @method ApiLogQuery groupByTime() Group by the time column
 * @method ApiLogQuery groupByRawParameters() Group by the raw_parameters column
 * @method ApiLogQuery groupByCount() Group by the count column
 *
 * @method ApiLogQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method ApiLogQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method ApiLogQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method ApiLogQuery leftJoinApiRbac($relationAlias = null) Adds a LEFT JOIN clause to the query using the ApiRbac relation
 * @method ApiLogQuery rightJoinApiRbac($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ApiRbac relation
 * @method ApiLogQuery innerJoinApiRbac($relationAlias = null) Adds a INNER JOIN clause to the query using the ApiRbac relation
 *
 * @method ApiLogQuery leftJoinAuthy($relationAlias = null) Adds a LEFT JOIN clause to the query using the Authy relation
 * @method ApiLogQuery rightJoinAuthy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Authy relation
 * @method ApiLogQuery innerJoinAuthy($relationAlias = null) Adds a INNER JOIN clause to the query using the Authy relation
 *
 * @method ApiLog findOne(?PropelPDO $con = null) Return the first ApiLog matching the query
 * @method ApiLog findOneOrCreate(?PropelPDO $con = null) Return the first ApiLog matching the query, or a new ApiLog object populated from the query conditions when no match is found
 *
 * @method ApiLog findOneByIdApiRbac(int $id_api_rbac) Return the first ApiLog filtered by the id_api_rbac column
 * @method ApiLog findOneByIdAuthy(int $id_authy) Return the first ApiLog filtered by the id_authy column
 * @method ApiLog findOneByTime(string $time) Return the first ApiLog filtered by the time column
 * @method ApiLog findOneByRawParameters(string $raw_parameters) Return the first ApiLog filtered by the raw_parameters column
 * @method ApiLog findOneByCount(int $count) Return the first ApiLog filtered by the count column
 *
 * @method array findByIdApiLog(int $id_api_log) Return ApiLog objects filtered by the id_api_log column
 * @method array findByIdApiRbac(int $id_api_rbac) Return ApiLog objects filtered by the id_api_rbac column
 * @method array findByIdAuthy(int $id_authy) Return ApiLog objects filtered by the id_authy column
 * @method array findByTime(string $time) Return ApiLog objects filtered by the time column
 * @method array findByRawParameters(string $raw_parameters) Return ApiLog objects filtered by the raw_parameters column
 * @method array findByCount(int $count) Return ApiLog objects filtered by the count column
 *
 * @package    propel.generator..om
 */
abstract class BaseApiLogQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseApiLogQuery object.
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
            $modelName = 'App\\ApiLog';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ApiLogQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   ApiLogQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return ApiLogQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof ApiLogQuery) {
            return $criteria;
        }
        $query = new ApiLogQuery(null, null, $modelAlias);

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
     * @return   ApiLog|ApiLog[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = ApiLogPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(ApiLogPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 ApiLog A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdApiLog($key, $con = null)
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
     * @return                 ApiLog A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_api_log`, `id_api_rbac`, `id_authy`, `time`, `raw_parameters`, `count` FROM `api_log` WHERE `id_api_log` = :p0';
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
            $obj = new ApiLog();
            $obj->hydrate($row);
            ApiLogPeer::addInstanceToPool($obj, (string) $key);
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
     * @return ApiLog|ApiLog[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|ApiLog[]|mixed the list of results, formatted by the current formatter
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
     * @return ApiLogQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(ApiLogPeer::ID_API_LOG, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ApiLogQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(ApiLogPeer::ID_API_LOG, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id_api_log column
     *
     * Example usage:
     * <code>
     * $query->filterByIdApiLog(1234); // WHERE id_api_log = 1234
     * $query->filterByIdApiLog(array(12, 34)); // WHERE id_api_log IN (12, 34)
     * $query->filterByIdApiLog(array('min' => 12)); // WHERE id_api_log >= 12
     * $query->filterByIdApiLog(array('max' => 12)); // WHERE id_api_log <= 12
     * </code>
     *
     * @param     mixed $idApiLog The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ApiLogQuery The current query, for fluid interface
     */
    public function filterByIdApiLog($idApiLog = null, $comparison = null)
    {
        if (is_array($idApiLog)) {
            $useMinMax = false;
            if (isset($idApiLog['min'])) {
                $this->addUsingAlias(ApiLogPeer::ID_API_LOG, $idApiLog['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idApiLog['max'])) {
                $this->addUsingAlias(ApiLogPeer::ID_API_LOG, $idApiLog['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ApiLogPeer::ID_API_LOG, $idApiLog, $comparison);
    }

    /**
     * Filter the query on the id_api_rbac column
     *
     * Example usage:
     * <code>
     * $query->filterByIdApiRbac(1234); // WHERE id_api_rbac = 1234
     * $query->filterByIdApiRbac(array(12, 34)); // WHERE id_api_rbac IN (12, 34)
     * $query->filterByIdApiRbac(array('min' => 12)); // WHERE id_api_rbac >= 12
     * $query->filterByIdApiRbac(array('max' => 12)); // WHERE id_api_rbac <= 12
     * </code>
     *
     * @see       filterByApiRbac()
     *
     * @param     mixed $idApiRbac The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ApiLogQuery The current query, for fluid interface
     */
    public function filterByIdApiRbac($idApiRbac = null, $comparison = null)
    {
        if (is_array($idApiRbac)) {
            $useMinMax = false;
            if (isset($idApiRbac['min'])) {
                $this->addUsingAlias(ApiLogPeer::ID_API_RBAC, $idApiRbac['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idApiRbac['max'])) {
                $this->addUsingAlias(ApiLogPeer::ID_API_RBAC, $idApiRbac['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ApiLogPeer::ID_API_RBAC, $idApiRbac, $comparison);
    }

    /**
     * Filter the query on the id_authy column
     *
     * Example usage:
     * <code>
     * $query->filterByIdAuthy(1234); // WHERE id_authy = 1234
     * $query->filterByIdAuthy(array(12, 34)); // WHERE id_authy IN (12, 34)
     * $query->filterByIdAuthy(array('min' => 12)); // WHERE id_authy >= 12
     * $query->filterByIdAuthy(array('max' => 12)); // WHERE id_authy <= 12
     * </code>
     *
     * @see       filterByAuthy()
     *
     * @param     mixed $idAuthy The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ApiLogQuery The current query, for fluid interface
     */
    public function filterByIdAuthy($idAuthy = null, $comparison = null)
    {
        if (is_array($idAuthy)) {
            $useMinMax = false;
            if (isset($idAuthy['min'])) {
                $this->addUsingAlias(ApiLogPeer::ID_AUTHY, $idAuthy['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idAuthy['max'])) {
                $this->addUsingAlias(ApiLogPeer::ID_AUTHY, $idAuthy['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ApiLogPeer::ID_AUTHY, $idAuthy, $comparison);
    }

    /**
     * Filter the query on the time column
     *
     * Example usage:
     * <code>
     * $query->filterByTime('2011-03-14'); // WHERE time = '2011-03-14'
     * $query->filterByTime('now'); // WHERE time = '2011-03-14'
     * $query->filterByTime(array('max' => 'yesterday')); // WHERE time < '2011-03-13'
     * </code>
     *
     * @param     mixed $time The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ApiLogQuery The current query, for fluid interface
     */
    public function filterByTime($time = null, $comparison = null)
    {
        if (is_array($time)) {
            $useMinMax = false;
            if (isset($time['min'])) {
                $this->addUsingAlias(ApiLogPeer::TIME, $time['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($time['max'])) {
                $this->addUsingAlias(ApiLogPeer::TIME, $time['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ApiLogPeer::TIME, $time, $comparison);
    }

    /**
     * Filter the query on the raw_parameters column
     *
     * Example usage:
     * <code>
     * $query->filterByRawParameters('fooValue');   // WHERE raw_parameters = 'fooValue'
     * $query->filterByRawParameters('%fooValue%'); // WHERE raw_parameters LIKE '%fooValue%'
     * </code>
     *
     * @param     string $rawParameters The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ApiLogQuery The current query, for fluid interface
     */
    public function filterByRawParameters($rawParameters = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($rawParameters)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $rawParameters)) {
                $rawParameters = str_replace('*', '%', $rawParameters);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(ApiLogPeer::RAW_PARAMETERS, $rawParameters, $comparison);
    }

    /**
     * Filter the query on the count column
     *
     * Example usage:
     * <code>
     * $query->filterByCount(1234); // WHERE count = 1234
     * $query->filterByCount(array(12, 34)); // WHERE count IN (12, 34)
     * $query->filterByCount(array('min' => 12)); // WHERE count >= 12
     * $query->filterByCount(array('max' => 12)); // WHERE count <= 12
     * </code>
     *
     * @param     mixed $count The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ApiLogQuery The current query, for fluid interface
     */
    public function filterByCount($count = null, $comparison = null)
    {
        if (is_array($count)) {
            $useMinMax = false;
            if (isset($count['min'])) {
                $this->addUsingAlias(ApiLogPeer::COUNT, $count['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($count['max'])) {
                $this->addUsingAlias(ApiLogPeer::COUNT, $count['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ApiLogPeer::COUNT, $count, $comparison);
    }

    /**
     * Filter the query by a related ApiRbac object
     *
     * @param   ApiRbac|PropelObjectCollection $apiRbac The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 ApiLogQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByApiRbac($apiRbac, $comparison = null)
    {
        if ($apiRbac instanceof ApiRbac) {
            return $this
                ->addUsingAlias(ApiLogPeer::ID_API_RBAC, $apiRbac->getIdApiRbac(), $comparison);
        } elseif ($apiRbac instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ApiLogPeer::ID_API_RBAC, $apiRbac->toKeyValue('PrimaryKey', 'IdApiRbac'), $comparison);
        } else {
            throw new PropelException('filterByApiRbac() only accepts arguments of type ApiRbac or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ApiRbac relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ApiLogQuery The current query, for fluid interface
     */
    public function joinApiRbac($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ApiRbac');

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
            $this->addJoinObject($join, 'ApiRbac');
        }

        return $this;
    }

    /**
     * Use the ApiRbac relation ApiRbac object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\ApiRbacQuery A secondary query class using the current class as primary query
     */
    public function useApiRbacQuery($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        return $this
            ->joinApiRbac($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ApiRbac', '\App\ApiRbacQuery');
    }

    /**
     * Filter the query by a related Authy object
     *
     * @param   Authy|PropelObjectCollection $authy The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 ApiLogQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthy($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(ApiLogPeer::ID_AUTHY, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ApiLogPeer::ID_AUTHY, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
        } else {
            throw new PropelException('filterByAuthy() only accepts arguments of type Authy or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Authy relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ApiLogQuery The current query, for fluid interface
     */
    public function joinAuthy($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Authy');

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
            $this->addJoinObject($join, 'Authy');
        }

        return $this;
    }

    /**
     * Use the Authy relation Authy object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyQuery A secondary query class using the current class as primary query
     */
    public function useAuthyQuery($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        return $this
            ->joinAuthy($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Authy', '\App\AuthyQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ApiLog $apiLog Object to remove from the list of results
     *
     * @return ApiLogQuery The current query, for fluid interface
     */
    public function prune($apiLog = null)
    {
        if ($apiLog) {
            $this->addUsingAlias(ApiLogPeer::ID_API_LOG, $apiLog->getIdApiLog(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

}
