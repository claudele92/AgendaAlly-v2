#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd -P)"
PREVIEW_ROOT="$ROOT/.local/agendaally-preview"
WEB_ROOT="$PREVIEW_ROOT/web"
ADMIN_ROOT="$PREVIEW_ROOT/admin"
NETWORK_GUARD="$ROOT/scripts/guard-original-preview-network.cjs"
MODE="${1:-both}"

case "$MODE" in
  web|admin|both) ;;
  *)
    printf 'Usage: %s [web|admin|both]\n' "$0" >&2
    exit 2
    ;;
esac

if [[ -z "${REPLIT_DEV_DOMAIN:-}" ]]; then
  printf 'REPLIT_DEV_DOMAIN is required; no .env files or credentials are read.\n' >&2
  exit 2
fi

# Accept only a plain public DNS hostname, never a URL, port, path, or IP.
if [[ ${#REPLIT_DEV_DOMAIN} -gt 253 ||
      "$REPLIT_DEV_DOMAIN" == *[!A-Za-z0-9.-]* ||
      "$REPLIT_DEV_DOMAIN" != *.* ||
      "$REPLIT_DEV_DOMAIN" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
  printf 'REPLIT_DEV_DOMAIN must be a plain DNS hostname.\n' >&2
  exit 2
fi
IFS='.' read -r -a domain_labels <<<"$REPLIT_DEV_DOMAIN"
for label in "${domain_labels[@]}"; do
  if [[ ${#label} -gt 63 || ! "$label" =~ ^[A-Za-z0-9]([A-Za-z0-9-]*[A-Za-z0-9])?$ ]]; then
    printf 'REPLIT_DEV_DOMAIN must be a plain DNS hostname.\n' >&2
    exit 2
  fi
done

WEB_PORT=3002
ADMIN_PORT=3003
if [[ -n "${PORT:-}" ]]; then
  case "$MODE" in
    web) WEB_PORT="$PORT" ;;
    admin) ADMIN_PORT="$PORT" ;;
    both)
      printf 'PORT can override one preview at a time; choose web or admin mode.\n' >&2
      exit 2
      ;;
  esac
fi
for port in "$WEB_PORT" "$ADMIN_PORT"; do
  if [[ ! "$port" =~ ^[0-9]+$ ]] || ((port < 1 || port > 65535)); then
    printf 'Preview port must be an integer from 1 to 65535: %s\n' "$port" >&2
    exit 2
  fi
done

for project_root in "$WEB_ROOT" "$ADMIN_ROOT"; do
  if [[ ! -d "$project_root" ]]; then
    printf 'Missing restored client source: %s\n' "$project_root" >&2
    exit 2
  fi
  # Both Next and Vite load dotenv files themselves. Refuse them rather than
  # allowing credentials to override the explicit, clean preview environment.
  for dotenv_file in \
    "$project_root/.env" \
    "$project_root/.env.local" \
    "$project_root/.env.development" \
    "$project_root/.env.development.local"; do
    if [[ -e "$dotenv_file" || -L "$dotenv_file" ]]; then
      printf 'Refusing to start with dotenv file present: %s\n' "$dotenv_file" >&2
      printf 'Move it outside the client tree; this launcher never reads secrets.\n' >&2
      exit 2
    fi
  done
done

if [[ ! -f "$NETWORK_GUARD" ]]; then
  printf 'Missing required network guard: %s\n' "$NETWORK_GUARD" >&2
  exit 2
fi
if [[ "$MODE" == web || "$MODE" == both ]]; then
  if [[ ! -f "$WEB_ROOT/node_modules/next/dist/bin/next" ]]; then
    printf 'Restored Next.js dependency is missing under %s\n' "$WEB_ROOT/node_modules" >&2
    exit 2
  fi
fi
if [[ "$MODE" == admin || "$MODE" == both ]]; then
  if [[ ! -f "$ADMIN_ROOT/node_modules/vite/bin/vite.js" ]]; then
    printf 'Restored Vite dependency is missing under %s\n' "$ADMIN_ROOT/node_modules" >&2
    exit 2
  fi
fi

API_ORIGIN="https://${REPLIT_DEV_DOMAIN}:8000"
WEBSITE_URL="https://${REPLIT_DEV_DOMAIN}:${WEB_PORT}"
ADMIN_URL="https://${REPLIT_DEV_DOMAIN}:${ADMIN_PORT}"

WEB_ENV=(
  HOME=/nonexistent
  LANG=C.UTF-8
  PATH="$PATH"
  TMPDIR=/tmp
  NODE_ENV=development
  NODE_OPTIONS="--require=$NETWORK_GUARD"
  NEXT_TELEMETRY_DISABLED=1
  NEXT_PUBLIC_BASE_URL="${API_ORIGIN}/api/"
  NEXT_PUBLIC_WEBSITE_URL="$WEBSITE_URL"
  NEXT_PUBLIC_ADMIN_PANEL_URL="$ADMIN_URL"
  NEXT_PUBLIC_DEFAULT_LANGUAGE_CODE=en
  NEXT_PUBLIC_DEFAULT_TOKEN_TYPE=Bearer
  NEXT_PUBLIC_CACHE_TIME=60
  NEXT_PUBLIC_PROJECT_NAME='AgendaAlly isolated preview'
  NEXT_PUBLIC_IMAGE_URL="${API_ORIGIN}/storage/"
  NEXT_PUBLIC_GOOGLE_MAPS_KEY=PREVIEW_ONLY_NO_GOOGLE_MAPS_CREDENTIAL
  NEXT_PUBLIC_STRIPE_PUBLISHABLE_KEY=PREVIEW_ONLY_NO_STRIPE_CREDENTIAL
  NEXT_PUBLIC_API_KEY=PREVIEW_ONLY_NOT_A_REAL_FIREBASE_API_KEY
  NEXT_PUBLIC_AUTH_DOMAIN=preview-only.invalid
  NEXT_PUBLIC_PROJECT_ID=original-preview-not-a-real-firebase-project
  NEXT_PUBLIC_STORAGE_BUCKET=preview-only.invalid
  NEXT_PUBLIC_MESSAGING_SENDER_ID=0
  NEXT_PUBLIC_APP_ID=preview-only-not-a-real-firebase-app
  NEXT_PUBLIC_MEASUREMENT_ID=G-PREVIEWONLY
  NEXT_PUBLIC_VAPID_KEY=PREVIEW_ONLY_NOT_A_REAL_VAPID_KEY
  REPLIT_DEV_DOMAIN="$REPLIT_DEV_DOMAIN"
  PORT="$WEB_PORT"
)
ADMIN_ENV=(
  HOME=/nonexistent
  LANG=C.UTF-8
  PATH="$PATH"
  TMPDIR=/tmp
  NODE_ENV=development
  NODE_OPTIONS="--require=$NETWORK_GUARD"
  BROWSER=none
  VITE_BASE_URL="$API_ORIGIN"
  VITE_WEBSITE_URL="$WEBSITE_URL"
  VITE_RECAPTCHA_SITE_KEY=PREVIEW_ONLY_NO_RECAPTCHA_SITE_KEY
  AGENDAALLY_PREVIEW_LOCAL_LOGIN=approved
  AGENDAALLY_PREVIEW_INSTALLER_REDIRECT=approved
  AGENDAALLY_PREVIEW_ORIGINAL_TRANSLATIONS=approved
  __VITE_ADDITIONAL_SERVER_ALLOWED_HOSTS="$REPLIT_DEV_DOMAIN"
  REPLIT_DEV_DOMAIN="$REPLIT_DEV_DOMAIN"
  PORT="$ADMIN_PORT"
)

printf '%s\n' \
  'Starting original client sources with an isolated environment and outbound-network guard.' \
  "Storefront: ${WEBSITE_URL} (Next development server, 0.0.0.0:${WEB_PORT})" \
  "Admin:      ${ADMIN_URL} (Vite, 0.0.0.0:${ADMIN_PORT})" \
  "API:        ${API_ORIGIN} (only permitted non-loopback Node origin)" \
  'Firebase values are synthetic, non-credentials; Firebase auth/chat/push are unavailable.' \
  'Third-party Node requests, including production providers, are blocked; no simulated success is returned.' \
  'Google Fonts and other third-party Node requests are blocked; unavailable integrations remain unavailable.' \
  'Next image allowlist is unchanged: remote backend-hosted images may be rejected by next/image.'

declare -a child_pids=()

stop_children() {
  local pid
  for pid in "${child_pids[@]}"; do
    kill -TERM -- "-$pid" 2>/dev/null || true
  done
  for pid in "${child_pids[@]}"; do
    wait "$pid" 2>/dev/null || true
  done
}
trap stop_children EXIT INT TERM

start_web() {
  (
    cd "$WEB_ROOT"
    exec setsid env -i "${WEB_ENV[@]}" \
      node "$WEB_ROOT/node_modules/next/dist/bin/next" dev \
      --hostname 0.0.0.0 --port "$WEB_PORT"
  ) &
  child_pids+=("$!")
}

start_admin() {
  (
    cd "$ADMIN_ROOT"
    exec setsid env -i "${ADMIN_ENV[@]}" \
      node "$ADMIN_ROOT/node_modules/vite/bin/vite.js" \
      --config "$ROOT/scripts/original-admin-preview.vite.mjs" \
      --host 0.0.0.0 --port "$ADMIN_PORT" --strictPort
  ) &
  child_pids+=("$!")
}

if [[ "$MODE" == web || "$MODE" == both ]]; then
  start_web
fi
if [[ "$MODE" == admin || "$MODE" == both ]]; then
  start_admin
fi

status=0
for pid in "${child_pids[@]}"; do
  if wait "$pid"; then
    :
  else
    child_status=$?
    if ((status == 0)); then
      status="$child_status"
    fi
  fi
done
exit "$status"