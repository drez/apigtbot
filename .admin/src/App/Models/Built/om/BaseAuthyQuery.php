<?php

namespace App\om;

use \Criteria;
use \Exception;
use \ModelCriteria;
use \ModelJoin;
use \PDO;
use \Propel;
use \PropelCollection;
use \PropelException;
use \PropelObjectCollection;
use \PropelPDO;
use App\ApiLog;
use App\ApiRbac;
use App\Authy;
use App\AuthyGroup;
use App\AuthyGroupX;
use App\AuthyLog;
use App\AuthyPeer;
use App\AuthyQuery;
use App\AuthyRefreshToken;
use App\BotCommand;
use App\BotDecision;
use App\BotEvent;
use App\BotOrder;
use App\Config;
use App\Country;
use App\FleetSlot;
use App\GridRun;
use App\GridRunAudit;
use App\MarketCandle;
use App\MarketOutlook;
use App\MarketOutlookState;
use App\MarketRegime;
use App\MarketSummary;
use App\MessageI18n;
use App\OauthAccessToken;
use App\OauthAuthCode;
use App\OauthClient;
use App\OauthRefreshToken;
use App\PushDevice;
use App\RegimeEpisode;
use App\SimWallet;
use App\Template;
use App\TemplateFile;
use App\TradeCycle;
use App\WalletNav;

