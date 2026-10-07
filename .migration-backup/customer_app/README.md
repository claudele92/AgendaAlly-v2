# 🚀 Demand24 Customer App

**Demand24** is a comprehensive multi-vendor service booking and eCommerce platform built with Flutter. It combines marketplace shopping with professional service booking in a single, powerful mobile application.

[![Flutter](https://img.shields.io/badge/Flutter-3.38.5+-blue.svg)](https://flutter.dev/)
[![Dart](https://img.shields.io/badge/Dart-3.10+-blue.svg)](https://dart.dev/)
[![License](https://img.shields.io/badge/License-Proprietary-red.svg)](LICENSE)

---

## 📱 About

Demand24 is a production-grade mobile application featuring:

- 🛒 **Multi-vendor Marketplace** - Browse and purchase products from multiple vendors
- 📅 **Service Booking System** - Book appointments with professional service providers
- 🗺️ **Location-based Search** - Find nearby shops and services with Google Maps integration
- 💳 **Multiple Payment Gateways** - PayFast, Maksekeskus, and wallet system
- 🎨 **Customizable UI** - Multiple themes with dark mode support
- 🌐 **Multi-language Support** - International and RTL language support
- 📱 **Cross-platform** - Native iOS and Android applications

---

## ✅ Requirements

Native Maps configuration is now environment-only. Use the workspace-root
`scripts/build-mobile.mjs` wrapper and follow
[`mobile-maps-build.md`](../../docs/development/mobile-maps-build.md).
Do not restore or commit the former generated `Dart-Defines.xcconfig`.

| Tool                | Version              |
|---------------------|----------------------|
| Flutter SDK         | 3.38.5+ (stable)     |
| Dart SDK            | 3.10+                |
| Android SDK         | API 34+ (Android 14) |
| Android Compile SDK | API 36               |
| iOS Deployment      | 16.7+                |
| Java                | 17+ (OpenJDK)        |
| Kotlin              | 2.0.21+              |
| Gradle              | 8.7.0+               |
| Xcode               | 26.0+ (macOS only)   |
| CocoaPods           | Latest (for iOS)     |

---

## 🏗️ Architecture

The application follows **Clean Architecture** principles with **BLoC** state management pattern:

```
lib/
├── application/          # 38 BLoC modules (State Management)
│   ├── auth/            # Authentication flows
│   ├── product/         # Product management
│   ├── booking/         # Service booking
│   ├── cart/            # Shopping cart
│   ├── order/           # Order processing
│   └── ...              # 33 more feature modules
│
├── domain/              # Business Logic Layer
│   ├── interface/       # 20+ repository interfaces
│   ├── model/           # Data models and responses
│   └── di/             # Dependency injection (GetIt)
│
├── infrastructure/      # Data Layer
│   ├── repository/     # 21 repository implementations
│   ├── service/        # HTTP, Firebase, Helpers
│   ├── local_storage/  # SharedPreferences wrapper
│   ├── firebase/       # Firebase Auth, FCM, Firestore
│   └── app_links/      # Deep linking service
│
└── presentation/       # UI Layer
    ├── pages/          # 40+ feature screens
    ├── components/     # 64+ reusable widgets
    ├── route/          # Navigation & routing
    └── style/          # Themes & styling
```

### Key Design Patterns

- **BLoC Pattern** - Business Logic Component for state management
- **Repository Pattern** - Data access abstraction
- **Dependency Injection** - GetIt service locator
- **Freezed** - Immutable state and union types
- **Either Pattern** - Functional error handling with dartz

---

## 🚀 Getting Started

### Prerequisites

#### 1. Flutter SDK
Install Flutter SDK 3.38.5 or higher:
```bash
flutter --version  # Verify installation
```

#### 2. Android Development
- **Android Studio** with Android SDK API 34+
- **Java 17+** (OpenJDK recommended)
- **Kotlin 2.0.21+**
- Set `ANDROID_HOME` environment variable

#### 3. iOS Development (macOS only)
- **Xcode 16.0+** from App Store
- **CocoaPods**: `sudo gem install cocoapods`
- **Xcode Command Line Tools**: `xcode-select --install`

#### 4. Code Generation Tools
Installed automatically with `flutter pub get`:
- build_runner
- freezed / freezed_annotation
- json_serializable

### Initial Setup

#### 1. Clone the Repository
```bash
git clone <repository-url>
cd demand_customer
```

#### 2. Install Dependencies
```bash
flutter pub get
```

#### 3. Generate Code (Freezed Models)
```bash
flutter pub run build_runner build --delete-conflicting-outputs
```

#### 4. Configure Environment Variables

Create a `.env` file in the project root (see [Environment Setup](#-environment-setup)):

```env
APP_NAME=Demand24
BASE_URL=https://api.yourdomain.com
WEB_URL=https://yourdomain.com
GOOGLE_MAPS_API_KEY=your_google_maps_api_key
# ... more variables (see Environment Setup section)
```

For iOS, also update `/ios/Flutter/Dart-Defines.xcconfig`:
```
APP_NAME=Demand24
BASE_URL=https://api.yourdomain.com/
# ... (same variables as .env)
```

#### 5. Run the Application
```bash
# Development mode with .env file
flutter run --dart-define-from-file=.env

# Or specify a device
flutter run -d <device-id> --dart-define-from-file=.env
```

---

## 🔧 Environment Setup

### Required Environment Variables

The application requires these environment variables for configuration:

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_NAME` | Application display name | Demand24 |
| `APP_ID` | Bundle identifier | com.demand24.customer |
| `BASE_URL` | API base endpoint | https://api.demand24.com |
| `WEB_URL` | Web application URL | https://demand24.com |
| `ADMIN_URL` | Admin panel URL | https://admin.demand24.com |
| `GOOGLE_MAPS_API_KEY` | Google Maps API key | AIza... |
| `FIREBASE_API_KEY` | Firebase web API key | AIza... |
| `ROUTING_API` | OpenRouteService URL | https://api.openrouteservice.org/ |
| `ROUTING_KEY` | Routing service API key | 5b3ce3... |
| `DEEP_LINK_URL` | Deep link host domain | demand24.com |
| `PAYFAST_PASSPHRASE` | PayFast passphrase | your_passphrase |
| `PAYFAST_MERCHANT_ID` | PayFast merchant ID | 10000100 |
| `PAYFAST_MERCHANT_KEY` | PayFast merchant key | 46f0cd... |
| `FACEBOOK_APP_ID` | Facebook app ID | 1234567890 |
| `FACEBOOK_CLIENT_TOKEN` | Facebook client token | abc123... |

### Configuration Files

#### Development Environment
Create `.env` in project root:
```env
APP_NAME=Demand24 Dev
APP_ID=com.demand24.customer.dev
BASE_URL=https://dev-api.demand24.com
WEB_URL=https://dev.demand24.com
ADMIN_URL=https://dev-admin.demand24.com
GOOGLE_MAPS_API_KEY=your_dev_key
FIREBASE_API_KEY=your_firebase_key
ROUTING_API=https://api.openrouteservice.org/
ROUTING_KEY=your_routing_key
DEEP_LINK_URL=dev.demand24.com
PAYFAST_PASSPHRASE=your_passphrase
PAYFAST_MERCHANT_ID=your_merchant_id
PAYFAST_MERCHANT_KEY=your_merchant_key
FACEBOOK_APP_ID=your_facebook_app_id
FACEBOOK_CLIENT_TOKEN=your_facebook_token
```

#### Production Environment
Create `.env.production`:
```env
APP_NAME=Demand24
APP_ID=com.demand24.customer
BASE_URL=https://api.demand24.com
# ... production values
```

#### iOS Configuration
Update `/ios/Flutter/Dart-Defines.xcconfig`:
```
APP_NAME=Demand24
BASE_URL=https://api.demand24.com/
WEB_URL=https://demand24.com/
# ... (mirror your .env file)
```

### Running with Environment

```bash
# Development
flutter run --dart-define-from-file=.env

# Production
flutter run --dart-define-from-file=.env.production
```

---

## 📱 App Customization

### Change App Name

#### Method 1: Environment Variables (Recommended)

1. Update `.env` file:
```env
APP_NAME=Your Custom App Name
```

2. Update `/ios/Flutter/Dart-Defines.xcconfig`:
```
APP_NAME=Your Custom App Name
```

3. Clean and rebuild:
```bash
flutter clean
flutter pub get
flutter run --dart-define-from-file=.env
```

#### Method 2: Direct Configuration

**Android:**
- Modify `/android/app/src/main/res/values/strings.xml`:
```xml
<string name="app_name">Your App Name</string>
```

**iOS:**
- Modify `/ios/Runner/Info.plist`:
```xml
<key>CFBundleDisplayName</key>
<string>Your App Name</string>
```

### Change App Logo

**Recommended Format:** 1024x1024px PNG with transparent background

1. **Replace logo file:**
   - Place your logo at `/assets/images/app_logo.png`

2. **Update configuration** (optional):
   - Edit `flutter_launcher_icons.yaml` if customization needed

3. **Generate icons:**
```bash
dart run flutter_launcher_icons
```

This generates icons for all required sizes on both platforms.

### Change Splash Screen

**Recommended Format:** 1080x1920px PNG (portrait orientation)

1. **Replace splash image:**
   - Place at `/assets/images/splash.png`

2. **Update configuration** (optional):
   - Edit `flutter_native_splash.yaml` if customization needed

3. **Generate splash screens:**
```bash
dart run flutter_native_splash:create
```

### Change Package Name

#### Android

1. Update `/android/app/build.gradle`:
```gradle
namespace "com.yourcompany.yourapp"
defaultConfig {
    applicationId "com.yourcompany.yourapp"
}
```

2. Update `/android/app/src/main/AndroidManifest.xml`:
```xml
<manifest package="com.yourcompany.yourapp">
```

3. Rename package directories:
```bash
cd android/app/src/main/kotlin
# Rename com/demand24/customer to com/yourcompany/yourapp
```

4. Update MainActivity.kt:
```kotlin
package com.yourcompany.yourapp
```

#### iOS

1. Open project in Xcode:
```bash
open ios/Runner.xcworkspace
```

2. Select **Runner** target → **General** tab
3. Update **Bundle Identifier**: `com.yourcompany.yourapp`
4. Update **Display Name** if needed

---

## 🎨 UI Customization

### UI Theme Selection

The app supports **multiple UI layouts** that users can choose from:

1. **Initial Launch Flow:**
   - UI Selection Screen (first launch)
   - Location Permission Request (after selection)
   - Main Application

2. **Available Themes:**
   - Home Layout 1 (Modern Grid)
   - Home Layout 2 (Classic List)
   - Home Layout 3 (Compact View)

3. **User Settings:**
   - Change UI theme from Settings → App Settings
   - Preference saved in local storage

### Dark Mode Support

**Automatic Dark Mode:**
- System-based: Follows device dark mode setting
- Manual toggle: Available in Settings
- All screens use `CustomScaffold` for consistent theming

**Implementation:**
- Theme configuration: `/lib/presentation/style/theme/`
- Custom colors: `CustomColorSet` classes
- Automatic color switching based on brightness

### Customizing Themes

Edit files in `/lib/presentation/style/theme/`:

**custom_theme.dart:**
```dart
class CustomColorSet {
  final Color primary;
  final Color backgroundColor;
  final Color textBlack;
  // ... define your colors
}
```

**Update color values:**
```dart
static const CustomColorSet lightTheme = CustomColorSet(
  primary: Color(0xFF007AFF),
  backgroundColor: Colors.white,
  // ...
);

static const CustomColorSet darkTheme = CustomColorSet(
  primary: Color(0xFF0A84FF),
  backgroundColor: Color(0xFF1C1C1E),
  // ...
);
```

---

## 🌍 Internationalization (i18n)

### Translation System

The app uses a **key-based translation system** with API-driven translations:

1. **Translation Keys:**
   - Defined in `/lib/infrastructure/service/tr_keys.dart`
   - ~500+ translation keys
   - Organized by feature area

2. **Usage in Code:**
```dart
// Import helper
import 'package:demand/infrastructure/service/services.dart';

// Use translation
Text(AppHelpers.getTranslation(TrKeys.welcome))
```

3. **Adding New Translations:**
   - Add key to `TrKeys` class
   - Update translations via admin panel
   - Translations sync from API on app start

### Supported Features

- ✅ **Multiple Languages** - English, Arabic, Russian, Uzbek, etc.
- ✅ **RTL Support** - Automatic layout mirroring for RTL languages
- ✅ **Dynamic Switching** - Change language without restart
- ✅ **Currency Localization** - Currency symbols based on locale
- ✅ **Date Formatting** - Locale-aware date/time display

### Language Configuration

**Supported languages** are configured via API. To add a new language:
1. Add translations in admin panel
2. Language appears automatically in app settings
3. User selects from Settings → Language

---

## 🔑 Key Features

### E-Commerce Features
- 🛒 **Multi-vendor Marketplace** - Browse products from multiple sellers
- 🔍 **Advanced Search & Filters** - Category, price, rating filters
- 🛍️ **Shopping Cart** - Multi-vendor cart with real-time calculation
- ❤️ **Wishlist & Compare** - Save favorites and compare products
- ⭐ **Reviews & Ratings** - User reviews with photos
- 📦 **Order Tracking** - Real-time order status updates
- 💾 **Digital Products** - Download digital files after purchase

### Service Booking Features
- 📅 **Appointment Booking** - Schedule with service providers (Masters)
- 🕐 **Time Slot Selection** - Available time management
- 👨‍💼 **Master Profiles** - View provider ratings and services
- 🏢 **Service Categories** - Browse by category and location
- 🔔 **Booking Reminders** - Notifications before appointments
- 📝 **Booking History** - View past and upcoming bookings
- 💬 **Direct Messaging** - Chat with service providers

### Location & Maps
- 🗺️ **Google Maps Integration** - Find nearby shops and services
- 📍 **Location-based Search** - Automatic nearby results
- 🧭 **Turn-by-turn Navigation** - OpenRouteService integration
- 📮 **Address Management** - Save multiple delivery addresses
- 🎯 **Geofencing** - Location-based notifications
- 🗺️ **Map Clustering** - Efficient marker display

### Payment & Wallet
- 💳 **Multiple Payment Methods:**
  - PayFast (South African gateway)
  - Maksekeskus (Estonian gateway)
  - Card payments
  - Digital wallet
- 💰 **Wallet System** - Store credit and quick checkout
- 📊 **Transaction History** - View all payment records
- 🎁 **Gift Cards** - Purchase and redeem gift cards
- 🔄 **Refund Management** - Request and track refunds

### Authentication & Social
- 🔐 **Email/Phone Login** - OTP verification
- 🔑 **Social Login:**
  - Google Sign-In
  - Facebook Login
  - Apple Sign-In (iOS)
- 📱 **SMS Auto-fill** - Automatic OTP entry
- 🔒 **Secure Token Management** - JWT authentication
- 👤 **Profile Management** - Update personal information

### Communication
- 💬 **Real-time Chat** - Firebase Firestore messaging
- 🔔 **Push Notifications** - Firebase Cloud Messaging
- 📧 **Email Notifications** - Order confirmations, updates
- 📱 **SMS Notifications** - Booking reminders
- 🔊 **In-app Notifications** - Notification center

### Special Features
- 📦 **Parcel Tracking** - Track package deliveries
- 🎮 **Gamification** - Loyalty points and rewards
- 👥 **Membership Plans** - Subscription-based benefits
- 🎯 **Referral System** - Invite friends and earn
- 📰 **Blog & Stories** - Content marketing
- 🏷️ **Brand Pages** - Featured brand collections
- 🎨 **UI Customization** - Multiple layout options

---

## 🏗️ Building the App

### Development Build

```bash
# Run on connected device with environment
flutter run --dart-define-from-file=.env

# Run on specific device
flutter devices  # List devices
flutter run -d <device-id> --dart-define-from-file=.env

# Run with specific flavor (if configured)
flutter run --flavor development --dart-define-from-file=.env
```

### Production Builds

#### Android APK (Universal)
```bash
flutter build apk --release --dart-define-from-file=.env.production
```
Output: `build/app/outputs/flutter-apk/app-release.apk`

#### Android App Bundle (Google Play)
```bash
flutter build appbundle --release --dart-define-from-file=.env.production
```
Output: `build/app/outputs/bundle/release/app-release.aab`

#### iOS (macOS only)
```bash
# Build for App Store
flutter build ios --release --dart-define-from-file=.env.production

# Or build IPA (requires Xcode configuration)
flutter build ipa --release --dart-define-from-file=.env.production
```
Output: `build/ios/ipa/*.ipa`

### Build with Different Environments

```bash
# Development
flutter build apk --dart-define-from-file=.env

# Staging
flutter build apk --dart-define-from-file=.env.staging

# Production
flutter build apk --dart-define-from-file=.env.production
```

### Code Generation

Run code generation after modifying freezed models:
```bash
# Full rebuild
flutter pub run build_runner build --delete-conflicting-outputs

# Watch mode (auto-generates on file changes)
flutter pub run build_runner watch --delete-conflicting-outputs

# Clean generated files
flutter pub run build_runner clean
```

---

## 📦 Dependencies

### Core Packages (38 BLoC Modules)

**State Management:**
- `flutter_bloc: ^8.1.6` - BLoC pattern implementation
- `freezed: ^2.5.9` - Code generation for immutable classes
- `freezed_annotation: ^2.4.5` - Annotations for freezed
- `json_annotation: ^4.10.0` - JSON serialization annotations

**Networking & API:**
- `dio: ^5.8.1` - HTTP client with interceptors
- `connectivity_plus: ^6.2.1` - Network connectivity monitoring
- `app_links: ^6.4.1` - Deep linking and Universal Links

**Dependency Injection:**
- `get_it: ^8.0.5` - Service locator for DI
- `dartz: ^0.10.2` - Functional programming (Either, Option)

### Feature Packages

**Maps & Location:**
- `google_maps_flutter: ^2.14.0` - Google Maps for Flutter
- `geolocator: ^14.0.2` - Location services and permissions
- `google_maps_cluster_manager: ^3.0.1` - Map marker clustering
- `google_place: custom` - Google Places API (forked)
- `osm_nominatim: ^3.2.0` - OpenStreetMap geocoding
- `map_launcher: ^3.6.1` - Launch external map apps

**Payment Gateways:**
- `payfast: custom` - PayFast payment integration (forked)
- `webview_flutter: ^4.12.0` - WebView for payment pages

**Authentication:**
- `firebase_auth: ^6.1.3` - Firebase Authentication
- `google_sign_in: ^6.3.0` - Google Sign-In
- `flutter_facebook_auth: ^7.1.2` - Facebook Login
- `sign_in_with_apple: ^7.0.1` - Apple Sign-In
- `app_tracking_transparency: ^2.0.10` - iOS ATT framework

**Firebase Services:**
- `firebase_core: ^4.3.0` - Firebase SDK core
- `firebase_messaging: ^16.1.0` - Push notifications (FCM)
- `cloud_firestore: ^6.1.1` - Real-time database for chat

**Media & Files:**
- `image_picker: ^1.2.1` - Camera and gallery picker
- `file_picker: ^10.3.8` - File selection
- `cached_network_image: ^3.4.1` - Image caching
- `video_player: ^2.10.1` - Video playback
- `chewie: ^2.0.3` - Video player UI
- `photo_view: ^0.15.0` - Zoomable image viewer
- `flutter_downloader: ^1.12.0` - Background file downloads

**UI Components:**
- `flutter_screenutil: ^5.10.0` - Responsive screen adaptation
- `carousel_slider: ^5.0.0` - Image carousels
- `flutter_staggered_grid_view: ^0.7.0` - Staggered grid layouts
- `flutter_slidable: ^3.2.1` - Swipe actions
- `pull_to_refresh: ^2.0.0` - Pull-to-refresh functionality
- `smooth_page_indicator: ^1.3.0` - Page indicators
- `lottie: ^3.4.0` - Lottie animations
- `flutter_html: ^3.0.1` - HTML rendering
- `readmore: ^3.0.0` - Expandable text

**Utilities:**
- `intl: ^0.20.2` - Internationalization and date formatting
- `jiffy: ^6.4.4` - Date manipulation
- `permission_handler: ^12.0.1` - Runtime permissions
- `device_info_plus: ^10.1.2` - Device information
- `share_plus: ^12.0.1` - Native sharing
- `url_launcher: ^6.3.2` - URL and deep link handling
- `add_2_calendar: ^3.0.3` - Calendar event creation
- `sms_autofill: ^2.5.0` - SMS OTP auto-fill
- `flutter_remix: ^0.0.3` - RemixIcon icon pack

**Storage:**
- `shared_preferences: ^2.3.6` - Key-value storage
- `path_provider: ^2.1.6` - File system paths

### Development Tools

- `build_runner: ^2.10.4` - Code generation runner
- `flutter_launcher_icons: ^0.14.4` - App icon generation
- `flutter_native_splash: ^2.4.7` - Splash screen generation
- `flutter_lints: ^5.0.0` - Linting rules

**Total Dependencies:** 147 packages (including transitive)

---

## 🛠️ Troubleshooting

### Common Issues

#### 1. iOS App Name Shows "Runner"
**Problem:** App displays "Runner" instead of your custom name.

**Solution:**
```bash
# 1. Verify environment variables
cat .env  # Check APP_NAME is set
cat ios/Flutter/Dart-Defines.xcconfig  # Check APP_NAME is set

# 2. Clean and rebuild
flutter clean
flutter pub get
cd ios && pod install && cd ..
flutter run --dart-define-from-file=.env
```

#### 2. Environment Variables Not Loading
**Problem:** App doesn't use values from `.env` file.

**Solution:**
- Verify `.env` file exists in project root
- Check format: `KEY=value` (no spaces around `=`, no quotes)
- Ensure using `--dart-define-from-file=.env` flag
- For iOS: Update `/ios/Flutter/Dart-Defines.xcconfig`
- Restart IDE after changing environment files

#### 3. Build Failures
**Problem:** Build fails with various errors.

**Solution:**
```bash
# Clean everything
flutter clean
rm -rf build/
rm -rf .dart_tool/

# Reinstall dependencies
flutter pub get
flutter pub run build_runner build --delete-conflicting-outputs

# For Android: Clean Gradle
cd android && ./gradlew clean && cd ..

# For iOS: Clean CocoaPods
cd ios
rm -rf Pods/ Podfile.lock
pod install
cd ..

# Rebuild
flutter run --dart-define-from-file=.env
```

#### 4. Freezed Code Generation Errors
**Problem:** Build runner fails to generate freezed files.

**Solution:**
```bash
# Delete all generated files
flutter pub run build_runner clean

# Regenerate with force
flutter pub run build_runner build --delete-conflicting-outputs

# If still failing, check for syntax errors in models
# Ensure all freezed classes have proper syntax:
# - @freezed annotation
# - abstract class with mixin
# - factory constructors
# - part directive
```

#### 5. Google Maps Not Showing
**Problem:** Map shows blank or "For development purposes only" watermark.

**Solution:**
- Verify `GOOGLE_MAPS_API_KEY` in `.env` and iOS xcconfig
- Enable required APIs in Google Cloud Console:
  - Maps SDK for Android
  - Maps SDK for iOS
  - Places API
  - Directions API
- Check billing is enabled for Google Cloud project
- Verify API key restrictions allow your bundle IDs
- For Android: Check `AndroidManifest.xml` has meta-data tag
- For iOS: Check `AppDelegate.swift` has API key setup

#### 6. Firebase Not Working
**Problem:** Authentication or messaging not working.

**Solution:**
- Download latest `google-services.json` (Android) and `GoogleService-Info.plist` (iOS)
- Place in correct locations:
  - Android: `android/app/google-services.json`
  - iOS: `ios/Runner/GoogleService-Info.plist`
- Verify bundle IDs match Firebase project settings
- Check Firebase project configuration in console
- Ensure Firebase services are enabled (Auth, FCM, Firestore)

#### 7. BLoC State Management Issues
**Problem:** Events not working after refactoring.

**Solution:**
- Ensure `BuildContext context` is **first positional parameter**:
  ```dart
  // ✅ CORRECT
  EventName(context, param: value)

  // ❌ WRONG
  EventName(context: context, param: value)
  ```
- Regenerate freezed files after event signature changes
- Check BLoC is provided in widget tree via `BlocProvider`

#### 8. Deep Linking Not Working
**Problem:** App doesn't open from web links.

**Solution:**
- **Android:**
  - Verify `AndroidManifest.xml` has intent filters
  - Check `autoVerify="true"` is set
  - Host `assetlinks.json` at `https://yourdomain.com/.well-known/assetlinks.json`
- **iOS:**
  - Verify Associated Domains entitlement in Xcode
  - Host `apple-app-site-association` at `https://yourdomain.com/.well-known/apple-app-site-association`
  - Check DEEP_LINK_URL in environment variables

#### 9. Payment Gateway Errors
**Problem:** PayFast or Maksekeskus payments failing.

**Solution:**
- Verify all payment credentials in `.env`:
  - `PAYFAST_MERCHANT_ID`
  - `PAYFAST_MERCHANT_KEY`
  - `PAYFAST_PASSPHRASE`
- Check sandbox vs production mode
- Test with sandbox credentials first
- Verify callback URLs are accessible
- Check server-side payment verification

#### 10. iOS Build Fails with CocoaPods
**Problem:** `pod install` fails or iOS build errors.

**Solution:**
```bash
cd ios

# Update CocoaPods repository
pod repo update

# Remove and reinstall
rm -rf Pods/ Podfile.lock
pod install --repo-update

# If still failing, try:
pod deintegrate
pod install

cd ..
flutter clean
flutter pub get
```

### Performance Issues

#### App Slow on Startup
- Check API response times
- Reduce initial data fetching
- Implement lazy loading for images
- Use `cached_network_image` for all images

#### Memory Issues
- Review image sizes (compress before upload)
- Limit cached images size (LocalStorage has 1920px limit)
- Dispose controllers in `dispose()` methods
- Use `AutomaticKeepAliveClientMixin` wisely

### Getting Help

1. **Documentation:**
   - Project Documentation: https://githubit.com/demand24-documentation/mobile-app
   - Flutter: https://docs.flutter.dev/
   - BLoC: https://bloclibrary.dev/
   - Firebase: https://firebase.google.com/docs/flutter

2. **Project Resources:**
   - Check project GitHub issues
   - Review commit history for recent changes
   - See `APP_LINKS_CONFIGURATION.md` for deep linking

3. **Community:**
   - Flutter Discord: https://discord.gg/flutter
   - Stack Overflow: Tag `flutter`

4. **Contact:**
   - Support Email: support@githubit.com
   - Development team contact via email

---

## 🔐 Security & Best Practices

### Environment Variables
- ✅ **Never commit** `.env` files to version control
- ✅ Add `.env*` to `.gitignore`
- ✅ Use separate `.env` files for dev/staging/production
- ✅ Rotate API keys regularly
- ✅ Use environment variables for all sensitive data

### API Security
- ✅ Token-based authentication (JWT)
- ✅ Automatic token refresh
- ✅ Secure token storage in SharedPreferences
- ✅ HTTPS-only API communication
- ✅ Certificate pinning (implement for production)

### Build Security
- ✅ Enable **ProGuard/R8** for Android release builds
- ✅ **Code obfuscation** for Flutter:
  ```bash
  flutter build apk --obfuscate --split-debug-info=build/debug-info
  ```
- ✅ Remove debug logs in production
- ✅ Secure signing keys (use `key.properties` for Android)
- ✅ Use App Store Connect for iOS distribution

### Data Protection
- ✅ Encrypt sensitive local data
- ✅ Clear cache on logout
- ✅ Validate user input
- ✅ Sanitize data before display
- ✅ Implement biometric authentication (optional)

### Permissions
- ✅ Request permissions only when needed
- ✅ Explain permission usage to users
- ✅ Handle permission denials gracefully
- ✅ Follow iOS ATT (App Tracking Transparency) guidelines

---

## 📄 License

This project is proprietary software. All rights reserved.

**Copyright © 2024 Demand24. All rights reserved.**

Unauthorized copying, distribution, or modification of this software is strictly prohibited.

---

## 👥 Contributing

### Development Workflow

1. **Create a feature branch:**
```bash
git checkout -b feature/your-feature-name
```

2. **Make your changes:**
   - Follow existing code style
   - Use BLoC pattern for state management
   - Add freezed classes for models
   - Write meaningful commit messages

3. **Test your changes:**
```bash
# Run app
flutter run --dart-define-from-file=.env

# Check for issues
flutter analyze

# Format code
dart format lib/

# Run tests (if available)
flutter test
```

4. **Commit and push:**
```bash
git add .
git commit -m "feat: add new feature description"
git push origin feature/your-feature-name
```

5. **Create Pull Request:**
   - Describe changes clearly
   - Reference related issues
   - Wait for code review

### Code Style Guidelines

- Follow [Effective Dart](https://dart.dev/guides/language/effective-dart)
- Use meaningful variable/function names
- Add comments for complex logic
- Keep functions small and focused
- Use `const` constructors where possible
- Run `dart format` before committing

### BLoC Pattern Guidelines

- One BLoC per feature
- Use freezed for Events and States
- Keep business logic in BLoCs, not in UI
- Use repository pattern for data access
- Handle errors with Either (Left/Right)

---

## 🔄 Recent Updates

### Version 2.0.0 (Latest)

#### Platform Updates
- ✅ **Flutter SDK**: Migrated to 3.38.5+
- ✅ **Dart SDK**: Updated to 3.10+
- ✅ **Android SDK**: Updated to API 34 (Android 14)
- ✅ **Android Compile SDK**: API 36
- ✅ **Kotlin**: Updated to 2.0.21
- ✅ **Java**: Migrated to Java 17
- ✅ **iOS Deployment**: Updated to 16.7+
- ✅ **Xcode**: Updated to 16.0+
- ✅ **Gradle**: Updated to 8.7.0+

#### Code Improvements
- ✅ **API Migration**: Replaced deprecated `withOpacity` → `withValues`
- ✅ **Package Updates**: Migrated `share` → `share_plus`
- ✅ **Code Generation**: Updated freezed and build_runner
- ✅ **BLoC Refactoring**: Context parameters now positional in all events
- ✅ **Performance**: Improved build times and app performance

#### New Features
- ✅ **Custom Date Picker**: Implemented custom date selection UI
- ✅ **UI Selection Flow**: Added UI theme selection on first launch
- ✅ **Location Permission**: Dedicated location permission request screen
- ✅ **Dark Mode**: Enhanced dark mode support across all screens
- ✅ **CustomScaffold**: Unified scaffold for consistent theming

#### Architecture Improvements
- ✅ **Clean Architecture**: Maintained clean separation of concerns
- ✅ **Repository Pattern**: All 21 repositories follow interface pattern
- ✅ **Dependency Injection**: GetIt configuration with 21+ services
- ✅ **Error Handling**: Consistent Either pattern for error handling

#### Developer Experience
- ✅ **Documentation**: Comprehensive README with setup guides
- ✅ **Environment Configuration**: Streamlined .env file setup
- ✅ **Code Generation**: Automated freezed model generation
- ✅ **Build Scripts**: Simplified build commands

### Upcoming Features (Roadmap)

- 🔜 **Offline Mode**: Cache API responses for offline access
- 🔜 **Biometric Auth**: Fingerprint/Face ID authentication
- 🔜 **AR Features**: Augmented reality for product preview
- 🔜 **Voice Search**: Voice-activated search functionality
- 🔜 **Analytics**: In-app analytics dashboard
- 🔜 **Video Calls**: Video consultation with service providers

---

## 📊 Project Statistics

- **Lines of Code**: ~50,000+
- **BLoC Modules**: 38
- **Screens**: 40+
- **Reusable Components**: 64+
- **Repository Interfaces**: 20+
- **Repository Implementations**: 21
- **Dependencies**: 147 packages
- **Supported Platforms**: Android (API 34+), iOS (16.7+)
- **Supported Languages**: 10+
- **Payment Gateways**: 2 (PayFast, Maksekeskus)
- **Map Providers**: Google Maps, OpenStreetMap

---

## 📞 Support & Contact

For technical support or inquiries:

- **Email**: support@githubit.com
- **Website**: https://demand24.com
- **Documentation**: https://githubit.com/demand24-documentation/mobile-app
- **Issue Tracker**: GitHub Issues (if applicable)

---

**Built with ❤️ using Flutter**

*Last Updated: January 2025*
