#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SOURCE="$ROOT/.migration-backup/backend"
RUNTIME="$ROOT/.local/agendaally-preview/backend"
MARKER="$RUNTIME/.original-http-preview-owned"
STORAGE="$RUNTIME/storage"

if [[ ! -d "$SOURCE" || ! -f "$SOURCE/composer.json" || ! -f "$SOURCE/composer.lock" ]]; then
  echo "Original backend source and locked Composer files were not found." >&2
  exit 1
fi
if [[ ! -e "$RUNTIME" ]]; then
  mkdir -p "$RUNTIME"
  for path in app artisan bootstrap config composer.json composer.lock public resources routes; do
    cp -a "$SOURCE/$path" "$RUNTIME/"
  done
  mkdir -p "$RUNTIME/database/factories" "$RUNTIME/database/seeders"
  cp -a "$SOURCE/database/factories/." "$RUNTIME/database/factories/"
  touch "$MARKER"
elif [[ ! -d "$RUNTIME" || ! -f "$MARKER" ]]; then
  echo "Refusing to provision an unowned runtime at $RUNTIME." >&2
  exit 1
fi
if ! cmp -s "$SOURCE/composer.json" "$RUNTIME/composer.json" ||
   ! cmp -s "$SOURCE/composer.lock" "$RUNTIME/composer.lock"; then
  echo "Runtime Composer manifests differ from the reviewed original source." >&2
  exit 1
fi

if [[ ! -f "$RUNTIME/vendor/autoload.php" ]]; then
  if ! command -v composer >/dev/null; then
    echo "Composer is required to restore the original locked dependencies." >&2
    exit 1
  fi
  COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-scripts --no-plugins --no-interaction --prefer-dist \
    --working-dir="$RUNTIME"
fi

mkdir -p "$STORAGE/framework/cache/data" "$STORAGE/framework/sessions" \
  "$STORAGE/framework/views" "$STORAGE/logs" "$RUNTIME/bootstrap/cache"
chmod 700 "$STORAGE"

if [[ ! -f "$RUNTIME/.preview-app-key" ]]; then
  php -r 'echo "base64:".base64_encode(random_bytes(32));' > "$RUNTIME/.preview-app-key"
  chmod 600 "$RUNTIME/.preview-app-key"
fi

if [[ ! -f "$RUNTIME/.preview-credentials" ]]; then
  admin_password="$(php -r 'echo bin2hex(random_bytes(24));')"
  customer_password="$(php -r 'echo bin2hex(random_bytes(24));')"
  master_password="$(php -r 'echo bin2hex(random_bytes(24));')"
  {
    printf 'ADMIN_EMAIL=admin@agendaally.local\n'
    printf 'ADMIN_PASSWORD=%s\n' "$admin_password"
    printf 'CUSTOMER_EMAIL=customer@agendaally.local\n'
    printf 'CUSTOMER_PASSWORD=%s\n' "$customer_password"
    printf 'MASTER_EMAIL=master@agendaally.local\n'
    printf 'MASTER_PASSWORD=%s\n' "$master_password"
  } > "$RUNTIME/.preview-credentials"
  chmod 600 "$RUNTIME/.preview-credentials"
fi

if [[ ! -f "$STORAGE/preview.sqlite" ]]; then
  umask 077
  : > "$STORAGE/preview.sqlite"
fi

env -i PATH="$PATH" HOME="$RUNTIME" \
  PREVIEW_RUNTIME="$RUNTIME" \
  PREVIEW_DB_FILE="$STORAGE/preview.sqlite" \
  PREVIEW_CREDENTIALS="$RUNTIME/.preview-credentials" \
  php "$ROOT/scripts/seed-original-http-preview.php"
chmod 600 "$STORAGE/preview.sqlite"

printf 'Original HTTP preview runtime provisioned at %s\n' "$RUNTIME"
printf 'Synthetic credentials are stored privately in %s/.preview-credentials\n' "$RUNTIME"