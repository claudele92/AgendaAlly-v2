export function getVerifiedCalendarScope(user, navigationScope) {
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

export function getVerifiedCalendarShopId(user, navigationScope) {
  const shopId = getVerifiedCalendarScope(user, navigationScope)?.shop?.id;
  return shopId === null || shopId === undefined || shopId === ''
    ? null
    : shopId;
}

export function getInitialFormOptionId(options) {
  if (!Array.isArray(options)) return null;
  const firstOption = options.find(
    (option) => option?.id !== null && option?.id !== undefined,
  );
  return firstOption?.id ?? null;
}