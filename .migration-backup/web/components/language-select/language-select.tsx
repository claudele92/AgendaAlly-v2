"use client";

import { Modal } from "@/components/modal";
import { useTranslation } from "react-i18next";
import { Language } from "@/types/global";
import { setCookie } from "cookies-next";
import useSettingsStore from "@/global-store/settings";
import { useQuery } from "@tanstack/react-query";
import { globalService } from "@/services/global";
import { useEffect, useState } from "react";
import CheckIcon from "@/assets/icons/check";
import EmptyCheckIcon from "@/assets/icons/empty-check";
import { Button } from "@/components/button";
import { useSettings } from "@/hook/use-settings";
import { LoadingCard } from "@/components/loading";

export const LanguageSelect = () => {
  const { t } = useTranslation();
  const { language: selectedLanguage } = useSettings();
  const { isLanguageSelectModalOpen, closeLanguageSelectModal } = useSettingsStore();

  const [localSelectedLanguage, setLocalSelectedLanguage] = useState<Language | undefined>(
    selectedLanguage
  );

  const { data, isLoading } = useQuery(["languages"], () => globalService.languages());

  const handleSelectLanguage = (lang: Language) => {
    setCookie("lang", lang.locale);
    setCookie("dir", lang.backward ? "rtl" : "ltr");
    window.location.reload();
    closeLanguageSelectModal();
  };

  useEffect(() => {
    setLocalSelectedLanguage(selectedLanguage);
  }, [selectedLanguage]);

  return (
    <Modal
      size="small"
      isOpen={isLanguageSelectModalOpen}
      onClose={closeLanguageSelectModal}
      withCloseButton={false}
      overflowHidden={false}
    >
      <div className="md:p-6 p-4 rounded-xl bg-white bg-opacity-80 dark:bg-dark dark:bg-opacity-50 backdrop-blur-md">
        <h1 className="font-medium text-xl mb-5">{t("change.language")}</h1>
        <div className="flex flex-col border border-gray-inputBorder rounded-xl">
          {!isLoading ? (
            data?.data?.map((language, index) => (
              <button
                type="button"
                className={`relative cursor-pointer text-left font-medium select-none py-4 flex items-center gap-2.5 px-5 
                ${index !== 0 && "border-t border-gray-inputBorder"}`}
                onClick={() => setLocalSelectedLanguage(language)}
              >
                {localSelectedLanguage?.id === language?.id ? (
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
                    localSelectedLanguage?.id === language?.id ? "font-medium" : "font-normal"
                  }`}
                >
                  {language?.title}
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
            onClick={closeLanguageSelectModal}
          >
            {t("cancel")}
          </Button>
          <Button
            size="small"
            fullWidth
            onClick={() => {
              if (localSelectedLanguage) {
                handleSelectLanguage(localSelectedLanguage);
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
