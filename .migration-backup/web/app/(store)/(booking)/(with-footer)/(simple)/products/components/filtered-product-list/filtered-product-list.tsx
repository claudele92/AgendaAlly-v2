"use client";

import { ProductCard } from "@/components/product-card";
import useFilterStore from "@/global-store/filter";
import clsx from "clsx";
import React, { useEffect } from "react";
import { useSearchParams } from "next/navigation";
import { useInfiniteQuery } from "@tanstack/react-query";
import { productService } from "@/services/product";
import { extractDataFromPagination } from "@/utils/extract-data";
import { InfiniteLoader } from "@/components/infinite-loader";
import dynamic from "next/dynamic";
import useAddressStore from "@/global-store/address";
import { useSettings } from "@/hook/use-settings";
import { Button } from "@/components/button";

interface Props {
  setTotalProducts: (totalProducts: number) => void;
}

const listStyles = {
  "1": "lg:grid-cols-4 md:grid-cols-3 grid-cols-2  md:gap-7 sm:gap-4 gap-2",
  "2": "lg:grid-cols-2 grid-cols-1  md:gap-7 sm:gap-4 gap-2",
  "3": "grid-cols-1  md:gap-7 sm:gap-4 gap-2",
  "4": "lg:grid-cols-3 md:grid-cols-2 grid-cols-1 md:gap-7 gap-6 ",
};

const Empty = dynamic(() =>
  import("@/components/empty").then((component) => ({ default: component.Empty }))
);
const ErrorFallback = dynamic(() => import("@/components/error-fallback"));

export const FilteredProductList = ({ setTotalProducts }: Props) => {
  const searchParams = useSearchParams();
  const { currency, language } = useSettings();

  const country = useAddressStore((state) => state.country);
  const city = useAddressStore((state) => state.city);
  const params = {
    shop_ids: searchParams.getAll("shop_id"),
    category_ids: searchParams.getAll("categories"),
    brand_ids: searchParams.getAll("brands"),
    price_from: searchParams.get("priceFrom"),
    price_to: searchParams.get("priceTo"),
    column: searchParams.get("column") || "min_price",
    sort: searchParams.get("sort") || "asc",
    brand_id: searchParams.get("brand_id"),
    extras: searchParams.getAll("extras"),
    has_discount: searchParams.get("has_discount"),
    currency_id: currency?.id,
    lang: language?.locale,
    banner_id: searchParams.get("bannerId"),
    country_id: country?.id,
    city_id: city?.id,
    region_id: country?.region_id,
    in_stock: searchParams.get("in_stock"),
  };
  const {
    data,
    isLoading,
    isError,
    refetch,
    isFetchingNextPage,
    fetchNextPage,
    hasNextPage,
  } = useInfiniteQuery({
    queryKey: ["products", params],
    queryFn: ({ pageParam }) => productService.getAll({ ...params, page: pageParam }),
    getNextPageParam: (lastPage) => lastPage.links.next && lastPage.meta.current_page + 1,
  });

  useEffect(() => {
    const totalProducts = data?.pages[0]?.meta?.total || 0;
    if (data) {
      setTotalProducts(totalProducts);
    }
  }, [data, setTotalProducts]);

  const productList = extractDataFromPagination(data?.pages);

  const productVariant = useFilterStore((state) => state.productVariant);
  if (isError && (!productList || productList.length === 0)) {
    return (
      <div className="col-span-full flex min-h-64 flex-col items-center justify-center gap-4" role="alert">
        <ErrorFallback />
        <Button size="small" color="black" onClick={() => refetch()}>
          Retry
        </Button>
      </div>
    );
  }
  if (isLoading) {
    return (
      <div className={clsx("grid ", listStyles[productVariant as keyof typeof listStyles])}>
        {Array.from(Array(10).keys()).map((product) => (
          <ProductCard.Loading variant={productVariant} key={product} />
        ))}
      </div>
    );
  }
  if (productList && productList.length === 0) {
    return <Empty text="no.products.found" />;
  }
  return (
    <>
      {isError && (
        <div
          className="mb-4 flex items-center justify-between gap-3 rounded-xl border border-[#ead1c8] bg-[#fff8f5] px-4 py-3 text-sm"
          role="alert"
        >
          <span>Could not refresh this catalog. Products already loaded are still shown.</span>
          <Button size="small" color="black" onClick={() => refetch()}>
            Retry
          </Button>
        </div>
      )}
      <InfiniteLoader loadMore={fetchNextPage} hasMore={hasNextPage} loading={isFetchingNextPage}>
        <div className={clsx("grid ", listStyles[productVariant as keyof typeof listStyles])}>
          {productList?.map((product) => (
            <ProductCard data={product} variant={productVariant} key={product.id} />
          ))}
        </div>
      </InfiniteLoader>
    </>
  );
};
