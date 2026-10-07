---
name: Isolated staging key custody
description: Why local staging uses managed key authority and why that is not a production disaster-recovery strategy.
---

The local AgendaAlly staging runtime uses domain-separated derivation from the existing managed session authority for its stable application key, database credential and backup encryption. Do not borrow imported legacy credentials or expose derived values.

**Why:** The approved work needed an isolated production-mode runtime and same-key restore without an approved external staging host or independent key-custody service. This avoids changing protected development configuration.

**How to apply:** Preserve that authority across local release switches and restores. Rotation or loss of the managed authority makes the old encrypted staging records/backups unreadable. Independent custody/escrow and an approved off-host retention destination must be established before claiming production disaster recovery; this local bootstrap choice is not a production key-management design.

The owner approved the independent backup/key-custody **design and proposed
targets only**, not operational access or implementation. Preserve separate
encrypted-backup/key custody, historical recoverability and least-privilege/
two-person controls. Recovery targets remain unmeasured.

**Why:** The owner explicitly withheld authorization for destinations/accounts,
credentials/secrets, normal-data transfer, key generation/escrow, VPS access,
restore execution, provider/SMTP/worker activation and production changes.

**How to apply:** Use the approval conditions in
`docs/deployment/mysql-independent-custody-recovery-contract.md` as the scope
boundary. Obtain separate owner approval for actual destinations, credentials,
schedules, retention implementation, monitoring and recovery execution; never
treat approval of the design as permission to implement it.