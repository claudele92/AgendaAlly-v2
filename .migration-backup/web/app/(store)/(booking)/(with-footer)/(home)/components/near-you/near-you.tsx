"use client";

import { useInfiniteQuery } from "@tanstack/react-query";
import { useSettings } from "@/hook/use-settings";
import { shopService } from "@/services/shop";
import { useTranslation } from "react-i18next";
import { Paginate } from "@/types/global";
import { Shop } from "@/types/shop";
import { extractDataFromPagination } from "@/utils/extract-data";
import { ShopCard } from "@/components/shop-card";
import { ListHeader } from "@/components/list-header";
import { Swiper, SwiperSlide } from "swiper/react";
import { A11y, Keyboard } from "swiper/modules";
import { buildUrlQueryParams } from "@/utils/build-url-query-params";
import useAddressStore from "@/global-store/address";

interface NearYouProps {
  data?: Paginate<Shop>;
  lat?: number;
  lon?: number;
}

export const NearYou = ({ data, lat, lon }: NearYouProps) => {
  const { language } = useSettings();
  const { t } = useTranslation();
  const country = useAddressStore((state) => state.country);
  const city = useAddressStore((state) => state.city);
  const { data: shops } = useInfiniteQuery(
    ["shops", language?.locale, country?.region_id, country?.id, city?.id],
    () =>
      shopService.getAll({
        lang: language?.locale,
        perPage: 8,
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
    <section className="mt-14 aa-s2-home-discovery-row">
      <ListHeader
        title={t("all.shops")}
        link={buildUrlQueryParams("/shops", {
          latitude: lat,
          longitude: lon,
        })}
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
