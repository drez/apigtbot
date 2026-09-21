<?php

namespace App\om;

use \BasePeer;
use \Criteria;
use \PDO;
use \PDOStatement;
use \Propel;
use \PropelException;
use \PropelPDO;
use App\AuthyGroupPeer;
use App\AuthyPeer;
use App\BotDecision;
use App\BotDecisionPeer;
use App\GridRunPeer;
use App\map\BotDecisionTableMap;

/**
 * Base static class for performing query and update operations on the 'bot_decision' table.
 *
 * Refit Decision
 *
 * @package propel.generator..om
 */
abstract class BaseBotDecisionPeer
{

    /** the default database name for this class */
    const DATABASE_NAME = 'apigtbot';

    /** the table name for this class */
    const TABLE_NAME = 'bot_decision';

    /** the related Propel class for this table */
    const OM_CLASS = 'App\\BotDecision';

    /** the related TableMap class for this table */
    const TM_CLASS = 'App\\map\\BotDecisionTableMap';

    /** The total number of columns. */
    const NUM_COLUMNS = 27;

    /** The number of lazy-loaded columns. */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /** The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS) */
    const NUM_HYDRATE_COLUMNS = 27;

    /** the column name for the id_bot_decision field */
    const ID_BOT_DECISION = 'bot_decision.id_bot_decision';

    /** the column name for the id_grid_run field */
    const ID_GRID_RUN = 'bot_decision.id_grid_run';

    /** the column name for the source field */
    const SOURCE = 'bot_decision.source';

    /** the column name for the p_low field */
    const P_LOW = 'bot_decision.p_low';

    /** the column name for the p_high field */
    const P_HIGH = 'bot_decision.p_high';

    /** the column name for the n_levels field */
    const N_LEVELS = 'bot_decision.n_levels';

    /** the column name for the deploy_pct field */
    const DEPLOY_PCT = 'bot_decision.deploy_pct';

    /** the column name for the reason field */
    const REASON = 'bot_decision.reason';

    /** the column name for the price_at field */
    const PRICE_AT = 'bot_decision.price_at';

    /** the column name for the realized_before field */
    const REALIZED_BEFORE = 'bot_decision.realized_before';

    /** the column name for the eval_status field */
    const EVAL_STATUS = 'bot_decision.eval_status';

    /** the column name for the eval_at field */
    const EVAL_AT = 'bot_decision.eval_at';

    /** the column name for the applied_at field */
    const APPLIED_AT = 'bot_decision.applied_at';

    /** the column name for the cycles_delta field */
    const CYCLES_DELTA = 'bot_decision.cycles_delta';

    /** the column name for the realized_delta field */
    const REALIZED_DELTA = 'bot_decision.realized_delta';

    /** the column name for the price_move_pct field */
    const PRICE_MOVE_PCT = 'bot_decision.price_move_pct';

    /** the column name for the verdict field */
    const VERDICT = 'bot_decision.verdict';

    /** the column name for the counterfactual_delta field */
    const COUNTERFACTUAL_DELTA = 'bot_decision.counterfactual_delta';

    /** the column name for the candidate_delta field */
    const CANDIDATE_DELTA = 'bot_decision.candidate_delta';

    /** the column name for the requested_json field */
    const REQUESTED_JSON = 'bot_decision.requested_json';

    /** the column name for the clamps_json field */
    const CLAMPS_JSON = 'bot_decision.clamps_json';

    /** the column name for the brief_json field */
    const BRIEF_JSON = 'bot_decision.brief_json';

    /** the column name for the date_creation field */
    const DATE_CREATION = 'bot_decision.date_creation';

    /** the column name for the date_modification field */
    const DATE_MODIFICATION = 'bot_decision.date_modification';

    /** the column name for the id_group_creation field */
    const ID_GROUP_CREATION = 'bot_decision.id_group_creation';

    /** the column name for the id_creation field */
    const ID_CREATION = 'bot_decision.id_creation';

    /** the column name for the id_modification field */
    const ID_MODIFICATION = 'bot_decision.id_modification';

    /** The enumerated values for the source field */
    const SOURCE_CLAUDE = 'Claude';
    const SOURCE_CRON = 'Cron';
    const SOURCE_MANUAL = 'Manual';

    /** The enumerated values for the eval_status field */
    const EVAL_STATUS_PENDING = 'Pending';
    const EVAL_STATUS_SCORED = 'Scored';

    /** The enumerated values for the verdict field */
    const VERDICT_WIN = 'Win';
    const VERDICT_FLAT = 'Flat';
    const VERDICT_LOSS = 'Loss';
    const VERDICT_SUPERSEDED = 'Superseded';
    const VERDICT_WORSE = 'Worse';

    /** The enumerated values for the candidate_delta field */
    const CANDIDATE_DELTA_SAME = 'same';
    const CANDIDATE_DELTA_DEVIATED = 'deviated';
    const CANDIDATE_DELTA_NONE = 'none';

