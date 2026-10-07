import fetcher from "@/lib/fetcher";
import { DefaultResponse, Paginate, ParamsType } from "@/types/global";
import { Blog, BlogFullTranslation, BlogShortTranslation } from "@/types/blog";
import { buildUrlQueryParams } from "@/utils/build-url-query-params";

type BlogRequestInit = RequestInit & { redirectOnError?: boolean };

export const blogService = {
  getAll: (params?: ParamsType, init?: RequestInit) =>
    fetcher<Paginate<Blog<BlogShortTranslation>>>(
      buildUrlQueryParams("v1/rest/blogs/paginate", params),
      init
    ),
  // Public blog URLs use UUIDs; retain numeric IDs for existing notification links.
  get: (idOrUuid: string, params?: ParamsType, init?: BlogRequestInit) =>
    fetcher<DefaultResponse<Blog<BlogFullTranslation>>>(
      buildUrlQueryParams(
        /^\d+$/.test(idOrUuid)
          ? `v1/rest/blog-by-id/${encodeURIComponent(idOrUuid)}`
          : `v1/rest/blogs/${encodeURIComponent(idOrUuid)}`,
        params
      ),
      { ...init, redirectOnError: init?.redirectOnError ?? true }
    ),
};
