import 'package:demand/presentation/style/theme/theme.dart';
import 'package:demand/presentation/style/theme/theme_wrapper.dart';
import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/app_constants.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import '../keyboard_dismisser.dart';
import 'blur_wrap.dart';

class ModalWrap extends StatelessWidget {
  final Widget Function(CustomColorSet colors) body;
  final EdgeInsets? padding;

  const ModalWrap({super.key, required this.body, this.padding});

  @override
  Widget build(BuildContext context) {
    return KeyboardDismisser(
      isLtr: LocalStorage.getLangLtr(),
      child: BlurWrap(
        radius: BorderRadius.only(
          topLeft: Radius.circular(AppConstants.radiusMax.r),
          topRight: Radius.circular(AppConstants.radiusMax.r),
        ),
        child: ThemeWrapper(
          builder: (colors, controller) {
            return Container(
              margin: MediaQuery.viewInsetsOf(context),
              width: double.infinity,
              padding: padding,
              decoration: BoxDecoration(
                color: colors.newBoxColor,
                borderRadius: BorderRadius.only(
                  topRight: Radius.circular(AppConstants.radiusMax.r),
                  topLeft: Radius.circular(AppConstants.radiusMax.r),
                ),
                boxShadow: [
                  BoxShadow(
                    color: colors.newBoxColor.withValues(alpha: 0.25),
                    offset: const Offset(0, -2),
                    blurRadius: 40,
                    spreadRadius: 0,
                  ),
                ],
              ),
              child: body(colors),
            );
          },
        ),
      ),
    );
  }
}
