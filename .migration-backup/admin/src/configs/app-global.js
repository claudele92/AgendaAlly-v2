import {
  getMapsKey,
  resolveAdminRuntimeConfigSafely,
} from './runtime-config.mjs';

const { runtime, error: runtimeConfigError } = resolveAdminRuntimeConfigSafely(
  import.meta.env,
  import.meta.env.PROD ? 'build' : 'serve',
  import.meta.env.PROD ? 'production' : 'development',
);

export const ADMIN_RUNTIME_CONFIG_VALID = Boolean(runtime);
export const ADMIN_RUNTIME_CONFIG_ERROR = runtimeConfigError;
export const PROJECT_NAME = import.meta.env.VITE_PROJECT_NAME || 'AgendaAlly';
export const BASE_URL = runtime?.apiOrigin || '';
export const WEBSITE_URL = runtime?.websiteUrl.replace(/\/+$/, '') || '';
export const ADMIN_PANEL_URL = runtime?.adminUrl.replace(/\/+$/, '') || '';
const apiBaseUrl = runtime?.developmentServer ? '/api/v1/' : `${BASE_URL}/api/v1/`;
export const api_url = apiBaseUrl;
export const api_url_admin = `${api_url}dashboard/admin/`;
export const api_url_admin_dashboard = `${api_url}dashboard/`;
export const IMG_URL = `${BASE_URL}/storage/`;
export const MAP_API_KEY = runtime
  ? getMapsKey(
      import.meta.env,
      import.meta.env.PROD ? 'production' : 'development',
    )
  : null;
export const export_url = `${BASE_URL}/storage/`;
export const example = `${BASE_URL}/`;
const defaultLocation = (import.meta.env.VITE_DEFAULT_LOCATION || '40.7127281,-74.0060152')
  .split(',')
  .map(Number);
export const defaultCenter = {
  lat: defaultLocation[0],
  lng: defaultLocation[1],
};

export const VAPID_KEY = import.meta.env.VITE_FIREBASE_VAPID_KEY || '';
export const API_KEY = import.meta.env.VITE_FIREBASE_API_KEY || '';
export const AUTH_DOMAIN = import.meta.env.VITE_FIREBASE_AUTH_DOMAIN || '';
export const PROJECT_ID = import.meta.env.VITE_FIREBASE_PROJECT_ID || '';
export const STORAGE_BUCKET = import.meta.env.VITE_FIREBASE_STORAGE_BUCKET || '';
export const MESSAGING_SENDER_ID = import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID || '';
export const APP_ID = import.meta.env.VITE_FIREBASE_APP_ID || '';
export const MEASUREMENT_ID = import.meta.env.VITE_FIREBASE_MEASUREMENT_ID || '';
export const RECAPTCHASITEKEY = runtime?.recaptchaSiteKey || '';
export const RECAPTCHA_DISABLED_LOCAL = runtime?.recaptchaDisabled || false;
export const RECAPTCHA_CONFIGURED = Boolean(runtime?.recaptchaSiteKey);
export const FIREBASE_ENABLED = runtime?.firebaseEnabled || false;

export const DEMO_SELLER = Number(import.meta.env.VITE_DEMO_SELLER_ID) || 0;
export const DEMO_SELLER_UUID = import.meta.env.VITE_DEMO_SELLER_UUID || '';
export const DEMO_SHOP = Number(import.meta.env.VITE_DEMO_SHOP_ID) || 0;
export const DEMO_DELIVERYMAN = Number(import.meta.env.VITE_DEMO_DELIVERYMAN_ID) || 0;
export const DEMO_MANEGER = Number(import.meta.env.VITE_DEMO_MANAGER_ID) || 0;
export const DEMO_MODERATOR = Number(import.meta.env.VITE_DEMO_MODERATOR_ID) || 0;
export const DEMO_ADMIN = Number(import.meta.env.VITE_DEMO_ADMIN_ID) || 0;

export const SUPPORTED_FORMATS = [
  'image/jpg',
  'image/jpeg',
  'image/png',
  'image/svg+xml',
  'image/svg',
];

export const COUNTRY_CODE = import.meta.env.VITE_COUNTRY_CODE || '';