    /** The default string format for model objects of the related table **/
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * An identity map to hold any loaded instances of BotDecision objects.
     * This must be public so that other peer classes can access this when hydrating from JOIN
     * queries.
     * @var        array BotDecision[]
     */
    public static $instances = array();


    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. BotDecisionPeer::$fieldNames[BotDecisionPeer::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        BasePeer::TYPE_PHPNAME => array ('IdBotDecision', 'IdGridRun', 'Source', 'PLow', 'PHigh', 'NLevels', 'DeployPct', 'Reason', 'PriceAt', 'RealizedBefore', 'EvalStatus', 'EvalAt', 'AppliedAt', 'CyclesDelta', 'RealizedDelta', 'PriceMovePct', 'Verdict', 'CounterfactualDelta', 'CandidateDelta', 'RequestedJson', 'ClampsJson', 'BriefJson', 'DateCreation', 'DateModification', 'IdGroupCreation', 'IdCreation', 'IdModification', ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('idBotDecision', 'idGridRun', 'source', 'pLow', 'pHigh', 'nLevels', 'deployPct', 'reason', 'priceAt', 'realizedBefore', 'evalStatus', 'evalAt', 'appliedAt', 'cyclesDelta', 'realizedDelta', 'priceMovePct', 'verdict', 'counterfactualDelta', 'candidateDelta', 'requestedJson', 'clampsJson', 'briefJson', 'dateCreation', 'dateModification', 'idGroupCreation', 'idCreation', 'idModification', ),
        BasePeer::TYPE_COLNAME => array (BotDecisionPeer::ID_BOT_DECISION, BotDecisionPeer::ID_GRID_RUN, BotDecisionPeer::SOURCE, BotDecisionPeer::P_LOW, BotDecisionPeer::P_HIGH, BotDecisionPeer::N_LEVELS, BotDecisionPeer::DEPLOY_PCT, BotDecisionPeer::REASON, BotDecisionPeer::PRICE_AT, BotDecisionPeer::REALIZED_BEFORE, BotDecisionPeer::EVAL_STATUS, BotDecisionPeer::EVAL_AT, BotDecisionPeer::APPLIED_AT, BotDecisionPeer::CYCLES_DELTA, BotDecisionPeer::REALIZED_DELTA, BotDecisionPeer::PRICE_MOVE_PCT, BotDecisionPeer::VERDICT, BotDecisionPeer::COUNTERFACTUAL_DELTA, BotDecisionPeer::CANDIDATE_DELTA, BotDecisionPeer::REQUESTED_JSON, BotDecisionPeer::CLAMPS_JSON, BotDecisionPeer::BRIEF_JSON, BotDecisionPeer::DATE_CREATION, BotDecisionPeer::DATE_MODIFICATION, BotDecisionPeer::ID_GROUP_CREATION, BotDecisionPeer::ID_CREATION, BotDecisionPeer::ID_MODIFICATION, ),
        BasePeer::TYPE_RAW_COLNAME => array ('ID_BOT_DECISION', 'ID_GRID_RUN', 'SOURCE', 'P_LOW', 'P_HIGH', 'N_LEVELS', 'DEPLOY_PCT', 'REASON', 'PRICE_AT', 'REALIZED_BEFORE', 'EVAL_STATUS', 'EVAL_AT', 'APPLIED_AT', 'CYCLES_DELTA', 'REALIZED_DELTA', 'PRICE_MOVE_PCT', 'VERDICT', 'COUNTERFACTUAL_DELTA', 'CANDIDATE_DELTA', 'REQUESTED_JSON', 'CLAMPS_JSON', 'BRIEF_JSON', 'DATE_CREATION', 'DATE_MODIFICATION', 'ID_GROUP_CREATION', 'ID_CREATION', 'ID_MODIFICATION', ),
        BasePeer::TYPE_FIELDNAME => array ('id_bot_decision', 'id_grid_run', 'source', 'p_low', 'p_high', 'n_levels', 'deploy_pct', 'reason', 'price_at', 'realized_before', 'eval_status', 'eval_at', 'applied_at', 'cycles_delta', 'realized_delta', 'price_move_pct', 'verdict', 'counterfactual_delta', 'candidate_delta', 'requested_json', 'clamps_json', 'brief_json', 'date_creation', 'date_modification', 'id_group_creation', 'id_creation', 'id_modification', ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. BotDecisionPeer::$fieldNames[BasePeer::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        BasePeer::TYPE_PHPNAME => array ('IdBotDecision' => 0, 'IdGridRun' => 1, 'Source' => 2, 'PLow' => 3, 'PHigh' => 4, 'NLevels' => 5, 'DeployPct' => 6, 'Reason' => 7, 'PriceAt' => 8, 'RealizedBefore' => 9, 'EvalStatus' => 10, 'EvalAt' => 11, 'AppliedAt' => 12, 'CyclesDelta' => 13, 'RealizedDelta' => 14, 'PriceMovePct' => 15, 'Verdict' => 16, 'CounterfactualDelta' => 17, 'CandidateDelta' => 18, 'RequestedJson' => 19, 'ClampsJson' => 20, 'BriefJson' => 21, 'DateCreation' => 22, 'DateModification' => 23, 'IdGroupCreation' => 24, 'IdCreation' => 25, 'IdModification' => 26, ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('idBotDecision' => 0, 'idGridRun' => 1, 'source' => 2, 'pLow' => 3, 'pHigh' => 4, 'nLevels' => 5, 'deployPct' => 6, 'reason' => 7, 'priceAt' => 8, 'realizedBefore' => 9, 'evalStatus' => 10, 'evalAt' => 11, 'appliedAt' => 12, 'cyclesDelta' => 13, 'realizedDelta' => 14, 'priceMovePct' => 15, 'verdict' => 16, 'counterfactualDelta' => 17, 'candidateDelta' => 18, 'requestedJson' => 19, 'clampsJson' => 20, 'briefJson' => 21, 'dateCreation' => 22, 'dateModification' => 23, 'idGroupCreation' => 24, 'idCreation' => 25, 'idModification' => 26, ),
        BasePeer::TYPE_COLNAME => array (BotDecisionPeer::ID_BOT_DECISION => 0, BotDecisionPeer::ID_GRID_RUN => 1, BotDecisionPeer::SOURCE => 2, BotDecisionPeer::P_LOW => 3, BotDecisionPeer::P_HIGH => 4, BotDecisionPeer::N_LEVELS => 5, BotDecisionPeer::DEPLOY_PCT => 6, BotDecisionPeer::REASON => 7, BotDecisionPeer::PRICE_AT => 8, BotDecisionPeer::REALIZED_BEFORE => 9, BotDecisionPeer::EVAL_STATUS => 10, BotDecisionPeer::EVAL_AT => 11, BotDecisionPeer::APPLIED_AT => 12, BotDecisionPeer::CYCLES_DELTA => 13, BotDecisionPeer::REALIZED_DELTA => 14, BotDecisionPeer::PRICE_MOVE_PCT => 15, BotDecisionPeer::VERDICT => 16, BotDecisionPeer::COUNTERFACTUAL_DELTA => 17, BotDecisionPeer::CANDIDATE_DELTA => 18, BotDecisionPeer::REQUESTED_JSON => 19, BotDecisionPeer::CLAMPS_JSON => 20, BotDecisionPeer::BRIEF_JSON => 21, BotDecisionPeer::DATE_CREATION => 22, BotDecisionPeer::DATE_MODIFICATION => 23, BotDecisionPeer::ID_GROUP_CREATION => 24, BotDecisionPeer::ID_CREATION => 25, BotDecisionPeer::ID_MODIFICATION => 26, ),
        BasePeer::TYPE_RAW_COLNAME => array ('ID_BOT_DECISION' => 0, 'ID_GRID_RUN' => 1, 'SOURCE' => 2, 'P_LOW' => 3, 'P_HIGH' => 4, 'N_LEVELS' => 5, 'DEPLOY_PCT' => 6, 'REASON' => 7, 'PRICE_AT' => 8, 'REALIZED_BEFORE' => 9, 'EVAL_STATUS' => 10, 'EVAL_AT' => 11, 'APPLIED_AT' => 12, 'CYCLES_DELTA' => 13, 'REALIZED_DELTA' => 14, 'PRICE_MOVE_PCT' => 15, 'VERDICT' => 16, 'COUNTERFACTUAL_DELTA' => 17, 'CANDIDATE_DELTA' => 18, 'REQUESTED_JSON' => 19, 'CLAMPS_JSON' => 20, 'BRIEF_JSON' => 21, 'DATE_CREATION' => 22, 'DATE_MODIFICATION' => 23, 'ID_GROUP_CREATION' => 24, 'ID_CREATION' => 25, 'ID_MODIFICATION' => 26, ),
        BasePeer::TYPE_FIELDNAME => array ('id_bot_decision' => 0, 'id_grid_run' => 1, 'source' => 2, 'p_low' => 3, 'p_high' => 4, 'n_levels' => 5, 'deploy_pct' => 6, 'reason' => 7, 'price_at' => 8, 'realized_before' => 9, 'eval_status' => 10, 'eval_at' => 11, 'applied_at' => 12, 'cycles_delta' => 13, 'realized_delta' => 14, 'price_move_pct' => 15, 'verdict' => 16, 'counterfactual_delta' => 17, 'candidate_delta' => 18, 'requested_json' => 19, 'clamps_json' => 20, 'brief_json' => 21, 'date_creation' => 22, 'date_modification' => 23, 'id_group_creation' => 24, 'id_creation' => 25, 'id_modification' => 26, ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, )
    );

    /** The enumerated values for this table */
    protected static $enumValueSets = array(
        BotDecisionPeer::SOURCE => array(
            BotDecisionPeer::SOURCE_CLAUDE,
            BotDecisionPeer::SOURCE_CRON,
            BotDecisionPeer::SOURCE_MANUAL,
        ),
        BotDecisionPeer::EVAL_STATUS => array(
            BotDecisionPeer::EVAL_STATUS_PENDING,
            BotDecisionPeer::EVAL_STATUS_SCORED,
        ),
        BotDecisionPeer::VERDICT => array(
            BotDecisionPeer::VERDICT_WIN,
            BotDecisionPeer::VERDICT_FLAT,
            BotDecisionPeer::VERDICT_LOSS,
            BotDecisionPeer::VERDICT_SUPERSEDED,
            BotDecisionPeer::VERDICT_WORSE,
        ),
        BotDecisionPeer::CANDIDATE_DELTA => array(
            BotDecisionPeer::CANDIDATE_DELTA_SAME,
            BotDecisionPeer::CANDIDATE_DELTA_DEVIATED,
            BotDecisionPeer::CANDIDATE_DELTA_NONE,
        ),
    );

    /**
     * Translates a fieldname to another type
     *
     * @param      string $name field name
     * @param      string $fromType One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *                         BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
     * @param      string $toType   One of the class type constants
     * @return string          translated name of the field.
     * @throws PropelException - if the specified name could not be found in the fieldname mappings.
     */
    public static function translateFieldName($name, $fromType, $toType)
    {
        $toNames = BotDecisionPeer::getFieldNames($toType);
        $key = isset(BotDecisionPeer::$fieldKeys[$fromType][$name]) ? BotDecisionPeer::$fieldKeys[$fromType][$name] : null;
        if ($key === null) {
            throw new PropelException("'$name' could not be found in the field names of type '$fromType'. These are: " . print_r(BotDecisionPeer::$fieldKeys[$fromType], true));
        }

        return $toNames[$key];
    }

    /**
     * Returns an array of field names.
     *
     * @param      string $type The type of fieldnames to return:
     *                      One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *                      BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
     * @return array           A list of field names
     * @throws PropelException - if the type is not valid.
     */
    public static function getFieldNames($type = BasePeer::TYPE_PHPNAME)
    {
        if (!array_key_exists($type, BotDecisionPeer::$fieldNames)) {
            throw new PropelException('Method getFieldNames() expects the parameter $type to be one of the class constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME, BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM. ' . $type . ' was given.');
        }

        return BotDecisionPeer::$fieldNames[$type];
    }

    /**
     * Gets the list of values for all ENUM columns
     * @return array
     */
    public static function getValueSets()
    {
      return BotDecisionPeer::$enumValueSets;
    }

    /**
     * Gets the list of values for an ENUM column
     *
     * @param string $colname The ENUM column name.
     *
     * @return array list of possible values for the column
     */
    public static function getValueSet($colname)
    {
        $valueSets = BotDecisionPeer::getValueSets();

        if (!isset($valueSets[$colname])) {
            throw new PropelException(sprintf('Column "%s" has no ValueSet.', $colname));
        }

        return $valueSets[$colname];
    }

    /**
     * Gets the SQL value for the ENUM column value
     *
     * @param string $colname ENUM column name.
     * @param string $enumVal ENUM value.
     *
     * @return int SQL value
     */
    public static function getSqlValueForEnum($colname, $enumVal)
    {
        $values = BotDecisionPeer::getValueSet($colname);
        if (!in_array($enumVal, $values)) {
            throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $colname));
        }

        return array_search($enumVal, $values);
    }

    /**
     * Convenience method which changes table.column to alias.column.
     *
     * Using this method you can maintain SQL abstraction while using column aliases.
     * <code>
     *		$c->addAlias("alias1", TablePeer::TABLE_NAME);
     *		$c->addJoin(TablePeer::alias("alias1", TablePeer::PRIMARY_KEY_COLUMN), TablePeer::PRIMARY_KEY_COLUMN);
     * </code>
     * @param      string $alias The alias for the current table.
     * @param      string $column The column name for current table. (i.e. BotDecisionPeer::COLUMN_NAME).
     * @return string
     */
    public static function alias($alias, $column)
    {
        return str_replace(BotDecisionPeer::TABLE_NAME.'.', $alias.'.', $column);
    }

    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param      Criteria $criteria object containing the columns to add.
     * @param      string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(BotDecisionPeer::ID_BOT_DECISION);
            $criteria->addSelectColumn(BotDecisionPeer::ID_GRID_RUN);
            $criteria->addSelectColumn(BotDecisionPeer::SOURCE);
            $criteria->addSelectColumn(BotDecisionPeer::P_LOW);
            $criteria->addSelectColumn(BotDecisionPeer::P_HIGH);
            $criteria->addSelectColumn(BotDecisionPeer::N_LEVELS);
            $criteria->addSelectColumn(BotDecisionPeer::DEPLOY_PCT);
            $criteria->addSelectColumn(BotDecisionPeer::REASON);
            $criteria->addSelectColumn(BotDecisionPeer::PRICE_AT);
            $criteria->addSelectColumn(BotDecisionPeer::REALIZED_BEFORE);
            $criteria->addSelectColumn(BotDecisionPeer::EVAL_STATUS);
            $criteria->addSelectColumn(BotDecisionPeer::EVAL_AT);
            $criteria->addSelectColumn(BotDecisionPeer::APPLIED_AT);
            $criteria->addSelectColumn(BotDecisionPeer::CYCLES_DELTA);
            $criteria->addSelectColumn(BotDecisionPeer::REALIZED_DELTA);
            $criteria->addSelectColumn(BotDecisionPeer::PRICE_MOVE_PCT);
            $criteria->addSelectColumn(BotDecisionPeer::VERDICT);
            $criteria->addSelectColumn(BotDecisionPeer::COUNTERFACTUAL_DELTA);
            $criteria->addSelectColumn(BotDecisionPeer::CANDIDATE_DELTA);
            $criteria->addSelectColumn(BotDecisionPeer::REQUESTED_JSON);
            $criteria->addSelectColumn(BotDecisionPeer::CLAMPS_JSON);
            $criteria->addSelectColumn(BotDecisionPeer::BRIEF_JSON);
            $criteria->addSelectColumn(BotDecisionPeer::DATE_CREATION);
            $criteria->addSelectColumn(BotDecisionPeer::DATE_MODIFICATION);
            $criteria->addSelectColumn(BotDecisionPeer::ID_GROUP_CREATION);
            $criteria->addSelectColumn(BotDecisionPeer::ID_CREATION);
            $criteria->addSelectColumn(BotDecisionPeer::ID_MODIFICATION);
        } else {
            $criteria->addSelectColumn($alias . '.id_bot_decision');
            $criteria->addSelectColumn($alias . '.id_grid_run');
            $criteria->addSelectColumn($alias . '.source');
            $criteria->addSelectColumn($alias . '.p_low');
            $criteria->addSelectColumn($alias . '.p_high');
            $criteria->addSelectColumn($alias . '.n_levels');
            $criteria->addSelectColumn($alias . '.deploy_pct');
            $criteria->addSelectColumn($alias . '.reason');
            $criteria->addSelectColumn($alias . '.price_at');
            $criteria->addSelectColumn($alias . '.realized_before');
            $criteria->addSelectColumn($alias . '.eval_status');
            $criteria->addSelectColumn($alias . '.eval_at');
            $criteria->addSelectColumn($alias . '.applied_at');
            $criteria->addSelectColumn($alias . '.cycles_delta');
            $criteria->addSelectColumn($alias . '.realized_delta');
            $criteria->addSelectColumn($alias . '.price_move_pct');
            $criteria->addSelectColumn($alias . '.verdict');
            $criteria->addSelectColumn($alias . '.counterfactual_delta');
            $criteria->addSelectColumn($alias . '.candidate_delta');
            $criteria->addSelectColumn($alias . '.requested_json');
            $criteria->addSelectColumn($alias . '.clamps_json');
            $criteria->addSelectColumn($alias . '.brief_json');
            $criteria->addSelectColumn($alias . '.date_creation');
            $criteria->addSelectColumn($alias . '.date_modification');
            $criteria->addSelectColumn($alias . '.id_group_creation');
            $criteria->addSelectColumn($alias . '.id_creation');
            $criteria->addSelectColumn($alias . '.id_modification');
        }
    }

