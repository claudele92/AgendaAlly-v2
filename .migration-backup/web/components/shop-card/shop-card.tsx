import { Shop } from "@/types/shop";
import React from "react";
import Link from "next/link";
import { ImageWithFallBack } from "@/components/image";
import MapPinIcon from "@/assets/icons/map-pin";
import { useTranslation } from "react-i18next";
import { createRatingText } from "@/utils/create-rating-text";
import { useLike } from "@/hook/use-like";
import HeartOutlinedIcon from "@/assets/icons/heart-outlined";
import HeartFillOutlinedIcon from "@/assets/icons/heart-fill-outlined";
import { IconButton } from "@/components/icon-button";
import VerifiedIcon from "@/assets/icons/verified";
import clsx from "clsx";
import { useSettings } from "@/hook/use-settings";
import { buildShopLocationQuery } from "@/utils/build-shop-location-query";

interface ShopCardProps {
  data: Shop;
  presentation?: "discovery";
}

export const ShopCard = ({ data, presentation }: ShopCardProps) => {
  const { t } = useTranslation();
  const { isLiked, handleLikeDisLike } = useLike("shop", data.id);
  const { settings } = useSettings();
  const displayAddress =
    data.matched_location?.address ||
    data?.translation?.address ||
    data.matched_location?.city?.translation?.title;
  return (
    <div className={`aa-s2-discovery-card aa-s2-shop-card relative rounded-button overflow-hidden group shadow-storeCard justify-start${presentation === "discovery" ? " aa-s2-home-shop-card" : ""}`}>
      <div className="absolute top-3 left-3 z-[1] text-dark">
        <IconButton
          aria-label={isLiked ? "Remove business from favorites" : "Add business to favorites"}
          aria-pressed={isLiked}
          onClick={handleLikeDisLike}
        >
          {isLiked ? <HeartFillOutlinedIcon /> : <HeartOutlinedIcon size={26} />}
        </IconButton>
      </div>
      {settings?.shop_reviews_enabled === "1" &&
        Number(data.r_count) > 0 &&
        data.r_avg != null && (
        <div className="absolute top-3 right-3 rounded-button border border-white w-7 h-7 flex items-center justify-center z-[1] bg-white bg-opacity-60">
          <span className="text-sm font-semibold">{data.r_avg || 0}</span>
        </div>
      )}
      <Link href={`/shops/${data.slug}${buildShopLocationQuery(data)}`} scroll>
        <div className="relative aspect-[2/1]">
          <div className="absolute inset-0 overflow-hidden">
            <ImageWithFallBack
              src={data.background_img}
              alt={data.translation?.title || ""}
              fill
              className="object-cover transition-all group-hover:scale-105"
            />
          </div>
          <div className="absolute -bottom-5 rounded-full bg-white w-10 h-10 left-6 flex items-center justify-center z-[1]">
            <ImageWithFallBack
              src={data.logo_img}
              alt={data.translation?.title ? `${data.translation.title} logo` : "Business logo"}
              width={36}
              height={36}
              className="rounded-full object-cover w-9 h-9"
            />
          </div>
        </div>
        <div className="flex flex-col gap-3 z-[2] xl:pb-5 xl:px-6 lg:pb-3 lg:px-3 pb-2 px-5">
          <div className="flex flex-col gap-1.5">
            <div className="flex items-center gap-2 mt-7">
              <strong
                className={clsx(
                  "md:text-lg text-base font-semibold",
                  presentation === "discovery" ? "line-clamp-2" : "line-clamp-1"
                )}
              >
                {data.translation?.title}
              </strong>
              {data?.verify && (
                <span>
                  <VerifiedIcon />
                </span>
              )}
            </div>
            <span className="md:text-sm text-xs text-gray-field line-clamp-2 h-10">
              {data.translation?.description}
            </span>
          </div>
          {(displayAddress || presentation !== "discovery") && (
            <div className="aa-s2-home-shop-location flex items-center gap-1">
              <MapPinIcon />
              <span className="text-xs text-gray-field line-clamp-1">
                {displayAddress}
              </span>
            </div>
          )}
          {settings?.shop_reviews_enabled === "1" &&
            Number(data.r_count) > 0 &&
            data.r_avg != null && (
            <>
              <div className="h-px w-full bg-gray-link" />
              <div className="flex items-center gap-2">
                <span className="md:text-sm text-xs font-medium">
                  {t(createRatingText(data.r_avg))}
                </span>
                <div className="bg-footerBg rounded-full w-1 h-1" />
                <span className="md:text-sm text-xs font-normal">
                  {data.r_count || 0} {t("reviews")}
                </span>
              </div>
            </>
          )}
        </div>
      </Link>
    </div>
  );
};
