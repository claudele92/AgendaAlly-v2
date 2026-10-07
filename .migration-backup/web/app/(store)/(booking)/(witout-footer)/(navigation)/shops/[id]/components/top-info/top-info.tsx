"use client";

import { DefaultResponse } from "@/types/global";
import { Shop } from "@/types/shop";
import { ImageWithFallBack } from "@/components/image";
import { useQuery } from "@tanstack/react-query";
import { useSettings } from "@/hook/use-settings";
import { shopService } from "@/services/shop";
import { useTranslation } from "react-i18next";
import MapPinIcon from "@/assets/icons/map-pin";
import { IconButton } from "@/components/icon-button";
import ShareIcon from "@/assets/icons/share";
import HeartOutlinedIcon from "@/assets/icons/heart-outlined";
import { useShopHours } from "@/hook/use-shop-hours";
import { useLike } from "@/hook/use-like";
import HeartFillOutlinedIcon from "@/assets/icons/heart-fill-outlined";
import Link from "next/link";
import dynamic from "next/dynamic";
import { LoadingCard } from "@/components/loading";
import { Modal } from "@/components/modal";
import { useModal } from "@/hook/use-modal";
import VerifiedIcon from "@/assets/icons/verified";
import { ShopSocialsPanel } from "@/components/shop-social-panel";
import Chat3LineIcon from "remixicon-react/Chat3LineIcon";
import { useShopLocationParams } from "@/hook/use-shop-location-params";

const ShopShare = dynamic(
  () => import("../shop-share").then((component) => ({ default: component.ShopShare })),
  {
    loading: () => <LoadingCard />,
  }
);

interface TopInfoProps {
  data?: DefaultResponse<Shop>;
  initialHoursTime: number;
}

