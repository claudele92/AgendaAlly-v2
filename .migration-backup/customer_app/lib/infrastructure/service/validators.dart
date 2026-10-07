import 'package:demand/infrastructure/service/services.dart';

abstract class AppValidators {
  AppValidators._();

  static bool isEmail(String email) => RegExp(
    "^[a-zA-Z0-9.!#\$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,253}[a-zA-Z0-9])?(?:\\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,253}[a-zA-Z0-9])?)*\$",
  ).hasMatch(email);

  static String? isValidEmail(String? email) {
    if (RegExp(
      "^[a-zA-Z0-9.!#\$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,253}[a-zA-Z0-9])?(?:\\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,253}[a-zA-Z0-9])?)*\$",
    ).hasMatch(email ?? "")) {
      return null;
    }
    return AppHelpers.getTranslation(TrKeys.thisNotEmail);
  }

  static String? isNotEmptyValidator(String? title) {
    if (title?.isEmpty ?? true) {
      return AppHelpers.getTranslation(TrKeys.thisFieldIsRequired);
    }
    return null;
  }

  static String? isNumberValidator(String? title) {
    if (title?.isEmpty ?? true) {
      return AppHelpers.getTranslation(TrKeys.thisFieldIsRequired);
    }
    if ((num.tryParse(title ?? "") ?? 0.0).isNegative) {
      return AppHelpers.getTranslation(TrKeys.thisFieldIsNotMinusOrZero);
    }
    return null;
  }

  static String? isValidPrice(String? title) {
    if (title?.isEmpty ?? true) {
      return AppHelpers.getTranslation(TrKeys.thisFieldIsRequired);
    } else if ((num.tryParse(title ?? "0") ?? 0) <= 0) {
      return AppHelpers.getTranslation(TrKeys.thisFieldIsNotMinusOrZero);
    }
    return null;
  }

  static String? isValidPassword(String? password) {
    if (password?.isEmpty ?? true) {
      return AppHelpers.getTranslation(TrKeys.thisFieldIsRequired);
    } else if ((password?.length ?? 0) < 5) {
      return AppHelpers.getTranslation(TrKeys.passwordMinFive);
    }
    return null;
  }

  static String? isValidConfirmPassword(
    String password,
    String? confirmPassword,
  ) {
    if (confirmPassword?.isEmpty ?? true) {
      return AppHelpers.getTranslation(TrKeys.thisFieldIsRequired);
    } else if (confirmPassword != password) {
      return AppHelpers.getTranslation(TrKeys.passwordNoCorrect);
    }
    return null;
  }
}
