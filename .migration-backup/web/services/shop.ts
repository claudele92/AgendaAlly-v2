import { DefaultResponse, Paginate, ParamsType } from "@/types/global";
import fetcher from "@/lib/fetcher";
import { buildUrlQueryParams } from "@/utils/build-url-query-params";
import { CreateShopCredentials, Shop, ShopGallery, ShopTag, ShopFilter } from "@/types/shop";

export const shopService = {
  // Unlike getById/getBySlug below, this had no explicit cache directive,
  // so it fell back to Next's ambient fetch-cache default - the actual
  // mechanism behind the homepage's carousels (Recommended/Deals/NearYou,
  // all built from this call with country_id/city_id filters) showing a
  // frozen shop list regardless of the visitor's real country cookie.
  // no-store matches the pattern already established below for exactly
  // this "always show current data" class of endpoint.
  getAll: (params?: ParamsType) =>
    fetcher<Paginate<Shop>>(buildUrlQueryParams("v1/rest/shops/paginate", params), {
      cache: "no-store",
    }),
  create: (data: CreateShopCredentials) => fetcher.post("v1/dashboard/user/shops", { body: data }),
  getById: (id?: number | string, params?: ParamsType) =>
    fetcher<DefaultResponse<Shop>>(buildUrlQueryParams(`v1/rest/shops/${id}`, params), {
      cache: "no-cache",
      redirectOnError: true,
    }),
  getBySlug: (slug?: string, params?: ParamsType) =>
    fetcher<DefaultResponse<Shop>>(buildUrlQueryParams(`v1/rest/shops/slug/${slug}`, params), {
      cache: "no-store",
      redirectOnError: true,
    }),
  gellery: (slug?: string) =>
    fetcher<DefaultResponse<ShopGallery>>(`v1/rest/shops/slug/${slug}/galleries`, {
      redirectOnError: true,
    }),
  // Same endpoint and same staleness exposure as getAll above.
  getByIds: (params?: ParamsType) =>
    fetcher<Paginate<Shop>>(buildUrlQueryParams("v1/rest/shops/paginate", params), {
      cache: "no-store",
    }),
  getAllTags: (params?: ParamsType) =>
    fetcher<DefaultResponse<ShopTag[]>>(buildUrlQueryParams("v1/rest/shops-takes", params)),
  getShopFilters: () => fetcher<ShopFilter>("v1/rest/shop-filter"),
};