/**
 * Base class that represents a query for the 'authy' table.
 *
 * User
 *
 * @method AuthyQuery orderByIdAuthy($order = Criteria::ASC) Order by the id_authy column
 * @method AuthyQuery orderByValidationKey($order = Criteria::ASC) Order by the validation_key column
 * @method AuthyQuery orderByUsername($order = Criteria::ASC) Order by the username column
 * @method AuthyQuery orderByFullname($order = Criteria::ASC) Order by the fullname column
 * @method AuthyQuery orderByEmail($order = Criteria::ASC) Order by the email column
 * @method AuthyQuery orderByPasswdHash($order = Criteria::ASC) Order by the passwd_hash column
 * @method AuthyQuery orderByExpire($order = Criteria::ASC) Order by the expire column
 * @method AuthyQuery orderByDeactivate($order = Criteria::ASC) Order by the deactivate column
 * @method AuthyQuery orderByLanguage($order = Criteria::ASC) Order by the language column
 * @method AuthyQuery orderByTheme($order = Criteria::ASC) Order by the theme column
 * @method AuthyQuery orderByGoogleSub($order = Criteria::ASC) Order by the google_sub column
 * @method AuthyQuery orderByGoogleEmail($order = Criteria::ASC) Order by the google_email column
 * @method AuthyQuery orderByResetTokenHash($order = Criteria::ASC) Order by the reset_token_hash column
 * @method AuthyQuery orderByResetTokenExpires($order = Criteria::ASC) Order by the reset_token_expires column
 * @method AuthyQuery orderByIdTenant($order = Criteria::ASC) Order by the id_tenant column
 * @method AuthyQuery orderByLocationAddress($order = Criteria::ASC) Order by the location_address column
 * @method AuthyQuery orderByLocationLat($order = Criteria::ASC) Order by the location_lat column
 * @method AuthyQuery orderByLocationLng($order = Criteria::ASC) Order by the location_lng column
 * @method AuthyQuery orderByIsRoot($order = Criteria::ASC) Order by the is_root column
 * @method AuthyQuery orderByIdAuthyGroup($order = Criteria::ASC) Order by the id_authy_group column
 * @method AuthyQuery orderByIsSystem($order = Criteria::ASC) Order by the is_system column
 * @method AuthyQuery orderByRightsAll($order = Criteria::ASC) Order by the rights_all column
 * @method AuthyQuery orderByRightsGroup($order = Criteria::ASC) Order by the rights_group column
 * @method AuthyQuery orderByRightsOwner($order = Criteria::ASC) Order by the rights_owner column
 * @method AuthyQuery orderByOnglet($order = Criteria::ASC) Order by the onglet column
 * @method AuthyQuery orderByDateCreation($order = Criteria::ASC) Order by the date_creation column
 * @method AuthyQuery orderByDateModification($order = Criteria::ASC) Order by the date_modification column
 * @method AuthyQuery orderByIdGroupCreation($order = Criteria::ASC) Order by the id_group_creation column
 * @method AuthyQuery orderByIdCreation($order = Criteria::ASC) Order by the id_creation column
 * @method AuthyQuery orderByIdModification($order = Criteria::ASC) Order by the id_modification column
 *
 * @method AuthyQuery groupByIdAuthy() Group by the id_authy column
 * @method AuthyQuery groupByValidationKey() Group by the validation_key column
 * @method AuthyQuery groupByUsername() Group by the username column
 * @method AuthyQuery groupByFullname() Group by the fullname column
 * @method AuthyQuery groupByEmail() Group by the email column
 * @method AuthyQuery groupByPasswdHash() Group by the passwd_hash column
 * @method AuthyQuery groupByExpire() Group by the expire column
 * @method AuthyQuery groupByDeactivate() Group by the deactivate column
 * @method AuthyQuery groupByLanguage() Group by the language column
 * @method AuthyQuery groupByTheme() Group by the theme column
 * @method AuthyQuery groupByGoogleSub() Group by the google_sub column
 * @method AuthyQuery groupByGoogleEmail() Group by the google_email column
 * @method AuthyQuery groupByResetTokenHash() Group by the reset_token_hash column
 * @method AuthyQuery groupByResetTokenExpires() Group by the reset_token_expires column
 * @method AuthyQuery groupByIdTenant() Group by the id_tenant column
 * @method AuthyQuery groupByLocationAddress() Group by the location_address column
 * @method AuthyQuery groupByLocationLat() Group by the location_lat column
 * @method AuthyQuery groupByLocationLng() Group by the location_lng column
 * @method AuthyQuery groupByIsRoot() Group by the is_root column
 * @method AuthyQuery groupByIdAuthyGroup() Group by the id_authy_group column
 * @method AuthyQuery groupByIsSystem() Group by the is_system column
 * @method AuthyQuery groupByRightsAll() Group by the rights_all column
 * @method AuthyQuery groupByRightsGroup() Group by the rights_group column
 * @method AuthyQuery groupByRightsOwner() Group by the rights_owner column
 * @method AuthyQuery groupByOnglet() Group by the onglet column
 * @method AuthyQuery groupByDateCreation() Group by the date_creation column
 * @method AuthyQuery groupByDateModification() Group by the date_modification column
 * @method AuthyQuery groupByIdGroupCreation() Group by the id_group_creation column
 * @method AuthyQuery groupByIdCreation() Group by the id_creation column
 * @method AuthyQuery groupByIdModification() Group by the id_modification column
 *
 * @method AuthyQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method AuthyQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method AuthyQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method AuthyQuery leftJoinAuthyGroupRelatedByIdAuthyGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroupRelatedByIdAuthyGroup relation
 * @method AuthyQuery rightJoinAuthyGroupRelatedByIdAuthyGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroupRelatedByIdAuthyGroup relation
 * @method AuthyQuery innerJoinAuthyGroupRelatedByIdAuthyGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroupRelatedByIdAuthyGroup relation
 *
 * @method AuthyQuery leftJoinAuthyGroupRelatedByIdGroupCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroupRelatedByIdGroupCreation relation
 * @method AuthyQuery rightJoinAuthyGroupRelatedByIdGroupCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroupRelatedByIdGroupCreation relation
 * @method AuthyQuery innerJoinAuthyGroupRelatedByIdGroupCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroupRelatedByIdGroupCreation relation
 *
 * @method AuthyQuery leftJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method AuthyQuery rightJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method AuthyQuery innerJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinAuthyRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method AuthyQuery rightJoinAuthyRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method AuthyQuery innerJoinAuthyRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinPushDeviceRelatedByIdAuthy($relationAlias = null) Adds a LEFT JOIN clause to the query using the PushDeviceRelatedByIdAuthy relation
 * @method AuthyQuery rightJoinPushDeviceRelatedByIdAuthy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the PushDeviceRelatedByIdAuthy relation
 * @method AuthyQuery innerJoinPushDeviceRelatedByIdAuthy($relationAlias = null) Adds a INNER JOIN clause to the query using the PushDeviceRelatedByIdAuthy relation
 *
 * @method AuthyQuery leftJoinAuthyGroupXRelatedByIdAuthy($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroupXRelatedByIdAuthy relation
 * @method AuthyQuery rightJoinAuthyGroupXRelatedByIdAuthy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroupXRelatedByIdAuthy relation
 * @method AuthyQuery innerJoinAuthyGroupXRelatedByIdAuthy($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroupXRelatedByIdAuthy relation
 *
 * @method AuthyQuery leftJoinAuthyLog($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyLog relation
 * @method AuthyQuery rightJoinAuthyLog($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyLog relation
 * @method AuthyQuery innerJoinAuthyLog($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyLog relation
 *
 * @method AuthyQuery leftJoinAuthyRelatedByIdAuthy0($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdAuthy0 relation
 * @method AuthyQuery rightJoinAuthyRelatedByIdAuthy0($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdAuthy0 relation
 * @method AuthyQuery innerJoinAuthyRelatedByIdAuthy0($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdAuthy0 relation
 *
 * @method AuthyQuery leftJoinAuthyRelatedByIdAuthy1($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdAuthy1 relation
 * @method AuthyQuery rightJoinAuthyRelatedByIdAuthy1($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdAuthy1 relation
 * @method AuthyQuery innerJoinAuthyRelatedByIdAuthy1($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdAuthy1 relation
 *
 * @method AuthyQuery leftJoinPushDeviceRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the PushDeviceRelatedByIdCreation relation
 * @method AuthyQuery rightJoinPushDeviceRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the PushDeviceRelatedByIdCreation relation
 * @method AuthyQuery innerJoinPushDeviceRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the PushDeviceRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinPushDeviceRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the PushDeviceRelatedByIdModification relation
 * @method AuthyQuery rightJoinPushDeviceRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the PushDeviceRelatedByIdModification relation
 * @method AuthyQuery innerJoinPushDeviceRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the PushDeviceRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinGridRunRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the GridRunRelatedByIdCreation relation
 * @method AuthyQuery rightJoinGridRunRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GridRunRelatedByIdCreation relation
 * @method AuthyQuery innerJoinGridRunRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the GridRunRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinGridRunRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the GridRunRelatedByIdModification relation
 * @method AuthyQuery rightJoinGridRunRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GridRunRelatedByIdModification relation
 * @method AuthyQuery innerJoinGridRunRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the GridRunRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinFleetSlotRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the FleetSlotRelatedByIdCreation relation
 * @method AuthyQuery rightJoinFleetSlotRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the FleetSlotRelatedByIdCreation relation
 * @method AuthyQuery innerJoinFleetSlotRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the FleetSlotRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinFleetSlotRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the FleetSlotRelatedByIdModification relation
 * @method AuthyQuery rightJoinFleetSlotRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the FleetSlotRelatedByIdModification relation
 * @method AuthyQuery innerJoinFleetSlotRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the FleetSlotRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinRegimeEpisodeRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the RegimeEpisodeRelatedByIdCreation relation
 * @method AuthyQuery rightJoinRegimeEpisodeRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the RegimeEpisodeRelatedByIdCreation relation
 * @method AuthyQuery innerJoinRegimeEpisodeRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the RegimeEpisodeRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinRegimeEpisodeRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the RegimeEpisodeRelatedByIdModification relation
 * @method AuthyQuery rightJoinRegimeEpisodeRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the RegimeEpisodeRelatedByIdModification relation
 * @method AuthyQuery innerJoinRegimeEpisodeRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the RegimeEpisodeRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinBotOrderRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the BotOrderRelatedByIdCreation relation
 * @method AuthyQuery rightJoinBotOrderRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BotOrderRelatedByIdCreation relation
 * @method AuthyQuery innerJoinBotOrderRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the BotOrderRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinBotOrderRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the BotOrderRelatedByIdModification relation
 * @method AuthyQuery rightJoinBotOrderRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BotOrderRelatedByIdModification relation
 * @method AuthyQuery innerJoinBotOrderRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the BotOrderRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinTradeCycleRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the TradeCycleRelatedByIdCreation relation
 * @method AuthyQuery rightJoinTradeCycleRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the TradeCycleRelatedByIdCreation relation
 * @method AuthyQuery innerJoinTradeCycleRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the TradeCycleRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinTradeCycleRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the TradeCycleRelatedByIdModification relation
 * @method AuthyQuery rightJoinTradeCycleRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the TradeCycleRelatedByIdModification relation
 * @method AuthyQuery innerJoinTradeCycleRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the TradeCycleRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinBotEventRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the BotEventRelatedByIdCreation relation
 * @method AuthyQuery rightJoinBotEventRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BotEventRelatedByIdCreation relation
 * @method AuthyQuery innerJoinBotEventRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the BotEventRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinBotEventRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the BotEventRelatedByIdModification relation
 * @method AuthyQuery rightJoinBotEventRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BotEventRelatedByIdModification relation
 * @method AuthyQuery innerJoinBotEventRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the BotEventRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinBotCommandRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the BotCommandRelatedByIdCreation relation
 * @method AuthyQuery rightJoinBotCommandRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BotCommandRelatedByIdCreation relation
 * @method AuthyQuery innerJoinBotCommandRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the BotCommandRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinBotCommandRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the BotCommandRelatedByIdModification relation
 * @method AuthyQuery rightJoinBotCommandRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BotCommandRelatedByIdModification relation
 * @method AuthyQuery innerJoinBotCommandRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the BotCommandRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinSimWalletRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the SimWalletRelatedByIdCreation relation
 * @method AuthyQuery rightJoinSimWalletRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the SimWalletRelatedByIdCreation relation
 * @method AuthyQuery innerJoinSimWalletRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the SimWalletRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinSimWalletRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the SimWalletRelatedByIdModification relation
 * @method AuthyQuery rightJoinSimWalletRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the SimWalletRelatedByIdModification relation
 * @method AuthyQuery innerJoinSimWalletRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the SimWalletRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinMarketSummaryRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the MarketSummaryRelatedByIdCreation relation
 * @method AuthyQuery rightJoinMarketSummaryRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the MarketSummaryRelatedByIdCreation relation
 * @method AuthyQuery innerJoinMarketSummaryRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the MarketSummaryRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinMarketSummaryRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the MarketSummaryRelatedByIdModification relation
 * @method AuthyQuery rightJoinMarketSummaryRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the MarketSummaryRelatedByIdModification relation
 * @method AuthyQuery innerJoinMarketSummaryRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the MarketSummaryRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinMarketRegimeRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the MarketRegimeRelatedByIdCreation relation
 * @method AuthyQuery rightJoinMarketRegimeRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the MarketRegimeRelatedByIdCreation relation
 * @method AuthyQuery innerJoinMarketRegimeRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the MarketRegimeRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinMarketRegimeRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the MarketRegimeRelatedByIdModification relation
 * @method AuthyQuery rightJoinMarketRegimeRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the MarketRegimeRelatedByIdModification relation
 * @method AuthyQuery innerJoinMarketRegimeRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the MarketRegimeRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinMarketCandleRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the MarketCandleRelatedByIdCreation relation
 * @method AuthyQuery rightJoinMarketCandleRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the MarketCandleRelatedByIdCreation relation
 * @method AuthyQuery innerJoinMarketCandleRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the MarketCandleRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinMarketCandleRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the MarketCandleRelatedByIdModification relation
 * @method AuthyQuery rightJoinMarketCandleRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the MarketCandleRelatedByIdModification relation
 * @method AuthyQuery innerJoinMarketCandleRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the MarketCandleRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinBotDecisionRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the BotDecisionRelatedByIdCreation relation
 * @method AuthyQuery rightJoinBotDecisionRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BotDecisionRelatedByIdCreation relation
 * @method AuthyQuery innerJoinBotDecisionRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the BotDecisionRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinBotDecisionRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the BotDecisionRelatedByIdModification relation
 * @method AuthyQuery rightJoinBotDecisionRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BotDecisionRelatedByIdModification relation
 * @method AuthyQuery innerJoinBotDecisionRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the BotDecisionRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinMarketOutlookRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the MarketOutlookRelatedByIdCreation relation
 * @method AuthyQuery rightJoinMarketOutlookRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the MarketOutlookRelatedByIdCreation relation
 * @method AuthyQuery innerJoinMarketOutlookRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the MarketOutlookRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinMarketOutlookRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the MarketOutlookRelatedByIdModification relation
 * @method AuthyQuery rightJoinMarketOutlookRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the MarketOutlookRelatedByIdModification relation
 * @method AuthyQuery innerJoinMarketOutlookRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the MarketOutlookRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinMarketOutlookStateRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the MarketOutlookStateRelatedByIdCreation relation
 * @method AuthyQuery rightJoinMarketOutlookStateRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the MarketOutlookStateRelatedByIdCreation relation
 * @method AuthyQuery innerJoinMarketOutlookStateRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the MarketOutlookStateRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinMarketOutlookStateRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the MarketOutlookStateRelatedByIdModification relation
 * @method AuthyQuery rightJoinMarketOutlookStateRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the MarketOutlookStateRelatedByIdModification relation
 * @method AuthyQuery innerJoinMarketOutlookStateRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the MarketOutlookStateRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinWalletNavRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the WalletNavRelatedByIdCreation relation
 * @method AuthyQuery rightJoinWalletNavRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the WalletNavRelatedByIdCreation relation
 * @method AuthyQuery innerJoinWalletNavRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the WalletNavRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinWalletNavRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the WalletNavRelatedByIdModification relation
 * @method AuthyQuery rightJoinWalletNavRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the WalletNavRelatedByIdModification relation
 * @method AuthyQuery innerJoinWalletNavRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the WalletNavRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinAuthyGroupRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroupRelatedByIdCreation relation
 * @method AuthyQuery rightJoinAuthyGroupRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroupRelatedByIdCreation relation
 * @method AuthyQuery innerJoinAuthyGroupRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroupRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinAuthyGroupRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroupRelatedByIdModification relation
 * @method AuthyQuery rightJoinAuthyGroupRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroupRelatedByIdModification relation
 * @method AuthyQuery innerJoinAuthyGroupRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroupRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinAuthyGroupXRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroupXRelatedByIdCreation relation
 * @method AuthyQuery rightJoinAuthyGroupXRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroupXRelatedByIdCreation relation
 * @method AuthyQuery innerJoinAuthyGroupXRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroupXRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinAuthyGroupXRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroupXRelatedByIdModification relation
 * @method AuthyQuery rightJoinAuthyGroupXRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroupXRelatedByIdModification relation
 * @method AuthyQuery innerJoinAuthyGroupXRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroupXRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinConfigRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the ConfigRelatedByIdCreation relation
 * @method AuthyQuery rightJoinConfigRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ConfigRelatedByIdCreation relation
 * @method AuthyQuery innerJoinConfigRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the ConfigRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinConfigRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the ConfigRelatedByIdModification relation
 * @method AuthyQuery rightJoinConfigRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ConfigRelatedByIdModification relation
 * @method AuthyQuery innerJoinConfigRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the ConfigRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinApiRbacRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the ApiRbacRelatedByIdCreation relation
 * @method AuthyQuery rightJoinApiRbacRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ApiRbacRelatedByIdCreation relation
 * @method AuthyQuery innerJoinApiRbacRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the ApiRbacRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinApiRbacRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the ApiRbacRelatedByIdModification relation
 * @method AuthyQuery rightJoinApiRbacRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ApiRbacRelatedByIdModification relation
 * @method AuthyQuery innerJoinApiRbacRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the ApiRbacRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinApiLog($relationAlias = null) Adds a LEFT JOIN clause to the query using the ApiLog relation
 * @method AuthyQuery rightJoinApiLog($relationAlias = null) Adds a RIGHT JOIN clause to the query using the ApiLog relation
 * @method AuthyQuery innerJoinApiLog($relationAlias = null) Adds a INNER JOIN clause to the query using the ApiLog relation
 *
 * @method AuthyQuery leftJoinTemplateRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the TemplateRelatedByIdCreation relation
 * @method AuthyQuery rightJoinTemplateRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the TemplateRelatedByIdCreation relation
 * @method AuthyQuery innerJoinTemplateRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the TemplateRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinTemplateRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the TemplateRelatedByIdModification relation
 * @method AuthyQuery rightJoinTemplateRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the TemplateRelatedByIdModification relation
 * @method AuthyQuery innerJoinTemplateRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the TemplateRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinTemplateFileRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the TemplateFileRelatedByIdCreation relation
 * @method AuthyQuery rightJoinTemplateFileRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the TemplateFileRelatedByIdCreation relation
 * @method AuthyQuery innerJoinTemplateFileRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the TemplateFileRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinTemplateFileRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the TemplateFileRelatedByIdModification relation
 * @method AuthyQuery rightJoinTemplateFileRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the TemplateFileRelatedByIdModification relation
 * @method AuthyQuery innerJoinTemplateFileRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the TemplateFileRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinAuthyRefreshTokenRelatedByIdAuthy($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRefreshTokenRelatedByIdAuthy relation
 * @method AuthyQuery rightJoinAuthyRefreshTokenRelatedByIdAuthy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRefreshTokenRelatedByIdAuthy relation
 * @method AuthyQuery innerJoinAuthyRefreshTokenRelatedByIdAuthy($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRefreshTokenRelatedByIdAuthy relation
 *
 * @method AuthyQuery leftJoinAuthyRefreshTokenRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRefreshTokenRelatedByIdCreation relation
 * @method AuthyQuery rightJoinAuthyRefreshTokenRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRefreshTokenRelatedByIdCreation relation
 * @method AuthyQuery innerJoinAuthyRefreshTokenRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRefreshTokenRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinAuthyRefreshTokenRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRefreshTokenRelatedByIdModification relation
 * @method AuthyQuery rightJoinAuthyRefreshTokenRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRefreshTokenRelatedByIdModification relation
 * @method AuthyQuery innerJoinAuthyRefreshTokenRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRefreshTokenRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinGridRunAuditRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the GridRunAuditRelatedByIdCreation relation
 * @method AuthyQuery rightJoinGridRunAuditRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GridRunAuditRelatedByIdCreation relation
 * @method AuthyQuery innerJoinGridRunAuditRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the GridRunAuditRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinGridRunAuditRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the GridRunAuditRelatedByIdModification relation
 * @method AuthyQuery rightJoinGridRunAuditRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GridRunAuditRelatedByIdModification relation
 * @method AuthyQuery innerJoinGridRunAuditRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the GridRunAuditRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinCountryRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the CountryRelatedByIdCreation relation
 * @method AuthyQuery rightJoinCountryRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the CountryRelatedByIdCreation relation
 * @method AuthyQuery innerJoinCountryRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the CountryRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinCountryRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the CountryRelatedByIdModification relation
 * @method AuthyQuery rightJoinCountryRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the CountryRelatedByIdModification relation
 * @method AuthyQuery innerJoinCountryRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the CountryRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinOauthClientRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the OauthClientRelatedByIdCreation relation
 * @method AuthyQuery rightJoinOauthClientRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the OauthClientRelatedByIdCreation relation
 * @method AuthyQuery innerJoinOauthClientRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the OauthClientRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinOauthClientRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the OauthClientRelatedByIdModification relation
 * @method AuthyQuery rightJoinOauthClientRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the OauthClientRelatedByIdModification relation
 * @method AuthyQuery innerJoinOauthClientRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the OauthClientRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinOauthAuthCodeRelatedByIdAuthy($relationAlias = null) Adds a LEFT JOIN clause to the query using the OauthAuthCodeRelatedByIdAuthy relation
 * @method AuthyQuery rightJoinOauthAuthCodeRelatedByIdAuthy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the OauthAuthCodeRelatedByIdAuthy relation
 * @method AuthyQuery innerJoinOauthAuthCodeRelatedByIdAuthy($relationAlias = null) Adds a INNER JOIN clause to the query using the OauthAuthCodeRelatedByIdAuthy relation
 *
 * @method AuthyQuery leftJoinOauthAuthCodeRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the OauthAuthCodeRelatedByIdCreation relation
 * @method AuthyQuery rightJoinOauthAuthCodeRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the OauthAuthCodeRelatedByIdCreation relation
 * @method AuthyQuery innerJoinOauthAuthCodeRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the OauthAuthCodeRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinOauthAuthCodeRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the OauthAuthCodeRelatedByIdModification relation
 * @method AuthyQuery rightJoinOauthAuthCodeRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the OauthAuthCodeRelatedByIdModification relation
 * @method AuthyQuery innerJoinOauthAuthCodeRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the OauthAuthCodeRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinOauthAccessTokenRelatedByIdAuthy($relationAlias = null) Adds a LEFT JOIN clause to the query using the OauthAccessTokenRelatedByIdAuthy relation
 * @method AuthyQuery rightJoinOauthAccessTokenRelatedByIdAuthy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the OauthAccessTokenRelatedByIdAuthy relation
 * @method AuthyQuery innerJoinOauthAccessTokenRelatedByIdAuthy($relationAlias = null) Adds a INNER JOIN clause to the query using the OauthAccessTokenRelatedByIdAuthy relation
 *
 * @method AuthyQuery leftJoinOauthAccessTokenRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the OauthAccessTokenRelatedByIdCreation relation
 * @method AuthyQuery rightJoinOauthAccessTokenRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the OauthAccessTokenRelatedByIdCreation relation
 * @method AuthyQuery innerJoinOauthAccessTokenRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the OauthAccessTokenRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinOauthAccessTokenRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the OauthAccessTokenRelatedByIdModification relation
 * @method AuthyQuery rightJoinOauthAccessTokenRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the OauthAccessTokenRelatedByIdModification relation
 * @method AuthyQuery innerJoinOauthAccessTokenRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the OauthAccessTokenRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinOauthRefreshTokenRelatedByIdAuthy($relationAlias = null) Adds a LEFT JOIN clause to the query using the OauthRefreshTokenRelatedByIdAuthy relation
 * @method AuthyQuery rightJoinOauthRefreshTokenRelatedByIdAuthy($relationAlias = null) Adds a RIGHT JOIN clause to the query using the OauthRefreshTokenRelatedByIdAuthy relation
 * @method AuthyQuery innerJoinOauthRefreshTokenRelatedByIdAuthy($relationAlias = null) Adds a INNER JOIN clause to the query using the OauthRefreshTokenRelatedByIdAuthy relation
 *
 * @method AuthyQuery leftJoinOauthRefreshTokenRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the OauthRefreshTokenRelatedByIdCreation relation
 * @method AuthyQuery rightJoinOauthRefreshTokenRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the OauthRefreshTokenRelatedByIdCreation relation
 * @method AuthyQuery innerJoinOauthRefreshTokenRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the OauthRefreshTokenRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinOauthRefreshTokenRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the OauthRefreshTokenRelatedByIdModification relation
 * @method AuthyQuery rightJoinOauthRefreshTokenRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the OauthRefreshTokenRelatedByIdModification relation
 * @method AuthyQuery innerJoinOauthRefreshTokenRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the OauthRefreshTokenRelatedByIdModification relation
 *
 * @method AuthyQuery leftJoinMessageI18nRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the MessageI18nRelatedByIdCreation relation
 * @method AuthyQuery rightJoinMessageI18nRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the MessageI18nRelatedByIdCreation relation
 * @method AuthyQuery innerJoinMessageI18nRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the MessageI18nRelatedByIdCreation relation
 *
 * @method AuthyQuery leftJoinMessageI18nRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the MessageI18nRelatedByIdModification relation
 * @method AuthyQuery rightJoinMessageI18nRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the MessageI18nRelatedByIdModification relation
 * @method AuthyQuery innerJoinMessageI18nRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the MessageI18nRelatedByIdModification relation
 *
 * @method Authy findOne(?PropelPDO $con = null) Return the first Authy matching the query
 * @method Authy findOneOrCreate(?PropelPDO $con = null) Return the first Authy matching the query, or a new Authy object populated from the query conditions when no match is found
 *
 * @method Authy findOneByValidationKey(string $validation_key) Return the first Authy filtered by the validation_key column
 * @method Authy findOneByUsername(string $username) Return the first Authy filtered by the username column
 * @method Authy findOneByFullname(string $fullname) Return the first Authy filtered by the fullname column
 * @method Authy findOneByEmail(string $email) Return the first Authy filtered by the email column
 * @method Authy findOneByPasswdHash(string $passwd_hash) Return the first Authy filtered by the passwd_hash column
 * @method Authy findOneByExpire(string $expire) Return the first Authy filtered by the expire column
 * @method Authy findOneByDeactivate(int $deactivate) Return the first Authy filtered by the deactivate column
 * @method Authy findOneByLanguage(int $language) Return the first Authy filtered by the language column
 * @method Authy findOneByTheme(int $theme) Return the first Authy filtered by the theme column
 * @method Authy findOneByGoogleSub(string $google_sub) Return the first Authy filtered by the google_sub column
 * @method Authy findOneByGoogleEmail(string $google_email) Return the first Authy filtered by the google_email column
 * @method Authy findOneByResetTokenHash(string $reset_token_hash) Return the first Authy filtered by the reset_token_hash column
 * @method Authy findOneByResetTokenExpires(int $reset_token_expires) Return the first Authy filtered by the reset_token_expires column
 * @method Authy findOneByIdTenant(int $id_tenant) Return the first Authy filtered by the id_tenant column
 * @method Authy findOneByLocationAddress(string $location_address) Return the first Authy filtered by the location_address column
 * @method Authy findOneByLocationLat(string $location_lat) Return the first Authy filtered by the location_lat column
 * @method Authy findOneByLocationLng(string $location_lng) Return the first Authy filtered by the location_lng column
 * @method Authy findOneByIsRoot(int $is_root) Return the first Authy filtered by the is_root column
 * @method Authy findOneByIdAuthyGroup(int $id_authy_group) Return the first Authy filtered by the id_authy_group column
 * @method Authy findOneByIsSystem(int $is_system) Return the first Authy filtered by the is_system column
 * @method Authy findOneByRightsAll(string $rights_all) Return the first Authy filtered by the rights_all column
 * @method Authy findOneByRightsGroup(string $rights_group) Return the first Authy filtered by the rights_group column
 * @method Authy findOneByRightsOwner(string $rights_owner) Return the first Authy filtered by the rights_owner column
 * @method Authy findOneByOnglet(string $onglet) Return the first Authy filtered by the onglet column
 * @method Authy findOneByDateCreation(string $date_creation) Return the first Authy filtered by the date_creation column
 * @method Authy findOneByDateModification(string $date_modification) Return the first Authy filtered by the date_modification column
 * @method Authy findOneByIdGroupCreation(int $id_group_creation) Return the first Authy filtered by the id_group_creation column
 * @method Authy findOneByIdCreation(int $id_creation) Return the first Authy filtered by the id_creation column
 * @method Authy findOneByIdModification(int $id_modification) Return the first Authy filtered by the id_modification column
 *
 * @method array findByIdAuthy(int $id_authy) Return Authy objects filtered by the id_authy column
 * @method array findByValidationKey(string $validation_key) Return Authy objects filtered by the validation_key column
 * @method array findByUsername(string $username) Return Authy objects filtered by the username column
 * @method array findByFullname(string $fullname) Return Authy objects filtered by the fullname column
 * @method array findByEmail(string $email) Return Authy objects filtered by the email column
 * @method array findByPasswdHash(string $passwd_hash) Return Authy objects filtered by the passwd_hash column
 * @method array findByExpire(string $expire) Return Authy objects filtered by the expire column
 * @method array findByDeactivate(int $deactivate) Return Authy objects filtered by the deactivate column
 * @method array findByLanguage(int $language) Return Authy objects filtered by the language column
 * @method array findByTheme(int $theme) Return Authy objects filtered by the theme column
 * @method array findByGoogleSub(string $google_sub) Return Authy objects filtered by the google_sub column
 * @method array findByGoogleEmail(string $google_email) Return Authy objects filtered by the google_email column
 * @method array findByResetTokenHash(string $reset_token_hash) Return Authy objects filtered by the reset_token_hash column
 * @method array findByResetTokenExpires(int $reset_token_expires) Return Authy objects filtered by the reset_token_expires column
 * @method array findByIdTenant(int $id_tenant) Return Authy objects filtered by the id_tenant column
 * @method array findByLocationAddress(string $location_address) Return Authy objects filtered by the location_address column
 * @method array findByLocationLat(string $location_lat) Return Authy objects filtered by the location_lat column
 * @method array findByLocationLng(string $location_lng) Return Authy objects filtered by the location_lng column
 * @method array findByIsRoot(int $is_root) Return Authy objects filtered by the is_root column
 * @method array findByIdAuthyGroup(int $id_authy_group) Return Authy objects filtered by the id_authy_group column
 * @method array findByIsSystem(int $is_system) Return Authy objects filtered by the is_system column
 * @method array findByRightsAll(string $rights_all) Return Authy objects filtered by the rights_all column
 * @method array findByRightsGroup(string $rights_group) Return Authy objects filtered by the rights_group column
 * @method array findByRightsOwner(string $rights_owner) Return Authy objects filtered by the rights_owner column
 * @method array findByOnglet(string $onglet) Return Authy objects filtered by the onglet column
 * @method array findByDateCreation(string $date_creation) Return Authy objects filtered by the date_creation column
 * @method array findByDateModification(string $date_modification) Return Authy objects filtered by the date_modification column
 * @method array findByIdGroupCreation(int $id_group_creation) Return Authy objects filtered by the id_group_creation column
 * @method array findByIdCreation(int $id_creation) Return Authy objects filtered by the id_creation column
 * @method array findByIdModification(int $id_modification) Return Authy objects filtered by the id_modification column
 *
 * @package    propel.generator..om
 */
