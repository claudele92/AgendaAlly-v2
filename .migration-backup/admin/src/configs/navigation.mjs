const STAGE1_SECTIONS = [
  'OVERVIEW',
  'BOOKING',
  'PRODUCT COMMERCE',
  'BUSINESS',
  'FINANCE',
  'REPORTING',
  'GROWTH',
  'SETTINGS',
  'OPERATIONS',
];

const SECTION_BY_SOURCE_GROUP = {
  'analytics.and.reports': 'REPORTING',
  'booking.management': 'BOOKING',
  'business.settings': 'SETTINGS',
  business: 'BUSINESS',
  content: 'GROWTH',
  'content.management': 'GROWTH',
  'delivery.management': 'BUSINESS',
  deliveryman: 'BUSINESS',
  'invitation.management': 'BUSINESS',
  'marketing.management': 'GROWTH',
  'order.management': 'PRODUCT COMMERCE',
  'parcel.order': 'PRODUCT COMMERCE',
  'product.management': 'PRODUCT COMMERCE',
  'service.management': 'BOOKING',
  'shop.management': 'BUSINESS',
  'staff.management': 'BUSINESS',
  'system.settings': 'SETTINGS',
  transaction: 'FINANCE',
  'transaction.management': 'FINANCE',
  'user.management': 'FINANCE',
  'zone.management': 'OPERATIONS',
};

const SECTION_BY_ITEM_NAME = {
  'email.subscriber': 'SETTINGS',
  dashboard: 'OVERVIEW',
  calendar: 'BOOKING',
  'booking.management': 'BOOKING',
  bookings: 'BOOKING',
  'service.master': 'BOOKING',
  'closed.days': 'BOOKING',
  'disabled.times': 'BOOKING',
  'pos.system': 'PRODUCT COMMERCE',
  pos: 'PRODUCT COMMERCE',
  warehouse: 'PRODUCT COMMERCE',
  'order.management': 'PRODUCT COMMERCE',
  'product.management': 'PRODUCT COMMERCE',
  'shop.management': 'BUSINESS',
  'staff.management': 'BUSINESS',
  'invitation.management': 'BUSINESS',
  'analytics.and.reports': 'REPORTING',
  'transaction.management': 'FINANCE',
  transaction: 'FINANCE',
  wallet: 'FINANCE',
  'business.settings': 'SETTINGS',
  'system.settings': 'SETTINGS',
};

const SAFE_ROUTE_PATH = /^[A-Za-z0-9._~-]+(?:\/[A-Za-z0-9._~-]+)*$/;
const SAFE_QUERY =
  /^[A-Za-z0-9_.~-]+=[A-Za-z0-9_.~-]+(?:&[A-Za-z0-9_.~-]+=[A-Za-z0-9_.~-]+)*$/;

/**
 * These are the native admin's existing product-off switches. They are kept
 * separate from authorization: a feature flag can remove an existing route,
 * but can never grant one.
 */
export const PRODUCT_DISABLED_MENU_NAMES = [
  'deliverymen',
  'pos.system',
  'delivery.price',
  'order.management',
  'product.management',
  'product.bonus',
  'ads',
  'ad.packages',
  'brands',
  'report/products',
  'products',
];

const COUNTRY_PERMISSION_KEYS = [
  'vendors.view',
  'vendors.manage',
  'orders.view',
  'orders.manage',
  'bookings.view',
  'bookings.manage',
  'transactions.view',
  'transactions.manage',
  'reports.view',
  'staff.view',
  'staff.invite',
  'staff.roles.manage',
  'products.view',
  'products.manage',
  'reviews.view',
  'reviews.manage',
  'tickets.view',
  'tickets.manage',
  'geography.manage',
  'marketing.view',
  'marketing.manage',
  'currency.view',
  'currency.manage',
];

