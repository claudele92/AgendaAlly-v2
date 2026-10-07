---
name: Selected notification boundary
description: Native account transport testing and conservative email acknowledgement recovery.
---
Selected account mail uses PHPMailer directly. Laravel's array transport or Mail::fake alone does not prevent a real SMTP connection.

**Why:** Native acceptance required proof at the actual mail boundary without activating external delivery.

**How to apply:** Bind a local PHPMailer transport when exercising SMTP mode in an isolated runtime. Keep real delivery an external dependency. Queue only selected account mail; never start the broad existing scheduler to test reminders.

An expired active-send claim is uncertain, not permission to resend. Retain UNKNOWN and visible operator evidence; a currently live claim must not be disturbed by duplicate workers.

**Why:** A process can die after SMTP accepts a message but before local acknowledgement. SMTP provides no universal idempotent retry guarantee.

**How to apply:** Separate safely retryable pre-send work from uncertain acceptance. Use the reviewed worker timeout/lease relationship and never report UNKNOWN as SENT.

Laravel scheduler commands run in child PHP processes; acceptance-only parent configuration does not automatically isolate those children.

**Why:** A normal scheduler subprocess would load the owned artisan environment rather than the disposable native configuration.

**How to apply:** Verify the sole selected event list, then use the native-only bootstrap for its child. Never use the owned artisan entry to exercise disposable scheduler fixtures.