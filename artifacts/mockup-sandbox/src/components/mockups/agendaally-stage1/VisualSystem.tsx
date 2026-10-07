import "./_group.css";
import { useState } from "react";
import { AlertCircle, CheckCircle2, CircleHelp, Eye, ShieldCheck } from "lucide-react";
import { Brand, Button, Field, PreviewNotice } from "./_shared/Stage1Primitives";

const palette = [
  { name: "Canvas", hex: "#FAF8F4", color: "#FAF8F4" },
  { name: "Surface", hex: "#FFFFFF", color: "#FFFFFF" },
  { name: "Primary ink", hex: "#191A19", color: "#191A19" },
  { name: "Muted copy", hex: "#64665F", color: "#64665F" },
  { name: "Border", hex: "#E5E2DC", color: "#E5E2DC" },
  { name: "Bronze", hex: "#916D41", color: "#916D41" },
  { name: "Bronze text", hex: "#76522E", color: "#76522E" },
  { name: "Bronze tint", hex: "#F0E7D9", color: "#F0E7D9" },
  { name: "Success", hex: "#276447", color: "#276447" },
  { name: "Error", hex: "#AC3D36", color: "#AC3D36" },
];

export function VisualSystem() {
  const [action, setAction] = useState("The example button is ready.");
  const [disabled, setDisabled] = useState(false);
  const [showError, setShowError] = useState(false);
  const [inputValue, setInputValue] = useState("");
  return <main className="aa-stage1">
    <div className="aa-demo-wrap">
      <header style={{ display: "flex", alignItems: "center", justifyContent: "space-between", gap: 18 }}>
        <Brand/>
        <span className="aa-eyebrow">Stage 1 · visual proposal</span>
      </header>
      <section style={{ marginTop: 52 }}>
        <div className="aa-eyebrow">A working identity system</div>
        <h1 className="aa-demo-title">Grounded in care.<br/>Clear in every detail.</h1>
        <p className="aa-demo-lede">A proposed visual foundation for AgendaAlly’s customer and independent-business experiences. Warm neutral surfaces keep the interface welcoming; decisive ink, purposeful bronze and clear states keep it dependable.</p>
      </section>
      <section aria-labelledby="palette-title" style={{ marginTop: 34 }}>
        <div className="aa-inline-between" style={{ marginBottom: 12 }}><h2 id="palette-title" style={{ margin: 0, fontSize: 15, letterSpacing: "-.03em" }}>Semantic colour</h2><span style={{ color: "var(--aa-muted)", fontSize: 11 }}>Proposed values · AA-conscious foreground use</span></div>
        <div className="aa-token-grid">{palette.map((token) => <article className="aa-token" key={token.name}>
          <div className="aa-swatch" style={{ backgroundColor: token.color, borderBottom: token.name === "Surface" ? "1px solid var(--aa-border)" : undefined }}/>
          <div className="aa-token-copy"><strong>{token.name}</strong><span>{token.hex}</span></div>
        </article>)}</div>
      </section>
      <div className="aa-system-columns">
        <section className="aa-card aa-system-card" aria-labelledby="type-title">
          <div className="aa-eyebrow" style={{ marginBottom: 9 }}>Voice & rhythm</div>
          <h3 id="type-title">Inter, with room to breathe.</h3>
          <p>Original local variable font; sentence case, considered tracking and compact labels keep the product human and easy to scan.</p>
          <div className="aa-type-row"><strong style={{ fontSize: 32, fontWeight: 560, letterSpacing: "-.065em" }}>Make room for you.</strong><span>Display · 32 / 36</span></div>
          <div className="aa-type-row"><strong style={{ fontSize: 18, fontWeight: 620, letterSpacing: "-.035em" }}>Sign in to AgendaAlly</strong><span>Heading · 18 / 24</span></div>
          <div className="aa-type-row"><strong style={{ fontSize: 14, fontWeight: 420, lineHeight: 1.6 }}>Find trusted local professionals and book with confidence.</strong><span>Body · 14 / 22</span></div>
          <div className="aa-type-row"><span style={{ fontSize: 11, letterSpacing: ".1em", textTransform: "uppercase", color: "var(--aa-bronze-ink)", fontWeight: 700 }}>WELCOME BACK</span><span>Eyebrow · 10 / 14</span></div>
          <div style={{ marginTop: 22 }}>
            <div className="aa-eyebrow">4 / 8 spacing scale</div>
            {[4, 8, 12, 16, 24, 32].map((unit) => <div className="aa-spacing-row" key={unit}><span style={{ width: 30 }}>{unit}px</span><div className="aa-spacing-bar" style={{ width: unit * 3 }}/><span>space-{unit}</span></div>)}
          </div>
          <div style={{ display: "flex", gap: 10, flexWrap: "wrap", marginTop: 21 }}>
            {[10,16,24,30].map((radius) => <div key={radius} style={{ display: "grid", width: 64, height: 53, placeItems: "center", border: "1px solid var(--aa-border)", borderRadius: radius, color: "var(--aa-muted)", background: "var(--aa-canvas)", fontSize: 10 }}>{radius}px</div>)}
          </div>
          <p style={{ margin: "10px 0 0", fontSize: 10 }}>Corner radii: 10 / 16 / 24 / 30 · restrained elevation, never decorative glow.</p>
        </section>

        <section className="aa-card aa-system-card" aria-labelledby="controls-title">
          <div className="aa-eyebrow" style={{ marginBottom: 9 }}>Same parts, real states</div>
          <h3 id="controls-title">Useful controls, plainly expressed.</h3>
          <p>These interactive examples use the very same shared helpers as the proposed auth screens.</p>
          <div className="aa-state-stack">
            <Field label="Email address" type="email" placeholder="you@example.com" value={inputValue} onChange={(event) => setInputValue(event.target.value)} hint="Default field · try keyboard focus"/>
            <Field label="Field with error" type="email" placeholder="you@example.com" error={showError ? "Enter a valid email address." : undefined} value={showError ? "not-an-email" : undefined} onChange={() => setShowError(false)}/>
            <Field label="Unavailable field" placeholder="Not available" disabled/>
            <div>
              <div className="aa-eyebrow" style={{ marginBottom: 9 }}>Actions</div>
              <div className="aa-example-actions">
                <Button onClick={() => setAction("Primary action pressed — this is only a visual example.")}>Primary action</Button>
                <Button variant="secondary" onClick={() => setAction("Secondary action pressed — nothing was submitted.")}>Secondary</Button>
                <Button disabled={disabled} onClick={() => setAction("Disabled action enabled for this demonstration.")}>{disabled ? "Unavailable" : "Try disabled"}</Button>
              </div>
              <label style={{ display: "inline-flex", gap: 7, alignItems: "center", marginTop: 12, color: "var(--aa-muted)", fontSize: 11 }}><input type="checkbox" checked={disabled} onChange={(event) => setDisabled(event.target.checked)}/> Toggle unavailable state</label>
              <div aria-live="polite" style={{ color: "var(--aa-muted)", fontSize: 11, marginTop: 8 }}>{action}</div>
            </div>
            <PreviewNotice tone="success"><CheckCircle2 size={15}/> Saved successfully. This is a proposed positive status treatment.</PreviewNotice>
            <PreviewNotice><ShieldCheck size={15}/> Preview only — actions do not send data or contact services.</PreviewNotice>
            {showError && <PreviewNotice tone="error"><AlertCircle size={15}/> Correct the highlighted field before continuing.</PreviewNotice>}
            <Button variant="quiet" onClick={() => setShowError(!showError)}><Eye size={14}/>{showError ? "Hide error state" : "Show error state"}</Button>
            <div style={{ display: "flex", alignItems: "center", gap: 8, color: "var(--aa-muted)", fontSize: 11 }}><CircleHelp size={14}/> Strong visible focus ring · keyboard navigable · touch-friendly 48px actions</div>
          </div>
        </section>
      </div>
      <footer style={{ paddingTop: 25, color: "var(--aa-muted)", fontSize: 11, lineHeight: 1.6 }}>Proposal only · These semantic values support visual review and are not a production token migration.</footer>
    </div>
  </main>;
}

export default VisualSystem;