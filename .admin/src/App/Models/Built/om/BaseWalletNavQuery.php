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
use App\WalletNav;
use App\WalletNavPeer;
use App\WalletNavQuery;

/**
 * Base class that represents a query for the 'wallet_nav' table.
 *
 * Wallet NAV
 *
 * @method WalletNavQuery orderByIdWalletNav($order = Criteria::ASC) Order by the id_wallet_nav column
 * @method WalletNavQuery orderByMode($order = Criteria::ASC) Order by the mode column
 * @method WalletNavQuery orderByEquityQuote($order = Criteria::ASC) Order by the equity_quote column
 * @method WalletNavQuery orderByBudgetQuote($order = Criteria::ASC) Order by the budget_quote column
 * @method WalletNavQuery orderByRefSymbol($order = Criteria::ASC) Order by the ref_symbol column
 * @method WalletNavQuery orderByRefPrice($order = Criteria::ASC) Order by the ref_price column
 * @method WalletNavQuery orderByUnpriced($order = Criteria::ASC) Order by the unpriced column
 * @method WalletNavQuery orderByDateCreation($order = Criteria::ASC) Order by the date_creation column
 * @method WalletNavQuery orderByDateModification($order = Criteria::ASC) Order by the date_modification column
 * @method WalletNavQuery orderByIdGroupCreation($order = Criteria::ASC) Order by the id_group_creation column
 * @method WalletNavQuery orderByIdCreation($order = Criteria::ASC) Order by the id_creation column
 * @method WalletNavQuery orderByIdModification($order = Criteria::ASC) Order by the id_modification column
 *
 * @method WalletNavQuery groupByIdWalletNav() Group by the id_wallet_nav column
 * @method WalletNavQuery groupByMode() Group by the mode column
 * @method WalletNavQuery groupByEquityQuote() Group by the equity_quote column
 * @method WalletNavQuery groupByBudgetQuote() Group by the budget_quote column
 * @method WalletNavQuery groupByRefSymbol() Group by the ref_symbol column
 * @method WalletNavQuery groupByRefPrice() Group by the ref_price column
 * @method WalletNavQuery groupByUnpriced() Group by the unpriced column
 * @method WalletNavQuery groupByDateCreation() Group by the date_creation column
 * @method WalletNavQuery groupByDateModification() Group by the date_modification column
 * @method WalletNavQuery groupByIdGroupCreation() Group by the id_group_creation column
 * @method WalletNavQuery groupByIdCreation() Group by the id_creation column
 * @method WalletNavQuery groupByIdModification() Group by the id_modification column
 *
 * @method WalletNavQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method WalletNavQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method WalletNavQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method WalletNavQuery leftJoinAuthyGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroup relation
 * @method WalletNavQuery rightJoinAuthyGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroup relation
 * @method WalletNavQuery innerJoinAuthyGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroup relation
 *
 * @method WalletNavQuery leftJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method WalletNavQuery rightJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method WalletNavQuery innerJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdCreation relation
 *
 * @method WalletNavQuery leftJoinAuthyRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method WalletNavQuery rightJoinAuthyRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method WalletNavQuery innerJoinAuthyRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdModification relation
 *
 * @method WalletNav findOne(?PropelPDO $con = null) Return the first WalletNav matching the query
 * @method WalletNav findOneOrCreate(?PropelPDO $con = null) Return the first WalletNav matching the query, or a new WalletNav object populated from the query conditions when no match is found
 *
 * @method WalletNav findOneByMode(int $mode) Return the first WalletNav filtered by the mode column
 * @method WalletNav findOneByEquityQuote(string $equity_quote) Return the first WalletNav filtered by the equity_quote column
 * @method WalletNav findOneByBudgetQuote(string $budget_quote) Return the first WalletNav filtered by the budget_quote column
 * @method WalletNav findOneByRefSymbol(string $ref_symbol) Return the first WalletNav filtered by the ref_symbol column
 * @method WalletNav findOneByRefPrice(string $ref_price) Return the first WalletNav filtered by the ref_price column
 * @method WalletNav findOneByUnpriced(string $unpriced) Return the first WalletNav filtered by the unpriced column
 * @method WalletNav findOneByDateCreation(string $date_creation) Return the first WalletNav filtered by the date_creation column
 * @method WalletNav findOneByDateModification(string $date_modification) Return the first WalletNav filtered by the date_modification column
 * @method WalletNav findOneByIdGroupCreation(int $id_group_creation) Return the first WalletNav filtered by the id_group_creation column
 * @method WalletNav findOneByIdCreation(int $id_creation) Return the first WalletNav filtered by the id_creation column
 * @method WalletNav findOneByIdModification(int $id_modification) Return the first WalletNav filtered by the id_modification column
 *
 * @method array findByIdWalletNav(int $id_wallet_nav) Return WalletNav objects filtered by the id_wallet_nav column
 * @method array findByMode(int $mode) Return WalletNav objects filtered by the mode column
 * @method array findByEquityQuote(string $equity_quote) Return WalletNav objects filtered by the equity_quote column
 * @method array findByBudgetQuote(string $budget_quote) Return WalletNav objects filtered by the budget_quote column
 * @method array findByRefSymbol(string $ref_symbol) Return WalletNav objects filtered by the ref_symbol column
 * @method array findByRefPrice(string $ref_price) Return WalletNav objects filtered by the ref_price column
 * @method array findByUnpriced(string $unpriced) Return WalletNav objects filtered by the unpriced column
 * @method array findByDateCreation(string $date_creation) Return WalletNav objects filtered by the date_creation column
 * @method array findByDateModification(string $date_modification) Return WalletNav objects filtered by the date_modification column
 * @method array findByIdGroupCreation(int $id_group_creation) Return WalletNav objects filtered by the id_group_creation column
 * @method array findByIdCreation(int $id_creation) Return WalletNav objects filtered by the id_creation column
 * @method array findByIdModification(int $id_modification) Return WalletNav objects filtered by the id_modification column
 *
 * @package    propel.generator..om
 */
