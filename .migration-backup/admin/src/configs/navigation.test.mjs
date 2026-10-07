import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import {
  effectiveNavigationMenuIds,
  findActiveNavigationItem,
  formatVerifiedNavigationScope,
  isSafeInternalMenuUrl,
  resolveNavigation,
  resolveStage1DashboardDestination,
} from './navigation.mjs';

const menuConfigSource = readFileSync(new URL('./menu-config.js', import.meta.url), 'utf8');
const menuConfigContext = { globalThis: {} };
runInNewContext(
  menuConfigSource.replace(/^export const data =/m, 'globalThis.data ='),
  menuConfigContext,
);
const nativeRoleMenus = menuConfigContext.globalThis.data;

test('email template library remains grant-controlled and appears in Settings', () => {
  const result = resolveNavigation({
    sourceMenus: nativeRoleMenus.admin,
    role: 'admin',
    isSuperAdmin: true,
    authorizedMenuIds: ['message.subscriber'],
  });
  const settings = result.items.find((item) => item.name === 'SETTINGS' || item.id === 'SETTINGS');
  assert.ok(settings, 'Settings section must exist');
  assert.ok(JSON.stringify(settings).includes('message/subscriber'));
  assert.ok(JSON.stringify(settings).includes('Email Templates'));
  const denied = resolveNavigation({
    sourceMenus: nativeRoleMenus.admin,
    role: 'admin',
    authorizedMenuIds: [],
  });
  assert.ok(!JSON.stringify(denied.items).includes('message/subscriber'));
});

test('native Stories navigation follows verified shop grants for owners and staff', () => {
  const catalog = [{
    name: 'content', id: 'content_management', type: 'group',
    menus: [{ name: 'stories', id: 'seller_stories', url: 'seller/stories', type: 'single' }],
  }];
  const scope = {
    user_id: 17, role: 'shop_manager', scope_status: 'known',
    shop: { id: 8, name: 'Shop', owner: false },
    shop_scope: { unrestricted: false, permission_keys: ['stories.view'] },
  };
  assert.ok(effectiveNavigationMenuIds(catalog, 'shop_manager', scope, 17).includes('seller_stories'));
  assert.ok(!effectiveNavigationMenuIds(catalog, 'shop_manager', {
    ...scope, shop_scope: { permission_keys: ['stories.manage'] },
  }, 17).includes('seller_stories'));
  assert.equal(effectiveNavigationMenuIds(catalog, 'shop_manager', { ...scope, scope_status: 'unknown' }, 17), null);
  assert.ok(effectiveNavigationMenuIds(catalog, 'seller', {
    ...scope, role: 'seller', shop: { ...scope.shop, owner: true },
    shop_scope: { unrestricted: true, permission_keys: [] },
  }, 17).includes('seller_stories'));
});

test('manual Finance and Shop payout request routes remain grant-controlled and discoverable', () => {
  const financeNav = resolveNavigation({
    sourceMenus: nativeRoleMenus.admin,
    role: 'admin',
    isSuperAdmin: true,
    authorizedMenuIds: ['manual-finance'],
  });
  assert.ok(JSON.stringify(financeNav.items).includes('manual-finance'));

  const financeScope = {
    user_id: 31,
    role: 'manager',
    scope_status: 'known',
    country: { id: 6, name: 'North' },
    country_source: 'restricted_country',
    country_scope: { permission_keys: ['transactions.manage'] },
  };
  const countryIds = effectiveNavigationMenuIds(nativeRoleMenus.manager, 'manager', financeScope, 31);
  assert.ok(countryIds.includes('manual-finance'));
  assert.ok(!countryIds.includes('admin.payouts'));

  const shopScope = {
    user_id: 44,
    role: 'shop_manager',
    scope_status: 'known',
    shop: { id: 9, name: 'Studio', owner: false },
    shop_scope: { permission_keys: ['payments.view'] },
  };
  const shopIds = effectiveNavigationMenuIds(nativeRoleMenus.shop_manager, 'shop_manager', shopScope, 44);
  assert.ok(shopIds.includes('seller-manual-payout-requests'));
});

const globalAdminMenu = [
  { name: 'dashboard', id: 'admin-home', url: 'dashboard', type: 'single' },
  {
    name: 'zone.management',
    id: 'zones',
    type: 'group',
    menus: [
      { name: 'country', id: 'country', url: 'deliveryzone/country', type: 'single' },
      {
        name: 'country.admins',
        id: 'country-admins',
        url: 'country-admins',
        type: 'single',
        superadminOnly: true,
      },
    ],
  },
  {
    name: 'transaction.management',
    id: 'transactions',
    type: 'group',
    menus: [
      { name: 'transactions', id: 'transactions-page', url: 'transactions', type: 'single' },
      { name: 'payouts', id: 'payouts', url: 'payouts', type: 'single' },
    ],
  },
  {
    name: 'business.settings',
    id: 'settings',
    type: 'group',
    menus: [
      { name: 'settings.general', id: 'settings-general', url: 'settings/general', type: 'single' },
      { name: 'platform.payment.configs', id: 'platform-payments', url: 'platform-payment-configs', type: 'single', superadminOnly: true },
    ],
  },
];

