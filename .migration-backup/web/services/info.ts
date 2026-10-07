import {
  DefaultResponse,
  Page,
  Paginate,
  ParamsType,
  // Referral,
  Term,
} from "@/types/global";
import fetcher, { getApiBaseUrl } from "@/lib/fetcher";
import { buildUrlQueryParams } from "@/utils/build-url-query-params";
import { Faq } from "@/types/info";
import { Career } from "@/types/career";
import { getCookie } from "cookies-next";

export const infoService = {
  terms: (params?: ParamsType) =>
    fetcher<DefaultResponse<Term>>(buildUrlQueryParams("v1/rest/term", params), {
      redirectOnError: true,
      cache: "no-cache",
    }),

  privacy: (params?: ParamsType) =>
    fetcher<DefaultResponse<Term>>(buildUrlQueryParams("v1/rest/policy", params), {
      redirectOnError: true,
    }),
  refundPolicy: (params?: ParamsType) =>
    fetcher<DefaultResponse<Page>>(
      buildUrlQueryParams("v1/rest/pages/refund_cancellation", params),
      { cache: "no-cache" }
    ),
  faq: (params: ParamsType) =>
    fetcher<Paginate<Faq>>(buildUrlQueryParams("v1/rest/faqs/paginate", params), {
      cache: "no-cache",
    }),
  // referrals: (params?: ParamsType) =>
  //   fetcher<DefaultResponse<Referral>>(buildUrlQueryParams("v1/rest/referral", params), {
  //     cache: "no-cache",
  //   }),
  referrals: (params?: ParamsType) =>
    fetch(`${getApiBaseUrl()}${buildUrlQueryParams("v1/rest/referral", params)}`, {
      headers: {
        "Content-Type": "application/json",
        Authorization: getCookie("token") as string,
        METHOD: "GET",
      },
    })
      .then((res) => res.json())
      .catch((err) => err),
  getPages: (params: ParamsType) =>
    fetcher<Paginate<Page>>(buildUrlQueryParams("v1/rest/pages/paginate", params)),
  careerList: (params?: ParamsType) =>
    fetcher<Paginate<Career>>(buildUrlQueryParams("v1/rest/careers/paginate", params)),
  getCareer: (id?: string, params?: ParamsType) =>
    fetcher<DefaultResponse<Career>>(buildUrlQueryParams(`v1/rest/careers/${id}`, params)),
};
