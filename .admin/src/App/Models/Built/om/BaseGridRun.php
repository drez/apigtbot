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
use \PropelCollection;
use \PropelDateTime;
use \PropelException;
use \PropelObjectCollection;
use \PropelPDO;
use App\Authy;
use App\AuthyGroup;
use App\AuthyGroupQuery;
use App\AuthyQuery;
use App\BotCommand;
use App\BotCommandQuery;
use App\BotDecision;
use App\BotDecisionQuery;
use App\BotEvent;
use App\BotEventQuery;
use App\BotOrder;
use App\BotOrderQuery;
use App\GridRun;
use App\GridRunPeer;
use App\GridRunQuery;
use App\TradeCycle;
use App\TradeCycleQuery;

/**
 * Base class that represents a row from the 'grid_run' table.
 *
 * Grid Run
 *
 * @package    propel.generator..om
 */
abstract class BaseGridRun extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'App\\GridRunPeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        GridRunPeer
     */
    protected static $peer;

    /**
     * The flag var to prevent infinite loop in deep copy
     * @var       boolean
     */
    protected $startCopy = false;

    /**
     * The value for the id_grid_run field.
     * @var        int
     */
    protected $id_grid_run;

    /**
     * The value for the label field.
     * @var        string
     */
    protected $label;

    /**
     * The value for the symbol field.
     * Note: this column has a database default value of: 'BTCUSDT'
     * @var        string
     */
    protected $symbol;

    /**
     * The value for the status field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $status;

    /**
     * The value for the kill_switch field.
     * Note: this column has a database default value of: false
     * @var        boolean
     */
    protected $kill_switch;

    /**
     * The value for the profile field.
     * Note: this column has a database default value of: 2
     * @var        int
     */
    protected $profile;

    /**
     * The value for the algo field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $algo;

    /**
     * The value for the simulated field.
     * Note: this column has a database default value of: true
     * @var        boolean
     */
    protected $simulated;

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
     * Note: this column has a database default value of: 20
     * @var        int
     */
    protected $n_levels;

    /**
     * The value for the spacing field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $spacing;

    /**
     * The value for the allocation field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $allocation;

    /**
     * The value for the budget_quote field.
     * @var        string
     */
    protected $budget_quote;

    /**
     * The value for the deploy_pct field.
     * Note: this column has a database default value of: 100
     * @var        int
     */
    protected $deploy_pct;

    /**
     * The value for the fee_pct field.
     * Note: this column has a database default value of: '0.001'
     * @var        string
     */
    protected $fee_pct;

    /**
     * The value for the max_position_quote field.
     * @var        string
     */
    protected $max_position_quote;

    /**
     * The value for the max_order_quote field.
     * @var        string
     */
    protected $max_order_quote;

    /**
     * The value for the daily_loss_limit_quote field.
     * @var        string
     */
    protected $daily_loss_limit_quote;

    /**
     * The value for the max_unrealized_loss_quote field.
     * @var        string
     */
    protected $max_unrealized_loss_quote;

    /**
     * The value for the breakout_buffer_pct field.
     * Note: this column has a database default value of: '0.02'
     * @var        string
     */
    protected $breakout_buffer_pct;

    /**
     * The value for the breakout_policy field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $breakout_policy;

    /**
     * The value for the max_open_orders field.
     * Note: this column has a database default value of: 60
     * @var        int
     */
    protected $max_open_orders;

    /**
     * The value for the max_buy_levels_below field.
     * @var        int
     */
    protected $max_buy_levels_below;

    /**
     * The value for the trend_tf field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $trend_tf;

    /**
     * The value for the donchian_period field.
     * Note: this column has a database default value of: 20
     * @var        int
     */
    protected $donchian_period;

    /**
     * The value for the trend_ema_fast field.
     * Note: this column has a database default value of: 20
     * @var        int
     */
    protected $trend_ema_fast;

    /**
     * The value for the trend_ema_slow field.
     * Note: this column has a database default value of: 50
     * @var        int
     */
    protected $trend_ema_slow;

    /**
     * The value for the atr_period field.
     * Note: this column has a database default value of: 14
     * @var        int
     */
    protected $atr_period;

    /**
     * The value for the atr_stop_mult field.
     * @var        string
     */
    protected $atr_stop_mult;

    /**
     * The value for the atr_initial_mult field.
     * @var        string
     */
    protected $atr_initial_mult;

    /**
     * The value for the reentry_cooldown field.
     * @var        int
     */
    protected $reentry_cooldown;

    /**
     * The value for the engine_state field.
     * @var        string
     */
    protected $engine_state;

    /**
     * The value for the last_tick_at field.
     * @var        string
     */
    protected $last_tick_at;

    /**
     * The value for the last_price field.
     * @var        string
     */
    protected $last_price;

    /**
     * The value for the bal_base field.
     * @var        string
     */
    protected $bal_base;

    /**
     * The value for the bal_quote field.
     * @var        string
     */
    protected $bal_quote;

    /**
     * The value for the sim_bal_base field.
     * @var        string
     */
    protected $sim_bal_base;

    /**
     * The value for the sim_bal_quote field.
     * @var        string
     */
    protected $sim_bal_quote;

    /**
     * The value for the run_uid field.
     * @var        string
     */
    protected $run_uid;

    /**
     * The value for the applied_geometry field.
     * @var        string
     */
    protected $applied_geometry;

    /**
     * The value for the ledger_reset_at field.
     * @var        string
     */
    protected $ledger_reset_at;

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
     * @var        PropelObjectCollection|BotOrder[] Collection to store aggregation of BotOrder objects.
     */
    protected $collBotOrders;
    protected $collBotOrdersPartial;

    /**
     * @var        PropelObjectCollection|TradeCycle[] Collection to store aggregation of TradeCycle objects.
     */
    protected $collTradeCycles;
    protected $collTradeCyclesPartial;

    /**
     * @var        PropelObjectCollection|BotEvent[] Collection to store aggregation of BotEvent objects.
     */
    protected $collBotEvents;
    protected $collBotEventsPartial;

    /**
     * @var        PropelObjectCollection|BotCommand[] Collection to store aggregation of BotCommand objects.
     */
    protected $collBotCommands;
    protected $collBotCommandsPartial;

    /**
     * @var        PropelObjectCollection|BotDecision[] Collection to store aggregation of BotDecision objects.
     */
    protected $collBotDecisions;
    protected $collBotDecisionsPartial;

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
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $botOrdersScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $tradeCyclesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $botEventsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $botCommandsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $botDecisionsScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see        __construct()
     */
    public function applyDefaultValues()
    {
        $this->symbol = 'BTCUSDT';
        $this->status = 0;
        $this->kill_switch = false;
        $this->profile = 2;
        $this->algo = 0;
        $this->simulated = true;
        $this->n_levels = 20;
        $this->spacing = 0;
        $this->allocation = 0;
        $this->deploy_pct = 100;
        $this->fee_pct = '0.001';
        $this->breakout_buffer_pct = '0.02';
        $this->breakout_policy = 0;
        $this->max_open_orders = 60;
        $this->trend_tf = 0;
        $this->donchian_period = 20;
        $this->trend_ema_fast = 20;
        $this->trend_ema_slow = 50;
        $this->atr_period = 14;
    }

    /**
     * Initializes internal state of BaseGridRun object.
     * @see        applyDefaults()
     */
    public function __construct()
    {
        parent::__construct();
        $this->applyDefaultValues();
    }

    /**
     * @Field()
     * Get the [id_grid_run] column value.
     *
     * @return int
     */
    public function getIdGridRun()
    {

        return $this->id_grid_run;
    }

    /**
     * @Field()
     * Get the [label] column value.
     * Label
     * @return string
     */
    public function getLabel()
    {

        return $this->label;
    }

    /**
     * @Field()
     * Get the [symbol] column value.
     * Symbol
     * @return string
     */
    public function getSymbol()
    {

        return $this->symbol;
    }

    /**
     * @Field()
     * Get the [status] column value.
     * Status
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getStatus()
    {
        if (null === $this->status) {
            return null;
        }
        $valueSet = GridRunPeer::getValueSet(GridRunPeer::STATUS);
        if (!isset($valueSet[$this->status])) {
            throw new PropelException('Unknown stored enum key: ' . $this->status);
        }

        return $valueSet[$this->status];
    }

    /**
     * @Field()
     * Get the [kill_switch] column value.
     * Kill switch
     * @return boolean
     */
    public function getKillSwitch()
    {

        return $this->kill_switch;
    }

    /**
     * @Field()
     * Get the [profile] column value.
     * Risk profile
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getProfile()
    {
        if (null === $this->profile) {
            return null;
        }
        $valueSet = GridRunPeer::getValueSet(GridRunPeer::PROFILE);
        if (!isset($valueSet[$this->profile])) {
            throw new PropelException('Unknown stored enum key: ' . $this->profile);
        }

        return $valueSet[$this->profile];
    }

    /**
     * @Field()
     * Get the [algo] column value.
     * Algorithm
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getAlgo()
    {
        if (null === $this->algo) {
            return null;
        }
        $valueSet = GridRunPeer::getValueSet(GridRunPeer::ALGO);
        if (!isset($valueSet[$this->algo])) {
            throw new PropelException('Unknown stored enum key: ' . $this->algo);
        }

        return $valueSet[$this->algo];
    }

    /**
     * @Field()
     * Get the [simulated] column value.
     * Simulated
     * @return boolean
     */
    public function getSimulated()
    {

        return $this->simulated;
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
     * Grid lines
     * @return int
     */
    public function getNLevels()
    {

        return $this->n_levels;
    }

    /**
     * @Field()
     * Get the [spacing] column value.
     * Spacing
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getSpacing()
    {
        if (null === $this->spacing) {
            return null;
        }
        $valueSet = GridRunPeer::getValueSet(GridRunPeer::SPACING);
        if (!isset($valueSet[$this->spacing])) {
            throw new PropelException('Unknown stored enum key: ' . $this->spacing);
        }

        return $valueSet[$this->spacing];
    }

    /**
     * @Field()
     * Get the [allocation] column value.
     * Allocation
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getAllocation()
    {
        if (null === $this->allocation) {
            return null;
        }
        $valueSet = GridRunPeer::getValueSet(GridRunPeer::ALLOCATION);
        if (!isset($valueSet[$this->allocation])) {
            throw new PropelException('Unknown stored enum key: ' . $this->allocation);
        }

        return $valueSet[$this->allocation];
    }

    /**
     * @Field()
     * Get the [budget_quote] column value.
     * Budget (USDT)
     * @return string
     */
    public function getBudgetQuote()
    {

        return $this->budget_quote;
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
     * Get the [fee_pct] column value.
     * Fee per side
     * @return string
     */
    public function getFeePct()
    {

        return $this->fee_pct;
    }

    /**
     * @Field()
     * Get the [max_position_quote] column value.
     * Max position (USDT)
     * @return string
     */
    public function getMaxPositionQuote()
    {

        return $this->max_position_quote;
    }

    /**
     * @Field()
     * Get the [max_order_quote] column value.
     * Max per-order (USDT)
     * @return string
     */
    public function getMaxOrderQuote()
    {

        return $this->max_order_quote;
    }

    /**
     * @Field()
     * Get the [daily_loss_limit_quote] column value.
     * Daily loss limit
     * @return string
     */
    public function getDailyLossLimitQuote()
    {

        return $this->daily_loss_limit_quote;
    }

    /**
     * @Field()
     * Get the [max_unrealized_loss_quote] column value.
     * Max unrealized loss
     * @return string
     */
    public function getMaxUnrealizedLossQuote()
    {

        return $this->max_unrealized_loss_quote;
    }

    /**
     * @Field()
     * Get the [breakout_buffer_pct] column value.
     * Breakout buffer
     * @return string
     */
    public function getBreakoutBufferPct()
    {

        return $this->breakout_buffer_pct;
    }

    /**
     * @Field()
     * Get the [breakout_policy] column value.
     * On breakout
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getBreakoutPolicy()
    {
        if (null === $this->breakout_policy) {
            return null;
        }
        $valueSet = GridRunPeer::getValueSet(GridRunPeer::BREAKOUT_POLICY);
        if (!isset($valueSet[$this->breakout_policy])) {
            throw new PropelException('Unknown stored enum key: ' . $this->breakout_policy);
        }

        return $valueSet[$this->breakout_policy];
    }

    /**
     * @Field()
     * Get the [max_open_orders] column value.
     * Max open orders
     * @return int
     */
    public function getMaxOpenOrders()
    {

        return $this->max_open_orders;
    }

    /**
     * @Field()
     * Get the [max_buy_levels_below] column value.
     * Active buy levels below price (0 = all)
     * @return int
     */
    public function getMaxBuyLevelsBelow()
    {

        return $this->max_buy_levels_below;
    }

    /**
     * @Field()
     * Get the [trend_tf] column value.
     * Signal timeframe
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getTrendTf()
    {
        if (null === $this->trend_tf) {
            return null;
        }
        $valueSet = GridRunPeer::getValueSet(GridRunPeer::TREND_TF);
        if (!isset($valueSet[$this->trend_tf])) {
            throw new PropelException('Unknown stored enum key: ' . $this->trend_tf);
        }

        return $valueSet[$this->trend_tf];
    }

    /**
     * @Field()
     * Get the [donchian_period] column value.
     * Breakout period
     * @return int
     */
    public function getDonchianPeriod()
    {

        return $this->donchian_period;
    }

    /**
     * @Field()
     * Get the [trend_ema_fast] column value.
     * Trend EMA fast
     * @return int
     */
    public function getTrendEmaFast()
    {

        return $this->trend_ema_fast;
    }

    /**
     * @Field()
     * Get the [trend_ema_slow] column value.
     * Trend EMA slow
     * @return int
     */
    public function getTrendEmaSlow()
    {

        return $this->trend_ema_slow;
    }

    /**
     * @Field()
     * Get the [atr_period] column value.
     * ATR period
     * @return int
     */
    public function getAtrPeriod()
    {

        return $this->atr_period;
    }

    /**
     * @Field()
     * Get the [atr_stop_mult] column value.
     * Trail stop x ATR
     * @return string
     */
    public function getAtrStopMult()
    {

        return $this->atr_stop_mult;
    }

    /**
     * @Field()
     * Get the [atr_initial_mult] column value.
     * Initial stop x ATR
     * @return string
     */
    public function getAtrInitialMult()
    {

        return $this->atr_initial_mult;
    }

    /**
     * @Field()
     * Get the [reentry_cooldown] column value.
     * Re-entry cooldown (bars)
     * @return int
     */
    public function getReentryCooldown()
    {

        return $this->reentry_cooldown;
    }

    /**
     * @Field()
     * Get the [engine_state] column value.
     * Engine state (daemon-managed)
     * @return string
     */
    public function getEngineState()
    {

        return $this->engine_state;
    }

    /**
     * @Field()
     * Get the [optionally formatted] temporal [last_tick_at] column value.
     * Last tick
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw DateTime object will be returned.
     * @return mixed Formatted date/time value as string or DateTime object (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getLastTickAt($format = 'Y-m-d H:i:s')
    {
        if ($this->last_tick_at === null) {
            return null;
        }

        if ($this->last_tick_at === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->last_tick_at);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->last_tick_at, true), $x);
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
     * Get the [last_price] column value.
     * Last price
     * @return string
     */
    public function getLastPrice()
    {

        return $this->last_price;
    }

    /**
     * @Field()
     * Get the [bal_base] column value.
     * Wallet base
     * @return string
     */
    public function getBalBase()
    {

        return $this->bal_base;
    }

    /**
     * @Field()
     * Get the [bal_quote] column value.
     * Wallet quote
     * @return string
     */
    public function getBalQuote()
    {

        return $this->bal_quote;
    }

    /**
     * @Field()
     * Get the [sim_bal_base] column value.
     * Paper wallet base (daemon-managed)
     * @return string
     */
    public function getSimBalBase()
    {

        return $this->sim_bal_base;
    }

    /**
     * @Field()
     * Get the [sim_bal_quote] column value.
     * Paper wallet quote (daemon-managed)
     * @return string
     */
    public function getSimBalQuote()
    {

        return $this->sim_bal_quote;
    }

    /**
     * @Field()
     * Get the [run_uid] column value.
     * Run UID
     * @return string
     */
    public function getRunUid()
    {

        return $this->run_uid;
    }

    /**
     * @Field()
     * Get the [applied_geometry] column value.
     * Applied geometry (daemon-managed)
     * @return string
     */
    public function getAppliedGeometry()
    {

        return $this->applied_geometry;
    }

    /**
     * @Field()
     * Get the [optionally formatted] temporal [ledger_reset_at] column value.
     * Ledger rebased at (daemon-managed)
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw DateTime object will be returned.
     * @return mixed Formatted date/time value as string or DateTime object (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getLedgerResetAt($format = 'Y-m-d H:i:s')
    {
        if ($this->ledger_reset_at === null) {
            return null;
        }

        if ($this->ledger_reset_at === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->ledger_reset_at);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->ledger_reset_at, true), $x);
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
     * Set the value of [id_grid_run] column.
     *
     * @param  int $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setIdGridRun($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_grid_run !== $v) {
            $this->id_grid_run = $v;
            $this->modifiedColumns[] = GridRunPeer::ID_GRID_RUN;
        }


        return $this;
    } // setIdGridRun()

    /**
     * Set the value of [label] column.
     * Label
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setLabel($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->label !== $v) {
            $this->label = $v;
            $this->modifiedColumns[] = GridRunPeer::LABEL;
        }


        return $this;
    } // setLabel()

    /**
     * Set the value of [symbol] column.
     * Symbol
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setSymbol($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->symbol !== $v) {
            $this->symbol = $v;
            $this->modifiedColumns[] = GridRunPeer::SYMBOL;
        }


        return $this;
    } // setSymbol()

    /**
     * Set the value of [status] column.
     * Status
     * @param  int $v new value
     * @return GridRun The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setStatus($v)
    {
        if ($v !== null) {
            $valueSet = GridRunPeer::getValueSet(GridRunPeer::STATUS);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->status !== $v) {
            $this->status = $v;
            $this->modifiedColumns[] = GridRunPeer::STATUS;
        }


        return $this;
    } // setStatus()

    /**
     * Sets the value of the [kill_switch] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * Kill switch
     * @param boolean|integer|string $v The new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setKillSwitch($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->kill_switch !== $v) {
            $this->kill_switch = $v;
            $this->modifiedColumns[] = GridRunPeer::KILL_SWITCH;
        }


        return $this;
    } // setKillSwitch()

    /**
     * Set the value of [profile] column.
     * Risk profile
     * @param  int $v new value
     * @return GridRun The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setProfile($v)
    {
        if ($v !== null) {
            $valueSet = GridRunPeer::getValueSet(GridRunPeer::PROFILE);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->profile !== $v) {
            $this->profile = $v;
            $this->modifiedColumns[] = GridRunPeer::PROFILE;
        }


        return $this;
    } // setProfile()

    /**
     * Set the value of [algo] column.
     * Algorithm
     * @param  int $v new value
     * @return GridRun The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setAlgo($v)
    {
        if ($v !== null) {
            $valueSet = GridRunPeer::getValueSet(GridRunPeer::ALGO);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->algo !== $v) {
            $this->algo = $v;
            $this->modifiedColumns[] = GridRunPeer::ALGO;
        }


        return $this;
    } // setAlgo()

    /**
     * Sets the value of the [simulated] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * Simulated
     * @param boolean|integer|string $v The new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setSimulated($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->simulated !== $v) {
            $this->simulated = $v;
            $this->modifiedColumns[] = GridRunPeer::SIMULATED;
        }


        return $this;
    } // setSimulated()

    /**
     * Set the value of [p_low] column.
     * Range low
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setPLow($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->p_low !== $v) {
            $this->p_low = $v;
            $this->modifiedColumns[] = GridRunPeer::P_LOW;
        }


        return $this;
    } // setPLow()

    /**
     * Set the value of [p_high] column.
     * Range high
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setPHigh($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->p_high !== $v) {
            $this->p_high = $v;
            $this->modifiedColumns[] = GridRunPeer::P_HIGH;
        }


        return $this;
    } // setPHigh()

    /**
     * Set the value of [n_levels] column.
     * Grid lines
     * @param  int $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setNLevels($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->n_levels !== $v) {
            $this->n_levels = $v;
            $this->modifiedColumns[] = GridRunPeer::N_LEVELS;
        }


        return $this;
    } // setNLevels()

    /**
     * Set the value of [spacing] column.
     * Spacing
     * @param  int $v new value
     * @return GridRun The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setSpacing($v)
    {
        if ($v !== null) {
            $valueSet = GridRunPeer::getValueSet(GridRunPeer::SPACING);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->spacing !== $v) {
            $this->spacing = $v;
            $this->modifiedColumns[] = GridRunPeer::SPACING;
        }


        return $this;
    } // setSpacing()

    /**
     * Set the value of [allocation] column.
     * Allocation
     * @param  int $v new value
     * @return GridRun The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setAllocation($v)
    {
        if ($v !== null) {
            $valueSet = GridRunPeer::getValueSet(GridRunPeer::ALLOCATION);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->allocation !== $v) {
            $this->allocation = $v;
            $this->modifiedColumns[] = GridRunPeer::ALLOCATION;
        }


        return $this;
    } // setAllocation()

    /**
     * Set the value of [budget_quote] column.
     * Budget (USDT)
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setBudgetQuote($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->budget_quote !== $v) {
            $this->budget_quote = $v;
            $this->modifiedColumns[] = GridRunPeer::BUDGET_QUOTE;
        }


        return $this;
    } // setBudgetQuote()

    /**
     * Set the value of [deploy_pct] column.
     * Deployed budget %
     * @param  int $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setDeployPct($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->deploy_pct !== $v) {
            $this->deploy_pct = $v;
            $this->modifiedColumns[] = GridRunPeer::DEPLOY_PCT;
        }


        return $this;
    } // setDeployPct()

    /**
     * Set the value of [fee_pct] column.
     * Fee per side
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setFeePct($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->fee_pct !== $v) {
            $this->fee_pct = $v;
            $this->modifiedColumns[] = GridRunPeer::FEE_PCT;
        }


        return $this;
    } // setFeePct()

    /**
     * Set the value of [max_position_quote] column.
     * Max position (USDT)
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setMaxPositionQuote($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->max_position_quote !== $v) {
            $this->max_position_quote = $v;
            $this->modifiedColumns[] = GridRunPeer::MAX_POSITION_QUOTE;
        }


        return $this;
    } // setMaxPositionQuote()

    /**
     * Set the value of [max_order_quote] column.
     * Max per-order (USDT)
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setMaxOrderQuote($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->max_order_quote !== $v) {
            $this->max_order_quote = $v;
            $this->modifiedColumns[] = GridRunPeer::MAX_ORDER_QUOTE;
        }


        return $this;
    } // setMaxOrderQuote()

    /**
     * Set the value of [daily_loss_limit_quote] column.
     * Daily loss limit
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setDailyLossLimitQuote($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->daily_loss_limit_quote !== $v) {
            $this->daily_loss_limit_quote = $v;
            $this->modifiedColumns[] = GridRunPeer::DAILY_LOSS_LIMIT_QUOTE;
        }


        return $this;
    } // setDailyLossLimitQuote()

    /**
     * Set the value of [max_unrealized_loss_quote] column.
     * Max unrealized loss
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setMaxUnrealizedLossQuote($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->max_unrealized_loss_quote !== $v) {
            $this->max_unrealized_loss_quote = $v;
            $this->modifiedColumns[] = GridRunPeer::MAX_UNREALIZED_LOSS_QUOTE;
        }


        return $this;
    } // setMaxUnrealizedLossQuote()

    /**
     * Set the value of [breakout_buffer_pct] column.
     * Breakout buffer
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setBreakoutBufferPct($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->breakout_buffer_pct !== $v) {
            $this->breakout_buffer_pct = $v;
            $this->modifiedColumns[] = GridRunPeer::BREAKOUT_BUFFER_PCT;
        }


        return $this;
    } // setBreakoutBufferPct()

    /**
     * Set the value of [breakout_policy] column.
     * On breakout
     * @param  int $v new value
     * @return GridRun The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setBreakoutPolicy($v)
    {
        if ($v !== null) {
            $valueSet = GridRunPeer::getValueSet(GridRunPeer::BREAKOUT_POLICY);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->breakout_policy !== $v) {
            $this->breakout_policy = $v;
            $this->modifiedColumns[] = GridRunPeer::BREAKOUT_POLICY;
        }


        return $this;
    } // setBreakoutPolicy()

    /**
     * Set the value of [max_open_orders] column.
     * Max open orders
     * @param  int $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setMaxOpenOrders($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->max_open_orders !== $v) {
            $this->max_open_orders = $v;
            $this->modifiedColumns[] = GridRunPeer::MAX_OPEN_ORDERS;
        }


        return $this;
    } // setMaxOpenOrders()

    /**
     * Set the value of [max_buy_levels_below] column.
     * Active buy levels below price (0 = all)
     * @param  int $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setMaxBuyLevelsBelow($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->max_buy_levels_below !== $v) {
            $this->max_buy_levels_below = $v;
            $this->modifiedColumns[] = GridRunPeer::MAX_BUY_LEVELS_BELOW;
        }


        return $this;
    } // setMaxBuyLevelsBelow()

    /**
     * Set the value of [trend_tf] column.
     * Signal timeframe
     * @param  int $v new value
     * @return GridRun The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setTrendTf($v)
    {
        if ($v !== null) {
            $valueSet = GridRunPeer::getValueSet(GridRunPeer::TREND_TF);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->trend_tf !== $v) {
            $this->trend_tf = $v;
            $this->modifiedColumns[] = GridRunPeer::TREND_TF;
        }


        return $this;
    } // setTrendTf()

    /**
     * Set the value of [donchian_period] column.
     * Breakout period
     * @param  int $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setDonchianPeriod($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->donchian_period !== $v) {
            $this->donchian_period = $v;
            $this->modifiedColumns[] = GridRunPeer::DONCHIAN_PERIOD;
        }


        return $this;
    } // setDonchianPeriod()

    /**
     * Set the value of [trend_ema_fast] column.
     * Trend EMA fast
     * @param  int $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setTrendEmaFast($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->trend_ema_fast !== $v) {
            $this->trend_ema_fast = $v;
            $this->modifiedColumns[] = GridRunPeer::TREND_EMA_FAST;
        }


        return $this;
    } // setTrendEmaFast()

    /**
     * Set the value of [trend_ema_slow] column.
     * Trend EMA slow
     * @param  int $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setTrendEmaSlow($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->trend_ema_slow !== $v) {
            $this->trend_ema_slow = $v;
            $this->modifiedColumns[] = GridRunPeer::TREND_EMA_SLOW;
        }


        return $this;
    } // setTrendEmaSlow()

    /**
     * Set the value of [atr_period] column.
     * ATR period
     * @param  int $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setAtrPeriod($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->atr_period !== $v) {
            $this->atr_period = $v;
            $this->modifiedColumns[] = GridRunPeer::ATR_PERIOD;
        }


        return $this;
    } // setAtrPeriod()

    /**
     * Set the value of [atr_stop_mult] column.
     * Trail stop x ATR
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setAtrStopMult($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->atr_stop_mult !== $v) {
            $this->atr_stop_mult = $v;
            $this->modifiedColumns[] = GridRunPeer::ATR_STOP_MULT;
        }


        return $this;
    } // setAtrStopMult()

    /**
     * Set the value of [atr_initial_mult] column.
     * Initial stop x ATR
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setAtrInitialMult($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->atr_initial_mult !== $v) {
            $this->atr_initial_mult = $v;
            $this->modifiedColumns[] = GridRunPeer::ATR_INITIAL_MULT;
        }


        return $this;
    } // setAtrInitialMult()

    /**
     * Set the value of [reentry_cooldown] column.
     * Re-entry cooldown (bars)
     * @param  int $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setReentryCooldown($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->reentry_cooldown !== $v) {
            $this->reentry_cooldown = $v;
            $this->modifiedColumns[] = GridRunPeer::REENTRY_COOLDOWN;
        }


        return $this;
    } // setReentryCooldown()

    /**
     * Set the value of [engine_state] column.
     * Engine state (daemon-managed)
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setEngineState($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->engine_state !== $v) {
            $this->engine_state = $v;
            $this->modifiedColumns[] = GridRunPeer::ENGINE_STATE;
        }


        return $this;
    } // setEngineState()

    /**
     * Sets the value of [last_tick_at] column to a normalized version of the date/time value specified.
     * Last tick
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return GridRun The current object (for fluent API support)
     */
    public function setLastTickAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->last_tick_at !== null || $dt !== null) {
            $currentDateAsString = ($this->last_tick_at !== null && $tmpDt = new DateTime($this->last_tick_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->last_tick_at = $newDateAsString;
                $this->modifiedColumns[] = GridRunPeer::LAST_TICK_AT;
            }
        } // if either are not null


        return $this;
    } // setLastTickAt()

    /**
     * Set the value of [last_price] column.
     * Last price
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setLastPrice($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->last_price !== $v) {
            $this->last_price = $v;
            $this->modifiedColumns[] = GridRunPeer::LAST_PRICE;
        }


        return $this;
    } // setLastPrice()

    /**
     * Set the value of [bal_base] column.
     * Wallet base
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setBalBase($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->bal_base !== $v) {
            $this->bal_base = $v;
            $this->modifiedColumns[] = GridRunPeer::BAL_BASE;
        }


        return $this;
    } // setBalBase()

    /**
     * Set the value of [bal_quote] column.
     * Wallet quote
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setBalQuote($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->bal_quote !== $v) {
            $this->bal_quote = $v;
            $this->modifiedColumns[] = GridRunPeer::BAL_QUOTE;
        }


        return $this;
    } // setBalQuote()

    /**
     * Set the value of [sim_bal_base] column.
     * Paper wallet base (daemon-managed)
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setSimBalBase($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->sim_bal_base !== $v) {
            $this->sim_bal_base = $v;
            $this->modifiedColumns[] = GridRunPeer::SIM_BAL_BASE;
        }


        return $this;
    } // setSimBalBase()

    /**
     * Set the value of [sim_bal_quote] column.
     * Paper wallet quote (daemon-managed)
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setSimBalQuote($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->sim_bal_quote !== $v) {
            $this->sim_bal_quote = $v;
            $this->modifiedColumns[] = GridRunPeer::SIM_BAL_QUOTE;
        }


        return $this;
    } // setSimBalQuote()

    /**
     * Set the value of [run_uid] column.
     * Run UID
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setRunUid($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->run_uid !== $v) {
            $this->run_uid = $v;
            $this->modifiedColumns[] = GridRunPeer::RUN_UID;
        }


        return $this;
    } // setRunUid()

    /**
     * Set the value of [applied_geometry] column.
     * Applied geometry (daemon-managed)
     * @param  string $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setAppliedGeometry($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->applied_geometry !== $v) {
            $this->applied_geometry = $v;
            $this->modifiedColumns[] = GridRunPeer::APPLIED_GEOMETRY;
        }


        return $this;
    } // setAppliedGeometry()

    /**
     * Sets the value of [ledger_reset_at] column to a normalized version of the date/time value specified.
     * Ledger rebased at (daemon-managed)
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return GridRun The current object (for fluent API support)
     */
    public function setLedgerResetAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->ledger_reset_at !== null || $dt !== null) {
            $currentDateAsString = ($this->ledger_reset_at !== null && $tmpDt = new DateTime($this->ledger_reset_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->ledger_reset_at = $newDateAsString;
                $this->modifiedColumns[] = GridRunPeer::LEDGER_RESET_AT;
            }
        } // if either are not null


        return $this;
    } // setLedgerResetAt()

    /**
     * Sets the value of [date_creation] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return GridRun The current object (for fluent API support)
     */
    public function setDateCreation($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_creation !== null || $dt !== null) {
            $currentDateAsString = ($this->date_creation !== null && $tmpDt = new DateTime($this->date_creation)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_creation = $newDateAsString;
                $this->modifiedColumns[] = GridRunPeer::DATE_CREATION;
            }
        } // if either are not null


        return $this;
    } // setDateCreation()

    /**
     * Sets the value of [date_modification] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return GridRun The current object (for fluent API support)
     */
    public function setDateModification($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_modification !== null || $dt !== null) {
            $currentDateAsString = ($this->date_modification !== null && $tmpDt = new DateTime($this->date_modification)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_modification = $newDateAsString;
                $this->modifiedColumns[] = GridRunPeer::DATE_MODIFICATION;
            }
        } // if either are not null


        return $this;
    } // setDateModification()

    /**
     * Set the value of [id_group_creation] column.
     *
     * @param  int $v new value
     * @return GridRun The current object (for fluent API support)
     */
    public function setIdGroupCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_group_creation !== $v) {
            $this->id_group_creation = $v;
            $this->modifiedColumns[] = GridRunPeer::ID_GROUP_CREATION;
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
     * @return GridRun The current object (for fluent API support)
     */
    public function setIdCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_creation !== $v) {
            $this->id_creation = $v;
            $this->modifiedColumns[] = GridRunPeer::ID_CREATION;
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
     * @return GridRun The current object (for fluent API support)
     */
    public function setIdModification($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_modification !== $v) {
            $this->id_modification = $v;
            $this->modifiedColumns[] = GridRunPeer::ID_MODIFICATION;
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
            if ($this->symbol !== 'BTCUSDT') {
                return false;
            }

            if ($this->status !== 0) {
                return false;
            }

            if ($this->kill_switch !== false) {
                return false;
            }

            if ($this->profile !== 2) {
                return false;
            }

            if ($this->algo !== 0) {
                return false;
            }

            if ($this->simulated !== true) {
                return false;
            }

            if ($this->n_levels !== 20) {
                return false;
            }

            if ($this->spacing !== 0) {
                return false;
            }

            if ($this->allocation !== 0) {
                return false;
            }

            if ($this->deploy_pct !== 100) {
                return false;
            }

            if ($this->fee_pct !== '0.001') {
                return false;
            }

            if ($this->breakout_buffer_pct !== '0.02') {
                return false;
            }

            if ($this->breakout_policy !== 0) {
                return false;
            }

            if ($this->max_open_orders !== 60) {
                return false;
            }

            if ($this->trend_tf !== 0) {
                return false;
            }

            if ($this->donchian_period !== 20) {
                return false;
            }

            if ($this->trend_ema_fast !== 20) {
                return false;
            }

            if ($this->trend_ema_slow !== 50) {
                return false;
            }

            if ($this->atr_period !== 14) {
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

            $this->id_grid_run = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
            $this->label = ($row[$startcol + 1] !== null) ? (string) $row[$startcol + 1] : null;
            $this->symbol = ($row[$startcol + 2] !== null) ? (string) $row[$startcol + 2] : null;
            $this->status = ($row[$startcol + 3] !== null) ? (int) $row[$startcol + 3] : null;
            $this->kill_switch = ($row[$startcol + 4] !== null) ? (boolean) $row[$startcol + 4] : null;
            $this->profile = ($row[$startcol + 5] !== null) ? (int) $row[$startcol + 5] : null;
            $this->algo = ($row[$startcol + 6] !== null) ? (int) $row[$startcol + 6] : null;
            $this->simulated = ($row[$startcol + 7] !== null) ? (boolean) $row[$startcol + 7] : null;
            $this->p_low = ($row[$startcol + 8] !== null) ? (string) $row[$startcol + 8] : null;
            $this->p_high = ($row[$startcol + 9] !== null) ? (string) $row[$startcol + 9] : null;
            $this->n_levels = ($row[$startcol + 10] !== null) ? (int) $row[$startcol + 10] : null;
            $this->spacing = ($row[$startcol + 11] !== null) ? (int) $row[$startcol + 11] : null;
            $this->allocation = ($row[$startcol + 12] !== null) ? (int) $row[$startcol + 12] : null;
            $this->budget_quote = ($row[$startcol + 13] !== null) ? (string) $row[$startcol + 13] : null;
            $this->deploy_pct = ($row[$startcol + 14] !== null) ? (int) $row[$startcol + 14] : null;
            $this->fee_pct = ($row[$startcol + 15] !== null) ? (string) $row[$startcol + 15] : null;
            $this->max_position_quote = ($row[$startcol + 16] !== null) ? (string) $row[$startcol + 16] : null;
            $this->max_order_quote = ($row[$startcol + 17] !== null) ? (string) $row[$startcol + 17] : null;
            $this->daily_loss_limit_quote = ($row[$startcol + 18] !== null) ? (string) $row[$startcol + 18] : null;
            $this->max_unrealized_loss_quote = ($row[$startcol + 19] !== null) ? (string) $row[$startcol + 19] : null;
            $this->breakout_buffer_pct = ($row[$startcol + 20] !== null) ? (string) $row[$startcol + 20] : null;
            $this->breakout_policy = ($row[$startcol + 21] !== null) ? (int) $row[$startcol + 21] : null;
            $this->max_open_orders = ($row[$startcol + 22] !== null) ? (int) $row[$startcol + 22] : null;
            $this->max_buy_levels_below = ($row[$startcol + 23] !== null) ? (int) $row[$startcol + 23] : null;
            $this->trend_tf = ($row[$startcol + 24] !== null) ? (int) $row[$startcol + 24] : null;
            $this->donchian_period = ($row[$startcol + 25] !== null) ? (int) $row[$startcol + 25] : null;
            $this->trend_ema_fast = ($row[$startcol + 26] !== null) ? (int) $row[$startcol + 26] : null;
            $this->trend_ema_slow = ($row[$startcol + 27] !== null) ? (int) $row[$startcol + 27] : null;
            $this->atr_period = ($row[$startcol + 28] !== null) ? (int) $row[$startcol + 28] : null;
            $this->atr_stop_mult = ($row[$startcol + 29] !== null) ? (string) $row[$startcol + 29] : null;
            $this->atr_initial_mult = ($row[$startcol + 30] !== null) ? (string) $row[$startcol + 30] : null;
            $this->reentry_cooldown = ($row[$startcol + 31] !== null) ? (int) $row[$startcol + 31] : null;
            $this->engine_state = ($row[$startcol + 32] !== null) ? (string) $row[$startcol + 32] : null;
            $this->last_tick_at = ($row[$startcol + 33] !== null) ? (string) $row[$startcol + 33] : null;
            $this->last_price = ($row[$startcol + 34] !== null) ? (string) $row[$startcol + 34] : null;
            $this->bal_base = ($row[$startcol + 35] !== null) ? (string) $row[$startcol + 35] : null;
            $this->bal_quote = ($row[$startcol + 36] !== null) ? (string) $row[$startcol + 36] : null;
            $this->sim_bal_base = ($row[$startcol + 37] !== null) ? (string) $row[$startcol + 37] : null;
            $this->sim_bal_quote = ($row[$startcol + 38] !== null) ? (string) $row[$startcol + 38] : null;
            $this->run_uid = ($row[$startcol + 39] !== null) ? (string) $row[$startcol + 39] : null;
            $this->applied_geometry = ($row[$startcol + 40] !== null) ? (string) $row[$startcol + 40] : null;
            $this->ledger_reset_at = ($row[$startcol + 41] !== null) ? (string) $row[$startcol + 41] : null;
            $this->date_creation = ($row[$startcol + 42] !== null) ? (string) $row[$startcol + 42] : null;
            $this->date_modification = ($row[$startcol + 43] !== null) ? (string) $row[$startcol + 43] : null;
            $this->id_group_creation = ($row[$startcol + 44] !== null) ? (int) $row[$startcol + 44] : null;
            $this->id_creation = ($row[$startcol + 45] !== null) ? (int) $row[$startcol + 45] : null;
            $this->id_modification = ($row[$startcol + 46] !== null) ? (int) $row[$startcol + 46] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);

            return $startcol + 47; // 47 = GridRunPeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating GridRun object", $e);
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
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = GridRunPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aAuthyGroup = null;
            $this->aAuthyRelatedByIdCreation = null;
            $this->aAuthyRelatedByIdModification = null;
            $this->collBotOrders = null;

            $this->collTradeCycles = null;

            $this->collBotEvents = null;

            $this->collBotCommands = null;

            $this->collBotDecisions = null;

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
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = GridRunQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                // GoatCheese behavior
                
                            if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                                \ApiGoat\Utility\TableVersion::bump('grid_run');
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
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
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
                                \ApiGoat\Utility\TableVersion::bump('grid_run');
                            }
                GridRunPeer::addInstanceToPool($this);
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

            if ($this->botOrdersScheduledForDeletion !== null) {
                if (!$this->botOrdersScheduledForDeletion->isEmpty()) {
                    BotOrderQuery::create()
                        ->filterByPrimaryKeys($this->botOrdersScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->botOrdersScheduledForDeletion = null;
                }
            }

            if ($this->collBotOrders !== null) {
                foreach ($this->collBotOrders as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->tradeCyclesScheduledForDeletion !== null) {
                if (!$this->tradeCyclesScheduledForDeletion->isEmpty()) {
                    TradeCycleQuery::create()
                        ->filterByPrimaryKeys($this->tradeCyclesScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->tradeCyclesScheduledForDeletion = null;
                }
            }

            if ($this->collTradeCycles !== null) {
                foreach ($this->collTradeCycles as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->botEventsScheduledForDeletion !== null) {
                if (!$this->botEventsScheduledForDeletion->isEmpty()) {
                    BotEventQuery::create()
                        ->filterByPrimaryKeys($this->botEventsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->botEventsScheduledForDeletion = null;
                }
            }

            if ($this->collBotEvents !== null) {
                foreach ($this->collBotEvents as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->botCommandsScheduledForDeletion !== null) {
                if (!$this->botCommandsScheduledForDeletion->isEmpty()) {
                    BotCommandQuery::create()
                        ->filterByPrimaryKeys($this->botCommandsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->botCommandsScheduledForDeletion = null;
                }
            }

            if ($this->collBotCommands !== null) {
                foreach ($this->collBotCommands as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->botDecisionsScheduledForDeletion !== null) {
                if (!$this->botDecisionsScheduledForDeletion->isEmpty()) {
                    BotDecisionQuery::create()
                        ->filterByPrimaryKeys($this->botDecisionsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->botDecisionsScheduledForDeletion = null;
                }
            }

            if ($this->collBotDecisions !== null) {
                foreach ($this->collBotDecisions as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
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

        $this->modifiedColumns[] = GridRunPeer::ID_GRID_RUN;
        if (null !== $this->id_grid_run) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . GridRunPeer::ID_GRID_RUN . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(GridRunPeer::ID_GRID_RUN)) {
            $modifiedColumns[':p' . $index++]  = '`id_grid_run`';
        }
        if ($this->isColumnModified(GridRunPeer::LABEL)) {
            $modifiedColumns[':p' . $index++]  = '`label`';
        }
        if ($this->isColumnModified(GridRunPeer::SYMBOL)) {
            $modifiedColumns[':p' . $index++]  = '`symbol`';
        }
        if ($this->isColumnModified(GridRunPeer::STATUS)) {
            $modifiedColumns[':p' . $index++]  = '`status`';
        }
        if ($this->isColumnModified(GridRunPeer::KILL_SWITCH)) {
            $modifiedColumns[':p' . $index++]  = '`kill_switch`';
        }
        if ($this->isColumnModified(GridRunPeer::PROFILE)) {
            $modifiedColumns[':p' . $index++]  = '`profile`';
        }
        if ($this->isColumnModified(GridRunPeer::ALGO)) {
            $modifiedColumns[':p' . $index++]  = '`algo`';
        }
        if ($this->isColumnModified(GridRunPeer::SIMULATED)) {
            $modifiedColumns[':p' . $index++]  = '`simulated`';
        }
        if ($this->isColumnModified(GridRunPeer::P_LOW)) {
            $modifiedColumns[':p' . $index++]  = '`p_low`';
        }
        if ($this->isColumnModified(GridRunPeer::P_HIGH)) {
            $modifiedColumns[':p' . $index++]  = '`p_high`';
        }
        if ($this->isColumnModified(GridRunPeer::N_LEVELS)) {
            $modifiedColumns[':p' . $index++]  = '`n_levels`';
        }
        if ($this->isColumnModified(GridRunPeer::SPACING)) {
            $modifiedColumns[':p' . $index++]  = '`spacing`';
        }
        if ($this->isColumnModified(GridRunPeer::ALLOCATION)) {
            $modifiedColumns[':p' . $index++]  = '`allocation`';
        }
        if ($this->isColumnModified(GridRunPeer::BUDGET_QUOTE)) {
            $modifiedColumns[':p' . $index++]  = '`budget_quote`';
        }
        if ($this->isColumnModified(GridRunPeer::DEPLOY_PCT)) {
            $modifiedColumns[':p' . $index++]  = '`deploy_pct`';
        }
        if ($this->isColumnModified(GridRunPeer::FEE_PCT)) {
            $modifiedColumns[':p' . $index++]  = '`fee_pct`';
        }
        if ($this->isColumnModified(GridRunPeer::MAX_POSITION_QUOTE)) {
            $modifiedColumns[':p' . $index++]  = '`max_position_quote`';
        }
        if ($this->isColumnModified(GridRunPeer::MAX_ORDER_QUOTE)) {
            $modifiedColumns[':p' . $index++]  = '`max_order_quote`';
        }
        if ($this->isColumnModified(GridRunPeer::DAILY_LOSS_LIMIT_QUOTE)) {
            $modifiedColumns[':p' . $index++]  = '`daily_loss_limit_quote`';
        }
        if ($this->isColumnModified(GridRunPeer::MAX_UNREALIZED_LOSS_QUOTE)) {
            $modifiedColumns[':p' . $index++]  = '`max_unrealized_loss_quote`';
        }
        if ($this->isColumnModified(GridRunPeer::BREAKOUT_BUFFER_PCT)) {
            $modifiedColumns[':p' . $index++]  = '`breakout_buffer_pct`';
        }
        if ($this->isColumnModified(GridRunPeer::BREAKOUT_POLICY)) {
            $modifiedColumns[':p' . $index++]  = '`breakout_policy`';
        }
        if ($this->isColumnModified(GridRunPeer::MAX_OPEN_ORDERS)) {
            $modifiedColumns[':p' . $index++]  = '`max_open_orders`';
        }
        if ($this->isColumnModified(GridRunPeer::MAX_BUY_LEVELS_BELOW)) {
            $modifiedColumns[':p' . $index++]  = '`max_buy_levels_below`';
        }
        if ($this->isColumnModified(GridRunPeer::TREND_TF)) {
            $modifiedColumns[':p' . $index++]  = '`trend_tf`';
        }
        if ($this->isColumnModified(GridRunPeer::DONCHIAN_PERIOD)) {
            $modifiedColumns[':p' . $index++]  = '`donchian_period`';
        }
        if ($this->isColumnModified(GridRunPeer::TREND_EMA_FAST)) {
            $modifiedColumns[':p' . $index++]  = '`trend_ema_fast`';
        }
        if ($this->isColumnModified(GridRunPeer::TREND_EMA_SLOW)) {
            $modifiedColumns[':p' . $index++]  = '`trend_ema_slow`';
        }
        if ($this->isColumnModified(GridRunPeer::ATR_PERIOD)) {
            $modifiedColumns[':p' . $index++]  = '`atr_period`';
        }
        if ($this->isColumnModified(GridRunPeer::ATR_STOP_MULT)) {
            $modifiedColumns[':p' . $index++]  = '`atr_stop_mult`';
        }
        if ($this->isColumnModified(GridRunPeer::ATR_INITIAL_MULT)) {
            $modifiedColumns[':p' . $index++]  = '`atr_initial_mult`';
        }
        if ($this->isColumnModified(GridRunPeer::REENTRY_COOLDOWN)) {
            $modifiedColumns[':p' . $index++]  = '`reentry_cooldown`';
        }
        if ($this->isColumnModified(GridRunPeer::ENGINE_STATE)) {
            $modifiedColumns[':p' . $index++]  = '`engine_state`';
        }
        if ($this->isColumnModified(GridRunPeer::LAST_TICK_AT)) {
            $modifiedColumns[':p' . $index++]  = '`last_tick_at`';
        }
        if ($this->isColumnModified(GridRunPeer::LAST_PRICE)) {
            $modifiedColumns[':p' . $index++]  = '`last_price`';
        }
        if ($this->isColumnModified(GridRunPeer::BAL_BASE)) {
            $modifiedColumns[':p' . $index++]  = '`bal_base`';
        }
        if ($this->isColumnModified(GridRunPeer::BAL_QUOTE)) {
            $modifiedColumns[':p' . $index++]  = '`bal_quote`';
        }
        if ($this->isColumnModified(GridRunPeer::SIM_BAL_BASE)) {
            $modifiedColumns[':p' . $index++]  = '`sim_bal_base`';
        }
        if ($this->isColumnModified(GridRunPeer::SIM_BAL_QUOTE)) {
            $modifiedColumns[':p' . $index++]  = '`sim_bal_quote`';
        }
        if ($this->isColumnModified(GridRunPeer::RUN_UID)) {
            $modifiedColumns[':p' . $index++]  = '`run_uid`';
        }
        if ($this->isColumnModified(GridRunPeer::APPLIED_GEOMETRY)) {
            $modifiedColumns[':p' . $index++]  = '`applied_geometry`';
        }
        if ($this->isColumnModified(GridRunPeer::LEDGER_RESET_AT)) {
            $modifiedColumns[':p' . $index++]  = '`ledger_reset_at`';
        }
        if ($this->isColumnModified(GridRunPeer::DATE_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_creation`';
        }
        if ($this->isColumnModified(GridRunPeer::DATE_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_modification`';
        }
        if ($this->isColumnModified(GridRunPeer::ID_GROUP_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_group_creation`';
        }
        if ($this->isColumnModified(GridRunPeer::ID_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_creation`';
        }
        if ($this->isColumnModified(GridRunPeer::ID_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_modification`';
        }

        $sql = sprintf(
            'INSERT INTO `grid_run` (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case '`id_grid_run`':
                        $stmt->bindValue($identifier, $this->id_grid_run, PDO::PARAM_INT);
                        break;
                    case '`label`':
                        $stmt->bindValue($identifier, $this->label, PDO::PARAM_STR);
                        break;
                    case '`symbol`':
                        $stmt->bindValue($identifier, $this->symbol, PDO::PARAM_STR);
                        break;
                    case '`status`':
                        $stmt->bindValue($identifier, $this->status, PDO::PARAM_INT);
                        break;
                    case '`kill_switch`':
                        $stmt->bindValue($identifier, (int) $this->kill_switch, PDO::PARAM_INT);
                        break;
                    case '`profile`':
                        $stmt->bindValue($identifier, $this->profile, PDO::PARAM_INT);
                        break;
                    case '`algo`':
                        $stmt->bindValue($identifier, $this->algo, PDO::PARAM_INT);
                        break;
                    case '`simulated`':
                        $stmt->bindValue($identifier, (int) $this->simulated, PDO::PARAM_INT);
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
                    case '`spacing`':
                        $stmt->bindValue($identifier, $this->spacing, PDO::PARAM_INT);
                        break;
                    case '`allocation`':
                        $stmt->bindValue($identifier, $this->allocation, PDO::PARAM_INT);
                        break;
                    case '`budget_quote`':
                        $stmt->bindValue($identifier, $this->budget_quote, PDO::PARAM_STR);
                        break;
                    case '`deploy_pct`':
                        $stmt->bindValue($identifier, $this->deploy_pct, PDO::PARAM_INT);
                        break;
                    case '`fee_pct`':
                        $stmt->bindValue($identifier, $this->fee_pct, PDO::PARAM_STR);
                        break;
                    case '`max_position_quote`':
                        $stmt->bindValue($identifier, $this->max_position_quote, PDO::PARAM_STR);
                        break;
                    case '`max_order_quote`':
                        $stmt->bindValue($identifier, $this->max_order_quote, PDO::PARAM_STR);
                        break;
                    case '`daily_loss_limit_quote`':
                        $stmt->bindValue($identifier, $this->daily_loss_limit_quote, PDO::PARAM_STR);
                        break;
                    case '`max_unrealized_loss_quote`':
                        $stmt->bindValue($identifier, $this->max_unrealized_loss_quote, PDO::PARAM_STR);
                        break;
                    case '`breakout_buffer_pct`':
                        $stmt->bindValue($identifier, $this->breakout_buffer_pct, PDO::PARAM_STR);
                        break;
                    case '`breakout_policy`':
                        $stmt->bindValue($identifier, $this->breakout_policy, PDO::PARAM_INT);
                        break;
                    case '`max_open_orders`':
                        $stmt->bindValue($identifier, $this->max_open_orders, PDO::PARAM_INT);
                        break;
                    case '`max_buy_levels_below`':
                        $stmt->bindValue($identifier, $this->max_buy_levels_below, PDO::PARAM_INT);
                        break;
                    case '`trend_tf`':
                        $stmt->bindValue($identifier, $this->trend_tf, PDO::PARAM_INT);
                        break;
                    case '`donchian_period`':
                        $stmt->bindValue($identifier, $this->donchian_period, PDO::PARAM_INT);
                        break;
                    case '`trend_ema_fast`':
                        $stmt->bindValue($identifier, $this->trend_ema_fast, PDO::PARAM_INT);
                        break;
                    case '`trend_ema_slow`':
                        $stmt->bindValue($identifier, $this->trend_ema_slow, PDO::PARAM_INT);
                        break;
                    case '`atr_period`':
                        $stmt->bindValue($identifier, $this->atr_period, PDO::PARAM_INT);
                        break;
                    case '`atr_stop_mult`':
                        $stmt->bindValue($identifier, $this->atr_stop_mult, PDO::PARAM_STR);
                        break;
                    case '`atr_initial_mult`':
                        $stmt->bindValue($identifier, $this->atr_initial_mult, PDO::PARAM_STR);
                        break;
                    case '`reentry_cooldown`':
                        $stmt->bindValue($identifier, $this->reentry_cooldown, PDO::PARAM_INT);
                        break;
                    case '`engine_state`':
                        $stmt->bindValue($identifier, $this->engine_state, PDO::PARAM_STR);
                        break;
                    case '`last_tick_at`':
                        $stmt->bindValue($identifier, $this->last_tick_at, PDO::PARAM_STR);
                        break;
                    case '`last_price`':
                        $stmt->bindValue($identifier, $this->last_price, PDO::PARAM_STR);
                        break;
                    case '`bal_base`':
                        $stmt->bindValue($identifier, $this->bal_base, PDO::PARAM_STR);
                        break;
                    case '`bal_quote`':
                        $stmt->bindValue($identifier, $this->bal_quote, PDO::PARAM_STR);
                        break;
                    case '`sim_bal_base`':
                        $stmt->bindValue($identifier, $this->sim_bal_base, PDO::PARAM_STR);
                        break;
                    case '`sim_bal_quote`':
                        $stmt->bindValue($identifier, $this->sim_bal_quote, PDO::PARAM_STR);
                        break;
                    case '`run_uid`':
                        $stmt->bindValue($identifier, $this->run_uid, PDO::PARAM_STR);
                        break;
                    case '`applied_geometry`':
                        $stmt->bindValue($identifier, $this->applied_geometry, PDO::PARAM_STR);
                        break;
                    case '`ledger_reset_at`':
                        $stmt->bindValue($identifier, $this->ledger_reset_at, PDO::PARAM_STR);
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
        $this->setIdGridRun($pk);

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


            if (($retval = GridRunPeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
            }


                if ($this->collBotOrders !== null) {
                    foreach ($this->collBotOrders as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collTradeCycles !== null) {
                    foreach ($this->collTradeCycles as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collBotEvents !== null) {
                    foreach ($this->collBotEvents as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collBotCommands !== null) {
                    foreach ($this->collBotCommands as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collBotDecisions !== null) {
                    foreach ($this->collBotDecisions as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
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
        $pos = GridRunPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
        if (isset($alreadyDumpedObjects['GridRun'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['GridRun'][$this->getPrimaryKey()] = true;
        $keys = GridRunPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getIdGridRun(),
            $keys[1] => $this->getLabel(),
            $keys[2] => $this->getSymbol(),
            $keys[3] => $this->getStatus(),
            $keys[4] => $this->getKillSwitch(),
            $keys[5] => $this->getProfile(),
            $keys[6] => $this->getAlgo(),
            $keys[7] => $this->getSimulated(),
            $keys[8] => $this->getPLow(),
            $keys[9] => $this->getPHigh(),
            $keys[10] => $this->getNLevels(),
            $keys[11] => $this->getSpacing(),
            $keys[12] => $this->getAllocation(),
            $keys[13] => $this->getBudgetQuote(),
            $keys[14] => $this->getDeployPct(),
            $keys[15] => $this->getFeePct(),
            $keys[16] => $this->getMaxPositionQuote(),
            $keys[17] => $this->getMaxOrderQuote(),
            $keys[18] => $this->getDailyLossLimitQuote(),
            $keys[19] => $this->getMaxUnrealizedLossQuote(),
            $keys[20] => $this->getBreakoutBufferPct(),
            $keys[21] => $this->getBreakoutPolicy(),
            $keys[22] => $this->getMaxOpenOrders(),
            $keys[23] => $this->getMaxBuyLevelsBelow(),
            $keys[24] => $this->getTrendTf(),
            $keys[25] => $this->getDonchianPeriod(),
            $keys[26] => $this->getTrendEmaFast(),
            $keys[27] => $this->getTrendEmaSlow(),
            $keys[28] => $this->getAtrPeriod(),
            $keys[29] => $this->getAtrStopMult(),
            $keys[30] => $this->getAtrInitialMult(),
            $keys[31] => $this->getReentryCooldown(),
            $keys[32] => $this->getEngineState(),
            $keys[33] => $this->getLastTickAt(),
            $keys[34] => $this->getLastPrice(),
            $keys[35] => $this->getBalBase(),
            $keys[36] => $this->getBalQuote(),
            $keys[37] => $this->getSimBalBase(),
            $keys[38] => $this->getSimBalQuote(),
            $keys[39] => $this->getRunUid(),
            $keys[40] => $this->getAppliedGeometry(),
            $keys[41] => $this->getLedgerResetAt(),
            $keys[42] => $this->getDateCreation(),
            $keys[43] => $this->getDateModification(),
            $keys[44] => $this->getIdGroupCreation(),
            $keys[45] => $this->getIdCreation(),
            $keys[46] => $this->getIdModification(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aAuthyGroup) {
                $result['AuthyGroup'] = $this->aAuthyGroup->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aAuthyRelatedByIdCreation) {
                $result['AuthyRelatedByIdCreation'] = $this->aAuthyRelatedByIdCreation->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aAuthyRelatedByIdModification) {
                $result['AuthyRelatedByIdModification'] = $this->aAuthyRelatedByIdModification->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collBotOrders) {
                $result['BotOrders'] = $this->collBotOrders->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collTradeCycles) {
                $result['TradeCycles'] = $this->collTradeCycles->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collBotEvents) {
                $result['BotEvents'] = $this->collBotEvents->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collBotCommands) {
                $result['BotCommands'] = $this->collBotCommands->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collBotDecisions) {
                $result['BotDecisions'] = $this->collBotDecisions->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = GridRunPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

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
                $this->setIdGridRun($value);
                break;
            case 1:
                $this->setLabel($value);
                break;
            case 2:
                $this->setSymbol($value);
                break;
            case 3:
                $valueSet = GridRunPeer::getValueSet(GridRunPeer::STATUS);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setStatus($value);
                break;
            case 4:
                $this->setKillSwitch($value);
                break;
            case 5:
                $valueSet = GridRunPeer::getValueSet(GridRunPeer::PROFILE);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setProfile($value);
                break;
            case 6:
                $valueSet = GridRunPeer::getValueSet(GridRunPeer::ALGO);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setAlgo($value);
                break;
            case 7:
                $this->setSimulated($value);
                break;
            case 8:
                $this->setPLow($value);
                break;
            case 9:
                $this->setPHigh($value);
                break;
            case 10:
                $this->setNLevels($value);
                break;
            case 11:
                $valueSet = GridRunPeer::getValueSet(GridRunPeer::SPACING);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setSpacing($value);
                break;
            case 12:
                $valueSet = GridRunPeer::getValueSet(GridRunPeer::ALLOCATION);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setAllocation($value);
                break;
            case 13:
                $this->setBudgetQuote($value);
                break;
            case 14:
                $this->setDeployPct($value);
                break;
            case 15:
                $this->setFeePct($value);
                break;
            case 16:
                $this->setMaxPositionQuote($value);
                break;
            case 17:
                $this->setMaxOrderQuote($value);
                break;
            case 18:
                $this->setDailyLossLimitQuote($value);
                break;
            case 19:
                $this->setMaxUnrealizedLossQuote($value);
                break;
            case 20:
                $this->setBreakoutBufferPct($value);
                break;
            case 21:
                $valueSet = GridRunPeer::getValueSet(GridRunPeer::BREAKOUT_POLICY);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setBreakoutPolicy($value);
                break;
            case 22:
                $this->setMaxOpenOrders($value);
                break;
            case 23:
                $this->setMaxBuyLevelsBelow($value);
                break;
            case 24:
                $valueSet = GridRunPeer::getValueSet(GridRunPeer::TREND_TF);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setTrendTf($value);
                break;
            case 25:
                $this->setDonchianPeriod($value);
                break;
            case 26:
                $this->setTrendEmaFast($value);
                break;
            case 27:
                $this->setTrendEmaSlow($value);
                break;
            case 28:
                $this->setAtrPeriod($value);
                break;
            case 29:
                $this->setAtrStopMult($value);
                break;
            case 30:
                $this->setAtrInitialMult($value);
                break;
            case 31:
                $this->setReentryCooldown($value);
                break;
            case 32:
                $this->setEngineState($value);
                break;
            case 33:
                $this->setLastTickAt($value);
                break;
            case 34:
                $this->setLastPrice($value);
                break;
            case 35:
                $this->setBalBase($value);
                break;
            case 36:
                $this->setBalQuote($value);
                break;
            case 37:
                $this->setSimBalBase($value);
                break;
            case 38:
                $this->setSimBalQuote($value);
                break;
            case 39:
                $this->setRunUid($value);
                break;
            case 40:
                $this->setAppliedGeometry($value);
                break;
            case 41:
                $this->setLedgerResetAt($value);
                break;
            case 42:
                $this->setDateCreation($value);
                break;
            case 43:
                $this->setDateModification($value);
                break;
            case 44:
                $this->setIdGroupCreation($value);
                break;
            case 45:
                $this->setIdCreation($value);
                break;
            case 46:
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
        $keys = GridRunPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setIdGridRun($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setLabel($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setSymbol($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setStatus($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setKillSwitch($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setProfile($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setAlgo($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setSimulated($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setPLow($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setPHigh($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setNLevels($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setSpacing($arr[$keys[11]]);
        if (array_key_exists($keys[12], $arr)) $this->setAllocation($arr[$keys[12]]);
        if (array_key_exists($keys[13], $arr)) $this->setBudgetQuote($arr[$keys[13]]);
        if (array_key_exists($keys[14], $arr)) $this->setDeployPct($arr[$keys[14]]);
        if (array_key_exists($keys[15], $arr)) $this->setFeePct($arr[$keys[15]]);
        if (array_key_exists($keys[16], $arr)) $this->setMaxPositionQuote($arr[$keys[16]]);
        if (array_key_exists($keys[17], $arr)) $this->setMaxOrderQuote($arr[$keys[17]]);
        if (array_key_exists($keys[18], $arr)) $this->setDailyLossLimitQuote($arr[$keys[18]]);
        if (array_key_exists($keys[19], $arr)) $this->setMaxUnrealizedLossQuote($arr[$keys[19]]);
        if (array_key_exists($keys[20], $arr)) $this->setBreakoutBufferPct($arr[$keys[20]]);
        if (array_key_exists($keys[21], $arr)) $this->setBreakoutPolicy($arr[$keys[21]]);
        if (array_key_exists($keys[22], $arr)) $this->setMaxOpenOrders($arr[$keys[22]]);
        if (array_key_exists($keys[23], $arr)) $this->setMaxBuyLevelsBelow($arr[$keys[23]]);
        if (array_key_exists($keys[24], $arr)) $this->setTrendTf($arr[$keys[24]]);
        if (array_key_exists($keys[25], $arr)) $this->setDonchianPeriod($arr[$keys[25]]);
        if (array_key_exists($keys[26], $arr)) $this->setTrendEmaFast($arr[$keys[26]]);
        if (array_key_exists($keys[27], $arr)) $this->setTrendEmaSlow($arr[$keys[27]]);
        if (array_key_exists($keys[28], $arr)) $this->setAtrPeriod($arr[$keys[28]]);
        if (array_key_exists($keys[29], $arr)) $this->setAtrStopMult($arr[$keys[29]]);
        if (array_key_exists($keys[30], $arr)) $this->setAtrInitialMult($arr[$keys[30]]);
        if (array_key_exists($keys[31], $arr)) $this->setReentryCooldown($arr[$keys[31]]);
        if (array_key_exists($keys[32], $arr)) $this->setEngineState($arr[$keys[32]]);
        if (array_key_exists($keys[33], $arr)) $this->setLastTickAt($arr[$keys[33]]);
        if (array_key_exists($keys[34], $arr)) $this->setLastPrice($arr[$keys[34]]);
        if (array_key_exists($keys[35], $arr)) $this->setBalBase($arr[$keys[35]]);
        if (array_key_exists($keys[36], $arr)) $this->setBalQuote($arr[$keys[36]]);
        if (array_key_exists($keys[37], $arr)) $this->setSimBalBase($arr[$keys[37]]);
        if (array_key_exists($keys[38], $arr)) $this->setSimBalQuote($arr[$keys[38]]);
        if (array_key_exists($keys[39], $arr)) $this->setRunUid($arr[$keys[39]]);
        if (array_key_exists($keys[40], $arr)) $this->setAppliedGeometry($arr[$keys[40]]);
        if (array_key_exists($keys[41], $arr)) $this->setLedgerResetAt($arr[$keys[41]]);
        if (array_key_exists($keys[42], $arr)) $this->setDateCreation($arr[$keys[42]]);
        if (array_key_exists($keys[43], $arr)) $this->setDateModification($arr[$keys[43]]);
        if (array_key_exists($keys[44], $arr)) $this->setIdGroupCreation($arr[$keys[44]]);
        if (array_key_exists($keys[45], $arr)) $this->setIdCreation($arr[$keys[45]]);
        if (array_key_exists($keys[46], $arr)) $this->setIdModification($arr[$keys[46]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(GridRunPeer::DATABASE_NAME);

        if ($this->isColumnModified(GridRunPeer::ID_GRID_RUN)) $criteria->add(GridRunPeer::ID_GRID_RUN, $this->id_grid_run);
        if ($this->isColumnModified(GridRunPeer::LABEL)) $criteria->add(GridRunPeer::LABEL, $this->label);
        if ($this->isColumnModified(GridRunPeer::SYMBOL)) $criteria->add(GridRunPeer::SYMBOL, $this->symbol);
        if ($this->isColumnModified(GridRunPeer::STATUS)) $criteria->add(GridRunPeer::STATUS, $this->status);
        if ($this->isColumnModified(GridRunPeer::KILL_SWITCH)) $criteria->add(GridRunPeer::KILL_SWITCH, $this->kill_switch);
        if ($this->isColumnModified(GridRunPeer::PROFILE)) $criteria->add(GridRunPeer::PROFILE, $this->profile);
        if ($this->isColumnModified(GridRunPeer::ALGO)) $criteria->add(GridRunPeer::ALGO, $this->algo);
        if ($this->isColumnModified(GridRunPeer::SIMULATED)) $criteria->add(GridRunPeer::SIMULATED, $this->simulated);
        if ($this->isColumnModified(GridRunPeer::P_LOW)) $criteria->add(GridRunPeer::P_LOW, $this->p_low);
        if ($this->isColumnModified(GridRunPeer::P_HIGH)) $criteria->add(GridRunPeer::P_HIGH, $this->p_high);
        if ($this->isColumnModified(GridRunPeer::N_LEVELS)) $criteria->add(GridRunPeer::N_LEVELS, $this->n_levels);
        if ($this->isColumnModified(GridRunPeer::SPACING)) $criteria->add(GridRunPeer::SPACING, $this->spacing);
        if ($this->isColumnModified(GridRunPeer::ALLOCATION)) $criteria->add(GridRunPeer::ALLOCATION, $this->allocation);
        if ($this->isColumnModified(GridRunPeer::BUDGET_QUOTE)) $criteria->add(GridRunPeer::BUDGET_QUOTE, $this->budget_quote);
        if ($this->isColumnModified(GridRunPeer::DEPLOY_PCT)) $criteria->add(GridRunPeer::DEPLOY_PCT, $this->deploy_pct);
        if ($this->isColumnModified(GridRunPeer::FEE_PCT)) $criteria->add(GridRunPeer::FEE_PCT, $this->fee_pct);
        if ($this->isColumnModified(GridRunPeer::MAX_POSITION_QUOTE)) $criteria->add(GridRunPeer::MAX_POSITION_QUOTE, $this->max_position_quote);
        if ($this->isColumnModified(GridRunPeer::MAX_ORDER_QUOTE)) $criteria->add(GridRunPeer::MAX_ORDER_QUOTE, $this->max_order_quote);
        if ($this->isColumnModified(GridRunPeer::DAILY_LOSS_LIMIT_QUOTE)) $criteria->add(GridRunPeer::DAILY_LOSS_LIMIT_QUOTE, $this->daily_loss_limit_quote);
        if ($this->isColumnModified(GridRunPeer::MAX_UNREALIZED_LOSS_QUOTE)) $criteria->add(GridRunPeer::MAX_UNREALIZED_LOSS_QUOTE, $this->max_unrealized_loss_quote);
        if ($this->isColumnModified(GridRunPeer::BREAKOUT_BUFFER_PCT)) $criteria->add(GridRunPeer::BREAKOUT_BUFFER_PCT, $this->breakout_buffer_pct);
        if ($this->isColumnModified(GridRunPeer::BREAKOUT_POLICY)) $criteria->add(GridRunPeer::BREAKOUT_POLICY, $this->breakout_policy);
        if ($this->isColumnModified(GridRunPeer::MAX_OPEN_ORDERS)) $criteria->add(GridRunPeer::MAX_OPEN_ORDERS, $this->max_open_orders);
        if ($this->isColumnModified(GridRunPeer::MAX_BUY_LEVELS_BELOW)) $criteria->add(GridRunPeer::MAX_BUY_LEVELS_BELOW, $this->max_buy_levels_below);
        if ($this->isColumnModified(GridRunPeer::TREND_TF)) $criteria->add(GridRunPeer::TREND_TF, $this->trend_tf);
        if ($this->isColumnModified(GridRunPeer::DONCHIAN_PERIOD)) $criteria->add(GridRunPeer::DONCHIAN_PERIOD, $this->donchian_period);
        if ($this->isColumnModified(GridRunPeer::TREND_EMA_FAST)) $criteria->add(GridRunPeer::TREND_EMA_FAST, $this->trend_ema_fast);
        if ($this->isColumnModified(GridRunPeer::TREND_EMA_SLOW)) $criteria->add(GridRunPeer::TREND_EMA_SLOW, $this->trend_ema_slow);
        if ($this->isColumnModified(GridRunPeer::ATR_PERIOD)) $criteria->add(GridRunPeer::ATR_PERIOD, $this->atr_period);
        if ($this->isColumnModified(GridRunPeer::ATR_STOP_MULT)) $criteria->add(GridRunPeer::ATR_STOP_MULT, $this->atr_stop_mult);
        if ($this->isColumnModified(GridRunPeer::ATR_INITIAL_MULT)) $criteria->add(GridRunPeer::ATR_INITIAL_MULT, $this->atr_initial_mult);
        if ($this->isColumnModified(GridRunPeer::REENTRY_COOLDOWN)) $criteria->add(GridRunPeer::REENTRY_COOLDOWN, $this->reentry_cooldown);
        if ($this->isColumnModified(GridRunPeer::ENGINE_STATE)) $criteria->add(GridRunPeer::ENGINE_STATE, $this->engine_state);
        if ($this->isColumnModified(GridRunPeer::LAST_TICK_AT)) $criteria->add(GridRunPeer::LAST_TICK_AT, $this->last_tick_at);
        if ($this->isColumnModified(GridRunPeer::LAST_PRICE)) $criteria->add(GridRunPeer::LAST_PRICE, $this->last_price);
        if ($this->isColumnModified(GridRunPeer::BAL_BASE)) $criteria->add(GridRunPeer::BAL_BASE, $this->bal_base);
        if ($this->isColumnModified(GridRunPeer::BAL_QUOTE)) $criteria->add(GridRunPeer::BAL_QUOTE, $this->bal_quote);
        if ($this->isColumnModified(GridRunPeer::SIM_BAL_BASE)) $criteria->add(GridRunPeer::SIM_BAL_BASE, $this->sim_bal_base);
        if ($this->isColumnModified(GridRunPeer::SIM_BAL_QUOTE)) $criteria->add(GridRunPeer::SIM_BAL_QUOTE, $this->sim_bal_quote);
        if ($this->isColumnModified(GridRunPeer::RUN_UID)) $criteria->add(GridRunPeer::RUN_UID, $this->run_uid);
        if ($this->isColumnModified(GridRunPeer::APPLIED_GEOMETRY)) $criteria->add(GridRunPeer::APPLIED_GEOMETRY, $this->applied_geometry);
        if ($this->isColumnModified(GridRunPeer::LEDGER_RESET_AT)) $criteria->add(GridRunPeer::LEDGER_RESET_AT, $this->ledger_reset_at);
        if ($this->isColumnModified(GridRunPeer::DATE_CREATION)) $criteria->add(GridRunPeer::DATE_CREATION, $this->date_creation);
        if ($this->isColumnModified(GridRunPeer::DATE_MODIFICATION)) $criteria->add(GridRunPeer::DATE_MODIFICATION, $this->date_modification);
        if ($this->isColumnModified(GridRunPeer::ID_GROUP_CREATION)) $criteria->add(GridRunPeer::ID_GROUP_CREATION, $this->id_group_creation);
        if ($this->isColumnModified(GridRunPeer::ID_CREATION)) $criteria->add(GridRunPeer::ID_CREATION, $this->id_creation);
        if ($this->isColumnModified(GridRunPeer::ID_MODIFICATION)) $criteria->add(GridRunPeer::ID_MODIFICATION, $this->id_modification);

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
        $criteria = new Criteria(GridRunPeer::DATABASE_NAME);
        $criteria->add(GridRunPeer::ID_GRID_RUN, $this->id_grid_run);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getIdGridRun();
    }

    /**
     * Generic method to set the primary key (id_grid_run column).
     *
     * @param  int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setIdGridRun($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getIdGridRun();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of GridRun (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setLabel($this->getLabel());
        $copyObj->setSymbol($this->getSymbol());
        $copyObj->setStatus($this->getStatus());
        $copyObj->setKillSwitch($this->getKillSwitch());
        $copyObj->setProfile($this->getProfile());
        $copyObj->setAlgo($this->getAlgo());
        $copyObj->setSimulated($this->getSimulated());
        $copyObj->setPLow($this->getPLow());
        $copyObj->setPHigh($this->getPHigh());
        $copyObj->setNLevels($this->getNLevels());
        $copyObj->setSpacing($this->getSpacing());
        $copyObj->setAllocation($this->getAllocation());
        $copyObj->setBudgetQuote($this->getBudgetQuote());
        $copyObj->setDeployPct($this->getDeployPct());
        $copyObj->setFeePct($this->getFeePct());
        $copyObj->setMaxPositionQuote($this->getMaxPositionQuote());
        $copyObj->setMaxOrderQuote($this->getMaxOrderQuote());
        $copyObj->setDailyLossLimitQuote($this->getDailyLossLimitQuote());
        $copyObj->setMaxUnrealizedLossQuote($this->getMaxUnrealizedLossQuote());
        $copyObj->setBreakoutBufferPct($this->getBreakoutBufferPct());
        $copyObj->setBreakoutPolicy($this->getBreakoutPolicy());
        $copyObj->setMaxOpenOrders($this->getMaxOpenOrders());
        $copyObj->setMaxBuyLevelsBelow($this->getMaxBuyLevelsBelow());
        $copyObj->setTrendTf($this->getTrendTf());
        $copyObj->setDonchianPeriod($this->getDonchianPeriod());
        $copyObj->setTrendEmaFast($this->getTrendEmaFast());
        $copyObj->setTrendEmaSlow($this->getTrendEmaSlow());
        $copyObj->setAtrPeriod($this->getAtrPeriod());
        $copyObj->setAtrStopMult($this->getAtrStopMult());
        $copyObj->setAtrInitialMult($this->getAtrInitialMult());
        $copyObj->setReentryCooldown($this->getReentryCooldown());
        $copyObj->setEngineState($this->getEngineState());
        $copyObj->setLastTickAt($this->getLastTickAt());
        $copyObj->setLastPrice($this->getLastPrice());
        $copyObj->setBalBase($this->getBalBase());
        $copyObj->setBalQuote($this->getBalQuote());
        $copyObj->setSimBalBase($this->getSimBalBase());
        $copyObj->setSimBalQuote($this->getSimBalQuote());
        $copyObj->setRunUid($this->getRunUid());
        $copyObj->setAppliedGeometry($this->getAppliedGeometry());
        $copyObj->setLedgerResetAt($this->getLedgerResetAt());
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

            foreach ($this->getBotOrders() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addBotOrder($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getTradeCycles() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addTradeCycle($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getBotEvents() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addBotEvent($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getBotCommands() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addBotCommand($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getBotDecisions() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addBotDecision($relObj->copy($deepCopy));
                }
            }

            //unflag object copy
            $this->startCopy = false;
        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setIdGridRun(NULL); // this is a auto-increment column, so set to default value
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
     * @return GridRun Clone of current object.
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
     * @return GridRunPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new GridRunPeer();
        }

        return self::$peer;
    }

    /**
     * Declares an association between this object and a AuthyGroup object.
     *
     * @param                  AuthyGroup $v
     * @return GridRun The current object (for fluent API support)
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
            $v->addGridRun($this);
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
                $this->aAuthyGroup->addGridRuns($this);
             */
        }

        return $this->aAuthyGroup;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return GridRun The current object (for fluent API support)
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
            $v->addGridRunRelatedByIdCreation($this);
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
                $this->aAuthyRelatedByIdCreation->addGridRunsRelatedByIdCreation($this);
             */
        }

        return $this->aAuthyRelatedByIdCreation;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return GridRun The current object (for fluent API support)
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
            $v->addGridRunRelatedByIdModification($this);
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
                $this->aAuthyRelatedByIdModification->addGridRunsRelatedByIdModification($this);
             */
        }

        return $this->aAuthyRelatedByIdModification;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('BotOrder' == $relationName) {
            $this->initBotOrders();
        }
        if ('TradeCycle' == $relationName) {
            $this->initTradeCycles();
        }
        if ('BotEvent' == $relationName) {
            $this->initBotEvents();
        }
        if ('BotCommand' == $relationName) {
            $this->initBotCommands();
        }
        if ('BotDecision' == $relationName) {
            $this->initBotDecisions();
        }
    }

    /**
     * Clears out the collBotOrders collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return GridRun The current object (for fluent API support)
     * @see        addBotOrders()
     */
    public function clearBotOrders()
    {
        $this->collBotOrders = null; // important to set this to null since that means it is uninitialized
        $this->collBotOrdersPartial = null;

        return $this;
    }

    /**
     * reset is the collBotOrders collection loaded partially
     *
     * @return void
     */
    public function resetPartialBotOrders($v = true)
    {
        $this->collBotOrdersPartial = $v;
    }

    /**
     * Initializes the collBotOrders collection.
     *
     * By default this just sets the collBotOrders collection to an empty array (like clearcollBotOrders());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initBotOrders($overrideExisting = true)
    {
        if (null !== $this->collBotOrders && !$overrideExisting) {
            return;
        }
        $this->collBotOrders = new PropelObjectCollection();
        $this->collBotOrders->setModel('BotOrder');
    }

    /**
     * Gets an array of BotOrder objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this GridRun is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|BotOrder[] List of BotOrder objects
     * @throws PropelException
     */
    public function getBotOrders($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collBotOrdersPartial && !$this->isNew();
        if (null === $this->collBotOrders || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collBotOrders) {
                // return empty collection
                $this->initBotOrders();
            } else {
                $collBotOrders = BotOrderQuery::create(null, $criteria)
                    ->filterByGridRun($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collBotOrdersPartial && count($collBotOrders)) {
                      $this->initBotOrders(false);

                      foreach ($collBotOrders as $obj) {
                        if (false == $this->collBotOrders->contains($obj)) {
                          $this->collBotOrders->append($obj);
                        }
                      }

                      $this->collBotOrdersPartial = true;
                    }

                    $collBotOrders->getInternalIterator()->rewind();

                    return $collBotOrders;
                }

                if ($partial && $this->collBotOrders) {
                    foreach ($this->collBotOrders as $obj) {
                        if ($obj->isNew()) {
                            $collBotOrders[] = $obj;
                        }
                    }
                }

                $this->collBotOrders = $collBotOrders;
                $this->collBotOrdersPartial = false;
            }
        }

        return $this->collBotOrders;
    }

    /**
     * Sets a collection of BotOrder objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $botOrders A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return GridRun The current object (for fluent API support)
     */
    public function setBotOrders(PropelCollection $botOrders, ?PropelPDO $con = null)
    {
        $botOrdersToDelete = $this->getBotOrders(new Criteria(), $con)->diff($botOrders);


        $this->botOrdersScheduledForDeletion = $botOrdersToDelete;

        foreach ($botOrdersToDelete as $botOrderRemoved) {
            $botOrderRemoved->setGridRun(null);
        }

        $this->collBotOrders = null;
        foreach ($botOrders as $botOrder) {
            $this->addBotOrder($botOrder);
        }

        $this->collBotOrders = $botOrders;
        $this->collBotOrdersPartial = false;

        return $this;
    }

    /**
     * Returns the number of related BotOrder objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related BotOrder objects.
     * @throws PropelException
     */
    public function countBotOrders(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collBotOrdersPartial && !$this->isNew();
        if (null === $this->collBotOrders || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collBotOrders) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getBotOrders());
            }
            $query = BotOrderQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByGridRun($this)
                ->count($con);
        }

        return count($this->collBotOrders);
    }

    /**
     * Method called to associate a BotOrder object to this object
     * through the BotOrder foreign key attribute.
     *
     * @param    BotOrder $l BotOrder
     * @return GridRun The current object (for fluent API support)
     */
    public function addBotOrder(BotOrder $l)
    {
        if ($this->collBotOrders === null) {
            $this->initBotOrders();
            $this->collBotOrdersPartial = true;
        }

        if (!in_array($l, $this->collBotOrders->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddBotOrder($l);

            if ($this->botOrdersScheduledForDeletion and $this->botOrdersScheduledForDeletion->contains($l)) {
                $this->botOrdersScheduledForDeletion->remove($this->botOrdersScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	BotOrder $botOrder The botOrder object to add.
     */
    protected function doAddBotOrder($botOrder)
    {
        $this->collBotOrders[]= $botOrder;
        $botOrder->setGridRun($this);
    }

    /**
     * @param	BotOrder $botOrder The botOrder object to remove.
     * @return GridRun The current object (for fluent API support)
     */
    public function removeBotOrder($botOrder)
    {
        if ($this->getBotOrders()->contains($botOrder)) {
            $this->collBotOrders->remove($this->collBotOrders->search($botOrder));
            if (null === $this->botOrdersScheduledForDeletion) {
                $this->botOrdersScheduledForDeletion = clone $this->collBotOrders;
                $this->botOrdersScheduledForDeletion->clear();
            }
            $this->botOrdersScheduledForDeletion[]= clone $botOrder;
            $botOrder->setGridRun(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|BotOrder[] List of BotOrder objects
     */
    public function getBotOrdersJoinAuthyGroup($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = BotOrderQuery::create(null, $criteria);
        $query->joinWith('AuthyGroup', $join_behavior);

        return $this->getBotOrders($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|BotOrder[] List of BotOrder objects
     */
    public function getBotOrdersJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = BotOrderQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getBotOrders($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|BotOrder[] List of BotOrder objects
     */
    public function getBotOrdersJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = BotOrderQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getBotOrders($query, $con);
    }

    /**
     * Clears out the collTradeCycles collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return GridRun The current object (for fluent API support)
     * @see        addTradeCycles()
     */
    public function clearTradeCycles()
    {
        $this->collTradeCycles = null; // important to set this to null since that means it is uninitialized
        $this->collTradeCyclesPartial = null;

        return $this;
    }

    /**
     * reset is the collTradeCycles collection loaded partially
     *
     * @return void
     */
    public function resetPartialTradeCycles($v = true)
    {
        $this->collTradeCyclesPartial = $v;
    }

    /**
     * Initializes the collTradeCycles collection.
     *
     * By default this just sets the collTradeCycles collection to an empty array (like clearcollTradeCycles());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initTradeCycles($overrideExisting = true)
    {
        if (null !== $this->collTradeCycles && !$overrideExisting) {
            return;
        }
        $this->collTradeCycles = new PropelObjectCollection();
        $this->collTradeCycles->setModel('TradeCycle');
    }

    /**
     * Gets an array of TradeCycle objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this GridRun is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|TradeCycle[] List of TradeCycle objects
     * @throws PropelException
     */
    public function getTradeCycles($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collTradeCyclesPartial && !$this->isNew();
        if (null === $this->collTradeCycles || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collTradeCycles) {
                // return empty collection
                $this->initTradeCycles();
            } else {
                $collTradeCycles = TradeCycleQuery::create(null, $criteria)
                    ->filterByGridRun($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collTradeCyclesPartial && count($collTradeCycles)) {
                      $this->initTradeCycles(false);

                      foreach ($collTradeCycles as $obj) {
                        if (false == $this->collTradeCycles->contains($obj)) {
                          $this->collTradeCycles->append($obj);
                        }
                      }

                      $this->collTradeCyclesPartial = true;
                    }

                    $collTradeCycles->getInternalIterator()->rewind();

                    return $collTradeCycles;
                }

                if ($partial && $this->collTradeCycles) {
                    foreach ($this->collTradeCycles as $obj) {
                        if ($obj->isNew()) {
                            $collTradeCycles[] = $obj;
                        }
                    }
                }

                $this->collTradeCycles = $collTradeCycles;
                $this->collTradeCyclesPartial = false;
            }
        }

        return $this->collTradeCycles;
    }

    /**
     * Sets a collection of TradeCycle objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $tradeCycles A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return GridRun The current object (for fluent API support)
     */
    public function setTradeCycles(PropelCollection $tradeCycles, ?PropelPDO $con = null)
    {
        $tradeCyclesToDelete = $this->getTradeCycles(new Criteria(), $con)->diff($tradeCycles);


        $this->tradeCyclesScheduledForDeletion = $tradeCyclesToDelete;

        foreach ($tradeCyclesToDelete as $tradeCycleRemoved) {
            $tradeCycleRemoved->setGridRun(null);
        }

        $this->collTradeCycles = null;
        foreach ($tradeCycles as $tradeCycle) {
            $this->addTradeCycle($tradeCycle);
        }

        $this->collTradeCycles = $tradeCycles;
        $this->collTradeCyclesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related TradeCycle objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related TradeCycle objects.
     * @throws PropelException
     */
    public function countTradeCycles(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collTradeCyclesPartial && !$this->isNew();
        if (null === $this->collTradeCycles || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collTradeCycles) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getTradeCycles());
            }
            $query = TradeCycleQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByGridRun($this)
                ->count($con);
        }

        return count($this->collTradeCycles);
    }

    /**
     * Method called to associate a TradeCycle object to this object
     * through the TradeCycle foreign key attribute.
     *
     * @param    TradeCycle $l TradeCycle
     * @return GridRun The current object (for fluent API support)
     */
    public function addTradeCycle(TradeCycle $l)
    {
        if ($this->collTradeCycles === null) {
            $this->initTradeCycles();
            $this->collTradeCyclesPartial = true;
        }

        if (!in_array($l, $this->collTradeCycles->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddTradeCycle($l);

            if ($this->tradeCyclesScheduledForDeletion and $this->tradeCyclesScheduledForDeletion->contains($l)) {
                $this->tradeCyclesScheduledForDeletion->remove($this->tradeCyclesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	TradeCycle $tradeCycle The tradeCycle object to add.
     */
    protected function doAddTradeCycle($tradeCycle)
    {
        $this->collTradeCycles[]= $tradeCycle;
        $tradeCycle->setGridRun($this);
    }

    /**
     * @param	TradeCycle $tradeCycle The tradeCycle object to remove.
     * @return GridRun The current object (for fluent API support)
     */
    public function removeTradeCycle($tradeCycle)
    {
        if ($this->getTradeCycles()->contains($tradeCycle)) {
            $this->collTradeCycles->remove($this->collTradeCycles->search($tradeCycle));
            if (null === $this->tradeCyclesScheduledForDeletion) {
                $this->tradeCyclesScheduledForDeletion = clone $this->collTradeCycles;
                $this->tradeCyclesScheduledForDeletion->clear();
            }
            $this->tradeCyclesScheduledForDeletion[]= clone $tradeCycle;
            $tradeCycle->setGridRun(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|TradeCycle[] List of TradeCycle objects
     */
    public function getTradeCyclesJoinAuthyGroup($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = TradeCycleQuery::create(null, $criteria);
        $query->joinWith('AuthyGroup', $join_behavior);

        return $this->getTradeCycles($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|TradeCycle[] List of TradeCycle objects
     */
    public function getTradeCyclesJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = TradeCycleQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getTradeCycles($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|TradeCycle[] List of TradeCycle objects
     */
    public function getTradeCyclesJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = TradeCycleQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getTradeCycles($query, $con);
    }

    /**
     * Clears out the collBotEvents collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return GridRun The current object (for fluent API support)
     * @see        addBotEvents()
     */
    public function clearBotEvents()
    {
        $this->collBotEvents = null; // important to set this to null since that means it is uninitialized
        $this->collBotEventsPartial = null;

        return $this;
    }

    /**
     * reset is the collBotEvents collection loaded partially
     *
     * @return void
     */
    public function resetPartialBotEvents($v = true)
    {
        $this->collBotEventsPartial = $v;
    }

    /**
     * Initializes the collBotEvents collection.
     *
     * By default this just sets the collBotEvents collection to an empty array (like clearcollBotEvents());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initBotEvents($overrideExisting = true)
    {
        if (null !== $this->collBotEvents && !$overrideExisting) {
            return;
        }
        $this->collBotEvents = new PropelObjectCollection();
        $this->collBotEvents->setModel('BotEvent');
    }

    /**
     * Gets an array of BotEvent objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this GridRun is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|BotEvent[] List of BotEvent objects
     * @throws PropelException
     */
    public function getBotEvents($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collBotEventsPartial && !$this->isNew();
        if (null === $this->collBotEvents || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collBotEvents) {
                // return empty collection
                $this->initBotEvents();
            } else {
                $collBotEvents = BotEventQuery::create(null, $criteria)
                    ->filterByGridRun($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collBotEventsPartial && count($collBotEvents)) {
                      $this->initBotEvents(false);

                      foreach ($collBotEvents as $obj) {
                        if (false == $this->collBotEvents->contains($obj)) {
                          $this->collBotEvents->append($obj);
                        }
                      }

                      $this->collBotEventsPartial = true;
                    }

                    $collBotEvents->getInternalIterator()->rewind();

                    return $collBotEvents;
                }

                if ($partial && $this->collBotEvents) {
                    foreach ($this->collBotEvents as $obj) {
                        if ($obj->isNew()) {
                            $collBotEvents[] = $obj;
                        }
                    }
                }

                $this->collBotEvents = $collBotEvents;
                $this->collBotEventsPartial = false;
            }
        }

        return $this->collBotEvents;
    }

    /**
     * Sets a collection of BotEvent objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $botEvents A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return GridRun The current object (for fluent API support)
     */
    public function setBotEvents(PropelCollection $botEvents, ?PropelPDO $con = null)
    {
        $botEventsToDelete = $this->getBotEvents(new Criteria(), $con)->diff($botEvents);


        $this->botEventsScheduledForDeletion = $botEventsToDelete;

        foreach ($botEventsToDelete as $botEventRemoved) {
            $botEventRemoved->setGridRun(null);
        }

        $this->collBotEvents = null;
        foreach ($botEvents as $botEvent) {
            $this->addBotEvent($botEvent);
        }

        $this->collBotEvents = $botEvents;
        $this->collBotEventsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related BotEvent objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related BotEvent objects.
     * @throws PropelException
     */
    public function countBotEvents(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collBotEventsPartial && !$this->isNew();
        if (null === $this->collBotEvents || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collBotEvents) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getBotEvents());
            }
            $query = BotEventQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByGridRun($this)
                ->count($con);
        }

        return count($this->collBotEvents);
    }

    /**
     * Method called to associate a BotEvent object to this object
     * through the BotEvent foreign key attribute.
     *
     * @param    BotEvent $l BotEvent
     * @return GridRun The current object (for fluent API support)
     */
    public function addBotEvent(BotEvent $l)
    {
        if ($this->collBotEvents === null) {
            $this->initBotEvents();
            $this->collBotEventsPartial = true;
        }

        if (!in_array($l, $this->collBotEvents->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddBotEvent($l);

            if ($this->botEventsScheduledForDeletion and $this->botEventsScheduledForDeletion->contains($l)) {
                $this->botEventsScheduledForDeletion->remove($this->botEventsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	BotEvent $botEvent The botEvent object to add.
     */
    protected function doAddBotEvent($botEvent)
    {
        $this->collBotEvents[]= $botEvent;
        $botEvent->setGridRun($this);
    }

    /**
     * @param	BotEvent $botEvent The botEvent object to remove.
     * @return GridRun The current object (for fluent API support)
     */
    public function removeBotEvent($botEvent)
    {
        if ($this->getBotEvents()->contains($botEvent)) {
            $this->collBotEvents->remove($this->collBotEvents->search($botEvent));
            if (null === $this->botEventsScheduledForDeletion) {
                $this->botEventsScheduledForDeletion = clone $this->collBotEvents;
                $this->botEventsScheduledForDeletion->clear();
            }
            $this->botEventsScheduledForDeletion[]= clone $botEvent;
            $botEvent->setGridRun(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|BotEvent[] List of BotEvent objects
     */
    public function getBotEventsJoinAuthyGroup($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = BotEventQuery::create(null, $criteria);
        $query->joinWith('AuthyGroup', $join_behavior);

        return $this->getBotEvents($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|BotEvent[] List of BotEvent objects
     */
    public function getBotEventsJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = BotEventQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getBotEvents($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|BotEvent[] List of BotEvent objects
     */
    public function getBotEventsJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = BotEventQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getBotEvents($query, $con);
    }

    /**
     * Clears out the collBotCommands collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return GridRun The current object (for fluent API support)
     * @see        addBotCommands()
     */
    public function clearBotCommands()
    {
        $this->collBotCommands = null; // important to set this to null since that means it is uninitialized
        $this->collBotCommandsPartial = null;

        return $this;
    }

    /**
     * reset is the collBotCommands collection loaded partially
     *
     * @return void
     */
    public function resetPartialBotCommands($v = true)
    {
        $this->collBotCommandsPartial = $v;
    }

    /**
     * Initializes the collBotCommands collection.
     *
     * By default this just sets the collBotCommands collection to an empty array (like clearcollBotCommands());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initBotCommands($overrideExisting = true)
    {
        if (null !== $this->collBotCommands && !$overrideExisting) {
            return;
        }
        $this->collBotCommands = new PropelObjectCollection();
        $this->collBotCommands->setModel('BotCommand');
    }

    /**
     * Gets an array of BotCommand objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this GridRun is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|BotCommand[] List of BotCommand objects
     * @throws PropelException
     */
    public function getBotCommands($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collBotCommandsPartial && !$this->isNew();
        if (null === $this->collBotCommands || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collBotCommands) {
                // return empty collection
                $this->initBotCommands();
            } else {
                $collBotCommands = BotCommandQuery::create(null, $criteria)
                    ->filterByGridRun($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collBotCommandsPartial && count($collBotCommands)) {
                      $this->initBotCommands(false);

                      foreach ($collBotCommands as $obj) {
                        if (false == $this->collBotCommands->contains($obj)) {
                          $this->collBotCommands->append($obj);
                        }
                      }

                      $this->collBotCommandsPartial = true;
                    }

                    $collBotCommands->getInternalIterator()->rewind();

                    return $collBotCommands;
                }

                if ($partial && $this->collBotCommands) {
                    foreach ($this->collBotCommands as $obj) {
                        if ($obj->isNew()) {
                            $collBotCommands[] = $obj;
                        }
                    }
                }

                $this->collBotCommands = $collBotCommands;
                $this->collBotCommandsPartial = false;
            }
        }

        return $this->collBotCommands;
    }

    /**
     * Sets a collection of BotCommand objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $botCommands A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return GridRun The current object (for fluent API support)
     */
    public function setBotCommands(PropelCollection $botCommands, ?PropelPDO $con = null)
    {
        $botCommandsToDelete = $this->getBotCommands(new Criteria(), $con)->diff($botCommands);


        $this->botCommandsScheduledForDeletion = $botCommandsToDelete;

        foreach ($botCommandsToDelete as $botCommandRemoved) {
            $botCommandRemoved->setGridRun(null);
        }

        $this->collBotCommands = null;
        foreach ($botCommands as $botCommand) {
            $this->addBotCommand($botCommand);
        }

        $this->collBotCommands = $botCommands;
        $this->collBotCommandsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related BotCommand objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related BotCommand objects.
     * @throws PropelException
     */
    public function countBotCommands(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collBotCommandsPartial && !$this->isNew();
        if (null === $this->collBotCommands || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collBotCommands) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getBotCommands());
            }
            $query = BotCommandQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByGridRun($this)
                ->count($con);
        }

        return count($this->collBotCommands);
    }

    /**
     * Method called to associate a BotCommand object to this object
     * through the BotCommand foreign key attribute.
     *
     * @param    BotCommand $l BotCommand
     * @return GridRun The current object (for fluent API support)
     */
    public function addBotCommand(BotCommand $l)
    {
        if ($this->collBotCommands === null) {
            $this->initBotCommands();
            $this->collBotCommandsPartial = true;
        }

        if (!in_array($l, $this->collBotCommands->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddBotCommand($l);

            if ($this->botCommandsScheduledForDeletion and $this->botCommandsScheduledForDeletion->contains($l)) {
                $this->botCommandsScheduledForDeletion->remove($this->botCommandsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	BotCommand $botCommand The botCommand object to add.
     */
    protected function doAddBotCommand($botCommand)
    {
        $this->collBotCommands[]= $botCommand;
        $botCommand->setGridRun($this);
    }

    /**
     * @param	BotCommand $botCommand The botCommand object to remove.
     * @return GridRun The current object (for fluent API support)
     */
    public function removeBotCommand($botCommand)
    {
        if ($this->getBotCommands()->contains($botCommand)) {
            $this->collBotCommands->remove($this->collBotCommands->search($botCommand));
            if (null === $this->botCommandsScheduledForDeletion) {
                $this->botCommandsScheduledForDeletion = clone $this->collBotCommands;
                $this->botCommandsScheduledForDeletion->clear();
            }
            $this->botCommandsScheduledForDeletion[]= clone $botCommand;
            $botCommand->setGridRun(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|BotCommand[] List of BotCommand objects
     */
    public function getBotCommandsJoinAuthyGroup($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = BotCommandQuery::create(null, $criteria);
        $query->joinWith('AuthyGroup', $join_behavior);

        return $this->getBotCommands($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|BotCommand[] List of BotCommand objects
     */
    public function getBotCommandsJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = BotCommandQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getBotCommands($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|BotCommand[] List of BotCommand objects
     */
    public function getBotCommandsJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = BotCommandQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getBotCommands($query, $con);
    }

    /**
     * Clears out the collBotDecisions collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return GridRun The current object (for fluent API support)
     * @see        addBotDecisions()
     */
    public function clearBotDecisions()
    {
        $this->collBotDecisions = null; // important to set this to null since that means it is uninitialized
        $this->collBotDecisionsPartial = null;

        return $this;
    }

    /**
     * reset is the collBotDecisions collection loaded partially
     *
     * @return void
     */
    public function resetPartialBotDecisions($v = true)
    {
        $this->collBotDecisionsPartial = $v;
    }

    /**
     * Initializes the collBotDecisions collection.
     *
     * By default this just sets the collBotDecisions collection to an empty array (like clearcollBotDecisions());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initBotDecisions($overrideExisting = true)
    {
        if (null !== $this->collBotDecisions && !$overrideExisting) {
            return;
        }
        $this->collBotDecisions = new PropelObjectCollection();
        $this->collBotDecisions->setModel('BotDecision');
    }

    /**
     * Gets an array of BotDecision objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this GridRun is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|BotDecision[] List of BotDecision objects
     * @throws PropelException
     */
    public function getBotDecisions($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collBotDecisionsPartial && !$this->isNew();
        if (null === $this->collBotDecisions || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collBotDecisions) {
                // return empty collection
                $this->initBotDecisions();
            } else {
                $collBotDecisions = BotDecisionQuery::create(null, $criteria)
                    ->filterByGridRun($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collBotDecisionsPartial && count($collBotDecisions)) {
                      $this->initBotDecisions(false);

                      foreach ($collBotDecisions as $obj) {
                        if (false == $this->collBotDecisions->contains($obj)) {
                          $this->collBotDecisions->append($obj);
                        }
                      }

                      $this->collBotDecisionsPartial = true;
                    }

                    $collBotDecisions->getInternalIterator()->rewind();

                    return $collBotDecisions;
                }

                if ($partial && $this->collBotDecisions) {
                    foreach ($this->collBotDecisions as $obj) {
                        if ($obj->isNew()) {
                            $collBotDecisions[] = $obj;
                        }
                    }
                }

                $this->collBotDecisions = $collBotDecisions;
                $this->collBotDecisionsPartial = false;
            }
        }

        return $this->collBotDecisions;
    }

    /**
     * Sets a collection of BotDecision objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $botDecisions A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return GridRun The current object (for fluent API support)
     */
    public function setBotDecisions(PropelCollection $botDecisions, ?PropelPDO $con = null)
    {
        $botDecisionsToDelete = $this->getBotDecisions(new Criteria(), $con)->diff($botDecisions);


        $this->botDecisionsScheduledForDeletion = $botDecisionsToDelete;

        foreach ($botDecisionsToDelete as $botDecisionRemoved) {
            $botDecisionRemoved->setGridRun(null);
        }

        $this->collBotDecisions = null;
        foreach ($botDecisions as $botDecision) {
            $this->addBotDecision($botDecision);
        }

        $this->collBotDecisions = $botDecisions;
        $this->collBotDecisionsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related BotDecision objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related BotDecision objects.
     * @throws PropelException
     */
    public function countBotDecisions(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collBotDecisionsPartial && !$this->isNew();
        if (null === $this->collBotDecisions || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collBotDecisions) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getBotDecisions());
            }
            $query = BotDecisionQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByGridRun($this)
                ->count($con);
        }

        return count($this->collBotDecisions);
    }

    /**
     * Method called to associate a BotDecision object to this object
     * through the BotDecision foreign key attribute.
     *
     * @param    BotDecision $l BotDecision
     * @return GridRun The current object (for fluent API support)
     */
    public function addBotDecision(BotDecision $l)
    {
        if ($this->collBotDecisions === null) {
            $this->initBotDecisions();
            $this->collBotDecisionsPartial = true;
        }

        if (!in_array($l, $this->collBotDecisions->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddBotDecision($l);

            if ($this->botDecisionsScheduledForDeletion and $this->botDecisionsScheduledForDeletion->contains($l)) {
                $this->botDecisionsScheduledForDeletion->remove($this->botDecisionsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	BotDecision $botDecision The botDecision object to add.
     */
    protected function doAddBotDecision($botDecision)
    {
        $this->collBotDecisions[]= $botDecision;
        $botDecision->setGridRun($this);
    }

    /**
     * @param	BotDecision $botDecision The botDecision object to remove.
     * @return GridRun The current object (for fluent API support)
     */
    public function removeBotDecision($botDecision)
    {
        if ($this->getBotDecisions()->contains($botDecision)) {
            $this->collBotDecisions->remove($this->collBotDecisions->search($botDecision));
            if (null === $this->botDecisionsScheduledForDeletion) {
                $this->botDecisionsScheduledForDeletion = clone $this->collBotDecisions;
                $this->botDecisionsScheduledForDeletion->clear();
            }
            $this->botDecisionsScheduledForDeletion[]= clone $botDecision;
            $botDecision->setGridRun(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|BotDecision[] List of BotDecision objects
     */
    public function getBotDecisionsJoinAuthyGroup($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = BotDecisionQuery::create(null, $criteria);
        $query->joinWith('AuthyGroup', $join_behavior);

        return $this->getBotDecisions($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|BotDecision[] List of BotDecision objects
     */
    public function getBotDecisionsJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = BotDecisionQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getBotDecisions($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|BotDecision[] List of BotDecision objects
     */
    public function getBotDecisionsJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = BotDecisionQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getBotDecisions($query, $con);
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id_grid_run = null;
        $this->label = null;
        $this->symbol = null;
        $this->status = null;
        $this->kill_switch = null;
        $this->profile = null;
        $this->algo = null;
        $this->simulated = null;
        $this->p_low = null;
        $this->p_high = null;
        $this->n_levels = null;
        $this->spacing = null;
        $this->allocation = null;
        $this->budget_quote = null;
        $this->deploy_pct = null;
        $this->fee_pct = null;
        $this->max_position_quote = null;
        $this->max_order_quote = null;
        $this->daily_loss_limit_quote = null;
        $this->max_unrealized_loss_quote = null;
        $this->breakout_buffer_pct = null;
        $this->breakout_policy = null;
        $this->max_open_orders = null;
        $this->max_buy_levels_below = null;
        $this->trend_tf = null;
        $this->donchian_period = null;
        $this->trend_ema_fast = null;
        $this->trend_ema_slow = null;
        $this->atr_period = null;
        $this->atr_stop_mult = null;
        $this->atr_initial_mult = null;
        $this->reentry_cooldown = null;
        $this->engine_state = null;
        $this->last_tick_at = null;
        $this->last_price = null;
        $this->bal_base = null;
        $this->bal_quote = null;
        $this->sim_bal_base = null;
        $this->sim_bal_quote = null;
        $this->run_uid = null;
        $this->applied_geometry = null;
        $this->ledger_reset_at = null;
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
            if ($this->collBotOrders) {
                foreach ($this->collBotOrders as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collTradeCycles) {
                foreach ($this->collTradeCycles as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collBotEvents) {
                foreach ($this->collBotEvents as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collBotCommands) {
                foreach ($this->collBotCommands as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collBotDecisions) {
                foreach ($this->collBotDecisions as $o) {
                    $o->clearAllReferences($deep);
                }
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

        if ($this->collBotOrders instanceof PropelCollection) {
            $this->collBotOrders->clearIterator();
        }
        $this->collBotOrders = null;
        if ($this->collTradeCycles instanceof PropelCollection) {
            $this->collTradeCycles->clearIterator();
        }
        $this->collTradeCycles = null;
        if ($this->collBotEvents instanceof PropelCollection) {
            $this->collBotEvents->clearIterator();
        }
        $this->collBotEvents = null;
        if ($this->collBotCommands instanceof PropelCollection) {
            $this->collBotCommands->clearIterator();
        }
        $this->collBotCommands = null;
        if ($this->collBotDecisions instanceof PropelCollection) {
            $this->collBotDecisions->clearIterator();
        }
        $this->collBotDecisions = null;
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
        return (string) $this->exportTo(GridRunPeer::DEFAULT_STRING_FORMAT);
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
     * @return     GridRun The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged(){
        $this->modifiedColumns[] = GridRunPeer::DATE_MODIFICATION;

        return $this;
    }

}
