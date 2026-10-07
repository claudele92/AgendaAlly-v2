import { Paginate, ParamsType } from "@/types/global";
import fetcher from "@/lib/fetcher";
import { buildUrlQueryParams } from "@/utils/build-url-query-params";
import { Category, CategoryChildrenResponse } from "@/types/category";

export const categoryService = {
  getAll: (params?: ParamsType) =>
    fetcher<Paginate<Category>>(buildUrlQueryParams("v1/rest/categories/paginate", params)),
  getChildren: (categoryId: number, params?: ParamsType) =>
    fetcher<CategoryChildrenResponse>(
      buildUrlQueryParams(`v1/rest/categories/children/${categoryId}`, params)
    ),
};
