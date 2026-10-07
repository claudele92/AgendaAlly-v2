import "./_group.css";
import { useEffect, useMemo, useState } from "react";
import { ArrowRight, Check, CircleAlert, Clock3, Filter, MapPin, Package, Search, Scissors, ShoppingBag, SlidersHorizontal } from "lucide-react";
import { LinkButton, PageShell, PreviewBadge, useMockLocation } from "./_shared/Shell";

type Domain = "Services"|"Products"|"Businesses"|"Specialists";
const listings = [
  { name:"Le Sawa Beauty Studio", kind:"Businesses", category:"Hair care", note:"Shop 501 · seeded business", image:"/__mockup/images/stage2-local-business.jpg", href:"BusinessProfile" },
  { name:"Haircut", kind:"Services", category:"Hair care", note:"Catalog example · service 1", image:"/__mockup/images/stage2-local-business.jpg", href:"BusinessProfile" },
  { name:"Moroccan Argan Oil Hair Serum", kind:"Products", category:"Beauty & Personal Care", note:"Product catalog example · stock/variant fields supported", image:"/__mockup/images/stage2-products.jpg", href:"BusinessProfile" },
  { name:"Armand Fotso", kind:"Specialists", category:"Hair care", note:"Specialist 112 · active assignment", image:"", href:"BusinessProfile" },
  { name:"AgendaAlly Learning Hub", kind:"Businesses", category:"Education", note:"Seeded Douala business", image:"/__mockup/images/stage2-learning.jpg", href:"BusinessProfile" },
];
const categories = ["All categories","Beauty & Personal Care","Tailoring & Apparel Supplies","Hair care","Tailoring","Dental care","Healthcare","Handyman","Laundry & dry cleaning","Home cleaning","Education"];
const productCategories = categories.slice(0, 3);
const serviceCategories = [categories[0], ...categories.slice(3)];

function parseDiscoveryQuery(search: string) {
  const params = new URLSearchParams(search);
  const requestedDomain = params.get("domain");
  const domain: Domain = requestedDomain === "Products" || requestedDomain === "Businesses" || requestedDomain === "Specialists" ? requestedDomain : "Services";
  const requestedCategory = params.get("category");
  return {
    domain,
    query: params.get("query") ?? "",
    category: requestedCategory && (domain === "Products" ? productCategories : serviceCategories).includes(requestedCategory) ? requestedCategory : "All categories",
  };
}

