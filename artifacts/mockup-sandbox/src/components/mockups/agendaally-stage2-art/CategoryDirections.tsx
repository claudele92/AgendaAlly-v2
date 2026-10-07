import "./_group.css";
import type { ReactNode } from "react";

type Category = { label: string; key: string; image: string };
const categories: Category[] = [
  { label: "Hair Care", key: "hair-care", image: "/__mockup/images/agendaally-stage2-art/categories/hair-care.svg" },
  { label: "Education", key: "education", image: "" },
  { label: "Tailoring", key: "tailoring", image: "/__mockup/images/agendaally-stage2-art/categories/tailoring.svg" },
  { label: "Dental Care", key: "dental-care", image: "" },
  { label: "Healthcare", key: "healthcare", image: "/__mockup/images/agendaally-stage2-art/categories/healthcare.svg" },
  { label: "Handyman", key: "handyman", image: "/__mockup/images/agendaally-stage2-art/categories/handyman.svg" },
  { label: "Laundry & Dry Cleaning", key: "laundry-dry-cleaning", image: "/__mockup/images/agendaally-stage2-art/categories/laundry-dry-cleaning.svg" },
  { label: "Tattoo & Piercing", key: "tattoo-piercing", image: "" },
];

function SemanticIcon({ category, illustration = false }: { category: Category; illustration?: boolean }) {
  if (!illustration && category.image) return <img src={category.image} alt="" aria-hidden="true"/>;
  const stroke = illustration ? "#76522e" : "#191a19";
  const fill = illustration ? "#e9d9c1" : "none";
  if (category.key === "education") return <svg viewBox="0 0 64 64" role="img" aria-hidden="true">
    <path d="M8 17c9-3 17-1 24 4v31c-7-5-15-7-24-4V17Z" fill={fill} stroke={stroke} strokeWidth="2.5" strokeLinejoin="round"/>
    <path d="M56 17c-9-3-17-1-24 4v31c7-5 15-7 24-4V17Z" fill="none" stroke={stroke} strokeWidth="2.5" strokeLinejoin="round"/>
    <path d="M14 24c5-1 9 0 13 2M14 31c5-1 9 0 13 2M39 26c4-2 8-3 13-2M39 33c4-2 8-3 13-2" fill="none" stroke={stroke} strokeWidth="2" strokeLinecap="round"/>
    {illustration && <path d="M30 8v5m-2.5-2.5h5" stroke="#916d41" strokeWidth="2" strokeLinecap="round"/>}
  </svg>;
  if (category.key === "dental-care") return <svg viewBox="0 0 64 64" role="img" aria-hidden="true">
    <path d="M21 10c-7 0-12 5-12 13 0 6 3 10 5 16 2 7 3 15 7 15 3 0 4-9 7-13 1-2 3-2 4 0 3 4 4 13 7 13 4 0 5-8 7-15 2-6 5-10 5-16 0-8-5-13-12-13-4 0-6 2-9 2s-5-2-9-2Z" fill={fill} stroke={stroke} strokeWidth="2.5" strokeLinejoin="round"/>
    <path d="M22 25c2 2 5 2 7 0m6 0c2 2 5 2 7 0" fill="none" stroke={stroke} strokeWidth="2" strokeLinecap="round"/>
    {illustration && <path d="M48 8v8m-4-4h8" stroke="#916d41" strokeWidth="2" strokeLinecap="round"/>}
  </svg>;
  if (category.key === "tattoo-piercing") return <svg viewBox="0 0 64 64" role="img" aria-hidden="true">
    <path d="M14 48 46 16l5 5-32 32-5-5Z" fill={fill} stroke={stroke} strokeWidth="2.5" strokeLinejoin="round"/>
    <path d="m39 23 5 5M11 53l-2 3 4-1M32 16l-3-6 5 3 5-2-2 5 3 5-6-2-4 4 2-7Z" fill="none" stroke={stroke} strokeWidth="2.2" strokeLinejoin="round"/>
    <circle cx="48" cy="47" r="7" fill="none" stroke={stroke} strokeWidth="2.5"/><path d="M52 42l5-5" stroke={stroke} strokeWidth="2.5" strokeLinecap="round"/>
    {illustration && <circle cx="48" cy="47" r="2" fill="#916d41"/>}
  </svg>;
  // Original illustrative mini-scenes for the six matched source categories.
  const paths: Record<string, ReactNode> = {
    "hair-care": <><path d="M24 15c8 0 13 7 12 16l-2 21H20l-3-20c-1-9 1-17 7-17Z"/><path d="M18 23c-6 8-5 18 0 23m17-24c7 8 5 16 1 22M22 31h8"/></>,
    tailoring: <><path d="m22 12 10 8 10-8 10 7-8 10-5-3v24H23V26l-5 3-8-10 12-7Z"/><path d="M26 37h12M32 20v30"/></>,
    healthcare: <><path d="M32 52S12 40 12 26c0-9 12-14 20-4 8-10 20-5 20 4 0 14-20 26-20 26Z"/><path d="M21 32h7l4-9 5 18 4-9h4"/></>,
    handyman: <><path d="m19 13 9 9-6 6-9-9a13 13 0 0 0 16 16l16 16a5 5 0 0 0 7-7L36 28a13 13 0 0 0-16-15Z"/></>,
    "laundry-dry-cleaning": <><path d="M17 18h30l-3 34H20l-3-34Z"/><path d="M23 13h18M24 27h16"/><path d="M24 38c3-5 6 5 9 0s6 5 9 0"/></>,
    "home-cleaning": <><path d="m11 29 21-17 21 17M18 25v27h28V25"/><path d="M28 52V36h9v16M47 12l2-5m5 12 5-2"/></>,
  };
  return <svg viewBox="0 0 64 64" role="img" aria-hidden="true">
    <circle cx="32" cy="32" r="27" fill={illustration ? "#f5f0e7" : "none"} stroke={illustration ? "#d8c5a9" : "none"} strokeWidth="1"/>
    <g fill={illustration ? "#f7f1e7" : "none"} stroke={stroke} strokeWidth={illustration ? 2.4 : 2.1} strokeLinecap="round" strokeLinejoin="round">{paths[category.key]}</g>
    {illustration && <path d="M10 46c7 8 17 12 28 10" fill="none" stroke="#b28a5d" strokeWidth="2" strokeLinecap="round"/>}
  </svg>;
}

