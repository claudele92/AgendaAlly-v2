"use client";

import { DefaultResponse, Term } from "@/types/global";
import { useQuery } from "@tanstack/react-query";
import { infoService } from "@/services/info";
import { useSettings } from "@/hook/use-settings";
import { Translate } from "@/components/translate";
import { LegalPolicyLinks } from "@/components/legal-policy-links";

interface PrivacyContentProps {
  data?: DefaultResponse<Term>;
  initialLocale: string;
}

export const PrivacyContent = ({ data, initialLocale }: PrivacyContentProps) => {
  const { language } = useSettings();
  const locale = language?.locale || initialLocale;
  const {
    data: privacy,
    isError,
    isLoading,
  } = useQuery(
    ["privacy", locale],
    () => infoService.privacy({ lang: locale }),
    {
      initialData: locale === initialLocale ? data : undefined,
    }
  );
  const title = privacy?.data?.translation?.title;
  const description = privacy?.data?.translation?.description;

  return (
    <main className="aa-s2">
      <section className="mx-auto max-w-7xl px-4 py-7 md:py-10">
        <div className="mx-auto max-w-4xl">
        <div className="mb-7 flex flex-wrap items-center justify-between gap-3">
          <p className="text-xs font-bold uppercase tracking-[0.16em] text-[#715033] dark:text-amber-200">
            Information
          </p>
          <LegalPolicyLinks current="privacy" />
        </div>
        <h1 className="mb-6 text-3xl font-bold leading-tight text-[#26241f] dark:text-white md:text-4xl">
          {title || <Translate value="privacy.policy" />}
        </h1>
        {description ? (
          <div
            aria-label="Privacy policy"
            className="break-words text-base leading-8 text-[#39362f] dark:text-gray-100 [&_a]:font-medium [&_a]:text-[#715033] [&_a]:underline [&_a]:underline-offset-4 [&_blockquote]:my-7 [&_blockquote]:border-l-4 [&_blockquote]:border-[#956a42] [&_blockquote]:pl-5 [&_h2]:mb-3 [&_h2]:mt-9 [&_h2]:text-2xl [&_h2]:font-semibold [&_h2:first-child]:mt-0 [&_h3]:mb-2 [&_h3]:mt-7 [&_h3]:text-xl [&_h3]:font-semibold [&_li]:mb-2 [&_ol]:my-5 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mb-5 [&_p:last-child]:mb-0 [&_ul]:my-5 [&_ul]:list-disc [&_ul]:pl-6"
            dangerouslySetInnerHTML={{ __html: description }}
          />
        ) : (
          <p
            className="rounded-2xl border border-[#e8e3d9] bg-[#fffefa] p-6 text-[#46433d] dark:border-gray-bold dark:bg-darkBgUi3 dark:text-gray-200"
            role={isError ? "alert" : "status"}
            data-testid={isError ? "status-privacy-error" : "status-privacy-unavailable"}
          >
            {isError
              ? "Privacy information could not be loaded. Please try again."
              : isLoading
              ? "Loading Privacy Policy…"
              : "Privacy information is not available in this language."}
          </p>
        )}
        </div>
      </section>
    </main>
  );
};
