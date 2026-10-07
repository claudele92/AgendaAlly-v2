"use client";

import { I18nextProvider } from "react-i18next";
import { ReactNode, useEffect, useRef } from "react";
import { Language } from "@/types/global";
import { setCookie } from "cookies-next";
import { DEFAULT_LANGUAGE_CODE } from "@/config/global";
import useSettingsStore from "@/global-store/settings";
import i18n from "@/lib/i18n";
import { criticalTranslationsFallback } from "@/lib/critical-translations-fallback";

const TranslationsProvider = ({
  children,
  locale,
  translation,
  languages,
}: {
  children: ReactNode;
  locale: string;
  translation?: Record<string, string>;
  languages?: Language[];
}) => {
  const { updateSelectedLanguage } = useSettingsStore();

  const supportedLngs = languages?.length
    ? languages?.map((language) => language?.locale)
    : [DEFAULT_LANGUAGE_CODE as string];
  const defaultLanguage = languages?.find((language) => Boolean(language?.default));
  const lang = locale;
  const language = languages?.find((item) => item?.locale === lang);

  useEffect(() => {
    setCookie("defaultLang", defaultLanguage?.locale || DEFAULT_LANGUAGE_CODE);
    setCookie("locales", supportedLngs);
    setCookie("lang", lang);
    if (language) {
      const html = document.documentElement;
      html.setAttribute("lang", lang);
      html.setAttribute("dir", language?.backward ? "rtl" : "ltr");
      updateSelectedLanguage(language);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // i18n.init() (and the changeLanguage/event-emitting work it does
  // internally) needs to run synchronously on this very first render so the
  // initial HTML — server-rendered and, on hydration, the client's first
  // pass — actually contains translated text instead of raw keys. Every
  // *subsequent* render of this same instance only happens because a parent
  // server component re-ran and handed down new locale/translation props;
  // by then hydration is long done, so nothing needs that resource ready
  // before paint. Re-initializing synchronously in the render body anyway
  // (as this used to do, unconditionally, every render) fires i18next's
  // internal event emitters while some other mounted component elsewhere in
  // the tree (e.g. ConfirmModal, via useTranslation()) can be rendering in
  // the same pass, which is exactly what React's "Cannot update a component
  // while rendering a different component" guard is warning about — so
  // updates beyond the first render are deferred into the effect below.
  const isFirstRender = useRef(true);

  if (isFirstRender.current) {
    i18n.init({
      lng: lang,
      fallbackLng: DEFAULT_LANGUAGE_CODE,
      supportedLngs,
      defaultNS: "translation",
      fallbackNS: "translation",
      ns: "translation",
      // The static fallback is spread first so any real value the backend
      // provides always wins - it only ever fills a gap left by a failed
      // translations fetch (see critical-translations-fallback.ts), it
      // never overrides real, backend-provided text.
      resources: { [lang]: { translation: { ...criticalTranslationsFallback, ...(translation || {}) } } },
      interpolation: {
        escapeValue: false,
      },
    });
  }

  useEffect(() => {
    if (isFirstRender.current) {
      isFirstRender.current = false;
      return;
    }
    i18n.addResourceBundle(
      lang,
      "translation",
      { ...criticalTranslationsFallback, ...(translation || {}) },
      true,
      true
    );
    if (i18n.language !== lang) {
      i18n.changeLanguage(lang);
    }
  }, [lang, translation]);

  return <I18nextProvider i18n={i18n}>{children}</I18nextProvider>;
};

export default TranslationsProvider;
