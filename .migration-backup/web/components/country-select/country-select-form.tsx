import React, { useState, useTransition } from "react";
import { Button } from "@/components/button";
import { useTranslation } from "react-i18next";
import useAddressStore from "@/global-store/address";
import { City, Country } from "@/types/global";
import { deleteCookie, setCookie } from "cookies-next";
import { AsyncSelect } from "@/components/async-select";
import useCartStore from "@/global-store/cart";
import { useRouter } from "next/navigation";

// Matches the zustand `address` store's localStorage persistence
// (effectively indefinite) - see handleSaveAddress below for why these two
// need to expire on the same schedule.
const COUNTRY_COOKIE_MAX_AGE = 60 * 60 * 24 * 365;

export const CountrySelectForm = ({ onSelect }: { onSelect: () => void }) => {
  const { t } = useTranslation();
  const router = useRouter();
  const selectedCountry = useAddressStore((state) => state.country);
  const updateCountry = useAddressStore((state) => state.updateCountry);
  const closeCountrySelectModal = useAddressStore((state) => state.closeCountrySelectModal);
  const selectedCity = useAddressStore((state) => state.city);
  const updateCity = useAddressStore((state) => state.updateCity);
  const [tempCountry, setTempCountry] = useState(selectedCountry);
  const [tempCity, setTempCity] = useState(selectedCity);
  const clearLocalCart = useCartStore((state) => state.clear);
  const [isPending, startTransition] = useTransition();
  const [isPressed, setIsPressed] = useState(false);

  const handleSaveAddress = () => {
    setIsPressed(true);
    if (tempCountry) {
      setIsPressed(false);
      updateCountry(tempCountry);
      // Without maxAge this is a session cookie (cleared when the browser
      // closes), while the zustand `address` store it's meant to mirror is
      // persisted to localStorage (never expires). On a returning visit
      // after the cookie expired but localStorage hadn't, the server (SSR,
      // reads this cookie) and the client (reads the still-persisted
      // zustand store) would resolve two different countries for the same
      // page load - the server's shop/master lists would reflect one
      // country, the client's immediate refetch another, replacing cards
      // right after mount. One year keeps both sources in lockstep.
      setCookie("country_id", tempCountry.id, { maxAge: COUNTRY_COOKIE_MAX_AGE });
      clearLocalCart();
    }
    if (tempCity) {
      setIsPressed(false);
      updateCity(tempCity);
      setCookie("city_id", tempCity.id, { maxAge: COUNTRY_COOKIE_MAX_AGE });
    } else {
      updateCity(null);
      deleteCookie("city_id");
    }

    closeCountrySelectModal();
    startTransition(() => onSelect());
    router.refresh();
  };
  return (
    <div className="flex flex-col gap-4">
      <AsyncSelect
        label="select.country"
        onSelect={(value) => {
          setTempCountry(value);
          setTempCity(null);
        }}
        extractTitle={(option) => option?.translation?.title as string}
        extractKey={(option) => option?.id}
        queryKey="v1/rest/countries"
        queryParams={{ country_id: tempCountry?.id, active: 1 }}
        size="medium"
        value={tempCountry as Country}
        error={isPressed && !tempCountry ? t("select.country") : undefined}
      />
      <AsyncSelect
        label="select.city"
        onSelect={(value) => setTempCity(value)}
        extractTitle={(option) => option?.translation?.title as string}
        extractKey={(option) => option?.id}
        queryKey="v1/rest/cities"
        queryEnabled={Boolean(tempCountry?.id)}
        disabled={!tempCountry?.id}
        size="medium"
        queryParams={{ country_id: tempCountry?.id }}
        value={tempCity as City}
        clearable
        onClear={() => setTempCity(null)}
      />
      <p className="-mt-3 text-xs text-gray-field">
        {t("city.optional.location", {
          defaultValue: "City is optional. Leave it blank to explore across your selected country.",
        })}
      </p>
      <Button
        loading={isPending}
        onClick={handleSaveAddress}
        disabled={!tempCountry || isPending}
        fullWidth
        size="small"
        color="black"
      >
        {t("save.address")}
      </Button>
      {selectedCountry && <button type="button" className="aa-stage2-location-cancel" onClick={closeCountrySelectModal}>
        {t("cancel", { defaultValue: "Cancel" })}
      </button>}
    </div>
  );
};
