"use client";

import { useInfiniteQuery } from "@tanstack/react-query";
import { masterService } from "@/services/master";
import { extractDataFromPagination } from "@/utils/extract-data";
import { MasterCard } from "@/components/master-card";
import dynamic from "next/dynamic";
import { ListHeader } from "@/components/list-header";
import { Swiper, SwiperSlide } from "swiper/react";
import { A11y, Keyboard } from "swiper/modules";
import { buildUrlQueryParams } from "@/utils/build-url-query-params";
import { useSettings } from "@/hook/use-settings";
import useAddressStore from "@/global-store/address";

const Empty = dynamic(() =>
  import("@/components/empty").then((component) => ({ default: component.Empty }))
);

export const Masters = () => {
  const { language, currency } = useSettings();
  const country = useAddressStore((state) => state.country);
  const city = useAddressStore((state) => state.city);
  const { data: masters, isLoading } = useInfiniteQuery(
    ["masters", language?.locale, currency?.id, country?.id, city?.id],
    () =>
      masterService.list({
        column: "r_avg",
        sort: "desc",
        lang: language?.locale,
        perPage: 12,
        currency_id: currency?.id,
        region_id: country?.region_id,
        country_id: country?.id,
        city_id: city?.id,
      }),
    {
      getNextPageParam: (lastPage) => lastPage.links.next && lastPage.meta.current_page + 1,
    }
  );
  // A master without a live shop invite has nowhere to send the customer -
  // `master.invite?.shop?.slug` would be undefined, producing a
  // `/shops/undefined/booking` link that 404s on click (and on Next's own
  // viewport prefetch, before anyone even clicks it).
  const masterList = extractDataFromPagination(masters?.pages)?.filter(
    (master) =>
      master.profile_visibility === "public" && Boolean(master.invite?.shop?.slug)
  );
  return (
    <section className="mt-14 aa-s2-home-discovery-row aa-s2-home-specialists">
      <ListHeader title="Top specialists" link="/masters" className="aa-s2-home-discovery-header" />
      {isLoading || (masterList?.length || 0) > 0 ? (
        <div className="w-full min-w-0">
          <Swiper
            className="aa-s2-home-discovery-swiper !px-0"
            modules={[A11y, Keyboard]}
            keyboard={{ enabled: true, onlyInViewport: true }}
            slidesPerView="auto"
            spaceBetween={16}
          >
            {isLoading
              ? Array.from(Array(8).keys()).map((item) => (
                  <SwiperSlide key={item} className="!h-auto">
                    <div className="aa-s2-home-specialist-skeleton" />
                  </SwiperSlide>
                ))
              : masterList?.map((master) => (
                  <SwiperSlide key={master.id} className="!h-auto">
                    <MasterCard
                      data={master}
                      profileHref={buildUrlQueryParams(
                        `/shops/${master.invite?.shop?.slug}/booking`,
                        { master_id: master.id }
                      )}
                    />
                  </SwiperSlide>
                ))}
          </Swiper>
        </div>
      ) : (
        <Empty animated={false} />
      )}
    </section>
  );
};
