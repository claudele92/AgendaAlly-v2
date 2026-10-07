import fetcher from "@/lib/fetcher";
import { Currency, DefaultResponse, Language, Setting } from "@/types/global";

export const globalService = {
  languages: () =>
    fetcher<DefaultResponse<Language[]>>("v1/rest/languages/active", {
      next: { revalidate: Number(process.env.NEXT_PUBLIC_CACHE_TIME) },
    }),
  currencies: () =>
    fetcher<DefaultResponse<Currency[]>>("v1/rest/currencies/active", {
      next: { revalidate: Number(process.env.NEXT_PUBLIC_CACHE_TIME) },
    }),
  // no-store, not a revalidate window: Settings values (logo, favicon,
  // hero copy, ...) are rarely changed but must reflect immediately when
  // they are - an admin saving a new logo shouldn't need to wait out a
  // cache window (let alone a server restart) to see it live. A revalidate
  // window here also interacts badly with the calling route's own caching
  // (see route.ts's dynamic export doc comment) - the two layers were each
  // individually reasonable but compounded into staleness neither one
  // alone would have caused.
  settings: () =>
    fetcher<DefaultResponse<Setting[]>>("v1/rest/settings", {
      cache: "no-store",
    }),
};
