import fetcher from "@/lib/fetcher";
import { Paginate } from "@/types/global";
import { Blog, BlogShortTranslation } from "@/types/blog";
import { buildUrlQueryParams } from "@/utils/build-url-query-params";
import { cookies } from "next/headers";
import { BlogContent } from "./content";

export const metadata = {
  title: "Blogs",
};

const BlogPage = async () => {
  const lang = (await cookies()).get("lang")?.value || "en";
  let blogs: Paginate<Blog<BlogShortTranslation>> | undefined;
  try {
    blogs = await fetcher<Paginate<Blog<BlogShortTranslation>>>(
      buildUrlQueryParams("v1/rest/blogs/paginate", { lang, type: "blog", page: 1 }),
      {
        cache: "no-cache",
      }
    );
  } catch {
    blogs = undefined;
  }

  return <BlogContent initialData={blogs} initialLocale={lang} />;
};

export default BlogPage;
