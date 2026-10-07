function matchedNavigationContext(user, navigationScope) {
  const context = navigationScope?.scope;
  const authenticatedUserId = user?.id;
  if (
    navigationScope?.status !== 'ready' ||
    navigationScope?.sessionMatches !== true ||
    context?.scope_status !== 'known' ||
    context?.role !== user?.role ||
    authenticatedUserId === null ||
    authenticatedUserId === undefined ||
    String(context?.user_id) !== String(authenticatedUserId)
  ) {
    return null;
  }

  return context;
}

function hasVerifiedShopPermission(user, navigationScope, permission) {
  const context = matchedNavigationContext(user, navigationScope);
  const permissionKeys = context?.shop_scope?.permission_keys;
  return (
    ['seller', 'moderator', 'shop_manager'].includes(user?.role) &&
    !!context?.shop &&
    Array.isArray(permissionKeys) &&
    permissionKeys.includes(permission)
  );
}

function hasVerifiedCountryPermission(user, navigationScope, permission) {
  const context = matchedNavigationContext(user, navigationScope);
  const permissionKeys = context?.country_scope?.permission_keys;
  return (
    ['admin', 'manager'].includes(user?.role) &&
    !!context?.country &&
    context?.country_source === 'restricted_country' &&
    Array.isArray(permissionKeys) &&
    permissionKeys.includes(permission)
  );
}

function isVerifiedSuperAdmin(user, navigationScope) {
  const context = matchedNavigationContext(user, navigationScope);
  return (
    ['admin', 'manager'].includes(user?.role) &&
    context?.is_super_admin === true
  );
}

function isStructuralShopOwner(user, navigationScope) {
  const context = matchedNavigationContext(user, navigationScope);
  return (
    ['seller', 'moderator'].includes(user?.role) &&
    context?.shop?.owner === true &&
    Array.isArray(context?.shop_scope?.permission_keys)
  );
}

export function hasVerifiedShopManagerPermission(
  user,
  navigationScope,
  permission,
) {
  return (
    user?.role === 'shop_manager' &&
    hasVerifiedShopPermission(user, navigationScope, permission)
  );
}

/**
 * Resolve which existing statistics API family a dashboard may use.
 *
 * shop_manager is a supported actor on the seller dashboard routes, but those
 * metrics are reports and must be backed by the authenticated shop scope's
 * actual reports.view grant. Never infer this from role, branch assignments,
 * or MyShop data.
 */
export function resolveDashboardStatisticsRole(user, navigationScope) {
  const role = user?.role;
  if (role === 'admin' || role === 'manager') return 'admin';
  if (role === 'seller' || role === 'moderator') return 'seller';
  if (
    role === 'shop_manager' &&
    hasVerifiedShopManagerPermission(user, navigationScope, 'reports.view')
  ) {
    return 'seller';
  }

  return null;
}

/**
 * Resolve MainCards' existing orders destination. Dashboard reports never
 * imply permission to open a restricted actor's orders list.
 */
export function resolveDashboardOrdersPath(user, navigationScope) {
  const role = user?.role;
  if (role === 'admin' || role === 'manager') {
    if (isVerifiedSuperAdmin(user, navigationScope)) {
      return role === 'admin' ? 'orders?type=all' : 'orders';
    }
    return hasVerifiedCountryPermission(user, navigationScope, 'orders.view')
      ? role === 'admin'
        ? 'orders?type=all'
        : 'orders'
      : null;
  }
  if (['seller', 'moderator', 'shop_manager'].includes(role)) {
    if (role !== 'shop_manager' && isStructuralShopOwner(user, navigationScope)) {
      return 'seller/orders';
    }
    return hasVerifiedShopPermission(user, navigationScope, 'orders.view')
      ? 'seller/orders'
      : null;
  }
  if (role === 'deliveryman') {
    return matchedNavigationContext(user, navigationScope)
      ? 'deliveryman/orders'
      : null;
  }
  return null;
}

export function resolveDashboardBookingsPath(user, navigationScope) {
  const role = user?.role;
  if (role === 'admin' || role === 'manager') {
    if (isVerifiedSuperAdmin(user, navigationScope)) return 'booking';
    return hasVerifiedCountryPermission(user, navigationScope, 'bookings.view')
      ? 'booking'
      : null;
  }
  if (['seller', 'moderator'].includes(role)) {
    if (isStructuralShopOwner(user, navigationScope)) return 'seller/bookings';
    return hasVerifiedShopPermission(user, navigationScope, 'bookings.view')
      ? 'seller/bookings'
      : null;
  }
  if (role === 'master') {
    return matchedNavigationContext(user, navigationScope)
      ? 'master/calendar'
      : null;
  }
  return null;
}