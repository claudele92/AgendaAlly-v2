"use client";

import { useSettings } from "@/hook/use-settings";
import { useInfiniteQuery } from "@tanstack/react-query";
import { categoryService } from "@/services/category";
import React from "react";
import { useTranslation } from "react-i18next";
import { extractDataFromPagination } from "@/utils/extract-data";
import dynamic from "next/dynamic";
import { InfiniteLoader } from "@/components/infinite-loader";
import { Button } from "@/components/button";
import { DiscoveryDomainNav } from "@/components/search-field-core/discovery-domain-nav";
import { ServiceCategoriesGrid } from "@/app/(store)/(booking)/(with-footer)/(home)/components/service-categories-grid";

const Empty = dynamic(() =>
  import("@/components/empty").then((component) => ({ default: component.Empty }))
);
const ErrorFallback = dynamic(() => import("@/components/error-fallback"));

const ServicesPage = () => {
  const { t } = useTranslation();
  const { language } = useSettings();
  const {
    data: services,
    hasNextPage,
    isFetchingNextPage,
    fetchNextPage,
    isLoading,
    isError,
    refetch,
  } = useInfiniteQuery(
    ["services", language?.locale],
    ({ pageParam }) =>
      categoryService.getAll({
        lang: language?.locale,
        perPage: 100,
        type: "service",
        page: pageParam,
        column: "input",
        sort: "asc",
      }),
    {
      getNextPageParam: (lastPage) => lastPage.links.next && lastPage.meta.current_page + 1,
    }
  );

  const serviceList = extractDataFromPagination(services?.pages);

  const renderResults = () => {
    if (isError && (!serviceList || serviceList.length === 0)) {
      return (
        <div className="flex min-h-64 flex-col items-center justify-center gap-4" role="alert">
          <ErrorFallback />
          <Button size="small" color="black" onClick={() => refetch()}>
            Retry
          </Button>
        </div>
      );
    }
    if (isLoading)
      return (
        <div className="px-4 grid grid-cols-6 lg:gap-7 sm:gap-4 gap-2.5 animate-pulse overflow-x-hidden flex-nowrap">
          {Array.from(Array(12).keys()).map((item) => (
            <div
              className="bg-gray-300 rounded-button min-w-[200px] xl:min-w-full h-40 xl:aspect-[200/152]"
              key={item}
            />
          ))}
        </div>
      );
    if (serviceList && serviceList.length > 0) {
      return (
        <>
          {isError && (
            <div
              className="mb-4 flex items-center justify-between gap-3 rounded-xl border border-[#ead1c8] bg-[#fff8f5] px-4 py-3 text-sm"
              role="alert"
            >
              <span>Could not refresh service categories. Categories already loaded are still shown.</span>
              <Button size="small" color="black" onClick={() => refetch()}>
                Retry
              </Button>
            </div>
          )}
          <InfiniteLoader
            hasMore={hasNextPage}
            loadMore={fetchNextPage}
            loading={isFetchingNextPage}
          >
            <div className="aa-s2">
              <ServiceCategoriesGrid categories={serviceList} loadError={isError} />
            </div>
          </InfiniteLoader>
        </>
      );
    }
    if (serviceList && serviceList.length === 0) {
      return <Empty animated={false} />;
    }
    return (
      <div className="flex items-center justify-center">
        <ErrorFallback />
      </div>
    );
  };
  return (
    <section className="aa-s2-native xl:container px-4 ">
      <div className="pt-6">
        <DiscoveryDomainNav active="services" />
      </div>
      <h2 className="text-head font-semibold my-7">{t("services")}</h2>
      {renderResults()}
    </section>
  );
};

export default ServicesPage;
