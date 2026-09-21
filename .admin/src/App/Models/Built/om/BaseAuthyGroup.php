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
use App\ApiRbac;
use App\ApiRbacQuery;
use App\Authy;
use App\AuthyGroup;
use App\AuthyGroupPeer;
use App\AuthyGroupQuery;
use App\AuthyGroupX;
use App\AuthyGroupXQuery;
use App\AuthyQuery;
use App\AuthyRefreshToken;
use App\AuthyRefreshTokenQuery;
use App\BotCommand;
use App\BotCommandQuery;
use App\BotDecision;
use App\BotDecisionQuery;
use App\BotEvent;
use App\BotEventQuery;
use App\BotOrder;
use App\BotOrderQuery;
use App\Config;
use App\ConfigQuery;
use App\Country;
use App\CountryQuery;
use App\FleetSlot;
use App\FleetSlotQuery;
use App\GridRun;
use App\GridRunAudit;
use App\GridRunAuditQuery;
use App\GridRunQuery;
use App\MarketCandle;
use App\MarketCandleQuery;
use App\MarketOutlook;
use App\MarketOutlookQuery;
use App\MarketOutlookState;
use App\MarketOutlookStateQuery;
use App\MarketRegime;
use App\MarketRegimeQuery;
use App\MarketSummary;
use App\MarketSummaryQuery;
use App\MessageI18n;
use App\MessageI18nQuery;
use App\OauthAccessToken;
use App\OauthAccessTokenQuery;
use App\OauthAuthCode;
use App\OauthAuthCodeQuery;
use App\OauthClient;
use App\OauthClientQuery;
use App\OauthRefreshToken;
use App\OauthRefreshTokenQuery;
use App\PushDevice;
use App\PushDeviceQuery;
use App\RegimeEpisode;
use App\RegimeEpisodeQuery;
use App\SimWallet;
use App\SimWalletQuery;
use App\Template;
use App\TemplateFile;
use App\TemplateFileQuery;
use App\TemplateQuery;
use App\TradeCycle;
use App\TradeCycleQuery;
use App\WalletNav;
use App\WalletNavQuery;

/**
 * Base class that represents a row from the 'authy_group' table.
 *
 * Group
 *
 * @package    propel.generator..om
 */
abstract class BaseAuthyGroup extends BaseObject implements Persistent
{
    /**
     * Peer class name
     */
    const PEER = 'App\\AuthyGroupPeer';

    /**
     * The Peer class.
     * Instance provides a convenient way of calling static methods on a class
     * that calling code may not be able to identify.
     * @var        AuthyGroupPeer
     */
    protected static $peer;

    /**
     * The flag var to prevent infinite loop in deep copy
     * @var       boolean
     */
    protected $startCopy = false;

    /**
     * The value for the id_authy_group field.
     * @var        int
     */
    protected $id_authy_group;

    /**
     * The value for the name field.
     * @var        string
     */
    protected $name;

    /**
     * The value for the desc field.
     * @var        string
     */
    protected $desc;

    /**
     * The value for the default_group field.
     * @var        int
     */
    protected $default_group;

    /**
     * The value for the admin field.
     * @var        int
     */
    protected $admin;

    /**
     * The value for the rights_all field.
     * @var        string
     */
    protected $rights_all;

    /**
     * The value for the rights_owner field.
     * @var        string
     */
    protected $rights_owner;

    /**
     * The value for the rights_group field.
     * @var        string
     */
    protected $rights_group;

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
    protected $aAuthyGroupRelatedByIdGroupCreation;

    /**
     * @var        Authy
     */
    protected $aAuthyRelatedByIdCreation;

    /**
     * @var        Authy
     */
    protected $aAuthyRelatedByIdModification;

    /**
     * @var        PropelObjectCollection|AuthyGroupX[] Collection to store aggregation of AuthyGroupX objects.
     */
    protected $collAuthyGroupxesRelatedByIdAuthyGroup;
    protected $collAuthyGroupxesRelatedByIdAuthyGroupPartial;

    /**
     * @var        PropelObjectCollection|Authy[] Collection to store aggregation of Authy objects.
     */
    protected $collAuthiesRelatedByIdAuthyGroup;
    protected $collAuthiesRelatedByIdAuthyGroupPartial;

    /**
     * @var        PropelObjectCollection|Authy[] Collection to store aggregation of Authy objects.
     */
    protected $collAuthiesRelatedByIdGroupCreation;
    protected $collAuthiesRelatedByIdGroupCreationPartial;

    /**
     * @var        PropelObjectCollection|PushDevice[] Collection to store aggregation of PushDevice objects.
     */
    protected $collPushDevices;
    protected $collPushDevicesPartial;

    /**
     * @var        PropelObjectCollection|GridRun[] Collection to store aggregation of GridRun objects.
     */
    protected $collGridRuns;
    protected $collGridRunsPartial;

    /**
     * @var        PropelObjectCollection|FleetSlot[] Collection to store aggregation of FleetSlot objects.
     */
    protected $collFleetSlots;
    protected $collFleetSlotsPartial;

    /**
     * @var        PropelObjectCollection|RegimeEpisode[] Collection to store aggregation of RegimeEpisode objects.
     */
    protected $collRegimeEpisodes;
    protected $collRegimeEpisodesPartial;

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
     * @var        PropelObjectCollection|SimWallet[] Collection to store aggregation of SimWallet objects.
     */
    protected $collSimWallets;
    protected $collSimWalletsPartial;

    /**
     * @var        PropelObjectCollection|MarketSummary[] Collection to store aggregation of MarketSummary objects.
     */
    protected $collMarketSummaries;
    protected $collMarketSummariesPartial;

    /**
     * @var        PropelObjectCollection|MarketRegime[] Collection to store aggregation of MarketRegime objects.
     */
    protected $collMarketRegimes;
    protected $collMarketRegimesPartial;

    /**
     * @var        PropelObjectCollection|MarketCandle[] Collection to store aggregation of MarketCandle objects.
     */
    protected $collMarketCandles;
    protected $collMarketCandlesPartial;

    /**
     * @var        PropelObjectCollection|BotDecision[] Collection to store aggregation of BotDecision objects.
     */
    protected $collBotDecisions;
    protected $collBotDecisionsPartial;

    /**
     * @var        PropelObjectCollection|MarketOutlook[] Collection to store aggregation of MarketOutlook objects.
     */
    protected $collMarketOutlooks;
    protected $collMarketOutlooksPartial;

    /**
     * @var        PropelObjectCollection|MarketOutlookState[] Collection to store aggregation of MarketOutlookState objects.
     */
    protected $collMarketOutlookStates;
    protected $collMarketOutlookStatesPartial;

    /**
     * @var        PropelObjectCollection|WalletNav[] Collection to store aggregation of WalletNav objects.
     */
    protected $collWalletNavs;
    protected $collWalletNavsPartial;

    /**
     * @var        PropelObjectCollection|AuthyGroup[] Collection to store aggregation of AuthyGroup objects.
     */
    protected $collAuthyGroupsRelatedByIdAuthyGroup;
    protected $collAuthyGroupsRelatedByIdAuthyGroupPartial;

    /**
     * @var        PropelObjectCollection|AuthyGroupX[] Collection to store aggregation of AuthyGroupX objects.
     */
    protected $collAuthyGroupxesRelatedByIdGroupCreation;
    protected $collAuthyGroupxesRelatedByIdGroupCreationPartial;

    /**
     * @var        PropelObjectCollection|Config[] Collection to store aggregation of Config objects.
     */
    protected $collConfigs;
    protected $collConfigsPartial;

    /**
     * @var        PropelObjectCollection|ApiRbac[] Collection to store aggregation of ApiRbac objects.
     */
    protected $collApiRbacs;
    protected $collApiRbacsPartial;

    /**
     * @var        PropelObjectCollection|Template[] Collection to store aggregation of Template objects.
     */
    protected $collTemplates;
    protected $collTemplatesPartial;

    /**
     * @var        PropelObjectCollection|TemplateFile[] Collection to store aggregation of TemplateFile objects.
     */
    protected $collTemplateFiles;
    protected $collTemplateFilesPartial;

    /**
     * @var        PropelObjectCollection|AuthyRefreshToken[] Collection to store aggregation of AuthyRefreshToken objects.
     */
    protected $collAuthyRefreshTokens;
    protected $collAuthyRefreshTokensPartial;

    /**
     * @var        PropelObjectCollection|GridRunAudit[] Collection to store aggregation of GridRunAudit objects.
     */
    protected $collGridRunAudits;
    protected $collGridRunAuditsPartial;

    /**
     * @var        PropelObjectCollection|Country[] Collection to store aggregation of Country objects.
     */
    protected $collCountries;
    protected $collCountriesPartial;

    /**
     * @var        PropelObjectCollection|OauthClient[] Collection to store aggregation of OauthClient objects.
     */
    protected $collOauthClients;
    protected $collOauthClientsPartial;

    /**
     * @var        PropelObjectCollection|OauthAuthCode[] Collection to store aggregation of OauthAuthCode objects.
     */
    protected $collOauthAuthCodes;
    protected $collOauthAuthCodesPartial;

    /**
     * @var        PropelObjectCollection|OauthAccessToken[] Collection to store aggregation of OauthAccessToken objects.
     */
    protected $collOauthAccessTokens;
    protected $collOauthAccessTokensPartial;

    /**
     * @var        PropelObjectCollection|OauthRefreshToken[] Collection to store aggregation of OauthRefreshToken objects.
     */
    protected $collOauthRefreshTokens;
    protected $collOauthRefreshTokensPartial;

    /**
     * @var        PropelObjectCollection|MessageI18n[] Collection to store aggregation of MessageI18n objects.
     */
    protected $collMessageI18ns;
    protected $collMessageI18nsPartial;

    /**
     * @var        PropelObjectCollection|Authy[] Collection to store aggregation of Authy objects.
     */
    protected $collAuthiesRelatedByIdAuthy;

    /**
     * @var        PropelObjectCollection|AuthyGroup[] Collection to store aggregation of AuthyGroup objects.
     */
    protected $collAuthyGroupsRelatedByIdGroupCreation;

    /**
     * @var        PropelObjectCollection|Authy[] Collection to store aggregation of Authy objects.
     */
    protected $collAuthiesRelatedByIdCreation;

    /**
     * @var        PropelObjectCollection|Authy[] Collection to store aggregation of Authy objects.
     */
    protected $collAuthiesRelatedByIdModification;

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

        /** Additive-rights fan-out flag: set in preSave, consumed in postSave. */
        public $gcFanoutRights = false;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $authiesRelatedByIdAuthyScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $authyGroupsRelatedByIdGroupCreationScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $authiesRelatedByIdCreationScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $authiesRelatedByIdModificationScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $authyGroupxesRelatedByIdAuthyGroupScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $authiesRelatedByIdAuthyGroupScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $authiesRelatedByIdGroupCreationScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $pushDevicesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $gridRunsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $fleetSlotsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $regimeEpisodesScheduledForDeletion = null;

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
    protected $simWalletsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $marketSummariesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $marketRegimesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $marketCandlesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $botDecisionsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $marketOutlooksScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $marketOutlookStatesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $walletNavsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $authyGroupsRelatedByIdAuthyGroupScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $authyGroupxesRelatedByIdGroupCreationScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $configsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $apiRbacsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $templatesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $templateFilesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $authyRefreshTokensScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $gridRunAuditsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $countriesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $oauthClientsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $oauthAuthCodesScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $oauthAccessTokensScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $oauthRefreshTokensScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var		PropelObjectCollection
     */
    protected $messageI18nsScheduledForDeletion = null;

    /**
     * @Field()
     * Get the [id_authy_group] column value.
     *
     * @return int
     */
    public function getIdAuthyGroup()
    {

        return $this->id_authy_group;
    }

    /**
     * @Field()
     * Get the [name] column value.
     * Name
     * @return string
     */
    public function getName()
    {

        return $this->name;
    }

    /**
     * @Field()
     * Get the [desc] column value.
     * Description
     * @return string
     */
    public function getDesc()
    {

        return $this->desc;
    }

    /**
     * @Field()
     * Get the [default_group] column value.
     * Default
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getDefaultGroup()
    {
        if (null === $this->default_group) {
            return null;
        }
        $valueSet = AuthyGroupPeer::getValueSet(AuthyGroupPeer::DEFAULT_GROUP);
        if (!isset($valueSet[$this->default_group])) {
            throw new PropelException('Unknown stored enum key: ' . $this->default_group);
        }

        return $valueSet[$this->default_group];
    }

    /**
     * @Field()
     * Get the [admin] column value.
     * Admin
     * @return int
     * @throws PropelException - if the stored enum key is unknown.
     */
    public function getAdmin()
    {
        if (null === $this->admin) {
            return null;
        }
        $valueSet = AuthyGroupPeer::getValueSet(AuthyGroupPeer::ADMIN);
        if (!isset($valueSet[$this->admin])) {
            throw new PropelException('Unknown stored enum key: ' . $this->admin);
        }

        return $valueSet[$this->admin];
    }

    /**
     * @Field()
     * Get the [rights_all] column value.
     * Rights
     * @return string
     */
    public function getRightsAll()
    {

        return $this->rights_all;
    }

    /**
     * @Field()
     * Get the [rights_owner] column value.
     * Rights owner
     * @return string
     */
    public function getRightsOwner()
    {

        return $this->rights_owner;
    }

    /**
     * @Field()
     * Get the [rights_group] column value.
     * Rights group
     * @return string
     */
    public function getRightsGroup()
    {

        return $this->rights_group;
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
     * Set the value of [id_authy_group] column.
     *
     * @param  int $v new value
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setIdAuthyGroup($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_authy_group !== $v) {
            $this->id_authy_group = $v;
            $this->modifiedColumns[] = AuthyGroupPeer::ID_AUTHY_GROUP;
        }


        return $this;
    } // setIdAuthyGroup()

    /**
     * Set the value of [name] column.
     * Name
     * @param  string $v new value
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->name !== $v) {
            $this->name = $v;
            $this->modifiedColumns[] = AuthyGroupPeer::NAME;
        }


        return $this;
    } // setName()

    /**
     * Set the value of [desc] column.
     * Description
     * @param  string $v new value
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setDesc($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->desc !== $v) {
            $this->desc = $v;
            $this->modifiedColumns[] = AuthyGroupPeer::DESC;
        }


        return $this;
    } // setDesc()

    /**
     * Set the value of [default_group] column.
     * Default
     * @param  int $v new value
     * @return AuthyGroup The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setDefaultGroup($v)
    {
        if ($v !== null) {
            $valueSet = AuthyGroupPeer::getValueSet(AuthyGroupPeer::DEFAULT_GROUP);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->default_group !== $v) {
            $this->default_group = $v;
            $this->modifiedColumns[] = AuthyGroupPeer::DEFAULT_GROUP;
        }


        return $this;
    } // setDefaultGroup()

    /**
     * Set the value of [admin] column.
     * Admin
     * @param  int $v new value
     * @return AuthyGroup The current object (for fluent API support)
     * @throws PropelException - if the value is not accepted by this enum.
     */
    public function setAdmin($v)
    {
        if ($v !== null) {
            $valueSet = AuthyGroupPeer::getValueSet(AuthyGroupPeer::ADMIN);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->admin !== $v) {
            $this->admin = $v;
            $this->modifiedColumns[] = AuthyGroupPeer::ADMIN;
        }


        return $this;
    } // setAdmin()

