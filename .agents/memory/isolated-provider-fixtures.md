---
name: Isolated provider fixtures
description: Non-obvious HTTP fake behavior when changing provider responses within a Laravel test
---

Repeated Laravel HTTP `fake()` calls append response stubs rather than replacing earlier matching stubs. A test that changes the same URL from a valid response to an invalid one can still receive the original response.

**Why:** A PayPal mismatch regression appeared to accept the wrong amount because its second fake never replaced the first matching URL, not because the verification accepted that response.

**How to apply:** Use a response sequence, one callback whose data changes, or swap to a fresh HTTP factory before installing new stubs. Keep stray-request prevention enabled after a factory swap.