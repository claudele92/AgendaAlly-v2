"use client";

import { useSettings } from "@/hook/use-settings";
import { useInfiniteQuery } from "@tanstack/react-query";
import { shopService } from "@/services/shop";
import { extractDataFromPagination } from "@/utils/extract-data";
import { ListHeader } from "@/components/list-header";
import { ShopCard } from "@/components/shop-card";
import { Swiper, SwiperSlide } from "swiper/react";
import { A11y, Keyboard } from "swiper/modules";
import { buildUrlQueryParams } from "@/utils/build-url-query-params";
import useAddressStore from "@/global-store/address";

export const Salons = () => {
  const { language } = useSettings();
  const country = useAddressStore((state) => state.country);
  const city = useAddressStore((state) => state.city);
  const { data: shops } = useInfiniteQuery(
    ["shops", language?.locale, "id", "desc", country?.region_id, country?.id, city?.id],
    () =>
      shopService.getAll({
        lang: language?.locale,
        perPage: 7,
        column: "id",
        sort: "desc",
        region_id: country?.region_id,
        country_id: country?.id,
        city_id: city?.id,
        location_type: "2",
      }),
    {}
  );

  const shopList = extractDataFromPagination(shops?.pages);

  return (
    <section className="mt-14 aa-s2-home-discovery-row">
      <ListHeader
        title="New businesses"
        link={buildUrlQueryParams("/shops", { column: "id", sort: "desc" })}
        className="aa-s2-home-discovery-header"
      />
      <div className="w-full min-w-0">
        <Swiper
          className="aa-s2-home-discovery-swiper !px-0"
          modules={[A11y, Keyboard]}
          keyboard={{ enabled: true, onlyInViewport: true }}
          slidesPerView="auto"
          spaceBetween={16}
        >
          {shopList?.map((shop) => (
            <SwiperSlide key={shop.id} className="!h-auto">
              <ShopCard data={shop} presentation="discovery" />
            </SwiperSlide>
          ))}
        </Swiper>
      </div>
    </section>
  );
};
