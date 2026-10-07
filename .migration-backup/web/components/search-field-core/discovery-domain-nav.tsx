"use client";

import Link from "next/link";
import { usePathname, useSearchParams } from "next/navigation";
import clsx from "clsx";
import useAddressStore from "@/global-store/address";
import { useSettings } from "@/hook/use-settings";

type DiscoveryDomain = "services" | "products" | "businesses" | "specialists";

const destinations: { id: DiscoveryDomain; label: string; href: string }[] = [
  { id: "services", label: "Book services", href: "/search" },
  { id: "products", label: "Shop products", href: "/products" },
  { id: "businesses", label: "Businesses", href: "/shops" },
  { id: "specialists", label: "Specialists", href: "/masters" },
];

interface DiscoveryDomainNavProps {
  active: DiscoveryDomain;
}

export const DiscoveryDomainNav = ({ active }: DiscoveryDomainNavProps) => {
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const country = useAddressStore((state) => state.country);
  const city = useAddressStore((state) => state.city);
  const { settings } = useSettings();
  const locationLabel = [city?.translation?.title, country?.translation?.title]
    .filter(Boolean)
    .join(", ");
  const visibleDestinations = destinations.filter(
    (destination) => destination.id !== "products" || settings?.products_enabled === "1"
  );
  const serviceQuery = pathname.startsWith("/search") ? searchParams.toString() : "";
  const serviceHref = serviceQuery ? `/search?${serviceQuery}` : "/search";
  const businessHref = serviceQuery ? `/shops?${serviceQuery}` : "/shops";

  return (
    <section className="aa-s2-discovery-head mb-6 rounded-2xl border border-[#e7e0d5] bg-[#fbfaf7] px-5 py-5 sm:px-7 sm:py-6">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div className="min-w-0">
          <span className="aa-s2-eyebrow text-xs font-semibold uppercase tracking-[0.12em] text-[#8b603b]">
            Local discovery
          </span>
          <h1 className="mt-2 max-w-3xl text-2xl font-semibold leading-tight text-[#201d19] sm:text-[34px]">
            Book local expertise. Shop local businesses.
          </h1>
          <p className="aa-s2-muted mt-2 max-w-2xl text-sm leading-6 text-[#69645d]">
            Choose a path to browse. Service and shop discovery stays separate from the product
            catalog and its stock filters.
          </p>
        </div>
        <div
          className="aa-s2-chip inline-flex min-h-9 shrink-0 items-center self-start rounded-full border border-[#e7e0d5] bg-white px-3 text-sm font-medium text-[#4f4942]"
          aria-label={`Current discovery location: ${locationLabel || "not selected"}`}
        >
          <span aria-hidden="true" className="mr-2 text-[#8b603b]">
            ●
          </span>
          {locationLabel || "Location not selected"}
        </div>
      </div>
      <nav
        className="aa-s2-domain-tabs mt-5 flex gap-2 overflow-x-auto pb-1"
        aria-label="Discovery categories"
      >
        {visibleDestinations.map((destination) => {
          const selected =
            active === destination.id ||
            (destination.id === "services" && pathname.startsWith("/search/"));
          return (
            <Link
              className={clsx(
                "aa-s2-domain-tab inline-flex min-h-11 shrink-0 items-center rounded-full border px-4 text-sm font-semibold transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#8b603b]",
                selected
                  ? "border-[#2c2925] bg-[#2c2925] text-white"
                  : "border-[#ddd5ca] bg-white text-[#423c35] hover:border-[#8b603b] hover:text-[#714621]"
              )}
              href={
                destination.id === "services"
                  ? serviceHref
                  : destination.id === "businesses"
                    ? businessHref
                    : destination.href
              }
              key={destination.id}
              aria-current={selected ? "page" : undefined}
            >
              {destination.label}
            </Link>
          );
        })}
      </nav>
    </section>
  );
};