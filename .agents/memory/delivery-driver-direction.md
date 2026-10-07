---
name: Delivery Driver product direction
description: Creator-defined Vendor delivery model and approval boundary for future onboarding and mobile work.
---

A Vendor should manage its own authorized Delivery Drivers and assign its own
product orders to them. Drivers should see only deliveries they are authorized
to handle, with authorized status updates visible to Vendor and Customer.
Vendor Staff access must follow existing Vendor permissions; intentionally
authorized Admin/Superadmin access may be broader.

**Why:** This is the creator's stated AgendaAlly business model, not an inference
from legacy global driver roles or platform dispatch behavior.

**How to apply:** Enforce relationships and order/driver ownership on the server,
not only in pickers. Recommend reuse of native identity/models/API contracts
instead of a competing subsystem. Use “Delivery Driver” in recommendations and
new UI while retaining exact legacy identifiers in compatibility documentation.

Prefer hybrid invitations: an existing account signs in and accepts, or a new
user registers and accepts the Vendor relationship. The Vendor should not need
to know or create the driver's password.

**Why:** The creator explicitly prefers this onboarding model unless existing
architecture gives a strong reason otherwise.

**How to apply:** Architecture must be reviewed and approved before implementing
invites, roles, relationships, migrations or assignment changes. Do not treat
the audit or its proposed phases as implementation approval.

The existing Delivery mobile application will be uploaded/refactored later.

**Why:** Its source is not yet available for actual compatibility verification.

**How to apply:** Preserve `/api/v1` compatibility where practical, document the
needed contract, and audit the actual app after upload. Do not build an invented
replacement or combine delivery operations with unapproved earnings/COD changes.

The platform will eventually include Customer, Vendor and Driver applications
and POS desktop/tablet capability.

**Why:** The owner explicitly stated these future client boundaries.

**How to apply:** Account for independent clients and contract/release boundaries
in repository recommendations; this direction is not authorization to create
the future applications.