const managerMenu = globalAdminMenu.map((item) => ({
  ...item,
  menus: item.menus?.map((child) => ({ ...child })),
}));

const sellerMenu = [
  { name: 'dashboard', id: 'seller-home', url: 'dashboard', type: 'single' },
  {
    name: 'shop.management',
    id: 'shop-management',
    type: 'group',
    menus: [
      { name: 'my.shop', id: 'my-shop', url: 'my-shop', type: 'single' },
      { name: 'staff', id: 'staff', url: 'seller/staff', type: 'single' },
    ],
  },
  {
    name: 'order.management',
    id: 'orders',
    type: 'group',
    menus: [
      { name: 'all.orders', id: 'seller-orders', url: 'seller/orders-board', type: 'single' },
    ],
  },
];

const shopStaffMenu = sellerMenu.map((item) => ({
  ...item,
  menus: item.menus?.map((child) => ({ ...child })),
}));

const shopPermissionMenu = [
  { name: 'dashboard', id: 'shop-home', url: 'dashboard', type: 'single' },
  {
    name: 'booking.management',
    id: 'booking',
    type: 'group',
    menus: [
      { name: 'bookings', id: 'booking-list', url: 'seller/bookings', type: 'single' },
      { name: 'bookings-report', id: 'booking-report', url: 'seller/bookings-report', type: 'single' },
    ],
  },
  {
    name: 'order.management',
    id: 'orders',
    type: 'group',
    menus: [
      { name: 'all.orders', id: 'order-list', url: 'seller/orders-board', type: 'single' },
      { name: 'refunds', id: 'refund-list', url: 'seller/refunds', type: 'single' },
      { name: 'order.status', id: 'order-status', url: 'settings/orderStatus', type: 'single' },
    ],
  },
  {
    name: 'transaction',
    id: 'transaction',
    type: 'group',
    menus: [
      { name: 'payments', id: 'payments-list', url: 'seller/payments', type: 'single' },
      { name: 'payouts', id: 'payout-list', url: 'seller/payouts', type: 'single' },
    ],
  },
  {
    name: 'service.management',
    id: 'services',
    type: 'group',
    menus: [{ name: 'services', id: 'service-list', url: 'seller/services', type: 'single' }],
  },
  {
    name: 'staff.management',
    id: 'staff',
    type: 'group',
    menus: [{ name: 'staff', id: 'staff-list', url: 'seller/staff', type: 'single' }],
  },
  {
    name: 'invitation.management',
    id: 'invitations',
    type: 'group',
    menus: [{ name: 'masters', id: 'masters-list', url: 'seller/invitations/masters', type: 'single' }],
  },
  {
    name: 'shop.management',
    id: 'shop',
    type: 'group',
    menus: [
      { name: 'my.shop', id: 'shop-settings', url: 'my-shop', type: 'single' },
      { name: 'users', id: 'shop-users', url: 'seller/shop-users', type: 'single' },
    ],
  },
  {
    name: 'product.management',
    id: 'products',
    type: 'group',
    menus: [
      { name: 'product', id: 'product-list', url: 'seller/products', type: 'single' },
      { name: 'discounts', id: 'discount-list', url: 'seller/discounts', type: 'single' },
    ],
  },
  {
    name: 'marketing.management',
    id: 'marketing',
    type: 'group',
    menus: [{ name: 'coupons', id: 'coupon-list', url: 'seller/coupons', type: 'single' }],
  },
  {
    name: 'content',
    id: 'content',
    type: 'group',
    menus: [
      { name: 'brands', id: 'brand-list', url: 'seller/brands', type: 'single' },
      { name: 'form.options', id: 'form-options', url: 'seller/form-options', type: 'single' },
    ],
  },
  {
    name: 'business',
    id: 'business',
    type: 'group',
    menus: [{ name: 'ad.packages', id: 'ad-packages', url: 'seller/advert', type: 'single' }],
  },
  { name: 'pos.system', id: 'pos', url: 'seller/pos-system', type: 'single' },
  { name: 'delivery.price', id: 'delivery-price', url: 'seller/delivery-price', type: 'single' },
];

