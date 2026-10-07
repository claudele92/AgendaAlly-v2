#!/usr/bin/env bash
# Separately approved bounded retry. Never repeat bootstrap or populated upgrade.
set -Eeuo pipefail
umask 077
ROOT="$(cd "$(dirname "$0")/../.." && pwd -P)"
STATE="$ROOT/.local/mysql-synthetic-recovery"
RETRY="$STATE/retry"
THIRD="$STATE/restore-retry"
[[ "$#" == 1 && "$1" == --confirm-approved-third-instance-retry ]] || exit 64
[[ -d "$STATE" && ! -L "$STATE" && ! -e "$RETRY" && ! -e "$THIRD" ]] || exit 65
[[ ! -S "$STATE/source/mysql.sock" && ! -S "$STATE/restore/mysql.sock" ]] || exit 66
[[ "$(mysqld --version)" == *8.0.42* ]] || exit 67
mkdir -m 700 "$RETRY" "$THIRD" "$THIRD/data" "$THIRD/bootstrap" "$THIRD/bootstrap/cache" "$THIRD/storage"
printf '%s\n' 'Owner approved bounded retry: completed synthetic source READ-ONLY solely for corrected consistent synthetic backup with restore locks omitted; NEW third disposable target; preserve failed target unchanged; same privilege policy; no populated migration replay, normal/live data, providers, SMTP, workers, external services or money.' > "$RETRY/approval.txt"
SOURCE_PID="" THIRD_PID="" STEP=0
PHP=(php -d disable_functions=curl_exec,curl_multi_exec,fsockopen,pfsockopen,stream_socket_client,socket_connect,mail,exec,shell_exec,proc_open,popen,system,passthru)
cleanup() {
  result=$?
  trap - EXIT INT TERM
  if [[ -S "$THIRD/mysql.sock" ]]; then
    mysql --no-defaults --socket="$THIRD/mysql.sock" -u root \
      -e "REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'lab_bootstrap'@'localhost'; ALTER USER 'lab_bootstrap'@'localhost' ACCOUNT LOCK;" \
      > "$THIRD/emergency-revoke.log" 2>&1 || result=91
    if [[ -f "$THIRD/revoke.sql" ]]; then
      mysql --no-defaults --socket="$THIRD/mysql.sock" -u root < "$THIRD/revoke.sql" \
        > "$THIRD/final-revoke.log" 2>&1 || result=92
    fi
    mysqladmin --no-defaults --socket="$THIRD/mysql.sock" -u root shutdown > "$THIRD/shutdown.log" 2>&1 || result=93
  fi
  # Source is never re-provisioned or granted anything during this retry.
  if [[ -S "$STATE/source/mysql.sock" ]]; then
    mysqladmin --no-defaults --socket="$STATE/source/mysql.sock" -u root shutdown > "$RETRY/source-shutdown.log" 2>&1 || result=94
  fi
  for pid in "$SOURCE_PID" "$THIRD_PID"; do
    [[ -z "$pid" ]] || { kill -TERM "$pid" 2>/dev/null || true; wait "$pid" || result=95; }
  done
  printf '{"supervisorExit":%d}\n' "$result" > "$RETRY/supervisor-exit.json"
  echo "Bounded synthetic recovery retry stopped with exit $result; all evidence retained."
  exit "$result"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM
run() {
  local mode="$1" side="$2" destination="$RETRY"
  [[ "$side" != restore-retry ]] || destination="$THIRD"
  STEP=$((STEP+1))
  local trace
  trace="$(printf '%02d-%s' "$STEP" "$mode")"
  set +e
  "${PHP[@]}" "$ROOT/scripts/database/mysql-recovery-runtime.php" "$mode" "$side" > "$destination/$trace.log" 2>&1
  local code=$?
  set -e
  cp "$destination/$trace.log" "$destination/$mode.log"
  printf '{"childExit":%d}\n' "$code" > "$destination/$trace-exit.json"
  printf '{"childExit":%d}\n' "$code" > "$destination/$mode-exit.json"
  [[ "$code" == 0 ]] || { echo "STOP: $side $mode exited $code"; exit "$code"; }
}
run retry-freeze source
mysqld --no-defaults --datadir="$STATE/source/data" --socket="$STATE/source/mysql.sock" \
  --pid-file="$STATE/source/mysql.pid" --skip-networking --mysqlx=OFF \
  --log-bin="$STATE/source/data/binlog" --server-id=5801 --binlog-format=ROW \
  --log-bin-trust-function-creators=OFF --event-scheduler=OFF \
  --innodb-buffer-pool-size=64M --innodb-redo-log-capacity=32M \
  --transaction-isolation=REPEATABLE-READ --default-time-zone=+00:00 \
  --read-only=ON --super-read-only=ON > "$RETRY/source-server.log" 2>&1 &
SOURCE_PID=$!
for attempt in $(seq 1 120); do
  mysqladmin --no-defaults --socket="$STATE/source/mysql.sock" -u root ping > /dev/null 2>&1 && break
  kill -0 "$SOURCE_PID" 2>/dev/null || exit 68
  sleep 1
done
run retry-backup-preconditions source
START_NS="$(date +%s%N)"
set +e
mysqldump --no-defaults --socket="$STATE/source/mysql.sock" -u root \
  --single-transaction --skip-lock-tables --skip-add-locks --no-tablespaces --set-gtid-purged=OFF \
  --hex-blob --triggers --skip-comments agendaally_synthetic_recovery > "$RETRY/synthetic.sql" 2> "$RETRY/dump.log"
DUMP=$?
set -e
printf '{"childExit":%d}\n' "$DUMP" > "$RETRY/dump-exit.json"
[[ "$DUMP" == 0 ]] || exit "$DUMP"
printf '{"backupCompletedNs":"%s","startedNs":"%s"}\n' "$(date +%s%N)" "$START_NS" > "$RETRY/backup-time.json"
run retry-backup-receipt source
run retry-preservation source
mysqladmin --no-defaults --socket="$STATE/source/mysql.sock" -u root shutdown > "$RETRY/source-shutdown.log" 2>&1
wait "$SOURCE_PID"
SOURCE_PID=""
mysqld --no-defaults --initialize-insecure --datadir="$THIRD/data" > "$THIRD/initialize.log" 2>&1
mysqld --no-defaults --datadir="$THIRD/data" --socket="$THIRD/mysql.sock" \
  --pid-file="$THIRD/mysql.pid" --skip-networking --mysqlx=OFF \
  --log-bin="$THIRD/data/binlog" --server-id=5803 --binlog-format=ROW \
  --log-bin-trust-function-creators=OFF --event-scheduler=OFF \
  --innodb-buffer-pool-size=64M --innodb-redo-log-capacity=32M \
  --innodb-ft-cache-size=1600000 --innodb-ft-total-cache-size=32000000 \
  --transaction-isolation=REPEATABLE-READ --default-time-zone=+00:00 > "$THIRD/server.log" 2>&1 &
THIRD_PID=$!
for attempt in $(seq 1 120); do
  mysqladmin --no-defaults --socket="$THIRD/mysql.sock" -u root ping > /dev/null 2>&1 && break
  kill -0 "$THIRD_PID" 2>/dev/null || exit 69
  sleep 1
done
mysql --no-defaults --socket="$THIRD/mysql.sock" -u root > "$THIRD/provision.log" 2>&1 <<'SQL'
CREATE DATABASE agendaally_synthetic_recovery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'lab_bootstrap'@'localhost';
CREATE USER 'lab_app'@'localhost';
GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,ALTER,DROP,INDEX,REFERENCES,TRIGGER ON agendaally_synthetic_recovery.* TO 'lab_bootstrap'@'localhost';
GRANT SUPER ON *.* TO 'lab_bootstrap'@'localhost';
GRANT SELECT,INSERT,UPDATE,DELETE ON agendaally_synthetic_recovery.* TO 'lab_app'@'localhost';
SQL
run restore-preconditions restore-retry
set +e
mysql --no-defaults --socket="$THIRD/mysql.sock" -u lab_bootstrap agendaally_synthetic_recovery \
  < "$RETRY/synthetic.sql" > "$THIRD/import.log" 2>&1
IMPORT=$?
set -e
printf '{"childExit":%d}\n' "$IMPORT" > "$THIRD/import-exit.json"
[[ "$IMPORT" == 0 ]] || { echo "STOP: new third-instance import exited $IMPORT"; exit "$IMPORT"; }
run definer restore-retry
mysql --no-defaults --socket="$THIRD/mysql.sock" -u root < "$THIRD/revoke.sql" > "$THIRD/revoke.log" 2>&1
run restored restore-retry
run compare-restore restore-retry
run recover-keys restore-retry
run probe restore-retry
run invalidate restore-retry
run final restore-retry
run assessment restore-retry
