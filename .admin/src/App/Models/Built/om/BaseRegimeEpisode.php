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
use App\FleetSlot;
use App\FleetSlotQuery;
use App\GridRun;
use App\GridRunQuery;
use App\RegimeEpisode;
use App\RegimeEpisodePeer;
use App\RegimeEpisodeQuery;

/**
 * Base class that represents a row from the 'regime_episode' table.
 *
 * Regime episode
 *
 * @package    propel.generator..om
 */
abstract class BaseRegimeEpisode extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'App\\RegimeEpisodePeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        RegimeEpisodePeer
     */
    protected static $peer;

    /**
     * The flag var to prevent infinite loop in deep copy
     * @var       boolean
     */
    protected $startCopy = false;

    /**
     * The value for the id_regime_episode field.
     * @var        int
     */
    protected $id_regime_episode;

    /**
     * The value for the symbol field.
     * @var        string
     */
    protected $symbol;

    /**
     * The value for the algo field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $algo;

    /**
     * The value for the id_fleet_slot field.
     * @var        int
     */
    protected $id_fleet_slot;

    /**
     * The value for the id_grid_run field.
     * @var        int
     */
    protected $id_grid_run;

    /**
     * The value for the verdict field.
     * @var        string
     */
    protected $verdict;

    /**
     * The value for the opened_at field.
     * @var        string
     */
    protected $opened_at;

    /**
     * The value for the closed_at field.
     * @var        string
     */
    protected $closed_at;

    /**
     * The value for the price_open field.
     * @var        string
     */
    protected $price_open;

    /**
     * The value for the price_close field.
     * @var        string
     */
    protected $price_close;

    /**
     * The value for the engaged_pct_tw field.
     * @var        string
     */
    protected $engaged_pct_tw;

    /**
     * The value for the samples field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $samples;

    /**
     * The value for the realized field.
     * Note: this column has a database default value of: '0'
     * @var        string
     */
    protected $realized;

    /**
     * The value for the mtm_close field.
     * @var        string
     */
    protected $mtm_close;

    /**
     * The value for the hodl_pct field.
     * @var        string
     */
    protected $hodl_pct;

    /**
     * The value for the captured_pct field.
     * @var        string
     */
    protected $captured_pct;

    /**
     * The value for the idle_samples field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $idle_samples;

    /**
     * The value for the idle_alerted_at field.
     * @var        string
     */
    protected $idle_alerted_at;

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
     * @var        FleetSlot
     */
    protected $aFleetSlot;

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
        $this->algo = 0;
        $this->samples = 0;
        $this->realized = '0';
        $this->idle_samples = 0;
    }

    /**
     * Initializes internal state of BaseRegimeEpisode object.
     * @see        applyDefaults()
     */
    public function __construct()
    {
        parent::__construct();
        $this->applyDefaultValues();
    }

    /**
     * @Field()
     * Get the [id_regime_episode] column value.
     *
     * @return int
     */
    public function getIdRegimeEpisode()
    {

        return $this->id_regime_episode;
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
        $valueSet = RegimeEpisodePeer::getValueSet(RegimeEpisodePeer::ALGO);
        if (!isset($valueSet[$this->algo])) {
            throw new PropelException('Unknown stored enum key: ' . $this->algo);
        }

        return $valueSet[$this->algo];
    }

    /**
     * @Field()
     * Get the [id_fleet_slot] column value.
     * Slot
     * @return int
     */
    public function getIdFleetSlot()
    {

        return $this->id_fleet_slot;
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
     * Get the [verdict] column value.
     * Verdict
     * @return string
     */
    public function getVerdict()
    {

        return $this->verdict;
    }

    /**
     * @Field()
     * Get the [optionally formatted] temporal [opened_at] column value.
     * Opened at
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw DateTime object will be returned.
     * @return mixed Formatted date/time value as string or DateTime object (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getOpenedAt($format = 'Y-m-d H:i:s')
    {
        if ($this->opened_at === null) {
            return null;
        }

        if ($this->opened_at === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->opened_at);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->opened_at, true), $x);
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
     * Get the [optionally formatted] temporal [closed_at] column value.
     * Closed at
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw DateTime object will be returned.
     * @return mixed Formatted date/time value as string or DateTime object (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getClosedAt($format = 'Y-m-d H:i:s')
    {
        if ($this->closed_at === null) {
            return null;
        }

        if ($this->closed_at === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->closed_at);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->closed_at, true), $x);
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
     * Get the [price_open] column value.
     * Price at open
     * @return string
     */
    public function getPriceOpen()
    {

        return $this->price_open;
    }

    /**
     * @Field()
     * Get the [price_close] column value.
     * Price at close
     * @return string
     */
    public function getPriceClose()
    {

        return $this->price_close;
    }

    /**
     * @Field()
     * Get the [engaged_pct_tw] column value.
     * Engaged % (time-weighted)
     * @return string
     */
    public function getEngagedPctTw()
    {

        return $this->engaged_pct_tw;
    }

    /**
     * @Field()
     * Get the [samples] column value.
     * Samples
     * @return int
     */
    public function getSamples()
    {

        return $this->samples;
    }

    /**
     * @Field()
     * Get the [realized] column value.
     * Realized (USDT)
     * @return string
     */
    public function getRealized()
    {

        return $this->realized;
    }

    /**
     * @Field()
     * Get the [mtm_close] column value.
     * Mark-to-market at close
     * @return string
     */
    public function getMtmClose()
    {

        return $this->mtm_close;
    }

    /**
     * @Field()
     * Get the [hodl_pct] column value.
     * HODL move %
     * @return string
     */
    public function getHodlPct()
    {

        return $this->hodl_pct;
    }

    /**
     * @Field()
     * Get the [captured_pct] column value.
     * Captured % of HODL
     * @return string
     */
    public function getCapturedPct()
    {

        return $this->captured_pct;
    }

    /**
     * @Field()
     * Get the [idle_samples] column value.
     * Consecutive sub-floor samples
     * @return int
     */
    public function getIdleSamples()
    {

        return $this->idle_samples;
    }

    /**
     * @Field()
     * Get the [optionally formatted] temporal [idle_alerted_at] column value.
     * Idle alerted at
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw DateTime object will be returned.
     * @return mixed Formatted date/time value as string or DateTime object (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getIdleAlertedAt($format = 'Y-m-d H:i:s')
    {
        if ($this->idle_alerted_at === null) {
            return null;
        }

        if ($this->idle_alerted_at === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->idle_alerted_at);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->idle_alerted_at, true), $x);
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
     * Set the value of [id_regime_episode] column.
     *
     * @param  int $v new value
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setIdRegimeEpisode($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_regime_episode !== $v) {
            $this->id_regime_episode = $v;
            $this->modifiedColumns[] = RegimeEpisodePeer::ID_REGIME_EPISODE;
        }


        return $this;
    } // setIdRegimeEpisode()

    /**
     * Set the value of [symbol] column.
     * Symbol
     * @param  string $v new value
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setSymbol($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->symbol !== $v) {
            $this->symbol = $v;
            $this->modifiedColumns[] = RegimeEpisodePeer::SYMBOL;
        }


        return $this;
    } // setSymbol()

    /**
     * Set the value of [algo] column.
     * Algorithm
     * @param  int $v new value
     * @return RegimeEpisode The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setAlgo($v)
    {
        if ($v !== null) {
            $valueSet = RegimeEpisodePeer::getValueSet(RegimeEpisodePeer::ALGO);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->algo !== $v) {
            $this->algo = $v;
            $this->modifiedColumns[] = RegimeEpisodePeer::ALGO;
        }


        return $this;
    } // setAlgo()

    /**
     * Set the value of [id_fleet_slot] column.
     * Slot
     * @param  int $v new value
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setIdFleetSlot($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_fleet_slot !== $v) {
            $this->id_fleet_slot = $v;
            $this->modifiedColumns[] = RegimeEpisodePeer::ID_FLEET_SLOT;
        }

        if ($this->aFleetSlot !== null && $this->aFleetSlot->getIdFleetSlot() !== $v) {
            $this->aFleetSlot = null;
        }


        return $this;
    } // setIdFleetSlot()

    /**
     * Set the value of [id_grid_run] column.
     * Run
     * @param  int $v new value
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setIdGridRun($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_grid_run !== $v) {
            $this->id_grid_run = $v;
            $this->modifiedColumns[] = RegimeEpisodePeer::ID_GRID_RUN;
        }

        if ($this->aGridRun !== null && $this->aGridRun->getIdGridRun() !== $v) {
            $this->aGridRun = null;
        }


        return $this;
    } // setIdGridRun()

    /**
     * Set the value of [verdict] column.
     * Verdict
     * @param  string $v new value
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setVerdict($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->verdict !== $v) {
            $this->verdict = $v;
            $this->modifiedColumns[] = RegimeEpisodePeer::VERDICT;
        }


        return $this;
    } // setVerdict()

    /**
     * Sets the value of [opened_at] column to a normalized version of the date/time value specified.
     * Opened at
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setOpenedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->opened_at !== null || $dt !== null) {
            $currentDateAsString = ($this->opened_at !== null && $tmpDt = new DateTime($this->opened_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->opened_at = $newDateAsString;
                $this->modifiedColumns[] = RegimeEpisodePeer::OPENED_AT;
            }
        } // if either are not null


        return $this;
    } // setOpenedAt()

    /**
     * Sets the value of [closed_at] column to a normalized version of the date/time value specified.
     * Closed at
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setClosedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->closed_at !== null || $dt !== null) {
            $currentDateAsString = ($this->closed_at !== null && $tmpDt = new DateTime($this->closed_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->closed_at = $newDateAsString;
                $this->modifiedColumns[] = RegimeEpisodePeer::CLOSED_AT;
            }
        } // if either are not null


        return $this;
    } // setClosedAt()

    /**
     * Set the value of [price_open] column.
     * Price at open
     * @param  string $v new value
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setPriceOpen($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->price_open !== $v) {
            $this->price_open = $v;
            $this->modifiedColumns[] = RegimeEpisodePeer::PRICE_OPEN;
        }


        return $this;
    } // setPriceOpen()

    /**
     * Set the value of [price_close] column.
     * Price at close
     * @param  string $v new value
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setPriceClose($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->price_close !== $v) {
            $this->price_close = $v;
            $this->modifiedColumns[] = RegimeEpisodePeer::PRICE_CLOSE;
        }


        return $this;
    } // setPriceClose()

    /**
     * Set the value of [engaged_pct_tw] column.
     * Engaged % (time-weighted)
     * @param  string $v new value
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setEngagedPctTw($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->engaged_pct_tw !== $v) {
            $this->engaged_pct_tw = $v;
            $this->modifiedColumns[] = RegimeEpisodePeer::ENGAGED_PCT_TW;
        }


        return $this;
    } // setEngagedPctTw()

    /**
     * Set the value of [samples] column.
     * Samples
     * @param  int $v new value
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setSamples($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->samples !== $v) {
            $this->samples = $v;
            $this->modifiedColumns[] = RegimeEpisodePeer::SAMPLES;
        }


        return $this;
    } // setSamples()

    /**
     * Set the value of [realized] column.
     * Realized (USDT)
     * @param  string $v new value
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setRealized($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->realized !== $v) {
            $this->realized = $v;
            $this->modifiedColumns[] = RegimeEpisodePeer::REALIZED;
        }


        return $this;
    } // setRealized()

    /**
     * Set the value of [mtm_close] column.
     * Mark-to-market at close
     * @param  string $v new value
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setMtmClose($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->mtm_close !== $v) {
            $this->mtm_close = $v;
            $this->modifiedColumns[] = RegimeEpisodePeer::MTM_CLOSE;
        }


        return $this;
    } // setMtmClose()

    /**
     * Set the value of [hodl_pct] column.
     * HODL move %
     * @param  string $v new value
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setHodlPct($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->hodl_pct !== $v) {
            $this->hodl_pct = $v;
            $this->modifiedColumns[] = RegimeEpisodePeer::HODL_PCT;
        }


        return $this;
    } // setHodlPct()

    /**
     * Set the value of [captured_pct] column.
     * Captured % of HODL
     * @param  string $v new value
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setCapturedPct($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->captured_pct !== $v) {
            $this->captured_pct = $v;
            $this->modifiedColumns[] = RegimeEpisodePeer::CAPTURED_PCT;
        }


        return $this;
    } // setCapturedPct()

    /**
     * Set the value of [idle_samples] column.
     * Consecutive sub-floor samples
     * @param  int $v new value
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setIdleSamples($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->idle_samples !== $v) {
            $this->idle_samples = $v;
            $this->modifiedColumns[] = RegimeEpisodePeer::IDLE_SAMPLES;
        }


        return $this;
    } // setIdleSamples()

    /**
     * Sets the value of [idle_alerted_at] column to a normalized version of the date/time value specified.
     * Idle alerted at
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setIdleAlertedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->idle_alerted_at !== null || $dt !== null) {
            $currentDateAsString = ($this->idle_alerted_at !== null && $tmpDt = new DateTime($this->idle_alerted_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->idle_alerted_at = $newDateAsString;
                $this->modifiedColumns[] = RegimeEpisodePeer::IDLE_ALERTED_AT;
            }
        } // if either are not null


        return $this;
    } // setIdleAlertedAt()

    /**
     * Sets the value of [date_creation] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setDateCreation($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_creation !== null || $dt !== null) {
            $currentDateAsString = ($this->date_creation !== null && $tmpDt = new DateTime($this->date_creation)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_creation = $newDateAsString;
                $this->modifiedColumns[] = RegimeEpisodePeer::DATE_CREATION;
            }
        } // if either are not null


        return $this;
    } // setDateCreation()

    /**
     * Sets the value of [date_modification] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setDateModification($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_modification !== null || $dt !== null) {
            $currentDateAsString = ($this->date_modification !== null && $tmpDt = new DateTime($this->date_modification)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_modification = $newDateAsString;
                $this->modifiedColumns[] = RegimeEpisodePeer::DATE_MODIFICATION;
            }
        } // if either are not null


        return $this;
    } // setDateModification()

    /**
     * Set the value of [id_group_creation] column.
     *
     * @param  int $v new value
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setIdGroupCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_group_creation !== $v) {
            $this->id_group_creation = $v;
            $this->modifiedColumns[] = RegimeEpisodePeer::ID_GROUP_CREATION;
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
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setIdCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_creation !== $v) {
            $this->id_creation = $v;
            $this->modifiedColumns[] = RegimeEpisodePeer::ID_CREATION;
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
     * @return RegimeEpisode The current object (for fluent API support)
     */
    public function setIdModification($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_modification !== $v) {
            $this->id_modification = $v;
            $this->modifiedColumns[] = RegimeEpisodePeer::ID_MODIFICATION;
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
            if ($this->algo !== 0) {
                return false;
            }

            if ($this->samples !== 0) {
                return false;
            }

            if ($this->realized !== '0') {
                return false;
            }

            if ($this->idle_samples !== 0) {
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

            $this->id_regime_episode = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
            $this->symbol = ($row[$startcol + 1] !== null) ? (string) $row[$startcol + 1] : null;
            $this->algo = ($row[$startcol + 2] !== null) ? (int) $row[$startcol + 2] : null;
            $this->id_fleet_slot = ($row[$startcol + 3] !== null) ? (int) $row[$startcol + 3] : null;
            $this->id_grid_run = ($row[$startcol + 4] !== null) ? (int) $row[$startcol + 4] : null;
            $this->verdict = ($row[$startcol + 5] !== null) ? (string) $row[$startcol + 5] : null;
            $this->opened_at = ($row[$startcol + 6] !== null) ? (string) $row[$startcol + 6] : null;
            $this->closed_at = ($row[$startcol + 7] !== null) ? (string) $row[$startcol + 7] : null;
            $this->price_open = ($row[$startcol + 8] !== null) ? (string) $row[$startcol + 8] : null;
            $this->price_close = ($row[$startcol + 9] !== null) ? (string) $row[$startcol + 9] : null;
            $this->engaged_pct_tw = ($row[$startcol + 10] !== null) ? (string) $row[$startcol + 10] : null;
            $this->samples = ($row[$startcol + 11] !== null) ? (int) $row[$startcol + 11] : null;
            $this->realized = ($row[$startcol + 12] !== null) ? (string) $row[$startcol + 12] : null;
            $this->mtm_close = ($row[$startcol + 13] !== null) ? (string) $row[$startcol + 13] : null;
            $this->hodl_pct = ($row[$startcol + 14] !== null) ? (string) $row[$startcol + 14] : null;
            $this->captured_pct = ($row[$startcol + 15] !== null) ? (string) $row[$startcol + 15] : null;
            $this->idle_samples = ($row[$startcol + 16] !== null) ? (int) $row[$startcol + 16] : null;
            $this->idle_alerted_at = ($row[$startcol + 17] !== null) ? (string) $row[$startcol + 17] : null;
            $this->date_creation = ($row[$startcol + 18] !== null) ? (string) $row[$startcol + 18] : null;
            $this->date_modification = ($row[$startcol + 19] !== null) ? (string) $row[$startcol + 19] : null;
            $this->id_group_creation = ($row[$startcol + 20] !== null) ? (int) $row[$startcol + 20] : null;
            $this->id_creation = ($row[$startcol + 21] !== null) ? (int) $row[$startcol + 21] : null;
            $this->id_modification = ($row[$startcol + 22] !== null) ? (int) $row[$startcol + 22] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);

            return $startcol + 23; // 23 = RegimeEpisodePeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating RegimeEpisode object", $e);
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

        if ($this->aFleetSlot !== null && $this->id_fleet_slot !== $this->aFleetSlot->getIdFleetSlot()) {
            $this->aFleetSlot = null;
        }
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
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = RegimeEpisodePeer::doSelectStmt($this->buildPkeyCriteria(), $con);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aFleetSlot = null;
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
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = RegimeEpisodeQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                // GoatCheese behavior

                            if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                                \ApiGoat\Utility\TableVersion::bump('regime_episode');
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
            $con = Propel::getConnection(RegimeEpisodePeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
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
                                \ApiGoat\Utility\TableVersion::bump('regime_episode');
                            }
                RegimeEpisodePeer::addInstanceToPool($this);
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

            if ($this->aFleetSlot !== null) {
                if ($this->aFleetSlot->isModified() || $this->aFleetSlot->isNew()) {
                    $affectedRows += $this->aFleetSlot->save($con);
                }
                $this->setFleetSlot($this->aFleetSlot);
            }

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

        $this->modifiedColumns[] = RegimeEpisodePeer::ID_REGIME_EPISODE;
        if (null !== $this->id_regime_episode) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . RegimeEpisodePeer::ID_REGIME_EPISODE . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(RegimeEpisodePeer::ID_REGIME_EPISODE)) {
            $modifiedColumns[':p' . $index++]  = '`id_regime_episode`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::SYMBOL)) {
            $modifiedColumns[':p' . $index++]  = '`symbol`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::ALGO)) {
            $modifiedColumns[':p' . $index++]  = '`algo`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::ID_FLEET_SLOT)) {
            $modifiedColumns[':p' . $index++]  = '`id_fleet_slot`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::ID_GRID_RUN)) {
            $modifiedColumns[':p' . $index++]  = '`id_grid_run`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::VERDICT)) {
            $modifiedColumns[':p' . $index++]  = '`verdict`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::OPENED_AT)) {
            $modifiedColumns[':p' . $index++]  = '`opened_at`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::CLOSED_AT)) {
            $modifiedColumns[':p' . $index++]  = '`closed_at`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::PRICE_OPEN)) {
            $modifiedColumns[':p' . $index++]  = '`price_open`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::PRICE_CLOSE)) {
            $modifiedColumns[':p' . $index++]  = '`price_close`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::ENGAGED_PCT_TW)) {
            $modifiedColumns[':p' . $index++]  = '`engaged_pct_tw`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::SAMPLES)) {
            $modifiedColumns[':p' . $index++]  = '`samples`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::REALIZED)) {
            $modifiedColumns[':p' . $index++]  = '`realized`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::MTM_CLOSE)) {
            $modifiedColumns[':p' . $index++]  = '`mtm_close`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::HODL_PCT)) {
            $modifiedColumns[':p' . $index++]  = '`hodl_pct`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::CAPTURED_PCT)) {
            $modifiedColumns[':p' . $index++]  = '`captured_pct`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::IDLE_SAMPLES)) {
            $modifiedColumns[':p' . $index++]  = '`idle_samples`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::IDLE_ALERTED_AT)) {
            $modifiedColumns[':p' . $index++]  = '`idle_alerted_at`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::DATE_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_creation`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::DATE_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_modification`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::ID_GROUP_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_group_creation`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::ID_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_creation`';
        }
        if ($this->isColumnModified(RegimeEpisodePeer::ID_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_modification`';
        }

        $sql = sprintf(
            'INSERT INTO `regime_episode` (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case '`id_regime_episode`':
                        $stmt->bindValue($identifier, $this->id_regime_episode, PDO::PARAM_INT);
                        break;
                    case '`symbol`':
                        $stmt->bindValue($identifier, $this->symbol, PDO::PARAM_STR);
                        break;
                    case '`algo`':
                        $stmt->bindValue($identifier, $this->algo, PDO::PARAM_INT);
                        break;
                    case '`id_fleet_slot`':
                        $stmt->bindValue($identifier, $this->id_fleet_slot, PDO::PARAM_INT);
                        break;
                    case '`id_grid_run`':
                        $stmt->bindValue($identifier, $this->id_grid_run, PDO::PARAM_INT);
                        break;
                    case '`verdict`':
                        $stmt->bindValue($identifier, $this->verdict, PDO::PARAM_STR);
                        break;
                    case '`opened_at`':
                        $stmt->bindValue($identifier, $this->opened_at, PDO::PARAM_STR);
                        break;
                    case '`closed_at`':
                        $stmt->bindValue($identifier, $this->closed_at, PDO::PARAM_STR);
                        break;
                    case '`price_open`':
                        $stmt->bindValue($identifier, $this->price_open, PDO::PARAM_STR);
                        break;
                    case '`price_close`':
                        $stmt->bindValue($identifier, $this->price_close, PDO::PARAM_STR);
                        break;
                    case '`engaged_pct_tw`':
                        $stmt->bindValue($identifier, $this->engaged_pct_tw, PDO::PARAM_STR);
                        break;
                    case '`samples`':
                        $stmt->bindValue($identifier, $this->samples, PDO::PARAM_INT);
                        break;
                    case '`realized`':
                        $stmt->bindValue($identifier, $this->realized, PDO::PARAM_STR);
                        break;
                    case '`mtm_close`':
                        $stmt->bindValue($identifier, $this->mtm_close, PDO::PARAM_STR);
                        break;
                    case '`hodl_pct`':
                        $stmt->bindValue($identifier, $this->hodl_pct, PDO::PARAM_STR);
                        break;
                    case '`captured_pct`':
                        $stmt->bindValue($identifier, $this->captured_pct, PDO::PARAM_STR);
                        break;
                    case '`idle_samples`':
                        $stmt->bindValue($identifier, $this->idle_samples, PDO::PARAM_INT);
                        break;
                    case '`idle_alerted_at`':
                        $stmt->bindValue($identifier, $this->idle_alerted_at, PDO::PARAM_STR);
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
        $this->setIdRegimeEpisode($pk);

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

            if ($this->aFleetSlot !== null) {
                if (!$this->aFleetSlot->validate($columns)) {
                    $failureMap = array_merge($failureMap, $this->aFleetSlot->getValidationFailures());
                }
            }

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


            if (($retval = RegimeEpisodePeer::doValidate($this, $columns)) !== true) {
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
        $pos = RegimeEpisodePeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
        if (isset($alreadyDumpedObjects['RegimeEpisode'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['RegimeEpisode'][$this->getPrimaryKey()] = true;
        $keys = RegimeEpisodePeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getIdRegimeEpisode(),
            $keys[1] => $this->getSymbol(),
            $keys[2] => $this->getAlgo(),
            $keys[3] => $this->getIdFleetSlot(),
            $keys[4] => $this->getIdGridRun(),
            $keys[5] => $this->getVerdict(),
            $keys[6] => $this->getOpenedAt(),
            $keys[7] => $this->getClosedAt(),
            $keys[8] => $this->getPriceOpen(),
            $keys[9] => $this->getPriceClose(),
            $keys[10] => $this->getEngagedPctTw(),
            $keys[11] => $this->getSamples(),
            $keys[12] => $this->getRealized(),
            $keys[13] => $this->getMtmClose(),
            $keys[14] => $this->getHodlPct(),
            $keys[15] => $this->getCapturedPct(),
            $keys[16] => $this->getIdleSamples(),
            $keys[17] => $this->getIdleAlertedAt(),
            $keys[18] => $this->getDateCreation(),
            $keys[19] => $this->getDateModification(),
            $keys[20] => $this->getIdGroupCreation(),
            $keys[21] => $this->getIdCreation(),
            $keys[22] => $this->getIdModification(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aFleetSlot) {
                $result['FleetSlot'] = $this->aFleetSlot->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
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
        $pos = RegimeEpisodePeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

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
                $this->setIdRegimeEpisode($value);
                break;
            case 1:
                $this->setSymbol($value);
                break;
            case 2:
                $valueSet = RegimeEpisodePeer::getValueSet(RegimeEpisodePeer::ALGO);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setAlgo($value);
                break;
            case 3:
                $this->setIdFleetSlot($value);
                break;
            case 4:
                $this->setIdGridRun($value);
                break;
            case 5:
                $this->setVerdict($value);
                break;
            case 6:
                $this->setOpenedAt($value);
                break;
            case 7:
                $this->setClosedAt($value);
                break;
            case 8:
                $this->setPriceOpen($value);
                break;
            case 9:
                $this->setPriceClose($value);
                break;
            case 10:
                $this->setEngagedPctTw($value);
                break;
            case 11:
                $this->setSamples($value);
                break;
            case 12:
                $this->setRealized($value);
                break;
            case 13:
                $this->setMtmClose($value);
                break;
            case 14:
                $this->setHodlPct($value);
                break;
            case 15:
                $this->setCapturedPct($value);
                break;
            case 16:
                $this->setIdleSamples($value);
                break;
            case 17:
                $this->setIdleAlertedAt($value);
                break;
            case 18:
                $this->setDateCreation($value);
                break;
            case 19:
                $this->setDateModification($value);
                break;
            case 20:
                $this->setIdGroupCreation($value);
                break;
            case 21:
                $this->setIdCreation($value);
                break;
            case 22:
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
        $keys = RegimeEpisodePeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setIdRegimeEpisode($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setSymbol($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setAlgo($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setIdFleetSlot($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setIdGridRun($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setVerdict($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setOpenedAt($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setClosedAt($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setPriceOpen($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setPriceClose($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setEngagedPctTw($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setSamples($arr[$keys[11]]);
        if (array_key_exists($keys[12], $arr)) $this->setRealized($arr[$keys[12]]);
        if (array_key_exists($keys[13], $arr)) $this->setMtmClose($arr[$keys[13]]);
        if (array_key_exists($keys[14], $arr)) $this->setHodlPct($arr[$keys[14]]);
        if (array_key_exists($keys[15], $arr)) $this->setCapturedPct($arr[$keys[15]]);
        if (array_key_exists($keys[16], $arr)) $this->setIdleSamples($arr[$keys[16]]);
        if (array_key_exists($keys[17], $arr)) $this->setIdleAlertedAt($arr[$keys[17]]);
        if (array_key_exists($keys[18], $arr)) $this->setDateCreation($arr[$keys[18]]);
        if (array_key_exists($keys[19], $arr)) $this->setDateModification($arr[$keys[19]]);
        if (array_key_exists($keys[20], $arr)) $this->setIdGroupCreation($arr[$keys[20]]);
        if (array_key_exists($keys[21], $arr)) $this->setIdCreation($arr[$keys[21]]);
        if (array_key_exists($keys[22], $arr)) $this->setIdModification($arr[$keys[22]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(RegimeEpisodePeer::DATABASE_NAME);

        if ($this->isColumnModified(RegimeEpisodePeer::ID_REGIME_EPISODE)) $criteria->add(RegimeEpisodePeer::ID_REGIME_EPISODE, $this->id_regime_episode);
        if ($this->isColumnModified(RegimeEpisodePeer::SYMBOL)) $criteria->add(RegimeEpisodePeer::SYMBOL, $this->symbol);
        if ($this->isColumnModified(RegimeEpisodePeer::ALGO)) $criteria->add(RegimeEpisodePeer::ALGO, $this->algo);
        if ($this->isColumnModified(RegimeEpisodePeer::ID_FLEET_SLOT)) $criteria->add(RegimeEpisodePeer::ID_FLEET_SLOT, $this->id_fleet_slot);
        if ($this->isColumnModified(RegimeEpisodePeer::ID_GRID_RUN)) $criteria->add(RegimeEpisodePeer::ID_GRID_RUN, $this->id_grid_run);
        if ($this->isColumnModified(RegimeEpisodePeer::VERDICT)) $criteria->add(RegimeEpisodePeer::VERDICT, $this->verdict);
        if ($this->isColumnModified(RegimeEpisodePeer::OPENED_AT)) $criteria->add(RegimeEpisodePeer::OPENED_AT, $this->opened_at);
        if ($this->isColumnModified(RegimeEpisodePeer::CLOSED_AT)) $criteria->add(RegimeEpisodePeer::CLOSED_AT, $this->closed_at);
        if ($this->isColumnModified(RegimeEpisodePeer::PRICE_OPEN)) $criteria->add(RegimeEpisodePeer::PRICE_OPEN, $this->price_open);
        if ($this->isColumnModified(RegimeEpisodePeer::PRICE_CLOSE)) $criteria->add(RegimeEpisodePeer::PRICE_CLOSE, $this->price_close);
        if ($this->isColumnModified(RegimeEpisodePeer::ENGAGED_PCT_TW)) $criteria->add(RegimeEpisodePeer::ENGAGED_PCT_TW, $this->engaged_pct_tw);
        if ($this->isColumnModified(RegimeEpisodePeer::SAMPLES)) $criteria->add(RegimeEpisodePeer::SAMPLES, $this->samples);
        if ($this->isColumnModified(RegimeEpisodePeer::REALIZED)) $criteria->add(RegimeEpisodePeer::REALIZED, $this->realized);
        if ($this->isColumnModified(RegimeEpisodePeer::MTM_CLOSE)) $criteria->add(RegimeEpisodePeer::MTM_CLOSE, $this->mtm_close);
        if ($this->isColumnModified(RegimeEpisodePeer::HODL_PCT)) $criteria->add(RegimeEpisodePeer::HODL_PCT, $this->hodl_pct);
        if ($this->isColumnModified(RegimeEpisodePeer::CAPTURED_PCT)) $criteria->add(RegimeEpisodePeer::CAPTURED_PCT, $this->captured_pct);
        if ($this->isColumnModified(RegimeEpisodePeer::IDLE_SAMPLES)) $criteria->add(RegimeEpisodePeer::IDLE_SAMPLES, $this->idle_samples);
        if ($this->isColumnModified(RegimeEpisodePeer::IDLE_ALERTED_AT)) $criteria->add(RegimeEpisodePeer::IDLE_ALERTED_AT, $this->idle_alerted_at);
        if ($this->isColumnModified(RegimeEpisodePeer::DATE_CREATION)) $criteria->add(RegimeEpisodePeer::DATE_CREATION, $this->date_creation);
        if ($this->isColumnModified(RegimeEpisodePeer::DATE_MODIFICATION)) $criteria->add(RegimeEpisodePeer::DATE_MODIFICATION, $this->date_modification);
        if ($this->isColumnModified(RegimeEpisodePeer::ID_GROUP_CREATION)) $criteria->add(RegimeEpisodePeer::ID_GROUP_CREATION, $this->id_group_creation);
        if ($this->isColumnModified(RegimeEpisodePeer::ID_CREATION)) $criteria->add(RegimeEpisodePeer::ID_CREATION, $this->id_creation);
        if ($this->isColumnModified(RegimeEpisodePeer::ID_MODIFICATION)) $criteria->add(RegimeEpisodePeer::ID_MODIFICATION, $this->id_modification);

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
        $criteria = new Criteria(RegimeEpisodePeer::DATABASE_NAME);
        $criteria->add(RegimeEpisodePeer::ID_REGIME_EPISODE, $this->id_regime_episode);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getIdRegimeEpisode();
    }

    /**
     * Generic method to set the primary key (id_regime_episode column).
     *
     * @param  int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setIdRegimeEpisode($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getIdRegimeEpisode();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of RegimeEpisode (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setSymbol($this->getSymbol());
        $copyObj->setAlgo($this->getAlgo());
        $copyObj->setIdFleetSlot($this->getIdFleetSlot());
        $copyObj->setIdGridRun($this->getIdGridRun());
        $copyObj->setVerdict($this->getVerdict());
        $copyObj->setOpenedAt($this->getOpenedAt());
        $copyObj->setClosedAt($this->getClosedAt());
        $copyObj->setPriceOpen($this->getPriceOpen());
        $copyObj->setPriceClose($this->getPriceClose());
        $copyObj->setEngagedPctTw($this->getEngagedPctTw());
        $copyObj->setSamples($this->getSamples());
        $copyObj->setRealized($this->getRealized());
        $copyObj->setMtmClose($this->getMtmClose());
        $copyObj->setHodlPct($this->getHodlPct());
        $copyObj->setCapturedPct($this->getCapturedPct());
        $copyObj->setIdleSamples($this->getIdleSamples());
        $copyObj->setIdleAlertedAt($this->getIdleAlertedAt());
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
            $copyObj->setIdRegimeEpisode(NULL); // this is a auto-increment column, so set to default value
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
     * @return RegimeEpisode Clone of current object.
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
     * @return RegimeEpisodePeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new RegimeEpisodePeer();
        }

        return self::$peer;
    }

    /**
     * Declares an association between this object and a FleetSlot object.
     *
     * @param                  FleetSlot $v
     * @return RegimeEpisode The current object (for fluent API support)
     * @throws PropelException
     */
    public function setFleetSlot(?FleetSlot $v = null)
    {
        if ($v === null) {
            $this->setIdFleetSlot(NULL);
        } else {
            $this->setIdFleetSlot($v->getIdFleetSlot());
        }

        $this->aFleetSlot = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the FleetSlot object, it will not be re-added.
        if ($v !== null) {
            $v->addRegimeEpisode($this);
        }


        return $this;
    }


    /**
     * Get the associated FleetSlot object
     *
     * @param PropelPDO $con Optional Connection object.
     * @param $doQuery Executes a query to get the object if required
     * @return FleetSlot The associated FleetSlot object.
     * @throws PropelException
     */
    public function getFleetSlot(?PropelPDO $con = null, $doQuery = true)
    {
        if ($this->aFleetSlot === null && ($this->id_fleet_slot !== null) && $doQuery) {
            $this->aFleetSlot = FleetSlotQuery::create()->findPk($this->id_fleet_slot, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aFleetSlot->addRegimeEpisodes($this);
             */
        }

        return $this->aFleetSlot;
    }

    /**
     * Declares an association between this object and a GridRun object.
     *
     * @param                  GridRun $v
     * @return RegimeEpisode The current object (for fluent API support)
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
            $v->addRegimeEpisode($this);
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
                $this->aGridRun->addRegimeEpisodes($this);
             */
        }

        return $this->aGridRun;
    }

    /**
     * Declares an association between this object and a AuthyGroup object.
     *
     * @param                  AuthyGroup $v
     * @return RegimeEpisode The current object (for fluent API support)
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
            $v->addRegimeEpisode($this);
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
                $this->aAuthyGroup->addRegimeEpisodes($this);
             */
        }

        return $this->aAuthyGroup;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return RegimeEpisode The current object (for fluent API support)
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
            $v->addRegimeEpisodeRelatedByIdCreation($this);
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
                $this->aAuthyRelatedByIdCreation->addRegimeEpisodesRelatedByIdCreation($this);
             */
        }

        return $this->aAuthyRelatedByIdCreation;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return RegimeEpisode The current object (for fluent API support)
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
            $v->addRegimeEpisodeRelatedByIdModification($this);
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
                $this->aAuthyRelatedByIdModification->addRegimeEpisodesRelatedByIdModification($this);
             */
        }

        return $this->aAuthyRelatedByIdModification;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id_regime_episode = null;
        $this->symbol = null;
        $this->algo = null;
        $this->id_fleet_slot = null;
        $this->id_grid_run = null;
        $this->verdict = null;
        $this->opened_at = null;
        $this->closed_at = null;
        $this->price_open = null;
        $this->price_close = null;
        $this->engaged_pct_tw = null;
        $this->samples = null;
        $this->realized = null;
        $this->mtm_close = null;
        $this->hodl_pct = null;
        $this->captured_pct = null;
        $this->idle_samples = null;
        $this->idle_alerted_at = null;
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
            if ($this->aFleetSlot instanceof Persistent) {
              $this->aFleetSlot->clearAllReferences($deep);
            }
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

        $this->aFleetSlot = null;
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
        return (string) $this->exportTo(RegimeEpisodePeer::DEFAULT_STRING_FORMAT);
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
     * @return     RegimeEpisode The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged(){
        $this->modifiedColumns[] = RegimeEpisodePeer::DATE_MODIFICATION;

        return $this;
    }

}
