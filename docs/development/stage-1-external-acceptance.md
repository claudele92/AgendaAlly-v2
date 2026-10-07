# Stage 1 external acceptance — 2026-10-01

**Status: ACCEPTED — bounded native development acceptance, 2026-10-01.**
The remaining Footer/canvas browser verification and existing-capability
development representation passed. This authorizes the Stage 2 **design
proposal only**, not native redesign implementation, publishing, production
operations, live payments/messages or provider activation.

## Final Stage 1 acceptance matrix

This matrix supersedes the pending and historical states preserved below.
Completed evidence was retained rather than repeated. PASS describes the
authorized development scope, not every possible application condition.

| Criterion | Classification | Evidence / limitation |
| --- | --- | --- |
| Native external transport and creator-owned reload checkpoint | PASS | Creator-confirmed customer geography and Admin login/reload evidence preserved; no unnecessary connectivity rerun |
| Customer native login/session, location and navigation | PASS | Existing real-browser checkpoint retained; final affected-path run retained Customer 102 without injected auth |
| Service/product/business/specialist discovery and seeded provenance | PASS | Existing real catalog, price, branch and media evidence retained; persisted synthetic seed provenance remains reproducible |
| Unique search and honest location/distance treatment | PASS | Hair Care returned one unique result without fabricated distance; existing accepted evidence preserved |
| Service/appointment focus and mobile navigation | PASS | Existing desktop and 390px keyboard/Escape focus checks preserved; no booking mutation |
| Super Admin context/navigation | PASS | Existing genuine native global-admin checkpoint preserved |
| Country Manager scope and 390px table containment | PASS | Existing own-501 200 / actual foreign-502 404 and contained scrolling preserved |
| Vendor Owner context/readable description | PASS | Existing native 390px wrap/no-clipping checkpoint preserved; no edit/save |
| Staff assigned branches and workspace | PASS | Existing genuine session and assigned 2/4 versus denied 1/3 evidence preserved |
| Master personal scope and workspace | PASS | Existing genuine session/reload and real foreign-resource denial evidence preserved |
| Registration validity, Terms and authorized verification boundary | PASS | Existing malformed-email/Terms checks and single permitted synthetic submission preserved |
| OTP retrieval, resend, delivery and completed registration | NOT EXERCISED / NOT APPLICABLE | Beyond the authorized verification boundary; no delivery or completion claim. Not a bounded Stage 1 blocker |
| Scoped missing/anonymous empty carts | PASS | Existing artwork/no-optional-query behavior and unchanged Yaoundé cart snapshots preserved; no repeated cart actions |
| Footer hydration, state and keyboard behavior | PASS | Fresh desktop/390px native buttons; click closes and removes links, Enter/Space reopens; no fresh hydration error |
| Canvas lifecycle in actual accepted journey | PASS | 390px Home-3 → Blog → Back → Blog: canvas 1 → 0 → 1 → 0, gradient remount visible, no pageerror or `uniformMatrix4fv` error |
| Missing marketplace image fallback | NOT EXERCISED / NOT APPLICABLE | No eligible reproducibly seeded missing image or actual broken image encountered. Source fallback evidence retained, no browser-fallback claim, no manufactured fixture. Not a blocker under creator-approved conditions |
| Unavailable-WebGL decorative fallback | NOT EXERCISED / NOT APPLICABLE | Real browser supplied WebGL; null-context/teardown source regression exists. No forced browser mock or fallback claim; no observed current failure requiring it |
| Existing CMS/legal/blog/footer/support representation | PASS | Owned seed creates 3 articles, 3 About sections, 4 FAQs, English Terms/Privacy and safe footer/social settings; affected native routes return 200 and render |
| Content media in actual affected journeys | PASS | About's three images, all three blog-card images and actual article hero loaded; no missing image encountered |
| Legal/contact/social/download honesty | PASS | Non-operative owner/legal-review notices; reserved example social labels; contact has no invented office/map/form; no fake store links |
| Safe original payment-method representation | PASS | Original 18 catalog identities, cash only active, 17 inactive including wallet; actual REST filtering tested; hardening remains unchanged |
| External charges, refunds, callbacks, settlement and payouts | NOT EXERCISED / NOT APPLICABLE | Explicitly prohibited and unavailable in this development scope. No fake successful operation or provider-verification claim; not production payment acceptance |
| Owned bootstrap, repeatability and owner-data preservation | PASS | Focused full-demo/repeat/partial/rollback tests passed; content-only seed applied twice; before/after marketplace and financial snapshots unchanged |
| Affected source checks and native production compilation | PASS | Current customer offline production build, TypeScript and 6 regressions passed; unchanged business build retained; focused backend checks below |
| Maps provider configuration and full Android/iOS builds | NOT EXERCISED / NOT APPLICABLE | Maps remain disabled; no key/provider operation. Full native builds remain a separate known follow-up, not a new Stage 1 gate |

