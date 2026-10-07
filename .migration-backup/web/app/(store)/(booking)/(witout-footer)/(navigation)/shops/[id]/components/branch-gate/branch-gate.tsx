"use client";

import { useEffect } from "react";
import { useRouter, usePathname } from "next/navigation";
import { useTranslation } from "react-i18next";
import { useBooking } from "@/context/booking";
import { Types } from "@/context/booking/booking.reducer";
import { Shop, ShopLocationEntry } from "@/types/shop";
import { DefaultResponse } from "@/types/global";
import { buildLocationQuery } from "@/utils/build-shop-location-query";

const SERVICE_LOCATION_TYPE = 2;

// Mirrors location.tsx's own label choice - a branch's alias is what should
// distinguish it from its siblings without crowding the page with full
// street addresses.
const locationLabel = (location: ShopLocationEntry) =>
  location.alias || location.address || location.city?.translation?.title;

interface BranchGateProps {
  data?: DefaultResponse<Shop>;
  children: React.ReactNode;
}

/**
 * Backend root cause: BookingService::resolveBookingLocation() requires a
 * shop_location_id once a shop has more than one SERVICE location, and
 * auto-resolves it only when there's no genuine ambiguity (one location, or
 * a master assigned to exactly one) - a shop-wide master at a multi-branch
 * shop with no branch context is a real ambiguity the server can't resolve
 * on its own (LOCATION_AMBIGUOUS). This is the frontend half: syncs the
 * branch context that already survives the booking flow (matched_location,
 * resolved from the region/country/city/area/location_type params a search
 * result or the branch switcher carries forward - see buildShopLocationQuery
 * / location.tsx) into booking state, and - only when that context is
 * missing and the shop genuinely has more than one branch to choose from -
 * blocks booking behind a required "choose a branch" prompt instead of
 * letting the customer reach checkout and fail there.
 */
export const BranchGate = ({ data, children }: BranchGateProps) => {
  const { t } = useTranslation();
  const { state, dispatch } = useBooking();
  const router = useRouter();
  const pathname = usePathname();

  const matchedLocationId = data?.data.matched_location?.id;
  const serviceLocations = (data?.data.locations ?? []).filter(
    (location) => location.type === SERVICE_LOCATION_TYPE
  );
  const isMultiBranch = serviceLocations.length > 1;

  useEffect(() => {
    if (matchedLocationId && state.shopLocationId !== matchedLocationId) {
      dispatch({ type: Types.SetShopLocation, payload: matchedLocationId });
    }
    // Only re-sync when the server-resolved match itself changes (a branch
    // switcher click, a fresh navigation) - not on every state update.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [matchedLocationId]);

  const resolvedLocationId = state.shopLocationId ?? matchedLocationId;
  const needsSelection = isMultiBranch && !resolvedLocationId;

  if (!needsSelection) {
    return <>{children}</>;
  }

  const handleSelect = (location: ShopLocationEntry) => {
    dispatch({ type: Types.SetShopLocation, payload: location.id });
    router.push(`${pathname}${buildLocationQuery(location)}`);
  };

  return (
    <div className="border border-gray-link rounded-button col-span-2 py-6 px-5">
      <h2 className="text-xl font-semibold mb-1">
        {t("choose.a.branch", { defaultValue: "Choose a branch" })}
      </h2>
      <p className="text-sm text-gray-field mb-4">
        {t("choose.a.branch.description", {
          defaultValue: "This shop has multiple locations - please select one to continue booking.",
        })}
      </p>
      <div className="flex flex-wrap gap-2">
        {serviceLocations.map((location) => (
          <button
            key={location.id}
            type="button"
            onClick={() => handleSelect(location)}
            className="text-sm px-3 py-2 rounded-button border border-gray-link hover:border-dark transition-colors"
          >
            {locationLabel(location)}
          </button>
        ))}
      </div>
    </div>
  );
};
