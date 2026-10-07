import { Setting } from "@/types/global";

export const parseSettings = (settings?: Setting[]): Record<string, string> =>
  settings
    ? Object.assign({}, ...settings.map((setting) => ({ [setting.key]: setting.value })))
    : {};

/**
 * Normalize configured public URLs before rendering them as new-tab links.
 * Social settings may be hostnames; marketplace/app store settings usually
 * contain a fully-qualified URL.
 */
export const configuredExternalUrl = (value?: string | null): string | undefined => {
  const candidate = value?.trim();

  if (!candidate) {
    return undefined;
  }

  try {
    const url = new URL(candidate.includes("://") ? candidate : `https://${candidate}`);
    if (url.protocol !== "https:" || !url.hostname || url.username || url.password) {
      return undefined;
    }
    return url.toString();
  } catch {
    return undefined;
  }
};

export const isDevelopmentExampleUrl = (value: string): boolean => {
  const hostname = new URL(value).hostname;
  return (
    hostname === "example.com" ||
    hostname.endsWith(".example") ||
    hostname.endsWith(".test") ||
    hostname.endsWith(".invalid")
  );
};
