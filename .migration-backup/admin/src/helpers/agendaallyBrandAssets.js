export const AGENDAALLY_BRAND_LOGO = '/brand/agendaally-logo.png';
export const AGENDAALLY_BRAND_MARK = '/brand/agendaally-mark.png';

const OWNED_BRAND_PATHS = {
  logo: '/storage/images/settings/agendaally-platform-logo.png',
  mark: '/storage/images/settings/agendaally-platform-mark.png',
};

export function resolveAgendaAllyBrandAsset(value, type) {
  if (typeof value !== 'string' || !value) {
    return value;
  }

  try {
    const path = new URL(value, 'https://agendaally-brand.invalid').pathname;
    if (path === OWNED_BRAND_PATHS[type]) {
      return type === 'logo' ? AGENDAALLY_BRAND_LOGO : AGENDAALLY_BRAND_MARK;
    }
  } catch {
    // Preserve malformed or custom values; this resolver only owns two exact paths.
  }

  return value;
}