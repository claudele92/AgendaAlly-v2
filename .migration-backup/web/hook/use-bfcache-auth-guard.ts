"use client";

import { useEffect } from "react";
import { hasCookie } from "cookies-next";

// A browser restoring a page from its back/forward cache (bfcache) doesn't
// re-run middleware or refetch anything - it just unfreezes the exact
// in-memory DOM/JS snapshot from before the visitor navigated away. If they
// logged out in the meantime, that snapshot still shows the signed-in UI:
// the cookie is genuinely gone, but nothing told this frozen page so. The
// pageshow event's persisted flag is the one reliable signal a bfcache
// restore actually happened; reloading re-runs middleware.ts's auth check
// (redirecting away from a protected route if the cookie is gone) and
// re-fetches everything else fresh.
export const useBfcacheAuthGuard = () => {
  useEffect(() => {
    const handlePageShow = (event: PageTransitionEvent) => {
      if (event.persisted && !hasCookie("token")) {
        window.location.reload();
      }
    };

    window.addEventListener("pageshow", handlePageShow);
    return () => window.removeEventListener("pageshow", handlePageShow);
  }, []);
};
