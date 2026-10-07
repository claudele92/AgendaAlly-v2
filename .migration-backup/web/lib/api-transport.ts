interface ApiBaseUrlOptions {
  developmentServer: boolean;
  isBrowser: boolean;
  publicBaseUrl: string;
  devApiTarget?: string;
}

export const isDevelopmentServer = (
  nodeEnvironment: string | undefined,
  appEnvironment: string | undefined,
  developmentMode: string | undefined,
): boolean =>
  nodeEnvironment !== "production" &&
  developmentMode === "true" &&
  ["local", "development"].includes(appEnvironment || "");

export const resolveApiBaseUrl = ({
  developmentServer,
  isBrowser,
  publicBaseUrl,
  devApiTarget,
}: ApiBaseUrlOptions): string => {
  if (!developmentServer) return publicBaseUrl;
  if (isBrowser) return "/api/";

  const configuredTarget = devApiTarget?.trim();
  if (!configuredTarget) {
    throw new Error("AGENDAALLY_DEV_API_TARGET is required for development server-side API requests");
  }

  let target: URL;
  try {
    target = new URL(configuredTarget);
  } catch {
    throw new Error("AGENDAALLY_DEV_API_TARGET must be an absolute HTTP(S) origin");
  }

  if (
    !["http:", "https:"].includes(target.protocol) ||
    target.username ||
    target.password ||
    target.pathname !== "/" ||
    target.search ||
    target.hash
  ) {
    throw new Error(
      "AGENDAALLY_DEV_API_TARGET must be an HTTP(S) origin without credentials, path, query, or fragment",
    );
  }

  return `${target.origin}/api/`;
};