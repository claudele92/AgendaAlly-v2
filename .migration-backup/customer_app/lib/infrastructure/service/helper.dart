import 'dart:io';
import 'dart:math';

import 'package:colornames/colornames.dart';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:ftoast/ftoast.dart';
import 'package:demand/app_constants.dart';
import 'package:demand/application/cart/cart_bloc.dart';
import 'package:demand/application/products/product_bloc.dart';
import 'package:demand/domain/model/response/global_settings_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:demand/presentation/style/theme/theme_wrapper.dart';
import 'package:intl/intl.dart';

import '../../domain/model/models.dart';
import 'vibration.dart';

// ignore: depend_on_referenced_packages
import 'package:http/http.dart' as http;

abstract class AppHelpers {
  AppHelpers._();

  static SignUpType getAuthOption() {
    final List<SettingsData> settings = LocalStorage.getSettingsList();
    for (final setting in settings) {
      if (setting.key == 'auth_option') {
        switch (setting.value) {
          case 'phone':
            return SignUpType.phone;
          case 'email':
            return SignUpType.email;
          default:
            return SignUpType.both;
        }
      }
    }
    return SignUpType.both;
  }

  static Future<Uint8List?> getImageFromNetwork(String? imageUrl) async {
    if (imageUrl == null) return null;
    try {
      final http.Response responseData = await http.get(Uri.parse(imageUrl));
      Uint8List imageBytes = responseData.bodyBytes;
      return imageBytes;
    } catch (e) {
      debugPrint('Image request failed.');
      return null;
    }
  }

  static String errorHandler(dynamic e) {
    try {
      return (e is DioException)
          ? (e.response?.data["message"] == "Bad request."
                ? (e.response?.data["params"] as Map).values.first[0]
                : e.response?.data["message"])
          : e.toString();
    } catch (s) {
      try {
        return (e is DioException)
            ? (e.response?.data.toString().substring(
                (e.response?.data.toString().indexOf("<title>") ?? 0) + 7,
                e.response?.data.toString().indexOf("</title") ?? 0,
              )).toString()
            : e.toString();
      } catch (r) {
        try {
          return (e is DioException)
              ? (e.response?.data["error"]["message"]).toString()
              : e.toString();
        } catch (f) {
          return e.toString();
        }
      }
    }
  }

  static String phoneDeFormat(String? phone) {
    return phone
            ?.replaceAll(' ', '')
            .replaceAll('-', '')
            .replaceAll('+', '')
            .replaceAll(')', '')
            .replaceAll('(', '') ??
        '';
  }

  static String generateNonce([int length = 32]) {
    final charset =
        '0123456789ABCDEFGHIJKLMNOPQRSTUVXYZabcdefghijklmnopqrstuvwxyz-.';
    final random = Random.secure();
    return List.generate(
      length,
      (i) => charset[random.nextInt(charset.length)],
    ).join();
  }

  static bool getHourFormat24() {
    final List<SettingsData> settings = LocalStorage.getSettingsList();
    for (final setting in settings) {
      if (setting.key == 'hour_format') {
        return (setting.value ?? "HH:mm") == "HH:mm";
      }
    }
    return true;
  }

