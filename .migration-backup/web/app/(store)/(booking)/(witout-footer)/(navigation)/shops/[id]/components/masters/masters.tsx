"use client";

import { Translate } from "@/components/translate";
import { useInfiniteQuery } from "@tanstack/react-query";
import { masterService } from "@/services/master";
import { extractDataFromPagination } from "@/utils/extract-data";
import { MasterCard } from "@/components/master-card";
import dynamic from "next/dynamic";
import { useRouter } from "next/navigation";
import { buildUrlQueryParams } from "@/utils/build-url-query-params";
import { useSettings } from "@/hook/use-settings";
import { useShopLocationParams } from "@/hook/use-shop-location-params";
import { useTranslation } from "react-i18next";

const Empty = dynamic(() =>
  import("@/components/empty").then((component) => ({ default: component.Empty }))
);

interface MastersProps {
  shopId?: number;
  shopSlug?: string;
}

export const Masters = ({ shopId, shopSlug }: MastersProps) => {
  const router = useRouter();
  const { t } = useTranslation();
  const { language, currency } = useSettings();
  const locationParams = useShopLocationParams();
  const {
    data: masters,
    isLoading,
    isError,
    isFetching,
    refetch,
  } = useInfiniteQuery(
    ["masters", shopId, language?.locale, locationParams],
    () =>
      masterService.list({
        shop_id: shopId,
        lang: language?.locale,
        currency_id: currency?.id,
        ...locationParams,
      }),
    {
      getNextPageParam: (lastPage) => lastPage.links.next && lastPage.meta.current_page + 1,
    }
  );
  const masterList = extractDataFromPagination(masters?.pages)?.filter(
    (master) => master.profile_visibility === "public"
  );
  return (
    <div className="border border-gray-link rounded-button col-span-2 py-6 px-5">
      <h2 className="text-xl font-semibold">
        <Translate value="our.specialists" />
      </h2>
      {isLoading ? (
        <div className="grid md:grid-cols-4 sm:grid-cols-3 grid-cols-2 md:gap-4 gap-2.5 mt-6">
          {Array.from(Array(12).keys()).map((item) => (
            <div className="rounded-button bg-gray-300 aspect-[1/1.5]" key={item} />
          ))}
        </div>
      ) : isError ? (
        <div className="mt-6 rounded-button border border-red-300 bg-red-50 p-3 text-sm text-red-800" role="alert">
          <p>{t("error.loading.specialists", { defaultValue: "Specialists could not be loaded." })}</p>
          <button
            type="button"
            className="mt-2 font-semibold underline"
            disabled={isFetching}
            onClick={() => void refetch()}
          >
            {isFetching
              ? t("loading", { defaultValue: "Loading…" })
              : t("retry", { defaultValue: "Retry" })}
          </button>
        </div>
      ) : masterList && masterList.length > 0 ? (
        <div className="grid md:grid-cols-4 sm:grid-cols-3 grid-cols-2 md:gap-4 gap-2.5 mt-6">
          {masterList.map((master) => {
            const goToMaster = () =>
              router.push(
                buildUrlQueryParams(`/shops/${shopSlug}/booking`, {
                  master_id: master.id,
                  ...locationParams,
                })
              );
            return (
              <div
                key={master.id}
                role="button"
                tabIndex={0}
                onClick={goToMaster}
                onKeyDown={(e) => {
                  if (e.key === "Enter" || e.key === " ") {
                    e.preventDefault();
                    goToMaster();
                  }
                }}
                className="cursor-pointer"
              >
                <MasterCard data={master} />
              </div>
            );
          })}
        </div>
      ) : (
        <Empty animated={false} smallText />
      )}
    </div>
  );
};
