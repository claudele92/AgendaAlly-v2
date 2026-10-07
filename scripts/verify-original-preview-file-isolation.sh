#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd -P)"
RUNTIME="$ROOT/.local/agendaally-preview/backend"
ADMIN_ROOT="$ROOT/.local/agendaally-preview/admin"
BASE_URL="http://127.0.0.1:3003"

if [[ ! -f "$RUNTIME/.original-http-preview-owned" ||
      ! -f "$RUNTIME/.preview-credentials" ||
      ! -f "$RUNTIME/.preview-app-key" ||
      ! -f "$RUNTIME/storage/preview.sqlite" ||
      ! -d "$ADMIN_ROOT" ]]; then
  printf 'The owned original preview files or admin client are missing.\n' >&2
  exit 1
fi
if ! command -v curl >/dev/null 2>&1; then
  printf 'curl is required for the local file-isolation check.\n' >&2
  exit 1
fi

response_file="$(mktemp)"
trap 'rm -f "$response_file"' EXIT
protected_checks=0
public_checks=0

check_protected() {
  local path="$1"
  local method="$2"
  local url="$BASE_URL$path"
  local status

  if [[ "$method" == HEAD ]]; then
    status="$(curl --noproxy '*' --path-as-is --silent --show-error \
      --connect-timeout 3 --max-time 10 --head -o "$response_file" \
      -w '%{http_code}' "$url")"
  else
    status="$(curl --noproxy '*' --path-as-is --silent --show-error \
      --connect-timeout 3 --max-time 10 -o "$response_file" \
      -w '%{http_code}' "$url")"
  fi
  if [[ "$status" != 403 && "$status" != 404 ]]; then
    printf 'Protected-file isolation failed: %s %s (HTTP %s).\n' "$method" "$path" "$status" >&2
    exit 1
  fi
  protected_checks=$((protected_checks + 1))
}

check_public() {
  local path="$1"
  local method="$2"
  local url="$BASE_URL$path"
  local status

  if [[ "$method" == HEAD ]]; then
    status="$(curl --noproxy '*' --path-as-is --silent --show-error \
      --connect-timeout 3 --max-time 10 --head -o "$response_file" \
      -w '%{http_code}' "$url")"
  else
    status="$(curl --noproxy '*' --path-as-is --silent --show-error \
      --connect-timeout 3 --max-time 10 -o "$response_file" \
      -w '%{http_code}' "$url")"
  fi
  if [[ "$status" != 200 ]]; then
    printf 'Expected an original admin source module to return HTTP 200 (received %s).\n' "$status" >&2
    exit 1
  fi
  public_checks=$((public_checks + 1))
}

private_paths=(
  "$RUNTIME/.preview-credentials"
  "$RUNTIME/.preview-app-key"
  "$RUNTIME/storage/preview.sqlite"
  "$RUNTIME/storage/preview.sqlite-wal"
  "$RUNTIME/storage/preview.sqlite-shm"
  "$RUNTIME/storage/logs/laravel.log"
  "$ROOT/.replit"
  "$ROOT/.agents/memory/MEMORY.md"
)

for absolute_path in "${private_paths[@]}"; do
  for fs_path in "/@fs${absolute_path}" "/@fs/${absolute_path}"; do
    for suffix in '' '?raw'; do
      check_protected "${fs_path}${suffix}" HEAD
      check_protected "${fs_path}${suffix}" GET
    done
  done
done

# Exercise encoded traversal from the Vite-served admin root into each private
# backend path. --path-as-is above preserves the encoded dot segments.
for relative_path in \
  '.preview-credentials' \
  '.preview-app-key' \
  'storage/preview.sqlite' \
  'storage/preview.sqlite-wal' \
  'storage/logs/laravel.log'; do
  for fs_path in \
    "/@fs${ADMIN_ROOT}/%2e%2e/backend/${relative_path}" \
    "/@fs/${ADMIN_ROOT}/%2e%2e/backend/${relative_path}"; do
    for suffix in '' '?raw'; do
      check_protected "${fs_path}${suffix}" HEAD
      check_protected "${fs_path}${suffix}" GET
    done
  done
done

for public_path in \
  /src/configs/i18next.js \
  /src/context/context.jsx \
  /src/views/login/index.jsx; do
  check_public "$public_path" HEAD
  check_public "$public_path" GET
done

printf 'File isolation passed: %d protected-path checks; %d source-module checks.\n' \
  "$protected_checks" "$public_checks"