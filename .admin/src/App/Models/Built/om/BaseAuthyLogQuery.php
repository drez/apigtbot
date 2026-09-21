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
use App\AuthyLog;
use App\AuthyLogPeer;
use App\AuthyLogQuery;

/**
 * Base class that represents a query for the 'authy_log' table.
 *
 * Login log
 *
 * @method AuthyLogQuery orderByIdAuthyLog($order = Criteria::ASC) Order by the id_authy_log column
 * @method AuthyLogQuery orderByIdAuthy($order = Criteria::ASC) Order by the id_authy column
 * @method AuthyLogQuery orderByTimestamp($order = Criteria::ASC) Order by the timestamp column
 * @method AuthyLogQuery orderByLogin($order = Criteria::ASC) Order by the login column
 * @method AuthyLogQuery orderByUserid($order = Criteria::ASC) Order by the userid column
 * @method AuthyLogQuery orderByResult($order = Criteria::ASC) Order by the result column
 * @method AuthyLogQuery orderByEvent($order = Criteria::ASC) Order by the event column
 * @method AuthyLogQuery orderByIp($order = Criteria::ASC) Order by the ip column
 * @method AuthyLogQuery orderByCount($order = Criteria::ASC) Order by the count column
 *
 * @method AuthyLogQuery groupByIdAuthyLog() Group by the id_authy_log column
 * @method AuthyLogQuery groupByIdAuthy() Group by the id_authy column
 * @method AuthyLogQuery groupByTimestamp() Group by the timestamp column
 * @method AuthyLogQuery groupByLogin() Group by the login column
 * @method AuthyLogQuery groupByUserid() Group by the userid column
 * @method AuthyLogQuery groupByResult() Group by the result column
 * @method AuthyLogQuery groupByEvent() Group by the event column
 * @method AuthyLogQuery groupByIp() Group by the ip column
 * @method AuthyLogQuery groupByCount() Group by the count column
 *
 * @method AuthyLogQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method AuthyLogQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method AuthyLogQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method AuthyLogQuery leftJoinAuthy($relationAlias = null) Adds a LEFT JOIN clause to the query using the Authy relation
 * @method AuthyLogQuery rightJoinAuthy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Authy relation
 * @method AuthyLogQuery innerJoinAuthy($relationAlias = null) Adds a INNER JOIN clause to the query using the Authy relation
 *
 * @method AuthyLog findOne(?PropelPDO $con = null) Return the first AuthyLog matching the query
 * @method AuthyLog findOneOrCreate(?PropelPDO $con = null) Return the first AuthyLog matching the query, or a new AuthyLog object populated from the query conditions when no match is found
 *
 * @method AuthyLog findOneByIdAuthy(int $id_authy) Return the first AuthyLog filtered by the id_authy column
 * @method AuthyLog findOneByTimestamp(string $timestamp) Return the first AuthyLog filtered by the timestamp column
 * @method AuthyLog findOneByLogin(string $login) Return the first AuthyLog filtered by the login column
 * @method AuthyLog findOneByUserid(int $userid) Return the first AuthyLog filtered by the userid column
 * @method AuthyLog findOneByResult(string $result) Return the first AuthyLog filtered by the result column
 * @method AuthyLog findOneByEvent(string $event) Return the first AuthyLog filtered by the event column
 * @method AuthyLog findOneByIp(string $ip) Return the first AuthyLog filtered by the ip column
 * @method AuthyLog findOneByCount(int $count) Return the first AuthyLog filtered by the count column
 *
 * @method array findByIdAuthyLog(int $id_authy_log) Return AuthyLog objects filtered by the id_authy_log column
 * @method array findByIdAuthy(int $id_authy) Return AuthyLog objects filtered by the id_authy column
 * @method array findByTimestamp(string $timestamp) Return AuthyLog objects filtered by the timestamp column
 * @method array findByLogin(string $login) Return AuthyLog objects filtered by the login column
 * @method array findByUserid(int $userid) Return AuthyLog objects filtered by the userid column
 * @method array findByResult(string $result) Return AuthyLog objects filtered by the result column
 * @method array findByEvent(string $event) Return AuthyLog objects filtered by the event column
 * @method array findByIp(string $ip) Return AuthyLog objects filtered by the ip column
 * @method array findByCount(int $count) Return AuthyLog objects filtered by the count column
 *
 * @package    propel.generator..om
 */
