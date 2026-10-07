import { Currency, Language } from "@/types/global";
import { create } from "zustand";
import { persist } from "zustand/middleware";

interface SettingsStoreState {
  selectedLanguage?: Language;
  selectedCurrency?: Currency;
  updateSelectedLanguage: (lang?: Language) => void;
  updateSelectedCurrency: (currency?: Currency) => void;
  settings?: Record<string, string>;
  updateSettings: (settings: Record<string, string>) => void;
  updateDefaultCurrency: (currency: Currency) => void;
  defaultCurrency?: Currency;
  isLanguageSelectModalOpen: boolean;
  openLanguageSelectModal: () => void;
  closeLanguageSelectModal: () => void;
  isCurrencySelectModalOpen: boolean;
  openCurrencySelectModal: () => void;
  closeCurrencySelectModal: () => void;
}

const useSettingsStore = create<SettingsStoreState>()(
  persist(
    (set) => ({
      isLanguageSelectModalOpen: false,
      isCurrencySelectModalOpen: false,
      updateSelectedCurrency: (selectedCurrency) => set({ selectedCurrency }),
      updateSelectedLanguage: (selectedLanguage) => set({ selectedLanguage }),
      updateSettings: (settings) => set({ settings }),
      updateDefaultCurrency: (currency) => set({ defaultCurrency: currency }),
      openLanguageSelectModal: () => set({ isLanguageSelectModalOpen: true }),
      closeLanguageSelectModal: () => set({ isLanguageSelectModalOpen: false }),
      openCurrencySelectModal: () => set({ isCurrencySelectModalOpen: true }),
      closeCurrencySelectModal: () => set({ isCurrencySelectModalOpen: false }),
    }),
    { name: "settings" }
  )
);

export default useSettingsStore;
