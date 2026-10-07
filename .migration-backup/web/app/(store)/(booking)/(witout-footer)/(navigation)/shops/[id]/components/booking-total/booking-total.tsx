"use client";

import { Shop } from "@/types/shop";
import { Button } from "@/components/button";
import { useTranslation } from "react-i18next";
import MapPinIcon from "@/assets/icons/map-pin";
import { useSettings } from "@/hook/use-settings";
import { useQuery } from "@tanstack/react-query";
import { shopService } from "@/services/shop";
import { DefaultResponse } from "@/types/global";
import { ImageWithFallBack } from "@/components/image";
import { Price } from "@/components/price";
import { useRouter, useSearchParams } from "next/navigation";
import { useBooking } from "@/context/booking";
import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";
import Link from "next/link";
import { createMapUrl } from "@/utils/create-map-url";
import { bookingService } from "@/services/booking";
import utc from "dayjs/plugin/utc";
import { useTransition, useMemo } from "react";
import { useModal } from "@/hook/use-modal";
import { Modal } from "@/components/modal";
import dynamic from "next/dynamic";
import { LoadingCard } from "@/components/loading";
import { Types } from "@/context/booking/booking.reducer";
import useUserStore from "@/global-store/user";
import VerifiedIcon from "@/assets/icons/verified";
import checkTimeConflicts from "@/utils/check-service-time-conflicts";
import { useShopLocationParams } from "@/hook/use-shop-location-params";
import { BookingCouponCheck } from "../coupon-check";

const GiftCartSelect = dynamic(
  () => import("./gift-cart-select").then((component) => ({ default: component.GiftCartSelect })),
  {
    loading: () => <LoadingCard />,
  }
);

dayjs.extend(utc);

interface BookingTotalProps {
  data?: DefaultResponse<Shop>;
  nextPage?: string;
  onClick?: () => void;
  isLoading?: boolean;
  checkStaff?: boolean;
  checkDate?: boolean;
  checkPayment?: boolean;
  checkLocation?: boolean;
  showCoupon?: boolean;
  runCalculate?: boolean;
  checkNotePageAuth?: boolean;
  hasExtraAddressForm?: boolean;
  mobileInFlow?: boolean;
}

dayjs.extend(customParseFormat);

