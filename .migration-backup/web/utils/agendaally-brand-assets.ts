export const AGENDAALLY_BRAND_LOGO = "/brand/agendaally-logo.png";
export const AGENDAALLY_BRAND_MARK = "/brand/agendaally-mark.png";
// Immutable URL for the approved compact symbol in browser-tab caches.
// Header/auth branding still uses the original shared mark URL.
export const AGENDAALLY_STOREFRONT_FAVICON = "/brand/agendaally-mark.0e2bbcff.png";

type BrandAssetType = "logo" | "mark";

const OWNED_BRAND_PATHS: Record<BrandAssetType, string> = {
  logo: "/storage/images/settings/agendaally-platform-logo.png",
  mark: "/storage/images/settings/agendaally-platform-mark.png",
};

export const resolveAgendaAllyBrandAsset = (
  value: string | null | undefined,
  type: BrandAssetType
): string | undefined => {
  if (!value) {
    return value || undefined;
  }

  try {
    const path = new URL(value, "https://agendaally-brand.invalid").pathname;
    if (path === OWNED_BRAND_PATHS[type]) {
      return type === "logo" ? AGENDAALLY_BRAND_LOGO : AGENDAALLY_BRAND_MARK;
    }
  } catch {
    // Preserve malformed or custom values; this resolver only owns two exact paths.
  }

  return value;
};

export const resolveStorefrontFavicon = (value: string | null | undefined): string => {
  const resolved = resolveAgendaAllyBrandAsset(value, "mark") || AGENDAALLY_BRAND_MARK;
  return resolved === AGENDAALLY_BRAND_MARK ? AGENDAALLY_STOREFRONT_FAVICON : resolved;
};