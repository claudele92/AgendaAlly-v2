---
name: Laravel auth limiter isolation
description: Numeric nested throttles can inherit ordinary API traffic and prematurely block selected authentication endpoints.
---
Use named, path-specific limiters for narrow authentication limits.

**Why:** A numeric verification limiter shared the default domain/IP bucket with
the outer numeric API throttle. Ordinary signed-in browsing exhausted the much
smaller verification allowance, so a correct fresh challenge received 429.

**How to apply:** Keep login/account limits in independently named buckets.
Verify correct authentication after ordinary API traffic as well as deliberate
429 behavior; a standalone low-traffic test does not establish isolation.