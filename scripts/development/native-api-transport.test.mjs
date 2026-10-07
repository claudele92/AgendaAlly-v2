import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';
import { fileURLToPath } from 'node:url';

const root = fileURLToPath(new URL('../..', import.meta.url));
const preview = path.join(root, '.migration-backup');
const web = path.join(preview, 'web');
const admin = path.join(preview, 'admin');
const webRequire = createRequire(path.join(web, 'package.json'));
const rootRequire = createRequire(path.join(root, 'package.json'));
const ts = webRequire('typescript');

function compileTypeScript(sourcePath) {
  const source = fs.readFileSync(sourcePath, 'utf8');
  const output = ts.transpileModule(source, {
    compilerOptions: {
      module: ts.ModuleKind.CommonJS,
      target: ts.ScriptTarget.ES2020,
      esModuleInterop: true,
    },
  }).outputText;
  const module = { exports: {} };

  new Function('module', 'exports', 'URL', output)(module, module.exports, URL);
  return module.exports;
}

test('web development selects same-origin browser URLs and configured local SSR URLs only in development', () => {
  const { isDevelopmentServer, resolveApiBaseUrl } = compileTypeScript(
    path.join(web, 'lib/api-transport.ts'),
  );
  const common = {
    publicBaseUrl: 'https://api.demand24.org/api/',
    developmentServer: true,
    devApiTarget: 'http://127.0.0.1:8123',
  };

  assert.equal(
    resolveApiBaseUrl({ ...common, isBrowser: true }),
    '/api/',
  );
  assert.equal(
    resolveApiBaseUrl({
      ...common,
      isBrowser: true,
      devApiTarget: 'not-an-origin',
    }),
    '/api/',
  );
  assert.equal(
    resolveApiBaseUrl({ ...common, isBrowser: false }),
    'http://127.0.0.1:8123/api/',
  );
  assert.equal(
    resolveApiBaseUrl({ ...common, developmentServer: false, isBrowser: true }),
    common.publicBaseUrl,
  );
  assert.equal(isDevelopmentServer('development', 'local', 'true'), true);
  assert.equal(isDevelopmentServer('production', 'local', 'true'), false);
  assert.equal(isDevelopmentServer('development', 'staging', 'true'), false);
  assert.equal(
    resolveApiBaseUrl({
      ...common,
      developmentServer: false,
      isBrowser: false,
      devApiTarget: 'not-an-origin',
    }),
    common.publicBaseUrl,
  );
  assert.throws(
    () =>
      resolveApiBaseUrl({
        ...common,
        isBrowser: false,
        devApiTarget: 'https://user:secret@api.example.invalid/path',
      }),
    /AGENDAALLY_DEV_API_TARGET/,
  );
  assert.throws(
    () =>
      resolveApiBaseUrl({
        developmentServer: true,
        isBrowser: false,
        publicBaseUrl: common.publicBaseUrl,
      }),
    /AGENDAALLY_DEV_API_TARGET/,
  );
  assert.doesNotMatch(
    fs.readFileSync(path.join(web, 'lib/api-transport.ts'), 'utf8'),
    /127\.0\.0\.1|localhost/,
  );
});

test('upstream target validation accepts an origin and rejects URLs that escape it', () => {
  const { DEFAULT_DEV_API_TARGET, resolveDevApiTarget } = rootRequire(
    path.join(root, 'scripts/development/dev-api-target.cjs'),
  );

  assert.equal(DEFAULT_DEV_API_TARGET, 'http://127.0.0.1:8000');
  assert.equal(resolveDevApiTarget(undefined), DEFAULT_DEV_API_TARGET);
  assert.equal(resolveDevApiTarget('https://api.local.example:8443'), 'https://api.local.example:8443');
  for (const target of [
    'file:///tmp/backend',
    'https://user:secret@api.local.example',
    'https://api.local.example/api',
    'https://api.local.example?override=1',
    'not-a-url',
  ]) {
    assert.throws(() => resolveDevApiTarget(target), /AGENDAALLY_DEV_API_TARGET/);
  }
});

