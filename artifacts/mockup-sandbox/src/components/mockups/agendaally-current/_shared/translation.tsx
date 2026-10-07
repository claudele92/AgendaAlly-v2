const labels: Record<string, string> = {
  "complete.auth": "Complete registration",
  complete: "Complete",
  email: "Email",
  "example@info.com": "name@example.com",
  "forgot.password": "Forgot password",
  "for.business": "For business",
  firstname: "First name",
  "i.agree.with": "I agree with",
  "online.booking": "Online booking",
  "password.confirmation": "Password confirmation",
  password: "Password",
  phone: "Phone",
  "project.description": "Book your next appointment in minutes with the best masters and salons near you",
  "referral.(optional)": "Referral (optional)",
  resend: "Send again",
  "sign.in": "Sign in",
  "sign.up": "Sign up",
  "terms.and.conditions": "Terms and conditions",
  verify: "Verify",
  "verify.email": "Email verification",
  "verify.phone": "Phone verification",
  "verify.text": "Please, enter the verification code we’ve sent you to",
  lastname: "Last name",
  login: "Login",
  required: "Required",
  or: "or",
};

export function useTranslation() {
  return {
    t: (key: string) => labels[key] ?? key,
  };
}

export function Translate({ value }: { value: string }) {
  const { t } = useTranslation();
  return <>{t(value)}</>;
}