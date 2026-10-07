# AgendaAlly isolated-preview localization audit

## Scope and safety

The original Laravel source and both copied clients are left unchanged. The preview provisioner reads the original repository's PHP translation catalog and the literal private `TRANSLATIONS` constant in `MissingTranslationsSeeder`, inserting those original values into the guarded SQLite database at `.local/agendaally-preview/backend/storage/preview.sqlite`. It loads the owned runtime's Composer autoloader solely to reflect that class constant; it never invokes the seeder's `run()` method, so neither of its delete statements executes. It does not call Artisan, run Laravel migrations or seeders, contact a production service, change locale behavior, or add invented/generated strings.

The importer is part of `scripts/seed-original-http-preview.php`; it is protected by the existing owned-runtime/database path checks. The dedicated read-only audit is:

```sh
PREVIEW_RUNTIME="$PWD/.local/agendaally-preview/backend" \
PREVIEW_DB_FILE="$PWD/.local/agendaally-preview/backend/storage/preview.sqlite" \
php scripts/verify-original-preview-localization.php
```

To emit every exact English `(locale, group, key)` gap as JSON Lines (23,189 rows from the combined original sources):

```sh
PREVIEW_RUNTIME="$PWD/.local/agendaally-preview/backend" \
PREVIEW_DB_FILE="$PWD/.local/agendaally-preview/backend/storage/preview.sqlite" \
php scripts/verify-original-preview-localization.php --missing-keys
```

The verifier reads no credentials and refuses to inspect a database outside the marked local preview runtime. A passing result confirms the canonical catalog, original supplemental literals, and seeded English default are present; it does not claim the partial non-English catalogs are complete.

## Original content and provenance

`TranslationSeeder::run()` in `.migration-backup/backend/database/seeders/TranslationSeeder.php` selects `resources/lang/translations.php` whenever that file exists, using `translations_en.sql` only as a fallback. `DatabaseSeeder.php:43` calls it before `DatabaseSeeder.php:107` calls `MissingTranslationsSeeder`. The preview follows that order: it imports the PHP array first using `firstOrCreate` identity `(locale, group, key)` (first row wins), then reads only `MissingTranslationsSeeder::TRANSLATIONS` through Reflection and inserts each literal as `en/web/<key>` with the same first-or-create behavior. The supplemental constant has 16 unique entries; two (`Go.to.installation` and `percentage`) already exist in the preferred PHP catalog with identical values, so the canonical rows win and 14 new identities are added. The seeder class is loaded from the original source after loading the owned Composer autoloader; its `run()` method is never called. No rows are deleted: the `other.locations` and `error.descriptoin` cleanup statements at lines 77 and 83 are not executed, and any existing rows under those keys are left intact.

`UserTranslationSeeder::run()` is an empty method and contributes no additional translation values.

The canonical PHP array contains 3,404 source rows and 3,402 unique identities; two repeated rows for `en/web/bonus.stock` are ignored as the original `firstOrCreate` path would do. The importer does not trust the array's exported `id` field: 1,206 rows have `id=0`, and the seeder itself never supplies IDs. Including the 14 supplemental identities, the combined original sources contain 3,416 unique API rows.

The canonical PHP catalog has 2,647 English records (2,645 unique keys) and 757 unique non-English records. The 14 supplemental identities expand the English API dictionary to 2,659 unique keys. All canonical and supplemental values are non-empty and active. Coverage against the expanded original English API dictionary is:

| Locale | Canonical records | Supplemental records | Combined unique keys | English keys missing | Keys present only in this locale |
| --- | ---: | ---: | ---: | ---: | ---: |
| `en` | 2,647 | 16 | 2,659 | 0 | 0 |
| `ar` | 317 | 0 | 317 | 2,357 | 15 |
| `av` | 4 | 0 | 4 | 2,655 | 0 |
| `de` | 33 | 0 | 33 | 2,626 | 0 |
| `el` | 61 | 0 | 61 | 2,598 | 0 |
| `es` | 33 | 0 | 33 | 2,626 | 0 |
| `fa` | 33 | 0 | 33 | 2,626 | 0 |
| `hu` | 22 | 0 | 22 | 2,637 | 0 |
| `ru` | 252 | 0 | 252 | 2,407 | 0 |
| `uzbek` | 2 | 0 | 2 | 2,657 | 0 |

These are the exact missing-key counts against the combined original English `(group, key)` set. Each non-English locale lacks all 14 supplemental English-only keys. The verifier's `--missing-keys` option emits the exact key identities without synthesizing values.

The alternate SQL dump is materially different: it has 5,193 rows. Its locale counts are `3` (93), `4` (128), `5` (82), `6` (65), `7` (39), `8` (24), `9` (22), `10` (14), `11` (14), `ar` (1,592), `av` (4), `be` (3), `de` (33), `el` (61), `en` (1,775), `es` (33), `fa` (33), `hu` (22), `ru` (761), `tr` (393), and `uzbek` (2). It includes `be`/`tr` absent from the preferred PHP array and numeric locale values `3` through `11`, and differs substantially in English, Arabic, and Russian row counts. It is not merged into the preview because the original seeder explicitly prefers the PHP array.

Other language-related files are not a replacement translation catalog:

- `resources/lang/en/{auth,errors,pagination,passwords,translation,validation}.php` contains framework strings in English only; there are no non-English framework language directories.
- `database/seeders/LanguageSeeder.php` configures only English (`en`, title `English`, default).
- `resources/lang/currencies.json` is currency data, not UI translations.
- The storefront's `config/languages.json` is a broad language-name list, not the active application-language catalog.
- Laravel's `config/app.php` sets both `locale` and `fallback_locale` to `en`.

