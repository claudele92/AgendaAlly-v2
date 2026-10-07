/**
 * Compile current native client sources in clean disposable snapshots.
 * Public build-only settings, no dotenv files, inherited secrets, or network.
 * Stop preview/browser processes before invoking this memory-intensive check.
 */
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { spawn } from 'node:child_process';
import { fileURLToPath } from 'node:url';
import { createHash } from 'node:crypto';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../..');
const selected = process.argv[2] || 'all';
const retainEvidence = process.argv[3] === '--retain-evidence';
if (process.argv[3] && !retainEvidence) throw new Error('Unknown build qualification option.');
if (!['web', 'admin', 'all'].includes(selected)) {
  throw new Error('Usage: node scripts/development/verify-stage1-builds.mjs [web|admin|all]');
}

const excluded = new Set([
  'node_modules', '.git', '.next', 'build', 'dist', 'coverage',
  '.npmrc', '.yarnrc', '.yarnrc.yml', '.pnp.cjs', '.pnp.loader.mjs',
]);
const logs = path.join(root, '.local/development/logs');
fs.mkdirSync(logs, { recursive: true });
const evidence = retainEvidence
  ? path.join(root, '.local/development/build-qualification', new Date().toISOString().replace(/[:.]/g, '-'))
  : null;
if (evidence) fs.mkdirSync(evidence, { recursive: true });
const results = [];
const temporaryRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'agendaally-stage1-build-'));
const home = path.join(temporaryRoot, 'home');
fs.mkdirSync(home);
// Keep the native clients' repository-relative shared configuration available
// without copying dotenv files or inheriting the workspace environment.
const sharedScripts = path.join(temporaryRoot, 'scripts', 'development');
fs.mkdirSync(sharedScripts, { recursive: true });
fs.copyFileSync(
  path.join(root, 'scripts/development/dev-api-target.cjs'),
  path.join(sharedScripts, 'dev-api-target.cjs')
);
const common = {
  PATH: process.env.PATH || '/usr/bin:/bin',
  HOME: home,
  TMPDIR: temporaryRoot,
  CI: 'true',
  NODE_ENV: 'production',
  NODE_OPTIONS: `--max-old-space-size=4096 --require=${path.join(root, 'scripts/block-original-client-network.cjs')}`,
  NEXT_TELEMETRY_DISABLED: '1',
};
const fixtures = {
  web: {
    NEXT_PUBLIC_APP_ENV: 'staging',
    NEXT_PUBLIC_DEVELOPMENT_MODE: 'false',
    NEXT_PUBLIC_BASE_URL: 'https://api.example.invalid/api/',
    NEXT_PUBLIC_WEBSITE_URL: 'https://store.example.invalid/',
    NEXT_PUBLIC_ADMIN_PANEL_URL: 'https://admin.example.invalid/',
    NEXT_PUBLIC_IMAGE_URL: 'https://api.example.invalid/storage/',
    NEXT_PUBLIC_DEFAULT_LANGUAGE_CODE: 'en',
    NEXT_PUBLIC_DEFAULT_TOKEN_TYPE: 'Bearer',
    NEXT_PUBLIC_CACHE_TIME: '60',
    NEXT_PUBLIC_FIREBASE_ENABLED: 'false',
    NEXT_PUBLIC_MAPS_ENABLED: 'false',
  },
  admin: {
    VITE_APP_ENV: 'staging',
    VITE_DEVELOPMENT_MODE: 'false',
    VITE_API_ORIGIN: 'https://api.example.invalid',
    VITE_STOREFRONT_URL: 'https://store.example.invalid',
    VITE_ADMIN_PANEL_URL: 'https://admin.example.invalid',
    VITE_RECAPTCHA_DISABLED: 'false',
    VITE_RECAPTCHA_SITE_KEY: 'BUILD_ONLY_NO_PROVIDER_KEY',
    VITE_FIREBASE_ENABLED: 'false',
    VITE_MAPS_ENABLED: 'false',
  },
};

function prepare(client) {
  const source = path.join(root, '.migration-backup', client);
  const destination = path.join(temporaryRoot, '.migration-backup', client);
  fs.mkdirSync(path.dirname(destination), { recursive: true });
  if (!fs.existsSync(path.join(source, 'node_modules'))) {
    throw new Error(`Native ${client} dependencies are missing; restore locked dependencies first.`);
  }
  fs.cpSync(source, destination, {
    recursive: true,
    filter(filename) {
      const name = path.basename(filename);
      return !excluded.has(name) && !name.startsWith('.env') &&
        !/\.(?:pem|key|sqlite|db)$/i.test(name);
    },
  });
  fs.symlinkSync(path.join(source, 'node_modules'), path.join(destination, 'node_modules'), 'dir');
  return destination;
}

