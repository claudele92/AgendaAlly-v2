"use client";

import AnchorLeftIcon from "@/assets/icons/anchor-left";
import Link from "next/link";
import { useTranslation } from "react-i18next";
import { useIsApple } from "@/hook/use-is-apple";
import { useSettings } from "@/hook/use-settings";
import { footerDestination } from "@/utils/footer-settings";

export const BusinessDownloads = () => {
  const { settings } = useSettings();
  const { t } = useTranslation();
  const isAppleDevice = useIsApple();
  const destination = isAppleDevice ? "ios" : "android";

  const list = [
    {
      value: isAppleDevice ? settings?.customer_app_ios : settings?.customer_app_android,
      title: "client.app",
    },
    {
      value: isAppleDevice ? settings?.vendor_app_ios : settings?.vendor_app_android,
      title: "business.app",
    },
    {
      value: isAppleDevice ? settings?.pos_app_ios : settings?.pos_app_android,
      title: "pos.system",
    },
    {
      value: isAppleDevice ? settings?.delivery_app_ios : settings?.delivery_app_android,
      title: "driver.app",
    },
  ];
  const getConfiguredDestination = (value?: string) => {
    return value ? footerDestination(value, destination) : undefined;
  };
  const availableApps = list.flatMap((item) => {
    const link = getConfiguredDestination(item.value);
    return link
      ? [{ ...item, link }]
      : [];
  });

  if (availableApps.length === 0) return null;

  return (
    <div className="flex flex-col md:gap-7 gap-3">
      {availableApps.map((item) => (
        <Link
          href={item.link}
          key={item.title}
          target="_blank"
          rel="noreferrer"
          className="md:grid flex justify-between grid-cols-2 items-center gap-5 rounded-button border border-gray-link bg-white/70 md:py-6 py-4 md:px-8 px-5 text-dark dark:bg-dark dark:text-white"
        >
          <span className="flex flex-col gap-1 md:text-xl text-base font-medium">
            {t(item.title)}
          </span>
          <AnchorLeftIcon style={{ rotate: "180deg" }} />
        </Link>
      ))}
    </div>
  );
};