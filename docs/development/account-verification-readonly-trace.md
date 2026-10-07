# Account verification: read-only trace

Date: 2026-10-05. No signup/code submission, resend, reset issuance, account change, queue processing, preview restart or application/configuration edit was performed during this diagnosis.

## Runtime provenance
The owner's supplied external Customer origin on port 3002 serves `/sign-up` successfully. Its public countries endpoint and the local Customer proxy both return HTTP 200 with identical four-country resources.

The active Next development routes manifest—not merely source configuration—maps `/api/v1/:path*` to `http://127.0.0.1:8000/api/v1/:path*`. The running Customer process uses `.migration-backup/web` on 3002. The normal Laravel process on 8000 uses `.migration-backup/backend`; its launcher validates the native local configuration, and the native bootstrap confirms the approved owned SQLite database:

`.migration-backup/backend/database/development/agendaally.sqlite`.

The port-8008 acceptance runtime is separate. It is not the active Customer API rewrite destination. No wrong-database routing was found in the current runtime.

## Verification submission boundary
The native six-input OTP control only updates component state while typing. It does **not** automatically submit the sixth digit. Clicking **Verify** invokes `POST /api/v1/auth/verify/email` with the recipient and code.

The controller calls the native recipient-bound code consumption service. A successful consume writes `email_verified_at`, clears the verification marker/cache, then the controller creates an access token. Profile/password completion happens afterward; an unfinished profile does not explain an absent verification timestamp after a successful consume.

The approved disposable Customer still has no verification timestamp, no password, and zero access tokens. Its audit entries show creation/code renewal, not a successful verification update.

## Retained requests and code lifetime
Available normal-listener logs begin with the runtime startup at **23:28 UTC (6:28pm Central)**. They contain no verification-code or after-verification POST. This does **not** establish what happened before that retained window; the historical code-submission HTTP status cannot be recovered from these logs.

The one authorized message's code was issued at **22:59:41 UTC (5:59:41pm Central)**, sent at **23:02:16 UTC (6:02:16pm Central)**, and expired at **23:09:41 UTC (6:09:41pm Central)**. A submission after that expiry cannot verify the account.

Later normal-source registration requests were recorded at **23:48:23** and **23:52:56 UTC**, with new verification outbox records at **23:48:28** and **23:52:56**. Both records belong to the same disposable Customer, not duplicate accounts. Each native signup/code renewal replaces the current recipient-bound challenge, additionally superseding earlier codes.

These observations explain why entering a received code is not proof of successful verification. The evidence cannot yet distinguish a Verify button never clicked, a browser-side submission failure, or an earlier server rejection. No claim of a specific rejected HTTP response is made without evidence.

## Why later signup did not send email
General email remains **log-only**, and selected-record send authority is default-off with no approval file. Signup queues encrypted native verification records; it does not authorize SMTP delivery. The later records remain PENDING and unsent. This is the controlled-delivery boundary, not evidence of a failed SMTP connection.

## Additional pending state discovered
The retained listener also recorded `POST /api/v1/auth/forgot/email-password` at **23:42:18 UTC**. A reset record for the disposable Customer was created at **23:42:19**; it remains PENDING and unsent. The request log does not identify the initiating person/browser. This diagnosis did not issue it.

At inspection, the normal source has:
- One historical SENT verification (the owner-confirmed Gmail receipt).
- Two additional PENDING verification records.
- One PENDING reset record.
- Three fresh, unreserved native jobs with zero attempts.
- No selected SMTP approval file.

No pending record/job was processed, deleted, cancelled or altered. Queue-empty preconditions no longer hold. The reset stage stays stopped because the account is unverified and additional pending state needs explicit handling.

## Acceptance
Owner-confirmed verification Gmail receipt, logo/social assets and presentation remain accepted. Verification must not be resent under the current authority. Account verification and reset acceptance are not complete.

**C1=0.5; O4=0.5; 16/20=80% unchanged.**