const masterMenu = [
  { name: 'dashboard', id: 'master-home', url: 'dashboard', type: 'single' },
  { name: 'calendar', id: 'master-calendar', url: 'master/calendar', type: 'single' },
  { name: 'service.master', id: 'master-services', url: 'master/service-master', type: 'single' },
  { name: 'closed.days', id: 'master-days', url: 'master/closed-days', type: 'single' },
];

const itemsInSection = (items, section) =>
  items.find((item) => item.stage1Title === section)?.menus || [];

const shopGrantIds = (permissionKeys) =>
  effectiveNavigationMenuIds(
    shopPermissionMenu,
    'shop_manager',
    {
      user_id: 450,
      role: 'shop_manager',
      scope_status: 'known',
      shop: { id: 7, name: 'Shop', owner: false },
      shop_scope: { unrestricted: false, permission_keys: permissionKeys },
    },
    450,
  );

test('verified global admin maps all original routes, including finance, settings, and POS', () => {
  const catalog = [
    { name: 'dashboard', id: 'home', url: 'dashboard', type: 'single' },
    { name: 'pos.system', id: 'pos', url: 'pos-system', type: 'single' },
    {
      name: 'transaction.management',
      id: 'finance',
      type: 'group',
      menus: [{ name: 'payouts', id: 'payouts', url: 'payouts', type: 'single' }],
    },
    {
      name: 'business.settings',
      id: 'settings',
      type: 'group',
      menus: [{ name: 'settings.general', id: 'settings', url: 'settings/general', type: 'single' }],
    },
    {
      name: 'zone.management',
      id: 'zones',
      type: 'group',
      menus: [{ name: 'country.admins', id: 'country-admins', url: 'country-admins', type: 'single', superadminOnly: true }],
    },
  ];
  const grants = effectiveNavigationMenuIds(catalog, 'admin', {
    user_id: 42,
    role: 'admin',
    is_super_admin: true,
    scope_status: 'known',
  }, 42);

  assert.deepEqual(grants, ['home', 'pos', 'payouts', 'settings', 'country-admins']);
  const result = resolveNavigation({
    sourceMenus: catalog,
    role: 'admin',
    isSuperAdmin: true,
    authorizedMenuIds: grants,
  });
  const visible = result.items.flatMap((section) => section.menus);
  assert.ok(visible.some((item) => item.id === 'pos'));
  assert.ok(visible.some((item) => item.id === 'payouts'));
  assert.ok(visible.some((item) => item.id === 'settings'));
});

test('country admin and accepted country manager receive only their effective country families', () => {
  const catalog = [
    { name: 'dashboard', id: 'home', url: 'dashboard', type: 'single' },
    { name: 'calendar', id: 'calendar', url: 'calendar', type: 'single' },
    {
      name: 'booking.management',
      id: 'booking',
      type: 'group',
      menus: [{ name: 'bookings', id: 'bookings', url: 'booking', type: 'single' }],
    },
    {
      name: 'zone.management',
      id: 'zones',
      type: 'group',
      menus: [
        { name: 'country', id: 'country', url: 'deliveryzone/country', type: 'single' },
        { name: 'country.staff', id: 'country-staff', url: 'country-admin/staff', type: 'single' },
        { name: 'country.admins', id: 'global-country-admins', url: 'country-admins', type: 'single', superadminOnly: true },
      ],
    },
    {
      name: 'transaction.management',
      id: 'transactions',
      type: 'group',
      menus: [{ name: 'transactions', id: 'transaction-list', url: 'transactions', type: 'single' }],
    },
    {
      name: 'business.settings',
      id: 'settings',
      type: 'group',
      menus: [{ name: 'currencies', id: 'currencies', url: 'currencies', type: 'single' }],
    },
  ];
  const context = {
    user_id: 18,
    role: 'manager',
    is_super_admin: false,
    scope_status: 'known',
    country: { id: 4, name: 'Cameroon' },
    country_source: 'restricted_country',
    country_scope: {
      unrestricted: false,
      permission_keys: ['bookings.view', 'staff.view', 'transactions.view'],
    },
  };
  const grants = effectiveNavigationMenuIds(catalog, 'manager', context, 18);

  assert.deepEqual(grants, ['home', 'calendar', 'bookings', 'country-staff', 'transaction-list']);
  assert.ok(!grants.includes('global-country-admins'));
  assert.ok(!grants.includes('currencies'));
  assert.equal(
    effectiveNavigationMenuIds(catalog, 'manager', { ...context, user_id: 99 }, 18),
    null,
  );
});

