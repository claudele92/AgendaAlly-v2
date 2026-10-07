import React from "react";
import ReviewList from "@/app/(store)/(booking)/components/reviews/review-list";
import ReviewSummary from "@/app/(store)/(booking)/components/reviews/review-summary";
import { Translate } from "@/components/translate";
import ShopReviewCreate from "./review-create";

interface ShopReviewProps {
  id?: number;
  reviewAvg?: number;
  reviewCount?: number;
}

const ShopReviewPanel = ({ id, reviewAvg, reviewCount }: ShopReviewProps) => (
  <div className="border border-gray-link rounded-button col-span-2 py-6 px-5">
    <h2 className="text-xl font-semibold">
      <Translate value="reviews" />
    </h2>
    {reviewCount != null && reviewCount > 0 && reviewAvg != null ? (
      <div className="mt-6 p-5 rounded-button border border-gray-link">
        <ReviewSummary type="shops" typeId={id} reviewAvg={reviewAvg} reviewCount={reviewCount} />
      </div>
    ) : (
      <p className="aa-s2-profile-empty">
        No public rating summary is available for this business.
      </p>
    )}
    <div>
      <ReviewList type="shops" id={id?.toString()} />
      <ShopReviewCreate id={id} />
    </div>
  </div>
);

export default ShopReviewPanel;