abstract class BaseWalletNavQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseWalletNavQuery object.
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
            $modelName = 'App\\WalletNav';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new WalletNavQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   WalletNavQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return WalletNavQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof WalletNavQuery) {
            return $criteria;
        }
        $query = new WalletNavQuery(null, null, $modelAlias);

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
     * @return   WalletNav|WalletNav[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = WalletNavPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(WalletNavPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 WalletNav A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdWalletNav($key, $con = null)
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
     * @return                 WalletNav A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_wallet_nav`, `mode`, `equity_quote`, `budget_quote`, `ref_symbol`, `ref_price`, `unpriced`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification` FROM `wallet_nav` WHERE `id_wallet_nav` = :p0';
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
            $obj = new WalletNav();
            $obj->hydrate($row);
            WalletNavPeer::addInstanceToPool($obj, (string) $key);
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
     * @return WalletNav|WalletNav[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|WalletNav[]|mixed the list of results, formatted by the current formatter
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
     * @return WalletNavQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(WalletNavPeer::ID_WALLET_NAV, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return WalletNavQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(WalletNavPeer::ID_WALLET_NAV, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id_wallet_nav column
     *
     * Example usage:
     * <code>
     * $query->filterByIdWalletNav(1234); // WHERE id_wallet_nav = 1234
     * $query->filterByIdWalletNav(array(12, 34)); // WHERE id_wallet_nav IN (12, 34)
     * $query->filterByIdWalletNav(array('min' => 12)); // WHERE id_wallet_nav >= 12
     * $query->filterByIdWalletNav(array('max' => 12)); // WHERE id_wallet_nav <= 12
     * </code>
     *
     * @param     mixed $idWalletNav The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return WalletNavQuery The current query, for fluid interface
     */
    public function filterByIdWalletNav($idWalletNav = null, $comparison = null)
    {
        if (is_array($idWalletNav)) {
            $useMinMax = false;
            if (isset($idWalletNav['min'])) {
                $this->addUsingAlias(WalletNavPeer::ID_WALLET_NAV, $idWalletNav['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idWalletNav['max'])) {
                $this->addUsingAlias(WalletNavPeer::ID_WALLET_NAV, $idWalletNav['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(WalletNavPeer::ID_WALLET_NAV, $idWalletNav, $comparison);
    }

    /**
     * Filter the query on the mode column
     *
     * @param     mixed $mode The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return WalletNavQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByMode($mode = null, $comparison = null)
    {
        if (is_scalar($mode)) {
            $mode = WalletNavPeer::getSqlValueForEnum(WalletNavPeer::MODE, $mode);
        } elseif (is_array($mode)) {
            $convertedValues = array();
            foreach ($mode as $value) {
                $convertedValues[] = WalletNavPeer::getSqlValueForEnum(WalletNavPeer::MODE, $value);
            }
            $mode = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(WalletNavPeer::MODE, $mode, $comparison);
    }

    /**
     * Filter the query on the equity_quote column
     *
     * Example usage:
     * <code>
     * $query->filterByEquityQuote(1234); // WHERE equity_quote = 1234
     * $query->filterByEquityQuote(array(12, 34)); // WHERE equity_quote IN (12, 34)
     * $query->filterByEquityQuote(array('min' => 12)); // WHERE equity_quote >= 12
     * $query->filterByEquityQuote(array('max' => 12)); // WHERE equity_quote <= 12
     * </code>
     *
     * @param     mixed $equityQuote The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return WalletNavQuery The current query, for fluid interface
     */
    public function filterByEquityQuote($equityQuote = null, $comparison = null)
    {
        if (is_array($equityQuote)) {
            $useMinMax = false;
            if (isset($equityQuote['min'])) {
                $this->addUsingAlias(WalletNavPeer::EQUITY_QUOTE, $equityQuote['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($equityQuote['max'])) {
                $this->addUsingAlias(WalletNavPeer::EQUITY_QUOTE, $equityQuote['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(WalletNavPeer::EQUITY_QUOTE, $equityQuote, $comparison);
    }

    /**
     * Filter the query on the budget_quote column
     *
     * Example usage:
     * <code>
     * $query->filterByBudgetQuote(1234); // WHERE budget_quote = 1234
     * $query->filterByBudgetQuote(array(12, 34)); // WHERE budget_quote IN (12, 34)
     * $query->filterByBudgetQuote(array('min' => 12)); // WHERE budget_quote >= 12
     * $query->filterByBudgetQuote(array('max' => 12)); // WHERE budget_quote <= 12
     * </code>
     *
     * @param     mixed $budgetQuote The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return WalletNavQuery The current query, for fluid interface
     */
    public function filterByBudgetQuote($budgetQuote = null, $comparison = null)
    {
        if (is_array($budgetQuote)) {
            $useMinMax = false;
            if (isset($budgetQuote['min'])) {
                $this->addUsingAlias(WalletNavPeer::BUDGET_QUOTE, $budgetQuote['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($budgetQuote['max'])) {
                $this->addUsingAlias(WalletNavPeer::BUDGET_QUOTE, $budgetQuote['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(WalletNavPeer::BUDGET_QUOTE, $budgetQuote, $comparison);
    }

    /**
     * Filter the query on the ref_symbol column
     *
     * Example usage:
     * <code>
     * $query->filterByRefSymbol('fooValue');   // WHERE ref_symbol = 'fooValue'
     * $query->filterByRefSymbol('%fooValue%'); // WHERE ref_symbol LIKE '%fooValue%'
     * </code>
     *
     * @param     string $refSymbol The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return WalletNavQuery The current query, for fluid interface
     */
    public function filterByRefSymbol($refSymbol = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($refSymbol)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $refSymbol)) {
                $refSymbol = str_replace('*', '%', $refSymbol);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(WalletNavPeer::REF_SYMBOL, $refSymbol, $comparison);
    }

    /**
     * Filter the query on the ref_price column
     *
     * Example usage:
     * <code>
     * $query->filterByRefPrice(1234); // WHERE ref_price = 1234
     * $query->filterByRefPrice(array(12, 34)); // WHERE ref_price IN (12, 34)
     * $query->filterByRefPrice(array('min' => 12)); // WHERE ref_price >= 12
     * $query->filterByRefPrice(array('max' => 12)); // WHERE ref_price <= 12
     * </code>
     *
     * @param     mixed $refPrice The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return WalletNavQuery The current query, for fluid interface
     */
    public function filterByRefPrice($refPrice = null, $comparison = null)
    {
        if (is_array($refPrice)) {
            $useMinMax = false;
            if (isset($refPrice['min'])) {
                $this->addUsingAlias(WalletNavPeer::REF_PRICE, $refPrice['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($refPrice['max'])) {
                $this->addUsingAlias(WalletNavPeer::REF_PRICE, $refPrice['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(WalletNavPeer::REF_PRICE, $refPrice, $comparison);
    }

    /**
     * Filter the query on the unpriced column
     *
     * Example usage:
     * <code>
     * $query->filterByUnpriced('fooValue');   // WHERE unpriced = 'fooValue'
     * $query->filterByUnpriced('%fooValue%'); // WHERE unpriced LIKE '%fooValue%'
     * </code>
     *
     * @param     string $unpriced The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return WalletNavQuery The current query, for fluid interface
     */
    public function filterByUnpriced($unpriced = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($unpriced)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $unpriced)) {
                $unpriced = str_replace('*', '%', $unpriced);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(WalletNavPeer::UNPRICED, $unpriced, $comparison);
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
     * @return WalletNavQuery The current query, for fluid interface
     */
    public function filterByDateCreation($dateCreation = null, $comparison = null)
    {
        if (is_array($dateCreation)) {
            $useMinMax = false;
            if (isset($dateCreation['min'])) {
                $this->addUsingAlias(WalletNavPeer::DATE_CREATION, $dateCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateCreation['max'])) {
                $this->addUsingAlias(WalletNavPeer::DATE_CREATION, $dateCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(WalletNavPeer::DATE_CREATION, $dateCreation, $comparison);
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
     * @return WalletNavQuery The current query, for fluid interface
     */
    public function filterByDateModification($dateModification = null, $comparison = null)
    {
        if (is_array($dateModification)) {
            $useMinMax = false;
            if (isset($dateModification['min'])) {
                $this->addUsingAlias(WalletNavPeer::DATE_MODIFICATION, $dateModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateModification['max'])) {
                $this->addUsingAlias(WalletNavPeer::DATE_MODIFICATION, $dateModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(WalletNavPeer::DATE_MODIFICATION, $dateModification, $comparison);
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
     * @return WalletNavQuery The current query, for fluid interface
     */
    public function filterByIdGroupCreation($idGroupCreation = null, $comparison = null)
    {
        if (is_array($idGroupCreation)) {
            $useMinMax = false;
            if (isset($idGroupCreation['min'])) {
                $this->addUsingAlias(WalletNavPeer::ID_GROUP_CREATION, $idGroupCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGroupCreation['max'])) {
                $this->addUsingAlias(WalletNavPeer::ID_GROUP_CREATION, $idGroupCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(WalletNavPeer::ID_GROUP_CREATION, $idGroupCreation, $comparison);
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
     * @return WalletNavQuery The current query, for fluid interface
     */
    public function filterByIdCreation($idCreation = null, $comparison = null)
    {
        if (is_array($idCreation)) {
            $useMinMax = false;
            if (isset($idCreation['min'])) {
                $this->addUsingAlias(WalletNavPeer::ID_CREATION, $idCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idCreation['max'])) {
                $this->addUsingAlias(WalletNavPeer::ID_CREATION, $idCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(WalletNavPeer::ID_CREATION, $idCreation, $comparison);
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
     * @return WalletNavQuery The current query, for fluid interface
     */
    public function filterByIdModification($idModification = null, $comparison = null)
    {
        if (is_array($idModification)) {
            $useMinMax = false;
            if (isset($idModification['min'])) {
                $this->addUsingAlias(WalletNavPeer::ID_MODIFICATION, $idModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idModification['max'])) {
                $this->addUsingAlias(WalletNavPeer::ID_MODIFICATION, $idModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(WalletNavPeer::ID_MODIFICATION, $idModification, $comparison);
    }

    /**
     * Filter the query by a related AuthyGroup object
     *
     * @param   AuthyGroup|PropelObjectCollection $authyGroup The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 WalletNavQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroup($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(WalletNavPeer::ID_GROUP_CREATION, $authyGroup->getIdAuthyGroup(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(WalletNavPeer::ID_GROUP_CREATION, $authyGroup->toKeyValue('PrimaryKey', 'IdAuthyGroup'), $comparison);
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
     * @return WalletNavQuery The current query, for fluid interface
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
     * @return                 WalletNavQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdCreation($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(WalletNavPeer::ID_CREATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(WalletNavPeer::ID_CREATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return WalletNavQuery The current query, for fluid interface
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
     * @return                 WalletNavQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdModification($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(WalletNavPeer::ID_MODIFICATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(WalletNavPeer::ID_MODIFICATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return WalletNavQuery The current query, for fluid interface
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
     * @param   WalletNav $walletNav Object to remove from the list of results
     *
     * @return WalletNavQuery The current query, for fluid interface
     */
    public function prune($walletNav = null)
    {
        if ($walletNav) {
            $this->addUsingAlias(WalletNavPeer::ID_WALLET_NAV, $walletNav->getIdWalletNav(), Criteria::NOT_EQUAL);
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
                    \ApiGoat\Utility\TableVersion::bump('wallet_nav');
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
                    \ApiGoat\Utility\TableVersion::bump('wallet_nav');
                }

        return $this->postUpdate($affectedRows, $con);
    }

    // add_tablestamp behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     WalletNavQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7){
        return $this->addUsingAlias(WalletNavPeer::DATE_MODIFICATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     WalletNavQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst(){
        return $this->addDescendingOrderByColumn(WalletNavPeer::DATE_MODIFICATION);
    }

    /**
     * Order by update date asc
     *
     * @return     WalletNavQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst(){
        return $this->addAscendingOrderByColumn(WalletNavPeer::DATE_MODIFICATION);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     WalletNavQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7){
        return $this->addUsingAlias(WalletNavPeer::DATE_CREATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return     WalletNavQuery The current query, for fluid interface
     */
    public function lastCreatedFirst(){
        return $this->addDescendingOrderByColumn(WalletNavPeer::DATE_CREATION);
    }

    /**
     * Order by create date asc
     *
     * @return     WalletNavQuery The current query, for fluid interface
     */
    public function firstCreatedFirst(){
        return $this->addAscendingOrderByColumn(WalletNavPeer::DATE_CREATION);
    }
}