**No genuine Stage 1 blocker remains.** Non-blocking legacy presentation items
for the Stage 2 proposal: FAQ answer contrast/spacing, article-detail date format
(`Th Oct, 2026` versus list `01 Oct, 26`), image `sizes`/placeholder warnings,
Lottie lifecycle deprecation, and media relevance/rights review. These do not
invalidate the accepted native functional paths or authorize broad fixes now.

### Final affected-path browser and seed evidence

- Fresh native Home-3 and `/about`, `/faq`, `/terms`, `/privacy`, `/contact`,
  `/blogs` and actual `/blogs/3` returned **200** and remained usable.
- Information/Help/Social desktop toggles and Information at **390×844**
  matched panel visibility to `aria-expanded`; closed links were removed.
  Keyboard reopening passed. Social examples and unavailable store links were
  visibly labeled. No external example/social/download destination was launched.
- Mobile canvas navigation/unmount/remount produced no fresh hydration,
  pageerror or WebGL exception. Earlier retained compile-overlay output did not
  reproduce on fresh navigation; the intermediate genuine Footer visibility
  mismatch was repaired and rechecked before acceptance.
- Screenshots: `m0ykrr`, `yx7thb` desktop Footer; `xwrttb` mobile Footer;
  `rg7ewr` mobile remounted Home-3; `kfdxlq` Blog list; `9cfk2i` detail;
  `nmhqdb` Terms; `4isc4u` Contact; `7ty7v5` FAQ.
- Known scoped cart GET **404** reads from the page shell are expected absence,
  not new cart failures. No cart flow, login, OTP, message, review, provider,
  marketplace fixture write or other-role rerun was performed in this final pass.
- Content/full-demo repeat/partial/rollback checks: **4 tests / 722 assertions**.
  Payment catalog/real REST filter: **1 test / 67 assertions**. Footer/content:
  **6 tests**. Development configuration/transport: **11 tests**. Web TypeScript,
  PHP syntax and current customer offline production compilation passed.
- The content-only seed ran twice through the existing local ownership,
  manifest, path and exclusive-lock guards. Shops, branches, products, stocks,
  users, wallets, bookings, carts/children, orders/details, transactions,
  wallet histories, payouts and fee ledger snapshots were unchanged.
- Compact original-capability audit and applied representation:
  [`preview-capability-matrix.md`](preview-capability-matrix.md). Payment
  architecture distinctions: [`payment-capability-matrix.md`](payment-capability-matrix.md).

## Preserved acceptance history

Pending states below are historical, superseded by the final matrix above.

**Creator-confirmed external-preview checkpoint — 2026-10-01:** In their own
browser, Admin/Vendor initialized without Network Error, native Admin login
survived reload, Customer selected Cameroon → Douala and retained it after
reload, and seeded storefront content loaded. The narrow development proxy
and request trace are preserved in
[`external-preview-connectivity.md`](external-preview-connectivity.md).
Resume **only** remaining acceptance checks. Do not repeat Staff/Master,
connectivity, Admin login/reload or customer geography persistence checks.
This confirmation removes the preview blocker, **not** the Stage 1 acceptance
gate. No Stage 2 work is authorized.

## Remaining-check update after creator confirmation

Completed checks below supplement, rather than replace, the valid Staff/Master
and creator-confirmed connectivity evidence. Those checks were not repeated.

