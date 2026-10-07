import 'package:flutter/material.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route_parcel.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class InfoPage extends StatelessWidget {
  final int index;
  final CustomColorSet colors;

  const InfoPage({super.key, required this.index, required this.colors});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          Container(
            decoration: BoxDecoration(
              image: DecorationImage(
                image: AssetImage(ListConstants.infoImage[index]),
                fit: BoxFit.cover,
              ),
            ),
            child: Padding(
              padding: EdgeInsets.symmetric(horizontal: 16.r),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.end,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    AppHelpers.getTranslation(ListConstants.infoTitle[index]),
                    style: CustomStyle.interNoSemi(
                      size: 40,
                      color: colors.white,
                    ),
                  ),
                  40.verticalSpace,
                  CustomButton(
                    title: index == 3
                        ? AppHelpers.getTranslation(TrKeys.back)
                        : AppHelpers.getTranslation(TrKeys.next),
                    bgColor: colors.primary,
                    titleColor: colors.white,
                    onTap: () {
                      if (index == 3) {
                        Navigator.pop(context);
                        return;
                      }
                      AppRouteParcel.goInfoScreen(
                        context,
                        replace: true,

                        index: index + 1,
                        colors: colors,
                      );
                    },
                  ),
                  32.verticalSpace,
                ],
              ),
            ),
          ),
          Positioned(
            top: 32.r,
            right: 8.r,
            child: IconButton(
              onPressed: () {
                Navigator.pop(context);
              },
              icon: Icon(
                FlutterRemix.close_line,
                color: colors.textBlack,
                size: 32.r,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
