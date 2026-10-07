import Image from "next/image";

const editorialImages = {
  business: {
    src: "/stage2/stage2-local-business.jpg",
    alt: "Synthetic editorial illustration of an African small-business owner welcoming a customer",
    note: "AI-generated promotional illustration; not a named business, provider portrait or verified listing.",
  },
  learning: {
    src: "/stage2/stage2-learning.jpg",
    alt: "Synthetic editorial illustration of an adult tutor and learner reviewing study notes",
    note: "AI-generated promotional illustration; not a portrait of a listed specialist or a verified service.",
  },
  products: {
    src: "/stage2/stage2-products.jpg",
    alt: "Synthetic still life of a bottle, comb and brush on a warm neutral surface",
    note: "AI-generated promotional illustration; not a named brand, product SKU, stock or availability proof.",
  },
  tailoring: {
    src: "/stage2/stage2-tailoring.jpg",
    alt: "Synthetic editorial illustration of an older customer discussing fabric with an African tailor",
    note: "AI-generated category illustration; not a real supplier, person, service or tailoring stock record.",
  },
} as const;

export interface Stage2EditorialImageProps {
  image: keyof typeof editorialImages;
  className?: string;
  imageClassName?: string;
}

/** A limited, explicitly labeled wrapper for the approved synthetic editorial assets. */
export const Stage2EditorialImage = ({
  image,
  className,
  imageClassName,
}: Stage2EditorialImageProps) => {
  const media = editorialImages[image];

  return (
    <figure className={`aa-s2-editorial-figure${className ? ` ${className}` : ""}`}>
      <Image
        src={media.src}
        alt={media.alt}
        width={1024}
        height={1024}
        className={`aa-s2-editorial-img${imageClassName ? ` ${imageClassName}` : ""}`}
        sizes="(max-width: 700px) 100vw, 50vw"
      />
      <figcaption>{media.note}</figcaption>
    </figure>
  );
};

export default Stage2EditorialImage;