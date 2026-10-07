import type { ReactNode, SVGProps } from "react";

type CategoryKey =
  | "hair-care"
  | "nail-care"
  | "spa-massage"
  | "makeup"
  | "barbershop"
  | "skin-care"
  | "tailoring"
  | "dental-care"
  | "healthcare"
  | "handyman"
  | "laundry-dry-cleaning"
  | "home-cleaning"
  | "education"
  | "tattoo-piercing"
  | "car-service"
  | "beauty-personal-care"
  | "tailoring-apparel-supplies";

const aliases: Record<string, CategoryKey> = {
  "hair-care": "hair-care",
  "nail-care": "nail-care",
  "spa-massage": "spa-massage",
  "spa-and-massage": "spa-massage",
  makeup: "makeup",
  barbershop: "barbershop",
  barber: "barbershop",
  "skin-care": "skin-care",
  tailoring: "tailoring",
  "dental-care": "dental-care",
  healthcare: "healthcare",
  handyman: "handyman",
  "laundry-dry-cleaning": "laundry-dry-cleaning",
  "laundry-and-dry-cleaning": "laundry-dry-cleaning",
  "home-cleaning": "home-cleaning",
  education: "education",
  "tattoo-piercing": "tattoo-piercing",
  "tattoo-and-piercing": "tattoo-piercing",
  "car-service": "car-service",
  "beauty-personal-care": "beauty-personal-care",
  "beauty-and-personal-care": "beauty-personal-care",
  "tailoring-apparel-supplies": "tailoring-apparel-supplies",
  "tailoring-and-apparel-supplies": "tailoring-apparel-supplies",
};

// Service root IDs confirmed by the active service-category contract. Resolving
// by ID first keeps the same mark when the translated title changes locale.
const categoryIds: Record<number, CategoryKey> = {
  1: "hair-care",
  4: "nail-care",
  7: "spa-massage",
  10: "makeup",
  13: "barbershop",
  16: "skin-care",
  19: "tailoring",
  24: "dental-care",
  28: "healthcare",
  32: "handyman",
  36: "laundry-dry-cleaning",
  40: "home-cleaning",
  44: "education",
  50: "tattoo-piercing",
};

const normalizeCategory = (value: string): CategoryKey | undefined => {
  const key = value
    .trim()
    .toLowerCase()
    .replace(/&/g, "and")
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-|-$/g, "");
  return aliases[key];
};