abstract class BaseAuthyQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseAuthyQuery object.
     *
     * @param     string $dbName The dabase name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = null, $modelName = null, $modelAlias = null)
    {
        if (null === $dbName) {
            $dbName = 'apigtbot';
        }
        if (null === $modelName) {
            $modelName = 'App\\Authy';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new AuthyQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   AuthyQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return AuthyQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof AuthyQuery) {
            return $criteria;
        }
        $query = new AuthyQuery(null, null, $modelAlias);

        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * @Query()
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param     PropelPDO $con an optional connection object
     *
     * @return   Authy|Authy[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = AuthyPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(AuthyPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }
        $this->basePreSelect($con);
        if ($this->formatter || $this->modelAlias || $this->with || $this->select
         || $this->selectColumns || $this->asColumns || $this->selectModifiers
         || $this->map || $this->having || $this->joins) {
            return $this->findPkComplex($key, $con);
        } else {
            return $this->findPkSimple($key, $con);
        }
    }

    /**
     * Alias of findPk to use instance pooling
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return                 Authy A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdAuthy($key, $con = null)
     {
        return $this->findPk($key, $con);
     }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return                 Authy A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_authy`, `validation_key`, `username`, `fullname`, `email`, `passwd_hash`, `expire`, `deactivate`, `language`, `theme`, `google_sub`, `google_email`, `reset_token_hash`, `reset_token_expires`, `id_tenant`, `location_address`, `location_lat`, `location_lng`, `is_root`, `id_authy_group`, `is_system`, `rights_all`, `rights_group`, `rights_owner`, `onglet`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification` FROM `authy` WHERE `id_authy` = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $obj = new Authy();
            $obj->hydrate($row);
            AuthyPeer::addInstanceToPool($obj, (string) $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * @Query()
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     PropelPDO $con A connection object
     *
     * @return Authy|Authy[]|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $stmt = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($stmt);
    }


    /**
     * @Query()
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     PropelPDO $con an optional connection object
     *
     * @return PropelObjectCollection|Authy[]|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection($this->getDbName(), Propel::CONNECTION_READ);
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $stmt = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($stmt);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(AuthyPeer::ID_AUTHY, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(AuthyPeer::ID_AUTHY, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id_authy column
     *
     * Example usage:
     * <code>
     * $query->filterByIdAuthy(1234); // WHERE id_authy = 1234
     * $query->filterByIdAuthy(array(12, 34)); // WHERE id_authy IN (12, 34)
     * $query->filterByIdAuthy(array('min' => 12)); // WHERE id_authy >= 12
     * $query->filterByIdAuthy(array('max' => 12)); // WHERE id_authy <= 12
     * </code>
     *
     * @param     mixed $idAuthy The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByIdAuthy($idAuthy = null, $comparison = null)
    {
        if (is_array($idAuthy)) {
            $useMinMax = false;
            if (isset($idAuthy['min'])) {
                $this->addUsingAlias(AuthyPeer::ID_AUTHY, $idAuthy['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idAuthy['max'])) {
                $this->addUsingAlias(AuthyPeer::ID_AUTHY, $idAuthy['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyPeer::ID_AUTHY, $idAuthy, $comparison);
    }

    /**
     * Filter the query on the validation_key column
     *
     * Example usage:
     * <code>
     * $query->filterByValidationKey('fooValue');   // WHERE validation_key = 'fooValue'
     * $query->filterByValidationKey('%fooValue%'); // WHERE validation_key LIKE '%fooValue%'
     * </code>
     *
     * @param     string $validationKey The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByValidationKey($validationKey = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($validationKey)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $validationKey)) {
                $validationKey = str_replace('*', '%', $validationKey);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyPeer::VALIDATION_KEY, $validationKey, $comparison);
    }

    /**
     * Filter the query on the username column
     *
     * Example usage:
     * <code>
     * $query->filterByUsername('fooValue');   // WHERE username = 'fooValue'
     * $query->filterByUsername('%fooValue%'); // WHERE username LIKE '%fooValue%'
     * </code>
     *
     * @param     string $username The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByUsername($username = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($username)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $username)) {
                $username = str_replace('*', '%', $username);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyPeer::USERNAME, $username, $comparison);
    }

    /**
     * Filter the query on the fullname column
     *
     * Example usage:
     * <code>
     * $query->filterByFullname('fooValue');   // WHERE fullname = 'fooValue'
     * $query->filterByFullname('%fooValue%'); // WHERE fullname LIKE '%fooValue%'
     * </code>
     *
     * @param     string $fullname The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByFullname($fullname = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($fullname)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $fullname)) {
                $fullname = str_replace('*', '%', $fullname);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyPeer::FULLNAME, $fullname, $comparison);
    }

    /**
     * Filter the query on the email column
     *
     * Example usage:
     * <code>
     * $query->filterByEmail('fooValue');   // WHERE email = 'fooValue'
     * $query->filterByEmail('%fooValue%'); // WHERE email LIKE '%fooValue%'
     * </code>
     *
     * @param     string $email The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByEmail($email = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($email)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $email)) {
                $email = str_replace('*', '%', $email);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyPeer::EMAIL, $email, $comparison);
    }

    /**
     * Filter the query on the passwd_hash column
     *
     * Example usage:
     * <code>
     * $query->filterByPasswdHash('fooValue');   // WHERE passwd_hash = 'fooValue'
     * $query->filterByPasswdHash('%fooValue%'); // WHERE passwd_hash LIKE '%fooValue%'
     * </code>
     *
     * @param     string $passwdHash The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByPasswdHash($passwdHash = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($passwdHash)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $passwdHash)) {
                $passwdHash = str_replace('*', '%', $passwdHash);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyPeer::PASSWD_HASH, $passwdHash, $comparison);
    }

    /**
     * Filter the query on the expire column
     *
     * Example usage:
     * <code>
     * $query->filterByExpire('2011-03-14'); // WHERE expire = '2011-03-14'
     * $query->filterByExpire('now'); // WHERE expire = '2011-03-14'
     * $query->filterByExpire(array('max' => 'yesterday')); // WHERE expire < '2011-03-13'
     * </code>
     *
     * @param     mixed $expire The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByExpire($expire = null, $comparison = null)
    {
        if (is_array($expire)) {
            $useMinMax = false;
            if (isset($expire['min'])) {
                $this->addUsingAlias(AuthyPeer::EXPIRE, $expire['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($expire['max'])) {
                $this->addUsingAlias(AuthyPeer::EXPIRE, $expire['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyPeer::EXPIRE, $expire, $comparison);
    }

    /**
     * Filter the query on the deactivate column
     *
     * @param     mixed $deactivate The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByDeactivate($deactivate = null, $comparison = null)
    {
        if (is_scalar($deactivate)) {
            $deactivate = AuthyPeer::getSqlValueForEnum(AuthyPeer::DEACTIVATE, $deactivate);
        } elseif (is_array($deactivate)) {
            $convertedValues = array();
            foreach ($deactivate as $value) {
                $convertedValues[] = AuthyPeer::getSqlValueForEnum(AuthyPeer::DEACTIVATE, $value);
            }
            $deactivate = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyPeer::DEACTIVATE, $deactivate, $comparison);
    }

    /**
     * Filter the query on the language column
     *
     * @param     mixed $language The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByLanguage($language = null, $comparison = null)
    {
        if (is_scalar($language)) {
            $language = AuthyPeer::getSqlValueForEnum(AuthyPeer::LANGUAGE, $language);
        } elseif (is_array($language)) {
            $convertedValues = array();
            foreach ($language as $value) {
                $convertedValues[] = AuthyPeer::getSqlValueForEnum(AuthyPeer::LANGUAGE, $value);
            }
            $language = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyPeer::LANGUAGE, $language, $comparison);
    }

    /**
     * Filter the query on the theme column
     *
     * @param     mixed $theme The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByTheme($theme = null, $comparison = null)
    {
        if (is_scalar($theme)) {
            $theme = AuthyPeer::getSqlValueForEnum(AuthyPeer::THEME, $theme);
        } elseif (is_array($theme)) {
            $convertedValues = array();
            foreach ($theme as $value) {
                $convertedValues[] = AuthyPeer::getSqlValueForEnum(AuthyPeer::THEME, $value);
            }
            $theme = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyPeer::THEME, $theme, $comparison);
    }

    /**
     * Filter the query on the google_sub column
     *
     * Example usage:
     * <code>
     * $query->filterByGoogleSub('fooValue');   // WHERE google_sub = 'fooValue'
     * $query->filterByGoogleSub('%fooValue%'); // WHERE google_sub LIKE '%fooValue%'
     * </code>
     *
     * @param     string $googleSub The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByGoogleSub($googleSub = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($googleSub)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $googleSub)) {
                $googleSub = str_replace('*', '%', $googleSub);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyPeer::GOOGLE_SUB, $googleSub, $comparison);
    }

    /**
     * Filter the query on the google_email column
     *
     * Example usage:
     * <code>
     * $query->filterByGoogleEmail('fooValue');   // WHERE google_email = 'fooValue'
     * $query->filterByGoogleEmail('%fooValue%'); // WHERE google_email LIKE '%fooValue%'
     * </code>
     *
     * @param     string $googleEmail The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByGoogleEmail($googleEmail = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($googleEmail)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $googleEmail)) {
                $googleEmail = str_replace('*', '%', $googleEmail);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyPeer::GOOGLE_EMAIL, $googleEmail, $comparison);
    }

    /**
     * Filter the query on the reset_token_hash column
     *
     * Example usage:
     * <code>
     * $query->filterByResetTokenHash('fooValue');   // WHERE reset_token_hash = 'fooValue'
     * $query->filterByResetTokenHash('%fooValue%'); // WHERE reset_token_hash LIKE '%fooValue%'
     * </code>
     *
     * @param     string $resetTokenHash The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByResetTokenHash($resetTokenHash = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($resetTokenHash)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $resetTokenHash)) {
                $resetTokenHash = str_replace('*', '%', $resetTokenHash);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyPeer::RESET_TOKEN_HASH, $resetTokenHash, $comparison);
    }

    /**
     * Filter the query on the reset_token_expires column
     *
     * Example usage:
     * <code>
     * $query->filterByResetTokenExpires(1234); // WHERE reset_token_expires = 1234
     * $query->filterByResetTokenExpires(array(12, 34)); // WHERE reset_token_expires IN (12, 34)
     * $query->filterByResetTokenExpires(array('min' => 12)); // WHERE reset_token_expires >= 12
     * $query->filterByResetTokenExpires(array('max' => 12)); // WHERE reset_token_expires <= 12
     * </code>
     *
     * @param     mixed $resetTokenExpires The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByResetTokenExpires($resetTokenExpires = null, $comparison = null)
    {
        if (is_array($resetTokenExpires)) {
            $useMinMax = false;
            if (isset($resetTokenExpires['min'])) {
                $this->addUsingAlias(AuthyPeer::RESET_TOKEN_EXPIRES, $resetTokenExpires['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($resetTokenExpires['max'])) {
                $this->addUsingAlias(AuthyPeer::RESET_TOKEN_EXPIRES, $resetTokenExpires['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyPeer::RESET_TOKEN_EXPIRES, $resetTokenExpires, $comparison);
    }

    /**
     * Filter the query on the id_tenant column
     *
     * Example usage:
     * <code>
     * $query->filterByIdTenant(1234); // WHERE id_tenant = 1234
     * $query->filterByIdTenant(array(12, 34)); // WHERE id_tenant IN (12, 34)
     * $query->filterByIdTenant(array('min' => 12)); // WHERE id_tenant >= 12
     * $query->filterByIdTenant(array('max' => 12)); // WHERE id_tenant <= 12
     * </code>
     *
     * @param     mixed $idTenant The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByIdTenant($idTenant = null, $comparison = null)
    {
        if (is_array($idTenant)) {
            $useMinMax = false;
            if (isset($idTenant['min'])) {
                $this->addUsingAlias(AuthyPeer::ID_TENANT, $idTenant['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idTenant['max'])) {
                $this->addUsingAlias(AuthyPeer::ID_TENANT, $idTenant['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyPeer::ID_TENANT, $idTenant, $comparison);
    }

    /**
     * Filter the query on the location_address column
     *
     * Example usage:
     * <code>
     * $query->filterByLocationAddress('fooValue');   // WHERE location_address = 'fooValue'
     * $query->filterByLocationAddress('%fooValue%'); // WHERE location_address LIKE '%fooValue%'
     * </code>
     *
     * @param     string $locationAddress The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByLocationAddress($locationAddress = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($locationAddress)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $locationAddress)) {
                $locationAddress = str_replace('*', '%', $locationAddress);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyPeer::LOCATION_ADDRESS, $locationAddress, $comparison);
    }

    /**
     * Filter the query on the location_lat column
     *
     * Example usage:
     * <code>
     * $query->filterByLocationLat(1234); // WHERE location_lat = 1234
     * $query->filterByLocationLat(array(12, 34)); // WHERE location_lat IN (12, 34)
     * $query->filterByLocationLat(array('min' => 12)); // WHERE location_lat >= 12
     * $query->filterByLocationLat(array('max' => 12)); // WHERE location_lat <= 12
     * </code>
     *
     * @param     mixed $locationLat The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByLocationLat($locationLat = null, $comparison = null)
    {
        if (is_array($locationLat)) {
            $useMinMax = false;
            if (isset($locationLat['min'])) {
                $this->addUsingAlias(AuthyPeer::LOCATION_LAT, $locationLat['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($locationLat['max'])) {
                $this->addUsingAlias(AuthyPeer::LOCATION_LAT, $locationLat['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyPeer::LOCATION_LAT, $locationLat, $comparison);
    }

    /**
     * Filter the query on the location_lng column
     *
     * Example usage:
     * <code>
     * $query->filterByLocationLng(1234); // WHERE location_lng = 1234
     * $query->filterByLocationLng(array(12, 34)); // WHERE location_lng IN (12, 34)
     * $query->filterByLocationLng(array('min' => 12)); // WHERE location_lng >= 12
     * $query->filterByLocationLng(array('max' => 12)); // WHERE location_lng <= 12
     * </code>
     *
     * @param     mixed $locationLng The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByLocationLng($locationLng = null, $comparison = null)
    {
        if (is_array($locationLng)) {
            $useMinMax = false;
            if (isset($locationLng['min'])) {
                $this->addUsingAlias(AuthyPeer::LOCATION_LNG, $locationLng['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($locationLng['max'])) {
                $this->addUsingAlias(AuthyPeer::LOCATION_LNG, $locationLng['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyPeer::LOCATION_LNG, $locationLng, $comparison);
    }

    /**
     * Filter the query on the is_root column
     *
     * @param     mixed $isRoot The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByIsRoot($isRoot = null, $comparison = null)
    {
        if (is_scalar($isRoot)) {
            $isRoot = AuthyPeer::getSqlValueForEnum(AuthyPeer::IS_ROOT, $isRoot);
        } elseif (is_array($isRoot)) {
            $convertedValues = array();
            foreach ($isRoot as $value) {
                $convertedValues[] = AuthyPeer::getSqlValueForEnum(AuthyPeer::IS_ROOT, $value);
            }
            $isRoot = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyPeer::IS_ROOT, $isRoot, $comparison);
    }

    /**
     * Filter the query on the id_authy_group column
     *
     * Example usage:
     * <code>
     * $query->filterByIdAuthyGroup(1234); // WHERE id_authy_group = 1234
     * $query->filterByIdAuthyGroup(array(12, 34)); // WHERE id_authy_group IN (12, 34)
     * $query->filterByIdAuthyGroup(array('min' => 12)); // WHERE id_authy_group >= 12
     * $query->filterByIdAuthyGroup(array('max' => 12)); // WHERE id_authy_group <= 12
     * </code>
     *
     * @see       filterByAuthyGroupRelatedByIdAuthyGroup()
     *
     * @param     mixed $idAuthyGroup The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByIdAuthyGroup($idAuthyGroup = null, $comparison = null)
    {
        if (is_array($idAuthyGroup)) {
            $useMinMax = false;
            if (isset($idAuthyGroup['min'])) {
                $this->addUsingAlias(AuthyPeer::ID_AUTHY_GROUP, $idAuthyGroup['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idAuthyGroup['max'])) {
                $this->addUsingAlias(AuthyPeer::ID_AUTHY_GROUP, $idAuthyGroup['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyPeer::ID_AUTHY_GROUP, $idAuthyGroup, $comparison);
    }

    /**
     * Filter the query on the is_system column
     *
     * @param     mixed $isSystem The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByIsSystem($isSystem = null, $comparison = null)
    {
        if (is_scalar($isSystem)) {
            $isSystem = AuthyPeer::getSqlValueForEnum(AuthyPeer::IS_SYSTEM, $isSystem);
        } elseif (is_array($isSystem)) {
            $convertedValues = array();
            foreach ($isSystem as $value) {
                $convertedValues[] = AuthyPeer::getSqlValueForEnum(AuthyPeer::IS_SYSTEM, $value);
            }
            $isSystem = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyPeer::IS_SYSTEM, $isSystem, $comparison);
    }

    /**
     * Filter the query on the rights_all column
     *
     * Example usage:
     * <code>
     * $query->filterByRightsAll('fooValue');   // WHERE rights_all = 'fooValue'
     * $query->filterByRightsAll('%fooValue%'); // WHERE rights_all LIKE '%fooValue%'
     * </code>
     *
     * @param     string $rightsAll The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByRightsAll($rightsAll = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($rightsAll)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $rightsAll)) {
                $rightsAll = str_replace('*', '%', $rightsAll);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyPeer::RIGHTS_ALL, $rightsAll, $comparison);
    }

    /**
     * Filter the query on the rights_group column
     *
     * Example usage:
     * <code>
     * $query->filterByRightsGroup('fooValue');   // WHERE rights_group = 'fooValue'
     * $query->filterByRightsGroup('%fooValue%'); // WHERE rights_group LIKE '%fooValue%'
     * </code>
     *
     * @param     string $rightsGroup The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByRightsGroup($rightsGroup = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($rightsGroup)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $rightsGroup)) {
                $rightsGroup = str_replace('*', '%', $rightsGroup);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyPeer::RIGHTS_GROUP, $rightsGroup, $comparison);
    }

    /**
     * Filter the query on the rights_owner column
     *
     * Example usage:
     * <code>
     * $query->filterByRightsOwner('fooValue');   // WHERE rights_owner = 'fooValue'
     * $query->filterByRightsOwner('%fooValue%'); // WHERE rights_owner LIKE '%fooValue%'
     * </code>
     *
     * @param     string $rightsOwner The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByRightsOwner($rightsOwner = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($rightsOwner)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $rightsOwner)) {
                $rightsOwner = str_replace('*', '%', $rightsOwner);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyPeer::RIGHTS_OWNER, $rightsOwner, $comparison);
    }

    /**
     * Filter the query on the onglet column
     *
     * Example usage:
     * <code>
     * $query->filterByOnglet('fooValue');   // WHERE onglet = 'fooValue'
     * $query->filterByOnglet('%fooValue%'); // WHERE onglet LIKE '%fooValue%'
     * </code>
     *
     * @param     string $onglet The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByOnglet($onglet = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($onglet)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $onglet)) {
                $onglet = str_replace('*', '%', $onglet);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(AuthyPeer::ONGLET, $onglet, $comparison);
    }

    /**
     * Filter the query on the date_creation column
     *
     * Example usage:
     * <code>
     * $query->filterByDateCreation('2011-03-14'); // WHERE date_creation = '2011-03-14'
     * $query->filterByDateCreation('now'); // WHERE date_creation = '2011-03-14'
     * $query->filterByDateCreation(array('max' => 'yesterday')); // WHERE date_creation < '2011-03-13'
     * </code>
     *
     * @param     mixed $dateCreation The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByDateCreation($dateCreation = null, $comparison = null)
    {
        if (is_array($dateCreation)) {
            $useMinMax = false;
            if (isset($dateCreation['min'])) {
                $this->addUsingAlias(AuthyPeer::DATE_CREATION, $dateCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateCreation['max'])) {
                $this->addUsingAlias(AuthyPeer::DATE_CREATION, $dateCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyPeer::DATE_CREATION, $dateCreation, $comparison);
    }

    /**
     * Filter the query on the date_modification column
     *
     * Example usage:
     * <code>
     * $query->filterByDateModification('2011-03-14'); // WHERE date_modification = '2011-03-14'
     * $query->filterByDateModification('now'); // WHERE date_modification = '2011-03-14'
     * $query->filterByDateModification(array('max' => 'yesterday')); // WHERE date_modification < '2011-03-13'
     * </code>
     *
     * @param     mixed $dateModification The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByDateModification($dateModification = null, $comparison = null)
    {
        if (is_array($dateModification)) {
            $useMinMax = false;
            if (isset($dateModification['min'])) {
                $this->addUsingAlias(AuthyPeer::DATE_MODIFICATION, $dateModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateModification['max'])) {
                $this->addUsingAlias(AuthyPeer::DATE_MODIFICATION, $dateModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyPeer::DATE_MODIFICATION, $dateModification, $comparison);
    }

    /**
     * Filter the query on the id_group_creation column
     *
     * Example usage:
     * <code>
     * $query->filterByIdGroupCreation(1234); // WHERE id_group_creation = 1234
     * $query->filterByIdGroupCreation(array(12, 34)); // WHERE id_group_creation IN (12, 34)
     * $query->filterByIdGroupCreation(array('min' => 12)); // WHERE id_group_creation >= 12
     * $query->filterByIdGroupCreation(array('max' => 12)); // WHERE id_group_creation <= 12
     * </code>
     *
     * @see       filterByAuthyGroupRelatedByIdGroupCreation()
     *
     * @param     mixed $idGroupCreation The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByIdGroupCreation($idGroupCreation = null, $comparison = null)
    {
        if (is_array($idGroupCreation)) {
            $useMinMax = false;
            if (isset($idGroupCreation['min'])) {
                $this->addUsingAlias(AuthyPeer::ID_GROUP_CREATION, $idGroupCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGroupCreation['max'])) {
                $this->addUsingAlias(AuthyPeer::ID_GROUP_CREATION, $idGroupCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyPeer::ID_GROUP_CREATION, $idGroupCreation, $comparison);
    }

    /**
     * Filter the query on the id_creation column
     *
     * Example usage:
     * <code>
     * $query->filterByIdCreation(1234); // WHERE id_creation = 1234
     * $query->filterByIdCreation(array(12, 34)); // WHERE id_creation IN (12, 34)
     * $query->filterByIdCreation(array('min' => 12)); // WHERE id_creation >= 12
     * $query->filterByIdCreation(array('max' => 12)); // WHERE id_creation <= 12
     * </code>
     *
     * @see       filterByAuthyRelatedByIdCreation()
     *
     * @param     mixed $idCreation The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByIdCreation($idCreation = null, $comparison = null)
    {
        if (is_array($idCreation)) {
            $useMinMax = false;
            if (isset($idCreation['min'])) {
                $this->addUsingAlias(AuthyPeer::ID_CREATION, $idCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idCreation['max'])) {
                $this->addUsingAlias(AuthyPeer::ID_CREATION, $idCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyPeer::ID_CREATION, $idCreation, $comparison);
    }

    /**
     * Filter the query on the id_modification column
     *
     * Example usage:
     * <code>
     * $query->filterByIdModification(1234); // WHERE id_modification = 1234
     * $query->filterByIdModification(array(12, 34)); // WHERE id_modification IN (12, 34)
     * $query->filterByIdModification(array('min' => 12)); // WHERE id_modification >= 12
     * $query->filterByIdModification(array('max' => 12)); // WHERE id_modification <= 12
     * </code>
     *
     * @see       filterByAuthyRelatedByIdModification()
     *
     * @param     mixed $idModification The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function filterByIdModification($idModification = null, $comparison = null)
    {
        if (is_array($idModification)) {
            $useMinMax = false;
            if (isset($idModification['min'])) {
                $this->addUsingAlias(AuthyPeer::ID_MODIFICATION, $idModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idModification['max'])) {
                $this->addUsingAlias(AuthyPeer::ID_MODIFICATION, $idModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(AuthyPeer::ID_MODIFICATION, $idModification, $comparison);
    }

    /**
     * Filter the query by a related AuthyGroup object
     *
     * @param   AuthyGroup|PropelObjectCollection $authyGroup The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroupRelatedByIdAuthyGroup($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY_GROUP, $authyGroup->getIdAuthyGroup(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY_GROUP, $authyGroup->toKeyValue('PrimaryKey', 'IdAuthyGroup'), $comparison);
        } else {
            throw new PropelException('filterByAuthyGroupRelatedByIdAuthyGroup() only accepts arguments of type AuthyGroup or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyGroupRelatedByIdAuthyGroup relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinAuthyGroupRelatedByIdAuthyGroup($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyGroupRelatedByIdAuthyGroup');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AuthyGroupRelatedByIdAuthyGroup');
        }

        return $this;
    }

    /**
     * Use the AuthyGroupRelatedByIdAuthyGroup relation AuthyGroup object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyGroupQuery A secondary query class using the current class as primary query
     */
    public function useAuthyGroupRelatedByIdAuthyGroupQuery($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        return $this
            ->joinAuthyGroupRelatedByIdAuthyGroup($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyGroupRelatedByIdAuthyGroup', '\App\AuthyGroupQuery');
    }

    /**
     * Filter the query by a related AuthyGroup object
     *
     * @param   AuthyGroup|PropelObjectCollection $authyGroup The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroupRelatedByIdGroupCreation($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_GROUP_CREATION, $authyGroup->getIdAuthyGroup(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(AuthyPeer::ID_GROUP_CREATION, $authyGroup->toKeyValue('PrimaryKey', 'IdAuthyGroup'), $comparison);
        } else {
            throw new PropelException('filterByAuthyGroupRelatedByIdGroupCreation() only accepts arguments of type AuthyGroup or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyGroupRelatedByIdGroupCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinAuthyGroupRelatedByIdGroupCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyGroupRelatedByIdGroupCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AuthyGroupRelatedByIdGroupCreation');
        }

        return $this;
    }

    /**
     * Use the AuthyGroupRelatedByIdGroupCreation relation AuthyGroup object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyGroupQuery A secondary query class using the current class as primary query
     */
    public function useAuthyGroupRelatedByIdGroupCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAuthyGroupRelatedByIdGroupCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyGroupRelatedByIdGroupCreation', '\App\AuthyGroupQuery');
    }

    /**
     * Filter the query by a related Authy object
     *
     * @param   Authy|PropelObjectCollection $authy The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdCreation($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_CREATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(AuthyPeer::ID_CREATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
        } else {
            throw new PropelException('filterByAuthyRelatedByIdCreation() only accepts arguments of type Authy or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinAuthyRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AuthyRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the AuthyRelatedByIdCreation relation Authy object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyQuery A secondary query class using the current class as primary query
     */
    public function useAuthyRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAuthyRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyRelatedByIdCreation', '\App\AuthyQuery');
    }

    /**
     * Filter the query by a related Authy object
     *
     * @param   Authy|PropelObjectCollection $authy The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdModification($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_MODIFICATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(AuthyPeer::ID_MODIFICATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
        } else {
            throw new PropelException('filterByAuthyRelatedByIdModification() only accepts arguments of type Authy or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinAuthyRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AuthyRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the AuthyRelatedByIdModification relation Authy object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyQuery A secondary query class using the current class as primary query
     */
    public function useAuthyRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAuthyRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyRelatedByIdModification', '\App\AuthyQuery');
    }

    /**
     * Filter the query by a related PushDevice object
     *
     * @param   PushDevice|PropelObjectCollection $pushDevice  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByPushDeviceRelatedByIdAuthy($pushDevice, $comparison = null)
    {
        if ($pushDevice instanceof PushDevice) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $pushDevice->getIdAuthy(), $comparison);
        } elseif ($pushDevice instanceof PropelObjectCollection) {
            return $this
                ->usePushDeviceRelatedByIdAuthyQuery()
                ->filterByPrimaryKeys($pushDevice->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByPushDeviceRelatedByIdAuthy() only accepts arguments of type PushDevice or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the PushDeviceRelatedByIdAuthy relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinPushDeviceRelatedByIdAuthy($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('PushDeviceRelatedByIdAuthy');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'PushDeviceRelatedByIdAuthy');
        }

        return $this;
    }

    /**
     * Use the PushDeviceRelatedByIdAuthy relation PushDevice object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\PushDeviceQuery A secondary query class using the current class as primary query
     */
    public function usePushDeviceRelatedByIdAuthyQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinPushDeviceRelatedByIdAuthy($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'PushDeviceRelatedByIdAuthy', '\App\PushDeviceQuery');
    }

    /**
     * Filter the query by a related AuthyGroupX object
     *
     * @param   AuthyGroupX|PropelObjectCollection $authyGroupX  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroupXRelatedByIdAuthy($authyGroupX, $comparison = null)
    {
        if ($authyGroupX instanceof AuthyGroupX) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $authyGroupX->getIdAuthy(), $comparison);
        } elseif ($authyGroupX instanceof PropelObjectCollection) {
            return $this
                ->useAuthyGroupXRelatedByIdAuthyQuery()
                ->filterByPrimaryKeys($authyGroupX->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByAuthyGroupXRelatedByIdAuthy() only accepts arguments of type AuthyGroupX or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyGroupXRelatedByIdAuthy relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinAuthyGroupXRelatedByIdAuthy($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyGroupXRelatedByIdAuthy');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AuthyGroupXRelatedByIdAuthy');
        }

        return $this;
    }

    /**
     * Use the AuthyGroupXRelatedByIdAuthy relation AuthyGroupX object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyGroupXQuery A secondary query class using the current class as primary query
     */
    public function useAuthyGroupXRelatedByIdAuthyQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinAuthyGroupXRelatedByIdAuthy($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyGroupXRelatedByIdAuthy', '\App\AuthyGroupXQuery');
    }

    /**
     * Filter the query by a related AuthyLog object
     *
     * @param   AuthyLog|PropelObjectCollection $authyLog  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyLog($authyLog, $comparison = null)
    {
        if ($authyLog instanceof AuthyLog) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $authyLog->getIdAuthy(), $comparison);
        } elseif ($authyLog instanceof PropelObjectCollection) {
            return $this
                ->useAuthyLogQuery()
                ->filterByPrimaryKeys($authyLog->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByAuthyLog() only accepts arguments of type AuthyLog or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyLog relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinAuthyLog($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyLog');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AuthyLog');
        }

        return $this;
    }

    /**
     * Use the AuthyLog relation AuthyLog object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyLogQuery A secondary query class using the current class as primary query
     */
    public function useAuthyLogQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAuthyLog($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyLog', '\App\AuthyLogQuery');
    }

    /**
     * Filter the query by a related Authy object
     *
     * @param   Authy|PropelObjectCollection $authy  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdAuthy0($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $authy->getIdCreation(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            return $this
                ->useAuthyRelatedByIdAuthy0Query()
                ->filterByPrimaryKeys($authy->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByAuthyRelatedByIdAuthy0() only accepts arguments of type Authy or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyRelatedByIdAuthy0 relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinAuthyRelatedByIdAuthy0($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyRelatedByIdAuthy0');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AuthyRelatedByIdAuthy0');
        }

        return $this;
    }

    /**
     * Use the AuthyRelatedByIdAuthy0 relation Authy object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyQuery A secondary query class using the current class as primary query
     */
    public function useAuthyRelatedByIdAuthy0Query($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAuthyRelatedByIdAuthy0($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyRelatedByIdAuthy0', '\App\AuthyQuery');
    }

    /**
     * Filter the query by a related Authy object
     *
     * @param   Authy|PropelObjectCollection $authy  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdAuthy1($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $authy->getIdModification(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            return $this
                ->useAuthyRelatedByIdAuthy1Query()
                ->filterByPrimaryKeys($authy->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByAuthyRelatedByIdAuthy1() only accepts arguments of type Authy or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyRelatedByIdAuthy1 relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinAuthyRelatedByIdAuthy1($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyRelatedByIdAuthy1');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AuthyRelatedByIdAuthy1');
        }

        return $this;
    }

    /**
     * Use the AuthyRelatedByIdAuthy1 relation Authy object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyQuery A secondary query class using the current class as primary query
     */
    public function useAuthyRelatedByIdAuthy1Query($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAuthyRelatedByIdAuthy1($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyRelatedByIdAuthy1', '\App\AuthyQuery');
    }

    /**
     * Filter the query by a related PushDevice object
     *
     * @param   PushDevice|PropelObjectCollection $pushDevice  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByPushDeviceRelatedByIdCreation($pushDevice, $comparison = null)
    {
        if ($pushDevice instanceof PushDevice) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $pushDevice->getIdCreation(), $comparison);
        } elseif ($pushDevice instanceof PropelObjectCollection) {
            return $this
                ->usePushDeviceRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($pushDevice->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByPushDeviceRelatedByIdCreation() only accepts arguments of type PushDevice or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the PushDeviceRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinPushDeviceRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('PushDeviceRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'PushDeviceRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the PushDeviceRelatedByIdCreation relation PushDevice object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\PushDeviceQuery A secondary query class using the current class as primary query
     */
    public function usePushDeviceRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinPushDeviceRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'PushDeviceRelatedByIdCreation', '\App\PushDeviceQuery');
    }

    /**
     * Filter the query by a related PushDevice object
     *
     * @param   PushDevice|PropelObjectCollection $pushDevice  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByPushDeviceRelatedByIdModification($pushDevice, $comparison = null)
    {
        if ($pushDevice instanceof PushDevice) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $pushDevice->getIdModification(), $comparison);
        } elseif ($pushDevice instanceof PropelObjectCollection) {
            return $this
                ->usePushDeviceRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($pushDevice->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByPushDeviceRelatedByIdModification() only accepts arguments of type PushDevice or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the PushDeviceRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinPushDeviceRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('PushDeviceRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'PushDeviceRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the PushDeviceRelatedByIdModification relation PushDevice object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\PushDeviceQuery A secondary query class using the current class as primary query
     */
    public function usePushDeviceRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinPushDeviceRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'PushDeviceRelatedByIdModification', '\App\PushDeviceQuery');
    }

    /**
     * Filter the query by a related GridRun object
     *
     * @param   GridRun|PropelObjectCollection $gridRun  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByGridRunRelatedByIdCreation($gridRun, $comparison = null)
    {
        if ($gridRun instanceof GridRun) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $gridRun->getIdCreation(), $comparison);
        } elseif ($gridRun instanceof PropelObjectCollection) {
            return $this
                ->useGridRunRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($gridRun->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByGridRunRelatedByIdCreation() only accepts arguments of type GridRun or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the GridRunRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinGridRunRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('GridRunRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'GridRunRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the GridRunRelatedByIdCreation relation GridRun object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\GridRunQuery A secondary query class using the current class as primary query
     */
    public function useGridRunRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinGridRunRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'GridRunRelatedByIdCreation', '\App\GridRunQuery');
    }

    /**
     * Filter the query by a related GridRun object
     *
     * @param   GridRun|PropelObjectCollection $gridRun  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByGridRunRelatedByIdModification($gridRun, $comparison = null)
    {
        if ($gridRun instanceof GridRun) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $gridRun->getIdModification(), $comparison);
        } elseif ($gridRun instanceof PropelObjectCollection) {
            return $this
                ->useGridRunRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($gridRun->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByGridRunRelatedByIdModification() only accepts arguments of type GridRun or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the GridRunRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinGridRunRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('GridRunRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'GridRunRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the GridRunRelatedByIdModification relation GridRun object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\GridRunQuery A secondary query class using the current class as primary query
     */
    public function useGridRunRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinGridRunRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'GridRunRelatedByIdModification', '\App\GridRunQuery');
    }

    /**
     * Filter the query by a related FleetSlot object
     *
     * @param   FleetSlot|PropelObjectCollection $fleetSlot  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByFleetSlotRelatedByIdCreation($fleetSlot, $comparison = null)
    {
        if ($fleetSlot instanceof FleetSlot) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $fleetSlot->getIdCreation(), $comparison);
        } elseif ($fleetSlot instanceof PropelObjectCollection) {
            return $this
                ->useFleetSlotRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($fleetSlot->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByFleetSlotRelatedByIdCreation() only accepts arguments of type FleetSlot or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the FleetSlotRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinFleetSlotRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('FleetSlotRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'FleetSlotRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the FleetSlotRelatedByIdCreation relation FleetSlot object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\FleetSlotQuery A secondary query class using the current class as primary query
     */
    public function useFleetSlotRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinFleetSlotRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'FleetSlotRelatedByIdCreation', '\App\FleetSlotQuery');
    }

    /**
     * Filter the query by a related FleetSlot object
     *
     * @param   FleetSlot|PropelObjectCollection $fleetSlot  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByFleetSlotRelatedByIdModification($fleetSlot, $comparison = null)
    {
        if ($fleetSlot instanceof FleetSlot) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $fleetSlot->getIdModification(), $comparison);
        } elseif ($fleetSlot instanceof PropelObjectCollection) {
            return $this
                ->useFleetSlotRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($fleetSlot->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByFleetSlotRelatedByIdModification() only accepts arguments of type FleetSlot or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the FleetSlotRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinFleetSlotRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('FleetSlotRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'FleetSlotRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the FleetSlotRelatedByIdModification relation FleetSlot object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\FleetSlotQuery A secondary query class using the current class as primary query
     */
    public function useFleetSlotRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinFleetSlotRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'FleetSlotRelatedByIdModification', '\App\FleetSlotQuery');
    }

    /**
     * Filter the query by a related RegimeEpisode object
     *
     * @param   RegimeEpisode|PropelObjectCollection $regimeEpisode  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByRegimeEpisodeRelatedByIdCreation($regimeEpisode, $comparison = null)
    {
        if ($regimeEpisode instanceof RegimeEpisode) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $regimeEpisode->getIdCreation(), $comparison);
        } elseif ($regimeEpisode instanceof PropelObjectCollection) {
            return $this
                ->useRegimeEpisodeRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($regimeEpisode->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByRegimeEpisodeRelatedByIdCreation() only accepts arguments of type RegimeEpisode or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the RegimeEpisodeRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinRegimeEpisodeRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('RegimeEpisodeRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'RegimeEpisodeRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the RegimeEpisodeRelatedByIdCreation relation RegimeEpisode object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\RegimeEpisodeQuery A secondary query class using the current class as primary query
     */
    public function useRegimeEpisodeRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinRegimeEpisodeRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'RegimeEpisodeRelatedByIdCreation', '\App\RegimeEpisodeQuery');
    }

    /**
     * Filter the query by a related RegimeEpisode object
     *
     * @param   RegimeEpisode|PropelObjectCollection $regimeEpisode  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByRegimeEpisodeRelatedByIdModification($regimeEpisode, $comparison = null)
    {
        if ($regimeEpisode instanceof RegimeEpisode) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $regimeEpisode->getIdModification(), $comparison);
        } elseif ($regimeEpisode instanceof PropelObjectCollection) {
            return $this
                ->useRegimeEpisodeRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($regimeEpisode->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByRegimeEpisodeRelatedByIdModification() only accepts arguments of type RegimeEpisode or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the RegimeEpisodeRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinRegimeEpisodeRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('RegimeEpisodeRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'RegimeEpisodeRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the RegimeEpisodeRelatedByIdModification relation RegimeEpisode object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\RegimeEpisodeQuery A secondary query class using the current class as primary query
     */
    public function useRegimeEpisodeRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinRegimeEpisodeRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'RegimeEpisodeRelatedByIdModification', '\App\RegimeEpisodeQuery');
    }

    /**
     * Filter the query by a related BotOrder object
     *
     * @param   BotOrder|PropelObjectCollection $botOrder  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByBotOrderRelatedByIdCreation($botOrder, $comparison = null)
    {
        if ($botOrder instanceof BotOrder) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $botOrder->getIdCreation(), $comparison);
        } elseif ($botOrder instanceof PropelObjectCollection) {
            return $this
                ->useBotOrderRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($botOrder->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByBotOrderRelatedByIdCreation() only accepts arguments of type BotOrder or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BotOrderRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinBotOrderRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BotOrderRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'BotOrderRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the BotOrderRelatedByIdCreation relation BotOrder object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\BotOrderQuery A secondary query class using the current class as primary query
     */
    public function useBotOrderRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinBotOrderRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BotOrderRelatedByIdCreation', '\App\BotOrderQuery');
    }

    /**
     * Filter the query by a related BotOrder object
     *
     * @param   BotOrder|PropelObjectCollection $botOrder  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByBotOrderRelatedByIdModification($botOrder, $comparison = null)
    {
        if ($botOrder instanceof BotOrder) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $botOrder->getIdModification(), $comparison);
        } elseif ($botOrder instanceof PropelObjectCollection) {
            return $this
                ->useBotOrderRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($botOrder->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByBotOrderRelatedByIdModification() only accepts arguments of type BotOrder or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BotOrderRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinBotOrderRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BotOrderRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'BotOrderRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the BotOrderRelatedByIdModification relation BotOrder object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\BotOrderQuery A secondary query class using the current class as primary query
     */
    public function useBotOrderRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinBotOrderRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BotOrderRelatedByIdModification', '\App\BotOrderQuery');
    }

    /**
     * Filter the query by a related TradeCycle object
     *
     * @param   TradeCycle|PropelObjectCollection $tradeCycle  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByTradeCycleRelatedByIdCreation($tradeCycle, $comparison = null)
    {
        if ($tradeCycle instanceof TradeCycle) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $tradeCycle->getIdCreation(), $comparison);
        } elseif ($tradeCycle instanceof PropelObjectCollection) {
            return $this
                ->useTradeCycleRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($tradeCycle->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByTradeCycleRelatedByIdCreation() only accepts arguments of type TradeCycle or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the TradeCycleRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinTradeCycleRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('TradeCycleRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'TradeCycleRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the TradeCycleRelatedByIdCreation relation TradeCycle object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\TradeCycleQuery A secondary query class using the current class as primary query
     */
    public function useTradeCycleRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinTradeCycleRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'TradeCycleRelatedByIdCreation', '\App\TradeCycleQuery');
    }

    /**
     * Filter the query by a related TradeCycle object
     *
     * @param   TradeCycle|PropelObjectCollection $tradeCycle  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByTradeCycleRelatedByIdModification($tradeCycle, $comparison = null)
    {
        if ($tradeCycle instanceof TradeCycle) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $tradeCycle->getIdModification(), $comparison);
        } elseif ($tradeCycle instanceof PropelObjectCollection) {
            return $this
                ->useTradeCycleRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($tradeCycle->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByTradeCycleRelatedByIdModification() only accepts arguments of type TradeCycle or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the TradeCycleRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinTradeCycleRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('TradeCycleRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'TradeCycleRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the TradeCycleRelatedByIdModification relation TradeCycle object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\TradeCycleQuery A secondary query class using the current class as primary query
     */
    public function useTradeCycleRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinTradeCycleRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'TradeCycleRelatedByIdModification', '\App\TradeCycleQuery');
    }

    /**
     * Filter the query by a related BotEvent object
     *
     * @param   BotEvent|PropelObjectCollection $botEvent  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByBotEventRelatedByIdCreation($botEvent, $comparison = null)
    {
        if ($botEvent instanceof BotEvent) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $botEvent->getIdCreation(), $comparison);
        } elseif ($botEvent instanceof PropelObjectCollection) {
            return $this
                ->useBotEventRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($botEvent->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByBotEventRelatedByIdCreation() only accepts arguments of type BotEvent or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BotEventRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinBotEventRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BotEventRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'BotEventRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the BotEventRelatedByIdCreation relation BotEvent object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\BotEventQuery A secondary query class using the current class as primary query
     */
    public function useBotEventRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinBotEventRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BotEventRelatedByIdCreation', '\App\BotEventQuery');
    }

    /**
     * Filter the query by a related BotEvent object
     *
     * @param   BotEvent|PropelObjectCollection $botEvent  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByBotEventRelatedByIdModification($botEvent, $comparison = null)
    {
        if ($botEvent instanceof BotEvent) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $botEvent->getIdModification(), $comparison);
        } elseif ($botEvent instanceof PropelObjectCollection) {
            return $this
                ->useBotEventRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($botEvent->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByBotEventRelatedByIdModification() only accepts arguments of type BotEvent or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BotEventRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinBotEventRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BotEventRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'BotEventRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the BotEventRelatedByIdModification relation BotEvent object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\BotEventQuery A secondary query class using the current class as primary query
     */
    public function useBotEventRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinBotEventRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BotEventRelatedByIdModification', '\App\BotEventQuery');
    }

    /**
     * Filter the query by a related BotCommand object
     *
     * @param   BotCommand|PropelObjectCollection $botCommand  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByBotCommandRelatedByIdCreation($botCommand, $comparison = null)
    {
        if ($botCommand instanceof BotCommand) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $botCommand->getIdCreation(), $comparison);
        } elseif ($botCommand instanceof PropelObjectCollection) {
            return $this
                ->useBotCommandRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($botCommand->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByBotCommandRelatedByIdCreation() only accepts arguments of type BotCommand or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BotCommandRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinBotCommandRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BotCommandRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'BotCommandRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the BotCommandRelatedByIdCreation relation BotCommand object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\BotCommandQuery A secondary query class using the current class as primary query
     */
    public function useBotCommandRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinBotCommandRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BotCommandRelatedByIdCreation', '\App\BotCommandQuery');
    }

    /**
     * Filter the query by a related BotCommand object
     *
     * @param   BotCommand|PropelObjectCollection $botCommand  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByBotCommandRelatedByIdModification($botCommand, $comparison = null)
    {
        if ($botCommand instanceof BotCommand) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $botCommand->getIdModification(), $comparison);
        } elseif ($botCommand instanceof PropelObjectCollection) {
            return $this
                ->useBotCommandRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($botCommand->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByBotCommandRelatedByIdModification() only accepts arguments of type BotCommand or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BotCommandRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinBotCommandRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BotCommandRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'BotCommandRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the BotCommandRelatedByIdModification relation BotCommand object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\BotCommandQuery A secondary query class using the current class as primary query
     */
    public function useBotCommandRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinBotCommandRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BotCommandRelatedByIdModification', '\App\BotCommandQuery');
    }

    /**
     * Filter the query by a related SimWallet object
     *
     * @param   SimWallet|PropelObjectCollection $simWallet  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterBySimWalletRelatedByIdCreation($simWallet, $comparison = null)
    {
        if ($simWallet instanceof SimWallet) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $simWallet->getIdCreation(), $comparison);
        } elseif ($simWallet instanceof PropelObjectCollection) {
            return $this
                ->useSimWalletRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($simWallet->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterBySimWalletRelatedByIdCreation() only accepts arguments of type SimWallet or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the SimWalletRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinSimWalletRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('SimWalletRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'SimWalletRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the SimWalletRelatedByIdCreation relation SimWallet object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\SimWalletQuery A secondary query class using the current class as primary query
     */
    public function useSimWalletRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinSimWalletRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'SimWalletRelatedByIdCreation', '\App\SimWalletQuery');
    }

    /**
     * Filter the query by a related SimWallet object
     *
     * @param   SimWallet|PropelObjectCollection $simWallet  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterBySimWalletRelatedByIdModification($simWallet, $comparison = null)
    {
        if ($simWallet instanceof SimWallet) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $simWallet->getIdModification(), $comparison);
        } elseif ($simWallet instanceof PropelObjectCollection) {
            return $this
                ->useSimWalletRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($simWallet->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterBySimWalletRelatedByIdModification() only accepts arguments of type SimWallet or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the SimWalletRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinSimWalletRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('SimWalletRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'SimWalletRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the SimWalletRelatedByIdModification relation SimWallet object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\SimWalletQuery A secondary query class using the current class as primary query
     */
    public function useSimWalletRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinSimWalletRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'SimWalletRelatedByIdModification', '\App\SimWalletQuery');
    }

    /**
     * Filter the query by a related MarketSummary object
     *
     * @param   MarketSummary|PropelObjectCollection $marketSummary  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByMarketSummaryRelatedByIdCreation($marketSummary, $comparison = null)
    {
        if ($marketSummary instanceof MarketSummary) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $marketSummary->getIdCreation(), $comparison);
        } elseif ($marketSummary instanceof PropelObjectCollection) {
            return $this
                ->useMarketSummaryRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($marketSummary->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByMarketSummaryRelatedByIdCreation() only accepts arguments of type MarketSummary or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the MarketSummaryRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinMarketSummaryRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('MarketSummaryRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'MarketSummaryRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the MarketSummaryRelatedByIdCreation relation MarketSummary object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\MarketSummaryQuery A secondary query class using the current class as primary query
     */
    public function useMarketSummaryRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinMarketSummaryRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'MarketSummaryRelatedByIdCreation', '\App\MarketSummaryQuery');
    }

    /**
     * Filter the query by a related MarketSummary object
     *
     * @param   MarketSummary|PropelObjectCollection $marketSummary  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByMarketSummaryRelatedByIdModification($marketSummary, $comparison = null)
    {
        if ($marketSummary instanceof MarketSummary) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $marketSummary->getIdModification(), $comparison);
        } elseif ($marketSummary instanceof PropelObjectCollection) {
            return $this
                ->useMarketSummaryRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($marketSummary->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByMarketSummaryRelatedByIdModification() only accepts arguments of type MarketSummary or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the MarketSummaryRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinMarketSummaryRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('MarketSummaryRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'MarketSummaryRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the MarketSummaryRelatedByIdModification relation MarketSummary object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\MarketSummaryQuery A secondary query class using the current class as primary query
     */
    public function useMarketSummaryRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinMarketSummaryRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'MarketSummaryRelatedByIdModification', '\App\MarketSummaryQuery');
    }

    /**
     * Filter the query by a related MarketRegime object
     *
     * @param   MarketRegime|PropelObjectCollection $marketRegime  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByMarketRegimeRelatedByIdCreation($marketRegime, $comparison = null)
    {
        if ($marketRegime instanceof MarketRegime) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $marketRegime->getIdCreation(), $comparison);
        } elseif ($marketRegime instanceof PropelObjectCollection) {
            return $this
                ->useMarketRegimeRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($marketRegime->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByMarketRegimeRelatedByIdCreation() only accepts arguments of type MarketRegime or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the MarketRegimeRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinMarketRegimeRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('MarketRegimeRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'MarketRegimeRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the MarketRegimeRelatedByIdCreation relation MarketRegime object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\MarketRegimeQuery A secondary query class using the current class as primary query
     */
    public function useMarketRegimeRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinMarketRegimeRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'MarketRegimeRelatedByIdCreation', '\App\MarketRegimeQuery');
    }

    /**
     * Filter the query by a related MarketRegime object
     *
     * @param   MarketRegime|PropelObjectCollection $marketRegime  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByMarketRegimeRelatedByIdModification($marketRegime, $comparison = null)
    {
        if ($marketRegime instanceof MarketRegime) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $marketRegime->getIdModification(), $comparison);
        } elseif ($marketRegime instanceof PropelObjectCollection) {
            return $this
                ->useMarketRegimeRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($marketRegime->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByMarketRegimeRelatedByIdModification() only accepts arguments of type MarketRegime or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the MarketRegimeRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinMarketRegimeRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('MarketRegimeRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'MarketRegimeRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the MarketRegimeRelatedByIdModification relation MarketRegime object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\MarketRegimeQuery A secondary query class using the current class as primary query
     */
    public function useMarketRegimeRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinMarketRegimeRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'MarketRegimeRelatedByIdModification', '\App\MarketRegimeQuery');
    }

    /**
     * Filter the query by a related MarketCandle object
     *
     * @param   MarketCandle|PropelObjectCollection $marketCandle  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByMarketCandleRelatedByIdCreation($marketCandle, $comparison = null)
    {
        if ($marketCandle instanceof MarketCandle) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $marketCandle->getIdCreation(), $comparison);
        } elseif ($marketCandle instanceof PropelObjectCollection) {
            return $this
                ->useMarketCandleRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($marketCandle->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByMarketCandleRelatedByIdCreation() only accepts arguments of type MarketCandle or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the MarketCandleRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinMarketCandleRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('MarketCandleRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'MarketCandleRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the MarketCandleRelatedByIdCreation relation MarketCandle object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\MarketCandleQuery A secondary query class using the current class as primary query
     */
    public function useMarketCandleRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinMarketCandleRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'MarketCandleRelatedByIdCreation', '\App\MarketCandleQuery');
    }

    /**
     * Filter the query by a related MarketCandle object
     *
     * @param   MarketCandle|PropelObjectCollection $marketCandle  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByMarketCandleRelatedByIdModification($marketCandle, $comparison = null)
    {
        if ($marketCandle instanceof MarketCandle) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $marketCandle->getIdModification(), $comparison);
        } elseif ($marketCandle instanceof PropelObjectCollection) {
            return $this
                ->useMarketCandleRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($marketCandle->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByMarketCandleRelatedByIdModification() only accepts arguments of type MarketCandle or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the MarketCandleRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinMarketCandleRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('MarketCandleRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'MarketCandleRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the MarketCandleRelatedByIdModification relation MarketCandle object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\MarketCandleQuery A secondary query class using the current class as primary query
     */
    public function useMarketCandleRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinMarketCandleRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'MarketCandleRelatedByIdModification', '\App\MarketCandleQuery');
    }

    /**
     * Filter the query by a related BotDecision object
     *
     * @param   BotDecision|PropelObjectCollection $botDecision  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByBotDecisionRelatedByIdCreation($botDecision, $comparison = null)
    {
        if ($botDecision instanceof BotDecision) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $botDecision->getIdCreation(), $comparison);
        } elseif ($botDecision instanceof PropelObjectCollection) {
            return $this
                ->useBotDecisionRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($botDecision->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByBotDecisionRelatedByIdCreation() only accepts arguments of type BotDecision or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BotDecisionRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinBotDecisionRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BotDecisionRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'BotDecisionRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the BotDecisionRelatedByIdCreation relation BotDecision object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\BotDecisionQuery A secondary query class using the current class as primary query
     */
    public function useBotDecisionRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinBotDecisionRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BotDecisionRelatedByIdCreation', '\App\BotDecisionQuery');
    }

    /**
     * Filter the query by a related BotDecision object
     *
     * @param   BotDecision|PropelObjectCollection $botDecision  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByBotDecisionRelatedByIdModification($botDecision, $comparison = null)
    {
        if ($botDecision instanceof BotDecision) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $botDecision->getIdModification(), $comparison);
        } elseif ($botDecision instanceof PropelObjectCollection) {
            return $this
                ->useBotDecisionRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($botDecision->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByBotDecisionRelatedByIdModification() only accepts arguments of type BotDecision or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BotDecisionRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinBotDecisionRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BotDecisionRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'BotDecisionRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the BotDecisionRelatedByIdModification relation BotDecision object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\BotDecisionQuery A secondary query class using the current class as primary query
     */
    public function useBotDecisionRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinBotDecisionRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BotDecisionRelatedByIdModification', '\App\BotDecisionQuery');
    }

    /**
     * Filter the query by a related MarketOutlook object
     *
     * @param   MarketOutlook|PropelObjectCollection $marketOutlook  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByMarketOutlookRelatedByIdCreation($marketOutlook, $comparison = null)
    {
        if ($marketOutlook instanceof MarketOutlook) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $marketOutlook->getIdCreation(), $comparison);
        } elseif ($marketOutlook instanceof PropelObjectCollection) {
            return $this
                ->useMarketOutlookRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($marketOutlook->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByMarketOutlookRelatedByIdCreation() only accepts arguments of type MarketOutlook or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the MarketOutlookRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinMarketOutlookRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('MarketOutlookRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'MarketOutlookRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the MarketOutlookRelatedByIdCreation relation MarketOutlook object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\MarketOutlookQuery A secondary query class using the current class as primary query
     */
    public function useMarketOutlookRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinMarketOutlookRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'MarketOutlookRelatedByIdCreation', '\App\MarketOutlookQuery');
    }

    /**
     * Filter the query by a related MarketOutlook object
     *
     * @param   MarketOutlook|PropelObjectCollection $marketOutlook  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByMarketOutlookRelatedByIdModification($marketOutlook, $comparison = null)
    {
        if ($marketOutlook instanceof MarketOutlook) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $marketOutlook->getIdModification(), $comparison);
        } elseif ($marketOutlook instanceof PropelObjectCollection) {
            return $this
                ->useMarketOutlookRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($marketOutlook->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByMarketOutlookRelatedByIdModification() only accepts arguments of type MarketOutlook or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the MarketOutlookRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinMarketOutlookRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('MarketOutlookRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'MarketOutlookRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the MarketOutlookRelatedByIdModification relation MarketOutlook object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\MarketOutlookQuery A secondary query class using the current class as primary query
     */
    public function useMarketOutlookRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinMarketOutlookRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'MarketOutlookRelatedByIdModification', '\App\MarketOutlookQuery');
    }

    /**
     * Filter the query by a related MarketOutlookState object
     *
     * @param   MarketOutlookState|PropelObjectCollection $marketOutlookState  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByMarketOutlookStateRelatedByIdCreation($marketOutlookState, $comparison = null)
    {
        if ($marketOutlookState instanceof MarketOutlookState) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $marketOutlookState->getIdCreation(), $comparison);
        } elseif ($marketOutlookState instanceof PropelObjectCollection) {
            return $this
                ->useMarketOutlookStateRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($marketOutlookState->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByMarketOutlookStateRelatedByIdCreation() only accepts arguments of type MarketOutlookState or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the MarketOutlookStateRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinMarketOutlookStateRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('MarketOutlookStateRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'MarketOutlookStateRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the MarketOutlookStateRelatedByIdCreation relation MarketOutlookState object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\MarketOutlookStateQuery A secondary query class using the current class as primary query
     */
    public function useMarketOutlookStateRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinMarketOutlookStateRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'MarketOutlookStateRelatedByIdCreation', '\App\MarketOutlookStateQuery');
    }

    /**
     * Filter the query by a related MarketOutlookState object
     *
     * @param   MarketOutlookState|PropelObjectCollection $marketOutlookState  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByMarketOutlookStateRelatedByIdModification($marketOutlookState, $comparison = null)
    {
        if ($marketOutlookState instanceof MarketOutlookState) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $marketOutlookState->getIdModification(), $comparison);
        } elseif ($marketOutlookState instanceof PropelObjectCollection) {
            return $this
                ->useMarketOutlookStateRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($marketOutlookState->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByMarketOutlookStateRelatedByIdModification() only accepts arguments of type MarketOutlookState or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the MarketOutlookStateRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinMarketOutlookStateRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('MarketOutlookStateRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'MarketOutlookStateRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the MarketOutlookStateRelatedByIdModification relation MarketOutlookState object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\MarketOutlookStateQuery A secondary query class using the current class as primary query
     */
    public function useMarketOutlookStateRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinMarketOutlookStateRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'MarketOutlookStateRelatedByIdModification', '\App\MarketOutlookStateQuery');
    }

    /**
     * Filter the query by a related WalletNav object
     *
     * @param   WalletNav|PropelObjectCollection $walletNav  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByWalletNavRelatedByIdCreation($walletNav, $comparison = null)
    {
        if ($walletNav instanceof WalletNav) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $walletNav->getIdCreation(), $comparison);
        } elseif ($walletNav instanceof PropelObjectCollection) {
            return $this
                ->useWalletNavRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($walletNav->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByWalletNavRelatedByIdCreation() only accepts arguments of type WalletNav or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the WalletNavRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinWalletNavRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('WalletNavRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'WalletNavRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the WalletNavRelatedByIdCreation relation WalletNav object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\WalletNavQuery A secondary query class using the current class as primary query
     */
    public function useWalletNavRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinWalletNavRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'WalletNavRelatedByIdCreation', '\App\WalletNavQuery');
    }

    /**
     * Filter the query by a related WalletNav object
     *
     * @param   WalletNav|PropelObjectCollection $walletNav  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByWalletNavRelatedByIdModification($walletNav, $comparison = null)
    {
        if ($walletNav instanceof WalletNav) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $walletNav->getIdModification(), $comparison);
        } elseif ($walletNav instanceof PropelObjectCollection) {
            return $this
                ->useWalletNavRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($walletNav->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByWalletNavRelatedByIdModification() only accepts arguments of type WalletNav or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the WalletNavRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinWalletNavRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('WalletNavRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'WalletNavRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the WalletNavRelatedByIdModification relation WalletNav object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\WalletNavQuery A secondary query class using the current class as primary query
     */
    public function useWalletNavRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinWalletNavRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'WalletNavRelatedByIdModification', '\App\WalletNavQuery');
    }

    /**
     * Filter the query by a related AuthyGroup object
     *
     * @param   AuthyGroup|PropelObjectCollection $authyGroup  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroupRelatedByIdCreation($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $authyGroup->getIdCreation(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            return $this
                ->useAuthyGroupRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($authyGroup->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByAuthyGroupRelatedByIdCreation() only accepts arguments of type AuthyGroup or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyGroupRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinAuthyGroupRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyGroupRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AuthyGroupRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the AuthyGroupRelatedByIdCreation relation AuthyGroup object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyGroupQuery A secondary query class using the current class as primary query
     */
    public function useAuthyGroupRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAuthyGroupRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyGroupRelatedByIdCreation', '\App\AuthyGroupQuery');
    }

    /**
     * Filter the query by a related AuthyGroup object
     *
     * @param   AuthyGroup|PropelObjectCollection $authyGroup  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroupRelatedByIdModification($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $authyGroup->getIdModification(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            return $this
                ->useAuthyGroupRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($authyGroup->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByAuthyGroupRelatedByIdModification() only accepts arguments of type AuthyGroup or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyGroupRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinAuthyGroupRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyGroupRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AuthyGroupRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the AuthyGroupRelatedByIdModification relation AuthyGroup object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyGroupQuery A secondary query class using the current class as primary query
     */
    public function useAuthyGroupRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAuthyGroupRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyGroupRelatedByIdModification', '\App\AuthyGroupQuery');
    }

    /**
     * Filter the query by a related AuthyGroupX object
     *
     * @param   AuthyGroupX|PropelObjectCollection $authyGroupX  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroupXRelatedByIdCreation($authyGroupX, $comparison = null)
    {
        if ($authyGroupX instanceof AuthyGroupX) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $authyGroupX->getIdCreation(), $comparison);
        } elseif ($authyGroupX instanceof PropelObjectCollection) {
            return $this
                ->useAuthyGroupXRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($authyGroupX->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByAuthyGroupXRelatedByIdCreation() only accepts arguments of type AuthyGroupX or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyGroupXRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinAuthyGroupXRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyGroupXRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AuthyGroupXRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the AuthyGroupXRelatedByIdCreation relation AuthyGroupX object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyGroupXQuery A secondary query class using the current class as primary query
     */
    public function useAuthyGroupXRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAuthyGroupXRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyGroupXRelatedByIdCreation', '\App\AuthyGroupXQuery');
    }

    /**
     * Filter the query by a related AuthyGroupX object
     *
     * @param   AuthyGroupX|PropelObjectCollection $authyGroupX  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroupXRelatedByIdModification($authyGroupX, $comparison = null)
    {
        if ($authyGroupX instanceof AuthyGroupX) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $authyGroupX->getIdModification(), $comparison);
        } elseif ($authyGroupX instanceof PropelObjectCollection) {
            return $this
                ->useAuthyGroupXRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($authyGroupX->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByAuthyGroupXRelatedByIdModification() only accepts arguments of type AuthyGroupX or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyGroupXRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinAuthyGroupXRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyGroupXRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AuthyGroupXRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the AuthyGroupXRelatedByIdModification relation AuthyGroupX object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyGroupXQuery A secondary query class using the current class as primary query
     */
    public function useAuthyGroupXRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAuthyGroupXRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyGroupXRelatedByIdModification', '\App\AuthyGroupXQuery');
    }

    /**
     * Filter the query by a related Config object
     *
     * @param   Config|PropelObjectCollection $config  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByConfigRelatedByIdCreation($config, $comparison = null)
    {
        if ($config instanceof Config) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $config->getIdCreation(), $comparison);
        } elseif ($config instanceof PropelObjectCollection) {
            return $this
                ->useConfigRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($config->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByConfigRelatedByIdCreation() only accepts arguments of type Config or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ConfigRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinConfigRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ConfigRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'ConfigRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the ConfigRelatedByIdCreation relation Config object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\ConfigQuery A secondary query class using the current class as primary query
     */
    public function useConfigRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinConfigRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ConfigRelatedByIdCreation', '\App\ConfigQuery');
    }

    /**
     * Filter the query by a related Config object
     *
     * @param   Config|PropelObjectCollection $config  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByConfigRelatedByIdModification($config, $comparison = null)
    {
        if ($config instanceof Config) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $config->getIdModification(), $comparison);
        } elseif ($config instanceof PropelObjectCollection) {
            return $this
                ->useConfigRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($config->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByConfigRelatedByIdModification() only accepts arguments of type Config or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ConfigRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinConfigRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ConfigRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'ConfigRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the ConfigRelatedByIdModification relation Config object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\ConfigQuery A secondary query class using the current class as primary query
     */
    public function useConfigRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinConfigRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ConfigRelatedByIdModification', '\App\ConfigQuery');
    }

    /**
     * Filter the query by a related ApiRbac object
     *
     * @param   ApiRbac|PropelObjectCollection $apiRbac  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByApiRbacRelatedByIdCreation($apiRbac, $comparison = null)
    {
        if ($apiRbac instanceof ApiRbac) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $apiRbac->getIdCreation(), $comparison);
        } elseif ($apiRbac instanceof PropelObjectCollection) {
            return $this
                ->useApiRbacRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($apiRbac->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByApiRbacRelatedByIdCreation() only accepts arguments of type ApiRbac or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ApiRbacRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinApiRbacRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ApiRbacRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'ApiRbacRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the ApiRbacRelatedByIdCreation relation ApiRbac object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\ApiRbacQuery A secondary query class using the current class as primary query
     */
    public function useApiRbacRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinApiRbacRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ApiRbacRelatedByIdCreation', '\App\ApiRbacQuery');
    }

    /**
     * Filter the query by a related ApiRbac object
     *
     * @param   ApiRbac|PropelObjectCollection $apiRbac  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByApiRbacRelatedByIdModification($apiRbac, $comparison = null)
    {
        if ($apiRbac instanceof ApiRbac) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $apiRbac->getIdModification(), $comparison);
        } elseif ($apiRbac instanceof PropelObjectCollection) {
            return $this
                ->useApiRbacRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($apiRbac->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByApiRbacRelatedByIdModification() only accepts arguments of type ApiRbac or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ApiRbacRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinApiRbacRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ApiRbacRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'ApiRbacRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the ApiRbacRelatedByIdModification relation ApiRbac object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\ApiRbacQuery A secondary query class using the current class as primary query
     */
    public function useApiRbacRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinApiRbacRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ApiRbacRelatedByIdModification', '\App\ApiRbacQuery');
    }

    /**
     * Filter the query by a related ApiLog object
     *
     * @param   ApiLog|PropelObjectCollection $apiLog  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByApiLog($apiLog, $comparison = null)
    {
        if ($apiLog instanceof ApiLog) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $apiLog->getIdAuthy(), $comparison);
        } elseif ($apiLog instanceof PropelObjectCollection) {
            return $this
                ->useApiLogQuery()
                ->filterByPrimaryKeys($apiLog->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByApiLog() only accepts arguments of type ApiLog or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the ApiLog relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinApiLog($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('ApiLog');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'ApiLog');
        }

        return $this;
    }

    /**
     * Use the ApiLog relation ApiLog object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\ApiLogQuery A secondary query class using the current class as primary query
     */
    public function useApiLogQuery($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        return $this
            ->joinApiLog($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'ApiLog', '\App\ApiLogQuery');
    }

    /**
     * Filter the query by a related Template object
     *
     * @param   Template|PropelObjectCollection $template  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByTemplateRelatedByIdCreation($template, $comparison = null)
    {
        if ($template instanceof Template) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $template->getIdCreation(), $comparison);
        } elseif ($template instanceof PropelObjectCollection) {
            return $this
                ->useTemplateRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($template->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByTemplateRelatedByIdCreation() only accepts arguments of type Template or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the TemplateRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinTemplateRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('TemplateRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'TemplateRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the TemplateRelatedByIdCreation relation Template object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\TemplateQuery A secondary query class using the current class as primary query
     */
    public function useTemplateRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinTemplateRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'TemplateRelatedByIdCreation', '\App\TemplateQuery');
    }

    /**
     * Filter the query by a related Template object
     *
     * @param   Template|PropelObjectCollection $template  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByTemplateRelatedByIdModification($template, $comparison = null)
    {
        if ($template instanceof Template) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $template->getIdModification(), $comparison);
        } elseif ($template instanceof PropelObjectCollection) {
            return $this
                ->useTemplateRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($template->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByTemplateRelatedByIdModification() only accepts arguments of type Template or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the TemplateRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinTemplateRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('TemplateRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'TemplateRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the TemplateRelatedByIdModification relation Template object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\TemplateQuery A secondary query class using the current class as primary query
     */
    public function useTemplateRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinTemplateRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'TemplateRelatedByIdModification', '\App\TemplateQuery');
    }

    /**
     * Filter the query by a related TemplateFile object
     *
     * @param   TemplateFile|PropelObjectCollection $templateFile  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByTemplateFileRelatedByIdCreation($templateFile, $comparison = null)
    {
        if ($templateFile instanceof TemplateFile) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $templateFile->getIdCreation(), $comparison);
        } elseif ($templateFile instanceof PropelObjectCollection) {
            return $this
                ->useTemplateFileRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($templateFile->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByTemplateFileRelatedByIdCreation() only accepts arguments of type TemplateFile or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the TemplateFileRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinTemplateFileRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('TemplateFileRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'TemplateFileRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the TemplateFileRelatedByIdCreation relation TemplateFile object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\TemplateFileQuery A secondary query class using the current class as primary query
     */
    public function useTemplateFileRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinTemplateFileRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'TemplateFileRelatedByIdCreation', '\App\TemplateFileQuery');
    }

    /**
     * Filter the query by a related TemplateFile object
     *
     * @param   TemplateFile|PropelObjectCollection $templateFile  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByTemplateFileRelatedByIdModification($templateFile, $comparison = null)
    {
        if ($templateFile instanceof TemplateFile) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $templateFile->getIdModification(), $comparison);
        } elseif ($templateFile instanceof PropelObjectCollection) {
            return $this
                ->useTemplateFileRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($templateFile->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByTemplateFileRelatedByIdModification() only accepts arguments of type TemplateFile or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the TemplateFileRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinTemplateFileRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('TemplateFileRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'TemplateFileRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the TemplateFileRelatedByIdModification relation TemplateFile object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\TemplateFileQuery A secondary query class using the current class as primary query
     */
    public function useTemplateFileRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinTemplateFileRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'TemplateFileRelatedByIdModification', '\App\TemplateFileQuery');
    }

    /**
     * Filter the query by a related AuthyRefreshToken object
     *
     * @param   AuthyRefreshToken|PropelObjectCollection $authyRefreshToken  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRefreshTokenRelatedByIdAuthy($authyRefreshToken, $comparison = null)
    {
        if ($authyRefreshToken instanceof AuthyRefreshToken) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $authyRefreshToken->getIdAuthy(), $comparison);
        } elseif ($authyRefreshToken instanceof PropelObjectCollection) {
            return $this
                ->useAuthyRefreshTokenRelatedByIdAuthyQuery()
                ->filterByPrimaryKeys($authyRefreshToken->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByAuthyRefreshTokenRelatedByIdAuthy() only accepts arguments of type AuthyRefreshToken or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyRefreshTokenRelatedByIdAuthy relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinAuthyRefreshTokenRelatedByIdAuthy($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyRefreshTokenRelatedByIdAuthy');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AuthyRefreshTokenRelatedByIdAuthy');
        }

        return $this;
    }

    /**
     * Use the AuthyRefreshTokenRelatedByIdAuthy relation AuthyRefreshToken object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyRefreshTokenQuery A secondary query class using the current class as primary query
     */
    public function useAuthyRefreshTokenRelatedByIdAuthyQuery($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        return $this
            ->joinAuthyRefreshTokenRelatedByIdAuthy($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyRefreshTokenRelatedByIdAuthy', '\App\AuthyRefreshTokenQuery');
    }

    /**
     * Filter the query by a related AuthyRefreshToken object
     *
     * @param   AuthyRefreshToken|PropelObjectCollection $authyRefreshToken  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRefreshTokenRelatedByIdCreation($authyRefreshToken, $comparison = null)
    {
        if ($authyRefreshToken instanceof AuthyRefreshToken) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $authyRefreshToken->getIdCreation(), $comparison);
        } elseif ($authyRefreshToken instanceof PropelObjectCollection) {
            return $this
                ->useAuthyRefreshTokenRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($authyRefreshToken->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByAuthyRefreshTokenRelatedByIdCreation() only accepts arguments of type AuthyRefreshToken or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyRefreshTokenRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinAuthyRefreshTokenRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyRefreshTokenRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AuthyRefreshTokenRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the AuthyRefreshTokenRelatedByIdCreation relation AuthyRefreshToken object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyRefreshTokenQuery A secondary query class using the current class as primary query
     */
    public function useAuthyRefreshTokenRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAuthyRefreshTokenRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyRefreshTokenRelatedByIdCreation', '\App\AuthyRefreshTokenQuery');
    }

    /**
     * Filter the query by a related AuthyRefreshToken object
     *
     * @param   AuthyRefreshToken|PropelObjectCollection $authyRefreshToken  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRefreshTokenRelatedByIdModification($authyRefreshToken, $comparison = null)
    {
        if ($authyRefreshToken instanceof AuthyRefreshToken) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $authyRefreshToken->getIdModification(), $comparison);
        } elseif ($authyRefreshToken instanceof PropelObjectCollection) {
            return $this
                ->useAuthyRefreshTokenRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($authyRefreshToken->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByAuthyRefreshTokenRelatedByIdModification() only accepts arguments of type AuthyRefreshToken or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyRefreshTokenRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinAuthyRefreshTokenRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyRefreshTokenRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'AuthyRefreshTokenRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the AuthyRefreshTokenRelatedByIdModification relation AuthyRefreshToken object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyRefreshTokenQuery A secondary query class using the current class as primary query
     */
    public function useAuthyRefreshTokenRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAuthyRefreshTokenRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyRefreshTokenRelatedByIdModification', '\App\AuthyRefreshTokenQuery');
    }

    /**
     * Filter the query by a related GridRunAudit object
     *
     * @param   GridRunAudit|PropelObjectCollection $gridRunAudit  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByGridRunAuditRelatedByIdCreation($gridRunAudit, $comparison = null)
    {
        if ($gridRunAudit instanceof GridRunAudit) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $gridRunAudit->getIdCreation(), $comparison);
        } elseif ($gridRunAudit instanceof PropelObjectCollection) {
            return $this
                ->useGridRunAuditRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($gridRunAudit->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByGridRunAuditRelatedByIdCreation() only accepts arguments of type GridRunAudit or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the GridRunAuditRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinGridRunAuditRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('GridRunAuditRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'GridRunAuditRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the GridRunAuditRelatedByIdCreation relation GridRunAudit object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\GridRunAuditQuery A secondary query class using the current class as primary query
     */
    public function useGridRunAuditRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinGridRunAuditRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'GridRunAuditRelatedByIdCreation', '\App\GridRunAuditQuery');
    }

    /**
     * Filter the query by a related GridRunAudit object
     *
     * @param   GridRunAudit|PropelObjectCollection $gridRunAudit  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByGridRunAuditRelatedByIdModification($gridRunAudit, $comparison = null)
    {
        if ($gridRunAudit instanceof GridRunAudit) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $gridRunAudit->getIdModification(), $comparison);
        } elseif ($gridRunAudit instanceof PropelObjectCollection) {
            return $this
                ->useGridRunAuditRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($gridRunAudit->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByGridRunAuditRelatedByIdModification() only accepts arguments of type GridRunAudit or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the GridRunAuditRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinGridRunAuditRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('GridRunAuditRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'GridRunAuditRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the GridRunAuditRelatedByIdModification relation GridRunAudit object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\GridRunAuditQuery A secondary query class using the current class as primary query
     */
    public function useGridRunAuditRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinGridRunAuditRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'GridRunAuditRelatedByIdModification', '\App\GridRunAuditQuery');
    }

    /**
     * Filter the query by a related Country object
     *
     * @param   Country|PropelObjectCollection $country  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByCountryRelatedByIdCreation($country, $comparison = null)
    {
        if ($country instanceof Country) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $country->getIdCreation(), $comparison);
        } elseif ($country instanceof PropelObjectCollection) {
            return $this
                ->useCountryRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($country->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByCountryRelatedByIdCreation() only accepts arguments of type Country or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the CountryRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinCountryRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('CountryRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'CountryRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the CountryRelatedByIdCreation relation Country object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\CountryQuery A secondary query class using the current class as primary query
     */
    public function useCountryRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinCountryRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'CountryRelatedByIdCreation', '\App\CountryQuery');
    }

    /**
     * Filter the query by a related Country object
     *
     * @param   Country|PropelObjectCollection $country  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByCountryRelatedByIdModification($country, $comparison = null)
    {
        if ($country instanceof Country) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $country->getIdModification(), $comparison);
        } elseif ($country instanceof PropelObjectCollection) {
            return $this
                ->useCountryRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($country->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByCountryRelatedByIdModification() only accepts arguments of type Country or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the CountryRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinCountryRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('CountryRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'CountryRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the CountryRelatedByIdModification relation Country object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\CountryQuery A secondary query class using the current class as primary query
     */
    public function useCountryRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinCountryRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'CountryRelatedByIdModification', '\App\CountryQuery');
    }

    /**
     * Filter the query by a related OauthClient object
     *
     * @param   OauthClient|PropelObjectCollection $oauthClient  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByOauthClientRelatedByIdCreation($oauthClient, $comparison = null)
    {
        if ($oauthClient instanceof OauthClient) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $oauthClient->getIdCreation(), $comparison);
        } elseif ($oauthClient instanceof PropelObjectCollection) {
            return $this
                ->useOauthClientRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($oauthClient->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByOauthClientRelatedByIdCreation() only accepts arguments of type OauthClient or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the OauthClientRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinOauthClientRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('OauthClientRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'OauthClientRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the OauthClientRelatedByIdCreation relation OauthClient object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\OauthClientQuery A secondary query class using the current class as primary query
     */
    public function useOauthClientRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinOauthClientRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'OauthClientRelatedByIdCreation', '\App\OauthClientQuery');
    }

    /**
     * Filter the query by a related OauthClient object
     *
     * @param   OauthClient|PropelObjectCollection $oauthClient  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByOauthClientRelatedByIdModification($oauthClient, $comparison = null)
    {
        if ($oauthClient instanceof OauthClient) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $oauthClient->getIdModification(), $comparison);
        } elseif ($oauthClient instanceof PropelObjectCollection) {
            return $this
                ->useOauthClientRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($oauthClient->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByOauthClientRelatedByIdModification() only accepts arguments of type OauthClient or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the OauthClientRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinOauthClientRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('OauthClientRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'OauthClientRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the OauthClientRelatedByIdModification relation OauthClient object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\OauthClientQuery A secondary query class using the current class as primary query
     */
    public function useOauthClientRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinOauthClientRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'OauthClientRelatedByIdModification', '\App\OauthClientQuery');
    }

    /**
     * Filter the query by a related OauthAuthCode object
     *
     * @param   OauthAuthCode|PropelObjectCollection $oauthAuthCode  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByOauthAuthCodeRelatedByIdAuthy($oauthAuthCode, $comparison = null)
    {
        if ($oauthAuthCode instanceof OauthAuthCode) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $oauthAuthCode->getIdAuthy(), $comparison);
        } elseif ($oauthAuthCode instanceof PropelObjectCollection) {
            return $this
                ->useOauthAuthCodeRelatedByIdAuthyQuery()
                ->filterByPrimaryKeys($oauthAuthCode->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByOauthAuthCodeRelatedByIdAuthy() only accepts arguments of type OauthAuthCode or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the OauthAuthCodeRelatedByIdAuthy relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinOauthAuthCodeRelatedByIdAuthy($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('OauthAuthCodeRelatedByIdAuthy');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'OauthAuthCodeRelatedByIdAuthy');
        }

        return $this;
    }

    /**
     * Use the OauthAuthCodeRelatedByIdAuthy relation OauthAuthCode object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\OauthAuthCodeQuery A secondary query class using the current class as primary query
     */
    public function useOauthAuthCodeRelatedByIdAuthyQuery($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        return $this
            ->joinOauthAuthCodeRelatedByIdAuthy($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'OauthAuthCodeRelatedByIdAuthy', '\App\OauthAuthCodeQuery');
    }

    /**
     * Filter the query by a related OauthAuthCode object
     *
     * @param   OauthAuthCode|PropelObjectCollection $oauthAuthCode  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByOauthAuthCodeRelatedByIdCreation($oauthAuthCode, $comparison = null)
    {
        if ($oauthAuthCode instanceof OauthAuthCode) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $oauthAuthCode->getIdCreation(), $comparison);
        } elseif ($oauthAuthCode instanceof PropelObjectCollection) {
            return $this
                ->useOauthAuthCodeRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($oauthAuthCode->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByOauthAuthCodeRelatedByIdCreation() only accepts arguments of type OauthAuthCode or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the OauthAuthCodeRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinOauthAuthCodeRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('OauthAuthCodeRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'OauthAuthCodeRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the OauthAuthCodeRelatedByIdCreation relation OauthAuthCode object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\OauthAuthCodeQuery A secondary query class using the current class as primary query
     */
    public function useOauthAuthCodeRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinOauthAuthCodeRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'OauthAuthCodeRelatedByIdCreation', '\App\OauthAuthCodeQuery');
    }

    /**
     * Filter the query by a related OauthAuthCode object
     *
     * @param   OauthAuthCode|PropelObjectCollection $oauthAuthCode  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByOauthAuthCodeRelatedByIdModification($oauthAuthCode, $comparison = null)
    {
        if ($oauthAuthCode instanceof OauthAuthCode) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $oauthAuthCode->getIdModification(), $comparison);
        } elseif ($oauthAuthCode instanceof PropelObjectCollection) {
            return $this
                ->useOauthAuthCodeRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($oauthAuthCode->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByOauthAuthCodeRelatedByIdModification() only accepts arguments of type OauthAuthCode or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the OauthAuthCodeRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinOauthAuthCodeRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('OauthAuthCodeRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'OauthAuthCodeRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the OauthAuthCodeRelatedByIdModification relation OauthAuthCode object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\OauthAuthCodeQuery A secondary query class using the current class as primary query
     */
    public function useOauthAuthCodeRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinOauthAuthCodeRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'OauthAuthCodeRelatedByIdModification', '\App\OauthAuthCodeQuery');
    }

    /**
     * Filter the query by a related OauthAccessToken object
     *
     * @param   OauthAccessToken|PropelObjectCollection $oauthAccessToken  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByOauthAccessTokenRelatedByIdAuthy($oauthAccessToken, $comparison = null)
    {
        if ($oauthAccessToken instanceof OauthAccessToken) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $oauthAccessToken->getIdAuthy(), $comparison);
        } elseif ($oauthAccessToken instanceof PropelObjectCollection) {
            return $this
                ->useOauthAccessTokenRelatedByIdAuthyQuery()
                ->filterByPrimaryKeys($oauthAccessToken->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByOauthAccessTokenRelatedByIdAuthy() only accepts arguments of type OauthAccessToken or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the OauthAccessTokenRelatedByIdAuthy relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinOauthAccessTokenRelatedByIdAuthy($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('OauthAccessTokenRelatedByIdAuthy');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'OauthAccessTokenRelatedByIdAuthy');
        }

        return $this;
    }

    /**
     * Use the OauthAccessTokenRelatedByIdAuthy relation OauthAccessToken object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\OauthAccessTokenQuery A secondary query class using the current class as primary query
     */
    public function useOauthAccessTokenRelatedByIdAuthyQuery($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        return $this
            ->joinOauthAccessTokenRelatedByIdAuthy($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'OauthAccessTokenRelatedByIdAuthy', '\App\OauthAccessTokenQuery');
    }

    /**
     * Filter the query by a related OauthAccessToken object
     *
     * @param   OauthAccessToken|PropelObjectCollection $oauthAccessToken  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByOauthAccessTokenRelatedByIdCreation($oauthAccessToken, $comparison = null)
    {
        if ($oauthAccessToken instanceof OauthAccessToken) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $oauthAccessToken->getIdCreation(), $comparison);
        } elseif ($oauthAccessToken instanceof PropelObjectCollection) {
            return $this
                ->useOauthAccessTokenRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($oauthAccessToken->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByOauthAccessTokenRelatedByIdCreation() only accepts arguments of type OauthAccessToken or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the OauthAccessTokenRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinOauthAccessTokenRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('OauthAccessTokenRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'OauthAccessTokenRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the OauthAccessTokenRelatedByIdCreation relation OauthAccessToken object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\OauthAccessTokenQuery A secondary query class using the current class as primary query
     */
    public function useOauthAccessTokenRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinOauthAccessTokenRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'OauthAccessTokenRelatedByIdCreation', '\App\OauthAccessTokenQuery');
    }

    /**
     * Filter the query by a related OauthAccessToken object
     *
     * @param   OauthAccessToken|PropelObjectCollection $oauthAccessToken  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByOauthAccessTokenRelatedByIdModification($oauthAccessToken, $comparison = null)
    {
        if ($oauthAccessToken instanceof OauthAccessToken) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $oauthAccessToken->getIdModification(), $comparison);
        } elseif ($oauthAccessToken instanceof PropelObjectCollection) {
            return $this
                ->useOauthAccessTokenRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($oauthAccessToken->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByOauthAccessTokenRelatedByIdModification() only accepts arguments of type OauthAccessToken or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the OauthAccessTokenRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinOauthAccessTokenRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('OauthAccessTokenRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'OauthAccessTokenRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the OauthAccessTokenRelatedByIdModification relation OauthAccessToken object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\OauthAccessTokenQuery A secondary query class using the current class as primary query
     */
    public function useOauthAccessTokenRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinOauthAccessTokenRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'OauthAccessTokenRelatedByIdModification', '\App\OauthAccessTokenQuery');
    }

    /**
     * Filter the query by a related OauthRefreshToken object
     *
     * @param   OauthRefreshToken|PropelObjectCollection $oauthRefreshToken  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByOauthRefreshTokenRelatedByIdAuthy($oauthRefreshToken, $comparison = null)
    {
        if ($oauthRefreshToken instanceof OauthRefreshToken) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $oauthRefreshToken->getIdAuthy(), $comparison);
        } elseif ($oauthRefreshToken instanceof PropelObjectCollection) {
            return $this
                ->useOauthRefreshTokenRelatedByIdAuthyQuery()
                ->filterByPrimaryKeys($oauthRefreshToken->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByOauthRefreshTokenRelatedByIdAuthy() only accepts arguments of type OauthRefreshToken or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the OauthRefreshTokenRelatedByIdAuthy relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinOauthRefreshTokenRelatedByIdAuthy($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('OauthRefreshTokenRelatedByIdAuthy');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'OauthRefreshTokenRelatedByIdAuthy');
        }

        return $this;
    }

    /**
     * Use the OauthRefreshTokenRelatedByIdAuthy relation OauthRefreshToken object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\OauthRefreshTokenQuery A secondary query class using the current class as primary query
     */
    public function useOauthRefreshTokenRelatedByIdAuthyQuery($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        return $this
            ->joinOauthRefreshTokenRelatedByIdAuthy($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'OauthRefreshTokenRelatedByIdAuthy', '\App\OauthRefreshTokenQuery');
    }

    /**
     * Filter the query by a related OauthRefreshToken object
     *
     * @param   OauthRefreshToken|PropelObjectCollection $oauthRefreshToken  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByOauthRefreshTokenRelatedByIdCreation($oauthRefreshToken, $comparison = null)
    {
        if ($oauthRefreshToken instanceof OauthRefreshToken) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $oauthRefreshToken->getIdCreation(), $comparison);
        } elseif ($oauthRefreshToken instanceof PropelObjectCollection) {
            return $this
                ->useOauthRefreshTokenRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($oauthRefreshToken->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByOauthRefreshTokenRelatedByIdCreation() only accepts arguments of type OauthRefreshToken or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the OauthRefreshTokenRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinOauthRefreshTokenRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('OauthRefreshTokenRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'OauthRefreshTokenRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the OauthRefreshTokenRelatedByIdCreation relation OauthRefreshToken object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\OauthRefreshTokenQuery A secondary query class using the current class as primary query
     */
    public function useOauthRefreshTokenRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinOauthRefreshTokenRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'OauthRefreshTokenRelatedByIdCreation', '\App\OauthRefreshTokenQuery');
    }

    /**
     * Filter the query by a related OauthRefreshToken object
     *
     * @param   OauthRefreshToken|PropelObjectCollection $oauthRefreshToken  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByOauthRefreshTokenRelatedByIdModification($oauthRefreshToken, $comparison = null)
    {
        if ($oauthRefreshToken instanceof OauthRefreshToken) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $oauthRefreshToken->getIdModification(), $comparison);
        } elseif ($oauthRefreshToken instanceof PropelObjectCollection) {
            return $this
                ->useOauthRefreshTokenRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($oauthRefreshToken->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByOauthRefreshTokenRelatedByIdModification() only accepts arguments of type OauthRefreshToken or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the OauthRefreshTokenRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinOauthRefreshTokenRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('OauthRefreshTokenRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'OauthRefreshTokenRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the OauthRefreshTokenRelatedByIdModification relation OauthRefreshToken object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\OauthRefreshTokenQuery A secondary query class using the current class as primary query
     */
    public function useOauthRefreshTokenRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinOauthRefreshTokenRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'OauthRefreshTokenRelatedByIdModification', '\App\OauthRefreshTokenQuery');
    }

    /**
     * Filter the query by a related MessageI18n object
     *
     * @param   MessageI18n|PropelObjectCollection $messageI18n  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByMessageI18nRelatedByIdCreation($messageI18n, $comparison = null)
    {
        if ($messageI18n instanceof MessageI18n) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $messageI18n->getIdCreation(), $comparison);
        } elseif ($messageI18n instanceof PropelObjectCollection) {
            return $this
                ->useMessageI18nRelatedByIdCreationQuery()
                ->filterByPrimaryKeys($messageI18n->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByMessageI18nRelatedByIdCreation() only accepts arguments of type MessageI18n or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the MessageI18nRelatedByIdCreation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinMessageI18nRelatedByIdCreation($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('MessageI18nRelatedByIdCreation');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'MessageI18nRelatedByIdCreation');
        }

        return $this;
    }

    /**
     * Use the MessageI18nRelatedByIdCreation relation MessageI18n object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\MessageI18nQuery A secondary query class using the current class as primary query
     */
    public function useMessageI18nRelatedByIdCreationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinMessageI18nRelatedByIdCreation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'MessageI18nRelatedByIdCreation', '\App\MessageI18nQuery');
    }

    /**
     * Filter the query by a related MessageI18n object
     *
     * @param   MessageI18n|PropelObjectCollection $messageI18n  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 AuthyQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByMessageI18nRelatedByIdModification($messageI18n, $comparison = null)
    {
        if ($messageI18n instanceof MessageI18n) {
            return $this
                ->addUsingAlias(AuthyPeer::ID_AUTHY, $messageI18n->getIdModification(), $comparison);
        } elseif ($messageI18n instanceof PropelObjectCollection) {
            return $this
                ->useMessageI18nRelatedByIdModificationQuery()
                ->filterByPrimaryKeys($messageI18n->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByMessageI18nRelatedByIdModification() only accepts arguments of type MessageI18n or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the MessageI18nRelatedByIdModification relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function joinMessageI18nRelatedByIdModification($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('MessageI18nRelatedByIdModification');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'MessageI18nRelatedByIdModification');
        }

        return $this;
    }

    /**
     * Use the MessageI18nRelatedByIdModification relation MessageI18n object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\MessageI18nQuery A secondary query class using the current class as primary query
     */
    public function useMessageI18nRelatedByIdModificationQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinMessageI18nRelatedByIdModification($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'MessageI18nRelatedByIdModification', '\App\MessageI18nQuery');
    }

    /**
     * Filter the query by a related AuthyGroup object
     * using the authy_group_x table as cross reference
     *
     * @param   AuthyGroup $authyGroup the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return   AuthyQuery The current query, for fluid interface
     */
    /*public function filterByRelAuthyGroupRelatedByIdAuthyGroup($authyGroup, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useAuthyGroupXRelatedByIdAuthyQuery()
            ->filterByAuthyGroupRelatedByIdAuthyGroup($authyGroup, $comparison)
            ->endUse();
    }*/

    /**
     * Filter the query by a related AuthyGroup object
     * using the authy_group_x table as cross reference
     *
     * @param   AuthyGroup $authyGroup the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return   AuthyQuery The current query, for fluid interface
     */
    /*public function filterByRelAuthyGroupRelatedByIdGroupCreation($authyGroup, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useAuthyGroupXRelatedByIdAuthyQuery()
            ->filterByAuthyGroupRelatedByIdGroupCreation($authyGroup, $comparison)
            ->endUse();
    }*/

    /**
     * Filter the query by a related Authy object
     * using the authy_group_x table as cross reference
     *
     * @param   Authy $authy the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return   AuthyQuery The current query, for fluid interface
     */
    /*public function filterByRelAuthyRelatedByIdCreation($authy, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useAuthyGroupXRelatedByIdAuthyQuery()
            ->filterByAuthyRelatedByIdCreation($authy, $comparison)
            ->endUse();
    }*/

    /**
     * Filter the query by a related Authy object
     * using the authy_group_x table as cross reference
     *
     * @param   Authy $authy the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return   AuthyQuery The current query, for fluid interface
     */
    /*public function filterByRelAuthyRelatedByIdModification($authy, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useAuthyGroupXRelatedByIdAuthyQuery()
            ->filterByAuthyRelatedByIdModification($authy, $comparison)
            ->endUse();
    }*/

    /**
     * Filter the query by a related AuthyGroup object
     * using the authy_group_x table as cross reference
     *
     * @param   AuthyGroup $authyGroup the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return   AuthyQuery The current query, for fluid interface
     */
    /*public function filterByRelAuthyGroupRelatedByIdAuthyGroup($authyGroup, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useAuthyGroupXRelatedByIdCreationQuery()
            ->filterByAuthyGroupRelatedByIdAuthyGroup($authyGroup, $comparison)
            ->endUse();
    }*/

    /**
     * Filter the query by a related Authy object
     * using the authy_group_x table as cross reference
     *
     * @param   Authy $authy the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return   AuthyQuery The current query, for fluid interface
     */
    /*public function filterByRelAuthyRelatedByIdAuthy($authy, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useAuthyGroupXRelatedByIdCreationQuery()
            ->filterByAuthyRelatedByIdAuthy($authy, $comparison)
            ->endUse();
    }*/

    /**
     * Filter the query by a related AuthyGroup object
     * using the authy_group_x table as cross reference
     *
     * @param   AuthyGroup $authyGroup the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return   AuthyQuery The current query, for fluid interface
     */
    /*public function filterByRelAuthyGroupRelatedByIdGroupCreation($authyGroup, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useAuthyGroupXRelatedByIdCreationQuery()
            ->filterByAuthyGroupRelatedByIdGroupCreation($authyGroup, $comparison)
            ->endUse();
    }*/

    /**
     * Filter the query by a related Authy object
     * using the authy_group_x table as cross reference
     *
     * @param   Authy $authy the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return   AuthyQuery The current query, for fluid interface
     */
    /*public function filterByRelAuthyRelatedByIdModification($authy, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useAuthyGroupXRelatedByIdCreationQuery()
            ->filterByAuthyRelatedByIdModification($authy, $comparison)
            ->endUse();
    }*/

    /**
     * Filter the query by a related AuthyGroup object
     * using the authy_group_x table as cross reference
     *
     * @param   AuthyGroup $authyGroup the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return   AuthyQuery The current query, for fluid interface
     */
    /*public function filterByRelAuthyGroupRelatedByIdAuthyGroup($authyGroup, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useAuthyGroupXRelatedByIdModificationQuery()
            ->filterByAuthyGroupRelatedByIdAuthyGroup($authyGroup, $comparison)
            ->endUse();
    }*/

    /**
     * Filter the query by a related Authy object
     * using the authy_group_x table as cross reference
     *
     * @param   Authy $authy the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return   AuthyQuery The current query, for fluid interface
     */
    /*public function filterByRelAuthyRelatedByIdAuthy($authy, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useAuthyGroupXRelatedByIdModificationQuery()
            ->filterByAuthyRelatedByIdAuthy($authy, $comparison)
            ->endUse();
    }*/

    /**
     * Filter the query by a related AuthyGroup object
     * using the authy_group_x table as cross reference
     *
     * @param   AuthyGroup $authyGroup the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return   AuthyQuery The current query, for fluid interface
     */
    /*public function filterByRelAuthyGroupRelatedByIdGroupCreation($authyGroup, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useAuthyGroupXRelatedByIdModificationQuery()
            ->filterByAuthyGroupRelatedByIdGroupCreation($authyGroup, $comparison)
            ->endUse();
    }*/

    /**
     * Filter the query by a related Authy object
     * using the authy_group_x table as cross reference
     *
     * @param   Authy $authy the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return   AuthyQuery The current query, for fluid interface
     */
    /*public function filterByRelAuthyRelatedByIdCreation($authy, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useAuthyGroupXRelatedByIdModificationQuery()
            ->filterByAuthyRelatedByIdCreation($authy, $comparison)
            ->endUse();
    }*/

    /**
     * Exclude object from result
     *
     * @param   Authy $authy Object to remove from the list of results
     *
     * @return AuthyQuery The current query, for fluid interface
     */
    public function prune($authy = null)
    {
        if ($authy) {
            $this->addUsingAlias(AuthyPeer::ID_AUTHY, $authy->getIdAuthy(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Code to execute before every SELECT statement
     *
     * @param     PropelPDO $con The connection object used by the query
     */
    protected function basePreSelect(PropelPDO $con)
    {
        // GoatCheese behavior

                if (defined('_AUTH_VAR') && isset($_SESSION[_AUTH_VAR]) && is_object($_SESSION[_AUTH_VAR])
                    && method_exists($_SESSION[_AUTH_VAR], 'get')
                    && $_SESSION[_AUTH_VAR]->get('connected') == 'YES'
                    && ! $_SESSION[_AUTH_VAR]->get('isRoot')
                    && $_SESSION[_AUTH_VAR]->get('id_tenant')) {
                    $this->filterByIdTenant($_SESSION[_AUTH_VAR]->get('id_tenant'));
                }

        return $this->preSelect($con);
    }

    /**
     * Code to execute before every DELETE statement
     *
     * @param     PropelPDO $con The connection object used by the query
     */
    protected function basePreDelete(PropelPDO $con)
    {
        // GoatCheese behavior

                if (defined('_AUTH_VAR') && isset($_SESSION[_AUTH_VAR]) && is_object($_SESSION[_AUTH_VAR])
                    && method_exists($_SESSION[_AUTH_VAR], 'get')
                    && $_SESSION[_AUTH_VAR]->get('connected') == 'YES'
                    && ! $_SESSION[_AUTH_VAR]->get('isRoot')
                    && $_SESSION[_AUTH_VAR]->get('id_tenant')) {
                    $this->filterByIdTenant($_SESSION[_AUTH_VAR]->get('id_tenant'));
                }

        return $this->preDelete($con);
    }

    /**
     * Code to execute after every DELETE statement
     *
     * @param     int $affectedRows the number of deleted rows
     * @param     PropelPDO $con The connection object used by the query
     */
    protected function basePostDelete($affectedRows, PropelPDO $con)
    {
        // GoatCheese behavior

                if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                    \ApiGoat\Utility\TableVersion::bump('authy');
                }

        return $this->postDelete($affectedRows, $con);
    }

    /**
     * Code to execute before every UPDATE statement
     *
     * @param     array $values The associative array of columns and values for the update
     * @param     PropelPDO $con The connection object used by the query
     * @param     boolean $forceIndividualSaves If false (default), the resulting call is a BasePeer::doUpdate(), otherwise it is a series of save() calls on all the found objects
     */
    protected function basePreUpdate(&$values, PropelPDO $con, $forceIndividualSaves = false)
    {
        // GoatCheese behavior

                if (defined('_AUTH_VAR') && isset($_SESSION[_AUTH_VAR]) && is_object($_SESSION[_AUTH_VAR])
                    && method_exists($_SESSION[_AUTH_VAR], 'get')
                    && $_SESSION[_AUTH_VAR]->get('connected') == 'YES'
                    && ! $_SESSION[_AUTH_VAR]->get('isRoot')
                    && $_SESSION[_AUTH_VAR]->get('id_tenant')) {
                    $this->filterByIdTenant($_SESSION[_AUTH_VAR]->get('id_tenant'));
                }

        return $this->preUpdate($values, $con, $forceIndividualSaves);
    }

    /**
     * Code to execute after every UPDATE statement
     *
     * @param     int $affectedRows the number of updated rows
     * @param     PropelPDO $con The connection object used by the query
     */
    protected function basePostUpdate($affectedRows, PropelPDO $con)
    {
        // GoatCheese behavior

                if (class_exists('\\ApiGoat\\Utility\\TableVersion')) {
                    \ApiGoat\Utility\TableVersion::bump('authy');
                }

        return $this->postUpdate($affectedRows, $con);
    }

    // add_tablestamp behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     AuthyQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7){
        return $this->addUsingAlias(AuthyPeer::DATE_MODIFICATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     AuthyQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst(){
        return $this->addDescendingOrderByColumn(AuthyPeer::DATE_MODIFICATION);
    }

    /**
     * Order by update date asc
     *
     * @return     AuthyQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst(){
        return $this->addAscendingOrderByColumn(AuthyPeer::DATE_MODIFICATION);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     AuthyQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7){
        return $this->addUsingAlias(AuthyPeer::DATE_CREATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return     AuthyQuery The current query, for fluid interface
     */
    public function lastCreatedFirst(){
        return $this->addDescendingOrderByColumn(AuthyPeer::DATE_CREATION);
    }

    /**
     * Order by create date asc
     *
     * @return     AuthyQuery The current query, for fluid interface
     */
    public function firstCreatedFirst(){
        return $this->addAscendingOrderByColumn(AuthyPeer::DATE_CREATION);
    }
}