| Remaining criterion | Result | Native evidence |
| --- | --- | --- |
| Customer products / price controls | PASS | Products rendered after an initial stale homepage snapshot settled within 15 seconds; no captured hydration error in the catalog/price interaction. This was not treated as a persistent navigation failure. |
| Search integrity | PASS | Hair Care returned one unique Wouri Beauty Bar, without a fabricated distance. |
| Shop-501 service and branch interaction | PASS | Genuine Douala catalog and branch controls exercised. |
| Service-detail and appointment focus | PASS after repair | Closing service details restores the originating card/extras control after transition. Actual desktop X and 390px Enter/Space → Escape checks passed; the existing Haircut booking #3 appointment-sheet focus check also passed. No booking was created or changed. |
| Representative visible category media | PASS | Five visible category images loaded. This is not nullable-marketplace-media proof. |
| Country Manager mobile containment and foreign detail | PASS after repair | 390px table scroll remains internal. Genuine existing browser session returned own shop 501 **200** and existing foreign shop 502 **404**, without foreign data. The former null `fresh()` dereference was repaired; the denial itself remains enforced. |
| Owner description readability | PASS after repair | Native `/my-shop/edit`, 390×844: existing 106-character description wraps in a 217px-client-width field; `scrollHeight = clientHeight = 122px`, without internal scrolling or clipping. Document width 375px fits the 390px viewport. No field was changed or saved. Screenshot `hligoq`. |
| Registration Terms and legitimate contact boundary | PASS within authorized boundary | One permitted synthetic `.test` contact submission reached Email verification with six empty boxes. No OTP access, resend, completion or delivery claim. |
| Registration email validity | PASS after semantic input repair | Source already had a Yup email/required submit guard. Email input now also uses native `type=email`. Actual malformed no-`@` input fails `checkValidity()` with Terms checked; reused valid `.test` input passes, and unchecked Terms disables Sign up. No new contact was submitted. Screenshots `w5k9d4`, `do6v21`. |
| Absent-cart handling and empty-cart artwork | BEHAVIOR PASS; RUNTIME RECHECK PENDING | Genuine Customer 102 / Douala 1 missing-cart GETs returned **404**, rendered visible empty artwork and no alert, without calculation/payment/open/write requests. Anonymous empty cart likewise displayed loaded artwork without those requests after native prerequisite location setup. The same pass captured runtime errors; see below. |
| Nullable marketplace media | NOT EXERCISED / NOT APPLICABLE | All eligible reproducibly seeded rendered images are populated. No missing/broken image was encountered in the accepted journeys. Existing source/regression evidence is not browser verification. Per creator instruction, do not manufacture a fixture; this theoretical condition alone is not a blocker unless an actual preview failure requires the fallback. |

### Cart evidence and limits

- Customer 102 has an existing **Yaoundé / city 2** cart; it is not globally
  cart-empty. The selected **Douala / city 1** scope has no matching cart.
  Source inspection confirms the scoped missing-cart GET returns before the
  legacy cleanup/write path.
- A fresh anonymous `/cart` check loaded `/img/empty_cart.png` successfully
  (`complete=true`, natural width 351px), behind the mandatory location dialog.
  It also exposed two public product-calculation **422** requests for an empty
  products payload. That check did **not** pass the no-calculation criterion.
- The missing anonymous calculation guard is repaired. Disabled empty queries
  do not leave a loading screen; nonempty malformed carts and genuine
  calculation errors show an alert rather than a false empty state.
- Source review corrected an earlier testing report: the authorized
  `orderService.paymentList` query already had a resolved-cart/nonzero-details
  guard. Regression checks now bind assertions to each actual query key and
  service call, rather than matching an unrelated enabled predicate.
- No group-cart route, cart add/remove/reset, checkout, payment-provider
  activation or existing marketplace-data mutation was performed.

### Final cart pass and source-only runtime repairs

- Genuine Customer 102 / Cameroon 1 / Douala 1 state was confirmed before the
  ordinary cart visit. Scoped cart GETs returned **404**; the missing-cart UI
  displayed artwork and its empty message. No calculate, payment-list, open,
  POST or DELETE request was recorded. Screenshots `mvuafl`, `re0902`.
- Fresh anonymous `/cart` was reloaded and the mandatory native Cameroon →
  Douala selection saved once as setup, not a repeated persistence acceptance.
  Artwork was visible and loaded. Background public reads returned **200**;
  there were zero calculation/422, payment, protected-cart, open, POST or DELETE
  requests. Screenshot `lar9ac`.
