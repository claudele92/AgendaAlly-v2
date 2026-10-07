---
name: Maps bootstrap opt-in
description: Why native browser Maps consumers must wait for authoritative public runtime flags.
---

Browser Maps consumers must require explicit platform enablement and permitted runtime settings before loading the SDK, including before public settings finish loading.

**Why:** A native browser pass found an OFF Admin switch while the authoritative public API said ON: old menu snapshots omitted the new flag. An SDK request cannot prove the setting is OFF or ON without checking the actual API flags. Missing flags also must not authorize an early environment-key fallback.

**How to apply:** Initialize the Maps switch from current server settings, not only menu snapshots, and preserve unsaved operator edits. Missing runtime flags mean disabled, not permission to use a fallback key. Preserve production environment kill switches. Check authoritative API flags alongside requests in a fresh browser context.