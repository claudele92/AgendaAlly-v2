import { Listbox, Transition } from "@headlessui/react";
import useSettingsStore from "@/global-store/settings";
import GlobeIcon from "@/assets/icons/globe";
import { useQuery } from "@tanstack/react-query";
import { globalService } from "@/services/global";
import { Fragment } from "react";
import CheckLineIcon from "remixicon-react/CheckLineIcon";
import LoadingIcon from "@/assets/icons/loading-icon";
import { setCookie } from "cookies-next";

export const LanguageSelect = () => {
  const { selectedLanguage, closeLanguageSelectModal } = useSettingsStore();

  const { data, isLoading } = useQuery(["languages"], () => globalService.languages());

  const handleChange = (locale: string) => {
    const newLanguage = data?.data?.find((item) => item?.locale === locale);
    if (newLanguage && newLanguage?.locale !== selectedLanguage?.locale) {
      setCookie("lang", newLanguage?.locale);
      setCookie("dir", newLanguage?.backward ? "rtl" : "ltr");
      window.location.reload();
      closeLanguageSelectModal();
    }
  };

  return (
    <Listbox value={selectedLanguage?.locale} onChange={handleChange}>
      <div className="relative">
        <Listbox.Button className="w-full py-5 px-4 inline-flex hover:bg-gray-segment dark:hover:bg-gray-darkSegment dark:hover:text-white transition-all hover:text-dark border-b border-gray-link">
          <div className="flex items-center gap-3">
            <GlobeIcon />
            <span className="text-sm font-medium">{selectedLanguage?.title}</span>
          </div>
        </Listbox.Button>
        <Transition
          as={Fragment}
          leave="transition ease-in duration-100"
          leaveFrom="opacity-100"
          leaveTo="opacity-0"
        >
          <Listbox.Options className="absolute left-4 w-[318px] max-h-60 overflow-auto rounded-md bg-white p-1 text-base shadow-lg border">
            {isLoading ? (
              <div className="flex items-center justify-center">
                <LoadingIcon />
              </div>
            ) : (
              data?.data?.map((item) => (
                <Listbox.Option
                  key={item?.locale}
                  value={item?.locale}
                  className="flex items-center cursor-pointer px-2 py-1.5 hover:bg-gray-100 rounded-[4px]"
                >
                  <span className="flex-1 block truncate font-normal text-sm">{item?.title}</span>
                  {item?.locale === selectedLanguage?.locale && (
                    <CheckLineIcon className="size-5" />
                  )}
                </Listbox.Option>
              ))
            )}
          </Listbox.Options>
        </Transition>
      </div>
    </Listbox>
  );
};