- Read-only before/after cart snapshots agree: original cart 1 remains in
  city 2 with total **9600**, one user-cart row, one detail row and one
  detail-product row with quantity **1**. No existing cart was emptied.
- **The pass was not clean-runtime acceptance.** The signed-in cart recorded a
  hydration mismatch. Its retained React diff specifically identified Footer
  Headless UI disclosure triggers: server `<div>` versus client `<button>`.
  Anonymous location setup also recorded two
  `Cannot read properties of null (reading 'uniformMatrix4fv')` errors. Their
  stacks were not retained, so library attribution is not established.
- Source-only repair now makes Footer trigger element types stable native
  buttons. The app-owned home-3 WebGL gradient additionally has a null-context
  decorative CSS fallback and owned animation-frame/timer/listener cleanup
  on unmount. This is a defensive repair to a plausible graphics candidate,
  **not proof** that it caused the recorded exception. Cart auth stores were
  not changed based on an unconfirmed hypothesis; SSR was not globally disabled
  and hydration warnings were not suppressed.
- Footer/canvas regressions passed **2 tests**, JavaScript syntax checks and
  web TypeScript checking. Customer workflow was restarted and reported ready;
  existing toolchain/catalog-age warnings remain. The last source-only runtime
  batch still requires a focused native-browser check before acceptance.

Latest focused client regressions: **10 passed**, including native error
contract, query-specific guards, signup semantics and modal focus. Web
`tsc --noEmit` passed for the relevant source batch. Owner responsive source
checks passed **2 tests** plus JSX syntax checking. Current isolated Laravel
Hardening passed **64 tests / 484 assertions**, with one existing deprecation.
These checks do not substitute for the explicitly pending browser/fixture
criterion. Maps remain disabled; Stage 2 remains blocked.

**Historical recovery update:** Staff and Master final-source browser checkpoints
below remain valid. The earlier `Browser closed`
and `Durableptc worker disconnected` failures remain historical infrastructure
evidence, not current application failures or accepted browser checks.
After the final source batch, managed native customer/business/Laravel
workflows were restored and startup logs were clean apart from existing
toolchain deprecation/catalog-age notices. External HTTPS requests to customer
`/login`, business `/login` and Laravel `/api/v1/rest/countries?lang=en` each
returned **200**. These requests establish restored transport only, not the
missing browser rechecks.

### Post-restart external HTTP evidence — 2026-10-01

- The development hostname is unchanged; all three external origins below
  were rechecked. Customer `/login`, business `/login` and Laravel public
  countries returned **200** after public-origin configuration was restored
  using the existing `origins --replit` command.
- Laravel's countries response explicitly allows the actual customer HTTPS
  origin, with `Access-Control-Allow-Credentials: true` and `Vary: Origin`.
  This confirms the sampled HTTP CORS response, not browser-wide network health.
- Public API reads returned Cameroon (country 1) and Douala (city 1), alongside
  the other seeded Cameroon cities. These are not proof of selector interaction
  or location persistence after the restart.
- Public reads returned seeded Douala businesses, six shop-501 services,
  specialist 112 and five shop-501 products. Representative names included
  Wouri Beauty Bar, AgendaAlly Learning Hub, Bridal Makeup, Armand Fotso and
  Wide-Tooth Detangling Comb. These are API results, not a new rendered-UI pass.
- Three public seeded Unsplash media references returned **200 / image/jpeg**.
  Actual image rendering and nullable-media coverage still require the browser.
- The restarted hardening workflow passed **63 tests / 483 assertions** with
  one existing deprecation. Source regressions do not replace genuine browser
  authentication, session persistence or authenticated scope probes.
- No API-assisted login, injected authentication, OTP access, provider delivery,
  database reset, marketplace-data mutation or Stage 2 work was performed.

## Actual native development origins

- Customer: https://b762a632-9df8-4939-8b17-506d75cfa277-00-2ngxdayklmnxt.worf.replit.dev:3002/
- Business: https://b762a632-9df8-4939-8b17-506d75cfa277-00-2ngxdayklmnxt.worf.replit.dev:3003/login
- Laravel: https://b762a632-9df8-4939-8b17-506d75cfa277-00-2ngxdayklmnxt.worf.replit.dev:8000/

The browser used these HTTPS origins, not localhost, proxy-loopback captures,
Canvas, the unrelated API scaffold, mocked responses, injected authentication
or browser-only marketplace records. Logins used the native forms.

