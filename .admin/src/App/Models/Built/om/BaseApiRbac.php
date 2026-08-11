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
use App\ApiLog;
use App\ApiLogQuery;
use App\ApiRbac;
use App\ApiRbacPeer;
use App\ApiRbacQuery;
use App\Authy;
use App\AuthyGroup;
use App\AuthyGroupQuery;
use App\AuthyQuery;

/**
 * Base class that represents a row from the 'api_rbac' table.
 *
 * API ACL
 *
 * @package    propel.generator..om
 */
abstract class BaseApiRbac extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'App\\ApiRbacPeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        ApiRbacPeer
     */
    protected static $peer;

    /**
     * The flag var to prevent infinite loop in deep copy
     * @var       boolean
     */
    protected $startCopy = false;

    /**
     * The value for the id_api_rbac field.
     * @var        int
     */
    protected $id_api_rbac;

    /**
     * The value for the date_creation field.
     * @var        string
     */
    protected $date_creation;

    /**
     * The value for the description field.
     * @var        string
     */
    protected $description;

    /**
     * The value for the model field.
     * @var        string
     */
    protected $model;

    /**
     * The value for the action field.
     * @var        string
     */
    protected $action;

    /**
     * The value for the body field.
     * @var        string
     */
    protected $body;

    /**
     * The value for the query field.
     * @var        string
     */
    protected $query;

    /**
     * The value for the method field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $method;

    /**
     * The value for the scope field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $scope;

    /**
     * The value for the rule field.
     * Note: this column has a database default value of: 1
     * @var        int
     */
    protected $rule;

    /**
     * The value for the count field.
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $count;

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
     * @var        PropelObjectCollection|ApiLog[] Collection to store aggregation of ApiLog objects.
     */
    protected $collApiLogs;
    protected $collApiLogsPartial;

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

    // GoatCheese behavior
    
        /** RBAC ruleset-cache trigger: set in preSave on rule-relevant changes. */
        public $gcRbacRuleChanged = false;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $apiLogsScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see        __construct()
     */
    public function applyDefaultValues()
    {
        $this->method = 0;
        $this->scope = 0;
        $this->rule = 1;
        $this->count = 0;
    }

    /**
     * Initializes internal state of BaseApiRbac object.
     * @see        applyDefaults()
     */
    public function __construct()
    {
        parent::__construct();
        $this->applyDefaultValues();
    }

    /**
     * @Field()
     * Get the [id_api_rbac] column value.
     *
     * @return int
     */
    public function getIdApiRbac()
    {

        return $this->id_api_rbac;
    }

    /**
     * @Field()
     * Get the [optionally formatted] temporal [date_creation] column value.
     * Date
     *
     * @param string $format The date/time format string (either date()-style or strftime()-style).
     *				 If format is null, then the raw DateTime object will be returned.
     * @return mixed Formatted date/time value as string or DateTime object (if format is null), null if column is null, and 0 if column value is 0000-00-00
     * @throws PropelException - if unable to parse/validate the date/time value.
     */
    public function getDateCreation($format = 'Y-m-d')
    {
        if ($this->date_creation === null) {
            return null;
        }

        if ($this->date_creation === '0000-00-00') {
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
     * Get the [description] column value.
     * Description
     * @return string
     */
    public function getDescription()
    {

        return $this->description;
    }

    /**
     * @Field()
     * Get the [model] column value.
     * Model
     * @return string
     */
    public function getModel()
    {

        return $this->model;
    }

    /**
     * @Field()
     * Get the [action] column value.
     * Action
     * @return string
     */
    public function getAction()
    {

        return $this->action;
    }

    /**
     * @Field()
     * Get the [body] column value.
     * Body
     * @return string
     */
    public function getBody()
    {

        return $this->body;
    }

    /**
     * @Field()
     * Get the [query] column value.
     * Query
     * @return string
     */
    public function getQuery()
    {

        return $this->query;
    }

    /**
     * @Field()
     * Get the [method] column value.
     * Method
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getMethod()
    {
        if (null === $this->method) {
            return null;
        }
        $valueSet = ApiRbacPeer::getValueSet(ApiRbacPeer::METHOD);
        if (!isset($valueSet[$this->method])) {
            throw new PropelException('Unknown stored enum key: ' . $this->method);
        }

        return $valueSet[$this->method];
    }

    /**
     * @Field()
     * Get the [scope] column value.
     * Scope
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getScope()
    {
        if (null === $this->scope) {
            return null;
        }
        $valueSet = ApiRbacPeer::getValueSet(ApiRbacPeer::SCOPE);
        if (!isset($valueSet[$this->scope])) {
            throw new PropelException('Unknown stored enum key: ' . $this->scope);
        }

        return $valueSet[$this->scope];
    }

    /**
     * @Field()
     * Get the [rule] column value.
     * Rule
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getRule()
    {
        if (null === $this->rule) {
            return null;
        }
        $valueSet = ApiRbacPeer::getValueSet(ApiRbacPeer::RULE);
        if (!isset($valueSet[$this->rule])) {
            throw new PropelException('Unknown stored enum key: ' . $this->rule);
        }

        return $valueSet[$this->rule];
    }

    /**
     * @Field()
     * Get the [count] column value.
     * Used count
     * @return int
     */
    public function getCount()
    {

        return $this->count;
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
     * Set the value of [id_api_rbac] column.
     *
     * @param  int $v new value
     * @return ApiRbac The current object (for fluent API support)
     */
    public function setIdApiRbac($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_api_rbac !== $v) {
            $this->id_api_rbac = $v;
            $this->modifiedColumns[] = ApiRbacPeer::ID_API_RBAC;
        }


        return $this;
    } // setIdApiRbac()

    /**
     * Sets the value of [date_creation] column to a normalized version of the date/time value specified.
     * Date
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return ApiRbac The current object (for fluent API support)
     */
    public function setDateCreation($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_creation !== null || $dt !== null) {
            $currentDateAsString = ($this->date_creation !== null && $tmpDt = new DateTime($this->date_creation)) ? $tmpDt->format('Y-m-d') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_creation = $newDateAsString;
                $this->modifiedColumns[] = ApiRbacPeer::DATE_CREATION;
            }
        } // if either are not null


        return $this;
    } // setDateCreation()

    /**
     * Set the value of [description] column.
     * Description
     * @param  string $v new value
     * @return ApiRbac The current object (for fluent API support)
     */
    public function setDescription($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->description !== $v) {
            $this->description = $v;
            $this->modifiedColumns[] = ApiRbacPeer::DESCRIPTION;
        }


        return $this;
    } // setDescription()

    /**
     * Set the value of [model] column.
     * Model
     * @param  string $v new value
     * @return ApiRbac The current object (for fluent API support)
     */
    public function setModel($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->model !== $v) {
            $this->model = $v;
            $this->modifiedColumns[] = ApiRbacPeer::MODEL;
        }


        return $this;
    } // setModel()

    /**
     * Set the value of [action] column.
     * Action
     * @param  string $v new value
     * @return ApiRbac The current object (for fluent API support)
     */
    public function setAction($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->action !== $v) {
            $this->action = $v;
            $this->modifiedColumns[] = ApiRbacPeer::ACTION;
        }


        return $this;
    } // setAction()

    /**
     * Set the value of [body] column.
     * Body
     * @param  string $v new value
     * @return ApiRbac The current object (for fluent API support)
     */
    public function setBody($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->body !== $v) {
            $this->body = $v;
            $this->modifiedColumns[] = ApiRbacPeer::BODY;
        }


        return $this;
    } // setBody()

    /**
     * Set the value of [query] column.
     * Query
     * @param  string $v new value
     * @return ApiRbac The current object (for fluent API support)
     */
    public function setQuery($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->query !== $v) {
            $this->query = $v;
            $this->modifiedColumns[] = ApiRbacPeer::QUERY;
        }


        return $this;
    } // setQuery()

    /**
     * Set the value of [method] column.
     * Method
     * @param  int $v new value
     * @return ApiRbac The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setMethod($v)
    {
        if ($v !== null) {
            $valueSet = ApiRbacPeer::getValueSet(ApiRbacPeer::METHOD);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->method !== $v) {
            $this->method = $v;
            $this->modifiedColumns[] = ApiRbacPeer::METHOD;
        }


        return $this;
    } // setMethod()

    /**
     * Set the value of [scope] column.
     * Scope
     * @param  int $v new value
     * @return ApiRbac The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setScope($v)
    {
        if ($v !== null) {
            $valueSet = ApiRbacPeer::getValueSet(ApiRbacPeer::SCOPE);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->scope !== $v) {
            $this->scope = $v;
            $this->modifiedColumns[] = ApiRbacPeer::SCOPE;
        }


        return $this;
    } // setScope()

    /**
     * Set the value of [rule] column.
     * Rule
     * @param  int $v new value
     * @return ApiRbac The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setRule($v)
    {
        if ($v !== null) {
            $valueSet = ApiRbacPeer::getValueSet(ApiRbacPeer::RULE);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->rule !== $v) {
            $this->rule = $v;
            $this->modifiedColumns[] = ApiRbacPeer::RULE;
        }


        return $this;
    } // setRule()

    /**
     * Set the value of [count] column.
     * Used count
     * @param  int $v new value
     * @return ApiRbac The current object (for fluent API support)
     */
    public function setCount($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->count !== $v) {
            $this->count = $v;
            $this->modifiedColumns[] = ApiRbacPeer::COUNT;
        }


        return $this;
    } // setCount()

    /**
     * Sets the value of [date_modification] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return ApiRbac The current object (for fluent API support)
     */
    public function setDateModification($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_modification !== null || $dt !== null) {
            $currentDateAsString = ($this->date_modification !== null && $tmpDt = new DateTime($this->date_modification)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_modification = $newDateAsString;
                $this->modifiedColumns[] = ApiRbacPeer::DATE_MODIFICATION;
            }
        } // if either are not null


        return $this;
    } // setDateModification()

    /**
     * Set the value of [id_group_creation] column.
     *
     * @param  int $v new value
     * @return ApiRbac The current object (for fluent API support)
     */
    public function setIdGroupCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_group_creation !== $v) {
            $this->id_group_creation = $v;
            $this->modifiedColumns[] = ApiRbacPeer::ID_GROUP_CREATION;
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
     * @return ApiRbac The current object (for fluent API support)
     */
    public function setIdCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_creation !== $v) {
            $this->id_creation = $v;
            $this->modifiedColumns[] = ApiRbacPeer::ID_CREATION;
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
     * @return ApiRbac The current object (for fluent API support)
     */
    public function setIdModification($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_modification !== $v) {
            $this->id_modification = $v;
            $this->modifiedColumns[] = ApiRbacPeer::ID_MODIFICATION;
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
            if ($this->method !== 0) {
                return false;
            }

            if ($this->scope !== 0) {
                return false;
            }

            if ($this->rule !== 1) {
                return false;
            }

            if ($this->count !== 0) {
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

            $this->id_api_rbac = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
            $this->date_creation = ($row[$startcol + 1] !== null) ? (string) $row[$startcol + 1] : null;
            $this->description = ($row[$startcol + 2] !== null) ? (string) $row[$startcol + 2] : null;
            $this->model = ($row[$startcol + 3] !== null) ? (string) $row[$startcol + 3] : null;
            $this->action = ($row[$startcol + 4] !== null) ? (string) $row[$startcol + 4] : null;
            $this->body = ($row[$startcol + 5] !== null) ? (string) $row[$startcol + 5] : null;
            $this->query = ($row[$startcol + 6] !== null) ? (string) $row[$startcol + 6] : null;
            $this->method = ($row[$startcol + 7] !== null) ? (int) $row[$startcol + 7] : null;
            $this->scope = ($row[$startcol + 8] !== null) ? (int) $row[$startcol + 8] : null;
            $this->rule = ($row[$startcol + 9] !== null) ? (int) $row[$startcol + 9] : null;
            $this->count = ($row[$startcol + 10] !== null) ? (int) $row[$startcol + 10] : null;
            $this->date_modification = ($row[$startcol + 11] !== null) ? (string) $row[$startcol + 11] : null;
            $this->id_group_creation = ($row[$startcol + 12] !== null) ? (int) $row[$startcol + 12] : null;
            $this->id_creation = ($row[$startcol + 13] !== null) ? (int) $row[$startcol + 13] : null;
            $this->id_modification = ($row[$startcol + 14] !== null) ? (int) $row[$startcol + 14] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);

            return $startcol + 15; // 15 = ApiRbacPeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating ApiRbac object", $e);
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
            $con = Propel::getConnection(ApiRbacPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = ApiRbacPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
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
            $this->collApiLogs = null;

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
            $con = Propel::getConnection(ApiRbacPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = ApiRbacQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                // GoatCheese behavior
                
                            if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                                \ApiGoat\Utility\TableVersion::bump('api_rbac');
                            }
                            if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                                \ApiGoat\Utility\TableVersion::bump('api_rbac@rules');
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
            $con = Propel::getConnection(ApiRbacPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        $isInsert = $this->isNew();
        try {
            $ret = $this->preSave($con);
            // GoatCheese behavior
            
                    if ($this->isNew()
                        || $this->isColumnModified(\App\ApiRbacPeer::RULE)
                        || $this->isColumnModified(\App\ApiRbacPeer::SCOPE)
                        || $this->isColumnModified(\App\ApiRbacPeer::BODY)
                        || $this->isColumnModified(\App\ApiRbacPeer::MODEL)
                        || $this->isColumnModified(\App\ApiRbacPeer::ACTION)
                        || $this->isColumnModified(\App\ApiRbacPeer::METHOD)) {
                        $this->gcRbacRuleChanged = true;
                    }
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
                                \ApiGoat\Utility\TableVersion::bump('api_rbac');
                            }
                            if (!empty($this->gcRbacRuleChanged)) {
                                $this->gcRbacRuleChanged = false;
                                if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                                    \ApiGoat\Utility\TableVersion::bump('api_rbac@rules');
                                }
                            }
                ApiRbacPeer::addInstanceToPool($this);
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

            if ($this->apiLogsScheduledForDeletion !== null) {
                if (!$this->apiLogsScheduledForDeletion->isEmpty()) {
                    ApiLogQuery::create()
                        ->filterByPrimaryKeys($this->apiLogsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->apiLogsScheduledForDeletion = null;
                }
            }

            if ($this->collApiLogs !== null) {
                foreach ($this->collApiLogs as $referrerFK) {
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

        $this->modifiedColumns[] = ApiRbacPeer::ID_API_RBAC;
        if (null !== $this->id_api_rbac) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . ApiRbacPeer::ID_API_RBAC . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(ApiRbacPeer::ID_API_RBAC)) {
            $modifiedColumns[':p' . $index++]  = '`id_api_rbac`';
        }
        if ($this->isColumnModified(ApiRbacPeer::DATE_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_creation`';
        }
        if ($this->isColumnModified(ApiRbacPeer::DESCRIPTION)) {
            $modifiedColumns[':p' . $index++]  = '`description`';
        }
        if ($this->isColumnModified(ApiRbacPeer::MODEL)) {
            $modifiedColumns[':p' . $index++]  = '`model`';
        }
        if ($this->isColumnModified(ApiRbacPeer::ACTION)) {
            $modifiedColumns[':p' . $index++]  = '`action`';
        }
        if ($this->isColumnModified(ApiRbacPeer::BODY)) {
            $modifiedColumns[':p' . $index++]  = '`body`';
        }
        if ($this->isColumnModified(ApiRbacPeer::QUERY)) {
            $modifiedColumns[':p' . $index++]  = '`query`';
        }
        if ($this->isColumnModified(ApiRbacPeer::METHOD)) {
            $modifiedColumns[':p' . $index++]  = '`method`';
        }
        if ($this->isColumnModified(ApiRbacPeer::SCOPE)) {
            $modifiedColumns[':p' . $index++]  = '`scope`';
        }
        if ($this->isColumnModified(ApiRbacPeer::RULE)) {
            $modifiedColumns[':p' . $index++]  = '`rule`';
        }
        if ($this->isColumnModified(ApiRbacPeer::COUNT)) {
            $modifiedColumns[':p' . $index++]  = '`count`';
        }
        if ($this->isColumnModified(ApiRbacPeer::DATE_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_modification`';
        }
        if ($this->isColumnModified(ApiRbacPeer::ID_GROUP_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_group_creation`';
        }
        if ($this->isColumnModified(ApiRbacPeer::ID_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_creation`';
        }
        if ($this->isColumnModified(ApiRbacPeer::ID_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_modification`';
        }

        $sql = sprintf(
            'INSERT INTO `api_rbac` (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case '`id_api_rbac`':
                        $stmt->bindValue($identifier, $this->id_api_rbac, PDO::PARAM_INT);
                        break;
                    case '`date_creation`':
                        $stmt->bindValue($identifier, $this->date_creation, PDO::PARAM_STR);
                        break;
                    case '`description`':
                        $stmt->bindValue($identifier, $this->description, PDO::PARAM_STR);
                        break;
                    case '`model`':
                        $stmt->bindValue($identifier, $this->model, PDO::PARAM_STR);
                        break;
                    case '`action`':
                        $stmt->bindValue($identifier, $this->action, PDO::PARAM_STR);
                        break;
                    case '`body`':
                        $stmt->bindValue($identifier, $this->body, PDO::PARAM_STR);
                        break;
                    case '`query`':
                        $stmt->bindValue($identifier, $this->query, PDO::PARAM_STR);
                        break;
                    case '`method`':
                        $stmt->bindValue($identifier, $this->method, PDO::PARAM_INT);
                        break;
                    case '`scope`':
                        $stmt->bindValue($identifier, $this->scope, PDO::PARAM_INT);
                        break;
                    case '`rule`':
                        $stmt->bindValue($identifier, $this->rule, PDO::PARAM_INT);
                        break;
                    case '`count`':
                        $stmt->bindValue($identifier, $this->count, PDO::PARAM_INT);
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
        $this->setIdApiRbac($pk);

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


            if (($retval = ApiRbacPeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
            }


                if ($this->collApiLogs !== null) {
                    foreach ($this->collApiLogs as $referrerFK) {
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
        $pos = ApiRbacPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
        if (isset($alreadyDumpedObjects['ApiRbac'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['ApiRbac'][$this->getPrimaryKey()] = true;
        $keys = ApiRbacPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getIdApiRbac(),
            $keys[1] => $this->getDateCreation(),
            $keys[2] => $this->getDescription(),
            $keys[3] => $this->getModel(),
            $keys[4] => $this->getAction(),
            $keys[5] => $this->getBody(),
            $keys[6] => $this->getQuery(),
            $keys[7] => $this->getMethod(),
            $keys[8] => $this->getScope(),
            $keys[9] => $this->getRule(),
            $keys[10] => $this->getCount(),
            $keys[11] => $this->getDateModification(),
            $keys[12] => $this->getIdGroupCreation(),
            $keys[13] => $this->getIdCreation(),
            $keys[14] => $this->getIdModification(),
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
            if (null !== $this->collApiLogs) {
                $result['ApiLogs'] = $this->collApiLogs->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = ApiRbacPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

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
                $this->setIdApiRbac($value);
                break;
            case 1:
                $this->setDateCreation($value);
                break;
            case 2:
                $this->setDescription($value);
                break;
            case 3:
                $this->setModel($value);
                break;
            case 4:
                $this->setAction($value);
                break;
            case 5:
                $this->setBody($value);
                break;
            case 6:
                $this->setQuery($value);
                break;
            case 7:
                $valueSet = ApiRbacPeer::getValueSet(ApiRbacPeer::METHOD);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setMethod($value);
                break;
            case 8:
                $valueSet = ApiRbacPeer::getValueSet(ApiRbacPeer::SCOPE);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setScope($value);
                break;
            case 9:
                $valueSet = ApiRbacPeer::getValueSet(ApiRbacPeer::RULE);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setRule($value);
                break;
            case 10:
                $this->setCount($value);
                break;
            case 11:
                $this->setDateModification($value);
                break;
            case 12:
                $this->setIdGroupCreation($value);
                break;
            case 13:
                $this->setIdCreation($value);
                break;
            case 14:
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
        $keys = ApiRbacPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setIdApiRbac($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setDateCreation($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setDescription($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setModel($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setAction($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setBody($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setQuery($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setMethod($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setScope($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setRule($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setCount($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setDateModification($arr[$keys[11]]);
        if (array_key_exists($keys[12], $arr)) $this->setIdGroupCreation($arr[$keys[12]]);
        if (array_key_exists($keys[13], $arr)) $this->setIdCreation($arr[$keys[13]]);
        if (array_key_exists($keys[14], $arr)) $this->setIdModification($arr[$keys[14]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(ApiRbacPeer::DATABASE_NAME);

        if ($this->isColumnModified(ApiRbacPeer::ID_API_RBAC)) $criteria->add(ApiRbacPeer::ID_API_RBAC, $this->id_api_rbac);
        if ($this->isColumnModified(ApiRbacPeer::DATE_CREATION)) $criteria->add(ApiRbacPeer::DATE_CREATION, $this->date_creation);
        if ($this->isColumnModified(ApiRbacPeer::DESCRIPTION)) $criteria->add(ApiRbacPeer::DESCRIPTION, $this->description);
        if ($this->isColumnModified(ApiRbacPeer::MODEL)) $criteria->add(ApiRbacPeer::MODEL, $this->model);
        if ($this->isColumnModified(ApiRbacPeer::ACTION)) $criteria->add(ApiRbacPeer::ACTION, $this->action);
        if ($this->isColumnModified(ApiRbacPeer::BODY)) $criteria->add(ApiRbacPeer::BODY, $this->body);
        if ($this->isColumnModified(ApiRbacPeer::QUERY)) $criteria->add(ApiRbacPeer::QUERY, $this->query);
        if ($this->isColumnModified(ApiRbacPeer::METHOD)) $criteria->add(ApiRbacPeer::METHOD, $this->method);
        if ($this->isColumnModified(ApiRbacPeer::SCOPE)) $criteria->add(ApiRbacPeer::SCOPE, $this->scope);
        if ($this->isColumnModified(ApiRbacPeer::RULE)) $criteria->add(ApiRbacPeer::RULE, $this->rule);
        if ($this->isColumnModified(ApiRbacPeer::COUNT)) $criteria->add(ApiRbacPeer::COUNT, $this->count);
        if ($this->isColumnModified(ApiRbacPeer::DATE_MODIFICATION)) $criteria->add(ApiRbacPeer::DATE_MODIFICATION, $this->date_modification);
        if ($this->isColumnModified(ApiRbacPeer::ID_GROUP_CREATION)) $criteria->add(ApiRbacPeer::ID_GROUP_CREATION, $this->id_group_creation);
        if ($this->isColumnModified(ApiRbacPeer::ID_CREATION)) $criteria->add(ApiRbacPeer::ID_CREATION, $this->id_creation);
        if ($this->isColumnModified(ApiRbacPeer::ID_MODIFICATION)) $criteria->add(ApiRbacPeer::ID_MODIFICATION, $this->id_modification);

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
        $criteria = new Criteria(ApiRbacPeer::DATABASE_NAME);
        $criteria->add(ApiRbacPeer::ID_API_RBAC, $this->id_api_rbac);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getIdApiRbac();
    }

    /**
     * Generic method to set the primary key (id_api_rbac column).
     *
     * @param  int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setIdApiRbac($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getIdApiRbac();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of ApiRbac (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setDateCreation($this->getDateCreation());
        $copyObj->setDescription($this->getDescription());
        $copyObj->setModel($this->getModel());
        $copyObj->setAction($this->getAction());
        $copyObj->setBody($this->getBody());
        $copyObj->setQuery($this->getQuery());
        $copyObj->setMethod($this->getMethod());
        $copyObj->setScope($this->getScope());
        $copyObj->setRule($this->getRule());
        $copyObj->setCount($this->getCount());
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

            foreach ($this->getApiLogs() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addApiLog($relObj->copy($deepCopy));
                }
            }

            //unflag object copy
            $this->startCopy = false;
        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setIdApiRbac(NULL); // this is a auto-increment column, so set to default value
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
     * @return ApiRbac Clone of current object.
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
     * @return ApiRbacPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new ApiRbacPeer();
        }

        return self::$peer;
    }

    /**
     * Declares an association between this object and a AuthyGroup object.
     *
     * @param                  AuthyGroup $v
     * @return ApiRbac The current object (for fluent API support)
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
            $v->addApiRbac($this);
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
                $this->aAuthyGroup->addApiRbacs($this);
             */
        }

        return $this->aAuthyGroup;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return ApiRbac The current object (for fluent API support)
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
            $v->addApiRbacRelatedByIdCreation($this);
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
                $this->aAuthyRelatedByIdCreation->addApiRbacsRelatedByIdCreation($this);
             */
        }

        return $this->aAuthyRelatedByIdCreation;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return ApiRbac The current object (for fluent API support)
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
            $v->addApiRbacRelatedByIdModification($this);
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
                $this->aAuthyRelatedByIdModification->addApiRbacsRelatedByIdModification($this);
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
        if ('ApiLog' == $relationName) {
            $this->initApiLogs();
        }
    }

    /**
     * Clears out the collApiLogs collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return ApiRbac The current object (for fluent API support)
     * @see        addApiLogs()
     */
    public function clearApiLogs()
    {
        $this->collApiLogs = null; // important to set this to null since that means it is uninitialized
        $this->collApiLogsPartial = null;

        return $this;
    }

    /**
     * reset is the collApiLogs collection loaded partially
     *
     * @return void
     */
    public function resetPartialApiLogs($v = true)
    {
        $this->collApiLogsPartial = $v;
    }

    /**
     * Initializes the collApiLogs collection.
     *
     * By default this just sets the collApiLogs collection to an empty array (like clearcollApiLogs());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initApiLogs($overrideExisting = true)
    {
        if (null !== $this->collApiLogs && !$overrideExisting) {
            return;
        }
        $this->collApiLogs = new PropelObjectCollection();
        $this->collApiLogs->setModel('ApiLog');
    }

    /**
     * Gets an array of ApiLog objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ApiRbac is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|ApiLog[] List of ApiLog objects
     * @throws PropelException
     */
    public function getApiLogs($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collApiLogsPartial && !$this->isNew();
        if (null === $this->collApiLogs || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collApiLogs) {
                // return empty collection
                $this->initApiLogs();
            } else {
                $collApiLogs = ApiLogQuery::create(null, $criteria)
                    ->filterByApiRbac($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collApiLogsPartial && count($collApiLogs)) {
                      $this->initApiLogs(false);

                      foreach ($collApiLogs as $obj) {
                        if (false == $this->collApiLogs->contains($obj)) {
                          $this->collApiLogs->append($obj);
                        }
                      }

                      $this->collApiLogsPartial = true;
                    }

                    $collApiLogs->getInternalIterator()->rewind();

                    return $collApiLogs;
                }

                if ($partial && $this->collApiLogs) {
                    foreach ($this->collApiLogs as $obj) {
                        if ($obj->isNew()) {
                            $collApiLogs[] = $obj;
                        }
                    }
                }

                $this->collApiLogs = $collApiLogs;
                $this->collApiLogsPartial = false;
            }
        }

        return $this->collApiLogs;
    }

    /**
     * Sets a collection of ApiLog objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $apiLogs A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return ApiRbac The current object (for fluent API support)
     */
    public function setApiLogs(PropelCollection $apiLogs, ?PropelPDO $con = null)
    {
        $apiLogsToDelete = $this->getApiLogs(new Criteria(), $con)->diff($apiLogs);


        $this->apiLogsScheduledForDeletion = $apiLogsToDelete;

        foreach ($apiLogsToDelete as $apiLogRemoved) {
            $apiLogRemoved->setApiRbac(null);
        }

        $this->collApiLogs = null;
        foreach ($apiLogs as $apiLog) {
            $this->addApiLog($apiLog);
        }

        $this->collApiLogs = $apiLogs;
        $this->collApiLogsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related ApiLog objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related ApiLog objects.
     * @throws PropelException
     */
    public function countApiLogs(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collApiLogsPartial && !$this->isNew();
        if (null === $this->collApiLogs || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collApiLogs) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getApiLogs());
            }
            $query = ApiLogQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByApiRbac($this)
                ->count($con);
        }

        return count($this->collApiLogs);
    }

    /**
     * Method called to associate a ApiLog object to this object
     * through the ApiLog foreign key attribute.
     *
     * @param    ApiLog $l ApiLog
     * @return ApiRbac The current object (for fluent API support)
     */
    public function addApiLog(ApiLog $l)
    {
        if ($this->collApiLogs === null) {
            $this->initApiLogs();
            $this->collApiLogsPartial = true;
        }

        if (!in_array($l, $this->collApiLogs->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddApiLog($l);

            if ($this->apiLogsScheduledForDeletion and $this->apiLogsScheduledForDeletion->contains($l)) {
                $this->apiLogsScheduledForDeletion->remove($this->apiLogsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	ApiLog $apiLog The apiLog object to add.
     */
    protected function doAddApiLog($apiLog)
    {
        $this->collApiLogs[]= $apiLog;
        $apiLog->setApiRbac($this);
    }

    /**
     * @param	ApiLog $apiLog The apiLog object to remove.
     * @return ApiRbac The current object (for fluent API support)
     */
    public function removeApiLog($apiLog)
    {
        if ($this->getApiLogs()->contains($apiLog)) {
            $this->collApiLogs->remove($this->collApiLogs->search($apiLog));
            if (null === $this->apiLogsScheduledForDeletion) {
                $this->apiLogsScheduledForDeletion = clone $this->collApiLogs;
                $this->apiLogsScheduledForDeletion->clear();
            }
            $this->apiLogsScheduledForDeletion[]= clone $apiLog;
            $apiLog->setApiRbac(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|ApiLog[] List of ApiLog objects
     */
    public function getApiLogsJoinAuthy($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = ApiLogQuery::create(null, $criteria);
        $query->joinWith('Authy', $join_behavior);

        return $this->getApiLogs($query, $con);
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id_api_rbac = null;
        $this->date_creation = null;
        $this->description = null;
        $this->model = null;
        $this->action = null;
        $this->body = null;
        $this->query = null;
        $this->method = null;
        $this->scope = null;
        $this->rule = null;
        $this->count = null;
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
            if ($this->collApiLogs) {
                foreach ($this->collApiLogs as $o) {
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

        if ($this->collApiLogs instanceof PropelCollection) {
            $this->collApiLogs->clearIterator();
        }
        $this->collApiLogs = null;
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
        return (string) $this->exportTo(ApiRbacPeer::DEFAULT_STRING_FORMAT);
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
     * @return     ApiRbac The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged(){
        $this->modifiedColumns[] = ApiRbacPeer::DATE_MODIFICATION;

        return $this;
    }

}
