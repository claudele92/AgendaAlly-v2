// Native public Service files may be serialized with Laravel's development
// APP_URL. Next's optimizer must instead use its configured local image origin.
// This never expands remotePatterns or changes production/cloud URLs.
function resolveServiceMediaUrl(src, config = {
  nodeEnvironment: process.env.NODE_ENV,
  developmentMode: process.env.NEXT_PUBLIC_DEVELOPMENT_MODE,
  appEnvironment: process.env.NEXT_PUBLIC_APP_ENV,
  imageBaseUrl: process.env.NEXT_PUBLIC_IMAGE_URL,
}) {
  if (typeof src !== "string" ||
      config.nodeEnvironment === "production" ||
      config.developmentMode !== "true" ||
      !["local", "development"].includes(config.appEnvironment)) return src;

  try {
    const imageBase = new URL(config.imageBaseUrl);
    if (!["http:", "https:"].includes(imageBase.protocol) ||
        !["127.0.0.1", "localhost", "[::1]"].includes(imageBase.hostname) ||
        imageBase.username || imageBase.password || imageBase.search || imageBase.hash ||
        !/^\/storage\/?$/.test(imageBase.pathname)) return src;

    const media = new URL(src, "https://native-media.invalid");
    if (media.username || media.password || media.search || media.hash ||
        !["http:", "https:"].includes(media.protocol) ||
        !/^\/storage\/images\/services\/(?:shops|admin)\/[1-9]\d*\/\d+-[a-f0-9-]{36}\.(?:png|jpe?g|webp|gif|avif)$/i.test(media.pathname)) return src;

    return imageBase.origin + media.pathname;
  } catch {
    return src;
  }
}

module.exports = { resolveServiceMediaUrl };