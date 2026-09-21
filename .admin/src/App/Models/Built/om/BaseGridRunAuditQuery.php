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
use App\GridRunAudit;
use App\GridRunAuditPeer;
use App\GridRunAuditQuery;

/**
 * Base class that represents a query for the 'grid_run_audit' table.
 *
 * Change history
 *
 * @method GridRunAuditQuery orderByIdGridRunAudit($order = Criteria::ASC) Order by the id_grid_run_audit column
 * @method GridRunAuditQuery orderByIdGridRun($order = Criteria::ASC) Order by the id_grid_run column
 * @method GridRunAuditQuery orderByField($order = Criteria::ASC) Order by the field column
 * @method GridRunAuditQuery orderByValueFrom($order = Criteria::ASC) Order by the value_from column
 * @method GridRunAuditQuery orderByValueTo($order = Criteria::ASC) Order by the value_to column
 * @method GridRunAuditQuery orderByActor($order = Criteria::ASC) Order by the actor column
 * @method GridRunAuditQuery orderBySource($order = Criteria::ASC) Order by the source column
 * @method GridRunAuditQuery orderByDateCreation($order = Criteria::ASC) Order by the date_creation column
 * @method GridRunAuditQuery orderByDateModification($order = Criteria::ASC) Order by the date_modification column
 * @method GridRunAuditQuery orderByIdGroupCreation($order = Criteria::ASC) Order by the id_group_creation column
 * @method GridRunAuditQuery orderByIdCreation($order = Criteria::ASC) Order by the id_creation column
 * @method GridRunAuditQuery orderByIdModification($order = Criteria::ASC) Order by the id_modification column
 *
 * @method GridRunAuditQuery groupByIdGridRunAudit() Group by the id_grid_run_audit column
 * @method GridRunAuditQuery groupByIdGridRun() Group by the id_grid_run column
 * @method GridRunAuditQuery groupByField() Group by the field column
 * @method GridRunAuditQuery groupByValueFrom() Group by the value_from column
 * @method GridRunAuditQuery groupByValueTo() Group by the value_to column
 * @method GridRunAuditQuery groupByActor() Group by the actor column
 * @method GridRunAuditQuery groupBySource() Group by the source column
 * @method GridRunAuditQuery groupByDateCreation() Group by the date_creation column
 * @method GridRunAuditQuery groupByDateModification() Group by the date_modification column
 * @method GridRunAuditQuery groupByIdGroupCreation() Group by the id_group_creation column
 * @method GridRunAuditQuery groupByIdCreation() Group by the id_creation column
 * @method GridRunAuditQuery groupByIdModification() Group by the id_modification column
 *
 * @method GridRunAuditQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method GridRunAuditQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method GridRunAuditQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method GridRunAuditQuery leftJoinGridRun($relationAlias = null) Adds a LEFT JOIN clause to the query using the GridRun relation
 * @method GridRunAuditQuery rightJoinGridRun($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GridRun relation
 * @method GridRunAuditQuery innerJoinGridRun($relationAlias = null) Adds a INNER JOIN clause to the query using the GridRun relation
 *
 * @method GridRunAuditQuery leftJoinAuthyGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroup relation
 * @method GridRunAuditQuery rightJoinAuthyGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroup relation
 * @method GridRunAuditQuery innerJoinAuthyGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroup relation
 *
 * @method GridRunAuditQuery leftJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method GridRunAuditQuery rightJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method GridRunAuditQuery innerJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdCreation relation
 *
 * @method GridRunAuditQuery leftJoinAuthyRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method GridRunAuditQuery rightJoinAuthyRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method GridRunAuditQuery innerJoinAuthyRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdModification relation
 *
 * @method GridRunAudit findOne(?PropelPDO $con = null) Return the first GridRunAudit matching the query
 * @method GridRunAudit findOneOrCreate(?PropelPDO $con = null) Return the first GridRunAudit matching the query, or a new GridRunAudit object populated from the query conditions when no match is found
 *
 * @method GridRunAudit findOneByIdGridRun(int $id_grid_run) Return the first GridRunAudit filtered by the id_grid_run column
 * @method GridRunAudit findOneByField(string $field) Return the first GridRunAudit filtered by the field column
 * @method GridRunAudit findOneByValueFrom(string $value_from) Return the first GridRunAudit filtered by the value_from column
 * @method GridRunAudit findOneByValueTo(string $value_to) Return the first GridRunAudit filtered by the value_to column
 * @method GridRunAudit findOneByActor(string $actor) Return the first GridRunAudit filtered by the actor column
 * @method GridRunAudit findOneBySource(int $source) Return the first GridRunAudit filtered by the source column
 * @method GridRunAudit findOneByDateCreation(string $date_creation) Return the first GridRunAudit filtered by the date_creation column
 * @method GridRunAudit findOneByDateModification(string $date_modification) Return the first GridRunAudit filtered by the date_modification column
 * @method GridRunAudit findOneByIdGroupCreation(int $id_group_creation) Return the first GridRunAudit filtered by the id_group_creation column
 * @method GridRunAudit findOneByIdCreation(int $id_creation) Return the first GridRunAudit filtered by the id_creation column
 * @method GridRunAudit findOneByIdModification(int $id_modification) Return the first GridRunAudit filtered by the id_modification column
 *
 * @method array findByIdGridRunAudit(int $id_grid_run_audit) Return GridRunAudit objects filtered by the id_grid_run_audit column
 * @method array findByIdGridRun(int $id_grid_run) Return GridRunAudit objects filtered by the id_grid_run column
 * @method array findByField(string $field) Return GridRunAudit objects filtered by the field column
 * @method array findByValueFrom(string $value_from) Return GridRunAudit objects filtered by the value_from column
 * @method array findByValueTo(string $value_to) Return GridRunAudit objects filtered by the value_to column
 * @method array findByActor(string $actor) Return GridRunAudit objects filtered by the actor column
 * @method array findBySource(int $source) Return GridRunAudit objects filtered by the source column
 * @method array findByDateCreation(string $date_creation) Return GridRunAudit objects filtered by the date_creation column
 * @method array findByDateModification(string $date_modification) Return GridRunAudit objects filtered by the date_modification column
 * @method array findByIdGroupCreation(int $id_group_creation) Return GridRunAudit objects filtered by the id_group_creation column
 * @method array findByIdCreation(int $id_creation) Return GridRunAudit objects filtered by the id_creation column
 * @method array findByIdModification(int $id_modification) Return GridRunAudit objects filtered by the id_modification column
 *
 * @package    propel.generator..om
 */