## Fresh post-restart Staff browser evidence

- Native business form login succeeded. Navigation context before/after real
  reload returned user **114**, `shop_manager`, shop **501**, restricted
  locations **[2,4]**. Calendar and Bookings navigation rendered intentionally.
- Desktop and 390×844 navigation passed; the mobile drawer showed the verified
  workspace. Document/client widths were both 375px within the 390px viewport.
- Genuine-session protected location list returned **200 / [4,2]**.
  Assigned details 2/4 returned **200**; unassigned details 1/3 returned
  expected **404**. No credentials or tokens were printed or injected.
- Seller-profile responses serialized locations 2/4 only. Real country
  geography matched assigned location 2; Yaoundé city and forged geography
  returned no matched location. No unassigned branch was disclosed.
- No unexpected UI request failures, CORS errors or blank workspace occurred.
  Expected denial-probe 404s are separate from normal UI errors. Existing
  Ant Design deprecations and Redux Date warnings remain.
- Screenshots: `w61szt` desktop Calendar/reload; `kwp5d0` mobile drawer;
  `mfa5nd` final mobile Calendar.

**Legacy GET side-effect caveat:** the tester's read-only source inspection
found that seller `shopShow()` conditionally deletes expired subscription
rows. Only GETs were issued, but absence of a historical cleanup mutation
cannot be retroactively certified. Subsequent read-only SQLite inspection
found **zero shop-subscription rows globally**, and Laravel uses UTC; thus
the cleanup predicate currently has no rows to remove. Subscription behavior
was not changed, and no deliberate business mutation was performed.

## Preserved fresh Master checkpoint

- Genuine native Master login and real reload retained user **112** / `master`
  / shop **501**. Personal Calendar rendered seeded October 15/16 Haircut
  entries; no Reports navigation or owner/global-report grants were present.
- Protected personal bookings returned three own records, services returned
  six own assignments, and working days returned seven own dates. Actor-ID
  overrides did not expose another specialist's private records.
- Actual foreign service-master **68** and service **48**, verified to exist
  in the public shop-509 catalog, returned **404** on protected Master detail
  routes. These were not fabricated nonexistent-ID denials.
- Desktop and 390px Calendar fit the document width. Mobile drawer Escape
  restored focus to Toggle navigation. Screenshots: `el3gsk`, `u01xgd`,
  `u7tody`. Existing Date warnings and expected denial-probe logs are separate
  from unexpected UI/network errors.
- This completed checkpoint is preserved; no further role testing is
  needed merely because the creator later confirmed external-preview
  connectivity. Do not repeat this valid checkpoint.

## Pre-restart browser matrix — not post-restart acceptance

| Requirement | Current result | Evidence / remaining work |
| --- | --- | --- |
| Customer real login and reload | PASS | Signed-in header and two appointments retained after real reload. |
| Cameroon → Douala selection and persistence | PASS | Actual selectors, save, login/reload and post-logout public drawer. |
| Categories, shops, specialists, products, recommended content | PARTIAL | Populated real listings and images; service/branch catalog interaction still needs completion. |
| Marketplace image rendering | PARTIAL | Shop/portrait photos and all nine inspected product-page images loaded; missing empty-cart artwork repaired, pending browser recheck. |
| Customer clean hydration/network behavior | PENDING RECHECK | Price-filter mismatch and false empty-cart exception repaired after observed failures. |
| Search result integrity / geography | PENDING RECHECK | Duplicate shop rows and unsupported map-center distance repaired; safe REST regression passes. |
| Customer desktop/mobile navigation | PASS / PENDING RECHECK | 390px drawer routing, Escape focus return and no overflow passed; appointment-sheet focus correction pending recheck. |
| Registration legitimate boundary | PENDING | No current-run submission/verification claim. |
| Super Admin real login, context, reload | PASS | Native role `admin`, user 103, unrestricted context; restored real top-products response 200, counts 3/2/2. |
| Country Manager real login/context/reload/mobile | PARTIAL | User 141, Cameroon, stable context and drawer; table containment and stronger foreign-detail probes pending. |
| Vendor Owner real login/context/reload/mobile | PARTIAL | User 107, shop 501, all four branches, stable reload/drawer; long-value readability recheck pending. |
| Vendor Staff real login/context/reload/calendar | PARTIAL / PENDING SECURITY RECHECK | User 114, shop 501, assigned 2/4; Calendar startup passed, but protected location reads leaked 1/3. Server fix pending browser recheck. |
| Master real login/context/reload/calendar | PENDING | Remaining real-browser journey. |
| Server country/shop/branch/personal enforcement | PARTIAL | Isolated real-route regressions and security review pass; current browser-authenticated positive/negative probes pending. |
| Displayed data provenance | PASS for sampled displayed records | Owned seed marker, persisted rows, original API call chains and rendered names agree; details below. |
| Current-source builds/regressions | PASS for completed changes | Both clients compiled; customer recompiled after later repairs. Current checks listed below. |

