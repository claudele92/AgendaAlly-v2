---
name: Native invoice printing
description: DomPDF page-frame behavior that can silently override declared print margins.
---

Normalize the invoice body, but do not reset the root HTML margin to zero.
Verify actual generated PDF boundaries rather than assuming CSS @page is honored.

**Why:** DomPDF merged the root HTML reset with its page frame. An invoice
declaring reasonable @page margins still placed the wordmark at x=0 and clipped
its right-side invoice number. Removing the HTML reset restored the intended
physical margins; browser HTML alone would not reveal this.

**How to apply:** For native PDF presentation changes, inspect an actual PDF
page and its bounding boxes, use readable point-based detail typography, and
keep authoritative financial getters/currency/statuses and delivery unchanged.

Keep per-document DomPDF options and rendering on the same returned wrapper.
Allow embedded raster data only for validated local document logos; do not
enable global remote fetching to make a logo appear.

**Why:** The native DomPDF facade is not cached: separate facade calls for
options and loading produced different wrappers. Its configured protocol list
also excluded data URIs, so an otherwise valid PDF silently showed image alt
text instead of the approved transparent PNG. Finally applying old ignored
DPI settings changed the whole invoice layout rather than only its branding.

**How to apply:** Preserve effective existing renderer defaults for a
branding-only change, keep one wrapper, restrict embedded data to validated
logos, and inspect an actual PDF image rather than treating a successful
download or correct data URI as proof of rendered branding.