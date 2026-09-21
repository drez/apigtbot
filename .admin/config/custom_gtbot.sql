-- gtbot shared-wallet config (BASE SEED — deploys to prod, survives resetdata).
-- gtbot_shared_budget_quote: the ONE budget all runs share (paper wallet seeds
-- from it; funding target for real). gtbot_sim_wallet_epoch: paper-era start —
-- wallet derivation replays only simulated fills after this datetime.

INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_shared_budget_quote', '1000', 0,
    'Shared trading budget in quote (USDT) for ALL grid runs together. Per-run budget_quote values are slices of this pool and must not sum above it.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_shared_budget_quote');

INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_sim_wallet_epoch', '', 0,
    'Paper-wallet era start (Y-m-d H:i:s, server/UTC clock). The shared sim wallet is derived from simulated fills AFTER this moment; empty = all history.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_sim_wallet_epoch');

-- gtbot_max_drawdown_pct: wallet-level hard floor — when global equity falls
-- below shared_budget × (1 − pct/100) for 3 consecutive ticks, ALL active
-- runs are killed (buys canceled, inventory held; routine decides recovery).
-- Empty/missing = default 25. Explicit 0 disables the stop.
INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_max_drawdown_pct', '25', 0,
    'Max total drawdown as % of gtbot_shared_budget_quote before ALL runs are killed (buys canceled, inventory held). Empty = 25. 0 disables.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_max_drawdown_pct');

-- gtbot_use_all_funds: 1 = the pool cap the run slices may sum to follows
-- the live account value (wallet USDT + priced holdings) instead of
-- gtbot_shared_budget_quote — a growing wallet is put to work, a shrinking
-- one stops new commitments. The fixed number still seeds the paper wallet
-- and anchors the drawdown floor / NAV ledger. Slices resize at the next
-- refit. 0/empty = fixed budget (default).
INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_use_all_funds', '0', 0,
    'Use all available funds: 1 = the shared budget cap follows the live wallet value (USDT + priced holdings) instead of gtbot_shared_budget_quote; 0 = fixed budget. Takes effect at the next refit.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_use_all_funds');

-- gtbot_pool_reserve_pct (2026-09-21): headroom the AUTOMATIC allocators may
-- not plan against. The machine sizes slices to fill the pool exactly — 2 trend
-- arms x 350 + 2 grids x 300 = 1300 of a 1300 cap — so any rounding, any raise,
-- or (with gtbot_use_all_funds) a one-USDT mark-to-market dip put sum(slices)
-- over the cap and the daemon's per-tick check fail-closed EVERY entry at once.
-- Allocators now plan against cap x (1 - pct/100); BudgetGuard still enforces
-- the FULL cap, so this is slack the machine refuses to spend, not a second
-- tripwire. Nothing is ever trimmed below a floor or committed inventory to
-- reach it. Invalid/absent = 5, 0 disables, above 50 is clamped to 50.
INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_pool_reserve_pct', '5', 0,
    'Shared pool: % of the cap the automatic allocators (rebalance, drift, trend activation, funds release) leave unallocated as headroom. BudgetGuard still enforces the full cap, so this is slack, not a second limit. Empty/invalid = 5. 0 disables. Max 50.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_pool_reserve_pct');

-- Mechanical grid geometry (2026-09-09, MechanicalRefit): the cron re-anchors
-- a fixed ±half-width × levels ladder around price every N hours or when
-- price leaves the range; the routine keeps deploy_pct/regime only. The
-- 3-month replay (Jun–Sep 2026) had this earn as much as hourly LLM refits
-- with a third of the changes. mode: routine (hourly LLM refits, cron is a
-- dead-man's switch) | mechanical.
INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_grid_refit_mode', 'mechanical', 0,
    'Grid refit mode: mechanical = the hourly cron re-anchors a fixed ladder (gtbot_grid_half_width_pct x gtbot_grid_levels) every gtbot_grid_refit_hours or when price leaves the range, gtbot_set_grid accepts deploy_pct only; routine = the LLM routine sets geometry and the cron only acts when the routine is silent.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_grid_refit_mode');
INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_grid_half_width_pct', '6', 0,
    'Mechanical grid: half-width around price in percent (widened to 4 x 4h ATR when the tape is wilder). Default 6.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_grid_half_width_pct');
INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_grid_levels', '6', 0,
    'Mechanical grid: number of rungs (reduced automatically until every rung clears the 1.3% spacing floor). Default 6: the 50 USDT/rung floor x 6 = a 300 slice.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_grid_levels');
INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_grid_refit_hours', '72', 0,
    'Mechanical grid: hours between clock re-anchors (price leaving the range re-anchors sooner; a 4h downtrend never re-anchors lower). Default 72.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_grid_refit_hours');

-- gtbot_trend_target_slice: what a trend-arm activation targets (whole USDT).
-- 350 lets two arms (BTC + BNB) and two grids at their 300 floor share 1300.
INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_trend_target_slice', '350', 0,
    'Trend arm: slice (USDT) an activation raises the arm to, taken from pool headroom then grids down to their floors. Empty = 500.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_trend_target_slice');

