# Connected original previews: selected flows verified

Date: 1 October 2026.

The owned Laravel HTTP environment is provisioned from reviewed original
schema requirements, not from production data or a historical migration replay.
Original application code, framework configurations, assets and locks remain
unchanged. See `agendaally-original-http-preview-backend.md` for backend checks.

## Separate original roots

The restored customer Next.js 16 / React 19 server uses port 3002, and the
original admin React 18 / Ant Design 4 Vite server uses port 3003. Replit extra
external ports give them distinct HTTPS origins at the same development hostname
with those port numbers. Both origin-root pages returned HTTP 200 during checks.
They are not a merged application or a subpath conversion.

`scripts/serve-original-preview-client.sh` clears inherited credentials and
uses the original API environment variables to target this workspace's isolated
Laravel service on port 8000, preserving `/api/v1` contracts. Its Node preload
blocks third-party application requests without fabricating responses. Firebase
initialization values are explicitly synthetic and cannot support Firebase
authentication, chat or push. This Node restriction is not a browser firewall.

HTTP 200 from a frontend is **not** proof of a completed user journey. The
storefront initially logged missing original-schema dependencies for geography
and homepage discovery. The scoped definitions were subsequently provisioned,
and the country/city detail API checks pass. No original queries or frontend
loading behavior were rewritten.

The complete HTTP verifier now passes: real admin password authentication,
token-protected non-empty product and booking lists, all public catalog/root
data endpoints, CORS allow/reject checks, and failed-password/missing-token/
invalid-token rejection. Original lock comparisons pass. The API is kept
running by `original-laravel-preview`, not a temporary shell.

## Approved preview-only sign-in exception

The original admin login always renders Google reCAPTCHA and disables its submit
button until the challenge returns a value:

- `.migration-backup/admin/src/views/login/index.jsx`
- `.migration-backup/admin/src/components/recaptcha.jsx`

The creator subsequently explicitly approved a preview-only reCAPTCHA bypass.
`scripts/original-admin-preview.vite.mjs` retains the original Vite configuration
and adds `scripts/original-admin-local-login-plugin.mjs`. This development
overlay transforms only the exact original login module in memory: it removes
the challenge component/import, enables the form submit button, and displays
a clear local-preview notice. It neither supplies a challenge token nor
injects an authenticated session. Password login and token authorization still
use the real original Laravel API.

The overlay refuses production/build mode, any unapproved runtime, a changed
login source, and any API origin other than the isolated development API. It
requires the owned backend marker and explicit clean-launcher approval.
The original admin source, runtime source files, locks and production build
configuration remain unchanged. Unit checks cover the accepted transform,
source preservation and rejection of production, unapproved or wrong-origin
use.

## Browser check: additional installer compatibility gate

The single browser pass confirmed that the preview disclosure is rendered,
but `/login` redirects to `/welcome` before credentials can be entered.
The original `PathLogout` calls `/api/v1/install/init/check` and redirects on
any error. The original Laravel `InstallController` deliberately disables
installer operations; the original routes also omit the installer check.
The live kernel returns HTTP 404 with `status: false`, `statusCode: ERROR_404`
and `"Item's not found."` for that absent route. This is not a missing
fixture or initialization setting: neither a database seed nor reCAPTCHA
configuration can make the original disabled endpoint return success.

