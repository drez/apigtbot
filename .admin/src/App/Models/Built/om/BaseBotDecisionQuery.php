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
use App\BotDecision;
use App\BotDecisionPeer;
use App\BotDecisionQuery;
use App\GridRun;

/**
 * Base class that represents a query for the 'bot_decision' table.
 *
 * Refit Decision
 *
 * @method BotDecisionQuery orderByIdBotDecision($order = Criteria::ASC) Order by the id_bot_decision column
 * @method BotDecisionQuery orderByIdGridRun($order = Criteria::ASC) Order by the id_grid_run column
 * @method BotDecisionQuery orderBySource($order = Criteria::ASC) Order by the source column
 * @method BotDecisionQuery orderByPLow($order = Criteria::ASC) Order by the p_low column
 * @method BotDecisionQuery orderByPHigh($order = Criteria::ASC) Order by the p_high column
 * @method BotDecisionQuery orderByNLevels($order = Criteria::ASC) Order by the n_levels column
 * @method BotDecisionQuery orderByDeployPct($order = Criteria::ASC) Order by the deploy_pct column
 * @method BotDecisionQuery orderByReason($order = Criteria::ASC) Order by the reason column
 * @method BotDecisionQuery orderByPriceAt($order = Criteria::ASC) Order by the price_at column
 * @method BotDecisionQuery orderByRealizedBefore($order = Criteria::ASC) Order by the realized_before column
 * @method BotDecisionQuery orderByEvalStatus($order = Criteria::ASC) Order by the eval_status column
 * @method BotDecisionQuery orderByEvalAt($order = Criteria::ASC) Order by the eval_at column
 * @method BotDecisionQuery orderByAppliedAt($order = Criteria::ASC) Order by the applied_at column
 * @method BotDecisionQuery orderByCyclesDelta($order = Criteria::ASC) Order by the cycles_delta column
 * @method BotDecisionQuery orderByRealizedDelta($order = Criteria::ASC) Order by the realized_delta column
 * @method BotDecisionQuery orderByPriceMovePct($order = Criteria::ASC) Order by the price_move_pct column
 * @method BotDecisionQuery orderByVerdict($order = Criteria::ASC) Order by the verdict column
 * @method BotDecisionQuery orderByCounterfactualDelta($order = Criteria::ASC) Order by the counterfactual_delta column
 * @method BotDecisionQuery orderByCandidateDelta($order = Criteria::ASC) Order by the candidate_delta column
 * @method BotDecisionQuery orderByRequestedJson($order = Criteria::ASC) Order by the requested_json column
 * @method BotDecisionQuery orderByClampsJson($order = Criteria::ASC) Order by the clamps_json column
 * @method BotDecisionQuery orderByBriefJson($order = Criteria::ASC) Order by the brief_json column
 * @method BotDecisionQuery orderByDateCreation($order = Criteria::ASC) Order by the date_creation column
 * @method BotDecisionQuery orderByDateModification($order = Criteria::ASC) Order by the date_modification column
 * @method BotDecisionQuery orderByIdGroupCreation($order = Criteria::ASC) Order by the id_group_creation column
 * @method BotDecisionQuery orderByIdCreation($order = Criteria::ASC) Order by the id_creation column
 * @method BotDecisionQuery orderByIdModification($order = Criteria::ASC) Order by the id_modification column
 *
 * @method BotDecisionQuery groupByIdBotDecision() Group by the id_bot_decision column
 * @method BotDecisionQuery groupByIdGridRun() Group by the id_grid_run column
 * @method BotDecisionQuery groupBySource() Group by the source column
 * @method BotDecisionQuery groupByPLow() Group by the p_low column
 * @method BotDecisionQuery groupByPHigh() Group by the p_high column
 * @method BotDecisionQuery groupByNLevels() Group by the n_levels column
 * @method BotDecisionQuery groupByDeployPct() Group by the deploy_pct column
 * @method BotDecisionQuery groupByReason() Group by the reason column
 * @method BotDecisionQuery groupByPriceAt() Group by the price_at column
 * @method BotDecisionQuery groupByRealizedBefore() Group by the realized_before column
 * @method BotDecisionQuery groupByEvalStatus() Group by the eval_status column
 * @method BotDecisionQuery groupByEvalAt() Group by the eval_at column
 * @method BotDecisionQuery groupByAppliedAt() Group by the applied_at column
 * @method BotDecisionQuery groupByCyclesDelta() Group by the cycles_delta column
 * @method BotDecisionQuery groupByRealizedDelta() Group by the realized_delta column
 * @method BotDecisionQuery groupByPriceMovePct() Group by the price_move_pct column
 * @method BotDecisionQuery groupByVerdict() Group by the verdict column
 * @method BotDecisionQuery groupByCounterfactualDelta() Group by the counterfactual_delta column
 * @method BotDecisionQuery groupByCandidateDelta() Group by the candidate_delta column
 * @method BotDecisionQuery groupByRequestedJson() Group by the requested_json column
 * @method BotDecisionQuery groupByClampsJson() Group by the clamps_json column
 * @method BotDecisionQuery groupByBriefJson() Group by the brief_json column
 * @method BotDecisionQuery groupByDateCreation() Group by the date_creation column
 * @method BotDecisionQuery groupByDateModification() Group by the date_modification column
 * @method BotDecisionQuery groupByIdGroupCreation() Group by the id_group_creation column
 * @method BotDecisionQuery groupByIdCreation() Group by the id_creation column
 * @method BotDecisionQuery groupByIdModification() Group by the id_modification column
 *
 * @method BotDecisionQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method BotDecisionQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method BotDecisionQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method BotDecisionQuery leftJoinGridRun($relationAlias = null) Adds a LEFT JOIN clause to the query using the GridRun relation
 * @method BotDecisionQuery rightJoinGridRun($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GridRun relation
 * @method BotDecisionQuery innerJoinGridRun($relationAlias = null) Adds a INNER JOIN clause to the query using the GridRun relation
 *
 * @method BotDecisionQuery leftJoinAuthyGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroup relation
 * @method BotDecisionQuery rightJoinAuthyGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroup relation
 * @method BotDecisionQuery innerJoinAuthyGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroup relation
 *
 * @method BotDecisionQuery leftJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method BotDecisionQuery rightJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method BotDecisionQuery innerJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdCreation relation
 *
 * @method BotDecisionQuery leftJoinAuthyRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method BotDecisionQuery rightJoinAuthyRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method BotDecisionQuery innerJoinAuthyRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdModification relation
 *
 * @method BotDecision findOne(?PropelPDO $con = null) Return the first BotDecision matching the query
 * @method BotDecision findOneOrCreate(?PropelPDO $con = null) Return the first BotDecision matching the query, or a new BotDecision object populated from the query conditions when no match is found
 *
 * @method BotDecision findOneByIdGridRun(int $id_grid_run) Return the first BotDecision filtered by the id_grid_run column
 * @method BotDecision findOneBySource(int $source) Return the first BotDecision filtered by the source column
 * @method BotDecision findOneByPLow(string $p_low) Return the first BotDecision filtered by the p_low column
 * @method BotDecision findOneByPHigh(string $p_high) Return the first BotDecision filtered by the p_high column
 * @method BotDecision findOneByNLevels(int $n_levels) Return the first BotDecision filtered by the n_levels column
 * @method BotDecision findOneByDeployPct(int $deploy_pct) Return the first BotDecision filtered by the deploy_pct column
 * @method BotDecision findOneByReason(string $reason) Return the first BotDecision filtered by the reason column
 * @method BotDecision findOneByPriceAt(string $price_at) Return the first BotDecision filtered by the price_at column
 * @method BotDecision findOneByRealizedBefore(string $realized_before) Return the first BotDecision filtered by the realized_before column
 * @method BotDecision findOneByEvalStatus(int $eval_status) Return the first BotDecision filtered by the eval_status column
 * @method BotDecision findOneByEvalAt(string $eval_at) Return the first BotDecision filtered by the eval_at column
 * @method BotDecision findOneByAppliedAt(string $applied_at) Return the first BotDecision filtered by the applied_at column
 * @method BotDecision findOneByCyclesDelta(int $cycles_delta) Return the first BotDecision filtered by the cycles_delta column
 * @method BotDecision findOneByRealizedDelta(string $realized_delta) Return the first BotDecision filtered by the realized_delta column
 * @method BotDecision findOneByPriceMovePct(string $price_move_pct) Return the first BotDecision filtered by the price_move_pct column
 * @method BotDecision findOneByVerdict(int $verdict) Return the first BotDecision filtered by the verdict column
 * @method BotDecision findOneByCounterfactualDelta(string $counterfactual_delta) Return the first BotDecision filtered by the counterfactual_delta column
 * @method BotDecision findOneByCandidateDelta(int $candidate_delta) Return the first BotDecision filtered by the candidate_delta column
 * @method BotDecision findOneByRequestedJson(string $requested_json) Return the first BotDecision filtered by the requested_json column
 * @method BotDecision findOneByClampsJson(string $clamps_json) Return the first BotDecision filtered by the clamps_json column
 * @method BotDecision findOneByBriefJson(string $brief_json) Return the first BotDecision filtered by the brief_json column
 * @method BotDecision findOneByDateCreation(string $date_creation) Return the first BotDecision filtered by the date_creation column
 * @method BotDecision findOneByDateModification(string $date_modification) Return the first BotDecision filtered by the date_modification column
 * @method BotDecision findOneByIdGroupCreation(int $id_group_creation) Return the first BotDecision filtered by the id_group_creation column
 * @method BotDecision findOneByIdCreation(int $id_creation) Return the first BotDecision filtered by the id_creation column
 * @method BotDecision findOneByIdModification(int $id_modification) Return the first BotDecision filtered by the id_modification column
 *
 * @method array findByIdBotDecision(int $id_bot_decision) Return BotDecision objects filtered by the id_bot_decision column
 * @method array findByIdGridRun(int $id_grid_run) Return BotDecision objects filtered by the id_grid_run column
 * @method array findBySource(int $source) Return BotDecision objects filtered by the source column
 * @method array findByPLow(string $p_low) Return BotDecision objects filtered by the p_low column
 * @method array findByPHigh(string $p_high) Return BotDecision objects filtered by the p_high column
 * @method array findByNLevels(int $n_levels) Return BotDecision objects filtered by the n_levels column
 * @method array findByDeployPct(int $deploy_pct) Return BotDecision objects filtered by the deploy_pct column
 * @method array findByReason(string $reason) Return BotDecision objects filtered by the reason column
 * @method array findByPriceAt(string $price_at) Return BotDecision objects filtered by the price_at column
 * @method array findByRealizedBefore(string $realized_before) Return BotDecision objects filtered by the realized_before column
 * @method array findByEvalStatus(int $eval_status) Return BotDecision objects filtered by the eval_status column
 * @method array findByEvalAt(string $eval_at) Return BotDecision objects filtered by the eval_at column
 * @method array findByAppliedAt(string $applied_at) Return BotDecision objects filtered by the applied_at column
 * @method array findByCyclesDelta(int $cycles_delta) Return BotDecision objects filtered by the cycles_delta column
 * @method array findByRealizedDelta(string $realized_delta) Return BotDecision objects filtered by the realized_delta column
 * @method array findByPriceMovePct(string $price_move_pct) Return BotDecision objects filtered by the price_move_pct column
 * @method array findByVerdict(int $verdict) Return BotDecision objects filtered by the verdict column
 * @method array findByCounterfactualDelta(string $counterfactual_delta) Return BotDecision objects filtered by the counterfactual_delta column
 * @method array findByCandidateDelta(int $candidate_delta) Return BotDecision objects filtered by the candidate_delta column
 * @method array findByRequestedJson(string $requested_json) Return BotDecision objects filtered by the requested_json column
 * @method array findByClampsJson(string $clamps_json) Return BotDecision objects filtered by the clamps_json column
 * @method array findByBriefJson(string $brief_json) Return BotDecision objects filtered by the brief_json column
 * @method array findByDateCreation(string $date_creation) Return BotDecision objects filtered by the date_creation column
 * @method array findByDateModification(string $date_modification) Return BotDecision objects filtered by the date_modification column
 * @method array findByIdGroupCreation(int $id_group_creation) Return BotDecision objects filtered by the id_group_creation column
 * @method array findByIdCreation(int $id_creation) Return BotDecision objects filtered by the id_creation column
 * @method array findByIdModification(int $id_modification) Return BotDecision objects filtered by the id_modification column
 *
 * @package    propel.generator..om
 */
