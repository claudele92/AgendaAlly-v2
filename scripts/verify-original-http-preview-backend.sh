#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
RUNTIME="$ROOT/.local/agendaally-preview/backend"
BASE_URL="${1:-http://127.0.0.1:8000}"
PUBLIC_DOMAIN="${2:-}"
CREDENTIALS="$RUNTIME/.preview-credentials"
TEMP_DIR="$RUNTIME/storage"

if [[ ! -f "$RUNTIME/.original-http-preview-owned" || ! -f "$CREDENTIALS" ]]; then
  echo "Provision the owned runtime before running HTTP checks." >&2
  exit 1
fi
if ! command -v curl >/dev/null || ! command -v php >/dev/null; then
  echo "curl and PHP are required for the HTTP checks." >&2
  exit 1
fi

umask 077
login_body="$(mktemp "$TEMP_DIR/http-login.XXXXXX")"
login_response="$(mktemp "$TEMP_DIR/http-login-response.XXXXXX")"
bad_login_body="$(mktemp "$TEMP_DIR/http-bad-login.XXXXXX")"
bad_login_response="$(mktemp "$TEMP_DIR/http-bad-login-response.XXXXXX")"
response="$(mktemp "$TEMP_DIR/http-response.XXXXXX")"
headers="$(mktemp "$TEMP_DIR/http-headers.XXXXXX")"
trap 'rm -f "$login_body" "$login_response" "$bad_login_body" "$bad_login_response" "$response" "$headers"' EXIT
php -r '
$credentials = parse_ini_file($argv[1], false, INI_SCANNER_RAW);
echo json_encode(["email" => $credentials["ADMIN_EMAIL"], "password" => $credentials["ADMIN_PASSWORD"]], JSON_THROW_ON_ERROR);
' "$CREDENTIALS" > "$login_body"
php -r '
$credentials = parse_ini_file($argv[1], false, INI_SCANNER_RAW);
echo json_encode(["email" => $credentials["ADMIN_EMAIL"], "password" => "intentionally-invalid-local-preview-password"], JSON_THROW_ON_ERROR);
' "$CREDENTIALS" > "$bad_login_body"

request_json() {
  local name="$1"
  local path="$2"
  local minimum_items=0
  shift 2
  if [[ "${1:-}" =~ ^[0-9]+$ ]]; then
    minimum_items="$1"
    shift
  fi
  local status
  status="$(curl --silent --show-error --output "$response" --write-out '%{http_code}' \
    --header 'Accept: application/json' "$@" "$BASE_URL$path")"
  if [[ "$status" != 2* ]]; then
    printf 'FAIL %-36s HTTP %s\n' "$name" "$status" >&2
    exit 1
  fi
  if ! php -r '
$response = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
if (($response["status"] ?? true) !== true) {
    exit(1);
}
$minimum = (int)$argv[2];
if ($minimum > 0) {
    $data = $response["data"] ?? null;
    $items = is_array($data) && isset($data["data"]) && is_array($data["data"])
        ? $data["data"]
        : (is_array($data) && array_is_list($data) ? $data : []);
    if (count($items) < $minimum) {
        exit(2);
    }
}
' "$response" "$minimum_items"; then
    printf 'FAIL %-36s response was not JSON\n' "$name" >&2
    exit 1
  fi
  printf 'PASS %s\n' "$name"
}

request_empty_collection() {
  local name="$1"
  local path="$2"
  local status
  status="$(curl --silent --show-error --output "$response" --write-out '%{http_code}' \
    --header 'Accept: application/json' "$BASE_URL$path")"
  if [[ "$status" != 2* ]] || ! php -r '
$response = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
if (($response["status"] ?? true) !== true) {
    exit(1);
}
$data = $response["data"] ?? null;
$items = is_array($data) && isset($data["data"]) && is_array($data["data"])
    ? $data["data"]
    : (is_array($data) && array_is_list($data) ? $data : null);
if (!is_array($items) || count($items) !== 0) {
    exit(1);
}
' "$response"; then
    printf 'FAIL %-36s expected a successful empty JSON collection (HTTP %s)\n' \
      "$name" "$status" >&2
    exit 1
  fi
  printf 'PASS %s (valid empty collection)\n' "$name"
}

request_json "settings" "/api/v1/rest/settings" 1
request_json "active languages" "/api/v1/rest/languages/active" 1
request_json "active currencies" "/api/v1/rest/currencies/active" 1
request_json "English translations" "/api/v1/rest/translations/paginate?lang=en"
request_json "countries" "/api/v1/rest/countries?lang=en&perPage=10" 1
request_json "cities" "/api/v1/rest/cities?lang=en&country_id=1&perPage=10" 1
request_json "public products" "/api/v1/rest/products/paginate?lang=en&perPage=10" 1
request_json "public services" "/api/v1/rest/services?lang=en&perPage=10" 1
request_json "geographic customer products" \
  "/api/v1/rest/products/paginate?lang=en&currency_id=1&country_id=1&city_id=1&region_id=1&perPage=10" 1
request_json "geographic customer services" \
  "/api/v1/rest/services?lang=en&currency_id=1&country_id=1&city_id=1&region_id=1&perPage=10" 1
request_json "customer service search categories" \
  "/api/v1/rest/categories/paginate?lang=en&perPage=11&type=service&page=1&search=&has_service=1&column=input&sort=asc" 1
request_empty_collection "root catalog banners" "/api/v1/rest/banners/paginate?lang=en"

