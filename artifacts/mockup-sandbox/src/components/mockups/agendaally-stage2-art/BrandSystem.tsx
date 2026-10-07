import "./_group.css";
import { useState } from "react";

const colors = [
  { name: "Warm canvas", hex: "#FAF8F4", swatch: "#FAF8F4" },
  { name: "Surface", hex: "#FFFFFF", swatch: "#FFFFFF" },
  { name: "Primary ink", hex: "#191A19", swatch: "#191A19" },
  { name: "Muted copy", hex: "#64665F", swatch: "#64665F" },
  { name: "Warm bronze", hex: "#916D41", swatch: "#916D41" },
  { name: "Bronze text", hex: "#76522E", swatch: "#76522E" },
  { name: "Bronze wash", hex: "#F0E7D9", swatch: "#F0E7D9" },
  { name: "Success", hex: "#276447", swatch: "#276447" },
  { name: "Error", hex: "#AC3D36", swatch: "#AC3D36" },
  { name: "Border", hex: "#E5E2DC", swatch: "#E5E2DC" },
];

function Mark({ light = false, size = "full" }: { light?: boolean; size?: "full" | "compact" }) {
  return <span className={`aa2-logo ${size === "compact" ? "compact" : ""} ${light ? "light" : ""}`} aria-label="AgendaAlly">
    <span className="aa2-mark" aria-hidden="true">a.</span>
    {size === "full" && <span className="aa2-wordmark">AgendaAlly</span>}
  </span>;
}

