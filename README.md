# apigtbot

**A self-hosted crypto trading desk that runs itself, explains itself, and
refuses to lose your money in the dumb ways.**

apigtbot runs a small fleet of always-on trading bots against live Binance
market data: **grid** ladders that harvest chop, and **trend** arms that
wake up when a real move starts. One shared wallet funds them all, capital moves
to wherever the opportunity is, and every decision is written down and
**scored against what actually happened**. An LLM agent can drive the whole thing over MCP,
with a strict budget, while hard risk rails stay enforced on the server
whatever the agent asks for.

Start in **paper mode** (real prices, simulated fills, no API keys) and move
to testnet or mainnet only when the built-in go-live checks say the fleet is ready.

---

## Why it's different

- **Evidence before trust.** Every strategy knob ships with a walk-forward
  sweep behind it: spacing, width, regime gates, allocation, entry variants,
  core-vs-trade books, long-horizon outlook. Ideas the data refuted stay
  refuted and are written up as such in
  [`refit-evidence.md`](.admin/docs/refit-evidence.md), 16 sections and counting.
- **Capital follows opportunity.** No fixed budget splits. Each symbol
  declares its arms. A trend arm is funded when its regime turns up and
  released when it fades. An allocator rebalances the pool every 15 minutes
  and keeps a reserve it will not spend.
- **Never overcommit, never panic-sell.** The shared-budget invariant is
  enforced in three layers (save time, allocator, daemon). A wallet-wide
  drawdown floor stops every run. By default a position is never sold below
  cost unless the ladder has nothing left to trade with.
- **It tells you why.** An arm sitting idle names its cause. A silent grid
  raises an alert. An arm with nothing running has to explain itself to the
  watchdog, or it pages you.
- **The AI proposes, the server disposes.** The hourly agent routine can
  re-fit geometry and move slices. It cannot touch risk caps, clear kill
  switches or liquidate inventory.

## Features

### Strategies
- **Grid engine**: geometric or arithmetic buy-low/sell-high ladders.
  Geometry can be set by the agent or run in **mechanical mode** (fixed
  ±% band, re-centred on a timer). Re-anchoring carries exits over, so a
  refit never abandons inventory.
- **Trend engine**: long-only breakout entries (Donchian high with an EMA
  filter). Exits use an ATR-ratcheted trailing stop with a hard floor.
  **Regime activation** is machine-scaled from 4h and 1d ADX / efficiency ratio,
  with hysteresis so it doesn't flap. The arm deploys its whole slice on
  activation and exits cleanly on deactivation.
- **Optional inventory core**: a 1d EMA-cross core position built in
  staggered tranches, bought on a pullback or after a time limit.
- **Per-run algo switch**: flip a live run between Grid and Trend. Open
  entries are cancelled, partial fills booked, and working exits carried over.

### Capital and risk
- **One shared wallet**, multi-asset seeded, with an optional "use all
  funds" mode where the cap follows account value.
- **Automatic pool reallocation**, plus a run lifecycle: retire and purge
  finished runs, and wallet sealing.
- **Risk profiles** (`NoLoss / Cautious / Balanced / Aggressive / Max`):
  one dropdown sets every cap as a live ratio of the run's budget.
- **Hold-at-loss policy**: the `sell_at_loss` switch defaults to off, and
  `sell_when_starved` sells below cost only when the ladder has no funds left.
  **Flatten** takes the bids and chases the price until the position is flat.
- **Drawdown floor**: if equity falls below the floor and stays there for
  3 ticks, every run is killed.

### Observability
- **Trading dashboard**: stored OHLCV candles in 5 timeframes with bot
  overlays (trades, grid band, EMA, lifecycle markers). Holdings, a
  shared-wallet tile with an inline budget editor, Hold/Release funds
  per run, start/pause/reload.
- **Wallet NAV ledger** and a P&L report benchmarked against HODL and USDT.
- **Decision receipts**: each agent decision records its clamps and candidate
  delta, and gets a counterfactual verdict computed from intra-bar OHLC.
- **Regime episodes and engagement tracking**: how long each arm was
  eligible and how much of that time it was actually working.
