# Native external preview connectivity

Stage 1 is **not accepted**. On 2026-10-01 the creator manually confirmed both
ordinary external previews from their own browser, including Admin initialization
and login/reload plus customer Cameroon → Douala/save/reload and seeded content.
Only previously unfinished acceptance checks may resume. Stage 2 remains blocked.

## Observed failure

The creator's Webview logs repeatedly showed Vite connected but
`Unable to initialize application translations: Network Error`.
The native configuration made Admin/browser API calls to the separate HTTPS
`:8000` origin; the customer used the same separate origin for geography.
Those console records do not contain the failed request's network status, so
the specific browser-edge rejection cannot be claimed as conclusively known.
They do establish that internal-browser successes were insufficient evidence
of the creator's experience.

The reconstructed Admin translation request was:

`https://<development-host>:3003`
→ `https://<development-host>:8000/api/v1/rest/translations/paginate?lang=en`.

An Authorization header triggers cross-origin preflight. Axios used its default
cross-origin credential behavior, not `withCredentials: true`.
Sample direct OPTIONS requests returned **204**, exact frontend
`Access-Control-Allow-Origin`, credentials allowed, requested GET/header
allowlisting and `Vary: Origin, Access-Control-Request-Method,
Access-Control-Request-Headers`. Successful container-side preflight did not
prove the creator's external browser could reach the separate API preview.

## Narrow development-only correction

- Browser API calls now stay on the current frontend's HTTPS origin.
- Native Vite and Next forward only `/api/v1/...` to Laravel internally.
- The server-only `AGENDAALLY_DEV_API_TARGET` defaults to
  `http://127.0.0.1:8000`; it is not a browser localhost URL.
- Customer SSR and middleware use that internal target in development.
- Next's real `/api/cache/settings` handler remains separate and unshadowed.
- Production retains its configured absolute API origin. Existing media URL
  configuration, REST paths, authorization headers, role grants, booking and
  commerce behavior are not replaced.
- Laravel's exact CORS origin allowlist was not broadened. The customer
  translation request's incorrect `Access-Control-Allow-Origin: *` header was
  removed. No browser response, geography or authentication was mocked.
- The portable changes are in the actual tracked `.migration-backup` sources,
  with the shared server helper under `scripts/development/`.

## Restored request trace

Host:
`b762a632-9df8-4939-8b17-506d75cfa277-00-2ngxdayklmnxt.worf.replit.dev`

| Frontend origin | Requested path on that same origin | Response |
| --- | --- | --- |
| HTTPS host:3003 | `/login` | 200 HTML |
| HTTPS host:3003 | `/api/v1/rest/translations/paginate?lang=en` | 200 JSON |
| HTTPS host:3003 | `/api/v1/rest/settings?lang=en` | 200 JSON |
| HTTPS host:3002 | `/login` | 200 HTML |
| HTTPS host:3002 | `/api/v1/rest/countries?lang=en&perPage=100` | 200 JSON; Cameroon 1 |
| HTTPS host:3002 | `/api/v1/rest/countries/1?lang=en` | 200 JSON; Cameroon |
| HTTPS host:3002 | `/api/v1/rest/cities/1?lang=en` | 200 JSON; Douala 1 |
| HTTPS host:3002 | `/api/cache/settings` | 200 JSON; original Next handler |

Browser cross-origin preflight is not required for these same-origin API
requests, including requests with Authorization. The API responses still
reported the exact requested frontend Origin when supplied in the HTTP checks;
no wildcard origin was introduced. Laravel logs show the real upstream
translation/settings/geography requests, not frontend fallback HTML.

Five focused transport regressions passed against the active native sources.
No production build or further role/browser acceptance run was started.
The native customer, business and Laravel workflows were restarted and restored.
New Webview logs showed successful settings responses rather than the earlier
translation Network Error. The creator independently confirmed both previews.

## Manual handoff

- Customer:
  https://b762a632-9df8-4939-8b17-506d75cfa277-00-2ngxdayklmnxt.worf.replit.dev:3002/
- Admin/Vendor:
  https://b762a632-9df8-4939-8b17-506d75cfa277-00-2ngxdayklmnxt.worf.replit.dev:3003/login

Reload the previews to obtain the new development bundle. Confirm that Admin
initializes without Network Error, then open the customer location selector,
choose Cameroon and Douala, save and reload, and confirm the location and
seeded storefront remain available.

All 36 reproducible synthetic development identities are listed in
`synthetic-development-credentials.csv`. Their shared password is public
development-fixture data, not a production secret. The inventory is sourced
from `DevelopmentDemoSeeder`, the owned development documentation and a
read-only identity/grant snapshot; it is not a claim that every role was
browser-tested. The non-reproducible signup probe account is intentionally
excluded.