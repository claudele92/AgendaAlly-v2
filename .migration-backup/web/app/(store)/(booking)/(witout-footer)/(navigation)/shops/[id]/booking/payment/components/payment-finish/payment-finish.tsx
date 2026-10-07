"use client";

import { Shop } from "@/types/shop";
import { BookingTotal } from "@/app/(store)/(booking)/(witout-footer)/(navigation)/shops/[id]/components/booking-total";
import { DefaultResponse } from "@/types/global";
import { useMutation } from "@tanstack/react-query";
import { Booking, BookingAddress, BookingCreateBody } from "@/types/booking";
import { bookingService } from "@/services/booking";
import { useBooking } from "@/context/booking";
import { Types } from "@/context/booking/booking.reducer";
import { useSettings } from "@/hook/use-settings";
import dayjs from "dayjs";
import dynamic from "next/dynamic";
import { useCallback, useState } from "react";
import { Modal } from "@/components/modal";
import { LoadingCard } from "@/components/loading";
import utc from "dayjs/plugin/utc";
import { useRouter } from "next/navigation";
import NetworkError from "@/utils/network-error";
import { error } from "@/components/alert";
import { useExternalPayment } from "@/hook/use-external-payment";
import { useTranslation } from "react-i18next";

dayjs.extend(utc);

const BookingDetail = dynamic(
  () =>
    import("@/app/(store)/(booking)/components/booking-detail").then((component) => ({
      default: component.BookingDetail,
    })),
  {
    loading: () => <LoadingCard />,
  }
);

interface PaymentFinishProps {
  shop?: DefaultResponse<Shop>;
}

export const PaymentFinish = ({ shop }: PaymentFinishProps) => {
  const router = useRouter();
  const { t } = useTranslation();
  const { state, dispatch } = useBooking();
  const { currency } = useSettings();
  const [orderDetail, setOrderDetail] = useState<Booking[] | undefined>();
  const { mutate: createPaymentProcess } = useExternalPayment();
  const { mutate: createBooking, isLoading } = useMutation({
    mutationFn: (body: BookingCreateBody) => bookingService.create(body),
    onSuccess: (res) => {
      setOrderDetail(res.data);
      if (state?.payment?.tag !== "cash" && state?.payment?.tag !== "wallet") {
        createPaymentProcess({
          tag: state.payment?.tag,
          data: {
            booking_id: res?.data?.[0]?.parent_id || res?.data?.[0]?.id,
          },
        });
      }
      dispatch({ type: Types.ResetBooking });
    },
    onError: (err: NetworkError) => {
      if (err.code === "LOCATION_AMBIGUOUS") {
        error(
          t("choose.a.branch.description", {
            defaultValue: "This shop has multiple locations - please select one to continue booking.",
          })
        );
        router.back();
        return;
      }
      error(err.message);
    },
  });

  const handleCreateBooking = useCallback(() => {
    const body: BookingCreateBody = {
      data: state.services.map((service) => {
        const startDateTime = state.dateAndTimes.find(
          (item) => item.serviceMasterId === service.master?.service_master?.id
        );
        return {
          service_master_id: service.master?.service_master?.id,
          note: service.note,
          data:
            service.type === "offline_out"
              ? { ...(state.address as BookingAddress), ...state.extraAddress }
              : undefined,
          user_member_ship_id: service.userMemberShipId,
          service_extras: service?.selected_extras?.length
            ? service?.selected_extras.map((extra) => extra.id)
            : undefined,
          start_date: `${dayjs(startDateTime?.date).format("YYYY-MM-DD")} ${startDateTime?.time}`,
          shop_location_id: state.shopLocationId,
        };
      }),
      currency_id: currency?.id,
      payment_id: state.payment?.id,
      user_gift_cart_id: state.giftCart?.shopGiftCartId,
      coupon: state.coupon,
      from_wallet_price:
        state.payment?.tag !== "wallet" && state.fromWalletPrice
          ? state.fromWalletPrice
          : undefined,
    };
    createBooking(body);
  }, [state]);

  const handleClose = () => {
    dispatch({ type: Types.ResetBooking });
    setOrderDetail(undefined);
    router.replace("/appointments");
  };

  return (
    <>
      <BookingTotal
        checkPayment
        isLoading={isLoading}
        data={shop}
        onClick={handleCreateBooking}
        showCoupon
        runCalculate
      />
      <Modal isOpen={!!orderDetail} onClose={handleClose} withCloseButton>
        <BookingDetail
          data={orderDetail}
          id={orderDetail?.[0]?.parent_id || orderDetail?.[0]?.id}
        />
      </Modal>
    </>
  );
};
