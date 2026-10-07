---
name: Native UI fixture verification
description: Avoid false failures when testing native Vendor UI with controlled network responses.
---

Synthetic responses must satisfy every consumer of a shared native endpoint,
not just the component being checked. A request-match counter is not proof that
the browser received the intended completed response or current synthetic state.

**Why:** Overlapping handlers and incomplete shared-endpoint fixtures made a
correct Driver assignment appear stale and generated unrelated client errors.
Correcting only the fixtures resolved those observations.

**How to apply:** Before diagnosing application code from a synthetic UI failure,
record the completed response's URL, status and relevant body/state. Keep
authoritative fixture routing unambiguous and query-independent; verify response
contracts across shared consumers. Do not bypass native authorization to make a
fixture render, or confuse mocked UI success with real database persistence.

Exercise saved-record reads separately from creation quotes; optional metadata
must be tested as absent as well as present.

**Why:** Native creation quotes supplied form metadata that saved booking
resources legitimately omitted. Quote-based checks concealed a fatal read-only
detail consumer failure until actual persisted appointments were opened.

**How to apply:** Include no-form persisted records in detail coverage. Do not
recalculate a saved appointment merely to manufacture a convenient display DTO
or silently replace its frozen economics with a new quote.

No observed geography GET is not proof that catalogue data or API configuration
is missing. Check query activation and actual option visibility separately.

**Why:** A mobile arrow changed HeadlessUI's expanded state without opening the
drawer that enabled loading, while the full-width field worked. An exact CSS
breakpoint mismatch also fetched healthy data without displaying options.
The same-origin normal catalogue was populated; reseeding or changing CORS
would have addressed neither defect.

**How to apply:** Compare all visible launch controls and exact responsive
boundaries on the supported origin. Record completed responses and visible
options before attributing a missing request to environment/data.

The tester's default SQL connection may be the unrelated scaffold PostgreSQL
database, not the native application's owned SQLite database.

**Why:** A native signup safety check reported no users relation despite the
normal application having populated users. The test connector was inspecting
a different database; changing native storage would have been the wrong fix.

**How to apply:** Keep native provenance unchanged. Let the main agent perform
read-only PDO checks against the owned SQLite file and supply those facts to
the browser tester when its SQL connector cannot reach that source.

Browser request interception does not cover Next server-layout fetches. A
financial component can hydrate successfully from completed native fixture
responses while unrelated server-rendered bootstrap reads still fail.

**Why:** Isolated financial browser acceptance with the normal backend
deliberately off retained successful three-role financial traces alongside
server-layout connection errors and unavailable navigation scope.

**How to apply:** Keep browser and server bootstrap evidence separate. Qualify
the tested component/identity boundary explicitly; do not claim whole-preview
health or full authenticated acceptance from fixture success, and do not launch
a normal financial backend merely to hide isolated-bootstrap failures.

For native file-upload acceptance, prefer the frontend's actual same-origin
proxy to the isolated backend over intercepting and rebuilding multipart bodies.

**Why:** Browser interception exposed multipart boundaries and filenames but
omitted the selected file bytes. Re-forwarding that body caused native MIME
validation failures; normal browser transport through the existing proxy
successfully retained the same PDF/PNG/JPEG files and matching hashes.

**How to apply:** Treat an empty intercepted file payload as a harness problem
until proven otherwise. Preserve the original file input interaction and native
authorization; temporarily direct the existing proxy to an owned disposable
backend, then restore its configuration. Never inject attachments directly to
claim a successful browser upload.