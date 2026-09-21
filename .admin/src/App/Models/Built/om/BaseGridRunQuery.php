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
use App\Authy;
use App\AuthyGroup;
use App\BotCommand;
use App\BotDecision;
use App\BotEvent;
use App\BotOrder;
use App\FleetSlot;
use App\GridRun;
use App\GridRunAudit;
use App\GridRunPeer;
use App\GridRunQuery;
use App\RegimeEpisode;
use App\TradeCycle;

/**
 * Base class that represents a query for the 'grid_run' table.
 *
 * Grid Run
 *
 * @method GridRunQuery orderByIdGridRun($order = Criteria::ASC) Order by the id_grid_run column
 * @method GridRunQuery orderByLabel($order = Criteria::ASC) Order by the label column
 * @method GridRunQuery orderBySymbol($order = Criteria::ASC) Order by the symbol column
 * @method GridRunQuery orderByStatus($order = Criteria::ASC) Order by the status column
 * @method GridRunQuery orderByKillSwitch($order = Criteria::ASC) Order by the kill_switch column
 * @method GridRunQuery orderByProfile($order = Criteria::ASC) Order by the profile column
 * @method GridRunQuery orderByAlgo($order = Criteria::ASC) Order by the algo column
 * @method GridRunQuery orderBySimulated($order = Criteria::ASC) Order by the simulated column
 * @method GridRunQuery orderByPLow($order = Criteria::ASC) Order by the p_low column
 * @method GridRunQuery orderByPHigh($order = Criteria::ASC) Order by the p_high column
 * @method GridRunQuery orderByNLevels($order = Criteria::ASC) Order by the n_levels column
 * @method GridRunQuery orderBySpacing($order = Criteria::ASC) Order by the spacing column
 * @method GridRunQuery orderByAllocation($order = Criteria::ASC) Order by the allocation column
 * @method GridRunQuery orderByBudgetQuote($order = Criteria::ASC) Order by the budget_quote column
 * @method GridRunQuery orderByDeployPct($order = Criteria::ASC) Order by the deploy_pct column
 * @method GridRunQuery orderByAllocMode($order = Criteria::ASC) Order by the alloc_mode column
 * @method GridRunQuery orderByFeePct($order = Criteria::ASC) Order by the fee_pct column
 * @method GridRunQuery orderByMaxPositionQuote($order = Criteria::ASC) Order by the max_position_quote column
 * @method GridRunQuery orderByMaxOrderQuote($order = Criteria::ASC) Order by the max_order_quote column
 * @method GridRunQuery orderByDailyLossLimitQuote($order = Criteria::ASC) Order by the daily_loss_limit_quote column
 * @method GridRunQuery orderByMaxUnrealizedLossQuote($order = Criteria::ASC) Order by the max_unrealized_loss_quote column
 * @method GridRunQuery orderBySellAtLoss($order = Criteria::ASC) Order by the sell_at_loss column
 * @method GridRunQuery orderBySellWhenStarved($order = Criteria::ASC) Order by the sell_when_starved column
 * @method GridRunQuery orderByBreakoutBufferPct($order = Criteria::ASC) Order by the breakout_buffer_pct column
 * @method GridRunQuery orderByBreakoutPolicy($order = Criteria::ASC) Order by the breakout_policy column
 * @method GridRunQuery orderByMaxOpenOrders($order = Criteria::ASC) Order by the max_open_orders column
 * @method GridRunQuery orderByMaxBuyLevelsBelow($order = Criteria::ASC) Order by the max_buy_levels_below column
 * @method GridRunQuery orderByTrendTf($order = Criteria::ASC) Order by the trend_tf column
 * @method GridRunQuery orderByDonchianPeriod($order = Criteria::ASC) Order by the donchian_period column
 * @method GridRunQuery orderByTrendEmaFast($order = Criteria::ASC) Order by the trend_ema_fast column
 * @method GridRunQuery orderByTrendEmaSlow($order = Criteria::ASC) Order by the trend_ema_slow column
 * @method GridRunQuery orderByAtrPeriod($order = Criteria::ASC) Order by the atr_period column
 * @method GridRunQuery orderByAtrStopMult($order = Criteria::ASC) Order by the atr_stop_mult column
 * @method GridRunQuery orderByAtrInitialMult($order = Criteria::ASC) Order by the atr_initial_mult column
 * @method GridRunQuery orderByTrendStopFloorPct($order = Criteria::ASC) Order by the trend_stop_floor_pct column
 * @method GridRunQuery orderByTrendSignal($order = Criteria::ASC) Order by the trend_signal column
 * @method GridRunQuery orderByReentryCooldown($order = Criteria::ASC) Order by the reentry_cooldown column
 * @method GridRunQuery orderByEngineState($order = Criteria::ASC) Order by the engine_state column
 * @method GridRunQuery orderByLastTickAt($order = Criteria::ASC) Order by the last_tick_at column
 * @method GridRunQuery orderByLastPrice($order = Criteria::ASC) Order by the last_price column
 * @method GridRunQuery orderByBalBase($order = Criteria::ASC) Order by the bal_base column
 * @method GridRunQuery orderByBalQuote($order = Criteria::ASC) Order by the bal_quote column
 * @method GridRunQuery orderBySimBalBase($order = Criteria::ASC) Order by the sim_bal_base column
 * @method GridRunQuery orderBySimBalQuote($order = Criteria::ASC) Order by the sim_bal_quote column
 * @method GridRunQuery orderByRunUid($order = Criteria::ASC) Order by the run_uid column
 * @method GridRunQuery orderByAppliedGeometry($order = Criteria::ASC) Order by the applied_geometry column
 * @method GridRunQuery orderByLedgerResetAt($order = Criteria::ASC) Order by the ledger_reset_at column
 * @method GridRunQuery orderByDateCreation($order = Criteria::ASC) Order by the date_creation column
 * @method GridRunQuery orderByDateModification($order = Criteria::ASC) Order by the date_modification column
 * @method GridRunQuery orderByIdGroupCreation($order = Criteria::ASC) Order by the id_group_creation column
 * @method GridRunQuery orderByIdCreation($order = Criteria::ASC) Order by the id_creation column
 * @method GridRunQuery orderByIdModification($order = Criteria::ASC) Order by the id_modification column
 *
 * @method GridRunQuery groupByIdGridRun() Group by the id_grid_run column
 * @method GridRunQuery groupByLabel() Group by the label column
 * @method GridRunQuery groupBySymbol() Group by the symbol column
 * @method GridRunQuery groupByStatus() Group by the status column
 * @method GridRunQuery groupByKillSwitch() Group by the kill_switch column
 * @method GridRunQuery groupByProfile() Group by the profile column
 * @method GridRunQuery groupByAlgo() Group by the algo column
 * @method GridRunQuery groupBySimulated() Group by the simulated column
 * @method GridRunQuery groupByPLow() Group by the p_low column
 * @method GridRunQuery groupByPHigh() Group by the p_high column
 * @method GridRunQuery groupByNLevels() Group by the n_levels column
 * @method GridRunQuery groupBySpacing() Group by the spacing column
 * @method GridRunQuery groupByAllocation() Group by the allocation column
 * @method GridRunQuery groupByBudgetQuote() Group by the budget_quote column
 * @method GridRunQuery groupByDeployPct() Group by the deploy_pct column
 * @method GridRunQuery groupByAllocMode() Group by the alloc_mode column
 * @method GridRunQuery groupByFeePct() Group by the fee_pct column
 * @method GridRunQuery groupByMaxPositionQuote() Group by the max_position_quote column
 * @method GridRunQuery groupByMaxOrderQuote() Group by the max_order_quote column
 * @method GridRunQuery groupByDailyLossLimitQuote() Group by the daily_loss_limit_quote column
 * @method GridRunQuery groupByMaxUnrealizedLossQuote() Group by the max_unrealized_loss_quote column
 * @method GridRunQuery groupBySellAtLoss() Group by the sell_at_loss column
 * @method GridRunQuery groupBySellWhenStarved() Group by the sell_when_starved column
 * @method GridRunQuery groupByBreakoutBufferPct() Group by the breakout_buffer_pct column
 * @method GridRunQuery groupByBreakoutPolicy() Group by the breakout_policy column
 * @method GridRunQuery groupByMaxOpenOrders() Group by the max_open_orders column
 * @method GridRunQuery groupByMaxBuyLevelsBelow() Group by the max_buy_levels_below column
 * @method GridRunQuery groupByTrendTf() Group by the trend_tf column
 * @method GridRunQuery groupByDonchianPeriod() Group by the donchian_period column
 * @method GridRunQuery groupByTrendEmaFast() Group by the trend_ema_fast column
 * @method GridRunQuery groupByTrendEmaSlow() Group by the trend_ema_slow column
 * @method GridRunQuery groupByAtrPeriod() Group by the atr_period column
 * @method GridRunQuery groupByAtrStopMult() Group by the atr_stop_mult column
 * @method GridRunQuery groupByAtrInitialMult() Group by the atr_initial_mult column
 * @method GridRunQuery groupByTrendStopFloorPct() Group by the trend_stop_floor_pct column
 * @method GridRunQuery groupByTrendSignal() Group by the trend_signal column
 * @method GridRunQuery groupByReentryCooldown() Group by the reentry_cooldown column
 * @method GridRunQuery groupByEngineState() Group by the engine_state column
 * @method GridRunQuery groupByLastTickAt() Group by the last_tick_at column
 * @method GridRunQuery groupByLastPrice() Group by the last_price column
 * @method GridRunQuery groupByBalBase() Group by the bal_base column
 * @method GridRunQuery groupByBalQuote() Group by the bal_quote column
 * @method GridRunQuery groupBySimBalBase() Group by the sim_bal_base column
 * @method GridRunQuery groupBySimBalQuote() Group by the sim_bal_quote column
 * @method GridRunQuery groupByRunUid() Group by the run_uid column
 * @method GridRunQuery groupByAppliedGeometry() Group by the applied_geometry column
 * @method GridRunQuery groupByLedgerResetAt() Group by the ledger_reset_at column
 * @method GridRunQuery groupByDateCreation() Group by the date_creation column
 * @method GridRunQuery groupByDateModification() Group by the date_modification column
 * @method GridRunQuery groupByIdGroupCreation() Group by the id_group_creation column
 * @method GridRunQuery groupByIdCreation() Group by the id_creation column
 * @method GridRunQuery groupByIdModification() Group by the id_modification column
 *
 * @method GridRunQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method GridRunQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method GridRunQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method GridRunQuery leftJoinAuthyGroup($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyGroup relation
 * @method GridRunQuery rightJoinAuthyGroup($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyGroup relation
 * @method GridRunQuery innerJoinAuthyGroup($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyGroup relation
 *
 * @method GridRunQuery leftJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method GridRunQuery rightJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdCreation relation
 * @method GridRunQuery innerJoinAuthyRelatedByIdCreation($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdCreation relation
 *
 * @method GridRunQuery leftJoinAuthyRelatedByIdModification($relationAlias = null) Adds a LEFT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method GridRunQuery rightJoinAuthyRelatedByIdModification($relationAlias = null) Adds a RIGHT JOIN clause to the query using the AuthyRelatedByIdModification relation
 * @method GridRunQuery innerJoinAuthyRelatedByIdModification($relationAlias = null) Adds a INNER JOIN clause to the query using the AuthyRelatedByIdModification relation
 *
 * @method GridRunQuery leftJoinFleetSlot($relationAlias = null) Adds a LEFT JOIN clause to the query using the FleetSlot relation
 * @method GridRunQuery rightJoinFleetSlot($relationAlias = null) Adds a RIGHT JOIN clause to the query using the FleetSlot relation
 * @method GridRunQuery innerJoinFleetSlot($relationAlias = null) Adds a INNER JOIN clause to the query using the FleetSlot relation
 *
 * @method GridRunQuery leftJoinRegimeEpisode($relationAlias = null) Adds a LEFT JOIN clause to the query using the RegimeEpisode relation
 * @method GridRunQuery rightJoinRegimeEpisode($relationAlias = null) Adds a RIGHT JOIN clause to the query using the RegimeEpisode relation
 * @method GridRunQuery innerJoinRegimeEpisode($relationAlias = null) Adds a INNER JOIN clause to the query using the RegimeEpisode relation
 *
 * @method GridRunQuery leftJoinBotOrder($relationAlias = null) Adds a LEFT JOIN clause to the query using the BotOrder relation
 * @method GridRunQuery rightJoinBotOrder($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BotOrder relation
 * @method GridRunQuery innerJoinBotOrder($relationAlias = null) Adds a INNER JOIN clause to the query using the BotOrder relation
 *
 * @method GridRunQuery leftJoinTradeCycle($relationAlias = null) Adds a LEFT JOIN clause to the query using the TradeCycle relation
 * @method GridRunQuery rightJoinTradeCycle($relationAlias = null) Adds a RIGHT JOIN clause to the query using the TradeCycle relation
 * @method GridRunQuery innerJoinTradeCycle($relationAlias = null) Adds a INNER JOIN clause to the query using the TradeCycle relation
 *
 * @method GridRunQuery leftJoinBotEvent($relationAlias = null) Adds a LEFT JOIN clause to the query using the BotEvent relation
 * @method GridRunQuery rightJoinBotEvent($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BotEvent relation
 * @method GridRunQuery innerJoinBotEvent($relationAlias = null) Adds a INNER JOIN clause to the query using the BotEvent relation
 *
 * @method GridRunQuery leftJoinBotCommand($relationAlias = null) Adds a LEFT JOIN clause to the query using the BotCommand relation
 * @method GridRunQuery rightJoinBotCommand($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BotCommand relation
 * @method GridRunQuery innerJoinBotCommand($relationAlias = null) Adds a INNER JOIN clause to the query using the BotCommand relation
 *
 * @method GridRunQuery leftJoinBotDecision($relationAlias = null) Adds a LEFT JOIN clause to the query using the BotDecision relation
 * @method GridRunQuery rightJoinBotDecision($relationAlias = null) Adds a RIGHT JOIN clause to the query using the BotDecision relation
 * @method GridRunQuery innerJoinBotDecision($relationAlias = null) Adds a INNER JOIN clause to the query using the BotDecision relation
 *
 * @method GridRunQuery leftJoinGridRunAudit($relationAlias = null) Adds a LEFT JOIN clause to the query using the GridRunAudit relation
 * @method GridRunQuery rightJoinGridRunAudit($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GridRunAudit relation
 * @method GridRunQuery innerJoinGridRunAudit($relationAlias = null) Adds a INNER JOIN clause to the query using the GridRunAudit relation
 *
 * @method GridRun findOne(?PropelPDO $con = null) Return the first GridRun matching the query
 * @method GridRun findOneOrCreate(?PropelPDO $con = null) Return the first GridRun matching the query, or a new GridRun object populated from the query conditions when no match is found
 *
 * @method GridRun findOneByLabel(string $label) Return the first GridRun filtered by the label column
 * @method GridRun findOneBySymbol(string $symbol) Return the first GridRun filtered by the symbol column
 * @method GridRun findOneByStatus(int $status) Return the first GridRun filtered by the status column
 * @method GridRun findOneByKillSwitch(boolean $kill_switch) Return the first GridRun filtered by the kill_switch column
 * @method GridRun findOneByProfile(int $profile) Return the first GridRun filtered by the profile column
 * @method GridRun findOneByAlgo(int $algo) Return the first GridRun filtered by the algo column
 * @method GridRun findOneBySimulated(boolean $simulated) Return the first GridRun filtered by the simulated column
 * @method GridRun findOneByPLow(string $p_low) Return the first GridRun filtered by the p_low column
 * @method GridRun findOneByPHigh(string $p_high) Return the first GridRun filtered by the p_high column
 * @method GridRun findOneByNLevels(int $n_levels) Return the first GridRun filtered by the n_levels column
 * @method GridRun findOneBySpacing(int $spacing) Return the first GridRun filtered by the spacing column
 * @method GridRun findOneByAllocation(int $allocation) Return the first GridRun filtered by the allocation column
 * @method GridRun findOneByBudgetQuote(string $budget_quote) Return the first GridRun filtered by the budget_quote column
 * @method GridRun findOneByDeployPct(int $deploy_pct) Return the first GridRun filtered by the deploy_pct column
 * @method GridRun findOneByAllocMode(int $alloc_mode) Return the first GridRun filtered by the alloc_mode column
 * @method GridRun findOneByFeePct(string $fee_pct) Return the first GridRun filtered by the fee_pct column
 * @method GridRun findOneByMaxPositionQuote(string $max_position_quote) Return the first GridRun filtered by the max_position_quote column
 * @method GridRun findOneByMaxOrderQuote(string $max_order_quote) Return the first GridRun filtered by the max_order_quote column
 * @method GridRun findOneByDailyLossLimitQuote(string $daily_loss_limit_quote) Return the first GridRun filtered by the daily_loss_limit_quote column
 * @method GridRun findOneByMaxUnrealizedLossQuote(string $max_unrealized_loss_quote) Return the first GridRun filtered by the max_unrealized_loss_quote column
 * @method GridRun findOneBySellAtLoss(boolean $sell_at_loss) Return the first GridRun filtered by the sell_at_loss column
 * @method GridRun findOneBySellWhenStarved(boolean $sell_when_starved) Return the first GridRun filtered by the sell_when_starved column
 * @method GridRun findOneByBreakoutBufferPct(string $breakout_buffer_pct) Return the first GridRun filtered by the breakout_buffer_pct column
 * @method GridRun findOneByBreakoutPolicy(int $breakout_policy) Return the first GridRun filtered by the breakout_policy column
 * @method GridRun findOneByMaxOpenOrders(int $max_open_orders) Return the first GridRun filtered by the max_open_orders column
 * @method GridRun findOneByMaxBuyLevelsBelow(int $max_buy_levels_below) Return the first GridRun filtered by the max_buy_levels_below column
 * @method GridRun findOneByTrendTf(int $trend_tf) Return the first GridRun filtered by the trend_tf column
 * @method GridRun findOneByDonchianPeriod(int $donchian_period) Return the first GridRun filtered by the donchian_period column
 * @method GridRun findOneByTrendEmaFast(int $trend_ema_fast) Return the first GridRun filtered by the trend_ema_fast column
 * @method GridRun findOneByTrendEmaSlow(int $trend_ema_slow) Return the first GridRun filtered by the trend_ema_slow column
 * @method GridRun findOneByAtrPeriod(int $atr_period) Return the first GridRun filtered by the atr_period column
 * @method GridRun findOneByAtrStopMult(string $atr_stop_mult) Return the first GridRun filtered by the atr_stop_mult column
 * @method GridRun findOneByAtrInitialMult(string $atr_initial_mult) Return the first GridRun filtered by the atr_initial_mult column
 * @method GridRun findOneByTrendStopFloorPct(string $trend_stop_floor_pct) Return the first GridRun filtered by the trend_stop_floor_pct column
 * @method GridRun findOneByTrendSignal(int $trend_signal) Return the first GridRun filtered by the trend_signal column
 * @method GridRun findOneByReentryCooldown(int $reentry_cooldown) Return the first GridRun filtered by the reentry_cooldown column
 * @method GridRun findOneByEngineState(string $engine_state) Return the first GridRun filtered by the engine_state column
 * @method GridRun findOneByLastTickAt(string $last_tick_at) Return the first GridRun filtered by the last_tick_at column
 * @method GridRun findOneByLastPrice(string $last_price) Return the first GridRun filtered by the last_price column
 * @method GridRun findOneByBalBase(string $bal_base) Return the first GridRun filtered by the bal_base column
 * @method GridRun findOneByBalQuote(string $bal_quote) Return the first GridRun filtered by the bal_quote column
 * @method GridRun findOneBySimBalBase(string $sim_bal_base) Return the first GridRun filtered by the sim_bal_base column
 * @method GridRun findOneBySimBalQuote(string $sim_bal_quote) Return the first GridRun filtered by the sim_bal_quote column
 * @method GridRun findOneByRunUid(string $run_uid) Return the first GridRun filtered by the run_uid column
 * @method GridRun findOneByAppliedGeometry(string $applied_geometry) Return the first GridRun filtered by the applied_geometry column
 * @method GridRun findOneByLedgerResetAt(string $ledger_reset_at) Return the first GridRun filtered by the ledger_reset_at column
 * @method GridRun findOneByDateCreation(string $date_creation) Return the first GridRun filtered by the date_creation column
 * @method GridRun findOneByDateModification(string $date_modification) Return the first GridRun filtered by the date_modification column
 * @method GridRun findOneByIdGroupCreation(int $id_group_creation) Return the first GridRun filtered by the id_group_creation column
 * @method GridRun findOneByIdCreation(int $id_creation) Return the first GridRun filtered by the id_creation column
 * @method GridRun findOneByIdModification(int $id_modification) Return the first GridRun filtered by the id_modification column
 *
 * @method array findByIdGridRun(int $id_grid_run) Return GridRun objects filtered by the id_grid_run column
 * @method array findByLabel(string $label) Return GridRun objects filtered by the label column
 * @method array findBySymbol(string $symbol) Return GridRun objects filtered by the symbol column
 * @method array findByStatus(int $status) Return GridRun objects filtered by the status column
 * @method array findByKillSwitch(boolean $kill_switch) Return GridRun objects filtered by the kill_switch column
 * @method array findByProfile(int $profile) Return GridRun objects filtered by the profile column
 * @method array findByAlgo(int $algo) Return GridRun objects filtered by the algo column
 * @method array findBySimulated(boolean $simulated) Return GridRun objects filtered by the simulated column
 * @method array findByPLow(string $p_low) Return GridRun objects filtered by the p_low column
 * @method array findByPHigh(string $p_high) Return GridRun objects filtered by the p_high column
 * @method array findByNLevels(int $n_levels) Return GridRun objects filtered by the n_levels column
 * @method array findBySpacing(int $spacing) Return GridRun objects filtered by the spacing column
 * @method array findByAllocation(int $allocation) Return GridRun objects filtered by the allocation column
 * @method array findByBudgetQuote(string $budget_quote) Return GridRun objects filtered by the budget_quote column
 * @method array findByDeployPct(int $deploy_pct) Return GridRun objects filtered by the deploy_pct column
 * @method array findByAllocMode(int $alloc_mode) Return GridRun objects filtered by the alloc_mode column
 * @method array findByFeePct(string $fee_pct) Return GridRun objects filtered by the fee_pct column
 * @method array findByMaxPositionQuote(string $max_position_quote) Return GridRun objects filtered by the max_position_quote column
 * @method array findByMaxOrderQuote(string $max_order_quote) Return GridRun objects filtered by the max_order_quote column
 * @method array findByDailyLossLimitQuote(string $daily_loss_limit_quote) Return GridRun objects filtered by the daily_loss_limit_quote column
 * @method array findByMaxUnrealizedLossQuote(string $max_unrealized_loss_quote) Return GridRun objects filtered by the max_unrealized_loss_quote column
 * @method array findBySellAtLoss(boolean $sell_at_loss) Return GridRun objects filtered by the sell_at_loss column
 * @method array findBySellWhenStarved(boolean $sell_when_starved) Return GridRun objects filtered by the sell_when_starved column
 * @method array findByBreakoutBufferPct(string $breakout_buffer_pct) Return GridRun objects filtered by the breakout_buffer_pct column
 * @method array findByBreakoutPolicy(int $breakout_policy) Return GridRun objects filtered by the breakout_policy column
 * @method array findByMaxOpenOrders(int $max_open_orders) Return GridRun objects filtered by the max_open_orders column
 * @method array findByMaxBuyLevelsBelow(int $max_buy_levels_below) Return GridRun objects filtered by the max_buy_levels_below column
 * @method array findByTrendTf(int $trend_tf) Return GridRun objects filtered by the trend_tf column
 * @method array findByDonchianPeriod(int $donchian_period) Return GridRun objects filtered by the donchian_period column
 * @method array findByTrendEmaFast(int $trend_ema_fast) Return GridRun objects filtered by the trend_ema_fast column
 * @method array findByTrendEmaSlow(int $trend_ema_slow) Return GridRun objects filtered by the trend_ema_slow column
 * @method array findByAtrPeriod(int $atr_period) Return GridRun objects filtered by the atr_period column
 * @method array findByAtrStopMult(string $atr_stop_mult) Return GridRun objects filtered by the atr_stop_mult column
 * @method array findByAtrInitialMult(string $atr_initial_mult) Return GridRun objects filtered by the atr_initial_mult column
 * @method array findByTrendStopFloorPct(string $trend_stop_floor_pct) Return GridRun objects filtered by the trend_stop_floor_pct column
 * @method array findByTrendSignal(int $trend_signal) Return GridRun objects filtered by the trend_signal column
 * @method array findByReentryCooldown(int $reentry_cooldown) Return GridRun objects filtered by the reentry_cooldown column
 * @method array findByEngineState(string $engine_state) Return GridRun objects filtered by the engine_state column
 * @method array findByLastTickAt(string $last_tick_at) Return GridRun objects filtered by the last_tick_at column
 * @method array findByLastPrice(string $last_price) Return GridRun objects filtered by the last_price column
 * @method array findByBalBase(string $bal_base) Return GridRun objects filtered by the bal_base column
 * @method array findByBalQuote(string $bal_quote) Return GridRun objects filtered by the bal_quote column
 * @method array findBySimBalBase(string $sim_bal_base) Return GridRun objects filtered by the sim_bal_base column
 * @method array findBySimBalQuote(string $sim_bal_quote) Return GridRun objects filtered by the sim_bal_quote column
 * @method array findByRunUid(string $run_uid) Return GridRun objects filtered by the run_uid column
 * @method array findByAppliedGeometry(string $applied_geometry) Return GridRun objects filtered by the applied_geometry column
 * @method array findByLedgerResetAt(string $ledger_reset_at) Return GridRun objects filtered by the ledger_reset_at column
 * @method array findByDateCreation(string $date_creation) Return GridRun objects filtered by the date_creation column
 * @method array findByDateModification(string $date_modification) Return GridRun objects filtered by the date_modification column
 * @method array findByIdGroupCreation(int $id_group_creation) Return GridRun objects filtered by the id_group_creation column
 * @method array findByIdCreation(int $id_creation) Return GridRun objects filtered by the id_creation column
 * @method array findByIdModification(int $id_modification) Return GridRun objects filtered by the id_modification column
 *
 * @package    propel.generator..om
 */
