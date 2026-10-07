import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import {
  getInitialFormOptionId,
  getVerifiedCalendarScope,
  getVerifiedCalendarShopId,
} from './calendar-context.mjs';

const sellerServiceForm = readFileSync(
  new URL('../../seller-views/calendar/components/service-form.jsx', import.meta.url),
  'utf8',
);
const sellerServiceFormItems = readFileSync(
  new URL('../../seller-views/calendar/forms/service-form.jsx', import.meta.url),
  'utf8',
);
const masterServiceForm = readFileSync(
  new URL('../../master-views/calendar/components/service-form.jsx', import.meta.url),
  'utf8',
);
const masterServiceFormItems = readFileSync(
  new URL('../../master-views/calendar/forms/service-form.jsx', import.meta.url),
  'utf8',
);
const sellerAddForm = readFileSync(
  new URL('../../seller-views/calendar/components/add-form.jsx', import.meta.url),
  'utf8',
);
const masterAddForm = readFileSync(
  new URL('../../master-views/calendar/components/add-form.jsx', import.meta.url),
  'utf8',
);
const sellerFormItems = readFileSync(
  new URL('../../seller-views/calendar/components/form-items.jsx', import.meta.url),
  'utf8',
);
const masterFormItems = readFileSync(
  new URL('../../master-views/calendar/components/form-items.jsx', import.meta.url),
  'utf8',
);

function navigationScope(user, overrides = {}) {
  return {
    status: 'ready',
    sessionMatches: true,
    scope: {
      user_id: user.id,
      role: user.role,
      scope_status: 'known',
      shop: { id: 72, owner: false },
      ...overrides,
    },
  };
}

test('Calendar derives a restricted staff shop only from the verified self context', () => {
  const user = { id: 12, role: 'shop_manager' };
  const scope = navigationScope(user);

  assert.equal(getVerifiedCalendarShopId(user, scope), 72);
  assert.equal(
    getVerifiedCalendarShopId(user, { ...scope, status: 'loading' }),
    null,
  );
  assert.equal(
    getVerifiedCalendarShopId(user, { ...scope, sessionMatches: false }),
    null,
  );
  assert.equal(
    getVerifiedCalendarShopId(user, {
      ...scope,
      scope: { ...scope.scope, user_id: 99 },
    }),
    null,
  );
  assert.equal(
    getVerifiedCalendarShopId(user, {
      ...scope,
      scope: { ...scope.scope, role: 'seller' },
    }),
    null,
  );
  assert.match(sellerServiceForm, /getVerifiedCalendarShopId\(user, navigationScope\)/);
  assert.doesNotMatch(sellerServiceForm, /state\.myShop/);
  assert.match(sellerServiceForm, /const shop = hasId\(shopId\) \? \{ id: shopId \} : null/);
  assert.match(sellerServiceForm, /if \(!hasId\(data\?\.value\)\)/);
  assert.match(sellerServiceForm, /if \(!hasId\(service_master\?\.id\) \|\| !selectedSlots\?\.start\)/);
  assert.match(sellerServiceFormItems, /fetchService\(\{ shop_id: shop\.id \}/);
});

test('master Calendar uses a verified current context and never sends an undefined shop ID', () => {
  const user = { id: 13, role: 'master' };
  const scopeWithoutShop = navigationScope(user, { shop: null });

  assert.ok(getVerifiedCalendarScope(user, scopeWithoutShop));
  assert.equal(getVerifiedCalendarShopId(user, scopeWithoutShop), null);
  assert.match(masterServiceForm, /serviceLookupReady=\{Boolean\(currentScope\)\}/);
  assert.match(masterServiceForm, /if \(!hasId\(data\?\.value\)\)/);
  assert.match(masterServiceForm, /if \(!hasId\(selectedService\?\.id\)/);
  assert.match(masterServiceFormItems, /disabled=\{!serviceLookupReady\}/);
  assert.match(masterServiceFormItems, /shop\?\.id === null \|\| shop\?\.id === undefined/);
  assert.match(masterServiceFormItems, /fetchService\(serviceParams\)/);
  assert.doesNotMatch(masterServiceFormItems, /shop\.value/);
});

test('Calendar form option loading tolerates an empty catalog and reports real request failures', () => {
  assert.equal(getInitialFormOptionId([]), null);
  assert.equal(getInitialFormOptionId(undefined), null);
  assert.equal(getInitialFormOptionId([null, {}]), null);
  assert.equal(getInitialFormOptionId([null, { id: 35 }]), 35);

  for (const source of [sellerAddForm, masterAddForm]) {
    assert.match(source, /if \(!isAddForm\) return undefined/);
    assert.match(source, /getInitialFormOptionId\(availableForms\)/);
    assert.match(source, /setLoadError\(/);
    assert.match(source, /<Empty description=/);
    assert.doesNotMatch(source, /data\[0\]\.id/);
  }
  for (const source of [sellerFormItems, masterFormItems]) {
    assert.match(source, /if \(service_id === null \|\| service_id === undefined \|\| service_id === ''\)/);
    assert.match(source, /if \(!item\) return null/);
    assert.match(source, /Unable to save the form\./);
  }
});