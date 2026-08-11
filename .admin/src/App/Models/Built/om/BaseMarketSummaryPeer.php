<?php

namespace App\om;

use \BasePeer;
use \Criteria;
use \PDO;
use \PDOStatement;
use \Propel;
use \PropelException;
use \PropelPDO;
use App\AuthyGroupPeer;
use App\AuthyPeer;
use App\MarketSummary;
use App\MarketSummaryPeer;
use App\map\MarketSummaryTableMap;

/**
 * Base static class for performing query and update operations on the 'market_summary' table.
 *
 * Market Data
 *
 * @package propel.generator..om
 */
abstract class BaseMarketSummaryPeer
{

    /** the default database name for this class */
    const DATABASE_NAME = 'apigtbot';

    /** the table name for this class */
    const TABLE_NAME = 'market_summary';

    /** the related Propel class for this table */
    const OM_CLASS = 'App\\MarketSummary';

    /** the related TableMap class for this table */
    const TM_CLASS = 'App\\map\\MarketSummaryTableMap';

    /** The total number of columns. */
    const NUM_COLUMNS = 31;

    /** The number of lazy-loaded columns. */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /** The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS) */
    const NUM_HYDRATE_COLUMNS = 31;

    /** the column name for the id_market_summary field */
    const ID_MARKET_SUMMARY = 'market_summary.id_market_summary';

    /** the column name for the symbol field */
    const SYMBOL = 'market_summary.symbol';

    /** the column name for the tf field */
    const TF = 'market_summary.tf';

    /** the column name for the price field */
    const PRICE = 'market_summary.price';

    /** the column name for the ema20 field */
    const EMA20 = 'market_summary.ema20';

    /** the column name for the ema50 field */
    const EMA50 = 'market_summary.ema50';

    /** the column name for the ema200 field */
    const EMA200 = 'market_summary.ema200';

    /** the column name for the rsi14 field */
    const RSI14 = 'market_summary.rsi14';

    /** the column name for the atr14 field */
    const ATR14 = 'market_summary.atr14';

    /** the column name for the atr_pct field */
    const ATR_PCT = 'market_summary.atr_pct';

    /** the column name for the trend field */
    const TREND = 'market_summary.trend';

    /** the column name for the swing_high field */
    const SWING_HIGH = 'market_summary.swing_high';

    /** the column name for the swing_low field */
    const SWING_LOW = 'market_summary.swing_low';

    /** the column name for the candles_used field */
    const CANDLES_USED = 'market_summary.candles_used';

    /** the column name for the recent_candles field */
    const RECENT_CANDLES = 'market_summary.recent_candles';

    /** the column name for the funding_rate field */
    const FUNDING_RATE = 'market_summary.funding_rate';

    /** the column name for the depth_imbalance field */
    const DEPTH_IMBALANCE = 'market_summary.depth_imbalance';

    /** the column name for the depth_imbalance_avg field */
    const DEPTH_IMBALANCE_AVG = 'market_summary.depth_imbalance_avg';

    /** the column name for the adx14 field */
    const ADX14 = 'market_summary.adx14';

    /** the column name for the atr_pct_rank field */
    const ATR_PCT_RANK = 'market_summary.atr_pct_rank';

    /** the column name for the taker_buy_ratio field */
    const TAKER_BUY_RATIO = 'market_summary.taker_buy_ratio';

    /** the column name for the vol_zscore field */
    const VOL_ZSCORE = 'market_summary.vol_zscore';

    /** the column name for the er20 field */
    const ER20 = 'market_summary.er20';

    /** the column name for the chop14 field */
    const CHOP14 = 'market_summary.chop14';

    /** the column name for the funding_pct field */
    const FUNDING_PCT = 'market_summary.funding_pct';

    /** the column name for the computed_at field */
    const COMPUTED_AT = 'market_summary.computed_at';

    /** the column name for the date_creation field */
    const DATE_CREATION = 'market_summary.date_creation';

    /** the column name for the date_modification field */
    const DATE_MODIFICATION = 'market_summary.date_modification';

    /** the column name for the id_group_creation field */
    const ID_GROUP_CREATION = 'market_summary.id_group_creation';

    /** the column name for the id_creation field */
    const ID_CREATION = 'market_summary.id_creation';

    /** the column name for the id_modification field */
    const ID_MODIFICATION = 'market_summary.id_modification';

    /** The enumerated values for the trend field */
    const TREND_STRONG_UP = 'strong_up';
    const TREND_UP = 'up';
    const TREND_SIDEWAYS = 'sideways';
    const TREND_DOWN = 'down';
    const TREND_STRONG_DOWN = 'strong_down';

