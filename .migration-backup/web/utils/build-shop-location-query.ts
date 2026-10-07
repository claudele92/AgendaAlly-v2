import { Shop, ShopLocationEntry } from "@/types/shop";

// Builds the region/country/city/area/location_type query string for a
// single branch - see useShopLocationParams, which reads these same keys
// back out on the receiving end. Shared by buildShopLocationQuery (search
// result cards) and the branch switcher (location.tsx), so both carry a
// branch forward the exact same way.
export const buildLocationQuery = (location?: ShopLocationEntry): string => {
  if (!location) {
    return "";
  }

  const params = new URLSearchParams();
  if (location.region_id) params.set("region_id", String(location.region_id));
  if (location.country_id) params.set("country_id", String(location.country_id));
  if (location.city_id) params.set("city_id", String(location.city_id));
  if (location.area_id) params.set("area_id", String(location.area_id));
  if (location.type) params.set("location_type", String(location.type));

  const query = params.toString();
  return query ? `?${query}` : "";
};

// Carries the branch a search result actually matched (see
// ShopResource::matched_location) forward as URL params, so the shop detail
// page can resolve the same branch instead of falling back to the shop's
// flat address.
export const buildShopLocationQuery = (data: Shop): string => buildLocationQuery(data.matched_location);
