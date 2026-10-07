#!/usr/bin/env bash
# Private, local-only authority rehearsal. Never a deployment installer.
set -Eeuo pipefail
umask 077
ROOT="$(cd "$(dirname "$0")/../.." && pwd -P)"
STATE="$ROOT/.local/mysql-approved-bootstrap-rehearsal"
[[ "${1:-}" == "--confirm-owner-approved-local-policy" && "$#" == 1 ]] || exit 64
[[ ! -e "$STATE" && ! -L "$STATE" ]] || { echo "Existing lab refused; no reset or replay."; exit 65; }
[[ "$(mysqld --version)" == *"8.0.42"* ]] || exit 66
mkdir -m 700 "$STATE"
mkdir -m 700 "$STATE/data" "$STATE/bootstrap" "$STATE/bootstrap/cache" "$STATE/storage"
printf '{"root":"%s","schemas":["agendaally_approved_empty_lab"],"policy":"owner-approved-local-temporary-super-locked-definer","priorEvidenceAvailable":false}\n' "$ROOT" > "$STATE/owner.json"
printf '%s\n' 'Owner approved isolated local temporary SUPER for dedicated bootstrap; binary logging ON; trusted creators OFF; unchanged guards; revoke/lock definer after bootstrap; no production, normal DB, external services, workers or financial execution. Prior private evidence is unavailable.' > "$STATE/approval.txt"
php "$ROOT/scripts/database/mysql-approved-source.php" before
mysqld --no-defaults --initialize-insecure --datadir="$STATE/data" > "$STATE/initialize.log" 2>&1
PID=""
cleanup() {
    local result=$?
    trap - EXIT INT TERM
    if [[ -n "$PID" ]] && kill -0 "$PID" 2>/dev/null; then
        # This exact local root socket never reaches a normal server.
        if [[ -S "$STATE/mysql.sock" ]]; then
            mysql --no-defaults --socket="$STATE/mysql.sock" -u root \
                < "$STATE/revoke.sql" > "$STATE/revoke.log" 2>&1 || result=91
            php "$ROOT/scripts/database/mysql-approved-evidence.php" \
                > "$STATE/verification.log" 2>&1 || result=92
            mysqladmin --no-defaults --socket="$STATE/mysql.sock" -u root shutdown \
                > "$STATE/shutdown.log" 2>&1 || result=93
        else
            kill -TERM "$PID" 2>/dev/null || true
            result=94
        fi
        wait "$PID" || result=95
    fi
    php "$ROOT/scripts/database/mysql-approved-source.php" after \
        > "$STATE/source-verification.log" 2>&1 || result=96
    printf '{"supervisorExit":%d}\n' "$result" > "$STATE/supervisor-exit.json"
    echo "Local rehearsal finished with supervisor exit $result; private evidence retained."
    exit "$result"
}
# Prepared before privilege elevation; cleanup revokes even on child failure.
cat > "$STATE/revoke.sql" <<'SQL'
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'lab_bootstrap'@'localhost';
ALTER USER 'lab_bootstrap'@'localhost' ACCOUNT LOCK;
SQL
trap cleanup EXIT
trap 'exit 130' INT
trap 'exit 143' TERM
mysqld --no-defaults --datadir="$STATE/data" --socket="$STATE/mysql.sock" \
    --pid-file="$STATE/mysql.pid" --skip-networking --mysqlx=OFF \
    --log-bin="$STATE/data/binlog" --server-id=5601 --binlog-format=ROW \
    --log-bin-trust-function-creators=OFF --event-scheduler=OFF \
    --innodb-buffer-pool-size=64M --innodb-redo-log-capacity=32M \
    --innodb-ft-cache-size=1600000 --innodb-ft-total-cache-size=32000000 \
    --transaction-isolation=REPEATABLE-READ --default-time-zone=+00:00 \
    > "$STATE/server.log" 2>&1 &
PID=$!
for attempt in $(seq 1 120); do
    if mysqladmin --no-defaults --socket="$STATE/mysql.sock" -u root ping > /dev/null 2>&1; then break; fi
    kill -0 "$PID" 2>/dev/null || exit 67
    sleep 1
done
mysql --no-defaults --socket="$STATE/mysql.sock" -u root > "$STATE/provision.log" 2>&1 <<'SQL'
CREATE DATABASE agendaally_approved_empty_lab CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'lab_bootstrap'@'localhost';
CREATE USER 'lab_app'@'localhost';
GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,ALTER,DROP,INDEX,REFERENCES,TRIGGER ON agendaally_approved_empty_lab.* TO 'lab_bootstrap'@'localhost';
GRANT SUPER ON *.* TO 'lab_bootstrap'@'localhost';
GRANT SELECT,INSERT,UPDATE,DELETE ON agendaally_approved_empty_lab.* TO 'lab_app'@'localhost';
SHOW GRANTS FOR 'lab_bootstrap'@'localhost';
SHOW GRANTS FOR 'lab_app'@'localhost';
SELECT @@version,@@datadir,@@skip_networking,@@log_bin,@@log_bin_trust_function_creators,@@event_scheduler;
SQL
set +e
php -d disable_functions=curl_exec,curl_multi_exec,fsockopen,pfsockopen,stream_socket_client,socket_connect,mail,exec,shell_exec,proc_open,popen,system,passthru \
    "$ROOT/scripts/database/mysql-bootstrap-rehearsal.php" --confirm-approved-owned-empty-lab \
    > "$STATE/migrations.log" 2>&1
CHILD=$?
set -e
printf '{"migrationChildExit":%d}\n' "$CHILD" > "$STATE/child-exit.json"
# Calculate narrow retained definer privileges from the installed unchanged
# bodies; only OLD/NEW references and SELECTs are supported, never SET NEW/DML.
php "$ROOT/scripts/database/mysql-approved-evidence.php" --prepare-definer \
    > "$STATE/definer-preparation.log" 2>&1
exit "$CHILD"
