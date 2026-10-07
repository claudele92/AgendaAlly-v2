import { useId, useState } from "react";
import type { InputHTMLAttributes, ReactNode } from "react";
import { ArrowRight, Eye, EyeOff, ShieldCheck, Sparkles } from "lucide-react";

export function Brand({ compact = false }: { compact?: boolean }) {
  return <span className="aa-brand" aria-label="AgendaAlly"><span className="aa-brand-mark">a.</span>{!compact && <span className="aa-brand-name">AgendaAlly</span>}</span>;
}

export function openProposalScreen(screen: "CustomerLogin" | "CustomerRegister" | "BusinessLogin") {
  window.location.assign(`/__mockup/preview/agendaally-stage1/${screen}`);
}

export function Button({ children, variant = "primary", className = "", ...props }: {
  children: ReactNode; variant?: "primary" | "secondary" | "quiet"; className?: string;
} & React.ButtonHTMLAttributes<HTMLButtonElement>) {
  return <button className={`aa-button ${variant === "secondary" ? "aa-button-secondary" : variant === "quiet" ? "aa-button-quiet" : ""} ${className}`} {...props}>{children}</button>;
}

export function TextLink({ children, onClick, ...props }: { children: ReactNode; onClick?: () => void } & React.ButtonHTMLAttributes<HTMLButtonElement>) {
  return <button type="button" className="aa-text-link" onClick={onClick} {...props}>{children}</button>;
}

export function Field({ label, error, hint, id: suppliedId, className = "", ...props }: {
  label: string; error?: string; hint?: string; id?: string; className?: string;
} & InputHTMLAttributes<HTMLInputElement>) {
  const generatedId = useId();
  const id = suppliedId || generatedId;
  return <label className={`aa-field ${className}`} htmlFor={id}>
    <span className="aa-label">{label}{props.required ? <span aria-hidden="true"> *</span> : null}</span>
    <input id={id} className="aa-input" aria-invalid={error ? "true" : undefined} aria-describedby={error ? `${id}-error` : hint ? `${id}-hint` : undefined} {...props} />
    {error && <span id={`${id}-error`} role="alert" style={{ color: "var(--aa-error)", fontSize: 11 }}>{error}</span>}
    {!error && hint && <span id={`${id}-hint`} style={{ color: "var(--aa-muted)", fontSize: 11 }}>{hint}</span>}
  </label>;
}

export function PasswordField({ label = "Password", ...props }: { label?: string } & Omit<InputHTMLAttributes<HTMLInputElement>, "type">) {
  const [visible, setVisible] = useState(false);
  const id = useId();
  return <div className="aa-field">
    <label className="aa-label" htmlFor={id}>{label}{props.required && <span aria-hidden="true"> *</span>}</label>
    <div className="aa-input-wrap"><input id={id} className="aa-input" type={visible ? "text" : "password"} autoComplete="off" {...props} />
      <button type="button" className="aa-password-toggle" aria-label={visible ? "Hide password" : "Show password"} aria-pressed={visible} onClick={() => setVisible(!visible)}>{visible ? <EyeOff size={17}/> : <Eye size={17}/>}</button>
    </div>
  </div>;
}

export function ContactSwitch({ value, onChange }: { value: "email" | "phone"; onChange: (value: "email" | "phone") => void }) {
  return <div className="aa-segment" role="group" aria-label="Choose contact method">
    <button type="button" aria-pressed={value === "email"} onClick={() => onChange("email")}>Email address</button>
    <button type="button" aria-pressed={value === "phone"} onClick={() => onChange("phone")}>Phone number</button>
  </div>;
}

export function PreviewNotice({ children, tone = "preview" }: { children: ReactNode; tone?: "preview" | "error" | "success" }) {
  return <div className={`aa-status ${tone === "error" ? "aa-status-error" : tone === "preview" ? "aa-status-preview" : ""}`} role="status">
    <ShieldCheck size={15} aria-hidden="true" style={{ flex: "0 0 auto", marginTop: 1 }}/><span>{children}</span>
  </div>;
}

export function AuthHeader({ action, onAction, business = false }: { action: string; onAction: () => void; business?: boolean }) {
  return <header className="aa-auth-top"><a href="#top" className="aa-brand" onClick={(event) => event.preventDefault()}><Brand/></a>
    <div className="aa-auth-top-right">
      {business ? <span className="aa-business-context" style={{ color: "var(--aa-muted)", fontSize: 12 }}>Business workspace</span> : <button type="button" className="aa-text-link aa-business-link" onClick={() => openProposalScreen("BusinessLogin")}>For businesses</button>}
      <Button variant="secondary" onClick={onAction} style={{ minHeight: 40, padding: "0 13px", fontSize: 12 }}>{action}<ArrowRight size={14}/></Button>
    </div>
  </header>;
}

export function AuthArtwork({ type }: { type: "customer" | "business" }) {
  const customer = type === "customer";
  return <aside className="aa-auth-art" style={{ backgroundImage: `url("/__mockup/images/agendaally-stage1/${customer ? "customer" : "business"}-auth-hero.jpg")` }} aria-label={customer ? "Client welcomed at a beauty studio" : "Professional at a reception desk"}>
    <div className="aa-art-top"><Sparkles size={13} aria-hidden="true"/> {customer ? "Good care, close to home" : "A clearer day at work"}</div>
    <div className="aa-art-copy">
      <h1>{customer ? <>Make room<br/>for you.</> : <>Your work.<br/>In good order.</>}</h1>
      <p>{customer ? "Find trusted local professionals, book with confidence, and shop things worth bringing home." : "Keep appointments, customers and day-to-day business in one calm, capable workspace."}</p>
    </div>
    <div className="aa-art-foot">{customer ? "Local people. Thoughtful service." : "A workspace built for independent business."}</div>
  </aside>;
}

export function PreviewSubmit({ children = "Continue" }: { children?: ReactNode }) {
  return <Button type="submit" className="aa-submit">{children}<ArrowRight size={16}/></Button>;
}

export function PreviewCaption() {
  return <p className="aa-preview-caption">Interactive design preview only. No credentials are saved, and no request is sent.</p>;
}