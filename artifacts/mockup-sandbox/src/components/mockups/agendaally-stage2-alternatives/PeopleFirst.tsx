import { useState, type FormEvent } from "react";
import { ArrowDownRight, ArrowRight, ArrowUpRight, BriefcaseBusiness, MapPin, Menu, Search, ShoppingBag, Sparkles, X } from "lucide-react";
import "./_proposal.css";

const DISCOVERY = "/__mockup/preview/agendaally-stage2/Discovery";
const BUSINESS = "/__mockup/preview/agendaally-stage2/BusinessProfile";
const FOR_BUSINESS = "/__mockup/preview/agendaally-stage2/ForBusiness";

function Brand() {
  return <span className="aa-brand"><span className="aa-brand-mark">a.</span><span className="aa-brand-name">AgendaAlly</span></span>;
}

function Location({ city, setCity }: { city: string; setCity: (city: string) => void }) {
  return <label className="aa-location" aria-label="Current location, Cameroon">
    <MapPin size={16} aria-hidden="true" />
    <span>Cameroon</span>
    <span aria-hidden="true">·</span>
    <select className="aa-city-select" value={city} onChange={(event) => setCity(event.target.value)} aria-label="Choose city">
      <option>Douala</option><option>Yaoundé</option>
    </select>
  </label>;
}

function Header({ city, setCity }: { city: string; setCity: (city: string) => void }) {
  const [drawer, setDrawer] = useState(false);
  return <>
    <header className="aa-header">
      <div className="aa-shell aa-header-inner">
        <div className="aa-head-left">
          <button className="aa-menu" type="button" aria-label="Open navigation menu" aria-expanded={drawer} onClick={() => setDrawer(true)}><Menu size={19} /></button>
          <a className="aa-brand" href={DISCOVERY} aria-label="AgendaAlly home"><Brand /></a>
          <Location city={city} setCity={setCity} />
        </div>
        <nav className="aa-nav" aria-label="Main navigation">
          <a href={DISCOVERY}>Discover</a><a href={`${DISCOVERY}?tab=products`}>Products</a><a href={FOR_BUSINESS}>For business</a>
        </nav>
        <div className="aa-head-actions"><a className="aa-business-link" href={FOR_BUSINESS}>For business <ArrowUpRight size={13} /></a></div>
      </div>
    </header>
    {drawer && <div className="aa-drawer" role="presentation" onMouseDown={(event) => { if (event.target === event.currentTarget) setDrawer(false); }}>
      <nav className="aa-drawer-panel" aria-label="Mobile navigation">
        <div className="aa-drawer-top"><Brand /><button className="aa-close" onClick={() => setDrawer(false)} aria-label="Close navigation"><X /></button></div>
        <Location city={city} setCity={setCity} />
        <a href={DISCOVERY}>Discover specialists & services <ArrowRight size={15} /></a>
        <a href={`${DISCOVERY}?tab=products`}>Shop products <ArrowRight size={15} /></a>
        <a href={BUSINESS}>Explore business profiles <ArrowRight size={15} /></a>
        <a href={FOR_BUSINESS}>AgendaAlly for Business <ArrowRight size={15} /></a>
      </nav>
    </div>}
  </>;
}

function SearchBox({ city }: { city: string }) {
  const [tab, setTab] = useState<"services" | "products">("services");
  const [query, setQuery] = useState("");
  const [status, setStatus] = useState("");
  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setStatus(`${tab === "services" ? "Service" : "Product"} discovery preview for ${query.trim() || "all"} in ${city}, Cameroon. Search APIs remain separate.`);
  };
  return <form className="aa-search" onSubmit={submit}>
    <div className="aa-tabs" role="tablist" aria-label="Choose a discovery domain">
      <button type="button" className="aa-tab" role="tab" aria-selected={tab === "services"} onClick={() => { setTab("services"); setStatus(""); }}><BriefcaseBusiness size={14} /> Services</button>
      <button type="button" className="aa-tab" role="tab" aria-selected={tab === "products"} onClick={() => { setTab("products"); setStatus(""); }}><ShoppingBag size={14} /> Products</button>
    </div>
    <div className="aa-search-row">
      <label className="aa-input-wrap"><Search size={17} /><input value={query} onChange={(event) => setQuery(event.target.value)} aria-label={tab === "services" ? "Search services or specialists" : "Search products"} placeholder={tab === "services" ? "Service, skill or specialist" : "Product or independent business"} /></label>
      <button className="aa-cta" type="submit">Explore {tab}</button>
    </div>
    <p className="aa-inline-status" role="status">{status || `Browsing ${tab} in ${city}, Cameroon · location changes only when you choose.`}</p>
  </form>;
}

