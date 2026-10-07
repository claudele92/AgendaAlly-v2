import { Shop } from "@/types/shop";
import React from "react";
import Link from "next/link";
import MapPinIcon from "@/assets/icons/map-pin";
import { ImageWithFallBack } from "@/components/image";
import { buildShopLocationQuery } from "@/utils/build-shop-location-query";

interface DealCardProps {
  data: Shop;
}

export const DealCard = ({ data }: DealCardProps) => {
  const displayAddress =
    data.matched_location?.address ||
    data?.translation?.address ||
    data.matched_location?.city?.translation?.title;

  return (
  <Link href={`/shops/${data.slug}${buildShopLocationQuery(data)}`}>
    <div className="aa-s2-discovery-card aa-s2-deal-card relative rounded-button overflow-hidden aspect-[313/300] w-full">
      <ImageWithFallBack
        src={data.background_img}
        alt="shopBackground"
        fill
        className="object-cover"
      />
      <div className="absolute z-[1] bottom-2 left-2 right-2 border border-white flex justify-between rounded-button bg-white bg-opacity-50 p-2.5">
        <div>
          <strong className="text-lg font-semibold line-clamp-1">{data.translation?.title}</strong>
          <div className="flex items-center gap-1">
            <MapPinIcon />
            <span className="text-sm text-opacity-50 line-clamp-1">
              {displayAddress}
            </span>
          </div>
        </div>
        <div className="border-2 relative overflow-hidden border-white rounded-full p-2 flex items-center justify-center w-[55px]  aspect-square ">
          <ImageWithFallBack
            src={data?.logo_img}
            alt={data.translation?.title || "shop"}
            fill
            className="object-cover aspect-square"
          />
        </div>
      </div>
    </div>
  </Link>
  );
};
