#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BUILD_HOME=""
TEST_FILE=""

cleanup_work() {
  [[ -z "$TEST_FILE" ]] || rm -f "$TEST_FILE"
  [[ -z "$BUILD_HOME" ]] || rm -rf "$BUILD_HOME"
}
trap cleanup_work EXIT

if ! command -v node >/dev/null 2>&1; then
  printf '%s\n' 'Node.js is required for the dependency-free original-client source checks.' >&2
  exit 2
fi

for file in \
  .migration-backup/admin/src/services/booking.js \
  .migration-backup/admin/src/services/order.js \
  .migration-backup/admin/src/services/refund.js
do
  node --check "$ROOT/$file"
  printf 'PASS JavaScript syntax: %s\n' "$file"
done

node "$ROOT/scripts/verify-original-clients.mjs"

if command -v dart >/dev/null 2>&1; then
  DART_SUPPRESS_ANALYTICS=true FLUTTER_SUPPRESS_ANALYTICS=true CI=true \
    dart "$ROOT/scripts/verify-original-cart-quote-parser.dart"
else
  printf '%s\n' 'SKIP standalone cart quote parser: Dart runtime is unavailable.'
fi

BUILD_FAILED=0
if [[ "${RUN_WEB_ADMIN_BUILDS:-0}" == "1" ||
  "${RUN_WEB_BUILD:-0}" == "1" ||
  "${RUN_ADMIN_BUILD:-0}" == "1" ]]; then
  WEB_DIR="${WEB_CLIENT_DIR:-}"
  ADMIN_DIR="${ADMIN_CLIENT_DIR:-}"
  if [[ "${RUN_WEB_ADMIN_BUILDS:-0}" == "1" || "${RUN_WEB_BUILD:-0}" == "1" ]] && [[ -z "$WEB_DIR" ]]; then
    printf '%s\n' 'Web build mode requires WEB_CLIENT_DIR pointing at an isolated, dependency-restored source-only copy.' >&2
    exit 2
  fi
  if [[ "${RUN_WEB_ADMIN_BUILDS:-0}" == "1" || "${RUN_ADMIN_BUILD:-0}" == "1" ]] && [[ -z "$ADMIN_DIR" ]]; then
    printf '%s\n' 'Admin build mode requires ADMIN_CLIENT_DIR pointing at an isolated, dependency-restored source-only copy.' >&2
    exit 2
  fi

  BUILD_HOME="$(mktemp -d /tmp/agendaally-original-client-home.XXXXXX)"
  run_restored_build() {
    local name="$1"
    local dir="$2"
    local original="$3"
    local endpoint_var="$4"
    local endpoint_value="$5"

    dir="$(cd "$dir" && pwd -P)"
    original="$(cd "$original" && pwd -P)"
    if [[ "$dir" == "$original" ]]; then
      printf 'Refusing to build preserved source tree: %s\n' "$dir" >&2
      return 2
    fi
    if [[ ! -d "$dir/node_modules" ]]; then
      printf 'Missing restored node_modules in isolated %s copy: %s\n' "$name" "$dir/node_modules" >&2
      return 2
    fi
    if ! cmp -s "$ROOT/.migration-backup/$name/package.json" "$dir/package.json" ||
      ! cmp -s "$ROOT/.migration-backup/$name/yarn.lock" "$dir/yarn.lock"; then
      printf 'Refusing %s build: package.json or yarn.lock differs from preserved original.\n' "$name" >&2
      return 2
    fi
    if find "$dir" -path "$dir/node_modules" -prune -o -type f -name '.env*' -print -quit | grep -q .; then
      printf 'Refusing %s build because the isolated copy contains an environment file.\n' "$name" >&2
      return 2
    fi
    if [[ -d "$dir/public" ]] && find "$dir/public" -type f -print -quit | grep -q .; then
      printf 'Refusing %s build because production public assets are not permitted in the isolated copy.\n' "$name" >&2
      return 2
    fi
    if find "$dir" -path "$dir/node_modules" -prune -o -type f \( \
      -iname '*.png' -o -iname '*.jpg' -o -iname '*.jpeg' -o -iname '*.gif' \
      -o -iname '*.webp' -o -iname '*.avif' -o -iname '*.ico' -o -iname '*.svg' \
      -o -iname '*.woff' -o -iname '*.woff2' -o -iname '*.ttf' -o -iname '*.eot' \
      -o -iname '*.riv' -o -iname '*.mp3' -o -iname '*.mp4' -o -iname '*.pdf' \
    \) -print -quit | grep -q .; then
      printf 'Refusing %s build because original binary/media assets are not permitted in the isolated copy.\n' "$name" >&2
      return 2
    fi

    printf 'Building restored isolated %s copy with cleared environment and external network blocked: %s\n' "$name" "$dir"
    set +e
    (
      cd "$dir"
      if [[ "$endpoint_var" == "NEXT_PUBLIC_BASE_URL" ]]; then
        env -i PATH="$PATH" HOME="$BUILD_HOME" CI=true \
          NODE_OPTIONS="--require=$ROOT/scripts/block-original-client-network.cjs" \
          NEXT_TELEMETRY_DISABLED=1 NEXT_PUBLIC_BASE_URL="$endpoint_value" \
          NEXT_PUBLIC_DEFAULT_LANGUAGE_CODE=en NEXT_PUBLIC_DEFAULT_TOKEN_TYPE=Bearer \
          yarn run build
      else
        env -i PATH="$PATH" HOME="$BUILD_HOME" CI=true \
          NODE_OPTIONS="--require=$ROOT/scripts/block-original-client-network.cjs" \
          VITE_BASE_URL="$endpoint_value" VITE_WEBSITE_URL='http://127.0.0.1:9' \
          bash -c 'node update-build.js && NODE_OPTIONS="--require=$1/scripts/block-original-client-network.cjs" node --max-old-space-size=4096 node_modules/vite/bin/vite.js build' _ "$ROOT"
      fi
    )
    local status=$?
    set -e
    printf '%s_BUILD_EXIT=%s\n' "${name^^}" "$status"
    if [[ "$status" != "0" ]]; then
      BUILD_FAILED=1
    fi
  }

  if [[ "${RUN_WEB_ADMIN_BUILDS:-0}" == "1" || "${RUN_WEB_BUILD:-0}" == "1" ]]; then
    run_restored_build web "$WEB_DIR" "$ROOT/.migration-backup/web" NEXT_PUBLIC_BASE_URL 'http://127.0.0.1:9/'
  fi
  if [[ "${RUN_WEB_ADMIN_BUILDS:-0}" == "1" || "${RUN_ADMIN_BUILD:-0}" == "1" ]]; then
    run_restored_build admin "$ADMIN_DIR" "$ROOT/.migration-backup/admin" VITE_BASE_URL 'http://127.0.0.1:9'
  fi
