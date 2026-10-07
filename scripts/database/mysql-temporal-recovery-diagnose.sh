#!/usr/bin/env bash
set -Eeuo pipefail
umask 077
ROOT="$(cd "$(dirname "$0")/../.." && pwd -P)"
STATE="$ROOT/.local/mysql-temporal-recovery-diagnostic"
PRIOR="$ROOT/.local/mysql-temporal-recovery-completed"
[[ $# == 1 && $1 == --confirm-approved-synthetic-read-only-copy ]] || exit 64
[[ ! -e "$STATE" && ! -L "$STATE" ]] || { echo 'Existing diagnostic evidence refused'; exit 65; }
[[ "$(mysqld --version)" == *8.0.42* ]] || exit 66
mkdir -m 700 "$STATE" "$STATE/source" "$STATE/source/data"
printf '%s\n' 'Assigned disposable synthetic temporal scope only: NEW read-only diagnostic COPY of this campaign synthetic source. Failed instances and all receipts unchanged; no original restart, DML, DDL, provider/SMTP/handlers/workers or production.' > "$STATE/approval.txt"
PID=""
cleanup() {
  result=$?
  trap - EXIT INT TERM
  if [[ -S "$STATE/source/mysql.sock" ]]; then
    # The copied definer was already locked/revoked and is checked natively.
    # Do not attempt forbidden DCL or disable quarantine to perform cleanup.
    mysqladmin --no-defaults --socket="$STATE/source/mysql.sock" -u root shutdown > "$STATE/source/shutdown.log" 2>&1 || result=93
  fi
  if [[ -n "$PID" ]]; then
    kill -TERM "$PID" 2>/dev/null || true
    wait "$PID" || result=94
  fi
  printf '{"supervisorExit":%d}\n' "$result" > "$STATE/supervisor-exit.json"
  echo "Read-only synthetic diagnosis stopped with exit $result"
  exit "$result"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM
PHP=(php -d disable_functions=curl_exec,curl_multi_exec,fsockopen,pfsockopen,stream_socket_client,socket_connect,mail,exec,shell_exec,proc_open,popen,system,passthru)
run() {
  local mode="$1" code
  set +e
  "${PHP[@]}" "$ROOT/scripts/database/mysql-temporal-recovery-diagnose.php" "$mode" > "$STATE/$mode.log" 2>&1
  code=$?
  set -e
  printf '{"childExit":%d}\n' "$code" > "$STATE/$mode-exit.json"
  [[ $code == 0 ]] || exit "$code"
}
run before
cp -a "$PRIOR/source/data/." "$STATE/source/data/"
run relocate
mysqld --no-defaults --datadir="$STATE/source/data" --socket="$STATE/source/mysql.sock" \
  --pid-file="$STATE/source/mysql.pid" --skip-networking --mysqlx=OFF \
  --read-only=ON --super-read-only=ON \
  --log-bin="$STATE/source/data/binlog" --server-id=5865 --binlog-format=ROW \
  --log-bin-trust-function-creators=OFF --event-scheduler=OFF \
  --innodb-buffer-pool-size=64M --innodb-redo-log-capacity=32M \
  --innodb-ft-cache-size=1600000 --innodb-ft-total-cache-size=32000000 \
  --transaction-isolation=REPEATABLE-READ --default-time-zone=+00:00 \
  > "$STATE/source/server.log" 2>&1 &
PID=$!
for attempt in $(seq 1 120); do
  mysqladmin --no-defaults --socket="$STATE/source/mysql.sock" -u root ping > /dev/null 2>&1 && break
  sleep 1
done
run snapshot
run after
