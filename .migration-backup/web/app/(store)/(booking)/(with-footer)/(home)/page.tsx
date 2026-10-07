import { cookies } from "next/headers";
import React from "react";
import nextDynamic from "next/dynamic";
import { categoryService } from "@/services/category";
import { shopService } from "@/services/shop";
import { globalService } from "@/services/global";
import { brandService } from "@/services/brand";
import storyService from "@/services/story";
import { parseSettings } from "@/utils/parse-settings";
import { resolveDefaultLocation } from "@/utils/resolve-default-location";
import type { Category } from "@/types/category";
import { Header } from "@/components/header";
import { SlidableProductList } from "@/components/slidable-product-list";
import { MarketplaceHero } from "./components/marketplace-hero";
import { ServiceCategoriesGrid } from "./components/service-categories-grid";
import { DiscoveryPaths } from "./components/discovery-paths";
import { Brands } from "./components/brands";
import { HomePathways } from "./components/home-pathways";

// Reads location cookies for location-scoped server data, so keep this route
// dynamic instead of allowing a visitor's marketplace sections to be frozen.
export const dynamic = "force-dynamic";

const Stories = nextDynamic(() => import("../../components/stories"), {
  loading: () => (
    <div
      className="flex max-w-full gap-4 overflow-hidden animate-pulse"
      aria-label="Loading business updates"
      aria-busy="true"
    >
      {Array.from({ length: 6 }, (_, item) => (
        <div
          className="h-[168px] w-[112px] shrink-0 rounded-lg border border-[#ded8cd] bg-[#e8e1d5] lg:h-[266px] lg:w-[168px]"
          key={item}
        />
      ))}
    </div>
  ),
});

const Deals = nextDynamic(
  () => import("./components/deals").then((component) => ({ default: component.Deals }))
);
const NearYou = nextDynamic(
  () => import("./components/near-you").then((component) => ({ default: component.NearYou }))
);
const Salons = nextDynamic(
  () => import("./components/salons").then((component) => ({ default: component.Salons }))
);
const Masters = nextDynamic(
  () => import("./components/masters").then((component) => ({ default: component.Masters }))
);
const Recommended = nextDynamic(
  () =>
    import("./components/recomended").then((component) => ({
      default: component.Recommended,
    }))
);

const getServiceCategories = async (lang: string): Promise<Category[]> => {
  const categories: Category[] = [];
  let page = 1;
  let lastPage = 1;

  do {
    const response = await categoryService.getAll({
      lang,
      type: "service",
      perPage: 100,
      page,
      column: "input",
      sort: "asc",
    });
    categories.push(...response.data);
    lastPage = response.meta.last_page;
    page = response.meta.current_page + 1;
  } while (page <= lastPage);

  return categories;
};

const HomePage = async () => {
  const cookieStore = await cookies();
  const lang = cookieStore.get("lang")?.value || "en";
  const cookieCountryId = cookieStore.get("country_id")?.value || undefined;
  const cookieCityId = cookieStore.get("city_id")?.value || undefined;

  const [settings, serviceCategories, brands, stories] = await Promise.all([
    globalService.settings().catch(() => undefined),
    getServiceCategories(lang).catch(() => undefined),
    brandService.getAll().catch(() => undefined),
    storyService.getAll({ lang }).catch(() => undefined),
  ]);

  const parsedSettings = parseSettings(settings?.data);
  const productsEnabled = parsedSettings?.products_enabled === "1";
  const { countryId, cityId } = resolveDefaultLocation(
    cookieCountryId,
    cookieCityId,
    parsedSettings
  );
  const locationParams = {
    lang,
    perPage: 8,
    country_id: countryId,
    city_id: cityId,
    location_type: "2",
  };

  const [recommendedShops, dealShops, nearbyShops] = await Promise.all([
    shopService
      .getAll({ ...locationParams, column: "r_avg", sort: "desc" })
      .catch(() => undefined),
    shopService
      .getAll({ ...locationParams, column: "b_count", sort: "desc" })
      .catch(() => undefined),
    shopService.getAll(locationParams).catch(() => undefined),
  ]);

  return (
    <div className="aa-s2">
      <Header isHidden={false} showLinks settings={parsedSettings} />
      <MarketplaceHero
        serviceCategories={serviceCategories || []}
        productsEnabled={productsEnabled}
      />
      <main className="aa-s2-wrap">
        <DiscoveryPaths productsEnabled={productsEnabled} />

        {!!stories?.length && (
          <section className="aa-s2-section">
            <div className="aa-s2-section-head">
              <div>
                <span className="aa-s2-eyebrow">Business updates</span>
                <h2>Recent notes from local businesses.</h2>
              </div>
            </div>
            <Stories data={stories} buttonVariant="1" />
          </section>
        )}
        {stories === undefined && (
          <section className="aa-s2-section" aria-live="polite">
            <div className="rounded-lg border border-[#d9cbb6] bg-[#f8f3e9] px-5 py-4">
              <span className="aa-s2-eyebrow">Business updates</span>
              <p className="mt-1 text-sm text-[#51483b]">
                Business updates are temporarily unavailable.
              </p>
            </div>
          </section>
        )}

        <ServiceCategoriesGrid
          categories={serviceCategories || []}
          loadError={!serviceCategories}
        />

        <section className="aa-s2-section">
          <div className="aa-s2-section-head">
            <div>
              <span className="aa-s2-eyebrow">Businesses and people</span>
              <h2>Explore businesses and specialists.</h2>
            </div>
          </div>
          <Recommended data={recommendedShops} />
          <Masters />
        </section>

        <section className="aa-s2-section">
          <div className="aa-s2-section-head">
            <div>
              <span className="aa-s2-eyebrow">Business discovery</span>
              <h2>More ways to browse businesses.</h2>
            </div>
          </div>
          <Deals data={dealShops} />
          <Salons />
        </section>

        {productsEnabled && (
          <>
            <section className="aa-s2-section">
              <div className="aa-s2-section-head">
                <div>
                  <span className="aa-s2-eyebrow">Product shops</span>
                  <h2>Browse product catalogs.</h2>
                  <p className="aa-s2-muted">
                    Find useful products from businesses across your community.
                  </p>
                </div>
              </div>
              <div className="rounded-2xl border border-[#e8e3d9] bg-[#fffefa] p-4 sm:p-6">
                <SlidableProductList
                  title="products"
                  link="/products"
                  visibleListCount={4}
                />
              </div>
            </section>
            <section className="aa-s2-section">
              <div className="aa-s2-section-head">
                <div>
                  <span className="aa-s2-eyebrow">Product brands</span>
                  <h2>Browse product brands.</h2>
                </div>
              </div>
              <Brands data={brands} />
            </section>
          </>
        )}

        <section className="aa-s2-section">
          <div className="aa-s2-section-head">
            <div>
              <span className="aa-s2-eyebrow">Businesses</span>
              <h2>Explore businesses.</h2>
            </div>
          </div>
          <NearYou data={nearbyShops} />
        </section>

        <section className="aa-s2-section">
          <HomePathways />
        </section>
      </main>
    </div>
  );
};

export default HomePage;