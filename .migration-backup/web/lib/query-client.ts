import { QueryCache, QueryClient } from "@tanstack/react-query";
import NetworkError from "@/utils/network-error";
import { error } from "@/components/alert";

// A factory, not a shared instance: this module is evaluated once per
// server process (not per request), so a module-level `new QueryClient()`
// here would be silently reused as the SAME cache across every concurrent
// SSR request. Query results written with `initialData` on one request
// (e.g. Recommended/Deals' shop lists, which have no explicit tiebreaker
// on tied ratings) could then leak into another request's server-rendered
// HTML, while that request's own hydration reads the correct per-request
// data - producing a hydration content mismatch with no code-level
// randomness involved. Call this inside a client-side `useState(() => ...)`
// so each request/mount gets its own client, matching TanStack Query's own
// Next.js App Router guidance.
export const createQueryClient = () =>
  new QueryClient({
    defaultOptions: {
      queries: {
        refetchOnWindowFocus: false,
      },
    },
    queryCache: new QueryCache({
      // eslint-disable-next-line @typescript-eslint/ban-ts-comment
      // @ts-ignore
      onError: (err: NetworkError, query) => {
        if (query.meta?.showErrorMessageFromServer) {
          error(err.message);
        }
      },
    }),
  });
