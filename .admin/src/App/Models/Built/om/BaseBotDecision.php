<?php

namespace App\om;

use \BaseObject;
use \BasePeer;
use \Criteria;
use \DateTime;
use \Exception;
use \PDO;
use \Persistent;
use \Propel;
use \PropelDateTime;
use \PropelException;
use \PropelPDO;
use App\Authy;
use App\AuthyGroup;
use App\AuthyGroupQuery;
use App\AuthyQuery;
use App\BotDecision;
use App\BotDecisionPeer;
use App\BotDecisionQuery;
use App\GridRun;
use App\GridRunQuery;

/**
 * Base class that represents a row from the 'bot_decision' table.
 *
 * Refit Decision
 *
 * @package    propel.generator..om
 */
abstract class BaseBotDecision extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'App\\BotDecisionPeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        BotDecisionPeer
     */
    protected static $peer;

    /**
     * The flag var to prevent infinite loop in deep copy
     * @var       boolean
     */
    protected $startCopy = false;

    /**
     * The value for the id_bot_decision field.
     * @var        int
     */
    protected $id_bot_decision;

    /**
     * The value for the id_grid_run field.
     * @var        int
     */
    protected $id_grid_run;

    /**
     * The value for the source field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $source;

    /**
     * The value for the p_low field.
     * @var        string
     */
    protected $p_low;

    /**
     * The value for the p_high field.
     * @var        string
     */
    protected $p_high;

    /**
     * The value for the n_levels field.
     * @var        int
     */
    protected $n_levels;

    /**
     * The value for the deploy_pct field.
     * @var        int
     */
    protected $deploy_pct;

    /**
     * The value for the reason field.
     * @var        string
     */
    protected $reason;

    /**
     * The value for the price_at field.
     * @var        string
     */
    protected $price_at;

    /**
     * The value for the realized_before field.
     * Note: this column has a database default value of: '0'
     * @var        string
     */
    protected $realized_before;

    /**
     * The value for the eval_status field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $eval_status;

    /**
     * The value for the eval_at field.
     * @var        string
     */
    protected $eval_at;

    /**
     * The value for the applied_at field.
     * @var        string
     */
    protected $applied_at;

    /**
     * The value for the cycles_delta field.
     * @var        int
     */
    protected $cycles_delta;

    /**
     * The value for the realized_delta field.
     * @var        string
     */
    protected $realized_delta;

    /**
     * The value for the price_move_pct field.
     * @var        string
     */
    protected $price_move_pct;

    /**
     * The value for the verdict field.
     * @var        int
     */
    protected $verdict;

    /**
     * The value for the date_creation field.
     * @var        string
     */
    protected $date_creation;

    /**
     * The value for the date_modification field.
     * @var        string
     */
    protected $date_modification;

    /**
     * The value for the id_group_creation field.
     * @var        int
     */
    protected $id_group_creation;

    /**
     * The value for the id_creation field.
     * @var        int
     */
    protected $id_creation;

    /**
     * The value for the id_modification field.
     * @var        int
     */
    protected $id_modification;

    /**
     * @var        GridRun
     */
    protected $aGridRun;

    /**
     * @var        AuthyGroup
     */
    protected $aAuthyGroup;

    /**
     * @var        Authy
     */
    protected $aAuthyRelatedByIdCreation;

    /**
     * @var        Authy
     */
    protected $aAuthyRelatedByIdModification;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInSave = false;

    /**
     * Flag to prevent endless validation loop, if this object is referenced
     * by another object which falls in this transaction.
     * @var        boolean
     */
    protected $alreadyInValidation = false;

    /**
     * Flag to prevent endless clearAllReferences($deep=true) loop, if this object is referenced
     * @var        boolean
     */
    protected $alreadyInClearAllReferencesDeep = false;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see        __construct()
     */
    public function applyDefaultValues()
    {
        $this->source = 0;
        $this->realized_before = '0';
        $this->eval_status = 0;
    }

    /**
     * Initializes internal state of BaseBotDecision object.
     * @see        applyDefaults()
     */
    public function __construct()
    {
        parent::__construct();
        $this->applyDefaultValues();
    }

    /**
     * @Field()
     * Get the [id_bot_decision] column value.
     *
     * @return int
     */
    public function getIdBotDecision()
    {

        return $this->id_bot_decision;
    }

    /**
     * @Field()
     * Get the [id_grid_run] column value.
     * Run
     * @return int
     */
    public function getIdGridRun()
    {

        return $this->id_grid_run;
    }

    /**
     * @Field()
     * Get the [source] column value.
     * Source
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getSource()
    {
        if (null === $this->source) {
            return null;
        }
        $valueSet = BotDecisionPeer::getValueSet(BotDecisionPeer::SOURCE);
        if (!isset($valueSet[$this->source])) {
            throw new PropelException('Unknown stored enum key: ' . $this->source);
        }

        return $valueSet[$this->source];
    }

    /**
     * @Field()
     * Get the [p_low] column value.
     * Range low
     * @return string
     */
    public function getPLow()
    {

        return $this->p_low;
    }

    /**
     * @Field()
     * Get the [p_high] column value.
     * Range high
     * @return string
     */
    public function getPHigh()
    {

        return $this->p_high;
    }

    /**
     * @Field()
     * Get the [n_levels] column value.
     * Levels
     * @return int
     */
    public function getNLevels()
    {

        return $this->n_levels;
    }

    /**
     * @Field()
     * Get the [deploy_pct] column value.
     * Deployed budget %
     * @return int
     */
    public function getDeployPct()
    {

        return $this->deploy_pct;
    }

    /**
     * @Field()
     * Get the [reason] column value.
     * Reason
     * @return string
     */
    public function getReason()
    {

        return $this->reason;
    }

    /**
     * @Field()
     * Get the [price_at] column value.
     * Price at decision
     * @return string
     */
    public function getPriceAt()
    {

        return $this->price_at;
    }

    /**
     * @Field()
     * Get the [realized_before] column value.
     * Realized before
     * @return string
     */
    public function getRealizedBefore()
    {

        return $this->realized_before;
    }

    /**
     * @Field()
     * Get the [eval_status] column value.
     * Eval
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getEvalStatus()
    {
        if (null === $this->eval_status) {
            return null;
        }
        $valueSet = BotDecisionPeer::getValueSet(BotDecisionPeer::EVAL_STATUS);
        if (!isset($valueSet[$this->eval_status])) {
            throw new PropelException('Unknown stored enum key: ' . $this->eval_status);
        }

        return $valueSet[$this->eval_status];
    }

    /**
     * @Field()
     * Get the [optionally formatted] temporal [eval_at] column value.
     * Scored at
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw DateTime object will be returned.
     * @return mixed Formatted date/time value as string or DateTime object (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getEvalAt($format = 'Y-m-d H:i:s')
    {
        if ($this->eval_at === null) {
            return null;
        }

        if ($this->eval_at === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->eval_at);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->eval_at, true), $x);
        }

        if ($format === null) {
            // Because propel.useDateTimeClass is true, we return a DateTime object.
            return $dt;
        }

        if (strpos($format, '%') !== false) {
            return strftime($format, $dt->format('U'));
        }

        return $dt->format($format);

    }

    /**
     * @Field()
     * Get the [optionally formatted] temporal [applied_at] column value.
     * Applied at
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw DateTime object will be returned.
     * @return mixed Formatted date/time value as string or DateTime object (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getAppliedAt($format = 'Y-m-d H:i:s')
    {
        if ($this->applied_at === null) {
            return null;
        }

        if ($this->applied_at === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->applied_at);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->applied_at, true), $x);
        }

        if ($format === null) {
            // Because propel.useDateTimeClass is true, we return a DateTime object.
            return $dt;
        }

        if (strpos($format, '%') !== false) {
            return strftime($format, $dt->format('U'));
        }

        return $dt->format($format);

    }

    /**
     * @Field()
     * Get the [cycles_delta] column value.
     * Cycles after
     * @return int
     */
    public function getCyclesDelta()
    {

        return $this->cycles_delta;
    }

    /**
     * @Field()
     * Get the [realized_delta] column value.
     * P/L after
     * @return string
     */
    public function getRealizedDelta()
    {

        return $this->realized_delta;
    }

    /**
     * @Field()
     * Get the [price_move_pct] column value.
     * Price move %
     * @return string
     */
    public function getPriceMovePct()
    {

        return $this->price_move_pct;
    }

    /**
     * @Field()
     * Get the [verdict] column value.
     * Verdict
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getVerdict()
    {
        if (null === $this->verdict) {
            return null;
        }
        $valueSet = BotDecisionPeer::getValueSet(BotDecisionPeer::VERDICT);
        if (!isset($valueSet[$this->verdict])) {
            throw new PropelException('Unknown stored enum key: ' . $this->verdict);
        }

        return $valueSet[$this->verdict];
    }

    /**
     * @Field()
     * Get the [optionally formatted] temporal [date_creation] column value.
     *
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw DateTime object will be returned.
     * @return mixed Formatted date/time value as string or DateTime object (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getDateCreation($format = 'Y-m-d H:i:s')
    {
        if ($this->date_creation === null) {
            return null;
        }

        if ($this->date_creation === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->date_creation);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->date_creation, true), $x);
        }

        if ($format === null) {
            // Because propel.useDateTimeClass is true, we return a DateTime object.
            return $dt;
        }

        if (strpos($format, '%') !== false) {
            return strftime($format, $dt->format('U'));
        }

        return $dt->format($format);

    }

    /**
     * @Field()
     * Get the [optionally formatted] temporal [date_modification] column value.
     *
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw DateTime object will be returned.
     * @return mixed Formatted date/time value as string or DateTime object (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getDateModification($format = 'Y-m-d H:i:s')
    {
        if ($this->date_modification === null) {
            return null;
        }

        if ($this->date_modification === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->date_modification);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->date_modification, true), $x);
        }

        if ($format === null) {
            // Because propel.useDateTimeClass is true, we return a DateTime object.
            return $dt;
        }

        if (strpos($format, '%') !== false) {
            return strftime($format, $dt->format('U'));
        }

        return $dt->format($format);

    }

    /**
     * @Field()
     * Get the [id_group_creation] column value.
     *
     * @return int
     */
    public function getIdGroupCreation()
    {

        return $this->id_group_creation;
    }

    /**
     * @Field()
     * Get the [id_creation] column value.
     *
     * @return int
     */
    public function getIdCreation()
    {

        return $this->id_creation;
    }

    /**
     * @Field()
     * Get the [id_modification] column value.
     *
     * @return int
     */
    public function getIdModification()
    {

        return $this->id_modification;
    }

    /**
     * Set the value of [id_bot_decision] column.
     *
     * @param  int $v new value
     * @return BotDecision The current object (for fluent API support)
     */
    public function setIdBotDecision($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_bot_decision !== $v) {
            $this->id_bot_decision = $v;
            $this->modifiedColumns[] = BotDecisionPeer::ID_BOT_DECISION;
        }


        return $this;
    } // setIdBotDecision()

    /**
     * Set the value of [id_grid_run] column.
     * Run
     * @param  int $v new value
     * @return BotDecision The current object (for fluent API support)
     */
    public function setIdGridRun($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_grid_run !== $v) {
            $this->id_grid_run = $v;
            $this->modifiedColumns[] = BotDecisionPeer::ID_GRID_RUN;
        }

        if ($this->aGridRun !== null && $this->aGridRun->getIdGridRun() !== $v) {
            $this->aGridRun = null;
        }


        return $this;
    } // setIdGridRun()

    /**
     * Set the value of [source] column.
     * Source
     * @param  int $v new value
     * @return BotDecision The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setSource($v)
    {
        if ($v !== null) {
            $valueSet = BotDecisionPeer::getValueSet(BotDecisionPeer::SOURCE);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->source !== $v) {
            $this->source = $v;
            $this->modifiedColumns[] = BotDecisionPeer::SOURCE;
        }


        return $this;
    } // setSource()

    /**
     * Set the value of [p_low] column.
     * Range low
     * @param  string $v new value
     * @return BotDecision The current object (for fluent API support)
     */
    public function setPLow($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->p_low !== $v) {
            $this->p_low = $v;
            $this->modifiedColumns[] = BotDecisionPeer::P_LOW;
        }


        return $this;
    } // setPLow()

    /**
     * Set the value of [p_high] column.
     * Range high
     * @param  string $v new value
     * @return BotDecision The current object (for fluent API support)
     */
    public function setPHigh($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->p_high !== $v) {
            $this->p_high = $v;
            $this->modifiedColumns[] = BotDecisionPeer::P_HIGH;
        }


        return $this;
    } // setPHigh()

    /**
     * Set the value of [n_levels] column.
     * Levels
     * @param  int $v new value
     * @return BotDecision The current object (for fluent API support)
     */
    public function setNLevels($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->n_levels !== $v) {
            $this->n_levels = $v;
            $this->modifiedColumns[] = BotDecisionPeer::N_LEVELS;
        }


        return $this;
    } // setNLevels()

    /**
     * Set the value of [deploy_pct] column.
     * Deployed budget %
     * @param  int $v new value
     * @return BotDecision The current object (for fluent API support)
     */
    public function setDeployPct($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->deploy_pct !== $v) {
            $this->deploy_pct = $v;
            $this->modifiedColumns[] = BotDecisionPeer::DEPLOY_PCT;
        }


        return $this;
    } // setDeployPct()

    /**
     * Set the value of [reason] column.
     * Reason
     * @param  string $v new value
     * @return BotDecision The current object (for fluent API support)
     */
    public function setReason($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->reason !== $v) {
            $this->reason = $v;
            $this->modifiedColumns[] = BotDecisionPeer::REASON;
        }


        return $this;
    } // setReason()

    /**
     * Set the value of [price_at] column.
     * Price at decision
     * @param  string $v new value
     * @return BotDecision The current object (for fluent API support)
     */
    public function setPriceAt($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->price_at !== $v) {
            $this->price_at = $v;
            $this->modifiedColumns[] = BotDecisionPeer::PRICE_AT;
        }


        return $this;
    } // setPriceAt()

    /**
     * Set the value of [realized_before] column.
     * Realized before
     * @param  string $v new value
     * @return BotDecision The current object (for fluent API support)
     */
    public function setRealizedBefore($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->realized_before !== $v) {
            $this->realized_before = $v;
            $this->modifiedColumns[] = BotDecisionPeer::REALIZED_BEFORE;
        }


        return $this;
    } // setRealizedBefore()

    /**
     * Set the value of [eval_status] column.
     * Eval
     * @param  int $v new value
     * @return BotDecision The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setEvalStatus($v)
    {
        if ($v !== null) {
            $valueSet = BotDecisionPeer::getValueSet(BotDecisionPeer::EVAL_STATUS);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->eval_status !== $v) {
            $this->eval_status = $v;
            $this->modifiedColumns[] = BotDecisionPeer::EVAL_STATUS;
        }


        return $this;
    } // setEvalStatus()

    /**
     * Sets the value of [eval_at] column to a normalized version of the date/time value specified.
     * Scored at
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return BotDecision The current object (for fluent API support)
     */
    public function setEvalAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->eval_at !== null || $dt !== null) {
            $currentDateAsString = ($this->eval_at !== null && $tmpDt = new DateTime($this->eval_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->eval_at = $newDateAsString;
                $this->modifiedColumns[] = BotDecisionPeer::EVAL_AT;
            }
        } // if either are not null


        return $this;
    } // setEvalAt()

    /**
     * Sets the value of [applied_at] column to a normalized version of the date/time value specified.
     * Applied at
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return BotDecision The current object (for fluent API support)
     */
    public function setAppliedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->applied_at !== null || $dt !== null) {
            $currentDateAsString = ($this->applied_at !== null && $tmpDt = new DateTime($this->applied_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->applied_at = $newDateAsString;
                $this->modifiedColumns[] = BotDecisionPeer::APPLIED_AT;
            }
        } // if either are not null


        return $this;
    } // setAppliedAt()

    /**
     * Set the value of [cycles_delta] column.
     * Cycles after
     * @param  int $v new value
     * @return BotDecision The current object (for fluent API support)
     */
    public function setCyclesDelta($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->cycles_delta !== $v) {
            $this->cycles_delta = $v;
            $this->modifiedColumns[] = BotDecisionPeer::CYCLES_DELTA;
        }


        return $this;
    } // setCyclesDelta()

    /**
     * Set the value of [realized_delta] column.
     * P/L after
     * @param  string $v new value
     * @return BotDecision The current object (for fluent API support)
     */
    public function setRealizedDelta($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->realized_delta !== $v) {
            $this->realized_delta = $v;
            $this->modifiedColumns[] = BotDecisionPeer::REALIZED_DELTA;
        }


        return $this;
    } // setRealizedDelta()

    /**
     * Set the value of [price_move_pct] column.
     * Price move %
     * @param  string $v new value
     * @return BotDecision The current object (for fluent API support)
     */
    public function setPriceMovePct($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->price_move_pct !== $v) {
            $this->price_move_pct = $v;
            $this->modifiedColumns[] = BotDecisionPeer::PRICE_MOVE_PCT;
        }


        return $this;
    } // setPriceMovePct()

    /**
     * Set the value of [verdict] column.
     * Verdict
     * @param  int $v new value
     * @return BotDecision The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setVerdict($v)
    {
        if ($v !== null) {
            $valueSet = BotDecisionPeer::getValueSet(BotDecisionPeer::VERDICT);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->verdict !== $v) {
            $this->verdict = $v;
            $this->modifiedColumns[] = BotDecisionPeer::VERDICT;
        }


        return $this;
    } // setVerdict()

    /**
     * Sets the value of [date_creation] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return BotDecision The current object (for fluent API support)
     */
    public function setDateCreation($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_creation !== null || $dt !== null) {
            $currentDateAsString = ($this->date_creation !== null && $tmpDt = new DateTime($this->date_creation)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_creation = $newDateAsString;
                $this->modifiedColumns[] = BotDecisionPeer::DATE_CREATION;
            }
        } // if either are not null


        return $this;
    } // setDateCreation()

    /**
     * Sets the value of [date_modification] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return BotDecision The current object (for fluent API support)
     */
    public function setDateModification($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_modification !== null || $dt !== null) {
            $currentDateAsString = ($this->date_modification !== null && $tmpDt = new DateTime($this->date_modification)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_modification = $newDateAsString;
                $this->modifiedColumns[] = BotDecisionPeer::DATE_MODIFICATION;
            }
        } // if either are not null


        return $this;
    } // setDateModification()

    /**
     * Set the value of [id_group_creation] column.
     *
     * @param  int $v new value
     * @return BotDecision The current object (for fluent API support)
     */
    public function setIdGroupCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_group_creation !== $v) {
            $this->id_group_creation = $v;
            $this->modifiedColumns[] = BotDecisionPeer::ID_GROUP_CREATION;
        }

        if ($this->aAuthyGroup !== null && $this->aAuthyGroup->getIdAuthyGroup() !== $v) {
            $this->aAuthyGroup = null;
        }


        return $this;
    } // setIdGroupCreation()

    /**
     * Set the value of [id_creation] column.
     *
     * @param  int $v new value
     * @return BotDecision The current object (for fluent API support)
     */
    public function setIdCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_creation !== $v) {
            $this->id_creation = $v;
            $this->modifiedColumns[] = BotDecisionPeer::ID_CREATION;
        }

        if ($this->aAuthyRelatedByIdCreation !== null && $this->aAuthyRelatedByIdCreation->getIdAuthy() !== $v) {
            $this->aAuthyRelatedByIdCreation = null;
        }


        return $this;
    } // setIdCreation()

    /**
     * Set the value of [id_modification] column.
     *
     * @param  int $v new value
     * @return BotDecision The current object (for fluent API support)
     */
    public function setIdModification($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_modification !== $v) {
            $this->id_modification = $v;
            $this->modifiedColumns[] = BotDecisionPeer::ID_MODIFICATION;
        }

        if ($this->aAuthyRelatedByIdModification !== null && $this->aAuthyRelatedByIdModification->getIdAuthy() !== $v) {
            $this->aAuthyRelatedByIdModification = null;
        }


        return $this;
    } // setIdModification()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
            if ($this->source !== 0) {
                return false;
            }

            if ($this->realized_before !== '0') {
                return false;
            }

            if ($this->eval_status !== 0) {
                return false;
            }

        // otherwise, everything was equal, so return true
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array $row The row returned by PDOStatement->fetch(PDO::FETCH_NUM)
     * @param int $startcol 0-based offset column which indicates which resultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false)
    {
        try {

            $this->id_bot_decision = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
            $this->id_grid_run = ($row[$startcol + 1] !== null) ? (int) $row[$startcol + 1] : null;
            $this->source = ($row[$startcol + 2] !== null) ? (int) $row[$startcol + 2] : null;
            $this->p_low = ($row[$startcol + 3] !== null) ? (string) $row[$startcol + 3] : null;
            $this->p_high = ($row[$startcol + 4] !== null) ? (string) $row[$startcol + 4] : null;
            $this->n_levels = ($row[$startcol + 5] !== null) ? (int) $row[$startcol + 5] : null;
            $this->deploy_pct = ($row[$startcol + 6] !== null) ? (int) $row[$startcol + 6] : null;
            $this->reason = ($row[$startcol + 7] !== null) ? (string) $row[$startcol + 7] : null;
            $this->price_at = ($row[$startcol + 8] !== null) ? (string) $row[$startcol + 8] : null;
            $this->realized_before = ($row[$startcol + 9] !== null) ? (string) $row[$startcol + 9] : null;
            $this->eval_status = ($row[$startcol + 10] !== null) ? (int) $row[$startcol + 10] : null;
            $this->eval_at = ($row[$startcol + 11] !== null) ? (string) $row[$startcol + 11] : null;
            $this->applied_at = ($row[$startcol + 12] !== null) ? (string) $row[$startcol + 12] : null;
            $this->cycles_delta = ($row[$startcol + 13] !== null) ? (int) $row[$startcol + 13] : null;
            $this->realized_delta = ($row[$startcol + 14] !== null) ? (string) $row[$startcol + 14] : null;
            $this->price_move_pct = ($row[$startcol + 15] !== null) ? (string) $row[$startcol + 15] : null;
            $this->verdict = ($row[$startcol + 16] !== null) ? (int) $row[$startcol + 16] : null;
            $this->date_creation = ($row[$startcol + 17] !== null) ? (string) $row[$startcol + 17] : null;
            $this->date_modification = ($row[$startcol + 18] !== null) ? (string) $row[$startcol + 18] : null;
            $this->id_group_creation = ($row[$startcol + 19] !== null) ? (int) $row[$startcol + 19] : null;
            $this->id_creation = ($row[$startcol + 20] !== null) ? (int) $row[$startcol + 20] : null;
            $this->id_modification = ($row[$startcol + 21] !== null) ? (int) $row[$startcol + 21] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);

            return $startcol + 22; // 22 = BotDecisionPeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating BotDecision object", $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {

        if ($this->aGridRun !== null && $this->id_grid_run !== $this->aGridRun->getIdGridRun()) {
            $this->aGridRun = null;
        }
        if ($this->aAuthyGroup !== null && $this->id_group_creation !== $this->aAuthyGroup->getIdAuthyGroup()) {
            $this->aAuthyGroup = null;
        }
        if ($this->aAuthyRelatedByIdCreation !== null && $this->id_creation !== $this->aAuthyRelatedByIdCreation->getIdAuthy()) {
            $this->aAuthyRelatedByIdCreation = null;
        }
        if ($this->aAuthyRelatedByIdModification !== null && $this->id_modification !== $this->aAuthyRelatedByIdModification->getIdAuthy()) {
            $this->aAuthyRelatedByIdModification = null;
        }
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param boolean $deep (optional) Whether to also de-associated any related objects.
     * @param PropelPDO $con (optional) The PropelPDO connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, ?PropelPDO $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = BotDecisionPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aGridRun = null;
            $this->aAuthyGroup = null;
            $this->aAuthyRelatedByIdCreation = null;
            $this->aAuthyRelatedByIdModification = null;
        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param PropelPDO $con
     * @return void
     * @throws PropelException
     * @throws Exception
     * @see        BaseObject::setDeleted()
     * @see        BaseObject::isDeleted()
     */
    public function delete(?PropelPDO $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = BotDecisionQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                // GoatCheese behavior
                
                            if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                                \ApiGoat\Utility\TableVersion::bump('bot_decision');
                            }
                $con->commit();
                $this->setDeleted(true);
            } else {
                $con->commit();
            }
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param PropelPDO $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @throws Exception
     * @see        doSave()
     */
    public function save(?PropelPDO $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($con === null) {
            $con = Propel::getConnection(BotDecisionPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        $isInsert = $this->isNew();
        try {
            $ret = $this->preSave($con);
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
                // add_tablestamp behavior

                    $this->setDateCreation(time());
                    $this->setDateModification(time());
                    $this->setIdGroupCreation( (get_class($_SESSION[_AUTH_VAR]) === 'ApiGoat\Sessions\AuthySession')?$_SESSION[_AUTH_VAR]->getIdPrimaryGroup():null );
                    if(!$this->getIdCreation())
                        $this->setIdCreation( (get_class($_SESSION[_AUTH_VAR]) === 'ApiGoat\Sessions\AuthySession')?$_SESSION[_AUTH_VAR]->getIdAuthy():null );
                    if(!$this->getIdModification())
                        $this->setIdModification( (get_class($_SESSION[_AUTH_VAR]) === 'ApiGoat\Sessions\AuthySession')?$_SESSION[_AUTH_VAR]->getIdAuthy():null );

            } else {
                $ret = $ret && $this->preUpdate($con);
                // add_tablestamp behavior
                if ($this->isModified() ) {
                    $this->setDateCreation( $this->getDateCreation() );
                    $this->setDateModification(time());
                    $this->setIdGroupCreation( (get_class($_SESSION[_AUTH_VAR]) === 'ApiGoat\Sessions\AuthySession')?$_SESSION[_AUTH_VAR]->getIdPrimaryGroup():null );
                    if(!$this->getIdCreation())
                        $this->setIdCreation( (get_class($_SESSION[_AUTH_VAR]) === 'ApiGoat\Sessions\AuthySession')?$_SESSION[_AUTH_VAR]->getIdAuthy():null );
                    if(!$this->getIdModification())
                        $this->setIdModification( (get_class($_SESSION[_AUTH_VAR]) === 'ApiGoat\Sessions\AuthySession')?$_SESSION[_AUTH_VAR]->getIdAuthy():null );
                }
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                // GoatCheese behavior
                
                            if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                                \ApiGoat\Utility\TableVersion::bump('bot_decision');
                            }
                BotDecisionPeer::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param PropelPDO $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see        save()
     */
    protected function doSave(PropelPDO $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            // We call the save method on the following object(s) if they
            // were passed to this object by their corresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aGridRun !== null) {
                if ($this->aGridRun->isModified() || $this->aGridRun->isNew()) {
                    $affectedRows += $this->aGridRun->save($con);
                }
                $this->setGridRun($this->aGridRun);
            }

            if ($this->aAuthyGroup !== null) {
                if ($this->aAuthyGroup->isModified() || $this->aAuthyGroup->isNew()) {
                    $affectedRows += $this->aAuthyGroup->save($con);
                }
                $this->setAuthyGroup($this->aAuthyGroup);
            }

            if ($this->aAuthyRelatedByIdCreation !== null) {
                if ($this->aAuthyRelatedByIdCreation->isModified() || $this->aAuthyRelatedByIdCreation->isNew()) {
                    $affectedRows += $this->aAuthyRelatedByIdCreation->save($con);
                }
                $this->setAuthyRelatedByIdCreation($this->aAuthyRelatedByIdCreation);
            }

            if ($this->aAuthyRelatedByIdModification !== null) {
                if ($this->aAuthyRelatedByIdModification->isModified() || $this->aAuthyRelatedByIdModification->isNew()) {
                    $affectedRows += $this->aAuthyRelatedByIdModification->save($con);
                }
                $this->setAuthyRelatedByIdModification($this->aAuthyRelatedByIdModification);
            }

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                } else {
                    $this->doUpdate($con);
                }
                $affectedRows += 1;
                $this->resetModified();
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param PropelPDO $con
     *
     * @throws PropelException
     * @see        doSave()
     */
    protected function doInsert(PropelPDO $con)
    {
        $modifiedColumns = array();
        $index = 0;

        $this->modifiedColumns[] = BotDecisionPeer::ID_BOT_DECISION;
        if (null !== $this->id_bot_decision) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . BotDecisionPeer::ID_BOT_DECISION . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(BotDecisionPeer::ID_BOT_DECISION)) {
            $modifiedColumns[':p' . $index++]  = '`id_bot_decision`';
        }
        if ($this->isColumnModified(BotDecisionPeer::ID_GRID_RUN)) {
            $modifiedColumns[':p' . $index++]  = '`id_grid_run`';
        }
        if ($this->isColumnModified(BotDecisionPeer::SOURCE)) {
            $modifiedColumns[':p' . $index++]  = '`source`';
        }
        if ($this->isColumnModified(BotDecisionPeer::P_LOW)) {
            $modifiedColumns[':p' . $index++]  = '`p_low`';
        }
        if ($this->isColumnModified(BotDecisionPeer::P_HIGH)) {
            $modifiedColumns[':p' . $index++]  = '`p_high`';
        }
        if ($this->isColumnModified(BotDecisionPeer::N_LEVELS)) {
            $modifiedColumns[':p' . $index++]  = '`n_levels`';
        }
        if ($this->isColumnModified(BotDecisionPeer::DEPLOY_PCT)) {
            $modifiedColumns[':p' . $index++]  = '`deploy_pct`';
        }
        if ($this->isColumnModified(BotDecisionPeer::REASON)) {
            $modifiedColumns[':p' . $index++]  = '`reason`';
        }
        if ($this->isColumnModified(BotDecisionPeer::PRICE_AT)) {
            $modifiedColumns[':p' . $index++]  = '`price_at`';
        }
        if ($this->isColumnModified(BotDecisionPeer::REALIZED_BEFORE)) {
            $modifiedColumns[':p' . $index++]  = '`realized_before`';
        }
        if ($this->isColumnModified(BotDecisionPeer::EVAL_STATUS)) {
            $modifiedColumns[':p' . $index++]  = '`eval_status`';
        }
        if ($this->isColumnModified(BotDecisionPeer::EVAL_AT)) {
            $modifiedColumns[':p' . $index++]  = '`eval_at`';
        }
        if ($this->isColumnModified(BotDecisionPeer::APPLIED_AT)) {
            $modifiedColumns[':p' . $index++]  = '`applied_at`';
        }
        if ($this->isColumnModified(BotDecisionPeer::CYCLES_DELTA)) {
            $modifiedColumns[':p' . $index++]  = '`cycles_delta`';
        }
        if ($this->isColumnModified(BotDecisionPeer::REALIZED_DELTA)) {
            $modifiedColumns[':p' . $index++]  = '`realized_delta`';
        }
        if ($this->isColumnModified(BotDecisionPeer::PRICE_MOVE_PCT)) {
            $modifiedColumns[':p' . $index++]  = '`price_move_pct`';
        }
        if ($this->isColumnModified(BotDecisionPeer::VERDICT)) {
            $modifiedColumns[':p' . $index++]  = '`verdict`';
        }
        if ($this->isColumnModified(BotDecisionPeer::DATE_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_creation`';
        }
        if ($this->isColumnModified(BotDecisionPeer::DATE_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_modification`';
        }
        if ($this->isColumnModified(BotDecisionPeer::ID_GROUP_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_group_creation`';
        }
        if ($this->isColumnModified(BotDecisionPeer::ID_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_creation`';
        }
        if ($this->isColumnModified(BotDecisionPeer::ID_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_modification`';
        }

        $sql = sprintf(
            'INSERT INTO `bot_decision` (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case '`id_bot_decision`':
                        $stmt->bindValue($identifier, $this->id_bot_decision, PDO::PARAM_INT);
                        break;
                    case '`id_grid_run`':
                        $stmt->bindValue($identifier, $this->id_grid_run, PDO::PARAM_INT);
                        break;
                    case '`source`':
                        $stmt->bindValue($identifier, $this->source, PDO::PARAM_INT);
                        break;
                    case '`p_low`':
                        $stmt->bindValue($identifier, $this->p_low, PDO::PARAM_STR);
                        break;
                    case '`p_high`':
                        $stmt->bindValue($identifier, $this->p_high, PDO::PARAM_STR);
                        break;
                    case '`n_levels`':
                        $stmt->bindValue($identifier, $this->n_levels, PDO::PARAM_INT);
                        break;
                    case '`deploy_pct`':
                        $stmt->bindValue($identifier, $this->deploy_pct, PDO::PARAM_INT);
                        break;
                    case '`reason`':
                        $stmt->bindValue($identifier, $this->reason, PDO::PARAM_STR);
                        break;
                    case '`price_at`':
                        $stmt->bindValue($identifier, $this->price_at, PDO::PARAM_STR);
                        break;
                    case '`realized_before`':
                        $stmt->bindValue($identifier, $this->realized_before, PDO::PARAM_STR);
                        break;
                    case '`eval_status`':
                        $stmt->bindValue($identifier, $this->eval_status, PDO::PARAM_INT);
                        break;
                    case '`eval_at`':
                        $stmt->bindValue($identifier, $this->eval_at, PDO::PARAM_STR);
                        break;
                    case '`applied_at`':
                        $stmt->bindValue($identifier, $this->applied_at, PDO::PARAM_STR);
                        break;
                    case '`cycles_delta`':
                        $stmt->bindValue($identifier, $this->cycles_delta, PDO::PARAM_INT);
                        break;
                    case '`realized_delta`':
                        $stmt->bindValue($identifier, $this->realized_delta, PDO::PARAM_STR);
                        break;
                    case '`price_move_pct`':
                        $stmt->bindValue($identifier, $this->price_move_pct, PDO::PARAM_STR);
                        break;
                    case '`verdict`':
                        $stmt->bindValue($identifier, $this->verdict, PDO::PARAM_INT);
                        break;
                    case '`date_creation`':
                        $stmt->bindValue($identifier, $this->date_creation, PDO::PARAM_STR);
                        break;
                    case '`date_modification`':
                        $stmt->bindValue($identifier, $this->date_modification, PDO::PARAM_STR);
                        break;
                    case '`id_group_creation`':
                        $stmt->bindValue($identifier, $this->id_group_creation, PDO::PARAM_INT);
                        break;
                    case '`id_creation`':
                        $stmt->bindValue($identifier, $this->id_creation, PDO::PARAM_INT);
                        break;
                    case '`id_modification`':
                        $stmt->bindValue($identifier, $this->id_modification, PDO::PARAM_INT);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', $e);
        }
        $this->setIdBotDecision($pk);

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param PropelPDO $con
     *
     * @see        doSave()
     */
    protected function doUpdate(PropelPDO $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();
        BasePeer::doUpdate($selectCriteria, $valuesCriteria, $con);
    }

    /**
     * Array of ValidationFailed objects.
     * @var        array ValidationFailed[]
     */
    protected $validationFailures = array();

    /**
     * Gets any ValidationFailed objects that resulted from last call to validate().
     *
     *
     * @return array ValidationFailed[]
     * @see        validate()
     */
    public function getValidationFailures()
    {
        return $this->validationFailures;
    }

    /**
     * Validates the objects modified field values and all objects related to this table.
     *
     * If $columns is either a column name or an array of column names
     * only those columns are validated.
     *
     * @param mixed $columns Column name or an array of column names.
     * @return boolean Whether all columns pass validation.
     * @see        doValidate()
     * @see        getValidationFailures()
     */
    public function validate($columns = null)
    {
        $res = $this->doValidate($columns);
        if ($res === true) {
            $this->validationFailures = array();

            return true;
        }

        $this->validationFailures = $res;

        return false;
    }

    /**
     * This function performs the validation work for complex object models.
     *
     * In addition to checking the current object, all related objects will
     * also be validated.  If all pass then <code>true</code> is returned; otherwise
     * an aggregated array of ValidationFailed objects will be returned.
     *
     * @param array $columns Array of column names to validate.
     * @return mixed <code>true</code> if all validations pass; array of <code>ValidationFailed</code> objects otherwise.
     */
    protected function doValidate($columns = null)
    {
        if (!$this->alreadyInValidation) {
            $this->alreadyInValidation = true;
            $retval = null;

            $failureMap = array();


            // We call the validate method on the following object(s) if they
            // were passed to this object by their corresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aGridRun !== null) {
                if (!$this->aGridRun->validate($columns)) {
                    $failureMap = array_merge($failureMap, $this->aGridRun->getValidationFailures());
                }
            }

            if ($this->aAuthyGroup !== null) {
                if (!$this->aAuthyGroup->validate($columns)) {
                    $failureMap = array_merge($failureMap, $this->aAuthyGroup->getValidationFailures());
                }
            }

            if ($this->aAuthyRelatedByIdCreation !== null) {
                if (!$this->aAuthyRelatedByIdCreation->validate($columns)) {
                    $failureMap = array_merge($failureMap, $this->aAuthyRelatedByIdCreation->getValidationFailures());
                }
            }

            if ($this->aAuthyRelatedByIdModification !== null) {
                if (!$this->aAuthyRelatedByIdModification->validate($columns)) {
                    $failureMap = array_merge($failureMap, $this->aAuthyRelatedByIdModification->getValidationFailures());
                }
            }


            if (($retval = BotDecisionPeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
            }



            $this->alreadyInValidation = false;
        }

        return (!empty($failureMap) ? $failureMap : true);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param string $name name
     * @param string $type The type of fieldname the $name is of:
     *               one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *               BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     *               Defaults to BasePeer::TYPE_PHPNAME
     * @return mixed Value of field.
     */
    public function getByName($name, $type = BasePeer::TYPE_PHPNAME)
    {
        $pos = BotDecisionPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME,
     *                    BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     *                    Defaults to BasePeer::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to true.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = BasePeer::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {
        if (isset($alreadyDumpedObjects['BotDecision'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['BotDecision'][$this->getPrimaryKey()] = true;
        $keys = BotDecisionPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getIdBotDecision(),
            $keys[1] => $this->getIdGridRun(),
            $keys[2] => $this->getSource(),
            $keys[3] => $this->getPLow(),
            $keys[4] => $this->getPHigh(),
            $keys[5] => $this->getNLevels(),
            $keys[6] => $this->getDeployPct(),
            $keys[7] => $this->getReason(),
            $keys[8] => $this->getPriceAt(),
            $keys[9] => $this->getRealizedBefore(),
            $keys[10] => $this->getEvalStatus(),
            $keys[11] => $this->getEvalAt(),
            $keys[12] => $this->getAppliedAt(),
            $keys[13] => $this->getCyclesDelta(),
            $keys[14] => $this->getRealizedDelta(),
            $keys[15] => $this->getPriceMovePct(),
            $keys[16] => $this->getVerdict(),
            $keys[17] => $this->getDateCreation(),
            $keys[18] => $this->getDateModification(),
            $keys[19] => $this->getIdGroupCreation(),
            $keys[20] => $this->getIdCreation(),
            $keys[21] => $this->getIdModification(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aGridRun) {
                $result['GridRun'] = $this->aGridRun->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aAuthyGroup) {
                $result['AuthyGroup'] = $this->aAuthyGroup->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aAuthyRelatedByIdCreation) {
                $result['AuthyRelatedByIdCreation'] = $this->aAuthyRelatedByIdCreation->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aAuthyRelatedByIdModification) {
                $result['AuthyRelatedByIdModification'] = $this->aAuthyRelatedByIdModification->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param string $name peer name
     * @param mixed $value field value
     * @param string $type The type of fieldname the $name is of:
     *                     one of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *                     BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     *                     Defaults to BasePeer::TYPE_PHPNAME
     * @return void
     */
    public function setByName($name, $value, $type = BasePeer::TYPE_PHPNAME)
    {
        $pos = BotDecisionPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

        $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param int $pos position in xml schema
     * @param mixed $value field value
     * @return void
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setIdBotDecision($value);
                break;
            case 1:
                $this->setIdGridRun($value);
                break;
            case 2:
                $valueSet = BotDecisionPeer::getValueSet(BotDecisionPeer::SOURCE);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setSource($value);
                break;
            case 3:
                $this->setPLow($value);
                break;
            case 4:
                $this->setPHigh($value);
                break;
            case 5:
                $this->setNLevels($value);
                break;
            case 6:
                $this->setDeployPct($value);
                break;
            case 7:
                $this->setReason($value);
                break;
            case 8:
                $this->setPriceAt($value);
                break;
            case 9:
                $this->setRealizedBefore($value);
                break;
            case 10:
                $valueSet = BotDecisionPeer::getValueSet(BotDecisionPeer::EVAL_STATUS);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setEvalStatus($value);
                break;
            case 11:
                $this->setEvalAt($value);
                break;
            case 12:
                $this->setAppliedAt($value);
                break;
            case 13:
                $this->setCyclesDelta($value);
                break;
            case 14:
                $this->setRealizedDelta($value);
                break;
            case 15:
                $this->setPriceMovePct($value);
                break;
            case 16:
                $valueSet = BotDecisionPeer::getValueSet(BotDecisionPeer::VERDICT);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setVerdict($value);
                break;
            case 17:
                $this->setDateCreation($value);
                break;
            case 18:
                $this->setDateModification($value);
                break;
            case 19:
                $this->setIdGroupCreation($value);
                break;
            case 20:
                $this->setIdCreation($value);
                break;
            case 21:
                $this->setIdModification($value);
                break;
        } // switch()
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME,
     * BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM.
     * The default key type is the column's BasePeer::TYPE_PHPNAME
     *
     * @param array  $arr     An array to populate the object from.
     * @param string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = BasePeer::TYPE_PHPNAME)
    {
        $keys = BotDecisionPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setIdBotDecision($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setIdGridRun($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setSource($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setPLow($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setPHigh($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setNLevels($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setDeployPct($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setReason($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setPriceAt($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setRealizedBefore($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setEvalStatus($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setEvalAt($arr[$keys[11]]);
        if (array_key_exists($keys[12], $arr)) $this->setAppliedAt($arr[$keys[12]]);
        if (array_key_exists($keys[13], $arr)) $this->setCyclesDelta($arr[$keys[13]]);
        if (array_key_exists($keys[14], $arr)) $this->setRealizedDelta($arr[$keys[14]]);
        if (array_key_exists($keys[15], $arr)) $this->setPriceMovePct($arr[$keys[15]]);
        if (array_key_exists($keys[16], $arr)) $this->setVerdict($arr[$keys[16]]);
        if (array_key_exists($keys[17], $arr)) $this->setDateCreation($arr[$keys[17]]);
        if (array_key_exists($keys[18], $arr)) $this->setDateModification($arr[$keys[18]]);
        if (array_key_exists($keys[19], $arr)) $this->setIdGroupCreation($arr[$keys[19]]);
        if (array_key_exists($keys[20], $arr)) $this->setIdCreation($arr[$keys[20]]);
        if (array_key_exists($keys[21], $arr)) $this->setIdModification($arr[$keys[21]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(BotDecisionPeer::DATABASE_NAME);

        if ($this->isColumnModified(BotDecisionPeer::ID_BOT_DECISION)) $criteria->add(BotDecisionPeer::ID_BOT_DECISION, $this->id_bot_decision);
        if ($this->isColumnModified(BotDecisionPeer::ID_GRID_RUN)) $criteria->add(BotDecisionPeer::ID_GRID_RUN, $this->id_grid_run);
        if ($this->isColumnModified(BotDecisionPeer::SOURCE)) $criteria->add(BotDecisionPeer::SOURCE, $this->source);
        if ($this->isColumnModified(BotDecisionPeer::P_LOW)) $criteria->add(BotDecisionPeer::P_LOW, $this->p_low);
        if ($this->isColumnModified(BotDecisionPeer::P_HIGH)) $criteria->add(BotDecisionPeer::P_HIGH, $this->p_high);
        if ($this->isColumnModified(BotDecisionPeer::N_LEVELS)) $criteria->add(BotDecisionPeer::N_LEVELS, $this->n_levels);
        if ($this->isColumnModified(BotDecisionPeer::DEPLOY_PCT)) $criteria->add(BotDecisionPeer::DEPLOY_PCT, $this->deploy_pct);
        if ($this->isColumnModified(BotDecisionPeer::REASON)) $criteria->add(BotDecisionPeer::REASON, $this->reason);
        if ($this->isColumnModified(BotDecisionPeer::PRICE_AT)) $criteria->add(BotDecisionPeer::PRICE_AT, $this->price_at);
        if ($this->isColumnModified(BotDecisionPeer::REALIZED_BEFORE)) $criteria->add(BotDecisionPeer::REALIZED_BEFORE, $this->realized_before);
        if ($this->isColumnModified(BotDecisionPeer::EVAL_STATUS)) $criteria->add(BotDecisionPeer::EVAL_STATUS, $this->eval_status);
        if ($this->isColumnModified(BotDecisionPeer::EVAL_AT)) $criteria->add(BotDecisionPeer::EVAL_AT, $this->eval_at);
        if ($this->isColumnModified(BotDecisionPeer::APPLIED_AT)) $criteria->add(BotDecisionPeer::APPLIED_AT, $this->applied_at);
        if ($this->isColumnModified(BotDecisionPeer::CYCLES_DELTA)) $criteria->add(BotDecisionPeer::CYCLES_DELTA, $this->cycles_delta);
        if ($this->isColumnModified(BotDecisionPeer::REALIZED_DELTA)) $criteria->add(BotDecisionPeer::REALIZED_DELTA, $this->realized_delta);
        if ($this->isColumnModified(BotDecisionPeer::PRICE_MOVE_PCT)) $criteria->add(BotDecisionPeer::PRICE_MOVE_PCT, $this->price_move_pct);
        if ($this->isColumnModified(BotDecisionPeer::VERDICT)) $criteria->add(BotDecisionPeer::VERDICT, $this->verdict);
        if ($this->isColumnModified(BotDecisionPeer::DATE_CREATION)) $criteria->add(BotDecisionPeer::DATE_CREATION, $this->date_creation);
        if ($this->isColumnModified(BotDecisionPeer::DATE_MODIFICATION)) $criteria->add(BotDecisionPeer::DATE_MODIFICATION, $this->date_modification);
        if ($this->isColumnModified(BotDecisionPeer::ID_GROUP_CREATION)) $criteria->add(BotDecisionPeer::ID_GROUP_CREATION, $this->id_group_creation);
        if ($this->isColumnModified(BotDecisionPeer::ID_CREATION)) $criteria->add(BotDecisionPeer::ID_CREATION, $this->id_creation);
        if ($this->isColumnModified(BotDecisionPeer::ID_MODIFICATION)) $criteria->add(BotDecisionPeer::ID_MODIFICATION, $this->id_modification);

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = new Criteria(BotDecisionPeer::DATABASE_NAME);
        $criteria->add(BotDecisionPeer::ID_BOT_DECISION, $this->id_bot_decision);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getIdBotDecision();
    }

    /**
     * Generic method to set the primary key (id_bot_decision column).
     *
     * @param  int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setIdBotDecision($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getIdBotDecision();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of BotDecision (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setIdGridRun($this->getIdGridRun());
        $copyObj->setSource($this->getSource());
        $copyObj->setPLow($this->getPLow());
        $copyObj->setPHigh($this->getPHigh());
        $copyObj->setNLevels($this->getNLevels());
        $copyObj->setDeployPct($this->getDeployPct());
        $copyObj->setReason($this->getReason());
        $copyObj->setPriceAt($this->getPriceAt());
        $copyObj->setRealizedBefore($this->getRealizedBefore());
        $copyObj->setEvalStatus($this->getEvalStatus());
        $copyObj->setEvalAt($this->getEvalAt());
        $copyObj->setAppliedAt($this->getAppliedAt());
        $copyObj->setCyclesDelta($this->getCyclesDelta());
        $copyObj->setRealizedDelta($this->getRealizedDelta());
        $copyObj->setPriceMovePct($this->getPriceMovePct());
        $copyObj->setVerdict($this->getVerdict());
        $copyObj->setDateCreation($this->getDateCreation());
        $copyObj->setDateModification($this->getDateModification());
        $copyObj->setIdGroupCreation($this->getIdGroupCreation());
        $copyObj->setIdCreation($this->getIdCreation());
        $copyObj->setIdModification($this->getIdModification());

        if ($deepCopy && !$this->startCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);
            // store object hash to prevent cycle
            $this->startCopy = true;

            //unflag object copy
            $this->startCopy = false;
        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setIdBotDecision(NULL); // this is a auto-increment column, so set to default value
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return BotDecision Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }

    /**
     * Returns a peer instance associated with this om.
     *
     * Since Peer classes are not to have any instance attributes, this method returns the
     * same instance for all member of this class. The method could therefore
     * be static, but this would prevent one from overriding the behavior.
     *
     * @return BotDecisionPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new BotDecisionPeer();
        }

        return self::$peer;
    }

    /**
     * Declares an association between this object and a GridRun object.
     *
     * @param                  GridRun $v
     * @return BotDecision The current object (for fluent API support)
     * @throws PropelException
     */
    public function setGridRun(?GridRun $v = null)
    {
        if ($v === null) {
            $this->setIdGridRun(NULL);
        } else {
            $this->setIdGridRun($v->getIdGridRun());
        }

        $this->aGridRun = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the GridRun object, it will not be re-added.
        if ($v !== null) {
            $v->addBotDecision($this);
        }


        return $this;
    }


    /**
     * Get the associated GridRun object
     *
     * @param PropelPDO $con Optional Connection object.
     * @param $doQuery Executes a query to get the object if required
     * @return GridRun The associated GridRun object.
     * @throws PropelException
     */
    public function getGridRun(?PropelPDO $con = null, $doQuery = true)
    {
        if ($this->aGridRun === null && ($this->id_grid_run !== null) && $doQuery) {
            $this->aGridRun = GridRunQuery::create()->findPk($this->id_grid_run, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aGridRun->addBotDecisions($this);
             */
        }

        return $this->aGridRun;
    }

    /**
     * Declares an association between this object and a AuthyGroup object.
     *
     * @param                  AuthyGroup $v
     * @return BotDecision The current object (for fluent API support)
     * @throws PropelException
     */
    public function setAuthyGroup(?AuthyGroup $v = null)
    {
        if ($v === null) {
            $this->setIdGroupCreation(NULL);
        } else {
            $this->setIdGroupCreation($v->getIdAuthyGroup());
        }

        $this->aAuthyGroup = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the AuthyGroup object, it will not be re-added.
        if ($v !== null) {
            $v->addBotDecision($this);
        }


        return $this;
    }


    /**
     * Get the associated AuthyGroup object
     *
     * @param PropelPDO $con Optional Connection object.
     * @param $doQuery Executes a query to get the object if required
     * @return AuthyGroup The associated AuthyGroup object.
     * @throws PropelException
     */
    public function getAuthyGroup(?PropelPDO $con = null, $doQuery = true)
    {
        if ($this->aAuthyGroup === null && ($this->id_group_creation !== null) && $doQuery) {
            $this->aAuthyGroup = AuthyGroupQuery::create()->findPk($this->id_group_creation, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aAuthyGroup->addBotDecisions($this);
             */
        }

        return $this->aAuthyGroup;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return BotDecision The current object (for fluent API support)
     * @throws PropelException
     */
    public function setAuthyRelatedByIdCreation(?Authy $v = null)
    {
        if ($v === null) {
            $this->setIdCreation(NULL);
        } else {
            $this->setIdCreation($v->getIdAuthy());
        }

        $this->aAuthyRelatedByIdCreation = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the Authy object, it will not be re-added.
        if ($v !== null) {
            $v->addBotDecisionRelatedByIdCreation($this);
        }


        return $this;
    }


    /**
     * Get the associated Authy object
     *
     * @param PropelPDO $con Optional Connection object.
     * @param $doQuery Executes a query to get the object if required
     * @return Authy The associated Authy object.
     * @throws PropelException
     */
    public function getAuthyRelatedByIdCreation(?PropelPDO $con = null, $doQuery = true)
    {
        if ($this->aAuthyRelatedByIdCreation === null && ($this->id_creation !== null) && $doQuery) {
            $this->aAuthyRelatedByIdCreation = AuthyQuery::create()->findPk($this->id_creation, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aAuthyRelatedByIdCreation->addBotDecisionsRelatedByIdCreation($this);
             */
        }

        return $this->aAuthyRelatedByIdCreation;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return BotDecision The current object (for fluent API support)
     * @throws PropelException
     */
    public function setAuthyRelatedByIdModification(?Authy $v = null)
    {
        if ($v === null) {
            $this->setIdModification(NULL);
        } else {
            $this->setIdModification($v->getIdAuthy());
        }

        $this->aAuthyRelatedByIdModification = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the Authy object, it will not be re-added.
        if ($v !== null) {
            $v->addBotDecisionRelatedByIdModification($this);
        }


        return $this;
    }


    /**
     * Get the associated Authy object
     *
     * @param PropelPDO $con Optional Connection object.
     * @param $doQuery Executes a query to get the object if required
     * @return Authy The associated Authy object.
     * @throws PropelException
     */
    public function getAuthyRelatedByIdModification(?PropelPDO $con = null, $doQuery = true)
    {
        if ($this->aAuthyRelatedByIdModification === null && ($this->id_modification !== null) && $doQuery) {
            $this->aAuthyRelatedByIdModification = AuthyQuery::create()->findPk($this->id_modification, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aAuthyRelatedByIdModification->addBotDecisionsRelatedByIdModification($this);
             */
        }

        return $this->aAuthyRelatedByIdModification;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id_bot_decision = null;
        $this->id_grid_run = null;
        $this->source = null;
        $this->p_low = null;
        $this->p_high = null;
        $this->n_levels = null;
        $this->deploy_pct = null;
        $this->reason = null;
        $this->price_at = null;
        $this->realized_before = null;
        $this->eval_status = null;
        $this->eval_at = null;
        $this->applied_at = null;
        $this->cycles_delta = null;
        $this->realized_delta = null;
        $this->price_move_pct = null;
        $this->verdict = null;
        $this->date_creation = null;
        $this->date_modification = null;
        $this->id_group_creation = null;
        $this->id_creation = null;
        $this->id_modification = null;
        $this->alreadyInSave = false;
        $this->alreadyInValidation = false;
        $this->alreadyInClearAllReferencesDeep = false;
        $this->clearAllReferences();
        $this->applyDefaultValues();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references to other model objects or collections of model objects.
     *
     * This method is a user-space workaround for PHP's inability to garbage collect
     * objects with circular references (even in PHP 5.3). This is currently necessary
     * when using Propel in certain daemon or large-volume/high-memory operations.
     *
     * @param boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep && !$this->alreadyInClearAllReferencesDeep) {
            $this->alreadyInClearAllReferencesDeep = true;
            if ($this->aGridRun instanceof Persistent) {
              $this->aGridRun->clearAllReferences($deep);
            }
            if ($this->aAuthyGroup instanceof Persistent) {
              $this->aAuthyGroup->clearAllReferences($deep);
            }
            if ($this->aAuthyRelatedByIdCreation instanceof Persistent) {
              $this->aAuthyRelatedByIdCreation->clearAllReferences($deep);
            }
            if ($this->aAuthyRelatedByIdModification instanceof Persistent) {
              $this->aAuthyRelatedByIdModification->clearAllReferences($deep);
            }

            $this->alreadyInClearAllReferencesDeep = false;
        } // if ($deep)

        $this->aGridRun = null;
        $this->aAuthyGroup = null;
        $this->aAuthyRelatedByIdCreation = null;
        $this->aAuthyRelatedByIdModification = null;
    }

    /**
     * return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(BotDecisionPeer::DEFAULT_STRING_FORMAT);
    }

    /**
     * return true is the object is in saving state
     *
     * @return boolean
     */
    public function isAlreadyInSave()
    {
        return $this->alreadyInSave;
    }

    // add_tablestamp behavior

    /**
     * Mark the current object so that the update date doesn't get updated during next save
     *
     * @return     BotDecision The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged(){
        $this->modifiedColumns[] = BotDecisionPeer::DATE_MODIFICATION;

        return $this;
    }

}