const SHOP_PERMISSION_KEYS = [
  'stories.view',
  'stories.manage',
  'bookings.view',
  'bookings.manage',
  'bookings.status',
  'bookings.availability',
  'bookings.view_all_branches',
  'payments.view',
  'payments.payouts.manage',
  'payments.gateways.manage',
  'payments.refunds.manage',
  'services.view',
  'services.manage',
  'services.auctions.manage',
  'staff.view',
  'staff.invite',
  'staff.roles.manage',
  'reports.view',
  'products.view',
  'products.manage',
  'orders.view',
  'orders.manage',
  'orders.delivery_settings',
  'marketing.view',
  'marketing.manage',
  'shop_settings.view',
  'shop_settings.manage',
  'customers.view',
];

const COUNTRY_READ_PERMISSION_KEYS = [
  'vendors.view',
  'orders.view',
  'bookings.view',
  'transactions.view',
  'reports.view',
  'staff.view',
  'products.view',
  'reviews.view',
  'tickets.view',
  'marketing.view',
  'currency.view',
];

const SHOP_READ_PERMISSION_KEYS = [
  'stories.view',
  'bookings.view',
  'payments.view',
  'services.view',
  'staff.view',
  'reports.view',
  'products.view',
  'orders.view',
  'marketing.view',
  'shop_settings.view',
  'customers.view',
];

export function isSafeInternalMenuUrl(url) {
  if (typeof url !== 'string' || !url || url.startsWith('/') || url.includes('\\')) {
    return false;
  }

  const [path, ...queryParts] = url.split('?');
  if (
    !path ||
    path.split('/').some((segment) => segment === '.' || segment === '..') ||
    !SAFE_ROUTE_PATH.test(path)
  ) {
    return false;
  }

  if (queryParts.length > 1) return false;
  return queryParts.length === 0 || SAFE_QUERY.test(queryParts[0]);
}

function normalizedIds(ids) {
  if (!Array.isArray(ids)) return new Set();
  return new Set(ids.filter((id) => id !== null && id !== undefined).map(String));
}

function filterByAuthorization(items, options) {
  if (!Array.isArray(items)) return [];
  const {
    grantedIds,
    disabledNames,
    isSuperAdmin,
  } = options;

  return items.reduce((filtered, item) => {
    if (!item || typeof item !== 'object') return filtered;
    if (disabledNames.has(item.name) || disabledNames.has(item.url)) {
      return filtered;
    }
    if (item.superadminOnly && !isSuperAdmin) return filtered;

    if (Array.isArray(item.menus)) {
      const menus = filterByAuthorization(item.menus, options);
      if (menus.length) filtered.push({ ...item, menus });
      return filtered;
    }

    if (Array.isArray(item.children)) {
      const children = filterByAuthorization(item.children, options);
      if (children.length) filtered.push({ ...item, children });
      return filtered;
    }

    if (
      item.type === 'single' &&
      isSafeInternalMenuUrl(item.url) &&
      grantedIds.has(String(item.id))
    ) {
      filtered.push({ ...item });
    }
    return filtered;
  }, []);
}

function assignSection(item, sourceGroupName, role) {
  if (role === 'master') {
    return item.name === 'dashboard' ? 'OVERVIEW' : 'BOOKING';
  }
  // This legacy group lived under user management; email configuration belongs
  // in Settings. Keep the same verified IDs/grants and every other grouping.
  if (item.name === 'email.subscriber') return 'SETTINGS';
  return (
    SECTION_BY_SOURCE_GROUP[sourceGroupName] ||
    SECTION_BY_ITEM_NAME[item.name] ||
    'OPERATIONS'
  );
}

function sourceEntries(items) {
  return items.flatMap((item) => {
    if (item?.type === 'group' && Array.isArray(item.menus)) {
      return item.menus.map((child) => ({
        item: child,
        sourceGroupName: item.name,
      }));
    }
    return [{ item, sourceGroupName: null }];
  });
}

function groupStage1Sections(items, role) {
  const sections = new Map(STAGE1_SECTIONS.map((title) => [title, []]));
  sourceEntries(items).forEach(({ item, sourceGroupName }) => {
    const section = assignSection(item, sourceGroupName, role);
    sections.get(section).push(item);
  });

  return STAGE1_SECTIONS.filter((title) => sections.get(title).length).map(
    (title) => ({
      id: `stage1-${title.toLowerCase().replace(/[^a-z0-9]+/g, '-')}`,
      name: title,
      stage1Title: title,
      type: 'group',
      menus: sections.get(title),
    }),
  );
}

