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
use App\ApiRbac;
use App\ApiRbacPeer;
use App\ApiRbacQuery;
use App\Authy;
use App\AuthyGroup;

/**
 * Base class that represents a query for the 'api_rbac' table.
 *
 * API ACL
 *
 * @method ApiRbacQuery orderByIdApiRbac($order = Criteria::ASC) Order by the id_api_rbac column
 * @method ApiRbacQuery orderByDateCreation($order = Criteria::ASC) Order by the date_creation column
 * @method ApiRbacQuery orderByDescription($order = Criteria::ASC) Order by the description column
 * @method ApiRbacQuery orderByModel($order = Criteria::ASC) Order by the model column
 * @method ApiRbacQuery orderByAction($order = Criteria::ASC) Order by the action column
 * @method ApiRbacQuery orderByBody($order = Criteria::ASC) Order by the body column
 * @method ApiRbacQuery orderByQuery($order = Criteria::ASC) Order by the query column
 * @method ApiRbacQuery orderByMethod($order = Criteria::ASC) Order by the method column
 * @method ApiRbacQuery orderByScope($order = Criteria::ASC) Order by the scope column
 * @method ApiRbacQuery orderByRule($order = Criteria::ASC) Order by the rule column
 * @method ApiRbacQuery orderByCount($order = Criteria::ASC) Order by the count column
 * @method ApiRbacQuery orderByDateModification($order = Criteria::ASC) Order by the date_modification column
 * @method ApiRbacQuery orderByIdGroupCreation($order = Criteria::ASC) Order by the id_group_creation column
 * @method ApiRbacQuery orderByIdCreation($order = Criteria::ASC) Order by the id_creation column
 * @method ApiRbacQuery orderByIdModification($order = Criteria::ASC) Order by the id_modification column
 *
 * @method ApiRbacQuery groupByIdApiRbac() Group by the id_api_rbac column
 * @method ApiRbacQuery groupByDateCreation() Group by the date_creation column
 * @method ApiRbacQuery groupByDescription() Group by the description column
 * @method ApiRbacQuery groupByModel() Group by the model column
 * @method ApiRbacQuery groupByAction() Group by the action column
 * @method ApiRbacQuery groupByBody() Group by the body column
 * @method ApiRbacQuery groupByQuery() Group by the query column
 * @method ApiRbacQuery groupByMethod() Group by the method column
 * @method ApiRbacQuery groupByScope() Group by the scope column
 * @method ApiRbacQuery groupByRule() Group by the rule column
 * @method ApiRbacQuery groupByCount() Group by the count column
 * @method ApiRbacQuery groupByDateModification() Group by the date_modification column
 * @method ApiRbacQuery groupByIdGroupCreation() Group by the id_group_creation column
 * @method ApiRbacQuery groupByIdCreation() Group by the id_creation column
 * @method ApiRbacQuery groupByIdModification() Group by the id_modification column
 *
 * @method ApiRbacQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method ApiRbacQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method ApiRbacQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method ApiRbacQuery leftJoinAuthyGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroup relation
 * @method ApiRbacQuery rightJoinAuthyGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroup relation
 * @method ApiRbacQuery innerJoinAuthyGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroup relation
 *
 * @method ApiRbacQuery leftJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method ApiRbacQuery rightJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method ApiRbacQuery innerJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdCreation relation
 *
 * @method ApiRbacQuery leftJoinAuthyRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method ApiRbacQuery rightJoinAuthyRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method ApiRbacQuery innerJoinAuthyRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdModification relation
 *
 * @method ApiRbacQuery leftJoinApiLog($relationAlias = null) Adds a LEFT JOIN clause to the query using the ApiLog relation
 * @method ApiRbacQuery rightJoinApiLog($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ApiLog relation
 * @method ApiRbacQuery innerJoinApiLog($relationAlias = null) Adds a INNER JOIN clause to the query using the ApiLog relation
 *
 * @method ApiRbac findOne(?PropelPDO $con = null) Return the first ApiRbac matching the query
 * @method ApiRbac findOneOrCreate(?PropelPDO $con = null) Return the first ApiRbac matching the query, or a new ApiRbac object populated from the query conditions when no match is found
 *
 * @method ApiRbac findOneByDateCreation(string $date_creation) Return the first ApiRbac filtered by the date_creation column
 * @method ApiRbac findOneByDescription(string $description) Return the first ApiRbac filtered by the description column
 * @method ApiRbac findOneByModel(string $model) Return the first ApiRbac filtered by the model column
 * @method ApiRbac findOneByAction(string $action) Return the first ApiRbac filtered by the action column
 * @method ApiRbac findOneByBody(string $body) Return the first ApiRbac filtered by the body column
 * @method ApiRbac findOneByQuery(string $query) Return the first ApiRbac filtered by the query column
 * @method ApiRbac findOneByMethod(int $method) Return the first ApiRbac filtered by the method column
 * @method ApiRbac findOneByScope(int $scope) Return the first ApiRbac filtered by the scope column
 * @method ApiRbac findOneByRule(int $rule) Return the first ApiRbac filtered by the rule column
 * @method ApiRbac findOneByCount(int $count) Return the first ApiRbac filtered by the count column
 * @method ApiRbac findOneByDateModification(string $date_modification) Return the first ApiRbac filtered by the date_modification column
 * @method ApiRbac findOneByIdGroupCreation(int $id_group_creation) Return the first ApiRbac filtered by the id_group_creation column
 * @method ApiRbac findOneByIdCreation(int $id_creation) Return the first ApiRbac filtered by the id_creation column
 * @method ApiRbac findOneByIdModification(int $id_modification) Return the first ApiRbac filtered by the id_modification column
 *
 * @method array findByIdApiRbac(int $id_api_rbac) Return ApiRbac objects filtered by the id_api_rbac column
 * @method array findByDateCreation(string $date_creation) Return ApiRbac objects filtered by the date_creation column
 * @method array findByDescription(string $description) Return ApiRbac objects filtered by the description column
 * @method array findByModel(string $model) Return ApiRbac objects filtered by the model column
 * @method array findByAction(string $action) Return ApiRbac objects filtered by the action column
 * @method array findByBody(string $body) Return ApiRbac objects filtered by the body column
 * @method array findByQuery(string $query) Return ApiRbac objects filtered by the query column
 * @method array findByMethod(int $method) Return ApiRbac objects filtered by the method column
 * @method array findByScope(int $scope) Return ApiRbac objects filtered by the scope column
 * @method array findByRule(int $rule) Return ApiRbac objects filtered by the rule column
 * @method array findByCount(int $count) Return ApiRbac objects filtered by the count column
 * @method array findByDateModification(string $date_modification) Return ApiRbac objects filtered by the date_modification column
 * @method array findByIdGroupCreation(int $id_group_creation) Return ApiRbac objects filtered by the id_group_creation column
 * @method array findByIdCreation(int $id_creation) Return ApiRbac objects filtered by the id_creation column
 * @method array findByIdModification(int $id_modification) Return ApiRbac objects filtered by the id_modification column
 *
 * @package    propel.generator..om
 */
