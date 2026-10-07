import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';

import 'package:demand/presentation/style/style.dart';

import '../../app_assets.dart';

class ConfirmPage extends StatelessWidget {
  const ConfirmPage({super.key});

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => SafeArea(
        child: Padding(
          padding: EdgeInsets.symmetric(horizontal: 16.r),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Padding(
                padding: EdgeInsets.only(top: 16.r),
                child: Text(
                  AppHelpers.getTranslation(TrKeys.confirmed),
                  style: CustomStyle.interSemi(
                    color: colors.textBlack,
                    size: 22,
                  ),
                ),
              ),
              Align(
                alignment: Alignment.center,
                child: Column(
                  children: [
                    SvgPicture.asset(Assets.svgVerify),
                    6.verticalSpace,
                    Text(
                      AppHelpers.getTranslation(TrKeys.confirmed),
                      style: CustomStyle.interBold(
                        color: colors.textBlack,
                        size: 20,
                      ),
                    ),
                  ],
                ),
              ),
              SizedBox(height: 32.r),
            ],
          ),
        ),
      ),
      floatingButtonLocation: FloatingActionButtonLocation.centerFloat,
      floatingButton: (colors) => Padding(
        padding: EdgeInsets.symmetric(horizontal: 16.r),
        child: SizedBox(
          height: 60.r,
          child: CustomButton(
            title: AppHelpers.getTranslation(TrKeys.returnHome),
            bgColor: colors.primary,
            titleColor: colors.white,
            onTap: () {
              Navigator.pop(context);
            },
          ),
        ),
      ),
    );
  }
}
