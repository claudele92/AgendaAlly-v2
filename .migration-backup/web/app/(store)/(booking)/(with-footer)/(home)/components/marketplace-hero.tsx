"use client";

import { FormEvent, useState } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useQuery } from "@tanstack/react-query";
import { useTranslation } from "react-i18next";
import { useSettings } from "@/hook/use-settings";
import useAddressStore from "@/global-store/address";
import { productService } from "@/services/product";
import { Category } from "@/types/category";
import { getCategoryHierarchy } from "@/utils/category-hierarchy";
import { SearchProduct } from "@/types/product";
import { ImageWithFallBack } from "@/components/image";
import { Empty } from "@/components/empty";
import ErrorFallback from "@/components/error-fallback";
import SearchIcon from "@/assets/icons/search";
import { buildUrlQueryParams } from "@/utils/build-url-query-params";

interface MarketplaceHeroProps {
  serviceCategories: Category[];
  productsEnabled: boolean;
}

const ProductSearchResults = ({
  search,
  categoryId,
}: {
  search: string;
  categoryId: string;
}) => {
  const { language, currency } = useSettings();
  const { t } = useTranslation();
  const { data, isLoading, isError, refetch } = useQuery(
    ["homepage-product-search", search, categoryId, language?.locale, currency?.id],
    () =>
      productService.search({
        search: search || undefined,
        category_id: categoryId || undefined,
        perPage: 6,
        lang: language?.locale,
        currency_id: currency?.id,
      }),
    { enabled: Boolean(search || categoryId) }
  );

  if (isLoading) {
    return (
      <div className="mt-4 grid gap-2 sm:grid-cols-2" aria-live="polite" aria-busy="true">
        {Array.from({ length: 4 }, (_, index) => (
          <div className="h-[72px] animate-pulse rounded-lg bg-[#f2eee6]" key={index} />
        ))}
      </div>
    );
  }

  if (isError) {
    return (
      <div className="mt-4 rounded-lg border border-[#e8e3d9] bg-[#fffefa] p-4">
        <ErrorFallback />
        <button
          className="aa-s2-btn aa-s2-btn-secondary mt-3"
          onClick={() => void refetch()}
          type="button"
        >
          Try again
        </button>
      </div>
    );
  }

  const products = data?.data || [];
  if (!products.length) {
    return <Empty animated={false} text="no.products.found" />;
  }

  return (
    <div className="mt-4">
      <div className="mb-2 flex items-center justify-between gap-3">
        <span className="text-sm font-semibold">Product search results</span>
        <Link className="text-sm font-semibold underline underline-offset-4" href="/products">
          Browse all products
        </Link>
      </div>
      <ul className="grid gap-2 sm:grid-cols-2">
        {products.map((product: SearchProduct) => (
          <li key={product.id}>
            <Link
              className="flex min-h-[76px] items-center gap-3 rounded-lg border border-[#e8e3d9] bg-[#fffefa] p-2 transition-colors hover:border-[#b79a7a]"
              href={`/products/${product.uuid}`}
            >
              <ImageWithFallBack
                src={product.img}
                alt=""
                width={56}
                height={56}
                className="h-14 w-14 rounded-md object-cover"
              />
              <span className="min-w-0">
                <span className="line-clamp-2 block text-sm font-semibold">
                  {product.translation?.title || t("products")}
                </span>
                {product.shop?.translation?.title && (
                  <span className="aa-s2-product-seller block">
                    Sold by <span>{product.shop.translation.title}</span>
                  </span>
                )}
              </span>
            </Link>
          </li>
        ))}
      </ul>
    </div>
  );
};

