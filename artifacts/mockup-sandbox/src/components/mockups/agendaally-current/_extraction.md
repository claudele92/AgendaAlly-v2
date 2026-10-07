# AgendaAlly current auth extraction

This group is an engineering extraction of the native customer and admin auth
screens, not a screenshot recreation. The original app source is preserved in
the repository's `.migration-backup/` tree; these copies resolve the supplied
`web/...` and `admin/...` source paths from that snapshot. No original source
files were changed.

## Preview targets

- `CurrentCustomerLogin.tsx` — customer login, initially on the email tab.
- `CurrentCustomerRegister.tsx` — customer sign-up and the original
  verification / registration-completion progression.
- `CurrentBusinessLogin.tsx` — admin login.

Both customer targets share the extracted auth layout, header, login form, and
registration flow in `_shared/`. The header switches login/sign-up locally so
the auth pages remain navigable in an isolated preview.

## Source and dependency trace

### Customer

- `.migration-backup/web/app/(auth)/layout.tsx` → customer auth layout and
  split promotional/form columns in `_shared/CustomerAuth.tsx`.
- `.migration-backup/web/app/(auth)/header.tsx` → header markup in the same
  file; `.migration-backup/web/app/(auth)/input-type-changer.tsx` → email/phone
  selector in `_shared/AuthPrimitives.tsx`.
- `.migration-backup/web/components/auth/auth.tsx` and
  `.migration-backup/web/components/auth/sign-up/sign-up.tsx` → auth view
  progression; `.migration-backup/web/components/auth/login/login.tsx` →
  `_shared/CustomerLoginFlow.tsx`.
- `.migration-backup/web/components/auth/sign-up/components/sign-up-form/sign-up-form.tsx`
  → sign-up form; the actual OTP and completion markup starts from
  `.migration-backup/web/components/auth/sign-up/components/confirmation/confirmation.tsx`
  and `.../components/complete/complete.tsx`. Their form hierarchy and
  classes are retained in `_shared/CustomerRegisterFlow.tsx`.
- `.migration-backup/web/components/button/button.tsx`,
  `.migration-backup/web/components/input/input.tsx`,
  `.migration-backup/web/components/icon-button/icon-button.tsx`, and
  `.migration-backup/web/components/phone-input/phone-input.tsx` → local
  primitives in `_shared/AuthPrimitives.tsx`.
- `.migration-backup/web/app/globals.css` → the auth-body global rule and
  relevant base font styling, scoped here rather than copied into sandbox
  `src/index.css`.
- `.migration-backup/web/tailwind.config.js` → auth-relevant color, radius,
  and image tokens in `_group.css`. The primary fallback is the original
  `#BB9B6A` from `.migration-backup/web/config/global.ts`.
- `.migration-backup/web/public/img/login.png` and
  `.migration-backup/web/public/fonts/inter/InterVariable.woff2` are copied
  into `public/images/agendaally-current/`.
- The email/phone/password/verification labels and promotional copy in
  `_shared/translation.tsx` use the matching English values in
  `.migration-backup/backend/resources/lang/translations.php`.

### Admin

- `.migration-backup/admin/src/views/login/index.jsx` → admin form and login
  page structure in `CurrentBusinessLogin.tsx`.
- `.migration-backup/admin/src/views/login/login.module.scss` → same selectors
  and declarations in `_shared/admin-login.module.css` (the Sass nesting is
  flattened and the background URL is rewritten to the sandbox asset path).
- `.migration-backup/admin/src/assets/images/login-bg.jpg` is copied into
  `public/images/agendaally-current/`.

## Local-preview boundaries and fidelity notes

- Login and sign-up never call auth, user, settings, social-login, or other
  services. Form actions display an explicit “Local preview only” status.
  Sign-up advances through the original stages locally; OTP is not sent or
  verified, and account completion is not submitted.
- Next `Link` / pathname behavior is represented by same-preview fragment
  links and local login/sign-up switching. The language hook is a tiny local
  translation map populated from the original English catalog.
- `react-hook-form` is already installed in the sandbox and is retained for
  the customer fields. Yup is absent, so its schema/resolver is replaced with
  React Hook Form's required/pattern checks; there is no installation.
- The original `react-phone-input-2` package is unavailable. `PhoneInput` is a
  small native tel-input boundary with the source wrapper/field styling,
  country-code affordance, and explicit US fallback; the original country
  dropdown/flag behavior is therefore not reproduced.
- Ant Design and Sass are not installed in the sandbox. The original admin
  JSX is retained and its Ant Design components are served by the small
  `_shared/AdminAntdAdapter.tsx` boundary with corresponding component class
  names and minimal styling in `_group.css`. Original icons are represented
  with Lucide line icons; the original Ant Design validation/notification,
  Redux/store, routing, settings fetch, auth API, and CAPTCHA providers are
  stubbed. CAPTCHA is disabled in the local adapter. These are the admin
  fidelity compromises; no packages were installed.
- The relevant auth styles/tokens are scoped to `_group.css`, imported by all
  three targets. The sandbox-wide `index.css` is untouched.