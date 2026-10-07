import type { ReactNode } from "react";
import { ArrowDown, ArrowUpRight, Check, CircleHelp, Layers3, ShieldAlert } from "lucide-react";
import { PageShell, PreviewBadge } from "./_shared/Shell";

const preview = "/__mockup/preview/";
const links = {
  home: `${preview}agendaally-stage2/Homepage`,
  discovery: `${preview}agendaally-stage2/Discovery`,
  profile: `${preview}agendaally-stage2/BusinessProfile`,
  business: `${preview}agendaally-stage2/ForBusiness`,
  blog: `${preview}agendaally-stage2/Blog`,
  article: `${preview}agendaally-stage2/Article`,
  terms: `${preview}agendaally-stage2/Terms`,
  privacy: `${preview}agendaally-stage2/Privacy`,
  support: `${preview}agendaally-stage2/Support`,
  payment: `${preview}agendaally-stage2/PaymentStates`,
  brand: `${preview}agendaally-stage2-art/BrandSystem`,
  categories: `${preview}agendaally-stage2-art/CategoryDirections`,
  people: `${preview}agendaally-stage2-alternatives/PeopleFirst`,
  businesses: `${preview}agendaally-stage2-alternatives/BusinessFirst`,
  current1: `${preview}agendaally-stage2-current/CurrentHome1`,
  current2: `${preview}agendaally-stage2-current/CurrentHome2`,
  current3: `${preview}agendaally-stage2-current/CurrentHome3`,
  current4: `${preview}agendaally-stage2-current/CurrentHome4`,
  currentBusiness: `${preview}agendaally-stage2-business-current/CurrentBusiness`,
  currentSupport: `${preview}agendaally-stage2-support-current/CurrentSupport`,
};

function PreviewLink({ href, children, quiet = false }: { href: string; children: ReactNode; quiet?: boolean }) {
  return <a className={`review-link${quiet ? " review-link-quiet" : ""}`} href={href} target="_blank" rel="noreferrer">
    {children}<ArrowUpRight size={14} aria-hidden="true"/>
  </a>;
}

const storefrontRows = [
  {
    view: "01",
    route: "/",
    decision: "IMPROVE",
    tint: "keep",
    summary: "Future structural foundation",
    keep: "Shared service/category widgets; broad discovery composition; established search patterns.",
    change: "Replace generic headline. Put Book services and Shop products at the same first decision point. Reduce repeated carousels; make country and city legible on mobile.",
    link: links.current1,
    label: "CurrentHome1 excerpt",
  },
  {
    view: "02",
    route: "/home-2",
    decision: "IMPROVE",
    tint: "improve",
    summary: "Retain the human-led hero idea",
    keep: "Layered, photo-led composition and compact cards as a reusable hero concept.",
    change: "Remove salon-only positioning and unsourced “10 people booked”. Verify the initial/refetched category type mismatch before consolidation.",
    link: links.current2,
    label: "CurrentHome2 excerpt",
  },
  {
    view: "03",
    route: "/home-3",
    decision: "CONSOLIDATE",
    tint: "consolidate",
    summary: "Fold shared body into View 1",
    keep: "The gradient's energy can remain an optional, motion-safe decorative treatment.",
    change: "Do not keep a second full homepage tree for a background. Broaden beyond beauty/services; keep full date/time search available where appropriate.",
    link: links.current3,
    label: "CurrentHome3 excerpt",
  },
  {
    view: "04",
    route: "/home-4",
    decision: "RETIRE",
    tint: "retire",
    summary: "Independent system, after approval",
    keep: "The distinctive image and outlined-type hero can inform a reusable component.",
    change: "Its source body repeats common widgets and omits service/category discovery. Do not retire the native route now.",
    link: links.current4,
    label: "CurrentHome4 excerpt",
  },
];

const coverage = [
  ["01", "Audit actual storefronts, ui_type, middleware and shared components", "Source comparison", "#storefronts", "Storefront audit"],
  ["02", "KEEP / IMPROVE / CONSOLIDATE / RETIRE dispositions", "Four-variant table", "#storefronts", "Disposition table"],
  ["03", "Recommend future primary and concepts to retain", "City marketplace", links.home, "Homepage"],
  ["04", "Three meaningfully different positioning directions", "City marketplace", links.home, "Homepage"],
  ["05", "Stronger capability-grounded headline and copy", "Customer proposal", links.home, "Homepage"],
  ["06", "Proposed information architecture", "IA map", "#information-architecture", "Information architecture"],
  ["07", "Representative customer homepage — desktop", "Homepage proposal", links.home, "Homepage"],
  ["08", "Representative customer homepage — ~390px", "Responsive proposal", links.home, "Homepage"],
  ["09", "Search / discovery domains, filters and states", "Discovery proposal", links.discovery, "Discovery"],
  ["10", "Country/city selection and location honesty", "Location & discovery", links.discovery, "Discovery"],
  ["11", "Business/vendor profile with supported details", "Profile proposal", links.profile, "BusinessProfile"],
  ["12", "Services and products have comparable prominence", "Profile + marketplace", links.profile, "BusinessProfile"],
  ["13", "Existing public Business page source audit", "Current excerpt + audit", links.currentBusiness, "CurrentBusiness"],
  ["14", "Redesigned Business landing — desktop", "Business proposal", links.business, "ForBusiness"],
  ["15", "Redesigned Business landing — ~390px", "Responsive proposal", links.business, "ForBusiness"],
  ["16", "Rental screenshot replaced by Calendar-grounded concept", "Native Calendar proposal", links.business, "ForBusiness"],
  ["17", "Three category-art directions", "Art direction review", links.categories, "CategoryDirections"],
  ["18", "Representative, scalable category cards", "Category card proposal", links.categories, "CategoryDirections"],
  ["19", "Africa-first, globally inclusive imagery", "Media direction", "#media-integrity", "Imagery direction"],
  ["20", "Seeded-media audit, reuse and provenance limits", "Media audit summary", "#media-integrity", "Media & provenance"],
  ["21", "Payment states; cash, inactive providers and wallet", "Payment proposal", links.payment, "PaymentStates"],
  ["22", "Blog listing and article detail", "Editorial screens", links.blog, "Blog"],
  ["23", "Professional Terms/Privacy drafts and review status", "Terms draft", links.terms, "Terms"],
  ["24", "Support, social and honest footer destinations", "Support proposal", links.support, "Support"],
  ["25", "Working logo and shared visual system", "Brand proposal", links.brand, "BrandSystem"],
  ["26", "Customer ↔ Business navigation relationship", "Business proposal", links.business, "ForBusiness"],
  ["27", "Supported today versus future recommendations", "Capability boundary", "#capability-boundary", "Read boundary"],
];

