# apigtbot — Grid Bot Runbook

Binance spot grid/DCA bot on GoatCheese. Long-only BTC/USDT: a ladder of
limit buys below price; each fill arms a matching sell one grid step up.
Full design: `docs/superpowers/plans/2026-07-21-trading-bot-apigtbot` in the
gc orchestrator repo.

**Who does what:** only the daemon (`.admin/bin/gtbot`) talks to Binance.
The admin GUI and the MCP tools read state and write commands into the DB;
the daemon consumes them every tick (~5s).

---

## Host setup (run this in prod — and after any host move)

```bash
cd <project>/.admin
sudo ./bin/prod-setup.sh <id_grid_run>  # e.g. sudo ./bin/prod-setup.sh 1
```

Needs root (system unit install). On locked-down ISPConfig web users the app
owner has no sudo and no systemd linger, so run this from a root shell — the
script takes the service User from the checkout owner, not from whoever runs
it, so `sudo` is correct. (Alternative without a root shell: have an admin run
`loginctl enable-linger <appuser>` once, then the app user can manage a
`systemctl --user` unit.)

That one script (idempotent, re-run any time):
1. installs `gtbot@.service` generated for this host's path/user and starts
   `gtbot@<run_id>`;
2. installs the watchdog crontab line (`gtbot-watchdog` every minute — the
   SUPERVISOR: it spawns a daemon for any active run whose heartbeat is
   stale, crash-loop guarded at 3 spawns/15 min, and only alerts when the
   spawns don't stick);
3. prints the service status and the cron line.

Before running it, the host needs (all in `.admin/.env`, chmod 600 —
NEVER committed; `gc deploy` does not ship `.env`):

| Key | What |
|---|---|
| `GTBOT_USE_TESTNET` | `1` testnet (default) / `0` mainnet |
| `GTBOT_DRY_RUN` | `1` log-only (default) / `0` place real orders |
| `GTBOT_API_KEY` | API key from the exchange key registration |
| `GTBOT_API_PRIVATE_KEY` | path to the Ed25519 private PEM (see Keys below) |
| `GTBOT_TELEGRAM_BOT_TOKEN` / `GTBOT_TELEGRAM_CHAT_ID` | alert channel (`bin/gtbot-watchdog --discover` finds the chat id) |
| `GTBOT_HEARTBEAT_STALE` | watchdog staleness threshold, seconds (default 60) |

## Keys (per host — never copy a private key between hosts)

```bash
cd <project>/.admin
openssl genpkey -algorithm ed25519 -out config/gtbot-ed25519-priv.pem
chmod 600 config/gtbot-ed25519-priv.pem
openssl pkey -in config/gtbot-ed25519-priv.pem -pubout      # → paste into the
                                                            # exchange "register
                                                            # Ed25519 key" form
```

The exchange returns an API key string → `GTBOT_API_KEY`. On mainnet:
trading-only permissions, withdrawals disabled, IP-allowlist the host.
The private PEM is gitignored; a new host means a new pair + registration.

## Daily ops

| Task | How |
|---|---|
| Is it alive / P&L? | GUI → Grid Run screen, or MCP `gtbot_status` / `gtbot_pnl_report` |
| Tail the daemon | `journalctl -u gtbot@1 -f` (events also in the GUI Events child list) |
| Pause (keep orders working) | Dashboard **⏸ Pause** button, MCP `gtbot_stop` (confirm-gated), or insert a `Pause` command row |
| Resume | Dashboard **▶ Start** button, or MCP `gtbot_start` |
| New run's daemon | Nothing — the watchdog cron spawns it within a minute of `gtbot_create_run`. A systemd unit (`sudo ./bin/prod-setup.sh <id>`) is an optional extra layer; the per-run DB lock keeps them from colliding |
| **EMERGENCY STOP** | MCP `gtbot_kill`, or tick the **Kill switch** checkbox on the run in the GUI — the daemon cancels open buys, holds inventory, stops. Works even if the daemon is wedged (it re-reads the flag every tick; watchdog alerts if it's dead). |
| Restart daemon | Dashboard **⟳ Reload** button (daemon exits cleanly, watchdog/systemd relaunches it), or `sudo systemctl restart gtbot@1` — safe either way: boot rehydrates from the DB and reconciles against the exchange before placing anything |
| Stop the service | `sudo systemctl stop gtbot@1` (finishes the tick, releases the DB lock) |
| Alert test | `php bin/gtbot-watchdog --test` |

The unrealized-loss stop is **staged**: at 60% of `max_unrealized_loss_quote`
the daemon cancels open buys and pauses entries (`derisk_on` — exits keep
working); entries re-arm automatically once the loss recovers under 40% of
the cap (`derisk_off`, hysteresis so it doesn't flap). At 100% it kills and
holds as before (`unrealized_stop`). While killed and still holding, every
additional half-cap of mark-to-market loss fires `unrealized_worsening` — a
bleeding hold is never silent; flatten or exit manually.

Alert-level events (kill, breakout halt, daily-loss breach, stale heartbeat,
API errors, foreign orders on the account) ping Telegram automatically.
Silence + a stale heartbeat is itself alerted by the watchdog cron. The
watchdog also alerts `routine_silent` when no gtbot_* MCP call has landed in
api_log for 2h (GTBOT_ROUTINE_SILENCE_ALERT) while a run is active — the
Claude refit routine is down (likely MCP disconnected); the gtbot-refit cron
keeps driving and re-centers IMMEDIATELY if price leaves the active range
(the 6h Claude-defer only applies while price is in range).

Telegram is throttled to **max 1 message per 5 minutes**, globally across the
daemon, refit cron, MCP tools and watchdog (shared state in
`tmp/telegram-notify.json`). The first message after a quiet spell goes
instantly; anything more queues and arrives as one digest when the window
expires (worst case ~5 min late — the bot_event table is always the
real-time source of truth). A failed delivery is retried on the next window.
**Exception: closed-cycle results** (`cycle_closed`) are sent the moment they
happen — no buffer, exempt from the throttle — so every harvested cycle pings
with its buy → sell, qty and realized P/L in real time.

## Going live checklist (M7 — do not skip gates)

1. Multi-day **testnet soak** is boring: cycles complete, restarts recover
   with zero duplicate orders, every induced fault alerts.
2. Jurisdiction/venue resolved (Binance is unavailable to Canadian residents).
3. Fresh mainnet Ed25519 key: trading-only, withdrawals off, IP-restricted.
4. `.env`: `GTBOT_USE_TESTNET=0`, `GTBOT_DRY_RUN=0`; run row status → `Live`
   (the daemon refuses mismatched combinations).
5. Canary sizing: smallest budget that clears min-notional, and pick the
   `NoLoss` or `Cautious` risk profile (`profile` column / `gtbot_create_run`
   `profile` arg) — caps are profile-derived live policy now, not hand-set
   columns (see `ProfilePolicy`) — and watch it.

## Troubleshooting

- **"another gtbot instance already holds the lock"** — one daemon per run,
  enforced via MySQL `GET_LOCK`; find it with `systemctl status gtbot@<id>`.
- **Signature errors (-1022)** — host clock drift; check NTP. Ed25519 key
  mismatch = key registered on the other network (testnet vs mainnet keys
  are separate registrations).
- **`Alert foreign_order`** — an order on the account the bot doesn't own:
  a second instance or manual trading on the bot account. The bot never
  touches it; investigate before resuming.
- **Run config rejected at boot** — the profitability floor (spacing ≥ 3×
  round-trip fees) or budget-vs-min-notional failed; preview configs first
  with MCP `gtbot_preview_grid`.
- **Tests** — `cd .admin && vendor/bin/phpunit --testsuite custom` (87 pure/
  integration tests, no keys needed; exchange emulated at the transport layer).

## Global drawdown stop (wallet floor)

When global equity < gtbot_shared_budget_quote × (1 − gtbot_max_drawdown_pct/100)
(default 25% → floor 750 on a 1000 budget) for 3 consecutive ticks, any live
daemon kills ALL active runs: buys canceled, kill switches ON, inventory HELD.
Telegram gets the `drawdown_stop` alert; `gtbot_status` shows the `drawdown`
block (equity, floor, tripped); the dashboard Account value tile turns red
with the floor shown under it.

Recovery (hourly routine is authorized, or do it manually):
- Hold: nothing to do — killed+holding is stable.
- Selloff: `gtbot_kill {run, flatten:true, confirm:true, reason:…}` per run
  (the tool refuses flatten for `NoLoss` runs — they stay held, not sold).
- Restart: re-baseline first — set `gtbot_shared_budget_quote` to current
  equity, refit the budget slices under it (decreases before increases),
  THEN clear the kill switches. Restarting below the floor re-trips in 3
  ticks by design.
Disable/resize: config `gtbot_max_drawdown_pct` (empty = 25, 0 = off) —
takes effect next tick, no daemon restart needed.
