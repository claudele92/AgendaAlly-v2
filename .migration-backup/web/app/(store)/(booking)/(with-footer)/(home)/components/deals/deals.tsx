"use client";

import { ListHeader } from "@/components/list-header";
import { useTranslation } from "react-i18next";
import { useInfiniteQuery } from "@tanstack/react-query";
import { shopService } from "@/services/shop";
import { extractDataFromPagination } from "@/utils/extract-data";
import { useSettings } from "@/hook/use-settings";
import { Paginate } from "@/types/global";
import { Shop } from "@/types/shop";
import { Swiper, SwiperSlide } from "swiper/react";
import { A11y, Keyboard } from "swiper/modules";
import { ShopCard } from "@/components/shop-card";
import { buildUrlQueryParams } from "@/utils/build-url-query-params";
import useAddressStore from "@/global-store/address";

interface DealsProps {
  data?: Paginate<Shop>;
}

export const Deals = ({ data }: DealsProps) => {
  const { t } = useTranslation();
  const { language } = useSettings();
  const country = useAddressStore((state) => state.country);
  const city = useAddressStore((state) => state.city);
  const { data: shops } = useInfiniteQuery(
    ["shops", language?.locale, "b_count", "desc", country?.region_id, country?.id, city?.id],
    () =>
      shopService.getAll({
        lang: language?.locale,
        perPage: 8,
        column: "b_count",
        sort: "desc",
        region_id: country?.region_id || undefined,
        country_id: country?.id || undefined,
        city_id: city?.id || undefined,
        location_type: "2",
      }),
    {
      initialData: data ? { pages: [data], pageParams: [1] } : undefined,
    }
  );

  const shopList = extractDataFromPagination(shops?.pages);
  return (
    <section className="aa-s2-home-discovery-row mt-14">
      <ListHeader
        title={t("deals")}
        link={buildUrlQueryParams("/shops", { column: "b_count", sort: "desc" })}
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
