import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';
import tsconfigPaths from 'vite-tsconfig-paths';
import { realpathSync } from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { resolveAdminRuntimeConfig } from './src/configs/runtime-config.mjs';
import devApiTargetModule from '../../scripts/development/dev-api-target.cjs';

const projectRoot = path.resolve(fileURLToPath(new URL('.', import.meta.url)));
const { resolveDevApiTarget } = devApiTargetModule;

function enforceDevFileBoundary() {
  return {
    name: 'admin-dev-file-boundary',
    configureServer(server) {
      server.middlewares.use((req, res, next) => {
        if (!req.url?.startsWith('/@fs/')) return next();

        let requested;
        try {
          const pathname = req.url.split('?', 1)[0];
          let target = pathname.slice('/@fs/'.length);
          for (let attempt = 0; attempt < 4; attempt += 1) {
            const decoded = decodeURIComponent(target);
            if (decoded === target) break;
            target = decoded;
          }
          if (/%[0-9a-f]{2}/i.test(target)) {
            res.statusCode = 403;
            res.end('Forbidden');
            return;
          }
          requested = path.resolve(target);
        } catch {
          res.statusCode = 403;
          res.end('Forbidden');
          return;
        }

        const insideRoot =
          requested === projectRoot || requested.startsWith(`${projectRoot}${path.sep}`);
        if (!insideRoot) {
          res.statusCode = 403;
          res.end('Forbidden');
          return;
        }

        const lowerPath = requested.toLowerCase();
        if (
          lowerPath.split(path.sep).some((part) =>
            part === '.git' ||
            part === 'node_modules' ||
            part === 'vendor' ||
            part === 'storage' ||
            part === '.cache' ||
            part.startsWith('.env') ||
            /\.(?:pem|key|crt|sqlite|db)$/.test(part)
          )
        ) {
          res.statusCode = 403;
          res.end('Forbidden');
          return;
        }

        try {
          const realTarget = realpathSync(requested);
          if (
            realTarget !== projectRoot &&
            !realTarget.startsWith(`${projectRoot}${path.sep}`)
          ) {
            res.statusCode = 403;
            res.end('Forbidden');
            return;
          }
        } catch {
          // Vite will return its normal 404 for a nonexistent in-root module.
        }

        return next();
      });
    },
  };
}

export default defineConfig(({ command, mode }) => {
  const environment = loadEnv(mode, projectRoot, '');
  const nodeEnvironment = command === 'build' ? 'production' : 'development';
  let runtime;
  try {
    runtime = resolveAdminRuntimeConfig(environment, command, nodeEnvironment);
  } catch (error) {
    if (command !== 'serve') throw error;
  }
  const adminHost = runtime ? new URL(runtime.adminUrl).hostname : null;

  return {
    root: projectRoot,
    plugins: [enforceDevFileBoundary(), react(), tsconfigPaths({ root: projectRoot })],
    assetsInclude: ['**/*.riv'],
    optimizeDeps: {
      esbuildOptions: {
        loader: {
          '.js': 'js',
        },
      },
    },
    server: {
      host: environment.VITE_HOST || '0.0.0.0',
      port: runtime?.port || 3003,
      strictPort: true,
      open: false,
      allowedHosts: Array.from(
        new Set([
          'localhost',
          '127.0.0.1',
          '::1',
          ...(adminHost ? [adminHost] : []),
          ...(runtime?.allowedHosts || []),
        ]),
      ),
      ...(runtime?.developmentServer
        ? {
            proxy: {
              '/api/v1/': {
                target: resolveDevApiTarget(
                  environment.AGENDAALLY_DEV_API_TARGET || process.env.AGENDAALLY_DEV_API_TARGET,
                ),
                changeOrigin: true,
              },
            },
          }
        : {}),
      fs: {
        strict: true,
        allow: [projectRoot],
        deny: ['.env', '.env.*', '**/.git/**', '**/*.pem', '**/*.key', '**/*.sqlite', '**/*.db'],
      },
    },
    build: {
      outDir: 'build',
    },
  };
});