## Reproducible seed provenance

Read-only inspection of the owned
`.migration-backup/backend/database/development/agendaally.sqlite` returned
`environment=local`, `schema_version=1`, `demo_seed_version=1`, and migration
SHA-256 `bf994251e0cb8ef0724587fffda0e5e62ec3dca5b4fb4cc327a625c7c6554e46`,
matching `database/development/manifest.php`. Integrity check was `ok`.
The ledger includes the reviewed migrations and separate ownership marker.
No development database reset, replacement or destructive migration was run.

`DevelopmentDemoSeeder` orchestrates reviewed geography, service, shop,
category and product seeders inside its owned-development guard. These are
persisted synthetic development records, not isolated regression fixtures:

| Type | Representative persisted record | Reproducible source / observed UI |
| --- | --- | --- |
| Geography | Cameroon country 1, Douala city 1, XAF | `DemoAfricaSeeder`; actual selectors and saved header. |
| Shop / branches | Le Sawa Beauty Studio shop 501; Douala locations 2/4, Yaoundé 1/3 | `UserSeeder` plus development normalization/geography; actual listing/profile and branch controls. |
| Other Douala shops | Wouri Beauty Bar 505, Learning Hub 508, Ink & Piercing Studio 509 | Expansion/education seeders; actual public shop cards. |
| Category / service | Hair Care 1, Haircut category 2, Haircut service 1 | Service/category seeders; category grid and existing appointment detail. Public service catalog interaction still pending. |
| Specialist | Armand Fotso user/master 112, accepted active assignment and working days | Reviewed specialist fixture; actual masters list and Haircut appointment. |
| Product | Moroccan Argan Oil Hair Serum product 1, published/active with stocked variants | `ProductCatalogDemoSeeder`; actual product listings/profile. Cards may represent separate stock variants. |
| Recommended content | Eligible approved Douala shops in existing homepage query | Original API-backed widget sorts `r_avg DESC`; sampled seed ratings tie at zero, not a claimed curated ranking. |
| Media | Persisted Unsplash shop/product/portrait references; local category SVGs | Original resources/image components; observed loaded shop/product/master imagery, not static auth artwork. |

The original homepage/list/detail components call the real Laravel REST
services. The separate Canvas mockup deliberately contains illustrative
records and is **not** used as this acceptance's data or image proof.
Authentication/brand artwork is separate static UI material.

## Repairs and regression evidence

- Customer appointment controls no longer nest buttons; empty selection is
  guarded and the selected trigger is retained for modal focus restoration.
- Price inputs use canonical finite values for SSR/client parity.
- A strictly identified absent-cart response maps to an empty sentinel;
  other errors remain visible. Calculation/payment reads wait for a real
  resolved cart with items. The existing REST absent-cart contract is retained.
- Empty-cart artwork now references an existing asset.
- Public shop pagination is distinct and deterministically ordered.
  Map/default coordinates are not claimed as permission-derived user position.
- Calendar service queries use verified actor/shop context and valid IDs;
  staff/master do not acquire owner settings or report grants.
- Disabled Firebase startup is quiet; enabled failures and request errors
  retain nonblank reporting.
- Restricted finance read authorization fails closed for ambiguous,
  mixed/null geography and conflicting creator associations. Country filters
  cannot hide disqualifying associations. Shared plan metadata, unrestricted
  access, seller creator ownership and accounting behavior are preserved.
