---
name: Native isolated Laravel fixture contracts
description: Framework and tenant prerequisites must be established before interpreting isolated business-rule failures.
---

Run full booking create/read/reschedule/cancel lifecycles in a separately
committed disposable clone, not beneath one outer rollback transaction.

**Why:** Directory-only HTTP checks passed in the rollback harness, but a complete
native booking lifecycle stalled there. The same lifecycle passed in a committed
scratch clone; an outer transaction can retain scheduling locks across requests.

**How to apply:** Keep directory rollback tests separate from scheduling lifecycle
tests. Compare the protected/preview databases independently; do not broaden
runtime grants or retarget either preview to the scratch connection.

Use fresh scenario dates for retained scheduling fixtures, preserving weekday
alignment and changing every date in the scenario together.

**Why:** Reusing already committed fixture slots caused expected overlap rejection
to look like a create regression; changing only some dates also left later steps
occupied. The complete date-isolated bounded scenario passed.

**How to apply:** Prefer a fresh disposable clone or a wholly distinct scenario
range. Do not delete retained preview operations to make a test pass.

Prove fault injection actually executed before interpreting a rollback assertion.
Use a free boundary, not another occupied appointment, for adjacency expectations.

**Why:** A native booking write bypassed the fixture listener, so a legitimate
successful commit looked like failed rollback; an occupied boundary also produced
a misleading adjacency failure. Neither demonstrated a new money invariant defect.

**How to apply:** Preserve failure receipts, assert an observed injection and its
transaction phase, compare persisted state, and verify complete fixture occupancy
before attributing a failure to source scheduling or accounting.

## Native schema enumeration and snapshot keys

Explicitly scope native schema enumeration to the fixture database and use
unqualified table keys when inherited snapshots exclude lifecycle-only tables.

**Why:** Native Laravel enumeration returned qualified keys spanning schemas;
the inherited financial snapshot removed short names, leaving legitimate
order/status-note changes and producing a false money-drift failure.

**How to apply:** Verify enumeration scope and key shape before interpreting
snapshot differences. Keep the original exclusion/financial assertion intact;
do not suppress actual money changes to make a fixture pass.

## Native disposable lifecycle adapters

Use native driver SQLSTATEs and managed savepoints when adapting statement-rejection
tests to PostgreSQL. Do not require MySQL's still-usable outer transaction behavior
after a bare PG statement error. For PG empty down/up, functions and triggers on
retained legacy tables may survive dropping the new evidence tables.

**Why:** The proof adapter reapplication encountered duplicate functions and a
legacy-table trigger; these were lifecycle/fixture failures, not money failures.

**How to apply:** Test initial install and empty down/up separately. Keep adaptation
in disposable prototypes unless a permanent port is separately authorized; never
weaken financial assertions or report a temporary adapter as production support.

Bootstrap the native country/identity and translation/filesystem/response contracts in isolated Laravel tests before diagnosing business-rule failures.

Verify renderer/logger bootstrap contracts before interpreting a captured
mail callback's redacted UNKNOWN result as transport evidence.

**Why:** Minimal native fixtures lacked full-kernel facade aliases; rendering
failed locally and the deliberately redacted delivery callback classified it
as uncertain acknowledgement even though network functions were disabled.

**How to apply:** Establish local rendering/logging dependencies and prove MIME
construction independently. Never activate SMTP to diagnose an isolated
fixture's UNKNOWN result.

Nested SQLite contention can surface as Laravel `DeadlockException`, not only
`QueryException`; independently constructed connections also need their own
transaction manager when framework callbacks are exercised.

**Why:** A valid losing SQLite writer was wrapped by Laravel's nested transaction
handling, and a bare second connection lacked the callback lifecycle. Treating
those fixture conditions as domain failures gave misleading concurrency results.

**How to apply:** Establish transaction callbacks on both connections, classify
only the actual retryable lock/deadlock outcomes, and verify retained rows plus
post-commit replay. Never turn an arbitrary domain exception into a passing lock test.

Do not treat a native service's failure response as evidence that financial
writes rolled back. Separate ancillary framework/notification failure from
the financial transaction's actual persistence.

**Why:** Native Booking creation can reach nonfinancial dispatch after its
database transaction; a missing isolated framework dependency can then produce
a failure response despite completed business writes. Mocked authentication
also does not supply configuration independently consumed by permission helpers.

**How to apply:** Establish ancillary contracts without booting the real
development kernel, keep the economic calculator/writer native, and prove
rollback or completion from persisted financial snapshots rather than response
status alone.

**Why:** Native tenant helpers and Laravel validation/JSON responses resolve otherwise unrelated framework dependencies lazily; absent fixture contracts masked valid pickup rules with infrastructure exceptions. Anonymous read-only policy evaluation can likewise fail on framework contracts before reaching a real capability decision.

