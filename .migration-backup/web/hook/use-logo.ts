"use client";

import { useEffect, useState } from "react";
import { useTheme } from "next-themes";
import {
  AGENDAALLY_BRAND_LOGO,
  resolveAgendaAllyBrandAsset,
} from "@/utils/agendaally-brand-assets";

// Selects the configured platform wordmark for the current theme and retains
// the approved local wordmark as a safe initial/unconfigured fallback.
//
// The `mounted` guard is required, not cosmetic: resolvedTheme reads
// localStorage, which only exists client-side, so the server-rendered
// markup always assumes the default (light) theme. Returning the
// dark_logo on the very first client render - before React finishes
// hydrating against that server markup - produces a src mismatch on this
// exact <img>, and React's hydration diffing deliberately does not patch
// mismatched attributes (see "This won't be patched up" in the console);
// it silently keeps the server's src forever, since nothing else
// re-renders this node afterward. Rendering the same default on the
// first pass, then flipping after mount, makes the swap happen through a
// normal (non-hydration) re-render, which React does patch.
export const useLogo = (settings?: Record<string, string>) => {
  const { resolvedTheme } = useTheme();
  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
  }, []);

  const configuredLogo =
    (mounted && resolvedTheme === "dark" && settings?.dark_logo) ||
    settings?.logo ||
    AGENDAALLY_BRAND_LOGO;
  return (
    resolveAgendaAllyBrandAsset(configuredLogo, "logo") || AGENDAALLY_BRAND_LOGO
  );
};
