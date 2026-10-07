"use client";

import { useSearchParams } from "next/navigation";

// Carries the same region/country/city/area/location_type context a search
// result was found with (see shop-card-horizontal's Link href) through to
// the shop detail page's own data fetching, so ShopResource::matched_location
// can resolve the branch the customer actually searched for/matched,
// instead of falling back to the shop's flat, single address - see
// ShopResource::toArray() on the backend for the matching logic this mirrors.
export const useShopLocationParams = () => {
  const searchParams = useSearchParams();

  const params: Record<string, string> = {};
  ["region_id", "country_id", "city_id", "area_id", "location_type"].forEach((key) => {
    const value = searchParams.get(key);
    if (value) {
      params[key] = value;
    }
  });

  return params;
};
