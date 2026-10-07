/**
 * Falls back to the superadmin's configured default_country_id/
 * default_city_id (see backend SettingsSeeder + Settings -> General
 * Settings -> Default Country/City) whenever a visitor has no country_id/
 * city_id cookie yet.
 *
 * Without this, a first-time visitor's SSR-rendered homepage (this
 * function's caller, which reads only the cookie) would show an
 * unfiltered/default shop list, while the client's zustand address store
 * gets seeded with the *same* default_country_id/default_city_id settings
 * (see context/settings/settings.tsx's SettingsProvider) as soon as it
 * mounts - two different "current country" values for the same page load,
 * the exact SSR/client divergence behind the carousel shop-swap bug this
 * mirrors the fix for (see country-select-form.tsx's cookie maxAge fix).
 * Once a visitor actually picks a country, their own cookie takes over and
 * this fallback no longer applies.
 */
export const resolveDefaultLocation = (
  countryId: string | undefined,
  cityId: string | undefined,
  settings?: Record<string, string>
): { countryId?: string; cityId?: string } => {
  if (countryId) {
    return { countryId, cityId };
  }

  return {
    countryId: settings?.default_country_id || undefined,
    cityId: settings?.default_city_id || undefined,
  };
};
