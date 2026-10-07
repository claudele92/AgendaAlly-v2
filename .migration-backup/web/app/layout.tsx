import "swiper/css";
import "remixicon/fonts/remixicon.css";
import "./globals.css";
import "@/components/header/stage1-navigation.css";
import localFont from "next/font/local";
import { Metadata } from "next";
import { parseSettings } from "@/utils/parse-settings";
import { cookies } from "next/headers";
import clsx from "clsx";
import { globalService } from "@/services/global";
import NextTopLoader from "nextjs-toploader";
import { cityService, countryService } from "@/services/country";
import {
  DEFAULT_LANGUAGE_CODE,
  DEFAULT_PRIMARY_BUTTON_FONT_COLOR,
  DEFAULT_PRIMARY_COLOR,
} from "@/config/global";
import { translationService } from "@/services/translation";
import { ReactNode } from "react";
import ThemeProvider from "./theme-provider";
import Providers from "./providers";
import TranslationsProvider from "./translations-provider";
import nextDynamic from "next/dynamic";
import {
  AGENDAALLY_BRAND_LOGO,
  resolveAgendaAllyBrandAsset,
  resolveStorefrontFavicon,
} from "@/utils/agendaally-brand-assets";

const CountrySelect = nextDynamic(() =>
  import("@/components/country-select").then((mod) => mod.CountrySelect)
);
const LanguageSelect = nextDynamic(() =>
  import("@/components/language-select").then((mod) => mod.LanguageSelect)
);
const CurrencySelect = nextDynamic(() =>
  import("@/components/currency-select").then((mod) => mod.CurrencySelect)
);

const inter = localFont({
  src: "../public/fonts/inter/InterVariable.woff2",
  weight: "100 900",
  display: "swap",
  variable: "--font-agendaally",
});

// Stage 2's root marketplace is no longer selected by ui_type. Legacy
// home-2/3/4 URLs now redirect to the approved root storefront.
if (process.env.NEXT_PUBLIC_UI_TYPE) {
  console.warn(
    `[layout] NEXT_PUBLIC_UI_TYPE="${process.env.NEXT_PUBLIC_UI_TYPE}" is set - ` +
      "this overrides legacy UI styling. The approved Stage 2 root marketplace " +
      "remains at /. Legacy home-2/3/4 URLs redirect to the approved root."
  );
}

// Reads cookies() below, which should already force dynamic rendering per
// Next's docs - but this exact codebase has already shown that
// auto-detection doesn't reliably hold (see the homepage page.tsx files'
// identical directive from PR #102). Since every page in the app renders
// through this root layout, a stale render here - locale-driven settings,
// translations - would silently propagate everywhere.
export const dynamic = "force-dynamic";

export const generateMetadata = async (): Promise<Metadata> => {
  const settings = await globalService.settings().catch((e) => console.log(e));
  const parsedSettings = parseSettings(settings?.data);
  return {
    metadataBase: new URL(process.env.NEXT_PUBLIC_WEBSITE_URL),
    title: {
      template: `%s | ${parsedSettings.title}`,
      default: parsedSettings.title,
    },
    icons: {
      icon: resolveStorefrontFavicon(parsedSettings.favicon),
    },
    description: "Book local professionals and shop products from trusted businesses across Cameroon.",
    openGraph: {
      images: [
        {
          url:
            resolveAgendaAllyBrandAsset(parsedSettings.logo, "logo") ||
            AGENDAALLY_BRAND_LOGO,
          width: 2172,
          height: 724,
        },
      ],
      title: parsedSettings.title,
      description: "Book local professionals and shop products from trusted businesses across Cameroon.",
      siteName: parsedSettings.title,
    },
  };
};

const RootLayout = async ({ children }: { children: ReactNode }) => {
  const languages = await globalService.languages().catch((e) => console.log("language error", e));
  const currencies = await globalService
    .currencies()
    .catch((e) => console.log("currency error", e));
  const selectedLocale = (await cookies()).get("lang")?.value || "en";
  const selectedDirection = (await cookies()).get("dir")?.value;

  const defaultLanguage = languages?.data?.find((lang) => Boolean(lang?.default));
  const lang = (selectedLocale || defaultLanguage?.locale || DEFAULT_LANGUAGE_CODE) as string;
  const defaultCurrency = currencies?.data?.find((currency) => Boolean(currency?.default));
  const settings = await globalService
    .settings()
    .then((res) => res.data)
    .catch((e) => console.log("settings error", e));
  const parsedSettings = parseSettings(typeof settings === "object" ? settings : []);
  if (process.env.NEXT_PUBLIC_UI_TYPE) {
    parsedSettings.ui_type = process.env.NEXT_PUBLIC_UI_TYPE;
  }
  let defaultCountry;
  if (parsedSettings?.default_country_id?.length) {
    defaultCountry = await countryService
      .get(Number(parsedSettings.default_country_id))
      .then((res) => {
        if (parsedSettings?.default_city_id?.length) {
          return cityService.get(Number(parsedSettings.default_city_id)).then((city) => {
            res.data.city = city.data;
            return res.data;
          });
        }
        return res.data;
      })
      .catch((e) => {
        console.log("default country/city error", e);
        return undefined;
      });
  }
  const translation = await translationService.getAll(lang).catch((error) => {
    console.log(error);
  });

  const primaryColor = parsedSettings?.primary_color || DEFAULT_PRIMARY_COLOR;
  const primaryButtonFontColor =
    parsedSettings?.primary_button_font_color || DEFAULT_PRIMARY_BUTTON_FONT_COLOR;

  return (
    <html
      lang={selectedLocale || defaultLanguage?.locale || "en"}
      dir={selectedDirection || (defaultLanguage?.backward ? "rtl" : "ltr")}
      style={
        {
          "--primary": primaryColor,
          "--primary-button-font-color": primaryButtonFontColor,
        } as React.CSSProperties
      }
      // next-themes (via ThemeProvider below) sets className/color-scheme on
      // <html> itself once it resolves the theme client-side, which by
      // design differs from the server-rendered markup for one render -
      // see https://github.com/pacocoursey/next-themes#with-app
      suppressHydrationWarning
    >
      <body className={clsx(inter.className, inter.variable)}>
        <div id="portal" />
        <TranslationsProvider
          locale={lang}
          translation={translation?.data}
          languages={languages?.data}
        >
          <ThemeProvider attribute="class" defaultTheme="light">
            <Providers
              currencies={currencies?.data}
              defaultCurrency={defaultCurrency}
              settings={parsedSettings}
              defaultCountry={defaultCountry}
            >
              {children}
              <CountrySelect
                defaultOpen={(await cookies()).get("showCountryDialog")?.value !== "false"}
                settings={parsedSettings}
              />
              <LanguageSelect />
              <CurrencySelect />
            </Providers>
            <NextTopLoader color="#BB9B6A" showSpinner={false} />
          </ThemeProvider>
        </TranslationsProvider>
      </body>
    </html>
  );
};

export default RootLayout;
