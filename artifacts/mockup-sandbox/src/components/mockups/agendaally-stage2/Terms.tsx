import "./_group.css";
import "./_support.css";
import { ArrowUpRight, FileText } from "lucide-react";
import { LinkButton, PageShell, PreviewBadge } from "./_shared/Shell";

export function Terms() {
  return <PageShell title="Terms & Conditions · development sample">
    <main className="aa-s2-support">
      <div className="aa-s2-wrap aa-s2-legal-layout">
        <aside className="aa-s2-legal-outline" aria-label="Terms page outline">
          <h2>On this page</h2>
          <ol><li><a href="#status">Sample status</a></li><li><a href="#scope">What this sample does</a></li><li><a href="#replacement">Before production</a></li></ol>
          <div style={{marginTop:18}}><LinkButton page="Privacy" variant="soft">Privacy sample <ArrowUpRight size={14}/></LinkButton></div>
        </aside>
        <article className="aa-s2-legal-article">
          <span className="aa-s2-eyebrow">Information · development copy</span>
          <h1>DEVELOPMENT PREVIEW — Terms &amp; Conditions (not operative)</h1>
          <p className="aa-s2-legal-meta"><FileText size={14} style={{verticalAlign:"-2px",marginRight:5}}/>Development sample · Prepared 1 October 2026</p>
          <div className="aa-s2-legal-alert"><strong>DEVELOPMENT SAMPLE — NOT OPERATIVE TERMS.</strong> Owner and qualified legal review are required before production.</div>
          <nav className="aa-s2-reading-nav" aria-label="Terms sections"><a href="#status">Sample status</a><a href="#scope">What this sample does</a><a href="#replacement">Before production</a></nav>
          <section id="status"><h2>Sample status</h2><p>This sample exists only to demonstrate AgendaAlly's existing Terms page. It does not create an agreement, describe binding customer or vendor duties, set a cancellation window, promise a refund, or establish a payment policy. Do not rely on it for a real booking, product order, or dispute.</p></section>
          <section id="scope"><h2>What this sample does</h2><p>This sample exists only to demonstrate AgendaAlly's existing Terms page. It does not create an agreement, describe binding customer or vendor duties, set a cancellation window, promise a refund, or establish a payment policy. Do not rely on it for a real booking, product order, or dispute.</p></section>
          <section id="replacement"><h2>Before production</h2><p>Before production, the owner must replace this sample with approved terms that accurately describe the service, commerce, payment, cancellation, and refund rules that apply to the release.</p><PreviewBadge>Publication is not represented or enabled here. This proposal makes no claims about legal approval or operational policy.</PreviewBadge></section>
        </article>
      </div>
    </main>
  </PageShell>;
}