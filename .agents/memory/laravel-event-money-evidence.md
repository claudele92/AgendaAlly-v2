---
name: Laravel event and audit evidence
description: Model events and audit records may expose original rather than newly persisted values.
---

Do not assume `getRawOriginal()` contains the newly persisted price inside an
Eloquent `created` event; the original snapshot may not yet be synchronized.
In an `updated` event it can still represent the previous value.

**Why:** Native Cash confirmation rejected a valid paid creation because its
created-event model exposed an empty original-price snapshot even though the
transaction row had already been inserted.

**How to apply:** Financial confirmation invoked from model events should read
the persisted monetary evidence inside the owning SQL transaction. Keep this
separate from quote construction after a completed save, and do not substitute
a stale caller model for persisted receipt evidence.

Native updated audit data can deliberately retain the previous original values
of changed fields. A stored audit value differing from the current database
value is not automatically evidence of a failed update.

**Why:** Reset-state verification initially compared an updated audit hash as
if it were the new password hash; the native audit semantics instead make it
prior-state evidence.

**How to apply:** Establish the audit's before/after semantics before claiming a
transition. Use persisted state and opaque fingerprint comparisons; never
expose password values, reset codes or authorization material.

## Fault injection after financial execution-order changes

Keep zero-affected-row debit failure and post-debit Wallet disappearance as
separate rollback obligations. A passing debit guard does not justify dropping
an existing disappearance/rollback assertion.

**Why:** Reordering debit before history creation changed what an existing
history-event fault simulated. The zero-row guard worked, but the later Wallet
disappearance still committed an accepted payout. The user approved repairing
that integrity failure while preserving the original assertion.

**How to apply:** Reproduce both scenarios before classifying a financial test
as stale. Preserve the existing fault coverage and add the correctly timed
arithmetic fault separately; revalidate original settlement identities after
callbacks, inside the owning transaction.