export function BrandSystem() {
  const [controlNote, setControlNote] = useState("Specimen controls are local visual examples only.");
  return <main className="aa2-art">
    <div className="aa2-wrap">
      <header className="aa2-top">
        <Mark/>
        <span className="aa2-topnote">Stage 2 · proposed system · proposal only</span>
      </header>
      <section className="aa2-hero">
        <div>
          <div className="aa2-kicker">Brand system · Customer + Business</div>
          <h1>One identity.<br/>Two ways to belong.</h1>
          <p>Keep the approved Stage 1 “a.” mark as the family anchor. This package refines how it appears across discovery, booking, commerce and the AgendaAlly for Business workspace—without proposing a new master mark.</p>
        </div>
        <aside className="aa2-aside"><strong>Recommendation</strong><br/>Use one quiet, dependable wordmark-and-mark family. Let the product context change—not the logo. Customer Marketplace and AgendaAlly for Business remain sibling experiences.</aside>
      </section>

      <section className="aa2-section" aria-labelledby="lockups-title">
        <div className="aa2-section-head"><div><div className="aa2-kicker">01 · approved mark in use</div><h2 id="lockups-title">Full and compact lockups, light and dark.</h2></div><p>Mark geometry follows the accepted Stage 1 primitive: ink square, asymmetric lower-left corner, lowercase “a.” and original proportions.</p></div>
        <div className="aa2-lockup-grid">
          <div className="aa2-lockup"><Mark/><small>Full · light</small></div>
          <div className="aa2-lockup"><Mark size="compact"/><small>Compact · light</small></div>
          <div className="aa2-lockup dark"><Mark light/><small>Full · dark</small></div>
          <div className="aa2-lockup dark"><Mark light size="compact"/><small>Compact · dark</small></div>
        </div>
        <div className="aa2-context-grid" aria-label="Logo use across product touchpoints">
          <article className="aa2-context">
            <div className="aa2-contextbar"><Mark/><nav className="aa2-navmock" aria-label="Sample customer navigation"><span>Services</span><span>Products</span><span>My bookings</span></nav><span className="aa2-kicker">Customer</span></div>
            <div className="aa2-contextbody"><strong>Marketplace header</strong><br/>Full lockup at desktop sizes; search, location and cart stay separate. At mobile, compact mark plus an accessible menu button.</div>
          </article>
          <article className="aa2-context">
            <div className="aa2-contextbar"><Mark size="compact"/><span className="aa2-kicker">Drawer</span></div>
            <div className="aa2-contextbody"><strong>Customer mobile drawer</strong><br/>Compact mark at top; distinct, text-labelled destinations for Marketplace and Business. Never rely on icon-only navigation labels.</div>
          </article>
          <article className="aa2-context auth">
            <div className="aa2-auth-art"><Mark light size="compact"/><b>Make room<br/>for you.</b></div>
            <div className="aa2-auth-form"><strong>Sign in to AgendaAlly</strong><div className="aa2-mini-line"/><div className="aa2-mini-line"/><div className="aa2-mini-cta"/></div>
          </article>
          <article className="aa2-context auth">
            <div className="aa2-auth-art"><Mark light size="compact"/><b>Your work.<br/>In good order.</b></div>
            <div className="aa2-auth-form"><strong>Business workspace</strong><div className="aa2-mini-line"/><div className="aa2-mini-line"/><div className="aa2-mini-cta"/></div>
          </article>
          <article className="aa2-context">
            <div className="aa2-contextbar"><Mark size="compact"/><span className="aa2-kicker">Business</span></div>
            <div className="aa2-contextbody"><strong>Workspace / public business page</strong><br/>Same family mark in the product shell and public listing. Business name, shop identity and branch identity remain their own content.</div>
          </article>
          <article className="aa2-context footer">
            <div className="aa2-footerbar"><Mark/><span className="aa2-kicker">Customer + Business</span></div>
            <div className="aa2-footertext">Explore services · Shop products · For businesses · About · Support · Policies</div>
          </article>
        </div>
      </section>

      <div className="aa2-spec-grid">
        <section className="aa2-section" aria-labelledby="clearance-title">
          <div className="aa2-section-head"><div><div className="aa2-kicker">02 · reproduction rules</div><h2 id="clearance-title">Clearspace, size, alignment.</h2></div></div>
          <div className="aa2-spec-box">
            <div className="aa2-measure">
              <div className="aa2-size-sample"><span className="aa2-mark">a.</span><span>24px · favicon floor</span></div>
              <div className="aa2-size-sample"><span className="aa2-mark">a.</span><span>32px · compact app/header</span></div>
              <div className="aa2-size-sample"><span className="aa2-mark">a.</span><span>48px · comfortable app tile</span></div>
            </div>
            <ul>
              <li>Maintain clearspace equal to at least ¼ of mark height on every side.</li>
              <li>Align wordmark optically to the mark’s vertical center; 8–10px gap.</li>
              <li>Proposal minimums: mark 24px square; full lockup 122px wide / 28px high.</li>
              <li>Keep aspect ratio fixed. No stretching, rotation, outlines, shadows, recoloring of the glyph, or standalone “a” redraw.</li>
              <li>At micro sizes, use the square mark only; don’t cram a tiny wordmark into a favicon.</li>
            </ul>
          </div>
        </section>
        <section className="aa2-section" aria-labelledby="platform-title">
          <div className="aa2-section-head"><div><div className="aa2-kicker">03 · small-screen relationship</div><h2 id="platform-title">Compact without losing context.</h2></div></div>
          <div className="aa2-spec-box">
            <h3>Mobile header / drawer example</h3>
            <div className="aa2-contextbar" style={{ border: "1px solid #e5e2dc", borderRadius: 11 }}><Mark size="compact"/><span style={{ color: "#64665f", fontSize: 10 }}>Douala, Cameroon</span><span aria-hidden="true" style={{ fontSize: 19 }}>≡</span></div>
            <p>Use compact mark in the tight header, then spell out “Customer Marketplace” and “AgendaAlly for Business” in the drawer destinations. On auth screens, preserve the accepted Stage 1 layout and art direction; this sample shows lockup placement only.</p>
            <h3 style={{ marginTop: 15 }}>Accessible naming</h3>
            <p>The full lockup exposes “AgendaAlly” as the accessible name; decorative glyph is hidden from assistive technology. A mark-only link must have an explicit accessible name such as “AgendaAlly home.” Never make the letterform itself the sole name.</p>
          </div>
        </section>
      </div>

      <section className="aa2-section" aria-labelledby="visual-language">
        <div className="aa2-section-head"><div><div className="aa2-kicker">04 · proposed tokens</div><h2 id="visual-language">Warm neutrals. Restrained bronze. Legible states.</h2></div><p>Baseline values align with the approved Stage 1 visual system. Tokens shown here are a review reference, not a global stylesheet edit.</p></div>
        <div className="aa2-color-row">{colors.map((c) => <div className="aa2-token" key={c.name}><div className="aa2-swatch" style={{ background: c.swatch, borderColor: c.name === "Surface" ? "#e5e2dc" : undefined }}/><b>{c.name}</b>{c.hex}</div>)}</div>
        <div className="aa2-spec-grid">
          <div className="aa2-spec-box"><h3>Typography · one working family</h3><div className="aa2-type-samples">
            <div><strong style={{ fontSize: 27, fontWeight: 590, letterSpacing: "-.06em" }}>Find your kind of care.</strong><span>Display · 28/32</span></div>
            <div><strong style={{ fontSize: 17, fontWeight: 650, letterSpacing: "-.03em" }}>Hair Care in Douala</strong><span>Heading · 18/24</span></div>
            <div><strong style={{ fontSize: 13, fontWeight: 430 }}>Compare services, products and people.</strong><span>Body · 14/22</span></div>
            <div><strong style={{ fontSize: 10, letterSpacing: ".12em", color: "#76522e" }}>LOCAL DISCOVERY</strong><span>Label · 10/14</span></div>
          </div><p>Agenda Stage Two variable sans is a local alias of the existing approved Stage 1 font asset. Use sentence case, restrained tracking and tabular figures for prices, dates and schedules.</p></div>
          <div className="aa2-spec-box"><h3>Space, surfaces, controls</h3><p>4 / 8 rhythm · 4, 8, 12, 16, 24, 32px spacing. Card radii 10 / 16 / 24px; one-pixel warm borders, minimal soft elevation. Search icon is a simple 1.5px round-capped magnifier, always paired with a visible “Search” label on primary action.</p><div className="aa2-controls"><div className="aa2-input"><span aria-hidden="true">⌕</span>Service, product or business</div><button className="aa2-button" type="button" onClick={() => setControlNote("Search pressed — demonstration only; no search request was sent.")}>Search</button><button className="aa2-button secondary" type="button" onClick={() => setControlNote("Location control pressed — demonstration only; location was not changed.")}>Change location</button><span className="aa2-focus-demo">Keyboard focus</span></div><p role="status" aria-live="polite">{controlNote}</p><p>Visible 3px bronze focus ring, 44px minimum touch controls (48px preferred), logical Tab order, clear disabled state and no bronze-only meaning for status.</p></div>
        </div>
        <div className="aa2-audit"><b>Identity audit · pending approval, not an action:</b> preserve the accepted “a.” mark and approved auth direction. Inconsistent settings-vs “a.” mark, green legacy admin identity, purple admin/favicon and unrelated app24 assets should be reviewed as retiring candidates only after explicit approval. Any master-mark replacement is a separate future approval—not part of this package.</div>
      </section>
      <p className="aa2-legal">Visual system proposal only · Existing Stage 1 approved mark and auth direction are referenced, not modified. No backend, data, native application or global styles changed.</p>
    </div>
  </main>;
}

export default BrandSystem;