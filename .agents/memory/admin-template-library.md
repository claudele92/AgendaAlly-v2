---
name: Admin email template library requirements
description: User-defined centralized native Admin library and real UI acceptance boundary.
---

AgendaAlly needs a centralized Email Template Library in the normal Admin
Settings/email configuration experience, not a developer-only page or an
account-only template feature. Represent every existing sender honestly:
managed content, application-controlled sender, or deferred/unimplemented.
Never label content editable when its sender does not consume it. Creating a
template must not invent an event, recipient rule, sender or workflow.

**Why:** The user corrected the earlier delivery: persisted rows, unit/API tests
and synthetic renders do not prove the owner can discover/manage templates in
the authenticated Admin panel.

**How to apply:** Preserve native routes/design/authorization and protected
system identities. Test the real authenticated list/open/save/reload/preview
path; if agent access is unavailable, finish safe work and obtain one concise
owner UI confirmation without inspecting Admin credentials or bypassing auth.
Do not begin gated real-email acceptance before that UI gate is satisfied.

The authoritative built-in managed library is Email Verification, Password
Reset, and ONE Subscription / Digest default. Provision missing content
idempotently; never overwrite customized Subscription content or create
subscribers, schedules, deliveries, outbox records, jobs, or email. Order/Invoice,
Driver Invitation and Admin Test Email remain application-controlled unless
their real senders are separately wired; Refund/Payout remains deferred.

**Why:** The user explicitly made the later final product requirement
authoritative after resolving conflicting Subscription-default briefs.

**How to apply:** Preserve existing Subscription workflows, but keep library
provisioning/content editing/preview separate from activation. Do not count
owner confirmation of the two account records as acceptance of an incomplete
three-template library or its remaining management checks.

The owner accepted focused provisioning/idempotency/CRUD/Preview/protection/
preservation evidence as sufficient to proceed to Phase B, while explicitly
keeping the remaining authenticated Admin browser CRUD UNVERIFIED, not failed
or passing. Do not start another Admin implementation campaign unless a real
UI defect is subsequently observed; scoring must remain conservative.

**Why:** The owner explicitly narrowed the remaining acceptance boundary after
confirming native discoverability and the three-row library.

**How to apply:** Preserve the UNVERIFIED record and continue the authorized
single Password Reset journey without re-requesting Admin CRUD confirmation.

Library presentation must be inert, separate from scheduled campaign state.
Retain the oldest existing Subscription content as its default rather than
replacing customized copy; additional Subscription records remain custom.

**Why:** The native table combines presentation with campaign fields. Reusing
pending/processed campaign semantics for library content can schedule delivery
or re-arm a previously processed campaign merely through a content edit.
The bounded approach avoids schema changes and preserves existing workflows.

**How to apply:** Keep new library presentation out of pending campaign state,
preserve existing campaign state during edits, and verify both inert library
behavior and unchanged native campaign selection with transport-disabled tests.
