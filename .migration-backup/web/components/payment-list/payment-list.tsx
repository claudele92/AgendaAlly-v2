import { useQuery } from "@tanstack/react-query";
import { useEffect, useRef } from "react";
import { orderService } from "@/services/order";
import { RadioGroup } from "@headlessui/react";
import { useTranslation } from "react-i18next";
import EmptyCheckIcon from "@/assets/icons/empty-check";
import { RadioFillIcon } from "@/assets/icons/radio-fill";
import { Payment } from "@/types/global";
import { LoadingCard } from "@/components/loading";
import { useSettings } from "@/hook/use-settings";
import { userService } from "@/services/user";
import useUserStore from "@/global-store/user";
import { Price } from "@/components/price";
import { Wallet } from "./wallet";
import "./stage2-payment.css";

interface PaymentListProps {
  value?: Payment;
  totalPrice?: number;
  fromWalletPrice?: number;
  onChange: (value?: Payment) => void;
  onChangeWalletPrice: (value?: number) => void;
  filter?: (value: Payment) => boolean;
  // Scopes the list to what this shop/cart actually accepts, per
  // v1/rest/payments (PaymentController::index) — shopId+locationType
  // for a single-shop checkout (booking, or a single-shop cart),
  // cartId alone for a cart that may span multiple shops. Omit both
  // for a platform-level purchase (gift card, membership) that has no
  // owning shop.
  shopId?: number;
  locationType?: number;
  cartId?: number;
  bookingId?: number;
}

