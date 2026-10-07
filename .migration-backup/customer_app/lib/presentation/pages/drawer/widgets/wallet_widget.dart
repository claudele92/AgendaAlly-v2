import 'package:auto_size_text/auto_size_text.dart';
import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/profile/profile_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route_setting.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class WalletScreen extends StatelessWidget {
  final CustomColorSet colors;

  const WalletScreen({super.key, required this.colors});

  @override
  Widget build(BuildContext context) {
    return ButtonEffectAnimation(
      onTap: () {
        AppRouteSetting.goTransactionList(context);
      },
      child: Container(
        height: 56.r,
        padding: EdgeInsets.all(10.r),
        decoration: BoxDecoration(
          color: colors.newBoxColor,
          borderRadius: BorderRadius.circular(AppConstants.radius.r),
        ),
        child: BlocBuilder<ProfileBloc, ProfileState>(
          buildWhen: (p, n) {
            return p.isLoading != n.isLoading;
          },
          builder: (context, state) {
            final numberLength = AppHelpers.numberFormat(
              LocalStorage.getUser().wallet?.price,
            ).length;
            return Row(
              children: [
                Icon(FlutterRemix.wallet_3_line, color: colors.textBlack),
                8.horizontalSpace,
                if (numberLength < 12)
                  Text(
                    "${AppHelpers.getTranslation(TrKeys.wallet)} : ",
                    style: CustomStyle.interNormal(
                      color: colors.textBlack,
                      size: 16,
                    ),
                  ),
                Expanded(
                  child: AutoSizeText(
                    AppHelpers.numberFormat(
                      LocalStorage.getUser().wallet?.price,
                    ),
                    style: CustomStyle.interNormal(
                      color: colors.textBlack,
                      size: 16,
                    ),
                    maxLines: 1,
                  ),
                ),
              ],
            );
          },
        ),
      ),
    );
  }
}
