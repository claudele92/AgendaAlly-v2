# Customer location selector: diagnosis and bounded repair

Date: 2026-10-05. Normal owned Customer preview only. No email sent.

## Finding
Genuine responsive picker interaction defects, not missing geography data or an API/SMTP configuration failure. The mobile full-width field already worked; the visible arrow did not. The previous acceptance attempt encountered that broken arrow path.

## Data and request trace
1. `CountrySelect` opens the chooser in a fresh session with no saved country.
2. `CountrySelectForm` supplies a shared `AsyncSelect` with `queryKey=v1/rest/countries` and `active=1`. The query is deliberately interaction-driven, not an automatic catalogue load on page navigation.
3. `AsyncSelect` uses `useInfiniteQuery`, `buildUrlQueryParams` and the native fetcher. Its query activates on input focus, mobile drawer opening, or an explicit query enable.
4. In supported local development, browser requests use same-origin `/api/v1/…`. Next forwards only the native API prefix to the normal Laravel listener, which uses the owned normal SQLite database. No retargeting occurred.
5. Public REST country/city routes use their controllers, repositories, model filters, translations and paginated resources. Options are in `data`; labels are in `translation.title`, not top-level `title`. Pagination is flattened by `extractDataFromPagination`.
6. Countries need active rows and appropriate translations for the requested/default language. The selector does **not** require Product delivery pricing, a payment provider, Maps/GPS, or an existing country selection to list countries.
7. Selecting a temporary country enables the real city GET immediately, with `country_id` bound to that selection. City selection is optional; before country selection, the city query is explicitly disabled. Save is local store/cookie/cart handling plus navigation—not a backend address mutation.

## Read-only source evidence
Normal same-origin countries GET returned HTTP 200 with four active countries: Cameroon, Burkina Faso, Nigeria and Ghana. Cameroon cities GET returned HTTP 200 with three cities: Douala, Yaoundé and Bafoussam. No geography data/configuration changes or reseeding were needed.

## Pre-fix browser comparison
- 390px arrow: changed HeadlessUI `aria-expanded`, but did not change the mobile drawer state. The hidden input did not gain focus. Query stayed disabled; no country GET and no visible options.
- 390px full-width field: opened the drawer and completed a genuine country GET with four visible options.
- 900px arrow: focused the visible desktop input, completed the country GET and showed four options.
- Exactly 768px: CSS showed desktop controls, but the component's inclusive `max-width:768px` media query selected mobile drawer visibility. The GET succeeded, but options were hidden.

This explains the earlier absence of country requests. No city request was expected while no country was selected. The earlier `127.0.0.1` test context also had a Next development-origin warning, but the defect reproduced on configured `localhost`; that warning was not the country-data root cause. Allowed origins/CORS/configuration were not widened.

## Only bounded application change
The shared selector's mobile arrow now toggles the same drawer as its mobile field. Its media query ends at 767px, matching CSS desktop controls starting at 768px. Existing query guards, API parameters, data, location persistence, cart behavior and account/email code remain unchanged.

Changed source: `.migration-backup/web/components/async-select/async-select.tsx`.
Focused regression file: `.migration-backup/web/components/country-select/city-query.test.cjs`.

## Focused verification
Six Node query/control regressions passed; changed TSX syntax parsed successfully. One Customer workflow restart followed the code batch.

Fresh guarded browser checks passed:
- 390px country **arrow only**: genuine GET 200; four visible options.
- Cameroon selection: city GET 200 scoped to country 1; three cities, including Douala.
- City arrow/selection and Save in a new empty-cart context: cookies/local storage persisted country 1/city 1, chooser closed, and `/login` exposed Forgot password. No issuance/password action was taken.
- Exactly 768px country arrow: genuine GET 200 and four visible options.
- Zero backend mutation requests passed; zero console/page errors in these supported-origin contexts. The already-passing 900px case and completed email suites/fixtures were not repeated.

## Preservation and C1/O4 stop
All **207 current table snapshots** and schema fingerprint match the pre-diagnosis baseline. Outbox/jobs/failed jobs remain **0/0/0**. SMTP credential was not selected; no credential operation, financial/booking/Wallet/provider change, broad worker/scheduler, migration or deployment occurred.

The UI blocker is resolved. Real acceptance is still stopped because no owner-controlled recipient or explicitly approved safe disposable account has been supplied. Existing selected-only processing remains default-off; no approval metadata or actual processing was activated. Recheck queue isolation/pending state before any future real attempt.

**C1 = 0.5; O4 = 0.5; 16/20 = 80%.** This repair is not real account-email certification evidence.

## Evidence
- `.local/staging-mvp/location-selector-diagnosis.json`
- `.local/staging-mvp/location-selector-fixed-browser.json`
- `.local/staging-mvp/location-selector-focused-tests.tap`
- `.local/staging-mvp/location-selector-before.json`, `location-selector-after.json`, `location-selector-preservation.json`