abstract class BaseAuthyLogQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseAuthyLogQuery object.
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
            $modelName = 'App\\AuthyLog';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new AuthyLogQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   AuthyLogQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return AuthyLogQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof AuthyLogQuery) {
            return $criteria;
        }
        $query = new AuthyLogQuery(null, null, $modelAlias);

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
     * @return   AuthyLog|AuthyLog[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = AuthyLogPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(AuthyLogPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 AuthyLog A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdAuthyLog($key, $con = null)
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
     * @return                 AuthyLog A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_authy_log`, `id_authy`, `timestamp`, `login`, `userid`, `result`, `event`, `ip`, `count` FROM `authy_log` WHERE `id_authy_log` = :p0';
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
            $obj = new AuthyLog();
            $obj->hydrate($row);
            AuthyLogPeer::addInstanceToPool($obj, (string) $key);
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
     * @return AuthyLog|AuthyLog[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|AuthyLog[]|mixed the list of results, formatted by the current formatter
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
     * @return AuthyLogQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(AuthyLogPeer::ID_AUTHY_LOG, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return AuthyLogQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(AuthyLogPeer::ID_AUTHY_LOG, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id_authy_log column
     *
     * Example usage:
     * <code>
     * $query->filterByIdAuthyLog(1234); // WHERE id_authy_log = 1234
     * $query->filterByIdAuthyLog(array(12, 34)); // WHERE id_authy_log IN (12, 34)
     * $query->filterByIdAuthyLog(array('min' => 12)); // WHERE id_authy_log >= 12
     * $query->filterByIdAuthyLog(array('max' => 12)); // WHERE id_authy_log <= 12
     * </code>
     *
     * @param     mixed $idAuthyLog The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyLogQuery The current query, for fluid interface
     */
    public function filterByIdAuthyLog($idAuthyLog = null, $comparison = null)
    {
        if (is_array($idAuthyLog)) {
            $useMinMax = false;
            if (isset($idAuthyLog['min'])) {
                $this->addUsingAlias(AuthyLogPeer::ID_AUTHY_LOG, $idAuthyLog['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idAuthyLog['max'])) {
                $this->addUsingAlias(AuthyLogPeer::ID_AUTHY_LOG, $idAuthyLog['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyLogPeer::ID_AUTHY_LOG, $idAuthyLog, $comparison);
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
     * @return AuthyLogQuery The current query, for fluid interface
     */
    public function filterByIdAuthy($idAuthy = null, $comparison = null)
    {
        if (is_array($idAuthy)) {
            $useMinMax = false;
            if (isset($idAuthy['min'])) {
                $this->addUsingAlias(AuthyLogPeer::ID_AUTHY, $idAuthy['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idAuthy['max'])) {
                $this->addUsingAlias(AuthyLogPeer::ID_AUTHY, $idAuthy['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyLogPeer::ID_AUTHY, $idAuthy, $comparison);
    }

    /**
     * Filter the query on the timestamp column
     *
     * Example usage:
     * <code>
     * $query->filterByTimestamp('2011-03-14'); // WHERE timestamp = '2011-03-14'
     * $query->filterByTimestamp('now'); // WHERE timestamp = '2011-03-14'
     * $query->filterByTimestamp(array('max' => 'yesterday')); // WHERE timestamp < '2011-03-13'
     * </code>
     *
     * @param     mixed $timestamp The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyLogQuery The current query, for fluid interface
     */
    public function filterByTimestamp($timestamp = null, $comparison = null)
    {
        if (is_array($timestamp)) {
            $useMinMax = false;
            if (isset($timestamp['min'])) {
                $this->addUsingAlias(AuthyLogPeer::TIMESTAMP, $timestamp['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($timestamp['max'])) {
                $this->addUsingAlias(AuthyLogPeer::TIMESTAMP, $timestamp['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyLogPeer::TIMESTAMP, $timestamp, $comparison);
    }

    /**
     * Filter the query on the login column
     *
     * Example usage:
     * <code>
     * $query->filterByLogin('fooValue');   // WHERE login = 'fooValue'
     * $query->filterByLogin('%fooValue%'); // WHERE login LIKE '%fooValue%'
     * </code>
     *
     * @param     string $login The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyLogQuery The current query, for fluid interface
     */
    public function filterByLogin($login = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($login)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $login)) {
                $login = str_replace('*', '%', $login);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyLogPeer::LOGIN, $login, $comparison);
    }

    /**
     * Filter the query on the userid column
     *
     * Example usage:
     * <code>
     * $query->filterByUserid(1234); // WHERE userid = 1234
     * $query->filterByUserid(array(12, 34)); // WHERE userid IN (12, 34)
     * $query->filterByUserid(array('min' => 12)); // WHERE userid >= 12
     * $query->filterByUserid(array('max' => 12)); // WHERE userid <= 12
     * </code>
     *
     * @param     mixed $userid The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyLogQuery The current query, for fluid interface
     */
    public function filterByUserid($userid = null, $comparison = null)
    {
        if (is_array($userid)) {
            $useMinMax = false;
            if (isset($userid['min'])) {
                $this->addUsingAlias(AuthyLogPeer::USERID, $userid['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userid['max'])) {
                $this->addUsingAlias(AuthyLogPeer::USERID, $userid['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyLogPeer::USERID, $userid, $comparison);
    }

    /**
     * Filter the query on the result column
     *
     * Example usage:
     * <code>
     * $query->filterByResult('fooValue');   // WHERE result = 'fooValue'
     * $query->filterByResult('%fooValue%'); // WHERE result LIKE '%fooValue%'
     * </code>
     *
     * @param     string $result The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyLogQuery The current query, for fluid interface
     */
    public function filterByResult($result = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($result)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $result)) {
                $result = str_replace('*', '%', $result);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyLogPeer::RESULT, $result, $comparison);
    }

    /**
     * Filter the query on the event column
     *
     * Example usage:
     * <code>
     * $query->filterByEvent('fooValue');   // WHERE event = 'fooValue'
     * $query->filterByEvent('%fooValue%'); // WHERE event LIKE '%fooValue%'
     * </code>
     *
     * @param     string $event The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyLogQuery The current query, for fluid interface
     */
    public function filterByEvent($event = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($event)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $event)) {
                $event = str_replace('*', '%', $event);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyLogPeer::EVENT, $event, $comparison);
    }

    /**
     * Filter the query on the ip column
     *
     * Example usage:
     * <code>
     * $query->filterByIp('fooValue');   // WHERE ip = 'fooValue'
     * $query->filterByIp('%fooValue%'); // WHERE ip LIKE '%fooValue%'
     * </code>
     *
     * @param     string $ip The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyLogQuery The current query, for fluid interface
     */
    public function filterByIp($ip = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($ip)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $ip)) {
                $ip = str_replace('*', '%', $ip);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyLogPeer::IP, $ip, $comparison);
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
     * @return AuthyLogQuery The current query, for fluid interface
     */
    public function filterByCount($count = null, $comparison = null)
    {
        if (is_array($count)) {
            $useMinMax = false;
            if (isset($count['min'])) {
                $this->addUsingAlias(AuthyLogPeer::COUNT, $count['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($count['max'])) {
                $this->addUsingAlias(AuthyLogPeer::COUNT, $count['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyLogPeer::COUNT, $count, $comparison);
    }

    /**
     * Filter the query by a related Authy object
     *
     * @param   Authy|PropelObjectCollection $authy The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyLogQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthy($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(AuthyLogPeer::ID_AUTHY, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(AuthyLogPeer::ID_AUTHY, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return AuthyLogQuery The current query, for fluid interface
     */
    public function joinAuthy($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
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
    public function useAuthyQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAuthy($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Authy', '\App\AuthyQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   AuthyLog $authyLog Object to remove from the list of results
     *
     * @return AuthyLogQuery The current query, for fluid interface
     */
    public function prune($authyLog = null)
    {
        if ($authyLog) {
            $this->addUsingAlias(AuthyLogPeer::ID_AUTHY_LOG, $authyLog->getIdAuthyLog(), Criteria::NOT_EQUAL);
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
                    \ApiGoat\Utility\TableVersion::bump('authy_log');
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
                    \ApiGoat\Utility\TableVersion::bump('authy_log');
                }

        return $this->postUpdate($affectedRows, $con);
    }

}
