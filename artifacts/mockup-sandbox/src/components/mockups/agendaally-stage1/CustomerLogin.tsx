import "./_group.css";
import { useState, type FormEvent } from "react";
import { ArrowUpRight, CalendarDays, Check, Eye, EyeOff } from "lucide-react";
import { AuthArtwork, AuthHeader, ContactSwitch, Field, openProposalScreen, PreviewCaption, PreviewNotice, PreviewSubmit, TextLink } from "./_shared/Stage1Primitives";

export function CustomerLogin() {
  const [contactType, setContactType] = useState<"email" | "phone">("email");
  const [notice, setNotice] = useState("");
  const [recovery, setRecovery] = useState(false);
  const [error, setError] = useState("");
  const [contact, setContact] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);

  const submit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setError("");
    if (!contact.trim() || !password) {
      setError(`Enter your ${contactType === "email" ? "email address" : "phone number"} and password to continue.`);
      return;
    }
    if (contactType === "email" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(contact)) {
      setError("Enter a valid email address.");
      return;
    }
    setNotice(recovery ? "Password recovery is not connected in this preview. Nothing was sent or saved." : "Preview only — sign-in is not connected. Nothing was sent or saved.");
  };
  const switchContact = (value: "email" | "phone") => { setContactType(value); setContact(""); setError(""); setNotice(""); };

  return <div className="aa-stage1 aa-auth-page" id="top">
    <AuthHeader action="Create account" onAction={() => openProposalScreen("CustomerRegister")}/>
    <main className="aa-auth-main">
      <AuthArtwork type="customer"/>
      <section className="aa-auth-panel" aria-labelledby="customer-login-title">
        <div className="aa-auth-form">
          <div className="aa-mobile-note"><CalendarDays size={16}/> Find local services and thoughtful products, all in one place.</div>
          <div className="aa-eyebrow" style={{ marginBottom: 11 }}>Welcome back</div>
          <h2 id="customer-login-title" className="aa-auth-heading">{recovery ? "Reset your password" : "Sign in to AgendaAlly"}</h2>
          <p className="aa-auth-subheading">{recovery ? "Use the email or phone number linked to your account." : "Your next appointment, right where you left it."}</p>
          <form onSubmit={submit} noValidate>
            <div className="aa-form-stack">
              <ContactSwitch value={contactType} onChange={switchContact}/>
              <Field
                label={contactType === "email" ? "Email address" : "Phone number"}
                type={contactType === "email" ? "email" : "tel"}
                inputMode={contactType === "email" ? "email" : "tel"}
                autoComplete={contactType === "email" ? "off" : "off"}
                placeholder={contactType === "email" ? "you@example.com" : "+234 800 000 0000"}
                value={contact}
                onChange={(event) => { setContact(event.target.value); setError(""); setNotice(""); }}
                required
                aria-label={contactType === "email" ? "Email address" : "Phone number"}
              />
              {!recovery && <div className="aa-field">
                <label className="aa-label" htmlFor="customer-password">Password</label>
                <div className="aa-input-wrap">
                  <input id="customer-password" className="aa-input" type={showPassword ? "text" : "password"} autoComplete="off" placeholder="Enter your password" value={password} onChange={(event) => { setPassword(event.target.value); setError(""); setNotice(""); }} required/>
                  <button type="button" className="aa-password-toggle" aria-label={showPassword ? "Hide password" : "Show password"} aria-pressed={showPassword} onClick={() => setShowPassword(!showPassword)}>{showPassword ? <EyeOff size={16}/> : <Eye size={16}/>}</button>
                </div>
              </div>}
              {!recovery && <div className="aa-inline-between">
                <span style={{ color: "var(--aa-muted)", fontSize: 12 }}><Check size={13} style={{ display: "inline", marginRight: 4, verticalAlign: "-2px" }}/> Secure account access</span>
                <TextLink onClick={() => { setRecovery(true); setError(""); setNotice(""); }}>Forgot password?</TextLink>
              </div>}
              {error && <PreviewNotice tone="error">{error}</PreviewNotice>}
              {notice && <PreviewNotice>{notice}</PreviewNotice>}
              <PreviewSubmit>{recovery ? "Continue" : "Sign in"}</PreviewSubmit>
            </div>
          </form>
          {recovery && <p className="aa-auth-footnote"><TextLink onClick={() => { setRecovery(false); setError(""); setNotice(""); }}>Back to sign in</TextLink></p>}
          {!recovery && <p className="aa-auth-footnote">New to AgendaAlly? <TextLink onClick={() => openProposalScreen("CustomerRegister")}>Create an account <ArrowUpRight size={12} style={{ display: "inline", verticalAlign: "-2px" }}/></TextLink></p>}
          <PreviewCaption/>
        </div>
      </section>
    </main>
  </div>;
}

export default CustomerLogin;