import { NextResponse } from "next/server";
import fetcher from "@/lib/fetcher";
import { DefaultResponse, Setting } from "@/types/global";
import { parseSettings } from "@/utils/parse-settings";

// This handler has no dynamic API usage (no cookies()/headers()) of its
// own, so Next/Turbopack treats it as a static route by default - captured
// once and never re-executed at all. force-dynamic makes the route itself
// re-run on every request. That alone isn't sufficient, though: the inner
// fetch below is a *separate* cache layer (Next's per-URL Data Cache), so
// even a re-executing route would still hand back a stale response until
// that fetch's own cache window happened to expire - confirmed: a changed
// Settings value (e.g. a new logo) stayed stale through a plain dev-server
// restart and only appeared after a full .next wipe forced recompilation.
// Both directives are needed together; either alone leaves the other gap.
export const dynamic = "force-dynamic";

export const GET = async () => {
  const settings = await fetcher<DefaultResponse<Setting[]>>("v1/rest/settings", {
    cache: "no-store",
  });
  return NextResponse.json(parseSettings(settings?.data));
};
