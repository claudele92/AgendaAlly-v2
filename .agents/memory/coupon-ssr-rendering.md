---
name: Coupon SSR rendering
description: Why the native Coupon field should not regain an unnecessary Next dynamic-loading boundary.
---

Keep the small native booking Coupon field normally imported and server-rendered.
Do not reintroduce lazy loading merely as a bundle optimization without checking
initial document hydration and accessible input/label binding.

**Why:** Next 16 / React 19 initial hydration produced different tree-derived
`useId` attributes under the Coupon loadable boundary while its text matched.
Removing only that boundary made the fresh server and client IDs identical and
preserved label focus and Coupon behavior. Client-side navigation alone did not
reproduce the warning, so it was not sufficient verification.

**How to apply:** When changing this rendering boundary, distinguish initial
hydration from soft navigation. Preserve SSR and generated accessible IDs;
do not suppress warnings, hardcode global IDs or disable SSR to hide divergence.
This is not a blanket prohibition on lazy loading larger optional components.