test('development rewrite is limited to /api/v1 and cannot consume /api/cache', async () => {
  const configPath = path.join(web, 'next.config.js');
  const resolveConfig = () => {
    delete webRequire.cache?.[configPath];
    return webRequire(configPath);
  };
  const envKeys = [
    'NODE_ENV',
    'AGENDAALLY_DEV_API_TARGET',
    'NEXT_PUBLIC_APP_ENV',
    'NEXT_PUBLIC_DEVELOPMENT_MODE',
    'NEXT_PUBLIC_BASE_URL',
    'NEXT_PUBLIC_WEBSITE_URL',
    'NEXT_PUBLIC_ADMIN_PANEL_URL',
    'NEXT_PUBLIC_IMAGE_URL',
    'NEXT_PUBLIC_FIREBASE_ENABLED',
    'NEXT_PUBLIC_MAPS_ENABLED',
    'NEXT_BASE_PATH',
  ];
  const previousEnvironment = Object.fromEntries(envKeys.map((key) => [key, process.env[key]]));

  try {
    process.env.NODE_ENV = 'development';
    process.env.NEXT_PUBLIC_APP_ENV = 'local';
    process.env.NEXT_PUBLIC_DEVELOPMENT_MODE = 'true';
    process.env.NEXT_PUBLIC_BASE_URL = 'http://127.0.0.1:8000/api/';
    process.env.NEXT_PUBLIC_WEBSITE_URL = 'http://localhost:3002/';
    process.env.NEXT_PUBLIC_ADMIN_PANEL_URL = 'http://localhost:3003/';
    process.env.NEXT_PUBLIC_IMAGE_URL = 'http://127.0.0.1:8000/storage/';
    process.env.NEXT_PUBLIC_FIREBASE_ENABLED = 'false';
    process.env.NEXT_PUBLIC_MAPS_ENABLED = 'false';
    delete process.env.NEXT_BASE_PATH;
    process.env.AGENDAALLY_DEV_API_TARGET = 'http://127.0.0.1:8123';
    const rewrites = await resolveConfig().rewrites();
    assert.deepEqual(rewrites, [
      {
        source: '/api/v1/:path*',
        destination: 'http://127.0.0.1:8123/api/v1/:path*',
      },
    ]);
    assert.deepEqual(Object.keys(rewrites[0]).sort(), ['destination', 'source']);

    const source = rewrites[0].source;
    const prefix = source.slice(0, source.indexOf('/:path*'));
    const matchesRewrite = (pathname) =>
      pathname === prefix || pathname.startsWith(`${prefix}/`);
    assert.equal(matchesRewrite('/api/v1'), true);
    assert.equal(matchesRewrite('/api/v1/rest/products'), true);
    assert.equal(matchesRewrite('/api/cache/settings'), false);
    assert.equal(matchesRewrite('/api/v10/rest/products'), false);

    delete process.env.AGENDAALLY_DEV_API_TARGET;
    const defaultRewrites = await resolveConfig().rewrites();
    assert.equal(process.env.AGENDAALLY_DEV_API_TARGET, 'http://127.0.0.1:8000');
    assert.equal(defaultRewrites[0].destination, 'http://127.0.0.1:8000/api/v1/:path*');

    process.env.NODE_ENV = 'production';
    process.env.NEXT_PUBLIC_DEVELOPMENT_MODE = 'false';
    process.env.NEXT_PUBLIC_BASE_URL = 'https://api.demand24.org/api/';
    process.env.NEXT_PUBLIC_WEBSITE_URL = 'https://agendaally.com/';
    process.env.NEXT_PUBLIC_ADMIN_PANEL_URL = 'https://admin.agendaally.com/';
    process.env.NEXT_PUBLIC_IMAGE_URL = 'https://api.demand24.org/storage/';
    assert.deepEqual(await resolveConfig().rewrites(), []);
  } finally {
    for (const key of envKeys) {
      if (previousEnvironment[key] === undefined) delete process.env[key];
      else process.env[key] = previousEnvironment[key];
    }
  }
});

