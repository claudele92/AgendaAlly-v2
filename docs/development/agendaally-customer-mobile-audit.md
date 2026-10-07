# AgendaAlly Customer Mobile — existing application audit

**Date:** 2026-10-06  
**Mode:** READ-ONLY architecture, feature-parity, API and MVP-readiness audit.  
**Authority:** `attached_assets/Pasted-AgendaAlly-Customer-Mobile-App-Existing-Application-Arc_1791319310598.txt`.  
**Evidence:** [`evidence/customer-mobile-audit/README.md`](evidence/customer-mobile-audit/README.md).

Throughout this report, **M** means `.migration-backup/customer_app`, **B** means `.migration-backup/backend`, and **W** means `.migration-backup/web`. Paths using these prefixes refer to actual source, not proposed files.

**Evidence levels:** SOURCE-CONFIRMED means observed implementation/configuration; STATICALLY VERIFIED means a deterministic inspection/check completed; BUILD-VERIFIED requires a successful native build; RUNTIME-VERIFIED requires execution of the customer flow. No mobile native build or customer runtime flow passed this audit. Source presence is not acceptance.

## 1. Executive summary

**Recommendation: B. REFACTOR IN PLACE.**

The intended Customer app is an existing Flutter application, not an Expo/React Native project. It has useful separation between presentation, BLoC state, interfaces, repositories and models. Its service discovery, Specialist selection, availability, booking, profile, Wallet/history and common UI are substantial reusable implementations. A whole-app rewrite is not justified.

It is nevertheless **not currently qualified as a Customer Mobile MVP**:

- Login credentials are submitted in URL query parameters, while the shared HTTP client installs sensitive request/response logging without a release guard.
- Bearer tokens are persisted in ordinary SharedPreferences, not secure platform storage.
- Email verification still calls a GET verification protocol that the current backend rejects for ordinary six-digit Customer codes.
- Booking payment discovery requests a general catalog, not booking/shop/currency checkout authorization.
- The current manual Customer refund workflow, eligible amounts, states, references and safe intent retry are absent.
- Customer financial completion cannot be qualified from the existing redirect/sentinel-based UI.
- Session cleanup and unresolved mutation handling need coordinated refactoring.
- Refund & Cancellation Policy integration is missing.
- Compatible dependencies could not be resolved with the installed SDK; Android development build failed before native compilation. iOS native builds are unavailable in this Linux environment.

**Findings:** 2 P0 confidentiality blockers; 9 P1 required-capability/qualification gaps. These are grouped findings, not a count of every affected line or screen. Environmental blockers and missing tests are explicitly distinguished from confirmed source defects.

**Estimated MVP implementation coverage: 57.5%, using the declared 20-capability source-based denominator in section 27.** This is not a readiness score or a passing acceptance result.

**Frozen Web baseline remains unchanged:** **16/20 = 80%; functional CONDITIONAL GO; production NO-GO.** Legal content remains product-policy drafts requiring professional review, not legal certification. No accepted Web campaign was reopened.

## 2. Customer mobile architecture discovered

**Intended source:** `M`, corroborated by `replit.md` and `scripts/build-mobile.mjs`. Discovery found no second `pubspec.yaml` under `.migration-backup`; no competing Customer mobile implementation was established. Root monorepo API/canvas artifacts are not alternative native Customer apps.

The existing application contains **875 files**, approximately **16.5 MB of file content / 18 MB filesystem usage**, **694 Dart files**, of which **657 are handwritten and 37 generated**, **39 presentation page directories**, **36 BLoC directories**, and **21 repository implementations**.

| Layer | Actual implementation | Assessment |
|---|---|---|
| Entry/bootstrap | `M/lib/main.dart` | Firebase initialization, background messaging handler, portrait orientation, downloader initialization, local storage and DI before `AppWidget` |
| App shell | `M/lib/presentation/app_widget.dart` | Provider/BLoC/theme setup, connectivity, splash and ScreenUtil |
| Presentation | `M/lib/presentation/pages`, `components`, `style` | Native Flutter screens and reusable widgets; broad legacy commerce scope |
| Navigation | `M/lib/presentation/route` | Static route helper classes and imperative Navigator navigation |
| State | `M/lib/application` | BLoC events/state, Freezed-generated files; some Provider state |
| Domain | `M/lib/domain/interface`, `model`, `di` | Interfaces, hand-written response parsers, GetIt dependency registration |
| Infrastructure | `M/lib/infrastructure/repository`, `service` | Dio HTTP, repositories, formatting/error helpers |
| Persistence | `M/lib/infrastructure/local_storage/local_storage.dart` | SharedPreferences-backed static storage |
| External integration | `M/lib/infrastructure/firebase`, `app_links` | Firebase/social/chat/push and deep-link helpers |
| Native shells | `M/android`, `M/ios` | Gradle/Kotlin and Xcode/Swift/CocoaPods |

This is a reusable layered foundation, but not strict domain isolation: BLoCs carry `BuildContext`, access global local storage and invoke callbacks/navigation; mutable nullable models, generic `dynamic` errors and broad app-wide helpers make authority/lifetime boundaries harder to verify.

## 3. Framework, version and dependency baseline

`M/pubspec.yaml`: package **`demand`**, version **`1.0.0+1`**, Dart **`>=3.10.0`**, Flutter **`>=3.38.5`**. `pubspec.lock` exists. Its presence does not establish reproducibility with this environment.

Installed toolchain observed: **Flutter 3.32.0 / Dart 3.8.0**, below both requirements.

Important dependency groups:

- BLoC/Flutter BLoC, Freezed, GetIt, Provider and Dartz.
- Dio, connectivity_plus and shared_preferences.
- ScreenUtil, Flutter HTML/SVG, calendars, form/phone widgets, refresh/shimmer/toast and Google Fonts.
- Firebase Core/Auth/Messaging/Firestore; Google, Facebook and Apple sign-in.
- Google Maps, clustering, geolocation, Places, map launcher, Nominatim/routing.
- WebView, PayFast, links/URL launcher, downloader, media/file/camera/share/calendar APIs.

Multiple foundational dependencies use **`any`**. Three dependencies are Git-sourced: `easy_date_timeline` uses a mutable `dev` ref; `google_place` and `payfast` have no explicit ref in the manifest. The lockfile must be evaluated with the supported toolchain before claiming a resolved package graph. No package version, lockfile or generated file was changed.

There is no evidence here that Flutter itself is obsolete or unsuitable. The observed mismatch is a local toolchain/dependency-resolution blocker, not a justification to regenerate the project.

## 4. Current buildability and status

Validation was isolated in `/tmp/agendaally-mobile-audit/customer_app`, with separate HOME, PUB_CACHE and XDG_CONFIG_HOME; original source was not built in place.

| Command | Result | Classification |
|---|---|---|
| `flutter --version` | Flutter 3.32.0; Dart 3.8.0 | STATICALLY VERIFIED environment observation |
| `flutter pub get --offline` | Exit 1: `Because demand requires SDK version >=3.10.0, version solving failed.` | Dependency/version issue: available SDK too old |
| `flutter analyze --no-pub` | Exit 1; unresolved package imports and cascading analyzer errors | Static validation NOT AVAILABLE on a valid resolved dependency graph |
| `flutter build apk --debug --no-pub` | Exit 1: `[!] No Android SDK found. Try setting the ANDROID_HOME environment variable.` | Unsupported local native build environment |
| iOS build | NOT EXECUTED | Xcode/macOS native tooling unavailable |
| Native app/customer flow | NOT EXECUTED | No qualified build/device/emulator |

The analyzer emitted **42,819 issues**, but this number is **not a valid mobile source-defect count**. It ran without resolved dependencies, so missing package symbols/generated context create cascading errors. The full output is retained compressed, not rewritten into a misleading passing report.

No online install, SDK upgrade, Java/Android setup, CocoaPods install, generation, Firebase provisioning or build repair was attempted. Missing secrets/services are not claimed as proven command-failure causes where the command stopped earlier.

