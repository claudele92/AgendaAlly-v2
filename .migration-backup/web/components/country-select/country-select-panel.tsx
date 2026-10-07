"use client";

import { CountrySelectForm } from "@/components/country-select/country-select-form";
import { useRouter, useSearchParams } from "next/navigation";
import { useTranslation } from "react-i18next";
import "./stage2-location.css";

const CountrySelectPanel = ({
  settings,
  validationError,
  onRetryValidation,
}: {
  settings: Record<string, string>;
  validationError?: string;
  onRetryValidation?: () => void;
}) => {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { t } = useTranslation();
  const customUrl = searchParams.get("url");
  return (
    <div className="aa-stage2-location">
      <p className="aa-stage2-location-eyebrow">{t("discovery.context", { defaultValue: "Your discovery context" })}</p>
      <h2>{t("choose.country.city", { defaultValue: "Choose your country and city" })}</h2>
      <p>{t("location.discovery.effect", { defaultValue: "Explore services, products and businesses in your selected location. No GPS or Maps access is needed." })}</p>
      <div className="aa-stage2-location-notice" role="note">
        {t("location.change.cart.notice", { defaultValue: "Confirming your location refreshes discovery and clears your local cart. Your selection is remembered in this browser and shared with the storefront through cookies. It does not change business branch permissions." })}
      </div>
      {validationError && (
        <div className="aa-stage2-location-error" role="alert">
          <p>{validationError}</p>
          <button type="button" onClick={onRetryValidation}>
            {t("retry", { defaultValue: "Retry" })}
          </button>
        </div>
      )}
      <CountrySelectForm onSelect={() => router.replace(customUrl || "/")} />
    </div>
  );
};

export default CountrySelectPanel;