export function Discovery() {
  const {city,country} = useMockLocation();
  const [initialQuery] = useState(() => parseDiscoveryQuery(window.location.search));
  const [domain,setDomain] = useState<Domain>(initialQuery.domain);
  const [query,setQuery] = useState(initialQuery.query);
  const [category,setCategory] = useState(initialQuery.category);
  const [sort,setSort] = useState("Relevance");
  const [state,setState] = useState<"ready"|"loading"|"empty"|"error">("ready");
  const [includeProfessionals,setIncludeProfessionals] = useState(true);
  useEffect(()=>{ if(state!=="loading")return; const timer=window.setTimeout(()=>setState("ready"),650); return()=>window.clearTimeout(timer); },[state]);
  const results = useMemo(()=>{
    const allowed = domain==="Services" ? ["Services","Businesses","Specialists"] : [domain];
    if(city !== "Douala") return [];
    return listings.filter(item=>allowed.includes(item.kind) && (category==="All categories" || item.category===category) && item.name.toLowerCase().includes(query.toLowerCase()) && (includeProfessionals || item.kind!=="Specialists"));
  },[domain,category,query,includeProfessionals,city]);
  const switchDomain=(value:Domain)=>{setDomain(value);setCategory("All categories");setState("ready");};
  const categoryOptions = domain === "Products" ? productCategories : serviceCategories;
  return <PageShell title="Search services and products">
    <main className="aa-s2-wrap">
      <section className="aa-s2-discovery-head">
        <span className="aa-s2-eyebrow">{city}, {country} · Separate catalogs</span>
        <h1 style={{fontSize:"clamp(34px,4vw,48px)",margin:"8px 0 11px",lineHeight:1.08}}>Find what you came for.</h1>
        <p className="aa-s2-muted" style={{margin:"0 0 21px",maxWidth:630}}>Choose a discovery path. Services and shops use service discovery; products have their own catalog and stock/variant filters.</p>
        <div className="aa-s2-domain-tabs" role="group" aria-label="Discovery domain">
          {(["Services","Products","Businesses","Specialists"] as Domain[]).map(label=><button className="aa-s2-domain-tab" key={label} aria-pressed={domain===label} onClick={()=>switchDomain(label)} type="button">{label}</button>)}
        </div>
        <div className="aa-s2-searchbox" style={{maxWidth:"none",marginTop:15}}>
          <Search size={17} style={{marginLeft:9,color:"var(--muted)"}}/>
          <label htmlFor="aa-s2-search" className="aa-s2-sr-only">Search {domain.toLowerCase()}</label>
          <input id="aa-s2-search" value={query} onChange={e=>{setQuery(e.target.value);setState("ready");}} placeholder={domain==="Products"?"Search product names":"Search services, businesses or specialists"} />
          <span className="aa-s2-chip"><MapPin size={13}/> {city}</span>
          <button className="aa-s2-btn aa-s2-btn-primary" type="button" onClick={()=>setState("loading")}><Search size={16}/><span>Search</span></button>
        </div>
        <div className="aa-s2-pill-row" style={{marginTop:13}}><span className="aa-s2-chip">Service/shop search</span><span className="aa-s2-chip">Product catalog is separate</span><span className="aa-s2-chip">No map or distance used</span></div>
      </section>
      <section className="aa-s2-discovery-layout" aria-label="Search results">
        <aside className="aa-s2-filters">
          <div style={{display:"flex",alignItems:"center",gap:8,fontWeight:800}}><SlidersHorizontal size={17}/> Refine</div>
          <div className="aa-s2-filter-group"><label htmlFor="aa-s2-category" style={{display:"block",fontWeight:700,color:"var(--ink)",padding:0,marginBottom:9}}>Category</label><select id="aa-s2-category" className="aa-s2-select-control" value={category} onChange={e=>setCategory(e.target.value)}>{categoryOptions.map(x=><option key={x}>{x}</option>)}</select></div>
          <div className="aa-s2-filter-group"><strong style={{fontSize:13}}>Catalog type</strong>
            <label><input type="checkbox" checked={includeProfessionals} onChange={e=>setIncludeProfessionals(e.target.checked)}/> Include specialists</label>
            <p className="aa-s2-muted" style={{fontSize:11,margin:"8px 0 0"}}>Applicable to the services path only.</p>
          </div>
          <div className="aa-s2-filter-group"><strong style={{fontSize:13}}>Appointment date</strong><p className="aa-s2-muted" style={{fontSize:11,margin:"7px 0 0"}}>Date/time filters are not an authoritative live slot check. Confirm availability in the supported booking flow.</p><button type="button" className="aa-s2-btn aa-s2-btn-secondary" style={{width:"100%",marginTop:10}} onClick={()=>setState("loading")}><Clock3 size={14}/> Apply preview filters</button></div>
          <div className="aa-s2-filter-group"><strong style={{fontSize:13}}>Map</strong><p className="aa-s2-muted" style={{fontSize:11,margin:"6px 0 0"}}>Optional and unavailable in this concept. No location permission or distance is requested.</p></div>
        </aside>
        <div>
            <div style={{display:"flex",alignItems:"center",justifyContent:"space-between",gap:12,flexWrap:"wrap",marginBottom:15}}>
            <div><h2 style={{fontSize:22,margin:"0 0 3px"}}>{domain} in {city}</h2><p className="aa-s2-muted" style={{margin:0,fontSize:12}}>Illustrative catalog references · not current supply inventory</p></div>
            <label style={{fontSize:12,fontWeight:700}}>Sort <select aria-label="Sort results" className="aa-s2-select-control" style={{width:143,marginLeft:7}} value={sort} onChange={e=>setSort(e.target.value)}><option>Relevance</option><option>Name</option><option>Category</option></select></label>
          </div>
          <PreviewBadge>Local interactions only. Service/shop results and product catalog are separate native API surfaces; this prototype does not unify them.</PreviewBadge>
          <div style={{display:"flex",gap:8,margin:"15px 0 17px",flexWrap:"wrap"}}>
            <button className="aa-s2-btn aa-s2-btn-secondary" type="button" onClick={()=>setState("loading")}><Filter size={15}/> Refresh preview</button>
            <button className="aa-s2-btn aa-s2-btn-secondary" type="button" onClick={()=>{setQuery("no matching item");setState("empty");}}>Show empty example</button>
            <button className="aa-s2-btn aa-s2-btn-secondary" type="button" onClick={()=>setState("error")}>Show error example</button>
          </div>
          {state==="loading" ? <div className="aa-s2-state-card" role="status"><strong>Updating this preview…</strong><p className="aa-s2-muted" style={{margin:5}}>A brief local loading state. No request is sent.</p><div style={{height:8,width:"48%",background:"#e7e0d5",borderRadius:9,marginTop:12}}/></div>
          : state==="error" ? <div className="aa-s2-state-card" role="alert"><CircleAlert size={21} color="var(--error)"/><h3 style={{margin:"9px 0 4px"}}>This preview could not refresh.</h3><p className="aa-s2-muted" style={{margin:"0 0 12px"}}>Local demonstration state only. Your filters have not been submitted anywhere.</p><button className="aa-s2-btn aa-s2-btn-secondary" type="button" onClick={()=>setState("ready")}>Retry preview</button></div>
          : state==="empty" || results.length===0 ? <div className="aa-s2-state-card"><span className="aa-s2-eyebrow">No matching examples</span><h3 style={{fontSize:22,margin:"8px 0"}}>Nothing in this illustrative set.</h3><p className="aa-s2-muted" style={{margin:"0 0 14px"}}>That does not establish a real-world supply gap. Try another category or clear your search.</p><button className="aa-s2-btn aa-s2-btn-secondary" type="button" onClick={()=>{setQuery("");setCategory("All categories");setState("ready");}}>Clear filters</button></div>
          : <div className="aa-s2-card-list">
            {results.slice().sort((a,b)=>sort==="Name"?a.name.localeCompare(b.name):sort==="Category"?a.category.localeCompare(b.category):0).map(item=><a key={item.name} className="aa-s2-result-card" href={`/__mockup/preview/agendaally-stage2/${item.href}`}>
              {item.image?<img src={item.image} alt="" />:<div style={{aspectRatio:"1.42",background:"var(--olive-pale)",display:"grid",placeItems:"center"}}><div className="aa-s2-initial">AF</div></div>}
              <div className="aa-s2-result-body"><span className="aa-s2-eyebrow">{item.kind} · {item.category}</span><h3 style={{fontSize:17,margin:"6px 0 4px"}}>{item.name}</h3><p className="aa-s2-muted" style={{fontSize:12,margin:"0 0 12px"}}>{item.note}</p><span style={{fontSize:12,fontWeight:700}}>View reference <ArrowRight size={13} style={{verticalAlign:"middle"}}/></span></div>
            </a>)}
          </div>}
          <div style={{display:"flex",gap:8,marginTop:18,flexWrap:"wrap"}}><span className="aa-s2-chip"><Check size={13}/> country & city context explicit</span><span className="aa-s2-chip"><Package size={13}/> separate product stock</span><span className="aa-s2-chip"><Scissors size={13}/> service booking path</span><span className="aa-s2-chip"><ShoppingBag size={13}/> no checkout action</span></div>
        </div>
      </section>
      <section className="aa-s2-section" style={{paddingBottom:5}}>
        <div className="aa-s2-preview"><span>?</span><span>Native search supports service/category/shop filters; product discovery has separate endpoints and filters. Search here is a local interaction demo and does not imply a unified search API.</span></div>
        <div style={{marginTop:18,display:"flex",gap:10,flexWrap:"wrap"}}><LinkButton page="Homepage" variant="secondary">Back to discovery</LinkButton><LinkButton page="BusinessProfile" variant="soft">Open business profile <ArrowRight size={14}/></LinkButton></div>
      </section>
    </main>
  </PageShell>;
}