## 5. Existing screen and feature inventory

The complete file inventory is `source-inventory.json`. The 39 actual page families are:

`address`, `app_setting`, `auth`, `become_seller`, `blog`, `booking`, `cart`, `category`, `chat`, `checkout`, `compare`, `country`, `drawer`, `filter`, `gift_cart`, `group_order`, `help_policy_term`, `home`, `initial`, `like`, `location`, `main`, `map`, `master_page`, `membership`, `my_digital_files`, `no_internet`, `notification`, `order`, `parcel`, `product_detail`, `products`, `profile`, `review`, `search`, `service`, `shop`, `story`, `transactions`.

| Customer capability | Existing source and behavior | Boundary |
|---|---|---|
| Registration/login/recovery/social/phone | `pages/auth`, `application/auth`, `auth_repository.dart` | Present; ordinary email verification stale; credentials/session unsafe |
| Profile and password/settings | `pages/profile`, `app_setting`, `user_repository.dart` | Present; backend response/session qualification pending |
| Country/address/location selection | `pages/country`, `address`, `location`, `map`; `address_repository.dart` | Present; native permissions/maps provisioning unqualified |
| Discovery/search/categories/filter | `home`, `search`, `filter`, `category`, `service`, `shop`, `master_page` | Present; service/Shop/locale/geography tuple must stay coherent |
| Service/Specialist/time booking | `booking/select_master.dart`, `select_book_time.dart`, `select_time_modal.dart` | Present; availability/calculation APIs used |
| Notes/custom forms/address/payment | `booking/notes_page.dart`, `form_page.dart`, `address_list.dart`, `booking_payment_page.dart` | Present; payment eligibility context missing |
| Booking confirmation/failure/detail/list/history | `booking/confirm_page.dart`, `failure_page.dart`, `booking_screen.dart`, `booking_list.dart` | Present; authoritative financial interpretation not qualified |
| Cancellation | `booking/widget/cancel_screen.dart`; `BookingBloc.CancelBook` | API mutation and list removal; not refund execution |
| Wallet/transactions | `transactions`, `user_repository.dart`, payment BLoCs | Balance/history and legacy funding/transfer paths; not cash redemption |
| Manual booking refund eligibility/request/history | No current manual-finance client/models/screens found | MISSING |
| Terms/Privacy/help | `help_policy_term`, drawer, settings repository | Present; Refund & Cancellation Policy absent |
| Notifications | `notification`, notification BLoC/model, Firebase integration | REST list plus push scaffolding; delivery/navigation acceptance absent |
| Products/orders/legacy order refunds | `products`, `product_detail`, `cart`, `checkout`, `order` | Broad pre-existing commerce implementation, not proof of frozen fulfillment/financial parity |
| Other existing scope | Chat, stories, blogs, reviews, favorites, compare, group order, membership, gift cards, parcel, digital files, become-seller | Inventory, not automatic Mobile MVP requirements |

No screen was deleted, consolidated, redesigned or added.

## 6. Navigation architecture and mobile-specific UX

`M/lib/presentation/route/app_route*.dart` separates general, Shop and service route helpers. Main page uses BLoC-selected tabs and multiple UI-type-dependent navigation layouts. Drawer exposes legal/account/legacy commerce entries. This is native stack/sheet navigation, not a Web screen embedded as the whole app.

SOURCE-CONFIRMED reusable UX: SafeArea usage, bottom sheets, CustomScaffold/CustomButton, pull-to-refresh, shimmers/loading flags, password visibility, forms, date/time widgets and custom booking cards. Fixed ScreenUtil design size is **375×812**; bootstrap requests portrait-up/down.

Source-review concerns:

- Multiple conditional tab variants broaden the state/back-navigation test surface.
- Booking spans several screens/sheets; payment completion and restart can leave unclear state.
- Calendar selection uses server-provided times/disabled dates but device formatting and selection state still need Shop-timezone qualification.
- Some icon controls/pop affordances and compact custom controls need semantic-label, touch-target and large-text review.
- Loading flags are not proof against two events arriving before disabled UI rebuilds.
- No source review proves TalkBack/VoiceOver, keyboard overflow, Android predictive/system back, iOS swipe-back, orientation, small-screen or tablet acceptance.

Recommendation: retain the native hierarchy; qualify one service-first navigation variant and relevant lifecycle states before expanding legacy variants. This audit did not redesign navigation.

## 7. Authentication and session architecture

`auth_repository.dart:17–38` submits login via `POST /api/v1/auth/login` with **`queryParameters` containing the password**. Backend login route/controller exists and returns `data.token`, `access_token`, `token_type`, `user`; API shape compatibility does not make credential transport safe.

`http_service.dart:23–30` always adds the bearer interceptor and request-header/body/response-body logging. `token_interceptor.dart` adds bearer only; it has no centralized `onError` session-expiry policy.

`local_storage.dart:23–28` stores/retrieves bearer tokens with SharedPreferences. Profile/user—including cached Wallet information—is also serialized there. No secure-storage adapter or bearer refresh protocol was found. Do not invent refresh-token semantics for the existing Sanctum bearer contract.

Logout has a concrete lifetime problem:

1. `drawer_page.dart:280–283` navigates to login and calls `authRepository.logout()` without awaiting or processing its result.
2. `auth_repository.dart:107–123` clears local data only after server logout succeeds.
3. A timeout/offline logout can therefore leave the old token/profile on-device after the UI has moved to login.
4. `LocalStorage.clear()` removes several keys but does not itself reset all in-memory BLoCs, Firebase identities, downloads or every preference.

Some profile/count paths locally react to unauthorized responses; this is not coordinated 401/403 handling. Backend authentication, revocation and ownership remain authoritative. No actual mobile server-logout, account-switching or revoked-token acceptance was performed.

## 8. API integration map

**172 repository HTTP call sites** are retained individually in `api-callsite-inventory.json`, including exact expressions, method, endpoint literals, auth expression, lexical request fields, response parser, direct consumer references and importing presentation files. SDK calls/downloader requests are additionally covered in sections 14, 18 and 19; they are not falsely counted as Dio calls.

The following is the human contract map. Status is source-level; even a matching route needs runtime acceptance. Lexical field extraction is not an exact schema where models produce JSON or branches build payloads.

