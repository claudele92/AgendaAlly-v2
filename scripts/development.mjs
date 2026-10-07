#!/usr/bin/env node
import fs from 'node:fs';
import path from 'node:path';
import { spawn } from 'node:child_process';
import { adminSmtpLaunchOptions } from './development/admin-smtp-launch.mjs';
import {
  root, projects, dotenv, assertLocal, initializeFile, replitOrigins, validatePort,
  assertDevelopmentDatabaseConfiguration, configureOrigins, developmentCorsOrigins,
  assertNativeDevelopmentPaths,
} from './development/config.mjs';

const [command = 'help', target, ...args] = process.argv.slice(2);
const log = text => console.log(text);

function run(binary, arguments_, { cwd = root, env = process.env } = {}) {
  return new Promise((resolve, reject) => {
    // A package runner/framework can spawn its own server child. Give the
    // command a process group so stopping this launcher also stops those
    // descendants instead of leaving an orphan occupying the preview port.
    const grouped = process.platform !== 'win32';
    const child = spawn(binary, arguments_, { cwd, env, stdio: 'inherit', detached: grouped });
    const terminate = signal => {
      try {
        if (grouped && child.pid) process.kill(-child.pid, signal);
        else child.kill(signal);
      } catch (error) {
        if (error.code !== 'ESRCH') throw error;
      }
    };
    const onInt = () => terminate('SIGINT');
    const onTerm = () => terminate('SIGTERM');
    process.on('SIGINT', onInt);
    process.on('SIGTERM', onTerm);
    child.once('error', reject);
    child.once('exit', (code, signal) => {
      process.off('SIGINT', onInt);
      process.off('SIGTERM', onTerm);
      if (code === 0) resolve();
      else reject(new Error(`${binary} failed (${signal || code}).`));
    });
  });
}

function localEnvironment() {
  const config = dotenv(projects.backend);
  assertDevelopmentDatabaseConfiguration(config, projects.backend);
  assertNativeDevelopmentPaths(config, process.env, projects.backend);
  const inherited = { ...process.env };
  // An explicitly verified app-specific SQLite file takes precedence over
  // an unrelated scaffold/host connection, without reading its secret value.
  delete inherited.DATABASE_URL;
  delete inherited.DB_URL;
  assertLocal(config, inherited);
  return { ...inherited, ...config };
}

async function artisan(arguments_) {
  await run('php', ['artisan', ...arguments_], { cwd: projects.backend, env: localEnvironment() });
}

function init() {
  if (process.env.APP_ENV && !['local', 'testing'].includes(process.env.APP_ENV)) {
    throw new Error('Refusing development init from a staging/production environment.');
  }
  const replit = target === '--replit' || args.includes('--replit');
  const origins = replit ? replitOrigins(process.env.REPLIT_DEV_DOMAIN) :
    { backend: 'http://localhost:8000', web: 'http://localhost:3002', admin: 'http://localhost:3003' };
  const backendDefaults = {
    APP_ENV: 'local', APP_DEBUG: 'false', DEVELOPMENT_MODE: 'true',
    APP_URL: origins.backend, CUSTOMER_URL: origins.web, ADMIN_URL: origins.admin,
    LARAVEL_BACKEND_URL: origins.backend, CUSTOMER_STOREFRONT_URL: origins.web,
    VENDOR_ADMIN_URL: origins.admin, FRONT_URL: origins.web,
    CORS_ALLOWED_ORIGINS: `${origins.web},${origins.admin}`,
    AGENDAALLY_DEVELOPMENT_DATABASE: 'true',
    DB_CONNECTION: 'sqlite', DB_DATABASE: path.join(projects.backend, 'database', 'development', 'agendaally.sqlite'),
    CACHE_STORE: 'file', CACHE_DRIVER: 'file', SESSION_DRIVER: 'file', QUEUE_CONNECTION: 'sync',
    MAIL_MAILER: 'log',
  };
  for (const [name, overrides] of Object.entries({
    backend: backendDefaults,
    web: { NEXT_PUBLIC_APP_ENV: 'local', NEXT_PUBLIC_DEVELOPMENT_MODE: 'true',
      NEXT_PUBLIC_BASE_URL: `${origins.backend}/api/`, NEXT_PUBLIC_WEBSITE_URL: origins.web,
      NEXT_PUBLIC_ADMIN_PANEL_URL: origins.admin, PORT: '3002' },
    admin: { VITE_APP_ENV: 'local', VITE_DEVELOPMENT_MODE: 'true',
      VITE_BASE_URL: origins.backend, VITE_WEBSITE_URL: origins.web,
      VITE_STOREFRONT_URL: origins.web, VITE_ADMIN_PANEL_URL: origins.admin,
      VITE_ADMIN_URL: origins.admin, VITE_PORT: '3003', PORT: '3003' },
  })) {
    const created = initializeFile(projects[name], overrides);
    log(`${name}: ${created ? 'created private .env from example' : 'existing .env preserved'}.`);
  }
  for (const directory of ['bootstrap/cache', 'storage/logs', 'storage/framework/cache/data',
    'storage/framework/sessions', 'storage/framework/views', 'storage/app/public']) {
    fs.mkdirSync(path.join(projects.backend, directory), { recursive: true, mode: 0o700 });
  }
}

