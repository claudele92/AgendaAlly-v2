# First-install reference and portable-demo proposal — owner review

**DRAFT / REVIEW ONLY. Nothing has been seeded, migrated, provisioned, downloaded,
sent or activated. No application source, database, account or key was changed.
Production remains NO-GO.**

The owner requested preparation, not execution, and corrected the initial proposal:
**“Do not finalize the manifest with empty geography solely to avoid the
country-triggered permission/role behavior.”** This revision restores the intended
source geography and separates its portable content from unsafe runtime effects.
It does **not** claim that the proposed changes below have already been approved.

## Review package and authority

- [Migration-phase catalogs and administrator/key proposals](local-synthetic-reference-manifest.json)
- [Post-ledger portable reference/demo rows, relationships, counts, source hashes
  and assets](local-synthetic-portable-baseline.json)
- [Original native bootstrap/recovery specification](mysql-bootstrap-recovery-specification.md)
- [Source-derived photograph inventory and unresolved approval decisions](local-synthetic-demo-asset-review.md)

The two JSON files form one draft proposal. The first file's empty geography/grant
projections describe migration checkpoints, **not the final baseline**. After the
unchanged ledger, the second file replaces overlapping projections and adds its
explicit rows. No migration/legacy/development seeder is permission to seed the
entire normal demo pipeline.

The task's original “no demo businesses” exclusion is **not silently relaxed for
execution**: the owner's correction requests a portable-demo proposal for review.
Actual demo inclusion, initialization, administrator creation and secure custody
each still need owner authority identifying the reviewed files and their hashes.

## Classified baseline

“APPROVED PORTABLE DEMO” below is the requested **content category**; its entries
are proposed for approval, not evidence of approval.

| Classification | Proposed content | Exact draft counts |
|---|---|---|
| REQUIRED REFERENCE | Fixed role identities, English; native shop/country/Spatie Finance permission **definitions** | 8 roles, 1 language, 28 shop, 23 base + 12 native Finance country definitions, 12 Spatie Finance definitions |
| REQUIRED REFERENCE | Africa; Cameroon, Burkina Faso, Nigeria, Ghana; their currencies/cities | 1 region, 4 countries, 7 cities, 8 currencies |
| REQUIRED REFERENCE | Service and retail category trees, native unit definitions and English text | 84 categories + 84 translations; 33 units + 33 translations; 2,689 unique English translation keys |
| REQUIRED REFERENCE | Source general/default-location/branding settings | 28 natural-key settings; blank contact/social/app-store links |
| APPROVED PORTABLE DEMO | Nine intended Shops and non-login synthetic profiles | 9 Shops; 9 sellers, 14 Specialists, 6 staff = 29 profiles and 29 explicit structural-role links |
| APPROVED PORTABLE DEMO | Explicit branches/memberships, working hours and services | 26 ShopLocation rows; 20 accepted Invitations; 42 invitation/location pivots; 63 Shop and 98 Specialist working-day rows; 51 Services + translations; 75 ServiceMaster assignments |
| APPROVED PORTABLE DEMO | Country/Shop role labels without automatic financial authority | 12 country roles with **zero** permission pivots/members; 9 Shop roles with 62 explicitly enumerated non-financial pivots |
| APPROVED PORTABLE DEMO | Product catalog, stock/variant examples and unassigned plans | 13 Products + translations; 14 Stocks; 1 brand; 1 Volume extra group/value/stock link; 4 plan definitions, **zero assignments/payments** |
| APPROVED PORTABLE DEMO | Local delivery/pickup reference examples | 1 Douala area; 11 delivery-price definitions; 1 free pickup + translation; 7 pickup working-day rows |
| APPROVED PORTABLE DEMO | Public source content and legal review drafts | 3 About pages + 1 Refund/Cancellation page; 4 FAQs; 3 blog drafts; 1 Terms and 1 Privacy document; matching English translations |
| APPROVED PORTABLE DEMO | Curated service media | 15 selected image files for 51 Services; 51 Service + 13 Product Gallery relationships |
| EXCLUDED RUNTIME/PRIVATE | Money, credential, account-activity and live-provider data | Zero Wallets/history, Orders, Bookings, Transactions, payment attempts, country gateways, merchant configs, SMTP providers/templates, country memberships and Spatie Finance grants |

There are 15 Service roots/47 leaves and 2 retail roots/7 intermediate groups/13
leaves. The Service tree includes Car Service (taxonomy only, **no car businesses
or Services**). Generic CategorySeeder's placeholder type-label rows and the
non-idempotent “Halal” ShopTag insertion are not the intended category baseline.

