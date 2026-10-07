import { FirebaseApp, getApp, getApps, initializeApp } from "firebase/app";
import { getFirebaseConfiguration as getConfiguredFirebase } from "@/config/runtime-config.cjs";

export const isFirebaseEnabled = (): boolean =>
  process.env.NEXT_PUBLIC_FIREBASE_ENABLED === "true";

const firebaseEnvironment = {
  NEXT_PUBLIC_FIREBASE_ENABLED: process.env.NEXT_PUBLIC_FIREBASE_ENABLED,
  NEXT_PUBLIC_API_KEY: process.env.NEXT_PUBLIC_API_KEY,
  NEXT_PUBLIC_AUTH_DOMAIN: process.env.NEXT_PUBLIC_AUTH_DOMAIN,
  NEXT_PUBLIC_PROJECT_ID: process.env.NEXT_PUBLIC_PROJECT_ID,
  NEXT_PUBLIC_STORAGE_BUCKET: process.env.NEXT_PUBLIC_STORAGE_BUCKET,
  NEXT_PUBLIC_MESSAGING_SENDER_ID: process.env.NEXT_PUBLIC_MESSAGING_SENDER_ID,
  NEXT_PUBLIC_APP_ID: process.env.NEXT_PUBLIC_APP_ID,
  NEXT_PUBLIC_MEASUREMENT_ID: process.env.NEXT_PUBLIC_MEASUREMENT_ID,
};

export const getFirebaseConfiguration = () =>
  getConfiguredFirebase(firebaseEnvironment);

export const getFirebaseApp = (): FirebaseApp => {
  const configuration = getFirebaseConfiguration();
  if (!configuration) {
    throw new Error(
      "Firebase is disabled for this environment. Explicitly enable and configure it before using social sign-in or push.",
    );
  }
  return getApps().length ? getApp() : initializeApp(configuration);
};

// Never initialize Firebase at module import. Without opt-in the REST-backed
// store and Laravel token authentication stay usable with no Firebase traffic.
const firebaseApp = isFirebaseEnabled() ? getFirebaseApp() : null;
export default firebaseApp;