test('country order-view and review grants do not expose order-status administration', () => {
  const catalog = [
    { name: 'dashboard', id: 'home', url: 'dashboard', type: 'single' },
    {
      name: 'order.management',
      id: 'orders',
      type: 'group',
      menus: [
        {
          name: 'orders',
          id: 'orders-parent',
          type: 'parent',
          children: [
            { name: 'all', id: 'order-list', url: 'orders', type: 'single' },
            { name: 'refunds', id: 'refunds', url: 'refunds', type: 'single' },
          ],
        },
        { name: 'order.status', id: 'order-status', url: 'settings/orderStatus', type: 'single' },
      ],
    },
    {
      name: 'product.management',
      id: 'products',
      type: 'group',
      menus: [
        { name: 'product.reviews', id: 'product-reviews', url: 'reviews/product', type: 'single' },
      ],
    },
  ];
  const context = {
    user_id: 30,
    role: 'admin',
    is_super_admin: false,
    scope_status: 'known',
    country: { id: 5, name: 'Cameroon' },
    country_source: 'restricted_country',
    country_scope: {
      unrestricted: false,
      permission_keys: ['orders.view', 'reviews.view'],
    },
  };
  const grants = effectiveNavigationMenuIds(catalog, 'admin', context, 30);

  assert.ok(grants.includes('order-list'));
  assert.ok(grants.includes('product-reviews'));
  assert.ok(!grants.includes('order-status'));
  assert.ok(!grants.includes('refunds'));
});

test('shop booking status- and availability-only permissions do not grant booking-list access', () => {
  for (const permission of ['bookings.status', 'bookings.availability']) {
    const grants = shopGrantIds([permission]);
    assert.ok(!grants.includes('booking-list'), permission);
    assert.ok(!grants.includes('booking-report'), permission);
    assert.ok(!grants.includes('shop-home'), permission);
  }
});

test('shop payout-, gateway-, and refund-manage-only permissions do not grant payment reads', () => {
  for (const permission of [
    'payments.payouts.manage',
    'payments.gateways.manage',
    'payments.refunds.manage',
  ]) {
    const grants = shopGrantIds([permission]);
    assert.ok(!grants.includes('payments-list'), permission);
    assert.ok(!grants.includes('payout-list'), permission);
    assert.ok(!grants.includes('refund-list'), permission);
    assert.ok(!grants.includes('shop-home'), permission);
  }
});

test('shop manage-only permissions do not expose read pages; explicit order-status management remains', () => {
  const grants = shopGrantIds([
    'bookings.manage',
    'bookings.status',
    'bookings.availability',
    'payments.payouts.manage',
    'payments.gateways.manage',
    'payments.refunds.manage',
    'services.manage',
    'services.auctions.manage',
    'staff.invite',
    'staff.roles.manage',
    'products.manage',
    'orders.manage',
    'orders.delivery_settings',
    'marketing.manage',
    'shop_settings.manage',
  ]);

  assert.deepEqual(Array.from(grants), ['order-status', 'delivery-price']);
  for (const id of [
    'shop-home',
    'booking-list',
    'order-list',
    'refund-list',
    'payments-list',
    'payout-list',
    'service-list',
    'staff-list',
    'masters-list',
    'shop-settings',
    'shop-users',
    'product-list',
    'discount-list',
    'coupon-list',
    'brand-list',
    'form-options',
    'ad-packages',
    'pos',
  ]) {
    assert.ok(!grants.includes(id), id);
  }
});

test('country manage-only permissions do not imply read grants or dashboard fallback', () => {
  const context = {
    user_id: 451,
    role: 'admin',
    is_super_admin: false,
    scope_status: 'known',
    country: { id: 9, name: 'Country' },
    country_source: 'restricted_country',
    country_scope: {
      unrestricted: false,
      permission_keys: [
        'vendors.manage',
        'orders.manage',
        'bookings.manage',
        'transactions.manage',
        'products.manage',
        'reviews.manage',
        'tickets.manage',
        'staff.invite',
        'staff.roles.manage',
        'marketing.manage',
        'currency.manage',
        'geography.manage',
      ],
    },
  };
  const catalog = [
    { name: 'dashboard', id: 'country-home', url: 'dashboard', type: 'single' },
    {
      name: 'shop.management',
      id: 'shops',
      type: 'group',
      menus: [{ name: 'shops', id: 'shop-list', url: 'shops', type: 'single' }],
    },
    {
      name: 'booking.management',
      id: 'booking',
      type: 'group',
      menus: [{ name: 'bookings', id: 'booking-list', url: 'bookings', type: 'single' }],
    },
    {
      name: 'transaction.management',
      id: 'transactions',
      type: 'group',
      menus: [{ name: 'transactions', id: 'transaction-list', url: 'transactions', type: 'single' }],
    },
    {
      name: 'user.management',
      id: 'users',
      type: 'group',
      menus: [{ name: 'withdraws', id: 'withdraw-list', url: 'withdraws', type: 'single' }],
    },
    { name: 'tickets', id: 'ticket-list', url: 'tickets', type: 'single' },
    {
      name: 'order.management',
      id: 'orders',
      type: 'group',
      menus: [
        { name: 'all.orders', id: 'order-list', url: 'orders', type: 'single' },
        { name: 'refunds', id: 'refund-list', url: 'refunds', type: 'single' },
        { name: 'order.status', id: 'order-status', url: 'settings/orderStatus', type: 'single' },
      ],
    },
    {
      name: 'product.management',
      id: 'products',
      type: 'group',
      menus: [{ name: 'warehouse', id: 'warehouse', url: 'warehouses', type: 'single' }],
    },
    {
      name: 'zone.management',
      id: 'zones',
      type: 'group',
      menus: [
        { name: 'country.staff', id: 'staff-list', url: 'country-admin/staff', type: 'single' },
        { name: 'country', id: 'geography', url: 'deliveryzone/country', type: 'single' },
      ],
    },
    {
      name: 'business.settings',
      id: 'settings',
      type: 'group',
      menus: [{ name: 'currencies', id: 'currencies', url: 'currencies', type: 'single' }],
    },
  ];
  const grants = effectiveNavigationMenuIds(catalog, 'admin', context, 451);

  assert.deepEqual(Array.from(grants), ['refund-list', 'order-status', 'geography']);
  for (const id of [
    'country-home',
    'shop-list',
    'booking-list',
    'transaction-list',
    'withdraw-list',
    'ticket-list',
    'order-list',
    'warehouse',
    'staff-list',
    'currencies',
  ]) {
    assert.ok(!grants.includes(id), id);
  }
});

