"use client";

import useLikeStore from "@/global-store/like";
import { InfiniteData, useMutation, useQueryClient } from "@tanstack/react-query";
import { LikeOptions, LikeTypes, Paginate } from "@/types/global";
import { likeService } from "@/services/like";
import { Product } from "@/types/product";
import useUserStore from "@/global-store/user";
import { useCallback } from "react";
import useAddressStore from "@/global-store/address";

const getQueryType = (type: LikeTypes) => {
  switch (type) {
    case "shop":
      return "likedShops";
    case "product":
      return "likedProducts";
    case "master":
      return "likedMasters";
    default:
      return type;
  }
};

export const useLike = (type: LikeTypes, itemId?: number) => {
  const queryClint = useQueryClient();
  const user = useUserStore((state) => state.user);
  const { list, likeOrDislike } = useLikeStore();
  const isLiked = list[type]?.some((item) => item.itemId === itemId);
  const country = useAddressStore((state) => state.country);
  const city = useAddressStore((state) => state.city);
  const { mutate: likeRequest } = useMutation({
    mutationFn: (body: LikeOptions) =>
      likeService.like({
        ...body,
        region_id: country?.region_id,
        country_id: country?.id,
        city_id: city?.id,
      }),
  });
  const { mutate: disLikeRequest } = useMutation({
    mutationFn: (body: LikeOptions) => likeService.dislike(body),
    onMutate: async (body) => {
      await queryClint.cancelQueries([getQueryType(type)], { exact: false });
      const prevLikeList = queryClint.getQueryData<InfiniteData<Paginate<Product>>>(
        [getQueryType(type)],
        {
          exact: false,
        }
      );
      console.log("prevLikeList => ", prevLikeList);
      queryClint.setQueriesData<InfiniteData<Paginate<Product>> | undefined>(
        { queryKey: [getQueryType(type)], exact: false },
        (old) => {
          console.log("old", old);
          if (!old?.pages) return prevLikeList;
          return {
            ...old,
            pages: old.pages.map((page) => ({
              ...page,
              data: page.data.filter((product) => product.id !== body.type_id),
            })),
          };
        }
      );

      return { prevLikeList };
    },
    onError: (error, variables, context) => {
      queryClint.setQueryData([type], context?.prevLikeList);
    },
    onSettled: () => {
      const customType = getQueryType(type);
      queryClint.invalidateQueries({ queryKey: [customType], exact: false });
    },
  });

  const handleLikeDisLike = useCallback(() => {
    if (itemId) {
      if (user) {
        if (isLiked) {
          disLikeRequest({ type_id: itemId, type });
        } else {
          likeRequest({ type_id: itemId, type });
        }
      }
      likeOrDislike(type, itemId, !!user);
      console.log("fired", itemId);
    }
  }, [type, itemId, user?.id]);
  return { handleLikeDisLike, isLiked };
};