    /**
     * Set the value of [rights_all] column.
     * Rights
     * @param  string $v new value
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setRightsAll($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->rights_all !== $v) {
            $this->rights_all = $v;
            $this->modifiedColumns[] = AuthyGroupPeer::RIGHTS_ALL;
        }


        return $this;
    } // setRightsAll()

    /**
     * Set the value of [rights_owner] column.
     * Rights owner
     * @param  string $v new value
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setRightsOwner($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->rights_owner !== $v) {
            $this->rights_owner = $v;
            $this->modifiedColumns[] = AuthyGroupPeer::RIGHTS_OWNER;
        }


        return $this;
    } // setRightsOwner()

    /**
     * Set the value of [rights_group] column.
     * Rights group
     * @param  string $v new value
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setRightsGroup($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->rights_group !== $v) {
            $this->rights_group = $v;
            $this->modifiedColumns[] = AuthyGroupPeer::RIGHTS_GROUP;
        }


        return $this;
    } // setRightsGroup()

    /**
     * Sets the value of [date_creation] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setDateCreation($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_creation !== null || $dt !== null) {
            $currentDateAsString = ($this->date_creation !== null && $tmpDt = new DateTime($this->date_creation)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_creation = $newDateAsString;
                $this->modifiedColumns[] = AuthyGroupPeer::DATE_CREATION;
            }
        } // if either are not null


        return $this;
    } // setDateCreation()

    /**
     * Sets the value of [date_modification] column to a normalized version of the date/time value specified.
     *
     * @param mixed $v string, integer (timestamp), or DateTime value.
     *               Empty strings are treated as null.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setDateModification($v)
    {
        $dt = PropelDateTime::newInstance($v, null, 'DateTime');
        if ($this->date_modification !== null || $dt !== null) {
            $currentDateAsString = ($this->date_modification !== null && $tmpDt = new DateTime($this->date_modification)) ? $tmpDt->format('Y-m-d H:i:s') : null;
            $newDateAsString = $dt ? $dt->format('Y-m-d H:i:s') : null;
            if ($currentDateAsString !== $newDateAsString) {
                $this->date_modification = $newDateAsString;
                $this->modifiedColumns[] = AuthyGroupPeer::DATE_MODIFICATION;
            }
        } // if either are not null


        return $this;
    } // setDateModification()

    /**
     * Set the value of [id_group_creation] column.
     *
     * @param  int $v new value
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setIdGroupCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_group_creation !== $v) {
            $this->id_group_creation = $v;
            $this->modifiedColumns[] = AuthyGroupPeer::ID_GROUP_CREATION;
        }

        if ($this->aAuthyGroupRelatedByIdGroupCreation !== null && $this->aAuthyGroupRelatedByIdGroupCreation->getIdAuthyGroup() !== $v) {
            $this->aAuthyGroupRelatedByIdGroupCreation = null;
        }


        return $this;
    } // setIdGroupCreation()

    /**
     * Set the value of [id_creation] column.
     *
     * @param  int $v new value
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setIdCreation($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_creation !== $v) {
            $this->id_creation = $v;
            $this->modifiedColumns[] = AuthyGroupPeer::ID_CREATION;
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
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setIdModification($v)
    {
        if ($v !== null && is_numeric($v)) {
            $v = (int) $v;
        }

        if ($this->id_modification !== $v) {
            $this->id_modification = $v;
            $this->modifiedColumns[] = AuthyGroupPeer::ID_MODIFICATION;
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

            $this->id_authy_group = ($row[$startcol + 0] !== null) ? (int) $row[$startcol + 0] : null;
            $this->name = ($row[$startcol + 1] !== null) ? (string) $row[$startcol + 1] : null;
            $this->desc = ($row[$startcol + 2] !== null) ? (string) $row[$startcol + 2] : null;
            $this->default_group = ($row[$startcol + 3] !== null) ? (int) $row[$startcol + 3] : null;
            $this->admin = ($row[$startcol + 4] !== null) ? (int) $row[$startcol + 4] : null;
            $this->rights_all = ($row[$startcol + 5] !== null) ? (string) $row[$startcol + 5] : null;
            $this->rights_owner = ($row[$startcol + 6] !== null) ? (string) $row[$startcol + 6] : null;
            $this->rights_group = ($row[$startcol + 7] !== null) ? (string) $row[$startcol + 7] : null;
            $this->date_creation = ($row[$startcol + 8] !== null) ? (string) $row[$startcol + 8] : null;
            $this->date_modification = ($row[$startcol + 9] !== null) ? (string) $row[$startcol + 9] : null;
            $this->id_group_creation = ($row[$startcol + 10] !== null) ? (int) $row[$startcol + 10] : null;
            $this->id_creation = ($row[$startcol + 11] !== null) ? (int) $row[$startcol + 11] : null;
            $this->id_modification = ($row[$startcol + 12] !== null) ? (int) $row[$startcol + 12] : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }
            $this->postHydrate($row, $startcol, $rehydrate);

            return $startcol + 13; // 13 = AuthyGroupPeer::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException("Error populating AuthyGroup object", $e);
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

        if ($this->aAuthyGroupRelatedByIdGroupCreation !== null && $this->id_group_creation !== $this->aAuthyGroupRelatedByIdGroupCreation->getIdAuthyGroup()) {
            $this->aAuthyGroupRelatedByIdGroupCreation = null;
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
            $con = Propel::getConnection(AuthyGroupPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $stmt = AuthyGroupPeer::doSelectStmt($this->buildPkeyCriteria(), $con);
        $row = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aAuthyGroupRelatedByIdGroupCreation = null;
            $this->aAuthyRelatedByIdCreation = null;
            $this->aAuthyRelatedByIdModification = null;
            $this->collAuthyGroupxesRelatedByIdAuthyGroup = null;

            $this->collAuthiesRelatedByIdAuthyGroup = null;

            $this->collAuthiesRelatedByIdGroupCreation = null;

            $this->collPushDevices = null;

            $this->collGridRuns = null;

            $this->collFleetSlots = null;

            $this->collRegimeEpisodes = null;

            $this->collBotOrders = null;

            $this->collTradeCycles = null;

            $this->collBotEvents = null;

            $this->collBotCommands = null;

            $this->collSimWallets = null;

            $this->collMarketSummaries = null;

            $this->collMarketRegimes = null;

            $this->collMarketCandles = null;

            $this->collBotDecisions = null;

            $this->collMarketOutlooks = null;

            $this->collMarketOutlookStates = null;

            $this->collWalletNavs = null;

            $this->collAuthyGroupsRelatedByIdAuthyGroup = null;

            $this->collAuthyGroupxesRelatedByIdGroupCreation = null;

            $this->collConfigs = null;

            $this->collApiRbacs = null;

            $this->collTemplates = null;

            $this->collTemplateFiles = null;

            $this->collAuthyRefreshTokens = null;

            $this->collGridRunAudits = null;

            $this->collCountries = null;

            $this->collOauthClients = null;

            $this->collOauthAuthCodes = null;

            $this->collOauthAccessTokens = null;

            $this->collOauthRefreshTokens = null;

            $this->collMessageI18ns = null;

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
            $con = Propel::getConnection(AuthyGroupPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        try {
            $deleteQuery = AuthyGroupQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                // GoatCheese behavior

                            if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                                \ApiGoat\Utility\TableVersion::bump('authy_group');
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
            $con = Propel::getConnection(AuthyGroupPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $con->beginTransaction();
        $isInsert = $this->isNew();
        try {
            $ret = $this->preSave($con);
            // GoatCheese behavior

                    if ($this->isColumnModified(\App\AuthyGroupPeer::RIGHTS_ALL)
                        || $this->isColumnModified(\App\AuthyGroupPeer::RIGHTS_OWNER)
                        || $this->isColumnModified(\App\AuthyGroupPeer::RIGHTS_GROUP)
                        || $this->isColumnModified(\App\AuthyGroupPeer::ADMIN)) {
                        $this->gcFanoutRights = true;
                    }
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
                                \ApiGoat\Utility\TableVersion::bump('authy_group');
                            }
                            if (!empty($this->gcFanoutRights)) {
                                $this->gcFanoutRights = false;
                                $gcAffected = array();
                                foreach (\App\AuthyQuery::create()->filterByIdAuthyGroup($this->getIdAuthyGroup())->select(array('IdAuthy'))->find() as $gcId) {
                                    $gcAffected[$gcId] = true;
                                }
                                foreach (\App\AuthyGroupXQuery::create()->filterByIdAuthyGroup($this->getIdAuthyGroup())->select(array('IdAuthy'))->find() as $gcId) {
                                    $gcAffected[$gcId] = true;
                                }
                                foreach (array_keys($gcAffected) as $gcIdAuthy) {
                                    \ApiGoat\Model\Authy::recomputeRightsFromGroups($gcIdAuthy);
                                }
                            }
                AuthyGroupPeer::addInstanceToPool($this);
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

            if ($this->aAuthyGroupRelatedByIdGroupCreation !== null) {
                if ($this->aAuthyGroupRelatedByIdGroupCreation->isModified() || $this->aAuthyGroupRelatedByIdGroupCreation->isNew()) {
                    $affectedRows += $this->aAuthyGroupRelatedByIdGroupCreation->save($con);
                }
                $this->setAuthyGroupRelatedByIdGroupCreation($this->aAuthyGroupRelatedByIdGroupCreation);
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

            if ($this->authyGroupxesRelatedByIdAuthyGroupScheduledForDeletion !== null) {
                if (!$this->authyGroupxesRelatedByIdAuthyGroupScheduledForDeletion->isEmpty()) {
                    AuthyGroupXQuery::create()
                        ->filterByPrimaryKeys($this->authyGroupxesRelatedByIdAuthyGroupScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->authyGroupxesRelatedByIdAuthyGroupScheduledForDeletion = null;
                }
            }

            if ($this->collAuthyGroupxesRelatedByIdAuthyGroup !== null) {
                foreach ($this->collAuthyGroupxesRelatedByIdAuthyGroup as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->authiesRelatedByIdAuthyGroupScheduledForDeletion !== null) {
                if (!$this->authiesRelatedByIdAuthyGroupScheduledForDeletion->isEmpty()) {
                    AuthyQuery::create()
                        ->filterByPrimaryKeys($this->authiesRelatedByIdAuthyGroupScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->authiesRelatedByIdAuthyGroupScheduledForDeletion = null;
                }
            }

            if ($this->collAuthiesRelatedByIdAuthyGroup !== null) {
                foreach ($this->collAuthiesRelatedByIdAuthyGroup as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->authiesRelatedByIdGroupCreationScheduledForDeletion !== null) {
                if (!$this->authiesRelatedByIdGroupCreationScheduledForDeletion->isEmpty()) {
                    foreach ($this->authiesRelatedByIdGroupCreationScheduledForDeletion as $authyRelatedByIdGroupCreation) {
                        // need to save related object because we set the relation to null
                        $authyRelatedByIdGroupCreation->save($con);
                    }
                    $this->authiesRelatedByIdGroupCreationScheduledForDeletion = null;
                }
            }

            if ($this->collAuthiesRelatedByIdGroupCreation !== null) {
                foreach ($this->collAuthiesRelatedByIdGroupCreation as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->pushDevicesScheduledForDeletion !== null) {
                if (!$this->pushDevicesScheduledForDeletion->isEmpty()) {
                    foreach ($this->pushDevicesScheduledForDeletion as $pushDevice) {
                        // need to save related object because we set the relation to null
                        $pushDevice->save($con);
                    }
                    $this->pushDevicesScheduledForDeletion = null;
                }
            }

            if ($this->collPushDevices !== null) {
                foreach ($this->collPushDevices as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->gridRunsScheduledForDeletion !== null) {
                if (!$this->gridRunsScheduledForDeletion->isEmpty()) {
                    foreach ($this->gridRunsScheduledForDeletion as $gridRun) {
                        // need to save related object because we set the relation to null
                        $gridRun->save($con);
                    }
                    $this->gridRunsScheduledForDeletion = null;
                }
            }

            if ($this->collGridRuns !== null) {
                foreach ($this->collGridRuns as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->fleetSlotsScheduledForDeletion !== null) {
                if (!$this->fleetSlotsScheduledForDeletion->isEmpty()) {
                    foreach ($this->fleetSlotsScheduledForDeletion as $fleetSlot) {
                        // need to save related object because we set the relation to null
                        $fleetSlot->save($con);
                    }
                    $this->fleetSlotsScheduledForDeletion = null;
                }
            }

            if ($this->collFleetSlots !== null) {
                foreach ($this->collFleetSlots as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
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

            if ($this->botOrdersScheduledForDeletion !== null) {
                if (!$this->botOrdersScheduledForDeletion->isEmpty()) {
                    foreach ($this->botOrdersScheduledForDeletion as $botOrder) {
                        // need to save related object because we set the relation to null
                        $botOrder->save($con);
                    }
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
                    foreach ($this->tradeCyclesScheduledForDeletion as $tradeCycle) {
                        // need to save related object because we set the relation to null
                        $tradeCycle->save($con);
                    }
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
                    foreach ($this->botEventsScheduledForDeletion as $botEvent) {
                        // need to save related object because we set the relation to null
                        $botEvent->save($con);
                    }
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
                    foreach ($this->botCommandsScheduledForDeletion as $botCommand) {
                        // need to save related object because we set the relation to null
                        $botCommand->save($con);
                    }
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

            if ($this->simWalletsScheduledForDeletion !== null) {
                if (!$this->simWalletsScheduledForDeletion->isEmpty()) {
                    foreach ($this->simWalletsScheduledForDeletion as $simWallet) {
                        // need to save related object because we set the relation to null
                        $simWallet->save($con);
                    }
                    $this->simWalletsScheduledForDeletion = null;
                }
            }

            if ($this->collSimWallets !== null) {
                foreach ($this->collSimWallets as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->marketSummariesScheduledForDeletion !== null) {
                if (!$this->marketSummariesScheduledForDeletion->isEmpty()) {
                    foreach ($this->marketSummariesScheduledForDeletion as $marketSummary) {
                        // need to save related object because we set the relation to null
                        $marketSummary->save($con);
                    }
                    $this->marketSummariesScheduledForDeletion = null;
                }
            }

            if ($this->collMarketSummaries !== null) {
                foreach ($this->collMarketSummaries as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->marketRegimesScheduledForDeletion !== null) {
                if (!$this->marketRegimesScheduledForDeletion->isEmpty()) {
                    foreach ($this->marketRegimesScheduledForDeletion as $marketRegime) {
                        // need to save related object because we set the relation to null
                        $marketRegime->save($con);
                    }
                    $this->marketRegimesScheduledForDeletion = null;
                }
            }

            if ($this->collMarketRegimes !== null) {
                foreach ($this->collMarketRegimes as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->marketCandlesScheduledForDeletion !== null) {
                if (!$this->marketCandlesScheduledForDeletion->isEmpty()) {
                    foreach ($this->marketCandlesScheduledForDeletion as $marketCandle) {
                        // need to save related object because we set the relation to null
                        $marketCandle->save($con);
                    }
                    $this->marketCandlesScheduledForDeletion = null;
                }
            }

            if ($this->collMarketCandles !== null) {
                foreach ($this->collMarketCandles as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->botDecisionsScheduledForDeletion !== null) {
                if (!$this->botDecisionsScheduledForDeletion->isEmpty()) {
                    foreach ($this->botDecisionsScheduledForDeletion as $botDecision) {
                        // need to save related object because we set the relation to null
                        $botDecision->save($con);
                    }
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

            if ($this->marketOutlooksScheduledForDeletion !== null) {
                if (!$this->marketOutlooksScheduledForDeletion->isEmpty()) {
                    foreach ($this->marketOutlooksScheduledForDeletion as $marketOutlook) {
                        // need to save related object because we set the relation to null
                        $marketOutlook->save($con);
                    }
                    $this->marketOutlooksScheduledForDeletion = null;
                }
            }

            if ($this->collMarketOutlooks !== null) {
                foreach ($this->collMarketOutlooks as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->marketOutlookStatesScheduledForDeletion !== null) {
                if (!$this->marketOutlookStatesScheduledForDeletion->isEmpty()) {
                    foreach ($this->marketOutlookStatesScheduledForDeletion as $marketOutlookState) {
                        // need to save related object because we set the relation to null
                        $marketOutlookState->save($con);
                    }
                    $this->marketOutlookStatesScheduledForDeletion = null;
                }
            }

            if ($this->collMarketOutlookStates !== null) {
                foreach ($this->collMarketOutlookStates as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->walletNavsScheduledForDeletion !== null) {
                if (!$this->walletNavsScheduledForDeletion->isEmpty()) {
                    foreach ($this->walletNavsScheduledForDeletion as $walletNav) {
                        // need to save related object because we set the relation to null
                        $walletNav->save($con);
                    }
                    $this->walletNavsScheduledForDeletion = null;
                }
            }

            if ($this->collWalletNavs !== null) {
                foreach ($this->collWalletNavs as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->authyGroupsRelatedByIdAuthyGroupScheduledForDeletion !== null) {
                if (!$this->authyGroupsRelatedByIdAuthyGroupScheduledForDeletion->isEmpty()) {
                    foreach ($this->authyGroupsRelatedByIdAuthyGroupScheduledForDeletion as $authyGroupRelatedByIdAuthyGroup) {
                        // need to save related object because we set the relation to null
                        $authyGroupRelatedByIdAuthyGroup->save($con);
                    }
                    $this->authyGroupsRelatedByIdAuthyGroupScheduledForDeletion = null;
                }
            }

            if ($this->collAuthyGroupsRelatedByIdAuthyGroup !== null) {
                foreach ($this->collAuthyGroupsRelatedByIdAuthyGroup as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->authyGroupxesRelatedByIdGroupCreationScheduledForDeletion !== null) {
                if (!$this->authyGroupxesRelatedByIdGroupCreationScheduledForDeletion->isEmpty()) {
                    foreach ($this->authyGroupxesRelatedByIdGroupCreationScheduledForDeletion as $authyGroupXRelatedByIdGroupCreation) {
                        // need to save related object because we set the relation to null
                        $authyGroupXRelatedByIdGroupCreation->save($con);
                    }
                    $this->authyGroupxesRelatedByIdGroupCreationScheduledForDeletion = null;
                }
            }

            if ($this->collAuthyGroupxesRelatedByIdGroupCreation !== null) {
                foreach ($this->collAuthyGroupxesRelatedByIdGroupCreation as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->configsScheduledForDeletion !== null) {
                if (!$this->configsScheduledForDeletion->isEmpty()) {
                    foreach ($this->configsScheduledForDeletion as $config) {
                        // need to save related object because we set the relation to null
                        $config->save($con);
                    }
                    $this->configsScheduledForDeletion = null;
                }
            }

            if ($this->collConfigs !== null) {
                foreach ($this->collConfigs as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->apiRbacsScheduledForDeletion !== null) {
                if (!$this->apiRbacsScheduledForDeletion->isEmpty()) {
                    foreach ($this->apiRbacsScheduledForDeletion as $apiRbac) {
                        // need to save related object because we set the relation to null
                        $apiRbac->save($con);
                    }
                    $this->apiRbacsScheduledForDeletion = null;
                }
            }

            if ($this->collApiRbacs !== null) {
                foreach ($this->collApiRbacs as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->templatesScheduledForDeletion !== null) {
                if (!$this->templatesScheduledForDeletion->isEmpty()) {
                    foreach ($this->templatesScheduledForDeletion as $template) {
                        // need to save related object because we set the relation to null
                        $template->save($con);
                    }
                    $this->templatesScheduledForDeletion = null;
                }
            }

            if ($this->collTemplates !== null) {
                foreach ($this->collTemplates as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->templateFilesScheduledForDeletion !== null) {
                if (!$this->templateFilesScheduledForDeletion->isEmpty()) {
                    foreach ($this->templateFilesScheduledForDeletion as $templateFile) {
                        // need to save related object because we set the relation to null
                        $templateFile->save($con);
                    }
                    $this->templateFilesScheduledForDeletion = null;
                }
            }

            if ($this->collTemplateFiles !== null) {
                foreach ($this->collTemplateFiles as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->authyRefreshTokensScheduledForDeletion !== null) {
                if (!$this->authyRefreshTokensScheduledForDeletion->isEmpty()) {
                    foreach ($this->authyRefreshTokensScheduledForDeletion as $authyRefreshToken) {
                        // need to save related object because we set the relation to null
                        $authyRefreshToken->save($con);
                    }
                    $this->authyRefreshTokensScheduledForDeletion = null;
                }
            }

            if ($this->collAuthyRefreshTokens !== null) {
                foreach ($this->collAuthyRefreshTokens as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->gridRunAuditsScheduledForDeletion !== null) {
                if (!$this->gridRunAuditsScheduledForDeletion->isEmpty()) {
                    foreach ($this->gridRunAuditsScheduledForDeletion as $gridRunAudit) {
                        // need to save related object because we set the relation to null
                        $gridRunAudit->save($con);
                    }
                    $this->gridRunAuditsScheduledForDeletion = null;
                }
            }

            if ($this->collGridRunAudits !== null) {
                foreach ($this->collGridRunAudits as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->countriesScheduledForDeletion !== null) {
                if (!$this->countriesScheduledForDeletion->isEmpty()) {
                    foreach ($this->countriesScheduledForDeletion as $country) {
                        // need to save related object because we set the relation to null
                        $country->save($con);
                    }
                    $this->countriesScheduledForDeletion = null;
                }
            }

            if ($this->collCountries !== null) {
                foreach ($this->collCountries as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->oauthClientsScheduledForDeletion !== null) {
                if (!$this->oauthClientsScheduledForDeletion->isEmpty()) {
                    foreach ($this->oauthClientsScheduledForDeletion as $oauthClient) {
                        // need to save related object because we set the relation to null
                        $oauthClient->save($con);
                    }
                    $this->oauthClientsScheduledForDeletion = null;
                }
            }

            if ($this->collOauthClients !== null) {
                foreach ($this->collOauthClients as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->oauthAuthCodesScheduledForDeletion !== null) {
                if (!$this->oauthAuthCodesScheduledForDeletion->isEmpty()) {
                    foreach ($this->oauthAuthCodesScheduledForDeletion as $oauthAuthCode) {
                        // need to save related object because we set the relation to null
                        $oauthAuthCode->save($con);
                    }
                    $this->oauthAuthCodesScheduledForDeletion = null;
                }
            }

            if ($this->collOauthAuthCodes !== null) {
                foreach ($this->collOauthAuthCodes as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->oauthAccessTokensScheduledForDeletion !== null) {
                if (!$this->oauthAccessTokensScheduledForDeletion->isEmpty()) {
                    foreach ($this->oauthAccessTokensScheduledForDeletion as $oauthAccessToken) {
                        // need to save related object because we set the relation to null
                        $oauthAccessToken->save($con);
                    }
                    $this->oauthAccessTokensScheduledForDeletion = null;
                }
            }

            if ($this->collOauthAccessTokens !== null) {
                foreach ($this->collOauthAccessTokens as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->oauthRefreshTokensScheduledForDeletion !== null) {
                if (!$this->oauthRefreshTokensScheduledForDeletion->isEmpty()) {
                    foreach ($this->oauthRefreshTokensScheduledForDeletion as $oauthRefreshToken) {
                        // need to save related object because we set the relation to null
                        $oauthRefreshToken->save($con);
                    }
                    $this->oauthRefreshTokensScheduledForDeletion = null;
                }
            }

            if ($this->collOauthRefreshTokens !== null) {
                foreach ($this->collOauthRefreshTokens as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->messageI18nsScheduledForDeletion !== null) {
                if (!$this->messageI18nsScheduledForDeletion->isEmpty()) {
                    foreach ($this->messageI18nsScheduledForDeletion as $messageI18n) {
                        // need to save related object because we set the relation to null
                        $messageI18n->save($con);
                    }
                    $this->messageI18nsScheduledForDeletion = null;
                }
            }

            if ($this->collMessageI18ns !== null) {
                foreach ($this->collMessageI18ns as $referrerFK) {
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

        $this->modifiedColumns[] = AuthyGroupPeer::ID_AUTHY_GROUP;
        if (null !== $this->id_authy_group) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . AuthyGroupPeer::ID_AUTHY_GROUP . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(AuthyGroupPeer::ID_AUTHY_GROUP)) {
            $modifiedColumns[':p' . $index++]  = '`id_authy_group`';
        }
        if ($this->isColumnModified(AuthyGroupPeer::NAME)) {
            $modifiedColumns[':p' . $index++]  = '`name`';
        }
        if ($this->isColumnModified(AuthyGroupPeer::DESC)) {
            $modifiedColumns[':p' . $index++]  = '`desc`';
        }
        if ($this->isColumnModified(AuthyGroupPeer::DEFAULT_GROUP)) {
            $modifiedColumns[':p' . $index++]  = '`default_group`';
        }
        if ($this->isColumnModified(AuthyGroupPeer::ADMIN)) {
            $modifiedColumns[':p' . $index++]  = '`admin`';
        }
        if ($this->isColumnModified(AuthyGroupPeer::RIGHTS_ALL)) {
            $modifiedColumns[':p' . $index++]  = '`rights_all`';
        }
        if ($this->isColumnModified(AuthyGroupPeer::RIGHTS_OWNER)) {
            $modifiedColumns[':p' . $index++]  = '`rights_owner`';
        }
        if ($this->isColumnModified(AuthyGroupPeer::RIGHTS_GROUP)) {
            $modifiedColumns[':p' . $index++]  = '`rights_group`';
        }
        if ($this->isColumnModified(AuthyGroupPeer::DATE_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_creation`';
        }
        if ($this->isColumnModified(AuthyGroupPeer::DATE_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`date_modification`';
        }
        if ($this->isColumnModified(AuthyGroupPeer::ID_GROUP_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_group_creation`';
        }
        if ($this->isColumnModified(AuthyGroupPeer::ID_CREATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_creation`';
        }
        if ($this->isColumnModified(AuthyGroupPeer::ID_MODIFICATION)) {
            $modifiedColumns[':p' . $index++]  = '`id_modification`';
        }

        $sql = sprintf(
            'INSERT INTO `authy_group` (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case '`id_authy_group`':
                        $stmt->bindValue($identifier, $this->id_authy_group, PDO::PARAM_INT);
                        break;
                    case '`name`':
                        $stmt->bindValue($identifier, $this->name, PDO::PARAM_STR);
                        break;
                    case '`desc`':
                        $stmt->bindValue($identifier, $this->desc, PDO::PARAM_STR);
                        break;
                    case '`default_group`':
                        $stmt->bindValue($identifier, $this->default_group, PDO::PARAM_INT);
                        break;
                    case '`admin`':
                        $stmt->bindValue($identifier, $this->admin, PDO::PARAM_INT);
                        break;
                    case '`rights_all`':
                        $stmt->bindValue($identifier, $this->rights_all, PDO::PARAM_STR);
                        break;
                    case '`rights_owner`':
                        $stmt->bindValue($identifier, $this->rights_owner, PDO::PARAM_STR);
                        break;
                    case '`rights_group`':
                        $stmt->bindValue($identifier, $this->rights_group, PDO::PARAM_STR);
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
        $this->setIdAuthyGroup($pk);

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

            if ($this->aAuthyGroupRelatedByIdGroupCreation !== null) {
                if (!$this->aAuthyGroupRelatedByIdGroupCreation->validate($columns)) {
                    $failureMap = array_merge($failureMap, $this->aAuthyGroupRelatedByIdGroupCreation->getValidationFailures());
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


            if (($retval = AuthyGroupPeer::doValidate($this, $columns)) !== true) {
                $failureMap = array_merge($failureMap, $retval);
            }


                if ($this->collAuthyGroupxesRelatedByIdAuthyGroup !== null) {
                    foreach ($this->collAuthyGroupxesRelatedByIdAuthyGroup as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collAuthiesRelatedByIdAuthyGroup !== null) {
                    foreach ($this->collAuthiesRelatedByIdAuthyGroup as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collAuthiesRelatedByIdGroupCreation !== null) {
                    foreach ($this->collAuthiesRelatedByIdGroupCreation as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collPushDevices !== null) {
                    foreach ($this->collPushDevices as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collGridRuns !== null) {
                    foreach ($this->collGridRuns as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collFleetSlots !== null) {
                    foreach ($this->collFleetSlots as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collRegimeEpisodes !== null) {
                    foreach ($this->collRegimeEpisodes as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
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

                if ($this->collSimWallets !== null) {
                    foreach ($this->collSimWallets as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collMarketSummaries !== null) {
                    foreach ($this->collMarketSummaries as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collMarketRegimes !== null) {
                    foreach ($this->collMarketRegimes as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collMarketCandles !== null) {
                    foreach ($this->collMarketCandles as $referrerFK) {
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

                if ($this->collMarketOutlooks !== null) {
                    foreach ($this->collMarketOutlooks as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collMarketOutlookStates !== null) {
                    foreach ($this->collMarketOutlookStates as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collWalletNavs !== null) {
                    foreach ($this->collWalletNavs as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collAuthyGroupsRelatedByIdAuthyGroup !== null) {
                    foreach ($this->collAuthyGroupsRelatedByIdAuthyGroup as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collAuthyGroupxesRelatedByIdGroupCreation !== null) {
                    foreach ($this->collAuthyGroupxesRelatedByIdGroupCreation as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collConfigs !== null) {
                    foreach ($this->collConfigs as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collApiRbacs !== null) {
                    foreach ($this->collApiRbacs as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collTemplates !== null) {
                    foreach ($this->collTemplates as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collTemplateFiles !== null) {
                    foreach ($this->collTemplateFiles as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collAuthyRefreshTokens !== null) {
                    foreach ($this->collAuthyRefreshTokens as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collGridRunAudits !== null) {
                    foreach ($this->collGridRunAudits as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collCountries !== null) {
                    foreach ($this->collCountries as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collOauthClients !== null) {
                    foreach ($this->collOauthClients as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collOauthAuthCodes !== null) {
                    foreach ($this->collOauthAuthCodes as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collOauthAccessTokens !== null) {
                    foreach ($this->collOauthAccessTokens as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collOauthRefreshTokens !== null) {
                    foreach ($this->collOauthRefreshTokens as $referrerFK) {
                        if (!$referrerFK->validate($columns)) {
                            $failureMap = array_merge($failureMap, $referrerFK->getValidationFailures());
                        }
                    }
                }

                if ($this->collMessageI18ns !== null) {
                    foreach ($this->collMessageI18ns as $referrerFK) {
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
        $pos = AuthyGroupPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);
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
        if (isset($alreadyDumpedObjects['AuthyGroup'][$this->getPrimaryKey()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['AuthyGroup'][$this->getPrimaryKey()] = true;
        $keys = AuthyGroupPeer::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getIdAuthyGroup(),
            $keys[1] => $this->getName(),
            $keys[2] => $this->getDesc(),
            $keys[3] => $this->getDefaultGroup(),
            $keys[4] => $this->getAdmin(),
            $keys[5] => $this->getRightsAll(),
            $keys[6] => $this->getRightsOwner(),
            $keys[7] => $this->getRightsGroup(),
            $keys[8] => $this->getDateCreation(),
            $keys[9] => $this->getDateModification(),
            $keys[10] => $this->getIdGroupCreation(),
            $keys[11] => $this->getIdCreation(),
            $keys[12] => $this->getIdModification(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aAuthyGroupRelatedByIdGroupCreation) {
                $result['AuthyGroupRelatedByIdGroupCreation'] = $this->aAuthyGroupRelatedByIdGroupCreation->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aAuthyRelatedByIdCreation) {
                $result['AuthyRelatedByIdCreation'] = $this->aAuthyRelatedByIdCreation->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aAuthyRelatedByIdModification) {
                $result['AuthyRelatedByIdModification'] = $this->aAuthyRelatedByIdModification->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->collAuthyGroupxesRelatedByIdAuthyGroup) {
                $result['AuthyGroupxesRelatedByIdAuthyGroup'] = $this->collAuthyGroupxesRelatedByIdAuthyGroup->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collAuthiesRelatedByIdAuthyGroup) {
                $result['AuthiesRelatedByIdAuthyGroup'] = $this->collAuthiesRelatedByIdAuthyGroup->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collAuthiesRelatedByIdGroupCreation) {
                $result['AuthiesRelatedByIdGroupCreation'] = $this->collAuthiesRelatedByIdGroupCreation->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collPushDevices) {
                $result['PushDevices'] = $this->collPushDevices->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collGridRuns) {
                $result['GridRuns'] = $this->collGridRuns->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collFleetSlots) {
                $result['FleetSlots'] = $this->collFleetSlots->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collRegimeEpisodes) {
                $result['RegimeEpisodes'] = $this->collRegimeEpisodes->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
            if (null !== $this->collSimWallets) {
                $result['SimWallets'] = $this->collSimWallets->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collMarketSummaries) {
                $result['MarketSummaries'] = $this->collMarketSummaries->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collMarketRegimes) {
                $result['MarketRegimes'] = $this->collMarketRegimes->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collMarketCandles) {
                $result['MarketCandles'] = $this->collMarketCandles->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collBotDecisions) {
                $result['BotDecisions'] = $this->collBotDecisions->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collMarketOutlooks) {
                $result['MarketOutlooks'] = $this->collMarketOutlooks->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collMarketOutlookStates) {
                $result['MarketOutlookStates'] = $this->collMarketOutlookStates->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collWalletNavs) {
                $result['WalletNavs'] = $this->collWalletNavs->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collAuthyGroupsRelatedByIdAuthyGroup) {
                $result['AuthyGroupsRelatedByIdAuthyGroup'] = $this->collAuthyGroupsRelatedByIdAuthyGroup->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collAuthyGroupxesRelatedByIdGroupCreation) {
                $result['AuthyGroupxesRelatedByIdGroupCreation'] = $this->collAuthyGroupxesRelatedByIdGroupCreation->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collConfigs) {
                $result['Configs'] = $this->collConfigs->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collApiRbacs) {
                $result['ApiRbacs'] = $this->collApiRbacs->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collTemplates) {
                $result['Templates'] = $this->collTemplates->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collTemplateFiles) {
                $result['TemplateFiles'] = $this->collTemplateFiles->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collAuthyRefreshTokens) {
                $result['AuthyRefreshTokens'] = $this->collAuthyRefreshTokens->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collGridRunAudits) {
                $result['GridRunAudits'] = $this->collGridRunAudits->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collCountries) {
                $result['Countries'] = $this->collCountries->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collOauthClients) {
                $result['OauthClients'] = $this->collOauthClients->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collOauthAuthCodes) {
                $result['OauthAuthCodes'] = $this->collOauthAuthCodes->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collOauthAccessTokens) {
                $result['OauthAccessTokens'] = $this->collOauthAccessTokens->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collOauthRefreshTokens) {
                $result['OauthRefreshTokens'] = $this->collOauthRefreshTokens->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collMessageI18ns) {
                $result['MessageI18ns'] = $this->collMessageI18ns->toArray(null, true, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
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
        $pos = AuthyGroupPeer::translateFieldName($name, $type, BasePeer::TYPE_NUM);

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
                $this->setIdAuthyGroup($value);
                break;
            case 1:
                $this->setName($value);
                break;
            case 2:
                $this->setDesc($value);
                break;
            case 3:
                $valueSet = AuthyGroupPeer::getValueSet(AuthyGroupPeer::DEFAULT_GROUP);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setDefaultGroup($value);
                break;
            case 4:
                $valueSet = AuthyGroupPeer::getValueSet(AuthyGroupPeer::ADMIN);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setAdmin($value);
                break;
            case 5:
                $this->setRightsAll($value);
                break;
            case 6:
                $this->setRightsOwner($value);
                break;
            case 7:
                $this->setRightsGroup($value);
                break;
            case 8:
                $this->setDateCreation($value);
                break;
            case 9:
                $this->setDateModification($value);
                break;
            case 10:
                $this->setIdGroupCreation($value);
                break;
            case 11:
                $this->setIdCreation($value);
                break;
            case 12:
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
        $keys = AuthyGroupPeer::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) $this->setIdAuthyGroup($arr[$keys[0]]);
        if (array_key_exists($keys[1], $arr)) $this->setName($arr[$keys[1]]);
        if (array_key_exists($keys[2], $arr)) $this->setDesc($arr[$keys[2]]);
        if (array_key_exists($keys[3], $arr)) $this->setDefaultGroup($arr[$keys[3]]);
        if (array_key_exists($keys[4], $arr)) $this->setAdmin($arr[$keys[4]]);
        if (array_key_exists($keys[5], $arr)) $this->setRightsAll($arr[$keys[5]]);
        if (array_key_exists($keys[6], $arr)) $this->setRightsOwner($arr[$keys[6]]);
        if (array_key_exists($keys[7], $arr)) $this->setRightsGroup($arr[$keys[7]]);
        if (array_key_exists($keys[8], $arr)) $this->setDateCreation($arr[$keys[8]]);
        if (array_key_exists($keys[9], $arr)) $this->setDateModification($arr[$keys[9]]);
        if (array_key_exists($keys[10], $arr)) $this->setIdGroupCreation($arr[$keys[10]]);
        if (array_key_exists($keys[11], $arr)) $this->setIdCreation($arr[$keys[11]]);
        if (array_key_exists($keys[12], $arr)) $this->setIdModification($arr[$keys[12]]);
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(AuthyGroupPeer::DATABASE_NAME);

        if ($this->isColumnModified(AuthyGroupPeer::ID_AUTHY_GROUP)) $criteria->add(AuthyGroupPeer::ID_AUTHY_GROUP, $this->id_authy_group);
        if ($this->isColumnModified(AuthyGroupPeer::NAME)) $criteria->add(AuthyGroupPeer::NAME, $this->name);
        if ($this->isColumnModified(AuthyGroupPeer::DESC)) $criteria->add(AuthyGroupPeer::DESC, $this->desc);
        if ($this->isColumnModified(AuthyGroupPeer::DEFAULT_GROUP)) $criteria->add(AuthyGroupPeer::DEFAULT_GROUP, $this->default_group);
        if ($this->isColumnModified(AuthyGroupPeer::ADMIN)) $criteria->add(AuthyGroupPeer::ADMIN, $this->admin);
        if ($this->isColumnModified(AuthyGroupPeer::RIGHTS_ALL)) $criteria->add(AuthyGroupPeer::RIGHTS_ALL, $this->rights_all);
        if ($this->isColumnModified(AuthyGroupPeer::RIGHTS_OWNER)) $criteria->add(AuthyGroupPeer::RIGHTS_OWNER, $this->rights_owner);
        if ($this->isColumnModified(AuthyGroupPeer::RIGHTS_GROUP)) $criteria->add(AuthyGroupPeer::RIGHTS_GROUP, $this->rights_group);
        if ($this->isColumnModified(AuthyGroupPeer::DATE_CREATION)) $criteria->add(AuthyGroupPeer::DATE_CREATION, $this->date_creation);
        if ($this->isColumnModified(AuthyGroupPeer::DATE_MODIFICATION)) $criteria->add(AuthyGroupPeer::DATE_MODIFICATION, $this->date_modification);
        if ($this->isColumnModified(AuthyGroupPeer::ID_GROUP_CREATION)) $criteria->add(AuthyGroupPeer::ID_GROUP_CREATION, $this->id_group_creation);
        if ($this->isColumnModified(AuthyGroupPeer::ID_CREATION)) $criteria->add(AuthyGroupPeer::ID_CREATION, $this->id_creation);
        if ($this->isColumnModified(AuthyGroupPeer::ID_MODIFICATION)) $criteria->add(AuthyGroupPeer::ID_MODIFICATION, $this->id_modification);

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
        $criteria = new Criteria(AuthyGroupPeer::DATABASE_NAME);
        $criteria->add(AuthyGroupPeer::ID_AUTHY_GROUP, $this->id_authy_group);

        return $criteria;
    }

    /**
     * Returns the primary key for this object (row).
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getIdAuthyGroup();
    }

    /**
     * Generic method to set the primary key (id_authy_group column).
     *
     * @param  int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setIdAuthyGroup($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {

        return null === $this->getIdAuthyGroup();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param object $copyObj An object of AuthyGroup (or compatible) type.
     * @param boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setName($this->getName());
        $copyObj->setDesc($this->getDesc());
        $copyObj->setDefaultGroup($this->getDefaultGroup());
        $copyObj->setAdmin($this->getAdmin());
        $copyObj->setRightsAll($this->getRightsAll());
        $copyObj->setRightsOwner($this->getRightsOwner());
        $copyObj->setRightsGroup($this->getRightsGroup());
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

            foreach ($this->getAuthyGroupxesRelatedByIdAuthyGroup() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addAuthyGroupXRelatedByIdAuthyGroup($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getAuthiesRelatedByIdAuthyGroup() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addAuthyRelatedByIdAuthyGroup($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getAuthiesRelatedByIdGroupCreation() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addAuthyRelatedByIdGroupCreation($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getPushDevices() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addPushDevice($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getGridRuns() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addGridRun($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getFleetSlots() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addFleetSlot($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getRegimeEpisodes() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addRegimeEpisode($relObj->copy($deepCopy));
                }
            }

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

            foreach ($this->getSimWallets() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addSimWallet($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getMarketSummaries() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addMarketSummary($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getMarketRegimes() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addMarketRegime($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getMarketCandles() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addMarketCandle($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getBotDecisions() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addBotDecision($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getMarketOutlooks() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addMarketOutlook($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getMarketOutlookStates() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addMarketOutlookState($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getWalletNavs() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addWalletNav($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getAuthyGroupsRelatedByIdAuthyGroup() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addAuthyGroupRelatedByIdAuthyGroup($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getAuthyGroupxesRelatedByIdGroupCreation() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addAuthyGroupXRelatedByIdGroupCreation($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getConfigs() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addConfig($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getApiRbacs() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addApiRbac($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getTemplates() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addTemplate($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getTemplateFiles() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addTemplateFile($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getAuthyRefreshTokens() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addAuthyRefreshToken($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getGridRunAudits() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addGridRunAudit($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getCountries() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addCountry($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getOauthClients() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addOauthClient($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getOauthAuthCodes() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addOauthAuthCode($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getOauthAccessTokens() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addOauthAccessToken($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getOauthRefreshTokens() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addOauthRefreshToken($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getMessageI18ns() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addMessageI18n($relObj->copy($deepCopy));
                }
            }

            //unflag object copy
            $this->startCopy = false;
        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setIdAuthyGroup(NULL); // this is a auto-increment column, so set to default value
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
     * @return AuthyGroup Clone of current object.
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
     * @return AuthyGroupPeer
     */
    public function getPeer()
    {
        if (self::$peer === null) {
            self::$peer = new AuthyGroupPeer();
        }

        return self::$peer;
    }

