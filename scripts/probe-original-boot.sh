#!/usr/bin/env bash
set -euo pipefail
root="$(cd "$(dirname "$0")/.." && pwd)"
vendor="${HARDENING_VENDOR_AUTOLOAD:-/tmp/agendaally-hardening-runtime/vendor/autoload.php}"
[[ -f "$vendor" ]] || { echo "Restore locked dependencies with --no-scripts --no-plugins first." >&2; exit 1; }
runtime="$(mktemp -d /tmp/agendaally-baseline-test.XXXXXX)"
trap 'rm -rf -- "$runtime"' EXIT
touch "$runtime/.baseline-owned"
source="$root/.migration-backup/backend"
# Allowlisted code only: no .env, caches, uploads, dumps or original storage.
for directory in app config routes resources lang; do
  [[ ! -d "$source/$directory" ]] || cp -R "$source/$directory" "$runtime/"
done
mkdir -p "$runtime/bootstrap/cache" "$runtime/storage/framework/"{cache,sessions,views} "$runtime/storage/logs"
cp "$source/bootstrap/app.php" "$runtime/bootstrap/app.php"
cp "$source/composer.json" "$runtime/composer.json"
ln -s "$(dirname "$vendor")" "$runtime/vendor"
php_binary="$(php -r 'echo PHP_BINARY;')"
# Independent SDK transports fail too, not just Laravel's HTTP facade.
disabled="curl_exec,curl_multi_exec,fsockopen,pfsockopen,stream_socket_client,socket_connect,mail,exec,shell_exec,passthru,proc_open,popen,system"
env -i PATH="$PATH" HOME="$runtime" APP_ENV=testing APP_DEBUG=false \
  APP_BASE_PATH="$runtime" DB_CONNECTION=sqlite DB_DATABASE=:memory: \
  CACHE_STORE=array CACHE_DRIVER=array SESSION_DRIVER=array QUEUE_CONNECTION=null \
  MAIL_MAILER=array LOG_CHANNEL=stderr \
  "$php_binary" -d allow_url_fopen=0 -d "disable_functions=$disabled" "$root/scripts/probe-original-boot.php" "$runtime" "$vendor"