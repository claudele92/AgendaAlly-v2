export const LEGACY_PUBLIC_ROUTE_REDIRECT = "/login";

export function getAuthenticatedDestination(menuActive) {
  return `/${menuActive ? menuActive.url : ""}`;
}

export function getAuthRouteDestination(user, menuActive) {
  return user
    ? getAuthenticatedDestination(menuActive)
    : LEGACY_PUBLIC_ROUTE_REDIRECT;
}