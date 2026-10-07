"use client";

import dynamic from "next/dynamic";
import useUserStore from "@/global-store/user";
import useCartStore from "@/global-store/cart";
import { useQuery } from "@tanstack/react-query";
import { userService } from "@/services/user";

const AuthorizedCart = dynamic(() => import("./authorized-cart"));
const UnAuthorizedCart = dynamic(() => import("./unauthorized-cart"));
const MemberCart = dynamic(() => import("./member-cart"));

const CartPage = () => {
  const user = useUserStore((state) => state.user);
  const memberCart = useCartStore((state) => state.memberCartId);
  const userCartUuid = useCartStore((state) => state.userCartUuid);
  const signIn = useUserStore((state) => state.signIn);

  useQuery(["profile"], () => userService.profile(), {
    enabled: !!user,
    onSuccess: (res) => {
      signIn(res?.data);
    },
  });

  if (memberCart && userCartUuid) {
    return <MemberCart />;
  }

  if (user) {
    return <AuthorizedCart />;
  }

  return <UnAuthorizedCart />;
};
export default CartPage;