function PictogramTile({ category }: { category: Category }) {
  return <div className="aa2-tile">
    <span className="aa2-tile-icon"><SemanticIcon category={category}/></span>
    <span className="aa2-tile-label">{category.label}</span>
  </div>;
}

function IllustrationTile({ category }: { category: Category }) {
  return <div className="aa2-tile">
    <span className="aa2-tile-icon"><SemanticIcon category={category} illustration/></span>
    <span className="aa2-tile-label">{category.label}</span>
  </div>;
}

const photoFor: Record<string, { src: string; alt: string; note: string }> = {
  "hair-care": { src: "/__mockup/images/stage2-local-business.jpg", alt: "Generated editorial scene of a Black African salon owner welcoming a customer", note: "AI-generated concept scene" },
  education: { src: "/__mockup/images/stage2-learning.jpg", alt: "Generated editorial scene of an African tutor and learner reviewing study notes", note: "AI-generated concept scene" },
  tailoring: { src: "/__mockup/images/stage2-tailoring.jpg", alt: "AI-generated illustrative scene of an older Asian woman customer consulting with a Black African male tailor; concept art, not a real supplier or proof of live tailoring supplies", note: "AI-generated category illustration · not a supplier" },
  "dental-care": { src: "/__mockup/images/stage2-learning.jpg", alt: "Generated learning consultation scene used as a placeholder for the photography concept, not a dental business", note: "AI-generated; not a dental example" },
  healthcare: { src: "/__mockup/images/stage2-local-business.jpg", alt: "Generated local service welcome scene used to test image-led category treatment, not a healthcare clinic", note: "AI-generated; not a clinic" },
  handyman: { src: "/__mockup/images/stage2-products.jpg", alt: "Generated product still life used to test image-led category treatment, not handyman work", note: "AI-generated; not handyman work" },
  "laundry-dry-cleaning": { src: "/__mockup/images/stage2-local-business.jpg", alt: "Generated local service setting used to test image-led category treatment, not a laundry business", note: "AI-generated; not a laundry business" },
  "tattoo-piercing": { src: "/__mockup/images/stage2-learning.jpg", alt: "Generated study scene used to test image-led category treatment, not a tattoo studio", note: "AI-generated; not a tattoo studio" },
};