The exact titles, IDs, descriptions, amounts, parent keys, foreign keys, scopes,
working days and explicit counts are in the row file, not inferred from this table.
Unspecified native columns retain the frozen schema's NULL/defaults and must be
included in eventual **full-row native receipts**; these projections alone are not
native proof.

### Intended geography and Shops

- Cameroon / XAF: Douala, Yaoundé, Bafoussam.
- Burkina Faso / XOF: Ouagadougou, Bobo-Dioulasso.
- Nigeria / NGN: Lagos.
- Ghana / GHS: Accra.
- Shops: Le Sawa Beauty Studio; Ouaga Éclat Beauté; Lagos Glow Studio;
  Accra Radiance Salon; Douala Chic Corner; Mfoundi Style House;
  Bafoussam Belle Époque; AgendaAlly Learning Hub; Wouri Ink & Piercing Studio.

Bobo-Dioulasso is intentionally a selectable city without a demo branch or
city-level delivery-price row, matching the source. Country-level fallback price
definitions remain. The Education/Tattoo branches preserve the source's explicit
Specialist assignment relationships. USD/EUR/CAD/GBP definitions reproduce the
development currency catalog, but are not extra countries or live exchange rates.

## Differences that require explicit review

1. **No login credentials in portable demo profiles.** Preserve their display and
   native role/Shop relationships, but replace source contacts with
   `demo-<id>@agendaally.invalid`, NULL phone/password/verification/tokens and
   deterministic non-secret UUID/referral values. No Wallet/points/audit side
   effects from importing demo users. These profiles remain active for catalog
   visibility, not proof of a tested login-disable/security contract.
2. **No automatic country authority.** Preserve 12 country role labels, but no
   automatic permission pivots or manager/accountant memberships. `Country
   Manager`'s expanding `all` sentinel is never executed. Main Accountant's global
   Finance-related role/membership is excluded.
3. **Narrow Shop role proposals.** Preserve Branch Manager, Moderator, Receptionist
   and Cashier labels. Six Branch Managers retain nine non-financial keys each,
   excluding `payments.view` and `payments.refunds.manage`. Receptionist/Cashier
   get four non-financial keys each; Moderator is unassigned with zero pivots,
   not `all`. These are reviewed differences, not claimed source-grant parity.
4. **Explicit Specialist branch scope.** Previously unscoped beauty Specialists
   get their intended branch-pair pivots; Armand gets both Cameroon branches.
   Education/Tattoo Specialists keep their source branch assignments.
5. **Physical Service type.** Propose all 51 as native `offline_in`, not the
   legacy accidental default. Online-capable Education is an owner decision.
6. **Synthetic pricing/defaults.** Preserve source Service/Product amounts,
   inventory starting quantities, delivery-price definitions and historical
   currency ratios; exclude sales decrements and every money transaction. Native
   storage precision still needs a future MySQL receipt. The source fee,
   cancellation/timing and active plan definitions require owner review; they do
   not establish production policy or enable paid subscriptions.
7. **Public drafts.** Freeze the source legal disclaimers and public text, with
   2026 explicitly proposed for the copyright year and 2026-10-06 for draft blog
   publication. No imported contact/legal authority. Blogs depend on separately
   approved synthetic administrator ID1; **never create an author to satisfy an FK**.
8. **Media differences remain visible.** Source Shop/Specialist/brand/CMS photos
   currently use external URLs. Proposed NULLs for those families are an explicit
   owner-review difference, **not photo-complete parity**. Five Product images
   require approved local bytes/licenses/hashes before their Gallery paths work.

### Asset portability is a blocker, not a fallback

All 15 Service-category icons are present in the sanitized customer source and
their bytes/hashes are frozen. The 15 selected Service photographs are present and
hash-checked in this workspace, with public source/license references. However,
`attached_assets/service-photos/` is **absent from the sanitized checkout**.
Source metadata includes 23 historical files; only 15 are selected by the 51
reviewed targets. Native Gallery relationships are explicitly remapped by reviewed
Service/Shop/title identity, not blindly by historical owner IDs.

Approve packaging and license/source inclusion before initialization. Copy only
approved bytes into a fresh public demo namespace, reject symlinks/missing hashes/
wrong MIME/occupied destinations, and verify every Gallery relationship. Do not
download media at bootstrap, fabricate byte hashes, borrow private uploads or
silently accept broken Product galleries/NULL source photo replacements.

## Proposed deterministic order — not executed

