import 'package:demand/presentation/style/theme/theme.dart';
import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

class ModalDrag extends StatelessWidget {
  final CustomColorSet colors;

  const ModalDrag({super.key, required this.colors});

  @override
  Widget build(BuildContext context) {
    return Align(
      alignment: Alignment.center,
      child: Column(
        children: [
          8.verticalSpace,
          Container(
            height: 4.r,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(40.r),
              color: colors.icon,
            ),
            margin: EdgeInsets.symmetric(
              horizontal: MediaQuery.sizeOf(context).width / 2 - 48.r,
            ),
          ),

          18.verticalSpace,
        ],
      ),
    );
  }
}