- **Long-horizon market outlook**: a 1d/1w regime *detector* with hysteresis
  and scored 7d/30d outcomes, plus an optional daily Telegram digest. It is
  a detector because the predictor version was tested and refuted
  (evidence §16).
- **Append-only run history**, audited by the watchdog.
- **Telegram fan-out**: alerts go out immediately and fills are batched into
  digests. Stop alerts are never throttled.

### Real-money readiness
- **Go-live gate** (`gtbot-golive`) is read off the ledgers and exits 0 only
  when every check passes.
- **Account audit** (`gtbot-audit`) checks that the real exchange account
  backs the ledgers. A finding is paged only if it survives two consecutive audits.
- Fills are booked at the price they actually traded at. The BNB fee float
  is handled, and exchange switches belong to the host, so a deploy never pushes them.

### Agent-native (MCP)
The whole system is exposed as an MCP server. The agent tools are:
`gtbot_status`, `gtbot_routine_brief` (one call per hour covers the whole
fleet, with a `material_change` fingerprint), `gtbot_set_grid`,
`gtbot_preview_grid`, `gtbot_refit_proposal`, `gtbot_market`,
`gtbot_backtest`, `gtbot_pnl_report`, `gtbot_decisions`, `gtbot_events`,
`gtbot_budget`, `gtbot_alloc_mode`, `gtbot_create_run`, `gtbot_start`,
`gtbot_stop`, `gtbot_kill`, `gtbot_retire_run`, `gtbot_purge_run`.

---

## Trading modes

| Mode | How | Keys |
|---|---|---|
| **Paper** (default) | Live mainnet market data, fills simulated locally, balances in a simulated wallet | none |
| **Real: testnet** | Orders sent to the Binance Spot testnet (`GTBOT_USE_TESTNET=1`) | testnet API keys |
| **Real: live** | Orders sent to mainnet (`simulated` off, `GTBOT_USE_TESTNET=0`, `GTBOT_DRY_RUN=0`) | mainnet API keys (HMAC or Ed25519) |

Real trading requires the per-run switch and the environment flags to agree.
The daemon refuses a mismatched combination at boot and fails fast without
credentials. Any real run moves the drawdown floor onto the real account.
Every risk rail applies the same way in every mode.

## Requirements

- PHP **8.4+** (CLI) with `bcmath`, `curl`, `pdo_mysql`
- MySQL / MariaDB
- Composer
- A web server with URL rewriting (Apache `mod_rewrite` or nginx equivalent)
- cron

## Install

The repo ships all generated artifacts (ORM models, `schema.sql`, seed
data), so there is no build toolchain to run:

```bash
git clone https://github.com/<you>/apigtbot.git
cd apigtbot
php install.php
```

The installer asks for database credentials, the public URL and the admin
account. It then writes `.env` with generated secrets, runs `composer install`,
loads the schema and seeds, and creates the admin user. For non-interactive
provisioning:

```bash
php install.php --non-interactive \
  --db-host=localhost --db-name=apigtbot --db-user=apigtbot --db-pass=... \
  --db-root-user=root --db-root-pass=... \
  --url=https://your-domain/apigtbot --admin-pass=...
```

Point your web server at the repo directory with rewriting enabled and log
in at `/.admin`.

> The project is developed with the ApiGoat/GoatCheese schema toolchain
> (internal). Its generated output is committed, so a public clone installs
> without it. Changing the schema needs that toolchain.

### Daemons and cron

Each active run gets one daemon, and a watchdog keeps the daemons alive.
No root or systemd is needed. `.admin/bin/prod-setup.sh` installs the full
crontab:

```cron
* * * * *          php8.4 /path/to/apigtbot/.admin/bin/gtbot-watchdog
*/10 * * * *       php8.4 /path/to/apigtbot/.admin/bin/gtbot-market-collect
* * * * *          php8.4 /path/to/apigtbot/.admin/bin/gtbot-market-collect --candles
3,18,33,48 * * * * php8.4 /path/to/apigtbot/.admin/bin/gtbot-trend-activate
8,23,38,53 * * * * php8.4 /path/to/apigtbot/.admin/bin/gtbot-allocate
12 * * * *         php8.4 /path/to/apigtbot/.admin/bin/gtbot-outlook
0 * * * *          php8.4 /path/to/apigtbot/.admin/bin/gtbot-refit
0 3 * * *          php8.4 /path/to/apigtbot/.admin/bin/gtbot-backup
```

