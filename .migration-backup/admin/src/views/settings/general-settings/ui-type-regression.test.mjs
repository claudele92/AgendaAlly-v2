import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

const uiTypeView = readFileSync(new URL('./ui-type.jsx', import.meta.url), 'utf8');

test('Admin UI type presents the single active AgendaAlly storefront read-only', () => {
  assert.match(uiTypeView, /title='STOREFRONT'/);
  assert.match(uiTypeView, /AgendaAlly Marketplace/);
  assert.match(uiTypeView, />\s*Active\s*</);
  assert.match(uiTypeView, /Book local expertise\. Shop local businesses\./);
  assert.doesNotMatch(uiTypeView, /View [1-4]|ui-type[1-4]\.png|iBeauty/);
  assert.doesNotMatch(uiTypeView, /InputCard|Radio|showConfirm|settingService|ui_type\s*:/);
});
