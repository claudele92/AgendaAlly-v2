import path from 'node:path';
import { realpathSync } from 'node:fs';
import { fileURLToPath } from 'node:url';

const adminRoot = realpathSync(fileURLToPath(new URL('../.local/agendaally-preview/admin', import.meta.url)));
const withinAdmin = (filename) => filename === adminRoot || filename.startsWith(`${adminRoot}${path.sep}`);

// Reject unauthorized filesystem URLs before Vite's missing-file SPA fallback,
// including encoded traversal. server.fs remains an independent second layer.
export function isolatedAdminFileAccessPlugin() {
  return {
    name: 'agendaally-owned-preview-file-access',
    configureServer(server) {
      server.middlewares.use((request, response, next) => {
        let pathname = (request.url || '/').split('?')[0];
        try {
          for (let count = 0; count < 4; count++) {
            const decoded = decodeURIComponent(pathname);
            if (decoded === pathname) break;
            pathname = decoded;
          }
          pathname = pathname.replaceAll('\\', '/');
          if (!pathname.startsWith('/@fs/')) return next();
          let filename = path.resolve(pathname.slice('/@fs'.length));
          if (!withinAdmin(filename)) throw new Error('Outside restored admin');
          // Resolve existing symlinks; Vite's own fs rules cover module requests.
          try {
            filename = realpathSync(filename);
          } catch (error) {
            if (error.code !== 'ENOENT' && error.code !== 'ENOTDIR') throw error;
          }
          if (!withinAdmin(filename)) throw new Error('Outside restored admin');
          return next();
        } catch {
          response.statusCode = 403;
          response.setHeader('Content-Type', 'text/plain');
          response.end('Forbidden preview filesystem request.');
        }
      });
    },
  };
}