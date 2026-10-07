# Isolated Admin SMTP test

**Current MVP direction:** The owner chose the existing normal Admin on port
3003 instead. Use [Controlled normal Admin SMTP testing](normal-admin-smtp-test.md).
The isolated permission remains off; do not copy the normal credential here.

This capability is default-off. General email mode stays `log`, Laravel's mailer
stays `array`, and the notification worker/scheduler do not gain SMTP permission.
No real message is part of local security verification.

## Credential security

- Writes use versioned application-key encryption; the model hides the password
  and Admin resources return only credential status.
- A blank/null update preserves the existing encrypted credential. A supplied
  nonblank password replaces it. API creation requires a nonblank credential.
- Legacy plaintext and corrupt/wrong-key ciphertext fail closed. The explicit
  `email-settings:encrypt-credentials --apply` command encrypts legacy values
  **in place** in the owned local or isolated, log-only runtime. It prints counts
  only and does not import/export credentials or send mail.
- Runtime application keys must remain available. Do not move either plaintext
  or ciphertext between the normal preview and staging.
- Existing backups/history are not rewritten by an in-place database transition.
  This is not a claim of forensic erasure or production key-custody readiness.
- SMTP uses certificate/hostname verification and rejects self-signed server
  certificates. Port 465 uses implicit TLS; other supported ports use STARTTLS.
- Test errors expose only generic messages and safe classifications. Unconfirmed
  delivery must not be retried automatically.

The native MySQL credential column requires TEXT capacity. The bounded schema
change is in `database/isolated-migrations`, outside the frozen historical
migration manifest. Apply with the isolated schema owner's authority; the
ordinary runtime database principal deliberately lacks DDL privileges. SQLite
already has unbounded storage and its protected schema is not changed.

## Owner's manual Admin steps

1. Open the **isolated Business/Admin preview**, not the normal Admin preview.
2. Navigate to **Settings → Email providers** (`/settings/emailProviders`).
3. Add a provider. Enter host `smtp.zoho.com`, port `465`, the authorized Zoho
   sender/mailbox in From Email, and the desired From Site/display name.
4. Turn SMTP Auth **on**, Active **off**, and SMTP Debug **off**. Manually enter
   the correct Zoho SMTP credential (an app password if that account requires
   one). Do not share it in chat or copy it through an agent.
5. Save. Reopen the provider. The password field must be blank and its status
   must read **configured**. Ordinary edits can now leave that field blank.
6. Stop here until separately authorizing the isolated Admin test permission.
   Only an operator should set `AGENDAALLY_ISOLATED_ADMIN_SMTP_TEST=true` for the
   isolated supervisor and restart that existing workflow. Do not set
   `EMAIL_MODE=smtp`, change the general mailer, or activate other providers.
7. After approval/enablement, reload this provider. Enter exactly one recipient
   you control in the Test Email field, then click **Send test email once**.
   It uses saved settings, not unsaved form values.
8. Check that mailbox and the result. If delivery is unconfirmed, investigate
   rather than repeatedly clicking. Turn the isolated test permission off again
   immediately afterward and restart the isolated workflow.

The exception also checks the isolated bootstrap authority/path, database,
loopback origins, production-mode/debug-off isolation configuration and log-only
email policy. An ordinary application environment variable alone cannot bypass
normal or production suppression. Restore instances never get this authority.
Published Replit runtimes are also explicitly denied, even if someone copies
the isolated bootstrap or its opt-in flag.

## Focused local verification

- PHP regression: `vendor/bin/phpunit -c phpunit-hardening.xml tests/Hardening/SmtpCredentialSecurityTest.php`
- Form contract: `node --test .migration-backup/admin/src/views/email-provider/credential-form.test.mjs`
- Isolated API fixture: `node scripts/staging/test-smtp-settings.mjs`

The API fixture requires the existing synthetic Admin storage state. It creates
only an inactive `.invalid` provider, tests encrypted CRUD and the **suppressed**
test endpoint, checks selected new log bytes for synthetic secrets, and deletes
its own record. It must not be run after enabling real SMTP test permission.
