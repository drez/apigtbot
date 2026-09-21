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
use App\OauthClient;
use App\OauthClientPeer;
use App\OauthClientQuery;

/**
 * Base class that represents a query for the 'oauth_client' table.
 *
 *
 *
 * @method OauthClientQuery orderByIdOauthClient($order = Criteria::ASC) Order by the id_oauth_client column
 * @method OauthClientQuery orderByClientId($order = Criteria::ASC) Order by the client_id column
 * @method OauthClientQuery orderByClientSecretHash($order = Criteria::ASC) Order by the client_secret_hash column
 * @method OauthClientQuery orderByName($order = Criteria::ASC) Order by the name column
 * @method OauthClientQuery orderByRedirectUris($order = Criteria::ASC) Order by the redirect_uris column
 * @method OauthClientQuery orderByGrantTypes($order = Criteria::ASC) Order by the grant_types column
 * @method OauthClientQuery orderByScopes($order = Criteria::ASC) Order by the scopes column
 * @method OauthClientQuery orderByIsConfidential($order = Criteria::ASC) Order by the is_confidential column
 * @method OauthClientQuery orderByCreatedAt($order = Criteria::ASC) Order by the created_at column
 * @method OauthClientQuery orderByDateCreation($order = Criteria::ASC) Order by the date_creation column
 * @method OauthClientQuery orderByDateModification($order = Criteria::ASC) Order by the date_modification column
 * @method OauthClientQuery orderByIdGroupCreation($order = Criteria::ASC) Order by the id_group_creation column
 * @method OauthClientQuery orderByIdCreation($order = Criteria::ASC) Order by the id_creation column
 * @method OauthClientQuery orderByIdModification($order = Criteria::ASC) Order by the id_modification column
 *
 * @method OauthClientQuery groupByIdOauthClient() Group by the id_oauth_client column
 * @method OauthClientQuery groupByClientId() Group by the client_id column
 * @method OauthClientQuery groupByClientSecretHash() Group by the client_secret_hash column
 * @method OauthClientQuery groupByName() Group by the name column
 * @method OauthClientQuery groupByRedirectUris() Group by the redirect_uris column
 * @method OauthClientQuery groupByGrantTypes() Group by the grant_types column
 * @method OauthClientQuery groupByScopes() Group by the scopes column
 * @method OauthClientQuery groupByIsConfidential() Group by the is_confidential column
 * @method OauthClientQuery groupByCreatedAt() Group by the created_at column
 * @method OauthClientQuery groupByDateCreation() Group by the date_creation column
 * @method OauthClientQuery groupByDateModification() Group by the date_modification column
 * @method OauthClientQuery groupByIdGroupCreation() Group by the id_group_creation column
 * @method OauthClientQuery groupByIdCreation() Group by the id_creation column
 * @method OauthClientQuery groupByIdModification() Group by the id_modification column
 *
 * @method OauthClientQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method OauthClientQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method OauthClientQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method OauthClientQuery leftJoinAuthyGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroup relation
 * @method OauthClientQuery rightJoinAuthyGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroup relation
 * @method OauthClientQuery innerJoinAuthyGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroup relation
 *
 * @method OauthClientQuery leftJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method OauthClientQuery rightJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method OauthClientQuery innerJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdCreation relation
 *
 * @method OauthClientQuery leftJoinAuthyRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method OauthClientQuery rightJoinAuthyRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method OauthClientQuery innerJoinAuthyRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdModification relation
 *
 * @method OauthClient findOne(?PropelPDO $con = null) Return the first OauthClient matching the query
 * @method OauthClient findOneOrCreate(?PropelPDO $con = null) Return the first OauthClient matching the query, or a new OauthClient object populated from the query conditions when no match is found
 *
 * @method OauthClient findOneByClientId(string $client_id) Return the first OauthClient filtered by the client_id column
 * @method OauthClient findOneByClientSecretHash(string $client_secret_hash) Return the first OauthClient filtered by the client_secret_hash column
 * @method OauthClient findOneByName(string $name) Return the first OauthClient filtered by the name column
 * @method OauthClient findOneByRedirectUris(string $redirect_uris) Return the first OauthClient filtered by the redirect_uris column
 * @method OauthClient findOneByGrantTypes(string $grant_types) Return the first OauthClient filtered by the grant_types column
 * @method OauthClient findOneByScopes(string $scopes) Return the first OauthClient filtered by the scopes column
 * @method OauthClient findOneByIsConfidential(int $is_confidential) Return the first OauthClient filtered by the is_confidential column
 * @method OauthClient findOneByCreatedAt(string $created_at) Return the first OauthClient filtered by the created_at column
 * @method OauthClient findOneByDateCreation(string $date_creation) Return the first OauthClient filtered by the date_creation column
 * @method OauthClient findOneByDateModification(string $date_modification) Return the first OauthClient filtered by the date_modification column
 * @method OauthClient findOneByIdGroupCreation(int $id_group_creation) Return the first OauthClient filtered by the id_group_creation column
 * @method OauthClient findOneByIdCreation(int $id_creation) Return the first OauthClient filtered by the id_creation column
 * @method OauthClient findOneByIdModification(int $id_modification) Return the first OauthClient filtered by the id_modification column
 *
 * @method array findByIdOauthClient(int $id_oauth_client) Return OauthClient objects filtered by the id_oauth_client column
 * @method array findByClientId(string $client_id) Return OauthClient objects filtered by the client_id column
 * @method array findByClientSecretHash(string $client_secret_hash) Return OauthClient objects filtered by the client_secret_hash column
 * @method array findByName(string $name) Return OauthClient objects filtered by the name column
 * @method array findByRedirectUris(string $redirect_uris) Return OauthClient objects filtered by the redirect_uris column
 * @method array findByGrantTypes(string $grant_types) Return OauthClient objects filtered by the grant_types column
 * @method array findByScopes(string $scopes) Return OauthClient objects filtered by the scopes column
 * @method array findByIsConfidential(int $is_confidential) Return OauthClient objects filtered by the is_confidential column
 * @method array findByCreatedAt(string $created_at) Return OauthClient objects filtered by the created_at column
 * @method array findByDateCreation(string $date_creation) Return OauthClient objects filtered by the date_creation column
 * @method array findByDateModification(string $date_modification) Return OauthClient objects filtered by the date_modification column
 * @method array findByIdGroupCreation(int $id_group_creation) Return OauthClient objects filtered by the id_group_creation column
 * @method array findByIdCreation(int $id_creation) Return OauthClient objects filtered by the id_creation column
 * @method array findByIdModification(int $id_modification) Return OauthClient objects filtered by the id_modification column
 *
 * @package    propel.generator..om
 */