To start trading, create a run (Trading → Grid Run) and set it to Live. The
watchdog spawns its daemon within a minute. To run a daemon in the
foreground instead: `php .admin/bin/gtbot --run=<id>`.

### Safety-model environment flags

| Variable | Default | Meaning |
|---|---|---|
| `GTBOT_DRY_RUN` | `1` | compute and log intended orders, send nothing |
| `GTBOT_USE_TESTNET` | `1` | testnet base URL for non-simulated runs |
| `GTBOT_API_KEY` / `GTBOT_API_SECRET` | — | only needed off paper mode |
| `GTBOT_TELEGRAM_BOT_TOKEN` / `GTBOT_TELEGRAM_CHAT_ID` | — | alert fan-out |

## Telegram notifications (optional)

Alerts go out immediately: kills, drawdown stop, idle or dark arms, stranded
inventory, algo switches and outlook changes. Closed trade cycles are sent
as soon as they book, with realized P/L. Other activity is batched into one
digest per tick. Every line ends with a unique `[#event-id]` tag.

1. Create a bot with [@BotFather](https://t.me/BotFather) and copy the token.
2. Send the bot any message, then read `message.chat.id` from
   `https://api.telegram.org/bot<TOKEN>/getUpdates`. For a group, use the
   negative group id.
3. In `.env`:
   ```bash
   GTBOT_TELEGRAM_BOT_TOKEN="123456:ABC-your-token"
   GTBOT_TELEGRAM_CHAT_ID="123456789"
   ```
4. Reload the daemons from the dashboard, or wait for the watchdog to restart them.

## AI-assisted refits (optional)

The repo ships an **hourly agent routine**
([`refit-routine.md`](.admin/docs/refit-routine.md)). The agent connects
over MCP and makes a single `gtbot_routine_brief` call. If nothing material
changed, the hour ends there. Otherwise it re-fits the flagged runs, moves
slices and journals each decision. The server scores those decisions
afterwards, so the agent can learn from its own track record.

1. **Connect the MCP server** (OAuth, acts as your admin user):
   ```bash
   claude mcp add --transport http gtbot https://your-domain/api/v1/mcp
   ```
2. **Schedule the routine hourly** using the prompt in `refit-routine.md`.
   You can run it as a Claude Code cloud routine (`/schedule`), in any
   MCP-capable agent runner, or from plain cron:
   ```cron
   0 * * * * claude -p "$(sed -n '/^PROMPT VERSION/,/^```/p' /path/to/apigtbot/.admin/docs/refit-routine.md | sed '$d')"
   ```
3. **A fallback is already installed.** The `gtbot-refit` cron stays silent
   while the routine is active. It takes over quantile-based refits if the
   routine goes quiet for 2 hours or more.

## Documentation

| Doc | Contents |
|---|---|
| `.admin/docs/RUNBOOK.md` | going live, incident recovery, alerts |
| `.admin/docs/refit-evidence.md` | every sweep, what it proved and what it refuted |
| `.admin/docs/refit-routine.md` | the hourly agent routine's prompt and design |
| `.admin/docs/MCP.md` | connecting an MCP client or LLM agent |
| `.admin/docs/API.md`, `SCHEMA-OVERVIEW.md`, `ENTITIES.md` | REST API and data model |
| `docs/superpowers/specs/` | design documents for every major feature |

## Development

```bash
cd .admin
vendor/bin/phpunit tests/            # full suite
vendor/bin/phpunit tests/Custom/Bot/ # bot/daemon suites
```

The schema lives in `schema/*.hjson`. Strategy code is in
`.admin/src/App/Domains/Bot/`, with the pluggable engines under `Engine/`
behind the `StrategyEngine` seam.

## Disclaimer

This is experimental software for research and paper trading. It makes no
profit promise, and the evidence doc records the losing results alongside
the winning ones. Nothing here is financial advice. If you connect real
funds, you do so at your own risk.
