import originalConfig from '../.local/agendaally-preview/admin/vite.config.js';
import { isolatedLocalLoginPlugin } from './original-admin-local-login-plugin.mjs';
import { isolatedOriginalTranslationsPlugin } from './original-admin-translations-plugin.mjs';
import { isolatedAdminFileAccessPlugin } from './original-admin-file-isolation-plugin.mjs';
import { realpathSync } from 'node:fs';
import { fileURLToPath } from 'node:url';

const adminRoot = realpathSync(fileURLToPath(new URL('../.local/agendaally-preview/admin', import.meta.url)));
const backendRoot = realpathSync(fileURLToPath(new URL('../.local/agendaally-preview/backend', import.meta.url)));

// Retain every original Vite option/plugin. This wrapper is used only by the
// isolated dev launcher; the original configuration and build remain untouched.
export default {
  ...originalConfig,
  plugins: [isolatedAdminFileAccessPlugin(), isolatedLocalLoginPlugin(), isolatedOriginalTranslationsPlugin(), ...originalConfig.plugins],
  server: {
    ...originalConfig.server,
    fs: {
      ...originalConfig.server?.fs,
      // Vite's inferred workspace root includes sibling private runtimes.
      // Only this restored client (including its own dependencies) may be served.
      strict: true,
      allow: [adminRoot],
      deny: [
        '.env', '.env.*', '*.{crt,pem}', '**/.git/**',
        ...(originalConfig.server?.fs?.deny || []),
        `${backendRoot}/**`,
        '**/.preview-*',
        '**/*.sqlite',
        '**/*.sqlite-*',
        '**/.agents/**',
      ],
    },
  },
};