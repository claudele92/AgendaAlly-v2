import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { resolveAppLayoutBootstrapPlan } from './app-layout-bootstrap.mjs';

const appLayoutSource = readFileSync(
  new URL('./app-layout.jsx', import.meta.url),
  'utf8',
);
const publicCurrencyServiceSource = readFileSync(
  new URL('../services/rest/currency.js', import.meta.url),
  'utf8',
);
function scopeFor(user, overrides = {}) {
  return {
    status: 'ready',
    sessionMatches: true,
    scope: {
      user_id: user.id,
      role: user.role,
      scope_status: 'known',
      is_super_admin: false,
      country_scope: { permission_keys: [] },
      shop_scope: { permission_keys: [] },
      shop: null,
      ...overrides,
    },
  };
}

test('all authenticated roles use the existing public currency REST catalog at startup', () => {
  for (const role of [
    'admin',
    'manager',
    'seller',
    'shop_manager',
    'master',
    'finance',
  ]) {
    const user = { id: 17, role };
    const plan = resolveAppLayoutBootstrapPlan(user, scopeFor(user));
    assert.equal(plan.currencySource, 'rest/currencies', role);
    assert.equal(plan.fetchAdminShops, false, role);
    assert.equal(plan.fetchMyShop, false, role);
  }

  assert.match(
    publicCurrencyServiceSource,
    /request\.get\(['"]rest\/currencies['"]/,
  );
  assert.match(appLayoutSource, /dispatch\(fetchRestCurrencies\(\{\}\)\)/);
  assert.doesNotMatch(appLayoutSource, /dispatch\(fetchCurrencies\(/);
  assert.match(appLayoutSource, /dispatch\(setCurrencySession\(user\)\)/);
});

test('country shop catalogs require the verified vendors.view grant or superadmin scope', () => {
  const manager = { id: 18, role: 'manager' };
  const financeScope = scopeFor(manager, {
    country_scope: {
      permission_keys: ['transactions.view', 'reports.view'],
    },
  });
  assert.equal(
    resolveAppLayoutBootstrapPlan(manager, financeScope).fetchAdminShops,
    false,
  );

  const vendorsScope = scopeFor(manager, {
    country_scope: { permission_keys: ['vendors.view'] },
  });
  assert.equal(
    resolveAppLayoutBootstrapPlan(manager, vendorsScope).fetchAdminShops,
    true,
  );

  const superAdminScope = scopeFor(manager, {
    is_super_admin: true,
    country_scope: { permission_keys: [] },
  });
  assert.equal(
    resolveAppLayoutBootstrapPlan(manager, superAdminScope).fetchAdminShops,
    true,
  );

  const currencyOnlyScope = scopeFor(manager, {
    country_scope: { permission_keys: ['currency.view'] },
  });
  assert.equal(
    resolveAppLayoutBootstrapPlan(manager, currencyOnlyScope).fetchAdminShops,
    false,
  );
});

test('seller shop details load only from a verified owned or settings-view shop scope', () => {
  const seller = { id: 19, role: 'seller' };
  assert.equal(
    resolveAppLayoutBootstrapPlan(
      seller,
      scopeFor(seller, { shop: { id: 2, owner: true } }),
    ).fetchMyShop,
    true,
  );
  assert.equal(
    resolveAppLayoutBootstrapPlan(
      seller,
      scopeFor(seller, { shop: null }),
    ).fetchMyShop,
    false,
  );

  const moderator = { id: 20, role: 'moderator' };
  assert.equal(
    resolveAppLayoutBootstrapPlan(
      moderator,
      scopeFor(moderator, {
        shop: { id: 2, owner: false },
        shop_scope: { permission_keys: ['shop_settings.view'] },
      }),
    ).fetchMyShop,
    true,
  );
  assert.equal(
    resolveAppLayoutBootstrapPlan(
      moderator,
      scopeFor(moderator, {
        shop: { id: 2, owner: false },
        shop_scope: { permission_keys: ['shop_settings.manage'] },
      }),
    ).fetchMyShop,
    false,
  );
});

test('unknown, mismatched, or stale sessions never trigger protected startup lists', () => {
  const user = { id: 21, role: 'admin' };
  const vendorsScope = scopeFor(user, {
    country_scope: { permission_keys: ['vendors.view'] },
  });

  assert.equal(
    resolveAppLayoutBootstrapPlan(user, {
      ...vendorsScope,
      sessionMatches: false,
    }).fetchAdminShops,
    false,
  );
  assert.equal(
    resolveAppLayoutBootstrapPlan(user, {
      ...vendorsScope,
      scope: { ...vendorsScope.scope, user_id: 99 },
    }).fetchAdminShops,
    false,
  );
  assert.equal(
    resolveAppLayoutBootstrapPlan(user, {
      ...vendorsScope,
      scope: { ...vendorsScope.scope, scope_status: 'unknown' },
    }).fetchAdminShops,
    false,
  );
});