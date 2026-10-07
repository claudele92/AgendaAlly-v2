import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import {
  hasVerifiedShopManagerPermission,
  resolveDashboardBookingsPath,
  resolveDashboardOrdersPath,
  resolveDashboardStatisticsRole,
} from './dashboard-statistics-role.mjs';

const user = { id: 17, role: 'shop_manager' };
const verifiedShopScope = {
  status: 'ready',
  sessionMatches: true,
  scope: {
    user_id: 17,
    role: 'shop_manager',
    scope_status: 'known',
    shop: { id: 8, name: 'Shop', owner: false },
    shop_scope: { permission_keys: ['bookings.view', 'reports.view'] },
  },
};

test('shop_manager dashboard statistics use the seller-scoped API family only with verified reports.view', () => {
  assert.equal(
    resolveDashboardStatisticsRole(user, verifiedShopScope),
    'seller',
  );
});

test('dashboard statistic cards fit the available content width without clipping', () => {
  const cardStyles = readFileSync(
    new URL('./main-cards/main-cards.module.scss', import.meta.url),
    'utf8',
  );

  assert.match(cardStyles, /\.cards\s*\{[\s\S]*?min-width:\s*0;/);
  assert.match(
    cardStyles,
    /grid-template-columns:\s*repeat\(auto-fit,\s*minmax\(min\(100%,\s*160px\),\s*1fr\)\)/,
  );
});

test('shop_manager dashboard statistics fail closed without matching scope or reports.view', () => {
  assert.equal(
    resolveDashboardStatisticsRole(user, {
      ...verifiedShopScope,
      scope: {
        ...verifiedShopScope.scope,
        shop_scope: { permission_keys: ['bookings.view'] },
      },
    }),
    null,
  );
  assert.equal(
    resolveDashboardStatisticsRole(user, {
      ...verifiedShopScope,
      sessionMatches: false,
    }),
    null,
  );
  assert.equal(
    resolveDashboardStatisticsRole(user, {
      ...verifiedShopScope,
      scope: { ...verifiedShopScope.scope, user_id: 18 },
    }),
    null,
  );
  assert.equal(
    resolveDashboardStatisticsRole(user, {
      ...verifiedShopScope,
      scope: { ...verifiedShopScope.scope, role: 'seller' },
    }),
    null,
  );
});

test('orders drilldown needs a verified shop orders.view grant independently of reports.view', () => {
  const reportAndOrdersScope = {
    ...verifiedShopScope,
    scope: {
      ...verifiedShopScope.scope,
      shop_scope: { permission_keys: ['reports.view', 'orders.view'] },
    },
  };
  const reportsOnlyScope = {
    ...verifiedShopScope,
    scope: {
      ...verifiedShopScope.scope,
      shop_scope: { permission_keys: ['reports.view'] },
    },
  };

  assert.equal(resolveDashboardStatisticsRole(user, reportAndOrdersScope), 'seller');
  assert.equal(
    resolveDashboardOrdersPath(user, reportAndOrdersScope),
    'seller/orders',
  );
  assert.equal(resolveDashboardStatisticsRole(user, reportsOnlyScope), 'seller');
  assert.equal(resolveDashboardOrdersPath(user, reportsOnlyScope), null);
  assert.equal(
    hasVerifiedShopManagerPermission(user, reportsOnlyScope, 'orders.view'),
    false,
  );
});

test('shop_manager never receives an orders destination from missing or stale scope', () => {
  assert.equal(resolveDashboardOrdersPath(user, null), null);
  assert.equal(
    resolveDashboardOrdersPath(user, {
      ...verifiedShopScope,
      sessionMatches: false,
    }),
    null,
  );
  assert.equal(
    resolveDashboardOrdersPath(user, {
      ...verifiedShopScope,
      scope: { ...verifiedShopScope.scope, user_id: 18 },
    }),
    null,
  );
  assert.equal(
    resolveDashboardOrdersPath(user, {
      ...verifiedShopScope,
      scope: { ...verifiedShopScope.scope, role: 'seller' },
    }),
    null,
  );
});

test('restricted country reports do not imply orders or bookings list access', () => {
  const countryFinanceUser = { id: 42, role: 'manager' };
  const countryFinanceScope = {
    status: 'ready',
    sessionMatches: true,
    scope: {
      user_id: 42,
      role: 'manager',
      scope_status: 'known',
      is_super_admin: false,
      country: { id: 9, name: 'Country' },
      country_source: 'restricted_country',
      country_scope: {
        permission_keys: ['reports.view', 'transactions.view'],
      },
    },
  };

  assert.equal(resolveDashboardStatisticsRole(countryFinanceUser, countryFinanceScope), 'admin');
  assert.equal(resolveDashboardOrdersPath(countryFinanceUser, countryFinanceScope), null);
  assert.equal(resolveDashboardBookingsPath(countryFinanceUser, countryFinanceScope), null);

  const withOrders = {
    ...countryFinanceScope,
    scope: {
      ...countryFinanceScope.scope,
      country_scope: {
        permission_keys: ['reports.view', 'transactions.view', 'orders.view'],
      },
    },
  };
  const withBookings = {
    ...countryFinanceScope,
    scope: {
      ...countryFinanceScope.scope,
      country_scope: {
        permission_keys: ['reports.view', 'bookings.view'],
      },
    },
  };
  assert.equal(resolveDashboardOrdersPath(countryFinanceUser, withOrders), 'orders');
  assert.equal(resolveDashboardBookingsPath(countryFinanceUser, withBookings), 'booking');
});

test('verified superadmin and structural shop owner keep their native dashboard destinations', () => {
  const superAdminUser = { id: 5, role: 'admin' };
  const superAdminScope = {
    status: 'ready',
    sessionMatches: true,
    scope: {
      user_id: 5,
      role: 'admin',
      scope_status: 'known',
      is_super_admin: true,
    },
  };
  assert.equal(resolveDashboardOrdersPath(superAdminUser, superAdminScope), 'orders?type=all');
  assert.equal(resolveDashboardBookingsPath(superAdminUser, superAdminScope), 'booking');

  const ownerUser = { id: 6, role: 'seller' };
  const ownerScope = {
    status: 'ready',
    sessionMatches: true,
    scope: {
      user_id: 6,
      role: 'seller',
      scope_status: 'known',
      shop: { id: 8, owner: true },
      shop_scope: { permission_keys: [] },
    },
  };
  assert.equal(resolveDashboardOrdersPath(ownerUser, ownerScope), 'seller/orders');
  assert.equal(resolveDashboardBookingsPath(ownerUser, ownerScope), 'seller/bookings');
});

test('existing administrator and seller dashboard service roles remain distinct', () => {
  assert.equal(
    resolveDashboardStatisticsRole({ id: 1, role: 'manager' }, null),
    'admin',
  );
  assert.equal(
    resolveDashboardStatisticsRole({ id: 2, role: 'seller' }, null),
    'seller',
  );
  assert.equal(
    resolveDashboardStatisticsRole({ id: 3, role: 'deliveryman' }, null),
    null,
  );
});

test('dashboard JSX wires staff metrics to seller thunks and permission-checks child drilldowns', () => {
  const dashboard = readFileSync(
    new URL('./index.jsx', import.meta.url),
    'utf8',
  );
  const shopManagerBranches = [
    ...dashboard.matchAll(
      /case 'shop_manager':([\s\S]*?)(?=\n\s*case '|\n\s*default:)/g,
    ),
  ].map((match) => match[1]);
  assert.equal(shopManagerBranches.length, 6);
  for (const branch of shopManagerBranches) {
    assert.match(branch, /fetchSeller/);
    assert.doesNotMatch(branch, /dispatch\(fetchStatistics\(/);
    assert.doesNotMatch(branch, /dispatch\(fetchOrderCounts\(/);
    assert.doesNotMatch(branch, /dispatch\(fetchOrderSales\(/);
    assert.doesNotMatch(branch, /dispatch\(fetchTopProducts\(/);
    assert.doesNotMatch(branch, /dispatch\(fetchTopCustomers\(/);
    assert.doesNotMatch(branch, /fetchStatisticsBookingsReportAdmin/);
  }

  const mainCards = readFileSync(
    new URL('./main-cards/main-cards.jsx', import.meta.url),
    'utf8',
  );
  assert.match(mainCards, /useNavigationScope/);
  assert.match(mainCards, /resolveDashboardOrdersPath\(user, navigationScope\)/);
  assert.match(mainCards, /\{ordersPath && \(/);

  const mainBookingCards = readFileSync(
    new URL('./main-cards/main-booking-cards.jsx', import.meta.url),
    'utf8',
  );
  assert.match(
    mainBookingCards,
    /resolveDashboardBookingsPath\(user, navigationScope\)/,
  );
  assert.match(mainBookingCards, /useNavigationScope/);
  assert.match(mainBookingCards, /\{bookingPath && \(/);

  const general = readFileSync(
    new URL('./general/general.jsx', import.meta.url),
    'utf8',
  );
  assert.match(general, /role !== 'shop_manager'/);
  assert.match(general, /<MainBookingCards \/>/);
});