| Screen/action → client | Method/path family; request/auth | Backend/response → mobile result | Classification |
|---|---|---|---|
| Login → AuthBloc/AuthRepository | POST `v1/auth/login`; email or phone + password; public | Auth LoginController → `LoginResponse` → token/user/profile | **UNSAFE / INCORRECT** transport/logging; route exists |
| Email signup/verify → AuthRepository | POST `auth/register`; email; GET `auth/verify/$verifyCode`; public | RegisterController/AuthByEmail; VerifyAuthController rejects ordinary six-digit GET codes | **STALE** verification |
| Password recovery → AuthRepository | POST email/phone forgot routes; legacy email code route carries email in query | LoginController now also offers recipient-bound body verification; parsers `RegisterResponse`/`VerifyData` | **PARTIALLY MATCHES**; supported legacy compatibility is not recommended transport |
| Phone/social → AuthRepository/FirebaseService | Phone verification sends `verifyId`, `verifyCode`, phone; social POST Google callback sends identity data; public | PhoneVerifyRequest requires Firebase `type/id` contract where used; callback gated by EnvironmentPolicy | **PARTIALLY MATCHES / UNKNOWN**; no activation or accepted identity exchange |
| Logout/delete → AuthRepository | POST logout; DELETE owned profile; bearer | LoginController/profile controller → local clear on success | **PARTIALLY MATCHES**; local lifecycle unsafe |
| Profile/password/digital list/history → UserRepository | Dashboard/user profile, histories, transactions; bearer; locale/currency/page fields | User/Wallet/Transaction resources → cached UserModel and history screens | **PARTIALLY MATCHES / UNKNOWN**; current finance workflow absent |
| Country/city/address/routing → AddressRepository | REST countries/cities/delivery/warehouses; owned address CRUD; public vs bearer; selected location fields | Rest geographic resources/UserAddress; routing uses external client | **UNKNOWN / REQUIRES RUNTIME ACCEPTANCE**; endpoint inventory retained |
| Home/banner/promotions → BannersRepository | REST banner/advertisement/promotion paths; locale/filter fields; generally public | Rest resources → home and promotional state | **UNKNOWN / REQUIRES RUNTIME ACCEPTANCE** |
| Blogs → BlogsRepository | REST blog list/detail; locale/page; public | Rest Blog resources → blog UI | **UNKNOWN / REQUIRES RUNTIME ACCEPTANCE** |
| Category/brand discovery → Categories/BrandsRepository | REST categories/brands, pagination/search/IDs; public | Rest Category/Brand resources → filters/list/detail | **UNKNOWN / REQUIRES RUNTIME ACCEPTANCE** |
| Shop/detail/photos/membership/gifts → ShopsRepository | REST shops, galleries, membership/gift paths; dashboard owned memberships/gifts; locale/geography/currency | Shop/related resources → Shop cards/detail/state | **PARTIALLY MATCHES / UNKNOWN**; legacy extras not automatically required |
| Specialist/detail/photos → MasterRepository | GET `rest/masters`, `masters/$id`, `master/$id/galleries`; service/Shop/geography filters; public | Rest MasterController/resources → MasterModel and selection | **PARTIALLY MATCHES** source contract; runtime pending |
| Service/category → ServiceRepository | GET `rest/services`, categories; `shop_id`, `master_id`, `has_master`, locale/currency/geography; public | Rest Service/Category resources → discovery/selection | **PARTIALLY MATCHES** source contract; runtime pending |
| Availability → BookingRepository | GET `rest/master/times-all`; selected service-master IDs and start; public | Rest MasterController/validated multi-time request → `CheckTimeResponse`/date times | **PARTIALLY MATCHES**; timezone/lifecycle acceptance pending |
| Calculation → BookingRepository | POST `rest/bookings/calculate`; `data[]`, service selections, coupon/gift; public | Rest MasterController/Booking StoreRequest/BookingRepository calculation → `BookingCalculateResponse` | **PARTIALLY MATCHES**; server is price authority |
| Create booking → BookingRepository | POST `dashboard/user/bookings`; bearer; `data[].service_master_id/start_date`, extras, notes/forms/location, payment/currency/wallet fields | User BookingController validates and injects authenticated user → BookingResource collection → booking ID/sentinel callback | **PARTIALLY MATCHES**; intent/financial UX gaps |
| Owned bookings/detail → BookingRepository | GET user bookings; parent/status/page/location filters; `bookings/$id/get-all`; bearer | User BookingController/BookingResource → lists/detail with transaction relationships | **PARTIALLY MATCHES**; source existence is not ownership acceptance |
| Cancel → BookingRepository | POST `dashboard/user/booking/parent/$id/canceled`; bearer | User BookingController cancellation → BLoC removes item and invokes callback | **PARTIALLY MATCHES**; refund is separate |
| Payments → PaymentsRepository/BookingBloc | GET `rest/payments`; repository supports context, booking BLoC passes none | Rest PaymentController returns `meta.catalog_only=true`, `checkout_authorization=false` without context → mobile treats rows as methods | **UNSAFE / INCORRECT contextual use** |
| Provider initiation → PaymentsRepository/WebView | Authenticated provider initiation paths; payable IDs/provider tag | Payment services → redirect URL → WebView boolean/confirm screen | **PARTIALLY MATCHES / UNSAFE** return interpretation; external providers not qualified |
| Product/cart/order → Products/Cart/OrderRepository | REST products; owned cart/order CRUD/status/refund paths; IDs, options, currency, delivery, coupon | Product/cart/order resources → checkout/order/legacy refund UI | **UNKNOWN / REQUIRES RUNTIME ACCEPTANCE** against frozen product fulfillment; not manual finance |
| Parcel → ParcelRepository | Parcel calculation/order/status paths, address/type fields; public/bearer by operation | Parcel controller/resources → parcel UI | **UNKNOWN / REQUIRES RUNTIME ACCEPTANCE**; defer |
| Reviews → ReviewRepository | REST review lists and owned POST review paths for supported entities; IDs/rating/content | Review resources → lists/forms | **UNKNOWN / REQUIRES RUNTIME ACCEPTANCE**; optional |
| Forms/gallery → Form/GalleryRepository | REST custom forms; authenticated gallery/upload operations | Form/Gallery resources → booking forms/media | **UNKNOWN / REQUIRES RUNTIME ACCEPTANCE**; validate upload/answer contracts |
| Chat → ChatRepository/Firebase | Backend user/chat discovery plus Firebase/Firestore operations | Chat/user models → conversational UI | **UNKNOWN / REQUIRES RUNTIME ACCEPTANCE**; optional |
| Notification/settings/legal → User/SettingsRepository | Authenticated notification list/read actions; public settings/languages/currencies, `rest/term`, `rest/policy` | Notification and translation models → lists/settings/legal HTML | **PARTIALLY MATCHES**; financial events/refund policy absent |
| Customer manual refund → no mobile client | Required existing `dashboard/manual-finance` capabilities/sources/list/detail/store/action | ManualFinanceController/WorkflowQueries → integer-unit amounts, version, states, customer-safe events/actions/reference | **MISSING mobile integration**; backend exists |
| Refund policy → no mobile client | Existing GET `v1/rest/pages/refund_cancellation` | Current Page/translation content, used by W/infoService | **MISSING mobile integration**; backend exists |

This audit deliberately leaves unexecuted or insufficiently traced request/resource variants **UNKNOWN**, rather than claiming every legacy integration matches. Deferred modules must receive their own contract qualification if included later; no backward-compatible backend change is inferred from their presence.

## 9. Backend/mobile mismatch inventory

| Mismatch | Actual authority/evidence | Category |
|---|---|---|
| Customer email OTP via GET token path | `B/routes/api.php:71–74`; `VerifyAuthController:53–59,115–123` requires POST email + six-digit OTP for ordinary Customer flow; GET is high-entropy Driver protocol | **A — MOBILE STALE**, P1-02 |
| Login secrets in query/header/body logs | Current backend accepts body credentials; mobile uses query and unconditional logger | **A — MOBILE UNSAFE**, P0-01 |
| Phone Firebase/native and recovery transport | `PhoneVerifyRequest`, current purpose-bound AuthByMobilePhone/EnvironmentPolicy must control identity exchange; mobile branches use legacy shapes | **A / runtime unknown**, P1-02; not proof that all phone paths fail |
| Payment catalog used as eligibility | Rest PaymentController explicitly differentiates catalog from checkout authorization; mobile FetchPayments drops context | **A — MOBILE STALE**, P1-04 |
| Manual Customer refund module absent | Existing capabilities/sources/workflow API and safe projection | **A — MOBILE MISSING**, P1-06 |
| No Refund & Cancellation Policy client | Existing REST Page endpoint and W policy page | **A — MOBILE MISSING**, P1-08 |
| Generic numeric finance/transaction models | Existing manual-finance uses string integer units + currency/scale/version/state; legacy models cannot express it | **A — MOBILE MISSING/STALE**, P1-05/06 |
| Wallet send/withdraw affordances | Routes exist but frozen boundaries intentionally contain/defer unsupported money movement; route existence does not grant permission | **A — stale scope / C — owner scope decision**, not a request to enable backend transfers |
| Durable approved Terms acceptance | No shared mobile policy-version receipt established; existing checkbox is not auditability | **C — OWNER/LEGAL DECISION** before production; not a present functional-MVP backend gap |

**No genuinely required new backend API has been established for the recommended bounded Mobile MVP.** Unknown compatibility is not evidence that the backend must change.

## 10. Business-logic drift findings

**MOBILE BUSINESS-LOGIC DRIFT RISK**:

- `BookingBloc:132–139,173–180` vetoes Wallet spending using cached `LocalStorage.getUser().wallet.price`. This may reject a legitimate newly funded customer or display stale affordability. It must not be a substitute for server balance/eligibility.
- Payment selection/filtering/default choice occurs locally; pay-later filters exclude Cash/Wallet but initial selection is taken from the unfiltered list (`booking_bloc.dart:189–210`).
- `PayLater:167–187` invokes a success sentinel for Cash/Wallet without a corresponding payment mutation. The normal pay-later list excludes these methods, so this is a inconsistent/stale branch, not proof Cash was marked collected.
- The app formats dates, chooses disabled times and displays pricing. Calculation and final booking must remain server-authoritative; device timezone and local presentation cannot grant availability.
- Booking history groups hard-coded status lists. Unknown future/backend states must not silently disappear or become completed.
- Public/owned geographic state, selected currency, service-master and Shop IDs must be invalidated together when context changes.
- `MasterRepository.getLikedMaster` assigns `region_id` from `countryId` in the authenticated branch. This is a source-confirmed filter error, not a cross-tenant exploit.

Positive: availability and booking calculation are already requested from backend services. No mobile implementation of the current manual refund amount/percentage exists; missing functionality must not later be filled with local formulas.

## 11. Financial semantics findings

| Required distinction | Source finding |
|---|---|
| CANCELLED ≠ REFUNDED | Cancellation only removes an item after API success; no source-confirmed automatic “refunded” claim in that handler. Missing refund integration is a gap, not proof cancellation moves money. |
| REFUND REQUESTED/APPROVED ≠ REFUNDED | Current manual refund states are absent. Legacy product/order-refund UI is not the manual execution workflow. |
| APPROVED ≠ MONEY MOVED | No current customer-safe manual approval/completion mapping exists in mobile. Required future states must use authoritative workflow state. |
| PAYMENT SELECTED ≠ PAYMENT COLLECTED | Selection/cached balance/ID sentinel does not establish collection; contextual payment eligibility is dropped. |
| CASH SELECTED ≠ CASH COLLECTED | Cash/Wallet booking callbacks both use `-1`, then generic confirmation. A booking “confirmed” label is not literally “Cash paid”; nevertheless the app lacks qualified distinct collection presentation. |
| REQUIRES_REVIEW ≠ COMPLETED | No typed manual-review state/UI handling found. Must preserve uncertainty and prohibit automatic resend/new intent. |
| Provider redirect ≠ payment proof | `web_view.dart:25–35` returns true for any URL containing `payment-success`; `booking_payment_page.dart:166–171,197–202` then navigates to confirmation without authoritative booking/payment refresh. |

`confirm_page.dart:27,41` says **“confirmed”**, not an explicitly observed “paid” or “refunded” assertion. The finding is an ambiguous/unverified completion path, not evidence of backend money movement. Existing transaction detail rows read backend transaction statuses; their existence does not prove support for manual finance states.

No payment, refund, payout, Wallet mutation or provider callback was executed during this audit.

## 12. Cancellation and refund findings

Existing cancellation: confirmation sheet → authenticated cancel endpoint → success callback/list refresh/removal. There is no current mobile cancellation-to-eligible-source-to-manual-request sequence.

| Required state/capability | Mobile |
|---|---|
| Cancellation result | Existing source; runtime not accepted |
| No eligible refund / eligible amount | MISSING for manual finance |
| Request with server source/context/amount/currency | MISSING |
| REQUESTED, APPROVED, REJECTED, CANCELLED request | MISSING |
| REQUIRES_REVIEW | MISSING |
| COMPLETED + safe external reference/history | MISSING |
| Saved same-intent retry | MISSING |

`B/ManualFinanceController.sources` already supplies eligible, finalized payer-owned sources and `amount_units`, `currency_code`, `money_scale`; ineligible/unsupported sources are omitted. `store` validates `command_key` UUID, allocation/context, integer-unit string, currency and masked destination metadata. `WorkflowQueries` supplies safe states/actions/events.

Refund percentages, windows, fees and timelines are not to be invented in Flutter. Empty eligible-source results must not be replaced with an estimated refund. Cancellation success must not produce refund-completion notifications. Wallet-to-cash redemption, arbitrary partial refunds and automatic provider refunds remain deferred.

## 13. Ownership and multi-tenant findings

Mobile passes Shop/service-master/booking/transaction/refund identifiers. These IDs and hidden buttons are not authorization.

Source-positive backend anchors: User BookingController injects authenticated user on create; manual refund sources filter payer ownership; safe workflow projection calls FinanceScope before serialization. This does not constitute a newly passed BOLA/ownership campaign.

Risks requiring later qualification:

- Non-namespaced profile/Wallet/global preferences and retained BLoCs across account changes.
- Authenticated requests without centrally coordinated expired-session handling.
- Late responses from a previous customer/Shop/currency updating current visible state.
- Deep-link/group invitation IDs accepted from URLs before strict URI/context validation.
- Public relation projections must remain public; a cached profile/default finance model must not be used to fabricate authoritative private values.

**No cross-customer or cross-Shop disclosure exploit was verified.** No destructive authorization tests or accepted backend campaigns were run.

## 14. Private financial evidence boundary

Current manual-finance projection is an important reusable boundary, not a missing Finance API:

- `B/app/Services/ManualFinance/WorkflowQueries.php:14–18` authorizes scope and selects safe fields.
- Actions and event details are permission-filtered.
- External reference/executed time appear only for authoritative COMPLETED.
- Attachments are included only with `evidence.view` (`:48–49`).
- Signed attachment download additionally checks expiry/signature and current actor scope in ManualFinanceController.

Mobile does not currently consume this module; therefore **no current manual Finance attachment exposure was confirmed**. Missing integration is not evidence that private receipts should be added to customer screens.

Adjacent source risks: legacy transaction models contain generic provider `request`/metadata; digital downloads use bearer headers and public device storage (`user_repository.dart:358–375`). They are digital-product downloads, **not proven private Finance evidence downloads**. Do not reuse them for private receipts or cache signed Finance URLs in customer preferences/deep links. Existing downloads are not purged by `LocalStorage.clear()`.

Future Customer UI should use only safe workflow status/reference data and backend-authorized actions. Finance-only evidence must stay absent even when a Customer can see refund history.

## 15. Retry, idempotency and network loss

Source mutation pattern is BLoC loading flag → repository request → callback/snackbar. This is useful UI feedback, but not durable intent custody or exactly-once submission.

| Flow | Existing protection | Missing/unknown |
|---|---|---|
| Booking create | Loading flag; server validation/availability | No persisted unresolved intent or qualified timeout/restart reconciliation |
| Cancellation | Server request before list removal | No qualified lost-response reconciliation; adjacent refresh can race mutation |
| Payment initiation | Provider request/callback; loading | Redirect bool, no authoritative resume/refresh/uncertainty state |
| Manual refund | Backend supports command UUID/version | No mobile intent generation/persistence/reuse |
| Wallet/legacy transfer | Local balance/UI flags; backend authority | Not proof of exactly-once behavior; unsupported operations must remain contained |

No automatic mutation retry interceptor or destructive concurrency campaign was run. Do not infer double-credit or duplicate booking from a missing UI guard alone; backend safeguards may reject duplicates.

Use existing refund `command_key`/version semantics. For booking/payment ambiguity, first reconcile owned server state and do not silently create a fresh intent or re-execute money movement. A new generic booking-idempotency API has **not** been established as mandatory by this audit.

## 16. Bounded mobile security review

**P0-01:** credentials/sensitive traffic disclosure via query parameters and unconditional Dio logging. Evidence: AuthRepository login/social methods, HttpService logger; WebView/deep-link helpers also log complete URLs. This is a source-confirmed leakage path, not evidence actual credentials were stolen.

**P0-02:** bearer tokens persisted in ordinary SharedPreferences. Evidence: `local_storage.dart:17–28`. OS application sandboxing is not secure token storage; compromise/backup/debug access and device/account reuse need explicit handling. No actual token was displayed or copied into evidence.

