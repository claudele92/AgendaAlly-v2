#!/usr/bin/env node
import { spawn } from 'node:child_process';
import { chmodSync, existsSync, lstatSync, mkdirSync, mkdtempSync, rmSync, symlinkSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

export const mobileRoot = fileURLToPath(new URL('../.migration-backup/customer_app/', import.meta.url));
export const defineNames = [
  'APP_NAME', 'APP_ID', 'BASE_URL', 'WEB_URL', 'ADMIN_URL', 'DEEP_LINK_URL',
  'WS_BASE_URL', 'WS_SECRET', 'FIREBASE_API_KEY', 'ROUTING_API', 'ROUTING_KEY',
  'PAYFAST_PASSPHRASE', 'PAYFAST_MERCHANT_ID', 'PAYFAST_MERCHANT_KEY',
  'FACEBOOK_APP_ID', 'FACEBOOK_CLIENT_TOKEN',
];

export function configuration(args, env) {
  const [environment, target, maps, ...extra] = args;
  if (!['development', 'production'].includes(environment) ||
      !['apk', 'appbundle', 'ios'].includes(target) ||
      !['--maps=disabled', '--maps=enabled'].includes(maps)) {
    throw new Error('Usage: node scripts/build-mobile.mjs development|production apk|appbundle|ios --maps=disabled|enabled [build options]');
  }
  // Source/CLI credential inputs and verbose output are deliberately forbidden.
  if (extra.some(a => !a.startsWith('--') || /dart-define|verbose|config-only/.test(a))) {
    throw new Error('Only long-form build options are allowed; Dart define files, CLI defines and verbose output are not allowed.');
  }
  const platform = target === 'ios' ? 'IOS' : 'ANDROID';
  const keyName = `MOBILE_MAPS_${environment.toUpperCase()}_${platform}_KEY`;
  const key = maps === '--maps=enabled' ? env[keyName] : '';
  if (maps === '--maps=enabled' && (!key || !/^[A-Za-z0-9_-]+$/.test(key))) {
    throw new Error(`A valid environment value for ${keyName} is required; its value will not be displayed.`);
  }
  const defines = Object.fromEntries(defineNames.filter(name => env[name] !== undefined)
    .map(name => [name, env[name]]));
  defines.GOOGLE_MAPS_API_KEY = key;
  if (Object.values(defines).some(value => /[\r\n\0]/.test(value))) {
    throw new Error('Native build environment values must be single-line.');
  }
  if (target === 'ios' && Object.values(defines).some(value => value.includes('$('))) {
    throw new Error('Native iOS environment values cannot contain build-setting expressions.');
  }
  const childEnv = { ...env, AGENDAALLY_NATIVE_MAPS_KEY: key,
    FLUTTER_SUPPRESS_ANALYTICS: 'true', DART_SUPPRESS_ANALYTICS: 'true' };
  for (const name of Object.keys(childEnv)) {
    if (/^MOBILE_MAPS_/.test(name) || name === 'GOOGLE_MAPS_API_KEY' || name === 'DART_DEFINES') {
      delete childEnv[name];
    }
  }
  return { target, extra, defines, childEnv };
}

export function xcconfig(defines) {
  // xcconfig treats // as a comment, including inside a URL.
  return Object.entries(defines).map(([name, value]) =>
    `${name} = ${value.replaceAll('//', '/$()/')}`).join('\n') + '\n';
}

export function redactor(defines) {
  const secrets = [...new Set(Object.entries(defines).flatMap(([name, value]) => [
    value, Buffer.from(`${name}=${value}`).toString('base64'),
  ]).filter(Boolean))].sort((a, b) => b.length - a.length);
  return line => secrets.reduce((text, value) => text.replaceAll(value, '[REDACTED]'), line);
}

export async function build(args, env = process.env, root = mobileRoot, executable = 'flutter') {
  const config = configuration(args, env);
  const nativeDir = resolve(root, 'ios/Flutter');
  const generated = resolve(nativeDir, 'Native-Build.generated.xcconfig');
  const lock = resolve(nativeDir, '.native-build-lock');
  // All platforms lock, so Android cannot race an iOS generated configuration.
  try { mkdirSync(lock); } catch {
    throw new Error('Native build is already active or has a stale lock; do not run concurrent builds in one checkout.');
  }
  let temporary;
  let ownsGenerated = false;
  let child;
  const cleanup = () => {
    if (ownsGenerated) rmSync(generated, { force: true });
    if (temporary) rmSync(temporary, { recursive: true, force: true });
    rmSync(lock, { recursive: true, force: true });
  };
  const stop = () => { child?.kill('SIGTERM'); };
  try {
    if (existsSync(generated) || (() => { try { lstatSync(generated); return true; } catch { return false; } })()) {
      throw new Error('Refusing existing native generated configuration; remove stale build artifacts without inspecting their values.');
    }
    temporary = mkdtempSync(resolve(tmpdir(), 'agendaally-native-build-'));
    chmodSync(temporary, 0o700);
    const definitions = resolve(temporary, 'defines.json');
    writeFileSync(definitions, JSON.stringify(config.defines), { mode: 0o600 });
    if (config.target === 'ios') {
      const settings = resolve(temporary, 'native.xcconfig');
      writeFileSync(settings, xcconfig(config.defines), { mode: 0o600 });
      symlinkSync(settings, generated);
      ownsGenerated = true;
    }
    const redact = redactor(config.defines);
    // Buffer complete lines so a value split across output chunks is redacted.
    const filtered = (stream, destination) => {
      let pending = '';
      stream.setEncoding('utf8');
      stream.on('data', chunk => {
        pending += chunk;
        let end;
        while ((end = pending.indexOf('\n')) !== -1) {
          destination.write(redact(pending.slice(0, end + 1)));
          pending = pending.slice(end + 1);
        }
      });
      stream.on('end', () => { if (pending) destination.write(redact(pending)); });
    };
    child = spawn(executable, ['build', config.target, `--dart-define-from-file=${definitions}`, ...config.extra],
      { cwd: root, env: config.childEnv, stdio: ['ignore', 'pipe', 'pipe'] });
    filtered(child.stdout, process.stdout);
    filtered(child.stderr, process.stderr);
    process.on('SIGINT', stop);
    process.on('SIGTERM', stop);
    return await new Promise((done, reject) => {
      child.once('error', () => reject(new Error('Unable to start Flutter; check the native toolchain.')));
      child.once('close', (code) => done(code ?? 1));
    });
  } finally {
    process.off('SIGINT', stop);
    process.off('SIGTERM', stop);
    cleanup();
  }
}

if (process.argv[1] && resolve(process.argv[1]) === fileURLToPath(import.meta.url)) {
  try { process.exitCode = await build(process.argv.slice(2)); }
  catch (error) { console.error(error.message); process.exitCode = 1; }
}