check_cors_origin() {
  local origin="$1"
  local expected="$2"
  local status
  status="$(curl --silent --show-error --output "$response" --dump-header "$headers" \
    --write-out '%{http_code}' --request OPTIONS \
    --header 'Accept: application/json' \
    --header "Origin: $origin" \
    --header 'Access-Control-Request-Method: GET' \
    --header 'Access-Control-Request-Headers: authorization,content-type' \
    "$BASE_URL/api/v1/rest/settings")"
  local allowed_origin
  allowed_origin="$(awk 'tolower($1) == "access-control-allow-origin:" {gsub("\r", "", $2); value=$2} END {print value}' "$headers")"
  if [[ "$status" != 2* || "$allowed_origin" != "$expected" ]]; then
    printf 'FAIL CORS origin %s (HTTP %s, allow-origin %s)\n' \
      "$origin" "$status" "${allowed_origin:-missing}" >&2
    exit 1
  fi
  printf 'PASS CORS allows %s\n' "$origin"
}

reject_cors_origin() {
  local origin="$1"
  local status
  status="$(curl --silent --show-error --output "$response" --dump-header "$headers" \
    --write-out '%{http_code}' --request OPTIONS \
    --header 'Accept: application/json' \
    --header "Origin: $origin" \
    --header 'Access-Control-Request-Method: GET' \
    --header 'Access-Control-Request-Headers: authorization,content-type' \
    "$BASE_URL/api/v1/rest/settings")"
  local allowed_origin
  allowed_origin="$(awk 'tolower($1) == "access-control-allow-origin:" {gsub("\r", "", $2); value=$2} END {print value}' "$headers")"
  if [[ "$status" != 2* || -n "$allowed_origin" ]]; then
    printf 'FAIL CORS rejected origin %s (HTTP %s, allow-origin %s)\n' \
      "$origin" "$status" "${allowed_origin:-unexpected}" >&2
    exit 1
  fi
  printf 'PASS CORS rejects %s\n' "$origin"
}

if [[ -n "$PUBLIC_DOMAIN" ]]; then
  check_cors_origin "https://$PUBLIC_DOMAIN:3002" "https://$PUBLIC_DOMAIN:3002"
  check_cors_origin "https://$PUBLIC_DOMAIN:3003" "https://$PUBLIC_DOMAIN:3003"
  reject_cors_origin "https://unapproved.$PUBLIC_DOMAIN:3002"
else
  check_cors_origin "http://localhost:3002" "http://localhost:3002"
  check_cors_origin "http://localhost:3003" "http://localhost:3003"
  reject_cors_origin "http://localhost:3004"
fi

unauthorized_request() {
  local name="$1"
  local path="$2"
  shift 2
  local status
  status="$(curl --silent --show-error --output "$response" --write-out '%{http_code}' \
    --header 'Accept: application/json' "$@" "$BASE_URL$path")"
  if [[ "$status" != 401* ]] || ! php -r '
$response = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
exit(($response["status"] ?? true) === false ? 0 : 1);
' "$response"; then
    printf 'FAIL %-36s expected an unauthorized JSON response (HTTP 401)\n' "$name" >&2
    exit 1
  fi
  printf 'PASS %s\n' "$name"
}

unauthorized_request "protected route without token" \
  "/api/v1/dashboard/admin/products/paginate?perPage=1"
unauthorized_request "protected route with invalid token" \
  "/api/v1/dashboard/admin/bookings?perPage=1" \
  --header 'Authorization: Bearer invalid-local-preview-token'

bad_login_status="$(curl --silent --show-error --output "$bad_login_response" --write-out '%{http_code}' \
  --header 'Accept: application/json' --header 'Content-Type: application/json' \
  --data-binary "@$bad_login_body" \
  "$BASE_URL/api/v1/auth/login")"
if [[ "$bad_login_status" != 4* ]] || ! php -r '
$response = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
exit(($response["status"] ?? true) === false &&
    empty($response["data"]["token"]) &&
    empty($response["data"]["access_token"]) ? 0 : 1);
' "$bad_login_response"; then
  echo "FAIL invalid password unexpectedly authenticated or returned an unexpected response" >&2
  exit 1
fi
rm -f "$bad_login_body" "$bad_login_response"
printf 'PASS invalid password rejected (no token returned)\n'

login_status="$(curl --silent --show-error --output "$login_response" --write-out '%{http_code}' \
  --header 'Accept: application/json' --header 'Content-Type: application/json' \
  --data-binary "@$login_body" \
  "$BASE_URL/api/v1/auth/login")"
if [[ "$login_status" != 2* ]]; then
  printf 'FAIL real admin login HTTP %s\n' "$login_status" >&2
  exit 1
fi
token="$(php -r '
$response = json_decode(file_get_contents($argv[1]), true, 512, JSON_THROW_ON_ERROR);
if (($response["status"] ?? false) !== true) {
    exit(1);
}
echo $response["data"]["access_token"] ?? "";
' "$login_response")"
if [[ -z "$token" ]]; then
  echo "FAIL real admin login did not return an access token" >&2
  exit 1
fi
rm -f "$login_body" "$login_response"
printf 'PASS real admin login (token withheld)\n'

request_json "protected admin product list" "/api/v1/dashboard/admin/products/paginate?perPage=10" 1 \
  --header "Authorization: Bearer $token"
request_json "protected admin booking list" "/api/v1/dashboard/admin/bookings?perPage=10" 1 \
  --header "Authorization: Bearer $token"