"use client";

import { Paginate } from "@/types/global";
import { Faq } from "@/types/info";
import { useInfiniteQuery } from "@tanstack/react-query";
import { infoService } from "@/services/info";
import React, { useState } from "react";
import { extractDataFromPagination } from "@/utils/extract-data";
import { InfiniteLoader } from "@/components/infinite-loader";
import { useTranslation } from "react-i18next";
import { useSettings } from "@/hook/use-settings";
import { Qa } from "./components/qa";

interface HelpContentProps {
  data?: Paginate<Faq>;
  initialLocale: string;
}

export const HelpContent = ({ data, initialLocale }: HelpContentProps) => {
  const { language } = useSettings();
  const { t } = useTranslation();
  const locale = language?.locale || initialLocale;
  const localizedInitialData = locale === initialLocale ? data : undefined;
  const [failedPage, setFailedPage] = useState(1);
  const {
    data: faqs,
    hasNextPage,
    fetchNextPage,
    isFetchingNextPage,
    isError,
    isLoading,
    isRefetchError,
    refetch,
  } = useInfiniteQuery(
    ["faq", locale],
    async ({ pageParam = 1 }) => {
      try {
        return await infoService.faq({ lang: locale, page: pageParam });
      } catch (error) {
        setFailedPage(Number(pageParam));
        throw error;
      }
    },
    {
      getNextPageParam: (lastPage) => lastPage.links.next && lastPage.meta.current_page + 1,
      initialData: localizedInitialData
        ? { pages: [localizedInitialData], pageParams: [1] }
        : undefined,
    }
  );

  const faqList = extractDataFromPagination(faqs?.pages);
  const isFetchNextPageError = isError && failedPage > 1;

  return (
    <main className="aa-s2">
      <header className="border-b border-[#e8e3d9] bg-[#f1ede5] dark:border-gray-bold dark:bg-darkBg">
        <div className="mx-auto max-w-7xl px-4 py-8 md:py-12">
          <p className="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-[#715033] dark:text-amber-200">
            Help
          </p>
          <h1 className="text-3xl font-bold text-[#26241f] dark:text-white md:text-4xl">
            {t("faq")}
          </h1>
        </div>
      </header>
      <section className="mx-auto max-w-4xl px-4 py-7 md:py-10">
        <InfiniteLoader
          hasMore={hasNextPage && !isFetchNextPageError}
          loadMore={fetchNextPage}
          loading={isFetchingNextPage}
        >
          <div className="flex w-full flex-col gap-3">
          {faqList?.map((faq) => (
            <Qa data={faq} key={faq.id} />
          ))}
          {(!faqList || faqList.length === 0) && isError && (
            <p
              className="rounded-2xl border border-[#e8e3d9] bg-[#fffefa] p-6 text-[#46433d] dark:border-gray-bold dark:bg-darkBgUi3 dark:text-gray-200"
              role="alert"
              data-testid="status-faq-error"
            >
              Help answers could not be loaded. Please try again.
            </p>
          )}
          {(!faqList || faqList.length === 0) && isLoading && (
            <p className="py-10 text-center text-[#46433d] dark:text-gray-200" role="status">
              Loading help answers…
            </p>
          )}
          {(!faqList || faqList.length === 0) && !isLoading && !isError && (
            <p
              className="rounded-2xl border border-[#e8e3d9] bg-[#fffefa] p-6 text-[#46433d] dark:border-gray-bold dark:bg-darkBgUi3 dark:text-gray-200"
              role="status"
              data-testid="status-faq-unavailable"
            >
              There are no published help answers in this language yet.
            </p>
          )}
          {faqList && faqList.length > 0 && isRefetchError && !isFetchNextPageError && (
            <div className="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-[#e8e3d9] bg-[#fbfaf7] p-4 dark:border-gray-bold dark:bg-darkBgUi3">
              <p className="text-sm text-[#8b463d]" role="alert">
                New help answers could not be checked. Already loaded answers remain available.
              </p>
              <button
                className="min-h-11 font-semibold text-[#715033] underline underline-offset-4 dark:text-amber-200"
                type="button"
                onClick={() => refetch()}
              >
                Try again
              </button>
            </div>
          )}
          {isFetchNextPageError && (
            <div className="mt-4 text-center">
              <p className="text-sm text-[#8b463d]" role="alert">
                More help answers could not be loaded.
              </p>
              <button
                className="mt-2 min-h-11 font-semibold text-[#715033] underline underline-offset-4 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#956a42] dark:text-amber-200"
                type="button"
                onClick={() => fetchNextPage()}
              >
                Try loading more
              </button>
            </div>
          )}
          </div>
        </InfiniteLoader>
      </section>
    </main>
  );
};
