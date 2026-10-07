"use client";

import { Modal } from "@/components/modal";
import useUserStore from "@/global-store/user";
import { useTranslation } from "react-i18next";
import { Currency } from "@/types/global";
import { setCookie } from "cookies-next";
import useSettingsStore from "@/global-store/settings";
import { useMutation, useQuery } from "@tanstack/react-query";
import { userService } from "@/services/user";
import { globalService } from "@/services/global";
import React, { useEffect, useState } from "react";
import CheckIcon from "@/assets/icons/check";
import EmptyCheckIcon from "@/assets/icons/empty-check";
import { Button } from "@/components/button";
import { useSettings } from "@/hook/use-settings";
import { LoadingCard } from "@/components/loading";

export const CurrencySelect = () => {
  const { t } = useTranslation();
  const user = useUserStore((state) => state.user);
  const { currency: selectedCurrency } = useSettings();
  const { isCurrencySelectModalOpen, closeCurrencySelectModal, updateSelectedCurrency } =
    useSettingsStore();

  const [localSelectedCurrency, setLocalSelectedCurrency] = useState<Currency | undefined>(
    selectedCurrency
  );

  // This modal is mounted once, globally, and never unmounts on close, so
  // its local staging state can otherwise go stale relative to the shared
  // store (e.g. after the currency is changed elsewhere, like App Settings,
  // or after the store's persisted value rehydrates post-mount). Resync
  // whenever the modal is (re)opened or the real selection changes so
  // "Save" never silently recommits a stale value.
  useEffect(() => {
    if (isCurrencySelectModalOpen) {
      setLocalSelectedCurrency(selectedCurrency);
    }
  }, [isCurrencySelectModalOpen, selectedCurrency]);

  const { data, isLoading } = useQuery(["currencies"], () => globalService.currencies());

  const { mutate: updateCurrency } = useMutation({
    mutationFn: (currencyId: number) => userService.updateCurrency(currencyId),
  });

  const handleSelectCurrency = (currency: Currency) => {
    updateSelectedCurrency(currency);
    setCookie("currency_id", currency.id);
    if (user) {
      updateCurrency(currency.id);
    }
    closeCurrencySelectModal();
  };

  return (
    <Modal
      size="small"
      isOpen={isCurrencySelectModalOpen}
      onClose={closeCurrencySelectModal}
      withCloseButton={false}
      overflowHidden={false}
    >
      <div className="md:p-6 p-4 rounded-xl bg-white bg-opacity-80 dark:bg-dark dark:bg-opacity-50 backdrop-blur-md">
        <h1 className="font-medium text-xl mb-5">{t("change.currency")}</h1>
        <div className="flex flex-col border border-gray-inputBorder rounded-xl">
          {!isLoading ? (
            data?.data?.map((currency, index) => (
              <button
                key={currency.id}
                type="button"
                className={`relative cursor-pointer text-left font-medium select-none py-4 flex items-center gap-2.5 px-5
                ${index !== 0 && "border-t border-gray-inputBorder"}`}
                onClick={() => setLocalSelectedCurrency(currency)}
              >
                {localSelectedCurrency?.id === currency?.id ? (
                  <div className="text-primary dark:text-white">
                    <CheckIcon />
                  </div>
                ) : (
                  <div className="text-gray-field">
                    <EmptyCheckIcon />
                  </div>
                )}
                <span
                  className={`block truncate ${
                    localSelectedCurrency?.id === currency?.id ? "font-medium" : "font-normal"
                  }`}
                >
                  {currency?.title}
                </span>
              </button>
            ))
          ) : (
            <LoadingCard />
          )}
        </div>
        <div className="flex items-center gap-x-1.5 mt-5">
          <Button
            className="!py-2"
            size="small"
            color="blackOutlined"
            fullWidth
            onClick={closeCurrencySelectModal}
          >
            {t("cancel")}
          </Button>
          <Button
            size="small"
            fullWidth
            onClick={() => {
              if (localSelectedCurrency) {
                handleSelectCurrency(localSelectedCurrency);
              }
            }}
          >
            {t("save")}
          </Button>
        </div>
      </div>
    </Modal>
  );
};
