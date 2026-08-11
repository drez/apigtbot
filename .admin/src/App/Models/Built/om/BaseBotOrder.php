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
use App\BotOrder;
use App\BotOrderPeer;
use App\BotOrderQuery;
use App\GridRun;
use App\GridRunQuery;

/**
 * Base class that represents a row from the 'bot_order' table.
 *
 * Order
 *
 * @package    propel.generator..om
 */
abstract class BaseBotOrder extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'App\\BotOrderPeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        BotOrderPeer
     */
    protected static $peer;

    /**
     * The flag var to prevent infinite loop in deep copy
     * @var       boolean
     */
    protected $startCopy = false;

    /**
     * The value for the id_bot_order field.
     * @var        int
     */
    protected $id_bot_order;

    /**
     * The value for the id_grid_run field.
     * @var        int
     */
    protected $id_grid_run;

    /**
     * The value for the client_order_id field.
     * @var        string
     */
    protected $client_order_id;

    /**
     * The value for the exchange_order_id field.
     * @var        string
     */
    protected $exchange_order_id;

    /**
     * The value for the level_idx field.
     * @var        int
     */
    protected $level_idx;

    /**
     * The value for the side field.
     * @var        int
     */
    protected $side;

    /**
     * The value for the state field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $state;

    /**
     * The value for the price field.
     * @var        string
     */
    protected $price;

    /**
     * The value for the qty field.
     * @var        string
     */
    protected $qty;

    /**
     * The value for the filled_qty field.
     * Note: this column has a database default value of: '0'
     * @var        string
     */
    protected $filled_qty;

    /**
     * The value for the fee_paid field.
     * Note: this column has a database default value of: '0'
     * @var        string
     */
    protected $fee_paid;

    /**
     * The value for the fee_asset field.
     * @var        string
     */
    protected $fee_asset;

    /**
     * The value for the is_legacy field.
     * Note: this column has a database default value of: false
     * @var        boolean
     */
    protected $is_legacy;

    /**
     * The value for the legacy_buy_price field.
     * @var        string
     */
    protected $legacy_buy_price;

    /**
     * The value for the legacy_buy_fee field.
     * @var        string
     */
    protected $legacy_buy_fee;

    /**
     * The value for the simulated field.
     * Note: this column has a database default value of: false
     * @var        boolean
     */
    protected $simulated;

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
        $this->state = 0;
        $this->filled_qty = '0';
        $this->fee_paid = '0';
        $this->is_legacy = false;
        $this->simulated = false;
    }

    /**
     * Initializes internal state of BaseBotOrder object.
     * @see        applyDefaults()
     */
    public function __construct()
    {
        parent::__construct();
        $this->applyDefaultValues();
    }

    /**
     * @Field()
     * Get the [id_bot_order] column value.
     *
     * @return int
     */
    public function getIdBotOrder()
    {

        return $this->id_bot_order;
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
     * Get the [client_order_id] column value.
     * Client order id
     * @return string
     */
    public function getClientOrderId()
    {

        return $this->client_order_id;
    }

    /**
     * @Field()
     * Get the [exchange_order_id] column value.
     * Exchange id
     * @return string
     */
    public function getExchangeOrderId()
    {

        return $this->exchange_order_id;
    }

    /**
     * @Field()
     * Get the [level_idx] column value.
     * Level
     * @return int
     */
    public function getLevelIdx()
    {

        return $this->level_idx;
    }

    /**
     * @Field()
     * Get the [side] column value.
     * Side
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getSide()
    {
        if (null === $this->side) {
            return null;
        }
        $valueSet = BotOrderPeer::getValueSet(BotOrderPeer::SIDE);
        if (!isset($valueSet[$this->side])) {
            throw new PropelException('Unknown stored enum key: ' . $this->side);
        }

        return $valueSet[$this->side];
    }

    /**
     * @Field()
     * Get the [state] column value.
     * State
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getState()
    {
        if (null === $this->state) {
            return null;
        }
        $valueSet = BotOrderPeer::getValueSet(BotOrderPeer::STATE);
        if (!isset($valueSet[$this->state])) {
            throw new PropelException('Unknown stored enum key: ' . $this->state);
        }

        return $valueSet[$this->state];
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
     * Get the [qty] column value.
     * Qty
     * @return string
     */
    public function getQty()
    {

        return $this->qty;
    }

    /**
     * @Field()
     * Get the [filled_qty] column value.
     * Filled qty
     * @return string
     */
    public function getFilledQty()
    {

        return $this->filled_qty;
    }

    /**
     * @Field()
     * Get the [fee_paid] column value.
     * Fee paid
     * @return string
     */
    public function getFeePaid()
    {

        return $this->fee_paid;
    }

    /**
     * @Field()
     * Get the [fee_asset] column value.
     * Fee asset
     * @return string
     */
    public function getFeeAsset()
    {

        return $this->fee_asset;
    }

    /**
     * @Field()
     * Get the [is_legacy] column value.
     * Legacy exit
     * @return boolean
     */
    public function getIsLegacy()
    {

        return $this->is_legacy;
    }

    /**
     * @Field()
     * Get the [legacy_buy_price] column value.
     * Legacy buy price (daemon-managed)
     * @return string
     */
    public function getLegacyBuyPrice()
    {

        return $this->legacy_buy_price;
    }

    /**
     * @Field()
     * Get the [legacy_buy_fee] column value.
     * Legacy buy fee (daemon-managed)
     * @return string
     */
    public function getLegacyBuyFee()
    {

        return $this->legacy_buy_fee;
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
     * Set the value of [id_bot_order] column.
     *
     * @param  int $v new value
     * @return BotOrder The current object (for fluent API support)
     */
    public function setIdBotOrder($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_bot_order !== $v) {
            $this->id_bot_order = $v;
            $this->modifiedColumns[] = BotOrderPeer::ID_BOT_ORDER;
        }


        return $this;
    } // setIdBotOrder()

    /**
     * Set the value of [id_grid_run] column.
     * Run
     * @param  int $v new value
     * @return BotOrder The current object (for fluent API support)
     */
    public function setIdGridRun($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_grid_run !== $v) {
            $this->id_grid_run = $v;
            $this->modifiedColumns[] = BotOrderPeer::ID_GRID_RUN;
        }

        if ($this->aGridRun !== null && $this->aGridRun->getIdGridRun() !== $v) {
            $this->aGridRun = null;
        }


        return $this;
    } // setIdGridRun()

    /**
     * Set the value of [client_order_id] column.
     * Client order id
     * @param  string $v new value
     * @return BotOrder The current object (for fluent API support)
     */
    public function setClientOrderId($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->client_order_id !== $v) {
            $this->client_order_id = $v;
            $this->modifiedColumns[] = BotOrderPeer::CLIENT_ORDER_ID;
        }


        return $this;
    } // setClientOrderId()

    /**
     * Set the value of [exchange_order_id] column.
     * Exchange id
     * @param  string $v new value
     * @return BotOrder The current object (for fluent API support)
     */
    public function setExchangeOrderId($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->exchange_order_id !== $v) {
            $this->exchange_order_id = $v;
            $this->modifiedColumns[] = BotOrderPeer::EXCHANGE_ORDER_ID;
        }


        return $this;
    } // setExchangeOrderId()

    /**
     * Set the value of [level_idx] column.
     * Level
     * @param  int $v new value
     * @return BotOrder The current object (for fluent API support)
     */
    public function setLevelIdx($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->level_idx !== $v) {
            $this->level_idx = $v;
            $this->modifiedColumns[] = BotOrderPeer::LEVEL_IDX;
        }


        return $this;
    } // setLevelIdx()

    /**
     * Set the value of [side] column.
     * Side
     * @param  int $v new value
     * @return BotOrder The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setSide($v)
    {
        if ($v !== null) {
            $valueSet = BotOrderPeer::getValueSet(BotOrderPeer::SIDE);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->side !== $v) {
            $this->side = $v;
            $this->modifiedColumns[] = BotOrderPeer::SIDE;
        }


        return $this;
    } // setSide()

    /**
     * Set the value of [state] column.
     * State
     * @param  int $v new value
     * @return BotOrder The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setState($v)
    {
        if ($v !== null) {
            $valueSet = BotOrderPeer::getValueSet(BotOrderPeer::STATE);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->state !== $v) {
            $this->state = $v;
            $this->modifiedColumns[] = BotOrderPeer::STATE;
        }


        return $this;
    } // setState()

    /**
     * Set the value of [price] column.
     * Price
     * @param  string $v new value
     * @return BotOrder The current object (for fluent API support)
     */
    public function setPrice($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->price !== $v) {
            $this->price = $v;
            $this->modifiedColumns[] = BotOrderPeer::PRICE;
        }


        return $this;
    } // setPrice()

    /**
     * Set the value of [qty] column.
     * Qty
     * @param  string $v new value
     * @return BotOrder The current object (for fluent API support)
     */
    public function setQty($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->qty !== $v) {
            $this->qty = $v;
            $this->modifiedColumns[] = BotOrderPeer::QTY;
        }


        return $this;
    } // setQty()

    /**
     * Set the value of [filled_qty] column.
     * Filled qty
     * @param  string $v new value
     * @return BotOrder The current object (for fluent API support)
     */
    public function setFilledQty($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->filled_qty !== $v) {
            $this->filled_qty = $v;
            $this->modifiedColumns[] = BotOrderPeer::FILLED_QTY;
        }


        return $this;
    } // setFilledQty()

    /**
     * Set the value of [fee_paid] column.
     * Fee paid
     * @param  string $v new value
     * @return BotOrder The current object (for fluent API support)
     */
    public function setFeePaid($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->fee_paid !== $v) {
            $this->fee_paid = $v;
            $this->modifiedColumns[] = BotOrderPeer::FEE_PAID;
        }


        return $this;
    } // setFeePaid()

    /**
     * Set the value of [fee_asset] column.
     * Fee asset
     * @param  string $v new value
     * @return BotOrder The current object (for fluent API support)
     */
    public function setFeeAsset($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->fee_asset !== $v) {
            $this->fee_asset = $v;
            $this->modifiedColumns[] = BotOrderPeer::FEE_ASSET;
        }


        return $this;
    } // setFeeAsset()

    /**
     * Sets the value of the [is_legacy] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * Legacy exit
     * @param boolean|integer|string $v The new value
     * @return BotOrder The current object (for fluent API support)
     */
    public function setIsLegacy($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->is_legacy !== $v) {
            $this->is_legacy = $v;
            $this->modifiedColumns[] = BotOrderPeer::IS_LEGACY;
        }


        return $this;
    } // setIsLegacy()

    /**
     * Set the value of [legacy_buy_price] column.
     * Legacy buy price (daemon-managed)
     * @param  string $v new value
     * @return BotOrder The current object (for fluent API support)
     */
    public function setLegacyBuyPrice($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->legacy_buy_price !== $v) {
            $this->legacy_buy_price = $v;
            $this->modifiedColumns[] = BotOrderPeer::LEGACY_BUY_PRICE;
        }


        return $this;
    } // setLegacyBuyPrice()

    /**
     * Set the value of [legacy_buy_fee] column.
     * Legacy buy fee (daemon-managed)
     * @param  string $v new value
     * @return BotOrder The current object (for fluent API support)
     */
    public function setLegacyBuyFee($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->legacy_buy_fee !== $v) {
            $this->legacy_buy_fee = $v;
            $this->modifiedColumns[] = BotOrderPeer::LEGACY_BUY_FEE;
        }


        return $this;
    } // setLegacyBuyFee()

    /**
     * Sets the value of the [simulated] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * Simulated
     * @param boolean|integer|string $v The new value
     * @return BotOrder The current object (for fluent API support)
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
            $this->modifiedColumns[] = BotOrderPeer::SIMULATED;
        }


        return $this;
    } // setSimulated()

    /**
     * Sets the value of [date_creation] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return BotOrder The current object (for fluent API support)
     */
    public function setDateCreation($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_creation !== null || $dt !== null) {
            $currentDateAsString = ($this->date_creation !== null && $tmpDt = new DateTime($this->date_creation)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_creation = $newDateAsString;
                $this->modifiedColumns[] = BotOrderPeer::DATE_CREATION;
            }
        } // if either are not null


        return $this;
    } // setDateCreation()

    /**
     * Sets the value of [date_modification] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return BotOrder The current object (for fluent API support)
     */
    public function setDateModification($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_modification !== null || $dt !== null) {
            $currentDateAsString = ($this->date_modification !== null && $tmpDt = new DateTime($this->date_modification)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_modification = $newDateAsString;
                $this->modifiedColumns[] = BotOrderPeer::DATE_MODIFICATION;
            }
        } // if either are not null


        return $this;
    } // setDateModification()

    /**
     * Set the value of [id_group_creation] column.
     *
     * @param  int $v new value
     * @return BotOrder The current object (for fluent API support)
     */
    public function setIdGroupCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_group_creation !== $v) {
            $this->id_group_creation = $v;
            $this->modifiedColumns[] = BotOrderPeer::ID_GROUP_CREATION;
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
     * @return BotOrder The current object (for fluent API support)
     */
    public function setIdCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_creation !== $v) {
            $this->id_creation = $v;
            $this->modifiedColumns[] = BotOrderPeer::ID_CREATION;
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
     * @return BotOrder The current object (for fluent API support)
     */
    public function setIdModification($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_modification !== $v) {
            $this->id_modification = $v;
            $this->modifiedColumns[] = BotOrderPeer::ID_MODIFICATION;
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
            if ($this->state !== 0) {
                return false;
            }

            if ($this->filled_qty !== '0') {
                return false;
            }

            if ($this->fee_paid !== '0') {
                return false;
            }

            if ($this->is_legacy !== false) {
                return false;
            }

            if ($this->simulated !== false) {
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

            $this->id_bot_order = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
            $this->id_grid_run = ($row[$startcol + 1] !== null) ? (int) $row[$startcol + 1] : null;
            $this->client_order_id = ($row[$startcol + 2] !== null) ? (string) $row[$startcol + 2] : null;
            $this->exchange_order_id = ($row[$startcol + 3] !== null) ? (string) $row[$startcol + 3] : null;
            $this->level_idx = ($row[$startcol + 4] !== null) ? (int) $row[$startcol + 4] : null;
            $this->side = ($row[$startcol + 5] !== null) ? (int) $row[$startcol + 5] : null;
            $this->state = ($row[$startcol + 6] !== null) ? (int) $row[$startcol + 6] : null;
            $this->price = ($row[$startcol + 7] !== null) ? (string) $row[$startcol + 7] : null;
            $this->qty = ($row[$startcol + 8] !== null) ? (string) $row[$startcol + 8] : null;
            $this->filled_qty = ($row[$startcol + 9] !== null) ? (string) $row[$startcol + 9] : null;
            $this->fee_paid = ($row[$startcol + 10] !== null) ? (string) $row[$startcol + 10] : null;
            $this->fee_asset = ($row[$startcol + 11] !== null) ? (string) $row[$startcol + 11] : null;
            $this->is_legacy = ($row[$startcol + 12] !== null) ? (boolean) $row[$startcol + 12] : null;
            $this->legacy_buy_price = ($row[$startcol + 13] !== null) ? (string) $row[$startcol + 13] : null;
            $this->legacy_buy_fee = ($row[$startcol + 14] !== null) ? (string) $row[$startcol + 14] : null;
            $this->simulated = ($row[$startcol + 15] !== null) ? (boolean) $row[$startcol + 15] : null;
            $this->date_creation = ($row[$startcol + 16] !== null) ? (string) $row[$startcol + 16] : null;
            $this->date_modification = ($row[$startcol + 17] !== null) ? (string) $row[$startcol + 17] : null;
            $this->id_group_creation = ($row[$startcol + 18] !== null) ? (int) $row[$startcol + 18] : null;
            $this->id_creation = ($row[$startcol + 19] !== null) ? (int) $row[$startcol + 19] : null;
            $this->id_modification = ($row[$startcol + 20] !== null) ? (int) $row[$startcol + 20] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);

            return $startcol + 21; // 21 = BotOrderPeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating BotOrder object", $e);
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
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = BotOrderPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
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
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = BotOrderQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                // GoatCheese behavior
                
                            if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                                \ApiGoat\Utility\TableVersion::bump('bot_order');
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
            $con = Propel::getConnection(BotOrderPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
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
                                \ApiGoat\Utility\TableVersion::bump('bot_order');
                            }
                BotOrderPeer::addInstanceToPool($this);
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

        $this->modifiedColumns[] = BotOrderPeer::ID_BOT_ORDER;
        if (null !== $this->id_bot_order) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . BotOrderPeer::ID_BOT_ORDER . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(BotOrderPeer::ID_BOT_ORDER)) {
            $modifiedColumns[':p' . $index++]  = '`id_bot_order`';
        }
        if ($this->isColumnModified(BotOrderPeer::ID_GRID_RUN)) {
            $modifiedColumns[':p' . $index++]  = '`id_grid_run`';
        }
        if ($this->isColumnModified(BotOrderPeer::CLIENT_ORDER_ID)) {
            $modifiedColumns[':p' . $index++]  = '`client_order_id`';
        }
        if ($this->isColumnModified(BotOrderPeer::EXCHANGE_ORDER_ID)) {
            $modifiedColumns[':p' . $index++]  = '`exchange_order_id`';
        }
        if ($this->isColumnModified(BotOrderPeer::LEVEL_IDX)) {
            $modifiedColumns[':p' . $index++]  = '`level_idx`';
        }
        if ($this->isColumnModified(BotOrderPeer::SIDE)) {
            $modifiedColumns[':p' . $index++]  = '`side`';
        }
        if ($this->isColumnModified(BotOrderPeer::STATE)) {
            $modifiedColumns[':p' . $index++]  = '`state`';
        }
        if ($this->isColumnModified(BotOrderPeer::PRICE)) {
            $modifiedColumns[':p' . $index++]  = '`price`';
        }
        if ($this->isColumnModified(BotOrderPeer::QTY)) {
            $modifiedColumns[':p' . $index++]  = '`qty`';
        }
        if ($this->isColumnModified(BotOrderPeer::FILLED_QTY)) {
            $modifiedColumns[':p' . $index++]  = '`filled_qty`';
        }
        if ($this->isColumnModified(BotOrderPeer::FEE_PAID)) {
            $modifiedColumns[':p' . $index++]  = '`fee_paid`';
        }
        if ($this->isColumnModified(BotOrderPeer::FEE_ASSET)) {
            $modifiedColumns[':p' . $index++]  = '`fee_asset`';
        }
        if ($this->isColumnModified(BotOrderPeer::IS_LEGACY)) {
            $modifiedColumns[':p' . $index++]  = '`is_legacy`';
        }
        if ($this->isColumnModified(BotOrderPeer::LEGACY_BUY_PRICE)) {
            $modifiedColumns[':p' . $index++]  = '`legacy_buy_price`';
        }
        if ($this->isColumnModified(BotOrderPeer::LEGACY_BUY_FEE)) {
            $modifiedColumns[':p' . $index++]  = '`legacy_buy_fee`';
        }
        if ($this->isColumnModified(BotOrderPeer::SIMULATED)) {
            $modifiedColumns[':p' . $index++]  = '`simulated`';
        }
        if ($this->isColumnModified(BotOrderPeer::DATE_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_creation`';
        }
        if ($this->isColumnModified(BotOrderPeer::DATE_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_modification`';
        }
        if ($this->isColumnModified(BotOrderPeer::ID_GROUP_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_group_creation`';
        }
        if ($this->isColumnModified(BotOrderPeer::ID_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_creation`';
        }
        if ($this->isColumnModified(BotOrderPeer::ID_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_modification`';
        }

        $sql = sprintf(
            'INSERT INTO `bot_order` (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case '`id_bot_order`':
                        $stmt->bindValue($identifier, $this->id_bot_order, PDO::PARAM_INT);
                        break;
                    case '`id_grid_run`':
                        $stmt->bindValue($identifier, $this->id_grid_run, PDO::PARAM_INT);
                        break;
                    case '`client_order_id`':
                        $stmt->bindValue($identifier, $this->client_order_id, PDO::PARAM_STR);
                        break;
                    case '`exchange_order_id`':
                        $stmt->bindValue($identifier, $this->exchange_order_id, PDO::PARAM_STR);
                        break;
                    case '`level_idx`':
                        $stmt->bindValue($identifier, $this->level_idx, PDO::PARAM_INT);
                        break;
                    case '`side`':
                        $stmt->bindValue($identifier, $this->side, PDO::PARAM_INT);
                        break;
                    case '`state`':
                        $stmt->bindValue($identifier, $this->state, PDO::PARAM_INT);
                        break;
                    case '`price`':
                        $stmt->bindValue($identifier, $this->price, PDO::PARAM_STR);
                        break;
                    case '`qty`':
                        $stmt->bindValue($identifier, $this->qty, PDO::PARAM_STR);
                        break;
                    case '`filled_qty`':
                        $stmt->bindValue($identifier, $this->filled_qty, PDO::PARAM_STR);
                        break;
                    case '`fee_paid`':
                        $stmt->bindValue($identifier, $this->fee_paid, PDO::PARAM_STR);
                        break;
                    case '`fee_asset`':
                        $stmt->bindValue($identifier, $this->fee_asset, PDO::PARAM_STR);
                        break;
                    case '`is_legacy`':
                        $stmt->bindValue($identifier, (int) $this->is_legacy, PDO::PARAM_INT);
                        break;
                    case '`legacy_buy_price`':
                        $stmt->bindValue($identifier, $this->legacy_buy_price, PDO::PARAM_STR);
                        break;
                    case '`legacy_buy_fee`':
                        $stmt->bindValue($identifier, $this->legacy_buy_fee, PDO::PARAM_STR);
                        break;
                    case '`simulated`':
                        $stmt->bindValue($identifier, (int) $this->simulated, PDO::PARAM_INT);
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
        $this->setIdBotOrder($pk);

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


            if (($retval = BotOrderPeer::doValidate($this, $columns)) !== true) {
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
        $pos = BotOrderPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
        if (isset($alreadyDumpedObjects['BotOrder'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['BotOrder'][$this->getPrimaryKey()] = true;
        $keys = BotOrderPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getIdBotOrder(),
            $keys[1] => $this->getIdGridRun(),
            $keys[2] => $this->getClientOrderId(),
            $keys[3] => $this->getExchangeOrderId(),
            $keys[4] => $this->getLevelIdx(),
            $keys[5] => $this->getSide(),
            $keys[6] => $this->getState(),
            $keys[7] => $this->getPrice(),
            $keys[8] => $this->getQty(),
            $keys[9] => $this->getFilledQty(),
            $keys[10] => $this->getFeePaid(),
            $keys[11] => $this->getFeeAsset(),
            $keys[12] => $this->getIsLegacy(),
            $keys[13] => $this->getLegacyBuyPrice(),
            $keys[14] => $this->getLegacyBuyFee(),
            $keys[15] => $this->getSimulated(),
            $keys[16] => $this->getDateCreation(),
            $keys[17] => $this->getDateModification(),
            $keys[18] => $this->getIdGroupCreation(),
            $keys[19] => $this->getIdCreation(),
            $keys[20] => $this->getIdModification(),
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
        $pos = BotOrderPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

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
                $this->setIdBotOrder($value);
                break;
            case 1:
                $this->setIdGridRun($value);
                break;
            case 2:
                $this->setClientOrderId($value);
                break;
            case 3:
                $this->setExchangeOrderId($value);
                break;
            case 4:
                $this->setLevelIdx($value);
                break;
            case 5:
                $valueSet = BotOrderPeer::getValueSet(BotOrderPeer::SIDE);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setSide($value);
                break;
            case 6:
                $valueSet = BotOrderPeer::getValueSet(BotOrderPeer::STATE);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setState($value);
                break;
            case 7:
                $this->setPrice($value);
                break;
            case 8:
                $this->setQty($value);
                break;
            case 9:
                $this->setFilledQty($value);
                break;
            case 10:
                $this->setFeePaid($value);
                break;
            case 11:
                $this->setFeeAsset($value);
                break;
            case 12:
                $this->setIsLegacy($value);
                break;
            case 13:
                $this->setLegacyBuyPrice($value);
                break;
            case 14:
                $this->setLegacyBuyFee($value);
                break;
            case 15:
                $this->setSimulated($value);
                break;
            case 16:
                $this->setDateCreation($value);
                break;
            case 17:
                $this->setDateModification($value);
                break;
            case 18:
                $this->setIdGroupCreation($value);
                break;
            case 19:
                $this->setIdCreation($value);
                break;
            case 20:
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
        $keys = BotOrderPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setIdBotOrder($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setIdGridRun($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setClientOrderId($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setExchangeOrderId($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setLevelIdx($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setSide($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setState($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setPrice($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setQty($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setFilledQty($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setFeePaid($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setFeeAsset($arr[$keys[11]]);
        if (array_key_exists($keys[12], $arr)) $this->setIsLegacy($arr[$keys[12]]);
        if (array_key_exists($keys[13], $arr)) $this->setLegacyBuyPrice($arr[$keys[13]]);
        if (array_key_exists($keys[14], $arr)) $this->setLegacyBuyFee($arr[$keys[14]]);
        if (array_key_exists($keys[15], $arr)) $this->setSimulated($arr[$keys[15]]);
        if (array_key_exists($keys[16], $arr)) $this->setDateCreation($arr[$keys[16]]);
        if (array_key_exists($keys[17], $arr)) $this->setDateModification($arr[$keys[17]]);
        if (array_key_exists($keys[18], $arr)) $this->setIdGroupCreation($arr[$keys[18]]);
        if (array_key_exists($keys[19], $arr)) $this->setIdCreation($arr[$keys[19]]);
        if (array_key_exists($keys[20], $arr)) $this->setIdModification($arr[$keys[20]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(BotOrderPeer::DATABASE_NAME);

        if ($this->isColumnModified(BotOrderPeer::ID_BOT_ORDER)) $criteria->add(BotOrderPeer::ID_BOT_ORDER, $this->id_bot_order);
        if ($this->isColumnModified(BotOrderPeer::ID_GRID_RUN)) $criteria->add(BotOrderPeer::ID_GRID_RUN, $this->id_grid_run);
        if ($this->isColumnModified(BotOrderPeer::CLIENT_ORDER_ID)) $criteria->add(BotOrderPeer::CLIENT_ORDER_ID, $this->client_order_id);
        if ($this->isColumnModified(BotOrderPeer::EXCHANGE_ORDER_ID)) $criteria->add(BotOrderPeer::EXCHANGE_ORDER_ID, $this->exchange_order_id);
        if ($this->isColumnModified(BotOrderPeer::LEVEL_IDX)) $criteria->add(BotOrderPeer::LEVEL_IDX, $this->level_idx);
        if ($this->isColumnModified(BotOrderPeer::SIDE)) $criteria->add(BotOrderPeer::SIDE, $this->side);
        if ($this->isColumnModified(BotOrderPeer::STATE)) $criteria->add(BotOrderPeer::STATE, $this->state);
        if ($this->isColumnModified(BotOrderPeer::PRICE)) $criteria->add(BotOrderPeer::PRICE, $this->price);
        if ($this->isColumnModified(BotOrderPeer::QTY)) $criteria->add(BotOrderPeer::QTY, $this->qty);
        if ($this->isColumnModified(BotOrderPeer::FILLED_QTY)) $criteria->add(BotOrderPeer::FILLED_QTY, $this->filled_qty);
        if ($this->isColumnModified(BotOrderPeer::FEE_PAID)) $criteria->add(BotOrderPeer::FEE_PAID, $this->fee_paid);
        if ($this->isColumnModified(BotOrderPeer::FEE_ASSET)) $criteria->add(BotOrderPeer::FEE_ASSET, $this->fee_asset);
        if ($this->isColumnModified(BotOrderPeer::IS_LEGACY)) $criteria->add(BotOrderPeer::IS_LEGACY, $this->is_legacy);
        if ($this->isColumnModified(BotOrderPeer::LEGACY_BUY_PRICE)) $criteria->add(BotOrderPeer::LEGACY_BUY_PRICE, $this->legacy_buy_price);
        if ($this->isColumnModified(BotOrderPeer::LEGACY_BUY_FEE)) $criteria->add(BotOrderPeer::LEGACY_BUY_FEE, $this->legacy_buy_fee);
        if ($this->isColumnModified(BotOrderPeer::SIMULATED)) $criteria->add(BotOrderPeer::SIMULATED, $this->simulated);
        if ($this->isColumnModified(BotOrderPeer::DATE_CREATION)) $criteria->add(BotOrderPeer::DATE_CREATION, $this->date_creation);
        if ($this->isColumnModified(BotOrderPeer::DATE_MODIFICATION)) $criteria->add(BotOrderPeer::DATE_MODIFICATION, $this->date_modification);
        if ($this->isColumnModified(BotOrderPeer::ID_GROUP_CREATION)) $criteria->add(BotOrderPeer::ID_GROUP_CREATION, $this->id_group_creation);
        if ($this->isColumnModified(BotOrderPeer::ID_CREATION)) $criteria->add(BotOrderPeer::ID_CREATION, $this->id_creation);
        if ($this->isColumnModified(BotOrderPeer::ID_MODIFICATION)) $criteria->add(BotOrderPeer::ID_MODIFICATION, $this->id_modification);

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
        $criteria = new Criteria(BotOrderPeer::DATABASE_NAME);
        $criteria->add(BotOrderPeer::ID_BOT_ORDER, $this->id_bot_order);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getIdBotOrder();
    }

    /**
     * Generic method to set the primary key (id_bot_order column).
     *
     * @param  int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setIdBotOrder($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getIdBotOrder();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of BotOrder (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setIdGridRun($this->getIdGridRun());
        $copyObj->setClientOrderId($this->getClientOrderId());
        $copyObj->setExchangeOrderId($this->getExchangeOrderId());
        $copyObj->setLevelIdx($this->getLevelIdx());
        $copyObj->setSide($this->getSide());
        $copyObj->setState($this->getState());
        $copyObj->setPrice($this->getPrice());
        $copyObj->setQty($this->getQty());
        $copyObj->setFilledQty($this->getFilledQty());
        $copyObj->setFeePaid($this->getFeePaid());
        $copyObj->setFeeAsset($this->getFeeAsset());
        $copyObj->setIsLegacy($this->getIsLegacy());
        $copyObj->setLegacyBuyPrice($this->getLegacyBuyPrice());
        $copyObj->setLegacyBuyFee($this->getLegacyBuyFee());
        $copyObj->setSimulated($this->getSimulated());
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
            $copyObj->setIdBotOrder(NULL); // this is a auto-increment column, so set to default value
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
     * @return BotOrder Clone of current object.
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
     * @return BotOrderPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new BotOrderPeer();
        }

        return self::$peer;
    }

    /**
     * Declares an association between this object and a GridRun object.
     *
     * @param                  GridRun $v
     * @return BotOrder The current object (for fluent API support)
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
            $v->addBotOrder($this);
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
                $this->aGridRun->addBotOrders($this);
             */
        }

        return $this->aGridRun;
    }

    /**
     * Declares an association between this object and a AuthyGroup object.
     *
     * @param                  AuthyGroup $v
     * @return BotOrder The current object (for fluent API support)
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
            $v->addBotOrder($this);
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
                $this->aAuthyGroup->addBotOrders($this);
             */
        }

        return $this->aAuthyGroup;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return BotOrder The current object (for fluent API support)
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
            $v->addBotOrderRelatedByIdCreation($this);
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
                $this->aAuthyRelatedByIdCreation->addBotOrdersRelatedByIdCreation($this);
             */
        }

        return $this->aAuthyRelatedByIdCreation;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return BotOrder The current object (for fluent API support)
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
            $v->addBotOrderRelatedByIdModification($this);
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
                $this->aAuthyRelatedByIdModification->addBotOrdersRelatedByIdModification($this);
             */
        }

        return $this->aAuthyRelatedByIdModification;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id_bot_order = null;
        $this->id_grid_run = null;
        $this->client_order_id = null;
        $this->exchange_order_id = null;
        $this->level_idx = null;
        $this->side = null;
        $this->state = null;
        $this->price = null;
        $this->qty = null;
        $this->filled_qty = null;
        $this->fee_paid = null;
        $this->fee_asset = null;
        $this->is_legacy = null;
        $this->legacy_buy_price = null;
        $this->legacy_buy_fee = null;
        $this->simulated = null;
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
        return (string) $this->exportTo(BotOrderPeer::DEFAULT_STRING_FORMAT);
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
     * @return     BotOrder The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged(){
        $this->modifiedColumns[] = BotOrderPeer::DATE_MODIFICATION;

        return $this;
    }

}