export const BookingTotal = ({
  data,
  nextPage,
  onClick,
  isLoading,
  checkStaff,
  checkDate,
  checkPayment,
  checkLocation,
  checkNotePageAuth,
  hasExtraAddressForm,
  mobileInFlow = false,
  showCoupon = false,
  runCalculate = false,
}: BookingTotalProps) => {
  const { language, currency } = useSettings();
  const user = useUserStore((state) => state.user);
  const { t } = useTranslation();
  const searchParams = useSearchParams();
  const { state, dispatch } = useBooking();
  const router = useRouter();
  const [isNavigating, startTransition] = useTransition();
  const [isGiftCartModalOpen, openGiftCartModal, closeGiftCartModal] = useModal();
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
  const matchedLocation = shopDetail?.data.matched_location;
  const displayAddress = matchedLocation?.address || shopDetail?.data.translation?.address;
  const displayLatitude = matchedLocation?.latitude ?? shopDetail?.data.lat_long.latitude;
  const displayLongitude = matchedLocation?.longitude ?? shopDetail?.data.lat_long.longitude;
  const calculateBody = {
    data: state.services.map((service) => {
      const startDateTime = state.dateAndTimes.find(
        (item) => item.serviceMasterId === service.master?.service_master?.id
      );
      return {
        service_master_id: service.master?.service_master?.id,
        user_member_ship_id: service.userMemberShipId,
        service_extras: service.selected_extras?.length
          ? service.selected_extras.map((extra) => extra.id)
          : undefined,
        start_date: `${dayjs(startDateTime?.date).format("YYYY-MM-DD")} ${startDateTime?.time}`,
      };
    }),
    currency_id: currency?.id,
    payment_id: state.payment?.id,
    user_gift_cart_id: state.giftCart?.shopGiftCartId,
    coupon: state.coupon,
  };
  const hasMembership = calculateBody.data.some((service) => service.user_member_ship_id);
  const {
    data: calculateRes,
    isError: isCalculateError,
    isFetching: isCalculating,
    isSuccess: isCalculateSuccess,
  } = useQuery(
    ["bookingCalculate", state],
    () => bookingService.calculate(calculateBody, { lang: language?.locale }),
    {
      enabled: !!user && !!state.time && state.services.length > 0 && runCalculate,
      retry: false,
      meta: {
        showErrorMessageFromServer: true,
      },
      onSuccess: (res) => {
        dispatch({ type: Types.UpdateTotalPrice, payload: res?.data?.total_price });
      },
    }
  );
  const localTotalPrice = state.services.reduce((acc, curr) => {
    const extrasTotal = curr?.selected_extras?.reduce(
      (extraAcc, extra) => extraAcc + (extra?.price || 0),
      0
    );
    return (
      acc +
      (curr?.master?.service_master?.price ??
        curr.min_price ??
        curr.price ??
        curr.total_price ??
        0) +
      (extrasTotal || 0)
    );
  }, 0);
  const totalPrice =
    typeof calculateRes?.data === "undefined" ? localTotalPrice : calculateRes?.data.total_price;

  let tempNextPage = nextPage;
  if (state.master && nextPage === "/booking/staff" && state.time) {
    tempNextPage = "/booking/note";
  }
  if (state.master && nextPage === "/booking/staff-select" && state.time) {
    tempNextPage = "/booking/note";
  }
  if (state.master && nextPage === "/booking/staff") {
    tempNextPage = "/booking/date";
  }
  if (
    state.services.some((service) => service.type === "offline_out") &&
    nextPage === "/booking/note" &&
    !state.address
  ) {
    tempNextPage = "/booking/location";
  }

  let isDisabled = state.services.length === 0;

  if (checkStaff) {
    isDisabled = state.services.some((service) => typeof service.master === "undefined");
  }
  if (checkDate) {
    isDisabled = state.services.length > state.dateAndTimes.length;
  }

  if (checkPayment) {
    isDisabled = !state.payment;
  }
  if (checkLocation) {
    isDisabled = !state.address;
  }

  if (checkNotePageAuth) {
    isDisabled = !user;
  }

  const handleButtonClick = () => {
    if (hasExtraAddressForm) return;
    const isNotePage = nextPage === "/booking/payment";
    if (onClick) {
      if (isNotePage) {
        if (calculateRes?.data?.total_price === 0) {
          onClick();
          return;
        }
      } else {
        onClick();
        return;
      }
    }
    startTransition(() =>
      router.push(
        `/shops/${shopDetail?.data.slug}${tempNextPage || "/bookings"}${
          searchParams.toString() ? `?${searchParams.toString()}` : ""
        }`
      )
    );
  };
  const allErrors = calculateRes?.data.items
    .flatMap((item) => item.errors)
    .filter((item) => typeof item !== "undefined");
  const serviceConflicts = useMemo(() => {
    // Only check for conflicts if we have services and dateAndTimes and dateSelectPage
    const isDateSelectPage = nextPage === "/booking/note";
    if (state.services.length === 0 || state.dateAndTimes.length === 0 || !isDateSelectPage) {
      return { hasConflicts: false, conflictsByMaster: [], summary: "No bookings to check" };
    }
    return checkTimeConflicts(state);
  }, [state.services, state.dateAndTimes]);

  return (
    <div className={`${mobileInFlow ? "relative w-full" : "fixed bottom-0 w-screen left-0 shadow-fixedBooking"} lg:static lg:w-auto lg:rounded-button rounded-t-button py-5 lg:px-5 px-4 lg:border border-gray-link lg:shadow-none bg-white`}>
      <div className="hidden lg:flex items-center gap-4 border-b border-gray-link pb-7">
        <div className="w-20 h-20 relative rounded-full border border-gray-link aspect-square ">
          <ImageWithFallBack
            src={shopDetail?.data?.logo_img}
            alt={shopDetail?.data.translation?.title || "shop"}
            fill
            className="object-contain rounded-full w-20 h-20"
          />
        </div>
        <div>
          <div className="flex items-center gap-2">
            <h2 className="text-[28px] font-semibold line-clamp-1">
              {shopDetail?.data.translation?.title}
            </h2>
            {shopDetail?.data?.verify && (
              <span>
                <VerifiedIcon />
              </span>
            )}
          </div>
          <Link
            className="flex items-start gap-1"
            href={createMapUrl(displayLatitude, displayLongitude)}
            target="_blank"
          >
            <MapPinIcon />
            <p className="text-sm line-clamp-2">{displayAddress}</p>
          </Link>
        </div>
      </div>
      {!!state.master && (
        <div className="flex items-center gap-1 border-b border-gray-link py-4">
          <div className="w-14 h-14 relative">
            <ImageWithFallBack
              src={state.master.img}
              alt={state.master.firstname || "master"}
              className="rounded-full object-cover"
              fill
            />
          </div>
          <div>
            <p className="text-xl font-medium">
              {state.master.firstname} {state.master?.lastname}
            </p>
            <span className="text-gray-field text-base font-semibold">Specialist</span>
          </div>
        </div>
      )}
      {state.services.length !== 0 && (
        <div className="hidden lg:block border-b border-gray-link">
          {state.services.map((service) => (
            <div key={service.id}>
              <div key={service.id} className="flex items-start justify-between py-4">
                <div>
                  <p className="text-xl font-semibold">{service.translation?.title}</p>
                  {service.selected_extras?.map((extra) => (
                    <p className="text-base font-medium">
                      {t("extra")}: {extra?.translation?.title}
                    </p>
                  ))}

                  <span className="text-base text-gray-field font-medium">
                    {t("time")}: {service?.master?.service_master?.interval ?? service.interval}
                    {t("min")}
                  </span>
                </div>
                <div>
                  <p className="text-xl font-semibold whitespace-nowrap">
                    <Price
                      number={
                        service?.master?.service_master?.price ??
                        service?.min_price ??
                        service?.price ??
                        service?.total_price ??
                        0
                      }
                      customCurrency={calculateRes?.data?.currency}
                    />
                  </p>
                  {service.selected_extras?.map((extra) => (
                    <p className="text-base font-medium" key={extra.id}>
                      <Price number={extra?.price ?? 0} customCurrency={calculateRes?.data?.currency} />
                    </p>
                  ))}
                </div>
              </div>
              {!!service.master && !state.master && (
                <div className="flex items-center gap-1 mb-4">
                  <div className="w-14 h-14 relative">
                    <ImageWithFallBack
                      src={service.master?.img}
                       alt={service.master?.firstname || "Specialist"}
                      className="rounded-full object-cover"
                      fill
                    />
                  </div>
                  <div>
                    <p className="text-xl font-medium">
                      {service.master?.firstname} {service.master?.lastname}
                    </p>
                    <span className="text-gray-field text-base font-semibold">Specialist</span>
                  </div>
                </div>
              )}
            </div>
          ))}
        </div>
      )}
      {!!calculateRes?.data?.coupon_price && (
        <div className="hidden lg:flex items-center justify-between py-7 text-xl border-b border-gray-link">
          <strong>{t("coupon")}</strong>
          <strong>
            <Price number={calculateRes?.data?.coupon_price} customCurrency={calculateRes?.data?.currency} />
          </strong>
        </div>
      )}

      {!!calculateRes?.data.total_discount && (
        <div className="hidden lg:flex items-center justify-between py-7 text-xl border-b border-gray-link">
          <strong>{t("total.discount")}</strong>
          <strong>
            <Price number={calculateRes?.data.total_discount} customCurrency={calculateRes?.data?.currency} />
          </strong>
        </div>
      )}

      {!!calculateRes?.data.total_gift_cart_price && (
        <div className="hidden lg:flex items-center justify-between py-7 text-xl border-b border-gray-link">
          <strong>{t("total.gift.card")}</strong>
          <strong>
            <Price
              number={calculateRes?.data?.total_gift_cart_price}
              customCurrency={calculateRes?.data?.currency}
            />
          </strong>
        </div>
      )}

      {!!calculateRes?.data.total_commission_fee && (
        <div className="hidden lg:flex items-center justify-between py-7 text-xl border-b border-gray-link">
          <strong>{t("total.commission.fee")}</strong>
          <strong>
            <Price
              number={calculateRes?.data.total_commission_fee}
              customCurrency={calculateRes?.data?.currency}
            />
          </strong>
        </div>
      )}

      {!!calculateRes?.data.total_service_fee && (
        <div className="hidden lg:flex items-center justify-between py-7 text-xl border-b border-gray-link">
          <strong>{t("total.service.fee")}</strong>
          <strong>
            <Price
              number={calculateRes?.data.total_service_fee}
              customCurrency={calculateRes?.data?.currency}
            />
          </strong>
        </div>
      )}

      {!!calculateRes?.data?.total_extra_price && (
        <div className="hidden lg:flex items-center justify-between py-7 text-xl border-b border-gray-link">
          <strong>{t("total.extras.price")}</strong>
          <strong>
            <Price
              number={calculateRes?.data?.total_extra_price}
              customCurrency={calculateRes?.data?.currency}
            />
          </strong>
        </div>
      )}

      <div className="hidden lg:flex items-center justify-between py-7 text-[26px] font-semibold">
        <strong>{t("total")}</strong>
        <strong>
          <Price
            number={totalPrice ?? calculateRes?.data.total_price}
            customCurrency={calculateRes?.data?.currency}
          />
        </strong>
      </div>
      <div>
        {allErrors?.map((error) => (
          <p className="text-red text-sm" role="alert" key={error}>
            {error}
          </p>
        ))}
      </div>
      {showCoupon && (
        <BookingCouponCheck
          defaultValue={state.coupon}
          shopId={shopDetail?.data.id}
          onCouponCheckSuccess={(coupon) =>
            dispatch({
              type: Types.UpdateCoupon,
              payload: {
                name: coupon.name,
              },
            })
          }
          onCouponCheckError={() =>
            dispatch({
              type: Types.UpdateCoupon,
              payload: {
                name: undefined,
              },
            })
          }
        />
      )}
      <div className="mt-4 flex flex-col gap-4">
        {onClick && !!user && isCalculateSuccess && !hasMembership && (
          <Button
            color="blackOutlined"
            size="medium"
            fullWidth
            onClick={() =>
              state.giftCart ? dispatch({ type: Types.DeleteGiftCart }) : openGiftCartModal()
            }
          >
            {t(state.giftCart ? "delete.gift.cart" : "use.gift.cart")}
          </Button>
        )}

        {serviceConflicts.conflictsByMaster.map((conflict) => (
          <p key={conflict.masterId} className="text-red text-sm" role="alert">
            {t("service.time.conflict.found", {
              master: conflict.masterName,
              services: conflict.conflictedServices.map(
                (service) => `${service.serviceName} (${service.startTime} - ${service.endTime})`
              ),
            })}
          </p>
        ))}

        <Button
          form={hasExtraAddressForm ? "external-address-form" : undefined}
          onClick={handleButtonClick}
          color="black"
          fullWidth
          size="medium"
          loading={isLoading || isCalculating || isNavigating}
          disabled={
            serviceConflicts.hasConflicts ||
            isDisabled ||
            isCalculateError ||
            (calculateRes?.data ? !calculateRes?.data.status : false) ||
            (state.master?.id !== undefined &&
              state.unsupportedMastersByServices?.includes(state.master.id))
          }
        >
          {t("book.now")}
        </Button>
      </div>
      <Modal isOpen={isGiftCartModalOpen} onClose={closeGiftCartModal} withCloseButton>
        <GiftCartSelect onSelect={closeGiftCartModal} shopId={shopDetail?.data.id} />
      </Modal>
    </div>
  );
};
