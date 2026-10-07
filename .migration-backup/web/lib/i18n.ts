import i18n from "i18next";
import { initReactI18next } from "react-i18next";
import I18NextChainedBackend from "i18next-chained-backend";

i18n.use(I18NextChainedBackend).use(initReactI18next);

export default i18n;
