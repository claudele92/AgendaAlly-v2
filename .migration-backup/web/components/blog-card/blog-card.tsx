import ReviewIcon from "@/assets/icons/review";
import { Blog, BlogShortTranslation } from "@/types/blog";
import React from "react";
import { ImageWithFallBack } from "@/components/image";
import { formatBlogDate } from "./format-date";

interface BlogCardProps {
  data: Blog<BlogShortTranslation>;
  detailed?: boolean;
  featured?: boolean;
  locale?: string;
}

export const BlogCard = ({ data, detailed, featured = false, locale }: BlogCardProps) => {
  const title = data.translation?.title?.trim() || "Article title unavailable";
  const imageAlt = data.translation?.title?.trim() || "";
  const date = formatBlogDate(data.published_at, data.translation?.locale || locale);
  const author =
    data.author?.full_name?.trim() ||
    [data.author?.firstname, data.author?.lastname].filter(Boolean).join(" ").trim();

  return (
    <a
      href={`/blogs/${data.uuid || data.id}`}
      aria-label={title ? `Read article: ${title}` : "Read article"}
      className="group block h-full rounded-2xl focus-visible:outline focus-visible:outline-3 focus-visible:outline-offset-4 focus-visible:outline-[#956a42]"
    >
      <article
        className={`grid h-full overflow-hidden rounded-2xl border border-[#e8e3d9] bg-[#fffefa] transition-colors hover:border-[#b79a7a] dark:border-gray-bold dark:bg-darkBgUi3 ${
          featured && data.img ? "lg:grid-cols-2" : ""
        }`}
      >
        {data.img && (
          <div
            className={`relative aspect-[1.55/1] overflow-hidden bg-[#f2eee6] ${
              featured ? "lg:aspect-auto lg:min-h-[300px]" : ""
            }`}
          >
            <ImageWithFallBack
              src={data.img}
              alt={imageAlt}
              fill
              className="object-cover transition-transform duration-300 group-hover:scale-[1.02]"
              sizes={featured ? "(max-width: 1024px) 100vw, 50vw" : "(max-width: 768px) 100vw, 33vw"}
            />
          </div>
        )}
        <div className={`flex min-w-0 flex-1 flex-col p-5 ${featured ? "md:p-7 lg:justify-center" : ""}`}>
          <h2
            className={`font-semibold leading-snug text-[#26241f] group-hover:text-[#715033] dark:text-white dark:group-hover:text-amber-200 ${
              featured ? "text-xl md:text-2xl" : "text-lg"
            }`}
          >
            {title}
          </h2>
          {detailed && data.translation?.short_desc && (
            <p className="mt-3 line-clamp-4 text-sm leading-relaxed text-[#46433d] dark:text-gray-200">
              {data.translation.short_desc}
            </p>
          )}
          <div className="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-[#625d54] dark:text-gray-300">
            {date && (
              <time dateTime={data.published_at} className="font-medium">
                {date}
              </time>
            )}
            {author && <span>By {author}</span>}
            {typeof data.r_count === "number" && (
              <span className="inline-flex items-center gap-1.5">
                <ReviewIcon />
                <span>{data.r_count}</span>
              </span>
            )}
          </div>
          <span className="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-[#715033] underline decoration-transparent underline-offset-4 transition group-hover:decoration-current dark:text-amber-200">
            Read article
          </span>
        </div>
      </article>
    </a>
  );
};
