# Entities

_Auto-generated from HJSON GoatCheese behavior parameters._

High-level reference: every table grouped by menu placement, with key
behavior parameters that define how the entity surfaces in the UI.

## Menu: Settings

### Authy (`authy`)

- **Priority:** 200
- **Child tables:** ["authy_group_x","authy_log"]
- **Columns:** 30, **Foreign keys:** 4

User

### PushDevice (`push_device`)

- **Priority:** 210
- **Columns:** 9, **Foreign keys:** 4

Push device

### Country (`country`)

- **Columns:** 11, **Foreign keys:** 3

Country

### SimWallet (`sim_wallet`)

- **Priority:** 6
- **Columns:** 8, **Foreign keys:** 3

Paper Wallet

### MarketSummary (`market_summary`)

- **Priority:** 5
- **Columns:** 31, **Foreign keys:** 3

Market Data

### MarketRegime (`market_regime`)

- **Priority:** 6
- **Columns:** 22, **Foreign keys:** 3

Regime History

### AuthyGroup (`authy_group`)

- **Priority:** 50
- **Columns:** 13, **Foreign keys:** 3

Group

### AuthyLog (`authy_log`)

- **Columns:** 9, **Foreign keys:** 1

Login log

### Message (`message`)

- **Priority:** 10
- **Columns:** 2, **Foreign keys:** 0

Message

### Config (`config`)

- **Columns:** 12, **Foreign keys:** 3

Setting

### ApiRbac (`api_rbac`)

- **Child tables:** ["api_log"]
- **Columns:** 15, **Foreign keys:** 3

API ACL

### ApiLog (`api_log`)

- **Columns:** 6, **Foreign keys:** 2

API log

### Template (`template`)

- **Child tables:** ["template_file"]
- **Columns:** 15, **Foreign keys:** 3

Template

## Menu: Trading

### GridRun (`grid_run`)

- **Child tables:** ["bot_order","trade_cycle","bot_event","bot_command"]
- **Columns:** 47, **Foreign keys:** 3

Grid Run

## Menu: _(no menu)_

### BotOrder (`bot_order`)

- **Columns:** 21, **Foreign keys:** 4

Order

### TradeCycle (`trade_cycle`)

- **Columns:** 14, **Foreign keys:** 4

Trade Cycle

### BotEvent (`bot_event`)

- **Columns:** 11, **Foreign keys:** 4

Event

### BotCommand (`bot_command`)

- **Columns:** 10, **Foreign keys:** 4

Command

### BotDecision (`bot_decision`)

- **Columns:** 22, **Foreign keys:** 4

Refit Decision

### AuthyGroupX (`authy_group_x`)

- **Columns:** 7, **Foreign keys:** 5

Group

### TemplateFile (`template_file`)

- **Columns:** 9, **Foreign keys:** 4

File
