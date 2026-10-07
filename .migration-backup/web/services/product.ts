import { DefaultResponse, Paginate, ParamsType } from "@/types/global";
import fetcher, { getApiBaseUrl } from "@/lib/fetcher";
import { buildUrlQueryParams } from "@/utils/build-url-query-params";
import { DigitalProduct, Filter, Product, ProductFull, SearchProduct } from "@/types/product";
import { getCookie } from "cookies-next";

const sellerMetadataParams = (params?: ParamsType): ParamsType => ({
  ...params,
  seller_metadata: 1,
});

export const productService = {
  getAll: (params?: ParamsType) =>
    fetcher<Paginate<Product>>(
      buildUrlQueryParams("v1/rest/products/paginate", sellerMetadataParams(params))
    ),
  getByIds: (params?: ParamsType) =>
    fetcher<Paginate<Product>>(
      buildUrlQueryParams("v1/rest/products/ids", sellerMetadataParams(params))
    ),
  filters: (params?: ParamsType) => fetcher<Filter>(buildUrlQueryParams("v1/rest/filter", params)),
  search: (params?: ParamsType) =>
    fetcher<Paginate<SearchProduct>>(
      buildUrlQueryParams("v1/rest/products/search", sellerMetadataParams(params))
    ),
  get: (id?: string, params?: ParamsType) =>
    fetcher<DefaultResponse<ProductFull>>(buildUrlQueryParams(`v1/rest/products/${id}`, params)),
  getViewHistory: (params?: ParamsType) =>
    fetcher<Paginate<Product>>(
      buildUrlQueryParams("v1/rest/product-histories/paginate", sellerMetadataParams(params))
    ),
  alsoBought: (params?: ParamsType, id?: number | string) =>
    fetcher<Paginate<Product>>(
      buildUrlQueryParams(`v1/rest/products/${id}/also-bought`, sellerMetadataParams(params))
    ),
  compare: (params?: ParamsType) =>
    fetcher<DefaultResponse<ProductFull[][]>>(
      buildUrlQueryParams("v1/rest/compare", sellerMetadataParams(params))
    ),
  myDigitalFiles: (params?: ParamsType) =>
    fetcher<Paginate<DigitalProduct>>(
      buildUrlQueryParams("v1/dashboard/user/my-digital-files", params)
    ),
  downloadFile: (id: number) =>
    fetch(`${getApiBaseUrl()}v1/dashboard/user/digital-files/${id}`, {
      headers: {
        "Content-Type": "application/zip",
        Authorization: getCookie("token") as string,
      },
    }),
};
