#!/usr/bin/env bash
# Read-only evidence recovery for the completed approved lab; never migration.
set -Eeuo pipefail
umask 077
ROOT="$(cd "$(dirname "$0")/../.." && pwd -P)"
STATE="$ROOT/.local/mysql-approved-bootstrap-rehearsal"
[[ "$#" == 1 && "$1" == "--confirm-read-only-completed-lab" ]] || exit 64
[[ -d "$STATE" && ! -L "$STATE" && ! -S "$STATE/mysql.sock" ]] || exit 65
[[ ! -e "$STATE/reinspection-exit.json" ]] || { echo "Existing reinspection receipt refused."; exit 66; }
php -r '
$s=$argv[1]; $r=json_decode(file_get_contents("$s/receipt.json"),true,512,JSON_THROW_ON_ERROR);
$o=json_decode(file_get_contents("$s/owner.json"),true,512,JSON_THROW_ON_ERROR);
$c=json_decode(file_get_contents("$s/child-exit.json"),true,512,JSON_THROW_ON_ERROR);
if(realpath($s)!==$s || (fileperms($s)&0777)!==0700 || fileowner($s)!==posix_geteuid()
 || $o["root"]!==$argv[2] || $o["policy"]!=="owner-approved-local-temporary-super-locked-definer"
 || $r["status"]!=="DDL_ONLY_PASS" || $r["failure"]!==null || isset($r["collectionFailure"])
 || count($r["completed"])!==229 || count($r["ledger"])!==229 || $c["migrationChildExit"]!==0) exit(67);
' "$STATE" "$ROOT"
PID=""
VERIFY=-1
cleanup() {
    local result=$?
    trap - EXIT INT TERM
    if [[ -n "$PID" ]] && kill -0 "$PID" 2>/dev/null; then
        if [[ -S "$STATE/mysql.sock" ]]; then
            mysqladmin --no-defaults --socket="$STATE/mysql.sock" -u root shutdown \
                > "$STATE/reinspection-shutdown.log" 2>&1 || result=93
        else
            kill -TERM "$PID" 2>/dev/null || true
            result=94
        fi
        wait "$PID" || result=95
    fi
    printf '{"verificationChildExit":%d,"reinspectionSupervisorExit":%d}\n' "$VERIFY" "$result" > "$STATE/reinspection-exit.json"
    echo "Read-only reinspection finished with exit $result; original failure receipts retained."
    exit "$result"
}
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
    > "$STATE/reinspection-server.log" 2>&1 &
PID=$!
for attempt in $(seq 1 120); do
    if mysqladmin --no-defaults --socket="$STATE/mysql.sock" -u root ping > /dev/null 2>&1; then break; fi
    kill -0 "$PID" 2>/dev/null || exit 68
    sleep 1
done
set +e
php "$ROOT/scripts/database/mysql-approved-evidence.php" > "$STATE/reinspection-verification.log" 2>&1
VERIFY=$?
set -e
exit "$VERIFY"
