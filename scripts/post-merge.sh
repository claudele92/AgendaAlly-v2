#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

# Application dependencies belong to the original locked projects, not the
# unrelated Express/Drizzle scaffold. Never push that scaffold's schema here.
node scripts/development.mjs install
if [[ -n "${REPLIT_DEV_DOMAIN:-}" ]]; then
  node scripts/development.mjs init --replit
else
  node scripts/development.mjs init
fi
node scripts/development.mjs configure
fresh_database=false
if ! node scripts/development.mjs database-exists; then
  fresh_database=true
fi
node scripts/development.mjs bootstrap
if [[ "$fresh_database" == true ]]; then
  node scripts/development.mjs seed
fi
