import { existsSync, readFileSync, realpathSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const workspace = realpathSync(fileURLToPath(new URL('..', import.meta.url)));
const admin = path.join(workspace, '.local/agendaally-preview/admin');
const original = path.join(workspace, '.migration-backup/admin/src/configs/i18next.js');
const target = path.join(admin, 'src/configs/i18next.js');

// Populate the original empty English resource before components initialize
// stateful labels. The original App REST loader and language modal remain active.
export function isolatedOriginalTranslationsPlugin() {
  let allowed = false;
  return {
    name: 'agendaally-owned-preview-original-translations',
    enforce: 'pre',
    configResolved(config) {
      const domain = process.env.REPLIT_DEV_DOMAIN;
      if (config.command !== 'serve' || config.mode !== 'development' ||
          process.env.AGENDAALLY_PREVIEW_ORIGINAL_TRANSLATIONS !== 'approved' ||
          !domain || !/^[a-zA-Z0-9.-]+$/.test(domain) ||
          process.env.VITE_BASE_URL !== `https://${domain}:8000` ||
          realpathSync(config.root) !== realpathSync(admin) ||
          !existsSync(path.join(workspace, '.local/agendaally-preview/backend/.original-http-preview-owned'))) {
        throw new Error('Original translations preload is restricted to the owned development preview and isolated API.');
      }
      allowed = true;
    },
    async transform(code, id) {
      if (id.split('?')[0] !== target) return null;
      if (!allowed || code !== readFileSync(original, 'utf8') ||
          code.split("translation: '',").length !== 2) {
        throw new Error('Original translations preload refused changed or unapproved localization source.');
      }
      const response = await fetch('http://127.0.0.1:8000/api/v1/rest/translations/paginate?lang=en', {
        headers: { Accept: 'application/json' },
        signal: AbortSignal.timeout(10000),
      });
      if (!response.ok) throw new Error(`Original translations API unavailable (HTTP ${response.status}).`);
      const payload = await response.json();
      const dictionary = payload.data;
      if (payload.status !== true || !dictionary || typeof dictionary !== 'object' ||
          Array.isArray(dictionary) || !Object.keys(dictionary).length ||
          Object.values(dictionary).some((value) => typeof value !== 'string')) {
        throw new Error('Original translations API did not return a nonempty string dictionary.');
      }
      return { code: code.replace("translation: '',", `translation: ${JSON.stringify(dictionary)},`), map: null };
    },
  };
}