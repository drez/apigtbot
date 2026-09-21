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
use App\OauthRefreshToken;
use App\OauthRefreshTokenPeer;
use App\OauthRefreshTokenQuery;

/**
 * Base class that represents a query for the 'oauth_refresh_token' table.
 *
 *
 *
 * @method OauthRefreshTokenQuery orderByIdOauthRefreshToken($order = Criteria::ASC) Order by the id_oauth_refresh_token column
 * @method OauthRefreshTokenQuery orderByTokenId($order = Criteria::ASC) Order by the token_id column
 * @method OauthRefreshTokenQuery orderByAccessTokenId($order = Criteria::ASC) Order by the access_token_id column
 * @method OauthRefreshTokenQuery orderByIdAuthy($order = Criteria::ASC) Order by the id_authy column
 * @method OauthRefreshTokenQuery orderByClientId($order = Criteria::ASC) Order by the client_id column
 * @method OauthRefreshTokenQuery orderByExpires($order = Criteria::ASC) Order by the expires column
 * @method OauthRefreshTokenQuery orderByRevoked($order = Criteria::ASC) Order by the revoked column
 * @method OauthRefreshTokenQuery orderByCreatedAt($order = Criteria::ASC) Order by the created_at column
 * @method OauthRefreshTokenQuery orderByDateCreation($order = Criteria::ASC) Order by the date_creation column
 * @method OauthRefreshTokenQuery orderByDateModification($order = Criteria::ASC) Order by the date_modification column
 * @method OauthRefreshTokenQuery orderByIdGroupCreation($order = Criteria::ASC) Order by the id_group_creation column
 * @method OauthRefreshTokenQuery orderByIdCreation($order = Criteria::ASC) Order by the id_creation column
 * @method OauthRefreshTokenQuery orderByIdModification($order = Criteria::ASC) Order by the id_modification column
 *
 * @method OauthRefreshTokenQuery groupByIdOauthRefreshToken() Group by the id_oauth_refresh_token column
 * @method OauthRefreshTokenQuery groupByTokenId() Group by the token_id column
 * @method OauthRefreshTokenQuery groupByAccessTokenId() Group by the access_token_id column
 * @method OauthRefreshTokenQuery groupByIdAuthy() Group by the id_authy column
 * @method OauthRefreshTokenQuery groupByClientId() Group by the client_id column
 * @method OauthRefreshTokenQuery groupByExpires() Group by the expires column
 * @method OauthRefreshTokenQuery groupByRevoked() Group by the revoked column
 * @method OauthRefreshTokenQuery groupByCreatedAt() Group by the created_at column
 * @method OauthRefreshTokenQuery groupByDateCreation() Group by the date_creation column
 * @method OauthRefreshTokenQuery groupByDateModification() Group by the date_modification column
 * @method OauthRefreshTokenQuery groupByIdGroupCreation() Group by the id_group_creation column
 * @method OauthRefreshTokenQuery groupByIdCreation() Group by the id_creation column
 * @method OauthRefreshTokenQuery groupByIdModification() Group by the id_modification column
 *
 * @method OauthRefreshTokenQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method OauthRefreshTokenQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method OauthRefreshTokenQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method OauthRefreshTokenQuery leftJoinAuthyRelatedByIdAuthy($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdAuthy relation
 * @method OauthRefreshTokenQuery rightJoinAuthyRelatedByIdAuthy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdAuthy relation
 * @method OauthRefreshTokenQuery innerJoinAuthyRelatedByIdAuthy($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdAuthy relation
 *
 * @method OauthRefreshTokenQuery leftJoinAuthyGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroup relation
 * @method OauthRefreshTokenQuery rightJoinAuthyGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroup relation
 * @method OauthRefreshTokenQuery innerJoinAuthyGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroup relation
 *
 * @method OauthRefreshTokenQuery leftJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method OauthRefreshTokenQuery rightJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method OauthRefreshTokenQuery innerJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdCreation relation
 *
 * @method OauthRefreshTokenQuery leftJoinAuthyRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method OauthRefreshTokenQuery rightJoinAuthyRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method OauthRefreshTokenQuery innerJoinAuthyRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdModification relation
 *
 * @method OauthRefreshToken findOne(?PropelPDO $con = null) Return the first OauthRefreshToken matching the query
 * @method OauthRefreshToken findOneOrCreate(?PropelPDO $con = null) Return the first OauthRefreshToken matching the query, or a new OauthRefreshToken object populated from the query conditions when no match is found
 *
 * @method OauthRefreshToken findOneByTokenId(string $token_id) Return the first OauthRefreshToken filtered by the token_id column
 * @method OauthRefreshToken findOneByAccessTokenId(string $access_token_id) Return the first OauthRefreshToken filtered by the access_token_id column
 * @method OauthRefreshToken findOneByIdAuthy(int $id_authy) Return the first OauthRefreshToken filtered by the id_authy column
 * @method OauthRefreshToken findOneByClientId(string $client_id) Return the first OauthRefreshToken filtered by the client_id column
 * @method OauthRefreshToken findOneByExpires(int $expires) Return the first OauthRefreshToken filtered by the expires column
 * @method OauthRefreshToken findOneByRevoked(int $revoked) Return the first OauthRefreshToken filtered by the revoked column
 * @method OauthRefreshToken findOneByCreatedAt(string $created_at) Return the first OauthRefreshToken filtered by the created_at column
 * @method OauthRefreshToken findOneByDateCreation(string $date_creation) Return the first OauthRefreshToken filtered by the date_creation column
 * @method OauthRefreshToken findOneByDateModification(string $date_modification) Return the first OauthRefreshToken filtered by the date_modification column
 * @method OauthRefreshToken findOneByIdGroupCreation(int $id_group_creation) Return the first OauthRefreshToken filtered by the id_group_creation column
 * @method OauthRefreshToken findOneByIdCreation(int $id_creation) Return the first OauthRefreshToken filtered by the id_creation column
 * @method OauthRefreshToken findOneByIdModification(int $id_modification) Return the first OauthRefreshToken filtered by the id_modification column
 *
 * @method array findByIdOauthRefreshToken(int $id_oauth_refresh_token) Return OauthRefreshToken objects filtered by the id_oauth_refresh_token column
 * @method array findByTokenId(string $token_id) Return OauthRefreshToken objects filtered by the token_id column
 * @method array findByAccessTokenId(string $access_token_id) Return OauthRefreshToken objects filtered by the access_token_id column
 * @method array findByIdAuthy(int $id_authy) Return OauthRefreshToken objects filtered by the id_authy column
 * @method array findByClientId(string $client_id) Return OauthRefreshToken objects filtered by the client_id column
 * @method array findByExpires(int $expires) Return OauthRefreshToken objects filtered by the expires column
 * @method array findByRevoked(int $revoked) Return OauthRefreshToken objects filtered by the revoked column
 * @method array findByCreatedAt(string $created_at) Return OauthRefreshToken objects filtered by the created_at column
 * @method array findByDateCreation(string $date_creation) Return OauthRefreshToken objects filtered by the date_creation column
 * @method array findByDateModification(string $date_modification) Return OauthRefreshToken objects filtered by the date_modification column
 * @method array findByIdGroupCreation(int $id_group_creation) Return OauthRefreshToken objects filtered by the id_group_creation column
 * @method array findByIdCreation(int $id_creation) Return OauthRefreshToken objects filtered by the id_creation column
 * @method array findByIdModification(int $id_modification) Return OauthRefreshToken objects filtered by the id_modification column
 *
 * @package    propel.generator..om
 */
abstract class BaseOauthRefreshTokenQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseOauthRefreshTokenQuery object.
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
            $modelName = 'App\\OauthRefreshToken';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new OauthRefreshTokenQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   OauthRefreshTokenQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return OauthRefreshTokenQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof OauthRefreshTokenQuery) {
            return $criteria;
        }
        $query = new OauthRefreshTokenQuery(null, null, $modelAlias);

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
     * @return   OauthRefreshToken|OauthRefreshToken[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = OauthRefreshTokenPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(OauthRefreshTokenPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 OauthRefreshToken A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdOauthRefreshToken($key, $con = null)
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
     * @return                 OauthRefreshToken A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_oauth_refresh_token`, `token_id`, `access_token_id`, `id_authy`, `client_id`, `expires`, `revoked`, `created_at`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification` FROM `oauth_refresh_token` WHERE `id_oauth_refresh_token` = :p0';
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
            $obj = new OauthRefreshToken();
            $obj->hydrate($row);
            OauthRefreshTokenPeer::addInstanceToPool($obj, (string) $key);
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
     * @return OauthRefreshToken|OauthRefreshToken[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|OauthRefreshToken[]|mixed the list of results, formatted by the current formatter
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
     * @return OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(OauthRefreshTokenPeer::ID_OAUTH_REFRESH_TOKEN, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(OauthRefreshTokenPeer::ID_OAUTH_REFRESH_TOKEN, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id_oauth_refresh_token column
     *
     * Example usage:
     * <code>
     * $query->filterByIdOauthRefreshToken(1234); // WHERE id_oauth_refresh_token = 1234
     * $query->filterByIdOauthRefreshToken(array(12, 34)); // WHERE id_oauth_refresh_token IN (12, 34)
     * $query->filterByIdOauthRefreshToken(array('min' => 12)); // WHERE id_oauth_refresh_token >= 12
     * $query->filterByIdOauthRefreshToken(array('max' => 12)); // WHERE id_oauth_refresh_token <= 12
     * </code>
     *
     * @param     mixed $idOauthRefreshToken The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByIdOauthRefreshToken($idOauthRefreshToken = null, $comparison = null)
    {
        if (is_array($idOauthRefreshToken)) {
            $useMinMax = false;
            if (isset($idOauthRefreshToken['min'])) {
                $this->addUsingAlias(OauthRefreshTokenPeer::ID_OAUTH_REFRESH_TOKEN, $idOauthRefreshToken['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idOauthRefreshToken['max'])) {
                $this->addUsingAlias(OauthRefreshTokenPeer::ID_OAUTH_REFRESH_TOKEN, $idOauthRefreshToken['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(OauthRefreshTokenPeer::ID_OAUTH_REFRESH_TOKEN, $idOauthRefreshToken, $comparison);
    }

    /**
     * Filter the query on the token_id column
     *
     * Example usage:
     * <code>
     * $query->filterByTokenId('fooValue');   // WHERE token_id = 'fooValue'
     * $query->filterByTokenId('%fooValue%'); // WHERE token_id LIKE '%fooValue%'
     * </code>
     *
     * @param     string $tokenId The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByTokenId($tokenId = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($tokenId)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $tokenId)) {
                $tokenId = str_replace('*', '%', $tokenId);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(OauthRefreshTokenPeer::TOKEN_ID, $tokenId, $comparison);
    }

    /**
     * Filter the query on the access_token_id column
     *
     * Example usage:
     * <code>
     * $query->filterByAccessTokenId('fooValue');   // WHERE access_token_id = 'fooValue'
     * $query->filterByAccessTokenId('%fooValue%'); // WHERE access_token_id LIKE '%fooValue%'
     * </code>
     *
     * @param     string $accessTokenId The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByAccessTokenId($accessTokenId = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($accessTokenId)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $accessTokenId)) {
                $accessTokenId = str_replace('*', '%', $accessTokenId);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(OauthRefreshTokenPeer::ACCESS_TOKEN_ID, $accessTokenId, $comparison);
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
     * @return OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByIdAuthy($idAuthy = null, $comparison = null)
    {
        if (is_array($idAuthy)) {
            $useMinMax = false;
            if (isset($idAuthy['min'])) {
                $this->addUsingAlias(OauthRefreshTokenPeer::ID_AUTHY, $idAuthy['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idAuthy['max'])) {
                $this->addUsingAlias(OauthRefreshTokenPeer::ID_AUTHY, $idAuthy['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(OauthRefreshTokenPeer::ID_AUTHY, $idAuthy, $comparison);
    }

    /**
     * Filter the query on the client_id column
     *
     * Example usage:
     * <code>
     * $query->filterByClientId('fooValue');   // WHERE client_id = 'fooValue'
     * $query->filterByClientId('%fooValue%'); // WHERE client_id LIKE '%fooValue%'
     * </code>
     *
     * @param     string $clientId The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByClientId($clientId = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($clientId)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $clientId)) {
                $clientId = str_replace('*', '%', $clientId);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(OauthRefreshTokenPeer::CLIENT_ID, $clientId, $comparison);
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
     * @return OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByExpires($expires = null, $comparison = null)
    {
        if (is_array($expires)) {
            $useMinMax = false;
            if (isset($expires['min'])) {
                $this->addUsingAlias(OauthRefreshTokenPeer::EXPIRES, $expires['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($expires['max'])) {
                $this->addUsingAlias(OauthRefreshTokenPeer::EXPIRES, $expires['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(OauthRefreshTokenPeer::EXPIRES, $expires, $comparison);
    }

    /**
     * Filter the query on the revoked column
     *
     * @param     mixed $revoked The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return OauthRefreshTokenQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByRevoked($revoked = null, $comparison = null)
    {
        if (is_scalar($revoked)) {
            $revoked = OauthRefreshTokenPeer::getSqlValueForEnum(OauthRefreshTokenPeer::REVOKED, $revoked);
        } elseif (is_array($revoked)) {
            $convertedValues = array();
            foreach ($revoked as $value) {
                $convertedValues[] = OauthRefreshTokenPeer::getSqlValueForEnum(OauthRefreshTokenPeer::REVOKED, $value);
            }
            $revoked = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(OauthRefreshTokenPeer::REVOKED, $revoked, $comparison);
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
     * @return OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByCreatedAt($createdAt = null, $comparison = null)
    {
        if (is_array($createdAt)) {
            $useMinMax = false;
            if (isset($createdAt['min'])) {
                $this->addUsingAlias(OauthRefreshTokenPeer::CREATED_AT, $createdAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdAt['max'])) {
                $this->addUsingAlias(OauthRefreshTokenPeer::CREATED_AT, $createdAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(OauthRefreshTokenPeer::CREATED_AT, $createdAt, $comparison);
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
     * @return OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByDateCreation($dateCreation = null, $comparison = null)
    {
        if (is_array($dateCreation)) {
            $useMinMax = false;
            if (isset($dateCreation['min'])) {
                $this->addUsingAlias(OauthRefreshTokenPeer::DATE_CREATION, $dateCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateCreation['max'])) {
                $this->addUsingAlias(OauthRefreshTokenPeer::DATE_CREATION, $dateCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(OauthRefreshTokenPeer::DATE_CREATION, $dateCreation, $comparison);
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
     * @return OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByDateModification($dateModification = null, $comparison = null)
    {
        if (is_array($dateModification)) {
            $useMinMax = false;
            if (isset($dateModification['min'])) {
                $this->addUsingAlias(OauthRefreshTokenPeer::DATE_MODIFICATION, $dateModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateModification['max'])) {
                $this->addUsingAlias(OauthRefreshTokenPeer::DATE_MODIFICATION, $dateModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(OauthRefreshTokenPeer::DATE_MODIFICATION, $dateModification, $comparison);
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
     * @return OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByIdGroupCreation($idGroupCreation = null, $comparison = null)
    {
        if (is_array($idGroupCreation)) {
            $useMinMax = false;
            if (isset($idGroupCreation['min'])) {
                $this->addUsingAlias(OauthRefreshTokenPeer::ID_GROUP_CREATION, $idGroupCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGroupCreation['max'])) {
                $this->addUsingAlias(OauthRefreshTokenPeer::ID_GROUP_CREATION, $idGroupCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(OauthRefreshTokenPeer::ID_GROUP_CREATION, $idGroupCreation, $comparison);
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
     * @return OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByIdCreation($idCreation = null, $comparison = null)
    {
        if (is_array($idCreation)) {
            $useMinMax = false;
            if (isset($idCreation['min'])) {
                $this->addUsingAlias(OauthRefreshTokenPeer::ID_CREATION, $idCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idCreation['max'])) {
                $this->addUsingAlias(OauthRefreshTokenPeer::ID_CREATION, $idCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(OauthRefreshTokenPeer::ID_CREATION, $idCreation, $comparison);
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
     * @return OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function filterByIdModification($idModification = null, $comparison = null)
    {
        if (is_array($idModification)) {
            $useMinMax = false;
            if (isset($idModification['min'])) {
                $this->addUsingAlias(OauthRefreshTokenPeer::ID_MODIFICATION, $idModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idModification['max'])) {
                $this->addUsingAlias(OauthRefreshTokenPeer::ID_MODIFICATION, $idModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(OauthRefreshTokenPeer::ID_MODIFICATION, $idModification, $comparison);
    }

    /**
     * Filter the query by a related Authy object
     *
     * @param   Authy|PropelObjectCollection $authy The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 OauthRefreshTokenQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdAuthy($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(OauthRefreshTokenPeer::ID_AUTHY, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(OauthRefreshTokenPeer::ID_AUTHY, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return OauthRefreshTokenQuery The current query, for fluid interface
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
     * @return                 OauthRefreshTokenQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroup($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(OauthRefreshTokenPeer::ID_GROUP_CREATION, $authyGroup->getIdAuthyGroup(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(OauthRefreshTokenPeer::ID_GROUP_CREATION, $authyGroup->toKeyValue('PrimaryKey', 'IdAuthyGroup'), $comparison);
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
     * @return OauthRefreshTokenQuery The current query, for fluid interface
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
     * @return                 OauthRefreshTokenQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdCreation($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(OauthRefreshTokenPeer::ID_CREATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(OauthRefreshTokenPeer::ID_CREATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return OauthRefreshTokenQuery The current query, for fluid interface
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
     * @return                 OauthRefreshTokenQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdModification($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(OauthRefreshTokenPeer::ID_MODIFICATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(OauthRefreshTokenPeer::ID_MODIFICATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return OauthRefreshTokenQuery The current query, for fluid interface
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
     * @param   OauthRefreshToken $oauthRefreshToken Object to remove from the list of results
     *
     * @return OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function prune($oauthRefreshToken = null)
    {
        if ($oauthRefreshToken) {
            $this->addUsingAlias(OauthRefreshTokenPeer::ID_OAUTH_REFRESH_TOKEN, $oauthRefreshToken->getIdOauthRefreshToken(), Criteria::NOT_EQUAL);
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
                    \ApiGoat\Utility\TableVersion::bump('oauth_refresh_token');
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
                    \ApiGoat\Utility\TableVersion::bump('oauth_refresh_token');
                }

        return $this->postUpdate($affectedRows, $con);
    }

    // add_tablestamp behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7){
        return $this->addUsingAlias(OauthRefreshTokenPeer::DATE_MODIFICATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst(){
        return $this->addDescendingOrderByColumn(OauthRefreshTokenPeer::DATE_MODIFICATION);
    }

    /**
     * Order by update date asc
     *
     * @return     OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst(){
        return $this->addAscendingOrderByColumn(OauthRefreshTokenPeer::DATE_MODIFICATION);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7){
        return $this->addUsingAlias(OauthRefreshTokenPeer::DATE_CREATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return     OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function lastCreatedFirst(){
        return $this->addDescendingOrderByColumn(OauthRefreshTokenPeer::DATE_CREATION);
    }

    /**
     * Order by create date asc
     *
     * @return     OauthRefreshTokenQuery The current query, for fluid interface
     */
    public function firstCreatedFirst(){
        return $this->addAscendingOrderByColumn(OauthRefreshTokenPeer::DATE_CREATION);
    }
}
