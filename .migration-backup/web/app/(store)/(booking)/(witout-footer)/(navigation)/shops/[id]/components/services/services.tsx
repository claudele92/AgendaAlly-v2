"use client";

import { Translate } from "@/components/translate";
import { useInfiniteQuery, useQuery, useQueryClient } from "@tanstack/react-query";
import { useSettings } from "@/hook/use-settings";
import { serviceService } from "@/services/service";
import { extractDataFromPagination } from "@/utils/extract-data";
import dynamic from "next/dynamic";
import { useQueryParams } from "@/hook/use-query-params";
import { Modal } from "@/components/modal";
import { Service } from "@/types/service";
import { InfiniteLoader } from "@/components/infinite-loader";
import { useTranslation } from "react-i18next";
import Link from "next/link";
import { memo, useRef, useState } from "react";
import { categoryService } from "@/services/category";
import { Category } from "@/types/category";
import { Option } from "@/components/search-field-core/option";
import { useBooking } from "@/context/booking";
import { masterService } from "@/services/master";
import { Types } from "@/context/booking/booking.reducer";
import useAddressStore from "@/global-store/address";
import { ServiceCard } from "./service-card";

const Empty = dynamic(() =>
  import("@/components/empty").then((component) => ({ default: component.Empty }))
);
const ServiceDetail = dynamic(() =>
  import("./service-detail").then((component) => ({ default: component.ServiceDetail }))
);

interface ServiceProps {
  shopId?: number;
  isInBookingPage?: boolean;
  shopSlug?: string;
  masterId?: string;
}

