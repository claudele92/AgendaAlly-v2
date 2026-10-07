/** @type {import("next").NextConfig} */
// eslint-disable-next-line @typescript-eslint/no-var-requires
const withBundleAnalyzer = require("@next/bundle-analyzer")({
  enabled: process.env.ANALYZE === "true",
});
const { resolveRuntimeConfig } = require("./config/runtime-config.cjs");
const { resolveDevApiTarget } = require("../../scripts/development/dev-api-target.cjs");
const runtime = resolveRuntimeConfig(process.env, process.env.NODE_ENV || "development");
const apiUrl = new URL(runtime.apiBaseUrl);
const imageUrl = new URL(process.env.NEXT_PUBLIC_IMAGE_URL || `${runtime.apiOrigin}/storage/`);
if (imageUrl.username || imageUrl.password || imageUrl.search || imageUrl.hash) {
  throw new Error("NEXT_PUBLIC_IMAGE_URL must not include credentials, query or fragment data.");
}
const storagePath = `${imageUrl.pathname.replace(/\/+$/, "")}/**`;
const devApiTarget = runtime.developmentServer
  ? resolveDevApiTarget(process.env.AGENDAALLY_DEV_API_TARGET)
  : undefined;

// The default upstream is private to the Next server and must not enter a
// NEXT_PUBLIC variable or the browser bundle.
if (devApiTarget && !process.env.AGENDAALLY_DEV_API_TARGET) {
  process.env.AGENDAALLY_DEV_API_TARGET = devApiTarget;
}

if (process.env.NEXT_BASE_PATH) {
  throw new Error(
    "AgendaAlly routes and assets are origin-rooted. NEXT_BASE_PATH is not supported until a complete basePath migration is implemented and tested.",
  );
}

const nextConfig = {
  // Each original client is independently portable; do not infer the unrelated
  // parent workspace as its build/trace root.
  outputFileTracingRoot: __dirname,
  turbopack: { root: __dirname },
  allowedDevOrigins: Array.from(
    new Set([
      new URL(runtime.websiteUrl).hostname,
      new URL(runtime.adminUrl).hostname,
    ]),
  ),
  images: {
    // Needed to render the demo category/service icons (static, trusted
    // SVGs shipped from our own remixicon dependency under public/icons) -
    // next/image refuses SVGs by default as an XSS precaution against
    // untrusted uploads, which doesn't apply here since these are our own
    // build-time assets, not user-supplied files.
    dangerouslyAllowSVG: true,
    // Only a development server whose configured image origin targets loopback may
    // use Next's constrained remotePatterns to optimize local backend media.
    // A production build can never opt out of Next's private-IP protection.
    dangerouslyAllowLocalIP:
      runtime.developmentServer &&
      ["127.0.0.1", "localhost"].includes(imageUrl.hostname),
    contentDispositionType: "attachment",
    contentSecurityPolicy: "default-src 'self'; script-src 'none'; sandbox;",
    remotePatterns: [
      {
        protocol: imageUrl.protocol.slice(0, -1),
        hostname: imageUrl.hostname,
        ...(imageUrl.port ? { port: imageUrl.port } : {}),
        pathname: storagePath,
      },
      {
        protocol: "https",
        hostname: "foodyman.s3.amazonaws.com",
        port: "",
        pathname: "/public/**",
      },
      {
        protocol: "https",
        hostname: "lh3.googleusercontent.com",
      },
      {
        protocol: "https",
        hostname: "i.ibb.co",
      },
      {
        protocol: "https",
        hostname: "flagcdn.com",
      },
      {
        protocol: "https",
        hostname: "images.unsplash.com",
      },
      {
        protocol: "https",
        hostname: "graph.facebook.com",
      },
    ],
  },
  async rewrites() {
    if (!devApiTarget) return [];

    return [
      {
        source: "/api/v1/:path*",
        destination: `${devApiTarget}/api/v1/:path*`,
      },
    ];
  },
};

module.exports = withBundleAnalyzer(nextConfig);
