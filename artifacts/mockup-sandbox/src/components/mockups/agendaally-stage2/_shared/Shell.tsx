import { useEffect, useRef, useState, useSyncExternalStore } from "react";
import type { ReactNode } from "react";
import { ArrowRight, MapPin, Menu, UserRound, X } from "lucide-react";
import "../_group.css";

const root = "/__mockup/preview/agendaally-stage2/";
const defaultLocation = { city: "Douala", country: "Cameroon" };
const locationKey = "agendaally-stage2-prototype-location";
const citiesByCountry: Record<string, string[]> = {
  Cameroon: ["Douala", "Yaoundé"], Ghana: ["Accra", "Kumasi"],
  Kenya: ["Nairobi", "Mombasa"], Nigeria: ["Lagos", "Abuja"],
};
let locationReadError = false;
let hasRememberedLocation = false;
function readPrototypeLocation(): { city: string; country: string } {
  if (typeof window === "undefined") return defaultLocation;
  try {
    const raw = window.localStorage.getItem(locationKey);
    if (!raw) return defaultLocation;
    const saved = JSON.parse(raw);
    if (typeof saved.country === "string" && citiesByCountry[saved.country]?.includes(saved.city)) {
      hasRememberedLocation = true;
      return { city: saved.city, country: saved.country };
    }
    locationReadError = true;
  } catch { locationReadError = true; }
  return defaultLocation;
}
let mockLocation = readPrototypeLocation();
const locationListeners = new Set<() => void>();
function subscribeLocation(listener: () => void) {
  locationListeners.add(listener);
  return () => locationListeners.delete(listener);
}
function setMockLocation(value: { city: string; country: string }) {
  mockLocation = value;
  locationListeners.forEach(listener => listener());
}
export function useMockLocation() {
  return useSyncExternalStore(subscribeLocation, () => mockLocation, () => defaultLocation);
}

export function Brand({ compact = false, dark = false, business = false }: { compact?: boolean; dark?: boolean; business?: boolean }) {
  return <span className={`aa-s2-brand ${dark ? "aa-s2-brand-dark" : ""}`} aria-label={business ? "AgendaAlly for Business" : "AgendaAlly"}>
    <span className="aa-s2-mark" aria-hidden="true">a.</span>{!compact && <span className="aa-s2-brand-name">Agenda<em>Ally</em>{business && <small style={{ display:"block",fontSize:9,letterSpacing:".08em",marginTop:4,color:"var(--muted)" }}>FOR BUSINESS</small>}</span>}
  </span>;
}

export function LinkButton({ page, children, variant = "primary", ariaLabel }: { page: string; children: ReactNode; variant?: "primary" | "secondary" | "soft"; ariaLabel?: string }) {
  const cls = variant === "secondary" ? "aa-s2-btn-secondary" : variant === "soft" ? "aa-s2-btn-soft" : "aa-s2-btn-primary";
  const href = page === "Stage1CustomerLogin" ? "/__mockup/preview/agendaally-stage1/CustomerLogin"
    : page === "Stage1BusinessLogin" ? "/__mockup/preview/agendaally-stage1/BusinessLogin"
    : page === "BrandSystem" ? "/__mockup/preview/agendaally-stage2-art/BrandSystem"
    : page === "CategoryDirections" ? "/__mockup/preview/agendaally-stage2-art/CategoryDirections"
    : `${root}${page}`;
  return <a className={`aa-s2-btn ${cls}`} href={href} aria-label={ariaLabel}>{children}</a>;
}

export function PreviewBadge({ children = "Interactive concept preview. Actions do not submit bookings, orders or account changes." }: { children?: ReactNode }) {
  return <div className="aa-s2-preview" role="note"><span aria-hidden="true">i</span><span>{children}</span></div>;
}

