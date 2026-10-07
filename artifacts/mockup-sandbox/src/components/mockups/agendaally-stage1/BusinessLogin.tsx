import "./_group.css";
import { useState, type FormEvent } from "react";
import { BriefcaseBusiness, CircleHelp, Fingerprint } from "lucide-react";
import { AuthArtwork, AuthHeader, ContactSwitch, Field, openProposalScreen, PasswordField, PreviewCaption, PreviewNotice, PreviewSubmit, TextLink } from "./_shared/Stage1Primitives";

export function BusinessLogin() {
  const [contactType, setContactType] = useState<"email" | "phone">("email");
  const [contact, setContact] = useState("");
  const [password, setPassword] = useState("");
  const [captchaReady, setCaptchaReady] = useState(false);
  const [error, setError] = useState("");
  const [notice, setNotice] = useState("");

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (!contact.trim() || !password) { setError("Enter your work email or phone number, and password."); return; }
    if (contactType === "email" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(contact)) { setError("Enter a valid email address."); return; }
    if (!captchaReady) { setError("Complete the configured verification slot to continue."); return; }
    setError("");
    setNotice("Preview only — no sign-in request was sent. Your destination is determined by your account permissions.");
  };
  return <div className="aa-stage1 aa-auth-page" id="top">
    <AuthHeader business action="Customer sign in" onAction={() => openProposalScreen("CustomerLogin")}/>
    <main className="aa-auth-main aa-business-main">
      <section className="aa-auth-panel" aria-labelledby="business-login-title">
        <div className="aa-auth-form">
          <div className="aa-mobile-note"><BriefcaseBusiness size={16}/> One practical workspace for your day-to-day business.</div>
          <div className="aa-eyebrow" style={{ marginBottom: 11 }}>AgendaAlly for business</div>
          <h2 id="business-login-title" className="aa-auth-heading">Good to have you back.</h2>
          <p className="aa-auth-subheading">Sign in to manage your work, team and customers.</p>
          <form onSubmit={submit} noValidate>
            <div className="aa-form-stack">
              <ContactSwitch value={contactType} onChange={(value) => { setContactType(value); setContact(""); setError(""); setNotice(""); }}/>
              <Field label={contactType === "email" ? "Work email" : "Phone number"} type={contactType === "email" ? "email" : "tel"} inputMode={contactType === "email" ? "email" : "tel"} autoComplete="off" placeholder={contactType === "email" ? "name@yourbusiness.com" : "+234 800 000 0000"} value={contact} onChange={(event) => { setContact(event.target.value); setError(""); setNotice(""); }} required/>
              <PasswordField label="Password" autoComplete="off" placeholder="Enter your password" value={password} onChange={(event) => { setPassword(event.target.value); setError(""); setNotice(""); }} required/>
              <div className="aa-inline-between">
                <span style={{ color: "var(--aa-muted)", fontSize: 12 }}>Protected workspace</span>
                <TextLink onClick={() => setNotice("Password recovery is not connected in this local preview.")}>Forgot password?</TextLink>
              </div>
              <div className="aa-captcha-slot">
                <span className="aa-captcha-mark"><Fingerprint size={17}/></span>
                <div style={{ flex: 1 }}><strong>Configured verification</strong><span>Provider integration appears here when enabled.</span></div>
                <label style={{ display: "flex", alignItems: "center", gap: 7, color: "var(--aa-muted)", fontSize: 10, whiteSpace: "nowrap" }}>
                  <input aria-label="Simulate configured verification in preview" type="checkbox" checked={captchaReady} onChange={(event) => { setCaptchaReady(event.target.checked); setError(""); }}/> Preview
                </label>
              </div>
              {error && <PreviewNotice tone="error">{error}</PreviewNotice>}
              {notice && <PreviewNotice>{notice}</PreviewNotice>}
              <PreviewSubmit>Sign in</PreviewSubmit>
            </div>
          </form>
          <p className="aa-auth-footnote">Your access and landing workspace follow your assigned account permissions.</p>
          <div style={{ display: "flex", justifyContent: "center", alignItems: "center", gap: 6, marginTop: 12, color: "var(--aa-subtle)", fontSize: 10 }}><CircleHelp size={13}/> Having trouble? Contact your account administrator.</div>
          <PreviewCaption/>
        </div>
      </section>
      <AuthArtwork type="business"/>
    </main>
  </div>;
}

export default BusinessLogin;