Additional source/configuration findings:

- WebView enables unrestricted JavaScript, has no host/scheme allowlist and uses substring success detection. No JS bridge exploit was claimed.
- App-link handler uses path substring/query presence, logs URL/query and does not explicitly enforce allowed host/scheme. Some link types are TODOs.
- iOS ATS permits arbitrary loads (`Info.plist:85–88`). No actual cleartext credential transmission was executed.
- Android/iOS permissions exceed a minimal service-booking scope; iOS calendar purpose is `INSERT_REASON_HERE`, and tracking/media/location descriptions need review.
- `AppConstants` contains release-sensitive demo/game/Firebase-phone flags and Uzbek phone/demo-location defaults.
- Build wrapper lists client defines named `WS_SECRET` and PayFast passphrase/merchant fields. A server secret must never be made safe merely by passing it through a define file. **No configured real secret value was established**; these are conditional bundle-boundary risks, not a reported credential discovery.
- No disabled TLS-verification override or hard-coded Admin password/private key was confirmed in reviewed mobile source. This is bounded review, not a global security certification.

No scanners, exploitation, credential rotation or provider activation were run.

## 17. Local storage, cache and offline behavior

Persisted source categories: bearer token, user/profile/Wallet snapshot, language/currency/settings/translations, addresses/location, cart, favorites, viewed products/recent search, selected UI variant, board/warehouse/admin-related selections. Models are stored as JSON/preferences, not a qualified account-isolated encrypted financial cache.

`LocalStorage.clear():546–560` deletes token/user/cart/address and several lists. It does not mean “every customer-specific artifact is cleared”: not every preference is removed, in-memory state is separate, and downloader files/Firebase session are outside this method. Several delete operations are asynchronous but the clear method itself is not awaited as a coordinated boundary.

There is no qualified offline mutation queue or durable refund/payment intent store. Connectivity/no-internet screens exist; they do not prove cached balances/status are visibly marked stale. Cached Wallet balance must be informational, not spending authority. Account/Shop/currency changes should invalidate dependent state and discard late responses.

## 18. Notifications

| Channel | Actual source | Qualification |
|---|---|---|
| In-app | Notification list/model/BLoC and authenticated REST operations | SOURCE-CONFIRMED; no accepted device flow |
| Backend email | Auth/register/recovery backend communication; mobile parses generic success/errors | Existing backend channel, not mobile email delivery acceptance; delivery uncertainty must not be hidden |
| Push | Firebase Messaging initialization, token upload, background hook; main-page message listeners | SDK wiring exists; delivery/service/device qualification NOT VERIFIED |
| Local device | Calendar integration and downloader notifications | Not a verified appointment-reminder/refund notification engine |

Main page opened-message handling primarily navigates `new_message` to chat; foreground handler is empty. A fully handled booking cancellation/refund-request/approval/review/completion push path was not established. Notification constants do not prove corresponding delivery/navigation behavior.

Existing backend notification content/status may be displayed, but mobile must not derive “refunded” from cancellation/approval or use a push as completion proof without refreshing authoritative state. Nullable/malformed message data, cold start, login gates and duplicate listener disposal need later qualification.

## 19. External SDK/service inventory

| SDK/service | Classification for proposed service-first MVP | Existing wiring and consequence |
|---|---|---|
| AgendaAlly REST backend | REQUIRED | Core account/discovery/booking/Wallet/refund authority |
| Firebase Core/Auth | REQUIRED by **current bootstrap**, OPTIONAL product dependency for email/password | Unconditional initialize; existing phone/social branches. Disabling safely would be later work, not configuration performed here |
| Firebase Messaging | OPTIONAL | Push scaffolding; REST in-app notification can be MVP core without claiming push |
| Cloud Firestore/chat | OPTIONAL / DEFERRED | Existing social/chat module, not required to qualify booking/refunds |
| Google/Facebook/Apple sign-in | OPTIONAL | Existing providers; native credentials/policies/runtime not qualified |
| Google Maps/Places/clustering/geolocation | OPTIONAL | Location UX; current key guards/build wrapper exist; non-map address/location path must be qualified |
| Routing/Nominatim/map launcher | OPTIONAL / DEFERRED | Legacy routing/navigation consumers; availability not tested |
| PayFast/provider WebViews | DEFERRED / activation-gated | Existing SDK/helper code does not establish approved live provider capability |
| Media/camera/files/downloader | OPTIONAL | Profile/media/digital content; cannot substitute for private Finance evidence controls |
| Calendar/share/app links | OPTIONAL mobile enhancements | Existing native helpers; association/device behavior unqualified |
| Google Fonts | OPTIONAL rendering dependency | Inter typography implementation; offline/fallback behavior unqualified |
| Analytics/crash reporting | NOT ESTABLISHED | No dedicated app analytics/crash-reporting integration was confirmed; Firebase Core is not proof of either |

No third-party service was connected, configured, enabled or exercised.

## 20. Legal-policy integration

The current seeded Terms, Privacy and separate Refund & Cancellation Policy are authoritative product drafts. Policies were not rewritten or reseeded.

Mobile SettingsRepository loads `rest/term` and `rest/policy`, extracts translation title/description and renders HTML in `TermPage`/`PolicyPage`. Drawer exposes those pages. It has no current refund policy call/page or links from cancellation/refund/booking surfaces.

Current Web reference: `W/services/info.ts` calls `v1/rest/pages/refund_cancellation`; Web route `/refund-cancellation` renders that policy. `W/manual-refunds/page.tsx` uses legal links and distinct financial state copy.

`AuthState`/AuthBloc contain a local `isAcceptTerm` toggle, but the presentation search did **not** establish a wired `isAcceptTerm` registration control or a server policy-version acceptance receipt. Do not describe this state field as accepted consent. Existing Web checkbox mechanics were preserved and untouched.

Recommendation: expose all three existing documents where appropriate in native account/legal, signup and booking/cancellation/refund flows; qualify HTML link handling and unavailable-content behavior. Do not invent a mandatory new consent requirement or professional legal approval.

## 21. Policy acceptance/versioning recommendation

**YES: recommend shared Web + Mobile approved-Terms acceptance auditability before production**, not separate incompatible client implementations.

Owner/legal review should determine approved document/version, the event that requires acceptance, actor/account binding, server timestamp, locale/channel, immutable version identity/content reference, and reacceptance rules. A client boolean/current-page view is not an auditable acceptance receipt.

This is the separately bounded future recommendation **“Make approved Terms acceptance auditable before launch.”** It is not a new consent mechanism, a completed implementation, or a required backend change to repair the existing mobile API during this audit.

## 22. Customer Web ↔ Customer Mobile feature-parity matrix

Web column refers to the frozen Customer/backend comparison source, **not re-executed Web acceptance**. No feature is classified runtime COMPLETE/MATCHED simply because source exists.

