# apigtbot — paper-trading crypto bot platform

A self-hosted crypto trading bot with **two switchable strategies per run** — a
classic **grid** (mean-reversion ladder) and a **trend follower** (Donchian
breakout entry, ATR trailing-stop exit) — running as always-on daemons against
live Binance market data in **paper-trading mode** (real prices, simulated
fills, no keys required). Built on the [ApiGoat / GoatCheese](https://apigoat.com/)
schema-driven PHP stack: the admin UI, REST API, and MCP server are generated
from an HJSON schema.

## What it does

- **Grid engine** — geometric/arithmetic buy-low-sell-high ladders with
  configurable range, levels, spacing, and per-level allocation; automatic
  re-anchoring when geometry changes, legacy-exit carry so refits never
  abandon inventory.
- **Trend engine** — long-only breakout entries (close over the N-bar high
  with fast-EMA > slow-EMA), ATR-ratcheted trailing stops, re-entry cooldown;
  entry tranches sized and risk-reviewed individually.
- **Per-run algo switch** — flip a live run between Grid and Trend from the
  dashboard; the daemon restarts itself and performs a clean cutover (open
  entries canceled with any partial fills booked, working exits carried as
  legacy orders, unguarded positions handed off at cost).
- **Risk profiles** — one dropdown (`NoLoss / Cautious / Balanced /
  Aggressive / Max`) drives all risk caps as live ratios of the run's budget:
  daily-loss limit, unrealized-loss stop, position/order caps, breakout
  policy, and the trend engine's stop multipliers. `NoLoss` structurally
  refuses every loss-realizing path.
- **Shared wallet with hard rails** — all runs draw slices from one budget
  pool; overcommitment is refused at save time and halts entries at the
  daemon; a wallet-level max-drawdown stop kills every run when equity
  breaches the floor.
- **Dashboard** — per-run charts (trades, price history, EMA trend line,
  grid band, lifecycle markers), holdings, shared-wallet tile, start/pause/
  reload controls.
- **MCP server** — the whole system is drivable by an LLM agent over the
  Model Context Protocol (`gtbot_status`, `gtbot_set_grid`, `gtbot_market`,
  `gtbot_pnl_report`, a scored decision journal, and more). An hourly
  agent routine can re-fit grid geometry from live market signal; every
  decision is journaled and scored against what actually happened.
- **Evidence culture** — walk-forward sweep scripts (`spacing`, `width`,
  `deploy-policy`, `trend`) validate strategy parameters against collected
  candle history before they are trusted with a live run.

Everything trades **paper by default**: mainnet public data, locally
simulated fills, balances in a simulated wallet table. Real-money mode
exists behind multiple explicit gates (per-run switch + environment flags +
API keys) and fast-fails without credentials.

## Requirements

- PHP **8.4+** (CLI) with `bcmath`, `curl`, `pdo_mysql`
- MySQL / MariaDB
- Composer
- A web server with URL rewriting (Apache `mod_rewrite` or nginx equivalent)
  for the admin UI / API
- cron (daemon watchdog + market data collection)

## Install

The repo ships all generated artifacts (ORM models, `schema.sql`, seed
data), so there is no build toolchain to run — the installer script does
everything:

```bash
git clone https://github.com/<you>/apigtbot.git
cd apigtbot
php install.php
```

It walks you through database credentials, the public URL, and the admin
account; writes `.env` (with generated secrets), runs `composer install`,
loads the schema and base seeds, and creates the admin user. Non-interactive
form for provisioning:

```bash
php install.php --non-interactive \
  --db-host=localhost --db-name=apigtbot --db-user=apigtbot --db-pass=... \
  --db-root-user=root --db-root-pass=... \
  --url=https://your-domain/apigtbot --admin-pass=...
```

(`--db-root-user` lets the script create the database and app user; omit it
if they already exist.) Then point your web server at the repo directory
with URL rewriting enabled and log in at `/.admin`.

> Note: the project is developed with the ApiGoat/GoatCheese schema
> toolchain (internal); its generated output is committed, which is why a
> public clone installs without it. Schema changes require that toolchain.

### Daemons and cron

Each active run gets one daemon process; a watchdog cron keeps them alive
(no root or systemd required):

```cron
* * * * *    /usr/bin/php8.4 /path/to/apigtbot/.admin/bin/gtbot-watchdog  >/dev/null 2>&1
*/10 * * * * /usr/bin/php8.4 /path/to/apigtbot/.admin/bin/gtbot-market-collect >/dev/null 2>&1
0 * * * *    /usr/bin/php8.4 /path/to/apigtbot/.admin/bin/gtbot-refit     >/dev/null 2>&1
0 3 * * *    /usr/bin/php8.4 /path/to/apigtbot/.admin/bin/gtbot-backup    >/dev/null 2>&1
```

Create a run in the admin UI (Trading → Grid Run), set its status to Live,
and the watchdog spawns its daemon within a minute. You can also run one in
the foreground: `php .admin/bin/gtbot --run=<id>`.

### Safety-model environment flags

| Variable | Default | Meaning |
|---|---|---|
| `GTBOT_DRY_RUN` | `1` | compute and log intended orders, send nothing |
| `GTBOT_USE_TESTNET` | `1` | testnet base URL for non-simulated runs |
| `GTBOT_API_KEY` / `GTBOT_API_SECRET` | — | only needed off paper mode |
| `GTBOT_TELEGRAM_BOT_TOKEN` / `GTBOT_TELEGRAM_CHAT_ID` | — | alert fan-out |

A run's own `simulated` switch (default **on**) is the paper-mode gate; the
env flags above only matter once it is off, and mismatched combinations are
refused at daemon boot.

## Documentation

| Doc | Contents |
|---|---|
| `.admin/docs/RUNBOOK.md` | operations: going live, incident recovery, alerts |
| `.admin/docs/MCP.md` | connecting an MCP client / LLM agent |
| `.admin/docs/refit-routine.md` | the hourly agent routine's prompt + design |
| `.admin/docs/API.md`, `SCHEMA-OVERVIEW.md`, `ENTITIES.md` | REST API and data model |
| `docs/superpowers/specs/` | design documents for every major feature |

## Development

```bash
cd .admin
vendor/bin/phpunit tests/            # full suite
vendor/bin/phpunit tests/Custom/Bot/ # bot/daemon suites
```

Schema lives in `schema/*.hjson`; run `gc build` after editing. Strategy
code is under `.admin/src/App/Domains/Bot/` (`Engine/` holds the pluggable
strategy engines behind the `StrategyEngine` seam).

## Disclaimer

This is experimental software for research and paper trading. Nothing here
is financial advice; if you connect real funds, you do so at your own risk.
