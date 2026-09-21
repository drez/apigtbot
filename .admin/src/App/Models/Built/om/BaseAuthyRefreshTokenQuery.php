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
use App\AuthyRefreshToken;
use App\AuthyRefreshTokenPeer;
use App\AuthyRefreshTokenQuery;

/**
 * Base class that represents a query for the 'authy_refresh_token' table.
 *
 *
 *
 * @method AuthyRefreshTokenQuery orderByIdAuthyRefreshToken($order = Criteria::ASC) Order by the id_authy_refresh_token column
 * @method AuthyRefreshTokenQuery orderByIdAuthy($order = Criteria::ASC) Order by the id_authy column
 * @method AuthyRefreshTokenQuery orderByFamilyId($order = Criteria::ASC) Order by the family_id column
 * @method AuthyRefreshTokenQuery orderByTokenHash($order = Criteria::ASC) Order by the token_hash column
 * @method AuthyRefreshTokenQuery orderByExpires($order = Criteria::ASC) Order by the expires column
 * @method AuthyRefreshTokenQuery orderByFamilyExpires($order = Criteria::ASC) Order by the family_expires column
 * @method AuthyRefreshTokenQuery orderByRevoked($order = Criteria::ASC) Order by the revoked column
 * @method AuthyRefreshTokenQuery orderByCreatedAt($order = Criteria::ASC) Order by the created_at column
 * @method AuthyRefreshTokenQuery orderByLastUsedAt($order = Criteria::ASC) Order by the last_used_at column
 * @method AuthyRefreshTokenQuery orderByDateCreation($order = Criteria::ASC) Order by the date_creation column
 * @method AuthyRefreshTokenQuery orderByDateModification($order = Criteria::ASC) Order by the date_modification column
 * @method AuthyRefreshTokenQuery orderByIdGroupCreation($order = Criteria::ASC) Order by the id_group_creation column
 * @method AuthyRefreshTokenQuery orderByIdCreation($order = Criteria::ASC) Order by the id_creation column
 * @method AuthyRefreshTokenQuery orderByIdModification($order = Criteria::ASC) Order by the id_modification column
 *
 * @method AuthyRefreshTokenQuery groupByIdAuthyRefreshToken() Group by the id_authy_refresh_token column
 * @method AuthyRefreshTokenQuery groupByIdAuthy() Group by the id_authy column
 * @method AuthyRefreshTokenQuery groupByFamilyId() Group by the family_id column
 * @method AuthyRefreshTokenQuery groupByTokenHash() Group by the token_hash column
 * @method AuthyRefreshTokenQuery groupByExpires() Group by the expires column
 * @method AuthyRefreshTokenQuery groupByFamilyExpires() Group by the family_expires column
 * @method AuthyRefreshTokenQuery groupByRevoked() Group by the revoked column
 * @method AuthyRefreshTokenQuery groupByCreatedAt() Group by the created_at column
 * @method AuthyRefreshTokenQuery groupByLastUsedAt() Group by the last_used_at column
 * @method AuthyRefreshTokenQuery groupByDateCreation() Group by the date_creation column
 * @method AuthyRefreshTokenQuery groupByDateModification() Group by the date_modification column
 * @method AuthyRefreshTokenQuery groupByIdGroupCreation() Group by the id_group_creation column
 * @method AuthyRefreshTokenQuery groupByIdCreation() Group by the id_creation column
 * @method AuthyRefreshTokenQuery groupByIdModification() Group by the id_modification column
 *
 * @method AuthyRefreshTokenQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method AuthyRefreshTokenQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method AuthyRefreshTokenQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method AuthyRefreshTokenQuery leftJoinAuthyRelatedByIdAuthy($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdAuthy relation
 * @method AuthyRefreshTokenQuery rightJoinAuthyRelatedByIdAuthy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdAuthy relation
 * @method AuthyRefreshTokenQuery innerJoinAuthyRelatedByIdAuthy($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdAuthy relation
 *
 * @method AuthyRefreshTokenQuery leftJoinAuthyGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroup relation
 * @method AuthyRefreshTokenQuery rightJoinAuthyGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroup relation
 * @method AuthyRefreshTokenQuery innerJoinAuthyGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroup relation
 *
 * @method AuthyRefreshTokenQuery leftJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method AuthyRefreshTokenQuery rightJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method AuthyRefreshTokenQuery innerJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdCreation relation
 *
 * @method AuthyRefreshTokenQuery leftJoinAuthyRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method AuthyRefreshTokenQuery rightJoinAuthyRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method AuthyRefreshTokenQuery innerJoinAuthyRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdModification relation
 *
 * @method AuthyRefreshToken findOne(?PropelPDO $con = null) Return the first AuthyRefreshToken matching the query
 * @method AuthyRefreshToken findOneOrCreate(?PropelPDO $con = null) Return the first AuthyRefreshToken matching the query, or a new AuthyRefreshToken object populated from the query conditions when no match is found
 *
 * @method AuthyRefreshToken findOneByIdAuthy(int $id_authy) Return the first AuthyRefreshToken filtered by the id_authy column
 * @method AuthyRefreshToken findOneByFamilyId(string $family_id) Return the first AuthyRefreshToken filtered by the family_id column
 * @method AuthyRefreshToken findOneByTokenHash(string $token_hash) Return the first AuthyRefreshToken filtered by the token_hash column
 * @method AuthyRefreshToken findOneByExpires(int $expires) Return the first AuthyRefreshToken filtered by the expires column
 * @method AuthyRefreshToken findOneByFamilyExpires(int $family_expires) Return the first AuthyRefreshToken filtered by the family_expires column
 * @method AuthyRefreshToken findOneByRevoked(int $revoked) Return the first AuthyRefreshToken filtered by the revoked column
 * @method AuthyRefreshToken findOneByCreatedAt(string $created_at) Return the first AuthyRefreshToken filtered by the created_at column
 * @method AuthyRefreshToken findOneByLastUsedAt(string $last_used_at) Return the first AuthyRefreshToken filtered by the last_used_at column
 * @method AuthyRefreshToken findOneByDateCreation(string $date_creation) Return the first AuthyRefreshToken filtered by the date_creation column
 * @method AuthyRefreshToken findOneByDateModification(string $date_modification) Return the first AuthyRefreshToken filtered by the date_modification column
 * @method AuthyRefreshToken findOneByIdGroupCreation(int $id_group_creation) Return the first AuthyRefreshToken filtered by the id_group_creation column
 * @method AuthyRefreshToken findOneByIdCreation(int $id_creation) Return the first AuthyRefreshToken filtered by the id_creation column
 * @method AuthyRefreshToken findOneByIdModification(int $id_modification) Return the first AuthyRefreshToken filtered by the id_modification column
 *
 * @method array findByIdAuthyRefreshToken(int $id_authy_refresh_token) Return AuthyRefreshToken objects filtered by the id_authy_refresh_token column
 * @method array findByIdAuthy(int $id_authy) Return AuthyRefreshToken objects filtered by the id_authy column
 * @method array findByFamilyId(string $family_id) Return AuthyRefreshToken objects filtered by the family_id column
 * @method array findByTokenHash(string $token_hash) Return AuthyRefreshToken objects filtered by the token_hash column
 * @method array findByExpires(int $expires) Return AuthyRefreshToken objects filtered by the expires column
 * @method array findByFamilyExpires(int $family_expires) Return AuthyRefreshToken objects filtered by the family_expires column
 * @method array findByRevoked(int $revoked) Return AuthyRefreshToken objects filtered by the revoked column
 * @method array findByCreatedAt(string $created_at) Return AuthyRefreshToken objects filtered by the created_at column
 * @method array findByLastUsedAt(string $last_used_at) Return AuthyRefreshToken objects filtered by the last_used_at column
 * @method array findByDateCreation(string $date_creation) Return AuthyRefreshToken objects filtered by the date_creation column
 * @method array findByDateModification(string $date_modification) Return AuthyRefreshToken objects filtered by the date_modification column
 * @method array findByIdGroupCreation(int $id_group_creation) Return AuthyRefreshToken objects filtered by the id_group_creation column
 * @method array findByIdCreation(int $id_creation) Return AuthyRefreshToken objects filtered by the id_creation column
 * @method array findByIdModification(int $id_modification) Return AuthyRefreshToken objects filtered by the id_modification column
 *
 * @package    propel.generator..om
 */
abstract class BaseAuthyRefreshTokenQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseAuthyRefreshTokenQuery object.
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
            $modelName = 'App\\AuthyRefreshToken';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new AuthyRefreshTokenQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   AuthyRefreshTokenQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return AuthyRefreshTokenQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof AuthyRefreshTokenQuery) {
            return $criteria;
        }
        $query = new AuthyRefreshTokenQuery(null, null, $modelAlias);

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
     * @return   AuthyRefreshToken|AuthyRefreshToken[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = AuthyRefreshTokenPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(AuthyRefreshTokenPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 AuthyRefreshToken A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdAuthyRefreshToken($key, $con = null)
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
     * @return                 AuthyRefreshToken A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_authy_refresh_token`, `id_authy`, `family_id`, `token_hash`, `expires`, `family_expires`, `revoked`, `created_at`, `last_used_at`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification` FROM `authy_refresh_token` WHERE `id_authy_refresh_token` = :p0';
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
            $obj = new AuthyRefreshToken();
            $obj->hydrate($row);
            AuthyRefreshTokenPeer::addInstanceToPool($obj, (string) $key);
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
     * @return AuthyRefreshToken|AuthyRefreshToken[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|AuthyRefreshToken[]|mixed the list of results, formatted by the current formatter
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
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(AuthyRefreshTokenPeer::ID_AUTHY_REFRESH_TOKEN, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(AuthyRefreshTokenPeer::ID_AUTHY_REFRESH_TOKEN, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id_authy_refresh_token column
     *
     * Example usage:
     * <code>
     * $query->filterByIdAuthyRefreshToken(1234); // WHERE id_authy_refresh_token = 1234
     * $query->filterByIdAuthyRefreshToken(array(12, 34)); // WHERE id_authy_refresh_token IN (12, 34)
     * $query->filterByIdAuthyRefreshToken(array('min' => 12)); // WHERE id_authy_refresh_token >= 12
     * $query->filterByIdAuthyRefreshToken(array('max' => 12)); // WHERE id_authy_refresh_token <= 12
     * </code>
     *
     * @param     mixed $idAuthyRefreshToken The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByIdAuthyRefreshToken($idAuthyRefreshToken = null, $comparison = null)
    {
        if (is_array($idAuthyRefreshToken)) {
            $useMinMax = false;
            if (isset($idAuthyRefreshToken['min'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::ID_AUTHY_REFRESH_TOKEN, $idAuthyRefreshToken['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idAuthyRefreshToken['max'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::ID_AUTHY_REFRESH_TOKEN, $idAuthyRefreshToken['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyRefreshTokenPeer::ID_AUTHY_REFRESH_TOKEN, $idAuthyRefreshToken, $comparison);
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
     * @see       filterByAuthyRelatedByIdAuthy()
     *
     * @param     mixed $idAuthy The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByIdAuthy($idAuthy = null, $comparison = null)
    {
        if (is_array($idAuthy)) {
            $useMinMax = false;
            if (isset($idAuthy['min'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::ID_AUTHY, $idAuthy['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idAuthy['max'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::ID_AUTHY, $idAuthy['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyRefreshTokenPeer::ID_AUTHY, $idAuthy, $comparison);
    }

    /**
     * Filter the query on the family_id column
     *
     * Example usage:
     * <code>
     * $query->filterByFamilyId('fooValue');   // WHERE family_id = 'fooValue'
     * $query->filterByFamilyId('%fooValue%'); // WHERE family_id LIKE '%fooValue%'
     * </code>
     *
     * @param     string $familyId The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByFamilyId($familyId = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($familyId)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $familyId)) {
                $familyId = str_replace('*', '%', $familyId);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyRefreshTokenPeer::FAMILY_ID, $familyId, $comparison);
    }

    /**
     * Filter the query on the token_hash column
     *
     * Example usage:
     * <code>
     * $query->filterByTokenHash('fooValue');   // WHERE token_hash = 'fooValue'
     * $query->filterByTokenHash('%fooValue%'); // WHERE token_hash LIKE '%fooValue%'
     * </code>
     *
     * @param     string $tokenHash The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByTokenHash($tokenHash = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($tokenHash)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $tokenHash)) {
                $tokenHash = str_replace('*', '%', $tokenHash);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyRefreshTokenPeer::TOKEN_HASH, $tokenHash, $comparison);
    }

    /**
     * Filter the query on the expires column
     *
     * Example usage:
     * <code>
     * $query->filterByExpires(1234); // WHERE expires = 1234
     * $query->filterByExpires(array(12, 34)); // WHERE expires IN (12, 34)
     * $query->filterByExpires(array('min' => 12)); // WHERE expires >= 12
     * $query->filterByExpires(array('max' => 12)); // WHERE expires <= 12
     * </code>
     *
     * @param     mixed $expires The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByExpires($expires = null, $comparison = null)
    {
        if (is_array($expires)) {
            $useMinMax = false;
            if (isset($expires['min'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::EXPIRES, $expires['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($expires['max'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::EXPIRES, $expires['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyRefreshTokenPeer::EXPIRES, $expires, $comparison);
    }

    /**
     * Filter the query on the family_expires column
     *
     * Example usage:
     * <code>
     * $query->filterByFamilyExpires(1234); // WHERE family_expires = 1234
     * $query->filterByFamilyExpires(array(12, 34)); // WHERE family_expires IN (12, 34)
     * $query->filterByFamilyExpires(array('min' => 12)); // WHERE family_expires >= 12
     * $query->filterByFamilyExpires(array('max' => 12)); // WHERE family_expires <= 12
     * </code>
     *
     * @param     mixed $familyExpires The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByFamilyExpires($familyExpires = null, $comparison = null)
    {
        if (is_array($familyExpires)) {
            $useMinMax = false;
            if (isset($familyExpires['min'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::FAMILY_EXPIRES, $familyExpires['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($familyExpires['max'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::FAMILY_EXPIRES, $familyExpires['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyRefreshTokenPeer::FAMILY_EXPIRES, $familyExpires, $comparison);
    }

    /**
     * Filter the query on the revoked column
     *
     * @param     mixed $revoked The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByRevoked($revoked = null, $comparison = null)
    {
        if (is_scalar($revoked)) {
            $revoked = AuthyRefreshTokenPeer::getSqlValueForEnum(AuthyRefreshTokenPeer::REVOKED, $revoked);
        } elseif (is_array($revoked)) {
            $convertedValues = array();
            foreach ($revoked as $value) {
                $convertedValues[] = AuthyRefreshTokenPeer::getSqlValueForEnum(AuthyRefreshTokenPeer::REVOKED, $value);
            }
            $revoked = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyRefreshTokenPeer::REVOKED, $revoked, $comparison);
    }

    /**
     * Filter the query on the created_at column
     *
     * Example usage:
     * <code>
     * $query->filterByCreatedAt('2011-03-14'); // WHERE created_at = '2011-03-14'
     * $query->filterByCreatedAt('now'); // WHERE created_at = '2011-03-14'
     * $query->filterByCreatedAt(array('max' => 'yesterday')); // WHERE created_at < '2011-03-13'
     * </code>
     *
     * @param     mixed $createdAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByCreatedAt($createdAt = null, $comparison = null)
    {
        if (is_array($createdAt)) {
            $useMinMax = false;
            if (isset($createdAt['min'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::CREATED_AT, $createdAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdAt['max'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::CREATED_AT, $createdAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyRefreshTokenPeer::CREATED_AT, $createdAt, $comparison);
    }

    /**
     * Filter the query on the last_used_at column
     *
     * Example usage:
     * <code>
     * $query->filterByLastUsedAt('2011-03-14'); // WHERE last_used_at = '2011-03-14'
     * $query->filterByLastUsedAt('now'); // WHERE last_used_at = '2011-03-14'
     * $query->filterByLastUsedAt(array('max' => 'yesterday')); // WHERE last_used_at < '2011-03-13'
     * </code>
     *
     * @param     mixed $lastUsedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByLastUsedAt($lastUsedAt = null, $comparison = null)
    {
        if (is_array($lastUsedAt)) {
            $useMinMax = false;
            if (isset($lastUsedAt['min'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::LAST_USED_AT, $lastUsedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($lastUsedAt['max'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::LAST_USED_AT, $lastUsedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyRefreshTokenPeer::LAST_USED_AT, $lastUsedAt, $comparison);
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
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByDateCreation($dateCreation = null, $comparison = null)
    {
        if (is_array($dateCreation)) {
            $useMinMax = false;
            if (isset($dateCreation['min'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::DATE_CREATION, $dateCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateCreation['max'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::DATE_CREATION, $dateCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyRefreshTokenPeer::DATE_CREATION, $dateCreation, $comparison);
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
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByDateModification($dateModification = null, $comparison = null)
    {
        if (is_array($dateModification)) {
            $useMinMax = false;
            if (isset($dateModification['min'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::DATE_MODIFICATION, $dateModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateModification['max'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::DATE_MODIFICATION, $dateModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyRefreshTokenPeer::DATE_MODIFICATION, $dateModification, $comparison);
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
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByIdGroupCreation($idGroupCreation = null, $comparison = null)
    {
        if (is_array($idGroupCreation)) {
            $useMinMax = false;
            if (isset($idGroupCreation['min'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::ID_GROUP_CREATION, $idGroupCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGroupCreation['max'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::ID_GROUP_CREATION, $idGroupCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyRefreshTokenPeer::ID_GROUP_CREATION, $idGroupCreation, $comparison);
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
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByIdCreation($idCreation = null, $comparison = null)
    {
        if (is_array($idCreation)) {
            $useMinMax = false;
            if (isset($idCreation['min'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::ID_CREATION, $idCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idCreation['max'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::ID_CREATION, $idCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyRefreshTokenPeer::ID_CREATION, $idCreation, $comparison);
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
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByIdModification($idModification = null, $comparison = null)
    {
        if (is_array($idModification)) {
            $useMinMax = false;
            if (isset($idModification['min'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::ID_MODIFICATION, $idModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idModification['max'])) {
                $this->addUsingAlias(AuthyRefreshTokenPeer::ID_MODIFICATION, $idModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyRefreshTokenPeer::ID_MODIFICATION, $idModification, $comparison);
    }

    /**
     * Filter the query by a related Authy object
     *
     * @param   Authy|PropelObjectCollection $authy The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyRefreshTokenQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdAuthy($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(AuthyRefreshTokenPeer::ID_AUTHY, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(AuthyRefreshTokenPeer::ID_AUTHY, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
        } else {
            throw new PropelException('filterByAuthyRelatedByIdAuthy() only accepts arguments of type Authy or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyRelatedByIdAuthy relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function joinAuthyRelatedByIdAuthy($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyRelatedByIdAuthy');

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
            $this->addJoinObject($join, 'AuthyRelatedByIdAuthy');
        }

        return $this;
    }

    /**
     * Use the AuthyRelatedByIdAuthy relation Authy object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyQuery A secondary query class using the current class as primary query
     */
    public function useAuthyRelatedByIdAuthyQuery($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        return $this
            ->joinAuthyRelatedByIdAuthy($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyRelatedByIdAuthy', '\App\AuthyQuery');
    }

    /**
     * Filter the query by a related AuthyGroup object
     *
     * @param   AuthyGroup|PropelObjectCollection $authyGroup The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyRefreshTokenQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroup($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(AuthyRefreshTokenPeer::ID_GROUP_CREATION, $authyGroup->getIdAuthyGroup(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(AuthyRefreshTokenPeer::ID_GROUP_CREATION, $authyGroup->toKeyValue('PrimaryKey', 'IdAuthyGroup'), $comparison);
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
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
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
     * @return                 AuthyRefreshTokenQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdCreation($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(AuthyRefreshTokenPeer::ID_CREATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(AuthyRefreshTokenPeer::ID_CREATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
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
     * @return                 AuthyRefreshTokenQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdModification($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(AuthyRefreshTokenPeer::ID_MODIFICATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(AuthyRefreshTokenPeer::ID_MODIFICATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
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
     * @param   AuthyRefreshToken $authyRefreshToken Object to remove from the list of results
     *
     * @return AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function prune($authyRefreshToken = null)
    {
        if ($authyRefreshToken) {
            $this->addUsingAlias(AuthyRefreshTokenPeer::ID_AUTHY_REFRESH_TOKEN, $authyRefreshToken->getIdAuthyRefreshToken(), Criteria::NOT_EQUAL);
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
                    \ApiGoat\Utility\TableVersion::bump('authy_refresh_token');
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
                    \ApiGoat\Utility\TableVersion::bump('authy_refresh_token');
                }

        return $this->postUpdate($affectedRows, $con);
    }

    // add_tablestamp behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7){
        return $this->addUsingAlias(AuthyRefreshTokenPeer::DATE_MODIFICATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst(){
        return $this->addDescendingOrderByColumn(AuthyRefreshTokenPeer::DATE_MODIFICATION);
    }

    /**
     * Order by update date asc
     *
     * @return     AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst(){
        return $this->addAscendingOrderByColumn(AuthyRefreshTokenPeer::DATE_MODIFICATION);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7){
        return $this->addUsingAlias(AuthyRefreshTokenPeer::DATE_CREATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return     AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function lastCreatedFirst(){
        return $this->addDescendingOrderByColumn(AuthyRefreshTokenPeer::DATE_CREATION);
    }

    /**
     * Order by create date asc
     *
     * @return     AuthyRefreshTokenQuery The current query, for fluid interface
     */
    public function firstCreatedFirst(){
        return $this->addAscendingOrderByColumn(AuthyRefreshTokenPeer::DATE_CREATION);
    }
}