/**
 * Filter an existing menu catalogue by IDs supplied by an authenticated,
 * server-authoritative navigation-scope response, then group the unchanged
 * native route nodes into the approved Stage 1 information architecture.
 *
 * `authorizedMenuIds` is deliberately required. Role names, `user.urls`, and
 * the role-editor permission catalogue are not access grants.
 */
export function resolveNavigation({
  sourceMenus,
  authorizedMenuIds,
  isSuperAdmin = false,
  disabledMenuNames = [],
  role,
}) {
  const catalog = Array.isArray(sourceMenus) ? sourceMenus : [];
  const authorizationKnown = Array.isArray(authorizedMenuIds);
  const grantedIds = normalizedIds(authorizedMenuIds);
  const disabledNames = new Set(
    Array.isArray(disabledMenuNames) ? disabledMenuNames : [],
  );

  const filtered = authorizationKnown
    ? filterByAuthorization(catalog, {
        grantedIds,
        disabledNames,
        isSuperAdmin: isSuperAdmin === true,
      })
    : [];

  return {
    authorizationKnown,
    items: groupStage1Sections(filtered, role),
  };
}

/**
 * Resolve the native Stage 1 dashboard destination from the same verified
 * grants and feature switches used by the sidebar. A denied dashboard can
 * route only to a different currently authorized native menu item.
 */
export function resolveStage1DashboardDestination({
  sourceMenus,
  role,
  context,
  authenticatedUserId,
  isSuperAdmin = false,
  disabledMenuNames = [],
}) {
  const authorizedMenuIds = effectiveNavigationMenuIds(
    sourceMenus,
    role,
    context,
    authenticatedUserId,
  );
  const resolved = resolveNavigation({
    sourceMenus,
    role,
    authorizedMenuIds,
    isSuperAdmin,
    disabledMenuNames,
  });
  const leaves = [];
  const collectLeaves = (items) => {
    items.forEach((item) => {
      if (item?.type === 'single') {
        leaves.push(item);
        return;
      }
      collectLeaves(item?.menus || item?.children || []);
    });
  };
  collectLeaves(resolved.items);

  return {
    authorizationKnown: resolved.authorizationKnown,
    dashboardAuthorized: leaves.some((item) => item.name === 'dashboard'),
    destinationUrl: leaves.find((item) => item.name !== 'dashboard')?.url || null,
  };
}

function permissionSet(keys, supportedKeys) {
  const supported = new Set(supportedKeys);
  return new Set(
    Array.isArray(keys)
      ? keys.filter((key) => typeof key === 'string' && supported.has(key))
      : [],
  );
}

function hasAny(keys, ...required) {
  return required.some((key) => keys.has(key));
}

function routePath(item) {
  return menuPath(item?.url);
}

function hasName(item, ...names) {
  return names.includes(item?.name);
}

