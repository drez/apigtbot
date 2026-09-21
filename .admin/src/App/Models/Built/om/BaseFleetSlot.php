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
use App\FleetSlot;
use App\FleetSlotPeer;
use App\FleetSlotQuery;
use App\GridRun;
use App\GridRunQuery;
use App\RegimeEpisode;
use App\RegimeEpisodeQuery;

/**
 * Base class that represents a row from the 'fleet_slot' table.
 *
 * Fleet slot
 *
 * @package    propel.generator..om
 */
abstract class BaseFleetSlot extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'App\\FleetSlotPeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        FleetSlotPeer
     */
    protected static $peer;

    /**
     * The flag var to prevent infinite loop in deep copy
     * @var       boolean
     */
    protected $startCopy = false;

    /**
     * The value for the id_fleet_slot field.
     * @var        int
     */
    protected $id_fleet_slot;

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
     * The value for the target_slice field.
     * Note: this column has a database default value of: '350'
     * @var        string
     */
    protected $target_slice;

    /**
     * The value for the enabled field.
     * Note: this column has a database default value of: true
     * @var        boolean
     */
    protected $enabled;

    /**
     * The value for the state field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $state;

    /**
     * The value for the confirm_up field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $confirm_up;

    /**
     * The value for the confirm_down field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $confirm_down;

    /**
     * The value for the last_verdict field.
     * @var        string
     */
    protected $last_verdict;

    /**
     * The value for the verdict_at field.
     * @var        string
     */
    protected $verdict_at;

    /**
     * The value for the episode_started_at field.
     * @var        string
     */
    protected $episode_started_at;

    /**
     * The value for the activation field.
     * @var        string
     */
    protected $activation;

    /**
     * The value for the id_grid_run field.
     * @var        int
     */
    protected $id_grid_run;

    /**
     * The value for the last_empty_alert_at field.
     * @var        string
     */
    protected $last_empty_alert_at;

    /**
     * The value for the last_parked_alert_at field.
     * @var        string
     */
    protected $last_parked_alert_at;

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
     * @var        PropelObjectCollection|RegimeEpisode[] Collection to store aggregation of RegimeEpisode objects.
     */
    protected $collRegimeEpisodes;
    protected $collRegimeEpisodesPartial;

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
    protected $regimeEpisodesScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see        __construct()
     */
    public function applyDefaultValues()
    {
        $this->algo = 0;
        $this->target_slice = '350';
        $this->enabled = true;
        $this->state = 0;
        $this->confirm_up = 0;
        $this->confirm_down = 0;
    }

    /**
     * Initializes internal state of BaseFleetSlot object.
     * @see        applyDefaults()
     */
    public function __construct()
    {
        parent::__construct();
        $this->applyDefaultValues();
    }

    /**
     * @Field()
     * Get the [id_fleet_slot] column value.
     *
     * @return int
     */
    public function getIdFleetSlot()
    {

        return $this->id_fleet_slot;
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
        $valueSet = FleetSlotPeer::getValueSet(FleetSlotPeer::ALGO);
        if (!isset($valueSet[$this->algo])) {
            throw new PropelException('Unknown stored enum key: ' . $this->algo);
        }

        return $valueSet[$this->algo];
    }

    /**
     * @Field()
     * Get the [target_slice] column value.
     * Target slice (USDT)
     * @return string
     */
    public function getTargetSlice()
    {

        return $this->target_slice;
    }

    /**
     * @Field()
     * Get the [enabled] column value.
     * Enabled
     * @return boolean
     */
    public function getEnabled()
    {

        return $this->enabled;
    }

    /**
     * @Field()
     * Get the [state] column value.
     * Arm state
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getState()
    {
        if (null === $this->state) {
            return null;
        }
        $valueSet = FleetSlotPeer::getValueSet(FleetSlotPeer::STATE);
        if (!isset($valueSet[$this->state])) {
            throw new PropelException('Unknown stored enum key: ' . $this->state);
        }

        return $valueSet[$this->state];
    }

    /**
     * @Field()
     * Get the [confirm_up] column value.
     * Consecutive TREND_UP passes
     * @return int
     */
    public function getConfirmUp()
    {

        return $this->confirm_up;
    }

    /**
     * @Field()
     * Get the [confirm_down] column value.
     * Consecutive non-TREND_UP passes
     * @return int
     */
    public function getConfirmDown()
    {

        return $this->confirm_down;
    }

    /**
     * @Field()
     * Get the [last_verdict] column value.
     * Last verdict
     * @return string
     */
    public function getLastVerdict()
    {

        return $this->last_verdict;
    }

    /**
     * @Field()
     * Get the [optionally formatted] temporal [verdict_at] column value.
     * Verdict at
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw DateTime object will be returned.
     * @return mixed Formatted date/time value as string or DateTime object (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getVerdictAt($format = 'Y-m-d H:i:s')
    {
        if ($this->verdict_at === null) {
            return null;
        }

        if ($this->verdict_at === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->verdict_at);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->verdict_at, true), $x);
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
     * Get the [optionally formatted] temporal [episode_started_at] column value.
     * TREND_UP episode started
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw DateTime object will be returned.
     * @return mixed Formatted date/time value as string or DateTime object (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getEpisodeStartedAt($format = 'Y-m-d H:i:s')
    {
        if ($this->episode_started_at === null) {
            return null;
        }

        if ($this->episode_started_at === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->episode_started_at);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->episode_started_at, true), $x);
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
     * Get the [activation] column value.
     * Activation payload
     * @return string
     */
    public function getActivation()
    {

        return $this->activation;
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
     * Get the [optionally formatted] temporal [last_empty_alert_at] column value.
     * Empty alerted at
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw DateTime object will be returned.
     * @return mixed Formatted date/time value as string or DateTime object (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getLastEmptyAlertAt($format = 'Y-m-d H:i:s')
    {
        if ($this->last_empty_alert_at === null) {
            return null;
        }

        if ($this->last_empty_alert_at === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->last_empty_alert_at);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->last_empty_alert_at, true), $x);
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
     * Get the [optionally formatted] temporal [last_parked_alert_at] column value.
     * Parked alerted at
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw DateTime object will be returned.
     * @return mixed Formatted date/time value as string or DateTime object (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getLastParkedAlertAt($format = 'Y-m-d H:i:s')
    {
        if ($this->last_parked_alert_at === null) {
            return null;
        }

        if ($this->last_parked_alert_at === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->last_parked_alert_at);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->last_parked_alert_at, true), $x);
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
     * Set the value of [id_fleet_slot] column.
     *
     * @param  int $v new value
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setIdFleetSlot($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_fleet_slot !== $v) {
            $this->id_fleet_slot = $v;
            $this->modifiedColumns[] = FleetSlotPeer::ID_FLEET_SLOT;
        }


        return $this;
    } // setIdFleetSlot()

    /**
     * Set the value of [symbol] column.
     * Symbol
     * @param  string $v new value
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setSymbol($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->symbol !== $v) {
            $this->symbol = $v;
            $this->modifiedColumns[] = FleetSlotPeer::SYMBOL;
        }


        return $this;
    } // setSymbol()

    /**
     * Set the value of [algo] column.
     * Algorithm
     * @param  int $v new value
     * @return FleetSlot The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setAlgo($v)
    {
        if ($v !== null) {
            $valueSet = FleetSlotPeer::getValueSet(FleetSlotPeer::ALGO);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->algo !== $v) {
            $this->algo = $v;
            $this->modifiedColumns[] = FleetSlotPeer::ALGO;
        }


        return $this;
    } // setAlgo()

    /**
     * Set the value of [target_slice] column.
     * Target slice (USDT)
     * @param  string $v new value
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setTargetSlice($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->target_slice !== $v) {
            $this->target_slice = $v;
            $this->modifiedColumns[] = FleetSlotPeer::TARGET_SLICE;
        }


        return $this;
    } // setTargetSlice()

    /**
     * Sets the value of the [enabled] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * Enabled
     * @param boolean|integer|string $v The new value
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setEnabled($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->enabled !== $v) {
            $this->enabled = $v;
            $this->modifiedColumns[] = FleetSlotPeer::ENABLED;
        }


        return $this;
    } // setEnabled()

    /**
     * Set the value of [state] column.
     * Arm state
     * @param  int $v new value
     * @return FleetSlot The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setState($v)
    {
        if ($v !== null) {
            $valueSet = FleetSlotPeer::getValueSet(FleetSlotPeer::STATE);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->state !== $v) {
            $this->state = $v;
            $this->modifiedColumns[] = FleetSlotPeer::STATE;
        }


        return $this;
    } // setState()

    /**
     * Set the value of [confirm_up] column.
     * Consecutive TREND_UP passes
     * @param  int $v new value
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setConfirmUp($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->confirm_up !== $v) {
            $this->confirm_up = $v;
            $this->modifiedColumns[] = FleetSlotPeer::CONFIRM_UP;
        }


        return $this;
    } // setConfirmUp()

    /**
     * Set the value of [confirm_down] column.
     * Consecutive non-TREND_UP passes
     * @param  int $v new value
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setConfirmDown($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->confirm_down !== $v) {
            $this->confirm_down = $v;
            $this->modifiedColumns[] = FleetSlotPeer::CONFIRM_DOWN;
        }


        return $this;
    } // setConfirmDown()

    /**
     * Set the value of [last_verdict] column.
     * Last verdict
     * @param  string $v new value
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setLastVerdict($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->last_verdict !== $v) {
            $this->last_verdict = $v;
            $this->modifiedColumns[] = FleetSlotPeer::LAST_VERDICT;
        }


        return $this;
    } // setLastVerdict()

    /**
     * Sets the value of [verdict_at] column to a normalized version of the date/time value specified.
     * Verdict at
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setVerdictAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->verdict_at !== null || $dt !== null) {
            $currentDateAsString = ($this->verdict_at !== null && $tmpDt = new DateTime($this->verdict_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->verdict_at = $newDateAsString;
                $this->modifiedColumns[] = FleetSlotPeer::VERDICT_AT;
            }
        } // if either are not null


        return $this;
    } // setVerdictAt()

    /**
     * Sets the value of [episode_started_at] column to a normalized version of the date/time value specified.
     * TREND_UP episode started
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setEpisodeStartedAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->episode_started_at !== null || $dt !== null) {
            $currentDateAsString = ($this->episode_started_at !== null && $tmpDt = new DateTime($this->episode_started_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->episode_started_at = $newDateAsString;
                $this->modifiedColumns[] = FleetSlotPeer::EPISODE_STARTED_AT;
            }
        } // if either are not null


        return $this;
    } // setEpisodeStartedAt()

    /**
     * Set the value of [activation] column.
     * Activation payload
     * @param  string $v new value
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setActivation($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->activation !== $v) {
            $this->activation = $v;
            $this->modifiedColumns[] = FleetSlotPeer::ACTIVATION;
        }


        return $this;
    } // setActivation()

    /**
     * Set the value of [id_grid_run] column.
     * Run
     * @param  int $v new value
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setIdGridRun($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_grid_run !== $v) {
            $this->id_grid_run = $v;
            $this->modifiedColumns[] = FleetSlotPeer::ID_GRID_RUN;
        }

        if ($this->aGridRun !== null && $this->aGridRun->getIdGridRun() !== $v) {
            $this->aGridRun = null;
        }


        return $this;
    } // setIdGridRun()

    /**
     * Sets the value of [last_empty_alert_at] column to a normalized version of the date/time value specified.
     * Empty alerted at
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setLastEmptyAlertAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->last_empty_alert_at !== null || $dt !== null) {
            $currentDateAsString = ($this->last_empty_alert_at !== null && $tmpDt = new DateTime($this->last_empty_alert_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->last_empty_alert_at = $newDateAsString;
                $this->modifiedColumns[] = FleetSlotPeer::LAST_EMPTY_ALERT_AT;
            }
        } // if either are not null


        return $this;
    } // setLastEmptyAlertAt()

    /**
     * Sets the value of [last_parked_alert_at] column to a normalized version of the date/time value specified.
     * Parked alerted at
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setLastParkedAlertAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->last_parked_alert_at !== null || $dt !== null) {
            $currentDateAsString = ($this->last_parked_alert_at !== null && $tmpDt = new DateTime($this->last_parked_alert_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->last_parked_alert_at = $newDateAsString;
                $this->modifiedColumns[] = FleetSlotPeer::LAST_PARKED_ALERT_AT;
            }
        } // if either are not null


        return $this;
    } // setLastParkedAlertAt()

    /**
     * Sets the value of [date_creation] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setDateCreation($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_creation !== null || $dt !== null) {
            $currentDateAsString = ($this->date_creation !== null && $tmpDt = new DateTime($this->date_creation)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_creation = $newDateAsString;
                $this->modifiedColumns[] = FleetSlotPeer::DATE_CREATION;
            }
        } // if either are not null


        return $this;
    } // setDateCreation()

    /**
     * Sets the value of [date_modification] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setDateModification($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_modification !== null || $dt !== null) {
            $currentDateAsString = ($this->date_modification !== null && $tmpDt = new DateTime($this->date_modification)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_modification = $newDateAsString;
                $this->modifiedColumns[] = FleetSlotPeer::DATE_MODIFICATION;
            }
        } // if either are not null


        return $this;
    } // setDateModification()

    /**
     * Set the value of [id_group_creation] column.
     *
     * @param  int $v new value
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setIdGroupCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_group_creation !== $v) {
            $this->id_group_creation = $v;
            $this->modifiedColumns[] = FleetSlotPeer::ID_GROUP_CREATION;
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
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setIdCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_creation !== $v) {
            $this->id_creation = $v;
            $this->modifiedColumns[] = FleetSlotPeer::ID_CREATION;
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
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setIdModification($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_modification !== $v) {
            $this->id_modification = $v;
            $this->modifiedColumns[] = FleetSlotPeer::ID_MODIFICATION;
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

            if ($this->target_slice !== '350') {
                return false;
            }

            if ($this->enabled !== true) {
                return false;
            }

            if ($this->state !== 0) {
                return false;
            }

            if ($this->confirm_up !== 0) {
                return false;
            }

            if ($this->confirm_down !== 0) {
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

            $this->id_fleet_slot = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
            $this->symbol = ($row[$startcol + 1] !== null) ? (string) $row[$startcol + 1] : null;
            $this->algo = ($row[$startcol + 2] !== null) ? (int) $row[$startcol + 2] : null;
            $this->target_slice = ($row[$startcol + 3] !== null) ? (string) $row[$startcol + 3] : null;
            $this->enabled = ($row[$startcol + 4] !== null) ? (boolean) $row[$startcol + 4] : null;
            $this->state = ($row[$startcol + 5] !== null) ? (int) $row[$startcol + 5] : null;
            $this->confirm_up = ($row[$startcol + 6] !== null) ? (int) $row[$startcol + 6] : null;
            $this->confirm_down = ($row[$startcol + 7] !== null) ? (int) $row[$startcol + 7] : null;
            $this->last_verdict = ($row[$startcol + 8] !== null) ? (string) $row[$startcol + 8] : null;
            $this->verdict_at = ($row[$startcol + 9] !== null) ? (string) $row[$startcol + 9] : null;
            $this->episode_started_at = ($row[$startcol + 10] !== null) ? (string) $row[$startcol + 10] : null;
            $this->activation = ($row[$startcol + 11] !== null) ? (string) $row[$startcol + 11] : null;
            $this->id_grid_run = ($row[$startcol + 12] !== null) ? (int) $row[$startcol + 12] : null;
            $this->last_empty_alert_at = ($row[$startcol + 13] !== null) ? (string) $row[$startcol + 13] : null;
            $this->last_parked_alert_at = ($row[$startcol + 14] !== null) ? (string) $row[$startcol + 14] : null;
            $this->date_creation = ($row[$startcol + 15] !== null) ? (string) $row[$startcol + 15] : null;
            $this->date_modification = ($row[$startcol + 16] !== null) ? (string) $row[$startcol + 16] : null;
            $this->id_group_creation = ($row[$startcol + 17] !== null) ? (int) $row[$startcol + 17] : null;
            $this->id_creation = ($row[$startcol + 18] !== null) ? (int) $row[$startcol + 18] : null;
            $this->id_modification = ($row[$startcol + 19] !== null) ? (int) $row[$startcol + 19] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);

            return $startcol + 20; // 20 = FleetSlotPeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating FleetSlot object", $e);
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
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = FleetSlotPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
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
            $this->collRegimeEpisodes = null;

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
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = FleetSlotQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                // GoatCheese behavior

                            if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                                \ApiGoat\Utility\TableVersion::bump('fleet_slot');
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
            $con = Propel::getConnection(FleetSlotPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
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
                                \ApiGoat\Utility\TableVersion::bump('fleet_slot');
                            }
                FleetSlotPeer::addInstanceToPool($this);
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

            if ($this->regimeEpisodesScheduledForDeletion !== null) {
                if (!$this->regimeEpisodesScheduledForDeletion->isEmpty()) {
                    foreach ($this->regimeEpisodesScheduledForDeletion as $regimeEpisode) {
                        // need to save related object because we set the relation to null
                        $regimeEpisode->save($con);
                    }
                    $this->regimeEpisodesScheduledForDeletion = null;
                }
            }

            if ($this->collRegimeEpisodes !== null) {
                foreach ($this->collRegimeEpisodes as $referrerFK) {
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

        $this->modifiedColumns[] = FleetSlotPeer::ID_FLEET_SLOT;
        if (null !== $this->id_fleet_slot) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . FleetSlotPeer::ID_FLEET_SLOT . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(FleetSlotPeer::ID_FLEET_SLOT)) {
            $modifiedColumns[':p' . $index++]  = '`id_fleet_slot`';
        }
        if ($this->isColumnModified(FleetSlotPeer::SYMBOL)) {
            $modifiedColumns[':p' . $index++]  = '`symbol`';
        }
        if ($this->isColumnModified(FleetSlotPeer::ALGO)) {
            $modifiedColumns[':p' . $index++]  = '`algo`';
        }
        if ($this->isColumnModified(FleetSlotPeer::TARGET_SLICE)) {
            $modifiedColumns[':p' . $index++]  = '`target_slice`';
        }
        if ($this->isColumnModified(FleetSlotPeer::ENABLED)) {
            $modifiedColumns[':p' . $index++]  = '`enabled`';
        }
        if ($this->isColumnModified(FleetSlotPeer::STATE)) {
            $modifiedColumns[':p' . $index++]  = '`state`';
        }
        if ($this->isColumnModified(FleetSlotPeer::CONFIRM_UP)) {
            $modifiedColumns[':p' . $index++]  = '`confirm_up`';
        }
        if ($this->isColumnModified(FleetSlotPeer::CONFIRM_DOWN)) {
            $modifiedColumns[':p' . $index++]  = '`confirm_down`';
        }
        if ($this->isColumnModified(FleetSlotPeer::LAST_VERDICT)) {
            $modifiedColumns[':p' . $index++]  = '`last_verdict`';
        }
        if ($this->isColumnModified(FleetSlotPeer::VERDICT_AT)) {
            $modifiedColumns[':p' . $index++]  = '`verdict_at`';
        }
        if ($this->isColumnModified(FleetSlotPeer::EPISODE_STARTED_AT)) {
            $modifiedColumns[':p' . $index++]  = '`episode_started_at`';
        }
        if ($this->isColumnModified(FleetSlotPeer::ACTIVATION)) {
            $modifiedColumns[':p' . $index++]  = '`activation`';
        }
        if ($this->isColumnModified(FleetSlotPeer::ID_GRID_RUN)) {
            $modifiedColumns[':p' . $index++]  = '`id_grid_run`';
        }
        if ($this->isColumnModified(FleetSlotPeer::LAST_EMPTY_ALERT_AT)) {
            $modifiedColumns[':p' . $index++]  = '`last_empty_alert_at`';
        }
        if ($this->isColumnModified(FleetSlotPeer::LAST_PARKED_ALERT_AT)) {
            $modifiedColumns[':p' . $index++]  = '`last_parked_alert_at`';
        }
        if ($this->isColumnModified(FleetSlotPeer::DATE_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_creation`';
        }
        if ($this->isColumnModified(FleetSlotPeer::DATE_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_modification`';
        }
        if ($this->isColumnModified(FleetSlotPeer::ID_GROUP_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_group_creation`';
        }
        if ($this->isColumnModified(FleetSlotPeer::ID_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_creation`';
        }
        if ($this->isColumnModified(FleetSlotPeer::ID_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_modification`';
        }

        $sql = sprintf(
            'INSERT INTO `fleet_slot` (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case '`id_fleet_slot`':
                        $stmt->bindValue($identifier, $this->id_fleet_slot, PDO::PARAM_INT);
                        break;
                    case '`symbol`':
                        $stmt->bindValue($identifier, $this->symbol, PDO::PARAM_STR);
                        break;
                    case '`algo`':
                        $stmt->bindValue($identifier, $this->algo, PDO::PARAM_INT);
                        break;
                    case '`target_slice`':
                        $stmt->bindValue($identifier, $this->target_slice, PDO::PARAM_STR);
                        break;
                    case '`enabled`':
                        $stmt->bindValue($identifier, (int) $this->enabled, PDO::PARAM_INT);
                        break;
                    case '`state`':
                        $stmt->bindValue($identifier, $this->state, PDO::PARAM_INT);
                        break;
                    case '`confirm_up`':
                        $stmt->bindValue($identifier, $this->confirm_up, PDO::PARAM_INT);
                        break;
                    case '`confirm_down`':
                        $stmt->bindValue($identifier, $this->confirm_down, PDO::PARAM_INT);
                        break;
                    case '`last_verdict`':
                        $stmt->bindValue($identifier, $this->last_verdict, PDO::PARAM_STR);
                        break;
                    case '`verdict_at`':
                        $stmt->bindValue($identifier, $this->verdict_at, PDO::PARAM_STR);
                        break;
                    case '`episode_started_at`':
                        $stmt->bindValue($identifier, $this->episode_started_at, PDO::PARAM_STR);
                        break;
                    case '`activation`':
                        $stmt->bindValue($identifier, $this->activation, PDO::PARAM_STR);
                        break;
                    case '`id_grid_run`':
                        $stmt->bindValue($identifier, $this->id_grid_run, PDO::PARAM_INT);
                        break;
                    case '`last_empty_alert_at`':
                        $stmt->bindValue($identifier, $this->last_empty_alert_at, PDO::PARAM_STR);
                        break;
                    case '`last_parked_alert_at`':
                        $stmt->bindValue($identifier, $this->last_parked_alert_at, PDO::PARAM_STR);
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
        $this->setIdFleetSlot($pk);

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


            if (($retval = FleetSlotPeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
            }


                if ($this->collRegimeEpisodes !== null) {
                    foreach ($this->collRegimeEpisodes as $referrerFK) {
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
        $pos = FleetSlotPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
        if (isset($alreadyDumpedObjects['FleetSlot'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['FleetSlot'][$this->getPrimaryKey()] = true;
        $keys = FleetSlotPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getIdFleetSlot(),
            $keys[1] => $this->getSymbol(),
            $keys[2] => $this->getAlgo(),
            $keys[3] => $this->getTargetSlice(),
            $keys[4] => $this->getEnabled(),
            $keys[5] => $this->getState(),
            $keys[6] => $this->getConfirmUp(),
            $keys[7] => $this->getConfirmDown(),
            $keys[8] => $this->getLastVerdict(),
            $keys[9] => $this->getVerdictAt(),
            $keys[10] => $this->getEpisodeStartedAt(),
            $keys[11] => $this->getActivation(),
            $keys[12] => $this->getIdGridRun(),
            $keys[13] => $this->getLastEmptyAlertAt(),
            $keys[14] => $this->getLastParkedAlertAt(),
            $keys[15] => $this->getDateCreation(),
            $keys[16] => $this->getDateModification(),
            $keys[17] => $this->getIdGroupCreation(),
            $keys[18] => $this->getIdCreation(),
            $keys[19] => $this->getIdModification(),
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
            if (null !== $this->collRegimeEpisodes) {
                $result['RegimeEpisodes'] = $this->collRegimeEpisodes->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = FleetSlotPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

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
                $this->setIdFleetSlot($value);
                break;
            case 1:
                $this->setSymbol($value);
                break;
            case 2:
                $valueSet = FleetSlotPeer::getValueSet(FleetSlotPeer::ALGO);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setAlgo($value);
                break;
            case 3:
                $this->setTargetSlice($value);
                break;
            case 4:
                $this->setEnabled($value);
                break;
            case 5:
                $valueSet = FleetSlotPeer::getValueSet(FleetSlotPeer::STATE);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setState($value);
                break;
            case 6:
                $this->setConfirmUp($value);
                break;
            case 7:
                $this->setConfirmDown($value);
                break;
            case 8:
                $this->setLastVerdict($value);
                break;
            case 9:
                $this->setVerdictAt($value);
                break;
            case 10:
                $this->setEpisodeStartedAt($value);
                break;
            case 11:
                $this->setActivation($value);
                break;
            case 12:
                $this->setIdGridRun($value);
                break;
            case 13:
                $this->setLastEmptyAlertAt($value);
                break;
            case 14:
                $this->setLastParkedAlertAt($value);
                break;
            case 15:
                $this->setDateCreation($value);
                break;
            case 16:
                $this->setDateModification($value);
                break;
            case 17:
                $this->setIdGroupCreation($value);
                break;
            case 18:
                $this->setIdCreation($value);
                break;
            case 19:
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
        $keys = FleetSlotPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setIdFleetSlot($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setSymbol($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setAlgo($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setTargetSlice($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setEnabled($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setState($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setConfirmUp($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setConfirmDown($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setLastVerdict($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setVerdictAt($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setEpisodeStartedAt($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setActivation($arr[$keys[11]]);
        if (array_key_exists($keys[12], $arr)) $this->setIdGridRun($arr[$keys[12]]);
        if (array_key_exists($keys[13], $arr)) $this->setLastEmptyAlertAt($arr[$keys[13]]);
        if (array_key_exists($keys[14], $arr)) $this->setLastParkedAlertAt($arr[$keys[14]]);
        if (array_key_exists($keys[15], $arr)) $this->setDateCreation($arr[$keys[15]]);
        if (array_key_exists($keys[16], $arr)) $this->setDateModification($arr[$keys[16]]);
        if (array_key_exists($keys[17], $arr)) $this->setIdGroupCreation($arr[$keys[17]]);
        if (array_key_exists($keys[18], $arr)) $this->setIdCreation($arr[$keys[18]]);
        if (array_key_exists($keys[19], $arr)) $this->setIdModification($arr[$keys[19]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(FleetSlotPeer::DATABASE_NAME);

        if ($this->isColumnModified(FleetSlotPeer::ID_FLEET_SLOT)) $criteria->add(FleetSlotPeer::ID_FLEET_SLOT, $this->id_fleet_slot);
        if ($this->isColumnModified(FleetSlotPeer::SYMBOL)) $criteria->add(FleetSlotPeer::SYMBOL, $this->symbol);
        if ($this->isColumnModified(FleetSlotPeer::ALGO)) $criteria->add(FleetSlotPeer::ALGO, $this->algo);
        if ($this->isColumnModified(FleetSlotPeer::TARGET_SLICE)) $criteria->add(FleetSlotPeer::TARGET_SLICE, $this->target_slice);
        if ($this->isColumnModified(FleetSlotPeer::ENABLED)) $criteria->add(FleetSlotPeer::ENABLED, $this->enabled);
        if ($this->isColumnModified(FleetSlotPeer::STATE)) $criteria->add(FleetSlotPeer::STATE, $this->state);
        if ($this->isColumnModified(FleetSlotPeer::CONFIRM_UP)) $criteria->add(FleetSlotPeer::CONFIRM_UP, $this->confirm_up);
        if ($this->isColumnModified(FleetSlotPeer::CONFIRM_DOWN)) $criteria->add(FleetSlotPeer::CONFIRM_DOWN, $this->confirm_down);
        if ($this->isColumnModified(FleetSlotPeer::LAST_VERDICT)) $criteria->add(FleetSlotPeer::LAST_VERDICT, $this->last_verdict);
        if ($this->isColumnModified(FleetSlotPeer::VERDICT_AT)) $criteria->add(FleetSlotPeer::VERDICT_AT, $this->verdict_at);
        if ($this->isColumnModified(FleetSlotPeer::EPISODE_STARTED_AT)) $criteria->add(FleetSlotPeer::EPISODE_STARTED_AT, $this->episode_started_at);
        if ($this->isColumnModified(FleetSlotPeer::ACTIVATION)) $criteria->add(FleetSlotPeer::ACTIVATION, $this->activation);
        if ($this->isColumnModified(FleetSlotPeer::ID_GRID_RUN)) $criteria->add(FleetSlotPeer::ID_GRID_RUN, $this->id_grid_run);
        if ($this->isColumnModified(FleetSlotPeer::LAST_EMPTY_ALERT_AT)) $criteria->add(FleetSlotPeer::LAST_EMPTY_ALERT_AT, $this->last_empty_alert_at);
        if ($this->isColumnModified(FleetSlotPeer::LAST_PARKED_ALERT_AT)) $criteria->add(FleetSlotPeer::LAST_PARKED_ALERT_AT, $this->last_parked_alert_at);
        if ($this->isColumnModified(FleetSlotPeer::DATE_CREATION)) $criteria->add(FleetSlotPeer::DATE_CREATION, $this->date_creation);
        if ($this->isColumnModified(FleetSlotPeer::DATE_MODIFICATION)) $criteria->add(FleetSlotPeer::DATE_MODIFICATION, $this->date_modification);
        if ($this->isColumnModified(FleetSlotPeer::ID_GROUP_CREATION)) $criteria->add(FleetSlotPeer::ID_GROUP_CREATION, $this->id_group_creation);
        if ($this->isColumnModified(FleetSlotPeer::ID_CREATION)) $criteria->add(FleetSlotPeer::ID_CREATION, $this->id_creation);
        if ($this->isColumnModified(FleetSlotPeer::ID_MODIFICATION)) $criteria->add(FleetSlotPeer::ID_MODIFICATION, $this->id_modification);

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
        $criteria = new Criteria(FleetSlotPeer::DATABASE_NAME);
        $criteria->add(FleetSlotPeer::ID_FLEET_SLOT, $this->id_fleet_slot);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getIdFleetSlot();
    }

    /**
     * Generic method to set the primary key (id_fleet_slot column).
     *
     * @param  int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setIdFleetSlot($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getIdFleetSlot();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of FleetSlot (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setSymbol($this->getSymbol());
        $copyObj->setAlgo($this->getAlgo());
        $copyObj->setTargetSlice($this->getTargetSlice());
        $copyObj->setEnabled($this->getEnabled());
        $copyObj->setState($this->getState());
        $copyObj->setConfirmUp($this->getConfirmUp());
        $copyObj->setConfirmDown($this->getConfirmDown());
        $copyObj->setLastVerdict($this->getLastVerdict());
        $copyObj->setVerdictAt($this->getVerdictAt());
        $copyObj->setEpisodeStartedAt($this->getEpisodeStartedAt());
        $copyObj->setActivation($this->getActivation());
        $copyObj->setIdGridRun($this->getIdGridRun());
        $copyObj->setLastEmptyAlertAt($this->getLastEmptyAlertAt());
        $copyObj->setLastParkedAlertAt($this->getLastParkedAlertAt());
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

            foreach ($this->getRegimeEpisodes() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addRegimeEpisode($relObj->copy($deepCopy));
                }
            }

            //unflag object copy
            $this->startCopy = false;
        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setIdFleetSlot(NULL); // this is a auto-increment column, so set to default value
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
     * @return FleetSlot Clone of current object.
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
     * @return FleetSlotPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new FleetSlotPeer();
        }

        return self::$peer;
    }

    /**
     * Declares an association between this object and a GridRun object.
     *
     * @param                  GridRun $v
     * @return FleetSlot The current object (for fluent API support)
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
            $v->addFleetSlot($this);
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
                $this->aGridRun->addFleetSlots($this);
             */
        }

        return $this->aGridRun;
    }

    /**
     * Declares an association between this object and a AuthyGroup object.
     *
     * @param                  AuthyGroup $v
     * @return FleetSlot The current object (for fluent API support)
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
            $v->addFleetSlot($this);
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
                $this->aAuthyGroup->addFleetSlots($this);
             */
        }

        return $this->aAuthyGroup;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return FleetSlot The current object (for fluent API support)
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
            $v->addFleetSlotRelatedByIdCreation($this);
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
                $this->aAuthyRelatedByIdCreation->addFleetSlotsRelatedByIdCreation($this);
             */
        }

        return $this->aAuthyRelatedByIdCreation;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return FleetSlot The current object (for fluent API support)
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
            $v->addFleetSlotRelatedByIdModification($this);
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
                $this->aAuthyRelatedByIdModification->addFleetSlotsRelatedByIdModification($this);
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
        if ('RegimeEpisode' == $relationName) {
            $this->initRegimeEpisodes();
        }
    }

    /**
     * Clears out the collRegimeEpisodes collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return FleetSlot The current object (for fluent API support)
     * @see        addRegimeEpisodes()
     */
    public function clearRegimeEpisodes()
    {
        $this->collRegimeEpisodes = null; // important to set this to null since that means it is uninitialized
        $this->collRegimeEpisodesPartial = null;

        return $this;
    }

    /**
     * reset is the collRegimeEpisodes collection loaded partially
     *
     * @return void
     */
    public function resetPartialRegimeEpisodes($v = true)
    {
        $this->collRegimeEpisodesPartial = $v;
    }

    /**
     * Initializes the collRegimeEpisodes collection.
     *
     * By default this just sets the collRegimeEpisodes collection to an empty array (like clearcollRegimeEpisodes());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initRegimeEpisodes($overrideExisting = true)
    {
        if (null !== $this->collRegimeEpisodes && !$overrideExisting) {
            return;
        }
        $this->collRegimeEpisodes = new PropelObjectCollection();
        $this->collRegimeEpisodes->setModel('RegimeEpisode');
    }

    /**
     * Gets an array of RegimeEpisode objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this FleetSlot is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|RegimeEpisode[] List of RegimeEpisode objects
     * @throws PropelException
     */
    public function getRegimeEpisodes($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collRegimeEpisodesPartial && !$this->isNew();
        if (null === $this->collRegimeEpisodes || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collRegimeEpisodes) {
                // return empty collection
                $this->initRegimeEpisodes();
            } else {
                $collRegimeEpisodes = RegimeEpisodeQuery::create(null, $criteria)
                    ->filterByFleetSlot($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collRegimeEpisodesPartial && count($collRegimeEpisodes)) {
                      $this->initRegimeEpisodes(false);

                      foreach ($collRegimeEpisodes as $obj) {
                        if (false == $this->collRegimeEpisodes->contains($obj)) {
                          $this->collRegimeEpisodes->append($obj);
                        }
                      }

                      $this->collRegimeEpisodesPartial = true;
                    }

                    $collRegimeEpisodes->getInternalIterator()->rewind();

                    return $collRegimeEpisodes;
                }

                if ($partial && $this->collRegimeEpisodes) {
                    foreach ($this->collRegimeEpisodes as $obj) {
                        if ($obj->isNew()) {
                            $collRegimeEpisodes[] = $obj;
                        }
                    }
                }

                $this->collRegimeEpisodes = $collRegimeEpisodes;
                $this->collRegimeEpisodesPartial = false;
            }
        }

        return $this->collRegimeEpisodes;
    }

    /**
     * Sets a collection of RegimeEpisode objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $regimeEpisodes A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return FleetSlot The current object (for fluent API support)
     */
    public function setRegimeEpisodes(PropelCollection $regimeEpisodes, ?PropelPDO $con = null)
    {
        $regimeEpisodesToDelete = $this->getRegimeEpisodes(new Criteria(), $con)->diff($regimeEpisodes);


        $this->regimeEpisodesScheduledForDeletion = $regimeEpisodesToDelete;

        foreach ($regimeEpisodesToDelete as $regimeEpisodeRemoved) {
            $regimeEpisodeRemoved->setFleetSlot(null);
        }

        $this->collRegimeEpisodes = null;
        foreach ($regimeEpisodes as $regimeEpisode) {
            $this->addRegimeEpisode($regimeEpisode);
        }

        $this->collRegimeEpisodes = $regimeEpisodes;
        $this->collRegimeEpisodesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related RegimeEpisode objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related RegimeEpisode objects.
     * @throws PropelException
     */
    public function countRegimeEpisodes(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collRegimeEpisodesPartial && !$this->isNew();
        if (null === $this->collRegimeEpisodes || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collRegimeEpisodes) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getRegimeEpisodes());
            }
            $query = RegimeEpisodeQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByFleetSlot($this)
                ->count($con);
        }

        return count($this->collRegimeEpisodes);
    }

    /**
     * Method called to associate a RegimeEpisode object to this object
     * through the RegimeEpisode foreign key attribute.
     *
     * @param    RegimeEpisode $l RegimeEpisode
     * @return FleetSlot The current object (for fluent API support)
     */
    public function addRegimeEpisode(RegimeEpisode $l)
    {
        if ($this->collRegimeEpisodes === null) {
            $this->initRegimeEpisodes();
            $this->collRegimeEpisodesPartial = true;
        }

        if (!in_array($l, $this->collRegimeEpisodes->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddRegimeEpisode($l);

            if ($this->regimeEpisodesScheduledForDeletion and $this->regimeEpisodesScheduledForDeletion->contains($l)) {
                $this->regimeEpisodesScheduledForDeletion->remove($this->regimeEpisodesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	RegimeEpisode $regimeEpisode The regimeEpisode object to add.
     */
    protected function doAddRegimeEpisode($regimeEpisode)
    {
        $this->collRegimeEpisodes[]= $regimeEpisode;
        $regimeEpisode->setFleetSlot($this);
    }

    /**
     * @param	RegimeEpisode $regimeEpisode The regimeEpisode object to remove.
     * @return FleetSlot The current object (for fluent API support)
     */
    public function removeRegimeEpisode($regimeEpisode)
    {
        if ($this->getRegimeEpisodes()->contains($regimeEpisode)) {
            $this->collRegimeEpisodes->remove($this->collRegimeEpisodes->search($regimeEpisode));
            if (null === $this->regimeEpisodesScheduledForDeletion) {
                $this->regimeEpisodesScheduledForDeletion = clone $this->collRegimeEpisodes;
                $this->regimeEpisodesScheduledForDeletion->clear();
            }
            $this->regimeEpisodesScheduledForDeletion[]= $regimeEpisode;
            $regimeEpisode->setFleetSlot(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|RegimeEpisode[] List of RegimeEpisode objects
     */
    public function getRegimeEpisodesJoinGridRun($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = RegimeEpisodeQuery::create(null, $criteria);
        $query->joinWith('GridRun', $join_behavior);

        return $this->getRegimeEpisodes($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|RegimeEpisode[] List of RegimeEpisode objects
     */
    public function getRegimeEpisodesJoinAuthyGroup($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = RegimeEpisodeQuery::create(null, $criteria);
        $query->joinWith('AuthyGroup', $join_behavior);

        return $this->getRegimeEpisodes($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|RegimeEpisode[] List of RegimeEpisode objects
     */
    public function getRegimeEpisodesJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = RegimeEpisodeQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getRegimeEpisodes($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|RegimeEpisode[] List of RegimeEpisode objects
     */
    public function getRegimeEpisodesJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = RegimeEpisodeQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getRegimeEpisodes($query, $con);
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id_fleet_slot = null;
        $this->symbol = null;
        $this->algo = null;
        $this->target_slice = null;
        $this->enabled = null;
        $this->state = null;
        $this->confirm_up = null;
        $this->confirm_down = null;
        $this->last_verdict = null;
        $this->verdict_at = null;
        $this->episode_started_at = null;
        $this->activation = null;
        $this->id_grid_run = null;
        $this->last_empty_alert_at = null;
        $this->last_parked_alert_at = null;
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
            if ($this->collRegimeEpisodes) {
                foreach ($this->collRegimeEpisodes as $o) {
                    $o->clearAllReferences($deep);
                }
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

        if ($this->collRegimeEpisodes instanceof PropelCollection) {
            $this->collRegimeEpisodes->clearIterator();
        }
        $this->collRegimeEpisodes = null;
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
        return (string) $this->exportTo(FleetSlotPeer::DEFAULT_STRING_FORMAT);
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
     * @return     FleetSlot The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged(){
        $this->modifiedColumns[] = FleetSlotPeer::DATE_MODIFICATION;

        return $this;
    }

}