async function install() {
  log('Installing the original locked dependencies without database-mutating hooks.');
  await run('composer', ['install', '--no-scripts', '--no-plugins', '--no-interaction', '--prefer-dist'], { cwd: projects.backend });
  for (const name of ['web', 'admin']) {
    await run('yarn', ['install', '--frozen-lockfile', '--ignore-scripts', '--non-interactive'], { cwd: projects[name] });
  }
}

async function configureBackend() {
  const config = dotenv(projects.backend);
  localEnvironment();
  await artisan(['package:discover', '--quiet']);
  if (!config.APP_KEY) await artisan(['key:generate', '--quiet']);
  await artisan(['storage:link', '--quiet']);
}

function origins() {
  localEnvironment();
  const selected = target === '--replit' ? replitOrigins(process.env.REPLIT_DEV_DOMAIN) :
    { backend: 'http://localhost:8000', web: 'http://localhost:3002', admin: 'http://localhost:3003' };
  configureOrigins(projects.backend, {
    APP_URL: selected.backend, LARAVEL_BACKEND_URL: selected.backend,
    CUSTOMER_STOREFRONT_URL: selected.web, CUSTOMER_URL: selected.web, FRONT_URL: selected.web,
    VENDOR_ADMIN_URL: selected.admin, ADMIN_URL: selected.admin,
    CORS_ALLOWED_ORIGINS: developmentCorsOrigins(selected),
  });
  configureOrigins(projects.web, {
    NEXT_PUBLIC_BASE_URL: `${selected.backend}/api/`,
    NEXT_PUBLIC_WEBSITE_URL: selected.web, NEXT_PUBLIC_ADMIN_PANEL_URL: selected.admin,
  });
  configureOrigins(projects.admin, {
    VITE_BASE_URL: selected.backend, VITE_STOREFRONT_URL: selected.web,
    VITE_WEBSITE_URL: selected.web, VITE_ADMIN_PANEL_URL: selected.admin, VITE_ADMIN_URL: selected.admin,
  });
  log('Updated only public application URLs/CORS origins; existing credentials were preserved.');
}

async function serve() {
  if (!Object.hasOwn(projects, target)) throw new Error('Serve requires backend, web or admin.');
  const env = localEnvironment();
  const config = dotenv(projects[target]);
  const defaultPort = { backend: '8000', web: '3002', admin: '3003' }[target];
  const port = validatePort(process.env.PORT || config.PORT || defaultPort);
  const host = process.env.HOST || '0.0.0.0';
  if (!['0.0.0.0', '127.0.0.1', 'localhost', '::1'].includes(host)) throw new Error('Unsupported development bind host.');
  if (target === 'backend') {
    await run('php', [path.join(root, 'scripts/development/check-backend.php'), projects.backend], { env });
    const router = path.join(projects.backend, 'vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php');
    if (!fs.existsSync(router)) throw new Error('Laravel dependencies are missing; run install first.');
    // The standard Laravel dev router boots public/index.php. Disable independent
    // SDK transports as defense in depth in this explicitly local launcher.
    const smtpTestEnabled = process.env.AGENDAALLY_NORMAL_ADMIN_SMTP_TEST === 'true';
    const smtp = adminSmtpLaunchOptions({
      enabled: smtpTestEnabled, backend: projects.backend, port, environment: env,
      published: process.env.REPLIT_DEPLOYMENT !== undefined,
    });
    await run('php', ['-d', 'allow_url_fopen=0', '-d', `disable_functions=${smtp.disabled}`,
      ...smtp.directives, '-S', `${host}:${port}`, router],
      { cwd: path.join(projects.backend, 'public'), env: {
        ...env, AGENDAALLY_NORMAL_ADMIN_SMTP_TEST: smtpTestEnabled ? 'true' : 'false',
      } });
  } else {
    const directory = projects[target];
    const executable = target === 'web' ? 'node_modules/next/dist/bin/next' : 'node_modules/vite/bin/vite.js';
    // Use the same supported Webpack engine as offline compilation. The
    // default Turbopack preview retained nearly 1.8 GB during native browsing,
    // leaving insufficient headroom for the browser recorder and editor.
    const arguments_ = target === 'web' ? ['dev', '--webpack'] : ['--host', host, '--port', port];
    // The clients load their own native dotenv configuration. The same sources
    // also support their normal yarn dev/build/start commands without this CLI.
    const launcher = target === 'web' ? 'scripts/server.mjs' : executable;
    await run('node', [path.join(directory, launcher), ...arguments_], {
      cwd: directory,
      env: { ...process.env, HOST: host, PORT: port, NEXT_TELEMETRY_DISABLED: '1' },
    });
  }
}