abstract class BaseApiRbacQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseApiRbacQuery object.
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
            $modelName = 'App\\ApiRbac';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ApiRbacQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   ApiRbacQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return ApiRbacQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof ApiRbacQuery) {
            return $criteria;
        }
        $query = new ApiRbacQuery(null, null, $modelAlias);

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
     * @return   ApiRbac|ApiRbac[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = ApiRbacPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(ApiRbacPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 ApiRbac A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdApiRbac($key, $con = null)
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
     * @return                 ApiRbac A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_api_rbac`, `date_creation`, `description`, `model`, `action`, `body`, `query`, `method`, `scope`, `rule`, `count`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification` FROM `api_rbac` WHERE `id_api_rbac` = :p0';
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
            $obj = new ApiRbac();
            $obj->hydrate($row);
            ApiRbacPeer::addInstanceToPool($obj, (string) $key);
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
     * @return ApiRbac|ApiRbac[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|ApiRbac[]|mixed the list of results, formatted by the current formatter
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
     * @return ApiRbacQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(ApiRbacPeer::ID_API_RBAC, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ApiRbacQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(ApiRbacPeer::ID_API_RBAC, $keys, Criteria::IN);
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
     * @param     mixed $idApiRbac The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ApiRbacQuery The current query, for fluid interface
     */
    public function filterByIdApiRbac($idApiRbac = null, $comparison = null)
    {
        if (is_array($idApiRbac)) {
            $useMinMax = false;
            if (isset($idApiRbac['min'])) {
                $this->addUsingAlias(ApiRbacPeer::ID_API_RBAC, $idApiRbac['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idApiRbac['max'])) {
                $this->addUsingAlias(ApiRbacPeer::ID_API_RBAC, $idApiRbac['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ApiRbacPeer::ID_API_RBAC, $idApiRbac, $comparison);
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
     * @return ApiRbacQuery The current query, for fluid interface
     */
    public function filterByDateCreation($dateCreation = null, $comparison = null)
    {
        if (is_array($dateCreation)) {
            $useMinMax = false;
            if (isset($dateCreation['min'])) {
                $this->addUsingAlias(ApiRbacPeer::DATE_CREATION, $dateCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateCreation['max'])) {
                $this->addUsingAlias(ApiRbacPeer::DATE_CREATION, $dateCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ApiRbacPeer::DATE_CREATION, $dateCreation, $comparison);
    }

    /**
     * Filter the query on the description column
     *
     * Example usage:
     * <code>
     * $query->filterByDescription('fooValue');   // WHERE description = 'fooValue'
     * $query->filterByDescription('%fooValue%'); // WHERE description LIKE '%fooValue%'
     * </code>
     *
     * @param     string $description The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ApiRbacQuery The current query, for fluid interface
     */
    public function filterByDescription($description = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($description)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $description)) {
                $description = str_replace('*', '%', $description);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(ApiRbacPeer::DESCRIPTION, $description, $comparison);
    }

    /**
     * Filter the query on the model column
     *
     * Example usage:
     * <code>
     * $query->filterByModel('fooValue');   // WHERE model = 'fooValue'
     * $query->filterByModel('%fooValue%'); // WHERE model LIKE '%fooValue%'
     * </code>
     *
     * @param     string $model The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ApiRbacQuery The current query, for fluid interface
     */
    public function filterByModel($model = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($model)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $model)) {
                $model = str_replace('*', '%', $model);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(ApiRbacPeer::MODEL, $model, $comparison);
    }

    /**
     * Filter the query on the action column
     *
     * Example usage:
     * <code>
     * $query->filterByAction('fooValue');   // WHERE action = 'fooValue'
     * $query->filterByAction('%fooValue%'); // WHERE action LIKE '%fooValue%'
     * </code>
     *
     * @param     string $action The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ApiRbacQuery The current query, for fluid interface
     */
    public function filterByAction($action = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($action)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $action)) {
                $action = str_replace('*', '%', $action);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(ApiRbacPeer::ACTION, $action, $comparison);
    }

    /**
     * Filter the query on the body column
     *
     * Example usage:
     * <code>
     * $query->filterByBody('fooValue');   // WHERE body = 'fooValue'
     * $query->filterByBody('%fooValue%'); // WHERE body LIKE '%fooValue%'
     * </code>
     *
     * @param     string $body The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ApiRbacQuery The current query, for fluid interface
     */
    public function filterByBody($body = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($body)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $body)) {
                $body = str_replace('*', '%', $body);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(ApiRbacPeer::BODY, $body, $comparison);
    }

    /**
     * Filter the query on the query column
     *
     * Example usage:
     * <code>
     * $query->filterByQuery('fooValue');   // WHERE query = 'fooValue'
     * $query->filterByQuery('%fooValue%'); // WHERE query LIKE '%fooValue%'
     * </code>
     *
     * @param     string $query The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ApiRbacQuery The current query, for fluid interface
     */
    public function filterByQuery($query = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($query)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $query)) {
                $query = str_replace('*', '%', $query);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(ApiRbacPeer::QUERY, $query, $comparison);
    }

    /**
     * Filter the query on the method column
     *
     * @param     mixed $method The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ApiRbacQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByMethod($method = null, $comparison = null)
    {
        if (is_scalar($method)) {
            $method = ApiRbacPeer::getSqlValueForEnum(ApiRbacPeer::METHOD, $method);
        } elseif (is_array($method)) {
            $convertedValues = array();
            foreach ($method as $value) {
                $convertedValues[] = ApiRbacPeer::getSqlValueForEnum(ApiRbacPeer::METHOD, $value);
            }
            $method = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ApiRbacPeer::METHOD, $method, $comparison);
    }

    /**
     * Filter the query on the scope column
     *
     * @param     mixed $scope The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ApiRbacQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByScope($scope = null, $comparison = null)
    {
        if (is_scalar($scope)) {
            $scope = ApiRbacPeer::getSqlValueForEnum(ApiRbacPeer::SCOPE, $scope);
        } elseif (is_array($scope)) {
            $convertedValues = array();
            foreach ($scope as $value) {
                $convertedValues[] = ApiRbacPeer::getSqlValueForEnum(ApiRbacPeer::SCOPE, $value);
            }
            $scope = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ApiRbacPeer::SCOPE, $scope, $comparison);
    }

    /**
     * Filter the query on the rule column
     *
     * @param     mixed $rule The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ApiRbacQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByRule($rule = null, $comparison = null)
    {
        if (is_scalar($rule)) {
            $rule = ApiRbacPeer::getSqlValueForEnum(ApiRbacPeer::RULE, $rule);
        } elseif (is_array($rule)) {
            $convertedValues = array();
            foreach ($rule as $value) {
                $convertedValues[] = ApiRbacPeer::getSqlValueForEnum(ApiRbacPeer::RULE, $value);
            }
            $rule = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ApiRbacPeer::RULE, $rule, $comparison);
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
     * @return ApiRbacQuery The current query, for fluid interface
     */
    public function filterByCount($count = null, $comparison = null)
    {
        if (is_array($count)) {
            $useMinMax = false;
            if (isset($count['min'])) {
                $this->addUsingAlias(ApiRbacPeer::COUNT, $count['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($count['max'])) {
                $this->addUsingAlias(ApiRbacPeer::COUNT, $count['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ApiRbacPeer::COUNT, $count, $comparison);
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
     * @return ApiRbacQuery The current query, for fluid interface
     */
    public function filterByDateModification($dateModification = null, $comparison = null)
    {
        if (is_array($dateModification)) {
            $useMinMax = false;
            if (isset($dateModification['min'])) {
                $this->addUsingAlias(ApiRbacPeer::DATE_MODIFICATION, $dateModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateModification['max'])) {
                $this->addUsingAlias(ApiRbacPeer::DATE_MODIFICATION, $dateModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ApiRbacPeer::DATE_MODIFICATION, $dateModification, $comparison);
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
     * @return ApiRbacQuery The current query, for fluid interface
     */
    public function filterByIdGroupCreation($idGroupCreation = null, $comparison = null)
    {
        if (is_array($idGroupCreation)) {
            $useMinMax = false;
            if (isset($idGroupCreation['min'])) {
                $this->addUsingAlias(ApiRbacPeer::ID_GROUP_CREATION, $idGroupCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGroupCreation['max'])) {
                $this->addUsingAlias(ApiRbacPeer::ID_GROUP_CREATION, $idGroupCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ApiRbacPeer::ID_GROUP_CREATION, $idGroupCreation, $comparison);
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
     * @return ApiRbacQuery The current query, for fluid interface
     */
    public function filterByIdCreation($idCreation = null, $comparison = null)
    {
        if (is_array($idCreation)) {
            $useMinMax = false;
            if (isset($idCreation['min'])) {
                $this->addUsingAlias(ApiRbacPeer::ID_CREATION, $idCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idCreation['max'])) {
                $this->addUsingAlias(ApiRbacPeer::ID_CREATION, $idCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ApiRbacPeer::ID_CREATION, $idCreation, $comparison);
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
     * @return ApiRbacQuery The current query, for fluid interface
     */
    public function filterByIdModification($idModification = null, $comparison = null)
    {
        if (is_array($idModification)) {
            $useMinMax = false;
            if (isset($idModification['min'])) {
                $this->addUsingAlias(ApiRbacPeer::ID_MODIFICATION, $idModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idModification['max'])) {
                $this->addUsingAlias(ApiRbacPeer::ID_MODIFICATION, $idModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ApiRbacPeer::ID_MODIFICATION, $idModification, $comparison);
    }

    /**
     * Filter the query by a related AuthyGroup object
     *
     * @param   AuthyGroup|PropelObjectCollection $authyGroup The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 ApiRbacQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroup($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(ApiRbacPeer::ID_GROUP_CREATION, $authyGroup->getIdAuthyGroup(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ApiRbacPeer::ID_GROUP_CREATION, $authyGroup->toKeyValue('PrimaryKey', 'IdAuthyGroup'), $comparison);
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
     * @return ApiRbacQuery The current query, for fluid interface
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
     * @return                 ApiRbacQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdCreation($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(ApiRbacPeer::ID_CREATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ApiRbacPeer::ID_CREATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return ApiRbacQuery The current query, for fluid interface
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
     * @return                 ApiRbacQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdModification($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(ApiRbacPeer::ID_MODIFICATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ApiRbacPeer::ID_MODIFICATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return ApiRbacQuery The current query, for fluid interface
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
     * Filter the query by a related ApiLog object
     *
     * @param   ApiLog|PropelObjectCollection $apiLog  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 ApiRbacQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByApiLog($apiLog, $comparison = null)
    {
        if ($apiLog instanceof ApiLog) {
            return $this
                ->addUsingAlias(ApiRbacPeer::ID_API_RBAC, $apiLog->getIdApiRbac(), $comparison);
        } elseif ($apiLog instanceof PropelObjectCollection) {
            return $this
                ->useApiLogQuery()
                ->filterByPrimaryKeys($apiLog->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByApiLog() only accepts arguments of type ApiLog or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ApiLog relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ApiRbacQuery The current query, for fluid interface
     */
    public function joinApiLog($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ApiLog');

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
            $this->addJoinObject($join, 'ApiLog');
        }

        return $this;
    }

    /**
     * Use the ApiLog relation ApiLog object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\ApiLogQuery A secondary query class using the current class as primary query
     */
    public function useApiLogQuery($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        return $this
            ->joinApiLog($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ApiLog', '\App\ApiLogQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ApiRbac $apiRbac Object to remove from the list of results
     *
     * @return ApiRbacQuery The current query, for fluid interface
     */
    public function prune($apiRbac = null)
    {
        if ($apiRbac) {
            $this->addUsingAlias(ApiRbacPeer::ID_API_RBAC, $apiRbac->getIdApiRbac(), Criteria::NOT_EQUAL);
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
                    \ApiGoat\Utility\TableVersion::bump('api_rbac');
                }
                if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                    \ApiGoat\Utility\TableVersion::bump('api_rbac@rules');
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
                    \ApiGoat\Utility\TableVersion::bump('api_rbac');
                }
                if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                    \ApiGoat\Utility\TableVersion::bump('api_rbac@rules');
                }

        return $this->postUpdate($affectedRows, $con);
    }

    // add_tablestamp behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     ApiRbacQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7){
        return $this->addUsingAlias(ApiRbacPeer::DATE_MODIFICATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     ApiRbacQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst(){
        return $this->addDescendingOrderByColumn(ApiRbacPeer::DATE_MODIFICATION);
    }

    /**
     * Order by update date asc
     *
     * @return     ApiRbacQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst(){
        return $this->addAscendingOrderByColumn(ApiRbacPeer::DATE_MODIFICATION);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     ApiRbacQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7){
        return $this->addUsingAlias(ApiRbacPeer::DATE_CREATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return     ApiRbacQuery The current query, for fluid interface
     */
    public function lastCreatedFirst(){
        return $this->addDescendingOrderByColumn(ApiRbacPeer::DATE_CREATION);
    }

    /**
     * Order by create date asc
     *
     * @return     ApiRbacQuery The current query, for fluid interface
     */
    public function firstCreatedFirst(){
        return $this->addAscendingOrderByColumn(ApiRbacPeer::DATE_CREATION);
    }
}