1. Freeze both documents, dependency source hashes and approved asset inventory.
   Confirm an independently owned, newly empty socket-only MySQL 8.0.42 lab and
   previously authorized native DDL/definer policy. No existing/partial schema
   repair, `migrate:fresh`, down-migrations or fabricated ledger entries.
2. Run the native migrator through the first 12 files. Verify ordered ledger and
   prefix hash. Insert roles with **user=1, shop_manager=14, admin=99**, English
   and XAF before any user/grant or manager-reassignment migration. Never run
   fixed-ID RoleSeeder after `shop_manager` has claimed ID1.
3. At ledger38, insert Cash ID1 active and Wallet ID2 inactive definitions. No
   Wallet account and no electronic provider definitions/credentials.
4. Keep geography empty **only during migrations**. At ledger186 verify the
   currency/provider backfill has no targets; at188 insert 28 Shop permission
   definitions before any Shop/manager holder; at192 insert 23 country
   definitions; at200 verify the default-country-role backfill has no targets.
5. Finish all229 unchanged migrations; verify their 12 Finance definitions in
   both catalogs and zero grants. Do not invoke CountryObserver/default-role
   backfill after countries or Finance definitions exist.
6. **Post-ledger**, insert exact reviewed currency/geography/translations and 12
   unassigned country role labels through controlled raw inserts. This is the
   explicit proposed side-effect suppression policy, not an ambient Eloquent run.
7. Insert approved categories/icons, non-login profiles/structural-role links,
   Shops/locations, exact Shop roles/pivots, Invitations/location pivots, working
   days, Services/ServiceMasters and approved Gallery assets. Then approved units,
   Product/stock/variant/plan definitions, delivery/pickup rows, settings,
   English translations and non-blog CMS drafts, in FK dependency order.
8. Verify exact full native rows, FKs, natural-key uniqueness, ledger/schema/
   trigger authority, zero money/provider/country/Finance activity and a
   **read-only identical second invocation**, including timestamps and
   auto-increment state. Fail nonzero on any mismatch/SQL error; never use the
   seeders' catch/log/continue behavior. Revoke elevated migration privileges.
9. Only after separate administrator/key approval and secure proof, provision
   the one initial administrator through the native User observer contract; allow
   inspected zero-value points/audit effects only. Insert the three authored blog
   drafts only after this independent account proof.

No installer/executor is supplied. The source compiler only produces review data.
Checkpoints, native Finance ordering, provider-required template behavior and
dedicated administrator/key proposals remain frozen in the first JSON.

## Read-only checks

```sh
node scripts/database/check-synthetic-reference-review.mjs --check
node scripts/database/check-synthetic-reference-review.mjs --self-test
```

These check source/ledger-prefix hashes, deterministic proposed rows/counts/
relationships, native required Brand UUID/title fields, deterministic demo UUIDs,
negative cases and existing workspace asset bytes. They do not
boot Laravel, execute PHP source, connect to any database, invoke seeders, generate
credentials/keys, read dotenv/secrets, download assets or write receipts.
The source generator emits JSON to stdout only; unsupported executable PHP is
rejected by its literal parser. Its output does not contain imported contacts or
passwords.

A pass is **draft integrity**, not native FKs/idempotency/security/custody proof,
portable media packaging or owner approval. Approval must name the exact documents
and hashes and selected differences. Editing a `status` field, a confirmation
switch or the presence of a manifest is not authority.

## Administrator, recovery and completion boundary

Administrator ID1 / role99 is separately proposed, with no country membership,
Shop membership or explicit Finance grant. The native Admin role remains broad
structural administrative authority; ManualFinance still requires actual grants.
Verify native bcrypt (10 rounds), right/wrong password behavior and Finance denial
only after independent authority. Never import demo credentials or normal keys.

Dedicated password/application/recovery secret names and custody conditions are
in JSON. Do not request values until approved. Password entry uses secure tooling;
independent random 32-byte application/recovery key generation and off-host escrow
need owner-approved tooling that exposes no values to chat/stdout/logs/code.
If it is unavailable, **STOP**. Never derive from `SESSION_SECRET` or staging keys,
rotate on retry, or use ordinary setters/chat as secret-generation substitutes.
Prove retained same-version decrypt and wrong-key rejection using synthetic text
without merchant revision/challenge creation. Real identities, policies,
production custody/defaults and release need separate owner authority.

The specification reports a prior DDL-ONLY PASS; its private approved-lab evidence
directory is currently absent. Source review cannot re-certify it. Native runs,
first-install idempotency, login/key custody, upgrades/restores and production
remain **UNEXECUTED / NOT ACCEPTED**. The deliverable is this review package only.
