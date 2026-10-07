import React from "react";
import { blogService } from "@/services/blog";
import { Blog, BlogFullTranslation } from "@/types/blog";
import { DefaultResponse } from "@/types/global";
import { cookies } from "next/headers";
import { notFound } from "next/navigation";
import dynamic from "next/dynamic";
import { Metadata } from "next";
import { BackButton } from "@/components/back-button";
import ReviewList from "@/app/(store)/(booking)/components/reviews/review-list";
import ReviewSummaryShort from "@/app/(store)/(booking)/components/reviews/review-summary-short";
import NetworkError from "@/utils/network-error";
import { ImageWithFallBack } from "@/components/image";
import Link from "next/link";
import { formatBlogDate } from "@/components/blog-card/format-date";

const CreateReview = dynamic(() => import("../components/blog-review-create"));
type BlogResponse = DefaultResponse<Blog<BlogFullTranslation>>;

const isNextRedirectError = (error: unknown): error is Error & { digest: string } =>
  error instanceof Error &&
  "digest" in error &&
  typeof error.digest === "string" &&
  error.digest.startsWith("NEXT_REDIRECT");

export const generateMetadata = async (
  props: {
    params: Promise<{ id: string }>;
  }
): Promise<Metadata> => {
  const params = await props.params;
  const lang = (await cookies()).get("lang")?.value || "en";
  let blog: BlogResponse | undefined;
  try {
    blog = await blogService.get(params.id, { lang }, { redirectOnError: false });
  } catch (error) {
    if (error instanceof NetworkError && error.statusCode === 404) {
      notFound();
    }
    if (isNextRedirectError(error)) {
      throw error;
    }
    return { title: "Blog" };
  }
  return {
    title: blog.data.translation?.title || "Blog",
    description: blog.data.translation?.short_desc,
    openGraph: {
      title: blog.data.translation?.title,
      description: blog.data.translation?.short_desc,
      images: blog.data.img ? [{ url: blog.data.img }] : [],
    },
  };
};

const BlogDetailPage = async (props: { params: Promise<{ id: string }> }) => {
  const params = await props.params;
  const lang = (await cookies()).get("lang")?.value || "en";
  let blog: BlogResponse | undefined;
  let loadFailed = false;
  try {
    blog = await blogService.get(params.id, { lang }, { redirectOnError: false });
  } catch (error) {
    if (error instanceof NetworkError && error.statusCode === 404) {
      notFound();
    }
    if (isNextRedirectError(error)) {
      throw error;
    }
    loadFailed = true;
  }

  const article = blog?.data;
  const translation = article?.translation;
  const author =
    article?.author?.full_name?.trim() ||
    [article?.author?.firstname, article?.author?.lastname].filter(Boolean).join(" ").trim();
  const publicationDate = formatBlogDate(article?.published_at, translation?.locale || lang);

  return (
    <main className="aa-s2">
      <section className="mx-auto max-w-7xl px-4 py-7 md:py-10">
        <BackButton title="blog" />
        {loadFailed || !article ? (
          <div className="mx-auto my-10 max-w-2xl rounded-2xl border border-[#e8e3d9] bg-[#fffefa] p-6 dark:border-gray-bold dark:bg-darkBgUi3">
            <p role="alert" data-testid="status-blog-detail-error">
              The article could not be loaded right now. Please try again.
            </p>
            <Link
              href="/blogs"
              className="mt-4 inline-flex min-h-11 items-center font-semibold text-[#715033] underline underline-offset-4 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#956a42] dark:text-amber-200"
            >
              Browse articles
            </Link>
          </div>
        ) : (
          <>
            <article className="mt-7">
            <header className="mx-auto max-w-3xl py-5 md:py-9" lang={translation?.locale || lang}>
              <h1 className="text-3xl font-bold leading-tight text-[#26241f] dark:text-white md:text-5xl">
                {translation?.title || "Article"}
              </h1>
              {translation?.short_desc && (
                <p className="mt-4 text-base leading-relaxed text-[#46433d] dark:text-gray-200 md:text-lg">
                  {translation.short_desc}
                </p>
              )}
              {(publicationDate || author) && (
                <div className="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-[#625d54] dark:text-gray-300">
                  {publicationDate && (
                    <time dateTime={article.published_at}>{publicationDate}</time>
                  )}
                  {author && <span>By {author}</span>}
                </div>
              )}
            </header>
            {article.img && (
              <div className="relative mx-auto my-5 aspect-[2.15/1] max-h-[510px] overflow-hidden rounded-2xl bg-[#f2eee6]">
                <ImageWithFallBack
                  src={article.img}
                  alt={translation?.title || ""}
                  className="object-cover"
                  fill
                  sizes="(max-width: 768px) 100vw, 1200px"
                />
              </div>
            )}
            {translation?.description ? (
              <div
                dangerouslySetInnerHTML={{ __html: translation.description }}
                className="mx-auto max-w-3xl py-5 text-base leading-8 text-[#39362f] dark:text-gray-100 md:py-8 [&_a]:font-medium [&_a]:text-[#715033] [&_a]:underline [&_a]:underline-offset-4 [&_blockquote]:my-7 [&_blockquote]:border-l-4 [&_blockquote]:border-[#956a42] [&_blockquote]:pl-5 [&_h2]:mb-3 [&_h2]:mt-9 [&_h2]:text-2xl [&_h2]:font-semibold [&_h2:first-child]:mt-0 [&_h3]:mb-2 [&_h3]:mt-7 [&_h3]:text-xl [&_h3]:font-semibold [&_li]:mb-2 [&_ol]:my-5 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mb-5 [&_p:last-child]:mb-0 [&_ul]:my-5 [&_ul]:list-disc [&_ul]:pl-6"
              />
            ) : (
              <p
                className="mx-auto max-w-3xl py-5 text-[#46433d] dark:text-gray-200"
                role="status"
                data-testid="status-blog-detail-empty"
              >
                The article text is not available in this language.
              </p>
            )}
            </article>
            <div className="grid grid-cols-1 gap-7 border-t border-[#e8e3d9] pt-7 dark:border-gray-bold lg:grid-cols-3">
              <div className="lg:col-span-2">
                <ReviewList title="comments" type="blogs" id={String(article.id)} />
              </div>
              <aside className="lg:col-span-1">
                <div className="lg:sticky lg:top-5">
                  <ReviewSummaryShort type="blogs" typeId={article.id} />
                  <CreateReview id={article.id} />
                </div>
              </aside>
            </div>
          </>
        )}
      </section>
    </main>
  );
};

export default BlogDetailPage;