test('dashboard defaults only when granted in Stage 1; otherwise redirects to a granted native page', () => {
  const catalog = [
    { name: 'dashboard', id: 'home', url: 'dashboard', type: 'single' },
    {
      name: 'order.management',
      id: 'orders',
      type: 'group',
      menus: [
        { name: 'all.orders', id: 'order-list', url: 'orders', type: 'single' },
        {
          name: 'order.status',
          id: 'order-status',
          url: 'settings/orderStatus',
          type: 'single',
        },
      ],
    },
    {
      name: 'zone.management',
      id: 'zones',
      type: 'group',
      menus: [
        {
          name: 'country',
          id: 'country',
          url: 'deliveryzone/country',
          type: 'single',
        },
      ],
    },
  ];
  const context = {
    user_id: 452,
    role: 'admin',
    scope_status: 'known',
    country: { id: 9, name: 'Country' },
    country_source: 'restricted_country',
    country_scope: {
      permission_keys: ['orders.manage', 'geography.manage'],
    },
  };
  const destination = resolveStage1DashboardDestination({
    sourceMenus: catalog,
    role: 'admin',
    context,
    authenticatedUserId: 452,
  });

  assert.equal(destination.authorizationKnown, true);
  assert.equal(destination.dashboardAuthorized, false);
  assert.equal(destination.destinationUrl, 'settings/orderStatus');

  const readDestination = resolveStage1DashboardDestination({
    sourceMenus: catalog,
    role: 'admin',
    context: {
      ...context,
      country_scope: { permission_keys: ['orders.view'] },
    },
    authenticatedUserId: 452,
  });
  assert.equal(readDestination.dashboardAuthorized, true);
});

test('dashboard has no default destination when navigation scope is unknown', () => {
  const destination = resolveStage1DashboardDestination({
    sourceMenus: [
      { name: 'dashboard', id: 'home', url: 'dashboard', type: 'single' },
    ],
    role: 'admin',
    context: null,
    authenticatedUserId: 452,
  });

  assert.equal(destination.authorizationKnown, false);
  assert.equal(destination.dashboardAuthorized, false);
  assert.equal(destination.destinationUrl, null);
});

test('shop owner structural access differs from staff permission keys; finance and POS remain available when granted', () => {
  const catalog = [
    { name: 'dashboard', id: 'home', url: 'dashboard', type: 'single' },
    { name: 'pos.system', id: 'pos', url: 'seller/pos-system', type: 'single' },
    { name: 'wallet', id: 'wallet', url: 'seller/wallet', type: 'single' },
    {
      name: 'transaction',
      id: 'transactions',
      type: 'group',
      menus: [
        { name: 'payments', id: 'payments', url: 'seller/payments', type: 'single' },
        { name: 'payouts', id: 'payouts', url: 'seller/payouts', type: 'single' },
      ],
    },
    {
      name: 'shop.management',
      id: 'shop',
      type: 'group',
      menus: [{ name: 'my.shop', id: 'my-shop', url: 'my-shop', type: 'single' }],
    },
    {
      name: 'order.management',
      id: 'orders',
      type: 'group',
      menus: [{ name: 'all.orders', id: 'orders-list', url: 'seller/orders-board', type: 'single' }],
    },
  ];
  const owner = effectiveNavigationMenuIds(catalog, 'seller', {
    user_id: 12,
    role: 'seller',
    scope_status: 'known',
    shop: { id: 7, name: 'Shop', owner: true },
    shop_scope: { unrestricted: true, permission_keys: [] },
  }, 12);
  const staff = effectiveNavigationMenuIds(catalog, 'shop_manager', {
    user_id: 13,
    role: 'shop_manager',
    scope_status: 'known',
    shop: { id: 7, name: 'Shop', owner: false },
    shop_scope: {
      unrestricted: false,
      permission_keys: ['orders.view', 'payments.view'],
    },
  }, 13);

  assert.deepEqual(owner, ['home', 'pos', 'wallet', 'payments', 'payouts', 'my-shop', 'orders-list']);
  assert.deepEqual(staff, ['pos', 'wallet', 'payments', 'payouts', 'orders-list']);
  assert.ok(!staff.includes('my-shop'));
});