  static void errorSnackBar(
    BuildContext context, {
    required String message,
    bool typeFixed = false,
  }) {
    if (message.contains("DioException")) {
      return;
    }
    FToast.toast(
      context,
      toast: Column(
        mainAxisAlignment: MainAxisAlignment.start,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: EdgeInsets.all(16.r),
            margin: EdgeInsets.only(
              bottom: MediaQuery.sizeOf(context).height / 1.5,
              left: 32.r,
              right: 32.r,
            ),
            decoration: BoxDecoration(
              color: CustomStyle.primary,
              borderRadius: BorderRadius.circular(8.r),
            ),
            child: Text(
              message,
              style: CustomStyle.interNormal(
                color: CustomStyle.white,
                size: 14,
              ),
            ),
          ),
        ],
      ),
    );
  }

  static int searchIndex(List<int> ids, int target) {
    int h = 0;
    int l = ids.length - 1;

    while (l <= h) {
      int mid = l + (h - l) ~/ 2;
      if (ids[mid] == target) {
        return mid;
      }

      if (ids[mid] > target) {
        l = mid + 1;
      } else {
        h = mid - 1;
      }
    }
    return -1;
  }

  static String numberFormat(
    num? number, {
    String? symbol,
    bool? isOrder,
    int? decimalDigits,
  }) {
    if (LocalStorage.getSelectedCurrency()?.position == "before") {
      return NumberFormat.currency(
        customPattern: '\u00a4 #,###.#',
        symbol: (isOrder ?? false)
            ? symbol
            : LocalStorage.getSelectedCurrency()?.symbol,
        decimalDigits: decimalDigits ?? 2,
      ).format(number ?? 0);
    } else {
      return NumberFormat.currency(
        customPattern: '#,###.#\u00a4',
        symbol: (isOrder ?? false)
            ? symbol
            : LocalStorage.getSelectedCurrency()?.symbol,
        decimalDigits: decimalDigits ?? 2,
      ).format(number ?? 0);
    }
  }

  static Future openDialog(BuildContext context, {required String title}) {
    return showDialog(
      context: context,
      builder: (_) {
        return ThemeWrapper(
          builder: (colors, controller) {
            return Dialog(
              backgroundColor: colors.transparent,
              insetPadding: EdgeInsets.symmetric(horizontal: 16.w),
              child: Container(
                margin: EdgeInsets.all(24.w),
                width: double.infinity,
                padding: EdgeInsets.all(24.w),
                decoration: BoxDecoration(
                  color: colors.backgroundColor,
                  borderRadius: BorderRadius.circular(24.r),
                ),
                child: SingleChildScrollView(
                  child: Column(
                    children: [
                      Text(
                        title,
                        textAlign: TextAlign.center,
                        style: CustomStyle.interNormal(
                          color: colors.textBlack,
                          size: 18,
                        ),
                      ),
                      24.verticalSpace,
                      CustomButton(
                        onTap: () => Navigator.pop(context),
                        title: AppHelpers.getTranslation(TrKeys.close),
                        bgColor: CustomStyle.primary,
                        titleColor: CustomStyle.white,
                      ),
                    ],
                  ),
                ),
              ),
            );
          },
        );
      },
    );
  }

  static Future<DateTime?> showSelectDate(
    BuildContext context, {
    required CustomColorSet colors,
    DateTime? dateTime,
  }) async {
    return await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: dateTime ?? DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: ColorScheme.light(
              primary: colors.primary, // <-- SEE HERE
              onPrimary: CustomStyle.black, // <-- SEE HERE
              onSurface: CustomStyle.black, // <-- SEE HERE
            ),
            textButtonTheme: TextButtonThemeData(
              style: TextButton.styleFrom(
                foregroundColor: CustomStyle.black, // button text color
              ),
            ),
          ),
          child: child!,
        );
      },
    );
  }

  static Future showCustomDialog(
    BuildContext context, {
    required Widget content,
  }) {
    return showDialog(
      context: context,
      builder: (_) {
        return ThemeWrapper(
          builder: (colors, controller) {
            return Dialog(
              backgroundColor: colors.transparent,
              insetPadding: EdgeInsets.symmetric(horizontal: 16.w),
              child: content,
            );
          },
        );
      },
    );
  }

  static void showAlertDialog(
    BuildContext context, {
    required Widget child,
    EdgeInsets? insetPadding,
    CustomColorSet? colors,
    double radius = 16,
  }) {
    AlertDialog alert = AlertDialog(
      insetPadding: insetPadding,
      backgroundColor: colors?.backgroundColor,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(radius.r),
      ),
      contentPadding: EdgeInsets.all(20.r),
      iconPadding: EdgeInsets.zero,
      content: child,
    );

    showDialog(
      context: context,
      builder: (BuildContext context) {
        return alert;
      },
    );
  }

  static void showCustomModalBottomSheet(
    BuildContext context, {
    required Widget modal,
    double radius = 24,
    bool isDrag = true,
    bool isDismissible = true,
    double paddingTop = 200,
  }) {
    showModalBottomSheet(
      isDismissible: isDismissible,
      enableDrag: isDrag,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.only(
          topLeft: Radius.circular(radius.r),
          topRight: Radius.circular(radius.r),
        ),
      ),
      isScrollControlled: true,
      constraints: BoxConstraints(
        maxHeight: MediaQuery.sizeOf(context).height - paddingTop.r,
      ),
      backgroundColor: CustomStyle.transparent,
      context: context,
      builder: (context) => modal,
    );
  }

  static void showCustomModalBottomSheetDrag(
    BuildContext context, {
    required Function(ScrollController controller) modal,
    double radius = 24,
    bool isDrag = true,
    bool isDismissible = true,
    double paddingTop = 200,
    double maxChildSize = 0.9,
    double minChildSize = 0.4,
    double initialChildSize = 0.8,
  }) {
    showModalBottomSheet(
      isDismissible: isDismissible,
      enableDrag: isDrag,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.only(
          topLeft: Radius.circular(radius.r),
          topRight: Radius.circular(radius.r),
        ),
      ),
      isScrollControlled: true,
      constraints: BoxConstraints(
        maxHeight: MediaQuery.sizeOf(context).height - paddingTop.r,
      ),
      backgroundColor: CustomStyle.transparent,
      context: context,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: initialChildSize,
        maxChildSize: maxChildSize,
        minChildSize: minChildSize,
        expand: false,
        builder: (BuildContext context, ScrollController scrollController) {
          return modal(scrollController);
        },
      ),
    );
  }

  static void addProduct(
    BuildContext context, {
    required ProductData? product,
    required Stocks? stock,
  }) {
    if ((stock?.quantity ?? 0) <= 0) {
      Vibrate.feedback(FeedbackType.error);
      openDialog(
        context,
        title: AppHelpers.getTranslation(TrKeys.errorQuantity),
      );
      return;
    }
    Vibrate.feedback(FeedbackType.selection);
    int count = getCountCart(productId: product?.id, stockId: stock?.id);
    String? image;
    stock?.extras?.forEach((element) {
      if (element.group?.type == "color") {
        image = ImgService.checkIfImage(element.value, product?.stocks)?.path;
        return;
      }
    });
    if (count >= (product?.maxQty ?? 100)) {
      openDialog(
        context,
        title: "${AppHelpers.getTranslation(TrKeys.errorMaxQty)} $count",
      );
      return;
    }
    if (count >= (stock?.quantity ?? 100)) {
      openDialog(
        context,
        title: "${AppHelpers.getTranslation(TrKeys.errorQuantity)} $count",
      );
      return;
    }
    if ((stock?.quantity ?? 100) <=
        count + (count != 0 ? 1 : product?.minQty ?? 1)) {
      LocalStorage.setCartList(
        productId: product?.id,
        stockId: stock?.id,
        image: image,
        count: count + (count != 0 ? 1 : stock?.quantity ?? 1),
      );
      context.read<ProductBloc>().add(const ProductEvent.updateState());
      context.read<CartBloc>().add(const CartEvent.updateState());

      return;
    }

    LocalStorage.setCartList(
      productId: product?.id,
      stockId: stock?.id,
      image: image,
      count:
          count +
          (count != 0
              ? 1
              : (product?.minQty ?? 1) == 0
              ? 1
              : (product?.minQty ?? 1)),
    );
    context.read<ProductBloc>().add(const ProductEvent.updateState());
    context.read<CartBloc>().add(const CartEvent.updateState());
  }

  static void removeProduct(
    BuildContext context, {
    required ProductData? product,
    required Stocks? stock,
  }) {
    Vibrate.feedback(FeedbackType.selection);
    int count = AppHelpers.getCountCart(
      productId: product?.id,
      stockId: stock?.id,
    );
    if (count <= (product?.minQty ?? 1)) {
      LocalStorage.setCartList(
        productId: product?.id,
        stockId: stock?.id,
        count: 0,
      );
      context.read<ProductBloc>().add(const ProductEvent.updateState());
      context.read<CartBloc>().add(const CartEvent.updateState());
      return;
    }
    LocalStorage.setCartList(
      productId: product?.id,
      stockId: stock?.id,
      count: count - 1,
    );
    context.read<ProductBloc>().add(const ProductEvent.updateState());
    context.read<CartBloc>().add(const CartEvent.updateState());
  }

  static void deleteProduct(
    BuildContext context, {
    required ProductData? product,
    required Stocks? stock,
  }) {
    Vibrate.feedback(FeedbackType.selection);
    LocalStorage.setCartList(
      productId: product?.id,
      stockId: stock?.id,
      count: 0,
    );
    context.read<ProductBloc>().add(const ProductEvent.updateState());
    context.read<CartBloc>().add(const CartEvent.updateState());
  }

  static String reviewText(num? review) {
    if (review != null) {
      if (review <= 1) {
        return AppHelpers.getTranslation(TrKeys.veryBad);
      }
      if (review <= 2) {
        return AppHelpers.getTranslation(TrKeys.bad);
      }
      if (review <= 3) {
        return AppHelpers.getTranslation(TrKeys.notBad);
      }
      if (review <= 4) {
        return AppHelpers.getTranslation(TrKeys.good);
      }
      if (review <= 4.5) {
        return AppHelpers.getTranslation(TrKeys.veryGood);
      }
      if (review <= 5) {
        return AppHelpers.getTranslation(TrKeys.exceptional);
      }
      return AppHelpers.getTranslation(TrKeys.newKey);
    } else {
      return AppHelpers.getTranslation(TrKeys.newKey);
    }
  }

  static String getAppName() {
    final List<SettingsData> settings = LocalStorage.getSettingsList();
    for (final setting in settings) {
      if (setting.key == 'title') {
        return setting.value ?? "";
      }
    }
    return '';
  }

  static String getMapKey() {
    final List<SettingsData> settings = LocalStorage.getSettingsList();
    for (final setting in settings) {
      if (setting.key == 'google_map_key') {
        return setting.value ?? AppConstants.googleApiKey;
      }
    }
    return AppConstants.googleApiKey;
  }

  static String getSellerApp() {
    final List<SettingsData> settings = LocalStorage.getSettingsList();
    for (final setting in settings) {
      if (Platform.isIOS) {
        if (setting.key == 'vendor_app_ios') {
          return setting.value ?? "";
        }
      } else {
        if (setting.key == 'vendor_app_android') {
          return setting.value ?? "";
        }
      }
    }
    return '';
  }

  static String getFirebaseApiKey() {
    final List<SettingsData> settings = LocalStorage.getSettingsList();
    for (final setting in settings) {
      if (setting.key == 'api_key') {
        return setting.value ?? AppConstants.firebaseWebKey;
      }
    }
    return AppConstants.firebaseWebKey;
  }

  static num getCommission() {
    final List<SettingsData> settings = LocalStorage.getSettingsList();
    for (final setting in settings) {
      if (setting.key == 'commission') {
        return num.tryParse(setting.value ?? "0") ?? 0;
      }
    }
    return 0;
  }

  static bool getProductUiType() {
    final List<SettingsData> settings = LocalStorage.getSettingsList();
    for (final setting in settings) {
      if (setting.key == 'product_ui_type') {
        return setting.value == "2";
      }
    }
    return true;
  }

  static bool getGroupOrder() {
    final List<SettingsData> settings = LocalStorage.getSettingsList();
    for (final setting in settings) {
      if (setting.key == 'group_order') {
        return setting.value == "1";
      }
    }
    return true;
  }

  static bool getProductsEnabled() {
    final List<SettingsData> settings = LocalStorage.getSettingsList();
    for (final setting in settings) {
      if (setting.key == 'products_enabled') {
        return setting.value == "1";
      }
    }
    return true;
  }

  static int getType() {
    if (AppConstants.isDemo) {
      return LocalStorage.getUiType() ?? 0;
    }
    final List<SettingsData> settings = LocalStorage.getSettingsList();
    for (final setting in settings) {
      if (setting.key == 'ui_type') {
        return (int.tryParse(setting.value ?? "1") ?? 1) - 1;
      }
    }
    return 0;
  }

  static String getTranslation(String trKey) {
    final Map<String, dynamic> translations = LocalStorage.getTranslations();
    if (AppConstants.autoTrn) {
      return (translations[trKey] ??
          (trKey.isNotEmpty
              ? trKey
                    .replaceAll(".", " ")
                    .replaceAll("_", " ")
                    .replaceFirst(
                      trKey.substring(0, 1),
                      trKey.substring(0, 1).toUpperCase(),
                    )
              : ''));
    } else {
      return translations[trKey] ?? trKey;
    }
  }

  static bool productInclude({required int? productId, required int? stockId}) {
    final list = LocalStorage.getCartList();
    for (var element in list) {
      if (element.productId == productId && element.stockId == stockId) {
        if (element.count <= 0) {
          return false;
        }
        return true;
      }
    }
    return false;
  }

  static int getCountCart({required int? productId, required int? stockId}) {
    final list = LocalStorage.getCartList();
    for (var element in list) {
      if (element.productId == productId && element.stockId == stockId) {
        return element.count;
      }
    }
    return 0;
  }

  static RichText getSearchResultText(
    String mainText,
    String subtext,
    CustomColorSet colors, {
    int? maxLine,
  }) {
    if (!mainText.toUpperCase().contains(subtext.toUpperCase())) {
      return RichText(
        overflow: maxLine == null ? TextOverflow.clip : TextOverflow.ellipsis,
        maxLines: maxLine,
        text: TextSpan(
          text: mainText,
          style: CustomStyle.interNormal(
            color: colors.textBlack.withValues(alpha: 0.3),
            size: 14,
          ),
        ),
      );
    }
    final int startIndex = mainText.toUpperCase().indexOf(
      subtext.toUpperCase(),
    );
    final String prefixText = mainText.substring(0, startIndex);
    final String centerText = mainText.substring(
      startIndex,
      startIndex + subtext.length,
    );
    final hasPostText = subtext.length + startIndex < mainText.length - 1;
    final String postText = hasPostText
        ? mainText.substring(subtext.length + startIndex, mainText.length - 1)
        : '';
    return RichText(
      overflow: maxLine == null ? TextOverflow.clip : TextOverflow.ellipsis,
      maxLines: maxLine,
      text: TextSpan(
        text: prefixText,
        style: CustomStyle.interNormal(
          color: colors.textBlack.withValues(alpha: 0.3),
          size: 14,
        ),
        children: <TextSpan>[
          TextSpan(
            text: centerText,
            style: CustomStyle.interNormal(color: colors.textBlack, size: 14),
          ),
          TextSpan(
            text: postText,
            style: CustomStyle.interNormal(
              color: colors.textBlack.withValues(alpha: 0.3),
              size: 14,
            ),
          ),
        ],
      ),
    );
  }

  static bool checkPhone(String value) {
    String pattern = r'^[0-9]+$';
    RegExp regExp = RegExp(pattern);
    return regExp.hasMatch(value.replaceAll("+", ""));
  }

  static bool checkServiceTime(Map<int, MasterModel> selectMasters) {
    if (selectMasters.isEmpty) {
      return true;
    }

    // Convert masters to time slots for easier comparison
    List<MapEntry<int, Map<String, DateTime>>> timeSlots = [];

    for (var entry in selectMasters.entries) {
      final master = entry.value;

      // Check if time is null
      if (master.time == null) {
        return false;
      }

      // Calculate end time based on interval and pause
      final interval = master.serviceMaster?.interval?.toInt() ?? 0;
      final pause = master.serviceMaster?.pause?.toInt() ?? 0;
      final endTime = master.time!.add(Duration(minutes: interval + pause));

      timeSlots.add(
        MapEntry(entry.key, {'start': master.time!, 'end': endTime}),
      );
    }

    // Sort time slots by start time
    timeSlots.sort((a, b) => a.value['start']!.compareTo(b.value['start']!));

    // Check for conflicts
    for (int i = 0; i < timeSlots.length - 1; i++) {
      final currentSlot = timeSlots[i];
      final nextSlot = timeSlots[i + 1];

      // If current slot's end time overlaps with next slot's start time
      if (currentSlot.value['end']!.isAfter(nextSlot.value['start']!)) {
        return false; // Conflict found
      }
    }

    return true; // No conflicts
  }

  static ExtrasType getExtraTypeByValue(String? value) {
    switch (value) {
      case 'color':
        return ExtrasType.color;
      case 'text':
        return ExtrasType.text;
      case 'image':
        return ExtrasType.image;
      default:
        return ExtrasType.text;
    }
  }

  static bool checkIfHex(String? type) {
    if (type == null || type.isEmpty) {
      return false;
    }
    if (type.replaceAll(' ', '').length == 7 && type[0] == '#') {
      return true;
    } else {
      return false;
    }
  }

  static String getNameColor(String? value) {
    if (checkIfHex(value)) {
      final color = Color(int.parse('0xFF${value?.substring(1, 7)}'));
      return color.colorName;
    }
    return value ?? TrKeys.unKnow;
  }

  static double getInitialLatitude() {
    final List<SettingsData> settings = LocalStorage.getSettingsList();
    for (final setting in settings) {
      if (setting.key == 'location') {
        final String? latString = setting.value?.substring(
          0,
          setting.value?.indexOf(','),
        );
        if (latString == null) {
          return AppConstants.demoLatitude;
        }
        final double? lat = double.tryParse(latString);
        return lat ?? AppConstants.demoLatitude;
      }
    }
    return AppConstants.demoLatitude;
  }

  static double getInitialLongitude() {
    final List<SettingsData> settings = LocalStorage.getSettingsList();
    for (final setting in settings) {
      if (setting.key == 'location') {
        final String? latString = setting.value?.substring(
          0,
          setting.value?.indexOf(','),
        );
        if (latString == null) {
          return AppConstants.demoLongitude;
        }
        final String? lonString = setting.value?.substring(
          (latString.length) + 2,
          setting.value?.length,
        );
        if (lonString == null) {
          return AppConstants.demoLatitude;
        }
        final double lon = double.parse(lonString);
        return lon;
      }
    }
    return AppConstants.demoLongitude;
  }

  static bool getParcel() {
    final List<SettingsData> settings = LocalStorage.getSettingsList();
    for (final setting in settings) {
      if (setting.key == 'active_parcel') {
        return setting.value == "1";
      }
    }
    return false;
  }

  static num getMinAmount() {
    final List<SettingsData> settings = LocalStorage.getSettingsList();
    for (final setting in settings) {
      if (setting.key == 'min_amount') {
        return num.tryParse(setting.value ?? "0") ?? 0;
      }
    }
    return 0;
  }

  static bool getReferralActive() {
    final List<SettingsData> settings = LocalStorage.getSettingsList();
    for (final setting in settings) {
      if (setting.key == 'referral_active') {
        return setting.value == "1";
      }
    }
    return false;
  }

  static int getOrderStatusForNumber(String? value) {
    switch (value) {
      case 'new':
        return 6;
      case 'accepted':
        return 5;
      case 'ready':
        return 4;
      case 'on_a_way':
        return 3;
      case 'pause':
        return 2;
      case 'delivered':
        return 1;
      case 'canceled':
        return 0;
      default:
        return 6;
    }
  }

  static String? findLowPriceStocks(List<Stocks>? stocks) {
    if (stocks?.isEmpty ?? true) {
      return numberFormat(0);
    }
    num price = stocks?.first.price ?? 0;
    stocks?.forEach((element) {
      if ((element.price ?? 0) < price) {
        price = element.price ?? 0;
      }
    });
    return numberFormat(price);
  }

  static Future openDialogImagePicker(
    BuildContext context, {
    required VoidCallback openCamera,
    required VoidCallback openGallery,
  }) {
    return showDialog(
      context: context,
      builder: (_) {
        return ThemeWrapper(
          builder: (colors, controller) {
            return Dialog(
              backgroundColor: colors.transparent,
              insetPadding: EdgeInsets.symmetric(horizontal: 16.w),
              child: Container(
                margin: EdgeInsets.all(24.w),
                width: double.infinity,
                padding: EdgeInsets.all(24.w),
                decoration: BoxDecoration(
                  color: colors.backgroundColor,
                  borderRadius: BorderRadius.circular(AppConstants.radiusMax.r),
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    Text(
                      getTranslation(TrKeys.selectPhoto),
                      textAlign: TextAlign.center,
                      style: CustomStyle.interNormal(
                        color: colors.textBlack,
                        size: 18,
                      ),
                    ),
                    const Divider(),
                    8.verticalSpace,
                    ButtonEffectAnimation(
                      onTap: openCamera,
                      child: Padding(
                        padding: EdgeInsets.symmetric(
                          horizontal: 16.r,
                          vertical: 8.r,
                        ),
                        child: Row(
                          children: [
                            Icon(
                              FlutterRemix.camera_lens_line,
                              color: colors.textBlack,
                            ),
                            4.horizontalSpace,
                            Text(
                              getTranslation(TrKeys.takePhoto),
                              textAlign: TextAlign.center,
                              style: CustomStyle.interNormal(
                                color: colors.textBlack,
                                size: 16,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    8.verticalSpace,
                    ButtonEffectAnimation(
                      onTap: openGallery,
                      child: Padding(
                        padding: EdgeInsets.symmetric(
                          horizontal: 16.r,
                          vertical: 8.r,
                        ),
                        child: Row(
                          children: [
                            Icon(
                              FlutterRemix.gallery_line,
                              color: colors.textBlack,
                            ),
                            4.horizontalSpace,
                            Text(
                              getTranslation(TrKeys.chooseFromLibrary),
                              textAlign: TextAlign.center,
                              style: CustomStyle.interNormal(
                                color: colors.textBlack,
                                size: 16,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }
}