-- ── allocator (2026-09-19) ──────────────────────────────────────────────────
-- Pool reallocation is automatic: a budget change, a retire or a purge spreads
-- the delta pro-rata over Auto runs, respecting each run's floor. Profit drift
-- is a SEPARATE, slower leg and ships DISABLED (drift_pct 0) — run
-- `bin/gtbot-allocate --dry` for a window, read the alloc_drift events, then
-- set it to 5. Drift may only move capital idle INSIDE a slice: a donor is
-- capped at slice - max(floor, committed), so it can never force a divestment.

INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_alloc_drift_pct', '0', 0,
    'Allocator: max % of the eligible slice sum profit-drift may move per pass. 0 = drift OFF (pool reallocation still runs). Recommended once observed: 5.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_alloc_drift_pct');

INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_alloc_drift_max', '50', 0,
    'Allocator: hard USDT ceiling on what one profit-drift pass may move, whatever the percentage works out to.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_alloc_drift_max');

INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_alloc_drift_hours', '24', 0,
    'Allocator: minimum hours between profit-drift passes. The rebalance leg runs every cron tick regardless.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_alloc_drift_hours');

INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_alloc_window_days', '14', 0,
    'Allocator: look-back (days) for the realized-PnL-per-deployed-USDT score that drift ranks runs by.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_alloc_window_days');

INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_alloc_max_share_pct', '50', 0,
    'Allocator: no single run may hold more than this % of the shared cap, however well it scores.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_alloc_max_share_pct');

INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_alloc_deadband', '25', 0,
    'Allocator: idle headroom (USDT) tolerated before a rebalance fires. Stops slice churn when the cap follows a moving wallet.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_alloc_deadband');

-- gtbot_shared_seed_assets: tokens the operator put INTO the shared pool, as
-- JSON {"BTC":"0.01"}. The paper wallet is DERIVED (deriveAndStore wipes
-- sim_wallet and replays the ledger from the USDT seed), so a row inserted by
-- hand is erased at the next daemon boot — a token has to enter here to
-- survive. Tokens shift the MIX a run can draw on; cap() stays min(wallet,
-- gtbot_shared_budget_quote), so they never raise the ceiling.
INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_shared_seed_assets', '', 0,
    'Non-USDT tokens seeded into the shared pool, JSON {"ASSET":"qty"} e.g. {"BTC":"0.01"}. Empty = none. Survives the wallet derivation; priced from market_summary when no run trades that symbol.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_shared_seed_assets');

-- ── engagement / regime episodes (2026-09-19) ──────────────────────────────
-- How much of the pool is actually WORKING per symbol (Domains\Bot\Engagement),
-- journaled into the regime_episode open at the time. Below the floor for
-- idle_passes consecutive allocator passes, the symbol gets ONE opportunity_idle
-- alert per episode — whatever the cause, including a park the operator chose.
-- 8 passes = 2 h at the 15-minute allocator cron.

INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_engagement_floor_pct', '40', 0,
    'Engagement floor (%): invested / slice per symbol below which the pool counts as idle through a TREND_UP episode. Default 40.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_engagement_floor_pct');

INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_engagement_idle_passes', '8', 0,
    'Consecutive sub-floor allocator passes before opportunity_idle alerts (one alert per episode). 8 = 2 h at the 15-minute cron.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_engagement_idle_passes');

-- ── real-account baseline (2026-09-21) ─────────────────────────────────────
-- The drawdown floor is a fraction of the wallet the fleet actually trades.
-- On paper that is gtbot_shared_budget_quote. On a real account it is what the
-- account was FUNDED with, which has nothing to do with the paper pool seed:
-- anchoring the floor to the paper number meant that flipping the money switch
-- before funding the account (the old RUNBOOK ordering) put the whole fleet
-- three ticks from a drawdown_stop, and that a small canary account sat far
-- under a floor it had never had anything to do with.
--
-- Ships EMPTY on purpose. DrawdownGuard seeds it once, from the first positive
-- real equity it ever measures, and alerts with the number so the operator can
-- correct it (a deposit that lands in two transfers would otherwise baseline
-- the fleet on the first half). Edit it whenever the account is funded or
-- drawn down deliberately.

INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_real_account_baseline', '', 0,
    'Real account: what it was funded with, in USDT. The drawdown floor is this x (1 - gtbot_max_drawdown_pct/100) once the fleet trades real money. Empty = seeded automatically from the first real equity measured.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_real_account_baseline');

-- gtbot_outlook_alerts: Telegram level for the long-horizon outlook
-- (bin/gtbot-outlook). off = log + score + routine brief only; major = one
-- message when a symbol enters or leaves MAJOR_UP / MAJOR_DOWN, plus the
-- daily digest; all = FORMING changes too. The outlook is a DETECTOR with no
-- proven forecast skill (refit-evidence §16) — the messages say so.
INSERT INTO `config` (`category`, `config`, `value`, `system`, `description`, `type`, `date_creation`)
  SELECT 0, 'gtbot_outlook_alerts', 'major', 0,
    'Long-horizon market outlook Telegram level: off | major | all. Detection only, not a forecast; logging, scoring and the routine brief run regardless.',
    'string', NOW()
  FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `config` WHERE `config` = 'gtbot_outlook_alerts');
