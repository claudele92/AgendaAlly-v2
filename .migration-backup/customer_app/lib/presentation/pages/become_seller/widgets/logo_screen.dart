import 'dart:io';

import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/become_seller/become_seller_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class LogoScreen extends StatelessWidget {
  final CustomColorSet colors;

  const LogoScreen({super.key, required this.colors});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          AppHelpers.getTranslation(TrKeys.logo),
          style: CustomStyle.interNormal(color: colors.textBlack),
        ),
        16.verticalSpace,
        BlocBuilder<BecomeSellerBloc, BecomeSellerState>(
          buildWhen: (p, n) {
            return p.logoPath != n.logoPath;
          },
          builder: (context, state) {
            return Container(
              width: double.infinity,
              padding: EdgeInsets.all(8.r),
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(AppConstants.radius.r),
                color: colors.newBoxColor,
              ),
              child: Row(
                children: [
                  state.logoPath == null
                      ? const CustomNetworkImage(
                          url: "",
                          height: 56,
                          width: 56,
                          radius: 28,
                        )
                      : Container(
                          height: 56.r,
                          width: 56.r,
                          decoration: BoxDecoration(
                            image: DecorationImage(
                              fit: BoxFit.cover,
                              image: FileImage(File(state.logoPath ?? "")),
                            ),
                            borderRadius: BorderRadius.circular(38.r),
                            color: colors.newBoxColor,
                          ),
                        ),
                  const Spacer(),
                  ButtonEffectAnimation(
                    onTap: () {
                      AppHelpers.openDialogImagePicker(
                        context,
                        openCamera: () async {
                          String? titleImg = await ImgService.getCamera();
                          if (context.mounted && (titleImg != null)) {
                            context.read<BecomeSellerBloc>().add(
                              BecomeSellerEvent.updateLogoPath(
                                imagePath: titleImg,
                              ),
                            );
                            Navigator.pop(context);
                          }
                        },
                        openGallery: () async {
                          String? titleImg = await ImgService.getGallery();
                          if (context.mounted && (titleImg != null)) {
                            context.read<BecomeSellerBloc>().add(
                              BecomeSellerEvent.updateLogoPath(
                                imagePath: titleImg,
                              ),
                            );
                            Navigator.pop(context);
                          }
                        },
                      );
                    },
                    child: Container(
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(28.r),
                        color: colors.backgroundColor,
                      ),
                      padding: EdgeInsets.symmetric(
                        vertical: 10.r,
                        horizontal: 20.r,
                      ),
                      child: Text(
                        AppHelpers.getTranslation(TrKeys.uploadPhoto),
                        style: CustomStyle.interNormal(color: colors.textBlack),
                      ),
                    ),
                  ),
                ],
              ),
            );
          },
        ),
      ],
    );
  }
}
