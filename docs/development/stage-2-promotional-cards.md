# Focused homepage promotional pathways

Authorized by the creator's two-card redesign brief on 1 October 2026.
Only the canonical homepage's final promotional section was changed. Existing
business discovery above and the footer below are retained.

The former full-photo dark overlays and Download actions are replaced with
related ivory/ink/bronze cards. Each has its own readable copy surface, small
semantic icon, one native navigation link, and a separate image panel.
Desktop uses two balanced split cards; mobile stacks the cards with full-width
copy and a 160px supporting image panel. Existing Stage 2 button/focus patterns
and Next Image lazy loading/responsive sizing are reused.

## Copy and routes

- **Customer:** Find and book your next appointment
  - Discover services and specialists that fit your needs.
  - Explore services → `/services`
- **Business:** Grow your business with AgendaAlly
  - Manage bookings, services, products, staff and your business operations.
  - Explore AgendaAlly for Business → `/for-business`

These are existing public routes, not new discovery/authentication behavior or
app-store destinations. The two CTAs use ordinary same-tab anchors: the bounded
browser pass observed stalled Next client navigation while direct navigation
reached both public destinations. No shared router/proxy investigation or fix
was attempted. The old shared MobileCard file is not deleted or changed.
No database, seed, API, locale/location state, booking, commerce, finance,
Flutter, proxy or production configuration was changed.

## Promotional media provenance

Both photographs are synthetic promotional illustrations, not actual
AgendaAlly businesses, specialists, customers or catalog images. Image alt
text identifies their illustrative intent. No database media assignments change.

### Reused customer illustration

`public/stage2/stage2-tailoring.jpg` is the approved Stage 2 local illustration:
a Black African tailor discussing fabric with an older East Asian woman.
Its original generation provenance remains in `stage-2-media-provenance.md`.
1024×1024 JPEG, 142,044 bytes.

SHA-256: `42b37724d9a5c184d17d030adf180199bcccbc1f53559010f325ae3755609184`.

### One new business illustration

Generated through Replit image generation, high resolution requested. Actual
result: 1024×1024 JPEG, 142,454 bytes. No provider/model version or random seed
was exposed; no independent commercial licensing claim is made.

Master: `attached_assets/generated_images/agendaally-business-cta-tablet.jpg`.
Stable application copy: `public/stage2/agendaally-business-cta-tablet.jpg`.

SHA-256: `3262a7e3dced87ffefcf8ee62a9be9dcc9587e658217b303ae1e52c85546ceed`.

Exact generation prompt:

> Square editorial promotional photograph for AgendaAlly, a broad Africa-first
> local services and commerce marketplace. A Black African woman entrepreneur
> about 35 with natural textured hair, contemporary simple ivory shirt,
> confidently reviewing her tablet at a small business worktable in a bright
> modern workspace. Tablet clearly visible in her hands, realistic anatomy.
> Neat unbranded kraft packaging and a few neutral artisan homewares on shelves
> behind her suggest an independent services-and-commerce business, NOT a beauty
> salon, spa, corporate office or generic handshake scene. Natural tropical
> daylight, warm ivory plaster, walnut wood and restrained bronze details,
> professional credible quiet small-business setting. Compose her face and tablet
> together within the central portion of the square, enough room for both
> portrait and wide responsive cropping. No readable text, no logos, no fake
> identifiable company, no metrics. Photorealistic synthetic promotional
> illustration, not a depiction of an actual AgendaAlly vendor or customer.

## Bounded verification

TypeScript and scoped CSS/route checks passed. One browser pass confirmed
1440px desktop cards at 570×294px, stacked ~390px mobile cards with 160px image
panels, loaded images, readable copy, no horizontal overflow, keyboard Tab order,
visible 3px bronze focus rings and CTA targets of at least 44px.
Both direct public destinations rendered. The client-navigation stall above
was addressed only by replacing these two Next Link components with ordinary
anchors; the layout/focus markup is otherwise unchanged. Final route/source
checks cover that straightforward correction; no second broad browser pass.
No new app runtime exception was observed; existing development/image preload
warnings were not investigated.