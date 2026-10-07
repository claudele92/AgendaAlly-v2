#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
RUNTIME="$ROOT/.local/agendaally-preview/backend"
PORT="${1:-8000}"
PUBLIC_DOMAIN="${2:-${REPLIT_DEV_DOMAIN:-}}"
BIND_MODE="${3:-}"

if [[ ! -f "$RUNTIME/.original-http-preview-owned" ||
      ! -f "$RUNTIME/storage/preview.sqlite" ||
      ! -f "$RUNTIME/.preview-app-key" ]]; then
  echo "Run scripts/provision-original-http-preview.sh before serving." >&2
  exit 1
fi
if [[ ! "$PORT" =~ ^[0-9]+$ ]] || (( PORT < 1 || PORT > 65535 )); then
  echo "Port must be an integer between 1 and 65535." >&2
  exit 1
fi
if [[ -n "$BIND_MODE" && "$BIND_MODE" != "loopback" ]]; then
  echo "The optional bind mode must be 'loopback'." >&2
  exit 1
fi

if [[ -n "$PUBLIC_DOMAIN" ]]; then
  if [[ ! "$PUBLIC_DOMAIN" =~ ^[A-Za-z0-9.-]+$ || "$PUBLIC_DOMAIN" == .* || "$PUBLIC_DOMAIN" == *. ]]; then
    echo "Public domain must be a hostname without scheme, port, or path." >&2
    exit 1
  fi
  BIND_ADDRESS=0.0.0.0
  if [[ "$BIND_MODE" == "loopback" ]]; then
    BIND_ADDRESS=127.0.0.1
  fi
  APP_URL="https://$PUBLIC_DOMAIN:$PORT"
  CORS_ORIGINS="https://$PUBLIC_DOMAIN:3002,https://$PUBLIC_DOMAIN:3003"
else
  BIND_ADDRESS=127.0.0.1
  APP_URL="http://127.0.0.1:$PORT"
  CORS_ORIGINS="http://localhost:3002,http://localhost:3003,http://127.0.0.1:3002,http://127.0.0.1:3003"
fi

APP_KEY="$(cat "$RUNTIME/.preview-app-key")"
exec env -i PATH="$PATH" HOME="$RUNTIME" \
  AGENDAALLY_PREVIEW_RUNTIME="$RUNTIME" \
  AGENDAALLY_PREVIEW_DB="$RUNTIME/storage/preview.sqlite" \
  AGENDAALLY_PREVIEW_CORS_ORIGINS="$CORS_ORIGINS" \
  APP_KEY="$APP_KEY" APP_ENV=local APP_DEBUG=false APP_URL="$APP_URL" \
  APP_TIMEZONE=Africa/Douala \
  DB_CONNECTION=sqlite DB_DATABASE="$RUNTIME/storage/preview.sqlite" \
  CACHE_DRIVER=array CACHE_STORE=array SESSION_DRIVER=array QUEUE_CONNECTION=sync \
  MAIL_MAILER=array BROADCAST_DRIVER=log LOG_CHANNEL=stderr \
  FILESYSTEM_DISK=local \
  php -d allow_url_fopen=0 \
      -d disable_functions="exec,shell_exec,system,passthru,proc_open,popen,pcntl_exec,mail,stream_socket_client,stream_socket_server,fsockopen,pfsockopen,curl_exec,curl_multi_exec" \
      -S "$BIND_ADDRESS:$PORT" \
      -t "$RUNTIME/public" \
      "$ROOT/scripts/original-http-preview-router.php"