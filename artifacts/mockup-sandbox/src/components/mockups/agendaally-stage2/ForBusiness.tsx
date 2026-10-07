import "./_group.css";
import { ArrowRight, Bell, CalendarDays, Check, CreditCard, Package, UsersRound, Settings2, Store, ClipboardList } from "lucide-react";
import { LinkButton, PageShell, PreviewBadge } from "./_shared/Shell";

const capabilities = [
  {icon:CalendarDays,title:"Online booking",copy:"Present the customer booking entry point without promising unverified supply or outcomes."},
  {icon:ClipboardList,title:"Management",copy:"Keep services, appointments and customer records in the view of the people running the shop."},
  {icon:CreditCard,title:"Payment",copy:"Payment methods and availability depend on the configured catalog and protected checkout state."},
  {icon:Bell,title:"Notifications",copy:"Show notification settings as a topic, not a guarantee of message delivery."},
];

export function ForBusiness() {
  return <PageShell business title="AgendaAlly for Business">
    <main>
      <section className="aa-s2-business-hero">
        <div className="aa-s2-wrap aa-s2-business-hero-grid">
          <div>
            <span className="aa-s2-eyebrow">AgendaAlly for Business</span>
            <h1>Appointments, services and people—clearer in one workday.</h1>
            <p className="aa-s2-muted" style={{fontSize:15,maxWidth:510}}>A practical workspace for independent service businesses: see scheduled work, manage service offerings and keep customer details in reach.</p>
            <div style={{display:"flex",gap:10,flexWrap:"wrap",marginTop:22}}><LinkButton page="Stage1BusinessLogin">Get started <ArrowRight size={15}/></LinkButton><LinkButton page="Homepage" variant="secondary">Explore as a customer</LinkButton></div>
            <p style={{fontSize:11,color:"var(--muted)",marginTop:11}}>Sign-in leads to the isolated approved Stage 1 visual preview; no application is submitted.</p>
          </div>
          <div>
            <div className="aa-s2-calendar" aria-label="Illustrative calendar concept grounded in native seller Calendar data shape">
              <div className="aa-s2-cal-head"><div><span className="aa-s2-eyebrow">AgendaAlly for Business</span><div style={{fontSize:15,marginTop:2}}>Calendar · Day view</div></div><span className="aa-s2-chip">Illustrative</span></div>
              <div className="aa-s2-cal-body">
                <div className="aa-s2-cal-times">{["09:00","09:30","10:00","10:30","11:00","11:30"].map(x=><span key={x}>{x}</span>)}</div>
                <div className="aa-s2-cal-grid">
                  <div className="aa-s2-cal-event"><strong>Haircut</strong><br/>09:00–09:30 · Armand Fotso<br/><span>Booking status · sample</span></div>
                  <div className="aa-s2-cal-event"><strong>Haircut</strong><br/>10:00–10:30 · service master<br/><span>Booking status · sample</span></div>
                  <div className="aa-s2-cal-event"><strong>Blocked time</strong><br/>11:00–11:30<br/><span>Disabled time · sample</span></div>
                </div>
              </div>
              <div className="aa-s2-calendar-note">Concept only · native seller day view, 30-minute step · no live bookings or calendar connection</div>
            </div>
            <p className="aa-s2-visual-credit" style={{textAlign:"right",margin:"9px 2px 0"}}>Illustrative / AI-generated image: none used for calendar; interface is a schematic proposal.</p>
          </div>
        </div>
      </section>
      <section className="aa-s2-wrap aa-s2-section">
        <div className="aa-s2-section-head"><div><span className="aa-s2-eyebrow">A grounded feature story</span><h2>Built around the working day—not a hotel reservation grid.</h2><p className="aa-s2-muted" style={{maxWidth:640}}>The native seller Calendar models bookings and disabled times, service titles, start/end times, status and a service master. The visual above follows that shape; it does not add rooms, occupancy, branches or product events.</p></div></div>
        <div className="aa-s2-business-value-grid">{capabilities.map(({icon:Icon,title,copy})=><article className="aa-s2-value" key={title}><span className="aa-s2-cat-glyph"><Icon size={18}/></span><h3 style={{fontSize:17,margin:"13px 0 7px"}}>{title}</h3><p className="aa-s2-muted" style={{fontSize:12,margin:0}}>{copy}</p></article>)}</div>
      </section>
      <section className="aa-s2-wrap aa-s2-section">
        <div className="aa-s2-feature-grid">
          <article className="aa-s2-panel" style={{minHeight:250}}>
            <span className="aa-s2-eyebrow">Services & team</span><h2 style={{fontSize:27}}>Shape a service menu, then keep the people behind it visible.</h2>
            <div className="aa-s2-pill-row" style={{margin:"14px 0"}}><span className="aa-s2-chip"><UsersRound size={13}/> Specialists & staff</span><span className="aa-s2-chip"><CalendarDays size={13}/> Bookings & schedules</span><span className="aa-s2-chip"><Settings2 size={13}/> Service setup</span></div>
            <p className="aa-s2-muted" style={{fontSize:13}}>Representative product family capabilities only; permissions and existing workspace behavior remain unchanged.</p>
          </article>
          <article className="aa-s2-panel" style={{minHeight:250,background:"var(--olive-pale)"}}>
            <span className="aa-s2-eyebrow">Independent commerce</span><h2 style={{fontSize:27}}>Products and branches stay in their own lanes.</h2>
            <div className="aa-s2-pill-row" style={{margin:"14px 0"}}><span className="aa-s2-chip"><Package size={13}/> Products & stock variants</span><span className="aa-s2-chip"><Store size={13}/> Business branches</span></div>
            <p className="aa-s2-muted" style={{fontSize:13}}>The seller Calendar event model does not make product stock or branch filtering a calendar feature.</p>
          </article>
        </div>
      </section>
      <section className="aa-s2-wrap aa-s2-section">
        <div className="aa-s2-section-head"><div><span className="aa-s2-eyebrow">What stays honest</span><h2>Capability topics, without invented promises.</h2></div><LinkButton page="PaymentStates" variant="secondary">Payment states <ArrowRight size={14}/></LinkButton></div>
        <div className="aa-s2-panel">
          <div className="aa-s2-service-row"><strong>Booking & schedule</strong><span className="aa-s2-muted" style={{fontSize:12}}>Keep · show native day-view idiom</span></div>
          <div className="aa-s2-service-row"><strong>Management / payment / notifications</strong><span className="aa-s2-muted" style={{fontSize:12}}>Keep as topics · qualify availability and outcomes</span></div>
          <div className="aa-s2-service-row"><strong>Rental-room calendar graphic</strong><span className="aa-s2-muted" style={{fontSize:12}}>Replace · not native appointment data</span></div>
          <div className="aa-s2-service-row"><strong>Unverified partner-growth claims & fixed metrics</strong><span className="aa-s2-muted" style={{fontSize:12}}>Remove unless substantiated</span></div>
          <div className="aa-s2-service-row"><strong>Category showcase</strong><span className="aa-s2-muted" style={{fontSize:12}}>Improve · service + product entry paths</span></div>
          <div className="aa-s2-service-row"><strong>App downloads / contact destinations</strong><span className="aa-s2-muted" style={{fontSize:12}}>Show only when settings configure a verified destination</span></div>
        </div>
      </section>
      <section className="aa-s2-wrap aa-s2-section">
        <div className="aa-s2-preview"><Check size={15}/><span>No hard-coded business success metrics, payment operation, live notification, booking count or download URL. Current public app destinations and configured contacts remain unavailable in this proposal.</span></div>
      </section>
      <section className="aa-s2-wrap aa-s2-section" style={{paddingTop:34}}>
        <div style={{padding:"28px 30px",background:"#eae4da",borderRadius:14,display:"flex",alignItems:"center",justifyContent:"space-between",gap:16,flexWrap:"wrap"}}><div><span className="aa-s2-eyebrow">Same family. Two ways to work.</span><h2 style={{fontSize:25,margin:"6px 0"}}>Customer discovery meets business operations.</h2><p className="aa-s2-muted" style={{margin:0,fontSize:13}}>Browse the marketplace experience or explore the Stage 1 business sign-in design.</p></div><LinkButton page="Homepage" variant="secondary">Customer marketplace <ArrowRight size={14}/></LinkButton></div>
      </section>
      <section className="aa-s2-wrap aa-s2-section" style={{paddingTop:18}}><PreviewBadge>Redesigned proposal based on the extracted native /for-business page and audited seller Calendar data shape. Not a screenshot of a live workspace.</PreviewBadge></section>
    </main>
  </PageShell>;
}