import { Master } from "@/types/master";
import { useTranslation } from "react-i18next";
import { ImageWithFallBack } from "@/components/image";
import React from "react";
import { Price } from "@/components/price";
import { IconButton } from "@/components/icon-button";
import HeartFillOutlinedIcon from "@/assets/icons/heart-fill-outlined";
import HeartOutlinedIcon from "@/assets/icons/heart-outlined";
import { useLike } from "@/hook/use-like";
import clsx from "clsx";
import Link from "next/link";

interface MasterCardProps {
  data: Master;
  selected?: boolean;
  profileHref?: string;
}

export const MasterCard = ({ data, selected, profileHref }: MasterCardProps) => {
  const { t } = useTranslation();
  const { isLiked, handleLikeDisLike } = useLike("master", data.id);
  const startingPrice = data.starting_price ?? data.service_min_price;
  return (
    <div
      className={clsx(
        "aa-s2-discovery-card aa-s2-specialist-card relative rounded-button overflow-hidden group justify-start h-full border border-gray-link",
        selected && "ring-2 ring-black"
      )}
    >
      <div className="absolute top-3 left-3 z-[1] text-dark">
        <IconButton
          aria-label={isLiked ? "Remove specialist from favorites" : "Add specialist to favorites"}
          aria-pressed={isLiked}
          onClick={(e) => {
            e.stopPropagation();
            e.preventDefault();
            handleLikeDisLike();
          }}
        >
          {isLiked ? <HeartFillOutlinedIcon /> : <HeartOutlinedIcon size={26} />}
        </IconButton>
      </div>
      {profileHref ? (
        <Link href={profileHref} className="relative block aspect-[198/182]" aria-label={`View ${[data?.firstname, data?.lastname].filter(Boolean).join(" ")} profile`}>
          <ImageWithFallBack
            src={data?.img}
            alt={[data?.firstname, data?.lastname].filter(Boolean).join(" ") || "Specialist"}
            fill
            className="object-cover transition-all group-hover:scale-105"
          />
        </Link>
      ) : (
        <div className="relative aspect-[198/182]">
          <ImageWithFallBack
            src={data?.img}
            alt={[data?.firstname, data?.lastname].filter(Boolean).join(" ") || "Specialist"}
            fill
            className="object-cover transition-all group-hover:scale-105"
          />
        </div>
      )}
      <div className="aa-s2-master-card-content xl:pb-5 lg:pb-3 px-3 pb-2 text-start">
        <div className="flex items-center justify-between mt-3">
          {profileHref ? (
            <Link href={profileHref} className="min-w-0 overflow-hidden text-ellipsis whitespace-nowrap text-lg font-semibold">
              {[data?.firstname, data?.lastname].filter(Boolean).join(" ")}
            </Link>
          ) : (
            <strong className="min-w-0 overflow-hidden text-ellipsis whitespace-nowrap text-lg font-semibold">
              {[data?.firstname, data?.lastname].filter(Boolean).join(" ")}
            </strong>
          )}
          {Number(data?.r_avg) > 0 && Number(data?.r_avg) <= 5 && <div className="rounded-button border border-gray-link w-7 h-7 flex items-center justify-center z-[1] bg-white bg-opacity-60">
            <span className="text-sm font-semibold">{data.r_avg}</span>
          </div>}
        </div>
        <span className="aa-s2-master-domains">
          {data.professional_domains?.length
            ? `${data.professional_domains.slice(0, 2).join(" · ")}${
                data.professional_domains.length > 2
                  ? ` · ${t("more.specialties", {
                      count: data.professional_domains.length - 2,
                      defaultValue: `+${data.professional_domains.length - 2} more`,
                    })}`
                  : ""
              }`
            : data?.translation?.title || t("specialist")}
        </span>
        {typeof startingPrice === "number" && Number.isFinite(startingPrice) && (
          <div className="flex flex-wrap flex-col gap-1 pt-2.5 mt-2.5 border-t border-gray-link">
            <span className="text-sm">{t("starting.from")}</span>
            <strong className="whitespace-nowrap text-lg font-bold">
              <Price number={startingPrice} groupThousands />
            </strong>
          </div>
        )}
        <div className="aa-s2-master-mode-slot flex flex-wrap flex-col gap-1 pt-2.5 mt-2.5 border-t border-gray-link">
          {(data.service_modes?.length || data?.service_master?.type) && (
            <>
              <span className="text-sm">{t("service.place")}</span>
              <strong className="aa-s2-master-modes">
                {(data.service_modes?.length
                  ? data.service_modes
                  : [data?.service_master?.type || ""]
                )
                  .filter(Boolean)
                  .slice(0, 3)
                  .map((mode) => t(mode))
                  .join(" · ")}
              </strong>
            </>
          )}
        </div>
        {profileHref && (
          <Link href={profileHref} className="aa-s2-master-profile-link">
            View profile
            <span aria-hidden="true">→</span>
          </Link>
        )}
      </div>
    </div>
  );
};
