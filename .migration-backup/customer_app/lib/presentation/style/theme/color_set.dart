part of 'theme.dart';

class CustomColorSet {
  final Color primary;

  final Color white;
  final Color black;

  final Color textHint;

  final Color textBlack;

  final Color textWhite;

  final Color icon;

  final Color success;

  final Color error;

  final Color transparent;

  final Color backgroundColor;

  final Color socialButtonColor;

  final Color newBoxColor;

  final Color bottomBarColor;

  final Color categoryColor;

  final Color categoryTitleColor;
  final Color border;
  final Color disable;

  CustomColorSet._({
    required this.textHint,
    required this.textBlack,
    required this.textWhite,
    required this.primary,
    required this.white,
    required this.black,
    required this.icon,
    required this.success,
    required this.error,
    required this.transparent,
    required this.backgroundColor,
    required this.socialButtonColor,
    required this.bottomBarColor,
    required this.categoryColor,
    required this.categoryTitleColor,
    required this.newBoxColor,
    required this.border,
    required this.disable,
  });

  factory CustomColorSet._create(CustomThemeMode mode) {
    final isLight = mode.isLight;

    final textHint = isLight ? CustomStyle.textHint : CustomStyle.white;

    final textBlack = isLight ? CustomStyle.black : CustomStyle.white;

    final textWhite = isLight ? CustomStyle.white : CustomStyle.black;

    final categoryColor = isLight
        ? CustomStyle.black
        : CustomStyle.categoryDark;

    final categoryTitleColor = isLight
        ? CustomStyle.black
        : CustomStyle.whiteWithOpacity;

    final primary = CustomStyle.primary;

    const white = CustomStyle.white;
    const black = CustomStyle.black;

    const icon = CustomStyle.icon;

    final backgroundColor = isLight ? CustomStyle.white : CustomStyle.bgDark;

    final newBoxColor = isLight
        ? CustomStyle.subCategory
        : CustomStyle.categoryDark;

    const success = CustomStyle.success;

    const error = CustomStyle.red;

    const transparent = CustomStyle.transparent;

    final border = isLight ? CustomStyle.border : CustomStyle.icon;
    final disable = isLight ? CustomStyle.disable : CustomStyle.black;

    final socialButtonColor = isLight
        ? CustomStyle.socialButtonLight
        : CustomStyle.socialButtonDark;

    final bottomBarColor = isLight ? CustomStyle.black : CustomStyle.black;

    return CustomColorSet._(
      categoryColor: categoryColor,
      textHint: textHint,
      textBlack: textBlack,
      textWhite: textWhite,
      primary: primary,
      white: white,
      black: black,
      icon: icon,
      backgroundColor: backgroundColor,
      success: success,
      error: error,
      transparent: transparent,
      socialButtonColor: socialButtonColor,
      bottomBarColor: bottomBarColor,
      categoryTitleColor: categoryTitleColor,
      newBoxColor: newBoxColor,
      border: border,
      disable: disable,
    );
  }

  static CustomColorSet createOrUpdate(CustomThemeMode mode) {
    return CustomColorSet._create(mode);
  }
}
