## App Links configuration — iBeauty project

This document explains, step by step, how to configure App Links (Android) and Universal Links (iOS) for your Flutter app. The domain/host is sourced from environment (dart-define) variables.

- Android package: `com.ibeauty.app`
- iOS Bundle Identifier: `com.ibeauty.org`
- Host (domain): provided via the `DEEP_LINK_URL` environment variable

### 1) Environment variables (dart-defines)

Pass the following values when running or building Flutter. Example:

```bash
flutter run \
  --dart-define=DEEP_LINK_URL=example.com \
  --dart-define=WEB_URL=https://example.com \
  --dart-define=BASE_URL=https://api.example.com \
  --dart-define=APP_NAME="iBeauty"
```

- **DEEP_LINK_URL**: App/Universal Links host (e.g., `example.com`).
- **WEB_URL**: Base site URL (must include https scheme).

In `lib/app_constants.dart`, `AppConstants.deepLinkHost` is read from dart-defines and is used by `AppLinksService.createDeepLink`.

### 2) Web files (.well-known)

Expose the following files over HTTPS on your domain. They must be available at `https://<HOST>/.well-known/...`. Replace `<HOST>` with your actual domain (e.g., `example.com`).

#### 2.1 For Android: `assetlinks.json`

URL: `https://<HOST>/.well-known/assetlinks.json`

Content (example):

```json
[
  {
    "relation": [
      "delegate_permission/common.handle_all_urls"
    ],
    "target": {
      "namespace": "android_app",
      "package_name": "com.ibeauty.app",
      "sha256_cert_fingerprints": [
        "PUT_YOUR_DEBUG_SHA256_FINGERPRINT_HERE",
        "PUT_YOUR_RELEASE_SHA256_FINGERPRINT_HERE"
      ]
    }
  }
]
```

- See section 8 below for obtaining SHA256 fingerprints.

#### 2.2 For iOS: `apple-app-site-association`

URL: `https://<HOST>/.well-known/apple-app-site-association`

- Must be served without a file extension
- Served as JSON with Content-Type: `application/json`

Content (example):

```json
{
  "applinks": {
    "apps": [],
    "details": [
      {
        "appIDs": [
          "APPLE_TEAM_ID.com.ibeauty.org"
        ],
        "paths": [
          "*"
        ]
      }
    ]
  },
  "webcredentials": {
    "apps": [
      "APPLE_TEAM_ID.com.ibeauty.org"
    ]
  }
}
```

- Replace `APPLE_TEAM_ID` with your Apple Team ID (App Store Connect → Membership).

### 3) Android configuration

In `android/app/build.gradle`, the following is already in place and used:

```gradle
android {
    defaultConfig {
        applicationId "com.ibeauty.app"
        resValue "string", "deep_link_host", dartDefines.DEEP_LINK_URL
        // ... other settings
    }
}
```

The App Links intent-filter in `AndroidManifest.xml`:

```xml
<intent-filter
    android:autoVerify="true"
    tools:targetApi="m">
    <action android:name="android.intent.action.VIEW" />
    <category android:name="android.intent.category.DEFAULT" />
    <category android:name="android.intent.category.BROWSABLE" />
    <data
        android:host="@string/deep_link_host"
        android:pathPattern="/.*"
        android:scheme="https" />
</intent-filter>
```

- With `android:autoVerify="true"`, once domain verification succeeds, supported links will open directly in the app.

### 4) iOS configuration

- In Xcode → Targets → Runner → Signing & Capabilities, add the “Associated Domains” capability.
- Add `applinks:<HOST>`. Example: `applinks:example.com`
- Bundle Identifier: `com.ibeauty.org`
- Ensure the `apple-app-site-association` file is correctly served over HTTPS.

Entitlements example (may already exist):

```xml
<key>com.apple.developer.associated-domains</key>
<array>
    <string>applinks:example.com</string>
</array>
```

### 5) Flutter behavior

`AppLinksService` listens for deep links and constructs links using `AppConstants.deepLinkHost`.

```dart
final url = AppLinksService.createDeepLink(
  path: 'shops/my-salon',
  parameters: {'shop_id': '123'},
);
// Result: https://<HOST>/shops/my-salon?shop_id=123
```

- Share group order: `DeepLinkUtils.createGroupOrderLink(cartId)` → `https://<HOST>/group?cart_id=...&user_id=...`
- Share a shop: `DeepLinkUtils.createShopLink(shopId: 1, slug: 'my-salon')` → `https://<HOST>/shops/my-salon?shop_id=1`
- Share a product: `DeepLinkUtils.createProductLink(productId: 10, shopId: 1)` → `https://<HOST>/product?product_id=10&shop_id=1`

### 6) Supported link formats

- Shop: `https://<HOST>/shops/<slug>?shop_id=<id>`
- Product: `https://<HOST>/product?product_id=<id>[&shop_id=<id>][&category_id=<id>]`
- Category: `https://<HOST>/category?category_id=<id>`
- Group/Cart: `https://<HOST>/group?cart_id=<id>&user_id=<id>[&country_id=&region_id=&city_id=]`

`AppLinksService._handleDeepLink` recognizes: `group`, `shops` (or `shop_id`), `product` (with `product_id`), `category` (with `category_id`).

### 7) Testing

Android:

```bash
# Test an app link
adb shell am start -W -a android.intent.action.VIEW \
  -d "https://example.com/shops/my-salon?shop_id=123" com.ibeauty.app

# Inspect domain associations
adb shell dumpsys package domain-preferred-apps | grep example.com | cat
```

iOS:

```bash
# Via Simulator/Safari
open "https://example.com/shops/my-salon?shop_id=123"
```

Verify web files:

```bash
curl -I https://example.com/.well-known/assetlinks.json
curl -I https://example.com/.well-known/apple-app-site-association
```

### 8) Getting SHA256 fingerprints (Android)

Debug:

```bash
keytool -list -v -keystore ~/.android/debug.keystore \
  -alias androiddebugkey -storepass android -keypass android | grep SHA256
```

Release:

```bash
keytool -list -v -keystore /path/to/release.keystore -alias YOUR_ALIAS | grep SHA256
```

### 9) Implementation checklist

- [ ] Choose your domain and enable SSL (HTTPS)
- [ ] Provide `DEEP_LINK_URL` and `WEB_URL` via dart-defines
- [ ] Create `assetlinks.json` (Android) with correct SHA256 fingerprints
- [ ] Create `apple-app-site-association` (iOS) with your Team ID and Bundle ID
- [ ] Add iOS Associated Domains: `applinks:<HOST>`
- [ ] Ensure Android `AndroidManifest.xml` has the App Links `intent-filter`
- [ ] Test links on both Android and iOS

### 10) Troubleshooting

- If the browser opens instead of the app: ensure domain verification succeeds (`assetlinks.json` / `apple-app-site-association`).
- Android not working: confirm `sha256_cert_fingerprints` match your debug/release keystores and `android:autoVerify` is enabled.
- iOS not working: verify Team ID + Bundle ID, and that “Associated Domains” is added.
- Validate link formats and parameters (`shop_id`, `product_id`, etc.) are provided correctly.

---

This guide reflects your current identifiers (`com.ibeauty.app`, `com.ibeauty.org`) and the env-based host (`DEEP_LINK_URL`). The host `example.com` is shown as a placeholder — replace it with your actual domain.
