# Controlled normal Admin SMTP testing

The owner selected the existing normal Admin on port **3003** as the controlled
MVP integration-test environment. Do not create another preview/environment or
copy, retrieve, replace, expose or log its stored SMTP credential.

Only the existing Admin `sendTest()` action gains permission. The saved provider
and its encrypted credential are unchanged. General email stays `log`/disabled,
Laravel mailer stays `log`/`array`, payments remain disabled, SMS log-only/disabled,
and Firebase/push/maps remain off. Workers, verification/reset mail, booking and
other notifications do not gain the exception.

## Enablement and custody

The existing `original-laravel-preview` workflow is explicitly launched with:

```
AGENDAALLY_NORMAL_ADMIN_SMTP_TEST=true node scripts/development.mjs serve backend
```

Do not add the flag globally or to the credential's private dotenv. Only the owned
debug-off local SQLite backend on port 8000 can use it. The launcher supplies a
server-only PHP INI authority, and releases only `stream_socket_client` because
PHPMailer needs it for verified TLS. All other existing SDK/HTTP/mail/command
restrictions remain. Application suppression remains mandatory for every other
email path. CLI workers, other bootstraps/databases, production, published apps,
and unsafe integration configurations fail closed.

To disable the control, launch that same workflow with the flag **false** and
restart it. The launcher restores the original socket restriction automatically.
No SMTP permission is enabled in isolated staging.

## Owner's first test

1. Use your existing **port-3003** Admin → Settings → Email providers.
2. Reload and open the existing Zoho provider. Its password field remains blank
   with configured status; no re-entry or replacement is needed.
3. Enter the recipient you control and click **Send test email once** yourself.
   The action uses saved settings and verified TLS (465 = implicit TLS).
4. Do not automatically retry failures or unconfirmed results. SMTP acceptance
   does not prove mailbox receipt. Confirm receipt separately.
5. Request disabling the Admin-test permission when finished.

Enabling this control does not authorize the agent to send a message. No other
integration or notification path is approved until individually authorized.
