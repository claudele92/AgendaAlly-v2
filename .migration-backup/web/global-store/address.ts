import { create } from "zustand";
import { persist } from "zustand/middleware";
import { City, Country } from "@/types/global";

interface AddressState {
  country: Country | null;
  city: City | null;
  isCountrySelectModalOpen: boolean;
  // False until persist has actually finished reading localStorage. Needed
  // because persist's rehydration is necessarily asynchronous (localStorage
  // isn't available during SSR, and applying it synchronously on the first
  // client render would itself be a hydration mismatch) - so `country` reads
  // as null on that first render even for a returning visitor who already
  // has one persisted. Code that only wants to act once it actually knows
  // whether a country was persisted (see SettingsProvider) must wait for
  // this flag rather than treating a still-null country as "none selected."
  hasHydrated: boolean;
  updateCountry: (country: Country) => void;
  updateCity: (city: City | null | undefined) => void;
  deleteCountry: () => void;
  openCountrySelectModal: () => void;
  closeCountrySelectModal: () => void;
  setHasHydrated: (hasHydrated: boolean) => void;
}

const useAddressStore = create<AddressState>()(
  persist(
    (set) => ({
      country: null,
      city: null,
      isCountrySelectModalOpen: false,
      hasHydrated: false,
      updateCountry: (country) => set({ country }),
      updateCity: (city) => set({ city }),
      deleteCountry: () => set({ country: null }),
      openCountrySelectModal: () => set({ isCountrySelectModalOpen: true }),
      closeCountrySelectModal: () => set({ isCountrySelectModalOpen: false }),
      setHasHydrated: (hasHydrated) => set({ hasHydrated }),
    }),
    {
      name: "address",
      onRehydrateStorage: () => (state) => {
        state?.setHasHydrated(true);
      },
    }
  )
);

export default useAddressStore;
