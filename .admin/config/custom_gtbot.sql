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