export const Services = memo(({ shopId, isInBookingPage, shopSlug, masterId }: ServiceProps) => {
  const { t } = useTranslation();
  const { language, currency } = useSettings();
  const { state, dispatch } = useBooking();
  const country = useAddressStore((countryState) => countryState.country);
  const city = useAddressStore((cityState) => cityState.city);
  const {
    data: categories,
    isLoading: isCategoryLoading,
    isError: isCategoryError,
    isFetching: isCategoryFetching,
    refetch: refetchCategories,
  } = useInfiniteQuery(
    ["categories", language?.locale, shopId],
    ({ pageParam }) =>
      categoryService.getAll({
        lang: language?.locale,
        perPage: 11,
        type: "service",
        page: pageParam,
        shop_id: shopId,
        has_service: 1,
        column: "input",
        sort: "asc",
        currency_id: currency?.id,
      }),
    {
      getNextPageParam: (lastPage) => lastPage.links.next && lastPage.meta.current_page + 1,
    }
  );
  const categoryList = extractDataFromPagination(categories?.pages);

  const [childCategoryIdx, setChildCategoryIdx] = useState<number>(-1);
  const [tempCategory, setTempCategory] = useState<Category | undefined>();
  const { setQueryParams, urlSearchParams, deleteParams } = useQueryParams({ scroll: false });
  const displayCategoryList =
    childCategoryIdx >= 0 ? categoryList?.[childCategoryIdx].children : categoryList;

  useQuery(
    ["staff", masterId, language?.locale, currency?.id],
    () => masterService.getById(masterId, { lang: language?.locale, currency_id: currency?.id }),
    {
      enabled: !!masterId,
      onSuccess: async (res) => {
        dispatch({ type: Types.SetMaster, payload: res.data });
      },
    }
  );
  const {
    data: services,
    hasNextPage,
    fetchNextPage,
    isFetchingNextPage,
    isLoading: isServiceLoading,
    isError: isServiceError,
    isFetching: isServiceFetching,
    isPreviousData: isPreviousServiceData,
    refetch: refetchServices,
  } = useInfiniteQuery(
    ["services", language?.locale, shopId, tempCategory?.id, isInBookingPage],
    ({ pageParam }) =>
      serviceService.list({
        lang: language?.locale,
        page: pageParam,
        shop_id: shopId,
        category_id: tempCategory?.id,
        perPage: isInBookingPage ? undefined : 4,
        currency_id: currency?.id,
        master_id: masterId,
        has_master: 1,
        country_id: country?.id,
        city_id: city?.id,
      }),
    {
      getNextPageParam: (lastPage) => lastPage.links.next && lastPage.meta.current_page + 1,
      keepPreviousData: true,
    }
  );
  const queryClient = useQueryClient();
  const detailTriggerRef = useRef<HTMLElement | null>(null);
  const serviceList = extractDataFromPagination(services?.pages);
  // ? state.master.service_masters?.map((serviceMaster) => ({
  //     ...serviceMaster.service,
  //     price: serviceMaster.total_price,
  //     interval: serviceMaster.interval,
  //     service_extras: serviceMaster.extras,
  //     master: { ...state.master, service_master: serviceMaster },
  //   }))
  const handleClickServiceCard = (service: Service, trigger: HTMLElement) => {
    detailTriggerRef.current = trigger;
    setQueryParams({ serviceId: service.id }, false);
    queryClient.setQueryData(["service", service.id.toString(), language?.locale], () => ({
      data: service,
    }));
  };

  const handleCloseServiceDetail = () => {
    deleteParams("serviceId");
  };
  return (
    <div className="border border-gray-link rounded-button col-span-2 overflow-hidden">
      <div className="py-6 px-5 ">
        <h2 className="text-xl font-semibold">
          <Translate value="services" />
        </h2>
      </div>
      {!state.master && (
        <div className="pb-6 px-5 ">
          <div className="flex items-center flex-wrap gap-2.5">
            {isCategoryError ? (
              <div className="w-full rounded-button border border-red-300 bg-red-50 p-3 text-sm text-red-800" role="alert">
                <p>{t("error.loading.service.categories", { defaultValue: "Service categories could not be loaded." })}</p>
                <button
                  type="button"
                  className="mt-2 font-semibold underline"
                  disabled={isCategoryFetching}
                  onClick={() => void refetchCategories()}
                >
                  {isCategoryFetching
                    ? t("loading", { defaultValue: "Loading…" })
                    : t("retry", { defaultValue: "Retry" })}
                </button>
              </div>
            ) : !isCategoryLoading ? (
              <>
                <Option
                  isSelected={!tempCategory}
                  text={t("all")}
                  onClick={() => {
                    setTempCategory(undefined);
                    setChildCategoryIdx(-1);
                  }}
                />
                {displayCategoryList?.map((category, idx) => (
                  <Option
                    isSelected={tempCategory?.id.toString() === category.id.toString()}
                    key={category.id}
                    text={category?.translation?.title}
                    img={category.img}
                    onClick={() => {
                      if (category.children?.length) {
                        setChildCategoryIdx(idx);
                      }
                      setTempCategory(category);
                    }}
                  />
                ))}
              </>
            ) : (
              <>
                <div className="bg-gray-300 animate-pulse rounded-button h-10 w-20" />
                <div className="bg-gray-300 animate-pulse rounded-button h-10 w-24" />
                <div className="bg-gray-300 animate-pulse rounded-button h-10 w-28" />
                <div className="bg-gray-300 animate-pulse rounded-button h-10 w-32" />
                <div className="bg-gray-300 animate-pulse rounded-button h-10 w-36" />
                <div className="bg-gray-300 animate-pulse rounded-button h-10 w-24" />
                <div className="bg-gray-300 animate-pulse rounded-button h-10 w-32" />
                <div className="bg-gray-300 animate-pulse rounded-button h-10 w-28" />
                <div className="bg-gray-300 animate-pulse rounded-button h-10 w-36" />
                <div className="bg-gray-300 animate-pulse rounded-button h-10 w-24" />
              </>
            )}
          </div>
        </div>
      )}
      {isServiceError && (!serviceList?.length || isPreviousServiceData) ? (
        <div className="mx-5 mb-6 rounded-button border border-red-300 bg-red-50 p-3 text-sm text-red-800" role="alert">
          <p>{t("error.loading.services", { defaultValue: "Services could not be loaded." })}</p>
          <button
            type="button"
            className="mt-2 font-semibold underline"
            disabled={isServiceFetching}
            onClick={() => void refetchServices()}
          >
            {isServiceFetching
              ? t("loading", { defaultValue: "Loading…" })
              : t("retry", { defaultValue: "Retry" })}
          </button>
        </div>
      ) : isServiceLoading ? (
        <div className="py-6 px-5 animate-pulse col-span-2">
          <div className="h-6 rounded-button w-1/3 bg-gray-300" />
          <div className="mt-6">
            <div className="w-1/2 mb-2 rounded-full h-[18px] bg-gray-300" />
            <div className="w-full mb-1 rounded-full h-[14px] bg-gray-300" />
            <div className="w-1/4 rounded-full h-[14px] bg-gray-300" />
          </div>
          <div className="mt-6">
            <div className="w-1/2 mb-2 rounded-full h-[18px] bg-gray-300" />
            <div className="w-full mb-1 rounded-full h-[14px] bg-gray-300" />
            <div className="w-1/4 rounded-full h-[14px] bg-gray-300" />
          </div>
          <div className="mt-6">
            <div className="w-1/2 mb-2 rounded-full h-[18px] bg-gray-300" />
            <div className="w-full mb-1 rounded-full h-[14px] bg-gray-300" />
            <div className="w-1/4 rounded-full h-[14px] bg-gray-300" />
          </div>
        </div>
      ) : (
        <div className="flex flex-col">
          {serviceList && serviceList.length !== 0 ? (
            <>
              {isServiceError && (
                <div className="mx-5 mt-5 rounded-button border border-red-300 bg-red-50 p-3 text-sm text-red-800" role="alert">
                  <p>{t("error.loading.more.services", { defaultValue: "More services could not be loaded." })}</p>
                  <button
                    type="button"
                    className="mt-2 font-semibold underline"
                    disabled={isServiceFetching}
                    onClick={() => void refetchServices()}
                  >
                    {isServiceFetching
                      ? t("loading", { defaultValue: "Loading…" })
                      : t("retry", { defaultValue: "Retry" })}
                  </button>
                </div>
              )}
              <InfiniteLoader
                hasMore={hasNextPage && isInBookingPage}
                loadMore={fetchNextPage}
                loading={isFetchingNextPage}
              >
                {serviceList?.map((service) => (
                  <ServiceCard
                    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
                    onCardClick={(trigger) => !!service && handleClickServiceCard(service, trigger)}
                    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
                    data={service}
                    key={service?.id}
                    isBookingPage={isInBookingPage}
                  />
                ))}
                {!isInBookingPage && (
                  <Link
                    href={`/shops/${shopSlug}/booking`}
                    className="text-lg font-medium my-6 max-w-max ml-5 hover:underline"
                  >
                    {t("view.all")}
                  </Link>
                )}
              </InfiniteLoader>
            </>
          ) : (
            <Empty animated={false} smallText />
          )}
        </div>
      )}
      <Modal
        withCloseButton
        isOpen={urlSearchParams.has("serviceId")}
        onClose={() => handleCloseServiceDetail()}
        onAfterClose={() => {
          const trigger = detailTriggerRef.current;
          detailTriggerRef.current = null;
          if (trigger?.isConnected) trigger.focus();
        }}
      >
        <ServiceDetail isBookingPage={isInBookingPage} id={urlSearchParams.get("serviceId")} />
      </Modal>
    </div>
  );
});
