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
use App\MarketOutlook;
use App\MarketOutlookPeer;
use App\MarketOutlookQuery;

/**
 * Base class that represents a row from the 'market_outlook' table.
 *
 * Market Outlook
 *
 * @package    propel.generator..om
 */
abstract class BaseMarketOutlook extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'App\\MarketOutlookPeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        MarketOutlookPeer
     */
    protected static $peer;

    /**
     * The flag var to prevent infinite loop in deep copy
     * @var       boolean
     */
    protected $startCopy = false;

    /**
     * The value for the id_market_outlook field.
     * @var        int
     */
    protected $id_market_outlook;

    /**
     * The value for the symbol field.
     * @var        string
     */
    protected $symbol;

    /**
     * The value for the kind field.
     * Note: this column has a database default value of: 'Change'
     * @var        string
     */
    protected $kind;

    /**
     * The value for the verdict field.
     * @var        string
     */
    protected $verdict;

    /**
     * The value for the prev_verdict field.
     * @var        string
     */
    protected $prev_verdict;

    /**
     * The value for the price_at field.
     * @var        string
     */
    protected $price_at;

    /**
     * The value for the called_at field.
     * @var        string
     */
    protected $called_at;

    /**
     * The value for the detail field.
     * @var        string
     */
    protected $detail;

    /**
     * The value for the eval_status field.
     * Note: this column has a database default value of: 'Pending'
     * @var        string
     */
    protected $eval_status;

    /**
     * The value for the price_7d field.
     * @var        string
     */
    protected $price_7d;

    /**
     * The value for the price_30d field.
     * @var        string
     */
    protected $price_30d;

    /**
     * The value for the ret_7d field.
     * @var        string
     */
    protected $ret_7d;

    /**
     * The value for the ret_30d field.
     * @var        string
     */
    protected $ret_30d;

    /**
     * The value for the max_adverse_pct field.
     * @var        string
     */
    protected $max_adverse_pct;

    /**
     * The value for the hit_7d field.
     * @var        boolean
     */
    protected $hit_7d;

    /**
     * The value for the hit_30d field.
     * @var        boolean
     */
    protected $hit_30d;

    /**
     * The value for the scored_at field.
     * @var        string
     */
    protected $scored_at;

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
        $this->kind = 'Change';
        $this->eval_status = 'Pending';
    }

    /**
     * Initializes internal state of BaseMarketOutlook object.
     * @see        applyDefaults()
     */
    public function __construct()
    {
        parent::__construct();
        $this->applyDefaultValues();
    }

    /**
     * @Field()
     * Get the [id_market_outlook] column value.
     *
     * @return int
     */
    public function getIdMarketOutlook()
    {

        return $this->id_market_outlook;
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
     * Get the [kind] column value.
     * Kind
     * @return string
     */
    public function getKind()
    {

        return $this->kind;
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
     * Get the [prev_verdict] column value.
     * Previous
     * @return string
     */
    public function getPrevVerdict()
    {

        return $this->prev_verdict;
    }

    /**
     * @Field()
     * Get the [price_at] column value.
     * Price at call
     * @return string
     */
    public function getPriceAt()
    {

        return $this->price_at;
    }

    /**
     * @Field()
     * Get the [optionally formatted] temporal [called_at] column value.
     * Called at
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw DateTime object will be returned.
     * @return mixed Formatted date/time value as string or DateTime object (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getCalledAt($format = 'Y-m-d H:i:s')
    {
        if ($this->called_at === null) {
            return null;
        }

        if ($this->called_at === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->called_at);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->called_at, true), $x);
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
     * Get the [detail] column value.
     * Detail
     * @return string
     */
    public function getDetail()
    {

        return $this->detail;
    }

    /**
     * @Field()
     * Get the [eval_status] column value.
     * Eval
     * @return string
     */
    public function getEvalStatus()
    {

        return $this->eval_status;
    }

    /**
     * @Field()
     * Get the [price_7d] column value.
     * Price +7d
     * @return string
     */
    public function getPrice7d()
    {

        return $this->price_7d;
    }

    /**
     * @Field()
     * Get the [price_30d] column value.
     * Price +30d
     * @return string
     */
    public function getPrice30d()
    {

        return $this->price_30d;
    }

    /**
     * @Field()
     * Get the [ret_7d] column value.
     * Return 7d %
     * @return string
     */
    public function getRet7d()
    {

        return $this->ret_7d;
    }

    /**
     * @Field()
     * Get the [ret_30d] column value.
     * Return 30d %
     * @return string
     */
    public function getRet30d()
    {

        return $this->ret_30d;
    }

    /**
     * @Field()
     * Get the [max_adverse_pct] column value.
     * Max adverse 30d %
     * @return string
     */
    public function getMaxAdversePct()
    {

        return $this->max_adverse_pct;
    }

    /**
     * @Field()
     * Get the [hit_7d] column value.
     * Hit 7d
     * @return boolean
     */
    public function getHit7d()
    {

        return $this->hit_7d;
    }

    /**
     * @Field()
     * Get the [hit_30d] column value.
     * Hit 30d
     * @return boolean
     */
    public function getHit30d()
    {

        return $this->hit_30d;
    }

    /**
     * @Field()
     * Get the [optionally formatted] temporal [scored_at] column value.
     * Scored at
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw DateTime object will be returned.
     * @return mixed Formatted date/time value as string or DateTime object (if format is null), null if column is null, and 0 if column value is 0000-00-00 00:00:00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getScoredAt($format = 'Y-m-d H:i:s')
    {
        if ($this->scored_at === null) {
            return null;
        }

        if ($this->scored_at === '0000-00-00 00:00:00') {
            // while technically this is not a default value of null,
            // this seems to be closest in meaning.
            return null;
        }

        try {
            $dt = new DateTime($this->scored_at);
        } catch (Exception $x) {
            throw new PropelException("Internally stored date/time/timestamp value could not be converted to DateTime: " . var_export($this->scored_at, true), $x);
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
     * Set the value of [id_market_outlook] column.
     *
     * @param  int $v new value
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setIdMarketOutlook($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_market_outlook !== $v) {
            $this->id_market_outlook = $v;
            $this->modifiedColumns[] = MarketOutlookPeer::ID_MARKET_OUTLOOK;
        }


        return $this;
    } // setIdMarketOutlook()

    /**
     * Set the value of [symbol] column.
     * Symbol
     * @param  string $v new value
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setSymbol($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->symbol !== $v) {
            $this->symbol = $v;
            $this->modifiedColumns[] = MarketOutlookPeer::SYMBOL;
        }


        return $this;
    } // setSymbol()

    /**
     * Set the value of [kind] column.
     * Kind
     * @param  string $v new value
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setKind($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->kind !== $v) {
            $this->kind = $v;
            $this->modifiedColumns[] = MarketOutlookPeer::KIND;
        }


        return $this;
    } // setKind()

    /**
     * Set the value of [verdict] column.
     * Verdict
     * @param  string $v new value
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setVerdict($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->verdict !== $v) {
            $this->verdict = $v;
            $this->modifiedColumns[] = MarketOutlookPeer::VERDICT;
        }


        return $this;
    } // setVerdict()

    /**
     * Set the value of [prev_verdict] column.
     * Previous
     * @param  string $v new value
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setPrevVerdict($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->prev_verdict !== $v) {
            $this->prev_verdict = $v;
            $this->modifiedColumns[] = MarketOutlookPeer::PREV_VERDICT;
        }


        return $this;
    } // setPrevVerdict()

    /**
     * Set the value of [price_at] column.
     * Price at call
     * @param  string $v new value
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setPriceAt($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->price_at !== $v) {
            $this->price_at = $v;
            $this->modifiedColumns[] = MarketOutlookPeer::PRICE_AT;
        }


        return $this;
    } // setPriceAt()

    /**
     * Sets the value of [called_at] column to a normalized version of the date/time value specified.
     * Called at
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setCalledAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->called_at !== null || $dt !== null) {
            $currentDateAsString = ($this->called_at !== null && $tmpDt = new DateTime($this->called_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->called_at = $newDateAsString;
                $this->modifiedColumns[] = MarketOutlookPeer::CALLED_AT;
            }
        } // if either are not null


        return $this;
    } // setCalledAt()

    /**
     * Set the value of [detail] column.
     * Detail
     * @param  string $v new value
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setDetail($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->detail !== $v) {
            $this->detail = $v;
            $this->modifiedColumns[] = MarketOutlookPeer::DETAIL;
        }


        return $this;
    } // setDetail()

    /**
     * Set the value of [eval_status] column.
     * Eval
     * @param  string $v new value
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setEvalStatus($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->eval_status !== $v) {
            $this->eval_status = $v;
            $this->modifiedColumns[] = MarketOutlookPeer::EVAL_STATUS;
        }


        return $this;
    } // setEvalStatus()

    /**
     * Set the value of [price_7d] column.
     * Price +7d
     * @param  string $v new value
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setPrice7d($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->price_7d !== $v) {
            $this->price_7d = $v;
            $this->modifiedColumns[] = MarketOutlookPeer::PRICE_7D;
        }


        return $this;
    } // setPrice7d()

    /**
     * Set the value of [price_30d] column.
     * Price +30d
     * @param  string $v new value
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setPrice30d($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->price_30d !== $v) {
            $this->price_30d = $v;
            $this->modifiedColumns[] = MarketOutlookPeer::PRICE_30D;
        }


        return $this;
    } // setPrice30d()

    /**
     * Set the value of [ret_7d] column.
     * Return 7d %
     * @param  string $v new value
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setRet7d($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->ret_7d !== $v) {
            $this->ret_7d = $v;
            $this->modifiedColumns[] = MarketOutlookPeer::RET_7D;
        }


        return $this;
    } // setRet7d()

    /**
     * Set the value of [ret_30d] column.
     * Return 30d %
     * @param  string $v new value
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setRet30d($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->ret_30d !== $v) {
            $this->ret_30d = $v;
            $this->modifiedColumns[] = MarketOutlookPeer::RET_30D;
        }


        return $this;
    } // setRet30d()

    /**
     * Set the value of [max_adverse_pct] column.
     * Max adverse 30d %
     * @param  string $v new value
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setMaxAdversePct($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (string) $v;
        }

        if ($this->max_adverse_pct !== $v) {
            $this->max_adverse_pct = $v;
            $this->modifiedColumns[] = MarketOutlookPeer::MAX_ADVERSE_PCT;
        }


        return $this;
    } // setMaxAdversePct()

    /**
     * Sets the value of the [hit_7d] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * Hit 7d
     * @param boolean|integer|string $v The new value
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setHit7d($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->hit_7d !== $v) {
            $this->hit_7d = $v;
            $this->modifiedColumns[] = MarketOutlookPeer::HIT_7D;
        }


        return $this;
    } // setHit7d()

    /**
     * Sets the value of the [hit_30d] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * Hit 30d
     * @param boolean|integer|string $v The new value
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setHit30d($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->hit_30d !== $v) {
            $this->hit_30d = $v;
            $this->modifiedColumns[] = MarketOutlookPeer::HIT_30D;
        }


        return $this;
    } // setHit30d()

    /**
     * Sets the value of [scored_at] column to a normalized version of the date/time value specified.
     * Scored at
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setScoredAt($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->scored_at !== null || $dt !== null) {
            $currentDateAsString = ($this->scored_at !== null && $tmpDt = new DateTime($this->scored_at)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->scored_at = $newDateAsString;
                $this->modifiedColumns[] = MarketOutlookPeer::SCORED_AT;
            }
        } // if either are not null


        return $this;
    } // setScoredAt()

    /**
     * Sets the value of [date_creation] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setDateCreation($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_creation !== null || $dt !== null) {
            $currentDateAsString = ($this->date_creation !== null && $tmpDt = new DateTime($this->date_creation)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_creation = $newDateAsString;
                $this->modifiedColumns[] = MarketOutlookPeer::DATE_CREATION;
            }
        } // if either are not null


        return $this;
    } // setDateCreation()

    /**
     * Sets the value of [date_modification] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setDateModification($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_modification !== null || $dt !== null) {
            $currentDateAsString = ($this->date_modification !== null && $tmpDt = new DateTime($this->date_modification)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_modification = $newDateAsString;
                $this->modifiedColumns[] = MarketOutlookPeer::DATE_MODIFICATION;
            }
        } // if either are not null


        return $this;
    } // setDateModification()

    /**
     * Set the value of [id_group_creation] column.
     *
     * @param  int $v new value
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setIdGroupCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_group_creation !== $v) {
            $this->id_group_creation = $v;
            $this->modifiedColumns[] = MarketOutlookPeer::ID_GROUP_CREATION;
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
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setIdCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_creation !== $v) {
            $this->id_creation = $v;
            $this->modifiedColumns[] = MarketOutlookPeer::ID_CREATION;
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
     * @return MarketOutlook The current object (for fluent API support)
     */
    public function setIdModification($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_modification !== $v) {
            $this->id_modification = $v;
            $this->modifiedColumns[] = MarketOutlookPeer::ID_MODIFICATION;
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
            if ($this->kind !== 'Change') {
                return false;
            }

            if ($this->eval_status !== 'Pending') {
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

            $this->id_market_outlook = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
            $this->symbol = ($row[$startcol + 1] !== null) ? (string) $row[$startcol + 1] : null;
            $this->kind = ($row[$startcol + 2] !== null) ? (string) $row[$startcol + 2] : null;
            $this->verdict = ($row[$startcol + 3] !== null) ? (string) $row[$startcol + 3] : null;
            $this->prev_verdict = ($row[$startcol + 4] !== null) ? (string) $row[$startcol + 4] : null;
            $this->price_at = ($row[$startcol + 5] !== null) ? (string) $row[$startcol + 5] : null;
            $this->called_at = ($row[$startcol + 6] !== null) ? (string) $row[$startcol + 6] : null;
            $this->detail = ($row[$startcol + 7] !== null) ? (string) $row[$startcol + 7] : null;
            $this->eval_status = ($row[$startcol + 8] !== null) ? (string) $row[$startcol + 8] : null;
            $this->price_7d = ($row[$startcol + 9] !== null) ? (string) $row[$startcol + 9] : null;
            $this->price_30d = ($row[$startcol + 10] !== null) ? (string) $row[$startcol + 10] : null;
            $this->ret_7d = ($row[$startcol + 11] !== null) ? (string) $row[$startcol + 11] : null;
            $this->ret_30d = ($row[$startcol + 12] !== null) ? (string) $row[$startcol + 12] : null;
            $this->max_adverse_pct = ($row[$startcol + 13] !== null) ? (string) $row[$startcol + 13] : null;
            $this->hit_7d = ($row[$startcol + 14] !== null) ? (boolean) $row[$startcol + 14] : null;
            $this->hit_30d = ($row[$startcol + 15] !== null) ? (boolean) $row[$startcol + 15] : null;
            $this->scored_at = ($row[$startcol + 16] !== null) ? (string) $row[$startcol + 16] : null;
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

            return $startcol + 22; // 22 = MarketOutlookPeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating MarketOutlook object", $e);
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
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = MarketOutlookPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
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
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = MarketOutlookQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                // GoatCheese behavior

                            if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                                \ApiGoat\Utility\TableVersion::bump('market_outlook');
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
            $con = Propel::getConnection(MarketOutlookPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
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
                                \ApiGoat\Utility\TableVersion::bump('market_outlook');
                            }
                MarketOutlookPeer::addInstanceToPool($this);
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

        $this->modifiedColumns[] = MarketOutlookPeer::ID_MARKET_OUTLOOK;
        if (null !== $this->id_market_outlook) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . MarketOutlookPeer::ID_MARKET_OUTLOOK . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(MarketOutlookPeer::ID_MARKET_OUTLOOK)) {
            $modifiedColumns[':p' . $index++]  = '`id_market_outlook`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::SYMBOL)) {
            $modifiedColumns[':p' . $index++]  = '`symbol`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::KIND)) {
            $modifiedColumns[':p' . $index++]  = '`kind`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::VERDICT)) {
            $modifiedColumns[':p' . $index++]  = '`verdict`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::PREV_VERDICT)) {
            $modifiedColumns[':p' . $index++]  = '`prev_verdict`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::PRICE_AT)) {
            $modifiedColumns[':p' . $index++]  = '`price_at`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::CALLED_AT)) {
            $modifiedColumns[':p' . $index++]  = '`called_at`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::DETAIL)) {
            $modifiedColumns[':p' . $index++]  = '`detail`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::EVAL_STATUS)) {
            $modifiedColumns[':p' . $index++]  = '`eval_status`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::PRICE_7D)) {
            $modifiedColumns[':p' . $index++]  = '`price_7d`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::PRICE_30D)) {
            $modifiedColumns[':p' . $index++]  = '`price_30d`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::RET_7D)) {
            $modifiedColumns[':p' . $index++]  = '`ret_7d`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::RET_30D)) {
            $modifiedColumns[':p' . $index++]  = '`ret_30d`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::MAX_ADVERSE_PCT)) {
            $modifiedColumns[':p' . $index++]  = '`max_adverse_pct`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::HIT_7D)) {
            $modifiedColumns[':p' . $index++]  = '`hit_7d`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::HIT_30D)) {
            $modifiedColumns[':p' . $index++]  = '`hit_30d`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::SCORED_AT)) {
            $modifiedColumns[':p' . $index++]  = '`scored_at`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::DATE_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_creation`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::DATE_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_modification`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::ID_GROUP_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_group_creation`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::ID_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_creation`';
        }
        if ($this->isColumnModified(MarketOutlookPeer::ID_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_modification`';
        }

        $sql = sprintf(
            'INSERT INTO `market_outlook` (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case '`id_market_outlook`':
                        $stmt->bindValue($identifier, $this->id_market_outlook, PDO::PARAM_INT);
                        break;
                    case '`symbol`':
                        $stmt->bindValue($identifier, $this->symbol, PDO::PARAM_STR);
                        break;
                    case '`kind`':
                        $stmt->bindValue($identifier, $this->kind, PDO::PARAM_STR);
                        break;
                    case '`verdict`':
                        $stmt->bindValue($identifier, $this->verdict, PDO::PARAM_STR);
                        break;
                    case '`prev_verdict`':
                        $stmt->bindValue($identifier, $this->prev_verdict, PDO::PARAM_STR);
                        break;
                    case '`price_at`':
                        $stmt->bindValue($identifier, $this->price_at, PDO::PARAM_STR);
                        break;
                    case '`called_at`':
                        $stmt->bindValue($identifier, $this->called_at, PDO::PARAM_STR);
                        break;
                    case '`detail`':
                        $stmt->bindValue($identifier, $this->detail, PDO::PARAM_STR);
                        break;
                    case '`eval_status`':
                        $stmt->bindValue($identifier, $this->eval_status, PDO::PARAM_STR);
                        break;
                    case '`price_7d`':
                        $stmt->bindValue($identifier, $this->price_7d, PDO::PARAM_STR);
                        break;
                    case '`price_30d`':
                        $stmt->bindValue($identifier, $this->price_30d, PDO::PARAM_STR);
                        break;
                    case '`ret_7d`':
                        $stmt->bindValue($identifier, $this->ret_7d, PDO::PARAM_STR);
                        break;
                    case '`ret_30d`':
                        $stmt->bindValue($identifier, $this->ret_30d, PDO::PARAM_STR);
                        break;
                    case '`max_adverse_pct`':
                        $stmt->bindValue($identifier, $this->max_adverse_pct, PDO::PARAM_STR);
                        break;
                    case '`hit_7d`':
                        $stmt->bindValue($identifier, (int) $this->hit_7d, PDO::PARAM_INT);
                        break;
                    case '`hit_30d`':
                        $stmt->bindValue($identifier, (int) $this->hit_30d, PDO::PARAM_INT);
                        break;
                    case '`scored_at`':
                        $stmt->bindValue($identifier, $this->scored_at, PDO::PARAM_STR);
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
        $this->setIdMarketOutlook($pk);

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


            if (($retval = MarketOutlookPeer::doValidate($this, $columns)) !== true) {
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
        $pos = MarketOutlookPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
        if (isset($alreadyDumpedObjects['MarketOutlook'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['MarketOutlook'][$this->getPrimaryKey()] = true;
        $keys = MarketOutlookPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getIdMarketOutlook(),
            $keys[1] => $this->getSymbol(),
            $keys[2] => $this->getKind(),
            $keys[3] => $this->getVerdict(),
            $keys[4] => $this->getPrevVerdict(),
            $keys[5] => $this->getPriceAt(),
            $keys[6] => $this->getCalledAt(),
            $keys[7] => $this->getDetail(),
            $keys[8] => $this->getEvalStatus(),
            $keys[9] => $this->getPrice7d(),
            $keys[10] => $this->getPrice30d(),
            $keys[11] => $this->getRet7d(),
            $keys[12] => $this->getRet30d(),
            $keys[13] => $this->getMaxAdversePct(),
            $keys[14] => $this->getHit7d(),
            $keys[15] => $this->getHit30d(),
            $keys[16] => $this->getScoredAt(),
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
        $pos = MarketOutlookPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

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
                $this->setIdMarketOutlook($value);
                break;
            case 1:
                $this->setSymbol($value);
                break;
            case 2:
                $this->setKind($value);
                break;
            case 3:
                $this->setVerdict($value);
                break;
            case 4:
                $this->setPrevVerdict($value);
                break;
            case 5:
                $this->setPriceAt($value);
                break;
            case 6:
                $this->setCalledAt($value);
                break;
            case 7:
                $this->setDetail($value);
                break;
            case 8:
                $this->setEvalStatus($value);
                break;
            case 9:
                $this->setPrice7d($value);
                break;
            case 10:
                $this->setPrice30d($value);
                break;
            case 11:
                $this->setRet7d($value);
                break;
            case 12:
                $this->setRet30d($value);
                break;
            case 13:
                $this->setMaxAdversePct($value);
                break;
            case 14:
                $this->setHit7d($value);
                break;
            case 15:
                $this->setHit30d($value);
                break;
            case 16:
                $this->setScoredAt($value);
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
        $keys = MarketOutlookPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setIdMarketOutlook($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setSymbol($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setKind($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setVerdict($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setPrevVerdict($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setPriceAt($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setCalledAt($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setDetail($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setEvalStatus($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setPrice7d($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setPrice30d($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setRet7d($arr[$keys[11]]);
        if (array_key_exists($keys[12], $arr)) $this->setRet30d($arr[$keys[12]]);
        if (array_key_exists($keys[13], $arr)) $this->setMaxAdversePct($arr[$keys[13]]);
        if (array_key_exists($keys[14], $arr)) $this->setHit7d($arr[$keys[14]]);
        if (array_key_exists($keys[15], $arr)) $this->setHit30d($arr[$keys[15]]);
        if (array_key_exists($keys[16], $arr)) $this->setScoredAt($arr[$keys[16]]);
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
        $criteria = new Criteria(MarketOutlookPeer::DATABASE_NAME);

        if ($this->isColumnModified(MarketOutlookPeer::ID_MARKET_OUTLOOK)) $criteria->add(MarketOutlookPeer::ID_MARKET_OUTLOOK, $this->id_market_outlook);
        if ($this->isColumnModified(MarketOutlookPeer::SYMBOL)) $criteria->add(MarketOutlookPeer::SYMBOL, $this->symbol);
        if ($this->isColumnModified(MarketOutlookPeer::KIND)) $criteria->add(MarketOutlookPeer::KIND, $this->kind);
        if ($this->isColumnModified(MarketOutlookPeer::VERDICT)) $criteria->add(MarketOutlookPeer::VERDICT, $this->verdict);
        if ($this->isColumnModified(MarketOutlookPeer::PREV_VERDICT)) $criteria->add(MarketOutlookPeer::PREV_VERDICT, $this->prev_verdict);
        if ($this->isColumnModified(MarketOutlookPeer::PRICE_AT)) $criteria->add(MarketOutlookPeer::PRICE_AT, $this->price_at);
        if ($this->isColumnModified(MarketOutlookPeer::CALLED_AT)) $criteria->add(MarketOutlookPeer::CALLED_AT, $this->called_at);
        if ($this->isColumnModified(MarketOutlookPeer::DETAIL)) $criteria->add(MarketOutlookPeer::DETAIL, $this->detail);
        if ($this->isColumnModified(MarketOutlookPeer::EVAL_STATUS)) $criteria->add(MarketOutlookPeer::EVAL_STATUS, $this->eval_status);
        if ($this->isColumnModified(MarketOutlookPeer::PRICE_7D)) $criteria->add(MarketOutlookPeer::PRICE_7D, $this->price_7d);
        if ($this->isColumnModified(MarketOutlookPeer::PRICE_30D)) $criteria->add(MarketOutlookPeer::PRICE_30D, $this->price_30d);
        if ($this->isColumnModified(MarketOutlookPeer::RET_7D)) $criteria->add(MarketOutlookPeer::RET_7D, $this->ret_7d);
        if ($this->isColumnModified(MarketOutlookPeer::RET_30D)) $criteria->add(MarketOutlookPeer::RET_30D, $this->ret_30d);
        if ($this->isColumnModified(MarketOutlookPeer::MAX_ADVERSE_PCT)) $criteria->add(MarketOutlookPeer::MAX_ADVERSE_PCT, $this->max_adverse_pct);
        if ($this->isColumnModified(MarketOutlookPeer::HIT_7D)) $criteria->add(MarketOutlookPeer::HIT_7D, $this->hit_7d);
        if ($this->isColumnModified(MarketOutlookPeer::HIT_30D)) $criteria->add(MarketOutlookPeer::HIT_30D, $this->hit_30d);
        if ($this->isColumnModified(MarketOutlookPeer::SCORED_AT)) $criteria->add(MarketOutlookPeer::SCORED_AT, $this->scored_at);
        if ($this->isColumnModified(MarketOutlookPeer::DATE_CREATION)) $criteria->add(MarketOutlookPeer::DATE_CREATION, $this->date_creation);
        if ($this->isColumnModified(MarketOutlookPeer::DATE_MODIFICATION)) $criteria->add(MarketOutlookPeer::DATE_MODIFICATION, $this->date_modification);
        if ($this->isColumnModified(MarketOutlookPeer::ID_GROUP_CREATION)) $criteria->add(MarketOutlookPeer::ID_GROUP_CREATION, $this->id_group_creation);
        if ($this->isColumnModified(MarketOutlookPeer::ID_CREATION)) $criteria->add(MarketOutlookPeer::ID_CREATION, $this->id_creation);
        if ($this->isColumnModified(MarketOutlookPeer::ID_MODIFICATION)) $criteria->add(MarketOutlookPeer::ID_MODIFICATION, $this->id_modification);

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
        $criteria = new Criteria(MarketOutlookPeer::DATABASE_NAME);
        $criteria->add(MarketOutlookPeer::ID_MARKET_OUTLOOK, $this->id_market_outlook);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getIdMarketOutlook();
    }

    /**
     * Generic method to set the primary key (id_market_outlook column).
     *
     * @param  int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setIdMarketOutlook($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getIdMarketOutlook();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of MarketOutlook (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setSymbol($this->getSymbol());
        $copyObj->setKind($this->getKind());
        $copyObj->setVerdict($this->getVerdict());
        $copyObj->setPrevVerdict($this->getPrevVerdict());
        $copyObj->setPriceAt($this->getPriceAt());
        $copyObj->setCalledAt($this->getCalledAt());
        $copyObj->setDetail($this->getDetail());
        $copyObj->setEvalStatus($this->getEvalStatus());
        $copyObj->setPrice7d($this->getPrice7d());
        $copyObj->setPrice30d($this->getPrice30d());
        $copyObj->setRet7d($this->getRet7d());
        $copyObj->setRet30d($this->getRet30d());
        $copyObj->setMaxAdversePct($this->getMaxAdversePct());
        $copyObj->setHit7d($this->getHit7d());
        $copyObj->setHit30d($this->getHit30d());
        $copyObj->setScoredAt($this->getScoredAt());
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
            $copyObj->setIdMarketOutlook(NULL); // this is a auto-increment column, so set to default value
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
     * @return MarketOutlook Clone of current object.
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
     * @return MarketOutlookPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new MarketOutlookPeer();
        }

        return self::$peer;
    }

    /**
     * Declares an association between this object and a AuthyGroup object.
     *
     * @param                  AuthyGroup $v
     * @return MarketOutlook The current object (for fluent API support)
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
            $v->addMarketOutlook($this);
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
                $this->aAuthyGroup->addMarketOutlooks($this);
             */
        }

        return $this->aAuthyGroup;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return MarketOutlook The current object (for fluent API support)
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
            $v->addMarketOutlookRelatedByIdCreation($this);
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
                $this->aAuthyRelatedByIdCreation->addMarketOutlooksRelatedByIdCreation($this);
             */
        }

        return $this->aAuthyRelatedByIdCreation;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return MarketOutlook The current object (for fluent API support)
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
            $v->addMarketOutlookRelatedByIdModification($this);
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
                $this->aAuthyRelatedByIdModification->addMarketOutlooksRelatedByIdModification($this);
             */
        }

        return $this->aAuthyRelatedByIdModification;
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id_market_outlook = null;
        $this->symbol = null;
        $this->kind = null;
        $this->verdict = null;
        $this->prev_verdict = null;
        $this->price_at = null;
        $this->called_at = null;
        $this->detail = null;
        $this->eval_status = null;
        $this->price_7d = null;
        $this->price_30d = null;
        $this->ret_7d = null;
        $this->ret_30d = null;
        $this->max_adverse_pct = null;
        $this->hit_7d = null;
        $this->hit_30d = null;
        $this->scored_at = null;
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
        return (string) $this->exportTo(MarketOutlookPeer::DEFAULT_STRING_FORMAT);
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
     * @return     MarketOutlook The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged(){
        $this->modifiedColumns[] = MarketOutlookPeer::DATE_MODIFICATION;

        return $this;
    }

}
