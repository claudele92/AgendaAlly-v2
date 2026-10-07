import React from "react";
import { notFound } from "next/navigation";
import fetcher from "@/lib/fetcher";
import { Paginate } from "@/types/global";
import { Banner } from "@/types/banner";
import { buildUrlQueryParams } from "@/utils/build-url-query-params";
import { cookies } from "next/headers";
import { globalService } from "@/services/global";
import { parseSettings } from "@/utils/parse-settings";
import { Banners } from "./components/banners";
import { FilterList } from "./components/filters/filter-list";
import FilteredProductList from "./components/filtered-product-list";
import { Brands } from "./components/brands";
import { DiscoveryDomainNav } from "@/components/search-field-core/discovery-domain-nav";

const Products = async () => {
  const lang = (await cookies()).get("lang")?.value || "en";
  // Banners is one decorative section on this page - Banners already
  // declares its prop optional and falls back to its own client-side
  // react-query fetch when initialData is missing (same pattern as the
  // homepage widgets), so failing it open to undefined degrades just this
  // section instead of the whole page.
  const banners = await fetcher<Paginate<Banner>>(
    buildUrlQueryParams("v1/rest/banners/paginate", { lang }),
    {
      cache: "no-cache",
    }
  ).catch(() => undefined);
  const settings = await globalService.settings().catch((e) => console.log("settings error", e));
  const parsedSettings = parseSettings(settings?.data);
  const productsEnabled = parsedSettings?.products_enabled === "1";

  if (!productsEnabled) {
    return notFound();
  }

  return (
    <div className="aa-s2-native">
      <div className="my-7">
        <Banners banners={banners} />
      </div>
      <div className="px-2 xl:container">
        <DiscoveryDomainNav active="products" />
        <Brands />
        <div className="grid xl:grid-cols-9 grid-cols-1 lg:gap-7 md:gap-4 gap-2">
          <div className="xl:col-span-2 hidden xl:block relative">
            <FilterList />
          </div>
          <FilteredProductList />
        </div>
      </div>
    </div>
  );
};

export default Products;
