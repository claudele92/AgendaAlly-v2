const COUNTRY_ADMIN_ROLES = new Set(['admin', 'manager']);
const SHOP_ROLES_WITH_OWN_SHOP_LOOKUP = new Set(['seller', 'moderator']);

function verifiedScopeForSession(user, navigationScope) {
  if (
    !user ||
    navigationScope?.status !== 'ready' ||
    navigationScope.sessionMatches !== true
  ) {
    return null;
  }

  const scope = navigationScope.scope;
  if (
    !scope ||
    scope.scope_status !== 'known' ||
    scope.user_id === null ||
    scope.user_id === undefined ||
    String(scope.user_id) !== String(user.id) ||
    scope.role !== user.role
  ) {
    return null;
  }

  return scope;
}

/**
 * Return only common startup reads that are supported by the authenticated
 * actor's fresh scope. Formatting currencies always use the existing public
 * REST catalog; protected management lists require their matching view grant.
 */
export function resolveAppLayoutBootstrapPlan(user, navigationScope) {
  const scope = verifiedScopeForSession(user, navigationScope);
  const role = user?.role;
  const countryPermissions = new Set(
    Array.isArray(scope?.country_scope?.permission_keys)
      ? scope.country_scope.permission_keys
      : [],
  );
  const shopPermissions = new Set(
    Array.isArray(scope?.shop_scope?.permission_keys)
      ? scope.shop_scope.permission_keys
      : [],
  );
  const isSuperAdmin =
    scope?.is_super_admin === true && COUNTRY_ADMIN_ROLES.has(role);

  return {
    currencySource: 'rest/currencies',
    fetchAdminShops:
      COUNTRY_ADMIN_ROLES.has(role) &&
      Boolean(scope) &&
      (isSuperAdmin || countryPermissions.has('vendors.view')),
    fetchMyShop:
      SHOP_ROLES_WITH_OWN_SHOP_LOOKUP.has(role) &&
      Boolean(scope?.shop) &&
      ((role === 'seller' && scope.shop.owner === true) ||
        shopPermissions.has('shop_settings.view')),
  };
}