function countryMenuAuthorized(item, sourceGroupName, keys) {
  const name = item?.name;
  const path = routePath(item);

  if (sourceGroupName === 'analytics.and.reports') return keys.has('reports.view');
  if (sourceGroupName === 'parcel.order') {
    if (name === 'all.orders') return keys.has('orders.view');
    if (name === 'order.status' || path.includes('orderstatus')) {
      return keys.has('orders.manage');
    }
    return false;
  }
  if (name === 'warehouse') {
    return keys.has('products.view');
  }
  if (name === 'order.status' || path.includes('orderstatus')) {
    // This native settings route is a status-management action, not the
    // orders listing endpoint guarded by orders.view.
    return keys.has('orders.manage');
  }
  if (path.startsWith('reviews/')) {
    return keys.has('reviews.view');
  }
  if (sourceGroupName === 'booking.management' || name === 'calendar') {
    return keys.has('bookings.view');
  }
  if (sourceGroupName === 'transaction.management') {
    if (name === 'transactions') return keys.has('transactions.view');
    if (name === 'Manual finance requests' || path === 'manual-finance') {
      return hasAny(keys, 'transactions.view', 'transactions.manage', 'orders.manage');
    }
    // These native pages read admin wallet histories, platform payouts,
    // subscription plans, or all shop subscriptions. Their current GET routes
    // have neither a country.permission gate nor country-scoped repositories.
    // A restricted country grant therefore cannot authorize them; verified
    // platform superadmins still receive the native catalogue above.
    return false;
  }
  if (sourceGroupName === 'transaction') {
    if (name === 'Shop payout requests' || path === 'seller/manual-payout-requests') {
      return hasAny(keys, 'payments.view', 'payments.payouts.manage');
    }
    return keys.has('transactions.view');
  }
  if (
    sourceGroupName === 'shop.management' &&
    (hasName(item, 'shops', 'my.shop') || path.includes('shops'))
  ) {
    return keys.has('vendors.view');
  }
  if (
    sourceGroupName === 'shop.management' &&
    (name === 'shop.reviews' || path.includes('shop-review'))
  ) {
    return keys.has('reviews.view');
  }
  if (sourceGroupName === 'product.management') {
    return keys.has('products.view');
  }
  if (
    sourceGroupName === 'content.management' &&
    (name === 'brands' || path.includes('brand'))
  ) {
    return keys.has('products.view');
  }
  if (sourceGroupName === 'marketing.management') {
    return keys.has('marketing.view');
  }
  if (
    sourceGroupName === 'zone.management' &&
    (name === 'country.staff' ||
      path.includes('country-admin/staff') ||
      path.includes('country-roles') ||
      path.includes('country-invites'))
  ) {
    return keys.has('staff.view');
  }
  if (sourceGroupName === 'zone.management') {
    return keys.has('geography.manage');
  }
  if (
    sourceGroupName === 'delivery.management' &&
    (name === 'delivery.point' || path.includes('delivery-point'))
  ) {
    return keys.has('geography.manage');
  }
  if (sourceGroupName === 'business.settings' && name === 'currencies') {
    return keys.has('currency.view');
  }
  if (
    sourceGroupName === 'user.management' &&
    (path.includes('withdraw') || path.includes('payout'))
  ) {
    return keys.has('transactions.view');
  }
  if (sourceGroupName === 'user.management' && name === 'wallets') {
    return keys.has('transactions.view');
  }
  if (sourceGroupName === 'order.management') {
    // Country refunds are a dedicated manage workflow; the shop refunds
    // listing below has a separate payments.view read gate.
    if (name === 'refunds') return keys.has('orders.manage');
    return keys.has('orders.view');
  }
  if (name === 'pos' || name === 'pos.system') {
    return keys.has('orders.view');
  }
  if (path.startsWith('ticket')) {
    return keys.has('tickets.view');
  }
  if (name === 'dashboard') return hasAny(keys, ...COUNTRY_READ_PERMISSION_KEYS);
  return false;
}