export function Review() {
  return <PageShell title="Stage 2 review">
    <style>{`
      .aa-review { --rv-line:#e4ddd1; --rv-paper:#fbfaf7; }
      .aa-review .review-wrap { max-width:1120px; margin:0 auto; padding:0 30px; }
      .aa-review .review-hero { padding:62px 0 52px; background:linear-gradient(115deg,#f1ece3 0%,#fbfaf7 54%,#eee7dc 100%); border-bottom:1px solid var(--rv-line); }
      .aa-review .hero-top { display:flex; justify-content:space-between; align-items:flex-start; gap:32px; }
      .aa-review .review-kicker { color:#785b3c; font-size:11px; font-weight:800; letter-spacing:.16em; text-transform:uppercase; }
      .aa-review h1 { max-width:760px; font-size:clamp(40px,5.7vw,70px); line-height:1.02; margin:14px 0 18px; }
      .aa-review .hero-copy { max-width:680px; color:#625d55; font-size:17px; line-height:1.65; margin:0; }
      .aa-review .gate-stamp { width:180px; flex:none; border:1px solid #baa17f; color:#674c34; background:#f7f1e8; padding:15px 16px; border-radius:3px; font-size:11px; font-weight:800; letter-spacing:.1em; line-height:1.6; text-transform:uppercase; transform:rotate(2deg); }
      .aa-review .gate-stamp span { display:block; color:#8a7864; font-weight:600; letter-spacing:.06em; }
      .aa-review .hero-actions { display:flex; flex-wrap:wrap; gap:12px; margin-top:27px; }
      .aa-review .hero-actions .aa-s2-btn { min-height:47px; }
      .aa-review .decision-band { display:grid; grid-template-columns:1.1fr .9fr; gap:30px; margin-top:42px; padding:25px 27px; background:#302d28; color:#f8f3ea; border-radius:12px; }
      .aa-review .decision-band h2 { margin:7px 0 8px; font-size:25px; line-height:1.15; }
      .aa-review .decision-band p { color:#d4cdc2; font-size:13px; margin:0; max-width:610px; }
      .aa-review .decision-points { border-left:1px solid #635c50; padding-left:25px; display:grid; gap:12px; align-content:center; }
      .aa-review .decision-point { display:flex; gap:11px; align-items:flex-start; font-size:13px; color:#eee8de; }
      .aa-review .decision-point svg { color:#d4b18a; flex:none; margin-top:2px; }
      .aa-review .section { padding:62px 0 0; scroll-margin-top:24px; }
      .aa-review .section-heading { display:flex; align-items:flex-end; justify-content:space-between; gap:24px; margin-bottom:23px; }
      .aa-review .section-heading h2 { font-size:clamp(28px,3.5vw,40px); margin:7px 0 0; line-height:1.12; }
      .aa-review .section-heading p { margin:0; color:#6f6a62; font-size:13px; max-width:400px; }
      .aa-review .subtle-note { color:#726d64; font-size:12px; }
      .aa-review .table-frame { border:1px solid var(--rv-line); border-radius:12px; overflow:hidden; background:#fffefa; }
      .aa-review table { width:100%; border-collapse:collapse; text-align:left; }
      .aa-review th { padding:12px 14px; color:#756b5e; background:#f3efe8; text-transform:uppercase; font-size:10px; letter-spacing:.1em; }
      .aa-review td { border-top:1px solid #eee9e1; padding:16px 14px; vertical-align:top; font-size:12px; line-height:1.55; color:#514d46; }
      .aa-review .view-label { color:#89775f; font-size:10px; letter-spacing:.12em; font-weight:800; }
      .aa-review .route { font:600 13px "DM Sans",sans-serif; color:#26241f; }
      .aa-review .decision-pill { display:inline-flex; padding:5px 8px; border-radius:4px; font-size:9px; font-weight:800; letter-spacing:.08em; white-space:nowrap; }
      .aa-review .pill-keep { background:#edf0e9; color:#4d6349; }
      .aa-review .pill-improve { background:#f4ede2; color:#795a35; }
      .aa-review .pill-consolidate { background:#e9eeef; color:#4d666a; }
      .aa-review .pill-retire { background:#f1e8e5; color:#88584c; }
      .aa-review .summary { color:#28251f; font-weight:700; margin:7px 0 0; }
      .aa-review .table-label { display:block; font-size:9px; text-transform:uppercase; letter-spacing:.1em; color:#8a8174; font-weight:800; margin-bottom:4px; }
      .aa-review .mobile-current-links { display:none; }
      .aa-review .position-list { display:grid; grid-template-columns:1.1fr .95fr .95fr; align-items:stretch; gap:12px; }
      .aa-review .position { border:1px solid var(--rv-line); background:#fffefa; border-radius:12px; padding:22px; display:flex; flex-direction:column; min-height:230px; }
      .aa-review .position-recommended { background:#f1ece3; border-color:#b9a184; transform:translateY(-8px); }
      .aa-review .position-index { font:700 12px "DM Sans",sans-serif; color:#8f7758; letter-spacing:.14em; }
      .aa-review .position h3 { font-size:22px; margin:13px 0 9px; line-height:1.12; }
      .aa-review .position p { font-size:12px; color:#666158; margin:0 0 12px; }
      .aa-review .position .tradeoff { border-top:1px solid #ded6c8; padding-top:11px; margin-top:auto; }
      .aa-review .recommended-tag { display:inline-block; background:#302d28; color:#f8f3ea; font-size:9px; font-weight:800; letter-spacing:.1em; padding:5px 7px; border-radius:3px; text-transform:uppercase; }
      .aa-review .pathway { display:grid; grid-template-columns:repeat(6,1fr); align-items:stretch; border:1px solid var(--rv-line); border-radius:12px; background:#fffefa; overflow:hidden; }
      .aa-review .path-node { position:relative; padding:20px 15px; border-right:1px solid var(--rv-line); min-height:136px; }
      .aa-review .path-node:last-child { border-right:0; }
      .aa-review .path-node small { display:block; color:#89775f; text-transform:uppercase; letter-spacing:.11em; font-size:9px; font-weight:800; margin-bottom:12px; }
      .aa-review .path-node strong { display:block; font-size:13px; line-height:1.35; }
      .aa-review .path-node span { display:block; color:#716c63; font-size:11px; margin-top:7px; }
      .aa-review .ia-foot { margin-top:14px; padding:17px 20px; border-left:3px solid #9a7752; background:#f4efe7; color:#5b554c; font-size:12px; }
      .aa-review .two-col { display:grid; grid-template-columns:1fr 1fr; gap:17px; }
      .aa-review .review-panel { padding:23px; border:1px solid var(--rv-line); border-radius:12px; background:#fffefa; }
      .aa-review .review-panel h3 { font-size:20px; margin:0 0 12px; }
      .aa-review .review-panel p { font-size:13px; color:#676158; margin:8px 0; }
      .aa-review .bullet-list { display:grid; gap:10px; padding:0; margin:15px 0 0; list-style:none; }
      .aa-review .bullet-list li { display:flex; align-items:flex-start; gap:9px; color:#554f47; font-size:12px; }
      .aa-review .bullet-list svg { color:#78836a; flex:none; margin-top:1px; }
      .aa-review .audit-table { display:grid; grid-template-columns:1.05fr .75fr 2.2fr; border:1px solid var(--rv-line); border-radius:12px; overflow:hidden; background:#fffefa; }
      .aa-review .audit-head { background:#f3efe8; color:#756b5e; padding:11px 14px; text-transform:uppercase; letter-spacing:.1em; font-size:9px; font-weight:800; }
      .aa-review .audit-cell { padding:12px 14px; border-top:1px solid #eee9e1; font-size:11px; line-height:1.5; color:#5d574f; }
      .aa-review .audit-cell strong { color:#2a2722; }
      .aa-review .tag-native,.aa-review .tag-future { display:inline-block; padding:4px 6px; border-radius:3px; font-size:9px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; }
      .aa-review .tag-native { background:#eaf0e8; color:#4e634a; }
      .aa-review .tag-future { background:#f2e9dd; color:#795a35; }
      .aa-review .media-note { background:#302d28; color:#f4eee5; border-radius:12px; padding:24px; display:grid; grid-template-columns:1fr 1fr; gap:28px; }
      .aa-review .media-note h3 { font-size:20px; margin:0 0 9px; }
      .aa-review .media-note p { color:#d1c9bd; font-size:12px; margin:0; }
      .aa-review .link-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; }
      .aa-review .screen-link { display:flex; min-height:87px; padding:15px; align-items:flex-start; justify-content:space-between; gap:13px; border:1px solid var(--rv-line); background:#fffefa; border-radius:10px; transition:transform .16s ease,border-color .16s ease; }
      .aa-review .screen-link:hover { transform:translateY(-2px); border-color:#b89b7d; }
      .aa-review .screen-link strong { display:block; font-size:13px; }
      .aa-review .screen-link span { display:block; color:#746d63; font-size:10px; margin-top:5px; line-height:1.4; }
      .aa-review .screen-link svg { color:#8b6c49; flex:none; }
      .aa-review .coverage-table td:first-child { color:#8b7356; font-size:10px; font-weight:800; white-space:nowrap; }
      .aa-review .coverage-table td:nth-child(2) { font-weight:600; color:#39352f; }
      .aa-review .coverage-table td:nth-child(3) { white-space:nowrap; }
      .aa-review .source-limits { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }
      .aa-review .limit { border-top:2px solid #ad8c68; padding:15px 0 0; }
      .aa-review .limit strong { display:block; font-size:12px; margin-bottom:6px; }
      .aa-review .limit p { color:#716b62; font-size:11px; margin:0; }
      .aa-review .stop-band { margin-top:48px; padding:28px; border:1px solid #c8a887; background:#f2e9dd; border-radius:12px; display:flex; justify-content:space-between; align-items:center; gap:25px; }
      .aa-review .stop-band h2 { margin:7px 0 5px; font-size:26px; }
      .aa-review .stop-band p { margin:0; color:#655b4d; font-size:12px; max-width:720px; }
      .aa-review .stop-icon { width:52px; height:52px; display:grid; place-items:center; border:1px solid #b79a79; border-radius:50%; color:#795a35; flex:none; }
      @media(max-width:800px) {
        .aa-review .review-wrap { padding:0 22px; }
        .aa-review .decision-band { grid-template-columns:1fr; }
        .aa-review .decision-points { border-left:0; border-top:1px solid #635c50; padding:16px 0 0; }
        .aa-review .pathway { grid-template-columns:repeat(3,1fr); }
        .aa-review .path-node:nth-child(3) { border-right:0; }
        .aa-review .path-node:nth-child(n+4) { border-top:1px solid var(--rv-line); }
        .aa-review .link-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
      }
      @media(max-width:600px) {
        .aa-review .review-wrap { padding:0 19px; }
        .aa-review .review-hero { padding:38px 0 32px; }
        .aa-review .hero-top { display:block; }
        .aa-review h1 { font-size:43px; max-width:440px; }
        .aa-review .hero-copy { font-size:15px; }
        .aa-review .gate-stamp { width:auto; display:inline-block; margin-top:22px; transform:none; }
        .aa-review .hero-actions { flex-direction:column; align-items:stretch; }
        .aa-review .hero-actions .aa-s2-btn { width:100%; }
        .aa-review .decision-band { margin-top:28px; padding:21px; }
        .aa-review .section { padding-top:43px; }
        .aa-review .section-heading { display:block; }
        .aa-review .section-heading p { margin-top:9px; }
        .aa-review .table-frame { overflow:visible; border:0; background:transparent; }
        .aa-review .storefront-table,.aa-review .storefront-table tbody,.aa-review .storefront-table tr,.aa-review .storefront-table td { display:block; width:100%; }
        .aa-review .storefront-table thead { display:none; }
        .aa-review .storefront-table tr { border:1px solid var(--rv-line); border-radius:11px; background:#fffefa; padding:15px; margin-bottom:10px; }
        .aa-review .storefront-table td { border:0; padding:5px 0; }
        .aa-review .storefront-table td:first-child { display:flex; align-items:center; justify-content:space-between; }
        .aa-review .storefront-table td:nth-child(3) { margin:8px 0; }
        .aa-review .storefront-table td:nth-child(4) { border-top:1px solid #eee9e1; padding-top:10px; }
        .aa-review .storefront-table td:nth-child(5) { display:none; }
        .aa-review .mobile-current-links { display:inline-flex; }
        .aa-review .position-list { grid-template-columns:1fr; gap:9px; }
        .aa-review .position { min-height:0; padding:18px; }
        .aa-review .position-recommended { transform:none; order:-1; }
        .aa-review .pathway { grid-template-columns:1fr 1fr; }
        .aa-review .path-node { min-height:120px; padding:16px 13px; border-bottom:1px solid var(--rv-line); }
        .aa-review .path-node:nth-child(3) { border-right:1px solid var(--rv-line); }
        .aa-review .path-node:nth-child(even) { border-right:0; }
        .aa-review .path-node:nth-child(n+4) { border-top:0; }
        .aa-review .path-node:nth-child(n+5) { border-bottom:0; }
        .aa-review .two-col,.aa-review .media-note { grid-template-columns:1fr; }
        .aa-review .audit-table { grid-template-columns:1fr; }
        .aa-review .audit-head { display:none; }
        .aa-review .audit-cell { padding:9px 13px; }
        .aa-review .audit-cell:nth-child(3n+1) { border-top:1px solid var(--rv-line); background:#f8f5ef; padding-top:13px; }
        .aa-review .audit-cell:nth-child(3n+2)::before { content:"Decision · "; color:#84745e; font-size:9px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; }
        .aa-review .link-grid { grid-template-columns:1fr; }
        .aa-review .screen-link { min-height:68px; }
        .aa-review .source-limits { grid-template-columns:1fr; gap:17px; }
        .aa-review .stop-band { align-items:flex-start; padding:21px; }
        .aa-review .stop-icon { width:42px;height:42px; }
        .aa-review .coverage-table thead { display:none; }
        .aa-review .coverage-table,.aa-review .coverage-table tbody,.aa-review .coverage-table tr,.aa-review .coverage-table td { display:block; width:100%; }
        .aa-review .coverage-table tr { display:grid; grid-template-columns:43px 1fr; gap:1px 9px; padding:11px 13px; border-top:1px solid #eee9e1; }
        .aa-review .coverage-table td { border:0; padding:2px 0; }
        .aa-review .coverage-table td:first-child { grid-row:1 / span 2; }
        .aa-review .coverage-table td:nth-child(2) { font-size:11px; }
        .aa-review .coverage-table td:nth-child(3) { white-space:normal; }
      }
    `}</style>
    <main className="aa-review">
      <section className="review-hero">
        <div className="review-wrap">
          <div className="hero-top">
            <div>
              <span className="review-kicker">AgendaAlly · Stage 2 visual review</span>
              <h1>One marketplace.<br/>Two ways to grow.</h1>
              <p className="hero-copy">A source-grounded proposal for customer discovery and AgendaAlly for Business. Review the recommendation, compare the native storefronts, then move through the linked desktop and mobile screens.</p>
              <div className="hero-actions">
                <a className="aa-s2-btn aa-s2-btn-primary" href={links.home} target="_blank" rel="noreferrer">Open recommended homepage <ArrowUpRight size={15}/></a>
                <a className="aa-s2-btn aa-s2-btn-secondary" href="#storefronts">Compare current storefronts <ArrowDown size={15}/></a>
              </div>
            </div>
            <div className="gate-stamp">Proposal only<span>Approval required<br/>before native integration</span></div>
          </div>
          <div className="decision-band">
            <div>
              <span className="review-kicker" style={{color:"#d4b18a"}}>Recommendation · City marketplace</span>
              <h2>Build from View 1’s shared composition—not its current hierarchy.</h2>
              <p>Lead with the selected country and city. Put “Book services” and “Shop products” side by side at the first decision. Keep people and businesses as meaningful secondary routes.</p>
            </div>
            <div className="decision-points">
              <div className="decision-point"><Check size={15}/> Consolidate duplicated discovery widgets from View 3.</div>
              <div className="decision-point"><Check size={15}/> Keep service and product search on their existing separate contracts.</div>
              <div className="decision-point"><ShieldAlert size={15}/> Do not change ui_type, middleware, native routes or transactions now.</div>
            </div>
          </div>
        </div>
      </section>

      <div className="review-wrap">
        <PreviewBadge>Review package only. Screen links open isolated sandbox proposals; they do not change the native app or perform live operations.</PreviewBadge>

        <section className="section" id="storefronts">
          <div className="section-heading">
            <div><span className="aa-s2-eyebrow">01 / Current native storefronts</span><h2>Four versions, one future system.</h2></div>
            <p>Disposition is a proposal, not a route change. The current links are source-extracted hero/category excerpts—not full app screenshots or live API acceptance.</p>
          </div>
          <div className="table-frame">
            <table className="storefront-table">
              <thead><tr><th>Native view</th><th>Decision</th><th>Keep</th><th>Change / trade-off</th><th>Reference</th></tr></thead>
              <tbody>{storefrontRows.map(row=><tr key={row.view}>
                <td><span className="view-label">VIEW {row.view}</span><div className="route">{row.route}</div><div className="summary">{row.summary}</div><span className={`mobile-current-links`}><PreviewLink href={row.link} quiet>{row.label}</PreviewLink></span></td>
                <td><span className={`decision-pill pill-${row.tint}`}>{row.decision}</span></td>
                <td><span className="table-label">Keep</span>{row.keep}</td>
                <td><span className="table-label">Change / trade-off</span>{row.change}</td>
                <td><PreviewLink href={row.link} quiet>{row.label}</PreviewLink></td>
              </tr>)}</tbody>
            </table>
          </div>
          <p className="subtle-note" style={{marginTop:12}}>The audit reviewed source composition, ui_type selection and middleware behavior. The `/` route may currently rewrite to Views 2–4; direct variant routes remain available. No variant is disabled by this proposal.</p>
        </section>

        <section className="section" id="positioning">
          <div className="section-heading"><div><span className="aa-s2-eyebrow">02 / Positioning hypotheses</span><h2>Different entry points, not colorways.</h2></div><p>Each alternative changes what people see first and how they begin—not just the art direction.</p></div>
          <div className="position-list">
            <article className="position position-recommended">
              <span className="recommended-tag">Recommended · Direction A</span><h3>City marketplace</h3>
              <p>“Find the right service or product in Douala.” City context leads; booking and shopping are equally immediate.</p>
              <p className="tradeoff"><b>Trade-off:</b> Needs careful category and empty-state treatment so it does not become a dense directory.</p>
              <PreviewLink href={links.home}>Homepage proposal</PreviewLink>
            </article>
            <article className="position">
              <span className="position-index">DIRECTION B</span><h3>People & expertise</h3>
              <p>Meet professionals, understand their skills and relationship to real businesses; products stay within easy reach.</p>
              <p className="tradeoff"><b>Trade-off:</b> Slower for exact product searches; must not imply specialists own standalone stores.</p>
              <PreviewLink href={links.people}>PeopleFirst proposal</PreviewLink>
            </article>
            <article className="position">
              <span className="position-index">DIRECTION C</span><h3>Businesses first</h3>
              <p>Lead with business identity and branch context; show services and products together inside a business profile.</p>
              <p className="tradeoff"><b>Trade-off:</b> Adds a business-selection step for people who already know which service or product they want.</p>
              <PreviewLink href={links.businesses}>BusinessFirst proposal</PreviewLink>
            </article>
          </div>
        </section>

        <section className="section" id="information-architecture">
          <div className="section-heading"><div><span className="aa-s2-eyebrow">03 / Proposed information architecture</span><h2>Make the route back obvious.</h2></div><p>One product family with two clear destinations, connected by common identity and explicit navigation.</p></div>
          <div className="pathway">
            <div className="path-node"><small>Marketplace</small><strong>Book services</strong><span>Appointment discovery</span></div>
            <div className="path-node"><small>Marketplace</small><strong>Shop products</strong><span>Separate catalog path</span></div>
            <div className="path-node"><small>Discover</small><strong>Businesses + specialists</strong><span>Comparable service / product entry</span></div>
            <div className="path-node"><small>Business</small><strong>Profile + branch</strong><span>Services, people, products, hours</span></div>
            <div className="path-node"><small>Business tools</small><strong>AgendaAlly for Business</strong><span>Public explanation → existing sign-in boundary</span></div>
            <div className="path-node"><small>Support</small><strong>Editorial · help · legal</strong><span>Useful, honest destinations</span></div>
          </div>
          <div className="ia-foot"><b>Account and transaction boundary:</b> existing appointments, orders, favorites, cart and approved authentication remain native. This proposal does not redesign booking, checkout or account transactions. Footer navigation should surface customer and business destinations, editorial/help/legal, chosen location and only configured social/store links.</div>
        </section>

        <section className="section" id="search-location">
          <div className="section-heading"><div><span className="aa-s2-eyebrow">04 / Search, location & parity</span><h2>Keep the contracts distinct.</h2></div><p>Show what is supported today; mark unified ranking and new location signals as future work.</p></div>
          <div className="two-col">
            <article className="review-panel"><h3>Discovery that names its domain</h3><p><span className="tag-native">Supported source contracts</span></p>
              <ul className="bullet-list">
                <li><Check size={15}/>Services: text/category, price, service type, gender, supported specialist and duration criteria; date/time filters are not proof of an open slot.</li>
                <li><Check size={15}/>Products: separate catalog/search APIs with product category, brand, stock/variant and price concepts.</li>
                <li><Check size={15}/>Businesses: branch geography and supported categories; show matched branch address when supplied.</li>
                <li><Check size={15}/>Country and city context; Cameroon → Douala is the explicit example. A change needs confirmation and an explanation of its effect.</li>
              </ul>
            </article>
            <article className="review-panel"><h3>Do not imply invisible capabilities</h3><p><span className="tag-future">Future recommendation</span></p>
              <ul className="bullet-list">
                <li><CircleHelp size={15}/>One cross-domain search/ranking endpoint is not current behavior.</li>
                <li><CircleHelp size={15}/>Maps are optional and disabled. No GPS, distance or “nearby” claim without real permission and inputs.</li>
                <li><CircleHelp size={15}/>No silent city changes; preserve selection and provide cancel, focus and keyboard behavior.</li>
                <li><CircleHelp size={15}/>Category taxonomy does not guarantee a seller or appointment supply. Keep thoughtful empty and error states.</li>
              </ul>
            </article>
          </div>
          <div className="hero-actions" style={{marginTop:16}}><a className="aa-s2-btn aa-s2-btn-secondary" href={links.discovery} target="_blank" rel="noreferrer">Open Discovery screens <ArrowUpRight size={14}/></a><a className="aa-s2-btn aa-s2-btn-secondary" href={links.profile} target="_blank" rel="noreferrer">Open BusinessProfile screens <ArrowUpRight size={14}/></a></div>
        </section>

        <section className="section" id="business-audit">
          <div className="section-heading"><div><span className="aa-s2-eyebrow">05 / AgendaAlly for Business</span><h2>Replace rental imagery with a real workday.</h2></div><p>The business route needs a credible capability story—not invented scale or a hotel calendar.</p></div>
          <div className="audit-table">
            <div className="audit-head">Existing public section</div><div className="audit-head">Disposition</div><div className="audit-head">Source-grounded proposal</div>
            <div className="audit-cell"><strong>Header and location</strong></div><div className="audit-cell"><span className="decision-pill pill-improve">IMPROVE</span></div><div className="audit-cell">Make Marketplace ↔ Business explicit; show geographic context. Current desktop business header has no clear return-to-customer switch; mobile city is not currently shown there.</div>
            <div className="audit-cell"><strong>Hero / Get started</strong></div><div className="audit-cell"><span className="decision-pill pill-improve">IMPROVE</span></div><div className="audit-cell">Explain a specific supported benefit and keep the existing sign-in/onboarding boundary honest. Sandbox CTA does not submit an application.</div>
            <div className="audit-cell"><strong>Booking / Management / Payment / Notification</strong></div><div className="audit-cell"><span className="decision-pill pill-keep">KEEP TOPICS</span></div><div className="audit-cell">Keep the four capability themes; qualify payment and message availability. Do not promise guaranteed external payment or delivery.</div>
            <div className="audit-cell"><strong>Stay in control + reservation image</strong></div><div className="audit-cell"><span className="decision-pill pill-retire">REPLACE IMAGE</span></div><div className="audit-cell">Show a source-grounded seller day-view: scheduled service, times, status and specialist; disabled times are distinct. No rooms, check-in/out, occupancy or rental states.</div>
            <div className="audit-cell"><strong>Categories / “your way”</strong></div><div className="audit-cell"><span className="decision-pill pill-improve">IMPROVE</span></div><div className="audit-cell">Give service taxonomy and distinct product commerce comparable space. Real categories do not mean seller supply; do not invent project-quote support.</div>
            <div className="audit-cell"><strong>Downloads / phone image</strong></div><div className="audit-cell"><span className="decision-pill pill-improve">IMPROVE</span></div><div className="audit-cell">Use authentic Business workspace visuals and only verified configured destinations. Current phone image represents customer marketplace screens.</div>
            <div className="audit-cell"><strong>Partner marquee / growth metrics</strong></div><div className="audit-cell"><span className="decision-pill pill-retire">REMOVE CLAIMS</span></div><div className="audit-cell">“Fastest-growing” and 121m+ / 12% / 221k+ lack evidence in the page source. Remove pending substantiation; no invented partner marks.</div>
            <div className="audit-cell"><strong>Footer</strong></div><div className="audit-cell"><span className="decision-pill pill-improve">IMPROVE</span></div><div className="audit-cell">Keep help, editorial and legal; show Business navigation, configured social and legitimate app destinations only.</div>
          </div>
          <div className="hero-actions" style={{marginTop:16}}><a className="aa-s2-btn aa-s2-btn-secondary" href={links.business} target="_blank" rel="noreferrer">Open redesigned business screens <ArrowUpRight size={14}/></a><a className="aa-s2-btn aa-s2-btn-secondary" href={links.currentBusiness} target="_blank" rel="noreferrer">Open CurrentBusiness excerpt <ArrowUpRight size={14}/></a></div>
          <p className="subtle-note" style={{marginTop:12}}>Calendar grounding: native seller Calendar maps booking ID, service title, start/end, status and service master; disabled-time events are separate. It does not map room inventory, product events or branch filtering. Any visual demo data is illustrative, not a verified live schedule.</p>
        </section>

        <section className="section" id="categories">
          <div className="section-heading"><div><span className="aa-s2-eyebrow">06 / Categories & shared visual language</span><h2>Let category meaning scale.</h2></div><p>Recommend refined custom pictograms for taxonomy, with selective editorial photography for human context.</p></div>
          <div className="two-col">
            <article className="review-panel"><h3>Art direction</h3><p><b>1 · Refined pictograms — recommended.</b> Consistent at small sizes, scalable, and able to replace misleading dental / education / tattoo proxies with exact semantic glyphs.</p><p><b>2 · Restrained illustration.</b> Warmer, but needs a managed vocabulary as categories grow.</p><p><b>3 · Photography / hybrid.</b> Helpful for human context; harder to crop consistently and manage licensing.</p><PreviewLink href={links.categories}>Review category art directions and cards</PreviewLink></article>
            <article className="review-panel"><h3>Identity stays in-family</h3><p>Preserve the approved Stage 1 <b>a.</b> mark and AgendaAlly wordmark as the working family anchor. Specify compact/full and light/dark use, minimum optical size, clear space, square app/favicon use, alignment and accessible name. Do not distort, repeat or replace the master mark without approval.</p><p>Audit legacy green/purple, settings-driven and “24” identities only after a direction is approved.</p><PreviewLink href={links.brand}>Review BrandSystem</PreviewLink></article>
          </div>
          <div className="ia-foot" style={{marginTop:13}}>Broader service categories include Tailoring, Dental Care, Healthcare, Handyman, Laundry & Dry Cleaning, Home Cleaning, Education, and Tattoo & Piercing. Taxonomy is not provider availability. Product taxonomy remains distinct: Beauty & Personal Care and Tailoring & Apparel Supplies. Do not silently unify category models.</div>
        </section>

        <section className="section" id="media-integrity">
          <div className="section-heading"><div><span className="aa-s2-eyebrow">07 / Media & content integrity</span><h2>Inclusive art, honest provenance.</h2></div><p>Photography should be Africa-first and globally inclusive—not salon-only and never a substitute for provider verification.</p></div>
          <div className="media-note">
            <div><h3>Visual direction</h3><p>Feature Black African customers, professionals and entrepreneurs alongside varied genders, ages, professions and settings. Pair people-led editorial moments with category-specific pictograms; avoid token diversity, repetitive portraits and copy/image mismatches.</p></div>
            <div><h3>What this package can prove</h3><p>Proposal photos are synthetic AI-generated local sandbox assets. They are not real entrepreneur identities, customer testimonials, verified provider photos, inventory/stock proof or backend media updates. No proposal image changes native media. Keep brand/auth art separate from catalog imagery.</p></div>
          </div>
          <div className="ia-foot" style={{marginTop:12}}><b>Seed audit & provenance:</b> the owned development seed has 54 remote media field/row references across shops, products, brand, blog and About, mapping to 30 exact Unsplash URLs; bounded HEAD checks returned 200/image/jpeg for 33 checked URL/crop variants. This proves response headers only—not body decoding, rendering, future availability or license. Nine synthetic argan-serum listings reuse one product image URL; all 13 gallery paths duplicate their product image. Category data has 76 rows: 54 local SVG paths (14 distinct icons) and 22 null legacy product-category images—not broken assets. Shop “logo” slots and the brand image are photos, not verified marks. Suggested mockup-only generated assets are `stage2-local-business.jpg` (local-business showcase), `stage2-learning.jpg` (learning), and `stage2-products.jpg` (generic product catalog); none proves a named provider, brand or SKU. Before native use, build an owner-reviewed offline media manifest with record mapping, source, crop, MIME/dimensions, hash, creator/rights evidence, attribution and reviewer/date. Source: `docs/development/stage-2-media-audit.md`.</div>
        </section>

        <section className="section" id="capability-boundary">
          <div className="section-heading"><div><span className="aa-s2-eyebrow">08 / Payments, content & trust</span><h2>Clarity beats implied activation.</h2></div><p>Show the state of each surface. A polished proposal does not make an unavailable provider live.</p></div>
          <div className="audit-table">
            <div className="audit-head">Surface</div><div className="audit-head">Today / boundary</div><div className="audit-head">Proposal treatment</div>
            <div className="audit-cell"><strong>Payment</strong></div><div className="audit-cell"><span className="tag-native">Cash active</span></div><div className="audit-cell">Demonstrate cash as active; 17 original provider identities inactive. Wallet remains a distinct internal balance/history, not a live gateway. No standalone bank transfer, funding, charge/refund/settlement/payout success.</div>
            <div className="audit-cell"><strong>Blog / article</strong></div><div className="audit-cell"><span className="tag-native">Seed: 3 articles</span></div><div className="audit-cell">Use title, short description, body, image, author and date. Improve date presentation. Do not invent native blog tags/categories.</div>
            <div className="audit-cell"><strong>Terms / Privacy</strong></div><div className="audit-cell"><span className="tag-future">Development samples</span></div><div className="audit-cell">Keep conspicuous non-operative draft/legal review status. No new retention periods, cancellation entitlements, jurisdiction or promises are approved.</div>
            <div className="audit-cell"><strong>Support / social</strong></div><div className="audit-cell"><span className="tag-native">Unknown destinations unavailable</span></div><div className="audit-cell">About / FAQ / Contact explain preview scope; improve FAQ contrast/spacing. No made-up office, map, phone, email, social profile or “coming soon” promise.</div>
            <div className="audit-cell"><strong>App stores / providers</strong></div><div className="audit-cell"><span className="tag-future">Not verified here</span></div><div className="audit-cell">Show only legitimate configured destinations. No live external/store/social/auth/provider operations from this review.</div>
          </div>
          <div className="hero-actions" style={{marginTop:16}}>
            <a className="aa-s2-btn aa-s2-btn-secondary" href={links.payment} target="_blank" rel="noreferrer">Payment states <ArrowUpRight size={14}/></a>
            <a className="aa-s2-btn aa-s2-btn-secondary" href={links.support} target="_blank" rel="noreferrer">Support & footer <ArrowUpRight size={14}/></a>
            <a className="aa-s2-btn aa-s2-btn-secondary" href={links.terms} target="_blank" rel="noreferrer">Terms / Privacy <ArrowUpRight size={14}/></a>
          </div>
        </section>

        <section className="section" id="proposed-screens">
          <div className="section-heading"><div><span className="aa-s2-eyebrow">09 / Proposed screen index</span><h2>Review the designs themselves.</h2></div><p>This page connects the independently built screen proposals; it is not a substitute for the requested page designs. Each link opens the real preview route group.</p></div>
          <div className="link-grid">
            {[
              [links.home,"Homepage","Customer marketplace · desktop + mobile"],
              [links.discovery,"Discovery","Services / products · filters and states"],
              [links.profile,"BusinessProfile","Business identity, branch and parity"],
              [links.business,"ForBusiness","Business story and native Calendar visual"],
              [links.people,"PeopleFirst","Alternative: people and expertise"],
              [links.businesses,"BusinessFirst","Alternative: business-led entry"],
              [links.categories,"CategoryDirections","Three taxonomy art directions"],
              [links.brand,"BrandSystem","Working identity and shared system"],
              [links.payment,"PaymentStates","Cash, unavailable providers and wallet"],
              [links.blog,"Blog","Editorial listing"],
              [links.article,"Article","Editorial detail"],
              [links.terms,"Terms","Development sample"],
              [links.privacy,"Privacy","Development sample / legal review"],
              [links.support,"Support","Help, footer and destination honesty"],
            ].map(([href,title,detail])=><a className="screen-link" key={title} href={href} target="_blank" rel="noreferrer"><span><strong>{title}</strong><span>{detail}</span></span><ArrowUpRight size={15} aria-hidden="true"/></a>)}
          </div>
        </section>

        <section className="section" id="coverage">
          <div className="section-heading"><div><span className="aa-s2-eyebrow">10 / Requested review coverage</span><h2>All 27 items, traceable.</h2></div><p>Coverage links route to the actual proposal screens or the relevant section here. Desktop and approximately 390px responsive versions are built into each requested proposal pair.</p></div>
          <div className="table-frame">
            <table className="coverage-table">
              <thead><tr><th>#</th><th>Coverage</th><th>Evidence</th></tr></thead>
              <tbody>{coverage.map((row,index)=><tr key={`${row[0]}-${index}`}><td>{row[0] || "↳"}</td><td>{row[1]}</td><td><PreviewLink href={row[3]} quiet>{row[4]}</PreviewLink></td></tr>)}</tbody>
            </table>
          </div>
        </section>

        <section className="section" id="source-limits">
          <div className="section-heading"><div><span className="aa-s2-eyebrow">11 / Scope & evidence</span><h2>Proposal rendering is not acceptance.</h2></div><p>Source audits and sandbox references establish a bounded design basis; they do not grant operational or product approval.</p></div>
          <div className="source-limits">
            <div className="limit"><strong>Current references</strong><p>CurrentHome1–4 are source-extracted hero/category excerpts. CurrentBusiness and CurrentSupport preserve representative sections. They are not full reconstructed live pages, fresh screenshots, or complete API datasets.</p></div>
            <div className="limit"><strong>Supported vs. future</strong><p>Native contracts and Stage 1 bounded acceptance remain the basis. Unified search/ranking, new inventory claims, changed location semantics and redesigned transactions are recommendations only.</p></div>
            <div className="limit"><strong>Not tested or activated</strong><p>No renewed auth/transactional acceptance, provider operation, official social/store destination, or production media/license proof. Responsive proposal rendering is visual evidence only.</p></div>
          </div>
        </section>

        <section className="stop-band" id="approval">
          <div><span className="aa-s2-eyebrow">Approval gate</span><h2>STOP here for explicit approval.</h2><p>Review the complete package, then approve a direction, combine elements or request revisions. Only explicit approval of selected Stage 2 designs authorizes native integration. No native route, payment, booking, API, authentication, provider, publishing or operations change is authorized by this proposal.</p></div>
          <div className="stop-icon" aria-hidden="true"><Layers3 size={22}/></div>
        </section>
      </div>
    </main>
  </PageShell>;
}

export default Review;