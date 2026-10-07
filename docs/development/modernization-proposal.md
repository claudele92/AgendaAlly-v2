# AgendaAlly UI/UX modernization proposal

**Status: Stage 1 visual direction and native authentication/navigation
integration approved on 2026-10-01. Implementation is pending; broader screen
redesign remains outside scope.**

## Preserve

Keep AgendaAlly's Laravel API, Next.js customer app, React/Ant Design business
portal, practical Flutter REST compatibility, identity, marketplace model,
country administration, vendor/branch permissions, booking, product commerce,
commissions, accounting and payouts. Modernization is not a framework rewrite.

## Evidence and limits

The native applications were inspected in the browser, not reconstructed from
mockups: customer homepage, country/city selection, shop-filtered product
catalog, product variant, cart, pickup/cash checkout, persisted pending order,
shop profile, branch selection, booking service selection, admin dashboard,
sidebar and Top Selling Products.

The final booking recheck also reached the actual calendar, Cash step,
successful New appointment confirmation and matching My appointments view.

Observed friction:

- The desktop homepage has substantial empty space before discovery content;
  booking and products compete within one search/navigation structure.
- The AgendaAlly brand is not consistently visible in the captured header.
- Product photography dominates a narrow detail column; the title wraps
  heavily, while product options, stock, merchant and purchase actions are
  split across multiple cards.
- Location, branch and fulfillment context is spread across controls.
- The checkout summary is useful, but unavailable fulfillment and payment
  states initially provided too little guidance. The engineering phase added
  actual local cash, area and pickup fixtures rather than faking success.
- The admin sidebar is long and separates closely related operational work.
  The dashboard repeats booking counts, percentages and amounts across a
  large vertical area before actionable information.
- Mixed-language/raw dashboard labels and repeated Top Selling rows were
  verified engineering defects, not design preferences; they were repaired
  in the underlying catalogs/query/rendering path.
- Booking needs an explicit distinction between online, at-venue and
  at-customer-location service modes. The local salon examples now identify
  their real at-venue mode.
- The specialist card's location-mode label and catalog-to-specialist price
  change need clearer, consistent explanations. The final booking itself
  persisted successfully through the native UI.

The remaining account, staff, finance and country-administration screens need
role-specific visual review during the approved design phase. A complete
mobile usability audit has not been claimed; responsive acceptance checks
must be part of that phase.

## 1. Proposed shared design system

Retain the existing neutral/black and warm bronze identity rather than
introducing an unrelated generic template.

- Establish semantic color tokens: background, surface, text, muted text,
  border, primary action, accent, success, warning, error and focus.
- Keep bronze as a brand accent; choose accessible foreground/background
  combinations instead of relying on white text over light bronze.
- Preserve Inter in the customer app; harmonize portal typography through
  Ant Design theme tokens, not a replacement component framework.
- Use a consistent spacing scale, two or three corner radii, restrained
  elevation and a predictable heading hierarchy.
- Define reusable buttons, inputs, selects, location/branch selectors,
  status badges, cards, tables, dialogs, drawers, steppers, empty/error/loading
  states and money/date formatting.
- Target WCAG AA contrast, keyboard navigation, visible focus and touch
  targets. Preserve localization, text expansion and RTL layouts.
- Mark local/demo environments clearly without altering production branding.

**Deliverables after approval:** token specification, existing-component
inventory and focused examples built from the real components.

## 2. Proposed navigation

### Customer

Make the two core activities explicit: **Book services** and **Shop products**.
Keep location/country visible and separate from the active business branch.
Provide persistent access to appointments, orders, favorites, cart and account.

On mobile, use a compact header and task-oriented navigation; keep the
primary booking or purchase action reachable without covering content.

### Vendor and staff

Group existing capabilities by work:

1. Overview
2. Calendar and bookings
3. Orders and POS
4. Services, specialists and schedules
5. Products, variants and inventory
6. Branches and team
7. Earnings, commissions and payouts
8. Business settings

Show the selected branch and permission scope clearly. Do not infer or expand
permissions from navigation visibility; server authorization remains decisive.

### Platform and country administration

Use a visible country scope and group the existing tools into marketplace,
vendors/branches, people/roles, operations, geography, finance and settings.
Keep country managers' restricted views distinct from global administration.

## 3. Proposed screens

| Area | Recommended improvement |
| --- | --- |
| Marketplace homepage | Shorter hero; explicit service/product entry points; useful category and nearby content visible sooner. |
| Search/discovery | Clear applied filters, result counts, sort controls, loading transitions and reset actions. |
| Categories | Consistent naming and imagery; distinguish service categories from product taxonomy. |
| Nearby/location | Country → city → area hierarchy; manual selection remains usable without Maps. Explain location permission and unavailable coverage. |
| Business profile | Clear service/product tabs, address, hours, policies, reviews and selected branch. |
| Branch selection | Display address, available services/staff and branch-specific availability before booking. |
| Service page | Price, duration, fulfillment mode, included details and eligible specialists together. |
| Specialist profile | Relevant services, branch, qualifications/reviews and availability; distinguish presence from service mode. |
| Availability/calendar | Accessible slot selection, timezone, unavailable reasons and schedule/break handling. |
| Booking | One coherent stepper with editable summary and consistent back behavior; cash/on-site vs remote payment clearly labeled. |
| Product discovery | Inventory-aware cards and filters, merchant context and correctly formatted currency. |
| Product detail | Balanced image/details layout; options, stock, quantity and main action grouped. |
| Cart | Clear grouping by vendor/branch, editable quantities, availability and transparent totals. |
| Checkout | Fulfillment first; progressive address requirements; explicit unavailable methods; persistent summary and no false payment success. |
| Customer account | Appointments/orders/favorites/profile in a consistent account shell with useful status history. |
| Vendor dashboard | Compact key metrics plus today's appointments, pending orders and operational exceptions. |
| Vendor navigation | Task groups above; branch context preserved across screens. |
| Staff/master management | Separate identities, permissions, invitations, branch assignments and schedules. |
| Bookings/orders | Shared table/list conventions, filters, status history and permission-aware actions. |
| Payments/earnings | Distinguish pending, earned, paid and available balances; show currency, commissions and reconciliation details. |
| Admin dashboard | Less repeated chart decoration; actionable marketplace/country summaries and genuine ranked-product data. |
| Country administration | Visible scoped context, grants and geography relationships with explicit empty/access states. |
| Responsive/mobile web | Review at 390px, tablet and desktop; test drawers, long labels, tables, calendar and sticky actions in LTR/RTL. |

## 4. Recommended sequence

1. **Design system and navigation first.** Agree on tokens, role-aware
   navigation and branch/country context before restyling individual screens.
2. **Customer discovery and transaction screens.** Homepage, search, business
   profile, booking, product detail, cart and checkout.
3. **Business operations.** Dashboard, calendar, orders, team, catalog and
   inventory using the approved system.
4. **Platform/country/finance and responsive completion.** Complete the
   specialized views, accessibility and RTL/mobile acceptance checks.

Each stage should show focused proposals for approval before applying broad
changes. Keep business rules, API response contracts and authorization intact.
No production access, live integrations or publishing is authorized by this
proposal.

## Next approval boundary

Stage 1 is approved. Review the shared visual system, role-aware navigation,
Customer login/registration and Admin/Vendor authentication proposals on the
Canvas, with extracted current screens for comparison. See the
[Stage 1 delivery and limits](stage-1-delivery.md).

The separately approved installer cleanup is implemented in the native
business app. The proposed visual changes remain isolated; review and approve
them before native visual integration or broader screen restyling.