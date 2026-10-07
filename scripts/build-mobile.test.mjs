import test from 'node:test';
import assert from 'node:assert/strict';
import { chmodSync, existsSync, mkdirSync, mkdtempSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { resolve } from 'node:path';
import { spawnSync } from 'node:child_process';
import { configuration, mobileRoot, redactor, xcconfig } from './build-mobile.mjs';

// Not Google credentials. Never inherit the workspace's secrets in child tests.
const synthetic = {
  MOBILE_MAPS_DEVELOPMENT_ANDROID_KEY: 'SYNTHETIC_ANDROID_DEVELOPMENT',
  MOBILE_MAPS_PRODUCTION_ANDROID_KEY: 'SYNTHETIC_ANDROID_PRODUCTION',
  MOBILE_MAPS_DEVELOPMENT_IOS_KEY: 'SYNTHETIC_IOS_DEVELOPMENT',
  MOBILE_MAPS_PRODUCTION_IOS_KEY: 'SYNTHETIC_IOS_PRODUCTION',
  GOOGLE_MAPS_API_KEY: 'UNSCOPED_VALUE_MUST_NOT_BE_USED',
  APP_NAME: 'Synthetic app',
  BASE_URL: 'https://example.invalid/api',
  ROUTING_KEY: 'SYNTHETIC_ROUTING_ONLY',
};

test('scope selection keeps platform/environment keys separate and disabled builds empty', () => {
  for (const environment of ['development', 'production']) {
    for (const target of ['apk', 'appbundle', 'ios']) {
      const platform = target === 'ios' ? 'IOS' : 'ANDROID';
      const config = configuration([environment, target, '--maps=enabled'], synthetic);
      assert.equal(config.defines.GOOGLE_MAPS_API_KEY, synthetic[`MOBILE_MAPS_${environment.toUpperCase()}_${platform}_KEY`]);
      assert.equal(config.childEnv.AGENDAALLY_NATIVE_MAPS_KEY, config.defines.GOOGLE_MAPS_API_KEY);
      assert.equal(Object.keys(config.childEnv).some(name => name.startsWith('MOBILE_MAPS_')), false);
      assert.equal('GOOGLE_MAPS_API_KEY' in config.childEnv, false);
      assert.equal(config.defines.BASE_URL, synthetic.BASE_URL);
      assert.equal(configuration([environment, target, '--maps=disabled'], synthetic).defines.GOOGLE_MAPS_API_KEY, '');
    }
  }
});

test('missing scope, overrides, multiline input and verbose output fail without values', () => {
  const invalid = [
    [['staging', 'ios', '--maps=enabled'], synthetic],
    [['development', 'ios', '--maps=enabled'], {}],
    [['production', 'apk', '--maps=enabled'], { MOBILE_MAPS_DEVELOPMENT_ANDROID_KEY: synthetic.MOBILE_MAPS_DEVELOPMENT_ANDROID_KEY }],
    [['development', 'ios', '--maps=enabled', '--dart-define=GOOGLE_MAPS_API_KEY=CLI'], synthetic],
    [['development', 'apk', '--maps=enabled', '--dart-define-from-file=source.json'], synthetic],
    [['development', 'apk', '--maps=enabled', '--verbose'], synthetic],
    [['development', 'apk', '--maps=enabled', '-v'], synthetic],
    [['development', 'apk', '--maps=disabled'], { APP_NAME: 'line\nbreak' }],
    [['development', 'ios', '--maps=disabled'], { APP_NAME: '$(OVERRIDE)' }],
  ];
  for (const [args, env] of invalid) assert.throws(() => configuration(args, env));
});

test('xcconfig preserves URLs and output filtering hides raw and encoded definitions', () => {
  const config = configuration(['development', 'ios', '--maps=enabled'], synthetic);
  assert.ok(xcconfig(config.defines).includes('BASE_URL = https:/$()/example.invalid/api'));
  const raw = config.defines.GOOGLE_MAPS_API_KEY;
  const encoded = Buffer.from(`GOOGLE_MAPS_API_KEY=${raw}`).toString('base64');
  const output = redactor(config.defines)(`tool: ${raw}; encoded: ${encoded}`);
  assert.ok(!output.includes(raw));
  assert.ok(!output.includes(encoded));
  assert.ok(output.includes('[REDACTED]'));
});

test('build lifecycle uses private transient inputs, removes them on success/failure and refuses stale config', () => {
  const directory = mkdtempSync(resolve(tmpdir(), 'maps-build-test-'));
  try {
    const root = resolve(directory, 'app');
    const native = resolve(root, 'ios/Flutter');
    mkdirSync(native, { recursive: true });
    const executable = resolve(directory, 'fake-flutter.mjs');
    const wrapperUrl = new URL('./build-mobile.mjs', import.meta.url).href;
    const runner = resolve(directory, 'runner.mjs');
    const result = resolve(directory, 'result.json');
    writeFileSync(executable, `#!/usr/bin/env node
import { readFileSync, writeFileSync, statSync, lstatSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
const path = process.argv.find(arg => arg.startsWith('--dart-define-from-file=')).split('=')[1];
const defines = JSON.parse(readFileSync(path, 'utf8'));
const generated = resolve(process.cwd(), 'ios/Flutter/Native-Build.generated.xcconfig');
const target = process.argv[3];
writeFileSync(process.env.TEST_RESULT, JSON.stringify({
 path, defines, key: process.env.AGENDAALLY_NATIVE_MAPS_KEY,
 fileMode: statSync(path).mode & 511, directoryMode: statSync(dirname(path)).mode & 511,
 generated: target === 'ios' ? readFileSync(generated, 'utf8') : null,
 symlink: target === 'ios' ? lstatSync(generated).isSymbolicLink() : false,
 args: process.argv.slice(2)
}));
const key = defines.GOOGLE_MAPS_API_KEY;
process.stdout.write(key.slice(0, 10));
setTimeout(() => {
 process.stdout.write(key.slice(10) + '\\n' + Buffer.from('GOOGLE_MAPS_API_KEY=' + key).toString('base64') + '\\n');
 process.exit(process.env.TEST_FAIL === 'yes' ? 7 : 0);
}, 10);
`, { mode: 0o700 });
    chmodSync(executable, 0o700);
    writeFileSync(runner, `import { build } from ${JSON.stringify(wrapperUrl)};
try { process.exitCode = await build(process.argv.slice(2), process.env, ${JSON.stringify(root)}, ${JSON.stringify(executable)}); }
catch (error) { console.error(error.message); process.exitCode = 1; }`);
    for (const [target, fail] of [['ios', false], ['apk', true]]) {
      const run = spawnSync(process.execPath, [runner, 'development', target, '--maps=enabled', '--no-pub'],
        { env: { PATH: process.env.PATH, ...synthetic, TEST_RESULT: result, TEST_FAIL: fail ? 'yes' : 'no' }, encoding: 'utf8' });
      assert.equal(run.status, fail ? 7 : 0);
      const data = JSON.parse(readFileSync(result, 'utf8'));
      assert.equal(data.fileMode, 0o600);
      assert.equal(data.directoryMode, 0o700);
      assert.equal(data.key, data.defines.GOOGLE_MAPS_API_KEY);
      assert.equal(data.symlink, target === 'ios');
      assert.ok(data.args.includes('--no-pub'));
      assert.ok(!data.args.some(arg => arg.includes(data.key)));
      assert.ok(!run.stdout.includes(data.key));
      assert.ok(!run.stdout.includes(Buffer.from(`GOOGLE_MAPS_API_KEY=${data.key}`).toString('base64')));
      assert.equal(existsSync(data.path), false);
      assert.equal(existsSync(resolve(native, 'Native-Build.generated.xcconfig')), false);
      assert.equal(existsSync(resolve(native, '.native-build-lock')), false);
    }
    const stale = resolve(native, 'Native-Build.generated.xcconfig');
    writeFileSync(stale, 'DO_NOT_OVERWRITE = synthetic');
    const staleRun = spawnSync(process.execPath, [runner, 'development', 'ios', '--maps=disabled'],
      { env: { PATH: process.env.PATH }, encoding: 'utf8' });
    assert.equal(staleRun.status, 1);
    assert.equal(readFileSync(stale, 'utf8'), 'DO_NOT_OVERWRITE = synthetic');
    assert.equal(existsSync(resolve(native, '.native-build-lock')), false);
  } finally {
    rmSync(directory, { recursive: true, force: true });
  }
});

test('native source wiring is keyless and preserves SDK entry points', () => {
  const source = path => readFileSync(resolve(mobileRoot, path), 'utf8');
  assert.equal(existsSync(resolve(mobileRoot, 'ios/Flutter/Dart-Defines.xcconfig')), false);
  for (const path of ['ios/Flutter/Debug.xcconfig', 'ios/Flutter/Release.xcconfig']) {
    const text = source(path);
    assert.ok(text.includes('GOOGLE_MAPS_API_KEY =\n'));
    assert.ok(text.includes('#include? "Native-Build.generated.xcconfig"'));
    assert.ok(!text.includes('Dart-Defines.xcconfig'));
  }
  assert.ok(source('android/app/build.gradle').includes("System.getenv('AGENDAALLY_NATIVE_MAPS_KEY')"));
  assert.ok(source('android/app/src/main/AndroidManifest.xml').includes('@string/google_maps_api_key'));
  assert.ok(source('ios/Runner/Info.plist').includes('$(GOOGLE_MAPS_API_KEY)'));
  assert.ok(source('ios/Runner/AppDelegate.swift').includes('GMSServices.provideAPIKey(apiKey)'));
  const project = source('ios/Runner.xcodeproj/project.pbxproj');
  assert.ok(project.includes('inject_native_defines.sh'));
  assert.ok(!project.includes('echo \\"Injecting'));
  assert.ok(source('lib/app_constants.dart').includes("'GOOGLE_MAPS_API_KEY'"));
});

test('iOS injection skips Maps and Flutter internals without printing configuration', () => {
  const definitions = [
    'GOOGLE_MAPS_API_KEY=SYNTHETIC_NOT_A_PROVIDER_KEY',
    'FLUTTER_BUILD_MODE=debug',
    'flutter.inspector.structuredErrors=true',
  ].map(value => Buffer.from(value).toString('base64')).join(',');
  const run = spawnSync('/bin/bash', [resolve(mobileRoot, 'ios/Flutter/inject_native_defines.sh')],
    { env: { PATH: process.env.PATH, DART_DEFINES: definitions }, encoding: 'utf8' });
  assert.equal(run.status, 0);
  assert.equal(run.stdout, '');
  assert.equal(run.stderr, '');
});

test('Dart compile-time Maps input compiles and runs with a synthetic value only', () => {
  const directory = mkdtempSync(resolve(tmpdir(), 'maps-dart-compile-test-'));
  try {
    const source = resolve(directory, 'verify.dart');
    const binary = resolve(directory, 'verify');
    writeFileSync(source, `import 'dart:io';
const value = String.fromEnvironment('GOOGLE_MAPS_API_KEY');
void main() {
  if (value != 'SYNTHETIC_COMPILE_ONLY') exit(1);
  stdout.writeln('PASS synthetic compile-time Maps input');
}`);
    const env = { PATH: process.env.PATH, HOME: directory,
      DART_SUPPRESS_ANALYTICS: 'true', FLUTTER_SUPPRESS_ANALYTICS: 'true' };
    const compile = spawnSync('dart', ['compile', 'exe', '-DGOOGLE_MAPS_API_KEY=SYNTHETIC_COMPILE_ONLY',
      '--output', binary, source], { env, encoding: 'utf8' });
    assert.equal(compile.status, 0, 'Synthetic Dart compilation must succeed; this is not a native app build.');
    const run = spawnSync(binary, [], { env, encoding: 'utf8' });
    assert.equal(run.status, 0);
    assert.equal(run.stdout.trim(), 'PASS synthetic compile-time Maps input');
  } finally {
    rmSync(directory, { recursive: true, force: true });
  }
});