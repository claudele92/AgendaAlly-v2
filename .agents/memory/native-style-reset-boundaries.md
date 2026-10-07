---
name: Native style reset boundaries
description: Preserve semantic native button/link foreground colors when importing scoped prototype presentation.
---

Do not apply descendant-level foreground resets to native buttons and links
inside a new presentation wrapper. Let the existing semantic/theme classes
control their colors; style newly authored controls explicitly.

**Why:** A scoped prototype reset using `color: inherit` had greater specificity
than native button utility classes. A functioning mobile booking action became
black text on a black background. The implementation type-checked and built;
reviewing the actual capture exposed the contrast failure.

**How to apply:** Inspect representative native transaction controls when
integrating presentation styles. Do not infer visual correctness from compilation,
and avoid overriding theme colors merely to normalize new markup.