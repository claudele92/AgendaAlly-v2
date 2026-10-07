import fs from 'node:fs';
import path from 'node:path';
import { parseEnv } from 'node:util';
import { isIP } from 'node:net';
import { fileURLToPath } from 'node:url';

export const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
export const projects = Object.freeze(Object.fromEntries(
  ['backend', 'web', 'admin'].map(name => [name, path.join(root, '.migration-backup', name)]),
));

export function dotenv(directory) {
  const filename = path.join(directory, '.env');
  if (!fs.existsSync(filename)) throw new Error(`Missing ${path.relative(root, filename)}. Run the init command first.`);
  return parseEnv(fs.readFileSync(filename, 'utf8'));
}

export function assertLocal(environment, inherited = process.env) {
  const selected = inherited.APP_ENV || environment.APP_ENV;
  if (!['local', 'testing'].includes(selected)) {
    throw new Error('Development tooling requires APP_ENV=local or testing; staging/production are never modified.');
  }
  if (environment.DEVELOPMENT_MODE !== 'true') {
    throw new Error('Development tooling requires explicit DEVELOPMENT_MODE=true.');
  }
  if (inherited.DATABASE_URL || inherited.DB_URL) {
    throw new Error('Unset inherited DATABASE_URL/DB_URL before using development tooling. No ambient database is selected.');
  }
}

export function validatePort(value) {
  const port = Number(value);
  if (!Number.isInteger(port) || port < 1 || port > 65535) throw new Error('Port must be between 1 and 65535.');
  return String(port);
}

export function assertDevelopmentDatabaseConfiguration(config, basePath) {
  if (config.DB_CONNECTION !== 'sqlite' || config.DB_URL || config.DATABASE_URL) {
    throw new Error('Development tooling requires an explicitly selected SQLite file, never a database URL/server.');
  }
  const directory = path.join(basePath, 'database', 'development');
  const filename = path.resolve(basePath, config.DB_DATABASE || '');
  if (!fs.existsSync(directory) || path.dirname(filename) !== directory ||
      !filename.endsWith('.sqlite') || !fs.existsSync(path.dirname(filename)) ||
      fs.realpathSync(directory) !== directory ||
      (fs.existsSync(filename) && fs.lstatSync(filename).isSymbolicLink())) {
    throw new Error('Development database must be a nonsymlink .sqlite file directly inside backend/database/development/.');
  }
}

export function assertNativeDevelopmentPaths(config, inherited, basePath) {
  for (const key of ['APP_BASE_PATH', 'APP_CONFIG_CACHE', 'APP_ROUTES_CACHE', 'APP_SERVICES_CACHE', 'APP_PACKAGES_CACHE']) {
    if (config[key] || inherited[key]) throw new Error(`Development tooling refuses a custom ${key}; use the original application's native paths.`);
  }
  if (fs.existsSync(path.join(basePath, 'bootstrap/cache/config.php'))) {
    throw new Error('A cached Laravel configuration can override .env. Remove the generated local bootstrap/cache/config.php before development setup.');
  }
}

export function configureOrigins(directory, replacements) {
  const allowed = new Set([
    'APP_URL', 'LARAVEL_BACKEND_URL', 'CUSTOMER_STOREFRONT_URL', 'CUSTOMER_URL', 'FRONT_URL',
    'VENDOR_ADMIN_URL', 'ADMIN_URL', 'CORS_ALLOWED_ORIGINS',
    'NEXT_PUBLIC_BASE_URL', 'NEXT_PUBLIC_WEBSITE_URL', 'NEXT_PUBLIC_ADMIN_PANEL_URL',
    'VITE_BASE_URL', 'VITE_STOREFRONT_URL', 'VITE_WEBSITE_URL', 'VITE_ADMIN_PANEL_URL', 'VITE_ADMIN_URL',
  ]);
  const target = path.join(directory, '.env');
  if (fs.lstatSync(target).isSymbolicLink()) throw new Error('Refusing to update a symlinked environment file.');
  let text = fs.readFileSync(target, 'utf8');
  for (const [key, value] of Object.entries(replacements)) {
    if (!allowed.has(key) || /[\r\n]/.test(String(value))) throw new Error('Only allowlisted public application URLs may be updated.');
    if (key === 'CORS_ALLOWED_ORIGINS') {
      for (const origin of String(value).split(',')) validateOrigin(origin);
    } else if (key === 'NEXT_PUBLIC_BASE_URL') {
      const url = new URL(value);
      validateOrigin(url.origin);
      if (url.username || url.password || url.pathname !== '/api/' || url.search || url.hash) throw new Error('Customer API URL must be an origin followed by /api/.');
    } else {
      validateOrigin(value);
    }
    const matcher = new RegExp(`^${key}=.*$`, 'm');
    const line = `${key}=${JSON.stringify(String(value))}`;
    text = matcher.test(text) ? text.replace(matcher, () => line) : `${text.trimEnd()}\n${line}\n`;
  }
  // Callers provide a fixed allowlist of public application URLs/CORS origins;
  // existing opaque credentials remain unchanged and are never printed.
  fs.writeFileSync(target, text, { mode: 0o600 });
}

export function validateOrigin(value) {
  const url = new URL(value);
  if (!['http:', 'https:'].includes(url.protocol) || url.username || url.password || url.pathname !== '/' || url.search || url.hash) {
    throw new Error('An application origin must be an HTTP(S) origin without credentials, paths or query parameters.');
  }
  return url.origin;
}

export function replitOrigins(host) {
  if (typeof host !== 'string' || isIP(host) || host.length > 253 || !host.includes('.') ||
      !host.split('.').every(label => /^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/i.test(label))) {
    throw new Error('--replit requires a plain REPLIT_DEV_DOMAIN hostname.');
  }
  return { backend: `https://${host}:8000`, web: `https://${host}:3002`, admin: `https://${host}:3003` };
}

/** Exact browser origins for owned local development, including loopback views. */
export function developmentCorsOrigins(selected) {
  return [...new Set([
    validateOrigin(selected.web),
    validateOrigin(selected.admin),
    'http://localhost:3002',
    'http://localhost:3003',
    'http://127.0.0.1:3002',
    'http://127.0.0.1:3003',
  ])].join(',');
}

/** Create missing configuration only. Never read, print or overwrite existing credentials. */
export function initializeFile(directory, replacements) {
  const target = path.join(directory, '.env');
  if (fs.existsSync(target)) return false;
  const example = path.join(directory, '.env.example');
  let text = fs.readFileSync(example, 'utf8');
  for (const [key, value] of Object.entries(replacements)) {
    if (!/^[A-Z][A-Z0-9_]*$/.test(key) || /[\r\n]/.test(String(value))) throw new Error('Invalid environment template replacement.');
    const line = `${key}=${JSON.stringify(String(value))}`;
    const matcher = new RegExp(`^${key}=.*$`, 'm');
    text = matcher.test(text) ? text.replace(matcher, () => line) : `${text.trimEnd()}\n${line}\n`;
  }
  fs.writeFileSync(target, text, { flag: 'wx', mode: 0o600 });
  return true;
}