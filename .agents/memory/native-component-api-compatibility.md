---
name: Native component API compatibility
description: Runtime compatibility of native UI library APIs versus newer prototype conventions.
---

Match component props to the installed native UI library version rather than copying newer prototype APIs.

**Why:** The native Ant Design 4.20 modal silently ignored the newer `open` prop. JSX parsing passed, but the Add new client button could not display its form. This required actual interaction verification to detect.

**How to apply:** Verify the installed component API before integrating dialogs and form controls. Preserve the existing dependency version unless an upgrade is explicitly authorized; use its supported visibility prop and ensure imperative form setters target a mounted, connected form.