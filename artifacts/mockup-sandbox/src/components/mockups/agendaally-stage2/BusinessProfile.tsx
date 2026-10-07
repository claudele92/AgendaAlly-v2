import "./_group.css";
import "./_profile.css";
import { useState } from "react";
import { ArrowRight, Clock3, MapPin, MessageCircle, PackageCheck, Phone, ShoppingBag, Star, Store, UserRound } from "lucide-react";
import { LinkButton, PageShell, PreviewBadge } from "./_shared/Shell";

export function BusinessProfile() {
  const [notice,setNotice] = useState("");
  const [branch,setBranch] = useState<"Location 2"|"Location 4">("Location 2");
  return <PageShell title="Business profile · Le Sawa Beauty Studio">
    <main className="aa-s2-wrap" style={{paddingTop:27}}>
      <div style={{display:"flex",alignItems:"center",gap:8,fontSize:12,color:"var(--muted)",marginBottom:17}}><a href="/__mockup/preview/agendaally-stage2/Homepage">Home</a><span>/</span><a href="/__mockup/preview/agendaally-stage2/Discovery?domain=Businesses">Businesses</a><span>/</span><span>Le Sawa Beauty Studio</span></div>
      <section className="aa-s2-profile-cover">
        <img src="https://images.unsplash.com/photo-1746723378067-83a345ff3160?auto=format&fit=crop&w=1200&q=80" alt="Seeded shop background_img photo; subject and fit have not been independently verified" />
        <div className="aa-s2-profile-summary">
          <div style={{display:"flex",alignItems:"center",gap:11,marginBottom:5}}>
            <img className="aa-s2-logo-photo" src="https://images.unsplash.com/photo-1600948836101-f9ffda59d250?auto=format&fit=crop&w=400&h=400&q=80" alt="Seeded shop logo_img photo; not verified as a wordmark" style={{width:54,height:54,borderRadius:10,objectFit:"cover",border:"1px solid var(--line)"}} />
            <span className="aa-s2-visual-credit">Seeded logo-slot photo · not a verified wordmark</span>
          </div>
          <span className="aa-s2-eyebrow">Synthetic development seed · shop 501 · le-sawa-beauty-studio-501</span><h1 style={{fontSize:37,margin:"9px 0 7px",lineHeight:1.08}}>Le Sawa Beauty Studio</h1>
          <p className="aa-s2-muted" style={{fontSize:14,margin:"0 0 14px"}}>A modern beauty studio in the heart of Douala, offering hair, nail, and spa services for the whole family.</p>
          <span className="aa-s2-chip"><Star size={13}/> No public ratings shown in this fixture</span>
          <p className="aa-s2-visual-credit" style={{margin:"12px 0 0"}}>Seeded background-image slot · image subject, fit and usage rights have not been independently verified.</p>
        </div>
      </section>
      <div style={{margin:"17px 0"}}><PreviewBadge>This is a public synthetic development profile, not a verified operating business. The seeded logo and cover URLs are shown in their source slots; image subject, fit and third-party rights remain unverified. No branch street addresses, aliases or shop-gallery media are linked in the fixture.</PreviewBadge></div>
      <section className="aa-s2-profile-grid">
        <div>
          <article className="aa-s2-panel">
            <div className="aa-s2-section-head" style={{marginBottom:6}}><div><span className="aa-s2-eyebrow">About the business</span><h2 style={{fontSize:25}}>A clearer view of what this shop offers.</h2></div></div>
            <p className="aa-s2-muted" style={{fontSize:14}}>{`A modern beauty studio in the heart of Douala, offering hair, nail, and spa services for the whole family.`}</p>
            <p className="aa-s2-visual-credit" style={{margin:"12px 0 0"}}>No shop-gallery relationships or gallery media are linked in this fixture. The cover above is the seeded background image, not gallery content.</p>
          </article>
          <article className="aa-s2-panel" style={{marginTop:15}}>
            <div className="aa-s2-section-head" style={{marginBottom:2}}><div><span className="aa-s2-eyebrow">Services · separate booking path</span><h2 style={{fontSize:24}}>Service menu</h2></div><span className="aa-s2-chip">Catalog reference</span></div>
            <div className="aa-s2-service-row"><div><strong>Haircut</strong><div className="aa-s2-muted" style={{fontSize:12}}>Representative service example · not supplied by this profile fixture.</div></div><button type="button" className="aa-s2-btn aa-s2-btn-secondary" onClick={()=>setNotice("Booking begins in the existing AgendaAlly flow. No appointment is created from this visual proposal.")}>View booking path <ArrowRight size={14}/></button></div>
            <div className="aa-s2-service-row"><div><strong>Other services</strong><div className="aa-s2-muted" style={{fontSize:12}}>No service catalog is included in this profile fixture.</div></div><span className="aa-s2-chip">Not supplied</span></div>
          </article>
          <article className="aa-s2-panel" style={{marginTop:15}}>
            <div className="aa-s2-section-head" style={{marginBottom:5}}><div><span className="aa-s2-eyebrow">Products · distinct catalog</span><h2 style={{fontSize:24}}>Shop products</h2></div><ShoppingBag size={19} color="var(--bronze)"/></div>
            <div className="aa-s2-service-row"><div><strong>Moroccan Argan Oil Hair Serum</strong><div className="aa-s2-muted" style={{fontSize:12}}>Representative product example · not supplied by this profile fixture.</div></div><button type="button" className="aa-s2-btn aa-s2-btn-soft" onClick={()=>setNotice("Product browsing is illustrated only; no cart or checkout operation is performed.")}>Browse item <ArrowRight size={14}/></button></div>
            <div className="aa-s2-preview" style={{marginTop:10}}><PackageCheck size={15}/><span>Inventory status and variant availability are not asserted by this card.</span></div>
          </article>
          <article className="aa-s2-panel" style={{marginTop:15}}>
            <div className="aa-s2-section-head" style={{marginBottom:4}}><div><span className="aa-s2-eyebrow">People</span><h2 style={{fontSize:24}}>Specialists</h2></div></div>
            <div className="aa-s2-person"><span className="aa-s2-initial">AF</span><div><strong>Armand Fotso</strong><div className="aa-s2-muted" style={{fontSize:12}}>Representative specialist example · not supplied by this profile fixture.</div></div></div>
          </article>
          <article className="aa-s2-panel" style={{marginTop:15}}>
            <div style={{display:"flex",alignItems:"center",gap:9}}><Star size={17} color="var(--bronze)"/><h2 style={{fontSize:22,margin:0}}>Reviews</h2></div><p className="aa-s2-muted" style={{marginBottom:0}}>Review and rating data are not included in this fixture; no score or count is shown.</p>
          </article>
        </div>
        <aside>
          <article className="aa-s2-panel">
            <span className="aa-s2-eyebrow">Location</span><h2 style={{fontSize:22,margin:"7px 0 14px"}}>Branch and shop address.</h2>
            <div style={{padding:"13px 0",borderBottom:"1px solid var(--line)"}}><div style={{display:"flex",alignItems:"center",gap:7,fontWeight:700,fontSize:13}}><MapPin size={15} color="var(--bronze)"/>City-matched branch</div><p style={{fontSize:13,margin:"7px 0 3px"}}>Selected branch · {branch} · Douala, Cameroon</p><small className="aa-s2-muted">Branch street address: not supplied. Branch alias: not supplied.</small>
              <div style={{display:"flex",gap:7,marginTop:11}}>{(["Location 2","Location 4"] as const).map(location=><button key={location} className={`aa-s2-btn ${branch===location?"aa-s2-btn-primary":"aa-s2-btn-secondary"}`} type="button" aria-pressed={branch===location} style={{minHeight:36,padding:"0 11px",fontSize:11}} onClick={()=>setBranch(location)}>{location} · Douala</button>)}</div>
            </div>
            <div style={{padding:"13px 0 2px"}}><div style={{display:"flex",alignItems:"center",gap:7,fontWeight:700,fontSize:13}}><Store size={15} color="var(--bronze)"/>Shop-level public address</div><p style={{fontSize:13,margin:"7px 0 3px"}}>12 Rue de la Joie, Bonanjo, Douala, Cameroon</p><small className="aa-s2-muted">This translated shop address is not confirmed as the street address of either selected branch.</small></div>
            <p style={{fontSize:10,color:"var(--muted)",margin:"14px 0 0"}}>Four seeded branches exist (Yaoundé/Douala, locations 1–4); each branch street address and alias is null. Type-code meaning is not asserted.</p>
          </article>
          <article className="aa-s2-panel" style={{marginTop:14}}>
            <span className="aa-s2-eyebrow">Hours & contact</span><h2 style={{fontSize:21,margin:"7px 0 10px"}}>Details from the business</h2>
            <p className="aa-s2-muted" style={{fontSize:12}}>Hours are not included in this profile fixture.</p>
            <button type="button" className="aa-s2-btn aa-s2-btn-secondary" style={{width:"100%",justifyContent:"flex-start",marginTop:4}} onClick={()=>setNotice("Contact details are not configured in this prototype. No call or message was started.")}><Phone size={15}/> Phone · unavailable in preview</button>
            <button type="button" className="aa-s2-btn aa-s2-btn-secondary" style={{width:"100%",justifyContent:"flex-start",marginTop:9}} onClick={()=>setNotice("Messaging is not available in this visual proposal.")}><MessageCircle size={15}/> Message · unavailable in preview</button>
          </article>
          <article className="aa-s2-panel" style={{marginTop:14}}>
            <span className="aa-s2-eyebrow">Task actions</span>
            <button type="button" className="aa-s2-btn aa-s2-btn-primary" style={{width:"100%",marginTop:10}} onClick={()=>setNotice("Booking is completed only through the existing service booking journey, not this prototype.")}><CalendarDaysIcon/> Explore appointment</button>
            <button type="button" className="aa-s2-btn aa-s2-btn-soft" style={{width:"100%",marginTop:9}} onClick={()=>setNotice("Shopping and payment are not executed in this prototype.")}><ShoppingBag size={15}/> Browse products</button>
            {notice&&<p role="status" style={{fontSize:12,color:"var(--bronze-deep)",padding:10,background:"var(--bronze-pale)",borderRadius:8,margin:"12px 0 0"}}>{notice}</p>}
          </article>
          <article className="aa-s2-panel" style={{marginTop:14}}>
            <div style={{display:"flex",gap:8,alignItems:"center"}}><Clock3 size={16} color="var(--bronze)"/><span style={{fontWeight:700,fontSize:13}}>Hours not supplied</span></div>
            <p className="aa-s2-muted" style={{fontSize:11,marginBottom:0}}>No open-now status or live appointment slots are implied.</p>
          </article>
        </aside>
      </section>
      <section className="aa-s2-section" style={{paddingBottom:0}}>
        <div style={{display:"flex",gap:10,flexWrap:"wrap"}}><LinkButton page="Discovery?domain=Services" variant="secondary"><UserRound size={14}/> Back to discovery</LinkButton><LinkButton page="Review" variant="soft">Review the profile proposal <ArrowRight size={14}/></LinkButton></div>
      </section>
    </main>
  </PageShell>;
}
function CalendarDaysIcon() { return <Clock3 size={15} aria-hidden="true"/>; }