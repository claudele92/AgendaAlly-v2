"use client";

import dynamic from "next/dynamic";
import { LoadingCard } from "@/components/loading";
import useAddressStore from "@/global-store/address";
import { Modal } from "@/components/modal";
import { useEffect, useRef, useState } from "react";
import { countryService, cityService } from "@/services/country";
import { deleteCookie } from "cookies-next";
import NetworkError from "@/utils/network-error";
import { useTranslation } from "react-i18next";

const CountrySelectPanel = dynamic(() => import("./country-select-panel"), {
  loading: () => <LoadingCard />,
});
export const CountrySelect = ({
  settings,
  defaultOpen = true,
}: {
  settings: Record<string, string>;
  defaultOpen: boolean;
}) => {
  const { t } = useTranslation();
  const isCountrySelectModalOpen = useAddressStore((state) => state.isCountrySelectModalOpen);
  const closeCountrySelectModal = useAddressStore((state) => state.closeCountrySelectModal);
  const country = useAddressStore((state) => state.country);
  const city = useAddressStore((state) => state.city);
  const deleteCountry = useAddressStore((state) => state.deleteCountry);
  const updateCity = useAddressStore((state) => state.updateCity);
  const [mounted, setMounted] = useState(false);
  const [countryLookupError, setCountryLookupError] = useState("");
  const [cityLookupError, setCityLookupError] = useState("");
  const [validationRetry, setValidationRetry] = useState(0);
  const countryRequest = useRef(0);
  const cityRequest = useRef(0);
  const isModalOpen = mounted ? isCountrySelectModalOpen || !country?.id : defaultOpen;
  useEffect(() => {
    setMounted(true);
  }, []);

  // A persisted country/city selection (zustand + localStorage, no
  // expiry) can outlive the data it points to - a reseed with a
  // different history than whichever one set this cookie, or in
  // production a country/city later deleted or deactivated. Once that
  // happens, Shop::scopeFilter()'s location match silently returns zero
  // shops instead of erroring - a filter that correctly matches nothing
  // looks identical to a filter that matches nothing because its target
  // no longer exists. Re-validate against the live API once mounted, and
  // clear whichever half no longer resolves rather than trusting a
  // cached id indefinitely; clearing country re-opens this same modal
  // via isModalOpen's existing !country?.id check, no new state needed.
  useEffect(() => {
    const requestId = ++countryRequest.current;
    if (!mounted || !country?.id) {
      return;
    }

    setCountryLookupError("");
    countryService
      .get(country.id)
      .catch((lookupError) => {
        if (requestId !== countryRequest.current) return;
        if (
          lookupError instanceof NetworkError &&
          lookupError.statusCode === 404 &&
          (lookupError.code === "ERROR_404" ||
            (!lookupError.code && lookupError.message.trim().toLowerCase() === "items not found"))
        ) {
          deleteCountry();
          updateCity(null);
          // Mirror the store clear into the cookies used by server discovery.
          deleteCookie("country_id");
          deleteCookie("city_id");
          setCountryLookupError(
            t("saved.country.no.longer.available", {
              defaultValue: "Your saved country is no longer available. Please choose another.",
            })
          );
          return;
        }
        setCountryLookupError(
          t("location.validation.failed", {
            defaultValue: "Your saved location could not be checked. Please retry.",
          })
        );
      });
    return () => {
      countryRequest.current += 1;
    };
  }, [mounted, country?.id, deleteCountry, updateCity, validationRetry, t]);

  useEffect(() => {
    const requestId = ++cityRequest.current;
    if (!mounted || !city?.id) {
      return;
    }

    setCityLookupError("");
    cityService
      .get(city.id)
      .catch((lookupError) => {
        if (requestId !== cityRequest.current) return;
        if (
          lookupError instanceof NetworkError &&
          lookupError.statusCode === 404 &&
          (lookupError.code === "ERROR_404" ||
            (!lookupError.code && lookupError.message.trim().toLowerCase() === "items not found"))
        ) {
          updateCity(null);
          deleteCookie("city_id");
          setCityLookupError(
            t("saved.city.no.longer.available", {
              defaultValue: "Your saved city is no longer available. Choose another or continue without one.",
            })
          );
          return;
        }
        setCityLookupError(
          t("location.validation.failed", {
            defaultValue: "Your saved location could not be checked. Please retry.",
          })
        );
      });
    return () => {
      cityRequest.current += 1;
    };
  }, [mounted, city?.id, updateCity, validationRetry, t]);

  return (
    <Modal
      size="large"
      isOpen={isModalOpen}
      onClose={closeCountrySelectModal}
      withCloseButton={false}
      overflowHidden={false}
    >
      <CountrySelectPanel
        settings={settings}
        validationError={countryLookupError || cityLookupError}
        onRetryValidation={() => setValidationRetry((count) => count + 1)}
      />
    </Modal>
  );
};
