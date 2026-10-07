#!/usr/bin/env bash
set -euo pipefail
root="$(cd "$(dirname "$0")/.." && pwd)"
backend="$root/.migration-backup/backend"
# This suite has a separate bootstrap: no original .env, original providers,
# original TestCase, migration command, seeder, or real provider transport.
autoload="${HARDENING_VENDOR_AUTOLOAD:-$backend/vendor/autoload.php}"
if [[ ! -f "$autoload" ]]; then
  echo "Missing reviewed dependencies. See docs/security/original-hardening.md." >&2
  exit 1
fi
vendor="$(dirname "$autoload")"
php_binary="$(php -r 'echo PHP_BINARY;')"
cd "$backend"
# PHP 8.4 accepts multiple files per lint invocation. Starting PHP once also
# avoids repeatedly entering the environment wrapper in restricted runners.
mapfile -d '' php_files < <(find app routes tests/Hardening -name '*.php' -print0)
"$php_binary" -l "${php_files[@]}" > /tmp/agendaally-hardening-lint.log
HARDENING_VENDOR_AUTOLOAD="$autoload" "$php_binary" "$vendor/bin/phpunit" -c phpunit-hardening.xml "$@"