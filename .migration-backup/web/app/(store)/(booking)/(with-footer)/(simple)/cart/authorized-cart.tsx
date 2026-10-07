"use client";

import React, { useEffect, useMemo, useRef, useState, useTransition } from "react";
import { useServerCart } from "@/hook/use-server-cart";
import Image from "next/image";
import { useTranslation } from "react-i18next";
import { Button } from "@/components/button";
import { cartService } from "@/services/cart";
import useSettingsStore from "@/global-store/settings";
import { Price } from "@/components/price";
import dynamic from "next/dynamic";
import useCartStore from "@/global-store/cart";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { CartCalculateBody } from "@/types/cart";
import { Shop } from "@/types/shop";
import { LoadingCard } from "@/components/loading";
import { CartTotal } from "@/components/cart-total";
import { useRouter } from "next/navigation";
import useAddressStore from "@/global-store/address";
import TrashIcon from "@/assets/icons/trash";
import { useModal } from "@/hook/use-modal";
import { ConfirmModal } from "@/components/confirm-modal";
import NetworkError from "@/utils/network-error";
import { error, warning } from "@/components/alert";
import useUserStore from "@/global-store/user";
import { BackButton } from "@/components/back-button";
import { useSettings } from "@/hook/use-settings";
import Wallet3LineIcon from "remixicon-react/Wallet3LineIcon";
import HandCoinLineIcon from "remixicon-react/HandCoinLineIcon";
import { Drawer } from "@/components/drawer";
import { OrderCreateBody } from "@/types/order";
import dayjs from "dayjs";
import { internalPayments } from "@/config/global";
import { orderService } from "@/services/order";
import { useExternalPayment } from "@/hook/use-external-payment";
import { Types } from "@/context/checkout/checkout.reducer";
import { useCheckout } from "@/context/checkout/checkout.context";
import { Modal } from "@/components/modal";
import { useMediaQuery } from "@/hook/use-media-query";
import { Wallet } from "@/components/payment-list/wallet";
import { Payment } from "@/types/global";
import { UserCartItem } from "./components/user-cart-item";
import { CartItem } from "./components/cart-item";
import CheckoutShipping from "./components/shipping";

const Empty = dynamic(() =>
  import("@/components/empty").then((component) => ({ default: component.Empty }))
);

const PaymentList = dynamic(() => import("./components/payment"), {
  loading: () => <LoadingCard />,
});

const Tips = dynamic(() => import("@/components/tips/tips"), {
  loading: () => <LoadingCard />,
});