async function compile(client) {
  const directory = prepare(client);
  const filename = path.join(evidence || logs, `stage1-${client}-production-build.log`);
  const descriptor = fs.openSync(filename, 'w');
  const command = client === 'web'
    ? ['node_modules/next/dist/bin/next', 'build', '--webpack']
    : ['node_modules/vite/bin/vite.js', 'build'];
  console.log(`Building native ${client}: clean source snapshot, offline public fixtures.`);
  const started = new Date().toISOString();
  let exitSignal = null;
  const exitCode = await new Promise((resolve, reject) => {
    const child = spawn(process.execPath, command, {
      cwd: directory,
      env: { ...common, ...fixtures[client] },
      stdio: ['ignore', descriptor, descriptor],
    });
    child.once('error', reject);
    child.once('close', (code, signal) => {
      exitSignal = signal;
      if (signal) console.error(`${client} build interrupted by ${signal}.`);
      resolve(code ?? 1);
    });
  }).finally(() => fs.closeSync(descriptor));
  results.push({ client, command: [process.execPath, ...command], environment: { ...common, ...fixtures[client] },
    started, finished: new Date().toISOString(), exitCode, signal: exitSignal, log: filename });
  if (evidence) {
    fs.writeFileSync(path.join(evidence, 'results.json'), JSON.stringify(results, null, 2) + '\n');
  }
  const output = fs.readFileSync(filename, 'utf8').trim().split('\n');
  console.log(output.slice(-28).join('\n'));
  if (exitCode !== 0) {
    throw new Error(`Native ${client} production compilation failed; see ${path.relative(root, filename)}.`);
  }
  if (evidence) {
    const outputDirectory = path.join(evidence, `${client}-artifacts`);
    fs.cpSync(path.join(directory, client === 'web' ? '.next' : 'build'), outputDirectory, { recursive: true });
    const artifacts = [];
    function inventory(dir) {
      for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
        const file = path.join(dir, entry.name);
        if (entry.isDirectory()) inventory(file);
        else if (entry.isFile()) artifacts.push({ path: path.relative(outputDirectory, file),
          bytes: fs.statSync(file).size, sha256: createHash('sha256').update(fs.readFileSync(file)).digest('hex') });
      }
    }
    inventory(outputDirectory);
    fs.writeFileSync(path.join(evidence, `${client}-artifact-manifest.json`), JSON.stringify(artifacts, null, 2) + '\n');
    if (client === 'web') {
      const args = ['node_modules/typescript/bin/tsc', '--noEmit', '--incremental', 'false', '--pretty', 'false'];
      const staticLog = path.join(evidence, 'web-typescript.log');
      const fd = fs.openSync(staticLog, 'w');
      const staticExit = await new Promise((resolve, reject) => {
        const child = spawn(process.execPath, args, {
          cwd: directory, env: { ...common, ...fixtures.web }, stdio: ['ignore', fd, fd],
        });
        child.once('error', reject);
        child.once('close', (code, signal) => resolve({ code: code ?? 1, signal }));
      }).finally(() => fs.closeSync(fd));
      results.push({ client: 'web-typescript', command: [process.execPath, ...args],
        environment: { ...common, ...fixtures.web }, exitCode: staticExit.code, signal: staticExit.signal, log: staticLog });
      fs.writeFileSync(path.join(evidence, 'results.json'), JSON.stringify(results, null, 2) + '\n');
      if (staticExit.code !== 0) throw new Error(`Native web TypeScript validation failed; see ${staticLog}.`);
      console.log('PASS: whole-web TypeScript validation, including fresh generated Next types.');
    }
  }
  console.log(`PASS: native ${client} production compilation. This is not provider or release verification.`);
}

try {
  for (const client of selected === 'all' ? ['web', 'admin'] : [selected]) {
    await compile(client);
  }
} finally {
  if (evidence) console.log(`Retained build evidence: ${path.relative(root, evidence)}`);
  fs.rmSync(temporaryRoot, { recursive: true, force: true });
}