export const TopInfo = ({ data, initialHoursTime }: TopInfoProps) => {
  const { language, currency, settings } = useSettings();
  const { t } = useTranslation();
  const locationParams = useShopLocationParams();
  const { data: shopDetail } = useQuery(
    ["shop", data?.data.id, language?.locale, locationParams],
    () =>
      shopService.getById(data?.data.id, {
        lang: language?.locale,
        currency_id: currency?.id,
        ...locationParams,
      }),
    {
      initialData: data,
    }
  );
  const shop = shopDetail?.data || data?.data;
  const title = shop?.translation?.title;
  const description = shop?.translation?.description;
  const matchedLocation = shop?.matched_location;
  const branchPlace = [
    matchedLocation?.city?.translation?.title,
    matchedLocation?.region?.translation?.title,
    matchedLocation?.country?.translation?.title,
  ]
    .filter(Boolean)
    .join(", ");
  const branchDetails = [matchedLocation?.alias, matchedLocation?.address, branchPlace]
    .filter(Boolean)
    .join(" · ");
  const schedule = shop?.shop_working_days;
  const { today, closed: isShopClosed, reason: hoursReason } = useShopHours(shop, initialHoursTime);
  const hasSchedule = !!schedule?.length;
  const { isLiked, handleLikeDisLike } = useLike("shop", shop?.id);
  const [isShareModalOpen, openShareModal, closeShareModal] = useModal();
  const [isSocialModalOpen, openSocialModal, closeSocialModal] = useModal();
  const galleryHref = `/shops/${shop?.slug}/gallery${
    Object.keys(locationParams).length
      ? `?${new URLSearchParams(locationParams).toString()}`
      : ""
  }`;

  return (
    <div className="aa-s2-profile-top">
      <section className="aa-s2-profile-cover">
        <div className="aa-s2-profile-cover-media">
          {shop?.background_img ? (
            <ImageWithFallBack
              src={shop.background_img}
              alt={title ? `${title} cover photo` : "Business cover photo"}
              className="aa-s2-profile-cover-image"
              fill
              priority
              sizes="(max-width: 700px) 100vw, 60vw"
            />
          ) : (
            <div
              className="aa-s2-profile-cover-empty"
              role="img"
              aria-label="Business cover photo not available"
            />
          )}
          <div className="aa-s2-profile-cover-shade" />
          <div className="aa-s2-profile-cover-actions">
            {!!shop?.socials?.length && (
              <IconButton
                aria-label={t("contact")}
                title={t("contact")}
                onClick={openSocialModal}
                className="aa-s2-profile-icon-button"
              >
                <Chat3LineIcon size={19} />
              </IconButton>
            )}
            <IconButton
              aria-label={t("share")}
              title={t("share")}
              onClick={openShareModal}
              className="aa-s2-profile-icon-button"
            >
              <ShareIcon />
            </IconButton>
            <IconButton
              aria-label={isLiked ? "Remove from favorites" : "Add to favorites"}
              title={isLiked ? "Remove from favorites" : "Add to favorites"}
              onClick={handleLikeDisLike}
              className="aa-s2-profile-icon-button"
            >
              {isLiked ? <HeartFillOutlinedIcon size={22} /> : <HeartOutlinedIcon />}
            </IconButton>
          </div>
          <div className="aa-s2-profile-cover-link">
            <Link href={galleryHref} className="aa-s2-btn aa-s2-btn-secondary">
              {t("see.photos")}
            </Link>
          </div>
        </div>
        <div className="aa-s2-profile-summary">
          <div className="aa-s2-profile-identity">
            <div className="aa-s2-profile-logo-frame">
              {shop?.logo_img ? (
                <ImageWithFallBack
                  src={shop.logo_img}
                  alt={title ? `${title} logo` : "Business logo"}
                  fill
                  className="aa-s2-logo-photo"
                  sizes="72px"
                />
              ) : (
                <span className="aa-s2-profile-logo-unavailable">Logo not provided</span>
              )}
            </div>
            <div className="aa-s2-profile-title-group">
              {shop?.verify && (
                <span className="aa-s2-chip aa-s2-profile-verified">
                  <VerifiedIcon />
                  {t("verified", { defaultValue: "Verified" })}
                </span>
              )}
              <h1>{title || "Business name unavailable"}</h1>
            </div>
          </div>
          {description ? (
            <p className="aa-s2-profile-description">{description}</p>
          ) : (
            <p className="aa-s2-profile-description aa-s2-muted">
              Business description is not available.
            </p>
          )}
          {matchedLocation ? (
            <p className="aa-s2-profile-location">
              <MapPinIcon />
              <span>{branchDetails || "Branch details are not available."}</span>
            </p>
          ) : (
            <p className="aa-s2-profile-location aa-s2-muted">
              <MapPinIcon />
              <span>No specific branch is selected for this view. See available locations below.</span>
            </p>
          )}
          <div className="aa-s2-profile-status-row">
            {settings?.shop_reviews_enabled === "1" &&
              (shop?.r_count ?? 0) > 0 &&
              shop?.r_avg != null && (
                <span className="aa-s2-chip">
                  {shop.r_avg} · {shop.r_count} {t("reviews")}
                </span>
              )}
            {hasSchedule ? (
              <span className="aa-s2-chip">
                {hoursReason || (isShopClosed ? "Closed based on shop hours" : "Open based on shop hours")}
                {today && !today.disabled && ` · ${today.from}–${today.to}`}
              </span>
            ) : (
              <span className="aa-s2-chip">Hours not provided</span>
            )}
          </div>
          <div className="aa-s2-profile-mobile-gallery">
            <Link href={galleryHref} className="aa-s2-btn aa-s2-btn-secondary">
              {t("see.all.photos")}
            </Link>
          </div>
        </div>
      </section>
      <Modal isOpen={isShareModalOpen} onClose={closeShareModal} withCloseButton>
        <ShopShare data={shop} />
      </Modal>
      <Modal isOpen={isSocialModalOpen} onClose={closeSocialModal} withCloseButton>
        <ShopSocialsPanel list={shop?.socials} />
      </Modal>
    </div>
  );
};
