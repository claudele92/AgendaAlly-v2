---
name: Financial notification state semantics
description: Approved manual financial execution direction and authoritative completion/notification boundaries.
---

Payout Requested/Approved/Completed/Rejected and Refund
Requested/Approved/Completed/Rejected templates must follow the manual
Refund/Payout state machine. APPROVED must never imply COMPLETED.
COMPLETED/REFUNDED messages must originate only from authoritative financial
completion states.

**Why:** The user explicitly specified these semantics for the upcoming manual
Refund/Payout design, independently of the account-email template work.

**How to apply:** Preserve this distinction when designing or implementing
financial notifications later; account-template implementation is not authority
to add them or infer completion from approval.

The approved MVP direction is manual financial execution. AgendaAlly controls
request, authorization, eligibility, workflow, evidence, audit, internal
accounting/entitlement, reconciliation and later notifications. Actual Refund/
Payout money movement occurs externally through an authorized Finance/Admin
operator, who records authoritative completion evidence.

**Why:** The user explicitly approved this product direction in the financial
readiness campaign brief.

**How to apply:** Approval is not completion; Booking cancellation is not a
refund; payment selection and Cash selection are not collection. Internal Wallet
entries must not imply provider/bank custody. Future provider automation may
replace the execution adapter, not these business-state distinctions.

Specialist compensation is a Vendor/Shop responsibility, not an AgendaAlly
Admin/Finance payout responsibility. The manual payout workflow is for
AgendaAlly/platform obligations to Vendors only. Do not create or reserve a
platform payable to a Specialist from assignments, booking statistics,
commissions, Wallet balances, or Specialist roles.

Any future Specialist compensation workflow must be Vendor-funded and
Vendor-controlled, based on a separately defined Shop-to-Specialist
earning/commission agreement. Admin/Finance may have oversight/audit capabilities
later, but must not be treated as the payer merely because the Specialist works
through AgendaAlly.

**Why:** The user explicitly clarified who owes and funds Specialist compensation.

**How to apply:** Keep Specialist compensation outside platform Vendor payout
eligibility and reservations; any future Shop-to-Specialist workflow must preserve
the separate funding, control and agreement boundary.

## Intentional MVP deferrals

Automatic provider-driven refunds/payouts, Specialist platform payouts,
Wallet-to-cash redemption, arbitrary partial refunds, receipt malware
certification and independent off-host receipt custody are intentionally deferred.
Do not treat them as blockers unless an original canonical readiness gate
actually requires them.

**Why:** The owner explicitly identified these as deferred capabilities, not
new prerequisites, in the bounded MVP/production-readiness audit.

**How to apply:** Preserve the original gate criteria and distinguish MVP
functional readiness from production assurance. Independent recovery-key and
off-host database-backup requirements are not the same as deferred independent
private-receipt custody.