const AuthorizedCart = () => {
  const router = useRouter();
  const {
    data,
    error: cartError,
    isLoading,
    isFetching: isCartFetching,
  } = useServerCart(true);
  const { currency, language, settings } = useSettings();
  const country = useAddressStore((state) => state.country);
  const city = useAddressStore((state) => state.city);
  const clearCart = useCartStore((state) => state.clear);
  const { t } = useTranslation();
  const [isPending, startTransition] = useTransition();
  const [isOrderCreateSuccess, setIsOrderCreateSuccess] = useState(false);
  const [isOrderPermissionModalOpen, openPermissionModal, closePermissionModal] = useModal();
  const { dispatch, state: checkoutState } = useCheckout();
  const previousGeography = useRef({
    countryId: country?.id,
    cityId: city?.id,
  });
  const defaultCurrency = useSettingsStore((state) => state.defaultCurrency);
  const [isClearModalOpen, openClearModal, closeClearModal] = useModal();
  const [isPaymentDrawerOpen, openPaymentDrawer, closePaymentDrawer] = useModal();
  const [isTipsModalOpen, openTipsModal, closeTipsModal] = useModal();
  const user = useUserStore((state) => state.user);
  const queryClient = useQueryClient();
  const isMobile = useMediaQuery("(max-width: 640px)");
  const cartDetailsLength = data?.data.user_carts.flatMap((userCart) =>
    userCart.cartDetails.flatMap((detail) => detail.cartDetailProducts)
  ).length;
  const physicalShops = useMemo(() => {
    const shops = new Map<number, Shop>();
    data?.data.user_carts.forEach((userCartItem) =>
      userCartItem.cartDetails.forEach((detail) => {
        if (detail.cartDetailProducts.some((item) => !item.stock.product.digital)) {
          shops.set(detail.shop.id, detail.shop);
        }
      })
    );
    return Array.from(shops.values());
  }, [data?.data.user_carts]);
  const productFulfillmentMethods = useMemo(() => {
    const supportsEveryShop = (method: "delivery" | "pickup") =>
      physicalShops.length > 0 &&
      physicalShops.every((shop) =>
        (shop.product_fulfillment_methods?.length
          ? shop.product_fulfillment_methods
           : ["delivery"]
        ).includes(method)
      );
    return [
      ...(supportsEveryShop("delivery") ? (["delivery"] as const) : []),
      ...(supportsEveryShop("pickup") ? (["pickup"] as const) : []),
      ...(supportsEveryShop("delivery") ? (["point"] as const) : []),
    ];
  }, [physicalShops]);
  const hasMixedShopFulfillment = useMemo(() => {
    if (physicalShops.length < 2) return false;
    return (["delivery", "pickup"] as const).some((method) => {
      const count = physicalShops.filter((shop) =>
        (shop.product_fulfillment_methods?.length
          ? shop.product_fulfillment_methods
           : ["delivery"]
        ).includes(method)
      ).length;
      return count > 0 && count < physicalShops.length;
    });
  }, [physicalShops]);
  const userCart = data?.data?.user_carts?.find(
    (userCartItem) => userCartItem.user_id === user?.id
  );
  // A cart can span multiple shops (group orders); Orange/MTN settle into
  // one shop's own merchant account, so those are only offerable when the
  // whole cart belongs to a single shop - see PaymentController::index().
  const cartShopIds = Array.from(
    new Set(
      data?.data.user_carts.flatMap((userCartItem) =>
        userCartItem.cartDetails.map((detail) => detail.shop_id)
      ) ?? []
    )
  );
  const singleCartShopId = cartShopIds.length === 1 ? cartShopIds[0] : undefined;

  const { mutate: createOrder, isLoading: isOrderCreateLoading } = useMutation({
    mutationFn: (body: OrderCreateBody) => orderService.create(body),
    onError: (err: NetworkError) => {
      error(err.message);
    },
  });
  const { mutate: externalPay, isLoading: isExternalPayLoading } = useExternalPayment();

  const isEveryItemDigital = data?.data.user_carts
    .flatMap((cartItem) => cartItem.cartDetails)
    .flatMap((detail) => detail.cartDetailProducts)
    .every((product) => product.stock.product.digital);

  const {
    data: cartTotal,
    isFetching: isCalculating,
    isError,
    refetch: retryCartTotal,
  } = useQuery({
    queryKey: [
      "calculate",
      currency?.id,
      language?.locale,
      userCart?.cartDetails,
      checkoutState,
      isEveryItemDigital,
      country?.id,
      city?.id,
    ],
    queryFn: () => {
      const body: CartCalculateBody = {
        currency_id: currency?.id,
        lang: language?.locale,
        tips: checkoutState?.tips,
      };
      if (
        !isEveryItemDigital &&
        checkoutState.deliveryType === "delivery" &&
        !!checkoutState.deliveryPrice
      ) {
        body.country_id = country?.id;
        body.city_id = city?.id;
        body.delivery_price_id = checkoutState.deliveryPrice?.id;
        body.delivery_type = checkoutState.deliveryType;
      }
      if (!isEveryItemDigital && checkoutState.deliveryType === "point") {
        body.delivery_point_id = checkoutState.deliveryPoint?.id;
        body.delivery_type = checkoutState.deliveryType;
      }
      if (!isEveryItemDigital && checkoutState.deliveryType === "pickup") {
        body.delivery_type = "pickup";
      }
      if (isEveryItemDigital) {
        body.delivery_type = "digital";
      }
      if (
        Object.values(checkoutState.coupons).filter((coupon) => typeof coupon !== "undefined")
          .length !== 0
      ) {
        body.coupon = checkoutState.coupons;
      }
      return cartService.calculate(data?.data?.id, body);
    },
    enabled:
      !isCartFetching &&
      !cartError &&
      !!userCart &&
      !!cartDetailsLength &&
      (checkoutState.deliveryType === "point" ? !!checkoutState.deliveryPoint : true),
    staleTime: Infinity,
    keepPreviousData: true,
    retry: false,
  });

  const { mutate: clearAll, isLoading: isClearing } = useMutation({
    mutationFn: () => cartService.clearAll(),
    onSuccess: () => {
      queryClient.invalidateQueries(["cart"], { exact: false });
      queryClient.setQueriesData({ queryKey: ["cart"], exact: false }, () => undefined);
    },
    onSettled: () => {
      closeClearModal();
    },
    onError: (err: NetworkError) => {
      error(err.message);
    },
  });

  const {
    data: payments,
    isLoading: isPaymentsLoading,
    isFetching: isPaymentsFetching,
    isError: isPaymentsError,
  } = useQuery({
    queryKey: ["payments", data?.data?.id, singleCartShopId, currency?.id],
    queryFn: () =>
      orderService.paymentList({
        cart_id: data?.data?.id,
        shop_id: singleCartShopId,
        // ShopLocation::PRODUCT in the backend
        location_type: singleCartShopId ? 1 : undefined,
        currency_id: currency?.id,
      }),
    enabled: !isCartFetching && !cartError && !!data?.data?.id && !!cartDetailsLength,
  });

  useEffect(() => {
    if (isPaymentsFetching || isPaymentsLoading) return;
    const selected = checkoutState.paymentMethod;
    if (isPaymentsError || !payments?.data) {
      if (selected) {
        dispatch({ type: Types.UpdatePaymentMethod, payload: { paymentMethod: undefined } });
      }
      if (checkoutState.fromWalletPrice) {
        dispatch({ type: Types.UpdateFromWalletPrice, payload: { fromWalletPrice: undefined } });
      }
      return;
    }
    const walletAvailable =
      payments.meta?.payment_context?.valid !== false &&
      payments.data.some((payment) => payment.tag === "wallet");
    if (!walletAvailable && checkoutState.fromWalletPrice) {
      dispatch({ type: Types.UpdateFromWalletPrice, payload: { fromWalletPrice: undefined } });
    }
    if (payments.meta?.payment_context?.valid === false && selected) {
      dispatch({ type: Types.UpdatePaymentMethod, payload: { paymentMethod: undefined } });
      return;
    }
    if (selected && !payments.data.some((payment) => payment.id === selected.id)) {
      dispatch({ type: Types.UpdatePaymentMethod, payload: { paymentMethod: undefined } });
      return;
    }
    if (
      !selected &&
      payments.meta?.payment_context?.valid !== false &&
      payments.data.length > 0
    ) {
      const defaultPayment = payments.data.find((payment) => payment.tag === "cash");
      if (defaultPayment) {
        dispatch({ type: Types.UpdatePaymentMethod, payload: { paymentMethod: defaultPayment } });
      }
    }
  }, [
    checkoutState.paymentMethod,
    checkoutState.fromWalletPrice,
    dispatch,
    isPaymentsFetching,
    isPaymentsError,
    isPaymentsLoading,
    payments?.meta?.payment_context?.valid,
    payments?.data,
  ]);

  useEffect(() => {
    if (isEveryItemDigital || !productFulfillmentMethods.length) return;
    if (!productFulfillmentMethods.includes(checkoutState.deliveryType as "delivery" | "pickup" | "point")) {
      dispatch({
        type: Types.UpdateDeliveryType,
        payload: { type: productFulfillmentMethods[0] },
      });
    }
  }, [checkoutState.deliveryType, dispatch, isEveryItemDigital, productFulfillmentMethods]);

  useEffect(() => {
    if (
      previousGeography.current.countryId === country?.id &&
      previousGeography.current.cityId === city?.id
    ) {
      return;
    }
    previousGeography.current = {
      countryId: country?.id,
      cityId: city?.id,
    };
    dispatch({
      type: Types.UpdateDeliveryType,
      payload: { type: checkoutState.deliveryType },
    });
  }, [country?.id, city?.id, checkoutState.deliveryType, dispatch]);

  const paymentContext = payments?.meta?.payment_context;
  const walletPayment =
    paymentContext?.valid === false
      ? undefined
      : payments?.data?.find((item) => item.tag === "wallet");
  const paymentsWithoutWallet = {
    ...(payments || {}),
    data: payments?.data?.filter((payment) => payment?.tag !== "wallet"),
  };

  const handleClearCart = () => {
    clearAll();
  };

  const handleChangePayment = (value?: Payment) => {
    dispatch({ type: Types.UpdatePaymentMethod, payload: { paymentMethod: value } });
  };

  const handleChangeFromWalletPrice = (value?: number) => {
    if (typeof value === "number" && value !== cartTotal?.data?.total_price) {
      openPaymentDrawer();
    }
    dispatch({ type: Types.UpdateFromWalletPrice, payload: { fromWalletPrice: value } });
    if (!value) {
      handleChangePayment();
    }
  };

  const handleOrderCreateSuccess = (orderId: number) => {
    router.push(`/orders/${orderId}`, { scroll: false });
    setIsOrderCreateSuccess(true);
    clearCart();
  };

  const handleCreateOrder = async () => {
    const body: OrderCreateBody = {
      delivery_date: dayjs(new Date()).format("YYYY-MM-DD HH:mm"),
      currency_id: currency?.id,
      rate: currency?.rate,
      cart_id: data?.data?.id,
      delivery_type: isEveryItemDigital ? "digital" : checkoutState.deliveryType,
      tips: checkoutState?.tips,
      notes:
        Object.keys(checkoutState.notes).length !== 0 ||
        Object.keys(checkoutState.shopNotes).length !== 0
          ? {
              product:
                Object.keys(checkoutState.notes).length !== 0 ? checkoutState.notes : undefined,
              order:
                Object.keys(checkoutState.shopNotes).length !== 0
                  ? checkoutState.shopNotes
                  : undefined,
            }
          : undefined,
      from_wallet_price:
        checkoutState.paymentMethod?.tag !== "wallet" && checkoutState.fromWalletPrice
          ? checkoutState.fromWalletPrice
          : undefined,
    };
    if (!isEveryItemDigital && checkoutState.deliveryType === "point") {
      body.delivery_point_id = checkoutState.deliveryPoint?.id;
    }
    if (!isEveryItemDigital && checkoutState.deliveryType === "delivery") {
      body.delivery_price_id = checkoutState.deliveryPrice?.id;
      body.address_id = checkoutState.deliveryAddress?.id;
    }
    if (!isEveryItemDigital && checkoutState.deliveryType === "pickup") {
      const pickupSelections: NonNullable<OrderCreateBody["pickup_selections"]> = {};
      physicalShops.forEach((shop) => {
        const selection = checkoutState.pickupSelections[shop.id];
        if (selection?.shop_location_id) {
          const { legacy_ready_based, ...serverSelection } = selection;
          pickupSelections[shop.id] = serverSelection;
        }
      });
      body.pickup_selections = pickupSelections;
    }

    const tempCoupons = { ...checkoutState.coupons };

    Object.entries(checkoutState.coupons).forEach(([key, coupon]) => {
      if (typeof coupon === "undefined") {
        delete tempCoupons[Number(key)];
      }
    });

    if (Object.keys(tempCoupons).length !== 0) {
      body.coupon = tempCoupons;
      body.coupon = tempCoupons;
    }

    if (internalPayments.includes(checkoutState.paymentMethod?.tag || "")) {
      body.payment_id = checkoutState.paymentMethod?.id;
    }

    if (!internalPayments.includes(checkoutState.paymentMethod?.tag || "")) {
      externalPay(
        {
          tag: checkoutState.paymentMethod?.tag,
          data: body,
        },
        {
          onSuccess: async () => {
            dispatch({ type: Types.ClearState, payload: { all: true } });
            await queryClient.invalidateQueries(["profile"], { exact: false });
            await queryClient.invalidateQueries(["cart"], { exact: false });
          },
        }
      );
      return;
    }

    createOrder(body, {
      onSuccess: async (res) => {
        dispatch({ type: Types.ClearState, payload: { all: true } });
        await queryClient.invalidateQueries(["profile"], { exact: false });
        await queryClient.invalidateQueries(["cart"], { exact: false });
        const parentOrder = res.data.find(
          (orderDetail) => typeof orderDetail.parent_id === "undefined"
        );

        if (parentOrder) {
          handleOrderCreateSuccess(parentOrder.id);
        }
        dispatch({ type: Types.ClearState, payload: { all: false } });
      },
    });
  };

  const handleGoToCheckout = () => {
    if (
      settings?.min_amount &&
      cartTotal?.data &&
      Number(settings?.min_amount) > cartTotal.data.price
    ) {
      warning(
        <span>
          {t("order.price.did.not.reach.the.min.amount.min.amount.is")}{" "}
          <Price number={Number(settings?.min_amount)} customCurrency={defaultCurrency} />
        </span>
      );
      return;
    }
    const members = data?.data.user_carts.filter((item) => item.user_id !== data?.data.owner_id);
    const isMemberActive = members?.some((item) => item.status);
    if (isMemberActive) {
      openPermissionModal();
      return;
    }
    startTransition(() => handleCreateOrder());
  };

  useEffect(() => {
    handleChangeFromWalletPrice();
  }, [cartTotal?.data?.total_price]);

  if (isOrderCreateSuccess) {
    return (
      <section className="xl:container px-4">
        <BackButton title="order.detail" />
        <div className="flex items-center justify-center flex-col my-20">
          <Image src="/img/order-success.png" alt="empty_cart" width={300} height={400} />
          <strong className="text-xl font-bold">{t("congrats")}</strong>
          <span className="text-lg font-medium text-center ">{t("order.success.message")}</span>
        </div>
      </section>
    );
  }
  if (isLoading && isPaymentsLoading) {
    return (
      <section className="xl:container px-4">
        <div className="grid grid-cols-7">
          <div className="flex flex-col gap-7 col-span-5">
            <div className="flex gap-7 animate-pulse">
              <div className="relative overflow-hidden lg:h-[320px] md:h-56 h-40 rounded-3xl aspect-[250/320] bg-gray-300" />
              <div className="flex-1 my-5">
                <div className="h-[22px] rounded-full w-full bg-gray-300 line-clamp-1" />
                <div className="h-4 mt-5 rounded-full bg-gray-300 w-4/5" />
                <div className="h-4 mt-4 rounded-full bg-gray-300 w-3/5" />
              </div>
            </div>
            <div className="flex gap-7 animate-pulse">
              <div className="relative overflow-hidden lg:h-[320px] md:h-56 h-40 rounded-3xl aspect-[250/320] bg-gray-300" />
              <div className="flex-1 my-5">
                <div className="h-[22px] rounded-full w-full bg-gray-300 line-clamp-1" />
                <div className="h-4 mt-5 rounded-full bg-gray-300 w-4/5" />
                <div className="h-4 mt-4 rounded-full bg-gray-300 w-3/5" />
              </div>
            </div>
          </div>
        </div>
      </section>
    );
  }
  if (cartError) {
    return (
      <section className="xl:container px-4">
        <BackButton title="order.detail" />
        <p className="py-8 text-center" role="alert">
          {(cartError instanceof Error && cartError.message) || t("unexpected.error.occured")}
        </p>
      </section>
    );
  }
  if ((!userCart && !cartDetailsLength) || cartDetailsLength === 0) {
    return (
      <section className="xl:container px-4">
        <BackButton title="order.detail" />
        <Empty animated={false} text="your.cart.is.empty" imagePath="/img/empty_cart.png" />
      </section>
    );
  }
  return (
    <section className="xl:container px-4 mb-4">
      <div className="flex items-center justify-between">
        <BackButton title="order.detail" />
        <button onClick={openClearModal} className="flex items-center gap-2.5 text-red-600">
          <TrashIcon />
          {t("clear.all")}
        </button>
      </div>
      <div className="grid grid-cols-7 mt-7 gap-7 relative pb-24">
        <div className="flex flex-col lg:col-span-4 col-span-7 gap-5 ">
          <div className="border border-gray-link rounded-button md:p-10 p-2">
      <CheckoutShipping
        methods={productFulfillmentMethods}
        pickupShops={physicalShops}
        hasMixedShopFulfillment={hasMixedShopFulfillment}
        allDigital={!!isEveryItemDigital}
      />
          </div>
          <div className="flex flex-col gap-7 md:border border-gray-link md:rounded-button md:p-10">
            {data?.data.group
              ? data?.data.user_carts?.map((userCartItem) => (
                  <UserCartItem
                    ownerId={data?.data.owner_id}
                    key={userCartItem.id}
                    data={userCartItem}
                    currency={cartTotal?.data?.currency}
                  />
                ))
              : userCart?.cartDetails.map((detail) => (
                  <CartItem
                    key={detail.id}
                    data={detail}
                    disabled={isCalculating}
                    cartUuid={userCart.uuid}
                    userId={data?.data.owner_id}
                    showCoupon
                    currency={cartTotal?.data?.currency}
                  />
                ))}
            {!!cartTotal?.data?.errors?.length && (
              <div className="flex flex-col gap-y-3">
                {cartTotal?.data?.errors?.map((item) => (
                  <span className="text-sm text-red">{item?.message}</span>
                ))}
              </div>
            )}
          </div>
        </div>
        <div className="lg:col-span-3 col-span-7">
          <div className="sticky top-2">
            <div className="md:border border-gray-link rounded-button md:p-10">
              <strong className="text-head font-semibold">{t("payment")}</strong>
              {(user?.wallet?.price || 0) > 0 && walletPayment && (
                <div className="mt-3">
                  <Wallet
                    totalPrice={cartTotal?.data?.total_price}
                    fromWalletPrice={checkoutState.fromWalletPrice}
                    onFullPricePaid={() => handleChangePayment(walletPayment)}
                    onChangeWalletPrice={handleChangeFromWalletPrice}
                  />
                </div>
              )}
              <div className="flex items-center justify-between mt-6">
                <div className="flex items-center gap-4">
                  <Wallet3LineIcon />
                  <span className="text-base font-medium">
                    {checkoutState.paymentMethod
                      ? t(checkoutState.paymentMethod.tag)
                      : t("add.payment.method")}
                  </span>
                </div>
                {paymentContext?.transaction_currency && (
                  <p className="mt-2 text-sm text-gray-600" role="status">
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
                {paymentContext?.valid === false && (
                  <p className="mt-2 text-sm text-red-600" role="alert">
                    {t("payment.context.unavailable", {
                      defaultValue: "Payment methods are unavailable for this transaction context.",
                    })}
                  </p>
                )}
                {isPaymentsError && (
                  <p className="mt-2 text-sm text-red-600" role="alert">
                    {t("payment.methods.failed.to.load", {
                      defaultValue: "Payment methods could not be loaded. Please try again.",
                    })}
                  </p>
                )}
                <Button size="xsmall" color="gray" onClick={openPaymentDrawer}>
                  {t("edit")}
                </Button>
              </div>
              <div className="flex items-center justify-between mt-6">
                <div className="flex items-center gap-4">
                  <HandCoinLineIcon />
                  <span className="text-base font-medium">
                    {checkoutState.tips ? (
                      <Price number={checkoutState.tips} customCurrency={cartTotal?.data?.currency} />
                    ) : (
                      t("add.tips")
                    )}
                  </span>
                </div>
                <Button size="xsmall" color="gray" onClick={openTipsModal}>
                  {t("edit")}
                </Button>
              </div>
              <CartTotal totals={cartTotal?.data} couponStyle={false} />
              {isError && (
                <div className="mt-3 rounded-button border border-badge-product bg-gray-card p-3" role="alert">
                  <p className="text-sm">
                    {t("cart.total.failed", {
                      defaultValue: "Order totals could not be updated.",
                    })}
                  </p>
                  <Button
                    className="mt-2"
                    size="xsmall"
                    color="gray"
                    onClick={() => void retryCartTotal()}
                  >
                    {t("retry", { defaultValue: "Retry" })}
                  </Button>
                </div>
              )}
              <Button
                className="md:mt-10 mt-4"
                loading={isPending || isCalculating || isOrderCreateLoading || isExternalPayLoading}
                fullWidth
                color="black"
                disabled={
                  isError ||
                  isPaymentsLoading ||
                  isPaymentsFetching ||
                  isPaymentsError ||
                  paymentContext?.valid === false ||
                  !payments?.data?.length ||
                  !checkoutState.paymentMethod ||
                  (!isEveryItemDigital &&
                    !productFulfillmentMethods.includes(
                      checkoutState.deliveryType as "delivery" | "pickup" | "point"
                    )) ||
                  (checkoutState.deliveryType === "point" && !checkoutState.deliveryPoint) ||
                  (checkoutState.deliveryType === "delivery" &&
                    (!checkoutState.deliveryPrice || !checkoutState.deliveryAddress)) ||
                  (checkoutState.deliveryType === "pickup" &&
                    (!productFulfillmentMethods.includes("pickup") ||
                      physicalShops.some((shop) => {
                        const selection = checkoutState.pickupSelections[shop.id];
                        return (
                          !selection ||
                          (!selection.legacy_ready_based && !selection.shop_location_id) ||
                          (!!selection.date &&
                            (!selection.window_start || !selection.window_end))
                        );
                      })))
                }
                onClick={handleGoToCheckout}
              >
                {t("checkout")}
                {" - "}
                <Price
                  number={(cartTotal?.data?.total_price ?? 0) + (cartTotal?.data?.tips ?? 0)}
                  customCurrency={cartTotal?.data?.currency}
                />
              </Button>
            </div>
          </div>
        </div>
      </div>
      <Drawer
        position="right"
        open={isPaymentDrawerOpen}
        onClose={closePaymentDrawer}
        container={false}
      >
        <PaymentList
          onSelect={closePaymentDrawer}
          payments={paymentsWithoutWallet}
          isLoading={isPaymentsLoading}
          isError={isPaymentsError}
          paymentContext={paymentContext}
        />
      </Drawer>
      <Modal isOpen={isTipsModalOpen} onClose={closeTipsModal} withCloseButton={!isMobile}>
        <Tips
          totalPrice={cartTotal?.data?.total_price ?? 0}
          currency={cartTotal?.data?.currency}
          onSubmit={(num) => {
            dispatch({ type: Types.UpdateTips, payload: { tips: num } });
            closeTipsModal();
          }}
        />
      </Modal>
      <ConfirmModal
        text="are.you.sure.want.to.clear.all.items.in.the.cart"
        onConfirm={handleClearCart}
        onCancel={closeClearModal}
        isOpen={isClearModalOpen}
        loading={isClearing}
      />
      <ConfirmModal
        text="group.order.permission"
        onConfirm={() => startTransition(() => handleGoToCheckout())}
        onCancel={closePermissionModal}
        isOpen={isOrderPermissionModalOpen}
      />
    </section>
  );
};

export default AuthorizedCart;
