"use client";

import { useInfiniteQuery } from "@tanstack/react-query";
import { bookingService } from "@/services/booking";
import { useSettings } from "@/hook/use-settings";
import Link from "next/link";
import { Translate } from "@/components/translate";
import useUserStore from "@/global-store/user";
import clsx from "clsx";
import { usePathname } from "next/navigation";
import { useTranslation } from "react-i18next";
import CalendarIcon from "@/assets/icons/calendar";
import BagIcon from "@/assets/icons/bag";

export const HeaderLinks = () => {
  const { language } = useSettings();
  const { t } = useTranslation();
  const pathname = usePathname();
  const user = useUserStore((state) => state.user);
  const { data } = useInfiniteQuery(
    ["appointments", language?.locale],
    ({ pageParam }) =>
      bookingService.getAll({
        lang: language?.locale,
        page: pageParam,
        parent: 1,
      }),
    {
      getNextPageParam: (lastPage) => lastPage.links.next && lastPage.meta.current_page + 1,
      enabled: !!user,
    }
  );
  const upcomingAppointmentsCount = data?.pages?.[0]?.meta.total ?? 0;
  const tempCount = upcomingAppointmentsCount > 99 ? "99+" : upcomingAppointmentsCount;

  const servicesActive = pathname.startsWith("/shops") || pathname.startsWith("/services");
  const productsActive = pathname.startsWith("/products");

  return (
    <nav
      className="aa-customer-navigation hidden lg:flex"
      aria-label={t("customer.navigation", { defaultValue: "Customer navigation" })}
    >
      <div
        className="aa-customer-nav-switch"
        role="group"
        aria-label={t("marketplace", { defaultValue: "Marketplace" })}
      >
        <Link
          href="/shops"
          className={clsx("aa-customer-nav-choice", servicesActive && "is-active")}
          aria-current={servicesActive ? "page" : undefined}
        >
          <CalendarIcon size={16} />
          {t("book.services", { defaultValue: "Book services" })}
        </Link>
        <Link
          href="/products"
          className={clsx("aa-customer-nav-choice", productsActive && "is-active")}
          aria-current={productsActive ? "page" : undefined}
        >
          <BagIcon size={16} />
          {t("shop.products", { defaultValue: "Shop products" })}
        </Link>
      </div>
      <div className="aa-customer-nav-links">
        <Link href="/shops" className="aa-customer-nav-link" aria-current={pathname.startsWith("/shops") ? "page" : undefined}>
          {t("businesses", { defaultValue: "Businesses" })}
        </Link>
        <Link
          href={user ? "/appointments" : "/login"}
          className="aa-customer-nav-link"
          aria-current={pathname.startsWith("/appointments") ? "page" : undefined}
        >
          <span className="aa-customer-nav-link-label">
            <Translate value="my.appointments" />
            {upcomingAppointmentsCount !== 0 && user && (
              <span className="aa-customer-nav-count">{tempCount}</span>
            )}
          </span>
        </Link>
        <Link
          href={user ? "/orders" : "/login"}
          className="aa-customer-nav-link"
          aria-current={pathname.startsWith("/orders") ? "page" : undefined}
        >
          <Translate value="orders" />
        </Link>
        <Link
          href={user ? "/liked-shops" : "/login"}
          className="aa-customer-nav-link"
          aria-current={pathname.startsWith("/liked-") ? "page" : undefined}
        >
          <Translate value="favorites" />
        </Link>
        <Link href="/blogs" className="aa-customer-nav-link" aria-current={pathname.startsWith("/blogs") ? "page" : undefined}>
          <Translate value="blog" />
        </Link>
      </div>
    </nav>
  );
};
