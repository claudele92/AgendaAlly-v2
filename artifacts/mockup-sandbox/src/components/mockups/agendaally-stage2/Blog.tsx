import "./_group.css";
import "./_support.css";
import type { ReactNode } from "react";
import { ArrowRight, BookOpen, CalendarDays, ChevronRight } from "lucide-react";
import { LinkButton, PageShell, PreviewBadge } from "./_shared/Shell";

const articles = [
  {
    id: "local-services",
    title: "A clearer way to explore local services",
    description: "Use the marketplace’s country, city, business, and service details to make an informed appointment choice.",
    image: "/__mockup/images/stage2-local-business.jpg",
    alt: "A Black African shop owner welcoming a customer inside an independent business",
  },
  {
    id: "products",
    title: "Browse products as well as appointments",
    description: "AgendaAlly’s original customer storefront includes product cards, stock variants, and a cart alongside service booking.",
    image: "/__mockup/images/stage2-products.jpg",
    alt: "An amber bottle and handcrafted comb arranged in a warm product still life",
  },
  {
    id: "business-profile",
    title: "A business profile can introduce more than one service",
    description: "Explore the existing business tools for service details, staff, branches, and product catalogs.",
    image: "/__mockup/images/stage2-learning.jpg",
    alt: "A Black African business owner and colleague reviewing a notebook together",
  },
];

function ArticleLink({ id, children, className = "" }: { id: string; children: ReactNode; className?: string }) {
  return <a className={className} href={`/__mockup/preview/agendaally-stage2/Article?id=${id}`}>{children}</a>;
}

function Byline() {
  return <div className="aa-s2-blog-meta"><span><CalendarDays size={14} aria-hidden="true"/>1 October 2026</span><span><BookOpen size={14} aria-hidden="true"/>Preview author record · synthetic admin</span></div>;
}

export function Blog() {
  const [lead, ...rest] = articles;
  return <PageShell title="Stories & guides">
    <main className="aa-s2-support">
      <section className="aa-s2-support-hero">
        <div className="aa-s2-wrap">
          <span className="aa-s2-eyebrow">AgendaAlly journal · preview edition</span>
          <h1>Local knowledge, useful next steps.</h1>
          <p>Notes on finding services, exploring independent products, and understanding the tools that bring a business profile together.</p>
        </div>
      </section>
      <div className="aa-s2-wrap aa-s2-support-main">
        <PreviewBadge>Development preview content. These are seeded editorial examples, not live offers or verified recommendations. Images are synthetic AI-generated illustrations made for this proposal.</PreviewBadge>
        <section className="aa-s2-support-section" aria-labelledby="journal-picks">
          <div className="aa-s2-support-section-head"><div><span className="aa-s2-eyebrow">A place to begin</span><h2 id="journal-picks">Explore the marketplace with context.</h2></div><span className="aa-s2-chip">3 sample articles</span></div>
          <div className="aa-s2-blog-layout">
            <ArticleLink id={lead.id} className="aa-s2-blog-lead">
              <img src={lead.image} alt={lead.alt}/>
              <div className="aa-s2-blog-lead-copy"><span className="aa-s2-eyebrow">Seeded article · 01</span><h2>{lead.title}</h2><p className="aa-s2-muted" style={{fontSize:14, lineHeight:1.65, margin:"0 0 19px"}}>{lead.description}</p><Byline/><span className="aa-s2-btn aa-s2-btn-primary" style={{marginTop:20}}>Read article <ArrowRight size={15}/></span></div>
            </ArticleLink>
            <div className="aa-s2-blog-side">
              {rest.map((article, index)=><ArticleLink key={article.id} id={article.id} className="aa-s2-blog-mini">
                <img src={article.image} alt={article.alt}/>
                <div className="aa-s2-blog-mini-copy"><span className="aa-s2-eyebrow">Seeded article · 0{index+2}</span><h2>{article.title}</h2><Byline/><span style={{display:"inline-flex",alignItems:"center",gap:5,fontSize:12,fontWeight:700,color:"var(--bronze-deep)",marginTop:12}}>Read article <ChevronRight size={14}/></span></div>
              </ArticleLink>)}
            </div>
          </div>
        </section>
        <section className="aa-s2-support-section" aria-labelledby="all-stories">
          <div className="aa-s2-support-section-head"><div><span className="aa-s2-eyebrow">From the preview desk</span><h2 id="all-stories">Three ways into the story.</h2></div></div>
          <div className="aa-s2-blog-grid">
            {articles.map((article,index)=><ArticleLink key={article.id} id={article.id} className="aa-s2-blog-card">
              <img src={article.image} alt={article.alt}/>
              <div className="aa-s2-blog-card-copy"><span className="aa-s2-eyebrow">Seeded article · 0{index+1}</span><h2>{article.title}</h2><p>{article.description}</p><Byline/></div>
            </ArticleLink>)}
          </div>
        </section>
        <div style={{marginTop:34}}><PreviewBadge>Article links open the matching local proposal detail. No category or tag taxonomy is implied; the native article record has no category/tag relationship.</PreviewBadge></div>
        <div style={{display:"flex",justifyContent:"flex-end",marginTop:20}}><LinkButton page="Support" variant="secondary">About & support <ArrowRight size={15}/></LinkButton></div>
      </div>
    </main>
  </PageShell>;
}