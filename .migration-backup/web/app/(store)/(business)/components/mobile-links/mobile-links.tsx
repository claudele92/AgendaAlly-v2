"use client";

import { useSettings } from "@/hook/use-settings";
import Link from "next/link";
import Image from "next/image";
import { footerDestination } from "@/utils/footer-settings";

export const MobileLinks = () => {
  const { settings } = useSettings();
  const links = [
    {
      url: settings?.customer_app_ios
        ? footerDestination(settings.customer_app_ios, "ios")
        : undefined,
      src: "/img/apple_store.png",
      label: "App Store",
    },
    {
      url: settings?.customer_app_android
        ? footerDestination(settings.customer_app_android, "android")
        : undefined,
      src: "/img/play_market.png",
      label: "Google Play",
    },
  ].filter((link) => Boolean(link.url?.trim()));

  if (links.length === 0) return null;

  return (
    <div className="flex items-center gap-2.5  mb-8 md:mb-0 absolute md:static w-full bottom-0 px-4 xl:px-0">
      {links.map(({ url, label, src }) => (
        <Link href={url!} key={label} target="_blank" rel="noreferrer">
          <Image src={src} alt={label} width={147} height={55} />
        </Link>
      ))}
    </div>
  );
};