Accordingly, the preview keeps the original one-language `languages` table. Although translation values exist for nine additional locale labels, the repository does not seed their language metadata/configuration. They remain unconfigured rather than being exposed with invented titles, direction, images, or active/default settings.

## Backend API path

The original public routes are `GET /api/v1/rest/languages/active` and `GET /api/v1/rest/translations/paginate?lang=<locale>`. `LanguageController::active()` reads active `languages` rows. `SettingController::translationsPaginate()` returns the requested locale's active `translations` rows as a key/value map and caches them as `language-<locale>`; the locale defaults to the configured language in the base controller. The preview provisioner seeds English as active/default and imports both the preferred PHP catalog and the 14 new original supplemental literals into this table. No controller, route, locale configuration, or production data is changed.

## Client wiring findings

Both isolated clients are configured by `scripts/serve-original-preview-client.sh` to use the same local preview API origin on port `8000`; this is runtime environment wiring and leaves checked-in/copied application configuration intact.

- **Next storefront:** `app/layout.tsx` loads active languages and fetches `translationService.getAll(lang)` from the Laravel REST translations route. It passes the returned map and the active-language list to `TranslationsProvider`, which initializes i18next with the backend values. The language selector's cookie is used as `lang`; the default/API catalog is English. Thus restoring the canonical rows populates the real dictionary used by the storefront.
- **Admin Vite client:** `src/app.jsx` calls `fetchTranslations()` unconditionally on mount, including the login route. It calls `informationService.translations({lang: i18n.language})` and installs the returned dictionary with `i18n.addResourceBundle`. The Axios interceptor also sends `lang: i18n.language` on GET requests. `LangModal` separately reloads the dictionary after the user saves a language choice. `configs/i18next.js` starts with an empty English resource, but this does **not** imply missing startup wiring: the App effect is the original automatic loader. Although `i18next-http-backend` has no Laravel `loadPath`, i18next skips its local JSON loading with the supplied resources and default `partialBundledLanguages: false`. No loader correction or replacement JSON files are needed. The locale is `localStorage.i18nextLng` or the original theme default `en`; a previously saved non-English value can override the sole active English locale. This corrects the earlier incomplete trace that missed the App mount effect.

The Laravel rows are now available for both clients' existing REST requests. No client source, language behavior, database credentials, copied application code, manifests, locks, or `.migration-backup` content is changed by this audit/import.

## Verification performed

The owned preview database contains all 3,416 distinct identities from the combined original sources, with zero missing identities or changed source values/statuses and no supplemental/canonical value conflicts. Its configured language row remains active/default `en`. Read-only requests to the already-running local API succeeded: `/api/v1/rest/languages/active` returned one language and `/api/v1/rest/translations/paginate?lang=en` returned 2,659 key/value entries. No service was started or restarted for these checks.

## Preview-only initialization timing correction

The browser showed raw booking column headings despite the API containing
`name.client: Client name`, `start.date: Start date`, and
`payment.type: Payment type`. The original booking component initializes its
column array with `useState` and does not recompute those titles when the App's
asynchronous translation request completes. These keys are **available**;
their raw display is an initialization timing issue, not absent source content.

`scripts/original-admin-translations-plugin.mjs` preloads the real isolated
API's English dictionary into the original empty i18next resource, before
components initialize state. This is a source-checked, owned-development-only
Vite transform; original application/configuration files and production builds
are unchanged. Original automatic loading, the language modal, persisted locale
selection and fallback settings remain intact. The transform fails explicitly
on unavailable/invalid API data and does not generate translations. Its
API-backed test checks exact dictionary parity and production/unapproved
rejection; the served module was checked. No further browser pass was run for
this straightforward preload correction.

The English catalog's `dashboard` value is literally `Tableau de bord`.
The browser's French sidebar label in English mode therefore reflects original
source content, not a missing fetch; no replacement was created.

## Exact English client-content gaps

A read-only scan of original JS/JSX/TS/TSX client source, excluding dependency
and test directories, compared literal `t(...)` / `i18n.t(...)` arguments
against the combined original English API dictionary. The customer has 392 distinct
literal keys, of which 28 are absent; the admin has 1,548, of which 10 are
absent. These are missing original catalog entries, not loading failures.
Some literal arguments are already English sentences and naturally display
that text as fallback. Computed/template keys cannot be fully resolved by
this static scan and are not included.

Customer keys absent from the combined original English dictionary:

```text
N/A
To see items that ship to a different country, change your delivery address.
appointments
ascending
at
buy
congrats
descending
detail
dismiss
downloading...
editing
favorites
for
members
order.price.did.not.reach.the.min.amount.min.amount.is
process
referral.(optional)
see
similar
specification
store
unlimited
use
want.to.pay.via.your.wallet?
would.you.like.to.add.a.tip?
you
you.can.pay.the.full.amount.with.your.wallet
```

Admin keys absent from the combined original English dictionary:

```text
Click or drag file to this area to upload
In order to update database using this file you need to click button above
N/A
The supplier is not assigned or delivery type pickup
are.you.sure.you.want.to.change.the.activity?
example@info.com
in stock
send.test.email
test.email.failed
test.email.sent.successfully
```

The 14 added English dictionary entries come only from the original seeder's
literal constant; no replacement values were generated for the remaining
gaps. The alternate SQL fallback was not merged into its preferred PHP
source, nor were disabled/incomplete locales enabled.