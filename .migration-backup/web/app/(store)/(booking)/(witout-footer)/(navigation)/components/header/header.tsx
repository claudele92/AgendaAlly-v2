"use client";

import Link from "next/link";
import { SearchField } from "@/components/main-search-field";
import { HeaderButtons } from "@/components/header-buttons/header-buttons";
import { BackButton } from "@/components/back-button";
import { ImageWithFallBack } from "@/components/image";
import { useLogo } from "@/hook/use-logo";
import "./header.css";

interface HeaderProps {
  settings?: Record<string, string>;
}

export const NavigationHeader = ({ settings }: HeaderProps) => {
  const logo = useLogo(settings);
  return (
    <header className="aa-customer-header border-b border-gray-link">
      <div className="aa-customer-header-inner">
        <Link href="/" className="aa-customer-header-brand" aria-label={settings?.title || "AgendaAlly home"}>
          {logo && (
            <ImageWithFallBack
              src={logo}
              alt={settings?.title || "logo"}
              width={156}
              height={52}
              className="aa-customer-header-wordmark object-contain"
            />
          )}
        </Link>
        <div className="aa-customer-header-back">
          <BackButton />
        </div>
        <SearchField isInHeader />
        <div className="aa-customer-header-actions">
          <HeaderButtons />
        </div>
      </div>
    </header>
  );
};