fi

CLIENT_DIR="${FLUTTER_CLIENT_DIR:-}"
if ! command -v flutter >/dev/null 2>&1; then
  printf '%s\n' 'SKIP comprehensive Flutter parser suite: flutter is not installed.'
  exit "$BUILD_FAILED"
fi

if [[ -z "$CLIENT_DIR" ]]; then
  printf '%s\n' 'SKIP Flutter parser tests: set FLUTTER_CLIENT_DIR to an isolated, dependency-restored copy of customer_app.'
  exit "$BUILD_FAILED"
fi

CLIENT_DIR="$(cd "$CLIENT_DIR" && pwd -P)"
ORIGINAL_CLIENT_DIR="$(cd "$ROOT/.migration-backup/customer_app" && pwd -P)"
if [[ "$CLIENT_DIR" == "$ORIGINAL_CLIENT_DIR" ]]; then
  printf '%s\n' 'Refusing to run Flutter tests inside the preserved .migration-backup/customer_app tree.' >&2
  exit 2
fi
PARSER_MODE="${FLUTTER_PARSER_MODE:-original-locked}"
case "$PARSER_MODE" in
  original-locked)
    if ! cmp -s "$ROOT/.migration-backup/customer_app/pubspec.yaml" "$CLIENT_DIR/pubspec.yaml" ||
      ! cmp -s "$ROOT/.migration-backup/customer_app/pubspec.lock" "$CLIENT_DIR/pubspec.lock"; then
      printf '%s\n' 'Refusing Flutter parser tests: isolated pubspec.yaml or pubspec.lock differs from preserved original.' >&2
      exit 2
    fi
    ;;
  dto-only)
    if ! diff -qr "$ROOT/.migration-backup/customer_app/lib/domain/model" "$CLIENT_DIR/lib/domain/model"; then
      printf '%s\n' 'Refusing DTO-only parser tests: isolated model source differs from original lib/domain/model.' >&2
      exit 2
    fi
    if ! grep -q '^name: demand$' "$CLIENT_DIR/pubspec.yaml"; then
      printf '%s\n' 'DTO-only parser harness package name must remain demand for original package imports.' >&2
      exit 2
    fi
    printf '%s\n' 'Running DTO-only compatibility harness; this is not the original full-app locked dependency restore.'
    ;;
  *)
    printf 'Unknown FLUTTER_PARSER_MODE: %s (expected original-locked or dto-only)\n' "$PARSER_MODE" >&2
    exit 2
    ;;