abstract class BaseGridRunQuery extends ModelCriteria
{
    /**
     * Initializes internal state of BaseGridRunQuery object.
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
            $modelName = 'App\\GridRun';
        }
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new GridRunQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param   GridRunQuery|Criteria $criteria Optional Criteria to build the query from
     *
     * @return GridRunQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof GridRunQuery) {
            return $criteria;
        }
        $query = new GridRunQuery(null, null, $modelAlias);

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
     * @return   GridRun|GridRun[]|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = GridRunPeer::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getConnection(GridRunPeer::DATABASE_NAME, Propel::CONNECTION_READ);
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
     * @return                 GridRun A model object, or null if the key is not found
     * @throws PropelException
     */
     public function findOneByIdGridRun($key, $con = null)
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
     * @return                 GridRun A model object, or null if the key is not found
     * @throws PropelException
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT `id_grid_run`, `label`, `symbol`, `status`, `kill_switch`, `profile`, `algo`, `simulated`, `p_low`, `p_high`, `n_levels`, `spacing`, `allocation`, `budget_quote`, `deploy_pct`, `alloc_mode`, `fee_pct`, `max_position_quote`, `max_order_quote`, `daily_loss_limit_quote`, `max_unrealized_loss_quote`, `sell_at_loss`, `sell_when_starved`, `breakout_buffer_pct`, `breakout_policy`, `max_open_orders`, `max_buy_levels_below`, `trend_tf`, `donchian_period`, `trend_ema_fast`, `trend_ema_slow`, `atr_period`, `atr_stop_mult`, `atr_initial_mult`, `trend_stop_floor_pct`, `trend_signal`, `reentry_cooldown`, `engine_state`, `last_tick_at`, `last_price`, `bal_base`, `bal_quote`, `sim_bal_base`, `sim_bal_quote`, `run_uid`, `applied_geometry`, `ledger_reset_at`, `date_creation`, `date_modification`, `id_group_creation`, `id_creation`, `id_modification` FROM `grid_run` WHERE `id_grid_run` = :p0';
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
            $obj = new GridRun();
            $obj->hydrate($row);
            GridRunPeer::addInstanceToPool($obj, (string) $key);
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
     * @return GridRun|GridRun[]|mixed the result, formatted by the current formatter
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
     * @return PropelObjectCollection|GridRun[]|mixed the list of results, formatted by the current formatter
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
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(GridRunPeer::ID_GRID_RUN, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(GridRunPeer::ID_GRID_RUN, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id_grid_run column
     *
     * Example usage:
     * <code>
     * $query->filterByIdGridRun(1234); // WHERE id_grid_run = 1234
     * $query->filterByIdGridRun(array(12, 34)); // WHERE id_grid_run IN (12, 34)
     * $query->filterByIdGridRun(array('min' => 12)); // WHERE id_grid_run >= 12
     * $query->filterByIdGridRun(array('max' => 12)); // WHERE id_grid_run <= 12
     * </code>
     *
     * @param     mixed $idGridRun The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByIdGridRun($idGridRun = null, $comparison = null)
    {
        if (is_array($idGridRun)) {
            $useMinMax = false;
            if (isset($idGridRun['min'])) {
                $this->addUsingAlias(GridRunPeer::ID_GRID_RUN, $idGridRun['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGridRun['max'])) {
                $this->addUsingAlias(GridRunPeer::ID_GRID_RUN, $idGridRun['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::ID_GRID_RUN, $idGridRun, $comparison);
    }

    /**
     * Filter the query on the label column
     *
     * Example usage:
     * <code>
     * $query->filterByLabel('fooValue');   // WHERE label = 'fooValue'
     * $query->filterByLabel('%fooValue%'); // WHERE label LIKE '%fooValue%'
     * </code>
     *
     * @param     string $label The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByLabel($label = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($label)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $label)) {
                $label = str_replace('*', '%', $label);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GridRunPeer::LABEL, $label, $comparison);
    }

    /**
     * Filter the query on the symbol column
     *
     * Example usage:
     * <code>
     * $query->filterBySymbol('fooValue');   // WHERE symbol = 'fooValue'
     * $query->filterBySymbol('%fooValue%'); // WHERE symbol LIKE '%fooValue%'
     * </code>
     *
     * @param     string $symbol The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterBySymbol($symbol = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($symbol)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $symbol)) {
                $symbol = str_replace('*', '%', $symbol);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GridRunPeer::SYMBOL, $symbol, $comparison);
    }

    /**
     * Filter the query on the status column
     *
     * @param     mixed $status The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByStatus($status = null, $comparison = null)
    {
        if (is_scalar($status)) {
            $status = GridRunPeer::getSqlValueForEnum(GridRunPeer::STATUS, $status);
        } elseif (is_array($status)) {
            $convertedValues = array();
            foreach ($status as $value) {
                $convertedValues[] = GridRunPeer::getSqlValueForEnum(GridRunPeer::STATUS, $value);
            }
            $status = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::STATUS, $status, $comparison);
    }

    /**
     * Filter the query on the kill_switch column
     *
     * Example usage:
     * <code>
     * $query->filterByKillSwitch(true); // WHERE kill_switch = true
     * $query->filterByKillSwitch('yes'); // WHERE kill_switch = true
     * </code>
     *
     * @param     boolean|string $killSwitch The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByKillSwitch($killSwitch = null, $comparison = null)
    {
        if (is_string($killSwitch)) {
            $killSwitch = in_array(strtolower($killSwitch), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(GridRunPeer::KILL_SWITCH, $killSwitch, $comparison);
    }

    /**
     * Filter the query on the profile column
     *
     * @param     mixed $profile The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByProfile($profile = null, $comparison = null)
    {
        if (is_scalar($profile)) {
            $profile = GridRunPeer::getSqlValueForEnum(GridRunPeer::PROFILE, $profile);
        } elseif (is_array($profile)) {
            $convertedValues = array();
            foreach ($profile as $value) {
                $convertedValues[] = GridRunPeer::getSqlValueForEnum(GridRunPeer::PROFILE, $value);
            }
            $profile = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::PROFILE, $profile, $comparison);
    }

    /**
     * Filter the query on the algo column
     *
     * @param     mixed $algo The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByAlgo($algo = null, $comparison = null)
    {
        if (is_scalar($algo)) {
            $algo = GridRunPeer::getSqlValueForEnum(GridRunPeer::ALGO, $algo);
        } elseif (is_array($algo)) {
            $convertedValues = array();
            foreach ($algo as $value) {
                $convertedValues[] = GridRunPeer::getSqlValueForEnum(GridRunPeer::ALGO, $value);
            }
            $algo = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::ALGO, $algo, $comparison);
    }

    /**
     * Filter the query on the simulated column
     *
     * Example usage:
     * <code>
     * $query->filterBySimulated(true); // WHERE simulated = true
     * $query->filterBySimulated('yes'); // WHERE simulated = true
     * </code>
     *
     * @param     boolean|string $simulated The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterBySimulated($simulated = null, $comparison = null)
    {
        if (is_string($simulated)) {
            $simulated = in_array(strtolower($simulated), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(GridRunPeer::SIMULATED, $simulated, $comparison);
    }

    /**
     * Filter the query on the p_low column
     *
     * Example usage:
     * <code>
     * $query->filterByPLow(1234); // WHERE p_low = 1234
     * $query->filterByPLow(array(12, 34)); // WHERE p_low IN (12, 34)
     * $query->filterByPLow(array('min' => 12)); // WHERE p_low >= 12
     * $query->filterByPLow(array('max' => 12)); // WHERE p_low <= 12
     * </code>
     *
     * @param     mixed $pLow The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByPLow($pLow = null, $comparison = null)
    {
        if (is_array($pLow)) {
            $useMinMax = false;
            if (isset($pLow['min'])) {
                $this->addUsingAlias(GridRunPeer::P_LOW, $pLow['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($pLow['max'])) {
                $this->addUsingAlias(GridRunPeer::P_LOW, $pLow['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::P_LOW, $pLow, $comparison);
    }

    /**
     * Filter the query on the p_high column
     *
     * Example usage:
     * <code>
     * $query->filterByPHigh(1234); // WHERE p_high = 1234
     * $query->filterByPHigh(array(12, 34)); // WHERE p_high IN (12, 34)
     * $query->filterByPHigh(array('min' => 12)); // WHERE p_high >= 12
     * $query->filterByPHigh(array('max' => 12)); // WHERE p_high <= 12
     * </code>
     *
     * @param     mixed $pHigh The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByPHigh($pHigh = null, $comparison = null)
    {
        if (is_array($pHigh)) {
            $useMinMax = false;
            if (isset($pHigh['min'])) {
                $this->addUsingAlias(GridRunPeer::P_HIGH, $pHigh['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($pHigh['max'])) {
                $this->addUsingAlias(GridRunPeer::P_HIGH, $pHigh['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::P_HIGH, $pHigh, $comparison);
    }

    /**
     * Filter the query on the n_levels column
     *
     * Example usage:
     * <code>
     * $query->filterByNLevels(1234); // WHERE n_levels = 1234
     * $query->filterByNLevels(array(12, 34)); // WHERE n_levels IN (12, 34)
     * $query->filterByNLevels(array('min' => 12)); // WHERE n_levels >= 12
     * $query->filterByNLevels(array('max' => 12)); // WHERE n_levels <= 12
     * </code>
     *
     * @param     mixed $nLevels The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByNLevels($nLevels = null, $comparison = null)
    {
        if (is_array($nLevels)) {
            $useMinMax = false;
            if (isset($nLevels['min'])) {
                $this->addUsingAlias(GridRunPeer::N_LEVELS, $nLevels['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($nLevels['max'])) {
                $this->addUsingAlias(GridRunPeer::N_LEVELS, $nLevels['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::N_LEVELS, $nLevels, $comparison);
    }

    /**
     * Filter the query on the spacing column
     *
     * @param     mixed $spacing The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterBySpacing($spacing = null, $comparison = null)
    {
        if (is_scalar($spacing)) {
            $spacing = GridRunPeer::getSqlValueForEnum(GridRunPeer::SPACING, $spacing);
        } elseif (is_array($spacing)) {
            $convertedValues = array();
            foreach ($spacing as $value) {
                $convertedValues[] = GridRunPeer::getSqlValueForEnum(GridRunPeer::SPACING, $value);
            }
            $spacing = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::SPACING, $spacing, $comparison);
    }

    /**
     * Filter the query on the allocation column
     *
     * @param     mixed $allocation The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByAllocation($allocation = null, $comparison = null)
    {
        if (is_scalar($allocation)) {
            $allocation = GridRunPeer::getSqlValueForEnum(GridRunPeer::ALLOCATION, $allocation);
        } elseif (is_array($allocation)) {
            $convertedValues = array();
            foreach ($allocation as $value) {
                $convertedValues[] = GridRunPeer::getSqlValueForEnum(GridRunPeer::ALLOCATION, $value);
            }
            $allocation = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::ALLOCATION, $allocation, $comparison);
    }

    /**
     * Filter the query on the budget_quote column
     *
     * Example usage:
     * <code>
     * $query->filterByBudgetQuote(1234); // WHERE budget_quote = 1234
     * $query->filterByBudgetQuote(array(12, 34)); // WHERE budget_quote IN (12, 34)
     * $query->filterByBudgetQuote(array('min' => 12)); // WHERE budget_quote >= 12
     * $query->filterByBudgetQuote(array('max' => 12)); // WHERE budget_quote <= 12
     * </code>
     *
     * @param     mixed $budgetQuote The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByBudgetQuote($budgetQuote = null, $comparison = null)
    {
        if (is_array($budgetQuote)) {
            $useMinMax = false;
            if (isset($budgetQuote['min'])) {
                $this->addUsingAlias(GridRunPeer::BUDGET_QUOTE, $budgetQuote['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($budgetQuote['max'])) {
                $this->addUsingAlias(GridRunPeer::BUDGET_QUOTE, $budgetQuote['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::BUDGET_QUOTE, $budgetQuote, $comparison);
    }

    /**
     * Filter the query on the deploy_pct column
     *
     * Example usage:
     * <code>
     * $query->filterByDeployPct(1234); // WHERE deploy_pct = 1234
     * $query->filterByDeployPct(array(12, 34)); // WHERE deploy_pct IN (12, 34)
     * $query->filterByDeployPct(array('min' => 12)); // WHERE deploy_pct >= 12
     * $query->filterByDeployPct(array('max' => 12)); // WHERE deploy_pct <= 12
     * </code>
     *
     * @param     mixed $deployPct The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByDeployPct($deployPct = null, $comparison = null)
    {
        if (is_array($deployPct)) {
            $useMinMax = false;
            if (isset($deployPct['min'])) {
                $this->addUsingAlias(GridRunPeer::DEPLOY_PCT, $deployPct['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($deployPct['max'])) {
                $this->addUsingAlias(GridRunPeer::DEPLOY_PCT, $deployPct['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::DEPLOY_PCT, $deployPct, $comparison);
    }

    /**
     * Filter the query on the alloc_mode column
     *
     * @param     mixed $allocMode The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByAllocMode($allocMode = null, $comparison = null)
    {
        if (is_scalar($allocMode)) {
            $allocMode = GridRunPeer::getSqlValueForEnum(GridRunPeer::ALLOC_MODE, $allocMode);
        } elseif (is_array($allocMode)) {
            $convertedValues = array();
            foreach ($allocMode as $value) {
                $convertedValues[] = GridRunPeer::getSqlValueForEnum(GridRunPeer::ALLOC_MODE, $value);
            }
            $allocMode = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::ALLOC_MODE, $allocMode, $comparison);
    }

    /**
     * Filter the query on the fee_pct column
     *
     * Example usage:
     * <code>
     * $query->filterByFeePct(1234); // WHERE fee_pct = 1234
     * $query->filterByFeePct(array(12, 34)); // WHERE fee_pct IN (12, 34)
     * $query->filterByFeePct(array('min' => 12)); // WHERE fee_pct >= 12
     * $query->filterByFeePct(array('max' => 12)); // WHERE fee_pct <= 12
     * </code>
     *
     * @param     mixed $feePct The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByFeePct($feePct = null, $comparison = null)
    {
        if (is_array($feePct)) {
            $useMinMax = false;
            if (isset($feePct['min'])) {
                $this->addUsingAlias(GridRunPeer::FEE_PCT, $feePct['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($feePct['max'])) {
                $this->addUsingAlias(GridRunPeer::FEE_PCT, $feePct['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::FEE_PCT, $feePct, $comparison);
    }

    /**
     * Filter the query on the max_position_quote column
     *
     * Example usage:
     * <code>
     * $query->filterByMaxPositionQuote(1234); // WHERE max_position_quote = 1234
     * $query->filterByMaxPositionQuote(array(12, 34)); // WHERE max_position_quote IN (12, 34)
     * $query->filterByMaxPositionQuote(array('min' => 12)); // WHERE max_position_quote >= 12
     * $query->filterByMaxPositionQuote(array('max' => 12)); // WHERE max_position_quote <= 12
     * </code>
     *
     * @param     mixed $maxPositionQuote The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByMaxPositionQuote($maxPositionQuote = null, $comparison = null)
    {
        if (is_array($maxPositionQuote)) {
            $useMinMax = false;
            if (isset($maxPositionQuote['min'])) {
                $this->addUsingAlias(GridRunPeer::MAX_POSITION_QUOTE, $maxPositionQuote['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($maxPositionQuote['max'])) {
                $this->addUsingAlias(GridRunPeer::MAX_POSITION_QUOTE, $maxPositionQuote['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::MAX_POSITION_QUOTE, $maxPositionQuote, $comparison);
    }

    /**
     * Filter the query on the max_order_quote column
     *
     * Example usage:
     * <code>
     * $query->filterByMaxOrderQuote(1234); // WHERE max_order_quote = 1234
     * $query->filterByMaxOrderQuote(array(12, 34)); // WHERE max_order_quote IN (12, 34)
     * $query->filterByMaxOrderQuote(array('min' => 12)); // WHERE max_order_quote >= 12
     * $query->filterByMaxOrderQuote(array('max' => 12)); // WHERE max_order_quote <= 12
     * </code>
     *
     * @param     mixed $maxOrderQuote The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByMaxOrderQuote($maxOrderQuote = null, $comparison = null)
    {
        if (is_array($maxOrderQuote)) {
            $useMinMax = false;
            if (isset($maxOrderQuote['min'])) {
                $this->addUsingAlias(GridRunPeer::MAX_ORDER_QUOTE, $maxOrderQuote['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($maxOrderQuote['max'])) {
                $this->addUsingAlias(GridRunPeer::MAX_ORDER_QUOTE, $maxOrderQuote['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::MAX_ORDER_QUOTE, $maxOrderQuote, $comparison);
    }

    /**
     * Filter the query on the daily_loss_limit_quote column
     *
     * Example usage:
     * <code>
     * $query->filterByDailyLossLimitQuote(1234); // WHERE daily_loss_limit_quote = 1234
     * $query->filterByDailyLossLimitQuote(array(12, 34)); // WHERE daily_loss_limit_quote IN (12, 34)
     * $query->filterByDailyLossLimitQuote(array('min' => 12)); // WHERE daily_loss_limit_quote >= 12
     * $query->filterByDailyLossLimitQuote(array('max' => 12)); // WHERE daily_loss_limit_quote <= 12
     * </code>
     *
     * @param     mixed $dailyLossLimitQuote The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByDailyLossLimitQuote($dailyLossLimitQuote = null, $comparison = null)
    {
        if (is_array($dailyLossLimitQuote)) {
            $useMinMax = false;
            if (isset($dailyLossLimitQuote['min'])) {
                $this->addUsingAlias(GridRunPeer::DAILY_LOSS_LIMIT_QUOTE, $dailyLossLimitQuote['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dailyLossLimitQuote['max'])) {
                $this->addUsingAlias(GridRunPeer::DAILY_LOSS_LIMIT_QUOTE, $dailyLossLimitQuote['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::DAILY_LOSS_LIMIT_QUOTE, $dailyLossLimitQuote, $comparison);
    }

    /**
     * Filter the query on the max_unrealized_loss_quote column
     *
     * Example usage:
     * <code>
     * $query->filterByMaxUnrealizedLossQuote(1234); // WHERE max_unrealized_loss_quote = 1234
     * $query->filterByMaxUnrealizedLossQuote(array(12, 34)); // WHERE max_unrealized_loss_quote IN (12, 34)
     * $query->filterByMaxUnrealizedLossQuote(array('min' => 12)); // WHERE max_unrealized_loss_quote >= 12
     * $query->filterByMaxUnrealizedLossQuote(array('max' => 12)); // WHERE max_unrealized_loss_quote <= 12
     * </code>
     *
     * @param     mixed $maxUnrealizedLossQuote The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByMaxUnrealizedLossQuote($maxUnrealizedLossQuote = null, $comparison = null)
    {
        if (is_array($maxUnrealizedLossQuote)) {
            $useMinMax = false;
            if (isset($maxUnrealizedLossQuote['min'])) {
                $this->addUsingAlias(GridRunPeer::MAX_UNREALIZED_LOSS_QUOTE, $maxUnrealizedLossQuote['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($maxUnrealizedLossQuote['max'])) {
                $this->addUsingAlias(GridRunPeer::MAX_UNREALIZED_LOSS_QUOTE, $maxUnrealizedLossQuote['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::MAX_UNREALIZED_LOSS_QUOTE, $maxUnrealizedLossQuote, $comparison);
    }

    /**
     * Filter the query on the sell_at_loss column
     *
     * Example usage:
     * <code>
     * $query->filterBySellAtLoss(true); // WHERE sell_at_loss = true
     * $query->filterBySellAtLoss('yes'); // WHERE sell_at_loss = true
     * </code>
     *
     * @param     boolean|string $sellAtLoss The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterBySellAtLoss($sellAtLoss = null, $comparison = null)
    {
        if (is_string($sellAtLoss)) {
            $sellAtLoss = in_array(strtolower($sellAtLoss), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(GridRunPeer::SELL_AT_LOSS, $sellAtLoss, $comparison);
    }

    /**
     * Filter the query on the sell_when_starved column
     *
     * Example usage:
     * <code>
     * $query->filterBySellWhenStarved(true); // WHERE sell_when_starved = true
     * $query->filterBySellWhenStarved('yes'); // WHERE sell_when_starved = true
     * </code>
     *
     * @param     boolean|string $sellWhenStarved The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterBySellWhenStarved($sellWhenStarved = null, $comparison = null)
    {
        if (is_string($sellWhenStarved)) {
            $sellWhenStarved = in_array(strtolower($sellWhenStarved), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(GridRunPeer::SELL_WHEN_STARVED, $sellWhenStarved, $comparison);
    }

    /**
     * Filter the query on the breakout_buffer_pct column
     *
     * Example usage:
     * <code>
     * $query->filterByBreakoutBufferPct(1234); // WHERE breakout_buffer_pct = 1234
     * $query->filterByBreakoutBufferPct(array(12, 34)); // WHERE breakout_buffer_pct IN (12, 34)
     * $query->filterByBreakoutBufferPct(array('min' => 12)); // WHERE breakout_buffer_pct >= 12
     * $query->filterByBreakoutBufferPct(array('max' => 12)); // WHERE breakout_buffer_pct <= 12
     * </code>
     *
     * @param     mixed $breakoutBufferPct The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByBreakoutBufferPct($breakoutBufferPct = null, $comparison = null)
    {
        if (is_array($breakoutBufferPct)) {
            $useMinMax = false;
            if (isset($breakoutBufferPct['min'])) {
                $this->addUsingAlias(GridRunPeer::BREAKOUT_BUFFER_PCT, $breakoutBufferPct['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($breakoutBufferPct['max'])) {
                $this->addUsingAlias(GridRunPeer::BREAKOUT_BUFFER_PCT, $breakoutBufferPct['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::BREAKOUT_BUFFER_PCT, $breakoutBufferPct, $comparison);
    }

    /**
     * Filter the query on the breakout_policy column
     *
     * @param     mixed $breakoutPolicy The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByBreakoutPolicy($breakoutPolicy = null, $comparison = null)
    {
        if (is_scalar($breakoutPolicy)) {
            $breakoutPolicy = GridRunPeer::getSqlValueForEnum(GridRunPeer::BREAKOUT_POLICY, $breakoutPolicy);
        } elseif (is_array($breakoutPolicy)) {
            $convertedValues = array();
            foreach ($breakoutPolicy as $value) {
                $convertedValues[] = GridRunPeer::getSqlValueForEnum(GridRunPeer::BREAKOUT_POLICY, $value);
            }
            $breakoutPolicy = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::BREAKOUT_POLICY, $breakoutPolicy, $comparison);
    }

    /**
     * Filter the query on the max_open_orders column
     *
     * Example usage:
     * <code>
     * $query->filterByMaxOpenOrders(1234); // WHERE max_open_orders = 1234
     * $query->filterByMaxOpenOrders(array(12, 34)); // WHERE max_open_orders IN (12, 34)
     * $query->filterByMaxOpenOrders(array('min' => 12)); // WHERE max_open_orders >= 12
     * $query->filterByMaxOpenOrders(array('max' => 12)); // WHERE max_open_orders <= 12
     * </code>
     *
     * @param     mixed $maxOpenOrders The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByMaxOpenOrders($maxOpenOrders = null, $comparison = null)
    {
        if (is_array($maxOpenOrders)) {
            $useMinMax = false;
            if (isset($maxOpenOrders['min'])) {
                $this->addUsingAlias(GridRunPeer::MAX_OPEN_ORDERS, $maxOpenOrders['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($maxOpenOrders['max'])) {
                $this->addUsingAlias(GridRunPeer::MAX_OPEN_ORDERS, $maxOpenOrders['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::MAX_OPEN_ORDERS, $maxOpenOrders, $comparison);
    }

    /**
     * Filter the query on the max_buy_levels_below column
     *
     * Example usage:
     * <code>
     * $query->filterByMaxBuyLevelsBelow(1234); // WHERE max_buy_levels_below = 1234
     * $query->filterByMaxBuyLevelsBelow(array(12, 34)); // WHERE max_buy_levels_below IN (12, 34)
     * $query->filterByMaxBuyLevelsBelow(array('min' => 12)); // WHERE max_buy_levels_below >= 12
     * $query->filterByMaxBuyLevelsBelow(array('max' => 12)); // WHERE max_buy_levels_below <= 12
     * </code>
     *
     * @param     mixed $maxBuyLevelsBelow The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByMaxBuyLevelsBelow($maxBuyLevelsBelow = null, $comparison = null)
    {
        if (is_array($maxBuyLevelsBelow)) {
            $useMinMax = false;
            if (isset($maxBuyLevelsBelow['min'])) {
                $this->addUsingAlias(GridRunPeer::MAX_BUY_LEVELS_BELOW, $maxBuyLevelsBelow['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($maxBuyLevelsBelow['max'])) {
                $this->addUsingAlias(GridRunPeer::MAX_BUY_LEVELS_BELOW, $maxBuyLevelsBelow['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::MAX_BUY_LEVELS_BELOW, $maxBuyLevelsBelow, $comparison);
    }

    /**
     * Filter the query on the trend_tf column
     *
     * @param     mixed $trendTf The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByTrendTf($trendTf = null, $comparison = null)
    {
        if (is_scalar($trendTf)) {
            $trendTf = GridRunPeer::getSqlValueForEnum(GridRunPeer::TREND_TF, $trendTf);
        } elseif (is_array($trendTf)) {
            $convertedValues = array();
            foreach ($trendTf as $value) {
                $convertedValues[] = GridRunPeer::getSqlValueForEnum(GridRunPeer::TREND_TF, $value);
            }
            $trendTf = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::TREND_TF, $trendTf, $comparison);
    }

    /**
     * Filter the query on the donchian_period column
     *
     * Example usage:
     * <code>
     * $query->filterByDonchianPeriod(1234); // WHERE donchian_period = 1234
     * $query->filterByDonchianPeriod(array(12, 34)); // WHERE donchian_period IN (12, 34)
     * $query->filterByDonchianPeriod(array('min' => 12)); // WHERE donchian_period >= 12
     * $query->filterByDonchianPeriod(array('max' => 12)); // WHERE donchian_period <= 12
     * </code>
     *
     * @param     mixed $donchianPeriod The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByDonchianPeriod($donchianPeriod = null, $comparison = null)
    {
        if (is_array($donchianPeriod)) {
            $useMinMax = false;
            if (isset($donchianPeriod['min'])) {
                $this->addUsingAlias(GridRunPeer::DONCHIAN_PERIOD, $donchianPeriod['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($donchianPeriod['max'])) {
                $this->addUsingAlias(GridRunPeer::DONCHIAN_PERIOD, $donchianPeriod['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::DONCHIAN_PERIOD, $donchianPeriod, $comparison);
    }

    /**
     * Filter the query on the trend_ema_fast column
     *
     * Example usage:
     * <code>
     * $query->filterByTrendEmaFast(1234); // WHERE trend_ema_fast = 1234
     * $query->filterByTrendEmaFast(array(12, 34)); // WHERE trend_ema_fast IN (12, 34)
     * $query->filterByTrendEmaFast(array('min' => 12)); // WHERE trend_ema_fast >= 12
     * $query->filterByTrendEmaFast(array('max' => 12)); // WHERE trend_ema_fast <= 12
     * </code>
     *
     * @param     mixed $trendEmaFast The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByTrendEmaFast($trendEmaFast = null, $comparison = null)
    {
        if (is_array($trendEmaFast)) {
            $useMinMax = false;
            if (isset($trendEmaFast['min'])) {
                $this->addUsingAlias(GridRunPeer::TREND_EMA_FAST, $trendEmaFast['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($trendEmaFast['max'])) {
                $this->addUsingAlias(GridRunPeer::TREND_EMA_FAST, $trendEmaFast['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::TREND_EMA_FAST, $trendEmaFast, $comparison);
    }

    /**
     * Filter the query on the trend_ema_slow column
     *
     * Example usage:
     * <code>
     * $query->filterByTrendEmaSlow(1234); // WHERE trend_ema_slow = 1234
     * $query->filterByTrendEmaSlow(array(12, 34)); // WHERE trend_ema_slow IN (12, 34)
     * $query->filterByTrendEmaSlow(array('min' => 12)); // WHERE trend_ema_slow >= 12
     * $query->filterByTrendEmaSlow(array('max' => 12)); // WHERE trend_ema_slow <= 12
     * </code>
     *
     * @param     mixed $trendEmaSlow The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByTrendEmaSlow($trendEmaSlow = null, $comparison = null)
    {
        if (is_array($trendEmaSlow)) {
            $useMinMax = false;
            if (isset($trendEmaSlow['min'])) {
                $this->addUsingAlias(GridRunPeer::TREND_EMA_SLOW, $trendEmaSlow['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($trendEmaSlow['max'])) {
                $this->addUsingAlias(GridRunPeer::TREND_EMA_SLOW, $trendEmaSlow['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::TREND_EMA_SLOW, $trendEmaSlow, $comparison);
    }

    /**
     * Filter the query on the atr_period column
     *
     * Example usage:
     * <code>
     * $query->filterByAtrPeriod(1234); // WHERE atr_period = 1234
     * $query->filterByAtrPeriod(array(12, 34)); // WHERE atr_period IN (12, 34)
     * $query->filterByAtrPeriod(array('min' => 12)); // WHERE atr_period >= 12
     * $query->filterByAtrPeriod(array('max' => 12)); // WHERE atr_period <= 12
     * </code>
     *
     * @param     mixed $atrPeriod The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByAtrPeriod($atrPeriod = null, $comparison = null)
    {
        if (is_array($atrPeriod)) {
            $useMinMax = false;
            if (isset($atrPeriod['min'])) {
                $this->addUsingAlias(GridRunPeer::ATR_PERIOD, $atrPeriod['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($atrPeriod['max'])) {
                $this->addUsingAlias(GridRunPeer::ATR_PERIOD, $atrPeriod['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::ATR_PERIOD, $atrPeriod, $comparison);
    }

    /**
     * Filter the query on the atr_stop_mult column
     *
     * Example usage:
     * <code>
     * $query->filterByAtrStopMult(1234); // WHERE atr_stop_mult = 1234
     * $query->filterByAtrStopMult(array(12, 34)); // WHERE atr_stop_mult IN (12, 34)
     * $query->filterByAtrStopMult(array('min' => 12)); // WHERE atr_stop_mult >= 12
     * $query->filterByAtrStopMult(array('max' => 12)); // WHERE atr_stop_mult <= 12
     * </code>
     *
     * @param     mixed $atrStopMult The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByAtrStopMult($atrStopMult = null, $comparison = null)
    {
        if (is_array($atrStopMult)) {
            $useMinMax = false;
            if (isset($atrStopMult['min'])) {
                $this->addUsingAlias(GridRunPeer::ATR_STOP_MULT, $atrStopMult['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($atrStopMult['max'])) {
                $this->addUsingAlias(GridRunPeer::ATR_STOP_MULT, $atrStopMult['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::ATR_STOP_MULT, $atrStopMult, $comparison);
    }

    /**
     * Filter the query on the atr_initial_mult column
     *
     * Example usage:
     * <code>
     * $query->filterByAtrInitialMult(1234); // WHERE atr_initial_mult = 1234
     * $query->filterByAtrInitialMult(array(12, 34)); // WHERE atr_initial_mult IN (12, 34)
     * $query->filterByAtrInitialMult(array('min' => 12)); // WHERE atr_initial_mult >= 12
     * $query->filterByAtrInitialMult(array('max' => 12)); // WHERE atr_initial_mult <= 12
     * </code>
     *
     * @param     mixed $atrInitialMult The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByAtrInitialMult($atrInitialMult = null, $comparison = null)
    {
        if (is_array($atrInitialMult)) {
            $useMinMax = false;
            if (isset($atrInitialMult['min'])) {
                $this->addUsingAlias(GridRunPeer::ATR_INITIAL_MULT, $atrInitialMult['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($atrInitialMult['max'])) {
                $this->addUsingAlias(GridRunPeer::ATR_INITIAL_MULT, $atrInitialMult['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::ATR_INITIAL_MULT, $atrInitialMult, $comparison);
    }

    /**
     * Filter the query on the trend_stop_floor_pct column
     *
     * Example usage:
     * <code>
     * $query->filterByTrendStopFloorPct(1234); // WHERE trend_stop_floor_pct = 1234
     * $query->filterByTrendStopFloorPct(array(12, 34)); // WHERE trend_stop_floor_pct IN (12, 34)
     * $query->filterByTrendStopFloorPct(array('min' => 12)); // WHERE trend_stop_floor_pct >= 12
     * $query->filterByTrendStopFloorPct(array('max' => 12)); // WHERE trend_stop_floor_pct <= 12
     * </code>
     *
     * @param     mixed $trendStopFloorPct The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByTrendStopFloorPct($trendStopFloorPct = null, $comparison = null)
    {
        if (is_array($trendStopFloorPct)) {
            $useMinMax = false;
            if (isset($trendStopFloorPct['min'])) {
                $this->addUsingAlias(GridRunPeer::TREND_STOP_FLOOR_PCT, $trendStopFloorPct['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($trendStopFloorPct['max'])) {
                $this->addUsingAlias(GridRunPeer::TREND_STOP_FLOOR_PCT, $trendStopFloorPct['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::TREND_STOP_FLOOR_PCT, $trendStopFloorPct, $comparison);
    }

    /**
     * Filter the query on the trend_signal column
     *
     * @param     mixed $trendSignal The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the value is not accepted by the enum.
     */
    public function filterByTrendSignal($trendSignal = null, $comparison = null)
    {
        if (is_scalar($trendSignal)) {
            $trendSignal = GridRunPeer::getSqlValueForEnum(GridRunPeer::TREND_SIGNAL, $trendSignal);
        } elseif (is_array($trendSignal)) {
            $convertedValues = array();
            foreach ($trendSignal as $value) {
                $convertedValues[] = GridRunPeer::getSqlValueForEnum(GridRunPeer::TREND_SIGNAL, $value);
            }
            $trendSignal = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::TREND_SIGNAL, $trendSignal, $comparison);
    }

    /**
     * Filter the query on the reentry_cooldown column
     *
     * Example usage:
     * <code>
     * $query->filterByReentryCooldown(1234); // WHERE reentry_cooldown = 1234
     * $query->filterByReentryCooldown(array(12, 34)); // WHERE reentry_cooldown IN (12, 34)
     * $query->filterByReentryCooldown(array('min' => 12)); // WHERE reentry_cooldown >= 12
     * $query->filterByReentryCooldown(array('max' => 12)); // WHERE reentry_cooldown <= 12
     * </code>
     *
     * @param     mixed $reentryCooldown The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByReentryCooldown($reentryCooldown = null, $comparison = null)
    {
        if (is_array($reentryCooldown)) {
            $useMinMax = false;
            if (isset($reentryCooldown['min'])) {
                $this->addUsingAlias(GridRunPeer::REENTRY_COOLDOWN, $reentryCooldown['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($reentryCooldown['max'])) {
                $this->addUsingAlias(GridRunPeer::REENTRY_COOLDOWN, $reentryCooldown['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::REENTRY_COOLDOWN, $reentryCooldown, $comparison);
    }

    /**
     * Filter the query on the engine_state column
     *
     * Example usage:
     * <code>
     * $query->filterByEngineState('fooValue');   // WHERE engine_state = 'fooValue'
     * $query->filterByEngineState('%fooValue%'); // WHERE engine_state LIKE '%fooValue%'
     * </code>
     *
     * @param     string $engineState The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByEngineState($engineState = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($engineState)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $engineState)) {
                $engineState = str_replace('*', '%', $engineState);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GridRunPeer::ENGINE_STATE, $engineState, $comparison);
    }

    /**
     * Filter the query on the last_tick_at column
     *
     * Example usage:
     * <code>
     * $query->filterByLastTickAt('2011-03-14'); // WHERE last_tick_at = '2011-03-14'
     * $query->filterByLastTickAt('now'); // WHERE last_tick_at = '2011-03-14'
     * $query->filterByLastTickAt(array('max' => 'yesterday')); // WHERE last_tick_at < '2011-03-13'
     * </code>
     *
     * @param     mixed $lastTickAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByLastTickAt($lastTickAt = null, $comparison = null)
    {
        if (is_array($lastTickAt)) {
            $useMinMax = false;
            if (isset($lastTickAt['min'])) {
                $this->addUsingAlias(GridRunPeer::LAST_TICK_AT, $lastTickAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($lastTickAt['max'])) {
                $this->addUsingAlias(GridRunPeer::LAST_TICK_AT, $lastTickAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::LAST_TICK_AT, $lastTickAt, $comparison);
    }

    /**
     * Filter the query on the last_price column
     *
     * Example usage:
     * <code>
     * $query->filterByLastPrice(1234); // WHERE last_price = 1234
     * $query->filterByLastPrice(array(12, 34)); // WHERE last_price IN (12, 34)
     * $query->filterByLastPrice(array('min' => 12)); // WHERE last_price >= 12
     * $query->filterByLastPrice(array('max' => 12)); // WHERE last_price <= 12
     * </code>
     *
     * @param     mixed $lastPrice The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByLastPrice($lastPrice = null, $comparison = null)
    {
        if (is_array($lastPrice)) {
            $useMinMax = false;
            if (isset($lastPrice['min'])) {
                $this->addUsingAlias(GridRunPeer::LAST_PRICE, $lastPrice['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($lastPrice['max'])) {
                $this->addUsingAlias(GridRunPeer::LAST_PRICE, $lastPrice['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::LAST_PRICE, $lastPrice, $comparison);
    }

    /**
     * Filter the query on the bal_base column
     *
     * Example usage:
     * <code>
     * $query->filterByBalBase(1234); // WHERE bal_base = 1234
     * $query->filterByBalBase(array(12, 34)); // WHERE bal_base IN (12, 34)
     * $query->filterByBalBase(array('min' => 12)); // WHERE bal_base >= 12
     * $query->filterByBalBase(array('max' => 12)); // WHERE bal_base <= 12
     * </code>
     *
     * @param     mixed $balBase The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByBalBase($balBase = null, $comparison = null)
    {
        if (is_array($balBase)) {
            $useMinMax = false;
            if (isset($balBase['min'])) {
                $this->addUsingAlias(GridRunPeer::BAL_BASE, $balBase['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($balBase['max'])) {
                $this->addUsingAlias(GridRunPeer::BAL_BASE, $balBase['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::BAL_BASE, $balBase, $comparison);
    }

    /**
     * Filter the query on the bal_quote column
     *
     * Example usage:
     * <code>
     * $query->filterByBalQuote(1234); // WHERE bal_quote = 1234
     * $query->filterByBalQuote(array(12, 34)); // WHERE bal_quote IN (12, 34)
     * $query->filterByBalQuote(array('min' => 12)); // WHERE bal_quote >= 12
     * $query->filterByBalQuote(array('max' => 12)); // WHERE bal_quote <= 12
     * </code>
     *
     * @param     mixed $balQuote The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByBalQuote($balQuote = null, $comparison = null)
    {
        if (is_array($balQuote)) {
            $useMinMax = false;
            if (isset($balQuote['min'])) {
                $this->addUsingAlias(GridRunPeer::BAL_QUOTE, $balQuote['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($balQuote['max'])) {
                $this->addUsingAlias(GridRunPeer::BAL_QUOTE, $balQuote['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::BAL_QUOTE, $balQuote, $comparison);
    }

    /**
     * Filter the query on the sim_bal_base column
     *
     * Example usage:
     * <code>
     * $query->filterBySimBalBase(1234); // WHERE sim_bal_base = 1234
     * $query->filterBySimBalBase(array(12, 34)); // WHERE sim_bal_base IN (12, 34)
     * $query->filterBySimBalBase(array('min' => 12)); // WHERE sim_bal_base >= 12
     * $query->filterBySimBalBase(array('max' => 12)); // WHERE sim_bal_base <= 12
     * </code>
     *
     * @param     mixed $simBalBase The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterBySimBalBase($simBalBase = null, $comparison = null)
    {
        if (is_array($simBalBase)) {
            $useMinMax = false;
            if (isset($simBalBase['min'])) {
                $this->addUsingAlias(GridRunPeer::SIM_BAL_BASE, $simBalBase['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($simBalBase['max'])) {
                $this->addUsingAlias(GridRunPeer::SIM_BAL_BASE, $simBalBase['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::SIM_BAL_BASE, $simBalBase, $comparison);
    }

    /**
     * Filter the query on the sim_bal_quote column
     *
     * Example usage:
     * <code>
     * $query->filterBySimBalQuote(1234); // WHERE sim_bal_quote = 1234
     * $query->filterBySimBalQuote(array(12, 34)); // WHERE sim_bal_quote IN (12, 34)
     * $query->filterBySimBalQuote(array('min' => 12)); // WHERE sim_bal_quote >= 12
     * $query->filterBySimBalQuote(array('max' => 12)); // WHERE sim_bal_quote <= 12
     * </code>
     *
     * @param     mixed $simBalQuote The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterBySimBalQuote($simBalQuote = null, $comparison = null)
    {
        if (is_array($simBalQuote)) {
            $useMinMax = false;
            if (isset($simBalQuote['min'])) {
                $this->addUsingAlias(GridRunPeer::SIM_BAL_QUOTE, $simBalQuote['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($simBalQuote['max'])) {
                $this->addUsingAlias(GridRunPeer::SIM_BAL_QUOTE, $simBalQuote['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::SIM_BAL_QUOTE, $simBalQuote, $comparison);
    }

    /**
     * Filter the query on the run_uid column
     *
     * Example usage:
     * <code>
     * $query->filterByRunUid('fooValue');   // WHERE run_uid = 'fooValue'
     * $query->filterByRunUid('%fooValue%'); // WHERE run_uid LIKE '%fooValue%'
     * </code>
     *
     * @param     string $runUid The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByRunUid($runUid = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($runUid)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $runUid)) {
                $runUid = str_replace('*', '%', $runUid);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GridRunPeer::RUN_UID, $runUid, $comparison);
    }

    /**
     * Filter the query on the applied_geometry column
     *
     * Example usage:
     * <code>
     * $query->filterByAppliedGeometry('fooValue');   // WHERE applied_geometry = 'fooValue'
     * $query->filterByAppliedGeometry('%fooValue%'); // WHERE applied_geometry LIKE '%fooValue%'
     * </code>
     *
     * @param     string $appliedGeometry The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByAppliedGeometry($appliedGeometry = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($appliedGeometry)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $appliedGeometry)) {
                $appliedGeometry = str_replace('*', '%', $appliedGeometry);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(GridRunPeer::APPLIED_GEOMETRY, $appliedGeometry, $comparison);
    }

    /**
     * Filter the query on the ledger_reset_at column
     *
     * Example usage:
     * <code>
     * $query->filterByLedgerResetAt('2011-03-14'); // WHERE ledger_reset_at = '2011-03-14'
     * $query->filterByLedgerResetAt('now'); // WHERE ledger_reset_at = '2011-03-14'
     * $query->filterByLedgerResetAt(array('max' => 'yesterday')); // WHERE ledger_reset_at < '2011-03-13'
     * </code>
     *
     * @param     mixed $ledgerResetAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByLedgerResetAt($ledgerResetAt = null, $comparison = null)
    {
        if (is_array($ledgerResetAt)) {
            $useMinMax = false;
            if (isset($ledgerResetAt['min'])) {
                $this->addUsingAlias(GridRunPeer::LEDGER_RESET_AT, $ledgerResetAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($ledgerResetAt['max'])) {
                $this->addUsingAlias(GridRunPeer::LEDGER_RESET_AT, $ledgerResetAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::LEDGER_RESET_AT, $ledgerResetAt, $comparison);
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
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByDateCreation($dateCreation = null, $comparison = null)
    {
        if (is_array($dateCreation)) {
            $useMinMax = false;
            if (isset($dateCreation['min'])) {
                $this->addUsingAlias(GridRunPeer::DATE_CREATION, $dateCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateCreation['max'])) {
                $this->addUsingAlias(GridRunPeer::DATE_CREATION, $dateCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::DATE_CREATION, $dateCreation, $comparison);
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
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByDateModification($dateModification = null, $comparison = null)
    {
        if (is_array($dateModification)) {
            $useMinMax = false;
            if (isset($dateModification['min'])) {
                $this->addUsingAlias(GridRunPeer::DATE_MODIFICATION, $dateModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dateModification['max'])) {
                $this->addUsingAlias(GridRunPeer::DATE_MODIFICATION, $dateModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::DATE_MODIFICATION, $dateModification, $comparison);
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
     * @see       filterByAuthyGroup()
     *
     * @param     mixed $idGroupCreation The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByIdGroupCreation($idGroupCreation = null, $comparison = null)
    {
        if (is_array($idGroupCreation)) {
            $useMinMax = false;
            if (isset($idGroupCreation['min'])) {
                $this->addUsingAlias(GridRunPeer::ID_GROUP_CREATION, $idGroupCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idGroupCreation['max'])) {
                $this->addUsingAlias(GridRunPeer::ID_GROUP_CREATION, $idGroupCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::ID_GROUP_CREATION, $idGroupCreation, $comparison);
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
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByIdCreation($idCreation = null, $comparison = null)
    {
        if (is_array($idCreation)) {
            $useMinMax = false;
            if (isset($idCreation['min'])) {
                $this->addUsingAlias(GridRunPeer::ID_CREATION, $idCreation['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idCreation['max'])) {
                $this->addUsingAlias(GridRunPeer::ID_CREATION, $idCreation['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::ID_CREATION, $idCreation, $comparison);
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
     * @return GridRunQuery The current query, for fluid interface
     */
    public function filterByIdModification($idModification = null, $comparison = null)
    {
        if (is_array($idModification)) {
            $useMinMax = false;
            if (isset($idModification['min'])) {
                $this->addUsingAlias(GridRunPeer::ID_MODIFICATION, $idModification['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($idModification['max'])) {
                $this->addUsingAlias(GridRunPeer::ID_MODIFICATION, $idModification['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GridRunPeer::ID_MODIFICATION, $idModification, $comparison);
    }

    /**
     * Filter the query by a related AuthyGroup object
     *
     * @param   AuthyGroup|PropelObjectCollection $authyGroup The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyGroup($authyGroup, $comparison = null)
    {
        if ($authyGroup instanceof AuthyGroup) {
            return $this
                ->addUsingAlias(GridRunPeer::ID_GROUP_CREATION, $authyGroup->getIdAuthyGroup(), $comparison);
        } elseif ($authyGroup instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(GridRunPeer::ID_GROUP_CREATION, $authyGroup->toKeyValue('PrimaryKey', 'IdAuthyGroup'), $comparison);
        } else {
            throw new PropelException('filterByAuthyGroup() only accepts arguments of type AuthyGroup or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the AuthyGroup relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function joinAuthyGroup($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('AuthyGroup');

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
            $this->addJoinObject($join, 'AuthyGroup');
        }

        return $this;
    }

    /**
     * Use the AuthyGroup relation AuthyGroup object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\AuthyGroupQuery A secondary query class using the current class as primary query
     */
    public function useAuthyGroupQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinAuthyGroup($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'AuthyGroup', '\App\AuthyGroupQuery');
    }

    /**
     * Filter the query by a related Authy object
     *
     * @param   Authy|PropelObjectCollection $authy The related object(s) to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdCreation($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(GridRunPeer::ID_CREATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(GridRunPeer::ID_CREATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return GridRunQuery The current query, for fluid interface
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
     * @return                 GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByAuthyRelatedByIdModification($authy, $comparison = null)
    {
        if ($authy instanceof Authy) {
            return $this
                ->addUsingAlias(GridRunPeer::ID_MODIFICATION, $authy->getIdAuthy(), $comparison);
        } elseif ($authy instanceof PropelObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(GridRunPeer::ID_MODIFICATION, $authy->toKeyValue('PrimaryKey', 'IdAuthy'), $comparison);
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
     * @return GridRunQuery The current query, for fluid interface
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
     * Filter the query by a related FleetSlot object
     *
     * @param   FleetSlot|PropelObjectCollection $fleetSlot  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByFleetSlot($fleetSlot, $comparison = null)
    {
        if ($fleetSlot instanceof FleetSlot) {
            return $this
                ->addUsingAlias(GridRunPeer::ID_GRID_RUN, $fleetSlot->getIdGridRun(), $comparison);
        } elseif ($fleetSlot instanceof PropelObjectCollection) {
            return $this
                ->useFleetSlotQuery()
                ->filterByPrimaryKeys($fleetSlot->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByFleetSlot() only accepts arguments of type FleetSlot or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the FleetSlot relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function joinFleetSlot($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('FleetSlot');

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
            $this->addJoinObject($join, 'FleetSlot');
        }

        return $this;
    }

    /**
     * Use the FleetSlot relation FleetSlot object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\FleetSlotQuery A secondary query class using the current class as primary query
     */
    public function useFleetSlotQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinFleetSlot($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'FleetSlot', '\App\FleetSlotQuery');
    }

    /**
     * Filter the query by a related RegimeEpisode object
     *
     * @param   RegimeEpisode|PropelObjectCollection $regimeEpisode  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByRegimeEpisode($regimeEpisode, $comparison = null)
    {
        if ($regimeEpisode instanceof RegimeEpisode) {
            return $this
                ->addUsingAlias(GridRunPeer::ID_GRID_RUN, $regimeEpisode->getIdGridRun(), $comparison);
        } elseif ($regimeEpisode instanceof PropelObjectCollection) {
            return $this
                ->useRegimeEpisodeQuery()
                ->filterByPrimaryKeys($regimeEpisode->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByRegimeEpisode() only accepts arguments of type RegimeEpisode or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the RegimeEpisode relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function joinRegimeEpisode($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('RegimeEpisode');

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
            $this->addJoinObject($join, 'RegimeEpisode');
        }

        return $this;
    }

    /**
     * Use the RegimeEpisode relation RegimeEpisode object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\RegimeEpisodeQuery A secondary query class using the current class as primary query
     */
    public function useRegimeEpisodeQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinRegimeEpisode($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'RegimeEpisode', '\App\RegimeEpisodeQuery');
    }

    /**
     * Filter the query by a related BotOrder object
     *
     * @param   BotOrder|PropelObjectCollection $botOrder  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByBotOrder($botOrder, $comparison = null)
    {
        if ($botOrder instanceof BotOrder) {
            return $this
                ->addUsingAlias(GridRunPeer::ID_GRID_RUN, $botOrder->getIdGridRun(), $comparison);
        } elseif ($botOrder instanceof PropelObjectCollection) {
            return $this
                ->useBotOrderQuery()
                ->filterByPrimaryKeys($botOrder->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByBotOrder() only accepts arguments of type BotOrder or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BotOrder relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function joinBotOrder($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BotOrder');

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
            $this->addJoinObject($join, 'BotOrder');
        }

        return $this;
    }

    /**
     * Use the BotOrder relation BotOrder object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\BotOrderQuery A secondary query class using the current class as primary query
     */
    public function useBotOrderQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinBotOrder($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BotOrder', '\App\BotOrderQuery');
    }

    /**
     * Filter the query by a related TradeCycle object
     *
     * @param   TradeCycle|PropelObjectCollection $tradeCycle  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByTradeCycle($tradeCycle, $comparison = null)
    {
        if ($tradeCycle instanceof TradeCycle) {
            return $this
                ->addUsingAlias(GridRunPeer::ID_GRID_RUN, $tradeCycle->getIdGridRun(), $comparison);
        } elseif ($tradeCycle instanceof PropelObjectCollection) {
            return $this
                ->useTradeCycleQuery()
                ->filterByPrimaryKeys($tradeCycle->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByTradeCycle() only accepts arguments of type TradeCycle or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the TradeCycle relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function joinTradeCycle($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('TradeCycle');

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
            $this->addJoinObject($join, 'TradeCycle');
        }

        return $this;
    }

    /**
     * Use the TradeCycle relation TradeCycle object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\TradeCycleQuery A secondary query class using the current class as primary query
     */
    public function useTradeCycleQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinTradeCycle($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'TradeCycle', '\App\TradeCycleQuery');
    }

    /**
     * Filter the query by a related BotEvent object
     *
     * @param   BotEvent|PropelObjectCollection $botEvent  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByBotEvent($botEvent, $comparison = null)
    {
        if ($botEvent instanceof BotEvent) {
            return $this
                ->addUsingAlias(GridRunPeer::ID_GRID_RUN, $botEvent->getIdGridRun(), $comparison);
        } elseif ($botEvent instanceof PropelObjectCollection) {
            return $this
                ->useBotEventQuery()
                ->filterByPrimaryKeys($botEvent->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByBotEvent() only accepts arguments of type BotEvent or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BotEvent relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function joinBotEvent($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BotEvent');

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
            $this->addJoinObject($join, 'BotEvent');
        }

        return $this;
    }

    /**
     * Use the BotEvent relation BotEvent object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\BotEventQuery A secondary query class using the current class as primary query
     */
    public function useBotEventQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinBotEvent($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BotEvent', '\App\BotEventQuery');
    }

    /**
     * Filter the query by a related BotCommand object
     *
     * @param   BotCommand|PropelObjectCollection $botCommand  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByBotCommand($botCommand, $comparison = null)
    {
        if ($botCommand instanceof BotCommand) {
            return $this
                ->addUsingAlias(GridRunPeer::ID_GRID_RUN, $botCommand->getIdGridRun(), $comparison);
        } elseif ($botCommand instanceof PropelObjectCollection) {
            return $this
                ->useBotCommandQuery()
                ->filterByPrimaryKeys($botCommand->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByBotCommand() only accepts arguments of type BotCommand or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BotCommand relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function joinBotCommand($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BotCommand');

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
            $this->addJoinObject($join, 'BotCommand');
        }

        return $this;
    }

    /**
     * Use the BotCommand relation BotCommand object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\BotCommandQuery A secondary query class using the current class as primary query
     */
    public function useBotCommandQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinBotCommand($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BotCommand', '\App\BotCommandQuery');
    }

    /**
     * Filter the query by a related BotDecision object
     *
     * @param   BotDecision|PropelObjectCollection $botDecision  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByBotDecision($botDecision, $comparison = null)
    {
        if ($botDecision instanceof BotDecision) {
            return $this
                ->addUsingAlias(GridRunPeer::ID_GRID_RUN, $botDecision->getIdGridRun(), $comparison);
        } elseif ($botDecision instanceof PropelObjectCollection) {
            return $this
                ->useBotDecisionQuery()
                ->filterByPrimaryKeys($botDecision->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByBotDecision() only accepts arguments of type BotDecision or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the BotDecision relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function joinBotDecision($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('BotDecision');

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
            $this->addJoinObject($join, 'BotDecision');
        }

        return $this;
    }

    /**
     * Use the BotDecision relation BotDecision object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\BotDecisionQuery A secondary query class using the current class as primary query
     */
    public function useBotDecisionQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinBotDecision($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'BotDecision', '\App\BotDecisionQuery');
    }

    /**
     * Filter the query by a related GridRunAudit object
     *
     * @param   GridRunAudit|PropelObjectCollection $gridRunAudit  the related object to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return                 GridRunQuery The current query, for fluid interface
     * @throws PropelException - if the provided filter is invalid.
     */
    public function filterByGridRunAudit($gridRunAudit, $comparison = null)
    {
        if ($gridRunAudit instanceof GridRunAudit) {
            return $this
                ->addUsingAlias(GridRunPeer::ID_GRID_RUN, $gridRunAudit->getIdGridRun(), $comparison);
        } elseif ($gridRunAudit instanceof PropelObjectCollection) {
            return $this
                ->useGridRunAuditQuery()
                ->filterByPrimaryKeys($gridRunAudit->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByGridRunAudit() only accepts arguments of type GridRunAudit or PropelCollection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the GridRunAudit relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function joinGridRunAudit($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('GridRunAudit');

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
            $this->addJoinObject($join, 'GridRunAudit');
        }

        return $this;
    }

    /**
     * Use the GridRunAudit relation GridRunAudit object
     *
     * @see       useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \App\GridRunAuditQuery A secondary query class using the current class as primary query
     */
    public function useGridRunAuditQuery($relationAlias = null, $joinType = 'LEFT JOIN')
    {
        return $this
            ->joinGridRunAudit($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'GridRunAudit', '\App\GridRunAuditQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   GridRun $gridRun Object to remove from the list of results
     *
     * @return GridRunQuery The current query, for fluid interface
     */
    public function prune($gridRun = null)
    {
        if ($gridRun) {
            $this->addUsingAlias(GridRunPeer::ID_GRID_RUN, $gridRun->getIdGridRun(), Criteria::NOT_EQUAL);
        }

        return $this;
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
                    \ApiGoat\Utility\TableVersion::bump('grid_run');
                }

        return $this->postDelete($affectedRows, $con);
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
                    \ApiGoat\Utility\TableVersion::bump('grid_run');
                }

        return $this->postUpdate($affectedRows, $con);
    }

    // add_tablestamp behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     GridRunQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7){
        return $this->addUsingAlias(GridRunPeer::DATE_MODIFICATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     GridRunQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst(){
        return $this->addDescendingOrderByColumn(GridRunPeer::DATE_MODIFICATION);
    }

    /**
     * Order by update date asc
     *
     * @return     GridRunQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst(){
        return $this->addAscendingOrderByColumn(GridRunPeer::DATE_MODIFICATION);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     GridRunQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7){
        return $this->addUsingAlias(GridRunPeer::DATE_CREATION, time() - $nbDays * 24 * 60 * 60, \Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date desc
     *
     * @return     GridRunQuery The current query, for fluid interface
     */
    public function lastCreatedFirst(){
        return $this->addDescendingOrderByColumn(GridRunPeer::DATE_CREATION);
    }

    /**
     * Order by create date asc
     *
     * @return     GridRunQuery The current query, for fluid interface
     */
    public function firstCreatedFirst(){
        return $this->addAscendingOrderByColumn(GridRunPeer::DATE_CREATION);
    }
}