**How to apply:** Start with one positive controller save/read and one rejected validation case before expanding the isolated rule matrix. Reuse the established native fixtures rather than treating missing framework bindings as application regressions. For read-only policy observation, establish its minimum framework contracts without booting the native kernel; infrastructure failures are not product-readiness findings.

Isolated HTTP-router fixtures must enable FormRequest resolution/validation
callbacks explicitly when the fixture deliberately does not boot an application.
Registering a provider is not equivalent to running its boot-time callbacks.
Also establish the native role guard and database-backed translation contracts.
Keep financial action/query verification distinct from unrelated nested
User/privacy/resource serialization: establish or explicitly isolate those
presentation dependencies rather than widening the financial fixture silently.

Schema-varying isolated suites must reset schema-derived ORM metadata, not just
model boot state.

Separate native worker processes must mirror the source fixture's framework
bootstrap contracts, including facade aliases and filesystem/translation services.
A minimal accounting container is not automatically a Wallet-compatible container.
Distinguish setup/presentation rollback failures from committed financial failures.
Publish cross-process result documents atomically; an existing file can still be
partially written and must not be treated as a complete receipt.

**Why:** In native certification, missing worker bootstrap services rolled back
otherwise valid financial actions; a partially published JSON receipt interrupted
a correct reservation run. Neither is evidence about financial correctness.

**How to apply:** Read the original fixture bootstrap before adapting a worker,
retain interrupted receipts honestly, and verify completed native outcomes before
claiming either compatibility or an independent financial failure.

**Why:** Laravel's guarded-column metadata can survive a fixture database change.
A thinner earlier schema can then silently strip valid native monetary fields
in a later fixture, making individually passing tests fail only when combined.

**How to apply:** Establish fresh schema-derived metadata when fixtures change
table contracts, and verify the actual persisted fields in combined runs before
attributing missing amounts to application behavior.

**Why:** Without those callbacks, a typed FormRequest can be injected empty and
unvalidated, creating misleading mutation failures instead of exercising the
real HTTP input contract. Guard/translation lookup failures can hide that gap.

**How to apply:** Keep the native application and its environment/database
providers unbooted; enable only the required request lifecycle in the isolated
container, and prove both an accepted request and a validation denial.

A negative tenant test must include an authorized own-tenant positive control
for the same operational actor.

**Why:** A synthetic staff actor without the native role/permission grants can
return a coarse 403 before reaching the target guard, hiding a real booking
authorization gap rather than certifying tenant isolation.

**How to apply:** Establish native invitation and permission contracts, prove
own-tenant access, then deny the foreign target. Distinguish a read-only shared
authorization-check reproduction from a harmful mutation that was not run.

Negative financial tests also require a currently valid scheduling context.

**Why:** New scheduling eligibility can reject an incomplete assignment or
calendar before the intended financial guard, making an expected failure pass
for the wrong reason. Sharing an occupied specialist across synthetic Shops
can likewise disguise fixture invalidity as a money or authorization defect.

**How to apply:** Establish valid scheduling/ownership positive controls before
interpreting financial denials. Confirm the intended guard was reached, rather
than treating a generic failure result or test count as financial assurance.

Establish the native facade aliases in isolated legacy-service fixtures.

**Why:** A missing global DB alias can be caught inside a service and return a
financial-looking failure even though the intended monetary guard never ran.

**How to apply:** Register Laravel's ordinary alias contract in the isolated test
bootstrap without booting the normal application's database or transports. Keep
the monetary assertions intact and distinguish fixture failure from a valid P0.

Share cross-engine setup through an abstract fixture, not by inheriting a
concrete SQLite test class into a native MySQL test class.

**Why:** PHPUnit discovers inherited public tests too. Cross-engine test
inheritance duplicates engine-specific fault injections into a suite where
their SQL is invalid, rather than sharing setup alone.

**How to apply:** Retain every SQLite case on SQLite and every purpose-built
native scenario on its intended engine; move only unchanged setup/helper bodies.
The native scenarios still require their explicitly owned listener; separating
fixtures never removes that prerequisite. Do not hide missing engines by
silently skipping or weakening the financial assertions.

Give disposable native database schemas independent run ownership. Forked
business-race workers share their parent test's schema, but separate validation
runs must never pre-clean the same schema.

**Why:** A native subset passed alone while a full overlapping run failed on
DDL deadlocks and disappearing tables, before reaching money assertions.
Shared destructive setup was not isolation.

**How to apply:** Create a uniquely named, new schema for each owner, fail rather
than adopt an existing schema, and restrict cleanup to that owner's validated
namespace. Keep all monetary concurrency/rollback assertions unchanged.