The creator separately approved the preview-only redirect exception. The same
development overlay now transforms the exact original `PathLogout` module to
skip its redirect only for the installer-check request's HTTP 404 with the
original `ERROR_404` failure envelope (or the controller's disabled message).
Network failures and every other error retain the original redirect. A distinct
launcher approval flag is required; the login notice discloses both exceptions.
Unit checks cover these error cases and production/unapproved rejection.
Installer endpoints remain disabled and return their original response; no
successful installer result is mocked and no migrations are replayed.
The targeted browser confirmation passed real admin form sign-in, invalid
password rejection, reload persistence at `/dashboard`, the published
`Preview cocoa` row at `/catalog/products`, and the upcoming `Relaxing massage`
booking at `/booking` (5,000 XAF). Bearer-authenticated list requests returned
HTTP 200; the booking payload matched the visible row. Credentials/tokens were
not included in reports or screenshots.

The customer browser showed Cameroon/XAF geography and the synthetic Douala
studio. Initial product/service discovery was blocked by missing banner/like
schema dependencies. Reviewed original definitions were added to the synthetic
database, with no banner rows or provider configuration. The expanded HTTP
verifier now passes the exact geographic product/service queries and a valid
empty banner collection. The browser then confirmed the customer `/products`
listing: one `Preview cocoa` product at 2,525 XAF (the original resource's
calculated price), English labels, and no captured local HTTP errors.

`/search/service` is the original category picker, not an offer-results page.
Its page passes `onSelect={() => router.back()}` to `ServiceSelect`; saving
Wellness after opening it from Products correctly returns to Products.
The browser confirmed the Wellness category and translated picker labels.
Public service records were verified through the real API; no unvisited
offer/results page or booking-creation journey is claimed as browser-tested.
The synthetic UI setting now uses the original numeric View 1 (`ui_type=1`),
and the unused invented `services_enabled` fixture was removed. Original
navigation and search behavior were not rewritten.

## Original localization restored

The original preferred PHP catalog and pre-existing supplemental seeder
constants are imported without running framework seeders or their deletion
logic. The owned database has 3,416 original identities with no missing or
changed source values; the English API returns 2,659 keys. Both clients'
existing Laravel dictionary fetches were traced, and translated labels
were observed in both previews.

The original admin stores some translated table headings in component state
before its asynchronous App dictionary fetch can finish. A guarded,
development-only Vite transform preloads the **same real API dictionary** into
the original empty English i18next resource, before those components mount.
It leaves the original App fetch, language modal, saved-locale selection,
fallback configuration, source files and production build unchanged.
The preload fails explicitly if the API is unavailable or its dictionary is
invalid; it neither generates strings nor invents successful API responses.
API-backed tests and the served Vite module confirm the original heading
values are present; the earlier browser pass is not represented as a
post-preload table-heading test.

See `agendaally-preview-localization.md` for provenance, original locale
configuration, exact remaining English key gaps (28 customer, 10 admin),
non-English coverage counts, and a verifier that emits every missing identity.
Only English is originally configured. Nine other source catalogs are
incomplete and remain disabled. The original English `dashboard` value is
`Tableau de bord`; it was preserved, not corrected or replaced.

To use the local identity, inspect the private ignored runtime file
`.local/agendaally-preview/backend/.preview-credentials` in the workspace.
Do not publish its contents. No passwords or bearer tokens are shown in
documentation or logs by the verification scripts.

## File-serving boundary and credential rotation

Completion review found that Vite's inferred workspace filesystem scope
included the sibling backend directory. HTTP HEAD checks returned 200 for
the synthetic credential and application-key paths; no values were read by
the reviewer. Filesystem mode 0600 alone did not prevent Vite, running as
the owner, from serving those files. The admin workflow was stopped
immediately while the boundary was repaired.

The development wrapper now allows filesystem serving only within the
restored admin client and its internal dependencies. Explicit deny rules
cover backend/private files, database files and agent metadata. An early
request gate also rejects outside-client `/@fs` URLs, including encoded
traversal and nonexistent files, before Vite's SPA fallback can return 200.
Original production configuration is unchanged.

All three synthetic passwords and the preview application key were rotated,
and all four existing personal access tokens were invalidated. The backend
was restarted with the new key. New real password authentication and protected
lists pass; old browser sessions must sign in again.

`scripts/verify-original-preview-file-isolation.sh` passed 104 protected-path
checks (HEAD/GET, raw imports, single/double slashes and encoded traversal),
plus six original source-module checks. Both private-file HEAD requests through
the public admin origin now return HTTP 403. Overlay, translation preload and
filesystem-gate tests pass. The rotation script logs only action counts, not
secret values.

## Other limits

- Live payments, refunds, payouts, messaging and production credentials/data
  are not used.
- Original Next image host restrictions remain unchanged. Synthetic nullable
  media are not replaced with invented assets.
- Firebase, maps, reCAPTCHA and payment/provider features are not claimed to
  work. No fake success is returned.
- The storefront references `/img/cartempty.png`, which returned 404 during
  checks; no replacement asset or original-code edit was made.
- Development port exposure is not a deployment.
- The admin dashboard displayed an internal-server-error alert on reload.
  This did not block sign-in or the verified lists; aggregate/dashboard
  endpoints beyond the selected route subset have not been provisioned or
  claimed functional.