| Capability | Frozen reference | Mobile parity |
|---|---|---|
| Registration | Existing native Web/backend account flow | IMPLEMENTED BUT STALE / PARTIAL |
| Verification | Recipient-bound Customer email OTP | IMPLEMENTED BUT STALE |
| Login | Backend credential/token contract | PARTIAL; source present, P0 blockers |
| Logout | Server logout and client cleanup | PARTIAL |
| Password reset | Recipient/purpose-bound backend reset | PARTIAL; transport/identity branches need adaptation |
| Profile/password/settings | Owned profile/currency/language | PARTIAL; source present |
| Discovery/search | Public Shop/service/geographic discovery | PARTIAL; broad reusable source |
| Shop detail | Public Shop/locations/services | PARTIAL |
| Services | Current service catalog | PARTIAL |
| Specialist selection | Shop/service-master relationships | PARTIAL |
| Availability | Server availability/time rules | PARTIAL; timezone/device qualification |
| Booking create | Server calculation/booking authority | PARTIAL; retry/context gaps |
| Booking details | Owned resource/transactions | PARTIAL |
| Upcoming/history | Owned booking statuses | PARTIAL |
| Cancellation | Server cancellation ≠ refund | PARTIAL |
| Payment selection | Context-authorized methods | IMPLEMENTED BUT STALE |
| Cash | Selected ≠ collected | PARTIAL; explicit collection presentation unqualified |
| Wallet balance/spend | Server balance/collection authority | PARTIAL; cached veto and state gaps |
| Wallet/transaction history | Owned transaction/history resources | PARTIAL |
| Refund eligibility | Existing eligible-source API | MISSING |
| Refund request | Current command-key workflow | MISSING |
| Refund states/history/reference | Current safe workflow projection | MISSING |
| In-app notifications | Backend notifications | PARTIAL |
| Push/local device calendar | Native extension | MOBILE-ONLY source; OPTIONAL/unqualified |
| Terms | Current seeded Terms | PARTIAL; document source available |
| Privacy | Current seeded Privacy | PARTIAL; document source available |
| Refund & Cancellation Policy | `/refund-cancellation`, REST Page | MISSING |
| Support/contact/help | Existing help/contact content | PARTIAL; no escalation/delivery promise qualified |
| Auditable approved-Terms acceptance | Proposed shared production mechanism | OWNER DECISION / FUTURE BACKEND DEPENDENCY, not current functional-MVP gate |
| Product shopping/pickup/fulfillment | Frozen Web product capability | IMPLEMENTED BUT STALE / UNKNOWN; DEFERRED from proposed service-first native scope pending owner choice |
| Legacy order refund | Product/order request resource | PARTIAL; NOT equivalent to manual financial execution |
| Wallet cash withdrawal/automatic provider refund | Intentionally contained/deferred | NOT REQUIRED / DEFERRED |
| Chat/stories/blogs/reviews/favorites | Existing extra features | DEFERRED / POST-MVP unless owner selects |
| Membership/gift/group/parcel/digital/become-seller | Broad legacy suite | NOT REQUIRED FOR proposed Customer Mobile MVP |
| Admin/Finance operations, Vendor/Specialist payouts | Separate roles/obligations | NOT REQUIRED; must not enter Customer UI |

## 23. KEEP / REPAIR / REFACTOR / REPLACE / BUILD / DEFER matrix

| Subsystem | Decision | Rationale |
|---|---|---|
| Flutter project/build architecture | KEEP WITH REPAIR/QUALIFICATION | Supported target framework; local toolchain below declared baseline |
| Navigation | KEEP WITH MINOR REPAIR | Native route/tab/sheet hierarchy reusable; lifetime/login/back states need qualification |
| Authentication | REFACTOR | Correct current OTP/identity transport and coordinated session authority |
| Shared API client | REFACTOR | Confidential transport/logging, typed failures, centralized auth handling |
| Models/types | REFACTOR selected contracts | Keep catalog models; add exact finance units/currency/version/state rather than force legacy numeric transaction schema |
| BLoC state management | KEEP WITH REFACTOR | Retain framework; reduce global/BuildContext coupling and define account/context/intent lifetime |
| Bearer storage | REPLACE adapter | Secure platform storage; coordinated clearing, not a new auth backend |
| Public cache/preferences | KEEP WITH REPAIR | Keep harmless preferences; account/context isolation and stale labels |
| Discovery/Shop/service/Specialist | KEEP WITH MINOR REPAIR | Substantial existing implementation; filter/data tuple fixes and qualification |
| Booking | REFACTOR IN PLACE | Server authority already used; explicit pending/reconciled financial and retry states missing |
| Payments | REFACTOR | Use context methods, server collection state and safe provider-return handling |
| Wallet | KEEP WITH REPAIR | Server-backed balance/history/spend; remove authority from cached balance, defer withdrawal |
| Cancellation | KEEP WITH REPAIR | Existing server action; explain distinct policy/refund steps |
| Manual Customer refunds | MISSING — BUILD | Existing backend supports required workflow; typed states/intents/customer-safe data needed |
| Notifications | REFACTOR core / DEFER optional push expansion | REST useful; event/lifecycle/financial wording needs qualification |
| Legal/policies | KEEP WITH REPAIR + BUILD missing policy | Reuse current documents; add refund document/client links later |
| Shared UI/design system | EVOLVE | Reusable native components; stale brand assets and financial-state clarity, not whole UI rewrite |
| Android configuration | KEEP WITH REPAIR/QUALIFICATION | Identity/signing/service/permissions and supported SDK/build required |
| iOS configuration | KEEP WITH REPAIR/QUALIFICATION | CocoaPods/Xcode shell reusable; deployment/ATS/permissions/association/signing require macOS acceptance |
| Testing | MISSING — BUILD | No meaningful Customer Flutter tests found |
| Broad legacy commerce/social/parcel | DEFER | Do not expand first native release merely because source exists |

## 24. Mobile design-system assessment

**Decision: EVOLVE.**

Source includes shared CustomScaffold, CustomButton, fields/cards/pop controls, theme/color sets, dark/light variants, Inter typography through Google Fonts, radius/spacing conventions and `.r/.sp` scaling. These are reusable native building blocks; pixel-for-pixel Web duplication is not required.

Direct static image inspection of `M/assets/images/app_logo.png` shows a white monogram with **“24”** on a tan square, not a demonstrated AgendaAlly wordmark. Package `demand`, Android `com.ibeauty.app`, generic manifest description and old demo defaults also show legacy identity. Build-time label configuration exists, but changing a label alone would not replace all assets/store identity.

Needed evolution: consistent AgendaAlly assets/labels, a single primary/secondary/destructive button hierarchy, differentiated booking vs collection/refund badges, visible pending/uncertain states, clear empty/refund-ineligible copy, accessible form errors, typography scaling and locale/time presentation. Branding differences alone are P2, not P0/P1.

No native screen rendering, contrast measurement, device-size capture or mobile redesign was performed.

## 25. Existing testing baseline

`source-inventory.json` found **no Dart unit/widget/API-client/navigation/integration test suite or `integration_test` directory**. `flutter_test` is declared as a development dependency but declaration is not coverage.

The only discovered test source is `M/ios/RunnerTests/RunnerTests.swift`; `testExample()` is an empty generated template, not Customer behavior coverage. No mobile CI/Fastlane/Codemagic/GitHub workflow was found within M.

Later implementation should include:

- Unit/model/contract fixtures for current auth, contextual payments, finance units/states and unknown errors.
- Widget/navigation tests for login/logout, lists/empty/error/pending states, forms/back/large text.
- Mocked-network tests for revoked sessions, stale responses, double taps, lost acknowledgments and same-intent refund retries.
- Supported-toolchain build checks with frozen dependency graph.
- Android and iOS device acceptance for account/booking/cancellation/Wallet/refund flows and lifecycle/security boundaries.

No tests were created or weakened, and no accepted backend/Web suites were rerun.

## 26. Prioritized findings

### P0 — 2 security/confidentiality blockers

| ID | Finding and exact evidence | Required closure later |
|---|---|---|
| P0-01 | Sensitive URL/header/body/response logging: `M/lib/infrastructure/repository/auth_repository.dart:17–38,43–64`; `M/lib/infrastructure/service/http_service.dart:23–30` | Body-only sensitive auth transport; prohibit secret/token-bearing logs; verify release/debug redaction and request contract |
| P0-02 | Bearer token in SharedPreferences: `M/lib/infrastructure/local_storage/local_storage.dart:17–28` | Secure platform token adapter, coordinated token migration/cleanup and account/session tests; no actual token disclosure |

### P1 — 9 required-capability/qualification findings