abstract class BaseGridRunAuditQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseGridRunAuditQuery object.
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
            $modelName = 'App\\GridRunAudit';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new GridRunAuditQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   GridRunAuditQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return GridRunAuditQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof GridRunAuditQuery) {
            return $criteria;
        }
        $query = new GridRunAuditQuery(null, null, $modelAlias);

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
     * @return   GridRunAudit|GridRunAudit[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = GridRunAuditPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(GridRunAuditPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 GridRunAudit A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdGridRunAudit($key, $con = null)
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
     * @return                 GridRunAudit A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_grid_run_audit`, `id_grid_run`, `field`, `value_from`, `value_to`, `actor`, `source`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification` FROM `grid_run_audit` WHERE `id_grid_run_audit` = :p0';
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
            $obj = new GridRunAudit();
            $obj->hydrate($row);
            GridRunAuditPeer::addInstanceToPool($obj, (string) $key);
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
     * @return GridRunAudit|GridRunAudit[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|GridRunAudit[]|mixed the list of results, formatted by the current formatter
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
     * @return GridRunAuditQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(GridRunAuditPeer::ID_GRID_RUN_AUDIT, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return GridRunAuditQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(GridRunAuditPeer::ID_GRID_RUN_AUDIT, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id_grid_run_audit column
     *
     * Example usage:
     * <code>
     * $query->filterByIdGridRunAudit(1234); // WHERE id_grid_run_audit = 1234
     * $query->filterByIdGridRunAudit(array(12, 34)); // WHERE id_grid_run_audit IN (12, 34)
     * $query->filterByIdGridRunAudit(array('min' => 12)); // WHERE id_grid_run_audit >= 12
     * $query->filterByIdGridRunAudit(array('max' => 12)); // WHERE id_grid_run_audit <= 12
     * </code>
     *
     * @param     mixed $idGridRunAudit The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunAuditQuery The current query, for fluid interface
     */
    public function filterByIdGridRunAudit($idGridRunAudit = null, $comparison = null)
    {
        if (is_array($idGridRunAudit)) {
            $useMinMax = false;
            if (isset($idGridRunAudit['min'])) {
                $this->addUsingAlias(GridRunAuditPeer::ID_GRID_RUN_AUDIT, $idGridRunAudit['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGridRunAudit['max'])) {
                $this->addUsingAlias(GridRunAuditPeer::ID_GRID_RUN_AUDIT, $idGridRunAudit['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunAuditPeer::ID_GRID_RUN_AUDIT, $idGridRunAudit, $comparison);
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
     * @return GridRunAuditQuery The current query, for fluid interface
     */
    public function filterByIdGridRun($idGridRun = null, $comparison = null)
    {
        if (is_array($idGridRun)) {
            $useMinMax = false;
            if (isset($idGridRun['min'])) {
                $this->addUsingAlias(GridRunAuditPeer::ID_GRID_RUN, $idGridRun['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGridRun['max'])) {
                $this->addUsingAlias(GridRunAuditPeer::ID_GRID_RUN, $idGridRun['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunAuditPeer::ID_GRID_RUN, $idGridRun, $comparison);
    }

    /**
     * Filter the query on the field column
     *
     * Example usage:
     * <code>
     * $query->filterByField('fooValue');   // WHERE field = 'fooValue'
     * $query->filterByField('%fooValue%'); // WHERE field LIKE '%fooValue%'
     * </code>
     *
     * @param     string $field The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunAuditQuery The current query, for fluid interface
     */
    public function filterByField($field = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($field)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $field)) {
                $field = str_replace('*', '%', $field);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GridRunAuditPeer::FIELD, $field, $comparison);
    }

    /**
     * Filter the query on the value_from column
     *
     * Example usage:
     * <code>
     * $query->filterByValueFrom('fooValue');   // WHERE value_from = 'fooValue'
     * $query->filterByValueFrom('%fooValue%'); // WHERE value_from LIKE '%fooValue%'
     * </code>
     *
     * @param     string $valueFrom The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunAuditQuery The current query, for fluid interface
     */
    public function filterByValueFrom($valueFrom = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($valueFrom)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $valueFrom)) {
                $valueFrom = str_replace('*', '%', $valueFrom);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GridRunAuditPeer::VALUE_FROM, $valueFrom, $comparison);
    }

    /**
     * Filter the query on the value_to column
     *
     * Example usage:
     * <code>
     * $query->filterByValueTo('fooValue');   // WHERE value_to = 'fooValue'
     * $query->filterByValueTo('%fooValue%'); // WHERE value_to LIKE '%fooValue%'
     * </code>
     *
     * @param     string $valueTo The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunAuditQuery The current query, for fluid interface
     */
    public function filterByValueTo($valueTo = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($valueTo)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $valueTo)) {
                $valueTo = str_replace('*', '%', $valueTo);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GridRunAuditPeer::VALUE_TO, $valueTo, $comparison);
    }

    /**
     * Filter the query on the actor column
     *
     * Example usage:
     * <code>
     * $query->filterByActor('fooValue');   // WHERE actor = 'fooValue'
     * $query->filterByActor('%fooValue%'); // WHERE actor LIKE '%fooValue%'
     * </code>
     *
     * @param     string $actor The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunAuditQuery The current query, for fluid interface
     */
    public function filterByActor($actor = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($actor)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $actor)) {
                $actor = str_replace('*', '%', $actor);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GridRunAuditPeer::ACTOR, $actor, $comparison);
    }

    /**
     * Filter the query on the source column
     *
     * @param     mixed $source The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunAuditQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterBySource($source = null, $comparison = null)
    {
        if (is_scalar($source)) {
            $source = GridRunAuditPeer::getSqlValueForEnum(GridRunAuditPeer::SOURCE, $source);
        } elseif (is_array($source)) {
            $convertedValues = array();
            foreach ($source as $value) {
                $convertedValues[] = GridRunAuditPeer::getSqlValueForEnum(GridRunAuditPeer::SOURCE, $value);
            }
            $source = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunAuditPeer::SOURCE, $source, $comparison);
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
     * @return GridRunAuditQuery The current query, for fluid interface
     */
    public function filterByDateCreation($dateCreation = null, $comparison = null)
    {
        if (is_array($dateCreation)) {
            $useMinMax = false;
            if (isset($dateCreation['min'])) {
                $this->addUsingAlias(GridRunAuditPeer::DATE_CREATION, $dateCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateCreation['max'])) {
                $this->addUsingAlias(GridRunAuditPeer::DATE_CREATION, $dateCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunAuditPeer::DATE_CREATION, $dateCreation, $comparison);
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
     * @return GridRunAuditQuery The current query, for fluid interface
     */
    public function filterByDateModification($dateModification = null, $comparison = null)
    {
        if (is_array($dateModification)) {
            $useMinMax = false;
            if (isset($dateModification['min'])) {
                $this->addUsingAlias(GridRunAuditPeer::DATE_MODIFICATION, $dateModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateModification['max'])) {
                $this->addUsingAlias(GridRunAuditPeer::DATE_MODIFICATION, $dateModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunAuditPeer::DATE_MODIFICATION, $dateModification, $comparison);
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
     * @return GridRunAuditQuery The current query, for fluid interface
     */
    public function filterByIdGroupCreation($idGroupCreation = null, $comparison = null)
    {
        if (is_array($idGroupCreation)) {
            $useMinMax = false;
            if (isset($idGroupCreation['min'])) {
                $this->addUsingAlias(GridRunAuditPeer::ID_GROUP_CREATION, $idGroupCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGroupCreation['max'])) {
                $this->addUsingAlias(GridRunAuditPeer::ID_GROUP_CREATION, $idGroupCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunAuditPeer::ID_GROUP_CREATION, $idGroupCreation, $comparison);
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
     * @return GridRunAuditQuery The current query, for fluid interface
     */
    public function filterByIdCreation($idCreation = null, $comparison = null)
    {
        if (is_array($idCreation)) {
            $useMinMax = false;
            if (isset($idCreation['min'])) {
                $this->addUsingAlias(GridRunAuditPeer::ID_CREATION, $idCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idCreation['max'])) {
                $this->addUsingAlias(GridRunAuditPeer::ID_CREATION, $idCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunAuditPeer::ID_CREATION, $idCreation, $comparison);
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
     * @return GridRunAuditQuery The current query, for fluid interface
     */
    public function filterByIdModification($idModification = null, $comparison = null)
    {
        if (is_array($idModification)) {
            $useMinMax = false;
            if (isset($idModification['min'])) {
                $this->addUsingAlias(GridRunAuditPeer::ID_MODIFICATION, $idModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idModification['max'])) {
                $this->addUsingAlias(GridRunAuditPeer::ID_MODIFICATION, $idModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunAuditPeer::ID_MODIFICATION, $idModification, $comparison);
    }

    /**
     * Filter the query by a related GridRun object
     *
     * @param   GridRun|PropelObjectCollection $gridRun The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 GridRunAuditQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByGridRun($gridRun, $comparison = null)
    {
        if ($gridRun instanceof GridRun) {
            return $this
                ->addUsingAlias(GridRunAuditPeer::ID_GRID_RUN, $gridRun->getIdGridRun(), $comparison);
        } elseif ($gridRun instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(GridRunAuditPeer::ID_GRID_RUN, $gridRun->toKeyValue('PrimaryKey', 'IdGridRun'), $comparison);
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
     * @return GridRunAuditQuery The current query, for fluid interface
     */
    public function joinGridRun($relationAlias = null, $joinType = 'LEFT JOIN')
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
    public function useGridRunQuery($relationAlias = null, $joinType = 'LEFT JOIN')
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
     * @return                 GridRunAuditQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroup($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(GridRunAuditPeer::ID_GROUP_CREATION, $authyGroup->getIdAuthyGroup(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(GridRunAuditPeer::ID_GROUP_CREATION, $authyGroup->toKeyValue('PrimaryKey', 'IdAuthyGroup'), $comparison);
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
     * @return GridRunAuditQuery The current query, for fluid interface
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
     * @return                 GridRunAuditQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdCreation($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(GridRunAuditPeer::ID_CREATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(GridRunAuditPeer::ID_CREATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return GridRunAuditQuery The current query, for fluid interface
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
     * @return                 GridRunAuditQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdModification($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(GridRunAuditPeer::ID_MODIFICATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(GridRunAuditPeer::ID_MODIFICATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return GridRunAuditQuery The current query, for fluid interface
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
     * @param   GridRunAudit $gridRunAudit Object to remove from the list of results
     *
     * @return GridRunAuditQuery The current query, for fluid interface
     */
    public function prune($gridRunAudit = null)
    {
        if ($gridRunAudit) {
            $this->addUsingAlias(GridRunAuditPeer::ID_GRID_RUN_AUDIT, $gridRunAudit->getIdGridRunAudit(), Criteria::NOT_EQUAL);
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
                    \ApiGoat\Utility\TableVersion::bump('grid_run_audit');
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
                    \ApiGoat\Utility\TableVersion::bump('grid_run_audit');
                }

        return $this->postUpdate($affectedRows, $con);
    }

    // add_tablestamp behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     GridRunAuditQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7){
        return $this->addUsingAlias(GridRunAuditPeer::DATE_MODIFICATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     GridRunAuditQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst(){
        return $this->addDescendingOrderByColumn(GridRunAuditPeer::DATE_MODIFICATION);
    }

    /**
     * Order by update date asc
     *
     * @return     GridRunAuditQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst(){
        return $this->addAscendingOrderByColumn(GridRunAuditPeer::DATE_MODIFICATION);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     GridRunAuditQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7){
        return $this->addUsingAlias(GridRunAuditPeer::DATE_CREATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return     GridRunAuditQuery The current query, for fluid interface
     */
    public function lastCreatedFirst(){
        return $this->addDescendingOrderByColumn(GridRunAuditPeer::DATE_CREATION);
    }

    /**
     * Order by create date asc
     *
     * @return     GridRunAuditQuery The current query, for fluid interface
     */
    public function firstCreatedFirst(){
        return $this->addAscendingOrderByColumn(GridRunAuditPeer::DATE_CREATION);
    }
}
