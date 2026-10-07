"use client";

import { Blog, BlogShortTranslation } from "@/types/blog";
import { Paginate } from "@/types/global";
import { blogService } from "@/services/blog";
import { BlogCard } from "@/components/blog-card";
import { InfiniteLoader } from "@/components/infinite-loader";
import { useSettings } from "@/hook/use-settings";
import { extractDataFromPagination } from "@/utils/extract-data";
import { useInfiniteQuery } from "@tanstack/react-query";
import { useTranslation } from "react-i18next";
import { BackButton } from "@/components/back-button";
import { useState } from "react";

interface BlogContentProps {
  initialData?: Paginate<Blog<BlogShortTranslation>>;
  initialLocale: string;
}

export const BlogContent = ({ initialData, initialLocale }: BlogContentProps) => {
  const { language } = useSettings();
  const { t } = useTranslation();
  const locale = language?.locale || initialLocale;
  const localizedInitialData =
    locale === initialLocale ? initialData : undefined;
  const [failedPage, setFailedPage] = useState(1);
  const {
    data,
    hasNextPage,
    fetchNextPage,
    isFetchingNextPage,
    isLoading,
    isError,
    isRefetchError,
    refetch,
  } = useInfiniteQuery(
    ["blogs", locale],
    async ({ pageParam = 1 }) => {
      try {
        return await blogService.getAll(
        { lang: locale, type: "blog", page: pageParam },
        { cache: "no-cache" }
        );
      } catch (error) {
        setFailedPage(Number(pageParam));
        throw error;
      }
    },
    {
      getNextPageParam: (lastPage) =>
        lastPage.links.next ? lastPage.meta.current_page + 1 : undefined,
      initialData: localizedInitialData
        ? { pages: [localizedInitialData], pageParams: [1] }
        : undefined,
    }
  );

  const posts = extractDataFromPagination(data?.pages) ?? [];
  // The native app uses React Query v4, which has no isFetchNextPageError.
  const isFetchNextPageError = isError && failedPage > 1;

  return (
    <main className="aa-s2">
      <section className="border-b border-[#e8e3d9] bg-[#f1ede5] dark:border-gray-bold dark:bg-darkBg">
        <div className="mx-auto max-w-7xl px-4 py-9 md:py-14">
          <p className="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-[#715033] dark:text-amber-200">
            {t("blog")}
          </p>
          <h1 className="max-w-3xl text-3xl font-bold leading-tight text-[#26241f] dark:text-white md:text-5xl">
            {t("news")}
          </h1>
          <p className="mt-3 max-w-2xl text-base leading-relaxed text-[#46433d] dark:text-gray-200">
            Articles and updates published by AgendaAlly.
          </p>
        </div>
      </section>
      <section className="mx-auto max-w-7xl px-4 py-7 md:py-10">
        <BackButton title="blog" />
        {posts.length > 0 && (
          <div className="my-7 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            {posts.map((post, index) => (
              <div key={post.id} className={index === 0 ? "lg:col-span-2" : ""}>
                <BlogCard data={post} detailed locale={locale} featured={index === 0} />
              </div>
            ))}
          </div>
        )}

        {isLoading && posts.length === 0 && (
          <p className="py-12 text-center text-sm text-[#625d54]" role="status">
            Loading articles…
          </p>
        )}
        {isError && posts.length === 0 && (
          <div className="my-8 rounded-2xl border border-[#e8e3d9] bg-white p-6 text-center dark:border-gray-bold dark:bg-darkBgUi3">
            <p className="text-[#26241f] dark:text-white" role="alert" data-testid="status-blog-list-error">
              Blog articles could not be loaded. Please try again.
            </p>
            <button
              className="mt-4 min-h-11 rounded-lg border border-[#d9d1c5] px-4 font-semibold text-[#715033] hover:bg-[#f2e9dd] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#956a42] dark:text-amber-200"
              type="button"
              onClick={() => refetch()}
            >
              Try again
            </button>
          </div>
        )}
        {!isLoading && !isError && posts.length === 0 && (
          <p
            className="my-8 rounded-2xl border border-[#e8e3d9] bg-white p-8 text-center text-[#46433d] dark:border-gray-bold dark:bg-darkBgUi3 dark:text-gray-200"
            role="status"
            data-testid="status-blog-list-empty"
          >
            There are no published articles available in this language yet.
          </p>
        )}
        {isRefetchError && !isFetchNextPageError && posts.length > 0 && (
          <div className="my-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-[#e8e3d9] bg-[#fbfaf7] p-4 dark:border-gray-bold dark:bg-darkBgUi3">
            <p role="alert">New articles could not be checked. The articles already loaded remain available.</p>
            <button className="font-semibold text-[#715033] underline underline-offset-4 dark:text-amber-200" type="button" onClick={() => refetch()}>
              Retry
            </button>
          </div>
        )}
        {posts.length > 0 && (
          <InfiniteLoader
            hasMore={hasNextPage && !isFetchNextPageError}
            loadMore={() => fetchNextPage()}
            loading={isFetchingNextPage}
          >
            {isFetchNextPageError && (
              <div className="my-4 text-center">
                <p role="alert">More articles could not be loaded.</p>
                <button
                  className="mt-2 font-semibold text-[#715033] underline underline-offset-4 dark:text-amber-200"
                  type="button"
                  onClick={() => fetchNextPage()}
                >
                  Try loading more
                </button>
              </div>
            )}
          </InfiniteLoader>
        )}
      </section>
    </main>
  );
};