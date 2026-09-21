#!/usr/bin/env bash
# prod-setup.sh — one-shot host setup for the gtbot daemon + watchdog.
# Idempotent, and correct whether run as root (prod) or as the owning user
# with sudo (dev): the service user is taken from the checkout's owner, not
# from whoever runs the script, and a PHP 8.x binary is auto-detected.
#
#   sudo ./bin/prod-setup.sh <id_grid_run>      # e.g. sudo ./bin/prod-setup.sh 1
#
# Prereqs on the host (see docs/RUNBOOK.md):
#   - .admin/.env has GTBOT_* keys (API key, Ed25519 pem, Telegram token/chat)
#   - the grid_run row exists with status DryRun/Testnet/Live
set -euo pipefail

RUN_ID="${1:?usage: prod-setup.sh <id_grid_run>}"
ADMIN="$(cd "$(dirname "$0")/.." && pwd)"
RUN_USER="$(stat -c '%U' "$ADMIN")"          # the app owner, whoever runs this

PHP_BIN=""
for c in php8.4 php8.3 php8.2 php; do
    if command -v "$c" >/dev/null 2>&1; then PHP_BIN="$(command -v "$c")"; break; fi
done
[ -n "$PHP_BIN" ] || { echo "prod-setup: no php binary found"; exit 1; }

SUDO=""
[ "$(id -u)" -eq 0 ] || SUDO="sudo"

echo "== gtbot host setup: admin=$ADMIN run=$RUN_ID user=$RUN_USER php=$PHP_BIN =="

# 1. systemd unit — runs as the app owner, not as root
$SUDO tee /etc/systemd/system/gtbot@.service >/dev/null <<UNIT
[Unit]
Description=apigtbot grid trading daemon (run %i)
After=network-online.target mariadb.service mysql.service
Wants=network-online.target
# Crash-loop guard: >6 starts in 5 min → stop the unit (loud) instead of
# hammering. The watchdog then alerts on the stale heartbeat.
StartLimitIntervalSec=300
StartLimitBurst=6

[Service]
Type=simple
User=$RUN_USER
WorkingDirectory=$ADMIN
ExecStart=$PHP_BIN $ADMIN/bin/gtbot --run=%i --interval=5
Restart=always
RestartSec=10
KillSignal=SIGTERM
TimeoutStopSec=30

[Install]
WantedBy=multi-user.target
UNIT
$SUDO systemctl daemon-reload
$SUDO systemctl enable --now "gtbot@${RUN_ID}"

# 2. crons for the app owner: watchdog (1 min), market collect (10 min),
#    chart candles (1 min, --candles: dashboard 1m/5m/15m freshness),
#    auto-refit + scoring/housekeeping (hourly), DB backup (daily 03:00),
#    trend-arm activator (every 15 min, 3 min after the :00/:10/:20… collector
#    passes so it reads fresh summaries; removes the retired gtbot-ab-watch)
WATCH_LINE="* * * * * $PHP_BIN $ADMIN/bin/gtbot-watchdog >/dev/null 2>&1"
COLLECT_LINE="*/10 * * * * $PHP_BIN $ADMIN/bin/gtbot-market-collect >/dev/null 2>&1"
CANDLE_LINE="* * * * * $PHP_BIN $ADMIN/bin/gtbot-market-collect --candles >/dev/null 2>&1"
REFIT_LINE="0 * * * * $PHP_BIN $ADMIN/bin/gtbot-refit >/dev/null 2>&1"
BACKUP_LINE="0 3 * * * $PHP_BIN $ADMIN/bin/gtbot-backup >/dev/null 2>&1"
TREND_LINE="3,18,33,48 * * * * $PHP_BIN $ADMIN/bin/gtbot-trend-activate >/dev/null 2>&1"
# 5 minutes after the trend activator, so the arm's slice is settled before the
# allocator reads it. Separate script on purpose: gtbot-trend-activate exits
# early when there is no Live Trend run, and allocation must still happen.
ALLOC_LINE="8,23,38,53 * * * * $PHP_BIN $ADMIN/bin/gtbot-allocate >/dev/null 2>&1"
# long-horizon market outlook (1d + 1w detection, Telegram warning + daily
# digest, scored log) — hourly, 2 minutes after the :10 collector pass
OUTLOOK_LINE="12 * * * * $PHP_BIN $ADMIN/bin/gtbot-outlook >/dev/null 2>&1"
install_cron() { # $1 = crontab command prefix
    ( $1 -l 2>/dev/null | grep -vF "gtbot-watchdog" | grep -vF "gtbot-market-collect" | grep -vF "gtbot-refit" | grep -vF "gtbot-backup" | grep -vF "gtbot-trend-activate" | grep -vF "gtbot-allocate" | grep -vF "gtbot-outlook" | grep -vF "gtbot-ab-watch" || true; echo "$WATCH_LINE"; echo "$COLLECT_LINE"; echo "$CANDLE_LINE"; echo "$REFIT_LINE"; echo "$BACKUP_LINE"; echo "$TREND_LINE"; echo "$ALLOC_LINE"; echo "$OUTLOOK_LINE" ) | $1 -
}
if [ "$(id -u)" -eq 0 ] && [ "$RUN_USER" != "root" ]; then
    install_cron "crontab -u $RUN_USER"
else
    install_cron "crontab"
fi

# 3. show the result
sleep 2
$SUDO systemctl status "gtbot@${RUN_ID}" --no-pager | head -8
echo
echo "watchdog cron for $RUN_USER:"
( [ "$(id -u)" -eq 0 ] && crontab -u "$RUN_USER" -l || crontab -l ) 2>/dev/null | grep gtbot-watchdog || true
echo
echo "OK. Tail the daemon with: journalctl -u gtbot@${RUN_ID} -f"