test('country finance grants expose only the transaction read route with a matching backend gate', () => {
  const catalog = [
    { name: 'dashboard', id: 'home', url: 'dashboard', type: 'single' },
    {
      name: 'transaction.management',
      id: 'finance',
      type: 'group',
      menus: [
        { name: 'transactions', id: 'transactions', url: 'transactions', type: 'single' },
        { name: 'payout.requests', id: 'payout-requests', url: 'payout-requests', type: 'single' },
        { name: 'payouts', id: 'admin.payouts', url: 'payouts', type: 'single' },
        { name: 'subscriptions', id: 13, url: 'subscriptions', type: 'single' },
        { name: 'shop.subscriptions', id: 14, url: 'shop-subscriptions', type: 'single' },
      ],
    },
  ];
  const grants = effectiveNavigationMenuIds(
    catalog,
    'manager',
    {
      user_id: 42,
      role: 'manager',
      scope_status: 'known',
      is_super_admin: false,
      country: { id: 9, name: 'Country' },
      country_source: 'restricted_country',
      country_scope: {
        permission_keys: [
          'currency.manage',
          'currency.view',
          'reports.view',
          'transactions.manage',
          'transactions.view',
        ],
      },
    },
    42,
  );

  assert.deepEqual(grants, ['home', 'transactions']);
});

test('shop_manager dashboard access requires reports.view and otherwise resolves to authorized bookings', () => {
  const catalog = [
    { name: 'dashboard', id: 'home', url: 'dashboard', type: 'single' },
    {
      name: 'booking.management',
      id: 'bookings',
      type: 'group',
      menus: [
        { name: 'bookings', id: 'booking-list', url: 'seller/bookings', type: 'single' },
      ],
    },
  ];
  const scope = {
    user_id: 17,
    role: 'shop_manager',
    scope_status: 'known',
    shop: { id: 8, name: 'Shop', owner: false },
    shop_scope: { permission_keys: ['bookings.view'] },
  };
  const grants = effectiveNavigationMenuIds(catalog, 'shop_manager', scope, 17);
  const destination = resolveStage1DashboardDestination({
    sourceMenus: catalog,
    role: 'shop_manager',
    context: scope,
    authenticatedUserId: 17,
  });

  assert.deepEqual(grants, ['booking-list']);
  assert.equal(destination.dashboardAuthorized, false);
  assert.equal(destination.destinationUrl, 'seller/bookings');
  assert.deepEqual(
    effectiveNavigationMenuIds(
      catalog,
      'shop_manager',
      { ...scope, shop: { ...scope.shop, owner: true } },
      17,
    ),
    ['booking-list'],
  );

  const withReportPermission = {
    ...scope,
    shop_scope: { permission_keys: ['bookings.view', 'reports.view'] },
  };
  assert.deepEqual(
    effectiveNavigationMenuIds(catalog, 'shop_manager', withReportPermission, 17),
    ['home', 'booking-list'],
  );
});

test('master receives only their distinct personal master menu, never seller owner groups', () => {
  const context = {
    user_id: 20,
    role: 'master',
    scope_status: 'known',
    shop: null,
    shop_scope: { unrestricted: false, permission_keys: [] },
  };
  const grants = effectiveNavigationMenuIds(masterMenu, 'master', context, 20);
  const result = resolveNavigation({
    sourceMenus: masterMenu,
    role: 'master',
    authorizedMenuIds: grants,
  });

  assert.deepEqual(grants, ['master-home', 'master-calendar', 'master-services', 'master-days']);
  assert.ok(!result.items.flatMap((section) => section.menus).some((item) => item.name === 'my.shop'));
});

