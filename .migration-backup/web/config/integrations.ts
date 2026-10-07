import { shouldEnableMaps } from "./runtime-config.cjs";

const mapEnvironment = {
  APP_ENV: process.env.NEXT_PUBLIC_APP_ENV,
  DEVELOPMENT_MODE: process.env.NEXT_PUBLIC_DEVELOPMENT_MODE,
  MAPS_ENABLED: process.env.NEXT_PUBLIC_MAPS_ENABLED,
  MAPS_KEY: process.env.NEXT_PUBLIC_GOOGLE_MAPS_KEY,
};

export const isFirebaseConfigured = (): boolean =>
  process.env.NEXT_PUBLIC_FIREBASE_ENABLED === "true";

export const getGoogleMapsApiKey = (publicKey?: string, runtimeEnabled?: boolean | string, environmentPermitted?: boolean | string): string | null => {
  // Do not load the SDK from an environment fallback before public settings
  // have arrived, or while the platform opt-in is off.
  if (![true, "1", "true"].includes(runtimeEnabled ?? false) ||
      ![true, "1", "true"].includes(environmentPermitted ?? false)) return null;
  if (
    !shouldEnableMaps(mapEnvironment, process.env.NODE_ENV || "production", {
      serverKey: publicKey,
      runtimeEnabled,
      environmentPermitted,
    })
  ) {
    return null;
  }
  return publicKey?.trim() || process.env.NEXT_PUBLIC_GOOGLE_MAPS_KEY || null;
};