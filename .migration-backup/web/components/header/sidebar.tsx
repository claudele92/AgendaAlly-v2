"use client";

import React from "react";
import Link from "next/link";
import clsx from "clsx";
import { usePathname } from "next/navigation";
import LogoutIcon from "@/assets/icons/logout";
import { useTranslation } from "react-i18next";
import { useInfiniteQuery } from "@tanstack/react-query";
import useUserStore from "@/global-store/user";
import Store2LineIcon from "remixicon-react/Store2LineIcon";
import CalendarIcon from "@/assets/icons/calendar";
import Settings2LineIcon from "remixicon-react/Settings2LineIcon";
import UserSettingsLineIcon from "remixicon-react/UserSettingsLineIcon";
import BellIcon from "@/assets/icons/bell";
import PortfelIcon from "@/assets/icons/portfel";
import DiscountLineIcon from "@/assets/icons/discount-line";
import AppStoreIcon from "@/assets/icons/app-store";
import PlayMarketIcon from "@/assets/icons/play-market";
import { useSettings } from "@/hook/use-settings";
import { bookingService } from "@/services/booking";
import BagIcon from "@/assets/icons/bag";
import useAddressStore from "@/global-store/address";
import LocationIcon from "@/assets/icons/location";
import useSettingsStore from "@/global-store/settings";
import CurrencyIcon from "@/assets/icons/currency";
import { LanguageSelect } from "@/components/header/language-select";
import HeartIcon from "@/assets/icons/heart";
import StoreIcon from "@/assets/icons/store";
import { useMemo } from "react";

interface ProfileSidebarProps {
  inDrawer?: boolean;
  onClose?: () => void;
  onLogoutButtonClick?: () => void;
}

const linkGroups = [
  {
    title: "discover",
    items: [
      { title: "shops", href: "/shops", icon: <StoreIcon /> },
      { title: "deals", href: "/shops?column=b_count&sort=desc", icon: <DiscountLineIcon /> },
    ],
  },
  {
    title: "shop.products",
    items: [
      { title: "products", href: "/products", icon: <BagIcon size={20} /> },
      { title: "cart", href: "/cart", icon: <BagIcon size={20} /> },
    ],
  },
  {
    title: "your.account",
    items: [
      {
        title: "my.appointments",
        href: "/appointments",
        icon: <CalendarIcon />,
        requireAuth: true,
      },
      { title: "orders", href: "/orders", icon: <BagIcon size={20} />, requireAuth: true },
      { title: "favorites", href: "/liked-shops", icon: <HeartIcon />, requireAuth: true },
      {
        title: "favorite.products",
        href: "/liked-products",
        icon: <HeartIcon />,
        requireAuth: true,
      },
      { title: "Favorite specialists", href: "/liked-masters", icon: <HeartIcon />, requireAuth: true },
      {
        title: "profile.settings",
        href: "/profile",
        icon: <UserSettingsLineIcon size={20} />,
        requireAuth: true,
      },
      {
        title: "notifications",
        href: "/notifications",
        icon: <BellIcon size={20} />,
        requireAuth: true,
      },
    ],
  },
  {
    title: "more",
    items: [
      { title: "blog", href: "/blogs", icon: <Store2LineIcon size={20} /> },
      { title: "app.settings", href: "/settings", icon: <Settings2LineIcon size={20} /> },
      { title: "for.business", href: "/for-business", icon: <PortfelIcon /> },
    ],
  },
];

