/* eslint-disable @next/next/no-img-element */

"use client";

import { Translate } from "@/components/translate";
import { DefaultResponse } from "@/types/global";
import { Shop, ShopLocationEntry } from "@/types/shop";
import MapPinIcon from "@/assets/icons/map-pin";
import { useSettings } from "@/hook/use-settings";
import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import { useTranslation } from "react-i18next";
import { useState } from "react";
import clsx from "clsx";
import { createMapUrl } from "@/utils/create-map-url";
import { buildLocationQuery } from "@/utils/build-shop-location-query";
import { getGoogleMapsApiKey } from "@/config/integrations";

const SERVICE_LOCATION_TYPE = 2;

interface ShopLocationProps {
  data?: DefaultResponse<Shop>;
}

// A branch's own alias is what should distinguish it from its siblings in
// the switcher without crowding the page with full street addresses -
// falling back to the shop's single flat address here (unlike the primary
// address above, which has no sibling to be confused with) would show the
// same text on every entry once one branch's own address is empty too.
const locationLabel = (location: ShopLocationEntry) =>
  [
    location.alias || location.address,
    location.city?.translation?.title,
    location.country?.translation?.title,
  ]
    .filter(Boolean)
    .filter((value, index, items) => items.indexOf(value) === index)
    .join(" · ");

export const ShopLocation = ({ data }: ShopLocationProps) => {
  const { settings } = useSettings();
  const { t } = useTranslation();
  const router = useRouter();
  const pathname = usePathname();
  // Google rejects a keyless or coordinate-less static map request outright
  // (see the "maps guard" fix in 75d0816), but a validly-formed request can
  // still fail at request time for reasons this component has no way to
  // predict up front - the key missing "Maps Static API" specifically,
  // billing not enabled, an HTTP referrer restriction that doesn't cover
  // this domain, a transient network error. Whatever the cause, the
  // customer must never see a broken-image icon + raw alt text, so this
  // hides the element the instant its own <img> fails to load, regardless
  // of why.
  const [mapImageFailed, setMapImageFailed] = useState(false);
  // A shop's public address and coordinates do not identify each branch.
  // Use only the branch resolved by ShopResource::matched_location for
  // branch-specific address/map information.
  const matchedLocation = data?.data.matched_location;
  const branchPlace = [
    matchedLocation?.city?.translation?.title,
    matchedLocation?.region?.translation?.title,
    matchedLocation?.country?.translation?.title,
  ]
    .filter(Boolean)
    .filter((value, index, items) => items.indexOf(value) === index)
    .join(", ");
  const latitude = matchedLocation?.latitude;
  const longitude = matchedLocation?.longitude;
  const hasBranchCoordinates = latitude != null && longitude != null;
  const googleMapsApiKey = getGoogleMapsApiKey(settings?.google_map_key, settings?.maps_enabled, settings?.maps_environment_permitted);

  const serviceLocations = data?.data.locations?.filter(
    (location) => location.type === SERVICE_LOCATION_TYPE
  );

  return (
    <div className="aa-s2-panel aa-s2-profile-location-panel">
      <h2 className="aa-s2-profile-card-heading">
        <Translate value="location" />
      </h2>
      {matchedLocation ? (
        <div className="aa-s2-profile-location-current">
          <span className="aa-s2-eyebrow">
            {t("selected.location", { defaultValue: "Matched location" })}
          </span>
          {matchedLocation.address && <p>{matchedLocation.address}</p>}
          {matchedLocation.alias && <p>{matchedLocation.alias}</p>}
          {branchPlace && <p className="aa-s2-muted">{branchPlace}</p>}
          {!matchedLocation.address && !matchedLocation.alias && !branchPlace && (
            <p className="aa-s2-muted">
              {t("branch.details.unavailable", { defaultValue: "Branch address details are not provided." })}
            </p>
          )}
          {hasBranchCoordinates && (
            <Link
              href={createMapUrl(latitude, longitude)}
              className="aa-s2-profile-map-link"
              aria-label={t("view.location.on.map", { defaultValue: "View matched location on map" })}
            >
              <MapPinIcon />
              {t("view.on.map", { defaultValue: "View on map" })}
            </Link>
          )}
        </div>
      ) : (
        <p className="aa-s2-profile-empty">
          {t("location.not.selected", {
            defaultValue: "No specific branch is selected for this view. Choose a service branch below to continue booking.",
          })}
        </p>
      )}
      {googleMapsApiKey && hasBranchCoordinates && !mapImageFailed && (
        <img
          src={`https://maps.googleapis.com/maps/api/staticmap?center=${latitude},${longitude}&zoom=10&size=600x270&markers=color:black|${latitude},${longitude}&key=${googleMapsApiKey}`}
          alt={t("matched.branch.map", { defaultValue: "Map of the matched branch location" })}
          className="aa-s2-profile-map"
          onError={() => setMapImageFailed(true)}
        />
      )}
      {data?.data.translation?.address && (
        <div className="aa-s2-profile-shop-address">
          <span className="aa-s2-eyebrow">
            {t("shop.address", { defaultValue: "Shop-level address" })}
          </span>
          <p>{data.data.translation.address}</p>
          <small>
            {t("shop.address.branch.disclaimer", {
              defaultValue: "This address is not confirmed as the street address of each branch.",
            })}
          </small>
        </div>
      )}
      {serviceLocations && serviceLocations.length > 0 && (
        <div className="aa-s2-profile-branch-list">
          <h3>
            {t("service.branches", { defaultValue: "Service branches" })}
          </h3>
          <div className="aa-s2-profile-branch-buttons">
            {serviceLocations.map((location) => {
              const isActive = !!matchedLocation && matchedLocation.id === location.id;
              return (
                <button
                  key={location.id}
                  type="button"
                  onClick={() => router.push(`${pathname}${buildLocationQuery(location)}`)}
                  className={clsx(
                    "aa-s2-profile-branch-button",
                    isActive
                      ? "aa-s2-profile-branch-button-active"
                      : ""
                  )}
                  aria-pressed={isActive}
                >
                  {locationLabel(location) || t("branch.details.unavailable", { defaultValue: "Branch details unavailable" })}
                </button>
              );
            })}
          </div>
        </div>
      )}
      {!serviceLocations?.length && (
        <p className="aa-s2-profile-empty">
          {t("service.branches.unavailable", { defaultValue: "Service branch details are not available." })}
        </p>
      )}
    </div>
  );
};
