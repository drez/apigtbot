# apigtbot — paper-trading crypto bot platform

A self-hosted crypto trading bot with **two switchable strategies per run** — a
classic **grid** (mean-reversion ladder) and a **trend follower** (Donchian
breakout entry, ATR trailing-stop exit) — running as always-on daemons against
live Binance market data. **Both trading modes are available**: paper mode
(the default — real prices, simulated fills, no keys required) and real
trading (Binance testnet or live mainnet with your API keys). Built on the
[ApiGoat / GoatCheese](https://apigoat.com/)
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

## Trading modes

| Mode | How | Keys |
|---|---|---|
| **Paper** (default) | Live mainnet market data, fills simulated locally, balances in a simulated wallet; a per-run `simulated` switch and a dashboard-wide toggle | none |
| **Real — testnet** | Orders sent to the Binance Spot testnet (run status `Testnet`, `GTBOT_USE_TESTNET=1`) | testnet API keys |
| **Real — live** | Orders sent to mainnet (run status `Live`, `simulated` off, `GTBOT_USE_TESTNET=0`, `GTBOT_DRY_RUN=0`) | mainnet API keys (HMAC or Ed25519) |

Real trading is gated on purpose: the per-run switch AND the environment
flags must all agree, mismatched combinations are refused at daemon boot,
and the daemon fast-fails without credentials. Every risk rail (per-run
loss caps, shared-budget guard, wallet drawdown stop) applies identically
in every mode.

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

## Telegram notifications (optional)

The daemons fan important events out to Telegram: **alerts immediately**
(kills, breakout halts, drawdown stop, stranded inventory, algo switches),
**closed trade cycles the moment they book** (with realized P/L), and other
trade activity (fills, refits, start/stop) coalesced into per-tick digests
so a burst of fills arrives as one message instead of ten. Alerts respect a
throttle window so an incident can't flood your phone, and every line ends
with a unique `[#event-id]` tag so a genuine repeat is distinguishable from
a transport double-send.

Setup (two env values):

1. Create a bot with [@BotFather](https://t.me/BotFather) → copy the token.
2. Send your new bot any message, then get your chat id — e.g. open
   `https://api.telegram.org/bot<TOKEN>/getUpdates` and read
   `message.chat.id` (for a group, add the bot to the group and use the
   negative group id).
3. In `.env`:
   ```bash
   GTBOT_TELEGRAM_BOT_TOKEN="123456:ABC-your-token"
   GTBOT_TELEGRAM_CHAT_ID="123456789"
   ```
4. Restart the daemons (dashboard ⟳ Reload per run, or just wait — the
   watchdog relaunches them). Every event still lands in the `bot_event`
   feed and the dashboard regardless, so Telegram is purely additive.

## AI-assisted refits (optional)

The grid geometry doesn't have to be tuned by hand. The repo ships a
battle-tested **hourly agent routine** (`.admin/docs/refit-routine.md`): an
LLM agent connects over MCP, reads live market signal (`gtbot_market`),
re-fits each grid run's range/levels/deployment (`gtbot_set_grid`),
reallocates budget slices across runs, and journals every decision — which
the server then **scores against what actually happened** (`gtbot_decisions`),
so the agent learns from its own track record. Evidence-derived guardrails
(spacing/width floors from the shipped walk-forward sweeps) are baked into
the prompt, and hard risk caps stay enforced server-side no matter what the
agent asks for.

Setup:

1. **Connect the MCP server** (OAuth; acts as your admin user):
   ```bash
   claude mcp add --transport http gtbot https://your-domain/api/v1/mcp
   ```
2. **Schedule the routine hourly** with the prompt block from
   `.admin/docs/refit-routine.md` — as a Claude Code cloud routine
   (`/schedule`), or any MCP-capable agent runner, or plain cron:
   ```cron
   0 * * * * claude -p "$(sed -n '/^PROMPT VERSION/,/^```/p' /path/to/apigtbot/.admin/docs/refit-routine.md | sed '$d')"
   ```
3. **The fallback is already installed**: the `gtbot-refit` cron from the
   install steps acts as a dead-man's switch — it stays silent while the
   agent routine is active and takes over quantile-based refits only if the
   routine goes quiet for 2+ hours.

The routine is advisory-with-guardrails by design: it may re-fit geometry
and reallocate slices, but it cannot touch risk caps, clear kill switches,
or liquidate inventory — those stay human decisions.

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