const normalizeImageSlug = (value?: string) => {
  const filename = value?.split(/[?#]/)[0].split("/").pop() || "";
  const slug = filename
    .replace(/\.(svg|png|jpe?g|webp)$/i, "")
    .replace(/-(icon|category|pictogram)$/i, "");
  return normalizeCategory(slug);
};

const paths: Record<CategoryKey, ReactNode> = {
  "hair-care": (
    <>
      <path d="M16 48c1-14 2-31 16-34 14 3 15 20 16 34" />
      <path d="M21 47c2-9 4-15 11-16 7 1 9 7 11 16M23 52h18" />
    </>
  ),
  "nail-care": (
    <>
      <path d="M18 48c4 5 9 7 14 7s10-2 14-7l-4-12c-1-3-6-3-7 1l-3 8-3-8c-1-4-6-4-7-1l-4 12Z" />
      <path d="M23 22c0-5 4-9 9-9s9 4 9 9v15H23V22Z" />
    </>
  ),
  "spa-massage": (
    <>
      <path d="M14 45c5-7 11-10 18-10s13 3 18 10M18 52h28" />
      <path d="M32 11c-5 7-5 12 0 17 5-5 5-10 0-17Zm-13 6c-1 7 2 11 9 12-1-7-4-11-9-12Zm26 0c1 7-2 11-9 12 1-7 4-11 9-12Z" />
    </>
  ),
  makeup: (
    <>
      <path d="M21 17h22l-2 37H23l-2-37Zm4-7h14l4 7H21l4-7Z" />
      <path d="M27 28h10m-7 7h4" />
    </>
  ),
  barbershop: (
    <>
      <path d="M18 12h28v40H18zM24 12v40m16-40v40" />
      <path d="m24 19 16 8m-16 8 16 8m-16 0 16-8m-16-8 16-8" />
    </>
  ),
  "skin-care": (
    <>
      <path d="M32 12c-12 0-20 9-20 21 0 12 8 19 20 19s20-7 20-19c0-12-8-21-20-21Z" />
      <path d="M25 29h.1M39 29h.1m-12 9c3 3 7 3 10 0" />
      <path d="M18 18c3-4 8-6 14-6" />
    </>
  ),
  tailoring: (
    <>
      <path d="m21 12 11 8 11-8 10 9-8 10-6-4v25H25V27l-6 4-8-10 10-9Z" />
      <path d="M27 38h10m-5-17v29" />
    </>
  ),
  "dental-care": (
    <>
      <path d="M21 10c-7 0-12 5-12 13 0 6 3 10 5 16 2 7 3 15 7 15 3 0 4-9 7-13 1-2 3-2 4 0 3 4 4 13 7 13 4 0 5-8 7-15 2-6 5-10 5-16 0-8-5-13-12-13-4 0-6 2-9 2s-5-2-9-2Z" />
      <path d="M22 25c2 2 5 2 7 0m6 0c2 2 5 2 7 0" />
    </>
  ),
  healthcare: (
    <>
      <path d="M32 53S11 41 11 26c0-9 12-14 21-4 9-10 21-5 21 4 0 15-21 27-21 27Z" />
      <path d="M19 33h8l5-10 5 18 4-8h5" />
    </>
  ),
  handyman: (
    <>
      <path d="m18 13 10 10-6 6-10-10a13 13 0 0 0 16 16l16 16a5 5 0 0 0 7-7L35 28a13 13 0 0 0-17-15Z" />
      <path d="m43 13 8 8m-4-12 8 8" />
    </>
  ),
  "laundry-dry-cleaning": (
    <>
      <path d="M17 18h30l-3 34H20l-3-34Zm6-5h18m-17 14h16" />
      <path d="M24 39c3-5 6 5 9 0s6 5 9 0" />
    </>
  ),
  "home-cleaning": (
    <>
      <path d="m10 29 22-18 22 18M17 24v28h30V24M27 52V36h10v16" />
      <path d="m48 12 2-5m5 13 5-2" />
    </>
  ),
  education: (
    <>
      <path d="M10 17c9-3 16-1 22 4v31c-6-5-13-7-22-4V17Zm44 0c-9-3-16-1-22 4v31c6-5 13-7 22-4V17Z" />
      <path d="M15 25c5-1 9 0 12 2m-12 6c5-1 9 0 12 2m10-8c4-2 8-3 12-2m-12 8c4-2 8-3 12-2" />
    </>
  ),
  "tattoo-piercing": (
    <>
      <path d="m14 48 32-32 5 5-32 32-5-5Zm25-25 5 5M11 53l-2 3 4-1" />
      <circle cx="48" cy="47" r="7" />
      <path d="m52 42 5-5M32 16l-3-6 5 3 5-2-2 5 3 5-6-2-4 4 2-7Z" />
    </>
  ),
  "car-service": (
    <>
      <path d="m15 33 4-12c.7-2.1 2.1-3 4.4-3h17.2c2.3 0 3.7.9 4.4 3l4 12" />
      <path d="M12 33h40v13H12zM18 46v5m28-5v5" />
      <path d="M17 33h30m-28-7h26" />
      <circle cx="19" cy="39.5" r="2" />
      <circle cx="45" cy="39.5" r="2" />
    </>
  ),
  "beauty-personal-care": (
    <>
      <path d="M20 23h24l-2 30H22l-2-30Zm4-8h16l4 8H20l4-8Z" />
      <path d="M27 31h10m-7-11v-4" />
    </>
  ),
  "tailoring-apparel-supplies": (
    <>
      <path d="M13 20h38v32H13zM22 20v32m20-32v32" />
      <path d="M23 11h18v9H23zm5 18h8m-8 8h8" />
    </>
  ),
};

export interface CategoryPictogramProps extends SVGProps<SVGSVGElement> {
  /** Stable native ID/image slug first, then title; unknown labels use the catalog glyph. */
  category: string;
  categoryId?: number;
  imageRef?: string;
}

/** Locally authored semantic category marks, normalized to one padded 64px frame. */
export const CategoryPictogram = ({
  category,
  categoryId,
  imageRef,
  ...props
}: CategoryPictogramProps) => {
  const key =
    normalizeImageSlug(imageRef) ||
    (categoryId && categoryIds[categoryId]) ||
    normalizeCategory(category);
  return (
    <svg
      viewBox="0 0 64 64"
      fill="none"
      stroke="currentColor"
      strokeWidth="2.4"
      strokeLinecap="round"
      strokeLinejoin="round"
      aria-hidden="true"
      focusable="false"
      {...props}
    >
      {key ? (
        paths[key]
      ) : (
        <>
          <path d="M15 17h34v31H15z" />
          <path d="M22 25h20m-20 7h20m-20 7h13" />
          <path d="m44 44 5 5 7-9" />
        </>
      )}
    </svg>
  );
};

export default CategoryPictogram;