function PhotoTile({ category }: { category: Category }) {
  const photo = photoFor[category.key];
  return <div className="aa2-photo-tile">
    <img src={photo.src} alt={photo.alt}/>
    <span className="aa2-photo-tag">{category.label}<small>{photo.note}</small></span>
  </div>;
}

function Direction({ type, number, title, kicker, body, tradeoff }: {
  type: "icons" | "illustrative" | "photography"; number: string; title: string; kicker: string; body: string; tradeoff: string;
}) {
  const photo = type === "photography";
  const illustrated = type === "illustrative";
  return <section className={`aa2-direction ${illustrated ? "aa2-illustrative" : ""}`} aria-labelledby={`direction-${number}`}>
    <div className="aa2-direction-head">
      <div><div className="aa2-kicker">{number} · {kicker}</div><h2 id={`direction-${number}`}>{title}</h2><p>{body}</p></div>
      <aside className="aa2-direction-aside"><strong>Trade-off</strong><br/>{tradeoff}</aside>
    </div>
    {photo ? <div className="aa2-photo-grid">{categories.map((c) => <PhotoTile category={c} key={c.key}/>)}</div> :
      <div className="aa2-tiles">{categories.map((c) => illustrated ? <IllustrationTile category={c} key={c.key}/> : <PictogramTile category={c} key={c.key}/>)}</div>}
  </section>;
}