export const PaymentList = ({
  value,
  totalPrice,
  fromWalletPrice,
  onChange,
  onChangeWalletPrice,
  filter,
  shopId,
  locationType,
  cartId,
  bookingId,
}: PaymentListProps) => {
  const { currency } = useSettings();
  const user = useUserStore((state) => state.user);
  const signIn = useUserStore((state) => state.signIn);
  useQuery(["profile"], () => userService.profile(), {
    onSuccess: (res) => {
      signIn(res?.data);
    },
  });
  const { data, isLoading, isFetching, isError, error: queryError } = useQuery(
    ["payments", shopId, locationType, cartId, bookingId, currency?.id],
    () =>
      orderService.paymentList({
        active: 1,
        currency_id: currency?.id,
        shop_id: shopId,
        location_type: locationType,
        cart_id: cartId,
        booking_id: bookingId,
      })
  );
  const { t } = useTranslation();

  const walletPayment = data?.data?.find((item) => item.tag === "wallet");
  const context = data?.meta?.payment_context;
  const difference = (totalPrice || 0) - (fromWalletPrice || 0);
  const selectionContext = JSON.stringify([shopId, locationType, cartId, bookingId, currency?.id]);
  const previousContext = useRef(selectionContext);

  useEffect(() => {
    if (previousContext.current !== selectionContext) {
      previousContext.current = selectionContext;
      // An earlier shop/currency's choice is not valid while its replacement loads.
      if (value) onChange(undefined);
      if (fromWalletPrice) onChangeWalletPrice(undefined);
    }
  }, [selectionContext, value, fromWalletPrice, onChange, onChangeWalletPrice]);

  useEffect(() => {
    if (isFetching || isLoading) return;
    if (isError || !data?.data) {
      if (value) onChange(undefined);
      if (fromWalletPrice) onChangeWalletPrice(undefined);
      return;
    }
    const walletAvailable =
      context?.valid !== false && data.data.some((payment) => payment.tag === "wallet");
    if (!walletAvailable && fromWalletPrice) onChangeWalletPrice(undefined);
    if (context?.valid === false && value) {
      onChange(undefined);
      return;
    }
    if (value && !data.data.some((payment) => payment.id === value.id && (!filter || filter(payment)))) {
      onChange(undefined);
    }
  }, [
    context?.valid,
    data?.data,
    filter,
    fromWalletPrice,
    isError,
    isFetching,
    isLoading,
    onChange,
    onChangeWalletPrice,
    value,
  ]);

  if (isLoading || isFetching) {
    return (
      <div className="py-10">
        <LoadingCard />
      </div>
    );
  }

  if (isError) {
    return (
      <section className="py-6" role="alert">
        <p className="aa-stage2-payment-note">
          {queryError instanceof Error
            ? queryError.message
            : t("payment.methods.failed.to.load", {
                defaultValue: "Payment methods could not be loaded. Please try again.",
              })}
        </p>
      </section>
    );
  }

  if (context?.valid === false) {
    return (
      <section className="py-6" role="status">
        <p className="aa-stage2-payment-note">
          {t("payment.context.unavailable", {
            defaultValue: "Payment methods are unavailable for this transaction context.",
          })}
        </p>
      </section>
    );
  }

  if (data?.data && data.data.filter((payment) => (filter ? filter(payment) : true)).length === 0) {
    return (
      <section className="py-6" role="status">
        <p className="aa-stage2-payment-note">
          {t("payment.methods.none.eligible", {
            defaultValue: "No eligible payment methods are available for this checkout.",
          })}
        </p>
        {context?.transaction_currency && (
          <p className="aa-stage2-payment-note">
            {t("payment.charge.currency", {
              currency: context.transaction_currency,
              defaultValue: `The charge currency for this transaction is ${context.transaction_currency}.`,
            })}
          </p>
        )}
      </section>
    );
  }

  return (
    <section className="aa-stage2-payment-options" aria-label={t("payment.type")}>
      <p className="aa-stage2-payment-note">
        {t("payment.available.methods.note", { defaultValue: "Only payment methods available for this checkout are shown. Unavailable providers cannot be selected here." })}
      </p>
      {context?.transaction_currency && (
        <p className="aa-stage2-payment-note" role="status">
          {context.display_currency_is_charge === false
            ? t("payment.charge.currency.display.only", {
                currency: context.transaction_currency,
                defaultValue: `This transaction will be charged in ${context.transaction_currency}. Your selected display currency does not convert the charge.`,
              })
            : t("payment.charge.currency", {
                currency: context.transaction_currency,
                defaultValue: `The charge currency for this transaction is ${context.transaction_currency}.`,
              })}
        </p>
      )}
      {(user?.wallet?.price || 0) > 0 && walletPayment && (
        <div>
        <p className="aa-stage2-payment-note">
          {t("wallet.internal.balance.note", { defaultValue: "Wallet is your internal account balance, not an external payment gateway." })}
        </p>
        <Wallet
          totalPrice={totalPrice}
          fromWalletPrice={fromWalletPrice}
          onChangeWalletPrice={onChangeWalletPrice}
          onFullPricePaid={() => onChange(walletPayment)}
        />
        </div>
      )}
      <div className="w-full h-4" />
      {/* Public owners use undefined for no selection; Headless UI requires a defined value. */}
      <RadioGroup value={value ?? null} by="id" onChange={(payment: Payment | null) => onChange(payment ?? undefined)}>
        <RadioGroup.Label className="sr-only">{t("payment.type")}</RadioGroup.Label>
        <div className="space-y-2">
          {data?.data
            ?.filter((item) => item?.tag !== "wallet")
            .map(
              (payment) =>
                (filter ? filter(payment) : true) && (
                  <RadioGroup.Option
                    key={payment.id}
                    value={payment}
                    className={({ active, checked }) =>
                      `${active ? "ring-2 ring-white/60 ring-offset-2 ring-offset-primary " : ""}
                  ${checked ? "border-dark" : "border-gray-link"}
                    relative flex cursor-pointer rounded-lg px-5 py-3 focus:outline-none border`
                    }
                  >
                    {({ checked }) => (
                      <div className="flex w-full items-center justify-between">
                        <div className="flex items-center gap-3">
                          {checked ? (
                            <RadioFillIcon />
                          ) : (
                            <span className="text-gray-link">
                              <EmptyCheckIcon size={14} />
                            </span>
                          )}
                          <div className="text-sm">
                            <RadioGroup.Label as="p" className="font-medium">
                              {t(payment.tag)}{" "}
                              {checked && !!fromWalletPrice && (
                                <span>
                                  (<Price number={difference} />)
                                </span>
                              )}
                            </RadioGroup.Label>
                            {payment.tag === "cash" && <p className="aa-stage2-payment-description">
                              {t("payment.cash.offline.note", { defaultValue: "Offline cash payment — no online provider transaction." })}
                            </p>}
                          </div>
                        </div>
                      </div>
                    )}
                  </RadioGroup.Option>
                )
            )}
        </div>
      </RadioGroup>
    </section>
  );
};