- Architect reproduced and verified closure of the payout association leak.
- Seller location reads now apply assigned branches before pagination and
  deny unassigned/foreign details; the shop-profile location relation and
  related personal working-day read chains are also scoped. Ownership retains
  all locations. Booking-wide permission is not treated as a location-read
  grant. The observed staff leak is not accepted until browser rechecked.
- Seller profile `matched_location` uses the same authorized branch set,
  including forged geography inputs. Ordinary accepted specialists with
  null staff-role fields remain eligible within the reader's assigned branches;
  the reader's role/grants are not widened. Architect reviewed both remedies.
- Mobile shop lists now contain their horizontal table scrolling; owner
  description values wrap rather than clip. No blanket body overflow hiding.

Current recorded checks:

- Client/configuration regressions: **101 passed**.
- Laravel development: **29 tests / 628 assertions passed**.
- Current isolated Hardening: **63 tests / 483 assertions passed**.
  Focused country/branch/profile/specialist checks: **7 tests / 80 assertions**.
- Public shop REST regression: **1 test / 21 assertions passed**, using
  isolated Hardening SQLite, not the default destructive Feature suite.
- Original booking/product domain baseline: **3 tests / 49 assertions**;
  existing warning/deprecation output remains.
- Native customer and business offline production compilations passed;
  customer compilation was repeated after its later source repairs.
  Business compilation was repeated after mobile containment repairs.
- Browser capture and large compilation are separated. During business-only
  verification the unused customer preview is paused; previews are restored
  before final acceptance. The development customer launcher now explicitly
  uses the supported Webpack engine to reduce preview memory pressure.

## Current-run managed browser screenshots

| ID | Captured evidence |
| --- | --- |
| `6iic2o` | Cameroon visibly listed. |
| `my9ljl` | Saved Douala/Cameroon header. |
| `7z6xcm` | Populated native shops and photos. |
| `2x43fe` | Le Sawa profile, address, branches and banner. |
| `yka3em`, `25ffpi` | Desktop/mobile products and serum photos. |
| `g69cjs` | Haircut appointment, Armand Fotso. |
| `g7rces` | Mobile specialists and portraits. |
| `cc78z5`, `wsmka6` | Authenticated/public mobile drawers. |
| `otfjuv` | Pre-repair duplicate search cards and false distance; **failure**, not final acceptance. |
| `84jk2a`, `0sl1re`, `3ru5lr` | Native business login/global admin dashboard. |
| `g97xtt` | Authorized Country admins route. |
| `l22qy2` | Logout confirmation before browser worker loss. |
| `6uza15`, `i3yc6p` | Interrupted business hosting availability; **failure**, not final acceptance. |
| `j8uri4` | Staff Calendar rendered at 390px without the former undefined-ID failure. |
| `ouoqco` | Staff Bookings; accompanying protected probes exposed the pre-repair branch leak. |
| `qmuoag` | Pre-repair Country Manager mobile shop-table overflow. |
| `x1ve25` | Pre-repair Owner mobile details with clipped long values. |

All final recheck outcomes and remaining actor/registration evidence must be
added before this record can say accepted. No publishing, production,
provider delivery, permission expansion or Stage 2 design work is authorized
or implied.

## Remaining checks after creator confirmation

1. Recheck only the repaired Footer/graphics runtime paths. Cart absence,
   artwork and no-optional-query behavior passed; do not repeat unrelated roles
   or already valid connectivity/catalog/registration checks.
2. Preserve nullable-media as NOT EXERCISED / NOT APPLICABLE for the current
   seeded catalog. The creator declined synthetic missing-image fixtures:
   do not create, mutate or remove marketplace records solely for this test.
   Reopen it only if a naturally encountered missing/broken image is a real
   preview failure; retain source/regression evidence without a browser claim.
3. Preserve all completed customer, Country Manager, Owner, registration,
   Staff/Master and creator-confirmed evidence. Registration stops at the
   already reached verification boundary: no OTP, resend or completion.
4. Accept Stage 1 only when all remaining criteria pass. Stage 2 stays blocked.
5. Before closure, audit and reproduce existing payment-method, CMS/legal,
   blog, social, footer, support and download configuration through the owned
   idempotent development bootstrap. No invented functionality, live providers,
   real messages, destructive reset or browser-only fixture data.
6. If Stage 1 passes, prepare the complete Stage 2 visual review package only.
   Broad native redesign remains approval-gated after that package is presented.
