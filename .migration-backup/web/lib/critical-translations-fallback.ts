// A minimal, build-time-baked translation bundle for the handful of keys
// that error/not-found/empty-state UI depends on to explain a problem to
// the customer. TranslationsProvider merges this in *underneath* whatever
// the backend returns, so it only ever fills a gap - real translations
// always win when the backend is reachable. It exists specifically for
// the case where the backend is fully unreachable (so the translations
// fetch itself fails open to {}): without this, every t() call - including
// on the very pages meant to tell the customer something's wrong - would
// render as a raw, untranslated key.
//
// English only, deliberately: this app's supported languages are
// admin-configured at runtime (see the `languages` table), not a fixed
// set at build time, so there's no coherent way to pre-translate this
// into every locale a given deployment might add. During a full outage, a
// non-English visitor sees these specific strings in English rather than
// their own language - real readable text in a fallback language beats a
// raw key in no language at all.
//
// These values mirror the same keys' seeded English text in
// backend/resources/lang/translations.php - if that seeded wording ever
// changes, update this file to match so the outage experience doesn't
// drift from the normal one.
export const criticalTranslationsFallback: Record<string, string> = {
  "error.message": "Something went wrong",
  "error.description": "An unexpected error occurred. Please try refreshing the page.",
  reset: "Reset",
  "some.thing.wrong": "Something went wrong",
  "page.doest.exist": "The page you're looking for doesn't exist",
  "go.to.home": "Go to homepage",
  "there.is.no.items": "There are no items",
  "no.referrals.found": "No referrals found",
};
