// Reads the same region/country/city/area/location_type params a search
// result's link (or the branch switcher) carries forward - see
// buildShopLocationQuery / location.tsx - so any page fetching a shop can
// resolve ShopResource::matched_location to the branch the customer
// actually came from, instead of the shop's flat/default one. Shared so
// every shop-fetching page under shops/[id] resolves the same branch
// consistently, rather than each page needing its own copy.
const shopLocationSearchKeys = ["region_id", "country_id", "city_id", "area_id", "location_type"];

export const extractShopLocationParams = (
  searchParams: Record<string, string | string[] | undefined>
): Record<string, string> => {
  const params: Record<string, string> = {};
  shopLocationSearchKeys.forEach((key) => {
    const value = searchParams[key];
    if (typeof value === "string") {
      params[key] = value;
    }
  });
  return params;
};
