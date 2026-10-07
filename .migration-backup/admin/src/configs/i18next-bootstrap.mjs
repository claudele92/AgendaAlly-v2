function getTranslationDictionary(response, locale, isFallbackLocale) {
  // The request interceptor normally unwraps Axios and returns Laravel's JSON
  // body. Accept an unwrapped Axios response as well for callers that bypass it.
  const body =
    typeof response?.status === 'number' && response.data
      ? response.data
      : response;

  if (!body || body.status === false) {
    throw new Error(
      `The ${locale} translation request was unsuccessful in the API.`,
    );
  }

  const dictionary = body.data;
  if (
    !dictionary ||
    typeof dictionary !== 'object' ||
    Array.isArray(dictionary) ||
    Object.values(dictionary).some((value) => typeof value !== 'string') ||
    (isFallbackLocale && Object.keys(dictionary).length === 0)
  ) {
    throw new Error(`The ${locale} translation response was invalid.`);
  }

  return dictionary;
}

export async function initializeTranslations({
  i18n,
  defaultLanguage,
  fetchTranslations,
}) {
  const language = i18n.language || i18n.resolvedLanguage || defaultLanguage;
  const locales = [...new Set([language, defaultLanguage])];

  // Load both the user's saved locale and English fallback before any screen
  // mounts stateful headings using t(). A partial but valid locale remains
  // partial; i18next serves any untranslated keys from the complete fallback.
  const dictionaries = await Promise.all(
    locales.map(async (locale) => ({
      locale,
      dictionary: getTranslationDictionary(
        await fetchTranslations(locale),
        locale,
        locale === defaultLanguage,
      ),
    })),
  );

  for (const { locale, dictionary } of dictionaries) {
    i18n.addResourceBundle(locale, 'translation', dictionary, true, true);
  }

  await i18n.changeLanguage(language);
  return language;
}