    /**
     * Returns the number of rows matching criteria.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @return int Number of matching rows.
     */
    public static function doCount(Criteria $criteria, $distinct = false, ?PropelPDO $con = null)
    {
        // we may modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(BotDecisionPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotDecisionPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count
        $criteria->setDbName(BotDecisionPeer::DATABASE_NAME); // Set the correct dbName

        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }
        // BasePeer returns a PDOStatement
        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }
    /**
     * Selects one object from the DB.
     *
     * @param      Criteria $criteria object used to create the SELECT statement.
     * @param      PropelPDO $con
     * @return BotDecision
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectOne(Criteria $criteria, ?PropelPDO $con = null)
    {
        $critcopy = clone $criteria;
        $critcopy->setLimit(1);
        $objects = BotDecisionPeer::doSelect($critcopy, $con);
        if ($objects) {
            return $objects[0];
        }

        return null;
    }
    /**
     * Selects several row from the DB.
     *
     * @param      Criteria $criteria The Criteria object used to build the SELECT statement.
     * @param      PropelPDO $con
     * @return array           Array of selected Objects
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelect(Criteria $criteria, ?PropelPDO $con = null)
    {
        return BotDecisionPeer::populateObjects(BotDecisionPeer::doSelectStmt($criteria, $con));
    }
    /**
     * Prepares the Criteria object and uses the parent doSelect() method to execute a PDOStatement.
     *
     * Use this method directly if you want to work with an executed statement directly (for example
     * to perform your own object hydration).
     *
     * @param      Criteria $criteria The Criteria object used to build the SELECT statement.
     * @param      PropelPDO $con The connection to use
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     * @return PDOStatement The executed PDOStatement object.
     * @see        BasePeer::doSelect()
     */
    public static function doSelectStmt(Criteria $criteria, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        if (!$criteria->hasSelectClause()) {
            $criteria = clone $criteria;
            BotDecisionPeer::addSelectColumns($criteria);
        }

        // Set the correct dbName
        $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);

        // BasePeer returns a PDOStatement
        return BasePeer::doSelect($criteria, $con);
    }
    /**
     * Adds an object to the instance pool.
     *
     * Propel keeps cached copies of objects in an instance pool when they are retrieved
     * from the database.  In some cases -- especially when you override doSelect*()
     * methods in your stub classes -- you may need to explicitly add objects
     * to the cache in order to ensure that the same objects are always returned by doSelect*()
     * and retrieveByPK*() calls.
     *
     * @param BotDecision $obj A BotDecision object.
     * @param      string $key (optional) key to use for instance map (for performance boost if key was already calculated externally).
     */
    public static function addInstanceToPool($obj, $key = null)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if ($key === null) {
                $key = (string) $obj->getIdBotDecision();
            } // if key === null
            BotDecisionPeer::$instances[$key] = $obj;
        }
    }

    /**
     * Removes an object from the instance pool.
     *
     * Propel keeps cached copies of objects in an instance pool when they are retrieved
     * from the database.  In some cases -- especially when you override doDelete
     * methods in your stub classes -- you may need to explicitly remove objects
     * from the cache in order to prevent returning objects that no longer exist.
     *
     * @param      mixed $value A BotDecision object or a primary key value.
     *
     * @return void
     * @throws PropelException - if the value is invalid.
     */
    public static function removeInstanceFromPool($value)
    {
        if (Propel::isInstancePoolingEnabled() && $value !== null) {
            if (is_object($value) && $value instanceof BotDecision) {
                $key = (string) $value->getIdBotDecision();
            } elseif (is_scalar($value)) {
                // assume we've been passed a primary key
                $key = (string) $value;
            } else {
                $e = new PropelException("Invalid value passed to removeInstanceFromPool().  Expected primary key or BotDecision object; got " . (is_object($value) ? get_class($value) . ' object.' : var_export($value,true)));
                throw $e;
            }

            unset(BotDecisionPeer::$instances[$key]);
        }
    } // removeInstanceFromPool()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param      string $key The key (@see getPrimaryKeyHash()) for this instance.
     * @return BotDecision Found object or null if 1) no instance exists for specified key or 2) instance pooling has been disabled.
     * @see        getPrimaryKeyHash()
     */
    public static function getInstanceFromPool($key)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if (isset(BotDecisionPeer::$instances[$key])) {
                return BotDecisionPeer::$instances[$key];
            }
        }

        return null; // just to be explicit
    }

    /**
     * Clear the instance pool.
     *
     * @return void
     */
    public static function clearInstancePool($and_clear_all_references = false)
    {
      if ($and_clear_all_references) {
        foreach (BotDecisionPeer::$instances as $instance) {
          $instance->clearAllReferences(true);
        }
      }
        BotDecisionPeer::$instances = array();
    }

    /**
     * Method to invalidate the instance pool of all tables related to bot_decision
     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool()
    {
    }

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param      array $row PropelPDO resultset row.
     * @param      int $startcol The 0-based offset for reading from the resultset row.
     * @return string A string version of PK or null if the components of primary key in result array are all null.
     */
    public static function getPrimaryKeyHashFromRow($row, $startcol = 0)
    {
        // If the PK cannot be derived from the row, return null.
        if ($row[$startcol] === null) {
            return null;
        }

        return (string) $row[$startcol];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param      array $row PropelPDO resultset row.
     * @param      int $startcol The 0-based offset for reading from the resultset row.
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $startcol = 0)
    {

        return (int) $row[$startcol];
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function populateObjects(PDOStatement $stmt)
    {
        $results = array();

        // set the class once to avoid overhead in the loop
        $cls = BotDecisionPeer::getOMClass();
        // populate the object(s)
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key = BotDecisionPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj = BotDecisionPeer::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                BotDecisionPeer::addInstanceToPool($obj, $key);
            } // if key exists
        }
        $stmt->closeCursor();

        return $results;
    }
    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param      array $row PropelPDO resultset row.
     * @param      int $startcol The 0-based offset for reading from the resultset row.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     * @return array (BotDecision object, last column rank)
     */
    public static function populateObject($row, $startcol = 0)
    {
        $key = BotDecisionPeer::getPrimaryKeyHashFromRow($row, $startcol);
        if (null !== ($obj = BotDecisionPeer::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $startcol, true); // rehydrate
            $col = $startcol + BotDecisionPeer::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = BotDecisionPeer::OM_CLASS;
            $obj = new $cls();
            $col = $obj->hydrate($row, $startcol);
            BotDecisionPeer::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * Gets the SQL value for Source ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getSourceSqlValue($enumVal)
    {
        return BotDecisionPeer::getSqlValueForEnum(BotDecisionPeer::SOURCE, $enumVal);
    }

    /**
     * Gets the SQL value for EvalStatus ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getEvalStatusSqlValue($enumVal)
    {
        return BotDecisionPeer::getSqlValueForEnum(BotDecisionPeer::EVAL_STATUS, $enumVal);
    }

    /**
     * Gets the SQL value for Verdict ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getVerdictSqlValue($enumVal)
    {
        return BotDecisionPeer::getSqlValueForEnum(BotDecisionPeer::VERDICT, $enumVal);
    }

    /**
     * Gets the SQL value for CandidateDelta ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getCandidateDeltaSqlValue($enumVal)
    {
        return BotDecisionPeer::getSqlValueForEnum(BotDecisionPeer::CANDIDATE_DELTA, $enumVal);
    }


    /**
     * Returns the number of rows matching criteria, joining the related GridRun table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinGridRun(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(BotDecisionPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotDecisionPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(BotDecisionPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyGroup table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAuthyGroup(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(BotDecisionPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotDecisionPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(BotDecisionPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyRelatedByIdCreation table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAuthyRelatedByIdCreation(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(BotDecisionPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotDecisionPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(BotDecisionPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyRelatedByIdModification table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAuthyRelatedByIdModification(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(BotDecisionPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotDecisionPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(BotDecisionPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Selects a collection of BotDecision objects pre-filled with their GridRun objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of BotDecision objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinGridRun(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);
        }

        BotDecisionPeer::addSelectColumns($criteria);
        $startcol = BotDecisionPeer::NUM_HYDRATE_COLUMNS;
        GridRunPeer::addSelectColumns($criteria);

        $criteria->addJoin(BotDecisionPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = BotDecisionPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = BotDecisionPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = BotDecisionPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                BotDecisionPeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = GridRunPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = GridRunPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = GridRunPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    GridRunPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (BotDecision) to $obj2 (GridRun)
                $obj2->addBotDecision($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of BotDecision objects pre-filled with their AuthyGroup objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of BotDecision objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyGroup(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);
        }

        BotDecisionPeer::addSelectColumns($criteria);
        $startcol = BotDecisionPeer::NUM_HYDRATE_COLUMNS;
        AuthyGroupPeer::addSelectColumns($criteria);

        $criteria->addJoin(BotDecisionPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = BotDecisionPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = BotDecisionPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = BotDecisionPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                BotDecisionPeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = AuthyGroupPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = AuthyGroupPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    AuthyGroupPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (BotDecision) to $obj2 (AuthyGroup)
                $obj2->addBotDecision($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of BotDecision objects pre-filled with their Authy objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of BotDecision objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyRelatedByIdCreation(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);
        }

        BotDecisionPeer::addSelectColumns($criteria);
        $startcol = BotDecisionPeer::NUM_HYDRATE_COLUMNS;
        AuthyPeer::addSelectColumns($criteria);

        $criteria->addJoin(BotDecisionPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = BotDecisionPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = BotDecisionPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = BotDecisionPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                BotDecisionPeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = AuthyPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = AuthyPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    AuthyPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (BotDecision) to $obj2 (Authy)
                $obj2->addBotDecisionRelatedByIdCreation($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of BotDecision objects pre-filled with their Authy objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of BotDecision objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyRelatedByIdModification(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);
        }

        BotDecisionPeer::addSelectColumns($criteria);
        $startcol = BotDecisionPeer::NUM_HYDRATE_COLUMNS;
        AuthyPeer::addSelectColumns($criteria);

        $criteria->addJoin(BotDecisionPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = BotDecisionPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = BotDecisionPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = BotDecisionPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                BotDecisionPeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = AuthyPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = AuthyPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    AuthyPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (BotDecision) to $obj2 (Authy)
                $obj2->addBotDecisionRelatedByIdModification($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Returns the number of rows matching criteria, joining all related tables
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAll(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(BotDecisionPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotDecisionPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(BotDecisionPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(BotDecisionPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(BotDecisionPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(BotDecisionPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }

    /**
     * Selects a collection of BotDecision objects pre-filled with all related objects.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of BotDecision objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAll(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);
        }

        BotDecisionPeer::addSelectColumns($criteria);
        $startcol2 = BotDecisionPeer::NUM_HYDRATE_COLUMNS;

        GridRunPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol6 = $startcol5 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(BotDecisionPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(BotDecisionPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(BotDecisionPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(BotDecisionPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = BotDecisionPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = BotDecisionPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = BotDecisionPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                BotDecisionPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

            // Add objects for joined GridRun rows

            $key2 = GridRunPeer::getPrimaryKeyHashFromRow($row, $startcol2);
            if ($key2 !== null) {
                $obj2 = GridRunPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = GridRunPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    GridRunPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 loaded

                // Add the $obj1 (BotDecision) to the collection in $obj2 (GridRun)
                $obj2->addBotDecision($obj1);
            } // if joined row not null

            // Add objects for joined AuthyGroup rows

            $key3 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol3);
            if ($key3 !== null) {
                $obj3 = AuthyGroupPeer::getInstanceFromPool($key3);
                if (!$obj3) {

                    $cls = AuthyGroupPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    AuthyGroupPeer::addInstanceToPool($obj3, $key3);
                } // if obj3 loaded

                // Add the $obj1 (BotDecision) to the collection in $obj3 (AuthyGroup)
                $obj3->addBotDecision($obj1);
            } // if joined row not null

            // Add objects for joined Authy rows

            $key4 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol4);
            if ($key4 !== null) {
                $obj4 = AuthyPeer::getInstanceFromPool($key4);
                if (!$obj4) {

                    $cls = AuthyPeer::getOMClass();

                    $obj4 = new $cls();
                    $obj4->hydrate($row, $startcol4);
                    AuthyPeer::addInstanceToPool($obj4, $key4);
                } // if obj4 loaded

                // Add the $obj1 (BotDecision) to the collection in $obj4 (Authy)
                $obj4->addBotDecisionRelatedByIdCreation($obj1);
            } // if joined row not null

            // Add objects for joined Authy rows

            $key5 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol5);
            if ($key5 !== null) {
                $obj5 = AuthyPeer::getInstanceFromPool($key5);
                if (!$obj5) {

                    $cls = AuthyPeer::getOMClass();

                    $obj5 = new $cls();
                    $obj5->hydrate($row, $startcol5);
                    AuthyPeer::addInstanceToPool($obj5, $key5);
                } // if obj5 loaded

                // Add the $obj1 (BotDecision) to the collection in $obj5 (Authy)
                $obj5->addBotDecisionRelatedByIdModification($obj1);
            } // if joined row not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Returns the number of rows matching criteria, joining the related GridRun table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptGridRun(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(BotDecisionPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotDecisionPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(BotDecisionPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(BotDecisionPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(BotDecisionPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyGroup table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptAuthyGroup(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(BotDecisionPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotDecisionPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(BotDecisionPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(BotDecisionPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(BotDecisionPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyRelatedByIdCreation table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptAuthyRelatedByIdCreation(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(BotDecisionPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotDecisionPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(BotDecisionPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(BotDecisionPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyRelatedByIdModification table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptAuthyRelatedByIdModification(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(BotDecisionPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            BotDecisionPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(BotDecisionPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(BotDecisionPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Selects a collection of BotDecision objects pre-filled with all related objects except GridRun.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of BotDecision objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptGridRun(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);
        }

        BotDecisionPeer::addSelectColumns($criteria);
        $startcol2 = BotDecisionPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(BotDecisionPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(BotDecisionPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(BotDecisionPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = BotDecisionPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = BotDecisionPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = BotDecisionPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                BotDecisionPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined AuthyGroup rows

                $key2 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = AuthyGroupPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = AuthyGroupPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    AuthyGroupPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (BotDecision) to the collection in $obj2 (AuthyGroup)
                $obj2->addBotDecision($obj1);

            } // if joined row is not null

                // Add objects for joined Authy rows

                $key3 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = AuthyPeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = AuthyPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    AuthyPeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (BotDecision) to the collection in $obj3 (Authy)
                $obj3->addBotDecisionRelatedByIdCreation($obj1);

            } // if joined row is not null

                // Add objects for joined Authy rows

                $key4 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol4);
                if ($key4 !== null) {
                    $obj4 = AuthyPeer::getInstanceFromPool($key4);
                    if (!$obj4) {

                        $cls = AuthyPeer::getOMClass();

                    $obj4 = new $cls();
                    $obj4->hydrate($row, $startcol4);
                    AuthyPeer::addInstanceToPool($obj4, $key4);
                } // if $obj4 already loaded

                // Add the $obj1 (BotDecision) to the collection in $obj4 (Authy)
                $obj4->addBotDecisionRelatedByIdModification($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of BotDecision objects pre-filled with all related objects except AuthyGroup.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of BotDecision objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptAuthyGroup(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);
        }

        BotDecisionPeer::addSelectColumns($criteria);
        $startcol2 = BotDecisionPeer::NUM_HYDRATE_COLUMNS;

        GridRunPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(BotDecisionPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(BotDecisionPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(BotDecisionPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = BotDecisionPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = BotDecisionPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = BotDecisionPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                BotDecisionPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined GridRun rows

                $key2 = GridRunPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = GridRunPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = GridRunPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    GridRunPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (BotDecision) to the collection in $obj2 (GridRun)
                $obj2->addBotDecision($obj1);

            } // if joined row is not null

                // Add objects for joined Authy rows

                $key3 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = AuthyPeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = AuthyPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    AuthyPeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (BotDecision) to the collection in $obj3 (Authy)
                $obj3->addBotDecisionRelatedByIdCreation($obj1);

            } // if joined row is not null

                // Add objects for joined Authy rows

                $key4 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol4);
                if ($key4 !== null) {
                    $obj4 = AuthyPeer::getInstanceFromPool($key4);
                    if (!$obj4) {

                        $cls = AuthyPeer::getOMClass();

                    $obj4 = new $cls();
                    $obj4->hydrate($row, $startcol4);
                    AuthyPeer::addInstanceToPool($obj4, $key4);
                } // if $obj4 already loaded

                // Add the $obj1 (BotDecision) to the collection in $obj4 (Authy)
                $obj4->addBotDecisionRelatedByIdModification($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of BotDecision objects pre-filled with all related objects except AuthyRelatedByIdCreation.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of BotDecision objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptAuthyRelatedByIdCreation(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);
        }

        BotDecisionPeer::addSelectColumns($criteria);
        $startcol2 = BotDecisionPeer::NUM_HYDRATE_COLUMNS;

        GridRunPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(BotDecisionPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(BotDecisionPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = BotDecisionPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = BotDecisionPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = BotDecisionPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                BotDecisionPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined GridRun rows

                $key2 = GridRunPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = GridRunPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = GridRunPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    GridRunPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (BotDecision) to the collection in $obj2 (GridRun)
                $obj2->addBotDecision($obj1);

            } // if joined row is not null

                // Add objects for joined AuthyGroup rows

                $key3 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = AuthyGroupPeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = AuthyGroupPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    AuthyGroupPeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (BotDecision) to the collection in $obj3 (AuthyGroup)
                $obj3->addBotDecision($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of BotDecision objects pre-filled with all related objects except AuthyRelatedByIdModification.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of BotDecision objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptAuthyRelatedByIdModification(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);
        }

        BotDecisionPeer::addSelectColumns($criteria);
        $startcol2 = BotDecisionPeer::NUM_HYDRATE_COLUMNS;

        GridRunPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + GridRunPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(BotDecisionPeer::ID_GRID_RUN, GridRunPeer::ID_GRID_RUN, $join_behavior);

        $criteria->addJoin(BotDecisionPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = BotDecisionPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = BotDecisionPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = BotDecisionPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                BotDecisionPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined GridRun rows

                $key2 = GridRunPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = GridRunPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = GridRunPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    GridRunPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (BotDecision) to the collection in $obj2 (GridRun)
                $obj2->addBotDecision($obj1);

            } // if joined row is not null

                // Add objects for joined AuthyGroup rows

                $key3 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = AuthyGroupPeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = AuthyGroupPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    AuthyGroupPeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (BotDecision) to the collection in $obj3 (AuthyGroup)
                $obj3->addBotDecision($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }

    /**
     * Returns the TableMap related to this peer.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getDatabaseMap(BotDecisionPeer::DATABASE_NAME)->getTable(BotDecisionPeer::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this peer class.
     */
    public static function buildTableMap()
    {
      $dbMap = Propel::getDatabaseMap(BaseBotDecisionPeer::DATABASE_NAME);
      if (!$dbMap->hasTable(BaseBotDecisionPeer::TABLE_NAME)) {
        $dbMap->addTableObject(new \App\map\BotDecisionTableMap());
      }
    }

    /**
     * The class that the Peer will make instances of.
     *
     *
     * @return string ClassName
     */
    public static function getOMClass($row = 0, $colnum = 0)
    {
        return BotDecisionPeer::OM_CLASS;
    }

    /**
     * Performs an INSERT on the database, given a BotDecision or Criteria object.
     *
     * @param      mixed $values Criteria or BotDecision object containing data that is used to create the INSERT statement.
     * @param      PropelPDO $con the PropelPDO connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doInsert($values, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity
        } else {
            $criteria = $values->buildCriteria(); // build Criteria from BotDecision object
        }

        if ($criteria->containsKey(BotDecisionPeer::ID_BOT_DECISION) && $criteria->keyContainsValue(BotDecisionPeer::ID_BOT_DECISION) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.BotDecisionPeer::ID_BOT_DECISION.')');
        }


        // Set the correct dbName
        $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);

        try {
            // use transaction because $criteria could contain info
            // for more than one table (I guess, conceivably)
            $con->beginTransaction();
            $pk = BasePeer::doInsert($criteria, $con);
            $con->commit();
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }

        return $pk;
    }

    /**
     * Performs an UPDATE on the database, given a BotDecision or Criteria object.
     *
     * @param      mixed $values Criteria or BotDecision object containing data that is used to create the UPDATE statement.
     * @param      PropelPDO $con The connection to use (specify PropelPDO connection object to exert more control over transactions).
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doUpdate($values, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $selectCriteria = new Criteria(BotDecisionPeer::DATABASE_NAME);

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity

            $comparison = $criteria->getComparison(BotDecisionPeer::ID_BOT_DECISION);
            $value = $criteria->remove(BotDecisionPeer::ID_BOT_DECISION);
            if ($value) {
                $selectCriteria->add(BotDecisionPeer::ID_BOT_DECISION, $value, $comparison);
            } else {
                $selectCriteria->setPrimaryTableName(BotDecisionPeer::TABLE_NAME);
            }

        } else { // $values is BotDecision object
            $criteria = $values->buildCriteria(); // gets full criteria
            $selectCriteria = $values->buildPkeyCriteria(); // gets criteria w/ primary key(s)
        }

        // set the correct dbName
        $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);

        return BasePeer::doUpdate($selectCriteria, $criteria, $con);
    }

    /**
     * Deletes all rows from the bot_decision table.
     *
     * @param      PropelPDO $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException
     */
    public static function doDeleteAll(?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += BasePeer::doDeleteAll(BotDecisionPeer::TABLE_NAME, $con, BotDecisionPeer::DATABASE_NAME);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            BotDecisionPeer::clearInstancePool();
            BotDecisionPeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs a DELETE on the database, given a BotDecision or Criteria object OR a primary key value.
     *
     * @param      mixed $values Criteria or BotDecision object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param      PropelPDO $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *				if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ?PropelPDO $con = null)
     {
        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            // invalidate the cache for all objects of this type, since we have no
            // way of knowing (without running a query) what objects should be invalidated
            // from the cache based on this Criteria.
            BotDecisionPeer::clearInstancePool();
            // rename for clarity
            $criteria = clone $values;
        } elseif ($values instanceof BotDecision) { // it's a model object
            // invalidate the cache for this single object
            BotDecisionPeer::removeInstanceFromPool($values);
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(BotDecisionPeer::DATABASE_NAME);
            $criteria->add(BotDecisionPeer::ID_BOT_DECISION, (array) $values, Criteria::IN);
            // invalidate the cache for this object(s)
            foreach ((array) $values as $singleval) {
                BotDecisionPeer::removeInstanceFromPool($singleval);
            }
        }

        // Set the correct dbName
        $criteria->setDbName(BotDecisionPeer::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();

            $affectedRows += BasePeer::doDelete($criteria, $con);
            BotDecisionPeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Validates all modified columns of given BotDecision object.
     * If parameter $columns is either a single column name or an array of column names
     * than only those columns are validated.
     *
     * NOTICE: This does not apply to primary or foreign keys for now.
     *
     * @param BotDecision $obj The object to validate.
     * @param      mixed $cols Column name or array of column names.
     *
     * @return mixed TRUE if all columns are valid or the error message of the first invalid column.
     */
    public static function doValidate($obj, $cols = null)
    {
        $columns = array();

        if ($cols) {
            $dbMap = Propel::getDatabaseMap(BotDecisionPeer::DATABASE_NAME);
            $tableMap = $dbMap->getTable(BotDecisionPeer::TABLE_NAME);

            if (! is_array($cols)) {
                $cols = array($cols);
            }

            foreach ($cols as $colName) {
                if ($tableMap->hasColumn($colName)) {
                    $get = 'get' . $tableMap->getColumn($colName)->getPhpName();
                    $columns[$colName] = $obj->$get();
                }
            }
        } else {

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::ID_GRID_RUN))
            $columns[BotDecisionPeer::ID_GRID_RUN] = $obj->getIdGridRun();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::ID_GRID_RUN))
            $columns[BotDecisionPeer::ID_GRID_RUN] = $obj->getIdGridRun();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::SOURCE))
            $columns[BotDecisionPeer::SOURCE] = $obj->getSource();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::SOURCE))
            $columns[BotDecisionPeer::SOURCE] = $obj->getSource();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::P_LOW))
            $columns[BotDecisionPeer::P_LOW] = $obj->getPLow();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::P_HIGH))
            $columns[BotDecisionPeer::P_HIGH] = $obj->getPHigh();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::N_LEVELS))
            $columns[BotDecisionPeer::N_LEVELS] = $obj->getNLevels();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::N_LEVELS))
            $columns[BotDecisionPeer::N_LEVELS] = $obj->getNLevels();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::DEPLOY_PCT))
            $columns[BotDecisionPeer::DEPLOY_PCT] = $obj->getDeployPct();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::REASON))
            $columns[BotDecisionPeer::REASON] = $obj->getReason();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::EVAL_STATUS))
            $columns[BotDecisionPeer::EVAL_STATUS] = $obj->getEvalStatus();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::EVAL_STATUS))
            $columns[BotDecisionPeer::EVAL_STATUS] = $obj->getEvalStatus();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::EVAL_AT))
            $columns[BotDecisionPeer::EVAL_AT] = $obj->getEvalAt();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::APPLIED_AT))
            $columns[BotDecisionPeer::APPLIED_AT] = $obj->getAppliedAt();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::CYCLES_DELTA))
            $columns[BotDecisionPeer::CYCLES_DELTA] = $obj->getCyclesDelta();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::VERDICT))
            $columns[BotDecisionPeer::VERDICT] = $obj->getVerdict();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::CANDIDATE_DELTA))
            $columns[BotDecisionPeer::CANDIDATE_DELTA] = $obj->getCandidateDelta();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::REQUESTED_JSON))
            $columns[BotDecisionPeer::REQUESTED_JSON] = $obj->getRequestedJson();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::CLAMPS_JSON))
            $columns[BotDecisionPeer::CLAMPS_JSON] = $obj->getClampsJson();

        if ($obj->isNew() || $obj->isColumnModified(BotDecisionPeer::BRIEF_JSON))
            $columns[BotDecisionPeer::BRIEF_JSON] = $obj->getBriefJson();

        }

        return BasePeer::doValidate(BotDecisionPeer::DATABASE_NAME, BotDecisionPeer::TABLE_NAME, $columns);
    }

    /**
     * Retrieve a single object by pkey.
     *
     * @param int $pk the primary key.
     * @param      PropelPDO $con the connection to use
     * @return BotDecision
     */
    public static function retrieveByPK($pk, ?PropelPDO $con = null)
    {

        if (null !== ($obj = BotDecisionPeer::getInstanceFromPool((string) $pk))) {
            return $obj;
        }

        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria = new Criteria(BotDecisionPeer::DATABASE_NAME);
        $criteria->add(BotDecisionPeer::ID_BOT_DECISION, $pk);

        $v = BotDecisionPeer::doSelect($criteria, $con);

        return !empty($v) > 0 ? $v[0] : null;
    }

    /**
     * Retrieve multiple objects by pkey.
     *
     * @param      array $pks List of primary keys
     * @param      PropelPDO $con the connection to use
     * @return BotDecision[]
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function retrieveByPKs($pks, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $objs = null;
        if (empty($pks)) {
            $objs = array();
        } else {
            $criteria = new Criteria(BotDecisionPeer::DATABASE_NAME);
            $criteria->add(BotDecisionPeer::ID_BOT_DECISION, $pks, Criteria::IN);
            $objs = BotDecisionPeer::doSelect($criteria, $con);
        }

        return $objs;
    }

} // BaseBotDecisionPeer

// This is the static code needed to register the TableMap for this table with the main Propel class.
//
BaseBotDecisionPeer::buildTableMap();