test('admin Vite only proxies /api/v1 during development', async () => {
  const adminRequire = createRequire(path.join(admin, 'package.json'));
  const { loadConfigFromFile } = adminRequire('vite');
  const configPath = path.join(admin, 'vite.config.js');
  const envKeys = [
    'AGENDAALLY_DEV_API_TARGET',
    'VITE_APP_ENV',
    'VITE_DEVELOPMENT_MODE',
    'VITE_API_ORIGIN',
    'VITE_BASE_URL',
    'VITE_STOREFRONT_URL',
    'VITE_ADMIN_PANEL_URL',
    'VITE_PORT',
    'VITE_RECAPTCHA_DISABLED',
    'VITE_FIREBASE_ENABLED',
    'VITE_MAPS_ENABLED',
    'VITE_ALLOWED_HOSTS',
  ];
  const previousEnvironment = Object.fromEntries(envKeys.map((key) => [key, process.env[key]]));

  try {
    process.env.VITE_APP_ENV = 'local';
    process.env.VITE_DEVELOPMENT_MODE = 'true';
    process.env.VITE_API_ORIGIN = 'http://127.0.0.1:8000';
    process.env.VITE_BASE_URL = '';
    process.env.VITE_STOREFRONT_URL = 'http://localhost:3002';
    process.env.VITE_ADMIN_PANEL_URL = 'http://localhost:3003';
    process.env.VITE_PORT = '3003';
    process.env.VITE_RECAPTCHA_DISABLED = 'true';
    process.env.VITE_FIREBASE_ENABLED = 'false';
    process.env.VITE_MAPS_ENABLED = 'false';
    process.env.VITE_ALLOWED_HOSTS = '';
    process.env.AGENDAALLY_DEV_API_TARGET = 'http://127.0.0.1:8123';
    const development = await loadConfigFromFile(
      { command: 'serve', mode: 'development' },
      configPath,
      admin,
    );
    assert.deepEqual(development.config.server.proxy, {
      '/api/v1/': {
        target: 'http://127.0.0.1:8123',
        changeOrigin: true,
      },
    });
    assert.equal(Object.hasOwn(development.config.server.proxy, '/api/cache'), false);

  } finally {
    for (const key of envKeys) {
      if (previousEnvironment[key] === undefined) delete process.env[key];
      else process.env[key] = previousEnvironment[key];
    }
  }
});

test('authorization remains attached through same-origin transport and translation adds no CORS response header', () => {
  const adminConstants = fs.readFileSync(path.join(admin, 'src/configs/app-global.js'), 'utf8');
  const translation = fs.readFileSync(path.join(web, 'services/translation.ts'), 'utf8');
  const adminViteConfig = fs.readFileSync(path.join(admin, 'vite.config.js'), 'utf8');
  const webFetcher = fs.readFileSync(path.join(web, 'lib/fetcher.ts'), 'utf8');

  assert.match(adminConstants, /runtime\?\.developmentServer \? '\/api\/v1\/' : `\$\{BASE_URL\}\/api\/v1\/`/);
  assert.match(adminConstants, /export_url = `\$\{BASE_URL\}\/storage\/`/);
  assert.match(adminViteConfig, /runtime\?\.developmentServer/);
  assert.match(adminViteConfig, /['"]\/api\/v1\/['"]/);
  assert.match(webFetcher, /Authorization: getCookie\("token"\) as string/);
  assert.match(webFetcher, /\.\.\.init\?\.headers/);
  const middleware = fs.readFileSync(path.join(web, 'middleware.ts'), 'utf8');
  // Root consolidation no longer needs middleware to fetch ui_type/settings.
  // The remaining locale request must retain the native server transport.
  assert.match(middleware, /const apiBaseUrl = resolveApiBaseUrl\(/);
  assert.match(middleware, /fetch\(`\$\{apiBaseUrl\}v1\/rest\/languages\/active`/);
  assert.doesNotMatch(translation, /Access-Control-Allow-Origin/);
});
