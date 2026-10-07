"use client";

import Link from "next/link";
import clsx from "clsx";
import dynamic from "next/dynamic";
import { BackButton } from "@/components/back-button";
import CountryIndicator from "@/components/country-indicator/country-indicator";
import { HeaderLinks } from "./links";
import { Stage2Brand } from "@/components/stage2";
import useAddressStore from "@/global-store/address";
import { useLogo } from "@/hook/use-logo";
import {
  AGENDAALLY_BRAND_MARK,
  resolveAgendaAllyBrandAsset,
} from "@/utils/agendaally-brand-assets";

const HeaderButtons = dynamic(
  () =>
    import("@/components/header-buttons").then((component) => ({
      default: component.HeaderButtons,
    })),
  {
    loading: () => (
      <div className="flex items-center gap-5">
        <div className="rounded-button bg-gray-300 w-44 h-10 lg:block hidden" />
        <div className="rounded-button bg-gray-300 lg:w-40 w-10 h-10" />
      </div>
    ),
  }
);
const MobileSidebar = dynamic(() => import("./mobile-sidebar"));

interface HeaderProps {
  settings?: Record<string, string>;
  borderBottom?: boolean;
  showLinks?: boolean;
  isHidden?: boolean;
  showOnlyBackButton?: boolean;
  showBusinessButton?: boolean;
}

export const Header = ({
  settings,
  showLinks,
  borderBottom = false,
  isHidden = true,
  showOnlyBackButton,
  showBusinessButton = true,
}: HeaderProps) => {
  const logo = useLogo(settings);
  const symbol =
    resolveAgendaAllyBrandAsset(settings?.favicon, "mark") || AGENDAALLY_BRAND_MARK;
  const country = useAddressStore((state) => state.country);
  const city = useAddressStore((state) => state.city);
  const currentLocation = [city?.translation?.title, country?.translation?.title]
    .filter(Boolean)
    .join(", ");

  return (
    <div className="aa-s2-native aa-s2-native-header-region">
      {!showOnlyBackButton && (
        <div className="aa-s2-topline">
          <div className="aa-s2-wrap aa-s2-topline-inner">
            <span className="aa-s2-topline-desktop">
              Book local expertise. Shop local businesses.
            </span>
            <span className="aa-s2-topline-mobile">
              {currentLocation || "Choose a city"}
            </span>
            <Link href={showBusinessButton ? "/for-business" : "/"}>
              {showBusinessButton
                ? settings?.ui_type === "3"
                  ? "AgendaAlly for Business"
                  : "For business"
                : "Marketplace"}
            </Link>
          </div>
        </div>
      )}
      <header
        className={clsx(
          "aa-s2-header aa-s2-native-header",
          borderBottom && "aa-s2-header-bordered"
        )}
      >
        <div className="aa-s2-wrap aa-s2-navrow">
          {!showOnlyBackButton && (
            <MobileSidebar
              isHidden={isHidden || !["2", "3", "4"].includes(settings?.ui_type || "")}
            />
          )}
          <div className="aa-s2-native-brand-location">
            <Link
              href="/"
              className={clsx("aa-s2-brand-link", showOnlyBackButton && "hidden")}
              aria-label={settings?.title || "AgendaAlly home"}
            >
              <Stage2Brand logo={logo} symbol={symbol} />
            </Link>
            <CountryIndicator />
            {showOnlyBackButton && (
              <div className="lg:hidden">
                <BackButton />
              </div>
            )}
          </div>
          {showLinks && <HeaderLinks />}
          <HeaderButtons
            canOpenDrawer={showOnlyBackButton}
            showBusinessButton={false}
          />
        </div>
      </header>
    </div>
  );
};
