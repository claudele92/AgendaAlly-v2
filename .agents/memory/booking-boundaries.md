---
name: Booking boundaries
description: Creator-confirmed appointment exclusion, working-hour and processing-time rules.
---

Apply half-open [start,end) exclusion per specialist/resource. The stored end
already includes service duration plus processing/pause time, so the next booking
may start exactly at the stored end with no additional gap. Availability and
booking creation/rescheduling must enforce the same rule. Reject any actual
overlap and any appointment extending outside that specialist's working hours.
Different specialists must remain independently bookable.

**Why:** The creator explicitly selected this rule after contradictory native
calculation/availability behavior was demonstrated.

**How to apply:** Preserve this boundary in admission, availability, rescheduling
and their acceptance checks; do not add another processing buffer.

## Creator-confirmed recurrence rules

Skip a month when the original monthly day-of-month anchor does not exist.
Do not clamp it to month end or change the anchor; a 31st rule resumes on the
31st of the next eligible month.

Count all scheduled calendar occurrences from the original recurrence rule,
including the first, regardless of working hours, closed dates, or the requested
availability window. Working-hour and closed-date rules determine whether an
occurrence blocks bookable capacity; they must not change or reset its count.

**Why:** The creator explicitly confirmed both rules after native recurrence
origin/range omission was reproduced.

**How to apply:** Generate/count calendar occurrences before applying working-hour
or closed-date capacity filters. Keep monthly stepping anchored to the original
day, including after skipped months, and never restart counts at the query window.