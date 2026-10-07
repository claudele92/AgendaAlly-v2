import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { isolatedOriginalTranslationsPlugin } from './original-admin-translations-plugin.mjs';

const admin = fileURLToPath(new URL('../.local/agendaally-preview/admin', import.meta.url));
const target = `${admin}/src/configs/i18next.js`;
const source = readFileSync(target, 'utf8');

test('preload uses the real isolated API without changing source or locale behavior', async () => {
  const saved = { ...process.env };
  try {
    process.env.REPLIT_DEV_DOMAIN = 'preview.replit.dev';
    process.env.VITE_BASE_URL = 'https://preview.replit.dev:8000';
    process.env.AGENDAALLY_PREVIEW_ORIGINAL_TRANSLATIONS = 'approved';
    const config = { command: 'serve', mode: 'development', root: admin };
    const plugin = isolatedOriginalTranslationsPlugin();
    await assert.rejects(plugin.transform(source, target));
    plugin.configResolved(config);
    assert.equal(await plugin.transform('unrelated', `${admin}/src/other.js`), null);
    const output = (await plugin.transform(source, target)).code;
    const dictionary = JSON.parse(output.match(/translation: (\{.*\}),/)[1]);
    const response = await fetch('http://127.0.0.1:8000/api/v1/rest/translations/paginate?lang=en');
    assert.deepEqual(dictionary, (await response.json()).data);
    assert.equal(dictionary['name.client'], 'Client name');
    assert.equal(dictionary['start.date'], 'Start date');
    assert.deepEqual(
      Object.fromEntries(
        [
          'dashboard',
          'revenue.over.time',
          'subWeek',
          'subMonth',
          'subYear',
        ].map((key) => [key, dictionary[key]]),
      ),
      {
        dashboard: 'Dashboard',
        'revenue.over.time': 'Revenue over time',
        subWeek: 'Last week',
        subMonth: 'Last month',
        subYear: 'Last year',
      },
    );
    assert.ok(output.includes("lng: localStorage.getItem('i18nextLng') || THEME_CONFIG.locale"));
    assert.equal(readFileSync(target, 'utf8'), source);
    await assert.rejects(plugin.transform(`${source}\n`, target));
    for (const changed of [{ command: 'build' }, { mode: 'production' }, { root: '/' }]) {
      assert.throws(() => isolatedOriginalTranslationsPlugin().configResolved({ ...config, ...changed }));
    }
    process.env.VITE_BASE_URL = 'https://unapproved.invalid';
    assert.throws(() => isolatedOriginalTranslationsPlugin().configResolved(config));
    process.env.VITE_BASE_URL = 'https://preview.replit.dev:8000';
    delete process.env.AGENDAALLY_PREVIEW_ORIGINAL_TRANSLATIONS;
    assert.throws(() => isolatedOriginalTranslationsPlugin().configResolved(config));
  } finally {
    process.env = saved;
  }
});