try {
  switch (command) {
    case 'install': await install(); break;
    case 'init': init(); break;
    case 'configure': await configureBackend(); break;
    case 'origins': origins(); break;
    case 'database-exists': {
      const environment = localEnvironment();
      process.exitCode = fs.existsSync(path.resolve(projects.backend, environment.DB_DATABASE)) ? 0 : 1;
      break;
    }
    case 'bootstrap': await artisan(['development:database-bootstrap', '--confirm-empty-sqlite', '--no-interaction']); break;
    case 'payment-completion-migrate':
      await artisan(['migrate', '--path=database/migrations/2026_10_03_100400_add_payment_completion_identity.php', '--force', '--no-interaction']);
      break;
    case 'password-reset-phase-b-prepare':
    case 'password-reset-phase-b-completion-check':
    case 'password-reset-phase-b-keep-latest-check':
    case 'password-reset-phase-b-keep-latest-send-once':
    case 'password-reset-phase-b-send-once': {
      const env = localEnvironment();
      await run('php', [path.join(root, 'scripts/development/check-backend.php'), projects.backend], { env });
      await run('php', ['-d', `agendaally.account_email_authority=${projects.backend}`,
        '-d', `agendaally.account_email_approval=${path.join(root, '.local/staging-mvp/account-email-approval.json')}`,
        path.join(root, 'scripts/development/password-reset-phase-b.php'),
        command === 'password-reset-phase-b-prepare' ? 'prepare'
          : command === 'password-reset-phase-b-completion-check' ? 'completion-check'
          : command === 'password-reset-phase-b-keep-latest-check' ? 'keep-latest-check'
          : command === 'password-reset-phase-b-keep-latest-send-once' ? 'keep-latest-send-once'
          : 'send-once'], { cwd: projects.backend, env });
      break;
    }
    case 'account-email-template-local-preview':
    case 'manual-finance-migrate':
    case 'manual-finance-template-provision':
    case 'account-email-system-templates-migrate':
    case 'subscription-email-system-template-migrate':
    case 'account-email-outbox-migrate': {
      const env = localEnvironment();
      const transportOff = ['-d', 'allow_url_fopen=0', '-d',
        'disable_functions=stream_socket_client,fsockopen,pfsockopen,socket_connect,mail,curl_exec,curl_multi_exec,exec,passthru,shell_exec,system,proc_open,popen'];
      await run('php', [...transportOff, path.join(root, 'scripts/development/check-backend.php'), projects.backend], { env });
      if (command === 'manual-finance-template-provision') {
        await run('php', [...transportOff, path.join(root, 'scripts/development/manual-finance-template-provision.php'), projects.backend], { env });
      } else if (command === 'account-email-template-local-preview') {
        await run('php', [...transportOff, path.join(root, 'scripts/development/render-account-template-previews.php'), projects.backend], { env });
      } else await run('php', [...transportOff, 'artisan', 'migrate',
        `--path=database/migrations/${command === 'manual-finance-migrate'
          ? '2026_10_10_010000_add_manual_financial_workflows.php'
          : command === 'account-email-system-templates-migrate'
          ? '2026_10_08_010000_provision_account_email_templates.php'
          : command === 'subscription-email-system-template-migrate'
            ? '2026_10_09_010000_provision_subscription_email_template.php'
            : '2026_10_07_010000_add_selected_email_deliveries.php'}`,
        '--force', '--no-interaction'], { cwd: projects.backend, env });
      break;
    }
    case 'seed-delivery-areas':
      await artisan(['db:seed', '--class=DevelopmentDeliveryAreasSeeder', '--force', '--no-interaction']);
      break;
    case 'seed': {
      const flags = [target, ...args].filter(Boolean);
      if (flags.some(flag => !['--content-only', '--stories-only', '--footer-only'].includes(flag)) ||
          flags.length > 1) {
        throw new Error('The seed command accepts only one of --content-only, --stories-only or --footer-only.');
      }
      await artisan(['development:database-seed', '--no-interaction', ...new Set(flags)]);
      break;
    }
    case 'serve': await serve(); break;
    case 'help':
      log('AgendaAlly development:\n  node scripts/development.mjs install\n  node scripts/development.mjs init [--replit]\n  node scripts/development.mjs origins [--replit]\n  node scripts/development.mjs configure\n  node scripts/development.mjs bootstrap\n  node scripts/development.mjs seed [--content-only | --stories-only | --footer-only]\n  node scripts/development.mjs serve backend|web|admin');
      break;
    default: throw new Error(`Unknown development command: ${command}`);
  }
} catch (error) {
  // Our own messages contain no environment values or credentials.
  console.error(`AgendaAlly development: ${error.message}`);
  process.exitCode = 1;
}