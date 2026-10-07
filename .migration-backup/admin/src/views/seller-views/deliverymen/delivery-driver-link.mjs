export function invitationResponsePayload(response) {
  const outer = response?.data || response;
  return outer?.data && typeof outer.data === 'object' ? outer.data : outer;
}

export function normalizeDriverInvitationLink(value) {
  if (typeof value !== 'string' || !value.trim()) return '';

  try {
    const url = new URL(value.trim(), window.location.origin);
    if (
      url.origin !== window.location.origin ||
      url.pathname !== '/delivery-driver-invitation' ||
      url.search
    ) {
      return '';
    }

    const fragment = new URLSearchParams(url.hash.slice(1));
    if (Array.from(fragment).length !== 1 || !fragment.get('token')) return '';

    return new URL(value.trim(), window.location.origin).href;
  } catch {
    return '';
  }
}