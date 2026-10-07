import { useState, type FormEvent } from "react";
import { ArrowRight, ArrowUpRight, BriefcaseBusiness, MapPin, Menu, Package, Search, ShoppingBag, X } from "lucide-react";
import "./_proposal.css";

const DISCOVERY = "/__mockup/preview/agendaally-stage2/Discovery";
const BUSINESS = "/__mockup/preview/agendaally-stage2/BusinessProfile";
const FOR_BUSINESS = "/__mockup/preview/agendaally-stage2/ForBusiness";

function Brand() {
  return <span className="aa-brand"><span className="aa-brand-mark">a.</span><span className="aa-brand-name">AgendaAlly</span></span>;
}

function LocationPicker({ city, onChange }: { city: string; onChange: (city: string) => void }) {
  return <label className="aa-location"><MapPin size={15} /><span>Cameroon</span><span>·</span><select className="aa-city-select" aria-label="Choose city" value={city} onChange={(event) => onChange(event.target.value)}><option>Douala</option><option>Yaoundé</option></select></label>;
}

function DirectoryHeader({ city, setCity }: { city: string; setCity: (city: string) => void }) {
  const [open, setOpen] = useState(false);
  return <>
    <header className="aa-header"><div className="aa-shell aa-header-inner">
      <div className="aa-head-left"><button className="aa-menu" type="button" aria-label="Open navigation menu" aria-expanded={open} onClick={() => setOpen(true)}><Menu size={19} /></button><a className="aa-brand" href={DISCOVERY} aria-label="AgendaAlly home"><Brand /></a><LocationPicker city={city} onChange={setCity} /></div>
      <nav className="aa-nav" aria-label="Main navigation"><a href={DISCOVERY}>City directory</a><a href={`${DISCOVERY}?tab=products`}>Products</a><a href={FOR_BUSINESS}>For business</a></nav>
      <div className="aa-head-actions"><a className="aa-business-link" href={FOR_BUSINESS}>For business <ArrowUpRight size={13} /></a></div>
    </div></header>
    {open && <div className="aa-drawer" onMouseDown={(event) => { if (event.target === event.currentTarget) setOpen(false); }}>
      <nav className="aa-drawer-panel" aria-label="Mobile navigation"><div className="aa-drawer-top"><Brand /><button className="aa-close" onClick={() => setOpen(false)} aria-label="Close navigation"><X /></button></div><LocationPicker city={city} onChange={setCity} /><a href={DISCOVERY}>City business directory <ArrowRight size={15} /></a><a href={DISCOVERY}>Services & specialists <ArrowRight size={15} /></a><a href={`${DISCOVERY}?tab=products`}>Products <ArrowRight size={15} /></a><a href={BUSINESS}>Business profile concept <ArrowRight size={15} /></a><a href={FOR_BUSINESS}>AgendaAlly for Business <ArrowRight size={15} /></a></nav>
    </div>}
  </>;
}

function DirectorySearch({ city }: { city: string }) {
  const [domain, setDomain] = useState<"businesses" | "products">("businesses");
  const [query, setQuery] = useState("");
  const [feedback, setFeedback] = useState("");
  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setFeedback(`${domain === "businesses" ? "Business directory" : "Product"} preview for ${query.trim() || "all"} in ${city}, Cameroon. Services and products use separate discovery paths.`);
  };
  return <form className="aa-search" onSubmit={submit}>
    <div className="aa-tabs" role="tablist" aria-label="Choose a directory"><button className="aa-tab" type="button" role="tab" aria-selected={domain === "businesses"} onClick={() => { setDomain("businesses"); setFeedback(""); }}><BriefcaseBusiness size={14} /> Businesses</button><button className="aa-tab" type="button" role="tab" aria-selected={domain === "products"} onClick={() => { setDomain("products"); setFeedback(""); }}><Package size={14} /> Products</button></div>
    <div className="aa-search-row"><label className="aa-input-wrap"><Search size={17} /><input aria-label={domain === "businesses" ? "Search businesses" : "Search products"} value={query} onChange={(event) => setQuery(event.target.value)} placeholder={domain === "businesses" ? "Business, service or specialist" : "Product or business"} /></label><button type="submit" className="aa-cta">Search directory</button></div>
    <p className="aa-inline-status" role="status">{feedback || `City directory · ${city}, Cameroon · location changes only when selected.`}</p>
  </form>;
}

