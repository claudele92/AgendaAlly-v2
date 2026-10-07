#!/usr/bin/env bash
# Owner-approved synthetic-only campaign. No configurable server/normal DB input.
set -Eeuo pipefail
umask 077
ROOT="$(cd "$(dirname "$0")/../.." && pwd -P)"
STATE="$ROOT/.local/mysql-synthetic-recovery"
[[ "$#" == 1 && "$1" == --confirm-approved-synthetic-only ]] || exit 64
[[ ! -e "$STATE" && ! -L "$STATE" ]] || { echo "Existing campaign refused; evidence is never reset."; exit 65; }
[[ "$(mysqld --version)" == *8.0.42* ]] || exit 66
mkdir -m 700 "$STATE"
printf '%s\n' 'Owner approved NEW socket-only synthetic MySQL upgrade and second-instance logical recovery only. Freeze historical hashes/ledger; exact additive allowlist; unchanged reviewed privilege policy; independent lab keys; no normal/live data, old dumps, external transports, workers, financial operations or down migrations. Stop on unexplained mismatch.' > "$STATE/approval.txt"
for side in source restore; do
  mkdir -m 700 "$STATE/$side" "$STATE/$side/data" "$STATE/$side/bootstrap" "$STATE/$side/bootstrap/cache" "$STATE/$side/storage"
