import "./_group.css";
import "./_support.css";
import { ArrowRight, CircleHelp, Info, MailX } from "lucide-react";
import { LinkButton, PageShell, PreviewBadge } from "./_shared/Shell";

const about = [
  {
    title: "About AgendaAlly — development preview",
    text: "AgendaAlly connects a customer marketplace for services and products with tools for businesses to present their offerings and manage bookings.",
    image: "/__mockup/images/stage2-local-business.jpg",
    alt: "A Black African shop owner welcoming a customer in an independent local shop",
  },
  {
    title: "Services and products in the same marketplace",
    text: "Original AgendaAlly workflows include service discovery and appointment booking, alongside product catalogs, stock variants, and a customer cart. Businesses can present services and retail products through the capabilities exposed by their account.",
  },
  {
    title: "An Africa-first marketplace — development preview",
    text: "AgendaAlly's local demo data shows Cameroon and Douala first, while the original application supports country- and city-aware discovery and broader service and product catalogs. These existing screens are intended for an Africa-first, globally inclusive marketplace; future brand and media choices should be reviewed for accurate representation and usage rights.",
  },
];

const faqs = [
  { question: "Are businesses, products, and appointments in this preview live?", answer: "No. Seeded listings are synthetic development examples. Do not treat their availability, prices, contact details, or images as live offers, and do not submit real personal or payment information." },
  { question: "What does the country and city selection do?", answer: "The original marketplace uses the selected country and city to scope supported discovery and listings. This preview contains synthetic geography and business data; no Google Maps service is required to use its seeded location choices." },
  { question: "Which cancellation or refund terms apply?", answer: "This sample does not set a cancellation deadline, refund entitlement, or dispute outcome. It is not an operative policy. Refer to final owner-approved terms and any applicable booking or order details before using a production release." },
  { question: "How can a business present its services and products?", answer: "The existing business workspace supports service and product catalog workflows, subject to account permissions. This preview uses synthetic records to demonstrate those native screens; it does not provide a live storefront or guarantee a transaction." },
];

export function Support() {
  return <PageShell title="About & support">
    <main className="aa-s2-support">
      <section className="aa-s2-support-hero"><div className="aa-s2-wrap"><span className="aa-s2-eyebrow">About · help · contact</span><h1>Local discovery, with the details in view.</h1><p>AgendaAlly connects service discovery and appointment booking with product catalogs and customer shopping—grounded in the chosen market, not an assumed one.</p></div></section>
      <div className="aa-s2-wrap aa-s2-support-main">
        <PreviewBadge>Development preview with synthetic listings and illustrative text. This page does not describe live local supply. Images are synthetic AI-generated proposal visuals.</PreviewBadge>
        <section className="aa-s2-support-section" aria-labelledby="about-marketplace">
          <div className="aa-s2-support-section-head"><div><span className="aa-s2-eyebrow">One product family</span><h2 id="about-marketplace">Services and products, side by side.</h2></div><LinkButton page="Homepage" variant="secondary">Explore the proposal <ArrowRight size={15}/></LinkButton></div>
          <div className="aa-s2-support-about-grid">
            <article className="aa-s2-about-feature"><img src={about[0].image} alt={about[0].alt}/><div className="aa-s2-about-feature-copy"><span className="aa-s2-eyebrow" style={{color:"#ead3ba"}}>Cameroon → Douala · synthetic demo context</span><h2>{about[0].title}</h2><p>{about[0].text} The seeded example is not a claim that pictured or listed businesses accept real customers.</p></div></article>
            <div className="aa-s2-about-stack">
              {about.slice(1).map((item,index)=><article className="aa-s2-about-note" key={item.title}><span className="aa-s2-eyebrow">0{index+2} · preview copy</span><h3>{item.title}</h3><p>{item.text} Availability, inventory, prices, and business information shown here come from local development data and are not live offers.</p></article>)}
            </div>
          </div>
        </section>
        <section className="aa-s2-support-section" aria-labelledby="common-questions">
          <div className="aa-s2-support-section-head"><div><span className="aa-s2-eyebrow"><CircleHelp size={14} style={{verticalAlign:"-2px",marginRight:5}}/>Frequently asked</span><h2 id="common-questions">Clear answers for this preview.</h2></div></div>
          <div className="aa-s2-faq-list">{faqs.map((faq,index)=><details className="aa-s2-faq-item" key={faq.question} open={index===0}><summary>{faq.question}</summary><div className="aa-s2-faq-answer">{faq.answer}</div></details>)}</div>
        </section>
        <section className="aa-s2-support-section" aria-labelledby="contact-preview">
          <div className="aa-s2-support-section-head"><div><span className="aa-s2-eyebrow">Contact</span><h2 id="contact-preview">No verified contact channel is configured.</h2></div></div>
          <div className="aa-s2-contact-panel"><div><h3><MailX size={18} style={{verticalAlign:"-4px",marginRight:7}}/>Contact information unavailable</h3><p>Contact information could not be loaded for this development preview. No official support mailbox, phone line, office address, social account, or message destination is configured here. This representative page has no message form.</p></div><Info size={22} color="var(--bronze-deep)" aria-hidden="true"/></div>
        </section>
        <div style={{marginTop:25}}><PreviewBadge>Footer destinations are the local proposal's supported content pages only. No official social or app-store links are presented.</PreviewBadge></div>
      </div>
    </main>
  </PageShell>;
}