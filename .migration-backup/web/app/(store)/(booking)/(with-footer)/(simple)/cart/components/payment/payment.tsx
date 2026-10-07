import { useTranslation } from "react-i18next";
import { RadioGroup } from "@headlessui/react";
import CheckIcon from "@/assets/icons/check";
import EmptyCheckIcon from "@/assets/icons/empty-check";
import React from "react";
import { Payment } from "@/types/global";
import { useServerCart } from "@/hook/use-server-cart";
import { ConfirmModal } from "@/components/confirm-modal";
import { useModal } from "@/hook/use-modal";
import { Types } from "@/context/checkout/checkout.reducer";
import { useCheckout } from "@/context/checkout/checkout.context";
import type { PaymentContextMetadata } from "@/services/order";

const comparePayments = (a?: Payment, b?: Payment) => a?.id === b?.id;

interface CheckoutPaymentProps {
  onSelect: () => void;
  payments?: {
    data?: Payment[];
  };
  isLoading: boolean;
  isError?: boolean;
  paymentContext?: PaymentContextMetadata;
}

const CheckoutPayment = ({
  onSelect,
  payments,
  isLoading,
  isError,
  paymentContext,
}: CheckoutPaymentProps) => {
  const { t } = useTranslation();
  const { data } = useServerCart();
  const [isConfirModalOpen, openConfirmModal, closeConfirmModal] = useModal();
  let hasDigitalProduct = false;
  data?.data.user_carts.forEach((userCart) => {
    userCart.cartDetails.forEach((detail) => {
      detail.cartDetailProducts.forEach((cartProduct) => {
        if (cartProduct.stock.product.digital) {
          hasDigitalProduct = true;
        }
      });
    });
  });
  const { state, dispatch } = useCheckout();
  const handleChangePayment = (payment: Payment) => {
    if (hasDigitalProduct && payment.tag === "cash") {
      openConfirmModal();
      return;
    }
    dispatch({ type: Types.UpdatePaymentMethod, payload: { paymentMethod: payment } });
    onSelect();
  };
  if (isLoading) {
    return (
      <div className="mt-7 px-4">
        <h6 className="text-base font-semibold">{t("payment.method")}</h6>
        <div className="flex flex-col gap-3 mt-7">
          <div className="bg-gray-300 w-full rounded-full h-4" />
        </div>
        <div className="flex flex-col gap-3 mt-7">
          <div className="bg-gray-300 w-full rounded-full h-4" />
        </div>
      </div>
    );
  }
  return (
    <div className="mb-12 px-4">
      <h6 className="text-base font-semibold">{t("payment.method")}</h6>
      {paymentContext?.transaction_currency && (
        <p className="py-2 text-sm text-gray-600" role="status">
          {paymentContext.display_currency_is_charge === false
            ? t("payment.charge.currency.display.only", {
                currency: paymentContext.transaction_currency,
                defaultValue: `This transaction will be charged in ${paymentContext.transaction_currency}. Your selected display currency does not convert the charge.`,
              })
            : t("payment.charge.currency", {
                currency: paymentContext.transaction_currency,
                defaultValue: `The charge currency for this transaction is ${paymentContext.transaction_currency}.`,
              })}
        </p>
      )}
      {isError ? (
        <p className="py-4 text-sm text-red-600" role="alert">
          {t("payment.methods.failed.to.load", {
            defaultValue: "Payment methods could not be loaded. Please try again.",
          })}
        </p>
      ) : paymentContext?.valid === false || !payments?.data?.length ? (
        <p className="py-4 text-sm text-gray-600" role="status">
          {t("payment.methods.none.eligible", {
            defaultValue: "No eligible payment methods are available for this checkout.",
          })}
        </p>
      ) : null}
      {paymentContext?.valid === false || isError ? null : (
        <RadioGroup by={comparePayments} value={state.paymentMethod} onChange={handleChangePayment}>
          {payments?.data?.map((payment) => (
            <RadioGroup.Option
              key={payment.id}
              value={payment}
              className="cursor-pointer border-b border-gray-layout dark:border-gray-inputBorder last:border-none"
            >
              {({ checked }) => (
                <div className="flex items-center gap-4 py-4 ">
                  {checked && !!state.paymentMethod ? (
                    <span className="text-primary dark:text-white">
                      <CheckIcon />
                    </span>
                  ) : (
                    <EmptyCheckIcon />
                  )}
                  <div className="flex flex-col">
                    <span className="text-sm font-medium">{payment.tag}</span>
                  </div>
                </div>
              )}
            </RadioGroup.Option>
          ))}
        </RadioGroup>
      )}
      <ConfirmModal
        text="you.cannot.pay.with.cash.because.there.is.a.digital.product.in.your.cart.please.change.the.payment.type.or.remove.the.digital.product.from.the.cart"
        onConfirm={closeConfirmModal}
        onCancel={closeConfirmModal}
        isOpen={isConfirModalOpen}
        confirmText="ok"
      />
    </div>
  );
};

export default CheckoutPayment;
