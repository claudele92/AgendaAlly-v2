import { useMutation } from "@tanstack/react-query";
import { orderService } from "@/services/order";
import NetworkError from "@/utils/network-error";
import { error, success } from "@/components/alert";
import { OrderCreateBody } from "@/types/order";
import { ParcelPaymentBody } from "@/types/parcel";
import { GiftCartPayBody, MembershipPayBody, WalletTopupBody } from "@/types/user";
import { BookingBookingPay } from "@/types/booking";
import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";

export const useExternalPayment = () => {
  const { t } = useTranslation();
  const [payFastUrl, setPayFastUrl] = useState("");
  const [redirectUrl, setRedirectUrl] = useState<"/orders" | "/appointments" | "">("");
  // eslint-disable-next-line consistent-return
  useEffect(() => {
    if (payFastUrl) {
      const script = document.createElement("script");
      script.src = payFastUrl;
      script.async = true;
      script.onload = () => {
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore
        if (window.payfast_do_onsite_payment) {
          // eslint-disable-next-line @typescript-eslint/ban-ts-comment
          // @ts-ignore
          window.payfast_do_onsite_payment(
            {
              uuid: payFastUrl.split("uuid=")[1],
            },
            (result: boolean) => {
              if (result) {
                success(t("payment.success"));
                if (redirectUrl.length) {
                  window.location.replace(redirectUrl);
                }
              } else {
                error(t("payment.failed"));
              }
            }
          );
        }
      };
      document.body.appendChild(script);
      setPayFastUrl("");
      return () => {
        document.body.removeChild(script);
      };
    }
  }, [payFastUrl]);

  return useMutation({
    mutationFn: (body: {
      tag?: string;
      data:
        | OrderCreateBody
        | ParcelPaymentBody
        | WalletTopupBody
        | MembershipPayBody
        | GiftCartPayBody
        | BookingBookingPay;
    }) => orderService.externalPayment(body.tag, body.data),
    onSuccess: (res, payload) => {
      // eslint-disable-next-line @typescript-eslint/ban-ts-comment
      // @ts-ignore
      if (payload?.data?.cart_id) {
        setRedirectUrl("/orders");
        // eslint-disable-next-line @typescript-eslint/ban-ts-comment
        // @ts-ignore
      } else if (payload?.data?.booking_id) {
        setRedirectUrl("/appointments");
      }
      if (payload.tag === "pay-fast") {
        if (res.data.data?.sandbox) {
          setPayFastUrl(
            `https://sandbox.payfast.co.za/onsite/engine.js/?uuid=${res.data.data?.uuid}`
          );
        } else {
          setPayFastUrl(`https://www.payfast.co.za/onsite/engine.js/?uuid=${res.data.data?.uuid}`);
        }
      } else {
        window.location.replace(res.data.data.url);
      }
    },
    onError: (err: NetworkError) => {
      error(err.message);
    },
  });
};