    /** The default string format for model objects of the related table **/
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * An identity map to hold any loaded instances of MarketSummary objects.
     * This must be public so that other peer classes can access this when hydrating from JOIN
     * queries.
     * @var        array MarketSummary[]
     */
    public static $instances = array();


    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. MarketSummaryPeer::$fieldNames[MarketSummaryPeer::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        BasePeer::TYPE_PHPNAME => array ('IdMarketSummary', 'Symbol', 'Tf', 'Price', 'Ema20', 'Ema50', 'Ema200', 'Rsi14', 'Atr14', 'AtrPct', 'Trend', 'SwingHigh', 'SwingLow', 'CandlesUsed', 'RecentCandles', 'FundingRate', 'DepthImbalance', 'DepthImbalanceAvg', 'Adx14', 'AtrPctRank', 'TakerBuyRatio', 'VolZscore', 'Er20', 'Chop14', 'FundingPct', 'ComputedAt', 'DateCreation', 'DateModification', 'IdGroupCreation', 'IdCreation', 'IdModification', ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('idMarketSummary', 'symbol', 'tf', 'price', 'ema20', 'ema50', 'ema200', 'rsi14', 'atr14', 'atrPct', 'trend', 'swingHigh', 'swingLow', 'candlesUsed', 'recentCandles', 'fundingRate', 'depthImbalance', 'depthImbalanceAvg', 'adx14', 'atrPctRank', 'takerBuyRatio', 'volZscore', 'er20', 'chop14', 'fundingPct', 'computedAt', 'dateCreation', 'dateModification', 'idGroupCreation', 'idCreation', 'idModification', ),
        BasePeer::TYPE_COLNAME => array (MarketSummaryPeer::ID_MARKET_SUMMARY, MarketSummaryPeer::SYMBOL, MarketSummaryPeer::TF, MarketSummaryPeer::PRICE, MarketSummaryPeer::EMA20, MarketSummaryPeer::EMA50, MarketSummaryPeer::EMA200, MarketSummaryPeer::RSI14, MarketSummaryPeer::ATR14, MarketSummaryPeer::ATR_PCT, MarketSummaryPeer::TREND, MarketSummaryPeer::SWING_HIGH, MarketSummaryPeer::SWING_LOW, MarketSummaryPeer::CANDLES_USED, MarketSummaryPeer::RECENT_CANDLES, MarketSummaryPeer::FUNDING_RATE, MarketSummaryPeer::DEPTH_IMBALANCE, MarketSummaryPeer::DEPTH_IMBALANCE_AVG, MarketSummaryPeer::ADX14, MarketSummaryPeer::ATR_PCT_RANK, MarketSummaryPeer::TAKER_BUY_RATIO, MarketSummaryPeer::VOL_ZSCORE, MarketSummaryPeer::ER20, MarketSummaryPeer::CHOP14, MarketSummaryPeer::FUNDING_PCT, MarketSummaryPeer::COMPUTED_AT, MarketSummaryPeer::DATE_CREATION, MarketSummaryPeer::DATE_MODIFICATION, MarketSummaryPeer::ID_GROUP_CREATION, MarketSummaryPeer::ID_CREATION, MarketSummaryPeer::ID_MODIFICATION, ),
        BasePeer::TYPE_RAW_COLNAME => array ('ID_MARKET_SUMMARY', 'SYMBOL', 'TF', 'PRICE', 'EMA20', 'EMA50', 'EMA200', 'RSI14', 'ATR14', 'ATR_PCT', 'TREND', 'SWING_HIGH', 'SWING_LOW', 'CANDLES_USED', 'RECENT_CANDLES', 'FUNDING_RATE', 'DEPTH_IMBALANCE', 'DEPTH_IMBALANCE_AVG', 'ADX14', 'ATR_PCT_RANK', 'TAKER_BUY_RATIO', 'VOL_ZSCORE', 'ER20', 'CHOP14', 'FUNDING_PCT', 'COMPUTED_AT', 'DATE_CREATION', 'DATE_MODIFICATION', 'ID_GROUP_CREATION', 'ID_CREATION', 'ID_MODIFICATION', ),
        BasePeer::TYPE_FIELDNAME => array ('id_market_summary', 'symbol', 'tf', 'price', 'ema20', 'ema50', 'ema200', 'rsi14', 'atr14', 'atr_pct', 'trend', 'swing_high', 'swing_low', 'candles_used', 'recent_candles', 'funding_rate', 'depth_imbalance', 'depth_imbalance_avg', 'adx14', 'atr_pct_rank', 'taker_buy_ratio', 'vol_zscore', 'er20', 'chop14', 'funding_pct', 'computed_at', 'date_creation', 'date_modification', 'id_group_creation', 'id_creation', 'id_modification', ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. MarketSummaryPeer::$fieldNames[BasePeer::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        BasePeer::TYPE_PHPNAME => array ('IdMarketSummary' => 0, 'Symbol' => 1, 'Tf' => 2, 'Price' => 3, 'Ema20' => 4, 'Ema50' => 5, 'Ema200' => 6, 'Rsi14' => 7, 'Atr14' => 8, 'AtrPct' => 9, 'Trend' => 10, 'SwingHigh' => 11, 'SwingLow' => 12, 'CandlesUsed' => 13, 'RecentCandles' => 14, 'FundingRate' => 15, 'DepthImbalance' => 16, 'DepthImbalanceAvg' => 17, 'Adx14' => 18, 'AtrPctRank' => 19, 'TakerBuyRatio' => 20, 'VolZscore' => 21, 'Er20' => 22, 'Chop14' => 23, 'FundingPct' => 24, 'ComputedAt' => 25, 'DateCreation' => 26, 'DateModification' => 27, 'IdGroupCreation' => 28, 'IdCreation' => 29, 'IdModification' => 30, ),
        BasePeer::TYPE_STUDLYPHPNAME => array ('idMarketSummary' => 0, 'symbol' => 1, 'tf' => 2, 'price' => 3, 'ema20' => 4, 'ema50' => 5, 'ema200' => 6, 'rsi14' => 7, 'atr14' => 8, 'atrPct' => 9, 'trend' => 10, 'swingHigh' => 11, 'swingLow' => 12, 'candlesUsed' => 13, 'recentCandles' => 14, 'fundingRate' => 15, 'depthImbalance' => 16, 'depthImbalanceAvg' => 17, 'adx14' => 18, 'atrPctRank' => 19, 'takerBuyRatio' => 20, 'volZscore' => 21, 'er20' => 22, 'chop14' => 23, 'fundingPct' => 24, 'computedAt' => 25, 'dateCreation' => 26, 'dateModification' => 27, 'idGroupCreation' => 28, 'idCreation' => 29, 'idModification' => 30, ),
        BasePeer::TYPE_COLNAME => array (MarketSummaryPeer::ID_MARKET_SUMMARY => 0, MarketSummaryPeer::SYMBOL => 1, MarketSummaryPeer::TF => 2, MarketSummaryPeer::PRICE => 3, MarketSummaryPeer::EMA20 => 4, MarketSummaryPeer::EMA50 => 5, MarketSummaryPeer::EMA200 => 6, MarketSummaryPeer::RSI14 => 7, MarketSummaryPeer::ATR14 => 8, MarketSummaryPeer::ATR_PCT => 9, MarketSummaryPeer::TREND => 10, MarketSummaryPeer::SWING_HIGH => 11, MarketSummaryPeer::SWING_LOW => 12, MarketSummaryPeer::CANDLES_USED => 13, MarketSummaryPeer::RECENT_CANDLES => 14, MarketSummaryPeer::FUNDING_RATE => 15, MarketSummaryPeer::DEPTH_IMBALANCE => 16, MarketSummaryPeer::DEPTH_IMBALANCE_AVG => 17, MarketSummaryPeer::ADX14 => 18, MarketSummaryPeer::ATR_PCT_RANK => 19, MarketSummaryPeer::TAKER_BUY_RATIO => 20, MarketSummaryPeer::VOL_ZSCORE => 21, MarketSummaryPeer::ER20 => 22, MarketSummaryPeer::CHOP14 => 23, MarketSummaryPeer::FUNDING_PCT => 24, MarketSummaryPeer::COMPUTED_AT => 25, MarketSummaryPeer::DATE_CREATION => 26, MarketSummaryPeer::DATE_MODIFICATION => 27, MarketSummaryPeer::ID_GROUP_CREATION => 28, MarketSummaryPeer::ID_CREATION => 29, MarketSummaryPeer::ID_MODIFICATION => 30, ),
        BasePeer::TYPE_RAW_COLNAME => array ('ID_MARKET_SUMMARY' => 0, 'SYMBOL' => 1, 'TF' => 2, 'PRICE' => 3, 'EMA20' => 4, 'EMA50' => 5, 'EMA200' => 6, 'RSI14' => 7, 'ATR14' => 8, 'ATR_PCT' => 9, 'TREND' => 10, 'SWING_HIGH' => 11, 'SWING_LOW' => 12, 'CANDLES_USED' => 13, 'RECENT_CANDLES' => 14, 'FUNDING_RATE' => 15, 'DEPTH_IMBALANCE' => 16, 'DEPTH_IMBALANCE_AVG' => 17, 'ADX14' => 18, 'ATR_PCT_RANK' => 19, 'TAKER_BUY_RATIO' => 20, 'VOL_ZSCORE' => 21, 'ER20' => 22, 'CHOP14' => 23, 'FUNDING_PCT' => 24, 'COMPUTED_AT' => 25, 'DATE_CREATION' => 26, 'DATE_MODIFICATION' => 27, 'ID_GROUP_CREATION' => 28, 'ID_CREATION' => 29, 'ID_MODIFICATION' => 30, ),
        BasePeer::TYPE_FIELDNAME => array ('id_market_summary' => 0, 'symbol' => 1, 'tf' => 2, 'price' => 3, 'ema20' => 4, 'ema50' => 5, 'ema200' => 6, 'rsi14' => 7, 'atr14' => 8, 'atr_pct' => 9, 'trend' => 10, 'swing_high' => 11, 'swing_low' => 12, 'candles_used' => 13, 'recent_candles' => 14, 'funding_rate' => 15, 'depth_imbalance' => 16, 'depth_imbalance_avg' => 17, 'adx14' => 18, 'atr_pct_rank' => 19, 'taker_buy_ratio' => 20, 'vol_zscore' => 21, 'er20' => 22, 'chop14' => 23, 'funding_pct' => 24, 'computed_at' => 25, 'date_creation' => 26, 'date_modification' => 27, 'id_group_creation' => 28, 'id_creation' => 29, 'id_modification' => 30, ),
        BasePeer::TYPE_NUM => array (0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, )
    );

    /** The enumerated values for this table */
    protected static $enumValueSets = array(
        MarketSummaryPeer::TREND => array(
            MarketSummaryPeer::TREND_STRONG_UP,
            MarketSummaryPeer::TREND_UP,
            MarketSummaryPeer::TREND_SIDEWAYS,
            MarketSummaryPeer::TREND_DOWN,
            MarketSummaryPeer::TREND_STRONG_DOWN,
        ),
    );

    /**
     * Translates a fieldname to another type
     *
     * @param      string $name field name
     * @param      string $fromType One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *                         BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
     * @param      string $toType   One of the class type constants
     * @return string          translated name of the field.
     * @throws PropelException - if the specified name could not be found in the fieldname mappings.
     */
    public static function translateFieldName($name, $fromType, $toType)
    {
        $toNames = MarketSummaryPeer::getFieldNames($toType);
        $key = isset(MarketSummaryPeer::$fieldKeys[$fromType][$name]) ? MarketSummaryPeer::$fieldKeys[$fromType][$name] : null;
        if ($key === null) {
            throw new PropelException("'$name' could not be found in the field names of type '$fromType'. These are: " . print_r(MarketSummaryPeer::$fieldKeys[$fromType], true));
        }

        return $toNames[$key];
    }

    /**
     * Returns an array of field names.
     *
     * @param      string $type The type of fieldnames to return:
     *                      One of the class type constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME
     *                      BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM
     * @return array           A list of field names
     * @throws PropelException - if the type is not valid.
     */
    public static function getFieldNames($type = BasePeer::TYPE_PHPNAME)
    {
        if (!array_key_exists($type, MarketSummaryPeer::$fieldNames)) {
            throw new PropelException('Method getFieldNames() expects the parameter $type to be one of the class constants BasePeer::TYPE_PHPNAME, BasePeer::TYPE_STUDLYPHPNAME, BasePeer::TYPE_COLNAME, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_NUM. ' . $type . ' was given.');
        }

        return MarketSummaryPeer::$fieldNames[$type];
    }

    /**
     * Gets the list of values for all ENUM columns
     * @return array
     */
    public static function getValueSets()
    {
      return MarketSummaryPeer::$enumValueSets;
    }

    /**
     * Gets the list of values for an ENUM column
     *
     * @param string $colname The ENUM column name.
     *
     * @return array list of possible values for the column
     */
    public static function getValueSet($colname)
    {
        $valueSets = MarketSummaryPeer::getValueSets();

        if (!isset($valueSets[$colname])) {
            throw new PropelException(sprintf('Column "%s" has no ValueSet.', $colname));
        }

        return $valueSets[$colname];
    }

    /**
     * Gets the SQL value for the ENUM column value
     *
     * @param string $colname ENUM column name.
     * @param string $enumVal ENUM value.
     *
     * @return int SQL value
     */
    public static function getSqlValueForEnum($colname, $enumVal)
    {
        $values = MarketSummaryPeer::getValueSet($colname);
        if (!in_array($enumVal, $values)) {
            throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $colname));
        }

        return array_search($enumVal, $values);
    }

    /**
     * Convenience method which changes table.column to alias.column.
     *
     * Using this method you can maintain SQL abstraction while using column aliases.
     * <code>
     *		$c->addAlias("alias1", TablePeer::TABLE_NAME);
     *		$c->addJoin(TablePeer::alias("alias1", TablePeer::PRIMARY_KEY_COLUMN), TablePeer::PRIMARY_KEY_COLUMN);
     * </code>
     * @param      string $alias The alias for the current table.
     * @param      string $column The column name for current table. (i.e. MarketSummaryPeer::COLUMN_NAME).
     * @return string
     */
    public static function alias($alias, $column)
    {
        return str_replace(MarketSummaryPeer::TABLE_NAME.'.', $alias.'.', $column);
    }

    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param      Criteria $criteria object containing the columns to add.
     * @param      string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(MarketSummaryPeer::ID_MARKET_SUMMARY);
            $criteria->addSelectColumn(MarketSummaryPeer::SYMBOL);
            $criteria->addSelectColumn(MarketSummaryPeer::TF);
            $criteria->addSelectColumn(MarketSummaryPeer::PRICE);
            $criteria->addSelectColumn(MarketSummaryPeer::EMA20);
            $criteria->addSelectColumn(MarketSummaryPeer::EMA50);
            $criteria->addSelectColumn(MarketSummaryPeer::EMA200);
            $criteria->addSelectColumn(MarketSummaryPeer::RSI14);
            $criteria->addSelectColumn(MarketSummaryPeer::ATR14);
            $criteria->addSelectColumn(MarketSummaryPeer::ATR_PCT);
            $criteria->addSelectColumn(MarketSummaryPeer::TREND);
            $criteria->addSelectColumn(MarketSummaryPeer::SWING_HIGH);
            $criteria->addSelectColumn(MarketSummaryPeer::SWING_LOW);
            $criteria->addSelectColumn(MarketSummaryPeer::CANDLES_USED);
            $criteria->addSelectColumn(MarketSummaryPeer::RECENT_CANDLES);
            $criteria->addSelectColumn(MarketSummaryPeer::FUNDING_RATE);
            $criteria->addSelectColumn(MarketSummaryPeer::DEPTH_IMBALANCE);
            $criteria->addSelectColumn(MarketSummaryPeer::DEPTH_IMBALANCE_AVG);
            $criteria->addSelectColumn(MarketSummaryPeer::ADX14);
            $criteria->addSelectColumn(MarketSummaryPeer::ATR_PCT_RANK);
            $criteria->addSelectColumn(MarketSummaryPeer::TAKER_BUY_RATIO);
            $criteria->addSelectColumn(MarketSummaryPeer::VOL_ZSCORE);
            $criteria->addSelectColumn(MarketSummaryPeer::ER20);
            $criteria->addSelectColumn(MarketSummaryPeer::CHOP14);
            $criteria->addSelectColumn(MarketSummaryPeer::FUNDING_PCT);
            $criteria->addSelectColumn(MarketSummaryPeer::COMPUTED_AT);
            $criteria->addSelectColumn(MarketSummaryPeer::DATE_CREATION);
            $criteria->addSelectColumn(MarketSummaryPeer::DATE_MODIFICATION);
            $criteria->addSelectColumn(MarketSummaryPeer::ID_GROUP_CREATION);
            $criteria->addSelectColumn(MarketSummaryPeer::ID_CREATION);
            $criteria->addSelectColumn(MarketSummaryPeer::ID_MODIFICATION);
        } else {
            $criteria->addSelectColumn($alias . '.id_market_summary');
            $criteria->addSelectColumn($alias . '.symbol');
            $criteria->addSelectColumn($alias . '.tf');
            $criteria->addSelectColumn($alias . '.price');
            $criteria->addSelectColumn($alias . '.ema20');
            $criteria->addSelectColumn($alias . '.ema50');
            $criteria->addSelectColumn($alias . '.ema200');
            $criteria->addSelectColumn($alias . '.rsi14');
            $criteria->addSelectColumn($alias . '.atr14');
            $criteria->addSelectColumn($alias . '.atr_pct');
            $criteria->addSelectColumn($alias . '.trend');
            $criteria->addSelectColumn($alias . '.swing_high');
            $criteria->addSelectColumn($alias . '.swing_low');
            $criteria->addSelectColumn($alias . '.candles_used');
            $criteria->addSelectColumn($alias . '.recent_candles');
            $criteria->addSelectColumn($alias . '.funding_rate');
            $criteria->addSelectColumn($alias . '.depth_imbalance');
            $criteria->addSelectColumn($alias . '.depth_imbalance_avg');
            $criteria->addSelectColumn($alias . '.adx14');
            $criteria->addSelectColumn($alias . '.atr_pct_rank');
            $criteria->addSelectColumn($alias . '.taker_buy_ratio');
            $criteria->addSelectColumn($alias . '.vol_zscore');
            $criteria->addSelectColumn($alias . '.er20');
            $criteria->addSelectColumn($alias . '.chop14');
            $criteria->addSelectColumn($alias . '.funding_pct');
            $criteria->addSelectColumn($alias . '.computed_at');
            $criteria->addSelectColumn($alias . '.date_creation');
            $criteria->addSelectColumn($alias . '.date_modification');
            $criteria->addSelectColumn($alias . '.id_group_creation');
            $criteria->addSelectColumn($alias . '.id_creation');
            $criteria->addSelectColumn($alias . '.id_modification');
        }
    }

    /**
     * Returns the number of rows matching criteria.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @return int Number of matching rows.
     */
    public static function doCount(Criteria $criteria, $distinct = false, ?PropelPDO $con = null)
    {
        // we may modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(MarketSummaryPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            MarketSummaryPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count
        $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME); // Set the correct dbName

        if ($con === null) {
            $con = Propel::getConnection(MarketSummaryPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }
        // BasePeer returns a PDOStatement
        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }
    /**
     * Selects one object from the DB.
     *
     * @param      Criteria $criteria object used to create the SELECT statement.
     * @param      PropelPDO $con
     * @return MarketSummary
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectOne(Criteria $criteria, ?PropelPDO $con = null)
    {
        $critcopy = clone $criteria;
        $critcopy->setLimit(1);
        $objects = MarketSummaryPeer::doSelect($critcopy, $con);
        if ($objects) {
            return $objects[0];
        }

        return null;
    }
    /**
     * Selects several row from the DB.
     *
     * @param      Criteria $criteria The Criteria object used to build the SELECT statement.
     * @param      PropelPDO $con
     * @return array           Array of selected Objects
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelect(Criteria $criteria, ?PropelPDO $con = null)
    {
        return MarketSummaryPeer::populateObjects(MarketSummaryPeer::doSelectStmt($criteria, $con));
    }
    /**
     * Prepares the Criteria object and uses the parent doSelect() method to execute a PDOStatement.
     *
     * Use this method directly if you want to work with an executed statement directly (for example
     * to perform your own object hydration).
     *
     * @param      Criteria $criteria The Criteria object used to build the SELECT statement.
     * @param      PropelPDO $con The connection to use
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     * @return PDOStatement The executed PDOStatement object.
     * @see        BasePeer::doSelect()
     */
    public static function doSelectStmt(Criteria $criteria, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(MarketSummaryPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        if (!$criteria->hasSelectClause()) {
            $criteria = clone $criteria;
            MarketSummaryPeer::addSelectColumns($criteria);
        }

        // Set the correct dbName
        $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME);

        // BasePeer returns a PDOStatement
        return BasePeer::doSelect($criteria, $con);
    }
    /**
     * Adds an object to the instance pool.
     *
     * Propel keeps cached copies of objects in an instance pool when they are retrieved
     * from the database.  In some cases -- especially when you override doSelect*()
     * methods in your stub classes -- you may need to explicitly add objects
     * to the cache in order to ensure that the same objects are always returned by doSelect*()
     * and retrieveByPK*() calls.
     *
     * @param MarketSummary $obj A MarketSummary object.
     * @param      string $key (optional) key to use for instance map (for performance boost if key was already calculated externally).
     */
    public static function addInstanceToPool($obj, $key = null)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if ($key === null) {
                $key = (string) $obj->getIdMarketSummary();
            } // if key === null
            MarketSummaryPeer::$instances[$key] = $obj;
        }
    }

    /**
     * Removes an object from the instance pool.
     *
     * Propel keeps cached copies of objects in an instance pool when they are retrieved
     * from the database.  In some cases -- especially when you override doDelete
     * methods in your stub classes -- you may need to explicitly remove objects
     * from the cache in order to prevent returning objects that no longer exist.
     *
     * @param      mixed $value A MarketSummary object or a primary key value.
     *
     * @return void
     * @throws PropelException - if the value is invalid.
     */
    public static function removeInstanceFromPool($value)
    {
        if (Propel::isInstancePoolingEnabled() && $value !== null) {
            if (is_object($value) && $value instanceof MarketSummary) {
                $key = (string) $value->getIdMarketSummary();
            } elseif (is_scalar($value)) {
                // assume we've been passed a primary key
                $key = (string) $value;
            } else {
                $e = new PropelException("Invalid value passed to removeInstanceFromPool().  Expected primary key or MarketSummary object; got " . (is_object($value) ? get_class($value) . ' object.' : var_export($value,true)));
                throw $e;
            }

            unset(MarketSummaryPeer::$instances[$key]);
        }
    } // removeInstanceFromPool()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param      string $key The key (@see getPrimaryKeyHash()) for this instance.
     * @return MarketSummary Found object or null if 1) no instance exists for specified key or 2) instance pooling has been disabled.
     * @see        getPrimaryKeyHash()
     */
    public static function getInstanceFromPool($key)
    {
        if (Propel::isInstancePoolingEnabled()) {
            if (isset(MarketSummaryPeer::$instances[$key])) {
                return MarketSummaryPeer::$instances[$key];
            }
        }

        return null; // just to be explicit
    }

    /**
     * Clear the instance pool.
     *
     * @return void
     */
    public static function clearInstancePool($and_clear_all_references = false)
    {
      if ($and_clear_all_references) {
        foreach (MarketSummaryPeer::$instances as $instance) {
          $instance->clearAllReferences(true);
        }
      }
        MarketSummaryPeer::$instances = array();
    }

    /**
     * Method to invalidate the instance pool of all tables related to market_summary
     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool()
    {
    }

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param      array $row PropelPDO resultset row.
     * @param      int $startcol The 0-based offset for reading from the resultset row.
     * @return string A string version of PK or null if the components of primary key in result array are all null.
     */
    public static function getPrimaryKeyHashFromRow($row, $startcol = 0)
    {
        // If the PK cannot be derived from the row, return null.
        if ($row[$startcol] === null) {
            return null;
        }

        return (string) $row[$startcol];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param      array $row PropelPDO resultset row.
     * @param      int $startcol The 0-based offset for reading from the resultset row.
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $startcol = 0)
    {

        return (int) $row[$startcol];
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function populateObjects(PDOStatement $stmt)
    {
        $results = array();

        // set the class once to avoid overhead in the loop
        $cls = MarketSummaryPeer::getOMClass();
        // populate the object(s)
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key = MarketSummaryPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj = MarketSummaryPeer::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                MarketSummaryPeer::addInstanceToPool($obj, $key);
            } // if key exists
        }
        $stmt->closeCursor();

        return $results;
    }
    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param      array $row PropelPDO resultset row.
     * @param      int $startcol The 0-based offset for reading from the resultset row.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     * @return array (MarketSummary object, last column rank)
     */
    public static function populateObject($row, $startcol = 0)
    {
        $key = MarketSummaryPeer::getPrimaryKeyHashFromRow($row, $startcol);
        if (null !== ($obj = MarketSummaryPeer::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $startcol, true); // rehydrate
            $col = $startcol + MarketSummaryPeer::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = MarketSummaryPeer::OM_CLASS;
            $obj = new $cls();
            $col = $obj->hydrate($row, $startcol);
            MarketSummaryPeer::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * Gets the SQL value for Trend ENUM value
     *
     * @param  string $enumVal ENUM value to get SQL value for
     * @return int SQL value
     */
    public static function getTrendSqlValue($enumVal)
    {
        return MarketSummaryPeer::getSqlValueForEnum(MarketSummaryPeer::TREND, $enumVal);
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyGroup table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAuthyGroup(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(MarketSummaryPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            MarketSummaryPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(MarketSummaryPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(MarketSummaryPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyRelatedByIdCreation table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAuthyRelatedByIdCreation(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(MarketSummaryPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            MarketSummaryPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(MarketSummaryPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(MarketSummaryPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyRelatedByIdModification table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAuthyRelatedByIdModification(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(MarketSummaryPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            MarketSummaryPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(MarketSummaryPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(MarketSummaryPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Selects a collection of MarketSummary objects pre-filled with their AuthyGroup objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of MarketSummary objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyGroup(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME);
        }

        MarketSummaryPeer::addSelectColumns($criteria);
        $startcol = MarketSummaryPeer::NUM_HYDRATE_COLUMNS;
        AuthyGroupPeer::addSelectColumns($criteria);

        $criteria->addJoin(MarketSummaryPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = MarketSummaryPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = MarketSummaryPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = MarketSummaryPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                MarketSummaryPeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = AuthyGroupPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = AuthyGroupPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    AuthyGroupPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (MarketSummary) to $obj2 (AuthyGroup)
                $obj2->addMarketSummary($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of MarketSummary objects pre-filled with their Authy objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of MarketSummary objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyRelatedByIdCreation(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME);
        }

        MarketSummaryPeer::addSelectColumns($criteria);
        $startcol = MarketSummaryPeer::NUM_HYDRATE_COLUMNS;
        AuthyPeer::addSelectColumns($criteria);

        $criteria->addJoin(MarketSummaryPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = MarketSummaryPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = MarketSummaryPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = MarketSummaryPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                MarketSummaryPeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = AuthyPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = AuthyPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    AuthyPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (MarketSummary) to $obj2 (Authy)
                $obj2->addMarketSummaryRelatedByIdCreation($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of MarketSummary objects pre-filled with their Authy objects.
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of MarketSummary objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAuthyRelatedByIdModification(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME);
        }

        MarketSummaryPeer::addSelectColumns($criteria);
        $startcol = MarketSummaryPeer::NUM_HYDRATE_COLUMNS;
        AuthyPeer::addSelectColumns($criteria);

        $criteria->addJoin(MarketSummaryPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = MarketSummaryPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = MarketSummaryPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {

                $cls = MarketSummaryPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                MarketSummaryPeer::addInstanceToPool($obj1, $key1);
            } // if $obj1 already loaded

            $key2 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol);
            if ($key2 !== null) {
                $obj2 = AuthyPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = AuthyPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol);
                    AuthyPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 already loaded

                // Add the $obj1 (MarketSummary) to $obj2 (Authy)
                $obj2->addMarketSummaryRelatedByIdModification($obj1);

            } // if joined row was not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Returns the number of rows matching criteria, joining all related tables
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAll(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(MarketSummaryPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            MarketSummaryPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY won't ever affect the count

        // Set the correct dbName
        $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(MarketSummaryPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(MarketSummaryPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(MarketSummaryPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(MarketSummaryPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }

    /**
     * Selects a collection of MarketSummary objects pre-filled with all related objects.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of MarketSummary objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAll(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME);
        }

        MarketSummaryPeer::addSelectColumns($criteria);
        $startcol2 = MarketSummaryPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol5 = $startcol4 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(MarketSummaryPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $criteria->addJoin(MarketSummaryPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(MarketSummaryPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = MarketSummaryPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = MarketSummaryPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = MarketSummaryPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                MarketSummaryPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

            // Add objects for joined AuthyGroup rows

            $key2 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol2);
            if ($key2 !== null) {
                $obj2 = AuthyGroupPeer::getInstanceFromPool($key2);
                if (!$obj2) {

                    $cls = AuthyGroupPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    AuthyGroupPeer::addInstanceToPool($obj2, $key2);
                } // if obj2 loaded

                // Add the $obj1 (MarketSummary) to the collection in $obj2 (AuthyGroup)
                $obj2->addMarketSummary($obj1);
            } // if joined row not null

            // Add objects for joined Authy rows

            $key3 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol3);
            if ($key3 !== null) {
                $obj3 = AuthyPeer::getInstanceFromPool($key3);
                if (!$obj3) {

                    $cls = AuthyPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    AuthyPeer::addInstanceToPool($obj3, $key3);
                } // if obj3 loaded

                // Add the $obj1 (MarketSummary) to the collection in $obj3 (Authy)
                $obj3->addMarketSummaryRelatedByIdCreation($obj1);
            } // if joined row not null

            // Add objects for joined Authy rows

            $key4 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol4);
            if ($key4 !== null) {
                $obj4 = AuthyPeer::getInstanceFromPool($key4);
                if (!$obj4) {

                    $cls = AuthyPeer::getOMClass();

                    $obj4 = new $cls();
                    $obj4->hydrate($row, $startcol4);
                    AuthyPeer::addInstanceToPool($obj4, $key4);
                } // if obj4 loaded

                // Add the $obj1 (MarketSummary) to the collection in $obj4 (Authy)
                $obj4->addMarketSummaryRelatedByIdModification($obj1);
            } // if joined row not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyGroup table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptAuthyGroup(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(MarketSummaryPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            MarketSummaryPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(MarketSummaryPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(MarketSummaryPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(MarketSummaryPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyRelatedByIdCreation table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptAuthyRelatedByIdCreation(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(MarketSummaryPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            MarketSummaryPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(MarketSummaryPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(MarketSummaryPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Returns the number of rows matching criteria, joining the related AuthyRelatedByIdModification table
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct Whether to select only distinct columns; deprecated: use Criteria->setDistinct() instead.
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return int Number of matching rows.
     */
    public static function doCountJoinAllExceptAuthyRelatedByIdModification(Criteria $criteria, $distinct = false, ?PropelPDO $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        // we're going to modify criteria, so copy it first
        $criteria = clone $criteria;

        // We need to set the primary table name, since in the case that there are no WHERE columns
        // it will be impossible for the BasePeer::createSelectSql() method to determine which
        // tables go into the FROM clause.
        $criteria->setPrimaryTableName(MarketSummaryPeer::TABLE_NAME);

        if ($distinct && !in_array(Criteria::DISTINCT, $criteria->getSelectModifiers())) {
            $criteria->setDistinct();
        }

        if (!$criteria->hasSelectClause()) {
            MarketSummaryPeer::addSelectColumns($criteria);
        }

        $criteria->clearOrderByColumns(); // ORDER BY should not affect count

        // Set the correct dbName
        $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME);

        if ($con === null) {
            $con = Propel::getConnection(MarketSummaryPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria->addJoin(MarketSummaryPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);

        $stmt = BasePeer::doCount($criteria, $con);

        if ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $count = (int) $row[0];
        } else {
            $count = 0; // no rows returned; we infer that means 0 matches.
        }
        $stmt->closeCursor();

        return $count;
    }


    /**
     * Selects a collection of MarketSummary objects pre-filled with all related objects except AuthyGroup.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of MarketSummary objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptAuthyGroup(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME);
        }

        MarketSummaryPeer::addSelectColumns($criteria);
        $startcol2 = MarketSummaryPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        AuthyPeer::addSelectColumns($criteria);
        $startcol4 = $startcol3 + AuthyPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(MarketSummaryPeer::ID_CREATION, AuthyPeer::ID_AUTHY, $join_behavior);

        $criteria->addJoin(MarketSummaryPeer::ID_MODIFICATION, AuthyPeer::ID_AUTHY, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = MarketSummaryPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = MarketSummaryPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = MarketSummaryPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                MarketSummaryPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined Authy rows

                $key2 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = AuthyPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = AuthyPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    AuthyPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (MarketSummary) to the collection in $obj2 (Authy)
                $obj2->addMarketSummaryRelatedByIdCreation($obj1);

            } // if joined row is not null

                // Add objects for joined Authy rows

                $key3 = AuthyPeer::getPrimaryKeyHashFromRow($row, $startcol3);
                if ($key3 !== null) {
                    $obj3 = AuthyPeer::getInstanceFromPool($key3);
                    if (!$obj3) {

                        $cls = AuthyPeer::getOMClass();

                    $obj3 = new $cls();
                    $obj3->hydrate($row, $startcol3);
                    AuthyPeer::addInstanceToPool($obj3, $key3);
                } // if $obj3 already loaded

                // Add the $obj1 (MarketSummary) to the collection in $obj3 (Authy)
                $obj3->addMarketSummaryRelatedByIdModification($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of MarketSummary objects pre-filled with all related objects except AuthyRelatedByIdCreation.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of MarketSummary objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptAuthyRelatedByIdCreation(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME);
        }

        MarketSummaryPeer::addSelectColumns($criteria);
        $startcol2 = MarketSummaryPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(MarketSummaryPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = MarketSummaryPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = MarketSummaryPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = MarketSummaryPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                MarketSummaryPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined AuthyGroup rows

                $key2 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = AuthyGroupPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = AuthyGroupPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    AuthyGroupPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (MarketSummary) to the collection in $obj2 (AuthyGroup)
                $obj2->addMarketSummary($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }


    /**
     * Selects a collection of MarketSummary objects pre-filled with all related objects except AuthyRelatedByIdModification.
     *
     * @param      Criteria  $criteria
     * @param      PropelPDO $con
     * @param      String    $join_behavior the type of joins to use, defaults to Criteria::LEFT_JOIN
     * @return array           Array of MarketSummary objects.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doSelectJoinAllExceptAuthyRelatedByIdModification(Criteria $criteria, $con = null, $join_behavior = Criteria::LEFT_JOIN)
    {
        $criteria = clone $criteria;

        // Set the correct dbName if it has not been overridden
        // $criteria->getDbName() will return the same object if not set to another value
        // so == check is okay and faster
        if ($criteria->getDbName() == Propel::getDefaultDB()) {
            $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME);
        }

        MarketSummaryPeer::addSelectColumns($criteria);
        $startcol2 = MarketSummaryPeer::NUM_HYDRATE_COLUMNS;

        AuthyGroupPeer::addSelectColumns($criteria);
        $startcol3 = $startcol2 + AuthyGroupPeer::NUM_HYDRATE_COLUMNS;

        $criteria->addJoin(MarketSummaryPeer::ID_GROUP_CREATION, AuthyGroupPeer::ID_AUTHY_GROUP, $join_behavior);


        $stmt = BasePeer::doSelect($criteria, $con);
        $results = array();

        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $key1 = MarketSummaryPeer::getPrimaryKeyHashFromRow($row, 0);
            if (null !== ($obj1 = MarketSummaryPeer::getInstanceFromPool($key1))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj1->hydrate($row, 0, true); // rehydrate
            } else {
                $cls = MarketSummaryPeer::getOMClass();

                $obj1 = new $cls();
                $obj1->hydrate($row);
                MarketSummaryPeer::addInstanceToPool($obj1, $key1);
            } // if obj1 already loaded

                // Add objects for joined AuthyGroup rows

                $key2 = AuthyGroupPeer::getPrimaryKeyHashFromRow($row, $startcol2);
                if ($key2 !== null) {
                    $obj2 = AuthyGroupPeer::getInstanceFromPool($key2);
                    if (!$obj2) {

                        $cls = AuthyGroupPeer::getOMClass();

                    $obj2 = new $cls();
                    $obj2->hydrate($row, $startcol2);
                    AuthyGroupPeer::addInstanceToPool($obj2, $key2);
                } // if $obj2 already loaded

                // Add the $obj1 (MarketSummary) to the collection in $obj2 (AuthyGroup)
                $obj2->addMarketSummary($obj1);

            } // if joined row is not null

            $results[] = $obj1;
        }
        $stmt->closeCursor();

        return $results;
    }

    /**
     * Returns the TableMap related to this peer.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getDatabaseMap(MarketSummaryPeer::DATABASE_NAME)->getTable(MarketSummaryPeer::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this peer class.
     */
    public static function buildTableMap()
    {
      $dbMap = Propel::getDatabaseMap(BaseMarketSummaryPeer::DATABASE_NAME);
      if (!$dbMap->hasTable(BaseMarketSummaryPeer::TABLE_NAME)) {
        $dbMap->addTableObject(new \App\map\MarketSummaryTableMap());
      }
    }

    /**
     * The class that the Peer will make instances of.
     *
     *
     * @return string ClassName
     */
    public static function getOMClass($row = 0, $colnum = 0)
    {
        return MarketSummaryPeer::OM_CLASS;
    }

    /**
     * Performs an INSERT on the database, given a MarketSummary or Criteria object.
     *
     * @param      mixed $values Criteria or MarketSummary object containing data that is used to create the INSERT statement.
     * @param      PropelPDO $con the PropelPDO connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doInsert($values, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(MarketSummaryPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity
        } else {
            $criteria = $values->buildCriteria(); // build Criteria from MarketSummary object
        }

        if ($criteria->containsKey(MarketSummaryPeer::ID_MARKET_SUMMARY) && $criteria->keyContainsValue(MarketSummaryPeer::ID_MARKET_SUMMARY) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.MarketSummaryPeer::ID_MARKET_SUMMARY.')');
        }


        // Set the correct dbName
        $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME);

        try {
            // use transaction because $criteria could contain info
            // for more than one table (I guess, conceivably)
            $con->beginTransaction();
            $pk = BasePeer::doInsert($criteria, $con);
            $con->commit();
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }

        return $pk;
    }

    /**
     * Performs an UPDATE on the database, given a MarketSummary or Criteria object.
     *
     * @param      mixed $values Criteria or MarketSummary object containing data that is used to create the UPDATE statement.
     * @param      PropelPDO $con The connection to use (specify PropelPDO connection object to exert more control over transactions).
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function doUpdate($values, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(MarketSummaryPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $selectCriteria = new Criteria(MarketSummaryPeer::DATABASE_NAME);

        if ($values instanceof Criteria) {
            $criteria = clone $values; // rename for clarity

            $comparison = $criteria->getComparison(MarketSummaryPeer::ID_MARKET_SUMMARY);
            $value = $criteria->remove(MarketSummaryPeer::ID_MARKET_SUMMARY);
            if ($value) {
                $selectCriteria->add(MarketSummaryPeer::ID_MARKET_SUMMARY, $value, $comparison);
            } else {
                $selectCriteria->setPrimaryTableName(MarketSummaryPeer::TABLE_NAME);
            }

        } else { // $values is MarketSummary object
            $criteria = $values->buildCriteria(); // gets full criteria
            $selectCriteria = $values->buildPkeyCriteria(); // gets criteria w/ primary key(s)
        }

        // set the correct dbName
        $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME);

        return BasePeer::doUpdate($selectCriteria, $criteria, $con);
    }

    /**
     * Deletes all rows from the market_summary table.
     *
     * @param      PropelPDO $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).
     * @throws PropelException
     */
    public static function doDeleteAll(?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(MarketSummaryPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += BasePeer::doDeleteAll(MarketSummaryPeer::TABLE_NAME, $con, MarketSummaryPeer::DATABASE_NAME);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            MarketSummaryPeer::clearInstancePool();
            MarketSummaryPeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Performs a DELETE on the database, given a MarketSummary or Criteria object OR a primary key value.
     *
     * @param      mixed $values Criteria or MarketSummary object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param      PropelPDO $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *				if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ?PropelPDO $con = null)
     {
        if ($con === null) {
            $con = Propel::getConnection(MarketSummaryPeer::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        if ($values instanceof Criteria) {
            // invalidate the cache for all objects of this type, since we have no
            // way of knowing (without running a query) what objects should be invalidated
            // from the cache based on this Criteria.
            MarketSummaryPeer::clearInstancePool();
            // rename for clarity
            $criteria = clone $values;
        } elseif ($values instanceof MarketSummary) { // it's a model object
            // invalidate the cache for this single object
            MarketSummaryPeer::removeInstanceFromPool($values);
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(MarketSummaryPeer::DATABASE_NAME);
            $criteria->add(MarketSummaryPeer::ID_MARKET_SUMMARY, (array) $values, Criteria::IN);
            // invalidate the cache for this object(s)
            foreach ((array) $values as $singleval) {
                MarketSummaryPeer::removeInstanceFromPool($singleval);
            }
        }

        // Set the correct dbName
        $criteria->setDbName(MarketSummaryPeer::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();

            $affectedRows += BasePeer::doDelete($criteria, $con);
            MarketSummaryPeer::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (Exception $e) {
            $con->rollBack();
            throw $e;
        }
    }

    /**
     * Validates all modified columns of given MarketSummary object.
     * If parameter $columns is either a single column name or an array of column names
     * than only those columns are validated.
     *
     * NOTICE: This does not apply to primary or foreign keys for now.
     *
     * @param MarketSummary $obj The object to validate.
     * @param      mixed $cols Column name or array of column names.
     *
     * @return mixed TRUE if all columns are valid or the error message of the first invalid column.
     */
    public static function doValidate($obj, $cols = null)
    {
        $columns = array();

        if ($cols) {
            $dbMap = Propel::getDatabaseMap(MarketSummaryPeer::DATABASE_NAME);
            $tableMap = $dbMap->getTable(MarketSummaryPeer::TABLE_NAME);

            if (! is_array($cols)) {
                $cols = array($cols);
            }

            foreach ($cols as $colName) {
                if ($tableMap->hasColumn($colName)) {
                    $get = 'get' . $tableMap->getColumn($colName)->getPhpName();
                    $columns[$colName] = $obj->$get();
                }
            }
        } else {

        if ($obj->isNew() || $obj->isColumnModified(MarketSummaryPeer::SYMBOL))
            $columns[MarketSummaryPeer::SYMBOL] = $obj->getSymbol();

        if ($obj->isNew() || $obj->isColumnModified(MarketSummaryPeer::SYMBOL))
            $columns[MarketSummaryPeer::SYMBOL] = $obj->getSymbol();

        if ($obj->isNew() || $obj->isColumnModified(MarketSummaryPeer::TF))
            $columns[MarketSummaryPeer::TF] = $obj->getTf();

        if ($obj->isNew() || $obj->isColumnModified(MarketSummaryPeer::TF))
            $columns[MarketSummaryPeer::TF] = $obj->getTf();

        if ($obj->isNew() || $obj->isColumnModified(MarketSummaryPeer::TREND))
            $columns[MarketSummaryPeer::TREND] = $obj->getTrend();

        if ($obj->isNew() || $obj->isColumnModified(MarketSummaryPeer::CANDLES_USED))
            $columns[MarketSummaryPeer::CANDLES_USED] = $obj->getCandlesUsed();

        if ($obj->isNew() || $obj->isColumnModified(MarketSummaryPeer::RECENT_CANDLES))
            $columns[MarketSummaryPeer::RECENT_CANDLES] = $obj->getRecentCandles();

        if ($obj->isNew() || $obj->isColumnModified(MarketSummaryPeer::COMPUTED_AT))
            $columns[MarketSummaryPeer::COMPUTED_AT] = $obj->getComputedAt();

        }

        return BasePeer::doValidate(MarketSummaryPeer::DATABASE_NAME, MarketSummaryPeer::TABLE_NAME, $columns);
    }

    /**
     * Retrieve a single object by pkey.
     *
     * @param int $pk the primary key.
     * @param      PropelPDO $con the connection to use
     * @return MarketSummary
     */
    public static function retrieveByPK($pk, ?PropelPDO $con = null)
    {

        if (null !== ($obj = MarketSummaryPeer::getInstanceFromPool((string) $pk))) {
            return $obj;
        }

        if ($con === null) {
            $con = Propel::getConnection(MarketSummaryPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $criteria = new Criteria(MarketSummaryPeer::DATABASE_NAME);
        $criteria->add(MarketSummaryPeer::ID_MARKET_SUMMARY, $pk);

        $v = MarketSummaryPeer::doSelect($criteria, $con);

        return !empty($v) > 0 ? $v[0] : null;
    }

    /**
     * Retrieve multiple objects by pkey.
     *
     * @param      array $pks List of primary keys
     * @param      PropelPDO $con the connection to use
     * @return MarketSummary[]
     * @throws PropelException Any exceptions caught during processing will be
     *		 rethrown wrapped into a PropelException.
     */
    public static function retrieveByPKs($pks, ?PropelPDO $con = null)
    {
        if ($con === null) {
            $con = Propel::getConnection(MarketSummaryPeer::DATABASE_NAME, Propel::CONNECTION_READ);
        }

        $objs = null;
        if (empty($pks)) {
            $objs = array();
        } else {
            $criteria = new Criteria(MarketSummaryPeer::DATABASE_NAME);
            $criteria->add(MarketSummaryPeer::ID_MARKET_SUMMARY, $pks, Criteria::IN);
            $objs = MarketSummaryPeer::doSelect($criteria, $con);
        }

        return $objs;
    }

} // BaseMarketSummaryPeer

// This is the static code needed to register the TableMap for this table with the main Propel class.
//
BaseMarketSummaryPeer::buildTableMap();

