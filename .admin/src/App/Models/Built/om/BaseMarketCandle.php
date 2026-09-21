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
use App\MarketCandle;
use App\MarketCandlePeer;
use App\MarketCandleQuery;

/**
 * Base class that represents a row from the 'market_candle' table.
 *
 * Candles
 *
 * @package    propel.generator..om
 */
abstract class BaseMarketCandle extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'App\\MarketCandlePeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        MarketCandlePeer
     */
    protected static $peer;

    /**
     * The flag var to prevent infinite loop in deep copy
     * @var       boolean
     */
    protected $startCopy = false;

    /**
     * The value for the id_market_candle field.
     * @var        int
     */
    protected $id_market_candle;

    /**
     * The value for the symbol field.
     * @var        string
     */
    protected $symbol;

    /**
     * The value for the tf field.
     * @var        int
     */
    protected $tf;

    /**
     * The value for the open_time field.
     * @var        int
     */
    protected $open_time;

    /**
     * The value for the open field.
     * @var        string
     */
    protected $open;

    /**
     * The value for the high field.
     * @var        string
     */
    protected $high;

    /**
     * The value for the low field.
     * @var        string
     */
    protected $low;

    /**
     * The value for the close field.
     * @var        string
     */
    protected $close;

    /**
     * The value for the volume field.
     * Note: this column has a database default value of: '0'
     * @var        string
     */
    protected $volume;

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
        $this->volume = '0';
    }

    /**
     * Initializes internal state of BaseMarketCandle object.
     * @see        applyDefaults()
     */
    public function __construct()
    {
        parent::__construct();
        $this->applyDefaultValues();
    }

    /**
     * @Field()
     * Get the [id_market_candle] column value.
     *
     * @return int
     */
    public function getIdMarketCandle()
    {

        return $this->id_market_candle;
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
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getTf()
    {
        if (null === $this->tf) {
            return null;
        }
        $valueSet = MarketCandlePeer::getValueSet(MarketCandlePeer::TF);
        if (!isset($valueSet[$this->tf])) {
            throw new PropelException('Unknown stored enum key: ' . $this->tf);
        }

        return $valueSet[$this->tf];
    }

    /**
     * @Field()
     * Get the [open_time] column value.
     * Open time (epoch s)
     * @return int
     */
    public function getOpenTime()
    {

        return $this->open_time;
    }

    /**
     * @Field()
     * Get the [open] column value.
     * Open
     * @return string
     */
    public function getOpen()
    {

        return $this->open;
    }

    /**
     * @Field()
     * Get the [high] column value.
     * High
     * @return string
     */
    public function getHigh()
    {

        return $this->high;
    }

    /**
     * @Field()
     * Get the [low] column value.
     * Low
     * @return string
     */
    public function getLow()
    {

        return $this->low;
    }

    /**
     * @Field()
     * Get the [close] column value.
     * Close
     * @return string
     */
    public function getClose()
    {

        return $this->close;
    }

    /**
     * @Field()
     * Get the [volume] column value.
     * Volume
     * @return string
     */
    public function getVolume()
    {

        return $this->volume;
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
            return self::formatStrftime($format, $dt);
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
            return self::formatStrftime($format, $dt);
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
     * strftime()-compatible formatting for the temporal accessors above.
     *
     * strftime() is deprecated as of PHP 8.1 and removed in PHP 9, so the
     * conversion specifiers are expanded here instead. The expansion follows the
     * C/POSIX locale, which is what these accessors have always resolved to in
     * practice. Anything not in the tables below -- including the %E / %O locale
     * modifiers and a trailing bare '%' -- raises rather than silently
     * mis-formatting.
     *
     * The one deliberate divergence from strftime() is %s: PHP's strftime()
     * double-applies the timezone offset for that specifier, this returns the
     * true Unix timestamp.
     *
     * @param  string   $format A strftime()-style format string.
     * @param  DateTime $dt     The value to format.
     * @return string
     * @throws PropelException If the format uses an unsupported conversion specifier.
     */
    protected static function formatStrftime($format, $dt)
    {
        // Composite specifiers, expanded to their C/POSIX-locale definitions.
        static $composite = array(
            'c' => '%a %b %e %H:%M:%S %Y',
            'D' => '%m/%d/%y',
            'F' => '%Y-%m-%d',
            'r' => '%I:%M:%S %p',
            'R' => '%H:%M',
            'T' => '%H:%M:%S',
            'x' => '%m/%d/%y',
            'X' => '%H:%M:%S',
        );
        // Specifiers that are exactly one date() format character.
        static $direct = array(
            'a' => 'D', 'A' => 'l', 'b' => 'M', 'h' => 'M', 'B' => 'F',
            'd' => 'd', 'H' => 'H', 'I' => 'h', 'm' => 'm', 'M' => 'i',
            'p' => 'A', 'P' => 'a', 's' => 'U', 'S' => 's', 'u' => 'N',
            'w' => 'w', 'y' => 'y', 'Y' => 'Y', 'G' => 'o', 'z' => 'O',
            'Z' => 'T',
        );
        // Literal passthroughs.
        static $literal = array('n' => "\n", 't' => "\t", '%' => '%');

        $out = '';
        $len = strlen($format);

        for ($i = 0; $i < $len; $i++) {
            if ('%' !== $format[$i]) {
                $out .= $format[$i];
                continue;
            }
            if (++$i === $len) {
                throw new PropelException("Malformed strftime() format string (trailing '%'): " . var_export($format, true));
            }

            $c = $format[$i];

            if (isset($composite[$c])) {
                $out .= self::formatStrftime($composite[$c], $dt);
            } elseif (isset($direct[$c])) {
                $out .= $dt->format($direct[$c]);
            } elseif (isset($literal[$c])) {
                $out .= $literal[$c];
            } elseif ('e' === $c) {
                $out .= sprintf('%2d', $dt->format('j'));                // space-padded day of the month
            } elseif ('k' === $c) {
                $out .= sprintf('%2d', $dt->format('G'));                // space-padded hour, 24h clock
            } elseif ('l' === $c) {
                $out .= sprintf('%2d', $dt->format('g'));                // space-padded hour, 12h clock
            } elseif ('j' === $c) {
                $out .= sprintf('%03d', $dt->format('z') + 1);           // day of the year, 001-366
            } elseif ('V' === $c) {
                $out .= sprintf('%02d', $dt->format('W'));               // ISO-8601 week number
            } elseif ('C' === $c) {
                $out .= sprintf('%02d', (int) ($dt->format('Y') / 100)); // century
            } elseif ('g' === $c) {
                $out .= substr('0' . $dt->format('o'), -2);              // 2-digit ISO-8601 year
            } elseif ('U' === $c) {
                // Week of the year, Sunday as the first day: (yday + 7 - wday) / 7.
                $out .= sprintf('%02d', (int) (($dt->format('z') + 7 - $dt->format('w')) / 7));
            } elseif ('W' === $c) {
                // Week of the year, Monday as the first day: (yday + 7 - (wday + 6) % 7) / 7.
                $out .= sprintf('%02d', (int) (($dt->format('z') + 7 - ($dt->format('N') - 1)) / 7));
            } else {
                throw new PropelException("Unsupported strftime() conversion specifier '%" . $c . "' in format " . var_export($format, true));
            }
        }

        return $out;
    }

    /**
     * Set the value of [id_market_candle] column.
     *
     * @param  int $v new value
     * @return MarketCandle The current object (for fluent API support)
     */
    public function setIdMarketCandle($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_market_candle !== $v) {
            $this->id_market_candle = $v;
            $this->modifiedColumns[] = MarketCandlePeer::ID_MARKET_CANDLE;
        }


        return $this;
    } // setIdMarketCandle()

    /**
     * Set the value of [symbol] column.
     * Symbol
     * @param  string $v new value
     * @return MarketCandle The current object (for fluent API support)
     */
    public function setSymbol($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->symbol !== $v) {
            $this->symbol = $v;
            $this->modifiedColumns[] = MarketCandlePeer::SYMBOL;
        }


        return $this;
    } // setSymbol()

    /**
     * Set the value of [tf] column.
     * Timeframe
     * @param  int $v new value
     * @return MarketCandle The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setTf($v)
    {
        if ($v !== null) {
            $valueSet = MarketCandlePeer::getValueSet(MarketCandlePeer::TF);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->tf !== $v) {
            $this->tf = $v;
            $this->modifiedColumns[] = MarketCandlePeer::TF;
        }


        return $this;
    } // setTf()

    /**
     * Set the value of [open_time] column.
     * Open time (epoch s)
     * @param  int $v new value
     * @return MarketCandle The current object (for fluent API support)
     */
    public function setOpenTime($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->open_time !== $v) {
            $this->open_time = $v;
            $this->modifiedColumns[] = MarketCandlePeer::OPEN_TIME;
        }


        return $this;
    } // setOpenTime()

    /**
     * Set the value of [open] column.
     * Open
     * @param  string $v new value
     * @return MarketCandle The current object (for fluent API support)
     */
    public function setOpen($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->open !== $v) {
            $this->open = $v;
            $this->modifiedColumns[] = MarketCandlePeer::OPEN;
        }


        return $this;
    } // setOpen()

    /**
     * Set the value of [high] column.
     * High
     * @param  string $v new value
     * @return MarketCandle The current object (for fluent API support)
     */
    public function setHigh($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->high !== $v) {
            $this->high = $v;
            $this->modifiedColumns[] = MarketCandlePeer::HIGH;
        }


        return $this;
    } // setHigh()

    /**
     * Set the value of [low] column.
     * Low
     * @param  string $v new value
     * @return MarketCandle The current object (for fluent API support)
     */
    public function setLow($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->low !== $v) {
            $this->low = $v;
            $this->modifiedColumns[] = MarketCandlePeer::LOW;
        }


        return $this;
    } // setLow()

    /**
     * Set the value of [close] column.
     * Close
     * @param  string $v new value
     * @return MarketCandle The current object (for fluent API support)
     */
    public function setClose($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->close !== $v) {
            $this->close = $v;
            $this->modifiedColumns[] = MarketCandlePeer::CLOSE;
        }


        return $this;
    } // setClose()

    /**
     * Set the value of [volume] column.
     * Volume
     * @param  string $v new value
     * @return MarketCandle The current object (for fluent API support)
     */
    public function setVolume($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->volume !== $v) {
            $this->volume = $v;
            $this->modifiedColumns[] = MarketCandlePeer::VOLUME;
        }


        return $this;
    } // setVolume()

    /**
     * Sets the value of [date_creation] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return MarketCandle The current object (for fluent API support)
     */
    public function setDateCreation($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_creation !== null || $dt !== null) {
            $currentDateAsString = ($this->date_creation !== null && $tmpDt = new DateTime($this->date_creation)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_creation = $newDateAsString;
                $this->modifiedColumns[] = MarketCandlePeer::DATE_CREATION;
            }
        } // if either are not null


        return $this;
    } // setDateCreation()

    /**
     * Sets the value of [date_modification] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return MarketCandle The current object (for fluent API support)
     */
    public function setDateModification($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_modification !== null || $dt !== null) {
            $currentDateAsString = ($this->date_modification !== null && $tmpDt = new DateTime($this->date_modification)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_modification = $newDateAsString;
                $this->modifiedColumns[] = MarketCandlePeer::DATE_MODIFICATION;
            }
        } // if either are not null


        return $this;
    } // setDateModification()

    /**
     * Set the value of [id_group_creation] column.
     *
     * @param  int $v new value
     * @return MarketCandle The current object (for fluent API support)
     */
    public function setIdGroupCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_group_creation !== $v) {
            $this->id_group_creation = $v;
            $this->modifiedColumns[] = MarketCandlePeer::ID_GROUP_CREATION;
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
     * @return MarketCandle The current object (for fluent API support)
     */
    public function setIdCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_creation !== $v) {
            $this->id_creation = $v;
            $this->modifiedColumns[] = MarketCandlePeer::ID_CREATION;
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
     * @return MarketCandle The current object (for fluent API support)
     */
    public function setIdModification($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_modification !== $v) {
            $this->id_modification = $v;
            $this->modifiedColumns[] = MarketCandlePeer::ID_MODIFICATION;
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
            if ($this->volume !== '0') {
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

            $this->id_market_candle = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
            $this->symbol = ($row[$startcol + 1] !== null) ? (string) $row[$startcol + 1] : null;
            $this->tf = ($row[$startcol + 2] !== null) ? (int) $row[$startcol + 2] : null;
            $this->open_time = ($row[$startcol + 3] !== null) ? (int) $row[$startcol + 3] : null;
            $this->open = ($row[$startcol + 4] !== null) ? (string) $row[$startcol + 4] : null;
            $this->high = ($row[$startcol + 5] !== null) ? (string) $row[$startcol + 5] : null;
            $this->low = ($row[$startcol + 6] !== null) ? (string) $row[$startcol + 6] : null;
            $this->close = ($row[$startcol + 7] !== null) ? (string) $row[$startcol + 7] : null;
            $this->volume = ($row[$startcol + 8] !== null) ? (string) $row[$startcol + 8] : null;
            $this->date_creation = ($row[$startcol + 9] !== null) ? (string) $row[$startcol + 9] : null;
            $this->date_modification = ($row[$startcol + 10] !== null) ? (string) $row[$startcol + 10] : null;
            $this->id_group_creation = ($row[$startcol + 11] !== null) ? (int) $row[$startcol + 11] : null;
            $this->id_creation = ($row[$startcol + 12] !== null) ? (int) $row[$startcol + 12] : null;
            $this->id_modification = ($row[$startcol + 13] !== null) ? (int) $row[$startcol + 13] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);

            return $startcol + 14; // 14 = MarketCandlePeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating MarketCandle object", $e);
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
            $con = Propel::getConnection(MarketCandlePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = MarketCandlePeer::doSelectStmt($this->buildPkeyCriteria(), $con);
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
            $con = Propel::getConnection(MarketCandlePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = MarketCandleQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                // GoatCheese behavior

                            if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                                \ApiGoat\Utility\TableVersion::bump('market_candle');
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
            $con = Propel::getConnection(MarketCandlePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
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
                    $this->setIdGroupCreation( (isset($_SESSION[_AUTH_VAR]) && is_object($_SESSION[_AUTH_VAR]) && get_class($_SESSION[_AUTH_VAR]) === 'ApiGoat\Sessions\AuthySession')?$_SESSION[_AUTH_VAR]->getIdPrimaryGroup():null );
                    if(!$this->getIdCreation())
                        $this->setIdCreation( (isset($_SESSION[_AUTH_VAR]) && is_object($_SESSION[_AUTH_VAR]) && get_class($_SESSION[_AUTH_VAR]) === 'ApiGoat\Sessions\AuthySession')?$_SESSION[_AUTH_VAR]->getIdAuthy():null );
                    if(!$this->getIdModification())
                        $this->setIdModification( (isset($_SESSION[_AUTH_VAR]) && is_object($_SESSION[_AUTH_VAR]) && get_class($_SESSION[_AUTH_VAR]) === 'ApiGoat\Sessions\AuthySession')?$_SESSION[_AUTH_VAR]->getIdAuthy():null );

            } else {
                $ret = $ret && $this->preUpdate($con);
                // add_tablestamp behavior
                if ($this->isModified() ) {
                    $this->setDateCreation( $this->getDateCreation() );
                    $this->setDateModification(time());
                    $this->setIdGroupCreation( (isset($_SESSION[_AUTH_VAR]) && is_object($_SESSION[_AUTH_VAR]) && get_class($_SESSION[_AUTH_VAR]) === 'ApiGoat\Sessions\AuthySession')?$_SESSION[_AUTH_VAR]->getIdPrimaryGroup():null );
                    if(!$this->getIdCreation())
                        $this->setIdCreation( (isset($_SESSION[_AUTH_VAR]) && is_object($_SESSION[_AUTH_VAR]) && get_class($_SESSION[_AUTH_VAR]) === 'ApiGoat\Sessions\AuthySession')?$_SESSION[_AUTH_VAR]->getIdAuthy():null );
                    if(!$this->getIdModification())
                        $this->setIdModification( (isset($_SESSION[_AUTH_VAR]) && is_object($_SESSION[_AUTH_VAR]) && get_class($_SESSION[_AUTH_VAR]) === 'ApiGoat\Sessions\AuthySession')?$_SESSION[_AUTH_VAR]->getIdAuthy():null );
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
                                \ApiGoat\Utility\TableVersion::bump('market_candle');
                            }
                MarketCandlePeer::addInstanceToPool($this);
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

        $this->modifiedColumns[] = MarketCandlePeer::ID_MARKET_CANDLE;
        if (null !== $this->id_market_candle) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . MarketCandlePeer::ID_MARKET_CANDLE . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(MarketCandlePeer::ID_MARKET_CANDLE)) {
            $modifiedColumns[':p' . $index++]  = '`id_market_candle`';
        }
        if ($this->isColumnModified(MarketCandlePeer::SYMBOL)) {
            $modifiedColumns[':p' . $index++]  = '`symbol`';
        }
        if ($this->isColumnModified(MarketCandlePeer::TF)) {
            $modifiedColumns[':p' . $index++]  = '`tf`';
        }
        if ($this->isColumnModified(MarketCandlePeer::OPEN_TIME)) {
            $modifiedColumns[':p' . $index++]  = '`open_time`';
        }
        if ($this->isColumnModified(MarketCandlePeer::OPEN)) {
            $modifiedColumns[':p' . $index++]  = '`open`';
        }
        if ($this->isColumnModified(MarketCandlePeer::HIGH)) {
            $modifiedColumns[':p' . $index++]  = '`high`';
        }
        if ($this->isColumnModified(MarketCandlePeer::LOW)) {
            $modifiedColumns[':p' . $index++]  = '`low`';
        }
        if ($this->isColumnModified(MarketCandlePeer::CLOSE)) {
            $modifiedColumns[':p' . $index++]  = '`close`';
        }
        if ($this->isColumnModified(MarketCandlePeer::VOLUME)) {
            $modifiedColumns[':p' . $index++]  = '`volume`';
        }
        if ($this->isColumnModified(MarketCandlePeer::DATE_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_creation`';
        }
        if ($this->isColumnModified(MarketCandlePeer::DATE_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_modification`';
        }
        if ($this->isColumnModified(MarketCandlePeer::ID_GROUP_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_group_creation`';
        }
        if ($this->isColumnModified(MarketCandlePeer::ID_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_creation`';
        }
        if ($this->isColumnModified(MarketCandlePeer::ID_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_modification`';
        }

        $sql = sprintf(
            'INSERT INTO `market_candle` (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case '`id_market_candle`':
                        $stmt->bindValue($identifier, $this->id_market_candle, PDO::PARAM_INT);
                        break;
                    case '`symbol`':
                        $stmt->bindValue($identifier, $this->symbol, PDO::PARAM_STR);
                        break;
                    case '`tf`':
                        $stmt->bindValue($identifier, $this->tf, PDO::PARAM_INT);
                        break;
                    case '`open_time`':
                        $stmt->bindValue($identifier, $this->open_time, PDO::PARAM_INT);
                        break;
                    case '`open`':
                        $stmt->bindValue($identifier, $this->open, PDO::PARAM_STR);
                        break;
                    case '`high`':
                        $stmt->bindValue($identifier, $this->high, PDO::PARAM_STR);
                        break;
                    case '`low`':
                        $stmt->bindValue($identifier, $this->low, PDO::PARAM_STR);
                        break;
                    case '`close`':
                        $stmt->bindValue($identifier, $this->close, PDO::PARAM_STR);
                        break;
                    case '`volume`':
                        $stmt->bindValue($identifier, $this->volume, PDO::PARAM_STR);
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
        $this->setIdMarketCandle($pk);

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


            if (($retval = MarketCandlePeer::doValidate($this, $columns)) !== true) {
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
        $pos = MarketCandlePeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
        if (isset($alreadyDumpedObjects['MarketCandle'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['MarketCandle'][$this->getPrimaryKey()] = true;
        $keys = MarketCandlePeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getIdMarketCandle(),
            $keys[1] => $this->getSymbol(),
            $keys[2] => $this->getTf(),
            $keys[3] => $this->getOpenTime(),
            $keys[4] => $this->getOpen(),
            $keys[5] => $this->getHigh(),
            $keys[6] => $this->getLow(),
            $keys[7] => $this->getClose(),
            $keys[8] => $this->getVolume(),
            $keys[9] => $this->getDateCreation(),
            $keys[10] => $this->getDateModification(),
            $keys[11] => $this->getIdGroupCreation(),
            $keys[12] => $this->getIdCreation(),
            $keys[13] => $this->getIdModification(),
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
        $pos = MarketCandlePeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

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
                $this->setIdMarketCandle($value);
                break;
            case 1:
                $this->setSymbol($value);
                break;
            case 2:
                $valueSet = MarketCandlePeer::getValueSet(MarketCandlePeer::TF);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setTf($value);
                break;
            case 3:
                $this->setOpenTime($value);
                break;
            case 4:
                $this->setOpen($value);
                break;
            case 5:
                $this->setHigh($value);
                break;
            case 6:
                $this->setLow($value);
                break;
            case 7:
                $this->setClose($value);
                break;
            case 8:
                $this->setVolume($value);
                break;
            case 9:
                $this->setDateCreation($value);
                break;
            case 10:
                $this->setDateModification($value);
                break;
            case 11:
                $this->setIdGroupCreation($value);
                break;
            case 12:
                $this->setIdCreation($value);
                break;
            case 13:
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
        $keys = MarketCandlePeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setIdMarketCandle($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setSymbol($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setTf($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setOpenTime($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setOpen($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setHigh($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setLow($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setClose($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setVolume($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setDateCreation($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setDateModification($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setIdGroupCreation($arr[$keys[11]]);
        if (array_key_exists($keys[12], $arr)) $this->setIdCreation($arr[$keys[12]]);
        if (array_key_exists($keys[13], $arr)) $this->setIdModification($arr[$keys[13]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(MarketCandlePeer::DATABASE_NAME);

        if ($this->isColumnModified(MarketCandlePeer::ID_MARKET_CANDLE)) $criteria->add(MarketCandlePeer::ID_MARKET_CANDLE, $this->id_market_candle);
        if ($this->isColumnModified(MarketCandlePeer::SYMBOL)) $criteria->add(MarketCandlePeer::SYMBOL, $this->symbol);
        if ($this->isColumnModified(MarketCandlePeer::TF)) $criteria->add(MarketCandlePeer::TF, $this->tf);
        if ($this->isColumnModified(MarketCandlePeer::OPEN_TIME)) $criteria->add(MarketCandlePeer::OPEN_TIME, $this->open_time);
        if ($this->isColumnModified(MarketCandlePeer::OPEN)) $criteria->add(MarketCandlePeer::OPEN, $this->open);
        if ($this->isColumnModified(MarketCandlePeer::HIGH)) $criteria->add(MarketCandlePeer::HIGH, $this->high);
        if ($this->isColumnModified(MarketCandlePeer::LOW)) $criteria->add(MarketCandlePeer::LOW, $this->low);
        if ($this->isColumnModified(MarketCandlePeer::CLOSE)) $criteria->add(MarketCandlePeer::CLOSE, $this->close);
        if ($this->isColumnModified(MarketCandlePeer::VOLUME)) $criteria->add(MarketCandlePeer::VOLUME, $this->volume);
        if ($this->isColumnModified(MarketCandlePeer::DATE_CREATION)) $criteria->add(MarketCandlePeer::DATE_CREATION, $this->date_creation);
        if ($this->isColumnModified(MarketCandlePeer::DATE_MODIFICATION)) $criteria->add(MarketCandlePeer::DATE_MODIFICATION, $this->date_modification);
        if ($this->isColumnModified(MarketCandlePeer::ID_GROUP_CREATION)) $criteria->add(MarketCandlePeer::ID_GROUP_CREATION, $this->id_group_creation);
        if ($this->isColumnModified(MarketCandlePeer::ID_CREATION)) $criteria->add(MarketCandlePeer::ID_CREATION, $this->id_creation);
        if ($this->isColumnModified(MarketCandlePeer::ID_MODIFICATION)) $criteria->add(MarketCandlePeer::ID_MODIFICATION, $this->id_modification);

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
        $criteria = new Criteria(MarketCandlePeer::DATABASE_NAME);
        $criteria->add(MarketCandlePeer::ID_MARKET_CANDLE, $this->id_market_candle);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getIdMarketCandle();
    }

    /**
     * Generic method to set the primary key (id_market_candle column).
     *
     * @param  int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setIdMarketCandle($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getIdMarketCandle();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of MarketCandle (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setSymbol($this->getSymbol());
        $copyObj->setTf($this->getTf());
        $copyObj->setOpenTime($this->getOpenTime());
        $copyObj->setOpen($this->getOpen());
        $copyObj->setHigh($this->getHigh());
        $copyObj->setLow($this->getLow());
        $copyObj->setClose($this->getClose());
        $copyObj->setVolume($this->getVolume());
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
            $copyObj->setIdMarketCandle(NULL); // this is a auto-increment column, so set to default value
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
     * @return MarketCandle Clone of current object.
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
     * @return MarketCandlePeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new MarketCandlePeer();
        }

        return self::$peer;
    }

    /**
     * Declares an association between this object and a AuthyGroup object.
     *
     * @param                  AuthyGroup $v
     * @return MarketCandle The current object (for fluent API support)
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
            $v->addMarketCandle($this);
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
                $this->aAuthyGroup->addMarketCandles($this);
             */
        }

        return $this->aAuthyGroup;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return MarketCandle The current object (for fluent API support)
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
            $v->addMarketCandleRelatedByIdCreation($this);
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
                $this->aAuthyRelatedByIdCreation->addMarketCandlesRelatedByIdCreation($this);
             */
        }

        return $this->aAuthyRelatedByIdCreation;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return MarketCandle The current object (for fluent API support)
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
            $v->addMarketCandleRelatedByIdModification($this);
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
                $this->aAuthyRelatedByIdModification->addMarketCandlesRelatedByIdModification($this);
             */
        }

        return $this->aAuthyRelatedByIdModification;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id_market_candle = null;
        $this->symbol = null;
        $this->tf = null;
        $this->open_time = null;
        $this->open = null;
        $this->high = null;
        $this->low = null;
        $this->close = null;
        $this->volume = null;
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
        return (string) $this->exportTo(MarketCandlePeer::DEFAULT_STRING_FORMAT);
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
     * @return     MarketCandle The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged(){
        $this->modifiedColumns[] = MarketCandlePeer::DATE_MODIFICATION;

        return $this;
    }

}
