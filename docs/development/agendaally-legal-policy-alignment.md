# AgendaAlly legal/policy alignment report

**Date:** 6 October 2026  
**Scope:** Policy content, existing legal presentation, and restoration of the existing Customer preview only.  
**Status:** Bounded implementation completed. These remain drafts for owner and qualified legal review, not legal certification or production approval. **Canonical score remains 16/20; MVP CONDITIONAL GO and production NO-GO are unchanged.**

## 1. Existing implementation discovered

- Terms and Privacy use native singleton models/tables (`term_conditions`, `term_condition_translations`, `privacy_policies`, `privacy_policy_translations`), their Admin editors, public `/api/v1/rest/term` and `/api/v1/rest/policy` APIs, and Customer `/terms` and `/privacy` pages.
- `DatabaseSeeder` calls `ContentPagesSeeder`. Its older placeholders differed from the richer `DevelopmentPreviewContentSeeder` drafts actually served by the normal preview. The existing English Terms/Privacy matched those richer drafts exactly before editing.
- Existing About/Delivery content uses the Pages CMS (`pages`, `page_translations`), Admin Pages management, and typed public Pages routes. This architecture was reused for the distinct Refund policy.
- Signup already has a Terms checkbox/link. Policy translations are editable records, not an immutable policy-version/acceptance ledger. No new consent mechanism or versioning architecture was introduced.
- Footer legal links, signup, booking payment selection, booking-detail cancellation text, and the account manual-refund header were inspected. Existing booking-detail cancellation copy is generic translated text; it does not establish a fixed fee or deadline.

### Audit: statement → implementation → disposition

| Existing statement/source | Implemented behavior/evidence | Disposition |
| --- | --- | --- |
| Marketplace connects Customers and independent businesses; credential security and lawful conduct requirements | Native Customer/Vendor/Specialist roles and existing account/listing workflows | Keep useful role/account/conduct language; clarify Shop-funded Specialist compensation |
| Richer draft describes product orders as supported commerce features | Accepted baseline is service-booking-first; product purchasing/additional payment integrations are deferred | Clarify current scope; do not activate or promise deferred capabilities |
| Older placeholder says cancellation deadlines/fees are disclosed before confirmation | Native cancellation settlement and manual eligibility consult configured platform settings; generic booking text is not complete individualized disclosure | Replace unsupported universal assurance with dynamic-rule description; leave final disclosure/notice decisions for owner/legal review |
| Richer draft defers detailed cancellation/refund policy | Implemented cancellation, verified collection, refund eligibility, request/review/execution/completion are distinct | Expand Terms boundaries and reference the separate Refund policy |
| Older Privacy placeholder promises marketing opt-out, “we do not sell,” permission-based location, and universal profile deletion | Those blanket statements were not established by the inspected implementation | Exclude unsupported promises from authoritative fresh defaults; retain conservative choices/location/retention language |
| Richer Privacy draft leaves technical collection undescribed | Native cookies/browser storage, verification/recovery, security/audit and financial evidence workflows exist | Add supported categories/purposes/access descriptions without secrets or internal security details |
| No separate Refund document in policy seeds or navigation | Existing Pages architecture supports a distinct typed document | Add first-class seeded legal Page and links |

Source checks included native booking cancellation settlement, manual-finance eligibility/status handling, policy/CMS requests/resources/repositories, and Customer presentation. No business or legal rule was selected to resolve an ambiguity.

## 2. Terms content preserved

The original 17-section development draft remains the base, not generic replacement boilerplate. Useful account eligibility/security, listing responsibilities, independent-business role, service performance, acceptable use, reviews/content, intellectual property, availability/account action, consumer-rights safeguards, liability-review boundaries, and governing/contact-review sections were retained.

## 3. Terms improvements

Added/clarified Customer–Vendor/Shop–Specialist relationships; service-booking-first scope; payment selection versus collection; Cash selection not proving receipt; cancellation versus refund; request/approval versus money movement; unresolved review versus completion; controlled manual execution; Vendor-only eligible payout scope; Shop-funded/controlled Specialist compensation; supported internal Wallet semantics without cash redemption; and no custody of uncollected Vendor-direct funds.

No automatic provider refund/payout, arbitrary partial-refund capability, new liability allocation, financial entitlement or completion deadline was created. Detailed refund rules are linked rather than duplicated wholesale.

## 4. Privacy content preserved

Retained the original ten-section draft structure and useful account/business/booking categories, marketplace-location explanation, necessary participant-sharing concept, conditional payment-provider treatment, cautious retention/security/request language, and unresolved minors/markets/hosting/contact disclosures.

## 5. Privacy improvements

Added supported authentication/recovery, Wallet/transaction/refund/Vendor payout records, receipt/evidence and associated metadata, authorized access and participant projections, selected account-email information, delivery status versus actual delivery, cookies/browser storage, technical/security/audit records, and conservative retention/deletion concepts.