function shopMenuAuthorized(item, sourceGroupName, keys, role) {
  const name = item?.name;
  const path = routePath(item);

  if (
    sourceGroupName === 'booking.management' ||
    name === 'calendar'
  ) {
    if (name === 'bookings-report' || path.includes('bookings-report')) {
      return keys.has('reports.view');
    }
    return keys.has('bookings.view');
  }
  if (sourceGroupName === 'service.management') {
    return keys.has('services.view');
  }
  if (
    sourceGroupName === 'staff.management' ||
    sourceGroupName === 'invitation.management' ||
    path.includes('seller/staff') ||
    path.includes('seller/shop-users')
  ) {
    return keys.has('staff.view');
  }
  if (sourceGroupName === 'shop.management') {
    if (name === 'my.shop') {
      return keys.has('shop_settings.view');
    }
    if (name === 'users' || path.includes('seller/shop-users')) {
      return keys.has('staff.view');
    }
    return false;
  }
  if (sourceGroupName === 'order.management') {
    // The refunds page immediately reads order-refunds, whose seller route is
    // guarded by payments.view; refunds.manage only authorizes mutations.
    if (name === 'refunds') return keys.has('payments.view');
    if (name === 'order.status' || path.includes('orderstatus')) {
      return keys.has('orders.manage');
    }
    return keys.has('orders.view');
  }
  if (sourceGroupName === 'transaction' || name === 'wallet') {
    return keys.has('payments.view');
  }
  if (sourceGroupName === 'product.management') {
    if (name === 'discounts') return keys.has('marketing.view');
    if (name === 'product.reviews' || path.includes('reviews/product')) {
      return false;
    }
    return keys.has('products.view');
  }
  if (sourceGroupName === 'marketing.management') {
    return keys.has('marketing.view');
  }
  if (sourceGroupName === 'analytics.and.reports') {
    return keys.has('reports.view');
  }
  if (sourceGroupName === 'business' && name === 'ad.packages') {
    return keys.has('marketing.view');
  }
  if (sourceGroupName === 'content') {
    if (name === 'stories') return keys.has('stories.view');
    if (name === 'brands') return keys.has('products.view');
    if (name === 'form.options') return keys.has('services.view');
    return false;
  }
  if (name === 'pos.system') {
    return keys.has('orders.view');
  }
  if (name === 'delivery.price') {
    return keys.has('orders.delivery_settings');
  }
  if (name === 'dashboard') {
    // Dashboard statistic endpoints are reports, not a side effect of having
    // any operational shop permission. Invited shop managers need the real
    // reports grant; otherwise Stage 1 routes them to an authorized page.
    if (role === 'shop_manager') return keys.has('reports.view');
    return hasAny(keys, ...SHOP_READ_PERMISSION_KEYS);
  }

  // Several sellers' native menu entries have no matching current
  // shop_permissions capability. They remain visible to the structural
  // owner, but are not implicitly granted to invited staff.
  return false;
}

/**
 * Convert the existing backend's effective domain grants into IDs from the
 * selected native role menu. No role editor catalog or user.urls list is used.
 * A null result means scope/grants are unknown and operational routes must
 * fail closed.
 */
export function effectiveNavigationMenuIds(
  sourceMenus,
  role,
  context,
  authenticatedUserId,
) {
  if (
    !Array.isArray(sourceMenus) ||
    !context ||
    context.scope_status === 'unknown' ||
    context.user_id === null ||
    context.user_id === undefined ||
    authenticatedUserId === null ||
    authenticatedUserId === undefined ||
    String(context.user_id) !== String(authenticatedUserId) ||
    context.role !== role
  ) {
    return null;
  }

  const allCatalogIds = [];
  const collectIds = (items) => {
    items.forEach((item) => {
      if (item?.type === 'single' && isSafeInternalMenuUrl(item.url)) {
        allCatalogIds.push(item.id);
      }
      collectIds(item?.menus || item?.children || []);
    });
  };

  // Masters and deliverymen have separate personal native catalogues, and
  // their corresponding role middleware scopes their personal routes. Neither
  // role inherits seller/shop-owner menu permissions.
  if (role === 'master' || role === 'deliveryman') {
    collectIds(sourceMenus);
    return allCatalogIds;
  }

  // The sole global bypass is the server-verified platform superadmin flag.
  // Country managers with the same coarse role remain country-scoped.
  if ((role === 'admin' || role === 'manager') && context.is_super_admin === true) {
    collectIds(sourceMenus);
    return allCatalogIds;
  }

  let domain;
  let keys;
  if (role === 'admin' || role === 'manager') {
    if (
      !context.country ||
      context.country_source !== 'restricted_country' ||
      !Array.isArray(context.country_scope?.permission_keys)
    ) {
      return null;
    }
    domain = 'country';
    keys = permissionSet(
      context.country_scope.permission_keys,
      COUNTRY_PERMISSION_KEYS,
    );
  } else if (['seller', 'shop_manager', 'moderator'].includes(role)) {
    if (
      !context.shop ||
      !context.shop_scope ||
      !Array.isArray(context.shop_scope.permission_keys)
    ) {
      return null;
    }
    if (role !== 'shop_manager' && context.shop.owner === true) {
      collectIds(sourceMenus);
      return allCatalogIds;
    }
    domain = 'shop';
    keys = permissionSet(context.shop_scope.permission_keys, SHOP_PERMISSION_KEYS);
  } else {
    return null;
  }

  const authorizedIds = [];
  const walk = (items, sourceGroupName = null) => {
    items.forEach((item) => {
      if (item?.type === 'group' && Array.isArray(item.menus)) {
        walk(item.menus, item.name);
        return;
      }
      if (item?.type === 'single' && isSafeInternalMenuUrl(item.url)) {
        const authorized =
          domain === 'country'
            ? countryMenuAuthorized(item, sourceGroupName, keys)
            : shopMenuAuthorized(item, sourceGroupName, keys, role);
        if (authorized) authorizedIds.push(item.id);
      }
      walk(item?.menus || item?.children || [], sourceGroupName);
    });
  };
  walk(sourceMenus);
  return authorizedIds;
}

