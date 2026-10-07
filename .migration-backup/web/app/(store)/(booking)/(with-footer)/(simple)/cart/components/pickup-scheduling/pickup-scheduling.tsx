"use client";

import React, { useEffect, useMemo, useState } from "react";
import { useQuery } from "@tanstack/react-query";
import { useTranslation } from "react-i18next";
import { Shop } from "@/types/shop";
import { pickupService } from "@/services/pickup";
import { PickupLocation, PickupSelection, PickupWindow } from "@/types/pickup";
import { Types } from "@/context/checkout/checkout.reducer";
import { useCheckout } from "@/context/checkout/checkout.context";

interface PickupSchedulingProps {
  shops: Shop[];
}

const pickupWindowStart = (window: PickupWindow) => window.window_start || window.start || "";
const pickupWindowEnd = (window: PickupWindow) => window.window_end || window.end || "";
const isReadyBased = (mode?: string) => mode === "asap" || mode === "as_soon_as_ready";

const PickupShop = ({ shop }: { shop: Shop }) => {
  const { t } = useTranslation();
  const { state, dispatch } = useCheckout();
  const selection: PickupSelection | undefined = state.pickupSelections[shop.id];
  const [locationId, setLocationId] = useState<number | undefined>(
    selection?.shop_location_id
  );
  const [date, setDate] = useState(selection?.date || "");

  const locationsQuery = useQuery({
    queryKey: ["pickup-availability", shop.id, "locations"],
    queryFn: () => pickupService.availability(shop.id),
    enabled: state.deliveryType === "pickup",
    retry: 1,
  });
  const availabilityQuery = useQuery({
    queryKey: ["pickup-availability", shop.id, locationId, date],
    queryFn: () => pickupService.availability(shop.id, locationId, date || undefined),
    enabled: state.deliveryType === "pickup" && !!locationId,
    retry: 1,
  });

  const branchOptions = useMemo(
    () => locationsQuery.data?.data?.locations?.filter((location) => location.id) || [],
    [locationsQuery.data?.data?.locations]
  );
  const selectedBranch: PickupLocation | undefined =
    availabilityQuery.data?.data?.locations?.find((location) => location.id === locationId) ||
    branchOptions.find((location) => location.id === locationId);
  const mode =
    selectedBranch?.timing_mode ||
    availabilityQuery.data?.data?.timing_mode ||
    locationsQuery.data?.data?.timing_mode;
  const timezone =
    selectedBranch?.timezone ||
    availabilityQuery.data?.data?.timezone ||
    locationsQuery.data?.data?.timezone;
  const serverToday =
    availabilityQuery.data?.data?.today || locationsQuery.data?.data?.today || "";
  const windows = availabilityQuery.data?.data?.windows || [];
  const branchEligible =
    selectedBranch?.eligible !== false && availabilityQuery.data?.data?.eligible !== false;
  const legacyReadyBased = locationsQuery.isSuccess &&
    locationsQuery.data?.data?.eligible === true && branchOptions.length === 0 &&
    isReadyBased(locationsQuery.data?.data?.timing_mode);

  useEffect(() => {
    if (legacyReadyBased && !selection?.legacy_ready_based) {
      dispatch({ type: Types.UpdatePickupSelection,
        payload: { shopId: shop.id, selection: { legacy_ready_based: true } } });
    }
  }, [legacyReadyBased, selection?.legacy_ready_based, dispatch, shop.id]);

  useEffect(() => {
    if (selection && (locationsQuery.isError ||
        (selection.legacy_ready_based && locationsQuery.isSuccess && !legacyReadyBased) ||
        (!selection.legacy_ready_based && (availabilityQuery.isError ||
        (availabilityQuery.isSuccess && !availabilityQuery.isFetching && (!branchEligible ||
          (mode === "scheduled" && !windows.some((window) =>
            pickupWindowStart(window) === selection.window_start &&
            pickupWindowEnd(window) === selection.window_end)))))))) {
      dispatch({ type: Types.UpdatePickupSelection,
        payload: { shopId: shop.id, selection: undefined } });
    }
  }, [availabilityQuery.isSuccess, availabilityQuery.isError, availabilityQuery.isFetching,
    locationsQuery.isSuccess, locationsQuery.isError, legacyReadyBased, branchEligible,
    mode, windows, selection, dispatch, shop.id]);

  useEffect(() => {
    if (
      locationsQuery.isSuccess &&
      selection &&
      !selection.legacy_ready_based &&
      !branchOptions.some((branch) => branch.id === selection.shop_location_id)
    ) {
      dispatch({
        type: Types.UpdatePickupSelection,
        payload: { shopId: shop.id, selection: undefined },
      });
      setLocationId(undefined);
      setDate("");
    }
  }, [branchOptions, dispatch, locationsQuery.isSuccess, selection, shop.id]);

  useEffect(() => {
    if (locationId && mode === "scheduled" && !date && serverToday) {
      setDate(serverToday);
    }
  }, [date, locationId, mode, serverToday]);

  useEffect(() => {
    if (locationId && isReadyBased(mode) && branchEligible) {
      const current = state.pickupSelections[shop.id];
      if (current?.shop_location_id !== locationId || current.date || current.window_start) {
        dispatch({
          type: Types.UpdatePickupSelection,
          payload: { shopId: shop.id, selection: { shop_location_id: locationId } },
        });
      }
    }
  }, [branchEligible, dispatch, locationId, mode, shop.id, state.pickupSelections]);

  const handleLocationChange = (value: string) => {
    const nextId = value ? Number(value) : undefined;
    setLocationId(nextId);
    setDate("");
    dispatch({
      type: Types.UpdatePickupSelection,
      payload: { shopId: shop.id, selection: undefined },
    });
  };

  const handleDateChange = (value: string) => {
    setDate(value);
    dispatch({
      type: Types.UpdatePickupSelection,
      payload: { shopId: shop.id, selection: undefined },
    });
  };

  const handleWindowChange = (window: PickupWindow) => {
    if (!locationId || !date || !timezone) return;
    dispatch({
      type: Types.UpdatePickupSelection,
      payload: {
        shopId: shop.id,
        selection: {
          shop_location_id: locationId,
          date,
          window_start: pickupWindowStart(window),
          window_end: pickupWindowEnd(window),
        },
      },
    });
  };

  const selectedWindow = windows.find(
    (window) =>
      pickupWindowStart(window) === selection?.window_start &&
      pickupWindowEnd(window) === selection?.window_end
  );
  const eligibleWindows = windows.filter((window) => window.available !== false);

  return (
    <article className="rounded-button border border-gray-link bg-gray-layout p-4">
      <div className="flex items-start justify-between gap-3">
        <div>
          <h3 className="font-semibold">
            {shop.translation?.title ||
              t("business.name.unavailable", { defaultValue: "Business name unavailable" })}
          </h3>
          <p className="mt-1 text-xs text-gray-field">
            {t("pickup.branch.by.shop", {
              defaultValue: "Choose a Product branch for this business.",
            })}
          </p>
        </div>
        {selection && (
          <span className="shrink-0 rounded-full bg-white px-2.5 py-1 text-xs font-medium text-gray-field">
            {mode === "scheduled" && selection.window_start
              ? t("pickup.scheduled", { defaultValue: "Scheduled" })
              : t("pickup.asap", { defaultValue: "ASAP" })}
          </span>
        )}
      </div>

      {locationsQuery.isLoading ? (
        <div className="mt-4 h-11 animate-pulse rounded-lg bg-gray-link" aria-label={t("loading")} />
      ) : locationsQuery.isError ? (
        <div className="mt-4 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-800" role="alert">
          <p>{t("pickup.availability.failed", { defaultValue: "Pickup branches could not be loaded." })}</p>
          <button
            type="button"
            className="mt-2 font-semibold underline"
            onClick={() => void locationsQuery.refetch()}
          >
            {t("retry", { defaultValue: "Retry" })}
          </button>
        </div>
      ) : legacyReadyBased ? (
        <p className="mt-4 text-sm">
          {t("pickup.asap.instruction", {
            defaultValue: "Collect at the Shop as soon as your order is ready.",
          })}
          {shop.translation?.address && <span className="mt-1 block">{shop.translation.address}</span>}
        </p>
      ) : branchOptions.length === 0 ? (
        <p className="mt-4 rounded-lg border border-gray-link bg-white p-3 text-sm text-gray-field">
          {t("pickup.no.product.branches", {
            defaultValue: "This business has no eligible Product pickup branches yet.",
          })}
        </p>
      ) : (
        <>
          <label className="mt-4 block text-sm font-medium" htmlFor={`pickup-branch-${shop.id}`}>
            {t("pickup.branch", { defaultValue: "Pickup branch" })}
          </label>
          <select
            id={`pickup-branch-${shop.id}`}
            className="mt-1 min-h-11 w-full rounded-lg border border-gray-link bg-white px-3 text-sm"
            value={locationId ? String(locationId) : ""}
            onChange={(event) => handleLocationChange(event.target.value)}
          >
            <option value="">{t("choose.pickup.branch", { defaultValue: "Choose a branch" })}</option>
            {branchOptions.map((branch) => {
              const place = [branch.city?.translation?.title, branch.country?.translation?.title]
                .filter(Boolean)
                .join(", ");
              return (
                <option key={branch.id} value={branch.id}>
                  {[branch.alias, branch.address, place].filter(Boolean).join(" · ") ||
                    t("pickup.address.unavailable", { defaultValue: "Address unavailable" })}
                </option>
              );
            })}
          </select>
          {selectedBranch && (
            <div className="mt-2 rounded-lg bg-white/70 px-3 py-2 text-sm text-gray-field">
              <p className="font-medium text-dark">
                {selectedBranch.alias || selectedBranch.address || t("pickup.branch", { defaultValue: "Pickup branch" })}
              </p>
              {selectedBranch.address && selectedBranch.alias && (
                <p>{selectedBranch.address}</p>
              )}
              {timezone && (
                <p className="mt-1 text-xs">
                  {t("pickup.timezone", { defaultValue: "Branch time" })}: {timezone}
                </p>
              )}
            </div>
          )}

          {locationId && availabilityQuery.isLoading && (
            <div className="mt-3 h-16 animate-pulse rounded-lg bg-gray-link" aria-label={t("loading")} />
          )}
          {locationId && availabilityQuery.isError && (
            <div className="mt-3 rounded-lg border border-red-300 bg-red-50 p-3 text-sm text-red-800" role="alert">
              <p>{t("pickup.availability.failed", { defaultValue: "Pickup availability could not be loaded." })}</p>
              <button type="button" className="mt-2 font-semibold underline" onClick={() => void availabilityQuery.refetch()}>
                {t("retry", { defaultValue: "Retry" })}
              </button>
            </div>
          )}
          {locationId && !availabilityQuery.isLoading && !availabilityQuery.isError && isReadyBased(mode) && (
            <p className="mt-3 rounded-lg bg-white px-3 py-2 text-sm">
              {t("pickup.asap.instruction", {
                defaultValue: "This branch offers collection as soon as your order is ready.",
              })}
            </p>
          )}
          {locationId && !availabilityQuery.isLoading && !availabilityQuery.isError && mode === "scheduled" && (
            <div className="mt-3">
              <label className="block text-sm font-medium" htmlFor={`pickup-date-${shop.id}`}>
                {t("pickup.date", { defaultValue: "Pickup date" })}
              </label>
              <input
                id={`pickup-date-${shop.id}`}
                type="date"
                min={serverToday || undefined}
                value={date}
                onChange={(event) => handleDateChange(event.target.value)}
                className="mt-1 min-h-11 w-full rounded-lg border border-gray-link bg-white px-3 text-sm"
              />
              <p className="mt-1 text-xs text-gray-field">
                {t("pickup.date.uses.branch.timezone", {
                  timezone: timezone || "",
                  defaultValue: "Times are shown in the branch timezone.",
                })}
              </p>
              {date && availabilityQuery.isFetching ? (
                <div className="mt-3 h-12 animate-pulse rounded-lg bg-gray-link" aria-label={t("loading")} />
              ) : date && !eligibleWindows.length ? (
                <p className="mt-3 rounded-lg border border-gray-link bg-white p-3 text-sm text-gray-field">
                  {t("pickup.no.windows", { defaultValue: "No pickup windows are available for this date." })}
                </p>
              ) : eligibleWindows.length > 0 ? (
                <fieldset className="mt-3">
                  <legend className="mb-2 text-sm font-medium">
                    {t("available.pickup.windows", { defaultValue: "Available pickup windows" })}
                  </legend>
                  <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    {eligibleWindows.map((window, index) => {
                      const start = pickupWindowStart(window);
                      const end = pickupWindowEnd(window);
                      const active =
                        selectedWindow === window ||
                        (selection?.window_start === start && selection?.window_end === end);
                      return (
                        <button
                          type="button"
                          key={`${start}-${end}-${index}`}
                          aria-pressed={active}
                          onClick={() => handleWindowChange(window)}
                          className={`min-h-11 rounded-lg border px-3 text-left text-sm transition-colors ${
                            active
                              ? "border-dark bg-dark font-semibold text-white"
                              : "border-gray-link bg-white text-dark hover:border-dark"
                          }`}
                        >
                          {window.local_start || start} – {window.local_end || end}
                        </button>
                      );
                    })}
                  </div>
                </fieldset>
              ) : null}
            </div>
          )}
          {locationId && !branchEligible && (
            <p className="mt-3 text-sm text-red-700" role="alert">
              {t("pickup.branch.unavailable", { defaultValue: "This branch is not currently available for pickup." })}
            </p>
          )}
        </>
      )}
    </article>
  );
};

export const PickupScheduling = ({ shops }: PickupSchedulingProps) => (
  <div className="grid gap-3">
    {shops.map((shop) => (
      <PickupShop key={shop.id} shop={shop} />
    ))}
  </div>
);