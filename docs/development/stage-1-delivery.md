# AgendaAlly — Stage 1 delivery

## Visual proposal

The Canvas contains a proposed shared visual-system guide, Customer login
and registration, shared Admin/Vendor login, role-aware navigation and 390px
customer/business authentication previews. Source-extracted current
authentication screens are provided separately for comparison.

The visual guide and authentication use the same proposed Brand, Button, Field,
PasswordField, status and preview-notice helpers. Navigation uses the same
brand, original local Inter font and semantic token layer. These are isolated
proposal components, not a production component-library/framework migration.

Customer registration retains contact and terms → verification → profile and
password progression. Admin/Vendor access remains determined by the
authenticated account, not a role picker on login. The navigation role selector
is explicitly a review control for synthetic workspace states, not
impersonation. Booking and product commerce are both visible; branch and
country scope remain explicit.

All proposal form actions are local-only: no login, account creation,
verification message, credential persistence, payments or live-provider calls.
Current-source extraction compromises are documented in the sandbox group's
`_extraction.md`, including native adapters for unavailable Ant Design,
phone-input and validation dependencies. These are not native-app screenshots.

## Static authentication artwork

The two new generated photographs are maintained independently of database
fixtures:

- Native customer canonical asset:
  `.migration-backup/web/public/img/auth/customer-welcome.jpg`
- Native business canonical asset:
  `.migration-backup/admin/src/assets/images/auth/business-workspace.jpg`
- Canvas-serving copies:
  `artifacts/mockup-sandbox/public/images/agendaally-stage1/`

Following approval, both photographs are wired into native Stage 1
authentication. Existing marketplace demo imagery and its reproducible
development seeds are unchanged.

## Native public-installer cleanup

Implemented separately from the proposal:

- No installer initialization check in normal login.
- `/welcome` and `/installation` redirect through normal `/login`.
- Authenticated users retain their selected application destination.
- Invalid runtime configuration renders a fixed, non-sensitive error before
  application/provider startup requests; production build validation stays
  strict.
- Active backup/system-information consumers use an ordinary maintenance
  service instead of importing installer operations.
- Backend installer routes/controller restrictions are unchanged and disabled.
- CAPTCHA/Firebase/Maps configuration guards and local-only CAPTCHA bypass
  remain intact.

## Earlier proposal and installer verification

- Focused native runtime/startup/localization tests: 16 passed.
- Sandbox TypeScript check passed after the final proposal fixes.
- Real native browser check: fresh login, unauthenticated legacy welcome and
  installation redirects, successful Admin login, UI logout, successful
  Seller/Vendor login. Both roles reached their actual dashboards.
- Browser network evidence: zero installer-check/init XHR/fetch requests.
- Read-only security/regression review found no blocking cleanup issue.
- Desktop and 390px authentication previews were inspected visually; the
  navigation, visual guide and registration preview are also reviewed for
  rendering. This is not a complete accessibility/RTL or native mobile audit.
- Early admin compilation attempts timed out or were interrupted. They are not
  counted as successful builds. The current native integration checks and
  clean-source compilation results are recorded in
  [Stage 1 implementation](stage-1-implementation.md).

No production access, new authentication provider, schema migration, framework
replacement, live operation or publishing was performed.

## Native integration and approval boundary

The creator accepted the presented Stage 1 visual direction on 2026-10-01.
The creator subsequently approved native authentication and role-aware
navigation integration together as Stage 1. Native authentication, scoped
tokens/artwork/local fonts, customer navigation and verified role-aware
business navigation are now integrated, alongside the portable installer
cleanup. Booking, checkout, product, finance and other major screen redesigns
remain outside scope.
Preserve server authorization, Spatie permissions, country scope, shop
membership, branch assignments and REST contracts; never silently change
branches or expand scope. Verify representative real sessions, navigation
grants/context, both client production builds, existing security/domain tests
and responsive views before stopping for visual review ahead of Stage 2.
The implementation report distinguishes real native browser evidence from
Canvas proposals, records provider and legacy-content limitations, and lists
the separately proposed finance-scope/calendar repairs. Stage 2 is not started.
The [design brief](stage-1-design-brief.md) records the proposed token basis,
source references and preserved product/authorization constraints.