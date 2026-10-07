#!/usr/bin/env bash
# Separate one-shot temporal campaign; never restart the earlier recovery labs.
set -Eeuo pipefail
umask 077
ROOT="$(cd "$(dirname "$0")/../.." && pwd -P)"
STATE="$ROOT/.local/mysql-temporal-recovery-qualified"
CUSTODY="$ROOT/.local/mysql-temporal-key-custody"
[[ $# == 1 && $1 == --confirm-approved-synthetic-temporal-only ]] || exit 64
[[ ! -e "$STATE" && ! -L "$STATE" && ! -e "$CUSTODY" && ! -L "$CUSTODY" ]] || {
  echo "Existing temporal campaign/custody refused; failed evidence is never reset."; exit 65;
}
[[ "$(mysqld --version)" == *8.0.42* ]] || exit 66
mkdir -m 700 "$STATE"
printf '%s\n' 'Separate disposable synthetic-only temporal recovery approval. NEW socket-only instances and NEW data only. Direct synthetic outcome fixtures after a fresh consistent backup; independent local synthetic evidence; read-only restored quarantine. No normal/live DB, old dumps, provider/SMTP, handlers, workers, money or production activation.' > "$STATE/approval.txt"
for side in source restore; do
  mkdir -m 700 "$STATE/$side" "$STATE/$side/data" "$STATE/$side/bootstrap" \
    "$STATE/$side/bootstrap/cache" "$STATE/$side/storage"
done
PIDS=()
declare -A LOCKED=()
cleanup() {
  result=$?
  trap - EXIT INT TERM
  for side in source restore; do
    if [[ -S "$STATE/$side/mysql.sock" ]]; then
      if [[ ${LOCKED[$side]:-0} != 1 ]]; then
      mysql --no-defaults --socket="$STATE/$side/mysql.sock" -u root \
        -e "REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'lab_bootstrap'@'localhost'; ALTER USER 'lab_bootstrap'@'localhost' ACCOUNT LOCK;" \
        > "$STATE/$side/emergency-revoke.log" 2>&1 || result=91
      if [[ -f "$STATE/$side/revoke.sql" ]]; then
        mysql --no-defaults --socket="$STATE/$side/mysql.sock" -u root < "$STATE/$side/revoke.sql" \
          > "$STATE/$side/final-revoke.log" 2>&1 || result=92
      fi
      fi
      mysqladmin --no-defaults --socket="$STATE/$side/mysql.sock" -u root shutdown \
        > "$STATE/$side/shutdown.log" 2>&1 || result=93
    fi
  done
  for pid in "${PIDS[@]}"; do
    kill -TERM "$pid" 2>/dev/null || true
    wait "$pid" || result=94
  done
  printf '{"supervisorExit":%d}\n' "$result" > "$STATE/supervisor-exit.json"
  echo "Temporal synthetic campaign stopped with exit $result; private evidence retained."
  exit "$result"
}
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM
PHP=(php -d disable_functions=curl_exec,curl_multi_exec,fsockopen,pfsockopen,stream_socket_client,socket_connect,mail,exec,shell_exec,proc_open,popen,system,passthru)
run() {
  local mode="$1" side="$2" code
  set +e
  "${PHP[@]}" "$ROOT/scripts/database/mysql-temporal-recovery-runtime.php" "$mode" "$side" > "$STATE/$side/$mode.log" 2>&1
  code=$?
  set -e
  printf '{"childExit":%d}\n' "$code" > "$STATE/$side/$mode-exit.json"
  [[ $code == 0 ]] || { echo "STOP: $mode exited $code"; exit "$code"; }
}
start() {
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
  PIDS+=("$!")
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
lock_definer() {
  run definer "$1"
  mysql --no-defaults --socket="$STATE/$1/mysql.sock" -u root < "$STATE/$1/revoke.sql" > "$STATE/$1/revoke.log" 2>&1
  LOCKED[$1]=1
}
run freeze source
start source 5861
run bootstrap source
lock_definer source
run fixtures source
run point source
mysql --no-defaults --socket="$STATE/source/mysql.sock" -u root \
  -e 'SET GLOBAL read_only=ON; SET GLOBAL super_read_only=ON;' > "$STATE/source/freeze-writes.log" 2>&1
run backup-preconditions source
set +e
mysqldump --no-defaults --socket="$STATE/source/mysql.sock" -u root \
  --single-transaction --skip-lock-tables --skip-add-locks --no-tablespaces --set-gtid-purged=OFF \
  --hex-blob --triggers --skip-comments agendaally_synthetic_recovery > "$STATE/synthetic.sql" 2> "$STATE/dump.log"
code=$?
set -e
printf '{"childExit":%d}\n' "$code" > "$STATE/dump-exit.json"
[[ $code == 0 ]] || exit "$code"
run backup-receipt source
# Only this new synthetic source becomes writable for direct post-point fixtures.
mysql --no-defaults --socket="$STATE/source/mysql.sock" -u root \
  -e 'SET GLOBAL super_read_only=OFF; SET GLOBAL read_only=OFF;' > "$STATE/source/open-synthetic-gap.log" 2>&1
run outcomes source
mysql --no-defaults --socket="$STATE/source/mysql.sock" -u root \
  -e 'SET GLOBAL read_only=ON; SET GLOBAL super_read_only=ON;' > "$STATE/source/close-synthetic-gap.log" 2>&1
run later source
start restore 5862
run restore-preconditions restore
set +e
mysql --no-defaults --socket="$STATE/restore/mysql.sock" -u lab_bootstrap agendaally_synthetic_recovery \
  < "$STATE/synthetic.sql" > "$STATE/restore/import.log" 2>&1
code=$?
set -e
printf '{"childExit":%d}\n' "$code" > "$STATE/restore/import-exit.json"
[[ $code == 0 ]] || exit "$code"
lock_definer restore
# No app is served. Restore is blocked at DB level before any reconciliation.
mysql --no-defaults --socket="$STATE/restore/mysql.sock" -u root \
  -e 'SET GLOBAL read_only=ON; SET GLOBAL super_read_only=ON;' > "$STATE/restore/quarantine.log" 2>&1
run restored restore
run reconcile restore
run final restore
run preservation source
run assessment restore
