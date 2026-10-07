"use client";

import Image, { ImageProps } from "next/image";
import React, { useEffect, useState } from "react";
import { useTheme } from "next-themes";
import { resolveServiceMediaUrl } from "@/utils/service-media-url.cjs";

// prettier-ignore
const shimmer = (isDark: boolean) => `
<svg width="100%" height="100%" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
  <defs>
    <linearGradient id="g">
      <stop 
      stop-color="${isDark ? "#383838" : `#E2E2E2`}" offset="20%" />
      <stop stop-color="${isDark ? "#A0A09C" : "#F6F6F6"}" offset="50%" />
<stop stop-color="${isDark ? "#383838" : "#E2E2E2"}" offset="70%" />
</linearGradient>
</defs>
      <rect width="100%" height="100%" fill="${isDark ? "#383838" : "#E2E2E2"}" />
      <rect id="r" width="100%" height="100%" fill="url(#g)" />
      <animate xlink:href="#r" attributeName="x" from="-100%" to="100%" dur="1s" repeatCount="indefinite"  />
</svg>`;

const toBase64 = (str: string) =>
  typeof window === "undefined" ? Buffer.from(str).toString("base64") : window.btoa(str);

type ImageWithFallBackProps = Omit<ImageProps, "src"> & {
  src?: ImageProps["src"] | null;
};

// next/image only accepts a src that's either a fully-qualified URL or a
// root-relative local path ("/img/...") - anything else (a bare relative
// path like "storage/images/settings/logo.webp", the exact shape a
// Settings-driven value takes when the backend's host-prefixing silently
// no-ops - see FileHelper::uploadFile()) fails its validation synchronously
// during render, before onError ever gets a chance to fire, crashing the
// whole page rather than just this one image. A non-empty but malformed
// string needs the same fallback treatment as a genuinely missing one.
const isRenderableSrc = (src: ImageProps["src"] | null | undefined): src is ImageProps["src"] => {
  // A StaticImageData/import (not a plain string) is always safe - it
  // only ever comes from a local `import`, never from API data.
  if (typeof src !== "string") {
    return Boolean(src);
  }

  return src.startsWith("/") || src.startsWith("data:") || /^https?:\/\//.test(src);
};

export const ImageWithFallBack = (props: ImageWithFallBackProps) => {
  const { src: originalSrc, loader, ...rest } = props;
  const src = resolveServiceMediaUrl(originalSrc);
  const [isError, setIsError] = useState(false);
  useEffect(() => setIsError(false), [src]);
  const { theme } = useTheme();

  // A falsy or malformed src (missing/empty/relative-without-a-leading-
  // slash field from the API) is treated the same as a load failure - see
  // isRenderableSrc above for why the shape check matters, not just
  // truthiness.
  const useFallback = isError || !isRenderableSrc(src);

  return (
    <Image
      {...rest}
      loader={useFallback ? undefined : loader}
      src={useFallback ? "/img/image-load-failed.png" : (src as ImageProps["src"])}
      placeholder={`data:image/svg+xml;base64,${toBase64(shimmer(theme === "dark"))}`}
      onError={() => {
        setIsError(true);
      }}
    />
  );
};