No exact retention period, blanket deletion or encryption guarantee, data-sale assertion, compliance/security certification, unverified subprocessor, residency/transfer mechanism, breach-notification deadline or regulatory status was asserted.

## 6. Refund & Cancellation Policy

Eight Customer-readable sections cover cancellation, configured timing/fees, eligible amount, request/review/approval, controlled execution/completion, unresolved review, Cash/Wallet boundaries, and supported collection/custody scope.

The policy describes original verified collection, applicable configuration, earlier returns and held requests as amount inputs. The authoritative eligible request amount governs; duplicate/replayed requests do not increase entitlement. Cash proof and Wallet-to-cash redemption are not offered. It does not impose fixture percentages, fixed windows/fees, completion timelines, new penalties/exceptions or provider promises.

## 7. Routes, pages, links and Admin presentation

- Existing Customer `/terms` and `/privacy` remain; new `/refund-cancellation` renders a distinct document.
- Public `/api/v1/rest/pages/refund_cancellation` uses the native Pages controller/resource/repository and extended Page type allowlist. No separate financial endpoint was added.
- Added Refund links to both footer legal locations and cross-policy navigation. Signup retains its existing Terms checkbox; Privacy/Refund links are informational and outside that checkbox.
- Shared informational links appear at service-booking payment selection, booking-detail cancellation text and the account manual-refund header. No handler, request payload, financial state or acceptance requirement changed.
- Admin Terms/Privacy editors remain. Refund is available through existing Admin Pages with its named type. Only that text-only legal Page is exempted from the existing decorative-image requirement; other CMS image validation remains unchanged.

## 8. Seeds and defaults

Both native seed entry points now use `LegalPoliciesSeeder` and `Support/LegalPolicyContent`. Original drafts/placeholders remain as explicitly historical inputs for preservation and exact known-default recognition, not competing active defaults.

Missing documents/translations are created. Only exact known seeded English text with recognized default titles is upgraded. Custom/Admin-edited text or titles and other languages are preserved. Reseeding unchanged defaults is a no-op.

`runLegalPoliciesOnly()` and the explicit CLI updater reuse the existing owned-development-database guard. The normal preview was updated through this legal-only path, not a broad demo/financial seed.

## 9. Fresh-install verification

An empty disposable in-memory database using native policy/content schemas received improved Terms, Privacy, Refund and existing About defaults through `ContentPagesSeeder`. Verification covered exact defaults, repeat-seed immutability, known legacy upgrades, custom text/title preservation, other-language preservation, missing translation creation and text-only legal CMS validation.

This was a **fresh policy/content installation slice**, not a full `migrate:fresh`/application reseed. The full native `DatabaseSeeder` remains wired to the same content entry point. No normal business data was destroyed to test installation.

## 10. Unsupported old claims corrected

Authoritative defaults no longer imply live deferred product purchasing, universal pre-confirmation cancellation disclosure, broad marketing opt-out, verified data-sale policy or unrestricted account/related-record deletion. Those claims occurred in older seed placeholders; the normal preview already used the more cautious draft. General provisions were not discarded merely for consistency.

## 11. Decisions intentionally unresolved

Owner and qualified counsel must establish the operating entity, notice/privacy/support contacts, jurisdictions and governing/dispute terms, consumer protections, eligibility/minors, legal payment/marketplace roles and any liability allocation. They must approve actual cancellation-setting disclosures and how changes apply to transactions; no new cutoff, percentage, fee or notice rule was chosen.

Production processor inventory, cookie/analytics choices, hosting/transfers, retention/deletion exceptions and rights-request processes remain to be verified and approved. No fictional contact information was filled in. Owner-reviewed translations for launch markets and stronger explicit policy acceptance/version records should be considered separately; either requires owner/legal approval before implementation.

## 12. Exact files, schema and data changed

All source paths below are workspace-relative:

- `.migration-backup/backend/app/Models/Page.php`
- `.migration-backup/backend/app/Http/Requests/Page/StoreRequest.php`
- `.migration-backup/backend/database/seeders/ContentPagesSeeder.php`
- `.migration-backup/backend/database/seeders/DevelopmentPreviewContentSeeder.php`
- `.migration-backup/backend/database/seeders/LegalPoliciesSeeder.php` (new)
- `.migration-backup/backend/database/seeders/Support/LegalPolicyContent.php` (new)
- `.migration-backup/admin/src/views/pages/type-list.js`
- `.migration-backup/web/services/info.ts`
- `.migration-backup/web/components/footer/footer.tsx`
- `.migration-backup/web/components/legal-policy-links.tsx` (new)
- `.migration-backup/web/components/auth/sign-up/components/sign-up-form/sign-up-form.tsx`
- `.migration-backup/web/app/(store)/(booking)/(with-footer)/(simple)/terms/content.tsx`
- `.migration-backup/web/app/(store)/(booking)/(with-footer)/(simple)/privacy/content.tsx`
- `.migration-backup/web/app/(store)/(booking)/(with-footer)/(simple)/refund-cancellation/page.tsx` (new)
- `.migration-backup/web/app/(store)/(booking)/(with-footer)/(simple)/refund-cancellation/content.tsx` (new)
- `.migration-backup/web/app/(store)/(booking)/(witout-footer)/(navigation)/shops/[id]/booking/payment/components/payment-list/payment-list.tsx`
- `.migration-backup/web/app/(store)/(booking)/components/booking-detail/booking-detail.tsx`
- `.migration-backup/web/app/(store)/(settings)/manual-refunds/page.tsx`
- `scripts/development/update-legal-policies.php` (new, guarded local CLI)
- `scripts/development/verify-legal-policies.php` (new, disposable verification)

