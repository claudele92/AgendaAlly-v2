import test from 'node:test';
import assert from 'node:assert/strict';
import { fileURLToPath } from 'node:url';
import { isolatedAdminFileAccessPlugin } from './original-admin-file-isolation-plugin.mjs';

const root = fileURLToPath(new URL('..', import.meta.url)).replace(/\/$/, '');
const admin = `${root}/.local/agendaally-preview/admin`;
const backend = `${root}/.local/agendaally-preview/backend`;

test('filesystem gate blocks sibling runtimes and traversal even for nonexistent files', () => {
  let middleware;
  isolatedAdminFileAccessPlugin().configureServer({ middlewares: { use(fn) { middleware = fn; } } });
  const request = (url) => {
    let continued = false;
    const response = { statusCode: 200, setHeader() {}, end() {} };
    middleware({ url }, response, () => { continued = true; });
    return { continued, status: response.statusCode };
  };
  for (const url of [
    `/@fs${backend}/.preview-credentials`,
    `/@fs/${backend}/.preview-app-key?raw`,
    `/@fs${backend}/storage/nonexistent.sqlite-wal`,
    `/@fs${root}/.replit?raw`,
    `/@fs${root}/.agents/memory/MEMORY.md`,
    `/@fs${admin}/../backend/.preview-credentials`,
    `/@fs${admin}/%2e%2e/backend/.preview-credentials`,
    `/@fs${admin}/%252e%252e/backend/.preview-credentials`,
    `/@fs${admin}/%5c..%5cbackend/.preview-credentials`,
    '/@fs/%zz',
  ]) {
    assert.deepEqual(request(url), { continued: false, status: 403 }, url);
  }
  for (const url of ['/src/views/login/index.jsx', `/@fs${admin}/src/configs/i18next.js`]) {
    assert.deepEqual(request(url), { continued: true, status: 200 }, url);
  }
});