esac
for restricted in assets public android ios; do
  if [[ -e "$CLIENT_DIR/$restricted" ]]; then
    printf 'Refusing Flutter parser tests: restricted original content directory exists in isolated copy: %s\n' "$restricted" >&2
    exit 2
  fi
done
if find "$CLIENT_DIR" -path "$CLIENT_DIR/.dart_tool" -prune -o -type f -name '.env*' -print -quit | grep -q .; then
  printf '%s\n' 'Refusing Flutter parser tests: isolated copy contains an environment file.' >&2
  exit 2
fi
if [[ ! -f "$CLIENT_DIR/.dart_tool/package_config.json" ]]; then
  printf 'Missing restored package configuration in isolated client copy: %s\n' \
    "$CLIENT_DIR/.dart_tool/package_config.json" >&2
  exit 2
fi
FIXTURE="$ROOT/.migration-backup/backend/tests/Baseline/fixtures/original-domain.json"
if [[ ! -f "$FIXTURE" ]]; then
  printf 'Missing original backend peer fixture: %s\n' "$FIXTURE" >&2
  exit 2
fi

for file in \
  lib/domain/model/response/booking_response.dart \
  lib/domain/model/response/booking_calculate_response.dart \
  lib/domain/model/response/cart_response.dart \
  lib/domain/model/response/cart_calculate_response.dart \
  lib/domain/model/response/product_calculate_response.dart \
  lib/domain/model/model/order_model.dart \
  lib/domain/model/response/order_pagenation_response.dart \
  lib/domain/model/response/refund_pagination_response.dart
do
  if ! cmp -s "$ROOT/.migration-backup/customer_app/$file" "$CLIENT_DIR/$file"; then
    printf 'Isolated parser source differs from preserved original: %s\n' "$file" >&2
    exit 2
  fi
done

mkdir -p "$CLIENT_DIR/test"
TEST_FILE="$(mktemp "$CLIENT_DIR/test/original_client_baseline_XXXXXX_test.dart")"
cp "$ROOT/scripts/verify-original-clients.dart" "$TEST_FILE"

cd "$CLIENT_DIR"
env -i PATH="$PATH" HOME="$BUILD_HOME" CI=true \
  FLUTTER_SUPPRESS_ANALYTICS=true DART_SUPPRESS_ANALYTICS=true \
  AGENDAALLY_BACKEND_FIXTURE="$FIXTURE" \
  flutter test --no-pub --reporter expanded "$TEST_FILE"
exit "$BUILD_FAILED"