Reporting/evidence: this report, `reports/agendaally-legal-policy-alignment.html`, files under `reports/agendaally-legal-policy-evidence/`, and local preservation checkpoints. A durable shared-preview-lock warning was added to the existing project memory topic.

**Schema:** unchanged; no migration/new table.  
**Normal data:** English Terms title/description and Privacy description updated; one new `pages` row (ID 4, type `refund_cancellation`) and its English `page_translations` row. Existing CMS rows/translations and all other fields preserved. No normal financial record changed.

## 13. Validation results

- All three native public policy APIs returned HTTP 200 and the intended content.
- All three normal Customer pages returned HTTP 200 with rendered document HTML and legal links. Next development compilation succeeded for the affected policy routes.
- Customer TypeScript `--noEmit --incremental false` passed with no diagnostics. Backend changed-file PHP syntax checks and Admin type-list JavaScript syntax validation passed.
- One bounded anonymous browser check, with a continuation to clear the existing discovery overlay using an empty disposable context, verified desktop/390px reading, physical cross-policy/footer navigation and signup Privacy/Refund navigation. Terms checkbox stayed unchecked, email empty and signup disabled; no submission occurred.
- Authenticated booking payment/cancellation/manual-refund placements were **source/static verified only**. No authenticated business journey or Admin edit/save campaign was run.
- Existing direct-browser HMR WebSocket handshake warnings and a generic 404 resource were observed; policy content loaded and navigation worked. These were not treated as policy failures or repaired outside scope.
- No full production build or accepted financial/receipt/email/calendar campaign was rerun for this task. Test/check results do not establish legal correctness.

## 14. Preview restoration and preservation

**Cause:** The normal Customer workflow had failed on the shared Next development lock; no normal listener was available on port 3002. `selected-customer-acceptance` was using the same source/cache on port 3000.

**Restoration:** Stop that conflicting acceptance storefront and restart the existing `original-customer-preview`. Retain its intended port 3002 and normal backend provenance. No restoration source, environment, dependency, port or configuration file change was required. Normal Customer/Laravel/Admin workflows were restarted once after the implementation batch; Customer startup reported ready and the policy routes served successfully.

The native “Choose your country and city” dialog initially blocked legal links. Selecting Cameroon/Douala in a clean signed-out context with no cart closed it and allowed the requested checks. The discovery/cart behavior was not modified.

Final row-ordered, field-level fingerprints show unchanged schema and **210 unchanged tables out of 214**. The only changed tables are the four legal-content tables described above. Additional narrowed checks establish that all pre-existing Pages/translations are identical and Terms/Privacy changes contain only the intended title/description delta.

## 15. Explicit prohibited-scope confirmation

No booking, availability, payment, cancellation/refund calculation or lifecycle, financial state machine, payout, Wallet/accounting, provider, permission/authorization, private receipt/evidence semantics, SMTP/email or notification business behavior changed. No real money movement, registration/login submission, financial communications, provider activation, production configuration/deployment, mobile audit or unrelated gate repair was performed. Prior accepted evidence and the canonical 16/20 audit were not revised.

## 16. Professional review and completion boundary

These are product-aligned drafts, not operative legal certification. Obtain qualified legal review and owner approval of the unresolved disclosures/rules before production publication. Any future consent/version tracking or behavior change requires separate approval; this report does not authorize it.

The bounded task ends here. No further implementation or acceptance campaign is started.

### Evidence index

Persisted evidence is under `reports/agendaally-legal-policy-evidence/`: before/after public policy JSON, fresh-seed result, static-check summary, normal preview startup logs, exact preservation result, and browser `result.json`/`result.txt`.

Browser screenshot observations include desktop Refund `v45o2w`, Terms `rlhryr`, Privacy `35m62m`; 390px Refund `hwcsu6`, Terms `cz6iku`, Privacy `rqn9ki`; mobile footer `mrsa5m` and signup `bqqf4f`. IDs/descriptions are recorded in the browser result. Screenshot image files could not be exported by that browser session; this limitation is not represented as persisted image evidence.
