import { NextResponse } from "next/server";
import { getFirebaseConfiguration } from "@/lib/firebase";

export const dynamic = "force-dynamic";

export const GET = () => {
  const firebaseConfig = getFirebaseConfiguration();
  if (!firebaseConfig) {
    return new NextResponse(
      "Firebase push messaging is not enabled for this environment.",
      { status: 404 },
    );
  }

  return new NextResponse(
    `importScripts("https://www.gstatic.com/firebasejs/8.2.0/firebase-app.js");\n` +
      `importScripts("https://www.gstatic.com/firebasejs/8.2.0/firebase-messaging.js");\n` +
      `firebase.initializeApp(${JSON.stringify(firebaseConfig)});\n` +
      `const messaging = firebase.messaging();\n` +
      `messaging.onBackgroundMessage((payload) => {\n` +
      `  const title = payload.notification?.title || "Notification";\n` +
      `  const options = { body: payload.notification?.body || "" };\n` +
      `  self.registration.showNotification(title, options);\n` +
      `});\n`,
    {
      headers: {
        "Cache-Control": "no-store",
        "Content-Type": "application/javascript; charset=utf-8",
        "Service-Worker-Allowed": "/",
        "X-Content-Type-Options": "nosniff",
      },
    },
  );
};