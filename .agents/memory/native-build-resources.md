---
name: Native build resource isolation
description: Avoid overlapping native production compilation and browser capture on the limited-memory development workspace.
---

After a workspace memory interruption, check for surviving owned processes
before restarting workflows marked not started.

**Why:** Workflow tracking reset while native PHP, nginx and MySQL processes
survived. Blind restarts produced port/socket conflicts and service restart
loops, not application failures.

**How to apply:** Identify exact owned listeners and parent processes. Gracefully
stop only the orphaned service, preserving database files, then restart its
configured workflow. Do not kill shared browser/recorder processes or create
replacement workflows.

MySQL stale socket/PID metadata can refer to a PID reused by a Node worker
thread, which may be absent from ordinary process listings but still pass a
PID liveness check.

**Why:** After a workspace restart, MySQL refused its socket because its former
PID now belonged to a live V8 worker rather than any database server.

**How to apply:** Stop the owned staging supervisor and confirm there is no
owned MySQL listener/process. Inspect the recorded PID's executable/thread
identity before acting. Never terminate the unrelated worker. Remove only
verified stale socket/lock/PID metadata, not database files, then restart the
configured supervisor once.

Run large native production compilations separately from browser captures.
Pause unused preview services during compilation and restore them afterward.

A previously passing build heap is not a guarantee after editor workers warm
up. Leave headroom for the language server and native services; pause unused
managed previews and reduce the bounded build heap before retrying an
otherwise successful compilation interrupted during chunk-size reporting.

**Why:** Background editor memory grew between builds, and the same heap that
had passed no longer fit the remaining workspace budget.

**How to apply:** Inspect actual memory and owned services, not just the
configured heap. Do not kill editor, browser or recorder workers to make room.

Also separate full native TypeScript checking from browser capture and pause
the customer preview during business-only browser verification.

**Why:** With several native previews running, observed memory use approached
the workspace limit. A production compilation timed out; another compilation
overlapping browser captures was interrupted by a workspace restart. The
exact restart cause was not established, so do not describe it as a confirmed
application crash or successful build.
Browser recording, desktop and editor processes also consume substantial
memory, so an unused development server reduces capture headroom. Forced
worker/manager termination can leave application children outside managed
workflow state; a stopped status alone does not prove ports are free.

**How to apply:** Finish compilation before screenshot/testing work; avoid
starting unrelated scaffold services. Restore required previews sequentially.
Treat interrupted builds as unverified rather than relying on older build
results for changed sources.
Keep disposable acceptance database state and its restart configuration in the
workspace rather than only in volatile /tmp storage.

**Why:** Compute recovery lost a temporary native database/runtime while
earlier acceptance receipts remained. A lost disposable runtime is a fixture
recovery problem, not evidence that the application lost protected data.

**How to apply:** Reconnect to persisted isolated state where available; if
it must be rebuilt, explicitly distinguish new fixtures from retained receipts
and never substitute the owned SQLite database for native financial acceptance.
Keep only the native services needed for the current browser journey active.
After a lost workflow manager/worker, check for application-owned orphan
processes before restarting; do not terminate shared recorder/editor services.

Treat native preview availability and automation-tool availability as separate
checks after compute recovery.

**Why:** The native HTTPS services can recover while the tool worker needed
to resume browser verification remains disconnected. HTTP success alone
does not establish that an interrupted browser acceptance run can continue.

**How to apply:** Check tool readiness before resuming the existing tester.
If the tool worker cannot reconnect through supported capabilities, preserve
the acceptance gaps and report the infrastructure blocker; do not substitute
API login, injected authentication or HTTP-only evidence for browser checks.

Keep long native browse sessions memory-bounded, not only production builds.
Use viewport-only captures and avoid warming every route in one browser pass.

**Why:** During a native multi-page run, dev webpack retained over 4 GB while
the recorder/display each retained roughly 2 GB and available memory reached
zero. Tool/notebook state was lost; shared recording state could not be
addressed from the fresh notebook. This is evidence of memory pressure, not
proof of a source-code crash.

**How to apply:** Use the native development launcher's bounded heap, isolate
customer and Business capture sessions, and request short focused follow-ups.
Do not kill shared recorder/editor processes to force tests through. If old
contexts are unaddressable, report the gap rather than inventing successful
cleanup. Production startup remains outside the development heap policy.

Pause unused native acceptance PHP/MySQL services as well as frontend previews
before a large Admin production build; pausing only frontends can be insufficient.

**Why:** Full Admin compilation exhausted remaining memory with unused acceptance
services still resident. Workflow tracking and the shared tool worker restarted
while owned servers survived; a resource-isolated build completed afterward.

**How to apply:** Keep the staging database/runtime needed for verification alive,
pause only unused owned workflows, and restore their prior running state afterward.
Do not kill platform editor/browser workers. Treat lost workflow tracking as an
orphan-recovery problem, not permission to start duplicate listeners.

Use a fully sanitized offline build snapshot rather than overriding selected
public variables over the native development dotenv configuration.

**Why:** Retained deprecated URL settings can conflict with modern settings, and
production guards correctly reject development mode. Successful module
transformation also does not prove bundling succeeds within a reduced heap.

**How to apply:** Reuse the owned build fixture's environment isolation from the
first attempt, preserve the guards, and report the actual completed build stage
instead of treating guard or heap failures as production-code acceptance.

Restore the disposable native MySQL listener before running the complete
hardening suite after build isolation.

**Why:** Leaving that intentionally paused listener off produced connection
errors in native regression tests, not application failures. Those errors must
be resolved by restoring the prerequisite, never by skipping the tests.

**How to apply:** Retain successful build evidence, restore the test listener,
then run the unchanged required validation. Restore previews to their previous
running state without trying to repair unrelated pre-existing failed workflows.