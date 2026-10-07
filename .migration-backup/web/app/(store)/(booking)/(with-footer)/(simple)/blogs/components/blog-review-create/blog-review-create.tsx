"use client";

import { useMutation, useQueryClient } from "@tanstack/react-query";
import { ReviewCreateFormValues, ReviewPermission } from "@/types/review";
import { reviewService } from "@/services/review";
import { useCallback } from "react";
import { DefaultResponse } from "@/types/global";
import dynamic from "next/dynamic";
import useUserStore from "@/global-store/user";
import { useTranslation } from "react-i18next";
import Link from "next/link";

const ReviewCreate = dynamic(
  () => import("@/app/(store)/(booking)/components/reviews/create-review")
);

interface ProductReviewCreateProps {
  id?: number;
}

export const BlogReviewCreate = ({ id }: ProductReviewCreateProps) => {
  const { t } = useTranslation();
  const queryClient = useQueryClient();
  const user = useUserStore((state) => state.user);
  const { mutate, isLoading } = useMutation({
    mutationFn: (body: ReviewCreateFormValues) =>
      reviewService.createReview("blogs", id, { ...body, type: "blog" }),
    onSuccess: () => {
      queryClient.invalidateQueries(["reviewList", String(id), "blogs"]);
      queryClient.invalidateQueries(["groupRating", "blogs", id]);
      queryClient.setQueryData<DefaultResponse<ReviewPermission> | undefined>(
        ["canReview", "blog", id],
        (old) => {
          if (!old)
            return {
              data: { ordered: true, added_review: true },
              message: "success",
              status: true,
              timestamp: "",
            };
          return { ...old, data: { ordered: true, added_review: true } };
        }
      );
    },
  });

  const handleSubmit = useCallback((body: ReviewCreateFormValues) => {
    mutate(body);
  }, []);

  if (!user) {
    return (
      <div className="my-4 flex items-center justify-between gap-4 border border-gray-border dark:border-gray-bold rounded-2xl p-5">
        <span className="text-sm font-medium">{t("please.login.first")}</span>
        <Link href="/login" className="text-sm font-semibold underline shrink-0">
          {t("login")}
        </Link>
      </div>
    );
  }

  return (
    <ReviewCreate
      type="blog"
      isProduct={false}
      typeId={id}
      withImageUpload={false}
      withCategories={false}
      onSubmit={handleSubmit}
      isSubmitting={isLoading}
    />
  );
};

export default BlogReviewCreate;
