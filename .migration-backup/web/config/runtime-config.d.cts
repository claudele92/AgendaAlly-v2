export interface FirebaseConfiguration {
  apiKey: string;
  authDomain: string;
  projectId: string;
  storageBucket: string;
  messagingSenderId: string;
  appId: string;
  measurementId?: string;
}

export interface RuntimeConfig {
  appEnvironment: string;
  developmentServer: boolean;
  apiBaseUrl: string;
  websiteUrl: string;
  adminUrl: string;
  apiOrigin: string;
}

export function getFirebaseConfiguration(
  environment?: NodeJS.ProcessEnv,
): FirebaseConfiguration | null;
export function getFirebaseConfiguration(
  environment: Partial<NodeJS.ProcessEnv>,
): FirebaseConfiguration | null;
export function isMapsEnabled(
  environment: Partial<NodeJS.ProcessEnv>,
  nodeEnvironment: string,
  configuredServerKey?: string,
): boolean;
export function normalizeApiBaseUrl(value: string, name?: string): string;
export function parseAppEnvironment(value?: string): string;
export function requireHttpUrl(value: string, name: string): URL;
export function resolveRuntimeConfig(
  environment: NodeJS.ProcessEnv,
  nodeEnvironment: string,
): RuntimeConfig;
export function shouldEnableMaps(
  environment: {
    APP_ENV?: string;
    DEVELOPMENT_MODE?: string;
    MAPS_ENABLED?: string;
    MAPS_KEY?: string;
  },
  nodeEnvironment: string,
  options?: { serverKey?: string; runtimeEnabled?: boolean | string; environmentPermitted?: boolean | string },
): boolean;