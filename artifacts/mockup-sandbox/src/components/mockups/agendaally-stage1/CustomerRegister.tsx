import "./_group.css";
import { useState, type FormEvent } from "react";
import { ArrowLeft, ShieldCheck } from "lucide-react";
import { AuthArtwork, AuthHeader, Button, ContactSwitch, Field, openProposalScreen, PasswordField, PreviewCaption, PreviewNotice, PreviewSubmit, TextLink } from "./_shared/Stage1Primitives";

type Stage = "contact" | "verify" | "profile";

export function CustomerRegister() {
  const [stage, setStage] = useState<Stage>("contact");
  const [contactType, setContactType] = useState<"email" | "phone">("email");
  const [contact, setContact] = useState("");
  const [terms, setTerms] = useState(false);
  const [code, setCode] = useState("");
  const [firstName, setFirstName] = useState("");
  const [lastName, setLastName] = useState("");
  const [referral, setReferral] = useState("");
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [error, setError] = useState("");
  const [notice, setNotice] = useState("");

  const changeContact = (value: "email" | "phone") => { setContactType(value); setContact(""); setError(""); setNotice(""); };
  const onContact = (event: FormEvent) => {
    event.preventDefault();
    if (!contact.trim()) { setError(`Enter your ${contactType === "email" ? "email address" : "phone number"}.`); return; }
    if (contactType === "email" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(contact)) { setError("Enter a valid email address."); return; }
    if (!terms) { setError("Please agree to the terms and conditions to continue."); return; }
    setError("");
    setNotice("Design preview: next step shown locally. No verification request was sent.");
    setStage("verify");
  };
  const onVerify = (event: FormEvent) => {
    event.preventDefault();
    if (code.length !== 6) { setError("Enter all six digits to preview the next step."); return; }
    setError("");
    setNotice("Design preview: code was not checked. Profile details are shown for review.");
    setStage("profile");
  };
  const onProfile = (event: FormEvent) => {
    event.preventDefault();
    if (!firstName.trim() || !lastName.trim()) { setError("Add your first and last name."); return; }
    if (password.length < 8) { setError("Use at least 8 characters for this preview."); return; }
    if (password !== confirmPassword) { setError("Passwords do not match."); return; }
    setError("");
    setNotice("Preview complete — no account was created, and no details were saved.");
  };
  const goBack = () => { setError(""); setNotice(""); setStage(stage === "profile" ? "verify" : "contact"); };
  const stepNumber = stage === "contact" ? 1 : stage === "verify" ? 2 : 3;
  const descriptions = ["Contact & terms", "Verify", "Your profile"];
  return <div className="aa-stage1 aa-auth-page" id="top">
    <AuthHeader action="Sign in" onAction={() => openProposalScreen("CustomerLogin")}/>
    <main className="aa-auth-main">
      <AuthArtwork type="customer"/>
      <section className="aa-auth-panel" aria-labelledby="register-title">
        <div className="aa-auth-form">
          <div className="aa-mobile-note"><ShieldCheck size={16}/> A simple, considered way to connect with local services.</div>
          <div className="aa-stepper" aria-label={`Registration step ${stepNumber} of 3`}>
            {descriptions.map((label, index) => <span key={label} style={{ display: "contents" }}>
              {index > 0 && <span className="aa-step-line"/>}
              <span className={`aa-step-dot ${index + 1 === stepNumber ? "is-active" : index + 1 < stepNumber ? "is-done" : ""}`} data-step={index + 1}>{label}</span>
            </span>)}
          </div>
          <div className="aa-eyebrow" style={{ marginBottom: 11 }}>Create your account</div>
          <h2 id="register-title" className="aa-auth-heading">{stage === "contact" ? "Start with your details" : stage === "verify" ? "Check your contact" : "Make it yours"}</h2>
          <p className="aa-auth-subheading">
            {stage === "contact" ? "We’ll use one contact method to help you get started." : stage === "verify" ? <>A six-digit code would be sent to <strong style={{ color: "var(--aa-ink)", fontWeight: 600 }}>{contact}</strong>.</> : "A few details and you’ll be ready to explore."}
          </p>

          {stage === "contact" && <form onSubmit={onContact} noValidate>
            <div className="aa-form-stack">
              <ContactSwitch value={contactType} onChange={changeContact}/>
              <Field label={contactType === "email" ? "Email address" : "Phone number"} type={contactType === "email" ? "email" : "tel"} inputMode={contactType === "email" ? "email" : "tel"} autoComplete="off" placeholder={contactType === "email" ? "you@example.com" : "+234 800 000 0000"} value={contact} onChange={(event) => { setContact(event.target.value); setError(""); setNotice(""); }} required/>
              <label className="aa-inline-between" style={{ justifyContent: "flex-start", alignItems: "flex-start", gap: 10, cursor: "pointer" }}>
                <input className="aa-check" type="checkbox" checked={terms} onChange={(event) => { setTerms(event.target.checked); setError(""); }}/>
                <span className="aa-legal">I agree to the <strong style={{ color: "var(--aa-bronze-ink)", fontWeight: 650 }}>Terms and Conditions</strong>.</span>
              </label>
              {error && <PreviewNotice tone="error">{error}</PreviewNotice>}
              {notice && <PreviewNotice>{notice}</PreviewNotice>}
              <PreviewSubmit>Create account</PreviewSubmit>
            </div>
          </form>}

          {stage === "verify" && <form onSubmit={onVerify} noValidate>
            <div className="aa-form-stack">
              <Field label="Six-digit verification code" inputMode="numeric" autoComplete="off" placeholder="Enter 6 digits" maxLength={6} value={code} onChange={(event) => { setCode(event.target.value.replace(/\D/g, "").slice(0, 6)); setError(""); }} required/>
              <div className="aa-inline-between"><span style={{ color: "var(--aa-muted)", fontSize: 12 }}>Didn't receive a code?</span><TextLink onClick={() => setNotice("Design preview only — a new code was not sent.")}>Resend code</TextLink></div>
              {error && <PreviewNotice tone="error">{error}</PreviewNotice>}
              {notice && <PreviewNotice>{notice}</PreviewNotice>}
              <PreviewSubmit>Continue</PreviewSubmit>
              <Button type="button" variant="quiet" onClick={goBack}><ArrowLeft size={14}/> Back to contact details</Button>
            </div>
          </form>}

          {stage === "profile" && <form onSubmit={onProfile} noValidate>
            <div className="aa-form-stack">
              <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 10 }}>
                <Field label="First name" autoComplete="off" value={firstName} onChange={(event) => { setFirstName(event.target.value); setError(""); }} required/>
                <Field label="Last name" autoComplete="off" value={lastName} onChange={(event) => { setLastName(event.target.value); setError(""); }} required/>
              </div>
              <Field label="Referral code (optional)" autoComplete="off" placeholder="If you have one" value={referral} onChange={(event) => setReferral(event.target.value)}/>
              <PasswordField label="Create password" autoComplete="new-password" placeholder="At least 8 characters" value={password} onChange={(event) => { setPassword(event.target.value); setError(""); }} required/>
              <PasswordField label="Confirm password" autoComplete="new-password" placeholder="Enter it once more" value={confirmPassword} onChange={(event) => { setConfirmPassword(event.target.value); setError(""); }} required/>
              {error && <PreviewNotice tone="error">{error}</PreviewNotice>}
              {notice && <PreviewNotice>{notice}</PreviewNotice>}
              <PreviewSubmit>Finish preview</PreviewSubmit>
              <Button type="button" variant="quiet" onClick={goBack}><ArrowLeft size={14}/> Back to verification</Button>
            </div>
          </form>}
          <p className="aa-auth-footnote">{stage === "contact" ? <>Already have an account? <TextLink onClick={() => openProposalScreen("CustomerLogin")}>Sign in</TextLink></> : <>Your details stay on this screen only. <TextLink onClick={goBack}>Go back</TextLink></>}</p>
          <PreviewCaption/>
        </div>
      </section>
    </main>
  </div>;
}

export default CustomerRegister;