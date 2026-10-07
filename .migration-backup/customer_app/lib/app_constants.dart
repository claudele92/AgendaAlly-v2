import 'presentation/app_assets.dart';

abstract class AppConstants {
  AppConstants._();

  static const bool isDemo = true;
  static const bool autoTrn = true;
  static const bool shopBadge = true;
  static const bool enableGame = true;

  static const bool isPhoneFirebase = true;
  static const int cacheSize = 1920;

  /// api urls
  static const String baseUrl = String.fromEnvironment('BASE_URL');
  static const String webUrl = String.fromEnvironment('WEB_URL');
  static const String adminPageUrl = String.fromEnvironment('ADMIN_URL');
  static const String wsBaseUrl = String.fromEnvironment('WS_BASE_URL');
  static const String wsSecret = String.fromEnvironment('WS_SECRET');
  static const String googleApiKey = String.fromEnvironment(
    'GOOGLE_MAPS_API_KEY',
  );
  static const String firebaseWebKey = String.fromEnvironment(
    'FIREBASE_API_KEY',
  );
  static const String routingBaseUrl = String.fromEnvironment('ROUTING_API');
  static const String routingKey = String.fromEnvironment('ROUTING_KEY');
  static const String deepLinkHost = String.fromEnvironment('DEEP_LINK_URL');

  // PayFast
  static const String passphrase = String.fromEnvironment('PAYFAST_PASSPHRASE');
  static const String merchantId = String.fromEnvironment(
    'PAYFAST_MERCHANT_ID',
  );
  static const String merchantKey = String.fromEnvironment(
    'PAYFAST_MERCHANT_KEY',
  );

  /// auth phone fields
  static const bool isNumberLengthAlwaysSame = true;
  static String countryCodeISO = 'UZ';
  static const bool showFlag = true;
  static const bool showArrowIcon = true;

  static const double radius = 12;
  static const double radiusMax = 20;

  /// location
  static const double demoLatitude = 41.304223;
  static const double demoLongitude = 69.2348277;
  static const double pinLoadingMin = 0.116666667;
  static const double pinLoadingMax = 0.611111111;

  ///refresh duration
  static const Duration timeRefresh = Duration(seconds: 30);

  ///image
  static const String darkBgChat = Assets.imagesDarkBg;
  static const String lightBgChat = Assets.imagesLightBg;

  static const String newMessage = "new_message";
}
