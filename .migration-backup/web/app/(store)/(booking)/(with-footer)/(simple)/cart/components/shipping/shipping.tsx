"use client";

import { useTranslation } from "react-i18next";
import clsx from "clsx";
import React from "react";
import { Types } from "@/context/checkout/checkout.reducer";
import { useCheckout } from "@/context/checkout";
import { CheckoutDeliveryForm } from "../delivery-form";
import { CheckoutPickupForm } from "../pickup-form";
import { Shop } from "@/types/shop";
import { PickupScheduling } from "../pickup-scheduling/pickup-scheduling";

type FulfillmentMethod = "delivery" | "pickup" | "point";

interface CheckoutShippingProps {
  methods: FulfillmentMethod[];
  pickupShops: Shop[];
  hasMixedShopFulfillment: boolean;
  allDigital: boolean;
}

const CheckoutShipping = ({
  methods,
  pickupShops,
  hasMixedShopFulfillment,
  allDigital,
}: CheckoutShippingProps) => {
  const { t } = useTranslation();
  const { state, dispatch } = useCheckout();
  const choices: { value: FulfillmentMethod; label: string; hint: string }[] = [
    {
      value: "delivery",
      label: t("delivery", { defaultValue: "Delivery" }),
      hint: t("deliver.order.to.address", { defaultValue: "Deliver this order to my address." }),
    },
    {
      value: "pickup",
      label: t("pickup.from.business", { defaultValue: "Pickup from this business" }),
      hint: t("collect.from.business", { defaultValue: "Collect each item from its business." }),
    },
    {
      value: "point",
      label: t("pickup.at.delivery.point", { defaultValue: "Collect from a pickup point" }),
      hint: t("choose.pickup.point", { defaultValue: "Choose an available collection point." }),
    },
  ];
  const options = choices.filter((option) => methods.includes(option.value));
  const ShippingUi =
    state.deliveryType === "delivery"
      ? CheckoutDeliveryForm
      : state.deliveryType === "point"
        ? CheckoutPickupForm
        : undefined;

  return (
    <section className="flex flex-col gap-4" aria-labelledby="fulfillment-heading">
      <div>
        <span className="text-xs font-semibold uppercase tracking-[0.12em] text-gray-field">
          {t("fulfillment", { defaultValue: "Fulfillment" })}
        </span>
        <h2 id="fulfillment-heading" className="mt-1 text-lg font-semibold">
          {t("how.to.receive.order", { defaultValue: "How would you like to receive your order?" })}
        </h2>
      </div>
      {allDigital ? (
        <p className="text-sm text-gray-field">
          {t("digital.items.no.delivery", { defaultValue: "Digital items do not need delivery or pickup." })}
        </p>
      ) : !options.length ? (
        <p className="rounded-button border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800" role="alert">
          {t("fulfillment.options.unavailable", {
            defaultValue:
              "No shared delivery or pickup method is available for this cart. Please contact the businesses before checking out.",
          })}
        </p>
      ) : (
        <>
          {hasMixedShopFulfillment && (
            <p className="rounded-button border border-gray-link bg-gray-card px-3 py-2 text-sm text-gray-field" role="note">
              {t("cart.shared.fulfillment.constraint", {
                defaultValue:
                  "This cart contains products from businesses with different fulfillment options. One method must apply to every item, so only options supported by all businesses are shown.",
              })}
            </p>
          )}
          <div className="grid grid-cols-1 gap-2 sm:grid-cols-3" role="group" aria-label={t("fulfillment", { defaultValue: "Fulfillment" })}>
            {options.map((option) => (
              <button
                type="button"
                key={option.value}
                aria-pressed={state.deliveryType === option.value}
                onClick={() =>
                  dispatch({ type: Types.UpdateDeliveryType, payload: { type: option.value } })
                }
                className={clsx(
                  "min-h-[76px] rounded-button border px-3 py-3 text-left transition-colors",
                  state.deliveryType === option.value
                    ? "border-dark bg-white text-dark"
                    : "border-gray-link bg-gray-layout text-gray-field"
                )}
              >
                <span className="block text-sm font-semibold">{option.label}</span>
                <span className="mt-1 block text-xs leading-5">{option.hint}</span>
              </button>
            ))}
          </div>
          {state.deliveryType === "pickup" && (
            <div className="grid gap-3">
              <PickupScheduling shops={pickupShops} />
              <p className="text-xs text-gray-field">
                {t("pickup.no.delivery.fee", { defaultValue: "No delivery fee is applied to pickup." })}
              </p>
            </div>
          )}
          {ShippingUi && <ShippingUi />}
        </>
      )}
    </section>
  );
};

export default CheckoutShipping;