test('deliveryman receives only the authenticated personal native catalogue', () => {
  const catalog = nativeRoleMenus.deliveryman;
  const context = {
    user_id: 402,
    role: 'deliveryman',
    scope_status: 'known',
    shop: { id: 7, name: 'Shop', owner: false },
    shop_scope: { unrestricted: false, permission_keys: [] },
  };
  const grants = effectiveNavigationMenuIds(catalog, 'deliveryman', context, 402);

  assert.deepEqual(Array.from(grants), ['dashboard', 'orders', 'withdraws']);
  assert.ok(!grants.includes('seller_masters_ master-invitations'));
  assert.deepEqual(
    resolveNavigation({
      sourceMenus: catalog,
      role: 'deliveryman',
      authorizedMenuIds: grants,
    })
      .items.flatMap((section) => section.menus)
      .map((item) => item.url),
    ['dashboard', 'deliveryman/orders', 'deliveryman/withdraws'],
  );
});

test('legacy waiter catalogue remains unsupported without a backend actor-role route', () => {
  const context = {
    user_id: 403,
    role: 'waiter',
    scope_status: 'known',
    shop: { id: 7, name: 'Shop', owner: false },
    shop_scope: { unrestricted: false, permission_keys: [] },
  };

  assert.deepEqual(
    Array.from(nativeRoleMenus.waiter, (item) => item.url),
    ['waiter/orders-board', 'waiter/orders'],
  );
  assert.equal(
    effectiveNavigationMenuIds(nativeRoleMenus.waiter, 'waiter', context, 403),
    null,
  );
});

test('global admin retains granted global menus and superadmin-only items', () => {
  const result = resolveNavigation({
    sourceMenus: globalAdminMenu,
    role: 'admin',
    isSuperAdmin: true,
    authorizedMenuIds: [
      'admin-home',
      'country',
      'country-admins',
      'transactions-page',
      'payouts',
      'settings-general',
      'platform-payments',
    ],
  });

  assert.equal(result.authorizationKnown, true);
  assert.ok(itemsInSection(result.items, 'OPERATIONS').some((item) => item.id === 'country-admins'));
  assert.ok(itemsInSection(result.items, 'FINANCE').some((item) => item.id === 'payouts'));
  assert.ok(itemsInSection(result.items, 'SETTINGS').some((item) => item.id === 'platform-payments'));
});

test('country administrator/manager remains country-scoped without superadmin-only routes', () => {
  const result = resolveNavigation({
    sourceMenus: managerMenu,
    role: 'manager',
    isSuperAdmin: false,
    authorizedMenuIds: ['admin-home', 'country', 'country-admins', 'settings-general'],
  });

  const visible = result.items.flatMap((section) => section.menus);
  assert.ok(visible.some((item) => item.id === 'country'));
  assert.ok(!visible.some((item) => item.id === 'country-admins'));
  assert.ok(!visible.some((item) => item.id === 'platform-payments'));
  assert.ok(visible.some((item) => item.url === 'dashboard'));
});

test('shop owner and shop staff with the same menu catalog get different granted capabilities', () => {
  const owner = resolveNavigation({
    sourceMenus: sellerMenu,
    role: 'seller',
    authorizedMenuIds: ['seller-home', 'my-shop', 'staff', 'seller-orders'],
  });
  const staff = resolveNavigation({
    sourceMenus: shopStaffMenu,
    role: 'shop_manager',
    authorizedMenuIds: ['seller-home', 'seller-orders'],
  });

  assert.ok(itemsInSection(owner.items, 'BUSINESS').some((item) => item.id === 'staff'));
  assert.ok(!itemsInSection(staff.items, 'BUSINESS').some((item) => item.id === 'staff'));
  assert.ok(itemsInSection(staff.items, 'PRODUCT COMMERCE').some((item) => item.id === 'seller-orders'));
});

test('master uses only its original master menu and retains personal work routes', () => {
  const result = resolveNavigation({
    sourceMenus: masterMenu,
    role: 'master',
    authorizedMenuIds: [
      'master-home',
      'master-calendar',
      'master-services',
      'master-days',
    ],
  });

  const visible = result.items.flatMap((section) => section.menus);
  assert.deepEqual(
    visible.map((item) => item.url),
    ['dashboard', 'master/calendar', 'master/service-master', 'master/closed-days'],
  );
  assert.ok(result.items.some((section) => section.stage1Title === 'BOOKING'));
});

test('unknown grants fail closed without a dashboard fallback', () => {
  const result = resolveNavigation({
    sourceMenus: sellerMenu,
    role: 'seller',
    authorizedMenuIds: undefined,
  });

  assert.equal(result.authorizationKnown, false);
  assert.deepEqual(result.items, []);
});

