import 'dart:ui';
import 'package:demand/app_constants.dart';
import 'package:demand/domain/model/response/languages_response.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';

class LanguageManager {
  static void getDeviceLocale(List<LanguageData>? supportedLanguages) {
    Locale deviceLocale = PlatformDispatcher.instance.locale;
    String languageCode = deviceLocale.languageCode;

    AppConstants.countryCodeISO = deviceLocale.countryCode ?? "SA";
    LanguageData? lang;
    supportedLanguages?.forEach((element) {
      if (element.locale == languageCode) {
        lang = element;
        return;
      } else if (element.isDefault ?? false) {
        lang = element;
      }
    });
    LocalStorage.setLanguageData(lang ?? supportedLanguages?.firstOrNull);
  }
}
