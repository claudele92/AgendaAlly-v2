import "./_group.css";
import "./_support.css";
import { ArrowUpRight, ShieldAlert } from "lucide-react";
import { LinkButton, PageShell, PreviewBadge } from "./_shared/Shell";

export function Privacy() {
  return <PageShell title="Privacy notice · development sample">
    <main className="aa-s2-support">
      <div className="aa-s2-wrap aa-s2-legal-layout">
        <aside className="aa-s2-legal-outline" aria-label="Privacy page outline">
          <h2>On this page</h2>
          <ol><li><a href="#status">Sample status</a></li><li><a href="#limits">What this is not</a></li><li><a href="#production">Before production</a></li></ol>
          <div style={{marginTop:18}}><LinkButton page="Terms" variant="soft">Terms sample <ArrowUpRight size={14}/></LinkButton></div>
        </aside>
        <article className="aa-s2-legal-article">
          <span className="aa-s2-eyebrow">Information · development copy</span>
          <h1>DEVELOPMENT PREVIEW — Privacy notice (not operative)</h1>
          <p className="aa-s2-legal-meta"><ShieldAlert size={14} style={{verticalAlign:"-2px",marginRight:5}}/>Development sample · Prepared 1 October 2026</p>
          <div className="aa-s2-legal-alert"><strong>DEVELOPMENT SAMPLE — NOT AN OPERATIVE PRIVACY POLICY.</strong> Owner and qualified legal review are required before production.</div>
          <nav className="aa-s2-reading-nav" aria-label="Privacy sections"><a href="#status">Sample status</a><a href="#limits">What this is not</a><a href="#production">Before production</a></nav>
          <section id="status"><h2>Sample status</h2><p>This sample demonstrates the existing AgendaAlly Privacy page only. It is not an accurate inventory of production data collection, recipients, retention, security, or individual rights, and it makes no production privacy commitments.</p></section>
          <section id="limits"><h2>What this is not</h2><p>This sample demonstrates the existing AgendaAlly Privacy page only. It is not an accurate inventory of production data collection, recipients, retention, security, or individual rights, and it makes no production privacy commitments.</p></section>
          <section id="production"><h2>Before production</h2><p>Do not submit real personal, contact, appointment, address, or payment information to the development preview. Before production, document the actual application and provider data flows and replace this text with an approved privacy policy.</p><PreviewBadge>Do not enter real personal, contact, appointment, address, or payment information in this illustrative preview.</PreviewBadge></section>
        </article>
      </div>
    </main>
  </PageShell>;
}