function menuPath(url) {
  return typeof url === 'string' ? url.split('?')[0].replace(/^\/+|\/+$/g, '') : '';
}

/**
 * Match the current native route without substring collisions; prefer the
 * most specific route for nested pages and ignore a menu item's query string.
 */
export function findActiveNavigationItem(items, pathname) {
  const currentPath = menuPath(pathname);
  if (!currentPath) return null;

  const candidates = [];
  const visit = (nodes) => {
    if (!Array.isArray(nodes)) return;
    nodes.forEach((item) => {
      if (item?.type === 'single' && isSafeInternalMenuUrl(item.url)) {
        const itemPath = menuPath(item.url);
        if (
          itemPath &&
          (currentPath === itemPath || currentPath.startsWith(`${itemPath}/`))
        ) {
          candidates.push({ item, length: itemPath.length });
        }
      }
      visit(item?.menus);
      visit(item?.children);
    });
  };
  visit(items);
  candidates.sort((left, right) => right.length - left.length);
  return candidates[0]?.item || null;
}

/**
 * Render only scope values explicitly supplied by the authenticated scope
 * response. No role-derived country, shop, branch, or active-location value
 * is inferred here.
 */
export function formatVerifiedNavigationScope(scope) {
  if (!scope || typeof scope !== 'object') return null;

  const labels = [];
  if (typeof scope.country?.name === 'string' && scope.country.name.trim()) {
    labels.push(scope.country.name.trim());
  }
  if (typeof scope.shop?.name === 'string' && scope.shop.name.trim()) {
    labels.push(scope.shop.name.trim());
  }

  if (scope.branches?.unrestricted === true) {
    labels.push('All branches');
  } else if (scope.branches && typeof scope.branches === 'object') {
    const locationIds = Array.isArray(scope.branches.location_ids)
      ? scope.branches.location_ids
      : Array.isArray(scope.branches.locations)
        ? scope.branches.locations
        : null;
    const locations = Array.isArray(scope.branches.locations)
      ? scope.branches.locations
      : [];
    const branchNames = locations
      .map((location) =>
        typeof location === 'string' ? location.trim() : location?.name,
      )
      .filter((name) => typeof name === 'string' && name.trim())
      .map((name) => name.trim());
    const assignedCount = locationIds?.length ?? locations.length;
    if (assignedCount === 0) {
      labels.push('No assigned branches');
    } else if (assignedCount > 0) {
      if (branchNames.length) {
        const shownNames = branchNames.slice(0, 2);
        const remaining = Math.max(0, assignedCount - shownNames.length);
        labels.push(
          `Branches: ${shownNames.join(' · ')}${
            remaining ? ` · +${remaining} more` : ''
          }`,
        );
      } else {
        labels.push(`${assignedCount} assigned branches`);
      }
    }
  }

  return labels.length ? labels.join(' · ') : null;
}

export const stage1NavigationSections = STAGE1_SECTIONS;