export const MarketplaceHero = ({
  serviceCategories,
  productsEnabled,
}: MarketplaceHeroProps) => {
  const router = useRouter();
  const { language } = useSettings();
  const country = useAddressStore((state) => state.country);
  const city = useAddressStore((state) => state.city);
  const hasHydrated = useAddressStore((state) => state.hasHydrated);
  const [domain, setDomain] = useState<"services" | "products">("services");
  const [serviceCategoryId, setServiceCategoryId] = useState("");
  const [productSearch, setProductSearch] = useState("");
  const [productCategoryId, setProductCategoryId] = useState("");
  const serviceCategoryRoots = getCategoryHierarchy(serviceCategories);
  const [submittedSearch, setSubmittedSearch] = useState("");
  const [submittedCategoryId, setSubmittedCategoryId] = useState("");
  const productCategories = useQuery(
    ["homepage-product-categories", "category", language?.locale],
    () => productService.filters({ type: "category", lang: language?.locale }),
    { enabled: productsEnabled }
  );

  const locationLabel = [city?.translation?.title, country?.translation?.title]
    .filter(Boolean)
    .join(", ");

  const submitServiceSearch = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    router.push(
      buildUrlQueryParams("/search", {
        category_id: serviceCategoryId || undefined,
      })
    );
  };

  const submitProductSearch = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const search = productSearch.trim();
    if (!search && !productCategoryId) {
      router.push("/products");
      return;
    }
    setSubmittedSearch(search);
    setSubmittedCategoryId(productCategoryId);
  };

  const productCategoryList = productCategories.data?.categories || [];

  return (
    <section className="aa-s2-hero">
      <div className="aa-s2-wrap aa-s2-hero-grid">
        <div className="aa-s2-reveal">
          <span className="aa-s2-eyebrow">
            {hasHydrated && locationLabel
              ? locationLabel
              : hasHydrated
              ? "Location not set"
              : "Loading location"}
          </span>
          <h1>
            Book local expertise.{" "}
            <span style={{ color: "var(--bronze)" }}>Shop local businesses.</span>
          </h1>
          <p className="aa-s2-muted" style={{ fontSize: 16, maxWidth: 525, margin: 0 }}>
            Find skilled local professionals and independent businesses. Start with a service or
            browse the separate product catalog.
          </p>
          <div className="aa-s2-domain-tabs" role="group" aria-label="Choose what to discover">
            <button
              aria-pressed={domain === "services"}
              className="aa-s2-domain-tab"
              onClick={() => setDomain("services")}
              type="button"
            >
              Services
            </button>
            {productsEnabled && (
              <button
                aria-pressed={domain === "products"}
                className="aa-s2-domain-tab"
                onClick={() => setDomain("products")}
                type="button"
              >
                Products
              </button>
            )}
          </div>

          {domain === "services" ? (
            <form className="aa-s2-searchbox" onSubmit={submitServiceSearch}>
              <label className="aa-s2-searchscope" htmlFor="aa-s2-service-category">
                <SearchIcon size={16} aria-hidden="true" />
                Services
              </label>
              <select
                className="aa-s2-select-control min-w-0 flex-1 border-0 bg-transparent"
                id="aa-s2-service-category"
                onChange={(event) => setServiceCategoryId(event.target.value)}
                style={{
                  width: "auto",
                  minWidth: 0,
                  flex: 1,
                  border: 0,
                  background: "transparent",
                }}
                value={serviceCategoryId}
              >
                <option value="">All service categories</option>
                {serviceCategoryRoots.map(({ category, title }) => (
                  <option key={category.id} value={category.id}>
                    {title}
                  </option>
                ))}
              </select>
              <button className="aa-s2-btn aa-s2-btn-primary" type="submit">
                <SearchIcon size={16} aria-hidden="true" />
                <span>Search</span>
              </button>
            </form>
          ) : (
            <>
              <form className="aa-s2-searchbox" onSubmit={submitProductSearch}>
                <label className="aa-s2-searchscope" htmlFor="aa-s2-product-search">
                  <SearchIcon size={16} aria-hidden="true" />
                  Products
                </label>
                <input
                  autoComplete="off"
                  id="aa-s2-product-search"
                  onChange={(event) => setProductSearch(event.target.value)}
                  placeholder="Search product names"
                  type="search"
                  value={productSearch}
                />
                <button className="aa-s2-btn aa-s2-btn-primary" type="submit">
                  <SearchIcon size={16} aria-hidden="true" />
                  <span>Search</span>
                </button>
              </form>
              <label className="mt-3 block max-w-[420px] text-sm font-semibold" htmlFor="aa-s2-product-category">
                Product category
              </label>
              <select
                className="aa-s2-select-control max-w-[420px]"
                id="aa-s2-product-category"
                onChange={(event) => setProductCategoryId(event.target.value)}
                value={productCategoryId}
              >
                <option value="">All product categories</option>
                {productCategoryList.map((category) => (
                  <option key={category.id} value={category.id}>
                    {category.title}
                  </option>
                ))}
              </select>
              {productCategories.isLoading && (
                <p className="mt-2 text-sm text-[#77746d]" role="status">
                  Loading product categories…
                </p>
              )}
              {productCategories.isError && (
                <p className="mt-2 text-sm text-[#9a4d42]" role="alert">
                  Product categories could not be loaded. Product-name search is still available.
                </p>
              )}
              <p className="aa-s2-muted mt-2 text-xs">
                Product search is catalog-wide; the current city is not used as a product
                proximity filter.
              </p>
              {!!(submittedSearch || submittedCategoryId) && (
                <ProductSearchResults
                  categoryId={submittedCategoryId}
                  search={submittedSearch}
                />
              )}
            </>
          )}
          <p className="mt-4 text-xs text-[#77746d]" aria-live="polite">
            {hasHydrated && locationLabel
              ? `Current browsing context: ${locationLabel}. Change country or city using the location control in the header.`
              : "Choose a country and city using the location control in the header."}
          </p>
        </div>
        <div className="aa-s2-hero-visual aa-s2-reveal-delay">
          <ImageWithFallBack
            alt="Illustrative, AI-generated image of a local business owner welcoming a customer"
            className="object-cover"
            fill
            priority
            sizes="(max-width: 700px) 100vw, 46vw"
            src="/stage2/stage2-local-business.jpg"
          />
          <span className="absolute bottom-3 left-3 rounded-md bg-[#fffefaed] px-3 py-2 text-[11px] font-semibold">
            Illustrative / AI-generated image
          </span>
        </div>
      </div>
    </section>
  );
};