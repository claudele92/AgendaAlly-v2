import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import {
  assertLocal, initializeFile, validateOrigin, validatePort, replitOrigins,
  assertDevelopmentDatabaseConfiguration, assertNativeDevelopmentPaths, configureOrigins, developmentCorsOrigins,
} from './config.mjs';

test('development operations fail closed outside explicitly enabled local/testing', () => {
  assert.doesNotThrow(() => assertLocal({ APP_ENV: 'local', DEVELOPMENT_MODE: 'true' }, {}));
  assert.doesNotThrow(() => assertLocal({ APP_ENV: 'testing', DEVELOPMENT_MODE: 'true' }, {}));
  for (const APP_ENV of ['production', 'staging', '', undefined]) {
    assert.throws(() => assertLocal({ APP_ENV, DEVELOPMENT_MODE: 'true' }, {}));
  }
  assert.throws(() => assertLocal({ APP_ENV: 'local', DEVELOPMENT_MODE: 'false' }, {}));
  assert.throws(() => assertLocal({ APP_ENV: 'local', DEVELOPMENT_MODE: 'true' }, { APP_ENV: 'production' }));
  assert.throws(() => assertLocal({ APP_ENV: 'local', DEVELOPMENT_MODE: 'true' }, { DATABASE_URL: 'ambient-database' }));
});

test('owned development CORS allows exact public and loopback UI origins only', () => {
  const selected = replitOrigins('preview.example.invalid');
  const values = developmentCorsOrigins(selected).split(',');
  assert.deepEqual(values, [
    selected.web, selected.admin,
    'http://localhost:3002', 'http://localhost:3003',
    'http://127.0.0.1:3002', 'http://127.0.0.1:3003',
  ]);
  assert.equal(values.includes(selected.backend), false);
  assert.equal(values.includes('*'), false);
  assert.equal(values.includes('https://unrelated.example.invalid'), false);
  assert.equal(developmentCorsOrigins({
    web: 'http://localhost:3002', admin: 'http://localhost:3003',
  }).split(',').length, 4);
  assert.throws(() => developmentCorsOrigins({
    web: 'https://user:password@example.invalid', admin: selected.admin,
  }));
  assert.throws(() => developmentCorsOrigins({ web: '*', admin: selected.admin }));
});

test('dotenv init preserves an existing file and safely creates a private configuration', () => {
  const temporary = fs.mkdtempSync(path.join(os.tmpdir(), 'agendaally-env-test-'));
  try {
    fs.writeFileSync(path.join(temporary, '.env.example'), 'APP_ENV=production\nAPP_KEY=\n');
    assert.equal(initializeFile(temporary, { APP_ENV: 'local', DEVELOPMENT_MODE: 'true' }), true);
    assert.match(fs.readFileSync(path.join(temporary, '.env'), 'utf8'), /APP_ENV="local"/);
    assert.equal(fs.statSync(path.join(temporary, '.env')).mode & 0o777, 0o600);
    fs.writeFileSync(path.join(temporary, '.env'), 'keep-existing-opaque-values');
    assert.equal(initializeFile(temporary, { APP_ENV: 'local' }), false);
    assert.equal(fs.readFileSync(path.join(temporary, '.env'), 'utf8'), 'keep-existing-opaque-values');
  } finally {
    fs.rmSync(temporary, { recursive: true, force: true });
  }
});

test('application URL and port validation rejects credentials and unexpected paths', () => {
  assert.equal(validateOrigin('http://localhost:8000'), 'http://localhost:8000');
  for (const origin of ['file:///tmp/app', 'https://user:pass@example.invalid', 'https://example.invalid/api']) {
    assert.throws(() => validateOrigin(origin));
  }
  assert.equal(validatePort('3002'), '3002');
  for (const port of ['0', '65536', 'x', '1.5']) assert.throws(() => validatePort(port));
  assert.equal(replitOrigins('example.replit.dev').web, 'https://example.replit.dev:3002');
  for (const host of ['https://example.replit.dev', '../escape', 'host:8000', '.invalid']) {
    assert.throws(() => replitOrigins(host));
  }
});

test('SQLite selection never accepts a server, ambient URL, external file or symlink', () => {
  const base = fs.mkdtempSync(path.join(os.tmpdir(), 'agendaally-database-path-'));
  const directory = path.join(base, 'database/development');
  fs.mkdirSync(directory, { recursive: true });
  const valid = { DB_CONNECTION: 'sqlite', DB_DATABASE: path.join(directory, 'demo.sqlite') };
  try {
    assert.doesNotThrow(() => assertDevelopmentDatabaseConfiguration(valid, base));
    for (const override of [
      { DB_CONNECTION: 'mysql' }, { DATABASE_URL: 'ambient-url' }, { DB_URL: 'explicit-url' },
      { DB_DATABASE: ':memory:' }, { DB_DATABASE: path.join(base, 'foreign.sqlite') },
    ]) assert.throws(() => assertDevelopmentDatabaseConfiguration({ ...valid, ...override }, base));
    fs.writeFileSync(path.join(base, 'foreign.sqlite'), '');
    fs.symlinkSync(path.join(base, 'foreign.sqlite'), valid.DB_DATABASE);
    assert.throws(() => assertDevelopmentDatabaseConfiguration(valid, base));
  } finally {
    fs.rmSync(base, { recursive: true, force: true });
  }
});

test('public-origin updates preserve opaque private fields and refuse unrelated keys', () => {
  const base = fs.mkdtempSync(path.join(os.tmpdir(), 'agendaally-origins-'));
  try {
    fs.writeFileSync(path.join(base, '.env'), 'APP_KEY=opaque-test-fixture\nAPP_URL=http://localhost:8000\n');
    configureOrigins(base, { APP_URL: 'https://api.example.invalid' });
    assert.match(fs.readFileSync(path.join(base, '.env'), 'utf8'), /^APP_KEY=opaque-test-fixture$/m);
    assert.throws(() => configureOrigins(base, { APP_KEY: 'not-allowed' }));
    assert.throws(() => configureOrigins(base, { APP_URL: 'https://user:password@example.invalid' }));
    assert.throws(() => configureOrigins(base, { NEXT_PUBLIC_BASE_URL: 'https://api.example.invalid/not-api/' }));
    assert.doesNotThrow(() => assertNativeDevelopmentPaths({}, {}, base));
    assert.throws(() => assertNativeDevelopmentPaths({}, { APP_CONFIG_CACHE: '/outside/config.php' }, base));
    fs.mkdirSync(path.join(base, 'bootstrap/cache'), { recursive: true });
    fs.writeFileSync(path.join(base, 'bootstrap/cache/config.php'), '<?php return [];');
    assert.throws(() => assertNativeDevelopmentPaths({}, {}, base));
  } finally {
    fs.rmSync(base, { recursive: true, force: true });
  }
});