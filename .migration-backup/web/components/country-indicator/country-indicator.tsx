"use client";

import { useEffect, useState } from "react";
import useAddressStore from "@/global-store/address";
import LocationIcon from "@/assets/icons/location";
import { useMediaQuery } from "@/hook/use-media-query";
import { useTranslation } from "react-i18next";
import { Button } from "@/components/button";
import { usePathname } from "next/navigation";
import { getCookie, setCookie } from "cookies-next";
import "@/components/country-select/stage2-location.css";

const CountryIndicator = () => {
  const { t } = useTranslation();
  const [mounted, setMounted] = useState(false);
  const [showDialog, setShowDialog] = useState(String(getCookie("showCountryDialog")) !== "false");
  const { openCountrySelectModal, country, city } = useAddressStore();
  const isMobile = useMediaQuery("(max-width: 760px)");
  const pathname = usePathname();

  useEffect(() => {
    setMounted(true);
  }, []);

  useEffect(() => {
    setCookie("showCountryDialog", showDialog);
  }, [showDialog]);

  const isLegacyHome = /^\/home-[234]\/?$/.test(pathname);
  if (!mounted || !country || (isLegacyHome && isMobile)) return null;
  const locationLabel = [city?.translation?.title, country.translation?.title].filter(Boolean).join(", ");

  return (
    <div className="relative">
      <button
        type="button"
        onClick={openCountrySelectModal}
        className="aa-country-location-button aa-stage2-location-trigger border border-footerBg px-4 py-2 rounded-button"
        aria-haspopup="dialog"
        aria-label={`${t("change.address")}: ${locationLabel}`}
        title={locationLabel}
      >
        <div className="flex flex-row items-center gap-x-2">
          <LocationIcon />
          {country?.translation?.title && (
            <span className="font-medium">
              {city?.translation?.title || country.translation.title}
              {city?.translation?.title && <span className="aa-stage2-location-country">, {country.translation.title}</span>}
            </span>
          )}
        </div>
      </button>
      {showDialog && isLegacyHome && (
        <div className="absolute dropdown-shadow bg-white px-4 py-5 max-w-[460px] w-96 rounded-button mt-2">
          <p className="font-medium text-sm mb-5">
            {`${t("We're showing you items that ship to")} ${country?.translation?.title}. ${t(
              "To see items that ship to a different country, change your delivery address."
            )}`}
          </p>
          <div className="flex items-center justify-end gap-3">
            <Button
              color="white"
              size="small"
              className="border border-footerBg"
              onClick={() => setShowDialog(false)}
            >
              {t("dismiss")}
            </Button>
            <Button
              color="black"
              size="small"
              onClick={() => {
                setShowDialog(false);
                openCountrySelectModal();
              }}
            >
              {t("change.address")}
            </Button>
          </div>
        </div>
      )}
    </div>
  );
};

export default CountryIndicator;