done
SOURCE_PID="" RESTORE_PID=""
cleanup() {
  result=$?
  trap - EXIT INT TERM
  for side in source restore; do
    if [[ -S "$STATE/$side/mysql.sock" ]]; then
      # Always revoke/lock, even when failure precedes the full retained grant map.
      mysql --no-defaults --socket="$STATE/$side/mysql.sock" -u root \
        -e "REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'lab_bootstrap'@'localhost'; ALTER USER 'lab_bootstrap'@'localhost' ACCOUNT LOCK;" \
        > "$STATE/$side/emergency-revoke.log" 2>&1 || result=91
      if [[ -f "$STATE/$side/revoke.sql" ]]; then
        mysql --no-defaults --socket="$STATE/$side/mysql.sock" -u root < "$STATE/$side/revoke.sql" \
          > "$STATE/$side/final-revoke.log" 2>&1 || result=92
      fi
      mysqladmin --no-defaults --socket="$STATE/$side/mysql.sock" -u root shutdown \
        > "$STATE/$side/shutdown.log" 2>&1 || result=93
    fi
  done
  for pid in "$SOURCE_PID" "$RESTORE_PID"; do
    [[ -z "$pid" ]] || { kill -TERM "$pid" 2>/dev/null || true; wait "$pid" || result=94; }
  done
  printf '{"supervisorExit":%d}\n' "$result" > "$STATE/supervisor-exit.json"
  echo "Synthetic campaign stopped with exit $result; private evidence retained."
  exit "$result"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM
PHP=(php -d disable_functions=curl_exec,curl_multi_exec,fsockopen,pfsockopen,stream_socket_client,socket_connect,mail,exec,shell_exec,proc_open,popen,system,passthru)
"${PHP[@]}" "$ROOT/scripts/database/mysql-recovery-runtime.php" freeze source > "$STATE/freeze.log" 2>&1
start_side() {
  local side="$1" id="$2"
  mysqld --no-defaults --initialize-insecure --datadir="$STATE/$side/data" > "$STATE/$side/initialize.log" 2>&1
  mysqld --no-defaults --datadir="$STATE/$side/data" --socket="$STATE/$side/mysql.sock" \
    --pid-file="$STATE/$side/mysql.pid" --skip-networking --mysqlx=OFF \
    --log-bin="$STATE/$side/data/binlog" --server-id="$id" --binlog-format=ROW \
    --log-bin-trust-function-creators=OFF --event-scheduler=OFF \
    --innodb-buffer-pool-size=64M --innodb-redo-log-capacity=32M \
    --innodb-ft-cache-size=1600000 --innodb-ft-total-cache-size=32000000 \
    --transaction-isolation=REPEATABLE-READ --default-time-zone=+00:00 \
    > "$STATE/$side/server.log" 2>&1 &
  if [[ "$side" == source ]]; then SOURCE_PID=$!; else RESTORE_PID=$!; fi
  for attempt in $(seq 1 120); do
    mysqladmin --no-defaults --socket="$STATE/$side/mysql.sock" -u root ping > /dev/null 2>&1 && break
    sleep 1
  done
  mysql --no-defaults --socket="$STATE/$side/mysql.sock" -u root > "$STATE/$side/provision.log" 2>&1 <<'SQL'
CREATE DATABASE agendaally_synthetic_recovery CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'lab_bootstrap'@'localhost';
CREATE USER 'lab_app'@'localhost';
GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,ALTER,DROP,INDEX,REFERENCES,TRIGGER ON agendaally_synthetic_recovery.* TO 'lab_bootstrap'@'localhost';
GRANT SUPER ON *.* TO 'lab_bootstrap'@'localhost';
GRANT SELECT,INSERT,UPDATE,DELETE ON agendaally_synthetic_recovery.* TO 'lab_app'@'localhost';
SQL
}
STEP=0
run() {
  local mode="$1" side="${2:-source}"
  STEP=$((STEP+1))
  local trace
  trace="$(printf '%02d-%s' "$STEP" "$mode")"
  set +e
  "${PHP[@]}" "$ROOT/scripts/database/mysql-recovery-runtime.php" "$mode" "$side" > "$STATE/$side/$trace.log" 2>&1
  local code=$?
  set -e
  # Numbered receipts are immutable, including repeated definer preparations.
  cp "$STATE/$side/$trace.log" "$STATE/$side/$mode.log"
  printf '{"childExit":%d}\n' "$code" > "$STATE/$side/$trace-exit.json"
  printf '{"childExit":%d}\n' "$code" > "$STATE/$side/$mode-exit.json"
  [[ "$code" == 0 ]] || { echo "STOP: $mode exited $code"; exit "$code"; }
}
lock_definer() {
  run definer "$1"
  mysql --no-defaults --socket="$STATE/$1/mysql.sock" -u root < "$STATE/$1/revoke.sql" \
    > "$STATE/$1/revoke.log" 2>&1
}
start_side source 5801
run bootstrap
lock_definer source
run fixtures
run before
run qualify
mysql --no-defaults --socket="$STATE/source/mysql.sock" -u root > "$STATE/source/upgrade-authority.log" 2>&1 <<'SQL'
ALTER USER 'lab_bootstrap'@'localhost' ACCOUNT UNLOCK;
GRANT SELECT,INSERT,CREATE,ALTER,INDEX,REFERENCES,TRIGGER ON agendaally_synthetic_recovery.* TO 'lab_bootstrap'@'localhost';
GRANT SUPER ON *.* TO 'lab_bootstrap'@'localhost';
SQL
run upgrade
lock_definer source
run after
run compare-upgrade
run probe
run backup-preconditions
START_NS="$(date +%s%N)"
# All writers are quiescent. Explicit synthetic schema, same local socket only.
# Administrative backup reads ONLY the named synthetic schema. The runtime
# account never receives TRIGGER/DDL merely to make mysqldump enumerate triggers.
mysqldump --no-defaults --socket="$STATE/source/mysql.sock" -u root \
  --single-transaction --skip-lock-tables --no-tablespaces --set-gtid-purged=OFF \
  --skip-add-locks --hex-blob --triggers --skip-comments agendaally_synthetic_recovery > "$STATE/synthetic.sql" 2> "$STATE/dump.log"
printf '{"backupCompletedNs":"%s","startedNs":"%s"}\n' "$(date +%s%N)" "$START_NS" > "$STATE/backup-time.json"
run backup-receipt
start_side restore 5802
run restore-preconditions restore
set +e
mysql --no-defaults --socket="$STATE/restore/mysql.sock" -u lab_bootstrap agendaally_synthetic_recovery \
  < "$STATE/synthetic.sql" > "$STATE/restore/import.log" 2>&1
IMPORT=$?
set -e
printf '{"childExit":%d}\n' "$IMPORT" > "$STATE/restore/import-exit.json"
[[ "$IMPORT" == 0 ]] || { echo "STOP: isolated import exited $IMPORT"; exit "$IMPORT"; }
lock_definer restore
run restored restore
run compare-restore restore
run recover-keys restore
run probe restore
run invalidate restore
run final restore
run preservation source
run assessment restore
