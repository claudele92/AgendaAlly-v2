export function getDeliveryDriverPermissions(user, navigationScope) {
  const context = navigationScope?.scope;
  const matched =
    navigationScope?.status === 'ready' &&
    navigationScope?.sessionMatches === true &&
    context?.scope_status === 'known' &&
    context?.role === user?.role &&
    user?.id !== null &&
    user?.id !== undefined &&
    String(context?.user_id) === String(user.id);
  const shopKeys = context?.shop_scope?.permission_keys;
  const validShopActor = ['seller', 'moderator', 'shop_manager'].includes(
    user?.role,
  );
  const canInvite =
    matched &&
    validShopActor &&
    Boolean(context?.shop) &&
    Array.isArray(shopKeys) &&
    shopKeys.includes('staff.invite');
  const canView =
    matched &&
    validShopActor &&
    Boolean(context?.shop) &&
    Array.isArray(shopKeys) &&
    shopKeys.includes('staff.view');

  return { canView, canInvite, shopId: context?.shop?.id || null };
}