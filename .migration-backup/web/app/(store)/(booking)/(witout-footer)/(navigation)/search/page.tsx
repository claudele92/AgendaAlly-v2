"use client";

import { SearchField } from "@/components/main-search-field";
import { useModal } from "@/hook/use-modal";
import { Drawer } from "@/components/drawer";
import clsx from "clsx";
import { useSearchParams } from "next/navigation";
import { useQueryParams } from "@/hook/use-query-params";
import { Shops } from "./components/shops";
import { ShopsInMap } from "./components/shops-in-map";
import { DiscoveryDomainNav } from "@/components/search-field-core/discovery-domain-nav";
// Statically imported (not next/dynamic) to match the already-stable
// /products pattern - lazy-loading this as a separate chunk raced with
// hydration's useId() allocation for every RadioGroup inside it, flipping
// the "headlessui-radiogroup-*" ids between the server-rendered HTML and
// the client's first render (a timing race, not deterministic per-load).
import { FilterList as Filters } from "../components/filters/filter-list";
const SearchPage = () => {
  const [isFilterModalOpen, openFilterModal, closeFilterModal] = useModal();
  const searchParams = useSearchParams();
  const { setQueryParams } = useQueryParams({ push: true });
  const isMapMode = searchParams.get("mode") === "map";
  const hasDateOrTimeFilter =
    searchParams.has("date") || searchParams.has("timeFrom") || searchParams.has("timeTo");
  return (
    <div className={clsx("aa-s2-native h-full", isMapMode && "!px-0")}>
      {!isMapMode && (
        <div className="xl:container px-4 pt-6">
          <DiscoveryDomainNav active="services" />
          {hasDateOrTimeFilter && (
            <p className="aa-s2-preview mb-4 rounded-xl border border-[#e7e0d5] bg-[#fbfaf7] px-4 py-3 text-sm text-[#514b44]">
              Date and time narrow discovery only. Confirm an available slot in the booking flow.
            </p>
          )}
        </div>
      )}
      <div
        className={clsx(
          "grid xl:grid-cols-12 grid-cols-5 xl:container px-4 h-full",
          isMapMode && "!px-0"
        )}
      >
        <div className={clsx("col-span-5 xl:hidden mt-5", isMapMode && "hidden")}>
          <SearchField withPadding={false} withButton={false} />
        </div>
        <div className="col-span-2 relative xl:block hidden">
          <Filters />
        </div>
        <div className={clsx(!isMapMode ? "col-span-5" : "hidden xl:block xl:col-span-5")}>
          <Shops
            onFilterButtonClick={openFilterModal}
            onMapButtonClick={() => setQueryParams({ mode: isMapMode ? "list" : "map" }, false)}
          />
        </div>
        <div
          className={clsx(
            isMapMode ? "col-span-5 min-h-[calc(100vh-78px)]" : "hidden xl:block xl:col-span-5"
          )}
        >
          <ShopsInMap />
        </div>
      </div>
      <Drawer
        withCloseIcon={false}
        open={isFilterModalOpen}
        onClose={closeFilterModal}
        position="bottom"
      >
        <Filters onClose={closeFilterModal} />
      </Drawer>
    </div>
  );
};

export default SearchPage;
