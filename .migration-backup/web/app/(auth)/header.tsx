"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useTranslation } from "react-i18next";
import {
  AGENDAALLY_BRAND_LOGO,
  resolveAgendaAllyBrandAsset,
} from "@/utils/agendaally-brand-assets";

const AuthHeader = ({ settings }: { settings: Record<string, string> }) => {
  const pathname = usePathname();
  const { t } = useTranslation();
  const isLoginPage = pathname.includes("/login");
  const logo = resolveAgendaAllyBrandAsset(settings.logo, "logo") || AGENDAALLY_BRAND_LOGO;
  return (
    <header className="aa-auth-top">
      <Link className="aa-brand" href="/" aria-label={settings.title || "AgendaAlly"}>
        <img className="aa-auth-brand-logo" src={logo} alt="" aria-hidden="true" />
      </Link>
      <div className="aa-auth-top-right">
        <a
          className="aa-text-link aa-business-link"
          href={process.env.NEXT_PUBLIC_ADMIN_PANEL_URL}
          target="_blank"
          rel="noreferrer"
        >
          {t("for.business")}
        </a>
        <Link
          className="aa-button aa-button-secondary aa-auth-header-action"
          href={isLoginPage ? "/sign-up" : "/login"}
        >
          {isLoginPage ? t("sign.up") : t("login")}
          <span aria-hidden="true">→</span>
        </Link>
      </div>
    </header>
  );
};

export default AuthHeader;
