import "./_group.css";
import "./_support.css";
import { ArrowLeft, CalendarDays, ChevronRight } from "lucide-react";
import { LinkButton, PageShell, PreviewBadge } from "./_shared/Shell";

const stories = {
  "local-services": {
    title: "A clearer way to explore local services",
    description: "Use the marketplace’s country, city, business, and service details to make an informed appointment choice.",
    image: "/__mockup/images/stage2-local-business.jpg",
    alt: "A Black African business owner warmly welcoming a customer at an independent shop",
    eyebrow: "Seeded article · development preview",
    sections: [
      { heading: null, text: "Start with a country and city, then explore the businesses and services listed for that market. The native marketplace shows service and provider details that can help you compare what is offered before proceeding." },
      { heading: "Check the details", text: "Review the listed service, location, displayed availability, and price before continuing. The contents of this development catalog are examples, not live appointments or verified recommendations." },
      { heading: null, text: "Beauty and wellness are part of the original catalog, alongside other service types represented by existing development data. A location choice does not require Maps to load seeded city listings." },
    ],
  },
  products: {
    title: "Browse products as well as appointments",
    description: "AgendaAlly’s original customer storefront includes product cards, stock variants, and a cart alongside service booking.",
    image: "/__mockup/images/stage2-products.jpg",
    alt: "Amber product bottle, natural comb, and brushes in a warm still life",
    eyebrow: "Seeded article · development preview",
    sections: [
      { heading: null, text: "Appointments are only one part of AgendaAlly’s native customer storefront. A customer can also explore product listings, review the options and prices attached to a product, and use the existing cart workflow." },
      { heading: "Product details matter", text: "Check the selected item, variant, and displayed stock and price before adding a product to a cart. Product records in this development preview are synthetic; they do not represent a real retailer or a promise that stock is available." },
    ],
  },
  "business-profile": {
    title: "A business profile can introduce more than one service",
    description: "Explore the existing business tools for service details, staff, branches, and product catalogs.",
    image: "/__mockup/images/stage2-learning.jpg",
    alt: "Two Black African colleagues discussing notes at a small business desk",
    eyebrow: "Seeded article · development preview",
    sections: [
      { heading: null, text: "The original AgendaAlly business application already contains workflows for managing a business profile, branches, service offerings, specialists or staff, and product catalogs. Which controls a person can use depends on their account permissions." },
      { heading: "Keep customer details current", text: "Clear service information and accurate branch context help customers understand what is listed. The native application provides its own booking and catalog workflows; this article does not imply that development sample businesses are taking real appointments or orders." },
    ],
  },
} as const;

type StoryKey = keyof typeof stories;

export function Article() {
  const key = new URLSearchParams(window.location.search).get("id") as StoryKey | null;
  const article = key && key in stories ? stories[key] : stories["local-services"];
  return <PageShell title={article.title}>
    <main className="aa-s2-support">
      <div className="aa-s2-wrap aa-s2-reading-wrap">
        <div style={{display:"flex",justifyContent:"space-between",alignItems:"center",gap:12,flexWrap:"wrap"}}>
          <LinkButton page="Blog" variant="secondary"><ArrowLeft size={15}/>All articles</LinkButton>
          <span className="aa-s2-chip">Development preview · synthetic editorial</span>
        </div>
        <header className="aa-s2-article-head">
          <span className="aa-s2-eyebrow">{article.eyebrow}</span>
          <h1>{article.title}</h1>
          <p style={{fontSize:16,lineHeight:1.65,color:"var(--ink-soft)",margin:"0 0 17px"}}>{article.description}</p>
          <div className="aa-s2-blog-meta"><span><CalendarDays size={14} aria-hidden="true"/>1 October 2026</span><span>By preview author record · synthetic admin</span></div>
        </header>
        <img className="aa-s2-article-cover" src={article.image} alt={article.alt}/>
        <p className="aa-s2-visual-credit" style={{margin:"8px 2px 0"}}>Illustrative synthetic AI-generated image created for this visual proposal.</p>
        <article className="aa-s2-article-body">
          {article.sections.map((section,index)=><section key={`${section.heading??"intro"}-${index}`}>{section.heading && <h2>{section.heading}</h2>}<p>{section.text}</p></section>)}
          <div className="aa-s2-article-end"><PreviewBadge>This article reproduces seeded development-preview copy. Examples do not represent live listings or verified recommendations.</PreviewBadge><LinkButton page="Blog" variant="soft">More from the journal <ChevronRight size={15}/></LinkButton></div>
        </article>
      </div>
    </main>
  </PageShell>;
}