export function PeopleFirst() {
  const [city, setCity] = useState("Douala");
  return <div className="aa-proposal aa-people">
    <Header city={city} setCity={setCity} />
    <main>
      <section className="aa-shell aa-hero">
        <div className="aa-hero-grid">
          <div className="aa-hero-copy">
            <div className="aa-notice"><Sparkles size={14} /> Isolated Stage 2 concept · illustrative prototype, not live marketplace supply.</div>
            <p className="aa-kicker" style={{ marginTop: 24 }}>People, practice, place</p>
            <h1 className="aa-title">Meet local expertise.<br /><em>Shop local essentials.</em></h1>
            <p>Start with the person behind the work. Explore specialists through the businesses they belong to, or take an equally direct path to products.</p>
            <div className="aa-hero-actions">
              <a href={DISCOVERY}>Meet specialists <ArrowRight size={15} /></a>
              <a href={`${DISCOVERY}?tab=products`}>Shop products <ArrowRight size={15} /></a>
            </div>
          </div>
          <div className="aa-hero-art">
            <div className="aa-hero-photo"><img className="aa-image" src="/__mockup/images/stage2-local-business.jpg" alt="Illustrative synthetic scene of a local business owner welcoming a customer" /></div>
            <span className="aa-photo-label">Synthetic promotional scene · concept art</span>
            <div className="aa-art-caption"><b>Expertise has a home</b><span>People are discoverable in the context of their business.</span></div>
          </div>
        </div>
        <div className="aa-search-zone">
          <div className="aa-location-line"><span>Choose a starting point</span><span>Current city: <strong>{city}, Cameroon</strong></span></div>
          <SearchBox city={city} />
        </div>
      </section>

      <section className="aa-expertise">
        <div className="aa-shell aa-section">
          <div className="aa-expertise-grid">
            <div className="aa-expertise-intro"><p className="aa-kicker">Find your kind of expertise</p><h2>Good work starts with the right person.</h2><p>Browse service specialties, then compare the specialists and businesses that offer them. Category names reflect the existing service taxonomy; no live availability or ratings are implied here.</p><a className="aa-text-link" href={DISCOVERY}>Explore service specialties <ArrowRight size={15} /></a></div>
            <div className="aa-expertise-list">
              {[
                ["Hair Care", "Care, styling and expertise"],
                ["Tailoring", "Made-to-measure and alterations"],
                ["Spa & Massage", "Wellbeing and restorative care"],
                ["Healthcare", "Health professionals and services"],
                ["Handyman", "Practical help for home and work"],
                ["Dental Care", "Dental services and care"],
              ].map(([name, note]) => <a className="aa-expertise-item" href={`${DISCOVERY}?domain=services`} key={name}><span><b>{name}</b><span>{note}</span></span><ArrowUpRight size={17} /></a>)}
            </div>
          </div>
        </div>
      </section>

      <section className="aa-shell aa-section">
        <div className="aa-connection">
          <div className="aa-connection-visual"><img className="aa-image" src="/__mockup/images/stage2-learning.jpg" alt="Illustrative synthetic image of two people sharing expertise" /><span className="aa-proposal-tag">Synthetic promotional scene · not a profile portrait</span></div>
          <div className="aa-connection-copy"><p className="aa-kicker">People in context</p><h2>Compare specialists through the businesses behind them.</h2><p>A specialist is part of a real business profile—not a separate storefront. The business is where its services, people and products come together, so discovery keeps those relationships clear.</p>
            <div className="aa-relation" aria-label="Specialist belongs to a business, which offers services and products"><span>Specialist</span><span className="aa-rel-arrow">›</span><span>Business</span><span className="aa-rel-arrow">›</span><span>Services + products</span></div>
            <a className="aa-text-link" style={{ marginTop: 20 }} href={BUSINESS}>See the business profile concept <ArrowRight size={15} /></a>
          </div>
        </div>
      </section>

      <section className="aa-products">
        <div className="aa-shell aa-section">
          <div className="aa-products-grid">
            <div className="aa-product-image"><img className="aa-image" src="/__mockup/images/stage2-products.jpg" alt="Illustrative synthetic product scene featuring a comb and amber bottle" /></div>
            <div className="aa-product-copy"><p className="aa-kicker">A second way in</p><h2>Products deserve their own front door.</h2><p>Shop products from independent businesses with the same clear prominence as service discovery. Product search remains its own supported path—not one combined search with services.</p><a className="aa-text-link" href={`${DISCOVERY}?tab=products`}>Browse products <ArrowRight size={15} /></a></div>
          </div>
        </div>
      </section>
      <section className="aa-shell aa-section">
        <div className="aa-section-heading"><div><p className="aa-kicker">One marketplace, two clear paths</p><h2>Start where your need starts.</h2></div><p>Choose a domain first. The next step stays focused on services or products, with your chosen city visible throughout.</p></div>
        <div className="aa-parity">
          <a className="aa-path" href={DISCOVERY}><div><span className="aa-path-mark"><BriefcaseBusiness size={20} /></span><h3>Find services & specialists</h3><p>Explore service categories and discover the businesses and professionals offering them.</p></div><ArrowDownRight className="aa-arrow" /></a>
          <a className="aa-path" href={`${DISCOVERY}?tab=products`}><div><span className="aa-path-mark"><ShoppingBag size={20} /></span><h3>Shop independent products</h3><p>Discover products through the businesses that make them available.</p></div><ArrowDownRight className="aa-arrow" /></a>
        </div>
      </section>
    </main>
    <footer className="aa-footer"><div className="aa-shell aa-footer-inner"><div className="aa-footer-brand"><a href={DISCOVERY} aria-label="AgendaAlly home"><Brand /></a><p>Local discovery, grounded in real businesses.</p><p className="aa-small-note">Concept proposal · synthetic imagery · no live supply represented.</p></div><nav className="aa-footer-links" aria-label="Footer"><a href={DISCOVERY}>Discover</a><a href={`${DISCOVERY}?tab=products`}>Products</a><a href={BUSINESS}>Business profiles</a><a href={FOR_BUSINESS}>For business</a></nav></div></footer>
  </div>;
}