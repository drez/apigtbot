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
use App\MarketRegime;
use App\MarketRegimePeer;
use App\MarketRegimeQuery;

/**
 * Base class that represents a row from the 'market_regime' table.
 *
 * Regime History
 *
 * @package    propel.generator..om
 */
abstract class BaseMarketRegime extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'App\\MarketRegimePeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        MarketRegimePeer
     */
    protected static $peer;

    /**
     * The flag var to prevent infinite loop in deep copy
     * @var       boolean
     */
    protected $startCopy = false;

    /**
     * The value for the id_market_regime field.
     * @var        int
     */
    protected $id_market_regime;

    /**
     * The value for the symbol field.
     * @var        string
     */
    protected $symbol;

    /**
     * The value for the tf field.
     * @var        string
     */
    protected $tf;

    /**
     * The value for the price field.
     * @var        string
     */
    protected $price;

    /**
     * The value for the trend field.
     * Note: this column has a database default value of: 2
     * @var        int
     */
    protected $trend;

    /**
     * The value for the rsi14 field.
     * @var        string
     */
    protected $rsi14;

    /**
     * The value for the atr_pct field.
     * @var        string
     */
    protected $atr_pct;

    /**
     * The value for the adx14 field.
     * @var        string
     */
    protected $adx14;

    /**
     * The value for the atr_pct_rank field.
     * @var        string
     */
    protected $atr_pct_rank;

    /**
     * The value for the taker_buy_ratio field.
     * @var        string
     */
    protected $taker_buy_ratio;

    /**
     * The value for the vol_zscore field.
     * @var        string
     */
    protected $vol_zscore;

    /**
     * The value for the er20 field.
     * @var        string
     */
    protected $er20;

    /**
     * The value for the chop14 field.
     * @var        string
     */
    protected $chop14;

    /**
     * The value for the funding_pct field.
     * @var        string
     */
    protected $funding_pct;

    /**
     * The value for the funding_rate field.
     * @var        string
     */
    protected $funding_rate;

    /**
     * The value for the depth_imbalance field.
     * @var        string
     */
    protected $depth_imbalance;

    /**
     * The value for the depth_imbalance_avg field.
     * @var        string
     */
    protected $depth_imbalance_avg;

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
        $this->trend = 2;
    }

    /**
     * Initializes internal state of BaseMarketRegime object.
     * @see        applyDefaults()
     */
    public function __construct()
    {
        parent::__construct();
        $this->applyDefaultValues();
    }

    /**
     * @Field()
     * Get the [id_market_regime] column value.
     *
     * @return int
     */
    public function getIdMarketRegime()
    {

        return $this->id_market_regime;
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
     * Get the [tf] column value.
     * Timeframe
     * @return string
     */
    public function getTf()
    {

        return $this->tf;
    }

    /**
     * @Field()
     * Get the [price] column value.
     * Price
     * @return string
     */
    public function getPrice()
    {

        return $this->price;
    }

    /**
     * @Field()
     * Get the [trend] column value.
     * Trend
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getTrend()
    {
        if (null === $this->trend) {
            return null;
        }
        $valueSet = MarketRegimePeer::getValueSet(MarketRegimePeer::TREND);
        if (!isset($valueSet[$this->trend])) {
            throw new PropelException('Unknown stored enum key: ' . $this->trend);
        }

        return $valueSet[$this->trend];
    }

    /**
     * @Field()
     * Get the [rsi14] column value.
     * RSI14
     * @return string
     */
    public function getRsi14()
    {

        return $this->rsi14;
    }

    /**
     * @Field()
     * Get the [atr_pct] column value.
     * ATR %
     * @return string
     */
    public function getAtrPct()
    {

        return $this->atr_pct;
    }

    /**
     * @Field()
     * Get the [adx14] column value.
     * ADX14
     * @return string
     */
    public function getAdx14()
    {

        return $this->adx14;
    }

    /**
     * @Field()
     * Get the [atr_pct_rank] column value.
     * ATR% percentile
     * @return string
     */
    public function getAtrPctRank()
    {

        return $this->atr_pct_rank;
    }

    /**
     * @Field()
     * Get the [taker_buy_ratio] column value.
     * Taker buy ratio
     * @return string
     */
    public function getTakerBuyRatio()
    {

        return $this->taker_buy_ratio;
    }

    /**
     * @Field()
     * Get the [vol_zscore] column value.
     * Volume z-score
     * @return string
     */
    public function getVolZscore()
    {

        return $this->vol_zscore;
    }

    /**
     * @Field()
     * Get the [er20] column value.
     * Efficiency ratio
     * @return string
     */
    public function getEr20()
    {

        return $this->er20;
    }

    /**
     * @Field()
     * Get the [chop14] column value.
     * Choppiness
     * @return string
     */
    public function getChop14()
    {

        return $this->chop14;
    }

    /**
     * @Field()
     * Get the [funding_pct] column value.
     * Funding 30d percentile
     * @return string
     */
    public function getFundingPct()
    {

        return $this->funding_pct;
    }

    /**
     * @Field()
     * Get the [funding_rate] column value.
     * Funding rate
     * @return string
     */
    public function getFundingRate()
    {

        return $this->funding_rate;
    }

    /**
     * @Field()
     * Get the [depth_imbalance] column value.
     * Depth imbalance
     * @return string
     */
    public function getDepthImbalance()
    {

        return $this->depth_imbalance;
    }

    /**
     * @Field()
     * Get the [depth_imbalance_avg] column value.
     * Depth imbalance (smoothed)
     * @return string
     */
    public function getDepthImbalanceAvg()
    {

        return $this->depth_imbalance_avg;
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
     * Set the value of [id_market_regime] column.
     *
     * @param  int $v new value
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setIdMarketRegime($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_market_regime !== $v) {
            $this->id_market_regime = $v;
            $this->modifiedColumns[] = MarketRegimePeer::ID_MARKET_REGIME;
        }


        return $this;
    } // setIdMarketRegime()

    /**
     * Set the value of [symbol] column.
     * Symbol
     * @param  string $v new value
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setSymbol($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->symbol !== $v) {
            $this->symbol = $v;
            $this->modifiedColumns[] = MarketRegimePeer::SYMBOL;
        }


        return $this;
    } // setSymbol()

    /**
     * Set the value of [tf] column.
     * Timeframe
     * @param  string $v new value
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setTf($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->tf !== $v) {
            $this->tf = $v;
            $this->modifiedColumns[] = MarketRegimePeer::TF;
        }


        return $this;
    } // setTf()

    /**
     * Set the value of [price] column.
     * Price
     * @param  string $v new value
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setPrice($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->price !== $v) {
            $this->price = $v;
            $this->modifiedColumns[] = MarketRegimePeer::PRICE;
        }


        return $this;
    } // setPrice()

    /**
     * Set the value of [trend] column.
     * Trend
     * @param  int $v new value
     * @return MarketRegime The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setTrend($v)
    {
        if ($v !== null) {
            $valueSet = MarketRegimePeer::getValueSet(MarketRegimePeer::TREND);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->trend !== $v) {
            $this->trend = $v;
            $this->modifiedColumns[] = MarketRegimePeer::TREND;
        }


        return $this;
    } // setTrend()

    /**
     * Set the value of [rsi14] column.
     * RSI14
     * @param  string $v new value
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setRsi14($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->rsi14 !== $v) {
            $this->rsi14 = $v;
            $this->modifiedColumns[] = MarketRegimePeer::RSI14;
        }


        return $this;
    } // setRsi14()

    /**
     * Set the value of [atr_pct] column.
     * ATR %
     * @param  string $v new value
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setAtrPct($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->atr_pct !== $v) {
            $this->atr_pct = $v;
            $this->modifiedColumns[] = MarketRegimePeer::ATR_PCT;
        }


        return $this;
    } // setAtrPct()

    /**
     * Set the value of [adx14] column.
     * ADX14
     * @param  string $v new value
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setAdx14($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->adx14 !== $v) {
            $this->adx14 = $v;
            $this->modifiedColumns[] = MarketRegimePeer::ADX14;
        }


        return $this;
    } // setAdx14()

    /**
     * Set the value of [atr_pct_rank] column.
     * ATR% percentile
     * @param  string $v new value
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setAtrPctRank($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->atr_pct_rank !== $v) {
            $this->atr_pct_rank = $v;
            $this->modifiedColumns[] = MarketRegimePeer::ATR_PCT_RANK;
        }


        return $this;
    } // setAtrPctRank()

    /**
     * Set the value of [taker_buy_ratio] column.
     * Taker buy ratio
     * @param  string $v new value
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setTakerBuyRatio($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->taker_buy_ratio !== $v) {
            $this->taker_buy_ratio = $v;
            $this->modifiedColumns[] = MarketRegimePeer::TAKER_BUY_RATIO;
        }


        return $this;
    } // setTakerBuyRatio()

    /**
     * Set the value of [vol_zscore] column.
     * Volume z-score
     * @param  string $v new value
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setVolZscore($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->vol_zscore !== $v) {
            $this->vol_zscore = $v;
            $this->modifiedColumns[] = MarketRegimePeer::VOL_ZSCORE;
        }


        return $this;
    } // setVolZscore()

    /**
     * Set the value of [er20] column.
     * Efficiency ratio
     * @param  string $v new value
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setEr20($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->er20 !== $v) {
            $this->er20 = $v;
            $this->modifiedColumns[] = MarketRegimePeer::ER20;
        }


        return $this;
    } // setEr20()

    /**
     * Set the value of [chop14] column.
     * Choppiness
     * @param  string $v new value
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setChop14($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->chop14 !== $v) {
            $this->chop14 = $v;
            $this->modifiedColumns[] = MarketRegimePeer::CHOP14;
        }


        return $this;
    } // setChop14()

    /**
     * Set the value of [funding_pct] column.
     * Funding 30d percentile
     * @param  string $v new value
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setFundingPct($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->funding_pct !== $v) {
            $this->funding_pct = $v;
            $this->modifiedColumns[] = MarketRegimePeer::FUNDING_PCT;
        }


        return $this;
    } // setFundingPct()

    /**
     * Set the value of [funding_rate] column.
     * Funding rate
     * @param  string $v new value
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setFundingRate($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->funding_rate !== $v) {
            $this->funding_rate = $v;
            $this->modifiedColumns[] = MarketRegimePeer::FUNDING_RATE;
        }


        return $this;
    } // setFundingRate()

    /**
     * Set the value of [depth_imbalance] column.
     * Depth imbalance
     * @param  string $v new value
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setDepthImbalance($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->depth_imbalance !== $v) {
            $this->depth_imbalance = $v;
            $this->modifiedColumns[] = MarketRegimePeer::DEPTH_IMBALANCE;
        }


        return $this;
    } // setDepthImbalance()

    /**
     * Set the value of [depth_imbalance_avg] column.
     * Depth imbalance (smoothed)
     * @param  string $v new value
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setDepthImbalanceAvg($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->depth_imbalance_avg !== $v) {
            $this->depth_imbalance_avg = $v;
            $this->modifiedColumns[] = MarketRegimePeer::DEPTH_IMBALANCE_AVG;
        }


        return $this;
    } // setDepthImbalanceAvg()

    /**
     * Sets the value of [date_creation] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setDateCreation($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_creation !== null || $dt !== null) {
            $currentDateAsString = ($this->date_creation !== null && $tmpDt = new DateTime($this->date_creation)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_creation = $newDateAsString;
                $this->modifiedColumns[] = MarketRegimePeer::DATE_CREATION;
            }
        } // if either are not null


        return $this;
    } // setDateCreation()

    /**
     * Sets the value of [date_modification] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setDateModification($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_modification !== null || $dt !== null) {
            $currentDateAsString = ($this->date_modification !== null && $tmpDt = new DateTime($this->date_modification)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_modification = $newDateAsString;
                $this->modifiedColumns[] = MarketRegimePeer::DATE_MODIFICATION;
            }
        } // if either are not null


        return $this;
    } // setDateModification()

    /**
     * Set the value of [id_group_creation] column.
     *
     * @param  int $v new value
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setIdGroupCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_group_creation !== $v) {
            $this->id_group_creation = $v;
            $this->modifiedColumns[] = MarketRegimePeer::ID_GROUP_CREATION;
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
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setIdCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_creation !== $v) {
            $this->id_creation = $v;
            $this->modifiedColumns[] = MarketRegimePeer::ID_CREATION;
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
     * @return MarketRegime The current object (for fluent API support)
     */
    public function setIdModification($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_modification !== $v) {
            $this->id_modification = $v;
            $this->modifiedColumns[] = MarketRegimePeer::ID_MODIFICATION;
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
            if ($this->trend !== 2) {
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

            $this->id_market_regime = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
            $this->symbol = ($row[$startcol + 1] !== null) ? (string) $row[$startcol + 1] : null;
            $this->tf = ($row[$startcol + 2] !== null) ? (string) $row[$startcol + 2] : null;
            $this->price = ($row[$startcol + 3] !== null) ? (string) $row[$startcol + 3] : null;
            $this->trend = ($row[$startcol + 4] !== null) ? (int) $row[$startcol + 4] : null;
            $this->rsi14 = ($row[$startcol + 5] !== null) ? (string) $row[$startcol + 5] : null;
            $this->atr_pct = ($row[$startcol + 6] !== null) ? (string) $row[$startcol + 6] : null;
            $this->adx14 = ($row[$startcol + 7] !== null) ? (string) $row[$startcol + 7] : null;
            $this->atr_pct_rank = ($row[$startcol + 8] !== null) ? (string) $row[$startcol + 8] : null;
            $this->taker_buy_ratio = ($row[$startcol + 9] !== null) ? (string) $row[$startcol + 9] : null;
            $this->vol_zscore = ($row[$startcol + 10] !== null) ? (string) $row[$startcol + 10] : null;
            $this->er20 = ($row[$startcol + 11] !== null) ? (string) $row[$startcol + 11] : null;
            $this->chop14 = ($row[$startcol + 12] !== null) ? (string) $row[$startcol + 12] : null;
            $this->funding_pct = ($row[$startcol + 13] !== null) ? (string) $row[$startcol + 13] : null;
            $this->funding_rate = ($row[$startcol + 14] !== null) ? (string) $row[$startcol + 14] : null;
            $this->depth_imbalance = ($row[$startcol + 15] !== null) ? (string) $row[$startcol + 15] : null;
            $this->depth_imbalance_avg = ($row[$startcol + 16] !== null) ? (string) $row[$startcol + 16] : null;
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

            return $startcol + 22; // 22 = MarketRegimePeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating MarketRegime object", $e);
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
            $con = Propel::getConnection(MarketRegimePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = MarketRegimePeer::doSelectStmt($this->buildPkeyCriteria(), $con);
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
            $con = Propel::getConnection(MarketRegimePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = MarketRegimeQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                // GoatCheese behavior
                
                            if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                                \ApiGoat\Utility\TableVersion::bump('market_regime');
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
            $con = Propel::getConnection(MarketRegimePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
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
                                \ApiGoat\Utility\TableVersion::bump('market_regime');
                            }
                MarketRegimePeer::addInstanceToPool($this);
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

        $this->modifiedColumns[] = MarketRegimePeer::ID_MARKET_REGIME;
        if (null !== $this->id_market_regime) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . MarketRegimePeer::ID_MARKET_REGIME . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(MarketRegimePeer::ID_MARKET_REGIME)) {
            $modifiedColumns[':p' . $index++]  = '`id_market_regime`';
        }
        if ($this->isColumnModified(MarketRegimePeer::SYMBOL)) {
            $modifiedColumns[':p' . $index++]  = '`symbol`';
        }
        if ($this->isColumnModified(MarketRegimePeer::TF)) {
            $modifiedColumns[':p' . $index++]  = '`tf`';
        }
        if ($this->isColumnModified(MarketRegimePeer::PRICE)) {
            $modifiedColumns[':p' . $index++]  = '`price`';
        }
        if ($this->isColumnModified(MarketRegimePeer::TREND)) {
            $modifiedColumns[':p' . $index++]  = '`trend`';
        }
        if ($this->isColumnModified(MarketRegimePeer::RSI14)) {
            $modifiedColumns[':p' . $index++]  = '`rsi14`';
        }
        if ($this->isColumnModified(MarketRegimePeer::ATR_PCT)) {
            $modifiedColumns[':p' . $index++]  = '`atr_pct`';
        }
        if ($this->isColumnModified(MarketRegimePeer::ADX14)) {
            $modifiedColumns[':p' . $index++]  = '`adx14`';
        }
        if ($this->isColumnModified(MarketRegimePeer::ATR_PCT_RANK)) {
            $modifiedColumns[':p' . $index++]  = '`atr_pct_rank`';
        }
        if ($this->isColumnModified(MarketRegimePeer::TAKER_BUY_RATIO)) {
            $modifiedColumns[':p' . $index++]  = '`taker_buy_ratio`';
        }
        if ($this->isColumnModified(MarketRegimePeer::VOL_ZSCORE)) {
            $modifiedColumns[':p' . $index++]  = '`vol_zscore`';
        }
        if ($this->isColumnModified(MarketRegimePeer::ER20)) {
            $modifiedColumns[':p' . $index++]  = '`er20`';
        }
        if ($this->isColumnModified(MarketRegimePeer::CHOP14)) {
            $modifiedColumns[':p' . $index++]  = '`chop14`';
        }
        if ($this->isColumnModified(MarketRegimePeer::FUNDING_PCT)) {
            $modifiedColumns[':p' . $index++]  = '`funding_pct`';
        }
        if ($this->isColumnModified(MarketRegimePeer::FUNDING_RATE)) {
            $modifiedColumns[':p' . $index++]  = '`funding_rate`';
        }
        if ($this->isColumnModified(MarketRegimePeer::DEPTH_IMBALANCE)) {
            $modifiedColumns[':p' . $index++]  = '`depth_imbalance`';
        }
        if ($this->isColumnModified(MarketRegimePeer::DEPTH_IMBALANCE_AVG)) {
            $modifiedColumns[':p' . $index++]  = '`depth_imbalance_avg`';
        }
        if ($this->isColumnModified(MarketRegimePeer::DATE_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_creation`';
        }
        if ($this->isColumnModified(MarketRegimePeer::DATE_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_modification`';
        }
        if ($this->isColumnModified(MarketRegimePeer::ID_GROUP_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_group_creation`';
        }
        if ($this->isColumnModified(MarketRegimePeer::ID_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_creation`';
        }
        if ($this->isColumnModified(MarketRegimePeer::ID_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_modification`';
        }

        $sql = sprintf(
            'INSERT INTO `market_regime` (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case '`id_market_regime`':
                        $stmt->bindValue($identifier, $this->id_market_regime, PDO::PARAM_INT);
                        break;
                    case '`symbol`':
                        $stmt->bindValue($identifier, $this->symbol, PDO::PARAM_STR);
                        break;
                    case '`tf`':
                        $stmt->bindValue($identifier, $this->tf, PDO::PARAM_STR);
                        break;
                    case '`price`':
                        $stmt->bindValue($identifier, $this->price, PDO::PARAM_STR);
                        break;
                    case '`trend`':
                        $stmt->bindValue($identifier, $this->trend, PDO::PARAM_INT);
                        break;
                    case '`rsi14`':
                        $stmt->bindValue($identifier, $this->rsi14, PDO::PARAM_STR);
                        break;
                    case '`atr_pct`':
                        $stmt->bindValue($identifier, $this->atr_pct, PDO::PARAM_STR);
                        break;
                    case '`adx14`':
                        $stmt->bindValue($identifier, $this->adx14, PDO::PARAM_STR);
                        break;
                    case '`atr_pct_rank`':
                        $stmt->bindValue($identifier, $this->atr_pct_rank, PDO::PARAM_STR);
                        break;
                    case '`taker_buy_ratio`':
                        $stmt->bindValue($identifier, $this->taker_buy_ratio, PDO::PARAM_STR);
                        break;
                    case '`vol_zscore`':
                        $stmt->bindValue($identifier, $this->vol_zscore, PDO::PARAM_STR);
                        break;
                    case '`er20`':
                        $stmt->bindValue($identifier, $this->er20, PDO::PARAM_STR);
                        break;
                    case '`chop14`':
                        $stmt->bindValue($identifier, $this->chop14, PDO::PARAM_STR);
                        break;
                    case '`funding_pct`':
                        $stmt->bindValue($identifier, $this->funding_pct, PDO::PARAM_STR);
                        break;
                    case '`funding_rate`':
                        $stmt->bindValue($identifier, $this->funding_rate, PDO::PARAM_STR);
                        break;
                    case '`depth_imbalance`':
                        $stmt->bindValue($identifier, $this->depth_imbalance, PDO::PARAM_STR);
                        break;
                    case '`depth_imbalance_avg`':
                        $stmt->bindValue($identifier, $this->depth_imbalance_avg, PDO::PARAM_STR);
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
        $this->setIdMarketRegime($pk);

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


            if (($retval = MarketRegimePeer::doValidate($this, $columns)) !== true) {
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
        $pos = MarketRegimePeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
        if (isset($alreadyDumpedObjects['MarketRegime'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['MarketRegime'][$this->getPrimaryKey()] = true;
        $keys = MarketRegimePeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getIdMarketRegime(),
            $keys[1] => $this->getSymbol(),
            $keys[2] => $this->getTf(),
            $keys[3] => $this->getPrice(),
            $keys[4] => $this->getTrend(),
            $keys[5] => $this->getRsi14(),
            $keys[6] => $this->getAtrPct(),
            $keys[7] => $this->getAdx14(),
            $keys[8] => $this->getAtrPctRank(),
            $keys[9] => $this->getTakerBuyRatio(),
            $keys[10] => $this->getVolZscore(),
            $keys[11] => $this->getEr20(),
            $keys[12] => $this->getChop14(),
            $keys[13] => $this->getFundingPct(),
            $keys[14] => $this->getFundingRate(),
            $keys[15] => $this->getDepthImbalance(),
            $keys[16] => $this->getDepthImbalanceAvg(),
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
        $pos = MarketRegimePeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

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
                $this->setIdMarketRegime($value);
                break;
            case 1:
                $this->setSymbol($value);
                break;
            case 2:
                $this->setTf($value);
                break;
            case 3:
                $this->setPrice($value);
                break;
            case 4:
                $valueSet = MarketRegimePeer::getValueSet(MarketRegimePeer::TREND);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setTrend($value);
                break;
            case 5:
                $this->setRsi14($value);
                break;
            case 6:
                $this->setAtrPct($value);
                break;
            case 7:
                $this->setAdx14($value);
                break;
            case 8:
                $this->setAtrPctRank($value);
                break;
            case 9:
                $this->setTakerBuyRatio($value);
                break;
            case 10:
                $this->setVolZscore($value);
                break;
            case 11:
                $this->setEr20($value);
                break;
            case 12:
                $this->setChop14($value);
                break;
            case 13:
                $this->setFundingPct($value);
                break;
            case 14:
                $this->setFundingRate($value);
                break;
            case 15:
                $this->setDepthImbalance($value);
                break;
            case 16:
                $this->setDepthImbalanceAvg($value);
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
        $keys = MarketRegimePeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setIdMarketRegime($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setSymbol($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setTf($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setPrice($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setTrend($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setRsi14($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setAtrPct($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setAdx14($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setAtrPctRank($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setTakerBuyRatio($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setVolZscore($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setEr20($arr[$keys[11]]);
        if (array_key_exists($keys[12], $arr)) $this->setChop14($arr[$keys[12]]);
        if (array_key_exists($keys[13], $arr)) $this->setFundingPct($arr[$keys[13]]);
        if (array_key_exists($keys[14], $arr)) $this->setFundingRate($arr[$keys[14]]);
        if (array_key_exists($keys[15], $arr)) $this->setDepthImbalance($arr[$keys[15]]);
        if (array_key_exists($keys[16], $arr)) $this->setDepthImbalanceAvg($arr[$keys[16]]);
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
        $criteria = new Criteria(MarketRegimePeer::DATABASE_NAME);

        if ($this->isColumnModified(MarketRegimePeer::ID_MARKET_REGIME)) $criteria->add(MarketRegimePeer::ID_MARKET_REGIME, $this->id_market_regime);
        if ($this->isColumnModified(MarketRegimePeer::SYMBOL)) $criteria->add(MarketRegimePeer::SYMBOL, $this->symbol);
        if ($this->isColumnModified(MarketRegimePeer::TF)) $criteria->add(MarketRegimePeer::TF, $this->tf);
        if ($this->isColumnModified(MarketRegimePeer::PRICE)) $criteria->add(MarketRegimePeer::PRICE, $this->price);
        if ($this->isColumnModified(MarketRegimePeer::TREND)) $criteria->add(MarketRegimePeer::TREND, $this->trend);
        if ($this->isColumnModified(MarketRegimePeer::RSI14)) $criteria->add(MarketRegimePeer::RSI14, $this->rsi14);
        if ($this->isColumnModified(MarketRegimePeer::ATR_PCT)) $criteria->add(MarketRegimePeer::ATR_PCT, $this->atr_pct);
        if ($this->isColumnModified(MarketRegimePeer::ADX14)) $criteria->add(MarketRegimePeer::ADX14, $this->adx14);
        if ($this->isColumnModified(MarketRegimePeer::ATR_PCT_RANK)) $criteria->add(MarketRegimePeer::ATR_PCT_RANK, $this->atr_pct_rank);
        if ($this->isColumnModified(MarketRegimePeer::TAKER_BUY_RATIO)) $criteria->add(MarketRegimePeer::TAKER_BUY_RATIO, $this->taker_buy_ratio);
        if ($this->isColumnModified(MarketRegimePeer::VOL_ZSCORE)) $criteria->add(MarketRegimePeer::VOL_ZSCORE, $this->vol_zscore);
        if ($this->isColumnModified(MarketRegimePeer::ER20)) $criteria->add(MarketRegimePeer::ER20, $this->er20);
        if ($this->isColumnModified(MarketRegimePeer::CHOP14)) $criteria->add(MarketRegimePeer::CHOP14, $this->chop14);
        if ($this->isColumnModified(MarketRegimePeer::FUNDING_PCT)) $criteria->add(MarketRegimePeer::FUNDING_PCT, $this->funding_pct);
        if ($this->isColumnModified(MarketRegimePeer::FUNDING_RATE)) $criteria->add(MarketRegimePeer::FUNDING_RATE, $this->funding_rate);
        if ($this->isColumnModified(MarketRegimePeer::DEPTH_IMBALANCE)) $criteria->add(MarketRegimePeer::DEPTH_IMBALANCE, $this->depth_imbalance);
        if ($this->isColumnModified(MarketRegimePeer::DEPTH_IMBALANCE_AVG)) $criteria->add(MarketRegimePeer::DEPTH_IMBALANCE_AVG, $this->depth_imbalance_avg);
        if ($this->isColumnModified(MarketRegimePeer::DATE_CREATION)) $criteria->add(MarketRegimePeer::DATE_CREATION, $this->date_creation);
        if ($this->isColumnModified(MarketRegimePeer::DATE_MODIFICATION)) $criteria->add(MarketRegimePeer::DATE_MODIFICATION, $this->date_modification);
        if ($this->isColumnModified(MarketRegimePeer::ID_GROUP_CREATION)) $criteria->add(MarketRegimePeer::ID_GROUP_CREATION, $this->id_group_creation);
        if ($this->isColumnModified(MarketRegimePeer::ID_CREATION)) $criteria->add(MarketRegimePeer::ID_CREATION, $this->id_creation);
        if ($this->isColumnModified(MarketRegimePeer::ID_MODIFICATION)) $criteria->add(MarketRegimePeer::ID_MODIFICATION, $this->id_modification);

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
        $criteria = new Criteria(MarketRegimePeer::DATABASE_NAME);
        $criteria->add(MarketRegimePeer::ID_MARKET_REGIME, $this->id_market_regime);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getIdMarketRegime();
    }

    /**
     * Generic method to set the primary key (id_market_regime column).
     *
     * @param  int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setIdMarketRegime($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getIdMarketRegime();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of MarketRegime (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setSymbol($this->getSymbol());
        $copyObj->setTf($this->getTf());
        $copyObj->setPrice($this->getPrice());
        $copyObj->setTrend($this->getTrend());
        $copyObj->setRsi14($this->getRsi14());
        $copyObj->setAtrPct($this->getAtrPct());
        $copyObj->setAdx14($this->getAdx14());
        $copyObj->setAtrPctRank($this->getAtrPctRank());
        $copyObj->setTakerBuyRatio($this->getTakerBuyRatio());
        $copyObj->setVolZscore($this->getVolZscore());
        $copyObj->setEr20($this->getEr20());
        $copyObj->setChop14($this->getChop14());
        $copyObj->setFundingPct($this->getFundingPct());
        $copyObj->setFundingRate($this->getFundingRate());
        $copyObj->setDepthImbalance($this->getDepthImbalance());
        $copyObj->setDepthImbalanceAvg($this->getDepthImbalanceAvg());
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
            $copyObj->setIdMarketRegime(NULL); // this is a auto-increment column, so set to default value
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
     * @return MarketRegime Clone of current object.
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
     * @return MarketRegimePeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new MarketRegimePeer();
        }

        return self::$peer;
    }

    /**
     * Declares an association between this object and a AuthyGroup object.
     *
     * @param                  AuthyGroup $v
     * @return MarketRegime The current object (for fluent API support)
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
            $v->addMarketRegime($this);
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
                $this->aAuthyGroup->addMarketRegimes($this);
             */
        }

        return $this->aAuthyGroup;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return MarketRegime The current object (for fluent API support)
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
            $v->addMarketRegimeRelatedByIdCreation($this);
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
                $this->aAuthyRelatedByIdCreation->addMarketRegimesRelatedByIdCreation($this);
             */
        }

        return $this->aAuthyRelatedByIdCreation;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return MarketRegime The current object (for fluent API support)
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
            $v->addMarketRegimeRelatedByIdModification($this);
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
                $this->aAuthyRelatedByIdModification->addMarketRegimesRelatedByIdModification($this);
             */
        }

        return $this->aAuthyRelatedByIdModification;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id_market_regime = null;
        $this->symbol = null;
        $this->tf = null;
        $this->price = null;
        $this->trend = null;
        $this->rsi14 = null;
        $this->atr_pct = null;
        $this->adx14 = null;
        $this->atr_pct_rank = null;
        $this->taker_buy_ratio = null;
        $this->vol_zscore = null;
        $this->er20 = null;
        $this->chop14 = null;
        $this->funding_pct = null;
        $this->funding_rate = null;
        $this->depth_imbalance = null;
        $this->depth_imbalance_avg = null;
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
        return (string) $this->exportTo(MarketRegimePeer::DEFAULT_STRING_FORMAT);
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
     * @return     MarketRegime The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged(){
        $this->modifiedColumns[] = MarketRegimePeer::DATE_MODIFICATION;

        return $this;
    }

}