export function CategoryDirections() {
  return <main className="aa2-art aa2-category">
    <div className="aa2-wrap">
      <header className="aa2-top"><span className="aa2-logo"><span className="aa2-mark" aria-hidden="true">a.</span><span className="aa2-wordmark">AgendaAlly</span></span><span className="aa2-topnote">Category art · three hypotheses · same category set</span></header>
      <section className="aa2-hero">
        <div><div className="aa2-kicker">Scalable discovery language</div><h1>One taxonomy.<br/>Three visual answers.</h1><p>Real service categories from the native fixture: Hair Care, Education, Tailoring, Dental Care, Healthcare, Handyman, Laundry &amp; Dry Cleaning, and Tattoo &amp; Piercing. Same labels; different ways to make those choices recognizable.</p></div>
        <aside className="aa2-aside"><strong>Held constant</strong><br/>These are service discovery categories. Product browse categories remain separately represented below; no shared parent hierarchy is implied.</aside>
      </section>
      <div className="aa2-directions">
        <Direction type="icons" number="A" kicker="Custom pictograms" title="A consistent native category family" body="Compact, reusable line symbols; one optical size and stroke, recognizable at a glance. The original Hair Care and other compatible category artwork is copied into this isolated sandbox; Dental Care, Education and Tattoo & Piercing get semantically corrected authored SVGs rather than borrowed tooth/book/tattoo proxies." tradeoff="The clearest and lightest at small tile sizes; requires disciplined vector stewardship as the category catalog expands."/>
        <Direction type="illustrative" number="B" kicker="Restrained vignettes" title="A little human context, still drawn" body="Original vector mini-scenes add warmth and are explicitly interpretive—not literal service guarantees. A sparse editorial palette keeps the cards calm, while each vignette stays tied to the same service label." tradeoff="More expressive for editorial moments, but less compact and harder to keep consistent across a rapidly growing taxonomy."/>
        <Direction type="photography" number="C" kicker="Photography / hybrid" title="Editorial images carry the mood" body="Use saved, AI-generated local concept photos with explicit alt text. These are illustrative category treatments, not genuine business listings, real suppliers, product availability, or proof of live service; some examples are deliberately imperfect fits to show the risk of forcing photography into every category. The Tailoring image adds an older Asian customer and a Black African male tailor to the represented ages, genders and global contexts." tradeoff="Human stories can build connection; image sourcing, representation, licensing, crops and category mismatch create ongoing maintenance cost."/>
      </div>
      <section className="aa2-direction" aria-labelledby="products-distinct">
        <div className="aa2-direction-head">
          <div><div className="aa2-kicker">Service / product distinction</div><h2 id="products-distinct">Products are their own browse surface.</h2><p>Examples from the actual product taxonomy are presented separately. They are not an extra branch inside this service grid, and are not repeated as a product offer under every service.</p></div>
          <aside className="aa2-direction-aside"><strong>Separate taxonomy</strong><br/>Do not merge category trees unless the product model explicitly supports it.</aside>
        </div>
        <div className="aa2-taxonomy">
          <article><span className="aa2-taxmark" aria-hidden="true">✳</span><div><b>Beauty &amp; Personal Care</b><span>Product category · distinct from Hair Care services.</span></div></article>
          <article><span className="aa2-taxmark" aria-hidden="true">⌁</span><div><b>Tailoring &amp; Apparel Supplies</b><span>Product category · distinct from Tailoring services.</span></div></article>
        </div>
      </section>
      <section className="aa2-rec" aria-labelledby="category-recommendation">
        <div><div className="aa2-kicker" style={{ color: "#d4b88f" }}>Recommendation</div><h2 id="category-recommendation">Custom pictograms first. Photography by choice.</h2><p>Adopt the coherent custom line-icon family for taxonomy, with selective human/editorial photography on campaign, profile and recommendation surfaces where real context helps. Don’t force every service into an image slot. This balances scan speed, inclusion, small-screen clarity and sustainable expansion.</p></div><span className="aa2-rec-badge">Recommended direction A + selective C</span>
      </section>
      <div className="aa2-spec-grid">
        <section className="aa2-section"><div className="aa2-section-head"><div><div className="aa2-kicker">Tile and scaling rules</div><h2>Designed for small screens first.</h2></div></div><div className="aa2-spec-box"><p><strong>Shown at mobile width:</strong> the category cards collapse to a two-column grid, with 42px symbols, 11px type, 7px gutters and labels allowed to wrap. Long category names such as “Laundry &amp; Dry Cleaning” keep their full label—never truncate meaning to save space. Aim for a 112px or wider card and 44px+ interactive target; allow horizontal overflow only if preserving a deliberate native carousel with clear affordance.</p><p><strong>Expansion:</strong> use a 24px viewBox, 1.75–2px round-capped strokes for refined pictograms; preserve a common optical center and 16% minimum internal margin. At 20px rendering, simplify interior detail before thinning strokes. Ensure at least 3:1 non-text contrast against the tile, with the adjacent text label carrying the category meaning.</p><p><strong>Card container:</strong> warm paper or white surface, 1px neutral border, 12–16px radius, 12px interior padding; never assign category meaning by color alone.</p></div></section>
        <section className="aa2-section"><div className="aa2-section-head"><div><div className="aa2-kicker">Source provenance</div><h2>Traceable, sandbox-local assets.</h2></div></div><div className="aa2-asset-note"><strong>Existing copied source:</strong> Hair Care, Tailoring, Healthcare, Handyman, Laundry &amp; Dry Cleaning SVGs copied from <code>.local/agendaally-preview/web/public/icons/categories/</code> into this group’s <code>assets/categories/</code> and sandbox public image directory. Their meaning matches the native category fixture values at <code>agendaally-stage2-current/current-baseline.tsx</code> (including the real labels).<br/><br/><strong>Original authored SVGs:</strong> Education open book, Dental Care tooth, Tattoo &amp; Piercing needle / piercing hoop; original illustrative mini-scenes authored in this file. Semantic corrections intentionally avoid using the tooth/book/tattoo symbols as unrelated generic service proxies.<br/><br/><strong>AI-generated illustrative photography:</strong> <code>stage2-local-business.jpg</code>, <code>stage2-learning.jpg</code>, <code>stage2-products.jpg</code>, and <code>stage2-tailoring.jpg</code> under the sandbox public images directory. Tailoring shows an older Asian woman customer and Black African male tailor; it is category concept art only—not a real supplier, live supply proof, or availability claim. All imagery is illustrative and not genuine business photography. Service and product taxonomies remain separate. Quiver generation produced no usable asset; no failed imports or Quiver dependencies are used.</div></section>
      </div>
      <p className="aa2-legal">Category art proposal only · all category cards are visual hypotheses based on native fixture names; no API, native code, category data or global styling was changed.</p>
    </div>
  </main>;
}

export default CategoryDirections;