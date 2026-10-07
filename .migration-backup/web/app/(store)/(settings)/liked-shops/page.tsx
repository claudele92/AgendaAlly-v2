"use client";

import useLikeStore from "@/global-store/like";
import React from "react";
import { ProductCardUi1Loading } from "@/components/product-card/product-card-ui-1";
import { useInfiniteQuery, useQuery } from "@tanstack/react-query";
import { likeService } from "@/services/like";
import { extractDataFromPagination } from "@/utils/extract-data";
import { InfiniteLoader } from "@/components/infinite-loader";
import { LoadingCard } from "@/components/loading";
import { Empty } from "@/components/empty";
import useUserStore from "@/global-store/user";
import useAddressStore from "@/global-store/address";
import { shopService } from "@/services/shop";
import { ShopCard } from "@/components/shop-card";
import { useSettings } from "@/hook/use-settings";

const LikedSalons = () => {
  const user = useUserStore((state) => state.user);
  const { currency, language } = useSettings();
  const country = useAddressStore((state) => state.country);
  const city = useAddressStore((state) => state.city);
  const { list } = useLikeStore();
  const setLiked = useLikeStore((state) => state.setMany);
  const {
    data: shops,
    isLoading: shopsLoading,
    isError: listError,
    isFetching: isFetchingShopsById,
  } = useQuery(
    ["shopsByIds", list, user],
    () =>
      shopService.getByIds({
        ids: list.shop.map((listItem) => listItem.itemId),
        lang: language?.locale,
        currency_id: currency?.id,
        city_id: city?.id,
        country_id: country?.id,
        region_id: country?.region_id,
      }),
    {
      enabled: !user && !!list.shop?.length,
      retry: false,
      keepPreviousData: true,
    }
  );
  const {
    data: likedShopsList,
    isLoading: likedShopsLoading,
    hasNextPage,
    fetchNextPage,
    isFetchingNextPage,
    isFetching,
  } = useInfiniteQuery(
    ["likedShops", user, country?.id, city?.id],
    ({ pageParam }) =>
      likeService.getShopAll({
        type: "shop",
        lang: language?.locale,
        city_id: city?.id,
        country_id: country?.id,
        page: pageParam,
        region_id: country?.region_id,
      }),
    {
      enabled: !!user,
      staleTime: 0,
      getNextPageParam: (lastPage) => lastPage.links.next && lastPage.meta.current_page + 1,
      refetchOnWindowFocus: false,
      onSuccess: (res) => {
        setLiked(
          "shop",
          res.pages.flatMap((page) => page.data).map((shop) => ({ itemId: shop.id, sent: true }))
        );
      },
    }
  );
  const likedShops = extractDataFromPagination(likedShopsList?.pages);

  const actualList = user ? likedShops : shops?.data;

  if ((actualList && actualList.length === 0) || listError || (!user && !list.shop?.length)) {
    return (
      <div className="h-full">
        <h1 className="md:text-head text-base font-semibold mb-6">Favorite businesses</h1>
        <div className="flex justify-center relative h-full items-center">
          {isFetching && <LoadingCard centered />}
          <Empty
            animated={false}
            text="empty.favorites"
          />
        </div>
      </div>
    );
  }
  if (user ? likedShopsLoading : shopsLoading && isFetchingShopsById) {
    return (
      <div>
        <h1 className="md:text-head text-base font-semibold mb-6">Favorite businesses</h1>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 md:gap-7 sm:gap-4 gap-2">
          {Array.from(Array(6).keys()).map((item) => (
            <ProductCardUi1Loading key={item} />
          ))}
        </div>
      </div>
    );
  }
  return (
    <div>
      <h1 className="md:text-head text-base font-semibold mb-6">Favorite businesses</h1>
      <InfiniteLoader loadMore={fetchNextPage} hasMore={hasNextPage} loading={isFetchingNextPage}>
        <div className="relative  grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 md:gap-7 sm:gap-4 gap-2">
          {(isFetching || isFetchingShopsById) && <LoadingCard centered />}
          {actualList?.map((shop) => (
            <ShopCard data={shop} key={shop.id} />
          ))}
        </div>
      </InfiniteLoader>
    </div>
  );
};

export default LikedSalons;