abstract class BaseOauthClientQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseOauthClientQuery object.
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
            $modelName = 'App\\OauthClient';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new OauthClientQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   OauthClientQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return OauthClientQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof OauthClientQuery) {
            return $criteria;
        }
        $query = new OauthClientQuery(null, null, $modelAlias);

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
     * @return   OauthClient|OauthClient[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = OauthClientPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(OauthClientPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 OauthClient A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdOauthClient($key, $con = null)
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
     * @return                 OauthClient A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_oauth_client`, `client_id`, `client_secret_hash`, `name`, `redirect_uris`, `grant_types`, `scopes`, `is_confidential`, `created_at`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification` FROM `oauth_client` WHERE `id_oauth_client` = :p0';
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
            $obj = new OauthClient();
            $obj->hydrate($row);
            OauthClientPeer::addInstanceToPool($obj, (string) $key);
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
     * @return OauthClient|OauthClient[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|OauthClient[]|mixed the list of results, formatted by the current formatter
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
     * @return OauthClientQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(OauthClientPeer::ID_OAUTH_CLIENT, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return OauthClientQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(OauthClientPeer::ID_OAUTH_CLIENT, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id_oauth_client column
     *
     * Example usage:
     * <code>
     * $query->filterByIdOauthClient(1234); // WHERE id_oauth_client = 1234
     * $query->filterByIdOauthClient(array(12, 34)); // WHERE id_oauth_client IN (12, 34)
     * $query->filterByIdOauthClient(array('min' => 12)); // WHERE id_oauth_client >= 12
     * $query->filterByIdOauthClient(array('max' => 12)); // WHERE id_oauth_client <= 12
     * </code>
     *
     * @param     mixed $idOauthClient The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return OauthClientQuery The current query, for fluid interface
     */
    public function filterByIdOauthClient($idOauthClient = null, $comparison = null)
    {
        if (is_array($idOauthClient)) {
            $useMinMax = false;
            if (isset($idOauthClient['min'])) {
                $this->addUsingAlias(OauthClientPeer::ID_OAUTH_CLIENT, $idOauthClient['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idOauthClient['max'])) {
                $this->addUsingAlias(OauthClientPeer::ID_OAUTH_CLIENT, $idOauthClient['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(OauthClientPeer::ID_OAUTH_CLIENT, $idOauthClient, $comparison);
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
     * @return OauthClientQuery The current query, for fluid interface
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

        return $this->addUsingAlias(OauthClientPeer::CLIENT_ID, $clientId, $comparison);
    }

    /**
     * Filter the query on the client_secret_hash column
     *
     * Example usage:
     * <code>
     * $query->filterByClientSecretHash('fooValue');   // WHERE client_secret_hash = 'fooValue'
     * $query->filterByClientSecretHash('%fooValue%'); // WHERE client_secret_hash LIKE '%fooValue%'
     * </code>
     *
     * @param     string $clientSecretHash The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return OauthClientQuery The current query, for fluid interface
     */
    public function filterByClientSecretHash($clientSecretHash = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($clientSecretHash)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $clientSecretHash)) {
                $clientSecretHash = str_replace('*', '%', $clientSecretHash);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(OauthClientPeer::CLIENT_SECRET_HASH, $clientSecretHash, $comparison);
    }

    /**
     * Filter the query on the name column
     *
     * Example usage:
     * <code>
     * $query->filterByName('fooValue');   // WHERE name = 'fooValue'
     * $query->filterByName('%fooValue%'); // WHERE name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $name The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return OauthClientQuery The current query, for fluid interface
     */
    public function filterByName($name = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($name)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $name)) {
                $name = str_replace('*', '%', $name);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(OauthClientPeer::NAME, $name, $comparison);
    }

    /**
     * Filter the query on the redirect_uris column
     *
     * Example usage:
     * <code>
     * $query->filterByRedirectUris('fooValue');   // WHERE redirect_uris = 'fooValue'
     * $query->filterByRedirectUris('%fooValue%'); // WHERE redirect_uris LIKE '%fooValue%'
     * </code>
     *
     * @param     string $redirectUris The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return OauthClientQuery The current query, for fluid interface
     */
    public function filterByRedirectUris($redirectUris = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($redirectUris)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $redirectUris)) {
                $redirectUris = str_replace('*', '%', $redirectUris);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(OauthClientPeer::REDIRECT_URIS, $redirectUris, $comparison);
    }

    /**
     * Filter the query on the grant_types column
     *
     * Example usage:
     * <code>
     * $query->filterByGrantTypes('fooValue');   // WHERE grant_types = 'fooValue'
     * $query->filterByGrantTypes('%fooValue%'); // WHERE grant_types LIKE '%fooValue%'
     * </code>
     *
     * @param     string $grantTypes The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return OauthClientQuery The current query, for fluid interface
     */
    public function filterByGrantTypes($grantTypes = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($grantTypes)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $grantTypes)) {
                $grantTypes = str_replace('*', '%', $grantTypes);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(OauthClientPeer::GRANT_TYPES, $grantTypes, $comparison);
    }

    /**
     * Filter the query on the scopes column
     *
     * Example usage:
     * <code>
     * $query->filterByScopes('fooValue');   // WHERE scopes = 'fooValue'
     * $query->filterByScopes('%fooValue%'); // WHERE scopes LIKE '%fooValue%'
     * </code>
     *
     * @param     string $scopes The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return OauthClientQuery The current query, for fluid interface
     */
    public function filterByScopes($scopes = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($scopes)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $scopes)) {
                $scopes = str_replace('*', '%', $scopes);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(OauthClientPeer::SCOPES, $scopes, $comparison);
    }

    /**
     * Filter the query on the is_confidential column
     *
     * @param     mixed $isConfidential The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return OauthClientQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByIsConfidential($isConfidential = null, $comparison = null)
    {
        if (is_scalar($isConfidential)) {
            $isConfidential = OauthClientPeer::getSqlValueForEnum(OauthClientPeer::IS_CONFIDENTIAL, $isConfidential);
        } elseif (is_array($isConfidential)) {
            $convertedValues = array();
            foreach ($isConfidential as $value) {
                $convertedValues[] = OauthClientPeer::getSqlValueForEnum(OauthClientPeer::IS_CONFIDENTIAL, $value);
            }
            $isConfidential = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(OauthClientPeer::IS_CONFIDENTIAL, $isConfidential, $comparison);
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
     * @return OauthClientQuery The current query, for fluid interface
     */
    public function filterByCreatedAt($createdAt = null, $comparison = null)
    {
        if (is_array($createdAt)) {
            $useMinMax = false;
            if (isset($createdAt['min'])) {
                $this->addUsingAlias(OauthClientPeer::CREATED_AT, $createdAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($createdAt['max'])) {
                $this->addUsingAlias(OauthClientPeer::CREATED_AT, $createdAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(OauthClientPeer::CREATED_AT, $createdAt, $comparison);
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
     * @return OauthClientQuery The current query, for fluid interface
     */
    public function filterByDateCreation($dateCreation = null, $comparison = null)
    {
        if (is_array($dateCreation)) {
            $useMinMax = false;
            if (isset($dateCreation['min'])) {
                $this->addUsingAlias(OauthClientPeer::DATE_CREATION, $dateCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateCreation['max'])) {
                $this->addUsingAlias(OauthClientPeer::DATE_CREATION, $dateCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(OauthClientPeer::DATE_CREATION, $dateCreation, $comparison);
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
     * @return OauthClientQuery The current query, for fluid interface
     */
    public function filterByDateModification($dateModification = null, $comparison = null)
    {
        if (is_array($dateModification)) {
            $useMinMax = false;
            if (isset($dateModification['min'])) {
                $this->addUsingAlias(OauthClientPeer::DATE_MODIFICATION, $dateModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateModification['max'])) {
                $this->addUsingAlias(OauthClientPeer::DATE_MODIFICATION, $dateModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(OauthClientPeer::DATE_MODIFICATION, $dateModification, $comparison);
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
     * @return OauthClientQuery The current query, for fluid interface
     */
    public function filterByIdGroupCreation($idGroupCreation = null, $comparison = null)
    {
        if (is_array($idGroupCreation)) {
            $useMinMax = false;
            if (isset($idGroupCreation['min'])) {
                $this->addUsingAlias(OauthClientPeer::ID_GROUP_CREATION, $idGroupCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGroupCreation['max'])) {
                $this->addUsingAlias(OauthClientPeer::ID_GROUP_CREATION, $idGroupCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(OauthClientPeer::ID_GROUP_CREATION, $idGroupCreation, $comparison);
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
     * @return OauthClientQuery The current query, for fluid interface
     */
    public function filterByIdCreation($idCreation = null, $comparison = null)
    {
        if (is_array($idCreation)) {
            $useMinMax = false;
            if (isset($idCreation['min'])) {
                $this->addUsingAlias(OauthClientPeer::ID_CREATION, $idCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idCreation['max'])) {
                $this->addUsingAlias(OauthClientPeer::ID_CREATION, $idCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(OauthClientPeer::ID_CREATION, $idCreation, $comparison);
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
     * @return OauthClientQuery The current query, for fluid interface
     */
    public function filterByIdModification($idModification = null, $comparison = null)
    {
        if (is_array($idModification)) {
            $useMinMax = false;
            if (isset($idModification['min'])) {
                $this->addUsingAlias(OauthClientPeer::ID_MODIFICATION, $idModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idModification['max'])) {
                $this->addUsingAlias(OauthClientPeer::ID_MODIFICATION, $idModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(OauthClientPeer::ID_MODIFICATION, $idModification, $comparison);
    }

    /**
     * Filter the query by a related AuthyGroup object
     *
     * @param   AuthyGroup|PropelObjectCollection $authyGroup The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 OauthClientQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroup($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(OauthClientPeer::ID_GROUP_CREATION, $authyGroup->getIdAuthyGroup(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(OauthClientPeer::ID_GROUP_CREATION, $authyGroup->toKeyValue('PrimaryKey', 'IdAuthyGroup'), $comparison);
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
     * @return OauthClientQuery The current query, for fluid interface
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
     * @return                 OauthClientQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdCreation($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(OauthClientPeer::ID_CREATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(OauthClientPeer::ID_CREATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return OauthClientQuery The current query, for fluid interface
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
     * @return                 OauthClientQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdModification($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(OauthClientPeer::ID_MODIFICATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(OauthClientPeer::ID_MODIFICATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return OauthClientQuery The current query, for fluid interface
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
     * @param   OauthClient $oauthClient Object to remove from the list of results
     *
     * @return OauthClientQuery The current query, for fluid interface
     */
    public function prune($oauthClient = null)
    {
        if ($oauthClient) {
            $this->addUsingAlias(OauthClientPeer::ID_OAUTH_CLIENT, $oauthClient->getIdOauthClient(), Criteria::NOT_EQUAL);
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
                    \ApiGoat\Utility\TableVersion::bump('oauth_client');
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
                    \ApiGoat\Utility\TableVersion::bump('oauth_client');
                }

        return $this->postUpdate($affectedRows, $con);
    }

    // add_tablestamp behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     OauthClientQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7){
        return $this->addUsingAlias(OauthClientPeer::DATE_MODIFICATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     OauthClientQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst(){
        return $this->addDescendingOrderByColumn(OauthClientPeer::DATE_MODIFICATION);
    }

    /**
     * Order by update date asc
     *
     * @return     OauthClientQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst(){
        return $this->addAscendingOrderByColumn(OauthClientPeer::DATE_MODIFICATION);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     OauthClientQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7){
        return $this->addUsingAlias(OauthClientPeer::DATE_CREATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return     OauthClientQuery The current query, for fluid interface
     */
    public function lastCreatedFirst(){
        return $this->addDescendingOrderByColumn(OauthClientPeer::DATE_CREATION);
    }

    /**
     * Order by create date asc
     *
     * @return     OauthClientQuery The current query, for fluid interface
     */
    public function firstCreatedFirst(){
        return $this->addAscendingOrderByColumn(OauthClientPeer::DATE_CREATION);
    }
}