| ID | Finding | Evidence / kind |
|---|---|---|
| P1-01 | Supported dependency/native bootstrap/build baseline unavailable | Manifest SDK constraints; isolated exact logs; unconditional Firebase bootstrap. **Environment/configuration qualification gap, not proven code compile defect** |
| P1-02 | Current account verification/recovery/identity contract adaptation | AuthRepository verification/recovery/phone branches vs current VerifyAuthController/PhoneVerifyRequest/purpose-bound auth. **Confirmed ordinary email mismatch; other branches partly unverified** |
| P1-03 | Coordinated logout/401/403/account-context invalidation absent | Drawer logout not awaited; clear only after server success; token-only interceptor; storage/BLoC/Firebase lifetimes. **Source-confirmed** |
| P1-04 | Checkout authorization context dropped | BookingBloc `FetchPayments:189–210` vs Rest PaymentController `23–42`. **Source-confirmed** |
| P1-05 | Qualified authoritative collection/financial state UI/model/resume missing | Booking callbacks/pay-later/WebView/confirmation; legacy numeric DTOs vs current finance state. **Source-confirmed architecture gap; no actual money movement test** |
| P1-06 | Customer manual refund eligible amount/request/states/history/reference missing | No mobile manual-finance client; existing ManualFinanceController/WorkflowQueries. **Source-confirmed** |
| P1-07 | Durable unresolved mutation/intent reconciliation missing | Booking/payment/cancellation BLoC/repository flows; no current refund command store. **Source-confirmed absence; duplicate financial effect NOT verified** |
| P1-08 | Refund & Cancellation Policy and appropriate flow links missing | SettingsRepository/Terms/Privacy vs current W/infoService/Page endpoint. **Source-confirmed** |
| P1-09 | Meaningful critical-flow mobile tests/acceptance absent | No Dart suite; empty Runner template; no qualified builds/device flows. **Coverage/qualification gap, not reported test failure** |

### P2 — hardening and mobile UX

P2-01: strict deep-link/WebView URI validation, listener disposal and safe URL logging; provider activation must be gated on these controls.  
P2-02: permission minimization, iOS ATS/purpose-description/file-sharing review and locale/device-accessibility qualification.  
P2-03: correct geographic filter fields, stale account/context responses and consistent unknown-status/empty/error presentation.  
P2-04: identity assets/demo defaults, design-system evolution and native navigation/back/keyboard refinement.  
P2-05: email/push delivery-state presentation, malformed/cold-start notification handling and financial event routing.  
P2-06: dependency provenance/pinning/reproducibility review without an automatic upgrade campaign.

### P3 / deferred / not required

Expanded social providers, chat/stories/blogs, membership/gifts/compare/groups, parcel/digital products, live maps/push/calendar enhancements and product-shopping expansion may be separate owner-selected work. Automatic provider refunds/payouts, Wallet-to-cash redemption, arbitrary partial refunds, Specialist platform payouts and Finance receipt access are **not** missing Customer MVP requirements.

## 27. Proposed Customer Mobile MVP scope and completeness

**Proposed scope, not owner-approved implementation:** service-first Customer account/profile → public Shop/service/Specialist discovery → authoritative availability/calculation → booking → owned upcoming/detail/history → cancellation → context-safe Cash/Wallet presentation/spend/history → current Customer manual refund eligibility/request/status/history → in-app notifications/support → all three current policies.

Exclude Admin/Finance execution/evidence, Vendor/Driver/Specialist apps, new live providers, cash redemption, arbitrary refund calculations and broad legacy commerce/social extras. If the owner requires physical-product shopping/pickup in the first native release, revise scope and completeness denominator **before implementation**; existing product screens are not qualified fulfillment parity.

**Scoring rule:** 20 equally weighted capability groups, each 1 point. **1** = substantial implemented source with no identified core missing path in that group; **0.5** = implemented but stale/partial; **0** = missing or unusable current integration. Full source credit is **not** runtime acceptance, security clearance or proof that every variant matches. Refund and legal/support are explicitly grouped; this estimate is implementation coverage, not a risk-weighted production score.

| # | Capability group | Credit |
|---|---|---|
| 1 | Registration | 0.5 |
| 2 | Current Customer email verification | 0 |
| 3 | Login | 0.5 |
| 4 | Logout/session boundary | 0.5 |
| 5 | Password recovery | 0.5 |
| 6 | Profile/password/settings | 1 |
| 7 | Discovery/search | 1 |
| 8 | Shop details | 1 |
| 9 | Services/Specialist selection | 1 |
| 10 | Availability selection | 1 |
| 11 | Booking creation | 0.5 |
| 12 | Owned booking details/upcoming/history | 1 |
| 13 | Cancellation | 0.5 |
| 14 | Context-authorized payment methods | 0 |
| 15 | Cash presentation | 0.5 |
| 16 | Wallet balance/spend/history | 0.5 |
| 17 | Transaction presentation | 0.5 |
| 18 | Manual refund eligibility/request/states/history | 0 |
| 19 | In-app notifications | 0.5 |
| 20 | Legal/support: all three policies and contact | 0.5 |

**11.5 / 20 = 57.5% estimated source-based MVP implementation coverage.**  
**Mobile runtime qualification: NOT PERFORMED. Mobile acceptance gates passed in this audit: NONE.**  
This denominator is separate from—and does not alter—the canonical Web 20-gate score.

## 28. Proposed implementation phases — NOT EXECUTED

1. **Confidentiality/session prerequisite:** approved compatible toolchain baseline; close P0 logging/transport and secure-token storage; coordinated account/logout/unauthorized handling with focused fixtures.
2. **Account/API/discovery contract alignment:** current OTP/recovery/identity, typed errors/models, Shop/location/currency lifetime, existing discovery/native forms.
3. **Booking/collection client alignment:** contextual payment methods, server-authoritative amount/availability/state, explicit uncertain/resume and safe retry paths; no provider activation.
4. **Customer manual refund and policy integration:** existing eligible-source/command/version APIs, exact integer money, all safe states/references, persistent same-intent retry, current policies; no Finance attachments.
5. **Separate platform qualification:** Android supported SDK/build/device; iOS macOS/Xcode/build/device; required critical-flow and accessibility/lifecycle acceptance.

Every phase requires owner authorization and bounded acceptance. No phase began, no implementation task was automatically created, and no dependency-upgrade/redesign campaign was started.

## 29. Backend API changes genuinely required

**BACKEND CHANGES REQUIRED: NO — for the proposed bounded functional Mobile MVP, none established by this audit.**

Existing backend supports current Customer auth, public discovery, booking calculation/creation/owned state/cancellation, contextual payment eligibility, Wallet/history, safe manual refund workflows, notifications and all three policy documents.

Required work is adapting/building the mobile client, not re-enabling unsafe legacy routes or weakening auth/ownership/financial rules. Read-only DTO uncertainty is not authority to change server behavior.

Potential shared approved-Terms acceptance/version receipts are a **separate owner/legal-approved production recommendation**. If approved, that shared design may require server persistence/API work; it is not silently included in the present “NO.”

## 30. Owner/business decisions

- Confirm service-first native MVP versus including physical-product shopping/pickup. This changes qualification scope, not the frozen Web baseline.
- Confirm first platform/OS coverage; current iOS Podfile targets **16.7**.
- Approve app/store identifier, AgendaAlly assets and removal/configuration of demo defaults.
- Choose required phone/social/Firebase/push/maps functionality; current source bootstrap depends on Firebase even if only email/password is selected.
- Decide supported markets/phone defaults/locales/Shop-timezone presentation.
- Obtain professional review of policy drafts and decide shared approved-Terms acceptance/reacceptance rules.
- Select compatible-toolchain provisioning and qualified build/signing custody without automatically upgrading application dependencies.

Financial authority is **not** reopened: cancellation/approval/selection do not move money; private Finance evidence stays private; platform payouts are Vendor-only and outside this Customer app.

## 31. Android qualification status

- **SOURCE REVIEWED:** YES — Dart plus Kotlin/Android shell.
- **CONFIGURATION REVIEWED:** YES — Gradle, manifest, resources and build wrapper.
- **STATIC VALIDATION:** NOT AVAILABLE on resolved supported dependencies; attempted analyzer FAILED for unresolved imports.
- **BUILD:** FAILED — isolated debug APK command exited 1, Android SDK unavailable.
- **RUNTIME ACCEPTANCE:** NOT EXECUTED.

