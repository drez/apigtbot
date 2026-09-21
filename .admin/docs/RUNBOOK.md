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
| `GTBOT_ARM_DARK_ALERT` | dark-arm sweep threshold, seconds (default 3600) |

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

The watchdog's second sweep alerts `arm_dark` (once per run / 6h) when a run
that is NOT Done/Draft has not ticked for `GTBOT_ARM_DARK_ALERT` seconds while
being outside the supervised set (Halted, DryRun) or carrying the kill switch,
AND nothing accounts for the silence. Accounted for means, by VALUE and not by
timing: the newest `grid_run_audit` row for `status` put the run in the status
it is in now, or the newest `kill_switch` row turned the kill on — somebody
parked it, whenever that was. Failing that, a lifecycle event (`funds_hold`,
`run_retired`, `run_finalized`, `kill`, `kill_requested`, `trend_release`,
`trend_deactivate`) within 6 h before the last tick. It is the answer to a run
parked by accident with its slice stranded: clear it by parking the run
deliberately (a status or kill change through the GUI/MCP is itself the
explanation) or by putting it back to work.

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

0. `php8.4 bin/gtbot-golive` prints every gate below that can be read off the
   ledgers (`GoLiveGate`: ≥ 28 paper days ahead of flat USDT, drawdown inside
   the floor, a TREND_UP leg the arm was engaged in, no killed runs, fixed
   pool with a reserve, alerts configured) and exits 0 only when all pass.
   While a canary runs, cron `bin/gtbot-audit --alert` every 10 min
   (`AccountAudit`: the account must hold what the ledgers track, and the
   book must match the ledgers' open orders, both directions).

1. Multi-day **testnet soak** is boring: cycles complete, restarts recover
   with zero duplicate orders, every induced fault alerts.
2. Jurisdiction/venue resolved (Binance is unavailable to Canadian residents).
3. Fresh mainnet Ed25519 key: trading-only, withdrawals off, IP-restricted.
4. `.env` **on the host**: `GTBOT_USE_TESTNET=0`, `GTBOT_DRY_RUN=0`; run row
   status → `Live` (the daemon refuses mismatched combinations). These two
   keys are host identity: they must never appear in `DEPLOY_PUSH_ENV`, or a
   deploy from a dev box stops a real fleet at boot (`DeployPushEnvTest`).
5. **Fund the account FIRST, with the pool and nothing else** — before the
   money switch, not after. The drawdown floor is whole-account equity, so
   spare money on the account disarms it, and (until 2026-09-21) flipping
   first meant the first daemon to reboot measured an account holding
   nothing: equity 0, three ticks, the whole fleet killed. The guard now
   stays on the paper wallet until the real account has been seen holding
   something, but funding first is still the order that makes sense.
6. Flip paper → real with the dashboard money switch only
   (`ModeSwitch::flipAll`), never run by run. Machine-created runs are born
   into the fleet's mode (`RunFactory::create`); while ANY real run is
   exposed and has stamped its account, the drawdown floor reads the real
   account. The first positive real equity seeds
   **`gtbot_real_account_baseline`** and alerts with the number: that is what
   `gtbot_max_drawdown_pct` is a fraction of from then on (NOT
   `gtbot_shared_budget_quote`, which stays the paper pool's seed). Check the
   alert, and edit the config row if the account was funded in more than one
   transfer, or you add to / withdraw from it later (see the drawdown-stop
   section — a withdrawal that is not re-baselined kills the fleet). An
   account worth less than 50 USDT (`TrendActivator::MIN_SLICE`) is NOT
   treated as funded: a BNB fee float that lands before the USDT cannot
   baseline the fleet at its own size.
7. **Fees: turn "pay fees in BNB" ON and hold a BNB float.** Binance takes a
   BUY's commission out of the coins it delivers; the float pays instead and
   exits keep their full size. `bnb_fee_float_low` alerts under
   `gtbot_bnb_fee_float_min_quote` (default 2 USDT). With the float empty
   the exchange silently falls back to the traded asset: `Daemon::bookFill`
   then holds/exits the NET qty (`fee_netted`, Alert once per daemon life) —
   safe, but each exit shrinks by up to one lot step, which eats a grid rung.
   A BNB pair is always charged in its base asset; spare BNB beyond the BNB
   runs' tracked inventory (`FeeFloat`) covers it the same way.
8. Canary sizing: smallest budget that clears min-notional, and pick the
   `NoLoss` or `Cautious` risk profile (`profile` column / `gtbot_create_run`
   `profile` arg) — caps are profile-derived live policy now, not hand-set
   columns (see `ProfilePolicy`) — and watch it. A canary account is small on
   purpose: its drawdown floor is 25% of what YOU funded it with
   (`gtbot_real_account_baseline`), never of the paper pool.

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

When global equity < **the measured wallet's baseline** × (1 −
gtbot_max_drawdown_pct/100) for 3 consecutive ticks, any live daemon kills ALL
active runs: buys canceled, kill switches ON, inventory HELD. The baseline is
`gtbot_shared_budget_quote` while the fleet is on paper (default 25% → floor
750 on a 1000 budget) and `gtbot_real_account_baseline` — what the exchange
account was funded with — once a real run is exposed and stamped. They are two
different wallets and one must never be measured against the other's size;
`gtbot_status` and the `drawdown_stop` alert both name the source.
Telegram gets the `drawdown_stop` alert; `gtbot_status` shows the `drawdown`
block (equity, floor, tripped); the dashboard Account value tile turns red
with the floor shown under it.

Recovery (hourly routine is authorized, or do it manually):
- Hold: nothing to do — killed+holding is stable.
- Selloff: `gtbot_kill {run, flatten:true, confirm:true, reason:…}` per run
  (the tool refuses flatten for `NoLoss` runs — they stay held, not sold).
- Restart: re-baseline first — **the row you edit is the MEASURED wallet's**
  (`gtbot_status` → `drawdown.source`): `gtbot_shared_budget_quote` on paper,
  **`gtbot_real_account_baseline` on real**. Editing the paper seed while the
  source is `real` does nothing at all and the restart re-trips in 3 ticks.
  Set it to current equity, refit the budget slices under it (decreases
  before increases), THEN clear the kill switches. Restarting below the floor
  re-trips in 3 ticks by design.
- **Withdrawing profit is a re-baseline too.** The floor is a fraction of what
  the account was funded with, so taking 700 out of a 2000 account leaves
  1300 against a 1500 floor and kills the fleet within three ticks. Lower
  `gtbot_real_account_baseline` by what you withdrew **before** the transfer
  (and raise it by what you add, per step 6 above).
Disable/resize: config `gtbot_max_drawdown_pct` (empty = 25, 0 = off) —
takes effect next tick, no daemon restart needed.

## Shared budget (pool cap)

- **Edit from the dashboard** — the Budget band tile has an *edit* pencil:
  enter the new USDT amount, confirm. It writes `gtbot_shared_budget_quote`
  and enqueues a Reload on every DryRun/Testnet/Live run (paper wallet
  re-seeds at boot). Run slices are NOT resized: the hourly routine / trend
  activator pick the new cap up on their next pass. Setting it below the
  current slices sum halts new entries on every run until then (the confirm
  dialog warns).
- **Use all available funds** — config `gtbot_use_all_funds` = 1 makes the
  cap the run slices may sum to follow the live account value (wallet USDT +
  priced holdings, whole USDT; `BudgetPool::cap()`) instead of the fixed
  number. The fixed number still seeds the paper wallet and anchors the
  drawdown floor and NAV ledger. The dashboard pencil is hidden while it is
  on. Falls back to the fixed budget while the wallet cannot be valued.