abstract class BaseBotDecisionQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseBotDecisionQuery object.
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
            $modelName = 'App\\BotDecision';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new BotDecisionQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   BotDecisionQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return BotDecisionQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof BotDecisionQuery) {
            return $criteria;
        }
        $query = new BotDecisionQuery(null, null, $modelAlias);

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
     * @return   BotDecision|BotDecision[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = BotDecisionPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 BotDecision A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdBotDecision($key, $con = null)
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
     * @return                 BotDecision A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_bot_decision`, `id_grid_run`, `source`, `p_low`, `p_high`, `n_levels`, `deploy_pct`, `reason`, `price_at`, `realized_before`, `eval_status`, `eval_at`, `applied_at`, `cycles_delta`, `realized_delta`, `price_move_pct`, `verdict`, `counterfactual_delta`, `candidate_delta`, `requested_json`, `clamps_json`, `brief_json`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification` FROM `bot_decision` WHERE `id_bot_decision` = :p0';
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
            $obj = new BotDecision();
            $obj->hydrate($row);
            BotDecisionPeer::addInstanceToPool($obj, (string) $key);
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
     * @return BotDecision|BotDecision[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|BotDecision[]|mixed the list of results, formatted by the current formatter
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
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(BotDecisionPeer::ID_BOT_DECISION, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(BotDecisionPeer::ID_BOT_DECISION, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id_bot_decision column
     *
     * Example usage:
     * <code>
     * $query->filterByIdBotDecision(1234); // WHERE id_bot_decision = 1234
     * $query->filterByIdBotDecision(array(12, 34)); // WHERE id_bot_decision IN (12, 34)
     * $query->filterByIdBotDecision(array('min' => 12)); // WHERE id_bot_decision >= 12
     * $query->filterByIdBotDecision(array('max' => 12)); // WHERE id_bot_decision <= 12
     * </code>
     *
     * @param     mixed $idBotDecision The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByIdBotDecision($idBotDecision = null, $comparison = null)
    {
        if (is_array($idBotDecision)) {
            $useMinMax = false;
            if (isset($idBotDecision['min'])) {
                $this->addUsingAlias(BotDecisionPeer::ID_BOT_DECISION, $idBotDecision['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idBotDecision['max'])) {
                $this->addUsingAlias(BotDecisionPeer::ID_BOT_DECISION, $idBotDecision['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::ID_BOT_DECISION, $idBotDecision, $comparison);
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
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByIdGridRun($idGridRun = null, $comparison = null)
    {
        if (is_array($idGridRun)) {
            $useMinMax = false;
            if (isset($idGridRun['min'])) {
                $this->addUsingAlias(BotDecisionPeer::ID_GRID_RUN, $idGridRun['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGridRun['max'])) {
                $this->addUsingAlias(BotDecisionPeer::ID_GRID_RUN, $idGridRun['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::ID_GRID_RUN, $idGridRun, $comparison);
    }

    /**
     * Filter the query on the source column
     *
     * @param     mixed $source The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterBySource($source = null, $comparison = null)
    {
        if (is_scalar($source)) {
            $source = BotDecisionPeer::getSqlValueForEnum(BotDecisionPeer::SOURCE, $source);
        } elseif (is_array($source)) {
            $convertedValues = array();
            foreach ($source as $value) {
                $convertedValues[] = BotDecisionPeer::getSqlValueForEnum(BotDecisionPeer::SOURCE, $value);
            }
            $source = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::SOURCE, $source, $comparison);
    }

    /**
     * Filter the query on the p_low column
     *
     * Example usage:
     * <code>
     * $query->filterByPLow(1234); // WHERE p_low = 1234
     * $query->filterByPLow(array(12, 34)); // WHERE p_low IN (12, 34)
     * $query->filterByPLow(array('min' => 12)); // WHERE p_low >= 12
     * $query->filterByPLow(array('max' => 12)); // WHERE p_low <= 12
     * </code>
     *
     * @param     mixed $pLow The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByPLow($pLow = null, $comparison = null)
    {
        if (is_array($pLow)) {
            $useMinMax = false;
            if (isset($pLow['min'])) {
                $this->addUsingAlias(BotDecisionPeer::P_LOW, $pLow['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($pLow['max'])) {
                $this->addUsingAlias(BotDecisionPeer::P_LOW, $pLow['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::P_LOW, $pLow, $comparison);
    }

    /**
     * Filter the query on the p_high column
     *
     * Example usage:
     * <code>
     * $query->filterByPHigh(1234); // WHERE p_high = 1234
     * $query->filterByPHigh(array(12, 34)); // WHERE p_high IN (12, 34)
     * $query->filterByPHigh(array('min' => 12)); // WHERE p_high >= 12
     * $query->filterByPHigh(array('max' => 12)); // WHERE p_high <= 12
     * </code>
     *
     * @param     mixed $pHigh The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByPHigh($pHigh = null, $comparison = null)
    {
        if (is_array($pHigh)) {
            $useMinMax = false;
            if (isset($pHigh['min'])) {
                $this->addUsingAlias(BotDecisionPeer::P_HIGH, $pHigh['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($pHigh['max'])) {
                $this->addUsingAlias(BotDecisionPeer::P_HIGH, $pHigh['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::P_HIGH, $pHigh, $comparison);
    }

    /**
     * Filter the query on the n_levels column
     *
     * Example usage:
     * <code>
     * $query->filterByNLevels(1234); // WHERE n_levels = 1234
     * $query->filterByNLevels(array(12, 34)); // WHERE n_levels IN (12, 34)
     * $query->filterByNLevels(array('min' => 12)); // WHERE n_levels >= 12
     * $query->filterByNLevels(array('max' => 12)); // WHERE n_levels <= 12
     * </code>
     *
     * @param     mixed $nLevels The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByNLevels($nLevels = null, $comparison = null)
    {
        if (is_array($nLevels)) {
            $useMinMax = false;
            if (isset($nLevels['min'])) {
                $this->addUsingAlias(BotDecisionPeer::N_LEVELS, $nLevels['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($nLevels['max'])) {
                $this->addUsingAlias(BotDecisionPeer::N_LEVELS, $nLevels['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::N_LEVELS, $nLevels, $comparison);
    }

    /**
     * Filter the query on the deploy_pct column
     *
     * Example usage:
     * <code>
     * $query->filterByDeployPct(1234); // WHERE deploy_pct = 1234
     * $query->filterByDeployPct(array(12, 34)); // WHERE deploy_pct IN (12, 34)
     * $query->filterByDeployPct(array('min' => 12)); // WHERE deploy_pct >= 12
     * $query->filterByDeployPct(array('max' => 12)); // WHERE deploy_pct <= 12
     * </code>
     *
     * @param     mixed $deployPct The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByDeployPct($deployPct = null, $comparison = null)
    {
        if (is_array($deployPct)) {
            $useMinMax = false;
            if (isset($deployPct['min'])) {
                $this->addUsingAlias(BotDecisionPeer::DEPLOY_PCT, $deployPct['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($deployPct['max'])) {
                $this->addUsingAlias(BotDecisionPeer::DEPLOY_PCT, $deployPct['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::DEPLOY_PCT, $deployPct, $comparison);
    }

    /**
     * Filter the query on the reason column
     *
     * Example usage:
     * <code>
     * $query->filterByReason('fooValue');   // WHERE reason = 'fooValue'
     * $query->filterByReason('%fooValue%'); // WHERE reason LIKE '%fooValue%'
     * </code>
     *
     * @param     string $reason The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByReason($reason = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($reason)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $reason)) {
                $reason = str_replace('*', '%', $reason);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::REASON, $reason, $comparison);
    }

    /**
     * Filter the query on the price_at column
     *
     * Example usage:
     * <code>
     * $query->filterByPriceAt(1234); // WHERE price_at = 1234
     * $query->filterByPriceAt(array(12, 34)); // WHERE price_at IN (12, 34)
     * $query->filterByPriceAt(array('min' => 12)); // WHERE price_at >= 12
     * $query->filterByPriceAt(array('max' => 12)); // WHERE price_at <= 12
     * </code>
     *
     * @param     mixed $priceAt The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByPriceAt($priceAt = null, $comparison = null)
    {
        if (is_array($priceAt)) {
            $useMinMax = false;
            if (isset($priceAt['min'])) {
                $this->addUsingAlias(BotDecisionPeer::PRICE_AT, $priceAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($priceAt['max'])) {
                $this->addUsingAlias(BotDecisionPeer::PRICE_AT, $priceAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::PRICE_AT, $priceAt, $comparison);
    }

    /**
     * Filter the query on the realized_before column
     *
     * Example usage:
     * <code>
     * $query->filterByRealizedBefore(1234); // WHERE realized_before = 1234
     * $query->filterByRealizedBefore(array(12, 34)); // WHERE realized_before IN (12, 34)
     * $query->filterByRealizedBefore(array('min' => 12)); // WHERE realized_before >= 12
     * $query->filterByRealizedBefore(array('max' => 12)); // WHERE realized_before <= 12
     * </code>
     *
     * @param     mixed $realizedBefore The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByRealizedBefore($realizedBefore = null, $comparison = null)
    {
        if (is_array($realizedBefore)) {
            $useMinMax = false;
            if (isset($realizedBefore['min'])) {
                $this->addUsingAlias(BotDecisionPeer::REALIZED_BEFORE, $realizedBefore['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($realizedBefore['max'])) {
                $this->addUsingAlias(BotDecisionPeer::REALIZED_BEFORE, $realizedBefore['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::REALIZED_BEFORE, $realizedBefore, $comparison);
    }

    /**
     * Filter the query on the eval_status column
     *
     * @param     mixed $evalStatus The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByEvalStatus($evalStatus = null, $comparison = null)
    {
        if (is_scalar($evalStatus)) {
            $evalStatus = BotDecisionPeer::getSqlValueForEnum(BotDecisionPeer::EVAL_STATUS, $evalStatus);
        } elseif (is_array($evalStatus)) {
            $convertedValues = array();
            foreach ($evalStatus as $value) {
                $convertedValues[] = BotDecisionPeer::getSqlValueForEnum(BotDecisionPeer::EVAL_STATUS, $value);
            }
            $evalStatus = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::EVAL_STATUS, $evalStatus, $comparison);
    }

    /**
     * Filter the query on the eval_at column
     *
     * Example usage:
     * <code>
     * $query->filterByEvalAt('2011-03-14'); // WHERE eval_at = '2011-03-14'
     * $query->filterByEvalAt('now'); // WHERE eval_at = '2011-03-14'
     * $query->filterByEvalAt(array('max' => 'yesterday')); // WHERE eval_at < '2011-03-13'
     * </code>
     *
     * @param     mixed $evalAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByEvalAt($evalAt = null, $comparison = null)
    {
        if (is_array($evalAt)) {
            $useMinMax = false;
            if (isset($evalAt['min'])) {
                $this->addUsingAlias(BotDecisionPeer::EVAL_AT, $evalAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($evalAt['max'])) {
                $this->addUsingAlias(BotDecisionPeer::EVAL_AT, $evalAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::EVAL_AT, $evalAt, $comparison);
    }

    /**
     * Filter the query on the applied_at column
     *
     * Example usage:
     * <code>
     * $query->filterByAppliedAt('2011-03-14'); // WHERE applied_at = '2011-03-14'
     * $query->filterByAppliedAt('now'); // WHERE applied_at = '2011-03-14'
     * $query->filterByAppliedAt(array('max' => 'yesterday')); // WHERE applied_at < '2011-03-13'
     * </code>
     *
     * @param     mixed $appliedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByAppliedAt($appliedAt = null, $comparison = null)
    {
        if (is_array($appliedAt)) {
            $useMinMax = false;
            if (isset($appliedAt['min'])) {
                $this->addUsingAlias(BotDecisionPeer::APPLIED_AT, $appliedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($appliedAt['max'])) {
                $this->addUsingAlias(BotDecisionPeer::APPLIED_AT, $appliedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::APPLIED_AT, $appliedAt, $comparison);
    }

    /**
     * Filter the query on the cycles_delta column
     *
     * Example usage:
     * <code>
     * $query->filterByCyclesDelta(1234); // WHERE cycles_delta = 1234
     * $query->filterByCyclesDelta(array(12, 34)); // WHERE cycles_delta IN (12, 34)
     * $query->filterByCyclesDelta(array('min' => 12)); // WHERE cycles_delta >= 12
     * $query->filterByCyclesDelta(array('max' => 12)); // WHERE cycles_delta <= 12
     * </code>
     *
     * @param     mixed $cyclesDelta The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByCyclesDelta($cyclesDelta = null, $comparison = null)
    {
        if (is_array($cyclesDelta)) {
            $useMinMax = false;
            if (isset($cyclesDelta['min'])) {
                $this->addUsingAlias(BotDecisionPeer::CYCLES_DELTA, $cyclesDelta['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($cyclesDelta['max'])) {
                $this->addUsingAlias(BotDecisionPeer::CYCLES_DELTA, $cyclesDelta['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::CYCLES_DELTA, $cyclesDelta, $comparison);
    }

    /**
     * Filter the query on the realized_delta column
     *
     * Example usage:
     * <code>
     * $query->filterByRealizedDelta(1234); // WHERE realized_delta = 1234
     * $query->filterByRealizedDelta(array(12, 34)); // WHERE realized_delta IN (12, 34)
     * $query->filterByRealizedDelta(array('min' => 12)); // WHERE realized_delta >= 12
     * $query->filterByRealizedDelta(array('max' => 12)); // WHERE realized_delta <= 12
     * </code>
     *
     * @param     mixed $realizedDelta The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByRealizedDelta($realizedDelta = null, $comparison = null)
    {
        if (is_array($realizedDelta)) {
            $useMinMax = false;
            if (isset($realizedDelta['min'])) {
                $this->addUsingAlias(BotDecisionPeer::REALIZED_DELTA, $realizedDelta['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($realizedDelta['max'])) {
                $this->addUsingAlias(BotDecisionPeer::REALIZED_DELTA, $realizedDelta['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::REALIZED_DELTA, $realizedDelta, $comparison);
    }

    /**
     * Filter the query on the price_move_pct column
     *
     * Example usage:
     * <code>
     * $query->filterByPriceMovePct(1234); // WHERE price_move_pct = 1234
     * $query->filterByPriceMovePct(array(12, 34)); // WHERE price_move_pct IN (12, 34)
     * $query->filterByPriceMovePct(array('min' => 12)); // WHERE price_move_pct >= 12
     * $query->filterByPriceMovePct(array('max' => 12)); // WHERE price_move_pct <= 12
     * </code>
     *
     * @param     mixed $priceMovePct The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByPriceMovePct($priceMovePct = null, $comparison = null)
    {
        if (is_array($priceMovePct)) {
            $useMinMax = false;
            if (isset($priceMovePct['min'])) {
                $this->addUsingAlias(BotDecisionPeer::PRICE_MOVE_PCT, $priceMovePct['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($priceMovePct['max'])) {
                $this->addUsingAlias(BotDecisionPeer::PRICE_MOVE_PCT, $priceMovePct['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::PRICE_MOVE_PCT, $priceMovePct, $comparison);
    }

    /**
     * Filter the query on the verdict column
     *
     * @param     mixed $verdict The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByVerdict($verdict = null, $comparison = null)
    {
        if (is_scalar($verdict)) {
            $verdict = BotDecisionPeer::getSqlValueForEnum(BotDecisionPeer::VERDICT, $verdict);
        } elseif (is_array($verdict)) {
            $convertedValues = array();
            foreach ($verdict as $value) {
                $convertedValues[] = BotDecisionPeer::getSqlValueForEnum(BotDecisionPeer::VERDICT, $value);
            }
            $verdict = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::VERDICT, $verdict, $comparison);
    }

    /**
     * Filter the query on the counterfactual_delta column
     *
     * Example usage:
     * <code>
     * $query->filterByCounterfactualDelta(1234); // WHERE counterfactual_delta = 1234
     * $query->filterByCounterfactualDelta(array(12, 34)); // WHERE counterfactual_delta IN (12, 34)
     * $query->filterByCounterfactualDelta(array('min' => 12)); // WHERE counterfactual_delta >= 12
     * $query->filterByCounterfactualDelta(array('max' => 12)); // WHERE counterfactual_delta <= 12
     * </code>
     *
     * @param     mixed $counterfactualDelta The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByCounterfactualDelta($counterfactualDelta = null, $comparison = null)
    {
        if (is_array($counterfactualDelta)) {
            $useMinMax = false;
            if (isset($counterfactualDelta['min'])) {
                $this->addUsingAlias(BotDecisionPeer::COUNTERFACTUAL_DELTA, $counterfactualDelta['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($counterfactualDelta['max'])) {
                $this->addUsingAlias(BotDecisionPeer::COUNTERFACTUAL_DELTA, $counterfactualDelta['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::COUNTERFACTUAL_DELTA, $counterfactualDelta, $comparison);
    }

    /**
     * Filter the query on the candidate_delta column
     *
     * @param     mixed $candidateDelta The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByCandidateDelta($candidateDelta = null, $comparison = null)
    {
        if (is_scalar($candidateDelta)) {
            $candidateDelta = BotDecisionPeer::getSqlValueForEnum(BotDecisionPeer::CANDIDATE_DELTA, $candidateDelta);
        } elseif (is_array($candidateDelta)) {
            $convertedValues = array();
            foreach ($candidateDelta as $value) {
                $convertedValues[] = BotDecisionPeer::getSqlValueForEnum(BotDecisionPeer::CANDIDATE_DELTA, $value);
            }
            $candidateDelta = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::CANDIDATE_DELTA, $candidateDelta, $comparison);
    }

    /**
     * Filter the query on the requested_json column
     *
     * Example usage:
     * <code>
     * $query->filterByRequestedJson('fooValue');   // WHERE requested_json = 'fooValue'
     * $query->filterByRequestedJson('%fooValue%'); // WHERE requested_json LIKE '%fooValue%'
     * </code>
     *
     * @param     string $requestedJson The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByRequestedJson($requestedJson = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($requestedJson)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $requestedJson)) {
                $requestedJson = str_replace('*', '%', $requestedJson);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::REQUESTED_JSON, $requestedJson, $comparison);
    }

    /**
     * Filter the query on the clamps_json column
     *
     * Example usage:
     * <code>
     * $query->filterByClampsJson('fooValue');   // WHERE clamps_json = 'fooValue'
     * $query->filterByClampsJson('%fooValue%'); // WHERE clamps_json LIKE '%fooValue%'
     * </code>
     *
     * @param     string $clampsJson The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByClampsJson($clampsJson = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($clampsJson)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $clampsJson)) {
                $clampsJson = str_replace('*', '%', $clampsJson);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::CLAMPS_JSON, $clampsJson, $comparison);
    }

    /**
     * Filter the query on the brief_json column
     *
     * Example usage:
     * <code>
     * $query->filterByBriefJson('fooValue');   // WHERE brief_json = 'fooValue'
     * $query->filterByBriefJson('%fooValue%'); // WHERE brief_json LIKE '%fooValue%'
     * </code>
     *
     * @param     string $briefJson The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByBriefJson($briefJson = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($briefJson)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $briefJson)) {
                $briefJson = str_replace('*', '%', $briefJson);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::BRIEF_JSON, $briefJson, $comparison);
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
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByDateCreation($dateCreation = null, $comparison = null)
    {
        if (is_array($dateCreation)) {
            $useMinMax = false;
            if (isset($dateCreation['min'])) {
                $this->addUsingAlias(BotDecisionPeer::DATE_CREATION, $dateCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateCreation['max'])) {
                $this->addUsingAlias(BotDecisionPeer::DATE_CREATION, $dateCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::DATE_CREATION, $dateCreation, $comparison);
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
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByDateModification($dateModification = null, $comparison = null)
    {
        if (is_array($dateModification)) {
            $useMinMax = false;
            if (isset($dateModification['min'])) {
                $this->addUsingAlias(BotDecisionPeer::DATE_MODIFICATION, $dateModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateModification['max'])) {
                $this->addUsingAlias(BotDecisionPeer::DATE_MODIFICATION, $dateModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::DATE_MODIFICATION, $dateModification, $comparison);
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
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByIdGroupCreation($idGroupCreation = null, $comparison = null)
    {
        if (is_array($idGroupCreation)) {
            $useMinMax = false;
            if (isset($idGroupCreation['min'])) {
                $this->addUsingAlias(BotDecisionPeer::ID_GROUP_CREATION, $idGroupCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGroupCreation['max'])) {
                $this->addUsingAlias(BotDecisionPeer::ID_GROUP_CREATION, $idGroupCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::ID_GROUP_CREATION, $idGroupCreation, $comparison);
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
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByIdCreation($idCreation = null, $comparison = null)
    {
        if (is_array($idCreation)) {
            $useMinMax = false;
            if (isset($idCreation['min'])) {
                $this->addUsingAlias(BotDecisionPeer::ID_CREATION, $idCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idCreation['max'])) {
                $this->addUsingAlias(BotDecisionPeer::ID_CREATION, $idCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::ID_CREATION, $idCreation, $comparison);
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
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function filterByIdModification($idModification = null, $comparison = null)
    {
        if (is_array($idModification)) {
            $useMinMax = false;
            if (isset($idModification['min'])) {
                $this->addUsingAlias(BotDecisionPeer::ID_MODIFICATION, $idModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idModification['max'])) {
                $this->addUsingAlias(BotDecisionPeer::ID_MODIFICATION, $idModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(BotDecisionPeer::ID_MODIFICATION, $idModification, $comparison);
    }

    /**
     * Filter the query by a related GridRun object
     *
     * @param   GridRun|PropelObjectCollection $gridRun The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 BotDecisionQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByGridRun($gridRun, $comparison = null)
    {
        if ($gridRun instanceof GridRun) {
            return $this
                ->addUsingAlias(BotDecisionPeer::ID_GRID_RUN, $gridRun->getIdGridRun(), $comparison);
        } elseif ($gridRun instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(BotDecisionPeer::ID_GRID_RUN, $gridRun->toKeyValue('PrimaryKey', 'IdGridRun'), $comparison);
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
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function joinGridRun($relationAlias = null, $joinType = Criteria::INNER_JOIN)
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
    public function useGridRunQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
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
     * @return                 BotDecisionQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroup($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(BotDecisionPeer::ID_GROUP_CREATION, $authyGroup->getIdAuthyGroup(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(BotDecisionPeer::ID_GROUP_CREATION, $authyGroup->toKeyValue('PrimaryKey', 'IdAuthyGroup'), $comparison);
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
     * @return BotDecisionQuery The current query, for fluid interface
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
     * @return                 BotDecisionQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdCreation($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(BotDecisionPeer::ID_CREATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(BotDecisionPeer::ID_CREATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return BotDecisionQuery The current query, for fluid interface
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
     * @return                 BotDecisionQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdModification($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(BotDecisionPeer::ID_MODIFICATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(BotDecisionPeer::ID_MODIFICATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return BotDecisionQuery The current query, for fluid interface
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
     * @param   BotDecision $botDecision Object to remove from the list of results
     *
     * @return BotDecisionQuery The current query, for fluid interface
     */
    public function prune($botDecision = null)
    {
        if ($botDecision) {
            $this->addUsingAlias(BotDecisionPeer::ID_BOT_DECISION, $botDecision->getIdBotDecision(), Criteria::NOT_EQUAL);
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
                    \ApiGoat\Utility\TableVersion::bump('bot_decision');
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
                    \ApiGoat\Utility\TableVersion::bump('bot_decision');
                }

        return $this->postUpdate($affectedRows, $con);
    }

    // add_tablestamp behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     BotDecisionQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7){
        return $this->addUsingAlias(BotDecisionPeer::DATE_MODIFICATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     BotDecisionQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst(){
        return $this->addDescendingOrderByColumn(BotDecisionPeer::DATE_MODIFICATION);
    }

    /**
     * Order by update date asc
     *
     * @return     BotDecisionQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst(){
        return $this->addAscendingOrderByColumn(BotDecisionPeer::DATE_MODIFICATION);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     BotDecisionQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7){
        return $this->addUsingAlias(BotDecisionPeer::DATE_CREATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return     BotDecisionQuery The current query, for fluid interface
     */
    public function lastCreatedFirst(){
        return $this->addDescendingOrderByColumn(BotDecisionPeer::DATE_CREATION);
    }

    /**
     * Order by create date asc
     *
     * @return     BotDecisionQuery The current query, for fluid interface
     */
    public function firstCreatedFirst(){
        return $this->addAscendingOrderByColumn(BotDecisionPeer::DATE_CREATION);
    }
}
