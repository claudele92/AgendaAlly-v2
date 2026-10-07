---
name: Flutter validation on Nix
description: Isolated supported Flutter validation can need ELF interpreter adaptation; keep native acceptance separate.
---

Use an isolated compatible Flutter SDK/pub cache outside the workspace rather than changing root modules to validate the imported Customer application. Downloaded engine Dart executables can require a Nix-compatible ELF interpreter, including both Dart and its AOT runtime.

**Why:** The standard Linux interpreter expected by the downloaded SDK was absent on this Nix host. Supported Flutter/Dart tests and analysis succeeded after temporary SDK adaptation without root modernization.

**How to apply:** Verify actual tool versions and the exact locked application graph. Keep tool-bootstrap dependency changes separate from app dependency changes. Passing Linux Flutter tests is not Android Keystore/iOS Keychain or native build acceptance.

Native SDK provisioning must account for the temporary filesystem's write quota, not only the free capacity shown by `df`. Moving an incomplete Android SDK can leave installer metadata referring to the old absolute staging directory.

**Why:** Android NDK staging exhausted the temporary write quota despite substantial advertised free space. Retrying after moving the SDK recreated the old staging directory until the failed installation state was discarded.

**How to apply:** Keep large native tool downloads, unpacking and host-JVM temporary files in a suitably sized isolated cache. Inspect failed installer staging ownership before retrying; clean only the campaign's incomplete installation, never application data or accepted evidence.

Do not accept Flutter/wrapper exit zero as an Android build PASS after cancellation or a deadline.

**Why:** A bounded build returned overall timeout 124 and logged Gradle cancellation 143, while Flutter/the wrapper reported zero and produced no APK.

**How to apply:** Require an uncancelled successful task and the expected APK, and preserve overall-command and child/task outcomes separately. A host timeout without a source diagnostic is not FAILED-SOURCE.
