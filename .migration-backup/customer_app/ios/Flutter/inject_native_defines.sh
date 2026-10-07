#!/bin/bash
# Preserve the existing Info.plist injection without printing any values.
set +x
set -euo pipefail
IFS=',' read -r -a entries <<< "${DART_DEFINES:-}"
for entry in "${entries[@]}"; do
    if [[ "$(uname -s)" == Darwin ]]; then
        decoded=$(printf '%s' "$entry" | base64 -D)
    else
        decoded=$(printf '%s' "$entry" | base64 -d)
    fi
    [[ "$decoded" == *=* ]] || continue
    key="${decoded%%=*}"
    value="${decoded#*=}"
    [[ "$key" =~ ^[A-Z][A-Z0-9_]*$ ]] || continue
    [[ "$key" == FLUTTER* ]] && continue
    # Maps is provided only through the wrapper's generated build setting.
    # Info.plist already expands that setting; never overwrite it here.
    [[ "$key" == GOOGLE_MAPS_API_KEY ]] && continue
    if ! /usr/libexec/PlistBuddy -c "Set :$key $value" \
        "${TARGET_BUILD_DIR}/${INFOPLIST_PATH}" >/dev/null 2>&1; then
        if ! /usr/libexec/PlistBuddy -c "Add :$key string $value" \
            "${TARGET_BUILD_DIR}/${INFOPLIST_PATH}" >/dev/null 2>&1; then
            printf '%s\n' 'Native Info.plist configuration failed.' >&2
            exit 1
        fi
    fi
done