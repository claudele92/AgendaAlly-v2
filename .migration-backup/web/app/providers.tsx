"use client";

import { QueryClientProvider } from "@tanstack/react-query";
import { createQueryClient } from "@/lib/query-client";
import SettingsProvider from "@/context/settings/settings";
import { Country, Currency, Language } from "@/types/global";
import { ReactQueryDevtools } from "@tanstack/react-query-devtools";
import { PropsWithChildren, useState } from "react";
import { useBfcacheAuthGuard } from "@/hook/use-bfcache-auth-guard";

interface ProviderProps extends PropsWithChildren {
  defaultCurrency?: Currency;
  defaultLanguage?: Language;
  settings?: Record<string, string>;
  currencies?: Currency[];
  defaultCountry?: Country;
}

const Providers = ({
  children,
  defaultCurrency,
  defaultLanguage,
  settings,
  currencies,
  defaultCountry,
}: ProviderProps) => {
  const [client] = useState(() => createQueryClient());

  useBfcacheAuthGuard();

  return (
    <QueryClientProvider client={client}>
      <SettingsProvider
        currencies={currencies}
        defaultLanguage={defaultLanguage}
        defaultCurrency={defaultCurrency}
        settings={settings}
        defaultCountry={defaultCountry}
      >
        {children}
      </SettingsProvider>
      <ReactQueryDevtools />
    </QueryClientProvider>
  );
};

export default Providers;
