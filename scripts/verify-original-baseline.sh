#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BACKEND="$ROOT/.migration-backup/backend"
VENDOR_AUTOLOAD="${HARDENING_VENDOR_AUTOLOAD:-$BACKEND/vendor/autoload.php}"

if [[ ! -f "$VENDOR_AUTOLOAD" ]]; then
  printf '%s\n' \
    "Missing reviewed Composer autoload: $VENDOR_AUTOLOAD" \
    "Restore dependencies in /tmp/agendaally-hardening-runtime with --no-scripts --no-plugins." >&2
  exit 2
fi

export HARDENING_VENDOR_AUTOLOAD="$VENDOR_AUTOLOAD"
cd "$BACKEND"

php -l tests/Baseline/bootstrap.php
php -l tests/Baseline/OriginalDomainBaselineTest.php
php -l tests/Hardening/IsolatedTestCase.php
php -l app/Services/BookingService/BookingService.php
php -l app/Repositories/BookingRepository/BookingRepository.php
php -l app/Services/CartService/CartService.php
php -l app/Services/OrderService/OrderService.php
php -l app/Services/OrderService/CartOrderService.php
php -l app/Services/OrderService/OrderRefundService.php
php -l app/Repositories/OrderRepository/OrderRepository.php
php -l app/Models/Booking.php
php -l app/Models/Cart.php
php -l app/Models/Order.php
php -l app/Models/OrderRefund.php
php -l app/Http/Resources/OrderRefundResource.php

if [[ "${BASELINE_WRITE_FIXTURES:-0}" == "1" ]]; then
  export BASELINE_WRITE_FIXTURES=1
fi

PHPUNIT="$BACKEND/vendor/bin/phpunit"
if [[ ! -x "$PHPUNIT" ]]; then
  PHPUNIT="$(dirname "$VENDOR_AUTOLOAD")/bin/phpunit"
fi
if [[ ! -x "$PHPUNIT" ]]; then
  printf 'Unable to locate PHPUnit next to autoload: %s\n' "$VENDOR_AUTOLOAD" >&2
  exit 2
fi

"$PHPUNIT" -c phpunit-baseline.xml --display-warnings --display-deprecations