    /**
     * Declares an association between this object and a AuthyGroup object.
     *
     * @param                  AuthyGroup $v
     * @return AuthyGroup The current object (for fluent API support)
     * @throws PropelException
     */
    public function setAuthyGroupRelatedByIdGroupCreation(?AuthyGroup $v = null)
    {
        if ($v === null) {
            $this->setIdGroupCreation(NULL);
        } else {
            $this->setIdGroupCreation($v->getIdAuthyGroup());
        }

        $this->aAuthyGroupRelatedByIdGroupCreation = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the AuthyGroup object, it will not be re-added.
        if ($v !== null) {
            $v->addAuthyGroupRelatedByIdAuthyGroup($this);
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
    public function getAuthyGroupRelatedByIdGroupCreation(?PropelPDO $con = null, $doQuery = true)
    {
        if ($this->aAuthyGroupRelatedByIdGroupCreation === null && ($this->id_group_creation !== null) && $doQuery) {
            $this->aAuthyGroupRelatedByIdGroupCreation = AuthyGroupQuery::create()->findPk($this->id_group_creation, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aAuthyGroupRelatedByIdGroupCreation->addAuthyGroupsRelatedByIdAuthyGroup($this);
             */
        }

        return $this->aAuthyGroupRelatedByIdGroupCreation;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return AuthyGroup The current object (for fluent API support)
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
            $v->addAuthyGroupRelatedByIdCreation($this);
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
                $this->aAuthyRelatedByIdCreation->addAuthyGroupsRelatedByIdCreation($this);
             */
        }

        return $this->aAuthyRelatedByIdCreation;
    }

    /**
     * Declares an association between this object and a Authy object.
     *
     * @param                  Authy $v
     * @return AuthyGroup The current object (for fluent API support)
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
            $v->addAuthyGroupRelatedByIdModification($this);
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
                $this->aAuthyRelatedByIdModification->addAuthyGroupsRelatedByIdModification($this);
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
        if ('AuthyGroupXRelatedByIdAuthyGroup' == $relationName) {
            $this->initAuthyGroupxesRelatedByIdAuthyGroup();
        }
        if ('AuthyRelatedByIdAuthyGroup' == $relationName) {
            $this->initAuthiesRelatedByIdAuthyGroup();
        }
        if ('AuthyRelatedByIdGroupCreation' == $relationName) {
            $this->initAuthiesRelatedByIdGroupCreation();
        }
        if ('PushDevice' == $relationName) {
            $this->initPushDevices();
        }
        if ('GridRun' == $relationName) {
            $this->initGridRuns();
        }
        if ('FleetSlot' == $relationName) {
            $this->initFleetSlots();
        }
        if ('RegimeEpisode' == $relationName) {
            $this->initRegimeEpisodes();
        }
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
        if ('SimWallet' == $relationName) {
            $this->initSimWallets();
        }
        if ('MarketSummary' == $relationName) {
            $this->initMarketSummaries();
        }
        if ('MarketRegime' == $relationName) {
            $this->initMarketRegimes();
        }
        if ('MarketCandle' == $relationName) {
            $this->initMarketCandles();
        }
        if ('BotDecision' == $relationName) {
            $this->initBotDecisions();
        }
        if ('MarketOutlook' == $relationName) {
            $this->initMarketOutlooks();
        }
        if ('MarketOutlookState' == $relationName) {
            $this->initMarketOutlookStates();
        }
        if ('WalletNav' == $relationName) {
            $this->initWalletNavs();
        }
        if ('AuthyGroupRelatedByIdAuthyGroup' == $relationName) {
            $this->initAuthyGroupsRelatedByIdAuthyGroup();
        }
        if ('AuthyGroupXRelatedByIdGroupCreation' == $relationName) {
            $this->initAuthyGroupxesRelatedByIdGroupCreation();
        }
        if ('Config' == $relationName) {
            $this->initConfigs();
        }
        if ('ApiRbac' == $relationName) {
            $this->initApiRbacs();
        }
        if ('Template' == $relationName) {
            $this->initTemplates();
        }
        if ('TemplateFile' == $relationName) {
            $this->initTemplateFiles();
        }
        if ('AuthyRefreshToken' == $relationName) {
            $this->initAuthyRefreshTokens();
        }
        if ('GridRunAudit' == $relationName) {
            $this->initGridRunAudits();
        }
        if ('Country' == $relationName) {
            $this->initCountries();
        }
        if ('OauthClient' == $relationName) {
            $this->initOauthClients();
        }
        if ('OauthAuthCode' == $relationName) {
            $this->initOauthAuthCodes();
        }
        if ('OauthAccessToken' == $relationName) {
            $this->initOauthAccessTokens();
        }
        if ('OauthRefreshToken' == $relationName) {
            $this->initOauthRefreshTokens();
        }
        if ('MessageI18n' == $relationName) {
            $this->initMessageI18ns();
        }
    }

    /**
     * Clears out the collAuthyGroupxesRelatedByIdAuthyGroup collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addAuthyGroupxesRelatedByIdAuthyGroup()
     */
    public function clearAuthyGroupxesRelatedByIdAuthyGroup()
    {
        $this->collAuthyGroupxesRelatedByIdAuthyGroup = null; // important to set this to null since that means it is uninitialized
        $this->collAuthyGroupxesRelatedByIdAuthyGroupPartial = null;

        return $this;
    }

    /**
     * reset is the collAuthyGroupxesRelatedByIdAuthyGroup collection loaded partially
     *
     * @return void
     */
    public function resetPartialAuthyGroupxesRelatedByIdAuthyGroup($v = true)
    {
        $this->collAuthyGroupxesRelatedByIdAuthyGroupPartial = $v;
    }

    /**
     * Initializes the collAuthyGroupxesRelatedByIdAuthyGroup collection.
     *
     * By default this just sets the collAuthyGroupxesRelatedByIdAuthyGroup collection to an empty array (like clearcollAuthyGroupxesRelatedByIdAuthyGroup());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initAuthyGroupxesRelatedByIdAuthyGroup($overrideExisting = true)
    {
        if (null !== $this->collAuthyGroupxesRelatedByIdAuthyGroup && !$overrideExisting) {
            return;
        }
        $this->collAuthyGroupxesRelatedByIdAuthyGroup = new PropelObjectCollection();
        $this->collAuthyGroupxesRelatedByIdAuthyGroup->setModel('AuthyGroupX');
    }

    /**
     * Gets an array of AuthyGroupX objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|AuthyGroupX[] List of AuthyGroupX objects
     * @throws PropelException
     */
    public function getAuthyGroupxesRelatedByIdAuthyGroup($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collAuthyGroupxesRelatedByIdAuthyGroupPartial && !$this->isNew();
        if (null === $this->collAuthyGroupxesRelatedByIdAuthyGroup || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collAuthyGroupxesRelatedByIdAuthyGroup) {
                // return empty collection
                $this->initAuthyGroupxesRelatedByIdAuthyGroup();
            } else {
                $collAuthyGroupxesRelatedByIdAuthyGroup = AuthyGroupXQuery::create(null, $criteria)
                    ->filterByAuthyGroupRelatedByIdAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collAuthyGroupxesRelatedByIdAuthyGroupPartial && count($collAuthyGroupxesRelatedByIdAuthyGroup)) {
                      $this->initAuthyGroupxesRelatedByIdAuthyGroup(false);

                      foreach ($collAuthyGroupxesRelatedByIdAuthyGroup as $obj) {
                        if (false == $this->collAuthyGroupxesRelatedByIdAuthyGroup->contains($obj)) {
                          $this->collAuthyGroupxesRelatedByIdAuthyGroup->append($obj);
                        }
                      }

                      $this->collAuthyGroupxesRelatedByIdAuthyGroupPartial = true;
                    }

                    $collAuthyGroupxesRelatedByIdAuthyGroup->getInternalIterator()->rewind();

                    return $collAuthyGroupxesRelatedByIdAuthyGroup;
                }

                if ($partial && $this->collAuthyGroupxesRelatedByIdAuthyGroup) {
                    foreach ($this->collAuthyGroupxesRelatedByIdAuthyGroup as $obj) {
                        if ($obj->isNew()) {
                            $collAuthyGroupxesRelatedByIdAuthyGroup[] = $obj;
                        }
                    }
                }

                $this->collAuthyGroupxesRelatedByIdAuthyGroup = $collAuthyGroupxesRelatedByIdAuthyGroup;
                $this->collAuthyGroupxesRelatedByIdAuthyGroupPartial = false;
            }
        }

