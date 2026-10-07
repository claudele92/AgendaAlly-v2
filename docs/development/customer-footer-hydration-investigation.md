# Customer Footer hydration investigation — closed, currently non-reproducible

Date: 2026-10-02, America/Chicago.

**Status: CLOSED — previously observed, currently non-reproducible. No application correction applied.**

The supplied brief reports different generated Headless UI Disclosure IDs.
This investigation does not dispute that observation, but could not reproduce
it in the current local runtime. The creator also confirms the warning is no
longer appearing during normal Customer browsing and has no currently
reproducible failing URL. The previously supplied console trace remains the
only captured failure evidence. No failing URL or root cause is inferred.
This closure does not claim the issue was fixed.

## Scope and sequencing

Only the separate Customer Footer brief was investigated. The payment P0
containment has not started and, under the creator's latest instruction, must
remain unstarted until separately approved. Closing this investigation does
not authorize payment changes or resumption of the broader payment audit.

No orders, bookings, payments, provider calls, payment mutations, or financial
fixtures were submitted. No application files, settings, dependencies, Driver,
Pickup, Shop-hours, favicon, or payment code were changed.

## Source trace

Paths below are relative to `.migration-backup/web/`.

- `app/(store)/(booking)/(with-footer)/layout.tsx` loads settings on the server,
  parses them, and renders children followed by the normally imported Footer.
  Footer is not wrapped in `next/dynamic`.
- `components/footer/footer.tsx` is a client component. The three FooterGroup
  instances (Discover, Your Account, Information) are unconditional.
- FooterGroup uses the same `<Disclosure as="div" defaultOpen>` structure at
  every breakpoint. Its Button and Panel retain Headless UI-generated IDs.
- `components/footer/footer.css` changes grid layout, spacing and chevron
  visibility with CSS. No browser-width branch chooses another initial
  Disclosure component tree.
- Settings determine description, app links and filtered social links. They
  arrive as server props. Those conditional sections do not conditionally
  instantiate the three FooterGroup components.
- The user store changes account-link destinations, not the number of account
  links or groups. The address store supplies conditional location text.
- The logo hook initially uses the configured light/default logo; theme
  selection is gated until after mount.
- Translate consumes the shared translation provider. No locale branch in
  Footer chooses a different Disclosure structure.
- The Footer copyright reads the current year. No year-boundary mismatch was
  observed; this is not established as the reported generated-ID cause.
- Ancestors inspected include the store layout, booking SearchProvider,
  simple-page layout, root layout, translation/theme/query/settings providers.
  SearchProvider initializes from URL query values. Root dynamic country,
  language and currency dialogs are siblings after the route children, not a
  lazy-loading wrapper around Footer.

**First server/client divergence:** none observed in the tested contexts.
**Exact root cause of the reported warning:** unresolved.
**Why the reported IDs differ:** cannot be established from this reproduction.
Responsive, auth, settings and ancestor logic were inspected, but no specific
branch has been proven responsible. No speculative blame is assigned to
Headless UI, browser caching, or a parent component.

## Runtime reproduction results

Owned local Chromium, logged out, no sign-in performed; initial fresh browser
profile followed by its naturally persisted discovery context.

| Check | Result |
| --- | --- |
| Homepage, desktop 1440 × 1000 | Footer rendered; no hydration warning |
| Products, desktop | Footer rendered; no hydration warning |
| Homepage and Products, mobile 390 × 950 | Footer rendered; no hydration warning |
| Homepage and Products, tablet 820 × 950 | Footer rendered; no hydration warning |
| Query context `country_id=1&city_id=1&location_type=2` | No Footer hydration warning |
| Footer Next-Link navigation to About | Native navigation completed without hydration warning |
| History back to Products and forward to About | Completed without hydration warning |
| Customer server restarted once, homepage loaded cold | No hydration warning |
| Second hard load after cold load | No hydration warning |
| Shops `/shops` | Current native route does not render this Footer |
| Specialists `/masters` | Current native route does not render this Footer |

On Homepage and Products, separately fetched server HTML and the hydrated DOM
contained these same generated IDs:

| Group | Button | Panel |
| --- | --- | --- |
| Discover | `headlessui-disclosure-button-_R_2jabn9et9epbalb_` | `headlessui-disclosure-panel-_R_4jabn9et9epbalb_` |
| Your Account | `headlessui-disclosure-button-_R_2rabn9et9epbalb_` | `headlessui-disclosure-panel-_R_4rabn9et9epbalb_` |
| Information | `headlessui-disclosure-button-_R_33abn9et9epbalb_` | `headlessui-disclosure-panel-_R_53abn9et9epbalb_` |

After the cold load and repeat hard load, all three buttons had
`aria-expanded="true"` and `aria-controls` pointing at the corresponding
rendered panel. Generated IDs were stable across those loads.

No post-fix keyboard/collapse certification is claimed: there is no fix to
verify. No authenticated-customer coverage is claimed. No regression test was
added for an unestablished condition.

Specialists emitted duplicate-child-key warnings during its initial load.
These are separate from the reported Footer hydration mismatch, were not
repaired, and prevent a claim of a globally clean Customer console.
Existing image warnings also remain outside this task.

## Evidence and changes

- `.local/footer-before.json`
- `.local/footer-reproduce.json`
- `.local/footer-reproduce-responsive.json`
- `.local/footer-cold-reproduce.json`
- `reports/footer-hydration/before-desktop.jpg`
- `reports/footer-hydration/cold-desktop.jpg`

Application files changed: **none**. This report, verification receipts and
screenshots, and approval-boundary memory are the only new investigation work.
The existing Customer workflow was restarted once for a cold reproduction;
Customer, Admin and Laravel previews remain available.

No `suppressHydrationWarning`, client-only Footer, custom/hard-coded ID,
accessibility removal, library replacement, or warning suppression was added.
The root's existing theme-related HTML suppression was not changed.

## Closure

The creator confirms the previously observed warning is currently
non-reproducible and explicitly instructs closing the investigation without
Footer changes. No speculative correction, warning suppression, SSR
disablement, Headless UI replacement, or hard-coded generated IDs is authorized.
No further reproduction attempt or failing-URL request is pending.

**Investigation closed. No Footer changes or claimed fix. Stopped after
documentation. Payment containment remains unstarted until separately approved.**