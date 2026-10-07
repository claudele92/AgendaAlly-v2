import { Brand } from "@/types/brand";
import { ImageWithFallBack } from "@/components/image";
import Image from "next/image";
import clsx from "clsx";
import { useTranslation } from "react-i18next";

interface BrandCardProps {
  data?: Brand;
  selected?: boolean;
  presentation?: "discovery";
}

export const BrandCard = ({ data, selected, presentation }: BrandCardProps) => {
  const { t } = useTranslation();
  const productCount = Number(data?.products_count);
  const hasProductCount = Number.isFinite(productCount) && productCount >= 0;
  if (presentation !== "discovery") {
    return (
      <div
        className={clsx(
          "aa-s2-discovery-card aa-s2-brand-card py-5 px-4 flex items-center gap-3 rounded-button border border-gray-link",
          selected && "bg-primary"
        )}
      >
        <div className="relative w-20 h-20 rounded-full">
          <Image
            src={data?.img || "/img/image-load-failed.png"}
            alt={data?.title || "brand"}
            className="rounded-full object-contain"
            fill
          />
        </div>
        <div>
          <div className="text-lg font-medium">{data?.title}</div>
        </div>
      </div>
    );
  }
  return (
    <div
      className={clsx(
        "aa-s2-discovery-card aa-s2-brand-card aa-s2-home-brand-card rounded-button border border-gray-link",
        selected && "bg-primary"
      )}
    >
      <div className="aa-s2-home-brand-image">
        <ImageWithFallBack
          src={data?.img}
          alt={data?.title ? `${data.title} brand` : "Brand"}
          fill
          sizes="(max-width: 700px) 78vw, 270px"
          className="object-contain"
        />
      </div>
      <div className="aa-s2-home-brand-copy">
        <div className="text-base font-semibold">{data?.title}</div>
        {hasProductCount && (
          <span className="aa-s2-home-brand-count">
            {productCount} {t("products")}
          </span>
        )}
      </div>
    </div>
  );
};

export const BrandCardLoading = () => (
  <div className="py-5 px-4 flex items-center gap-3 rounded-button border border-gray-link">
    <div className="w-20 h-20 rounded-full bg-gray-300" />
    <div className="w-20">
      <div className="h-4 w-full rounded-full bg-gray-300 mb-2" />
      <div className="h-4 w-10/12 rounded-full bg-gray-300" />
    </div>
  </div>
);