        return $this->collAuthyGroupxesRelatedByIdAuthyGroup;
    }

    /**
     * Sets a collection of AuthyGroupXRelatedByIdAuthyGroup objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $authyGroupxesRelatedByIdAuthyGroup A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setAuthyGroupxesRelatedByIdAuthyGroup(PropelCollection $authyGroupxesRelatedByIdAuthyGroup, ?PropelPDO $con = null)
    {
        $authyGroupxesRelatedByIdAuthyGroupToDelete = $this->getAuthyGroupxesRelatedByIdAuthyGroup(new Criteria(), $con)->diff($authyGroupxesRelatedByIdAuthyGroup);


        //since at least one column in the foreign key is at the same time a PK
        //we can not just set a PK to NULL in the lines below. We have to store
        //a backup of all values, so we are able to manipulate these items based on the onDelete value later.
        $this->authyGroupxesRelatedByIdAuthyGroupScheduledForDeletion = clone $authyGroupxesRelatedByIdAuthyGroupToDelete;

        foreach ($authyGroupxesRelatedByIdAuthyGroupToDelete as $authyGroupXRelatedByIdAuthyGroupRemoved) {
            $authyGroupXRelatedByIdAuthyGroupRemoved->setAuthyGroupRelatedByIdAuthyGroup(null);
        }

        $this->collAuthyGroupxesRelatedByIdAuthyGroup = null;
        foreach ($authyGroupxesRelatedByIdAuthyGroup as $authyGroupXRelatedByIdAuthyGroup) {
            $this->addAuthyGroupXRelatedByIdAuthyGroup($authyGroupXRelatedByIdAuthyGroup);
        }

        $this->collAuthyGroupxesRelatedByIdAuthyGroup = $authyGroupxesRelatedByIdAuthyGroup;
        $this->collAuthyGroupxesRelatedByIdAuthyGroupPartial = false;

        return $this;
    }

    /**
     * Returns the number of related AuthyGroupX objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related AuthyGroupX objects.
     * @throws PropelException
     */
    public function countAuthyGroupxesRelatedByIdAuthyGroup(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collAuthyGroupxesRelatedByIdAuthyGroupPartial && !$this->isNew();
        if (null === $this->collAuthyGroupxesRelatedByIdAuthyGroup || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collAuthyGroupxesRelatedByIdAuthyGroup) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getAuthyGroupxesRelatedByIdAuthyGroup());
            }
            $query = AuthyGroupXQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroupRelatedByIdAuthyGroup($this)
                ->count($con);
        }

        return count($this->collAuthyGroupxesRelatedByIdAuthyGroup);
    }

    /**
     * Method called to associate a AuthyGroupX object to this object
     * through the AuthyGroupX foreign key attribute.
     *
     * @param    AuthyGroupX $l AuthyGroupX
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addAuthyGroupXRelatedByIdAuthyGroup(AuthyGroupX $l)
    {
        if ($this->collAuthyGroupxesRelatedByIdAuthyGroup === null) {
            $this->initAuthyGroupxesRelatedByIdAuthyGroup();
            $this->collAuthyGroupxesRelatedByIdAuthyGroupPartial = true;
        }

        if (!in_array($l, $this->collAuthyGroupxesRelatedByIdAuthyGroup->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddAuthyGroupXRelatedByIdAuthyGroup($l);

            if ($this->authyGroupxesRelatedByIdAuthyGroupScheduledForDeletion and $this->authyGroupxesRelatedByIdAuthyGroupScheduledForDeletion->contains($l)) {
                $this->authyGroupxesRelatedByIdAuthyGroupScheduledForDeletion->remove($this->authyGroupxesRelatedByIdAuthyGroupScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	AuthyGroupXRelatedByIdAuthyGroup $authyGroupXRelatedByIdAuthyGroup The authyGroupXRelatedByIdAuthyGroup object to add.
     */
    protected function doAddAuthyGroupXRelatedByIdAuthyGroup($authyGroupXRelatedByIdAuthyGroup)
    {
        $this->collAuthyGroupxesRelatedByIdAuthyGroup[]= $authyGroupXRelatedByIdAuthyGroup;
        $authyGroupXRelatedByIdAuthyGroup->setAuthyGroupRelatedByIdAuthyGroup($this);
    }

    /**
     * @param	AuthyGroupXRelatedByIdAuthyGroup $authyGroupXRelatedByIdAuthyGroup The authyGroupXRelatedByIdAuthyGroup object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeAuthyGroupXRelatedByIdAuthyGroup($authyGroupXRelatedByIdAuthyGroup)
    {
        if ($this->getAuthyGroupxesRelatedByIdAuthyGroup()->contains($authyGroupXRelatedByIdAuthyGroup)) {
            $this->collAuthyGroupxesRelatedByIdAuthyGroup->remove($this->collAuthyGroupxesRelatedByIdAuthyGroup->search($authyGroupXRelatedByIdAuthyGroup));
            if (null === $this->authyGroupxesRelatedByIdAuthyGroupScheduledForDeletion) {
                $this->authyGroupxesRelatedByIdAuthyGroupScheduledForDeletion = clone $this->collAuthyGroupxesRelatedByIdAuthyGroup;
                $this->authyGroupxesRelatedByIdAuthyGroupScheduledForDeletion->clear();
            }
            $this->authyGroupxesRelatedByIdAuthyGroupScheduledForDeletion[]= clone $authyGroupXRelatedByIdAuthyGroup;
            $authyGroupXRelatedByIdAuthyGroup->setAuthyGroupRelatedByIdAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|AuthyGroupX[] List of AuthyGroupX objects
     */
    public function getAuthyGroupxesRelatedByIdAuthyGroupJoinAuthyRelatedByIdAuthy($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = AuthyGroupXQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdAuthy', $join_behavior);

        return $this->getAuthyGroupxesRelatedByIdAuthyGroup($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|AuthyGroupX[] List of AuthyGroupX objects
     */
    public function getAuthyGroupxesRelatedByIdAuthyGroupJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = AuthyGroupXQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getAuthyGroupxesRelatedByIdAuthyGroup($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|AuthyGroupX[] List of AuthyGroupX objects
     */
    public function getAuthyGroupxesRelatedByIdAuthyGroupJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = AuthyGroupXQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getAuthyGroupxesRelatedByIdAuthyGroup($query, $con);
    }

    /**
     * Clears out the collAuthiesRelatedByIdAuthyGroup collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addAuthiesRelatedByIdAuthyGroup()
     */
    public function clearAuthiesRelatedByIdAuthyGroup()
    {
        $this->collAuthiesRelatedByIdAuthyGroup = null; // important to set this to null since that means it is uninitialized
        $this->collAuthiesRelatedByIdAuthyGroupPartial = null;

        return $this;
    }

    /**
     * reset is the collAuthiesRelatedByIdAuthyGroup collection loaded partially
     *
     * @return void
     */
    public function resetPartialAuthiesRelatedByIdAuthyGroup($v = true)
    {
        $this->collAuthiesRelatedByIdAuthyGroupPartial = $v;
    }

    /**
     * Initializes the collAuthiesRelatedByIdAuthyGroup collection.
     *
     * By default this just sets the collAuthiesRelatedByIdAuthyGroup collection to an empty array (like clearcollAuthiesRelatedByIdAuthyGroup());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initAuthiesRelatedByIdAuthyGroup($overrideExisting = true)
    {
        if (null !== $this->collAuthiesRelatedByIdAuthyGroup && !$overrideExisting) {
            return;
        }
        $this->collAuthiesRelatedByIdAuthyGroup = new PropelObjectCollection();
        $this->collAuthiesRelatedByIdAuthyGroup->setModel('Authy');
    }

    /**
     * Gets an array of Authy objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|Authy[] List of Authy objects
     * @throws PropelException
     */
    public function getAuthiesRelatedByIdAuthyGroup($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collAuthiesRelatedByIdAuthyGroupPartial && !$this->isNew();
        if (null === $this->collAuthiesRelatedByIdAuthyGroup || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collAuthiesRelatedByIdAuthyGroup) {
                // return empty collection
                $this->initAuthiesRelatedByIdAuthyGroup();
            } else {
                $collAuthiesRelatedByIdAuthyGroup = AuthyQuery::create(null, $criteria)
                    ->filterByAuthyGroupRelatedByIdAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collAuthiesRelatedByIdAuthyGroupPartial && count($collAuthiesRelatedByIdAuthyGroup)) {
                      $this->initAuthiesRelatedByIdAuthyGroup(false);

                      foreach ($collAuthiesRelatedByIdAuthyGroup as $obj) {
                        if (false == $this->collAuthiesRelatedByIdAuthyGroup->contains($obj)) {
                          $this->collAuthiesRelatedByIdAuthyGroup->append($obj);
                        }
                      }

                      $this->collAuthiesRelatedByIdAuthyGroupPartial = true;
                    }

                    $collAuthiesRelatedByIdAuthyGroup->getInternalIterator()->rewind();

                    return $collAuthiesRelatedByIdAuthyGroup;
                }

                if ($partial && $this->collAuthiesRelatedByIdAuthyGroup) {
                    foreach ($this->collAuthiesRelatedByIdAuthyGroup as $obj) {
                        if ($obj->isNew()) {
                            $collAuthiesRelatedByIdAuthyGroup[] = $obj;
                        }
                    }
                }

                $this->collAuthiesRelatedByIdAuthyGroup = $collAuthiesRelatedByIdAuthyGroup;
                $this->collAuthiesRelatedByIdAuthyGroupPartial = false;
            }
        }

        return $this->collAuthiesRelatedByIdAuthyGroup;
    }

    /**
     * Sets a collection of AuthyRelatedByIdAuthyGroup objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $authiesRelatedByIdAuthyGroup A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setAuthiesRelatedByIdAuthyGroup(PropelCollection $authiesRelatedByIdAuthyGroup, ?PropelPDO $con = null)
    {
        $authiesRelatedByIdAuthyGroupToDelete = $this->getAuthiesRelatedByIdAuthyGroup(new Criteria(), $con)->diff($authiesRelatedByIdAuthyGroup);


        $this->authiesRelatedByIdAuthyGroupScheduledForDeletion = $authiesRelatedByIdAuthyGroupToDelete;

        foreach ($authiesRelatedByIdAuthyGroupToDelete as $authyRelatedByIdAuthyGroupRemoved) {
            $authyRelatedByIdAuthyGroupRemoved->setAuthyGroupRelatedByIdAuthyGroup(null);
        }

        $this->collAuthiesRelatedByIdAuthyGroup = null;
        foreach ($authiesRelatedByIdAuthyGroup as $authyRelatedByIdAuthyGroup) {
            $this->addAuthyRelatedByIdAuthyGroup($authyRelatedByIdAuthyGroup);
        }

        $this->collAuthiesRelatedByIdAuthyGroup = $authiesRelatedByIdAuthyGroup;
        $this->collAuthiesRelatedByIdAuthyGroupPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Authy objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related Authy objects.
     * @throws PropelException
     */
    public function countAuthiesRelatedByIdAuthyGroup(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collAuthiesRelatedByIdAuthyGroupPartial && !$this->isNew();
        if (null === $this->collAuthiesRelatedByIdAuthyGroup || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collAuthiesRelatedByIdAuthyGroup) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getAuthiesRelatedByIdAuthyGroup());
            }
            $query = AuthyQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroupRelatedByIdAuthyGroup($this)
                ->count($con);
        }

        return count($this->collAuthiesRelatedByIdAuthyGroup);
    }

    /**
     * Method called to associate a Authy object to this object
     * through the Authy foreign key attribute.
     *
     * @param    Authy $l Authy
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addAuthyRelatedByIdAuthyGroup(Authy $l)
    {
        if ($this->collAuthiesRelatedByIdAuthyGroup === null) {
            $this->initAuthiesRelatedByIdAuthyGroup();
            $this->collAuthiesRelatedByIdAuthyGroupPartial = true;
        }

        if (!in_array($l, $this->collAuthiesRelatedByIdAuthyGroup->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddAuthyRelatedByIdAuthyGroup($l);

            if ($this->authiesRelatedByIdAuthyGroupScheduledForDeletion and $this->authiesRelatedByIdAuthyGroupScheduledForDeletion->contains($l)) {
                $this->authiesRelatedByIdAuthyGroupScheduledForDeletion->remove($this->authiesRelatedByIdAuthyGroupScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	AuthyRelatedByIdAuthyGroup $authyRelatedByIdAuthyGroup The authyRelatedByIdAuthyGroup object to add.
     */
    protected function doAddAuthyRelatedByIdAuthyGroup($authyRelatedByIdAuthyGroup)
    {
        $this->collAuthiesRelatedByIdAuthyGroup[]= $authyRelatedByIdAuthyGroup;
        $authyRelatedByIdAuthyGroup->setAuthyGroupRelatedByIdAuthyGroup($this);
    }

    /**
     * @param	AuthyRelatedByIdAuthyGroup $authyRelatedByIdAuthyGroup The authyRelatedByIdAuthyGroup object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeAuthyRelatedByIdAuthyGroup($authyRelatedByIdAuthyGroup)
    {
        if ($this->getAuthiesRelatedByIdAuthyGroup()->contains($authyRelatedByIdAuthyGroup)) {
            $this->collAuthiesRelatedByIdAuthyGroup->remove($this->collAuthiesRelatedByIdAuthyGroup->search($authyRelatedByIdAuthyGroup));
            if (null === $this->authiesRelatedByIdAuthyGroupScheduledForDeletion) {
                $this->authiesRelatedByIdAuthyGroupScheduledForDeletion = clone $this->collAuthiesRelatedByIdAuthyGroup;
                $this->authiesRelatedByIdAuthyGroupScheduledForDeletion->clear();
            }
            $this->authiesRelatedByIdAuthyGroupScheduledForDeletion[]= clone $authyRelatedByIdAuthyGroup;
            $authyRelatedByIdAuthyGroup->setAuthyGroupRelatedByIdAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|Authy[] List of Authy objects
     */
    public function getAuthiesRelatedByIdAuthyGroupJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = AuthyQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getAuthiesRelatedByIdAuthyGroup($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|Authy[] List of Authy objects
     */
    public function getAuthiesRelatedByIdAuthyGroupJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = AuthyQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getAuthiesRelatedByIdAuthyGroup($query, $con);
    }

    /**
     * Clears out the collAuthiesRelatedByIdGroupCreation collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addAuthiesRelatedByIdGroupCreation()
     */
    public function clearAuthiesRelatedByIdGroupCreation()
    {
        $this->collAuthiesRelatedByIdGroupCreation = null; // important to set this to null since that means it is uninitialized
        $this->collAuthiesRelatedByIdGroupCreationPartial = null;

        return $this;
    }

    /**
     * reset is the collAuthiesRelatedByIdGroupCreation collection loaded partially
     *
     * @return void
     */
    public function resetPartialAuthiesRelatedByIdGroupCreation($v = true)
    {
        $this->collAuthiesRelatedByIdGroupCreationPartial = $v;
    }

    /**
     * Initializes the collAuthiesRelatedByIdGroupCreation collection.
     *
     * By default this just sets the collAuthiesRelatedByIdGroupCreation collection to an empty array (like clearcollAuthiesRelatedByIdGroupCreation());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initAuthiesRelatedByIdGroupCreation($overrideExisting = true)
    {
        if (null !== $this->collAuthiesRelatedByIdGroupCreation && !$overrideExisting) {
            return;
        }
        $this->collAuthiesRelatedByIdGroupCreation = new PropelObjectCollection();
        $this->collAuthiesRelatedByIdGroupCreation->setModel('Authy');
    }

    /**
     * Gets an array of Authy objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|Authy[] List of Authy objects
     * @throws PropelException
     */
    public function getAuthiesRelatedByIdGroupCreation($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collAuthiesRelatedByIdGroupCreationPartial && !$this->isNew();
        if (null === $this->collAuthiesRelatedByIdGroupCreation || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collAuthiesRelatedByIdGroupCreation) {
                // return empty collection
                $this->initAuthiesRelatedByIdGroupCreation();
            } else {
                $collAuthiesRelatedByIdGroupCreation = AuthyQuery::create(null, $criteria)
                    ->filterByAuthyGroupRelatedByIdGroupCreation($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collAuthiesRelatedByIdGroupCreationPartial && count($collAuthiesRelatedByIdGroupCreation)) {
                      $this->initAuthiesRelatedByIdGroupCreation(false);

                      foreach ($collAuthiesRelatedByIdGroupCreation as $obj) {
                        if (false == $this->collAuthiesRelatedByIdGroupCreation->contains($obj)) {
                          $this->collAuthiesRelatedByIdGroupCreation->append($obj);
                        }
                      }

                      $this->collAuthiesRelatedByIdGroupCreationPartial = true;
                    }

                    $collAuthiesRelatedByIdGroupCreation->getInternalIterator()->rewind();

                    return $collAuthiesRelatedByIdGroupCreation;
                }

                if ($partial && $this->collAuthiesRelatedByIdGroupCreation) {
                    foreach ($this->collAuthiesRelatedByIdGroupCreation as $obj) {
                        if ($obj->isNew()) {
                            $collAuthiesRelatedByIdGroupCreation[] = $obj;
                        }
                    }
                }

                $this->collAuthiesRelatedByIdGroupCreation = $collAuthiesRelatedByIdGroupCreation;
                $this->collAuthiesRelatedByIdGroupCreationPartial = false;
            }
        }

        return $this->collAuthiesRelatedByIdGroupCreation;
    }

    /**
     * Sets a collection of AuthyRelatedByIdGroupCreation objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $authiesRelatedByIdGroupCreation A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setAuthiesRelatedByIdGroupCreation(PropelCollection $authiesRelatedByIdGroupCreation, ?PropelPDO $con = null)
    {
        $authiesRelatedByIdGroupCreationToDelete = $this->getAuthiesRelatedByIdGroupCreation(new Criteria(), $con)->diff($authiesRelatedByIdGroupCreation);


        $this->authiesRelatedByIdGroupCreationScheduledForDeletion = $authiesRelatedByIdGroupCreationToDelete;

        foreach ($authiesRelatedByIdGroupCreationToDelete as $authyRelatedByIdGroupCreationRemoved) {
            $authyRelatedByIdGroupCreationRemoved->setAuthyGroupRelatedByIdGroupCreation(null);
        }

        $this->collAuthiesRelatedByIdGroupCreation = null;
        foreach ($authiesRelatedByIdGroupCreation as $authyRelatedByIdGroupCreation) {
            $this->addAuthyRelatedByIdGroupCreation($authyRelatedByIdGroupCreation);
        }

        $this->collAuthiesRelatedByIdGroupCreation = $authiesRelatedByIdGroupCreation;
        $this->collAuthiesRelatedByIdGroupCreationPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Authy objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related Authy objects.
     * @throws PropelException
     */
    public function countAuthiesRelatedByIdGroupCreation(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collAuthiesRelatedByIdGroupCreationPartial && !$this->isNew();
        if (null === $this->collAuthiesRelatedByIdGroupCreation || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collAuthiesRelatedByIdGroupCreation) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getAuthiesRelatedByIdGroupCreation());
            }
            $query = AuthyQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroupRelatedByIdGroupCreation($this)
                ->count($con);
        }

        return count($this->collAuthiesRelatedByIdGroupCreation);
    }

    /**
     * Method called to associate a Authy object to this object
     * through the Authy foreign key attribute.
     *
     * @param    Authy $l Authy
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addAuthyRelatedByIdGroupCreation(Authy $l)
    {
        if ($this->collAuthiesRelatedByIdGroupCreation === null) {
            $this->initAuthiesRelatedByIdGroupCreation();
            $this->collAuthiesRelatedByIdGroupCreationPartial = true;
        }

        if (!in_array($l, $this->collAuthiesRelatedByIdGroupCreation->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddAuthyRelatedByIdGroupCreation($l);

            if ($this->authiesRelatedByIdGroupCreationScheduledForDeletion and $this->authiesRelatedByIdGroupCreationScheduledForDeletion->contains($l)) {
                $this->authiesRelatedByIdGroupCreationScheduledForDeletion->remove($this->authiesRelatedByIdGroupCreationScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	AuthyRelatedByIdGroupCreation $authyRelatedByIdGroupCreation The authyRelatedByIdGroupCreation object to add.
     */
    protected function doAddAuthyRelatedByIdGroupCreation($authyRelatedByIdGroupCreation)
    {
        $this->collAuthiesRelatedByIdGroupCreation[]= $authyRelatedByIdGroupCreation;
        $authyRelatedByIdGroupCreation->setAuthyGroupRelatedByIdGroupCreation($this);
    }

    /**
     * @param	AuthyRelatedByIdGroupCreation $authyRelatedByIdGroupCreation The authyRelatedByIdGroupCreation object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeAuthyRelatedByIdGroupCreation($authyRelatedByIdGroupCreation)
    {
        if ($this->getAuthiesRelatedByIdGroupCreation()->contains($authyRelatedByIdGroupCreation)) {
            $this->collAuthiesRelatedByIdGroupCreation->remove($this->collAuthiesRelatedByIdGroupCreation->search($authyRelatedByIdGroupCreation));
            if (null === $this->authiesRelatedByIdGroupCreationScheduledForDeletion) {
                $this->authiesRelatedByIdGroupCreationScheduledForDeletion = clone $this->collAuthiesRelatedByIdGroupCreation;
                $this->authiesRelatedByIdGroupCreationScheduledForDeletion->clear();
            }
            $this->authiesRelatedByIdGroupCreationScheduledForDeletion[]= $authyRelatedByIdGroupCreation;
            $authyRelatedByIdGroupCreation->setAuthyGroupRelatedByIdGroupCreation(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|Authy[] List of Authy objects
     */
    public function getAuthiesRelatedByIdGroupCreationJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = AuthyQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getAuthiesRelatedByIdGroupCreation($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|Authy[] List of Authy objects
     */
    public function getAuthiesRelatedByIdGroupCreationJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = AuthyQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getAuthiesRelatedByIdGroupCreation($query, $con);
    }

    /**
     * Clears out the collPushDevices collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addPushDevices()
     */
    public function clearPushDevices()
    {
        $this->collPushDevices = null; // important to set this to null since that means it is uninitialized
        $this->collPushDevicesPartial = null;

        return $this;
    }

    /**
     * reset is the collPushDevices collection loaded partially
     *
     * @return void
     */
    public function resetPartialPushDevices($v = true)
    {
        $this->collPushDevicesPartial = $v;
    }

    /**
     * Initializes the collPushDevices collection.
     *
     * By default this just sets the collPushDevices collection to an empty array (like clearcollPushDevices());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initPushDevices($overrideExisting = true)
    {
        if (null !== $this->collPushDevices && !$overrideExisting) {
            return;
        }
        $this->collPushDevices = new PropelObjectCollection();
        $this->collPushDevices->setModel('PushDevice');
    }

    /**
     * Gets an array of PushDevice objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|PushDevice[] List of PushDevice objects
     * @throws PropelException
     */
    public function getPushDevices($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collPushDevicesPartial && !$this->isNew();
        if (null === $this->collPushDevices || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collPushDevices) {
                // return empty collection
                $this->initPushDevices();
            } else {
                $collPushDevices = PushDeviceQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collPushDevicesPartial && count($collPushDevices)) {
                      $this->initPushDevices(false);

                      foreach ($collPushDevices as $obj) {
                        if (false == $this->collPushDevices->contains($obj)) {
                          $this->collPushDevices->append($obj);
                        }
                      }

                      $this->collPushDevicesPartial = true;
                    }

                    $collPushDevices->getInternalIterator()->rewind();

                    return $collPushDevices;
                }

                if ($partial && $this->collPushDevices) {
                    foreach ($this->collPushDevices as $obj) {
                        if ($obj->isNew()) {
                            $collPushDevices[] = $obj;
                        }
                    }
                }

                $this->collPushDevices = $collPushDevices;
                $this->collPushDevicesPartial = false;
            }
        }

        return $this->collPushDevices;
    }

    /**
     * Sets a collection of PushDevice objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $pushDevices A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setPushDevices(PropelCollection $pushDevices, ?PropelPDO $con = null)
    {
        $pushDevicesToDelete = $this->getPushDevices(new Criteria(), $con)->diff($pushDevices);


        $this->pushDevicesScheduledForDeletion = $pushDevicesToDelete;

        foreach ($pushDevicesToDelete as $pushDeviceRemoved) {
            $pushDeviceRemoved->setAuthyGroup(null);
        }

        $this->collPushDevices = null;
        foreach ($pushDevices as $pushDevice) {
            $this->addPushDevice($pushDevice);
        }

        $this->collPushDevices = $pushDevices;
        $this->collPushDevicesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related PushDevice objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related PushDevice objects.
     * @throws PropelException
     */
    public function countPushDevices(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collPushDevicesPartial && !$this->isNew();
        if (null === $this->collPushDevices || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collPushDevices) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getPushDevices());
            }
            $query = PushDeviceQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collPushDevices);
    }

    /**
     * Method called to associate a PushDevice object to this object
     * through the PushDevice foreign key attribute.
     *
     * @param    PushDevice $l PushDevice
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addPushDevice(PushDevice $l)
    {
        if ($this->collPushDevices === null) {
            $this->initPushDevices();
            $this->collPushDevicesPartial = true;
        }

        if (!in_array($l, $this->collPushDevices->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddPushDevice($l);

            if ($this->pushDevicesScheduledForDeletion and $this->pushDevicesScheduledForDeletion->contains($l)) {
                $this->pushDevicesScheduledForDeletion->remove($this->pushDevicesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	PushDevice $pushDevice The pushDevice object to add.
     */
    protected function doAddPushDevice($pushDevice)
    {
        $this->collPushDevices[]= $pushDevice;
        $pushDevice->setAuthyGroup($this);
    }

    /**
     * @param	PushDevice $pushDevice The pushDevice object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removePushDevice($pushDevice)
    {
        if ($this->getPushDevices()->contains($pushDevice)) {
            $this->collPushDevices->remove($this->collPushDevices->search($pushDevice));
            if (null === $this->pushDevicesScheduledForDeletion) {
                $this->pushDevicesScheduledForDeletion = clone $this->collPushDevices;
                $this->pushDevicesScheduledForDeletion->clear();
            }
            $this->pushDevicesScheduledForDeletion[]= $pushDevice;
            $pushDevice->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|PushDevice[] List of PushDevice objects
     */
    public function getPushDevicesJoinAuthyRelatedByIdAuthy($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = PushDeviceQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdAuthy', $join_behavior);

        return $this->getPushDevices($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|PushDevice[] List of PushDevice objects
     */
    public function getPushDevicesJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = PushDeviceQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getPushDevices($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|PushDevice[] List of PushDevice objects
     */
    public function getPushDevicesJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = PushDeviceQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getPushDevices($query, $con);
    }

    /**
     * Clears out the collGridRuns collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addGridRuns()
     */
    public function clearGridRuns()
    {
        $this->collGridRuns = null; // important to set this to null since that means it is uninitialized
        $this->collGridRunsPartial = null;

        return $this;
    }

    /**
     * reset is the collGridRuns collection loaded partially
     *
     * @return void
     */
    public function resetPartialGridRuns($v = true)
    {
        $this->collGridRunsPartial = $v;
    }

    /**
     * Initializes the collGridRuns collection.
     *
     * By default this just sets the collGridRuns collection to an empty array (like clearcollGridRuns());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initGridRuns($overrideExisting = true)
    {
        if (null !== $this->collGridRuns && !$overrideExisting) {
            return;
        }
        $this->collGridRuns = new PropelObjectCollection();
        $this->collGridRuns->setModel('GridRun');
    }

    /**
     * Gets an array of GridRun objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|GridRun[] List of GridRun objects
     * @throws PropelException
     */
    public function getGridRuns($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collGridRunsPartial && !$this->isNew();
        if (null === $this->collGridRuns || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collGridRuns) {
                // return empty collection
                $this->initGridRuns();
            } else {
                $collGridRuns = GridRunQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collGridRunsPartial && count($collGridRuns)) {
                      $this->initGridRuns(false);

                      foreach ($collGridRuns as $obj) {
                        if (false == $this->collGridRuns->contains($obj)) {
                          $this->collGridRuns->append($obj);
                        }
                      }

                      $this->collGridRunsPartial = true;
                    }

                    $collGridRuns->getInternalIterator()->rewind();

                    return $collGridRuns;
                }

                if ($partial && $this->collGridRuns) {
                    foreach ($this->collGridRuns as $obj) {
                        if ($obj->isNew()) {
                            $collGridRuns[] = $obj;
                        }
                    }
                }

                $this->collGridRuns = $collGridRuns;
                $this->collGridRunsPartial = false;
            }
        }

        return $this->collGridRuns;
    }

    /**
     * Sets a collection of GridRun objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $gridRuns A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setGridRuns(PropelCollection $gridRuns, ?PropelPDO $con = null)
    {
        $gridRunsToDelete = $this->getGridRuns(new Criteria(), $con)->diff($gridRuns);


        $this->gridRunsScheduledForDeletion = $gridRunsToDelete;

        foreach ($gridRunsToDelete as $gridRunRemoved) {
            $gridRunRemoved->setAuthyGroup(null);
        }

        $this->collGridRuns = null;
        foreach ($gridRuns as $gridRun) {
            $this->addGridRun($gridRun);
        }

        $this->collGridRuns = $gridRuns;
        $this->collGridRunsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related GridRun objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related GridRun objects.
     * @throws PropelException
     */
    public function countGridRuns(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collGridRunsPartial && !$this->isNew();
        if (null === $this->collGridRuns || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collGridRuns) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getGridRuns());
            }
            $query = GridRunQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collGridRuns);
    }

    /**
     * Method called to associate a GridRun object to this object
     * through the GridRun foreign key attribute.
     *
     * @param    GridRun $l GridRun
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addGridRun(GridRun $l)
    {
        if ($this->collGridRuns === null) {
            $this->initGridRuns();
            $this->collGridRunsPartial = true;
        }

        if (!in_array($l, $this->collGridRuns->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddGridRun($l);

            if ($this->gridRunsScheduledForDeletion and $this->gridRunsScheduledForDeletion->contains($l)) {
                $this->gridRunsScheduledForDeletion->remove($this->gridRunsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	GridRun $gridRun The gridRun object to add.
     */
    protected function doAddGridRun($gridRun)
    {
        $this->collGridRuns[]= $gridRun;
        $gridRun->setAuthyGroup($this);
    }

    /**
     * @param	GridRun $gridRun The gridRun object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeGridRun($gridRun)
    {
        if ($this->getGridRuns()->contains($gridRun)) {
            $this->collGridRuns->remove($this->collGridRuns->search($gridRun));
            if (null === $this->gridRunsScheduledForDeletion) {
                $this->gridRunsScheduledForDeletion = clone $this->collGridRuns;
                $this->gridRunsScheduledForDeletion->clear();
            }
            $this->gridRunsScheduledForDeletion[]= $gridRun;
            $gridRun->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|GridRun[] List of GridRun objects
     */
    public function getGridRunsJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = GridRunQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getGridRuns($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|GridRun[] List of GridRun objects
     */
    public function getGridRunsJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = GridRunQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getGridRuns($query, $con);
    }

    /**
     * Clears out the collFleetSlots collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addFleetSlots()
     */
    public function clearFleetSlots()
    {
        $this->collFleetSlots = null; // important to set this to null since that means it is uninitialized
        $this->collFleetSlotsPartial = null;

        return $this;
    }

    /**
     * reset is the collFleetSlots collection loaded partially
     *
     * @return void
     */
    public function resetPartialFleetSlots($v = true)
    {
        $this->collFleetSlotsPartial = $v;
    }

    /**
     * Initializes the collFleetSlots collection.
     *
     * By default this just sets the collFleetSlots collection to an empty array (like clearcollFleetSlots());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initFleetSlots($overrideExisting = true)
    {
        if (null !== $this->collFleetSlots && !$overrideExisting) {
            return;
        }
        $this->collFleetSlots = new PropelObjectCollection();
        $this->collFleetSlots->setModel('FleetSlot');
    }

    /**
     * Gets an array of FleetSlot objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|FleetSlot[] List of FleetSlot objects
     * @throws PropelException
     */
    public function getFleetSlots($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collFleetSlotsPartial && !$this->isNew();
        if (null === $this->collFleetSlots || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collFleetSlots) {
                // return empty collection
                $this->initFleetSlots();
            } else {
                $collFleetSlots = FleetSlotQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collFleetSlotsPartial && count($collFleetSlots)) {
                      $this->initFleetSlots(false);

                      foreach ($collFleetSlots as $obj) {
                        if (false == $this->collFleetSlots->contains($obj)) {
                          $this->collFleetSlots->append($obj);
                        }
                      }

                      $this->collFleetSlotsPartial = true;
                    }

                    $collFleetSlots->getInternalIterator()->rewind();

                    return $collFleetSlots;
                }

                if ($partial && $this->collFleetSlots) {
                    foreach ($this->collFleetSlots as $obj) {
                        if ($obj->isNew()) {
                            $collFleetSlots[] = $obj;
                        }
                    }
                }

                $this->collFleetSlots = $collFleetSlots;
                $this->collFleetSlotsPartial = false;
            }
        }

        return $this->collFleetSlots;
    }

    /**
     * Sets a collection of FleetSlot objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $fleetSlots A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setFleetSlots(PropelCollection $fleetSlots, ?PropelPDO $con = null)
    {
        $fleetSlotsToDelete = $this->getFleetSlots(new Criteria(), $con)->diff($fleetSlots);


        $this->fleetSlotsScheduledForDeletion = $fleetSlotsToDelete;

        foreach ($fleetSlotsToDelete as $fleetSlotRemoved) {
            $fleetSlotRemoved->setAuthyGroup(null);
        }

        $this->collFleetSlots = null;
        foreach ($fleetSlots as $fleetSlot) {
            $this->addFleetSlot($fleetSlot);
        }

        $this->collFleetSlots = $fleetSlots;
        $this->collFleetSlotsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related FleetSlot objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related FleetSlot objects.
     * @throws PropelException
     */
    public function countFleetSlots(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collFleetSlotsPartial && !$this->isNew();
        if (null === $this->collFleetSlots || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collFleetSlots) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getFleetSlots());
            }
            $query = FleetSlotQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collFleetSlots);
    }

    /**
     * Method called to associate a FleetSlot object to this object
     * through the FleetSlot foreign key attribute.
     *
     * @param    FleetSlot $l FleetSlot
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addFleetSlot(FleetSlot $l)
    {
        if ($this->collFleetSlots === null) {
            $this->initFleetSlots();
            $this->collFleetSlotsPartial = true;
        }

        if (!in_array($l, $this->collFleetSlots->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddFleetSlot($l);

            if ($this->fleetSlotsScheduledForDeletion and $this->fleetSlotsScheduledForDeletion->contains($l)) {
                $this->fleetSlotsScheduledForDeletion->remove($this->fleetSlotsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	FleetSlot $fleetSlot The fleetSlot object to add.
     */
    protected function doAddFleetSlot($fleetSlot)
    {
        $this->collFleetSlots[]= $fleetSlot;
        $fleetSlot->setAuthyGroup($this);
    }

    /**
     * @param	FleetSlot $fleetSlot The fleetSlot object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeFleetSlot($fleetSlot)
    {
        if ($this->getFleetSlots()->contains($fleetSlot)) {
            $this->collFleetSlots->remove($this->collFleetSlots->search($fleetSlot));
            if (null === $this->fleetSlotsScheduledForDeletion) {
                $this->fleetSlotsScheduledForDeletion = clone $this->collFleetSlots;
                $this->fleetSlotsScheduledForDeletion->clear();
            }
            $this->fleetSlotsScheduledForDeletion[]= $fleetSlot;
            $fleetSlot->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|FleetSlot[] List of FleetSlot objects
     */
    public function getFleetSlotsJoinGridRun($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = FleetSlotQuery::create(null, $criteria);
        $query->joinWith('GridRun', $join_behavior);

        return $this->getFleetSlots($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|FleetSlot[] List of FleetSlot objects
     */
    public function getFleetSlotsJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = FleetSlotQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getFleetSlots($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|FleetSlot[] List of FleetSlot objects
     */
    public function getFleetSlotsJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = FleetSlotQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getFleetSlots($query, $con);
    }

    /**
     * Clears out the collRegimeEpisodes collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
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
     * If this AuthyGroup is new, it will return
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
                    ->filterByAuthyGroup($this)
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
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setRegimeEpisodes(PropelCollection $regimeEpisodes, ?PropelPDO $con = null)
    {
        $regimeEpisodesToDelete = $this->getRegimeEpisodes(new Criteria(), $con)->diff($regimeEpisodes);


        $this->regimeEpisodesScheduledForDeletion = $regimeEpisodesToDelete;

        foreach ($regimeEpisodesToDelete as $regimeEpisodeRemoved) {
            $regimeEpisodeRemoved->setAuthyGroup(null);
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
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collRegimeEpisodes);
    }

    /**
     * Method called to associate a RegimeEpisode object to this object
     * through the RegimeEpisode foreign key attribute.
     *
     * @param    RegimeEpisode $l RegimeEpisode
     * @return AuthyGroup The current object (for fluent API support)
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
        $regimeEpisode->setAuthyGroup($this);
    }

    /**
     * @param	RegimeEpisode $regimeEpisode The regimeEpisode object to remove.
     * @return AuthyGroup The current object (for fluent API support)
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
            $regimeEpisode->setAuthyGroup(null);
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
    public function getRegimeEpisodesJoinFleetSlot($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = RegimeEpisodeQuery::create(null, $criteria);
        $query->joinWith('FleetSlot', $join_behavior);

        return $this->getRegimeEpisodes($query, $con);
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
     * Clears out the collBotOrders collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
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
     * If this AuthyGroup is new, it will return
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
                    ->filterByAuthyGroup($this)
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
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setBotOrders(PropelCollection $botOrders, ?PropelPDO $con = null)
    {
        $botOrdersToDelete = $this->getBotOrders(new Criteria(), $con)->diff($botOrders);


        $this->botOrdersScheduledForDeletion = $botOrdersToDelete;

        foreach ($botOrdersToDelete as $botOrderRemoved) {
            $botOrderRemoved->setAuthyGroup(null);
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
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collBotOrders);
    }

    /**
     * Method called to associate a BotOrder object to this object
     * through the BotOrder foreign key attribute.
     *
     * @param    BotOrder $l BotOrder
     * @return AuthyGroup The current object (for fluent API support)
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
        $botOrder->setAuthyGroup($this);
    }

    /**
     * @param	BotOrder $botOrder The botOrder object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeBotOrder($botOrder)
    {
        if ($this->getBotOrders()->contains($botOrder)) {
            $this->collBotOrders->remove($this->collBotOrders->search($botOrder));
            if (null === $this->botOrdersScheduledForDeletion) {
                $this->botOrdersScheduledForDeletion = clone $this->collBotOrders;
                $this->botOrdersScheduledForDeletion->clear();
            }
            $this->botOrdersScheduledForDeletion[]= $botOrder;
            $botOrder->setAuthyGroup(null);
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
    public function getBotOrdersJoinGridRun($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = BotOrderQuery::create(null, $criteria);
        $query->joinWith('GridRun', $join_behavior);

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
     * @return AuthyGroup The current object (for fluent API support)
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
     * If this AuthyGroup is new, it will return
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
                    ->filterByAuthyGroup($this)
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
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setTradeCycles(PropelCollection $tradeCycles, ?PropelPDO $con = null)
    {
        $tradeCyclesToDelete = $this->getTradeCycles(new Criteria(), $con)->diff($tradeCycles);


        $this->tradeCyclesScheduledForDeletion = $tradeCyclesToDelete;

        foreach ($tradeCyclesToDelete as $tradeCycleRemoved) {
            $tradeCycleRemoved->setAuthyGroup(null);
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
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collTradeCycles);
    }

    /**
     * Method called to associate a TradeCycle object to this object
     * through the TradeCycle foreign key attribute.
     *
     * @param    TradeCycle $l TradeCycle
     * @return AuthyGroup The current object (for fluent API support)
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
        $tradeCycle->setAuthyGroup($this);
    }

    /**
     * @param	TradeCycle $tradeCycle The tradeCycle object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeTradeCycle($tradeCycle)
    {
        if ($this->getTradeCycles()->contains($tradeCycle)) {
            $this->collTradeCycles->remove($this->collTradeCycles->search($tradeCycle));
            if (null === $this->tradeCyclesScheduledForDeletion) {
                $this->tradeCyclesScheduledForDeletion = clone $this->collTradeCycles;
                $this->tradeCyclesScheduledForDeletion->clear();
            }
            $this->tradeCyclesScheduledForDeletion[]= $tradeCycle;
            $tradeCycle->setAuthyGroup(null);
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
    public function getTradeCyclesJoinGridRun($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = TradeCycleQuery::create(null, $criteria);
        $query->joinWith('GridRun', $join_behavior);

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
     * @return AuthyGroup The current object (for fluent API support)
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
     * If this AuthyGroup is new, it will return
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
                    ->filterByAuthyGroup($this)
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
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setBotEvents(PropelCollection $botEvents, ?PropelPDO $con = null)
    {
        $botEventsToDelete = $this->getBotEvents(new Criteria(), $con)->diff($botEvents);


        $this->botEventsScheduledForDeletion = $botEventsToDelete;

        foreach ($botEventsToDelete as $botEventRemoved) {
            $botEventRemoved->setAuthyGroup(null);
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
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collBotEvents);
    }

    /**
     * Method called to associate a BotEvent object to this object
     * through the BotEvent foreign key attribute.
     *
     * @param    BotEvent $l BotEvent
     * @return AuthyGroup The current object (for fluent API support)
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
        $botEvent->setAuthyGroup($this);
    }

    /**
     * @param	BotEvent $botEvent The botEvent object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeBotEvent($botEvent)
    {
        if ($this->getBotEvents()->contains($botEvent)) {
            $this->collBotEvents->remove($this->collBotEvents->search($botEvent));
            if (null === $this->botEventsScheduledForDeletion) {
                $this->botEventsScheduledForDeletion = clone $this->collBotEvents;
                $this->botEventsScheduledForDeletion->clear();
            }
            $this->botEventsScheduledForDeletion[]= $botEvent;
            $botEvent->setAuthyGroup(null);
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
    public function getBotEventsJoinGridRun($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = BotEventQuery::create(null, $criteria);
        $query->joinWith('GridRun', $join_behavior);

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
     * @return AuthyGroup The current object (for fluent API support)
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
     * If this AuthyGroup is new, it will return
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
                    ->filterByAuthyGroup($this)
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
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setBotCommands(PropelCollection $botCommands, ?PropelPDO $con = null)
    {
        $botCommandsToDelete = $this->getBotCommands(new Criteria(), $con)->diff($botCommands);


        $this->botCommandsScheduledForDeletion = $botCommandsToDelete;

        foreach ($botCommandsToDelete as $botCommandRemoved) {
            $botCommandRemoved->setAuthyGroup(null);
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
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collBotCommands);
    }

    /**
     * Method called to associate a BotCommand object to this object
     * through the BotCommand foreign key attribute.
     *
     * @param    BotCommand $l BotCommand
     * @return AuthyGroup The current object (for fluent API support)
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
        $botCommand->setAuthyGroup($this);
    }

    /**
     * @param	BotCommand $botCommand The botCommand object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeBotCommand($botCommand)
    {
        if ($this->getBotCommands()->contains($botCommand)) {
            $this->collBotCommands->remove($this->collBotCommands->search($botCommand));
            if (null === $this->botCommandsScheduledForDeletion) {
                $this->botCommandsScheduledForDeletion = clone $this->collBotCommands;
                $this->botCommandsScheduledForDeletion->clear();
            }
            $this->botCommandsScheduledForDeletion[]= $botCommand;
            $botCommand->setAuthyGroup(null);
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
    public function getBotCommandsJoinGridRun($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = BotCommandQuery::create(null, $criteria);
        $query->joinWith('GridRun', $join_behavior);

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
     * Clears out the collSimWallets collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addSimWallets()
     */
    public function clearSimWallets()
    {
        $this->collSimWallets = null; // important to set this to null since that means it is uninitialized
        $this->collSimWalletsPartial = null;

        return $this;
    }

    /**
     * reset is the collSimWallets collection loaded partially
     *
     * @return void
     */
    public function resetPartialSimWallets($v = true)
    {
        $this->collSimWalletsPartial = $v;
    }

    /**
     * Initializes the collSimWallets collection.
     *
     * By default this just sets the collSimWallets collection to an empty array (like clearcollSimWallets());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initSimWallets($overrideExisting = true)
    {
        if (null !== $this->collSimWallets && !$overrideExisting) {
            return;
        }
        $this->collSimWallets = new PropelObjectCollection();
        $this->collSimWallets->setModel('SimWallet');
    }

    /**
     * Gets an array of SimWallet objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|SimWallet[] List of SimWallet objects
     * @throws PropelException
     */
    public function getSimWallets($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collSimWalletsPartial && !$this->isNew();
        if (null === $this->collSimWallets || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collSimWallets) {
                // return empty collection
                $this->initSimWallets();
            } else {
                $collSimWallets = SimWalletQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collSimWalletsPartial && count($collSimWallets)) {
                      $this->initSimWallets(false);

                      foreach ($collSimWallets as $obj) {
                        if (false == $this->collSimWallets->contains($obj)) {
                          $this->collSimWallets->append($obj);
                        }
                      }

                      $this->collSimWalletsPartial = true;
                    }

                    $collSimWallets->getInternalIterator()->rewind();

                    return $collSimWallets;
                }

                if ($partial && $this->collSimWallets) {
                    foreach ($this->collSimWallets as $obj) {
                        if ($obj->isNew()) {
                            $collSimWallets[] = $obj;
                        }
                    }
                }

                $this->collSimWallets = $collSimWallets;
                $this->collSimWalletsPartial = false;
            }
        }

        return $this->collSimWallets;
    }

    /**
     * Sets a collection of SimWallet objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $simWallets A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setSimWallets(PropelCollection $simWallets, ?PropelPDO $con = null)
    {
        $simWalletsToDelete = $this->getSimWallets(new Criteria(), $con)->diff($simWallets);


        $this->simWalletsScheduledForDeletion = $simWalletsToDelete;

        foreach ($simWalletsToDelete as $simWalletRemoved) {
            $simWalletRemoved->setAuthyGroup(null);
        }

        $this->collSimWallets = null;
        foreach ($simWallets as $simWallet) {
            $this->addSimWallet($simWallet);
        }

        $this->collSimWallets = $simWallets;
        $this->collSimWalletsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related SimWallet objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related SimWallet objects.
     * @throws PropelException
     */
    public function countSimWallets(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collSimWalletsPartial && !$this->isNew();
        if (null === $this->collSimWallets || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collSimWallets) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getSimWallets());
            }
            $query = SimWalletQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collSimWallets);
    }

    /**
     * Method called to associate a SimWallet object to this object
     * through the SimWallet foreign key attribute.
     *
     * @param    SimWallet $l SimWallet
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addSimWallet(SimWallet $l)
    {
        if ($this->collSimWallets === null) {
            $this->initSimWallets();
            $this->collSimWalletsPartial = true;
        }

        if (!in_array($l, $this->collSimWallets->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddSimWallet($l);

            if ($this->simWalletsScheduledForDeletion and $this->simWalletsScheduledForDeletion->contains($l)) {
                $this->simWalletsScheduledForDeletion->remove($this->simWalletsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	SimWallet $simWallet The simWallet object to add.
     */
    protected function doAddSimWallet($simWallet)
    {
        $this->collSimWallets[]= $simWallet;
        $simWallet->setAuthyGroup($this);
    }

    /**
     * @param	SimWallet $simWallet The simWallet object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeSimWallet($simWallet)
    {
        if ($this->getSimWallets()->contains($simWallet)) {
            $this->collSimWallets->remove($this->collSimWallets->search($simWallet));
            if (null === $this->simWalletsScheduledForDeletion) {
                $this->simWalletsScheduledForDeletion = clone $this->collSimWallets;
                $this->simWalletsScheduledForDeletion->clear();
            }
            $this->simWalletsScheduledForDeletion[]= $simWallet;
            $simWallet->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|SimWallet[] List of SimWallet objects
     */
    public function getSimWalletsJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = SimWalletQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getSimWallets($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|SimWallet[] List of SimWallet objects
     */
    public function getSimWalletsJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = SimWalletQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getSimWallets($query, $con);
    }

    /**
     * Clears out the collMarketSummaries collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addMarketSummaries()
     */
    public function clearMarketSummaries()
    {
        $this->collMarketSummaries = null; // important to set this to null since that means it is uninitialized
        $this->collMarketSummariesPartial = null;

        return $this;
    }

    /**
     * reset is the collMarketSummaries collection loaded partially
     *
     * @return void
     */
    public function resetPartialMarketSummaries($v = true)
    {
        $this->collMarketSummariesPartial = $v;
    }

    /**
     * Initializes the collMarketSummaries collection.
     *
     * By default this just sets the collMarketSummaries collection to an empty array (like clearcollMarketSummaries());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initMarketSummaries($overrideExisting = true)
    {
        if (null !== $this->collMarketSummaries && !$overrideExisting) {
            return;
        }
        $this->collMarketSummaries = new PropelObjectCollection();
        $this->collMarketSummaries->setModel('MarketSummary');
    }

    /**
     * Gets an array of MarketSummary objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|MarketSummary[] List of MarketSummary objects
     * @throws PropelException
     */
    public function getMarketSummaries($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collMarketSummariesPartial && !$this->isNew();
        if (null === $this->collMarketSummaries || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collMarketSummaries) {
                // return empty collection
                $this->initMarketSummaries();
            } else {
                $collMarketSummaries = MarketSummaryQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collMarketSummariesPartial && count($collMarketSummaries)) {
                      $this->initMarketSummaries(false);

                      foreach ($collMarketSummaries as $obj) {
                        if (false == $this->collMarketSummaries->contains($obj)) {
                          $this->collMarketSummaries->append($obj);
                        }
                      }

                      $this->collMarketSummariesPartial = true;
                    }

                    $collMarketSummaries->getInternalIterator()->rewind();

                    return $collMarketSummaries;
                }

                if ($partial && $this->collMarketSummaries) {
                    foreach ($this->collMarketSummaries as $obj) {
                        if ($obj->isNew()) {
                            $collMarketSummaries[] = $obj;
                        }
                    }
                }

                $this->collMarketSummaries = $collMarketSummaries;
                $this->collMarketSummariesPartial = false;
            }
        }

        return $this->collMarketSummaries;
    }

    /**
     * Sets a collection of MarketSummary objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $marketSummaries A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setMarketSummaries(PropelCollection $marketSummaries, ?PropelPDO $con = null)
    {
        $marketSummariesToDelete = $this->getMarketSummaries(new Criteria(), $con)->diff($marketSummaries);


        $this->marketSummariesScheduledForDeletion = $marketSummariesToDelete;

        foreach ($marketSummariesToDelete as $marketSummaryRemoved) {
            $marketSummaryRemoved->setAuthyGroup(null);
        }

        $this->collMarketSummaries = null;
        foreach ($marketSummaries as $marketSummary) {
            $this->addMarketSummary($marketSummary);
        }

        $this->collMarketSummaries = $marketSummaries;
        $this->collMarketSummariesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related MarketSummary objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related MarketSummary objects.
     * @throws PropelException
     */
    public function countMarketSummaries(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collMarketSummariesPartial && !$this->isNew();
        if (null === $this->collMarketSummaries || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collMarketSummaries) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getMarketSummaries());
            }
            $query = MarketSummaryQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collMarketSummaries);
    }

    /**
     * Method called to associate a MarketSummary object to this object
     * through the MarketSummary foreign key attribute.
     *
     * @param    MarketSummary $l MarketSummary
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addMarketSummary(MarketSummary $l)
    {
        if ($this->collMarketSummaries === null) {
            $this->initMarketSummaries();
            $this->collMarketSummariesPartial = true;
        }

        if (!in_array($l, $this->collMarketSummaries->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddMarketSummary($l);

            if ($this->marketSummariesScheduledForDeletion and $this->marketSummariesScheduledForDeletion->contains($l)) {
                $this->marketSummariesScheduledForDeletion->remove($this->marketSummariesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	MarketSummary $marketSummary The marketSummary object to add.
     */
    protected function doAddMarketSummary($marketSummary)
    {
        $this->collMarketSummaries[]= $marketSummary;
        $marketSummary->setAuthyGroup($this);
    }

    /**
     * @param	MarketSummary $marketSummary The marketSummary object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeMarketSummary($marketSummary)
    {
        if ($this->getMarketSummaries()->contains($marketSummary)) {
            $this->collMarketSummaries->remove($this->collMarketSummaries->search($marketSummary));
            if (null === $this->marketSummariesScheduledForDeletion) {
                $this->marketSummariesScheduledForDeletion = clone $this->collMarketSummaries;
                $this->marketSummariesScheduledForDeletion->clear();
            }
            $this->marketSummariesScheduledForDeletion[]= $marketSummary;
            $marketSummary->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|MarketSummary[] List of MarketSummary objects
     */
    public function getMarketSummariesJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = MarketSummaryQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getMarketSummaries($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|MarketSummary[] List of MarketSummary objects
     */
    public function getMarketSummariesJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = MarketSummaryQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getMarketSummaries($query, $con);
    }

    /**
     * Clears out the collMarketRegimes collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addMarketRegimes()
     */
    public function clearMarketRegimes()
    {
        $this->collMarketRegimes = null; // important to set this to null since that means it is uninitialized
        $this->collMarketRegimesPartial = null;

        return $this;
    }

    /**
     * reset is the collMarketRegimes collection loaded partially
     *
     * @return void
     */
    public function resetPartialMarketRegimes($v = true)
    {
        $this->collMarketRegimesPartial = $v;
    }

    /**
     * Initializes the collMarketRegimes collection.
     *
     * By default this just sets the collMarketRegimes collection to an empty array (like clearcollMarketRegimes());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initMarketRegimes($overrideExisting = true)
    {
        if (null !== $this->collMarketRegimes && !$overrideExisting) {
            return;
        }
        $this->collMarketRegimes = new PropelObjectCollection();
        $this->collMarketRegimes->setModel('MarketRegime');
    }

    /**
     * Gets an array of MarketRegime objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|MarketRegime[] List of MarketRegime objects
     * @throws PropelException
     */
    public function getMarketRegimes($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collMarketRegimesPartial && !$this->isNew();
        if (null === $this->collMarketRegimes || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collMarketRegimes) {
                // return empty collection
                $this->initMarketRegimes();
            } else {
                $collMarketRegimes = MarketRegimeQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collMarketRegimesPartial && count($collMarketRegimes)) {
                      $this->initMarketRegimes(false);

                      foreach ($collMarketRegimes as $obj) {
                        if (false == $this->collMarketRegimes->contains($obj)) {
                          $this->collMarketRegimes->append($obj);
                        }
                      }

                      $this->collMarketRegimesPartial = true;
                    }

                    $collMarketRegimes->getInternalIterator()->rewind();

                    return $collMarketRegimes;
                }

                if ($partial && $this->collMarketRegimes) {
                    foreach ($this->collMarketRegimes as $obj) {
                        if ($obj->isNew()) {
                            $collMarketRegimes[] = $obj;
                        }
                    }
                }

                $this->collMarketRegimes = $collMarketRegimes;
                $this->collMarketRegimesPartial = false;
            }
        }

        return $this->collMarketRegimes;
    }

    /**
     * Sets a collection of MarketRegime objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $marketRegimes A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setMarketRegimes(PropelCollection $marketRegimes, ?PropelPDO $con = null)
    {
        $marketRegimesToDelete = $this->getMarketRegimes(new Criteria(), $con)->diff($marketRegimes);


        $this->marketRegimesScheduledForDeletion = $marketRegimesToDelete;

        foreach ($marketRegimesToDelete as $marketRegimeRemoved) {
            $marketRegimeRemoved->setAuthyGroup(null);
        }

        $this->collMarketRegimes = null;
        foreach ($marketRegimes as $marketRegime) {
            $this->addMarketRegime($marketRegime);
        }

        $this->collMarketRegimes = $marketRegimes;
        $this->collMarketRegimesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related MarketRegime objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related MarketRegime objects.
     * @throws PropelException
     */
    public function countMarketRegimes(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collMarketRegimesPartial && !$this->isNew();
        if (null === $this->collMarketRegimes || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collMarketRegimes) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getMarketRegimes());
            }
            $query = MarketRegimeQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collMarketRegimes);
    }

    /**
     * Method called to associate a MarketRegime object to this object
     * through the MarketRegime foreign key attribute.
     *
     * @param    MarketRegime $l MarketRegime
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addMarketRegime(MarketRegime $l)
    {
        if ($this->collMarketRegimes === null) {
            $this->initMarketRegimes();
            $this->collMarketRegimesPartial = true;
        }

        if (!in_array($l, $this->collMarketRegimes->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddMarketRegime($l);

            if ($this->marketRegimesScheduledForDeletion and $this->marketRegimesScheduledForDeletion->contains($l)) {
                $this->marketRegimesScheduledForDeletion->remove($this->marketRegimesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	MarketRegime $marketRegime The marketRegime object to add.
     */
    protected function doAddMarketRegime($marketRegime)
    {
        $this->collMarketRegimes[]= $marketRegime;
        $marketRegime->setAuthyGroup($this);
    }

    /**
     * @param	MarketRegime $marketRegime The marketRegime object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeMarketRegime($marketRegime)
    {
        if ($this->getMarketRegimes()->contains($marketRegime)) {
            $this->collMarketRegimes->remove($this->collMarketRegimes->search($marketRegime));
            if (null === $this->marketRegimesScheduledForDeletion) {
                $this->marketRegimesScheduledForDeletion = clone $this->collMarketRegimes;
                $this->marketRegimesScheduledForDeletion->clear();
            }
            $this->marketRegimesScheduledForDeletion[]= $marketRegime;
            $marketRegime->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|MarketRegime[] List of MarketRegime objects
     */
    public function getMarketRegimesJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = MarketRegimeQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getMarketRegimes($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|MarketRegime[] List of MarketRegime objects
     */
    public function getMarketRegimesJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = MarketRegimeQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getMarketRegimes($query, $con);
    }

    /**
     * Clears out the collMarketCandles collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addMarketCandles()
     */
    public function clearMarketCandles()
    {
        $this->collMarketCandles = null; // important to set this to null since that means it is uninitialized
        $this->collMarketCandlesPartial = null;

        return $this;
    }

    /**
     * reset is the collMarketCandles collection loaded partially
     *
     * @return void
     */
    public function resetPartialMarketCandles($v = true)
    {
        $this->collMarketCandlesPartial = $v;
    }

    /**
     * Initializes the collMarketCandles collection.
     *
     * By default this just sets the collMarketCandles collection to an empty array (like clearcollMarketCandles());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initMarketCandles($overrideExisting = true)
    {
        if (null !== $this->collMarketCandles && !$overrideExisting) {
            return;
        }
        $this->collMarketCandles = new PropelObjectCollection();
        $this->collMarketCandles->setModel('MarketCandle');
    }

    /**
     * Gets an array of MarketCandle objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|MarketCandle[] List of MarketCandle objects
     * @throws PropelException
     */
    public function getMarketCandles($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collMarketCandlesPartial && !$this->isNew();
        if (null === $this->collMarketCandles || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collMarketCandles) {
                // return empty collection
                $this->initMarketCandles();
            } else {
                $collMarketCandles = MarketCandleQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collMarketCandlesPartial && count($collMarketCandles)) {
                      $this->initMarketCandles(false);

                      foreach ($collMarketCandles as $obj) {
                        if (false == $this->collMarketCandles->contains($obj)) {
                          $this->collMarketCandles->append($obj);
                        }
                      }

                      $this->collMarketCandlesPartial = true;
                    }

                    $collMarketCandles->getInternalIterator()->rewind();

                    return $collMarketCandles;
                }

                if ($partial && $this->collMarketCandles) {
                    foreach ($this->collMarketCandles as $obj) {
                        if ($obj->isNew()) {
                            $collMarketCandles[] = $obj;
                        }
                    }
                }

                $this->collMarketCandles = $collMarketCandles;
                $this->collMarketCandlesPartial = false;
            }
        }

        return $this->collMarketCandles;
    }

    /**
     * Sets a collection of MarketCandle objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $marketCandles A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setMarketCandles(PropelCollection $marketCandles, ?PropelPDO $con = null)
    {
        $marketCandlesToDelete = $this->getMarketCandles(new Criteria(), $con)->diff($marketCandles);


        $this->marketCandlesScheduledForDeletion = $marketCandlesToDelete;

        foreach ($marketCandlesToDelete as $marketCandleRemoved) {
            $marketCandleRemoved->setAuthyGroup(null);
        }

        $this->collMarketCandles = null;
        foreach ($marketCandles as $marketCandle) {
            $this->addMarketCandle($marketCandle);
        }

        $this->collMarketCandles = $marketCandles;
        $this->collMarketCandlesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related MarketCandle objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related MarketCandle objects.
     * @throws PropelException
     */
    public function countMarketCandles(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collMarketCandlesPartial && !$this->isNew();
        if (null === $this->collMarketCandles || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collMarketCandles) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getMarketCandles());
            }
            $query = MarketCandleQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collMarketCandles);
    }

    /**
     * Method called to associate a MarketCandle object to this object
     * through the MarketCandle foreign key attribute.
     *
     * @param    MarketCandle $l MarketCandle
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addMarketCandle(MarketCandle $l)
    {
        if ($this->collMarketCandles === null) {
            $this->initMarketCandles();
            $this->collMarketCandlesPartial = true;
        }

        if (!in_array($l, $this->collMarketCandles->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddMarketCandle($l);

            if ($this->marketCandlesScheduledForDeletion and $this->marketCandlesScheduledForDeletion->contains($l)) {
                $this->marketCandlesScheduledForDeletion->remove($this->marketCandlesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	MarketCandle $marketCandle The marketCandle object to add.
     */
    protected function doAddMarketCandle($marketCandle)
    {
        $this->collMarketCandles[]= $marketCandle;
        $marketCandle->setAuthyGroup($this);
    }

    /**
     * @param	MarketCandle $marketCandle The marketCandle object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeMarketCandle($marketCandle)
    {
        if ($this->getMarketCandles()->contains($marketCandle)) {
            $this->collMarketCandles->remove($this->collMarketCandles->search($marketCandle));
            if (null === $this->marketCandlesScheduledForDeletion) {
                $this->marketCandlesScheduledForDeletion = clone $this->collMarketCandles;
                $this->marketCandlesScheduledForDeletion->clear();
            }
            $this->marketCandlesScheduledForDeletion[]= $marketCandle;
            $marketCandle->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|MarketCandle[] List of MarketCandle objects
     */
    public function getMarketCandlesJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = MarketCandleQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getMarketCandles($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|MarketCandle[] List of MarketCandle objects
     */
    public function getMarketCandlesJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = MarketCandleQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getMarketCandles($query, $con);
    }

    /**
     * Clears out the collBotDecisions collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
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
     * If this AuthyGroup is new, it will return
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
                    ->filterByAuthyGroup($this)
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
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setBotDecisions(PropelCollection $botDecisions, ?PropelPDO $con = null)
    {
        $botDecisionsToDelete = $this->getBotDecisions(new Criteria(), $con)->diff($botDecisions);


        $this->botDecisionsScheduledForDeletion = $botDecisionsToDelete;

        foreach ($botDecisionsToDelete as $botDecisionRemoved) {
            $botDecisionRemoved->setAuthyGroup(null);
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
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collBotDecisions);
    }

    /**
     * Method called to associate a BotDecision object to this object
     * through the BotDecision foreign key attribute.
     *
     * @param    BotDecision $l BotDecision
     * @return AuthyGroup The current object (for fluent API support)
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
        $botDecision->setAuthyGroup($this);
    }

    /**
     * @param	BotDecision $botDecision The botDecision object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeBotDecision($botDecision)
    {
        if ($this->getBotDecisions()->contains($botDecision)) {
            $this->collBotDecisions->remove($this->collBotDecisions->search($botDecision));
            if (null === $this->botDecisionsScheduledForDeletion) {
                $this->botDecisionsScheduledForDeletion = clone $this->collBotDecisions;
                $this->botDecisionsScheduledForDeletion->clear();
            }
            $this->botDecisionsScheduledForDeletion[]= $botDecision;
            $botDecision->setAuthyGroup(null);
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
    public function getBotDecisionsJoinGridRun($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = BotDecisionQuery::create(null, $criteria);
        $query->joinWith('GridRun', $join_behavior);

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
     * Clears out the collMarketOutlooks collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addMarketOutlooks()
     */
    public function clearMarketOutlooks()
    {
        $this->collMarketOutlooks = null; // important to set this to null since that means it is uninitialized
        $this->collMarketOutlooksPartial = null;

        return $this;
    }

    /**
     * reset is the collMarketOutlooks collection loaded partially
     *
     * @return void
     */
    public function resetPartialMarketOutlooks($v = true)
    {
        $this->collMarketOutlooksPartial = $v;
    }

    /**
     * Initializes the collMarketOutlooks collection.
     *
     * By default this just sets the collMarketOutlooks collection to an empty array (like clearcollMarketOutlooks());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initMarketOutlooks($overrideExisting = true)
    {
        if (null !== $this->collMarketOutlooks && !$overrideExisting) {
            return;
        }
        $this->collMarketOutlooks = new PropelObjectCollection();
        $this->collMarketOutlooks->setModel('MarketOutlook');
    }

    /**
     * Gets an array of MarketOutlook objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|MarketOutlook[] List of MarketOutlook objects
     * @throws PropelException
     */
    public function getMarketOutlooks($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collMarketOutlooksPartial && !$this->isNew();
        if (null === $this->collMarketOutlooks || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collMarketOutlooks) {
                // return empty collection
                $this->initMarketOutlooks();
            } else {
                $collMarketOutlooks = MarketOutlookQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collMarketOutlooksPartial && count($collMarketOutlooks)) {
                      $this->initMarketOutlooks(false);

                      foreach ($collMarketOutlooks as $obj) {
                        if (false == $this->collMarketOutlooks->contains($obj)) {
                          $this->collMarketOutlooks->append($obj);
                        }
                      }

                      $this->collMarketOutlooksPartial = true;
                    }

                    $collMarketOutlooks->getInternalIterator()->rewind();

                    return $collMarketOutlooks;
                }

                if ($partial && $this->collMarketOutlooks) {
                    foreach ($this->collMarketOutlooks as $obj) {
                        if ($obj->isNew()) {
                            $collMarketOutlooks[] = $obj;
                        }
                    }
                }

                $this->collMarketOutlooks = $collMarketOutlooks;
                $this->collMarketOutlooksPartial = false;
            }
        }

        return $this->collMarketOutlooks;
    }

    /**
     * Sets a collection of MarketOutlook objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $marketOutlooks A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setMarketOutlooks(PropelCollection $marketOutlooks, ?PropelPDO $con = null)
    {
        $marketOutlooksToDelete = $this->getMarketOutlooks(new Criteria(), $con)->diff($marketOutlooks);


        $this->marketOutlooksScheduledForDeletion = $marketOutlooksToDelete;

        foreach ($marketOutlooksToDelete as $marketOutlookRemoved) {
            $marketOutlookRemoved->setAuthyGroup(null);
        }

        $this->collMarketOutlooks = null;
        foreach ($marketOutlooks as $marketOutlook) {
            $this->addMarketOutlook($marketOutlook);
        }

        $this->collMarketOutlooks = $marketOutlooks;
        $this->collMarketOutlooksPartial = false;

        return $this;
    }

    /**
     * Returns the number of related MarketOutlook objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related MarketOutlook objects.
     * @throws PropelException
     */
    public function countMarketOutlooks(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collMarketOutlooksPartial && !$this->isNew();
        if (null === $this->collMarketOutlooks || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collMarketOutlooks) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getMarketOutlooks());
            }
            $query = MarketOutlookQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collMarketOutlooks);
    }

    /**
     * Method called to associate a MarketOutlook object to this object
     * through the MarketOutlook foreign key attribute.
     *
     * @param    MarketOutlook $l MarketOutlook
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addMarketOutlook(MarketOutlook $l)
    {
        if ($this->collMarketOutlooks === null) {
            $this->initMarketOutlooks();
            $this->collMarketOutlooksPartial = true;
        }

        if (!in_array($l, $this->collMarketOutlooks->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddMarketOutlook($l);

            if ($this->marketOutlooksScheduledForDeletion and $this->marketOutlooksScheduledForDeletion->contains($l)) {
                $this->marketOutlooksScheduledForDeletion->remove($this->marketOutlooksScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	MarketOutlook $marketOutlook The marketOutlook object to add.
     */
    protected function doAddMarketOutlook($marketOutlook)
    {
        $this->collMarketOutlooks[]= $marketOutlook;
        $marketOutlook->setAuthyGroup($this);
    }

    /**
     * @param	MarketOutlook $marketOutlook The marketOutlook object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeMarketOutlook($marketOutlook)
    {
        if ($this->getMarketOutlooks()->contains($marketOutlook)) {
            $this->collMarketOutlooks->remove($this->collMarketOutlooks->search($marketOutlook));
            if (null === $this->marketOutlooksScheduledForDeletion) {
                $this->marketOutlooksScheduledForDeletion = clone $this->collMarketOutlooks;
                $this->marketOutlooksScheduledForDeletion->clear();
            }
            $this->marketOutlooksScheduledForDeletion[]= $marketOutlook;
            $marketOutlook->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|MarketOutlook[] List of MarketOutlook objects
     */
    public function getMarketOutlooksJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = MarketOutlookQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getMarketOutlooks($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|MarketOutlook[] List of MarketOutlook objects
     */
    public function getMarketOutlooksJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = MarketOutlookQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getMarketOutlooks($query, $con);
    }

    /**
     * Clears out the collMarketOutlookStates collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addMarketOutlookStates()
     */
    public function clearMarketOutlookStates()
    {
        $this->collMarketOutlookStates = null; // important to set this to null since that means it is uninitialized
        $this->collMarketOutlookStatesPartial = null;

        return $this;
    }

    /**
     * reset is the collMarketOutlookStates collection loaded partially
     *
     * @return void
     */
    public function resetPartialMarketOutlookStates($v = true)
    {
        $this->collMarketOutlookStatesPartial = $v;
    }

    /**
     * Initializes the collMarketOutlookStates collection.
     *
     * By default this just sets the collMarketOutlookStates collection to an empty array (like clearcollMarketOutlookStates());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initMarketOutlookStates($overrideExisting = true)
    {
        if (null !== $this->collMarketOutlookStates && !$overrideExisting) {
            return;
        }
        $this->collMarketOutlookStates = new PropelObjectCollection();
        $this->collMarketOutlookStates->setModel('MarketOutlookState');
    }

    /**
     * Gets an array of MarketOutlookState objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|MarketOutlookState[] List of MarketOutlookState objects
     * @throws PropelException
     */
    public function getMarketOutlookStates($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collMarketOutlookStatesPartial && !$this->isNew();
        if (null === $this->collMarketOutlookStates || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collMarketOutlookStates) {
                // return empty collection
                $this->initMarketOutlookStates();
            } else {
                $collMarketOutlookStates = MarketOutlookStateQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collMarketOutlookStatesPartial && count($collMarketOutlookStates)) {
                      $this->initMarketOutlookStates(false);

                      foreach ($collMarketOutlookStates as $obj) {
                        if (false == $this->collMarketOutlookStates->contains($obj)) {
                          $this->collMarketOutlookStates->append($obj);
                        }
                      }

                      $this->collMarketOutlookStatesPartial = true;
                    }

                    $collMarketOutlookStates->getInternalIterator()->rewind();

                    return $collMarketOutlookStates;
                }

                if ($partial && $this->collMarketOutlookStates) {
                    foreach ($this->collMarketOutlookStates as $obj) {
                        if ($obj->isNew()) {
                            $collMarketOutlookStates[] = $obj;
                        }
                    }
                }

                $this->collMarketOutlookStates = $collMarketOutlookStates;
                $this->collMarketOutlookStatesPartial = false;
            }
        }

        return $this->collMarketOutlookStates;
    }

    /**
     * Sets a collection of MarketOutlookState objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $marketOutlookStates A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setMarketOutlookStates(PropelCollection $marketOutlookStates, ?PropelPDO $con = null)
    {
        $marketOutlookStatesToDelete = $this->getMarketOutlookStates(new Criteria(), $con)->diff($marketOutlookStates);


        $this->marketOutlookStatesScheduledForDeletion = $marketOutlookStatesToDelete;

        foreach ($marketOutlookStatesToDelete as $marketOutlookStateRemoved) {
            $marketOutlookStateRemoved->setAuthyGroup(null);
        }

        $this->collMarketOutlookStates = null;
        foreach ($marketOutlookStates as $marketOutlookState) {
            $this->addMarketOutlookState($marketOutlookState);
        }

        $this->collMarketOutlookStates = $marketOutlookStates;
        $this->collMarketOutlookStatesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related MarketOutlookState objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related MarketOutlookState objects.
     * @throws PropelException
     */
    public function countMarketOutlookStates(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collMarketOutlookStatesPartial && !$this->isNew();
        if (null === $this->collMarketOutlookStates || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collMarketOutlookStates) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getMarketOutlookStates());
            }
            $query = MarketOutlookStateQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collMarketOutlookStates);
    }

    /**
     * Method called to associate a MarketOutlookState object to this object
     * through the MarketOutlookState foreign key attribute.
     *
     * @param    MarketOutlookState $l MarketOutlookState
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addMarketOutlookState(MarketOutlookState $l)
    {
        if ($this->collMarketOutlookStates === null) {
            $this->initMarketOutlookStates();
            $this->collMarketOutlookStatesPartial = true;
        }

        if (!in_array($l, $this->collMarketOutlookStates->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddMarketOutlookState($l);

            if ($this->marketOutlookStatesScheduledForDeletion and $this->marketOutlookStatesScheduledForDeletion->contains($l)) {
                $this->marketOutlookStatesScheduledForDeletion->remove($this->marketOutlookStatesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	MarketOutlookState $marketOutlookState The marketOutlookState object to add.
     */
    protected function doAddMarketOutlookState($marketOutlookState)
    {
        $this->collMarketOutlookStates[]= $marketOutlookState;
        $marketOutlookState->setAuthyGroup($this);
    }

    /**
     * @param	MarketOutlookState $marketOutlookState The marketOutlookState object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeMarketOutlookState($marketOutlookState)
    {
        if ($this->getMarketOutlookStates()->contains($marketOutlookState)) {
            $this->collMarketOutlookStates->remove($this->collMarketOutlookStates->search($marketOutlookState));
            if (null === $this->marketOutlookStatesScheduledForDeletion) {
                $this->marketOutlookStatesScheduledForDeletion = clone $this->collMarketOutlookStates;
                $this->marketOutlookStatesScheduledForDeletion->clear();
            }
            $this->marketOutlookStatesScheduledForDeletion[]= $marketOutlookState;
            $marketOutlookState->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|MarketOutlookState[] List of MarketOutlookState objects
     */
    public function getMarketOutlookStatesJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = MarketOutlookStateQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getMarketOutlookStates($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|MarketOutlookState[] List of MarketOutlookState objects
     */
    public function getMarketOutlookStatesJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = MarketOutlookStateQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getMarketOutlookStates($query, $con);
    }

    /**
     * Clears out the collWalletNavs collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addWalletNavs()
     */
    public function clearWalletNavs()
    {
        $this->collWalletNavs = null; // important to set this to null since that means it is uninitialized
        $this->collWalletNavsPartial = null;

        return $this;
    }

    /**
     * reset is the collWalletNavs collection loaded partially
     *
     * @return void
     */
    public function resetPartialWalletNavs($v = true)
    {
        $this->collWalletNavsPartial = $v;
    }

    /**
     * Initializes the collWalletNavs collection.
     *
     * By default this just sets the collWalletNavs collection to an empty array (like clearcollWalletNavs());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initWalletNavs($overrideExisting = true)
    {
        if (null !== $this->collWalletNavs && !$overrideExisting) {
            return;
        }
        $this->collWalletNavs = new PropelObjectCollection();
        $this->collWalletNavs->setModel('WalletNav');
    }

    /**
     * Gets an array of WalletNav objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|WalletNav[] List of WalletNav objects
     * @throws PropelException
     */
    public function getWalletNavs($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collWalletNavsPartial && !$this->isNew();
        if (null === $this->collWalletNavs || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collWalletNavs) {
                // return empty collection
                $this->initWalletNavs();
            } else {
                $collWalletNavs = WalletNavQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collWalletNavsPartial && count($collWalletNavs)) {
                      $this->initWalletNavs(false);

                      foreach ($collWalletNavs as $obj) {
                        if (false == $this->collWalletNavs->contains($obj)) {
                          $this->collWalletNavs->append($obj);
                        }
                      }

                      $this->collWalletNavsPartial = true;
                    }

                    $collWalletNavs->getInternalIterator()->rewind();

                    return $collWalletNavs;
                }

                if ($partial && $this->collWalletNavs) {
                    foreach ($this->collWalletNavs as $obj) {
                        if ($obj->isNew()) {
                            $collWalletNavs[] = $obj;
                        }
                    }
                }

                $this->collWalletNavs = $collWalletNavs;
                $this->collWalletNavsPartial = false;
            }
        }

        return $this->collWalletNavs;
    }

    /**
     * Sets a collection of WalletNav objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $walletNavs A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setWalletNavs(PropelCollection $walletNavs, ?PropelPDO $con = null)
    {
        $walletNavsToDelete = $this->getWalletNavs(new Criteria(), $con)->diff($walletNavs);


        $this->walletNavsScheduledForDeletion = $walletNavsToDelete;

        foreach ($walletNavsToDelete as $walletNavRemoved) {
            $walletNavRemoved->setAuthyGroup(null);
        }

        $this->collWalletNavs = null;
        foreach ($walletNavs as $walletNav) {
            $this->addWalletNav($walletNav);
        }

        $this->collWalletNavs = $walletNavs;
        $this->collWalletNavsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related WalletNav objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related WalletNav objects.
     * @throws PropelException
     */
    public function countWalletNavs(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collWalletNavsPartial && !$this->isNew();
        if (null === $this->collWalletNavs || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collWalletNavs) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getWalletNavs());
            }
            $query = WalletNavQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collWalletNavs);
    }

    /**
     * Method called to associate a WalletNav object to this object
     * through the WalletNav foreign key attribute.
     *
     * @param    WalletNav $l WalletNav
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addWalletNav(WalletNav $l)
    {
        if ($this->collWalletNavs === null) {
            $this->initWalletNavs();
            $this->collWalletNavsPartial = true;
        }

        if (!in_array($l, $this->collWalletNavs->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddWalletNav($l);

            if ($this->walletNavsScheduledForDeletion and $this->walletNavsScheduledForDeletion->contains($l)) {
                $this->walletNavsScheduledForDeletion->remove($this->walletNavsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	WalletNav $walletNav The walletNav object to add.
     */
    protected function doAddWalletNav($walletNav)
    {
        $this->collWalletNavs[]= $walletNav;
        $walletNav->setAuthyGroup($this);
    }

    /**
     * @param	WalletNav $walletNav The walletNav object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeWalletNav($walletNav)
    {
        if ($this->getWalletNavs()->contains($walletNav)) {
            $this->collWalletNavs->remove($this->collWalletNavs->search($walletNav));
            if (null === $this->walletNavsScheduledForDeletion) {
                $this->walletNavsScheduledForDeletion = clone $this->collWalletNavs;
                $this->walletNavsScheduledForDeletion->clear();
            }
            $this->walletNavsScheduledForDeletion[]= $walletNav;
            $walletNav->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|WalletNav[] List of WalletNav objects
     */
    public function getWalletNavsJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = WalletNavQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getWalletNavs($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|WalletNav[] List of WalletNav objects
     */
    public function getWalletNavsJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = WalletNavQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getWalletNavs($query, $con);
    }

    /**
     * Clears out the collAuthyGroupsRelatedByIdAuthyGroup collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addAuthyGroupsRelatedByIdAuthyGroup()
     */
    public function clearAuthyGroupsRelatedByIdAuthyGroup()
    {
        $this->collAuthyGroupsRelatedByIdAuthyGroup = null; // important to set this to null since that means it is uninitialized
        $this->collAuthyGroupsRelatedByIdAuthyGroupPartial = null;

        return $this;
    }

    /**
     * reset is the collAuthyGroupsRelatedByIdAuthyGroup collection loaded partially
     *
     * @return void
     */
    public function resetPartialAuthyGroupsRelatedByIdAuthyGroup($v = true)
    {
        $this->collAuthyGroupsRelatedByIdAuthyGroupPartial = $v;
    }

    /**
     * Initializes the collAuthyGroupsRelatedByIdAuthyGroup collection.
     *
     * By default this just sets the collAuthyGroupsRelatedByIdAuthyGroup collection to an empty array (like clearcollAuthyGroupsRelatedByIdAuthyGroup());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initAuthyGroupsRelatedByIdAuthyGroup($overrideExisting = true)
    {
        if (null !== $this->collAuthyGroupsRelatedByIdAuthyGroup && !$overrideExisting) {
            return;
        }
        $this->collAuthyGroupsRelatedByIdAuthyGroup = new PropelObjectCollection();
        $this->collAuthyGroupsRelatedByIdAuthyGroup->setModel('AuthyGroup');
    }

    /**
     * Gets an array of AuthyGroup objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|AuthyGroup[] List of AuthyGroup objects
     * @throws PropelException
     */
    public function getAuthyGroupsRelatedByIdAuthyGroup($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collAuthyGroupsRelatedByIdAuthyGroupPartial && !$this->isNew();
        if (null === $this->collAuthyGroupsRelatedByIdAuthyGroup || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collAuthyGroupsRelatedByIdAuthyGroup) {
                // return empty collection
                $this->initAuthyGroupsRelatedByIdAuthyGroup();
            } else {
                $collAuthyGroupsRelatedByIdAuthyGroup = AuthyGroupQuery::create(null, $criteria)
                    ->filterByAuthyGroupRelatedByIdGroupCreation($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collAuthyGroupsRelatedByIdAuthyGroupPartial && count($collAuthyGroupsRelatedByIdAuthyGroup)) {
                      $this->initAuthyGroupsRelatedByIdAuthyGroup(false);

                      foreach ($collAuthyGroupsRelatedByIdAuthyGroup as $obj) {
                        if (false == $this->collAuthyGroupsRelatedByIdAuthyGroup->contains($obj)) {
                          $this->collAuthyGroupsRelatedByIdAuthyGroup->append($obj);
                        }
                      }

                      $this->collAuthyGroupsRelatedByIdAuthyGroupPartial = true;
                    }

                    $collAuthyGroupsRelatedByIdAuthyGroup->getInternalIterator()->rewind();

                    return $collAuthyGroupsRelatedByIdAuthyGroup;
                }

                if ($partial && $this->collAuthyGroupsRelatedByIdAuthyGroup) {
                    foreach ($this->collAuthyGroupsRelatedByIdAuthyGroup as $obj) {
                        if ($obj->isNew()) {
                            $collAuthyGroupsRelatedByIdAuthyGroup[] = $obj;
                        }
                    }
                }

                $this->collAuthyGroupsRelatedByIdAuthyGroup = $collAuthyGroupsRelatedByIdAuthyGroup;
                $this->collAuthyGroupsRelatedByIdAuthyGroupPartial = false;
            }
        }

        return $this->collAuthyGroupsRelatedByIdAuthyGroup;
    }

    /**
     * Sets a collection of AuthyGroupRelatedByIdAuthyGroup objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $authyGroupsRelatedByIdAuthyGroup A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setAuthyGroupsRelatedByIdAuthyGroup(PropelCollection $authyGroupsRelatedByIdAuthyGroup, ?PropelPDO $con = null)
    {
        $authyGroupsRelatedByIdAuthyGroupToDelete = $this->getAuthyGroupsRelatedByIdAuthyGroup(new Criteria(), $con)->diff($authyGroupsRelatedByIdAuthyGroup);


        $this->authyGroupsRelatedByIdAuthyGroupScheduledForDeletion = $authyGroupsRelatedByIdAuthyGroupToDelete;

        foreach ($authyGroupsRelatedByIdAuthyGroupToDelete as $authyGroupRelatedByIdAuthyGroupRemoved) {
            $authyGroupRelatedByIdAuthyGroupRemoved->setAuthyGroupRelatedByIdGroupCreation(null);
        }

        $this->collAuthyGroupsRelatedByIdAuthyGroup = null;
        foreach ($authyGroupsRelatedByIdAuthyGroup as $authyGroupRelatedByIdAuthyGroup) {
            $this->addAuthyGroupRelatedByIdAuthyGroup($authyGroupRelatedByIdAuthyGroup);
        }

        $this->collAuthyGroupsRelatedByIdAuthyGroup = $authyGroupsRelatedByIdAuthyGroup;
        $this->collAuthyGroupsRelatedByIdAuthyGroupPartial = false;

        return $this;
    }

    /**
     * Returns the number of related AuthyGroup objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related AuthyGroup objects.
     * @throws PropelException
     */
    public function countAuthyGroupsRelatedByIdAuthyGroup(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collAuthyGroupsRelatedByIdAuthyGroupPartial && !$this->isNew();
        if (null === $this->collAuthyGroupsRelatedByIdAuthyGroup || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collAuthyGroupsRelatedByIdAuthyGroup) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getAuthyGroupsRelatedByIdAuthyGroup());
            }
            $query = AuthyGroupQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroupRelatedByIdGroupCreation($this)
                ->count($con);
        }

        return count($this->collAuthyGroupsRelatedByIdAuthyGroup);
    }

    /**
     * Method called to associate a AuthyGroup object to this object
     * through the AuthyGroup foreign key attribute.
     *
     * @param    AuthyGroup $l AuthyGroup
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addAuthyGroupRelatedByIdAuthyGroup(AuthyGroup $l)
    {
        if ($this->collAuthyGroupsRelatedByIdAuthyGroup === null) {
            $this->initAuthyGroupsRelatedByIdAuthyGroup();
            $this->collAuthyGroupsRelatedByIdAuthyGroupPartial = true;
        }

        if (!in_array($l, $this->collAuthyGroupsRelatedByIdAuthyGroup->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddAuthyGroupRelatedByIdAuthyGroup($l);

            if ($this->authyGroupsRelatedByIdAuthyGroupScheduledForDeletion and $this->authyGroupsRelatedByIdAuthyGroupScheduledForDeletion->contains($l)) {
                $this->authyGroupsRelatedByIdAuthyGroupScheduledForDeletion->remove($this->authyGroupsRelatedByIdAuthyGroupScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	AuthyGroupRelatedByIdAuthyGroup $authyGroupRelatedByIdAuthyGroup The authyGroupRelatedByIdAuthyGroup object to add.
     */
    protected function doAddAuthyGroupRelatedByIdAuthyGroup($authyGroupRelatedByIdAuthyGroup)
    {
        $this->collAuthyGroupsRelatedByIdAuthyGroup[]= $authyGroupRelatedByIdAuthyGroup;
        $authyGroupRelatedByIdAuthyGroup->setAuthyGroupRelatedByIdGroupCreation($this);
    }

    /**
     * @param	AuthyGroupRelatedByIdAuthyGroup $authyGroupRelatedByIdAuthyGroup The authyGroupRelatedByIdAuthyGroup object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeAuthyGroupRelatedByIdAuthyGroup($authyGroupRelatedByIdAuthyGroup)
    {
        if ($this->getAuthyGroupsRelatedByIdAuthyGroup()->contains($authyGroupRelatedByIdAuthyGroup)) {
            $this->collAuthyGroupsRelatedByIdAuthyGroup->remove($this->collAuthyGroupsRelatedByIdAuthyGroup->search($authyGroupRelatedByIdAuthyGroup));
            if (null === $this->authyGroupsRelatedByIdAuthyGroupScheduledForDeletion) {
                $this->authyGroupsRelatedByIdAuthyGroupScheduledForDeletion = clone $this->collAuthyGroupsRelatedByIdAuthyGroup;
                $this->authyGroupsRelatedByIdAuthyGroupScheduledForDeletion->clear();
            }
            $this->authyGroupsRelatedByIdAuthyGroupScheduledForDeletion[]= $authyGroupRelatedByIdAuthyGroup;
            $authyGroupRelatedByIdAuthyGroup->setAuthyGroupRelatedByIdGroupCreation(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|AuthyGroup[] List of AuthyGroup objects
     */
    public function getAuthyGroupsRelatedByIdAuthyGroupJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = AuthyGroupQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getAuthyGroupsRelatedByIdAuthyGroup($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|AuthyGroup[] List of AuthyGroup objects
     */
    public function getAuthyGroupsRelatedByIdAuthyGroupJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = AuthyGroupQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getAuthyGroupsRelatedByIdAuthyGroup($query, $con);
    }

    /**
     * Clears out the collAuthyGroupxesRelatedByIdGroupCreation collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addAuthyGroupxesRelatedByIdGroupCreation()
     */
    public function clearAuthyGroupxesRelatedByIdGroupCreation()
    {
        $this->collAuthyGroupxesRelatedByIdGroupCreation = null; // important to set this to null since that means it is uninitialized
        $this->collAuthyGroupxesRelatedByIdGroupCreationPartial = null;

        return $this;
    }

    /**
     * reset is the collAuthyGroupxesRelatedByIdGroupCreation collection loaded partially
     *
     * @return void
     */
    public function resetPartialAuthyGroupxesRelatedByIdGroupCreation($v = true)
    {
        $this->collAuthyGroupxesRelatedByIdGroupCreationPartial = $v;
    }

    /**
     * Initializes the collAuthyGroupxesRelatedByIdGroupCreation collection.
     *
     * By default this just sets the collAuthyGroupxesRelatedByIdGroupCreation collection to an empty array (like clearcollAuthyGroupxesRelatedByIdGroupCreation());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initAuthyGroupxesRelatedByIdGroupCreation($overrideExisting = true)
    {
        if (null !== $this->collAuthyGroupxesRelatedByIdGroupCreation && !$overrideExisting) {
            return;
        }
        $this->collAuthyGroupxesRelatedByIdGroupCreation = new PropelObjectCollection();
        $this->collAuthyGroupxesRelatedByIdGroupCreation->setModel('AuthyGroupX');
    }

    /**
     * Gets an array of AuthyGroupX objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|AuthyGroupX[] List of AuthyGroupX objects
     * @throws PropelException
     */
    public function getAuthyGroupxesRelatedByIdGroupCreation($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collAuthyGroupxesRelatedByIdGroupCreationPartial && !$this->isNew();
        if (null === $this->collAuthyGroupxesRelatedByIdGroupCreation || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collAuthyGroupxesRelatedByIdGroupCreation) {
                // return empty collection
                $this->initAuthyGroupxesRelatedByIdGroupCreation();
            } else {
                $collAuthyGroupxesRelatedByIdGroupCreation = AuthyGroupXQuery::create(null, $criteria)
                    ->filterByAuthyGroupRelatedByIdGroupCreation($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collAuthyGroupxesRelatedByIdGroupCreationPartial && count($collAuthyGroupxesRelatedByIdGroupCreation)) {
                      $this->initAuthyGroupxesRelatedByIdGroupCreation(false);

                      foreach ($collAuthyGroupxesRelatedByIdGroupCreation as $obj) {
                        if (false == $this->collAuthyGroupxesRelatedByIdGroupCreation->contains($obj)) {
                          $this->collAuthyGroupxesRelatedByIdGroupCreation->append($obj);
                        }
                      }

                      $this->collAuthyGroupxesRelatedByIdGroupCreationPartial = true;
                    }

                    $collAuthyGroupxesRelatedByIdGroupCreation->getInternalIterator()->rewind();

                    return $collAuthyGroupxesRelatedByIdGroupCreation;
                }

                if ($partial && $this->collAuthyGroupxesRelatedByIdGroupCreation) {
                    foreach ($this->collAuthyGroupxesRelatedByIdGroupCreation as $obj) {
                        if ($obj->isNew()) {
                            $collAuthyGroupxesRelatedByIdGroupCreation[] = $obj;
                        }
                    }
                }

                $this->collAuthyGroupxesRelatedByIdGroupCreation = $collAuthyGroupxesRelatedByIdGroupCreation;
                $this->collAuthyGroupxesRelatedByIdGroupCreationPartial = false;
            }
        }

        return $this->collAuthyGroupxesRelatedByIdGroupCreation;
    }

    /**
     * Sets a collection of AuthyGroupXRelatedByIdGroupCreation objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $authyGroupxesRelatedByIdGroupCreation A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setAuthyGroupxesRelatedByIdGroupCreation(PropelCollection $authyGroupxesRelatedByIdGroupCreation, ?PropelPDO $con = null)
    {
        $authyGroupxesRelatedByIdGroupCreationToDelete = $this->getAuthyGroupxesRelatedByIdGroupCreation(new Criteria(), $con)->diff($authyGroupxesRelatedByIdGroupCreation);


        $this->authyGroupxesRelatedByIdGroupCreationScheduledForDeletion = $authyGroupxesRelatedByIdGroupCreationToDelete;

        foreach ($authyGroupxesRelatedByIdGroupCreationToDelete as $authyGroupXRelatedByIdGroupCreationRemoved) {
            $authyGroupXRelatedByIdGroupCreationRemoved->setAuthyGroupRelatedByIdGroupCreation(null);
        }

        $this->collAuthyGroupxesRelatedByIdGroupCreation = null;
        foreach ($authyGroupxesRelatedByIdGroupCreation as $authyGroupXRelatedByIdGroupCreation) {
            $this->addAuthyGroupXRelatedByIdGroupCreation($authyGroupXRelatedByIdGroupCreation);
        }

        $this->collAuthyGroupxesRelatedByIdGroupCreation = $authyGroupxesRelatedByIdGroupCreation;
        $this->collAuthyGroupxesRelatedByIdGroupCreationPartial = false;

        return $this;
    }

    /**
     * Returns the number of related AuthyGroupX objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related AuthyGroupX objects.
     * @throws PropelException
     */
    public function countAuthyGroupxesRelatedByIdGroupCreation(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collAuthyGroupxesRelatedByIdGroupCreationPartial && !$this->isNew();
        if (null === $this->collAuthyGroupxesRelatedByIdGroupCreation || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collAuthyGroupxesRelatedByIdGroupCreation) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getAuthyGroupxesRelatedByIdGroupCreation());
            }
            $query = AuthyGroupXQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroupRelatedByIdGroupCreation($this)
                ->count($con);
        }

        return count($this->collAuthyGroupxesRelatedByIdGroupCreation);
    }

    /**
     * Method called to associate a AuthyGroupX object to this object
     * through the AuthyGroupX foreign key attribute.
     *
     * @param    AuthyGroupX $l AuthyGroupX
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addAuthyGroupXRelatedByIdGroupCreation(AuthyGroupX $l)
    {
        if ($this->collAuthyGroupxesRelatedByIdGroupCreation === null) {
            $this->initAuthyGroupxesRelatedByIdGroupCreation();
            $this->collAuthyGroupxesRelatedByIdGroupCreationPartial = true;
        }

        if (!in_array($l, $this->collAuthyGroupxesRelatedByIdGroupCreation->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddAuthyGroupXRelatedByIdGroupCreation($l);

            if ($this->authyGroupxesRelatedByIdGroupCreationScheduledForDeletion and $this->authyGroupxesRelatedByIdGroupCreationScheduledForDeletion->contains($l)) {
                $this->authyGroupxesRelatedByIdGroupCreationScheduledForDeletion->remove($this->authyGroupxesRelatedByIdGroupCreationScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	AuthyGroupXRelatedByIdGroupCreation $authyGroupXRelatedByIdGroupCreation The authyGroupXRelatedByIdGroupCreation object to add.
     */
    protected function doAddAuthyGroupXRelatedByIdGroupCreation($authyGroupXRelatedByIdGroupCreation)
    {
        $this->collAuthyGroupxesRelatedByIdGroupCreation[]= $authyGroupXRelatedByIdGroupCreation;
        $authyGroupXRelatedByIdGroupCreation->setAuthyGroupRelatedByIdGroupCreation($this);
    }

    /**
     * @param	AuthyGroupXRelatedByIdGroupCreation $authyGroupXRelatedByIdGroupCreation The authyGroupXRelatedByIdGroupCreation object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeAuthyGroupXRelatedByIdGroupCreation($authyGroupXRelatedByIdGroupCreation)
    {
        if ($this->getAuthyGroupxesRelatedByIdGroupCreation()->contains($authyGroupXRelatedByIdGroupCreation)) {
            $this->collAuthyGroupxesRelatedByIdGroupCreation->remove($this->collAuthyGroupxesRelatedByIdGroupCreation->search($authyGroupXRelatedByIdGroupCreation));
            if (null === $this->authyGroupxesRelatedByIdGroupCreationScheduledForDeletion) {
                $this->authyGroupxesRelatedByIdGroupCreationScheduledForDeletion = clone $this->collAuthyGroupxesRelatedByIdGroupCreation;
                $this->authyGroupxesRelatedByIdGroupCreationScheduledForDeletion->clear();
            }
            $this->authyGroupxesRelatedByIdGroupCreationScheduledForDeletion[]= $authyGroupXRelatedByIdGroupCreation;
            $authyGroupXRelatedByIdGroupCreation->setAuthyGroupRelatedByIdGroupCreation(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|AuthyGroupX[] List of AuthyGroupX objects
     */
    public function getAuthyGroupxesRelatedByIdGroupCreationJoinAuthyRelatedByIdAuthy($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = AuthyGroupXQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdAuthy', $join_behavior);

        return $this->getAuthyGroupxesRelatedByIdGroupCreation($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|AuthyGroupX[] List of AuthyGroupX objects
     */
    public function getAuthyGroupxesRelatedByIdGroupCreationJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = AuthyGroupXQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getAuthyGroupxesRelatedByIdGroupCreation($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|AuthyGroupX[] List of AuthyGroupX objects
     */
    public function getAuthyGroupxesRelatedByIdGroupCreationJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = AuthyGroupXQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getAuthyGroupxesRelatedByIdGroupCreation($query, $con);
    }

    /**
     * Clears out the collConfigs collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addConfigs()
     */
    public function clearConfigs()
    {
        $this->collConfigs = null; // important to set this to null since that means it is uninitialized
        $this->collConfigsPartial = null;

        return $this;
    }

    /**
     * reset is the collConfigs collection loaded partially
     *
     * @return void
     */
    public function resetPartialConfigs($v = true)
    {
        $this->collConfigsPartial = $v;
    }

    /**
     * Initializes the collConfigs collection.
     *
     * By default this just sets the collConfigs collection to an empty array (like clearcollConfigs());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initConfigs($overrideExisting = true)
    {
        if (null !== $this->collConfigs && !$overrideExisting) {
            return;
        }
        $this->collConfigs = new PropelObjectCollection();
        $this->collConfigs->setModel('Config');
    }

    /**
     * Gets an array of Config objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|Config[] List of Config objects
     * @throws PropelException
     */
    public function getConfigs($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collConfigsPartial && !$this->isNew();
        if (null === $this->collConfigs || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collConfigs) {
                // return empty collection
                $this->initConfigs();
            } else {
                $collConfigs = ConfigQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collConfigsPartial && count($collConfigs)) {
                      $this->initConfigs(false);

                      foreach ($collConfigs as $obj) {
                        if (false == $this->collConfigs->contains($obj)) {
                          $this->collConfigs->append($obj);
                        }
                      }

                      $this->collConfigsPartial = true;
                    }

                    $collConfigs->getInternalIterator()->rewind();

                    return $collConfigs;
                }

                if ($partial && $this->collConfigs) {
                    foreach ($this->collConfigs as $obj) {
                        if ($obj->isNew()) {
                            $collConfigs[] = $obj;
                        }
                    }
                }

                $this->collConfigs = $collConfigs;
                $this->collConfigsPartial = false;
            }
        }

        return $this->collConfigs;
    }

    /**
     * Sets a collection of Config objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $configs A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setConfigs(PropelCollection $configs, ?PropelPDO $con = null)
    {
        $configsToDelete = $this->getConfigs(new Criteria(), $con)->diff($configs);


        $this->configsScheduledForDeletion = $configsToDelete;

        foreach ($configsToDelete as $configRemoved) {
            $configRemoved->setAuthyGroup(null);
        }

        $this->collConfigs = null;
        foreach ($configs as $config) {
            $this->addConfig($config);
        }

        $this->collConfigs = $configs;
        $this->collConfigsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Config objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related Config objects.
     * @throws PropelException
     */
    public function countConfigs(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collConfigsPartial && !$this->isNew();
        if (null === $this->collConfigs || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collConfigs) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getConfigs());
            }
            $query = ConfigQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collConfigs);
    }

    /**
     * Method called to associate a Config object to this object
     * through the Config foreign key attribute.
     *
     * @param    Config $l Config
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addConfig(Config $l)
    {
        if ($this->collConfigs === null) {
            $this->initConfigs();
            $this->collConfigsPartial = true;
        }

        if (!in_array($l, $this->collConfigs->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddConfig($l);

            if ($this->configsScheduledForDeletion and $this->configsScheduledForDeletion->contains($l)) {
                $this->configsScheduledForDeletion->remove($this->configsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	Config $config The config object to add.
     */
    protected function doAddConfig($config)
    {
        $this->collConfigs[]= $config;
        $config->setAuthyGroup($this);
    }

    /**
     * @param	Config $config The config object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeConfig($config)
    {
        if ($this->getConfigs()->contains($config)) {
            $this->collConfigs->remove($this->collConfigs->search($config));
            if (null === $this->configsScheduledForDeletion) {
                $this->configsScheduledForDeletion = clone $this->collConfigs;
                $this->configsScheduledForDeletion->clear();
            }
            $this->configsScheduledForDeletion[]= $config;
            $config->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|Config[] List of Config objects
     */
    public function getConfigsJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = ConfigQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getConfigs($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|Config[] List of Config objects
     */
    public function getConfigsJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = ConfigQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getConfigs($query, $con);
    }

    /**
     * Clears out the collApiRbacs collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addApiRbacs()
     */
    public function clearApiRbacs()
    {
        $this->collApiRbacs = null; // important to set this to null since that means it is uninitialized
        $this->collApiRbacsPartial = null;

        return $this;
    }

    /**
     * reset is the collApiRbacs collection loaded partially
     *
     * @return void
     */
    public function resetPartialApiRbacs($v = true)
    {
        $this->collApiRbacsPartial = $v;
    }

    /**
     * Initializes the collApiRbacs collection.
     *
     * By default this just sets the collApiRbacs collection to an empty array (like clearcollApiRbacs());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initApiRbacs($overrideExisting = true)
    {
        if (null !== $this->collApiRbacs && !$overrideExisting) {
            return;
        }
        $this->collApiRbacs = new PropelObjectCollection();
        $this->collApiRbacs->setModel('ApiRbac');
    }

    /**
     * Gets an array of ApiRbac objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|ApiRbac[] List of ApiRbac objects
     * @throws PropelException
     */
    public function getApiRbacs($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collApiRbacsPartial && !$this->isNew();
        if (null === $this->collApiRbacs || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collApiRbacs) {
                // return empty collection
                $this->initApiRbacs();
            } else {
                $collApiRbacs = ApiRbacQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collApiRbacsPartial && count($collApiRbacs)) {
                      $this->initApiRbacs(false);

                      foreach ($collApiRbacs as $obj) {
                        if (false == $this->collApiRbacs->contains($obj)) {
                          $this->collApiRbacs->append($obj);
                        }
                      }

                      $this->collApiRbacsPartial = true;
                    }

                    $collApiRbacs->getInternalIterator()->rewind();

                    return $collApiRbacs;
                }

                if ($partial && $this->collApiRbacs) {
                    foreach ($this->collApiRbacs as $obj) {
                        if ($obj->isNew()) {
                            $collApiRbacs[] = $obj;
                        }
                    }
                }

                $this->collApiRbacs = $collApiRbacs;
                $this->collApiRbacsPartial = false;
            }
        }

        return $this->collApiRbacs;
    }

    /**
     * Sets a collection of ApiRbac objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $apiRbacs A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setApiRbacs(PropelCollection $apiRbacs, ?PropelPDO $con = null)
    {
        $apiRbacsToDelete = $this->getApiRbacs(new Criteria(), $con)->diff($apiRbacs);


        $this->apiRbacsScheduledForDeletion = $apiRbacsToDelete;

        foreach ($apiRbacsToDelete as $apiRbacRemoved) {
            $apiRbacRemoved->setAuthyGroup(null);
        }

        $this->collApiRbacs = null;
        foreach ($apiRbacs as $apiRbac) {
            $this->addApiRbac($apiRbac);
        }

        $this->collApiRbacs = $apiRbacs;
        $this->collApiRbacsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related ApiRbac objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related ApiRbac objects.
     * @throws PropelException
     */
    public function countApiRbacs(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collApiRbacsPartial && !$this->isNew();
        if (null === $this->collApiRbacs || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collApiRbacs) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getApiRbacs());
            }
            $query = ApiRbacQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collApiRbacs);
    }

    /**
     * Method called to associate a ApiRbac object to this object
     * through the ApiRbac foreign key attribute.
     *
     * @param    ApiRbac $l ApiRbac
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addApiRbac(ApiRbac $l)
    {
        if ($this->collApiRbacs === null) {
            $this->initApiRbacs();
            $this->collApiRbacsPartial = true;
        }

        if (!in_array($l, $this->collApiRbacs->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddApiRbac($l);

            if ($this->apiRbacsScheduledForDeletion and $this->apiRbacsScheduledForDeletion->contains($l)) {
                $this->apiRbacsScheduledForDeletion->remove($this->apiRbacsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	ApiRbac $apiRbac The apiRbac object to add.
     */
    protected function doAddApiRbac($apiRbac)
    {
        $this->collApiRbacs[]= $apiRbac;
        $apiRbac->setAuthyGroup($this);
    }

    /**
     * @param	ApiRbac $apiRbac The apiRbac object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeApiRbac($apiRbac)
    {
        if ($this->getApiRbacs()->contains($apiRbac)) {
            $this->collApiRbacs->remove($this->collApiRbacs->search($apiRbac));
            if (null === $this->apiRbacsScheduledForDeletion) {
                $this->apiRbacsScheduledForDeletion = clone $this->collApiRbacs;
                $this->apiRbacsScheduledForDeletion->clear();
            }
            $this->apiRbacsScheduledForDeletion[]= $apiRbac;
            $apiRbac->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|ApiRbac[] List of ApiRbac objects
     */
    public function getApiRbacsJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = ApiRbacQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getApiRbacs($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|ApiRbac[] List of ApiRbac objects
     */
    public function getApiRbacsJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = ApiRbacQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getApiRbacs($query, $con);
    }

    /**
     * Clears out the collTemplates collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addTemplates()
     */
    public function clearTemplates()
    {
        $this->collTemplates = null; // important to set this to null since that means it is uninitialized
        $this->collTemplatesPartial = null;

        return $this;
    }

    /**
     * reset is the collTemplates collection loaded partially
     *
     * @return void
     */
    public function resetPartialTemplates($v = true)
    {
        $this->collTemplatesPartial = $v;
    }

    /**
     * Initializes the collTemplates collection.
     *
     * By default this just sets the collTemplates collection to an empty array (like clearcollTemplates());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initTemplates($overrideExisting = true)
    {
        if (null !== $this->collTemplates && !$overrideExisting) {
            return;
        }
        $this->collTemplates = new PropelObjectCollection();
        $this->collTemplates->setModel('Template');
    }

    /**
     * Gets an array of Template objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|Template[] List of Template objects
     * @throws PropelException
     */
    public function getTemplates($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collTemplatesPartial && !$this->isNew();
        if (null === $this->collTemplates || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collTemplates) {
                // return empty collection
                $this->initTemplates();
            } else {
                $collTemplates = TemplateQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collTemplatesPartial && count($collTemplates)) {
                      $this->initTemplates(false);

                      foreach ($collTemplates as $obj) {
                        if (false == $this->collTemplates->contains($obj)) {
                          $this->collTemplates->append($obj);
                        }
                      }

                      $this->collTemplatesPartial = true;
                    }

                    $collTemplates->getInternalIterator()->rewind();

                    return $collTemplates;
                }

                if ($partial && $this->collTemplates) {
                    foreach ($this->collTemplates as $obj) {
                        if ($obj->isNew()) {
                            $collTemplates[] = $obj;
                        }
                    }
                }

                $this->collTemplates = $collTemplates;
                $this->collTemplatesPartial = false;
            }
        }

        return $this->collTemplates;
    }

    /**
     * Sets a collection of Template objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $templates A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setTemplates(PropelCollection $templates, ?PropelPDO $con = null)
    {
        $templatesToDelete = $this->getTemplates(new Criteria(), $con)->diff($templates);


        $this->templatesScheduledForDeletion = $templatesToDelete;

        foreach ($templatesToDelete as $templateRemoved) {
            $templateRemoved->setAuthyGroup(null);
        }

        $this->collTemplates = null;
        foreach ($templates as $template) {
            $this->addTemplate($template);
        }

        $this->collTemplates = $templates;
        $this->collTemplatesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Template objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related Template objects.
     * @throws PropelException
     */
    public function countTemplates(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collTemplatesPartial && !$this->isNew();
        if (null === $this->collTemplates || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collTemplates) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getTemplates());
            }
            $query = TemplateQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collTemplates);
    }

    /**
     * Method called to associate a Template object to this object
     * through the Template foreign key attribute.
     *
     * @param    Template $l Template
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addTemplate(Template $l)
    {
        if ($this->collTemplates === null) {
            $this->initTemplates();
            $this->collTemplatesPartial = true;
        }

        if (!in_array($l, $this->collTemplates->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddTemplate($l);

            if ($this->templatesScheduledForDeletion and $this->templatesScheduledForDeletion->contains($l)) {
                $this->templatesScheduledForDeletion->remove($this->templatesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	Template $template The template object to add.
     */
    protected function doAddTemplate($template)
    {
        $this->collTemplates[]= $template;
        $template->setAuthyGroup($this);
    }

    /**
     * @param	Template $template The template object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeTemplate($template)
    {
        if ($this->getTemplates()->contains($template)) {
            $this->collTemplates->remove($this->collTemplates->search($template));
            if (null === $this->templatesScheduledForDeletion) {
                $this->templatesScheduledForDeletion = clone $this->collTemplates;
                $this->templatesScheduledForDeletion->clear();
            }
            $this->templatesScheduledForDeletion[]= $template;
            $template->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|Template[] List of Template objects
     */
    public function getTemplatesJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = TemplateQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getTemplates($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|Template[] List of Template objects
     */
    public function getTemplatesJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = TemplateQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getTemplates($query, $con);
    }

    /**
     * Clears out the collTemplateFiles collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addTemplateFiles()
     */
    public function clearTemplateFiles()
    {
        $this->collTemplateFiles = null; // important to set this to null since that means it is uninitialized
        $this->collTemplateFilesPartial = null;

        return $this;
    }

    /**
     * reset is the collTemplateFiles collection loaded partially
     *
     * @return void
     */
    public function resetPartialTemplateFiles($v = true)
    {
        $this->collTemplateFilesPartial = $v;
    }

    /**
     * Initializes the collTemplateFiles collection.
     *
     * By default this just sets the collTemplateFiles collection to an empty array (like clearcollTemplateFiles());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initTemplateFiles($overrideExisting = true)
    {
        if (null !== $this->collTemplateFiles && !$overrideExisting) {
            return;
        }
        $this->collTemplateFiles = new PropelObjectCollection();
        $this->collTemplateFiles->setModel('TemplateFile');
    }

    /**
     * Gets an array of TemplateFile objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|TemplateFile[] List of TemplateFile objects
     * @throws PropelException
     */
    public function getTemplateFiles($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collTemplateFilesPartial && !$this->isNew();
        if (null === $this->collTemplateFiles || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collTemplateFiles) {
                // return empty collection
                $this->initTemplateFiles();
            } else {
                $collTemplateFiles = TemplateFileQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collTemplateFilesPartial && count($collTemplateFiles)) {
                      $this->initTemplateFiles(false);

                      foreach ($collTemplateFiles as $obj) {
                        if (false == $this->collTemplateFiles->contains($obj)) {
                          $this->collTemplateFiles->append($obj);
                        }
                      }

                      $this->collTemplateFilesPartial = true;
                    }

                    $collTemplateFiles->getInternalIterator()->rewind();

                    return $collTemplateFiles;
                }

                if ($partial && $this->collTemplateFiles) {
                    foreach ($this->collTemplateFiles as $obj) {
                        if ($obj->isNew()) {
                            $collTemplateFiles[] = $obj;
                        }
                    }
                }

                $this->collTemplateFiles = $collTemplateFiles;
                $this->collTemplateFilesPartial = false;
            }
        }

        return $this->collTemplateFiles;
    }

    /**
     * Sets a collection of TemplateFile objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $templateFiles A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setTemplateFiles(PropelCollection $templateFiles, ?PropelPDO $con = null)
    {
        $templateFilesToDelete = $this->getTemplateFiles(new Criteria(), $con)->diff($templateFiles);


        $this->templateFilesScheduledForDeletion = $templateFilesToDelete;

        foreach ($templateFilesToDelete as $templateFileRemoved) {
            $templateFileRemoved->setAuthyGroup(null);
        }

        $this->collTemplateFiles = null;
        foreach ($templateFiles as $templateFile) {
            $this->addTemplateFile($templateFile);
        }

        $this->collTemplateFiles = $templateFiles;
        $this->collTemplateFilesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related TemplateFile objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related TemplateFile objects.
     * @throws PropelException
     */
    public function countTemplateFiles(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collTemplateFilesPartial && !$this->isNew();
        if (null === $this->collTemplateFiles || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collTemplateFiles) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getTemplateFiles());
            }
            $query = TemplateFileQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collTemplateFiles);
    }

    /**
     * Method called to associate a TemplateFile object to this object
     * through the TemplateFile foreign key attribute.
     *
     * @param    TemplateFile $l TemplateFile
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addTemplateFile(TemplateFile $l)
    {
        if ($this->collTemplateFiles === null) {
            $this->initTemplateFiles();
            $this->collTemplateFilesPartial = true;
        }

        if (!in_array($l, $this->collTemplateFiles->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddTemplateFile($l);

            if ($this->templateFilesScheduledForDeletion and $this->templateFilesScheduledForDeletion->contains($l)) {
                $this->templateFilesScheduledForDeletion->remove($this->templateFilesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	TemplateFile $templateFile The templateFile object to add.
     */
    protected function doAddTemplateFile($templateFile)
    {
        $this->collTemplateFiles[]= $templateFile;
        $templateFile->setAuthyGroup($this);
    }

    /**
     * @param	TemplateFile $templateFile The templateFile object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeTemplateFile($templateFile)
    {
        if ($this->getTemplateFiles()->contains($templateFile)) {
            $this->collTemplateFiles->remove($this->collTemplateFiles->search($templateFile));
            if (null === $this->templateFilesScheduledForDeletion) {
                $this->templateFilesScheduledForDeletion = clone $this->collTemplateFiles;
                $this->templateFilesScheduledForDeletion->clear();
            }
            $this->templateFilesScheduledForDeletion[]= $templateFile;
            $templateFile->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|TemplateFile[] List of TemplateFile objects
     */
    public function getTemplateFilesJoinTemplate($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = TemplateFileQuery::create(null, $criteria);
        $query->joinWith('Template', $join_behavior);

        return $this->getTemplateFiles($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|TemplateFile[] List of TemplateFile objects
     */
    public function getTemplateFilesJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = TemplateFileQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getTemplateFiles($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|TemplateFile[] List of TemplateFile objects
     */
    public function getTemplateFilesJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = TemplateFileQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getTemplateFiles($query, $con);
    }

    /**
     * Clears out the collAuthyRefreshTokens collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addAuthyRefreshTokens()
     */
    public function clearAuthyRefreshTokens()
    {
        $this->collAuthyRefreshTokens = null; // important to set this to null since that means it is uninitialized
        $this->collAuthyRefreshTokensPartial = null;

        return $this;
    }

    /**
     * reset is the collAuthyRefreshTokens collection loaded partially
     *
     * @return void
     */
    public function resetPartialAuthyRefreshTokens($v = true)
    {
        $this->collAuthyRefreshTokensPartial = $v;
    }

    /**
     * Initializes the collAuthyRefreshTokens collection.
     *
     * By default this just sets the collAuthyRefreshTokens collection to an empty array (like clearcollAuthyRefreshTokens());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initAuthyRefreshTokens($overrideExisting = true)
    {
        if (null !== $this->collAuthyRefreshTokens && !$overrideExisting) {
            return;
        }
        $this->collAuthyRefreshTokens = new PropelObjectCollection();
        $this->collAuthyRefreshTokens->setModel('AuthyRefreshToken');
    }

    /**
     * Gets an array of AuthyRefreshToken objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|AuthyRefreshToken[] List of AuthyRefreshToken objects
     * @throws PropelException
     */
    public function getAuthyRefreshTokens($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collAuthyRefreshTokensPartial && !$this->isNew();
        if (null === $this->collAuthyRefreshTokens || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collAuthyRefreshTokens) {
                // return empty collection
                $this->initAuthyRefreshTokens();
            } else {
                $collAuthyRefreshTokens = AuthyRefreshTokenQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collAuthyRefreshTokensPartial && count($collAuthyRefreshTokens)) {
                      $this->initAuthyRefreshTokens(false);

                      foreach ($collAuthyRefreshTokens as $obj) {
                        if (false == $this->collAuthyRefreshTokens->contains($obj)) {
                          $this->collAuthyRefreshTokens->append($obj);
                        }
                      }

                      $this->collAuthyRefreshTokensPartial = true;
                    }

                    $collAuthyRefreshTokens->getInternalIterator()->rewind();

                    return $collAuthyRefreshTokens;
                }

                if ($partial && $this->collAuthyRefreshTokens) {
                    foreach ($this->collAuthyRefreshTokens as $obj) {
                        if ($obj->isNew()) {
                            $collAuthyRefreshTokens[] = $obj;
                        }
                    }
                }

                $this->collAuthyRefreshTokens = $collAuthyRefreshTokens;
                $this->collAuthyRefreshTokensPartial = false;
            }
        }

        return $this->collAuthyRefreshTokens;
    }

    /**
     * Sets a collection of AuthyRefreshToken objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $authyRefreshTokens A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setAuthyRefreshTokens(PropelCollection $authyRefreshTokens, ?PropelPDO $con = null)
    {
        $authyRefreshTokensToDelete = $this->getAuthyRefreshTokens(new Criteria(), $con)->diff($authyRefreshTokens);


        $this->authyRefreshTokensScheduledForDeletion = $authyRefreshTokensToDelete;

        foreach ($authyRefreshTokensToDelete as $authyRefreshTokenRemoved) {
            $authyRefreshTokenRemoved->setAuthyGroup(null);
        }

        $this->collAuthyRefreshTokens = null;
        foreach ($authyRefreshTokens as $authyRefreshToken) {
            $this->addAuthyRefreshToken($authyRefreshToken);
        }

        $this->collAuthyRefreshTokens = $authyRefreshTokens;
        $this->collAuthyRefreshTokensPartial = false;

        return $this;
    }

    /**
     * Returns the number of related AuthyRefreshToken objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related AuthyRefreshToken objects.
     * @throws PropelException
     */
    public function countAuthyRefreshTokens(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collAuthyRefreshTokensPartial && !$this->isNew();
        if (null === $this->collAuthyRefreshTokens || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collAuthyRefreshTokens) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getAuthyRefreshTokens());
            }
            $query = AuthyRefreshTokenQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collAuthyRefreshTokens);
    }

    /**
     * Method called to associate a AuthyRefreshToken object to this object
     * through the AuthyRefreshToken foreign key attribute.
     *
     * @param    AuthyRefreshToken $l AuthyRefreshToken
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addAuthyRefreshToken(AuthyRefreshToken $l)
    {
        if ($this->collAuthyRefreshTokens === null) {
            $this->initAuthyRefreshTokens();
            $this->collAuthyRefreshTokensPartial = true;
        }

        if (!in_array($l, $this->collAuthyRefreshTokens->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddAuthyRefreshToken($l);

            if ($this->authyRefreshTokensScheduledForDeletion and $this->authyRefreshTokensScheduledForDeletion->contains($l)) {
                $this->authyRefreshTokensScheduledForDeletion->remove($this->authyRefreshTokensScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	AuthyRefreshToken $authyRefreshToken The authyRefreshToken object to add.
     */
    protected function doAddAuthyRefreshToken($authyRefreshToken)
    {
        $this->collAuthyRefreshTokens[]= $authyRefreshToken;
        $authyRefreshToken->setAuthyGroup($this);
    }

    /**
     * @param	AuthyRefreshToken $authyRefreshToken The authyRefreshToken object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeAuthyRefreshToken($authyRefreshToken)
    {
        if ($this->getAuthyRefreshTokens()->contains($authyRefreshToken)) {
            $this->collAuthyRefreshTokens->remove($this->collAuthyRefreshTokens->search($authyRefreshToken));
            if (null === $this->authyRefreshTokensScheduledForDeletion) {
                $this->authyRefreshTokensScheduledForDeletion = clone $this->collAuthyRefreshTokens;
                $this->authyRefreshTokensScheduledForDeletion->clear();
            }
            $this->authyRefreshTokensScheduledForDeletion[]= $authyRefreshToken;
            $authyRefreshToken->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|AuthyRefreshToken[] List of AuthyRefreshToken objects
     */
    public function getAuthyRefreshTokensJoinAuthyRelatedByIdAuthy($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = AuthyRefreshTokenQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdAuthy', $join_behavior);

        return $this->getAuthyRefreshTokens($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|AuthyRefreshToken[] List of AuthyRefreshToken objects
     */
    public function getAuthyRefreshTokensJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = AuthyRefreshTokenQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getAuthyRefreshTokens($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|AuthyRefreshToken[] List of AuthyRefreshToken objects
     */
    public function getAuthyRefreshTokensJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = AuthyRefreshTokenQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getAuthyRefreshTokens($query, $con);
    }

    /**
     * Clears out the collGridRunAudits collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addGridRunAudits()
     */
    public function clearGridRunAudits()
    {
        $this->collGridRunAudits = null; // important to set this to null since that means it is uninitialized
        $this->collGridRunAuditsPartial = null;

        return $this;
    }

    /**
     * reset is the collGridRunAudits collection loaded partially
     *
     * @return void
     */
    public function resetPartialGridRunAudits($v = true)
    {
        $this->collGridRunAuditsPartial = $v;
    }

    /**
     * Initializes the collGridRunAudits collection.
     *
     * By default this just sets the collGridRunAudits collection to an empty array (like clearcollGridRunAudits());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initGridRunAudits($overrideExisting = true)
    {
        if (null !== $this->collGridRunAudits && !$overrideExisting) {
            return;
        }
        $this->collGridRunAudits = new PropelObjectCollection();
        $this->collGridRunAudits->setModel('GridRunAudit');
    }

    /**
     * Gets an array of GridRunAudit objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|GridRunAudit[] List of GridRunAudit objects
     * @throws PropelException
     */
    public function getGridRunAudits($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collGridRunAuditsPartial && !$this->isNew();
        if (null === $this->collGridRunAudits || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collGridRunAudits) {
                // return empty collection
                $this->initGridRunAudits();
            } else {
                $collGridRunAudits = GridRunAuditQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collGridRunAuditsPartial && count($collGridRunAudits)) {
                      $this->initGridRunAudits(false);

                      foreach ($collGridRunAudits as $obj) {
                        if (false == $this->collGridRunAudits->contains($obj)) {
                          $this->collGridRunAudits->append($obj);
                        }
                      }

                      $this->collGridRunAuditsPartial = true;
                    }

                    $collGridRunAudits->getInternalIterator()->rewind();

                    return $collGridRunAudits;
                }

                if ($partial && $this->collGridRunAudits) {
                    foreach ($this->collGridRunAudits as $obj) {
                        if ($obj->isNew()) {
                            $collGridRunAudits[] = $obj;
                        }
                    }
                }

                $this->collGridRunAudits = $collGridRunAudits;
                $this->collGridRunAuditsPartial = false;
            }
        }

        return $this->collGridRunAudits;
    }

    /**
     * Sets a collection of GridRunAudit objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $gridRunAudits A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setGridRunAudits(PropelCollection $gridRunAudits, ?PropelPDO $con = null)
    {
        $gridRunAuditsToDelete = $this->getGridRunAudits(new Criteria(), $con)->diff($gridRunAudits);


        $this->gridRunAuditsScheduledForDeletion = $gridRunAuditsToDelete;

        foreach ($gridRunAuditsToDelete as $gridRunAuditRemoved) {
            $gridRunAuditRemoved->setAuthyGroup(null);
        }

        $this->collGridRunAudits = null;
        foreach ($gridRunAudits as $gridRunAudit) {
            $this->addGridRunAudit($gridRunAudit);
        }

        $this->collGridRunAudits = $gridRunAudits;
        $this->collGridRunAuditsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related GridRunAudit objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related GridRunAudit objects.
     * @throws PropelException
     */
    public function countGridRunAudits(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collGridRunAuditsPartial && !$this->isNew();
        if (null === $this->collGridRunAudits || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collGridRunAudits) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getGridRunAudits());
            }
            $query = GridRunAuditQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collGridRunAudits);
    }

    /**
     * Method called to associate a GridRunAudit object to this object
     * through the GridRunAudit foreign key attribute.
     *
     * @param    GridRunAudit $l GridRunAudit
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addGridRunAudit(GridRunAudit $l)
    {
        if ($this->collGridRunAudits === null) {
            $this->initGridRunAudits();
            $this->collGridRunAuditsPartial = true;
        }

        if (!in_array($l, $this->collGridRunAudits->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddGridRunAudit($l);

            if ($this->gridRunAuditsScheduledForDeletion and $this->gridRunAuditsScheduledForDeletion->contains($l)) {
                $this->gridRunAuditsScheduledForDeletion->remove($this->gridRunAuditsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	GridRunAudit $gridRunAudit The gridRunAudit object to add.
     */
    protected function doAddGridRunAudit($gridRunAudit)
    {
        $this->collGridRunAudits[]= $gridRunAudit;
        $gridRunAudit->setAuthyGroup($this);
    }

    /**
     * @param	GridRunAudit $gridRunAudit The gridRunAudit object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeGridRunAudit($gridRunAudit)
    {
        if ($this->getGridRunAudits()->contains($gridRunAudit)) {
            $this->collGridRunAudits->remove($this->collGridRunAudits->search($gridRunAudit));
            if (null === $this->gridRunAuditsScheduledForDeletion) {
                $this->gridRunAuditsScheduledForDeletion = clone $this->collGridRunAudits;
                $this->gridRunAuditsScheduledForDeletion->clear();
            }
            $this->gridRunAuditsScheduledForDeletion[]= $gridRunAudit;
            $gridRunAudit->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|GridRunAudit[] List of GridRunAudit objects
     */
    public function getGridRunAuditsJoinGridRun($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = GridRunAuditQuery::create(null, $criteria);
        $query->joinWith('GridRun', $join_behavior);

        return $this->getGridRunAudits($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|GridRunAudit[] List of GridRunAudit objects
     */
    public function getGridRunAuditsJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = GridRunAuditQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getGridRunAudits($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|GridRunAudit[] List of GridRunAudit objects
     */
    public function getGridRunAuditsJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = GridRunAuditQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getGridRunAudits($query, $con);
    }

    /**
     * Clears out the collCountries collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addCountries()
     */
    public function clearCountries()
    {
        $this->collCountries = null; // important to set this to null since that means it is uninitialized
        $this->collCountriesPartial = null;

        return $this;
    }

    /**
     * reset is the collCountries collection loaded partially
     *
     * @return void
     */
    public function resetPartialCountries($v = true)
    {
        $this->collCountriesPartial = $v;
    }

    /**
     * Initializes the collCountries collection.
     *
     * By default this just sets the collCountries collection to an empty array (like clearcollCountries());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initCountries($overrideExisting = true)
    {
        if (null !== $this->collCountries && !$overrideExisting) {
            return;
        }
        $this->collCountries = new PropelObjectCollection();
        $this->collCountries->setModel('Country');
    }

    /**
     * Gets an array of Country objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|Country[] List of Country objects
     * @throws PropelException
     */
    public function getCountries($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collCountriesPartial && !$this->isNew();
        if (null === $this->collCountries || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collCountries) {
                // return empty collection
                $this->initCountries();
            } else {
                $collCountries = CountryQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collCountriesPartial && count($collCountries)) {
                      $this->initCountries(false);

                      foreach ($collCountries as $obj) {
                        if (false == $this->collCountries->contains($obj)) {
                          $this->collCountries->append($obj);
                        }
                      }

                      $this->collCountriesPartial = true;
                    }

                    $collCountries->getInternalIterator()->rewind();

                    return $collCountries;
                }

                if ($partial && $this->collCountries) {
                    foreach ($this->collCountries as $obj) {
                        if ($obj->isNew()) {
                            $collCountries[] = $obj;
                        }
                    }
                }

                $this->collCountries = $collCountries;
                $this->collCountriesPartial = false;
            }
        }

        return $this->collCountries;
    }

    /**
     * Sets a collection of Country objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $countries A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setCountries(PropelCollection $countries, ?PropelPDO $con = null)
    {
        $countriesToDelete = $this->getCountries(new Criteria(), $con)->diff($countries);


        $this->countriesScheduledForDeletion = $countriesToDelete;

        foreach ($countriesToDelete as $countryRemoved) {
            $countryRemoved->setAuthyGroup(null);
        }

        $this->collCountries = null;
        foreach ($countries as $country) {
            $this->addCountry($country);
        }

        $this->collCountries = $countries;
        $this->collCountriesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related Country objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related Country objects.
     * @throws PropelException
     */
    public function countCountries(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collCountriesPartial && !$this->isNew();
        if (null === $this->collCountries || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collCountries) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getCountries());
            }
            $query = CountryQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collCountries);
    }

    /**
     * Method called to associate a Country object to this object
     * through the Country foreign key attribute.
     *
     * @param    Country $l Country
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addCountry(Country $l)
    {
        if ($this->collCountries === null) {
            $this->initCountries();
            $this->collCountriesPartial = true;
        }

        if (!in_array($l, $this->collCountries->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddCountry($l);

            if ($this->countriesScheduledForDeletion and $this->countriesScheduledForDeletion->contains($l)) {
                $this->countriesScheduledForDeletion->remove($this->countriesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	Country $country The country object to add.
     */
    protected function doAddCountry($country)
    {
        $this->collCountries[]= $country;
        $country->setAuthyGroup($this);
    }

    /**
     * @param	Country $country The country object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeCountry($country)
    {
        if ($this->getCountries()->contains($country)) {
            $this->collCountries->remove($this->collCountries->search($country));
            if (null === $this->countriesScheduledForDeletion) {
                $this->countriesScheduledForDeletion = clone $this->collCountries;
                $this->countriesScheduledForDeletion->clear();
            }
            $this->countriesScheduledForDeletion[]= $country;
            $country->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|Country[] List of Country objects
     */
    public function getCountriesJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = CountryQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getCountries($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|Country[] List of Country objects
     */
    public function getCountriesJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = CountryQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getCountries($query, $con);
    }

    /**
     * Clears out the collOauthClients collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addOauthClients()
     */
    public function clearOauthClients()
    {
        $this->collOauthClients = null; // important to set this to null since that means it is uninitialized
        $this->collOauthClientsPartial = null;

        return $this;
    }

    /**
     * reset is the collOauthClients collection loaded partially
     *
     * @return void
     */
    public function resetPartialOauthClients($v = true)
    {
        $this->collOauthClientsPartial = $v;
    }

    /**
     * Initializes the collOauthClients collection.
     *
     * By default this just sets the collOauthClients collection to an empty array (like clearcollOauthClients());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initOauthClients($overrideExisting = true)
    {
        if (null !== $this->collOauthClients && !$overrideExisting) {
            return;
        }
        $this->collOauthClients = new PropelObjectCollection();
        $this->collOauthClients->setModel('OauthClient');
    }

    /**
     * Gets an array of OauthClient objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|OauthClient[] List of OauthClient objects
     * @throws PropelException
     */
    public function getOauthClients($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collOauthClientsPartial && !$this->isNew();
        if (null === $this->collOauthClients || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collOauthClients) {
                // return empty collection
                $this->initOauthClients();
            } else {
                $collOauthClients = OauthClientQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collOauthClientsPartial && count($collOauthClients)) {
                      $this->initOauthClients(false);

                      foreach ($collOauthClients as $obj) {
                        if (false == $this->collOauthClients->contains($obj)) {
                          $this->collOauthClients->append($obj);
                        }
                      }

                      $this->collOauthClientsPartial = true;
                    }

                    $collOauthClients->getInternalIterator()->rewind();

                    return $collOauthClients;
                }

                if ($partial && $this->collOauthClients) {
                    foreach ($this->collOauthClients as $obj) {
                        if ($obj->isNew()) {
                            $collOauthClients[] = $obj;
                        }
                    }
                }

                $this->collOauthClients = $collOauthClients;
                $this->collOauthClientsPartial = false;
            }
        }

        return $this->collOauthClients;
    }

    /**
     * Sets a collection of OauthClient objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $oauthClients A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setOauthClients(PropelCollection $oauthClients, ?PropelPDO $con = null)
    {
        $oauthClientsToDelete = $this->getOauthClients(new Criteria(), $con)->diff($oauthClients);


        $this->oauthClientsScheduledForDeletion = $oauthClientsToDelete;

        foreach ($oauthClientsToDelete as $oauthClientRemoved) {
            $oauthClientRemoved->setAuthyGroup(null);
        }

        $this->collOauthClients = null;
        foreach ($oauthClients as $oauthClient) {
            $this->addOauthClient($oauthClient);
        }

        $this->collOauthClients = $oauthClients;
        $this->collOauthClientsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related OauthClient objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related OauthClient objects.
     * @throws PropelException
     */
    public function countOauthClients(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collOauthClientsPartial && !$this->isNew();
        if (null === $this->collOauthClients || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collOauthClients) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getOauthClients());
            }
            $query = OauthClientQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collOauthClients);
    }

    /**
     * Method called to associate a OauthClient object to this object
     * through the OauthClient foreign key attribute.
     *
     * @param    OauthClient $l OauthClient
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addOauthClient(OauthClient $l)
    {
        if ($this->collOauthClients === null) {
            $this->initOauthClients();
            $this->collOauthClientsPartial = true;
        }

        if (!in_array($l, $this->collOauthClients->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddOauthClient($l);

            if ($this->oauthClientsScheduledForDeletion and $this->oauthClientsScheduledForDeletion->contains($l)) {
                $this->oauthClientsScheduledForDeletion->remove($this->oauthClientsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	OauthClient $oauthClient The oauthClient object to add.
     */
    protected function doAddOauthClient($oauthClient)
    {
        $this->collOauthClients[]= $oauthClient;
        $oauthClient->setAuthyGroup($this);
    }

    /**
     * @param	OauthClient $oauthClient The oauthClient object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeOauthClient($oauthClient)
    {
        if ($this->getOauthClients()->contains($oauthClient)) {
            $this->collOauthClients->remove($this->collOauthClients->search($oauthClient));
            if (null === $this->oauthClientsScheduledForDeletion) {
                $this->oauthClientsScheduledForDeletion = clone $this->collOauthClients;
                $this->oauthClientsScheduledForDeletion->clear();
            }
            $this->oauthClientsScheduledForDeletion[]= $oauthClient;
            $oauthClient->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|OauthClient[] List of OauthClient objects
     */
    public function getOauthClientsJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = OauthClientQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getOauthClients($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|OauthClient[] List of OauthClient objects
     */
    public function getOauthClientsJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = OauthClientQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getOauthClients($query, $con);
    }

    /**
     * Clears out the collOauthAuthCodes collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addOauthAuthCodes()
     */
    public function clearOauthAuthCodes()
    {
        $this->collOauthAuthCodes = null; // important to set this to null since that means it is uninitialized
        $this->collOauthAuthCodesPartial = null;

        return $this;
    }

    /**
     * reset is the collOauthAuthCodes collection loaded partially
     *
     * @return void
     */
    public function resetPartialOauthAuthCodes($v = true)
    {
        $this->collOauthAuthCodesPartial = $v;
    }

    /**
     * Initializes the collOauthAuthCodes collection.
     *
     * By default this just sets the collOauthAuthCodes collection to an empty array (like clearcollOauthAuthCodes());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initOauthAuthCodes($overrideExisting = true)
    {
        if (null !== $this->collOauthAuthCodes && !$overrideExisting) {
            return;
        }
        $this->collOauthAuthCodes = new PropelObjectCollection();
        $this->collOauthAuthCodes->setModel('OauthAuthCode');
    }

    /**
     * Gets an array of OauthAuthCode objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|OauthAuthCode[] List of OauthAuthCode objects
     * @throws PropelException
     */
    public function getOauthAuthCodes($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collOauthAuthCodesPartial && !$this->isNew();
        if (null === $this->collOauthAuthCodes || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collOauthAuthCodes) {
                // return empty collection
                $this->initOauthAuthCodes();
            } else {
                $collOauthAuthCodes = OauthAuthCodeQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collOauthAuthCodesPartial && count($collOauthAuthCodes)) {
                      $this->initOauthAuthCodes(false);

                      foreach ($collOauthAuthCodes as $obj) {
                        if (false == $this->collOauthAuthCodes->contains($obj)) {
                          $this->collOauthAuthCodes->append($obj);
                        }
                      }

                      $this->collOauthAuthCodesPartial = true;
                    }

                    $collOauthAuthCodes->getInternalIterator()->rewind();

                    return $collOauthAuthCodes;
                }

                if ($partial && $this->collOauthAuthCodes) {
                    foreach ($this->collOauthAuthCodes as $obj) {
                        if ($obj->isNew()) {
                            $collOauthAuthCodes[] = $obj;
                        }
                    }
                }

                $this->collOauthAuthCodes = $collOauthAuthCodes;
                $this->collOauthAuthCodesPartial = false;
            }
        }

        return $this->collOauthAuthCodes;
    }

    /**
     * Sets a collection of OauthAuthCode objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $oauthAuthCodes A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setOauthAuthCodes(PropelCollection $oauthAuthCodes, ?PropelPDO $con = null)
    {
        $oauthAuthCodesToDelete = $this->getOauthAuthCodes(new Criteria(), $con)->diff($oauthAuthCodes);


        $this->oauthAuthCodesScheduledForDeletion = $oauthAuthCodesToDelete;

        foreach ($oauthAuthCodesToDelete as $oauthAuthCodeRemoved) {
            $oauthAuthCodeRemoved->setAuthyGroup(null);
        }

        $this->collOauthAuthCodes = null;
        foreach ($oauthAuthCodes as $oauthAuthCode) {
            $this->addOauthAuthCode($oauthAuthCode);
        }

        $this->collOauthAuthCodes = $oauthAuthCodes;
        $this->collOauthAuthCodesPartial = false;

        return $this;
    }

    /**
     * Returns the number of related OauthAuthCode objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related OauthAuthCode objects.
     * @throws PropelException
     */
    public function countOauthAuthCodes(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collOauthAuthCodesPartial && !$this->isNew();
        if (null === $this->collOauthAuthCodes || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collOauthAuthCodes) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getOauthAuthCodes());
            }
            $query = OauthAuthCodeQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collOauthAuthCodes);
    }

    /**
     * Method called to associate a OauthAuthCode object to this object
     * through the OauthAuthCode foreign key attribute.
     *
     * @param    OauthAuthCode $l OauthAuthCode
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addOauthAuthCode(OauthAuthCode $l)
    {
        if ($this->collOauthAuthCodes === null) {
            $this->initOauthAuthCodes();
            $this->collOauthAuthCodesPartial = true;
        }

        if (!in_array($l, $this->collOauthAuthCodes->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddOauthAuthCode($l);

            if ($this->oauthAuthCodesScheduledForDeletion and $this->oauthAuthCodesScheduledForDeletion->contains($l)) {
                $this->oauthAuthCodesScheduledForDeletion->remove($this->oauthAuthCodesScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	OauthAuthCode $oauthAuthCode The oauthAuthCode object to add.
     */
    protected function doAddOauthAuthCode($oauthAuthCode)
    {
        $this->collOauthAuthCodes[]= $oauthAuthCode;
        $oauthAuthCode->setAuthyGroup($this);
    }

    /**
     * @param	OauthAuthCode $oauthAuthCode The oauthAuthCode object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeOauthAuthCode($oauthAuthCode)
    {
        if ($this->getOauthAuthCodes()->contains($oauthAuthCode)) {
            $this->collOauthAuthCodes->remove($this->collOauthAuthCodes->search($oauthAuthCode));
            if (null === $this->oauthAuthCodesScheduledForDeletion) {
                $this->oauthAuthCodesScheduledForDeletion = clone $this->collOauthAuthCodes;
                $this->oauthAuthCodesScheduledForDeletion->clear();
            }
            $this->oauthAuthCodesScheduledForDeletion[]= $oauthAuthCode;
            $oauthAuthCode->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|OauthAuthCode[] List of OauthAuthCode objects
     */
    public function getOauthAuthCodesJoinAuthyRelatedByIdAuthy($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = OauthAuthCodeQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdAuthy', $join_behavior);

        return $this->getOauthAuthCodes($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|OauthAuthCode[] List of OauthAuthCode objects
     */
    public function getOauthAuthCodesJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = OauthAuthCodeQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getOauthAuthCodes($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|OauthAuthCode[] List of OauthAuthCode objects
     */
    public function getOauthAuthCodesJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = OauthAuthCodeQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getOauthAuthCodes($query, $con);
    }

    /**
     * Clears out the collOauthAccessTokens collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addOauthAccessTokens()
     */
    public function clearOauthAccessTokens()
    {
        $this->collOauthAccessTokens = null; // important to set this to null since that means it is uninitialized
        $this->collOauthAccessTokensPartial = null;

        return $this;
    }

    /**
     * reset is the collOauthAccessTokens collection loaded partially
     *
     * @return void
     */
    public function resetPartialOauthAccessTokens($v = true)
    {
        $this->collOauthAccessTokensPartial = $v;
    }

    /**
     * Initializes the collOauthAccessTokens collection.
     *
     * By default this just sets the collOauthAccessTokens collection to an empty array (like clearcollOauthAccessTokens());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initOauthAccessTokens($overrideExisting = true)
    {
        if (null !== $this->collOauthAccessTokens && !$overrideExisting) {
            return;
        }
        $this->collOauthAccessTokens = new PropelObjectCollection();
        $this->collOauthAccessTokens->setModel('OauthAccessToken');
    }

    /**
     * Gets an array of OauthAccessToken objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|OauthAccessToken[] List of OauthAccessToken objects
     * @throws PropelException
     */
    public function getOauthAccessTokens($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collOauthAccessTokensPartial && !$this->isNew();
        if (null === $this->collOauthAccessTokens || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collOauthAccessTokens) {
                // return empty collection
                $this->initOauthAccessTokens();
            } else {
                $collOauthAccessTokens = OauthAccessTokenQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collOauthAccessTokensPartial && count($collOauthAccessTokens)) {
                      $this->initOauthAccessTokens(false);

                      foreach ($collOauthAccessTokens as $obj) {
                        if (false == $this->collOauthAccessTokens->contains($obj)) {
                          $this->collOauthAccessTokens->append($obj);
                        }
                      }

                      $this->collOauthAccessTokensPartial = true;
                    }

                    $collOauthAccessTokens->getInternalIterator()->rewind();

                    return $collOauthAccessTokens;
                }

                if ($partial && $this->collOauthAccessTokens) {
                    foreach ($this->collOauthAccessTokens as $obj) {
                        if ($obj->isNew()) {
                            $collOauthAccessTokens[] = $obj;
                        }
                    }
                }

                $this->collOauthAccessTokens = $collOauthAccessTokens;
                $this->collOauthAccessTokensPartial = false;
            }
        }

        return $this->collOauthAccessTokens;
    }

    /**
     * Sets a collection of OauthAccessToken objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $oauthAccessTokens A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setOauthAccessTokens(PropelCollection $oauthAccessTokens, ?PropelPDO $con = null)
    {
        $oauthAccessTokensToDelete = $this->getOauthAccessTokens(new Criteria(), $con)->diff($oauthAccessTokens);


        $this->oauthAccessTokensScheduledForDeletion = $oauthAccessTokensToDelete;

        foreach ($oauthAccessTokensToDelete as $oauthAccessTokenRemoved) {
            $oauthAccessTokenRemoved->setAuthyGroup(null);
        }

        $this->collOauthAccessTokens = null;
        foreach ($oauthAccessTokens as $oauthAccessToken) {
            $this->addOauthAccessToken($oauthAccessToken);
        }

        $this->collOauthAccessTokens = $oauthAccessTokens;
        $this->collOauthAccessTokensPartial = false;

        return $this;
    }

    /**
     * Returns the number of related OauthAccessToken objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related OauthAccessToken objects.
     * @throws PropelException
     */
    public function countOauthAccessTokens(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collOauthAccessTokensPartial && !$this->isNew();
        if (null === $this->collOauthAccessTokens || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collOauthAccessTokens) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getOauthAccessTokens());
            }
            $query = OauthAccessTokenQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collOauthAccessTokens);
    }

    /**
     * Method called to associate a OauthAccessToken object to this object
     * through the OauthAccessToken foreign key attribute.
     *
     * @param    OauthAccessToken $l OauthAccessToken
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addOauthAccessToken(OauthAccessToken $l)
    {
        if ($this->collOauthAccessTokens === null) {
            $this->initOauthAccessTokens();
            $this->collOauthAccessTokensPartial = true;
        }

        if (!in_array($l, $this->collOauthAccessTokens->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddOauthAccessToken($l);

            if ($this->oauthAccessTokensScheduledForDeletion and $this->oauthAccessTokensScheduledForDeletion->contains($l)) {
                $this->oauthAccessTokensScheduledForDeletion->remove($this->oauthAccessTokensScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	OauthAccessToken $oauthAccessToken The oauthAccessToken object to add.
     */
    protected function doAddOauthAccessToken($oauthAccessToken)
    {
        $this->collOauthAccessTokens[]= $oauthAccessToken;
        $oauthAccessToken->setAuthyGroup($this);
    }

    /**
     * @param	OauthAccessToken $oauthAccessToken The oauthAccessToken object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeOauthAccessToken($oauthAccessToken)
    {
        if ($this->getOauthAccessTokens()->contains($oauthAccessToken)) {
            $this->collOauthAccessTokens->remove($this->collOauthAccessTokens->search($oauthAccessToken));
            if (null === $this->oauthAccessTokensScheduledForDeletion) {
                $this->oauthAccessTokensScheduledForDeletion = clone $this->collOauthAccessTokens;
                $this->oauthAccessTokensScheduledForDeletion->clear();
            }
            $this->oauthAccessTokensScheduledForDeletion[]= $oauthAccessToken;
            $oauthAccessToken->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|OauthAccessToken[] List of OauthAccessToken objects
     */
    public function getOauthAccessTokensJoinAuthyRelatedByIdAuthy($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = OauthAccessTokenQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdAuthy', $join_behavior);

        return $this->getOauthAccessTokens($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|OauthAccessToken[] List of OauthAccessToken objects
     */
    public function getOauthAccessTokensJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = OauthAccessTokenQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getOauthAccessTokens($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|OauthAccessToken[] List of OauthAccessToken objects
     */
    public function getOauthAccessTokensJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = OauthAccessTokenQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getOauthAccessTokens($query, $con);
    }

    /**
     * Clears out the collOauthRefreshTokens collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addOauthRefreshTokens()
     */
    public function clearOauthRefreshTokens()
    {
        $this->collOauthRefreshTokens = null; // important to set this to null since that means it is uninitialized
        $this->collOauthRefreshTokensPartial = null;

        return $this;
    }

    /**
     * reset is the collOauthRefreshTokens collection loaded partially
     *
     * @return void
     */
    public function resetPartialOauthRefreshTokens($v = true)
    {
        $this->collOauthRefreshTokensPartial = $v;
    }

    /**
     * Initializes the collOauthRefreshTokens collection.
     *
     * By default this just sets the collOauthRefreshTokens collection to an empty array (like clearcollOauthRefreshTokens());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initOauthRefreshTokens($overrideExisting = true)
    {
        if (null !== $this->collOauthRefreshTokens && !$overrideExisting) {
            return;
        }
        $this->collOauthRefreshTokens = new PropelObjectCollection();
        $this->collOauthRefreshTokens->setModel('OauthRefreshToken');
    }

    /**
     * Gets an array of OauthRefreshToken objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|OauthRefreshToken[] List of OauthRefreshToken objects
     * @throws PropelException
     */
    public function getOauthRefreshTokens($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collOauthRefreshTokensPartial && !$this->isNew();
        if (null === $this->collOauthRefreshTokens || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collOauthRefreshTokens) {
                // return empty collection
                $this->initOauthRefreshTokens();
            } else {
                $collOauthRefreshTokens = OauthRefreshTokenQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collOauthRefreshTokensPartial && count($collOauthRefreshTokens)) {
                      $this->initOauthRefreshTokens(false);

                      foreach ($collOauthRefreshTokens as $obj) {
                        if (false == $this->collOauthRefreshTokens->contains($obj)) {
                          $this->collOauthRefreshTokens->append($obj);
                        }
                      }

                      $this->collOauthRefreshTokensPartial = true;
                    }

                    $collOauthRefreshTokens->getInternalIterator()->rewind();

                    return $collOauthRefreshTokens;
                }

                if ($partial && $this->collOauthRefreshTokens) {
                    foreach ($this->collOauthRefreshTokens as $obj) {
                        if ($obj->isNew()) {
                            $collOauthRefreshTokens[] = $obj;
                        }
                    }
                }

                $this->collOauthRefreshTokens = $collOauthRefreshTokens;
                $this->collOauthRefreshTokensPartial = false;
            }
        }

        return $this->collOauthRefreshTokens;
    }

    /**
     * Sets a collection of OauthRefreshToken objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $oauthRefreshTokens A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setOauthRefreshTokens(PropelCollection $oauthRefreshTokens, ?PropelPDO $con = null)
    {
        $oauthRefreshTokensToDelete = $this->getOauthRefreshTokens(new Criteria(), $con)->diff($oauthRefreshTokens);


        $this->oauthRefreshTokensScheduledForDeletion = $oauthRefreshTokensToDelete;

        foreach ($oauthRefreshTokensToDelete as $oauthRefreshTokenRemoved) {
            $oauthRefreshTokenRemoved->setAuthyGroup(null);
        }

        $this->collOauthRefreshTokens = null;
        foreach ($oauthRefreshTokens as $oauthRefreshToken) {
            $this->addOauthRefreshToken($oauthRefreshToken);
        }

        $this->collOauthRefreshTokens = $oauthRefreshTokens;
        $this->collOauthRefreshTokensPartial = false;

        return $this;
    }

    /**
     * Returns the number of related OauthRefreshToken objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related OauthRefreshToken objects.
     * @throws PropelException
     */
    public function countOauthRefreshTokens(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collOauthRefreshTokensPartial && !$this->isNew();
        if (null === $this->collOauthRefreshTokens || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collOauthRefreshTokens) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getOauthRefreshTokens());
            }
            $query = OauthRefreshTokenQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collOauthRefreshTokens);
    }

    /**
     * Method called to associate a OauthRefreshToken object to this object
     * through the OauthRefreshToken foreign key attribute.
     *
     * @param    OauthRefreshToken $l OauthRefreshToken
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addOauthRefreshToken(OauthRefreshToken $l)
    {
        if ($this->collOauthRefreshTokens === null) {
            $this->initOauthRefreshTokens();
            $this->collOauthRefreshTokensPartial = true;
        }

        if (!in_array($l, $this->collOauthRefreshTokens->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddOauthRefreshToken($l);

            if ($this->oauthRefreshTokensScheduledForDeletion and $this->oauthRefreshTokensScheduledForDeletion->contains($l)) {
                $this->oauthRefreshTokensScheduledForDeletion->remove($this->oauthRefreshTokensScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	OauthRefreshToken $oauthRefreshToken The oauthRefreshToken object to add.
     */
    protected function doAddOauthRefreshToken($oauthRefreshToken)
    {
        $this->collOauthRefreshTokens[]= $oauthRefreshToken;
        $oauthRefreshToken->setAuthyGroup($this);
    }

    /**
     * @param	OauthRefreshToken $oauthRefreshToken The oauthRefreshToken object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeOauthRefreshToken($oauthRefreshToken)
    {
        if ($this->getOauthRefreshTokens()->contains($oauthRefreshToken)) {
            $this->collOauthRefreshTokens->remove($this->collOauthRefreshTokens->search($oauthRefreshToken));
            if (null === $this->oauthRefreshTokensScheduledForDeletion) {
                $this->oauthRefreshTokensScheduledForDeletion = clone $this->collOauthRefreshTokens;
                $this->oauthRefreshTokensScheduledForDeletion->clear();
            }
            $this->oauthRefreshTokensScheduledForDeletion[]= $oauthRefreshToken;
            $oauthRefreshToken->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|OauthRefreshToken[] List of OauthRefreshToken objects
     */
    public function getOauthRefreshTokensJoinAuthyRelatedByIdAuthy($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = OauthRefreshTokenQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdAuthy', $join_behavior);

        return $this->getOauthRefreshTokens($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|OauthRefreshToken[] List of OauthRefreshToken objects
     */
    public function getOauthRefreshTokensJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = OauthRefreshTokenQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getOauthRefreshTokens($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|OauthRefreshToken[] List of OauthRefreshToken objects
     */
    public function getOauthRefreshTokensJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = OauthRefreshTokenQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getOauthRefreshTokens($query, $con);
    }

    /**
     * Clears out the collMessageI18ns collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return AuthyGroup The current object (for fluent API support)
     * @see        addMessageI18ns()
     */
    public function clearMessageI18ns()
    {
        $this->collMessageI18ns = null; // important to set this to null since that means it is uninitialized
        $this->collMessageI18nsPartial = null;

        return $this;
    }

    /**
     * reset is the collMessageI18ns collection loaded partially
     *
     * @return void
     */
    public function resetPartialMessageI18ns($v = true)
    {
        $this->collMessageI18nsPartial = $v;
    }

    /**
     * Initializes the collMessageI18ns collection.
     *
     * By default this just sets the collMessageI18ns collection to an empty array (like clearcollMessageI18ns());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initMessageI18ns($overrideExisting = true)
    {
        if (null !== $this->collMessageI18ns && !$overrideExisting) {
            return;
        }
        $this->collMessageI18ns = new PropelObjectCollection();
        $this->collMessageI18ns->setModel('MessageI18n');
    }

    /**
     * Gets an array of MessageI18n objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this AuthyGroup is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @return PropelObjectCollection|MessageI18n[] List of MessageI18n objects
     * @throws PropelException
     */
    public function getMessageI18ns($criteria = null, ?PropelPDO $con = null)
    {
        $partial = $this->collMessageI18nsPartial && !$this->isNew();
        if (null === $this->collMessageI18ns || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collMessageI18ns) {
                // return empty collection
                $this->initMessageI18ns();
            } else {
                $collMessageI18ns = MessageI18nQuery::create(null, $criteria)
                    ->filterByAuthyGroup($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collMessageI18nsPartial && count($collMessageI18ns)) {
                      $this->initMessageI18ns(false);

                      foreach ($collMessageI18ns as $obj) {
                        if (false == $this->collMessageI18ns->contains($obj)) {
                          $this->collMessageI18ns->append($obj);
                        }
                      }

                      $this->collMessageI18nsPartial = true;
                    }

                    $collMessageI18ns->getInternalIterator()->rewind();

                    return $collMessageI18ns;
                }

                if ($partial && $this->collMessageI18ns) {
                    foreach ($this->collMessageI18ns as $obj) {
                        if ($obj->isNew()) {
                            $collMessageI18ns[] = $obj;
                        }
                    }
                }

                $this->collMessageI18ns = $collMessageI18ns;
                $this->collMessageI18nsPartial = false;
            }
        }

        return $this->collMessageI18ns;
    }

    /**
     * Sets a collection of MessageI18n objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param PropelCollection $messageI18ns A Propel collection.
     * @param PropelPDO $con Optional connection object
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function setMessageI18ns(PropelCollection $messageI18ns, ?PropelPDO $con = null)
    {
        $messageI18nsToDelete = $this->getMessageI18ns(new Criteria(), $con)->diff($messageI18ns);


        $this->messageI18nsScheduledForDeletion = $messageI18nsToDelete;

        foreach ($messageI18nsToDelete as $messageI18nRemoved) {
            $messageI18nRemoved->setAuthyGroup(null);
        }

        $this->collMessageI18ns = null;
        foreach ($messageI18ns as $messageI18n) {
            $this->addMessageI18n($messageI18n);
        }

        $this->collMessageI18ns = $messageI18ns;
        $this->collMessageI18nsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related MessageI18n objects.
     *
     * @param Criteria $criteria
     * @param boolean $distinct
     * @param PropelPDO $con
     * @return int             Count of related MessageI18n objects.
     * @throws PropelException
     */
    public function countMessageI18ns(?Criteria $criteria = null, $distinct = false, ?PropelPDO $con = null)
    {
        $partial = $this->collMessageI18nsPartial && !$this->isNew();
        if (null === $this->collMessageI18ns || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collMessageI18ns) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getMessageI18ns());
            }
            $query = MessageI18nQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByAuthyGroup($this)
                ->count($con);
        }

        return count($this->collMessageI18ns);
    }

    /**
     * Method called to associate a MessageI18n object to this object
     * through the MessageI18n foreign key attribute.
     *
     * @param    MessageI18n $l MessageI18n
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function addMessageI18n(MessageI18n $l)
    {
        if ($this->collMessageI18ns === null) {
            $this->initMessageI18ns();
            $this->collMessageI18nsPartial = true;
        }

        if (!in_array($l, $this->collMessageI18ns->getArrayCopy(), true)) { // only add it if the **same** object is not already associated
            $this->doAddMessageI18n($l);

            if ($this->messageI18nsScheduledForDeletion and $this->messageI18nsScheduledForDeletion->contains($l)) {
                $this->messageI18nsScheduledForDeletion->remove($this->messageI18nsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param	MessageI18n $messageI18n The messageI18n object to add.
     */
    protected function doAddMessageI18n($messageI18n)
    {
        $this->collMessageI18ns[]= $messageI18n;
        $messageI18n->setAuthyGroup($this);
    }

    /**
     * @param	MessageI18n $messageI18n The messageI18n object to remove.
     * @return AuthyGroup The current object (for fluent API support)
     */
    public function removeMessageI18n($messageI18n)
    {
        if ($this->getMessageI18ns()->contains($messageI18n)) {
            $this->collMessageI18ns->remove($this->collMessageI18ns->search($messageI18n));
            if (null === $this->messageI18nsScheduledForDeletion) {
                $this->messageI18nsScheduledForDeletion = clone $this->collMessageI18ns;
                $this->messageI18nsScheduledForDeletion->clear();
            }
            $this->messageI18nsScheduledForDeletion[]= $messageI18n;
            $messageI18n->setAuthyGroup(null);
        }

        return $this;
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|MessageI18n[] List of MessageI18n objects
     */
    public function getMessageI18nsJoinMessage($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = MessageI18nQuery::create(null, $criteria);
        $query->joinWith('Message', $join_behavior);

        return $this->getMessageI18ns($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|MessageI18n[] List of MessageI18n objects
     */
    public function getMessageI18nsJoinAuthyRelatedByIdCreation($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = MessageI18nQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdCreation', $join_behavior);

        return $this->getMessageI18ns($query, $con);
    }


    /**

     *
     * @param Criteria $criteria optional Criteria object to narrow the query
     * @param PropelPDO $con optional connection object
     * @param string $join_behavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return PropelObjectCollection|MessageI18n[] List of MessageI18n objects
     */
    public function getMessageI18nsJoinAuthyRelatedByIdModification($criteria = null, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $query = MessageI18nQuery::create(null, $criteria);
        $query->joinWith('AuthyRelatedByIdModification', $join_behavior);

        return $this->getMessageI18ns($query, $con);
    }

    /**
     * Clears the current object and sets all attributes to their default values
     */
    public function clear()
    {
        $this->id_authy_group = null;
        $this->name = null;
        $this->desc = null;
        $this->default_group = null;
        $this->admin = null;
        $this->rights_all = null;
        $this->rights_owner = null;
        $this->rights_group = null;
        $this->date_creation = null;
        $this->date_modification = null;
        $this->id_group_creation = null;
        $this->id_creation = null;
        $this->id_modification = null;
        $this->alreadyInSave = false;
        $this->alreadyInValidation = false;
        $this->alreadyInClearAllReferencesDeep = false;
        $this->clearAllReferences();
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
            if ($this->collAuthyGroupxesRelatedByIdAuthyGroup) {
                foreach ($this->collAuthyGroupxesRelatedByIdAuthyGroup as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collAuthiesRelatedByIdAuthyGroup) {
                foreach ($this->collAuthiesRelatedByIdAuthyGroup as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collAuthiesRelatedByIdGroupCreation) {
                foreach ($this->collAuthiesRelatedByIdGroupCreation as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collPushDevices) {
                foreach ($this->collPushDevices as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collGridRuns) {
                foreach ($this->collGridRuns as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collFleetSlots) {
                foreach ($this->collFleetSlots as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collRegimeEpisodes) {
                foreach ($this->collRegimeEpisodes as $o) {
                    $o->clearAllReferences($deep);
                }
            }
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
            if ($this->collSimWallets) {
                foreach ($this->collSimWallets as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collMarketSummaries) {
                foreach ($this->collMarketSummaries as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collMarketRegimes) {
                foreach ($this->collMarketRegimes as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collMarketCandles) {
                foreach ($this->collMarketCandles as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collBotDecisions) {
                foreach ($this->collBotDecisions as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collMarketOutlooks) {
                foreach ($this->collMarketOutlooks as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collMarketOutlookStates) {
                foreach ($this->collMarketOutlookStates as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collWalletNavs) {
                foreach ($this->collWalletNavs as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collAuthyGroupsRelatedByIdAuthyGroup) {
                foreach ($this->collAuthyGroupsRelatedByIdAuthyGroup as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collAuthyGroupxesRelatedByIdGroupCreation) {
                foreach ($this->collAuthyGroupxesRelatedByIdGroupCreation as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collConfigs) {
                foreach ($this->collConfigs as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collApiRbacs) {
                foreach ($this->collApiRbacs as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collTemplates) {
                foreach ($this->collTemplates as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collTemplateFiles) {
                foreach ($this->collTemplateFiles as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collAuthyRefreshTokens) {
                foreach ($this->collAuthyRefreshTokens as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collGridRunAudits) {
                foreach ($this->collGridRunAudits as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collCountries) {
                foreach ($this->collCountries as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collOauthClients) {
                foreach ($this->collOauthClients as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collOauthAuthCodes) {
                foreach ($this->collOauthAuthCodes as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collOauthAccessTokens) {
                foreach ($this->collOauthAccessTokens as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collOauthRefreshTokens) {
                foreach ($this->collOauthRefreshTokens as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collMessageI18ns) {
                foreach ($this->collMessageI18ns as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->aAuthyGroupRelatedByIdGroupCreation instanceof Persistent) {
              $this->aAuthyGroupRelatedByIdGroupCreation->clearAllReferences($deep);
            }
            if ($this->aAuthyRelatedByIdCreation instanceof Persistent) {
              $this->aAuthyRelatedByIdCreation->clearAllReferences($deep);
            }
            if ($this->aAuthyRelatedByIdModification instanceof Persistent) {
              $this->aAuthyRelatedByIdModification->clearAllReferences($deep);
            }

            $this->alreadyInClearAllReferencesDeep = false;
        } // if ($deep)

        if ($this->collAuthyGroupxesRelatedByIdAuthyGroup instanceof PropelCollection) {
            $this->collAuthyGroupxesRelatedByIdAuthyGroup->clearIterator();
        }
        $this->collAuthyGroupxesRelatedByIdAuthyGroup = null;
        if ($this->collAuthiesRelatedByIdAuthyGroup instanceof PropelCollection) {
            $this->collAuthiesRelatedByIdAuthyGroup->clearIterator();
        }
        $this->collAuthiesRelatedByIdAuthyGroup = null;
        if ($this->collAuthiesRelatedByIdGroupCreation instanceof PropelCollection) {
            $this->collAuthiesRelatedByIdGroupCreation->clearIterator();
        }
        $this->collAuthiesRelatedByIdGroupCreation = null;
        if ($this->collPushDevices instanceof PropelCollection) {
            $this->collPushDevices->clearIterator();
        }
        $this->collPushDevices = null;
        if ($this->collGridRuns instanceof PropelCollection) {
            $this->collGridRuns->clearIterator();
        }
        $this->collGridRuns = null;
        if ($this->collFleetSlots instanceof PropelCollection) {
            $this->collFleetSlots->clearIterator();
        }
        $this->collFleetSlots = null;
        if ($this->collRegimeEpisodes instanceof PropelCollection) {
            $this->collRegimeEpisodes->clearIterator();
        }
        $this->collRegimeEpisodes = null;
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
        if ($this->collSimWallets instanceof PropelCollection) {
            $this->collSimWallets->clearIterator();
        }
        $this->collSimWallets = null;
        if ($this->collMarketSummaries instanceof PropelCollection) {
            $this->collMarketSummaries->clearIterator();
        }
        $this->collMarketSummaries = null;
        if ($this->collMarketRegimes instanceof PropelCollection) {
            $this->collMarketRegimes->clearIterator();
        }
        $this->collMarketRegimes = null;
        if ($this->collMarketCandles instanceof PropelCollection) {
            $this->collMarketCandles->clearIterator();
        }
        $this->collMarketCandles = null;
        if ($this->collBotDecisions instanceof PropelCollection) {
            $this->collBotDecisions->clearIterator();
        }
        $this->collBotDecisions = null;
        if ($this->collMarketOutlooks instanceof PropelCollection) {
            $this->collMarketOutlooks->clearIterator();
        }
        $this->collMarketOutlooks = null;
        if ($this->collMarketOutlookStates instanceof PropelCollection) {
            $this->collMarketOutlookStates->clearIterator();
        }
        $this->collMarketOutlookStates = null;
        if ($this->collWalletNavs instanceof PropelCollection) {
            $this->collWalletNavs->clearIterator();
        }
        $this->collWalletNavs = null;
        if ($this->collAuthyGroupsRelatedByIdAuthyGroup instanceof PropelCollection) {
            $this->collAuthyGroupsRelatedByIdAuthyGroup->clearIterator();
        }
        $this->collAuthyGroupsRelatedByIdAuthyGroup = null;
        if ($this->collAuthyGroupxesRelatedByIdGroupCreation instanceof PropelCollection) {
            $this->collAuthyGroupxesRelatedByIdGroupCreation->clearIterator();
        }
        $this->collAuthyGroupxesRelatedByIdGroupCreation = null;
        if ($this->collConfigs instanceof PropelCollection) {
            $this->collConfigs->clearIterator();
        }
        $this->collConfigs = null;
        if ($this->collApiRbacs instanceof PropelCollection) {
            $this->collApiRbacs->clearIterator();
        }
        $this->collApiRbacs = null;
        if ($this->collTemplates instanceof PropelCollection) {
            $this->collTemplates->clearIterator();
        }
        $this->collTemplates = null;
        if ($this->collTemplateFiles instanceof PropelCollection) {
            $this->collTemplateFiles->clearIterator();
        }
        $this->collTemplateFiles = null;
        if ($this->collAuthyRefreshTokens instanceof PropelCollection) {
            $this->collAuthyRefreshTokens->clearIterator();
        }
        $this->collAuthyRefreshTokens = null;
        if ($this->collGridRunAudits instanceof PropelCollection) {
            $this->collGridRunAudits->clearIterator();
        }
        $this->collGridRunAudits = null;
        if ($this->collCountries instanceof PropelCollection) {
            $this->collCountries->clearIterator();
        }
        $this->collCountries = null;
        if ($this->collOauthClients instanceof PropelCollection) {
            $this->collOauthClients->clearIterator();
        }
        $this->collOauthClients = null;
        if ($this->collOauthAuthCodes instanceof PropelCollection) {
            $this->collOauthAuthCodes->clearIterator();
        }
        $this->collOauthAuthCodes = null;
        if ($this->collOauthAccessTokens instanceof PropelCollection) {
            $this->collOauthAccessTokens->clearIterator();
        }
        $this->collOauthAccessTokens = null;
        if ($this->collOauthRefreshTokens instanceof PropelCollection) {
            $this->collOauthRefreshTokens->clearIterator();
        }
        $this->collOauthRefreshTokens = null;
        if ($this->collMessageI18ns instanceof PropelCollection) {
            $this->collMessageI18ns->clearIterator();
        }
        $this->collMessageI18ns = null;
        $this->aAuthyGroupRelatedByIdGroupCreation = null;
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
        return (string) $this->exportTo(AuthyGroupPeer::DEFAULT_STRING_FORMAT);
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
     * @return     AuthyGroup The current object (for fluent API support)
     */
    public function keepUpdateDateUnchanged(){
        $this->modifiedColumns[] = AuthyGroupPeer::DATE_MODIFICATION;

        return $this;
    }

}
