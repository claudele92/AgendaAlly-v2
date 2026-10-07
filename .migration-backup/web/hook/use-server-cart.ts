import useCartStore from "@/global-store/cart";
import useAddressStore from "@/global-store/address";
import { useQuery } from "@tanstack/react-query";
import { cartService } from "@/services/cart";
import useUserStore from "@/global-store/user";
import { useState } from "react";
import { useSettings } from "@/hook/use-settings";
import { DefaultResponse } from "@/types/global";
import { Cart } from "@/types/cart";
import { isKnownMissingCartError } from "@/utils/cart-error.mjs";

export const useServerCart = (enabled = true, suspense = false) => {
  const [isGroup, setIsGroup] = useState(false);
  const updateLocalCart = useCartStore((state) => state.updateList);
  const { language } = useSettings();
  const country = useAddressStore((state) => state.country);
  const city = useAddressStore((state) => state.city);
  const user = useUserStore((state) => state.user);
  const params = {
    region_id: country?.region_id,
    country_id: country?.id,
    city_id: city?.id,
    lang: language?.locale,
  };
  return useQuery({
    queryKey: ["cart", params],
    queryFn: async (): Promise<DefaultResponse<Cart> | null> => {
      try {
        return await cartService.get(params);
      } catch (error) {
        // Null is a typed empty-cart state, not a fabricated API cart response.
        if (isKnownMissingCartError(error)) return null;
        throw error;
      }
    },

    onSuccess: (res) => {
      if (res === null) {
        updateLocalCart([]);
        setIsGroup(false);
        return;
      }
      const products = res.data?.user_carts
        ?.find((userCart) => userCart.user_id === user?.id)
        ?.cartDetails.flatMap((details) => details.cartDetailProducts)
        .map((product) => ({
          stockId: product.stock.id,
          quantity: product.quantity,
          cartDetailId: product.id,
        }));

      updateLocalCart(products || []);
      setIsGroup(res.data.group);
    },

    enabled,
    retry: false,
    suspense,
    refetchOnMount: "always",
    refetchInterval: isGroup ? 5000 : undefined,
  });
};
