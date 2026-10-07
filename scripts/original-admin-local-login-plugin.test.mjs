import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { isolatedLocalLoginPlugin } from './original-admin-local-login-plugin.mjs';

const admin = fileURLToPath(new URL('../.local/agendaally-preview/admin', import.meta.url));
const login = `${admin}/src/views/login/index.jsx`;
const source = readFileSync(login, 'utf8');
const logout = `${admin}/src/context/path-logout.jsx`;
const logoutSource = readFileSync(logout, 'utf8');

test('overlay is development-only, fails closed, and leaves source untouched', () => {
  const saved = { ...process.env };
  try {
    process.env.REPLIT_DEV_DOMAIN = 'preview.replit.dev';
    process.env.VITE_BASE_URL = 'https://preview.replit.dev:8000';
    process.env.AGENDAALLY_PREVIEW_LOCAL_LOGIN = 'approved';
    process.env.AGENDAALLY_PREVIEW_INSTALLER_REDIRECT = 'approved';
    const config = { command: 'serve', mode: 'development', root: admin };
    const plugin = isolatedLocalLoginPlugin();
    assert.throws(() => plugin.transform(source, login));
    plugin.configResolved(config);
    assert.equal(plugin.transform('unchanged', `${admin}/src/other.jsx`), null);
    const output = plugin.transform(source, login).code;
    assert.match(output, /Isolated local preview: reCAPTCHA is disabled/);
    assert.match(output, /disabled=\{false\}/);
    assert.doesNotMatch(output, /import Recaptcha/);
    assert.equal(readFileSync(login, 'utf8'), source);
    assert.throws(() => plugin.transform(`${source}\n`, login));
    const logoutOutput = plugin.transform(logoutSource, logout).code;
    assert.equal(readFileSync(logout, 'utf8'), logoutSource);
    assert.throws(() => plugin.transform(`${logoutSource}\n`, logout));
    const catchBody = logoutOutput.match(/\.catch\(\(error\) => \{([\s\S]*?)\n      \}\);/)[1];
    const handleError = new Function('error', 'navigate', catchBody);
    const redirects = [];
    const navigate = (target) => redirects.push(target);
    handleError({ config: { url: 'install/init/check' }, response: { status: 404, data: { message: 'Installer endpoints are disabled.' } } }, navigate);
    handleError({ config: { url: 'install/init/check' }, response: { status: 404, data: { status: false, statusCode: 'ERROR_404', message: "Item's not found." } } }, navigate);
    assert.deepEqual(redirects, []);
    for (const error of [
      new Error('Network failure'),
      { response: { status: 404, data: { message: 'Not found' } } },
      { response: { status: 500, data: { message: 'Installer endpoints are disabled.' } } },
      { response: { status: 403 } },
      { config: { url: 'other/endpoint' }, response: { status: 404, data: { status: false, statusCode: 'ERROR_404' } } },
      { config: { url: 'install/init/check' }, response: { status: 404, data: { message: 'Unexpected response' } } },
    ]) {
      handleError(error, navigate);
    }
    assert.deepEqual(redirects, Array(6).fill('/welcome'));
    assert.throws(() => isolatedLocalLoginPlugin().configResolved({ ...config, command: 'build' }));
    assert.throws(() => isolatedLocalLoginPlugin().configResolved({ ...config, mode: 'production' }));
    assert.throws(() => isolatedLocalLoginPlugin().configResolved({ ...config, root: '/' }));
    process.env.VITE_BASE_URL = 'https://api.agendaally.com';
    assert.throws(() => isolatedLocalLoginPlugin().configResolved(config));
    process.env.VITE_BASE_URL = 'https://preview.replit.dev:8000';
    delete process.env.AGENDAALLY_PREVIEW_LOCAL_LOGIN;
    assert.throws(() => isolatedLocalLoginPlugin().configResolved(config));
    process.env.AGENDAALLY_PREVIEW_LOCAL_LOGIN = 'approved';
    delete process.env.AGENDAALLY_PREVIEW_INSTALLER_REDIRECT;
    assert.throws(() => isolatedLocalLoginPlugin().configResolved(config));
  } finally {
    process.env = saved;
  }
});