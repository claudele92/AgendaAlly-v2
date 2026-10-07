import fetcher from "@/lib/fetcher";
import { DefaultResponse } from "@/types/global";

export const translationService = {
  // no-store, not a revalidate window: same reasoning as
  // services/global.ts's settings() - a translation edited in the admin
  // panel needs to show up on the next storefront request, not up to
  // NEXT_PUBLIC_CACHE_TIME seconds later (and, combined with the root
  // layout not being force-dynamic, this fetch's own revalidate window
  // never actually got a chance to apply - the whole route was frozen at
  // whatever it rendered on the last recompile, same bug class as the
  // Settings/shops staleness fixed in PR #102, just in a spot that PR
  // didn't reach).
  getAll: (lang: string) =>
    fetcher<DefaultResponse<Record<string, string>>>(`v1/rest/translations/paginate?lang=${lang}`, {
      cache: "no-store",
    }),
};
