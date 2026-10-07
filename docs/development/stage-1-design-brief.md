# AgendaAlly — Stage 1 visual proposal

Status: visual proposal authorized; broad application restyling is not yet
approved. The separately approved public-installer cleanup is implemented
and verified independently of these mockups.

## Product and direction

AgendaAlly connects customers with local service professionals and products,
while giving vendors, staff and country administrators a practical workspace.
The identity remains black/neutral with warm bronze. The proposal should feel
confident, welcoming and professional, not like a purchased software template.
Use the original Inter font as the working typography basis.

## Representative surfaces

- Shared visual-system reference: typography, semantic palette, spacing,
  buttons, inputs, cards, status and focus/error/disabled states.
- Customer email/phone login, password recovery and registration progression:
  contact + terms, verification, then profile/password completion.
- Admin/Vendor login: the existing shared email-or-phone/password flow,
  configured verification and role-derived destination after sign-in.
- Responsive customer and business authentication at 390px.
- Customer navigation separating Book services and Shop products.
- Business navigation grouped by work, with visible branch context.
- Platform/country administration with explicit scope; staff, masters and
  finance retain their restricted workspaces.

Source references: native customer app/(auth) and components/auth; native
business views/login, components/sidebar and configs/menu-config. Extracted
Current screens are the comparison baseline. New mockups are isolated from
native authentication and never make API calls or pretend to authenticate.

## Proposed token basis

Values are proposals, not a production token migration.

| Semantic role | Working value |
| --- | --- |
| Canvas | #FAF8F4 |
| Surface | #FFFFFF |
| Primary text/action | #191A19 |
| Muted text | #64665F |
| Border | #E5E2DC |
| Bronze accent | #916D41 |
| Bronze text | #76522E |
| Bronze tint | #F0E7D9 |
| Success | #276447 |
| Error | #AC3D36 |
| Focus | #76522E |

White text belongs on black primary actions, not on light bronze. Use bronze
for brand details and selection emphasis. Target WCAG AA foreground contrast,
visible keyboard focus and comfortable touch targets. Use a consistent
4/8-based spacing scale, restrained elevation and modest corner radii.

## Asset and permission boundaries

New authentication photographs are static, generated application assets.
Keep their canonical files in the native customer public assets and native
business source assets, with local copies for Canvas previews. They are not
marketplace catalog records or seeder dependencies. Marketplace demo
photography remains governed by existing reproducible development seed data.

The navigation proposal maps existing capabilities; it neither adds roles nor
grants permissions. Country/branch restrictions remain server-authoritative.
Selecting a role in a proposal is a review control, not a login or impersonation
feature. Optional social authentication must be shown only when configured.

Configured environments must enter normal authentication/application flows.
Invalid configuration must fail visibly and safely with no public installer,
setup form, database creation or admin-creation action. Backend installer
endpoints stay disabled. No production access, publishing, schema changes,
external providers, framework migration or financial behavior changes.

## Acceptance for this proposal

Present the actual visual system and source-based representative screens on
the Canvas, including mobile authentication. Review the result before
integrating broad visual changes into the native applications. Keep the
prototypes explicitly separate from functioning authentication.

## Native integration status

The creator approved this visual direction and native Stage 1 authentication
and role-aware navigation integration on 2026-10-01. That integration is now
implemented in the native clients; Canvas components remain isolated review
proposals, not evidence of real authentication. See
[Stage 1 implementation](stage-1-implementation.md) for verification and known
limits. Stop for native visual review before Stage 2; the approval does not
extend to broader transaction-screen redesign or production operations.