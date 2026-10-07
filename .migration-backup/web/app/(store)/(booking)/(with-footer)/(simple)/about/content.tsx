"use client";

import { Page, Paginate } from "@/types/global";
import { useQuery } from "@tanstack/react-query";
import { useSettings } from "@/hook/use-settings";
import { infoService } from "@/services/info";
import { ImageWithFallBack } from "@/components/image";
import clsx from "clsx";

interface AboutPageContentProps {
  initialData?: Paginate<Page>;
  initialLocale: string;
}

export const AboutPageContent = ({ initialData, initialLocale }: AboutPageContentProps) => {
  const { language } = useSettings();
  const locale = language?.locale || initialLocale;
  const { data, isError, isLoading } = useQuery(
    ["about", locale],
    () =>
      infoService.getPages({
        type: "all_about",
        lang: locale,
      }),
    {
      initialData: locale === initialLocale ? initialData : undefined,
    }
  );

  const pages = data?.data ?? [];
  const mainSection = pages.find((page) => page.type === "about");
  const otherSections = pages.filter((page) => page.type !== "about");

  return (
    <main className="aa-s2">
      <section className="mx-auto max-w-7xl px-4 py-7 md:py-10">
      {mainSection && (
        <article className="grid overflow-hidden rounded-3xl border border-[#e8e3d9] bg-[#fffefa] dark:border-gray-bold dark:bg-darkBgUi3 md:grid-cols-2">
          {mainSection.img && (
            <div className="relative min-h-[220px] aspect-[1.6/1] bg-[#f2eee6] md:aspect-auto md:min-h-[380px]">
              <ImageWithFallBack
                src={mainSection.img}
                alt={mainSection.translation?.title || "About AgendaAlly"}
                fill
                className="object-cover"
                sizes="(max-width: 768px) 100vw, 50vw"
              />
            </div>
          )}
          <div className="flex min-w-0 flex-col justify-center p-5 md:p-9">
            <p className="mb-3 text-xs font-bold uppercase tracking-[0.16em] text-[#715033] dark:text-amber-200">
              About
            </p>
            <h1 className="text-2xl font-bold leading-tight text-[#26241f] dark:text-white md:text-4xl">
              {mainSection.translation?.title}
            </h1>
            {mainSection.translation?.description && (
              <div
                dangerouslySetInnerHTML={{ __html: mainSection.translation?.description || "" }}
                className="mt-5 text-base leading-relaxed text-[#46433d] dark:text-gray-200 [&_a]:font-medium [&_a]:text-[#715033] [&_a]:underline [&_a]:underline-offset-4 [&_h2]:mb-2 [&_h2]:mt-6 [&_h2]:text-xl [&_h2]:font-semibold [&_li]:mb-2 [&_ol]:my-4 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mb-4 [&_p:last-child]:mb-0 [&_ul]:my-4 [&_ul]:list-disc [&_ul]:pl-6"
              />
            )}
          </div>
        </article>
      )}
      {isError && pages.length === 0 ? (
        <p
          className="my-7 rounded-2xl border border-[#e8e3d9] bg-[#fffefa] p-6 text-[#46433d] dark:border-gray-bold dark:bg-darkBgUi3 dark:text-gray-200"
          role="alert"
          data-testid="status-about-error"
        >
          About information could not be loaded. Please try again.
        </p>
      ) : isLoading && pages.length === 0 ? (
        <p className="my-7 text-[#46433d] dark:text-gray-200" role="status">
          Loading about information…
        </p>
      ) : !isError && pages.length === 0 ? (
        <p
          className="my-7 rounded-2xl border border-[#e8e3d9] bg-[#fffefa] p-6 text-[#46433d] dark:border-gray-bold dark:bg-darkBgUi3 dark:text-gray-200"
          role="status"
          data-testid="status-about-unavailable"
        >
          About information is not available in this language.
        </p>
      ) : null}
      {otherSections?.map((section, i) => (
        <div
          className={clsx(
            "my-5 grid overflow-hidden rounded-3xl border border-[#e8e3d9] bg-[#fffefa] dark:border-gray-bold dark:bg-darkBgUi3 md:grid-cols-2",
            i % 2 !== 0 && "md:[&>div:first-child]:order-2"
          )}
          key={section.id}
        >
          {section.img && (
            <div className="relative min-h-[200px] aspect-[1.6/1] bg-[#f2eee6] md:aspect-auto md:min-h-[300px]">
              <ImageWithFallBack
                src={section.img}
                alt={section.translation?.title || ""}
                fill
                className="object-cover"
                sizes="(max-width: 768px) 100vw, 50vw"
              />
            </div>
          )}
          <div className="flex min-w-0 flex-col justify-center p-5 md:p-8">
            <p className="mb-2 text-xs font-bold uppercase tracking-[0.16em] text-[#715033] dark:text-amber-200">
              About AgendaAlly
            </p>
            <h2 className="text-xl font-semibold leading-tight text-[#26241f] dark:text-white md:text-2xl">
              {section.translation?.title}
            </h2>
            {section.translation?.description && (
              <div
                dangerouslySetInnerHTML={{ __html: section.translation.description }}
                className="mt-4 text-base leading-relaxed text-[#46433d] dark:text-gray-200 [&_a]:font-medium [&_a]:text-[#715033] [&_a]:underline [&_a]:underline-offset-4 [&_h2]:mb-2 [&_h2]:mt-6 [&_h2]:text-xl [&_h2]:font-semibold [&_li]:mb-2 [&_ol]:my-4 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mb-4 [&_p:last-child]:mb-0 [&_ul]:my-4 [&_ul]:list-disc [&_ul]:pl-6"
              />
            )}
          </div>
        </div>
      ))}
      </section>
    </main>
  );
};
