import { configuredExternalUrl, isDevelopmentExampleUrl } from "./parse-settings";

type Destination = "instagram" | "facebook" | "twitter" | "linkedin" | "tiktok" | "ios" | "android";

const hosts: Record<Destination, string[]> = {
  instagram: ["instagram.com"],
  facebook: ["facebook.com", "fb.com"],
  twitter: ["x.com", "twitter.com"],
  linkedin: ["linkedin.com"],
  tiktok: ["tiktok.com"],
  ios: ["apps.apple.com"],
  android: ["play.google.com"],
};

/** Only configured destinations are shown; reserved example hosts are never public links. */
export const footerDestination = (value: string | undefined, destination: Destination) => {
  const configured = configuredExternalUrl(value);
  if (!configured || isDevelopmentExampleUrl(configured)) return undefined;
  const url = new URL(configured);
  const hostname = url.hostname.toLowerCase();
  if (!hosts[destination].some((host) => hostname === host || hostname.endsWith(`.${host}`))) {
    return undefined;
  }
  if (!url.pathname.replace(/\//g, "")) return undefined;
  if (destination === "ios" && !/\/id[1-9]\d*(?:\/|$)/.test(url.pathname)) return undefined;
  if (destination === "android" &&
      (url.pathname !== "/store/apps/details" || !url.searchParams.get("id"))) return undefined;
  return configured;
};

export const footerCopyright = (configured: string | undefined, year: number) =>
  configured?.trim()
    ? configured.trim().replace(/^©\s*\d{4}\b/, `© ${year}`)
    : `© ${year} AgendaAlly. All rights reserved.`;