export function BusinessFirst() {
  const [city, setCity] = useState("Douala");
  const [profileTab, setProfileTab] = useState<"services" | "people" | "products">("services");
  const profileContent = {
    services: ["Services", "A business profile groups its service offerings under one business identity."],
    people: ["Specialists", "Specialists are discoverable as part of the business they work with."],
    products: ["Products", "Products belong to the business profile; a specialist is not presented as a separate shop."],
  }[profileTab];
  return <div className="aa-proposal aa-business">
    <DirectoryHeader city={city} setCity={setCity} />
    <main>
      <section className="aa-shell aa-directory-head">
        <div className="aa-directory-topline"><div className="aa-notice"><span>Isolated Stage 2 proposal · illustrative, not live supply.</span></div><div className="aa-location-line">Showing city context: <strong>{city}, Cameroon</strong></div></div>
        <div className="aa-directory-heading"><div><p className="aa-kicker">AgendaAlly city directory</p><h1 className="aa-title">Discover the businesses shaping your city.</h1></div><p>Start with a business—not a blended search result. See its services, specialists and products in one clear profile.</p></div>
        <div className="aa-parity">
          <a className="aa-path" href={DISCOVERY}><div><span className="aa-path-mark"><BriefcaseBusiness size={18} /></span><h3>Explore businesses & services</h3><p>Browse businesses by service specialty, then understand who and what they offer.</p></div><ArrowUpRight className="aa-arrow" size={18} /></a>
          <a className="aa-path" href={`${DISCOVERY}?tab=products`}><div><span className="aa-path-mark"><ShoppingBag size={18} /></span><h3>Explore products</h3><p>Shop products through their independent business profiles, just as directly.</p></div><ArrowUpRight className="aa-arrow" size={18} /></a>
        </div>
      </section>

      <section className="aa-city-index">
        <div className="aa-shell">
          <div className="aa-index-header"><div><p className="aa-kicker">A directory built around real work</p><h2>What are you looking for?</h2></div><p>These are service taxonomy examples from the existing storefront. They are discovery entry points, not claims about current seller supply in Douala.</p></div>
          <div className="aa-index-layout">
            <div className="aa-index-list">
              {["Hair Care", "Tailoring", "Spa & Massage", "Healthcare", "Dental Care", "Handyman", "Nail Care", "Laundry & Dry Cleaning"].map((name) => <a key={name} className="aa-index-item" href={`${DISCOVERY}?domain=services`}>{name}<ArrowUpRight size={15} /></a>)}
            </div>
            <div className="aa-index-art"><img className="aa-image" src="/__mockup/images/stage2-local-business.jpg" alt="Synthetic illustrative scene of an independent business and customer" /><div className="aa-index-art-caption"><b>Local expertise, in its own context</b><span>AI-generated concept imagery · not evidence of a listed seller.</span></div></div>
          </div>
        </div>
      </section>

      <section className="aa-shell aa-profile-section">
        <div className="aa-section-heading"><div><p className="aa-kicker">The business is the discovery unit</p><h2>One profile. A complete picture.</h2></div><p>Profile preview anatomy, not a fictitious merchant. A real profile can connect identity, branches, services, specialists and products.</p></div>
        <article className="aa-profile-card">
          <div className="aa-profile-image"><img className="aa-image" src="/__mockup/images/stage2-learning.jpg" alt="Synthetic illustration of two people discussing expertise" /><span className="aa-proposal-tag">Synthetic concept art · not profile media</span></div>
          <div className="aa-profile-body">
            <div className="aa-profile-overline"><span>Business profile · proposed</span><span className="aa-small-note">No live listing shown</span></div>
            <h2>Meet the people and work behind a business.</h2>
            <p>A single place to understand what the business offers before choosing a service or product. Identity, branches and real business media would come from its profile data.</p>
            <div className="aa-profile-tabs" role="tablist" aria-label="Business profile sections">
              <button type="button" role="tab" aria-selected={profileTab === "services"} onClick={() => setProfileTab("services")}>Services</button>
              <button type="button" role="tab" aria-selected={profileTab === "people"} onClick={() => setProfileTab("people")}>Specialists</button>
              <button type="button" role="tab" aria-selected={profileTab === "products"} onClick={() => setProfileTab("products")}>Products</button>
            </div>
            <div className="aa-profile-panel" role="tabpanel"><strong>{profileContent[0]}</strong>{profileContent[1]}</div>
            <div className="aa-profile-actions"><a className="aa-cta" href={BUSINESS}>View profile concept</a><a href={DISCOVERY}>Browse directory <ArrowRight size={14} /></a></div>
          </div>
        </article>
      </section>

      <section className="aa-searchband">
        <div className="aa-shell aa-searchband-inner"><div><p className="aa-kicker">Search the city, by domain</p><h2>Find a business to begin with.</h2><p>Services and products stay separate, supported discovery journeys.</p></div><DirectorySearch city={city} /></div>
      </section>
      <section className="aa-shell aa-section">
        <div className="aa-section-heading"><div><p className="aa-kicker">Two doors into the same city</p><h2>Choose your way to discover.</h2></div><p>Both pathways stay visible from the first screen. Business profiles connect the marketplace without merging search behavior.</p></div>
        <div className="aa-parity">
          <a className="aa-path" href={DISCOVERY}><div><span className="aa-path-mark"><BriefcaseBusiness size={20} /></span><h3>Services & specialists</h3><p>Find business expertise by service, then discover its people.</p></div><ArrowRight className="aa-arrow" /></a>
          <a className="aa-path" href={`${DISCOVERY}?tab=products`}><div><span className="aa-path-mark"><ShoppingBag size={20} /></span><h3>Products from businesses</h3><p>Browse product discovery as its own destination in the marketplace.</p></div><ArrowRight className="aa-arrow" /></a>
        </div>
      </section>
    </main>
    <footer className="aa-footer"><div className="aa-shell aa-footer-inner"><div className="aa-footer-brand"><a href={DISCOVERY} aria-label="AgendaAlly home"><Brand /></a><p>A city directory of independent business.</p><p className="aa-small-note">Isolated concept · no live seller supply represented.</p></div><nav className="aa-footer-links" aria-label="Footer"><a href={DISCOVERY}>Directory</a><a href={`${DISCOVERY}?tab=products`}>Products</a><a href={BUSINESS}>Business profile</a><a href={FOR_BUSINESS}>For business</a></nav></div></footer>
  </div>;
}