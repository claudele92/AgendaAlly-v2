import Link from "next/link";
import { ImageWithFallBack } from "@/components/image";

interface DiscoveryPathsProps {
  productsEnabled: boolean;
}

export const DiscoveryPaths = ({ productsEnabled }: DiscoveryPathsProps) => (
  <section className="aa-s2-section" style={{ paddingTop: 42 }}>
    <div className="aa-s2-section-head">
      <div>
        <span className="aa-s2-eyebrow">One marketplace, two clear paths</span>
        <h2>Book a service. Browse a shop.</h2>
      </div>
    </div>
    <div className="aa-s2-feature-grid">
      <article className="aa-s2-feature-card">
        <div className="relative min-h-[220px]">
          <ImageWithFallBack
            alt="Illustrative, AI-generated image of a tutor and learner reviewing a lesson"
            className="object-cover"
            fill
            sizes="(max-width: 700px) 100vw, 50vw"
            src="/stage2/stage2-learning.jpg"
          />
          <span className="absolute bottom-3 left-3 rounded-md bg-[#fffefaed] px-3 py-2 text-[11px] font-semibold">
            Illustrative / AI-generated image
          </span>
        </div>
        <div className="aa-s2-feature-copy">
          <span className="aa-s2-eyebrow">Services</span>
          <h3 className="my-2 text-2xl font-bold">Find a specialist for your next task.</h3>
          <p className="aa-s2-muted text-sm">
            Browse service categories and businesses through the existing booking marketplace.
          </p>
          <Link className="aa-s2-btn aa-s2-btn-secondary mt-2" href="/services">
            Explore services <span aria-hidden="true">→</span>
          </Link>
        </div>
      </article>
      {productsEnabled && (
        <article className="aa-s2-feature-card">
          <div className="relative min-h-[220px]">
            <ImageWithFallBack
              alt="Illustrative, AI-generated product still life representing tailoring supplies"
              className="object-cover"
              fill
              sizes="(max-width: 700px) 100vw, 50vw"
              src="/stage2/stage2-tailoring.jpg"
            />
            <span className="absolute bottom-3 left-3 rounded-md bg-[#fffefaed] px-3 py-2 text-[11px] font-semibold">
              Illustrative / AI-generated image
            </span>
          </div>
          <div className="aa-s2-feature-copy">
            <span className="aa-s2-eyebrow">Products</span>
            <h3 className="my-2 text-2xl font-bold">Browse product catalogs.</h3>
            <p className="aa-s2-muted text-sm">
              Product catalogs, categories, and stock are browsed separately from appointment
              services.
            </p>
            <Link className="aa-s2-btn aa-s2-btn-soft mt-2" href="/products">
              Browse products <span aria-hidden="true">→</span>
            </Link>
          </div>
        </article>
      )}
    </div>
  </section>
);