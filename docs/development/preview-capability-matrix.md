# AgendaAlly development-preview completeness

This audit concerns the original Laravel, Next.js, React/Ant Design and Flutter
application—not the separate illustrative Canvas. Only existing capabilities
may receive representative development content. The pre-enrichment read-only
snapshot had no legal documents, About pages, blogs or FAQs.

| Capability | Exists in original source | Current preview state at audit | Existing source/model | Development representation needed |
| --- | --- | --- | --- | --- |
| Cash / offline collection | Yes, booking and product-order paths | Cash catalog entry; synthetic historical records are not proof of collection | `Payment`, `Transaction`, booking/order services; `DevelopmentPaymentCatalogSeeder` | Preserve actual cash availability and pending/offline semantics; no new financial activity |
| External payment methods | Yes: Stripe, PayPal, Flutterwave, Paystack, MTN, Orange and other original catalog tags | Omitted from local catalog; operations development-disabled or fail closed | Original `PaymentSeeder`, provider services, `EnvironmentPolicy`, hardened controllers | Seed inactive catalog identities only; original management UI can show inactive states; do not expose selectable gateways or credentials |
| Wallet / balance | Yes, internal balance/history and checkout integration | Existing synthetic balance/history; no external funding proof | `Wallet`, wallet history, web `PaymentList`/`Wallet`, original Flutter clients | Keep balance distinct from gateways; wallet catalog entry remains inactive, not newly enabled checkout or top-up |
| Standalone bank transfer | No independent rail found | Not available | Maksekeskus provider banklinks are not an offline bank-transfer method | NOT APPLICABLE; do not invent a bank payment feature |
| Platform/vendor collection, fees | Yes, separate merchant routing and fee ledger | Existing synthetic bookkeeping only | `collect_via_platform`, shop/platform configs, `PlatformFeeLedgerEntry` | Document distinctions; no simulated settlement or new paid fee entries |
| Refund / settlement / payout | Separate supported workflows; some providers lack safe verification | Not claimed operational; existing payout fixture is pending | Refund services, provider reconciliation, `Payout` | Preserve hardening; no fake successful refund, callback, settlement or payout |
| Terms / Privacy | Yes, translated HTML and native web/mobile pages | No development documents | `TermCondition`, `PrivacyPolicy`, translations; REST term/policy resources; admin editors | Clearly labeled non-operative development examples requiring owner/legal review |
| About | Yes, existing multi-section static-page renderer | No development pages | `Page` / translations, `all_about`; native `/about` | Representative Africa-first, broader service-and-product marketplace copy using actual page types |
| FAQ / help | Yes, translated questions and answers | Empty FAQ list | `FAQ` / translations; native FAQ and public REST listing | Demo answers about geography, services/products, disabled providers, cancellations and support limits |
| Cancellation / refund information | Can be expressed in existing legal and FAQ surfaces | No populated content | Existing legal/FAQ content; actual booking/order/refund workflows | Explain that business-specific terms and actual server rules govern; do not invent guarantees, deadlines or automatic refunds |
| Blog / content | Yes, listing, cards, image, localized detail, author and publish date | No blogs in owned snapshot | `Blog` / translations, paginate/detail resources; native `/blogs`, admin CRUD | Several professional relevant articles with images and existing author/date fields; no new CMS or invented taxonomy |
| Global social links | Yes, settings-driven footer links | No safe representative configuration | Existing social settings keys, native footer | Reserved example destinations explicitly labeled development examples—not official AgendaAlly profiles |
| Per-business social links | Yes, a distinct model | No rows in snapshot | `ShopSocial`, public shop-social resources, native business profiles | Leave existing marketplace records untouched; global demonstration does not require creating shop socials |
| Footer / marketplace navigation | Yes, shared native footer | Incomplete settings; existing blog route not discoverable there | `Setting`, original footer component | Populate supported description/footer text, expose existing content links, retain stable disclosure elements |
| Support / contact | Yes, settings-driven native contact page | No verified official destination | Existing address/phone/email settings where consumed | Clearly describe demo support limitations; reserved examples only, no claimed office, operational phone line or real messages |
| App/download information | Yes, native web/business badges and Flutter apps | No verified official store listings | Existing customer Android/iOS settings and native download sections | Keep store links unset; communicate preview/unavailable state without fake badges or listings |
| Other existing configuration | Localization, geography, currencies, roles, branches, service/product catalogs already represented | Valid accepted evidence remains intact | Guarded development bootstrap/seed and original models/clients | Preserve existing records and evidence; do not replay unrelated acceptance or enable external integrations |

## Reproducibility and boundaries

- General owned development bootstrap/seeding must reproduce the content and
  inactive catalog entries, including repeat and partial-dataset seeding.
- The content-only seed path uses the same ownership/path/manifest guards and
  exclusive lock, and does not replay business, inventory, cart, booking, wallet
  or order fixture generation.
- Existing owner-authored content must not be overwritten merely to make the
  preview look complete. Demo legal is not approved policy.
- No production access, credentials, provider payloads, live charges/messages,
  destructive reset, `migrate:fresh`, browser-only records or missing-image
  fixture manufacture.
- See [`payment-capability-matrix.md`](payment-capability-matrix.md) for payment
  scope, collection and unavailable-provider details. Applied seed and rendering
  results belong in the final Stage 1 acceptance record.

## Applied development representation — 2026-10-01

The guarded root command `node scripts/development.mjs seed --content-only`
was applied twice successfully. The resulting original-model content comprises
three articles and translations, three About sections and translations, four
FAQs and translations, and one English Terms and Privacy document each.
Development social examples and footer copy are configured; unknown official
contact and app-store destinations remain unset and the native UI explains
their unavailable state. Native blog discovery was added to the footer.

The payment catalog now contains the original 18 method identities: cash is
the only active entry; the other 17, including wallet, are inactive. Provider
credentials, merchant configuration and financial activity were not added.

Read-only before/after row snapshots confirmed no changes to shops, branches,
products, stock, users, wallets, bookings, carts and child rows, orders and
details, transactions, wallet histories, payouts or platform-fee ledger rows.
No missing-image fixture was created.

Focused isolated verification:

- Content-only, full-demo fresh/repeat integration, owner-content preservation
  and failed-seed rollback: **4 tests / 722 assertions passed**.
- Original payment catalog completeness/idempotence and real active REST
  selection: **1 test / 67 assertions passed**.
- Native footer/content regressions: **6 tests passed**; web TypeScript passed.
- Development configuration/transport regressions: **11 tests passed**.
- Current native customer offline production compilation passed. The unchanged
  business client's previous passing compilation remains valid.
- Six referenced content-image URLs returned **200 / image/jpeg**; that HTTP
  evidence does not by itself establish browser rendering.

The isolated compiler snapshot was updated to preserve the original relative
shared development-helper path without copying dotenv files or inherited
secrets. Customer and temporarily paused Canvas previews were restored after
the compilation. Final affected-page/browser results are recorded separately
in the Stage 1 acceptance document.