test('restricted and unassigned branch scope is displayed without a fabricated active branch', () => {
  assert.equal(
    formatVerifiedNavigationScope({
      country: { id: 3, name: 'Cameroon' },
      shop: { id: 6, name: 'Maison Naya' },
      branches: {
        unrestricted: false,
        location_ids: [21, 22],
        locations: [{ id: 21, name: 'Bonapriso' }, { id: 22, name: 'Akwa' }],
      },
    }),
    'Cameroon · Maison Naya · Branches: Bonapriso · Akwa',
  );
  assert.equal(
    formatVerifiedNavigationScope({
      shop: { id: 6, name: 'Maison Naya' },
      branches: { unrestricted: false, location_ids: [], locations: [] },
    }),
    'Maison Naya · No assigned branches',
  );
  assert.equal(
    formatVerifiedNavigationScope({
      shop: { id: 6, name: 'Maison Naya' },
      branches: { unrestricted: true, location_ids: [], locations: [] },
    }),
    'Maison Naya · All branches',
  );
});

test('nested parent/group items are pruned when no authorized children remain', () => {
  const source = [
    {
      name: 'product.management',
      id: 'products',
      type: 'group',
      menus: [
        {
          name: 'extras',
          id: 'extras-parent',
          type: 'parent',
          children: [
            { name: 'extra.group', id: 'extra-group', url: 'catalog/extras', type: 'single' },
            { name: 'extra.value', id: 'extra-value', url: 'catalog/extras/value', type: 'single' },
          ],
        },
      ],
    },
    {
      name: 'business.settings',
      id: 'page-setup',
      type: 'group',
      menus: [
        {
          name: 'page.setup',
          id: 'setup-parent',
          type: 'parent',
          children: [
            { name: 'faq', id: 'faq', url: 'settings/faqs', type: 'single' },
          ],
        },
      ],
    },
  ];
  const result = resolveNavigation({
    sourceMenus: source,
    role: 'admin',
    authorizedMenuIds: ['extra-value'],
  });
  const productSection = itemsInSection(result.items, 'PRODUCT COMMERCE');

  assert.equal(productSection.length, 1);
  assert.equal(productSection[0].children.length, 1);
  assert.equal(productSection[0].children[0].id, 'extra-value');
  assert.equal(itemsInSection(result.items, 'SETTINGS').length, 0);
});

test('existing feature flags and superadminOnly metadata still prune routes', () => {
  const result = resolveNavigation({
    sourceMenus: globalAdminMenu,
    role: 'manager',
    isSuperAdmin: false,
    authorizedMenuIds: ['admin-home', 'country', 'transactions-page', 'payouts'],
    disabledMenuNames: ['transaction.management'],
  });

  const visible = result.items.flatMap((section) => section.menus);
  assert.ok(visible.some((item) => item.id === 'country'));
  assert.ok(!visible.some((item) => item.id === 'payouts'));
  assert.ok(!visible.some((item) => item.id === 'country-admins'));
});

test('existing disabled-menu switches continue matching both native names and route URLs', () => {
  const result = resolveNavigation({
    sourceMenus: [
      { name: 'dashboard', id: 'home', url: 'dashboard', type: 'single' },
      { name: 'products', id: 'report-products', url: 'report/products', type: 'single' },
    ],
    role: 'admin',
    authorizedMenuIds: ['home', 'report-products'],
    disabledMenuNames: ['report/products'],
  });

  assert.deepEqual(
    result.items.flatMap((section) => section.menus).map((item) => item.id),
    ['home'],
  );
});

test('unsafe URLs and redirect-like paths never enter operational navigation', () => {
  for (const url of [
    'https://example.com',
    '//example.com',
    '/dashboard',
    '../login',
    'orders/../../login',
    'javascript:alert(1)',
    'dashboard#external',
    'orders?next=https://example.com',
  ]) {
    assert.equal(isSafeInternalMenuUrl(url), false, url);
  }
  assert.equal(isSafeInternalMenuUrl('orders?type=all'), true);

  const result = resolveNavigation({
    sourceMenus: [
      { name: 'dashboard', id: 'home', url: 'dashboard', type: 'single' },
      { name: 'redirect', id: 'unsafe', url: '//evil.example', type: 'single' },
    ],
    role: 'seller',
    authorizedMenuIds: ['unsafe'],
  });
  assert.deepEqual(
    result.items.flatMap((section) => section.menus).map((item) => item.url),
    [],
  );
});

test('active route selects the most specific existing URL, including nested paths', () => {
  const items = [
    {
      type: 'group',
      menus: [
        { id: 'orders', type: 'single', url: 'orders?type=all' },
        { id: 'seller-orders', type: 'single', url: 'orders/seller' },
      ],
    },
  ];
  assert.equal(findActiveNavigationItem(items, '/orders/seller/edit')?.id, 'seller-orders');
  assert.equal(findActiveNavigationItem(items, '/orders')?.id, 'orders');
});