import 'package:demand/presentation/style/style.dart';
import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

import '../../app_constants.dart';

class CommonAppBar extends StatelessWidget {
  final Widget child;
  final double height;
  final double bottomPadding;
  final double? left;
  final double? right;
  final Color? color;

  const CommonAppBar({
    super.key,
    required this.child,
    this.height = 76,
    this.bottomPadding = 16,
    this.color,
    this.left,
    this.right,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      height:
          height.h +
          ((MediaQuery.paddingOf(context).top > 34)
              ? 34.h
              : MediaQuery.paddingOf(context).top),
      decoration: BoxDecoration(
        color: color ?? CustomStyle.white,
        borderRadius: BorderRadius.only(
          bottomLeft: Radius.circular(AppConstants.radius.r),
          bottomRight: Radius.circular(AppConstants.radius.r),
        ),
      ),
      child: Align(
        alignment: Alignment.bottomLeft,
        child: Padding(
          padding: REdgeInsets.only(
            left: left ?? 16,
            right: right ?? 16,
            bottom: bottomPadding,
          ),
          child: child,
        ),
      ),
    );
  }
}