Observed configuration: `compileSdkVersion 36`; `applicationId "com.ibeauty.app"`; target/min SDK delegated to Flutter; release signing configuration reads keystore properties; deep-link host/app label/Facebook/maps values are build-configured. Production signing, service config, package association, platform permissions and API/device behavior are not qualified.

Existing `scripts/build-mobile.mjs` key guard/redaction/generated-file lock/cleanup was inspected, **not invoked**. Its existence does not prove a successful maps-disabled/enabled release build.

## 32. iOS qualification status

- **SOURCE REVIEWED:** YES — shared Dart plus Swift AppDelegate/Runner shell.
- **CONFIGURATION REVIEWED:** YES — Podfile, Info.plist, entitlements and Xcode/native build settings reviewed within available source.
- **STATIC VALIDATION:** shared Dart dependency validation NOT AVAILABLE; Swift/Xcode validation NOT AVAILABLE here.
- **BUILD:** NOT EXECUTED — Linux environment lacks macOS/Xcode.
- **RUNTIME ACCEPTANCE:** NOT EXECUTED.

Observed: Podfile minimum **iOS 16.7**, framework/modular-header settings and native plugin handling; maps-key bootstrap guard; push/Apple sign-in/associated-domain entitlements; ATS arbitrary loads, broad permission descriptions, background modes and file sharing. Apple capabilities/provisioning, APNs, Firebase, key association, native compilation and device lifecycle are not accepted.

## 33. Exact commands and read-only validation

Actual build/static command arguments and output/exit evidence are retained in `validation-commands.json` and corresponding logs. The isolation environment was:

```sh
cd /tmp/agendaally-mobile-audit/customer_app
HOME=/tmp/agendaally-mobile-audit/home
PUB_CACHE=/tmp/agendaally-mobile-audit/pub-cache
XDG_CONFIG_HOME=/tmp/agendaally-mobile-audit/config
CI=true
FLUTTER_SUPPRESS_ANALYTICS=true
DART_SUPPRESS_ANALYTICS=true
flutter --version
flutter pub get --offline
flutter analyze --no-pub
flutter build apk --debug --no-pub
```

The environment values above describe the isolated process environment; they are not instructions to run the four commands against normal source. The separate version output includes the CLI's first-run telemetry notice; it is not app analytics acceptance.

Source inspection used `find`, `rg`, `sed`, file reads and Node traversal/hashing. Durable evidence generation:

```sh
node docs/development/evidence/customer-mobile-audit/collect-source-evidence.mjs
git status --short --untracked-files=all
git diff --stat
```

Database verification used **CLI PDO only**, read-only SQLite URI `?mode=ro`, `PRAGMA query_only=ON`, a read transaction and rollback. It did not boot Laravel. Exact reproducible recipe:

```sql
SELECT type,name,tbl_name,sql FROM sqlite_master
WHERE name NOT LIKE 'sqlite_%' ORDER BY type,name;
SELECT * FROM "<escaped-table>" ORDER BY rowid;
```

Hash: **SHA256 of PHP `serialize(fetchAll(PDO::FETCH_ASSOC))`**. Result: unchanged schema and **214/214 identical table fingerprints**, not merely equal counts. Row content/credentials were not copied to evidence.

Source baseline result: **5,816/5,816 identical file hashes**, zero changed/missing baseline files. Independent Git checks show no tracked application/root configuration changes; the only additions are report/evidence and the pre-existing instruction attachment. Fixed hash-baseline scope/limitations are declared in `source-preservation.json`.

No workflow was started/restarted/reconfigured, no provider/email/financial action executed, no database/schema/policy change, no production deployment, no active app screenshot represented as native acceptance, and no Web acceptance campaign rerun.

## 34. Exact files changed

**Application source, dependencies, native configuration, Web/Admin/backend, normal database/schema: NONE.**

Only these audit report/evidence files were added:

```text
docs/development/agendaally-customer-mobile-audit.md
docs/development/evidence/customer-mobile-audit/README.md
docs/development/evidence/customer-mobile-audit/collect-source-evidence.mjs
docs/development/evidence/customer-mobile-audit/source-inventory.json
docs/development/evidence/customer-mobile-audit/api-callsite-inventory.json
docs/development/evidence/customer-mobile-audit/source-before.json
docs/development/evidence/customer-mobile-audit/source-after.json
docs/development/evidence/customer-mobile-audit/source-preservation.json
docs/development/evidence/customer-mobile-audit/database-before.json
docs/development/evidence/customer-mobile-audit/database-after.json
docs/development/evidence/customer-mobile-audit/database-fingerprint-recipe.json
docs/development/evidence/customer-mobile-audit/sdk-version.txt
docs/development/evidence/customer-mobile-audit/pub-get---offline.txt
docs/development/evidence/customer-mobile-audit/pub-get---offline.exit
docs/development/evidence/customer-mobile-audit/analyze---no-pub.exit
docs/development/evidence/customer-mobile-audit/analyze---no-pub.txt.gz
docs/development/evidence/customer-mobile-audit/analyze-excerpt.txt
docs/development/evidence/customer-mobile-audit/build-apk---debug---no-pub.txt
docs/development/evidence/customer-mobile-audit/build-apk---debug---no-pub.exit
docs/development/evidence/customer-mobile-audit/validation-commands.json
docs/development/evidence/customer-mobile-audit/git-status.txt
docs/development/evidence/customer-mobile-audit/git-diff-stat.txt
docs/development/evidence/customer-mobile-audit/report.html
docs/development/evidence/customer-mobile-audit/evidence-manifest.json
```

Temporary copy/tool outputs remained under `/tmp`; none were applied to M. The instruction attachment was already untracked before report generation and is not an app change made by this audit. No project-memory or profile edits were necessary.

## 35. Recommended next implementation task

**One bounded recommendation:** after owner review, authorize **Customer Mobile authentication/API confidentiality and session-foundation repair in place**, including an explicitly approved compatible-toolchain validation baseline, P0 secret-free transport/logging and secure token storage, current Customer OTP contract, and coordinated logout/unauthorized/account isolation with focused tests.

Exclude booking/refund feature implementation, redesign, backend changes, provider activation, broad package upgrades, Vendor/Driver work and production deployment from that first campaign. This is a recommendation only; nothing has been started.

## Final decision

**CUSTOMER MOBILE FOUNDATION: B. REFACTOR IN PLACE**

**CUSTOMER MOBILE MVP COMPLETENESS: 57.5%** — declared source-based implementation coverage, **not accepted native readiness**.

**P0 FINDINGS: 2**  
**P1 FINDINGS: 9**

**BACKEND CHANGES REQUIRED: NO** — none genuinely established for the proposed bounded functional MVP; shared policy-acceptance auditability remains a separately approved production recommendation.

**CURRENT MOBILE ARCHITECTURE SUITABLE FOR CONTINUED DEVELOPMENT: YES WITH REFACTORING**

**ANDROID STATUS:** source/configuration reviewed; compatible static validation unavailable; debug APK attempt failed for missing Android SDK; runtime NOT EXECUTED.

**IOS STATUS:** source/configuration reviewed; Xcode/native validation unavailable; native build and runtime NOT EXECUTED.

**SHARED WEB + MOBILE POLICY ACCEPTANCE AUDITABILITY RECOMMENDED BEFORE PRODUCTION: YES**

**CUSTOMER MOBILE IMPLEMENTATION CAN SAFELY BEGIN: YES AFTER SPECIFIED P0 CLOSURE** — this means broader MVP work after confidentiality/session prerequisite closure and owner authorization. An explicitly authorized P0-only prerequisite campaign can begin first; the audit itself authorizes no repair.

**NEXT RECOMMENDED IMPLEMENTATION CAMPAIGN:** the single bounded authentication/API confidentiality and session-foundation campaign in section 35.

**STOP:** Audit and evidence only. Await owner review and explicit authorization. No remediation, mobile redesign, backend change, dependency-upgrade campaign or Vendor/Driver work was initiated.
