import "./_group.css";
import { useState } from "react";
import { ArrowRight, ArrowUpRight, BookOpen, BriefcaseBusiness, Brush, CalendarDays, ChevronRight, CircleHelp, Scissors, Search, ShoppingBag, Sparkles, Stethoscope, Wrench } from "lucide-react";
import { LinkButton, PageShell, PreviewBadge, useMockLocation } from "./_shared/Shell";

const categories: Array<{ title: string; icon: typeof Scissors; type: string; image?: string }> = [
  { title:"Hair care", icon:Scissors, type:"Services" },
  { title:"Tailoring", icon:Brush, type:"Services", image:"/__mockup/images/agendaally-stage2-art/categories/tailoring.svg" },
  { title:"Dental care", icon:Stethoscope, type:"Services", image:"/__mockup/images/agendaally-stage2-art/categories/dental-care.svg" },
  { title:"Home cleaning", icon:Sparkles, type:"Services" },
  { title:"Handyman", icon:Wrench, type:"Services" },
  { title:"Education", icon:BookOpen, type:"Services" },
];

export function Homepage() {
  const {city,country} = useMockLocation();
  const [domain, setDomain] = useState<"Services"|"Products">("Services");
  const [query, setQuery] = useState("");
  return <PageShell title="Discover local services and shops">
    <main>
      <section className="aa-s2-hero">
        <div className="aa-s2-wrap aa-s2-hero-grid">
          <div className="aa-s2-reveal">
            <span className="aa-s2-eyebrow">{city}, {country} · Local by design</span>
            <h1>Book local expertise. <span style={{color:"var(--bronze)"}}>Shop local businesses.</span></h1>
            <p className="aa-s2-muted" style={{fontSize:16,maxWidth:525,margin:0}}>Find skilled local professionals and independent businesses. Start with a service or browse products from shops in your city.</p>
            <div className="aa-s2-domain-tabs" role="group" aria-label="Choose what to discover">
              {(["Services","Products"] as const).map(item=><button key={item} type="button" className="aa-s2-domain-tab" aria-pressed={domain===item} onClick={()=>setDomain(item)}>{item}</button>)}
            </div>
            <form className="aa-s2-searchbox" onSubmit={event=>{event.preventDefault();window.location.assign(`/__mockup/preview/agendaally-stage2/Discovery?domain=${encodeURIComponent(domain)}&query=${encodeURIComponent(query)}`);}}>
              <span className="aa-s2-searchscope">{domain==="Services"?<Scissors size={16}/>:<ShoppingBag size={16}/>}<span>{domain}</span></span>
              <label className="aa-s2-sr-only" htmlFor="aa-s2-home-search">Search {domain.toLowerCase()}</label>
              <input id="aa-s2-home-search" value={query} onChange={e=>setQuery(e.target.value)} placeholder={domain==="Services"?"Haircut, tailoring, home cleaning":"Beauty & Personal Care, Tailoring & Apparel Supplies"} />
              <button className="aa-s2-btn aa-s2-btn-primary" type="submit" aria-label={`Search ${domain}`}><Search size={17}/><span>Search</span></button>
            </form>
            <div style={{display:"flex",alignItems:"center",gap:8,marginTop:17,color:"var(--muted)",fontSize:12}}><span className="aa-s2-location-dot"/>Current browsing context: {city}, {country} <span aria-hidden="true">·</span> <span>change above</span></div>
          </div>
          <div className="aa-s2-hero-visual aa-s2-reveal-delay">
            <img src="/__mockup/images/stage2-local-business.jpg" alt="A local business owner welcoming a customer in a warm independent shop" />
            <span style={{position:"absolute",left:15,bottom:15,background:"#fffefaed",padding:"8px 11px",borderRadius:7,fontSize:11,fontWeight:700}}>Illustrative / AI-generated image</span>
          </div>
        </div>
      </section>
      <main className="aa-s2-wrap">
        <section className="aa-s2-section">
          <div className="aa-s2-section-head"><div><span className="aa-s2-eyebrow">Find your starting point</span><h2>Made for the things you came to do.</h2></div><LinkButton page="Discovery?domain=Services" variant="secondary">Explore services <ArrowRight size={15}/></LinkButton></div>
          <div className="aa-s2-category-grid">
            {categories.map(({title,icon:Icon,type,image})=><a key={title} className="aa-s2-category" href={`/__mockup/preview/agendaally-stage2/Discovery?domain=Services&category=${encodeURIComponent(title)}`}><span className="aa-s2-cat-glyph">{image?<img src={image} alt="" width={24} height={24}/>:<Icon size={19}/>}</span><span><strong style={{fontSize:13}}>{title}</strong><span style={{display:"block",fontSize:10,color:"var(--muted)"}}>{type} · category</span></span></a>)}
          </div>
          <p style={{fontSize:11,color:"var(--muted)",marginTop:12}}>Category set follows supported catalog breadth; examples do not imply current local supply.</p>
        </section>
        <section className="aa-s2-section" style={{paddingBottom:5}}>
          <div className="aa-s2-section-head"><div><span className="aa-s2-eyebrow">One marketplace, two clear paths</span><h2>Book a service. Browse a shop.</h2></div></div>
          <div className="aa-s2-feature-grid">
            <article className="aa-s2-feature-card">
              <img src="/__mockup/images/stage2-learning.jpg" alt="A tutor and learner looking over a lesson together" />
              <div className="aa-s2-feature-copy"><span className="aa-s2-eyebrow">Services</span><h3 style={{fontSize:25,lineHeight:1.15,margin:"9px 0"}}>Find a specialist for your next task.</h3><p className="aa-s2-muted" style={{fontSize:13}}>From personal care to education and home help, discover businesses and the people who do the work.</p><LinkButton page="Discovery?domain=Services" variant="secondary">Explore services <ArrowRight size={14}/></LinkButton></div>
            </article>
            <article className="aa-s2-feature-card">
              <img src="/__mockup/images/stage2-products.jpg" alt="A comb and amber hair-care bottle arranged as a product still life" />
              <div className="aa-s2-feature-copy"><span className="aa-s2-eyebrow">Products</span><h3 style={{fontSize:25,lineHeight:1.15,margin:"9px 0"}}>Shop independent finds.</h3><p className="aa-s2-muted" style={{fontSize:13}}>Explore product catalogs separately from appointment services. Stock and variants belong to each product.</p><LinkButton page="Discovery?domain=Products" variant="soft">Browse products <ArrowRight size={14}/></LinkButton></div>
            </article>
          </div>
        </section>
        <section className="aa-s2-section">
          <div className="aa-s2-section-head"><div><span className="aa-s2-eyebrow">People & places</span><h2>Start with a business. Meet a specialist.</h2><p className="aa-s2-muted" style={{margin:"9px 0 0"}}>Seeded examples are references, not current availability or endorsement.</p></div><LinkButton page="BusinessProfile" variant="secondary">See a profile <ArrowRight size={15}/></LinkButton></div>
          <div className="aa-s2-card-list">
            <a className="aa-s2-result-card" href="/__mockup/preview/agendaally-stage2/BusinessProfile"><img src="/__mockup/images/stage2-local-business.jpg" alt="A customer and shop owner meeting at a local business" /><div className="aa-s2-result-body"><span className="aa-s2-eyebrow">Business · shop 501</span><h3 style={{margin:"7px 0 4px",fontSize:19}}>Le Sawa Beauty Studio</h3><p className="aa-s2-muted" style={{margin:0,fontSize:12}}>Open business profile <ChevronRight size={14} style={{verticalAlign:"middle"}}/></p></div></a>
            <a className="aa-s2-result-card" href="/__mockup/preview/agendaally-stage2/Discovery?domain=Businesses"><img src="/__mockup/images/stage2-learning.jpg" alt="A teacher and learner reviewing written work" /><div className="aa-s2-result-body"><span className="aa-s2-eyebrow">Business · education</span><h3 style={{margin:"7px 0 4px",fontSize:19}}>AgendaAlly Learning Hub</h3><p className="aa-s2-muted" style={{margin:0,fontSize:12}}>Business discovery entry <ChevronRight size={14} style={{verticalAlign:"middle"}}/></p></div></a>
            <article className="aa-s2-result-card"><div style={{display:"grid",placeItems:"center",aspectRatio:"1.42",background:"var(--olive-pale)",color:"var(--olive)"}}><div style={{textAlign:"center"}}><span className="aa-s2-initial" style={{margin:"auto"}}>AF</span><p style={{margin:"9px 0 0",fontWeight:700}}>Armand Fotso</p><small>Specialist · seed reference</small></div></div><div className="aa-s2-result-body"><span className="aa-s2-eyebrow">Service professional</span><h3 style={{margin:"7px 0 4px",fontSize:19}}>Haircut</h3><p className="aa-s2-muted" style={{margin:0,fontSize:12}}>Specialist + service example from accepted seed data.</p></div></article>
          </div>
        </section>
        <section className="aa-s2-section" style={{paddingBottom:8}}>
          <div className="aa-s2-preview"><CircleHelp size={16} style={{flex:"0 0 auto"}}/><span><strong>Honest discovery.</strong> No distance, live slots, ratings, or shop availability are inferred here. The city context is explicit; services, shops and products remain separate catalogs.</span></div>
        </section>
        <section className="aa-s2-section" style={{paddingTop:36}}>
          <div style={{borderRadius:14,background:"#eae4da",padding:"28px 30px",display:"flex",alignItems:"center",justifyContent:"space-between",gap:20,flexWrap:"wrap"}}>
            <div><span className="aa-s2-eyebrow">For independent businesses</span><h2 style={{fontSize:27,margin:"7px 0"}}>Bring your appointments and catalog into view.</h2><p className="aa-s2-muted" style={{margin:0,fontSize:13}}>AgendaAlly for Business belongs to the same product family.</p></div><LinkButton page="ForBusiness">See business tools <ArrowUpRight size={16}/></LinkButton>
          </div>
        </section>
      </main>
      <div className="aa-s2-wrap" style={{marginTop:25}}><PreviewBadge>Proposal using extracted homepage structure as its baseline. No native routes, API data or transaction flows are called.</PreviewBadge></div>
    </main>
  </PageShell>;
}