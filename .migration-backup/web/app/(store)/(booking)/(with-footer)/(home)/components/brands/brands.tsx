"use client";

import { Paginate } from "@/types/global";
import { Brand } from "@/types/brand";
import { useInfiniteQuery } from "@tanstack/react-query";
import { brandService } from "@/services/brand";
import { ListHeader } from "@/components/list-header";
import { useTranslation } from "react-i18next";
import { Swiper, SwiperSlide } from "swiper/react";
import { A11y, Keyboard } from "swiper/modules";
import { extractDataFromPagination } from "@/utils/extract-data";
import { BrandCard } from "@/components/brand-card";
import Link from "next/link";
import { buildUrlQueryParams } from "@/utils/build-url-query-params";

interface BrandsProps {
  data?: Paginate<Brand>;
}

export const Brands = ({ data }: BrandsProps) => {
  const { t } = useTranslation();
  const { data: brands } = useInfiniteQuery(
    ["brands"],
    ({ pageParam }) => brandService.getAll({ page: pageParam }),
    {
      initialData: data ? { pages: [data], pageParams: [1] } : undefined,
    }
  );
  const brandList = extractDataFromPagination(brands?.pages);

  return (
    <section className="my-14 aa-s2-home-discovery-row">
      <ListHeader title={t("brands")} link="/brands" className="aa-s2-home-discovery-header" />
      <div className="w-full min-w-0">
        <Swiper
          className="aa-s2-home-discovery-swiper aa-s2-home-brand-swiper !px-0"
          modules={[A11y, Keyboard]}
          keyboard={{ enabled: true, onlyInViewport: true }}
          slidesPerView="auto"
          spaceBetween={16}
        >
          {brandList?.map((brand) => (
            <SwiperSlide key={brand.id} className="!h-auto">
              <Link href={buildUrlQueryParams("/products", { brands: brand.id })}>
                <BrandCard data={brand} presentation="discovery" />
              </Link>
            </SwiperSlide>
          ))}
        </Swiper>
      </div>
    </section>
  );
};
