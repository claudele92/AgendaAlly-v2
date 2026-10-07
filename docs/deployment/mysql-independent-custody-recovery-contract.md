# Independent backup and key custody recovery contract

Status: **OWNER-APPROVED DESIGN AND PROPOSED TARGETS ONLY — execution NOT authorized.**

This is a design contract for AgendaAlly MySQL/database, private files and
configuration recovery after complete loss of the application machine and its
local disks. It is not an implemented backup service or disaster-recovery
certification. No external destination, VPS, credential, secret, normal data,
provider or activation is contacted or provisioned by this document.

### Design/approval deliverable versus operational acceptance

The assigned work explicitly defines its completion boundary as: “Done means an
owner-reviewable custody/recovery contract, not another claim based on local file
copies.” It requires planning and obtaining owner approval, while excluding
external provisioning/access without separate explicit approval.

The design/approval deliverable is satisfied by this contract and the explicit
owner approval in §8: it covers encrypted database/files/config custody, separate
historical-key escrow, retention/rotation, restore identity privileges, independent
access recovery and proposed RPO/RTO. No implementation or host-loss drill is
claimed. Operational acceptance remains blocked by §8's execution decisions and
must be pursued only as separately authorized work. Completion of this bounded
design work must not be interpreted as backups/keys already surviving host loss.

## 1. Authority and existing evidence

Read with:

- [Bootstrap specification §§5–6](mysql-bootstrap-recovery-specification.md#5-independent-admin-and-key-custody).
- [Synthetic upgrade/recovery](mysql-synthetic-upgrade-recovery.md), especially
  its same-host exclusions and backup loader qualification.
- [Temporal recovery](mysql-synthetic-temporal-recovery.md): an older database
  point cannot establish the outcome of newer external effects.

The local synthetic recovery measured 54.35 seconds and zero lost writes at its
selected synthetic point. Neither is a production RPO/RTO promise. Separate
directories on this host, source control, a source checkout, and keys derived
only from local session authority do not satisfy independent custody.

Owner approval of this contract accepts design requirements and proposed targets
only. It does not authorize choosing/provisioning accounts, retrieving credentials,
generating/copying keys, transferring data, testing real recipients/providers,
deploying, or restoring into any real installation. Each execution needs a
separate approved scope, identities, exact targets, data class and rollback/stop
conditions. First-install reference/demo data and initial administrator/grants
remain separate work; this contract creates no administrator or default password.

## 2. Required independent custody topology

| Component | Proposed contract | Loss/security boundary |
| --- | --- | --- |
| Backup destination A | Off-host encrypted database/files/config generations; versioned, deletion-protected immutable retention | Independent of application machine; writer cannot read, overwrite, shorten retention or delete existing generations |
| Backup destination B | Second independently retained encrypted copy of every accepted generation | Separate failure domain and administrative account from A; loss/lockout of A cannot remove access to B |
| Key escrow K1 | Independently accessible secured vault holding versioned decryption/application/authority material | Not either backup account, not application-host-only, not dependent on restored app login |
| Key escrow K2 | Separately controlled sealed recovery copy of the same required material and recovery instructions | Independent of K1's account/device and available after loss of host and primary operator access |
| Recovery records | Protected manifest, release/integrity receipts, custody receipts and access runbook retained off-host | Non-secret identifiers only in repository; full records remain private and independently accessible |

A provider name, region, account or destination has **not** been selected.
Independent accounts alone do not prove independent failure domains: execution
approval must document underlying region/provider, billing, identity-provider,
email/MFA recovery and administrator dependencies. K1/K2 cannot both depend on a
device, mailbox, session secret or account recoverable only from the lost host.
No custom key-splitting scheme is prescribed; use an approved established escrow
mechanism with documented two-person release controls.

## 3. Recovery generation and data inventory

Every accepted generation has a unique identifier and UTC recovery-point time.
Retain one bound manifest covering:

- Consistent MySQL logical snapshot, exact engine/session settings, source release,
  complete migration ledger and migration hashes, raw schema/trigger/definer
  definitions, row fingerprints with original ordering/serialization recipe,
  financial invariant totals and source consistency boundaries.
- All database-referenced private receipts/attachments and required public/media
  assets; file paths, sizes and cryptographic hashes, ownership/access policy,
  including required storage outside the application host.
- Required runtime configuration, selected transport-disable policy, compatible
  source/dependency lockfiles and recovery tools. Redact public reports; encrypted
  configuration may contain sensitive settings and is treated as private data.
- Key-version references for backup encryption, Laravel application encryption,
  HMAC/selected authority and private-storage encryption, where actually used.
  Preserve original key associations; the database dump is not key escrow.
- Signed/authenticated integrity manifest and transfer acknowledgements for A/B.
  Verification authority and its historical versions must also survive host loss.
  A checksum alone detects damage but does not establish trusted provenance.

Capture only approved data classes. A later inventory must locate actual file
stores, application secrets and authority dependencies without publishing values.
Do not silently omit assets or include incidental local caches, credentials,
session files or unrelated projects. Preserve backed-up authentication records
as evidence, then explicitly invalidate restored authentication after equivalence.

For a consistent database/files/config point, quiesce approved writers, DDL and
file mutators, drain only approved local work, and capture a consistent InnoDB
snapshot plus matching file/config versions. Do not dispatch external work to
drain a queue. Mixed-engine, changing file sets or unmatched key versions STOP
acceptance; the later implementation must prove its consistency method.

Encrypt before transfer using an established authenticated-encryption backup
format/tool and approved algorithm/version; transport encryption alone is
insufficient. Use per-generation data keys wrapped by versioned escrow-held
backup keys. Persist non-secret algorithm/tool/key identifiers and authenticated
manifest bindings. The actual product, parameters and native loader commands
require implementation review before execution, not ad hoc cryptography.

## 4. Proposed cadence, retention and rotation

These are owner-reviewable defaults, not observed capabilities:

| Policy | Proposed default |
| --- | --- |
| Capture and independent custody | Every 12 hours; a generation counts as protected only once both A and B have verified complete ciphertext/manifest receipts |
| Operational retention | Every 12-hour generation for 35 days at A and B |
| Longer retention | One successful month-end generation for 12 months at A and B |
| Pre-change points | Before approved schema/key/config changes; retain at least 35 days and until the change is accepted and hold cleared |
| Escrow retention | Retain every needed historical key/version until all dependent backups, retained encrypted records and evidence have expired with approved destruction |
| Verification | Transfer/integrity receipt every generation; monthly isolated decrypt/restore sample; quarterly host-loss and access-recovery exercise |
| Rotation review | Review backup wrapping keys and access grants every 90 days; rotate access credentials after suspected compromise/personnel changes using approved secure channels |

Select immutable retention before writing; test that the writer cannot shorten it.
Lifecycle deletion requires retention expiry, absence of legal/evidence/incident
holds, intact newer recovery generations and two-person authorization. Apply holds
to both stores and dependent escrow; never destroy an unresolved-outcome recovery
point merely to meet storage limits. Document legally required geography,
privacy/deletion requirements, capacity and cost before execution; a conflicting
requirement blocks deployment rather than being silently overridden.

Backup-key rotation uses a new version for new generations; old keys remain
recoverable. Rewrapping old data keys, if needed, is separately approved and
verified before removing the previous wrapper. Application/HMAC authority rotation
is **not** routine backup rotation: it needs compatibility, re-encryption and
challenge-lifetime review. Never regenerate an application key during restore.
Suspected compromise triggers containment and incident review; do not delete the
only working key or evidence. Escrow-copy completeness must be proved after every
approved key change.

## 5. Roles, privileges and access recovery

Execution approval must nominate real people through private records for:
accountable owner, backup operator, escrow custodians, recovery operator, security
reviewer and substitute operators. At least two authorized people must be able
to recover access if the primary operator and their devices are unavailable.
Owner authorization plus a separate custodian is required for key release and
destructive retention changes; no operator may approve their own exception.

| Identity | Permitted authority | Explicit exclusion |
| --- | --- | --- |
| Application runtime | Existing scoped schema CRUD only | No backup account, escrow, account-management or global database privileges |
| Backup DB reader | Exact selected-schema read/snapshot metadata privileges and only reviewed native dump requirements | No writes, grants or arbitrary schema reads; qualify actual dump output as well as capture commands |
| Backup transfer writer | Create new generation objects/manifests in A/B only | No history read/decrypt, overwrite/delete or retention-policy changes |
| Retention administrator | Scoped hold/lifecycle management under two-person approval | No automatic key release; not a runtime or writer credential |
| Restore loader | Time-limited approved DDL/DML/import rights on a new isolated schema/instance | No existing normal schema overwrite, global broadening or automatic migration replay |
| Escrow custodians | Approved key release to isolated recovery environment with recorded purpose | No unattended application access, Git/chat/log disclosure or unrelated data access |
| Recovery verifier | Scoped read-only DB/files/integrity checks | No financial handlers, resend, worker activation or approval-grant creation |

The restore loader's exact grant set must be tested against the emitted dump
commands. Default import locks, DEFINER handling and binary-log trigger restrictions
can exceed apparently sufficient grants. The previously reviewed temporary
bootstrap-SUPER/locked-trigger-definer approach is a local rehearsal precedent,
not blanket production approval. DBA approval must name exact elevated operations,
duration, isolated scope, revocation and retained locked-definer table grants.
Never weaken financial triggers/trusted-creator policy to make restore succeed.

Store access recovery instructions, account ownership/billing continuity,
independent MFA recovery mechanisms and recovery codes through approved secure
custody, never here. Prove access from a clean authorized device without this
machine, its environment, active sessions or primary operator. Escrow release is
logged with non-secret key version, approvers, target, timestamp and revocation
receipt; credentials and key bytes are never logged. Expire temporary access,
remove working key copies securely and retain the valid escrow originals.
Application administrator recovery and grants remain separately approved.

## 6. RPO/RTO and alert contract

- **RPO target: at most 24 hours** of database/files/config changes lost after
  machine loss. Measure from incident time to the latest complete, consistent,
  independently verified recoverable generation at both destinations, not local
  dump start or upload success alone.
- **RTO to verified isolated quarantine: at most 8 hours** from incident
  declaration. Includes clean target availability, access/escrow release, download,
  decrypt/import, file/config recovery, integrity checks and stale-auth denial.
- **Full service restoration target: 24 hours**, conditional on owner-approved
  reconciliation, security checks and separately authorized traffic/transport
  release. Measure full elapsed time including approval delays; never report the
  quarantine time as full-service recovery. An unresolved gate means the target
  is missed and service remains held.
- Financial/SMTP external outcomes are **not** guaranteed by the 24-hour data
  RPO. Missing or newer outcome evidence requires HOLD, not retry. Independent
  outcome retention and reconciliation remain separate existing work.

Proposed alerting: any failed/missing scheduled generation, escrow mismatch or
integrity failure alerts owner and substitute; no silent last-known-good success.
Warn when the latest protected point is 18 hours old; critical at 24 hours.
Maintain an independently monitored expected-generation heartbeat so host loss
or a broken local notifier is visible. A/B divergence is not a successful run.
Respond by preserving working generations, investigating and explicitly reporting
the RPO breach; never contact a destination/provider without approved access.

Production data size, transfer rates, costs, staffing and clean-target provisioning
time are unknown. Size and time a separately approved isolated drill before
promising these targets. Report actual measured RPO, quarantine RTO and full
service RTO separately, including waiting and failures.

## 7. Host-loss recovery gates

1. Declare incident; record owner authorization, scope and incident clock.
   Assume the host unavailable/untrusted. Fence the old deployment and prevent
   duplicate writers; retain evidence. A new target requires separate approval.
2. Recover A or B access and escrow using independent identities/devices. If a
   destination is unavailable, record degradation and verify the selected surviving
   generation rather than substituting an unverified local copy.
3. Verify trusted manifest, complete artifacts/key associations and selected
   point; retain original encrypted generation untouched. Stop on missing files,
   wrong key, tampering, engine mismatch or inconsistent point.
4. On a clean isolated target pin reviewed MySQL/source/ledger versions.
   Deny network egress to providers/SMTP and disable transports, event scheduler,
   workers, cron and callback ingress **before loading keys or booting the app**.
   Database read-only mode alone cannot prevent external sends.
5. Import under reviewed temporary loader privileges; recover files/config/keys.
   On partial failure preserve target and logs, stop, obtain approval for another
   new empty target. Do not repair/replay or overwrite an existing database.
6. Prove raw schema/row/ledger/invariant/file/grant equivalence with original
   fingerprint recipes and narrow documented native DDL equivalence. Revoke
   elevated loader rights; verify runtime CRUD and locked-definer restrictions.
7. Only then apply approved session/token/remember/reset/verification challenge
   invalidation and prove stale-authority denial. Preserve passwords, grants,
   encrypted evidence, money/reservations and PENDING/UNKNOWN lifecycle records.
8. Record a passive hold inventory and hand off to separately approved external
   outcome reconciliation. Key recovery/DB equivalence never authorizes money
   movement, email replay or traffic release.
9. Record timings, failed gates, access logs and private proof; owner accepts
   quarantine result. Full activation requires its own approved decision and
   fencing proof. Keep recovery backups and failed targets under custody.

## 8. Acceptance and authorization record

**Design decision:** the owner explicitly selected “Approve the design and proposed
targets” in response to the review question “Do you approve the proposed backup
and key-custody design?” The owner's conditions are retained below verbatim.
No operational approval is inferred from task assignment, earlier local rehearsal
approval, this design decision, or this document's existence.

Accepted design defaults: A/B + K1/K2 separation;
two-person recovery authority; 12-hour capture, 35-day/12-month retention;
historical-key preservation and rotation rules; restricted restore privileges;
24-hour RPO, 8-hour quarantine RTO and conditional 24-hour service target.
These are design defaults only; actual schedules and retention implementation
still need separate approval.

### Owner's approval conditions

> Approved as the owner-reviewed backup and key-custody design and proposed recovery targets only. This approval does not authorize creation of backup destinations or accounts, access to credentials or secrets, transfer of normal/production data, key generation or escrow, VPS access, restore execution, provider/SMTP/worker activation, or production changes. Preserve separation between encrypted backup custody and key custody, retain historical keys required to recover retained backup generations, and maintain least-privilege/two-person recovery controls. Treat the proposed RPO/RTO values as unmeasured targets until independently tested against the eventual production/off-host architecture. Actual destinations, credentials, schedules, retention implementation, monitoring and recovery execution require separate owner approval.

### Outstanding execution decisions

Before execution the owner must separately approve and privately record:

| Decision | Current state |
| --- | --- |
| Exact A/B destinations, geography, immutability, independent accounts and costs | Unselected; blocked |
| K1/K2 mechanism, custody/access dependency map and named substitutes | Unselected; blocked |
| Data/file/config inventory, classification, retention/privacy/hold compatibility | Unverified; blocked |
| Backup/encryption tooling and complete native dump/import grant allowlist | Unqualified; blocked |
| Real access recovery, MFA, billing continuity and escrow release procedure | Untested; blocked |
| Exact clean restore target, resources, data class and network quarantine | Unapproved; blocked |
| Transfer/restore permissions, proof location and drill incident window | Unapproved; blocked |
| Actual schedules, retention implementation and monitoring deployment | Unapproved; blocked |
| Measured targets and monitored off-host heartbeat | Unmeasured/unimplemented |
| Reconciliation and traffic/transport activation | Separate gates; not authorized |

Disaster-recovery acceptance requires evidence that an approved clean operator
can recover a complete approved generation **without any source-host files or
sessions**, and when the primary backup or escrow access path is unavailable.
Prove corruption/wrong-key failure, deny deletion by writer, verify retention and
historical-key recovery, native loader/revocation, restored auth denial and no
external effects. Retain independently accessible signed proof and owner
acceptance. Until then: **design only; production/off-host recovery NOT certified**.
