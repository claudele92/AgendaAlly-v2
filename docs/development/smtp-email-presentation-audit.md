# AgendaAlly SMTP receipt and email presentation audit

**Later owner confirmation:** corrected Admin Test Email logo, Instagram,
Facebook, LinkedIn and footer are now VERIFIED in Gmail. The local-only
qualification below records the earlier correction audit, not current receipt
status. Current authority is the [complete email-system audit](agendaally-email-system-audit.md)
and readiness section 22. No new agent mail was sent.

Date: 2026-10-05. Scope: existing normal Admin, shared mail presentation and
local-only verification. No SMTP test, connection, provider configuration
change or integration activation.

## 1. Verified delivery evidence

The owner confirms that the enabled normal port-3003 Admin Test Email displayed
“Test email sent successfully” and the message arrived in Gmail. The owner
reports the earlier Zoho account/configuration issue is resolved. Record
current-build SMTP transport and actual mailbox delivery **VERIFIED**.
This is explicit owner testimony; no screenshot was attached to this audit turn.
The received-template defects are also owner-reported and independently
reproduced from the current stored nonsecret settings and source.

## 2. Broken logo root cause

`settings.logo` is
`http://localhost:8000/storage/images/settings/agendaally-platform-logo.png`.
The original Blade copied that URL directly into an image src. A Gmail recipient
cannot fetch this workspace's localhost. The owned file exists and is a valid
2172×724 PNG: this is a reachability defect, not an SVG/font failure.
Changing it to a private/transient Replit preview URL would not be reliable.

The corrected renderer resolves the owned public-storage path locally and
attaches the image via PHPMailer CID. No remote asset fetch is attempted.
The canonical logo file and saved settings are unchanged. Missing, unsupported
or out-of-root sources produce a visible text wordmark, not a broken remote image.

## 3. Social icons and link root cause

The original template deliberately rendered literal IG/FB/X/in text within
CSS circles; there were no branded icon assets. It also prepended https://
unconditionally to already complete HTTPS settings, producing malformed
double-scheme destinations.

Current intended destinations match the existing approved development content:

- Instagram: https://www.instagram.com/agendaally
- Facebook: https://www.facebook.com/AgendaAlly/
- LinkedIn: https://www.linkedin.com/company/agendaally
- X/Twitter is unconfigured and remains omitted.

The correction preserves those exact destinations and embeds recognizable
local raster icon artwork. Bare-host, scheme-relative and complete HTTPS
settings normalize once. Wrong-platform, credential-bearing, private,
placeholder, root-only or malformed destinations are omitted. No account
ownership/live social-page availability claim is made; this audit verifies
configured destinations and generated links, not third-party account control.

## 4. Exact bounded changes

- New `app/Support/EmailPresentation.php`: owned public-storage resolution,
  MIME-checked PNG/JPEG/GIF logo embedding, dimensions, inline PNG social assets,
  normalized platform-allowlisted HTTPS links and safe gallery attachments.
- `resources/views/emails/layout.blade.php`: CID logo, branded icon images,
  accessible alt text, inline styling and table-based social layout.
- `EmailSendService.php`: supply the existing mailer to four shared-layout
  consumers and the invoice renderer; resolve gallery attachments to local
  files; keep attachment warnings redacted. No connection/authentication/
  TLS/provider/policy code changed.
- `resources/views/order-email-invoice.blade.php`: same corrected logo/social
  preparation. Invoice MIME headers are left to PHPMailer so multipart/related
  is not contradicted by hardcoded HTML headers.
- `resources/email/icons/{instagram,facebook,linkedin,twitter}.{svg,png}`:
  local vector artwork plus raster output. **Only PNGs enter email MIME**;
  no SVG, webfont, background-image, external icon CDN or data URI is sent.
- Focused isolated tests and safe local evidence/readiness documentation.

## 5. Other templates and external assets

Admin Test Email, subscription, verification and password-reset messages share
the layout and therefore shared both defects. The separate legacy order-email
invoice duplicated the defects and is corrected too. Delivery-driver invitation
is plain HTML with no logo/social imagery; it is not affected by these assets.

The current owned database contains no custom email-template body rows, so no
stored body-image URLs currently require migration. Future CMS body HTML can
still introduce externally referenced assets and needs its own validation;
this correction does not silently rewrite arbitrary content or action links.
Configured galleries are now attached from owned storage rather than invalid
HTTP-shaped file paths. Browser/print invoice views reference Bootstrap/JS CDNs;
they are not the outgoing order-email view and were not altered or certified
for email rendering. Suppressed reset/invitation links and business journeys
are not certified by this Admin test.

## 6. Local verification and limits

Focused tests render actual shared Blade output, build PHPMailer MIME with
`preSend()` only, verify every CID/PNG attachment and compare exact encoded bytes.
Sockets, cURL and PHP mail functions are disabled during the test process.
The visual proof substitutes those MIME bytes into data URIs **only for the
browser preview**. It contains synthetic .invalid sender/recipient addresses.
No real provider password is loaded, no send/postSend method is called, and
these local tests alone do not claim corrected-template Gmail receipt. The
owner's later confirmation now verifies that corrected Admin receipt.

Local tests also cover malformed/private/placeholder destinations, missing/
traversing image paths, duplicate embedding, invoice renderer wiring and
local gallery resolution. Current readiness/security/financial/browser
acceptance is reused, not rerun.

## 7. Readiness

SMTP transport and actual Gmail receipt: **VERIFIED, owner-confirmed**.
Corrected Admin presentation: **VERIFIED in Gmail by owner confirmation**,
in addition to local Blade/MIME/browser verification.
SMTP portion of communications: closed in the authorized normal Admin runtime.
Reset/verification/booking/worker delivery remains suppressed and unverified.

Same twenty gates: **16/20 = 80%, REMAIN IN STAGING**, unchanged from accepted
calendar closure. Customer5 + Vendor4 + Admin3 + operations4. C1 and O4 stay
0.5; a Test Email does not prove their operational mail journeys. No production,
provider activation, payout/refund, SMS/push or unrelated gate credit.

Stop after this local correction. A further real email requires explicit approval.
