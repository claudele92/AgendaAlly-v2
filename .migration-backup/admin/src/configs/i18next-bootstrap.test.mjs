import assert from 'node:assert/strict';
import test from 'node:test';
import { initializeTranslations } from './i18next-bootstrap.mjs';

function createI18n(language) {
  const resources = {};
  return {
    language,
    resources,
    addResourceBundle(locale, namespace, bundle) {
      resources[locale] ??= {};
      resources[locale][namespace] = bundle;
    },
    async changeLanguage(nextLanguage) {
      this.language = nextLanguage;
    },
  };
}

test('loads saved language and complete English fallback before initializing labels', async () => {
  const i18n = createI18n('fr');
  const requests = [];
  const result = await initializeTranslations({
    i18n,
    defaultLanguage: 'en',
    fetchTranslations: async (locale) => {
      requests.push(locale);
      return {
        status: true,
        data:
          locale === 'fr'
            ? { dashboard: 'Tableau de bord' }
            : { dashboard: 'Dashboard', 'top.selling.products': 'Top selling products' },
      };
    },
  });

  assert.equal(result, 'fr');
  assert.equal(i18n.language, 'fr');
  assert.deepEqual(requests, ['fr', 'en']);
  assert.deepEqual(i18n.resources.fr.translation, {
    dashboard: 'Tableau de bord',
  });
  assert.deepEqual(i18n.resources.en.translation, {
    dashboard: 'Dashboard',
    'top.selling.products': 'Top selling products',
  });
});

test('loads only one dictionary when saved language is already the fallback', async () => {
  const i18n = createI18n('en');
  const requests = [];

  await initializeTranslations({
    i18n,
    defaultLanguage: 'en',
    fetchTranslations: async (locale) => {
      requests.push(locale);
      return { status: true, data: { dashboard: 'Dashboard' } };
    },
  });

  assert.deepEqual(requests, ['en']);
  assert.equal(i18n.language, 'en');
});

test('preserves a valid but partial saved locale and installs the English fallback', async () => {
  const i18n = createI18n('ar');

  await initializeTranslations({
    i18n,
    defaultLanguage: 'en',
    fetchTranslations: async (locale) => ({
      status: true,
      data: locale === 'ar' ? {} : { dashboard: 'Dashboard' },
    }),
  });

  assert.deepEqual(i18n.resources.ar.translation, {});
  assert.deepEqual(i18n.resources.en.translation, { dashboard: 'Dashboard' });
  assert.equal(i18n.language, 'ar');
});

test('rejects a failed or malformed fallback before installing any partial dictionary', async () => {
  for (const invalidResponse of [
    { status: false, message: 'Translation API failed.' },
    { status: true, data: {} },
    { status: true, data: [] },
    { status: true, data: { dashboard: null } },
  ]) {
    const i18n = createI18n('fr');
    await assert.rejects(
      initializeTranslations({
        i18n,
        defaultLanguage: 'en',
        fetchTranslations: async (locale) =>
          locale === 'fr'
            ? { status: true, data: { dashboard: 'Tableau de bord' } }
            : invalidResponse,
      }),
    );
    assert.deepEqual(i18n.resources, {});
    assert.equal(i18n.language, 'fr');
  }
});

test('accepts the actual Axios response shape when used without the request interceptor', async () => {
  const i18n = createI18n('en');

  await initializeTranslations({
    i18n,
    defaultLanguage: 'en',
    fetchTranslations: async () => ({
      status: 200,
      data: { status: true, data: { dashboard: 'Dashboard' } },
    }),
  });

  assert.deepEqual(i18n.resources.en.translation, { dashboard: 'Dashboard' });
});