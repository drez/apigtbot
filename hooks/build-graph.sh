#!/usr/bin/env bash
# Per-project build hook: refresh the project's Graphify graph after `gc build`.
# Installed into <project>/hooks/. Runs only when MY_GRAPHIFY_ON_BUILD=1 (the gc
# wrapper sources the project .env before running hooks). Non-blocking.
set -euo pipefail
[ "${MY_GRAPHIFY_ON_BUILD:-0}" = "1" ] || exit 0

ENGINE=/path/to/gc/scripts/graphify-build.sh
ROOT="${PWD%/.admin}"   # gc wrapper runs hooks with PWD at project root (or .admin)

if [ ! -d "$ROOT/.admin" ]; then
  echo "[build-graph hook] cannot locate project root from PWD=$PWD; skipping" >&2
  exit 0
fi

mkdir -p "$ROOT/graphify-out"
LOG="$ROOT/graphify-out/build-graph.log"
echo "[build-graph hook] refreshing graph for $ROOT (background)"
nohup "$ENGINE" project "$ROOT" > "$LOG" 2>&1 &