export function PageShell({ children, business = false, title }: { children: ReactNode; business?: boolean; title?: string }) {
  const [menuOpen, setMenuOpen] = useState(false);
  const [locationOpen, setLocationOpen] = useState(false);
  const [country, setCountry] = useState(mockLocation.country);
  const [city, setCity] = useState(mockLocation.city);
  const [draftCountry, setDraftCountry] = useState(country);
  const [draftCity, setDraftCity] = useState(city);
  const [rememberLocation, setRememberLocation] = useState(hasRememberedLocation);
  const [locationError, setLocationError] = useState(locationReadError ? "A saved prototype location could not be read. This view uses Douala until you choose a city." : "");
  const opener = useRef<HTMLButtonElement>(null);
  useEffect(() => { if (title) document.title = `${title} · AgendaAlly`; }, [title]);
  useEffect(() => {
    if (!locationOpen) return;
    const prev = document.activeElement as HTMLElement | null;
    const first = document.getElementById("aa-s2-country");
    first?.focus();
    const onKey = (event: KeyboardEvent) => {
      if (event.key === "Escape") { setLocationOpen(false); requestAnimationFrame(() => opener.current?.focus()); }
      if (event.key === "Tab") {
        const items = Array.from(document.querySelectorAll<HTMLElement>(".aa-s2-modal button, .aa-s2-modal select, .aa-s2-modal input"));
        if (items.length && event.shiftKey && document.activeElement === items[0]) { event.preventDefault(); items[items.length - 1].focus(); }
        else if (items.length && !event.shiftKey && document.activeElement === items[items.length - 1]) { event.preventDefault(); items[0].focus(); }
      }
    };
    window.addEventListener("keydown", onKey);
    return () => { window.removeEventListener("keydown", onKey); if (prev && !locationOpen) opener.current?.focus(); };
  }, [locationOpen]);
  const beginLocation = () => { setDraftCountry(country); setDraftCity(city); setLocationOpen(true); };
  const confirmLocation = () => {
    setCountry(draftCountry); setCity(draftCity);
    setMockLocation({ city: draftCity, country: draftCountry });
    try {
      if (rememberLocation) window.localStorage.setItem(locationKey, JSON.stringify({ city: draftCity, country: draftCountry }));
      else window.localStorage.removeItem(locationKey);
      setLocationError("");
    } catch { setLocationError("City changed in this view, but the browser could not save or clear the prototype preference."); }
    setLocationOpen(false); requestAnimationFrame(() => opener.current?.focus());
  };
  const nav = [
    ["Explore", "Discovery?domain=Services"],
    ["Services", "Discovery?domain=Services"],
    ["Products", "Discovery?domain=Products"],
  ];
  return <div className="aa-s2">
    <div className="aa-s2-topline"><div className="aa-s2-wrap aa-s2-topline-inner"><span className="aa-s2-topline-desktop">Local skill. Independent shops. One city at a time.</span><span className="aa-s2-topline-mobile">{city}, {country}</span><a href={`${root}ForBusiness`}>For business <ArrowRight size={12} aria-hidden="true"/></a></div></div>
    <header className="aa-s2-header">
      <div className="aa-s2-wrap aa-s2-navrow">
        <button className="aa-s2-menu" type="button" aria-label={menuOpen ? "Close menu" : "Open menu"} aria-expanded={menuOpen} onClick={() => setMenuOpen(v => !v)}>{menuOpen ? <X size={19}/> : <Menu size={19}/>}</button>
        <a href={`${root}Homepage`} aria-label="AgendaAlly home"><Brand business={business}/></a>
        <nav className="aa-s2-navlinks" aria-label="Main navigation">{nav.map(([label,page])=><a key={label} href={`${root}${page}`}>{label}</a>)}<a href={`${root}BusinessProfile`}>Businesses</a></nav>
        <div className="aa-s2-navactions">
          <button ref={opener} className="aa-s2-location" type="button" onClick={beginLocation} aria-haspopup="dialog" aria-label={`Change location, currently ${city}, ${country}`}><span className="aa-s2-location-dot"/><MapPin size={14}/><span className="aa-s2-location-full">{city}, {country}</span><span className="aa-s2-location-mobile">{city}</span></button>
          <LinkButton page="Stage1CustomerLogin" variant="secondary" ariaLabel="Customer sign in"><UserRound size={15}/><span className="aa-s2-login">Sign in</span></LinkButton>
          <LinkButton page="ForBusiness" variant="primary" ariaLabel="Open AgendaAlly for Business"><span className="aa-s2-business-cta">For business</span><ArrowRight size={14}/></LinkButton>
        </div>
      </div>
      {menuOpen && <nav className="aa-s2-mobile-panel" aria-label="Mobile navigation">
        <a href={`${root}Discovery?domain=Services`} onClick={()=>setMenuOpen(false)}>Services</a><a href={`${root}Discovery?domain=Products`} onClick={()=>setMenuOpen(false)}>Products</a><a href={`${root}BusinessProfile`} onClick={()=>setMenuOpen(false)}>Find a business</a><a href={`${root}ForBusiness`} onClick={()=>setMenuOpen(false)}>AgendaAlly for Business</a><a href="/__mockup/preview/agendaally-stage1/CustomerLogin" onClick={()=>setMenuOpen(false)}>Sign in</a>
      </nav>}
    </header>
    {locationError && <div className="aa-s2-wrap" role="alert">{locationError}</div>}
    {children}
    <footer className="aa-s2-footer"><div className="aa-s2-wrap">
      <div className="aa-s2-footer-main">
        <div><a href={`${root}Homepage`}><Brand business={business}/></a><p style={{maxWidth:280,fontSize:13,marginTop:16}}>Discover services and independent products from businesses in your city.</p><small>Illustrative proposal · no live supply claims</small></div>
        <div><h3>Discover</h3><div className="aa-s2-footer-col"><a href={`${root}Discovery?domain=Services`}>Services</a><a href={`${root}Discovery?domain=Products`}>Products</a><a href={`${root}BusinessProfile`}>Businesses & specialists</a><a href="/__mockup/preview/agendaally-stage2-art/CategoryDirections">Categories</a></div></div>
        <div><h3>For business</h3><div className="aa-s2-footer-col"><a href={`${root}ForBusiness`}>Business overview</a><a href="/__mockup/preview/agendaally-stage1/BusinessLogin">Business sign in</a><a href={`${root}Support`}>Support</a></div></div>
        <div><h3>Information</h3><div className="aa-s2-footer-col"><a href={`${root}Blog`}>Blog</a><a href={`${root}Article`}>Article format</a><a href={`${root}Terms`}>Terms · development draft</a><a href={`${root}Privacy`}>Privacy · review needed</a><a href={`${root}PaymentStates`}>Payment states</a><a href="/__mockup/preview/agendaally-stage2-art/BrandSystem">Brand system</a><a href="/__mockup/preview/agendaally-stage2-art/CategoryDirections">Category art directions</a><a href={`${root}Review`}>Stage 2 review</a></div></div>
      </div>
      <div className="aa-s2-footer-bottom"><span>© AgendaAlly · visual proposal only</span><span>Illustrative / AI-generated imagery used for layout exploration.</span></div>
    </div></footer>
    {locationOpen && <div className="aa-s2-modal-backdrop" role="presentation" onMouseDown={(event)=>{if(event.target===event.currentTarget){setLocationOpen(false);requestAnimationFrame(()=>opener.current?.focus());}}}>
      <section className="aa-s2-modal" role="dialog" aria-modal="true" aria-labelledby="aa-s2-location-heading">
        <div className="aa-s2-modal-head"><div><span className="aa-s2-eyebrow">Your discovery context</span><h2 id="aa-s2-location-heading" style={{fontSize:25,margin:"8px 0"}}>Choose a city</h2><p className="aa-s2-muted" style={{fontSize:13,margin:0}}>Changes apply only to demonstration results. No GPS, native country, shop or branch permissions change.</p></div><button type="button" className="aa-s2-icon-btn" aria-label="Close location selector" onClick={()=>{setLocationOpen(false);requestAnimationFrame(()=>opener.current?.focus());}}><X size={18}/></button></div>
        <label htmlFor="aa-s2-country" style={{display:"block",marginTop:22,fontWeight:700,fontSize:13}}>Country</label><select className="aa-s2-select" id="aa-s2-country" value={draftCountry} onChange={e=>{setDraftCountry(e.target.value);setDraftCity(citiesByCountry[e.target.value][0]);}}><option>Cameroon</option><option>Ghana</option><option>Kenya</option><option>Nigeria</option></select>
        <label htmlFor="aa-s2-city" style={{display:"block",marginTop:15,fontWeight:700,fontSize:13}}>City</label><select className="aa-s2-select" id="aa-s2-city" value={draftCity} onChange={e=>setDraftCity(e.target.value)}>{citiesByCountry[draftCountry].map(x=><option key={x}>{x}</option>)}</select>
        <label style={{display:"flex",alignItems:"center",gap:10,minHeight:44,fontSize:14,marginTop:14}}><input type="checkbox" checked={rememberLocation} onChange={e=>setRememberLocation(e.target.checked)}/>Remember this city for this prototype in this browser</label>
        <p className="aa-s2-muted" style={{fontSize:12}}>Opt-in preview preference only. Uncheck and confirm to clear it; native persistence remains subject to integration approval.</p>
         <div className="aa-s2-modal-actions"><button type="button" className="aa-s2-btn aa-s2-btn-secondary" onClick={()=>{setLocationOpen(false);requestAnimationFrame(()=>opener.current?.focus());}}>Cancel</button><button type="button" className="aa-s2-btn aa-s2-btn-primary" onClick={confirmLocation}>Confirm city</button></div>
      </section>
    </div>}
  </div>;
}