const ProfileSidebar = ({ inDrawer, onClose, onLogoutButtonClick }: ProfileSidebarProps) => {
  const { t } = useTranslation();

  const { user } = useUserStore();
  const { settings, language } = useSettings();
  const { openCountrySelectModal, country, city } = useAddressStore();
  const { openCurrencySelectModal } = useSettingsStore();
  const pathname = usePathname();

  const { data } = useInfiniteQuery(
    ["appointments", language?.locale],
    ({ pageParam }) =>
      bookingService.getAll({
        lang: language?.locale,
        page: pageParam,
        statuses: ["new", "booked", "progress"],
      }),
    {
      getNextPageParam: (lastPage) => lastPage.links.next && lastPage.meta.current_page + 1,
      enabled: !!user,
    }
  );
  const handleChangeLocation = () => {
    if (onClose) {
      onClose();
    }
    openCountrySelectModal();
  };
  // const handleChangeLanguage = () => {
  //   if (onClose) {
  //     onClose();
  //   }
  //   openLanguageSelectModal();
  // };
  const handleChangeCurrency = () => {
    if (onClose) {
      onClose();
    }
    openCurrencySelectModal();
  };
  const upcomingAppointmentsCount = data?.pages?.[0]?.meta.total ?? 0;
  const tempCount = upcomingAppointmentsCount > 99 ? "99+" : upcomingAppointmentsCount;

  const actualLinkGroups = useMemo(
    () =>
      linkGroups
        .map((group) => ({
          ...group,
          items: group.items.filter((link) => user || !("requireAuth" in link && link.requireAuth)),
        }))
        .filter((group) => group.items.length),
    [user]
  );
  const servicesActive = pathname.startsWith("/shops") || pathname.startsWith("/services");
  const productsActive = pathname.startsWith("/products") || pathname.startsWith("/cart");

  return (
    <div className="aa-nav-sidebar-panel flex flex-col justify-between h-full">
      {inDrawer && (
        <div className="aa-nav-drawer-top">
          <Link href="/" className="aa-nav-drawer-brand" onClick={onClose}>
            <span className="aa-nav-drawer-mark" aria-hidden="true">
              a.
            </span>
            {settings?.title || "AgendaAlly"}
          </Link>
          <button
            type="button"
            className="aa-nav-drawer-close"
            aria-label={t("close", { defaultValue: "Close navigation" })}
            onClick={onClose}
          >
            ×
          </button>
        </div>
      )}
      <div
        className={clsx(
          "flex flex-col gap-7",
          !inDrawer && "w-[290px] sticky top-2 pr-4 pb-4",
          settings?.ui_type === "3" &&
            "lg:bg-white lg:rounded-xl lg:pl-4 lg:pt-4 dark:bg-transparent dark:pl-0 dark:pt-0"
        )}
      >
        <div className="">
          <div className="aa-nav-current-location">
            <span className="aa-nav-location-label">
              {t("current.location", { defaultValue: "CURRENT LOCATION" })}
            </span>
            {!!country && (
              <button
                type="button"
                onClick={handleChangeLocation}
                className="aa-nav-location-button"
              >
                <span className="aa-nav-location-icon">
                  <LocationIcon />
                </span>
                <span className="aa-nav-location-copy">
                  <strong>
                    {country.translation?.title || t("country", { defaultValue: "Country" })}
                  </strong>
                  <small>
                    {city?.translation?.title ||
                      t("choose.city", { defaultValue: "Choose a city" })}
                  </small>
                </span>
                <span aria-hidden="true">⌄</span>
              </button>
            )}
          </div>
          <div
            className="aa-nav-customer-switch"
            role="group"
            aria-label={t("marketplace", { defaultValue: "Marketplace" })}
          >
            <Link
              href="/shops"
              onClick={onClose}
              className={servicesActive ? "is-active" : ""}
              aria-current={servicesActive ? "page" : undefined}
            >
              <CalendarIcon />
              {t("book.services", { defaultValue: "Book services" })}
            </Link>
            <Link
              href="/products"
              onClick={onClose}
              className={productsActive ? "is-active" : ""}
              aria-current={productsActive ? "page" : undefined}
            >
              <BagIcon size={18} />
              {t("shop.products", { defaultValue: "Shop products" })}
            </Link>
          </div>
          <nav
            className="aa-nav-groups"
            aria-label={t("customer.navigation", { defaultValue: "Customer navigation" })}
          >
            {actualLinkGroups.map((group) => (
              <section key={group.title} className="aa-nav-section">
                <h2>{t(group.title, { defaultValue: group.title.replace(/\./g, " ") })}</h2>
                {group.items.map((link) => (
                  <Link
                    key={link.href}
                    onClick={onClose}
                    className={clsx("aa-nav-link", pathname === link.href && "is-current")}
                    href={!user && "requireAuth" in link && link.requireAuth ? "/login" : link.href}
                    aria-current={pathname === link.href ? "page" : undefined}
                  >
                    <span className="aa-nav-link-icon">{link.icon}</span>
                    <span>{t(link.title, { defaultValue: link.title.replace(/\./g, " ") })}</span>
                    {upcomingAppointmentsCount !== 0 && link.href === "/appointments" && user && (
                      <span className="aa-nav-appointment-count">{tempCount}</span>
                    )}
                  </Link>
                ))}
              </section>
            ))}
          </nav>
          <LanguageSelect />
          {/*<button*/}
          {/*  type="button"*/}
          {/*  className="w-full py-5 px-4 inline-flex  text-sm font-medium hover:bg-gray-segment dark:hover:bg-gray-darkSegment dark:hover:text-white transition-all hover:text-dark border-b border-gray-link"*/}
          {/*  onClick={handleChangeLanguage}*/}
          {/*>*/}
          {/*  <div className="flex items-center gap-3">*/}
          {/*    <GlobeIcon />*/}
          {/*    <span className="text-sm font-medium">{t("language")}</span>*/}
          {/*  </div>*/}
          {/*</button>*/}
          <button
            type="button"
            className="w-full py-5 px-4 inline-flex  text-sm font-medium hover:bg-gray-segment dark:hover:bg-gray-darkSegment dark:hover:text-white transition-all hover:text-dark border-b border-gray-link"
            onClick={handleChangeCurrency}
          >
            <div className="flex items-center gap-3">
              <CurrencyIcon />
              <span className="text-sm font-medium">{t("currency")}</span>
            </div>
          </button>
        </div>
      </div>
      {user && (
        <button
          onClick={() => {
            if (onLogoutButtonClick) {
              onLogoutButtonClick();
            }
            if (onClose) {
              onClose();
            }
          }}
          className="aa-nav-logout"
        >
          <LogoutIcon /> {t("logout")}
        </button>
      )}

      <div className="aa-nav-stores px-4 py-10">
        <span className="text-sm font-medium">{t("there.is.more")}</span>
        <div className="flex items-center gap-2.5 mt-2.5">
          <a rel="noreferrer" target="_blank" className="flex-1" href={settings?.customer_app_ios}>
            <div className="rounded-md py-2 px-2.5 bg-gray-link flex items-center flex-1 gap-1.5">
              <AppStoreIcon />
              <div>
                <div className="text-[10px] text-gray-field">Download on the</div>
                <div className="text-base font-semibold text-gray-field">App Store</div>
              </div>
            </div>
          </a>
          <a
            rel="noreferrer"
            target="_blank"
            className="flex-1"
            href={settings?.customer_app_android}
          >
            <div className="rounded-md py-2 px-2.5 bg-gray-link flex items-center flex-1 gap-1.5">
              <PlayMarketIcon />
              <div>
                <div className="text-[10px] text-gray-field">Get it on</div>
                <div className="text-base font-semibold text-gray-field whitespace-nowrap">
                  Google Play
                </div>
              </div>
            </div>
          </a>
        </div>
      </div>
    </div>
  );
};

export default ProfileSidebar;
