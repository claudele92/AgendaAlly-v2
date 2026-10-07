import "./_group.css";
import "./_support.css";
import { Banknote, Check, CircleDollarSign, Clock3, WalletCards } from "lucide-react";
import { PageShell, PreviewBadge } from "./_shared/Shell";

const methods = [
  { name:"ZainCash", tag:"zain-cash", reason:"Development-disabled · provider unavailable" },
  { name:"PayTabs", tag:"paytabs", reason:"Development-disabled · provider unavailable" },
  { name:"Flutterwave", tag:"flutter-wave", reason:"Development-disabled · provider unavailable" },
  { name:"Paystack", tag:"paystack", reason:"Development-disabled · provider unavailable" },
  { name:"Mercado Pago", tag:"mercado-pago", reason:"Development-disabled · provider unavailable" },
  { name:"Razorpay", tag:"razorpay", reason:"Development-disabled · provider unavailable" },
  { name:"Stripe", tag:"stripe", reason:"Development-disabled · no test configuration" },
  { name:"PayPal", tag:"paypal", reason:"Development-disabled · no test configuration" },
  { name:"MoyaSar", tag:"moya-sar", reason:"Development-disabled · provider unavailable" },
  { name:"Mollie", tag:"mollie", reason:"Development-disabled · provider unavailable" },
  { name:"iyzico", tag:"iyzico", reason:"Development-disabled · provider unavailable" },
  { name:"Maksekeskus", tag:"maksekeskus", reason:"Development-disabled · provider unavailable" },
  { name:"Orange Money", tag:"orange", reason:"Development-disabled · merchant setup and verification unavailable" },
  { name:"MTN Mobile Money", tag:"mtn", reason:"Development-disabled · merchant setup unavailable" },
  { name:"PayFast", tag:"pay-fast", reason:"Development-disabled · provider unavailable" },
  { name:"PayU", tag:"payu", reason:"Development-disabled · provider unavailable" },
];

export function PaymentStates() {
  return <PageShell title="Payment availability · preview">
    <main className="aa-s2-support">
      <div className="aa-s2-wrap">
        <header className="aa-s2-payment-intro">
          <span className="aa-s2-eyebrow">Payment availability · catalog status</span>
          <h1>Available here means available here.</h1>
          <p>This read-only board shows the original catalog identities and their local development state. A listed method is not proof of provider readiness, successful collection, or settlement.</p>
        </header>
        <PreviewBadge>Visual proposal only · no checkout controls, payment requests, wallet operations, callbacks, purchases, refunds, or transactions are performed from this page.</PreviewBadge>
        <section className="aa-s2-payment-overview" aria-label="Active method and wallet distinction">
          <article className="aa-s2-payment-cash">
            <span className="aa-s2-payment-symbol" style={{background:"#dce6d7",color:"#36533c"}}><Banknote size={20}/></span>
            <div><span className="aa-s2-state-label is-active"><Check size={13}/>Active · offline</span><h2 style={{marginTop:10}}>Cash</h2><p>The single active catalog choice in this development preview. Cash is seller-confirmed offline collection—not a gateway charge. A pending booking or order does not mean cash has been collected.</p></div>
          </article>
          <article className="aa-s2-wallet">
            <span className="aa-s2-state-label is-wallet"><WalletCards size={13}/>Internal balance · inactive</span>
            <h2 style={{marginTop:10}}>Wallet is not a gateway.</h2>
            <p>The original product has a separate internal balance and history. Its catalog entry remains inactive here; this page does not enable wallet checkout, top-up, withdrawal, or external funding.</p>
          </article>
        </section>
        <section aria-labelledby="unavailable-catalog">
          <div className="aa-s2-payment-list-head"><div><span className="aa-s2-eyebrow">Original catalog · 17 inactive entries</span><h2 id="unavailable-catalog">Not selectable in this preview.</h2></div><span className="aa-s2-chip">Catalog identity ≠ availability</span></div>
          <div className="aa-s2-payment-list">
            <div className="aa-s2-payment-row" aria-label="Wallet inactive internal balance">
              <span className="aa-s2-payment-symbol"><WalletCards size={17}/></span><span className="aa-s2-payment-copy"><strong>Wallet</strong><small>Internal balance · not a provider</small></span><span className="aa-s2-state-label is-wallet">Inactive</span>
            </div>
            {methods.map(method=><div className="aa-s2-payment-row" key={method.tag} aria-label={`${method.name}: unavailable, ${method.reason}`}>
              <span className="aa-s2-payment-symbol"><CircleDollarSign size={17}/></span><span className="aa-s2-payment-copy"><strong>{method.name}</strong><small>{method.reason}</small></span><span className="aa-s2-state-label is-disabled"><Clock3 size={12}/>Unavailable</span>
            </div>)}
          </div>
        </section>
        <section className="aa-s2-payment-explainer" aria-label="Payment distinctions">
          <div><h3>Provider credentials are not configured</h3><p>Inactive provider rows do not issue payment requests. Sandbox payment setup is not implied or shown as tested. Orange and MTN merchant credentials are not configured; authoritative Orange callback/status verification is also incomplete.</p></div>
          <div><h3>No separate bank-transfer method</h3><p>The original catalog contains no standalone bank-transfer rail. Maksekeskus provider banklinks are part of that provider, not an offline transfer option. No bank transfer has been added.</p></div>
        </section>
        <div className="aa-s2-future-note"><strong>Future recommendation—not current behavior.</strong> If operations are enabled in a later, separately approved release, show region- and account-scoped readiness with a clear reason for every disabled method before any selection step. Confirm provider verification and collection state separately from booking, order, wallet balance, settlement, refunds, and payouts.</div>
        <div style={{marginTop:20}}><PreviewBadge>This board is read-only. No method card is a button and no selection can be made. Existing transactions and wallet history remain untouched.</PreviewBadge></div>
      </div>
    </main>
  </PageShell>;
}