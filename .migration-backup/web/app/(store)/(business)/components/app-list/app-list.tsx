"use client";

import { useSettings } from "@/hook/use-settings";
import Link from "next/link";
import { useTranslation } from "react-i18next";
import AnchorLeftIcon from "@/assets/icons/anchor-left";
import { useIsApple } from "@/hook/use-is-apple";
import { configuredExternalUrl } from "@/utils/parse-settings";

export const AppList = () => {
  const { settings } = useSettings();
  const { t } = useTranslation();
  const isAppleDevice = useIsApple();

  const list = [
    {
      link: configuredExternalUrl(
        isAppleDevice ? settings?.customer_app_ios : settings?.customer_app_android
      ),
      title: "client.app",
    },
    {
      link: configuredExternalUrl(
        isAppleDevice ? settings?.vendor_app_ios : settings?.vendor_app_android
      ),
      title: "business.app",
    },
    {
      link: configuredExternalUrl(
        isAppleDevice ? settings?.pos_app_ios : settings?.pos_app_android
      ),
      title: "pos.system",
    },
    {
      link: configuredExternalUrl(
        isAppleDevice ? settings?.delivery_app_ios : settings?.delivery_app_android
      ),
      title: "driver.app",
    },
  ];
  const availableApps = list.filter((item): item is (typeof list)[number] & { link: string } =>
    Boolean(item.link?.trim())
  );

  return (
    <div className="flex flex-col md:gap-7 gap-3">
      {availableApps.length ? (
        availableApps.map((item) => (
          <Link
            href={item.link!}
            key={item.title}
            target="_blank"
            rel="noreferrer"
            className="md:grid flex justify-between grid-cols-2 items-center gap-28 bg-gradient-to-r from-white to-transparent rounded-button md:py-7 py-4 md:px-12 px-5"
          >
            <span className="md:text-3xl text-xl font-medium">{t(item.title)}</span>
            <AnchorLeftIcon style={{ rotate: "180deg" }} />
          </Link>
        ))
      ) : (
        <p className="text-sm text-gray-600" role="status" data-testid="status-business-app-links">
          Official app downloads{" "}
          {process.env.NODE_ENV === "development"
            ? "are not available in this development preview."
            : "are not configured at this time."}
        </p>
      )}
    </div>
  );
};
